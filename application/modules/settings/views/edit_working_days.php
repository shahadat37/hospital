<script type="text/javascript" charset="utf-8">	
	$(window).load(function(){
		$('#working_date').datetimepicker({
			timepicker:false,
			format: '<?=$def_dateformate;?>',
			scrollMonth:false,
			scrollTime:false,
			scrollInput:false
		}); 
	});
</script>
<div id="page-inner">
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-primary">
				<div class="panel-heading">Exceptional Days</div>
				<div class="panel-body">
					<?php echo form_open('settings/update_exceptional_days'); ?>
					<div class="col-md-12">
						<input type="hidden" id="uid" name="uid" class="form-control" value="<?=$exceptional['uid'];?>">
						<div class="col-md-3">
							<label>Date</label>
							<input type="text" id="working_date" name="working_date" class="form-control" value="<?=date($def_dateformate,strtotime($exceptional['working_date']));?>">
							<?php echo form_error('working_date','<div class="alert alert-danger">','</div>'); ?>
						</div>
						<div class="col-md-3">
							<label>Status</label>
							<?php
							$option = array('Working'=>'Working',
											'Non Working'=>'Non Working');
							$attr = 'class="form-control"';
							echo form_dropdown("working_status",$option,$exceptional['working_status'],$attr);
							?>
							<?php echo form_error('working_status','<div class="alert alert-danger">','</div>'); ?>
						</div>
						<div class="col-md-3">
							<label>Reason</label>
							<input type="text" id="working_reason" name="working_reason" value="<?=$exceptional['working_reason'];?>" class="form-control">
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
					
				</div>
		</div>
		</div>
	</div>
</div>	
