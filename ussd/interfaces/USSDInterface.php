<?php

include_once 'bootstrap.php';

/**
 * Super class to all PHP - USSD MNO interafces
 * 
 * This class contains relevant implementation for core logic implemented within
 * the various MNO interfaces which are PHP based. It handles session existence
 * validation, payload formulation, session ID generation among other 
 * functionalities
 * 
 * @author kevin kipn kevin.kipngetich@cellulant.com
 * @since "1"
 * 
 */
class USSDInterface
{

    private $tat;
    public $ussdRouter; // Component responsible for request routing
    public $msisdn;     // subscribers mobile number
    public $sessionID;  // unique identifier for this session
    public $sessionState; // Indicates the status of this session possible values: ABORT, NEW, EXISTS 
    public $accessPoint;  // The short code the customer dialed
    public $input;  // Input from the service subscriber
    public $logparams = array(); //array holding parameters to log.
    public $origin; // MNO identifier
    public $other_params;
    // create a super class variable to hold the IMSI
    public $IMSI;

    /**
     * Main class constructor. Used to inilize global variables from MNO
     * @param string $MSISDN
     * @param string $SESSION_STATE
     * @param string $ACCESSPOINT
     * @param string $INPUT
     * @param string $ORIGIN
     * @param array $OTHER_PARAMS
     * @param long $SESSION_ID
     */
    function __construct($MSISDN, $SESSION_STATE, $ACCESSPOINT, $INPUT, $ORIGIN, $OTHER_PARAMS, $SESSION_ID, $IMSI
    = null) {
        $this->msisdn = $MSISDN;
        $this->sessionState = $SESSION_STATE;
        $this->accessPoint = $ACCESSPOINT;
        $this->input = $INPUT;
        $this->origin = $ORIGIN;
        $this->other_params = $OTHER_PARAMS;
        $this->sessionID = $SESSION_ID;
        $this->IMSI = $IMSI;

        $this->logparams['msisdn'] = $MSISDN;
        $this->logparams['sessionID'] = $SESSION_ID;
        $this->logparams['networkID'] = $ORIGIN;
        $this->tat = new BenchMark($SESSION_ID);
    }

    // CREATE A FUNCTION TO SET THE IMSI
    public function setIMSI($newIMSI) {
        $this->tat->logTAT(BenchMark::FUNCTION_LEVEL, __METHOD__,
            $this->sessionID);
        $this->IMSI = $newIMSI;
    }

    // CREATE A FUNCTION TO GET THE IMSI
    public function getIMSI() {
        return $this->IMSI;
    }

    /**
     * This function generates the session id incase none is provided by the MNO
     * @return generated sessionID type long 
     */
    public function generateSessionID() {
        $this->tat->start(BenchMark::FUNCTION_LEVEL, __METHOD__,
            $this->sessionID);
        $SESSIONID = rand(1000, 1000000000);
        srand((double) microtime() * $SESSIONID);

        CoreUtils::flog4php(4, null,
            array("MESSAGE" => "Random sessionID generated is :" . $SESSIONID),
            __FILE__, __FUNCTION__, __LINE__, 'ussdinfo', USSD_LOG_PROPERTIES);
        
        $this->tat->logTAT(BenchMark::FUNCTION_LEVEL, __METHOD__,
           $this->sessionID);
        return $SESSIONID;
    }

    /**
     * Retrieves stored random generated sessionIDs from database using mobilenumber, time and networkID
     * 
     * @param string $MSISDN
     * @param int $NETWORKID
     * @return int
     */
    public function getSessionID($MSISDN, $NETWORKID) {
        $this->tat->start(BenchMark::FUNCTION_LEVEL, __METHOD__,
            $this->sessionID);
        $query = "SELECT randsessionID FROM c_ussdGenSessionID WHERE MSISDN  = ? AND networkID = ? AND dateCreated>=DATE_ADD(now(),interval -" . SESSION_EXPIRY_DURATION . " minute)";

        $params = array($MSISDN, $NETWORKID);

        //get db object
        $dbutils = new DbUtils();
        $this->tat->start(BenchMark::DATABASE_LEVEL, __METHOD__,
            $this->sessionID);
        $result = $dbutils->query($query, true, $params);
        $this->tat->logTAT(BenchMark::DATABASE_LEVEL, __METHOD__,
            $this->sessionID);
        $sessionID = 0;
        if ($result['SUCCESS'] && $result['DATA']['RESULTS'] && $result['STATCODE']
            == 1) {
            $sessionID = $result['DATA']['RESULTS']['randsessionID']; //we have a sessionID
        }
        $this->tat->logTAT(BenchMark::FUNCTION_LEVEL, __METHOD__,
            $this->sessionID);
        return $sessionID;
    }

    /**
     * Stores given sessionID to database for persistence purposes. Associated
     * with the mobile number and networkID for quick search.
     * 
     * @param int $SESSIONID
     * @param String $MSISDN
     * @param int $NETWORKID
     */
    public function saveSessionID($SESSIONID, $MSISDN, $NETWORKID) {
        $this->tat->start(BenchMark::FUNCTION_LEVEL, __METHOD__,
            $this->sessionID);
        $insertquery = "INSERT INTO c_ussdGenSessionID (MSISDN,randsessionID,networkID,dateCreated) VALUES (?,?,?,?)";

        $insertparams = array($MSISDN, $SESSIONID, $NETWORKID, date('Y-m-d H:i:s O'));
        $dbutils = new DbUtils();
        $this->tat->start(BenchMark::DATABASE_LEVEL, __METHOD__, $SESSIONID);
        $insertresult = $dbutils->execute($insertquery, $insertparams);
        $this->tat->logTAT(BenchMark::DATABASE_LEVEL, __METHOD__, $SESSIONID);
        if ($insertresult['SUCCESS'] && $insertresult['DATA']['LAST_INSERT_ID']) { //successfull insert
            $lastid = $insertresult['DATA']['LAST_INSERT_ID'];
            CoreUtils::flog4php(4, null,
                array("MESSAGE" => "Successfully created random session ID record for mobile number $MSISDN via network $NETWORKID sessionID:" . $SESSIONID),
                __FILE__, __FUNCTION__, __LINE__, 'ussdinfo',
                USSD_LOG_PROPERTIES);
            $this->tat->logTAT(BenchMark::FUNCTION_LEVEL, __METHOD__,
                $this->sessionID);
        }
    }

    /**
     * Deletes random generated session ID from DB when menu END is reached i.e 
     * session terminated
     * @param int $SESSIONID
     * @param string $MSISDN
     * @param int $NETWORKID
     */
    public function cleanSessionID($SESSIONID, $MSISDN, $NETWORKID) {
        $this->tat->start(BenchMark::FUNCTION_LEVEL, __METHOD__,
            $this->sessionID);


        $deletequery = "DELETE FROM c_ussdGenSessionID WHERE MSISDN = ? AND networkID = ? AND randsessionID = ?";
        //set params
        $deleteparams = array($MSISDN, $NETWORKID, $SESSIONID);
        $dbutils = new DbUtils();
        $this->tat->start(BenchMark::DATABASE_LEVEL, __METHOD__, $SESSIONID);
        $deleteresult = $dbutils->execute($deletequery, $deleteparams);
        $this->tat->logTAT(BenchMark::DATABASE_LEVEL, __METHOD__, $SESSIONID);
        if ($deleteresult['SUCCESS'] && $deleteresult['STATCODE']) { // success delete.
            CoreUtils::flog4php(4, null,
                array("MESSAGE" => "Successfully deleted random session ID for mobile number $MSISDN via network $NETWORKID sessionID:" . $SESSIONID),
                __FILE__, __FUNCTION__, __LINE__, 'ussdinfo',
                USSD_LOG_PROPERTIES);
            $this->tat->logTAT(BenchMark::FUNCTION_LEVEL, __METHOD__,
                $this->sessionID);
        }
        else { // failed delete.
            CoreUtils::flog4php(4, null,
                array("MESSAGE" => "Failed delete for random session ID for mobile number $MSISDN via network $NETWORKID sessionID:" . $SESSIONID),
                __FILE__, __FUNCTION__, __LINE__, 'ussdinfo',
                USSD_LOG_PROPERTIES);
            $this->tat->logTAT(BenchMark::FUNCTION_LEVEL, __METHOD__,
                $this->sessionID);
        }
    }

    /**
     * Function called by the MNO interface. Sets params and calls 
     * the log request function in the ussd router class.
     * @param type $result 
     */
    public function processRequest() {
        $this->tat->start(BenchMark::FUNCTION_LEVEL, __METHOD__,
            $this->sessionID);
        
        $this->tat->start(BenchMark::THIRDPARTY_LEVEL, __METHOD__,
            $this->sessionID);
        $ussdRouter = new USSDRouter($this->msisdn, $this->sessionState,
            $this->accessPoint, $this->input, $this->sessionID, $this->origin,
            $this->logparams, $this->IMSI);
        $this->tat->logTAT(BenchMark::THIRDPARTY_LEVEL, __METHOD__,
            $this->sessionID);
        // share the IMSI with the USSDRouter class
        //$ussdRouter->setIMSI($this->IMSI);


        $this->logparams['message'] = "Made an instance of the USSDRouter component, pushing the request";
        CoreUtils::flog4php(4, $this->msisdn, $this->logparams, __FILE__,
            __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);

        $result = $ussdRouter->processRoutingRequest();

        $this->logparams['message'] = "Response from the USSDRouter component $result:" . json_encode($result,
                JSON_FORCE_OBJECT);
        CoreUtils::flog4php(4, null, $this->logparams, __FILE__, __FUNCTION__,
            __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);
        $this->tat->logTAT(BenchMark::FUNCTION_LEVEL, __METHOD__,
            $this->sessionID);
        //return JSON string to interface
        return $result;
    }

}

?>
