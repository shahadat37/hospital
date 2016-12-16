<?php

class Admin_model extends CI_Model {

    function __construct() {
        parent::__construct();

        $this->load->database();
    }

	function insert_user($contact_id,$level){
		$query = $this->db->get_where('contacts', array('contact_id' => $contact_id));
        $contact = $query->row_array();
		
		$data['name'] = $contact['first_name'] . " " .$contact['middle_name']. " ".$contact['last_name'];
		if($this->input->post('email')){
			$data['username'] = $this->input->post('email');
		}else{
			$query = $this->db->get_where('contacts', array('contact_id' => $contact_id));
			$contact = $query->row_array();
			$email = $contact['email'];
			$data['username'] = $email;
		}
		$data['level'] = $level;
		$data['is_active'] = 1;
		$data['contact_id'] = $contact_id;
		if($this->input->post('password')){
			$data['password'] = base64_encode($this->input->post('password'));
		}else{
			$password = $this->random_password(8);
			$data['password'] = base64_encode($password);
		}
		$this->db->insert('users', $data);
		$user_id = $this->db->insert_id();
		
		
		return $user_id;
	}
	function random_password( $length = 8 ) {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
		$password = substr( str_shuffle( $chars ), 0, $length );
		return $password;
	}
    function add_user() {
        $data = array(
            'name' => $this->input->post('name'),
            'username' => $this->input->post('username'),
            'level' => $this->input->post('level'),
            'password' => base64_encode($this->input->post('password'))
        );
        $this->db->insert('users', $data);
    }
	function add_doctor($doctor_name,$user_name){
		$data = array(
            'name' => $doctor_name,
            'username' => $user_name,
            'level' => 'Doctor',
            'password' => base64_encode($user_name)
        );
		$this->db->insert('users', $data);
	}
    function get_users() {
        $query = $this->db->get("users");
        return $query->result_array();
    }
	function get_user($user_id) {
        $query = $this->db->get_where('users', array('userid' => $user_id));
        return $query->row_array();
    }
	function get_user_detail_by_contact_id($contact_id) {
        $query = $this->db->get_where('users', array('contact_id' => $contact_id));
		//echo $this->db->last_query()."<br/>";
        return $query->row_array();
    }
    function get_user_detail($user_id) {
        $query = $this->db->get_where('users', array('userid' => $user_id));
        return $query->row_array();
    }
	function find_doctor_by_name($doctor_name){
		$query = $this->db->get_where('users', array('name' => $doctor_name,'level' => 'Doctor'));
		//echo $this->db->last_query()."<br/>";
        $row = $query->row_array();
		//print_r($row);
		return $row['userid'];
	}
    function get_doctor($userid = NULL) {
        if ($userid == NULL) {
            $query = $this->db->get_where('users', array('level' => 'Doctor'));
            return $query->result_array();
        }else{
            //$this->db->select('userid,name');
            $query = $this->db->get_where('users', array('userid' => $userid));
            return $query->row_array();
        }
    }

    function delete_user($id) {
        $this->db->delete('users', array('userid' => $id));
    }

    function edit_user_data($id) {
        $level = $this->input->post('level');
		$data['level'] = $level;
		
		if($this->input->post('newpassword') !="" ){
			$password = base64_encode($this->input->post('newpassword'));
			$data['password'] = $password;
		}
        $data['is_active'] = $this->input->post('is_active');
		$data['username'] = $this->input->post('username');
		
        $this->db->where('userid', $id);
        $this->db->update('users', $data);
    }

    function change_profile($user_id){
        $data['name'] = $this->input->post('name');
        
        $this->db->where('userid', $user_id);
        $this->db->update('users', $data);
    }
    
    function change_password($user_id){
        $data['name'] = $this->input->post('name');
        $data['password'] = base64_encode($this->input->post('newpassword'));
        
        $this->db->where('userid', $user_id);
        $this->db->update('users', $data);
    }
	/*category Master ---------------------------------------------------------------------------------------*/
	public function find_category() {	
         $query = $this->db->get("user_categories");
        return $query->result_array();

    }
	public function get_category($id) {
            $query = $this->db->get_where('user_categories', array('id' => $id));
            return $query->row_array();
        }
		
	 public function update_category() {
		$id = $this->input->post('id');
		$data['id'] = $this->input->post('id');
		$data['category_name'] = $this->input->post('category_name');
		$this->db->update('user_categories', $data, array('id' =>  $id));		
	}
	function add_category() {       
        $data['category_name'] = $this->input->post('category_name');	
        $this->db->insert('user_categories', $data);	
		return $this->db->insert_id();		
    }
	function delete_category($id) {
        $this->db->delete('user_categories', array('id' => $id));
    }
	function get_active_verification_code($email,$current_time){
		$this->db->where('user_email', $email);
		$expire_time = date('Y-m-d H:i:s',strtotime($current_time.' -30 minutes'));
		$this->db->where('code_generated_at > ', $expire_time);
		$this->db->where('code_is_verified ', 0);
		$query = $this->db->get("user_verification");
		if ($query->num_rows() > 0){
			$user_verification = $query->row_array();
			return $user_verification['verification_code'];
		}else{
			return 0;
		}
        
	}
}

?>
