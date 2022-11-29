(function ($) {

    if ($('#form_auth').length > 0) {
        $('#form_auth input[name="password"]').focus();
    }

    $('#cat_box').change(function () {
        $('#test_box').append('<option>loading...</option>');
        $.ajax({
            url: _get_test_url,
            type: 'GET',
            data: {
                'cat_ids[]': [$(this).val()]
            },
            success: function (data) {
                var html = '<option value="">' + text_selection + '</option>';
                if (data.length > 0) {
                    for (var i = 0; i < data.length; i++) {
                        var test = data[i];
                        html += '<option value="' + test.id + '">' + test.name + ' (' + test.time + '\')</option>';
                    }
                }
                $('#test_box').html(html);
            }
        });
    });

    $('#select_form').submit(function () {
        $('#gmat_error').addClass('hidden');
        $('#test_error').addClass('hidden');
        var gmat_id = $('#gmat_box').val();
        var test_id = $('#test_box').val();
        if (!gmat_id || gmat_id == 0) {
            $('#gmat_error').removeClass('hidden');
        } else if (!test_id || test_id == 0) {
            $('#test_error').removeClass('hidden');
        } else {
            window.open(_home_url + '/test_old/show/' + gmat_id + '/?q_id=' + test_id);
            window.open(_home_url + '/test_old/show/' + test_id + '/?q_id=' + gmat_id);
            return false;
        }
        return false;
    });

    if ($('.btn-start').length > 0) {
        var checkStartTime;

        clearInterval(checkStartTime);
        checkStartTime = setInterval(function () {
            if (t_checkStart()) {
                $('.btn-start').click();
                t_delStart();
                clearInterval(checkStartTime);
            }
        }, 1000);

        var timeCountDown;
        var el_minute = $('.test-time .minute');
        var el_second = $('.test-time .second');
        var minute = parseInt(el_minute.text());
        var second = parseInt(el_second.text());
        var or_time = t_getItem('originTime');
        console.log(or_time);
        var st_time = t_getItem('startTime');
        console.log(st_time);
        var cr_time = new Date().getTime();
        var sb_time = cr_time - st_time;
        if (sb_time <= or_time) {
            var rs_time = or_time - sb_time;
            var minute = Math.floor(rs_time / (1000 * 60));
            var second = Math.floor(rs_time / 1000) % 60;
            el_minute.text(twoDigit(minute));
            el_second.text(twoDigit(second));
            $('.btn-start').text(text_testing + '...').removeClass('btn-primary').addClass('btn-warning').prop('disabled', true);
            timeCountDown = setInterval(countDown, 1000);
            clearInterval(checkStartTime);
        } else {
            t_removeItem('originTime');
            t_removeItem('startTime');
        }
        $('body').on('click', '.btn-start', function () {
            var origin_time = (minute * 60 + second) * 1000;
            t_setItem('originTime', origin_time);
            t_setItem('startTime', new Date().getTime());
            t_setStart();

            clearInterval(checkStartTime);
            clearInterval(timeCountDown);
            timeCountDown = setInterval(countDown, 1000);

            $(this).text(text_testing + '...').removeClass('btn-primary').addClass('btn-warning').prop('disabled', true);
        });

    }

    function countDown() {
        if (second == 0) {
            if (minute == 0) {
                clearInterval(timeCountDown);
                $('.btn-start').removeClass('btn-primary btn-flickr').addClass('btn-danger').text(text_finish);
                alert(text_finish);
//                window.location.href = '/test/finish';
            } else {
                second = 59;
                minute--;
                el_minute.text(twoDigit(minute));
            }
        } else {
            second--;
        }
        if (minute <= 5) {
            $('.btn-start').addClass('btn-flickr');
        }
        el_second.text(twoDigit(second));
    }

    selectSearchReload();

    window.history.forward();
    window.onload = function ()
    {
        window.history.forward();
    };
    window.onunload = function () {
        null;
    };

})(jQuery);

function twoDigit(value) {
    return (value < 10) ? '0' + value : value;
}

function t_setItem(key, val) {
    if (typeof Storage != "undefined") {
        sessionStorage.setItem(key, val);
    }
}

function t_getItem(key) {
    if (typeof Storage != "undefined") {
        var val = sessionStorage[key];
        return (typeof val != "undefined") ? val : null;
    }
    return null;
}

function t_removeItem(key) {
    if (typeof Storage != "undefined") {
        sessionStorage.removeItem(key);
    }
}

function t_setStart() {
    if (typeof Storage != "undefined") {
        localStorage.setItem('hasStarted', true);
    } 
}

function t_checkStart(){
    if (typeof Storage != "undefined") {
        var started = localStorage.getItem('hasStarted');
        return (typeof started != "undefined") ? started : false;
    }
    return false;
}

function t_delStart() {
    if (typeof Storage != "undefined") {
        localStorage.removeItem('hasStarted');
    }
}

function selectSearchReload(option) {
    optionDefault = {
        showSearch: false
    };
    option = jQuery.extend(optionDefault, option);
    if (option.showSearch) {
        jQuery(".select-search").select2();
    } else {
        jQuery(".select-search.has-search").select2();
        jQuery(".select-search:not(.has-search)").select2({
            minimumResultsForSearch: Infinity
        });
    }

    jQuery('.select-search').each(function (i, k) {
        text = jQuery(this).find('option:selected').text().trim();
        jQuery(this).siblings('.select2-container').find('.select2-selection__rendered').text(text);
    });
    jQuery('.select-search').on('select2:select', function (evt) {
        text = jQuery(this).find('option:selected').text().trim();
        jQuery(this).siblings('.select2-container').find('.select2-selection__rendered').text(text);
    });
}


