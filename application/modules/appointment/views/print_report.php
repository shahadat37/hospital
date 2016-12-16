<head>
<style>
	.table-bordered{
		border-collapse:collapse;
	}
	.table-bordered > thead > tr > th,
	.table-bordered > tbody > tr > th,
	.table-bordered > tfoot > tr > th,
	.table-bordered > thead > tr > td,
	.table-bordered > tbody > tr > td,
	.table-bordered > tfoot > tr > td{
		border:1px solid #ddd;
	}
	.table > thead > tr > th,
	.table > tbody > tr > th,
	.table > tfoot > tr > th,
	.table > thead > tr > td,
	.table > tbody > tr > td,
	.table > tfoot > tr > td{
		padding:8px;
		line-height:1.42857143;
		vertical-align:top;
	}
</style>
</head>
<body onload="window.print();">
<?php
	$level = $this->session->userdata('category');
?>
<div id="page-inner">
	<div class="row">
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
</body>
