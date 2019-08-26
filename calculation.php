<?php
require_once('db.inc.php');
require_once('common.inc.php');
ini_set('display_errors',1);
error_reporting(0);

$additions=$_POST['addition'];
$deductions=$_POST['deduction'];

/** Creating Salary Object */
$salary=new stdClass();
$salary->type=$_POST['salary_type'];
$salary->user_type=$_POST['employee_type'];
$salary->pay_frequency=$_POST['pay_frequency'];
//$salary->no_days=noDays();
$salary->state_id=$_POST['state'];
$salary->status_id=$_POST['marital_status'];
$salary->annual_salary=  ( trim($_POST['annual_salary'])!=''  ? $_POST['annual_salary'] : 40000  );
$salary->payDates=$_POST['payDateS'];
$salary->dependants=$_POST['employee_dependants'];

$salary->ytd_gross=$_POST['ytd_gross'];
$salary->startDate=$_POST['startDate'];


/**creating Hourly Object */

$hourly=new stdClass();
$hourly->type=$_POST['salary_type'];
$hourly->user_type=$_POST['employee_type'];
$hourly->pay_frequency=$_POST['pay_frequency'];
// $hourly->no_days=noDays();
$hourly->hourly_rate=$_POST['hourly_rate'] ;
$hourly->state_id=$_POST['state'];
$hourly->status_id=$_POST['marital_status'];
// $hourly->total_hour=$_POST['hourspayDate'];
$hourly->payDates=$_POST['payDateH'];
$hourly->dependants=$_POST['employee_dependants'];

$hourly->ytd_gross=$_POST['ytd_gross'];
$hourly->startDate=$_POST['startDate'];

/** Creating Company Object */
$company=new stdClass();
$company->name=$_POST['company_name'];
$company->logo=$_FILES['company_logo'];
$company->address=$_POST['company_address'];
$company->phone=$_POST['company_phone'];
$company->ein=$_POST['company_ein'];
$company->zip=$_POST['company_zip'];

$cquery='select * from postal_codes where zipcode='.$company->zip;
$resc=sql_query($cquery);
if( $rowc=sql_fetch_array($resc) ){
    $company->stateCity=$rowc['city'].', '.$rowc['state'].' '.$rowc['zipcode'];
}else{
    $company->stateCity='';
}



/** Creating User Object */
$user=new stdClass();
$user->type=$_POST['employee_type'];
$user->name=$_POST['name'];
$user->ssn=$_POST['ssn'];
$user->id=$_POST['emp_id'];
$user->address=$_POST['emp_address'];
$user->status=$_POST['marital_status'];
$user->zip=$_POST['emp_zip'];
$user->blind=$_POST['employee_blind'];
$user->dependants=$_POST['employee_dependants'];


$uquery='select * from postal_codes where zipcode='.$user->zip;
$resu=sql_query($uquery);
if( $rowu=sql_fetch_array($resu) ){
    $user->stateCity=$rowu['city'].', '.$rowu['state'].' '.$rowu['zipcode'];
}else{
    $user->stateCity='';
}

$payType=$_POST['salary_type'];

if($payType=='salary'){
    foreach($salary->payDates as $key=>$value){
        $salary->thisDate=( $value ? $value : date('m/d/Y') );
        $salary->no_days=noDays($salary->thisDate);
        $checkNo=$_POST['check_numbers'][$key];
        $totalArr[$checkNo]=calculateSalary($salary,$additions,$deductions);
    }
}elseif($payType=='hourly'){
    foreach($hourly->payDates as $key=>$value){
        $checkNo=$_POST['check_numbers'][$key];
        $hourly->thisDate=( $value ? $value : date('m/d/Y') );
        $hourly->no_days=noDays($hourly->thisDate);
        $hourly->total_hour=$_POST['hourspayDate'][$key];
        $totalArr[$checkNo]=calculateHourly($hourly,$additions,$deductions);
    }

}else{
    $return['code']=1;
    $return['data']='<h>Can not create Preview</h2>';
    echo json_encode($return);
    exit();
    
}

$templateID=$_POST['tempID'];

switch($templateID){
    case 1:{
        $html=getTemplateOne($company,$user,$totalArr,$additions,$deductions);
        break;
    }
    case 2:{
        $html=getTemplateTwo($company,$user,$totalArr,$additions,$deductions);
        break;
    }
    case 3:{
        $html=getTemplateThree($company,$user,$totalArr,$additions,$deductions);
        break;
    }
    case 4:{
        $html=getTemplateFour($company,$user,$totalArr,$additions,$deductions);
        break;
    }
    default:{
        $html=getTemplateOne($company,$user,$totalArr);
    }


}

$return['code']=0;
$return['data']=$html;
$return['cstateCity']=$company->stateCity;
$return['ustateCity']=$user->stateCity;
echo json_encode($return);
exit();


function noDays($date)  {

    $dateStamp=strtotime($date);
    $y=date("Y");
    $firstDate='01/01/'.$y;
    $firstStamp=strtotime($firstDate);
    $diff = ( $dateStamp - $firstStamp);
    return abs(round($diff / 86400));

}

?>