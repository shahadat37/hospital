<?php
class Patient extends CI_Controller {

    function __construct() {
        parent::__construct();
		
        $this->load->model('contact/contact_model');
        $this->load->model('patient_model');
	    $this->load->model('settings/settings_model');
        $this->load->model('admin/admin_model');
        $this->load->model('appointment/appointment_model');
		$this->load->model('module/module_model');
		$this->load->model('payment/payment_model');
		$this->load->model('menu_model');
		
        $this->load->helper('url');
        $this->load->helper('form');
        $this->load->helper('currency');
		$this->load->helper('date');
		
        $this->load->library('form_validation');
		$this->load->library('session');
		$this->load->library('export');
        
		$this->load->database();

		$this->lang->load('main');
		
    }
	
	/** Browse all patients*/
    public function index() {
		//Check if user has logged in 
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {
            redirect('login/index');
        } else {
            $data['patients'] = $this->patient_model->find_patient();
            $this->load->view('templates/header');
            $this->load->view('templates/menu');
            $this->load->view('patient/browse',$data);
            $this->load->view('templates/footer');
        }
    }
	/** File Upload for Patient Profile Image */
	function do_upload() {
        $config['upload_path'] = './profile_picture/';
		$config['allowed_types'] = 'jpg|png';
		$config['max_size'] = '500';
		$config['max_width'] = '1024';
		$config['max_height'] = '768';
		$config['overwrite'] = TRUE;
		$config['file_name'] = $this->input->post('contact_id');

		$this->load->library('upload', $config);

		if (!$this->upload->do_upload()) {
			$error = array('error' => $this->upload->display_errors());
			return $error;
		} else {
			$data = array('upload_data' => $this->upload->data());
			return $data['upload_data'];
		}
    }
	/** Edit Patient Details */
	public function edit($patient_id,$called_from) {
		//Check if user has logged in 
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {				
            redirect('login/index');
        } else {
			$this->form_validation->set_rules('first_name', 'First Name', 'required');
            $this->form_validation->set_rules('last_name', 'Last Name', 'required');
			$this->form_validation->set_rules('email', 'Email', 'valid_email');
			
			if ($this->form_validation->run() === FALSE) {
				$contact_id = $this->patient_model->get_contact_id($patient_id);
				$data['called_from']=$called_from;
				$data['patient_id'] = $patient_id;
				$data['patient'] = $this->patient_model->get_patient_detail($patient_id);
				$data['contacts'] = $this->contact_model->get_contacts($contact_id);
				$data['address'] = $this->contact_model->get_contact_address($contact_id);
				$data['emails'] = $this->contact_model->get_contact_email($contact_id);
				$data['def_dateformate'] = $this->settings_model->get_date_formate();
				$this->load->view('templates/header');
				$this->load->view('templates/menu');
				$this->load->view('form', $data);
				$this->load->view('templates/footer');
			} else {
				$patient_id = $this->input->post('patient_id');
				
				$file_upload = $this->do_upload(); 
				//Error uploading the file
				if(isset($file_upload['error']) && $file_upload['error']!='<p>You did not select a file to upload.</p>'){
					$contact_id = $this->patient_model->get_contact_id($patient_id);
					$data['patient_id'] = $patient_id;
					$data['patient'] = $this->patient_model->get_patient_detail($patient_id);
					$data['contacts'] = $this->contact_model->get_contacts($contact_id);
					$data['address'] = $this->contact_model->get_contact_address($contact_id);
					$data['emails'] = $this->contact_model->get_contact_email($contact_id); 
					$data['def_dateformate'] = $this->settings_model->get_date_formate();					
					$data['error'] = $file_upload['error'];		
					$this->load->view('templates/header');
					$this->load->view('templates/menu');
					$this->load->view('form', $data);
					$this->load->view('templates/footer');
				} else {
					if(isset($file_upload['file_name'])){
						$file_name = $file_upload['file_name'];
					}else{	
						$file_name = NULL;
					}
					//Save the details
					if(isset($patient_id) && $patient_id!=NULL){
						$this->contact_model->update_contact($file_name);
						$this->contact_model->update_address();
						$this->patient_model->update_reference_by($patient_id);
						$this->patient_model->update_patient_data($patient_id);
						$this->patient_model->update_display_id();
						if($called_from =="patient"){
							$message = "Patient updated successfully!";
							$this->index();
						}else{
							redirect('patient/visit/' . $patient_id);
						}
					}else{
						$contact_id = $this->contact_model->insert_contact();     
						$patient_id = $this->patient_model->insert_patient($contact_id);
						$this->contact_model->update_profile_image($file_name,$contact_id);
						$active_modules = $this->module_model->get_active_modules();
						if (in_array("alert", $active_modules)) {				
							//Send Alert : new_patient
							redirect("alert/send/new_patient/$patient_id/0/0/0/0/patient/index/0/0/0");
						}else{
							$message = "Patient added successfully!";
							$this->index();
						}
					}
                }
			}
        }
	}
	/**Just show the Form to Add Patient */
    public function insert() {
		//Check if user has logged in 
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {
            redirect('login/index');
        } else {
			$data['def_dateformate'] = $this->settings_model->get_date_formate();
			$this->load->view('templates/header');
			$this->load->view('templates/menu');
			$this->load->view('form', $data);
			$this->load->view('templates/footer');
        }
    }
	/** Delete Patient */
    public function delete($patient_id) {
		//Check if user has logged in 
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {	
            redirect('login/index/');
        } else {
            $this->patient_model->delete_patient($patient_id);
            $this->index();
        }
    }
	public function add_inquiry(){
		$contact_id = $this->contact_model->insert_contact();     
		$patient_id = $this->patient_model->insert_patient($contact_id);
		echo "Patient Inquiry Saved";
	}
	/** Visit details of a Patient*/
    public function visit($patient_id = NULL, $appointment_id = NULL, $app_date = NULL, $hour = NULL , $min = NULL) {
		//Check if user has logged in 
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {
            redirect('login/index/');
        } else {
			//Set Timezone
			$timezone = $this->settings_model->get_time_zone();
			if (function_exists('date_default_timezone_set'))
				date_default_timezone_set($timezone);
			
			$data['def_dateformate'] = $this->settings_model->get_date_formate();
			$data['def_timeformate'] = $this->settings_model->get_time_formate();
			$data['morris_date_format'] = $this->settings_model->get_morris_date_format();
			$data['morris_time_format'] = $this->settings_model->get_morris_time_format();
			
			$data['curr_date']=date($data['def_dateformate']);
			$data['curr_time']=date($data['def_timeformate']);		
            
			$data['error']="";
			
			$data['active_modules'] = $this->module_model->get_active_modules();
			$active_modules=$data['active_modules'];
			
            $data['clinic_start_time'] = $this->settings_model->get_clinic_start_time();
            $data['clinic_end_time'] = $this->settings_model->get_clinic_end_time();
			$data['time_interval'] = $this->settings_model->get_time_interval();
			
			if ($this->session->userdata('category') == 'Doctor'){
				$id = $this->session->userdata('id');
				$data['doctors']=$this->admin_model->get_doctor($id);
			}else{
				$data['doctors'] = $this->admin_model->get_doctor();
				$this->form_validation->set_rules('doctor', 'Doctor Name', 'required');
			}
			
			if (in_array("treatment", $active_modules)){
				$this->load->model('treatment/treatment_model');
            	$data['treatments'] = $this->treatment_model->get_treatments();
			}
			if (in_array("prescription", $active_modules)){
				$this->load->model('prescription/prescription_model');
            	$data['medicines'] = $this->prescription_model->get_medicines();
			}
			
			$data['clinic_settings'] = $this->settings_model->get_clinic_settings();
			$data['appointment_doctor'] = 0;
            
			$this->form_validation->set_rules('doctor', 'Doctor', 'required');
			$this->form_validation->set_rules('visit_date', 'Visit Date', 'required');
            $this->form_validation->set_rules('notes', 'Notes', 'required');
			$this->form_validation->set_rules('visit_time', 'Time', 'required');
			
            if ($this->form_validation->run() === FALSE) {
                
            } else {
                $visit_id = $this->patient_model->insert_visit();
				$patient_id = $this->input->post('patient_id');
				if (in_array("prescription", $active_modules)){
					$this->prescription_model->insert_prescription($visit_id,$patient_id);
				}
                
				$doctor_id = $this->input->post('doctor');
				
				$this->patient_model->change_followup_detail($patient_id,$doctor_id);
            }
			$data['patient_id'] = $patient_id;

			if (!isset($appointment_id)) {
                $result = $this->appointment_model->get_appointment_by_patient($patient_id);
				
                if ($result == FALSE) {
                    $data['appointment_id'] = NULL;
                    $data['start_time'] = NULL;
                    $data['appointment_date'] = NULL;			
					$data['appointment_reason'] = NULL;
                } else {
                    $data['appointment_id'] = $result['appointment_id'];
                    $data['start_time'] = $result['start_time'];
                    $data['appointment_date'] = $result['appointment_date'];
					$data['appointment_doctor']= $result['userid'];
					$data['appointment_reason'] = $result['appointment_reason'];
                }
            } else {
                $data['appointment_id'] = $appointment_id;
				$time = $hour . ":" . $min;
                $data['start_time'] = $time;
                $data['appointment_date'] = $app_date;
				$appointment = $this->appointment_model->get_appointments_id($appointment_id);
				$data['appointment_doctor']=$appointment['userid'];
				$data['appointment_reason']=$appointment['appointment_reason'];
            }
			$data['active_modules'] = $this->module_model->get_active_modules();
			$data['patient'] = $this->patient_model->get_patient_detail($patient_id);
			$data['addresses'] = $this->contact_model->get_contacts($data['patient']['contact_id']);
			$data['visit_treatments'] = $this->patient_model->get_visit_treatments();
			$data['currency_postfix'] = $this->settings_model->get_currency_postfix();
			$data['visits'] = $this->patient_model->get_previous_visits($patient_id);
			
			$this->load->view('templates/header');
			$this->load->view('templates/menu');
			$this->load->view('visit', $data);
			$this->load->view('templates/footer');
        }
    }
	/** Edit Visit */
    public function edit_visit($visit_id, $patient_id,$appointment_id =NULL ) {
		//session_start();
		//Check if user has logged in 
		//if (!isset($_SESSION["user_name"]) || $_SESSION["user_name"] == '') {
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {	
            redirect('login/index/');
        } else {			
            $this->form_validation->set_rules('notes', 'Note Field', 'required');
			$this->form_validation->set_rules('visit_doctor', 'Doctor Name', 'required');
			$this->form_validation->set_rules('visit_date', 'Visit Date', 'required');
			$this->form_validation->set_rules('visit_time', 'Visit Time', 'required');
            if ($this->form_validation->run() === FALSE) {
				$data['visit'] = $this->patient_model->get_visit_data($visit_id);
				
				if ($this->session->userdata('category') == 'Doctor'){
					$id = $this->session->userdata('id');
					$data['doctors']=$this->admin_model->get_doctor();
					$data['doctor'] = $this->admin_model->get_doctor($id);
				}else{
					$data['doctors'] = $this->admin_model->get_doctor();
					$data['doctor'] = $this->admin_model->get_doctor($data['visit']['userid']);
				}
				
				$active_modules = $this->module_model->get_active_modules();
				$data['active_modules'] = $active_modules;
				if (in_array("treatment", $active_modules)) {
					$this->load->model('treatment/treatment_model');
                	$data['treatments'] = $this->treatment_model->get_treatments();
				}
                $data['visit_treatments'] = $this->settings_model->get_visit_treatment($visit_id);
				$data['def_dateformate'] = $this->settings_model->get_date_formate();
				$data['def_timeformate'] = $this->settings_model->get_time_formate();
				$data['time_interval'] = $this->settings_model->get_time_interval();
				$data['visit_id'] = $visit_id;
				$data['patient_id'] = $patient_id;
				$data['appointment_id'] = $appointment_id;
                $this->load->view('templates/header');
                $this->load->view('templates/menu');
                $this->load->view('edit_visit', $data);
                $this->load->view('templates/footer');
            } else {
                $this->patient_model->edit_visit_data($visit_id);
				$this->visit($patient_id);
                //redirect('patient/visit/' . $patient_id);
            }
        }
    }
	public function check_available_stock($required_stock, $item_id) {
		if ($this->module_model->is_active('stock')){
			$this->load->model('stock/stock_model');
			$item_detail = $this->stock_model->get_item($item_id);	
			
			$available_quantity = $item_detail['available_quantity'];
			if ($available_quantity < $required_stock) {
				$this->form_validation->set_message('check_available_stock', 'Required Quantity ' . $required_stock . ' exceeds Available Stock (' . $available_quantity . ') for Item ' . $item_detail['item_name']);
				return FALSE;
			} else {
				return TRUE;
			}	
		}else{
			$this->form_validation->set_message('check_available_stock', 'Stock Module Missing');
			return FALSE;
		}
    }
	public function edit_bill($bill_id){
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {
            redirect('login/index/');
        } else {
			$visit_id = $this->patient_model->get_visit_id($bill_id);
			$patient_id = $this->patient_model->get_patient_id_for_bill($bill_id);
			$this->bill($visit_id,$patient_id);
		}
	}
	/* Bill Details */
    public function bill($visit_id = NULL, $patient_id = NULL) {
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {
            redirect('login/index/');
        } else {
            $bill_id = $this->patient_model->get_bill_id($visit_id);
		    if ($bill_id == NULL)
				$bill_id = $this->patient_model->create_bill($visit_id, $patient_id);
			
			$data['visit_id'] = $visit_id;
			
			$visit = $this->patient_model->get_visit_data($visit_id);
			$patient_id = $visit['patient_id'];
			$data['patient_id'] = $patient_id;
			$data['visit_date'] = date('d-m-Y',strtotime($visit['visit_date']));
			
			$doctor_id = $visit['userid'];
			$doctor = $this->admin_model->get_doctor($doctor_id);
			$data['doctor_name'] = $doctor['name'];
            
            $data['patient'] = $this->patient_model->get_patient_detail($patient_id);
            
			$result = $this->appointment_model->get_appointment_by_patient($patient_id);
			if ($result == FALSE) {
				$data['appointment_id'] = NULL;
			} else {
				$data['appointment_id'] = $result['appointment_id'];
			}
				
            $data['adv_payment'] = $this->patient_model->get_balance_amount($bill_id,$patient_id);
			
            $data['currency_postfix'] = $this->settings_model->get_currency_postfix();
			
            $action = $this->input->post('submit');
			
			$active_modules = $this->module_model->get_active_modules();
			$data['active_modules'] = $active_modules;
			
			$data['fees'] = array();
			if (in_array("doctor", $active_modules)) {
				$this->load->model('doctor/doctor_model');
				$doc_id = $this->doctor_model->get_doctor_user_id($doctor_id);
				$data['fees'] = $this->doctor_model->get_doctor_fees($doc_id['doctor_id']);
			}
			
			if (in_array("treatment", $active_modules)) {
				$this->load->model('treatment/treatment_model');
				$data['treatments'] = $this->treatment_model->get_treatments();
			}
			
            if ($action == 'item') {
				$item_id = $this->input->post('item_id');
				$this->form_validation->set_rules('item_name', 'Item', 'required');
                $this->form_validation->set_rules('item_amount', 'Amount', 'required');
				$this->form_validation->set_rules('item_quantity', 'Quantity', 'required|callback_check_available_stock['.$item_id.']');
				if ($this->form_validation->run() === FALSE) {
                   
                } else {
                    $item = $this->input->post('item_name');
					
                    $amount = $this->input->post('item_amount');
					$quantity = $this->input->post('item_quantity');
                    $this->patient_model->add_bill_item($action, $bill_id, $item, $quantity, $amount*$quantity, $amount,$item_id);
                }
				
                $data['bill_id'] = $bill_id;
				
                $data['bill'] = $this->patient_model->get_bill($visit_id);
                $data['bill_details'] = $this->patient_model->get_bill_detail($visit_id);
                
				$data['balance'] = $this->patient_model->get_balance_amount($bill_id,$patient_id);
				
			}elseif ($action == 'fees') {
				$this->form_validation->set_rules('fees_detail', 'Fees', 'required');
                $this->form_validation->set_rules('fees_amount', 'Amount', 'required');
				if ($this->form_validation->run() === FALSE) {
                   
                } else {
                    $fees_detail = $this->input->post('fees_detail');
                    $fees_amount = $this->input->post('fees_amount');
                    $this->patient_model->add_bill_item($action, $bill_id, $fees_detail, 1, $fees_amount,$fees_amount);
                }
				
                $data['bill_id'] = $bill_id;
				
                $data['bill'] = $this->patient_model->get_bill($visit_id);
                $data['bill_details'] = $this->patient_model->get_bill_detail($visit_id);
                
				$data['balance'] = $this->patient_model->get_balance_amount($bill_id,$patient_id);
			}elseif ($action == 'treatment') {
				$this->form_validation->set_rules('treatment', 'Treatment', 'required');
                $this->form_validation->set_rules('treatment_price', 'Amount', 'required');
				if ($this->form_validation->run() === FALSE) {
                   
                } else {
                    $treatment = $this->input->post('treatment');
                    $treatment_price = $this->input->post('treatment_price');
                    $this->patient_model->add_bill_item($action, $bill_id, $treatment, 1, $treatment_price,$treatment_price);
                }
				
                $data['bill_id'] = $bill_id;
				
                $data['bill'] = $this->patient_model->get_bill($visit_id);
                $data['bill_details'] = $this->patient_model->get_bill_detail($visit_id);
                
				$data['balance'] = $this->patient_model->get_balance_amount($bill_id,$patient_id);

			}elseif ($action == 'particular') {
				
				$this->form_validation->set_rules('particular', 'Particular', 'required');
                $this->form_validation->set_rules('particular_amount', 'Amount', 'required');
				if ($this->form_validation->run() === FALSE) {
					
                } else {
                    $particular = $this->input->post('particular');
                    $particular_amount = $this->input->post('particular_amount');
                    $this->patient_model->add_bill_item($action, $bill_id, $particular, 1, $particular_amount,$particular_amount);
                }
				
                $data['bill_id'] = $bill_id;
				
                $data['bill'] = $this->patient_model->get_bill($visit_id);
                $data['bill_details'] = $this->patient_model->get_bill_detail($visit_id);
                
				$data['balance'] = $this->patient_model->get_balance_amount($bill_id,$patient_id);
			
			}elseif ($action == 'discount') {
				$bill_amount = $this->patient_model->get_bill_amount($bill_id);
				$discount = $this->patient_model->get_discount_amount($bill_id);
				$bill_amount = $bill_amount + 1;
                $this->form_validation->set_rules('discount', 'Discount', 'required|less_than['.$bill_amount.']');
				if ($this->form_validation->run() === FALSE) {
					
                } else {
                    $discount_amount = $this->input->post('discount');
                    $this->patient_model->update_discount($bill_id,$discount_amount);
                }
				
                $data['bill_id'] = $bill_id;
				
                $data['bill'] = $this->patient_model->get_bill($visit_id);
                $data['bill_details'] = $this->patient_model->get_bill_detail($visit_id);
                
				$data['balance'] = $this->patient_model->get_balance_amount($bill_id,$patient_id);
			}else{
				
			    $data['bill_id'] = $bill_id;
				
				$data['bill'] = $this->patient_model->get_bill($visit_id);
				
                $data['bill_details'] = $this->patient_model->get_bill_detail($visit_id);
				
				$data['balance'] = $this->patient_model->get_balance_amount($bill_id,$patient_id);
				
			}
			if (in_array("stock", $active_modules)) {
				$this->load->model('stock/stock_model');
				$data['items'] = $this->stock_model->get_items();
				$data['item_total'] = $this->patient_model->get_item_total($visit_id);
			}else{
				$data['item_total'] = 0;
			}
			
			
			$data['particular_total'] = $this->patient_model->get_particular_total($visit_id);
			
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
			
			$data['paid_amount'] = $this->payment_model->get_paid_amount($bill_id);
			$data['discount'] = $this->patient_model->get_discount_amount($bill_id);
			
			$this->load->view('templates/header');
			$this->load->view('templates/menu');
			$this->load->view('bill', $data);
			$this->load->view('templates/footer');
        }
    }
	/* Print Receipt */
	public function print_receipt($visit_id) {
        //session_start();
		//Check if user has logged in 
		//if (!isset($_SESSION["user_name"]) || $_SESSION["user_name"] == '') {
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {
            redirect('login/index/');
        } else {
			$active_modules = $this->module_model->get_active_modules();
			$data['active_modules'] = $active_modules;
			
            //$data['medicine'] = $this->patient_model->get_medicine_total($visit_id);
            if (in_array("treatment", $active_modules)) {
				$data['treatment_total'] = $this->patient_model->get_treatment_total($visit_id);
			}
			$data['item_total'] = $this->patient_model->get_item_total($visit_id);
            $bill_id = $this->patient_model->get_bill_id($visit_id);
            
            $data['paid_amount'] = $this->payment_model->get_paid_amount($bill_id);
			$data['particular_total'] = $this->patient_model->get_particular_total($visit_id);
			if (in_array("doctor", $active_modules)) {
				$data['fees_total'] = $this->patient_model->get_fee_total($visit_id);
			}
            $data['currency_postfix'] = $this->settings_model->get_currency_postfix();
			
			$def_dateformate = $this->settings_model->get_date_formate();
			$def_timeformate = $this->settings_model->get_time_formate();
			$invoice = $this->settings_model->get_invoice_settings();
			$receipt_template = $this->patient_model->get_template();
			$template = $receipt_template['template'];
			
			
			
			$clinic = $this->settings_model->get_clinic_settings();
			
			//Clinic Details
			$clinic_array = array('clinic_name','tag_line','clinic_address','landline','mobile','email');
			foreach($clinic_array as $clinic_detail){
				$template = str_replace("[$clinic_detail]", $clinic[$clinic_detail], $template);
			}
			
			//Bill Details
			$bill_array = array('bill_date','bill_id','bill_time');
			$bill = $this->patient_model->get_bill($visit_id);
			$patient_id = $bill['patient_id'];
			$bill_details = $this->patient_model->get_bill_detail($visit_id);
			foreach($bill_array as $bill_detail){
				if($bill_detail == 'bill_date'){
					$bill_date = date($def_dateformate, strtotime($bill['bill_date']));
					$template = str_replace("[bill_date]", $bill_date, $template);
				}elseif($bill_detail == 'bill_time'){
					$bill_time = date($def_timeformate, strtotime($bill['bill_time']));
					$template = str_replace("[bill_time]", $bill_time, $template);
				}elseif($bill_detail == 'bill_id'){
					$bill_id = $invoice['static_prefix'] . sprintf("%0" . $invoice['left_pad'] . "d", $bill['bill_id']);
					$template = str_replace("[bill_id]", $bill_id, $template);
				}else{
					$template = str_replace("[$bill_detail]", $bill[$bill_detail], $template);
				}
			}
			//Patient Details
			$patient = $this->patient_model->get_patient_detail($patient_id);
			$patient_array = array('patient_name');
			foreach($patient_array as $patient_detail){
				if($patient_detail == 'patient_name'){
					$patient_name = $patient['first_name']." ".$patient['middle_name']." ".$patient['last_name'];
					$template = str_replace("[patient_name]",$patient_name, $template);
				}else{
					$template = str_replace("[$patient_detail]", $patient[$patient_detail], $template);
				}
			}
			$particular_table = "";
			$item_table = "";
			$treatment_table = "";
			$fees_table = "";
			$col_string = "";
			$particular_amount = 0;
			$item_amount = 0;
			$treatment_amount = 0;
			$fees_amount = 0;
			//Bill Columns
			$start_pos = strpos($template, '[col:');
			if ($start_pos !== false) {
				
				$end_pos= strpos($template, ']',$start_pos);
				$length = abs($end_pos - $start_pos);
				$col_string = substr($template, $start_pos, $length+1);
				$columns = str_replace("[col:", "", $col_string);
				$columns = str_replace("]", "", $columns);
				$cols = explode("|",$columns);
				
				
				foreach($bill_details as $bill_detail){
					
					if($bill_detail['type']=='particular'){
						$particular_table .= "<tr>";
						foreach($cols as $col){
							if($col =='mrp' || $col =='amount'){
								$particular_table .= "<td style='text-align:right;padding:5px;border:1px solid black;'>";
								$particular_table .= currency_format($bill_detail[$col])."</td>";
							}else{
								$particular_table .= "<td style='padding:5px;border:1px solid black;'>";
								$particular_table .= $bill_detail[$col]."</td>";
							}
						}
						$particular_table .= "</tr>";
						$particular_amount = $particular_amount + $bill_detail['amount'];
					}elseif($bill_detail['type']=='item'){
						$item_table .= "<tr>";
						foreach($cols as $col){
							if($col =='mrp' || $col =='amount'){
								$item_table .= "<td style='text-align:right;padding:5px;border:1px solid black;'>";
								$item_table .= currency_format($bill_detail[$col])."</td>";
							}else{
								$item_table .= "<td style='padding:5px;border:1px solid black;'>";
								$item_table .= $bill_detail[$col]."</td>";
							}
							
						}
						$item_table .= "</tr>";
						$item_amount = $item_amount + $bill_detail['amount'];
					}elseif($bill_detail['type']=='treatment'){
						$treatment_table .= "<tr>";
						foreach($cols as $col){
							if($col =='mrp' || $col =='amount'){
								$treatment_table .= "<td style='text-align:right;padding:5px;border:1px solid black;'>";
								$treatment_table .= currency_format($bill_detail[$col])."</td>";
							}else{
								$treatment_table .= "<td style='padding:5px;border:1px solid black;'>";
								$treatment_table .= $bill_detail[$col]."</td>";
							}
							
						}
						$treatment_table .= "</tr>";
						$treatment_amount = $treatment_amount + $bill_detail['amount'];
					}elseif($bill_detail['type']=='fees'){
						$fees_table .= "<tr>";
						foreach($cols as $col){
							if($col =='mrp' || $col =='amount'){
								$fees_table .= "<td style='text-align:right;padding:5px;border:1px solid black;'>";
								$fees_table .= currency_format($bill_detail[$col])."</td>";
							}else{
								$fees_table .= "<td style='padding:5px;border:1px solid black;'>";
								$fees_table .= $bill_detail[$col]."</td>";
							}
							
						}
						$fees_table .= "</tr>";
						$fees_amount = $fees_amount + $bill_detail['amount'];
					}
				}
				if($particular_table != ""){	
					$particular_table .= "<tr><td colspan='3' style='padding:5px;border:1px solid black;'><strong>Sub Total - Particular</strong></td><td style='text-align:right;padding:5px;border:1px solid black;'><strong>".currency_format($particular_amount)."</strong></td></tr>";
				}
				if($item_table != ""){
					$item_table .= "<tr><td colspan='3' style='padding:5px;border:1px solid black;'><strong>Sub Total - Items</strong></td><td style='text-align:right;padding:5px;border:1px solid black;'><strong>".currency_format($item_amount)."</strong></td></tr>";
				}
				if($treatment_table != ""){	
					$treatment_table .= "<tr><td colspan='3' style='padding:5px;border:1px solid black;'><strong>Sub Total - Treatment</strong></td><td style='text-align:right;padding:5px;border:1px solid black;'><strong>".currency_format($treatment_amount)."</strong></td></tr>";
				}
				if($fees_table != ""){	
					$fees_table .= "<tr><td colspan='3' style='padding:5px;border:1px solid black;'><strong>Sub Total - Fees</strong></td><td style='text-align:right;padding:5px;border:1px solid black;'><strong>".currency_format($fees_amount)."</strong></td></tr>";
				}
			}
			$table = $particular_table . $item_table . $treatment_table .$fees_table;
			$template = str_replace("$col_string",$table, $template);
			
			$balance = $this->patient_model->get_balance_amount($bill['bill_id']);
			$balance = currency_format($balance);
			$template = str_replace("[previous_due]",$balance, $template);
			
			$paid_amount = $this->payment_model->get_paid_amount($bill['bill_id']);
			$paid_amount = currency_format($paid_amount);
			$template = str_replace("[paid_amount]",$paid_amount, $template);
			
			$discount_amount = $this->patient_model->get_discount_amount($bill['bill_id']);
			$discount = currency_format($discount_amount);
			$template = str_replace("[discount]",$discount, $template);
			
			$total_amount = $particular_amount + $item_amount + $treatment_amount + $fees_amount - $discount_amount;
			$total_amount = currency_format($total_amount);
			$template = str_replace("[total]",$total_amount, $template);
			
			$template .="<input type='button' value='Print' id='print_button' onclick='window.print()'>
			<style>
				@media print{
					#print_button{
						display:none;
					}
					
				}
			</style>";
			$data['receipt_template'] = $template;
            $this->load->view('receipt_template/receipt', $data);
        }
    }
    function bill_detail_report_export($from_date,$to_date,$doctor){
		$query = $this->patient_model->get_bill_detail_export_query($from_date,$to_date,$doctor);
		$this->export->to_excel($query, 'bill_detail_report'); 
	}
	function bill_detail_report() {
        //Check if user has logged in 
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {
            redirect('login/index/');
        } else {
            $level = $this->session->userdata('category');
            $data['doctors'] = $this->admin_model->get_doctor();
			$data['currency_postfix'] = $this->settings_model->get_currency_postfix();
			$data['def_dateformate'] = $this->settings_model->get_date_formate();
			$data['def_timeformate'] = $this->settings_model->get_time_formate();
            $this->form_validation->set_rules('bill_from_date', 'From Bill Date', 'required');
            $this->form_validation->set_rules('bill_to_date', 'To Bill Date', 'required');

            if ($this->form_validation->run() === FALSE) {
				$data['selected_doctor'] = 'all';
				$data['reports'] = array();
				$data['bill_from_date'] = date('Y-m-d');
				$data['bill_to_date'] = date('Y-m-d');
				$data['reports'] = $this->patient_model->get_bill_report($data['bill_from_date'],$data['bill_to_date'],$data['selected_doctor']);
				
                $this->load->view('templates/header');
                $this->load->view('templates/menu');
                $this->load->view('patient/bill_detail_report', $data);
                $this->load->view('templates/footer');
            } else {
				$data['selected_doctor'] = $this->input->post('doctor');
				$data['bill_from_date'] = date('Y-m-d', strtotime($this->input->post('bill_from_date')));
				$data['bill_to_date'] = date('Y-m-d', strtotime($this->input->post('bill_to_date')));
				$level = $this->session->userdata('category');
				if ($level == 'Doctor') {
					$data['selected_doctor'] = $this->session->userdata('id');
				}elseif($this->input->post('doctor')){
					$data['selected_doctor'] = $this->input->post('doctor');
				}else{
					$data['selected_doctor'] = "all";
				}
                $data['reports'] = $this->patient_model->get_bill_report($data['bill_from_date'],$data['bill_to_date'],$data['selected_doctor']);
				$this->load->view('templates/header');
                $this->load->view('templates/menu');
                $this->load->view('patient/bill_detail_report', $data);
				$this->load->view('templates/footer');
            }
        }
    }
    function print_bill_detail_report($bill_from_date,$bill_to_date,$selected_doctor){
		$data['currency_postfix'] = $this->settings_model->get_currency_postfix();
		$data['reports'] = $this->patient_model->get_bill_report($bill_from_date,$bill_to_date,$selected_doctor);
		$this->load->view('patient/print_bill_detail_report', $data);
	}
	public function delete_bill_detail($bill_detail_id, $bill_id, $visit_id, $patient_id) {
        //session_start();
		//Check if user has logged in 
		//if (!isset($_SESSION["user_name"]) || $_SESSION["user_name"] == '') {
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {
            redirect('login/index/');
        } else {
            $this->patient_model->delete_bill_detail($bill_detail_id, $bill_id);
            $this->bill($visit_id, $patient_id);
        }
    }
    
	function followup($patient_id) {
		
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {
            redirect('login/index/');
        } else {
            $data['patient_id'] = $patient_id;
			$data['followups'] = $this->patient_model->get_followups_patient($patient_id);
			$data['patient'] = $this->patient_model->get_patient_detail($patient_id);
			$data['def_dateformate'] = $this->settings_model->get_date_formate();
			$data['def_timeformate'] = $this->settings_model->get_time_formate();
			if($this->session->userdata('category') == 'Doctor'){
				$id = $this->session->userdata('id');
				$data['doctor']=$this->admin_model->get_doctor($id);
			}else{
				$data['doctors'] = $this->admin_model->get_doctor();
			}
            $this->load->view('templates/header');
            $this->load->view('templates/menu');
            $this->load->view('followup', $data);
            $this->load->view('templates/footer');
        }
    }
    
	function dismiss_followup($followup_id) {
        //session_start();
		//Check if user has logged in 
		//if (!isset($_SESSION["user_name"]) || $_SESSION["user_name"] == '') {
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {
            redirect('login/index/');
        } else {
			$followup = $this->patient_model->get_patient_from_followup($followup_id);
			$patient_id = $followup['patient_id'];
			//echo $patient_id."<br/>";
            $this->patient_model->remove_followup($followup_id);
            $this->followup($patient_id);
        }
    }
    
	function change_followup_date() {
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {
            redirect('login/index/');
        } else {
			$this->form_validation->set_rules('doctor_id', 'Doctor Name', 'required');
			$this->form_validation->set_rules('followup_date', 'Follow up Date', 'required');
			if ($this->form_validation->run() === FALSE) {
				$patient_id = $this->input->post('patient_id');
				$this->followup($patient_id);
			}else{
				$patient_id = $this->input->post('patient_id');
				$doctor_id = $this->input->post('doctor_id');
				$this->patient_model->change_followup_detail($patient_id,$doctor_id);
				redirect('appointment/index');
			}
        }
    }
    function edit_followup($followup_id){
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {
            redirect('login/index/');
        } else {
			$patient = $this->patient_model->get_patient_from_followup($followup_id);
            $patient_id = $patient['patient_id'];
			$data['patient_id'] = $patient_id;
			$data['followups'] = $this->patient_model->get_followups_patient($patient_id);
			$data['patient'] = $this->patient_model->get_patient_detail($patient_id);
			$data['followup'] = $this->patient_model->get_followup($followup_id);
			$data['def_dateformate'] = $this->settings_model->get_date_formate();
			$data['def_timeformate'] = $this->settings_model->get_time_formate();
			if($this->session->userdata('category') == 'Doctor'){
				$id = $this->session->userdata('id');
				$data['doctor']=$this->admin_model->get_doctor($id);
			}else{
				$data['doctors'] = $this->admin_model->get_doctor();
			}
            $this->load->view('templates/header');
            $this->load->view('templates/menu');
            $this->load->view('followup', $data);
            $this->load->view('templates/footer');
        }
	}
	function new_inquiry_report() {
		//session_start();
		//Check if user has logged in 
		//if (!isset($_SESSION["user_name"]) || $_SESSION["user_name"] == '') {
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {
			redirect('login/index/');
		} else {
			$data['patients_detail'] = $this->patient_model->new_inquiries();
			$this->load->view('templates/header');
			$this->load->view('templates/menu');
			$this->load->view('new_inquiries', $data);
			$this->load->view('templates/footer');
		}
    }

	function email_bill($visit_id,$patient_id = NULL){
		$active_modules = $this->module_model->get_active_modules();
		if (in_array("alert", $active_modules)) {				
			//Send Alert : bill
			redirect('alert/send/new_bill/0/0/0/'.$visit_id.'/0/patient/bill/'.$visit_id.'/'.$patient_id.'/0');
		}else{
			$this->bill($visit_id,$patient_id);
		}
	}
}
?>