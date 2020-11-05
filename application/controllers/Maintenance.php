<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Maintenance extends CI_Controller {

	public function index()
	{
		$data['image'] = base_url('assets/vendor/qrcode/img/maintenance.jpg');
		$this->load->view('maintenance', $data);
	}

}

/* End of file Maintenance.php */
/* Location: ./application/controllers/Maintenance.php */