<?php

/**
 * Paybill manager
 * 
 * @author Maritim, Kiprotich <maritim.kiprotich@cellulant.com>
 */

class PayBill {
	
	/**
	 * @var $params
	 */
	private $params;
	
	//Class constructor
	function __construct($params) {
		$this->setParams($params);
	}//__construct
	
	function beginProcessing() {
		//Get all the parameters that we need
		$params = $this->getParams();
		flog(mnoLogs, 'Paybill request parameters = Amount:' .$amount . ' MSISDN:' . $MSISDN );
		$amount = $this->array_var($params, 'amount');
		$MSISDN = $this->array_var($params, 'MSISDN');
		$merchantCode = $this->array_var($params, 'merchantCode');
		$accountNumber = $this->array_var($params, 'accountNumber');

		//Deduct the Paybill amount from this profile
		//Fetch the current account balance on this account
		$query = "SELECT * FROM profiles WHERE MSISDN = ' " . $MSISDN . "'limit 1 ";
		$queryResult = selectSQL($query);
		$result = mysql_fetch_assoc($queryResult);
		if(isset($result['profileID'])) {		
			flog(mnoLogs, 'The customers profile has been found');
			$originalBalance = $mobileMoneyBalance = (int) $result['mobileMoneyBalance'];
				
					
			//Make some transactions fail (Any number that is a divisible of 13)
			if($amount % 13 == 0) {	
				flog(mnoLogs, 'Generic system failure, this request is configured to fail');
				return array(
						'SUCCESS' => false,
						'STATUS_CODE' => 800,						
				);
			}
				
			//Insert the new value into the database
			//$query = "UPDATE profiles set mobileMoneyBalance = '" . $mobileMoneyBalance . "' WHERE MSISDN = '" . $MSISDN . "' LIMIT 1";
			//$updateResult = updateSQL($query);
			$updateResult = true;
			if($updateResult) {
				//We now need need to increment the balance on the Merchant account
				
				$query = "SELECT * FROM merchantProfiles WHERE merchantProfileID = 1 limit 1 ";
				$queryResult = selectSQL($query);
				if($queryResult) {
					$result = mysql_fetch_assoc($queryResult);
					flog(mnoLogs, 'Trying to update the merchant account balance');
					$accountBalance = $result['accountBalance'] + $amount;
					$query = "UPDATE merchantProfiles set accountBalance = '" . $accountBalance . "' WHERE merchantProfileID = 1 LIMIT 1";
					$updateResult = updateSQL($query);
					if($updateResult) {
						flog(mnoLogs, 'Merchant account has been successfully updated');
						//We have successfully updated all account balances
						return array(
								'SUCCESS' => true,
								'STATUS_CODE' => 200,
								);
					} else {
						flog(mnoLogs, 'An error occured while trying to increment the merchant account balance');
						//could not update the merchant account balance
						$query = "SELECT * FROM profiles WHERE MSISDN = ' " . $MSISDN . "'limit 1 ";
						$queryResult = selectSQL($query);
						$result = mysql_fetch_assoc($queryResult);
						$mobileMoneyBalance = $result['mobileMoneyBalance'] + $amount;
						//$query = "UPDATE profiles set mobileMoneyBalance = '" . $mobileMoneyBalance . "' WHERE MSISDN = '" . $MSISDN . "' LIMIT 1";
						//$updateResult = updateSQL($query);
						return array(
								'SUCCESS' => false,
								'STATUS_CODE' => 141,
						);
					}
				} else {
					//The merchant does not exist
					flog(mnoLogs, 'the requested merchant account does not exist');
					return array(
							'SUCCESS' => false,
							'STATUS_CODE' => 802,
					);
				}//if			
				
			} else {
				return array(
						'SUCCESS' => false,
						'STATUS_CODE' => 141,
				);
			}
		} else {
			flog(mnoLogs, 'The customer profile account does not exist, let us try to create it');
			//Create the customer profile if it does not exist
			$query = "insert into profiles(MSISDN, networkID,airtimeBalance, mobileMoneyBalance, dateCreated) values($MSISDN,1,0,0,now())";
			if($result = insertSQL($query)) {
				flog(mnoLogs, 'Successfully created the customer account profile');
				//Fetch the current account balance on this account
				$query = "SELECT * FROM profiles WHERE MSISDN = ' " . $MSISDN . "'limit 1 ";
				$queryResult = selectSQL($query);
				if($queryResult) {
					$result = mysql_fetch_assoc($queryResult);
					$originalBalance = $mobileMoneyBalance = (int) $result['mobileMoneyBalance'];
				
						
					//Make some transactions fail (Any number that is a divisible of 13)
					if($amount % 13 == 0) {
						return array(
								'SUCCESS' => false,
								'STATUS_CODE' => 800,
						);
					}
				
					//Insert the new value into the database
					//$query = "UPDATE profiles set mobileMoneyBalance = '" . $mobileMoneyBalance . "' WHERE MSISDN = '" . $MSISDN . "' LIMIT 1";
					//$updateResult = updateSQL($query);
					$updateResult = true;
					if($updateResult) {
						//We now need need to increment the balance on the Merchant account
				
						$query = "SELECT * FROM merchantProfiles WHERE merchantProfileID = 1 limit 1 ";
						$queryResult = selectSQL($query);
						if($queryResult) {
							$result = mysql_fetch_assoc($queryResult);
								
							$accountBalance = $result['accountBalance'] + $amount;
							$query = "UPDATE merchantProfiles set accountBalance = '" . $accountBalance . "' WHERE merchantProfileID = 1 LIMIT 1";
							$updateResult = updateSQL($query);
							if($updateResult) {
								//We have successfully updated all account balances
								return array(
										'SUCCESS' => true,
										'STATUS_CODE' => 200,
								);
							} else {
								//could not update the merchant account balance
								$query = "SELECT * FROM profiles WHERE MSISDN = ' " . $MSISDN . "'limit 1 ";
								$queryResult = selectSQL($query);
								$result = mysql_fetch_assoc($queryResult);
								$mobileMoneyBalance = $result['mobileMoneyBalance'] + $amount;
								//$query = "UPDATE profiles set mobileMoneyBalance = '" . $mobileMoneyBalance . "' WHERE MSISDN = '" . $MSISDN . "' LIMIT 1";
								//$updateResult = updateSQL($query);
								return array(
										'SUCCESS' => false,
										'STATUS_CODE' => 141,
								);
							}
						} else {
							//The merchant does not exist
							$query = "SELECT * FROM profiles WHERE MSISDN = ' " . $MSISDN . "'limit 1 ";
							$queryResult = selectSQL($query);
							$result = mysql_fetch_assoc($queryResult);
							$mobileMoneyBalance = $result['mobileMoneyBalance'] + $amount;
							//$query = "UPDATE profiles set mobileMoneyBalance = '" . $mobileMoneyBalance . "' WHERE MSISDN = '" . $MSISDN . "' LIMIT 1";
							//$updateResult = updateSQL($query);
							return array(
									'SUCCESS' => false,
									'STATUS_CODE' => 802,
							);
						}//if
				
					} else {
						return array(
								'SUCCESS' => false,
								'STATUS_CODE' => 141,
						);
					}
				}
			}//if
			
			return array(
					'SUCCESS' => false,
					'STATUS_CODE' => 801,
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