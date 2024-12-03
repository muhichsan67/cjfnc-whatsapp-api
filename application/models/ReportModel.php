<?php
	class ReportModel extends CI_Model{

		function __construct(){
			parent::__construct();
			$this->load->database();
      $this->limit = 10;
		}

		public function get_visit_report($filter) {
      
      $result     = [];
      $base_query = $this->visit_report_base_query($filter);
      $total_data = $this->get_count($base_query);
      if ($total_data < 0) {
        return $result;
      }
      $current_pagination = $filter['pagination'];
      $limit              = $this->limit;
      $max_pagination     = ceil($total_data / $limit);

      if ($current_pagination > $max_pagination) {
        return $result;
      }
      
      $start              = ($current_pagination * $limit) - $limit;
      $end                = $current_pagination * $limit;
      $data               = $this->get_data($base_query, $start, $end);
      if (!empty($data)) {
        foreach ($data as $v) {
          $v->VISITING_DATE = date('Y-m-d', strtotime($v->VISITING_DATE));
          $result[] = $v;
        }
      }
      return $result;
	  }

    private function visit_report_base_query($filter) {
      $sdate = $filter['start_date'];
      $edate = $filter['end_date'];
      $query = "
        select 
          VISITING_NO,
          VISITING_DATE,
          b.CUSTOMER,
          b.CUSTOMER_NAME,
          FN_CODE_NAME('AB' ,SALES_ORG)   COMPANY_NAME,
          FN_USER_NAME(CREATED_BY) CREATED_BY_NAME
        from TR_VR a,
              CD_CUSTOMER b
        where 
          a.CUSTOMER = b.CUSTOMER
          and a.PLANT = b.SALES_ORG
          and VISITING_DATE BETWEEN '$sdate' AND '$edate'
      ";
      if ($filter['plant'] != '*') {
        $query .= " and b.SALES_ORG = '".$filter['plant']."'";
      }
      $query .= " order by VISITING_DATE DESC";

      return $query;
    }

    public function get_overdue_report($filter) {
      if ($filter['first_time'] == 'true') {
        $maxDate 	= $this->Dbhelper->selectOneRawQuery("SELECT MMDDYYY FROM FEED_CUST_REMAINDER_WA ORDER BY MMDDYYY DESC");
        $date 		= date('Ymd', strtotime($maxDate['MMDDYYY']));
        $filter['date'] = $date;
      }
      $result     = [];
      $base_query = $this->overdue_report_base_query($filter);
      $total_data = $this->get_count($base_query);
      if ($total_data < 0) {
        return $result;
      }
      $current_pagination = $filter['pagination'];
      $limit              = $this->limit;
      $max_pagination     = ceil($total_data / $limit);

      if ($current_pagination > $max_pagination) {
        return $result;
      }
      
      $start              = ($current_pagination * $limit) - $limit;
      $end                = $current_pagination * $limit;
      $data               = $this->get_data($base_query, $start, $end);
      if (!empty($data)) {
        foreach ($data as $v) {
          $v->MMDDYYY = date('Y-m-d', strtotime($v->MMDDYYY));
          $v->OVERDUE = formatRupiah($v->OVERDUE);
          $v->STOP = formatRupiah($v->STOP);
          $v->BEGINNING = formatRupiah($v->BEGINNING);
          $v->DEBIT = formatRupiah($v->DEBIT);
          $v->CREDIT = formatRupiah($v->CREDIT);
          $v->ENDING = formatRupiah($v->ENDING);
          $result[] = $v;
        }
      }
      return $result;
	  }

    private function overdue_report_base_query($filter) {
      $date = $filter['date'];
      $query = "SELECT 
          BUSINESS_AREA, BUSINESS_AREA_DESC, 
          MMDDYYY, 
          GROUP_CUSTOMER, GROUP_CUSTOMER_NM, 
          CUSTOMER, CUSTOMER_NM,
          BEGINNING, DEBIT,
          CREDIT, ENDING, CREDIT_DEBIT_NOTE,
          CREDIT_AMT, NORMAL, OVERDUE, STOP, SALESMAN, MOBILE_NO
        FROM FEED_CUST_REMAINDER_WA
        WHERE MMDDYYY = '$date'";
      
      if ($filter['plant'] != '*') {
        $query .= " AND BUSINESS_AREA = '".$filter['plant']."'";
      }
      if ($filter['type'] != '*') {
        $query .= " AND GROUP_CUSTOMER LIKE '".$filter['type']."%'";
      }

      if ($filter['overdue_notempty'] == 'true') {
        $query .= " AND OVERDUE + STOP > 0";
      }
      $query .= " ORDER BY MMDDYYY DESC";
      return $query;
    }

    public function get_collector_report($filter) {
      if ($filter['first_time'] == 'true') {
        $maxDateTarget 	= $this->Dbhelper->selectTabelOne('YYMM', 'TR_MONTHLY_TARGET', [], 'YYMM', 'DESC')['YYMM'];
        $maxDate 	= $this->Dbhelper->selectOneRawQuery("SELECT MMDDYYY FROM FEED_CUST_REMAINDER_WA WHERE MMDDYYY LIKE '$maxDateTarget%' ORDER BY MMDDYYY DESC");
        $date 		= date('Y-m-d', strtotime($maxDate['MMDDYYY']));
        $filter['date'] = $date;
      }
      $result     = [];
      // dd($filter);
      $base_query = $this->collector_report_base_query($filter);
      $total_data = $this->get_count($base_query);
      if ($total_data < 0) {
        return $result;
      }
      $current_pagination = $filter['pagination'];
      $limit              = $this->limit;
      $max_pagination     = ceil($total_data / $limit);

      if ($current_pagination > $max_pagination) {
        return $result;
      }
      
      $start              = ($current_pagination * $limit) - $limit;
      $end                = $current_pagination * $limit;
      $data               = $this->get_data($base_query, $start, $end);
      if (!empty($data)) {
        foreach ($data as $v) {
          $defaultRoundedRunning  = 2;
          $defaultRoundedStop     = 2;
          $defaultRoundedTotal    = 2;
          $runningIndex   = ($v->RUNNING_CASH_IN > 0 && $v->RUNNING_TARGET > 0) ? round(($v->RUNNING_CASH_IN / $v->RUNNING_TARGET) * 100, $defaultRoundedRunning) : 0;
          if ($v->RUNNING_CASH_IN > 0 && $v->RUNNING_TARGET > 0) {
              while ($runningIndex <= 0 && $defaultRoundedRunning <= 5) {
                  $defaultRoundedRunning += 1;
                  $runningIndex   = ($v->RUNNING_CASH_IN > 0 && $v->RUNNING_TARGET > 0) ? round(($v->RUNNING_CASH_IN / $v->RUNNING_TARGET) * 100, $defaultRoundedRunning) : 0;
              }
          }
          $stopIndex      = ($v->STOP_CASH_IN > 0 && $v->STOP_TARGET > 0) ? round(($v->STOP_CASH_IN / $v->STOP_TARGET) * 100, $defaultRoundedStop) : 0;
          if ($v->STOP_CASH_IN > 0 && $v->STOP_TARGET > 0) {
              while ($stopIndex <= 0 && $defaultRoundedStop <= 5) {
                  $defaultRoundedStop += 1;
                  $stopIndex      = ($v->STOP_CASH_IN > 0 && $v->STOP_TARGET > 0) ? round(($v->STOP_CASH_IN / $v->STOP_TARGET) * 100, $defaultRoundedStop) : 0;
              }
          }

          $cTarget    = $v->RUNNING_TARGET + $v->STOP_TARGET;
          $cCashIn    = $v->RUNNING_CASH_IN + $v->STOP_CASH_IN;
          $totalIndex = ($cCashIn > 0 && $cTarget > 0) ? round(($cCashIn / $cTarget) * 100, $defaultRoundedTotal) : 0;
          if ($cCashIn > 0 && $cTarget > 0) {
              while ($totalIndex <= 0 && $defaultRoundedTotal <= 5) {
                  $defaultRoundedTotal += 1;
                  $totalIndex      = ($cCashIn > 0 && $cTarget > 0) ? round(($cCashIn / $cTarget) * 100, $defaultRoundedTotal) : 0;
              }
          }
          $v->COLLECTION_DATE = $filter['date'];
          $v->RUNNING_TARGET = formatRupiah($v->RUNNING_TARGET);
          $v->RUNNING_CASH_IN = formatRupiah($v->RUNNING_CASH_IN);
          $v->RUNNING_PERCENTAGE = $runningIndex;
          $v->STOP_TARGET = formatRupiah($v->STOP_TARGET);
          $v->STOP_CASH_IN = formatRupiah($v->STOP_CASH_IN);
          $v->STOP_PERCENTAGE = $stopIndex;
          $v->TOTAL_TARGET = formatRupiah($cTarget);
          $v->TOTAL_CASH_IN = formatRupiah($cCashIn);
          $v->TOTAL_PERCENTAGE = $totalIndex;
          $result[] = $v;
        }
      }
      return $result;
	  }

    private function collector_report_base_query($filter) {
      $where = "";
      if ($filter['plant'] != '*') {
        $filter['plant'] = substr_replace($filter['plant'], 0, -1);
        $where .= " and B.BUSINESS_AREA = '".$filter['plant']."'";
      }
      $date = date('Ymd', strtotime($filter['date']));
      $datemonth = date('Ym', strtotime($filter['date']));
      $query = "
        SELECT
          FN_CODE_NAME('AB' ,D.BUSINESS_AREA) AS COMPANY_NAME,
          D.BUSINESS_AREA AS COMPANY_ID,
          D.EMPNO AS EMPLOYEE_ID,
          FN_HR_EMPLOYEE('02', D.EMPNO) AS EMPLOYEE_NAME,
          D.RUNNING_TARGET,
          D.RUNNING_CASH_IN,
          CASE WHEN D.RUNNING_CASH_IN > 0 AND D.RUNNING_TARGET > 0 THEN ((D.RUNNING_CASH_IN / D.RUNNING_TARGET) * 100) ELSE 0 END as RUNNING_PERCENTAGE,
          D.STOP_TARGET,
          D.STOP_CASH_IN,
          CASE WHEN D.STOP_CASH_IN > 0 AND D.STOP_TARGET > 0 THEN ((D.STOP_CASH_IN / D.STOP_TARGET) * 100) ELSE 0 END as STOP_PERCENTAGE,
          0 as TOTAL_TARGET,
          0 as TOTAL_CASH_IN,
          0 as TOTAL_PERCENTAGE
        FROM (
            SELECT
                SUBSTR(C.BUSINESS_AREA,1,3)||'2' as BUSINESS_AREA,
                C.EMPNO,
                SUM(C.RUNNING_TARGET) AS RUNNING_TARGET,
                SUM(C.STOP_TARGET) AS STOP_TARGET,
                SUM(CASE WHEN STATUS = 'R' THEN CREDIT ELSE 0 END) AS RUNNING_CASH_IN,
                SUM(CASE WHEN STATUS = 'S' THEN CREDIT ELSE 0 END) AS STOP_CASH_IN
            FROM (
                SELECT 
                    B.BUSINESS_AREA,
                    B.CUSTOMER,
                    B.EMPNO,
                    B.RUNNING_TARGET,
                    B.STOP_TARGET, 
                    (SELECT A.CREDIT FROM FEED_CUST_REMAINDER_WA A WHERE A.BUSINESS_AREA = B.BUSINESS_AREA AND A.CUSTOMER = B.CUSTOMER AND A.MMDDYYY =  '$date') as CREDIT,
                    CASE WHEN RUNNING_TARGET > 0 THEN 'R' ELSE 'S' END AS STATUS
                FROM TR_MONTHLY_TARGET B
                WHERE 
                    B.YYMM = '$datemonth'
                    AND B.RUNNING_TARGET + B.STOP_TARGET > 0
                    AND B.EMPNO != '(none)'
                    $where
            ) C
            GROUP BY SUBSTR(C.BUSINESS_AREA,1,3)||'2', C.EMPNO
        ) D
        ORDER BY D.BUSINESS_AREA ASC, D.EMPNO
      ";
      return $query;
    }

    private function get_count($base_query) {
      $query = "
        SELECT COUNT(*) as TOTAL_DATA FROM ($base_query)
      ";
      $result = $this->db->query($query)->row();
      return $result->TOTAL_DATA;
    }

    private function get_data($base_query, $start, $end) {
      $query = "
        SELECT 
          a.* 
        FROM (
          SELECT rownum as rownumber, b.* FROM ( $base_query ) b
        ) a 
        WHERE 
          a.rownumber > $start AND 
          a.rownumber <= $end 
        ORDER BY a.rownumber ASC 
      ";
      // dd($query);
      $result = $this->db->query($query)->result();
      return $result;
    }
	}
?>