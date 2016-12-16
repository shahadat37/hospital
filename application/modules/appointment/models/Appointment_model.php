<?php

class Appointment_model extends CI_Model {

    public function __construct() {
        $this->load->database();
		
    }
	function get_dr_inavailability($appointment_id = NULL, $user_id = NULL) {
        $level = $this->session->userdata('category');
		
		if($appointment_id != NULL && $user_id!=NULL)
		{
				$this->db->where('end_date IS NOT NULL');
				$this->db->where('appointment_id', $appointment_id);
				$this->db->where('userid', $user_id);
				$query=$this->db->get('appointments');
				return $query->result_array();
		}
		else
		{	
			if($level == 'Doctor')
			{
				$userid = $this->session->userdata('id');
				$this->db->where('end_date IS NOT NULL');
				$this->db->where('status', 'NotAvailable');
				$this->db->where('userid', $userid);
				$this->db->order_by('appointment_id');
				$query=$this->db->get('appointments');
			}
			else
			{
				$this->db->where('end_date IS NOT NULL');
				$this->db->where('status', 'NotAvailable');
				$this->db->order_by('appointment_id');
				$query=$this->db->get('appointments');
			}
			return $query->result_array();
		}        
    }
	function get_doctor_unavailability($appointment_date,$start_time,$end_time,$doctor_id){
		$this->db->where('appointment_date', $appointment_date);
		$this->db->where('status', 'NotAvailable');
		$query = $this->db->get('appointments');
		//echo $this->db->last_query();
		$num = $query->num_rows();
		if($num > 1){
			return TRUE;
		}else{
			return FALSE;
		}
		
	}
	function insert_new_appointment($patient_id,$doctor_id,$appointment_date,$appointment_start_time,$appointment_end_time){
		$data['appointment_date'] = $appointment_date;
        $data['start_time'] = $appointment_start_time;
        $data['end_time'] = $appointment_end_time;
		$data['visit_id'] = 0;
		$data['status'] = 'Complete';
		$data['title']=$this->get_patient_name($patient_id);
		$data['patient_id'] = $patient_id;
		$data['userid'] = $doctor_id;
		$this->db->insert('appointments', $data);
		$appointment_id = $this->db->insert_id();
		return $appointment_id;
	}
	//Add New Appointment
    function add_appointment($status) {

		/* Set Local TimeZone as Default TimeZone */
		$timezone = $this->settings_model->get_time_zone();
        if (function_exists('date_default_timezone_set'))
            date_default_timezone_set($timezone);

        $appointment_date = date("Y-m-d", strtotime($this->input->post('appointment_date')));
        $start_time = date("H:i:s",strtotime($this->input->post('start_time'))); //Do Not Use Time Format
		$end_time = date("H:i:s",strtotime($this->input->post('end_time'))); //Do Not Use Time Format

		$appointment_reason = $this->input->post('appointment_reason');
		$data['appointment_reason'] = $appointment_reason;
		$data['appointment_date'] = $appointment_date;
        $data['start_time'] = $start_time;
        $data['end_time'] = $end_time;
		$data['visit_id'] = 0;
		
		$doctor_id = $this->input->post('doctor_id');
		$data['userid'] = $doctor_id;
        if ($this->input->post('patient_id') <> 0) {
			if($this->input->post('patient_name')){
				$data['title'] = $this->input->post('patient_name');
			}else{
				$patient_id = $this->input->post('patient_id');
				$this->db->where('patient_id', $patient_id);
				$query=$this->db->get('view_patient');
				$patient =  $query->row_array();
				$data['title'] = $patient['first_name'].' '.$patient['middle_name'].' '.$patient['last_name'];
			}
            
        }else{
            $data['title'] = $this->input->post('title');
        }
        $data['patient_id'] = $this->input->post('patient_id');
        $patient_id = $this->input->post('patient_id');

		
		//Adding Appintment, so reset the followup date
        if ($patient_id <> NULL) {
			$data3['followup_date'] = '00:00:00';
			$this->db->update('patient', $data3, array('patient_id' => $patient_id));
		}
		// Insert Appointment
		$data['status'] = $status;
		$data['clinic_id'] = $this->session->userdata('clinic_id');
		$this->db->insert('appointments', $data);
		$appointment_id = $this->db->insert_id();
					
		//Creating a Log of Appintment
		$data2['appointment_id'] = $appointment_id;
		$data2['appointment_reason'] = $appointment_reason;
		$data2['change_date_time'] = date('d/m/Y H:i:s'); //Do not use Time Format
		$data2['start_time'] = $this->input->post('start_time');
		$data2['old_status'] = " ";
		$data2['status'] = 'Appointment';
		$data2['from_time'] = date('H:i:s'); //Do not use Time Format
		$data2['to_time'] = " ";
		$data2['name'] = $this->session->userdata('name');
		
		$this->db->insert('appointment_log', $data2);
		return $appointment_id;
    }
	function update_appointment($title){
		$appointment_id = $this->input->post('appointment_id');
		$data['appointment_reason'] = $this->input->post('appointment_reason');
		$data['appointment_date'] = date("Y-m-d",strtotime($this->input->post('appointment_date')));
        $data['start_time'] = $this->input->post('start_time');
        $data['end_time'] = $this->input->post('end_time');
        $data['patient_id'] = $this->input->post('patient_id');
		$data['title'] = $title;
		$data['userid'] = $this->input->post('doctor_id');
		
		$this->db->where('appointment_id', $appointment_id);
		$this->db->update('appointments', $data);
	}
	function add_patient_appointment() {
        $data['appointment_date'] = date("Y-m-d",strtotime($this->input->post('appointment_date')));
        $data['start_time'] = $this->input->post('start_time');
        $data['end_time'] = $this->input->post('end_time');
        $data['title'] = $this->input->post('title');
        $data['patient_id'] = $this->input->post('patient_id');
		$data['clinic_id'] = $this->session->userdata('clinic_id');
           
        $this->db->insert('appointments', $data);
     }
	function get_future_appointments($date){
		$qry = " CONCAT( appointment_date,  ' ', start_time ) >= '$date' ";
		$this->db->where($qry);
		$query=$this->db->get('appointments');
		$appointments = $query->result_array();

		return $appointments;
	} 
    function get_appointments($appointment_date,$doctor_id = NULL) {
		$clinic_id = $this->session->userdata('clinic_id');

		$qry = "appointment_date ='$appointment_date' AND status !='NotAvailable'";
		if(isset($doctor_id)){
			$qry .= " AND  userid='$doctor_id'";
		}
		$this->db->where($qry);
		$query=$this->db->get('appointments');
		//echo $this->db->last_query();
		$appointments = $query->result_array();

		return $appointments;
    }
	function get_appointments_between_dates($start_date,$end_date,$doctor_id = NULL) {
		$qry = "appointment_date >= '$start_date'  AND appointment_date <= '$end_date' AND status !='NotAvailable'";
		if(isset($doctor_id)){
			$qry .= " AND userid='$doctor_id'";
		}
		$this->db->where($qry);
		$query=$this->db->get('appointments');
		$appointments = $query->result_array();
		//echo $this->db->last_query();
		return $appointments;
    }
	function get_appointments_between_times($start_date,$end_date,$start_time,$end_time,$doctor_id = NULL){
		$qry = "appointment_date >= '$start_date'  AND appointment_date <= '$end_date' AND (status !='NotAvailable' AND status != 'Cancel') ";
		$qry .= " AND (('".date('H:i:s',strtotime($start_time))."' >= start_time  AND '".date('H:i:s',strtotime($start_time))."' <= end_time ) OR ('".date('H:i:s',strtotime($end_time))."' >= start_time  AND '".date('H:i:s',strtotime($end_time))."' <= end_time)) ";
		if(isset($doctor_id)){
			$qry .= " AND userid='$doctor_id'";
		}
		$this->db->where($qry);
		$query=$this->db->get('appointments');
		//echo $this->db->last_query();
		$appointments = $query->result_array();
		
		return $appointments;
	}
	function get_appointments_by_email($patient_email) {
		$query = $this->db->get_where('view_patient', array('email' => $patient_email));
		
        $patients = $query->result_array();
		$patient_ids ="";
		foreach($patients as $patient){
			$patient_ids .= $patient['patient_id'];
		}
		
		
		$query = $this->db->get_where('appointments', array('patient_id IN ('.$patient_ids.')'));
		//echo $this->db->last_query()."<br/>";
        $appointments = $query->result_array();
		return $appointments;
	}
	function get_appointments_id($appointment_id) {
        $query = $this->db->get_where('appointments', array('appointment_id' => $appointment_id));
        return $query->row_array();
    }
	function get_appointment_from_id($appointment_id) {
        $query = $this->db->get_where('appointments', array('appointment_id' => $appointment_id));
        return $query->row_array();
    }
	function get_appointment_at($appointment_date, $hour, $min, $doc = NULL) {
        $appointment_date = date("Y-m-d", strtotime($appointment_date));
        if ($doc == NULL) {
            return;
        } else {
            $start_time = $hour.":".$min;
            $query = $this->db->get_where('appointments', array('appointment_date' => $appointment_date, 'start_time' => $start_time, 'userid' => $doc));
            return $query->row_array();
        }
    }
    function get_appointment_by_patient($patient_id){
        $date = date('Y-m-d');
        $this->db->select('appointment_id,start_time,appointment_date,userid,appointment_reason');
        $query = $this->db->get_where('appointments', array('patient_id' => $patient_id, 'appointment_date' => $date,'status!='=>'Complete'));
        $row = $query->num_rows();
        if ($row > 0) {
            return $query->row_array();
        } else {
            return FALSE;
        }
    }
    function delete_appointment($appointment_id){
        $this->db->delete('appointments', array('appointment_id' => $appointment_id));
    }
	function insert_availability($appointment_id = NULL){
		$start_date = date("Y-m-d", strtotime($this->input->post('visit_date')));
        $data['appointment_date'] = $start_date;
		$end_date=date("Y-m-d", strtotime($this->input->post('end_date')));
		$data['end_date']=$end_date;
		
		//Set Time Zone
		$timezone = $this->settings_model->get_time_zone();
        if (function_exists('date_default_timezone_set'))
            date_default_timezone_set($timezone);
		
		$timeformat = $this->settings_model->get_time_formate();	
		$data['start_time'] = date($timeformat,strtotime($this->input->post('start_time')));
		$data['end_time'] =  date($timeformat,strtotime($this->input->post('end_time')));
		        
		$data['status'] = 'NotAvailable';
		$data['visit_id']=0;
		$data['patient_id']=0;
		$data['title']="";
		if($this->input->post('doctor_id')==0)
		{
			$doc_id = $this->input->post('doctor');
		}
		else
		{
			$doc_id = $this->input->post('doctor_id');
		}
		$data['userid'] = $doc_id;
		
		if($appointment_id == NULL){
			$this->db->insert('appointments', $data);
		}else{
			$this->db->where('appointment_id', $appointment_id);
			$this->db->update('appointments', $data);
		}
	}
	function delete_availability($appointment_id) {
        $this->db->delete('appointments', array('appointment_id' => $appointment_id));       
    }
    function change_status($appointment_id, $new_status,$visit_id = NULL) {
		//Fetch Current Details
		$current_appointment = $this->get_appointments_id($appointment_id);
		
        //Update Status
        $data['status'] = $new_status;
		if(isset($visit_id)){
			$data['visit_id'] = $visit_id;
		}
		//Set To Time in Appointment if not set
		if($current_appointment['end_time'] == '00:00:00'){
			$data['end_time'] = date('H:i:s'); //Do Not Use Time Format	
		}
        $this->db->update('appointments', $data, array('appointment_id' => $appointment_id));
			
		
        //Set Time Zone
		$timezone = $this->settings_model->get_time_zone();
        if (function_exists('date_default_timezone_set'))
            date_default_timezone_set($timezone);
		
		//Update Old Appointment Log
        $data2['to_time'] = date('H:i:s');//Do Not Use Time Format
        $this->db->update('appointment_log', $data2, array('appointment_id' => $appointment_id, 'to_time' => '00:00:00'));
		
		//Insert New Log
        $data3['appointment_id'] = $appointment_id;
		$data3['appointment_reason'] = $current_appointment['appointment_reason'];
        $data3['change_date_time'] = date('d/m/Y H:i:s'); //Do Not Use Time Format
        $data3['start_time'] =  $current_appointment['start_time'];
        $data3['old_status'] = $current_appointment['status'];
        $data3['status'] = $new_status;
        $data3['from_time'] = date('H:i:s');//Do Not Use Time Format
        $data3['to_time'] = '';
        $data3['name'] = $this->session->userdata('name');
        $this->db->insert('appointment_log', $data3);

    }
	function change_status_visit($visit_id) {
        
        $data['status'] = "Complete";
		$this->db->update('appointments', $data, array('visit_id' => $visit_id));

		
		$this->db->where('visit_id', $visit_id);;
		$query=$this->db->get('appointments');
		$row=$query->row();
		
		$timezone = $this->settings_model->get_time_zone();
        if (function_exists('date_default_timezone_set'))
            date_default_timezone_set($timezone);

        $data2['to_time'] = date('H:i:s'); //Do Not Use Time Format
        $this->db->update('appointment_log', $data2, array('appointment_id' =>$row->appointment_id, 'to_time' => '00:00:00'));

		
        $data3['appointment_id'] = $row->appointment_id;
		$data3['appointment_reason'] = $row->appointment_reason;
        $data3['change_date_time'] = date('d/m/Y H:i:s');
        $data3['start_time'] = $row->start_time;
        $data3['old_status'] = "Consultation";
        $data3['status'] = "Complete";
        $data3['from_time'] = date('H:i:s'); //Do Not Use Time Format
        $data3['to_time'] = '';
        $data3['name'] = $this->session->userdata('name');
        $this->db->insert('appointment_log', $data3);

		/* Get Insert Visit's patient_id */
        $patient_id = $this->get_patient_id($visit_id);

        $this->db->select('bill_id');
        $this->db->order_by("bill_id", "desc");
        $this->db->limit(1);
        $query = $this->db->get_where('bill', array('patient_id' => $patient_id));
        $result = $query->row();

        if($result)
		{
            $result = $query->row();
            $bill_id = $result->bill_id;            

            $this->db->select('due_amount');
            $query = $this->db->get_where('bill', array('bill_id' => $bill_id));
            $result = $query->row();
            $pre_due_amount = $result->due_amount;

            $this->db->select_sum('amount');
            $query = $this->db->get_where('bill_detail', array('bill_id' => $bill_id));
            $result = $query->row();
            $bill_amount = $result->amount;

            $this->db->select('amount');
            $query = $this->db->get_where('payment_transaction', array('bill_id' => $bill_id, 'payment_type' => 'bill_payment'));
            
            if($query->num_rows() > 0){
                $result = $query->row();
                $paid_amount = $result->amount;
            }else{
                $paid_amount = 0;
            }
            $due_amount = $pre_due_amount + $bill_amount - $paid_amount;

            $bill_id = $this->create_bill($visit_id, $patient_id, $due_amount);
        }
		else
		{
            $bill_id = $this->create_bill($visit_id, $patient_id);
        }
    }
    function get_user_id($user_name) {
        $this->db->select('userid');
        $query = $this->db->get_where('users', array('username' => $user_name));
        return $query->row();
    }
    function get_followup($follow_date) {
        $this->db->order_by("followup_date", "desc");
        $query = $this->db->get_where('patient', array('followup_date <' => $follow_date, 'followup_date !=' => '0000:00:00'));
        return $query->result_array();
    }
    function get_report($from_date,$to_date, $user_id=NULL) {
		if($user_id == NULL){
			$this->db->order_by("appointment_date", "asc");
			$query = $this->db->get_where('view_report', array('appointment_date >=' => $from_date, 'appointment_date <=' => $to_date,));
			//echo $this->db->last_query();
		}else{
			$this->db->order_by("userid", "asc");
			$this->db->order_by("appointment_date", "asc");
			$query = $this->db->get_where('view_report', array('userid' => $user_id, 'appointment_date >=' => $from_date, 'appointment_date <=' => $to_date));
		}
        return $query->result_array();;
    }
	function get_export_query($from_date,$to_date, $user_id) {
		$this->db->select('patient_name,doctor_name,appointment_date,appointment_time,waiting_in,waiting_duration,consultation_in,consultation_out,consultation_duration');
		if($user_id == NULL){
			$this->db->order_by("appointment_date", "asc");
			$query = $this->db->get_where('view_report', array('appointment_date >=' => $from_date, 'appointment_date <=' => $to_date,));
			//echo $this->db->last_query();
		}else{
			$this->db->order_by("userid", "asc");
			$this->db->order_by("appointment_date", "asc");
			$query = $this->db->get_where('view_report', array('userid' => $user_id, 'appointment_date >=' => $from_date, 'appointment_date <=' => $to_date));
			//echo $this->db->last_query();
		}
        return $query;
    }
    function get_todos(){
        $user_id = $this->session->userdata('id'); 
        $query = "Select * FROM " . $this->db->dbprefix('todos') . " WHERE userid = " . $user_id . " AND (done = 0 OR (done_date > DATE_SUB(NOW(), INTERVAL 29 DAY) AND done = 1)) ORDER BY done ASC, add_date DESC;";
        $result = $this->db->query($query);
        return $result->result_array();
    }
    function add_todos(){
        $data['userid'] = $this->session->userdata('id'); 
        $data['add_date'] = date('Y-m-d H:i:s');
        $data['done'] = 0;
        $data['todo'] = $this->input->post('task');
        $this->db->insert('todos', $data);

        redirect('appointment/index');
    }
    function todo_done($done, $id) {
        $data['done'] = $done;
        if ($data['done'] == 1) {
            $data['done_date'] = date('Y-m-d H:i:s');
        } else {
            $data['done_date'] = NULL;
        }
        $this->db->update('todos', $data, array('id_num' => $id));

        return;
    }
    function delete_todo($id) {
        $this->db->delete('todos', array('id_num' => $id));

        return;
    }
	function get_patient_id($visit_id) {
        $query = $this->db->get_where('visit', array('visit_id' => $visit_id));
        $row = $query->row();
        if ($row)
            return $row->patient_id;
        else
            return 0;
    }
	function get_visit_from_id($visit_id) {
        $query = $this->db->get_where('visit', array('visit_id' => $visit_id));
        $row = $query->row_array();
        return $row;
    }
	function add_appointment_from_visit() {
		$data['patient_id'] = $this->input->post('patient_id');
		$level = $this->session->userdata('category');
        if($level == 'Doctor'){
            $data['userid'] = $this->session->userdata('id');
			$doctor_id = $this->session->userdata('id');
        }else{
            $data['userid'] = $this->input->post('doctor');
			$doctor_id=$this->input->post('doctor');
        }
		$data['appointment_date'] = date("Y-m-d", strtotime($this->input->post('visit_date')));
		
		$time_interval = $this->settings_model->get_time_interval();	
		$hr=date("H", strtotime($this->input->post('visit_time')));
		$min=date("i", strtotime($this->input->post('visit_time')));
		if($time_interval==30)
		{
			if($min<15 || $min>45){
				$data['start_time']=$hr.":00:00";
			}else{
				$data['start_time']=$hr.":30:00";	
			}
		}
		elseif($time_interval==15)
		{
			if($min<8 ||$min >=52){
				$data['start_time']=$hr.":00:00";
			}elseif($min<22 ||$min >=8){
				$data['start_time']=$hr.":15:00";
			}elseif($min<38 ||$min >=22){
				$data['start_time']=$hr.":30:00";
			}elseif($min<52 ||$min >=38){
				$data['start_time']=$hr.":45:00";
			}
		}
		else		
		{
			$data['start_time'] = $hr.":".$min.":00";
		}
		
		$time = strtotime($data['start_time']);
		$time = date("H:i",strtotime('+30 minutes',$time)); //Do Not Use Time Format
		$data['end_time']=date("H:i",strtotime($time));		//Do Not Use Time Format
		 /* Get Insert Visit's visit_id */
 		$this->db->select_max('visit_id','visit_id');
		$query = $this->db->get('ck_visit');
		$row=$query->row_array();
		$visit_id = $row['visit_id'];
	
        /* Get Insert Visit's patient_id */
        $patient_id = $this->get_patient_id($visit_id);
		$data['visit_id']=$visit_id;
		$data['patient_id']=$patient_id;
		$data['status'] = 'Consultation';
		
		/* Get title */
		$data['title']=$this->get_patient_name($patient_id);
		$this->db->insert('appointments', $data);

				
		$data2['appointment_id'] = $this->db->insert_id();
		$data2['change_date_time'] = date('d/m/Y H:i:s A'); //Do Not Use Time Format
		$data2['start_time'] = $data['start_time'];
		$data2['old_status'] = "Waiting";
		$data2['status'] = 'Consultation';
		$data2['from_time'] = $data['start_time'];
		$data2['to_time'] = "00:00:00";
		$data2['name'] = $this->session->userdata('name'); 

		$this->db->insert('appointment_log', $data2);

     }
	public function get_patient_name($patient_id) {
	
		$this->db->select('first_name,middle_name,last_name');
		$this->db->from('contacts');
		$this->db->join('patient', 'patient.contact_id = contacts.contact_id');
		$this->db->where('patient_id', $patient_id); 
		$query = $this->db->get();

        $row = $query->row();
        if ($row)
            return $row->first_name.' '.$row->middle_name.' '.$row->last_name;
        else
            return 0;
    }
	public function insert_new_visit($appointment_id,$visit_notes){
		$query=$this->db->get_where('appointments',array('appointment_id'=>$appointment_id));
		//echo $this->db->last_query()."<br/>";
		$row=$query->row();
		if ($row){
			$data['visit_date'] = $row->appointment_date;
			$data['visit_time'] = date("h:i:s",strtotime($row->start_time)); //Do Not Use Time Format
			$data['notes'] = $visit_notes;
			$data['patient_id']=$row->patient_id;
			$data['userid']=$row->userid;
			$this->db->insert('visit', $data);
			//echo $this->db->last_query()."<br/>";
			$visit_id= $this->db->insert_id();
			
			$update_data['visit_id']=$visit_id;
			$this->db->update('appointments', $update_data, array('appointment_id' => $appointment_id));
			
			$bill_data['bill_date'] =  $row->appointment_date;
			$bill_data['patient_id'] = $row->patient_id;
			$bill_data['visit_id'] = $visit_id;
			$bill_data['due_amount'] = 0.00;
			$this->db->insert('bill', $bill_data);
			
			return $visit_id;
		}
		
		
	}
    public function insert_visit($app_id) {

        /* Insert New Visit */		
		
		$query=$this->db->get_where('appointments',array('appointment_id'=>$app_id));
		$row=$query->row();
		$patient_id=$row->patient_id;
		$data['notes'] = "";
        $data['type'] = "New Visit";
        $data['visit_date'] = $row->appointment_date;
        $data['visit_time'] = date("h:i:s",strtotime($row->start_time)); //Do Not Use Time Format
		$data['patient_id']=$row->patient_id;
		$data['userid']=$row->userid;
        $this->db->insert('visit', $data);

		
        /* Get Insert Visit's visit_id */
        $insert_visit_id= $this->db->insert_id();
		
		$date['followup_date'] = date("Y-m-d",strtotime($row->appointment_date.'+ 15 days')); //Do Not Use Time Format
        $sql = "update " . $this->db->dbprefix('patient') . " set followup_date = ? where patient_id = ?;";
        $this->db->query($sql, array($date['followup_date'], $patient_id));		
		
        /* Get Insert Visit's patient_id */
        $patient_id = $this->get_patient_id($insert_visit_id);

        $this->db->select('bill_id');
        $this->db->order_by("bill_id", "desc");
        $this->db->limit(1);
        $query = $this->db->get_where('bill', array('patient_id' => $patient_id));
        $result = $query->row();

        if($result)
		{
            $result = $query->row();
            $bill_id = $result->bill_id;            

            $this->db->select('due_amount');
            $query = $this->db->get_where('bill', array('bill_id' => $bill_id));
            $result = $query->row();
            $pre_due_amount = $result->due_amount;

            $this->db->select_sum('amount');
            $query = $this->db->get_where('bill_detail', array('bill_id' => $bill_id));
            $result = $query->row();
            $bill_amount = $result->amount;

            $this->db->select('amount');
            $query = $this->db->get_where('payment_transaction', array('bill_id' => $bill_id, 'payment_type' => 'bill_payment'));
            
            if($query->num_rows() > 0){
                $result = $query->row();
                $paid_amount = $result->amount;
            }else{
                $paid_amount = 0;
            }
            $due_amount = $pre_due_amount + $bill_amount - $paid_amount;

            $bill_id = $this->create_bill($insert_visit_id, $patient_id, $due_amount);
        }
		else
		{
            $bill_id = $this->create_bill($insert_visit_id, $patient_id);
        }
        /* Create Bill For Newly Entered Visit and Get bill_id */
		
		return $insert_visit_id;

    }
	public function create_bill($visit_id, $patient_id, $due_amount = NULL) {
        $data['bill_date'] = date('Y-m-d');
        $data['patient_id'] = $patient_id;
        $data['visit_id'] = $visit_id;
        if($due_amount == NULL){
            $data['due_amount'] = 0.00;
        }else{
            $data['due_amount'] = $due_amount;
        }
        $this->db->insert('bill', $data);

        return $this->db->insert_id();
    }
	
	
}