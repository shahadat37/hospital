			<!----start-content----->
			<div class="content">
				<div class="wrap">
					<!---start-contact---->
					<div class="contact">			
						<?php if($logged_in){ ?>
							<div class="book_appointment">
								<div class="services">
									<div class="section group">
										<div class="service-content grid_1_of_4 images_1_of_4 contact-form">
											<a href="submit" class="make_appointment_button"><?php echo $this->lang->line('book').' '.$this->lang->line('appointment');?></a>
										</div>
										<div class="service-content grid_1_of_4 images_1_of_4 contact-form">
											<h4>Appointment History</h4>
										</div>
										<div class="grid_4_of_4 contact-form">
											<table class="book_appointment_calendar">
												<thead>
													<tr>
														<th>Date</th>
														<th>Time</th>
														<th>Patient Name</th>
														<th>Doctor</th>
														<th>Reason</th>
													</tr>
												</thead>
												<tbody>
													<?php foreach($appointments as $appointment) {?>
													<tr>
														<td><?=$appointment['appointment_date'];?></td>
														<td><?=$appointment['start_time'];?> - <?=$appointment['end_time'];?></td>
														<td><?=$appointment['patient_id'];?></td>
														<td><?=$appointment['userid'];?></td>
														<td><?=$appointment['appointment_reason'];?></td>
													</tr>
													<?php }?>
												</tbody>
											</table>
										</div>
									</div>
								</div>
							</div>
						<?php }else{ ?>
							<div class="book_appointment">
								<div class="services">
									<div class="section group">
										<div class="service-content grid_1_of_4 images_1_of_4 contact-form">
											<h4><?php echo $this->lang->line('new') . ' ' . $this->lang->line('registration');?></h4>
											<?php echo form_open('frontend/register_user') ?>
								
												<label><?php echo $this->lang->line('name');?></label>
												<input type="text" id="first_name" name="first_name" placeholder="<?php echo $this->lang->line('first_name');?>"/>
												<?php echo form_error('first_name','<div class="alert alert-danger">','</div>'); ?>
												<input type="text" id="middle_name" name="middle_name" placeholder="<?php echo $this->lang->line('middle_name');?>"/>
												<?php echo form_error('middle_name','<div class="alert alert-danger">','</div>'); ?>
												<input type="text" id="last_name" name="last_name" placeholder="<?php echo $this->lang->line('last_name');?>"/>
												<?php echo form_error('last_name','<div class="alert alert-danger">','</div>'); ?>
												<label><?php echo $this->lang->line('email');?></label>
												<input type="text" id="email" name="email" placeholder="<?php echo $this->lang->line('email');?>"/>
												<?php echo form_error('email','<div class="alert alert-danger">','</div>'); ?>
												<label><?php echo $this->lang->line('password');?></label>
												<input type="password" id="password" name="password" placeholder="<?php echo $this->lang->line('password');?>"/>
												<?php echo form_error('password','<div class="alert alert-danger">','</div>'); ?>
													
												<button type="submit" name="submit" class="make_appointment_button"><?php echo $this->lang->line('register');?></button>
																			
											<?php echo form_close(); ?>
										</div>
										<div class="services-sidebar grid_1_of_4 images_1_of_4 contact-form">
											<h4><?php echo $this->lang->line('login');?></h4>
											<?php echo form_open('frontend/login_user') ?>
					
												<label><?php echo $this->lang->line('email');?></label>
												<input type="text" id="email" name="email" placeholder="<?php echo $this->lang->line('email');?>"/>
												<?php echo form_error('email','<div class="alert alert-danger">','</div>'); ?>
												<label><?php echo $this->lang->line('password');?></label>
												<input type="password" id="password" name="password" placeholder="<?php echo $this->lang->line('password');?>"/>
												<?php echo form_error('password','<div class="alert alert-danger">','</div>'); ?>
												<button type="submit" name="submit" class="make_appointment_button"><?php echo $this->lang->line('login');?></button>
											<?php echo form_close(); ?>  
										</div>
									</div>
								</div>
								<div class="services">
									<div class="service-content grid_1_of_4 images_1_of_4 contact-form">
										<h4><?php echo $this->lang->line('verify_email');?></h4>
										<?php echo form_open('frontend/verify_account_code');?>
										
											<label><?php echo $this->lang->line('email');?></label>
											<input type="text" id="verify_email" name="verify_email" placeholder="<?php echo $this->lang->line('email');?>"/>
											<?php echo form_error('verify_email','<div class="alert alert-danger">','</div>'); ?>	
											<label><?php echo $this->lang->line('verification_code');?></label>
											<input type="text" id="verification_code" name="verification_code" placeholder="<?php echo $this->lang->line('verification_code');?>"/>
											<?php echo form_error('verification_code','<div class="alert alert-danger">','</div>'); ?>	
											<button type="submit" name="submit" class="make_appointment_button"><?php echo $this->lang->line('verify');?></button>
										<?php echo form_close();?>
									</div>
									<div class="services-sidebar grid_1_of_4 images_1_of_4 contact-form">
										<h4><?php echo $this->lang->line('resend_code');?></h4>
										<?php echo form_open('frontend/resend_code') ?>
				
											<label><?php echo $this->lang->line('email');?></label>
											<input type="text" id="email" name="email" placeholder="<?php echo $this->lang->line('email');?>"/>
											<?php echo form_error('email','<div class="alert alert-danger">','</div>'); ?>
											<button type="submit" name="submit" class="make_appointment_button"><?php echo $this->lang->line('resend_code');?></button>
										<?php echo form_close(); ?>  
									</div>
								</div>
							</div>
							<?php } ?>
					</div>
					<!---End-contact---->
				<div class="clear"> </div>
				</div>
			<!----End-content----->
		</div>
		<!---End-wrap---->
		

