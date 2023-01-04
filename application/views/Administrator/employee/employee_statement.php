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
<div id="employeePaymentReport">
	<div class="row">
		<div class="col-xs-12 col-md-12 col-lg-12" style="border-bottom:1px #ccc solid;">
			<form class="form-inline" id="searchForm" v-on:submit.prevent="getReport" >
				<div class="form-group">
					<label> Employee </label>
					<v-select v-bind:options="employees" v-model="selectedEmployee" label="display_name" placeholder="Select employee"></v-select>
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
	<div class="row" style="display:none;" v-bind:style="{display: showTable ? '' : 'none'}">
		<div class="col-sm-12">
			<a href="" style="margin: 7px 0;display:block;width:50px;" v-on:click.prevent="print">
				<i class="fa fa-print"></i> Print
			</a>
			<div class="table-responsive" id="reportTable">
				<table class="table table-bordered" v-if="payments.length > 0">
					<thead>
						<tr>
							<th style="text-align:center">Date</th>
							<th style="text-align:center">Description</th>
							<th style="text-align:center">Salary</th>
							<th style="text-align:center">Paid</th>
							<th style="text-align:center">Bonus</th>
							<th style="text-align:center">Deductions</th>
							<th style="text-align:center">Balance</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td></td>
							<td style="text-align:left;">Previous Balance</td>
							<td colspan="4"></td>
							<td style="text-align:right;">{{ parseFloat(previousBalance).toFixed(2) }}</td>
						</tr>
						<tr v-for="payment in payments">
							<td>{{ payment.date | dateFormat('DD-MM-YYYY') }}</td>
							<td style="text-align:left;">{{ payment.description }}</td>
							<td style="text-align:right;">{{ parseFloat(payment.bill).toFixed(2) }}</td>
							<td style="text-align:right;">{{ parseFloat(payment.paid).toFixed(2) }}</td>
							<td style="text-align:right;">{{ parseFloat(payment.bonus).toFixed(2) }}</td>
							<td style="text-align:right;">{{ parseFloat(payment.deductions).toFixed(2) }}</td>
							<td style="text-align:right;">{{ parseFloat(payment.balance).toFixed(2) }}</td>
						</tr>
					</tbody>
					<tfoot style="font-weight: bold;" v-if="payments.length > 0">
						<tr>
							<td colspan="2" style="text-align: right;">Total</td>
							<td style="text-align: right;">{{ payments.reduce((p, c) => { return +p + +c.bill }, 0).toFixed(2) }}</td>
							<td style="text-align: right;">{{ payments.reduce((p, c) => { return +p + +c.paid }, 0).toFixed(2) }}</td>
							<td style="text-align: right;">{{ payments.reduce((p, c) => { return +p + +c.bonus }, 0).toFixed(2) }}</td>
							<td style="text-align: right;">{{ payments.reduce((p, c) => { return +p + +c.deductions }, 0).toFixed(2) }}</td>
							<td style="text-align: right;"></td>
						</tr>
					</tfoot>
				</table>

				<table v-if="payments.length == 0">
					<tbody>
						<tr>
							<td>No records found</td>
						</tr>
					</tbody>
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
		el: '#employeePaymentReport',

		filters: {
			dateFormat(value, format) {
				return moment(value).format(format);
			}
		},

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
            this.getEmployees();
			let today = moment().format('YYYY-MM-DD');
			this.dateTo = today;
			this.dateFrom = moment().format('YYYY-MM-DD');
		},
		methods:{
			getEmployees(){
				axios.get('/get_employees').then(res => {
					this.employees = res.data;
				})
			},
			getReport(){
				if(this.selectedEmployee == null){
					alert('Select employee');
					return;
				}
				let data = {
					dateFrom: this.dateFrom,
					dateTo: this.dateTo,
					employeeId: this.selectedEmployee.Employee_SlNo
				}

				axios.post('/get_employee_ledgers', data).then(res => {
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
								<strong>Employee Id: </strong> ${this.selectedEmployee.Employee_ID}<br>
								<strong>Name: </strong> ${this.selectedEmployee.Employee_Name}<br>
								<strong>Department: </strong> ${this.selectedEmployee.Department_Name}<br>
								<strong>Designation: </strong> ${this.selectedEmployee.Designation_Name}<br>
							</div>
							<div class="col-xs-6 text-right">
								<strong>Statement from</strong> ${this.dateFrom} <strong>to</strong> ${this.dateTo} <br>
                                <strong>Basic Salary: </strong> ${this.selectedEmployee.salary_range}
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