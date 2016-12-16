<?php

class Appointment extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('appointment_model');
        $this->load->model('admin/admin_model');
		$this->load->model('contact/contact_model');
        $this->load->model('patient/patient_model');
		$this->load->model('payment/payment_model');
        $this->load->model('settings/settings_model');
		$this->load->model('module/module_model');
		$this->load->model('menu_model');
		
        $this->load->helper('url');
        $this->load->helper('form');
		$this->load->helper('currency_helper');
		$this->load->helper('directory' );
		$this->load->helper('inflector');

		$this->lang->load('main'); 
		
        $this->load->library('form_validation');
		$this->load->library('session');
		$this->load->library('export');
		
        $prefs = array(
            'show_next_prev' => TRUE,
            'next_prev_url' => base_url() . 'index.php/appointment/index',
        );
        $this->load->library('calendar', $prefs);		
    }
	public function index($year = NULL, $month = NULL, $day = NULL) {
		// Check If user has logged in or not 
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {
            redirect('login/index');
        } else {
			$timezone = $this->settings_model->get_time_zone();
			if (function_exists('date_default_timezone_set'))
				date_default_timezone_set($timezone);
				
			//Default to today's date if date is not mentioned
            if ($year == NULL) { $year = date("Y"); }
            if ($month == NULL) { $month = date("m"); }
            if ($day == NULL) { $day = date("d");}
			
            $data['year'] = $year;
            $data['month'] = $month;
            $data['day'] = $day;
			
			//Fetch Time Interval from settings
            $data['time_interval'] = $this->settings_model->get_time_interval();
			$data['time_format'] = $this->settings_model->get_time_formate();
			
			//Generate display date in YYYY-MM-DD formate
            //$appointment_date = date("Y-n-d", gmmktime(0, 0, 0, $month, $day, $year));                      
			$appointment_date = $year ."-". $month."-".$day;
			$data['appointment_date']= $appointment_date;
			
			//Fetch Clinic Start Time and Clinic End Time
            $data['start_time'] = $this->settings_model->get_clinic_start_time();
            $data['end_time'] = $this->settings_model->get_clinic_end_time();
			
			//Fetch Task Details
            $data['todos'] = $this->appointment_model->get_todos();
			
			//Display Followups for next 8 days
			$followup_date = date('Y-m-d', strtotime("+8 days"));
			$data['followups'] = $this->patient_model->get_followups($followup_date);
			
			//Fetch all patient details
			$data['patients'] = $this->patient_model->get_patient();
			//Fetch Doctor Schedules
			$doctor="doctor";
			$doctor_active=$this->module_model->is_active($doctor);
			$data['doctor_active']=$doctor_active;
			
			if($doctor_active){	
				$this->load->model('doctor/doctor_model');			
				$data['doctors_data'] = $this->doctor_model->find_doctor();
				$data['drschedules'] = $this->doctor_model->find_drschedule();
				$data['inavailability'] = $this->appointment_model->get_dr_inavailability();
			}
			$data['exceptional_days']= $this->settings_model->get_exceptional_days();
			$data['working_days']= $this->settings_model->get_working_days();
			//Fetch Level of Current User
            //$level = $_SESSION["category"];
			$level = $this->session->userdata('category');
			
			
			//For Doctor's login
            if ($level == 'Doctor') {
				//Fetch this doctor's appointments for the date 
                $doctor_id = $this->session->userdata('id');
				$data['appointments'] = $this->appointment_model->get_appointments($appointment_date,$doctor_id);

            } else {
				//Fetch appointments for the date
                $data['appointments'] = $this->appointment_model->get_appointments($appointment_date);
            }
			//Fetch details of all Doctors
			$data['doctors'] = $this->admin_model->get_doctor();
			//Load the view
			$this->load->view('templates/header');
			$this->load->view('templates/menu');
			$this->load->view('browse', $data);
			$this->load->view('templates/footer');
        }
    }
	/** Add Appointment */
	public function add($year = NULL, $month = NULL, $day = NULL, $hour = NULL, $min = NULL,$status = NULL,$patient_id=NULL,$doctor_id=NULL) {
		//Check if user has logged in 
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {
            redirect('login/index');
        } else {
			$timezone = $this->settings_model->get_time_zone();
			if (function_exists('date_default_timezone_set'))
				date_default_timezone_set($timezone);
				
			$level = $this->session->userdata('category');
			
            if ($year == NULL) { $year = date("Y");}
            if ($month == NULL) { $month = date("m");}
            if ($day == NULL) { $day = date("d");}
			
			if ($hour == NULL) { $hour = date("H");}
            if ($min == NULL) { $min = date("i");}
			
			$data['year'] = $year;
			$data['month'] = $month;
			$data['day'] = $day;
			
            $today = date('Y-m-d');
			
			$data['hour'] = $hour;
			$data['min'] = $min;
			$time = $hour . ":" . $min;
			
            $appointment_dt = date("Y-m-d", strtotime($year."-".$month."-".$day));
			
            $data['appointment_date'] = $appointment_dt;
			$data['appointment_time'] = $time;
			$data['appointment_id']=0;
			if($status == NULL){
				$data['app_status'] = 'Appointments';
			}else{
				$data['app_status']=$status;
			}
				
			//Form Validation Rules
			$this->form_validation->set_rules('patient_id', 'Patient', 'required');
			$this->form_validation->set_rules('doctor_id', 'Doctor Name', 'required|callback_is_available');
			$this->form_validation->set_rules('start_time', 'Start Time', 'required|callback_validate_time');
			$this->form_validation->set_rules('end_time', 'End Time', 'required|callback_validate_time');
			$this->form_validation->set_rules('appointment_date', 'Date', 'required');

			if ($this->form_validation->run() === FALSE){
				$data['clinic_start_time'] = $this->settings_model->get_clinic_start_time();
				$data['clinic_end_time'] = $this->settings_model->get_clinic_end_time();
				$data['time_interval'] = $this->settings_model->get_time_interval();
				$data['patients'] = $this->patient_model->get_patient();
				$data['def_dateformate'] = $this->settings_model->get_date_formate();
				$data['def_timeformate'] = $this->settings_model->get_time_formate();
				$data['morris_date_format'] = $this->settings_model->get_morris_date_format();
				$data['morris_time_format'] = $this->settings_model->get_morris_time_format();
				if ($patient_id) {
					$data['curr_patient'] = $this->patient_model->get_patient_detail($patient_id);
				}
				if ($level == 'Doctor'){
					$doctor_id = $this->session->userdata('id');
					$data['doctors'] = $this->admin_model->get_doctor();
					$data['doctor']=$this->admin_model->get_doctor($doctor_id);
					$data['selected_doctor_id'] = $doctor_id;
				}else{
					$data['doctors'] = $this->admin_model->get_doctor();
				}
				$data['selected_doctor_id'] = $doctor_id;
				$this->load->view('templates/header');
				$this->load->view('templates/menu');
				$this->load->view('form', $data);
				$this->load->view('templates/footer');
			}else{
				$appointment_id = $this->appointment_model->add_appointment($status);
				$year = date("Y", strtotime($this->input->post('appointment_date')));
				$month = date("m", strtotime($this->input->post('appointment_date')));
				$day = date("d", strtotime($this->input->post('appointment_date')));
				
				$active_modules = $this->module_model->get_active_modules();
				if (in_array("alert", $active_modules)) {
					//Send Alert : new_appointment
					redirect('alert/send/new_appointment/0/0/'.$appointment_id.'/0/0/appointment/index/'.$year.'/'.$month.'/'.$day);
				}else{
					redirect('appointment/index/'.$year.'/'.$month.'/'.$day);
				}
			}
        }
    }
	function validate_time(){
		$appointment_date = date("Y-m-d", strtotime($this->input->post('appointment_date')));
		$start_time = $this->input->post('start_time');
		$end_time = $this->input->post('end_time');
		$doctor_id = $this->input->post('doctor_id');
		
		$clinic = $this->settings_model->get_clinic();
		$max_patient = $clinic['max_patient'];
		
		$appointments = $this->appointment_model->get_appointments_between_times($appointment_date,$appointment_date,$start_time,$end_time,$doctor_id);
		if($max_patient > 0){
			if(count($appointments) >= $max_patient){
				$this->form_validation->set_message('validate_time','This time is already booked with maximum patients!');
				return FALSE;
			} else{
				return TRUE;
			}
		}else{
			return TRUE;
		}
		
	}
	function is_available(){
		$appointment_date = date("Y-m-d", strtotime($this->input->post('appointment_date')));
		$start_time = $this->input->post('start_time');
		$end_time = $this->input->post('end_time');
		$doctor_id = $this->input->post('doctor_id');
		
		$is_unavailable = $this->appointment_model->get_doctor_unavailability($appointment_date,$start_time,$end_time,$doctor_id);
		if($is_unavailable){
			$this->form_validation->set_message('is_available','Doctor is not available during this time');
			return FALSE;
		} else{
			return TRUE;
		}
	}
	function edit_appointment($appointment_id) {
		//Check if user has logged in 
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {
            redirect('login/index');
        } else {
			$this->form_validation->set_rules('patient_id', 'Patient Name', 'required');
			$this->form_validation->set_rules('doctor_id', 'Doctor Name', 'required');
			$this->form_validation->set_rules('start_time', 'Start Time', 'required|callback_validate_time');
			$this->form_validation->set_rules('end_time', 'End Time', 'required|callback_validate_time');
			$this->form_validation->set_rules('appointment_date', 'Date', 'required');
			if ($this->form_validation->run() === FALSE){
				$appointment = $this->appointment_model->get_appointments_id($appointment_id);
				$data['appointment']=$appointment;
				$patient_id = $appointment['patient_id'];
				$data['curr_patient']=$this->patient_model->get_patient_detail($patient_id);
				$data['patients']=$this->patient_model->get_patient();
				$doctor_id = $appointment['userid'];
				$data['doctors'] = $this->admin_model->get_doctor();
				$data['selected_doctor_id'] = $doctor_id;
				$data['def_dateformate'] = $this->settings_model->get_date_formate();
				$data['def_timeformate'] = $this->settings_model->get_time_formate();
				$data['time_interval'] = $this->settings_model->get_time_interval();
				$data['clinic_start_time'] = $this->settings_model->get_clinic_start_time();
				$data['clinic_end_time'] = $this->settings_model->get_clinic_end_time();
				$data['morris_date_format'] = $this->settings_model->get_morris_date_format();
				$data['morris_time_format'] = $this->settings_model->get_morris_time_format();
				$this->load->view('templates/header');
				$this->load->view('templates/menu');
				$this->load->view('form', $data);
				$this->load->view('templates/footer');
			}else{
				$patient_id = $this->input->post('patient_id');
				$curr_patient = $this->patient_model->get_patient_detail($patient_id);
				$title = $curr_patient['first_name']." " .$curr_patient['middle_name'].$curr_patient['last_name']; 
				$this->appointment_model->update_appointment($title);
				$year = date('Y', strtotime($this->input->post('appointment_date')));
				$month = date('m', strtotime($this->input->post('appointment_date')));
				$day = date('d', strtotime($this->input->post('appointment_date')));
				redirect('appointment/index/'.$year.'/'.$month.'/'.$day);
			}
		}
	}
	public function insert_patient_add_appointment($hour = NULL, $min =NULL, $appointment_date = NULL, $status = NULL, $doc_id = NULL,$pid=NULL,$appid=NULL) {
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {
            redirect('login/index/');
        } 
		else 
		{            
            $this->form_validation->set_rules('first_name', 'First Name', 'required|alpha');
            $this->form_validation->set_rules('last_name', 'Last Name', 'required|alpha');
			$this->form_validation->set_rules('middle_name', 'Middle Name', 'alpha');

            if ($this->form_validation->run() === FALSE) {                				
				$this->add();
            } 
			else 
			{	
				$contact_id = $this->contact_model->insert_contact();
                $patient_id = $this->patient_model->insert_patient($contact_id);
				$appointment_date = date('Y-m-d',strtotime($appointment_date));
				list($year, $month, $day) = explode('-', $appointment_date);
				redirect('appointment/add/' . $year . '/' . $month . '/' . $day . '/' . $hour . "/" . $min . '/Appointments/' . $patient_id . "/" . $doc_id );
            }
        }
    }
	function change_status($appointment_id = NULL,$new_status = NULL) {
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {	
            redirect('login/index');
        }else{
			
			$this->appointment_model->change_status($appointment_id,$new_status);
			$appointment = $this->appointment_model->get_appointment_from_id($appointment_id);
			$appointment_date = $appointment['appointment_date'];
			$year = date("Y", strtotime($appointment_date));
            $month = date("m", strtotime($appointment_date));
            $day = date("d", strtotime($appointment_date));
			if($new_status == "Cancel"){
				$active_modules = $this->module_model->get_active_modules();
				if (in_array("alert", $active_modules)) {
					//Send Alert : appointment_cancel
					redirect('alert/send/appointment_cancel/0/0/'.$appointment_id.'/0/0/appointment/index/'.$year.'/'.$month.'/'.$day);
				}else{
					redirect('appointment/index/'.$year.'/'.$month.'/'.$day);
				}
			}elseif($new_status == "Complete"){
				$active_modules = $this->module_model->get_active_modules();
				if (in_array("alert", $active_modules)) {
					//Send Alert : appointment_complete
					redirect('alert/send/appointment_complete/0/0/'.$appointment_id.'/0/0/appointment/index/'.$year.'/'.$month.'/'.$day);
				}else{
					redirect('appointment/index/'.$year.'/'.$month.'/'.$day);
				}	
			}else{
				redirect('appointment/index/'.$year.'/'.$month.'/'.$day);
			}
        }
    }
	function change_status_visit($visit_id = NULL){
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {
            redirect('login/index');
        } else {            
            $this->appointment_model->change_status_visit($visit_id);
			redirect('appointment/index');
        }
	}
	function view_appointment($appointment_id) {
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {
			redirect('login/index');
        } else {        
			$appointment = $this->appointment_model->get_appointments_id($appointment_id);
			$data['appointment']=$appointment;
			$patient_id = $appointment['patient_id'];
			$data['patient']=$this->patient_model->get_patient_detail($patient_id);
			$doctor_id = $appointment['userid'];
			$data['doctor'] = $this->admin_model->get_doctor($doctor_id);
			$visit_id = $appointment['visit_id'];
			$data['visit'] = $this->appointment_model->get_visit_from_id($visit_id);
			$data['def_dateformate'] = $this->settings_model->get_date_formate();
			$data['def_timeformate'] = $this->settings_model->get_time_formate();
			$data['bill'] = $this->patient_model->get_bill($visit_id);
			$data['bill_details'] = $this->patient_model->get_bill_detail($visit_id);
			$data['particular_total'] = $this->patient_model->get_particular_total($visit_id);
			$data['active_modules'] = $this->module_model->get_active_modules();
			$active_modules=$data['active_modules'];
			$data['currency_postfix'] = $this->settings_model->get_currency_postfix();
			if (in_array("doctor", $active_modules)) {
				$data['fees_total'] = $this->patient_model->get_fee_total($visit_id);
			}else{
				$data['fees_total'] = 0;
			}
			if (in_array("treatment", $active_modules)) {
				$data['treatment_total'] = $this->patient_model->get_treatment_total($visit_id);
			}else{
				$data['treatment_total'] = 0;
			}
			$data['item_total'] = 0;
			$data['balance'] = 0;
			$bill_id = $this->patient_model->get_bill_id($visit_id);
			$data['paid_amount'] = $this->payment_model->get_paid_amount($bill_id);
			$data['discount'] = $this->patient_model->get_discount_amount($bill_id);
			$this->load->view('templates/header');
			$this->load->view('templates/menu');
			$this->load->view('view_appointment', $data);
			$this->load->view('templates/footer');
		}
	}
    function appointment_report_excel_export($from_date,$to_date,$user_id=NULL){
		$query = $this->appointment_model->get_export_query($from_date,$to_date,$user_id);
		$this->export->to_excel($query, 'appointment_report'); 
	}
	function print_appointment_report($from_date, $to_date, $user_id=NULL){
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {	
            redirect('login/index');
        } else {
			$data['def_dateformate'] = $this->settings_model->get_date_formate();
			$data['app_reports'] = $this->appointment_model->get_report($from_date, $to_date, $user_id);
			$this->load->view('appointment/print_report', $data);
		}
	}
	function appointment_report() {	
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {	
            redirect('login/index');
        } else {
            $data['doctors'] = $this->admin_model->get_doctor();
            $level = $this->session->userdata('category');
            $data['currency_postfix'] = $this->settings_model->get_currency_postfix();
			$data['def_dateformate'] = $this->settings_model->get_date_formate();
            if ($level == 'Doctor'){
                $this->form_validation->set_rules('from_date', $this->lang->line('from_date'), 'required');
				$this->form_validation->set_rules('to_date', $this->lang->line('to_date'), 'required');
                if ($this->form_validation->run() === FALSE) {
					$timezone = $this->settings_model->get_time_zone();
					if (function_exists('date_default_timezone_set'))
						date_default_timezone_set($timezone);
					$from_date = date('Y-m-d');
					$to_date = date('Y-m-d');
					$data['from_date'] = $from_date;
					$data['to_date'] = $to_date;
					$user_id = $this->session->userdata('id');
					$data['app_reports'] = $this->appointment_model->get_report($from_date, $to_date, $user_id);
                } else {
                    $from_date = date('Y-m-d', strtotime($this->input->post('from_date')));
					$data['from_date'] = $from_date;
					$to_date = date('Y-m-d', strtotime($this->input->post('to_date')));
					$data['to_date'] = $to_date;
					$user_id = $this->session->userdata('id');
                    $data['app_reports'] = $this->appointment_model->get_report($from_date, $to_date, $user_id);
                }
				$data['doctor_id'] = $this->session->userdata('id');
				//var_dump($data);
                $this->load->view('templates/header');
                $this->load->view('templates/menu');
                $this->load->view('appointment/report', $data);
                $this->load->view('templates/footer');
            }else{
                $this->form_validation->set_rules('from_date', $this->lang->line('from_date'), 'required');
				$this->form_validation->set_rules('to_date', $this->lang->line('to_date'), 'required');
                if ($this->form_validation->run() === FALSE) {
                    $timezone = $this->settings_model->get_time_zone();
					if (function_exists('date_default_timezone_set'))
						date_default_timezone_set($timezone);
						
					$from_date = date('Y-m-d');
					$to_date = date('Y-m-d');
					//$user_id = $_SESSION['id'];
					$user_id = $this->session->userdata('id');
                    
					$data['from_date'] = $from_date;
					$data['to_date'] = $to_date;
					$data['doctor_id'] = NULL;
                    $data['app_reports'] = $this->appointment_model->get_report($from_date,$to_date, NULL);
					$this->load->view('templates/header');
					$this->load->view('templates/menu');
					$this->load->view('appointment/report', $data);
					$this->load->view('templates/footer');
                } 
				else 
				{
                    $from_date = date('Y-m-d', strtotime($this->input->post('from_date')));
					$to_date = date('Y-m-d', strtotime($this->input->post('to_date')));
                    $user_id = $this->input->post('doctor');
					$data['from_date'] = $from_date;
					$data['to_date'] = $to_date;
					$data['doctor_id'] = $user_id;
                    $data['app_reports'] = $this->appointment_model->get_report($from_date,$to_date, $user_id);
                    $this->load->view('templates/header');
					$this->load->view('templates/menu');
					$this->load->view('appointment/report', $data);
					$this->load->view('templates/footer');
                }
            }
        }
    }
    function todos() {
		$this->form_validation->set_rules('task', 'Task', 'required');
        if ($this->form_validation->run() === FALSE) {
		}else{
			$this->appointment_model->add_todos();	
		}
        redirect('appointment/index');
    }
    function todos_done($done, $id) {
		
        $this->appointment_model->todo_done($done, $id);
    }
    function delete_todo($id) {
        $this->appointment_model->delete_todo($id);
        redirect('appointment/index');
    }
}
?>