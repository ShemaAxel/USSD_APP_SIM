<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CoreUtils
 *
 * @author cellulant-pd pd@cellulant.com
 */
class CoreUtils {

   /**
     * Return an array as a string indicating all keys and values
     * @param Array $theArray Array to be rendered
     * @param Text $seperator (default '\n') character to use in seperating aray entries
     * @param Text $indent (default '\t') character to prepend every seperate entry
     * @param Bool $keys (default 'true') Show or not to show Key values
     * @param Bool $heading (default 'true') Show or not to show "ARRAY(" headings
     * @param Text $equator (default '=') character to seperate Key from value
     * @param Text $open (default '[') character to appear befor Key value
     * @param Text $close (default ']') character to appeart after key value
     * @param Text $doubleindent (default '\t') character to be appended to $indent when in nested array
     * @return Text Text representation of the array
     */
    public static function printArray($theArray, $seperator = "\n", $indent = " \t", $keys = true, $heading = true, $equator = ' => ', $open = '[', $close = ']', $doubleIndent = " \t") {
        $ss = 0;
        $myString = '';
        if (is_array($theArray)) {
            if ($heading)
                $myString = "Array($seperator" . "$indent";

            foreach ($theArray as $key => $value) {
                if ($ss++ != 0)
                    $myString .= $seperator . $indent;
                if (is_array($value)) {
                    if ($keys) {
                        $myString .= $open . $key . $close . $equator;
                    }

                    $myString .= self::printArray($value, $seperator, $indent . $doubleIndent, $keys, $heading, $equator, $open, $close, $doubleIndent);
                } else {
                    if ($keys) {
                        $myString .= $open . $key . $close . $equator;
                    }

                    $myString .= $value;
                }
            }
            if ($heading)
                $myString .= $seperator . $indent . ")";
        } else {
            $myString = (string) $theArray;
        }
        return $myString;
    }

    /**
     * Log Function using log4php library .
     * @param int $logLevel
     * @param string $uniqueID 
     * @param string $stringtolog
     * @param string $fileName
     * @param string $function
     * @param int $lineNo
     * @param string $logger 
     * 
     * @example $stringtolog = CoreUtils::processLogArray(array("networkid"=>"1","message"=>"New safaricom USSD request","msisdn"=>$MSISDN,"accessPoint"=>$DATA));
        CoreUtils::flog4php(4,$stringtolog , __FILE__, __FUNCTION__, __LINE__, "safussdinterfaceinfo", $logproperties);
     */
    public static function flog4php($logLevel, $uniqueID=NULL, $arrayparams = null, $fileName = NULL, $function = NULL, $lineNo = NULL, $logger = NULL, $propertiespath) {
        
        $stringtolog = self::processLogArray($arrayparams);
        
        Logger::configure($propertiespath);
        $log4phplogger = Logger::getLogger($logger);
        //[date time | log level | file | function | unique ID(e.g MSISDN) | log text ]

        $loggedstring = "$fileName|$function|$uniqueID| $stringtolog";
        switch ($logLevel) {
            case 1: //critical
                $log4phplogger->fatal($loggedstring);
                break;
            case 2://fatal
                $log4phplogger->fatal($loggedstring);

                break;
            case 3://error
                $log4phplogger->error($loggedstring);

                break;
            case 4://info
                $log4phplogger->info($loggedstring);

                break;
            case 5://sequel
                $log4phplogger->debug($loggedstring);

                break;
            case 6://trace
                $log4phplogger->trace($loggedstring);

                break;
            case 7://debug
                $log4phplogger->debug($loggedstring);

                break;
            case 8://custom
                $log4phplogger->info($loggedstring);

                break;
            case 9://undefined
                // $log4phplogger->fatal($loggedstring);

                break;
            case 10: //warn
                $log4phplogger->warn($loggedstring);
                break;

            default; //undefined
        }
    }

    /**
     * Formulates common hub channels payload to log within a given request.
     * String to be returned will be concatenated for final log as show below
     * [date time | log level | file | function | unique ID | <<STRING RETURNED>> ]
     * 
     * @example method call - processLogArray(array("channelRequestID"=>32323,"networkid"=>1,"msisdn"=>254721159049..etc));
     * @param type $arrayparams
     */
    public static function processLogArray($arrayparams) {
       
        $logstringtoreturn = "";
        $paramCount = count($arrayparams);
        
        if(is_array($arrayparams))
        {
            $counter = 0;
            foreach ($arrayparams as $key => $value) {
                $counter ++;
                
                $logstringtoreturn.= $key . ":" . $value . ($counter < $paramCount ? "," : "");
            }
        }
        else
        {
            return (string)$arrayparams;
        }
        
        return $logstringtoreturn;
    }

}

?>