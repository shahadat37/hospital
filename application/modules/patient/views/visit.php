<script type="text/javascript" charset="utf-8">
    $(window).load(function() {
		$(".expand-collapse-header").click(function () {
			if($(this).find("i").hasClass("fa-arrow-circle-down"))
			{
				$(this).find("i").removeClass("fa-arrow-circle-down");
				$(this).find("i").addClass("fa-arrow-circle-up");
			}else{
				$(this).find("i").removeClass("fa-arrow-circle-up");
				$(this).find("i").addClass("fa-arrow-circle-down");
			}
			
			$content = $(this).next('.expand-collapse-content');
			$content.slideToggle(500);

		});
		$('#visit_date').datetimepicker({
			timepicker:false,
			format: '<?=$def_dateformate; ?>',
			scrollInput:false, 
			scrollMonth:false,
			scrollTime:false,
		}); 
		$('#visit_time').datetimepicker({
			datepicker:false,
			format: '<?=$def_timeformate; ?>',
			formatTime:'<?=$def_timeformate; ?>',
			scrollInput:false, 
			scrollMonth:false,
			scrollTime:false,
		}); 
		$('#followup_date').datetimepicker({
			timepicker:false,
			format: '<?=$def_dateformate; ?>',
			scrollInput:false, 
			scrollMonth:false,
			scrollTime:false,
		});
		$('#visit_date').change(function() {
			var startdate = $('#visit_date').val();
			$('#followup_date').val(moment(startdate, '<?=$morris_date_format; ?>').add(<?=$clinic_settings['next_followup_days'];?>, 'day').format('<?=$morris_date_format; ?>'));
		});
		
    });
</script>
<style>
	.collapsed{
		display:none;
	}
</style>

<?php $bal_amount=0;
$total_amount=0;
 ?>
<div id="page-inner">
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-primary">
				<div class="panel-heading expand-collapse-header"><i class="fa fa-arrow-circle-down"></i>
					Patient Details (Click to toggle display)
				</div>
				<div class="panel-body expand-collapse-content collapsed">
					<div class="col-md-9">
						<div class="col-md-4">
							<div class="form-group">
								<label><?php echo $this->lang->line('id');?> :</label>
								<span><?php echo $patient['display_id']; ?></span>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label><?php echo $this->lang->line('name');?> :</label>
								<span><?php echo $patient['first_name'] . " " . $patient['middle_name'] . " " . $patient['last_name']; ?></span>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label><?php echo $this->lang->line('display_name');?>:</label>
								<span><?php echo $patient['display_name']; ?></span>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label><?php echo $this->lang->line('reference_by');?> :</label>
								<span><?php echo $patient['reference_by']; ?></span>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label><?php echo $this->lang->line('dob');?> :</label>
								<?php if($patient['dob'] != NULL) { ?>
								<span><?php echo date($def_dateformate,strtotime($patient['dob'])); ?></span>
								<?php } ?>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label><?php echo $this->lang->line('gender');?> :</label>
								<span><?= $patient['gender']; ?></span>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label><?php echo $this->lang->line('mobile');?> :</label>
								<span><?= $patient['phone_number']; ?></span>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label><?php echo $this->lang->line('email');?> :</label>
								<span><?= $addresses['email']; ?></span>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label style="display:table-cell;"><?php echo $this->lang->line('address');?> :</label>
								<span><strong>(<?=$addresses['type']; ?>)</strong><br/>
									   <?=$addresses['address_line_1'];?><br/>
									   <?=$addresses['address_line_2'];?><br/>
									   <?=$addresses['city'] . "," . $addresses['state'] . "," . $addresses['postal_code'] . "," . $addresses['country']; ?>
								</span>
							</div>
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group">
							<?php if(isset($addresses['contact_image']) && $addresses['contact_image'] != ""){ ?>
								<img src="<?php echo base_url() . $addresses['contact_image']; ?>" height="150" width="150"/>	
							<?php }else{ ?>
								<img src="<?php echo base_url() . "images/Profile.png" ?>" height="150" width="150"/>	
							<?php } ?>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<a class="btn btn-primary" title="Edit" href="<?php echo site_url("patient/edit/" . $patient['patient_id']."/visit"); ?>">Edit</a>
						</div>
					</div>
				</div>
			</div>
			<div class="panel panel-primary">
				<div class="panel-heading expand-collapse-header">
					<i class="fa fa-arrow-circle-up"></i>
					<?php echo $this->lang->line('new')." ".$this->lang->line('visit'). " " . $this->lang->line('toggle_display');?>
				</div>
				<div class="panel-body expand-collapse-content">
					<?php echo form_open('patient/visit/' . $patient_id); ?>
					<div class="col-md-12">	
						<input type="hidden" name="patient_id" value="<?= $patient_id; ?>"/>
						<div class="col-md-12">
							<div class="col-md-4">
								<div class="form-group">
									<label for="visit_doctor"><?=$this->lang->line('doctor');?></label>
									<?php 
										$level = $this->session->userdata('category');
										if ($this->session->userdata('category') == 'Doctor') {
											$userid = $this->session->userdata('id');
											$doctor_name = $doctors['name'];
											?><input type="text" name="doctor_name" class="form-control" readonly="readonly" value="<?=$doctor_name;?>"/>
											<input type="hidden" name="doctor" value="<?=$userid;?>"/><?php
										}else{
											$userid = 0;
											?>
											<select name="doctor" class="form-control">
												<option></option>
												<?php foreach ($doctors as $doctor) { ?>
												<option value="<?php echo $doctor['userid'] ?>" <?php if($appointment_doctor == $doctor['userid']) echo "selected";?>><?= $doctor['name']; ?></option>
												<?php }	?>
											</select>
										<?php } ?>
										<?php echo form_error('doctor','<div class="alert alert-danger">','</div>'); ?>
								</div>
							</div>
						</div>
						<div class="col-md-12">	
							<div class="col-md-4">	
								<div class="form-group">
									<label for="visit_date"><?=$this->lang->line('visit')." ".$this->lang->line('date');?></label>
									<input type="text" name="visit_date" id="visit_date" value="<?php echo $curr_date; ?>" class="form-control"/>
									<?php echo form_error('visit_date','<div class="alert alert-danger">','</div>'); ?>
								</div>
							</div>
							<div class="col-md-4">	
								<div class="form-group">
									<label for="visit_time"><?php echo $this->lang->line('time');?></label>	
									<input type="text" name="visit_time" id="visit_time" value="<?php echo $curr_time; ?>" class="form-control"/>
									<?php echo form_error('visit_time','<div class="alert alert-danger">','</div>'); ?>
								</div>
							</div>
						</div>
						<div class="col-md-12">	
							<div class="col-md-4">	
								<div class="form-group">
									<label for="type"><?php echo $this->lang->line('type');?></label> 
									<select name="type" class="form-control">
										<option value="New Visit"><?php echo $this->lang->line('new')." ".$this->lang->line('visit');?></option>
										<option <?php if ($visits) {echo 'selected = "selected"';} ?> value="Established Patient"><?php echo $this->lang->line('established_patient');?></option>
									</select>
								</div>
							</div>
							<div class="col-md-8">
								<div class="form-group">
									<label for="appointment_reason">Reason</label>
									<input type="text" name="appointment_reason" id="appointment_reason" value="<?=$appointment_reason;?>" class="form-control"/>
									<?php echo form_error('reason','<div class="alert alert-danger">','</div>'); ?>
								</div>
							</div>
						</div>
						
						<div class="col-md-12">	
							<div class="col-md-12">	
								<div class="form-group">
									<label for="notes"><?php echo $this->lang->line('notes');?></label> 
									<textarea rows="4" cols="100" class="form-control" name="notes"></textarea>
									<?php echo form_error('notes','<div class="alert alert-danger">','</div>'); ?>
								</div>
							</div>
						</div>
						<div class="col-md-12">	
							<div class="col-md-12">	
								<div class="form-group">
									<label for="patient_notes">Notes For Patient</label> 
									<textarea rows="4" cols="100" class="form-control" name="patient_notes"></textarea>
									<?php echo form_error('patient_notes','<div class="alert alert-danger">','</div>'); ?>
								</div>
							</div>
						</div>
						<?php if (in_array("treatment", $active_modules)) { ?>
						<div class="col-md-12">	
							<div class="col-md-4">	
								<label for="visit_treatment" style="display:block;text-align:left;"><?php echo $this->lang->line('treatment');?></label>
								<select id="treatment" class="form-control" multiple="multiple" style="width:350px;" tabindex="4" name="treatment[]">
									<?php foreach ($treatments as $treatment) { ?>
										<option value="<?php echo $treatment['id'] . "/" . $treatment['treatment'] . "/" . $treatment['price'] ?>"><?= $treatment['treatment']; ?></option>
									<?php } ?>
								</select>
								<script>jQuery('#treatment').chosen();</script>
							</div>
						</div>
						<?php } ?>
						<?php if (in_array("prescription", $active_modules)) { ?>
						
						<script>
									$(window).load(function() {    
										var medicine_array = [<?php
											$i=0;
											foreach ($medicines as $medicine){
												if ($i>0) {echo ",";}
												echo '{value:"' . $medicine['medicine_name'] . '",id:"' . $medicine['medicine_id'] . '"}';
												$i++;
											}
										?>];
										$("#medicine_name").autocomplete({
											source: medicine_array,
											minLength: 1,//search after one characters
											select: function(event,ui){
												//do something
												$("#medicine_id").val(ui.item ? ui.item.id : '');

											},
											change: function(event, ui) {
												 if (ui.item == null) {
													$("#medicine_name").val('');
													}
											},
										});
										$( "#add_medicine" ).click(function() {
											var medicine_count = parseInt( $( "#medicine_count" ).val());
											medicine_count = medicine_count + 1;
											$( "#medicine_count" ).val(medicine_count);
											
											var medicine = "<div><div class='col-md-2'><label for='medicine' style='display:block;text-align:left;'>Medicine</label><input type='text' name='medicine_name[]' id='medicine_name"+medicine_count+"' class='form-control'/><input type='hidden' name='medicine_id[]' id='medicine_id"+medicine_count+"' class='form-control'/></div>";
											medicine += "<div class='col-md-6'><label for='frequency' style='display:block;text-align:left;'>Frequency</label><div class='col-md-1'>M</div><div class='col-md-3'><input type='text' name='freq_morning[]' id='freq_morning' class='form-control'/></div><div class='col-md-1'>A</div><div class='col-md-3'><input type='text' name='freq_afternoon[]' id='freq_afternoon' class='form-control'/></div><div class='col-md-1'>N</div><div class='col-md-3'><input type='text' name='freq_evening[]' id='freq_evening' class='form-control'/></div></div>";
											medicine += "<div class='col-md-1'><label for='days' style='display:block;text-align:left;'>Days</label><input type='text' name='days[]' id='days' class='form-control'/></div>";
											medicine += "<div class='col-md-2'><label for='prescription_notes' style='display:block;text-align:left;'>Instructions</label><input type='text' name='prescription_notes[]' id='prescription_notes' class='form-control'/></div>";
											medicine += "<div class='col-md-1'><label></label><a href='#' id='delete_medicine"+medicine_count+"' class='btn btn-danger btn-sm square-btn-adjust'>Delete</a></div></div>";
											$( "#prescription_list" ).append(medicine);

											$("#delete_medicine"+medicine_count).click(function() {			
												$(this).parent().parent().remove();
											});			
											$("#medicine_name"+medicine_count).autocomplete({
												source: medicine_array,
												minLength: 1,//search after one characters
												select: function(event,ui){
													//do something
													$("#medicine_id"+medicine_count).val(ui.item ? ui.item.id : '');

												},
												change: function(event, ui) {
													 if (ui.item == null) {
														$("#medicine_name"+medicine_count).val('');
														}
												},
											});											
										});
									});
								</script>
						<div class="col-md-12">	
							<div class="col-md-12">	
								<label style="display:block;text-align:left;">Prescription</label>
							</div>
							<div class="col-md-12">
								<a href="#" id="add_medicine" class="btn btn-primary square-btn-adjust">Add another medicine</a>
								<input type="hidden" id="medicine_count" value="0"/>
							</div>
							<div id="prescription_list">
							<div class="col-md-2">	
								<label for="medicine" style="display:block;text-align:left;">Medicine</label>
								<input type="text" name="medicine_name[]" id="medicine_name" class="form-control"/>
								<input type="hidden" name="medicine_id[]" id="medicine_id" class="form-control"/>
							</div>
							<div class="col-md-6">
								<label for="frequency" style="display:block;text-align:left;">Frequency</label>
								<div class="col-md-1">
									M
								</div>
								<div class="col-md-3">
									<input type="text" name="freq_morning[]" id="freq_morning" class="form-control"/>
								</div>
								<div class="col-md-1">
									A
								</div>
								<div class="col-md-3">
									<input type="text" name="freq_afternoon[]" id="freq_afternoon" class="form-control"/>
								</div>
								<div class="col-md-1">
									N
								</div>
								<div class="col-md-3">
								<input type="text" name="freq_evening[]" id="freq_evening" class="form-control"/>
								</div>
							</div>
							<div class="col-md-1">
								<label for="days" style="display:block;text-align:left;">Days</label>
								<input type="text" name="days[]" id="days" class="form-control"/>
							</div>
							<div class="col-md-2">
								<label for="prescription_notes" style="display:block;text-align:left;">Instructions</label>
								<input type="text" name="prescription_notes[]" id="prescription_notes" class="form-control"/>
							</div>
							</div>
						</div>
						<?php } ?>
						<div class="col-md-12">	
							<div class="col-md-4">	
								<div class="form-group">
									<label for="followup_date"><?php echo $this->lang->line('next_follow_date');?></label>
									<input type="text" class="form-control" name="followup_date" id="followup_date" value="<?php echo date($def_dateformate, strtotime('+' . $clinic_settings['next_followup_days'] . ' days', time())); ?>"/>
								</div>
							</div>
						</div>
						<div class="col-md-12">	
							<div class="col-md-4">	
								<div class="form-group">
									<button class="btn btn-primary square-btn-adjust" type="submit" name="submit" /><?php echo $this->lang->line('save');?></button>
								</div>
							</div>
							<div class="col-md-4">	
								<input type="hidden" name="appointment_id" value="<?=$appointment_id;?>"/>
								<?php if ($appointment_id != NULL) { 
									$time = explode(":", $start_time); ?>
									<div class="form-group">
										<a class="btn btn-primary btn-sm square-btn-adjust" href='<?=base_url() . "index.php/appointment/change_status/" . $appointment_id . "/Complete";?>'>Complete</a>
									</div>
								<?php } ?>
							</div>
						</div>
						
					</div>
					<?php echo form_close(); ?>
				</div>
			</div>
			
			<div class="panel panel-primary">
				<div class="panel-heading expand-collapse-header">
					<i class="fa fa-arrow-circle-up"></i>
					<?php echo $this->lang->line('visits');?> <?php echo $this->lang->line('toggle_display');?>
				</div>	
				<div class="panel-body expand-collapse-content">
					<div class="table-responsive">
						<table class="table table-striped table-bordered table-hover" id="visit_table">			
						<thead>
							<tr>
								<th><?php echo $this->lang->line('date');?> <?php echo $this->lang->line('time');?></th>
								<th style="width:250px;"><?php echo $this->lang->line('notes');?></th>
								<th style="width:250px;">Patient Notes</th>
								<th><?php echo $this->lang->line('doctor');?></th>
								<?php if (in_array("gallery",$active_modules)) {?>
								<th><?php echo $this->lang->line('progress');?></th>
								<?php }?>
								<?php if (in_array("marking",$active_modules)) {?>
								<th><?php echo $this->lang->line('marking');?></th>
								<?php }?>
								<th style="text-align:right;"><?php echo $this->lang->line('bill') . ' ' . $this->lang->line('amount');?></th>
								<th style="text-align:right;"><?php echo $this->lang->line('balance');?></th>
								<th><?php echo $this->lang->line('bill');?></th>
								<th><?php echo $this->lang->line('bill') . ' '. $this->lang->line('print');?></th>
								<?php if (in_array("prescription", $active_modules)) { ?>
								<th>Prescription</th>
								<?php } ?>
								<th><?php echo $this->lang->line('edit');?></th>
							</tr>
						</thead>
						<?php $i = 1; ?>     
						<tbody>
						<?php if ($visits) { ?>
						<?php foreach ($visits as $visit) { ?>

							<tr>
								<td><?= date($def_dateformate, strtotime($visit['visit_date'])); ?> <?= date($def_timeformate, strtotime($visit['visit_time'])); ?></td>
								<td><?= $visit['notes']; ?><br />
								<td><?= $visit['patient_notes']; ?><br />
								<?php
								$flag = FALSE;
								foreach ($visit_treatments as $visit_treatment) {
									if ($visit_treatment['visit_id'] == $visit['visit_id'] && $visit_treatment['type'] == 'treatment') {
										if ($flag == FALSE) {                    
											echo $visit_treatment['particular'];
											$flag = TRUE;
										} else {
											echo " ," . $visit_treatment['particular'];
										}
									}
								}
								?>
								</td>
								<td><?php echo $visit['name']; ?></td>
								<?php if (in_array("gallery",$active_modules)) {?>
								<td>
									<a class="btn btn-primary square-btn-adjust" href="<?= site_url('gallery/index') ."/". $visit['patient_id'] ."/". $visit['visit_id']; ?>"><?php echo $this->lang->line('gallery');?></a>
								</td>
								<?php }?>
								<?php if (in_array("marking",$active_modules)) {?>
								<td>
									<a class="btn btn-primary square-btn-adjust" href="<?= site_url('marking/index') ."/". $visit['patient_id'] ."/". $visit['visit_id']; ?>"><?php echo $this->lang->line('marking');?></a>
								</td>
								<?php }?>
								<td style="text-align:right;"><?php echo currency_format($visit['total_amount']);if($currency_postfix) echo $currency_postfix['currency_postfix']; ?></td>
								<td style="text-align:right;"><?php echo currency_format($visit['due_amount']);if($currency_postfix) {echo $currency_postfix['currency_postfix'];} ?></td>
								<?php $total_amount=$total_amount+$visit['total_amount']; ?>
								<?php $bal_amount=$bal_amount+$visit['due_amount']; ?>
								<td><center><a class="btn btn-primary square-btn-adjust" href="<?= site_url('patient/bill') . "/" . $visit['visit_id'] . "/" . $visit['patient_id']; ?>"><?php echo $this->lang->line('bill');?></a></center></td>
								<td><center><a target="_blank" class="btn btn-primary square-btn-adjust" href="<?= site_url('patient/print_receipt') . "/" . $visit['visit_id']; ?>"><?php echo $this->lang->line('print') . ' ' . $this->lang->line('bill');?></a></center></td>
								<?php if (in_array("prescription", $active_modules)) { ?>
									<td>
									<?php if ($this->prescription_model->is_prescription($visit['visit_id'])){ ?>
										<a target="_blank" class="btn btn-primary square-btn-adjust" href="<?= site_url('prescription/print_prescription') . "/" . $visit['visit_id']; ?>"><?php echo $this->lang->line('print') . ' ' . $this->lang->line('prescription');?></a></br>
										<a class="btn btn-primary square-btn-adjust" href="<?= site_url('prescription/edit_prescription') . "/" . $visit['visit_id']; ?>"><?php echo $this->lang->line('edit') . ' ' . $this->lang->line('prescription');?></a>
									<?php }else{ ?>
										<a class="btn btn-primary square-btn-adjust" href="<?= site_url('prescription/edit_prescription') . "/" . $visit['visit_id']; ?>"><?php echo $this->lang->line('add') . ' ' . $this->lang->line('prescription');?></a>
									<?php }?>
									</td>
								<?php } ?>
								<td><center><a class="btn btn-primary square-btn-adjust" href="<?= site_url('patient/edit_visit') . "/" . $visit['visit_id'] . "/" . $visit['patient_id']; ?>"><?php echo $this->lang->line('edit');?></a></center></td>
							</tr>
							<?php $i++; ?>
							<?php } ?>
							<script>
								$(window).load(function() {
									$.fn.dataTable.moment( '<?=$morris_date_format;?> <?=$morris_time_format;?>' );// for sort date from our date formate
									$('#visit_table').dataTable({
										 "order": [[ 0, "desc" ]] 
									});
								});
							</script>
							
							<?php }else{ ?>
								<tr>
									<td colspan="9"><?php echo $this->lang->line('no_visits');?></td>
								</tr>
							<?php } ?>
							</tbody>
							<tfoot>
							<tr>
								<th></th>
								<th></th>
								<th></th>
								<?php if (in_array("gallery",$active_modules)) {?>
								<th></th>
								<?php }?>
								<?php if (in_array("marking",$active_modules)) {?>
								<th></th>
								<?php }?>
								<th></th>
								<th style="text-align:right;"><?php echo currency_format($total_amount)?></th>
								<th style="text-align:right;"><?php echo currency_format($bal_amount)?></th>
								<th></th>
								<?php if (in_array("prescription", $active_modules)) { ?>
								<th></th>
								<?php }?>
								<th></th>
								<th></th>
							</tr>
						</tfoot>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>