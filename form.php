
<?php
require_once('db.inc.php');
$query=sql_Query('select * from states order by name ASC');

$stateList='';
while($row=sql_fetch_array($query)){
	$stateList.='<option value="'.$row['id'].'">'.$row['name'].'</option>';
}

?>

<form id="infoForm" action="calculation.php" method="post" enctype="multipart/form-data">
	<input type="hidden" name="tempID" value="2" id="tempID"/>
	<details open>
		<summary class="blockHeader">1. State Of Residency</summary>
		<div class="form-group">
			<select class="form-control" name="state" >
				<?php echo $stateList; ?>
			</select>
		</div>
	</details>
	<details open>
		<summary class="blockHeader">2. <span class="user_name">Employee</span> Information</summary>
		<div class="form-group">
			<label><input class="userType" type="radio" name="employee_type" value="employee" checked />Employee</label>
			<label><input class="userType" type="radio" name="employee_type" value="contractor" />Contractor</label>
		</div>
		<div class="form-group">
			<input type="text" name="name" class="form-control name_field" placeholder="Employee Name" />
		</div>
		<div class="form-group">
			<input id="ssn" type="text" class="form-control" name="ssn" placeholder="xxx-xx-xxxx" />
		</div>
		<div class="form-group">
			<input type="text" name="emp_id" class="form-control empID_field" placeholder="Employee ID" />
		</div>
		<div class="form-group">
			<input type="text" name="emp_address" class="form-control" placeholder="Address" />
		</div>
		<div class="form-group">
			<input type="number" minlength="5" maxlength="5" name="emp_zip" class="form-control" placeholder="ZIP Code" />
		</div>
		<div class="form-group ustateCity">
			<input type="text" class="form-control" val='' id="ustateCity" readonly data-toggle="tooltip" title="City, State and Zipcode"/>
		</div>

		<div class="form-group">
			<select class="form-control" name="marital_status" data-toggle="tooltip" title="The employee’s marital status">
				<option value="1">Marital Status</option>
				<option value="1">Single</option>
				<option value="2">Married</option>
				<option value="3">Head of household</option>
			</select>
		</div>
		<div class="form-group">
			<select name="employee_dependants" class="form-control" data-toggle="tooltip" title="Choose the number of dependants">
				<option value="0">No of Dependants</option>
				<option value="0">0</option>
				<option value="1">1</option>
				<option value="2">2</option>
				<option value="3">3</option>
				<option value="4">4</option>
				<option value="5">5</option>
				<option value="6">6</option>
				<option value="7">7</option>
				<option value="8">8</option>
				<option value="9">9</option>
				<option value="10">9+</option>
			</select>
		</div>

		<div class="form-group">
			<select class="form-control" name="employee_blind">
				<option value="0">Age 65 and Over/Blind Exemptions</option>
				<option value="0">0</option><option value="1">1</option>
				<option value="2">2</option>
				<option value="3">3</option>
				<option value="4">4</option>
				<option value="5">5</option>
			</select>
		</div>

	</details>
	<details open>
		<summary class="blockHeader">3. Company Information</summary>
		<div class="form-group">
			<input type="text" name="company_name" placeholder="Company Name" class="form-control" data-toggle="tooltip" title="Company Name"/>
		</div>
		<div class="form-group">
			<input type="file" name="company_logo" placeholder="Company Logo" class="form-control" accept="image/*" data-toggle="tooltip" title="Company Logo"/>
		</div>
		<div class="form-group">
			<input type="text" name="company_address" placeholder="Company Address" class="form-control" data-toggle="tooltip" title="Company Address"/>
		</div>
		<div class="form-group">
			<input id="cphone" type="text" name="company_phone" placeholder="Company Phone" class="form-control" data-toggle="tooltip" title="Company Phone"/>
		</div>
		<div class="form-group">
			<input type="text" name="company_ein" placeholder="Company EIN/SSN" class="form-control" data-toggle="tooltip" title="Company EIN/SSN"/>
		</div>
		<div class="form-group">
			<input type="number" minlength="5" maxlength="5" name="company_zip" placeholder="ZIP Code" class="form-control" data-toggle="tooltip" title="Company ZIP Code"/>
		</div>
		<div class="form-group cstateCity">
			<input type="text" class="form-control" val='' id="cstateCity" readonly data-toggle="tooltip" title="City, State and Zipcode"/>
		</div>

	</details>
	<details open>
		<summary class="blockHeader">4. Salary Information</summary>
		<div class="form-group">
			<label><input type="radio" class="salaryType" name="salary_type" value="salary" checked />Salary</label>
			<label><input type="radio" class="salaryType" name="salary_type" value="hourly" />Hourly</label>    
		</div>
		<div class="form-group">
			<select name="pay_frequency" class="form-control" data-toggle="tooltip" title="Pay Frequency">
				<option value="once">Once</option>
				<option value="daily">Daily</option>
				<option value="weekly">Weekly (Ex: Every Friday)</option>
				<option value="biweekly" selected="">Bi-Weekly (ex: every other Wednesday)</option>
				<option value="semimonthly">Semi-Monthly (ex: 1st and 16th)</option>
				<option value="monthly">Monthly (ex: 1st of month only)</option>
				<option value="quaterly">Quarterly</option>
				<option value="semiannualy">Semi-Annually</option>
				<option value="annually">Annually</option>
			</select>
        </div>
		<div class="form-group">
			<input type="text" data-toggle="tooltip" title="Mannual YTD Gross" name="ytd_gross" placeholder="Mannual YTD Gross Entry" class="form-control"/>
		</div>
		<div class="input-group mb-1">
			<input type="text" name="startDate" placeholder="Mannual Pay Period" class="form-control datePicker2" readonly/>
			<div class="input-group-append">
				<span data-toggle="tooltip" title="Clear" class="input-group-text" id="clearDate">X</span>
			</div>
		</div>

        <div class="filterSalary">
            <div class="form-group">
                <input type="text" data-toggle="tooltip" title="Annual Salary" name="annual_salary" placeholder="Annual Salary* ex: 40000.00" class="form-control"/>
            </div>
            <div class="form-group">
                <input type="text" data-toggle="tooltip" title="Pay Date" name="payDateS[]" class="form-control datePicker" placeholder="Pay Date" readonly="readonly" />
            </div>
        </div>
        <div class="filterHourly" style="display:none;">
            <div class="form-group">
                <input data-toggle="tooltip" title="Hourly Rate" type="text" value="25" name="hourly_rate" placeholder="Hourly Rate* ex: 10.00" class="form-control" />
            </div>
            <div class="form-group row">
                <div class="col-md-6">
                    <input type="text" data-toggle="tooltip" title="Pay Date" name="payDateH[]" class="form-control datePicker" placeholder="Pay Date" readonly="readonly" />
                </div>
                <div class="col-md-6">
                    <input data-toggle="tooltip" title="Total Worked Hours" type="text" value="80" placeholder="Total Hours" name="hourspayDate[]" class="form-control" />
                </div>
            </div>
        </div>

		<h5>Additions</h5>

		<div id="additionArea"></div>

		<span id="addAddition" data-toggle="tooltip" title="Add Additions like bonus overtime ..">Add Addition</span>

		<h5>Deductions</h5>

		<div id="deductionArea"></div>

		<span id="addDeduction" data-toggle="tooltip" title="Add Deductions like taxes ..">Add Deduction</span>



	</details>
	<details open>
		<summary class="blockHeader">6. Check Numbers</summary>
		<div class="form-group">
			<input id="checkNumber" value="123" type="text" class="form-control" name="check_numbers[]" placeholder="Check Numbers" />
		</div>
	</details>
	<details open>
		<summary class="blockHeader">7. Your Email</summary>
		<div class="form-group">
			<input type="email" class="form-control" placeholder="Your Email Address" />
		</div>
	</details>
	<div class="form-group">
		<input type="submit" class="form-control btn-success" id="submit" value="Submit Information" />
	</div>
</form>

<div class="addition_text" style="display:none;">
	<div class="alert alert-primary">
		<span class="removeAddition" data-toggle="tooltip" title="Remove">X</span>
		<div class="form-group">
			<input class="form-control" type="text" name="addition[0][desc]" placeholder="Description" required/>
		</div>
		<div class="form-group row">
			<div class="col-md-6">
				<input class="form-control" type="text" name="addition[0][currentAmount]" placeholder="Current Amount" required />
			</div>
			<div class="col-md-6">
				<input class="form-control" type="text" name="addition[0][ytdAmount]" placeholder="YTD Amount" required />
			</div>
		</div>
	</div>
</div>

<div class="deduction_text" style="display:none;">
	<div class="alert alert-warning">
		<span class="removeDeduction" data-toggle="tooltip" title="Remove">X</span>
		<div class="form-group">
			<input class="form-control" type="text" name="deduction[0][desc]" placeholder="Description" required/>
		</div>
		<div class="form-group row">
			<div class="col-md-6">
				<input class="form-control" type="text" name="deduction[0][currentAmount]" placeholder="Current Amount" required />
			</div>
			<div class="col-md-6">
				<input class="form-control" type="text" name="deduction[0][ytdAmount]" placeholder="YTD Amount" required />
			</div>
		</div>
	</div>
</div>
