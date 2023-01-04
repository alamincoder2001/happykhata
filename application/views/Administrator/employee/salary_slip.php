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
<div id="salarySlip">
    <div class="row" style="border-bottom: 1px solid #ccc;padding: 3px 0;">
		<div class="col-md-10 col-md-offset-1">
			<form class="form-inline" @submit.prevent="getSalarySlip">

				<div class="form-group">
					<label>Employee</label>
					<v-select v-bind:options="employees" v-model="selectedEmployee" label="Employee_Name"></v-select>
				</div>

				<div class="form-group">
                    <input type="date" class="form-control" v-model="dateFrom">
				</div>

				<div class="form-group">
					<input type="date" class="form-control" v-model="dateTo">
				</div>

				<div class="form-group" style="margin-top: -5px;">
					<input type="submit" value="Search">
				</div>
			</form>
		</div>
	</div>

    <div class="row" style="margin-top: 15px;display: none" :style="{display:  records.length > 0  ? '': 'none'}">
        <div class="col-md-10 col-md-offset-1" style="margin-bottom: 10px;">
            <a href="" @click.prevent="print"><i class="fa fa-print"></i> Print</a>
        </div>

        <div class="col-md-10 col-md-offset-1">
            <div class="table-responsive" id="reportContent">
                <table class="record-table" style="display: none;" :style="{display: records.length > 0 ? '' : 'none'}">
                    <thead>
                        <th>Serial</th>
                        <th>Emp. Id</th>
                        <th>Emp. Name</th>
                        <th>Designation</th>
						<th>Present</th>
						<th>Half Day</th>
						<th>Absent</th>
                        <th>Bonus</th>
                        <th>Deductions</th>
                        <th>Salary Total</th>
                        <th>Payment</th>
                    </thead>

                    <tbody>
                        <tr v-for="(record, sl) in records">
                            <td style="text-align: center;">{{ sl + 1 }}</td>
                            <td style="text-align: center;">{{ record.Employee_ID }}</td>
							<td style="text-align: center;">{{ record.Employee_Name }}</td>
							<td style="text-align: center;">{{ record.Designation_Name }}</td>
							<td style="text-align: center;">{{ record.present }}</td>
							<td style="text-align: center;">{{ record.half_day }}</td>
							<td style="text-align: center;">{{ record.absent }}</td>
                            <td style="text-align: center;">{{ record.bonus }}</td>
                            <td style="text-align: center;">{{ record.deductions }}</td>
                            <td style="text-align: center;">{{ record.total }}</td>
                            <td style="text-align: center;">{{ record.payment }}</td>
                        </tr>
						<tr style="text-align:center; font-weight:bold">
							<td colspan="7" style="text-align:right">Total</td>
							<td>{{ records.reduce((prev, curr) => { return prev + +curr.bonus},0)  }} </td>
							<td>{{ records.reduce((prev, curr) => { return prev + +curr.deductions},0)  }} </td>
							<td>{{ records.reduce((prev, curr) => { return prev + +curr.total},0)  }} </td>
							<td>{{ records.reduce((prev, curr) => { return prev + +curr.payment},0)  }} </td>
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
        el: '#salarySlip',
        data: {
            dateFrom: moment().format('YYYY-MM-DD'),
            dateTo: moment().format('YYYY-MM-DD'),
            records: [],
            employees: [],
            selectedEmployee: null
        },
        created() {
            this.getEmployees();
        },
        methods: {
            getEmployees(){
				axios.get('/get_employees')
				.then(res => {
					this.employees = res.data;
				})
			},
            getSalarySlip() {
                let filter = {
                    dateFrom: this.dateFrom,
                    dateTo: this.dateTo,
					employeeId: this.selectedEmployee == null ? null : this.selectedEmployee.Employee_SlNo
                }

                axios.post('/get_salary_report', filter)
                .then(res => {
                    this.records = res.data.map(item => {
                        let present = (+item.present * +item.daily_salary);
                        let half = (+item.half_day * (+item.daily_salary / 2));
                        item.total = (+present + +half).toFixed(2);
                        return item;
                    })
                })
            },
            async print(){
				let dateText = '';
				if(this.dateFrom != '' && this.dateTo != ''){
					dateText = `Statement from <strong>${this.dateFrom}</strong> to <strong>${this.dateTo}</strong>`;
				}

				let reportContent = `
					<div class="container">
						<div class="row">
							<div class="col-xs-12 text-center">
								<h3>Attendance Record</h3>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-6">
							</div>
							<div class="col-xs-6 text-right">
								${dateText}
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