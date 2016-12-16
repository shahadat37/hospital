<script type="text/javascript" charset="utf-8">	
	$(window).load(function(){
		$('#working_date').datetimepicker({
			timepicker:false,
			format: '<?=$def_dateformate;?>',
			scrollMonth:false,
			scrollTime:false,
			scrollInput:false
		}); 
		$('#working_table').dataTable();
	});
</script>
<div id="page-inner">
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-primary">
				<div class="panel-heading">Working Days</div>
				<div class="panel-body">
					<?php echo form_open('settings/save_working_days'); ?>
					
					<?php 
						$days = array();
						$days[7] = "S";
						$days[1] = "M";
						$days[2] = "T";
						$days[3] = "W";
						$days[4] = "T";
						$days[5] = "F";
						$days[6] = "S";
						
						foreach($days as $key=>$value){
							$checked = '';	
							if(in_array($key, $working_days)){	
								$checked = 'checked';
							}
							echo"<div class='col-md-1'><label><input type='checkbox' name='working_days[]' $checked value='$key'>$value</label></div>";
						}
					?>
										
					<div class="col-md-1">	
						<input type="submit" name="submit" class="btn btn-primary" value="Save">
					</div>	
					<?php echo form_close(); ?>
				</div>
			</div>
			<div class="panel panel-primary">
				<div class="panel-heading">Exceptional Days</div>
				<div class="panel-body">
					<?php echo form_open('settings/save_exceptional_days'); ?>
					<div class="col-md-12">
						<div class="col-md-3">
							<label>Date</label>
							<input type="text" id="working_date" name="working_date" class="form-control">
							<?php echo form_error('working_date','<div class="alert alert-danger">','</div>'); ?>
						</div>
						<div class="col-md-3">
							<label>Status</label>
							<select name="working_status" id="working" class="form-control">
								<option value="Working">Working</option>
								<option value="Non Working">Non Working</option>
							</select>
							<?php echo form_error('working_status','<div class="alert alert-danger">','</div>'); ?>
						</div>
						<div class="col-md-3">
							<label>Reason</label>
							<input type="text" id="working_reason" name="working_reason" class="form-control">
							<?php echo form_error('working_reason','<div class="alert alert-danger">','</div>'); ?>
						</div>
						<div class="col-md-3">
							<p></p>
							<input type="submit" name="submit" class="btn btn-primary">
						</div>
					</div>
					<?php echo form_close(); ?>
					<div class="col-md-12">
						<p></p>
					</div>
					<div class="col-md-12">
					<div class="table-responsive">
						<table class="table table-striped table-bordered table-hover" id="working_table">
							<thead>
							<tr>
								<th>Date</th>
								<th>Status</th>
								<th>Reason</th>
								<th></th>
							</tr>
							</thead>
							<tbody>
							<?php foreach($exceptional_days as $exceptional_day){?>
								
							
							<tr>
								<td><?php echo date($def_dateformate,strtotime($exceptional_day['working_date']));?></td>
								<td><?php echo $exceptional_day['working_status'];?></td>
								<td><?php echo $exceptional_day['working_reason'];?></td>
								<td>
									<a href="<?=site_url('settings/edit_exceptional_days/'.$exceptional_day['uid']);?>" class="btn btn-primary btn-sm">Edit</a>
									<a href="<?=site_url('settings/delete_exceptional_days/'.$exceptional_day['uid']);?>" class="btn btn-danger btn-sm confirmDelete">Delete</a>
								</td>
							</tr>
							
							<?php }?>
							</tbody>
						</table>
					</div>
					</div>
				</div>
		</div>
		</div>
	</div>
</div>	
