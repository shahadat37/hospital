<script type="text/javascript">
    $(document).ready(function(){
        $(".slidingDiv").hide();
        $(".show_hide").show();
 
        $('.show_hide').click(function(){
            $(".slidingDiv").slideToggle();
        });
    });
</script>
<style>
    .slidingDiv {
        /*        height:300px;*/
        padding:20px;
        margin-top:10px;
        /*        border-bottom:5px solid #3399FF;*/
    }

    .show_hide {
        display:none;
    }


</style>
<script type="text/javascript" charset="utf-8">
$( window ).load(function() {
	
    $('#followup_date').datetimepicker({
		timepicker:false,
		format: '<?=$def_dateformate; ?>',
		scrollInput:false, 
		scrollMonth:false,
		scrollTime:false,
		minDate: 0,
    });    
});
</script>
<?php

if(isset($followup)){
	$followup_date = date($def_dateformate,strtotime($followup['followup_date']));
	$doctor_id = $followup['userid'];
}else{
	$followup_date = "";
	$doctor_id = "";
}

$timezone = $this->settings_model->get_time_zone();

if (function_exists('date_default_timezone_set'))
{
	date_default_timezone_set($timezone);
}
$t = date('H:i');
$time = explode(":", $t);
?>
<div id="page-inner">
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-primary">
			<div class="panel-heading">
					<?php echo $this->lang->line("follow_up");?>
			</div>
			<div class="panel-body">
			<div class="form-group">        
				
				<a class="btn btn-primary" href="<?php echo base_url() . '/index.php/patient/edit/' . $patient_id ."/followup"?> "><?php echo $this->lang->line("edit")." ".$this->lang->line("patient");?></a>
			</div>

			<?php echo form_open('patient/change_followup_date/' . $patient_id) ?>
			<div class="form-group">
				<label for="doctor_id"><?php echo $this->lang->line("doctor")." ".$this->lang->line("name");?></label>
				<?php if($this->session->userdata('category') == 'Doctor'){ ?>
					<input type="hidden" name="doctor_id" id="doctor_id" value="<?php echo $doctor['userid']; ?>"/>
					<input readonly='readonly' type="text" name="doctor_name" id="doctor_name" class="form-control" value="<?php echo $doctor['name']; ?>"/>
				<?php }else{ ?>
					<select name="doctor_id" id="doctor_id" class="form-control" >
						<?php foreach($doctors as $doctor){ ?>
							<option value="<?=$doctor['userid'];?>" <?php if($doctor_id == $doctor['userid']) {echo "selected='selected'";} ?>> <?=$doctor['name'];?></option>
						<?php } ?>
					</select>
				<?php } ?>
				<?php echo form_error('doctor_id','<div class="alert alert-danger">','</div>'); ?>
			</div>
			<div class="form-group">
				<label for="patient_name"><?php echo $this->lang->line("patient")." ".$this->lang->line("name");?></label>
				<input type="hidden" name="patient_id" value="<?=$patient_id ;?>"/>
				<input readonly='readonly' type="text" name="patient_name" id="patient_name" class="form-control" value="<?php echo $patient['first_name'] . ' ' . $patient['middle_name']. ' ' . $patient['last_name']; ?>"/>
			</div>
			<div class="form-group">
				<label for="phone_number"><?php echo $this->lang->line("phone_number");?></label>
				<input readonly='readonly' type="text" name="phone_number" id="phone_number" class="form-control" value="<?php echo $patient['phone_number']; ?>"/>
			</div>
			<div class="form-group">
				<label for="followup_date"><?php echo $this->lang->line("follow_up")." ".$this->lang->line("date");?></label>
				<input type="text" name="followup_date" id="followup_date" class="form-control" autocomplete="off" value="<?=$followup_date;?>"/>
				<?php echo form_error('followup_date','<div class="alert alert-danger">','</div>'); ?>
			</div>
			<div class="form-group">
				<button class="btn btn-primary" type="submit" name="submit" /><?php echo $this->lang->line("save");?></button>
			</div>
			</div>
			</div>
			<div class="panel panel-primary">
			<div class="panel-heading">
				All Follow Ups
			</div>
			<div class="panel-body">
				<div class="table-responsive">
					<table class="table table-striped table-bordered table-hover" id="patient_table">
					<thead>
						<tr>
							<th>Sr. No.</th>
							<th>Doctor</th>
							<th>Date</th>
							<th>Actions</th>
						</tr>
					</thead>
					<?php $i=0; ?>
					<?php foreach($followups as $followup){
						$y = date('Y',strtotime($followup['followup_date']));
						$m = date('n',strtotime($followup['followup_date']));
						$d = date('j',strtotime($followup['followup_date']));
						if($this->session->userdata('category') == 'Doctor'){ 
							if($doctor['userid'] != $followup['userid']){ 
								break;
							}
						}
						$i++;?>
						<tr>
							<td><?=$i;?></td>
							<td>
							<?php if($this->session->userdata('category') == 'Doctor'){ 
									echo $doctor['name'];
								  }else{?>
								<?php foreach($doctors as $doctor) { 
									if($doctor['userid'] == $followup['userid']){ 
										echo $doctor['name'];
									} 
								} ?>	
							<?php }?>			
							</td>
							<?php $followup_date = date($def_dateformate,strtotime($followup['followup_date'])); ?>
							<td><?=$followup_date;?></td>
							<td><a class="btn btn-primary btn-sm " href='<?=base_url() . "index.php/appointment/add/" . $y . "/" . $m . "/" . $d . "/" . $time[0] . "/" . $time[1] . "/Appointments/" . $patient_id ?>' ><?php echo $this->lang->line("add")." ".$this->lang->line("appointment");?></a></td>
							<td><a class="btn btn-primary btn-sm" href="<?=site_url('/patient/edit_followup/'.$followup['id']);?>">Edit</a>
								<a class="btn btn-danger btn-sm" href="<?=site_url('/patient/dismiss_followup/'.$followup['id']);?>">Delete</a></td>
						</tr>
					<?php }?>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>