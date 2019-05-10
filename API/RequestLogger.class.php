<?php

/**
 * Log requests sent to the MNO
 * 
 * @author Maritim, Kiprotich <maritim.kiprotich@cellulant.com>
 */
include_once '../_coreCode.php';

class RequestLogger {

    private $MSISDN;
    private $amount;
    private $transactionTypeID;
    private $transactionStatusID;
    private $statusCode = null;
    private $beepTransactionID;
    private $merchantCode = null;
    private $accountNumber = null;

    function __construct($MSISDN, $amount, $transactionTypeID, $beepTransactionID, $merchantCode = null, $accountNumber = null, $statusCode = null) {
        $this->setMSISDN($MSISDN);
        $this->setAmount($amount);
        $this->setTransactionTypeID($transactionTypeID);
        //$this->setTransactionStatusID($statusCode);		
        $this->setBeepTransactionID($beepTransactionID);
        if ($merchantCode != null) {
            $this->setMerchantCode($merchantCode);
        }//if
        if ($accountNumber != null) {
            $this->setAccountNumber($accountNumber);
        }//if		
        if ($statusCode != null) {
            $this->setStatusCode($statusCode);
        }//if
    }

    //Log the request and return the logRequestID
    function logRequest() {
        $transactionType = $this->getTransactionTypeID();
        $MSISDN = $this->getMSISDN();
        $amount = $this->getAmount();
        $beepTransactionID = $this->getBeepTransactionID();
        $merchantCode = $this->getMerchantCode() != null ? $this->getMerchantCode() : 0;
        $accountNumber = $this->getAccountNumber() != null ? $this->getAccountNumber() : 0;
        flog(mnoLogs, 'Creating a request log');
        $query = "INSERT INTO MNORequestLogs (transactionType, beepTrxID, MSISDN, amount,merchantCode,accountNumber,dateCreated, dateModified) values('$transactionType', '$beepTransactionID', '$MSISDN', '$amount','$merchantCode','$accountNumber', now(),now())";
        //Execute the query
        $queryResult = insertSQL($query);
        if ($queryResult) {
            flog(mnoLogs, 'The request log has successfully been created ID => ' . mysql_insert_id());
            //Return the insert ID
            return array(
                'SUCCESS' => true,
                'INSERTID' => mysql_insert_id(),
            );
        } else {
            flog(mnoLogs, 'Failed to create the request log');
            return array(
                'SUCCESS' => false,
                'ERROR_DESC' => mysql_error(),
            );
        }
    }

//logRequest
    //update the statuses of this request after it has been processed
    static function updateRequestLogStatus($requestLogID, $statusCode, $statusDescription) {
        flog(mnoLogs, 'Updating the request log with final status');
        //Save the request
        $query = "UPDATE MNORequestLogs set statCode = '$statusCode', statusDescription = '$statusDescription'  WHERE requestLogID = $requestLogID limit 1";
        $queryResult = updateSQL($query);
    }

//updateRequestLog
    //+-------------------------------------
    //| Getters and setters 
    //+-------------------------------------
    private function setMSISDN($MSISDN) {
        $this->MSISDN = $MSISDN;
    }

    private function getMSISDN() {
        return $this->MSISDN;
    }

    private function setAmount($amount) {
        $this->amount = $amount;
    }

    private function getAmount() {
        return $this->amount;
    }

    private function setTransactionTypeID($transactionTypeID) {
        $this->transactionTypeID = $transactionTypeID;
    }

    private function getTransactionTypeID() {
        return $this->transactionTypeID;
    }

    private function setTransactionStatusID($statusCode) {
        $this->transactionStatusID = $statusCode;
    }

    private function getTransactionStatusID() {
        return $this->transactionStatusID;
    }

    private function setStatusCode($statusCode) {
        $this->statusCode = $statusCode;
    }

    private function getStatusCode() {
        return $this->statusCode;
    }

    private function setStatusDescription($statusDescription) {
        $this->statusDescription = $statusDescription;
    }

    private function getStatusDescription() {
        $this->statusDescription;
    }

    private function setBeepTransactionID($beepTransactionID) {
        $this->beepTransactionID = $beepTransactionID;
    }

    private function getBeepTransactionID() {
        return $this->beepTransactionID;
    }

    private function setMerchantCode($merchantCode) {
        $this->merchantCode = $merchantCode;
    }

    private function getMerchantCode() {
        return $this->merchantCode;
    }

    private function setAccountNumber($accountNumber) {
        $this->accountNumber = $accountNumber;
    }

    private function getAccountNumber() {
        return $this->accountNumber;
    }

}
