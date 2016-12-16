<?php

class Payment_model extends CI_Model {

    public function __construct() {
        $this->load->database();
    }

    public function list_payments() {
        $this->db->order_by("pay_date", "desc");
        $query = $this->db->get('view_payment');
        return $query->result_array();
    }
	public function get_payments() {
        $this->db->order_by("pay_date", "desc");
        $query = $this->db->get('payment');
        return $query->result_array();
    }
	public function get_bill_payment_r() {
        $query = $this->db->get('bill_payment_r');
        return $query->result_array();
    }
	public function get_bills_for_payment($payment_id) {
		$this->db->where('payment_id', $payment_id);
        $query = $this->db->get('bill_payment_r');
        return $query->result_array();
    }
	public function get_payments_for_bill($bill_id) {
		$this->db->where('bill_id', $bill_id);
        $query = $this->db->get('bill_payment_r');
        return $query->result_array();
    }
	function insert_payment() {
		$data = array();
		$data_r = array();
		
		$pay_amount = $this->input->post('payment_amount');
		
		$data['pay_amount'] = $pay_amount;
		$data['pay_date'] = date('Y-m-d',strtotime($this->input->post('payment_date')));
		$data['pay_mode'] = $this->input->post('pay_mode');
		$data['cheque_no'] = $this->input->post('cheque_number');
		$data['patient_id'] = $this->input->post('patient_id');
		$this->db->insert('payment', $data);
		//echo $this->db->last_query();
		$payment_id = $this->db->insert_id();
		
		/** Multiple Bill Adjustment */
		if($this->input->post('bill_id')){
			$bill_array = $this->input->post('bill_id');
			$adjust_amount = $this->input->post('adjust_amount');
			
			$i=0;
			foreach($bill_array as $bill){
				$bill_id = $bill;
				$bill_adjust_amount = $adjust_amount[$i];
				
				$data_r['bill_id'] = $bill_id;
				$data_r['payment_id'] = $payment_id;
				$data_r['adjust_amount'] = $bill_adjust_amount;
				$this->db->insert('bill_payment_r', $data_r);
				//echo $this->db->last_query();
				
				$this->db->set('due_amount', "`due_amount`-$bill_adjust_amount", FALSE);
				$this->db->where('bill_id', $bill_id);
				$this->db->update('bill');
				//echo $this->db->last_query();
				
				$i++;
			}
		}
		return 	$payment_id;
    }
	function get_paid_amount($bill_id){
		$payments =  $this->get_payments_for_bill($bill_id);
		$total_payment = 0;
		foreach($payments as $payment){
			$payment_id = $payment['payment_id'];
			$adjust_amount = $this->get_adjustment_amount($bill_id,$payment_id);
			$total_payment = $total_payment + $adjust_amount;
		}
		return $total_payment;
	}
	function get_payment($payment_id){
		$query = $this->db->get_where('payment', array('payment_id' => $payment_id));
        return $query->row_array();
	}
	function edit_payment($payment_id){
		//Get previous details
		
		$bill_array = $this->input->post('bill_id');
		$adjust_amount = $this->input->post('adjust_amount');
		$i=0;
		foreach($bill_array as $bill){
			$bill_id = $bill;
			$bill_adjust_amount = $adjust_amount[$i];
			
			//Previous Adjustment Amount
			$previous_adjust_amount = $this->get_adjustment_amount($bill_id,$payment_id);
			
			//Update Bill Payment Relation
			$data_r['adjust_amount'] = $bill_adjust_amount;
			$this->db->where('payment_id', $payment_id);
			$this->db->where('bill_id', $bill_id);
			$this->db->update('bill_payment_r', $data_r);
			//echo $this->db->last_query();
			
			//Update Bill
			$this->db->set('due_amount', "`due_amount`+$previous_adjust_amount-$bill_adjust_amount", FALSE);
			$this->db->where('bill_id', $bill_id);
			$this->db->update('bill');
			//echo $this->db->last_query();
			
			$i++;
		}
		
		//Update Payment
		$data['pay_amount'] = $this->input->post('payment_amount');
		$pay_amount = $data['pay_amount'];
		$data['pay_mode'] = $this->input->post('pay_mode');
		$data['pay_date'] = date('Y-m-d',strtotime($this->input->post('payment_date')));
		$data['cheque_no'] = $this->input->post('cheque_number');
		$this->db->where('payment_id', $payment_id);
		$this->db->update('payment', $data);
		
	}
	function get_adjustment_amount($bill_id,$payment_id){
		$this->db->where('payment_id', $payment_id);
		$this->db->where('bill_id', $bill_id);
        $query = $this->db->get('bill_payment_r');
        $row = $query->row_array();
		$previous_adjust_amount = $row['adjust_amount'];
		return $previous_adjust_amount;
	}
	function delete_payment($payment_id){
		//Adjust Bills
		$related_bills = $this->get_bills_for_payment($payment_id);
		foreach($related_bills as $bill){
			$bill_id = $bill['bill_id'];
			$bill_adjust_amount = $bill['adjust_amount'];
			$this->db->set('due_amount', "`due_amount`+$bill_adjust_amount", FALSE);
			$this->db->where('bill_id', $bill_id);
			$this->db->update('bill');	
		}
		//Delete Bill Payment Relation
		$this->db->delete('bill_payment_r', array('payment_id' => $payment_id)); 
		//Delete Payment
		$this->db->delete('payment', array('payment_id' => $payment_id)); 
	}
}
?>