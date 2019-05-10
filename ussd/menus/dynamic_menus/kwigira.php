<?php

/**
 * Kwigira
 * 
 * @author Maritim, Kip <kip@hotcash.co.ke>
 * Copyright Tele10 2016
 * 
 */
include_once('DynamicMenuController.php');
include_once('./../idiorm/idiorm.php');

class Kwigira extends DynamicMenuController {

    //Choose Language
    public function startPage($input = null) {
        $this->displayText[] = 'Please choose Language';
        $this->displayText[] = '1. English';
        $this->displayText[] = '2. kinyarwanda';
        $this->nextFunction = 'enterPIN';
        $this->sessionState = 'CONTINUE';
    }

    //start page
    public function enterPIN($input = null) {
        if (!is_numeric($input) || !in_array($input, array('1','2'))) {
            $this->displayText[] = 'Invalid option, Please choose Language';
            $this->displayText[] = '1. English';
            $this->displayText[] = '2. kinyarwanda';
            $this->nextFunction = 'enterPIN';
            $this->sessionState = 'CONTINUE';
        } else {
            switch ($input) {
                case 2:
                    $this->saveSessionVar('language', 'rw');
                    break;
                case 1:
                default:
                    $this->saveSessionVar('language', 'en');
            }//switch 
            
            $lang = $this->getSessionVar('language');
            if(is_null($lang)) {
                $lang = include 'en.php';
            } else {
                $lang = include $lang.'.php';                
            }


            $msisdn = $this->_msisdn;
            self::connect();
            //Check whether the account is activated
            $member = ORM::for_table('associationMembers')->where('MSISDN', $msisdn)->find_one();
            if ($member) {
                $this->displayText[] = $lang['dear-member'];
                $this->displayText[] = $lang['enter-pin'];
                $this->sessionState = 'CONTINUE';
                $this->nextFunction = 'processPIN';
            } else {
                $this->displayText[] = 'Dear customer, Kindly visit your association office to register for Kwigira and access mobile loans';
                $this->sessionState = 'END';
            }
        }
    }

    //Process PIN
    public function processPIN($input = null) {
        $lang = $this->getSessionVar('language');
        if (is_null($lang)) {
            $lang = include 'en.php';
        } else {
            $lang = include $lang . '.php';
        }


        $msisdn = $this->_msisdn;
        if (!is_numeric($input) || strlen($input) < 4 || $input == '') {
            $this->displayText[] = "Sorry, you entered incorrectly, \n " . $lang['enter-pin'];
            $this->nextFunction = 'processPIN';
            $this->sessionState = 'CONTINUE';
        } else {
            //Authenticate PIN
            self::connect();
            $member = ORM::for_table('associationMembers')->where('MSISDN', $msisdn)->find_one();
            if (md5($input) == $member->hash) {
                $this->displayText[] = $lang['welcome-customer'] . ' ' . $member->surname;
                $this->displayText[] = $lang['withdraw-1'];
                $this->displayText[] = $lang['withdraw-2'];
                $this->displayText[] = $lang['withdraw-3'];
                $this->displayText[] = $lang['withdraw-4'];
                $this->displayText[] = $lang['exit'];
                $this->nextFunction = 'processDashboard';
                $this->sessionState = 'CONTINUE';
            } else {
                $this->displayText[] = $lang['enter-pin'];
                $this->nextFunction = 'processPIN';
                $this->sessionState = 'CONTINUE';
            }
        }
    }

    //Process Dashboard
    public function processDashboard($input = null) {
        $lang = $this->getSessionVar('language');
        if (is_null($lang)) {
            $lang = include 'en.php';
        } else {
            $lang = include $lang . '.php';
        }
        if ($input == '00') {
            $this->displayText[] = 'Thank you for choosing Kwigira. Bye';
            $this->sessionState = 'END';
        } elseif ($input == '99') {
            self::connect();
            $member = ORM::for_table('associationMembers')->where('MSISDN', $msisdn)->find_one();
            $this->displayText[] = $lang['welcome-customer'] . ' ' . $member->surname;
            $this->displayText[] = $lang['withdraw-1'];
            $this->displayText[] = $lang['withdraw-2'];
            $this->displayText[] = $lang['withdraw-3'];
            $this->displayText[] = $lang['withdraw-4'];
            $this->displayText[] = $lang['exit'];
            $this->nextFunction = 'processDashboard';
            $this->sessionState = 'CONTINUE';
        } else {
            $msisdn = $this->_msisdn;
            self::connect();
            $member = ORM::for_table('associationMembers')->where('MSISDN', $msisdn)->find_one();
            if (!is_numeric($input) || $input == '') {
                $this->displayText[] = $lang['welcome-customer'] . ' ' . $member->surname;
                $this->displayText[] = $lang['withdraw-1'];
                $this->displayText[] = $lang['withdraw-2'];
                $this->displayText[] = $lang['withdraw-3'];
                $this->displayText[] = $lang['withdraw-4'];
                $this->displayText[] = $lang['exit'];
                $this->nextFunction = 'processDashboard';
                $this->sessionState = 'CONTINUE';
            } else {
                switch ($input) {
                    case '1':
                        $this->displayText[] = 'Select Mobile Money Service';
                        $this->displayText[] = '1. Airtel Money';
                        $this->displayText[] = '2. MTN Money';
                        $this->displayText[] = '99. Main Menu';
                        $this->displayText[] = "00. Exit";
                        $this->nextFunction = 'processMobileMoney';
                        $this->sessionState = "CONTINUE";
                        break;
                    case 2:
                        $this->displayText[] = $lang['select-loan-option'];
                        $this->displayText[] = $lang['request-loan'];
                        $this->displayText[] = $lang['repay-loan'];
                        $this->displayText[] = $lang['check-limit'];
                        $this->displayText[] = $lang['main-menu'];
                        $this->displayText[] = $lang['exit'];
                        $this->nextFunction = 'processLoanOptions';
                        $this->sessionState = 'CONTINUE';
                        break;

                    case 3:
                        $this->displayText[] = 'Your Kwigira Account Balance is RWF 12,400';
                        $this->displayText[] = '99. Main Menu';
                        $this->displayText[] = '00. Exit';
                        $this->sessionState = 'CONTINUE';
                        break;
                    case 4:
                        $this->displayText[] = 'Your Kwigira Mini Statement';
                        $this->displayText[] = "RWF 10,240 - Credit";
                        $this->displayText[] = '99. Main Menu';
                        $this->displayText[] = '00. Exit';
                        $this->sessionState = 'CONTINUE';
                        break;
                    default:
                        $this->displayText[] = 'Thank you and goodbye';
                        $this->sessionState = 'END';
                }
            }
        }
    }

    function processMobileMoney($input = null) {
        $lang = $this->getSessionVar('language');
        if (is_null($lang)) {
            $lang = include 'en.php';
        } else {
            $lang = include $lang . '.php';
        }
        $msisdn = $this->_msisdn;
        self::connect();
        $member = ORM::for_table('associationMembers')->where('MSISDN', $msisdn)->find_one();
        if ($input == '00') {
            $this->displayText[] = 'Thank you for choosing Kwigira. Bye';
            $this->sessionState = 'END';
            exit();
        } elseif ($input == '99') {            
            $this->displayText[] = $lang['welcome-customer'] . ' ' . $member->surname;
            $this->displayText[] = $lang['withdraw-1'];
            $this->displayText[] = $lang['withdraw-2'];
            $this->displayText[] = $lang['withdraw-3'];
            $this->displayText[] = $lang['withdraw-4'];
            $this->displayText[] = $lang['exit'];
            $this->nextFunction = 'processDashboard';
            $this->sessionState = 'CONTINUE';
            exit();
        }

        if (!is_numeric($input) || $input == '') {
            $this->displayText[] = 'Invalid option. Select Mobile Money Service';
            $this->displayText[] = '1. Airtel Money';
            $this->displayText[] = '2. MTN Money';
            $this->displayText[] = '99. Main Menu';
            $this->displayText[] = "00. Exit";
            $this->nextFunction = 'processMobileMoney';
            $this->sessionState = 'CONTINUE';
        } else {
            switch ($input) {
                case 1:
                    $this->displayText[] = 'Cash out Kwigira via Airtel Money. Bye';
                    $this->sessionState = 'END';
                    break;
                case 2:
                    $this->displayText[] = 'Enter 1 to confirm transfer to MTN Money';
                    $this->displayText[] = '99. Main Menu';
                    $this->displayText[] = "00. Exit";
                    $this->sessionState = 'CONTINUE';
                    $this->nextFunction = 'processTransferToMM';
                    break;
                default:
                    $this->displayText[] = 'Invalid option. Select Mobile Money Service';
                    $this->displayText[] = '1. Airtel Money';
                    $this->displayText[] = '2. MTN Money';
                    $this->displayText[] = '99. Main Menu';
                    $this->displayText[] = "00. Exit";
                    $this->nextFunction = 'processMobileMoney';
                    $this->sessionState = 'CONTINUE';
            }
        }
    }

    //Transfer to MM
    public function processTransferToMM($input = null) {
        $lang = $this->getSessionVar('language');
        if (is_null($lang)) {
            $lang = include 'en.php';
        } else {
            $lang = include $lang . '.php';
        }
        $msisdn = $this->_msisdn;
        self::connect();
        $member = ORM::for_table('associationMembers')->where('MSISDN', $msisdn)->find_one();

        if ($input == '00') {
            $this->displayText[] = 'Thank you for choosing Kwigira. Bye';
            $this->sessionState = 'END';
        } elseif ($input == '99') {
            $this->displayText[] = $lang['welcome-customer'] . ' ' . $member->surname;
            $this->displayText[] = $lang['withdraw-1'];
            $this->displayText[] = $lang['withdraw-2'];
            $this->displayText[] = $lang['withdraw-3'];
            $this->displayText[] = $lang['withdraw-4'];
            $this->displayText[] = $lang['exit'];
            $this->nextFunction = 'processDashboard';
            $this->sessionState = 'CONTINUE';
        } else {
            if (!is_numeric($input) || $input == '') {
                $this->displayText[] = 'Invalid Option, Enter 1 to confirm transfer to MTN Money';
                $this->displayText[] = '99. Main Menu';
                $this->displayText[] = "00. Exit";
                $this->sessionState = 'CONTINUE';
                $this->nextFunction = 'processTransferToMM';
            } else {
                switch ($input) {
                    case '1':
                        //Log an SMS to Send                    
                        $sms = ORM::for_table('outboundSMS')->create();
                        $sms->MSISDN = $msisdn;
                        $sms->message = 'Your request to Cash out to MTN money is being processed. You will receive a confirmation shortly';
                        $sms->status = 0;
                        $sms->dateCreated = $sms->dateModified = date('Y-m-d H:i:s');
                        $sms->save();

                        $this->displayText[] = 'Your request to MTN Money is being processed';
                        $this->sessionState = 'END';
                        break;
                    default:
                        $this->displayText[] = 'Invalid Option, Enter 1 to confirm transfer to MTN Money';
                        $this->displayText[] = '99. Main Menu';
                        $this->displayText[] = "00. Exit";
                        $this->sessionState = 'CONTINUE';
                        $this->nextFunction = 'processTransferToMM';
                }
            }
        }
    }

    //Process Loan Options
    function processLoanOptions($input = null) {
        $lang = $this->getSessionVar('language');
        if (is_null($lang)) {
            $lang = include 'en.php';
        } else {
            $lang = include $lang . '.php';
        }
        $msisdn = $this->_msisdn;
        self::connect();
        $member = ORM::for_table('associationMembers')->where('MSISDN', $msisdn)->find_one();

        if ($input == '00') {
            $this->displayText[] = 'Thank you for choosing Kwigira. Bye';
            $this->sessionState = 'END';
        } elseif ($input == '99') {
            $this->displayText[] = $lang['welcome-customer'] . ' ' . $member->surname;
            $this->displayText[] = $lang['withdraw-1'];
            $this->displayText[] = $lang['withdraw-2'];
            $this->displayText[] = $lang['withdraw-3'];
            $this->displayText[] = $lang['withdraw-4'];
            $this->displayText[] = $lang['exit'];
            $this->nextFunction = 'processDashboard';
            $this->sessionState = 'CONTINUE';
        } else {
            if (!is_numeric($input) || $input == '') {
                $this->displayText[] = $lang['welcome-customer'] . ' ' . $member->surname;
                $this->displayText[] = $lang['withdraw-1'];
                $this->displayText[] = $lang['withdraw-2'];
                $this->displayText[] = $lang['withdraw-3'];
                $this->displayText[] = $lang['withdraw-4'];
                $this->displayText[] = $lang['exit'];
                $this->nextFunction = 'processDashboard';
                $this->sessionState = 'CONTINUE';
            } else {
                switch ($input) {
                    case 1:
                        $this->displayText[] = $lang['enter-loan-amount'];
                        $this->displayText[] = $lang['main-menu'];
                        $this->displayText[] = $lang['exit'];;
                        $this->nextFunction = 'processLoanRequest';
                        $this->sessionState = 'CONTINUE';
                        break;
                    case 2:
                        $this->displayText[] = $lang['pay-back-loan'];
                        $this->sessionState = "END";
                        break;
                    case 3:
                        $this->displayText[] = 'Your loan limit is RWF 50,000';
                        $this->sessionState = "END";
                        break;
                    default :
                        $this->displayText[] = 'Invalid Option, Select Loan Option';
                        $this->displayText[] = '1. Request for a Loan';
                        $this->displayText[] = '2. Re-pay Loan';
                        $this->displayText[] = '3. Check loan limit';
                        $this->displayText[] = '99. Main Menu';
                        $this->displayText[] = "00. Exit";
                        $this->nextFunction = 'processLoanOptions';
                        $this->sessionState = 'CONTINUE';
                }
            }
        }
    }

    //Process Loan request
    function processLoanRequest($input = null) {
        $lang = $this->getSessionVar('language');
        if (is_null($lang)) {
            $lang = include 'en.php';
        } else {
            $lang = include $lang . '.php';
        }
        $msisdn = $this->_msisdn;
        self::connect();
        $member = ORM::for_table('associationMembers')->where('MSISDN', $msisdn)->find_one();

        if ($input == '00') {
            $this->displayText[] = 'Thank you for choosing Kwigira. Bye';
            $this->sessionState = 'END';
            exit();
        } elseif ($input == '99') {
            $this->displayText[] = $lang['welcome-customer'] . ' ' . $member->surname;
                $this->displayText[] = $lang['withdraw-1'];
                $this->displayText[] = $lang['withdraw-2'];
                $this->displayText[] = $lang['withdraw-3'];
                $this->displayText[] = $lang['withdraw-4'];
                $this->displayText[] = $lang['exit'];
                $this->nextFunction = 'processDashboard';
                $this->sessionState = 'CONTINUE';
            exit();
        } else {
            if (!is_numeric($input) || $input == '') {
                $this->displayText[] = $lang['welcome-customer'] . ' ' . $member->surname;
                $this->displayText[] = $lang['withdraw-1'];
                $this->displayText[] = $lang['withdraw-2'];
                $this->displayText[] = $lang['withdraw-3'];
                $this->displayText[] = $lang['withdraw-4'];
                $this->displayText[] = $lang['exit'];
                $this->nextFunction = 'processDashboard';
                $this->sessionState = 'CONTINUE';
            } else {
                $sms = ORM::for_table('outboundSMS')->create();
                $sms->MSISDN = $msisdn;
                $sms->message = $lang['loan-processed'];
                $sms->status = 0;
                $sms->dateCreated = $sms->dateModified = date('Y-m-d H:i:s');
                $sms->save();

                $sms = ORM::for_table('outboundSMS')->create();
                $sms->MSISDN = $msisdn;
                $sms->message = $lang['received-1'] . ' ' . $input . ' ' . $lang['received-2'];
                $sms->status = 0;
                $sms->dateCreated = $sms->dateModified = date('Y-m-d H:i:s');
                $sms->save();

                $this->displayText[] = $lang['loan-processed'];
                $this->sessionState = 'END';
            }
        }
    }

    //Database connection here
    static function connect() {
        ORM::configure('mysql:host=localhost;dbname=kwigira');
        ORM::configure('username', 'kwigira');
        ORM::configure('password', 'kw1g1rA');
    }

    private function translate($text, $language) {
        if ($text == null) {
            return $text;
        }

        //Get the translation
        $translation = require_once($language.'.php');                
        return $translation[$text];
               
    }

}

$menu = new Kwigira();
echo $menu->navigate();
