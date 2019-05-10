<?php

//Process Airtime Recharge request
//@author Maritim, Kiprotich


class B2C {

	private $params;
	
	//Class constructor
	function __construct($params) {
		$this->setParams($params);
	}//__construct
	
	function beginProcessing() {
		//Get all the parameters that we need
		flog(mnoLogs, 'Processing B2C request');
		$params = $this->getParams();
		
		$amount = $this->array_var($params, 'amount');
		$MSISDN = $this->array_var($params, 'MSISDN');

		//Fetch the current account balance on this account
		flog(mnoLogs, 'Lets try find the customer profile');
		$query = "SELECT * FROM profiles WHERE MSISDN = '" . $MSISDN . "'limit 1 ";
		$queryResult = selectSQL($query);
		$result = mysql_fetch_assoc($queryResult);
		if(isset($result['profileID'])) {

                        $profileID = $result['profileID'];
			flog(mnoLogs, 'The customer profile exists, update their account with amount of ' . $amount);			$result = mysql_fetch_assoc($queryResult);
			$mobileMoneyBalance = (int) $result['mobileMoneyBalance'];
			
			//increment airtime balance by value
			$mobileMoneyBalance = $mobileMoneyBalance + $amount;
			flog(mnoLogs, 'New account balance is ' . $mobileMoneyBalance);
			//Make some top up requests fail
			/*if(($amount % 13) == 0) {
				flog(mnoLogs, 'This request is configured to fail');
				return false;
			}*/
			
			//Insert the new value into the database
			$query = "UPDATE profiles set mobileMoneyBalance = '" . $mobileMoneyBalance . "' WHERE MSISDN = '" . $MSISDN . "' LIMIT 1";
			$updateResult = updateSQL($query);
			if($updateResult) {

                         $length = 7;
                         $randomString = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
                         $message = $randomString." Confirmed .You have received Ksh ".$amount." From Paybill. Your MPESA balance is ".$mobileMoneyBalance;         
                         $query = "INSERT INTO samsung_emulator.messages(sourceaddr, destaddr, messageContent, messageType, messageRead, dateCreated) VALUES('MPESA','$MSISDN','$message','1','1',now(),$profileID)";
                         $sql = insertSQL($query);


				flog(mnoLogs, 'Successfully update the customer profile mobile money account balance');
				return array('status'=>true,'receiptNumber'=>$randomString);
			} else {
				flog(mnoLogs, 'The 	Mobile money account balance could not be updated');
				return array('status'=>false);
			}
		} else {
			//Create the customer profile if it does not exist
			flog(mnoLogs, 'The customer profile does not exist, lets create it');
			$query = "insert into profiles(MSISDN, networkID,airtimeBalance, mobileMoneyBalance, dateCreated) values($MSISDN,1,0,0,now())";
			if($resultInsert = insertSQL($query)) {
				//Fetch the current account balance on this account
				flog(mnoLogs, 'The customer profile was successfully created');
				$query = "SELECT * FROM profiles WHERE MSISDN = '" . $MSISDN . "'limit 1 ";
				$queryResult = selectSQL($query);
				if($queryResult) {
					$result = mysql_fetch_assoc($queryResult);
					$mobileMoneyBalance = (int) $result['mobileMoneyBalance'];
						
					//increment airtime balance by value
					$mobileMoneyBalance = $mobileMoneyBalance + $amount;
						
					//Make some top up requests fail
					/*if(($amount % 13) == 0) {
						flog(mnoLogs, 'The request is configured to fail');
						return false;
					}*/
						
					//Insert the new value into the database
					$query = "UPDATE profiles set mobileMoneyBalance = '" . $mobileMoneyBalance . "' WHERE MSISDN = '" . $MSISDN . "' LIMIT 1";
					$updateResult = updateSQL($query);
					if($updateResult) {
                                                 $length = 7;
                                                 $randomString = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
                                                 $message = $randomString." Confirmed .You have recieved Ksh ".$amount." From Paybill. Your MPESA balance is ".$mobileMoneyBalance;         
                                                 $query = "INSERT INTO samsung_emulator.messages(sourceaddr, destaddr, messageContent, messageType, messageRead, dateCreated,profile_id) VALUES('MPESA','$MSISDN','$message','1','1',now(),$resultInsert)";
                                                 $sql = insertSQL($query);
						flog(mnoLogs, 'Successfully update the mobile money account balance');
						return array('status'=>true,'receiptNumber'=>$randomString);
					} else {
						flog(mnoLogs, 'An error occured while trying to update the mobile money account balance');
						return array('status'=>false);
					}
				}
			}
			flog(mnoLogs, 'could not create the customer account balance, check fatal logs for error description');
			return array('status'=>false);
		}
	}
	
	//--------------------------------------------------
	//	Getters and setters
	//--------------------------------------------------
	private function setParams($params) {
		$this->params = $params;
	}
	
	private function getParams() {
		return $this->params;
	}
	
	private function array_var(&$from, $name, $default = null) {
		if (is_array($from)) {
			return isset($from[$name]) ? $from[$name] : $default;
		}
		return $default;
	} // array_var
}
