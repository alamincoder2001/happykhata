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
<div id="employeeAttendance">
    <div class="row" style="margin-top: 15px; margin-bottom: 15px;" v-if="userType != 'u'">
        <div class="col-md-12" style="padding-bottom:15px; border-bottom:1px #ccc solid;">
            <form id="searchForm" class="form-inline">
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" class="form-control" v-model="attendance_date" @change="getAttendance">
                </div>
                <div class="form-group" style="display:none;">
                    <label>Month</label>
                    <v-select :options="months" label="month_name" v-model="selectedMonth"></v-select>
                </div>
            </form>
        </div>
    </div>

    <div v-if="attendance.length > 0" style="display:none;" v-bind:style="{display: attendance.length > 0 ? '' : 'none'}">
        <form @submit.prevent="saveAttendance">
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Sl</th>
                                    <th>Employee Id</th>
                                    <th>Employee Name</th>
                                    <th>Designation</th>
                                    <th>Attendance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(employee, sl) in attendance">
                                    <td>{{ sl + 1 }}</td>
                                    <td>{{ employee.employee_code }}</td>
                                    <td>{{ employee.employee_name }}</td>
                                    <td>{{ employee.designation }}</td>
                                    <td>
                                        <input type="radio" :id="`present${employee.employee_id}`" :name="`attendance${employee.employee_id}`" value="present" v-model="employee.attendance" required> 
                                        <label :for="`present${employee.employee_id}`" style="cursor:pointer;">Present</label> &nbsp;&nbsp;
                                        
                                        <input type="radio" :id="`half_day${employee.employee_id}`" :name="`attendance${employee.employee_id}`" value="half_day" v-model="employee.attendance" required>
                                        <label :for="`half_day${employee.employee_id}`" style="cursor:pointer;">Half Day</label> &nbsp;&nbsp;
                                        
                                        <input type="radio" :id="`absent${employee.employee_id}`" :name="`attendance${employee.employee_id}`" value="absent" v-model="employee.attendance" required>
                                        <label :for="`absent${employee.employee_id}`" style="cursor:pointer;">Absent</label>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        
            <div class="row">
                <div class="col-md-12 text-right">
                    <button class="btn btn-success">Save</button>
                </div>
            </div>
        </form>
    </div>

    <div class="row" v-if="attendance.length == 0">
        <div class="col-md-12 text-center">
            No record found
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
        el: '#employeeAttendance',

        data() {
            return {
                attendance_date: moment().format('YYYY-MM-DD'),
                attendance: [],
                months: [],
                selectedMonth: null,
                userType: '<?php echo $this->session->userdata("accountType");?>'
            }
        },

        async created() {
            await this.getMonths();
            await this.getAttendance();
        },

        methods: {
            async getMonths() {
                await axios.get('/get_months').then(res => {
                    this.months = res.data;
                })
            },

            getEmployees() {
                axios.get('/get_employees').then(res => {
                    this.attendance = res.data.map(employee => {
                        return {
                            employee_id: employee.Employee_SlNo,
                            employee_code: employee.Employee_ID,
                            employee_name: employee.Employee_Name,
                            designation: employee.Designation_Name,
                            attendance: '',
                        }
                    });
                })
            },

            saveAttendance() {
                if(this.selectedMonth == null) {
                    let monthName = moment(this.attendance_date).format('MMMM-YYYY');
                    alert(`Add month ${monthName}`);
                    return;
                }
                this.attendance = this.attendance.map(att => {
                    att.attendance_date = this.attendance_date;
                    att.month_id = this.selectedMonth.month_id;
                    return att;
                });

                axios.post('/add_employee_attendance', this.attendance).then(res => {
                    let r = res.data;
                    alert(r.message);
                });
            },

            async getAttendance() {
                let monthName = moment(this.attendance_date).format('MMMM-YYYY');
                if(monthName == 'Invalid date') {
                    return;
                }

                this.selectedMonth = this.months.find(m => m.month_name == monthName);

                let attendance = await axios.post('/get_employee_attendance', { attendanceDate: this.attendance_date }).then(res => {
                    return res.data;
                });

                if(attendance.length == 0) {
                    this.getEmployees();
                } else {
                    this.attendance = attendance;
                    this.selectedMonth = {
                        month_id: attendance[0].month_id,
                        month_name: attendance[0].month_name,
                    }
                }
            }
        }
    })
</script>