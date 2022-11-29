$(function() {
    $('#datetimepicker-start-date').datepicker({
        autoclose: true,
        format: 'dd-mm-yyyy',
    });

    $('#datetimepicker-end-date').datepicker({
        autoclose: true,
        format: 'dd-mm-yyyy',
    });
});

var xhr = null;

function loadData(page)
{
    if(xhr != null) {
        xhr.abort();
    }
    var startDateFilter = $('#start_date').val();
    var endDateFilter = $('#end_date').val();
    var timekeepingTableIdActive = $('#timekeeping_table_active').val();
    var $refresh = $buttonFilter.find('.fa-refresh');
    $refresh.removeClass('hidden');
    var limit = $('select[name=limit] option:selected').data('value');
    $('html, body').animate({
        scrollTop: $('body').offset().top
    }, 'slow');

    xhr = $.ajax({
        type: "GET",
        url: urlFilter,
        timeout: 30000,
        data : {
            startDateFilter: startDateFilter,
            endDateFilter: endDateFilter,
            teamId: endDateFilter,
            timekeepingTableIdActive: timekeepingTableIdActive,
            limit: limit,
            page: page
        },
        success: function (html) {
            $('#filter_data').html(html);
            $buttonLast.find('a').attr('data-page', totalPage );
            $inputPager.val(page);
            var nextPage = parseInt(page) + 1;
            var prevPage = parseInt(page) - 1;
            $buttonPrev.removeClass('disabled');
            $buttonNext.removeClass('disabled');
            $buttonLast.removeClass('disabled');
            $buttonFirst.removeClass('disabled');
            $buttonNext.find('a').attr('data-page', nextPage );
            $buttonPrev.find('a').attr('data-page', prevPage );
            if (totalPage < nextPage) {
                $buttonNext.addClass('disabled');
            } 
            if (prevPage == 0) { 
                $buttonPrev.addClass('disabled');
            } 
            if (page == 1) {
                $buttonFirst.addClass('disabled');
            }
            if (page == totalPage) {
                $buttonLast.addClass('disabled');
            }
            $('.data-pager-info span').html(pagerInfo);
            setHeightBody('.content-wrapper', 50);
        },
        complete: function () {
            $refresh.addClass('hidden');
            var timekeeingTableId = $('#timekeeping_table_active').val();
            $('.managetime-menu li a').each(function() {
                if ($(this).attr('data-timekeeping-table-id') == timekeeingTableId) {
                    $('.timekeeping-time-to-time a.active').removeClass('active');
                    $(this).addClass('active');
                }
            });
        },
    });
}

// Set min height body
function setHeightBody(elem, height)
{
    $(elem).css('min-height', $(window).height() - $('.main-footer').outerHeight() - height);
}

function setFixedHeaderTimekeeping() {
    // Set fillter to left table
    setTimeout(function () {
        var meTblHeight = $('#_me_table').height();
        if ($(window).height() < meTblHeight + 200) {
            var arrLeftWidth = [];
            $('#_me_tbl_left thead tr:eq(0) th').each(function (idx) {
                arrLeftWidth[idx] = $(this).width();
                $(this).css('width', arrLeftWidth[idx]);
                $(this).closest('table').find('tbody tr:eq(1) td:eq('+ idx +')').css('width', arrLeftWidth[idx]);
            });

            var arrTdWidth = [];
            $('#_me_table thead tr:eq(0) th').each(function (idx) {
                arrTdWidth[idx] = $(this).width();
                $(this).css('min-width', arrTdWidth[idx]);
                $(this).closest('table').find('tbody tr:eq(1) td:eq('+ idx +')').css('width', arrTdWidth[idx]);
            });

            var theadHeight = $('#_me_table thead').height();

            var topFixed = $('#top_fixed_head');
            var tblContainer = $('#me_table_container').clone().removeAttr('id');
            var tblLeft = tblContainer.find('#_me_tbl_left');
            var tblMe = tblContainer.find('#_me_table');

            tblLeft.find('tbody tr:not(:first)').remove();
            tblLeft.find('tbody tr .select2').remove();
            tblMe.find('tbody tr:not(:first)').remove();
            tblMe.find('tbody tr .select2').remove();

            tblContainer.appendTo(topFixed);
            tblContainer.find('tr select:not(.compare)').select2();
            $('#me_table_container').find('table').each(function () {
                $(this).find('tbody tr:first').remove();
            });
            $('#me_table_container').find('table').css("cssText", 'margin-top: ' + (-theadHeight) + 'px!important;');

            $('._me_table_responsive').on('scroll', function () {
                var scroll_left = $(this).scrollLeft();
                $('._me_table_responsive').scrollLeft(scroll_left);
            });
            
            var fixedOffsetTop = topFixed.offset().top;
            var fixedHeight = topFixed.height();
            
            $(window).scroll(function () {
               var scrollTop = $(window).scrollTop();
               if (scrollTop > fixedOffsetTop) {
                   $('#top_fixed_head').addClass('top-fixed').css('top', scrollTop - $('._me_review_page').offset().top - 5);
                   $('#me_table_container').css('margin-top', fixedHeight);
               } else {
                   $('#top_fixed_head').removeClass('top-fixed').css('top', 'auto');
                   $('#me_table_container').css('margin-top', 0);
               }
            });
        }
    }, 200);
}

$('.btn-edit-row').on('click', function() {
    var btn = $(this);
    var trParent = btn.closest('tr');

    //Reset border-color
    trParent.find('td .edit-field').css('border-color', '#ccc');

    trParent.find('.edit-field').removeClass('hidden');
    trParent.find('.result-field').addClass('hidden');
    trParent.find('.btn-save-row').removeClass('hidden');
    trParent.find('.btn-cancel-row').removeClass('hidden');
    btn.addClass('hidden');
    setValue(trParent, 1);
    fixHeightTr();
});

$('.btn-cancel-row').on('click', function() {
    var btn = $(this);
    var trParent = btn.closest('tr');
    trParent.find('.edit-field').addClass('hidden');
    trParent.find('.result-field').removeClass('hidden');
    trParent.find('.btn-save-row').addClass('hidden');
    trParent.find('.btn-edit-row').removeClass('hidden');
    btn.addClass('hidden');
    fixHeightTr()
});

/**
 * Save a row event
 */
$('.btn-save-row').on('click', function() {
    var btn = $(this);
    var trParent = btn.closest('tr');

    if (!validateRow(trParent)) {
        return false;
    }

    btn.prop('disabled', true);
    btn.find('i.fa-refresh').removeClass('hidden');

    var data = {};
    trParent.find('td .edit-field').each(function () {
        var field = $(this).data('field');
        data[field] = $(this).val();
    });

    $.ajax({
        type: "post",
        dataType: 'json',
        data : {
            _token: token,
            data: data,
            empId: trParent.data('emp'),
            tableId: tableId,
        },
        url: btn.data('url'),
        success: function () {
            trParent.find('.edit-field').addClass('hidden');
            trParent.find('.result-field').removeClass('hidden');
            trParent.find('.btn-save-row').addClass('hidden');
            trParent.find('.btn-cancel-row').addClass('hidden');
            trParent.find('.btn-edit-row').removeClass('hidden');
            setValue(trParent, 2);
            fixHeightTr()
        },
        complete: function() {
            btn.prop('disabled', false);
            btn.find('i.fa-refresh').addClass('hidden');
        },
    });  
});

/**
 * Validate row before save
 *
 * @param dom tr
 *
 * @return boolean if invalid return false
 */
function validateRow(tr) {
    var valid = true;
    tr.find('td .edit-field').each(function () {
        var _this = $(this);
        var value = _this.val().trim();
        if (!value || !$.isNumeric(value)) {
            _this.css('border-color', 'red');
            valid = false;
        }
    });
    return valid;
}

/**
 * Set value
 * Type = 1. click btn edit
 * Type = 2. click btn save
 *
 * @param {dom} tr tr of table
 * @param {int} type
 *
 * @return void
 */
function setValue(tr, type) {
    tr.find('td').each(function () {
        var td = $(this);
        if (type === 1) {
            td.find('.edit-field').each(function() {
                var field = $(this).data('field');
                var valueSet = td.find('.edit-field-hidden[data-field='+field+']').val();
                if (!valueSet) {
                    valueSet = 0;
                }
                var decimal = jQuery.inArray(field, ['total_number_late_in', 'total_number_early_out']) !== -1 ? 0 : 2;
                $(this).val(rounding(valueSet, decimal));
            });
        } else {
            var total = 0;
            td.find('.edit-field').each(function() {
                var field = $(this).data('field'),
                valueSet = $(this).val();
                if (!valueSet) {
                    valueSet = 0;
                }
                td.find('.edit-field-hidden[data-field='+field+']').val($(this).val());
                total += parseFloat(valueSet);
            });
            var resultField = td.find('.result-field');
            if (resultField.hasClass('is-int')) {
                resultField.text(rounding(total, 0));
            } else {
                resultField.text(rounding(total, 2));
            }
        }
    });

    //Set text total_ot_official
    var total_official_ot_weekdays = tr.find('.edit-field-hidden[data-field=total_official_ot_weekdays]').val();
    var total_official_ot_weekends = tr.find('.edit-field-hidden[data-field=total_official_ot_weekends]').val();
    var total_official_ot_holidays = tr.find('.edit-field-hidden[data-field=total_official_ot_holidays]').val();
    var totalOTOfficial = parseFloat(total_official_ot_weekdays) + parseFloat(total_official_ot_weekends) + parseFloat(total_official_ot_holidays);
    totalOTOfficial = parseFloat(totalOTOfficial) || 0;
    tr.find('span[data-field=total_ot_official]').text(rounding(totalOTOfficial, 2));

    //Set text total_ot_trial
    var total_trial_ot_weekdays = tr.find('.edit-field-hidden[data-field=total_trial_ot_weekdays]').val();
    var total_trial_ot_weekends = tr.find('.edit-field-hidden[data-field=total_trial_ot_weekends]').val();
    var total_trial_ot_holidays = tr.find('.edit-field-hidden[data-field=total_trial_ot_holidays]').val();
    var totalOTTrial = parseFloat(total_trial_ot_weekdays) + parseFloat(total_trial_ot_weekends) + parseFloat(total_trial_ot_holidays);
    totalOTTrial = parseFloat(totalOTTrial) || 0;
    tr.find('span[data-field=total_ot_trial]').text(rounding(totalOTTrial, 2));

    //var total_official_leave_day_no_salary = tr.find('.edit-field-hidden[data-field=total_leave_day_no_salary]').val();
    //Set text total_working_official_salary
    var total_official_working_days = tr.find('.edit-field-hidden[data-field=total_official_working_days]').val();
    var total_official_business_trip = tr.find('.edit-field-hidden[data-field=total_official_business_trip]').val();
    var total_official_leave_day_has_salary = tr.find('.edit-field-hidden[data-field=total_official_leave_day_has_salary]').val();
    var total_official_supplement_has_salary = tr.find('.edit-field-hidden[data-field=total_official_supplement]').val();
    var total_official_holiay = tr.find('.edit-field-hidden[data-field=total_official_holiay]').val();
    var num_com_off = tr.find('.edit-field-hidden[data-field=number_com_off]').val();
    var totalWorkingOfficialToSalary = parseFloat(total_official_working_days) 
            + parseFloat(total_official_business_trip)
            + parseFloat(total_official_leave_day_has_salary)
            + parseFloat(total_official_supplement_has_salary)
            + parseFloat(total_official_holiay)
            //- (total_official_leave_day_no_salary ? total_official_leave_day_no_salary : 0)
            - num_com_off;
    totalWorkingOfficialToSalary = parseFloat(totalWorkingOfficialToSalary) || 0;
    tr.find('span[data-field=total_working_official_salary]').text(rounding(totalWorkingOfficialToSalary, 2));

    //Set text total_working_trial_salary
    var total_trial_working_days = tr.find('.edit-field-hidden[data-field=total_trial_working_days]').val();
    var total_trial_business_trip = tr.find('.edit-field-hidden[data-field=total_trial_business_trip]').val();
    var total_trial_leave_day_has_salary = tr.find('.edit-field-hidden[data-field=total_trial_leave_day_has_salary]').val();
    var total_trial_supplement_has_salary = tr.find('.edit-field-hidden[data-field=total_trial_supplement]').val();
    var total_trial_holiay = tr.find('.edit-field-hidden[data-field=total_trial_holiay]').val();
    total_trial_business_trip = isNaN(total_trial_business_trip) ? 0 : total_trial_business_trip;
    total_trial_leave_day_has_salary = isNaN(total_trial_leave_day_has_salary) ? 0 : total_trial_leave_day_has_salary;
    total_trial_supplement_has_salary = isNaN(total_trial_supplement_has_salary) ? 0 : total_trial_supplement_has_salary;
    total_trial_holiay = isNaN(total_trial_holiay) ? 0 : total_trial_holiay;
    var num_com_tri = tr.find('.edit-field-hidden[data-field=number_com_tri]').val();
    var totalWorkingTrialToSalary = parseFloat(total_trial_working_days)
            + parseFloat(total_trial_business_trip)
            + parseFloat(total_trial_leave_day_has_salary)
            + parseFloat(total_trial_supplement_has_salary)
            + parseFloat(total_trial_holiay)
            - num_com_tri;
    totalWorkingTrialToSalary = parseFloat(totalWorkingTrialToSalary) || 0;
    tr.find('span[data-field=total_working_trial_salary]').text(rounding(totalWorkingTrialToSalary, 2));
}

$('input.edit-field').focus( function() {
    $(this).parent().find('.note').removeClass('hidden');
    $(this).parent().find('.arrow-left').removeClass('hidden');
});

$('input.edit-field').blur( function() {
    $(this).parent().find('.note').addClass('hidden');
    $(this).parent().find('.arrow-left').addClass('hidden');
});

/**
 * Focus in input filter
 */
$(document).on('click', '.text-label', function() {
    var td = $(this).closest('td');
    td.find('.arrow-up').removeClass('hidden');
    td.find('.text-label').addClass('hidden');
    td.find('.filter-grid').removeClass('hidden');
    td.find('.filter-group').addClass('filter-focus');
    td.find('input.filter-grid').focus();
});

/**
 * Focus out input filter
 */
$(document).mouseup(function(e) {
    var container = $(".filter-focus");

    // if the target of the click isn't the container nor a descendant of the container
    if (!container.is(e.target) && container.has(e.target).length === 0) {
        var td = container.closest('td');
        var newValue = td.find('input.filter-grid').val();
        var newCompare = td.find('select.filter-grid').val();
        if (newValue) {
            td.find('.text-label').val(newCompare + ' ' + newValue);
        } else {
            td.find('.text-label').val('');
        }

        td.find('.arrow-up').addClass('hidden');
        td.find('.text-label').removeClass('hidden');
        td.find('.filter-grid').addClass('hidden');
        td.find('.filter-group').removeClass('filter-focus');
    }
});

/**
 * focus input text filter after change operator select
 */
$(document).on('change', 'select.filter-grid', function() {
    $(this).parent().find('input.filter-grid').focus();
});
