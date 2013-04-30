<?php
/**
 * @package GunetSMS
 * @version $Id$
 */

namespace GunetSMS;

/**
 * SMS class to be used in the application.
 * How to use:
 *
 * Sending by username:
 *
 * <pre>
 * $mysms = new GunetSMS('username', $smsgwconfig);
 * $mysms->send('This is a test message.');
 * </pre>
 *
 * Sending by mobile phone number: (for jsonrpc or kannel methods)
 *
 * <pre>
 * $mysms = new GunetSMS('6991324567', $smsgwconfig);
 * $mysms->send('This is a test message.');
 * </pre>
 *
 * @package GunetSMS
 */
class Sender {

    /** Configuration (Zend Config instance) */
    protected $_config;

    /** Destination (mobile number or recipient username) */
    protected $_recipient;

    /** Log ID of message that we reply to. Optional. Can be used to build a
     * relationship between this outgoing text with an incoming one.
     */
    protected $log_id = null;


    /**
     * @param string $recipient Username or mobile number of recipient
     * @param object $config Zend_Config instance of sms gateway configuration
     * @return void
     */
    public function __construct(&$recipient, &$config) {
        $this->_recipient = $recipient;
        $this->_config = $config;
    }

    /**
     * Set Log ID of message that we reply to. Optional. Can be used to build a
     * relationship between this outgoing text with an incoming one.
     *
     * @param string $log_id
     * @return void
     */
    public function setLogId($log_id) {
        $this->log_id = $log_id;
    }

    /**
     * @param string $message Message to send
     * @return mixed Boolean true upon success, or string with error message upon error.
     */
    public function send($message) {
        if(isset($this->_config->institution) && !empty($this->_config->institution)) {
            // We are sending an sms to a user by their username.
            // The SMS web service will do the hard work.
            $method = 'send_by_uid';
            $request = array(
                'uid' => $this->_recipient,
                'message' => $message,
                'institution' => $this->_config->institution,
                // 'simulate' => true
            );

        } else {
            // We are sending an sms to a user by their mobile number
            $method = 'send';

            // canonicalize number
            if(substr($this->_recipient, 0, 3) == '+30') {
                $number = substr($this->_recipient, 3);
            } else {
                $number = $this->_recipient;
            }

            $request = array(
                'number' => $number,
                'message' => $message,
                // 'simulate' => true
            );
        }

        if(!is_null($this->log_id)) {
            $request['log_id'] = $this->log_id;
        }

        $url = $this->_config->host . $this->_config->uri;

        try {
            $ret = $this->_jsonrpccall($url, $method, $request);
        } catch (Exception $e) {
            // Failure; message was not sent
            return $e->getMessage();
        }

        // Success; Message sent
        return true;

    }


    /**
     * Performs a jsonRCP request and gets the results as an array
     *
     * @param string $url
     * @param string $method
     * @param array $params
     * @return array
     */
    private function _jsonrpccall($url, $method, $params) {
        $currentId = 1;
        
        // prepares the request
        $request = array(
            'jsonrpc' => "2.0",
            'method' => $method,
            'params' => $params,
            'id' => $currentId
        );
        
        $request = json_encode($request, JSON_FORCE_OBJECT);
        
        // performs the HTTP POST
        $opts = array ('http' => array (
            'method'  => 'POST',
            'header'  => 'Content-type: application/json',
            'content' => $request
        ));
        $context  = stream_context_create($opts);
        if ($fp = fopen($url, 'r', false, $context)) {
            $response = '';
            while($row = fgets($fp)) {
                $response.= trim($row)."\n";
            }
            $response = json_decode($response,true);
        } else {
            throw new Exception('Unable to connect to URL');
        }
        
        // final checks and return
        // check
        if ($response['id'] != $currentId) {
            throw new Exception('Incorrect response id (request id: '.$currentId.', response id: '.$response['id'].')');
        }
        
        if (isset($response['error'])) {
            throw new Exception($response['error']['code'] . ' ' . $response['error']['message']);
        }
        
        return $response['result'];
    }

}

