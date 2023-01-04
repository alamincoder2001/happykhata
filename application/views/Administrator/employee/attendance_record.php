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

    <div class="row" style="margin-top: 15px;">
        <div class="col-md-10 col-md-offset-1" style="margin-bottom: 10px;">
            <a href="" @click.prevent="print"><i class="fa fa-print"></i> Print</a>
        </div>

        <div class="col-md-10 col-md-offset-1">
            <div class="table-responsive" id="reportContent">
                <table class="record-table" style="display: none" :style="{display: searchType == '' ? '' : 'none'}">
                    <thead>
                        <th width="5%">Serial</th>
                        <th width="12%">Date</th>
						<th>Present</th>
						<th>Half Day</th>
						<th>Absent</th>
                        <th>Comments</th>
                        <th width="12%">Save By</th>
                        <th width="10%">Action</th>
                    </thead>

                    <tbody>
                        <tr v-for="(record, sl) in records">
                            <td style="text-align: center;">{{ sl + 1 }}</td>
                            <td style="text-align: center;">{{ record.date }}</td>
							<td style="text-align: center;">{{ record.details.filter(item => item.attendance == 'present').length }}</td>
							<td style="text-align: center;">{{ record.details.filter(item => item.attendance == 'half_day').length }}</td>
							<td style="text-align: center;">{{ record.details.filter(item => item.attendance == 'absent').length }}</td>
                            <td>{{ record.note }}</td>
                            <td style="text-align: center;">{{ record.added_by }}</td>
                            <td style="text-align: center;">
                                <a :href="`attendance-invoice/${record.id}`" title="View Details"><i class="fa fa-file-o"></i></a>
                            </td>
                        </tr>
                    </tbody>
                </table>

				<table 
					class="record-table" 
					style="display: none;"
					:style="{display: searchType == 'employee' ? '': 'none'}">
                    <thead>
                        <th>Serial</th>
                        <th>Date</th>
						<th>Employee Id</th>
						<th>Employee Name</th>
						<th>Designation</th>
						<th>Attendence</th>
                        <th>Save By</th>
                    </thead>

                    <tbody>
                        <tr v-for="(record, sl) in details">
                            <td style="text-align: center;">{{ sl + 1 }}</td>
                            <td style="text-align: center;">{{ record.entry_date }}</td>
                            <td style="text-align: center;">{{ record.Employee_ID }}</td>
                            <td style="text-align: center;">{{ record.Employee_Name }}</td>
                            <td style="text-align: center;">{{ record.Designation_Name }}</td>
                            <td style="text-align: center;">{{ record.attendance }}</td>
                            <td style="text-align: center;">{{ record.added_by }}</td>
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
            dateFrom: moment().format('YYYY-MM-DD'),
            dateTo: moment().format('YYYY-MM-DD'),
            records: [],
			details: [],
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
                let filter = {
                    dateFrom: this.dateFrom,
                    dateTo: this.dateTo,
					employeeId: this.selectedEmployee == null ? null : this.selectedEmployee.Employee_SlNo
                }

				if(this.searchType == 'employee') {
					axios.post('/get_attendance_details', filter)
					.then(res => {
						this.details = res.data;
					})

				} else {
					axios.post('/get_attendance', filter)
					.then(res => {
						this.records = res.data;
					})
				}

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

                let rows = reportWindow.document.querySelectorAll('.record-table tr');
                rows.forEach(row => {
                    row.lastChild.remove();
                })


				reportWindow.focus();
				await new Promise(resolve => setTimeout(resolve, 1000));
				reportWindow.print();
				reportWindow.close();
			}
        }
    })
</script>