<?php
	class Dbhelper extends CI_Model{
		function __construct(){
			parent::__construct();
			$this->load->database();
		}
		function selectTabel($select=false,$tabel=false,$where=false,$order=false,$orderMethod=false,$limit=false){
			$this->db->select($select);
			if ($where==true) {
				if(count($where)>0){
					$this->db->where($where);
				}
			}
			if($order==true){
				$this->db->order_by($order,$orderMethod);
			}
			if ($limit) {
				$this->db->limit($limit);
			}
			$hasil = $this->db->get($tabel);
			return $hasil->result_array();
		}

		function selectTabelOne($select=false,$tabel=false,$where=false,$order=false,$orderMethod=false,$limit=false){
			$this->db->select($select);
			if ($where==true) {
				if(count($where)>0){
					$this->db->where($where);
				}
			}
			if($order==true){
				$this->db->order_by($order,$orderMethod);
			}
			if ($limit) {
				$this->db->limit($limit);
			}
			$hasil = $this->db->get($tabel);
			return $hasil->row_array();
		}
		
		function selectRawQuery($query) {
			return $this->db->query($query)->result_array();
		}

		function selectOneRawQuery($query) {
			return $this->db->query($query)->row_array();
		}

		function selectTabelDistinct($select=false,$tabel=false,$distinct=false){
			$this->db->select($select);
			if ($distinct==true) {
				$this->db->distinct();
			}
			$hasil = $this->db->get($tabel);
			// debugCode($hasil->result_array());
			return $hasil->result_array();
		}

		function getCountById($tabel=false,$where=false){
			$this->db->select('*');
			if(count($where)>0){
				$this->db->where($where);
			}
			$hasil = $this->db->get($tabel);
			return $hasil->num_rows();
		}
		function updateData($tabel=false,$where=false,$data=false){
			if(count($where)>0){
				$this->db->where($where);
			}
			$hasil = $this->db->update($tabel,$data);
			return $hasil;
		}
		function insertData($tabel=false,$data=false){
			$hasil = $this->db->insert($tabel,$data);
			return $hasil;
		}
		function insertDataWithReturnID($tabel=false,$data=false,$id=false){
			$this->db->insert($tabel,$data);
			$sql = "SELECT MAX($id) AS MAX_ID FROM $tabel";
			$query = $this->db->query($sql);
			$result = $query->result();
			return $result[0]->MAX_ID;
		}
		function deleteData($tabel,$where){
			$sql = "DECLARE child_exists EXCEPTION; PRAGMA EXCEPTION_INIT(child_exists, -2292); BEGIN DELETE $tabel WHERE $where; EXCEPTION WHEN child_exists THEN null; END;";
			$this->db->query($sql);
		}
		function updateDataClob($tabel,$kolom_update,$kolom_key,$kolom_val,$databaru){
			$SQL = "UPDATE $tabel SET $kolom_update = $kolom_update || to_clob('$databaru') WHERE $kolom_key = $kolom_val";
			$this->db->query($SQL);
		}
	}
?>