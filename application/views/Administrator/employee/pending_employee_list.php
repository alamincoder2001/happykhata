<div class="row">
    <div class="col-xs-12">
        <div class="clearfix">
            <div class="pull-right tableTools-container"></div>
        </div>

        <div class="table-header">
            Pending Employee Information
        </div>
    </div>

    <div class="col-xs-12">
        <div class="table-responsive">
            <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Employee ID</th>
                        <th>Employee Name</th>
                        <th class="hidden-480">Designation</th>
                        <th>Contact No</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    if (isset($employees) && $employees) {
                        foreach ($employees as $row) {
                    ?>
                            <tr>
                                <td>
                                    <img src="<?php echo base_url() . 'uploads/employeePhoto_thum/' . $row->Employee_Pic_thum; ?>" alt="" style="width:45px;height:45px;"></td>
                                </td>
                                <td><?php echo $row->Employee_ID; ?></td>
                                <td class="hidden-480"><?php echo $row->Employee_Name; ?></td>
                                <td><?php echo $row->Designation_Name; ?></td>

                                <td class="hidden-480"><?php echo $row->Employee_ContactNo; ?></td>

                                <td>
                                    <div class="hidden-sm hidden-xs action-buttons">
                                        <a class="blue" href="<?php echo base_url(); ?>employee_details/<?php echo $row->Employee_SlNo; ?>" target="_blank" style="cursor:pointer;margin-right:20px;">
                                            <i class="ace-icon fa fa-file bigger-130"></i>
										</a>
                                        <span onclick="active(<?php echo $row->Employee_SlNo; ?>)" style="cursor:pointer;color:green;font-size:20px;margin-right:20px;"><i class="fa fa-check-square"></i></span>

                                        <a class="blue" href="<?php echo base_url(); ?>employeeEdit/<?php echo $row->Employee_SlNo; ?>" style="cursor:pointer;">
                                            <i class="ace-icon fa fa-pencil bigger-130"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>

                    <?php
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div><!-- /.col -->
</div><!-- /.row -->

<script type="text/javascript">
    function active(id){
        var deletedd= id;
        var inputdata = 'deleted='+deletedd;
        if (confirm("Are you sure, You want to active this?")) {
        var urldata = "<?php echo base_url();?>employeeActive";
        $.ajax({
            type: "POST",
            url: urldata,
            data: inputdata,
            success:function(data){
                //$("#saveResult").html(data);
                alert("Employee Activated");
				location.reload();
            }
        });
		}
    }
</script>