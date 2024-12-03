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


class Master extends RestController {
    public function __Construct() {
			parent::__construct();
			$header = authorization_header();  
			check_api_version($header['data']->api_version);
			if ($header['data']->company == 'PT. SUPER UNGGAS JAYA') {
				setcookie('connection', 'suja');
			} else {
					setcookie('connection', 'default');
			}
		}

    public function plant_post(){
				$data 			= $this->Dbhelper->selectTabel('CODE, CODE_NAME', 'CD_CODE', ['HEAD_CODE'	=> 'AB'], 'CODE', 'ASC');
        success('retreive daily data success', $data);
    }

		public function plant_overdue_post(){
			$data 			= $this->Dbhelper->selectTabel('CODE, CODE_NAME', 'CD_CODE', ['HEAD_CODE'	=> 'CS07'], 'CODE', 'ASC');
			success('retreive daily data success', $data);
	}

		public function customer_post() {
			$plant = $this->input->get('plant');
			$this->db->select('
				CD_CUSTOMER.SALES_ORG as COMPANY_CODE,
				COMPANY.CODE_NAME as COMPANY_NAME,  
				CD_CUSTOMER.CUSTOMER,
				CD_CUSTOMER.CUSTOMER_NAME as CUSTOMER_NAME,
				REGION.CODE_NAME as REGION_NAME,
				SALES_OFFICE.CODE_NAME as SALES_OFFICE_NAME,
				CD_CUSTOMER.TELEPHONE_2,
				CD_CUSTOMER.STREET,
				CD_CUSTOMER.CHIEF,
				CD_CUSTOMER.CHIEF_ID
			');
			$this->db->from('CD_CUSTOMER');
			$this->db->join('CD_CODE COMPANY', "CD_CUSTOMER.SALES_ORG = COMPANY.CODE AND COMPANY.HEAD_CODE = 'AB'");
			$this->db->join('CD_CODE SALES_OFFICE', "CD_CUSTOMER.SALES_OFFICE = SALES_OFFICE.CODE AND SALES_OFFICE.HEAD_CODE = 'AC01'", "left");
			$this->db->join('CD_CODE REGION', "CD_CUSTOMER.REGION = REGION.CODE AND REGION.HEAD_CODE = 'CS02'", "left");
			if (!empty($plant) && $plant != '*') {
				$this->db->where('CD_CUSTOMER.SALES_ORG', $plant);
			}
			$this->db->order_by('CD_CUSTOMER.CUSTOMER_NAME', 'ASC');

			$data = $this->db->get()->result_array();
			success('retreive master data customer success', $data);
		}

    public function customer_remainder_post(){

        $curryear = date("Y");
				$all_plant = [
					['CUSTOMER' => '*', 'CUSTOMER_NM' => 'ALL CUSTOMER']
				];
		
				$plant = $this->input->post('plant');
				$group_customer = $this->input->post('group_customer');
				$this->db->select('
					CUSTOMER, CUSTOMER_NM
				');
				$this->db->from('FEED_CUST_REMAINDER_WA');
				if (!empty($plant) && $plant != '*') {
					$this->db->where('FEED_CUST_REMAINDER_WA.BUSINESS_AREA', $plant);
				}
				if (!empty($group_customer) && $group_customer != "*") {
					$this->db->where('FEED_CUST_REMAINDER_WA.GROUP_CUSTOMER', $group_customer);
				}
				$this->db->group_by('CUSTOMER, CUSTOMER_NM');
				$this->db->order_by('FEED_CUST_REMAINDER_WA.CUSTOMER_NM', 'ASC');

				$data = $this->db->get()->result_array();
				$data = array_merge($all_plant, $data);
        success('retreive master data customer remainder success', $data);
    }

}
