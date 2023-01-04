<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Employee extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->brunch = $this->session->userdata('BRANCHid');
        $access = $this->session->userdata('userId');
        if ($access == '') {
            redirect("Login");
        }
        $this->load->model('Billing_model');
        $this->load->model("Model_myclass", "mmc", TRUE);
        $this->load->model('Model_table', "mt", TRUE);

        $vars['branch_info'] = $this->Billing_model->company_branch_profile($this->brunch);
        $this->load->vars($vars);
    }

    public function index()
    {
        $access = $this->mt->userAccess();
        if(!$access){
            redirect(base_url());
        }
        $data['title'] = "Employee";
        $data['content'] = $this->load->view('Administrator/employee/add_employee', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function getEmployees(){
        $employees = $this->db->query("
            select 
                e.*,
                (select e.salary_range / 30)as daily_salary,
                dp.Department_Name,
                ds.Designation_Name,
                concat(e.Employee_Name, ' - ', e.Employee_ID) as display_name
            from tbl_employee e 
            join tbl_department dp on dp.Department_SlNo = e.Department_ID
            join tbl_designation ds on ds.Designation_SlNo = e.Designation_ID
            where e.status = 'a'
            and e.Employee_brinchid = ?
        ", $this->session->userdata('BRANCHid'))->result();

        echo json_encode($employees);
    }

    public function getMonths(){
        $months = $this->db->query("
            select * from tbl_month
        ")->result();

        echo json_encode($months);
    }

    public function getEmployeePayments(){
        $data = json_decode($this->input->raw_input_stream);

        $clauses = "";
        if(isset($data->employeeId) && $data->employeeId != ''){
            $clauses .= " and e.Employee_SlNo = '$data->employeeId'";
        }

        if(isset($data->month) && $data->month != ''){
            $clauses .= " and ep.month_id = '$data->month'";
        }

        if(isset($data->dateFrom) && $data->dateFrom != '' && isset($data->dateTo) && $data->dateTo != ''){
            $clauses .= " and ep.payment_date between '$data->dateFrom' and '$data->dateTo'";
        }

        if(isset($data->paymentType) && ($data->paymentType == '' || $data->paymentType == null)) {
            $clauses .= " and ep.payment_amount > 0";
        } else if(isset($data->paymentType) && $data->paymentType == 'deduct') {
            $clauses .= " and ep.deduction_amount > 0";
        }

        $payments = $this->db->query("
            select 
                ep.*,
                e.Employee_Name,
                e.Employee_ID,
                e.salary_range,
                dp.Department_Name,
                ds.Designation_Name,
                m.month_name
            from tbl_employee_payment ep
            join tbl_employee e on e.Employee_SlNo = ep.Employee_SlNo
            join tbl_department dp on dp.Department_SlNo = e.Department_ID
            join tbl_designation ds on ds.Designation_SlNo = e.Designation_ID
            join tbl_month m on m.month_id = ep.month_id
            where ep.paymentBranch_id = ?
            and ep.status = 'a'
            $clauses
            order by ep.employee_payment_id desc
        ", $this->session->userdata('BRANCHid'))->result();

        echo json_encode($payments);
    }

    public function getSalarySummary(){

        $data = json_decode($this->input->raw_input_stream);

        $summary = $this->mt->employeeDue(null, null, $data->monthId);

        echo json_encode($summary);
    }

    public function getPayableSalary(){
        $data = json_decode($this->input->raw_input_stream);

        $payableAmount = $this->mt->employeeDue($data->employeeId, null, $data->monthId)[0]->due;

        echo $payableAmount;
    }

    //Designation
    public function designation()
    {
        $access = $this->mt->userAccess();
        if(!$access){
            redirect(base_url());
        }
        $data['title'] = "Add Designation";
        $data['content'] = $this->load->view('Administrator/employee/designation', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function insert_designation()
    {
        $mail = $this->input->post('Designation');
        $query = $this->db->query("SELECT Designation_Name from tbl_designation where Designation_Name = '$mail'");

        if ($query->num_rows() > 0) {
            $data['exists'] = "This Name is Already Exists";
            $this->load->view('Administrator/ajax/designation', $data);
        } else {
            $data = array(
                "Designation_Name" => $this->input->post('Designation', TRUE),
                "AddBy" => $this->session->userdata("FullName"),
                "AddTime" => date("Y-m-d H:i:s")
            );
            $this->mt->save_data('tbl_designation', $data);
            //$this->load->view('Administrator/ajax/designation');
        }
    }

    public function designationedit($id)
    {
        $data['title'] = "Edit Designation";
        $fld = 'Designation_SlNo';
        $data['selected'] = $this->Billing_model->select_by_id('tbl_designation', $id, $fld);
        $this->load->view('Administrator/edit/designation_edit', $data);
    }

    public function designationupdate()
    {
        $id = $this->input->post('id');
        $fld = 'Designation_SlNo';
        $data = array(
            "Designation_Name" => $this->input->post('Designation', TRUE),
            "UpdateBy" => $this->session->userdata("FullName"),
            "UpdateTime" => date("Y-m-d H:i:s")
        );
        $this->mt->update_data("tbl_designation", $data, $id, $fld);
    }

    public function designationdelete()
    {
        $fld = 'Designation_SlNo';
        $id = $this->input->post('deleted');
        $this->mt->delete_data("tbl_designation", $id, $fld);
        //$this->load->view('Administrator/ajax/designation');

    }
    //^^^^^^^^^^^^^^^^^^^^^^^^^
    //
    public function depertment()
    {
        $access = $this->mt->userAccess();
        if(!$access){
            redirect(base_url());
        }
        $data['title'] = "Add Depertment";
        $data['content'] = $this->load->view('Administrator/employee/depertment', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function insert_depertment()
    {
        $mail = $this->input->post('Depertment');
        $query = $this->db->query("SELECT Department_Name from tbl_department where Department_Name = '$mail'");

        if ($query->num_rows() > 0) {
            $exists = "This Name is Already Exists";
            echo json_encode($exists);
            //$this->load->view('Administrator/ajax/depertment', $data);
        } else {
            $data = array(
                "Department_Name" => $this->input->post('Depertment', TRUE),
                "AddBy" => $this->session->userdata("FullName"),
                "AddTime" => date("Y-m-d H:i:s")
            );
            $this->mt->save_data('tbl_department', $data);
            $message = "Save Successful";
            echo json_encode($message);
        }
    }

    public function depertmentedit($id)
    {
        $data['title'] = "Edit Department";
        $fld = 'Department_SlNo';
        $data['selected'] = $this->Billing_model->select_by_id('tbl_department', $id, $fld);
        $data['content'] = $this->load->view('Administrator/edit/depertment_edit', $data);
        //$this->load->view('Administrator/index', $data);
    }

    public function depertmentupdate()
    {
        $id = $this->input->post('id');
        $fld = 'Department_SlNo';
        $data = array(
            "Department_Name" => $this->input->post('Depertment', TRUE),
            "UpdateBy" => $this->session->userdata("FullName"),
            "UpdateTime" => date("Y-m-d H:i:s")
        );
        $this->mt->update_data("tbl_department", $data, $id, $fld);
    }

    public function depertmentdelete()
    {
        $fld = 'Department_SlNo';
        $id = $this->input->post('deleted');
        $this->mt->delete_data("tbl_department", $id, $fld);
        //$this->load->view('Administrator/ajax/depertment');

    }

    //^^^^^^^^^^^^^^^^^^^^
    public function emplists()
    {
        $access = $this->mt->userAccess();
        if(!$access){
            redirect(base_url());
        }
        $data['title'] = "Employee List";
        $data['employes'] = $this->HR_model->get_all_employee_list();
        $data['content'] = $this->load->view('Administrator/employee/list', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    // fancybox add
    public function fancybox_depertment()
    {
        $this->load->view('Administrator/employee/em_depertment');
    }

    public function fancybox_insert_depertment()
    {
        $mail = $this->input->post('Depertment');
        $query = $this->db->query("SELECT Department_Name from tbl_department where Department_Name = '$mail'");

        if ($query->num_rows() > 0) {
            $data['exists'] = "This Name is Already Exists";
            $this->load->view('Administrator/ajax/fancybox_depertmetn', $data);
        } else {
            $data = array(
                "Department_Name" => $this->input->post('Depertment', TRUE),
                "AddBy" => $this->session->userdata("FullName"),
                "AddTime" => date("Y-m-d H:i:s")
            );
            $this->mt->save_data('tbl_department', $data);
            $this->load->view('Administrator/ajax/fancybox_depertmetn');
        }
    }
    //^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

    // fancybox add 
    public function month()
    {
        $access = $this->mt->userAccess();
        if(!$access){
            redirect(base_url());
        }
        $data['title'] = 'Month';
        $data['content'] = $this->load->view('Administrator/employee/month', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function insert_month()
    {
        $month_name = $this->input->post('month');
        $query = $this->db->query("SELECT month_name from tbl_month where month_name = '$month_name'");

        if ($query->num_rows() > 0) {
            $exists = "This Name is Already Exists";
            echo json_encode($exists);
        } else {
            $data = array(
                "month_name" => $this->input->post('month', TRUE),
                /*   "AddBy"                  =>$this->session->userdata("FullName"),
                  "AddTime"                =>date("Y-m-d H:i:s") */
            );
            if ($this->mt->save_data('tbl_month', $data)) {
                $message = "Month insert success";
                echo json_encode($message);
            }
        }
    }

    public function editMonth($id)
    {
        $query = $this->db->query("SELECT * from tbl_month where month_id = '$id'");
        $data['row'] = $query->row();
        $this->load->view('Administrator/employee/edit_month', $data);
    }

    public function updateMonth()
    {
        $id = $this->input->post('month_id');
        $fld = 'month_id';
        $data = array(
            "month_name" => $this->input->post('month', TRUE),
        );
        if ($this->mt->update_data("tbl_month", $data, $id, $fld)) {
            //$message = "Update insert success";
            //echo json_encode($message);
            redirect('month');
        }
    }

    public function fancybox_designation()
    {
        $this->load->view('Administrator/employee/em_designation');
    }

    public function fancybox_insert_designation()
    {
        $mail = $this->input->post('Designation');
        $query = $this->db->query("SELECT Designation_Name from tbl_designation where Designation_Name = '$mail'");

        if ($query->num_rows() > 0) {
            $data['exists'] = "This Name is Already Exists";
            $this->load->view('Administrator/ajax/fancybox_designation', $data);
        } else {
            $data = array(
                "Designation_Name" => $this->input->post('Designation', TRUE),
                "AddBy" => $this->session->userdata("FullName"),
                "AddTime" => date("Y-m-d H:i:s")
            );
            $this->mt->save_data('tbl_designation', $data);
            $this->load->view('Administrator/ajax/fancybox_designation');
        }
    }
    //^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
    // Employee Insert
    public function employee_insert()
    {
        $data = array();
        $this->load->library('upload');
        $config['upload_path'] = './uploads/employeePhoto_org/';
        $config['allowed_types'] = 'gif|jpg|png|jpeg';
        $config['max_size'] = '10000';
        $config['image_width'] = '4000';
        $config['image_height'] = '4000';
        $this->upload->initialize($config);

        $data['Designation_ID'] = $this->input->post('em_Designation', true);
        $data['Department_ID'] = $this->input->post('em_Depertment', true);
        $data['Employee_ID'] = $this->input->post('Employeer_id', true);
        $data['Employee_Name'] = $this->input->post('em_name', true);
        $data['Employee_JoinDate'] = $this->input->post('em_Joint_date');
        $data['Employee_Gender'] = $this->input->post('Gender', true);
        $data['Employee_BirthDate'] = $this->input->post('em_dob', true);
        $data['Employee_ContactNo'] = $this->input->post('em_contact', true);
        $data['Employee_Email'] = $this->input->post('ec_email', true);
        $data['Employee_MaritalStatus'] = $this->input->post('Marital', true);
        $data['Employee_FatherName'] = $this->input->post('em_father', true);
        $data['Employee_MotherName'] = $this->input->post('mother_name', true);
        $data['Employee_PrasentAddress'] = $this->input->post('em_Present_address', true);
        $data['Employee_PermanentAddress'] = $this->input->post('em_Permanent_address', true);
        $data['salary_range'] = $this->input->post('salary_range', true);
        $data['status'] = 'a';

        $data['AddBy'] = $this->session->userdata("FullName");
        $data['Employee_brinchid'] = $this->session->userdata("BRANCHid");
        $data['AddTime'] = date("Y-m-d H:i:s");

        $this->upload->do_upload('em_photo');
        $images = $this->upload->data();
        $data['Employee_Pic_org'] = $images['file_name'];

        $config['image_library'] = 'gd2';
        $config['source_image'] = $this->upload->upload_path . $this->upload->file_name;
        $config['new_image'] = 'uploads/' . 'employeePhoto_thum/' . $this->upload->file_name;
        $config['maintain_ratio'] = FALSE;
        $config['width'] = 165;
        $config['height'] = 175;
        $this->load->library('image_lib', $config);
        $this->image_lib->resize();
        $data['Employee_Pic_thum'] = $this->upload->file_name;
        //echo "<pre>";print_r($data);exit;
        $this->mt->save_data('tbl_employee', $data);
        //$this->Billing_model->save_employee_data($data);
        //redirect('Administrator/Employee/');
        //$this->load->view('Administrator/ajax/add_employee');
    }

    public function employee_edit($id)
    {
        $data['title'] = "Edit Employee";
        $query = $this->db->query("SELECT tbl_employee.*,tbl_department.*,tbl_designation.* FROM tbl_employee left join tbl_department on tbl_department.Department_SlNo=tbl_employee.Department_ID left join tbl_designation on tbl_designation.Designation_SlNo=tbl_employee.Designation_ID  where tbl_employee.Employee_SlNo = '$id'");
        $data['selected'] = $query->row();
        //echo "<pre>";print_r($data['selected']);exit;
        $data['content'] = $this->load->view('Administrator/edit/employee_edit', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function employee_Update()
    {

        $id = $this->input->post('iidd');
        $fld = 'Employee_SlNo';
        $this->load->library('upload');
        $config['upload_path'] = './uploads/employeePhoto_org/';
        $config['allowed_types'] = 'gif|jpg|png|jpeg';
        $config['max_size'] = '10000';
        $config['image_width'] = '4000';
        $config['image_height'] = '4000';
        $this->upload->initialize($config);

        $data['Designation_ID'] = $this->input->post('em_Designation', true);
        $data['Department_ID'] = $this->input->post('em_Depertment', true);
        $data['Employee_ID'] = $this->input->post('Employeer_id', true);
        $data['Employee_Name'] = $this->input->post('em_name', true);
        $data['Employee_JoinDate'] = $this->input->post('em_Joint_date');
        $data['Employee_Gender'] = $this->input->post('Gender', true);
        $data['Employee_BirthDate'] = $this->input->post('em_dob', true);
        $data['Employee_ContactNo'] = $this->input->post('em_contact', true);
        $data['Employee_Email'] = $this->input->post('ec_email', true);
        $data['Employee_MaritalStatus'] = $this->input->post('Marital', true);
        $data['Employee_FatherName'] = $this->input->post('em_father', true);
        $data['Employee_MotherName'] = $this->input->post('mother_name', true);
        $data['Employee_PrasentAddress'] = $this->input->post('em_Present_address', true);
        $data['Employee_PermanentAddress'] = $this->input->post('em_Permanent_address', true);
        $data['Employee_brinchid'] = $this->session->userdata("BRANCHid");
        $data['salary_range'] = $this->input->post('salary_range', true);
        $data['status'] = $this->input->post('status', true);

        $data['UpdateBy'] = $this->session->userdata("FullName");
        $data['UpdateTime'] = date("Y-m-d H:i:s");

        $xx = $this->mt->select_by_id("tbl_employee", $id, $fld);

        $image = $this->upload->do_upload('em_photo');
        $images = $this->upload->data();

        if ($image != "") {
            if ($xx['Employee_Pic_thum'] && $xx['Employee_Pic_org']) {
                unlink("./uploads/employeePhoto_thum/" . $xx['Employee_Pic_thum']);
                unlink("./uploads/employeePhoto_org/" . $xx['Employee_Pic_org']);
            }
            $data['Employee_Pic_org'] = $images['file_name'];

            $config['image_library'] = 'gd2';
            $config['source_image'] = $this->upload->upload_path . $this->upload->file_name;
            $config['new_image'] = 'uploads/' . 'employeePhoto_thum/' . $this->upload->file_name;
            $config['maintain_ratio'] = FALSE;
            $config['width'] = 165;
            $config['height'] = 175;
            $this->load->library('image_lib', $config);
            $this->image_lib->resize();
            $data['Employee_Pic_thum'] = $this->upload->file_name;
        } else {

            $data['Employee_Pic_org'] = $xx['Employee_Pic_org'];
            $data['Employee_Pic_thum'] = $xx['Employee_Pic_thum'];
        }

        $this->mt->update_data("tbl_employee", $data, $id, $fld);
    }

    public function employee_Delete()
    {
        $id = $this->input->post('deleted');
        $this->db->set(['status'=>'d'])->where('Employee_SlNo', $id)->update('tbl_employee');
    }

    public function active()
    {
        $id = $this->input->post('deleted');
        $this->db->query("update tbl_employee set status = 'a' where Employee_SlNo = ?", $id);
    }

    public function employeesalarypayment($paymentType = null)
    {
        $access = $this->mt->userAccess();
        if(!$access){
            redirect(base_url());
        }
        $data['title'] = $paymentType == null || $paymentType == '' ? "Employee Salary Payment" : "Employee Salary Deduction";
        $data['paymentType'] = $paymentType;
        $data['content'] = $this->load->view('Administrator/employee/employee_salary', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function selectEmployee()
    {
        $data['title'] = "Employee Salary Payment";
        $employee_id = $this->input->post('employee_id');
        $query = $this->db->query("SELECT `salary_range` FROM tbl_employee where Employee_SlNo='$employee_id'");
        $data['employee'] = $query->row();
        $this->load->view('Administrator/employee/ajax_employeey', $data);
    }

    public function addEmployeePayment()
    {
        $res = ['success'=>false, 'message'=>'Nothing happened'];
        try{
            $paymentObj = json_decode($this->input->raw_input_stream);
            $payment = (array)$paymentObj;
            unset($payment['employee_payment_id']);
            $payment['status'] = 'a';
            $payment['save_by'] = $this->session->userdata('userId');
            $payment['save_date'] = Date('Y-m-d H:i:s');
            $payment['paymentBranch_id'] = $this->brunch;

            $this->db->insert('tbl_employee_payment', $payment);
            $res = ['success'=>true, 'message'=>'Employee payment added'];
        } catch (Exception $ex){
            throw new Exception($ex->getMessage());
        }

        echo json_encode($res);
    }

    public function employeesalaryreport()
    {
        $access = $this->mt->userAccess();
        if(!$access){
            redirect(base_url());
        }
        $data['title'] = "Employee Salary Report";
        $data['content'] = $this->load->view('Administrator/employee/employee_salary_report', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function EmployeeSalary_list()
    {
        $datas['employee_id'] = $employee_id = $this->input->post('employee_id');
        $datas['month'] = $month = $this->input->post('month');

        $this->session->set_userdata($datas);

        $BRANCHid = $this->session->userdata("BRANCHid");

        if ($employee_id == 'All') {

            $employeequery = $this->db
                ->join('tbl_designation', 'tbl_designation.Designation_SlNo=tbl_employee.Designation_ID', 'left')
                ->where('tbl_employee.Employee_brinchid', $BRANCHid)
                ->get('tbl_employee')->result();
            $data['employee_list'] = $employeequery;


        } else {


            $employeequery = $this->db
                ->join('tbl_designation', 'tbl_designation.Designation_SlNo=tbl_employee.Designation_ID', 'left')
                ->where('tbl_employee.Employee_brinchid', $BRANCHid)
                ->where('tbl_employee.Employee_SlNo	', $employee_id)
                ->get('tbl_employee')->result();
            $data['employee_list'] = $employeequery;
        }

        $data['month'] = $month;
        $this->load->view('Administrator/employee/employee_salary_report_list', $data);
    }

    public function EmploeePaymentReportPrint()
    {
        $BRANCHid = $this->session->userdata("BRANCHid");

        $employee_id = $this->session->userdata('employee_id');
        $month = $this->session->userdata('month');

        if ($employee_id == 'All') {

            $employeequery = $this->db
                ->join('tbl_designation', 'tbl_designation.Designation_SlNo=tbl_employee.Designation_ID', 'left')
                ->where('tbl_employee.Employee_brinchid', $BRANCHid)
                ->get('tbl_employee')->result();
            $data['employee_list'] = $employeequery;


        } else {

            $employeequery = $this->db
                ->join('tbl_designation', 'tbl_designation.Designation_SlNo=tbl_employee.Designation_ID', 'left')
                ->where('tbl_employee.Employee_brinchid', $BRANCHid)
                ->where('tbl_employee.Employee_SlNo	', $employee_id)
                ->get('tbl_employee')->result();
            $data['employee_list'] = $employeequery;
        }

        $data['month'] = $month;
        $this->load->view('Administrator/employee/employee_salary_report_print', $data);
    }

    public function edit_employee_salary($id)
    {
        $data['title'] = "Edit Employee Salary";
        $BRANCHid = $this->session->userdata("BRANCHid");
        $query = $this->db->query("SELECT tbl_employee.*,tbl_employee_payment.*,tbl_month.*,tbl_designation.* FROM tbl_employee left join tbl_employee_payment on tbl_employee_payment.Employee_SlNo=tbl_employee.Employee_SlNo left join tbl_month on tbl_employee_payment.month_id=tbl_month.month_id left join tbl_designation on tbl_designation.Designation_SlNo=tbl_employee.Designation_ID where tbl_employee_payment.employee_payment_id='$id' AND tbl_employee_payment.paymentBranch_id='$BRANCHid'");
        $data['selected'] = $query->row();
        //echo "<pre>";print_r($data['selected']);exit;
        $data['content'] = $this->load->view('Administrator/employee/edit_employee_salary', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function updateEmployeePayment()
    {
        $res = ['success'=>false, 'message'=>'Nothing happened'];
        try{
            $paymentObj = json_decode($this->input->raw_input_stream);
            $payment = (array)$paymentObj;
            unset($payment['employee_payment_id']);
            $payment['update_by'] = $this->session->userdata('userId');
            $payment['update_date'] = Date('Y-m-d H:i:s');

            $this->db->where('employee_payment_id', $paymentObj->employee_payment_id)->update('tbl_employee_payment', $payment);
            $res = ['success'=>true, 'message'=>'Employee payment updated'];
        } catch (Exception $ex){
            throw new Exception($ex->getMessage());
        }

        echo json_encode($res);
    }

    public function deleteEmployeePayment(){
        $res = ['success'=>false, 'message'=>'Nothing happened'];
        try{
            $data = json_decode($this->input->raw_input_stream);

            $this->db->set(['status'=>'d'])->where('employee_payment_id', $data->paymentId)->update('tbl_employee_payment');
            $res = ['success'=>true, 'message'=>'Employee payment deleted'];
        } catch (Exception $ex){
            throw new Exception($ex->getMessage());
        }

        echo json_encode($res);
    }

    public function getShifts(){
        $shifts = $this->db->query("
            select
            *
            from tbl_shifts
        ")->result();

        echo json_encode($shifts);
    }

    public function employeeAttendance() {
        $access = $this->mt->userAccess();
        if(!$access){
            redirect(base_url());
        }
        $data['title'] = "Employee Attendance";
        $data['content'] = $this->load->view('Administrator/employee/employee_attendance', $data, true);
        $this->load->view('Administrator/index', $data);
    }

    public function attendanceReport() {
        $access = $this->mt->userAccess();
        if(!$access){
            redirect(base_url());
        }
        $data['title'] = "Attendance Report";
        $data['content'] = $this->load->view("Administrator/employee/attendance_report", $data, true);
        $this->load->view("Administrator/index", $data);
    }

    public function employeeAttendanceReport() {
        $access = $this->mt->userAccess();
        if(!$access){
            redirect(base_url());
        }
        $data['title'] = "Employee Attendance Report";
        $data['content'] = $this->load->view("Administrator/employee/employee_attendance_report", $data, true);
        $this->load->view("Administrator/index", $data);
    }

    public function getEmployeeAttendance() {
        $data = json_decode($this->input->raw_input_stream);

        $clauses = "";
        if(isset($data->attendanceDate) && $data->attendanceDate != '') {
            $clauses .= " and att.attendance_date = '$data->attendanceDate'";
        }

        if((isset($data->dateFrom) && $data->dateFrom != '') && (isset($data->dateTo) && $data->dateTo != '')) {
            $clauses .= " and att.attendance_date between '$data->dateFrom' and '$data->dateTo'";
        }

        if(isset($data->employeeId) && $data->employeeId != '') {
            $clauses .= " and att.employee_id = '$data->employeeId'";
        }

        $attendance = $this->db->query("
            select 
                att.*,
                emp.Employee_ID as employee_code,
                emp.Employee_Name as employee_name,
                des.Designation_Name as designation,
                mon.month_name
            from tbl_employee_attendance att
            join tbl_employee emp on emp.Employee_SlNo = att.employee_id
            join tbl_designation des on des.Designation_SlNo = emp.Designation_ID
            join tbl_month mon on mon.month_id = att.month_id
            where att.branch_id = ?
            $clauses
        ", $this->brunch)->result();

        echo json_encode($attendance);
    }

    public function addEmployeeAttendance() {
        $res = ['success' => false, 'message' => ''];

        try {
            $attendanceData = json_decode($this->input->raw_input_stream);

            foreach($attendanceData as $attendance) {
                $attendance = [
                    'attendance_date' => $attendance->attendance_date,
                    'month_id' => $attendance->month_id,
                    'employee_id' => $attendance->employee_id,
                    'attendance' => $attendance->attendance,
                    'branch_id' => $this->brunch,
                    'saved_by' => $this->session->userdata("FullName"),
                    'saved_datetime' => date('Y-m-d H:i:s')
                ];

                $duplicate = $this->db->query("
                    select * from tbl_employee_attendance 
                    where attendance_date = ?
                    and employee_id = ?
                    and branch_id = ?
                ", [$attendance['attendance_date'], $attendance['employee_id'], $this->brunch]);

                if($duplicate->num_rows() == 0) {
                    $this->db->insert('tbl_employee_attendance', $attendance);
                } else {
                    $this->db->where('attendance_id', $duplicate->row()->attendance_id)->update('tbl_employee_attendance', $attendance);
                }
            }

            $salarySheet = [
                'process_date' => $attendanceData[0]->attendance_date,
                'process_by' => $this->session->userdata("FullName"),
                'process_datetime' => date('Y-m-d H:i:s'),
                'month_id' => $attendanceData[0]->month_id,
                'branch_id' => $this->brunch
            ];

            $salarySheetId = null;
            $duplicateSalarySheet = $this->db->query("select * from tbl_salary_sheet where month_id = ? and branch_id = ?", [$attendanceData[0]->month_id, $this->brunch]);
            if($duplicateSalarySheet->num_rows() != 0) {
                $salarySheetId = $duplicateSalarySheet->row()->salary_sheet_id;
                $this->db->where(['salary_sheet_id' => $salarySheetId])->update('tbl_salary_sheet', $salarySheet);
            } else {
                $this->db->insert('tbl_salary_sheet', $salarySheet);
                $salarySheetId = $this->db->insert_id();
            }
    
            $salaryDetails = $this->db->query("
                select
                    emp.*,
                    (
                        select count(att.attendance)
                        from tbl_employee_attendance att
                        where att.employee_id = emp.Employee_SlNo
                        and att.attendance = 'present'
                        and att.month_id = " . $attendanceData[0]->month_id . "
                    ) as presences,
                    (
                        select count(att.attendance)
                        from tbl_employee_attendance att
                        where att.employee_id = emp.Employee_SlNo
                        and att.attendance = 'half_day'
                        and att.month_id = " . $attendanceData[0]->month_id . "
                    ) as half_days,
                    (
                        select count(att.attendance)
                        from tbl_employee_attendance att
                        where att.employee_id = emp.Employee_SlNo
                        and att.attendance = 'absent'
                        and att.month_id = " . $attendanceData[0]->month_id . "
                    ) as absences,
                    (
                        select ifnull(sum(ep.payment_amount), 0)
                        from tbl_employee_payment ep
                        where ep.status = 'a'
                        and ep.month_id = " . $attendanceData[0]->month_id . "
                        and ep.Employee_SlNo = emp.Employee_SlNo
                    ) as paid,
                    (
                        select ifnull(sum(ep.deduction_amount), 0)
                        from tbl_employee_payment ep
                        where ep.status = 'a'
                        and ep.month_id = " . $attendanceData[0]->month_id . "
                        and ep.Employee_SlNo = emp.Employee_SlNo
                    ) as deducted,
                    (
                        select emp.salary_range - (paid + deducted)
                    ) as payable
                from tbl_employee emp
                where emp.status = 'a'
                and emp.Employee_brinchid = ?
            ", $this->brunch)->result();
    
            $salaryDetails = array_map(function($details) use($salarySheetId) {

                $duplicateSalary = $this->db->query("select * from tbl_salary_sheet_details where employee_id = ? and salary_sheet_id = ?", [$details->Employee_SlNo, $salarySheetId]);
                if($duplicateSalary->num_rows() != 0) {
                    $details->salary_range = $duplicateSalary->row()->salary;
                }

                $details->deducted = $details->deducted + 
                    ($details->salary_range - 
                    (
                        ($details->presences * ($details->salary_range / 30)) + 
                        ($details->half_days * (($details->salary_range / 30) / 2))
                    ));

                $details->payable = $details->salary_range - ($details->paid + $details->deducted);

                return [
                    'salary_sheet_id' => $salarySheetId,
                    'employee_id' => $details->Employee_SlNo,
                    'salary' => $details->salary_range,
                    'presences' => $details->presences,
                    'half_days' => $details->half_days,
                    'absences' => $details->absences,
                    'paid' => $details->paid,
                    'deducted' => $details->deducted,
                    'payable' => $details->payable,
                    'branch_id' => $details->Employee_brinchid,
                ];
            }, $salaryDetails);
    
            if($duplicateSalarySheet->num_rows() != 0) {
                $this->db->query("delete from tbl_salary_sheet_details where salary_sheet_id = ?", $salarySheetId);
            }
    
            $this->db->insert_batch('tbl_salary_sheet_details', $salaryDetails);

            $res = ['success' => true, 'message' => 'Attendance saved'];
        } catch(Exception $ex) {
            $res = ['success' => false, 'message' => $ex->getMessage()];
        }

        echo json_encode($res);
    }

    public function salaryStatement() {
        $access = $this->mt->userAccess();
        if(!$access){
            redirect(base_url());
        }
        $data['title'] = "Salary Statement";
        $data['content'] = $this->load->view('Administrator/employee/salary_statement', $data, true);
        $this->load->view('Administrator/index', $data);
    }

    public function getEmployeeLedger() {
        $data = json_decode($this->input->raw_input_stream);

        $previousDue = $this->mt->employeeDue($data->employeeId, $data->dateFrom)[0]->due;

        $ledger = $this->db->query("
            select * from (
                select 
                    'a' as sequence,
                    shd.salary_sheet_id as id,
                    sh.process_date as date,
                    concat('Salary of ', m.month_name) as description,
                    shd.salary,
                    0 as paid,
                    deducted,
                    0 as balance
                from tbl_salary_sheet_details shd
                join tbl_salary_sheet sh on sh.salary_sheet_id = shd.salary_sheet_id
                join tbl_month m on m.month_id = sh.month_id
                where shd.employee_id = '$data->employeeId'
            
                UNION
                select 
                    'b' as sequence,
                    ep.employee_payment_id as id,
                    ep.payment_date as date,
                    'Payment' as description,
                    0 as salary,
                    ep.payment_amount as paid,
                    0 as deducted,
                    0 as balance
                from tbl_employee_payment ep
                where ep.status = 'a'
                and ep.payment_amount != 0
                and ep.Employee_SlNo = '$data->employeeId'
            ) as tbl
            order by tbl.date, tbl.sequence, tbl.id
        ")->result();

        $ledger = array_map(function($payment, $index) use($ledger) {
            $payment->balance = $index == 0 ? $payment->salary - ($payment->paid + $payment->deducted) : $ledger[$index - 1]->balance + ($payment->salary - ($payment->paid + $payment->deducted));
            return $payment;
        }, $ledger, array_keys($ledger));

        $ledger = array_filter($ledger, function($payment) use($data) {
            return $payment->date >= $data->dateFrom && $payment->date <= $data->dateTo;
        });

        $ledger = array_values($ledger);

        $res['payments'] = $ledger;
        $res['previousBalance'] = $previousDue;

        echo json_encode($res);
    }

    public function employeeDueReport() {
        $access = $this->mt->userAccess();
        if(!$access){
            redirect(base_url());
        }
        $data['title'] = "Employee Due Report";
        $data['content'] = $this->load->view("Administrator/employee/employee_due_report", $data, true);
        $this->load->view("Administrator/index", $data);
    }

    public function getEmployeeDueList() {
        $data = json_decode($this->input->raw_input_stream);
        $employeeId = null;

        if(isset($data->employeeId) && $data->employeeId != '') {
            $employeeId = $data->employeeId;
        }

        $dueList = $this->mt->employeeDue($employeeId);

        echo json_encode($dueList);
    }

    public function processSalary() {
        $access = $this->mt->userAccess();
        if(!$access){
            redirect(base_url());
        }
        $data['title'] = "Process Salary";
        $data['content'] = $this->load->view('Administrator/employee/process_salary', $data, true);
        $this->load->view('Administrator/index', $data);
    }

    public function generateSalary() {
        $res = ['success' => false, 'message' => ''];

        try {
            $data = json_decode($this->input->raw_input_stream);
    
            $salarySheet = [
                'process_date' => $data->processing_date,
                'process_by' => $this->session->userdata("FullName"),
                'process_datetime' => date('Y-m-d H:i:s'),
                'month_id' => $data->month_id,
                'branch_id' => $this->brunch
            ];

            $salarySheetId = null;
            $duplicateSalarySheet = $this->db->query("select * from tbl_salary_sheet where month_id = ? and branch_id = ?", [$data->month_id, $this->brunch]);
            if($duplicateSalarySheet->num_rows() != 0) {
                $salarySheetId = $duplicateSalarySheet->row()->salary_sheet_id;
                $this->db->where(['salary_sheet_id' => $salarySheetId])->update('tbl_salary_sheet', $salarySheet);
            } else {
                $this->db->insert('tbl_salary_sheet', $salarySheet);
                $salarySheetId = $this->db->insert_id();
            }
    
            $salaryDetails = $this->db->query("
                select
                    emp.*,
                    (
                        select count(att.attendance)
                        from tbl_employee_attendance att
                        where att.employee_id = emp.Employee_SlNo
                        and att.attendance = 'present'
                        and att.month_id = '$data->month_id'
                    ) as presences,
                    (
                        select count(att.attendance)
                        from tbl_employee_attendance att
                        where att.employee_id = emp.Employee_SlNo
                        and att.attendance = 'half_day'
                        and att.month_id = '$data->month_id'
                    ) as half_days,
                    (
                        select count(att.attendance)
                        from tbl_employee_attendance att
                        where att.employee_id = emp.Employee_SlNo
                        and att.attendance = 'absent'
                        and att.month_id = '$data->month_id'
                    ) as absences,
                    (
                        select ifnull(sum(ep.payment_amount), 0)
                        from tbl_employee_payment ep
                        where ep.status = 'a'
                        and ep.month_id = '$data->month_id'
                        and ep.Employee_SlNo = emp.Employee_SlNo
                    ) as paid,
                    (
                        select ifnull(sum(ep.deduction_amount), 0)
                        from tbl_employee_payment ep
                        where ep.status = 'a'
                        and ep.month_id = '$data->month_id'
                        and ep.Employee_SlNo = emp.Employee_SlNo
                    ) as deducted,
                    (
                        select emp.salary_range - (paid + deducted)
                    ) as payable
                from tbl_employee emp
                where emp.status = 'a'
                and emp.Employee_brinchid = ?
            ", $this->brunch)->result();
    
            $salaryDetails = array_map(function($details) use($salarySheetId) {

                $duplicateSalary = $this->db->query("select * from tbl_salary_sheet_details where employee_id = ? and salary_sheet_id = ?", [$details->Employee_SlNo, $salarySheetId]);
                if($duplicateSalary->num_rows() != 0) {
                    $details->salary_range = $duplicateSalary->row()->salary;
                }

                $details->deducted = $details->deducted + 
                    ($details->salary_range - 
                    (
                        ($details->presences * ($details->salary_range / 30)) + 
                        ($details->half_days * (($details->salary_range / 30) / 2))
                    ));

                $details->payable = $details->salary_range - ($details->paid + $details->deducted);

                return [
                    'salary_sheet_id' => $salarySheetId,
                    'employee_id' => $details->Employee_SlNo,
                    'salary' => $details->salary_range,
                    'presences' => $details->presences,
                    'half_days' => $details->half_days,
                    'absences' => $details->absences,
                    'paid' => $details->paid,
                    'deducted' => $details->deducted,
                    'payable' => $details->payable,
                    'branch_id' => $details->Employee_brinchid,
                ];
            }, $salaryDetails);
    
            if($duplicateSalarySheet->num_rows() != 0) {
                $this->db->query("delete from tbl_salary_sheet_details where salary_sheet_id = ?", $salarySheetId);
            }
    
            $this->db->insert_batch('tbl_salary_sheet_details', $salaryDetails);

            $res = ['success' => true, 'message' => 'Success', 'salarySheetId' => $salarySheetId];

        } catch(Exception $ex) {
            $res = ['success' => false, 'message' => $ex->getMessage()];
        }

        echo json_encode($res);
    }

    public function getSalarySheet() {
        $data = json_decode($this->input->raw_input_stream);

        $res = [];

        $clauses = "";

        if(isset($data->id) && $data->id != '') {
            $clauses = " and sh.salary_sheet_id = '$data->id'";
            $res['salaryDetails'] = $this->db->query("
                select
                    shd.*,
                    (shd.presences + shd.half_days + shd.absences) as working_days,
                    emp.Employee_ID as employee_code,
                    emp.Employee_Name as employee_name,
                    dp.Department_Name as department,
                    ds.Designation_Name as designation
                from tbl_salary_sheet_details shd
                join tbl_employee emp on emp.Employee_SlNo = shd.employee_id
                join tbl_department dp on dp.Department_SlNo = emp.Department_ID
                join tbl_designation ds on ds.Designation_SlNo = emp.Designation_ID 
                where shd.salary_sheet_id = ?
            ", $data->id)->result();
        }

        $res['salarySheets'] = $this->db->query("
            select
                sh.*,
                m.month_name,
                (
                    select ifnull(sum(shd.payable), 0)
                    from tbl_salary_sheet_details shd
                    where shd.salary_sheet_id = sh.salary_sheet_id
                ) as total_amount
            from tbl_salary_sheet sh
            join tbl_month m on m.month_id = sh.month_id
            where branch_id = ?
            $clauses
        ", $this->brunch)->result();

        echo json_encode($res);
    }

    public function salarySheet($salarySheetId) {
        $access = $this->mt->userAccess();
        if(!$access){
            redirect(base_url());
        }
        $data['title'] = "Salary Sheet";
        $data['salarySheetId'] = $salarySheetId;
        $this->load->view("Administrator/employee/salary_sheet", $data);
    }
    
    public function pendingEmployeeList() {
        $access = $this->mt->userAccess();
        if(!$access){
            redirect(base_url());
        }
        $data['title'] = "Pending Employee List";
        $data['employees'] = $this->db->query("
            select 
                e.*,
                dp.Department_Name,
                ds.Designation_Name,
                concat(e.Employee_Name, ' - ', e.Employee_ID) as display_name
            from tbl_employee e 
            join tbl_department dp on dp.Department_SlNo = e.Department_ID
            join tbl_designation ds on ds.Designation_SlNo = e.Designation_ID
            where e.status = 'p'
            and e.Employee_brinchid = ?
        ", $this->session->userdata('BRANCHid'))->result();

        $data['content'] = $this->load->view("Administrator/employee/pending_employee_list", $data, true);
        $this->load->view("Administrator/index", $data);
        
    }

    public function employeeDetails($employeeId) {
        $data['title'] = "Employee Details";
        $data['employee'] = $this->db->query("
            select 
                e.*,
                dp.Department_Name,
                ds.Designation_Name,
                concat(e.Employee_Name, ' - ', e.Employee_ID) as display_name
            from tbl_employee e 
            join tbl_department dp on dp.Department_SlNo = e.Department_ID
            join tbl_designation ds on ds.Designation_SlNo = e.Designation_ID
            where e.Employee_SlNo = ?
            and e.Employee_brinchid = ?
        ", [$employeeId, $this->session->userdata('BRANCHid')])->row();

        $data['content'] = $this->load->view("Administrator/employee/employee_details", $data, true);
        $this->load->view("Administrator/index", $data);
    }

    public function attendance() {
        $access = $this->mt->userAccess();
        if(!$access){
            redirect(base_url());
        }

        $data['title'] = "Attendance Entry";
        $data['content'] = $this->load->view("Administrator/employee/attendance_process", $data, true);
        $this->load->view("Administrator/index", $data);
    }

    public function addAttendance() {
        $res = new stdClass;
        try {
            $this->db->trans_begin();
            $data = json_decode($this->input->raw_input_stream);

            $attendance = array(
                'date' => $data->employee->date,
                'note' => $data->employee->note,
                'status' => 'a',
                'added_by' => $this->session->userdata('FullName'),
                'added_date' => date('Y-m-d H:i:s'),
                'branch_id' => $this->session->userdata('BRANCHid')
            );

            $this->db->insert('tbl_attendance', $attendance);
            $insertId = $this->db->insert_id();

            $employees = [];
            foreach($data->attendance as $employee) {
                $salary = 0;
                if($employee->attendance == 'present') {
                    $salary = $employee->daily_salary;
                } else if($employee->attendance == 'half_day') {
                    $salary = ($employee->daily_salary / 2);
                } else {
                    $salary = 0;
                }


                $detail = array(
                    'attendance_id' => $insertId,
                    'employee_id' => $employee->employee_id,
                    'date' => $data->employee->date,
                    'attendance' => $employee->attendance,
                    'salary' => $salary,
                    'added_by' => $this->session->userdata('FullName'),
                    'added_date' => date('Y-m-d H:i:s'),
                );

                array_push($employees, $detail);
            }

            $this->db->insert_batch('tbl_attendance_details', $employees);

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
            } else {
                $this->db->trans_commit();
                $res->success = true;
                $res->message = 'Attendance entry successfully';
            }
            
        } catch (\Exception $e) {
            $this->db->trans_rollback();
            $res->success = false;
            $res->message = 'faild'. $e->getMessage();
        }

        echo json_encode($res);
    }

    public function updateAttendance() {
        $res = new stdClass;
        try {
            $this->db->trans_begin();
            $data = json_decode($this->input->raw_input_stream);
            $attId = $data->employee->id;

            $attendance = array(
                'date' => $data->employee->date,
                'note' => $data->employee->note,
                'status' => 'a',
                'update_by' => $this->session->userdata('FullName'),
                'update_date' => date('Y-m-d H:i:s'),
                'branch_id' => $this->session->userdata('BRANCHid')
            );

            $this->db->where('id', $attId)->update('tbl_attendance', $attendance);
            // $insertId = $this->db->insert_id();

            $employees = [];
            foreach($data->attendance as $employee) {
                $salary = 0;
                if($employee->attendance == 'present') {
                    $salary = $employee->daily_salary;
                } else if($employee->attendance == 'half_day') {
                    $salary = ($employee->daily_salary / 2);
                } else {
                    $salary = 0;
                }
                
                $detail = array(
                    'employee_id' => $employee->employee_id,
                    'date' => $data->employee->date,
                    'attendance' => $employee->attendance,
                    'salary' => $salary,
                    'update_by' => $this->session->userdata('FullName'),
                    'update_date' => date('Y-m-d H:i:s')
                );


                array_push($employees, $detail);
            }

            $this->db->where('date', $data->employee->date)->update_batch('tbl_attendance_details', $employees, 'employee_id');

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
            } else {
                $this->db->trans_commit();
                $res->success = true;
                $res->message = 'Attendance update successfully';
            }
            
        } catch (\Exception $e) {
            $this->db->trans_rollback();
            $res->success = false;
            $res->message = 'faild'. $e->getMessage();
        }

        echo json_encode($res);
    }

    public function getAttendances() {
        $data = json_decode($this->input->raw_input_stream);
        $clauses = '';

        if(isset($data->date) && $data->date != '') {
            $clauses .= " and att.date = '$data->date'";
        }

        if(isset($data->dateFrom) && $data->dateFrom != '' && isset($data->dateTo) && $data->dateTo != '') {
            $clauses .= " and att.date between '$data->dateFrom' and '$data->dateTo'";
        }

        if(isset($data->attId) && $data->attId != "") {
            $clauses .= " and att.id = $data->attId";
        }

        $attendances = $this->db->query("
            select 
                att.*
            from tbl_attendance att
            where att.status = 'a'
            and att.branch_id = ?
            $clauses
            order by att.date asc
        ", $this->session->userdata('BRANCHid'))->result();

        $attendances = array_map(function($attendance) {
            $attendance->details = $this->db->query("
                select 
                    ad.*,
                    e.Employee_ID,
                    e.Employee_Name,
                    e.salary_range,
                    d.Designation_Name
                from tbl_attendance_details ad 
                join tbl_employee e on e.Employee_SlNo = ad.employee_id
                join tbl_designation d on d.Designation_SlNo = e.Designation_ID
                where ad.attendance_id = ?
            ", $attendance->id)->result();
            return $attendance;
        }, $attendances);

        echo json_encode($attendances);
    }
    
    public function getAttendanceDetails() {
        $data = json_decode($this->input->raw_input_stream);
        $clauses = '';

        if(isset($data->employeeId) && $data->employeeId != '') {
            $clauses .= " and ad.employee_id = $data->employeeId";
        }

        if(isset($data->dateFrom) && $data->dateFrom != '' && isset($data->dateTo) && $data->dateTo != '') {
            $clauses .= " and a.date between '$data->dateFrom' and '$data->dateTo'";   
        }

        $details = $this->db->query("
            select 
                a.date as entry_date,
                ad.*,
                e.Employee_ID,
                e.Employee_Name,
                d.Designation_Name
            from tbl_attendance_details ad
            join tbl_attendance a on a.id = ad.attendance_id
            join tbl_employee e on e.Employee_SlNo = ad.employee_id 
            join tbl_designation d on d.Designation_SlNo = e.Designation_ID
            where a.status = 'a'
            and a.branch_id = ?
            $clauses
            order by entry_date asc
        ", $this->session->userdata('BRANCHid'))->result();

        echo json_encode($details);
    }

    public function attendanceRecord() {
        $access = $this->mt->userAccess();
        if(!$access){
            redirect(base_url());
        }

        $data['title'] = "Attendance Record";
        $data['content'] = $this->load->view("Administrator/employee/attendance_record", $data, true);
        $this->load->view("Administrator/index", $data);
    }

    public function salaryPayment() {
        $access = $this->mt->userAccess();
        if(!$access){
            redirect(base_url());
        }

        $data['title'] = "Salary Payment Entry";
        $data['content'] = $this->load->view("Administrator/employee/salary_payment", $data, true);
        $this->load->view("Administrator/index", $data);
    }

    public function getEmployeeDue() {
        $data = json_decode($this->input->raw_input_stream);
        $clauses = '';

        if(isset($data->employeeId) && $data->employeeId != '') {
            $clauses .= " and e.Employee_SlNo = $data->employeeId";
        }

        $dues = $this->db->query("
            select 
                e.Employee_ID,
                e.Employee_Name,
                d.Designation_Name,
                (
                    select 
                        ifnull(sum(ad.salary), 0)
                    from tbl_attendance_details ad 
                    where ad.employee_id = e.Employee_SlNo
                ) as bill,
                (
                    select 
                        ifnull(sum(sp.amount), 0)
                    from tbl_salary_payment sp 
                    where sp.status = 'a'
                    and sp.employee_id = e.Employee_SlNo
                    and sp.type = 'payment'
                ) as payment,
                (
                    select 
                        ifnull(sum(sp.amount), 0)
                    from tbl_salary_payment sp 
                    where sp.status = 'a'
                    and sp.employee_id = e.Employee_SlNo
                    and sp.type = 'bonus'
                ) as bonus,
                (
                    select 
                        ifnull(sum(sp.amount), 0)
                    from tbl_salary_payment sp 
                    where sp.status = 'a'
                    and sp.employee_id = e.Employee_SlNo
                    and sp.type = 'deduct'
                ) as deductions,
                (
                    select (bill + bonus) - (payment + deductions)
                ) as due
            from tbl_employee e
            left join tbl_designation d on d.Designation_SlNo = e.Designation_ID
            where e.status = 'a'
            and e.Employee_brinchid = ?
            $clauses
        ", $this->session->userdata('BRANCHid'))->result();

        echo json_encode($dues);
    }

    // salary payment
    public function getSalarayPayment() {
        $data = json_decode($this->input->raw_input_stream);
        $clauses = '';

        if(isset($data->dateFrom) && $data->dateFrom != '' && isset($data->dateTo) && $data->dateTo != '') {
            $clauses .= " and sp.date between '$data->dateFrom' and '$data->dateTo'";
        }

        if(isset($data->type) && $data->type != '') {
            $clauses .= " and sp.type = '$data->type'";
        }

        $payments = $this->db->query("
            select 
                sp.*,
                e.Employee_Id,
                e.Employee_Name
            from tbl_salary_payment sp 
            join tbl_employee e on e.Employee_SlNo = sp.employee_id
            where sp.status = 'a'
            and sp.branch_id = ?
            $clauses
        ", $this->session->userdata('BRANCHid'))->result();

        echo json_encode($payments);
    }

    public function addSalarayPayment() {
        $res = new stdClass;
        try {
            $data = json_decode($this->input->raw_input_stream);

            $salary = array(
                'date' => $data->date,                
                'employee_id' => $data->employeeId,                
                'type' => $data->type,                
                'amount' => $data->amount,                
                'note' => $data->note,                
                'status' => 'a',                
                'added_by' => $this->session->userdata('FullName'),  
                'add_time' => date('Y-m-d H:i:s'),
                'branch_id' => $this->session->userdata('BRANCHid')              
            );

            $this->db->insert('tbl_salary_payment', $salary);

            $res->success = true;
            $res->message = 'Salary payment success';
        } catch (\Exception $e) {
            $res->success = false;
            $res->message = 'faild' . $e->getMessage();   
        }

        echo json_encode($res);
    }

    public function upateSalarayPayment() {
        $res = new stdClass;
        try {
            $data = json_decode($this->input->raw_input_stream);

            $salary = array(
                'date' => $data->date,                
                'employee_id' => $data->employeeId,                
                'type' => $data->type,                
                'amount' => $data->amount,                
                'note' => $data->note,                
                'update_by' => $this->session->userdata('FullName'),  
                'update_time' => date('Y-m-d H:i:s'),
                'branch_id' => $this->session->userdata('BRANCHid')              
            );

            $this->db->where('id', $data->id)->update('tbl_salary_payment', $salary);

            $res->success = true;
            $res->message = 'Salary payment update success';
        } catch (\Exception $e) {
            $res->success = false;
            $res->message = 'faild' . $e->getMessage();   
        }

        echo json_encode($res);
    }

    public function deleteSalarayPayment() {
        $res = new stdClass;
        try {
            $data = json_decode($this->input->raw_input_stream);

            $this->db->set('status', 'd')->where('id', $data->paymentId)->update('tbl_salary_payment');

            $res->success = true;
            $res->message = 'Salary payment delete';
        } catch (\Exception $e) {
            $res->success = false;
            $res->message = 'failed'. $e->getMessage();
        }

        echo json_encode($res);
    }

    public function employeeStatement() {
        $access = $this->mt->userAccess();
        if(!$access){
            redirect(base_url());
        }

        $data['title'] = "Employee Statement";
        $data['content'] = $this->load->view("Administrator/employee/employee_statement", $data, true);
        $this->load->view("Administrator/index", $data);
    }
    
    public function salaryDueReport() {
        $access = $this->mt->userAccess();
        if(!$access){
            redirect(base_url());
        }

        $data['title'] = "Salary Due Report";
        $data['content'] = $this->load->view("Administrator/employee/salary_due", $data, true);
        $this->load->view("Administrator/index", $data);
    }

    public function getEmployeeSalaryLedger() {
        $res = new stdClass;
        $data = json_decode($this->input->raw_input_stream);
        $branchId = $this->session->userdata("BRANCHid");

        $payments = $this->db->query("
            select 
                'a' as sequence,
                ad.id,
                ad.date,
                concat('Salary - ', ad.attendance, ' ', a.note) as description,
                ad.salary as bill,
                0.00 as paid,
                0.00 as bonus,
                0.00 as deductions,
                0.00 as balance
            from tbl_attendance_details ad 
            join tbl_attendance a on a.id = ad.attendance_id
            where a.status = 'a'
            and a.branch_id = $branchId
            and ad.employee_id = $data->employeeId
            
            UNION
            select 
                'b' as sequence,
                sp.id,
                sp.date,
                concat('Salary payment - ', sp.note) as description,
                0.00 as bill,
                sp.amount as paid,
                0.00 as bonus,
                0.00 as deductions,
                0.00 as balance
            from tbl_salary_payment sp 
            where sp.status = 'a'
            and sp.branch_id = $branchId
            and sp.employee_id = $data->employeeId
            and sp.type = 'payment'
            
            UNION
            select 
                'c' as sequence,
                sp.id,
                sp.date,
                concat('Bonus - ', sp.note) as description,
                0.00 as bill,
                0.00 as paid,
                sp.amount as bonus,
                0.00 as deductions,
                0.00 as balance
            from tbl_salary_payment sp 
            where sp.status = 'a'
            and sp.branch_id = $branchId
            and sp.employee_id = $data->employeeId
            and sp.type = 'bonus'
            
            UNION
            select 
                'd' as sequence,
                sp.id,
                sp.date,
                concat('Deductions - ', sp.note) as description,
                0.00 as bill,
                0.00 as paid,
                0.00 as bonus,
                sp.amount as deductions,
                0.00 as balance
            from tbl_salary_payment sp 
            where sp.status = 'a'
            and sp.branch_id = $branchId
            and sp.employee_id = $data->employeeId
            and sp.type = 'deduct'

            order by date
        ")->result();

        $previousBalance = 0;

        foreach($payments as $key=>$payment){
            $lastBalance = $key == 0 ? 0 : $payments[$key - 1]->balance;
            $payment->balance = ($lastBalance + $payment->bill + $payment->bonus) - ($payment->paid + $payment->deductions);
        }

        if((isset($data->dateFrom) && $data->dateFrom != null) && (isset($data->dateTo) && $data->dateTo != null)){
            $previousPayments = array_filter($payments, function($payment) use ($data){
                return $payment->date < $data->dateFrom;
            });

            $previousBalance = count($previousPayments) > 0 ? $previousPayments[count($previousPayments) - 1]->balance : $previousBalance;

            $payments = array_filter($payments, function($payment) use ($data){
                return $payment->date >= $data->dateFrom && $payment->date <= $data->dateTo;
            });

            $payments = array_values($payments);
        }

        $res->payments = $payments;
        $res->previousBalance = $previousBalance;
        echo json_encode($res);
    }

    public function attendanceInvoice($id) {
        $data['title'] = "Employee Attendance List";
        $data['attId'] = $id;
        $data['content'] = $this->load->view("Administrator/employee/attendance_invoice", $data, true);
        $this->load->view("Administrator/index", $data);
    }
    
     public function salarySlip() {
        $access = $this->mt->userAccess();
        if(!$access){
            redirect(base_url());
        }
        $data['title'] = "Salary Slip";
        $data['content'] = $this->load->view("Administrator/employee/salary_slip", $data, true);
        $this->load->view("Administrator/index", $data);
    }

    public function getSalaryReport() {
        $data = json_decode($this->input->raw_input_stream);

        $clauses = "";
        $dateClause = "";
        $spClause = "";
        
        if(isset($data->dateFrom) && $data->dateFrom != "" && isset($data->dateTo) && $data->dateTo != "") {
            $dateClause .= " and ad.date between '$data->dateFrom' and '$data->dateTo'";
            $spClause .= " and sp.date between '$data->dateFrom' and '$data->dateTo'";
        } 

        if(isset($data->employeeId) && $data->employeeId != '') {
            $clauses .= " and e.Employee_SlNo = $data->employeeId";
        }

        $employees = $this->db->query("
            select 
                e.Employee_ID,
                e.Employee_Name,
                d.Designation_Name,
                (
                    select 
                        count(*)
                    from tbl_attendance_details ad 
                    where ad.employee_id = e.Employee_SlNo
                    and ad.attendance = 'present'
                    $dateClause
                ) as present,
                (
                    select 
                        count(*)
                    from tbl_attendance_details ad 
                    where ad.employee_id = e.Employee_SlNo
                    and ad.attendance = 'half_day'
                    $dateClause
                ) as half_day,
                (
                    select 
                        count(*)
                    from tbl_attendance_details ad 
                    where ad.employee_id = e.Employee_SlNo
                    and ad.attendance = 'absent'
                    $dateClause
                ) as absent,
                (
                    select 
                        ifnull(sum(sp.amount), 0)
                    from tbl_salary_payment sp
                    where sp.type = 'bonus'
                    and sp.employee_id = e.Employee_SlNo
                    and sp.status = 'a'
                    $spClause
                ) as bonus,
                (
                    select 
                        ifnull(sum(sp.amount), 0)
                    from tbl_salary_payment sp
                    where sp.type = 'deduct'
                    and sp.employee_id = e.Employee_SlNo
                    and sp.status = 'a'
                    $spClause
                ) as deductions,
                (
                    select 
                        ifnull(sum(sp.amount), 0)
                    from tbl_salary_payment sp
                    where sp.type = 'payment'
                    and sp.employee_id = e.Employee_SlNo
                    and sp.status = 'a'
                    $spClause
                ) as payment,
                (e.salary_range / 30)as daily_salary
            from tbl_employee e 
            join tbl_designation d on d.Designation_SlNo = e.Designation_ID
            where e.status = 'a'
            and e.Employee_brinchid = ?
            $clauses
        ", $this->session->userdata('BRANCHid'))->result();

        echo json_encode($employees);
    }
}
