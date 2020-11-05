<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {
	
	public function __construct()
	{
		parent::__construct();
		
		if (!$this->ion_auth->logged_in())
		{
			redirect('/', 'refresh');
		}
	}
	public function index()
	{

		$this->data['template_page'] = "home";
		$this->data['title']         = "Home";
		$this->data['subtitle']      = "Home";
		$this->data['activepage']    = "dashboard";

		$this->template->load('template', $this->data['template_page'], $this->data);
	}

}

/* End of file Home.php */
/* Location: ./application/controllers/Home.php */