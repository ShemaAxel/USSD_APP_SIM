<?php

//Include the DB manager
require_once '../_sessions.php';
require_once '../_coreCode.php';

//Fetch the system configs
$config = (include 'config.php');

//Request Logger
include_once 'RequestLogger.class.php';

//Airtime processor
include_once 'processAirtimeRequest/ProcessAirtimeRequest.class.php';
include_once 'payBill/PayBill.class.php';
include_once 'B2C/B2C.class.php';

//What parameters are we expecting
$serviceID = null;
$statusCode = null;
$statusCodeDescription = null;
$invoiceNumber = null;
$accountNumber = null;
$beepTransactionID = null;
$narration = null;
$amount = null;
$currencyCode = null;
$payerClientCode = null;
$payerTransactionID = null;
$MSISDN = null;
$merchantCode = null;

//Get the individual $_GET params
foreach ($_GET as $k => $v) {
    //$keyName = $k;
    ${$k} = $v;
}//foreach
flog(mnoLogs, 'MNO has receieved a request, params => ' . var_dump($_GET));

$params = array(
    'serviceID' => $serviceID,
    'MSISDN' => $MSISDN,
    'amount' => $amount,
);

//Get the service we are processing
if ($serviceID != null) {
    switch ($serviceID) {
        case $config['airtimeRechargeService']['ID']:
            flog(mnoLogs, 'Processing Airtime recharge');
            //Log the request
            $requestLogger = new RequestLogger($MSISDN, $amount, $config['airtimeRechargeService']['transactionType'], $beepTransactionID, $merchantCode, $accountNumber);
            $requestLog = $requestLogger->logRequest();
            flog(mnoLogs, 'Request log response => ' . print_r($requestLog));
            if ($requestLog['SUCCESS'] != true) {
                //We failed trying to insert the request Log
                $response = array(
                    'statusCode' => $config['statusCodes']['FAIL']['CODE'],
                    'statusDescription' => $config['statusCodes']['FAIL']['DESC'],
                    'payerTransactionID' => null,
                    'invoiceNumber' => null,
                    'beepTransactionID' => $beepTransactionID,
                    'amountExpected' => $amount,
                    'isTokenService' => 0,
                );
            }//if
            $airtimeProcessor = new ProcessAirtimeRequest($params);
            $result = $airtimeProcessor->beginProcessing();
            $response = null;
            if ($result['SUCCESS'] == true) {
                $response = array(
                    'statusCode' => $config['statusCodes']['SUCCESS']['CODE'],
                    'statusDescription' => $config['statusCodes']['SUCCESS']['DESC'],
                    'payerTransactionID' => $requestLog['INSERTID'],
                    'invoiceNumber' => $requestLog['INSERTID'],
                    'beepTransactionID' => $beepTransactionID,
                    'amountExpected' => $amount,
                    'isTokenService' => 0,
                );
                $requestLogger = RequestLogger::updateRequestLogStatus($requestLog['INSERTID'], $config['statusCodes']['SUCCESS']['CODE'], $config['statusCodes']['SUCCESS']['DESC']);
            } else {
                $response = array(
                    'statusCode' => $config['statusCodes']['FAIL']['CODE'],
                    'statusDescription' => $config['statusCodes']['FAIL']['DESC'],
                    'payerTransactionID' => $requestLog['INSERTID'],
                    'invoiceNumber' => $requestLog['INSERTID'],
                    'beepTransactionID' => $beepTransactionID,
                    'amountExpected' => $amount,
                    'isTokenService' => 0,
                );
                $requestLogger = RequestLogger::updateRequestLogStatus($requestLog['INSERTID'], $config['statusCodes']['FAIL']['CODE'], $config['statusCodes']['FAIL']['DESC']);
            }//if
            //Update the request log with the status of this response			
            ob_clean();
            echo json_encode($response);
            die;
            break;
        case $config['payBillService']['ID']:
            flog(mnoLogs, 'Process Bill pay service');
            //Log the request
            $requestLogger = new RequestLogger($MSISDN, $amount, $config['payBillService']['transactionType'], $beepTransactionID, $merchantCode, $accountNumber);
            $requestLog = $requestLogger->logRequest();
            flog(mnoLogs, 'Request log response = ' . $requestLog['SUCCESS']);
            if ($requestLog['SUCCESS'] != true) {
                flog(mnoLogs, 'An error occured while trying to log the request');
                //We failed trying to insert the request Log
                $response = array(
                    'statusCode' => $config['statusCodes']['FAIL']['CODE'],
                    'statusDescription' => $config['statusCodes']['FAIL']['DESC'],
                    'payerTransactionID' => null,
                    'invoiceNumber' => null,
                    'beepTransactionID' => $beepTransactionID,
                    'amountExpected' => $amount,
                    'isTokenService' => 0,
                );
            }//if
            flog(mnoLogs, 'Starting to process the paybill request');
            $payBillProcessor = new Paybill($params);
            $payBillResult = $payBillProcessor->beginProcessing();
            $response = null;
            if (is_array($payBillResult) && $payBillResult['SUCCESS']) {
                $response = array(
                    'statusCode' => $config['statusCodes']['SUCCESS']['CODE'],
                    'statusDescription' => $config['statusCodes']['SUCCESS']['DESC'],
                    'payerTransactionID' => $requestLog['INSERTID'],
                    'invoiceNumber' => $requestLog['INSERTID'],
                    'beepTransactionID' => $beepTransactionID,
                    'amountExpected' => $amount,
                    'isTokenService' => 0,
                );
                $requestLogger = RequestLogger::updateRequestLogStatus($requestLog['INSERTID'], $config['statusCodes']['SUCCESS']['CODE'], $config['statusCodes']['SUCCESS']['DESC']);
            } else {
                $response = array(
                    'statusCode' => $config['statusCodes']['FAIL']['CODE'],
                    'statusDescription' => $config['statusCodes']['FAIL']['DESC'],
                    'payerTransactionID' => $requestLog['INSERTID'],
                    'invoiceNumber' => $requestLog['INSERTID'],
                    'beepTransactionID' => $beepTransactionID,
                    'amountExpected' => $amount,
                    'isTokenService' => 0,
                );
                $requestLogger = RequestLogger::updateRequestLogStatus($requestLog['INSERTID'], $config['statusCodes']['FAIL']['CODE'], $config['statusCodes']['FAIL']['DESC']);
            }//if
            ob_clean();
            echo json_encode($response);
            die;
            break;

        //B2C transactions
        case $config['B2CService']['ID']:
            flog(mnoLogs, 'We are processing a B2C request');
            //Log the request
            $requestLogger = new RequestLogger($MSISDN, $amount, $config['B2CService']['transactionType'], $beepTransactionID, $merchantCode, $accountNumber);
            $requestLog = $requestLogger->logRequest();
            if ($requestLog['SUCCESS'] != true) {
                flog(mnoLogs, 'We could not create a request log');
                //We failed trying to insert the request Log
                $response = array(
                    'statusCode' => $config['statusCodes']['FAIL']['CODE'],
                    'statusDescription' => $config['statusCodes']['FAIL']['DESC'],
                    'payerTransactionID' => null,
                    'invoiceNumber' => null,
                    'beepTransactionID' => $beepTransactionID,
                    'amountExpected' => $amount,
                    'isTokenService' => 0,
                );
            }//if
            $b2cProcessor = new B2C($params);
            $result = $b2cProcessor->beginProcessing();
            $response = null;
            if (is_array($result) && $result['status']) {

                $response = array(
                    'statusCode' => $config['statusCodes']['SUCCESS']['CODE'],
                    'statusDescription' => $config['statusCodes']['SUCCESS']['DESC'],
                    'payerTransactionID' => $requestLog['INSERTID'],
                    'invoiceNumber' => $requestLog['INSERTID'],
                    'beepTransactionID' => $beepTransactionID,
                    'amountExpected' => $amount,
                    'isTokenService' => 0,
                    'receiptNumber' => $result['receiptNumber'],
                );
                $requestLogger = RequestLogger::updateRequestLogStatus($requestLog['INSERTID'], $config['statusCodes']['SUCCESS']['CODE'], $config['statusCodes']['SUCCESS']['DESC']);
            } else {
                $response = array(
                    'statusCode' => $config['statusCodes']['FAIL']['CODE'],
                    'statusDescription' => $config['statusCodes']['FAIL']['DESC'],
                    'payerTransactionID' => $requestLog['INSERTID'],
                    'invoiceNumber' => $requestLog['INSERTID'],
                    'beepTransactionID' => $beepTransactionID,
                    'amountExpected' => $amount,
                    'isTokenService' => 0,
                    'receiptNumber' => NULL,
                );
                $requestLogger = RequestLogger::updateRequestLogStatus($requestLog['INSERTID'], $config['statusCodes']['FAIL']['CODE'], $config['statusCodes']['FAIL']['DESC']);
            }//if
            //Update the request log with the status of this response
            ob_clean();
            echo json_encode($response);
            die;
            break;
        default:
            $response = array(
                'statusCode' => $config['statusCodes']['INVALIDSERVICEID']['CODE'],
                'statusDescription' => $config['statusCodes']['INVALIDSERVICEID']['DESC'],
                'payerTransactionID' => null,
                'invoiceNumber' => null,
                'beepTransactionID' => $beepTransactionID,
                'amountExpected' => $amount,
                'isTokenService' => 0,
            );
            ob_clean();
            echo json_encode($response);
            die;
    }//switch
} else {
    $response = array(
        'statusCode' => $config['statusCodes']['INVALIDSERVICEID']['CODE'],
        'statusDescription' => $config['statusCodes']['INVALIDSERVICEID']['DESC'],
        'payerTransactionID' => null,
        'invoiceNumber' => null,
        'beepTransactionID' => $beepTransactionID,
        'amountExpected' => $amount,
        'isTokenService' => 0,
    );
    ob_clean();
    echo json_encode($response);
    die;
}



