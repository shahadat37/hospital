<?php

class Settings_model extends CI_Model {

    public function __construct() {
        $this->load->database();
    }
	/**Clinic*/
    public function get_clinic_settings() {
		$query=$this->db->get_where('clinic',array('clinic_id'=>1));
        return $query->row_array();
    }
	public function get_all_clinics(){
		$result = $this->db->get('clinic');
        return $result->result_array();
	}
	public function get_clinic($clinic_id = 1){
		$query = $this->db->get_where('clinic',array('clinic_id' => $clinic_id));
        $row =  $query->row_array();
		return $row;
	}
	public function get_clinic_name(){
		$query = $this->db->get('clinic');
        $row =  $query->row_array();
		return $row['clinic_name'];
	}
    public function save_clinic_settings() {
		
        $data['clinic_name'] = $this->input->post('clinic_name');
        $data['tag_line'] = $this->input->post('tag_line');
        $data['clinic_address'] = $this->input->post('clinic_address');
        $data['landline'] = $this->input->post('landline');
        $data['mobile'] = $this->input->post('mobile');
        $data['email'] = $this->input->post('email');
        $data['start_time'] = $this->input->post('start_time');
        $data['end_time'] = $this->input->post('end_time');
        $data['time_interval'] = $this->input->post('time_interval');
        $data['next_followup_days'] = $this->input->post('next_followup_days');        
		$data['facebook'] = $this->input->post('facebook');
		$data['twitter'] = $this->input->post('twitter');
		$data['google_plus'] = $this->input->post('google_plus');
		$data['max_patient'] = $this->input->post('max_patient');
        
		if($this->input->post('clinic_id')){
			$this->db->update('clinic', $data, array('clinic_id' => $this->input->post('clinic_id')));
		}else{
			$this->db->insert('clinic', $data);
		}
    }
	public function number_of_clinic(){
		$query = $this->db->get('clinic');
		$num_of_clinic = $query->num_rows();
		return $num_of_clinic;
	}
	public function delete_clinic($clinic_id){
		$this->db->delete('clinic', array('clinic_id' => $clinic_id));
	}
	public function update_clinic_logo($file_name) {
		if($file_name != NULL && $file_name != "" ){
			$data['clinic_logo'] = 'images/'. $file_name;
		}else{
			$data['clinic_logo'] = '';
		}
		$this->db->update('clinic', $data,array('clinic_id' => 1));
	}
	public function remove_clinic_logo() {
		$data['clinic_logo'] = '';
		$this->db->update('clinic', $data,array('clinic_id' => 1));
	}
    public function get_clinic_start_time() {
        $query = $this->db->get('clinic');
        $row = $query->row_array();
        if (!$row) {
            return '09:00';
        }
        return $row['start_time'];
    }
    public function get_clinic_end_time() {
        $query = $this->db->get('clinic');
        $row = $query->row_array();
        if (!$row) {
            return '18:00';
        }
        return $row['end_time'];
    }
    public function get_time_interval() {
        $query = $this->db->get('clinic');
        $row = $query->row_array();
        if (!$row) {
            return '0.50';
        }
        return $row['time_interval'];
    }
	/**Invoice*/
    public function get_invoice_settings() {
        $query = $this->db->get('invoice');
        return $query->row_array();
    }
    public function get_currency_postfix(){
        $this->db->select('currency_postfix');
        $query = $this->db->get('invoice');
        return $query->row_array();        
    }
    public function save_invoice_settings() {
        $data['static_prefix'] = $this->input->post('static_prefix');
        $data['left_pad'] = $this->input->post('left_pad');
        $data['currency_symbol'] = $this->input->post('currency_symbol');
        $data['currency_postfix'] = $this->input->post('currency_postfix');

        $this->db->update('invoice', $data, array('invoice_id' => 1));
    }
    public function get_invoice_next_id() {
        $query = $this->db->get('invoice');
        $row = $query->row_array();
        return $row['next_id'];
    }
    public function increment_next_id() {
        $next_id = $this->get_invoice_next_id();
        $next_id++;
        $data->next_id = $next_id;

        $this->db->update('invoice', $data, array('invoice_id' => 1));
    }
	public function get_treatments(){
        $result = $this->db->get('treatments');
        return $result->result_array();
    }
    public function add_treatment() {
        $data['treatment'] = $this->input->post('treatment');
        $data['price'] = $this->input->post('treatment_price');
        $this->db->insert('treatments',$data);
    }
    public function get_edit_treatment($id) {    
        $this->db->where("id", $id);
        $query = $this->db->get("treatments");
        return $query->row_array();    
    }
    public function edit_treatment($id){
        $data['treatment'] = $this->input->post('treatment');
        $data['price'] = $this->input->post('treatment_price');
        $this->db->where('id', $id);
        $this->db->update('treatments', $data);
    }
    public function delete_treatment($id) {
        $this->db->delete('treatments', array('id' => $id));
    }
    public function get_visit_treatment($visit_id){
        $bill_id = $this->patient_model->get_bill_id($visit_id);
        
        $query = $this->db->get_where('bill_detail', array('bill_id' => $bill_id));
        return $query->result_array();
    }
	public function get_time_zone(){
	   $this->db->select('ck_value');
	   $query=$this->db->get_where('data',array('ck_key'=>'default_timezone'));
	   $row=$query->row();
	   return $row->ck_value;
	}
	public function save_timezone($key, $value) {
		$this->db->where('ck_key', $key);
		$db_array = array('ck_value' => $value);
		$this->db->update('data', $db_array);	
	}
	public function get_data_value($key){
		$this->db->select('ck_value');
		$query=$this->db->get_where('data',array('ck_key'=>$key));
		$row=$query->row();
		if (!$row) {
			return "";
		}else{
			return $row->ck_value;	
		}
	}
	public function set_data_value($key, $value) {
		$db_array = array('ck_key' => $key,'ck_value' => $value);
		
		$query=$this->db->get_where('data',array('ck_key'=>$key));
		//echo $this->db->last_query();
		$row=$query->row();
		//print_r($row);
		if (!$row) {
			$this->db->insert('data',$db_array);
			//echo $this->db->last_query();
		}else{
			$this->db->update('data',$db_array,array('ck_key'=>$key));	
			//echo $this->db->last_query();
		}
	}
	public function get_time_formate(){
	   $this->db->select('ck_value');
	   $query=$this->db->get_where('data',array('ck_key'=>'default_timeformate'));
	   $row=$query->row();
	   return $row->ck_value;
	}
	public function save_timeformate($key, $value) {
		$this->db->where('ck_key', $key);
		$db_array = array('ck_value' => $value);
		$this->db->update('data', $db_array);	
	}
	public function get_date_formate(){
	   $this->db->select('ck_value');
	   $query=$this->db->get_where('data',array('ck_key'=>'default_dateformate'));
	   $row=$query->row();
	   return $row->ck_value;
	}
	public function get_morris_date_format(){
		$date_format = $this->get_date_formate();
		if($date_format == "d-m-Y"){
			return 'DD-MM-YYYY';
		}elseif($date_format == "Y-m-d"){
			return 'YYYY-MM-DD';
		}
	}
	public function get_morris_time_format(){
		$time_format = $this->get_time_formate();
		if($time_format == "h:i A"){
			return 'h:mm a';
		}elseif($time_format == "H:i"){
			return 'H:mm';
		}elseif($time_format == "H:i:s"){
			return 'H:mm:ss';
		}
	}
	public function save_dateformate($key, $value) {
		$this->db->where('ck_key', $key);
		$db_array = array('ck_value' => $value);
		$this->db->update('data', $db_array);	
	}
	public function save_working_days(){
		$working_days_array = $this->input->post('working_days');
		$working_days_string = implode(",", $working_days_array);
		$this->set_data_value('working_days', $working_days_string);
	}
	public function get_working_days(){
		$working_days_string = $this->get_data_value('working_days');
		$working_days_array = explode(",", $working_days_string);
		return $working_days_array;
	}
	public function save_exceptional_days(){
		//prepare data
		$data['working_date'] = date('Y-m-d',strtotime($this->input->post('working_date')));
		$data['working_status'] = $this->input->post('working_status');
		$data['working_reason'] = $this->input->post('working_reason');
		//check if any data exists for this date. 
		$query=$this->db->get_where('working_days',array('working_date' => $data['working_date']));
		$row=$query->row();
		if (!$row) {
			//if data doesnot exist then insert it
			$this->db->insert('working_days',$data);
		}else{
			//If data exists then update it
			$this->db->update('working_days',$data,array('working_date'=>$data['working_date']));	
		}
	}
	
	public function get_exceptional_days(){
		$query = $this->db->get('working_days');
        return $query->result_array();
	}
	public function get_exceptional_day($uid){
		$query = $this->db->get_where('working_days',array('uid' => $uid));
        return $query->row_array();
	}
	public function delete_exceptional_days($uid){
		$this->db->delete('working_days', array('uid' => $uid));
	}	
	public function update_exceptional_days(){
		//prepare data
		$data['working_date'] = date('Y-m-d',strtotime($this->input->post('working_date')));
		$data['working_status'] = $this->input->post('working_status');
		$data['working_reason'] = $this->input->post('working_reason');
		$uid = $this->input->post('uid');
		$this->db->update('working_days',$data,array('uid' => $uid));	
	}
}

?>
