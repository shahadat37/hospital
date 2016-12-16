<?php  
	if(isset($payment)){
		$payment_cheque_no = $payment->cheque_no;
		$payment_pay_amount = $payment->pay_amount;
		$payment_pay_mode = $payment->pay_mode;
	} else {
		$payment_cheque_no = "";
		$payment_pay_amount = 0;
		$payment_pay_mode = "";
	}
	if(isset($patient)){
		$patient_first_name = $patient['first_name'];
		$patient_middle_name = $patient['middle_name'];
		$patient_last_name = $patient['last_name'];
		$patient_name = $patient_first_name . " " . $patient_middle_name . " " . $patient_last_name;
	}else{
		$patient_name = " " ;	
	}
	$ttl_due_amount = 0;
?> 
<script>
	$(window).load(function(){

		<?php if(isset($patient_id) && $patient_id !=0 && !isset($payment)){ ?>
				var this_patient_id = <?=$patient_id;?>;
				var billArray = [
				<?php 
				$total_due_amount = 0;
				foreach($bills as $bill){
					$total_due_amount = $total_due_amount + $bill['due_amount'];	
					$bill_due_amount = currency_format($bill['due_amount']);
					if($currency_postfix) $bill_due_amount = $bill_due_amount . $currency_postfix['currency_postfix'];	
					echo '["'.$bill['bill_id'].'", "'.$bill['patient_id'].'","'. $bill_due_amount.'","'. $bill['due_amount'].'"],';
				}
				$total_due_amount = currency_format($total_due_amount );
				if($currency_postfix) $total_due_amount = $total_due_amount . $currency_postfix['currency_postfix'];	
				?>
				];
				var total_due_amount = 0;
				$("#bill_detail").empty();
				$("#bill_detail_footer").empty();
				$.each(billArray, function(i,val) {
					$.each(val, function(index,value) {
						if(index == 0){	//bill id
							bill_id = value;
						}
						if(index == 1){	//patient id
							patient_id = value;
						}
						if(index == 2){	//due amount string
							due_amount = value;
						}
						if(index == 3){	//due amount string
							due_amount_val = value;
						}
						
					})
					if(this_patient_id == patient_id){
						$("#bill_detail").append("<tr><td><a href='<?=site_url('patient/edit_bill/');?>/"+bill_id+"' class='btn btn-primary btn-sm square-btn-adjust'>"+bill_id+"</a><input type='hidden' name='bill_id[]' value='"+bill_id+"'/></td><td style='text-align:right;'>"+due_amount+"</td><td class='adjust_amoount' style='text-align:right;' amount='"+due_amount_val+"'></td></tr>");
						total_due_amount = parseInt(total_due_amount) + parseInt(due_amount_val);
					}
				});
				$("#bill_detail_footer").append("<tr><th>Total</th><th style='text-align:right;'>"+total_due_amount+"<input type='hidden' name='total_due_amount' value='"+total_due_amount+"'/></th><th style='text-align:right;' id='total_payment_amount'></th></tr>");
		<?php }else{ ?>
		var searcharrpatient=[<?php $i = 0;
		foreach ($patients as $p) {
			if ($i > 0) { echo ",";}
			echo '{value:"' . $p['first_name'] . " " . $p['middle_name'] . " " . $p['last_name'] . '",id:"' . $p['patient_id'] . '",display:"' . $p['display_id'] . '",num:"' . $p['phone_number'] . '"}';
			$i++;
		}?>];
		$("#patient_name").autocomplete({
			autoFocus: true,
			source: searcharrpatient,
			minLength: 1,//search after one characters
			
			select: function(event,ui){
				//do something
				$("#patient_id").val(ui.item ? ui.item.id : '');
				var this_patient_id = ui.item.id;
				var billArray = [
				<?php 
				$total_due_amount = 0;
				foreach($bills as $bill){
					
					$total_due_amount = $total_due_amount + $bill['due_amount'];	
					$bill_due_amount = currency_format($bill['due_amount']);
					if($currency_postfix) $bill_due_amount = $bill_due_amount . $currency_postfix['currency_postfix'];	
					echo '["'.$bill['bill_id'].'", "'.$bill['patient_id'].'","'. $bill_due_amount.'","'. $bill['due_amount'].'"],';
				}
				//$ttl_due_amount = $total_due_amount;
				//$total_due_amount = currency_format($total_due_amount );
				//if($currency_postfix) $total_due_amount = $total_due_amount . $currency_postfix['currency_postfix'];	
				?>
				];
				var bill_id;
				var patient_id;
				var due_amount;
				var total_due_amount = 0;
				var total_due_after_payment = 0;
				$("#bill_detail").empty();
				$("#bill_detail_footer").empty();
				//total_due_amount = '<?=$total_due_amount;?>';
				//ttl_due_amount = '<?=$ttl_due_amount;?>';
				$.each(billArray, function(i,val) {
					$.each(val, function(index,value) {
						if(index == 0){	//bill id
							bill_id = value;
						}
						if(index == 1){	//patient id
							patient_id = value;
						}
						if(index == 2){	//due amount string
							due_amount = value;
						}
						if(index == 3){	//due amount string
							due_amount_val = value;
						}
						
					})
					if(this_patient_id == patient_id){
						$("#bill_detail").append("<tr><td><a href='<?=site_url('patient/edit_bill/');?>/"+bill_id+"' class='btn btn-primary btn-sm square-btn-adjust'>"+bill_id+"</a><input type='hidden' name='bill_id[]' value='"+bill_id+"'/></td><td style='text-align:right;'>"+due_amount+"</td><td style='text-align:right;' class='adjust_amoount' amount='"+due_amount_val+"'></td></tr>");
						total_due_amount = parseInt(total_due_amount) + parseInt(due_amount_val);
					}
				});
				$("#bill_detail_footer").append("<tr><th>Total</th><th style='text-align:right;'>"+total_due_amount+"<input type='hidden' name='total_due_amount' value='"+total_due_amount+"'/></th><th style='text-align:right;' id='total_payment_amount'></th></tr>");
				$("#bill_detail_footer").append("<tr><th colspan='2'>Total Due After Payment</th><th style='text-align:right;' id='total_due_after_payment'>"+total_due_after_payment+"</th></tr>");
			},
			
			change: function(event, ui) {
				 if (ui.item == null) {
					$("#patient_id").val('');
					$("#patient_name").val('');
					}
			},
			response: function(event, ui) {
				if (ui.content.length === 0) 
				{
					$("#patient_id").val('');
					$("#patient_name").val('');
				}
			}
		});
		<?php } ?>
		$("#payment_amount").change(function() {
			
			var due_amount;
			var payment_amount;
			var adjust_amount;
			var total_due_after_payment = 0;
			payment_amount = parseFloat($("#payment_amount").val());
			$('#total_payment_amount').html(payment_amount);
			$('.adjust_amoount').each(function(){
				adjust_amount = 0;
				due_amount = parseFloat($(this).attr('amount'));
				if(due_amount <= payment_amount && payment_amount > 0){
					adjust_amount = due_amount;
					payment_amount = payment_amount - due_amount;
					
				}else{
					if(due_amount > payment_amount && payment_amount > 0){
						adjust_amount = payment_amount;
						payment_amount = 0;
						total_due_after_payment = total_due_after_payment + due_amount - adjust_amount;
					}	
				}
				
				$(this).html(adjust_amount + '<input type="hidden" name="adjust_amount[]" value="'+adjust_amount+'" />');
			});
			$('#total_due_after_payment').html(total_due_after_payment);
		});	
		$('#payment_date').datetimepicker({
			timepicker:false,
			format: '<?=$def_dateformate;?>',
			scrollInput:false, 
			scrollMonth:false,
			scrollTime:false,
		}); 
		$( "#pay_mode" ).change(function() {
			if($( "#pay_mode" ).val() == 'cheque'){
				$( "#cheque_number" ).parent().parent().show();
				$( "#paid_cash" ).parent().parent().hide();
				$( "#return_change" ).parent().parent().hide();
			}else{
				$( "#cheque_number" ).parent().parent().hide();
				$( "#paid_cash" ).parent().parent().show();
				$( "#return_change" ).parent().parent().show();
			}
		});
		<?php if ($payment_pay_mode !='cheque') { ?>
		$( "#cheque_number" ).parent().parent().hide();
		<?php } ?>
		
		$( "#paid_cash" ).change(function() {
			var paid_cash = $( "#paid_cash" ).val();
			var payment_amount = $( "#payment_amount" ).val();
			var return_change = paid_cash - payment_amount;
			$( "#return_change" ).val(return_change);
		});
		
	});
</script>
<div id="page-inner">
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-primary">
			<div class="panel-heading">
				Payment Form
			</div>
			<div class="panel-body">
			<?php  if(!isset($payment)){ ?> 
			<?php echo form_open('payment/insert/'.$patient_id.'/'.$called_from) ?>
			<?php  }else{ ?> 
			<?php echo form_open('payment/edit/'.$payment_id.'/'.$called_from) ?>
			<?php  } ?> 
			<?php 
				if(isset($payment)){
					$payment_date = date($def_dateformate,strtotime($payment->pay_date)); 
				}else{
					$payment_date = date($def_dateformate); 
				}
			?>
			<input type="hidden" name="payment_type" value="bill_payment" />
			<div class="col-md-12">
				<label for="patient_name"><?php echo $this->lang->line('patient') . ' ' . $this->lang->line('name');?></label>
				<?php if(isset($payment)){ //Edit Mode ?>
					<input type="hidden" name="patient_id" id="patient_id" value="<?= $patient_id; ?>" />
					<input name="patient_name" id="patient_name" type="text" disabled="disabled" class="form-control" value="<?= $patient_name;?>"/><br />
					<?php echo form_error('patient_id','<div class="alert alert-danger">','</div>'); ?>
				<?php }else{ //Insert Mode  ?>
					
					<?php if(isset($patient)){ ?>
					<input name="patient_name" id="patient_name" type="text" disabled="disabled" class="form-control" value="<?= $patient_name;?>"/><br />
					<?php }else{ ?>
					<input name="patient_name" id="patient_name" type="text" class="form-control" value=""/><br />
					<?php } ?>
					<input type="hidden" name="patient_id" id="patient_id" value="<?= $patient_id; ?>" />
					<?php echo form_error('patient_id','<div class="alert alert-danger">','</div>'); ?>
				<?php } ?>
			</div>
			<div class="col-md-12">
				<div class="table-responsive">
					<table class="table table-striped table-bordered table-hover" id="bill_table">
					<thead>
						<tr>
							<th>Bill No</th>
							<th style="text-align:right;">Due Amount</th>
							<th style="text-align:right;">Payment Adjustment</th>
						</tr>
					</thead>
					<?php if(isset($payment)){ //Edit Mode ?>
					<tbody id="bill_detail">
						<?php 
							$total_due_amount = 0;
							$total_adjust_amount = 0;
							foreach($adjusted_bills as $bill){ ?>
								<tr>
								<td>
									<a href="<?=site_url('patient/edit_bill/'.$bill['bill_id']);?>" class="btn btn-primary btn-sm square-btn-adjust"><?=$bill['bill_id'];?></a>
									<input type='hidden' name='bill_id[]' value='<?=$bill['bill_id'];?>'/>
								</td>
								<?php 
								foreach($bills as $patient_bill){
									if($patient_bill['bill_id'] == $bill['bill_id']){
										$due_amount = $patient_bill['due_amount'] + $bill['adjust_amount'];
										$amount = $due_amount;
										$total_due_amount = $total_due_amount + $due_amount;
										$due_amount = currency_format($due_amount);
										if($currency_postfix) $due_amount = $due_amount . $currency_postfix['currency_postfix'];	
									}
								}
								?>
								<td style="text-align:right;"><?=$due_amount;?></td>
								<?php 
								$adjust_amount = currency_format($bill['adjust_amount']);
								$total_adjust_amount = $total_adjust_amount + $bill['adjust_amount'];
								if($currency_postfix) $adjust_amount = $adjust_amount . $currency_postfix['currency_postfix'];	
								
								?>
								<td style="text-align:right;" class="adjust_amoount" amount="<?=$amount;?>"><?=$adjust_amount;?></td>
								</tr>
						<?php } 
								$total_due_after_payment = $total_due_amount - $total_adjust_amount;
								$total_due_after_payment = currency_format($total_due_after_payment);
								if($currency_postfix) $total_due_after_payment = $total_due_after_payment . $currency_postfix['currency_postfix'];	
								$ttl_due_amount = $total_due_amount;
								$total_due_amount = currency_format($total_due_amount);
								if($currency_postfix) $total_due_amount = $total_due_amount . $currency_postfix['currency_postfix'];	
								$total_adjust_amount = currency_format($total_adjust_amount);
								if($currency_postfix) $total_adjust_amount = $total_adjust_amount . $currency_postfix['currency_postfix'];	
						?>
					</tbody>
					<tfoot id="bill_detail_footer">
						<tr>
							<th>Total</th>
							<th style="text-align:right;"><?=$total_due_amount;?><input type="hidden" name="total_due_amount" value="<?=$ttl_due_amount;?>"/></th>
							<th style="text-align:right;" id="total_payment_amount"><?=$total_adjust_amount;?></th>
						</tr>
						<tr>
							<th colspan="2">Total Due After Payment</th>
							<th style="text-align:right;" id="total_due_after_payment"><?=$total_due_after_payment?></th>
						</tr>
					</tfoot>
					</table>
					<?php }else{ //Insert Mode  ?>
						<tbody id="bill_detail">
						</tbody>
						<tfoot id="bill_detail_footer">
						</tfoot>
						</table>
					<?php } ?>
				</div>
			</div>
			<div class="col-md-12">
				<div class="form-group">
					<label for="title"><?php echo $this->lang->line('payment_amount');?></label>        
					<input type="text" name="payment_amount" id="payment_amount" class="form-control" value="<?=$payment_pay_amount;?>" />
					<?php echo form_error('payment_amount','<div class="alert alert-danger">','</div>'); ?>
				</div>
			</div>
			<div class="col-md-12">
				<div class="form-group">
					<label for="title"><?php echo $this->lang->line('payment_date');?></label>        
					<input type="text" name="payment_date" id="payment_date" class="form-control" value="<?=$payment_date;?>" />
					<?php echo form_error('payment_date','<div class="alert alert-danger">','</div>'); ?>
				</div>
			</div>
			<div class="col-md-12">
				<div class="form-group">
					<label for="title"><?php echo $this->lang->line('payment_mode');?></label>        
					<select name="pay_mode" id="pay_mode" class="form-control">
						<option value="cash" <?php if ($payment_pay_mode =='cash') {echo "selected";} ?>>Cash</option>
						<option value="cheque" <?php if ($payment_pay_mode =='cheque') {echo "selected";} ?>>Cheque</option>
					</select>
				</div>
			</div>
			<div class="col-md-12">
				<div class="form-group">
					<label for="title"><?php echo $this->lang->line('cheque_number');?></label>        
					<input type="text" name="cheque_number" id="cheque_number" class="form-control" value="<?=$payment_cheque_no;?>" />
					<?php echo form_error('cheque_number','<div class="alert alert-danger">','</div>'); ?>
				</div>	
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label for="title">Paid Cash</label>        
					
					<input type="text" name="paid_cash" id="paid_cash" class="form-control" value="" />
					<small>*These fields are for calculation purpose only. Add actual payment amount in Payment Amount field.</small>
				</div>	
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label for="title">Return Change</label>        
					<input type="text" name="return_change" id="return_change" readonly="readonly" class="form-control" value="" />
				</div>	
			</div>
			<div class="col-md-12">
				<div class="form-group">
					<?php  if(!isset($payment)){ ?> 
					<input class="btn btn-primary" type="submit" value="Add Payment" name="submit" />
					<?php }else{ ?> 
					<input class="btn btn-primary" type="submit" value="Update Payment" name="submit" />
					<?php } ?> 
				</div>
			</div>
			<?php 
				if(!isset($payment)){
			?> 
			<div class="col-md-12">
				<div class="form-group">
					<a href="<?=site_url("appointment/index"); ?>" class="btn btn-primary" ><?php echo $this->lang->line('back_to_app');?></a>
				</div>
			</div>
			<?php 
				}
			?> 
			<?php echo form_close(); ?>
			</div>
			</div>
		</div>
	</div>
</div>			
