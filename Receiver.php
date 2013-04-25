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

        $ret = $request->getParams();

        $this->mobile = $ret['mobile'];
        $this->smsc = $ret['smsc'];
        $this->text = $ret['text'];

        if(isset($ret['log_id'])) {
            $this->log_id = $ret['log_id'];
        }

        return;
    }

}
