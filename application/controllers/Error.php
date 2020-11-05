<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Error extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		
	}
	public function index()
	{

		$this->data['title']   = "404";
		$this->data['message'] = "Halaman Tidak Ditemukan!";

		if (!$this->ion_auth->logged_in())
		{
			$this->load->view('404_utama', $this->data);

		}
		else{
		
			$this->data['template_page'] = "404";
			$this->template->load('template', $this->data['template_page'], $this->data);

		}
	}

}

/* End of file Error.php */
/* Location: ./application/controllers/Error.php */