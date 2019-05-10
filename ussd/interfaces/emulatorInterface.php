<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include 'USSDInterface.php';

        Const ORIGIN = 63902;

class GenericInterface extends USSDInterface {

    public function __construct($MSISDN, $SESSION_STATE, $ACCESSPOINT, $INPUT, $ORIGIN, $OTHER_PARAMS, $SESSION_ID, $IMSI) {

        $NETID = ORIGIN;
        if (isset($_GET['NETCODE']) and 0 + $_GET['NETCODE'] > 0) {
            $NETID = 0 + $_GET['NETCODE'];
        }
        parent::__construct($MSISDN, $SESSION_STATE, $ACCESSPOINT, $INPUT, $NETID, $OTHER_PARAMS, $SESSION_ID, $IMSI);
    }

}

if (isset($_GET['MSISDN'])) {
    $MSISDN = $_GET['MSISDN'];
}

if (isset($_GET['SERVICE_CODE'])) {
    $ACCESSPOINT = $_GET['SERVICE_CODE'];
}

if (isset($_GET['INPUT_STRING'])) {
    $INPUT = $_GET['INPUT_STRING'];
}

$SESSION_STATE = "CONT";
if (isset($_GET['OP_CODE'])) {
    $SESSION_STATE = $_GET['OP_CODE'];
}

if (isset($_GET['EMULATOR_IMSI'])) {
    $IMSI = $_GET['EMULATOR_IMSI'];
}


$SESSION_ID = $_GET['SESSIONID'];
$OTHER_PARAMS = array();






$response = new GenericInterface($MSISDN, $SESSION_STATE, $ACCESSPOINT, $INPUT, ORIGIN, $OTHER_PARAMS, $SESSION_ID, $IMSI);
$jsonmenuresponse = $response->processRequest();
echo $jsonmenuresponse;
?>
