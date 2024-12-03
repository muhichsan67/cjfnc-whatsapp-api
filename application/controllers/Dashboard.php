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


class Dashboard extends RestController {
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

    public function daily_remainder_get(){
			$includeInternal = $this->input->get('include');
			$cls  	= "'FM'";
			if ($includeInternal == 'true') {
				$cls .= ", 'IN'";
			}
			// $currmonth = '202407';
			$currmonth = date('Ym');
			$query = "
					select 
									YMD,
									SUM(CHASH_IN) AS COLL, 
									SUM(OVER_DUE) AS OD, 
									SUM(STOP) AS BD 
							from TR_DAILY_REMAINDER
							where YMD like '$currmonth%' and CLS in ($cls)
							group by YMD
							order by YMD desc
			";
			$result_data = $this->db->query($query)->result();
			if (!empty($result_data)) {
				$daily_data = $result_data[0];
				$previous_data = $result_data[1];

				$coll_margin = $daily_data->COLL - $previous_data->COLL;
				$od_margin = $daily_data->OD - $previous_data->OD;
				$bd_margin = $daily_data->BD - $previous_data->BD;

				$daily_data->YMD = date('d M Y', strtotime($daily_data->YMD));
				$daily_data->COLL = formatRupiah($daily_data->COLL);
				$daily_data->OD = formatRupiah($daily_data->OD);
				$daily_data->BD = formatRupiah($daily_data->BD);
				$daily_data->COLL_MARGIN = formatRupiah($coll_margin);
				$daily_data->OD_MARGIN = formatRupiah($od_margin);
				$daily_data->BD_MARGIN = formatRupiah($bd_margin);

				$daily_data->COLL_MARGIN = ($daily_data->COLL_MARGIN[0] != "-") ? "+".$daily_data->COLL_MARGIN : $daily_data->COLL_MARGIN;
				$daily_data->OD_MARGIN = ($daily_data->OD_MARGIN[0] != "-") ? "+".$daily_data->OD_MARGIN : $daily_data->OD_MARGIN;
				$daily_data->BD_MARGIN = ($daily_data->BD_MARGIN[0] != "-") ? "+".$daily_data->BD_MARGIN : $daily_data->BD_MARGIN;
			} else {
				$daily_data = [
					'YMD' 				=> date('d M Y'),
					'COLL'				=> "-",
					'OD'					=> "-",
					'STOP'				=> "-",
					'COLL_MARGIN' => "0",
					'OD_MARGIN' 	=> "0",
					'STOP_MARGIN' => "0",
				];
			}

			$data = $daily_data;
			success('retreive daily data success', $data);
    }

    public function monthly_remainder_get(){
			$includeInternal = $this->input->get('include');
			$cls  	= "'FM'";
			if ($includeInternal == 'true') {
				$cls .= ", 'IN'";
			}
			$curryear = date('Y');
			$query = "
					select 
									YYMM,
									SUM(CHASH_IN) AS COLL, 
									SUM(OVER_DUE) AS OD, 
									SUM(STOP) AS BD 
							from TR_MONTHLY_REMAINDER
							where YYMM LIKE '$curryear%' and CLS in ($cls)
							group by YYMM
							order by YYMM desc
			";
			$result_data = $this->db->query($query)->result();

			// urutan data index 1 = bulan juli
			// urutan data index 2 = bulan juni (untuk cek margin)
			if (!empty($result_data)) {
				$monthly_data = $result_data[0];
				$previous_data = $result_data[1];

				$coll_margin = $monthly_data->COLL - $previous_data->COLL;
				$od_margin = $monthly_data->OD - $previous_data->OD;
				$bd_margin = $monthly_data->BD - $previous_data->BD;

				$monthly_data->YYMM = $monthly_data->YYMM."01";
				$monthly_data->YYMM = date('M Y', strtotime($monthly_data->YYMM));
				$monthly_data->COLL = formatRupiah($monthly_data->COLL);
				$monthly_data->OD = formatRupiah($monthly_data->OD);
				$monthly_data->BD = formatRupiah($monthly_data->BD);
				$monthly_data->COLL_MARGIN = formatRupiah($coll_margin);
				$monthly_data->OD_MARGIN = formatRupiah($od_margin);
				$monthly_data->BD_MARGIN = formatRupiah($bd_margin);

				$monthly_data->COLL_MARGIN = ($monthly_data->COLL_MARGIN[0] != "-") ? "+".$monthly_data->COLL_MARGIN : $monthly_data->COLL_MARGIN;
				$monthly_data->OD_MARGIN = ($monthly_data->OD_MARGIN[0] != "-") ? "+".$monthly_data->OD_MARGIN : $monthly_data->OD_MARGIN;
				$monthly_data->BD_MARGIN = ($monthly_data->BD_MARGIN[0] != "-") ? "+".$monthly_data->BD_MARGIN : $monthly_data->BD_MARGIN;
			} else {
				$monthly_data = [
					'YYMM' 				=> date('M Y'),
					'COLL'				=> "-",
					'OD'					=> "-",
					'STOP'				=> "-",
					'COLL_MARGIN' => "0",
					'OD_MARGIN' 	=> "0",
					'STOP_MARGIN' => "0",
				];
			}

			$data = $monthly_data;
			success('retreive monthly data success', $data);
    }

    public function monthly_ranking_get() {
			// $previousMonthDate 	= date('Ymd', strtotime('last day of previous month'));
			$maxDateTarget 	= $this->Dbhelper->selectTabelOne('YYMM', 'TR_MONTHLY_TARGET', [], 'YYMM', 'DESC')['YYMM'];
			// $maxDateTarget	= '202407';
			// done
			// Report wkwk, default nya pengen juli juga ya?
			// Iyak wkwk
			$maxDate 	= $this->Dbhelper->selectOneRawQuery("SELECT MMDDYYY FROM FEED_CUST_REMAINDER_WA WHERE MMDDYYY LIKE '$maxDateTarget%' ORDER BY MMDDYYY DESC");
			$previousMonthDate 		= date('Ymd', strtotime($maxDate['MMDDYYY']));
			// dd($previousMonthDate);

			$previousMonth 			= date('Ym', strtotime($previousMonthDate));

			$queries 				= $this->query_ranking($previousMonthDate, $previousMonth);
			// dd($queries);
			$data_running 	= $this->db->query($queries['running'])->result_array();
			$data_stop 			= $this->db->query($queries['stop'])->result_array();

			$limit_rank = 5;
			$top_running = array_slice($data_running, 0, $limit_rank);
			$bot_running = array_slice($data_running, -5);
			krsort($bot_running);
			krsort($top_running);
			$bot_running = array_values($bot_running);
			$top_running = array_values($top_running);
			$running = [
				"TOP"			=> $top_running,
				"BOTTOM"	=> $bot_running
			];

			foreach ($running["TOP"] as $i => $v) {
				$running["TOP"][$i]["EMPLOYEE_NAME"] = $v["EMPLOYEE_NAME"]." (".$v["COMPANY_NAME"].")";
				$running["TOP"][$i]["TARGET"] = formatRupiah($v['TARGET']/1000000);
				$running["TOP"][$i]["CASH_IN"] = formatRupiah($v['CASH_IN']/1000000);
				$running["TOP"][$i]["PERCENTAGE"] = round($v['PERCENTAGE'], 2).'%';
			}

			foreach ($running["BOTTOM"] as $i => $v) {
				$running["BOTTOM"][$i]["EMPLOYEE_NAME"] = $v["EMPLOYEE_NAME"]." (".$v["COMPANY_NAME"].")";
				$running["BOTTOM"][$i]["TARGET"] = formatRupiah($v['TARGET']/1000000);
				$running["BOTTOM"][$i]["CASH_IN"] = formatRupiah($v['CASH_IN']/1000000);
				$running["BOTTOM"][$i]["PERCENTAGE"] = round($v['PERCENTAGE'], 2).'%';
			}

			$top_stop = array_slice($data_stop, 0, $limit_rank);
			$bot_stop = array_slice($data_stop, -5);

			krsort($bot_stop);
			krsort($top_stop);
			$bot_stop = array_values($bot_stop);
			$top_stop = array_values($top_stop);
			$stop = [
				"TOP"			=> $top_stop,
				"BOTTOM"	=> $bot_stop
			];
			foreach ($stop["TOP"] as $i => $v) {
				$stop["TOP"][$i]["EMPLOYEE_NAME"] = $v["EMPLOYEE_NAME"]." (".$v["COMPANY_NAME"].")";
				$stop["TOP"][$i]["TARGET"] = formatRupiah($v['TARGET']/1000000);
				$stop["TOP"][$i]["CASH_IN"] = formatRupiah($v['CASH_IN']/1000000);
				$stop["TOP"][$i]["PERCENTAGE"] = round($v['PERCENTAGE'], 2).'%';
			}

			foreach ($stop["BOTTOM"] as $i => $v) {
				$stop["BOTTOM"][$i]["EMPLOYEE_NAME"] = $v["EMPLOYEE_NAME"]." (".$v["COMPANY_NAME"].")";
				$stop["BOTTOM"][$i]["TARGET"] = formatRupiah($v['TARGET']/1000000);
				$stop["BOTTOM"][$i]["CASH_IN"] = formatRupiah($v['CASH_IN']/1000000);
				$stop["BOTTOM"][$i]["PERCENTAGE"] = round($v['PERCENTAGE'], 2).'%';
			}

			$result = [
				"PERIODE"	=> date('M Y', strtotime($previousMonthDate)),
				"RUNNING"	=> $running,
				"STOP"		=> $stop
			];
			// dd($result);
			success('retrieve data monthly ranking success', $result);
		}

		private function query_ranking($previousMonthDate, $previousMonth) {
			$query_stop = "
				SELECT * FROM (
					SELECT
							FN_CODE_NAME('AB' ,SUBSTR(D.BUSINESS_AREA,1,3)||'2') COMPANY_NAME,
							D.BUSINESS_AREA,
							D.EMPNO,
							FN_HR_EMPLOYEE('02', D.EMPNO) AS EMPLOYEE_NAME,
							D.TARGET,
							D.CASH_IN,
							CASE WHEN D.CASH_IN > 0 AND D.TARGET > 0 THEN ((D.CASH_IN / D.TARGET) * 100) ELSE 0 END as PERCENTAGE,
							'S' AS STATUS
					FROM (
							SELECT
									C.BUSINESS_AREA,
									C.EMPNO,
									SUM(C.TARGET) AS TARGET,
									SUM(C.CREDIT) AS CASH_IN
							FROM (
									SELECT 
											B.BUSINESS_AREA,
											B.CUSTOMER,
											B.EMPNO,
											B.STOP_TARGET AS TARGET,
											(SELECT A.CREDIT FROM FEED_CUST_REMAINDER_WA A WHERE A.BUSINESS_AREA = B.BUSINESS_AREA AND A.CUSTOMER = B.CUSTOMER AND A.MMDDYYY = '$previousMonthDate') as CREDIT
									FROM TR_MONTHLY_TARGET B
									WHERE 
											B.YYMM = '$previousMonth'
											AND B.STOP_TARGET > 0
											AND B.EMPNO != '(none)'
							) C
							GROUP BY C.BUSINESS_AREA, C.EMPNO
					) D
				) ORDER BY CASH_IN DESC
			";
			$query_running = "
				SELECT * FROM (
					SELECT
							FN_CODE_NAME('AB' ,SUBSTR(D.BUSINESS_AREA,1,3)||'2') COMPANY_NAME,
							D.BUSINESS_AREA,
							D.EMPNO,
							FN_HR_EMPLOYEE('02', D.EMPNO) AS EMPLOYEE_NAME,
							D.TARGET,
							D.CASH_IN,
							CASE WHEN D.CASH_IN > 0 AND D.TARGET > 0 THEN ((D.CASH_IN / D.TARGET) * 100) ELSE 0 END as PERCENTAGE,
							'R' AS STATUS
					FROM (
							SELECT
									C.BUSINESS_AREA,
									C.EMPNO,
									SUM(C.TARGET) AS TARGET,
									SUM(C.CREDIT) AS CASH_IN
							FROM (
									SELECT 
											B.BUSINESS_AREA,
											B.CUSTOMER,
											B.EMPNO,
											B.RUNNING_TARGET AS TARGET,
											(SELECT A.CREDIT FROM FEED_CUST_REMAINDER_WA A WHERE A.BUSINESS_AREA = B.BUSINESS_AREA AND A.CUSTOMER = B.CUSTOMER AND A.MMDDYYY = '$previousMonthDate') as CREDIT
									FROM TR_MONTHLY_TARGET B
									WHERE 
											B.YYMM = '$previousMonth'
											AND B.RUNNING_TARGET > 0
											AND B.EMPNO != '(none)'
							) C
							GROUP BY C.BUSINESS_AREA, C.EMPNO
					) D
				) ORDER BY CASH_IN DESC
			";

			$query = [
				"running" => $query_running,
				"stop" 		=> $query_stop
			];
			// dd($query);
			return $query;
		}
}
