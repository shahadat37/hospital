<script type="text/javascript" charset="utf-8">	
	$(window).load(function(){
		$('.confirmCancel').click(function(){
			return confirm("<?=$this->lang->line('areyousure') . " " . $this->lang->line('cancel') . " " . $this->lang->line('appointment') . "?";?>");
		});
		
		$(".todo").change(function() {
			var element = $(this);
			var id = $(this).val();
			if($(this).is(':checked')){
				
				$.ajax({
					type: "POST",
					url: "<?php echo base_url(); ?>index.php/appointment/todos_done/1/" + id,
					success: function(){
						element.parent().addClass("done");
					}
				});
			}else{
				$.ajax({
					type: "POST",
					url: "<?php echo base_url(); ?>index.php/appointment/todos_done/0/" + id,
					success: function(){
						element.parent().removeClass("done");
					}
				});
			}
		});
		
		$('#select_date').datetimepicker({
			timepicker:false,
			format: 'd F Y,l',
			scrollMonth:false,
			scrollTime:false,
			scrollInput:false,
			onChangeDateTime:function(dp,$input){
				var month= dp.getMonth() + 1;
				window.location='<?php echo base_url(); ?>index.php/appointment/index/'+dp.getFullYear()+'/'+month+'/'+dp.getDate();
			}
		}); 
		
		$("#select_date").change(function(){
			var select_date = $(this).datepicker('getDate');
			alert(select_date.getDate());
			//
		});
		
	
		$("#add_inquiry_submit").click(function(event) {
			event.preventDefault();
			var first_name = $("#first_name").val();
			var middle_name = $("#middle_name").val();
			var last_name = $("#last_name").val();
			var email_id = $("#email_id").val();
			var mobile_no = $("#mobile_no").val();
			
			$.post( "<?php echo base_url(); ?>index.php/patient/add_inquiry",
				{first_name: first_name, middle_name: middle_name,last_name: last_name,email: email_id, phone_number:mobile_no},
				function(data,status)
				{
					alert(data);
				});
		});
		
		
	});
</script>
<?php
global $time_intervals;
global $doctor_inavailability;
global $doctors_details;
global $doctors_schedules;
global $day_of_week;
global $g_day;
global $g_month;
global $g_year;
global $holidays;
global $workingdays;

$day_of_week = date('l', strtotime($day . "-" . $month . "-" . $year));
$g_day = $day;
$g_month = $month;
$g_year = $year;


if($doctor_active){
	$doctor_inavailability = $inavailability;
	$doctors_details = $doctors_data;
	$doctors_schedules = $drschedules;
}else{
	$doctor_inavailability = array();
	$doctors_details = array();
	$doctors_schedules = array();
}

$holidays = $exceptional_days;
$workingdays = $working_days;

//Converts Integer to Time. e.g. 9 -> 9:00 , 9.5 -> 9:30
function inttotime12($tm,$time_format) {
    //if ($tm >= 13) {  $tm = $tm - 12; }
    $hr = intval($tm);
    $min = ($tm - intval($tm)) * 60;
    $format = '%02d:%02d';
	$time = sprintf($format, $hr, $min); //H:i
	$time = date($time_format, strtotime($time));
    return $time;
}
//Convert Time to integer.e.g. 09:00 -> 9, 09:30 -> 9.5
function timetoint12($time)
{
	$hours = idate('H', strtotime($time));
	$minutes = idate('i', strtotime($time));
	
	return $hours + ($minutes/60);
}

function inttotime($tm) {
    $hr = intval($tm);
    $min = ($tm - intval($tm)) * 60;
    $format = '%02d:%02d';
    return sprintf($format, $hr, $min);
}
function timetoint($time) {
    $hrcorrection = 0;
    if (strpos($time, 'PM') > 0) { $hrcorrection = 12;}
    list($hours, $mins) = explode(':', $time);
    $mins = str_replace('AM', '', $mins);
    $mins = str_replace('PM', '', $mins);
    return $hours + $hrcorrection + ($mins / 60);
}
function nearest_timeinterval($time){
	global $time_intervals;
	$prev_interval = 0;
	foreach($time_intervals as $curr_interval){
		if($curr_interval == $time){
			return $time;
		}else{
			if($time >= $prev_interval && $time < $curr_interval){
				if($prev_interval == 0){
					return $curr_interval;
				}else{
					$median = ($prev_interval + $curr_interval)/2;
					if ($time < $median){
						return $prev_interval;
					}else{
						return $curr_interval;
					}
				}
			}
		}
		$prev_interval = $curr_interval;
		
	}
	
}

function check_doctor_availability($i,$doctor_id){
	global $doctor_inavailability;	
	global $doctors_details;
	global $doctors_schedules;
	global $day_of_week;
	global $g_day;
	global $g_month;
	global $g_year;
	
	
	$today = date('Y-m-d', strtotime($g_day . "-" . $g_month . "-" . $g_year));

	$doctor_is_available = TRUE;	
	
	//Is this Doctors' Schedule Available?
	foreach ($doctors_details as $doctor_data){
		foreach ($doctors_schedules as $drschedules_availability){		
			if($drschedules_availability['doctor_id']==$doctor_data['doctor_id']){
				if ($doctor_data['userid']==$doctor_id){	
					//Except Schedule, Doctor is not available
					$doctor_is_available = FALSE;	
					break;
				}
			}
		}
	}
	
	//Is this Doctor's Schedule?
	foreach ($doctors_details as $doctor_data){
		if ($doctor_data['userid']==$doctor_id){	
			foreach ($doctors_schedules as $drschedules_availability){														
				if($drschedules_availability['doctor_id']==$doctor_data['doctor_id']){
					$schedule_day = $drschedules_availability['schedule_day'];
					if (strpos($schedule_day,$day_of_week) !== false) {				
						if ($i>= timetoint($drschedules_availability['from_time']) && $i<= timetoint($drschedules_availability['to_time']) ){
							//Doctor is not available
							$doctor_is_available = TRUE;
							break;
						}
					}
				}
			}
		}
	}
	//Is Doctor Out?
	if ($doctor_is_available){
		foreach ($doctor_inavailability as $inavailability){
			if ($inavailability['userid']==$doctor_id){
				if($today >= $inavailability['appointment_date'] && $today <= $inavailability['end_date']){
					if ($i>=timetoint($inavailability['start_time']) && $i<timetoint($inavailability['end_time'])){
						//Doctor is not available
						$doctor_is_available = FALSE;
					}
				}
			}
		}
	}
	return $doctor_is_available;
}
function is_holiday($today){
	global $holidays;
	global $workingdays;
	
	$holiday_reason = "";
	//For Working Days
	$day = date("N",strtotime(($today)));
	if (!in_array($day, $workingdays)){
		$holiday_reason = "Non Working Day";
	}
	//For Holidays
	foreach($holidays as $holiday){
		if($holiday['working_status'] == "Non Working")	{
			if(strtotime($holiday['working_date']) == strtotime($today)){
				$holiday_reason = $holiday['working_reason'];
			}
		}else{
			if(strtotime($holiday['working_date']) == strtotime($today)){
				$holiday_reason = "";
			}
		}
	}
	
	return $holiday_reason;
}

?>
<div id="page-inner">
    <div class="row">
		<div class="col-md-12">
			<!--------------------------- Display Appointments  ------------------------------->
			<div class="panel panel-primary">
				<div class="panel-heading">
					<input type="text" id="select_date" name="select_date" class="btn btn-success" value="<?=date('d F Y, l', strtotime($day . "-" . $month . "-" . $year));?>"/>
					<?php $day_date=$day; ?>
				</div>
                    <div class="panel-body">
					<?php 
						$day = date('l', strtotime($day . "-" . $month . "-" . $year));
						$today = date('Y-m-d', strtotime($appointment_date ));
						
						//$day_date=date('d', strtotime($day . "-" . $month . "-" . $year));
						$level = $this->session->userdata('category'); 
						//Clinic Start Time and Clinic End Time
						$start_time = timetoint($start_time);
						$end_time = timetoint($end_time);
						
					?>
						<!--------------------------- Display Doctor's Screen  ------------------------------->
					<?php if ($level == 'Doctor') {?>
						<a href="<?=site_url('appointment/add');?>" class="btn square-btn-adjust btn-primary">Add Appointment</a>
						<a href="#" class="btn square-btn-adjust btn-primary">Add Inquiry</a>
						
						
						<div class="table-responsive"  style='position:relative;height:500px;'>
							<table class="table table-condensed table-striped table-bordered table-hover dataTable no-footer"  >
								<thead>
									<tr>
										<th><?=$this->lang->line('time');?></th>
										<th><?=$this->lang->line('appointments');?></th>
										<th><?=$this->lang->line('waiting');?></th>
										<th><?=$this->lang->line('consultation');?></th>
										<th><?=$this->lang->line('complete');?></th>
										<th><?=$this->lang->line('cancel');?></th>
									</tr>
								</thead>
								<tbody>
								<?php
									//Doctor ID
									$userid = $this->session->userdata('id');
									$doctor_id = $userid;
									global $time_intervals;
									$time_intervals = array();
									$is_holiday = is_holiday($today);
									for ($i = $start_time; $i < $end_time; $i = $i + $time_interval) {
										$time = explode(":",inttotime($i));                    
										$time_intervals[] = $i*100;
										if ($is_holiday == ""){
											$doctor_is_available = check_doctor_availability($i,$doctor_id);			
											if ($doctor_is_available){ ?>
											<tr>
												<th><?=inttotime12( $i ,$time_format);?></th><!-- Display the Time -->
												<td id="app<?=$i*100;?>" class="appointments"><a href='<?=base_url() . "index.php/appointment/add/" . $year . "/" . $month . "/" . $day_date . "/" . $time[0] . "/" . $time[1] . "/Appointments" ?>' class="add_appointment"></a></td>
												<td id="wai<?=$i*100;?>" class="waiting"><a href='<?=base_url() . "index.php/appointment/add/" . $year . "/" . $month . "/" . $day_date . "/" . $time[0] . "/" . $time[1] . "/Waiting" ?>' class="add_appointment" ></a></td>
												<td id="con<?=$i*100;?>" class="consultation"><a href='<?=base_url() . "index.php/appointment/add/" . $year . "/" . $month . "/" . $day_date . "/" . $time[0] . "/" . $time[1] . "/Consultation" ?>' class="add_appointment" ></a></td>
												<td id="com<?=$i*100;?>" class="complete"></td>
												<td id="can<?=$i*100;?>" class="cancel"></td>
												<td id="pend<?=$i*100;?>" class="cancel"></td>
											</tr>
											<?php }else{ ?>
											<tr>
												<th><?=inttotime12( $i ,$time_format);?></th><!-- Display the Time -->
												<td id="app<?=$i*100;?>" style="background-color:grey;"></td>
												<td id="wai<?=$i*100;?>" style="background-color:grey;"></td>
												<td id="con<?=$i*100;?>" style="background-color:grey;"></td>
												<td id="com<?=$i*100;?>" style="background-color:grey;"></td>
												<td id="can<?=$i*100;?>" style="background-color:grey;"></td>
												<td id="pend<?=$i*100;?>" style="background-color:grey;"></td>
											</tr>
											<?php } ?>
										<?php }else{ ?>
											<tr>
												<th><?=inttotime12( $i ,$time_format);?></th><!-- Display the Time -->
												<td id="app<?=$i*100;?>" style="background-color:#FF5599;color:white;;"><?=$is_holiday;?></td>
												<td id="wai<?=$i*100;?>" style="background-color:#FF5599;color:white;;"><?=$is_holiday;?></td>
												<td id="con<?=$i*100;?>" style="background-color:#FF5599;color:white;;"><?=$is_holiday;?></td>
												<td id="com<?=$i*100;?>" style="background-color:#FF5599;color:white;;"><?=$is_holiday;?></td>
												<td id="can<?=$i*100;?>" style="background-color:#FF5599;color:white;;"><?=$is_holiday;?></td>
												<td id="pend<?=$i*100;?>" style="background-color:#FF5599;color:white;;"><?=$is_holiday;?></td>
											</tr>
										<?php } ?>
							<?php }
								$cell = array();
								foreach ($appointments as $appointment) {
									$patient_id = $appointment['patient_id'];
										
									$appointment_id = $appointment['appointment_id'];
									
									if (strlen($appointment['title'])>12){ 
										$appointment_title = substr($appointment['title'],0,9)."..." ;
									}else{
										$appointment_title = $appointment['title'];
									}
									//Check if there are any more appointments of same time
									$start_position =  timetoint($appointment['start_time'])*100;
									$start_position = round($start_position);
									$start_position = nearest_timeinterval($start_position);
									$end_position =  timetoint($appointment['end_time'])*100;
									$end_position = round($end_position);
									$end_position = nearest_timeinterval($end_position);
									
									$appointment_column = 0;
									for($column = 1;$column <= 10;$column = $column + 1){
										$cell_available = FALSE;
										//Check if cell is empty
										if(!isset($cell[$doctor_id][$start_position][$column]) || !($cell[$doctor_id][$start_position][$column] > 0)){
											for($row = $start_position;$row<$end_position;$row = $row + ($time_interval * 100)){
												if(!isset($cell[$doctor_id][$row][$column]) || !($cell[$doctor_id][$row][$column] > 0)){
													$cell[$doctor_id][$row][$column] = $appointment_id;	
													$appointment_column = $column;
													$cell_available = TRUE;
												}else{
													//Clear the incorrect data
													for($r = $start_position;$r<$row;$r = $r + ($time_interval * 100)){
														$cell[$doctor_id][$r][$column] = 0;		
													}
													$cell_available = FALSE;
													break;	
												}
											}
											if($cell_available){ 
												break;
											}
										}
									}
									$nxt=false;
									$ca=false;
									switch($appointment['status']){
										case 'Appointments':
											$class = "btn-primary";
											$start_position = "app".$start_position;
											$end_position = "app".$end_position;
											$href = base_url() . "index.php/appointment/edit_appointment/" . $appointment_id ;
											$nxt=true;
											$nextstatus= base_url() ."index.php/appointment/change_status/". $appointment_id."/Waiting";
											$ca=true;
											$cancelapp= base_url() ."index.php/appointment/change_status/". $appointment_id."/Cancel";
											break;
										case 'Consultation':
											$class = "btn-danger";
											$start_position = "con".$start_position;
											$end_position = "con".$end_position;
											$href = base_url() . "index.php/patient/visit/" . $patient_id ."/" . $appointment_id ;
											$nxt=false;
											$ca=false;
											break;
										case 'Complete':
											$class = "btn btn-success";
											$start_position = "com".$start_position;
											$end_position = "com".$end_position;
											$href = base_url() . "index.php/patient/visit/" . $patient_id ."/" . $appointment_id ;
											$nxt=false;
											$ca=false;
											break;
										case 'Cancel':
											$class = "btn btn-info";
											$start_position = "can".$start_position;
											$end_position = "can".$end_position;
											$href = base_url() . "index.php/appointment/edit_appointment/" . $appointment_id ;
											$nxt=false;
											$ca=false;
											break;
										case 'Pending':
											$class = "btn btn_pending";
											$start_position = "pend".$start_position;
											$end_position = "pend".$end_position;
											$href = base_url() . "index.php/appointment/edit_appointment/" . $appointment_id ;
											$nxt=false;
											$ca=false;
											break;
										case 'Waiting':
											$class = "btn-warning";
											$start_position = "wai".$start_position;
											$end_position = "wai".$end_position;
											$href = base_url() . "index.php/appointment/edit_appointment/" . $appointment_id ;
											$nxt=true;
											$nextstatus= base_url() ."index.php/appointment/change_status/". $appointment_id."/Consultation";
											$ca=true;
											$cancelapp= base_url() ."index.php/appointment/change_status/". $appointment_id."/Cancel";
											break;
										default:
											break;
									}
						?>
									
									<div id="<?=$appointment_id;?>" start_position="<?=$start_position;?>" end_position="<?=$end_position;?>" appointment_column="<?=$appointment_column;?>"  style="display:none;" >
										<a href='<?=$href;?>' title="<?=$appointment['title'];?>" class="btn square-btn-adjust <?=$class;?> " style="height:100%;" ><?= $appointment_title;?></a><?php if ($nxt){?><a href='<?=$nextstatus;?>' class="btn square-btn-adjust <?=$class;?> " style="height:100%;"><i class="fa fa-arrow-circle-right"></i></a><?php } ?><?php if($ca){ ?><a href='<?=$cancelapp;?>' class="btn square-btn-adjust <?=$class;?>" style="height:100%;"><i class="fa fa-times"></i></a><?php } ?>
									</div>
									<script>
										//$(window).load(function() {
											var start_position = $("#<?=$appointment_id;?>").attr( "start_position" );
											var end_position = $("#<?=$appointment_id;?>").attr( "end_position" );
											var s_position = $( "#" + start_position ).position();
											var e_position = $( "#" + end_position ).position();
											var height = e_position.top - s_position.top - 2;
											
											var appointment_column = $("#<?=$appointment_id;?>").attr( "appointment_column" ) - 1;
											var width = 100;
											width = width + 2;
											var element_left = s_position.left + ( appointment_column * width );
											$("#<?=$appointment_id;?>").attr("style","position:absolute;top:"+ s_position.top +"px;left:" + element_left +"px;height:"+height+"px");
											$("#<?=$appointment_id;?>").show();
										//});
									</script>
									<?php 
									} 
									
									?>       
								</tbody>
							</table>
						</div>
					<?php
					} else {
					?><!--------------------------- Display Administration's Screen / Staff Scrren  ------------------------------->	
					<div class="table-responsive"  style='position:relative;overflow:scroll;height:500px;'>
						<a href="<?=site_url('appointment/add');?>" class="btn square-btn-adjust btn-primary">Add Appointment</a>
						
						
							<a href="#" class="btn square-btn-adjust btn-primary" data-toggle="modal" data-target="#myModal">Add Inquiry</a>
							<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
											<h4 class="modal-title" id="myModalLabel">Add Inquiry</h4>
										</div>
										<?php echo form_open(); ?>
										<div class="modal-body">
												<div class="col-md-12"><label>Name:</label></div>
												<div class="col-md-4"><input type="text" id="first_name" name="first_name" class="form-control" placeholder="first name"/></div>										
												<div class="col-md-4"><input type="text" id="middle_name" name="middle_name" class="form-control" placeholder="middle name"/></div>
												<div class="col-md-4"><input type="text" id="last_name" name="last_name" class="form-control" placeholder="last name"/></div>
											
											
												<div class="col-md-12"><label>Email ID:</label></div>
												<div class="col-md-12"><input type="text" id="email_id" name="email_id" class="form-control"/></div>
											
											
												<div class="col-md-12"><label>Mobile No:</label></div>
												<div class="col-md-12"><input type="text" id="mobile_no" name="mobile_no" class="form-control"/></div>
											
										</div>
										<div class="modal-footer">
												<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
												<input id="add_inquiry_submit" type="submit" name="submit" value="Save" class="btn btn-primary" data-dismiss="modal"/>
										</div>
										<?php echo form_close(); ?>
									</div>
								</div>
							</div>
						
												
						<table class="table table-condensed table-striped table-bordered table-hover dataTable no-footer"  >
							<thead>
								<tr>
									<th><?=$this->lang->line('time');?></th>
									<?php 
									foreach ($doctors as $doctor) { 
										if (strlen($doctor['name'])>12){ 
											$doctor_name = substr($doctor['name'],0,9)."..." ;
										}else{
											$doctor_name = $doctor['name'];
										}
									?>
									<th><?=$doctor_name;?></th>
									<?php } ?>
								</tr>
							</thead>	
							<tbody>
								<?php
								global $time_intervals;
								$time_intervals = array();
								for ($i = $start_time; $i < $end_time; $i = $i + $time_interval){
									$time = explode(":",inttotime($i));
									$time_intervals[] = $i*100;
									?>
										<tr>
										<th><?=inttotime12( $i ,$time_format);?></th><!-- Display the Time -->
									<?php
										$is_holiday = is_holiday($today);
										if ($is_holiday == ""){
											foreach ($doctors as $doctor) {
												$doctor_is_available = check_doctor_availability($i,$doctor['userid']);												
												if ($doctor_is_available){
													?><td id="<?=$doctor['userid'];?>_<?=$i*100;?>"><a href='<?=base_url() . "index.php/appointment/add/" . $year . "/" . $month . "/" . $day_date . "/" . $time[0] . "/" . $time[1] . "/Appointments/0/".$doctor['userid'] ?>' class="add_appointment"></a></td>	<?php
												}else{
													?><td id="<?=$doctor['userid'];?>_<?=$i*100;?>" bgcolor="gray"></td><?php
												}
											}	
										}else{
											foreach ($doctors as $doctor) {
												?><td id="<?=$doctor['userid'];?>_<?=$i*100;?>" style="background-color:#FF5599;color:white;"><?=$is_holiday;?></td><?php
											}	
											
										}
										 ?>
										</tr>
								<?php }
									$time = explode(":",inttotime($i));
									$time_intervals[] = $i*100;
									?>
										<tr>
										<th></th>
										<?php
											foreach ($doctors as $doctor) {
												?><td id="<?=$doctor['userid'];?>_<?=$i*100;?>"></td><?php
											}?>
										</tr>
										<?php
									$cell = array();
									foreach ($appointments as $appointment) {
										$patient_id = $appointment['patient_id'];
										$appointment_id = $appointment['appointment_id'];
										
										$doctor_id = $appointment['userid'];
										if (strlen($appointment['title'])>12){ 
											$appointment_title = substr($appointment['title'],0,9)."..." ;
										}else{
											$appointment_title = $appointment['title'];
										}
										$start_position = timetoint($appointment['start_time'])*100;
										$start_position = round($start_position);
										$start_position = nearest_timeinterval($start_position);
										
										
										$end_position =  timetoint($appointment['end_time'])*100;
										$end_position = round($end_position);
										$end_position = nearest_timeinterval($end_position);
										
										
										$appointment_column = 0;
										//Select a column inside the doctor column
										for($column = 1;$column <= 3;$column = $column + 1){
											//Check if cell is empty
											if(isset($cell[$doctor_id][$start_position][$column]) && ($cell[$doctor_id][$start_position][$column] != 0)){
												//Cell is occupied
											}else{
												//Cell is not occupied
												$cell[$doctor_id][$start_position][$column] = $appointment_id;	
												$appointment_column = $column-1;
												break;
											}
										}
										

										switch($appointment['status']){
											case 'Appointments':
												$class = "btn-primary";
												$href = base_url() . "index.php/appointment/edit_appointment/" . $appointment_id ;
												break;
											case 'Consultation':
												$class = "btn-danger";
												$href = base_url() . "index.php/patient/visit/" . $patient_id."/".$appointment_id ;
												break;
											case 'Complete':
												$class = "btn-success";
												$href = base_url() . "index.php/appointment/view_appointment	/". $appointment_id  ;
												break;
											case 'Cancel':
												$class = "btn-info";
												$href = base_url() . "index.php/appointment/edit_appointment/" . $appointment_id ;
												break;
											case 'Waiting':
												$class = "btn-warning";
												$href = base_url() . "index.php/appointment/edit_appointment/" . $appointment_id ;
												break;
											case 'Pending':
												$class = "btn_pending";
												$href = base_url() . "index.php/appointment/edit_appointment/" . $appointment_id ;
												break;
											default:
												$class = "btn-primary";
												$href = base_url() . "index.php/appointment/edit_appointment/" . $appointment_id ;
												break;
										}
										
										$start_position = $appointment['userid']."_".$start_position;
										$end_position = $appointment['userid']."_".$end_position;
								?>
									<div id="<?=$appointment_id;?>" start_position="<?=$start_position;?>" end_position="<?=$end_position;?>" appointment_column="<?=$appointment_column;?>"  style="display:none;" >
										<a href='<?=$href;?>' title="<?=$appointment['title'];?>" class="btn square-btn-adjust <?=$class;?> " style="height:100%;">
											<?= $appointment_title;?>
										</a>
									</div>
									
									<script>
										//$(window).load(function() {
											var start_position = $("#<?=$appointment_id;?>").attr( "start_position" );
											var end_position = $("#<?=$appointment_id;?>").attr( "end_position" );
											var s_position = $( "#" + start_position ).position();
											var e_position = $( "#" + end_position ).position();
											var height = e_position.top - s_position.top - 2;
											
											var appointment_column = $("#<?=$appointment_id;?>").attr( "appointment_column" ) ;
											var width = $( document ).width()/12;
											width = width + 2;
											var element_left = s_position.left + ( appointment_column * width );
											$("#<?=$appointment_id;?>").attr("style","position:absolute;top:"+ s_position.top +"px;left:" + element_left +"px;height:"+height+"px;width:"+width+"px");
											$("#<?=$appointment_id;?>").show();
										//});
									</script>
									<?php 
									}
									?>   
							</tbody>
						</table>
					</div>
                    <?php
                }
			?>
			<div class="col-md-12 col-sm-12 col-xs-12">
				<div class="col-md-2">
					<span class="btn square-btn-adjust btn-primary"><?=$this->lang->line('appointment');?></span>
				</div>
				<div class="col-md-2">
					<span class="btn square-btn-adjust btn-danger"><?=$this->lang->line('consultation');?></span>
				</div>
				<div class="col-md-3">
					<span class="btn square-btn-adjust btn-success"><?=$this->lang->line('complete') .' '. $this->lang->line('appointment');?></span>
				</div>
				<div class="col-md-3">
					<span class="btn square-btn-adjust btn-info"><?=$this->lang->line('cancelled') .' '. $this->lang->line('appointment');?></span>
				</div>
				<div class="col-md-2">
					<span class="btn square-btn-adjust btn-warning"><?=$this->lang->line('waiting');?></span>
				</div>
				<div class="col-md-2">
					<span class="btn square-btn-adjust btn-grey"><?=$this->lang->line('not_available');?></span>
				</div>
				<div class="col-md-2">
					<span class="btn square-btn-adjust btn_pending"><?=$this->lang->line('pending');?></span>
				</div>
            </div>
			<?php
			echo "</div></br>";
			?>
			</div>
		</div>
			<div class="col-md-4">
				<!--------------------------- Display Follow-Up  ------------------------------->
				<div class="panel panel-primary">
                    <div class="panel-heading"><?=$this->lang->line('follow_ups');?></div>
					<div class="panel-body"  style="overflow:scroll;height:250px;padding:0;">
						<?php if ($followups) { ?>
							<table class="table table-condensed table-striped table-bordered table-hover dataTable no-footer" id="followup_table">
								<thead>
									<th><?= $this->lang->line('follow_up') .' '. $this->lang->line('date');?></th>
									<th><?= $this->lang->line('doctor');?></th>
									<th><?= $this->lang->line('patient');?></th>
								</thead>
								<tbody>
								<?php
									$i = 0;
									foreach ($followups as $followup) {
										foreach ($patients as $patient) {
											if ($followup['patient_id'] == $patient['patient_id']) { 
												if ($followup['patient_id'] == $patient['patient_id']) {
													foreach ($doctors as $doctor) {	
														if ($followup['userid'] == $doctor['userid']) {
															$followup_date = $followup['followup_date'];
															$patient_name = $patient['first_name'] . " " . $patient['middle_name'] . " " . $patient['last_name'];
															?>
															<tr>
																<td><?= date('d.m.y', strtotime($followup_date));?></td>
																<td><?=$doctor['name'];?></td>
																<td><a href='<?= base_url() . "index.php/patient/followup/" . $patient['patient_id'] ;?>' ><?=$patient_name;?></a></td>
															</tr>
												<?php 
														}
													}
												}
												break; 
											}
										}
									} ?>
								</tbody>	
							</table>
						<?php }	?>
					</div>
				</div>
			</div>
		<div class="col-md-4">
			<div class="panel panel-primary">
				<div class="panel-heading"><?=$this->lang->line('tasks');?></div>
				<div class="panel-body">
				<!--------------------------- Display To Do  ------------------------------->
				<?php echo form_open('appointment/todos'); ?>
					<div class="input-group">
						<input type="text" name="task"  class="form-control">
						<span class="form-group input-group-btn">
							<input type="submit" class="btn btn-primary" value='<?=$this->lang->line('submit');?>' />
						</span>
					</div>
				<?php echo form_close(); ?>
				<?php foreach ($todos as $todo) { ?>
					<div class="checkbox">
						<label class="<?php if ($todo['done'] == 1) {echo 'done';} else {echo 'not_done';} ?>">
							<input type="checkbox" class="todo" name='todo' <?php if ($todo['done'] == 1) {echo 'checked="checked"';} ?> value="<?=$todo['id_num'];?>" /><?=$todo['todo'];?>
						</label>
						<a class='todo_img' href='<?=base_url() . "index.php/appointment/delete_todo/" . $todo['id_num'];?>'><i class='fa fa-remove'></i></a>
					</div>
				<?php } ?>
				</div>
			</div>
			<!--------------------------- Display To Do  ------------------------------->
		</div>
	
</div>
