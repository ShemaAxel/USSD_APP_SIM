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
//USSD Super interface
require __DIR__ . '/../../USSDInterface.php';

//Monolog
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class MTNSuperInterface extends USSDInterface {
    
    /**
     *
     * @var type string Application logger
     */
    private $logger;
    
    /**
     * __constructor
     */
    public function __construct($MSISDN,$input) {
        $this->logger = new Logger('kwigira|ussd');
        //Set the handlers
        $this->logger->pushHandler(new StreamHandler('/var/log/applications/kwigira/channels/ussd/interfaces/mtn_rw_info.log', Logger::INFO));
        $this->logger->pushHandler(new StreamHandler('/var/log/applications/kwigira/channels/ussd/interfaces/mtn_rw_error.log', Logger::ERROR));
        $this->logger->pushHandler(new StreamHandler('/var/log/applications/kwigira/channels/ussd/interfaces/mtn_rw_warning.log', Logger::WARNING));
        
        //Generate the new sessionID
        
        parent::__construct($MSISDN, $SESSION_STATE, $ACCESSPOINT, $INPUT, $ORIGIN, $OTHER_PARAMS, $SESSION_ID, $IMSI);
    }
    
    /**
     * Format the MSISDN to country code format
     */
    
    //Setup database stuff
    static function connect() {
        ORM::configure('mysql:host=localhost;dbname=kwigira');
        ORM::configure('username', 'kwigira');
        ORM::configure('password', 'kw1g1rA');
        
        //Override the defaults
        ORM::configure('id_column_overrides', array(            
            'c_ussdGenSessionID' => 'ussdGenSessionID',            
        ));
    }
}


//Setup a new instance of the application logger

