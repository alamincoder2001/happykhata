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
<div id="employeeAttendanceReport">
    <div class="row">
		<div class="col-xs-12 col-md-12 col-lg-12" style="border-bottom:1px #ccc solid;">
			<form class="form-inline" id="searchForm" v-on:submit.prevent="getAttendance">
                <div class="form-group">
                    <v-select :options="employees" label="display_name" v-model="selectedEmployee"></v-select>
                </div>

				<div class="form-group">
					<label> Date from </label>
					<input type="date" class="form-control" v-model="fromDate" @change="generateDates">
					<label> to </label>
					<input type="date" class="form-control" v-model="toDate" @change="generateDates">
				</div>

				<div class="form-group" style="margin-top: -5px;">
					<div class="col-sm-1">
						<input type="submit" value="Show">
					</div>
				</div>
			</div>
		</form>
    </div>
    
    <div class="row" v-if="attendance.length > 0" style="display:none;" v-bind:style="{ display: attendance.length > 0 ? '' : 'none' }">
        <div class="col-sm-12">
			<a href="" style="margin: 7px 0;display:block;width:50px;" v-on:click.prevent="print">
				<i class="fa fa-print"></i> Print
			</a>
			<div class="table-responsive" id="printContent">
				<table class="table table-bordered">
					<thead>
						<tr>
                            <th>Sl</th>
							<th style="text-align:center">Attendance Date</th>
							<th style="text-align:center">Week Day</th>
							<th style="text-align:center">Attendance</th>
						</tr>
					</thead>
					<tbody>
                        <tr v-for="(day, ind) in dates">
                            <td>{{ ind + 1 }}</td>
                            <td>{{ day.date }}</td>
                            <td>{{ day.weekDay }}</td>
							<td>{{ findAttendance(day.date) }}</td>
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
		el: '#employeeAttendanceReport',
		data(){
			return {
                fromDate: moment().format('YYYY-MM-DD'),
                toDate: moment().format('YYYY-MM-DD'),
				dates: [],
                employees: [],
                selectedEmployee: null,
                attendance: []
			}
		},
		created(){
			this.generateDates();
            this.getEmployees();
		},
		methods:{
            getEmployees() {
                axios.get('/get_employees').then(res => {
                    this.employees = res.data;
                })
            },

            getAttendance() {
                if(this.selectedEmployee == null) {
                    alert('Select employee');
                    return;
                }

                axios.post('/get_employee_attendance', { 
                    fromDate: this.fromDate, 
                    toDate: this.toDate, 
                    employeeId: this.selectedEmployee.Employee_SlNo
                }).then(res => {
                    this.attendance = res.data;
                });
            },

			findAttendance(date) {
				let attendance = this.attendance.find(att => att.attendance_date == date);
				if(attendance != undefined) {
					return attendance.attendance;
				} else {
					return null;
				}
			},

            generateDates() {
				this.attendance = [];
				this.dates = [];
                let firstDay = moment(this.fromDate).format('DD');
                let days = moment(this.toDate).diff(moment(this.fromDate), 'days');

				for(let i = 0; i <= days; i++) {
					this.dates.push({
						date: moment(this.fromDate).add(i, 'days').format('YYYY-MM-DD'),
						weekDay: moment(this.fromDate).add(i, 'days').format('dddd')
					})
				}
            },

            async print(){
				let reportContent = `
					<div class="container">
						<div class="row">
							<div class="col-xs-12 text-center">
                                <h2>Attendance Report</h2>
                            </div>
						</div>
					</div>
					<div class="container">
						<div class="row">
							<div class="col-xs-6">
								<strong>Employee Id:</strong> ${this.selectedEmployee.Employee_ID}<br>
								<strong>Name:</strong> ${this.selectedEmployee.Employee_Name}<br>
								<strong>Designation:</strong> ${this.selectedEmployee.Designation_Name}
							</div>

							<div class="col-xs-6 text-right">
								From: <strong>${this.fromDate}</strong> to <strong>${this.toDate}</strong>
							</div>
						</div>
					</div>
					<div class="container">
						<div class="row">
							<div class="col-xs-12">
								${document.querySelector('#printContent').innerHTML}
							</div>
						</div>
					</div>
				`;

				var printWindow = window.open('', 'PRINT', `width=${screen.width}, height=${screen.height}`);
				printWindow.document.write(`
					<?php $this->load->view('Administrator/reports/reportHeader.php');?>
				`);

				printWindow.document.body.innerHTML += reportContent;

				printWindow.focus();
				await new Promise(resolve => setTimeout(resolve, 1000));
				printWindow.print();
				printWindow.close();
			}
		}
	})
</script>