<?php

// ------------------------------------
session_name('MOBILE_EMULATOR');
session_start();

$hubServer = "localhost";
$hubURL = "http://$hubServer/samsung-simulator";
//$hubURL = "http://$hubServer:11007";
$emulatorUrl = "http://localhost";


define("sql_host", "localhost");
define("sql_user", "root");
define("sql_password", "r00t");
define("sql_db", "samsung_emulator");
define("log_path",'/var/log/applications/logs/');
define("fatalLogs", "/var/log/applications/logs/fatal.log");
define("sqlLogs", "/var/log/applications/logs/sequel.log");
define("mnoLogs", "/var/log/applications/logs/mno.log");
define("infoLogs", "/var/log/applications/logs/info.log");

define("hub_SMS_URL", $hubURL."/SMS/web/gatewayInterfaces/EMGInterface.php");
define("hub_DLR_URL", $hubURL."/SMS/web/APIs/DLRLogger.php");
//define("hub_USSD_URL", $hubURL."/ussd/interfaces/emulatorInterface.php"); //http://KE-Hub4-MNO-Apps:9011/hub/smsInterfaces/gatewayInterfaces/EMGInterface.php
define("hub_USSD_URL", $hubURL."/ussd/interfaces/rw/mtn/index.php");
define("simulator_URL", $emulatorUrl.":8000/ussd/samsung-simulator");
define("MoMo_safURL", $emulatorUrl."/MomoSafaricomInterface.php");
define("MoMo_airtelURL", $emulatorUrl."/MomoAirtelInterface.php");

//default countryID - the primary key of the value in countries table
define('country_id', 117);

define("b2c_service_id", 2);
define('ssl_engine', false); //process requests via SSL config
define('ssl_cert_path', '/etc/pki/tls/certs/COMODO-ADMIN-UI/comodo-cellulantke.crt');
define('verify_peer', false);

require_once '_coreCode.php';
require_once 'sajax/sajax.php';
//require_once($_SERVER['DOCUMENT_ROOT'] . "/phoneEmulator/encryption_classes/dynamicConfigs.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/ussdApps/encryptClasses/encryptPin.php");

/*

  $sajax_request_type = "POST";
  sajax_init();

  //sajax_export("home_page", "open_browser", "start_call", "make_call", "list_messages", "view_message", "ussd_reply", "ussd_clear", "log_out");
  sajax_handle_client_request();

 */

$sajax_debug_mode = true;
$sajax_failure_redirect = "error.html";
sajax_export(
	array( "name" => "profile_page", "method" => "POST" ),
	array( "name" => "message_new_page", "method" => "POST" ),
	array( "name" => "message_read_page", "method" => "POST" ),
	array( "name" => "messaging_home_page", "method" => "POST" ),
	array( "name" => "browser_page", "method" => "POST" ),
	array( "name" => "get_time", "method" => "POST" ),
	array( "name" => "ussd_reply", "method" => "POST" ),
	array( "name" => "ussd_clear", "method" => "POST" ),
	array( "name" => "dial_page", "method" => "POST" ),
	array( "name" => "send_sms", "method" => "POST" ),
	array( "name" => "calllog_page", "method" => "POST" ),
	array( "name" => "make_call", "method" => "POST" ),
	array( "name" => "home_page", "method" => "POST" ),
	array( "name" => "ussd_do_post_BillRequest", "method" => "POST" ),
	array( "name" => "pay_bill", "method" => "POST" ),
	array( "name" => "newmenus", "method" => "POST" ), //show the new menus
	array( "name" => "topup", "method" => "POST" ),
	array( "name" => "airtime_request", "method" => "POST" ),
	array( "name" => "b2ctransfer", "method" => "POST" ),
	array( "name" => "b2cRequest", "method" => "POST" ),
	array( "name" => "checkBalance", "method" => "POST" ),
	array( "name" => "postBillRequest", "method" => "POST" )
);

sajax_handle_client_request();
/*
 * sajax now supports returning an array and using js to access the objects and manipulate them.
 */


