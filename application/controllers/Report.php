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


class Report extends RestController {
    public function __Construct() {
		  parent::__construct();
      $header = authorization_header();  
      check_api_version($header['data']->api_version);
      if ($header['data']->company == 'PT. SUPER UNGGAS JAYA') {
          setcookie('connection', 'suja');
      } else {
          setcookie('connection', 'default');
      }
      $this->load->model('ReportModel', 'model');
	  }

    public function visit_report_post(){
      $postdata = $this->input->post();
      $filter   = $this->filter_report('VISIT_REPORT', $postdata);
      if (empty($filter)) {
        badrequest('your post data is invalid, please check again');
      }
      $data 			= $this->model->get_visit_report($filter);
      success('retreive visit report success', $data);
    }

    public function overdue_report_post(){
      $postdata = $this->input->post();
      $filter   = $this->filter_report('OVERDUE_REPORT', $postdata);
      if (empty($filter)) {
        badrequest('your post data is invalid, please check again');
      }
      $data 			= $this->model->get_overdue_report($filter);
      success('retreive overdue report success', $data);
    }

    public function collector_report_post(){
      $postdata = $this->input->post();
      $filter   = $this->filter_report('COLLECTOR_REPORT', $postdata);
      if (empty($filter)) {
        badrequest('your post data is invalid, please check again');
      }
      $data 			= $this->model->get_collector_report($filter);
      success('retreive collector report success', $data);
    }

    private function filter_report($report_name, $postdata) {
      $filter = [];
      $validate_data = [];
      if ($report_name == 'VISIT_REPORT') {
        $validate_data = [
          'start_date'  => 'required|date',
          'end_date'    => 'required|date',
          'plant'       => 'required',
          'pagination'  => 'required|number'
        ];
      } elseif ($report_name == 'OVERDUE_REPORT') {
        $validate_data = [
          'date'        => 'required|date',
          'plant'       => 'required',
          'type'        => 'required',
          'pagination'  => 'required|number',
          'first_time'  => '',
          'overdue_notempty' => ''
        ];
      } elseif ($report_name == 'COLLECTOR_REPORT') {
        $validate_data = [
          'date'        => 'required|date',
          'plant'       => 'required',
          'pagination'  => 'required|number',
          'first_time'  => ''
        ];
      }
      if (!empty($validate_data)) {
        foreach ($validate_data as $column => $str_rules) {
          if (empty($postdata[$column])) {
            badrequest("$column not found!");
          }
  
          $post_value = $postdata[$column];
          $rules = explode("|", $str_rules);
          foreach ($rules as $rule) {
            switch ($rule) {
              case 'required':
                validate_required($column, $post_value);
                break;
              case 'date':
                validate_date($column, $post_value);
                break;
              case 'number':
                validate_number($column, $post_value);
                break;
              default:
                # code...
                break;
            }
          }
          if (in_array('date', $rules)) {
            $post_value = date('Ymd', strtotime($post_value));
          } else {
            $post_value = dbClean($post_value);
          }
          $filter[$column] = $post_value;
        }
      }

      return $filter;
    }
}
