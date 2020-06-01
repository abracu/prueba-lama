<?php
define('USER','crm@lamanicurista.com');
define('PASS','ea9391751a2c253d64db8a4b7b58e0b594d10b85e6808e5ff690974ba912e2f3');

$fields = array('login'=>USER,
						'password'=>PASS);
						
$postfields = json_encode($fields);
		
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://api.dev.lamanicurista.com/api/v1/auth",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => $postfields,
  CURLOPT_HTTPHEADER => array(
    "Content-Type: application/json"
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;
?>