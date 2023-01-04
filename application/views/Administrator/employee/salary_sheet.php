<div id="salarySheet">
    <div v-if="salaryDetails.length > 0" style="display:none;" v-bind:style="{ display: salaryDetails.length > 0 ? '' : 'none'}">
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive" id="printContent">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Sl</th>
                                <th>Employee Id</th>
                                <th>Employee Name</th>
                                <th>Department</th>
                                <th>Designation</th>
                                <th>Working Days</th>
                                <th>Present</th>
                                <th>Half Day</th>
                                <th>Absent</th>
                                <th>Salary</th>
                                <th>Paid</th>
                                <th>Deducted</th>
                                <th>Payable</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(salaryDetail, ind) in salaryDetails">
                                <td>{{ ind + 1 }}</td>
                                <td style="text-align:left;">{{ salaryDetail.employee_code }}</td>
                                <td style="text-align:left;">{{ salaryDetail.employee_name }}</td>
                                <td style="text-align:left;">{{ salaryDetail.department }}</td>
                                <td style="text-align:left;">{{ salaryDetail.designation }}</td>
                                <td style="text-align:center;">{{ salaryDetail.working_days }}</td>
                                <td style="text-align:center;">{{ salaryDetail.presences }}</td>
                                <td style="text-align:center;">{{ salaryDetail.half_days }}</td>
                                <td style="text-align:center;">{{ salaryDetail.absences }}</td>
                                <td style="text-align:right;">{{ salaryDetail.salary | decimal }}</td>
                                <td style="text-align:right;">{{ salaryDetail.paid | decimal }}</td>
                                <td style="text-align:right;">{{ salaryDetail.deducted | decimal }}</td>
                                <td style="text-align:right;">{{ salaryDetail.payable | decimal }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="9" style="text-align:right;">Total</td>
                                <td style="text-align:right;">{{ total.totalSalary | decimal }}</td>
                                <td style="text-align:right;">{{ total.totalPaid | decimal }}</td>
                                <td style="text-align:right;">{{ total.totalDeducted | decimal }}</td>
                                <td style="text-align:right;">{{ total.totalPayable | decimal }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo base_url();?>assets/js/vue/vue.min.js"></script>
<script src="<?php echo base_url();?>assets/js/vue/axios.min.js"></script>
<script src="<?php echo base_url();?>assets/js/moment.min.js"></script>

<script>
    new Vue({
        el: '#salarySheet',
        
        filters: {
            decimal(value) {
                return value == null ? '0.00' : parseFloat(value).toFixed(2);
            }
        },

        data() {
            return {
                salarySheetId: parseInt('<?php echo $salarySheetId;?>'),
                salarySheet: {},
                salaryDetails: []
            }
        },

        computed: {
            total() {
                return {
                    totalSalary: this.salaryDetails.reduce((p, c) => p + +c.salary, 0),
                    totalPaid: this.salaryDetails.reduce((p, c) => p + +c.paid, 0),
                    totalDeducted: this.salaryDetails.reduce((p, c) => p + +c.deducted, 0),
                    totalPayable: this.salaryDetails.reduce((p, c) => p + +c.payable, 0),
                }
            }
        },

        async created() {
            await this.getSalarySheet();
            await this.print();
        },

        methods: {
            async getSalarySheet() {
                await axios.post('/get_salary_sheet', { id: this.salarySheetId }).then(res => {
                    this.salarySheet = res.data.salarySheets[0];
                    this.salaryDetails = res.data.salaryDetails;
                })
            },

            async print() {
                let printContent = `
					<div class="container">
						<div class="row">
							<div class="col-xs-12 text-center">
								<h3>Salary Sheet</h3>
                                <p>${this.salarySheet.month_name}</p>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								${document.querySelector('#printContent').innerHTML}
							</div>
						</div>
					</div>
				`;

				var printWindow = window.open('', 'PRINT', `height=${screen.height}, width=${screen.width}`);
				printWindow.document.write(`
					<?php $this->load->view('Administrator/reports/reportHeader.php');?>
				`);

				printWindow.document.body.innerHTML += printContent;

				printWindow.focus();
				await new Promise(resolve => setTimeout(resolve, 1000));
				printWindow.print();
				printWindow.close();
            },
            
        }
    })
</script>
