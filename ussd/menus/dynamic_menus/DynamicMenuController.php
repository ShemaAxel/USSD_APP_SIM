<?php

include_once '../bootstrap.php';
/* for IDE functionality only 
require_once './log4php/Logger.php';
require_once './configs/UssdConfigs.php';
require_once './configs/dbConfigs.php';
require_once './profiling/Profiler.php';
require_once './utils/DBUtils.php';
require_once './utils/CoreUtils.php';
require_once './utils/IXR_Library.php';
require_once './security/Encryption.php';
/**
 * The dynamic menu handler 
 * This class handles session management and navigation properties of the 
 * dynamic menus. Super class of all dynamic menus. Various functions and 
 * attributes will be accessed as parent::<attribute> by respective menu classes.
 * 
 * @author cellulant-pd pd@cellulant.com
 * @since "1"
 *
 */
class DynamicMenuController {

    /**
     * assist classes
     */
    public $dbUtils;

    /**
     * activities attributes useful to this processor.
     */
    public $_activityID;
    public $_clientSystemID;
    public $_msisdn;
    public $_accessPoint;
    public $_gatewayID;
    public $_gatewayUID;
    public $_requestPayload;
    public $_connectorID;
    public $_networkID;
    public $_sessionID;
    public $_IMCID;
    public $_clientACKID;
    public $_externalSystemServiceID;
    public $_serviceDescription;
    public $_expiryDate;
    public $_responsePayload;
    public $_input;
    public $logparams; //array holding parameters to log.
    public $_clientSystem_ExpiryPeriod;
    public $_clientID;
//todo: replace this with channelRequestID and chnnelResponseID; verify where it has been used correctly
    /**
     * Use this ID for channelRequests update
     */
    public $updateChannelSystemID;

    /**
     * navigation values
     */
    public $previousPage;
    public $nextFunction;
    public $displayText = array();
    public $sessionState;
    public $profiler;
    public $clientProfile;
    
    // WILL STORE THE IMSI
    public $IMSI;

    /**
     * Class constructor . Will initialize navigation module and the 
     * DB utils module alongside initialization of processing.
     */
    function __construct() {
        $this->init();
        CoreUtils::flog4php(4, $this->_msisdn, array("MESSAGE" => "|===== new menu request for $this->_msisdn ======"), __FILE__, __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);

        $this->dbUtils = new DbUtils();
        $this->logparams = array();

        //Initialize all variables via post
        //if (isset($_POST['activityID'])) {
        //    $this->init();
        //}
        CoreUtils::flog4php(4, $this->_msisdn, array("MESSAGE" => "|About to fetch yyyyyyyyyyyyy $this->_msisdn Profile details from the profiler:" . json_encode($this->clientProfile, JSON_PRETTY_PRINT)), __FILE__, __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);
        //initialize profiler and retrieve profile data
        $this->profiler = NULL; //new Profiler($this->dbUtils, $this->_msisdn, $this->_clientSystemID, $this->_networkID);
        $this->clientProfile = 1;//$this->profiler->getClientProfile();
        $this->transactingAccounts = NULL;
        $this->_clientID = 1;//$this->profiler->clientID;
        
        //load this session or create it since msisdn and sessionID are set.
        $this->loadSession($this->_msisdn, $this->_sessionID);

        //get all navigation values from session
        $this->previousPage = $this->getSessionVar('previousPage');
        $this->nextFunction = $this->getSessionVar('nextFunction');
    }

    /**
     * Obtains POST variable, Initializes respective values within this class
     * with what is obtained via POST.
     * @return void
     */
    private function init() {

        //initialize all POST attributes
        $this->_activityID = $_POST['activityID'];
        $this->_clientSystemID = $_POST['clientSystemID'];
        $this->_msisdn = $_POST['MSISDN'];
        $this->_accessPoint = $_POST['accessPoint'];
        $this->_input = base64_decode($_POST['input']);
        $this->_gatewayID = isset($_POST['gatewayID']) ? $_POST['gatewayID'] : "";
        $this->_gatewayUID = isset($_POST['gatewayUID']) ? $_POST['gatewayUID'] : "";
        $this->_requestPayload = $_POST['requestPayload'];
        $this->_connectorID = isset($_POST['connectorID']) ? $_POST['connectorID'] : "";
        $this->_networkID = $_POST['networkID'];
        $this->_sessionID = $_POST['sessionID'];
        $this->_sessionState = isset($_POST['sessionState']) ? $_POST['sessionState'] : "";
        $this->_IMCID = $_POST['IMCID'];
        $this->_clientSystem_ExpiryPeriod = $_POST['expiryPeriod'];
        $this->_clientACKID = isset($_POST['clientACKID']) ? $_POST['clientACKID'] : "";
        $this->_externalSystemServiceID = isset($_POST['externalSystemServiceID']) ? $_POST['externalSystemServiceID'] : "";
        $this->_serviceDescription = isset($_POST['serviceDescription']) ? $_POST['serviceDescription'] : "";
        $this->_expiryDate = isset($_POST['expiryDate']) ? $_POST['expiryDate'] : "";
        $this->_responsePayload = isset($_POST['responsePayload']) ? $_POST['responsePayload'] : "";
        // EXTRACT THE IMSI FROM THE REQUEST PAYLOAD
        $this->IMSI = isset($_POST['IMSI']) ? $_POST['IMSI'] : "";        
        CoreUtils::flog4php(4, $this->_msisdn, array("MESSAGE" => "|initialized all POST data . Post data is :" . json_encode($_POST, JSON_FORCE_OBJECT)), __FILE__, __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);
    }
    
    
    public function setIMSI($newIMSI) {
        $this->IMSI = $newIMSI;
        
    }
    
    public function getIMSI() {
        return $this->IMSI;        
    }
    

    /**
     * To re-load a session or newly create one. In this case it uses the mobile
     * number and the accessPoint
     * @param string $msisdn 
     * @param string $sessionID
     * @return void
     */
    public function loadSession($msisdn, $sessionID) {
        //msisdn and sessionID concatenated will be the id of this session 
        $concatsessionname = $msisdn . ' ' . $sessionID;
        //load session / create 
        $sessionID = md5($concatsessionname);
        session_id($sessionID);
        @session_start();
        if (!isset($_SESSION['FIRST_DIAL_MINUTE'])) {
            $_SESSION['FIRST_DIAL_MINUTE'] = date('i');
        }
        if (isset($_SESSION['FIRST_DIAL_MINUTE']) && (date('i') - $_SESSION['FIRST_DIAL_MINUTE'] >= SESSION_EXPIRY_DURATION)) {
            CoreUtils::flog4php(4, $this->_msisdn, array("MESSAGE" => "|Session timedout hence destroyed after :" . SESSION_EXPIRY_DURATION . " minutes"), __FILE__, __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);
            $this->destroySession();
        }
    }

    /**
     * Save navigation data and destroy session if necessary.
     * @return string JSON response.
     */
    public function finalizeProcessing() {
        //save navigation values within session first
        $this->saveSessionVar('nextFunction', $this->nextFunction);
        $this->saveSessionVar('previousPage', $this->previousPage);
        CoreUtils::flog4php(4, $this->_msisdn, array("MESSAGE" => "|Preparing to send response back to router. Next function will be:" . $this->nextFunction), __FILE__, __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);

        //we destory this session since end has been called.
        if ($this->sessionState == "END") {
            $this->destroySession();
        }
        $responsearray = $this->formatResponse();
        CoreUtils::flog4php(4, $this->_msisdn, array("MESSAGE" => "|=======Finalized processing this request. Responding back to the USSD router with the following data=====" . json_encode($responsearray, JSON_FORCE_OBJECT)), __FILE__, __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);

        return json_encode($responsearray, JSON_FORCE_OBJECT);
    }

    /**
     * Force session destroy during timeout or 
     * SESSIONSTATE forced
     */
    public function destroySession() {
        CoreUtils::flog4php(4, $this->_msisdn, array("MESSAGE" => "|Session state is END or timeout occured. Destroying this session"), __FILE__, __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);
        @session_unset();
        @session_destroy();
    }

    /**
     * Prepares JSON string to send back to routing component.
     * @return type
     */
    public function formatResponse() {
        //formulate response payload.
        $responsearray = array();
        $responsearray['RESPONSE_DESCRIPTION'] = $this->_serviceDescription;
        $responsearray['RESPONSE_STRING'] = '';
        foreach($this->displayText as $key) {
            $responsearray['RESPONSE_STRING'] .= $key . "\n";
        }
        //$responsearray['PAGE_STRING'] = $this->displayText;
        $responsearray['SESSION_STATE'] = $this->sessionState;
        $responsearray['SESSION_ID'] = $this->_sessionID;
        $responsearray['MSISDN'] = $this->_msisdn;
        //$responsearray['EXTERNAL_SYSTEM_SERVICEID'] = $this->_externalSystemServiceID;
        //$responsearray['IMSI'] = $this->IMSI;

        return $responsearray;
    }

//todo: function pre-navigate()
    /**
     * Function invoked before manu navigation
     * return false so as to exit subsequent navigation behaviour.
     */
    public function pre_navigate() {
        return true;
    }

//todo: function post-navigate()
    /**
     * Function invoked before manu navigation
     */
    public function post_navigate() {
        // no return function, cannot affect navigator behaviour
    }

    /**
     * Validates which menu function to call. Either the startPage or other pages
     * depending on  the navigation variables
     * @return string json string for current response
     */
    public function navigate() {

        if ($this->pre_navigate()) {
            // reset function to call so that we know when user has changed it
            $functionToCall = $this->nextFunction;
            $this->nextFunction = '';
            $this->sessionState = 'END';

            //trim input to remove trailing spaces
            $this->_input = trim($this->_input);

            if ($functionToCall == '' || $functionToCall == null) { //if no next function defined, use startPage()
                CoreUtils::flog4php(4, $this->_msisdn, array("MESSAGE" => "|Start page call"), __FILE__, __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);

                $this->startPage($this->_input);
            } else {
                $returnresult = call_user_func(array($this, $functionToCall), $this->_input);
                CoreUtils::flog4php(4, $this->_msisdn, array("MESSAGE" => "|$functionToCall Input : $this->_input return result from custom function" . json_encode($returnresult)), __FILE__, __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);
            }
        }
        
        $this->post_navigate();

        if ($this->sessionState == 'CONTINUE' && $this->nextFunction == '') {
            $this->nextFunction = $functionToCall;
        }
        if ($this->nextFunction != '') {
            $this->sessionState = 'CONTINUE';
        }
        return $this->finalizeProcessing();
    }

    /**
     * Used to connect to alternate experience database
     * @param string $dbhost
     * @param string $dbUser
     * @param string $dbPassword
     * @param string $dbSchema
     * @return boolean db available
     */
    public function getDBConnection($dbhost, $dbUser, $dbPassword, $dbSchema) {
        $connect = mysql_connect("$dbhost", "$dbUser", "$dbPassword") or CoreUtils::flog4php(4, $this->_msisdn, array("MESSAGE" => "Service cannot be processsed at the moment-DB connection error" . mysql_error($connect)), __FILE__, __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);

        return mysql_select_db($dbSchema, $connect) or CoreUtils::flog4php(4, $this->_msisdn, array("MESSAGE" => "cannot connect " . mysql_error($connect)), __FILE__, __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);
    }

    /**
     * Acts as a stub if startPage is not set within the menu
     * @param text $input
     */
    public function startPage($input) {
        $this->displayText[] = "Your USSD EXperience does not have a start page defined\n Please refer to developer manual";
    }

    /**
     * Error Page for Dynamic Menu Controller
     * @param const $error_code
     * @param text $functionName
     * @param text $input
     */
    public function errorPage($error_code, $functionName, $input) {
        switch ($error_code) {
            case '404':
                $this->displayText[] = "Your USSD EXperience does not have the function $functionName defined\n Please refer to developer manual";
                break;
            default:
                $this->displayText[] = "Your USSD EXperience encountered an error processing the function $functionName\n Please refer to developer manual";
                break;
        }
    }
    
    protected function syncClientProfile($currentIMSI)
    {
	$currentIMSI = md5($this->_msisdn);
        //todo: complete this function. (use public variables)
	$data = $this->profiler->syncClientProfile(1,0,$currentIMSI);
	return $data;
    }
    
    protected function validateIMSI($currentIMSI) {
         
       return  $this->profiler->validateIMSI($currentIMSI); 
    }
    
    protected function checkIMSIConsent() {
        
        return $this->profiler->checkIMSIConsent();
    }
    
    protected function saveIMSIConsent($consentValue) {
        
        return $this->profiler->saveIMSIConsent($consentValue);
    }
    
    protected function updateIMSI($currentIMSI, $active)
    {
         //todo: complete this function. (use public variables)
        $this->profiler->updateIMSI($currentIMSI, $active); 
    }

    /**
     * Specific menu file log function. 
     * 
     * @param int $logLevel
     * @param string $message
     * @param string $this_menu_script
     * @param fn $this_function
     * @param fn $this_lineNo
     */
    public function flog($logLevel, $logString, $this_menu_script, $function = null, $lineNo = null) {
        //$logLevel, $logString = NULL, $fileName = NULL, $function = NULL, $lineNo = NULL
        // prepend sessionID and MSISDN 
        // force log File Name as agreed upon, unique per accesspoing-protocol-mapping
        $SYSTEM_LOG_LEVEL = (defined('SYSTEM_LOG_LEVEL')) ? SYSTEM_LOG_LEVEL : 10;

        $logDirectory = LOG_DIRECTORY . 'menuLogs/';
        $file = $logDirectory . "" . $this_menu_script;
        $date = date("Y-m-d H:i:s");
        $logType = null;
        $logType[0] = 'CRITICAL';
        $logType[1] = 'FATAL';
        $logType[2] = 'ERROR';
        $logType[3] = 'WARNING';
        $logType[4] = 'INFO';
        $logType[5] = 'SEQUEL';
        $logType[6] = 'TRACE';
        $logType[7] = 'DEBUG';
        $logType[8] = 'CUSTOM';
        $logType[9] = 'UNDEFINED';
        $logTitle = 'UNDEFINED';

        // covert ID to file Name
        if (!is_int($logLevel)) {// level is a string convert back to int and overide the default file
            if (strtolower(substr($logLevel, (strlen($logLevel) - 4), 4)) == '.log' or strtolower(substr($logLevel, (strlen($logLevel) - 4), 4)) == '.txt') {// overide the current paths {{faster than changing all scripts with custom paths}}
                $file = $logDirectory . basename($logLevel);
            } else {// file does not have the correct extension.
                $file = $logDirectory . basename($logLevel) . '.log';
            }

            $logLevel = 8;
        } else {
            if (isset($logType[$logLevel])) {
                // overide the current paths {{faster than changing all scripts with custom paths}}
                $file = $logDirectory . basename($logType[$logLevel]) . ".log";
            } else {
                $logLevel = 9;
            }
        }

        $logTitle = $logType[$logLevel];

        if ($fileName == NULL)
            $fileName = $_SERVER['PHP_SELF'];

        // should be <= $DEBUG_LEVEL
        if ($logLevel <= $SYSTEM_LOG_LEVEL) {
            if ($fo = fopen($file, 'ab')) {
                fwrite($fo, "$date -[$logTitle] $fileName:$lineNo $function $logString\n");
                fclose($fo);
            } else {
                trigger_error("flog Cannot log '$logString' to file '$file' ", E_USER_WARNING);
            }
        }
    }

    /**
     * 
     * Retrieves defined payload value for specific attribute key. 
     * Returns both explicit and implicit attributes.
     * Can be many attributes values for one key. Formats data for menu use
     * Explict flag = 1
     * implicit flag = 0
     * @param int $isExplicit
     * @param string $attributeKey
     * @return array $attributesarrayFormatted
     */
    public function getAttribute($isExplicit, $attributeKey) {
        //run query
        CoreUtils::flog4php(4, $this->_msisdn, array("MESSAGE" => "fetching attributes using $isExplicit and $attributeKey"), __FILE__, __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);
        $attributearray = $this->profiler->fetchAttributes($isExplicit, $attributeKey);

        $attributesarrayFormatted = isset($attributearray['DATA']['RESULTS']) ? $attributearray['DATA']['RESULTS'] : "";

        CoreUtils::flog4php(4, $this->_msisdn, array("MESSAGE" => "array is  " . json_encode($attributesarrayFormatted, JSON_FORCE_OBJECT)), __FILE__, __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);

        return $attributesarrayFormatted;
    }

    /**
     * 
     * Retrieves defined payload value for specific attribute key. 
     * Returns both explicit and implicit attributes.
     * Can be many attributes values for one key. Formats data for menu use
     * Explict flag = 1
     * implicit flag = 0
     * @param int $isExplicit
     * @param string $attributeKey
     * @return array $attributesarrayFormatted
     */
    public function getAccountAttributes($accountType, $clientProfileID) {
        //run query
        CoreUtils::flog4php(4, $this->_msisdn, array("MESSAGE" => "fetching account attributes using $accountType and $clientProfileID"), __FILE__, __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);
        $attributearray = $this->profiler->fetchAccountAttributes($accountType, $clientProfileID);

        $attributesarrayFormatted = isset($attributearray['DATA']['RESULTS']) ? $attributearray['DATA']['RESULTS'] : "";

        CoreUtils::flog4php(4, $this->_msisdn, array("MESSAGE" => "array is  " . json_encode($attributesarrayFormatted, JSON_FORCE_OBJECT)), __FILE__, __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);

        return $attributesarrayFormatted;
    }

    /**
     * @param string $attributeKey
     * @param string $attributeValue
     * @param int $isExplicit
     * @return boolean
     */
    public function setAttribute($attributeKey, $attributeValue, $isExplicit) {
        $processresult = $this->profiler->saveAttribute($attributeKey, $attributeValue, $isExplicit);
        return $processresult['SUCCESS'];
    }

    /**
     * This function saves the session variable in the php session file
     * @param type $sessionkey
     * @param type $sessionvalue 
     * @return void
     */
    public function saveSessionVar($sessionkey, $sessionvalue) {
        $_SESSION[$sessionkey] = $sessionvalue;
    }

    /**
     * Returns a value from session
     * @param string $sessionkey
     * @return type 
     */
    public function getSessionVar($sessionkey = null) {
        try {
            $sessionvalue = isset($_SESSION[$sessionkey]) ? $_SESSION[$sessionkey] : "";
            return $sessionvalue;
        } catch (Exception $e) {
            $this->logparams['message'] = "Experienced error while obtaining session value:" . $e;
            CoreUtils::flog4php(3, $this->_msisdn, $this->logparams, __FILE__, __FUNCTION__, __LINE__, "ussderror", USSD_LOG_PROPERTIES);
        }
    }

    /**
     * Pin encryption function. Used mainly by mobile banking menus.
     * @param int $input
     * @param int $uniqueID
     */
    public function encryptPin($input, $uniqueID) {
        $encryption = new Encryption();
        $encryptedInput = $encryption->encryptPin($input, $uniqueID);
        return $encryptedInput;
    }

    public function decryptPin($input) {
        $decryption = new Encryption();
        $decryptedInput = $decryption->decryptPin($input);
        return $decryptedInput;
    }

    /**
     * makes calls to functions that invoke USSD dependent systems, update requests after receiving a response 
     * and returns response to menu.
     * @param string $clientProtocalID
     * @param array $arraypayload
     * @return string 
     */
    public function processSync($clientProtocolID, $arraypayload) {

        $payload = json_encode($arraypayload, JSON_FORCE_OBJECT); //json encode payload
        //get URL using clientProtocalID
        $externalUrl = $this->getSystemURL($clientProtocolID);
        $message = "";
        if ($externalUrl['SUCCESS'] == TRUE) {

            $responseArray = $this->sendExternalRequest($payload, $externalUrl['DATA']); //message from external system

            switch ($responseArray['DATA']['HTTP_STATUS']) {
                case 200: //success
                    CoreUtils::flog4php(4, $this->_msisdn, array("MESSAGE" => "|HTTP STATUS 200. Successful system calls"), __FILE__, __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);
                    $this->updateChannelRequests($this->updateChannelSystemID, 1);
                    $message = $responseArray['MESSAGE'];
                    $this->logChannelResponses($this->updateChannelSystemID, $message, 1);
                    break;
                case 0: //read response timeout error
                    $this->updateChannelRequests($this->updateChannelSystemID, 11);
                    $message = LOG_RESPONSE_TIME_OUT;
                    CoreUtils::flog4php(4, $this->_msisdn, array("MESSAGE" => "Experienced read timeout error"), __FILE__, __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);
                    break;

                case 404: //connect timeout error
                    $this->updateChannelRequests($this->updateChannelSystemID, 6);
                    $message = CONNECT_TIMEOUT;
                    CoreUtils::flog4php(4, $this->_msisdn, array("MESSAGE" => "Experienced 404 HTTP error code"), __FILE__, __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);
                    break;
                default:
                    $message = UNKNOWN_HTTP_ERR_MESSAGE;
                    $this->updateChannelRequests($this->updateChannelSystemID, 5);
                    CoreUtils::flog4php(4, $this->_msisdn, array("MESSAGE" => "Experienced Unknown http Error code" . $responseArray['DATA']['HTTP_STATUS']), __FILE__, __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);
            }
        } else {

            $this->updateChannelRequests($this->updateChannelSystemID, 42);
            $message = FETCH_SYSTEM_URL_ERR_MESSAGE;
            CoreUtils::flog4php(4, $this->_msisdn, array("MESSAGE" => "|URL fetch failed. Updating channelRequests to status 42"), __FILE__, __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);
        }

        return $message;
    }
    
    /**
     * This function is used to block the IMSI of a customer when the IMSI they dial with
     * does not match the IMSI stored in the database
     * 
     * @param type $active
     * @param type $clientProfileID
     * @param type $keyID
     * 
     * 
     */
    public function updateIMSIStatus($active, $clientProfileID, $keyID) {
        
        CoreUtils::flog4php(4, $this->msisdn, array("MESSAGE" => "DynamicMenuController | updateIMSIStatus | params ($active, $clientProfileID, $keyID) | fffffffffffffffffffffffffffffffffffffffffffffffffff"), __FILE__, __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);
        $response = $this->profiler->updateIMSIStatus($active, $clientProfileID, $keyID);
        CoreUtils::flog4php(4, $this->msisdn, array("MESSAGE" => "DynamicMenuController | updateIMSIStatus | after invoke profiler updateIMSIStatus() | RESPONSE >>> " . print_r($response, true)), __FILE__, __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);
        
        $STATUS_IMSI_BLOCKED = 13;
        
        $payload = array();
        $payload[] = array('OLDIMSI' => "", 
                                         'NEWIMSI' => "",
                                         'DATECREATED' => "",
                                         'DATESIMCHANGED' => "",
                                         'DATEMODIFIED' => date('jS m, Y'),
                                         'COMMENT' => 'IMSI Updated');
        //$payload = json_encode($payload);
        
        $this->logChannelRequest($payload, $STATUS_IMSI_BLOCKED);
        
    }

    /**
     * Logs requested payload within channelRequests table before fowarding to 
     * external systems
     * @param type $params
     * @param array $payload
     * @return type
     */
    public function logChannelRequest($arraypayload, $status, $clientSystemID = NULL) {

        $payload = json_encode($arraypayload, JSON_FORCE_OBJECT);

        if ($clientSystemID != NULL) {
            $this->_clientSystemID = $clientSystemID;
        }

        $query = "INSERT INTO c_channelRequests ("
              . " gatewayID, gatewayUID, MSISDN, accessPoint, " 
              . " activityID, payload, networkID, IMCID, "
              . " clientSystemID, externalSystemServiceID, overalStatus, appID,"
              . " expiryDate, nextSend, firstSend, lastSend, "
              . " dateCreated, connectorID)"
              . " VALUES "
              . "(?, ?, ?, ?, " 
              . "?, ?, ?, ?, "
              . "?, ?, ?, ?, "
              . "adddate(NOW(), INTERVAL $this->_clientSystem_ExpiryPeriod  minute), now(), now(), now(),"
              . "now(), ?)";
        
        $insertparams = array(
            GATEWAYID, $this->_sessionID, $this->_msisdn, $this->_accessPoint,
            $this->_activityID, $payload, $this->_networkID, $this->_IMCID,
            $this->_clientSystemID, 0, $status, DMC_APPID, 
            1);
        
        $debugQuery = 
                " INSERT INTO c_channelRequests (gatewayID, gatewayUID,MSISDN, accessPoint, 
            activityID, payload, networkID, IMCID, clientSystemID,externalSystemServiceID,
            overalStatus,appID,expiryDate,nextSend,firstSend,lastSend, dateCreated, connectorID)
            VALUES (". GATEWAYID . "," 
                . $this->_sessionID . ","  
                . $this->_msisdn . "," 
                . $this->_accessPoint . ", "  
                . $this->_activityID . ", '"  
                . $payload . "', "
                . $this->_networkID . ", "
                . $this->_IMCID . ", "
                . $this->_clientSystemID  . ", "
                . $this->_externalSystemServiceID . ", "
                . $status . ", "
                . DMC_APPID 
                . ", adddate(NOW(), INTERVAL $this->_clientSystem_ExpiryPeriod  minute),now(),now(),now(),now(), 1)";

        CoreUtils::flog4php(4, $this->_msisdn, 
                array("MESSAGE" => "| logChannelRequest() | QUERY iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii>>> $debugQuery <<< " ), __FILE__, __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);
        
        $result = $this->dbUtils->execute($query, $insertparams);
        
        CoreUtils::flog4php(4, $this->_msisdn, 
                array("MESSAGE" => "| logChannelRequest() | QUERY >>> " . print_r($result, true) ), __FILE__, __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);

        $request_logstatus = array();
        if (!$result['SUCCESS'] && $result['DATA']['LAST_INSERT_ID']) {
            $request_logstatus['SUCCESS'] = FALSE;
            $request_logstatus['DATA'] = $result['DATA'];
            $request_logstatus['MESSAGE'] = 'Failed to log request into channelRequests table.';
            CoreUtils::flog4php(4, $this->_msisdn, array("MESSAGE" => "Failed to log into channel requests table. Reason:::::" . $result['DATA']), __FILE__, __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);
        } else {
            $request_logstatus['SUCCESS'] = TRUE;
            $request_logstatus['DATA'] = $result['DATA'];
            $request_logstatus['MESSAGE'] = 'Request successfully logged into channelRequests table.';

            CoreUtils::flog4php(4, $this->_msisdn, array("MESSAGE" => "Successfully logged into channel requests table. Reason:"), __FILE__, __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);
            //ID for update
            $this->updateChannelSystemID = $result['DATA']['LAST_INSERT_ID'];
        }

        return $request_logstatus;
    }

    /**
     * Update channel requests table for async processing after sync failure
     * or update status to successful synchronous processing.
     * @param int $clientSystemID
     * @param int $statusCode
     * @return boolean array 
     */
    public function updateChannelRequests($channelRequestID, $statusCode) {

        $query = "UPDATE c_channelRequests SET overalStatus = ? WHERE channelRequestID = ?";

        $response = $this->dbUtils->execute($query, array($statusCode, $channelRequestID));

        $updatereturn = array();
        if (!$response['DATA']) {
            $updatereturn['SUCCESS'] = FALSE;
            $updatereturn['DATA'] = $response;
            $updatereturn['MESSAGE'] = 'Failed to update channel requests table.';

            CoreUtils::flog4php(4, $this->_msisdn, array("MESSAGE" => "Failed to update into channel requests table. Reason:" . $response['DATA']), __FILE__, __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);
        } else {
            $updatereturn['SUCCESS'] = TRUE;
            $updatereturn['DATA'] = $response;
            $updatereturn['MESSAGE'] = 'Successfully updated channelRequests table.';

            CoreUtils::flog4php(4, $this->_msisdn, array("MESSAGE" => "Successfully updated the channel requests table."), __FILE__, __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);
        }

        return $updatereturn;
    }

    /**
     *  The function fetches external system url using the clientProtocolID passed
     * @param int $clientProtocolID
     * @return boolean 
     */
    public function getSystemURL($clientProtocolID) {

        $query = "SELECT p.protocolID, apiUrl, apiPortNumber, apiFunctionName,
            apiUserName, apiPassword from c_clientProtocols cp inner join 
            c_protocols p on p.protocolID = cp.protocolID WHERE 
            clientProtocolID = ?";

        $response = $this->dbUtils->query($query, true, array($clientProtocolID));
        $fetcharray = array();
        if (!$response['DATA']['RESULTS']['apiUrl']) {
            $fetcharray['SUCCESS'] = FALSE;
            $fetcharray['DATA'] = $response['DATA'];
            $fetcharray['MESSAGE'] = 'Failed to fetch external system URL.';
            CoreUtils::flog4php(4, $this->_msisdn, array("MESSAGE" => "|Failed to fetch external system URL and returning"), __FILE__, __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);
        } else {
            $fetcharray['SUCCESS'] = TRUE;
            $fetcharray['DATA'] = $response['DATA'];
            $fetcharray['MESSAGE'] = 'Successfully fetched the external system url.';

            CoreUtils::flog4php(4, $this->_msisdn, array("MESSAGE" => "|Fetched the external system URL and returning"), __FILE__, __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);
        }

        return $fetcharray;
    }

    /**
     * Processes POST requests
     * @param type $payload
     * @param type $urlmetadata 
     */
    public function processPOSTRequest($payload, $urlmetadata) {
        $requestData = array();

        //invoke external system url and return json response
        $requestData['PAYLOAD'] = $payload;
        $requestData['MSISDN'] = $this->_msisdn;
        $requestData['NETWORKID'] = $this->_networkID;
        $requestData['IMCID'] = $this->_IMCID;
        $requestData['ACCESSPOINT'] = $this->_accessPoint;
        $requestData['CLIENTSYSTEM_ID'] = $this->_clientSystemID;
        $requestData['EXTERNAL_SYSTEM_SERVICEID'] = $this->_externalSystemServiceID;

        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $urlmetadata['apiUrl']);
        curl_setopt($ch, CURLOPT_POST, count(http_build_query($requestData)));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($requestData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        //time out configs
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, CONNECT_TIMEOUT);
        curl_setopt($ch, CURLOPT_TIMEOUT, TIMEOUT_DURATION); //timeout in seconds
        // Simply configuring cURL to accept any server(peer) certificate for now. 
        // This can be changed to CA certificate
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //execute post
        $response = curl_exec($ch);

        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        CoreUtils::flog4php(4, $this->_msisdn, array("MESSAGE" => "|Response 
            from $urlmetadata:" . json_encode($response, JSON_FORCE_OBJECT)), __FILE__, __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);
        //array that will hold values to return - success or fail
        $returnvals = array();

        if (!$response) {
            $returnvals['SUCCESS'] = FALSE;
            $returnvals['MESSAGE'] = curl_error($ch);
            $returnvals['DATA'] = array('HTTP_STATUS' => $http_status);
        } else {
            $returnvals['SUCCESS'] = TRUE;
            $returnvals['MESSAGE'] = $response;
            $returnvals['DATA'] = array('HTTP_STATUS' => $http_status);
        }
        //close connection
        curl_close($ch);

        return $returnvals;
    }

    /**
     * Processes XML RPC requests only
     * @param type $payload
     * @param type $urlmetadata 
     */
    public function processXMLRPCRequest($payload, $urlmetadata) {

        $externalUrl = $urlmetadata['DATA']['apiUrl'];
        $credentials = array('username' => $urlmetadata['DATA']['username'], 'password' => $urlmetadata['DATA']['password']);
        $requestData = array('credentials' => $credentials, 'payload' => $payload);
        $externalClient = new IXR_Client($externalUrl);

        $externalClient->query($urlmetadata['DATA']['apiFunctionName'], $requestData);

        $externalResponse = $externalClient->getResponse();

        if ($externalResponse) {
            $externalResponse['SUCCESS'] = TRUE;
            $externalResponse['MESSAGE'] = $externalResponse;
            $externalResponse['DATA'] = array('HTTP_STATUS' => 200);
        } else {
            $externalResponse['SUCCESS'] = FALSE;
            $externalResponse['MESSAGE'] = $externalClient->getErrorMessage();
            $externalResponse['DATA'] = array('HTTP_STATUS' => '');
        }

        return $externalResponse;
    }

    /**
     * Invokes system URL via HTTP-POST using one parameter called PAYLOAD that will comprise of a JSON string
     * as formulated by the USSD MENU
     * @param string $payload
     * @param array $urlmetadata
     * @return array response data 
     */
    public function sendExternalRequest($payload, $urlmetadata) {
        $protocol = $urlmetadata['DATA']['RESULTS']['protocolID'];
        $responseData = array();

        if ($protocol == PHP_POST_PROTOCOL_ID) {//post
            CoreUtils::flog4php(4, $this->_msisdn, array("MESSAGE" => "|POST request"), __FILE__, __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);
            $responseData = $this->processPOSTRequest($payload, $urlmetadata);
        } else if ($protocol == XML_RPC_PROTOCOL_ID) {//xmlrpc
            $responseData = $this->processXMLRPCRequest($payload, $urlmetadata);
            CoreUtils::flog4php(4, $this->_msisdn, array("MESSAGE" => "|XML RPC request"), __FILE__, __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);
        }
        return $responseData;
    }

    /**
     * This function logs the response gotten from external system into channel
     * responses table with the overal status
     * @param type $channelRequestID
     * @param type $message
     * @param type $overalStatus
     * @return string 
     */
    public function logChannelResponses($channelRequestID, $message, $overalStatus) {

        $query = "INSERT INTO c_channelResponses (MSISDN, channelRequestID, 
            accessPoint, message,  networkID, IMCID, clientSystemID, 
            externalSystemServiceID, overalStatus, payload, 
            dateCreated) VALUES (?,?,?,?,?,?,?,?,?,?,?);";

        $insertparams = array(
            $this->_msisdn,
            $channelRequestID,
            $this->_accessPoint,
            $message,
            $this->_networkID,
            $this->_IMCID,
            $this->_clientSystemID,
            $this->_externalSystemServiceID,
            $overalStatus,
            $payload, //insert raw JSON  string.
            'now()');

        $result = $this->dbUtils->execute($query, $insertparams);

        $response_logstatus = array();

        if (!$result['DATA']) {
            $response_logstatus['SUCCESS'] = FALSE;
            $response_logstatus['DATA'] = $result['DATA'];
            $response_logstatus['MESSAGE'] = 'Failed to log request into channelResponses table.';
            CoreUtils::flog4php(4, $this->_msisdn, array("MESSAGE" => "Failed to log into channel responses table. Reason:" . $result['DATA']), __FILE__, __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);
        } else {
            $response_logstatus['SUCCESS'] = TRUE;
            $response_logstatus['DATA'] = $result['DATA'];
            $response_logstatus['MESSAGE'] = 'Request successfully logged into channelResponses table.';

            CoreUtils::flog4php(4, $this->_msisdn, array("MESSAGE" => "Successfully logged channel responses. Reason:" . $result['DATA']), __FILE__, __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);
            //ID for update
            $this->updateChannelSystemID = $result['DATA']['LAST_INSERT_ID'];
        }

        return $response_logstatus;
    }

}

?>