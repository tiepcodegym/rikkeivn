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
    }, 'T??n n??y ???? ???????c s??? d???ng, h??y nh???p v??o t??n kh??c');
    
    $.validator.addMethod('decimal', function () {
        if(decimal()) {
            return true;
        }
    }, 'Ch??? ???????c nh???p t???i ??a 2 s??? sau d???u ph???y');
    
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
                required: 'B???t bu???c ph???i ??i???n t??n',
                maxlength: 'T??n l?? do kh??ng d??i qu?? 255 k?? t???',
                remote: 'T??n n??y ???? ???????c s??? d???ng, xin h??y nh???p l???i t??n kh??c'
            },
            sort_order: {
                number: 'S???p x???p ph???i l?? s??? nguy??n v?? l???n h??n 0',
                min: 'S???p x???p ph???i l?? s??? nguy??n v?? l???n h??n 0',
                digits: 'S???p x???p ph???i l?? s??? nguy??n v?? l???n h??n 0',
            },
            salary_rate: {
                number: 'T??? l??? h?????ng l????ng nh???p v??o ph???i l?? s???, n???m trong kho???ng 0 ?????n 100',
                min: 'T??? l??? h?????ng l????ng nh???p v??o ph???i l?? s???, n???m trong kho???ng 0 ?????n 100',
                max: 'T??? l??? h?????ng l????ng nh???p v??o ph???i l?? s???, n???m trong kho???ng 0 ?????n 100',
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