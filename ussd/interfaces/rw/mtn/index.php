<?php

/**
 * 
 * MTN Rwanda USSD Interface
 * 
 * @author Maritim, Kip <kiprotich.maritim@gmail.com>
 * @copyright (c) 2017, Kwigira
 * 
 */ 


//USSD Interface manager
include dirname(__FILE__) . '/../../USSDInterface.php';

$SESSION_ID = NULL;
$IMSI = NULL;

Const ORIGIN = 'ACCESSRW';

class GenericInterface extends USSDInterface {

    public function __construct($MSISDN, $SESSION_STATE, $ACCESSPOINT, $INPUT, $ORIGIN, $OTHER_PARAMS, $SESSION_ID, $IMSI) {

        $NETID = ORIGIN;
        if (isset($_GET['NETCODE']) and 0 + $_GET['NETCODE'] > 0) {
            $NETID = 0 + $_GET['NETCODE'];
        }
        parent::__construct($MSISDN, $SESSION_STATE, $ACCESSPOINT, $INPUT, $NETID, $OTHER_PARAMS, $SESSION_ID, $IMSI);
    }

}

if (!isset($_GET['MSISDN'])) {
     $response = array(
        'responseCode' => 141,
        'responseDescription' => 'MSISDN is not set, check API documentation',
        'metadata' => array(),
    );
    echo json_encode($response);
    die;     
}//if
$MSISDN = $_GET['MSISDN'];

if (!isset($_GET['SERVICE_CODE'])) {
    $response = array(
        'responseCode' => 141,
        'responseDescription' => 'Service Code is not set, check API documentation',
        'metadata' => array(),
    );
    echo json_encode($response);
    die;         
}//if
$ACCESSPOINT = $_GET['SERVICE_CODE'];

if (!isset($_GET['INPUT_STRING'])) {
    $response = array(
        'responseCode' => 141,
        'responseDescription' => 'INPUT String is not set, check API documentation',
        'metadata' => array(),
    );
    echo json_encode($response);
    die;             
}//if
$INPUT = $_GET['INPUT_STRING'];

//print_r($_GET); die;
if (!isset($_GET['SESSIONID']) || !isset($_GET['SESSIONID'])) {
    $response = array(
        'responseCode' => 141,
        'responseDescription' => 'SessionID is not set, check API documentation',
        'metadata' => array(),
    );
    echo json_encode($response);
    die;             
}//if
$SESSION_ID = isset($_GET['SESSION_ID']) ? $_GET['SESSION_ID'] : $_GET['SESSIONID'];


$SESSION_STATE = "CONT";
if (isset($_GET['OP_CODE'])) {
    $SESSION_STATE = $_GET['OP_CODE'];
}

if (isset($_GET['IMSI'])) {
    $IMSI = $_GET['IMSI'];
}

$OTHER_PARAMS = array();


//Initialize
$response = new GenericInterface($MSISDN, $SESSION_STATE, $ACCESSPOINT, $INPUT, ORIGIN, $OTHER_PARAMS, $SESSION_ID, $IMSI);

//Process and return Response
$jsonmenuresponse = $response->processRequest();
echo $jsonmenuresponse;

