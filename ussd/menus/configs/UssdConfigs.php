<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

define('LOG_DIRECTORY', '/var/log/applications/rw/kwigira/ussd/');
define('SYSTEM_LOG_LEVEL', 10);
define('NL', "\n");
define('TB', "");
define('USSD_LOG_PROPERTIES', dirname(__FILE__) . '/ussd_logs.php');

// Various HTTP Error response messages for the Dynamic Menu Controller class
define('TIMEOUT_DURATION', 5); //response timeout duration in sec
define('CONNECT_TIMEOUT', 0); //connect duration timeout.

define('LOG_RESPONSE_TIME_OUT', 'Dynamic controller : Response Timeout-Dear Customer, we are experiencing technical difficulties. Please try again later');
define('LOG_CHANNEL_REQUEST_ERROR_MESSAGE', 'Log channel Requests err-Dear Customer, we are experiencing technical difficulties. Please try again later');
define('LOG_CONNECTION_TIME_OUT', 'Dynamic controller:Connect timout error-Dear Customer, we are experiencing technical difficulties. Please try again later');
define('FETCH_SYSTEM_URL_ERR_MESSAGE', 'Dynamic controller : Fetch System URL err-Dear Customer, we are experiencing technical difficulties. Please try again later');
define('UNKNOWN_HTTP_ERR_MESSAGE', 'Dynamic controller : Unknown HTTP err code-Dear Customer, we are experiencing technical difficulties. Please try again later');
define('DMC_NO_PAGE_ERROR', '404');

// The time in minutes to which a session should last.
define('SESSION_EXPIRY_DURATION', 5);

//client function types routing configs 
define('EXTERNAL_MENU_CLIENT_FUNCTION_TYPE_ID', 2);
define('INTERNAL_MENU_CLIENT_FUNCTION_TYPE_ID', 9);

// These contants are used by the activity class
define('GATEWAYID', 3);
define('GATEWAYUID', 0);
define('DMC_APPID', 0);
define('PHP_POST_PROTOCOL_ID', 1);
define('XML_RPC_PROTOCOL_ID', 3);
define('IMCID', 1);

// Various status codes used by ussd
define('SUCCESS_UPDATE_STATUS', 1);
define('TIME_OUT_ERROR_STATUS', 11);
define('ROUTE_NOT_FOUND_STATUS', 6);
define('INTERNAL_SERVER_ERROR_STATUS', 7);
define('UNKNOWN_ERROR_STATUS', 0);
define('IMSI_KEY_ID', 39); // Added the IMSI KEY ID
define('IMSI_CUSTOMER_CONSENT', 40); // Added the IMSI KEY ID

/**
 * This is used to identify the request. (Panama Request)
 */
define('PANAMA_REQUEST_ID', 15);
/**
 * Defined Pin length
 */
define('MINPINLength', 4);

/**
 * Defined Pin length
 */
define('MAXPINLength', 8);
/**
 * ID used to identify Panama KYC level in attributes tables.
 */
define('PANAMA_KYC_LEVEL', 42);
define('PANAMA_AUTH', 45);
define('PANAMA_CUSTOMER_ID_NUMBER', 50);

/**
 * This key will be used to prevent spamming
 */
define('PANAMA_CUSTOMER_REG_LIMIT', 54);

define('PANAMA_CUSTOMER_BANK_NAME', 52);

/**
 * Customer bank account number
 */
define('PANAMA_CUSTOMER_BANK_ACCOUNT_NUMBER', 53);
/**
 * Panama transaction history
 */
define('PANAMA_TRANSACTION_HISTORY', 49);
/**
 * AirTime Transaction limit without pin
 */
define('KYC_PIN_LESS_LIMIT', 500);
/**
 * AirTime Transaction limit with pin
 */
define('KYC_PIN_LIMIT', 5000);
/**
 * AirTime Transaction limit
 */
define('KYC_UNREGISTERED_LIMIT', 250);
/**
 * This is used by ChannelRequestPusher daemon to identify the kind of transaction
 */
define('ACTION_REGISTRATION', 17);
/**
 * This is used by ChannelRequestPusher daemon to identify the kind of transaction
 */
define('ACTION_TRANSACTION', 18);
/**
 * This is used by ChannelRequestPusher daemon to identify an activation request
 */
define('ACTION_ACTIVATION_REQUEST', 20);
/**
 * Default KYC Level
 */
define('DEFAULT_KYC_LEVEL', 'D');

//Panama Session Variable Constants.
/**
 *Amount Session Key
 */
define('KEY_AMOUNT', 'amount');
/**
 *User KYC_LEVEL Session Key
 */
define('KEY_users_KYC_level', 'kyc_level');
/**
 *Selected Service Session Key
 */
define('KEY_USER_SERVICE', 'user_service');
/**
 *Selected ServiceID Session Key
 */
define('KEY_USER_SERVICEID', 'user_service_id');
/**
 *Selected Self Service Session Key
 */
define('KEY_USER_SELF_SERVICE', 'self_service');
/**
 *Selected self ServiceID Session Key
 */
define('KEY_USER_SELF_SERVICE_ID', 'self_service_id');
/**
 *Service Amount Session Key
 */
define('KEY_SERVICE_ACCOUNT', 'service_account');
/**
 *Hashed Pin Session Key
 */
define('KEY_HASHED_PIN', 'hashedPin');
/**
 *Initial pin Session Key
 */
define('KEY_INITIAL_PIN', 'initialPin');
/**
 *Selected bank Session Key
 */
define('KEY_SELECTED_BANK', 'selectedBank');
/**
 *SElected bank ID Session Key
 */
define('KEY_SELECTED_BANK_KEY', 'selectedBankKey');
/**
 *User ID Number Session Key
 */
define('KEY_ID_NUMBER', 'idNumber');
/**
 *New ID Session Key
 */
define('KEY_ID_NUMBER_NEW', 'idNumberNew');
/**
 *User Account Number Session Key
 */
define('KEY_ACCOUNT_NUMBER', 'accountNumber');
/**
 *New Account Number Session Key. Account Re-entered during pin validation.
 */
define('KEY_ACCOUNT_NUMBER_NEW', 'accountNumberNew');
/**
 *User Account Alias Session Key
 */
define('KEY_ACCOUNT_ALIAS', 'accountAlias');
/**
 *Account Session Key
 */
define('ACCOUNT_ID', 'accountID');
/**
 *Client ID Session Key
 */
define('USERS_CLIENT_ID', 'userClientID');
/**
 *Customer Names Session Key
 */
define('KEY_CUSTOMER_NAME', 'customerNames');
/**
 *Client profile ID Session Key
 */
define('KEY_CLIENT_PROFILE_ID', 'clientProfileID');
/**
 *Profile ID Session Key
 */
define('KEY_PROFILE_ID', 'profileID');
/**
 *Customer accounts Session Key
 */
define('KEY_CUSTOMER_ACCOUNTS', 'customerAccounts');
/**
 *User has more that one account Session Key
 */
define('KEY_HAS_MORE_THAN_ONE_ACCOUNT', 'hasMoreThanOneAccount');
/**
 *User has more than one client profile Session Key
 */
define('KEY_HAS_MORE_THAN_ONE_CLIENT_PROFILE', 'hasMoreThanOneClientProfile');
/**
 *Stored Pin Session Key
 */
define('STORED_PIN', 'storedPin');
/**
 *Pin Display message Session Key
 */
define('PIN_DISPLAY_MESSAGE', 'PIN');
/**
 *Mobile banking Session Key.
 * store this in session so that we will know if the mobile banking pin was used
 */
define('IS_MOBILE_BANKING_PIN', 'isMobileBankingPin');
/**
 *Pin request Session Key
 */
define('REQUEST_FOR_PIN', 'requestForPin');
/**
 * Pinless transaction Session Key
 */
define('PIN_LESS_TRANSACTION', 'pinLessTransaction');
/**
 * Post to Self Registration Session Key.
 * This variable will hold the check to post request to self reg, this ensures we don't break the UXP
 */
define('POST_REQUEST_TO_SELF_REG', 'postToSelfReg');
/**
 *In Line Service Session Key
 */
define('KEY_INLINE_SERVICE', 'inlineService');
/**
 *Bank list Session Key
 */
define('BANK_LIST', 'bankList');
/**
 *Service list Session Key
 */
define('SERVICE_LIST', 'serviceList');
/**
 *Service Limits Session Key
 */
define('SERVICE_LIMIT', 'serviceLimits');
/**
 *Selected Account ID Session Key
 */
define('KEY_SELECTED_ACCOUNT_KEY', 'selectedBankIDKey');
/**
 *User Account Number Session Key
 */
define('KEY_USER_ACCOUNT_NUMBER', 'userAccountNumber');
/**
 *Sur name Session Key
 */
define('KEY_SURNAME', 'userSurName');
/**
 *User ID Number Session Key
 */
define('KEY_USER_ID_NUMBER', 'userIdNumber');
/**
 *Function key Session Key.This is used when the a render function is called. Stores the name of the
 * function that called it.
 */
define('KEY_FUNCTION', 'function');
/**
 *Key used when saving Service Amount
 */
define('KEY_SERVICE_AMOUNT', 'serviceAmount');
/**
 *User old pin session variable
 */
define('KEY_OLD_PIN', 'oldPin');
/**
 *User new pin session variable
 */
define('KEY_NEW_PIN', 'newPin');
/**
 * Required when user is performing a Change/Reset pin request.
 */
define('KEY_PIN_TWO', 'pinTwo');
/**
 *Self Service List
 */
define('SELF_SERVICE_LIST', 'selfServiceList');
/**
 *String paymentGatewayURL
 */
define('PANAMA_GATEWAY_URL', 'paymentGatewayURL');
/**
 * @var boolean enforceNumericPINS
 */
define('ENFORCE_NUMERIC_PIN', 'enforceNumericPINS');
/**
 *String Country Currency Code
 */
define('CURRENCY', 'currency');
/**
 * @var bool Checks if the requests supports SSL
 */
define('IS_SSL', 'isSSL');
/**
 * @var int Airtime Service Code.
 */
define('AIRTIME_SERVICE_CODE', 'airtimeServiceCode');
/**
 * @var int Mobile Money code.
 */
define('MOBILE_MONEY_CODE', 'mobileMoneyCode');
/**
 * @var null This will be the key to hold the service code.
 */
define('SERVICE_CODE', 'serviceCode');

//IMSI Configs
/**
 * @var int Status code when there the consent is null.
 */
define('RESULT_SET_NULL', 'resultSetNull');
/**
 * @var int user Accepted IMSI tracking status code.
 */
define('ACCEPTED_CONSENT', 'acceptedConsent');
/**
 * @var int user declined IMSI Tracking status code
 */
define('DECLINED_CONSENT', 'declinedConsent');
/**
 * @var int unknown error code
 */
define('UNKNOWN_ERROR', 'unknownError');
/**
 * @var int User is active on Mobile banking
 */
define('TARIFF_ACTIVE', 'tariffActive');
/**
 * @var int User is on mobile banking but you might not be active
 */
define('TARIFF_MOBILE_BANKING', 'tariffMobileBanking');
/**
 * @var int Required account number of digits
 */
define('ACCOUNT_NUMBER_DIGIT', "accountNumberDigits");
/**
 *String Fetch Bills Username
 */
define('CREDENTIALS_USER_NAME', 'credentialsUserName');
/**
 *String  Fetch bills Password
 */
define('CREDENTIALS_PASSWORD', 'credentialsPassword');
/**
 * @var boolean Is boolean Enabled or disabled.
 */
define('ENABLE_IMISI', 'enableIMSI');
/**
 * @var bool sets if the user is required to provide the account number for the selected service.
 */
define('SERVICE_NEEDS_ACCOUNT', 'service_needs_account');
/**
 * @var bool Sets if the selected service needs an amount.
 */
define('SERVICE_NEEDS_AMOUNT', 'service_needs_amount');
/**
 * @var int Default service lower range amount.
 */
define('DEFAULT_SERVICE_LOWER_RANGE', 'defaultServiceLowerRange');
/**
 * @var int Total Panama transaction in a single day.
 */
define('DAY_TRANSACTION_AMOUNT', 'dayTransactionAmount');
/**
 * @var int Total number of transaction made in the month using panama service.
 */
define('MONTHLY_TRANSACTION_COUNT', 'monthlyTransactionCount');
/**
 * @var int Total monthly transaction amount
 */
define('MONTHLY_TRANSACTION_AMOUNT', 'monthlyTransactionAmount');
/**
 * @var int Maximum number of dial attempts
 */
define('MAX_DIAL_COUNT', 'maxDialCount');
/**
 * @var null Total day user transaction count.
 */
define('DAY_TRANSACTION_COUNT', 'dayTransactionCount');
/**
 * @var null Last Panama transaction date.
 */
define('LAST_TRANSACTION_DATE', 'lastTransactionDate');
/**
 * @var bool Set's if user is registered. Default is false
 */
define('USER_HAS_REGISTERED', 'user_has_registered');
/**
 * @var bool If the customer has more than once account, enforce choosing an account
 */
define('USER_HAS_SELECTED_ACCOUNT', 'userHasSelectedAccount');
/**
 * @var null User Client ID
 */
define('USER_CLIENT_ID', 'users_clientID');

/**
 *String Default account. Used until the user changes pin.
 */
define('DEFAULT_PIN', 'defaultPin');
/**
 *int Change pin request ServiceID
 */
define('CHANGE_PIN_SERVICE_ID', 'changePinServiceID');
/**
 *int Registration limit.
 */
define('REGISTRATION_LIMIT', 'registrationLimits');
?>
