<?php

defined('BASEPATH') OR exit('No direct script access allowed');


class Ion_auth_model extends CI_Model
{
	/**
	 * Holds an array of tables used
	 *
	 * @var array
	 */
	public $tables = array();

	/**
	 * activation code
	 *
	 * @var string
	 */
	public $activation_code;

	/**
	 * forgotten password key
	 *
	 * @var string
	 */
	public $forgotten_password_code;

	/**
	 * new password
	 *
	 * @var string
	 */
	public $new_password;

	/**
	 * Identity
	 *
	 * @var string
	 */
	public $identity;

	/**
	 * Where
	 *
	 * @var array
	 */
	public $_ion_where = array();

	/**
	 * Select
	 *
	 * @var array
	 */
	public $_ion_select = array();

	/**
	 * Like
	 *
	 * @var array
	 */
	public $_ion_like = array();

	/**
	 * Limit
	 *
	 * @var string
	 */
	public $_ion_limit = NULL;

	/**
	 * Offset
	 *
	 * @var string
	 */
	public $_ion_offset = NULL;

	/**
	 * Order By
	 *
	 * @var string
	 */
	public $_ion_order_by = NULL;

	/**
	 * Order
	 *
	 * @var string
	 */
	public $_ion_order = NULL;

	/**
	 * Hooks
	 *
	 * @var object
	 */
	protected $_ion_hooks;

	/**
	 * Response
	 *
	 * @var string
	 */
	protected $response = NULL;

	/**
	 * message (uses lang file)
	 *
	 * @var string
	 */
	protected $messages;

	/**
	 * error message (uses lang file)
	 *
	 * @var string
	 */
	protected $errors;

	/**
	 * error start delimiter
	 *
	 * @var string
	 */
	protected $error_start_delimiter;

	/**
	 * error end delimiter
	 *
	 * @var string
	 */
	protected $error_end_delimiter;

	/**
	 * caching of users and their groups
	 *
	 * @var array
	 */
	public $_cache_user_in_group = array();
	public $_cache_group_in_menu = array();

	/**
	 * caching of groups
	 *
	 * @var array
	 */
	protected $_cache_groups = array();
	protected $_cache_menus = array();

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->config->load('ion_auth', TRUE);
		$this->load->helper('cookie');
		$this->load->helper('date');
		$this->lang->load('ion_auth');

		// initialize db tables data
		$this->tables = $this->config->item('tables', 'ion_auth');

		// initialize data
		$this->identity_column = $this->config->item('identity', 'ion_auth');
		$this->store_salt = $this->config->item('store_salt', 'ion_auth');
		$this->salt_length = $this->config->item('salt_length', 'ion_auth');
		$this->join = $this->config->item('join', 'ion_auth');

		// initialize hash method options (Bcrypt)
		$this->hash_method = $this->config->item('hash_method', 'ion_auth');
		$this->default_rounds = $this->config->item('default_rounds', 'ion_auth');
		$this->random_rounds = $this->config->item('random_rounds', 'ion_auth');
		$this->min_rounds = $this->config->item('min_rounds', 'ion_auth');
		$this->max_rounds = $this->config->item('max_rounds', 'ion_auth');

		// initialize messages and error
		$this->messages    = array();
		$this->errors      = array();
		$delimiters_source = $this->config->item('delimiters_source', 'ion_auth');

		// load the error delimeters either from the config file or use what's been supplied to form validation
		if ($delimiters_source === 'form_validation')
		{
			// load in delimiters from form_validation
			// to keep this simple we'll load the value using reflection since these properties are protected
			$this->load->library('form_validation');
			$form_validation_class = new ReflectionClass("CI_Form_validation");

			$error_prefix = $form_validation_class->getProperty("_error_prefix");
			$error_prefix->setAccessible(TRUE);
			$this->error_start_delimiter = $error_prefix->getValue($this->form_validation);
			$this->message_start_delimiter = $this->error_start_delimiter;

			$error_suffix = $form_validation_class->getProperty("_error_suffix");
			$error_suffix->setAccessible(TRUE);
			$this->error_end_delimiter = $error_suffix->getValue($this->form_validation);
			$this->message_end_delimiter = $this->error_end_delimiter;
		}
		else
		{
			// use delimiters from config
			$this->message_start_delimiter = $this->config->item('message_start_delimiter', 'ion_auth');
			$this->message_end_delimiter = $this->config->item('message_end_delimiter', 'ion_auth');
			$this->error_start_delimiter = $this->config->item('error_start_delimiter', 'ion_auth');
			$this->error_end_delimiter = $this->config->item('error_end_delimiter', 'ion_auth');
		}

		// initialize our hooks object
		$this->_ion_hooks = new stdClass;

		// load the bcrypt class if needed
		if ($this->hash_method == 'bcrypt')
		{
			if ($this->random_rounds)
			{
				$rand = rand($this->min_rounds,$this->max_rounds);
				$params = array('rounds' => $rand);
			}
			else
			{
				$params = array('rounds' => $this->default_rounds);
			}

			$params['salt_prefix'] = $this->config->item('salt_prefix', 'ion_auth');
			$this->load->library('bcrypt',$params);
		}

		$this->trigger_events('model_constructor');
	}

	public function hash_password($password, $salt = FALSE, $use_sha1_override = FALSE)
	{
		if (empty($password))
		{
			return FALSE;
		}

		// bcrypt
		if ($use_sha1_override === FALSE && $this->hash_method == 'bcrypt')
		{
			return $this->bcrypt->hash($password);
		}


		if ($this->store_salt && $salt)
		{
			return sha1($password . $salt);
		}
		else
		{
			$salt = $this->salt();
			return $salt . substr(sha1($salt . $password), 0, -$this->salt_length);
		}
	}

	public function hash_password_db($id, $password, $use_sha1_override = FALSE)
	{
		if (empty($id) || empty($password))
		{
			return FALSE;
		}

		$this->trigger_events('extra_where');

		$query = $this->db->select('PASSWORD, SALT')
		                  ->where('ID', $id)
		                  ->limit(1)
		                  ->order_by('ID', 'desc')
		                  ->get($this->tables['users']);

		$hash_password_db = $query->row();

		if ($query->num_rows() !== 1)
		{
			return FALSE;
		}

		// bcrypt
		if ($use_sha1_override === FALSE && $this->hash_method == 'bcrypt')
		{
			if ($this->bcrypt->verify($password,$hash_password_db->PASSWORD))
			{
				return TRUE;
			}

			return FALSE;
		}

		// sha1
		if ($this->store_salt)
		{
			$db_password = sha1($password . $hash_password_db->salt);
		}
		else
		{
			$salt = substr($hash_password_db->PASSWORD, 0, $this->salt_length);

			$db_password =  $salt . substr(sha1($salt . $password), 0, -$this->salt_length);
		}

		if($db_password == $hash_password_db->PASSWORD)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	public function hash_code($password)
	{
		return $this->hash_password($password, FALSE, TRUE);
	}

	public function salt()
	{
		$raw_salt_len = 16;

		$buffer = '';
		$buffer_valid = FALSE;

		if (function_exists('random_bytes'))
		{
			$buffer = random_bytes($raw_salt_len);
			if ($buffer)
			{
				$buffer_valid = TRUE;
			}
		}

		if (!$buffer_valid && function_exists('mcrypt_create_iv') && !defined('PHALANGER'))
		{
			$buffer = mcrypt_create_iv($raw_salt_len, MCRYPT_DEV_URANDOM);
			if ($buffer)
			{
				$buffer_valid = TRUE;
			}
		}

		if (!$buffer_valid && function_exists('openssl_random_pseudo_bytes'))
		{
			$buffer = openssl_random_pseudo_bytes($raw_salt_len);
			if ($buffer)
			{
				$buffer_valid = TRUE;
			}
		}

		if (!$buffer_valid && @is_readable('/dev/urandom'))
		{
			$f = fopen('/dev/urandom', 'r');
			$read = strlen($buffer);
			while ($read < $raw_salt_len)
			{
				$buffer .= fread($f, $raw_salt_len - $read);
				$read = strlen($buffer);
			}
			fclose($f);
			if ($read >= $raw_salt_len)
			{
				$buffer_valid = TRUE;
			}
		}

		if (!$buffer_valid || strlen($buffer) < $raw_salt_len)
		{
			$bl = strlen($buffer);
			for ($i = 0; $i < $raw_salt_len; $i++)
			{
				if ($i < $bl)
				{
					$buffer[$i] = $buffer[$i] ^ chr(mt_rand(0, 255));
				}
				else
				{
					$buffer .= chr(mt_rand(0, 255));
				}
			}
		}

		$salt = $buffer;

		// encode string with the Base64 variant used by crypt
		$base64_digits = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
		$bcrypt64_digits = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		$base64_string = base64_encode($salt);
		$salt = strtr(rtrim($base64_string, '='), $base64_digits, $bcrypt64_digits);

		$salt = substr($salt, 0, $this->salt_length);

		return $salt;
	}

	public function activate($id, $code = FALSE)
	{
		$this->trigger_events('pre_activate');

		if ($code !== FALSE)
		{
			$query = $this->db->select($this->identity_column)
			                  ->where('ACTIVATION_CODE', $code)
			                  ->where('ID', $id)
			                  ->limit(1)
			                  ->order_by('ID', 'desc')
			                  ->get($this->tables['users']);

			$query->row();

			if ($query->num_rows() !== 1)
			{
				$this->trigger_events(array('post_activate', 'post_activate_unsuccessful'));
				$this->set_error('activate_unsuccessful');
				return FALSE;
			}

			$data = array(
			    'ACTIVATION_CODE' => NULL,
			    'ACTIVE'          => 1
			);

			$this->trigger_events('extra_where');
			$this->db->update($this->tables['users'], $data, array('ID' => $id));
		}
		else
		{
			$data = array(
			    'ACTIVATION_CODE' => NULL,
			    'ACTIVE'          => 1
			);

			$this->trigger_events('extra_where');
			$this->db->update($this->tables['users'], $data, array('ID' => $id));
		}

		$return = $this->db->affected_rows() == 1;
		if ($return)
		{
			$this->trigger_events(array('post_activate', 'post_activate_successful'));
			$this->set_message('activate_successful');
		}
		else
		{
			$this->trigger_events(array('post_activate', 'post_activate_unsuccessful'));
			$this->set_error('activate_unsuccessful');
		}

		return $return;
	}


	public function deactivate($id = NULL)
	{
		$this->trigger_events('deactivate');

		if (!isset($id))
		{
			$this->set_error('deactivate_unsuccessful');
			return FALSE;
		}
		else if ($this->ion_auth->logged_in() && $this->user()->row()->ID == $id)
		{
			$this->set_error('deactivate_current_user_unsuccessful');
			return FALSE;
		}

		$activation_code = sha1(md5(microtime()));
		$this->activation_code = $activation_code;

		$data = array(
		    'ACTIVATION_CODE' => $activation_code,
		    'ACTIVE'          => 0
		);

		$this->trigger_events('extra_where');
		$this->db->update($this->tables['users'], $data, array('ID' => $id));

		$return = $this->db->affected_rows() == 1;
		if ($return)
		{
			$this->set_message('deactivate_successful');
		}
		else
		{
			$this->set_error('deactivate_unsuccessful');
		}

		return $return;
	}

	public function reset_password($identity, $new) {
		$this->trigger_events('pre_change_password');

		if (!$this->identity_check($identity)) {
			$this->trigger_events(array('post_change_password', 'post_change_password_unsuccessful'));
			return FALSE;
		}

		$this->trigger_events('extra_where');

		$query = $this->db->select('ID, PASSWORD, SALT')
		                  ->where($this->identity_column, $identity)
		                  ->limit(1)
		                  ->order_by('ID', 'desc')
		                  ->get($this->tables['users']);

		if ($query->num_rows() !== 1)
		{
			$this->trigger_events(array('post_change_password', 'post_change_password_unsuccessful'));
			$this->set_error('password_change_unsuccessful');
			return FALSE;
		}

		$result = $query->row();

		$new = $this->hash_password($new);

		// store the new password and reset the remember code so all remembered instances have to re-login
		// also clear the forgotten password code
		$data = array(
			'PASSWORD'                => $new,
			'REMEMBER_CODE'           => NULL,
			'FORGOTTEN_PASSWORD_CODE' => NULL,
			'FORGOTTEN_PASSWORD_TIME' => NULL
		);

		$this->trigger_events('extra_where');
		$this->db->update($this->tables['users'], $data, array($this->identity_column => $identity));
		$last_query = $this->db->last_query();
		$this->log_history("update", $last_query);

		$return = $this->db->affected_rows() == 1;
		if ($return)
		{
			$this->last_update($this->tables['users'], $result->ID);

			$this->trigger_events(array('post_change_password', 'post_change_password_successful'));
			$this->set_message('password_change_successful');
		}
		else
		{
			$this->trigger_events(array('post_change_password', 'post_change_password_unsuccessful'));
			$this->set_error('password_change_unsuccessful');
		}

		return $return;
	}

	public function last_update($table, $id){

		$last_update_by = $this->session->userdata('identity');
		$query_update   = "UPDATE ".$table." SET LAST_UPDATE_DATE = SYSDATE,
												 LAST_UPDATE_BY   = '".$last_update_by."'
									WHERE ID = '".$id."'";

		if($this->db->query($query_update)){
			return true;
		}
	}

	public function log_history($action, $query_string, $keterangan = ""){

		$user_name  = $this->session->userdata('identity');
		$ip_address = $this->_prepare_ip($this->input->ip_address());

		$query_string = str_replace('"', '', $query_string);
		$query_string = str_replace("'", '"', $query_string);

		$query_insert = "INSERT INTO QRCODE_USER_LOG (IP_ADDRESS, USER_NAME, ACTION, QUERY_STRING, CREATION_DATE, DESCRIPTION)
									VALUES ('".$ip_address."', '".$user_name."', '".$action."', '".$query_string."' , SYSDATE, '".$keterangan."')";

		if($this->db->query($query_insert)){
			return true;
		}
	}

	public function change_password($identity, $old, $new)
	{
		$this->trigger_events('pre_change_password');

		$this->trigger_events('extra_where');

		$query = $this->db->select('ID, PASSWORD, SALT')
		                  ->where($this->identity_column, $identity)
		                  ->limit(1)
		                  ->order_by('ID', 'desc')
		                  ->get($this->tables['users']);

		if ($query->num_rows() !== 1)
		{
			$this->trigger_events(array('post_change_password', 'post_change_password_unsuccessful'));
			$this->set_error('password_change_unsuccessful');
			return FALSE;
		}

		$user = $query->row();

		$old_password_matches = $this->hash_password_db($user->ID, $old);

		if ($old_password_matches === TRUE)
		{
			// store the new password and reset the remember code so all remembered instances have to re-login
			$hashed_new_password  = $this->hash_password($new, $user->SALT);
			$data = array(
			    'PASSWORD' => $hashed_new_password,
			    'REMEMBER_CODE' => NULL,
			);

			$this->trigger_events('extra_where');

			$successfully_changed_password_in_db = $this->db->update($this->tables['users'], $data, array($this->identity_column => $identity));
			if ($successfully_changed_password_in_db)
			{
				$this->trigger_events(array('post_change_password', 'post_change_password_successful'));
				$this->set_message('password_change_successful');
			}
			else
			{
				$this->trigger_events(array('post_change_password', 'post_change_password_unsuccessful'));
				$this->set_error('password_change_unsuccessful');
			}

			return $successfully_changed_password_in_db;
		}

		$this->set_error('password_change_unsuccessful');
		return FALSE;
	}

	public function username_check($username = '')
	{
		$this->trigger_events('username_check');

		if (empty($username))
		{
			return FALSE;
		}

		$this->trigger_events('extra_where');

		return $this->db->where('USER_NAME', $username)
						->limit(1)
						->count_all_results($this->tables['users']) > 0;
	}

	public function email_check($email = '')
	{
		$this->trigger_events('email_check');

		if (empty($email))
		{
			return FALSE;
		}

		$this->trigger_events('extra_where');

		return $this->db->where('EMAIL', $email)
						->limit(1)
						->count_all_results($this->tables['users']) > 0;
	}

	public function identity_check($identity = '')
	{
		$this->trigger_events('identity_check');

		if (empty($identity))
		{
			return FALSE;
		}

		return $this->db->where($this->identity_column, $identity)
						->limit(1)
						->count_all_results($this->tables['users']) > 0;
	}



	public function group_check($user_id)
	{

		if (empty($user_id))
		{
			return FALSE;
		}

		$query = $this->db->select("*")
						  ->where("USER_ID", $user_id)
						  ->get($this->tables['users_groups']);

		if ($query->num_rows() >0){
			return TRUE;
		}
	}


	public function register($identity, $password, $email, $additional_data = array(), $groups = array())
	{

		$this->trigger_events('pre_register');

		$manual_activation = $this->config->item('manual_activation', 'ion_auth');

		// IP Address
		$ip_address = $this->_prepare_ip($this->input->ip_address());
		$salt       = $this->store_salt ? $this->salt() : FALSE;
		$password   = $this->hash_password($password, $salt);

		// Users table.
		$data = array(
			$this->identity_column => $identity,
			'USER_NAME'            => $identity,
			'PASSWORD'             => $password,
			'EMAIL'                => $email,
			'IP_ADDRESS'           => $ip_address,
			'CREATED_ON'           => time(),
			'ACTIVE'               => ($manual_activation === FALSE ? 1 : 0)
		);

		if ($this->store_salt)
		{
			$data['SALT'] = $salt;
		}

		// filter out any data passed that doesnt have a matching column in the users table
		// and merge the set user data and the additional data
		$user_data = array_merge($this->_filter_data($this->tables['users'], $additional_data), $data);

		$this->trigger_events('extra_set');

		$this->db->insert($this->tables['users'], $user_data);
		$last_query = $this->db->last_query();
		$this->log_history("insert", $last_query);

		// $id = $this->db->insert_id($this->tables['users'] . '_id_seq');
/*		$fix_id = $this->db->query("SELECT MAX (ID) AS ID FROM QRCODE_USER");
		$id     = $fix_id->row()->ID;*/

		$fix_id = $this->db->query("SELECT ".$this->tables['users']."_SEQ.NEXTVAL AS NEXTID FROM DUAL");

//		$fix_id = $this->db->query("SELECT ".$this->tables['users']."_SEQ.NEXTVAL AS NEXTID FROM ".$this->tables['users']);

		$id     = $fix_id->row()->NEXTID;

		// add in groups array if it doesn't exists and stop adding into default group if default group ids are set
		// if (isset($default_group->ID) && empty($groups))
		// {
		// 	$groups[] = $default_group->ID;
		// }


		if (!empty($groups))
		{
			// add to groups
			foreach ($groups as $group)
			{
				$this->add_to_group($group, $id);
			}
		}

		$this->trigger_events('post_register');
		$this->last_update($this->tables['users'], $id);

		return (isset($id)) ? $id : FALSE;
	}

	/**
	 * login
	 *
	 * @param    string $identity
	 * @param    string $password
	 * @param    bool   $remember
	 *
	 * @return    bool
	 * @author    Mathew
	 */
	public function login($identity, $password, $remember=FALSE)
	{
		$this->trigger_events('pre_login');

		if (empty($identity) || empty($password))
		{
			$this->set_error('login_unsuccessful');
			return FALSE;
		}

		$this->trigger_events('extra_where');

		$query = $this->db->select($this->identity_column . ', DISPLAY_NAME, EMAIL, ID, PASSWORD, ACTIVE, LAST_LOGIN')
						  ->where($this->identity_column, $identity)
						  ->limit(1)
						  ->get($this->tables['users']);

		if ($this->is_max_login_attempts_exceeded($identity))
		{
			// Hash something anyway, just to take up time
			$this->hash_password($password);
			$this->trigger_events('post_login_unsuccessful');
			$this->set_error('login_timeout');

			return FALSE;
		}
		if ($query->num_rows() === 1)
		{
			$user     = $query->row();
			$password = $this->hash_password_db($user->ID, $password);

			if ($password === TRUE)
			{

				if ($user->ACTIVE == 0)
				{
					$this->trigger_events('post_login_unsuccessful');
					$this->set_error('login_unsuccessful_not_active');

					return FALSE;
				}

				if(!$this->group_check($user->ID)){
					return FALSE;
				}

				$this->set_session($user);
				$this->update_last_login($user->ID);
				$this->clear_login_attempts($identity);
				
				if ($remember && $this->config->item('remember_users', 'ion_auth'))
				{
					$this->remember_user($user->ID);
				}

				$this->trigger_events(array('post_login', 'post_login_successful'));
				$this->set_message('login_successful');

				return TRUE;
			}
		}

		// Hash something anyway, just to take up time
		$this->hash_password($password);

		$this->increase_login_attempts($identity);

		$this->trigger_events('post_login_unsuccessful');
		$this->set_error('login_unsuccessful');

		return FALSE;
	}

	/**
	 * Verifies if the session should be rechecked according to the configuration item recheck_timer. If it does, then
	 * it will check if the user is still active
	 * @return bool
	 */
	public function recheck_session()
	{
		$recheck = (NULL !== $this->config->item('recheck_timer', 'ion_auth')) ? $this->config->item('recheck_timer', 'ion_auth') : 0;

		if ($recheck !== 0)
		{
			$last_login = $this->session->userdata('last_check');
			if ($last_login + $recheck < time())
			{
				$query = $this->db->select('ID')
								  ->where(array($this->identity_column => $this->session->userdata('identity'), 'ACTIVE' => '1'))
								  ->limit(1)
								  ->order_by('ID', 'desc')
								  ->get($this->tables['users']);
				if ($query->num_rows() === 1)
				{
					$this->session->set_userdata('last_check', time());
				}
				else
				{
					$this->trigger_events('logout');

					$identity = $this->config->item('identity', 'ion_auth');

					if (substr(CI_VERSION, 0, 1) == '2')
					{
						$this->session->unset_userdata(array($identity => '', 'id' => '', 'user_id' => ''));
					}
					else
					{
						$this->session->unset_userdata(array($identity, 'id', 'user_id'));
					}
					return FALSE;
				}
			}
		}

		return (bool)$this->session->userdata('identity');
	}

	/**
	 * is_max_login_attempts_exceeded
	 * Based on code from Tank Auth, by Ilya Konyukhov (https://github.com/ilkon/Tank-Auth)
	 *
	 * @param string      $identity   user's identity
	 * @param string|null $ip_address IP address
	 *                                Only used if track_login_ip_address is set to TRUE.
	 *                                If NULL (default value), the current IP address is used.
	 *                                Use get_last_attempt_ip($identity) to retrieve a user's last IP
	 *
	 * @return boolean
	 */
	public function is_max_login_attempts_exceeded($identity, $ip_address = NULL)
	{
		if ($this->config->item('track_login_attempts', 'ion_auth'))
		{
			$max_attempts = $this->config->item('maximum_login_attempts', 'ion_auth');
			if ($max_attempts > 0)
			{
				$attempts = $this->get_attempts_num($identity, $ip_address);
				return $attempts >= $max_attempts;
			}
		}
		return FALSE;
	}

	/**
	 * Get number of login attempts for the given IP-address or identity
	 * Based on code from Tank Auth, by Ilya Konyukhov (https://github.com/ilkon/Tank-Auth)
	 *
	 * @param string      $identity   User's identity
	 * @param string|null $ip_address IP address
	 *                                Only used if track_login_ip_address is set to TRUE.
	 *                                If NULL (default value), the current IP address is used.
	 *                                Use get_last_attempt_ip($identity) to retrieve a user's last IP
	 *
	 * @return int
	 */
	public function get_attempts_num($identity, $ip_address = NULL)
	{
		if ($this->config->item('track_login_attempts', 'ion_auth'))
		{
			$this->db->select('1', FALSE);
			$this->db->where('LOGIN', $identity);
			if ($this->config->item('track_login_ip_address', 'ion_auth'))
			{
				if (!isset($ip_address))
				{
					$ip_address = $this->_prepare_ip($this->input->ip_address());
				}
				$this->db->where('IP_ADDRESS', $ip_address);
			}
			$this->db->where('TIME >', time() - $this->config->item('lockout_time', 'ion_auth'), FALSE);
			$qres = $this->db->get($this->tables['login_attempts']);
			return $qres->num_rows();
		}
		return 0;
	}

	/**
	 * @deprecated This function is now only a wrapper for is_max_login_attempts_exceeded() since it only retrieve
	 *             attempts within the given period.
	 *
	 * @param string      $identity   User's identity
	 * @param string|null $ip_address IP address
	 *                                Only used if track_login_ip_address is set to TRUE.
	 *                                If NULL (default value), the current IP address is used.
	 *                                Use get_last_attempt_ip($identity) to retrieve a user's last IP
	 *
	 * @return boolean Whether an account is locked due to excessive login attempts within a given period
	 */
	public function is_time_locked_out($identity, $ip_address = NULL)
	{
		return $this->is_max_login_attempts_exceeded($identity, $ip_address);
	}

	/**
	 * @deprecated This function is now only a wrapper for is_max_login_attempts_exceeded() since it only retrieve
	 *             attempts within the given period.
	 *
	 * @param string      $identity   User's identity
	 * @param string|null $ip_address IP address
	 *                                Only used if track_login_ip_address is set to TRUE.
	 *                                If NULL (default value), the current IP address is used.
	 *                                Use get_last_attempt_ip($identity) to retrieve a user's last IP
	 *
	 * @return int The time of the last login attempt for a given IP-address or identity
	 */
	public function get_last_attempt_time($identity, $ip_address = NULL)
	{
		if ($this->config->item('track_login_attempts', 'ion_auth'))
		{
			$this->db->select('TIME');
			$this->db->where('LOGIN', $identity);
			if ($this->config->item('track_login_ip_address', 'ion_auth'))
			{
				if (!isset($ip_address))
				{
					$ip_address = $this->_prepare_ip($this->input->ip_address());
				}
				$this->db->where('IP_ADDRESS', $ip_address);
			}
			$this->db->order_by('ID', 'desc');
			$qres = $this->db->get($this->tables['login_attempts'], 1);

			if ($qres->num_rows() > 0)
			{
				return $qres->row()->time;
			}
		}

		return 0;
	}

	/**
	 * Get the IP address of the last time a login attempt occured from given identity
	 *
	 * @param string $identity User's identity
	 *
	 * @return string
	 */
	public function get_last_attempt_ip($identity)
	{
		if ($this->config->item('track_login_attempts', 'ion_auth') && $this->config->item('track_login_ip_address', 'ion_auth'))
		{
			$this->db->select('IP_ADDRESS');
			$this->db->where('LOGIN', $identity);
			$this->db->order_by('ID', 'desc');
			$qres = $this->db->get($this->tables['login_attempts'], 1);

			if ($qres->num_rows() > 0)
			{
				return $qres->row()->IP_ADDRESS;
			}
		}

		return '';
	}

	/**
	 * Based on code from Tank Auth, by Ilya Konyukhov (https://github.com/ilkon/Tank-Auth)
	 *
	 * Note: the current IP address will be used if track_login_ip_address config value is TRUE
	 *
	 * @param string $identity User's identity
	 *
	 * @return bool
	 */
	public function increase_login_attempts($identity)
	{
		if ($this->config->item('track_login_attempts', 'ion_auth'))
		{
			$data = array('IP_ADDRESS' => '', 'LOGIN' => $identity, 'TIME' => time());
			if ($this->config->item('track_login_ip_address', 'ion_auth'))
			{
				$data['IP_ADDRESS'] = $this->_prepare_ip($this->input->ip_address());
			}
			return $this->db->insert($this->tables['login_attempts'], $data);
		}
		return FALSE;
	}

	/**
	 * clear_login_attempts
	 * Based on code from Tank Auth, by Ilya Konyukhov (https://github.com/ilkon/Tank-Auth)
	 *
	 * @param string      $identity                   User's identity
	 * @param int         $old_attempts_expire_period In seconds, any attempts older than this value will be removed.
	 *                                                It is used for regularly purging the attempts table.
	 *                                                (for security reason, minimum value is lockout_time config value)
	 * @param string|null $ip_address                 IP address
	 *                                                Only used if track_login_ip_address is set to TRUE.
	 *                                                If NULL (default value), the current IP address is used.
	 *                                                Use get_last_attempt_ip($identity) to retrieve a user's last IP
	 *
	 * @return bool
	 */
	public function clear_login_attempts($identity, $old_attempts_expire_period = 86400, $ip_address = NULL)
	{
		if ($this->config->item('track_login_attempts', 'ion_auth'))
		{
			// Make sure $old_attempts_expire_period is at least equals to lockout_time
			$old_attempts_expire_period = max($old_attempts_expire_period, $this->config->item('lockout_time', 'ion_auth'));

			$this->db->where('LOGIN', $identity);
			if ($this->config->item('track_login_ip_address', 'ion_auth'))
			{
				if (!isset($ip_address))
				{
					$ip_address = $this->_prepare_ip($this->input->ip_address());
				}
				$this->db->where('IP_ADDRESS', $ip_address);
			}
			// Purge obsolete login attempts
			$this->db->or_where('TIME <', time() - $old_attempts_expire_period, FALSE);

			return $this->db->delete($this->tables['login_attempts']);
		}
		return FALSE;
	}

	/**
	 * @param int $limit
	 *
	 * @return static
	 */
	public function limit($limit)
	{
		$this->trigger_events('limit');
		$this->_ion_limit = $limit;

		return $this;
	}

	/**
	 * @param int $offset
	 *
	 * @return static
	 */
	public function offset($offset)
	{
		$this->trigger_events('offset');
		$this->_ion_offset = $offset;

		return $this;
	}

	/**
	 * @param array|string $where
	 * @param null|string  $value
	 *
	 * @return static
	 */
	public function where($where, $value = NULL)
	{
		$this->trigger_events('where');

		if (!is_array($where))
		{
			$where = array($where => $value);
		}

		array_push($this->_ion_where, $where);

		return $this;
	}

	/**
	 * @param string      $like
	 * @param string|null $value
	 * @param string      $position
	 *
	 * @return static
	 */
	public function like($like, $value = NULL, $position = 'both')
	{
		$this->trigger_events('like');

		array_push($this->_ion_like, array(
			'like'     => $like,
			'value'    => $value,
			'position' => $position
		));

		return $this;
	}

	/**
	 * @param array|string $select
	 *
	 * @return static
	 */
	public function select($select)
	{
		$this->trigger_events('select');

		$this->_ion_select[] = $select;

		return $this;
	}

	/**
	 * @param string $by
	 * @param string $order
	 *
	 * @return static
	 */
	public function order_by($by, $order='desc')
	{
		$this->trigger_events('order_by');

		$this->_ion_order_by = $by;
		$this->_ion_order    = $order;

		return $this;
	}

	/**
	 * @return object|mixed
	 */
	public function row()
	{
		$this->trigger_events('row');

		$row = $this->response->row();

		return $row;
	}

	/**
	 * @return array|mixed
	 */
	public function row_array()
	{
		$this->trigger_events(array('row', 'row_array'));

		$row = $this->response->row_array();

		return $row;
	}

	/**
	 * @return mixed
	 */
	public function result()
	{
		$this->trigger_events('result');

		$result = $this->response->result();

		return $result;
	}

	/**
	 * @return array|mixed
	 */
	public function result_array()
	{
		$this->trigger_events(array('result', 'result_array'));

		$result = $this->response->result_array();

		return $result;
	}

	/**
	 * @return int
	 */
	public function num_rows()
	{
		$this->trigger_events(array('num_rows'));

		$result = $this->response->num_rows();

		return $result;
	}


	public function get_users($start, $length, $draw, $keywords = ""){

		$where	= "";
		if($keywords) {
			$where	= "AND upper(USER_NAME) like '%".strtoupper($keywords)."%' or upper(DISPLAY_NAME) like '%".strtoupper($keywords)."%' or EMAIL like '%".strtolower($keywords)."%'";
		}
					  
		$sql = "SELECT * FROM (
						SELECT rownum rnum, a.* 
						FROM(
							SELECT  U.*, to_char(LAST_UPDATE_DATE, 'yyyy-mm-dd hh24:mi') LAST_UPDATE_DATE2
							FROM ".$this->tables['users']." u WHERE ID != 1 ".$where."
							ORDER BY ID 
						) a 
						WHERE rownum <= ".$start." + ".$length."
					)
					WHERE rnum > ".$start;
		
		$query = $this->db->query($sql);

		return $query->result();

	}

	public function get_total_users($keywords = ""){

		$where	= "";
		if($keywords) {
			$where	= "AND upper(USER_NAME) like '%".strtoupper($keywords)."%' or upper(DISPLAY_NAME) like '%".strtoupper($keywords)."%' or EMAIL like '%".strtolower($keywords)."%'";
		}

		$query = $this->db->query("SELECT * FROM ".$this->tables['users']." WHERE ID != 1 ".$where);		
		return $query->num_rows();

	}

	function get_identity($id){
		
    	$this->db->select('USER_NAME');
		$this->db->from($this->tables['users']); 
		$this->db->where('ID', $id);
		$query = $this->db->get();

		return $query->row()->USER_NAME;
	}

	public function get_groups($start, $length, $draw, $keywords = ""){

		$where	= "";
		if($keywords) {
			$where	= "AND upper(NAME) like '%".strtoupper($keywords)."%'";
		}
					  
		$sql = "SELECT * FROM (
						SELECT rownum rnum, a.* 
						FROM(
							SELECT *
							FROM ".$this->tables['groups']." WHERE ID != 1 ".$where."
							ORDER BY ID 
						) a 
						WHERE rownum <= ".$start." + ".$length."
					)
					WHERE rnum > ".$start;
		
		$query = $this->db->query($sql);

		return $query->result();

	}

	public function get_total_groups($keywords = ""){

		$where	= "";
		if($keywords) {
			$where	= "AND upper(NAME) like '%".strtoupper($keywords)."%'";
		}

		$query = $this->db->query("SELECT * FROM ".$this->tables['groups']." WHERE ID != 1 ".$where);		
		return $query->num_rows();

	}


	/**
	 * users
	 *
	 * @param array|null $groups
	 *
	 * @return static
	 * @author Ben Edmunds
	 */
	public function users($groups = NULL)
	{
		$this->trigger_events('users');

		if (isset($this->_ion_select) && !empty($this->_ion_select))
		{
			foreach ($this->_ion_select as $select)
			{
				$this->db->select($select);
			}

			$this->_ion_select = array();
		}
		else
		{
			// default selects
			$this->db->select(array(
			    $this->tables['users'].'.*',
			    $this->tables['users'].'.ID as ID2',
			    $this->tables['users'].'.ID as USER_ID'
			));
		}

		// filter by group id(s) if passed
		if (isset($groups))
		{
			// build an array if only one group was passed
			if (!is_array($groups))
			{
				$groups = Array($groups);
			}

			// join and then run a where_in against the group ids
			if (isset($groups) && !empty($groups))
			{
				$this->db->distinct();
				$this->db->join(
				    $this->tables['users_groups'],
				    $this->tables['users_groups'].'.'.$this->join['users'].'='.$this->tables['users'].'.ID',
				    'inner'
				);
			}

			// verify if group name or group id was used and create and put elements in different arrays
			$group_ids = array();
			$group_names = array();
			foreach($groups as $group)
			{
				if(is_numeric($group)) $group_ids[] = $group;
				else $group_names[] = $group;
			}
			$or_where_in = (!empty($group_ids) && !empty($group_names)) ? 'or_where_in' : 'where_in';
			// if group name was used we do one more join with groups
			if(!empty($group_names))
			{
				$this->db->join($this->tables['groups'], $this->tables['users_groups'] . '.' . $this->join['groups'] . ' = ' . $this->tables['groups'] . '.ID', 'inner');
				$this->db->where_in($this->tables['groups'] . '.NAME', $group_names);
			}
			if(!empty($group_ids))
			{
				$this->db->{$or_where_in}($this->tables['users_groups'].'.'.$this->join['groups'], $group_ids);
			}

			
		}

		$this->trigger_events('extra_where');

		// run each where that was passed
		if (isset($this->_ion_where) && !empty($this->_ion_where))
		{
			foreach ($this->_ion_where as $where)
			{
				$this->db->where($where);
			}

			$this->_ion_where = array();
		}

		if (isset($this->_ion_like) && !empty($this->_ion_like))
		{
			foreach ($this->_ion_like as $like)
			{
				$this->db->or_like($like['like'], $like['value'], $like['position']);
			}

			$this->_ion_like = array();
		}

		if (isset($this->_ion_limit) && isset($this->_ion_offset))
		{
			$this->db->limit($this->_ion_limit, $this->_ion_offset);

			$this->_ion_limit  = NULL;
			$this->_ion_offset = NULL;
		}
		else if (isset($this->_ion_limit))
		{
			$this->db->limit($this->_ion_limit);

			$this->_ion_limit  = NULL;
		}

		// set the order
		if (isset($this->_ion_order_by) && isset($this->_ion_order))
		{
			$this->db->order_by($this->_ion_order_by, $this->_ion_order);

			$this->_ion_order    = NULL;
			$this->_ion_order_by = NULL;
		}

		$this->response = $this->db->get($this->tables['users']);

		return $this;
	}

	/**
	 * user
	 *
	 * @param int|string|null $id
	 *
	 * @return static
	 * @author Ben Edmunds
	 */
	public function user($id = NULL)
	{
		$this->trigger_events('user');

		// if no id was passed use the current users id
		$id = isset($id) ? $id : $this->session->userdata('user_id');

		$this->limit(1);
		$this->order_by($this->tables['users'].'.ID', 'desc');
		$this->where($this->tables['users'].'.ID', $id);

		$this->users();

		return $this;
	}

	/**
	 * get_users_groups
	 *
	 * @param int|string|bool $id
	 *
	 * @return CI_DB_result
	 * @author Ben Edmunds
	 */
	public function get_users_groups($id = FALSE)
	{
		$this->trigger_events('get_users_group');

		// if no id was passed use the current users id
		$id || $id = $this->session->userdata('user_id');

		return $this->db->select($this->tables['users_groups'].'.'.$this->join['groups'].' as ID, '.$this->tables['groups'].'.NAME, '.$this->tables['groups'].'.DESCRIPTION')
		                ->where($this->tables['users_groups'].'.'.$this->join['users'], $id)
		                ->join($this->tables['groups'], $this->tables['users_groups'].'.'.$this->join['groups'].'='.$this->tables['groups'].'.ID')
		                ->get($this->tables['users_groups']);
	}
	/**
	 * get_users_groups
	 *
	 * @param int|string|bool $id
	 *
	 * @return CI_DB_result
	 * @author Ben Edmunds
	 */
	public function get_groups_menus($id = FALSE)
	{

		// if no id was passed use the current users id
		$id || $id = $this->session->userdata('user_id');

		return $this->db->select($this->tables['groups_menus'].'.'.$this->join['menus'].' as ID, '.$this->tables['menus'].'.TITLE, '.$this->tables['menus'].'.URL')
		                ->where($this->tables['groups_menus'].'.'.$this->join['groups'], $id)
		                ->join($this->tables['menus'], $this->tables['groups_menus'].'.'.$this->join['menus'].'='.$this->tables['menus'].'.ID')
		                ->get($this->tables['groups_menus']);
	}

	public function get_groups_menu_id($id = FALSE)
	{

		$this->db->select($this->tables['groups_menus'].'.'.$this->join['menus'].' as ID, '.$this->tables['menus'].'.TITLE, '.$this->tables['menus'].'.URL');
		$this->db->from($this->tables['groups_menus']); 
		$this->db->where_IN($this->tables['groups_menus'].'.'.$this->join['groups'], $id);
		$this->db->join($this->tables['menus'], $this->tables['groups_menus'].'.'.$this->join['menus'].'='.$this->tables['menus'].'.ID');

		$query = $this->db->get();

		return $query->result();
		                
	}

	public function add_to_group($group_ids, $user_id = FALSE)
	{
		$this->trigger_events('add_to_group');

		// if no id was passed use the current users id
		$user_id || $user_id = $this->session->userdata('user_id');

		if(!is_array($group_ids))
		{
			$group_ids = array($group_ids);
		}

		$return = 0;

		// Then insert each into the database
		foreach ($group_ids as $group_id)
		{
			// Cast to float to support bigint data type
			if ($this->db->insert(
								  $this->tables['users_groups'],
								  array(
								  	$this->join['groups'] => (float)$group_id,
									$this->join['users']  => (float)$user_id
								  )
								)
			)
			{
				if (isset($this->_cache_groups[$group_id]))
				{
					$group_name = $this->_cache_groups[$group_id];
				}
				else
				{
					$group = $this->group($group_id)->result();
					$group_name = $group[0]->NAME;
					$this->_cache_groups[$group_id] = $group_name;
				}
				$this->_cache_user_in_group[$user_id][$group_id] = $group_name;

				// Return the number of groups added
				$return++;
			}
		}

		return $return;
	}

	public function add_to_menu($menu_ids, $group_id = FALSE)
	{
		$this->trigger_events('add_to_menu');

		if(!is_array($menu_ids))
		{
			$menu_ids = array($menu_ids);
		}

		$return = 0;

		// Then insert each into the database
		foreach ($menu_ids as $menu_id)
		{
			// Cast to float to support bigint data type
			if ($this->db->insert(
								  $this->tables['groups_menus'],
								  array(
								  	$this->join['menus'] => (float)$menu_id,
									$this->join['groups'] => (float)$group_id
								  )
								)
			)
			{
				if (isset($this->_cache_menus[$menu_id]))
				{
					$menu_name = $this->_cache_menus[$menu_id];
				}
				else
				{
					$menu = $this->menu($menu_id)->result();
					$menu_name = $menu[0]->TITLE;
					$this->_cache_menus[$menu_id] = $menu_name;
				}
				$this->_cache_group_in_menu[$menu_id][$menu_id] = $menu_name;

				// Return the number of menus added
				$return++;
			}
		}

		return $return;
	}

	public function remove_from_group($group_ids = FALSE, $user_id = FALSE)
	{
		$this->trigger_events('remove_from_group');

		// user id is required
		if (empty($user_id))
		{
			return FALSE;
		}

		// if group id(s) are passed remove user from the group(s)
		if (!empty($group_ids))
		{
			if (!is_array($group_ids))
			{
				$group_ids = array($group_ids);
			}

			foreach ($group_ids as $group_id)
			{
				// Cast to float to support bigint data type
				$this->db->delete(
					$this->tables['users_groups'],
					array($this->join['groups'] => (float)$group_id, $this->join['users'] => (float)$user_id)
				);
				if (isset($this->_cache_user_in_group[$user_id]) && isset($this->_cache_user_in_group[$user_id][$group_id]))
				{
					unset($this->_cache_user_in_group[$user_id][$group_id]);
				}
			}

			$return = TRUE;
		}
		// otherwise remove user from all groups
		else
		{
			// Cast to float to support bigint data type
			if ($return = $this->db->delete($this->tables['users_groups'], array($this->join['users'] => (float)$user_id)))
			{
				$this->_cache_user_in_group[$user_id] = array();
			}
		}
		return $return;
	}

	public function remove_from_menu($menu_ids = FALSE, $group_id = FALSE)
	{
		$this->trigger_events('remove_from_menu');

		// user id is required
		if (empty($group_id))
		{
			return FALSE;
		}

		// if group id(s) are passed remove user from the group(s)
		if (!empty($menu_ids))
		{
			if (!is_array($menu_ids))
			{
				$menu_ids = array($menu_ids);
			}

			foreach ($menu_ids as $menu_id)
			{
				// Cast to float to support bigint data type
				$this->db->delete(
					$this->tables['groups_menus'],
					array($this->join['menus'] => (float)$menu_id, $this->join['groups'] => (float)$group_id)
				);
				if (isset($this->_cache_group_in_menu[$group_id]) && isset($this->_cache_group_in_menu[$group_id][$menu_id]))
				{
					unset($this->_cache_group_in_menu[$group_id][$menu_id]);
				}
			}

			$return = TRUE;
		}
		// otherwise remove user from all groups
		else
		{
			// Cast to float to support bigint data type
			if ($return = $this->db->delete($this->tables['groups_menus'], array($this->join['groups'] => (float)$group_id)))
			{
				$this->_cache_group_in_menu[$group_id] = array();
			}
		}
		return $return;
	}

	public function groups()
	{
		$this->trigger_events('groups');

		// run each where that was passed
		if (isset($this->_ion_where) && !empty($this->_ion_where))
		{
			foreach ($this->_ion_where as $where)
			{
				$this->db->where($where);
			}
			$this->_ion_where = array();
		}

		if (isset($this->_ion_limit) && isset($this->_ion_offset))
		{
			$this->db->limit($this->_ion_limit, $this->_ion_offset);

			$this->_ion_limit  = NULL;
			$this->_ion_offset = NULL;
		}
		else if (isset($this->_ion_limit))
		{
			$this->db->limit($this->_ion_limit);

			$this->_ion_limit  = NULL;
		}

		// set the order
		if (isset($this->_ion_order_by) && isset($this->_ion_order))
		{
			$this->db->order_by($this->_ion_order_by, $this->_ion_order);
		}

		$this->db->where("ID !=", "1");
		$this->db->order_by("NAME");


		$this->response = $this->db->get($this->tables['groups']);

		return $this;
	}

	public function menus()
	{
		$this->trigger_events('menus');

		// run each where that was passed
		if (isset($this->_ion_where) && !empty($this->_ion_where))
		{
			foreach ($this->_ion_where as $where)
			{
				$this->db->where($where);
			}
			$this->_ion_where = array();
		}

		if (isset($this->_ion_limit) && isset($this->_ion_offset))
		{
			$this->db->limit($this->_ion_limit, $this->_ion_offset);

			$this->_ion_limit  = NULL;
			$this->_ion_offset = NULL;
		}
		else if (isset($this->_ion_limit))
		{
			$this->db->limit($this->_ion_limit);

			$this->_ion_limit  = NULL;
		}

		// set the order
		if (isset($this->_ion_order_by) && isset($this->_ion_order))
		{
			$this->db->order_by($this->_ion_order_by, $this->_ion_order);
		}

		$this->response = $this->db->get($this->tables['menus']);

		return $this;
	}

	public function group($id = NULL)
	{
		$this->trigger_events('group');

		if (isset($id))
		{
			$this->where($this->tables['groups'].'.ID', $id);
		}

		$this->limit(1);
		$this->order_by('ID', 'desc');

		return $this->groups();
	}

	public function menu($id = NULL)
	{
		$this->trigger_events('menu');

		if (isset($id))
		{
			$this->where($this->tables['menus'].'.ID', $id);
		}

		$this->limit(1);
		$this->order_by('ID', 'desc');

		return $this->menus();
	}

	public function update($id, array $data)
	{
		$this->trigger_events('pre_update_user');

		$user = $this->user($id)->row();

		$this->db->trans_begin();

		if (array_key_exists($this->identity_column, $data) && $this->identity_check($data[$this->identity_column]) && $user->{$this->identity_column} !== $data[$this->identity_column])
		{

			$this->db->trans_rollback();
			$this->set_error('account_creation_duplicate_identity');

			$this->trigger_events(array('post_update_user', 'post_update_user_unsuccessful'));
			$this->set_error('update_unsuccessful');

			return FALSE;
		}

		// Filter the data passed
		$data = $this->_filter_data($this->tables['users'], $data);

		if (array_key_exists($this->identity_column, $data) || array_key_exists('PASSWORD', $data) || array_key_exists('EMAIL', $data))
		{
			if (array_key_exists('PASSWORD', $data))
			{
				if( ! empty($data['PASSWORD']))
				{
					$data['PASSWORD'] = $this->hash_password($data['PASSWORD'], $user->SALT);
				}
				else
				{
					// unset password so it doesn't effect database entry if no password passed
					unset($data['PASSWORD']);
				}
			}
		}

		$this->trigger_events('extra_where');
		$this->db->update($this->tables['users'], $data, array('ID' => $user->ID));

		if ($this->db->trans_status() === FALSE)
		{
			$this->db->trans_rollback();

			$this->trigger_events(array('post_update_user', 'post_update_user_unsuccessful'));
			$this->set_error('update_unsuccessful');
			return FALSE;
		}

		$this->db->trans_commit();

		$this->trigger_events(array('post_update_user', 'post_update_user_successful'));
		$this->set_message('update_successful');

		$last_query = $this->db->last_query();
		$this->log_history("update", $last_query);
		$this->last_update($this->tables['users'], $id);
		
		return TRUE;
	}

	public function delete_user($id, $keterangan="")
	{
		$this->trigger_events('pre_delete_user');

		$this->db->trans_begin();

		// remove user from groups
		$this->remove_from_group(NULL, $id);

		// delete user from users table should be placed after remove from group
		$this->db->delete($this->tables['users'], array('ID' => $id));

		$last_query = $this->db->last_query();
		$this->log_history("delete", $last_query, $keterangan);

		if ($this->db->trans_status() === FALSE)
		{
			$this->db->trans_rollback();
			$this->trigger_events(array('post_delete_user', 'post_delete_user_unsuccessful'));
			$this->set_error('delete_unsuccessful');
			return FALSE;
		}

		$this->db->trans_commit();

		$this->trigger_events(array('post_delete_user', 'post_delete_user_successful'));
		$this->set_message('delete_successful');
		return TRUE;
	}

	public function update_last_login($id)
	{
		$this->trigger_events('update_last_login');

		$this->load->helper('date');

		$this->trigger_events('extra_where');

		$this->db->update($this->tables['users'], array('LAST_LOGIN' => time()), array('ID' => $id));

		return $this->db->affected_rows() == 1;
	}

	public function set_lang($lang = 'en')
	{
		$this->trigger_events('set_lang');

		// if the user_expire is set to zero we'll set the expiration two years from now.
		if($this->config->item('user_expire', 'ion_auth') === 0)
		{
			$expire = (60*60*24*365*2);
		}
		// otherwise use what is set
		else
		{
			$expire = $this->config->item('user_expire', 'ion_auth');
		}

		set_cookie(array(
			'name'   => 'lang_code',
			'value'  => $lang,
			'expire' => $expire
		));

		return TRUE;
	}

	public function set_session($user)
	{
		$this->trigger_events('pre_set_session');

		$groupIds = $this->get_users_groups($user->ID)->result();

		$group_id = array();
		$menu_id  = array();
		$menu_url = array();

		foreach ($groupIds as $key => $groupId) {
			$group_id[] = $groupId->ID;
		}

		$menus = $this->get_groups_menu_id($group_id);

		foreach ($menus as $menu)
		{
			$menuArr = array();
			foreach($menu as $val) {
				$menuArr[] = $val;
			}
			$menu_id[]  = $menuArr[0];
			$menu_url[] = $menuArr[2];
		}

	
		$session_data = array(
			'identity'             => $user->{$this->identity_column},
			$this->identity_column => $user->{$this->identity_column},
			'email'                => $user->EMAIL,
			'user_id'              => $user->ID, //everyone likes to overwrite id so we'll use user_id
			'display_name'         => $user->DISPLAY_NAME,
			'old_last_login'       => $user->LAST_LOGIN,
			'group_id'             => $group_id,
		//	'kd_cabang'            => $user->KD_CABANG,
			'menu_id'              => $menu_id,
			'menu_url'             => $menu_url,
			'last_check'           => time(),
		);


		$this->session->set_userdata($session_data);

		$this->trigger_events('post_set_session');

		return TRUE;
	}

	public function remember_user($id)
	{
		$this->trigger_events('pre_remember_user');

		if (!$id)
		{
			return FALSE;
		}

		$user = $this->user($id)->row();

		$salt = $this->salt();

		$this->db->update($this->tables['users'], array('REMEMBER_CODE' => $salt), array('ID' => $id));

		if ($this->db->affected_rows() > -1)
		{
			// if the user_expire is set to zero we'll set the expiration two years from now.
			if($this->config->item('user_expire', 'ion_auth') === 0)
			{
				$expire = (60*60*24*365*2);
			}
			// otherwise use what is set
			else
			{
				$expire = $this->config->item('user_expire', 'ion_auth');
			}

			set_cookie(array(
			    'name'   => $this->config->item('identity_cookie_name', 'ion_auth'),
			    'value'  => $user->{$this->identity_column},
			    'expire' => $expire
			));

			set_cookie(array(
			    'name'   => $this->config->item('remember_cookie_name', 'ion_auth'),
			    'value'  => $salt,
			    'expire' => $expire
			));

			$this->trigger_events(array('post_remember_user', 'remember_user_successful'));
			return TRUE;
		}

		$this->trigger_events(array('post_remember_user', 'remember_user_unsuccessful'));
		return FALSE;
	}

	public function login_remembered_user()
	{
		$this->trigger_events('pre_login_remembered_user');

		// check for valid data
		if (!get_cookie($this->config->item('identity_cookie_name', 'ion_auth'))
			|| !get_cookie($this->config->item('remember_cookie_name', 'ion_auth'))
			|| !$this->identity_check(get_cookie($this->config->item('identity_cookie_name', 'ion_auth'))))
		{
			$this->trigger_events(array('post_login_remembered_user', 'post_login_remembered_user_unsuccessful'));
			return FALSE;
		}

		// get the user
		$this->trigger_events('extra_where');
		$query = $this->db->select($this->identity_column . ', ID, EMAIL, LAST_LOGIN')
						  ->where($this->identity_column, urldecode(get_cookie($this->config->item('identity_cookie_name', 'ion_auth'))))
						  ->where('REMEMBER_CODE', get_cookie($this->config->item('remember_cookie_name', 'ion_auth')))
						  ->where('ACTIVE', 1)
						  ->limit(1)
						  ->order_by('ID', 'desc')
						  ->get($this->tables['users']);

		// if the user was found, sign them in
		if ($query->num_rows() == 1)
		{
			$user = $query->row();

			$this->update_last_login($user->ID);

			$this->set_session($user);

			// extend the users cookies if the option is enabled
			if ($this->config->item('user_extend_on_login', 'ion_auth'))
			{
				$this->remember_user($user->ID);
			}

			$this->trigger_events(array('post_login_remembered_user', 'post_login_remembered_user_successful'));
			return TRUE;
		}

		$this->trigger_events(array('post_login_remembered_user', 'post_login_remembered_user_unsuccessful'));
		return FALSE;
	}

	public function create_group($group_name = FALSE, $group_description = '', $additional_data = array())
	{
		// bail if the group name was not passed
		if(!$group_name)
		{
			$this->set_error('group_name_required');
			return FALSE;
		}

		// bail if the group name already exists
		$existing_group = $this->db->get_where($this->tables['groups'], array('NAME' => $group_name))->num_rows();
		if($existing_group !== 0)
		{
			$this->set_error('group_already_exists');
			return FALSE;
		}

		$data = array('NAME'=>$group_name,'DESCRIPTION'=>$group_description);

		// filter out any data passed that doesnt have a matching column in the groups table
		// and merge the set group data and the additional data
		if (!empty($additional_data)) $data = array_merge($this->_filter_data($this->tables['groups'], $additional_data), $data);

		$this->trigger_events('extra_group_set');

		// insert the new group
		$this->db->insert($this->tables['groups'], $data);
		// $group_id = $this->db->insert_id($this->tables['groups'] . '_id_seq');

		$fix_id   = $this->db->query("SELECT ".$this->tables['groups']."_SEQ.NEXTVAL AS NEXTID FROM ".$this->tables['groups']);
		$group_id = $fix_id->row()->NEXTID;
	
		// report success
		$this->set_message('group_creation_successful');
		// return the brand new group id
		return $group_id;
	}

	public function update_group($group_id = FALSE, $group_name = FALSE, $additional_data = array())
	{
		if (empty($group_id))
		{
			return FALSE;
		}

		$data = array();

		if (!empty($group_name))
		{
			// we are changing the name, so do some checks

			// bail if the group name already exists
			$existing_group = $this->db->get_where($this->tables['groups'], array('NAME' => $group_name))->row();
			if (isset($existing_group->ID) && $existing_group->ID != $group_id)
			{
				$this->set_error('group_already_exists');
				return FALSE;
			}

			$data['NAME'] = $group_name;
		}

		// restrict change of name of the admin group
		$group = $this->db->get_where($this->tables['groups'], array('ID' => $group_id))->row();
		if ($this->config->item('admin_group', 'ion_auth') === $group->NAME && $group_name !== $group->NAME)
		{
			$this->set_error('group_name_admin_not_alter');
			return FALSE;
		}

		// TODO Third parameter was string type $description; this following code is to maintain backward compatibility
		if (is_string($additional_data))
		{
			$additional_data = array('DESCRIPTION' => $additional_data);
		}

		// filter out any data passed that doesnt have a matching column in the groups table
		// and merge the set group data and the additional data
		if (!empty($additional_data))
		{
			$data = array_merge($this->_filter_data($this->tables['groups'], $additional_data), $data);
		}

		$this->db->update($this->tables['groups'], $data, array('ID' => $group_id));

		$this->set_message('group_update_successful');

		return TRUE;
	}

	public function delete_group($group_id = FALSE)
	{
		// bail if mandatory param not set
		if(!$group_id || empty($group_id))
		{
			return FALSE;
		}

		$this->remove_from_menu(NULL, $group_id);
		
		$group = $this->group($group_id)->row();
		if($group->NAME == $this->config->item('admin_group', 'ion_auth'))
		{
			$this->trigger_events(array('post_delete_group', 'post_delete_group_notallowed'));
			$this->set_error('group_delete_notallowed');
			return FALSE;
		}

		$this->trigger_events('pre_delete_group');

		$this->db->trans_begin();

		// remove all users from this group
		$this->db->delete($this->tables['users_groups'], array($this->join['groups'] => $group_id));
		// remove the group itself
		$this->db->delete($this->tables['groups'], array('ID' => $group_id));

		if ($this->db->trans_status() === FALSE)
		{
			$this->db->trans_rollback();
			$this->trigger_events(array('post_delete_group', 'post_delete_group_unsuccessful'));
			return FALSE;
		}

		$this->db->trans_commit();

		$this->trigger_events(array('post_delete_group', 'post_delete_group_successful'));
		return TRUE;
	}

	public function set_hook($event, $name, $class, $method, $arguments)
	{
		$this->_ion_hooks->{$event}[$name] = new stdClass;
		$this->_ion_hooks->{$event}[$name]->class     = $class;
		$this->_ion_hooks->{$event}[$name]->method    = $method;
		$this->_ion_hooks->{$event}[$name]->arguments = $arguments;
	}

	public function remove_hook($event, $name)
	{
		if (isset($this->_ion_hooks->{$event}[$name]))
		{
			unset($this->_ion_hooks->{$event}[$name]);
		}
	}

	public function remove_hooks($event)
	{
		if (isset($this->_ion_hooks->$event))
		{
			unset($this->_ion_hooks->$event);
		}
	}

	protected function _call_hook($event, $name)
	{
		if (isset($this->_ion_hooks->{$event}[$name]) && method_exists($this->_ion_hooks->{$event}[$name]->class, $this->_ion_hooks->{$event}[$name]->method))
		{
			$hook = $this->_ion_hooks->{$event}[$name];

			return call_user_func_array(array($hook->class, $hook->method), $hook->arguments);
		}

		return FALSE;
	}

	public function trigger_events($events)
	{
		if (is_array($events) && !empty($events))
		{
			foreach ($events as $event)
			{
				$this->trigger_events($event);
			}
		}
		else
		{
			if (isset($this->_ion_hooks->$events) && !empty($this->_ion_hooks->$events))
			{
				foreach ($this->_ion_hooks->$events as $name => $hook)
				{
					$this->_call_hook($events, $name);
				}
			}
		}
	}

	public function set_message_delimiters($start_delimiter, $end_delimiter)
	{
		$this->message_start_delimiter = $start_delimiter;
		$this->message_end_delimiter   = $end_delimiter;

		return TRUE;
	}

	public function set_error_delimiters($start_delimiter, $end_delimiter)
	{
		$this->error_start_delimiter = $start_delimiter;
		$this->error_end_delimiter   = $end_delimiter;

		return TRUE;
	}

	public function set_message($message)
	{
		$this->messages[] = $message;

		return $message;
	}

	public function messages()
	{
		$_output = '';
		foreach ($this->messages as $message)
		{
			$messageLang = $this->lang->line($message) ? $this->lang->line($message) : '##' . $message . '##';
			$_output .= $this->message_start_delimiter . $messageLang . $this->message_end_delimiter;
		}

		return $_output;
	}

	public function messages_array($langify = TRUE)
	{
		if ($langify)
		{
			$_output = array();
			foreach ($this->messages as $message)
			{
				$messageLang = $this->lang->line($message) ? $this->lang->line($message) : '##' . $message . '##';
				$_output[] = $this->message_start_delimiter . $messageLang . $this->message_end_delimiter;
			}
			return $_output;
		}
		else
		{
			return $this->messages;
		}
	}

	public function clear_messages()
	{
		$this->messages = array();

		return TRUE;
	}

	public function set_error($error)
	{
		$this->errors[] = $error;

		return $error;
	}

	public function errors()
	{
		$_output = '';
		foreach ($this->errors as $error)
		{
			$errorLang = $this->lang->line($error) ? $this->lang->line($error) : '##' . $error . '##';
			$_output .= $this->error_start_delimiter . $errorLang . $this->error_end_delimiter;
		}

		return $_output;
	}

	public function errors_array($langify = TRUE)
	{
		if ($langify)
		{
			$_output = array();
			foreach ($this->errors as $error)
			{
				$errorLang = $this->lang->line($error) ? $this->lang->line($error) : '##' . $error . '##';
				$_output[] = $this->error_start_delimiter . $errorLang . $this->error_end_delimiter;
			}
			return $_output;
		}
		else
		{
			return $this->errors;
		}
	}

	public function clear_errors()
	{
		$this->errors = array();

		return TRUE;
	}

	protected function _filter_data($table, $data)
	{
		$filtered_data = array();
		$columns = $this->db->list_fields($table);

		if (is_array($data))
		{
			foreach ($columns as $column)
			{
				if (array_key_exists($column, $data))
					$filtered_data[$column] = $data[$column];
			}
		}

		return $filtered_data;
	}

	protected function _prepare_ip($ip_address) {
		return $ip_address;
	}
}
