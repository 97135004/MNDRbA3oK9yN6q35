<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Get_csv_mdl extends CI_Model {

	function add($data){

		$insert = $this->db->insert("QRCODE_PAJAK_LINES_test", $data);

		return true;
	}


}

/* End of file Get_csv_mdl.php */
/* Location: ./application/models/Get_csv_mdl.php */