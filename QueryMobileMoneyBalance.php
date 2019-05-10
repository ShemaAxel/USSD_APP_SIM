<?php

/**
 * Created by JetBrains PhpStorm.
 * User: root
 * Date: 11/14/13
 * Time: 12:23 PM
 * To change this template use File | Settings | File Templates.
 */
include_once '_sessions.php';
include_once '_coreCode.php';

$msisdn = $_GET['MSISDN'];
//$msisdn =254725671644;

echo getCustomerBalance();

function getCustomerBalance() {
    global $msisdn;

    $query = "SELECT mobileMoneyBalance FROM profiles WHERE MSISDN = " . $msisdn;
    $sql = selectSQL($query);
    $result = mysql_fetch_array($sql);
    $balance = number_format($result['mobileMoneyBalance'], 2);

    if (!empty($result)) {

        return "Dear Customer, Your mobile money balance is $balance/- as of " . date('d M, Y h:i A');
    } else {
        //register the customer

        return "System could not complete your mobile money balance";

        /* $query = " INSERT INTO profiles (MSISDN,networkID,dateCreated,mobileMoneyBalance, mobileMoneyBalance) VALUES ($msisdn,1,'" . date('Y:m:d H:i:s') . "',0,0) ";
        $sql = insertSQL($query);


        if (!empty($sql)) {

            $query = "SELECT mobileMoneyBalance FROM profiles WHERE profile_id= " . $sql;
            $sql = selectSQL($query);
            $result = mysql_fetch_array($sql);
            $balance = $result['mobileMoneyBalance'];
            return "Dear Customer, Your Your Mobile Money balance assss of " . date('Y:m:d H:i:s') . " is " . $balance;
        }*/
    }
}

