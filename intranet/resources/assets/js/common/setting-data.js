jQuery(document).ready(function ($) {
    $('.project_sh-datetime').datetimepicker({
        useCurrent: false,
        inline: true,
        sideBySide: true,
        format: 'YYYY-MM-DD'
    });
    $('.project_sh-datetime-btn').click(function() {
        var _this = $(this);
        _this.parents('.project_sh-datetime').find('.bootstrap-datetimepicker-widget').toggleClass('hidden');
    });
    $('.project_sh-datetime .bootstrap-datetimepicker-widget').addClass('hidden');
    $(".project_sh-datetime").on("dp.change", function (e) {
        var element = $(this).closest('.form-label-left').find('textarea'),
            value = element.val();

        if (!value) {
            value = '';
        }
        var array = value.replace(/ /g,'').split(/[\n;]+/).sort();
        if (array.indexOf(e.date.format('YYYY-MM-DD')) === -1) {
            value = e.date.format('YYYY-MM-DD') + "; " + value;
        } else {
            alert(duplicatedDateMes);
        }
        value = value.trim();
        element.val(value);
    });
    $(".project-sh-textarea").on("change", function (e) {
        var element = $(this).closest('.form-label-left').find('textarea'),
            value = element.val();
        if (!value) {
            value = '';
        }
        var array = value.replace(/ /g,'').split(/[\n;]+/).sort();
        var arrayDuplicate = [];
        for (var i = 0; i < array.length - 1; i++) {
            if (array[i + 1] === array[i]) {
                arrayDuplicate.push(array[i]);
            }
        }
        if (arrayDuplicate.length > 0) {
            var unique = array.filter( onlyUnique ).join('; ');
            element.val(unique);
            alert(duplicatedDateMes);
        } else {
            value = value.trim();
        }
    });
});

select2Projects('#project-ot-18h');
select2Branch('#branch_time');
function onlyUnique(value, index, self) {
    return self.indexOf(value) === index;
}
function select2Projects(dom) {
    ajaxSelect2(dom, selectProjectMes);
}

function select2Branch(dom) {
    ajaxSelect2(dom, selectBranchMes);
}

function ajaxSelect2(dom, placeHolder) {
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
        placeholder: placeHolder,
        escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
        minimumInputLength: 3,
        maximumSelectionSize: 5,
    });
}