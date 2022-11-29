$(function (){
    var pgurl = window.location.href.substr(window.location.href);

    $('.managetime-menu li a').each(function() 
    {
        if ($(this).attr('href') == pgurl) {
            $(this).addClass('active');
        }
    });

    $('.managetime-select-2').select2({
        minimumResultsForSearch: Infinity
    });

    selectSearchReload();

    $(".managetime-read-more").shorten({
        "showChars" : 200,
        "moreText"  : "See more",
        "lessText"  : "Less",
    });

    $('.filter-date').datepicker({
        autoclose: true,
        format: 'dd-mm-yyyy',
        weekStart: 1,
        todayHighlight: true
    });

    $('.filter-date').on('keyup', function(e) {
        e.stopPropagation();
        if (e.keyCode == 13) {
            $('.btn-search-filter').trigger('click');
        }
    });
});

function checkInputKeyup()
{
    var checkSubmit = $('#check_submit').val();
    if (checkSubmit == 1) {
        var locationDegister = $('#location').val().trim();

        if (locationDegister == '') {
            $('#location-error').show();
        } else {
            $('#location-error').hide();
        }
    }
}

function checkInputReasonKeyup()
{
    var checkSubmit = $('#check_submit').val();
    if (checkSubmit == 1) {
        var reasonRegister = $('#reason').val().trim();

        if (reasonRegister == '') {
            $('#reason-error').show();
        } else {
            $('#reason-error').hide();
        }
    }
}

select2Interviewers('.select2');

function childCheck() {
    if ($('.check_emp:checked').length > 0) {
        $('.btn-remove-emp').prop('disabled', false);
    } else {
        $('.btn-remove-emp').prop('disabled', true);
    }
}

$('.btn-remove-emp').on('click', function() {
    $('#modal_remove_emp .modal-body .emp-remove').remove();
    $('#modal_remove_emp .modal-body .emp-list').html('');
    if ($('.check_emp:checked').length > 0) {
        $('.check_emp:checked').each(function() {
            $('#modal_remove_emp .modal-body').prepend('<input type="hidden" name="employee[]" class="emp-remove" value="'+$(this).data('emp_id')+'" />');
            $('#modal_remove_emp .modal-body .emp-list').append('<li class="emp-remove">'+$(this).data('emp_name') + ' ('+$(this).data('emp_code')+')' +'</li>');
        });
    }
    $('#modal_remove_emp').modal('show');
});

$('.btn-show-modal-add-emp').on('click', function() {
    $('#modal_add_emp').modal('show');
});

function select2Interviewers(dom) {
    $(dom).select2({
        ajax: {
            url: $(dom).data('remote-url'),
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function (data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;

                return {
                    results: data.items,
                    pagination: {
                        more: (params.page * 5) < data.total_count
                    }
                };
            },
            cache: true
          },
          placeholder: 'Chọn nhân viên',
          escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
          minimumInputLength: 3,
          templateResult: formatRepo,
          templateSelection: formatRepoSelection,
          maximumSelectionSize: 5,
    });
}

function formatRepo (repo) {
  if (repo.loading) {
    return repo.text;
  }

  var markup = "<div class='select2-result-repository clearfix'>" +
    "<div class='select2-result-repository__avatar' style='float: left;'><img style='border-radius: 50px;width: 50px; height: 50px;' src='" + repo.avatar + "' /></div>" +
    "<div class='select2-result-repository__meta select2_text' style='float: left; padding: 17px 10px 17px 10px; font-size: 13px;'>" +
      "<div class='select2-result-repository__title'>" + repo.text + "</div>" +
    "</div>" + 
    "</div>";

  
  return markup;
}

function formatRepoSelection (repo) {
  return repo.text;
}

$('select[name="employee[]"]').change(function() {
    $('#modal_add_emp .modal-body .error').remove();
    var empSelected = $(this).val();
    if (!empSelected) {
        $('.btn-add-emp').prop('disabled', true);
    } else {
        var arrExist = [];
        $('.btn-add-emp').prop('disabled', false);
        $.each(empSelected, function(k, value) {
            if (jQuery.inArray(parseInt(value), empIdInList) !== -1) {
                var empName = $('select[name="employee[]"]').find('option[value='+value+']').text();
                arrExist.push('<div class="error">' + empName + messageExist + '</div>');
            }
        });

        if (arrExist.length > 0) {
            $.each(arrExist, function(k, value) {
                $('#modal_add_emp .modal-body').append(value);
            });
            $('.btn-add-emp').prop('disabled', true);
        }
    }
});

/**
 * Check date is weekend without compensation days
 *
 * @param {Date} date
 *
 * @returns {Boolean}
 */
function isWeekend(date) {
    return (jQuery.inArray(date.getDay(), [0, 6]) !== -1 // is weekend
        && jQuery.inArray(getFormattedDate(date), compensationDays['com']) === -1) // but not compensation
        || jQuery.inArray(getFormattedDate(date), compensationDays['lea']) !== -1; // or is leave compensation
}

/**
 * get date string format yyyy-mm-dd
 *
 * @param {Date} date
 * @returns {String}
 */
function getFormattedDate(date) {
    return date.getFullYear() + "-" + getFormattedPartTime(date.getMonth() + 1) + "-" + getFormattedPartTime(date.getDate());
}

/**
 * Format num < 10
 *
 * @param {int} partTime
 *
 * @returns {String}
 */
function getFormattedPartTime(partTime){
    if (partTime < 10) {
        return "0" + partTime;
    }
    return partTime;
}

(function ($) {
var RKMTime = {};
RKMTime.countryProvince = {
    init: function () {
        if (!$('[data-cp-country]').length ||
            typeof country !== 'object'
        ) {
            return true;
        }
        var that = this;
        $('[data-cp-country]').each (function () {
            that.renderCountry($(this));
        });
        $('[data-cp-country]').change(function () {
            that.renderProvince($(this));
        });
    },
    /**
     * render html country dom
     *
     * @param {type} domCountry
     * @returns {undefined}
     */
    renderCountry: function (domCountry) {
        var that = this,
            type = domCountry.data('cp-country'),
            countryValue = typeof countryActive === 'object' &&
                typeof countryActive[type] !== 'undefined' ? countryActive[type] : null,
            selectHtml = '';
        if (typeof domCountry.data('select2') === 'object') {
            domCountry.data('select2').destroy();
        }
        selectHtml += '<option value="">&nbsp;</option>';
        $.each(country, function (id, name) {
            var active = '';
            if (id == countryValue) {
                active = ' selected';
            }
            if (typeof name === 'object' && name.hasOwnProperty(1)) {
                selectHtml += '<option code="'+name[1]+'" value="'+id+'"'+active+'>'+name[0]+'</option>';
            } else {
                selectHtml += '<option value="'+id+'"'+active+'>'+name+'</option>';
            } 
        });
        domCountry.html(selectHtml);
        domCountry.select2();
        that.renderProvince(domCountry);
    },
    /**
     * render province dom
     *
     * @param {type} domCountry
     * @returns {Boolean}
     */
    renderProvince: function (domCountry) {
        var that = this,
            valueCountryCur = domCountry.val(),
            type = domCountry.data('cp-country'),
            domProv = $('[data-cp-province="'+type+'"]'),
            selectHtml = '';
        if (!domProv.length) {
            return true;
        }
        if (!valueCountryCur ||
            !type ||
            typeof provinces !== 'object' ||
            typeof provinces[valueCountryCur] !== 'object'
        ) {
            that.hideDomProv(domProv, type);
            return true;
        }
        if (typeof domProv.data('select2') === 'object') {
            domProv.data('select2').destroy();
        }
        var provinceValue = typeof provinceActive === 'object' &&
            typeof provinceActive[type] !== 'undefined' ? provinceActive[type] : null;
        selectHtml += '<option value="">&nbsp;</option>';
        $.each(provinces[valueCountryCur], function (id, name) {
            var active = '';
            if (id == provinceValue) {
                active = ' selected';
            }
            selectHtml += '<option value="'+id+'"'+active+'>'+name+'</option>';
        });
        domProv.html(selectHtml);
        $('[data-cp-province-wrapper="'+type+'"]').removeClass('hidden');
        domProv.select2();
    },
    /**
     * hidden province dom
     *
     * @param {type} domProv
     * @returns {undefined}
     */
    hideDomProv: function (domProv, type) {
        $('[data-cp-province-wrapper="'+type+'"]').addClass('hidden');
        if (typeof domProv.data('select2') === 'object') {
            domProv.data('select2').destroy();
        }
        domProv.val('').change();
        domProv.html('');
    }
};
RKMTime.countryProvince.init();
})(jQuery);
