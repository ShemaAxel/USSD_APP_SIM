<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

define('LOG_DIRECTORY', '/var/log/applications/rw/kwigira/ussd/');
define('SYSTEM_LOG_LEVEL', 10);
define('NL', "\n");
define('TB', "");
define('USSD_LOG_PROPERTIES', dirname(__FILE__) . '/ussd_logs.php');
define('USSD_CERT_PATH', '');
define('SSL_ENGINE', true);

// Various HTTP Error response messages for the Dynamic Menu Controller class

define('TIMEOUT_DURATION', 5); //response timeout duration in sec
define('CONNECT_TIMEOUT', 0); //connect duration timeout.


//Various friendly error messages for different situations. used within the USSDRouter Class
define('ERROR_MESSAGE_HTTP_404', "USSDRouter:404 err-Dear Customer, we are experiencing technical difficulties. Please try again later");
define('ERROR_MESSAGE_HTTP_RESPONSE_TIMEOUT', "USSDRouter:Timeout err- Dear Customer, we are experiencing technical difficulties. Please try again later");
define('ERROR_LOG_TO_USSD_ACTIVITIES', "USSDRouter:Insert activites err-Dear Customer, we are experiencing technical difficulties. Please try again later");
define('ERROR_FETCH_MENU_SYSTEM_URL', "USSDRouter:Fetch route URL err-Dear Customer, we are experiencing technical difficulties. Please try again later");
define('ERROR_UNKNOWN_HTTP_ERROR', "USSDRouter:Unknown HTTP err -Dear Customer, we are experiencing technical difficulties. Please try again later");
define('ERROR_MESSAGE_HTTP_500', "USSDRouter:Internal server error -Dear Customer, we are experiencing technical difficulties. Please try again later");
  
    
// The time in minutes to which a session should last.
define('SESSION_EXPIRY_DURATION', 5);

//client function types routing configs 
define('EXTERNAL_MENU_CLIENT_FUNCTION_TYPE_ID',9);
define('INTERNAL_MENU_CLIENT_FUNCTION_TYPE_ID',2);


// These contants are used by the activity class
define('GATEWAYID', 3);
define('GATEWAYUID', 0);
define('PHP_POST_PROTOCOL_ID', 1);
define('XML_RPC_PROTOCOL_ID', 3);
define('IMCID', 1);

// Various status codes used by ussd
define('SUCCESS_UPDATE_STATUS', 1);
define('TIME_OUT_ERROR_STATUS', 11);
define('ROUTE_NOT_FOUND_STATUS', 6);
define('INTERNAL_SERVER_ERROR_STATUS', 7);
define('UNKNOWN_ERROR_STATUS', 0);
?>
