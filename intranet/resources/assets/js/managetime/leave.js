jQuery(document).ready(function ($) {
    $('.managetime-select-2').select2({
        minimumResultsForSearch: Infinity
    });

    $('#btn_add_reason').click(function() {
        $('#name').removeAttr('edit');
        $('#modal-create-edit').modal('show');
        $("#used_leave_day").select2('val', '0');
        $('#close_form').one('click',function(e) {
            $('#modal-create-edit').modal('hide');
            $('#name').val('');
            $('#sort_order').val('');
            $('#salary_rate').val('');
            $('#name-error').text('');
            $('#sort_order-error').remove();
            $('#salary_rate-error').remove();
        });
    });

    $(document).on("click","tr.reason-data .reason-edit", function(e){
        var data = $(this).closest('tr');
        $('#name').attr('edit', data.attr('reason-id'));
        $('#name').val(data.attr('reason-name'));
        $('#sort_order').val(data.attr('reason-order'));
        $('#salary_rate').val(data.attr('reason-salary'));
        $('#reason_id').val(data.attr('reason-id'));
        $("#used_leave_day").select2('val', data.attr('reason-used-leave-day'));
        $('.modal-title').text(titleEditReson);
        $('#add_submit').text(labelSave);
        $('#sort_order-error').remove();
        $('#salary_rate-error').remove();
        $('#form-submit-reason').find('input[type=text]').removeClass('error');
        $('#modal-create-edit').modal('show');

        $('#close_form').one('click',function(e) {
            $('#modal-create-edit').modal('hide');
            $('#form-submit-reason').find('input[type=text]').val("");
            $('#form-submit-reason').find('input[type=text]').removeClass('error');
            $('#reason_id').val("");
            $('#add_submit').text(labelSave);
            $('.modal-title').text(titleAddReason);
            $('#name-error').text('');
            $('#sort_order-error').remove();
            $('#salary_rate-error').remove();
        });
    });

    $.validator.addMethod('uniqueName', function (value, element, param) {
        if(uniqueName()) {
            return true;
        }
    }, 'Tên này đã được sử dụng, hãy nhập vào tên khác');
    
    $.validator.addMethod('decimal', function () {
        if(decimal()) {
            return true;
        }
    }, 'Chỉ được nhập tối đa 2 số sau dấu phẩy');
    
    $('#form-submit-reason').validate({
        onkeyup: false,
        rules: {
            name: {
                required: true,
                maxlength: 255,
                remote: {
                    url: $('#name').attr("check-name"),
                    type: "get",
                    data: {
                        name: function() {
                            return $('#name').val();
                        },
                        edit: function() {
                            return $('#name').attr("edit");
                        },
                    },
                },
            },
            sort_order: {
                number:true,
                min: 0,
                digits: true
            },
            salary_rate: {
                number: true,
                decimal: true,
                min: 0,
                max: 100
            }
        },
        messages: {
            name: {
                required: 'Bắt buộc phải điền tên',
                maxlength: 'Tên lý do không dài quá 255 ký tự',
                remote: 'Tên này đã được sử dụng, xin hãy nhập lại tên khác'
            },
            sort_order: {
                number: 'Sắp xếp phải là số nguyên và lớn hơn 0',
                min: 'Sắp xếp phải là số nguyên và lớn hơn 0',
                digits: 'Sắp xếp phải là số nguyên và lớn hơn 0',
            },
            salary_rate: {
                number: 'Tỷ lệ hưởng lương nhập vào phải là số, nằm trong khoảng 0 đến 100',
                min: 'Tỷ lệ hưởng lương nhập vào phải là số, nằm trong khoảng 0 đến 100',
                max: 'Tỷ lệ hưởng lương nhập vào phải là số, nằm trong khoảng 0 đến 100',
            }
        }
    });
    
    function decimal() {
        var val = $('#salary_rate').val();
        if (val == parseInt(val)||val.length == 0) {
            return true;
        }
        var numberAfter = val.split(".")[1];
        if (numberAfter.length > 2) {
            return false;
        }
        return true;

    }
});