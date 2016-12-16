<script type="text/javascript" charset="utf-8">
	$(window).load(function() {
		$("#from_date").datetimepicker({
			timepicker:false,
			format: '<?=$def_dateformate;?>',
			scrollInput:false, 
			scrollMonth:false,
			scrollTime:false,
			maxDate: 0,
		});
		$("#to_date").datetimepicker({
			timepicker:false,
			format: '<?=$def_dateformate;?>',
			scrollInput:false, 
			scrollMonth:false,
			scrollTime:false,
			maxDate: 0,
		});
		<?php if ($app_reports) {?>
		 var table = $('#appointment_report').DataTable({
			"columnDefs": [
				{ "visible": false, "targets": 0 }
			],
			"order": [[ 0, 'asc' ]],
			"displayLength": 25,
			"drawCallback": function ( settings ) {
				var api = this.api();
				var rows = api.rows( {page:'current'} ).nodes();
				var last=null;
	 
				api.column(0, {page:'current'} ).data().each( function ( group, i ) {
					if ( last !== group ) {
						$(rows).eq( i ).before(
							'<tr class="group"><td colspan="7">'+group+'</td></tr>'
						);
	 
						last = group;
					}
				} );
			}
		} );
		<?php } ?>
    });
</script>
<?php
	$level = $this->session->userdata('category');
?>
<div id="page-inner">
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-primary">
				<div class="panel-heading">
					<?php echo $this->lang->line('appointment')." ".$this->lang->line('report');?>
				</div>
				<div class="panel-body">
					<?php echo form_open('appointment/appointment_report'); ?>
					<div class="col-md-3">
						<?php echo $this->lang->line('from_date');?>
						<input type="text" name="from_date" id="from_date" class="form-control" value="<?=date($def_dateformate,strtotime($from_date));?>" />
					</div>
					<div class="col-md-3">
						<?php echo $this->lang->line('to_date');?>
						<input type="text" name="to_date" id="to_date" class="form-control" value="<?=date($def_dateformate,strtotime($to_date));?>" />
					</div>
					<div class="col-md-3" <?php if($level == 'Doctor'){ echo 'style = display:none;';} ?>>
						<?php echo $this->lang->line('doctor');?>
						<select name="doctor" class="form-control">
							<option></option>
							<?php foreach ($doctors as $doctor) {?>
								<option value="<?php echo $doctor['userid'] ?>" <?php if($doctor['userid'] == $doctor_id){echo "selected='selected'";} ?>><?= $doctor['name'];?></option>
							<?php } ?>
							<input type="hidden" name="doctor_id" id="doctor_id" value="" />
						</select>
					</div>
					<div class="col-md-3">
						<button type="submit" name="submit" class="btn btn-primary"><?php echo $this->lang->line('go');?></button>
					</div>
					<div class="col-md-3">
						<a href="<?php echo site_url('appointment/appointment_report_excel_export/'.date('Y-m-d',strtotime($from_date)).'/'.date('Y-m-d',strtotime($to_date)).'/'.$doctor_id);?>" name="excel_export" class="btn btn-primary"><?php echo $this->lang->line('export_to_excel');?></a>
						<a href="<?php echo site_url('appointment/print_appointment_report/'.$from_date.'/'.$to_date.'/'.$doctor_id);?>" class="btn btn-primary"><?php echo $this->lang->line('print_report');?></a>
					</div>
					<?php echo form_close(); ?>
				</div>
			</div>
		</div>	
		
			<div class="col-md-12">
				<div class="panel panel-primary">
					<div class="table-responsive">
						<table class="table table-striped table-bordered table-hover" id="appointment_report" >
							<thead>
								<tr>
									<th width="100px;"><?php echo $this->lang->line('doctor')." ".$this->lang->line('name');?></th>
									<th width="100px;"><?php echo $this->lang->line('patient')." ".$this->lang->line('name');?></th>
									<th width="100px;"><?php echo $this->lang->line('appointment')." ".$this->lang->line('date');?></th>
									<th width="100px;"><?php echo $this->lang->line('appointment')." ".$this->lang->line('time');?></th>
									<th><?php echo $this->lang->line('waiting')." ".$this->lang->line('time');?></th>
									<th><?php echo $this->lang->line('waiting')." ".$this->lang->line('duration');?></th>
									<th><?php echo $this->lang->line('consultation');?></th>
									<th><?php echo $this->lang->line('consultation')." ".$this->lang->line('duration');?></th>
								</tr>
							</thead>
							<?php if ($app_reports) {?>
							<tbody>
								<?php $i=1; ?>
								<?php foreach ($app_reports as $report):  ?>
									<tr <?php if ($i%2 == 0) { echo "class='even'"; }else{ echo "class='odd'"; } ?> >
										<td><?=$report['doctor_name'];?></td>      
										<td><?=$report['patient_name'];?></td>                
										<td><?=date($def_dateformate,strtotime($report['appointment_date'])); ?></td>
										<td><?=$report['appointment_time']; ?></td>
										<td><?=$report['waiting_in']; ?></td>
										<td><?=$report['waiting_duration']; ?></td>
										<td><?=$report['consultation_in'];?></td>
										<td><?=$report['consultation_duration']; ?></td>
										<!--td class="right"><?php echo currency_format($report['collection_amount']);if($currency_postfix) echo $currency_postfix['currency_postfix']; ?></td-->
									</tr>
								
								<?php $i++; ?>
								<?php endforeach ?>
							</tbody>
							<?php } else { ?>
							<tbody>
								<tr>
									<td colspan="8"><?php echo $this->lang->line('norecfound');?></td>
								</tr>
							</tbody>
							<?php } ?>
						</table>
					</div>
				</div>
			</div>
		
	</div>
</div>
