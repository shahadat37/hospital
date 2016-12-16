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
<div id="page-inner">
	<h1>Bill Report</h1>
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-primary">
				<div class="panel-body">
					<div class="table-responsive">
						<table class="table table-striped table-bordered table-hover" id="bill_table">			
							<thead>
								<tr>
									<th><?php echo $this->lang->line("bill")." ".$this->lang->line("date");?></th>
									
									<th><?php echo $this->lang->line("doctor") . ' ' . $this->lang->line("name");?></th>
									<th><?php echo $this->lang->line("patient_id");?></th>
									<th><?php echo $this->lang->line("patient_name");?></th>
									<th><?php echo $this->lang->line("bill")." ".$this->lang->line("no");?></th>
									
									<th style="text-align:right;"><?php echo $this->lang->line('bill') . ' ' . $this->lang->line('amount');?></th>
									<th style="text-align:right;"><?php echo $this->lang->line('payment_amount');?></th>
									<th style="text-align:right;">Due Amount</th>
								</tr>
							</thead>
							<tbody>
							<?php $bill_amt=0; $pay_amt=0; $due_amt=0;?>
							<?php if ($reports) { ?>
							<?php foreach ($reports as $report) { ?>
								<tr>
									<?php $bill_date = date('d-m-Y',strtotime($report['bill_date'])); ?>
									<td><?php echo $bill_date; ?></td>
                                    
									<td><?php echo $report['doctor_name']; ?></td>
									<td><?php echo $report['display_id']; ?></td>
                                    <td><?php echo $report['first_name'] . ' ' .$report['middle_name'] . ' ' . $report['last_name'] ?></td>
                                    <td><?php echo $report['bill_id']; ?></td>
									
                                    <td style="text-align:right;"><?php 
											echo currency_format($report['total_amount']);
											if($currency_postfix) echo $currency_postfix['currency_postfix'];
											
											$bill_amt=$bill_amt+$report['total_amount'];
										?></td>
									<td style="text-align:right;"><?php 
											echo currency_format($report['pay_amount']);
											if($currency_postfix) echo $currency_postfix['currency_postfix']; 
											
											$pay_amt=$pay_amt+$report['pay_amount'];
										?>
									</td>
									<td style="text-align:right;"><?php 
											echo currency_format($report['total_amount'] - $report['pay_amount']);
											if($currency_postfix) echo $currency_postfix['currency_postfix']; 
											
											$due_amt=$due_amt+$report['total_amount'] - $report['pay_amount'];
										?></td>
                                </tr>
								<?php } ?>
								<script>
									$(window).load(function() {
										$('#bill_table').dataTable();
									});
								</script>
								
								<?php }else{ ?>
									<tr>
										<td colspan="7">No Bills found for selected parameters</td>
									</tr>
								<?php } ?>
							</tbody>
							<thead>
								<tr>
									<th></th>									
									<th></th>
									<th></th>
									<th></th>
									<th></th>									
									<th style="text-align:right;"><?php echo currency_format($bill_amt); if($currency_postfix) echo $currency_postfix['currency_postfix']; ?></th>
									<th style="text-align:right;"><?php echo currency_format($pay_amt); if($currency_postfix) echo $currency_postfix['currency_postfix']; ?></th>
									<th style="text-align:right;"><?php echo currency_format($due_amt); if($currency_postfix) echo $currency_postfix['currency_postfix']; ?></th>
								</tr>
							</thead>
						</table>
					</div>	
				</div>
			</div>
		</div>
	</div>
</div>
</body>
