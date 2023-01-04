<style>
	.v-select{
		margin-bottom: 5px;
	}
	.v-select .dropdown-toggle{
		padding: 0px;
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
</style>

<div class="row" id="employeeDueList">
	<div class="col-xs-12 col-md-12 col-lg-12" style="border-bottom:1px #ccc solid;">
		<div class="form-group">
			<label class="col-sm-1 control-label no-padding-right">Search Type</label>
			<div class="col-sm-2">
				<select class="form-control" v-model="searchType" v-on:change="onChangeSearchType" style="padding:0px;">
					<option value="all">All</option>
					<option value="employee">By Employee</option>
				</select>
			</div>
		</div>
		<div class="form-group" style="display: none" v-bind:style="{display: searchType == 'all' ? 'none' : ''}">
			<label class="col-sm-2 control-label no-padding-right">Select Employee</label>
			<div class="col-sm-2">
				<v-select v-bind:options="employees" v-model="selectedEmployee" label="display_name" placeholder="Select employee"></v-select>
			</div>
		</div>

		<div class="form-group">
			<div class="col-sm-2">
				<input type="button" class="btn btn-primary" value="Show Report" v-on:click="getDues" style="margin-top:0px;border:0px;height:28px;">
			</div>
		</div>
	</div>

	<div class="col-md-12" style="display: none" v-bind:style="{display: dues.length > 0 ? '' : 'none'}">
		<a href="" style="margin: 7px 0;display:block;width:50px;" v-on:click.prevent="print">
			<i class="fa fa-print"></i> Print
		</a>
		<div class="table-responsive" id="reportTable">
			<table class="table table-bordered">
				<thead>
					<tr>
						<th>Employee Id</th>
						<th>Employee Name</th>
						<th>Designation</th>
						<th>Employee Mobile</th>
						<th>Total Bill</th>
						<th>Total Paid</th>
						<th>Total Deducted</th>
						<th>Due Amount</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="data in dues">
						<td>{{ data.Employee_ID }}</td>
						<td>{{ data.Employee_Name }}</td>
						<td>{{ data.Designation_Name }}</td>
						<td>{{ data.Employee_ContactNo }}</td>
						<td style="text-align:right">{{ parseFloat(data.payable_amount).toFixed(2) }}</td>
						<td style="text-align:right">{{ parseFloat(data.paid_amount).toFixed(2) }}</td>
						<td style="text-align:right">{{ parseFloat(data.deducted_amount).toFixed(2) }}</td>
						<td style="text-align:right">{{ parseFloat(data.due).toFixed(2) }}</td>
					</tr>
				</tbody>
				<tfoot>
					<tr style="font-weight:bold;">
						<td colspan="7" style="text-align:right">Total Due</td>
						<td style="text-align:right">{{ parseFloat(totalDue).toFixed(2) }}</td>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
</div>

<script src="<?php echo base_url();?>assets/js/vue/vue.min.js"></script>
<script src="<?php echo base_url();?>assets/js/vue/axios.min.js"></script>
<script src="<?php echo base_url();?>assets/js/vue/vue-select.min.js"></script>

<script>
	Vue.component('v-select', VueSelect.VueSelect);
	new Vue({
		el: '#employeeDueList',
		data(){
			return {
				searchType: 'all',
				employees: [],
				selectedEmployee: null,
				dues: [],
				totalDue: 0.00
			}
		},
		created(){

		},
		methods:{
			onChangeSearchType(){
				if(this.searchType == 'employee' && this.employees.length == 0){
					this.getEmployees();
				}
				if(this.searchType == 'all'){
					this.selectedEmployee = null;
				}
			},
			getEmployees(){
				axios.get('/get_employees').then(res => {
					this.employees = res.data;
				})
			},
			getDues(){
				if(this.searchType == 'employee' && this.selectedEmployee == null){
					alert('Select employee');
					console.log(this.selectedEmployee);
					return;
				}

				let employeeId = this.selectedEmployee == null ? null : this.selectedEmployee.Employee_SlNo;
				axios.post('/get_employee_due_list', {employeeId: employeeId}).then(res => {
					if(this.searchType == 'employee'){
						this.dues = res.data;
					} else {
						this.dues = res.data.filter(d => parseFloat(d.due) != 0);
					}
					this.totalDue = this.dues.reduce((prev, cur) => { return prev + parseFloat(cur.due) }, 0);
				})
			},
			async print(){
				let reportContent = `
					<div class="container">
						<h4 style="text-align:center">Employee due report</h4 style="text-align:center">
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