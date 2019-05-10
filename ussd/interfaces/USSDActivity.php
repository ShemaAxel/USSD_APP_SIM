<?php

/**
 * Model representation of the activities table used by USSD.
 * 
 * This class contains relevant implementation for interfacting with the activities
 * table used to log all USSD interactions. Each attribute has be represented.
 * The USSDRouter class will interact with the component directly.
 * 
 * @author cellulant-pd pd@cellulant.com
 * @since "1"
 * 
 */
class USSDActivity
{

    /**
     * Attributes of the hubchannels activities table
     * @var int 
     */
    public $activityID;

    /**
     * @var column clientSystemID type int
     */
    public $clientSystemID;

    /**
     * @var column MSISDN type int
     */
    public $MSISDN;

    /**
     * @var column acessPoint type string
     */
    public $accessPoint;

    /**
     * @var additional variable accessPointID int
     */
    public $accessPointID;

    /**
     * @var column gatewayID type int
     */
    public $gatewayID;

    /**
     * @var column gatewayUID type int
     */
    public $gatewayUID;

    /**
     * @var column requestPayload type string
     */
    public $requestPayload;

    /**
     * @var column connectorID type int
     */
    public $connectorID;

    /**
     * @var column networkID type int
     */
    public $networkID;

    /**
     * @var column sessionID type int
     */
    public $sessionID;

    /**
     *
     * @var the current session state either new or exists
     */
    public $sessionState;

    /**
     * @var the service id 
     */
    public $externalSystemServiceID;

    /**
     * @var column IMCID type int
     */
    public $IMCID;

    /**
     *
     * @var string user input to be stored within activities
     */
    public $input;

    /**
     * @var column clientACK type int
     */
    public $clientACKID;

    /**
     * @var column overalStatus type string
     */
    public $overalStatus;

    /**
     * @var column statusHistory type string
     */
    public $statusHistory;

    /**
     * @var column statusDescription type string
     */
    public $statusDescription;

    /**
     * @var column serviceDescription type string
     */
    public $serviceDescription;

    /**
     * @var column dateCreated type date time
     */
    public $dateCreated;

    /**
     * @var column modified type date time
     */
    public $dateModified;

    /**
     * @var column expiryDate type date time
     */
    public $expiryDate;

    /**
     * @var column responsePayload type string
     */
    public $responsePayload;

    /**
     * @var column dateResponded type date time
     */
    public $dateResponded;

    /**
     * @var column appID type int
     */
    public $appID;

    /**
     * @var object of DBUtils class
     */
    public $dbUtils;

    /**
     * Array to log
     */
    public $logparms;
    public $payload;
    private $tat;

    /**
     * The class constructor. Instatiates the DBUtils class
     */
    public function __construct($MSISDN, $SESSIONSTATE, $ACCESSPOINT, $USSD_INPUT, $SESSION_ID, $NETWORKID, $LOG_PARAMS, $DBUTILS, $IMSI = null) {

        $this->MSISDN = $MSISDN;
        $this->accessPoint = $ACCESSPOINT;
        $this->payload = array("MSISDN" => $MSISDN, "ACCESSPOINT" => $ACCESSPOINT,
            "INPUT" => $USSD_INPUT,
            "SESSIONID" => $SESSION_ID, "NETWORKID" => $NETWORKID, 'IMSI' => $IMSI); //log whole payload
        $this->input = $USSD_INPUT;
        $this->networkID = $NETWORKID;
        $this->sessionID = $SESSION_ID;
        $this->sessionState = $SESSIONSTATE;
        $this->logparms = $LOG_PARAMS;
        $this->requestPayload = json_encode($this->payload);

        $this->dbUtils = $DBUTILS;
        $this->IMCID = IMCID;
        $this->tat = new BenchMark($SESSION_ID);
    }

    /**
     * The function logs activities table. It is called by the ussd router class 
     * just before fetching menu contents.
     * @return result array
     */
    public function logActivities() {
        $this->tat->start(BenchMark::FUNCTION_LEVEL, __METHOD__,
            $this->sessionID);
        $query = "INSERT INTO c_activities (networkID,IMCID,MSISDN,sessionID,sessionState,accessPoint,requestPayload,input, dateCreated) VALUES (?,?,?,?,?,?,?,?,?);";

        $params = array(
            $this->networkID,
            $this->IMCID,
            $this->MSISDN,
            $this->sessionID,
            $this->sessionState,
            $this->accessPoint,
            $this->requestPayload,
            $this->input,
            date('Y-m-d H:i:s'));
        $this->logparams['message'] = "Logging the following params to DB " . json_encode($params);
        CoreUtils::flog4php(4, $this->MSISDN, $this->logparams, __FILE__,
            __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);
        $this->tat->start(BenchMark::DATABASE_LEVEL, __METHOD__,
            $this->sessionID);
        $result = $this->dbUtils->execute($query, $params);
        $this->tat->logTAT(BenchMark::DATABASE_LEVEL, __METHOD__,
            $this->sessionID);
        $logstatus = array();

        if (!$result['DATA']) {
            $logstatus['SUCCESS'] = FALSE;
            $logstatus['DATA'] = $result['DATA'];
            $logstatus['MESSAGE'] = 'Failed to log request into activities table.';
            $this->logparams['message'] = "The logging into activites table has failed. Reason" . json_encode($result['DATA']);
            CoreUtils::flog4php(4, $this->MSISDN, $this->logparams, __FILE__,
                __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);
        }
        else {
            $logstatus['SUCCESS'] = TRUE;
            $logstatus['DATA'] = $result['DATA'];
            $logstatus['MESSAGE'] = 'Request successfully logged into activities table.';
            $this->logparams['message'] = "The request has successfully been logged into activities table" . json_encode($result['DATA']);
            CoreUtils::flog4php(4, $this->MSISDN, $this->logparams, __FILE__,
                __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);
        }
        $this->tat->logTAT(BenchMark::FUNCTION_LEVEL, __METHOD__,
            $this->sessionID);
        return $logstatus;
    }

    /**
     * This function updates the activities table using the primary key returned. 
     * It is called by the ussd router class when rendering menu response back to the user
     * @param array $params
     * @return boolean
     */
    public function updateActivities($params) {
        $this->tat->start(BenchMark::FUNCTION_LEVEL, __METHOD__,
            $this->sessionID);
        $decodedValues = json_decode($params, true);
        $query = "UPDATE c_activities set clientSystemID=?, sessionState=?, externalSystemServiceID=?, overalStatus=?, serviceDescription=?, 
            statusDescription=?, responsePayload=?, dateResponded=? where activityID = ?";

        $details = array(
            $this->clientSystemID,
            $this->sessionState = $decodedValues['MNO_RESPONSE_SESSION_STATE'],
            $this->externalSystemServiceID = $decodedValues['EXTERNAL_SYSTEM_SERVICEID'],
            $this->overalStatus = SUCCESS_UPDATE_STATUS,
            $this->serviceDescription = $decodedValues['SERVICE_DESCRIPTION'],
            $this->statusDescription = 'Received Menu Response and logged',
            $this->responsePayload = $params,
            $this->dateResponded = date('Y-m-d H:i:s'),
            $this->activityID
        );
        $this->logparams['message'] = "Updating the following params within activities table  " . json_encode($details,
                JSON_FORCE_OBJECT);

        CoreUtils::flog4php(4, $this->clientSystemID, $this->logparams,
            __FILE__, __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);
        $this->tat->start(BenchMark::DATABASE_LEVEL, __METHOD__,
            $this->sessionID);
        $result = $this->dbUtils->execute($query, $details);
        $this->tat->logTAT(BenchMark::DATABASE_LEVEL, __METHOD__,
            $this->sessionID);
        $updatereturn = array();
        if (!$result['DATA']) {
            $updatereturn['SUCCESS'] = FALSE;
            $updatereturn['DATA'] = $result['DATA'];
            $updatereturn['REASON'] = 'Failed to update activities with the response .';
            $this->logparams['message'] = "The update in the activities table has failed. Reason" . $result['DATA'];
            CoreUtils::flog4php(4, $this->MSISDN, $this->logparams, __FILE__,
                __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);
        }
        else {
            $updatereturn['SUCCESS'] = TRUE;
            $updatereturn['DATA'] = $result['DATA'];
            $updatereturn['REASON'] = 'Successfully update activities table with the response.';
            $this->logparams['message'] = "The updating into activites table was successful." . json_encode($result['DATA']);
            CoreUtils::flog4php(4, $this->MSISDN, $this->logparams, __FILE__,
                __FUNCTION__, __LINE__, "ussdinfo", USSD_LOG_PROPERTIES);
        }
        $this->tat->logTAT(BenchMark::FUNCTION_LEVEL, __METHOD__,
            $this->sessionID);
        return $updatereturn;
    }

}

?>
