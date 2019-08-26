$(document).ready(function(){
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    });
    
    $(".datePicker").datetimepicker({
        format:'m/d/Y',
        timepicker:false,
        scrollInput:false,
        value:new Date(),
        formatDate:'m/d/Y'
    });
    
    jQuery(document).on('focus','.datePicker2',function(){	
		jQuery( this ).daterangepicker();	
	});

    $("#clearDate").click(function(){
        $(".datePicker2").val('');
        $("#infoForm").trigger('change');
    });

    $(".salaryType").change(function(){
        formValidator.resetForm();
        if( $("input[name='salary_type']:checked").val()=='hourly'  ){
            $(".filterSalary").hide();
            $(".filterHourly").show();
        }else{
            $(".filterHourly").hide();
            $(".filterSalary").show();
        }

    });

    $(".userType").change(function(){
        if( $("input[name='employee_type']:checked").val()=='employee' ){
            $(".user_name").html('Employee');
            $(".name_field").attr('placeholder', 'Employee Name' );
            $(".empID_field").attr('placeholder', 'Employee ID');
        }else{
            $(".user_name").html('Contractor');
            $(".name_field").attr('placeholder', 'Contractor Name' );
            $(".empID_field").attr('placeholder', 'Contractor ID');
        }
    });

    $("#changeTemplate").change(function(){
        $("#tempID").val( $(this).val() );
        $("#infoForm").trigger('change');
    });

    $(".chooseTemplate").click(function(){
        $("#tempID").val( $(this).data('id') );
        $("#infoForm").trigger('change');

        $("#templateModal").modal("toggle");
    });

    $("#btnPrint").on("click", function () {
        var divContents = $("#preview").html();
        var printWindow = window.open('', '', 'height=800,width=1600');
        printWindow.document.write('<html><head>');
        printWindow.document.write('</head><body >');
        printWindow.document.write(divContents);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
        printWindow.close();
        return true;

    });

    $("#previewIMG").click(function(){
        let src =$(this).attr("src");
        $("#modalIMG").attr('src',src);
    });

    $("#infoForm").change(function(e){
        e.preventDefault();
        let data = new FormData(jQuery('#infoForm')[0]);
        jQuery.ajax({
            url:'calculation.php',
            data:data,
            type:'post',
            contentType: false,
            cache: false,
            processData:false,
            asynchronous:false,            
            success:function(response){
                let res=JSON.parse(response);
                let html=res.data;
                let cStateCity=res.cstateCity;
                let uStateCity=res.ustateCity;

                if( cStateCity.trim()!='' ){
                    $("#cstateCity").val(cStateCity);
                    $('.cstateCity').show();
                }else{
                    $("#cstateCity").val('');
                    $('.cstateCity').hide();
                }
                if( uStateCity.trim()!='' ){
                    $("#ustateCity").val(uStateCity);
                    $('.ustateCity').show();
                }else{
                    $("#ustateCity").val('');
                    $('.ustateCity').hide();
                }

                $("#template").html(html);
                html2canvas([document.getElementById('template')], {
                    onrendered: function(canvas) {
                       var data = canvas.toDataURL('image/png');
                       $("#previewIMG").attr('src',data);
                    },
                });
            }

        });

    });

    $("#infoForm").trigger('change');

    let formValidator=$("#infoForm").validate({

        rules:{
            ytd_gross:{
                number:true,
            },
            salary_type:{
                required:true
            },
            hourly_rate:{
                required:{
                    depends: function(element) {
                        if( $("input[name='salary_type']:checked").val()=='hourly'  ){
                            return true;
                        }else{
                            return false;
                        }
                    }
                }
            },
            annual_salary:{
                required:{
                    depends: function(element) {
                        if( $("input[name='salary_type']:checked").val()=='salary'){
                            return true;
                        }else{
                            return false;
                        }
                    }
                }
            },
            "payDateS[]":{
                required:{
                    depends: function(element) {
                        if( $("input[name='salary_type']:checked").val()=='salary'){
                            return true;
                        }else{
                            return false;
                        }
                    }
                }

            },
            "payDateH[]":{
                required:{
                    depends: function(element) {
                        if( $("input[name='salary_type']:checked").val()=='hourly'){
                            return true;
                        }else{
                            return false;
                        }
                    }
                }

            },
            "hourspayDate[]":{
                required:{
                    depends: function(element) {
                        if( $("input[name='salary_type']:checked").val()=='hourly'){
                            return true;
                        }else{
                            return false;
                        }
                    }
                }
            },

        },
        submitHandler: function(form) {
            console.log('In Submit');
        }



    });

    $('body').on('click', '.removeAddition', function(){
        $('[data-toggle="tooltip"], .tooltip').tooltip("hide");
        $("#infoForm").trigger('change');
        $(this).parent().remove();
    });

    $('body').on('click', '.removeDeduction', function(){
        $('[data-toggle="tooltip"], .tooltip').tooltip("hide");
        $("#infoForm").trigger('change');
        $(this).parent().remove();
    });

    var additionCounter=0;
    $("#addAddition").click(function(){
        // let html=$(".addition_text").html();
        let html='	<div class="alert alert-primary"><span class="removeAddition" data-toggle="tooltip" title="Remove">X</span><div class="form-group"><input class="form-control" type="text" name="addition['+additionCounter+'][desc]" placeholder="Description" required/></div><div class="form-group row"><div class="col-md-6"><input class="form-control" type="number" name="addition['+additionCounter+'][currentAmount]" placeholder="Current Amount" required /></div><div class="col-md-6"><input class="form-control" type="number" name="addition['+additionCounter+'][ytdAmount]" placeholder="YTD Amount" required /></div></div></div>';
        $("#additionArea").append(html);
        $('[data-toggle="tooltip"]').tooltip();
        additionCounter++;
    });

    var deductionCounter=0;
    $("#addDeduction").click(function(){
        let html='	<div class="alert alert-primary"><span class="removeDeduction" data-toggle="tooltip" title="Remove">X</span><div class="form-group"><input class="form-control" type="text" name="deduction['+deductionCounter+'][desc]" placeholder="Description" required/></div><div class="form-group row"><div class="col-md-6"><input class="form-control" type="number" name="deduction['+deductionCounter+'][currentAmount]" placeholder="Current Amount" required /></div><div class="col-md-6"><input class="form-control" type="number" name="deduction['+deductionCounter+'][ytdAmount]" placeholder="YTD Amount" required /></div></div></div>';
        $("#deductionArea").append(html);
        $('[data-toggle="tooltip"]').tooltip();
        deductionCounter++;
    });

    //Script for input formatter

	$("#ssn").on( 'keyup', function(value){
		let invalue=$(this).val();		
		let outvalue=formatValue(invalue);
		$(this).val(outvalue);
	});

	$("#ssn").on( 'keypress', function(e){
		let invalue=$(this).val();
		invalue=invalue.replace(/-/g, "");
		if(invalue.length>8){
			e.preventDefault();
		}
	});

	function formatValue(value){
		value=value.replace(/-/g, "");
		let output=value;

		if(value.length > 5){
			output = value.slice(0,3)+'-'+value.slice(3,5)+'-'+value.slice(5,9) ;
		}else if(value.length > 2){
			output = value.slice(0,3)+'-'+value.slice(3,5);
		}

		return output;

	}

	$("#cphone").on( 'keyup', function(value){
		let invalue=$(this).val();		
		let outvalue=formatNumber(invalue);
		$(this).val(outvalue);
	});

	$("#cphone").on( 'keypress', function(e){
		let invalue=$(this).val();
		invalue=invalue.replace(/-/g, "");
		invalue=invalue.replace(/[(,)]/g, "");

		if(invalue.length > 9){
			e.preventDefault();
		}
	});

	function formatNumber(value){

		value=value.replace(/-/g, "");
		value=value.replace(/[(,)]/g, "");

		let output=value;

		if(value.length > 6){
			output = '('+value.slice(0,3)+')'+value.slice(3,6)+'-'+value.slice(6,10) ;
		}else if(value.length > 3){
			output = '('+value.slice(0,3)+')'+value.slice(3,6);
		}

		return output;

	}




});