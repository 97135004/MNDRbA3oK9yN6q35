<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Menurights extends CI_Controller {
	public function __construct()
	{
		parent::__construct();
		if (!$this->ion_auth->logged_in())
                {
                        redirect('dashboard', 'refresh');
                }

		$this->load->database();
		$this->load->library(array('ion_auth', 'form_validation'));
		$this->load->helper(array('url', 'language'));

		$this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));
		$this->load->model('menu_mdl', 'menu');
		
		if (!$this->ion_auth->logged_in())
		{
			redirect('login', 'refresh');
		}
/*		else if (!$this->ion_auth->is_admin())
		{
			redirect(base_url());
		}*/

	}

	public function index()
	{

		$this->data['template_page'] = "administrator/menu";
		$this->data['title']         = 'Menu Rights';
		$this->data['subtitle']      = "List Menu Rights";
		$this->data['activepage']    = "administrator";
		
		$this->data['parent_singles'] = $this->menu->get_parent_single();
		$this->data['parents']        = $this->menu->get_parent();

		$this->template->load('template', $this->data['template_page'], $this->data);
	}

	public function get_parent_singles($id){

		$this->data['parent_singles'] = $this->menu->get_parent_single($id);
		/*foreach ($this->data['parent_singles'] as $key => $value) {
			if($value['ID'] == $id){
				$this->data['parent_singles'][$key]['parentxx'] = "ini"; 
			}
		}*/
/*
		$parent_singles  = $this->menu->get_parent_single($id);

		$parentSingles = array();
		foreach ($parent_singles as $key => $value) {
			$parentSingles[$key] = $value;
			if($value['ID'] == $id){
				echo 'x';
				die();
			}
		}
		*/
		echo json_encode($this->data['parent_singles']);

	}

	public function get_childs($id, $idSelf = NULL){

		$this->data['child'] = $this->menu->get_child($id, $idSelf);

		if($this->data['child']){
			echo json_encode($this->data['child']);
		}
		else{
			echo '0';
		}

	}

    function load_all_menu()
	{

		if($this->ion_auth->is_admin()){
			$permission = true;
		}
		else if(in_array("admin/menurights", $this->session->userdata['menu_url'])){
			$permission = true;
		}
		else{
			$permission = false;
		}

		$result['data']            = "";
		$result['draw']            = "";
		$result['recordsTotal']    = 0;
		$result['recordsFiltered'] = 0;

		if($permission === true)
		{
			
			$start    = ($this->input->post('start')) ? $this->input->post('start') : 0;
			$length   = ($this->input->post('length')) ? $this->input->post('length') : 10;
			$draw     = ($this->input->post('draw')) ? $this->input->post('draw') : 0;
			$keywords = (isset($_POST['search'])) ? $_POST['search']['value'] : '';
			
			$hasil       = $this->menu->get_all_menu($start, $length, $keywords);

			$rowCount    = $hasil['jmlRow'];
			$query       = $hasil['query'];
			$jumlahpotong = 0;

			if($rowCount > 0){

				$previousID = null;
				foreach($query->result_array() as $row)	{

					$parent    = ($row['PARENT_NAME'] != NULL) ? $row['PARENT_NAME'] : "XXX";
					$showorder = "<a href='javascript:void(0)' class='showorder' data-productid='".$row['SHOWORDER']."' data-order='".$row['SHOWORDER']."' data-parent='". strtolower(str_replace(' ', '_', $parent)) ."' style='color:#ff6436'><i class='fa fa-retweet' aria-hidden='true'></i></a>";

					$result['data'][] = array(
								'no'              => $row['RNUM'],
								'id'              => $row['ID'],
								'parent'          => $parent,
								'parent_id'       => $row['PARENT_ID'],
								'category'        => $row['LINK_TYPE'],
								'title'           => $row['TITLE'],
								'url'             => $row['URL'],
								'style'           => $row['STYLE'],
								'showorder_after' => $previousID,
								'showorder'       => $row['SHOWORDER'],
								'showorder_icon'  => $showorder
								);
					$previousID = $row['ID'];

				}

				$query->free_result();
				
				$result['draw']            = $draw;
				$result['recordsTotal']    = $rowCount;
				$result['recordsFiltered'] = $rowCount;
				
			}
			else{
				$result['data']            = "";
				$result['draw']            = "";
				$result['recordsTotal']    = 0;
				$result['recordsFiltered'] = 0;
			}
    	}

		echo json_encode($result);
    }

	public function create_menu(){

		if (isset($_POST) && !empty($_POST))
		{

			$this->form_validation->set_rules('title', 'Nama Menu', 'required');
			$this->form_validation->set_rules('category', 'Kategori Menu', 'required');
			$categoryMenu = $this->input->post('category');
			
			switch ($categoryMenu) {
				case 'parent':

					$this->form_validation->set_rules('style', 'Style', 'required');
					$this->form_validation->set_rules('showorder_parent', 'Showorder', 'required');

					$parentID  = 0;
					$url       = "#".str_replace(" ","_", strtolower($this->input->post('title')));
					$isParent  = 1;
					$showorder = $this->input->post('showorder_parent');
					$style     = $this->input->post('style');

					break;
				case 'single':

					$this->form_validation->set_rules('style', 'Style', 'required');
					$this->form_validation->set_rules('url', 'URL', 'required');
					$this->form_validation->set_rules('showorder_parent', 'Showorder', 'required');
				
					$parentID  = 0;
					$url       = $this->input->post('url');
					$isParent  = 0;
					$showorder = $this->input->post('showorder_parent');
					$style     = $this->input->post('style');

					break;
				case 'child':
					
					$this->form_validation->set_rules('parent', 'Parent', 'required');
					$this->form_validation->set_rules('url', 'URL', 'required');
				
					$parentID  = $this->input->post('parent');
					$url       = $this->input->post('url');
					$isParent  = 0;
					$showorder = $this->input->post('showorder_child');
					$style     = "";

				/*	if($this->input->post('showorder_child_add_hidden') != "1"){
						$this->form_validation->set_rules('showorder_child', 'Showorder', 'required');
						$showorder = 1;
					}*/

					break;
				default:
					break;
			}

			if ($this->form_validation->run() === TRUE)
			{
				
				$title = preg_replace("/\s+/", " ", $this->input->post('title'));

				if($categoryMenu == "child"){
					$menuList   = $this->menu->get_child($parentID);
					$parent     = $this->menu->get_by_id($parentID);
					$moduleName = str_replace(" ","_", strtolower($parent->MODULE_NAME));
				}else{
					$menuList   = $this->menu->get_parent_single();
					$moduleName = str_replace(" ","_", strtolower($title));
				}
				
				
				$dataUpdate   = array();
				if(count($menuList) > 0){

					$showorderNew = 0;
					$plusOrder    = false;
					foreach ($menuList as $key => $value) {
					
						$id           = (int)$value['ID'];
						$showorderNow = (int)$value['SHOWORDER'];

						if($plusOrder){
							$showorderNow++;
						}

						if($showorder == "first"){
							if($key == 0){
								$showorderNew = 1;
							}
							$showorderNow++;
						}
						else if($showorder == $id){
							$showorderNew = $showorderNow+1;
							$plusOrder    = true;
						}

						$dataUpdate[] = array(
												'ID'        => $id,
												'SHOWORDER' => $showorderNow 
											);
					}

				}
/*
				echo $showorderNew;
				echo "\n";
				echo "\n";

				print_r($dataUpdate);
				die();
*/
				$data = array(
					'TITLE'       => $title,
					'MODULE_NAME' => $moduleName,
					'LINK_TYPE'   => $categoryMenu,
					'URL'         => $url,
					'PARENT_ID'   => $parentID,
					'IS_PARENT'   => $isParent,
					'SHOW_MENU'   => 1,
					'STYLE'       => $style,
					'SHOWORDER'   => $showorderNew,
				);

				if ($this->menu->add($data)) {
					if(count($dataUpdate)){
						if($update = $this->menu->update_multi_menu($dataUpdate)){
							echo '1';
						}else{
							echo 'Gagal';
						}
					}
					else{
						echo '1';
					}
				}
				else{
					echo 'Gagal';
				}

			}
			else
			{

				echo validation_errors();

			}
		
		}

	}

	public function edit_menu($id = NULL){
		
		$id = (int)$id;

		if (isset($_POST) && !empty($_POST))
		{

			$this->form_validation->set_rules('title', 'Nama Menu', 'required');
			$this->form_validation->set_rules('category', 'Kategori Menu', 'required');
			$categoryMenu = $this->input->post('category');
			
			switch ($categoryMenu) {
				case 'parent':

					$this->form_validation->set_rules('style', 'Style', 'required');
					$this->form_validation->set_rules('showorder_parent', 'Showorder', 'required');

					$parentID  = 0;
					$url       = "";
					$isParent  = 1;
					$showorder = $this->input->post('showorder_parent');
					$style     = $this->input->post('style');

					break;
				case 'single':

					$this->form_validation->set_rules('style', 'Style', 'required');
					$this->form_validation->set_rules('url', 'URL', 'required');
					$this->form_validation->set_rules('showorder_parent', 'Showorder', 'required');
				
					$parentID  = 0;
					$url       = $this->input->post('url');
					$isParent  = 0;
					$showorder = $this->input->post('showorder_parent');
					$style     = $this->input->post('style');

					break;
				case 'child':

					$this->form_validation->set_rules('parent', 'Parent', 'required');
					$this->form_validation->set_rules('url', 'URL', 'required');
					$this->form_validation->set_rules('showorder_child', 'Showorder', 'required');
				
					$parentID  = $this->input->post('parent');
					$url       = $this->input->post('url');
					$isParent  = 0;
					$showorder = $this->input->post('showorder_child');
					$style     = "";

					break;
				default:
					break;
			}

			if ($this->form_validation->run() === TRUE)
			{
				
				if($categoryMenu == "child"){
					$menuList = $this->menu->get_child($parentID);
				}else{
					$menuList = $this->menu->get_parent_single();
				}

				$dataUpdate   = array();
				$showorderNew = 0;
				$plusOrder    = false;
				foreach ($menuList as $key => $value) {
					
					$idX          = (int)$value['ID'];
					$showorderNow = (int)$value['SHOWORDER'];

					if($plusOrder){
						$showorderNow++;
					}

					if($showorder == "first"){
						if($key == 0){
							$showorderNew = $showorderNow;
						}
						$showorderNow;
					}
					else if($showorder == $idX){
						$showorderNew = $showorderNow+1;
						$plusOrder    = true;
					}

					if($value['ID'] != $id){
						$dataUpdate[] = array(
											'ID'        => $idX,
											'SHOWORDER' => $showorderNow
										);
					}
					
				}

				$title = preg_replace("/\s+/", " ", $this->input->post('title'));

				$data = array(
					'TITLE'       => $this->input->post('title'),
					'MODULE_NAME' => str_replace(" ","_", strtolower($title)),
					'LINK_TYPE'   => $categoryMenu,
					'URL'         => $url,
					'PARENT_ID'   => $parentID,
					'IS_PARENT'   => $isParent,
					'SHOW_MENU'   => 1,
					'STYLE'       => $style,
					'SHOWORDER'   => $showorderNew,
				);
/*
				echo json_encode($dataUpdate);
				echo json_encode($data);
				die();
*/
				if ($this->menu->update($data, $id)) {

					if($update = $this->menu->update_multi_menu($dataUpdate)){
						echo '1';
					}else{
						echo 'Gagal';
					}

				}
				else{
					echo 'Gagal';
				}

			}
			else
			{

				echo validation_errors();

			}
		
		}

	}

	public function delete_menu($id = NULL){

		$id = (int)$id;

		$this->form_validation->set_rules('id', 'No id Selected', 'required|alpha_numeric');

		if ($this->form_validation->run() === TRUE)
		{
			if($this->menu->delete($id)){

				echo '1';

			}
			else {

				echo 'Gagal';

			}
		}

	}

	public function swaporder($id1, $order1, $id2, $order2){

		$data['status'] = false;
		$data['ket']    = "";

		if(!$id1 || !$id2){
			echo json_encode($data);
			die();
		}
		
		$dataUpdate = array(
						array(
							'ID'        => $id1,
							'SHOWORDER' => (int)$order2
						),
						array(
							'ID'        => $id2,
							'SHOWORDER' => (int)$order1
						)
					);

		if($update = $this->menu->update_multi_menu($dataUpdate)){
			$data['status'] = true;
			$data['ket']    = "Swap order successfully";
		}else{
			$data['status'] = false;
			$data['ket']    = "Failed to swap order";
		}

		echo json_encode($data);
		die();
	}

}

/* End of file Menurights.php */
/* Location: ./application/controllers/Menurights.php */
