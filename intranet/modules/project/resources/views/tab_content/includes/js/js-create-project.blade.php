<?php
use Rikkei\Project\Model\Project;
?>
<script>
    const TYPE_MM = {{Project::MM_TYPE}};
    const TYPE_MD = {{Project::MD_TYPE}};

    $(document).ready(function () {
        var g_oldData;

        $('#button-detail').click(function (e) {
            e.preventDefault();
            $('#tblOperationBody tbody tr').remove();
            var strStartDay = $('#start_at').val();
            var strEndDay = $('#end_at').val();
            var strHtml = '';

            if (strStartDay == '' && strEndDay == '') {
                $('.alert-message-error').removeClass('hidden');
                $('.str-error-message').text('{{trans('project::view.Start date and end date are not available')}}');
                return;
            } else {
                $('.alert-message-error').addClass('hidden');
                $('.str-error-message').text('');
                $('#taskModal').modal({backdrop: "static"});
            }

            var arrMonth = getMonthInYear(moment(strStartDay).format('YYYY-MM'), moment(strEndDay).format('YYYY-MM'));
            if (!g_oldData) {
                $('.total-app-pro-cost').text(0);
                for (var i = 0, l = arrMonth.length; i < l; i++) {
                    addRowDetail(null, i, arrMonth[i])
                    if ((i+1) % 2 == 0) {
                        $('#tblOperationBody tbody').find('tr[tabindex=' + (i+1) +']').addClass('table-css-active');
                    } else {
                        $('#tblOperationBody tbody').find('tr[tabindex=' + (i+1) +']').addClass('table-css-no-active');
                    }
                }
            } else {
                g_oldData = removerItemDetail(arrMonth, g_oldData);
                $('#tblOperationBody tbody tr').remove();
                var arrMonth2 = [];
                for (var i = 0, l = g_oldData.length; i < l; i++) {
                    var month = g_oldData[i].year + '-' + g_oldData[i].month;
                    arrMonth2.push(month);
                    addRowDetail(g_oldData[i], i, month);
                    if ((i+1) % 2 == 0) {
                        $('#tblOperationBody tbody').find('tr[tabindex=' + (i+1) +']').addClass('table-css-active');
                    } else {
                        $('#tblOperationBody tbody').find('tr[tabindex=' + (i+1) +']').addClass('table-css-no-active');
                    }
                    var parent =  $('#tblOperationBody tbody').find('tr[tabindex=' + (i+1) +']');
                    var index = Number(parent.first().attr('data-row'));
                    var rowDetail = $('#activity_month_from' + (i + 1)).closest('tr');
                    var lengthTr = Number(parent.find('tr').length) + 1;
                    parent.first().find('td:first').attr('rowspan', (g_oldData[i].detail.length + 1));
                    for (var n = 0, m = g_oldData[i].detail.length; n < m; n++) {
                        strHtml = renderRowSpan(g_oldData[i].detail[n], (i + 1), (index + 1));
                        rowDetail.after(strHtml);
                        if ((i+1) % 2 == 0) {
                            $('#tblOperationBody tbody').find('tr[tabindex=' + (i+1) +']').addClass('table-css-active');
                        } else {
                            $('#tblOperationBody tbody').find('tr[tabindex=' + (i+1) +']').addClass('table-css-no-active');
                        }
                        renderDataChildSelectTeam(g_oldData[i].detail[n].team_id, (i + 1), (index + 1));
                        index++;
                    }
                    if (lengthTr > 0) {
                        var itemNotLast = $('#tblOperationBody tbody').find('tr[tabindex=' + (i + 1) +']:not(:last)');
                        itemNotLast.find('.btn-add-row').addClass('hidden');
                    }
                }

                var arrMonth3 = Unique(arrMonth, arrMonth2);
                var tabindex = g_oldData.length;

                for (var j = 0, k = arrMonth3.length; j < k; j++) {
                    addRowDetail(null, tabindex, arrMonth3[j]);
                    if ((tabindex+1) % 2 == 0) {
                        $('#tblOperationBody tbody').find('tr[tabindex=' + (tabindex+1) +']').addClass('table-css-active');
                    } else {
                        $('#tblOperationBody tbody').find('tr[tabindex=' + (tabindex+1) +']').addClass('table-css-no-active');
                    }
                    tabindex++;
                }
            }
            var totalAppProdCost = 0;
            $('#tblOperationBody input[id*=cost_approved_production]').each(function () {
                if ($(this).val() === '') {
                    totalAppProdCost = totalAppProdCost + 0;
                } else {
                    totalAppProdCost = totalAppProdCost + Number($(this).val());
                }
            });
            $('.total-app-pro-cost').text(totalAppProdCost.toFixed(2));

            checkCostMapping();
        });

        $('body').on('click', '#close-modal', function (event) {
            $('#tblOperationBody tbody tr').remove();
        });

        // ------------------------------add row detail-------------------------------------//
        $('body').on('click', '.btn-add-row', function (event) {
            var $this = $(this);
            var strHtml = '';
            var parent = $this.closest('tr');
            var rowDetail = $this.closest('tr');
            var index = Number(parent.attr('data-row'));
            var tabindex = parent.attr('tabindex');
            var lengthTr = Number($('#tblOperationBody tbody').find('tr[tabindex=' + tabindex +']').length) + 1;
            var rowspan = $('#tblOperationBody tbody').find('tr[tabindex=' + tabindex +']').first().find('td:first').attr('rowspan');

            if (rowspan == undefined) {
                strHtml = renderRowSpan(null, tabindex, (index + 1));
                var trFirst = $('#tblOperationBody tbody').find('tr[tabindex=' + tabindex +']').first();
                trFirst.find('td:first').attr('rowspan', (index + 1));
            } else {
                strHtml = renderRowSpan(null, tabindex, Number(rowspan) + 1);
                var trFirst = $('#tblOperationBody tbody').find('tr[tabindex=' + tabindex +']').first();
                trFirst.find('td:first').attr('rowspan', (Number(rowspan) + 1));
            }
            rowDetail.after(strHtml);
            if (tabindex % 2 == 0) {
                $('#tblOperationBody tbody').find('tr[tabindex=' + tabindex +']').addClass('table-css-active');
            } else {
                $('#tblOperationBody tbody').find('tr[tabindex=' + tabindex+']').addClass('table-css-no-active');
            }
            if (lengthTr > 1) {
                var fristItem = $('#tblOperationBody tbody').find('tr[tabindex=' + tabindex +']').first();
                var itemNotLast = $('#tblOperationBody tbody').find('tr[tabindex=' + tabindex +']:not(:last)');
                itemNotLast.find('.btn-add-row').addClass('hidden');
                fristItem.find('.btn-add-row, .btn-remove-row').addClass('hidden');
            }
            if (rowspan == undefined) {
                renderDataChildSelectTeam(null, tabindex, (index + 1));
            } else {
                renderDataChildSelectTeam(null, tabindex, Number(rowspan) + 1);
            }
        });

        $(document).ready(function () {
            var urlUpdateCloseDate = '{{ route('project::project.update.close') }}'
            var statusCancel = '{{ Project::STATUS_PROJECT_CLOSE }}'
            $('#state').on('change', function() {
                var statusProj = $(this).val();
                var html = '';
                if (statusProj == statusCancel) {
                    html += '<label for="close_date" class="col-sm-4 control-label">Close date</label>\n' +
                        '<div class="col-sm-8">\n' +
                        '<input type="text" class="form-control date popClass input-basic-info" id="close_date" name="close_date" data-date-format="yyyy-mm-dd" data-provide="datepicker" >\n' +
                        '</div>'
                    $('#closed_date').html(html);
                } else {
                    $.ajax({
                        url: urlUpdateCloseDate,
                        method: "POST",
                        data: {
                            projectId: projectId,
                        },
                        success: function(data) {
                            $('#closed_date').html(html);
                        }
                    });
                }
            })
        });

        $('#cus_email').change(function () {
            var cusEmail = $(this).val();
            var cusContact = cusEmail.split('@')[0];
            var cusContactCurrent = $('#cus_contact').val();
            if (!cusContactCurrent) {
                $('#cus_contact').val(cusContact);
            }
        });

        var urlGetDayOfProject = '{{ route('project::project.get-day-project') }}';
        $('.count_day_project_work').change(function () {
            var start_date = $('#start_at').val();
            var end_date = $('#end_at').val();
            if (start_date && end_date) {
                $.ajax({
                    url: urlGetDayOfProject,
                    method: "POST",
                    data: {
                        start_date: start_date,
                        end_date: end_date,
                    },
                    success: function(data) {
                        $('#project_date').val(data);
                    }
                });
            }
        });

        //------------------------------------ Remove row detail ------------------------------//
        $('body').on('click', '.btn-remove-row ', function (event) {
            var $this = $(this);
            var rowDetail = $this.closest('tr');
            var parent = $this.closest("tr");
            var index = parent.find('tr').length;

            if (parent.find('tr').length == 1) {
                var cost = parent.find('input[id*=cost_approved_production]');
                var total = $('.total-app-pro-cost').text();
                $('.total-app-pro-cost').text((Number(total) - Number(cost.val())).toFixed(2));
                parent.remove();
            } else {
                var tabindex = parent.attr('tabindex');
                var cost = rowDetail.find('input[id*=cost_approved_production]');
                var total = $('.total-app-pro-cost').text();
                $('.total-app-pro-cost').text((Number(total) - Number(cost.val())).toFixed(2));
                rowDetail.remove();
                var trFirst = $('#tblOperationBody tbody').find('tr[tabindex=' + tabindex +']').first();
                var Rowspan = trFirst.find('td:first').attr('rowspan');
                trFirst.find('td:first').attr('rowspan', (Rowspan - 1));
                var itemLast = $('#tblOperationBody tbody').find('tr[tabindex=' + tabindex +']').last();
                itemLast.find('.btn-add-row').removeClass('hidden');
            }
            checkCostMapping();
        });

        $('body').on('click', '.btn-submit', function () {
            var data = setDataJson();
            setErrorInput();
            if ($('#tblOperationBody').find('.error-input').length == 0) {
                $("#close-modal").click();
                // $('#cost_approved_production').val(data['total']);
                $('#cost_approved_production').trigger('blur');
                delete data['total'];
                g_oldData = data;
                $('#data-project-cost').val(JSON.stringify(data));
            } else {
                // nothing
            }
        });
    });

    function setDataJson() {
        var $tbodyCtr = $('#tblOperationBody tbody tr.tblDetailInput');
        var objectParent = {};
        var data = [];
        var total = 0;
        $tbodyCtr.each(function () {
            var tabindex = $(this).attr('tabindex');
            var dataMonth = $('#activity_month_from' + tabindex).val().split('-');
            var g_arrDetailData = [];
            var totalchild = 0;

            $('#tblOperationBody tbody').find('tr[tabindex=' + tabindex +']:not(:first)').each(function (index) {
                var dataRow = $(this).attr('data-row');
                var team_id = $('#team-group' + tabindex + '_' + dataRow).val();
                var approved_production_cost = $('#cost_approved_production' + tabindex + '_' + dataRow).val();
                var price = $('#price' + tabindex + '_' + dataRow).val();
                var unit_price = $('#unit_price' + tabindex + '_' + dataRow).val();
                var approve_cost_note = $('#approve_cost_note' + tabindex + '_' + dataRow).val();
                var billable_effort_select = $('#billable_effort_select' + tabindex + '_' + dataRow).val();
                var month = dataMonth['1'];
                var year = dataMonth['0'];

                g_arrDetailData.push({
                    team_id: team_id,
                    approve_cost_note: approve_cost_note,
                    approved_production_cost: approved_production_cost,
                    price: price,
                    unit_price: unit_price,
                    billable_effort_select: billable_effort_select
                });
                totalchild = totalchild + Number(approved_production_cost);
            });

            objectParent = {
                team_id: $('#team-group-' + tabindex).val(),
                approved_production_cost: $('#cost_approved_production' + tabindex).val(),
                price: $('#price' + tabindex).val(),
                unit_price: $('#unit_price' + tabindex).val(),
                approve_cost_note: $('#approve_cost_note' + tabindex).val(),
                billable_effort_select: $('#billable_effort_select' + tabindex).val(),
                month: dataMonth['1'],
                year: dataMonth['0'],
                detail: g_arrDetailData
            };
            totalchild = totalchild + Number($('#cost_approved_production' + tabindex).val());
            total = total + totalchild;
            data.push(objectParent);
        });
        data['total'] = total;
        return data;
    }

    function removerItemDetail(arrMonth, g_oldData) {
        var arrNew = []
        for (var i = 0, l = arrMonth.length; i < l; i++) {
            for (var i = 0, l = g_oldData.length; i < l; i++) {
                var month = g_oldData[i].year + '-' + g_oldData[i].month;
                if (arrMonth[i] == month) {
                    arrNew.push(g_oldData[i]);
                }
            }
        }
        return arrNew;
    }

    function checkCostMapping() {
        if ($('#cost_approved_production').length) {
            var innerCost = 0;
            var outterCost = $('#cost_approved_production').val();
            if ($('#type_mm').val() == TYPE_MD) {
                outterCost = outterCost/20;
            }
            for (var i = 0; i < $('.approve_cost_item').length; i++) {
                innerCost+=(+$($('.approve_cost_item')[i]).val());
            }
            innerCost = (innerCost).toFixed(2);
            if (+outterCost < +innerCost) {
                $('#jsApproveMappingMsg').removeClass('hidden');
                $('#jsApproveCost').html(outterCost + ' MM');
                $('.btn-submit').attr('disabled', 'disabled').hide();
            } else {
                $('#jsApproveMappingMsg').addClass('hidden');
                $('#jsApproveCost').html('');
                $('.btn-submit').attr('disabled', false).show();
            }
        }
    }
    checkCostMapping();
    $('body').on('keyup mouseup', '#cost_approved_production, .approve_cost_item', function () {
        checkCostMapping();
    });
</script>
