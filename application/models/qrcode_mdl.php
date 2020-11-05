<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Qrcode_mdl extends CI_Model {

	function update_history($table, $params){

		$last_update_by = $this->session->userdata('identity');
		$whereCondition = "";

		if(is_array($params)){
			$arrKeys = array_keys($params);
			for ($i=0; $i < count($params); $i++) {
				$keys = $arrKeys[$i];
				if($i == 0){
					$whereAnd = " WHERE ";
				}
				else{
					$whereAnd = " AND ";
				}
				$whereCondition .= $whereAnd.$keys." = '".$params[$keys]."'";
			}
		}
		else{
			$whereCondition = "WHERE ID = ".$params;
		}

		$query_update   = "UPDATE ".$table." SET LAST_UPDATE_DATE = SYSDATE,
												 LAST_UPDATE_BY   = '".$last_update_by."'
												 ".$whereCondition;

		if($this->db->query($query_update)){
			return true;
		}
	}

	function create_history($table, $params=""){

		$created_by     = $this->session->userdata('identity');
		$whereCondition = "";
		
		if($params != ""){
			if(is_array($params)){
				$arrKeys = array_keys($params);
				for ($i=0; $i < count($params); $i++) {
					$keys = $arrKeys[$i];
					if($i == 0){
						$whereAnd = " WHERE ";
					}
					else{
						$whereAnd = " AND ";
					}
					$whereCondition .= $whereAnd.$keys." = '".$params[$keys]."'";
				}
			}
			else{
				$this->db->select_max($params, 'MAX');
				$query = $this->db->get($table);
				$id    = $query->row()->MAX;

				$whereCondition = "WHERE ".$params." = ".$id;
			}
		}
		else{
			$this->db->select_max('ID', 'MAX');
			$query = $this->db->get($table);
			$id    = $query->row()->MAX;

			$whereCondition = "WHERE ID = ".$id;
		}
		$query_update   = "UPDATE ".$table." SET CREATION_DATE = SYSDATE,
												 CREATED_BY   = '".$created_by."'
												 ".$whereCondition;
		if($this->db->query($query_update)){
			return true;
		}
	}
}

/* End of file QRCODE_mdl.php */
/* Location: ./application/models/QRCODE_mdl.php */