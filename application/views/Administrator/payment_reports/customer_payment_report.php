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
<div id="customerPaymentReport">
	<div class="row">
		<div class="col-xs-12 col-md-12 col-lg-12" style="border-bottom:1px #ccc solid;">
			<form class="form-inline" id="searchForm" v-on:submit.prevent="getReport" >
				<div class="form-group">
					<label>Customer Type </label>
					<select class="form-control" v-model="customerType" v-on:change="onChangeType">
						<option value="">Select customer type</option>
						<option value="retail">Retail</option>
						<option value="wholesale">Wholesale</option>
					</select>
				</div>
				<div class="form-group">
					<label> Customer </label>
					<v-select v-bind:options="customers" v-model="selectedCustomer" label="display_name" placeholder="Select customer"></v-select>
				</div>

				<div class="form-group">
					<label> Date from </label>
					<input type="date" class="form-control" v-model="dateFrom">
					<label> to </label>
					<input type="date" class="form-control" v-model="dateTo">
				</div>

				<div class="form-group" style="margin-top: -5px;">
					<div class="col-sm-1">
						<input type="submit" value="Show">
					</div>
				</div>
			</div>
		</form>
	</div>
	<div class="row" style="display:none;" v-bind:style="{display: showTable ? '' : 'none'}">
		<div class="col-sm-12">
			<a href="" style="margin: 7px 0;display:block;width:50px;" v-on:click.prevent="print">
				<i class="fa fa-print"></i> Print
			</a>
			<div class="table-responsive" id="reportTable">
				<table class="table table-bordered" v-if="payments.length > 0">
					<thead>
						<tr>
							<th style="text-align:center">Date</th>
							<th style="text-align:center">Description</th>
							<th style="text-align:center">Bill</th>
							<th style="text-align:center">Paid</th>
							<th style="text-align:center">Inv.Due</th>
							<th style="text-align:center">Retruned</th>
							<th style="text-align:center">Paid to customer</th>
							<th style="text-align:center">Balance</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td></td>
							<td style="text-align:left;">Previous Balance</td>
							<td colspan="5"></td>
							<td style="text-align:right;">{{ parseFloat(previousBalance).toFixed(2) }}</td>
						</tr>
						<tr v-for="payment in payments">
							<td>{{ payment.date | dateFormat('DD/MM/YYYY') }}</td>
							<td style="text-align:left;">{{ payment.description }}</td>
							<td style="text-align:right;">{{ parseFloat(payment.bill).toFixed(2) }}</td>
							<td style="text-align:right;">{{ parseFloat(payment.paid).toFixed(2) }}</td>
							<td style="text-align:right;">{{ parseFloat(payment.due).toFixed(2) }}</td>
							<td style="text-align:right;">{{ parseFloat(payment.returned).toFixed(2) }}</td>
							<td style="text-align:right;">{{ parseFloat(payment.paid_out).toFixed(2) }}</td>
							<td style="text-align:right;">{{ parseFloat(payment.balance).toFixed(2) }}</td>
						</tr>
					</tbody>
					<tfoot style="font-weight: bold;">
						<tr>
							<td colspan="2" style="text-align: right;">Total</td>
							<td style="text-align: right;">{{ payments.reduce((p, c) => { return p + parseFloat(c.bill) }, 0).toFixed(2) }}</td>
							<td style="text-align: right;">{{ payments.reduce((p, c) => { return p + parseFloat(c.paid) }, 0).toFixed(2) }}</td>
							<td style="text-align: right;">{{ payments.reduce((p, c) => { return p + parseFloat(c.due) }, 0).toFixed(2) }}</td>
							<td style="text-align: right;">{{ payments.reduce((p, c) => { return p + parseFloat(c.returned) }, 0).toFixed(2) }}</td>
							<td style="text-align: right;">{{ payments.reduce((p, c) => { return p + parseFloat(c.paid_out) }, 0).toFixed(2) }}</td>
							<td style="text-align: right;">{{ payments[payments.length - 1].balance.toFixed(2) }}</td>
						</tr>
					</tfoot>
				</table>

				<table v-if="payments.length == 0">
					<tbody>
						<tr>
							<td>No records found</td>
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
		el: '#customerPaymentReport',

		filters: {
			dateFormat(value, format) {
				return moment(value).format(format);
			}
		},

		data(){
			return {
				customerType: '',
				customers: [],
				selectedCustomer: null,
				dateFrom: null,
				dateTo: null,
				payments: [],
				previousBalance: 0.00,
				showTable: false
			}
		},
		created(){
			let today = moment().format('YYYY-MM-DD');
			this.dateTo = today;
			this.dateFrom = moment().format('YYYY-MM-DD');
		},
		methods:{
			onChangeType(){
				this.getCustomers();
			},
			getCustomers(){
				axios.post('/get_customers', {customerType: this.customerType}).then(res => {
					this.customers = res.data;
				})
			},
			getReport(){
				if(this.selectedCustomer == null){
					alert('Select customer');
					return;
				}
				let data = {
					dateFrom: this.dateFrom,
					dateTo: this.dateTo,
					customerId: this.selectedCustomer.Customer_SlNo
				}

				axios.post('/get_customer_ledger', data).then(res => {
					this.payments = res.data.payments;
					this.previousBalance = res.data.previousBalance;
					this.showTable = true;
				})
			},
			async print(){
				let reportContent = `
					<div class="container">
						<h4 style="text-align:center">Customer payment report</h4 style="text-align:center">
						<div class="row">
							<div class="col-xs-6" style="font-size:12px;">
								<strong>Customer Code: </strong> ${this.selectedCustomer.Customer_Code}<br>
								<strong>Name: </strong> ${this.selectedCustomer.Customer_Name}<br>
								<strong>Address: </strong> ${this.selectedCustomer.Customer_Address}<br>
								<strong>Mobile: </strong> ${this.selectedCustomer.Customer_Mobile}<br>
							</div>
							<div class="col-xs-6 text-right">
								<strong>Statement from</strong> ${this.dateFrom} <strong>to</strong> ${this.dateTo}
							</div>
						</div>
					</div>
					<div class="container">
						<div class="row">
							<div class="col-xs-12">
								${document.querySelector('#reportTable').innerHTML}
							</div>
						</div>
					</div>
				`;

				var mywindow = window.open('', 'PRINT', `width=${screen.width}, height=${screen.height}`);
				mywindow.document.write(`
					<?php $this->load->view('Administrator/reports/reportHeader.php');?>
				`);

				mywindow.document.body.innerHTML += reportContent;

				mywindow.focus();
				await new Promise(resolve => setTimeout(resolve, 1000));
				mywindow.print();
				mywindow.close();
			}
		}
	})
</script>