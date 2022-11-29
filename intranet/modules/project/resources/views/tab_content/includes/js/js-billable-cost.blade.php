<script>
    var globalAutoGenrateBillable = {};
    var globalRangeMonth = getRangeMonth();

    if ($('#js-table-billable tbody tr').length) {
        $('#js-billable-detail-data').val(JSON.stringify(initJsonData()));
    }

    $('#start_at, #end_at').change(function (e) {
        globalRangeMonth = getRangeMonth()
    });

    $("#js-table-billable").on("change", 'input[name^="billable[][price]"]', function () {
        calculateGrandTotal();
    });

    /*
    *
    * Get Range Month
    **/
    function getRangeMonth() {
        var strStartDay = $('#start_at').val();
        var strEndDay = $('#end_at').val();
        if (!strStartDay) {
            strStartDay = $('#lbl_start_at').parent().find('div>p').text();
        }
        if (!strEndDay) {
            strEndDay = $('#lbl_end_at').parent().find('div>p').text();
        }
        if (strStartDay && strEndDay) {
            return getMonthInYear(moment(strStartDay).format('YYYY-MM'), moment(strEndDay).format('YYYY-MM'));
        }
        return [];
    }

    /*
    *
    * View Billable Detail
    **/
    function viewDetailWhenBillableJsonDataAlreadyInit() {
        $('#modal-billable-detail').modal({backdrop: "static"});
        if ($('#billable_effort').length === 0) {
            $('#js-table-billable .input-price').prop('readonly', true);
            $('#js-table-billable .input-price').prop('disabled', true);
        }
        setTimeout(calculateGrandTotal(), 500);
    }

    /*
    *
    * Get all Month
    **/
    function getMonthInYear(from_month, to_month) {
        if (from_month && to_month) {
            var from_month = moment(from_month);
            var to_month = moment(to_month);
            var arr_month = [];

            while (from_month <= to_month) {
                arr_month.push(from_month.format('YYYY-MM'));
                from_month.add(1, 'months');
            }
            return arr_month;
        }

        return [];
    }

    /*
    *
    * Get Billable Data detail
    **/
    function getBillableData() {
        var billableJsonData = {};
        var billableReturnData = {};
        //If billable data detail is already haved
        if ($('#js-billable-detail-data').val()) {
            billableJsonData = JSON.parse($('#js-billable-detail-data').val());
        } else {
            //else using data auto generate from approve cost
            billableJsonData = globalAutoGenrateBillable;
        }
        //Merge billable date detail with range month
        globalRangeMonth.forEach(function (month) {
            billableReturnData[month] = billableJsonData[month] ? billableJsonData[month] : null;
        });

        return billableReturnData;
    }


    $('#billable-button-detail-create, #billable-button-detail').click(function (e) {
        var strStartDay = $('#start_at').val();
        var strEndDay = $('#end_at').val();
        //Using for view only data
        if (!strStartDay) {
            strStartDay = $('#lbl_start_at').parent().find('div>p').text();
        }
        if (!strEndDay) {
            strEndDay = $('#lbl_end_at').parent().find('div>p').text();
        }
        if (!strStartDay || !strEndDay) {
            $('.alert-message-error').removeClass('hidden');
            $('.str-error-message').text('{{trans('project::view.Start date and end date are not available')}}');
            return;
        } else {
            $('.alert-message-error').addClass('hidden');
            $('.str-error-message').text('');
        }
        var data = getBillableData();

        $('#js-table-billable tbody').html('');

        var trClone;

        for (var month in data) {
            $('.js-clone .input-month').val(month);
            $('.js-clone .label-month').text(month);
            $('.js-clone .input-price').val(data[month]);
            trClone = $('.js-clone tr').clone();
            trClone.appendTo('#js-table-billable tbody');
        }
        if (typeof globalIsAllowUpdateApproveCost !== 'undefined' && !globalIsAllowUpdateApproveCost) {
            $('.btn-add').remove();
        } else {
            setErrorBillableInput();
        }
        viewDetailWhenBillableJsonDataAlreadyInit();
    });




    function calculateGrandTotal() {
        var grandTotal = 0;
        $("#js-table-billable").find('input[name^="billable[][price]"]').each(function () {
            grandTotal += +$(this).val();
        });
        grandTotal = grandTotal.toFixed(2);
        $(".js-total-billable").text(grandTotal);

        var outterCost = $('#billable_effort').val();
        if ($('#type_mm').val() == TYPE_MD) {
            outterCost = outterCost/20;
        }
        if (+outterCost < +grandTotal) {
            $('#jsBillableCostMsg').removeClass('hidden');
            $('.btn-common-submit').attr('disabled', 'disabled').hide();
        } else {
            $('#jsBillableCostMsg').addClass('hidden');
            $('.btn-common-submit').attr('disabled', false).show();
        }
    }

    function initJsonData() {
        var data = {};
        $('#js-table-billable').find('tbody tr').each(function (e) {
            data[$(this).find('.label-month').text()] = $(this).find('.input-price').val();
        });

        return data;

    }

    function setErrorBillableInput() {
        var $tbodyCtr  = $('#js-table-billable tbody');

        $tbodyCtr.each(function() {
            $(this).find('input:not(".non-required"), select').each(function () {
                if ($(this).val() == '') {
                    $('.error-input-mess').text('{{ trans('project::message.Input all') }}');
                    $('.group-error').removeClass('hidden');
                    $(this).addClass('error-input');
                } else {
                    $(this).removeClass('error-input');
                }
            });

            if ($('#js-table-billable').find('.error-input').length == 0 ) {
                $('.error-input-mess').hide();
            } else {
                $('.error-input-mess').show();
            }
        });
    }

    $('.js-btn-billable-submit').on('click', function (e) {
        var data = initJsonData();
        setErrorBillableInput();
        $('#js-billable-detail-data').val(JSON.stringify(data));
        if ($('#js-table-billable').find('.error-input').length == 0) {
            $("#billable-close-modal").click();
            sendData($('#billable_effort'));
        }
    });

    $('.js-btn-billable-save').on('click', function (e) {
        var data = initJsonData();
        setErrorBillableInput();
        if ($('#js-table-billable').find('.error-input').length == 0) {
            $("#billable-close-modal").click();
            $('#billable_effort').trigger('blur');
            $('#js-billable-detail-data').val(JSON.stringify(data));
        }
    });

    $('#taskModal').on('hidden.bs.modal', function () {
        if ($('#data-project-cost').val()) {
            globalAutoGenrateBillable = [];
            var projetcCostObject = JSON.parse($('#data-project-cost').val());
            var projectCostGroupPrice = {};
            var totalApproveCost = $('#cost_approved_production').val();
            var totalBillableEffort = $('#billable_effort').val();
            projetcCostObject.forEach(function (value) {
                var monthIndex = value.year + '-' + value.month;
                projectCostGroupPrice[monthIndex] = value.approved_production_cost;
                value.detail.forEach(function (detailValue) {
                    projectCostGroupPrice[monthIndex] = projectCostGroupPrice[monthIndex] + detailValue.approved_production_cost;
                });
                globalAutoGenrateBillable[monthIndex] = totalBillableEffort * projectCostGroupPrice[monthIndex] / totalApproveCost;
            });
        }
    })

</script>
