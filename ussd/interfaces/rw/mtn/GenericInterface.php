<?php

/**
 * Generic interface manager
 * 
 * @author Maritim, Kip <kip@maritim.co.ke>
 * @copyright 2017
 * 
 */
//USSD Interface manager
include dirname(__FILE__) . '/../../USSDInterface.php';

class GenericInterface extends USSDInterface {

    /**
     * 
     * @param type $MSISDN
     * @param type $SESSION_STATE
     * @param type $ACCESSPOINT
     * @param type $INPUT
     * @param type $ORIGIN
     * @param type $OTHER_PARAMS
     * @param type $SESSION_ID
     * @param type $IMSI
     */
    public function __construct($MSISDN, $INPUT, $SESSION_ID, $newRequest) {

        $NETID = $ACCESSPOINT = '919';
        $SESSION_STATE = $IMSI = $OTHER_PARAMS = '';
        if (isset($_GET['NETCODE']) and 0 + $_GET['NETCODE'] > 0) {
            $NETID = 0 + $_GET['NETCODE'];
        }
        parent::__construct($MSISDN, $SESSION_STATE, $ACCESSPOINT, $INPUT, $NETID, $OTHER_PARAMS, $SESSION_ID, $IMSI);
    }

}
