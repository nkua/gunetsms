<?php
/**
 * SMS error codes
 *
 * @package GunetSMS
 */

namespace GunetSMS;

class Codes {

    // ==================================================================
    //
    // Generic Codes
    //
    // ------------------------------------------------------------------

    const INVALID_REQUEST = -1;
    const SMSC_DENIED = -2;
    const SMSC_NOT_ALLOWED = -3;
    const LDAP_CONNECT = -4;
    const LDAP_BIND = -5;
    const LDAP_SEARCH = -6;
    const MYSQL_CONNECT = -7;
    const MYSQL_DB = -8;
    const MYSQL_QUERY = -9;
    const INTERNAL_ERROR = -10;
    const NO_ROUTE = -11;
    const ROUTE_NO_CONNECTION = -12;
    const ROUTE_ERROR_RESPONSE_RECEIVED = -13;

    // ==================================================================
    //
    // Application-logic codes
    //
    // ------------------------------------------------------------------

    /**
     * Argument invalid
     */
    const ARGUMENT_INVALID = -100;

    /**
     * Argument not found
     */
    const ARGUMENT_NOT_FOUND = -101;

    /**
     * Action Denied
     */
    const DENIED = -102;

    /**
     * Application limit or quota reached
     */
    const OVERQUOTA_OR_LIMIT = -103;

    /**
     * List of usernames presented
     */
    const USERNAMES_LIST = 200;

    /**
     * More information requested
     */
    const NEED_INFO = 201;

    /**
     * Action completed successfully
     */
    const SUCCESS = 300;

    /**
     * Action was valid but triggered no change
     */
    const SUCCESS_NO_CHANGE = 301;

    public static function allCodes() {
        $reflect = new \ReflectionClass('\GunetSMS\Codes');
        return $reflect->getConstants();
    }


    public static function lookup($code) {
        static $lookuptable;
        if(!isset($lookuptable)) {
            $reflect = new \ReflectionClass('\GunetSMS\Codes');
            $lookuptable = $reflect->getConstants();
        }

        foreach ($lookuptable as $name => $value) {
            if ($value == $code) {
                return $name;
            }
        }
        return null;    
    }

    /**
     * Return a formatted textual description of sms error code / outcome code
     *
     * @param int $o sms error code / outcome code
     * @return string Textual description of sms error code / outcome code
     */
    public static function format($o) {
        $constname = Codes::lookup($o);
        
        if($constname === null) {
          $desc = 'Unknown ('.$o.')';
          $label = 'default';

        } else {
            if($o < -99) {
              $label = 'warning';
            } elseif($o < 0) {
              $label = 'important';
            } else {
              $label = 'success';
          }
          $desc = ucwords(strtolower(str_replace('_', ' ', $constname)));
        }

        return '<span class="label label-'.$label.'">'.$desc.'</span>';
    }


}

