<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class Auth
 * @property Ion_auth|Ion_auth_model $ion_auth        The ION Auth spark
 * @property CI_Form_validation      $form_validation The form validation library
 */
class Auth extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->database();

		$this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));

		$this->load->model('menu_mdl', 'menu');

		$this->lang->load('auth');
	}


	public function index()
	{

		if (!$this->ion_auth->logged_in()) {
			redirect('/', 'refresh');
		} else if (!$this->ion_auth->is_admin()) {
			redirect(base_url());
		} else {
			redirect('auth/users');
		}
	}

	public function users()
	{

		if (!$this->ion_auth->logged_in()) {
			$permission = false;
		} elseif ($this->ion_auth->is_admin()) {
			$permission = true;
		} else if (in_array("admin/users", $this->session->userdata['menu_url'])) {
			$permission = true;
		} else {
			$permission = false;
		}

		if ($permission == false) {
			redirect('dashboard', 'refresh');
		} else {

			$this->data['template_page'] = "auth/user";
			$this->data['title']         = 'User';
			$this->data['subtitle']      = "List Data User";
			$this->data['activepage']    = "administrator";

			$this->data['groups']        = $this->ion_auth->groups()->result_array();
			//$this->data['list_cabang']   = $this->cabang->get_all();

			$this->template->load('template', $this->data['template_page'], $this->data);
		}
	}

	public function get_all_user()
	{

		if (!$this->ion_auth->logged_in()) {
			$permission = false;
		} elseif ($this->ion_auth->is_admin()) {
			$permission = true;
		} else if (in_array("admin/users", $this->session->userdata['menu_url'])) {
			$permission = true;
		} else {
			$permission = false;
		}

		$result['data']            = "";
		$result['draw']            = "";
		$result['recordsTotal']    = 0;
		$result['recordsFiltered'] = 0;

		if ($permission == true) {

			$start    = ($this->input->post('start')) ? $this->input->post('start') : 0;
			$length   = ($this->input->post('length')) ? $this->input->post('length') : 10;
			$draw     = ($this->input->post('draw')) ? $this->input->post('draw') : 0;
			$keywords = (isset($_POST['search'])) ? $_POST['search']['value'] : '';

			$this->data['users'] = $this->ion_auth->get_users($start, $length, $draw, $keywords);
			$total_user          = $this->ion_auth->get_total_users($keywords);

			foreach ($this->data['users'] as $k => $user) {
				$this->data['users'][$k]->GROUPS = $this->ion_auth->get_users_groups($user->ID)->result();
			}
			$row = array();

			foreach ($this->data['users'] as $value) {

				$userGroupID = array();
				$userGroup   = array();
				foreach ($value->GROUPS as $group) {
					$userGroupID[] = $group->ID;
					$userGroup[]   = $group->NAME;
				}
				$row[] = array(
					'no'               => $value->RNUM,
					'id'               => $value->ID,
					'user_name'        => $value->USER_NAME,
					'email'            => $value->EMAIL,
					'display_name'     => $value->DISPLAY_NAME,
					'group_id'         => implode(", ", $userGroupID),
					'user_group'       => implode(", ", $userGroup),
					//							'kd_cabang'        => get_nama_cabang($value->KD_CABANG),
					'status'           => ($value->ACTIVE == 1) ? 'Active' : 'Not Active',
					'last_update_by'   => ($value->LAST_UPDATE_BY) ? $value->LAST_UPDATE_BY : '',
					'last_update_date' => ($value->LAST_UPDATE_DATE) ? $value->LAST_UPDATE_DATE2 : ''
				);
				unset($userGroupID);
				unset($userGroup);
			}

			$result['data']            = ($total_user > 0) ? $row : "";
			$result['draw']            = ($total_user > 0) ? $draw : "";
			$result['recordsTotal']    = ($total_user > 0) ? $total_user : 0;
			$result['recordsFiltered'] = ($total_user > 0) ? $total_user : 0;
		}

		echo json_encode($result);
	}

	public function groups()
	{

		if (!$this->ion_auth->logged_in()) {
			$permission = false;
		} elseif ($this->ion_auth->is_admin()) {
			$permission = true;
		} else if (in_array("admin/groups", $this->session->userdata['menu_url'])) {
			$permission = true;
		} else {
			$permission = false;
		}

		if ($permission == false) {
			redirect('dashboard', 'refresh');
		} else {

			$this->data['template_page'] = "auth/groups";
			$this->data['title']         = 'User Groups';
			$this->data['subtitle']      = "List Data Groups";
			$this->data['activepage']    = "administrator";

			$this->data['menus'] = $this->menu->menus();

			$this->template->load('template', $this->data['template_page'], $this->data);
		}
	}

	public function get_all_groups()
	{

		if (!$this->ion_auth->logged_in()) {
			$permission = false;
		} elseif ($this->ion_auth->is_admin()) {
			$permission = true;
		} else if (in_array("admin/groups", $this->session->userdata['menu_url'])) {
			$permission = true;
		} else {
			$permission = false;
		}

		$result['data']            = "";
		$result['draw']            = "";
		$result['recordsTotal']    = 0;
		$result['recordsFiltered'] = 0;

		if ($permission == true) {

			$start    = ($this->input->post('start')) ? $this->input->post('start') : 0;
			$length   = ($this->input->post('length')) ? $this->input->post('length') : 10;
			$draw     = ($this->input->post('draw')) ? $this->input->post('draw') : 0;
			$keywords = (isset($_POST['search'])) ? $_POST['search']['value'] : '';

			$this->data['groups'] = $this->ion_auth->get_groups($start, $length, $draw, $keywords);

			$total_groups         = $this->ion_auth->get_total_groups($keywords);

			foreach ($this->data['groups'] as $k => $group) {
				$this->data['groups'][$k]->MENUS = $this->ion_auth->get_groups_menus($group->ID)->result();
			}


			foreach ($this->data['groups'] as $value) {

				$groupMenuID = array();
				$groupMenu   = array();
				foreach ($value->MENUS as $menu) {
					$groupMenuID[] = $menu->ID;
					$groupMenu[]   = $menu->TITLE;
				}
				$row[] = array(
					'no'          => $value->RNUM,
					'id'          => $value->ID,
					'group_name'  => $value->NAME,
					'menu_id'     => implode(", ", $groupMenuID),
					'menu_name'   => implode(", ", $groupMenu),
					'description' => $value->DESCRIPTION
				);
			}

			$result['data']            = ($total_groups > 0) ? $row : "";
			$result['draw']            = ($total_groups > 0) ? $draw : "";
			$result['recordsTotal']    = ($total_groups > 0) ? $total_groups : 0;
			$result['recordsFiltered'] = ($total_groups > 0) ? $total_groups : 0;
		}

		echo json_encode($result);
	}


	public function edit_profil()
	{

		if (!$this->ion_auth->logged_in()) {
			redirect('/', 'refresh');
		}

		$this->data['template_page']   = "auth/edit_profil";
		$this->data['title']           = "Edit Profile";
		$this->data['subtitle']        = "Edit Profile";
		$this->data['activepage']      = "";
		$this->data['message_success'] = "";
		$this->data['message']         = "";

		$user_id            = $this->session->userdata['user_id'];
		$this->data['user'] = $this->ion_auth->user($user_id)->row();

		if (isset($_POST) && !empty($_POST)) {

			$user = $this->ion_auth->user($user_id)->row();
			$this->form_validation->set_rules('display_name', 'Full Name', 'trim|required');
			$this->form_validation->set_rules('password', 'Password', 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']');

			if ($this->form_validation->run() === TRUE) {

				$data = array(
					'DISPLAY_NAME' => $this->input->post('display_name')
				);

				// update the password if it was posted
				if ($this->input->post('password') != "password") {
					$data['PASSWORD'] = $this->input->post('password');
				}

				// check to see if we are updating the user
				if ($this->ion_auth->update($user->ID, $data)) {
					$this->data['message_success'] = "Profile update successfully";

					$this->data['user'] = $this->ion_auth->user($user_id)->row();
					$session_data = array(
						'display_name'         => $this->data['user']->DISPLAY_NAME
					);

					$this->session->set_userdata($session_data);
				} else {
					$this->data['message'] = "Profil failed to update";
				}
			} else {
				$this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');
			}
		}

		$this->template->load('template', $this->data['template_page'], $this->data);
	}

	/**
	 * Create a new user
	 */
	public function create_user()
	{

		$tables          = $this->config->item('tables', 'ion_auth');
		$identity_column = $this->config->item('identity', 'ion_auth');

		// validate form input
		$this->form_validation->set_rules('user_name', 'Username', 'trim|required|is_unique[' . $tables['users'] . '.' . $identity_column . ']');
		$this->form_validation->set_rules('display_name', 'Nama Lengkap', 'trim|required');
		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
		//		$this->form_validation->set_rules('kd_cabang', 'Cabang', 'required');
		$this->form_validation->set_rules('groups[]', 'Assign Groups', 'trim|required');

		if ($this->form_validation->run() === TRUE) {

			$email    = strtolower($this->input->post('email'));
			$identity = $this->input->post('user_name');
			$password = $this->config->item('default_password', 'ion_auth');

			$additional_data = array(
				'DISPLAY_NAME' => $this->input->post('display_name'),
				//				'KD_CABANG'    => $this->input->post('kd_cabang'),
				'ACTIVE'       => ($this->input->post('active')) ? $this->input->post('active') : 0,
			);

			$create_user = $this->ion_auth->register($identity, $password, $email, $additional_data);

			if ($create_user) {

				$id_user   = $create_user;
				$groupData = $this->input->post('groups');

				foreach ($groupData as $grp) {
					$oke = false;
					if ($this->ion_auth->add_to_group($grp, $id_user)) {
						$oke = true;
					}
				}
				if ($oke) {
					echo '1';
				} else {
					echo 'Failed to asssign group';
				}
			} else {
				echo 'Failed to add record';
			}
		} else {
			echo validation_errors();
		}
	}


	public function edit_user()
	{
		$id = $this->input->post('id');
		$user          = $this->ion_auth->user($id)->row();

		if (isset($_POST) && !empty($_POST)) {
			// validate form input
			$this->form_validation->set_rules('display_name', 'Nama Lengkap', 'trim|required');
			$this->form_validation->set_rules('groups[]', 'Assign Groups', 'trim|required');

			if ($this->form_validation->run() === TRUE) {

				$data = array(
					'DISPLAY_NAME' => $this->input->post('display_name'),
					'ACTIVE'       => ($this->input->post('active')) ? $this->input->post('active') : 0,
				);

				if ($this->ion_auth->update($user->ID, $data)) {

					$groupData = $this->input->post('groups');
					$this->ion_auth->remove_from_group('', $id);

					foreach ($groupData as $grp) {
						$oke = false;
						if ($this->ion_auth->add_to_group($grp, $id)) {
							$oke = true;
						}
					}
					if ($oke) {
						echo '1';
					} else {
						echo 'Failed to asssign group';
					}
				} else {
					echo 'Gagal';
				}
			} else {
				echo validation_errors();
			}
		}
	}

	public function assign_menu($id)
	{

		$group = $this->ion_auth->group($id)->row();
		$this->form_validation->set_rules('menus[]', 'Assign Menu', 'trim|required');

		if (isset($_POST) && !empty($_POST)) {
			if ($this->form_validation->run() === TRUE) {
				$menuData = $this->input->post('menus');


				$this->ion_auth->remove_from_menu('', $id);

				foreach ($menuData as $mnu) {
					$oke = false;
					if ($this->ion_auth->add_to_menu($mnu, $id)) {
						$oke = true;
					}
				}

				if ($oke) {
					echo '1';
				} else {
					echo 'Gagal';
				}
			} else {
				echo validation_errors();
			}
		}
	}

	public function delete_user($id, $keterangan = "")
	{

		$id = (int)$id;

		$this->load->library('form_validation');
		$this->form_validation->set_rules('id', 'No id Selected', 'required|alpha_numeric');

		if ($this->ion_auth->logged_in()) {
			if ($this->ion_auth->delete_user($id, $keterangan)) {

				echo '1';
			} else {

				echo 'Gagal';
			}
		}
	}

	public function reset_user_pass($id)
	{
		$id = (int)$id;

		$this->load->library('form_validation');
		$this->form_validation->set_rules('id', 'No id Selected', 'required|alpha_numeric');

		if ($this->ion_auth->logged_in()) {
			$identity     = $this->ion_auth->get_identity($id);
			$new_password = $this->config->item('default_password', 'ion_auth');

			if ($this->ion_auth->reset_password($identity, $new_password)) {
				echo '1';
			} else {
				echo 'Gagal';
			}
		}
	}

	public function create_group()
	{
		// validate form input
		$this->form_validation->set_rules('group_name', 'Group Name', 'trim|required');

		if ($this->form_validation->run() === TRUE) {
			if ($new_group_id = $this->ion_auth->create_group($this->input->post('group_name'), $this->input->post('description'))) {
				echo '1';
			}
		} else {
			echo validation_errors();
		}
	}

	public function edit_group($id)
	{
		// bail if no group id given
		if (!$id || empty($id)) {
			echo 'Gagal';
		}

		$group = $this->ion_auth->group($id)->row();

		// validate form input
		$this->form_validation->set_rules('group_name', 'Group Name', 'required|trim');

		if (isset($_POST) && !empty($_POST)) {
			if ($this->form_validation->run() === TRUE) {

				if ($this->ion_auth->update_group($id, $this->input->post('group_name'), $this->input->post('description'))) {
					echo '1';
				} else {
					echo 'Gagal';
				}
			} else {
				echo validation_errors();
			}
		}
	}

	public function delete_group($id = NULL)
	{

		$id = (int)$id;

		$this->load->library('form_validation');
		$this->form_validation->set_rules('id', 'No id Selected', 'required|alpha_numeric');

		if ($this->ion_auth->logged_in()) {
			if ($this->ion_auth->delete_group($id)) {

				echo '1';
			} else {

				echo 'Gagal';
			}
		}
	}

	/**
	 * Log the user in
	 */
	public function login()
	{

		if ($this->ion_auth->logged_in()) {
			redirect('dashboard', 'refresh');
		}

		$this->data['title'] = "Login";


		if ($_POST) {

			$result      = array();
			$status      = false;
			$description = "";
			$this->form_validation->set_rules('identity', "Username", 'required');
			$this->form_validation->set_rules('password', "Password", 'required');

			// validate form input
			if ($this->form_validation->run() === TRUE) {
				$remember = (bool)$this->input->post('remember');

				$login = $this->ion_auth->login($this->input->post('identity'), $this->input->post('password'), $remember);

				if ($login) {
					$status      = true;
					$description = $this->ion_auth->messages();
				} else {
					$description = $this->ion_auth->errors();
				}
			} else {
				$description = validation_errors();
			}

			$result['status']      = $status;
			$result['description'] = $description;

			echo json_encode($result);
		} else {
			$this->_render_page('auth/login', $this->data);
		}
	}

	/**
	 * Log the user out
	 */
	public function logout()
	{
		$this->data['title'] = "Logout";

		// log the user out
		$logout = $this->ion_auth->logout();

		// redirect them to the login page
		$this->session->set_flashdata('message', $this->ion_auth->messages());
		redirect('/', 'refresh');
	}

	public function _get_csrf_nonce()
	{
		$this->load->helper('string');
		$key = random_string('alnum', 8);
		$value = random_string('alnum', 20);
		$this->session->set_flashdata('csrfkey', $key);
		$this->session->set_flashdata('csrfvalue', $value);

		return array($key => $value);
	}


	/**
	 * @return bool Whether the posted CSRF token matches
	 */
	public function _valid_csrf_nonce()
	{
		$csrfkey = $this->input->post($this->session->flashdata('csrfkey'));
		if ($csrfkey && $csrfkey === $this->session->flashdata('csrfvalue')) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * @param string     $view
	 * @param array|null $data
	 * @param bool       $returnhtml
	 *
	 * @return mixed
	 */
	public function _render_page($view, $data = NULL, $returnhtml = FALSE) //I think this makes more sense
	{

		$this->viewdata = (empty($data)) ? $this->data : $data;

		$view_html = $this->load->view($view, $this->viewdata, $returnhtml);

		// This will return html on 3rd argument being true
		if ($returnhtml) {
			return $view_html;
		}
	}


	/*	public function clear_session_cabang()
	{
		// log the user out

		$kode_cabang = $this->input->post('kode_cabang');

		$session_data = array(
			'kd_cabang' => $kode_cabang
		);

		$this->session->set_userdata($session_data);

		return true;

	}
*/
}
