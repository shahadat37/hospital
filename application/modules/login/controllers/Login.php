<?php

class login extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        
		$this->load->helper('form');
		$this->load->helper('url');
        $this->load->helper('security');
		
		$this->load->model('login_model');
		$this->load->model('menu_model');
		$this->load->model('module/module_model');
		$this->load->model('settings/settings_model');
		
		$this->load->library('session');

		$this->lang->load('main');
    }
  	function login_page(){
		$level = $this->session->userdata('category');
		if($level == 'Patient'){
			return '/frontend/my_account';
		}
		$parent_name="";
		$result_top_menu = $this->menu_model->find_menu($parent_name);
		foreach ($result_top_menu as $top_menu){
			$id = $top_menu['id'];
			$parent_name = $top_menu['menu_name'];
			if($this->menu_model->has_access($top_menu['menu_name'],$level)){ 
				if($this->menu_model->is_module_active($top_menu['required_module'])){
					$result_sub_menu = $this->menu_model->find_menu($parent_name);
					$rowcount= count($result_sub_menu);	
					if($rowcount != 0){
						foreach ($result_sub_menu as $sub_menu){	
							if($this->menu_model->has_access($sub_menu['menu_name'],$level)){ 
								if($this->menu_model->is_module_active($sub_menu['required_module'])){
									return $sub_menu['menu_url'];
								}
							}
						}
					}else{
						return $top_menu['menu_url'];
					}
				}
			}
		}
		return '/appointment/index';
	}
    function index() {
		//If Not Logged In, Go to Login Form
        if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {
			$data['clinic']=$this->settings_model->get_clinic_settings();
			$data['clinics']=$this->settings_model->get_all_clinics();
			$frontend_active=$this->module_model->is_active("frontend");
			$data['frontend_active']=$frontend_active;
			$this->load->view('login/login_signup',$data);
		} else {
			//Go to Appointment Page if logged in
			$login_page = $this->login_page();
            redirect($login_page, 'refresh');
        }
    }

    function valid_signin() {
		//Check if loggin details entered
        $this->form_validation->set_rules('username', 'Username', 'trim|required|min_length[5]|max_length[25]|xss_clean');
        $this->form_validation->set_rules('password', 'Password', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $this->index();
        } else {
			$logged_in = FALSE;
			$is_active = TRUE;
            if($this->input->post('username')){
				//Check Login details
				$username = $this->input->post('username');
				$clinic_id = $this->input->post('clinic_id');
				$password = base64_encode($this->input->post('password'));
				$result = $this->login_model->login($username, $password);
				if(!empty($result)){
					$is_active = $this->login_model->is_active($username);
					if($is_active){
						$userdata = array();
						$userdata["name"] = $result->name;
						$userdata["user_name"] = $result->username;
						$userdata["category"] = $result->level;
						$userdata["id"] = $result->userid;
						$userdata["clinic_id"] = $clinic_id;
						$userdata["logged_in"] = TRUE;
						
						$this->session->set_userdata($userdata);
						$logged_in = TRUE;
					}
				}
			}
			//If Username and Password matches
			if ($logged_in) {
				$login_page = $this->login_page();
				redirect($login_page, 'refresh');
			} else {
				if($is_active){
					$data['username'] = $this->input->post('username');
					$data['level'] = $this->input->post('level');
					$data['error'] = 'Invalid Username and/or Password';
					$data['clinic']=$this->settings_model->get_clinic_settings();
					$frontend_active=$this->module_model->is_active("frontend");
					$data['frontend_active']=$frontend_active;
					$this->load->view('login/login_signup',$data);
				}else{
					$data['username'] = $this->input->post('username');
					$data['level'] = $this->input->post('level');
					$data['error'] = 'User is Inactive. Please contact Administrator.';
					$data['clinic']=$this->settings_model->get_clinic_settings();
					$frontend_active=$this->module_model->is_active("frontend");
					$data['frontend_active']=$frontend_active;
					$this->load->view('login/login_signup',$data);
				}
			}
        }
    }

    public function logout() {
		//Destroy Session and go to login form
        //if ($this->session->userdata('user_name')) {
			// remove all session variables
			//session_unset(); 
			// destroy the session 
			//session_destroy(); 
		//}
		$this->session->sess_destroy();
        $this->index();
    }
	/*public function is_session_started(){
		if ( php_sapi_name() !== 'cli' ) {
			if ( version_compare(phpversion(), '5.4.0', '>=') ) {
				return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
			} else {
				return session_id() === '' ? FALSE : TRUE;
			}
		}
		return FALSE;
	}*/
	public function cleardata() {
		//if ( $this->is_session_started() === TRUE ){
			// remove all session variables
			//session_unset(); 
			// destroy the session 
			//session_destroy(); 
		//}
		$frontend_active=$this->module_model->is_active("frontend");
		$data['frontend_active']=$frontend_active;
		$center_active=$this->module_model->is_active("center");
		$data['center_active']=$center_active;
		$this->session->sess_destroy();
		$data['clinic']=$this->settings_model->get_clinic_settings();
		$data['message']='Use Username / Password : admin/admin to login ';
		$this->load->view('login/login_signup',$data);
    }
	
}

?>
