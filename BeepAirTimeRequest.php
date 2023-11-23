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

//$msisdn =254725671644;
//$amount =100;

$serviceID = beep_airtime_service_id;
$apiUserName = beep_user;
$apiPassWord = beep_password;
$client = new IXR_client(beep_url);


echo logAirtimeRequest();

//insert into requests
function logAirtimeRequest() {
    global $msisdn, $amount, $serviceID, $client, $apiPassWord, $apiUserName;


    $invoiceNo = bin2hex(openssl_random_pseudo_bytes(2));
    $invoiceNo = $invoiceNo . "-" . $msisdn;

    //generate a random transactionID
    $payerTrx = bin2hex(openssl_random_pseudo_bytes(7));
    $date = date('Y-m-d H:i:s');
    // $dbLink= connectDB();
    $query = "INSERT INTO requestLog(amount,MSISDN,transactionTypeID,payerTransactionID,invoiceNumber,dateCreated) VALUES('$amount','$msisdn',1,'$payerTrx','$invoiceNo','$date')";
    $sql = insertSQL($query);

    //check if inserted
    if ($sql) {
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
                    'accountNumber' => $msisdn,
                    'payerTransactionID' => $payerTrx,
                    'amount' => $amount,
                    'narration' => 'Airtime TopUp',
                    'datePaymentReceived' => date('Y-m-d H:i:s'),
                    'currencyCode' => 'KES',
                    'paymentMode' => 'Mobile'
                )
            )
        );

        if (!$client->query('BEEP.postPayment', $payload)) {
            //die('An error occurred - ' . $client->getErrorCode() . ":" . $client->getErrorMessage());

            flog(fatalLogs, "postPayment() |" . sprintf(" %01.4f ", $time) . "| " . $client->getErrorCode() . " -" . $client->getErrorMessage());

            return "Your request to topup has failed .Please try again";
        } else {
            $response = $client->getResponse();
            return updateAirTime($response);
        }
    } else {
        return "Failed to insert in requestsLog table";
    }
    //print_r($response);exit;
}

function updateAirTime($response) {

    $response = $response['results'][0];
     //if not successful tell the customer
    if ($response['statusCode'] == 139) {
        $query = "UPDATE requestLog set beepTrxID =" . $response['beepTransactionID'] . " ,statCode =" . $response['statusCode'] . ", statusDescription ='" . $response['statusDescription'] . "' WHERE payerTransactionID ='" . $response['payerTransactionID'] . "'";
        $sql = updateSQL($query);
        if ($sql > 0) {
            return "Your request to topup has been received successfully .Please wait";
        } else {
            return "Your request to topup has failed .Please try again";
        }
    } else {  ////if beep returned an error
        return "Your request to topup has failed .Please try again." . $response['statusDescription'];
    }
}

?>
