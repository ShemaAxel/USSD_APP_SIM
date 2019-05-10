<?php 

/**
 * MTN Rwanda | Flares 
 * 
 * @author Maritim, Kip <kiprotich.maritim@gmail.com>
 * @copyright 2017 
 * 
 */

//Manage the composer dependencies
require __DIR__ . '/../../vendor/autoload.php';

//Generic interface
include 'GenericInterface.php';

//Monolog
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

//Setup a new instance of the application logger
$log = new Logger('kwigira|ussd');
//Set the handlers
$log->pushHandler(new StreamHandler('/var/log/applications/kwigira/channels/ussd/interfaces/mtn_rw_info.log', Logger::INFO));
$log->pushHandler(new StreamHandler('/var/log/applications/kwigira/channels/ussd/interfaces/mtn_rw_error.log', Logger::ERROR));
$log->pushHandler(new StreamHandler('/var/log/applications/kwigira/channels/ussd/interfaces/mtn_rw_warning.log', Logger::WARNING));

//Global parameters

/**
 * @var $SessionID
 */
$sessionID = NULL;

/**
 * @var NewRequest
 */
$newRequest = NULL;


/**
 * @var $MSISDN Subscriber phone number
 */
$MSISDN = NULL;

/**
 * @var $input
 */
$input = NULL;

try {
    $log->info('Started an instance of USSD interface. Payload Params : ' . json_encode($_GET));
    //userID
    $sessionID = (string) filter_input(INPUT_GET, 'SessionID');
    //newRequest
    $newRequest = (string) filter_input(INPUT_GET, 'NewRequest');
    //Number
    $MSISDN = (string) filter_input(INPUT_GET, 'MSISDN');    
    //input
    $input = (string) filter_input(INPUT_GET, 'Input');
    
    $interface = new GenericInterface($MSISDN,$input,$sessionID,$newRequest);
    $menuResponse = $interface->processRequest();
    $log->info('JSON Response from Menu systems: ' . $menuResponse);
    //Get the response from the menu systems
    $response = json_decode($menuResponse);
    $responseString = $response->RESPONSE_STRING;
     
    //Is the session continuing?
    $freeFlow = 'FB';
    if(isset($response->SESSION_STATE) && $response->SESSION_STATE == 'CONTINUE') {
        $freeFlow = 'FC';
    }
    
    
    //$response = 'Welcome to Kwigira, We are launching in a few days, we will keep you posted';
    
    //Just return a generic response for now
    header('Freeflow: '.$freeFlow);
    header('Charge: N');
    header('Content-Type: UTF-8');
    header('Content-Length: ' . strlen($responseString));
    //header('Response Message: ' . $response);
    echo $responseString;
    
} catch (Exception $ex) {
    $log->err('An application exception occured while processing the request. Exception Details: ' . $ex->getMessage());
}
