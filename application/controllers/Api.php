<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/libraries/JWT.php';
require APPPATH . '/libraries/ExpiredException.php';
require APPPATH . '/libraries/BeforeValidException.php';
require APPPATH . '/libraries/SignatureInvalidException.php';
require APPPATH . '/libraries/JWK.php';

use chriskacerguis\RestServer\RestController;
use \Firebase\JWT\JWT;
use \Firebase\JWT\ExpiredException;


class Api extends RestController {
    public function login_post(){
        check_api_version($this->input->post('apiversion'));

        $iat = time();
        $exp = time() + 3600;
        $nbf = time() - 20;
        $postdata = array(
            "username"      => dbClean($this->input->post('username')),
            "password"      => dbClean($this->input->post('password')),
            "company"       => dbClean($this->input->post('company')),
            "api_version"   => dbClean($this->input->post('apiversion')),
            "issue_date"    => date('Y-m-d H:i:s', $iat),
            "not_before"    => date('Y-m-d H:i:s', $nbf),
            "expire_date"   => date('Y-m-d H:i:s', $exp)
        );

        if (empty($postdata['username']) || empty($postdata['password']) || empty($postdata['company']) || empty($postdata['api_version'])) {
            error('username or password are required!');
        }
        if ($postdata['company'] == 'PT. SUPER UNGGAS JAYA') {
            setcookie('connection', 'suja');
        } else {
            setcookie('connection', 'default');
        }

        $employee_id    = $postdata['username'];
        $password       = $postdata['password'];
        $user = $this->Dbhelper->selectTabelOne("EMPLOYEE_ID, PLANT, REGION, FULL_NAME, EMAIL, PASSWORD, CASE WHEN PLANT = '*' THEN 'ALL PLANT' ELSE FN_CODE_NAME('AB' ,PLANT) END AS COMPANY_NAME", 'CD_USER', array('EMPLOYEE_ID' => $employee_id));
        if (empty($user)) {
            error('employee id not found!');
        } else {
            if (password_verify($password, $user['PASSWORD'])) {
                $token = array(
                    "iss"           => 'CJFNC-CCTAPI',
                    "aud"           => 'CJFNC-CCTAPIUSER',
                    "iat"           => $iat,
                    "nbf"           => $nbf,
                    "exp"           => $exp,
                    "data"          => $postdata
                );       
                
                $jwt = api_generate_token($token);
                unset($user['PASSWORD']);
                $data = array('token' => $jwt, 'exp' => $postdata['expire_date'], 'user' => $user);
                success('login success', $data);
            }
            error('username or password wrong!');
        }
        // $prefix = $employee_id[0].$employee_id[1];
        // if ($company == 'SUJA') {
        //     setcookie('connection', 'suja');
        // } else {
        //     setcookie('connection', 'default');
        // }
    }

    public function verify_post(){
        $postdata = $this->input->post();
        $token = "";

        $authorization_header = authorization_header();  
        dd($authorization_header);

        $jwt = api_generate_token($token);
        $data = array('token'=>$jwt, 'exp'=>$exp);
        success('login success', $data);
    }
}
