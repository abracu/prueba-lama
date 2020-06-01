<?php
/*
	La Manicurista API.
	
	Funciones: 
	/auth
	/customer
	/professional
	/order
	/orderRating
	
	
*/
	
	include_once('vtwsclib/Vtiger/WSClient.php');
	include_once('integration.php');
	//error_reporting(E_ALL);
	//ini_set('display_errors','1');
	
	define('MYSQL_HOST', 'localhost');
	define('MYSQL_USER', 'crm');
	define('MYSQL_PASS', '64650abc');
	define('MYSQL_NAME', 'crm');
	
	global $client, $lastError, $user, $apikey;
	$client = NULL;
	$lastError = NULL;
	
	function query($query) {
		global $lastid;
		$conn = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_NAME);
		// Check connection
		if ($conn->connect_error) {
		    die("Connection failed: " . $conn->connect_error);
		} 

		mysqli_set_charset($conn, "utf8");
		$conn->set_charset("utf8");

		$result = $conn->query($query);
		$res = array();
		if ($result && isset($result->num_rows) && $result->num_rows > 0) {
		    // output data of each row
		    while($row = $result->fetch_assoc()) {
		      $res[] = $row;
		    }
		}
		
		$lastid = mysqli_insert_id($conn);
		$conn->close();


		return $res;
		
	}
	
	function getClientVT($user, $apikey) {
		global $client, $lastError;
		
		$url = 'http://80.211.50.106/crm';

		$client = new Vtiger_WSClient($url);
		
		if ($client) {
			$login = $client->doLogin($user, $apikey);
			
			if (!$login) {
				$lastError = $client->lastError();		
				$client = NULL;
				return false;
			}
			$r['username'] = $user;
			$r['apikey'] = $apikey;
			$r['sessionid'] = rand(1000000,99999999);
			return $r;
		} else {
			$lastError = 'Webservice no esta disponible';
		}
		return false;
	}
	
/* getToken */
	
	function getToken($apikey,$username,$sessionid) {
		// Create token header as a JSON string
		$header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
		// Create token payload as a JSON string
		$payload = json_encode(['username' => $username,
								'apikey' => $apikey,
								'exp' => (new DateTime("now"))->getTimestamp(),
								'sessionid' => $sessionid]);
		// Encode Header to Base64Url String
		$base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
		// Encode Payload to Base64Url String
		$base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
		// Create Signature Hash
		$signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, 'abC123!', true);
		// Encode Signature to Base64Url String
		$base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
		// Create JWT
		$jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
		
		return $jwt;
	}
	
	/* Decode JWT */
	
	function decodeToken($token) {
		global $user, $apikey;
		
		$part = explode(".",$token);
		$header = $part[0];
		$payload = $part[1];
		$signature = $part[2];
		
		$valid = hash_hmac('sha256',"$header.$payload",'abC123!',true);
		$valid = str_replace(['+', '/', '='], ['-', '_', ''],base64_encode($valid));
		
		if($signature == $valid){
			$lst = json_decode(base64_decode($payload),true);
			$apikey = $lst['apikey'];
			$user = $lst['username'];
			
			return true;
		} 
		return false;
	}
	
	/* convertirLead: Se convierte el Lead para Cuentas y Contactos. Mas adelante se actualiza con el resto de campos y se crea la oportunidad */

	function convertirLead($campos) {
		global $client, $lastError;
		$convert_lead_array = array();
		$convert_lead_array['leadId'] = $campos['id'];
		$convert_lead_array['assignedTo'] = '19x1';
		$convert_lead_array['entities']['Accounts']['create']=true;
		$convert_lead_array['entities']['Accounts']['name'] = 'Accounts';
		$convert_lead_array['entities']['Accounts']['accountname'] = $campos['firstname'].' '.$campos['lastname'];
		$convert_lead_array['entities']['Accounts']['email1'] = $campos['email'];
		
		$convert_lead_array['entities']['Contacts']['create']=true;
		$convert_lead_array['entities']['Contacts']['name'] = 'Contacts';
		$convert_lead_array['entities']['Contacts']['lastname'] = $campos['lastname'];
		$convert_lead_array['entities']['Contacts']['firstname'] = $campos['firstname'];
		$convert_lead_array['entities']['Contacts']['email'] = $campos['email'];
		$convert_lead_json = json_encode($convert_lead_array);
		
		$r = $client->doInvoke('convertlead', array('element'=>$convert_lead_json));
		$lastError = $client->lastError();
		return array($r['Accounts'],$r['Contacts'],$r['Potentials']);
	}



	
	function getAccountId($id,$bBuscar = false) {
		global $client, $lastError;
		
		$module = 'Accounts';
		
		$sql = "SELECT id FROM $module WHERE coreid = '".$id."'";
		
		$record = $client->doQuery($sql);
		$lastError = $client->lastError();
		
		if ($record && count($record) > 0) {
			$rA = $record[0]['id'];
			
			if ($bBuscar) {
				return $rA;
			}
			
			$module = 'Contacts';
		
			$sql = "SELECT id FROM $module WHERE coreid = '".$input['id']."'";
			$record = $client->doQuery($sql);
			if (count($record) > 0) {
				$rC = $record[0]['id'];
			}
			
			return array($rA,$rC);
		}
		
		if ($bBuscar)
			return false;
		
		//Se verifica si existe como Leads y se convierte a Cuenta / Contacto
		$module = 'Leads';
		
		$sql = "SELECT id FROM $module WHERE coreid = '".$id."'";
		$record = $client->doQuery($sql);
		
		if (count($record) > 0) {
			$record = $client->doRetrieve($record[0]['id']);
			list($rA,$rC,$rP) = convertirLead($record);
			return array($rA,$rC);
		}
		return false;
	}
	
	function getVendorId($id) {
		global $client, $lastError;
		
		$module = 'Vendors';
		
		$sql = "SELECT id FROM $module WHERE coreid = '".$id."'";
		$record = $client->doQuery($sql);
		if (count($record) > 0) {
			return $record[0]['id'];
		}
		
		return false;
	}
	
	function getPotentialId($id) {
		global $client, $lastError;
		
		$module = 'Potentials';
		
		$sql = "SELECT id FROM $module WHERE coreid = '".$id."'";
		$record = $client->doQuery($sql);
		if (count($record) > 0) {
			return $record[0]['id'];
		}
		
		return false;
	}
	
	function setCustomer($input) {
		global $client, $lastError;
		
		//Consultamos primero la cuenta
		
		$module = 'Accounts';
		
		$sql = "SELECT id FROM $module WHERE coreid = '".$input['id']."'";
		$record = $client->doQuery($sql);
		if (count($record) > 0) {
			$record = $client->doRetrieve($record[0]['id']);			
		}
		
		$lstCampos = array('coreid'=>'id',
						'email1'=>'email',
						'accountname'=>'company',
						'phone'=>'phone_number',
						'bull_street' => 'address',
						'bill_city' => 'city',
						);
		
		foreach($lstCampos as $fieldC=>$fieldF) {
			$record[$fieldC] = $input[$fieldF];
		}
		
		if (isset($record['id']) && !empty($record['id'])) {
			$r = $client->doUpdate($record);
			
			$lastError = $client->lastError();
			
			$module = 'Contacts';
			
			$sql = "SELECT id FROM $module WHERE coreid = '".$input['id']."'";
			$record = $client->doQuery($sql);
			if (count($record) > 0) {
				$record = $client->doRetrieve($record[0]['id']);			
			}
			
			$lstCampos = array('coreid'=>'id',
							'firstname'=>'first_name',
							'lastname'=>'last_name',
							'email'=>'email',
							'phone'=>'phone_number',
							'mailing_street' => 'address',
							'maling_city' => 'city',
							);
			
			foreach($lstCampos as $fieldC=>$fieldF) {
				$record[$fieldC] = $input[$fieldF];
			}
			
			if (isset($record['id']) && !empty($record['id'])) {
				$r = $client->doUpdate($record);
			
				$lastError = $client->lastError();
				return array('id'=>$r['id']);
			}
		}
		
		$module = 'Leads';
		
		$sql = "SELECT id FROM $module WHERE coreid = '".$input['id']."'";
		$record = $client->doQuery($sql);
		if (count($record) > 0) {
			$record = $client->doRetrieve($record[0]['id']);			
		}
		
		$lstCampos = array('coreid'=>'id',
						'firstname'=>'first_name',
						'lastname'=>'last_name',
						'email'=>'email',
						'company'=>'company',
						'phone'=>'phone_number',
						'leadsource' => 'origin_pre_contact',
						'lane' => 'address',
						'city' => 'city',
						'leadstatus'=>'state',
						);
		
		foreach($lstCampos as $fieldC=>$fieldF) {
			$record[$fieldC] = $input[$fieldF];
		}
		
		if (isset($record['id']) && !empty($record['id'])) {
			$r = $client->doUpdate($record);
		} else {
			$r = $client->doCreate($module,$record);
		}
		$lastError = $client->lastError();
		return array('id'=>$r['id']);
	}
	
	function setProfessional($input) {
		global $client, $lastError;
		$module = 'Vendors';
		
		$sql = "SELECT id FROM $module WHERE coreid = '".$input['id']."'";
		$record = $client->doQuery($sql);
		if (count($record) > 0) {
			$record = $client->doRetrieve($record[0]['id']);			
		}
		
		$lstCampos = array('coreid'=>'id',
						'cf_864'=>'first_name',
						'cf_866'=>'last_name',
						'email'=>'email',
						'vendorname'=>'company',
						'phone'=>'phone_number',
						'cf_870' => 'origin_professional',
						'street' => 'address',
						'city' => 'city',
						'cf_868'=>'state',
						);
		
		foreach($lstCampos as $fieldC=>$fieldF) {
			$record[$fieldC] = $input[$fieldF];
		}
		if (isset($record['id']) && !empty($record['id'])) {
			$r = $client->doUpdate($record);
		} else {
			$r = $client->doCreate($module,$record);
		}
		$lastError = $client->lastError();
		return array('id'=>$r['id']);
	}
	
	function setOrder($input) {
		global $client, $lastError;
		$module = 'Potentials';
		
		$sql = "SELECT id FROM $module WHERE coreid = '".$input['id']."'";
		$record = $client->doQuery($sql);
		if (count($record) > 0) {
			$record = $client->doRetrieve($record[0]['id']);			
		}
		
		$lstCampos = array('coreid'=>'id',
						'potentialname' => 'name_organization',
						'account_id'=>'customer_id',
						'vendor_id'=>'manicurist_id',
						'amount'=>'total_order',
						'cf_872'=>'discount',
						'closingdate'=>'order_date',
						'cf_874' => 'origin_order',
						'sales_stage' => 'status',
						'description' => 'product_list',
						);
		
		foreach($lstCampos as $fieldC=>$fieldF) {
			if ($fieldC == 'account_id') {
				$rS = getAccountId($input[$fieldF]);
				if ($rS) {
					$record['related_to'] = $rS[0];
					$record['contact_id'] = $rS[1];
				} else {
					$lastError = array('code'=>'MISSING MANDATORY VALUES','message'=>'Cliente no encontrado');
				}
			} elseif ($fieldC == 'vendor_id') {
				$rS = getVendorId($input[$fieldF]);
				if ($rS) {
					$record[$fieldC] = $rS;
				} else {
					$lastError = array('code'=>'MISSING MANDATORY VALUES','message'=>'Profesional no encontrado');
				}
			} else {
				$record[$fieldC] = $input[$fieldF];
			}
		}
		
		if (isset($record['id']) && !empty($record['id'])) {
			$r = $client->doUpdate($record);
		} else {
			$r = $client->doCreate($module,$record);
		}
		$lastError = $client->lastError();
		return array('id'=>$r['id']);
		
	}
	
	function setOrderRating($input) {
		global $client, $lastError;
		$module = 'HelpDesk';
		
		$sql = "SELECT id FROM $module WHERE coreid = '".$input['id']."'";
		$record = $client->doQuery($sql);
		if (count($record) > 0) {
			$record = $client->doRetrieve($record[0]['id']);			
		}
		
		$lstCampos = array('coreid'=>'id',
						'potential_id'=>'order_id',
						'parent_id'=>'customer_id',
						'vendor_id'=>'manicurist_id',
						'cf_880'=>'date_rating',
						'ticketcategories' => 'type',
						'description'=>'observation',
						'cf_884' => 'question_star_id',
						'cf_882' => 'star_number',
						);
		
		foreach($lstCampos as $fieldC=>$fieldF) {
			if ($fieldC == 'parent_id') {
				$rS = getAccountId($input[$fieldF],true);
				if ($rS) {
					$record['parent_id'] = $rS;
				} else {
					$lastError = array('code'=>'MISSING MANDATORY VALUES','message'=>'Cliente no encontrado');
				}
			} elseif ($fieldC == 'vendor_id') {
				$rS = getVendorId($input[$fieldF]);
				if ($rS) {
					$record[$fieldC] = $rS;
				} else {
					$lastError = array('code'=>'MISSING MANDATORY VALUES','message'=>'Profesional no encontrado');
				}
			} elseif ($fieldC == 'potential_id') {
				$rS = getPotentialId($input[$fieldF]);
				if ($rS) {
					$record[$fieldC] = $rS;
				} else {
					$lastError = array('code'=>'MISSING MANDATORY VALUES','message'=>'Orden no encontrada');
				}
			} elseif ($fieldC == 'cf_884') {
				$lst = getQuestionsStarsDictionary();
				foreach($lst[$input['star_number']]['answers'] as $answers) {
					if ($answers['id'] == $input['question_star_id']) {
						$record[$fieldC] = $answers['answer'];
						break;
					}
				}
			} else {
				$record[$fieldC] = $input[$fieldF];
			}
		}
		if (isset($record['id']) && !empty($record['id'])) {
			$r = $client->doUpdate($record);
		} else {
			$r = $client->doCreate($module,$record);
		}
		$lastError = $client->lastError();
		return array('id'=>$r['id']);
	}
	
	/* API - CREF - VtigerCRM */
	
	function init($request,$headers) {
		global $userid, $sessionid, $lastError;
		
		$obj = json_decode(file_get_contents("php://input"),true);
		$mode = $request['mode'];
		
		
		
		if ($mode == 'auth') {
			
			$lst = getClientVT($obj['user'],$obj['apikey']);
			if ($lst) {
				$apikey = $lst['apikey'];
				$sessionid = $lst['sessionid'];
				
				$jwt = getToken($lst['apikey'],$lst['username'],$lst['sessionid']);
				
				$refresh_token = uniqid('',true);
				
				$r["access_token"] = $jwt;
				$r["token_type"] = "bearer";
				$r["expires_in"] = (60*60*24*7)-1;
				$r['refresh_token'] = $refresh_token;
				$r[".issued"] = date('r');
				$r[".expires"] = $dateExp;
					
			} else {
				header($_SERVER['SERVER_PROTOCOL'] . ' 400 Internal Server Error', true, 400);
				$r["error"] = "invalid_grant";
				$r["error_description"] = $lastError;
			}
		} else {//Validamos el token
			global $user, $apikey, $client, $lastError;
			$token = $headers['Authorization'];
			if (decodeToken($token)) {
				if (getClientVT($user,$apikey)) {
					if ($mode == 'customer') {
						$r = setCustomer($obj);
					} elseif ($mode == 'professional') {
						$r = setProfessional($obj);
					} elseif ($mode == 'order') {
						$r = setOrder($obj);
					} elseif ($mode == 'orderRating') {
						$r = setOrderRating($obj);
					}
					
				}
				if (!empty($lastError)) {
					$r["error"] = "operation_error";
					$r["error_description"] = $lastError;
				}
			} else {
				$r["error"] = "invalid_token";
				$r["error_description"] = 'Token no valido';
			}
		}
		
		header("Content-type: application/json; charset=utf-8"); 
		if (isset($r['error']) && !empty($r['error'])) {
			$a['success'] = false;
		} else {
			$a['success'] = true;
		}
		$a['result'] = $r;
		echo json_encode($a, JSON_UNESCAPED_UNICODE );
		
		$sql = "INSERT INTO core_log VALUES('".date('Y-m-d H:i:s')."','".json_encode($obj)."','".json_encode($a, JSON_UNESCAPED_UNICODE )."',NULL)";
		query($sql);
		die();
	}
	
	
	$headers = getallheaders();
	
	init($_REQUEST,$headers);
	
?>