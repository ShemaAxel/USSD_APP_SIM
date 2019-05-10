<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once '_sessions.php';
include_once '_beepConfigs.php';
include_once '_coreCode.php';
include_once 'IXR_Library.inc.php';


//get the params

$msisdn = $_GET['MSISDN'];
$amount = $_GET['AMOUNT'];
$accountNo = $_GET['ACCOUNT_NUMBER'];
$merchantCode = $_GET['MERCHANT_CODE'];
$profileID = $_SESSION['user_profileID'];
$networkID = $_SESSION['user_network'];
$url = '';

/*
  $msisdn = 254721851111;
  $amount = 1000;
  $accountNo = 10000;
  $merchantCode = 100203; */
//$profileID = $_SESSION['user_profileID'];


$serviceID = beep_paybill_service_id;
$apiUserName = beep_user;
$apiPassWord = beep_password;
$client = new IXR_client(beep_url);



echo logPayBillRequest();

//insert into requests
function logPayBillRequest() {
    global $msisdn, $amount, $serviceID, $client, $apiPassWord, $apiUserName, $accountNo, $merchantCode, $sql_conn, $networkID;

    $invoiceNo = bin2hex(openssl_random_pseudo_bytes(2));
    $invoiceNo = $invoiceNo . "-" . $msisdn;

    //generate a random transactionID
    $payerTrx = bin2hex(openssl_random_pseudo_bytes(7));
    $date = date('Y-m-d H:i:s');


    //check if the customer has enoough cash from the profile
    $query = "SELECT mobileMoneyBalance FROM profiles WHERE MSISDN = " . $msisdn;
    $sql = selectSQL($query);
    $result = mysql_fetch_array($sql);
    $balance = $result['mobileMoneyBalance'];

    if ($balance >= $amount) {

        // $dbLink= connectDB();
        $query = "INSERT INTO requestLog(amount, MSISDN, transactionTypeID, payerTransactionID, merchantCode, invoiceNumber,dateCreated) VALUES('$amount','$msisdn',2,'$payerTrx','$merchantCode','$accountNo','$date')";
        $sql = insertSQL($query);

        //check if it inserted
        if ($sql) {
        /*
            //get the service ID from merchant code
            $sql_conn_hub = mysql_connect('192.168.254.47', 'cellulant', 'cellulant') or loqError(fatalLogs, 'connect_DB', mysql_error());
            mysql_selectdb('hub_4_3', $sql_conn_hub) or loqError(fatalLogs, 'select_DB', mysql_error());
            $queryHub = "select cs.clientID, cs.clientSystemID, cs.serviceSettingID, cs.systemName, ap.accessPointID, ap.IMCID, ap.networkID, "
                    . "ap.accessPoint, apm.accessPointMappingID, apm.accessPointTypeID, apm.aphanumericAccessPoint, apm.MOCost, apm.MTCost, ss.serviceSettingID, ss.serviceID, smsSourceAddress "
                    . "from c_accessPoints ap inner join c_accessPointMappings apm using(accessPointID) "
                    . "inner join c_clientSystems cs using(clientSystemID) inner join s_serviceSettings ss using(serviceSettingID) "
                    . "where ap.accessPoint = '" . $merchantCode . "' and ap.networkID='" . $networkID . "'";
            $sqlHub = selectSQL($queryHub, $sql_conn_hub);
            $result = mysql_fetch_array($sqlHub);
            if (!empty($result)) {
                $serviceID = $result['serviceID'];
                $serviceName = $result['systemName'];
            } else {
                $serviceID = "";
            }

            mysql_close($sql_conn_hub);


            $requestLogID = $sql;
            $payload = array(
                //define the credentials here for auntentication @ beep
                "credentials" => array(
                    'username' => $apiUserName,
                    'password' => $apiPassWord,
                ),
                //send the  details of the transaction here
                "packet" => array(
                    array
                        (
                        'hubID' => $requestLogID,
                        'serviceID' => $serviceID,
                        'MSISDN' => $msisdn,
                        'invoiceNumber' => $invoiceNo,
                        'accountNumber' => $accountNo,
                        'payerTransactionID' => $payerTrx,
                        'amount' => $amount,
                        'narration' => 'Simulator Paybill Request',
                        'datePaymentReceived' => date('Y-m-d H:i:s'),
                        'currencyCode' => 'KES',
                        'paymentMode' => 'MoMo'
                    )
                )
            );



            if (!$client->query('BEEP.postPayment', $payload)) {
                //die('An error occurred - ' . $client->getErrorCode() . ":" . $client->getErrorMessage());

                flog(fatalLogs, "postPayment() |" . sprintf(" %01.4f ", $time) . "| " . $client->getErrorCode() . " -" . $client->getErrorMessage());

                return "Your request to pay bill has failed at beep level.Please try again";
            } else {
                $response = $client->getResponse();

                if ($response['results'][0]['statusCode'] == 139) {
                    //actual bal
                    $remainingBalance = $balance - $amount;
                    //update the balance
                    $query = "UPDATE samsung_emulator.profiles set mobileMoneyBalance =" . $remainingBalance . " WHERE MSISDN = " . $msisdn;
                    $sql = updateSQL($query, $sql_conn);
                }


                return updatePayBill($response, $serviceName, $remainingBalance);
            }*/

            $data = array(
                'mpesa_code' => $payerTrx,
                'business_number' => $merchantCode,
                'mpesa_msisdn' => $msisdn,
                'mpesa_sender' => 'Simulator Pay bill Request',
                'mpesa_amt' => $amount,
                'mpesa_acc' => $accountNo,
                'id' => $sql,
                'orig' => 'Simulator Paybill Request',
                'tstamp' =>  date('Y-m-d H:i:s'),
                'text' => 'Simulator payment',
                'user' => $apiUserName,
                'pass' => $apiPassWord,
                'mpesa_trx_time' => date('H:i:s'),
                'mpesa_trx_date' => date('Y-m-d'),
                'networkID' => $networkID,

            );

            if ($networkID == 63902) {
                $url = MoMo_safURL;
            } else if ($networkID == 63903) {
                $url = MoMo_airtelURL;
            } else {
                $url = MoMo_safURL;
            }
            $response =  curlUsingGet($url, $data);
            $json_decoded = json_decode($response, true);

            if ($json_decoded['stat_code'] == 1) {
                //actual bal
                $remainingBalance = $balance - $amount;
                //update the balance
                $query = "UPDATE samsung_emulator.profiles set mobileMoneyBalance =" . $remainingBalance . " WHERE MSISDN = " . $msisdn;
                $sql = updateSQL($query, $sql_conn);

                flog(infoLogs, "postPayment() | request was processed successfully ". print_r($response, true));
                return "Your request was processed successfully";
            } else {
                flog(infoLogs, "postPayment() | your request was not processed successfully ". print_r($response, true));

                return "Your request has been received: ". $json_decoded['stat_description'];
            }

        } else {
            return "Failed to insert in requestsLog table";
        }
    } else { //if the balance is less
        return "You have insufficient balance in your wallet to pay the bill";
    }
}

function updatePayBill($response, $serviceName, $remainingBalance) {

    global $msisdn, $amount, $serviceID, $client, $apiPassWord, $apiUserName, $accountNo, $merchantCode, $sql_conn;

    //get profile ID
    $response = $response['results'][0];
    // $sql_conn = connectDB();
    //if not successful tell the customer
    if ($response['statusCode'] == 139) {
        $query = "UPDATE samsung_emulator.requestLog set beepTrxID =" . $response['beepTransactionID'] . " ,statCode =" . $response['statusCode'] . ", statusDescription ='" . $response['statusDescription'] . "' WHERE payerTransactionID ='" . $response['payerTransactionID'] . "'";
        $sql = updateSQL($query, $sql_conn);

        if ($sql > 0) {

            $length = 7;
            $randomString = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
            $message = $randomString . " Confirmed .Ksh " . $amount . " sent to " . $serviceName . ". Your MPESA balance is KES " . $remainingBalance;
            $query = "INSERT INTO samsung_emulator.messages(sourceaddr, destaddr, messageContent, messageType, messageRead, dateCreated,profile_id) VALUES('MPESA','$msisdn','$message','1','0',now(),0)";
            $sql = insertSQL($query, $sql_conn);


            return "Your request to pay bill has been received successfully .Please wait";
        } else {
            return "Your request to pay bill has failed .Please try agains.";
        }
    } else {  ////if beep returned an error
        return "Your request to pay bill has failed .Please try again." . $response['statusDescription'];
    }
}

/**
 * @param $url
 * @param $data
 * @return mixed|string
 */
function curlUsingGet($url, $data)
{

    if(empty($url) OR empty($data))
    {
        return 'Error: invalid Url or Data';
    }

    //url-ify the data for the get  : Actually create datastring
    $fields_string = '';

    foreach($data as $key=> $value){
        $fields_string[]=$key.'='.urlencode($value).'&'; }
    $urlStringData = $url.'?'.implode('&',$fields_string);

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,10); # timeout after 10 seconds, you can increase it
    curl_setopt($ch, CURLOPT_USERAGENT , "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
    curl_setopt($ch, CURLOPT_URL, $urlStringData ); #set the url and get string together

    $return = curl_exec($ch);
    curl_close($ch);

    return $return;
}

?>
