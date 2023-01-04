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
    .record-table{
		width: 100%;
		border-collapse: collapse;
	}
	.record-table thead{
		background-color: #0097df;
		color:white;
	}
	.record-table th, .record-table td{
		padding: 3px;
		border: 1px solid #454545;
	}
    .record-table th{
        text-align: center;
    }
</style>
<div id="attendances">
    <div class="row" style="border-bottom: 1px solid #ccc;padding: 3px 0;">
		<div class="col-md-10 col-md-offset-1">
			<form class="form-inline" @submit.prevent="getAttendances">
				<div class="form-group">
					<label for="type">Type</label>
					<select class="form-control" v-model="searchType" style="padding: 0px 10px;">
						<option value="">All</option>
						<option value="employee">By Employee</option>
					</select>
				</div>

				<div class="form-group" style="display:none;" v-bind:style="{display: searchType == 'employee' && employees.length > 0 ? '' : 'none'}">
					<label>Employee</label>
					<v-select v-bind:options="employees" v-model="selectedEmployee" label="Employee_Name"></v-select>
				</div>

				<div class="form-group" style="margin-top: -5px;">
					<input type="submit" value="Search">
				</div>
			</form>
		</div>
	</div>

    <div class="row" style="margin-top: 15px;display: none" :style="{display:  records.length > 0 ? '': 'none'}">
        <div class="col-md-10 col-md-offset-1" style="margin-bottom: 10px;">
            <a href="" @click.prevent="print"><i class="fa fa-print"></i> Print</a>
        </div>

        <div class="col-md-10 col-md-offset-1">
            <div class="table-responsive" id="reportContent">
                <table class="record-table" style="display: none;" :style="{display: records.length > 0 ? '' : 'none'}">
                    <thead>
                        <th width="5%">Serial</th>
						<th>Employee Id</th>
						<th>Employee Name</th>
						<th>Designation</th>
						<th>Bill</th>
						<th>Bonus</th>
						<th>Deductions</th>
						<th>Payment</th>
						<th>Due Amount</th>
                    </thead>

                    <tbody>
                        <tr v-for="(employee, sl) in records">
                            <td style="text-align: center;">{{ sl + 1 }}</td>
                            <td style="text-align: center;">{{ employee.Employee_ID }}</td>
                            <td style="text-align: center;">{{ employee.Employee_Name }}</td>
                            <td style="text-align: center;">{{ employee.Designation_Name }}</td>
                            <td style="text-align: center;">{{ employee.bill }}</td>
                            <td style="text-align: center;">{{ employee.bonus }}</td>
                            <td style="text-align: center;">{{ employee.deductions }}</td>
                            <td style="text-align: center;">{{ employee.payment }}</td>
                            <td style="text-align: center;">{{ employee.due }}</td>
                        </tr>
                        <tr>
                            <td colspan="4" style="text-align: right;"><strong>Total</strong></td>
                            <td style="text-align: center;"><strong>{{ records.reduce((p, c) => {return +p + +c.bill}, 0).toFixed(2) }}</strong></td>
                            <td style="text-align: center;"><strong>{{ records.reduce((p, c) => {return +p + +c.bonus}, 0).toFixed(2) }}</strong></td>
                            <td style="text-align: center;"><strong>{{ records.reduce((p, c) => {return +p + +c.deductions}, 0).toFixed(2) }}</strong></td>
                            <td style="text-align: center;"><strong>{{ records.reduce((p, c) => {return +p + +c.payment}, 0).toFixed(2) }}</strong></td>
                            <td style="text-align: center;"><strong>{{ records.reduce((p, c) => {return +p + +c.due}, 0).toFixed(2) }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script src="<?php echo base_url();?>assets/js/vue/vue.min.js"></script>
<script src="<?php echo base_url();?>assets/js/vue/axios.min.js"></script>
<script src="<?php echo base_url();?>assets/js/moment.min.js"></script>
<script src="<?php echo base_url();?>assets/js/vue/vue-select.min.js"></script>

<script>
	Vue.component('v-select', VueSelect.VueSelect);

    const app = new Vue({
        el: '#attendances',
        data: {
			searchType: '',
            records: [],
			employees: [],
			selectedEmployee: null
        },
        created() {
            // this.getAttendances();
			this.getEmployees();
        },
        methods: {
			getEmployees(){
				axios.get('/get_employees')
				.then(res => {
					this.employees = res.data;
				})
			},
            getAttendances() {
                if(this.searchType == 'employee') {
                    let filter = {
                        employeeId: this.selectedEmployee == null ? null : this.selectedEmployee.Employee_SlNo
                    }
                    axios.post('/get_employee_due', filter)
                    .then(res => {
                        this.records = res.data;
                    })
                } else {
                    this.selectedEmployee = null;
                    axios.post('/get_employee_due')
                    .then(res => {
                        this.records = res.data;
                    })
                }


            },
            async print(){

				let reportContent = `
					<div class="container">
						<div class="row">
							<div class="col-xs-12 text-center">
								<h3>Salary Due Report</h3>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								${document.querySelector('#reportContent').innerHTML}
							</div>
						</div>
					</div>
				`;

				var reportWindow = window.open('', 'PRINT', `height=${screen.height}, width=${screen.width}`);
				reportWindow.document.write(`
					<?php $this->load->view('Administrator/reports/reportHeader.php');?>
				`);

				reportWindow.document.head.innerHTML += `
					<style>
						.record-table{
							width: 100%;
							border-collapse: collapse;
						}
						.record-table thead{
							background-color: #0097df;
							color:white;
						}
						.record-table th, .record-table td{
							padding: 3px;
							border: 1px solid #454545;
						}
						.record-table th{
							text-align: center;
						}
					</style>
				`;
				reportWindow.document.body.innerHTML += reportContent;

				reportWindow.focus();
				await new Promise(resolve => setTimeout(resolve, 1000));
				reportWindow.print();
				reportWindow.close();
			}
        }
    })
</script>