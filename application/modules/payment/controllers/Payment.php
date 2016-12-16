<?php

class Payment extends CI_Controller {

    function __construct() {
        parent::__construct();
		$this->load->model('payment_model');
		$this->load->model('patient/patient_model');
		$this->load->model('settings/settings_model');
		$this->load->model('menu_model');
		
		$this->load->helper('form');
		$this->load->helper('currency');
		$this->load->helper('url');
		
		$this->load->library('form_validation');
		$this->load->library('session');
		
		$this->lang->load('main');
    }
    public function index() {
		//Check if user has logged in 
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {
			redirect('login/index');
        } else {
			$data['payments'] = $this->payment_model->list_payments();
			
			$data['currency_postfix'] = $this->settings_model->get_currency_postfix();
			$this->load->view('templates/header');
			$this->load->view('templates/menu');
			$this->load->view('browse',$data);
			$this->load->view('templates/footer');
        }
    }
	public function insert($patient_id,$called_from = 'bill') {
		//Check if user has logged in 
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {
            redirect('login/index');
        } else {
			$total_due_amount = $this->input->post('total_due_amount')+1;
			$this->form_validation->set_rules('patient_id', 'Patient', 'required');
			$this->form_validation->set_rules('payment_amount', 'Payment Amount', "required|less_than[$total_due_amount]|greater_than[0]");
			
			if ($this->form_validation->run() === FALSE) {
				$data['patients'] = $this->patient_model->get_patient();
				$data['bills'] = $this->patient_model->get_pending_bills();
				$data['currency_postfix'] = $this->settings_model->get_currency_postfix();
				$data['patient_id'] =$patient_id;
				$data['patient'] = $this->patient_model->get_patient_detail($patient_id); 
				$data['called_from'] = $called_from;
				$data['def_dateformate'] = $this->settings_model->get_date_formate();
				$this->load->view('templates/header');
				$this->load->view('templates/menu');
				$this->load->view('form',$data);
				$this->load->view('templates/footer');
			}else{
				$payment_id = $this->payment_model->insert_payment();
				$patient_id = $this->input->post('patient_id');
				if($called_from == 'bill'){
					redirect('patient/visit/'.$patient_id);
					//redirect("alert/send/payment_received/$patient_id/0/0/0/$payment_id/patient/visit/$patient_id/0/0");
				//}else{
					//$this->index();
					
				}
			}
        }
    }
	
	public function edit($payment_id,$called_from='payment'){
		//Check if user has logged in 
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {
            redirect('login/index');
        } else {
			$total_due_amount = $this->input->post('total_due_amount')+1;
			$this->form_validation->set_rules('patient_id', 'Patient', 'required');
			$this->form_validation->set_rules('payment_amount', 'Payment Amount', "required|less_than[$total_due_amount]");
			
			if ($this->form_validation->run() === FALSE) {
				$data = array();
				$payment = $this->payment_model->get_payment($payment_id);
				$data['payment'] = $payment;
				$patient_id = $payment->patient_id;
				$data['payment_id'] = $payment->payment_id;
				$data['patient_id'] = $patient_id;
				$data['patient'] = $this->patient_model->get_patient_detail($patient_id); 
				$data['called_from'] = $called_from;
				$data['def_dateformate'] = $this->settings_model->get_date_formate();
				$data['adjusted_bills'] = $this->payment_model->get_bills_for_payment($payment_id);
				$data['bills'] = $this->patient_model->get_patient_bills($patient_id);
				$data['currency_postfix'] = $this->settings_model->get_currency_postfix();
				$data['patients'] = $this->patient_model->get_patient();
				/*
				$bill_id = $payment->bill_id;
				$data['bill_id'] = $bill_id;
				$data['due_amount'] = $this->patient_model->get_due_amount($bill_id);
				$patient_id = $this->patient_model->get_patient_id_from_bill_id($bill_id);
				$data['bills'] = $this->patient_model->get_pending_bills();
				*/
				$this->load->view('templates/header');
				$this->load->view('templates/menu');
				$this->load->view('form',$data);
				$this->load->view('templates/footer');
			}else{
				$this->payment_model->edit_payment($payment_id);
				$this->index();
			}
			
		}
	}
	public function delete ($payment_id ,$called_from='payment'){
		if (!$this->session->userdata('user_name') || $this->session->userdata('user_name') == '') {
            redirect('login/index');
        } else {
			$this->payment_model->delete_payment($payment_id);
			$this->index();
		}
	}
}
?>
