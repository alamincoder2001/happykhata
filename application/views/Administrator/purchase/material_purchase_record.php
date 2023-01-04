<style>
    .form-group {
        margin-right: 15px;
    }

    .v-select{
		margin-top:-2.5px;
        float: right;
        min-width: 180px;
        margin-left: 5px;
	}
	.v-select .dropdown-toggle{
		padding: 0px;
        height: 30px;
        border-radius: 0;
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

    input[type="date"] {
        border-radius: 0 !important;
        height: 29px;
        margin-top: 2px;
    }
</style>
<div id="purchaseRecord">
    <div class="row" style="padding: 15px;border-bottom: 1px solid #ccc;">
        <div class="col-sm-12">
            <form class="form-inline" v-on:submit.prevent="getPurchase">
                <div class="form-group">
                    <label>Search Type</label><br>
                    <select class="form-conrol" v-model="searchType">
                        <option value="all">All</option>
                        <option value="bySupplier">By Supplier</option>
                    </select>
                </div>
                <div class="form-group" v-if="searchType == 'bySupplier'" style="display:none;" v-bind:style="{display: searchType == 'bySupplier' ? '' : 'none'}">
                    <label>Supplier</label><br>
                    <v-select label="display_name" v-bind:options="suppliers" v-model="selectedSupplier" placeholder="Select Supplier"></v-select>
                </div>
                <div class="form-group">
                    <label>Date From</label><br>
                    <input type="date" class="form-control" v-model="dateFrom">
                </div>
                <div class="form-group">
                    <label>Date To</label><br>
                    <input type="date" class="form-control" v-model="dateTo">
                </div>
                <div class="form-group">
                    <label>&nbsp;</label><br>
                    <button type="submit" class="btn btn-info btn-xs">Search</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row" style="padding: 15px;">
        <div class="col-sm-12">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Invoice No.</th>
                            <th>Date</th>
                            <th>Supplier Id</th>
                            <th>Supplier Name</th>
                            <th>Sub Total</th>
                            <th>VAT</th>
                            <th>Discount</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Due</th>
                            <th>Note</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <template style="display:none;" v-bind:style="{display: purchases.length > 0 ? '' : 'none'">
                    <tbody>
                        <tr v-for="purchase in purchases">
                            <td>{{ purchase.invoice_no }}</td>
                            <td>{{ purchase.purchase_date }}</td>
                            <td>{{ purchase.supplier_code }}</td>
                            <td>{{ purchase.supplier_name }}</td>
                            <td>{{ purchase.sub_total }}</td>
                            <td>{{ purchase.vat }}</td>
                            <td>{{ purchase.discount }}</td>
                            <td>{{ purchase.total }}</td>
                            <td>{{ purchase.paid }}</td>
                            <td>{{ purchase.due }}</td>
                            <td>{{ purchase.note }}</td>
                            <td>
                                <?php if($this->session->userdata('accountType') != 'u'){?>
                                <a href="" v-bind:href="`material_purchase_invoice/${purchase.purchase_id}`" target="_blank"><i class="fa fa-file-text fa-2x"></i></a>
                                <a href="" v-bind:href="`material_purchase/${purchase.purchase_id}`"><i class="fa fa-pencil-square fa-2x"></i></a>
                                <a href="" v-on:click.prevent="deletePurchase(purchase.purchase_id, purchase.invoice_no)"><i class="fa fa-trash fa-2x"></i></a>
                                <?php }?>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="7"></td>
                            <td>{{ totalPurchase }}</td>
                            <td>{{ totalPaid }}</td>
                            <td>{{ totalDue }}</td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tfoot>
                    </template>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="<?php echo base_url();?>assets/js/vue/vue.min.js"></script>
<script src="<?php echo base_url();?>assets/js/vue/axios.min.js"></script>
<script src="<?php echo base_url();?>assets/js/vue/vuejs-datatable.js"></script>
<script src="<?php echo base_url();?>assets/js/vue/vue-select.min.js"></script>
<script src="<?php echo base_url();?>assets/js/moment.min.js"></script>

<script>
    Vue.component('v-select', VueSelect.VueSelect);
    new Vue({
        el: '#purchaseRecord',
        data() {
            return {
                suppliers: [],
                selectedSupplier: null,
                dateFrom: moment().format('YYYY-MM-DD'),
                dateTo: moment().format('YYYY-MM-DD'),
                purchases: [],
                searchType: 'all',
                totalPurchase: 0.00,
                totalPaid: 0.00,
                totalDue: 0.00
            }
        },
        created() {
            this.getSuppliers();
            this.getPurchase();
        },
        methods: {
            getSuppliers() {
                axios.get('get_suppliers')
                    .then(res => {
                        this.suppliers = res.data;
                    })
            },
            getPurchase(){
                let supplier_id = null;
                if(this.selectedSupplier != null && this.searchType == 'bySupplier'){
                    supplier_id = this.selectedSupplier.Supplier_SlNo;
                }
                let options = {
                    supplier_id: supplier_id,
                    dateFrom: this.dateFrom,
                    dateTo: this.dateTo
                }
                axios.post('get_material_purchase', options)
                    .then(res=>{
                        this.purchases = res.data.purchases;
                        this.totalPurchase = res.data.totalPurchase;
                        this.totalPaid = res.data.totalPaid;
                        this.totalDue = res.data.totalDue;
                    })
            },
            deletePurchase(purchase_id, invoice_no){
                let conf = confirm('Are you sure?');
                if(conf == false){
                    return;
                }
                let options = {
                    purchase_id,
                    invoice_no
                }
                axios.post('/delete_material_purchase', options)
                    .then(res=>{
                        let r = res.data;
                        alert(r.message);
                        if(r.success){
                            this.getPurchase();
                        }
                    })
            }
        }
    })
</script>