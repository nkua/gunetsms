<?php
/**
 * Receiver from GUnet Web Service. It acts as intermediary between an application and an
 * SMS gateway. It uses JSONRPC 2.0 calls for communication.
 *
 * Example for testing: 
 * <pre>
 * curl -i -X POST 'http://.../endpoint.php' -d '{"jsonrpc":"2.0","method":"incoming_sms_message","params":{"mobile":"777777777","smsc":"34782387","text":"Hello World"},"id":103}'
 * </pre>
 *
 * @package GunetSMS
 */

namespace GunetSMS;

class Receiver {
    
    protected $mobile;
    protected $smsc;
    protected $text;
    protected $log_id = null;
    protected $_request_id;

    /**
     * Getter for mobile number
     * @return string
     */
    public function getMobile() {
        return $this->mobile;
    }

    /**
     * Getter for SMS Center number
     * @return string
     */
    public function getSmsc() {
        return $this->smsc;
    }

    /**
     * Getter for Text Message
     * @return string
     */
    public function getText() {
        return $this->text;
    }

    /**
     * Getter for Log ID
     * @return string
     */
    public function getLogId() {
        return $this->log_id;
    }

    public function __construct() {
    }

    public function read() {
        $server = new \Zend_Json_Server();

        if ('GET' == $_SERVER['REQUEST_METHOD']) {
            throw new InvalidRequestException(); return false;
        }

        $request = $server->getRequest();
        
        if($request->getMethod() != 'incoming_sms_message') {
            throw new InvalidRequestException(); return false;
        }
        
        $this->_request_id = $request->getId();

        $ret = $request->getParams();

        $this->mobile = $ret['mobile'];
        $this->smsc = $ret['smsc'];
        $this->text = $ret['text'];

        if(isset($ret['log_id'])) {
            $this->log_id = $ret['log_id'];
        }

        return;
    }


    /**
     * Send status response
     *
     * @param int $code;
     * @param string $message;
     */
    public function status($code, $message = '')
    {
        $response = new \Zend_Json_Server_Response();
        $response->setId($this->_request_id);
        

        // Note: for simplicity, I don't return a JSON-RPC error message for negative answers;
        // I simply return the negative error code to the SMS Web Service.
        
        $response->setResult($code);
        
        /*
        if($code < 0) {
            $response->setError(
                new \Zend_Json_Server_Error($message, -32000, $code)
            );
        } else {
            $response->setResult($code);
        }
        */
        
        header("Content-type: application/json-rpc; charset=utf-8");
        echo $response->toJson();
        flush();
    }
}
