<?php

//Process Airtime Recharge request
//@author Maritim, Kiprotich


class ProcessAirtimeRequest {

	private $params;
	
	//Class constructor
	function __construct($params) {
		$this->setParams($params);
	}//__construct
	
	function beginProcessing() {
                 session_start();
                 $profileID =  $_SESSION['user_profileID'];
		//Get all the parameters that we need
		$params = $this->getParams();
		
		$amount = $this->array_var($params, 'amount');
		$MSISDN = $this->array_var($params, 'MSISDN');

		//Fetch the current account balance on this account
		$query = "SELECT * FROM profiles WHERE MSISDN = ' " . $MSISDN . "'limit 1 ";
		$queryResult = selectSQL($query);
		$result = mysql_fetch_assoc($queryResult);
		if(isset($result['profileID'])) {
 
                        $profileID = $result['profileID'];
			
			$airtimeBalance = (int) $result['airtimeBalance'];
			
			//increment airtime balance by value
			$airtimeBalance = $airtimeBalance + $amount;
			
			//Make some top up requests fail
			/*if($amount % 13 == 0) {
				return array(
						'SUCCESS' => false,
						'STATUS_CODE' => 800,						
				);
			}*/
			
			//Insert the new value into the database
			$query = "UPDATE profiles set airtimeBalance = '" . $airtimeBalance . "' WHERE MSISDN = '" . $MSISDN . "' LIMIT 1";
			$updateResult = updateSQL($query);
			if($updateResult) {
                                                
                                                  $message = " Recharge  of ".$amount." successful.Your new balance as of ".date('Y-m-d H:i:s')."is ".$airtimeBalance;         
                                                 $query = "INSERT INTO samsung_emulator.messages(sourceaddr, destaddr, messageContent, messageType, messageRead, dateCreated,profile_id) VALUES('AIRTIME','$MSISDN','$message','1','0',now(),$profileID)";
                                                 $sql = insertSQL($query);


				return array(
						'SUCCESS' => true,
						'STATUS_CODE' => 140,						
				);
			} else {
				return array(
						'SUCCESS' => false,
						'STATUS_CODE' => 801,						
				);
			}
		} else {
			//We could not fetch the result for some reason
			//Create the customer profile if it does not exist
			$query = "insert into profiles(MSISDN, networkID,airtimeBalance, mobileMoneyBalance, dateCreated) values($MSISDN,1,0,0,now())";
			if($resultInsert = insertSQL($query)) {
				//Fetch the current account balance on this account
				$query = "SELECT * FROM profiles WHERE MSISDN = ' " . $MSISDN . "'limit 1 ";
				$queryResult = selectSQL($query);
                             
				if($queryResult) {
					$result = mysql_fetch_assoc($queryResult);
					$airtimeBalance = (int) $result['airtimeBalance'];
						
					//increment airtime balance by value
					$airtimeBalance = $airtimeBalance + $amount;
						
					//Make some top up requests fail
					/*if($amount % 13 == 0) {
						return array(
						'SUCCESS' => false,
						'STATUS_CODE' => 800,						
				);
					}*/
						
					//Insert the new value into the database
					$query = "UPDATE profiles set airtimeBalance = '" . $airtimeBalance . "' WHERE MSISDN = '" . $MSISDN . "' LIMIT 1";
					$updateResult = updateSQL($query);
					if($updateResult) {
                                                 $message = " Recharge of ".$amount." successful.Your new balance as of ".date('Y-m-d H:i:s')." is ".$airtimeBalance;         
                                                 $query = "INSERT INTO samsung_emulator.messages(sourceaddr, destaddr, messageContent, messageType, messageRead, dateCreated,profile_id) VALUES('AIRTIME','$MSISDN','$message','1','0',now(),$resultInsert)";
                                                 $sql = insertSQL($query);

						return array(
						'SUCCESS' => true,
						'STATUS_CODE' => 140,						
				);
					} else {
						return array(
						'SUCCESS' => false,
						'STATUS_CODE' => 801,						
				);
					}
				}
			}//if
			return array(
						'SUCCESS' => false,
						'STATUS_CODE' => 802,						
				);
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
