<style>
    .employee-details td {
        padding: 3px 10px;
    }
</style>

<div id="employeeDetails">
    <div class="row">
        <div class="col-md-12">
            <a href="" id="printEmployee"><i class="fa fa-print"></i> Print</a>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12" id="printContent">
            <table class="employee-details">
                <tr><td colspan="3" style="padding-bottom:15px;"><img src="<?php echo base_url() . 'uploads/employeePhoto_thum/' . $employee->Employee_Pic_thum; ?>" alt="" style="width:150px"></td></tr>
                
                <tr><td colspan="3" style="padding-bottom:15px;font-size: 15px;font-weight:bold;text-decoration:underline;">Official Information</td></tr>

                <tr><td><strong>Employee Id</strong></td><td> : </td><td><?php echo $employee->Employee_ID;?></td></tr>
                <tr><td><strong>Name</strong></td><td> : </td><td><?php echo $employee->Employee_Name;?></td></tr>
                <tr><td><strong>Department</strong></td><td> : </td><td><?php echo $employee->Department_Name;?></td></tr>
                <tr><td><strong>Designation</strong></td><td> : </td><td><?php echo $employee->Designation_Name;?></td></tr>
                <tr><td><strong>Joining Date</strong></td><td> : </td><td><?php echo $employee->Employee_JoinDate;?></td></tr>
                <tr><td><strong>Salary</strong></td><td> : </td><td><?php echo $employee->salary_range;?></td></tr>

                <tr><td colspan="3" style="padding-top:15px;padding-bottom:15px;font-size: 15px;font-weight:bold;text-decoration:underline;">Personal Information</td></tr>

                <tr><td><strong>Father's Name</strong></td><td> : </td><td><?php echo $employee->Employee_FatherName;?></td></tr>
                <tr><td><strong>Mother's Name</strong></td><td> : </td><td><?php echo $employee->Employee_MotherName;?></td></tr>
                <tr><td><strong>Gender</strong></td><td> : </td><td><?php echo $employee->Employee_Gender;?></td></tr>
                <tr><td><strong>Date of Birth</strong></td><td> : </td><td><?php echo $employee->Employee_BirthDate;?></td></tr>
                <tr><td><strong>Marital Status</strong></td><td> : </td><td><?php echo $employee->Employee_MaritalStatus;?></td></tr>
                <tr><td><strong>Present Address</strong></td><td> : </td><td><?php echo $employee->Employee_PrasentAddress;?></td></tr>
                <tr><td><strong>Permanent Address</strong></td><td> : </td><td><?php echo $employee->Employee_PermanentAddress;?></td></tr>
                <tr><td><strong>Contact No.</strong></td><td> : </td><td><?php echo $employee->Employee_ContactNo;?></td></tr>
                <tr><td><strong>Email</strong></td><td> : </td><td><?php echo $employee->Employee_Email;?></td></tr>
            </table>
        </div>
    </div>
</div>

<script>
    document.querySelector('#printEmployee').addEventListener('click', (e) => {
        e.preventDefault();
        printEmployee();
    })
    async function printEmployee() {
        let printContent = `
            <div class="container">
                <div class="row">
                    <div class="col-xs-12 text-center">
                        <h3>Employee Information</h3>
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

        printWindow.document.head.innerHTML += `
            <style>
                .employee-details td {
                    padding: 3px 10px;
                }
            </style>
        `;
        printWindow.document.body.innerHTML += printContent;

        printWindow.focus();
        await new Promise(resolve => setTimeout(resolve, 1000));
        printWindow.print();
        printWindow.close();
    }
</script>