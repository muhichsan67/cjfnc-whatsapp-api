<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['api/login']		= 'Api/login';
$route['api/verify']	= 'Api/verify';


$route['api/dashboard/daily-remainder']	  	= 'Dashboard/daily_remainder';
$route['api/dashboard/monthly-remainder']	  = 'Dashboard/monthly_remainder';
$route['api/dashboard/monthly-ranking']		  = 'Dashboard/monthly_ranking';

$route['api/master/plant']					        = 'Master/plant';
$route['api/master/customer']					      = 'Master/customer';

$route['api/report/visit']                  = 'Report/visit_report';
$route['api/report/overdue']                = 'Report/overdue_report';
$route['api/report/collector']               = 'Report/collector_report';

