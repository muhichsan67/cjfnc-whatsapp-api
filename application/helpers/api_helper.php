<?php

	use \Firebase\JWT\JWT;
use \Firebase\JWT\ExpiredException;

	if (!defined('BASEPATH')) {
	    exit('No direct script access allowed');
	}

	if (!function_exists('tokenization')) {
	    function tokenization(){
	        $config['exp'] = 30; //dalam hitungan detik
	        $config['secretkey'] = 'CJFNC-CCTAPI-2024';
	        return $config;		
	    }
	}

	if (!function_exists('api_generate_token')) {
	    function api_generate_token($token){
	        return JWT::encode($token, tokenization()['secretkey']);
	    }
	}

	if (!function_exists('check_api_version')) {
	    function check_api_version($api_version){
	    	if (API_VERSION == $api_version) {
		        return true;
	    	}
	    	error('APK Version does not match, please contact IT for more information');
	    }
	}


	if (!function_exists('authorization_header')) {
	    function authorization_header(){
	    	$CI = &get_instance();
	        $header = !empty($CI->input->request_headers()['authorization']) ? $CI->input->request_headers()['authorization'] : $CI->input->request_headers()['Authorization'];
	        if (empty($header)) {
	        	badrequest('authentication not found');
	        }

	        if (!str_contains($header, "Bearer")) {
	        	error('wrong authorization given');
	        }
        	$arr = explode(" ", $header);
        	$data = [
        		"auth_name"	=> $arr[0],
        		"token"		=> trim($arr[1])
        	];

        	try{
						$decoded = JWT::decode($data["token"], tokenization()['secretkey'], array('HS256'));      
						if ($decoded){
								return ['data' => $decoded->data, 'token' => $data['token']];
						}
					} catch (\Exception $e) {
						error($e->getMessage());
							
					}
	    }
	}	
?>