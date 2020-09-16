<?php

/**
 * Modification done by @author <boniface.kegode@cellulant.com>
 *
 * addition of pay bill, buy airtime, B2C functionality
 */
require_once '_include_sections.php';
require_once 'idiorm/idiorm.php';

// load from session
if (isset($_SESSION["user_profileID"])) {
    $profileID = $_SESSION['user_profileID'];
    $msisdn = $_SESSION['user_MSISDN'];
    $names = $_SESSION['user_names'];
    $network = $_SESSION['user_network'];
}

$DEBUG = null;
$network_list = get_network();

function check_session() {
    if (!isset($_SESSION['user_profileID'])) {
        $_SESSION = null;
        die("<p>Invalid Session</p>");
        return false;
    }
    return true;
}

function create_session() {
    return rand(1000000000, 9999999999);
}

function cleanDebug($string) {
    $string = str_replace("  ", "&nbsp;", $string);
    $string = str_replace("\t", "&nbsp;&nbsp;", $string);
    return nl2br($string);
}

function home_page() {
    return array(
        "html" => render_homePage(),
        "debug" => ''
    );
}

function get_network($number = null) {
    $network_list = array();
    connectDB();
    $networks = ORM::for_table('networks')->find_many();
    foreach($networks as $network) {
        $network_list[$network->network_id] = $network;
        //echo '<pre>';
        //print_r($network->network_id); die;
    }
    
    

    if ($number == null) {
        return $network_list;
    } else {
        return $network_list[1];
    }
}

function dial_page($dialNumber = "*360#") {
    check_session();
    $_SESSION['ussd'] = null;

    return array(
        'dial_number' => $dialNumber,
        'html' => render_dialPage($dialNumber),
        "debug" => ''
    );
}

function calllog_page() {
    return array(
        'html' => render_callLogPage(),
        "debug" => ''
    );
}

function make_call($dialNumber) {
    global $DEBUG;
    check_session();
    $_SESSION['ussd']['session-id'] = create_session();
    $_SESSION['ussd']['servicecode'] = $dialNumber;
    $_SESSION['ussd']['servicecommand'] = $dialNumber;
    $_SESSION['ussd']['opcode'] = 'BEG';

    $query = "INSERT INTO call_logs (profile_id, dialNumber, numberOfDials, dateCreated) VALUES (" . gSQLv($_SESSION['user_profileID']) . ", " . gSQLv($dialNumber) . ", 1, now())
                ON DUPLICATE KEY UPDATE numberOfDials=numberOfDials+1";
    
    ORM::raw_execute($query);

    //render pages according to the shortcode dialed
    if ($dialNumber == '*144#') {
        check_session();

        $query = "SELECT airtimeBalance FROM profiles WHERE MSISDN = " . $_SESSION['user_MSISDN'];        
        $result = ORM::raw_execute($query);
        $balance = number_format($result->airtimeBalance, 2);

        if (!empty($result)) {

            $message = "Dear Customer, " . '<br>' . "  Your airtime balance " . '<br>' . " is $balance/- as of " . date('d M, Y h:i A');
        } else {
            $message = "System could not process your airtime balance";
        }

        return array(
            'dial_number' => $dialNumber,
            'html' => render_new_alerts($message),
            //"debug" => "<br/> ------------------ airtime query ------------------ " . cleanDebug("balance: $balance\n" . $DEBUG . mysql_error())
        );
    }

    $result = process_ussd($_SESSION['ussd']['session-id'], $dialNumber);
    $continue = true;
    if (strtoupper($_SESSION['ussd']['opcode']) == 'END') {
        $continue = false;
    }
    $DEBUG .= "\n<b>Result_Len:</b>" . strlen($result);

    return array(
        'dial_number' => $dialNumber,
        'html' => render_ussdSession($result, $dialNumber, $continue),
        "debug" => "<br/> ------------------ " . $_SESSION['ussd']['session-id'] . " ------------------ " . cleanDebug($DEBUG)
    );
}

function ussd_reply($response) {
    global $DEBUG;
    check_session();
    $dialNumber = $_SESSION['ussd']['servicecode'];

    $result = process_ussd($_SESSION['ussd']['session-id'], $response);
    $continue = true;
    if (strtoupper($_SESSION['ussd']['opcode']) == 'END')
        $continue = false;
    $DEBUG .= "\nResult_Len:" . strlen($result);

    return array(
        'dial_number' => $dialNumber,
        'html' => render_ussdSession($result, $dialNumber, $continue),
        "debug" => "<br/> ------------------ " . $_SESSION['ussd']['session-id'] . " ------------------ " . cleanDebug($DEBUG)
    );
}

function ussd_clear() {
    global $DEBUG;
    check_session();
    $dialNumber = $_SESSION['ussd']['servicecode'];
    $_SESSION['ussd'] = null;

    return array(
        'dial_number' => $dialNumber,
        'html' => render_dialPage($dialNumber),
        "debug" => "<br/>" . cleanDebug($DEBUG)
    );
}

function send_sms($destaddr, $message) {
    $profileID = $_SESSION['user_profileID'];
    $msisdn = $_SESSION['user_MSISDN'];
    $names = $_SESSION['user_names'];
    $network = $_SESSION['user_network'];

    $query = "insert into messages (profile_id, sourceaddr, destaddr, messageContent, dateCreated, messageType, messageRead) values
                ($profileID, '$msisdn', " . gSQLv($destaddr) . ", " . gSQLv($message) . ", now(), 0, 1)";
    $id = insertSQL($query);

    $result = process_sms($msisdn, $destaddr, $message, $id);
    return array(
        'html' => render_message_read($id),
        "debug" => "<br/>---------------------- SMS $id " . cleanDebug($DEBUG)
    );
}

function process_sms($sourceaddr, $destaddr, $messageContent, $message_id) {
    global $DEBUG;
    $server_url = hub_SMS_URL;

    $url = $server_url . "?ID=$message_id&DLR=1&SOURCEADDR=" . rawurlencode($sourceaddr) . "&DESTADDR=" . rawurlencode($destaddr) . "&MESSAGE=" . rawurlencode($messageContent);
    //echo $url;
    $DEBUG .= "\n$url";
    $result = join("", file($url));
    $DEBUG .= $result;

    $query = "update messages set messageRead=1 where message_id = $message_id";
    updateSQL($query);

    return $result . "=" . $url;
}

function process_ussd($sessionID, $input) {
    global $DEBUG;
    $server_url = hub_USSD_URL;

    $session_id = $sessionID;
    $msisdn = $_SESSION['user_MSISDN'];

    $opcode = "BEG";
    if (isset($_SESSION['ussd']['opcode'])) {
        $opcode = $_SESSION['ussd']['opcode'];
    }

    if (isset($_SESSION['ussd']['dialcode']) == "") {
        $_SESSION['ussd']['dialcode'] = $input;
    }
    $shortcode = str_replace('#', '*', $input);
//      if (isset($_SESSION['ussd']['servicecommand'])) {
//              $shortcode = str_replace('#', '', $_SESSION['ussd']['servicecommand']) . "*" . "$input";
//      }

    $shortcode = rawurlencode($shortcode);
    $imsi = '12345-85948-59409-22224';
    $url = $server_url . "?MSISDN=$msisdn&SERVICE_CODE=" . rawurlencode($_SESSION['ussd']['dialcode']) . "&EMULATOR_IMSI=$imsi&SESSIONID=$session_id&NETCODE=" . rawurlencode($_SESSION['user_network']). "&INPUT_STRING=" . rawurlencode($input);
    try {
        if (!ssl_engine) { //SSL Engine is off
            $returnedString = join('', file($url));

            $DEBUG .= "<b>Invoking via HTTP</b> <u>" . $url . "</u> \n<b>Response:</b>" . $returnedString;
            //we expect JSON
            $decodedresponse = json_decode($returnedString, true);
        } else { //SSL Engine is true
            $ch = curl_init();
            //set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            //curl_setopt($ch, CURLOPT_MUTE,1);
            curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
            //    curl_setopt($ch, CURLOPT_POST, true);
            //  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            // For Debug mode; shows up any error encountered during the operation
            curl_setopt($ch, CURLOPT_VERBOSE, 1);
            //Enable it to return http errors
            curl_setopt($ch, CURLOPT_FAILONERROR, true);
            //set the timeout
            curl_setopt($ch, CURLOPT_TIMEOUT, 120);
            curl_setopt($ch, CURLE_OPERATION_TIMEOUTED, 120);

            //new options
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, verify_peer); //config
            //curl_setopt($ch, CURLOPT_CAINFO, REQUEST_SSL_CERTIFICATE);
            curl_setopt($ch, CURLOPT_CAINFO, ssl_cert_path); //config
            //execute post
            $returnedString = curl_exec($ch);
            
            $DEBUG .= "Invoking via HTTPS* <u>" . $url . "</u> \n<b>Response:</b> " . $returnedString;
            //we expect JSON
            $decodedresponse = json_decode($returnedString, true);
            //close connection
            curl_close($ch);
        }
        $DEBUG .= "\n<b>Decoded:</b>" . print_r($decodedresponse,true);
        $message = isset($decodedresponse['RESPONSE_STRING']) ? $decodedresponse['RESPONSE_STRING'] : $decodedresponse['PAGE_STRING'];
        if (isset($decodedresponse['SESSION_ID'])) {
            $_SESSION['ussd']['session'] = $decodedresponse['SESSION_ID'];
        }
        $_SESSION['ussd']['servicecommand'] = rawurldecode($shortcode);

        if (isset($decodedresponse['SESSION_STATE'])) {
            $opcode = $decodedresponse['SESSION_STATE'];
            $_SESSION['ussd']['opcode'] = $opcode;
        }

        if ($message == "") {
            $message = "Sorry server returned a blank message for your request\n\nDid not process request for " . htmlentities(rawurldecode($shortcode));
            $_SESSION['ussd']['opcode'] = 'END';
        }

        ///===================================================
        //decode response
    } catch (Exception $exc) {
        $DEBUG .= "EXCEPTION:" . $url . "\n" . $exc->getTraceAsString();
        $message = "Sorry we could not process your request at the moment. Please try again Later\n\nCannot process request for " . htmlentities(rawurldecode($shortcode));
        $_SESSION['ussd']['opcode'] = 'END';
    }
    return $message;
}

function get_time() {
    global $DEBUG, $network_list;
    $dbg = cleanDebug($DEBUG);
    $DEBUG = "";
    return array(
        'time' => date("h:i A"),
        'debug' => $dbg,
        'date' => date("Y-M-d H:i:s"),
        'messages' => unread_messages(),
        'msisdn' => $_SESSION['user_MSISDN'],
        'operator' => $network_list[$_SESSION['user_network']]['networkName']
    );
}

function unread_messages() {
    connectDB();
    $message = ORM::for_table('messages')
            ->where('messageRead',0)
            ->count();
    print_r($message); die;
    $query = "select count(*) messagecount from messages where messageRead=0 and (profile_id = " . gSQLv($_SESSION['user_profileID']) . " or destaddr = " . gSQLv($_SESSION['user_MSISDN']) . ")";
    if ($result = selectSQL($query)) {
        if ($messages = mysql_fetch_assoc($result)) {
            if (isset($messages['messagecount']) and $messages['messagecount'] > 0) {
                return "<a style='background-image:url(images/blank.gif);color:#FFF;font-weight:bold;' onclick='return go_messaging()' title='You have " . $messages['messagecount'] . " unread messages'>" . $messages['messagecount'] . "</a>";
                // success you have unread messages
            } else {
                return "<a style='background-image:url(images/blank.gif);color:#FFF;font-weight:bold;' onclick='return go_messaging()' title='You have No unread messages'>0</a>";
                // success you have no unread messages
            }
        } else {
            return "<a style='background-image:url(images/blank.gif);color:#FFF;font-weight:bold;' onclick='return go_messaging()' title='You have No unread messages'>00</a>";
            // Fail mysql result is invalid
        }
    } else {
        return "<a style='background-image:url(images/blank.gif);color:#FFF;font-weight:bold;' onclick='return go_messaging()' title='You have No unread messages $query'>000</a>";
        // Fail mysql error ocurred.
    }
    return "";
}

function isValidMobileNo($mobileNumber) {
    global $DEBUG;
    $number = trim($mobileNumber);
// ------ check for nothing
    if ($number == '') {
        return 0;
    }
    if (!is_numeric($number)) {
        return 0;
    }
// ------ check for leading 0
    $prfx = substr($number, 0, 1);
    if ($prfx == '0') {
        $number = substr($number, 1);
    }
// ------ check for mising country code and network use Kenya as default
    $prfx = substr($number, 0, 2);
    if ($prfx >= 50 and $prfx <= 99) {
        $number = '254' . $number;
    }
// ------ check country prefix Africa 20* to 291* 
    $prfx = substr($number, 0, 4);
    if ($prfx < 2001 or $prfx > 2919) {
        $DEBUG .= "<!-- #invalid_Number $mobileNumber [$number] wrong prefix -->\n";
        return 0;
    }
// ------ check that number is long enough
    if (strlen($number) != 12) {
        $DEBUG .= "<!-- #invalid_Number $mobileNumber [$number] wrong length -->\n";
        return 0;
    }
    return $number;
// ------ Valid number return it
}

function browser_page($url = '') {
    if ($url == "") {
        $url = 'http://m.google.com';
    }

    return array(
        'html' => render_browser($url),
        "debug" => $url
    );
}

function messaging_home_page() {
    return array(
        'html' => render_messaging_home(),
        "debug" => ''
    );
}

function message_new_page() {
    return array(
        'html' => render_message_new(),
        "debug" => ''
    );
}

function message_read_page($messageID) {
    return array(
        'html' => render_message_read($messageID),
        "debug" => ''
    );
}

function profile_page($action) {
    return array(
        'html' => render_profile_page($action),
        "debug" => ''
    );
}

function pay_bill($action) {
    $script = 'QueryMobileMoneyBalance';
    $message = $action;
    //  $message .= "\n" . invokeURL($_SESSION['ussd']['session-id'], "", $script);

    $query = "SELECT mobileMoneyBalance FROM profiles WHERE MSISDN = " . $_SESSION['user_MSISDN'];
    $sql = selectSQL($query);
    $result = mysql_fetch_array($sql);
    $balance = number_format($result['mobileMoneyBalance'], 2);

    if (!empty($result)) {
        $message .= "\nAccount Balance is $balance";
    } else {
        $message .= "\nSystem could not process your airtime balance";
    }

    return array(
        'html' => render_get_payments($message, "", true),
        "debug" => ''
    );
}

function newmenus($action) {
    return array(
        'html' => render_emulator_menus($action),
        "debug" => ''
    );
}

function topup($action) {
    $script = 'QueryAirtimeBalance';
    $message = $action;
    $message .= "\n" . invokeURL($_SESSION['ussd']['session-id'], "", $script);

    return array(
        'html' => render_topup_airtime($message, "", true),
        "debug" => ''
    );
}

function b2ctransfer($action) {
    $script = 'QueryMobileMoneyBalance';
    $message = $action;
    $message .= "\n" . invokeURL($_SESSION['ussd']['session-id'], "", $script);

    return array(
        'html' => render_b2ctransfer($message, "", true),
        "debug" => ''
    );
}

//==============================================================================
//==============================================================================
//==============================================================================
//==============================================================================

function microtime_f() {
    list ($msec, $sec) = explode(" ", microtime());
    return ((float) $msec + (float) $sec);
}

function connectDB() {
    ORM::configure('mysql:host=localhost;dbname=samsung_simulator');
    ORM::configure('username', 'root');
    ORM::configure('password', 'ax3l1234');
    
    ORM::configure('id_column_overrides', array(
        'profiles' => 'profile_id',    
    ));
}

function selectSQL($query, $dbLink = null) {    
    if ($dbLink == null)
        $dbLink = connectDB();

    $start = microtime_f();
    //$result = mysql_query("$query", $dbLink) or flog(fatalLogs, "selectSQL dberror | ".mysql_error($dbLink)." | on ".$query);
    $result = ORM::raw_execute($query);
    if(!$result) {
        loqError(fatalLogs, 'selectSQL', mysql_error($dbLink), $query);
    }//if
    
    //$result = mysql_query("$query", $dbLink) 
    $stop = microtime_f();
    $time = $stop - $start;
    flog(sqlLogs, "selectSQL() |" . sprintf(" %01.4f ", $time) . "| $query");

    return $result;
}

function insertSQL($query, $dbLink = null) {
    connectDB();

    $start = microtime_f();
    //mysql_query("$query", $dbLink) or flog(fatalLogs, "insertSQL dberror | ".mysql_error($dbLink)." | on ".$query);
    //mysql_query("$query", $dbLink) or loqError(fatalLogs, 'insertSQL', mysql_error($dbLink), $query);
    ORM::raw_execute($query);
    $stop = microtime_f();
    $time = $stop - $start;
    flog(sqlLogs, "insertSQL() |" . sprintf(" %01.4f ", $time) . "| $query");
    return rand(1,100);
}

function updateSQL($query, $dbLink = null) {
    if ($dbLink == null)
        $dbLink = connectDB();

    $start = microtime_f();
    //mysql_query("$query", $dbLink) or flog(fatalLogs, "updateSQL dberror | ".mysql_error($dbLink)." | on ".$query);
    mysql_query("$query", $dbLink) or loqError(fatalLogs, 'updateSQL', mysql_error($dbLink), $query);
    $stop = microtime_f();
    $time = $stop - $start;
    flog(sqlLogs, "updateSQL() |" . sprintf(" %01.4f ", $time) . "| $query");
    return mysql_affected_rows($dbLink);
}

function loqError($log, $function, $error, $query = "") {
    flog($log, "$function | $error on | $query");
    if (($function == 'updateSQL' or $function == 'insertSQL')) { // and (stristr($query, 'outbound') !== FALSE and stristr($query, 'inboxRouter') !== FALSE))
        xflog("queriesToRun.log", $query);
    }
}

function gSQLv($theValue, $theType = 'text', $theDefinedValue = "", $theNotDefinedValue = "") {
    connectDB();

    $theValue = trim($theValue);
    $theValue = (!get_magic_quotes_gpc()) ? addslashes($theValue) : $theValue;

    switch ($theType) {
        case "cell":
            $theValue = ($theValue != "") ? isValidMobileNo($theValue) : "NULL";
            break;
        case "asis":
            $theValue = ($theValue != "") ? $theValue : "''";
            break;
        case "text":
            $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
            break;
        case "long":
            $theValue = ($theValue != "") ? 0 + $theValue : "NULL";
            break;
        case "int":
            $theValue = ($theValue != "") ? intval($theValue) : "NULL";
            break;
        case "float":
            $theValue = ($theValue != "") ? floatval($theValue) : "NULL";
            break;
        case "double":
            $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
            break;
        case "date":
            $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
            break;
        case "check":
            $theValue = ($theValue != "") ? "1" : "0";
            break;
        case "defined":
            $theValue = ( $theValue != "") ? $theDefinedValue : $theNotDefinedValue;
            break;
    }
    return $theValue;
}

function getRow($rsData) {
    return mysql_fetch_assoc($rsData);
}

function getRsRow($query, $dbLink = null) {
    if ($rsRec = selectSQL($query, $dbLink)) {
        return getRow($rsRec);
    } else {
        return false;
    }
}

function getValue($query, $collumn, $dbLink = null) {
    if ($rsData = selectSQL($query, $dbLink)) {
        $rwRec = mysql_fetch_assoc($rsData);
        return $rwRec[$collumn];
    } else {
        return false;
    }
}

function xflog($xfile, $string) {
    $file = flogPath($xfile);
    $date = date("Y-m-d H:i:s");
    $fo = fopen($file, 'ab');
    fwrite($fo, $string . " /* $date | " . $_SERVER['PHP_SELF'] . " */\n");
    fclose($fo);
}

function flogPath($file) {
    $log_path = log_path;
    if (strtolower(substr($file, (strlen($file) - 4), 4)) == '.log' or strtolower(substr($file, (strlen($file) - 4), 4)) == '.txt') {
        return $log_path . basename($file);
    } else {
        return $log_path . basename($file) . '.log';
    }
}

function flog($level, $string = '', $lineNo = '', $function = '') {
    global $DEBUG_LEVEL, $logFiles;

    $date = date("Y-m-d H:i:s");
    $logType[0] = 'UNDEFINED';
    $logType[1] = 'UNDEFINED';
    $logType[2] = 'UNDEFINED';
    $logType[3] = 'UNDEFINED';
    $logType[4] = 'INFOLOG';
    $logType[5] = 'SEQUEL';
    $logType[6] = 'TRACELOG';
    $logType[7] = 'DEBUGLOG';
    $logType[8] = 'ERRORLOG';
    $logType[9] = 'FATALLOG';
    $logType[10] = 'UNDEFINED';
    $logTitle = 'UNDEFINED';


    if (!is_int($level)) { // level is a string convert back to int and overide the default file
        if (strtolower(substr($level, (strlen($level) - 4), 4)) == '.log' or strtolower(substr($level, (strlen($level) - 4), 4)) == '.txt') { // overide the current paths {{faster than changing all scripts with custom paths}}
            $file = log_path . basename($level);
        } else { // ensure that the extension is there
            $file = log_path . basename($level) . '.log';
        }
        $level = 3;
        $logTitle = 'CUSTOM';
    } else {
        if (isset($logFiles[$level])) {
            // overide the current paths {{faster than changing all scripts with custom paths}}
            $file = $file = log_path . basename($logFiles[$level]);
            $logTitle = $logType[$level];
        } else {
            $file = $logFiles[3];
            $logTitle = 'UNDEFINED';
        }
    }

    if ($level >= $DEBUG_LEVEL) {
        if ($fo = fopen($file, 'ab')) {
            fwrite($fo, "$date - [ $logTitle ] " . $_SERVER['PHP_SELF'] . ":$lineNo $function | $string\n");
            fclose($fo);
        } else {
            trigger_error("flog Cannot log '$string' to file '$file' ", E_USER_WARNING);
        }
    }
}

function invokeURL($sessionID, $input, $script, $params = null) {
    global $DEBUG;
    $session_id = $sessionID;
    $msisdn = $_SESSION['user_MSISDN'];

    $opcode = "BEG";
    if (isset($_SESSION['ussd']['opcode'])) {
        $opcode = $_SESSION['ussd']['opcode'];
    }

    if (isset($_SESSION['ussd']['dialcode']) == "") {
        $_SESSION['ussd']['dialcode'] = $input;
    }
    $shortcode = str_replace('#', '*', $input);
//      if (isset($_SESSION['ussd']['servicecommand'])) {
//              $shortcode = str_replace('#', '', $_SESSION['ussd']['servicecommand']) . "*" . "$input";
//      }
    $next = NULL;
    if (!isset($_SESSION['ussd']['next'])) {
        $next = 1;
    }

    $url = simulator_URL . "/$script.php?MSISDN=$msisdn";

    $urlString = "";
    if (count($params) > 0) {
        foreach ($params as $key => $value) {
            $urlString .= "&" . $key . "=" . $value;
        }

        flog(infoLogs, "String " . $urlString);

        $url = simulator_URL . "/$script.php?MSISDN=$msisdn$urlString";
    }

    $shortcode = rawurlencode($shortcode);
    try {
        $returnedString = join('', file($url));


        $DEBUG .= $url . "\n" . $returnedString . "\n" . print_r($_SESSION, true);

        $message = $returnedString;
        //we doing JSON
        //  $decodedresponse = json_decode($returnedString, true);
        /*
          $message = $decodedresponse['PAGE_STRING'];
          if (isset($decodedresponse['SESSION_ID']))
          $_SESSION['ussd']['session'] = $decodedresponse['SESSION_ID'];
          $_SESSION['ussd']['servicecommand'] = rawurldecode($shortcode);

          if (isset($decodedresponse['MNO_RESPONSE_SESSION_STATE'])) {
          $opcode = $decodedresponse['MNO_RESPONSE_SESSION_STATE'];
          $_SESSION['ussd']['opcode'] = $opcode;
          }
         */
        if ($message == "") {
            $message = "Sorry server returned a blank message for your request\n\nDid not process request for " . htmlentities(rawurldecode($shortcode));
            $_SESSION['ussd']['opcode'] = 'END';
        }

        ///===================================================
        //decode response
    } catch (Exception $exc) {
        $DEBUG .= "EXCEPTION:" . $url . "\n" . $exc->getTraceAsString();
        $message = "Sorry we could not process your request at the moment. Please try again Later\n\nCannot process request for " . htmlentities(rawurldecode($shortcode));
        $_SESSION['ussd']['opcode'] = 'END';
    }
    return $message;
}

//post bill request
function postBillRequest($payBillNumber, $amount, $accountNumber) {
    global $DEBUG;
    $_SESSION['ussd']['session-id'] = create_session();
    $_SESSION['ussd']['servicecode'] = $dialNumber;
    $_SESSION['ussd']['servicecommand'] = $dialNumber;
    $_SESSION['ussd']['opcode'] = 'BEG';
    check_session();
    $message = "Please input the following" . '<br>';
    $error = false;
    if ($payBillNumber == "") {
        $message .= " - Bill Number " . '<br>';
        $error = true;
    }
    if ($amount == "") {
        $message .= " - Amount " . '<br>';
        $error = true;
    }
    if ($accountNumber == "") {
        $message .= " - Account Number " . '<br>';
        $error = true;
    }
    if ($payBillNumber != "" && $amount != "" && $accountNumber != "") {
        $dialNumber = $_SESSION['ussd']['servicecode'];
        $script = 'BeepPayBillRequest';
        $msisdn = $_SESSION['user_MSISDN'];
        $params = array('MSISDN' => $msisdn, 'MERCHANT_CODE' => $payBillNumber, 'AMOUNT' => $amount, 'ACCOUNT_NUMBER' => $accountNumber);

        $message = "";
        $message = invokeURL($_SESSION['ussd']['session-id'], $dialNumber, $script, $params);

        $continue = false;
        if (strtoupper($_SESSION['ussd']['opcode']) == 'END')
            $continue = false;
        $DEBUG .= "\nResult_Len:" . strlen($message) . ";\nText:" . htmlentities($message);
    }
    return array(
        'html' => render_alerts($message, $error),
        "debug" => $_SESSION['ussd']['session-id'] . "\n" . htmlentities($DEBUG)
    );
}

//post airtime request
function airtime_request($amount) {
    global $DEBUG;
    check_session();
    $dialNumber = $_SESSION['ussd']['servicecode'];
    $script = 'BeepAirTimeRequest';
    $msisdn = $_SESSION['user_MSISDN'];
    $params = array('MSISDN' => $msisdn, 'AMOUNT' => $amount);

    $result = invokeURL($_SESSION['ussd']['session-id'], $dialNumber, $script, $params);

    $continue = false;
    if (strtoupper($_SESSION['ussd']['opcode']) == 'END')
        $continue = false;
    $DEBUG .= "\nResult_Len:" . strlen($result) . ";\nText:" . htmlentities($result);

    return array(
        'dial_number' => $dialNumber,
        'html' => render_ussdSession($result, $dialNumber, $continue),
        "debug" => $_SESSION['ussd']['session-id'] . "\n" . htmlentities($DEBUG)
    );
}

//process b2c request
function b2cRequest($amount) {
    global $DEBUG;
    check_session();
    $dialNumber = $_SESSION['ussd']['servicecode'];
    $script = 'BeepMpesaRequest';
    $params = array('AMOUNT' => $amount, 'SERVICEID' => b2c_service_id);

    $result = invokeURL($_SESSION['ussd']['session-id'], $dialNumber, $script, $params);

    $continue = false;
    if (strtoupper($_SESSION['ussd']['opcode']) == 'END')
        $continue = false;
    $DEBUG .= "\nResult_Len:" . strlen($result) . ";\nText:" . htmlentities($result);

    return array(
        'dial_number' => $dialNumber,
        'html' => render_ussdSession($result, $dialNumber, $continue),
        "debug" => $_SESSION['ussd']['session-id'] . "\n" . htmlentities($DEBUG)
    );
}

//process b2c request
function checkBalance($amount) {
    check_session();

    $query = "SELECT mobileMoneyBalance FROM profiles WHERE MSISDN = " . $_SESSION['user_MSISDN'];
    $sql = selectSQL($query);
    $result = mysql_fetch_array($sql);
    $balance = number_format($result['mobileMoneyBalance'], 2);

    if (!empty($result)) {

        $message = "Dear Customer, " . '<br>' . "  Your mobile money balance " . '<br>' . " is $balance/- as of " . date('d M, Y h:i A');
    } else {
        $message = "System could not process your mobile money balance";
    }

    return array(
        'html' => render_alerts($message),
    );
}
