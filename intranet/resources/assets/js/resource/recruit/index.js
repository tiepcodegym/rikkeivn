(function ($) {
    
    $('body').on('click', '.input-edit', function () {
        $(this).find('.value').addClass('hidden');
        var input = $(this).find('input');
        input.removeClass('hidden');
        setTimeout(function () {
            input.trigger('focus');
        }, 10);
    });
    
    $('body').on('blur', '.input-edit input', function () {
        var input_val = $(this).val();
        input_val = input_val ? parseInt(input_val) : null;
        var old_val = $(this).attr('data-old');
        old_val = old_val ? parseInt(old_val) : null;
        var min_val = parseInt($(this).attr('min'));
        if (input_val && input_val < min_val) {
            input_val = min_val;
        }
       
        var value = $(this).parent().find('.value');
        value.text(input_val).removeClass('hidden');
        $(this).val(input_val).addClass('hidden');
        if (old_val != input_val) {
            $('#is_change_edit').val(1);
            $('.submit_plan').addClass('hidden');
            $('.submit_plan.btn-delete').removeClass('hidden');
        }
        $(this).attr('data-old', input_val);
        sumPlanMonth();
    });
    
    $('body').on('keydown', '.input-edit input', function (e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode == 9) {
            var inputEdit = $(this).parent();
            var nextInputEdit = inputEdit.next('.input-edit');
            if (inputEdit.is(':last-child')) {
                var tr = inputEdit.parent().next('tr');
                nextInputEdit = tr.find('.input-edit:first');
            }
            nextInputEdit.click();
        }
    });
    
    $('body').on('change', '#rc_year, #recruiter', function () {
        if ($(this).val()) {
            var form = $(this).closest('form');
            form.submit();
        }
    });
    
    if ($('#plan_table').length > 0) {
        sumPlanMonth();
    }
    
    $('.submit_plan').click(function () {
        $('.submit_plan').attr('data-click', 1);
    });
    
    $(window).resize(function () {
        if ($('.nopadding-col .table').length > 0) {
            var maxHeight = 0;
            $('.nopadding-col .table').each(function () {
                var thHeight = $(this).find('thead').height();
                if (thHeight > maxHeight) {
                    maxHeight = thHeight;
                }
            });
            $('.nopadding-col .table').find('thead tr').height(maxHeight);
        }
    }).resize();
    
    if ($('#recruit_char').length > 0) {
        var labelsMonth = [];
        for (var i = 1; i <= 12; i++) {
            labelsMonth.push('T' + i);
        }
        var ctx = document.getElementById("recruit_char");
        var datasets = [{
            label: rcParams.actualTitle,
            backgroundColor: 'rgb(51, 102, 204)',
            data: rcParams.actualsMonth,
        }];
        if (!isTrainee) {
            datasets.push({
                label: rcParams.planTitle,
                backgroundColor: 'rgb(220, 57, 18)',
                data: rcParams.plansMonth,
            });
        }
        var myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labelsMonth,
                datasets: datasets,
            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero:true
                        }
                    }],
                    xAxes: [{
                        scaleLabel: {
                            display: true,
                            labelString: rcParams.monthTitle
                        }
                    }]
                }
            }
        });
    }
    
    //caculate sum plan for each month
    function sumPlanMonth() {
        for (var i = 1; i <= 12; i++) {
            var sumMonth = 0;
            $('.input-edit[data-month="'+ i +'"]').each(function () {
                var value = $(this).find('.value');
                value = value.text() ? parseInt(value.text()) : 0;
                sumMonth += value;
            });
            $('.sum-month-' + i).text(sumMonth);
        }
    }
    
    selectSearchReload();

    /*
     * export statistics detail
     */
    $('#btn_export_stats_detail').click(function (e) {
        e.preventDefault();
        var btn = $(this);
        var url = btn.data('url');
        if (!url) {
            return false;
        }

        btn.prop('disabled', true);
        setTimeout(function () {
            btn.prop('disabled', false);
        }, 3000);
        var form = document.createElement('form');
        form.setAttribute('method', 'post');
        form.setAttribute('action', url);
        var params = {
            _token: siteConfigGlobal.token
        };
        for (var key in params) {
            var hiddenField = document.createElement('input');
            hiddenField.setAttribute('type', 'hidden');
            hiddenField.setAttribute('name', key);
            hiddenField.setAttribute('value', params[key]);
            form.appendChild(hiddenField);
        }
        document.body.appendChild(form);
        form.submit();
        form.remove();
    });

})(jQuery);

jQuery(document).ready(function ($) {
    $('#recruiter').select2();
}); 

// hover on employee account status
function statusHover(selector) {
    $(document).on({
        mouseenter : function() {
            var thisObject = $(this);
            if(thisObject.find("i").length > 0 || thisObject.find("select").length > 0) {
                return true;
            }
            thisObject.append("<i class='fa fa-pencil-square-o pull-right' aria-hidden='true'></i>");
        },
        mouseleave: function() {
            var thisObject = $(this);
            thisObject.find("i").remove();
        }
    }, selector);
}
