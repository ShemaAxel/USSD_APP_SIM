<?php

ackPayments();

function ackPayments(){
	$config = (include_once (dirname(__FILE__).'/../API/config.php'));
        
        $server = "192.168.254.66:9000"; 
        $hubApi = "http://$server/BeepJsonAPI4.1/index.php";
	$username = "mcaAdmin";
	$password="!23qweASD";
	 
	$credentials =array(
			"username" => $username,
			"password" => $password,
	);
	$bill = array(
			'serviceID' => 1,
	);

	//$data[] = $bill;
	$payload = array(
			"credentials"=>$credentials,
			"packet"=>$bill
	);

	$spayload = array(
			"function"=>"BEEP.fetchPayments",
			"payload"=>json_encode($payload)
	);

	//flog("Payload:: ".json_encode($spayload));

	$response = post($hubApi,json_encode($spayload));

	$response = json_decode($response,true);
	//flog("FetchPayment:: ".print_r($response,true) );
	//ack
	$results = $response['results'];
	
	if(!empty($results) && $results[0]['statusCode'] != 186){
		foreach($results as $k => $request) {
			$MSISDN = $request['MSISDN'];
			$beepTransactionID = $request['beepTransactionID'];
			$accountNumber = $request['accountNumber'];
			$payerClientCode = $request['payerClientCode'];
			$amount = $request['amount'];
			$payerTransactionID = $request['payerTransactionID'];
			
			$params = array(
					'MSISDN' => $MSISDN,
					'beepTransactionID' => $beepTransactionID,
					'accountNumber' => $accountNumber,
					'merchantCode' => $payerClientCode,
					'serviceID' => 1,
					'amount' => $amount,
					);
			
			//Where are we pushing requests
			$payBillAPI = $config['api'] . '?' . http_build_query($params);
			
			//Set the context to GET
			$context = stream_context_create(
					array('http' =>
							array('method' => 'GET',
							)
					)
			);
			
			$response = file_get_contents($payBillAPI, false, $context);
			if($response) {
				$response = json_decode($response);
				$statusCode = $response->statusCode;
				$statusDescription = $response->statusDescription;
				$payerTransactionID = $payerTransactionID;
				$invoiceNumber = $response->invoiceNumber;
				$beepTransactionID = $response->beepTransactionID;
				$amountExpected = $response->amountExpected;
				$isTokenService = $response->isTokenService;
				
				$credentials =array(
						"username" => $username,
						"password" => $password,
				);
				
				$bill = array(
						'payerTransactionID' =>$payerTransactionID,
						'beepTransactionID'=>$beepTransactionID,
						'amountExpected'=>$amount,
						'statusCode'=> $statusCode,
						'statusDescription'=> $statusDescription,
						'isTokenService'=> $isTokenService,
				);
				$data = array();
				$data[0] = $bill;
				$payload = array(
						"credentials"=>$credentials,
						"packet"=>$data
				);
				
				$spayload = array(
						"function"=>"BEEP.postPaymentAck",
						"payload"=>json_encode($payload)
				);
				
				//flog("ACK Response:: ".json_encode($spayload));
				
				$response = post($hubApi,json_encode($spayload));				
			}
						
			
		}//foreach	
	}

}


//Do a post
function post($url,$fields) {
	//open connection
	$ch = curl_init();
	//set the url, number of POST vars, POST data
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
	curl_setopt($ch, CURLOPT_POST, count($fields));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
	//execute post
	$result = curl_exec($ch);
	//close connection
	curl_close($ch);
	return $result;
}
