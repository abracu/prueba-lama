<?php
/*
	La Manicurista integración desde Vtiger para Core.
	
	Funciones: 
	getToken
	getQuestionsStarsDictionary
	
	
*/
	define('USER','crm@lamanicurista.com');
	define('PASS','ea9391751a2c253d64db8a4b7b58e0b594d10b85e6808e5ff690974ba912e2f3');
	define('HOST','https://api.dev.lamanicurista.com/api');
	
	function getCoreToken() {
		
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
		$err = curl_error($curl);
		
		curl_close($curl);

		if ($err) {
		  return  "cURL Error #:" . $err;
		} else {
			$r = json_decode($response,true);
		   
			if ($r['success']) {
			   return $r['data']['token'];
		   }
		}

		return false;
	}
	
	function getQuestionsStarsDictionary() {
		if ($token = getCoreToken()) {
			$curl = curl_init();
			
			curl_setopt_array($curl, array(
			  CURLOPT_URL => 'https://api.dev.lamanicurista.com/api/v1/getQuestionsStarsDictionary/C',
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "GET",
			  CURLOPT_HTTPHEADER => array(
				"Authorization: $token"
			  ),
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);
			
			curl_close($curl);

			if ($err) {
			  return  "cURL Error #:" . $err;
			} else {
				$r = json_decode($response,true);
			   
				if ($r['success']) {
					return $r['data'];
				}
			}

		}
	}
	
	function setUpdateCalification($fields,$crmid = '') {
		global $adb;
		
		$postfields = json_encode($fields);
		
		if ($token = getCoreToken()) {
			$curl = curl_init();
			
			curl_setopt_array($curl, array(
			  CURLOPT_URL => 'https://api.dev.lamanicurista.com/api/v1/serviceRating',
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "PUT",
			  CURLOPT_POSTFIELDS => $postfields,
			  CURLOPT_HTTPHEADER => array(
				"Authorization: $token",
				"Content-Type: application/json"
			  ),
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);
			
			curl_close($curl);

			$sql = "INSERT INTO api_log VALUES (?,?,?,?,NULL)";
			$adb->pquery($sql,array(date('Y-m-d H:i:s'),$crmid,$postfields,$response));
			
			if ($err) {
			  return  "cURL Error #:" . $err;
			} else {
				$r = json_decode($response,true);
			   
				if ($r['success']) {
					return $r['data'];
				}
			}

		}
	}
	
	function getCoreId($module,$value) {
		global $adb;
		
		if (strstr($value,'x') !== false) {
			list($module,$value) = explode('x',$value);
		}
		
		switch($module) {
			case 'HelpDesk': 
				$tablename = 'vtiger_ticketcf';
				$fieldname = 'ticketid';
				break;
			case 'Potentials': 
				$tablename = 'vtiger_potentialscf';
				$fieldname = 'potentialid';
				break;
			case 'Vendors': 
				$tablename = 'vtiger_vendorcf';
				$fieldname = 'vendorid';
				break;
			case 'Accounts': 
				$tablename = 'vtiger_accountscf';
				$fieldname = 'accountid';
				break;
				
		}
		$sql = "SELECT coreid FROM $tablename INNER JOIN vtiger_crmentity ON (crmid = $fieldname AND deleted = 0)
					WHERE $fieldname = ?";
				
		
		$res = $adb->pquery($sql,array($value));
		if ($res && $adb->num_rows($res) == 1) {
			return $adb->query_result($res,0,'coreid');
		}
		return;
	}
?>