<?php
/**
 * Created by JetBrains PhpStorm.
 * User: root
 * Date: 11/15/13
 * Time: 3:00 PM
 * To change this template use File | Settings | File Templates.
 */

if ($_GET['ACCOUNT_NUMBER'] == "") {
    echo  "Account number is not specified";
    exit;
}

if ($_GET['AMOUNT'] == "") {
    echo  "Amount is not specified";
    exit;
}

if ($_GET['PAYBILL_NO'] == "") {
    echo  "Pay bill Number is not specified";
    exit;
}

echo "Your request to pay bill to Account No.  " . $_GET['ACCOUNT_NUMBER'] . " Amount " .$_GET['AMOUNT'] . " to ".$_GET['MSISDN'] ." will be processed shortly";
