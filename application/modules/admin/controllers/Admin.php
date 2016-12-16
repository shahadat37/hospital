<?php

class Admin extends CI_Controller {

    function __construct() {
        parent::__construct();
	
        $this->load->library('form_validation');

		$this->load->helper('security');
		$this->load->helper('form');
		$this->load->helper('url');
		
		$this->load->model('menu_model');
        $this->load->model('admin_model');

		$this->load->library('session');

		$this->lang->load('main');
    }
	
	/**Browse Users*/
    function users() {		
		//Check if user has logged in 
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {
            redirect('login/index');
        } else {
            $this->form_validation->set_rules('level', 'Level', 'trim|required');
            $this->form_validation->set_rules('name', 'Name', 'trim|required|min_length[5]|max_length[200]|xss_clean');
            $this->form_validation->set_rules('username', 'Username', 'trim|required|min_length[5]|max_length[12]|xss_clean|is_unique[users.username]');
            $this->form_validation->set_rules('password', 'Password', 'trim|required|matches[passconf]');
            $this->form_validation->set_rules('passconf', 'Password Confirmation', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                $data['user'] = $this->admin_model->get_users();
				$data['message']="";
				$data['categories'] = $this->admin_model->find_category();
				$this->load->view('templates/header');
				$this->load->view('templates/menu');
				$this->load->view('users_list', $data);
				$this->load->view('templates/footer');
            } else {
                $this->admin_model->add_user();
				$data['user'] = $this->admin_model->get_users();
				$data['message']="User added Successfully";
				$data['categories'] = $this->admin_model->find_category();
				$this->load->view('templates/header');
				$this->load->view('templates/menu');
				$this->load->view('users_list', $data);
				$this->load->view('templates/footer');
            }
            
        }
    }
	/**Delete User*/
    function delete($id) {
		//Check if user has logged in 
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {
            redirect('login/index');
        } else {
            $this->admin_model->delete_user($id);			
            redirect('login/index');
        }
    }
	/**Edit User*/
    function edit_user($uid) {
        //if ( $this->is_session_started() === FALSE ){
		//	session_start();
		//}
		//Check if user has logged in 
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {
            redirect('login/index');
        } else {
            $this->form_validation->set_rules('level', 'Level', 'trim|required');
			$this->form_validation->set_rules('newpassword', 'New Password', 'trim|matches[passconf]');
            $this->form_validation->set_rules('passconf', 'Password Confirmation', 'trim');
            if ($this->form_validation->run() == FALSE) {
                $data['user'] = $this->admin_model->get_user_detail($uid);
				$data['categories'] = $this->admin_model->find_category();
				$this->load->view('templates/header');
				$this->load->view('templates/menu');
				$this->load->view('user_edit', $data);
				$this->load->view('templates/footer');
            } else {
                $this->admin_model->edit_user_data($uid);
                $data['user'] = $this->admin_model->get_users();	
				$data['categories'] = $this->admin_model->find_category();				
				$data['message']="User Updated Successfully";
				$this->load->view('templates/header');
				$this->load->view('templates/menu');
				$this->load->view('users_list', $data);
				$this->load->view('templates/footer');
            }
        }
    }
	/**Change Profile*/
    public function change_profile() {		
        //if ( $this->is_session_started() === FALSE ){
		//	session_start();
		//}
		//Check if user has logged in 
		//if (!isset($_SESSION["user_name"]) || $_SESSION["user_name"] == '') {
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {
            redirect('login/index');
        } else {
            $user_id = $this->session->userdata('id');

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $data['user'] = $this->admin_model->get_user_detail($user_id);
                $this->load->view('templates/header');
                $this->load->view('templates/menu');
                $this->load->view('edit_profile', $data);
                $this->load->view('templates/footer');
            } else {
                if ($this->input->post('newpassword') == '') {
                    $this->form_validation->set_rules('name', 'Name', 'trim|required|min_length[5]|max_length[200]|xss_clean');
                    if ($this->form_validation->run() == FALSE) {
                        $data['user'] = $this->admin_model->get_user_detail($user_id);
                        $this->load->view('templates/header');
                        $this->load->view('templates/menu');
                        $this->load->view('edit_profile', $data);
                        $this->load->view('templates/footer');
                    } else {
                        $this->admin_model->change_profile($user_id);
                        redirect('appointment/index');
                    }
                } else {
                    $this->form_validation->set_rules('name', 'Name', 'trim|required|min_length[5]|max_length[200]|xss_clean');
                    $this->form_validation->set_rules('oldpassword', 'Old Password', 'trim|required|xss_clean|callback_password_check[' . $user_id . ']');
                    $this->form_validation->set_rules('newpassword', 'New Password', 'trim|required|matches[passconf]');
                    $this->form_validation->set_rules('passconf', 'Password Confirmation', 'trim|required');
                    if ($this->form_validation->run() == FALSE) {
                        $data['user'] = $this->admin_model->get_user_detail($user_id);
                        $this->load->view('templates/header');
                        $this->load->view('templates/menu');
                        $this->load->view('edit_profile', $data);
                        $this->load->view('templates/footer');
                    } else {
                        $this->admin_model->change_password($user_id);
                        redirect('appointment/index');
                    }
                }
            }
        }
    }
	
    function password_check($str, $user_id) {
        $data['user'] = $this->admin_model->get_user_detail($user_id);
        $password = base64_decode($data['user']['password']);
        if ($str == $password) {
            return TRUE;
        } else {
            $this->form_validation->set_message('password_check', 'Old Password Not Matched');
            return FALSE;
        }
    }
	function about(){
		$data['version'] = $this->menu_model->find_version(); 
		$this->load->view('templates/header');
		$this->load->view('templates/menu');
		$this->load->view('about',$data);
		$this->load->view('templates/footer');
	}
	
}

?>
