<style>
	.v-select{
		margin-top:-2.5px;
        float: right;
        min-width: 180px;
        margin-left: 5px;
	}
	.v-select .dropdown-toggle{
		padding: 0px;
        height: 25px;
	}
	.v-select input[type=search], .v-select input[type=search]:focus{
		margin: 0px;
	}
	.v-select .vs__selected-options{
		overflow: hidden;
		flex-wrap:nowrap;
	}
	.v-select .selected-tag{
		margin: 2px 0px;
		white-space: nowrap;
		position:absolute;
		left: 0px;
	}
	.v-select .vs__actions{
		margin-top:-5px;
	}
	.v-select .dropdown-menu{
		width: auto;
		overflow-y:auto;
	}
	#searchForm select{
		padding:0;
		border-radius: 4px;
	}
	#searchForm .form-group{
		margin-right: 5px;
	}
	#searchForm *{
		font-size: 13px;
	}
</style>
<div id="salaryStatement">
	<div class="row">
		<div class="col-xs-12 col-md-12 col-lg-12" style="border-bottom:1px #ccc solid;">
			<form class="form-inline" id="searchForm" v-on:submit.prevent="getEmployeeLedger" >
				<div class="form-group">
					<label> Employee </label>
					<v-select v-bind:options="employees" v-model="selectedEmployee" label="Employee_Name" placeholder="Select employee"></v-select>
				</div>

				<div class="form-group">
					<label> Date from </label>
					<input type="date" class="form-control" v-model="dateFrom">
					<label> to </label>
					<input type="date" class="form-control" v-model="dateTo">
				</div>

				<div class="form-group" style="margin-top: -5px;">
					<div class="col-sm-1">
						<input type="submit" value="Show">
					</div>
				</div>
			</div>
		</form>
	</div>
	<div class="row" v-if="showTable" style="display:none;" v-bind:style="{display: showTable ? '' : 'none'}">
		<div class="col-sm-12">
			<a href="" style="margin: 7px 0;display:block;width:50px;" v-on:click.prevent="print">
				<i class="fa fa-print"></i> Print
			</a>
			<div class="table-responsive" id="reportTable">
				<table class="table table-bordered">
					<thead>
						<tr>
							<th style="text-align:center">Date</th>
							<th style="text-align:center">Description</th>
							<th style="text-align:center">Salary</th>
							<th style="text-align:center">Paid</th>
							<th style="text-align:center">Deducted</th>
							<th style="text-align:center">Balance</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td></td>
							<td style="text-align:left;">Previous Balance</td>
							<td colspan="3"></td>
							<td style="text-align:right;">{{ parseFloat(previousBalance).toFixed(2) }}</td>
						</tr>
						<template v-if="payments.length > 0">
							<tr v-for="payment in payments">
								<td>{{ payment.date }}</td>
								<td style="text-align:left;">{{ payment.description }}</td>
								<td style="text-align:right;">{{ parseFloat(payment.salary).toFixed(2) }}</td>
								<td style="text-align:right;">{{ parseFloat(payment.paid).toFixed(2) }}</td>
								<td style="text-align:right;">{{ parseFloat(payment.deducted).toFixed(2) }}</td>
								<td style="text-align:right;">{{ parseFloat(payment.balance).toFixed(2) }}</td>
							</tr>
						</template>
					</tbody>
					<tfoot style="font-weight: bold;" v-if="payments.length > 0">
						<tr>
							<td colspan="2" style="text-align: right;">Total</td>
							<td style="text-align: right;">{{ payments.reduce((p, c) => { return p + parseFloat(c.salary) }, 0).toFixed(2) }}</td>
							<td style="text-align: right;">{{ payments.reduce((p, c) => { return p + parseFloat(c.paid) }, 0).toFixed(2) }}</td>
							<td style="text-align: right;">{{ payments.reduce((p, c) => { return p + parseFloat(c.deducted) }, 0).toFixed(2) }}</td>
							<td style="text-align: right;">{{ payments[payments.length - 1].balance.toFixed(2) }}</td>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>
	</div>
</div>

<script src="<?php echo base_url();?>assets/js/vue/vue.min.js"></script>
<script src="<?php echo base_url();?>assets/js/vue/axios.min.js"></script>
<script src="<?php echo base_url();?>assets/js/vue/vue-select.min.js"></script>
<script src="<?php echo base_url();?>assets/js/moment.min.js"></script>

<script>
	Vue.component('v-select', VueSelect.VueSelect);
	new Vue({
		el: '#salaryStatement',
		data(){
			return {
				employees: [],
				selectedEmployee: null,
				dateFrom: null,
				dateTo: null,
				payments: [],
				previousBalance: 0.00,
				showTable: false
			}
		},
		created(){
			let today = moment().format('YYYY-MM-DD');
			this.dateTo = today;
			this.dateFrom = today;
			this.getEmployees();
		},
		methods:{
			getEmployees(){
				axios.get('/get_employees').then(res => {
					this.employees = res.data;
				})
			},
			getEmployeeLedger(){
				if(this.selectedEmployee == null){
					alert('Select employee');
					return;
				}
				let data = {
					dateFrom: this.dateFrom,
					dateTo: this.dateTo,
					employeeId: this.selectedEmployee.Employee_SlNo
				}

				this.showTable = false;

				axios.post('/get_employee_ledger', data).then(res => {
					this.payments = res.data.payments;
					this.previousBalance = res.data.previousBalance;
					this.showTable = true;
				})
			},
			async print(){
				let reportContent = `
					<div class="container">
						<h4 style="text-align:center">Employee payment report</h4 style="text-align:center">
						<div class="row">
							<div class="col-xs-6" style="font-size:12px;">
								<strong>Employee Code: </strong> ${this.selectedEmployee.Employee_ID}<br>
								<strong>Name: </strong> ${this.selectedEmployee.Employee_Name}<br>
								<strong>Designation: </strong> ${this.selectedEmployee.Designation_Name}<br>
								<strong>Address: </strong> ${this.selectedEmployee.Employee_PrasentAddress}<br>
								<strong>Mobile: </strong> ${this.selectedEmployee.Employee_ContactNo}<br>
							</div>
							<div class="col-xs-6 text-right">
								<strong>Statement from</strong> ${this.dateFrom} <strong>to</strong> ${this.dateTo}
							</div>
						</div>
					</div>
					<div class="container">
						<div class="row">
							<div class="col-xs-12">
								${document.querySelector('#reportTable').innerHTML}
							</div>
						</div>
					</div>
				`;

				var mywindow = window.open('', 'PRINT', `width=${screen.width}, height=${screen.height}`);
				mywindow.document.write(`
					<?php $this->load->view('Administrator/reports/reportHeader.php');?>
				`);

				mywindow.document.body.innerHTML += reportContent;

				mywindow.focus();
				await new Promise(resolve => setTimeout(resolve, 1000));
				mywindow.print();
				mywindow.close();
			}
		}
	})
</script>