<script type="text/javascript" charset="utf-8">
$( window ).load(function() {
	
	$('.confirmDelete').click(function(){
		return confirm("Are you sure you want to delete?");
	})

    $("#patient_table").dataTable({
		"pageLength": 50
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
				location.reload();
			});
	});
});
</script>
<?php
	$level = $this->session->userdata('category'); 
?>
<div id="page-inner">
	<div class="row">
		<div class="col-md-12">
			<!-- Advanced Tables -->
			<div class="panel panel-primary">
				<div class="panel-heading">
					<?php echo $this->lang->line('patients');?>
				</div>
				<div class="panel-body">
					<a title="<?php echo $this->lang->line("add")." ".$this->lang->line("patient");?>" href="<?php echo base_url()."index.php/patient/insert/" ?>" class="btn btn-primary square-btn-adjust"><?php echo $this->lang->line("add")." ".$this->lang->line("patient");?></a>
					
					
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
					
					<p></p>
					<div class="table-responsive">
						<table class="table table-striped table-bordered table-hover" id="patient_table">
							<thead>
								<tr>
									<th><?php echo $this->lang->line("id");?></th>
									<th><?php echo $this->lang->line("name");?></th>
									<th><?php echo $this->lang->line("display")." ".$this->lang->line("name");?></th>
									<th><?php echo $this->lang->line("phone_number");?></th>
									<th><?php echo $this->lang->line("email");?></th>
									<th><?php echo $this->lang->line("reference_by");?></th>
									<?php if($level != "Receptionist") { ?>
									<th><?php echo $this->lang->line("visit");?></th>
									<?php } ?>
									<th><?php echo $this->lang->line("follow_up");?></th>
									<?php if($level != "Receptionist") { ?>
									<th><?php echo $this->lang->line("delete");?></th>
									<?php } ?>
								</tr>
							</thead>
							<tbody>
								<?php $i=1; ?>
								<?php foreach ($patients as $patient):  ?>      
								<?php if(isset($patient['followup_date']) && $patient['followup_date'] != '0000-00-00'){?>
								<?php $followup_date = $patient['followup_date']; ?>
								<?php $followup_date = date('d-m-Y',strtotime($patient['followup_date'])); ?>
								<?php }else{ ?>
								<?php $followup_date = "Set Next Follow Up"; ?>
								<?php } ?>
								<tr <?php if ($i%2 == 0) { echo "class='even'"; } else { echo "class='odd'"; }?> >
									<td><?php echo $patient['display_id']; ?></td>
									<td><a class="btn btn-info btn-sm square-btn-adjust" title="Edit" href="<?php echo site_url("patient/edit/" . $patient['patient_id']."/patient"); ?>"><?php echo $patient['first_name'] . " " . $patient['middle_name'] . " " . $patient['last_name'] ?></a></td>
									<td><?php echo $patient['display_name']; ?></td>
									<td><?php echo $patient['phone_number']; ?></td>
									<td><?php echo $patient['email']; ?></td>
									<td><?php echo $patient['reference_by'];?></td>
									<?php if($level != "Receptionist") { ?>
									<td><a class="btn btn-primary btn-sm square-btn-adjust" title="Visit" href="<?php echo site_url("patient/visit/" . $patient['patient_id']); ?>"><?php echo $this->lang->line("visit");?></a></td>
									<?php } ?>
									<td><a class="btn btn-success btn-sm square-btn-adjust" title="Follow Up" href="<?php echo site_url("patient/followup/" . $patient['patient_id']); ?>"><?php echo $followup_date; ?></a></td>
									<?php if($level != "Receptionist") { ?>
									<td><a class="btn btn-danger btn-sm square-btn-adjust confirmDelete" title="<?php echo $this->lang->line('delete').' '. $this->lang->line("patient").' : ' . $patient['first_name'] . " " . $patient['middle_name'] . " " . $patient['last_name'] ?>" href="<?php echo site_url("patient/delete/" . $patient['patient_id']); ?>"><?php echo $this->lang->line("delete");?></a></td>
									<?php } ?>
								</tr>
								<?php $i++; ?>
								<?php endforeach ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<!--End Advanced Tables -->
		</div>
	</div>
</div>

