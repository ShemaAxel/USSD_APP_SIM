<?php

//MNO simulator configurations
$server = "192.168.254.66";
return array(
    'api' => "http://$server/samsung-simulator/API/index.php",
    'airtimeRechargeService' => array(
        'ID' => 33,
        'transactionType' => 'Airtime Recharge',
    ),
    'payBillService' => array(
        'ID' => 1,
        'transactionType' => 'Bill Payment'
    ),
    'B2CService' => array(
        'ID' => 35,
        'transactionType' => 'Bank to MPesa'
    ),
    'statusCodes' => array(
        'SUCCESS' => array(
            'CODE' => '140',
            'DESC' => 'The Payment was success',
        ),
        'FAIL' => array(
            'CODE' => '141',
            'DESC' => 'General Failure',
        ),
        'INVALIDSERVICEID' => array(
            'CODE' => '141',
            'DESC' => 'Sorry, could not identify the requested service',
        ),
        'GENERICFAILURE' => array(
            'CODE' => 800,
            'DESC' => 'Generic system reject for this request'
        ),
        'PROFILENOTFOUND' => array(
            'CODE' => 801,
            'DESC' => "Requested customer profile cannot be found",
        ),
        'MERCHANTPROFILENOTFOUND' => array(
            'CODE' => 802,
            'DESC' => 'The requested merchant profile cannot be found',
        ),
    ),
);
