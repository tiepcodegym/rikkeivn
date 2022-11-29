var period = [];
var line_deleted = [];
var rate_ot_list = [],
    checkin_standard = '8:00', checkout_standard = '17:30',
    ot_normal_start = '', ot_normal_end = '',
    ot_day_off_start = '', ot_day_off_end = '',
    ot_holiday_start = '', ot_holiday_end = '',
    ot_overnight_start = '', ot_overnight_end = '';
$(document).ready(function () {
    $('.input-date').datepicker({
        autoclose: true,
        format: 'yyyy-mm-dd',
        weekStart: 1,
        todayHighlight: true
    });

    RKExternal.select2.init();

    $(document).on('click', '.toogle-item', function () {
        if ($(this).hasClass('collapsed')) {
            $(this).text('[ + ]');
        } else {
            $(this).text('[ - ]');
        }
    });

    $('.search-employee').selectSearchEmployee();

    $('#project-select').on('change', function () {
        var project_id = $(this).val();
        var url = GET_PO_URL + '?project_id=' + project_id
        $('.img-loading').show();
        $('#timesheet-body').html('');
        getAjax(url).done(handleChangeProject);
    });

    $('#period-select').on('change', function () {
        $('#timesheet-body').html('<img src="' + IMG_LOADING + '" class="data-loading" />');
        var val = $(this).val().split('->');

        if(!checkIsDate(val[0])) {
            $('#timesheet-body').html('');
            return;
        }

        $('#start_date').val(val[0]);
        $('#end_date').val(val[1]);
        var data = {
            po_id: $('#po-select').val(),
            project_id: $('#project-select').val(),
            start_date: val[0],
            end_date: val[1],
            timesheet_id: TIMESHEET_ID,
            checkin_standard: checkin_standard,
            checkout_standard: checkout_standard
        };

        $('#project_name').val($('#project-select>option:selected').text());
        var url = GET_LINE_ITEM_URL + '?' + $.param(data);
        $('.data-loading').show();
        getAjax(url).done(handleChangePeriod);
        $('.edit-time-block').show();
    });

    $('#po-select').on('change', function () {
        var po_id = $(this).val();
        handleChangePO(po_id);

        load_rateOt(rate_ot_list[po_id]);
    });

    $('#btn-edit-time').on('click', function () {
        var checkin = $('.edit-checkin').val(),
            checkout = $('.edit-checkout').val(),
            break_time = $('.edit-breaktime').val();

        var validate = validate_edit_all();

        if(validate) {
            if(checkin != ''){
                $('.input_checkin').not('.is-weekend').not('.leave-day').val(checkin);
            }

            if(checkout != ''){
                $('.input_checkout').not('.is-weekend').not('.leave-day').val(checkout);
            }
            if(break_time != ''){
                $('.input_break_time').not('.is-weekend').not('.leave-day').val(break_time);
            }

            $('.line-item').each(function (index) {
                var lineid = $(this).find('.tbl-line-item').attr('id');
                reCalTimeWorking(lineid);
                reCalTotalWorkingHour(lineid);
                reCalTotalLeaveDay(lineid);
            })

        }
    })

    $(document).on('click', '.show-note', function () {
        $('.btn-save-note').data('id', $(this).data('id'));
        $('.btn-save-note').data('date', $(this).data('date'));
        var note_val = $(this).parent().find('.note-' + $(this).data('date')).val();
        $('#txt-note').val(note_val);

        $('#note-modal').modal();
    });

    $(document).on('click', '.toogle-item', function () {
        if ($(this).hasClass('collapsed')) {
            $(this).text('[ + ]');
        } else {
            $(this).text('[ - ]');
        }
    });

    $(document).on('click', '.btn-save-note', function () {
        var line_item_id = $(this).data('id'),
            date = $(this).data('date');
        $('textarea[name="line_item[' + line_item_id + '][details][' + date + '][note]"]').val($('#txt-note').val())
    });

    $(document).on('click', '.btn-sync-timesheet', function(){
        var start_date = $('#start_date').val(),
            end_date = $('#end_date').val(),
            btn_sync = $('#sync-timesheet');

        btn_sync.data('line-id', $(this).data('line-id'));
        btn_sync.data('start-date', start_date);
        btn_sync.data('end-date', end_date);

        $('#sync-modal').modal();
    });

    $('#sync-timesheet').on('click', function() {
        var empId = $('.search-employee').val(),
            line_id = $(this).data('line-id');

        $('.' + line_id).find('.message-item').html('<img src="' + IMG_LOADING + '" />').show();
        var data = {
            employee_id: empId,
            line_item_id: line_id,
            start_date: $(this).data('start-date'),
            end_date: $(this).data('end-date'),
            checkin: checkin_standard,
            checkout: checkout_standard
        };
        var url = URL_SYNC_TIMESHEET + '?' + $.param(data);

        getAjax(url).done(handleSyncTimesheet);
    });

    $("#btn-submit").on('click', function(e){
        e.preventDefault();

        var error_float = false;
        $('.input-float').each(function(index) {
            if(!validateValueHour($(this).val())) {
                $(this).addClass('error');
                if(!error_float) {
                    error_float = true;
                }
            }
        });

        var error_time = false;
        $('.input-time').each(function(index) {
            var val = $(this).val();
            if(val != '' && !checkIsTime(val)) {
                $(this).addClass('error');
                if(!error_time) {
                    error_time = true;
                }
            }
        });

        $('.tbl-line-item').each(function () {
            var line_id = $(this).attr('id');

            var valid = validateTime(line_id);
            if(!valid) {
                $('#' + line_id).find('.valid-time').text('Có dữ liệu không hợp lệ, vui lòng kiểm tra lại');
                error_time = true;
            } else {
                $('#' + line_id).find('.valid-time').text('');
            }
        });

        if(!error_float && !error_time) {
            $('#timesheet-form').submit();
        }
    });

    $(document).on('change', '.input-float', function(){
        var val = $.trim($(this).val());
        $(this).val(val);
        if(!validateValueHour(val)) {
            $(this).addClass('error');
            $(this).focus();
            return;
        }

        $(this).val(rebuildValueFloat(val));
        $(this).removeClass('error');
    });

    $(document).on('change', '.input-time', function () {
        var val = $.trim($(this).val());
        $(this).val(val);
        var this_name = $(this).attr('name'),
            line_id = $(this).parents('div').attr('id'),
            type = this_name.match(/\[(checkin|checkout|break_time)\]/);

        var name_checkin = this_name.replace(type[1], 'checkin'),
            name_checkout = this_name.replace(type[1], 'checkout'),
            name_break = this_name.replace(type[1], 'break_time'),
            name_working = this_name.replace(type[1], 'working_hour'),
            name_ot = this_name.replace(type[1], 'ot_hour'),
            val_checkin = $('[name="'+name_checkin+'"]').val(),
            val_checkout = $('[name="'+name_checkout+'"]').val(),
            val_break = $('[name="'+name_break+'"]').val();

        var valid = validateTime(line_id);

        if(!valid) {
            $('#' + line_id).find('.valid-time').text('Có dữ liệu không hợp lệ, vui lòng kiểm tra lại');
        } else {
            $('#' + line_id).find('.valid-time').text('');
        }

        if(val_checkin != '' || val_checkout != '' || val_break != ''){
            calTimeWorking(name_working, name_ot, val_checkin, val_checkout, val_break);
        }

        reCalTotalWorkingHour(line_id);
        reCalTotalLeaveDay(line_id);
    });

    $(document).on('change', '.input-float', function () {
        var line_id = $(this).parents('div.tbl-line-item').attr('id');
        reCalTotalWorkingHour(line_id);
        reCalTotalLeaveDay(line_id);
    });

    $(document).on('click', '.edit-row', function () {
        var type = $(this).data('type'),
            row = $(this).data('row'),
            line_id = $(this).parents('div').attr('id');

        $('#data-row').val('');
        $('#data-row').data('type', type);
        $('.btn-save-row').data('row', row);
        $('.btn-save-row').data('line-id', line_id);

        $('#edit-modal').find('.edit-row-error').text('');
        $('#edit-modal').modal();
    });

    $('.btn-save-row').on('click', function () {
        var $val = $('#data-row').val(),
            $type = $('#data-row').data('type'),
            $row = $(this).data('row'),
            $line_id = $(this).data('line-id');

        if($val == '') {
            $('.edit-row-error').text('Vui lòng nhập giá trị');
            return;
        }

        if($type == 'input-float') {
            //validate hour
            if(!validateValueHour($val)) {
                $('.edit-row-error').text('Vui lòng dạng số thập phân lớn hơn 0 và nhỏ hơn 24 (h)');
                return;
            }
        }

        if($type == 'input-time') {
            // validate time
            if(!checkIsTime($val)){
                $('.edit-row-error').text('Vui lòng nhập đúng định dạng (H:m)');
                return;
            }
        }

        editRow($line_id, $row, $type, $val);

        var valid = validateTime($line_id);
        if(!valid) {
            $('#' + $line_id).find('.valid-time').text('Có dữ liệu không hợp lệ, vui lòng kiểm tra lại');
        } else {
            $('#' + $line_id).find('.valid-time').text('');
        }

        if($type == 'input-time') {
            reCalTimeWorking($line_id);
        }

        reCalTotalWorkingHour($line_id);
        reCalTotalLeaveDay($line_id);

        $('#edit-modal').modal('hide');
    });

    $(document).on('click', '.btn-remove-item', function () {
        var line = $(this).parents('div.line-item');

        if(confirm('Bạn muốn xóa thông tin timesheet của nhân viên này?')) {
            line.remove();
            if(line.data('id') != ''){
                line_deleted.push(line.data('id'));
                $('#line-deleted').val(line_deleted.join(','))
            }
        }
    })

    $('.btn-refresh-item').on('click', function () {
        refreshItem();
    })
});

function reCalTimeWorking(line_id) {
    $('#'+line_id).find('.input_checkin').each(function () {
        var this_name = $(this).attr('name'),
            type = this_name.match(/\[(checkin|checkout|break_time)\]/);

        var name_checkin = this_name.replace(type[1], 'checkin'),
            name_checkout = this_name.replace(type[1], 'checkout'),
            name_break = this_name.replace(type[1], 'break_time'),

            name_working = this_name.replace(type[1], 'working_hour'),
            name_ot = this_name.replace(type[1], 'ot_hour'),

            val_checkin = $('[name="'+name_checkin+'"]').val(),
            val_checkout = $('[name="'+name_checkout+'"]').val(),
            val_break = $('[name="'+name_break+'"]').val();

        if(val_checkin != '' && val_checkout != ''){
            calTimeWorking(name_working, name_ot, val_checkin, val_checkout, val_break);
        }
    })
}

function refreshItem() {
    $('#timesheet-body').html('<img src="' + IMG_LOADING + '" class="data-loading" />');
    var val = $('#period-select').val().split('->');

    if(!checkIsDate(val[0])) {
        $('#timesheet-body').html('');
        return;
    }

    $('#start_date').val(val[0]);
    $('#end_date').val(val[1]);
    var data = {
        po_id: $('#po-select').val(),
        project_id: $('#project-select').val(),
        start_date: val[0],
        end_date: val[1]
    };

    var url = REFRESH_LINE_ITEM_URL + '?' + $.param(data);
    $('.data-loading').show();
    getAjax(url).done(handleChangePeriod);
}

function validateTime($line_id) {
    var valid = true;
    var input_time;
    if($line_id !== undefined) {
        input_time = $('#' + $line_id).find('.input-time');
    } else {
        input_time = $('.input-time');
    }

    input_time.each(function() {
        var this_name = $(this).attr('name'),
            type = this_name.match(/\[(checkin|checkout|break_time)\]/);

        var name_checkin = this_name.replace(type[1], 'checkin'),
            name_checkout = this_name.replace(type[1], 'checkout'),
            name_break = this_name.replace(type[1], 'break_time'),
            name_working = this_name.replace(type[1], 'working_hour'),
            checkin = $('[name="'+name_checkin+'"]'),
            checkout = $('[name="'+name_checkout+'"]'),
            break_input = $('[name="'+name_break+'"]'),
            working_hour = $('[name="'+name_working+'"]');

        var $in = convertTimeToMinutes(checkin.val()),
            $out = convertTimeToMinutes(checkout.val()),
            $break = convertTimeToMinutes(break_input.val());

        checkin.removeClass('error');
        checkout.removeClass('error');
        working_hour.removeClass('error');

        if(checkin.val() == '' && checkout.val() == '' && break_input.val() == '') {
            return;
        }

        if(checkin.val() == '' && checkout.val() != '') {
            checkin.addClass('error');
            valid = false;
            return;
        }

        if(checkin.val() != '' && checkout.val() == '') {
            checkout.addClass('error');
            valid = false;
            return;
        }


        if (($out - $in - $break) < 0) {
            checkin.addClass('error');
            checkout.addClass('error');
            working_hour.addClass('error');
            valid = false;
        }
    });

    return valid;
}

function calTimeWorking($name_working, $name_ot, $in, $out, $break) {
    if ( $in == '' || $out == '') {
        $('input[name="'+ $name_working +'"]').val('');
        $('input[name="'+ $name_ot +'"]').val('');
        return;
    }

    $in = convertTimeToMinutes($in);
    $out = convertTimeToMinutes($out);
    $break = convertTimeToMinutes($break);
    var $checkin_standard = convertTimeToMinutes(checkin_standard),
        $checkout_standard = convertTimeToMinutes(checkout_standard),
        $ot = 0;

    if($checkin_standard > $in) {
        $ot += $checkin_standard - $in;
    }
    if($checkout_standard < $out) {
        $ot += $out - $checkout_standard;
    }

    var working_hour = rebuildValueFloat(($out - $in - $break - $ot)/60),
        ot_hour = rebuildValueFloat($ot/60);

    if($('input[name="'+ $name_working +'"]').hasClass('is-weekend')) {
        $('input[name="'+ $name_working +'"]').val('');
        $('[name="'+ $name_ot +'"]').val(parseFloat(working_hour) + parseFloat(ot_hour));
        return;
    }

    $('input[name="'+ $name_working +'"]').val(working_hour);
    $('input[name="'+ $name_ot +'"]').val(ot_hour);
}

function editRow($line_id, $row, $type, $val) {
    $val = rebuildValueFloat($val);
    $('#'+$line_id).find('.row-' + $row).find('.' + $type).not('.is-weekend').not('.leave-day').val($val);
}

function validateValueHour(val) {
    if(!isFloat(val)) {
        return false;
    }

    if (val > 24) {
        return false;
    }

    return true;
}

function rebuildValueFloat(val) {
    var dec = 0;
    val = String(val);
    if(val.indexOf('.') >= 0) {
        dec = val.substring(parseInt(val.indexOf('.')) + 1);

        if (parseInt(dec) > 0) {
            return parseFloat(val).toFixed(2);
        } else {
            return val.substring(0, parseInt(val.indexOf('.')));
        }
    }

    return val;
}

function handleChangeProject(data) {
    var po_group = $('.po-group'),
        po_select = $('#po-select'),
        period_group = $('.period-group');
    resetPoPeriod();
    $('.img-loading').hide();
    if (data.data.length === 0) {
        $('.po-group-no-item').show();
        po_group.hide();
        period_group.hide();
        return;
    }

    po_group.show();
    period_group.show();

    $.each(data['data'], function (index, value) {
        po_select.append('<option value="' + value.id + '">' + value.name + '</option>')
        period[value.id] = value.period;
        rate_ot_list[value.id] = value.rate_ot;
    });
}

function load_rateOt($rate)
{
    checkin_standard = '8:00';
    checkout_standard = '17:30';
    if($rate.checkin_standard != '') {
        checkin_standard = $rate.checkin_standard
    }

    if($rate.checkout_standard != '') {
        checkout_standard = $rate.checkout_standard
    }

    ot_normal_start = $rate.ot_normal_start;
    ot_normal_end = $rate.ot_normal_end;
    ot_day_off_start = $rate.ot_day_off_start;
    ot_day_off_end = $rate.ot_day_off_end;
    ot_holiday_start = $rate.ot_holiday_start;
    ot_holiday_end = $rate.ot_holiday_end;
    ot_overnight_start = $rate.ot_overnight_start;
    ot_overnight_end = $rate.ot_overnight_end;

    $('#checkin_standard').val(checkin_standard);
    $('#checkout_standard').val(checkout_standard);
    $('#ot_normal_start').val(ot_normal_start);
    $('#ot_normal_end').val(ot_normal_end);
    $('#ot_day_off_start').val(ot_day_off_start);
    $('#ot_day_off_end').val(ot_day_off_end);
    $('#ot_holiday_start').val(ot_holiday_start);
    $('#ot_holiday_end').val(ot_holiday_end);
    $('#ot_overnight_start').val(ot_overnight_start);
    $('#ot_overnight_end').val(ot_overnight_end);

    $('.checkin_standard').text(checkin_standard);
    $('.checkout_standard').text(checkout_standard);
    $('.ot_normal_start').text(ot_normal_start);
    $('.ot_normal_end').text(ot_normal_end);
    $('.ot_day_off_start').text(ot_day_off_start);
    $('.ot_day_off_end').text(ot_day_off_end);
    $('.ot_holiday_start').text(ot_holiday_start);
    $('.ot_holiday_end').text(ot_holiday_end);
    $('.ot_overnight_start').text(ot_overnight_start);
    $('.ot_overnight_end').text(ot_overnight_end);

    if($rate.ot_normal_start != '') {
        $('.rate-ot-block').show();
    }
}

function handleChangePO(po_id) {
    var po_period = period[po_id];

    $('#period-select').html('<option>--- Select ---</option>');
    $('#po_title').val($('#po-select>option:selected').text());

    //show Period
    if (po_period.length > 0) {
        $.each(po_period, function (index, value) {
            $('#period-select').append('<option value="' + value.start + '->' + value.end + '">' + value.start + ' -> ' + value.end + '</option>');
        });
    }
}

function handleChangePeriod(data) {
    if (data.status == 'error') {
        $('#note').html(data.message);
        $('.data-loading').hide();
        return;
    }

    if (data.html != '') {
        $('#note').html('<strong>Chú ý:</strong> Những dữ liệu bên dưới được set với các giá trị mặc định, bạn cần đồng bộ với timesheet thực tế của nhân viên.')
        $('#timesheet-body').html(data.html);
        $('.data-loading').hide();
        RKExternal.select2.init();
    } else {
        $('#note').html('Không có dữ liệu trong khoảng thời gian này. Vui lòng kiểm tra lại đơn hàng');
        $('.data-loading').hide();
    }

    $('.tbl-line-item').each(function () {
        reCalTotalWorkingHour($(this).attr('id'));
    })
}

function handleSyncTimesheet(data) {
    var line_id = data.line_item_id;
    $('.'+line_id).find('.message-item').html('').removeClass('error text-success');
    $('[name="line_item['+line_id+'][employee_id]"]').val($('.search-employee').val());

    if(data.status == 'error') {
        $('.'+line_id).find('.message-item').addClass('error').text(data.message);
    } else {
        if (data.html != '') {
            $('.division-' + line_id).val(data.team_id).trigger('change');
            $('#'+line_id).html(data.html);
            $('.'+line_id).find('.message-item').addClass('text-success').text('Đồng bộ timesheet thành công');
        }
    }

    $('.input-float').each(function (index) {
        $(this).val(rebuildValueFloat($(this).val()));
    });

    reCalTotalWorkingHour(line_id);
    reCalTotalLeaveDay(line_id);
}

function resetPoPeriod() {
    $('#po-select').html('<option>--- Select ---</option>');
    $('#period-select').html('<option>--- Select ---</option>');
    $('.po-group').hide();
    $('.period-group').hide();
    $('.po-group-no-item').hide();
}

function getAjax(url, data, method) {
    if (data === 'undefined') {
        data = ''
    }

    if (method === undefined) {
        method = 'GET';
    }

    return $.ajax({
        type: method,
        url: url,
        data: data
    });
}

/**
 * check string is time format 'H:i'
 * @param str
 */
function checkIsTime(str) {
    return /^\s*(2[0-3]|[01]?[0-9]):([0-5]?[0-9])\s*$/.test(str);
}

function checkIsDate(str) {
    return /^[0-9]{4}-[0-9]{2}-[0-9]{2}$/.test(str);
}

function isFloat(val) {
    var regexp = /^\s*\d*(\.\d+)?\s*$/;
    return regexp.test(val);
}

function convertTimeToMinutes(str) {
    if (!checkIsTime(str)) {
        return 0;
    }
    var arr = str.split(':');
    return arr[0] * 60 + parseFloat(arr[1]);
}

function reCalTotalWorkingHour(line_id) {
    var table = $('#' + line_id);

    var total_working = 0, total_ot = 0, total_overnight = 0;

    table.find('.input_working_hour').each(function() {
        if($(this).val() != '' ) {
            total_working += parseFloat($(this).val());
        }
    });

    table.find('.input_ot_hour').each(function() {
        if($(this).val() != '' ) {
            total_ot += parseFloat($(this).val());
        }
    });

    table.find('.input_overnight').each(function() {
        if($(this).val() != '' ) {
            total_overnight += parseFloat($(this).val());
        }
    });

    $('.total-working-' +  line_id).text(rebuildValueFloat(total_working + total_ot + total_overnight));
    $('.total-ot-' +  line_id).text(rebuildValueFloat(total_ot));
    $('.total-overnight-' +  line_id).text(rebuildValueFloat(total_overnight));
}

function reCalTotalLeaveDay(line_id) {
    var table = $('#' + line_id),
        total_working = 0,
        rel_working = 0,
        count_day = 0;

    table.find('.input_working_hour').not('.is-weekend').each(function() {
        count_day++;
        rel_working += 8;
        if($(this).val() != '' ) {
            total_working += parseFloat($(this).val());
        }
    });

    count_day = Math.max((rel_working-total_working)/8, 0);
    $('#collapse-'+line_id).find('.input_day_of_leave').val(count_day.toFixed(3));
}

function validate_edit_all() {
    var valid = true;
    $('.valid-mesage').hide();
    $('.edit-item').removeClass('error');
    $('.edit-item').each(function(index) {
        if($(this).val() != '' && !checkIsTime($(this).val())){
            valid = false;
            $(this).addClass('error');
            $('.valid-mesage').show();
        }
    });

    return valid;
}