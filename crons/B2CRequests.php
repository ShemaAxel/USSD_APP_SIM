<?php

ackPayments();


function ackPayments(){

    $username = 'Safaricom_adminAPI';
    $password = '!23qweASD';

    $server = "192.168.254.66"; 
    $hubApi = "http://$server/BeepJsonAPI/index.php";
    $mnoAPI = "http://$server/samsung-simulator/API/index.php";
    
    $credentials =array(
        "username" => "$username",
        "password" => "$password",
    );
    $bill = array(
        'serviceID' => 10,
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

    flog("Payload:: ".json_encode($spayload));

    $response = post($hubApi,json_encode($spayload));

  // print_r($response);die;

    $response = json_decode($response,true);
    flog("FetchPayment:: ".print_r($response,true) );
    
    //ack
    $results = $response['results'];

      
   
    if(!empty($results)){
        pushAck($hubApi,$username,$password,$results, $mnoAPI);
    }

}

/* --Log to a file--- */
function flog($string) {
    
    $file = "/var/log/applications/logs/mnoSimulator.log";
    $date = date("Y-m-d G:i:s");
    if ($fo = fopen($file, 'ab')) {
        fwrite($fo, "$date | $string\n");
        fclose($fo);
    }

}

function pushAck($hubApi,$username,$password,$results, $mnoAPI){

    
    foreach($results as $k => $result) {
        $statusCode = $result['statusCode'];
        
        if($statusCode == 186) {
            flog("No payments pending ACK ");
        } else {
            flog('Posting payment to the MNO API');
            $MSISDN = $result['MSISDN'];
            $beepTransactionID = $result['beepTransactionID'];
            $accountNumber = $result['accountNumber'];
            $payerClientCode = $result['payerClientCode'];
            $amount = $result['amount'];
            $payerTransactionID = $result['payerTransactionID'];
                
            $params = array(
                    'MSISDN' => $MSISDN,
                    'beepTransactionID' => $beepTransactionID,
                    'accountNumber' => $accountNumber,
                    'merchantCode' => $payerClientCode,
                    'serviceID' => 35,
                    'amount' => $amount,
            );
                
            $AirtimeMNOAPI = $mnoAPI . '?' . http_build_query($params);
            flog("MNO API + params = " . $AirtimeMNOAPI);
            //echo $AirtimeMNOAPI; exit;
                
            //Set the context to GET
            $context = stream_context_create(
                    array('http' =>
                            array('method' => 'GET',
                            )
                    )
            );
            flog("Invoking the API");
            $response = file_get_contents($AirtimeMNOAPI, false, $context);
            
            flog("MNO response => " . $response);
                
            if($response) {
                $response = json_decode($response);
                $statusCode = $response->statusCode;
                $statusDescription = $response->statusDescription;
                $payerTransactionID = $payerTransactionID;
                $invoiceNumber = $response->invoiceNumber;
                $beepTransactionID = $response->beepTransactionID;
                $amountExpected = $response->amountExpected;
                $isTokenService = $response->isTokenService;
                $receiptNumber = $response->receiptNumber;
                $credentials =array(
                        "username" => $username,
                        "password" => $password,
                );
        
        
            } else {
                flog("No response from the MNO");
            }                                
        
            $bill = array(
                    'payerTransactionID' =>$payerTransactionID,
                    'beepTransactionID'=>$beepTransactionID,
                    'amountExpected'=>$amountExpected,
                    'statusCode'=>$statusCode,
                    'statusDescription'=> $statusDescription,
                    'isTokenService'=> $isTokenService,
                    'receiptNumber' => $receiptNumber
            );
        
            $data[0] = $bill;
            $payload = array(
                    "credentials"=>$credentials,
                    "packet"=>$data
            );

          // print_r($payload);die;
        
            $spayload = array(
                    "function"=>"BEEP.postPaymentAck",
                    "payload"=>json_encode($payload)
            );
        
            flog("ACK Response:: ".json_encode($spayload));
        
            $response = post($hubApi,json_encode($spayload));        

        }
    }
    
}

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

?>
