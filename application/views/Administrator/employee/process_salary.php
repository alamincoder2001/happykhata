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

<div id="processSalary">
    <div class="row" style="margin-top: 15px; margin-bottom: 15px;">
        <div class="col-md-12" style="padding-bottom:15px; border-bottom:1px #ccc solid;">
            <form id="searchForm" class="form-inline" @submit.prevent="generateSalary">
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" class="form-control" v-model="processingDate">
                </div>
                <div class="form-group">
                    <label>Month</label>
                    <v-select :options="months" label="month_name" v-model="selectedMonth"></v-select>
                </div>
                <div class="form-group" style="margin-top: -5px;">
					<input type="submit" value="Generate">
				</div>
            </form>
        </div>
    </div>

    <div v-if="salarySheets.length > 0" style="display:none;" v-bind:style="{ display: salarySheets.length > 0 ? '' : 'none'}">
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive" id="printContent">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Sl</th>
                                <th>Month Name</th>
                                <th>Total Amount</th>
                                <th>Processed Date</th>
                                <th>Processed By</th>
                                <th>View</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(salarySheet, ind) in salarySheets">
                                <td>{{ ind + 1 }}</td>
                                <td style="text-align:left;">{{ salarySheet.month_name }}</td>
                                <td style="text-align:right;">{{ salarySheet.total_amount }}</td>
                                <td style="text-align:center;">{{ salarySheet.process_date }}</td>
                                <td style="text-align:center;">{{ salarySheet.process_by }}</td>
                                <td>
                                    <a href="" @click.prevent="viewSalarySheet(salarySheet.salary_sheet_id)" class="btn btn-info btn-xs" title="View">
                                        <i class="fa fa-file"></i>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
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
        el: '#processSalary',
        
        filters: {
            decimal(value) {
                return value == null ? '0.00' : parseFloat(value).toFixed(2);
            }
        },

        data() {
            return {
                processingDate: moment().format('YYYY-MM-DD'),
                months: [],
                selectedMonth: null,
                salarySheets: []
            }
        },

        created() {
            this.getMonths();
            this.getSalarySheets();
        },

        methods: {
            getMonths() {
                axios.get('/get_months').then(res => {
                    this.months = res.data;
                })
            },

            generateSalary() {
                if(this.selectedMonth == null) {
                    alert('Select month');
                    return;
                }
                axios.post('/generate_salary', { 
                    processing_date: this.processingDate, 
                    month_id: this.selectedMonth.month_id 
                })
                .then(res => {
                    let r = res.data;
                    alert(r.message);
                    if(r.success) {
                        this.viewSalarySheet(r.salarySheetId);
                        this.getSalarySheets();
                    }
                })
                .catch(error => alert(error.message));
            },

            getSalarySheets() {
                axios.get('/get_salary_sheet').then(res => {
                    this.salarySheets = res.data.salarySheets;
                })
            },

            viewSalarySheet(salarySheetId) {
                window.open(`/salary_sheet/${salarySheetId}`, 'PRINT', `height=${screen.height}, width=${screen.width}`);
            },
        }
    })
</script>