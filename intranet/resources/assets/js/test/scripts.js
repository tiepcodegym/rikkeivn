(function ($) {
	$(document).on('change', '#test', function(event) {
		var test = $('#test').val();
		if (test !== 0) {
			$.ajax({
				url: urlOption,
	            type: 'post',
	            data: {
	                _token: token,
	                test: test,
	            },
	            dataType: 'json',
	            success: function(dataRes) {
					checkDivision = [];
					checkEmployee = [];
	            	if (!dataRes.error) {
	            		if (dataRes.multi_times === 0) {
	            			$("#multi_times").prop('checked', false);
                			$("#multi_times").removeAttr('checked');
	            		} else {
	            			$("#multi_times").prop('checked', true);
	            		}
		                $('#time_from').val(dataRes.time_from);
		                $('#time_to').val(dataRes.time_to);
		                checkDivision = dataRes.checkDivision;
		                $("#division").val(checkDivision);
		                $("#division").multiselect("refresh");
		                $('.select-division').html(dataRes.division);
		                checkEmployee = dataRes.checkEmployee;
		                searchEmployee();
		                $('.select-employee').html(dataRes.employee);
		            } else {
		            	$("#multi_times").prop('checked', false);
                		$("#multi_times").removeAttr('checked');
		            	$('#time_from').val('');
		                $('#time_to').val('');
						$("#division").val('');
		                $("#division").multiselect("refresh");
		                $('.select-division').html('');
		                searchEmployee();
		                $('.select-employee').html('');
					}
	            },
			});
		}
	});
	$('#time_from').datetimepicker({
		format: 'YYYY-MM-DD HH:mm:ss',
		minDate: new Date,
	});
    $('#time_to').datetimepicker({
    	format: 'YYYY-MM-DD HH:mm:ss',
    	minDate: new Date,
    });
    $('#time_from').on('dp.change', function(e) {
    	var endTime = $(this).parent().parent().find('#time_to');
    	$(this).parent().find('label').remove();
    	endTime.parent().find('label').remove();
    	if (endTime.val() !== "") {
    		if ($(this).val() > endTime.val()) {
    			$(this).after('<label id="name-error" class="error" for="name">' + timeFromValidate + '</label>');
    		}
    	}
    });
    $('#time_to').on('dp.change', function(e) {
    	var startTime = $(this).parent().parent().find('#time_from');
    	$(this).parent().find('label').remove();
    	startTime.parent().find('label').remove();
    	if ($(this).val() < startTime.val()) {
    		$(this).after('<label id="name-error" class="error" for="name">' + timeToValidate + '</label>');
    	}
    });
    $(document).on('change', '#division', function(event) {
		var division = $('#division').val();
		$.ajax({
			url: urlOption,
            type: 'post',
            data: {
                _token: token,
                division: division,
            },
            dataType: 'json',
            success: function(res) {
                $('.select-division').html(res.division);
            },
		});
	});
	$("#input-name").keyup(function(event) {
	    if (event.keyCode === 13) {
	        searchEmployee();
	    }
	});
	$("#input-email").keyup(function(event) {
	    if (event.keyCode === 13) {
	        searchEmployee();
	    }
	});
	$(document).on('click', '.btn-reset-filter-emp', function(event) {
		$("#team_id").multiselect('clearSelection');
		$('#input-name').val('');
		$('#input-email').val('');
		searchEmployee();
	});
	$(document).on('click', '.btn-search-filter-emp', function(event) {
		searchEmployee();
	});

	$(".check-all").click( function() {
		var checkArr;
		if($(this).is(':checked')) {
			$('.check-item').each( function() {
	            $(this).prop('checked', true);
	            checkArr = checkEmployeeArr($(this));
        	});
		} else {
			$('.check-item').each( function() {
	            $(this).prop('checked', false);
                $(this).removeAttr('checked');
	            checkArr = checkEmployeeArr($(this));
        	});
		}
        $.ajax({
            url: urlEmployee,
            type: 'post',
            data: {
                _token: token,
                checkArr: checkArr,
        	},
            dataType: 'json',
            success: function(e) {
                $('.select-employee').html(e.content);
            },
        });
	});
	
    $(".check-item").click( function() {
        $.ajax({
            url: urlEmployee,
            type: 'post',
            data: {
                _token: token,
                checkArr: checkEmployeeArr($(this)),
        	},
            dataType: 'json',
            success: function(check) {
                $('.select-employee').html(check.content);
                checkAll();
            },
        });
    });
    $(window).on('hashchange', function() {
        if (window.location.hash) {
            var page = window.location.hash.replace('#', '');
            if (page === Number.NaN || page <= 0) {
                return false;
            } else {
                getData(page);
            }
        }
    });
    $(document).ready( function() {
	    $(document).on('click', '.pagination a', function(event) {
	        event.preventDefault();
	        $('li').removeClass('active');
	        $(this).parent('li').addClass('active');
	        var myurl = $(this).attr('href');
	        getData(myurl);
	    });
    });
    
    $(document).on('click', '#btn-save', function(event) {
		var employee = [];
		$(".check-employee").each(function() {
			employee.push($(this).val());
		});
		var multi_times = 0;
		if ($("#multi_times").is(':checked')) {
			multi_times = 1;
		}
		dataSave = {
			_token: token,
			data: {
				test_id: $("#test").val(),
				multi_times: multi_times,
				time_from: $("#time_from").val(),
				time_to: $("#time_to").val(),
				team_id: $('#division').val(),
				employee_id: employee,
			}
		}
		$.ajax({
			url: urlSave,
		    type: 'post',
		    data: dataSave,
		    dataType: 'json',
		    success: function (data) {
                if (data.success) {
                    $('#modal-success-notification .text-default').html(data.message);
                    $('#modal-success-notification').modal('show');
                }
                if (data.error) {
                	if (data.error === 2) {
                		if (data.message) {
		                	if (!confirm(data.message)) {
		                		return;
		                	}
		                }
						dataComfirm = {
						        _token: token,
						        data: data.data,
						        ok: 'ok',
						};
				        $.ajax({
				            url: urlSave,
						    type: 'post',
						    data: dataComfirm,
						    dataType: 'json',
				            success: function (respon) {
				                if (respon.success) {
				                    $('#modal-success-notification .text-default').html(respon.message);
				                    $('#modal-success-notification').modal('show');
				                }
				            },
				        });
					} else {
	                	$('#modal-warning-notification .text-default').html(data.message);
	                	$('#modal-warning-notification').modal('show');
	                }
                }
            },
		});
	});
})(jQuery);
function checkEmployeeArr($checkBox) {
    if ($checkBox.is(':checked')) {
        checkEmployee.push($checkBox.val());
    } else {
    	for (var i = checkEmployee.length - 1; i >= 0; i--) {
    		if (checkEmployee[i] === $checkBox.val()) {
    			checkEmployee.splice(i,1);
    		}
    	}
    }
    return checkEmployee;
}
function checkAll() {
	var count = 0;
	var i = 0;
	$('.check-item').each( function() {
	    i++;
		if($(this).is(':checked')) {
			count++;
	    } else {
	    	$(".check-all").prop('checked', false);
        	$(".check-all").removeAttr('checked');
	    }
    });
    if (i === count) {
    	$(".check-all").prop('checked', true);
    }
}
function getData(myurl) {
	var team = $('#team_id').val();
	var name = $('#input-name').val();
	var email = $('#input-email').val();
    $.ajax(
    {
        url: myurl,
        type: "get",
        datatype: "html",
        data: {
        	team: team,
            name: name,
            email: email,
            checkArr: checkEmployee,
        },
    }).done(function(dataPage){
    	var startTable = dataPage.indexOf('<div class="row table-responsive">');
    	var endTable = dataPage.indexOf('<div class="end-seach-employee">');
    	var viewTable = dataPage.slice(startTable, endTable) + "<script>$('.check-item').click(function(){$.ajax({url:urlEmployee,type:'post',data:{_token: token,type:'check',checkArr:checkEmployeeArr($(this))},dataType: 'json',success:function(check){$('.select-employee').html(check.content);checkAll();},});});<" + "/script>";
        $(".search-employee").empty().html(viewTable);
    }).fail(function(jqXHR, ajaxOptions, thrownError) {

    });
}
function searchEmployee() {
	var team = $('#team_id').val();
	var name = $('#input-name').val();
	var email = $('#input-email').val();
	$.ajax({
		url: urlList,
        type: 'get',
        data: {
            _token: token,
            team: team,
            name: name,
            email: email,
            checkArr: checkEmployee,
        },
        dataType: 'json',
        success: function(dataEmp) {
        	var startTable = dataEmp.content.indexOf('<div class="row table-responsive">');
    		var endTable = dataEmp.content.indexOf('<div class="end-seach-employee">');
    		var viewTable = dataEmp.content.slice(startTable, endTable) + "<script>$('.check-item').click(function(){$.ajax({url:urlEmployee,type:'post',data:{_token: token,type:'check',checkArr:checkEmployeeArr($(this))},dataType: 'json',success:function(check){$('.select-employee').html(check.content);checkAll();},});});<" + "/script>";
            $('.search-employee').html(viewTable);
        },
	});
}
