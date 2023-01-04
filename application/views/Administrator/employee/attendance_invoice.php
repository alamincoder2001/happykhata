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

<div id="attendance">
    <div class="row">
        <div class="col-md-12" style="margin-bottom: 10px;">
            <a href="" @click.prevent="print"><i class="fa fa-print"></i> Print</a>
        </div>

        <div class="col-md-12">
            <div class="table-responsive" id="reportContent">
                <table class="record-table">
                    <thead>
                        <th>SL</th>
                        <th>Date</th>
                        <th>Employee Id</th>
                        <th>Employee Name</th>
                        <th>Designation</th>
                        <th>Attendance</th>
                        <th>Daily Salary</th>
                        <th>Save By</th>
                    </thead>
                    <tbody>
                        <tr v-for="(employee, sl) in attendances">
                            <td style="text-align: center;">{{ sl + 1 }}</td>
                            <td style="text-align: center;">{{ employee.date }}</td>
                            <td>{{ employee.Employee_ID }}</td>
                            <td>{{ employee.Employee_Name }}</td>
                            <td>{{ employee.Designation_Name }}</td>
                            <td>{{ employee.attendance }}</td>
                            <td style="text-align: right;">{{ employee.salary }}</td>
                            <td>{{ employee.added_by }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo base_url();?>assets/js/vue/vue.min.js"></script>
<script src="<?php echo base_url();?>assets/js/vue/axios.min.js"></script>

<script>
    const app = new Vue({
        el: '#attendance',
        data: {
            attId: '<?php echo $attId?>',
            attendances: []
        },
        created() {
            this.getAttendances();
        },
        methods: {
            getAttendances() {
                axios.post('/get_attendance', {attId: this.attId})
                .then(res => {
                    this.attendances = res.data[0].details;

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