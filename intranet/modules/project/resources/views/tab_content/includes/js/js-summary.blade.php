<?php
use Rikkei\Project\Model\Project;
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script>
$(document).ready(function() {
    var urlEditBasicInfo = '<?php echo e(route('project::project.edit_basic_info')); ?>';
    var g_oldData;
    var g_allIds = [];
    var parent1;
    var rowDetail2;
    const TYPE_MM = {{Project::MM_TYPE}};
    const TYPE_MD = {{Project::MD_TYPE}};

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
    function data() {
        var properties = {
            "data": [
                    @foreach( $projectAppProductCost as $value)
                    @php
                        $note = (str_replace(array("\r\n", "\n\r", "\r", "\n"), "&#13;&#10;", (htmlspecialchars($value['note']))));
                    @endphp
                {
                    "id": {!! $value['id'] !!},
                    'project_id': {!! $value['project_id'] !!},
                    'approved_production_cost': {!! $value['approved_production_cost'] !!},
                    'month_year': {!! $value['year'] !!} +'-' + '{{ $value['month'] }}',
                    'team_id': {!! $value['team_id'] !!},
                    'price': '{!! $value['price'] !!}',
                    'unapproved_price': '{!! $value['unapproved_price'] !!}',
                    'unit_price': '{!! $value['unit_price'] !!}',
                    'approve_cost_note' : '{!!  $note  !!}',
                    'role': '{!! $value['role'] !!}',
                    'level': '{!! $value['level'] !!}',
                    'detail': [
                            @foreach( $value['detail'] as $vl )
                            @php
                                $note = (str_replace(array("\r\n", "\n\r", "\r", "\n"), "&#13;&#10;", (htmlspecialchars($vl['note']))));

                            @endphp
                        {
                            'id': {!! $vl['id'] !!},
                            'approved_production_cost': {!! $vl['approved_production_cost'] !!},
                            'price': '{!! $vl['price'] !!}',
                            'unapproved_price': '{!! $vl['unapproved_price'] !!}',
                            'unit_price': '{!! $vl['unit_price'] !!}',
                            'team_id':  {!! $vl['team_id'] !!},
                            'approve_cost_note' : '{!! $note !!}',
                            'role': '{!! $vl['role'] !!}',
                            'level': '{!! $vl['level'] !!}',
                        },
                        @endforeach
                    ]
                },
                @endforeach
            ]
        };
        return properties;
    }

    var properties = data();

    $('#button-detail').click(function (e) {
        $('body').find('.submit-successful').hide();
        e.preventDefault();
        var strStartDay = $('#start_at').val();
        var strEndDay = $('#end_at').val();
        if (!strStartDay) {
            strStartDay = $('#lbl_start_at').parent().find('div>p').text();
        }
        if (!strEndDay) {
            strEndDay = $('#lbl_end_at').parent().find('div>p').text();
        }
        if (strStartDay == '' && strEndDay == '') {
            $('.alert-message-error').removeClass('hidden');

            return;
        } else {
            $('.alert-message-error').addClass('hidden');
            $('#taskModal').modal({backdrop: "static"});
        }

        var arrMonth = getMonthInYear(moment(strStartDay).format('YYYY-MM'), moment(strEndDay).format('YYYY-MM'));
        $('#tblOperationBody tbody tr').remove();
        if (!g_oldData) {
            var unique = transFormerData(properties.data, arrMonth);
            renderShowDetail(unique, arrMonth);
        } else {
            var unique = transFormerData(g_oldData, arrMonth);
            renderShowDetail(unique, arrMonth);
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
        if (typeof globalIsAllowUpdateApproveCost !== 'undefined' && !globalIsAllowUpdateApproveCost && !hasPermissionViewCostDetail) {
            $('.btn-add').remove();
        }
        checkCostMapping();
    });

    // $('body').on('change', '#end_at, #start_at', function () {
    //    var dataOld = $(this).attr('data-old-value');
    //    var data = moment($(this).val()).format('YYYY-MM');
    //    if (data > dataOld) {
    //        $('.open-modal-wo-submit').attr('disabled',true);
    //        if ($(this).hasClass('start-date')) {
    //            $('#start_at-error-ap_cost').css('display','block');
    //        } else {
    //            $('#end_at-error-ap_cost').css('display','block');
    //        }
    //    } else if (data == dataOld) {
    //        $('.open-modal-wo-submit').attr('disabled',false);
    //        if ($(this).hasClass('start-date')) {
    //            $('#start_at-error-ap_cost').css('display','none');
    //        } else {
    //            $('#end_at-error-ap_cost').css('display','none');
    //        }
    //    } else {
    //        $('.open-modal-wo-submit').attr('disabled',true);
    //        if ($(this).hasClass('start-date')) {
    //            $('#start_at-error-ap_cost').css('display','block');
    //        } else {
    //            $('#end_at-error-ap_cost').css('display','block');
    //        }
    //    }
    // });

    // ------------------------------add row detail-------------------------------------//
    $('body').on('click', '.btn-add-row', function (event) {
        var $this = $(this);
        var strHtml = '';
        var parent = $this.closest("tr");
        var rowDetail = $this.closest('tr');
        var index = Number(parent.attr('data-row'));
        var tabindex = parent.attr('tabindex');
        var lengthTr = Number($('#tblOperationBody tbody').find('tr[tabindex=' + tabindex +']').length) + 1;
        var rowspan = $('#tblOperationBody tbody').find('tr[tabindex=' + tabindex +']').first().find('td:first').attr('rowspan');

        var arr_month = parent.find(".js-arr-month").val();
        if (rowspan == undefined) {
            strHtml = renderRowSpan(null, tabindex, (index + 1), arr_month, true);
            var trFirst = $('#tblOperationBody tbody').find('tr[tabindex=' + tabindex +']').first();
            trFirst.find('td:first').attr('rowspan', (index + 1));
        } else {
            var trFirst = $('#tblOperationBody tbody').find('tr[tabindex=' + tabindex +']').first();
            strHtml = renderRowSpan(null, tabindex, Number(rowspan) + 1, arr_month, true);
            trFirst.find('td:first').attr('rowspan', (Number(rowspan) + 1));
        }
        rowDetail.after(strHtml);
        if (tabindex % 2 == 0) {
            $('#tblOperationBody tbody').find('tr[tabindex=' + tabindex +']').addClass('table-css-active');
        } else {
            $('#tblOperationBody tbody').find('tr[tabindex=' + tabindex +']').addClass('table-css-no-active');
        }
        if (lengthTr > 1) {
            var fristItem = $('#tblOperationBody tbody').find('tr[tabindex=' + tabindex +']').first();
            var itemNotLast = $('#tblOperationBody tbody').find('tr[tabindex=' + tabindex +']:not(:last)');
            itemNotLast.find('.btn-add-row').addClass('hidden');
            fristItem.find('.btn-add-row, .btn-remove-row').addClass('hidden');
        }
        if (rowspan == undefined) {
            renderDataChildSelectTeam(null, tabindex, (index + 1), showByTeam);
            renderDataChildSelectTypeMember(null, tabindex, (index + 1));
            renderDataChildSelectLevel(null, tabindex, (index + 1));
        } else {
            renderDataChildSelectTeam(null, tabindex, Number(rowspan) + 1, showByTeam);
            renderDataChildSelectTypeMember(null, tabindex, Number(rowspan) + 1);
            renderDataChildSelectLevel(null, tabindex, Number(rowspan) + 1);
        }
    });

    //------------------------------------ Remove row detail ------------------------------//
    $('body').on('click', '.btn-remove-row ', function (event) {
        var $this = $(this);
        rowDetail2 = $this.closest('tr');
        parent1 = $this.closest('tr');
        $this.addClass('delete-confirm');
    });

    //------------------------------------ click button delete ------------------------------//
    $('body').on('click', '.btn-ok', function (event) {
        var index = parent1.attr('tabindex');
        var deleteOperationProductionCostUrl = '{{ route('project::operation.delete_operation-production-cost') }}';

        if (rowDetail2.find('td input[type="hidden"]').val()) {
            var cost = rowDetail2.find('input[id*=cost_approved_production]');
            var total = $('.total-app-pro-cost').text();
            $('.total-app-pro-cost').text((Number(total) - Number(cost.val())).toFixed(2));
            rowDetail2.remove();
            var trFirst = $('#tblOperationBody tbody').find('tr[tabindex=' + index +']').first();
            var Rowspan = trFirst.find('td:first').attr('rowspan');
            trFirst.find('td:first').attr('rowspan', (Rowspan - 1));
            var itemLast = $('#tblOperationBody tbody').find('tr[tabindex=' + index +']').last();
            itemLast.find('.btn-add-row').removeClass('hidden');
            checkCostMapping();
        } else {
            $.ajax({
                url: deleteOperationProductionCostUrl,
                type: 'get',
                dataType: 'json',
                data: {
                    id: rowDetail2.find('td input[type="hidden"]').val()
                },
                success: function (data) {
                    if (data.status) {
                        if ($('#tblOperationBody tbody').find('tr[tabindex=' + index +']').length == 1) {
                            var cost = parent1.find('input[id*=cost_approved_production]');
                            var total = $('.total-app-pro-cost').text();
                            $('.total-app-pro-cost').text((Number(total) - Number(cost.val()).toFixed(2)));
                            parent1.remove();
                        } else {
                            var cost = rowDetail2.find('input[id*=cost_approved_production]');
                            var total = $('.total-app-pro-cost').text();
                            $('.total-app-pro-cost').text((Number(total) - Number(cost.val())).toFixed(2));
                            rowDetail2.remove();
                            var trFirst = $('#tblOperationBody tbody').find('tr[tabindex=' + index +']').first();
                            var Rowspan = trFirst.find('td:first').attr('rowspan');
                            trFirst.find('td:first').attr('rowspan', (Rowspan - 1));
                            var itemLast = $('#tblOperationBody tbody').find('tr[tabindex=' + index +']').last();
                            itemLast.find('.btn-add-row').removeClass('hidden');
                        }
                        checkCostMapping();
                    }
                },
                error: function (x, t, m) {
                    if (t === 'timeout') {
                        $('#modal-warning-notification .modal-body p.text-default').text(errorTimeoutText);
                    } else {
                        $('#modal-warning-notification .modal-body p.text-default').text(errorText);
                    }
                }
            });
        }
        checkCostMapping();
    });

    //------------------------------------ button save in modal ------------------------------//
    $('body').on('click', '.btn-submit', function () {
        $('#jsErrorCostApprovedAroduction').html('');
        $('body').find('.submit-successful').hide();
        let type_submit = $(this).data('submit');
        if (type_submit && type_submit == 'is_coo') {
            let $domIsApprove = $('body').find('input[name=is_approve]');
            if ($domIsApprove.length > 0) {
                if ($('input[name="is_approve"]:checked').length < 1) {
                    alert('Tick vào checkbox cần duyệt!');
                    return;
                }
            }
        }
        //validate price
        var checkFalse = false;
        $iptPrices = $('body').find('.js-input-price');
        $iptPrices.each(function( i, v ) {
            var valPrice = $(v).val();
            var reg = /^[0-9,]+$/;
            if (valPrice && !reg.test(valPrice)) {
                checkFalse = true;
            }
        });
        if (checkFalse) {
            bootbox.alert({
                message: 'Đơn giá nhập vào không hợp lệ!',
                className: 'modal-danger',
            });
            return;
        }

        var data = setDataJson();
        $('body').find('.submit-successful').hide();

        setErrorInput();
        if ($('#tblOperationBody').find('.error-input').length == 0) {
            // $("#close-modal").click();
            // $('#cost_approved_production').val(data['total']);
            delete data['total'];
            g_oldData = data;
            $('#data-project-cost').val(JSON.stringify(data));
            sendData($('#cost_approved_production'), type_submit);
            $('.open-modal-wo-submit').attr('disabled', false);
            $('#end_at-error-ap_cost').css('display', 'none');
            $('#start_at-error-ap_cost').css('display', 'none');
        }
    });

    //------------------------------------ render html when click button view detail -----------------//
    function renderShowDetail(p_objectdata, arrMonth) {
        let checkData = false;
        if (p_objectdata.length > 0) {
            checkData = true;
        }
        for (var i = 0; i < arrMonth.length; i++) {
            var month = arrMonth[i];
            var dataTransfer = null;
            for (var j = 0; j < p_objectdata.length; j++) {
                if (p_objectdata[j].month_year == arrMonth[i]) {
                    dataTransfer = p_objectdata[j];
                }
            }
            addRowDetail(dataTransfer, i, month, checkData);
            if ((i+1) % 2 == 0) {
                $('#tblOperationBody tbody').find('tr[tabindex=' + (i+1) +']').addClass('table-css-active');
            } else {
                $('#tblOperationBody tbody').find('tr[tabindex=' + (i+1) +']').addClass('table-css-no-active');
            }
            for (var j = 0; j < p_objectdata.length; j++) {
                if (p_objectdata[j].month_year == arrMonth[i]) {
                    var parent = $('#activity_month_from' + (i + 1)).closest('tr');
                    var index = Number(parent.attr('data-row'));
                    var rowDetail = $('#activity_month_from' + (i + 1)).closest('tr');
                    var lengthTr = p_objectdata[j].detail.length + 1;
                    parent.find('td:first').attr('rowspan', (p_objectdata[j].detail.length + 1));
                    if (p_objectdata[j].detail.length > 0) {
                        for (var n = 0; n < p_objectdata[j].detail.length; n++) {
                            strHtml = renderRowSpan(p_objectdata[j].detail[n], i + 1, (index + 1), month);
                            rowDetail.after(strHtml);
                            if (lengthTr > 1) {
                                var fristItem = $('#tblOperationBody tbody').find('tr[tabindex=' + (i + 1) +']').first();
                                var itemNotLast = $('#tblOperationBody tbody').find('tr[tabindex=' + (i + 1) +']:not(:last)');
                                itemNotLast.find('.btn-add-row').addClass('hidden');
                                fristItem.find('.btn-add-row, .btn-remove-row').addClass('hidden');
                            }
                            renderDataChildSelectTeam(p_objectdata[j].detail[n].team_id, i + 1, (index + 1), showByTeam);
                            if ((i+1) % 2 == 0) {
                                $('#tblOperationBody tbody').find('tr[tabindex=' + (i+1) +']').addClass('table-css-active');
                            } else {
                                $('#tblOperationBody tbody').find('tr[tabindex=' + (i+1) +']').addClass('table-css-no-active');
                            }

                            if (level_id = p_objectdata[j].detail[n].level ? p_objectdata[j].detail[n].level : p_objectdata[j].detail[n].level_id) {
                                renderDataChildSelectLevel(level_id, i + 1, (index + 1));
                            } else {
                                renderDataChildSelectLevel(null, i + 1, (index + 1));
                            }
                            if (role_id = p_objectdata[j].detail[n].role ? p_objectdata[j].detail[n].role : p_objectdata[j].detail[n].role_id) {
                                renderDataChildSelectTypeMember(role_id, i + 1, (index + 1));
                            } else {
                                renderDataChildSelectTypeMember(null, i + 1, (index + 1));
                            }
                            index++;
                        }
                    }
                }
            }
        }
    }

    function transFormerData(array1, array2) {
        var unique = [];
        for (var i = 0; i < array1.length; i++) {
            var found = false;

            for (var j = 0; j < array2.length; j++) { // j < is missed;
                if (array1[i].month_year == array2[j]) {
                    found = true;
                    break;
                }
            }
            if (found == true) {
                unique.push(array1[i]);
            }
        }

        return unique;
    }

    //---------------------- set data json -----------------//
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
                var price_main = $('#price_main' + tabindex + '_' + dataRow).data("pricemain");
                var unit_price = $('#unit_price' + tabindex + '_' + dataRow).val();
                var approve_cost_note = $('#approve_cost_note' + tabindex + '_' + dataRow).val();
                var billable_effort_select = $('#billable_effort_select' + tabindex + '_' + dataRow).val();
                var id = $('#id' + tabindex + '_' + dataRow).val();
                var id_temp = $('#id_temp' + tabindex + '_' + dataRow).val() ? $('#id_temp' + tabindex + '_' + dataRow).val() : 0;
                var month = dataMonth['1'];
                var year = dataMonth['0'];
                var role_id = $('#role-group' + tabindex + '_' + dataRow).val();
                var level_id = $('#level-group' + tabindex + '_' + dataRow).val();

                g_arrDetailData.push({
                    team_id: team_id,
                    level_id: level_id,
                    role_id: role_id,
                    approved_production_cost: approved_production_cost,
                    price: price,
                    price_main: price_main,
                    unit_price: unit_price,
                    billable_effort_select: billable_effort_select,
                    approve_cost_note: approve_cost_note,
                    id: id,
                    id_temp: id_temp,
                    is_approve: $('#is-approve' + tabindex + '_' + dataRow).is(":checked") ? 1 : 0
                });
                totalchild = totalchild + Number(approved_production_cost);
            });

            objectParent = {
                team_id: $('#team-group-' + tabindex).val(),
                approved_production_cost: $('#cost_approved_production' + tabindex).val(),
                price: $('#price' + tabindex).val(),
                price_main: $('#price_main' + tabindex).data("pricemain"),
                unit_price: $('#unit_price' + tabindex).val(),
                billable_effort_select: $('#billable_effort_select' + tabindex).val(),
                approve_cost_note: $('#approve_cost_note' + tabindex).val(),
                id: $('#id' + tabindex).val(),
                id_temp: $('#id_temp' + tabindex).val() ? $('#id_temp' + tabindex).val() : 0,
                month: dataMonth['1'],
                year: dataMonth['0'],
                month_year: dataMonth['0'] + '-' + dataMonth['1'],
                role_id: $('#role-group-' + tabindex).val(),
                level_id: $('#level-group-' + tabindex).val(),
                is_approve: $('#is-approve-' + tabindex).is(":checked") ? 1 : 0,
                detail: g_arrDetailData
            };
            totalchild = totalchild + Number($('#cost_approved_production' + tabindex).val());
            total = total + totalchild;
            data.push(objectParent);
        });
        data['total'] = total;

        return data;
    }

    //---------------------- ajax update approved production cost  -----------------//
    function sendData($this, typeSubmit = '') {
        if ($this.data('requestRunning')) {
            return;
        }
        $this.data('requestRunning', true);
        $this.removeClass('error');
        var value = null;
        if ($this.is(':checkbox')) {
            if ($this.is(':checked')) {
                value = $this.val();
            } else {
                value = null;
            }
        } else {
            value = $this.val();
        }
        var name = $this.attr('id'),
            id = $this.attr('data-id'),
            $indicator = $('<i class="fa fa-refresh fa-spin form-control-feedback"></i>');
        var team = $('#team_id').val();
        if ($this.hasClass('not-approved')) {
            isApproved = false;
        } else {
            isApproved = true;
        }
        if (typeof name == 'undefined') {
            name = $this.attr('same-id');
        }
        $('#' + name + '-error').remove();
        $parent = $this.parent();

        data = {};
        data.name = name;
        data.value = value;
        data._token = token;
        data.isApproved = isApproved;
        data.team = team;
        data.datadetai = $('#data-project-cost').val();
        data.billableDetail = $('#js-billable-detail-data').val();
        data.is_coo = typeSubmit;
        if (typeof id !== 'undefined') {
            data.id = id;
        }
        if (name == 'start_at') {
            input = 'end_at';
            valueEndAt = $("#end_at").val();
            data[input] = valueEndAt;
        }
        if (name == 'end_at') {
            input = 'start_at';
            valueStartAt = $("#start_at").val();
            data[input] = valueStartAt;
        }
        data.project_id = project_id;
        url = urlEditBasicInfo;
        $this.prop('disabled', true);
        $('[data-toggle="tooltip"], .tooltip').tooltip("hide");
        if ($parent.hasClass('input-group')) {
            data.isQuality = true;
            $rightAddon = $parent.find('.form-control + .input-group-addon');
            var indicatorRightPosition = $rightAddon.outerWidth() + 15;
            $indicator.css('right', indicatorRightPosition + 'px');
        } else {
            if ($this.hasClass('scope')) {
                data.isScope = true;
            }
            $indicator.css('right', '25px');
        }
        if ($this.hasClass('id-source-server')) {
            data.isSourceServer = true;
            $indicator.css('top', '70px');
        }
        $indicator.insertAfter($this);

        if ($this.hasClass('select2-hidden-accessible')) {
            $parent.find('.select2-selection__arrow').addClass('display-none');
        }
        $.ajax({
            url: url,
            type: 'post',
            dataType: 'json',
            data: data,
            success: function (data) {
                if (data.hasOwnProperty('data') && data.data.hasOwnProperty('total_cost_approve_detail')) {
                    globalTotalApproveCostDetail = data.data.total_cost_approve_detail ? data.data.total_cost_approve_detail : 0;
                }
                if (!data.status) {
                    if (data.message_error.name) {
                        $this.after('<label id="name-error" class="error" for="name">' + data.message_error.name[0] + '</label>');
                    }
                    if (data.message_error.project_code) {
                        $this.after('<label id="project_code-error" class="error" for="project_code">' + data.message_error.project_code[0] + '</label>');
                    }
                    if (data.message_error.end_at) {
                        $this.after('<label id="end_at-error" class="error" for="end_at">' + data.message_error.end_at[0] + '</label>');
                    }
                    if (data.message_error.start_at) {
                        $this.after('<label id="start_at-error" class="error" for="start_at">' + data.message_error.start_at[0] + '</label>');
                    }
                    if (data.message_error.billable_effort) {
                        $parent.after('<label id="billable_effort-error" class="error" for="billable_effort">' + data.message_error.billable_effort[0] + '</label>');
                    }
                    if (data.message_error.plan_effort) {
                        $parent.after('<label id="plan_effort-error" class="error" for="plan_effort">' + data.message_error.plan_effort[0] + '</label>');
                    }
                    if (data.message_error.cost_approved_production) {
                        $('#cost_approved_production').addClass('error');
                        $('#cost_approved_production-error').remove();
                        $parent.after('<label id="cost_approved_production-error" class="error" for="cost_approved_production">' + data.message_error.cost_approved_production[0] + '</label>');
                        $('#jsErrorCostApprovedAroduction').html(data.message_error.cost_approved_production[0]);
                    }

                    if (data.message_error.lineofcode_baseline) {
                        $this.after('<label id="lineofcode_baseline-error" class="error" for="lineofcode_baseline">' + data.message_error.lineofcode_baseline[0] + '</label>');
                    }
                    if (data.message_error.lineofcode_current) {
                        $this.after('<label id="lineofcode_current-error" class="error" for="lineofcode_current">' + data.message_error.lineofcode_current[0] + '</label>');
                    }
                    if (data.message_error.id_git_external) {
                        $this.after('<label id="id_git_external-error" class="error" for="id_git_external">' + data.message_error.id_git_external[0] + '</label>');
                    }
                    if (data.message_error.id_redmine_external) {
                        $this.after('<label id="id_redmine_external-error" class="error" for="id_redmine_external">' + data.message_error.id_redmine_external[0] + '</label>');
                    }
                    if (data.message_error.schedule_link) {
                        $this.after('<label id="schedule_link-error" class="error" for="schedule_link">' + data.message_error.schedule_link[0] + '</label>');
                    }
                    if (data.message_error.schedule_link) {
                        $this.after('<label id="schedule_link-error" class="error" for="schedule_link">' + data.message_error.schedule_link[0] + '</label>');
                    }
                    if (typeof data.popuperror !== 'undefined' && data.popuperror == 1) {
                        if (typeof data.reload !== 'undefined' && data.reload == 1) {
                            window.location.reload();
                        } else {
                            $('#modal-task-close').modal('show');
                        }
                    }
                } else {
                    let $domTdPrice = $('body').find('.js-td-price');
                    if (data.typeCOO) {
                        for (let i = 0; i < g_oldData.length; i++) {
                            if (g_oldData[i]['is_approve']) {
                                g_oldData[i]['price_main'] = g_oldData[i]['price'];
                                let idItem = g_oldData[i]['id'];
                                let $domPrice = $('body').find(`tr[data-record-id='${idItem}']`);
                                $domPrice.find('.js-input-price').removeClass('unapproved-price').attr('data-original-title', '');
                                
                                //reset pricemain
                                let $domPriceMain = $domPrice.find('.js-price-main'),
                                    valInputPrice = $domPrice.find('.js-input-price').val();
                                $domPriceMain.attr('data-pricemain', valInputPrice).text(valInputPrice);
                            }
                            for (let j = 0; j < g_oldData[i]['detail'].length; j++) {
                                if (g_oldData[i]['detail'][j]['is_approve']) {
                                    g_oldData[i]['detail'][j]['price_main'] = g_oldData[i]['detail'][j]['price'];
                                    let idSubItem = g_oldData[i]['detail'][j]['id'];
                                    let $domSubPrice = $('body').find(`tr[data-record-id='${idSubItem}']`);
                                    $domSubPrice.find('.js-input-price').removeClass('unapproved-price').attr('data-original-title', '');
                                    
                                    //reset pricemain
                                    let $domPriceMain = $domSubPrice.find('.js-price-main'),
                                        valInputPrice = $domSubPrice.find('.js-input-price').val();
                                    $domPriceMain.attr('data-pricemain', valInputPrice).text(valInputPrice);
                                }
                            }
                        }
                        //Show-hide checkbox when approved
                        let $domIsApprove = $('body').find('input[name="is_approve"]:checked');
                        if ($domIsApprove.length > 0) {
                            $domIsApprove.each(function(i, obj) {
                                $(obj).prop('checked', false).attr('type', 'hidden').attr("value", 0);
                            });
                        }
                    } else {
                        $domTdPrice.each(function(i, obj) {
                            let $domInputPrice = $(obj).find('.js-input-price'),
                                $domPriceMain = $(obj).find('.js-price-main'),
                                valInputPrice = $domInputPrice.val(),
                                valPriceMain = $domPriceMain.text();
                            if (valInputPrice == valPriceMain) {
                                $domInputPrice.removeClass('unapproved-price');
                                $domInputPrice.attr('data-original-title', '');
                            } else {
                                let data_tooltip = 'Approved Value: ' + valPriceMain;
                                $domInputPrice.addClass('unapproved-price').attr('data-original-title', data_tooltip)
                            }
                        });
                    }

                    //Show checkbox when data_null
                    let $tdIsApprove = $('body').find('.td-is-approve');
                    if ($tdIsApprove.length > 0) {
                        $tdIsApprove.each(function(i, obj) {
                            let $inputTdIsApprove = $(obj).find("input[value='data_null']");
                            if ($inputTdIsApprove.length > 0) {
                                $inputTdIsApprove.attr("type","checkbox").attr("value","1");
                            }
                        });
                    }

                    // Add/remove checkbox when submit
                    if (hasPermissionViewCostPriceDetail) {
                        $domTdPrice.each(function(i, obj) {
                            let $domTrParent = $(obj).closest('tr'),
                                $domTdCheckbox = $domTrParent.find('.td-is-approve'),
                                $g_domInputPrice = $(obj).find('.js-input-price'),
                                $g_domPriceMain = $(obj).find('.js-price-main'),
                                g_valInputPrice = $g_domInputPrice.val(),
                                g_valPriceMain = $g_domPriceMain.text();
                            if ($domTdCheckbox.length > 0) {
                                let $domInput = $domTdCheckbox.find('input');
                                if ($domInput.length > 0) {
                                    if (g_valInputPrice == g_valPriceMain && g_valInputPrice != '' && g_valPriceMain != '') {
                                        $domInput.prop('checked', false).attr('type', 'hidden').attr("value", 0);
                                    } else {
                                        $domInput.prop('checked', true).attr('type', 'checkbox').attr("value", 1);
                                    }
                                }
                            }
                        });
                    }

                    //Check item has just been added
                    $domTR = $('body').find('.js-tbody tr');
                    if ($domTR.length > 0) {
                        //Get all id
                        $domTR.each(function(i, obj) {
                            let g_idTr = $(obj).attr('data-record-id');
                            if (g_idTr.length > 1) {
                                if(jQuery.inArray(g_idTr, g_allIds) == -1){
                                    g_allIds.push(g_idTr); 
                                }
                            }
                        });

                        $domTR.each(function(i, obj) {
                            let idTr = $(obj).attr('data-record-id');
                            if (idTr.length <= 1) {
                                let arr_month_temp = $(obj).find('.js-arr-month').val(),
                                    approved_production_cost_temp = $(obj).find('.js-cost-approved-production').val(),
                                    team_id_temp = $(obj).find('.js-team-id').val(),
                                    role_temp = $(obj).find('.js-role-id').val() ? $(obj).find('.js-role-id').val() : 0,
                                    level_temp = $(obj).find('.js-level-id').val() ? $(obj).find('.js-level-id').val() : 0,
                                    note_temp = $(obj).find('.js-note').val(),
                                    price_temp = $(obj).find('.js-price-main').text(),
                                    unapproved_price_temp = $(obj).find('.js-input-price').val(),
                                    unit_price_temp = $(obj).find('.js-unit-price').val();
                                if (!approved_production_cost_temp) {
                                    approved_production_cost_temp = 0;
                                }
                                if (price_temp == 'null') {
                                    price_temp = null;
                                } else {
                                    unapproved_price_temp = null;
                                }
                                if (arr_month_temp) {
                                    let monthItem = arr_month_temp.split('-'),
                                        month_temp = monthItem[1],
                                        year_temp = monthItem[0];
                                    $.each(data.dataRespone, function( index, value ) {
                                        if (value.approved_production_cost == parseFloat(approved_production_cost_temp).toFixed(2)
                                            && value.team_id == team_id_temp && value.month == month_temp && value.year == year_temp 
                                            && value.role == role_temp && value.level == level_temp
                                            && value.price == price_temp 
                                            && value.unapproved_price == unapproved_price_temp
                                            && value.note == note_temp && value.unit_price == unit_price_temp) {
                                            let id_item = value.id;
                                            if(jQuery.inArray(id_item, g_allIds) == -1){
                                                g_allIds.push(id_item);
                                                $(obj).attr('data-record-id', id_item);
                                                $(obj).find("input[name='id']").val(id_item);
                                                return false;
                                            }
                                        }
                                        $.each(value.detail, function( key, val ) {
                                            if (val.approved_production_cost == parseFloat(approved_production_cost_temp).toFixed(2)
                                                && val.team_id == team_id_temp && value.month == month_temp && value.year == year_temp 
                                                && val.role == role_temp && val.level == level_temp
                                                && val.price == price_temp && val.unapproved_price == unapproved_price_temp
                                                && val.note == note_temp && val.unit_price == unit_price_temp) {
                                                let id_subItem = val.id;
                                                if(jQuery.inArray(id_subItem, g_allIds) == -1){
                                                    g_allIds.push(id_subItem);
                                                    $(obj).attr('data-record-id', id_subItem);
                                                    $(obj).find("input[name='id']").val(id_subItem);
                                                    return false;
                                                }
                                            }
                                        });
                                    });
                                }
                            }
                        });
                    }

                    $('body').find('.submit-successful').show();                    
                    if (data.result.duration) {
                        $('.duration').html(data.result.duration);
                    }
                    showOrHideButtonSubmitWorkorder(data.isCheckShowSubmit);
                    if (isApproved) {
                        var elSame = $('[same-id="' + $this.attr('same-id') + '"]');
                        if (data.result.isChange) {
                            if ($this.hasClass('select2-hidden-accessible')) {
                                $parent.find('.select2-selection').css('background', '#f7f0cb');
                                if (elSame.length > 0) {
                                    elSame.parent().find('.select2-selection').css('background', '#f7f0cb');
                                }
                            } else {
                                $this.addClass('changed');
                                if (elSame.length > 0) {
                                    elSame.addClass('changed');
                                }
                            }
                        } else {
                            if ($this.hasClass('select2-hidden-accessible')) {
                                $parent.find('.select2-selection').css('background', '#fff');
                            } else {
                                $this.removeClass('changed');
                            }
                        }
                    }
                    //update resource effort
                    var flatResources = data.result.flat_resources;
                    if (typeof flatResources != 'undefined' && flatResources) {
                        updateFlatResource(flatResources, value);
                    }

                    $('input').removeClass('error-input2');
                    $('select').removeClass('error-input2');
                }
            }, complete: function () {
                $this.data('requestRunning', false);
                $this.prop('disabled', false);
                $this.removeAttr('data-requestRunning');
                $indicator.remove();
                if ($this.hasClass('select2-hidden-accessible')) {
                    $parent.find('.select2-selection__arrow').removeClass('display-none');
                }
                $valueTypeMM = $('#type_mm').attr('data-original-title');
                setTimeout(function () {
                    $('[same-id="type_mm"]').next('.select2-container').tooltip({
                        title: $valueTypeMM
                    });
                }, 300);
                // location.reload();
            }
        });
    }


    function checkCostMapping() {
        if ($('#cost_approved_production').length) {
            var innerCost = 0;
            var outterCost = $('#cost_approved_production').val();
            for (var i = 0; i < $('.approve_cost_item').length; i++) {
                innerCost+=(+$($('.approve_cost_item')[i]).val());
            }
            if ($('#type_mm').val() == TYPE_MD) {
                outterCost = outterCost/20;
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
});
</script>
