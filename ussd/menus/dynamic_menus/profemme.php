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

    /**
     * @var Min loan amount
     */
    private $minLoanAmount = '10000';

    /**
     * @var $maxLoanAmount
     */
    private $maxLoanAmount = '30000';

    /**
     * @var $currency
     */
    private $currencyCode = 'Rwf';

    //Choose Language
    public function startPage($input = null) {
        $this->displayText[] = 'Please choose Language, Guhitamo urumini';
        $this->displayText[] = '1. English';
        $this->displayText[] = '2. kinyarwanda';
        $this->nextFunction = 'processLanguage';
        $this->sessionState = 'CONTINUE';
    }

    //Display the different menu options depending on the chose language
    public function processLanguage($input) {
        if (!is_numeric($input) || !in_array($input, array('1', '2'))) {
            $this->displayText[] = 'Invalid option, Choose Language, Guhitamo urumini';
            $this->displayText[] = '1. English';
            $this->displayText[] = '2. kinyarwanda';
            $this->nextFunction = 'processLanguage';
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
            if (is_null($lang)) {
                $lang = include 'en.php';
            } else {
                $lang = include $lang . '.php';
            }


            $msisdn = $this->_msisdn;
            self::connect();
            //Check whether the account is activated
            $member = ORM::for_table('associationMembers')->where('MSISDN', $msisdn)->find_one();
            if ($member) {
                //Save the associationMemberID on session
                $this->saveSessionVar('assocMemberID', $member->associationMemberID);

                //Check the status of the member whether we need to activate PIN
                if ($member->active == 5) {
                    //Formulate activation SMS
                    $randomPIN = rand(1000, 9999);
                    $member->hash = md5($randomPIN);
                    $member->active = 1;
                    if ($member->save()) {
                        //Send SMS here 
                        $message = $lang['activation-sms'];
                        $message = str_replace('^MEMBER_NAME^', $member->surname, $message);
                        $message = str_replace('^PIN^', $randomPIN, $message);
                        $sms = ORM::for_table('outboundSMS')->create();
                        $sms->MSISDN = $member->MSISDN;
                        $sms->message = $message;
                        $sms->status = 0;
                        $sms->dateCreated = $sms->dateModified = date('Y-m-d H:i:s');
                        $sms->save();

                        $this->displayText[] = $lang['account-to-be-activated'];
                        $this->sessionState = 'END';
                    } else {
                        $this->displayText[] = $lang['error-activating-account'];
                        $this->sessionState = 'END';
                    }
                } elseif ($member->active == 6) {
                    $this->displayText[] = $lang['not-registered-welcome'];
                    $this->sessionState = 'END';
                } else {
                    //They can login using their PIN
                    $message = $lang['dear-member'];
                    $message = str_replace('^MEMBER_NAME^', $member->surname, $message);
                    $this->displayText[] = $message;
                    $this->displayText[] = $lang['enter-pin'];
                    $this->sessionState = 'CONTINUE';
                    $this->nextFunction = 'authenticatePIN';
                    $this->saveSessionVar('newRequest', true);
                }//if
            } else {
                $this->displayText[] = $lang['not-registered-welcome'];
                $this->sessionState = 'END';
            }
        }
    }

//processLanguage
    //Authenticate the customer PIN
    public function authenticatePIN($input) {
        self::connect();

        $lang = $this->getSessionVar('language');
        if (is_null($lang)) {
            $lang = include 'en.php';
        } else {
            $lang = include $lang . '.php';
        }

        $msisdn = $this->_msisdn;
        //Check whether the account is activated
        $member = ORM::for_table('associationMembers')->where('MSISDN', $msisdn)->find_one();
        if ($member) {
            if ($member->active == 3) {
                //Account is blocked
                $this->displayText[] = $lang['account-is-blocked'];
                $this->sessionState = 'END';
            } else {
                if ($input == '') {
                    $message = $lang['dear-member'];
                    $message = str_replace('^MEMBER_NAME^', $member->surname, $message);
                    $this->displayText[] = $message;
                    $this->displayText[] = $lang['enter-pin'];
                    $this->sessionState = 'CONTINUE';
                    $this->nextFunction = 'authenticatePIN';
                } else {
                    //If its a new session var, reset the num of tries counter
                    if ($this->getSessionVar('newRequest') && $this->getSessionVar('newRequest') == true) {
                        //Reset the counter
                        $member->numOfTries = 0;
                        $member->save();
                        $this->saveSessionVar('newRequest', false);
                    }

                    $pin = md5($input);
                    if ($pin == $member->hash && $member->numOfTries <= 3) {
                        //Display Menu options
                        $this->displayText[] = $lang['choose-option'];
                        $this->displayText[] = $lang['loan-eligibility-option'];
                        $this->displayText[] = $lang['menu-1'];
                        $this->displayText[] = $lang['menu-2'];
                        $this->displayText[] = $lang['menu-3'];
                        $this->displayText[] = $lang['menu-4'];
                        $this->displayText[] = $lang['logout'];
                        $this->sessionState = 'CONTINUE';
                        $this->nextFunction = 'serviceOptions';
                        $member->numOfTries = 0;
                        $member->save();
                    } else {
                        if ($member->numOfTries < 3) {
                            $member->numOfTries++;
                            $this->displayText[] = $lang['invalid-pin'];
                            $this->displayText[] = $lang['enter-pin'];
                            $this->sessionState = 'CONTINUE';
                            $this->nextFunction = 'authenticatePIN';
                        } else {
                            //Account is blocked                        
                            $member->numOfTries++;
                            $this->displayText[] = $lang['account-blocked'];
                            $this->sessionState = 'END';
                        }
                        $member->save();
                    }
                }
            }
        } else {
            $this->displayText[] = $lang['not-registered-welcome'];
            $this->sessionState = 'END';
        }
    }

    //Handle the service options       
    public function serviceOptions($input = null) {
        self::connect();
        $lang = $this->getSessionVar('language');
        if (is_null($lang)) {
            $lang = include 'en.php';
        } else {
            $lang = include $lang . '.php';
        }
        if ($input == "" || !in_array($input, array(1, 2, 3, 4, 5, 99))) {
            $this->displayText[] = $lang['choose-option'];
            $this->displayText[] = $lang['loan-eligibility-option'];
            $this->displayText[] = $lang['menu-1'];
            $this->displayText[] = $lang['menu-2'];
            $this->displayText[] = $lang['menu-3'];
            $this->displayText[] = $lang['menu-4'];
            $this->displayText[] = $lang['logout'];
            $this->sessionState = 'CONTINUE';
            $this->nextFunction = 'serviceOptions';
        } elseif ($input == '99') {
            $this->displayText[] = $lang['logout-text'];
            $this->sessionState = "END";
        } else {
            $member = ORM::for_table('associationMembers')->where('MSISDN', $this->_msisdn)->find_one();
            switch ($input) {
                case 1:
                    //Loan eligibility. Just display the max and min loan amounts if they dont have a loan currently
                    $glAccount = ORM::for_table('gl_accounts')->where('associationMemberID', $member->associationMemberID)->find_one();
                    //Do a check whether this member has more than 1 unrepaid loan
                    $loans = ORM::for_table('loans')->where('associationMemberID', $glAccount->associationMemberID)->where_in('status', ['4', '6'])->findMany();
                    if ($glAccount) {
                        if (($glAccount->loanID == '' || $glAccount == NULL) && count($loans) < 1) {
                            //the gl account does not have a loan
                            
                            //Get the loanTierID
                            $loanTier = ORM::for_table('loanTiers')->find_one($member->loanTierID);                            
                            
                            $message = $lang['min-max-qualified'];
                            $message = str_replace('^MIN_AMOUNT^', $loanTier->minAmount, $message);
                            $message = str_replace('^MAX_AMOUNT^', $loanTier->maxAmount, $message);
                            $this->displayText[] = $message;
                            $this->displayText[] = $lang['main-menu'];
                            $this->nextFunction = 'serviceOptions';
                            $this->sessionState = 'CONTINUE';
                        } else {
                            //Are we checking from GL account or historical
                            if ($glAccount->loanID == '' || $glAccount == NULL) {
                                $loanID = $glAccount->loanID;
                                $loan = ORM::for_table('loans')->where('associationMemberID', $glAccount->associationMemberID)->where_in('status', [4, 6])->find_one();
                                $message = $lang['have-loan-not-full'];
                                $message = str_replace('^CURRENCY_CODE^', $this->currencyCode, $message);
                                $message = str_replace('^AMOUNT^', $loan->nextDueAmount, $message);
                                $this->displayText[] = $message;
                                $this->displayText[] = $lang['main-menu'];
                                $this->nextFunction = 'serviceOptions';
                                $this->sessionState = 'CONTINUE';
                            } else {
                                $message = $lang['have-loan-not-full'];
                                //Get the existing loan details
                                $loanID = $glAccount->loanID;
                                $loan = ORM::for_table('loans')->findOne($loanID);
                                $message = str_replace('^CURRENCY_CODE^', $this->currencyCode, $message);
                                $message = str_replace('^AMOUNT^', $loan->nextDueAmount, $message);
                                $this->displayText[] = $message;
                                $this->displayText[] = $lang['main-menu'];
                                $this->nextFunction = 'serviceOptions';
                                $this->sessionState = 'CONTINUE';
                            }
                        }
                    } else {
                        $this->displayText[] = $lang['error-check-gl-account'];
                        $this->sessionState = 'END';
                    }
                    break;
                case 2:
                    //Request for loan                    
                    $pendingLoanRequest = ORM::for_table('loanRequests')->where('associationMemberID', $member->associationMemberID)->where('status', 0)->find_many();
                    if ($pendingLoanRequest) {
                        //Check their GL Account whether they have a loan attached to it
                        $this->displayText[] = $lang['pending-loan-request'];
                        $this->displayText[] = $lang['exit'];
                        $this->nextFunction = 'serviceOptions';
                        $this->sessionState = 'CONTINUE';
                    } else {

                        //Check their GL Accounts
                        //Loan eligibility. Just display the max and min loan amounts if they dont have a loan currently
                        $glAccount = ORM::for_table('gl_accounts')->where('associationMemberID', $member->associationMemberID)->find_one();
                        //Do a check whether this member has more than 1 unrepaid loan
                        $loans = ORM::for_table('loans')->where('associationMemberID', $glAccount->associationMemberID)->where_in('status', ['4', '6'])->findMany();
                        if ($glAccount) {
                            if (($glAccount->loanID == '' || $glAccount == NULL) && count($loans) < 1 ) {
                                //the gl account does not actionApprovedAshave a loan
                                //Get the loanTierID
                                $loanTier = ORM::for_table('loanTiers')->find_one($member->loanTierID);                                
                                $message = $lang['enter-loan-amount'];
                                $message = str_replace('^MIN^', $loanTier->minAmount, $message);
                                $message = str_replace('^MAX^', $loanTier->maxAmount, $message);
                                $this->displayText[] = $message;
                                $this->displayText[] = $lang['main-menu'];
                                $this->nextFunction = 'processLoanAmount';
                                $this->sessionState = 'CONTINUE';
                            } else {
                                $loan = NULL;
                                if (($glAccount->loanID == '' || $glAccount == NULL) && count($loans) >= 1) {
                                    $loan = ORM::for_table('loans')->where('associationMemberID', $glAccount->associationMemberID)->where_in('status', [4, 6])->find_one();
                                } else {
                                    //Get the loan details and inform them that they have a loan being processed  
                                    $loan = ORM::for_table('loans')->find_one($glAccount->loanID);
                                }                                
                                switch ($loan->status) {
                                    case 1:
                                    case 2:
                                        //Loan waiting association approval
                                        //Ask them to repay the existing loan
                                        $message = $lang['have-loan-awaiting-approval'];
                                        //Get the existing loan details
                                        $loanID = $glAccount->loanID;
                                        $loan = ORM::for_table('loans')->findOne($loanID);
                                        $message = str_replace('^CURRENCY_CODE^', $this->currencyCode, $message);
                                        $message = str_replace('^AMOUNT^', $loan->totalAmountDue, $message);
                                        $this->displayText[] = $message;
                                        $this->displayText[] = $lang['main-menu'];
                                        $this->nextFunction = 'serviceOptions';
                                        $this->sessionState = 'CONTINUE';
                                        break;
                                    case 3:
                                        //Loan was approved by all parties 
                                        //Ask them to repay the existing loan
                                        $message = $lang['have-loan-awaiting-disburse'];
                                        //Get the existing loan details
                                        $loanID = $glAccount->loanID;
                                        $loan = ORM::for_table('loans')->findOne($loanID);
                                        $message = str_replace('^CURRENCY_CODE^', $this->currencyCode, $message);
                                        $message = str_replace('^AMOUNT^', $loan->totalAmountDue, $message);
                                        $this->displayText[] = $message;
                                        $this->displayText[] = $lang['main-menu'];
                                        $this->nextFunction = 'serviceOptions';
                                        $this->sessionState = 'CONTINUE';
                                        break;
                                    case 6:
                                        //They have an existing loan not fully repaid
                                        //Ask them to repay the existing loan
                                        $message = $lang['have-loan-not-full'];
                                        //Get the existing loan details
                                        $loanID = $glAccount->loanID;
                                        $loan = ORM::for_table('loans')->findOne($loanID);
                                        $message = str_replace('^CURRENCY_CODE^', $this->currencyCode, $message);
                                        $message = str_replace('^AMOUNT^', $loan->nextDueAmount, $message);
                                        $this->displayText[] = $message;
                                        $this->displayText[] = $lang['main-menu'];
                                        $this->nextFunction = 'serviceOptions';
                                        $this->sessionState = 'CONTINUE';
                                        break;
                                    default:
                                        //Ask them to repay the existing loan
                                        $message = $lang['have-loan'];
                                        //Get the existing loan details
                                        $loanID = $glAccount->loanID;
                                        $loan = ORM::for_table('loans')->findOne($loanID);
                                        $message = str_replace('^CURRENCY_CODE^', $this->currencyCode, $message);
                                        $message = str_replace('^AMOUNT^', $loan->totalAmountDue, $message);
                                        $this->displayText[] = $message;
                                        $this->displayText[] = $lang['main-menu'];
                                        $this->nextFunction = 'serviceOptions';
                                        $this->sessionState = 'CONTINUE';
                                }
                            }
                        } else {
                            $associationMemberID = $member->associationMemberID;
                            $glAccount = ORM::for_table('gl_accounts')->create();
                            $glAccount->associationMemberID = $associationMemberID;
                            $glAccount->glAccountNumber = $associationMemberID;
                            $glAccount->loanID = NULL;
                            $glAccount->active = 1;
                            $glAccount->dateCreated = $glAccount->dateModified = date('Y-m-d H:i:s');
                            $glAccount->save();

                            $this->displayText[] = $lang['choose-option'];
                            $this->displayText[] = $lang['loan-eligibility-option'];
                            $this->displayText[] = $lang['menu-1'];
                            $this->displayText[] = $lang['menu-2'];
                            $this->displayText[] = $lang['menu-3'];
                            $this->displayText[] = $lang['menu-4'];
                            $this->displayText[] = $lang['logout'];
                            $this->sessionState = 'CONTINUE';
                            $this->nextFunction = 'serviceOptions';
                        }
                    }//if                    
                    break;
                case 3:
                    //Loan Status : Check the status of a loan that is being processed    
                    //Get the latest transaction from gl_loanAccounts and check the status of the transaction    
                    $glAccount = ORM::for_table('gl_accounts')->where('associationMemberID', $member->associationMemberID)->find_one();
                    if ($glAccount) {
                        if ($glAccount->loanID == '' || $glAccount == NULL) {

                            //Check loan requests whether they have a loan
                            $loanRequest = ORM::for_table('loanRequests')->where('associationMemberID', $member->associationMemberID)->where('status', 0)->order_by_desc('loanRequestID')->limit(1)->find_one();
                            if ($loanRequest) {
                                $message = $lang['loan-in-process'];
                                $message = str_replace('^CURRENCY_CODE^', $this->currencyCode, $message);
                                $message = str_replace('^AMOUNT^', $loanAccount->totalAmountDue, $message);
                                $this->displayText[] = $message;
                                $this->displayText[] = $lang['exit'];
                                $this->sessionState = 'CONTINUE';
                                $this->nextFunction = 'serviceOptions';
                            } else {
                                //They do not have any current loans
                                $this->displayText[] = $lang['no-existing-loan'];
                                $this->displayText[] = $lang['main-menu'];
                                $this->sessionState = 'CONTINUE';
                                $this->nextFunction = 'serviceOptions';
                            }
                        } else {
                            //They have a loan, whats the status
                            $loanID = $glAccount->loanID;
                            $loan = ORM::for_table('loans')->find_one($loanID);
                            if ($loan) {
                                switch ($loan->status) {
                                    case 2:
                                        $message = $lang['loan-exported'];
                                        $message = str_replace('^CURRENCY_CODE^', $this->currencyCode, $message);
                                        $message = str_replace('^AMOUNT^', $loan->totalAmountDue, $message);
                                        $this->displayText[] = $message;
                                        $this->displayText[] = $lang['main-menu'];
                                        $this->sessionState = 'CONTINUE';
                                        $this->nextFunction = 'serviceOptions';
                                        break;
                                    case 4:
                                    case 3:
                                        $message = $lang['loan-processed'];
                                        $message = str_replace('^CURRENCY_CODE^', $this->currencyCode, $message);
                                        $message = str_replace('^AMOUNT^', $loan->totalAmountDue, $message);
                                        $this->displayText[] = $message;
                                        $this->displayText[] = $lang['main-menu'];
                                        $this->sessionState = 'CONTINUE';
                                        $this->nextFunction = 'serviceOptions';
                                        break;
                                    case 6:
                                        //Loan partially repaid
                                        //They have an existing loan not fully repaid
                                        //Ask them to repay the existing loan
                                        $message = $lang['have-loan-not-full'];
                                        //Get the existing loan details                                                                                
                                        $message = str_replace('^CURRENCY_CODE^', $this->currencyCode, $message);
                                        $message = str_replace('^AMOUNT^', $loan->nextDueAmount, $message);
                                        $this->displayText[] = $message;
                                        $this->displayText[] = $lang['main-menu'];
                                        $this->nextFunction = 'serviceOptions';
                                        $this->sessionState = 'CONTINUE';
                                        break;
                                    case 1:
                                    case 0:
                                    default:
                                        //Pending approval by association
                                        $message = $lang['loan-logged'];
                                        $message = str_replace('^CURRENCY_CODE^', $this->currencyCode, $message);
                                        $message = str_replace('^AMOUNT^', $loan->totalAmountDue, $message);
                                        $this->displayText[] = $message;
                                        $this->displayText[] = $lang['main-menu'];
                                        $this->sessionState = 'CONTINUE';
                                        $this->nextFunction = 'serviceOptions';
                                }
                            } else {
                                $this->displayText[] = $lang['no-existing-loan'];
                                $this->displayText[] = $lang['main-menu'];
                                $this->sessionState = 'CONTINUE';
                                $this->nextFunction = 'serviceOptions';
                            }
                        }
                    }

                    break;
                case 4:
                    //Mini statement, Get the last 5 transaction from gl_loanAccounts
                    $loanAccounts = ORM::for_table('loans')->where('associationMemberID', $member->associationMemberID)->order_by_desc('loanID')->limit(5)->find_many();
                    if (count($loanAccounts) >= 1) {
                        $this->displayText[] = $lang['mini-statement'];
                        foreach ($loanAccounts as $loan) {
                            $this->displayText[] = 'Rwf ' . $loan->totalAmountDue . ' :' . date('Y/m/d', strtotime($loan->nextDueDate));
                        }//foreach
                        $this->displayText[] = $lang['exit'];
                        $this->sessionState = 'CONTINUE';
                        $this->nextFunction = 'serviceOptions';
                    } else {
                        $this->displayText[] = $lang['no-existing-transactions'];
                        $this->displayText[] = $lang['exit'];
                        $this->sessionState = 'CONTINUE';
                        $this->nextFunction = 'serviceOptions';
                    }
                    break;
                case 5:
                    //About
                    $msisdn = $this->_msisdn;
                    //Get the member details                                        
                    $this->displayText[] = $lang['member-details'];
                    $this->displayText[] = $member->surname . ', ' . $member->otherNames;
                    //$this->displayText[] = $lang['association'] . ': ' . ($this->getAssociationName($member->associationID)) ? $this->getAssociationName($member->association) : '';
                    $this->displayText[] = $lang['association'] . ': ' . $this->getAssociationName($member->associationID);
                    $this->displayText[] = $lang['date-registered'] . ':' . date('Y/m/d', strtotime($member->dateCreated));
                    $this->displayText[] = "\n" . $lang['exit'];
                    $this->nextFunction = 'serviceOptions';
                    $this->sessionState = 'CONTINUE';
            }//switch            
        }
    }

//serviceOptions
    //Process Loan Amount
    public function processLoanAmount($input) {
        self::connect();
        $lang = $this->getSessionVar('language');
        if (is_null($lang)) {
            $lang = include 'en.php';
        } else {
            $lang = include $lang . '.php';
        }
        if ($input == '') {
            $this->displayText[] = $lang['invalid-option'];
            $this->displayText[] = $lang['enter-loan-amount'];
            $this->displayText[] = $lang['exit'];
            $this->nextFunction = 'processLoanAmount';
            $this->sessionState = 'CONTINUE';
        }//if
        //Handle main menu input
        //Check that the request amount are within the set limits
        $member = ORM::for_table('associationMembers')->where('MSISDN', $this->_msisdn)->find_one();
        $loanTier = ORM::for_table('loanTiers')->find_one($member->loanTierID);
        if ($input == '00') {
            //Go back to main menu
            //Display Menu options
            $this->displayText[] = $lang['choose-option'];
            $this->displayText[] = $lang['loan-eligibility-option'];
            $this->displayText[] = $lang['menu-1'];
            $this->displayText[] = $lang['menu-2'];
            $this->displayText[] = $lang['menu-3'];
            $this->displayText[] = $lang['menu-4'];
            $this->displayText[] = $lang['logout'];
            $this->sessionState = 'CONTINUE';
            $this->nextFunction = 'serviceOptions';
        } elseif ($input < $loanTier->maxAmount || $input > $loanTier->minAmount) {
            $message = $lang['enter-loan-amount'];
            $message = str_replace('^MIN^',$loanTier->minAmount, $message);
            $message = str_replace('^MAX^',$loanTier->maxAmount, $message);
            $this->displayText[] = $message;
            $this->displayText[] = $lang['exit'];
            $this->nextFunction = 'processLoanAmount';
            $this->sessionState = 'CONTINUE';
        } else {
            //Have the member confirm that the entered amount is correct
            $message = $lang['confirm-loan-request'];
            $this->displayText[] = str_replace('^AMOUNT^', $input, $message);
            $this->displayText[] = $lang['main-menu'];
            $this->sessionState = 'CONTINUE';
            $this->saveSessionVar('requestedAmount', $input);
            $this->nextFunction = 'confirmLoanRequest';
        }
    }

//processLoanAmount

    public function confirmLoanRequest($input) {
        self::connect();
        if (is_null($lang)) {
            $lang = include 'en.php';
        } else {
            $lang = include $lang . '.php';
        }

        $requestedAmount = $this->getSessionVar('requestedAmount');
        if ($input == '') {
            $this->displayText[] = $lang['invalid-option'];
            $message = $lang['confirm-loan-request'];
            $this->displayText[] = str_replace('^AMOUNT^', $nput, $message);
            $this->displayText[] = $lang['main-menu'];
            $this->sessionState = 'CONTINUE';
            $this->saveSessionVar('requestedAmount', $input);
            $this->nextFunction = 'confirmLoanRequest';
        } else {
            switch ($input) {
                case 1:
                    //has confirmed request for loan
                    $associationMember = ORM::for_table('associationMembers')->where('MSISDN', $this->_msisdn)->find_one();
                    //throw new Exception('Error .' . $this->_msisdn);
                    //Save the loan Request for app profiling
                    $loanRequest = ORM::for_table('loanRequests')->create();
                    $loanRequest->associationMemberID = $associationMember->associationMemberID;
                    $loanRequest->loanAmount = $requestedAmount;
                    $loanRequest->metadata = '';
                    $loanRequest->status = 0;
                    $loanRequest->dateCreated = $loanRequest->dateModified = date('Y-m-d H:i:s');
                    if ($loanRequest->save()) {
                        $message = $lang['loan-logged'];
                        $message = str_replace('^CURRENCY_CODE^', $this->currencyCode, $message);
                        $message = str_replace('^AMOUNT^', $loanRequest->loanAmount, $message);
                        $this->displayText[] = $message;
                        $this->displayText[] = $lang['exit'];
                        $this->displayText[] = $lang['logout'];
                        $this->nextFunction = 'serviceOptions';
                        $this->sessionState = 'CONTINUE';
                    } else {
                        //The loan could not be logged for some reason
                        $this->displayText[] = $lang['error-processing-request'];
                        $this->displayText[] = $lang['main-menu'];
                        $this->nextFunction = 'serviceOptions';
                        $this->sessionState = 'CONTINUE';
                    }
                    break;
                default:
                    $this->displayText[] = $lang['choose-option'];
                    $this->displayText[] = $lang['loan-eligibility-option'];
                    $this->displayText[] = $lang['menu-1'];
                    $this->displayText[] = $lang['menu-2'];
                    $this->displayText[] = $lang['menu-3'];
                    $this->displayText[] = $lang['menu-4'];
                    $this->displayText[] = $lang['logout'];
                    $this->sessionState = 'CONTINUE';
                    $this->nextFunction = 'serviceOptions';
            }
        }
    }

    public function processLoanOptions($input) {
        $lang = $this->getSessionVar('language');
        if (is_null($lang)) {
            $lang = include 'en.php';
        } else {
            $lang = include $lang . '.php';
        }
        if ($input == "") {
            $this->displayText[] = $lang['invalid-option'];
            $this->displayText[] = $lang['request-loan'];
            $this->displayText[] = $lang['repay-loan'];
            $this->displayText[] = $lang['check-limit'];
            $this->displayText[] = $lang['exit'];
            $this->displayText[] = $lang['logout'];
            $this->nextFunction = 'processLoanOptions';
            $this->sessionState = 'CONTINUE';
        } else {
            switch ($input) {
                //Request for Loan
                //@todo Run checks here to see whether the member qualifies for loan and upto what amount
                case 1:
                    $this->displayText[] = $lang['enter-loan-amount'];
                    $this->displayText[] = $lang['main-menu'];
                    $this->displayText[] = $lang['exit'];
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
                default:
                    $this->displayText[] = $lang['invalid-option'];
                    $this->displayText[] = $lang['request-loan'];
                    $this->displayText[] = $lang['repay-loan'];
                    $this->displayText[] = $lang['check-limit'];
                    $this->displayText[] = $lang['exit'];
                    $this->displayText[] = $lang['logout'];
                    $this->nextFunction = 'processLoanOptions';
                    $this->sessionState = 'CONTINUE';
            }
        }
    }

//processLoanOptions

    public function processLoanRequest($input) {
        $lang = $this->getSessionVar('language');
        if (is_null($lang)) {
            $lang = include 'en.php';
        } else {
            $lang = include $lang . '.php';
        }
        self::connect();
        if ($input == "") {
            $this->displayText[] = $lang['invalid-option'];
            $this->displayText[] = $lang['request-loan'];
            $this->displayText[] = $lang['repay-loan'];
            $this->displayText[] = $lang['check-limit'];
            $this->displayText[] = $lang['exit'];
            $this->displayText[] = $lang['logout'];
            $this->nextFunction = 'processLoanOptions';
            $this->sessionState = 'CONTINUE';
        } else {
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
                    $this->displayText[] = $lang['welcome-customer'];
                    $this->displayText[] = $lang['withdraw-1'];
                    $this->displayText[] = $lang['withdraw-2'];
                    $this->displayText[] = $lang['withdraw-3'];
                    $this->displayText[] = $lang['withdraw-4'];
                    $this->displayText[] = $lang['exit'];
                    $this->nextFunction = 'processDashboard';
                    $this->sessionState = 'CONTINUE';
                } else {
                    //Log a Request to the Loan Requests tables
                    $loanAmount = (float) $input;
                    $msisdn = $this->_msisdn;
                    $member = ORM::for_table('associationMembers')->where('MSISDN', $msisdn)->find_one();

                    $loanRequest = ORM::for_table('loanRequests')->create();
                    $loanRequest->associationMemberID = $member->associationMemberID;
                    $loanRequest->loanAmount = $loanAmount;
                    $loanRequest->metadata = '{}';
                    $loanRequest->status = 0;
                    $loanRequest->dateCreated = $loanRequest->dateModified = date('Y-m-d H:i:s');
                    $loanRequest->save();

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
    }

//processLoanRequest
    //Database connection here
    static function connect() {
        ORM::configure('mysql:host=localhost;dbname=kwigira');
        ORM::configure('username', 'kwigira');
        ORM::configure('password', 'kw1g1rA');

        //Override the defaults
        ORM::configure('id_column_overrides', array(
            'associationMembers' => 'associationMemberID',
            'outboundSMS' => 'outboundSMSID',
            'loanRequests' => 'loanRequestID',
            'associations' => 'associationID',
            'loans' => 'loanID',
            'gl_accounts' => 'glAccountID',
            'loanTiers' => 'loanTierID',
        ));
    }

    private function translate($text, $language) {
        if ($text == null) {
            return $text;
        }

        //Get the translation
        $translation = require_once($language . '.php');
        return $translation[$text];
    }

    //Get the name of an association using the associationID
    private function getAssociationName($associationID) {
        self::connect();
        $association = ORM::for_table('associations')->where('associationID', $associationID)->find_one();
        if ($association) {
            return $association->associationName;
        } else {
            return false;
        }
    }

}

$menu = new Kwigira();
echo $menu->navigate();


