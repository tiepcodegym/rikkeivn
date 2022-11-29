$(document).ready(function () {
    const HOME_MESSAGE_GROUP_PRIORITY = 1;
    const HOME_MESSAGE_GROUP_BIRTHDAY = 2;
    const HOME_MESSAGE_GROUP_DEFINED_DATE = 4;

    $('.icon-old ul li').click(function () {
        $('input#icon_url').val('');
        var icon_path_chosen = $(this).find('img').attr('src');
        $('.box-upload-file #icon_url_old').val(icon_path_chosen);
        $('.box-upload-file img').attr('src', icon_path_chosen);
        $('.icon-old ul li').removeClass('active');
        $(this).addClass('active');
    });

    $('#icon_url').change(function () {
        $('#icon_url_old').val('');
        $('.box-upload-file img').attr('src', '');
        $('.icon-old ul li').removeClass('active');
        readURL(this);
    });

    window.onWeekDayAllHandleChange = function (el) {
        var box = $(el).closest('.day-box');
        var checkbox = box.find('.day-list input');
        if (el.checked) {
            for (var i = 0; i < checkbox.length; i++) {
                checkbox[i].checked = true;
            }
            return;
        }
        for (var i = 0; i < checkbox.length; i++) {
            checkbox[i].checked = false;
        }
    };

    window.onWeekDayHandleChange = function (el) {
        var box = $(el).closest('.day-box');
        var checkbox = box.find('.day-list input');
        var checkboxAll = box.find('#week_days_all');
        var checkedCheckbox = box.find('.day-list input:checked');
        if (checkedCheckbox.length === checkbox.length) {
            checkboxAll[0].checked = true;
            return;
        }
        checkboxAll[0].checked = false;
    };

    window.onGroupHandleChange = function (el) {
        var value = el.value;
        var daysOfWeek = $('#daysOfWeek');
        var pickOneDay = $('#pickOneDay');
        var dateApply = $('#txt_date_apply');
        var dateApplyRequired = $('#txt_date_apply_required');
        if (value == HOME_MESSAGE_GROUP_DEFINED_DATE) {
            daysOfWeek.show();
            pickOneDay.hide();
            dateApplyRequired.hide();
            dateApply.removeAttr('required');
            return;
        }
        if (value == HOME_MESSAGE_GROUP_PRIORITY) {
            dateApplyRequired.show();
            dateApply.attr('required', 'required');
            daysOfWeek.hide();
            pickOneDay.show();
            return;
        }
        if (value == HOME_MESSAGE_GROUP_BIRTHDAY) {
            pickOneDay.hide();
            dateApply.val('');
            return;
        }
        daysOfWeek.hide();
        pickOneDay.show();
        dateApplyRequired.hide();
        dateApply.removeAttr('required');
    };

    $('#datepicker_start_at,#datepicker_end_at').datetimepicker(
        {
            allowInputToggle: true,
            format: 'LT',
            sideBySide: true,
        }
    );

    window.readURL = function (input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('.box-upload-file img').attr('src', e.target.result);
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            $('.box-upload-file img').attr('src', "");
        }
    };
});