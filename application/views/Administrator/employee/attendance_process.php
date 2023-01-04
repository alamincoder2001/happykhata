<div id="attendance">
    <div class="row" style="border-bottom: 1px solid #ccc;padding: 3px 0;">
        <div class="col-md-10">
            <div class="form-group">
                <label for="date" class="col-md-1">Date</label>
                <div class="col-md-2">
                    <input type="date" class="form-control" v-model="employee.date" required @change="getAttendances">
                </div>
            </div>
        </div>
    </div>

    <div v-if="attendance.length > 0" style="display:none;margin-top:15px" v-bind:style="{display: attendance.length > 0 && show ? '' : 'none'}">
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
                                    <td width="25%">
                                        <input type="radio" :id="`present${employee.employee_id}`" :name="`attendance${employee.employee_id}`" value="present" v-model="employee.attendance" required> 
                                        <label :for="`present${employee.employee_id}`" style="cursor:pointer;">Present</label> &nbsp;&nbsp;
                                        
                                        <input type="radio" :id="`half_day${employee.employee_id}`" :name="`attendance${employee.employee_id}`" value="half_day" v-model="employee.attendance" required>
                                        <label :for="`half_day${employee.employee_id}`" style="cursor:pointer;">Half Day</label> &nbsp;&nbsp;
                                        
                                        <input type="radio" :id="`absent${employee.employee_id}`" :name="`attendance${employee.employee_id}`" value="absent" v-model="employee.attendance" required>
                                        <label :for="`absent${employee.employee_id}`" style="cursor:pointer;">Absent</label>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4">
                                        <textarea class="form-control" placeholder="Comment" v-model="employee.note"></textarea>
                                    </td>
                                    <td style="text-align: left;"><button class="btn btn-success">Save</button></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        
        </form>
    </div>

    <div class="row" v-if="attendance.length == 0 && show">
        <div class="col-md-12 text-center">
            No record found
        </div>
    </div>
</div>

<script src="<?php echo base_url();?>assets/js/vue/vue.min.js"></script>
<script src="<?php echo base_url();?>assets/js/vue/axios.min.js"></script>
<script src="<?php echo base_url();?>assets/js/moment.min.js"></script>

<script>
    const app = new Vue({
        el: '#attendance',
        data: {
            employee: {
                id: null,
                date: moment().format('YYYY-MM-DD'),
                note: '',
            },
            attendance: [],
            show: false,
        },
        created() {
            this.getAttendances();
        },
        methods: {
            getAttendances() {
                axios.post('/get_attendance', {date: this.employee.date})
                .then(res => {
                    let attendance = res.data;
                    if(attendance.length > 0) {
                        let att = attendance[0];
                        this.employee.id = att.id;
                        this.employee.date = att.date;
                        this.employee.note = att.note;
    
                        this.attendance = att.details.map(employee => {
                            return {
                                employee_id: employee.employee_id,
                                employee_code: employee.Employee_ID,
                                employee_name: employee.Employee_Name,
                                designation: employee.Designation_Name,
                                attendance: employee.attendance,
                                daily_salary: employee.salary_range / 30
                            }
                        })
                    } else {
                        this.employee.id = null;
                        this.getEmployees();
                    }

                    this.show = true;
                })
            },
            getEmployees() {
                axios.get('/get_employees')
                .then(res => {
                    this.attendance = res.data.map(employee => {
                        return {
                            employee_id: employee.Employee_SlNo,
                            employee_code: employee.Employee_ID,
                            employee_name: employee.Employee_Name,
                            designation: employee.Designation_Name,
                            attendance: '',
                            daily_salary: employee.daily_salary
                        }
                    })
                    this.show = true;
                })
            },
            saveAttendance() {
                let data = {
                    employee: this.employee,
                    attendance: this.attendance
                }
                
                let url = '/';
                if(this.employee.id != null) {
                    url = '/update_attendance';
                } else {
                    url = '/add_attendance';
                    delete this.employee.id
                }

                axios.post(url, data)
                .then(res => {
                    if(res.data.success) {
                        alert(res.data.message)
                        location.reload();
                    } else {
                        alert(res.data.message);
                    }
                })
            }
        }
    })
</script>