<?php

function calculateHourly($input,$additions,$deductions){
    $interval='Hourly';
    
    $hourlyRate=$input->hourly_rate;
    $totalHour=$input->total_hour;
    $payFrequency=$input->pay_frequency;
    $noDays=$input->no_days;

    $current= ($hourlyRate * $totalHour );

    switch($payFrequency){
        case "daily":
            $annualSalary= ($current*365);
            $paidPeriods=$noDays;
            $ytd= ($paidPeriods*$current);
            $interval=date('m/d/Y', strtotime('-3 days', strtotime($input->thisDate) ) ).'-'.date('m/d/Y', strtotime('- 3 days', strtotime($input->thisDate) ) );
            break;
        case "weekly":{
            $annualSalary= ($current*52);
            $paidPeriods=(int)($noDays/7);
            $ytd= ($paidPeriods*$current) + $current;

            $interval=date('m/d/Y', strtotime('-9 days', strtotime($input->thisDate) ) ).'-'.date('m/d/Y', strtotime('- 3 days', strtotime($input->thisDate) ) );
            break;
        }
        case "biweekly":{
            $annualSalary= ($current*26);
            $paidPeriods=(int)($noDays/14);
            $ytd= ($paidPeriods*$current) + $current;

            $interval=date('m/d/Y', strtotime('-16 days', strtotime($input->thisDate) ) ).'-'.date('m/d/Y', strtotime('- 3 days', strtotime($input->thisDate) ) );

            break;
        }
        case "semimonthly":{
            $annualSalary= ($current*24);
            $paidPeriods=(int)($noDays/15); 
            $ytd= ($paidPeriods*$current) + $current;
            
            $interval=date('m/d/Y', strtotime('-17 days', strtotime($input->thisDate) ) ).'-'.date('m/d/Y', strtotime('- 3 days', strtotime($input->thisDate) ) );
            break;

        }
        case "monthly":{
            $annualSalary= ($current*12);
            $paidPeriods=(int)($noDays/30);
            $ytd= ($paidPeriods*$current) + $current;
            
            $interval=date('m/d/Y', strtotime('-32 days', strtotime($input->thisDate) ) ).'-'.date('m/d/Y', strtotime('- 3 days', strtotime($input->thisDate) ) );
            break;
        }
        case "quaterly":{
            $annualSalary= ($current*4);
            $paidPeriods=(int)($noDays/90);
            $ytd= ($paidPeriods*$current) + $current;

            $interval=date('m/d/Y', strtotime('-92 days', strtotime($input->thisDate) ) ).'-'.date('m/d/Y', strtotime('- 3 days', strtotime($input->thisDate) ) );
            break;

        }
        case "semiannualy":{
            $annualSalary= ($current*2);
            $paidPeriods=(int)($noDays/180);
            $ytd= ($paidPeriods*$current) + $current;
            $interval=date('m/d/Y', strtotime('-182 days', strtotime($input->thisDate) ) ).'-'.date('m/d/Y', strtotime('- 3 days', strtotime($input->thisDate) ) );

            break;

        }
        case "once":
        case "annually":{
            $annualSalary= ($current*1);
            $paidPeriods=(int)($noDays/360);
            $ytd= ($paidPeriods*$current) + $current;

            $interval=date('m/d/Y', strtotime('-362 days', strtotime($input->thisDate) ) ).'-'.date('m/d/Y', strtotime('- 3 days', strtotime($input->thisDate) ) );
            break;

        }


    }

    //For mannual edit date
    if( strlen($input->startDate) > 0 ){
        $interval=$input->startDate;
    }

    $input->annual_salary=$annualSalary;

    $return=new stdClass();
    $additionCurrentTotal=0;
    $additionYTDTotal=0;
    foreach($additions as $row){
        $additionCurrentTotal += $row['currentAmount'];
        $additionYTDTotal += $row['ytdAmount'];        
    }

    $deductionCurrentTotal=0;
    $deductionYTDTotal=0;    
    foreach($deductions as $row){
        $deductionCurrentTotal += $row['currentAmount'];
        $deductionYTDTotal += $row['ytdAmount'];

    }

    /* Calculate weekly Salary */
    $wpaidPeriods=(int)($noDays/7);
    $wperDaySalary = ($annualSalary/364);
    $wperPeriodSalary=$wperDaySalary*7;
    $wcurrent=$wperPeriodSalary;
    // $wytd= ( $wperPeriodSalary*$wpaidPeriods)+$wperPeriodSalary;

    $deductionRate=getFedralDeductionRate($input->dependants,$wcurrent);

    $return->current=round($current, 2);
    $return->currentTotal=round($current, 2) + round($additionCurrentTotal,2);

    if( strlen($input->ytd_gross) > 0 ){
        $ytd=$input->ytd_gross+$return->current;
    }

    $return->ytd=round($ytd, 2);
    $return->ytdTotal=round($ytd, 2) + round($additionYTDTotal,2);

    //$stateTax=getStateTaxmultiple($input,$return->current,$return->ytd);
    $fedralTax=getFedralTaxBreak($input,$return->current,$return->ytd,$deductionRate);

    $healthTax=getHealthTax($input,$return->current,$return->ytd);
    $oasdiTax=getOASDITax($input,$return->current,$return->ytd);

    $taxdeductioncurrent= ($fedralTax->current + $healthTax->current + $oasdiTax->current ) ;
    $taxdeductionytd=($fedralTax->ytd + $healthTax->ytd + $oasdiTax->ytd );

    $taxableCurrentAmount=($return->current - $taxdeductioncurrent);
    $taxableYtdAmount= ($return->ytd - $taxdeductionytd);
 
    $stateTax=getStateTaxmultiple($input,$taxableCurrentAmount,$taxableYtdAmount);



    $curretTaxTotal= ($fedralTax->current + $stateTax->current + $healthTax->current + $oasdiTax->current );
    $ytdTaxTotal=($fedralTax->ytd + $stateTax->ytd + $healthTax->ytd + $oasdiTax->ytd );

    $return->stateTax=$stateTax;
    $return->fedralTax=$fedralTax;
    $return->healthTax=$healthTax;
    $return->oasdiTax=$oasdiTax;

    $return->currentTax= ($curretTaxTotal + $deductionCurrentTotal );
    $return->ytdTax= ($ytdTaxTotal + $deductionYTDTotal );

    $return->cNetPay= number_format( ( $return->currentTotal - $return->currentTax ) , 2, '.', '');
    $return->yNetPay= number_format( ( $return->ytdTotal -  $return->ytdTax ) , 2, '.', '');

    $return->total_hour=$input->total_hour;
    $return->hourly_rate=$input->hourly_rate;

    $return->payPeriodDate=$interval;
    $return->payDate=$input->thisDate;

    return $return;

}

function calculateSalary($input,$additions,$deductions){
    $return=new stdClass();
    $interval='Salary';

    $payFrequency=$input->pay_frequency;
    $noDays=$input->no_days;
    $annualSalary=$input->annual_salary;

    switch($payFrequency){
        case "daily":
            $paidPeriods=$noDays;
            $perPeriodSalary = ($annualSalary/360);
            $current=$perPeriodSalary;
            $ytd=($perPeriodSalary*$paidPeriods);

            $interval=date('m/d/Y', strtotime('-3 days ', strtotime($input->thisDate) ) ).'-'.date('m/d/Y', strtotime('- 3 days', strtotime($input->thisDate) ) );
            break;
        case "weekly":{
            $paidPeriods=(int)($noDays/7);
            $perDaySalary = ($annualSalary/364);
            $perPeriodSalary=$perDaySalary*7;
            $current=$perPeriodSalary;
            $ytd= ( $perPeriodSalary*$paidPeriods)+$perPeriodSalary;

            $interval=date('m/d/Y', strtotime('-9 days ',strtotime($input->thisDate) ) ).'-'.date('m/d/Y', strtotime('- 3 days', strtotime($input->thisDate)) );
            break;
        }
        case "biweekly":{
            $paidPeriods=(int)($noDays/14);
            $perDaySalary = ($annualSalary/364);
            $perPeriodSalary=$perDaySalary*14;
            $current=$perPeriodSalary;
            $ytd= ( $perPeriodSalary*$paidPeriods)+$perPeriodSalary;

            $interval=date('m/d/Y', strtotime('-16 days', strtotime($input->thisDate) ) ).'-'.date('m/d/Y', strtotime('- 3 days', strtotime($input->thisDate)) );
            break;

        }
        case "semimonthly":{
            $paidPeriods=(int)($noDays/15);
            $perDaySalary = ($annualSalary/360);
            $perPeriodSalary=$perDaySalary*15;
            $current=$perPeriodSalary;
            $ytd= ( $perPeriodSalary*$paidPeriods)+$perPeriodSalary;

            $interval=date('m/d/Y', strtotime('-17 days', strtotime($input->thisDate) ) ).'-'.date('m/d/Y', strtotime('- 3 days', strtotime($input->thisDate) ) );               
            break;

        }
        case "monthly":{
            $paidPeriods=(int)($noDays/30);
            $perDaySalary = ($annualSalary/360);
            $perPeriodSalary=$perDaySalary*30;
            $current=$perPeriodSalary;
            $ytd= ( $perPeriodSalary*$paidPeriods)+$perPeriodSalary;

            $interval=date('m/d/Y', strtotime('-32 days', strtotime($input->thisDate) ) ).'-'.date('m/d/Y', strtotime('- 3 days', strtotime($input->thisDate) ) );
            break;

        }
        case "quaterly":{
            $paidPeriods=(int)($noDays/90);
            $perDaySalary = ($annualSalary/360);
            $perPeriodSalary=$perDaySalary*90;
            $current=$perPeriodSalary;
            $ytd= ( $perPeriodSalary*$paidPeriods)+$perPeriodSalary;

            $interval=date('m/d/Y', strtotime('-92 days', strtotime($input->thisDate) ) ).'-'.date('m/d/Y', strtotime('- 3 days', strtotime($input->thisDate) ) );
            break;

        }
        case "semiannualy":{
            $paidPeriods=(int)($noDays/180);
            $perDaySalary = ($annualSalary/360);
            $perPeriodSalary=$perDaySalary*180;
            $current=$perPeriodSalary;
            $ytd= ( $perPeriodSalary*$paidPeriods)+$perPeriodSalary;

            $interval=date('m/d/Y', strtotime('-182 days', strtotime($input->thisDate) ) ).'-'.date('m/d/Y', strtotime('- 3 days', strtotime($input->thisDate) ) );
            break;

        }
        case "once":
        case "annually":{
            $paidPeriods=(int)($noDays/360);
            $perDaySalary = ($annualSalary/360);
            $perPeriodSalary=$perDaySalary*360;
            $current=$perPeriodSalary;
            $ytd= ( $perPeriodSalary*$paidPeriods)+$perPeriodSalary;

            $interval=date('m/d/Y', strtotime('-362 days', strtotime($input->thisDate) ) ).'-'.date('m/d/Y', strtotime('- 3 days', strtotime($input->thisDate) ) );
            break;
        }


    }

    //For mannual edit date
    if( strlen($input->startDate) > 0 ){
        $interval=$input->startDate;
    }

    $additionCurrentTotal=0;
    $additionYTDTotal=0;
    foreach($additions as $row){
        $additionCurrentTotal += $row['currentAmount'];
        $additionYTDTotal += $row['ytdAmount'];

    }

    $deductionCurrentTotal=0;
    $deductionYTDTotal=0;    
    foreach($deductions as $row){
        $deductionCurrentTotal += $row['currentAmount'];
        $deductionYTDTotal += $row['ytdAmount'];

    }

    /* Calculate weekly Salary */
    $wpaidPeriods=(int)($noDays/7);
    $wperDaySalary = ($annualSalary/364);
    $wperPeriodSalary=$wperDaySalary*7;
    $wcurrent=$wperPeriodSalary;
    // $wytd= ( $wperPeriodSalary*$wpaidPeriods)+$wperPeriodSalary;

    $deductionRate=getFedralDeductionRate($input->dependants,$wcurrent);

    $return->current= format_num( round($current, 2) );
    $return->currentTotal= format_num( round($current, 2) + round($additionCurrentTotal,2) );

    if( strlen($input->ytd_gross) > 0 ){
        $ytd=$input->ytd_gross+$return->current;
    }

    $return->ytd= format_num( round($ytd, 2) );
    $return->ytdTotal= format_num( round($ytd, 2) + round($additionYTDTotal,2)  );

    // $stateTax=getStateTaxmultiple($input,$return->current,$return->ytd);    
    $fedralTax=getFedralTaxBreak($input,$return->current,$return->ytd,$deductionRate);

    $healthTax=getHealthTax($input,$return->current,$return->ytd);
    $oasdiTax=getOASDITax($input,$return->current,$return->ytd);

    $taxdeductioncurrent= ($fedralTax->current + $healthTax->current + $oasdiTax->current ) ;
    $taxdeductionytd=($fedralTax->ytd + $healthTax->ytd + $oasdiTax->ytd );

    $taxableCurrentAmount=($return->current - $taxdeductioncurrent);
    $taxableYtdAmount= ($return->ytd - $taxdeductionytd);
 
    $stateTax=getStateTaxmultiple($input,$taxableCurrentAmount,$taxableYtdAmount);

    $curretTaxTotal= ($fedralTax->current + $stateTax->current + $healthTax->current + $oasdiTax->current ) ;
    $ytdTaxTotal=($fedralTax->ytd + $stateTax->ytd + $healthTax->ytd + $oasdiTax->ytd );

    $return->stateTax=$stateTax;
    $return->fedralTax=$fedralTax;
    $return->healthTax=$healthTax;
    $return->oasdiTax=$oasdiTax;

    $return->currentTax= format_num( ($curretTaxTotal + $deductionCurrentTotal ) );
    $return->ytdTax=format_num( ($ytdTaxTotal + $deductionYTDTotal ) );

    $return->cNetPay= number_format( ( $return->currentTotal - $return->currentTax ) , 2, '.', '');
    $return->yNetPay= number_format( ( $return->ytdTotal -  $return->ytdTax ) , 2, '.', '');
    $return->payPeriodDate=$interval;
    $return->payDate=$input->thisDate;

    return $return;

}

function format_num($num){
    return number_format( $num , 2, '.', '');

}

function getStateTax($input,$current,$ytd){
    $stateTax=new stdClass();
    $query='SELECT AVG(state_tax) FROM `income_brackets` WHERE state_id='.$input->state_id.' AND status_id='.$input->status_id.' AND min < '.$input->annual_salary.' ';

    $res=sql_query($query);
    if($row=sql_fetch_array($res) ){
        $st=$row['AVG(state_tax)'];
    }else{
        $st=0;
    }

    if($input->user_type=='contractor'){
        $stateTax->current= 0;
        $stateTax->ytd= 0;    
    }else{
        $stateTax->current= round( ($st/100)*$current, 2);
        $stateTax->ytd= round( ($st/100)*$ytd, 2 );
    }

    return $stateTax;

}

function getStateTaxFlat($input,$current,$ytd){

    $stateTax=new stdClass();
    $query='SELECT state_tax FROM `income_brackets` WHERE state_id='.$input->state_id.' AND status_id='.$input->status_id.' AND ( CASE WHEN max=0 THEN ( '.$input->annual_salary.' > min ) ELSE ( '.$input->annual_salary.' BETWEEN min AND max ) END ) ';

    $res=sql_query($query);
    if($row=sql_fetch_array($res) ){
        $st=$row['state_tax'];
    }else{
        $st=0;
    }

    $stateTax->current= round( ($st/100)*$current, 2);
    $stateTax->ytd= round( ($st/100)*$ytd, 2 );

    return $stateTax;

}

function getStateTaxmultiple($input,$current,$ytd){
    $stateTax=new stdClass();
    $query='SELECT t.min,t.max,t.state_tax,s.name FROM `income_brackets` t LEFT JOIN states s on s.id=t.state_id where status_id='.$input->status_id.' AND state_id='.$input->state_id.' AND min < '.$ytd.' ORDER BY min ASC';

    $res=sql_query($query);

    $leftAmount=$ytd;
    $tax=0;
    while($row=sql_fetch_array($res) ){
        $st=$row['state_tax'];
        $bracket=$row['max']-$row['min'];

        if( $bracket <= 0 ){
            $taxable=$leftAmount;
            $leftAmount=0;
        }elseif($bracket > $leftAmount){
            $taxable=$leftAmount;
            $leftAmount=0;
        }else{
            $taxable=$bracket;
            $leftAmount=$leftAmount-$bracket;
        }

        $tax += round( ($st/100)*$taxable, 2);
        $stateTax->name=ucfirst(strtolower($row['name'])).' State Tax ';

    }


    if($input->user_type=='contractor'){
        $stateTax->current= 0;
        $stateTax->ytd= 0;    
    }elseif($ytd==0 && $current==0){
        $stateTax->current= 0;
        $stateTax->ytd= 0;
    }else{
        $stateTax->current= format_num( round( ($tax / ($ytd/$current) ), 2 ) );
        $stateTax->ytd=format_num( round($tax, 2) );
    }

    return $stateTax;

}

function getFedralDeductionRate($dependent,$wsalary){

    $query='SELECT * FROM fedral_deductions where depend= '.$dependent.' AND ( CASE WHEN max=0 THEN ( '.$wsalary.' > min ) ELSE ( '.$wsalary.' BETWEEN min AND max ) END ) ';
    $res=sql_query($query);
    if($row=sql_fetch_array($res)){
        return $row['deductionRate'];
    }else{
        return 0;
    }

}

function getFedralTax($input,$current,$ytd){

    $fedralTax=new stdClass();
    $query='SELECT fedral_tax FROM `fedral_tax` WHERE status_id='.$input->status_id.' AND ( CASE WHEN max=0 THEN ( '.$input->annual_salary.' > min ) ELSE ( '.$input->annual_salary.' BETWEEN min AND max ) END ) ';

    $res=sql_query($query);
    if($row=sql_fetch_array($res) ){
        $ft=$row['fedral_tax'];
    }else{
        $ft=0;
    }

    if($input->user_type=='contractor'){
        $fedralTax->current= 0;
        $fedralTax->ytd= 0;    
    }else{
        $fedralTax->current= round( ($ft/100)*$current, 2);
        $fedralTax->ytd= round( ($ft/100)*$ytd, 2 );
    }

    return $fedralTax;

}

function getFedralTaxBreak($input,$current,$ytd,$deductionRate){

    $fedralTax=new stdClass();
    $query='SELECT min,max,fedral_tax FROM `fedral_tax` where status_id='.$input->status_id.' AND min < '.$input->annual_salary.' ORDER BY min ASC';

    $res=sql_query($query);

    $leftAmount=$input->annual_salary;
    $tax=0;
    while($row=sql_fetch_array($res) ){
        $ft=$row['fedral_tax'];
        $bracket=$row['max']-$row['min'];

        if( $bracket < 0 ){
            $taxable=$leftAmount;
            $leftAmount=0;
        }elseif($bracket > $leftAmount){
            $taxable=$leftAmount;
            $leftAmount=0;
        }else{
            $taxable=$bracket;
            $leftAmount=$leftAmount-$bracket;
        }

        $tax += round( ($ft/100)*$taxable, 2);

    }

    if($input->user_type=='contractor'){

        $fedralTax->current= 0;
        $fedralTax->ytd= 0;    

    }elseif( $current==0 && $ytd==0 ){

        $fedralTax->current= 0;
        $fedralTax->ytd= 0;

    }else{

        // $tax =  ($tax*(54/100) );
       
        $tax =  ($tax*($deductionRate/100) );
        
        // $fedralTax->current=format_num( round( ($tax / ($ytd/$current) ) , 2 ) );
        // $fedralTax->ytd=format_num( round($tax , 2) );
        $fedralTax->current=format_num( round( ($tax / ($input->annual_salary/$current) ) , 2 ) );
        $fedralTax->ytd=format_num( round( ($tax / ($input->annual_salary/$ytd) ) , 2 ) );


    }

    return $fedralTax;

}

function getHealthTax($input,$current,$ytd){
    $healthTax=new stdClass();

    $ht=1.45;

    if($input->user_type=='contractor'){
        $healthTax->current= 0;
        $healthTax->ytd= 0;
    }else{
        $healthTax->current=format_num( round( ($ht/100)*$current, 2) );
        $healthTax->ytd=format_num( round( ($ht/100)*$ytd, 2 ) );
    }

    return $healthTax;
        
}

function getOASDITax($input,$current,$ytd){
    $oasdiTax=new stdClass();

    $ot=6.2;

    if($input->user_type=='contractor'){
        $oasdiTax->current= 0;
        $oasdiTax->ytd= 0;
    }else{
        $oasdiTax->current=format_num( round( ($ot/100)*$current, 2) );
        $oasdiTax->ytd=format_num( round( ($ot/100)*$ytd, 2 ) );
    }

    return $oasdiTax;
        
}

function getTemplateOne($company,$user,$totalArr,$additions,$deductions){

    $additionliname='';
    $additionlicurrent='';
    $additionliytd='';

    foreach($additions as $row){
        $additionliname.='<li>'.$row['desc'].'</li>';
        $additionlicurrent.='<li>'.$row['currentAmount'].'</li>';
        $additionliytd.='<li>'.$row['ytdAmount'].'</li>';
    }

    $deductionliname='';
    $deductionlicurrent='';
    $deductionliytd='';

    foreach($deductions as $row){
        $deductionliname.='<li>'.$row['desc'].'</li>';
        $deductionlicurrent.='<li>'.$row['currentAmount'].'</li>';
        $deductionliytd.='<li>'.$row['ytdAmount'].'</li>';

    }

    foreach($totalArr as $key=>$check){
        $str='
        <div id="temp1">
            <div class="container-fluid">
                <div class="row companyRow">
                    '.( !empty($_FILES['company_logo']['tmp_name']) ? '<div class="col-md-2"><img width="100%;" src="data:image/png;base64,'.base64_encode(file_get_contents($_FILES['company_logo']['tmp_name'])).'" /></div>' : '').'
                    <div class=" '.(empty($_FILES['company_logo']['tmp_name']) ? 'col-md-9' : 'col-md-7' ).' ">
                        <ul>
                        '.( $company->name ? '<li>'.$company->name.'</li>' : '' ).'
                        '.( $company->address ? '<li>'.$company->address.'</li>' : '' ).'
                        '.( $company->phone ? '<li>'.$company->phone.'</li>' : '' ).'
                        '.( $company->ein ? '<li>EIN:'.$company->ein.'</li>' : '' ).'
                        </ul>
                    </div>
                    <div class="col-md-3 vCenter">
                        <span class="estatement">Earnings Statement</span>
                    </div>
                </div>
    
                <div class="row employeeRow">
                    <div class="col-md-12">
                        <table class="table" id="employedetails">
                            <tr>
                                <th>Employee Name</th>
                                <th>Social Sec. ID</th>
                                <th>Employee ID</th>
                                <th>Check No.</th>
                                <th>Pay Record</th>
                                <th>Pay Date</th>
                            </tr>
                            <tr>
                                <td>'. ( $user->name ? $user->name : 'John Doe' ) .' </td>
                                <td>'. ( $user->ssn ? $user->ssn : '' ) .'</td>
                                <td>'. ( $user->id ? $user->id : '' ) .'</td>
                                <td>#'. ( strlen($key) ? $key : '' ) .'</td>
                                <td>'. $check->payPeriodDate .'</td>
                                <td>'. $check->payDate .'</td>
                            </tr>
                        </table>
                    </div>
                </div>
    
                <div class="row earningRow">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="earningTable" width="100%">
                                    <tr>
                                        <th width="32%">Earnings</th>
                                        <th width="18%">Rate</th>
                                        <th width="25%">Hours</th>
                                        <th width="25%">Current</th>
                                    </tr>
                                </table>
                                <div class="row" style="margin:0;">
                                    <div class="col-md-4 firstColumn">
                                        <ul class="innerUL">
                                            <li>Regular Earnings</li>
                                            '.$additionliname.'
                                        </ul>
                                    </div>
                                    <div class="col-md-2">
                                        <ul class="innerUL">
                                            <li>'.$check->hourly_rate.'</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-3">
                                        <ul class="innerUL">
                                            <li>'.$check->total_hour.'</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-3">
                                        <ul class="innerUL">
                                            <li>'.$check->current.'</li>
                                            '.$additionlicurrent.'
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <table class="deductionTable" width="100%">
                                    <tr>
                                        <th width="33.33%">Deductions</th>
                                        <th width="33.33%">Current</th>
                                        <th width="33.33%">Year To Date</th>
                                    </tr>
                                </table>
                                <div class="row" style="margin:0;">
                                    <div class="col-md-4 firstColumn">
                                        <ul class="innerUL">
                                            <li>Fedral Tax</li>
                                            <li>'.$check->stateTax->name.'</li>
                                            <li>OASDI Tax</li>
                                            <li>Health Insurance Tax</li>
                                            '.$deductionliname.'
                                        </ul>
                                    </div>
                                    <div class="col-md-4">
                                        <ul class="innerUL">
                                            <li>'.$check->fedralTax->current.'</li>
                                            <li>'.$check->stateTax->current.'</li>
                                            <li>'.$check->oasdiTax->current.'</li>
                                            <li>'.$check->healthTax->current.'</li>
                                            '.$deductionlicurrent.'
                                        </ul>
                                    </div>
                                    <div class="col-md-4">
                                        <ul class="innerUL">
                                            <li>'.$check->fedralTax->ytd.'</li>
                                            <li>'.$check->stateTax->ytd.'</li>
                                            <li>'.$check->oasdiTax->ytd.'</li>
                                            <li>'.$check->healthTax->ytd.'</li>
                                            '.$deductionliytd.'
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                        </div>    
                    </div>
                </div>
        
                <div class="row totalRow">
                    <div class="col-md-12">
                        <table width="100%">
                            <tr>
                                <th>YTD Gross</th>
                                <th>YTD Deductions</th>
                                <th>YTD Net Pay</th>
                                <th>Current Total</th>
                                <th>Current Deductions</th>
                                <th>Net Pay</th>
                            </tr>
                            <tr>
                                <td>'.$check->ytdTotal.'</td>
                                <td>'.$check->ytdTax.'</td>
                                <td>'.$check->yNetPay.'</td>
                                <td>'.$check->currentTotal.'</td>
                                <td>'.$check->currentTax.'</td>
                                <td>'.$check->cNetPay.'</td>
                            </tr>
                        </table>
                    </div>
                </div>            
            </div>
        </div>
        ';

    }

    return $str;



}

function getTemplateTwo($company,$user,$totalArr,$additions,$deductions){

    $additionliname='';
    $additionlicurrent='';
    $additionliytd='';

    foreach($additions as $row){
        $additionliname.='<li>'.$row['desc'].'</li>';
        $additionlicurrent.='<li>'.display_format($row['currentAmount']).'</li>';
        $additionliytd.='<li>'.display_format($row['ytdAmount']).'</li>';
    }

    $deductionliname='';
    $deductionlicurrent='';
    $deductionliytd='';

    foreach($deductions as $row){
        $deductionliname.='<li>'.$row['desc'].'</li>';
        $deductionlicurrent.='<li>'.display_format($row['currentAmount']).'</li>';
        $deductionliytd.='<li>'.display_format($row['ytdAmount']).'</li>';

    }

    foreach($totalArr as $key=>$check){
        $str='
        <div id="temp2">
            <div class="container-fluid">
    
                <div class="row companyRow">
                    '.( !empty($_FILES['company_logo']['tmp_name']) ? '<div class="col-md-2"><img width="100%;" src="data:image/png;base64,'.base64_encode(file_get_contents($_FILES['company_logo']['tmp_name'])).'" /></div>' : '').'
                    <div class=" '.(empty($_FILES['company_logo']['tmp_name']) ? 'col-md-9' : 'col-md-7' ).' ">
                        <ul>
                        '.( $company->name ? '<li>'.$company->name.'</li>' : '' ).'
                        '.( $company->address ? '<li>'.$company->address.'</li>' : '' ).'
                        '.( $company->stateCity ? '<li>'.$company->stateCity.'</li>' : '' ).'
                        '.( $company->phone ? '<li>'.$company->phone.'</li>' : '' ).'
                        '.( $company->ein ? '<li>EIN:'.$company->ein.'</li>' : '' ).'
                        </ul>
                    </div>
                    <div class="col-md-3 vCenter">
                        <span class="estatement">Earnings Statement</span>
                    </div>
                </div>
    
                <div class="row employeeRow">
                    <div class="col-md-12">
                        <table class="table" id="employedetails">
                            <tr>
                                <th>Employee Name</th>
                                <th>Social Sec. ID</th>
                                <th>Employee ID</th>
                                <th>Check No.</th>
                                <th>Pay Record</th>
                                <th>Pay Date</th>
                            </tr>
                            <tr>
                                <td>'. ( $user->name ? $user->name : 'John Doe' ) .' </td>
                                <td>'. ( $user->ssn ? $user->ssn : '' ) .'</td>
                                <td>'. ( $user->id ? $user->id : '' ) .'</td>
                                <td>'. ( strlen($key) ? $key : '' ) .'</td>
                                <td>'.$check->payPeriodDate.'</td>
                                <td>'. $check->payDate .'</td>
                            </tr>
                        </table>
                    </div>
                </div>
    
                <div class="row earningRow">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="earningTable" width="100%">
                                    <tr>
                                        <th width="32%">Earnings</th>
                                        <th width="18%">Rate</th>
                                        <th width="25%">Hours</th>
                                        <th width="25%">Current</th>
                                    </tr>
                                </table>
                                <div class="row" style="margin:0;">
                                    <div class="col-md-4 firstColumn">
                                        <ul class="innerUL">
                                            <li>Regular Earnings</li>
                                            '.$additionliname.'
                                        </ul>
                                    </div>
                                    <div class="col-md-2">
                                        <ul class="innerUL">
                                            <li>'. ( strlen($check->hourly_rate) ? display_format($check->hourly_rate) : ''  ).'</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-3">
                                        <ul class="innerUL">
                                            <li>'. ( strlen($check->total_hour) ? display_format($check->total_hour) : ''  ).'</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-3">
                                        <ul class="innerUL">
                                            <li>'.display_format($check->current).'</li>
                                            '.$additionlicurrent.'
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <table class="deductionTable" width="100%">
                                    <tr>
                                        <th width="50%">Deductions</th>
                                        <th width="25%">Current</th>
                                        <th width="25%">Year To Date</th>
                                    </tr>
                                </table>
                                <div class="row" style="margin:0;">
                                    <div class="col-md-6 firstColumn">
                                        <ul class="innerUL" style="padding-left:6px;">
                                            <li>Fedral Tax</li>
                                            <li>'.$check->stateTax->name.'</li>
                                            <li>OASDI Tax</li>
                                            <li>Health Insurance Tax</li>
                                            '.$deductionliname.'
                                        </ul>
                                    </div>
                                    <div class="col-md-3">
                                        <ul class="innerUL">
                                            <li>'.display_format($check->fedralTax->current).'</li>
                                            <li>'.display_format($check->stateTax->current).'</li>
                                            <li>'.display_format($check->oasdiTax->current).'</li>
                                            <li>'.display_format($check->healthTax->current).'</li>
                                            '.$deductionlicurrent.'
                                        </ul>
                                    </div>
                                    <div class="col-md-3">
                                        <ul class="innerUL">
                                            <li>'.display_format($check->fedralTax->ytd).'</li>
                                            <li>'.display_format($check->stateTax->ytd).'</li>
                                            <li>'.display_format($check->oasdiTax->ytd).'</li>
                                            <li>'.display_format($check->healthTax->ytd).'</li>
                                            '.$deductionliytd.'
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                        </div>    
                    </div>
                </div>
        
                <div class="row totalRow">
                    <div class="col-md-12">
                        <table width="100%">
                            <tr>
                                <th>YTD Gross</th>
                                <th>YTD Deductions</th>
                                <th>YTD Net Pay</th>
                                <th>Current Total</th>
                                <th>Current Deductions</th>
                                <th>Net Pay</th>
                            </tr>
                            <tr>
                                <td>$'.display_format($check->ytdTotal).'</td>
                                <td>$'.display_format($check->ytdTax).'</td>
                                <td>$'.display_format($check->yNetPay).'</td>
                                <td>$'.display_format($check->currentTotal).'</td>
                                <td>$'.display_format($check->currentTax).'</td>
                                <td>$'.display_format($check->cNetPay).'</td>
                            </tr>
                        </table>
                    </div>
                </div>            
            </div>
        </div>
        ';

    }

    return $str;

}

function getTemplateThree($company,$user,$totalArr,$additions,$deductions){
    $additionliname='';
    $additionlicurrent='';
    $additionliytd='';

    foreach($additions as $row){
        $additionliname.='<li>'.$row['desc'].'</li>';
        $additionlicurrent.='<li>'.display_format($row['currentAmount']).'</li>';
        $additionliytd.='<li>'.display_format($row['ytdAmount']).'</li>';
    }

    $deductionliname='';
    $deductionlivalue='';
    $deductionliytd='';
    foreach($deductions as $row){
        $deductionliname.='<li>'.$row['desc'].'</li>';
        $deductionlicurrent.='<li>'.display_format($row['currentAmount']).'</li>';
        $deductionliytd.='<li>'.display_format($row['ytdAmount']).'</li>';
    }

    foreach($totalArr as $key=>$check){
        $str='
        <div id="temp3">
            <div class="container-fluid">
                <div class="row companyRow">
                    '.( !empty($_FILES['company_logo']['tmp_name']) ? '<div class="col-md-2"><img width="100%;" src="data:image/png;base64,'.base64_encode(file_get_contents($_FILES['company_logo']['tmp_name'])).'" /></div>' : '').'
                    <div class=" '.(empty($_FILES['company_logo']['tmp_name']) ? 'col-md-9' : 'col-md-7' ).' ">
                        <ul>
                        '.( $company->name ? '<li class="companyname">'.$company->name.'</li>' : '' ).'
                        '.( $company->address ? '<li>'.$company->address.'</li>' : '' ).'
                        '.( $company->stateCity ? '<li>'.$company->stateCity.'</li>' : '' ).'
                        '.( $company->phone ? '<li>'.$company->phone.'</li>' : '' ).'
                        '.( $company->ein ? '<li>EIN:'.$company->ein.'</li>' : '' ).'
                        </ul>
                    </div>
                    <div class="col-md-3 vCentert">
                        <span class="estatement">Earnings Statement</span><br>
                        <span>Period Ending: '. date('m/d/Y', strtotime('-3 days', strtotime($check->payDate) ) ) .'</span><br>
                        <span>Pay Date: '. $check->payDate .'</span>
                    </div>
                </div>
    
                <div class="row employeeRow">

                    <div class="col-md-5">
                        <ul>
                            <li><b>SSN:</b> '. ( $user->ssn ? $user->ssn : '' ) .'</li>
                            <li><b>Taxable Marital Status:</b> '.( $user->status==1 ? 'Single': ($user->status==2 ? 'Married' : 'Head of household' )  ).' </li>
                            <li><b>Exemptions/Allowances:</b> '.$user->blind.'</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                    </div>
                    <div class="col-md-3">
                        <ul>
                            <li><b>'. ( $user->name ? $user->name : 'John Doe' ) .'</b></li>
                            <li><b>'. ( $user->address ? $user->address : '' ) .'</b></li>
                            <li><b>'. ( $user->stateCity ? $user->stateCity : '' ) .'</b></li>
                        </ul>
                    </div>

                </div>
    
                <div class="row earningRow">
                    <div class="col-md-12 pd-0">
                        <div class="row">
                            <div class="col-md-12">
                                <table class="earningTable" width="100%">
                                    <tr>
                                        <th width="25%">Earnings</th>
                                        <th width="15%">Rate</th>
                                        <th width="10%">Hours</th>
                                        <th width="25%">Current</th>
                                        <th width="25%">Year To Date</th>
                                    </tr>
                                </table>
                                <div class="row" style="margin:0;">
                                    <div class="col-md-3 firstColumn">
                                        <ul class="innerUL">
                                            <li>Regular Earnings</li>
                                            '.$additionliname.'
                                        </ul>
                                    </div>
                                    <div class="col-md-2">
                                        <ul class="innerUL">
                                            <li>'. ( strlen($check->hourly_rate) ? display_format($check->hourly_rate) : ''  ).'</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-1">
                                        <ul class="innerUL">
                                            <li>'. ( strlen($check->total_hour) ? display_format($check->total_hour) : ''  ).'</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-3">
                                        <ul class="innerUL">
                                            <li>'.display_format($check->current).'</li>
                                            '.$additionlicurrent.'
                                        </ul>
                                    </div>
                                    <div class="col-md-3">
                                        <ul class="innerUL">
                                            <li>'.display_format($check->ytd).'</li>
                                            '.$additionliytd.'
                                        </ul>
                                    </div>

                                </div>
                                <table class="grossRow" width="100%">
                                    <tr>
                                        <td width="25%">Gross Pay</td>
                                        <td width="15%"></td>
                                        <td width="10%"></td>
                                        <td width="25%">$'.display_format($check->currentTotal).'</td>
                                        <td width="25%">$'.display_format($check->ytdTotal).'</td>
                                    </tr>
                                </table>

                            </div>
                        
                        </div>

                        <div class="row pt-4">
                            <div class="col-md-12">
                                <table class="deductionTable" width="100%">
                                    <tr>
                                        <th width="25%">Deductions</th>
                                        <th width="25%">Type</th>
                                        <th width="25%">This Period</th>
                                        <th width="25%">Year To Date</th>
                                    </tr>
                                </table>
                                <div class="row" style="margin:0;">
                                    <div class="col-md-3">
                                        <ul class="innerUL">
                                            <li></li>
                                        </ul>
                                    </div>
                                    <div class="col-md-3 firstColumn">
                                        <ul class="innerUL">
                                            <li>Fedral Tax</li>
                                            <li>'.$check->stateTax->name.'</li>
                                            <li>OASDI Tax</li>
                                            <li>Health Insurance Tax</li>
                                            '.$deductionliname.'
                                        </ul>
                                    </div>
                                    <div class="col-md-3">
                                        <ul class="innerUL">
                                            <li>'.display_format($check->fedralTax->current).'</li>
                                            <li>'.display_format($check->stateTax->current).'</li>
                                            <li>'.display_format($check->oasdiTax->current).'</li>
                                            <li>'.display_format($check->healthTax->current).'</li>
                                            <li>'.$deductionlicurrent.'</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-3">
                                        <ul class="innerUL">
                                            <li>'.display_format($check->fedralTax->ytd).'</li>
                                            <li>'.display_format($check->stateTax->ytd).'</li>
                                            <li>'.display_format($check->oasdiTax->ytd).'</li>
                                            <li>'.display_format($check->healthTax->ytd).'</li>
                                            '.$deductionliytd.'
                                        </ul>
                                    </div>

                                </div>
                                <table class="grossRow" width="100%">
                                    <tr>
                                        <td width="25%">Net Pay</td>
                                        <td width="15%"></td>
                                        <td width="10%"></td>
                                        <td width="25%">$'.display_format($check->cNetPay).'</td>
                                        <td width="25%">$'.display_format($check->yNetPay).'</td>
                                    </tr>
                                </table>

                            </div>
                            
                        </div>    
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <label><b> Your Deductions for this period are $'.display_format($check->currentTax).' </b></span>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <img src="images/scissor.png" />
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-4">
                        <ul>
                            <li>'. ( $company->name ? $company->name : '' ) .'</li>
                            '.( $company->address ? '<li>'.$company->address.'</li>' : '' ).'
                            '.( $company->stateCity ? '<li>'.$company->stateCity.'</li>' : '' ).'
                        </ul>
                    </div>
                    <div class="col-md-5">
                    </div>
                    <div class="col-md-3">
                        <ul>
                            <li>Payroll Check Number</li>
                            <li>'. ( strlen($key) ? $key : '' ) .'</li>
                            <li>Pay Date: '. $check->payDate .'</li>
                        </ul>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12">
                        <b>'. ( $user->name ? $user->name : 'John Doe' ) .'</b>
                    </div>
                </div>

                <div class="row mt-3 grossRow" style="background-color:#ebebeb;padding: 15px 15px;">
                    <div class="col-md-9">
                        <b>'. numberTowords($check->cNetPay).' CENTS</b>
                    </div>
                    <div class="col-md-3">
                        <b>$'.display_format($check->cNetPay).'</b>
                    </div>
                </div>

                <div class="row mt-5">
                    <div class="col-md-7"></div>
                    <div class="col-md-5" style="border-top: 1px solid grey;">
                        <br>
                        <span>Authorized <br> Signature</span>
                    </div>
                </div>

            </div>
        </div>
        ';

    }

    return $str;

}

function getTemplateFour($company,$user,$totalArr,$additions,$deductions){

    $additionliname='';
    $additionlicurrent='';
    $additionliytd='';

    foreach($additions as $row){
        $additionliname.='<li>'.trim($row['desc']).'</li>';
        $additionlicurrent.='<li>'.display_format($row['currentAmount']).'</li>';
        $additionliytd.='<li>'.display_format($row['ytdAmount']).'</li>';
    }

    $deductionliname='';
    $deductionlicurrent='';
    $deductionliytd='';

    foreach($deductions as $row){
        $deductionliname.='<li>'.trim($row['desc']).'</li>';
        $deductionlicurrent.='<li>'.display_format($row['currentAmount']).'</li>';
        $deductionliytd.='<li>'.display_format($row['ytdAmount']).'</li>';

    }

    foreach($totalArr as $key=>$check){
        $dateArr = explode('-',$check->payPeriodDate);
        $startDate=$dateArr[0];
        $endDate=$dateArr[1];

        $str='
        <div id="temp4">
            <div class="container-fluid">

                <div class="row mt-5">
                </div>

                <div class="row">
                    '.( !empty($_FILES['company_logo']['tmp_name']) ? '<div class="col-md-2"><img width="100%;" src="data:image/png;base64,'.base64_encode(file_get_contents($_FILES['company_logo']['tmp_name'])).'" /></div>' : '').'
                    <div class=" '.(empty($_FILES['company_logo']['tmp_name']) ? 'col-md-9' : 'col-md-7' ).' ">
                       <h5>'.$company->name.'</h5>
                       <ul>
                           <li>'.$company->address.'</li>
                           <li>'.$company->stateCity.'</li>
                        </ul>
                    </div>
                    <div class="col-md-3 vCenter text-right">
                        <span class="estatement">'. date('F d,Y', strtotime($check->payDate) ) .'</span>
                    </div>
                </div>
                
                <div class="row mt-5" style="border-top: 3px solid #c6c6c6;border-bottom:3px solid #c6c6c6;">
                    <div class="col-md-8 vCenter">
                        <b>PAY '. numberTowords($check->cNetPay).' CENTS</b>
                    </div>
                    <div class="col-md-4 text-right">
                        <b>$'.display_format($check->cNetPay).'</b>
                        <p>This is not a check.</p>
                    </div>
                </div>

                <div class="row mt-2 mb-2">
                    <div class="col-md-2">Pay to the order of</div>
                    <div class="col-md-10">
                        <ul class="innerLI">
                            <li><b>'. ( $user->name ? $user->name : 'John Doe' ) .'</b></li>
                            <li><b>'. ( $user->address ? $user->address : '' ) .'</b></li>
                            <li><b>'. ( $user->stateCity ? $user->stateCity : '' ) .'</b></li>
                        </ul>
                    </div>
                </div>

                <div class="row companyRow">
                    <div class="col-md-9">
                        <ul>
                        <li><b>Company Information</b></li>
                        '.( $company->name ? '<li>'.$company->name.'</li>' : '' ).'
                        '.( $company->address ? '<li>'.$company->address.'</li>' : '' ).'
                        '.( $company->stateCity ? '<li>'.$company->stateCity.'</li>' : '' ).'
                        '.( $company->phone ? '<li>'.$company->phone.'</li>' : '' ).'
                        '.( $company->ein ? '<li>EIN:'.$company->ein.'</li>' : '' ).'
                        </ul>
                    </div>
                    <div class="col-md-3 vCenter">
                        <span class="estatement"><b>Earnings Statement</b></span>
                    </div>
                </div>
    
                <div class="row employeeRow">
                    <div class="col-md-12">
                        <table class="table" id="employedetails">
                            <tr>
                                <th>Employee Information</th>
                                <th>Social Sec. ID</th>
                                <th>Employee ID</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Check Date</th>
                            </tr>
                            <tr>
                                <td>
                                    <ul class="innerLI">
                                        <li>'. ( $user->name ? $user->name : 'John Doe' ) .'</li>
                                        <li>'. ( $user->address ? $user->address : '' ) .'</li>
                                        <li>'. ( $user->stateCity ? $user->stateCity : '' ) .'</li>
                                    </ul>
                                </td>
                                <td>'. ( $user->ssn ? $user->ssn : '' ) .'</td>
                                <td>'. ( $user->id ? $user->id : '' ) .'</td>
                                <td>'.$startDate.'</td>
                                <td>'. $endDate .'</td>
                                <td>'. date('m/d/Y') .'</td>
                            </tr>
                        </table>
                    </div>
                </div>
    
                <div class="row earningRow">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-7" style="padding:0;">
                                <table class="earningTable" width="100%">
                                    <tr>
                                        <th width="25%">Earnings</th>
                                        <th width="15%">Rate</th>
                                        <th width="15%">Hours</th>
                                        <th width="15%">Current</th>
                                        <th width="25%">Year To Date</th>
                                    </tr>
                                </table>
                                <div class="row" style="margin:0;">

                                    <div class="col-md-3 firstColumn">
                                        <ul class="innerUL">
                                            <li>Regular Earnings</li>
                                            '.$additionliname.'
                                        </ul>
                                    </div>
                                    <div class="col-md-2">
                                        <ul class="innerUL">
                                            <li>'. ( strlen($check->hourly_rate) ? display_format($check->hourly_rate) : ''  ).'</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-2">
                                        <ul class="innerUL">
                                            <li>'. ( strlen($check->total_hour) ? display_format($check->total_hour) : ''  ).'</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-2">
                                        <ul class="innerUL">
                                            <li>'.display_format($check->current).'</li>
                                            '.$additionlicurrent.'
                                        </ul>
                                    </div>
                                    <div class="col-md-3">
                                        <ul class="innerUL">
                                            <li>'.display_format($check->ytd).'</li>
                                            '.$additionliytd.'
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-5" style="padding:0;">
                                <table class="deductionTable" width="100%">
                                    <tr>
                                        <th width="50%">Deductions</th>
                                        <th width="25%">Current</th>
                                        <th width="25%">YTD</th>
                                    </tr>
                                </table>
                                <div class="row" style="margin:0;">
                                    <div class="col-md-6 firstColumn" >
                                        <ul class="innerUL" style="padding-left:6px;">
                                            <li>Fedral Tax</li>
                                            <li>'.$check->stateTax->name.'</li>
                                            <li>OASDI Tax</li>
                                            <li>Health Insurance Tax</li>
                                            '.$deductionliname.'
                                        </ul>
                                    </div>
                                    <div class="col-md-3">
                                        <ul class="innerUL">
                                            <li>'.display_format($check->fedralTax->current).'</li>
                                            <li>'.display_format($check->stateTax->current).'</li>
                                            <li>'.display_format($check->oasdiTax->current).'</li>
                                            <li>'.display_format($check->healthTax->current).'</li>
                                            '.$deductionlicurrent.'
                                        </ul>
                                    </div>
                                    <div class="col-md-3">
                                        <ul class="innerUL">
                                            <li>'.display_format($check->fedralTax->ytd).'</li>
                                            <li>'.display_format($check->stateTax->ytd).'</li>
                                            <li>'.display_format($check->oasdiTax->ytd).'</li>
                                            <li>'.display_format($check->healthTax->ytd).'</li>
                                            '.$deductionliytd.'
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                        </div>    
                    </div>
                </div>
        
                <div class="row totalRow">
                    <div class="col-md-12">
                        <table width="100%">
                            <tr>
                                <th width="25%">Gross Earnings</th>
                                <th width="10%">'.display_format($check->currentTotal).'</th>
                                <th width="10%">'.display_format($check->ytdTotal).'</th>
                                <th width="15%">Gross Deductions</th>
                                <th width="7%">'.display_format($check->currentTax).'</th>
                                <th width="8%">'.display_format($check->ytdTax).'</th>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="row mt-5 justify-content-end netRow">
                    <div class="col-md-4">
                        <table width="100%">
                            <tr>
                                <th>Exemptions/Allowances</th>
                                <th style="text-align:right;">'.$user->blind.'</th>
                            </tr>
                            <tr>
                                <th>Check No.</th>
                                <th style="text-align:right;">#'. (strlen($key) ? $key : '' ).'</th>
                            </tr>
                            <tr>
                                <th>Net Pay</th>
                                <th style="text-align:right;">$'.display_format($check->cNetPay).'</th>
                            </tr>
                            <tr>
                                <th>YTD Net Pay</th>
                                <th style="text-align:right;">$'.display_format($check->yNetPay).'</th>
                            </tr>

                        </table>

                    </div>
                </div>

            </div>
        </div>
        ';

    }

    return $str;

}

function numberTowords($num){
    $ones = array(
    0 =>"ZERO",
    1 => "ONE",
    2 => "TWO",
    3 => "THREE",
    4 => "FOUR",
    5 => "FIVE",
    6 => "SIX",
    7 => "SEVEN",
    8 => "EIGHT",
    9 => "NINE",
    10 => "TEN",
    11 => "ELEVEN",
    12 => "TWELVE",
    13 => "THIRTEEN",
    14 => "FOURTEEN",
    15 => "FIFTEEN",
    16 => "SIXTEEN",
    17 => "SEVENTEEN",
    18 => "EIGHTEEN",
    19 => "NINETEEN",
    "014" => "FOURTEEN"
    );
    $tens = array( 
    0 => "ZERO",
    1 => "TEN",
    2 => "TWENTY",
    3 => "THIRTY", 
    4 => "FORTY", 
    5 => "FIFTY", 
    6 => "SIXTY", 
    7 => "SEVENTY", 
    8 => "EIGHTY", 
    9 => "NINETY" 
    ); 
    $hundreds = array( 
    "HUNDRED", 
    "THOUSAND", 
    "MILLION", 
    "BILLION", 
    "TRILLION", 
    "QUARDRILLION" 
    ); /*limit t quadrillion */

    $num = number_format($num,2,".",","); 
    $num_arr = explode(".",$num); 
    $wholenum = $num_arr[0]; 
    $decnum = $num_arr[1];
    $whole_arr = array_reverse(explode(",",$wholenum)); 
    krsort($whole_arr,1); 
    $rettxt = ""; 
    foreach($whole_arr as $key => $i){
        
    while(substr($i,0,1)=="0")
        $i=substr($i,1,5);
        if($i < 20){ 
            $rettxt .= $ones[$i]; 
        }elseif($i < 100){ 
            if(substr($i,0,1)!="0")  $rettxt .= $tens[substr($i,0,1)]; 
            if(substr($i,1,1)!="0") $rettxt .= " ".$ones[substr($i,1,1)]; 
        }else{ 
            if(substr($i,0,1)!="0") $rettxt .= $ones[substr($i,0,1)]." ".$hundreds[0]; 
            if(substr($i,1,1)!="0")$rettxt .= " ".$tens[substr($i,1,1)]; 
            if(substr($i,2,1)!="0")$rettxt .= " ".$ones[substr($i,2,1)]; 
        } 
        if($key > 0){ 
        $rettxt .= " ".$hundreds[$key]." "; 
        }
    } 
    if($decnum >= 0){

        if($wholenum > 0){
            $rettxt .= " AND ";
        }

        if($decnum == 00){
            $rettxt .= $ones[0];
        }elseif($decnum < 20){

            // $rettxt .= $ones[$decnum];
            if( substr($decnum,0,1) ==0 ){
                $rettxt .= $ones[substr($decnum,1,1)];
            }else{
                $rettxt .= $ones[$decnum];
            }

        }elseif($decnum < 100){
            $rettxt .= $tens[substr($decnum,0,1)];
            if( substr($decnum,1,1) > 0 ){
                $rettxt .= " ".$ones[substr($decnum,1,1)];
            }

        }
    }

    return $rettxt;
}

function display_format($num){

    return number_format( $num, 2 );

}

?>