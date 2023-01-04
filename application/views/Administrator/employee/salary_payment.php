<style>
	.v-select {
		margin-bottom: 5px;
	}

	.v-select.open .dropdown-toggle {
		border-bottom: 1px solid #ccc;
	}

	.v-select .dropdown-toggle {
		padding: 0px;
		height: 25px;
	}

	.v-select input[type=search],
	.v-select input[type=search]:focus {
		margin: 0px;
	}

	.v-select .vs__selected-options {
		overflow: hidden;
		flex-wrap: nowrap;
	}

	.v-select .selected-tag {
		margin: 2px 0px;
		white-space: nowrap;
		position: absolute;
		left: 0px;
	}

	.v-select .vs__actions {
		margin-top: -5px;
	}

	.v-select .dropdown-menu {
		width: auto;
		overflow-y: auto;
	}

	#salarypayment label {
		font-size: 13px;
	}

	#salarypayment select {
		border-radius: 3px;
	}

	#salarypayment .add-button {
		padding: 2.5px;
		width: 28px;
		background-color: #298db4;
		display: block;
		text-align: center;
		color: white;
	}

	#salarypayment .add-button:hover {
		background-color: #41add6;
		color: white;
	}
</style>
<div id="salarypayment">
	<form @submit.prevent="savePayment">
		<div class="row" style="margin-top: 10px;margin-bottom:15px;border-bottom: 1px solid #ccc;padding-bottom: 15px;">
			<div class="col-md-5 col-md-offset-1">
				<div class="form-group">
					<label class="control-label col-md-4">Employee</label>
					<div class="col-md-7">
						<v-select v-bind:options="employees" label="display_name" v-model="selectedEmployee"></v-select>
					</div>
					<div class="col-md-1" style="padding:0;margin-left: -15px;"><a href="/employee" target="_blank" class="add-button"><i class="fa fa-plus"></i></a></div>
				</div>

				<div class="form-group">
					<label class="control-label col-md-4">Select Type</label>
					<div class="col-md-7">
						<select name="type" id="type" class="form-control" v-model="payment.type" style="padding: 0px;">
                            <option value="payment">Payment</option>
                            <option value="bonus">Bonus</option>
                            <option value="deduct">Deductions</option>
                        </select>
					</div>
				</div>

				<div class="form-group">
					<label class="control-label col-md-4">Payable Amount</label>
					<div class="col-md-7">
						<input type="text" class="form-control" v-model="payable_amount" disabled>
					</div>
				</div>
			</div>
			<div class="col-md-5">
				<div class="form-group">
					<label class="control-label col-md-4">Date</label>
					<div class="col-md-7">
						<input type="date" class="form-control" v-model="payment.date"  @change="getPayments">
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-4">Description</label>
					<div class="col-md-7">
						<input type="text" class="form-control" v-model="payment.note">
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-4">Payment Amount</label>
					<div class="col-md-7">
						<input type="number" step="0.01" class="form-control" v-model="payment.amount">
					</div>
				</div>
				<div class="form-group">
					<div class="col-md-7 col-md-offset-4 text-right">
						<input type="submit" value="Save" class="btn btn-success btn-sm">
						<input type="button" value="Cancel" class="btn btn-danger btn-sm" @click="resetForm">
					</div>
				</div>
			</div>
		</div>
	</form>

	<div class="row">
		<div class="col-sm-12 form-inline">
			<div class="form-group">
				<label for="filter" class="sr-only">Filter</label>
				<input type="text" class="form-control" v-model="filter" placeholder="Filter">
			</div>
		</div>
		<div class="col-md-12">
			<div class="table-responsive">
				<datatable :columns="columns" :data="payments" :filter-by="filter">
					<template scope="{ row }">
						<tr>
							<td>{{ row.date }}</td>
							<td>{{ row.Employee_Id }}</td>
							<td>{{ row.Employee_Name }}</td>
							<td>{{ row.type }}</td>
							<td>{{ row.note }}</td>
							<td>{{ row.amount }}</td>
							<td>
								<?php if ($this->session->userdata('accountType') != 'u') { ?>
									<button type="button" class="button edit" @click="editPayment(row)">
										<i class="fa fa-pencil"></i>
									</button>
									<button type="button" class="button" @click="deletePayment(row.id)">
										<i class="fa fa-trash"></i>
									</button>
								<?php } ?>
							</td>
						</tr>
					</template>
				</datatable>
				<datatable-pager v-model="page" type="abbreviated" :per-page="per_page"></datatable-pager>
			</div>
		</div>
	</div>
</div>

<script src="<?php echo base_url(); ?>assets/js/vue/vue.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/vue/axios.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/vue/vuejs-datatable.js"></script>
<script src="<?php echo base_url(); ?>assets/js/vue/vue-select.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/moment.min.js"></script>

<script>
	Vue.component('v-select', VueSelect.VueSelect);
	new Vue({
		el: '#salarypayment',
		data() {
			return {
				payment: {
					id: null,
					employeeId: null,
					date: moment().format('YYYY-MM-DD'),
					note: '',
					amount: 0,
				},
				payments: [],
				employees: [],
				selectedEmployee: null,
				payable_amount: 0.00,

				columns: [
                    { label: 'Date', field: 'date', align: 'center', filterable: false },
                    { label: 'Employee Id', field: 'Employee_ID', align: 'center' },
                    { label: 'Employee Name', field: 'Employee_Name', align: 'center' },
                    { label: 'Type', field: 'type', align: 'center' },
                    { label: 'Description', field: 'note', align: 'center' },
                    { label: 'Amount', field: 'amount', align: 'center' },
                    { label: 'Action', align: 'center', filterable: false }
                ],
                page: 1,
                per_page: 10,
                filter: ''
			}
		},
		watch: {
			selectedEmployee(employee) {
				if(employee == undefined) return;
				this.payment.employeeId = employee.Employee_SlNo;
				this.getPayableSalary();
			}
		},
		created() {
			this.getEmployees();
			this.getPayments();
		},
		methods: {
			getPayments() {
				let filter = {
					dateFrom: this.payment.date,
					dateTo: this.payment.date,
				}
				axios.post('/get_salary_payment', filter)
				.then(res => {
					this.payments = res.data;
				})
			},
			getEmployees() {
				axios.get('/get_employees').then(res => {
					this.employees = res.data;
				})
			},
			getPayableSalary() {
				if (this.selectedEmployee == null) {
					return;
				}

				axios.post('/get_employee_due', {employeeId: this.selectedEmployee.Employee_SlNo})
				.then(res => {
					this.payable_amount = res.data[0].due;
				})
			},
			savePayment() {
				if(this.selectedEmployee == null){
					alert('Select employee');
					return;
				}

				let url = '';
				if(this.payment.id != null){
					url = '/update_salary_payment';
				} else {
					url = '/add_salary_payment';
					delete this.payment.id
				}

				axios.post(url, this.payment)
				.then(res => {
					let r = res.data;
					
					if(r.success){
						alert(r.message);
						this.resetForm();
						this.getPayments();
					} else{
						alert(r.message);
					}
				})
				.catch(error => alert(error.response.statusText))
			},
			editPayment(payment){
				let keys = Object.keys(this.payment);
				keys.forEach(key => this.payment[key] = payment[key]);
				this.payment.type = payment.type;

				this.selectedEmployee = this.employees.find(item => item.Employee_SlNo == payment.employee_id)

			},
			deletePayment(paymentId){
				let confirmation = confirm('Are you sure?');
				if(confirmation == false){
					return;
				}
				axios.post('/delete_salary_payment', {paymentId: paymentId})
				.then(res => {
					let r = res.data;
					if(r.success){
						alert(r.message);
						this.getPayments();
					} else {
						alert(r.message);
					}
				})
			},
			resetForm(){
				this.payment = {
					id: null,
					employeeId: null,
					date: moment().format('YYYY-MM-DD'),
					note: '',
					amount: '',
					type: ''
				}

				this.payable_amount = 0.00;
				this.selectedEmployee = null;
			}
		}
	})
</script>