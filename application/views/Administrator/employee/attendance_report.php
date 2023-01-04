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
<div id="attendanceReport">
    <div class="row">
		<div class="col-xs-12 col-md-12 col-lg-12" style="border-bottom:1px #ccc solid;">
			<form class="form-inline" id="searchForm" v-on:submit.prevent="getAttendance">
				<div class="form-group">
					<label> Date </label>
					<input type="date" class="form-control" v-model="attendanceDate">
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
							<th style="text-align:center">Employee Id</th>
							<th style="text-align:center">Employee Name</th>
							<th style="text-align:center">Designation</th>
							<th style="text-align:center">Attendance</th>
						</tr>
					</thead>
					<tbody>
                        <tr v-for="(employee, ind) in attendance">
                            <td>{{ ind + 1 }}</td>
                            <td>{{ employee.employee_code }}</td>
                            <td>{{ employee.employee_name }}</td>
                            <td>{{ employee.designation }}</td>
                            <td>{{ employee.attendance }}</td>
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

<script>
	new Vue({
		el: '#attendanceReport',
		data(){
			return {
                attendanceDate: moment().format('YYYY-MM-DD'),
                attendance: []
			}
		},
		created(){
            this.getAttendance();
		},
		methods:{
            getAttendance() {
                axios.post('/get_employee_attendance', { 
                    attendanceDate: this.attendanceDate, 
                }).then(res => {
                    this.attendance = res.data;
                });
            },

            async print(){
				let reportContent = `
					<div class="container">
						<div class="row">
							<div class="col-xs-12 text-center">
                                <h3>Attendance List</h3>
                                <p>${moment(this.attendanceDate).format('dddd, MMMM DD YYYY')}</p>
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