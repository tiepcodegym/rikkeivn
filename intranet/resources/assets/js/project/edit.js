var projectEditConst = {
    numberShowTeam: 2
};
var load_data = true;
var hash = window.location.hash;
var globalTotalApproveCostDetail = 0;

if (hash) {
    var splHash = hash.split('?');
    hash = splHash[0];
}
RKfuncion.multiselect2 = {
    overfollow: function(dom) {
        var wrapper = dom.$container.closest('.multiselect2-wrapper.flag-over-hidden');
        if (wrapper.length) {
            wrapper.height(wrapper[0].scrollHeight);
        }
    },
    overfollowClose: function(dom) {
        var wrapper = dom.$container.closest('.multiselect2-wrapper.flag-over-hidden');
        if (wrapper.length) {
            wrapper.removeAttr('style');
        }
    }
};

jQuery(document).ready(function ($) {
    selectSearchReload();
    $('#status').multiselect({
        numberDisplayed: 3,
        nonSelectedText: '',
        allSelectedText: 'All',
        onDropdownHide: function(event) {
            RKfuncion.filterGrid.filterRequest(this.$select);
        }
    });
});

jQuery(document).ready(function ($) {
    selectSearchReload();
    $('#status_issue').multiselect({
        numberDisplayed: 3,
        nonSelectedText: '',
        allSelectedText: 'All',
        onDropdownHide: function(event) {
            RKfuncion.filterGrid.filterRequest(this.$select);
        }
    });
});

jQuery(document).ready(function ($) {
    selectSearchReload();
    $('#scope_scope').multiselect({
        numberDisplayed: 7,
        nonSelectedText: '',
        allSelectedText: 'All',
        enableFiltering: true,
        onDropdownHide: function(event) {
            RKfuncion.filterGrid.filterRequest(this.$select);
        }
    });
});

var xhrLoadCust = null;
jQuery(document).ready(function ($) {
    $('#company_id').change(callDataAjax);

    /**
     * Load customer and saler by companyId
     */
    function callDataAjax() {
        if (xhrLoadCust) {
            xhrLoadCust.abort();
        }
        var valueSale = [];
        if (typeof project_id === 'undefined') {
            project_id = null;
        }
        var $el = $("#cust_contact_id");
        var companyId = $(this).val();
        if (!companyId) {
            $el.val('').trigger('change');
            return false;
        }
        setTimeout(function () {
            xhrLoadCust = $.ajax({
                url: urlGenCusByCompany,
                data: {
                    _token: token,
                    company_id: companyId,
                    checkEdit: checkEdit,
                    project_id: project_id,
                    cust_contact_id: $('#cust_contact_id').val(),
                    sale_id: $('#sale-selected').val(),
                },
                dataType: 'json',
                type: 'post',
                success: function (data) {
                    $('#cust_contact_id').val('');
                    $('#sale_id').empty();
                    $('#sale-selected').val('');
                    if (data.status) {
                        if (data.customer.length > 0) {
                            $.each(data.customer, function (key, item) {
                                $el.append($("<option></option>").attr("value", item['id']).text(item['name']));
                            });
                        }
                        if (data.saler.length > 0) {
                            $.each(data.saler, function (key, item) {
                                $('#sale_id').append($("<option selected></option>").attr("value", item.id).text(item.nickname));
                                valueSale.push(item.id);
                            });
                            $('#sale-selected').val(valueSale);
                        }
                        $('#select_customer').html(data.content);
                        $('#sale_id').select2('enable', false);
                        RKfuncion.select2.init({}, $('#cust_contact_id'));
                    }
                },
                complete: function () {
                    xhrLoadCust = null;
                },
            }, 300);
        });
    }

    // render team dev option
    if (typeof RKVarPassGlobal !== 'undefined' && $('select.team-dev-tree').length) {
        var teamDevOption = RKfuncion.teamTree.init(RKVarPassGlobal.teamPath, RKVarPassGlobal.teamSelected);
        var htmlTeamDevOption, disabledTeamDevOption, selectedTeamDevOption;
        $.each(teamDevOption, function(i,v) {
            disabledTeamDevOption = v.disabled ? ' disabled' : '';
            selectedTeamDevOption = v.selected ? ' selected' : '';
            htmlTeamDevOption += '<option value="'+v.id+'"'+disabledTeamDevOption+''
                +selectedTeamDevOption+'>' + v.label+'</option>';
        });
        $('select.team-dev-tree').append(htmlTeamDevOption);
    }
    // end render team dev option

    token = siteConfigGlobal.token;
    //checkReloadPage(true);
    $('#cust_contact_id').select2().on('select2:close', function(evt) { tabToChange (evt)});
    $('#manager_id').select2().on('select2:close', function(evt) { tabToChange (evt)});
    $('#state').select2().on('select2:close', function(evt) { tabToChange (evt)});
    $('#cust_contact_id').select2().on('select2:close', function(evt) { tabToChange (evt)});
    $('.select_leader_id').select2().on('select2:close', function(evt) { tabToChange (evt)});
    $('#level').select2().on('select2:close', function(evt) { tabToChange (evt)});
    $('#type').select2().on('select2:close', function(evt) { tabToChange (evt)});
    $("[data-toggle='tooltip']").tooltip();
    $('.employee_id_select2').select2().on('select2:close', function(evt) { tabToChange (evt)});
    $('.select-stage').select2().on('select2:close', function(evt) { tabToChange (evt)});
    $('.select-stage-deliverable').select2().on('select2:close', function(evt) { tabToChange (evt)});
    $('.tr-project .type-project-member').select2().on('select2:close', function(evt) { tabToChange (evt)});
    $('.training-member-select2').select2().on('select2:close', function(evt) { tabToChange (evt)});
    // $('.customer_communication-role-select2').select2().on('select2:close', function(evt) { tabToChange (evt)});
    $('#company_id').select2().on('select2:close', function(evt) { tabToChange (evt)});
    $('#sale_id').prop('disabled', 'disabled');
    $('#kind_id').select2().on('select2:close', function(evt) { tabToChange (evt)});
    $('#category').select2().on('select2:close', function(evt) { tabToChange (evt)});
    $('#classification').select2().on('select2:close', function(evt) { tabToChange (evt)});
    $('#business').select2().on('select2:close', function(evt) { tabToChange (evt)});
    $('#sub_sector').select2().on('select2:close', function(evt) { tabToChange (evt)});
    $('#cust_contact_id').select2().on('select2:close', function(evt) { tabToChange (evt)});
    $('body').on('focus', '.date', function (e) {
        $(this).datepicker({
            format: 'yyyy-mm-dd',
            weekStart: 1,
            todayHighlight: true,
            autoclose: true
        });
    });
    $valueTypeproject = $('#type').attr('data-original-title');
    $("#type-project .select2-container").tooltip({
        title: $valueTypeproject,
    });
    if($('#type-project .input-basic-info').hasClass('changed')) {
        $('#type-project .select2-selection').css('background', '#f7f0cb');
    }

    if($('#status-project .input-basic-info').hasClass('changed')) {
        $('#status-project .select2-selection').css('background', '#f7f0cb');
    }
    if($('#manager-project .input-basic-info').hasClass('changed')) {
        $('#manager-project .select2-selection').css('background', '#f7f0cb');
    }
    if($('#select_kind .input-basic-info').hasClass('changed')) {
        $('#select_kind .select2-selection').css('background', '#f7f0cb');
    }
    if($('#select_category .input-basic-info').hasClass('changed')) {
        $('#select_category .select2-selection').css('background', '#f7f0cb');
    }
    if($('#select_classification .input-basic-info').hasClass('changed')) {
        $('#select_classification .select2-selection').css('background', '#f7f0cb');
    }
    if($('#select_business .input-basic-info').hasClass('changed')) {
        $('#select_business .select2-selection').css('background', '#f7f0cb');
    }
    if($('#select_sub_sector .input-basic-info').hasClass('changed')) {
        $('#select_sub_sector .select2-selection').css('background', '#f7f0cb');
    }
    if($('#select_cus_email .input-basic-info').hasClass('changed')) {
        $('#select_cus_email .form-control').css('background', '#f7f0cb');
    }
    if($('#select_cus_contact .input-basic-info').hasClass('changed')) {
        $('#select_cus_contact .form-control').css('background', '#f7f0cb');
    }
    $valueStatusproject = $('#state').attr('data-original-title');
    $("#status-project .select2-container").tooltip({
        title: $valueStatusproject,
    });
    $valueManagerProject = $('#manager_id').attr('data-original-title');
    $("#manager-project .select2-container").tooltip({
        title: $valueManagerProject,
    });
    $valueTeam = $('#team_id').attr('data-original-title');
    $("#select-team .team-dropdown").tooltip({
        title: $valueTeam,
    });

    $valueLeader = $('#leader_id').attr('data-original-title');
    $(".div-leader-id .select2-container").tooltip({
        title: $valueLeader,
    });
    $valueTypeMM = $('#type_mm').attr('data-original-title');
    $('[same-id="type_mm"]').next('.select2-container').tooltip({
        title: $valueTypeMM
    });

    setTimeout(function () {
        var selectedOptions = $('#team_id option:selected');
        if (selectedOptions.length > projectEditConst.numberShowTeam) {
            return true;
        }
        input = '';
        selectedOptions.each(function (index, el) {
            if (index == 0) {
                input += $(this).text().trim();
            } else {
                input += ', ' + $(this).text().trim();
            }
        });
        if (input.length) {
            $('#select-team .team-dropdown .multiselect-selected-text').text(input);
        } else {
            $('#select-team .team-dropdown .multiselect-selected-text').text(textChooseTeam);
        }
    });
    $('.multiselect2').multiselect({
        includeSelectAllOption: false,
        numberDisplayed: 2,
        nonSelectedText: RKVarPassGlobal.multiSelectTextNone,
        allSelectedText: RKVarPassGlobal.multiSelectTextAll,
        nSelectedText: RKVarPassGlobal.multiSelectTextSelected,
        enableFiltering: true,
        enableCaseInsensitiveFiltering: true
    });
    $("#select-team .team-dropdown").hover(function(){
    }, function(){
        $('[data-toggle="tooltip"], .tooltip').tooltip("hide");
    });
    $('#team_id').multiselect({
        includeSelectAllOption: false,
        numberDisplayed: projectEditConst.numberShowTeam,
        nonSelectedText: textChooseTeam,
        allSelectedText: checkAll,
        nSelectedText: textTeam,
        enableFiltering: true,
        onChange: function (option, checked, event) {
            $('#prog_langs-error').remove();
            data = {
                _token: token,
                value: $('#team_id').val(),
                project_id: projectId,
                checkEdit: checkEdit,
            };
            $('#team_id-error').remove();
            url = urlGenerateSelectLeader;
            var $indicator = $('<i class="fa fa-refresh fa-spin form-control-feedback"></i>');
            $indicator.css('right', '25px');
            $indicator.insertAfter($('#team_id'));
            $.ajax({
                url: url,
                type: 'post',
                data: data,
                dataType: 'json',
                success: function(data) {
                    if(data.status) {
                        showOrHideButtonSubmitWorkorder(data.isCheckShowSubmit);
                        $('.div-leader-id').html(data.content);
                        $('.select_leader_id').select2().on('select2:close', function(evt) { tabToChange (evt)});
                        if(data.result.isChange) {
                            $('#select-team .multiselect').css('background', '#f7f0cb');
                        } else {
                            $('#select-team .multiselect').css('background', '#fff');
                        }
                        $valueLeader = $('#leader_id').attr('data-original-title');
                        $(".div-leader-id .select2-container").tooltip({
                            title: $valueLeader,
                        });
                        if($('#leader_id').hasClass('changed')) {
                            $('.div-leader-id .select2-selection').css('background', '#f7f0cb');
                        }
                    } else {
                        if(data.message_error.team_id) {
                            $('#select-team .team-dropdown').after('<label id="team_id-error" class="error" for="team_id">'+ data.message_error.team_id[0] +'</label>');
                        }
                        $('.div-leader-id').html(data.content);
                    }
                }, complete: function() {
                    $indicator.remove();
                }
            });
            // Get selected options.
            var selectedOptions = $('#team_id option:selected');
            input = '';
            leaderName = '';
            selectedOptions.each(function (index, el) {
                if (index == 0) {
                    leaderName += $(this).attr('data-leader-name');
                    input += $(this).text().trim();
                } else {
                    leaderName += ', ' + $(this).attr('data-leader-name');
                    input += ', ' + $(this).text().trim();
                }
            });
            if (selectedOptions.length <= projectEditConst.numberShowTeam) {
                setTimeout(function () {
                    if (input.length) {
                        $('#select-team .btn-group .multiselect').attr('title', input);
                        $('#select-team .btn-group .multiselect-selected-text').html(input);
                    }
                }, 10);
            }
            if (selectedOptions.length < 4) {
                setTimeout(function () {
                    $('#group_director').val(leaderName);
                }, 10);
            } else {
                $('#group_director').val(leaderName);
            }
        }
    });
    messages = {
        name: {
            required: nameRequired,
            rangelength: nameMax,
            remote: nameUnique,
        },
        'team_id[]': {
            required: teamRequired,
        },
        company_id: {
            required: companyRequired,
        },
        cust_contact_id: {
            required: customerRequired,
        },
        sale_id: {
            required: saleRequired,
        },
        start_at: {
            required: startAtRequired,
        },
        end_at: {
            required: endAtRequired,
        },
        schedule_link: {
            required: scheduleLinkRequired,
            url: scheduleLinkUrl,
            rangelength: nameMax,
            remote: scheduleLinkUnique,
        },
        lineofcode_baseline: {
            number: baselineNumber,
        },
        lineofcode_current: {
            number: currentNumber,
        },
        id_redmine: {
            rangelength: idRemineMax,
            remote: idRemineUnique,
        },
        id_git: {
            rangelength: idGitMax,
            remote: idGitUnique,
        },
        id_svn: {
            rangelength: idSvnMax,
            remote: idSvnUnique,
        },
        'except[leader_id]' : {
            required: inputGroupLeader
        },
        billable_effort: {
            required: billableEffortRequired,
            number: billableEffortNumber
        },
        plan_effort: {
            required: planEffortRequired,
            number: planEffortNumber
        },
        cost_approved_production: {
            required: costApprovedProductionRequired,
            number: costApprovedProductionNumber,
        },
        billable_effort_select:{
            required: billableEffortSelectRequired,
        },
        plan_effort_select: {
            required: billableEffortSelectRequired,
        },
        cost_approved_production_select: {
            required: billableEffortSelectRequired,
        },
        kind_id: {
            required: kindIdRequired,
        },
        category: {
            required: categoryRequired,
        },
        classification: {
            required: classificationRequired,
        },
        business: {
            required: businessRequired,
        },
        sub_sector: {
            required: subSectorRequired,
        },
        cus_email: {
            email: emailFormatRequired,
        },
        cus_contact: {
            required: cusContactRequired,
        },
    };
    rules = {
        name: {
            required: true,
            rangelength: [1, 255],
            remote: {
                url: urlCheckExists,
                type: "post",
                data: {
                    _token: token,
                    name: 'name',
                    value: function () {
                        return $("#name").val().trim();
                    },
                    table: TABLE_PROJECT,
                    projectId: projectId
                }
            }
        },
        'team_id[]': {
            required: true
        },
        company_id: {
            required: true
        },
        cust_contact_id: {
            required: true
        },
        sale_id: {
            required: true
        },
        start_at: {
            required: true,
            lessThan: 'input[name^="end_at"'
        },
        end_at: {
            required: true
        },
        schedule_link: {
            required: true,
            rangelength: [1, 255],
            url: true,
            remote: {
                url: urlCheckExists,
                type: "post",
                data: {
                    _token: token,
                    name: 'schedule_link',
                    value: function () {
                        return $("#schedule_link").val().trim();
                    },
                    table: TABLE_PROJECT_META,
                    projectId: projectId
                }
            }
        },
        lineofcode_baseline: {
            number: true,
            rangelength: [1, 255]
        },
        lineofcode_current: {
            number: true,
            rangelength: [1, 255]
        },
        id_redmine: {
            rangelength: [1, 100],
            remote: {
                url: checkExistsSourceServer,
                type: "post",
                data: {
                    _token: token,
                    name: 'id_redmine',
                    value: function () {
                        return $("#id_redmine").val();
                    },
                    projectId: projectId
                }
            },
            validateIdRemine: true,
        },
        id_git: {
            rangelength: [1, 100],
            remote: {
                url: checkExistsSourceServer,
                type: "post",
                data: {
                    _token: token,
                    name: 'id_git',
                    value: function () {
                        return $("#id_git").val();
                    },
                    projectId: projectId
                }
            },
            validateIdGit: true,
        },
        id_svn: {
            rangelength: [1, 100],
            remote: {
                url: checkExistsSourceServer,
                type: "post",
                data: {
                    _token: token,
                    name: 'id_svn',
                    value: function () {
                        return $("#id_svn").val();
                    },
                    projectId: projectId
                }
            },
            validateIdSvn: true,
        },
        manager_id: {
            required: true
        },
        'except[leader_id]' : {
            required: true
        },
        billable_effort: {
            required: true,
            number: true,
            greateThan: 0,
        },
        billable_effort_select: {
            required: true,
        },
        plan_effort: {
            required: true,
            number: true,
            greateThan: 0,
        },
        plan_effort_select: {
          required: true,
        },
        cost_approved_production: {
            required: true,
            number: true,
            greateThan: 0,
        },
        cost_approved_production_select: {
            required: true,
        },
        kind_id: {
            required: true,
        },
        category: {
            required: true,
        },
        classification: {
            required: true,
        },
        business: {
            required: true,
        },
        sub_sector: {
            required: true,
        },
        cus_email: {
            email: true,
        }
    };

    jQuery.validator.addMethod('greateThan', function (value, element, param) {
        return value > param;
    }, valueGreaterThanZero);
    jQuery.validator.addMethod('lessThan', function(value, element, param) {
        if (value && $(param).val()) {
            return this.optional(element) || value <= $(param).val();
        }
        return true;
    }, startDateBefore);
    jQuery.validator.addMethod("validateIdRemine", function(value, element) {
        if ($('#is_check_redmine').is(":checked")) {
            if (!$('#id_redmine').val().trim()) {
                return false;
            }
            return true;
        }
        return true;
    }, idRemineRequired);
    jQuery.validator.addMethod("validateIdGit", function(value, element) {
        if ($('#is_check_git').is(":checked")) {
            if (!$('#id_git').val().trim()) {
                return false;
            }
            return true;
        }
        return true;
    }, idGitRequired);
    jQuery.validator.addMethod("validateIdSvn", function(value, element) {
        if ($('#is_check_svn').is(":checked")) {
            if (!$('#id_svn').val().trim()) {
                return false;
            }
            return true;
        }
        return true;
    }, idSvnRequired);
    $('#create-project-form').validate({
        rules: rules,
        ignore: ':hidden:not("#team_id, #sale_id, #sale-selected")',
        errorClass: "error",
        messages: messages,
        errorPlacement: function (error, element) {
            if (element.attr("name") == "team_id[]") {
                error.insertAfter("#select-team .team-dropdown");
            } else if (element.attr("name") == "sale_id[]") {
                error.insertAfter('#select-sales .team-dropdown');
            } else if (element.attr("name") == "billable_effort") {
                error.insertAfter('#input-billable-effort .input-group');
            } else if (element.attr("name") == "plan_effort") {
                error.insertAfter('#input-plan-effort .input-group');
            } else if (element.attr("name") == "cost_approved_production") {
                error.insertAfter('#input-cost-approved-production .input-group');
            } else if (element.attr("name") == "cust_contact_id") {
                error.insertAfter("#select_customer .select2-selection");
            } else if (element.attr("name") == "billable_effort_select") {
                error.insertAfter("#input-billable-effort");
            } else if (element.attr("name") == "plan_effort_select") {
                error.insertAfter("#input-plan-effort .input-group");
            } else if (element.attr("name") == "cost_approved_production_select") {
                error.insertAfter("#input-cost-approved-production .input-group");
            } else if (element.attr("name") == 'company_id') {
                error.insertAfter("#select_company .select2-selection");
            } else if (element.attr("name") == 'kind_id') {
                error.insertAfter("#select_kind .select2-selection");
            } else if (element.attr("name") == 'category') {
                error.insertAfter("#select_category .select2-selection");
            } else if (element.attr("name") == 'classification') {
                error.insertAfter("#select_classification .select2-selection");
            } else if (element.attr("name") == 'business') {
                error.insertAfter("#select_business .select2-selection");
            } else if (element.attr("name") == 'sub_sector') {
                error.insertAfter("#select_sub_sector .select2-selection");
            } else if (element.attr("name") == 'cus_email') {
                error.insertAfter("#select_cus_email .form-control");
            } else if (element.attr("name") == 'cus_contact') {
                error.insertAfter("#select_cus_contact .form-control");
            } else {
                error.insertAfter(element);
            }
        }
    });
        $('#billable_effort_select').on("change",function(){ selectFunction();});
        $('#plan_effort_select').on("change",function(){ selectFunction();});
        $('#cost_approved_production_select').on("change",function(){ selectFunction();});
        function selectFunction(){
        $('#billable_effort-error-select').css ("display", "none");
        $('#plan_effort_select-error').css ("display", "none");
        $('#cost_approved_production_select-error').css ("display", "none");
        }

    /**
     * Submit Work Order click event
     * Check sale require
     */
    var requiredFields = [
        {field: 'sale_id', messTab: saleRequiredTabBasic, mess: saleRequired,},
        {field: 'cust_contact_id', messTab: customerRequiredTabBasic, mess: customerRequired,},
        {field: 'company_id', messTab: companyRequiredTabBasic, mess: companyRequired,},
        {field: 'cost_approved_production', messTab: approvedProdCostRequiredTabBasic, mess: approvedProdCostRequired,},
        {field: 'kind_id', messTab: kindRequiredTabBasic, mess: kindIdRequired,},
        {field: 'category', messTab: categoryRequiredTabBasic, mess: categoryRequired,},
        {field: 'classification ', messTab: classificationRequiredTabBasic, mess: classificationRequired,},
        {field: 'business', messTab: businessRequiredTabBasic, mess: businessRequired,},
        {field: 'sub_sector', messTab: subSectorRequiredTabBasic, mess: subSectorRequired,},
    ];

    function isApproveCostValid() {
        var totalApproveCostDetail = +globalTotalApproveCostDetail;
        var approveCost = +$('#cost_approved_production').val();

        if ($('#type_mm').val() == TYPE_MD) {
            approveCost = approveCost / 20
        }

        return approveCost >= totalApproveCostDetail;
    }

    $('.open-modal-wo-submit').click(function () {
        var messageErrors = [];
        for (var i = 0; i < requiredFields.length; i++) {
            var field = requiredFields[i];
            var elField = $('#' + field.field);
            var fieldVal = elField.val();
            elField.removeClass('error');
            if (!fieldVal) {
                elField.addClass('error');
                messageErrors.push(field.messTab);
                var container = elField.parent().parent();
                container.find('#' + field.field + '-error').remove();
                container.append('<label id="'+ field.field +'-error" class="error" for="'+ field.field +'">' + field.mess + '</label>');
            }
            $('#prog_langs').removeClass('error');
        }
        var customer = $('#cust_contact_id').val();
        if (customer === '') {
            $('#select_customer').next('#cust_contact_id-error').remove();
            $('#select_customer .select2-selection').after('<label id="cust_contact_id-error" class="error" for="cust_contact_id">'+ customerRequired + '</label>');
        }
        //Check Approve cost Valid
        if (!isApproveCostValid()) {
            messageErrors.push(approveCostInvalidTabBasic);
        }

        if (messageErrors.length > 0) {
            $('#modal-warning-notification').modal('show');
            $('#modal-warning-notification .modal-body .text-default').html(messageErrors.join("<br />"));
            return false;
        }
        $('#modal-wo-submit').modal('show');
    });

    /**
     * Select #sale_id change event
     * check if has sale then remove class error
     */
    for (var i = 0; i < requiredFields.length; i++) {
        $('#' + requiredFields[i].field).change(function() {
            var saleVal = $(this).val();
            if (saleVal != null) {
                $(this).removeClass('error');
            }
        });
    }

    /**
     * Check #sale_id
     * if #sale_id has class error then display error
     * else remove label error
     * run every 100ms
     */
    setInterval(function() {
        var container = $('#sale_id').parent().parent();
        var companyDiv = $('#company_id').parent().parent();
        container.find('#sale_id-error').remove();
        companyDiv.find('#company_id-error').remove();
        if ($('#sale_id').hasClass('error')) {
            container.append('<label id="sale_id-error" class="error" for="sale_id">' + saleRequired + '</label>');
        }
        if ($('#company_id').hasClass('error')) {
            companyDiv.append('<label id="company_id-error" class="error" for="company_id">' + companyRequired + '</label>');
        }
    }, 100);

    $('#modal-warning-reload').on('hidden.bs.modal', function () {
        location.reload();
    });

    $(document).on('change', '.qua_gate_actual-stage-and-milestone', function(event) {
        event.preventDefault();
        if ($(this).val()) {
            if ($('.span-qua_gate_result-stage-and-milestone').hasClass('display-none')) {
                $('.span-qua_gate_result-stage-and-milestone').removeClass('display-none');
                $('.qua_gate_result-stage-and-milestone').addClass('display-none');
            }
        } else {
            if (!$('.span-qua_gate_result-stage-and-milestone').hasClass('display-none')) {
                $('.span-qua_gate_result-stage-and-milestone').addClass('display-none');
                $('.qua_gate_result-stage-and-milestone').removeClass('display-none');
            }
        }
    });

    $(document).on('change', '.input-qua_gate_actual', function(event) {
        id = $(this).data('id');
        event.preventDefault();
        if ($(this).val()) {
            if ($('.span-qua_gate_result-stage-and-milestone-' + id).hasClass('display-none')) {
                $('.qua_gate_result-stage-and-milestone-' + id).addClass('display-none');
                $('.span-qua_gate_result-stage-and-milestone-' + id).removeClass('display-none');
            }
        } else {
            if (!$('.qua_gate_result-stage-and-milestone-' + id).data('status')) {
                $('.qua_gate_result-stage-and-milestone-' + id).text('NA');
            }
            if ($('.qua_gate_result-stage-and-milestone-' + id).hasClass('display-none')) {
                $('.qua_gate_result-stage-and-milestone-' + id).removeClass('display-none');
                $('.span-qua_gate_result-stage-and-milestone-' + id).addClass('display-none');
            }
        }
    });

    $(document).on('change', '.input-project-wo-note', function (event) {
        name = $(this).attr('name');
        value = $(this).val();
        data = {
            data: {}
        };
        data.data[name] = value;
        data._token = token;
        data.id = project_id;
        url = urlUpdateNote;
        $.ajax({
            url: url,
            type: 'post',
            dataType: 'json',
            data: data,
            success: function (data) {
                if(!data.status) {
                    $('#modal-warning-notification .text-default').html(messageError);
                    $('#modal-warning-notification').modal('show');
                }
            },
            error: function () {
                $('#modal-warning-notification .text-default').html(messageError);
                $('#modal-warning-notification').modal('show');
            }
        });
    });

    $(document).on('click', '.radio-toggle-click', function (event) {
        var $this = $(this);
        var name = $(this).attr('name');
        var value = $(this).attr('value');
        var dataId = $this.attr('data-id');
        data = {};
        data.name = name;
        data.value = value;
        data._token = token;
        data.project_id = project_id;
        url = urlEditBasicInfo;
        data.isSourceServer = true;
        $.ajax({
            url: url,
            type: 'post',
            dataType: 'json',
            data: data,
            success: function(data) {
                $('.radio-toggle-click-show-' + name).addClass('display-none');
                $('.radio-' + dataId).removeClass('display-none');
            }
        });
    });
    $(document).on('click', '.edit-strategy', function (event) {
        $(this).addClass('display-none');
        $('.refresh-strategy').removeClass('display-none');
        $('.content-strategy').addClass('display-none');
        $('.input-content-strategy').removeClass('display-none');
        setTimeout(function () {
            input = $('.input-content-strategy')
            input.focus();
            var tmpStr = input.val();
            input.val('');
            input.val(tmpStr);
        }, 0);
    });
    $(document).on('click', '.refresh-strategy', function (event) {
        $(this).addClass('display-none');
        $('.edit-strategy').removeClass('display-none');
        $('.input-content-strategy').addClass('display-none');
        $('.content-strategy').removeClass('display-none');
    });
    $(document).on('change', '.input-content-strategy', function (event) {
        $('.error-validate-input-content-strategy').remove();
        val = $(this).val();
        data = {
            _token: token,
            content: val,
            project_id: project_id,
        }
        url = urlAddQualityPlan;
        $.ajax({
            url: url,
            type: 'post',
            dataType: 'json',
            data: data,
            success: function (data) {
                if(data.status) {
                    $('.edit-strategy').removeClass('display-none');
                    $('.refresh-strategy').addClass('display-none');
                    $('.input-content-strategy').addClass('display-none');
                    $('.content-strategy').removeClass('display-none');
                    $('.content-strategy').text(val);
                } else {
                    $('.input-content-strategy').after('<p class="word-break error-validate error-validate-input-content-strategy" for="input-content-strategy">' + data.message_error.content[0] + '</p>');
                }
            },
            error: function () {
                $('#modal-warning-notification .text-default').html(messageError);
                $('#modal-warning-notification').modal('show');
            }
        });
    });

    $('.input-endat').datepicker({
        autoclose: true
    });
    $('body').on('focus', '.input-endat', function (e) {
        $(this).datepicker({
            autoclose: true
        });
    });
    $(document).on('click', '.btn-edit-end-at', function (event) {
        $(this).addClass('display-none');
        $('.input-endat').removeClass('display-none');
        $('.btn-save-end-at').removeClass('display-none');
        $('.btn-cancel-end-at').removeClass('display-none');
        $('.btn-edit-end-at').addClass('display-none');
    });

    $(document).on('click', '.btn-cancel-end-at', function (event) {
        oldValue = $('.edit-draft-end-date').text();
        if (!oldValue) {
            oldValue = $('.content-endat-approved').text();
        }
        $('.input-endat').val(oldValue.trim());
        $(this).addClass('display-none');
        $('.input-endat').addClass('display-none');
        $('.btn-edit-end-at').removeClass('display-none');
        $('.btn-save-end-at').addClass('display-none');
    });

    $(document).on('click', '.btn-save-end-at', function (event) {
        $('.error-validate-performance-arroved').remove();
        if ($(this).data('requestRunning')) {
            return;
        }
        $(this).data('requestRunning', true);
        var _this = $(this);
        var endDate = $('.input-endat').val();
        if (workorderApproved.performance) {
            data = {
                _token: token,
                project_id: project_id,
                end_at: endDate
            };
            id = $('.input-endat').attr('data-id');
            if (typeof id !== 'undefined') {
                data.id = id;
                data.isEdit = true;
            } else {
                data.isAddNew = true;
            }
        } else {
            data = {
                _token: token,
                project_id: project_id,
                end_at: endDate
            };
        }
        url = urlAddPerformance;

        $.ajax({
            url: url,
            type: 'post',
            dataType: 'json',
            data: data,
            success: function (data) {
                if(!data.status) {
                    if (data.message_error.end_at) {
                        $('.input-endat').after('<p class="word-break error-validate error-validate-performance-arroved" for="input-endat">' + data.message_error.end_at[0] + '</p>')
                    }
                } else {
                    $('#table-performance .content-end-date').html(data.content);
                    showOrHideButtonSubmitWorkorder(data.isCheckShowSubmit);
                    if (data.duration) {
                        $('.duration').html(data.duration);
                    }
                }
            },
            error: function () {
                $('#modal-warning-notification .text-default').html(messageError);
                $('#modal-warning-notification').modal('show');
            }, complete: function () {
                _this.data('requestRunning', false);
            },
        });
    });

    $(document).on('click', '.btn-edit-billable_effort', function (event) {
        showFormEditQuality('billable_effort');
    });

    $(document).on('click', '.btn-cancel-billable_effort', function (event) {
        hiddeFormEditQuality('billable_effort');
    });

    $(document).on('click', '.btn-edit-plan_effort', function (event) {
        showFormEditQuality('plan_effort');
    });

    $(document).on('click', '.btn-cancel-plan_effort', function (event) {
        hiddeFormEditQuality('plan_effort');
    });

    /* slove quality*/
    $(document).on('click', '.btn-save-quality', function (event) {
        var _this = $(this);
        if (_this.data('requestRunning')) {
            return;
        }
        _this.data('requestRunning', true);
        name = _this.attr('data-name');
        $('.error-validate-' + name).remove();
        value = $("#table-quality ." + name).val();
        url = urlAddQuality;
        data = {
            data: {}
        };
        id = $("#table-quality ." + name).attr('data-id');
        if (typeof id !== 'undefined') {
            $('.error-validate-' + name + '-' + id).remove();
            data.id = id;
            data.isEdit = true;
        } else {
            $('.error-validate-' + name).remove();
            data.isAddNew = true;
        }
        data._token = token;
        data.data[name] = value;
        data.data['type'] = name;
        data.project_id = project_id;

        $.ajax({
            url: url,
            type: 'post',
            dataType: 'json',
            data: data,
            success: function (data) {
                if (data.status) {
                    $('.content-' + name).html(data.content);
                    showOrHideButtonSubmitWorkorder(data.isCheckShowSubmit);
                } else {
                    if (data.message_error.billable_effort) {
                        $('.content-' + name + ' .input-add-quality').after('<p class="word-break error-validate">' + data.message_error.billable_effort[0] + '</p>');
                    }
                    if (data.message_error.plan_effort) {
                        $('.content-' + name + ' .input-add-quality').after('<p class="word-break error-validate">' + data.message_error.plan_effort[0] + '</p>');
                    }
                }
            },
            error: function () {
                $('#modal-warning-notification .text-default').html(messageError);
                $('#modal-warning-notification').modal('show');
            },
            complete: function () {
                _this.data('requestRunning', false);
            },
        });
    });
    $('.flex-text').flexText();
    RKfuncion.keepStatusTab.init();

    $('#workorder a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var $tab = $($(e.target).attr('href'));
        var $typeElement = $(e.target).attr('href').replace(/(\#|\?|\=|\&|\:|\/)/g, '');
        loadData($tab, $typeElement);
    });
    $('#project-over >ul >li > a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        if ($(e.target).attr('href') == '#workorder') {
            if (tabActiveWO) {
                if (typeof project_id != 'undefined') {
                    tabs = $('.wo-tab-content #' + tabActiveWO);
                    loadData(tabs, tabActiveWO);
                }
            }
        }
    });
    if (tabActive == 'workorder') {
        if(hash && $('ul.nav a[href="' + hash + '"]').tab('show')) {
            tabs = $('.wo-tab-content ' + hash);
            loadData(tabs, hash.replace(/(\#|\?|\=|\&|\:|\/)/g, ''));
        } else {
            if (tabActiveWO) {
                if (typeof project_id != 'undefined') {
                    tabs = $('.wo-tab-content #' + tabActiveWO);
                    loadData(tabs, tabActiveWO);
                }
            }
        }
    }

    $(document).on('change', 'select.select-proj-member-type', function (event) {
        var __this = $(this);
        if (__this.val() == RKVarPassGlobal.memberTypeDev
            || __this.val() == RKVarPassGlobal.memberTypeLeader
            || __this.val() == RKVarPassGlobal.memberTypeSubPm
            || __this.val() == RKVarPassGlobal.memberTypePm ) {
            __this.closest('tr').find('td.td-prog .proj-member-prog-lang .btn-group,' +
                'td.td-prog .proj-member-prog-lang .prog-project-member').removeClass('hidden');
        } else {
            __this.closest('tr').find('td.td-prog .proj-member-prog-lang .btn-group, ' +
                'td.td-prog .proj-member-prog-lang .prog-project-member').addClass('hidden');
        }
    });
    $( ".active-summary" ).click(function() {
        url = urlGetALlPmOfProjectByAjax;
        token = siteConfigGlobal.token;
        data = {
            _token: token,
            project_id: projectId,
        };
        $.ajax({
            url: url,
            type: 'post',
            data: data,
            dataType: 'json',
            success: function(data) {
                var manager_id = managerId;
                $('#manager_id').empty();
                if (data.allPmActive) {
                    var newOption = new Option('', '');
                    $("#manager_id").append(newOption);
                    $.each( data.allPmActive, function( key, value ) {
                        var str= value.email;
                        var nameMatch = str.match(/^([^@]*)@/);
                        var name = nameMatch ? nameMatch[1] : null;
                        var newOption = new Option(value.name + ' (' + name + ')', value.id);
                        if (manager_id == value.id) {
                            $("#manager_id").append(newOption);
                        } else {
                            $("#manager_id").append(newOption);
                        }
                    });
                }
                if(data.currentPm) {
                    $("#manager_id").val(data.currentPm).trigger('change');
                }
            }
        });
    });
});

$(document).on('click', '.show-content-table', function (event) {
    var type = $(this).data('type');
    $('#loading-' + type).removeClass('display-none');
    if ($('#workorder-content-' + type + ' .table-content-' +type).length) {
        $('#workorder-content-' + type + ' .table-content-' +type).removeClass('display-none');
        $('.add-or-edit-' + type).show();
        $(this).addClass('display-none');
        $('.hide-content-table-' + type).removeClass('display-none');
    } else {
        buttonThis = $(this);
        if (buttonThis.data('requestRunning')) {
            return;
        }
        buttonThis.data('requestRunning', true);
        url = urlGetContentTable;
        data = {
            _token: token,
            projectId: project_id,
            type: type
        }
        $.ajax({
            url: url,
            data: data,
            dataType: 'json',
            type: 'post',
            success:function(data) {
                if (data.status) {
                    if (data.content.error) {
                        $('#modal-warning-notification .text-default').html(messageError);
                        $('#modal-warning-notification').modal('show');
                        $('#workorder-content-' + type).html(data.content.content);
                    } else {
                        $('#workorder-content-' + type).html(data.content);
                    }
                    $('[data-toggle="tooltip"], .tooltip').tooltip("hide");
                    if (type == TYPE_PROJECT_MEMBER) {
                        $('.employee_id_select2').select2().on('select2:close', function(evt) { tabToChange (evt)});
                        $('.tr-project .type-project-member').select2().on('select2:close', function(evt) { tabToChange (evt)});
                        iniTableSorter();
                    }
                    if (type == TYPE_STAGE_AND_MILESTONE) {
                        $('.select-stage').select2().on('select2:close', function(evt) { tabToChange (evt)});
                    }
                    if (type == TYPE_DELIVERABLE) {
                        $('.select-stage-deliverable').select2().on('select2:close', function(evt) { tabToChange (evt)});
                    }
                    if (type == TYPE_WO_DEVICES_EXPENSE) {
                        $('#table-derived-expenses input.time-datepicker').datepicker({
                            format: 'yyyy-mm',
                            weekStart: 1,
                            todayHighlight: true,
                            autoclose: true,
                            viewMode: "months",
                            minViewMode: "months"
                        });
                    }
                }
            },
            complete: function () {
                buttonThis.data('requestRunning', false);
                $('#loading-' + type).addClass('display-none');
            },
        });
    }

});

$(document).on('click', '.hide-content-table', function (event) {
    type = $(this).data('type');
    $('.table-content-' + type).addClass('display-none');
    $(this).addClass('display-none');
    $('.show-content-table-' + type).removeClass('display-none');
    $('.add-or-edit-' + type).hide();
});

$(document).on('change', '.input-basic-info', function (event) {
    var $this = $(this);
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
    name = $this.attr('id'),
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
    if (typeof id !== 'undefined') {
        data.id = id;
    }
    if (name == 'start_at') {
        input = 'end_at';
        valueEndAt = $("#end_at").val();
        data[input] = valueEndAt;
    }
    if(name == 'end_at') {
        input = 'start_at';
        valueStartAt = $("#start_at").val();
        data[input] = valueStartAt;
    }
    data.project_id = project_id;

    if (name == 'close_date') {
        input = 'close_date';
        valueCloseDate = $('#close_date').val();
        data[input] = valueCloseDate;
    }

    url = urlEditBasicInfo;
    $this.prop('disabled', true);
    $('[data-toggle="tooltip"], .tooltip').tooltip("hide");
    if($parent.hasClass('input-group')) {
        data.isQuality = true;
        $rightAddon = $parent.find('.form-control + .input-group-addon');
        var indicatorRightPosition = $rightAddon.outerWidth() + 15;
        $indicator.css('right', indicatorRightPosition + 'px');
    } else {
        if($this.hasClass('scope')) {
            data.isScope = true;
        }
        $indicator.css('right', '25px');
    }
    if($this.hasClass('id-source-server')) {
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
        success: function(data) {
            if (data.hasOwnProperty('data') && data.data.hasOwnProperty('total_cost_approve_detail')) {
                globalTotalApproveCostDetail = data.data.total_cost_approve_detail ? data.data.total_cost_approve_detail : 0;
            }
            if(!data.status) {
                if(data.message_error.name) {
                    $this.after('<label id="name-error" class="error" for="name">'+ data.message_error.name[0] +'</label>');
                }
                if(data.message_error.end_at) {
                    $this.after('<label id="end_at-error" class="error" for="end_at">'+ data.message_error.end_at[0] +'</label>');
                }
                if(data.message_error.start_at) {
                    $this.after('<label id="start_at-error" class="error" for="start_at">'+ data.message_error.start_at[0] +'</label>');
                }
                if(data.message_error.billable_effort) {
                    $parent.after('<label id="billable_effort-error" class="error" for="billable_effort">'+ data.message_error.billable_effort[0] +'</label>');
                }
                if(data.message_error.plan_effort) {
                    $parent.after('<label id="plan_effort-error" class="error" for="plan_effort">'+ data.message_error.plan_effort[0] +'</label>');
                }
                if (data.message_error.cost_approved_production) {
                    $('#cost_approved_production').addClass('error');
                    $('#cost_approved_production-error').remove();
                    $parent.after('<label id="cost_approved_production-error" class="error" for="cost_approved_production">'+ data.message_error.cost_approved_production[0] +'</label>');
                }

                if(data.message_error.lineofcode_baseline) {
                    $this.after('<label id="lineofcode_baseline-error" class="error" for="lineofcode_baseline">'+ data.message_error.lineofcode_baseline[0] +'</label>');
                }
                if(data.message_error.lineofcode_current) {
                    $this.after('<label id="lineofcode_current-error" class="error" for="lineofcode_current">'+ data.message_error.lineofcode_current[0] +'</label>');
                }
                if(data.message_error.id_git_external) {
                    $this.after('<label id="id_git_external-error" class="error" for="id_git_external">'+ data.message_error.id_git_external[0] +'</label>');
                }
                if(data.message_error.id_redmine_external) {
                    $this.after('<label id="id_redmine_external-error" class="error" for="id_redmine_external">'+ data.message_error.id_redmine_external[0] +'</label>');
                }
                if(data.message_error.schedule_link) {
                    $this.after('<label id="schedule_link-error" class="error" for="schedule_link">'+ data.message_error.schedule_link[0] +'</label>');
                }
                if (typeof data.popuperror !== 'undefined' && data.popuperror == 1) {
                    if (typeof data.reload !== 'undefined' && data.reload == 1) {
                        window.location.reload();
                    } else {
                        $('#modal-task-close').modal('show');
                    }
                }
            } else {
                if(data.result.duration) {
                    $('.duration').html(data.result.duration);
                }
                showOrHideButtonSubmitWorkorder(data.isCheckShowSubmit);
                if(isApproved) {
                    var elSame = $('[same-id="'+ $this.attr('same-id') +'"]');
                    if(data.result.isChange) {
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
            }
        }, complete: function() {
            $this.data('requestRunning', false);
            $this.prop('disabled', false);
            $this.removeAttr('data-requestRunning');
            $indicator.remove();
            if ($this.hasClass('select2-hidden-accessible')) {
                $parent.find('.select2-selection__arrow').removeClass('display-none');
            }
            $valueTypeMM = $('#type_mm').attr('data-original-title');
            if (name === 'type_mm') {
                $('#cost_approved_production').trigger('change');
            }
            setTimeout(function () {
                $('[same-id="type_mm"]').next('.select2-container').tooltip({
                    title: $valueTypeMM
                });
            }, 300);
        }
    });
});
//update flat resource if change resource type
function updateFlatResource(flatResources, value) {
    var totalResource = 0;
    var totalDraftResource = 0;
    for (var i in flatResources) {
        var flatItem = flatResources[i];
        var elItem = $('.tr-project-' + flatItem.id + ' .flat_resource_col');
        if (elItem.length > 0) {
            elItem.find('span').text(flatItem.flat_resource);
            if (flatItem.parent_id !== null) {
                var parentItem = null;
                for (var j in flatResources) {
                    if (flatResources[j].id == flatItem.parent_id) {
                        parentItem = flatResources[j];
                        break;
                    }
                }
                if (parentItem) {
                    elItem.attr('data-original-title', approvedText + ': ' + parentItem.flat_resource);
                    totalResource += parentItem.flat_resource;
                } else {
                    totalResource += flatItem.flat_resource;
                }
            } else {
                totalResource += flatItem.flat_resource;
            }
            totalDraftResource += flatItem.flat_resource;
        }
    }
    var elTotalFlatResource = $('.total_flat_resource');
    var fixedDigit = parseInt(value) == MD_TYPE ? 1 : 2;
    if (elTotalFlatResource.hasClass('is-change-value')) {
        elTotalFlatResource.attr('data-original-title', approvedText + ': ' + totalResource.toFixed(fixedDigit));
    }
    elTotalFlatResource.text(totalDraftResource.toFixed(fixedDigit));
}

$(document).on('click', '.delete-performance', function (event) {
    if (workorderApproved.project_member) {
        deleteWorkorderApproved('performance', this, urlAddPerformance);
    } else {
        deleteWorkorderNotApproved('performance', this, urlAddPerformance);
    }
});


$(document).on('change', '.input-reason-change-workorder', function () {
    id = $(this).data('id');
    value = $(this).val();
    data = {
        _token: token,
        id: id,
        reason: value,
        project_id: project_id
    }
    url = urlUpdateReason;
    $.ajax({
        url: url,
        type: 'post',
        dataType: 'json',
        data: data,
        success: function (data) {
            if(!data.status) {
                $('#modal-warning-notification .text-default').html(data.message);
                $('#modal-warning-notification').modal('show');
            }
        },
        error: function () {
            $('#modal-warning-notification .text-default').html(data.message);
            $('#modal-warning-notification').modal('show');
        }
    });
});
$(document).on('click', '.edit-cm-plan', function () {
    dataId = $(this).data('id');
    $('.flex-text-' + dataId).flexText();
    $('.content-cm-plan-' + dataId).addClass('display-none');
    $('.input-cm-plan-' + dataId).removeClass('display-none');
    $('.save-cm-plan-' + dataId).removeClass('display-none');
    $(this).addClass('display-none');
    $('.delete-cm-plan-' + dataId).addClass('display-none');
    $('.refresh-cm-plan-' + dataId).removeClass('display-none');
    $('.input-cm-plan-' + dataId).parent().addClass('display-block');
    setTimeout(function () {
        input = $('.input-cm-plan-' + dataId)
        input.focus();
        var tmpStr = input.val();
        input.val('');
        input.val(tmpStr);
    }, 0);

});

$(document).on('click', '.refresh-cm-plan', function () {
    dataId = $(this).data('id');
    $(this).addClass('display-none');
    $('.error-validate-cm-plan-'+ dataId).remove();
    $('.content-cm-plan-' + dataId).removeClass('display-none');
    $('.input-cm-plan-' + dataId).addClass('display-none');
    $('.input-cm-plan-' + dataId).val($('.content-cm-plan-' + dataId).text());
    $('.save-cm-plan-' + dataId).addClass('display-none');
    $('.delete-cm-plan-' + dataId).removeClass('display-none');
    $('.edit-cm-plan-' + dataId).removeClass('display-none');
    $('.input-cm-plan-' + dataId).parent().removeClass('display-block');
    if ($('.content-cm-plan-' + dataId).height() > 57) {
        $('.input-cm-plan-' + dataId).height($('.content-cm-plan-' + dataId).height());
    } else {
        $('.input-cm-plan-' + dataId).height(57);
    }
});


$(document).on('click', '.add-cm-plan', function () {
    $('.div-cm-plan-hidden').removeClass('display-none');
    $('.div-cm-plan-hidden .flex-text-wrap').addClass('display-block');
    setTimeout(function () {
        input = $('.input-cm-plan-add')
        input.focus();
        var tmpStr = input.val();
        input.val('');
        input.val(tmpStr);
    }, 0);
    $(this).addClass('display-none');
});

$(document).on('click', '.remove-cm-plan', function () {
    $('.div-cm-plan-hidden').addClass('display-none');
    $('.input-cm-plan-add').val('');
    $('.add-cm-plan').removeClass('display-none');
    $('.error-validate-cm-plan').remove();
});


$(document).on('click', '.save-cm-plan', function () {
    if ($(this).data('requestRunning')) {
        return;
    }
    element = $(this);
    $(this).data('requestRunning', true);
    $(this).addClass('display-none');

    id = $(this).data('id');
    if (typeof id !== 'undefined') {
        $('.error-validate-cm-plan-'+ id).remove();
        val = $('.input-cm-plan-' + id).val();
        data = {
            _token: token,
            content: val,
            project_id: project_id,
            id: id,
            isEdit: true,
        }
        $('#loading-item-cmplan-' +id).removeClass('display-none');
    } else {
        $('.error-validate-cm-plan').remove();
        val = $('.input-cm-plan-add').val();
        data = {
            _token: token,
            content: val,
            project_id: project_id,
            isAddNew: true,
        }
        $('#loading-item-cmplan').removeClass('display-none');
    }
    url = urlAddCMPlan;
    $.ajax({
        url: url,
        type: 'post',
        dataType: 'json',
        data: data,
        success: function (data) {
            if(!data.status) {
                if (data.message_error.content) {
                    if (typeof id !== 'undefined') {
                        $('.cm-plan-' + id + ' div:last').after('<p class="word-break error-validate error-validate-cm-plan-'+ id +'" for="input-cm-plan-'+ id +'">' + data.message_error.content[0] + '</p>');
                    } else {
                        $('.div-cm-plan-hidden .cm-plan > textarea').after('<p class="word-break error-validate error-validate-cm-plan" for="input-cm-plan">' + data.message_error.content[0] + '</p>');
                    }
                }
            } else {
                $('.workorder-cm-plan').html(data.content);
                showOrHideButtonSubmitWorkorder(data.isCheckShowSubmit);
            }
        },
        error: function () {
            $('#modal-warning-notification .text-default').html(messageError);
            $('#modal-warning-notification').modal('show');
        },
        complete: function () {
            element.data('requestRunning', false);
            element.removeClass('display-none');
            if (typeof id !== 'undefined') {
                $('#loading-item-cmplan-' +id).addClass('display-none');
            } else {
                $('#loading-item-cmplan').addClass('display-none');
            }
        },
    });
});

$(document).on('click', '.delete-cm-plan', function (event) {
    $('#modal-delete-confirm-new').modal('show');
    displayTextDeleteConfirm(element);
    dataUrl = urlAddCMPlan;
    $(document).on('click', '#modal-delete-confirm-new .btn-ok', function () {
        $('#modal-delete-confirm-new').modal('hide');
        if ($(this).data('requestRunning')) {
            return;
        }
        $(this).data('requestRunning', true);
        isDelete = true;
        data = {
            _token: token,
            project_id: project_id,
            id: dataId,
            isDelete: isDelete
        };
        $.ajax({
            url: dataUrl,
            type: 'post',
            dataType: 'json',
            data: data,
            success: function (data) {
                if (data.status) {
                    showOrHideButtonSubmitWorkorder(data.isCheckShowSubmit);
                    $('.div-cm-plan-' + dataId).remove();
                } else {
                    $('#modal-warning-notification .text-default').html(messageError);
                    $('#modal-warning-notification').modal('show');
                }
            },
            error: function () {
                $('#modal-warning-notification .text-default').html(messageError);
                $('#modal-warning-notification').modal('show');
            },
            complete: function () {
                $('#modal-delete-confirm-new .btn-ok').data('requestRunning', false);
            },
        });
    });
});

$(document).on('keypress', '.input-cm-plan', function () {
    $(this).height('100%');
});

function string_to_slug(str, config) {
    str = str.replace(/^\s+|\s+$/g, ''); // trim
    str = str.toLowerCase();
    var configDefault = {
        'allow_slash': 1
    };
    config = $.extend(configDefault, config);
    // remove accents, swap ñ for n, etc
    // remove accents, swap ñ for n, etc
    var from = "àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷýđ·,:;";
    var to = "aaaaaaaaaaaaaaaaaeeeeeeeeeeeiiiiiooooooooooooooooouuuuuuuuuuuyyyyyd----";
    if (config.allow_slash == 1) {
        from += '\/';
        to += '\-';
    }
    for (var i = 0, l = from.length; i < l; i++) {
        str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
    }
    if (config.allow_slash == 1) {
        str = str.replace(/[^a-z0-9 -_]/g, '') // remove invalid chars
            .replace(/\s+/g, '-') // collapse whitespace and replace by -
            .replace(/-+/g, '-'); // collapse dashes
    } else {
        str = str.replace(/[^a-z0-9 -_\/]/g, '') // remove invalid chars
            .replace(/\s+/g, '-') // collapse whitespace and replace by -
            .replace(/-+/g, '-'); // collapse dashes
    }
    return str;
}

function slug_project_code(str) {
    str = str.replace(/^\s+|\s+$/g, ''); // trim
    // str = str.toLowerCase();
    // remove accents, swap ñ for n, etc
    // remove accents, swap ñ for n, etc
    var from = "àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷýđÀÁẠẢÃÂẦẤẬẨẪĂẰẮẶẲẴÈÉẸẺẼÊỀẾỆỂỄÌÍỊỈĨÒÓỌỎÕÔỒỐỘỔỖƠỜỚỢỞỠÙÚỤỦŨƯỪỨỰỬỮỲÝỴỶÝĐ·_,:;";
    var to = "aaaaaaaaaaaaaaaaaeeeeeeeeeeeiiiiiooooooooooooooooouuuuuuuuuuuyyyyydAAAAAAAAAAAAAAAAAEEEEEEEEEEEIIIIIOOOOOOOOOOOOOOOOOUUUUUUUUUUUYYYYYD-----";
    for (var i = 0, l = from.length; i < l; i++) {
        str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
    }
    str = str.replace(/[^a-zA-Z0-9 -\/]/g, '') // remove invalid chars
        .replace(/\s+/g, '-') // collapse whitespace and replace by -
        .replace(/-+/g, '-'); // collapse dashes
    return str;
}



$(document).on('click', '.delete-quality', function (event) {
    $('#modal-delete-confirm-new').modal('show');
    dataId = $(this).data('id');
    dataUrl = urlAddQuality;
    $(document).on('click', '#modal-delete-confirm-new .btn-ok', function () {
        $('#modal-delete-confirm-new').modal('hide');
        if ($(this).data('requestRunning')) {
            return;
        }
        $(this).data('requestRunning', true);
        isDelete = true;
        data = {
            _token: token,
            project_id: project_id,
            id: dataId,
            isDelete: isDelete
        };
        $.ajax({
            url: dataUrl,
            type: 'post',
            dataType: 'json',
            data: data,
            success: function (data) {
                if (data.status) {
                    $('.workorder-quality').html(data.content);
                    showOrHideButtonSubmitWorkorder(data.isCheckShowSubmit);
                } else {
                    $('#modal-warning-notification .text-default').html(messageError);
                    $('#modal-warning-notification').modal('show');
                }
            },
            error: function () {
                $('#modal-warning-notification .text-default').html(messageError);
                $('#modal-warning-notification').modal('show');
            },
            complete: function () {
                $('#modal-delete-confirm-new .btn-ok').data('requestRunning', false);
            },
        });
    });
});


/* slove critical dependencies */
$(document).on('click', '.add-critical-dependencies', function (event) {
    if (workorderApproved.critical_dependencies) {
        showFormAddWorkorderApproved('critical-dependencies', TYPE_CRITICAL_DEPENDENCIES);
    } else {
        showFormAddWorkorderNotApproved('critical-dependencies', TYPE_CRITICAL_DEPENDENCIES);
    }
});

$(document).on('click', '.remove-critical-dependencies', function (event) {
    if (workorderApproved.critical_dependencies) {
        removeFormAddWorkorderApproved('critical-dependencies', this, TYPE_CRITICAL_DEPENDENCIES);
    } else {
        removeFormAddWorkorderNotApproved('critical-dependencies', TYPE_CRITICAL_DEPENDENCIES);
    }
});

$(document).on('click', '.delete-critical-dependencies', function (event) {
    if (workorderApproved.critical_dependencies) {
        deleteWorkorderApproved('critical-dependencies', this, urlAddCriticalDepenrencies, TYPE_CRITICAL_DEPENDENCIES);
    } else {
        deleteWorkorderNotApproved('critical-dependencies', this, urlAddCriticalDepenrencies, TYPE_CRITICAL_DEPENDENCIES);
    }
});

$(document).on('click', '.save-critical-dependencies', function () {
    if (workorderApproved.critical_dependencies) {
        saveWorkorderApproved('critical-dependencies', this, urlAddCriticalDepenrencies, TYPE_CRITICAL_DEPENDENCIES);
    } else {
        saveWorkorderNotApproved('critical-dependencies', this, urlAddCriticalDepenrencies, TYPE_CRITICAL_DEPENDENCIES);
    }
});

$(document).on('click', '.edit-critical-dependencies', function () {
    if (workorderApproved.critical_dependencies) {
        editWorkorderApproved('critical-dependencies', this, TYPE_CRITICAL_DEPENDENCIES);
    } else {
        editWorkorderNotApproved('critical-dependencies', this, TYPE_CRITICAL_DEPENDENCIES);
    }
});

$(document).on('click', '.refresh-critical-dependencies', function () {
    if (workorderApproved.critical_dependencies) {
        refreshWorkorderApproved('critical-dependencies', this, TYPE_CRITICAL_DEPENDENCIES);
    } else {
        refreshWorkorderNotApproved('critical-dependencies', this, TYPE_CRITICAL_DEPENDENCIES);
    }
});

$(document).on('click', '.add-new-critical-dependencies', function () {
    if (workorderApproved.critical_dependencies) {
        saveDraftWorkorderApproved('critical-dependencies', this, urlAddCriticalDepenrencies, TYPE_CRITICAL_DEPENDENCIES);
    } else {
        submitWorkorderNotApproved('critical-dependencies', this, urlAddCriticalDepenrencies, TYPE_CRITICAL_DEPENDENCIES);
    }
});


/* slove assumption and constrains*/
$(document).on('click', '.add-assumption-constrains', function (event) {
    if (workorderApproved.critical_dependencies) {
        showFormAddWorkorderApproved('assumption-constrains', TYPE_ASSUMPTION_CONSTRAIN);
    } else {
        showFormAddWorkorderNotApproved('assumption-constrains', TYPE_ASSUMPTION_CONSTRAIN);
    }
});

$(document).on('click', '.remove-assumption-constrains', function (event) {
    if (workorderApproved.critical_dependencies) {
        removeFormAddWorkorderApproved('assumption-constrains', this, TYPE_ASSUMPTION_CONSTRAIN);
    } else {
        removeFormAddWorkorderNotApproved('assumption-constrains', TYPE_ASSUMPTION_CONSTRAIN);
    }
});

$(document).on('click', '.delete-assumption-constrains', function (event) {
    if (workorderApproved.critical_dependencies) {
        deleteWorkorderApproved('assumption-constrains', this, urlAddAssumptionConstrain, TYPE_ASSUMPTION_CONSTRAIN);
    } else {
        deleteWorkorderNotApproved('assumption-constrains', this, urlAddAssumptionConstrain, TYPE_ASSUMPTION_CONSTRAIN);
    }
});

$(document).on('click', '.save-assumption-constrains', function () {
    if (workorderApproved.critical_dependencies) {
        saveWorkorderApproved('assumption-constrains', this, urlAddAssumptionConstrain, TYPE_ASSUMPTION_CONSTRAIN);
    } else {
        saveWorkorderNotApproved('assumption-constrains', this, urlAddAssumptionConstrain, TYPE_ASSUMPTION_CONSTRAIN);
    }
});

$(document).on('click', '.edit-assumption-constrains', function () {
    if (workorderApproved.critical_dependencies) {
        editWorkorderApproved('assumption-constrains', this, TYPE_ASSUMPTION_CONSTRAIN);
    } else {
        editWorkorderNotApproved('assumption-constrains', this, TYPE_ASSUMPTION_CONSTRAIN);
    }
});


$(document).on('click', '.refresh-assumption-constrains', function () {
    if (workorderApproved.critical_dependencies) {
        refreshWorkorderApproved('assumption-constrains', this, TYPE_ASSUMPTION_CONSTRAIN);
    } else {
        refreshWorkorderNotApproved('assumption-constrains', this, TYPE_ASSUMPTION_CONSTRAIN);
    }
});

$(document).on('click', '.add-new-assumption-constrains', function () {
    if (workorderApproved.critical_dependencies) {
        saveDraftWorkorderApproved('assumption-constrains', this, urlAddAssumptionConstrain, TYPE_ASSUMPTION_CONSTRAIN);
    } else {
        submitWorkorderNotApproved('assumption-constrains', this, urlAddAssumptionConstrain, TYPE_ASSUMPTION_CONSTRAIN);
    }
});

/*sync from team allocation*/

$(document).on('click', '#sync_project_allocation', function () {
    var projectId = $(this).data('id');
    $(this).prop('disabled', true);
    if (projectId) {
        $.ajax({
            url: urlSyncTeamAllocation,
            type: 'post',
            data: {projectId: projectId},
            dataType: 'text',
            success: function (data) {
                window.location.reload();
            },
            error: function () {
                alert('ajax fail to fetch data');
            }
        });
    }
});

/*sync report communication example*/

$(document).on('click', '.sync_report_example', function () {
    var projectId = $(this).data('id');
    $(this).prop('disabled', true);
    if (projectId) {
        $.ajax({
            url: urlSyncReportExample,
            type: 'post',
            data: {projectId: projectId},
            dataType: 'text',
            success: function (data) {
                window.location.reload();
            },
            error: function () {
                alert('ajax fail to fetch data');
            }
        });
    }
});

/* slove risk*/
$(document).on('click', '.add-risk', function () {
    var $curElem = $(this);
    var projectId = $curElem.data('project-id');
    $('.add-risk').prop('disabled', true);
    $curElem.find('i.fa-plus').removeClass('fa-plus').addClass('fa-refresh').addClass('fa-spin');
    $.ajax({
        url: urlEditRisk,
        type: 'get',
        data: {projectId: projectId},
        dataType: 'text',
        success: function (data) {
            BootstrapDialog.show({
                title: modalRiskTitle,
                cssClass: 'risk-dialog',
                message: $('<div></div>').html(data),
                closable: false,
                buttons: [{
                    id: 'btn-close',
                    icon: 'fa fa-close',
                    label: 'Close',
                    cssClass: 'btn-primary',
                    autospin: false,
                    action: function(dialogRef){
                        dialogRef.close();
                    }
                },{
                    id: 'btn-save',
                    icon: 'glyphicon glyphicon-check',
                    label: 'Save',
                    cssClass: 'btn-primary',
                    autospin: false,
                    action: function(dialogRef){
                        $('.form-riks-detail').submit();
                    }
                }]
            });
        },
        error: function () {
            alert('ajax fail to fetch data');
        },
        complete: function () {
            $curElem.find('i.fa-refresh').addClass('fa-plus').removeClass('fa-refresh').removeClass('fa-spin');
            $('.add-risk').prop('disabled', false);
        }
    });
});

/* slove issue*/
$(document).on('click', '.add-issue', function () {
    var $curElem = $(this);
    var $projectId = $curElem.data('project-id');
    $('.add-issue').prop('disabled', true);
    $curElem.find('i.fa-plus').removeClass('fa-plus').addClass('fa-refresh').addClass('fa-spin');
    $.ajax({
        url: urlEditIssue,
        type: 'get',
        data: {projectId: projectId},
        dataType: 'text',
        success: function (data) {
            BootstrapDialog.show({
                title: modalIssueTitle,
                cssClass: 'task-dialog',
                message: $('<div></div>').html(data),
                closable: false,
                buttons: [{
                    id: 'btn-close',
                    icon: 'fa fa-close',
                    label: 'Close',
                    cssClass: 'btn-primary',
                    autospin: false,
                    action: function(dialogRef){
                        dialogRef.close();
                    }
                },{
                    id: 'btn-save',
                    icon: 'glyphicon glyphicon-check',
                    label: 'Save',
                    cssClass: 'btn-primary',
                    autospin: false,
                    action: function(dialogRef){
                        $('.form-issue-detail').submit();
                    }
                }]
            });
        },
        error: function () {
            alert('ajax fail to fetch data');
        },
        complete: function () {
            $curElem.find('i.fa-refresh').addClass('fa-plus').removeClass('fa-refresh').removeClass('fa-spin');
            $('.add-issue').prop('disabled', false);
        }
    });
});

$(document).on('click', '.remove-risk', function (event) {
    if (workorderApproved.risk) {
        removeFormAddWorkorderApproved('risk', this, TYPE_RISK);
    } else {
        removeFormAddWorkorderNotApproved('risk', TYPE_RISK);
    }
});

$(document).on('click', '.delete-risk', function (event) {
    if (workorderApproved.risk) {
        deleteWorkorderApproved('risk', this, urlAddRisk, TYPE_RISK);
    } else {
        deleteWorkorderNotApproved('risk', this, urlAddRisk, TYPE_RISK);
    }
});

$(document).on('click', '.delete-issue', function() {
    $('#modal-delete-confirm-issue').modal('show');
    var issueId = $(this).attr('data-id');
    $('#modal-delete-confirm-issue').find('.btn-submit').attr('data-id', issueId);
});
$(document).on('click', '#modal-delete-confirm-issue .btn-submit', function () {
    $('#modal-delete-confirm-issue').modal('hide');
    var issueId = $(this).attr('data-id');
    $.ajax({
        url: urlDeleteIssue,
        type: 'post',
        data: {
            issueId: issueId
        },
        success: function (data) {
            $("tr[data-id='" + issueId + "']").remove();
        },
        error: function () {
            alert('ajax fail to fetch data');
        },
    });
});

$(document).on('click', '.save-risk', function () {

});
var iconClassEdit = 'fa-pencil-square-o';
var iconClassView = 'fa-eye';
$(document).on('click', '.edit-risk', function () {
    var $curElem = $(this);
    var riskId = $curElem.data('id');
    var editFlag = true;
    if ($curElem.hasClass(iconClassEdit)) {
        $curElem.removeClass(iconClassEdit);
    } else {
        $curElem.removeClass(iconClassView);
        editFlag = false;
    }
    $curElem.find('i.fa-refresh').removeClass('hidden');
    var redirectUrl = $(this).data('redirect');
    if (typeof redirectUrl === typeof undefined && redirectUrl === false) {
        redirectUrl = '';
    }
    $.ajax({
        url: urlEditRisk,
        type: 'get',
        data: {riskId: riskId, redirectUrl: redirectUrl},
        dataType: 'text',
        success: function (data) {
            var buttons = [];
            buttons.push({
                id: 'btn-close',
                icon: 'fa fa-close',
                label: RKVarPassGlobal.textClose,
                cssClass: 'btn-primary',
                autospin: false,
                action: function(dialogRef){
                    dialogRef.close();
                }
            });
            if ($curElem.data('view') == 0) {
                buttons.push({
                    id: 'btn-save',
                    icon: 'glyphicon glyphicon-check',
                    label: RKVarPassGlobal.textSave,
                    cssClass: 'btn-primary',
                    autospin: false,
                    action: function(dialogRef){
                        $('.form-riks-detail').submit();
                    }
                });
            }
            BootstrapDialog.show({
                title: modalRiskTitle,
                cssClass: 'risk-dialog',
                message: $('<div></div>').html(data),
                closable: false,
                buttons: buttons
            });
        },
        error: function () {
            alert('ajax fail to fetch data');
        },
        complete: function () {
            if (editFlag) {
                $curElem.addClass(iconClassEdit);
            } else {
                $curElem.addClass(iconClassView);
            }
            $curElem.find('i.fa-refresh').addClass('hidden');
        }
    });
});

$(document).on('click', '.edit-nc', function () {
    var $curElem = $(this);
    var ncId = $curElem.data('id');
    var editFlag = true;
    if ($curElem.hasClass(iconClassEdit)) {
        $curElem.removeClass(iconClassEdit);
    } else {
        $curElem.removeClass(iconClassView);
        editFlag = false;
    }
    $curElem.find('i.fa-refresh').removeClass('hidden');
    var redirectUrl = $(this).data('redirect');
    if (typeof redirectUrl === typeof undefined && redirectUrl === false) {
        redirectUrl = '';
    }
    $.ajax({
        url: urlEditNc,
        type: 'get',
        data: {ncId: ncId, redirectUrl: redirectUrl},
        dataType: 'text',
        success: function (data) {
            var buttons = [];
            buttons.push({
                id: 'btn-close',
                icon: 'fa fa-close',
                label: RKVarPassGlobal.textClose,
                cssClass: 'btn-primary',
                autospin: false,
                action: function(dialogRef){
                    dialogRef.close();
                }
            });
            if ($curElem.data('view') == 0) {
                buttons.push({
                    id: 'btn-save',
                    icon: 'glyphicon glyphicon-check',
                    label: RKVarPassGlobal.textSave,
                    cssClass: 'btn-primary',
                    autospin: false,
                    action: function(dialogRef){
                        $('.form-nc-detail').submit();
                    }
                });
            }
            BootstrapDialog.show({
                title: modalNcTitle,
                cssClass: 'task-dialog',
                message: $('<div></div>').html(data),
                closable: false,
                buttons: buttons
            });
        },
        error: function () {
            alert('ajax fail to fetch data');
        },
        complete: function () {
            if (editFlag) {
                $curElem.addClass(iconClassEdit);
            } else {
                $curElem.addClass(iconClassView);
            }
            $curElem.find('i.fa-refresh').addClass('hidden');
        }
    });
});

$(document).on('click', '.refresh-risk', function () {
    if (workorderApproved.risk) {
        refreshWorkorderApproved('risk', this, TYPE_RISK);
    } else {
        refreshWorkorderNotApproved('risk', this, TYPE_RISK);
    }
});

$(document).on('click', '.add-new-risk', function () {
    if (workorderApproved.risk) {
        saveDraftWorkorderApproved('risk', this, urlAddRisk, TYPE_RISK);
    } else {
        submitWorkorderNotApproved('risk', this, urlAddRisk, TYPE_RISK);
    }
});

/* slove stage and milestone*/
$(document).on('click', '.add-stage-and-milestone', function (event) {
    if (workorderApproved.stage_and_milestone) {
        showFormAddWorkorderApproved('stage-and-milestone', TYPE_STAGE_AND_MILESTONE);
    } else {
        showFormAddWorkorderNotApproved('stage-and-milestone', TYPE_STAGE_AND_MILESTONE);
    }
});

$(document).on('click', '.remove-stage-and-milestone', function (event) {
    if (workorderApproved.stage_and_milestone) {
        removeFormAddWorkorderApproved('stage-and-milestone', this, TYPE_STAGE_AND_MILESTONE);
    } else {
        removeFormAddWorkorderNotApproved('stage-and-milestone', TYPE_STAGE_AND_MILESTONE);
    }
});

$(document).on('click', '.delete-stage-and-milestone', function (event) {
    if (workorderApproved.stage_and_milestone) {
        deleteWorkorderApproved('stage-and-milestone', this, urlAddStageAndMilestone, TYPE_STAGE_AND_MILESTONE);
    } else {
        deleteWorkorderNotApproved('stage-and-milestone', this, urlAddStageAndMilestone, TYPE_STAGE_AND_MILESTONE);
    }
});

$(document).on('click', '.save-stage-and-milestone', function () {
    if (workorderApproved.stage_and_milestone) {
        saveWorkorderApproved('stage-and-milestone', this, urlAddStageAndMilestone, TYPE_STAGE_AND_MILESTONE);
    } else {
        saveWorkorderNotApproved('stage-and-milestone', this, urlAddStageAndMilestone, TYPE_STAGE_AND_MILESTONE);
    }
});

$(document).on('click', '.edit-stage-and-milestone', function (e) {
    e.preventDefault();
    id = $(this).data('id');
    $('.tr-stage-' + id + ' .select2').addClass('display-block');
    if (workorderApproved.stage_and_milestone) {
        editWorkorderApproved('stage-and-milestone', this, TYPE_STAGE_AND_MILESTONE);
    } else {
        editWorkorderNotApproved('stage-and-milestone', this, TYPE_STAGE_AND_MILESTONE);
    }
});


$(document).on('click', '.refresh-stage-and-milestone', function (e) {
    e.preventDefault();
    id = $(this).data('id');
    $('.tr-stage-' + id + ' .select2').removeClass('display-block');
    if (workorderApproved.stage_and_milestone) {
        refreshWorkorderApproved('stage-and-milestone', this, TYPE_STAGE_AND_MILESTONE);
    } else {
        refreshWorkorderNotApproved('stage-and-milestone', this, TYPE_STAGE_AND_MILESTONE);
    }
});

$(document).on('click', '.add-new-stage-and-milestone', function () {
    if (workorderApproved.stage_and_milestone) {
        saveDraftWorkorderApproved('stage-and-milestone', this, urlAddStageAndMilestone, TYPE_STAGE_AND_MILESTONE);
    } else {
        submitWorkorderNotApproved('stage-and-milestone', this, urlAddStageAndMilestone, TYPE_STAGE_AND_MILESTONE);
    }
});

/* slove member communication */
$(document).on('click', '.add-member_communication', function (event) {
    if (workorderApproved.member_communication) {
        showFormAddWorkorderApproved('member_communication', TYPE_MEMBER_COMMUNICATION);
    } else {
        showFormAddWorkorderNotApproved('member_communication', TYPE_MEMBER_COMMUNICATION);
    }
});

$(document).on('click', '.remove-member_communication', function (event) {
    if (workorderApproved.member_communication) {
        removeFormAddWorkorderApproved('member_communication', this, TYPE_MEMBER_COMMUNICATION);
    } else {
        removeFormAddWorkorderNotApproved('member_communication', TYPE_MEMBER_COMMUNICATION);
    }
});

$(document).on('click', '.delete-member_communication', function (event) {
    if (workorderApproved.member_communication) {
        deleteWorkorderApproved('member_communication', this, urlAddMemberCommunication, TYPE_MEMBER_COMMUNICATION);
    } else {
        deleteWorkorderNotApproved('member_communication', this, urlAddMemberCommunication, TYPE_MEMBER_COMMUNICATION);
    }
});

$(document).on('click', '.save-member_communication', function () {
    if (workorderApproved.member_communication) {
        saveWorkorderApproved('member_communication', this, urlAddMemberCommunication, TYPE_MEMBER_COMMUNICATION);
    } else {
        saveWorkorderNotApproved('member_communication', this, urlAddMemberCommunication, TYPE_MEMBER_COMMUNICATION);
    }
});

$(document).on('click', '.edit-member_communication', function () {
    id = $(this).data('id');
    $('.tr-member_communication-' + id + ' select').select2().on('select2:close', function(evt) { tabToChange (evt)});
    if (workorderApproved.member_communication) {
        editWorkorderApproved('member_communication', this, TYPE_MEMBER_COMMUNICATION);
    } else {
        editWorkorderNotApproved('member_communication', this, TYPE_MEMBER_COMMUNICATION);
    }
});


$(document).on('click', '.refresh-member_communication', function () {
    id = $(this).data('id');
    $('.tr-member_communication-' + id + ' .select2').addClass('display-none');
    if (workorderApproved.member_communication) {
        refreshWorkorderApproved('member_communication', this, TYPE_MEMBER_COMMUNICATION);
    } else {
        refreshWorkorderNotApproved('member_communication', this, TYPE_MEMBER_COMMUNICATION);
    }
});

$(document).on('click', '.add-new-member_communication', function () {
    if (workorderApproved.member_communication) {
        saveDraftWorkorderApproved('member_communication', this, urlAddMemberCommunication, TYPE_MEMBER_COMMUNICATION);
    } else {
        submitWorkorderNotApproved('member_communication', this, urlAddMemberCommunication, TYPE_MEMBER_COMMUNICATION);
    }
});

/* slove customer communication */
$(document).on('click', '.add-customer_communication', function (event) {
    if (workorderApproved.customer_communication) {
        showFormAddWorkorderApproved('customer_communication', TYPE_CUSTOMER_COMMUNICATION);
    } else {
        showFormAddWorkorderNotApproved('customer_communication', TYPE_CUSTOMER_COMMUNICATION);
    }
});

$(document).on('click', '.remove-customer_communication', function (event) {
    if (workorderApproved.customer_communication) {
        removeFormAddWorkorderApproved('customer_communication', this, TYPE_CUSTOMER_COMMUNICATION);
    } else {
        removeFormAddWorkorderNotApproved('customer_communication', TYPE_CUSTOMER_COMMUNICATION);
    }
});

$(document).on('click', '.delete-customer_communication', function (event) {
    if (workorderApproved.customer_communication) {
        deleteWorkorderApproved('customer_communication', this, urlAddCustomerCommunication, TYPE_CUSTOMER_COMMUNICATION);
    } else {
        deleteWorkorderNotApproved('customer_communication', this, urlAddCustomerCommunication, TYPE_CUSTOMER_COMMUNICATION);
    }
});

$(document).on('click', '.save-customer_communication', function () {
    if (workorderApproved.customer_communication) {
        saveWorkorderApproved('customer_communication', this, urlAddCustomerCommunication, TYPE_CUSTOMER_COMMUNICATION);
    } else {
        saveWorkorderNotApproved('customer_communication', this, urlAddCustomerCommunication, TYPE_CUSTOMER_COMMUNICATION);
    }
});

$(document).on('click', '.edit-customer_communication', function () {
    id = $(this).data('id');
    $('.tr-customer_communication-' + id + ' select').select2().on('select2:close', function(evt) { tabToChange (evt)});
    if (workorderApproved.customer_communication) {
        editWorkorderApproved('customer_communication', this, TYPE_CUSTOMER_COMMUNICATION);
    } else {
        editWorkorderNotApproved('customer_communication', this, TYPE_CUSTOMER_COMMUNICATION);
    }
});


$(document).on('click', '.refresh-customer_communication', function () {
    id = $(this).data('id');
    $('.tr-customer_communication-' + id + ' .select2').addClass('display-none');
    if (workorderApproved.customer_communication) {
        refreshWorkorderApproved('customer_communication', this, TYPE_CUSTOMER_COMMUNICATION);
    } else {
        refreshWorkorderNotApproved('customer_communication', this, TYPE_CUSTOMER_COMMUNICATION);
    }
});

$(document).on('click', '.add-new-customer_communication', function () {
    if (workorderApproved.customer_communication) {
        saveDraftWorkorderApproved('customer_communication', this, urlAddCustomerCommunication, TYPE_CUSTOMER_COMMUNICATION);
    } else {
        submitWorkorderNotApproved('customer_communication', this, urlAddCustomerCommunication, TYPE_CUSTOMER_COMMUNICATION);
    }
});

/* slove training */
$(document).on('click', '.add-training', function (event) {
    if (workorderApproved.training) {
        showFormAddWorkorderApproved('training', TYPE_TRAINING);
    } else {
        showFormAddWorkorderNotApproved('training', TYPE_TRAINING);
    }
});

$(document).on('click', '.remove-training', function (event) {
    if (workorderApproved.training) {
        removeFormAddWorkorderApproved('training', this, TYPE_TRAINING);
    } else {
        removeFormAddWorkorderNotApproved('training', TYPE_TRAINING);
    }
});

$(document).on('click', '.delete-training', function (event) {
    if (workorderApproved.training) {
        deleteWorkorderApproved('training', this, urlAddTraining, TYPE_TRAINING);
    } else {
        deleteWorkorderNotApproved('training', this, urlAddTraining, TYPE_TRAINING);
    }
});

$(document).on('click', '.save-training', function () {
    if (workorderApproved.training) {
        saveWorkorderApproved('training', this, urlAddTraining, TYPE_TRAINING);
    } else {
        saveWorkorderNotApproved('training', this, urlAddTraining, TYPE_TRAINING);
    }
});

$(document).on('click', '.edit-training', function () {
    id = $(this).data('id');
    $('.tr-training-' + id + ' select').select2().on('select2:close', function(evt) { tabToChange (evt)});
    if (workorderApproved.training) {
        editWorkorderApproved('training', this, TYPE_TRAINING);
    } else {
        editWorkorderNotApproved('training', this, TYPE_TRAINING);
    }
});


$(document).on('click', '.refresh-training', function () {
    id = $(this).data('id');
    $('.tr-training-' + id + ' .select2').addClass('display-none');
    if (workorderApproved.training) {
        refreshWorkorderApproved('training', this, TYPE_TRAINING);
    } else {
        refreshWorkorderNotApproved('training', this, TYPE_TRAINING);
    }
});

$(document).on('click', '.add-new-training', function () {
    if (workorderApproved.training) {
        saveDraftWorkorderApproved('training', this, urlAddTraining, TYPE_TRAINING);
    } else {
        submitWorkorderNotApproved('training', this, urlAddTraining, TYPE_TRAINING);
    }
});

/* slove communication meeting */
$(document).on('click', '.add-communication_meeting', function (event) {
    if (workorderApproved.communication_meeting) {
        showFormAddWorkorderApproved('communication_meeting', TYPE_COMMUNICATION_MEETING);
    } else {
        showFormAddWorkorderNotApproved('communication_meeting', TYPE_COMMUNICATION_MEETING);
    }
});

$(document).on('click', '.remove-communication_meeting', function (event) {
    if (workorderApproved.communication_meeting) {
        removeFormAddWorkorderApproved('communication_meeting', this, TYPE_COMMUNICATION_MEETING);
    } else {
        removeFormAddWorkorderNotApproved('communication_meeting', TYPE_COMMUNICATION_MEETING);
    }
});

$(document).on('click', '.delete-communication_meeting', function (event) {
    if (workorderApproved.communication_meeting) {
        deleteWorkorderApproved('communication_meeting', this, urlAddProjCommunication, TYPE_COMMUNICATION_MEETING);
    } else {
        deleteWorkorderNotApproved('communication_meeting', this, urlAddProjCommunication, TYPE_COMMUNICATION_MEETING);
    }
});

$(document).on('click', '.save-communication_meeting', function () {
    if (workorderApproved.communication_meeting) {
        saveWorkorderApproved('communication_meeting', this, urlAddProjCommunication, TYPE_COMMUNICATION_MEETING);
    } else {
        saveWorkorderNotApproved('communication_meeting', this, urlAddProjCommunication, TYPE_COMMUNICATION_MEETING);
    }
});

$(document).on('click', '.edit-communication_meeting', function () {
    id = $(this).data('id');
    if (workorderApproved.communication_meeting) {
        editWorkorderApproved('communication_meeting', this, TYPE_COMMUNICATION_MEETING);
    } else {
        editWorkorderNotApproved('communication_meeting', this, TYPE_COMMUNICATION_MEETING);
    }
});


$(document).on('click', '.refresh-communication_meeting', function () {
    id = $(this).data('id');
    if (workorderApproved.communication_meeting) {
        refreshWorkorderApproved('communication_meeting', this, TYPE_COMMUNICATION_MEETING);
    } else {
        refreshWorkorderNotApproved('communication_meeting', this, TYPE_COMMUNICATION_MEETING);
    }
});

$(document).on('click', '.add-new-communication_meeting', function () {
    if (workorderApproved.communication_meeting) {
        saveDraftWorkorderApproved('communication_meeting', this, urlAddProjCommunication, TYPE_COMMUNICATION_MEETING);
    } else {
        submitWorkorderNotApproved('communication_meeting', this, urlAddProjCommunication, TYPE_COMMUNICATION_MEETING);
    }
});

/* slove communication report */
$(document).on('click', '.add-communication_report', function (event) {
    if (workorderApproved.communication_report) {
        showFormAddWorkorderApproved('communication_report', TYPE_COMMUNICATION_REPORT);
    } else {
        showFormAddWorkorderNotApproved('communication_report', TYPE_COMMUNICATION_REPORT);
    }
});

$(document).on('click', '.remove-communication_report', function (event) {
    if (workorderApproved.communication_report) {
        removeFormAddWorkorderApproved('communication_report', this, TYPE_COMMUNICATION_REPORT);
    } else {
        removeFormAddWorkorderNotApproved('communication_report', TYPE_COMMUNICATION_REPORT);
    }
});

$(document).on('click', '.delete-communication_report', function (event) {
    if (workorderApproved.communication_report) {
        deleteWorkorderApproved('communication_report', this, urlAddProjCommunication, TYPE_COMMUNICATION_REPORT);
    } else {
        deleteWorkorderNotApproved('communication_report', this, urlAddProjCommunication, TYPE_COMMUNICATION_REPORT);
    }
});

$(document).on('click', '.save-communication_report', function () {
    if (workorderApproved.communication_report) {
        saveWorkorderApproved('communication_report', this, urlAddProjCommunication, TYPE_COMMUNICATION_REPORT);
    } else {
        saveWorkorderNotApproved('communication_report', this, urlAddProjCommunication, TYPE_COMMUNICATION_REPORT);
    }
});

$(document).on('click', '.edit-communication_report', function () {
    id = $(this).data('id');
    if (workorderApproved.communication_report) {
        editWorkorderApproved('communication_report', this, TYPE_COMMUNICATION_REPORT);
    } else {
        editWorkorderNotApproved('communication_report', this, TYPE_COMMUNICATION_REPORT);
    }
});


$(document).on('click', '.refresh-communication_report', function () {
    id = $(this).data('id');
    if (workorderApproved.communication_report) {
        refreshWorkorderApproved('communication_report', this, TYPE_COMMUNICATION_REPORT);
    } else {
        refreshWorkorderNotApproved('communication_report', this, TYPE_COMMUNICATION_REPORT);
    }
});

$(document).on('click', '.add-new-communication_report', function () {
    if (workorderApproved.communication_report) {
        saveDraftWorkorderApproved('communication_report', this, urlAddProjCommunication, TYPE_COMMUNICATION_REPORT);
    } else {
        submitWorkorderNotApproved('communication_report', this, urlAddProjCommunication, TYPE_COMMUNICATION_REPORT);
    }
});

/* slove communication other */
$(document).on('click', '.add-communication_other', function (event) {
    if (workorderApproved.communication_other) {
        showFormAddWorkorderApproved('communication_other', TYPE_COMMUNICATION_OTHER);
    } else {
        showFormAddWorkorderNotApproved('communication_other', TYPE_COMMUNICATION_OTHER);
    }
});

$(document).on('click', '.remove-communication_other', function (event) {
    if (workorderApproved.communication_other) {
        removeFormAddWorkorderApproved('communication_other', this, TYPE_COMMUNICATION_OTHER);
    } else {
        removeFormAddWorkorderNotApproved('communication_other', TYPE_COMMUNICATION_OTHER);
    }
});

$(document).on('click', '.delete-communication_other', function (event) {
    if (workorderApproved.communication_other) {
        deleteWorkorderApproved('communication_other', this, urlAddProjCommunication, TYPE_COMMUNICATION_OTHER);
    } else {
        deleteWorkorderNotApproved('communication_other', this, urlAddProjCommunication, TYPE_COMMUNICATION_OTHER);
    }
});

$(document).on('click', '.save-communication_other', function () {
    if (workorderApproved.communication_other) {
        saveWorkorderApproved('communication_other', this, urlAddProjCommunication, TYPE_COMMUNICATION_OTHER);
    } else {
        saveWorkorderNotApproved('communication_other', this, urlAddProjCommunication, TYPE_COMMUNICATION_OTHER);
    }
});

$(document).on('click', '.edit-communication_other', function () {
    id = $(this).data('id');
    if (workorderApproved.communication_other) {
        editWorkorderApproved('communication_other', this, TYPE_COMMUNICATION_OTHER);
    } else {
        editWorkorderNotApproved('communication_other', this, TYPE_COMMUNICATION_OTHER);
    }
});

$(document).on('click', '.refresh-communication_other', function () {
    id = $(this).data('id');
    if (workorderApproved.communication_other) {
        refreshWorkorderApproved('communication_other', this, TYPE_COMMUNICATION_OTHER);
    } else {
        refreshWorkorderNotApproved('communication_other', this, TYPE_COMMUNICATION_OTHER);
    }
});

$(document).on('click', '.add-new-communication_other', function () {
    if (workorderApproved.communication_other) {
        saveDraftWorkorderApproved('communication_other', this, urlAddProjCommunication, TYPE_COMMUNICATION_OTHER);
    } else {
        submitWorkorderNotApproved('communication_other', this, urlAddProjCommunication, TYPE_COMMUNICATION_OTHER);
    }
});

/* slove external interface */
$(document).on('click', '.add-external-interface', function (event) {
    if (workorderApproved.external_interface) {
        showFormAddWorkorderApproved('external-interface', TYPE_EXTERNAL_INTERFACE);
    } else {
        showFormAddWorkorderNotApproved('external-interface', TYPE_EXTERNAL_INTERFACE);
    }
});

$(document).on('click', '.remove-external-interface', function (event) {
    if (workorderApproved.external_interface) {
        removeFormAddWorkorderApproved('external-interface', this, TYPE_EXTERNAL_INTERFACE);
    } else {
        removeFormAddWorkorderNotApproved('external-interface', TYPE_EXTERNAL_INTERFACE);
    }
});

$(document).on('click', '.delete-external-interface', function (event) {
    if (workorderApproved.external_interface) {
        deleteWorkorderApproved('external-interface', this, urlAddExternalInterface, TYPE_EXTERNAL_INTERFACE);
    } else {
        deleteWorkorderNotApproved('external-interface', this, urlAddExternalInterface, TYPE_EXTERNAL_INTERFACE);
    }
});

$(document).on('click', '.save-external-interface', function () {
    if (workorderApproved.external_interface) {
        saveWorkorderApproved('external-interface', this, urlAddExternalInterface, TYPE_EXTERNAL_INTERFACE);
    } else {
        saveWorkorderNotApproved('external-interface', this, urlAddExternalInterface, TYPE_EXTERNAL_INTERFACE);
    }
});

$(document).on('click', '.edit-external-interface', function () {
    if (workorderApproved.external_interface) {
        editWorkorderApproved('external-interface', this, TYPE_EXTERNAL_INTERFACE);
    } else {
        editWorkorderNotApproved('external-interface', this, TYPE_EXTERNAL_INTERFACE);
    }
});

$(document).on('click', '.refresh-external-interface', function () {
    if (workorderApproved.external_interface) {
        refreshWorkorderApproved('external-interface', this, TYPE_EXTERNAL_INTERFACE);
    } else {
        refreshWorkorderNotApproved('external-interface', this, TYPE_EXTERNAL_INTERFACE);
    }
});

$(document).on('click', '.add-new-external-interface', function () {
    if (workorderApproved.external_interface) {
        saveDraftWorkorderApproved('external-interface', this, urlAddExternalInterface, TYPE_EXTERNAL_INTERFACE);
    } else {
        submitWorkorderNotApproved('external-interface', this, urlAddExternalInterface, TYPE_EXTERNAL_INTERFACE);
    }
});

/* slove tool and infrastructure */
$(document).on('click', '.add-tool-and-infrastructure', function (event) {
    if (workorderApproved.tool_and_infrastructure) {
        showFormAddWorkorderApproved('tool-and-infrastructure', TYPE_TOOL_AND_INFRASTRUCTURE);
    } else {
        showFormAddWorkorderNotApproved('tool-and-infrastructure', TYPE_TOOL_AND_INFRASTRUCTURE);
    }
});

$(document).on('click', '.remove-tool-and-infrastructure', function (event) {
    if (workorderApproved.tool_and_infrastructure) {
        removeFormAddWorkorderApproved('tool-and-infrastructure', this, TYPE_TOOL_AND_INFRASTRUCTURE);
    } else {
        removeFormAddWorkorderNotApproved('tool-and-infrastructure', TYPE_TOOL_AND_INFRASTRUCTURE);
    }
});

$(document).on('click', '.delete-tool-and-infrastructure', function (event) {
    if (workorderApproved.tool_and_infrastructure) {
        deleteWorkorderApproved('tool-and-infrastructure', this, urlAddToolAndInfrastructure, TYPE_TOOL_AND_INFRASTRUCTURE);
    } else {
        deleteWorkorderNotApproved('tool-and-infrastructure', this, urlAddToolAndInfrastructure, TYPE_TOOL_AND_INFRASTRUCTURE);
    }
});

$(document).on('click', '.save-tool-and-infrastructure', function () {
    if (workorderApproved.tool_and_infrastructure) {
        saveWorkorderApproved('tool-and-infrastructure', this, urlAddToolAndInfrastructure, TYPE_TOOL_AND_INFRASTRUCTURE);
    } else {
        saveWorkorderNotApproved('tool-and-infrastructure', this, urlAddToolAndInfrastructure, TYPE_TOOL_AND_INFRASTRUCTURE);
    }
});

$(document).on('click', '.edit-tool-and-infrastructure', function () {
    id = $(this).data('id');
    $('.tr-tool-and-infrastructure-' + id + ' select').select2().on('select2:close', function(evt) { tabToChange (evt)});
    if (workorderApproved.tool_and_infrastructure) {
        editWorkorderApproved('tool-and-infrastructure', this, TYPE_TOOL_AND_INFRASTRUCTURE);
    } else {
        editWorkorderNotApproved('tool-and-infrastructure', this, TYPE_TOOL_AND_INFRASTRUCTURE);
    }
});


$(document).on('click', '.refresh-tool-and-infrastructure', function () {
    id = $(this).data('id');
    $('.tr-tool-and-infrastructure-' + id + ' .select2').addClass('display-none');
    if (workorderApproved.tool_and_infrastructure) {
        refreshWorkorderApproved('tool-and-infrastructure', this, TYPE_TOOL_AND_INFRASTRUCTURE);
    } else {
        refreshWorkorderNotApproved('tool-and-infrastructure', this, TYPE_TOOL_AND_INFRASTRUCTURE);
    }
});

$(document).on('click', '.add-new-tool-and-infrastructure', function () {
    if (workorderApproved.tool_and_infrastructure) {
        saveDraftWorkorderApproved('tool-and-infrastructure', this, urlAddToolAndInfrastructure, TYPE_TOOL_AND_INFRASTRUCTURE);
    } else {
        submitWorkorderNotApproved('tool-and-infrastructure', this, urlAddToolAndInfrastructure, TYPE_TOOL_AND_INFRASTRUCTURE);
    }
});

/* communication */
$(document).on('click', '.add-communication', function (event) {
    if (workorderApproved.communication) {
        showFormAddWorkorderApproved('communication', TYPE_COMMUNICATION);
    } else {
        showFormAddWorkorderNotApproved('communication', TYPE_COMMUNICATION);
    }
});

$(document).on('click', '.remove-communication', function (event) {
    if (workorderApproved.communication) {
        removeFormAddWorkorderApproved('communication', this, TYPE_COMMUNICATION);
    } else {
        removeFormAddWorkorderNotApproved('communication', TYPE_COMMUNICATION);
    }
});

$(document).on('click', '.delete-communication', function (event) {
    if (workorderApproved.communication) {
        deleteWorkorderApproved('communication', this, urlAddCommunication, TYPE_COMMUNICATION);
    } else {
        deleteWorkorderNotApproved('communication', this, urlAddCommunication, TYPE_COMMUNICATION);
    }
});

$(document).on('click', '.save-communication', function () {
    if (workorderApproved.communication) {
        saveWorkorderApproved('communication', this, urlAddCommunication, TYPE_COMMUNICATION);
    } else {
        saveWorkorderNotApproved('communication', this, urlAddCommunication, TYPE_COMMUNICATION);
    }
});

$(document).on('click', '.edit-communication', function () {
    if (workorderApproved.communication) {
        editWorkorderApproved('communication', this, TYPE_COMMUNICATION);
    } else {
        editWorkorderNotApproved('communication', this, TYPE_COMMUNICATION);
    }
});


$(document).on('click', '.refresh-communication', function () {
    if (workorderApproved.communication) {
        refreshWorkorderApproved('communication', this, TYPE_COMMUNICATION);
    } else {
        refreshWorkorderNotApproved('communication', this, TYPE_COMMUNICATION);
    }
});

$(document).on('click', '.add-new-communication', function () {
    if (workorderApproved.communication) {
        saveDraftWorkorderApproved('communication', this, urlAddCommunication, TYPE_COMMUNICATION);
    } else {
        submitWorkorderNotApproved('communication', this, urlAddCommunication, TYPE_COMMUNICATION);
    }
});

/* slove deliverable */
$(document).on('click', '.add-deliverable', function (event) {
    if (workorderApproved.deliverable) {
        showFormAddWorkorderApproved('deliverable', TYPE_DELIVERABLE);
    } else {
        showFormAddWorkorderNotApproved('deliverable', TYPE_DELIVERABLE);
    }
});

$(document).on('click', '.remove-deliverable', function (event) {
    if (workorderApproved.deliverable) {
        removeFormAddWorkorderApproved('deliverable', this, TYPE_DELIVERABLE);
    } else {
        removeFormAddWorkorderNotApproved('deliverable', TYPE_DELIVERABLE);
    }
});

$(document).on('click', '.delete-deliverable', function (event) {
    if (workorderApproved.deliverable) {
        deleteWorkorderApproved('deliverable', this, urlAddDeliverable, TYPE_DELIVERABLE);
    } else {
        deleteWorkorderNotApproved('deliverable', this, urlAddDeliverable, TYPE_DELIVERABLE);
    }
});

$(document).on('click', '.save-deliverable', function () {
    if (workorderApproved.deliverable) {
        saveWorkorderApproved('deliverable', this, urlAddDeliverable, TYPE_DELIVERABLE);
    } else {
        saveWorkorderNotApproved('deliverable', this, urlAddDeliverable, TYPE_DELIVERABLE);
    }
});

$(document).on('click', '.edit-deliverable', function () {
    id = $(this).data('id');
    $('.tr-deliverable-' + id + ' .select2').addClass('display-block');
    if (workorderApproved.deliverable) {
        editWorkorderApproved('deliverable', this, TYPE_DELIVERABLE);
    } else {
        editWorkorderNotApproved('deliverable', this, TYPE_DELIVERABLE);
    }
});


$(document).on('click', '.refresh-deliverable', function () {
    id = $(this).data('id');
    $('.tr-deliverable-' + id + ' .select2').removeClass('display-block');
    if (workorderApproved.deliverable) {
        refreshWorkorderApproved('deliverable', this, TYPE_DELIVERABLE);
    } else {
        refreshWorkorderNotApproved('deliverable', this, TYPE_DELIVERABLE);
    }
});

$(document).on('click', '.add-new-deliverable', function () {
    if (workorderApproved.deliverable) {
        saveDraftWorkorderApproved('deliverable', this, urlAddDeliverable, TYPE_DELIVERABLE);
    } else {
        submitWorkorderNotApproved('deliverable', this, urlAddDeliverable, TYPE_DELIVERABLE);
    }
});

/* slove project assumptions*/

$(document).on('click', '.add-assumptions', function (event) {
    if (workorderApproved.assumptions) {
        showFormAddWorkorderApproved('assumptions', TYPE_ASSUMPTIONS);
    } else {
        showFormAddWorkorderNotApproved('assumptions', TYPE_ASSUMPTIONS);
    }
});

$(document).on('click', '.remove-assumptions', function (event) {
    if (workorderApproved.assumptions) {
        removeFormAddWorkorderApproved('assumptions', this, TYPE_ASSUMPTIONS);
    } else {
        removeFormAddWorkorderNotApproved('assumptions', TYPE_ASSUMPTIONS);
    }
});

$(document).on('click', '.delete-assumptions', function (event) {
    if (workorderApproved.assumptions) {
        deleteWorkorderApproved('assumptions', this, urlAddAssumptions, TYPE_ASSUMPTIONS);
    } else {
        deleteWorkorderNotApproved('assumptions', this, urlAddAssumptions, TYPE_ASSUMPTIONS);
    }
});

$(document).on('click', '.save-assumptions', function () {
    if (workorderApproved.assumptions) {
        saveWorkorderApproved('assumptions', this, urlAddAssumptions, TYPE_ASSUMPTIONS);
    } else {
        saveWorkorderNotApproved('assumptions', this, urlAddAssumptions, TYPE_ASSUMPTIONS);
    }
});

$(document).on('click', '.edit-assumptions', function () {
    id = $(this).data('id');
    $('.tr-assumptions-' + id + ' .select2').removeClass('display-block');
    if (workorderApproved.assumptions) {
        editWorkorderApproved('assumptions', this, TYPE_ASSUMPTIONS);
    } else {
        editWorkorderNotApproved('assumptions', this, TYPE_ASSUMPTIONS);
    }
});


$(document).on('click', '.refresh-assumptions', function () {
    id = $(this).data('id');
    if (workorderApproved.assumptions) {
        refreshWorkorderApproved('assumptions', this, TYPE_ASSUMPTIONS);
    } else {
        refreshWorkorderNotApproved('assumptions', this, TYPE_ASSUMPTIONS);
    }
});

$(document).on('click', '.add-new-assumptions', function () {
    if (workorderApproved.assumptions) {
        saveDraftWorkorderApproved('assumptions', this, urlAddAssumptions, TYPE_ASSUMPTIONS);
    } else {
        submitWorkorderNotApproved('assumptions', this, urlAddAssumptions, TYPE_ASSUMPTIONS);
    }
});

/* slove project security */

$(document).on('click', '.add-security', function (event) {
    if (workorderApproved.security) {
        showFormAddWorkorderApproved('security', TYPE_SECURITY);
    } else {
        showFormAddWorkorderNotApproved('security', TYPE_SECURITY);
    }
});

$(document).on('click', '.remove-security', function (event) {
    if (workorderApproved.security) {
        removeFormAddWorkorderApproved('security', this, TYPE_SECURITY);
    } else {
        removeFormAddWorkorderNotApproved('security', TYPE_SECURITY);
    }
});

$(document).on('click', '.delete-security', function (event) {
    if (workorderApproved.security) {
        deleteWorkorderApproved('security', this, urlAddSecurity, TYPE_SECURITY);
    } else {
        deleteWorkorderNotApproved('security', this, urlAddSecurity, TYPE_SECURITY);
    }
});

$(document).on('click', '.save-security', function () {
    if (workorderApproved.security) {
        saveWorkorderApproved('security', this, urlAddSecurity, TYPE_SECURITY);
    } else {
        saveWorkorderNotApproved('security', this, urlAddSecurity, TYPE_SECURITY);
    }
});

$(document).on('click', '.edit-security', function () {
    id = $(this).data('id');
    $('.tr-security-' + id + ' select').select2().on('select2:close', function(evt) { tabToChange (evt)});
    if (workorderApproved.security) {
        editWorkorderApproved('security', this, TYPE_SECURITY);
    } else {
        editWorkorderNotApproved('security', this, TYPE_SECURITY);
    }
});


$(document).on('click', '.refresh-security', function () {
    id = $(this).data('id');
    $('.tr-security-' + id + ' .select2').addClass('display-none');
    if (workorderApproved.security) {
        refreshWorkorderApproved('security', this, TYPE_SECURITY);
    } else {
        refreshWorkorderNotApproved('security', this, TYPE_SECURITY);
    }
});

$(document).on('click', '.add-new-security', function () {
    if (workorderApproved.security) {
        saveDraftWorkorderApproved('security', this, urlAddSecurity, TYPE_SECURITY);
    } else {
        submitWorkorderNotApproved('security', this, urlAddSecurity, TYPE_SECURITY);
    }
});

/* slove project skill request*/

$(document).on('click', '.add-skill_request', function (event) {
    if (workorderApproved.skill_request) {
        showFormAddWorkorderApproved('skill_request', TYPE_SKILL_REQUEST);
    } else {
        showFormAddWorkorderNotApproved('skill_request', TYPE_SKILL_REQUEST);
    }
});

$(document).on('click', '.remove-skill_request', function (event) {
    if (workorderApproved.skill_request) {
        removeFormAddWorkorderApproved('skill_request', this, TYPE_SKILL_REQUEST);
    } else {
        removeFormAddWorkorderNotApproved('skill_request', TYPE_SKILL_REQUEST);
    }
});

$(document).on('click', '.delete-skill_request', function (event) {
    if (workorderApproved.skill_request) {
        deleteWorkorderApproved('skill_request', this, urlAddSkillRequest, TYPE_SKILL_REQUEST);
    } else {
        deleteWorkorderNotApproved('skill_request', this, urlAddSkillRequest, TYPE_SKILL_REQUEST);
    }
});

$(document).on('click', '.save-skill_request', function () {
    if (workorderApproved.skill_request) {
        saveWorkorderApproved('skill_request', this, urlAddSkillRequest, TYPE_SKILL_REQUEST);
    } else {
        saveWorkorderNotApproved('skill_request', this, urlAddSkillRequest, TYPE_SKILL_REQUEST);
    }
});

$(document).on('click', '.edit-skill_request', function () {
    id = $(this).data('id');
    $('.tr-skill_request-' + id + ' .select2').removeClass('display-block');
    if (workorderApproved.skill_request) {
        editWorkorderApproved('skill_request', this, TYPE_SKILL_REQUEST);
    } else {
        editWorkorderNotApproved('skill_request', this, TYPE_SKILL_REQUEST);
    }
});


$(document).on('click', '.refresh-skill_request', function () {
    id = $(this).data('id');
    if (workorderApproved.skill_request) {
        refreshWorkorderApproved('skill_request', this, TYPE_SKILL_REQUEST);
    } else {
        refreshWorkorderNotApproved('skill_request', this, TYPE_SKILL_REQUEST);
    }
});

$(document).on('click', '.add-new-skill_request', function () {
    if (workorderApproved.skill_request) {
        saveDraftWorkorderApproved('skill_request', this, urlAddSkillRequest, TYPE_SKILL_REQUEST);
    } else {
        submitWorkorderNotApproved('skill_request', this, urlAddSkillRequest, TYPE_SKILL_REQUEST);
    }
});

/* slove project constraints*/

$(document).on('click', '.add-constraints', function (event) {
    if (workorderApproved.constraints) {
        showFormAddWorkorderApproved('constraints', TYPE_CONSTRAINTS);
    } else {
        showFormAddWorkorderNotApproved('constraints', TYPE_CONSTRAINTS);
    }
});

$(document).on('click', '.remove-constraints', function (event) {
    if (workorderApproved.constraints) {
        removeFormAddWorkorderApproved('constraints', this, TYPE_CONSTRAINTS);
    } else {
        removeFormAddWorkorderNotApproved('constraints', TYPE_CONSTRAINTS);
    }
});

$(document).on('click', '.delete-constraints', function (event) {
    if (workorderApproved.constraints) {
        deleteWorkorderApproved('constraints', this, urlAddAssumptions, TYPE_CONSTRAINTS);
    } else {
        deleteWorkorderNotApproved('constraints', this, urlAddAssumptions, TYPE_CONSTRAINTS);
    }
});

$(document).on('click', '.save-constraints', function () {
    if (workorderApproved.constraints) {
        saveWorkorderApproved('constraints', this, urlAddAssumptions, TYPE_CONSTRAINTS);
    } else {
        saveWorkorderNotApproved('constraints', this, urlAddAssumptions, TYPE_CONSTRAINTS);
    }
});

$(document).on('click', '.edit-constraints', function () {
    id = $(this).data('id');
    $('.tr-constraints-' + id + ' .select2').removeClass('display-block');
    if (workorderApproved.constraints) {
        editWorkorderApproved('constraints', this, TYPE_CONSTRAINTS);
    } else {
        editWorkorderNotApproved('constraints', this, TYPE_CONSTRAINTS);
    }
});


$(document).on('click', '.refresh-constraints', function () {
    id = $(this).data('id');
    if (workorderApproved.constraints) {
        refreshWorkorderApproved('constraints', this, TYPE_CONSTRAINTS);
    } else {
        refreshWorkorderNotApproved('constraints', this, TYPE_CONSTRAINTS);
    }
});

$(document).on('click', '.add-new-constraints', function () {
    if (workorderApproved.constraints) {
        saveDraftWorkorderApproved('constraints', this, urlAddAssumptions, TYPE_CONSTRAINTS);
    } else {
        submitWorkorderNotApproved('constraints', this, urlAddAssumptions, TYPE_CONSTRAINTS);
    }
});

/* slove project member */
$(document).on('click', '.add-project-member', function (event) {
    if (workorderApproved.project_member) {
        showFormAddWorkorderApproved('project-member', TYPE_PROJECT_MEMBER);
    } else {
        showFormAddWorkorderNotApproved('project-member', TYPE_PROJECT_MEMBER);
    }
});

$(document).on('click', '.remove-project-member', function (event) {
    if (workorderApproved.project_member) {
        removeFormAddWorkorderApproved('project-member', this, TYPE_PROJECT_MEMBER);
    } else {
        removeFormAddWorkorderNotApproved('project-member', TYPE_PROJECT_MEMBER);
    }
});

$(document).on('click', '.delete-project-member', function (event) {
    if (workorderApproved.project_member) {
        deleteWorkorderApproved('project-member', this, urlAddProjectMember, TYPE_PROJECT_MEMBER);
    } else {
        deleteWorkorderNotApproved('project-member', this, urlAddProjectMember, TYPE_PROJECT_MEMBER);
    }
});

$(document).on('click', '.save-project-member', function () {
    if (workorderApproved.project_member) {
        saveWorkorderApproved('project-member', this, urlAddProjectMember, TYPE_PROJECT_MEMBER);
    } else {
        saveWorkorderNotApproved('project-member', this, urlAddProjectMember, TYPE_PROJECT_MEMBER);
    }
});

$(document).on('click', '.edit-project-member', function () {
    var id = $(this).data('id');
    $('.tr-project-' + id + ' .select2').addClass('display-block');
    if (workorderApproved.project_member) {
        editWorkorderApproved('project-member', this, TYPE_PROJECT_MEMBER);
    } else {
        editWorkorderNotApproved('project-member', this, TYPE_PROJECT_MEMBER);
    }
});


$(document).on('click', '.refresh-project-member', function () {
    var id = $(this).data('id'),
        employeeId = $(this).data('employee_id');
    $('.tr-project-' + id + ' .select2').removeClass('display-block');
    if (workorderApproved.project_member) {
        refreshWorkorderApproved('project-member', this, TYPE_PROJECT_MEMBER);
    } else {
        refreshWorkorderNotApproved('project-member', this, TYPE_PROJECT_MEMBER);
    }
    $('.select-search-remote-member[data-id="' + id + '"]').val(employeeId).trigger('change');
});

$(document).on('click', '.add-new-project-member', function () {
    if (workorderApproved.project_member) {
        saveDraftWorkorderApproved('project-member', this, urlAddProjectMember, TYPE_PROJECT_MEMBER);
    } else {
        submitWorkorderNotApproved('project-member', this, urlAddProjectMember, TYPE_PROJECT_MEMBER);
    }
});

/* slove Derived expenses */
$(document).on('click', '.add-derived-expenses', function (event) {
    if (workorderApproved.devices_expenses
    ) {
        showFormAddWorkorderApproved('derived-expenses', TYPE_WO_DEVICES_EXPENSE);
    } else {
        showFormAddWorkorderNotApproved('derived-expenses', TYPE_WO_DEVICES_EXPENSE);
    }
});

$(document).on('click', '.remove-derived-expenses', function (event) {
    if (workorderApproved.devices_expenses) {
        removeFormAddWorkorderApproved('derived-expenses', this, TYPE_WO_DEVICES_EXPENSE);
    } else {
        removeFormAddWorkorderNotApproved('derived-expenses', TYPE_WO_DEVICES_EXPENSE);
    }
});

$(document).on('click', '.add-new-derived-expenses', function () {
    if (workorderApproved.devices_expenses) {
        saveDraftWorkorderApproved('derived-expenses', this, urlAddDerivedExpenese, TYPE_WO_DEVICES_EXPENSE);
    } else {
        submitWorkorderNotApproved('derived-expenses', this, urlAddDerivedExpenese, TYPE_WO_DEVICES_EXPENSE);
    }
});

$(document).on('click', '.edit-derived-expenses', function () {
    if (workorderApproved.devices_expenses) {
        editWorkorderApproved('derived-expenses', this, TYPE_WO_DEVICES_EXPENSE);
    } else {
        editWorkorderNotApproved('derived-expenses', this, TYPE_WO_DEVICES_EXPENSE);
    }
});

$(document).on('click', '.refresh-derived-expenses', function () {
    if (workorderApproved.devices_expenses) {
        refreshWorkorderApproved('derived-expenses', this, TYPE_WO_DEVICES_EXPENSE);
    } else {
        refreshWorkorderNotApproved('derived-expenses', this, TYPE_WO_DEVICES_EXPENSE);
    }
});

$(document).on('click', '.save-derived-expenses', function () {
    if (workorderApproved.devices_expenses) {
        saveWorkorderApproved('derived-expenses', this, urlAddDerivedExpenese, TYPE_WO_DEVICES_EXPENSE);
    } else {
        saveWorkorderNotApproved('derived-expenses', this, urlAddDerivedExpenese, TYPE_WO_DEVICES_EXPENSE);
    }
});

$(document).on('click', '.delete-derived-expenses', function (event) {
    if (workorderApproved.devices_expenses) {
        deleteWorkorderApproved('derived-expenses', this, urlAddDerivedExpenese, TYPE_WO_DEVICES_EXPENSE);
    } else {
        deleteWorkorderNotApproved('derived-expenses', this, urlAddDerivedExpenese, TYPE_WO_DEVICES_EXPENSE);
    }
});

$('body').on('change', '.select-same', function () {
    var val = $(this).val();
    var sameId = $(this).attr('same-id');
    $('.select-same[same-id="'+ sameId +'"]').val(val);
    select2Same();
});
select2Same();
function select2Same() {
    $('.select-same').select2({
        minimumResultsForSearch: -1
    });
}

function assignDefaultValue (key, value) {
    if (typeof value === 'undefined') {
        value = null;
    }
    if (typeof key === 'undefined') {
        key = value;
    }
    return key;
}

function showFormAddWorkorderApproved(className, type) {
    $('.error-validate-' + className).remove();
    type = assignDefaultValue(type);
    selectSearchReload();
    if(type == TYPE_DELIVERABLE) {
        if ($('.add-deliverable').data('requestRunning')) {
            return;
        }
        $('.add-deliverable').data('requestRunning', true);
        url = urlCheckStage;
        data = {
            _token: token,
            project_id: project_id,
        }
        $.ajax({
            url: url,
            type: 'post',
            data: data,
            dataType: 'json',
            success: function (data) {
                if (data.status || data.dontPermission  || data.notFound) {
                    $('#modal-warning-notification .text-default').html(data.message);
                    $('#modal-warning-notification').modal('show');
                } else {
                    showFormAddWorkorder(className, type);
                }
                RKfuncion.select2.init();
            },
            error: function () {
                $('#modal-warning-notification .text-default').html('error');
                $('#modal-warning-notification').modal('show');
            },
            complete: function () {
                $('.add-deliverable').data('requestRunning', false);
            }
        });
    } else {
        showFormAddWorkorder(className, type);
    }
}

function showFormAddWorkorder(className, type) {
    $('.remove-' + className).removeClass('display-none');
    $('.add-' + className).addClass('display-none');
    $('.tr-add-' + className).addClass('display-none');
    trElement = $('.tr-' + className).clone();
    trElement.removeClass('display-none tr-' + className);
    index = $('.tr-' + className + '-new').length + 1;

    if (type == TYPE_DELIVERABLE) {
        setTimeout(function () {
            $('.title-' + className).focus();
        }, 0);
        trElement.find('.title-' + className).attr('data-id', index).addClass('add-title-' + className + '-new-' + index);
        trElement.find('.committed_date-' + className).attr('data-id', index).addClass('add-committed_date-' + className + '-new-' + index);
        trElement.find('.re_commited_date-' + className).attr('data-id', index).addClass('add-re_commited_date-' + className + '-new-' + index);
        trElement.find('.actual_date-' + className).attr('data-id', index).addClass('add-actual_date-' + className + '-new-' + index);
        trElement.find('.change_request_by-' + className).attr('data-id', index).addClass('add-change_request_by-' + className + '-new-' + index);
        trElement.find('.stage-' + className).attr('data-id', index).addClass('add-stage-' + className + '-new-' + index);
        trElement.find('.note-' + className).attr('data-id', index).addClass('add-note-' + className + '-new-' + index);
    } else if (type == TYPE_STAGE_AND_MILESTONE) {
        setTimeout(function () {
            $('.stage-' + className).focus();
        }, 0);
        trElement.find('.stage-' + className).attr('data-id', index).addClass('add-stage-' + className + '-new-' + index);
        trElement.find('.description-' + className).attr('data-id', index).addClass('add-description-' + className + '-new-' + index);
        trElement.find('.milestone-' + className).attr('data-id', index).addClass('add-milestone-' + className + '-new-' + index);
        trElement.find('.qua_gate_plan-' + className).attr('data-id', index).addClass('add-qua_gate_plan-' + className + '-new-' + index);
        trElement.find('.qua_gate_actual-' + className).attr('data-id', index).addClass('add-qua_gate_actual-' + className + '-new-' + index);
        trElement.find('.span-qua_gate_result-' + className).attr('data-id', index).addClass('add-qua_gate_result-' + className + '-new-' + index);
    } else if (type == TYPE_TRAINING) {
        setTimeout(function () {
            $('.topic-' + className).focus();
        }, 0);
        trElement.find('.topic-' + className).attr('data-id', index).addClass('add-topic-' + className + '-new-' + index);
        trElement.find('.description-' + className).attr('data-id', index).addClass('add-description-' + className + '-new-' + index);
        trElement.find('.training-member-' + className).attr('data-id', index).addClass('add-training-member-' + className + '-new-' + index);
        trElement.find('.start_at-' + className).attr('data-id', index).addClass('add-start_at-' + className + '-new-' + index);
        trElement.find('.end_at-' + className).attr('data-id', index).addClass('add-end_at-' + className + '-new-' + index);
        trElement.find('.result-' + className).attr('data-id', index).addClass('add-result-' + className + '-new-' + index);
        trElement.find('.walver_criteria-' + className).attr('data-id', index).addClass('add-walver_criteria-' + className + '-new-' + index);
    } else if (type == TYPE_CUSTOMER_COMMUNICATION) {
        setTimeout(function () {
            $('.contact_address-' + className).focus();
        }, 0);
        trElement.find('.contact_address-' + className).attr('data-id', index).addClass('add-contact_address-' + className + '-new-' + index);
        trElement.find('.responsibility-' + className).attr('data-id', index).addClass('add-responsibility-' + className + '-new-' + index);
        trElement.find('.customer_communication-member-' + className).attr('data-id', index).addClass('add-customer_communication-member-' + className + '-new-' + index);
        trElement.find('.customer_communication-role-' + className).attr('data-id', index).addClass('add-customer_communication-role-' + className + '-new-' + index);
    } else if (type == TYPE_MEMBER_COMMUNICATION) {
        setTimeout(function () {
            $('.contact_address-' + className).focus();
        }, 0);
        trElement.find('.contact_address-' + className).attr('data-id', index).addClass('add-contact_address-' + className + '-new-' + index);
        trElement.find('.responsibility-' + className).attr('data-id', index).addClass('add-responsibility-' + className + '-new-' + index);
        trElement.find('.member_communication-member-' + className).attr('data-id', index).addClass('add-member_communication-member-' + className + '-new-' + index);
        trElement.find('.member_communication-role-' + className).attr('data-id', index).addClass('add-member_communication-role-' + className + '-new-' + index);
    } else if (type == TYPE_SECURITY) {
        setTimeout(function () {
            $('.content-' + className).focus();
        }, 0);
        trElement.find('.content-' + className).attr('data-id', index).addClass('add-topic-' + className + '-new-' + index);
        trElement.find('.description-' + className).attr('data-id', index).addClass('add-description-' + className + '-new-' + index);
        trElement.find('.security-member-' + className).attr('data-id', index).addClass('add-security-member-' + className + '-new-' + index);
        trElement.find('.procedure-' + className).attr('data-id', index).addClass('add-procedure-' + className + '-new-' + index);
        trElement.find('.period-' + className).attr('data-id', index).addClass('add-period-' + className + '-new-' + index);
    } else if (type == TYPE_COMMUNICATION) {
        setTimeout(function () {
            $('.content-' + className).focus();
        }, 0);
        trElement.find('.content-' + className).attr('data-id', index).addClass('add-content-' + className + '-new-' + index);
    } else if (type == TYPE_EXTERNAL_INTERFACE) {
        setTimeout(function () {
            $('.name-' + className).focus();
        }, 0);
        trElement.find('.name-' + className).attr('data-id', index).addClass('add-name-' + className + '-new-' + index);
        trElement.find('.position-' + className).attr('data-id', index).addClass('add-position-' + className + '-new-' + index);
        trElement.find('.responsibilities-' + className).attr('data-id', index).addClass('add-responsibilities-' + className + '-new-' + index);
        trElement.find('.contact-' + className).attr('data-id', index).addClass('add-contact-' + className + '-new-' + index);
    } else if (type == TYPE_TOOL_AND_INFRASTRUCTURE) {
        setTimeout(function () {
            $('.soft_hard_ware-' + className).focus();
        }, 0);
        trElement.find('.soft_hard_ware-' + className).attr('data-id', index).addClass('add-soft_hard_ware-' + className + '-new-' + index);
        trElement.find('.purpose-' + className).attr('data-id', index).addClass('add-purpose-' + className + '-new-' + index);
        trElement.find('.start-date-' + className).attr('data-id', index).addClass('add-start-date-' + className + '-new-' + index);
        trElement.find('.end-date-' + className).attr('data-id', index).addClass('add-end-date-' + className + '-new-' + index);
        trElement.find('.note-' + className).attr('data-id', index).addClass('add-note-' + className + '-new-' + index);
    } else if (type == TYPE_ASSUMPTIONS) {
        setTimeout(function () {
            $('.description-' + className).focus();
        }, 0);
        trElement.find('.description-' + className).attr('data-id', index).addClass('add-description-' + className + '-new-' + index);
        trElement.find('.remark-' + className).attr('data-id', index).addClass('add-remark-' + className + '-new-' + index);
    } else if (type == TYPE_CONSTRAINTS) {
        setTimeout(function () {
            $('.description-' + className).focus();
        }, 0);
        trElement.find('.description-' + className).attr('data-id', index).addClass('add-description-' + className + '-new-' + index);
        trElement.find('.remark-' + className).attr('data-id', index).addClass('add-remark-' + className + '-new-' + index);
    } else if (type == TYPE_PROJECT_MEMBER) {
        trTotal = $('.tr-total-resource').clone();
        $('.tr-total-resource').remove();
        setTimeout(function () {
            $('.type-' + className).focus();
        }, 0);
        trElement.find('.type-' + className).attr('data-id', index).addClass('add-type-' + className + '-new-' + index);
        trElement.find('.employee_id-' + className).attr('data-id', index).addClass('add-employee_id-' + className + '-new-' + index);
        trElement.find('.start_at-' + className).attr('data-id', index).addClass('add-start_at-' + className + '-new-' + index);
        trElement.find('.end_at-' + className).attr('data-id', index).addClass('add-end_at-' + className + '-new-' + index);
        trElement.find('.effort-' + className).attr('data-id', index).addClass('add-effort-' + className + '-new-' + index);
        trElement.find('.prog-' + className).attr('data-id', index).addClass('add-prog-' + className + '-new-' + index);
    } else {
        setTimeout(function () {
            $('.content-' + className).focus();
        }, 0);
        trElement.find('.content-' + className).attr('data-id', index).addClass('add-content-' + className + '-new-' + index);
        trElement.find('.note-' + className).attr('data-id', index).addClass('add-note-' + className + '-new-' + index);
    }
    if (type != TYPE_STAGE_AND_MILESTONE && type != TYPE_DELIVERABLE && type != TYPE_PROJECT_MEMBER) {
        $('#table-' + className + ' tr:last td:first').html($('#table-' + className + ' tr').length - 2);
    }
    trElement.find('.remove-' + className).attr('data-id', index).addClass('remove-' + className + '-new');
    trElement.find('.add-new-' + className).attr('data-id', index).addClass('add-' + className + '-new');
    trElement.addClass('tr-' + className + '-' + index + ' tr-' + className + '-new');
    $('#table-' + className + ' table tbody tr:last').after(trElement);
    if(type == TYPE_PROJECT_MEMBER) {
        setTimeout(function () {
            $('.tr-project-member-new .select-search-add').select2().on('select2:close', function(evt) { tabToChange (evt)});
            $('.tr-project-member-new .select-search-remote-member-add').each (function() {
                RKfuncion.select2.elementRemote(
                    $(this)
                );
            });
            $('.multiselect2-proj-add').multiselect({
                includeSelectAllOption: false,
                numberDisplayed: 1,
                nonSelectedText: RKVarPassGlobal.multiSelectTextNone,
                allSelectedText: RKVarPassGlobal.multiSelectTextAll,
                nSelectedText: RKVarPassGlobal.multiSelectTextSelectedShort,
                enableFiltering: true,
                onDropdownShown: function() {
                    RKfuncion.multiselect2.overfollow(this);
                },
                onDropdownHide: function() {
                    RKfuncion.multiselect2.overfollowClose(this);
                }
            });
        }, 200);
        $('#table-' + className + ' table tbody tr:last').after(trTotal);
    }
    if (type == TYPE_STAGE_AND_MILESTONE) {
        setTimeout(function () {
            $('.tr-stage-and-milestone-new select').select2().on('select2:close', function(evt) { tabToChange (evt)});
        });
    }

    if (type == TYPE_DELIVERABLE) {
        setTimeout(function () {
            $('.tr-deliverable-new select').select2().on('select2:close', function(evt) { tabToChange (evt)});
        });
    }
    if (type == TYPE_TRAINING) {
        setTimeout(function () {
            $('.tr-training-hidden select').select2().on('select2:close', function(evt) { tabToChange (evt)});
        });
    }
    if (type == TYPE_CRITICAL_DEPENDENCIES) {
        setTimeout(function () {
            $('.critical-assignee-select2-new').select2({
                ajax: {
                    url: urlSearchEmployee,
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term, // search term
                            page: params.page,
                            projectId: projectId
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
                                more: (params.page * 30) < data.total_count
                            }
                        };
                    },
                    cache: true
                },
                escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
                minimumInputLength: 1,
            });
        });
    }
    if (type == TYPE_ASSUMPTION_CONSTRAIN) {
        setTimeout(function () {
            $('.assignee-assumption-assignee-select2-new').select2({
                ajax: {
                    url: urlSearchEmployee,
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term, // search term
                            page: params.page,
                            projectId: projectId
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
                                more: (params.page * 30) < data.total_count
                            }
                        };
                    },
                    cache: true
                },
                escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
                minimumInputLength: 1,
            });
        });
    }
    if (index == 1) {
        $('.save-or-draft-' + className).removeClass('display-none');
    }
    RKfuncion.select2.init();
}

function removeFormAddWorkorderApproved(className, element, type) {
    type = assignDefaultValue(type);
    var dataId = $(element).data('id');
    $('.remove-' + className).addClass('display-none');
    $('.add-' + className).removeClass('display-none');
    $('.tr-add-' + className).removeClass('display-none');
    trElement = $('.tr-' + className + '-new').remove();
    if (type == TYPE_DELIVERABLE) {
        $('.tr-deliverable-new .select2').removeClass('display-block');
        $('.add-title-' + className + '-new-' + dataId).val('');
        $('.add-committed_date-' + className + '-new-' + dataId).val('');
        $('.add-actual_date-' + className + '-new-' + dataId).val('');
        $('.add-stage-' + className + '-new-' + dataId).val('');
        $('.add-note-' + className + '-new-' + dataId).val('');
    } else if (type == TYPE_STAGE_AND_MILESTONE) {
        $('.tr-stage-and-milestone-new .select2').removeClass('display-block');
        $('.add-stage-' + className + '-new-' + dataId).val('');
        $('.add-description-' + className + '-new-' + dataId).val('');
        $('.add-milestone-' + className + '-new-' + dataId).val('');
        $('.add-qua_gate_actual-' + className + '-new-' + dataId).val('');
        $('.add-qua_gate_plan-' + className + '-new-' + dataId).val('');
    } else if (type == TYPE_TRAINING) {
        $('.add-topic-' + className + '-new-' + dataId).val('');
        $('.add-description-' + className + '-new-' + dataId).val('');
        $('.add-participants-' + className + '-new-' + dataId).val('');
        $('.tr-training-new .select2').addClass('display-none');
        $('.add-time-' + className + '-new-' + dataId).val('');
        $('.add-walver_criteria-' + className + '-new-' + dataId).val('');
    } else if (type == TYPE_CUSTOMER_COMMUNICATION) {
        $('.add-customer-' + className + '-new-' + dataId).val('');
        $('.add-contact_address-' + className + '-new-' + dataId).val('');
        $('.tr-customer_communication-new .select2').addClass('display-none');
        $('.add-responsibility-' + className + '-new-' + dataId).val('');
    } else if (type == TYPE_COMMUNICATION_MEETING || type == TYPE_COMMUNICATION_REPORT || type == TYPE_COMMUNICATION_OTHER) {
        $('.add-type-' + className + '-new-' + dataId).val('');
        $('.add-method-' + className + '-new-' + dataId).val('');
        $('.add-time-' + className + '-new-' + dataId).val('');
        $('.add-information-' + className + '-new-' + dataId).val('');
        $('.add-stakeholder-' + className + '-new-' + dataId).val('');
    } else if (type == TYPE_SECURITY) {
        $('.add-content-' + className + '-new-' + dataId).val('');
        $('.add-description-' + className + '-new-' + dataId).val('');
        $('.add-participants-' + className + '-new-' + dataId).val('');
        $('.tr-security-new .select2').addClass('display-none');
        $('.add-period-' + className + '-new-' + dataId).val('');
        $('.add-procedure-' + className + '-new-' + dataId).val('');
    } else if (type == TYPE_CONSTRAINTS || type == TYPE_ASSUMPTIONS) {
        $('.add-description-' + className + '-new-' + dataId).val('');
        $('.add-remark-' + className + '-new-' + dataId).val('');
    } else if (type == TYPE_SKILL_REQUEST) {
        $('.add-skill-' + className + '-new-' + dataId).val('');
        $('.add-category-' + className + '-new-' + dataId).val('');
        $('.add-course_name-' + className + '-new-' + dataId).val('');
        $('.add-mode-' + className + '-new-' + dataId).val('');
        $('.add-provider-' + className + '-new-' + dataId).val('');
        $('.add-required_role-' + className + '-new-' + dataId).val('');
        $('.add-hours-' + className + '-new-' + dataId).val('');
        $('.add-level-' + className + '-new-' + dataId).val('');
        $('.add-remark-' + className + '-new-' + dataId).val('');
    } else if (type == TYPE_PROJECT_MEMBER) {
        trAdd = $('.tr-add-project-member').clone();
        $('.tr-add-project-member').remove();
        $('#table-' + className + ' table tbody tr:last').after(trAdd);
        $('.tr-project-member-new .select2').removeClass('display-block');
        $('.add-type-' + className + '-new-' + dataId).val($('.add-type-' + className + '-new-' + dataId + ' option:first').val());
        $('.add-employee_id-' + className + '-new-' + dataId).val($('.add-employee_id-' + className + '-new-' + dataId + ' option:first').val());
        $('.add-start_at-' + className + '-new-' + dataId).val('');
        $('.add-end_at-' + className + '-new-' + dataId).val('');
        $('.add-effort-' + className + '-new-' + dataId).val('');
    }  else if (type == TYPE_COMMUNICATION) {
        $('.add-content-' + className + '-new-' + dataId).val('');
    } else if (type == TYPE_EXTERNAL_INTERFACE) {
        $('.add-name-' + className + '-new-' + dataId).val('');
        $('.add-position-' + className + '-new-' + dataId).val('');
        $('.add-responsibilities-' + className + '-new-' + dataId).val('');
        $('.add-contact-' + className + '-new-' + dataId).val('');
    } else if (type == TYPE_TOOL_AND_INFRASTRUCTURE) {
        $('.tr-tool-and-infrastructure .select2').removeClass('display-block');
        $('.add-soft_hard_ware-' + className + '-new-' + dataId).val('');
        $('.add-purpose-' + className + '-new-' + dataId).val('');
        $('.add-note-' + className + '-new-' + dataId).val('');
        $('.add-start-date-' + className + '-new-' + dataId).val('');
        $('.add-end-date-' + className + '-new-' + dataId).val('');
    } else if (type == TYPE_WO_DEVICES_EXPENSE) {
        $('.add-time-' + className + '-new-' + dataId).val('');
        $('.add-amount-' + className + '-new-' + dataId).val('');
        $('.add-description-' + className + '-new-' + dataId).val('');
    } else {
        $('.add-content-' + className + '-new-' + dataId).val('');
        $('.add-expected_date-' + className + '-new-' + dataId).val('');
    }
    $('.error-' + className).remove();
}


function editWorkorderApproved(className, element, type) {
    type = assignDefaultValue(type);
    var id = $(element).data('id');
    $(element).addClass('display-none');
    $('.save-' + className + '-' + id).removeClass('display-none');
    $('.delete-' + className + '-' + id).addClass('display-none');
    $('.refresh-' + className + '-' + id).removeClass('display-none');
    if (type == TYPE_DELIVERABLE) {
        setTimeout(function () {
            input = $('.input-title-' + className + '-' + id);
            input.focus();
            var tmpStr = input.val();
            input.val('');
            input.val(tmpStr);
        }, 0);

        $('.title-' + className + '-' + id).addClass('display-none');
        $('.input-title-' + className + '-' + id).removeClass('display-none');
        $('.committed_date-' + className + '-' + id).addClass('display-none');
        $('.re_commited_date-' + className + '-' + id).addClass('display-none');
        $('.input-committed_date-' + className + '-' + id).removeClass('display-none');
        $('.input-re_commited_date-' + className + '-' + id).removeClass('display-none');
        $('.actual_date-' + className + '-' + id).addClass('display-none');
        $('.input-actual_date-' + className + '-' + id).removeClass('display-none');
        $('.stage-' + className + '-' + id).addClass('display-none');
        $('.change_request_by-' + className + '-' + id).addClass('display-none');
        $('.input-stage-' + className + '-' + id).removeClass('display-none');
        $('.input-change_request_by-' + className + '-' + id).removeClass('display-none');
        $('.note-' + className + '-' + id).addClass('display-none');
        $('.input-note-' + className + '-' + id).removeClass('display-none');
    } else if (type == TYPE_STAGE_AND_MILESTONE) {
        setTimeout(function () {
            input = $('.input-stage-' + className + '-' + id)
            input.focus();
            var tmpStr = input.val();
            input.val('');
            input.val(tmpStr);
        }, 0);

        $('.stage-' + className + '-' + id).addClass('display-none');
        $('.input-stage-' + className + '-' + id).removeClass('display-none');
        $('.description-' + className + '-' + id).addClass('display-none');
        $('.input-description-' + className + '-' + id).removeClass('display-none');
        $('.milestone-' + className + '-' + id).addClass('display-none');
        $('.input-milestone-' + className + '-' + id).removeClass('display-none');
        $('.qua_gate_plan-' + className + '-' + id).addClass('display-none');
        $('.input-qua_gate_plan-' + className + '-' + id).removeClass('display-none');
        $('.qua_gate_actual-' + className + '-' + id).addClass('display-none');
        $('.input-qua_gate_actual-' + className + '-' + id).removeClass('display-none');
        $('.qua_gate_result-' + className + '-' + id).addClass('display-none');
        $('.span-qua_gate_result-' + className + '-' + id).removeClass('display-none');
    } else if (type == TYPE_TRAINING) {
        setTimeout(function () {
            input = $('.input-topic-' + className + '-' + id)
            input.focus();
            var tmpStr = input.val();
            input.val('');
            input.val(tmpStr);
        }, 0);

        $('.topic-' + className + '-' + id).addClass('display-none');
        $('.input-topic-' + className + '-' + id).removeClass('display-none');
        $('.description-' + className + '-' + id).addClass('display-none');
        $('.input-description-' + className + '-' + id).removeClass('display-none');
        $('.participants-' + className + '-' + id).addClass('display-none');
        $('.input-participants-' + className + '-' + id).removeClass('display-none');
        $('.start_at-' + className + '-' + id).addClass('display-none');
        $('.input-start_at-' + className + '-' + id).removeClass('display-none');
        $('.end_at-' + className + '-' + id).addClass('display-none');
        $('.input-end_at-' + className + '-' + id).removeClass('display-none');
        $('.result-' + className + '-' + id).addClass('display-none');
        $('.input-result-' + className + '-' + id).removeClass('display-none');
        $('.walver_criteria-' + className + '-' + id).addClass('display-none');
        $('.input-walver_criteria-' + className + '-' + id).removeClass('display-none');
    } else if (type == TYPE_COMMUNICATION_MEETING || type == TYPE_COMMUNICATION_REPORT || type == TYPE_COMMUNICATION_OTHER) {
        setTimeout(function () {
            input = $('.input-type-' + className + '-' + id)
            input.focus();
            var tmpStr = input.val();
            input.val('');
            input.val(tmpStr);
        }, 0);
        $('.type-' + className + '-' + id).addClass('display-none');
        $('.input-type-' + className + '-' + id).removeClass('display-none');
        $('.method-' + className + '-' + id).addClass('display-none');
        $('.input-method-' + className + '-' + id).removeClass('display-none');
        $('.time-' + className + '-' + id).addClass('display-none');
        $('.input-time-' + className + '-' + id).removeClass('display-none');
        $('.information-' + className + '-' + id).addClass('display-none');
        $('.input-information-' + className + '-' + id).removeClass('display-none');
        $('.stakeholder-' + className + '-' + id).addClass('display-none');
        $('.input-stakeholder-' + className + '-' + id).removeClass('display-none');
    } else if (type == TYPE_MEMBER_COMMUNICATION) {
        setTimeout(function () {
            input = $('.input-member-' + className + '-' + id)
            input.focus();
            var tmpStr = input.val();
            input.val('');
            input.val(tmpStr);
        }, 0);

        $('.member-' + className + '-' + id).addClass('display-none');
        $('.input-member-' + className + '-' + id).removeClass('display-none');
        $('.role-' + className + '-' + id).addClass('display-none');
        $('.input-role-' + className + '-' + id).removeClass('display-none');
        $('.contact_address-' + className + '-' + id).addClass('display-none');
        $('.input-contact_address-' + className + '-' + id).removeClass('display-none');
        $('.responsibility-' + className + '-' + id).addClass('display-none');
        $('.input-responsibility-' + className + '-' + id).removeClass('display-none');
    } else if (type == TYPE_CUSTOMER_COMMUNICATION) {
        setTimeout(function () {
            input = $('.input-customer-' + className + '-' + id)
            input.focus();
            var tmpStr = input.val();
            input.val('');
            input.val(tmpStr);
        }, 0);

        $('.customer-' + className + '-' + id).addClass('display-none');
        $('.input-customer-' + className + '-' + id).removeClass('display-none');
        $('.role-' + className + '-' + id).addClass('display-none');
        $('.input-role-' + className + '-' + id).removeClass('display-none');
        $('.contact_address-' + className + '-' + id).addClass('display-none');
        $('.input-contact_address-' + className + '-' + id).removeClass('display-none');
        $('.responsibility-' + className + '-' + id).addClass('display-none');
        $('.input-responsibility-' + className + '-' + id).removeClass('display-none');
    } else if (type == TYPE_SKILL_REQUEST) {
        setTimeout(function () {
            input = $('.input-skill-' + className + '-' + id)
            input.focus();
            var tmpStr = input.val();
            input.val('');
            input.val(tmpStr);
        }, 0);

        $('.skill-' + className + '-' + id).addClass('display-none');
        $('.input-skill-' + className + '-' + id).removeClass('display-none');
        $('.category-' + className + '-' + id).addClass('display-none');
        $('.input-category-' + className + '-' + id).removeClass('display-none');
        $('.course_name-' + className + '-' + id).addClass('display-none');
        $('.input-course_name-' + className + '-' + id).removeClass('display-none');
        $('.mode-' + className + '-' + id).addClass('display-none');
        $('.input-mode-' + className + '-' + id).removeClass('display-none');
        $('.provider-' + className + '-' + id).addClass('display-none');
        $('.input-provider-' + className + '-' + id).removeClass('display-none');
        $('.required_role-' + className + '-' + id).addClass('display-none');
        $('.input-required_role-' + className + '-' + id).removeClass('display-none');
        $('.hours-' + className + '-' + id).addClass('display-none');
        $('.input-hours-' + className + '-' + id).removeClass('display-none');
        $('.level-' + className + '-' + id).addClass('display-none');
        $('.input-level-' + className + '-' + id).removeClass('display-none');
        $('.remark-' + className + '-' + id).addClass('display-none');
        $('.input-remark-' + className + '-' + id).removeClass('display-none');
    } else if (type == TYPE_SECURITY) {
        setTimeout(function () {
            input = $('.input-content-' + className + '-' + id)
            input.focus();
            var tmpStr = input.val();
            input.val('');
            input.val(tmpStr);
        }, 0);

        $('.content-' + className + '-' + id).addClass('display-none');
        $('.input-content-' + className + '-' + id).removeClass('display-none');
        $('.description-' + className + '-' + id).addClass('display-none');
        $('.input-description-' + className + '-' + id).removeClass('display-none');
        $('.participants-' + className + '-' + id).addClass('display-none');
        $('.input-participants-' + className + '-' + id).removeClass('display-none');
        $('.period-' + className + '-' + id).addClass('display-none');
        $('.input-period-' + className + '-' + id).removeClass('display-none');
        $('.procedure-' + className + '-' + id).addClass('display-none');
        $('.input-procedure-' + className + '-' + id).removeClass('display-none');
    } else if (type == TYPE_ASSUMPTIONS || type == TYPE_CONSTRAINTS) {
        setTimeout(function () {
            input = $('.input-description-' + className + '-' + id)
            input.focus();
            var tmpStr = input.val();
            input.val('');
            input.val(tmpStr);
        }, 0);

        $('.description-' + className + '-' + id).addClass('display-none');
        $('.input-description-' + className + '-' + id).removeClass('display-none');
        $('.remark-' + className + '-' + id).addClass('display-none');
        $('.input-remark-' + className + '-' + id).removeClass('display-none');
    } else if (type == TYPE_PROJECT_MEMBER) {
        setTimeout(function () {
            input = $('.input-type-' + className + '-' + id)
            input.focus();
        }, 0);

        $('.type-' + className + '-' + id).addClass('display-none');
        $('.input-type-' + className + '-' + id).removeClass('display-none');
        $('.employee_id-' + className + '-' + id).addClass('display-none');
        $('.input-employee_id-' + className + '-' + id).removeClass('display-none');
        $('.start_at-' + className + '-' + id).addClass('display-none');
        $('.input-start_at-' + className + '-' + id).removeClass('display-none');
        $('.end_at-' + className + '-' + id).addClass('display-none');
        $('.input-end_at-' + className + '-' + id).removeClass('display-none');
        $('.effort-' + className + '-' + id).addClass('display-none');
        $('.input-effort-' + className + '-' + id).removeClass('display-none');
        $('.prog-' + className + '-' + id).addClass('display-none');
        $('.input-prog-' + className + '-' + id).removeClass('display-none');
        $('.tr-project-' + id + ' .proj-member-prog-lang > .btn-group').removeClass('display-none');
    } else if (type == TYPE_COMMUNICATION) {
        setTimeout(function () {
            input = $('.input-content-' + className + '-' + id)
            input.focus();
            var tmpStr = input.val();
            input.val('');
            input.val(tmpStr);
        }, 0);

        $('.content-' + className + '-' + id).addClass('display-none');
        $('.input-content-' + className + '-' + id).removeClass('display-none');
    } else if (type == TYPE_EXTERNAL_INTERFACE) {
        setTimeout(function () {
            input = $('.input-name-' + className + '-' + id)
            input.focus();
            var tmpStr = input.val();
            input.val('');
            input.val(tmpStr);
        }, 0);

        $('.name-' + className + '-' + id).addClass('display-none');
        $('.input-name-' + className + '-' + id).removeClass('display-none');
        $('.position-' + className + '-' + id).addClass('display-none');
        $('.input-position-' + className + '-' + id).removeClass('display-none');
        $('.responsibilities-' + className + '-' + id).addClass('display-none');
        $('.input-responsibilities-' + className + '-' + id).removeClass('display-none');
        $('.contact-' + className + '-' + id).addClass('display-none');
        $('.input-contact-' + className + '-' + id).removeClass('display-none');
    } else if (type == TYPE_TOOL_AND_INFRASTRUCTURE) {
        setTimeout(function () {
            input = $('.input-soft_hard_ware-' + className + '-' + id)
            input.focus();
            var tmpStr = input.val();
            input.val('');
            input.val(tmpStr);
        }, 0);

        $('.soft_hard_ware-' + className + '-' + id).addClass('display-none');
        $('.input-soft_hard_ware-' + className + '-' + id).removeClass('display-none');
        $('.purpose-' + className + '-' + id).addClass('display-none');
        $('.input-purpose-' + className + '-' + id).removeClass('display-none');
        $('.start-date-' + className + '-' + id).addClass('display-none');
        $('.input-start-date-' + className + '-' + id).removeClass('display-none');
        $('.end-date-' + className + '-' + id).addClass('display-none');
        $('.input-end-date-' + className + '-' + id).removeClass('display-none');
        $('.note-' + className + '-' + id).addClass('display-none');
        $('.input-note-' + className + '-' + id).removeClass('display-none');
    } else if (type == TYPE_WO_DEVICES_EXPENSE) {
        setTimeout(function () {
            input = $('.input-time-' + className + '-' + id)
            input.focus();
            var tmpStr = input.val();
            input.val('');
            input.val(tmpStr);
        }, 0);

        $('.time-' + className + '-' + id).addClass('display-none');
        $('.input-time-' + className + '-' + id).removeClass('display-none');
        $('.amount-' + className + '-' + id).addClass('display-none');
        $('.input-amount-' + className + '-' + id).removeClass('display-none');
        $('.description-' + className + '-' + id).addClass('display-none');
        $('.input-description-' + className + '-' + id).removeClass('display-none');
    } else {
        setTimeout(function () {
            input = $('.input-content-' + className + '-' + id)
            input.focus();
            var tmpStr = input.val();
            input.val('');
            input.val(tmpStr);
        }, 0);
        $('.content-' + className + '-' + id).addClass('display-none');
        $('.input-content-' + className + '-' + id).removeClass('display-none');
        $('.expected_date-' + className + '-' + id).addClass('display-none');
        $('.input-expected_date-' + className + '-' + id).removeClass('display-none');
    }
}

function refreshWorkorderApproved(className, element, type) {
    type = assignDefaultValue(type);
    id = $(element).data('id');
    $('.error-validate-' + className + '-' + id).remove();
    $(element).addClass('display-none');
    $('.save-' + className + '-' + id).addClass('display-none');
    $('.edit-' + className + '-' + id).removeClass('display-none');
    $('.delete-' + className + '-' + id).removeClass('display-none');
    $('.refresh-' + className + '-' + id).addClass('display-none');

    if (type == TYPE_DELIVERABLE) {
        $('.title-' + className + '-' + id).removeClass('display-none');
        $('.input-title-' + className + '-' + id).val($('.title-' + className + '-' + id).text());
        $('.input-title-' + className + '-' + id).addClass('display-none');

        $('.committed_date-' + className + '-' + id).removeClass('display-none');
        $('.input-committed_date-' + className + '-' + id).val($('.committed_date-' + className + '-' + id).text());
        $('.input-committed_date-' + className + '-' + id).addClass('display-none');
        $('.re_commited_date-' + className + '-' + id).removeClass('display-none');
        $('.input-re_commited_date-' + className + '-' + id).val($('.re_commited_date-' + className + '-' + id).text());
        $('.input-re_commited_date-' + className + '-' + id).addClass('display-none');

        $('.actual_date-' + className + '-' + id).removeClass('display-none');
        $('.input-actual_date-' + className + '-' + id).addClass('display-none');
        $('.input-actual_date-' + className + '-' + id).val($('.actual_date-' + className + '-' + id).text());

        $('.stage-' + className + '-' + id).removeClass('display-none');
        $('.change_request_by-' + className + '-' + id).removeClass('display-none');
        dataValue = $('.stage-' + className + '-' + id).data('value');
        $('.input-stage-' + className + '-' + id).addClass('display-none');
        $('.input-change_request_by-' + className + '-' + id).addClass('display-none');
        if (dataValue) {
            $('.input-stage-' + className + '-' + id).val(dataValue).trigger("change");
        } else {
            $('.input-stage-' + className + '-' + id).val($('.input-stage-' + className + '-' + id + ' option:first').val()).trigger("change");
        }
        dataChangeRequest = $('.change_request_by-' + className + '-' + id).data('value');
        if (dataChangeRequest) {
            $('.input-change_request_by-' + className + '-' + id).val(dataChangeRequest).trigger("change");
        } else {
            $('.input-change_request_by-' + className + '-' + id).val($('.input-change_request_by-' + className + '-' + id + ' option:first').val()).trigger("change");
        }

        $('.note-' + className + '-' + id).removeClass('display-none');
        $('.input-note-' + className + '-' + id).addClass('display-none');
        $('.input-note-' + className + '-' + id).val($('.note-' + className + '-' + id).text());
    } else if (type == TYPE_STAGE_AND_MILESTONE) {
        $('.stage-' + className + '-' + id).removeClass('display-none');
        $('.input-stage-' + className + '-' + id).addClass('display-none');
        $('.input-stage-' + className + '-' + id).val($('.stage-' + className + '-' + id).text());

        $('.description-' + className + '-' + id).removeClass('display-none');
        $('.input-description-' + className + '-' + id).addClass('display-none');
        $('.input-description-' + className + '-' + id).val($('.description-' + className + '-' + id).text());

        $('.milestone-' + className + '-' + id).removeClass('display-none');
        $('.input-milestone-' + className + '-' + id).addClass('display-none');
        $('.input-milestone-' + className + '-' + id).val($('.milestone-' + className + '-' + id).text());

        $('.qua_gate_actual-' + className + '-' + id).removeClass('display-none');
        $('.input-qua_gate_actual-' + className + '-' + id).addClass('display-none');
        if ($('.qua_gate_actual-' + className + '-' + id).data('status')) {
            $('.input-qua_gate_actual-' + className + '-' + id).val($('.qua_gate_actual-' + className + '-' + id).text());
        } else {
            $('.input-qua_gate_actual-' + className + '-' + id).val('');
        }

        $('.qua_gate_plan-' + className + '-' + id).removeClass('display-none');
        $('.input-qua_gate_plan-' + className + '-' + id).addClass('display-none');
        $('.input-qua_gate_plan-' + className + '-' + id).val($('.qua_gate_plan-' + className + '-' + id).text());

        if ($('.qua_gate_actual-' + className + '-' + id).data('status')) {
            text = $('.qua_gate_result-' + className + '-' + id).data('value') ? 'Pass' : 'Fail';
            $('.qua_gate_result-' + className + '-' + id).text(text);
        }

        $('.qua_gate_result-' + className + '-' + id).removeClass('display-none');
        $('.span-qua_gate_result-' + className + '-' + id).addClass('display-none');
    } else if (type == TYPE_TRAINING) {
        $('.topic-' + className + '-' + id).removeClass('display-none');
        $('.input-topic-' + className + '-' + id).addClass('display-none');
        $('.input-topic-' + className + '-' + id).val($('.topic-' + className + '-' + id).text());

        $('.description-' + className + '-' + id).removeClass('display-none');
        $('.input-description-' + className + '-' + id).addClass('display-none');
        $('.input-description-' + className + '-' + id).val($('.description-' + className + '-' + id).text());

        $('.participants-' + className + '-' + id).removeClass('display-none');
        $('.input-participants-' + className + '-' + id).addClass('display-none');
        oldValue = $('.participants-' + className + '-' + id).attr('data-value');
        arrayValue = oldValue.split(',');
        $('.input-participants-' + className + '-' + id).val(arrayValue);
        $('.start_at-' + className + '-' + id).removeClass('display-none');
        $('.input-start_at-' + className + '-' + id).addClass('display-none');
        $('.input-start_at-' + className + '-' + id).val($('.start_at-' + className + '-' + id).text());

        $('.end_at-' + className + '-' + id).removeClass('display-none');
        $('.input-end_at-' + className + '-' + id).addClass('display-none');
        $('.input-end_at-' + className + '-' + id).val($('.end_at-' + className + '-' + id).text());

        $('.result-' + className + '-' + id).removeClass('display-none');
        $('.input-result-' + className + '-' + id).addClass('display-none');
        oldValueResult = $('.result-' + className + '-' + id).attr('data-value');
        $('.input-result-' + className + '-' + id).val(oldValueResult);

        $('.walver_criteria-' + className + '-' + id).removeClass('display-none');
        $('.input-walver_criteria-' + className + '-' + id).addClass('display-none');
        $('.input-walver_criteria-' + className + '-' + id).val($('.walver_criteria-' + className + '-' + id).text());
    } else if (type == TYPE_COMMUNICATION_MEETING || type == TYPE_COMMUNICATION_REPORT || type == TYPE_COMMUNICATION_OTHER) {
        $('.type-' + className + '-' + id).removeClass('display-none');
        $('.input-type-' + className + '-' + id).addClass('display-none');
        $('.input-type-' + className + '-' + id).val($('.type-' + className + '-' + id).text());

        $('.method-' + className + '-' + id).removeClass('display-none');
        $('.input-method-' + className + '-' + id).addClass('display-none');
        $('.input-method-' + className + '-' + id).val($('.method-' + className + '-' + id).text());

        $('.time-' + className + '-' + id).removeClass('display-none');
        $('.input-time-' + className + '-' + id).addClass('display-none');
        $('.input-time-' + className + '-' + id).val($('.time-' + className + '-' + id).text());

        $('.information-' + className + '-' + id).removeClass('display-none');
        $('.input-information-' + className + '-' + id).addClass('display-none');
        $('.input-information-' + className + '-' + id).val($('.information-' + className + '-' + id).text());

        $('.stakeholder-' + className + '-' + id).removeClass('display-none');
        $('.input-stakeholder-' + className + '-' + id).addClass('display-none');
        $('.input-stakeholder-' + className + '-' + id).val($('.stakeholder-' + className + '-' + id).text());
    } else if (type == TYPE_MEMBER_COMMUNICATION) {
        $('.contact_address-' + className + '-' + id).removeClass('display-none');
        $('.input-contact_address-' + className + '-' + id).addClass('display-none');
        $('.input-contact_address-' + className + '-' + id).val($('.contact_address-' + className + '-' + id).text());

        $('.role-' + className + '-' + id).removeClass('display-none');
        $('.input-role-' + className + '-' + id).addClass('display-none');
        oldValueRole = $('.participants-' + className + '-' + id).attr('data-value');
        arrayValue = oldValueRole.split(',');
        $('.input-role-' + className + '-' + id).val(arrayValue);

        $('.member-' + className + '-' + id).removeClass('display-none');
        $('.input-member-' + className + '-' + id).addClass('display-none');
        oldValue = $('.member-' + className + '-' + id).attr('data-value');
        $('.input-member-' + className + '-' + id).val(oldValue);

        $('.responsibility-' + className + '-' + id).removeClass('display-none');
        $('.input-responsibility-' + className + '-' + id).addClass('display-none');
        $('.input-responsibility-' + className + '-' + id).val($('.responsibility-' + className + '-' + id).text());
    } else if (type == TYPE_CUSTOMER_COMMUNICATION) {
        $('.contact_address-' + className + '-' + id).removeClass('display-none');
        $('.input-contact_address-' + className + '-' + id).addClass('display-none');
        $('.input-contact_address-' + className + '-' + id).val($('.contact_address-' + className + '-' + id).text());

        $('.role-' + className + '-' + id).removeClass('display-none');
        $('.input-role-' + className + '-' + id).addClass('display-none');
        oldValueRole = $('.participants-' + className + '-' + id).attr('data-value');
        arrayValue = oldValueRole.split(',');
        $('.input-role-' + className + '-' + id).val(arrayValue);

        $('.customer-' + className + '-' + id).removeClass('display-none');
        $('.input-customer-' + className + '-' + id).addClass('display-none');
        $('.input-customer-' + className + '-' + id).val($('.customer-' + className + '-' + id).text());

        $('.responsibility-' + className + '-' + id).removeClass('display-none');
        $('.input-responsibility-' + className + '-' + id).addClass('display-none');
        $('.input-responsibility-' + className + '-' + id).val($('.responsibility-' + className + '-' + id).text());
    } else if (type == TYPE_SECURITY) {
        $('.content-' + className + '-' + id).removeClass('display-none');
        $('.input-content-' + className + '-' + id).addClass('display-none');
        $('.input-content-' + className + '-' + id).val($('.content-' + className + '-' + id).text());

        $('.description-' + className + '-' + id).removeClass('display-none');
        $('.input-description-' + className + '-' + id).addClass('display-none');
        $('.input-description-' + className + '-' + id).val($('.description-' + className + '-' + id).text());

        $('.participants-' + className + '-' + id).removeClass('display-none');
        $('.input-participants-' + className + '-' + id).addClass('display-none');
        oldValue = $('.participants-' + className + '-' + id).attr('data-value');
        arrayValue = oldValue.split(',');
        $('.input-participants-' + className + '-' + id).val(arrayValue);
        $('.period-' + className + '-' + id).removeClass('display-none');
        $('.input-period-' + className + '-' + id).addClass('display-none');
        $('.input-period-' + className + '-' + id).val($('.period-' + className + '-' + id).text());

        $('.procedure-' + className + '-' + id).removeClass('display-none');
        $('.input-procedure-' + className + '-' + id).addClass('display-none');
        $('.input-procedure-' + className + '-' + id).val($('.procedure-' + className + '-' + id).text());
    } else if (type == TYPE_ASSUMPTIONS || type == TYPE_CONSTRAINTS) {
        $('.description-' + className + '-' + id).removeClass('display-none');
        $('.input-description-' + className + '-' + id).addClass('display-none');
        $('.input-description-' + className + '-' + id).val($('.description-' + className + '-' + id).text());

        $('.remark-' + className + '-' + id).removeClass('display-none');
        $('.input-remark-' + className + '-' + id).addClass('display-none');
        $('.input-remark-' + className + '-' + id).val($('.remark-' + className + '-' + id).text());
    } else if (type == TYPE_SKILL_REQUEST) {
        $('.skill-' + className + '-' + id).removeClass('display-none');
        $('.input-skill-' + className + '-' + id).addClass('display-none');
        $('.input-skill-' + className + '-' + id).val($('.skill-' + className + '-' + id).text());

        $('.category-' + className + '-' + id).removeClass('display-none');
        $('.input-category-' + className + '-' + id).addClass('display-none');
        $('.input-category-' + className + '-' + id).val($('.category-' + className + '-' + id).text());

        $('.course_name-' + className + '-' + id).removeClass('display-none');
        $('.input-course_name-' + className + '-' + id).addClass('display-none');
        $('.input-course_name-' + className + '-' + id).val($('.course_name-' + className + '-' + id).text());

        $('.mode-' + className + '-' + id).removeClass('display-none');
        $('.input-mode-' + className + '-' + id).addClass('display-none');
        $('.input-mode-' + className + '-' + id).val($('.mode-' + className + '-' + id).text());

        $('.provider-' + className + '-' + id).removeClass('display-none');
        $('.input-provider-' + className + '-' + id).addClass('display-none');
        $('.input-provider-' + className + '-' + id).val($('.provider-' + className + '-' + id).text());

        $('.required_role-' + className + '-' + id).removeClass('display-none');
        $('.input-required_role-' + className + '-' + id).addClass('display-none');
        $('.input-required_role-' + className + '-' + id).val($('.required_role-' + className + '-' + id).text());

        $('.level-' + className + '-' + id).removeClass('display-none');
        $('.input-level-' + className + '-' + id).addClass('display-none');
        $('.input-level-' + className + '-' + id).val($('.level-' + className + '-' + id).text());

        $('.hours-' + className + '-' + id).removeClass('display-none');
        $('.input-hours-' + className + '-' + id).addClass('display-none');
        $('.input-hours-' + className + '-' + id).val($('.hours-' + className + '-' + id).text());

        $('.remark-' + className + '-' + id).removeClass('display-none');
        $('.input-remark-' + className + '-' + id).addClass('display-none');
        $('.input-remark-' + className + '-' + id).val($('.remark-' + className + '-' + id).text());
    } else if (type == TYPE_PROJECT_MEMBER) {
        $('.type-' + className + '-' + id).removeClass('display-none');
        $('.input-type-' + className + '-' + id).addClass('display-none');
        $('.input-type-' + className + '-' + id).val($('.type-' + className + '-' + id).attr('data-value'));

        $('.employee_id-' + className + '-' + id).removeClass('display-none');
        $('.input-employee_id-' + className + '-' + id).addClass('display-none');
        $('.input-employee_id-' + className + '-' + id).val($('.employee_id-' + className + '-' + id).attr('data-value'));

        $('.start_at-' + className + '-' + id).removeClass('display-none');
        $('.input-start_at-' + className + '-' + id).addClass('display-none');
        $('.input-start_at-' + className + '-' + id).val($('.start_at-' + className + '-' + id).text());

        $('.end_at-' + className + '-' + id).removeClass('display-none');
        $('.input-end_at-' + className + '-' + id).addClass('display-none');
        $('.input-end_at-' + className + '-' + id).val($('.end_at-' + className + '-' + id).text());

        $('.effort-' + className + '-' + id).removeClass('display-none');
        $('.input-effort-' + className + '-' + id).addClass('display-none');
        $('.input-effort-' + className + '-' + id).val($('.effort-' + className + '-' + id).text());

        $('.prog-' + className + '-' + id).removeClass('display-none');
        $('.input-prog-' + className + '-' + id).addClass('display-none');
        var valueProg = $('.prog-' + className + '-' + id).attr('data-value').split(',').map(Number);
        $('select.input-prog-' + className + '-' + id)
            .multiselect('deselectAll')
            .multiselect('select', valueProg);
        $('.tr-project-' + id + ' .proj-member-prog-lang > .btn-group').addClass('display-none');
    } else if (type == TYPE_COMMUNICATION) {
        $('.content-' + className + '-' + id).removeClass('display-none');
        $('.input-content-' + className + '-' + id).addClass('display-none');
        $('.input-content-' + className + '-' + id).val($('.content-' + className + '-' + id).text());
    } else if (type == TYPE_EXTERNAL_INTERFACE) {
        $('.name-' + className + '-' + id).removeClass('display-none');
        $('.input-name-' + className + '-' + id).addClass('display-none');
        $('.input-name-' + className + '-' + id).val($('.name-' + className + '-' + id).text());

        $('.responsibilities-' + className + '-' + id).removeClass('display-none');
        $('.input-responsibilities-' + className + '-' + id).addClass('display-none');
        $('.input-responsibilities-' + className + '-' + id).val($('.responsibilities-' + className + '-' + id).text());

        $('.position-' + className + '-' + id).removeClass('display-none');
        $('.input-position-' + className + '-' + id).addClass('display-none');
        $('.input-position-' + className + '-' + id).val($('.position-' + className + '-' + id).text());

        $('.contact-' + className + '-' + id).removeClass('display-none');
        $('.input-contact-' + className + '-' + id).addClass('display-none');
        $('.input-contact-' + className + '-' + id).val($('.contact-' + className + '-' + id).text());
    } else if (type == TYPE_TOOL_AND_INFRASTRUCTURE) {
        $('.soft_hard_ware-' + className + '-' + id).removeClass('display-none');
        $('.input-soft_hard_ware-' + className + '-' + id).addClass('display-none');
        $('.input-soft_hard_ware-' + className + '-' + id).val($('.soft_hard_ware-' + className + '-' + id).text());

        $('.purpose-' + className + '-' + id).removeClass('display-none');
        $('.input-purpose-' + className + '-' + id).addClass('display-none');
        $('.input-purpose-' + className + '-' + id).val($('.purpose-' + className + '-' + id).text());

        $('.start-date-' + className + '-' + id).removeClass('display-none');
        $('.input-start-date-' + className + '-' + id).addClass('display-none');
        $('.input-start-date-' + className + '-' + id).val($('.start-date-' + className + '-' + id).text());

        $('.end-date-' + className + '-' + id).removeClass('display-none');
        $('.input-end-date-' + className + '-' + id).addClass('display-none');
        $('.input-end-date-' + className + '-' + id).val($('.end-date-' + className + '-' + id).text());

        $('.note-' + className + '-' + id).removeClass('display-none');
        $('.input-note-' + className + '-' + id).addClass('display-none');
        $('.input-note-' + className + '-' + id).val($('.note-' + className + '-' + id).text());
    } else if (type == TYPE_WO_DEVICES_EXPENSE) {
        $('.time-' + className + '-' + id).removeClass('display-none');
        $('.input-time-' + className + '-' + id).addClass('display-none');
        $('.input-stime-' + className + '-' + id).val($('.time-' + className + '-' + id).text());

        $('.amount-' + className + '-' + id).removeClass('display-none');
        $('.input-amount-' + className + '-' + id).addClass('display-none');
        $('.input-amount-' + className + '-' + id).val($('.amount-' + className + '-' + id).text());

        $('.description-' + className + '-' + id).removeClass('display-none');
        $('.input-description-' + className + '-' + id).addClass('display-none');
        $('.input-description-' + className + '-' + id).val($('.description-' + className + '-' + id).text());

    } else {
        $('.content-' + className + '-' + id).removeClass('display-none');
        $('.input-content-' + className + '-' + id).addClass('display-none');
        $('.input-content-' + className + '-' + id).val($('.content-' + className + '-' + id).text());

        $('.expected_date-' + className + '-' + id).removeClass('display-none');
        $('.input-expected_date-' + className + '-' + id).addClass('display-none');
        $('.input-expected_date-' + className + '-' + id).val($('.expected_date-' + className + '-' + id).text());
    }
}

function saveWorkorderApproved(className, e, url, type) {
    type = assignDefaultValue(type);
    id = $(e).data('id');
    status = $(e).data('status');
    $('.error-validate-' + className + '-' + id).remove();
    $('.input-' + className + '-' + id).removeClass('error');
    if ($(e).data('requestRunning')) {
        return;
    }
    $(e).data('requestRunning', true);
    $(e).addClass('display-none');
    $('#loading-item-' + type + '-' + id).removeClass('display-none');
    if (type == TYPE_DELIVERABLE) {
        stageOld = $(e).attr('data-stage');
        title = $('.input-title-' + className + '-' + id).val();
        committed_date = $('.input-committed_date-' + className + '-' + id).val();
        re_commited_date = $('.input-re_commited_date-' + className + '-' + id).val();
        actual_date = $('.input-actual_date-' + className + '-' + id).val();
        change_request_by = $('.input-change_request_by-' + className + '-' + id).val();
        stage = $('.input-stage-' + className + '-' + id).val();
        note = $('.input-note-' + className + '-' + id).val();
        data = {
            _token: token,
            title_1: title,
            committed_date_1: committed_date,
            re_commited_date_1: re_commited_date,
            actual_date_1: actual_date,
            change_request_by_1: change_request_by,
            stage_1: stage,
            note_1: note,
            number_record: 1,
            isEdit: true,
            status: status,
            project_id: project_id,
            id: id
        };
    } else if (type == TYPE_STAGE_AND_MILESTONE) {
        stage = $('.input-stage-' + className + '-' + id).val();
        stageText = $('.input-stage-' + className + '-' + id+ ' option[value='+ stage +']').text();
        description = $('.input-description-' + className + '-' + id).val();
        milestone = $('.input-milestone-' + className + '-' + id).val();
        qua_gate_actual = $('.input-qua_gate_actual-' + className + '-' + id).val();
        qua_gate_plan = $('.input-qua_gate_plan-' + className + '-' + id).val();
        qua_gate_result = $('.span-qua_gate_result-' + className + '-' + id + ' input[name=qua_gate_result-' + id + ']:checked').val();
        data = {
            _token: token,
            stage_1: stage,
            description_1: description,
            milestone_1: milestone,
            qua_gate_actual_1: qua_gate_actual,
            qua_gate_plan_1: qua_gate_plan,
            qua_gate_result_1: qua_gate_result,
            number_record: 1,
            isEdit: true,
            status: status,
            project_id: project_id,
            id: id
        };
    } else if (type == TYPE_ASSUMPTIONS) {
        description = $('.input-description-' + className + '-' + id).val();
        remark = $('.input-remark-' + className + '-' + id).val();
        data = {
            _token: token,
            description_1: description,
            remark_1: remark,
            number_record: 1,
            isEdit: true,
            status: status,
            type: typeAssumption,
            project_id: project_id,
            id: id
        };
    } else if (type == TYPE_COMMUNICATION_MEETING) {
        typeProj = $('.input-type-' + className + '-' + id).val();
        method = $('.input-method-' + className + '-' + id).val();
        time = $('.input-time-' + className + '-' + id).val();
        information = $('.input-information-' + className + '-' + id).val();
        stakeholder = $('.input-stakeholder-' + className + '-' + id).val();
        data = {
            _token: token,
            type_1: typeProj,
            method_1: method,
            time_1: time,
            information_1: information,
            stakeholder_1: stakeholder,
            number_record: 1,
            isEdit: true,
            status: status,
            type_task: typeMeetingCom,
            project_id: project_id,
            id: id
        };
    } else if (type == TYPE_COMMUNICATION_REPORT) {
        typeProj = $('.input-type-' + className + '-' + id).val();
        method = $('.input-method-' + className + '-' + id).val();
        time = $('.input-time-' + className + '-' + id).val();
        information = $('.input-information-' + className + '-' + id).val();
        stakeholder = $('.input-stakeholder-' + className + '-' + id).val();
        data = {
            _token: token,
            type_1: typeProj,
            method_1: method,
            time_1: time,
            information_1: information,
            stakeholder_1: stakeholder,
            number_record: 1,
            isEdit: true,
            status: status,
            type_task: typeReportCom,
            project_id: project_id,
            id: id
        };
    } else if (type == TYPE_COMMUNICATION_OTHER) {
        typeProj = $('.input-type-' + className + '-' + id).val();
        method = $('.input-method-' + className + '-' + id).val();
        time = $('.input-time-' + className + '-' + id).val();
        information = $('.input-information-' + className + '-' + id).val();
        stakeholder = $('.input-stakeholder-' + className + '-' + id).val();
        data = {
            _token: token,
            type_1: typeProj,
            method_1: method,
            time_1: time,
            information_1: information,
            stakeholder_1: stakeholder,
            number_record: 1,
            isEdit: true,
            status: status,
            type_task: typeOtherCom,
            project_id: project_id,
            id: id
        };
    } else if (type == TYPE_CONSTRAINTS) {
        description = $('.input-description-' + className + '-' + id).val();
        remark = $('.input-remark-' + className + '-' + id).val();
        data = {
            _token: token,
            description_1: description,
            remark_1: remark,
            number_record: 1,
            isEdit: true,
            status: status,
            type: typeConstraints,
            project_id: project_id,
            id: id
        };
    } else if (type == TYPE_SKILL_REQUEST) {
        skill = $('.input-skill-' + className + '-' + id).val();
        category = $('.input-category-' + className + '-' + id).val();
        course_name = $('.input-course_name-' + className + '-' + id).val();
        mode = $('.input-mode-' + className + '-' + id).val();
        provider = $('.input-provider-' + className + '-' + id).val();
        required_role = $('.input-required_role-' + className + '-' + id).val();
        hours = $('.input-hours-' + className + '-' + id).val();
        level = $('.input-level-' + className + '-' + id).val();
        remark = $('.input-remark-' + className + '-' + id).val();
        data = {
            _token: token,
            skill_1: skill,
            category_1: category,
            course_name_1: course_name,
            mode_1: mode,
            provider_1: provider,
            required_role_1: required_role,
            hours_1: hours,
            level_1: level,
            remark_1: remark,
            number_record: 1,
            isEdit: true,
            project_id: project_id,
            id: id
        };
    } else if (type == TYPE_TRAINING) {
        topic = $('.input-topic-' + className + '-' + id).val();
        description = $('.input-description-' + className + '-' + id).val();
        participants = $('.input-participants-' + className + '-' + id).val();
        start_at = $('.input-start_at-' + className + '-' + id).val();
        end_at = $('.input-end_at-' + className + '-' + id).val();
        result = $('.input-result-' + className + '-' + id).val();
        time = $('.input-time-' + className + '-' + id).val();
        walver_criteria = $('.input-walver_criteria-' + className + '-' + id).val();
        data = {
            _token: token,
            topic_1: topic,
            description_1: description,
            member_1: participants,
            start_at_1: start_at,
            end_at_1: end_at,
            result: result,
            walver_criteria_1: walver_criteria,
            number_record: 1,
            isEdit: true,
            status: status,
            project_id: project_id,
            id: id
        };
    } else if (type == TYPE_MEMBER_COMMUNICATION) {
        employee = $('.input-member-' + className + '-' + id).val();
        role = $('.input-role-' + className + '-' + id).val();
        contact_address = $('.input-contact_address-' + className + '-' + id).val();
        responsibility = $('.input-responsibility-' + className + '-' + id).val();
        data = {
            _token: token,
            employee_1: employee,
            role_1: role,
            contact_address_1: contact_address,
            responsibility_1: responsibility,
            type: typeMemberCommunication,
            number_record: 1,
            isEdit: true,
            project_id: project_id,
            id: id
        };
    } else if (type == TYPE_CUSTOMER_COMMUNICATION) {
        employee = $('.input-customer-' + className + '-' + id).val();
        role = $('.input-role-' + className + '-' + id).val();
        contact_address = $('.input-contact_address-' + className + '-' + id).val();
        responsibility = $('.input-responsibility-' + className + '-' + id).val();
        data = {
            _token: token,
            employee_1: employee,
            role_1: role,
            contact_address_1: contact_address,
            responsibility_1: responsibility,
            type: typeCustomerCommunication,
            number_record: 1,
            isEdit: true,
            project_id: project_id,
            id: id
        };
    } else if (type == TYPE_SECURITY) {
        content = $('.input-content-' + className + '-' + id).val();
        description = $('.input-description-' + className + '-' + id).val();
        participants = $('.input-participants-' + className + '-' + id).val();
        period = $('.input-period-' + className + '-' + id).val();
        procedure = $('.input-procedure-' + className + '-' + id).val();
        data = {
            _token: token,
            content_1: content,
            description_1: description,
            member_1: participants,
            period_1: period,
            procedure_1: procedure,
            number_record: 1,
            isEdit: true,
            project_id: project_id,
            id: id
        };
    } else if (type == TYPE_PROJECT_MEMBER) {
        typeMember = $('.input-type-' + className + '-' + id).val();
        employee_id = $('.input-employee_id-' + className + '-' + id).val();
        start_at = $('.input-start_at-' + className + '-' + id).val();
        end_at = $('.input-end_at-' + className + '-' + id).val();
        effort = $('.input-effort-' + className + '-' + id).val();
        projMemberProgLangs = $('select.input-prog-' + className + '-' + id).length ?
            $('select.input-prog-' + className + '-' + id).val() :
            $('input.input-prog-' + className + '-' + id).val();
        data = {
            _token: token,
            type: typeMember,
            employee_id: employee_id,
            start_at: start_at,
            end_at: end_at,
            effort: effort,
            number_record: 1,
            isEdit: true,
            status: status,
            project_id: project_id,
            id: id,
            prog_langs: projMemberProgLangs
        };
    } else if (type == TYPE_COMMUNICATION) {
        content = $('.input-content-' + className + '-' + id).val();
        data = {
            _token: token,
            content_1: content,
            number_record: 1,
            isEdit: true,
            status: status,
            project_id: project_id,
            id: id
        };
    } else if (type == TYPE_EXTERNAL_INTERFACE) {
        name = $('.input-name-' + className + '-' + id).val();
        position = $('.input-position-' + className + '-' + id).val();
        responsibilities = $('.input-responsibilities-' + className + '-' + id).val();
        contact = $('.input-contact-' + className + '-' + id).val();
        data = {
            _token: token,
            name_1: name,
            position_1: position,
            responsibilities_1: responsibilities,
            contact_1: contact,
            number_record: 1,
            isEdit: true,
            status: status,
            project_id: project_id,
            id: id
        };
    } else if (type == TYPE_TOOL_AND_INFRASTRUCTURE) {
        soft_ware_id = $('.input-soft_hard_ware-' + className + '-' + id).val();
        soft_hard_ware = $('.input-soft_hard_ware-' + className + '-' + id+ ' option[value='+ soft_ware_id +']').text();
        purpose = $('.input-purpose-' + className + '-' + id).val();
        start_date = $('.input-start-date-' + className + '-' + id).val();
        end_date = $('.input-end-date-' + className + '-' + id).val();
        note = $('.input-note-' + className + '-' + id).val();
        data = {
            _token: token,
            soft_hard_ware_1: soft_hard_ware,
            soft_ware_id_1: soft_ware_id,
            purpose_1: purpose,
            start_date_1: start_date,
            end_date_1: end_date,
            note_1: note,
            number_record: 1,
            isEdit: true,
            status: status,
            project_id: project_id,
            id: id
        };
        globalData = data;
        globalurl = url;
        globalClassName = className;
    } else if (type == TYPE_WO_DEVICES_EXPENSE) {
        date = $('.input-time-' + className + '-' + id).val();
        amount = $('.input-amount-' + className + '-' + id).val().split(",");
        var inputUpdatedPoint = parseFloat(amount[0].split(".").join(""));
        description = $('.input-description-' + className + '-' + id).val();
        data = {
            _token: token,
            time_1: date,
            amount_1: inputUpdatedPoint,
            purpose_1: purpose,
            description_1: description,
            number_record: 1,
            isEdit: true,
            project_id: project_id,
            id: id
        };

    } else {
        content = $('.input-content-' + className + '-' + id).val();
        expected_date = $('.input-expected_date-' + className + '-' + id).val();
        data = {
            _token: token,
            content_1: content,
            expected_date_1: expected_date,
            number_record: 1,
            isEdit: true,
            status: status,
            project_id: project_id,
            id: id
        };
    }
    $.ajax({
        url: url,
        type: 'post',
        data: data,
        dataType: 'json',
        success: function (data) {
            if (data.status) {
                if(type == TYPE_DELIVERABLE) {
                    btnDeleteDeliverable = $('.delete-deliverable');
                    countDeliver = 0;

                    btnDeleteDeliverable.each(function() {
                        if ($(this).attr('data-stage') == stageOld) {
                            countDeliver++;
                        }
                    });
                }
                showOrHideButtonSubmitWorkorder(data.isCheckShowSubmit);
                dataSort = getDataColumSort(type);
                $('.workorder-' + className).html(data.content);
                $('[data-toggle="tooltip"], .tooltip').tooltip("hide");
                if (type == TYPE_PROJECT_MEMBER) {
                    $('.employee_id_select2').select2().on('select2:close', function(evt) { tabToChange (evt)});
                    $('.tr-project .type-project-member').select2().on('select2:close', function(evt) { tabToChange (evt)});
                    iniTableSorter(dataSort);
                    if (data.effort) {
                        $('.team-size').html(data.effort.count);
                        $('.effort-usage').html(data.effort.total);
                        $('.effort-dev').html(data.effort.dev);
                        $('.effort-pm').html(data.effort.pm);
                        $('.effort-qa').html(data.effort.qa);
                    }
                }

                if (type == TYPE_ASSUMPTIONS) {
                    $('#workorder_assumptions').html(data.content);
                }
                if (type == TYPE_CONSTRAINTS) {
                    $('#workorder_constraints').html(data.content);
                }
                if (type == TYPE_CRITICAL_DEPENDENCIES) {
                    $('#workorder_critical-dependencies').html(data.content);
                }
                if (type == TYPE_SKILL_REQUEST) {
                    $('#workorder_skill_request').html(data.content);
                }
                if (type == TYPE_MEMBER_COMMUNICATION) {
                    $('#workorder_member_communication').html(data.content);
                }
                if (type == TYPE_COMMUNICATION_MEETING || type == TYPE_COMMUNICATION_REPORT || type == TYPE_COMMUNICATION_OTHER) {
                    $('#workorder_communication').html(data.content);
                }
                if (type == TYPE_CUSTOMER_COMMUNICATION) {
                    $('#workorder_customer_communication').html(data.content);
                }
                if (type == TYPE_STAGE_AND_MILESTONE) {
                    $('.option-select-stage-' + id).text(stageText);
                    $('.stage-name-' + id).text(stageText);
                    $('.select-stage').select2().on('select2:close', function(evt) { tabToChange (evt)});
                    classStageName = $('.stage-name-' + id);
                    classStageName.each(function(index, el) {
                        if ($(this).hasClass('display-none')) {
                            $(this).parent('.td-deliverable').find('.select2-container').addClass('display-block select2-container--focus');
                        }
                    });
                }
                if (type == TYPE_DELIVERABLE) {
                    $('.select-stage-deliverable').select2().on('select2:close', function(evt) { tabToChange (evt)});
                    if(countDeliver <= 1) {
                        $('.delete-stage-and-milestone-' + stageOld).removeClass('display-none');
                    }
                    $('.delete-stage-and-milestone-' + stage).addClass('display-none');
                }
                if (type == TYPE_WO_DEVICES_EXPENSE) {
                    $('#table-derived-expenses input.time-datepicker').datepicker({
                        format: 'yyyy-mm',
                        weekStart: 1,
                        todayHighlight: true,
                        autoclose: true,
                        viewMode: "months",
                        minViewMode: "months"
                    });
                }
            } else {
                if (data.message_error) {
                    if (data.message_error.title_1) {
                        $('.input-title-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="content">' + data.message_error.title_1[0] + '</p>');
                    }
                    if (data.message_error.committed_date_1) {
                        $('.input-committed_date-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="content">' + data.message_error.committed_date_1[0] + '</p>');
                    }
                    if (data.message_error.actual_date_1) {
                        $('.input-actual_date-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="content">' + data.message_error.actual_date_1[0] + '</p>');
                    }
                    if (data.message_error.re_commited_date_1) {
                        $('.input-re_commited_date-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="content">' + data.message_error.re_commited_date_1[0] + '</p>');
                    }
                    if (data.message_error.change_request_by_1) {
                        $('.input-change_request_by-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="content">' + data.message_error.change_request_by_1[0] + '</p>');
                    }
                    if (data.message_error.note_1) {
                        $('.input-note-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="content">' + data.message_error.note_1[0] + '</p>');
                    }
                    if (data.message_error.stage_1) {
                        $('.tr-stage-' + id +' .td-stage .select2-container').after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="content">' + data.message_error.stage_1[0] + '</p>');
                    }
                    if (data.message_error.content_1) {
                        $('.input-content-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="content">' + data.message_error.content_1[0] + '</p>');
                    }
                    if (data.message_error.description_1) {
                        $('.input-description-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="description">' + data.message_error.description_1[0] + '</p>');
                    }
                    if (data.message_error.topic_1) {
                        $('.input-topic-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="topic">' + data.message_error.topic_1[0] + '</p>');
                    }
                    if (data.message_error.skill_1) {
                        $('.input-skill-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="skill">' + data.message_error.skill_1[0] + '</p>');
                    }
                    if (data.message_error.category_1) {
                        $('.input-category-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="category">' + data.message_error.category_1[0] + '</p>');
                    }
                    if (data.message_error.course_name_1) {
                        $('.input-course_name-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="course_name">' + data.message_error.course_name_1[0] + '</p>');
                    }
                    if (data.message_error.mode_1) {
                        $('.input-mode-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="mode">' + data.message_error.mode_1[0] + '</p>');
                    }
                    if (data.message_error.provider_1) {
                        $('.input-provider-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="provider">' + data.message_error.provider_1[0] + '</p>');
                    }
                    if (data.message_error.required_role_1) {
                        $('.input-required_role-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="required_role">' + data.message_error.required_role_1[0] + '</p>');
                    }
                    if (data.message_error.hours_1) {
                        $('.input-hours-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="hours">' + data.message_error.hours_1[0] + '</p>');
                    }
                    if (data.message_error.level_1) {
                        $('.input-level-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="level">' + data.message_error.level_1[0] + '</p>');
                    }
                    if (data.message_error.remark_1) {
                        $('.input-remark-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="remark">' + data.message_error.remark_1[0] + '</p>');
                    }
                    if (data.message_error.customer_1) {
                        $('.input-customer-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="customer">' + data.message_error.customer_1[0] + '</p>');
                    }
                    if (data.message_error.participants_1) {
                        $('.input-participants-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="participants">' + data.message_error.participants_1[0] + '</p>');
                    }
                    if (data.message_error.role_member_communication_1) {
                        $('.input-role-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="member_communication">' + data.message_error.role_member_communication_1[0] + '</p>')
                    }
                    if (data.message_error.role_customer_communication_1) {
                        $('.input-role-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="customer_communication">' + data.message_error.role_customer_communication_1[0] + '</p>')
                    }
                    if (data.message_error.time_1) {
                        $('.input-time-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="time">' + data.message_error.time_1[0] + '</p>');
                    }
                    if (data.message_error.milestone_1) {
                        $('.input-milestone-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="milestone">' + data.message_error.milestone_1[0] + '</p>');
                    }
                    if (data.message_error.walver_criteria_1) {
                        $('.input-walver_criteria-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="walver_criteria">' + data.message_error.walver_criteria_1[0] + '</p>');
                    }
                    if (data.message_error.name_1) {
                        $('.input-name-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="name">' + data.message_error.name_1[0] + '</p>');
                    }
                    if (data.message_error.position_1) {
                        $('.input-position-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="position">' + data.message_error.position_1[0] + '</p>');
                    }
                    if (data.message_error.responsibilities_1) {
                        $('.input-responsibilities-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="responsibilities">' + data.message_error.responsibilities_1[0] + '</p>');
                    }
                    if (data.message_error.description_constraints_1) {
                        $('.input-description-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="description_constraints">' + data.message_error.description_constraints_1[0] + '</p>');
                    }
                    if (data.message_error.description_assumptions_1) {
                        $('.input-description-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="description_assumptions">' + data.message_error.description_assumptions_1[0] + '</p>');
                    }
                    if (data.message_error.description_security_1) {
                        $('.input-description-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="description_security">' + data.message_error.description_security_1[0] + '</p>');
                    }
                    if (data.message_error.period_security_1) {
                        $('.input-period-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="period_security">' + data.message_error.period_security_1[0] + '</p>');
                    }
                    if (data.message_error.procedure_security_1) {
                        $('.input-procedure-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="procedure_security">' + data.message_error.procedure_security_1[0] + '</p>');
                    }
                    if (data.message_error.contact_1) {
                        $('.input-contact-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="contact">' + data.message_error.contact_1[0] + '</p>');
                    }
                    if (data.message_error.type_1) {
                        $('.input-type-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="type">' + data.message_error.type_1[0] + '</p>');
                    }
                    if (data.message_error.method_1) {
                        $('.input-method-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="method">' + data.message_error.method_1[0] + '</p>');
                    }
                    if (data.message_error.soft_hard_ware_1) {
                        $('.input-soft_hard_ware-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="soft_hard_ware">' + data.message_error.soft_hard_ware_1[0] + '</p>');
                    }
                    if (data.message_error.purpose_1) {
                        $('.input-purpose-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="purpose">' + data.message_error.purpose_1[0] + '</p>');
                    }
                    if (data.message_error.employee_id) {
                        $('.tr-project-' + id + ' .td-employee .select2-container').after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="employee_id">' + data.message_error.employee_id[0] + '</p>');
                    }
                    if (data.message_error.start_at) {
                        $('.input-start_at-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="start_at">' + data.message_error.start_at[0] + '</p>');
                    }
                    if (data.message_error.end_at) {
                        $('.input-end_at-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="end_at">' + data.message_error.end_at[0] + '</p>');
                    }
                    if (data.message_error.type) {
                        $('.tr-project-' + id + ' .td-type .select2-container').after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="type">' + data.message_error.type[0] + '</p>');
                    }
                    if (data.message_error.effort) {
                        $('.input-effort-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="effort">' + data.message_error.effort[0] + '</p>');
                    }
                    if (data.message_error.qua_gate_plan_1) {
                        $('.input-qua_gate_plan-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="effort">' + data.message_error.qua_gate_plan_1[0] + '</p>');
                    }
                    if (data.message_error.qua_gate_actual_1) {
                        $('.input-qua_gate_actual-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="effort">' + data.message_error.qua_gate_actual_1[0] + '</p>');
                    }
                    if (data.message_error.stage_1) {
                        $('.tr-deliverable-' + id +' .td-deliverable .select2-container').after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="content">' + data.message_error.stage_1[0] + '</p>');
                    }
                    if (data.message_error.start_at_1) {
                        $('.input-start_at-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="start_at">' + data.message_error.start_at_1[0] + '</p>');
                    }
                    if (data.message_error.end_at_1) {
                        $('.input-end_at-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="end_at">' + data.message_error.end_at_1[0] + '</p>');
                    }
                    if (data.message_error.member_1) {
                        $('.tr-training-' + id + ' .td-training-member .select2-container').after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate">' + data.message_error.member_1[0] + '</p>');
                    }
                    if (data.message_error.member_1) {
                        $('.tr-security-' + id + ' .td-security-member .select2-container').after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate">' + data.message_error.member_1[0] + '</p>');
                    }
                    if (data.message_error.start_date_1) {
                        $('.input-start-date-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="start_date">' + data.message_error.start_date_1[0] + '</p>');
                    }
                    if (data.message_error.end_date_1) {
                        $('.input-end-date-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="end_date">' + data.message_error.end_date_1[0] + '</p>');
                    }
                    if (data.message_error.amount_1) {
                        $('.input-amount-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="amount">' + data.message_error.amount_1[0] + '</p>');
                    }
                }
                if (type == TYPE_PROJECT_MEMBER && data.message) {
                    $('#modal-warning-notification .text-default').html(data.message);
                    $('#modal-warning-notification').modal('show');
                }
                if (typeof data.popuperror !== 'undefined' && data.popuperror == 1) {
                    if (typeof data.reload !== 'undefined' && data.reload == 1) {
                        window.location.reload();
                    } else {
                        $('.warning-action').attr('data-noti', data.message_error);
                        $('.warning-action').trigger('click');
                    }
                }
                if(data.warning) {
                    $('.show-warning').remove();
                    $('.end-date-' + className).after('<button class="show-warning hidden" type="button" data-noti="'+ data.content + '"></button>');
                    $('.show-warning').addClass('warn-confirm').click();
                }
            }
        },
        error: function () {
            $('#modal-warning-notification .text-default').html(messageError);
            $('#modal-warning-notification').modal('show');
        },
        complete: function () {
            $(e).data('requestRunning', false);
            $(e).removeClass('display-none');
            $('#loading-item-' + type + '-' + id).addClass('display-none');
        }
    });
}

function saveDraftWorkorderApproved(className, e, url, type) {
    type = assignDefaultValue(type);
    $('.error-validate-' + className).remove();
    if ($(e).data('requestRunning')) {
        return;
    }
    $(e).data('requestRunning', true);
    dataId = $(e).data('id');
    $(e).addClass('display-none');
    $('.tr-' + className + '-new .loading-item-' + type).removeClass('display-none');
    if (type == TYPE_DELIVERABLE) {
        stageOld = $(e).attr('data-stage');
        title = $('.add-title-' + className + '-new-' + dataId).val();
        committed_date = $('.add-committed_date-' + className + '-new-' + dataId).val();
        re_commited_date = $('.add-re_commited_date-' + className + '-new-' + dataId).val();
        actual_date = $('.add-actual_date-' + className + '-new-' + dataId).val();
        stage = $('.add-stage-' + className + '-new-' + dataId).val();
        note = $('.add-note-' + className + '-new-' + dataId).val();
        change_request_by = $('.add-change_request_by-' + className + '-new-' + dataId).val();
        data = {
            _token: token,
            title_1: title,
            committed_date_1: committed_date,
            re_commited_date_1: re_commited_date,
            actual_date_1: actual_date,
            stage_1: stage,
            note_1: note,
            number_record: 1,
            isAddNew: true,
            project_id: project_id,
            change_request_by_1: change_request_by,
        };
    } else if(type == TYPE_STAGE_AND_MILESTONE) {
        stage = $('.add-stage-' + className + '-new-' + dataId).val();
        stageName = $('.add-stage-' + className + '-new-' + dataId + ' option[value='+ stage +']').text();
        description = $('.add-description-' + className + '-new-' + dataId).val();
        milestone = $('.add-milestone-' + className + '-new-' + dataId).val();
        qua_gate_actual = $('.add-qua_gate_actual-' + className + '-new-' + dataId).val();
        qua_gate_plan = $('.add-qua_gate_plan-' + className + '-new-' + dataId).val();
        qua_gate_result = $('.add-qua_gate_result-' + className + '-new-' + dataId + ' input[name=qua_gate_result]:checked').val();
        data = {
            _token: token,
            stage_1: stage,
            description_1: description,
            milestone_1: milestone,
            qua_gate_actual_1: qua_gate_actual,
            qua_gate_plan_1: qua_gate_plan,
            qua_gate_result_1: qua_gate_result,
            number_record: 1,
            isAddNew: true,
            project_id: project_id
        };
    } else if(type == TYPE_ASSUMPTIONS) {
        description = $('.add-description-' + className + '-new-' + dataId).val();
        remark = $('.add-remark-' + className + '-new-' + dataId).val();
        data = {
            _token: token,
            description_1: description,
            remark_1: remark,
            number_record: 1,
            isAddNew: true,
            type: typeAssumption,
            project_id: project_id
        };
    } else if(type == TYPE_CONSTRAINTS) {
        description = $('.add-description-' + className + '-new-' + dataId).val();
        remark = $('.add-remark-' + className + '-new-' + dataId).val();
        data = {
            _token: token,
            description_1: description,
            remark_1: remark,
            number_record: 1,
            isAddNew: true,
            type: typeConstraints,
            project_id: project_id
        };
    } else if(type == TYPE_SKILL_REQUEST) {
        skill = $('.add-skill-' + className + '-new-' + dataId).val();
        category = $('.add-category-' + className + '-new-' + dataId).val();
        course_name = $('.add-course_name-' + className + '-new-' + dataId).val();
        mode = $('.add-mode-' + className + '-new-' + dataId).val();
        provider = $('.add-provider-' + className + '-new-' + dataId).val();
        required_role = $('.add-required_role-' + className + '-new-' + dataId).val();
        hours = $('.add-hours-' + className + '-new-' + dataId).val();
        level = $('.add-level-' + className + '-new-' + dataId).val();
        remark = $('.add-remark-' + className + '-new-' + dataId).val();
        data = {
            _token: token,
            skill_1: skill,
            category_1: category,
            course_name_1: course_name,
            mode_1: mode,
            provider_1: provider,
            required_role_1: required_role,
            hours_1: hours,
            level_1: level,
            remark_1: remark,
            number_record: 1,
            isAddNew: true,
            project_id: project_id
        };
    } else if(type == TYPE_SECURITY) {
        content = $('.add-content-' + className + '-new-' + dataId).val();
        description = $('.add-description-' + className + '-new-' + dataId).val();
        procedure = $('.add-procedure-' + className + '-new-' + dataId).val();
        period = $('.add-period-' + className + '-new-' + dataId).val();
        employee = $('.add-security-member-' + className + '-new-' + dataId).val();
        data = {
            _token: token,
            content_1: content,
            description_1: description,
            procedure_1: procedure,
            period_1: period,
            member_1: employee,
            number_record: 1,
            isAddNew: true,
            type: typeConstraints,
            project_id: project_id
        };
    } else if(type == TYPE_TRAINING) {
        topic = $('.add-topic-' + className + '-new-' + dataId).val();
        description = $('.add-description-' + className + '-new-' + dataId).val();
        participants = $('.add-training-member-' + className + '-new-' + dataId).val();
        start_at = $('.add-start_at-' + className + '-new-' + dataId).val();
        end_at = $('.add-end_at-' + className + '-new-' + dataId).val();
        result = $('.add-result-' + className + '-new-' + dataId).val();
        walver_criteria = $('.add-walver_criteria-' + className + '-new-' + dataId).val();
        data = {
            _token: token,
            topic_1: topic,
            description_1: description,
            member_1: participants,
            start_at_1: start_at,
            end_at_1: end_at,
            result: result,
            walver_criteria_1: walver_criteria,
            number_record: 1,
            isAddNew: true,
            project_id: project_id
        };
    } else if (type == TYPE_COMMUNICATION_MEETING) {
        typeProj = $('.add-type-' + className + '-new-' + dataId).val();
        method = $('.add-method-' + className + '-new-' + dataId).val();
        time = $('.add-time-' + className + '-new-' + dataId).val();
        information = $('.add-information-' + className + '-new-' + dataId).val();
        stakeholder = $('.add-stakeholder-' + className + '-new-' + dataId).val();
        data = {
            _token: token,
            type_1: typeProj,
            method_1: method,
            time_1: time,
            information_1: information,
            stakeholder_1: stakeholder,
            type_task: typeMeetingCom,
            number_record: 1,
            isAddNew: true,
            project_id: project_id
        };
    } else if (type == TYPE_COMMUNICATION_REPORT) {
        typeProj = $('.add-type-' + className + '-new-' + dataId).val();
        method = $('.add-method-' + className + '-new-' + dataId).val();
        time = $('.add-time-' + className + '-new-' + dataId).val();
        information = $('.add-information-' + className + '-new-' + dataId).val();
        stakeholder = $('.add-stakeholder-' + className + '-new-' + dataId).val();
        data = {
            _token: token,
            type_1: typeProj,
            method_1: method,
            time_1: time,
            information_1: information,
            stakeholder_1: stakeholder,
            type_task: typeReportCom,
            number_record: 1,
            isAddNew: true,
            project_id: project_id
        };
    } else if (type == TYPE_COMMUNICATION_OTHER) {
        typeProj = $('.add-type-' + className + '-new-' + dataId).val();
        method = $('.add-method-' + className + '-new-' + dataId).val();
        time = $('.add-time-' + className + '-new-' + dataId).val();
        information = $('.add-information-' + className + '-new-' + dataId).val();
        stakeholder = $('.add-stakeholder-' + className + '-new-' + dataId).val();
        data = {
            _token: token,
            type_1: typeProj,
            method_1: method,
            time_1: time,
            information_1: information,
            stakeholder_1: stakeholder,
            type_task: typeOtherCom,
            number_record: 1,
            isAddNew: true,
            project_id: project_id
        };
    } else if(type == TYPE_MEMBER_COMMUNICATION) {
        employee = $('.add-member_communication-member-' + className + '-new-' + dataId).val();
        role = $('.add-member_communication-role-' + className + '-new-' + dataId).val();
        contact_address = $('.add-contact_address-' + className + '-new-' + dataId).val();
        responsibility = $('.add-responsibility-' + className + '-new-' + dataId).val();
        data = {
            _token: token,
            employee_1: employee,
            contact_address_1: contact_address,
            role_1: role,
            type: typeMemberCommunication,
            responsibility_1: responsibility,
            number_record: 1,
            isAddNew: true,
            project_id: project_id
        };
    } else if(type == TYPE_CUSTOMER_COMMUNICATION) {
        employee = $('.add-customer-' + className + '-new-' + dataId).val();
        role = $('.add-customer_communication-role-' + className + '-new-' + dataId).val();
        contact_address = $('.add-contact_address-' + className + '-new-' + dataId).val();
        responsibility = $('.add-responsibility-' + className + '-new-' + dataId).val();
        data = {
            _token: token,
            employee_1: employee,
            contact_address_1: contact_address,
            role_1: role,
            type: typeCustomerCommunication,
            responsibility_1: responsibility,
            number_record: 1,
            isAddNew: true,
            project_id: project_id
        };
    } else if(type == TYPE_PROJECT_MEMBER) {
        typeMember = $('.add-type-' + className + '-new-' + dataId).val();
        employee_id = $('.add-employee_id-' + className + '-new-' + dataId).val();
        start_at = $('.add-start_at-' + className + '-new-' + dataId).val();
        end_at = $('.add-end_at-' + className + '-new-' + dataId).val();
        effort = $('.add-effort-' + className + '-new-' + dataId).val();
        projMemberProgLangs = $('select.add-prog-' + className + '-new-' + dataId).length ?
            $('select.add-prog-' + className + '-new-' + dataId).val() :
            $('input.add-prog-' + className + '-new-' + dataId).val();
        data = {
            _token: token,
            type: typeMember,
            employee_id: employee_id,
            start_at: start_at,
            end_at: end_at,
            effort: effort,
            number_record: 1,
            isAddNew: true,
            project_id: project_id,
            prog_langs: projMemberProgLangs
        };
    } else if(type == TYPE_COMMUNICATION) {
        content = $('.add-content-' + className + '-new-' + dataId).val();
        data = {
            _token: token,
            content_1: content,
            number_record: 1,
            isAddNew: true,
            project_id: project_id
        };
    } else if(type == TYPE_EXTERNAL_INTERFACE) {
        name = $('.add-name-' + className + '-new-' + dataId).val();
        position = $('.add-position-' + className + '-new-' + dataId).val();
        responsibilities = $('.add-responsibilities-' + className + '-new-' + dataId).val();
        contact = $('.add-contact-' + className + '-new-' + dataId).val();
        data = {
            _token: token,
            name_1: name,
            position_1: position,
            responsibilities_1: responsibilities,
            contact_1: contact,
            number_record: 1,
            isAddNew: true,
            project_id: project_id
        };
    } else if(type == TYPE_WO_DEVICES_EXPENSE) {
        date = $('.add-time-' + className + '-new-' + dataId).val();
        amount = $('.add-amount-' + className + '-new-' + dataId).val().split(",");
        var inputUpdatedPoint = parseFloat(amount[0].split(".").join(""));
        description = $('.add-description-' + className + '-new-' + dataId).val();
        data = {
            _token: token,
            time_1: date,
            amount_1: inputUpdatedPoint,
            description_1: description,
            number_record: 1,
            isAddNew: true,
            project_id: project_id
        };
    } else if(type == TYPE_TOOL_AND_INFRASTRUCTURE) {
        soft_ware_id = $('.add-soft_hard_ware-' + className + '-new-' + dataId).val();
        soft_hard_ware = $('.add-soft_hard_ware-' + className + '-new-' + dataId + ' option[value='+ soft_ware_id +']').text();
        purpose = $('.add-purpose-' + className + '-new-' + dataId).val();
        note = $('.add-note-' + className + '-new-' + dataId).val();
        start_date = $('.add-start-date-' + className + '-new-' + dataId).val();
        end_date = $('.add-end-date-' + className + '-new-' + dataId).val();
        data = {
            _token: token,
            soft_hard_ware_1: soft_hard_ware,
            soft_ware_id_1: soft_ware_id,
            purpose_1: purpose,
            note_1: note,
            start_date_1: start_date,
            end_date_1: end_date,
            number_record: 1,
            isAddNew: true,
            project_id: project_id
        };
        globalData = data;
        globalurl = url;
        globalClassName = className;
    } else {
        content = $('.add-content-' + className + '-new-' + dataId).val();
        expected_date = $('.expected_date-' + className).val();
        data = {
            _token: token,
            content_1: content,
            expected_date_1: expected_date,
            number_record: 1,
            isAddNew: true,
            project_id: project_id
        };
    }
    $.ajax({
        url: url,
        type: 'post',
        data: data,
        dataType: 'json',
        success: function (data) {
            if (data.status) {
                if(type == TYPE_DELIVERABLE) {
                    btnDeleteDeliverable = $('.delete-deliverable');
                    countDeliver = 0;

                    btnDeleteDeliverable.each(function() {
                        if ($(this).attr('data-stage') == stageOld) {
                            countDeliver++;
                        }
                    });
                    if (countDeliver <= 1) {
                        $('.delete-stage-and-milestone-' + stageOld).removeClass('display-none');
                    }
                    $('.delete-stage-and-milestone-' + stage).addClass('display-none');
                }
                dataColumn = getDataColumSort(type);
                showOrHideButtonSubmitWorkorder(data.isCheckShowSubmit);
                $('.workorder-' + className).html(data.content);
                $('[data-toggle="tooltip"], .tooltip').tooltip("hide");
                if (type == TYPE_PROJECT_MEMBER) {
                    $('.employee_id_select2').select2().on('select2:close', function(evt) { tabToChange (evt)});
                    $('.tr-project .type-project-member').select2().on('select2:close', function(evt) { tabToChange (evt)});
                    iniTableSorter(dataColumn);
                    if (data.effort) {
                        $('.team-size').html(data.effort.count);
                        $('.effort-usage').html(data.effort.total);
                        $('.effort-dev').html(data.effort.dev);
                        $('.effort-pm').html(data.effort.pm);
                        $('.effort-qa').html(data.effort.qa);
                    }
                }
                if (type == TYPE_STAGE_AND_MILESTONE) {
                    $('.select-stage').select2().on('select2:close', function(evt) { tabToChange (evt)});
                    $('.select-stage-custom').append($("<option></option>")
                        .attr("value",data.id)
                        .text(stageName));
                }
                if (type == TYPE_DELIVERABLE) {
                    $('.select-stage-deliverable').select2().on('select2:close', function(evt) { tabToChange (evt)});
                }
                if (type == TYPE_ASSUMPTIONS) {
                    $('#workorder_assumptions').html(data.content);
                }
                if (type == TYPE_CONSTRAINTS) {
                    $('#workorder_constraints').html(data.content);
                }
                if (type == TYPE_CRITICAL_DEPENDENCIES) {
                    $('#workorder_critical-dependencies').html(data.content);
                }
                if (type == TYPE_SECURITY) {
                    $('#workorder_security').html(data.content);
                }
                if (type == TYPE_SKILL_REQUEST) {
                    $('#workorder_skill_request').html(data.content);
                }
                if (type == TYPE_MEMBER_COMMUNICATION) {
                    $('#workorder_member_communication').html(data.content);
                }
                if (type == TYPE_COMMUNICATION_MEETING || type == TYPE_COMMUNICATION_REPORT || type == TYPE_COMMUNICATION_OTHER) {
                    $('#workorder_communication').html(data.content);
                }
                if (type == TYPE_CUSTOMER_COMMUNICATION) {
                    $('#workorder_customer_communication').html(data.content);
                }
            } else {
                if (data.message_error) {
                    if (data.message_error.title_1) {
                        $('.title-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="content">' + data.message_error.title_1[0] + '</p>')
                    }
                    if (data.message_error.committed_date_1) {
                        $('.committed_date-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="content">' + data.message_error.committed_date_1[0] + '</p>')
                    }
                    if (data.message_error.actual_date_1) {
                        $('.actual_date-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="content">' + data.message_error.actual_date_1[0] + '</p>')
                    }
                    if (data.message_error.stage_1) {
                        $('.tr-stage-and-milestone-new .td-stage .select2-container').after('<p class="word-break error-validate-' + className + ' error-validate" for="type">' + data.message_error.stage_1[0] + '</p>')
                    }
                    if (data.message_error.skill_1) {
                        $('.skill-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="skill">' + data.message_error.skill_1[0] + '</p>')
                    }
                    if (data.message_error.course_name_1) {
                        $('.course_name-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="course_name">' + data.message_error.course_name_1[0] + '</p>')
                    }
                    if (data.message_error.category_1) {
                        $('.category-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="category">' + data.message_error.category_1[0] + '</p>')
                    }
                    if (data.message_error.hours_1) {
                        $('.hours-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="hours">' + data.message_error.hours_1[0] + '</p>')
                    }
                    if (data.message_error.level_1) {
                        $('.level-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="level">' + data.message_error.level_1[0] + '</p>')
                    }
                    if (data.message_error.mode_1) {
                        $('.mode-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="mode">' + data.message_error.mode_1[0] + '</p>')
                    }
                    if (data.message_error.provider_1) {
                        $('.provider-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="provider">' + data.message_error.provider_1[0] + '</p>')
                    }
                    if (data.message_error.required_role_1) {
                        $('.required_role-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="required_role">' + data.message_error.required_role_1[0] + '</p>')
                    }
                    if (data.message_error.remark_1) {
                        $('.remark-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="remark">' + data.message_error.remark_1[0] + '</p>')
                    }
                    if (data.message_error.customer_1) {
                        $('.customer-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="customer">' + data.message_error.customer_1[0] + '</p>')
                    }
                    if (data.message_error.content_1) {
                        $('.content-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="content">' + data.message_error.content_1[0] + '</p>')
                    }
                    if (data.message_error.description_1) {
                        $('.description-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="description">' + data.message_error.description_1[0] + '</p>')
                    }
                    if (data.message_error.description_constraints_1) {
                        $('.description-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="description_constraints">' + data.message_error.description_constraints_1[0] + '</p>')
                    }
                    if (data.message_error.role_member_communication_1) {
                        $('.tr-member_communication .td-member_communication-role .select2-container').after('<p class="word-break error-validate-' + className + ' error-validate" for="member_communication">' + data.message_error.role_member_communication_1[0] + '</p>')
                    }
                    if (data.message_error.role_customer_communication_1) {
                        $('.tr-customer_communication .td-customer_communication-role .select2-container').after('<p class="word-break error-validate-' + className + ' error-validate" for="customer_communication">' + data.message_error.role_customer_communication_1[0] + '</p>')
                    }
                    if (data.message_error.description_assumptions_1) {
                        $('.description-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="description_assumptions">' + data.message_error.description_assumptions_1[0] + '</p>')
                    }
                    if (data.message_error.description_security_1) {
                        $('.description-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="description_security">' + data.message_error.description_security_1[0] + '</p>')
                    }
                    if (data.message_error.period_security_1) {
                        $('.period-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="period_security">' + data.message_error.period_security_1[0] + '</p>')
                    }
                    if (data.message_error.procedure_security_1) {
                        $('.procedure-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="procedure_security">' + data.message_error.procedure_security_1[0] + '</p>')
                    }
                    if (data.message_error.milestone_1) {
                        $('.milestone-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="milestone">' + data.message_error.milestone_1[0] + '</p>')
                    }
                    if (data.message_error.topic_1) {
                        $('.topic-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="topic">' + data.message_error.topic_1[0] + '</p>')
                    }
                    if (data.message_error.participants_1) {
                        $('.participants-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="participants">' + data.message_error.participants_1[0] + '</p>')
                    }
                    if (data.message_error.time_1) {
                        $('.time-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="time">' + data.message_error.time_1[0] + '</p>')
                    }
                    if (data.message_error.walver_criteria_1) {
                        $('.walver_criteria-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="walver_criteria">' + data.message_error.walver_criteria_1[0] + '</p>')
                    }
                    if (data.message_error.name_1) {
                        $('.name-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="name">' + data.message_error.name_1[0] + '</p>')
                    }
                    if (data.message_error.position_1) {
                        $('.position-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="position">' + data.message_error.position_1[0] + '</p>')
                    }
                    if (data.message_error.responsibilities_1) {
                        $('.responsibilities-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="responsibilities">' + data.message_error.responsibilities_1[0] + '</p>')
                    }
                    if (data.message_error.contact_1) {
                        $('.contact-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="contact">' + data.message_error.contact_1[0] + '</p>')
                    }
                    if (data.message_error.soft_hard_ware_1) {
                        $('.soft_hard_ware-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="soft_hard_ware">' + data.message_error.soft_hard_ware_1[0] + '</p>')
                    }
                    if (data.message_error.purpose_1) {
                        $('.purpose-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="purpose">' + data.message_error.purpose_1[0] + '</p>')
                    }
                    if (data.message_error.employee_id) {
                        $('.tr-project-member-new .td-employee .select2-container').after('<p class="word-break error-validate-' + className + ' error-validate" for="employee_id">' + data.message_error.employee_id[0] + '</p>')
                    }
                    if (data.message_error.start_at) {
                        $('.start_at-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="start_at">' + data.message_error.start_at[0] + '</p>')
                    }
                    if (data.message_error.end_at) {
                        $('.end_at-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="end_at">' + data.message_error.end_at[0] + '</p>')
                    }
                    if (data.message_error.description) {
                        $('.description-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="description">' + data.message_error.description[0] + '</p>')
                    }
                    if (data.message_error.effort) {
                        $('.effort-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="effort">' + data.message_error.effort[0] + '</p>')
                    }
                    if (data.message_error.type_1) {
                        $('.type-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="type">' + data.message_error.type_1[0] + '</p>')
                    }
                    if (data.message_error.method_1) {
                        $('.method-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="method">' + data.message_error.method_1[0] + '</p>')
                    }
                    if (data.message_error.type) {
                        $('.tr-project-member-new .td-type .select2-container').after('<p class="word-break error-validate-' + className + ' error-validate" for="type">' + data.message_error.type[0] + '</p>')
                    }
                    if (data.message_error.qua_gate_actual_1) {
                        $('.qua_gate_actual-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="qua_gate_actual">' + data.message_error.qua_gate_actual_1[0] + '</p>')
                    }
                    if (data.message_error.qua_gate_plan_1) {
                        $('.qua_gate_plan-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="qua_gate_plan">' + data.message_error.qua_gate_plan_1[0] + '</p>')
                    }
                    if (data.message_error.stage_1) {
                        $('.tr-deliverable-new .td-deliverable .select2-container').after('<p class="word-break error-validate-' + className + ' error-validate" for="type">' + data.message_error.stage_1[0] + '</p>')
                    }
                    if (data.message_error.start_at_1) {
                        $('.start_at-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="start_at">' + data.message_error.start_at_1[0] + '</p>')
                    }
                    if (data.message_error.end_at_1) {
                        $('.end_at-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="end_at">' + data.message_error.end_at_1[0] + '</p>')
                    }
                    if (data.message_error.member_1) {
                        $('.tr-training-new .td-training-member .select2-container').after('<p class="word-break error-validate-' + className + ' error-validate">' + data.message_error.member_1[0] + '</p>')
                    }
                    if (data.message_error.employee_1) {
                        $('.tr-member_communication-new .td-member_communication-member').after('<p class="word-break error-validate-' + className + ' error-validate">' + data.message_error.employee_1[0] + '</p>')
                    }
                    if (data.message_error.role_1) {
                        $('.tr-member_communication-new .td-member_communication-role').after('<p class="word-break error-validate-' + className + ' error-validate">' + data.message_error.role_1[0] + '</p>')
                    }
                    if (data.message_error.member_1) {
                        $('.tr-security-new .td-security-member .select2-container').after('<p class="word-break error-validate-' + className + ' error-validate">' + data.message_error.member_1[0] + '</p>')
                    }
                    if (data.message_error.start_date_1) {
                        $('.start-date-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="start_date">' + data.message_error.start_date_1[0] + '</p>');
                    }
                    if (data.message_error.end_date_1) {
                        $('.end-date-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="end_date">' + data.message_error.end_date_1[0] + '</p>');
                    }
                    if (data.message_error.amount_1) {
                        $('.amount-' + className).after('<p class="word-break error-validate-' + className + ' error-validate" for="amount">' + data.message_error.amount_1[0] + '</p>');
                    }
                }
                if (typeof data.popuperror !== 'undefined' && data.popuperror == 1) {
                    if (typeof data.reload !== 'undefined' && data.reload == 1) {
                        window.location.reload();
                    } else {
                        $('.warning-action').attr('data-noti', data.message_error);
                        $('.warning-action').trigger('click');
                    }
                }
                if(data.warning) {
                    $('.show-warning').remove();
                    $('.end-date-' + className).after('<button class="show-warning hidden" type="button" data-noti="'+ data.content + '"></button>');
                    $('.show-warning').addClass('warn-confirm').click();
                }
            }
        },
        error: function () {
            $('#modal-warning-notification .text-default').html(messageError);
            $('#modal-warning-notification').modal('show');
        },
        complete: function () {
            $(e).data('requestRunning', false);
            $(e).removeClass('display-none');
            $('.tr-' + className + '-new .loading-item-' + type).addClass('display-none');
        }
    });
}

function deleteWorkorderApproved(className, element, url, type) {
    var type = assignDefaultValue(type);
    $('#modal-delete-confirm-new').modal('show');
    $('#modal-delete-confirm-new .btn-ok').data('type', type);
    $('#modal-delete-confirm-new .btn-ok').data('className', className);
    dataId = $(element).data('id');
    displayTextDeleteConfirm(element);
    dataClassName = className;
    dataUrl = url;
    if(type == TYPE_DELIVERABLE) {
        dataStage = $(element).attr('data-stage');
    }
    $(document).on('click', '#modal-delete-confirm-new .btn-ok', function () {
        typeElement = $(this).data('type');
        className = $(this).data('className');
        if (type == typeElement) {
            $('#modal-delete-confirm-new').modal('hide');
            $('#loading-item-' + typeElement + '-' + dataId).removeClass('display-none');
            $('.delete-' + className).addClass('display-none');
            $('.edit-' + className).addClass('display-none');

            if ($(this).data('requestRunning')) {
                return;
            }
            $(this).data('requestRunning', true);
            isDelete = true;
            data = {
                _token: token,
                project_id: project_id,
                id: dataId,
                type: type,
                isDelete: isDelete
            };
            $.ajax({
                url: dataUrl,
                type: 'post',
                dataType: 'json',
                data: data,
                success: function (data) {
                    if (data.status) {
                        if(type == TYPE_DELIVERABLE) {
                            btnDeleteDeliverable = $('.delete-deliverable');
                            countDeliver = 0;

                            btnDeleteDeliverable.each(function() {
                                if ($(this).attr('data-stage') == dataStage) {
                                    countDeliver++;
                                }
                            });
                        }
                        dataSort = getDataColumSort(type);
                        showOrHideButtonSubmitWorkorder(data.isCheckShowSubmit);
                        $('#modal-delete-confirm-new .btn-ok').removeAttr('type');
                        $('#modal-delete-confirm-new .btn-ok').removeAttr('className');
                        $('.workorder-' + className).html(data.content);
                        $('[data-toggle="tooltip"], .tooltip').tooltip("hide");
                        if (typeElement == TYPE_PROJECT_MEMBER) {
                            $('.employee_id_select2').select2().on('select2:close', function(evt) { tabToChange (evt)});
                            $('.tr-project .type-project-member').select2().on('select2:close', function(evt) { tabToChange (evt)});
                            iniTableSorter(dataSort);
                            if (data.effort) {
                                $('.team-size').html(data.effort.count);
                                $('.effort-usage').html(data.effort.total);
                                $('.effort-dev').html(data.effort.dev);
                                $('.effort-pm').html(data.effort.pm);
                                $('.effort-qa').html(data.effort.qa);
                            }
                        }
                        if (typeElement == TYPE_STAGE_AND_MILESTONE) {
                            $('.select-stage').select2().on('select2:close', function(evt) { tabToChange (evt)});
                            $(".select-stage-custom option[value='"+ dataId +"']").remove();
                        }
                        if (typeElement == TYPE_DELIVERABLE) {
                            $('.select-stage-deliverable').select2().on('select2:close', function(evt) { tabToChange (evt)});
                            if (countDeliver <= 1) {
                                $('.delete-stage-and-milestone-' + dataStage).removeClass('display-none');
                            }
                        }
                        if (typeElement == TYPE_RISK) {
                            location.reload();
                        }
                        if (typeElement == TYPE_ASSUMPTIONS) {
                            $('#workorder_assumptions').html(data.content);
                        }
                        if (typeElement == TYPE_CONSTRAINTS) {
                            $('#workorder_constraints').html(data.content);
                        }
                        if (type == TYPE_CRITICAL_DEPENDENCIES) {
                            $('#workorder_critical-dependencies').html(data.content);
                        }
                        if (type == TYPE_SECURITY) {
                            $('#workorder_security').html(data.content);
                        }
                        if (type == TYPE_SKILL_REQUEST) {
                            $('#workorder_skill_request').html(data.content);
                        }
                        if (type == TYPE_MEMBER_COMMUNICATION) {
                            $('#workorder_member_communication').html(data.content);
                        }
                        if (type == TYPE_COMMUNICATION_MEETING || type == TYPE_COMMUNICATION_REPORT || type == TYPE_COMMUNICATION_OTHER) {
                            $('#workorder_communication').html(data.content);
                        }
                        if (type == TYPE_CUSTOMER_COMMUNICATION) {
                            $('#workorder_customer_communication').html(data.content);
                        }
                    } else {
                        if (typeof data.popuperror !== 'undefined' && data.popuperror == 1) {
                            if (typeof data.reload !== 'undefined' && data.reload == 1) {
                                window.location.reload();
                                return true;
                            } else {
                                $('.warning-action').attr('data-noti', data.message_error);
                                $('.warning-action').trigger('click');
                            }
                        }
                        if (typeElement == TYPE_PROJECT_MEMBER && data.message) {
                            $('#modal-warning-notification .text-default').html(data.message);
                            $('#modal-warning-notification').modal('show');
                            $('#loading-item-' + typeElement + '-' + dataId).addClass('display-none');
                            $('.delete-' + className).removeClass('display-none');
                            $('.edit-' + className).removeClass('display-none');
                        } else {
                            $('#modal-warning-notification .text-default').html(messageError);
                            $('#modal-warning-notification').modal('show');
                        }
                    }
                },
                error: function () {
                    $('#modal-warning-notification .text-default').html(messageError);
                    $('#modal-warning-notification').modal('show');
                },
                complete: function () {
                    $('#modal-delete-confirm-new .btn-ok').data('requestRunning', false);
                }
            });
        }
    });
}

/* function slove averall plan*/
function showFormAddWorkorderNotApproved(className, type) {
    $('.error-validate-' + className).remove();
    type = assignDefaultValue(type);
    selectSearchReload();
    setTimeout(function () {
        if (type == TYPE_STAGE_AND_MILESTONE) {
            $('.stage-' + className).focus();
        } else if (type == TYPE_TRAINING) {
            $('.topic-' + className).focus();
        } else if (type == TYPE_PROJECT_MEMBER) {
            $('.type-' + className).focus();
        } else if (type == TYPE_EXTERNAL_INTERFACE) {
            $('.name-' + className).focus();
        } else if (type == TYPE_TOOL_AND_INFRASTRUCTURE) {
            $('.soft_hard_ware-' + className).focus();
        } else if (type == TYPE_DELIVERABLE) {
            $('.title-' + className).focus();
        } else if (type == TYPE_WO_DEVICES_EXPENSE) {
            $('.time-' + className).focus();
        } else {
            $('.content-' + className).focus();
        }

    }, 0);
    $('.tr-' + className).removeClass('display-none');
    $('.tr-add-' + className).addClass('display-none');
    $('.button-submit-' + className).removeClass('display-none');
    if (type != TYPE_STAGE_AND_MILESTONE && type != TYPE_DELIVERABLE) {
        $('#table-' + className + ' tr:last td:first').html($('#table-' + className + ' tr').length - 2);
    }
    if (type == TYPE_STAGE_AND_MILESTONE) {
        setTimeout(function () {
            $('.tr-stage-and-milestone select').select2().on('select2:close', function(evt) { tabToChange (evt)});
        });
    }

    if (type == TYPE_TOOL_AND_INFRASTRUCTURE) {
        setTimeout(function () {
            $('.tr-tool-and-infrastructure select').select2().on('select2:close', function(evt) { tabToChange (evt)});
        });
    }

    if (type == TYPE_TRAINING) {
        setTimeout(function () {
            $('.tr-training-hidden select').select2().on('select2:close', function(evt) { tabToChange (evt)});
        });
    }

    if (type == TYPE_CUSTOMER_COMMUNICATION) {
        setTimeout(function () {
            $('.tr-customer_communication-hidden select').select2().on('select2:close', function(evt) { tabToChange (evt)});
        });
    }

    if (type == TYPE_MEMBER_COMMUNICATION) {
        setTimeout(function () {
            $('.tr-member_communication-hidden select').select2().on('select2:close', function(evt) { tabToChange (evt)});
        });
    }

    if (type == TYPE_SECURITY) {
        setTimeout(function () {
            $('.tr-security-hidden select').select2().on('select2:close', function(evt) { tabToChange (evt)});
        });
    }

    if (type == TYPE_DELIVERABLE) {
        setTimeout(function () {
            $('.tr-deliverable select').select2().on('select2:close', function(evt) { tabToChange (evt)});
        });
    }
    if (type == TYPE_CRITICAL_DEPENDENCIES) {
        setTimeout(function () {
            $('.critical-assignee-select2-new').select2({
                ajax: {
                    url: urlSearchEmployee,
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term, // search term
                            page: params.page,
                            projectId: projectId
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
                                more: (params.page * 30) < data.total_count
                            }
                        };
                    },
                    cache: true
                },
                escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
                minimumInputLength: 1,
            });
        });
    }

    if (type == TYPE_ASSUMPTION_CONSTRAIN) {
        setTimeout(function () {
            $('.assignee-assumption-assignee-select2-new').select2({
                ajax: {
                    url: urlSearchEmployee,
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term, // search term
                            page: params.page,
                            projectId: projectId
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
                                more: (params.page * 30) < data.total_count
                            }
                        };
                    },
                    cache: true
                },
                escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
                minimumInputLength: 1,
            });
        });
    }

    if (type == TYPE_WO_DEVICES_EXPENSE) {
        $('#table-derived-expenses input.time-datepicker').datepicker({
            format: 'yyyy-mm',
            weekStart: 1,
            todayHighlight: true,
            autoclose: true,
            viewMode: "months",
            minViewMode: "months"
        });
    }
    $('.slove-' + className + ' .remove-' + className).removeClass('display-none');
    $('.slove-' + className + ' .add-' + className).addClass('display-none');
}

function deleteWorkorderNotApproved(className, element, url, type) {
    type = assignDefaultValue(type);
    $('#modal-delete-confirm-new').modal('show');
    $('#modal-delete-confirm-new .btn-ok').data('type', type);
    $('#modal-delete-confirm-new .btn-ok').data('className', className);
    displayTextDeleteConfirm(element);
    dataId = $(element).data('id');
    dataClassName = className;
    dataUrl = url;
    $(document).on('click', '#modal-delete-confirm-new .btn-ok', function () {
        typeElement = $(this).data('type');
        className = $(this).data('className');
        if (type == typeElement) {
            $('#loading-item-' + type + '-' + dataId).removeClass('display-none');
            $('.delete-' + className + '-' + dataId).addClass('display-none');
            $('.edit-' + className + '-' + dataId).addClass('display-none');
            $('#modal-delete-confirm-new').modal('hide');
            if ($(this).data('requestRunning')) {
                return;
            }
            $(this).data('requestRunning', true);
            isDelete = true;
            data = {
                _token: token,
                project_id: project_id,
                id: dataId,
                type: type,
                isDelete: isDelete
            };
            $.ajax({
                url: dataUrl,
                type: 'post',
                dataType: 'json',
                data: data,
                success: function (data) {
                    if (data.status) {
                        showOrHideButtonSubmitWorkorder(data.isCheckShowSubmit);
                        $('#modal-delete-confirm-new .btn-ok').removeAttr('type');
                        $('#modal-delete-confirm-new .btn-ok').removeAttr('className');
                        $('[data-toggle="tooltip"], .tooltip').tooltip("hide");
                        $('.workorder-' + className).html(data.content);
                        if (type == TYPE_PROJECT_MEMBER) {
                            $('.employee_id_select2').select2().on('select2:close', function(evt) { tabToChange (evt)});
                            $('.tr-project .type-project-member').select2().on('select2:close', function(evt) { tabToChange (evt)});
                            iniTableSorter();
                        }
                        if (type == TYPE_STAGE_AND_MILESTONE) {
                            $('.select-stage').select2().on('select2:close', function(evt) { tabToChange (evt)});
                            $(".select-stage-custom option[value='"+ dataId +"']").remove();
                        }
                        if (type == TYPE_DELIVERABLE) {
                            $('.select-stage-deliverable').select2().on('select2:close', function(evt) { tabToChange (evt)});
                        }
                        if (type == TYPE_RISK) {
                            location.reload();
                        }
                        if (type == TYPE_ASSUMPTIONS) {
                            $('#workorder_assumptions').html(data.content);
                        }
                        if (type == TYPE_CONSTRAINTS) {
                            $('#workorder_constraints').html(data.content);
                        }
                        if (type == TYPE_CRITICAL_DEPENDENCIES) {
                            $('#workorder_critical-dependencies').html(data.content);
                        }
                        if (type == TYPE_SECURITY) {
                            $('#workorder_security').html(data.content);
                        }

                        if (type == TYPE_MEMBER_COMMUNICATION) {
                            $('#workorder_member_communication').html(data.content);
                        }
                        if (type == TYPE_COMMUNICATION_MEETING || type == TYPE_COMMUNICATION_REPORT || type == TYPE_COMMUNICATION_OTHER) {
                            $('#workorder_communication').html(data.content);
                        }

                        if (type == TYPE_CUSTOMER_COMMUNICATION) {
                            $('#workorder_customer_communication').html(data.content);
                        }
                    } else {
                        $('#modal-warning-notification .text-default').html(messageError);
                        $('#modal-warning-notification').modal('show');
                    }
                },
                error: function () {
                    $('#modal-warning-notification .text-default').html(messageError);
                    $('#modal-warning-notification').modal('show');
                },
                complete: function () {
                    $('#modal-delete-confirm-new .btn-ok').data('requestRunning', false);
                },
            });
        }
    });
}

function removeFormAddWorkorderNotApproved(className, type) {
    type = assignDefaultValue(type);
    $('.slove-' + className + ' .remove-' + className).addClass('display-none');
    $('.slove-' + className + ' .add-' + className).removeClass('display-none');
    $('.tr-' + className).addClass('display-none');
    $('.tr-add-' + className).removeClass('display-none');
    $('.button-submit-' + className).addClass('display-none');
    if (type == TYPE_STAGE_AND_MILESTONE) {
        $('.stage-' + className).val($('.stage-' + className + ' option:first').val());
        $('.description-' + className).val('');
        $('.milestone-' + className).val('');
        $('.qua_gate_plan-' + className).val('');
        $('.qua_gate_actual-' + className).val('');
    } else if (type == TYPE_TRAINING) {
        $('.topic-' + className).val('');
        $('.description-' + className).val('');
        $('.participants-' + className).val('');
        $('.time-' + className).val('');
        $('.walver_criteria-' + className).val('');
    } else if (type == TYPE_CUSTOMER_COMMUNICATION) {
        $('.customer-' + className).val('');
        $('.role-' + className).val('');
        $('.contact_address-' + className).val('');
        $('.responsibility-' + className).val('');
    } else if (type == TYPE_SECURITY) {
        $('.add-content-' + className).val('');
        $('.add-description-' + className).val('');
        $('.add-participants-' + className).val('');
        $('.add-period-' + className).val('');
        $('.add-procedure-' + className).val('');
    } else if (type == TYPE_ASSUMPTIONS || type == TYPE_CONSTRAINTS) {
        $('.description-' + className).val('');
        $('.remark-' + className).val('');
    } else if (type == TYPE_COMMUNICATION_MEETING || type == TYPE_COMMUNICATION_REPORT || type == TYPE_COMMUNICATION_OTHER) {
        $('.add-type-' + className).val('');
        $('.add-method-' + className).val('');
        $('.add-time-' + className).val('');
        $('.add-information-' + className).val('');
        $('.add-stakeholder-' + className).val('');
    } else if (type == TYPE_SKILL_REQUEST) {
        $('.skill-' + className).val('');
        $('.category-' + className).val('');
        $('.course_name-' + className).val('');
        $('.mode-' + className).val('');
        $('.provider-' + className).val('');
        $('.required_role-' + className).val('');
        $('.hours-' + className).val('');
        $('.level-' + className).val('');
        $('.remark-' + className).val('');
    } else if (type == TYPE_PROJECT_MEMBER) {
        $('.type-' + className).val($('.type-' + className + ' option:first').val());
        $('.employee_id-' + className).val($('.employee_id-' + className + ' option:first').val());
        $('.start_at-' + className).val('');
        $('.end_at-' + className).val('');
        $('.effort-' + className).val('');
    } else if (type == TYPE_EXTERNAL_INTERFACE) {
        $('.name-' + className).val('');
        $('.position-' + className).val('');
        $('.responsibilities-' + className).val('');
        $('.contact-' + className).val('');
    } else if (type == TYPE_TOOL_AND_INFRASTRUCTURE) {
        $('.soft_hard_ware-' + className).val($('.soft_hard_ware-' + className + ' option:first').val());
        // $('.soft_hard_ware-' + className).val('');
        $('.purpose-' + className).val('');
        $('.note-' + className).val('');
        $('.start-date-' + className).val('');
        $('.end-date-' + className).val('');
    } else if (type == TYPE_DELIVERABLE) {
        $('.title-' + className).val('');
        $('.committed_date-' + className).val('');
        $('.actual_date-' + className).val('');
        $('.stage-' + className).val($('.stage-' + className + ' option:first').val());
        $('.note-' + className).val('');
    } else if (type == TYPE_WO_DEVICES_EXPENSE) {
        $('.time-' + className).val('');
        $('.amount-' + className).val('');
        $('.description-' + className).val('');
    } else {
        $('.content-' + className).val('');
        $('.expected_date-' + className).val('');
    }
    $('.error-' + className).remove();
}

function reloadTable(tableName, type) {
    type = assignDefaultValue(type);
    $('#table-' + tableName).load(location.href + ' #table-' + tableName);
    $('.remove-' + tableName).addClass('display-none');
    $('.add-' + tableName).removeClass('display-none');

}

function saveWorkorderNotApproved(className, e, url, type) {
    if ($(e).data('requestRunning')) {
        return;
    }
    $(e).data('requestRunning', true);
    type = assignDefaultValue(type);
    id = $(e).data('id');
    $(e).addClass('display-none');
    $('#loading-item-' + type + '-' + id).removeClass('display-none');
    $('.error-validate-' + className + '-' + id).remove();
    if (type == TYPE_STAGE_AND_MILESTONE) {
        stage = $('.input-stage-' + className + '-' + id).val();
        stageName = $('.input-stage-' + className + '-' + id + ' option[value='+ stage +']').text();
        description = $('.input-description-' + className + '-' + id).val();
        milestone = $('.input-milestone-' + className + '-' + id).val();
        qua_gate_actual = $('.input-qua_gate_actual-' + className + '-' + id).val();
        qua_gate_plan = $('.input-qua_gate_plan-' + className + '-' + id).val();
        qua_gate_result = $('.span-qua_gate_result-' + className + '-' + id + ' input[name=qua_gate_result-' + id + ']:checked').val();
        data = {
            _token: token,
            number_record: 1,
            stage_1: stage,
            description_1: description,
            milestone_1: milestone,
            qua_gate_actual_1: qua_gate_actual,
            qua_gate_plan_1: qua_gate_plan,
            qua_gate_result_1: qua_gate_result,
            project_id: project_id,
            id: id
        };
    } else if (type == TYPE_ASSUMPTIONS) {
        description = $('.input-description-' + className + '-' + id).val();
        remark = $('.input-remark-' + className + '-' + id).val();
        data = {
            _token: token,
            number_record: 1,
            description_1: description,
            remark_1: remark,
            type: typeAssumption,
            project_id: project_id,
            id: id
        };
    } else if (type == TYPE_CONSTRAINTS) {
        description = $('.input-description-' + className + '-' + id).val();
        remark = $('.input-remark-' + className + '-' + id).val();
        data = {
            _token: token,
            number_record: 1,
            description_1: description,
            remark_1: remark,
            type: typeConstraints,
            project_id: project_id,
            id: id
        };
    } else if (type == TYPE_COMMUNICATION_MEETING) {
        typeProj = $('.input-type-' + className + '-' + id).val();
        method = $('.input-method-' + className + '-' + id).val();
        time = $('.input-time-' + className + '-' + id).val();
        information = $('.input-information-' + className + '-' + id).val();
        stakeholder = $('.input-stakeholder-' + className + '-' + id).val();
        data = {
            _token: token,
            type_1: typeProj,
            method_1: method,
            time_1: time,
            information_1: information,
            stakeholder_1: stakeholder,
            number_record: 1,
            type_task: typeMeetingCom,
            project_id: project_id,
            id: id
        };
    } else if (type == TYPE_COMMUNICATION_REPORT) {
        typeProj = $('.input-type-' + className + '-' + id).val();
        method = $('.input-method-' + className + '-' + id).val();
        time = $('.input-time-' + className + '-' + id).val();
        information = $('.input-information-' + className + '-' + id).val();
        stakeholder = $('.input-stakeholder-' + className + '-' + id).val();
        data = {
            _token: token,
            type_1: typeProj,
            method_1: method,
            time_1: time,
            information_1: information,
            stakeholder_1: stakeholder,
            number_record: 1,
            type_task: typeReportCom,
            project_id: project_id,
            id: id
        };
    } else if (type == TYPE_COMMUNICATION_OTHER) {
        typeProj = $('.input-type-' + className + '-' + id).val();
        method = $('.input-method-' + className + '-' + id).val();
        time = $('.input-time-' + className + '-' + id).val();
        information = $('.input-information-' + className + '-' + id).val();
        stakeholder = $('.input-stakeholder-' + className + '-' + id).val();
        data = {
            _token: token,
            type_1: typeProj,
            method_1: method,
            time_1: time,
            information_1: information,
            stakeholder_1: stakeholder,
            number_record: 1,
            type_task: typeOtherCom,
            project_id: project_id,
            id: id
        };
    } else if (type == TYPE_TRAINING) {
        topic = $('.input-topic-' + className + '-' + id).val();
        description = $('.input-description-' + className + '-' + id).val();
        participants = $('.input-participants-' + className + '-' + id).val();
        start_at = $('.input-start_at-' + className + '-' + id).val();
        end_at = $('.input-end_at-' + className + '-' + id).val();
        result = $('.input-result-' + className + '-' + id).val();
        walver_criteria = $('.input-walver_criteria-' + className + '-' + id).val();
        data = {
            _token: token,
            number_record: 1,
            topic_1: topic,
            description_1: description,
            member_1: participants,
            start_at_1: start_at,
            end_at_1: end_at,
            result: result,
            walver_criteria_1: walver_criteria,
            project_id: project_id,
            id: id
        };
    } else if (type == TYPE_MEMBER_COMMUNICATION) {
        member = $('.input-member-' + className + '-' + id).val();
        role = $('.input-role-' + className + '-' + id).val();
        contact_address = $('.input-contact_address-' + className + '-' + id).val();
        responsibility = $('.input-responsibility-' + className + '-' + id).val();
        data = {
            _token: token,
            number_record: 1,
            employee_1: member,
            role_1: role,
            type: typeMemberCommunication,
            contact_address_1: contact_address,
            responsibility_1: responsibility,
            project_id: project_id,
            id: id
        };
    } else if (type == TYPE_CUSTOMER_COMMUNICATION) {
        member = $('.input-customer-' + className + '-' + id).val();
        role = $('.input-role-' + className + '-' + id).val();
        contact_address = $('.input-contact_address-' + className + '-' + id).val();
        responsibility = $('.input-responsibility-' + className + '-' + id).val();
        data = {
            _token: token,
            number_record: 1,
            employee_1: member,
            role_1: role,
            type: typeCustomerCommunication,
            contact_address_1: contact_address,
            responsibility_1: responsibility,
            project_id: project_id,
            id: id
        };
    } else if (type == TYPE_SKILL_REQUEST) {
        skill = $('.input-skill-' + className + '-' + id).val();
        category = $('.input-category-' + className + '-' + id).val();
        course_name = $('.input-course_name-' + className + '-' + id).val();
        mode = $('.input-mode-' + className + '-' + id).val();
        provider = $('.input-provider-' + className + '-' + id).val();
        required_role = $('.input-required_role-' + className + '-' + id).val();
        hours = $('.input-hours-' + className + '-' + id).val();
        level = $('.input-level-' + className + '-' + id).val();
        remark = $('.input-remark-' + className + '-' + id).val();
        data = {
            _token: token,
            skill_1: skill,
            category_1: category,
            course_name_1: course_name,
            mode_1: mode,
            provider_1: provider,
            required_role_1: required_role,
            hours_1: hours,
            level_1: level,
            remark_1: remark,
            number_record: 1,
            project_id: project_id,
            id: id
        };
    } else if (type == TYPE_SECURITY) {
        content = $('.input-content-' + className + '-' + id).val();
        description = $('.input-description-' + className + '-' + id).val();
        participants = $('.input-participants-' + className + '-' + id).val();
        period = $('.input-period-' + className + '-' + id).val();
        procedure = $('.input-procedure-' + className + '-' + id).val();
        data = {
            _token: token,
            number_record: 1,
            content_1: content,
            description_1: description,
            member_1: participants,
            period_1: period,
            procedure_1: procedure,
            project_id: project_id,
            id: id
        };
    } else if (type == TYPE_PROJECT_MEMBER) {
        typeMember = $('.input-type-' + className + '-' + id).val();
        start_at = $('.input-start_at-' + className + '-' + id).val();
        end_at = $('.input-end_at-' + className + '-' + id).val();
        effort = $('.input-effort-' + className + '-' + id).val();
        employee_id = $('.input-employee_id-' + className + '-' + id).val();
        data = {
            _token: token,
            number_record: 1,
            type: typeMember,
            start_at: start_at,
            end_at: end_at,
            effort: effort,
            project_id: project_id,
            employee_id: employee_id,
            id: id
        };
    } else if (type == TYPE_COMMUNICATION) {
        content = $('.input-content-' + className + '-' + id).val();
        data = {
            _token: token,
            number_record: 1,
            content_1: content,
            project_id: project_id,
            id: id
        };
    } else if (type == TYPE_EXTERNAL_INTERFACE) {
        name = $('.input-name-' + className + '-' + id).val();
        position = $('.input-position-' + className + '-' + id).val();
        responsibilities = $('.input-responsibilities-' + className + '-' + id).val();
        contact = $('.input-contact-' + className + '-' + id).val();
        data = {
            _token: token,
            number_record: 1,
            name_1: name,
            position_1: position,
            responsibilities_1: responsibilities,
            contact_1: contact,
            project_id: project_id,
            id: id
        };
    } else if (type == TYPE_DELIVERABLE) {
        title = $('.input-title-' + className + '-' + id).val();
        committed_date = $('.input-committed_date-' + className + '-' + id).val();
        actual_date = $('.input-actual_date-' + className + '-' + id).val();
        stage = $('.input-stage-' + className + '-' + id).val();
        note = $('.input-note-' + className + '-' + id).val();
        data = {
            _token: token,
            number_record: 1,
            title_1: title,
            committed_date_1: committed_date,
            actual_date_1: actual_date,
            stage_1: stage,
            note_1: note,
            project_id: project_id,
            id: id
        };
    } else if (type == TYPE_TOOL_AND_INFRASTRUCTURE) {
        soft_ware_id = $('.input-soft_hard_ware-' + className + '-' + id).val();
        soft_hard_ware = $('.input-soft_hard_ware-' + className + '-' + id + ' option[value='+ soft_ware_id +']').text();
        purpose = $('.input-purpose-' + className + '-' + id).val();
        note = $('.input-note-' + className + '-' + id).val();
        start_date = $('.input-start-date-' + className + '-' + id).val();
        end_date = $('.input-end-date-' + className + '-' + id).val();
        data = {
            _token: token,
            number_record: 1,
            soft_hard_ware_1: soft_hard_ware,
            soft_ware_id_1: soft_ware_id,
            purpose_1: purpose,
            note_1: note,
            start_date_1: start_date,
            end_date_1 : end_date,
            project_id: project_id,
            id: id
        };
        globalData = data;
        globalurl = url;
        globalClassName = className;
    } else if (type == TYPE_WO_DEVICES_EXPENSE) {
        date = $('.input-time-' + className + '-' + id).val();
        amount = $('.input-amount-' + className + '-' + id).val().split(",");
        var inputUpdatedPoint = parseFloat(amount[0].split(".").join(""));
        description = $('.input-description-' + className + '-' + id).val();
        data = {
            _token: token,
            number_record: 1,
            time_1: date,
            amount_1: inputUpdatedPoint,
            description_1: description,
            project_id: project_id,
            id: id
        };
    } else {
        content = $('.input-content-' + className + '-' + id).val();
        expected_date = $('.input-expected_date-' + className + '-' + id).val();
        data = {
            _token: token,
            number_record: 1,
            content_1: content,
            expected_date_1: expected_date,
            project_id: project_id,
            id: id
        };
    }
    url = url;
    $.ajax({
        url: url,
        type: 'post',
        dataType: 'json',
        data: data,
        success: function (data) {
            if (data.status) {
                showOrHideButtonSubmitWorkorder(data.isCheckShowSubmit);
                $('.workorder-' + className).html(data.content);
                $('[data-toggle="tooltip"], .tooltip').tooltip("hide");
                if (type == TYPE_PROJECT_MEMBER) {
                    $('.employee_id_select2').select2().on('select2:close', function(evt) { tabToChange (evt)});
                    $('.tr-project .type-project-member').select2().on('select2:close', function(evt) { tabToChange (evt)});
                    iniTableSorter();
                }
                if (type == TYPE_STAGE_AND_MILESTONE) {
                    $('.stage-name-' + id).text(stageName);
                    $('.option-select-stage-' + id).text(stageName);
                    $('.select-stage').select2().on('select2:close', function(evt) { tabToChange (evt)});
                    classStageName = $('.stage-name-' + id);
                    classStageName.each(function(index, el) {
                        if ($(this).hasClass('display-none')) {
                            $(this).parent('.td-deliverable').find('.select2-container').addClass('display-block select2-container--focus');
                        }
                    });
                }
                if (type == TYPE_DELIVERABLE) {
                    $('.select-stage-deliverable').select2().on('select2:close', function(evt) { tabToChange (evt)});
                }
                if (type == TYPE_ASSUMPTIONS) {
                    $('#workorder_assumptions').html(data.content);
                }

                if (type == TYPE_CONSTRAINTS) {
                    $('#workorder_constraints').html(data.content);
                }

                if (type == TYPE_CRITICAL_DEPENDENCIES) {
                    $('#workorder_critical-dependencies').html(data.content);
                }

                if (type == TYPE_SKILL_REQUEST) {
                    $('#workorder_skill_request').html(data.content);
                }

                if (type == TYPE_MEMBER_COMMUNICATION) {
                    $('#workorder_member_communication').html(data.content);
                }
                if (type == TYPE_COMMUNICATION_MEETING || type == TYPE_COMMUNICATION_REPORT || type == TYPE_COMMUNICATION_OTHER) {
                    $('#workorder_communication').html(data.content);
                }

                if (type == TYPE_CUSTOMER_COMMUNICATION) {
                    $('#workorder_customer_communication').html(data.content);
                }

                if (type == TYPE_WO_DEVICES_EXPENSE) {
                    $('#table-derived-expenses input.time-datepicker').datepicker({
                        format: 'yyyy-mm',
                        weekStart: 1,
                        todayHighlight: true,
                        autoclose: true,
                        viewMode: "months",
                        minViewMode: "months"
                    });
                }
            } else {
                if (data.message_error) {
                    if (data.message_error.content_1) {
                        $('.input-content-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="content">' + data.message_error.content_1[0] + '</p>');
                    }
                    if (data.message_error.description_1) {
                        $('.input-description-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="description">' + data.message_error.description_1[0] + '</p>');
                    }
                    if (data.message_error.milestone_1) {
                        $('.input-milestone-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="milestone">' + data.message_error.milestone_1[0] + '</p>');
                    }
                    if (data.message_error.topic_1) {
                        $('.input-topic-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="topic">' + data.message_error.topic_1[0] + '</p>');
                    }
                    if (data.message_error.participants_1) {
                        $('.input-participants-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="participants">' + data.message_error.participants_1[0] + '</p>');
                    }
                    if (data.message_error.time_1) {
                        $('.input-time-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="time">' + data.message_error.time_1[0] + '</p>');
                    }
                    if (data.message_error.walver_criteria_1) {
                        $('.input-walver_criteria-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="walver_criteria">' + data.message_error.walver_criteria_1[0] + '</p>');
                    }
                    if (data.message_error.description_constraints_1) {
                        $('.input-description-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="description_constraints">' + data.message_error.description_constraints_1[0] + '</p>');
                    }
                    if (data.message_error.description_assumptions_1) {
                        $('.input-description-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="description_assumptions">' + data.message_error.description_assumptions_1[0] + '</p>');
                    }
                    if (data.message_error.description_security_1) {
                        $('.input-description-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="description_security">' + data.message_error.description_security_1[0] + '</p>');
                    }
                    if (data.message_error.period_security_1) {
                        $('.input-period-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="period_security">' + data.message_error.period_security_1[0] + '</p>');
                    }
                    if (data.message_error.procedure_security_1) {
                        $('.input-procedure-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="procedure_security">' + data.message_error.procedure_security_1[0] + '</p>');
                    }
                    if (data.message_error.contact_1) {
                        $('.input-contact-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="contact">' + data.message_error.contact_1[0] + '</p>');
                    }
                    if (data.message_error.skill_1) {
                        $('.input-skill-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="skill">' + data.message_error.skill_1[0] + '</p>');
                    }
                    if (data.message_error.category_1) {
                        $('.input-category-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="category">' + data.message_error.category_1[0] + '</p>');
                    }
                    if (data.message_error.course_name_1) {
                        $('.input-course_name-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="course_name">' + data.message_error.course_name_1[0] + '</p>');
                    }
                    if (data.message_error.mode_1) {
                        $('.input-mode-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="mode">' + data.message_error.mode_1[0] + '</p>');
                    }
                    if (data.message_error.provider_1) {
                        $('.input-provider-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="provider">' + data.message_error.provider_1[0] + '</p>');
                    }
                    if (data.message_error.required_role_1) {
                        $('.input-required_role-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="required_role">' + data.message_error.required_role_1[0] + '</p>');
                    }
                    if (data.message_error.hours_1) {
                        $('.input-hours-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="hours">' + data.message_error.hours_1[0] + '</p>');
                    }
                    if (data.message_error.level_1) {
                        $('.input-level-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="level">' + data.message_error.level_1[0] + '</p>');
                    }
                    if (data.message_error.remark_1) {
                        $('.input-remark-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="remark">' + data.message_error.remark_1[0] + '</p>');
                    }
                    if (data.message_error.customer_1) {
                        $('.input-customer-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="customer">' + data.message_error.customer_1[0] + '</p>');
                    }
                    if (data.message_error.name_1) {
                        $('.input-name-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="name">' + data.message_error.name_1[0] + '</p>');
                    }
                    if (data.message_error.position_1) {
                        $('.input-position-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="position">' + data.message_error.position_1[0] + '</p>');
                    }
                    if (data.message_error.responsibilities_1) {
                        $('.input-responsibilities-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="responsibilities">' + data.message_error.responsibilities_1[0] + '</p>');
                    }
                    if (data.message_error.soft_hard_ware) {
                        $('.input-soft_hard_ware-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="soft_hard_ware">' + data.message_error.soft_hard_ware[0] + '</p>');
                    }
                    if (data.message_error.purpose) {
                        $('.input-purpose-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="purpose">' + data.message_error.purpose[0] + '</p>');
                    }
                    if (data.message_error.title_1) {
                        $('.input-title-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="title_1">' + data.message_error.title_1[0] + '</p>');
                    }
                    if (data.message_error.committed_date_1) {
                        $('.input-committed_date-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="committed_date_1">' + data.message_error.committed_date_1[0] + '</p>');
                    }
                    if (data.message_error.actual_date_1) {
                        $('.input-actual_date-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="actual_date_1">' + data.message_error.actual_date_1[0] + '</p>');
                    }
                    if (data.message_error.stage_1) {
                        $('.tr-stage-'+ id +' .td-stage .select2-container').after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="stage_1">' + data.message_error.stage_1[0] + '</p>');
                    }
                    if (data.message_error.soft_hard_ware_1) {
                        $('.input-soft_hard_ware-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="soft_hard_ware_1">' + data.message_error.soft_hard_ware_1[0] + '</p>');
                    }
                    if (data.message_error.purpose_1) {
                        $('.input-purpose-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="purpose_1">' + data.message_error.purpose_1[0] + '</p>');
                    }
                    if (data.message_error.type) {
                        $('.input-type-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="type">' + data.message_error.type[0] + '</p>');
                    }
                    if (data.message_error.type_1) {
                        $('.input-type-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="type">' + data.message_error.type_1[0] + '</p>');
                    }
                    if (data.message_error.method_1) {
                        $('.input-method-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="method">' + data.message_error.method_1[0] + '</p>');
                    }
                    if (data.message_error.employee_id) {
                        $('.input-employee_id-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="employee_id">' + data.message_error.employee_id[0] + '</p>');
                    }
                    if (data.message_error.start_at) {
                        $('.input-start_at-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="start_at">' + data.message_error.start_at[0] + '</p>');
                    }
                    if (data.message_error.end_at) {
                        $('.input-end_at-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="end_at">' + data.message_error.end_at[0] + '</p>');
                    }
                    if (data.message_error.effort) {
                        $('.input-effort-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="effort">' + data.message_error.effort[0] + '</p>');
                    }
                    if (data.message_error.qua_gate_plan_1) {
                        $('.input-qua_gate_plan-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="qua_gate_plan_1">' + data.message_error.qua_gate_plan_1[0] + '</p>');
                    }
                    if (data.message_error.qua_gate_actual_1) {
                        $('.input-qua_gate_actual-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="qua_gate_actual_1">' + data.message_error.qua_gate_actual_1[0] + '</p>');
                    }
                    if (data.message_error.stage_1) {
                        $('.tr-deliverable-'+ id +' .td-deliverable .select2-container').after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="stage_1">' + data.message_error.stage_1[0] + '</p>');
                    }
                    if (data.message_error.role_member_communication_1) {
                        $('.tr-member_communication-' + id + ' .td-member_communication-role .select2-container').after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="member_communication">' + data.message_error.role_member_communication_1[0] + '</p>')
                    }
                    if (data.message_error.role_customer_communication_1) {
                        $('.tr-customer_communication-' + id + ' .td-customer_communication-role .select2-container').after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="customer_communication">' + data.message_error.role_customer_communication_1[0] + '</p>')
                    }
                    if (data.message_error.start_at_1) {
                        $('.input-start_at-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="start_at">' + data.message_error.start_at_1[0] + '</p>');
                    }
                    if (data.message_error.end_at_1) {
                        $('.input-end_at-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="end_at">' + data.message_error.end_at_1[0] + '</p>');
                    }
                    if (data.message_error.start_date_1) {
                        $('.input-start-date-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="start_date">' + data.message_error.start_date_1[0] + '</p>');
                    }
                    if (data.message_error.end_date_1) {
                        $('.input-end-date-' + className + '-' + id).after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate" for="end_date">' + data.message_error.end_date_1[0] + '</p>');
                    }
                    if (data.message_error.member_1) {
                        $('.tr-training-' + id + ' .td-training-member .select2-container').after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate">' + data.message_error.member_1[0] + '</p>');
                    }
                    if (data.message_error.member_1) {
                        $('.tr-security-' + id + ' .td-security-member .select2-container').after('<p class="word-break error-validate-' + className + '-' + id + ' error-' + className + ' error-validate">' + data.message_error.member_1[0] + '</p>');
                    }
                }
                if(data.warning) {
                    $('.show-warning').remove();
                    $('.end-date-' + className).after('<button class="show-warning hidden" type="button" data-noti="'+ data.content + '"></button>');
                    $('.show-warning').addClass('warn-confirm').click();
                }
            }
        },
        error: function () {
            $('#modal-warning-notification .text-default').html(messageError);
            $('#modal-warning-notification').modal('show');
        },
        complete: function () {
            $(e).data('requestRunning', false);
            $('#loading-item-' + type + '-' + id).addClass('display-none');
            $(e).removeClass('display-none');
        },
    });
}

function editWorkorderNotApproved(className, element, type) {
    type = assignDefaultValue(type);
    id = $(element).data('id');
    $(element).addClass('display-none');
    $('.save-' + className + '-' + id).removeClass('display-none');
    $('.delete-' + className + '-' + id).addClass('display-none');
    $('.refresh-' + className + '-' + id).removeClass('display-none');
    if (type == TYPE_STAGE_AND_MILESTONE) {
        setTimeout(function () {
            input = $('.input-stage-' + className + '-' + id)
            input.focus();
            var tmpStr = input.val();
            input.val('');
            input.val(tmpStr);
        }, 0);

        $('.stage-' + className + '-' + id).addClass('display-none');
        $('.input-stage-' + className + '-' + id).removeClass('display-none');

        $('.description-' + className + '-' + id).addClass('display-none');
        $('.input-description-' + className + '-' + id).removeClass('display-none');

        $('.milestone-' + className + '-' + id).addClass('display-none');
        $('.input-milestone-' + className + '-' + id).removeClass('display-none');

        $('.qua_gate_actual-' + className + '-' + id).addClass('display-none');
        $('.input-qua_gate_actual-' + className + '-' + id).removeClass('display-none');

        $('.qua_gate_plan-' + className + '-' + id).addClass('display-none');
        $('.input-qua_gate_plan-' + className + '-' + id).removeClass('display-none');

        if ($('.qua_gate_actual-' + className + '-' + id).data('status')) {
            $('.qua_gate_result-' + className + '-' + id).addClass('display-none');
            $('.span-qua_gate_result-' + className + '-' + id).removeClass('display-none');
        }
    } else if (type == TYPE_ASSUMPTIONS || type == TYPE_CONSTRAINTS) {
        setTimeout(function () {
            input = $('.input-description-' + className + '-' + id)
            input.focus();
            var tmpStr = input.val();
            input.val('');
            input.val(tmpStr);
        }, 0);

        $('.description-' + className + '-' + id).addClass('display-none');
        $('.input-description-' + className + '-' + id).removeClass('display-none');

        $('.remark-' + className + '-' + id).addClass('display-none');
        $('.input-remark-' + className + '-' + id).removeClass('display-none');
    } else if (type == TYPE_TRAINING) {
        setTimeout(function () {
            input = $('.input-topic-' + className + '-' + id)
            input.focus();
            var tmpStr = input.val();
            input.val('');
            input.val(tmpStr);
        }, 0);

        $('.topic-' + className + '-' + id).addClass('display-none');
        $('.input-topic-' + className + '-' + id).removeClass('display-none');

        $('.description-' + className + '-' + id).addClass('display-none');
        $('.input-description-' + className + '-' + id).removeClass('display-none');

        $('.participants-' + className + '-' + id).addClass('display-none');
        $('.input-participants-' + className + '-' + id).removeClass('display-none');

        $('.start_at-' + className + '-' + id).addClass('display-none');
        $('.input-start_at-' + className + '-' + id).removeClass('display-none');

        $('.end_at-' + className + '-' + id).addClass('display-none');
        $('.input-end_at-' + className + '-' + id).removeClass('display-none');

        $('.result-' + className + '-' + id).addClass('display-none');
        $('.input-result-' + className + '-' + id).removeClass('display-none');

        $('.walver_criteria-' + className + '-' + id).addClass('display-none');
        $('.input-walver_criteria-' + className + '-' + id).removeClass('display-none');
    } else if (type == TYPE_COMMUNICATION_MEETING || type == TYPE_COMMUNICATION_REPORT || type == TYPE_COMMUNICATION_OTHER) {
        setTimeout(function () {
            input = $('.input-type-' + className + '-' + id)
            input.focus();
            var tmpStr = input.val();
            input.val('');
            input.val(tmpStr);
        }, 0);
        $('.type-' + className + '-' + id).addClass('display-none');
        $('.input-type-' + className + '-' + id).removeClass('display-none');
        $('.method-' + className + '-' + id).addClass('display-none');
        $('.input-method-' + className + '-' + id).removeClass('display-none');
        $('.time-' + className + '-' + id).addClass('display-none');
        $('.input-time-' + className + '-' + id).removeClass('display-none');
        $('.information-' + className + '-' + id).addClass('display-none');
        $('.input-information-' + className + '-' + id).removeClass('display-none');
        $('.stakeholder-' + className + '-' + id).addClass('display-none');
        $('.input-stakeholder-' + className + '-' + id).removeClass('display-none');
    } else if (type == TYPE_MEMBER_COMMUNICATION) {
        setTimeout(function () {
            input = $('.input-member-' + className + '-' + id)
            input.focus();
            var tmpStr = input.val();
            input.val('');
            input.val(tmpStr);
        }, 0);

        $('.member-' + className + '-' + id).addClass('display-none');
        $('.input-member-' + className + '-' + id).removeClass('display-none');

        $('.role-' + className + '-' + id).addClass('display-none');
        $('.input-role-' + className + '-' + id).removeClass('display-none');

        $('.contact_address-' + className + '-' + id).addClass('display-none');
        $('.input-contact_address-' + className + '-' + id).removeClass('display-none');

        $('.responsibility-' + className + '-' + id).addClass('display-none');
        $('.input-responsibility-' + className + '-' + id).removeClass('display-none');
    } else if (type == TYPE_CUSTOMER_COMMUNICATION) {
        setTimeout(function () {
            input = $('.input-customer-' + className + '-' + id)
            input.focus();
            var tmpStr = input.val();
            input.val('');
            input.val(tmpStr);
        }, 0);

        $('.customer-' + className + '-' + id).addClass('display-none');
        $('.input-customer-' + className + '-' + id).removeClass('display-none');

        $('.role-' + className + '-' + id).addClass('display-none');
        $('.input-role-' + className + '-' + id).removeClass('display-none');

        $('.contact_address-' + className + '-' + id).addClass('display-none');
        $('.input-contact_address-' + className + '-' + id).removeClass('display-none');

        $('.responsibility-' + className + '-' + id).addClass('display-none');
        $('.input-responsibility-' + className + '-' + id).removeClass('display-none');
    } else if (type == TYPE_SKILL_REQUEST) {
        setTimeout(function () {
            input = $('.input-skill-' + className + '-' + id)
            input.focus();
            var tmpStr = input.val();
            input.val('');
            input.val(tmpStr);
        }, 0);

        $('.skill-' + className + '-' + id).addClass('display-none');
        $('.input-skill-' + className + '-' + id).removeClass('display-none');

        $('.category-' + className + '-' + id).addClass('display-none');
        $('.input-category-' + className + '-' + id).removeClass('display-none');

        $('.course_name-' + className + '-' + id).addClass('display-none');
        $('.input-course_name-' + className + '-' + id).removeClass('display-none');

        $('.mode-' + className + '-' + id).addClass('display-none');
        $('.input-mode-' + className + '-' + id).removeClass('display-none');

        $('.provider-' + className + '-' + id).addClass('display-none');
        $('.input-provider-' + className + '-' + id).removeClass('display-none');

        $('.required_role-' + className + '-' + id).addClass('display-none');
        $('.input-required_role-' + className + '-' + id).removeClass('display-none');

        $('.hours-' + className + '-' + id).addClass('display-none');
        $('.input-hours-' + className + '-' + id).removeClass('display-none');

        $('.level-' + className + '-' + id).addClass('display-none');
        $('.input-level-' + className + '-' + id).removeClass('display-none');

        $('.remark-' + className + '-' + id).addClass('display-none');
        $('.input-remark-' + className + '-' + id).removeClass('display-none');
    } else if (type == TYPE_SECURITY) {
        setTimeout(function () {
            input = $('.input-content-' + className + '-' + id)
            input.focus();
            var tmpStr = input.val();
            input.val('');
            input.val(tmpStr);
        }, 0);

        $('.content-' + className + '-' + id).addClass('display-none');
        $('.input-content-' + className + '-' + id).removeClass('display-none');

        $('.description-' + className + '-' + id).addClass('display-none');
        $('.input-description-' + className + '-' + id).removeClass('display-none');

        $('.participants-' + className + '-' + id).addClass('display-none');
        $('.input-participants-' + className + '-' + id).removeClass('display-none');

        $('.period-' + className + '-' + id).addClass('display-none');
        $('.input-period-' + className + '-' + id).removeClass('display-none');

        $('.procedure-' + className + '-' + id).addClass('display-none');
        $('.input-procedure-' + className + '-' + id).removeClass('display-none');
    } else if (type == TYPE_PROJECT_MEMBER) {
        setTimeout(function () {
            input = $('.input-type-' + className + '-' + id)
            input.focus();
        }, 0);

        $('.type-' + className + '-' + id).addClass('display-none');
        $('.input-type-' + className + '-' + id).removeClass('display-none');

        $('.employee_id-' + className + '-' + id).addClass('display-none');
        $('.input-employee_id-' + className + '-' + id).removeClass('display-none');

        $('.start_at-' + className + '-' + id).addClass('display-none');
        $('.input-start_at-' + className + '-' + id).removeClass('display-none');

        $('.end_at-' + className + '-' + id).addClass('display-none');
        $('.input-end_at-' + className + '-' + id).removeClass('display-none');

        $('.effort-' + className + '-' + id).addClass('display-none');
        $('.input-effort-' + className + '-' + id).removeClass('display-none');
    } else if (type == TYPE_COMMUNICATION) {
        setTimeout(function () {
            input = $('.input-content-' + className + '-' + id)
            input.focus();
            var tmpStr = input.val();
            input.val('');
            input.val(tmpStr);
        }, 0);

        $('.content-' + className + '-' + id).addClass('display-none');
        $('.input-content-' + className + '-' + id).removeClass('display-none');
    } else if (type == TYPE_TOOL_AND_INFRASTRUCTURE) {
        setTimeout(function () {
            input = $('.input-soft_hard_ware-' + className + '-' + id)
            input.focus();
            var tmpStr = input.val();
            input.val('');
            input.val(tmpStr);
        }, 0);

        $('.soft_hard_ware-' + className + '-' + id).addClass('display-none');
        $('.input-soft_hard_ware-' + className + '-' + id).removeClass('display-none');

        $('.purpose-' + className + '-' + id).addClass('display-none');
        $('.input-purpose-' + className + '-' + id).removeClass('display-none');

        $('.start-date-' + className + '-' + id).addClass('display-none');
        $('.input-start-date-' + className + '-' + id).removeClass('display-none');

        $('.end-date-' + className + '-' + id).addClass('display-none');
        $('.input-end-date-' + className + '-' + id).removeClass('display-none');

        $('.note-' + className + '-' + id).addClass('display-none');
        $('.input-note-' + className + '-' + id).removeClass('display-none');
    } else if (type == TYPE_EXTERNAL_INTERFACE) {
        setTimeout(function () {
            input = $('.input-name-' + className + '-' + id)
            input.focus();
            var tmpStr = input.val();
            input.val('');
            input.val(tmpStr);
        }, 0);

        $('.name-' + className + '-' + id).addClass('display-none');
        $('.input-name-' + className + '-' + id).removeClass('display-none');

        $('.position-' + className + '-' + id).addClass('display-none');
        $('.input-position-' + className + '-' + id).removeClass('display-none');

        $('.responsibilities-' + className + '-' + id).addClass('display-none');
        $('.input-responsibilities-' + className + '-' + id).removeClass('display-none');

        $('.contact-' + className + '-' + id).addClass('display-none');
        $('.input-contact-' + className + '-' + id).removeClass('display-none');
    } else if (type == TYPE_DELIVERABLE) {
        setTimeout(function () {
            input = $('.input-title-' + className + '-' + id)
            input.focus();
            var tmpStr = input.val();
            input.val('');
            input.val(tmpStr);
        }, 0);

        $('.title-' + className + '-' + id).addClass('display-none');
        $('.input-title-' + className + '-' + id).removeClass('display-none');
        $('.committed_date-' + className + '-' + id).addClass('display-none');
        $('.input-committed_date-' + className + '-' + id).removeClass('display-none');
        $('.actual_date-' + className + '-' + id).addClass('display-none');
        $('.input-actual_date-' + className + '-' + id).removeClass('display-none');
        $('.stage-' + className + '-' + id).addClass('display-none');
        $('.input-stage-' + className + '-' + id).removeClass('display-none');
        $('.note-' + className + '-' + id).addClass('display-none');
        $('.input-note-' + className + '-' + id).removeClass('display-none');
    } else if (type == TYPE_WO_DEVICES_EXPENSE) {
        setTimeout(function () {
            input = $('.input-time-' + className + '-' + id)
            input.focus();
            var tmpStr = input.val();
            input.val('');
            input.val(tmpStr);
        }, 0);

        $('.time-' + className + '-' + id).addClass('display-none');
        $('.input-time-' + className + '-' + id).removeClass('display-none');
        $('.amount-' + className + '-' + id).addClass('display-none');
        $('.input-amount-' + className + '-' + id).removeClass('display-none');
        $('.description-' + className + '-' + id).addClass('display-none');
        $('.input-description-' + className + '-' + id).removeClass('display-none');
    } else {
        setTimeout(function () {
            input = $('.input-content-' + className + '-' + id)
            input.focus();
            var tmpStr = input.val();
            input.val('');
            input.val(tmpStr);
        }, 0);
        $('.content-' + className + '-' + id).addClass('display-none');
        $('.input-content-' + className + '-' + id).removeClass('display-none');
        $('.expected_date-' + className + '-' + id).addClass('display-none');
        $('.input-expected_date-' + className + '-' + id).removeClass('display-none');
    }
}

function submitWorkorderNotApproved(className, element, url, type) {
    if ($(element).data('requestRunning')) {
        return;
    }
    $(element).data('requestRunning', true);
    type = assignDefaultValue(type);
    $(element).addClass('display-none');
    $('#loading-item-' + type).removeClass('display-none');
    $('.error-validate-add-' + className).remove();
    url = url;
    if (type == TYPE_STAGE_AND_MILESTONE) {
        stage = $('.stage-' + className).val();
        stageName = $('.stage-' + className + ' option[value='+ stage +']').text();
        description = $('.description-' + className).val();
        milestone = $('.milestone-' + className).val();
        qua_gate_actual = $('.qua_gate_actual-' + className).val();
        qua_gate_plan = $('.qua_gate_plan-' + className).val();
        qua_gate_result = $('.span-qua_gate_result-' + className + ' input[name=qua_gate_result]:checked').val();
        data = {
            _token: token,
            stage_1: stage,
            description_1: description,
            milestone_1: milestone,
            qua_gate_actual_1: qua_gate_actual,
            qua_gate_plan_1: qua_gate_plan,
            qua_gate_result_1: qua_gate_result,
            number_record: 1,
            project_id: project_id
        };
    } else if (type == TYPE_ASSUMPTIONS) {
        description = $('.description-' + className).val();
        remark = $('.remark-' + className).val();
        data = {
            _token: token,
            number_record: 1,
            description_1: description,
            remark_1: remark,
            type: typeAssumption,
            project_id: project_id
        };
    } else if (type == TYPE_CONSTRAINTS) {
        description = $('.description-' + className).val();
        remark = $('.remark-' + className).val();
        data = {
            _token: token,
            number_record: 1,
            description_1: description,
            remark_1: remark,
            type: typeConstraints,
            project_id: project_id
        };
    } else if (type == TYPE_TRAINING) {
        topic = $('.topic-' + className).val();
        description = $('.description-' + className).val();
        member = $('.training-member-' + className).val();
        start_at = $('.start_at-' + className).val();
        end_at = $('.end_at-' + className).val();
        result = $('.result-' + className).val();
        walver_criteria = $('.walver_criteria-' + className).val();
        data = {
            _token: token,
            number_record: 1,
            topic_1: topic,
            description_1: description,
            member_1: member,
            start_at_1: start_at,
            end_at_1: end_at,
            result: result,
            walver_criteria_1: walver_criteria,
            project_id: project_id
        };
    } else if (type == TYPE_COMMUNICATION_MEETING) {
        typeCom = $('.type-' + className).val();
        method = $('.method-' + className).val();
        time = $('.time-' + className).val();
        information = $('.information-' + className).val();
        stakeholder = $('.stakeholder-' + className).val();
        data = {
            _token: token,
            number_record: 1,
            type_1: typeCom,
            method_1: method,
            time_1: time,
            information_1: information,
            stakeholder_1: stakeholder,
            type_task: typeMeetingCom,
            project_id: project_id
        };
    } else if (type == TYPE_COMMUNICATION_REPORT) {
        typeCom = $('.type-' + className).val();
        method = $('.method-' + className).val();
        time = $('.time-' + className).val();
        information = $('.information-' + className).val();
        stakeholder = $('.stakeholder-' + className).val();
        data = {
            _token: token,
            number_record: 1,
            type_1: typeCom,
            method_1: method,
            time_1: time,
            information_1: information,
            stakeholder_1: stakeholder,
            type_task: typeReportCom,
            project_id: project_id
        };
    } else if (type == TYPE_COMMUNICATION_OTHER) {
        typeCom = $('.type-' + className).val();
        method = $('.method-' + className).val();
        time = $('.time-' + className).val();
        information = $('.information-' + className).val();
        stakeholder = $('.stakeholder-' + className).val();
        data = {
            _token: token,
            number_record: 1,
            type_1: typeCom,
            method_1: method,
            time_1: time,
            information_1: information,
            stakeholder_1: stakeholder,
            type_task: typeOtherCom,
            project_id: project_id
        };
    } else if (type == TYPE_MEMBER_COMMUNICATION) {
        employee = $('.member_communication-member-' + className).val();
        role = $('.member_communication-role-' + className).val();
        contact_address = $('.contact_address-' + className).val();
        responsibility = $('.responsibility-' + className).val();
        data = {
            _token: token,
            number_record: 1,
            employee_1: employee,
            role_1: role,
            type: typeMemberCommunication,
            contact_address_1: contact_address,
            responsibility_1: responsibility,
            project_id: project_id
        };
    } else if (type == TYPE_CUSTOMER_COMMUNICATION) {
        employee = $('.customer-' + className).val();
        role = $('.customer_communication-role-' + className).val();
        contact_address = $('.contact_address-' + className).val();
        responsibility = $('.responsibility-' + className).val();
        data = {
            _token: token,
            number_record: 1,
            employee_1: employee,
            role_1: role,
            type: typeCustomerCommunication,
            contact_address_1: contact_address,
            responsibility_1: responsibility,
            project_id: project_id
        };
    } else if(type == TYPE_SKILL_REQUEST) {
        skill = $('.skill-' + className).val();
        category = $('.category-' + className).val();
        course_name = $('.course_name-' + className).val();
        mode = $('.mode-' + className).val();
        provider = $('.provider-' + className).val();
        required_role = $('.required_role-' + className).val();
        hours = $('.hours-' + className).val();
        level = $('.level-' + className).val();
        remark = $('.remark-' + className).val();
        data = {
            _token: token,
            number_record: 1,
            skill_1: skill,
            category_1: category,
            course_name_1: course_name,
            mode_1: mode,
            provider_1: provider,
            required_role_1: required_role,
            hours_1: hours,
            level_1: level,
            remark_1: remark,
            project_id: project_id
        };
    } else if (type == TYPE_SECURITY) {
        content = $('.content-' + className).val();
        description = $('.description-' + className).val();
        member = $('.security-member-' + className).val();
        procedure = $('.procedure-' + className).val();
        period = $('.period-' + className).val();
        result = $('.result-' + className).val();
        walver_criteria = $('.walver_criteria-' + className).val();
        data = {
            _token: token,
            number_record: 1,
            content_1: content,
            description_1: description,
            member_1: member,
            procedure_1: procedure,
            period_1: period,
            project_id: project_id
        };
    } else if (type == TYPE_PROJECT_MEMBER) {
        typeMember = $('.type-' + className).val();
        employee_id = $('.employee_id-' + className).val();
        start_at = $('.start_at-' + className).val();
        effort = $('.effort-' + className).val();
        end_at = $('.end_at-' + className).val();
        data = {
            _token: token,
            number_record: 1,
            type: typeMember,
            employee_id: employee_id,
            start_at: start_at,
            effort: effort,
            end_at: end_at,
            project_id: project_id
        };
    } else if (type == TYPE_COMMUNICATION) {
        content = $('.content-' + className).val();
        data = {
            _token: token,
            number_record: 1,
            content_1: content,
            project_id: project_id
        };
    } else if (type == TYPE_TOOL_AND_INFRASTRUCTURE) {
        soft_ware_id = $('.soft_hard_ware-' + className).val();
        soft_hard_ware = $('.soft_hard_ware-' + className + ' option[value='+ soft_ware_id +']').text();
        purpose = $('.purpose-' + className).val();
        note = $('.note-' + className).val();
        start_date = $('.start-date-' + className).val();
        end_date = $('.end-date-' + className).val();
        data = {
            _token: token,
            number_record: 1,
            soft_hard_ware_1: soft_hard_ware,
            soft_ware_id_1: soft_ware_id,
            purpose_1: purpose,
            note_1: note,
            start_date_1: start_date,
            end_date_1: end_date,
            project_id: project_id
        };
        globalData = data;
        globalurl = url;
        globalClassName = className;
    } else if (type == TYPE_EXTERNAL_INTERFACE) {
        name = $('.name-' + className).val();
        position = $('.position-' + className).val();
        responsibilities = $('.responsibilities-' + className).val();
        contact = $('.contact-' + className).val();
        data = {
            _token: token,
            number_record: 1,
            name_1: name,
            position_1: position,
            responsibilities_1: responsibilities,
            contact_1: contact,
            project_id: project_id
        };
    } else if (type == TYPE_DELIVERABLE) {
        title = $('.title-' + className).val();
        committed_date = $('.committed_date-' + className).val();
        actual_date = $('.actual_date-' + className).val();
        stage = $('.stage-' + className).val();
        note = $('.note-' + className).val();
        data = {
            _token: token,
            title_1: title,
            committed_date_1: committed_date,
            actual_date_1: actual_date,
            stage_1: stage,
            note_1: note,
            number_record: 1,
            project_id: project_id
        };
    } else if (type == TYPE_WO_DEVICES_EXPENSE) {
        date = $('.time-' + className).val();
        amount = $('.amount-' + className).val().split(",");
        var inputUpdatedPoint = parseFloat(amount[0].split(".").join(""));
        description = $('.description-' + className).val();
        data = {
            _token: token,
            time_1: date,
            amount_1: inputUpdatedPoint,
            description_1: description,
            number_record: 1,
            project_id: project_id
        };
    } else {
        content = $('.content-' + className).val();
        expected_date = $('.expected_date-' + className).val();
        data = {
            _token: token,
            content_1: content,
            expected_date_1: expected_date,
            number_record: 1,
            project_id: project_id
        };
    }
    $.ajax({
        url: url,
        type: 'post',
        data: data,
        success: function (data) {
            if (data.status) {
                $('.slove-' + className + ' .remove-' + className).addClass('display-none');
                $('.slove-' + className + ' .add-' + className).removeClass('display-none');
                showOrHideButtonSubmitWorkorder(data.isCheckShowSubmit);
                $('.workorder-' + className).html(data.content);
                $('[data-toggle="tooltip"], .tooltip').tooltip("hide");
                if (type == TYPE_PROJECT_MEMBER) {
                    $('.employee_id_select2').select2().on('select2:close', function(evt) { tabToChange (evt)});
                    $('.tr-project .type-project-member').select2().on('select2:close', function(evt) { tabToChange (evt)});
                    iniTableSorter();
                }
                if (type == TYPE_STAGE_AND_MILESTONE) {
                    $('.select-stage').select2().on('select2:close', function(evt) { tabToChange (evt)});
                    $('.select-stage-custom').append($("<option></option>")
                        .attr("value",data.id)
                        .text(stageName));
                }
                if (type == TYPE_DELIVERABLE) {
                    $('.select-stage-deliverable').select2().on('select2:close', function(evt) { tabToChange (evt)});
                }
                if (type == TYPE_ASSUMPTIONS) {
                    $('#workorder_assumptions').html(data.content);
                }
                if (type == TYPE_CONSTRAINTS) {
                    $('#workorder_constraints').html(data.content);
                }
                if (type == TYPE_CRITICAL_DEPENDENCIES) {
                    $('#workorder_critical-dependencies').html(data.content);
                }
                if (type == TYPE_SECURITY) {
                    $('#workorder_security').html(data.content);
                }
                if (type == TYPE_SKILL_REQUEST) {
                    $('#workorder_skill_request').html(data.content);
                }
                if (type == TYPE_MEMBER_COMMUNICATION) {
                    $('#workorder_member_communication').html(data.content);
                }
                if (type == TYPE_COMMUNICATION_MEETING || type == TYPE_COMMUNICATION_REPORT || type == TYPE_COMMUNICATION_OTHER) {
                    $('#workorder_communication').html(data.content);
                }
                if (type == TYPE_CUSTOMER_COMMUNICATION) {
                    $('#workorder_customer_communication').html(data.content);
                }
            } else {
                if (data.message_error) {
                    if (data.message_error.content_1) {
                        $('.content-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="content">' + data.message_error.content_1[0] + '</p>');
                    }
                    if (data.message_error.stage_1) {
                        $('.tr-stage-and-milestone .td-stage .select2-container').after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="stage">' + data.message_error.stage_1[0] + '</p>');
                    }
                    if (data.message_error.description) {
                        $('.description-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="description">' + data.message_error.description[0] + '</p>');
                    }
                    if (data.message_error.description_constraints_1) {
                        $('.description-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="description_constraints">' + data.message_error.description_constraints_1[0] + '</p>')
                    }
                    if (data.message_error.description_assumptions_1) {
                        $('.description-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="description_assumptions">' + data.message_error.description_assumptions_1[0] + '</p>')
                    }
                    if (data.message_error.role_member_communication_1) {
                        $('.tr-member_communication .td-member_communication-role .select2-container').after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="role_member_communication">' + data.message_error.role_member_communication_1[0] + '</p>')
                    }
                    if (data.message_error.role_customer_communication_1) {
                        $('.tr-customer_communication .td-customer_communication-role .select2-container').after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="role_customer_communication">' + data.message_error.role_customer_communication_1[0] + '</p>')
                    }
                    if (data.message_error.description_security_1) {
                        $('.description-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="description_security">' + data.message_error.description_security_1[0] + '</p>')
                    }
                    if (data.message_error.period_security_1) {
                        $('.period-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="period_security">' + data.message_error.period_security_1[0] + '</p>')
                    }
                    if (data.message_error.procedure_security_1) {
                        $('.procedure-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="procedure_security">' + data.message_error.procedure_security_1[0] + '</p>')
                    }
                    if (data.message_error.skill_1) {
                        $('.skill-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="skill">' + data.message_error.skill_1[0] + '</p>')
                    }
                    if (data.message_error.category_1) {
                        $('.category-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="category">' + data.message_error.category_1[0] + '</p>')
                    }
                    if (data.message_error.course_name_1) {
                        $('.course_name-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="course_name">' + data.message_error.course_name_1[0] + '</p>')
                    }
                    if (data.message_error.mode_1) {
                        $('.mode-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="mode">' + data.message_error.mode_1[0] + '</p>')
                    }
                    if (data.message_error.provider_1) {
                        $('.provider-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="provider">' + data.message_error.provider_1[0] + '</p>')
                    }
                    if (data.message_error.remark_1) {
                        $('.remark-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="remark">' + data.message_error.remark_1[0] + '</p>')
                    }
                    if (data.message_error.customer_1) {
                        $('.customer-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="customer">' + data.message_error.customer_1[0] + '</p>')
                    }
                    if (data.message_error.required_role_1) {
                        $('.required_role-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="required_role">' + data.message_error.required_role_1[0] + '</p>')
                    }
                    if (data.message_error.hours_1) {
                        $('.hours-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="hours">' + data.message_error.hours_1[0] + '</p>')
                    }
                    if (data.message_error.level_1) {
                        $('.level-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="level">' + data.message_error.level_1[0] + '</p>')
                    }
                    if (data.message_error.milestone) {
                        $('.milestone-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="milestone">' + data.message_error.milestone[0] + '</p>');
                    }
                    if (data.message_error.topic_1) {
                        $('.topic-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="topic">' + data.message_error.topic_1[0] + '</p>');
                    }
                    if (data.message_error.participants_1) {
                        $('.participants-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="participants">' + data.message_error.participants_1[0] + '</p>');
                    }
                    if (data.message_error.time_1) {
                        $('.time-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="time">' + data.message_error.time_1[0] + '</p>');
                    }
                    if (data.message_error.walver_criteria_1) {
                        $('.walver_criteria-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="walver_criteria">' + data.message_error.walver_criteria_1[0] + '</p>');
                    }
                    if (data.message_error.name_1) {
                        $('.name-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="name">' + data.message_error.name_1[0] + '</p>');
                    }
                    if (data.message_error.position_1) {
                        $('.position-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="position">' + data.message_error.position_1[0] + '</p>');
                    }
                    if (data.message_error.responsibilities_1) {
                        $('.responsibilities-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="responsibilities">' + data.message_error.responsibilities_1[0] + '</p>');
                    }
                    if (data.message_error.contact_1) {
                        $('.contact-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="contact">' + data.message_error.contact_1[0] + '</p>');
                    }
                    if (data.message_error.soft_hard_ware) {
                        $('.soft_hard_ware-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="soft_hard_ware">' + data.message_error.soft_hard_ware[0] + '</p>');
                    }
                    if (data.message_error.purpose) {
                        $('.purpose-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="purpose">' + data.message_error.purpose[0] + '</p>');
                    }
                    if (data.message_error.title_1) {
                        $('.title-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="title">' + data.message_error.title_1[0] + '</p>');
                    }
                    if (data.message_error.committed_date_1) {
                        $('.committed_date-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="committed_date">' + data.message_error.committed_date_1[0] + '</p>');
                    }
                    if (data.message_error.actual_date_1) {
                        $('.actual_date-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="actual_date">' + data.message_error.actual_date_1[0] + '</p>');
                    }
                    if (data.message_error.description_1) {
                        $('.description-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="description">' + data.message_error.description_1[0] + '</p>');
                    }
                    if (data.message_error.milestone_1) {
                        $('.milestone-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="milestone">' + data.message_error.milestone_1[0] + '</p>');
                    }
                    if (data.message_error.soft_hard_ware_1) {
                      $('.soft_hard_ware_' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="soft_hard_ware">' + data.message_error.soft_hard_ware_1[0] + '</p>');
                    }
                    if (data.message_error.purpose_1) {
                        $('.purpose-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="purpose">' + data.message_error.purpose_1[0] + '</p>');
                    }
                    if (data.message_error.type) {
                        $('.type-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="type">' + data.message_error.type[0] + '</p>');
                    }
                    if (data.message_error.type_1) {
                        $('.type-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="type">' + data.message_error.type_1[0] + '</p>');
                    }
                    if (data.message_error.method_1) {
                        $('.method-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="method">' + data.message_error.method_1[0] + '</p>');
                    }
                    if (data.message_error.employee_id) {
                        $('.employee_id-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="employee_id">' + data.message_error.employee_id[0] + '</p>');
                    }
                    if (data.message_error.start_at) {
                        $('.start_at-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="start_at">' + data.message_error.start_at[0] + '</p>');
                    }
                    if (data.message_error.end_at) {
                        $('.end_at-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="end_at">' + data.message_error.end_at[0] + '</p>');
                    }
                    if (data.message_error.effort) {
                        $('.effort-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="effort">' + data.message_error.effort[0] + '</p>');
                    }
                    if (data.message_error.qua_gate_actual_1) {
                        $('.qua_gate_actual-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="qua_gate_actual">' + data.message_error.qua_gate_actual_1[0] + '</p>');
                    }
                    if (data.message_error.qua_gate_plan_1) {
                        $('.qua_gate_plan-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="qua_gate_plan">' + data.message_error.qua_gate_plan_1[0] + '</p>');
                    }
                    if (data.message_error.stage_1) {
                        $('.tr-deliverable .td-deliverable .select2-container').after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="stage">' + data.message_error.stage_1[0] + '</p>');
                    }
                    if (data.message_error.start_at_1) {
                        $('.start_at-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="start_at">' + data.message_error.start_at_1[0] + '</p>');
                    }
                    if (data.message_error.end_at_1) {
                        $('.end_at-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="end_at">' + data.message_error.end_at_1[0] + '</p>');
                    }
                    if (data.message_error.member_1) {
                        $('.tr-training .td-training-member .select2-container').after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '">' + data.message_error.member_1[0] + '</p>');
                    }
                    if (data.message_error.employee_1) {
                        $('.tr-member_communication .td-member_communication-member .select2-container').after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '">' + data.message_error.employee_1[0] + '</p>');
                    }
                    if (data.message_error.role_1) {
                        $('.tr-member_communication .td-member_communication-role .select2-container').after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '">' + data.message_error.role_1[0] + '</p>');
                    }
                    if (data.message_error.member_1) {
                        $('.tr-security .td-security-member .select2-container').after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '">' + data.message_error.member_1[0] + '</p>');
                    }
                    if (data.message_error.start_date_1) {
                        $('.start-date-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="start_date">' + data.message_error.start_date_1[0] + '</p>');
                    }
                    if (data.message_error.end_date_1) {
                        $('.end-date-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="end_date">' + data.message_error.end_date_1[0] + '</p>');
                    }
                    if (data.message_error.amount_1) {
                        $('.amount-' + className).after('<p class="word-break error-validate error-validate-add-' + className + ' error-' + className + '" for="amount">' + data.message_error.amount_1[0] + '</p>');
                    }
                }
                if(data.warning) {
                    $('.show-warning').remove();
                    $('.end-date-' + className).after('<button class="show-warning hidden" type="button" data-noti="'+ data.content + '"></button>');
                    $('.show-warning').addClass('warn-confirm').click();
                }
            }
        },
        error: function () {
            $('#modal-warning-notification .text-default').html(messageError);
            $('#modal-warning-notification').modal('show');
        },
        complete: function () {
            $(element).data('requestRunning', false);
            $('#loading-item-' + type).addClass('display-none');
            $(element).removeClass('display-none');
        },
    });
}

function refreshWorkorderNotApproved(className, element, type) {
    type = assignDefaultValue(type);
    id = $(element).data('id');
    $('.error-validate-' + className + '-' + id).remove();
    $(element).addClass('display-none');
    $('.save-' + className + '-' + id).addClass('display-none');
    $('.edit-' + className + '-' + id).removeClass('display-none');
    $('.delete-' + className + '-' + id).removeClass('display-none');
    $('.refresh-' + className + '-' + id).addClass('display-none');
    if (type == TYPE_DELIVERABLE) {
        $('.title-' + className + '-' + id).removeClass('display-none');
        $('.input-title-' + className + '-' + id).val($('.title-' + className + '-' + id).text());
        $('.input-title-' + className + '-' + id).addClass('display-none');

        $('.committed_date-' + className + '-' + id).removeClass('display-none');
        $('.input-committed_date-' + className + '-' + id).val($('.committed_date-' + className + '-' + id).text());
        $('.input-committed_date-' + className + '-' + id).addClass('display-none');

        $('.actual_date-' + className + '-' + id).removeClass('display-none');
        $('.input-actual_date-' + className + '-' + id).addClass('display-none');
        $('.input-actual_date-' + className + '-' + id).val($('.actual_date-' + className + '-' + id).text());

        $('.stage-' + className + '-' + id).removeClass('display-none');
        $('.input-stage-' + className + '-' + id).addClass('display-none');
        $('.input-stage-' + className + '-' + id).val($('.stage-' + className + '-' + id).text());

        $('.note-' + className + '-' + id).removeClass('display-none');
        $('.input-note-' + className + '-' + id).addClass('display-none');
        $('.input-note-' + className + '-' + id).val($('.note-' + className + '-' + id).text());
    } else if (type == TYPE_STAGE_AND_MILESTONE) {
        $('.stage-' + className + '-' + id).removeClass('display-none');
        $('.input-stage-' + className + '-' + id).addClass('display-none');

        $('.description-' + className + '-' + id).removeClass('display-none');
        $('.input-description-' + className + '-' + id).addClass('display-none');
        $('.input-description-' + className + '-' + id).val($('.description-' + className + '-' + id).text());

        $('.milestone-' + className + '-' + id).removeClass('display-none');
        $('.input-milestone-' + className + '-' + id).addClass('display-none');
        $('.input-milestone-' + className + '-' + id).val($('.milestone-' + className + '-' + id).text());

        $('.qua_gate_actual-' + className + '-' + id).removeClass('display-none');
        $('.input-qua_gate_actual-' + className + '-' + id).addClass('display-none');
        if ($('.qua_gate_actual-' + className + '-' + id).data('status')) {
            $('.input-qua_gate_actual-' + className + '-' + id).val($('.qua_gate_actual-' + className + '-' + id).text());
        } else {
            $('.input-qua_gate_actual-' + className + '-' + id).val('');
        }

        $('.qua_gate_plan-' + className + '-' + id).removeClass('display-none');
        $('.input-qua_gate_plan-' + className + '-' + id).addClass('display-none');
        $('.input-qua_gate_plan-' + className + '-' + id).val($('.qua_gate_plan-' + className + '-' + id).text());

        if ($('.qua_gate_actual-' + className + '-' + id).data('status')) {
            text = $('.qua_gate_result-' + className + '-' + id).data('value') ? 'Pass' : 'Fail';
            $('.qua_gate_result-' + className + '-' + id).text(text);
        }
        $('.qua_gate_result-' + className + '-' + id).removeClass('display-none');
        $('.span-qua_gate_result-' + className + '-' + id).addClass('display-none');

        $('.milestone-' + className + '-' + id).removeClass('display-none');
        $('.input-milestone-' + className + '-' + id).addClass('display-none');
        $('.input-milestone-' + className + '-' + id).val($('.milestone-' + className + '-' + id).text());
    } else if (type == TYPE_TRAINING) {
        $('.topic-' + className + '-' + id).removeClass('display-none');
        $('.input-topic-' + className + '-' + id).addClass('display-none');
        $('.input-topic-' + className + '-' + id).val($('.topic-' + className + '-' + id).text());

        $('.description-' + className + '-' + id).removeClass('display-none');
        $('.input-description-' + className + '-' + id).addClass('display-none');
        $('.input-description-' + className + '-' + id).val($('.description-' + className + '-' + id).text());

        $('.participants-' + className + '-' + id).removeClass('display-none');
        $('.input-participants-' + className + '-' + id).addClass('display-none');
        oldValue = $('.participants-' + className + '-' + id).attr('data-value');
        arrayValue = oldValue.split(',');
        $('.input-participants-' + className + '-' + id).val(arrayValue);
        $('.start_at-' + className + '-' + id).removeClass('display-none');
        $('.input-start_at-' + className + '-' + id).addClass('display-none');
        $('.input-start_at-' + className + '-' + id).val($('.start_at-' + className + '-' + id).text());

        $('.end_at-' + className + '-' + id).removeClass('display-none');
        $('.input-end_at-' + className + '-' + id).addClass('display-none');
        $('.input-end_at-' + className + '-' + id).val($('.end_at-' + className + '-' + id).text());

        $('.result-' + className + '-' + id).removeClass('display-none');
        $('.input-result-' + className + '-' + id).addClass('display-none');
        oldValuResult = $('.result-' + className + '-' + id).attr('data-value');
        $('.input-result-' + className + '-' + id).val(oldValuResult);

        $('.walver_criteria-' + className + '-' + id).removeClass('display-none');
        $('.input-walver_criteria-' + className + '-' + id).addClass('display-none');
        $('.input-walver_criteria-' + className + '-' + id).val($('.walver_criteria-' + className + '-' + id).text());
    } else if (type == TYPE_COMMUNICATION_MEETING || type == TYPE_COMMUNICATION_REPORT || type == TYPE_COMMUNICATION_OTHER) {
        $('.type-' + className + '-' + id).removeClass('display-none');
        $('.input-type-' + className + '-' + id).addClass('display-none');
        $('.input-type-' + className + '-' + id).val($('.type-' + className + '-' + id).text());

        $('.method-' + className + '-' + id).removeClass('display-none');
        $('.input-method-' + className + '-' + id).addClass('display-none');
        $('.input-method-' + className + '-' + id).val($('.method-' + className + '-' + id).text());

        $('.time-' + className + '-' + id).removeClass('display-none');
        $('.input-time-' + className + '-' + id).addClass('display-none');
        $('.input-time-' + className + '-' + id).val($('.time-' + className + '-' + id).text());

        $('.information-' + className + '-' + id).removeClass('display-none');
        $('.input-information-' + className + '-' + id).addClass('display-none');
        $('.input-information-' + className + '-' + id).val($('.information-' + className + '-' + id).text());

        $('.stakeholder-' + className + '-' + id).removeClass('display-none');
        $('.input-stakeholder-' + className + '-' + id).addClass('display-none');
        $('.input-stakeholder-' + className + '-' + id).val($('.stakeholder-' + className + '-' + id).text());
    } else if (type == TYPE_MEMBER_COMMUNICATION) {
        $('.member-' + className + '-' + id).removeClass('display-none');
        $('.input-member-' + className + '-' + id).addClass('display-none');
        oldValue = $('.member-' + className + '-' + id).attr('data-value');
        $('.input-member-' + className + '-' + id).val(oldValue);

        $('.role-' + className + '-' + id).removeClass('display-none');
        $('.input-role-' + className + '-' + id).addClass('display-none');
        oldValueRole = $('.role-' + className + '-' + id).attr('data-value');
        arrayValue = oldValueRole.split(',');
        $('.input-role-' + className + '-' + id).val(arrayValue);

        $('.contact_address-' + className + '-' + id).removeClass('display-none');
        $('.input-contact_address-' + className + '-' + id).addClass('display-none');
        $('.input-contact_address-' + className + '-' + id).val($('.contact_address-' + className + '-' + id).text());

        $('.responsibility-' + className + '-' + id).removeClass('display-none');
        $('.input-responsibility-' + className + '-' + id).addClass('display-none');
        $('.input-responsibility-' + className + '-' + id).val($('.responsibility-' + className + '-' + id).text());
    } else if (type == TYPE_CUSTOMER_COMMUNICATION) {
        $('.customer-' + className + '-' + id).removeClass('display-none');
        $('.input-customer-' + className + '-' + id).addClass('display-none');
        $('.input-customer-' + className + '-' + id).val($('.customer-' + className + '-' + id).text());

        $('.role-' + className + '-' + id).removeClass('display-none');
        $('.input-role-' + className + '-' + id).addClass('display-none');
        oldValueRole = $('.role-' + className + '-' + id).attr('data-value');
        arrayValue = oldValueRole.split(',');
        $('.input-role-' + className + '-' + id).val(arrayValue);

        $('.contact_address-' + className + '-' + id).removeClass('display-none');
        $('.input-contact_address-' + className + '-' + id).addClass('display-none');
        $('.input-contact_address-' + className + '-' + id).val($('.contact_address-' + className + '-' + id).text());

        $('.responsibility-' + className + '-' + id).removeClass('display-none');
        $('.input-responsibility-' + className + '-' + id).addClass('display-none');
        $('.input-responsibility-' + className + '-' + id).val($('.responsibility-' + className + '-' + id).text());
    } else if (type == TYPE_SECURITY) {
        $('.content-' + className + '-' + id).removeClass('display-none');
        $('.input-content-' + className + '-' + id).addClass('display-none');
        $('.input-content-' + className + '-' + id).val($('.content-' + className + '-' + id).text());

        $('.description-' + className + '-' + id).removeClass('display-none');
        $('.input-description-' + className + '-' + id).addClass('display-none');
        $('.input-description-' + className + '-' + id).val($('.description-' + className + '-' + id).text());

        $('.participants-' + className + '-' + id).removeClass('display-none');
        $('.input-participants-' + className + '-' + id).addClass('display-none');
        oldValue = $('.participants-' + className + '-' + id).attr('data-value');
        arrayValue = oldValue.split(',');
        $('.input-participants-' + className + '-' + id).val(arrayValue);
        $('.period-' + className + '-' + id).removeClass('display-none');
        $('.input-period-' + className + '-' + id).addClass('display-none');
        $('.input-period-' + className + '-' + id).val($('.period-' + className + '-' + id).text());

        $('.procedure-' + className + '-' + id).removeClass('display-none');
        $('.input-procedure-' + className + '-' + id).addClass('display-none');
        $('.input-procedure-' + className + '-' + id).val($('.procedure-' + className + '-' + id).text());
    } else if (type == TYPE_ASSUMPTIONS || type == TYPE_CONSTRAINTS) {
        $('.description-' + className + '-' + id).removeClass('display-none');
        $('.input-description-' + className + '-' + id).addClass('display-none');
        $('.input-description-' + className + '-' + id).val($('.description-' + className + '-' + id).text());

        $('.remark-' + className + '-' + id).removeClass('display-none');
        $('.input-remark-' + className + '-' + id).addClass('display-none');
        $('.input-remark-' + className + '-' + id).val($('.remark-' + className + '-' + id).text());
    } else if (type == TYPE_SKILL_REQUEST) {
        $('.skill-' + className + '-' + id).removeClass('display-none');
        $('.input-skill-' + className + '-' + id).addClass('display-none');
        $('.input-skill-' + className + '-' + id).val($('.skill-' + className + '-' + id).text());

        $('.category-' + className + '-' + id).removeClass('display-none');
        $('.input-category-' + className + '-' + id).addClass('display-none');
        $('.input-category-' + className + '-' + id).val($('.category-' + className + '-' + id).text());

        $('.course_name-' + className + '-' + id).removeClass('display-none');
        $('.input-course_name-' + className + '-' + id).addClass('display-none');
        $('.input-course_name-' + className + '-' + id).val($('.course_name-' + className + '-' + id).text());

        $('.mode-' + className + '-' + id).removeClass('display-none');
        $('.input-mode-' + className + '-' + id).addClass('display-none');
        $('.input-mode-' + className + '-' + id).val($('.mode-' + className + '-' + id).text());

        $('.provider-' + className + '-' + id).removeClass('display-none');
        $('.input-provider-' + className + '-' + id).addClass('display-none');
        $('.input-provider-' + className + '-' + id).val($('.provider-' + className + '-' + id).text());

        $('.required_role-' + className + '-' + id).removeClass('display-none');
        $('.input-required_role-' + className + '-' + id).addClass('display-none');
        $('.input-required_role-' + className + '-' + id).val($('.required_role-' + className + '-' + id).text());

        $('.level-' + className + '-' + id).removeClass('display-none');
        $('.input-level-' + className + '-' + id).addClass('display-none');
        $('.input-level-' + className + '-' + id).val($('.level-' + className + '-' + id).text());

        $('.hours-' + className + '-' + id).removeClass('display-none');
        $('.input-hours-' + className + '-' + id).addClass('display-none');
        $('.input-hours-' + className + '-' + id).val($('.hours-' + className + '-' + id).text());

        $('.remark-' + className + '-' + id).removeClass('display-none');
        $('.input-remark-' + className + '-' + id).addClass('display-none');
        $('.input-remark-' + className + '-' + id).val($('.remark-' + className + '-' + id).text());
    } else if (type == TYPE_PROJECT_MEMBER) {
        $('.type-' + className + '-' + id).removeClass('display-none');
        $('.input-type-' + className + '-' + id).addClass('display-none');
        $('.input-type-' + className + '-' + id).val($('.type-' + className + '-' + id).attr('data-value'));

        $('.employee_id-' + className + '-' + id).removeClass('display-none');
        $('.input-employee_id-' + className + '-' + id).addClass('display-none');
        $('.input-employee_id-' + className + '-' + id).val($('.employee_id-' + className + '-' + id).attr('data-value'));

        $('.start_at-' + className + '-' + id).removeClass('display-none');
        $('.input-start_at-' + className + '-' + id).addClass('display-none');
        $('.input-start_at-' + className + '-' + id).val($('.start_at-' + className + '-' + id).text());

        $('.end_at-' + className + '-' + id).removeClass('display-none');
        $('.input-end_at-' + className + '-' + id).addClass('display-none');
        $('.input-end_at-' + className + '-' + id).val($('.end_at-' + className + '-' + id).text());

        $('.effort-' + className + '-' + id).removeClass('display-none');
        $('.input-effort-' + className + '-' + id).addClass('display-none');
        $('.input-effort-' + className + '-' + id).val($('.effort-' + className + '-' + id).text());
    } else if (type == TYPE_COMMUNICATION) {
        $('.content-' + className + '-' + id).removeClass('display-none');
        $('.input-content-' + className + '-' + id).addClass('display-none');
        $('.input-content-' + className + '-' + id).val($('.content-' + className + '-' + id).text());
    } else if (type == TYPE_EXTERNAL_INTERFACE) {
        $('.name-' + className + '-' + id).removeClass('display-none');
        $('.input-name-' + className + '-' + id).addClass('display-none');
        $('.input-name-' + className + '-' + id).val($('.name-' + className + '-' + id).text());

        $('.position-' + className + '-' + id).removeClass('display-none');
        $('.input-position-' + className + '-' + id).addClass('display-none');
        $('.input-position-' + className + '-' + id).val($('.position-' + className + '-' + id).text());

        $('.responsibilities-' + className + '-' + id).removeClass('display-none');
        $('.input-responsibilities-' + className + '-' + id).addClass('display-none');
        $('.input-responsibilities-' + className + '-' + id).val($('.responsibilities-' + className + '-' + id).text());

        $('.contact-' + className + '-' + id).removeClass('display-none');
        $('.input-contact-' + className + '-' + id).addClass('display-none');
        $('.input-contact-' + className + '-' + id).val($('.contact-' + className + '-' + id).text());
    } else if (type == TYPE_TOOL_AND_INFRASTRUCTURE) {
        $('.soft_hard_ware-' + className + '-' + id).removeClass('display-none');
        $('.input-soft_hard_ware-' + className + '-' + id).addClass('display-none');
        // $('.input-soft_hard_ware-' + className + '-' + id).val($('.soft_hard_ware-' + className + '-' + id).text());

        $('.start-date-' + className + '-' + id).removeClass('display-none');
        $('.input-start-date-' + className + '-' + id).addClass('display-none');
        $('.input-start-date-' + className + '-' + id).val($('.start-date-' + className + '-' + id).text());

        $('.end-date-' + className + '-' + id).removeClass('display-none');
        $('.input-end-date-' + className + '-' + id).addClass('display-none');
        $('.input-end-date-' + className + '-' + id).val($('.end-date-' + className + '-' + id).text());

        $('.purpose-' + className + '-' + id).removeClass('display-none');
        $('.input-purpose-' + className + '-' + id).addClass('display-none');
        $('.input-purpose-' + className + '-' + id).val($('.purpose-' + className + '-' + id).text());

        $('.note-' + className + '-' + id).removeClass('display-none');
        $('.input-note-' + className + '-' + id).addClass('display-none');
        $('.input-note-' + className + '-' + id).val($('.note-' + className + '-' + id).text());
    } else if (type == TYPE_WO_DEVICES_EXPENSE) {
        $('.time-' + className + '-' + id).removeClass('display-none');
        $('.input-time-' + className + '-' + id).addClass('display-none');
        $('.input-soft_hard_ware-' + className + '-' + id).val($('.time-' + className + '-' + id).text());

        $('.amount-' + className + '-' + id).removeClass('display-none');
        $('.input-amount-' + className + '-' + id).addClass('display-none');
        $('.input-amount-' + className + '-' + id).val($('.amount-' + className + '-' + id).text());

        $('.description-' + className + '-' + id).removeClass('display-none');
        $('.input-description-' + className + '-' + id).addClass('display-none');
        $('.input-description-' + className + '-' + id).val($('.description-' + className + '-' + id).text());
    } else {
        $('.content-' + className + '-' + id).removeClass('display-none');
        $('.input-content-' + className + '-' + id).addClass('display-none');
        $('.input-content-' + className + '-' + id).val($('.content-' + className + '-' + id).text());

        $('.expected_date-' + className + '-' + id).removeClass('display-none');
        $('.input-expected_date-' + className + '-' + id).addClass('display-none');
        $('.input-expected_date-' + className + '-' + id).val($('.expected_date-' + className + '-' + id).text());
    }
}

function showOrHideButtonSubmitWorkorder(status)
{
    if (status) {
        $('.submit-workorder').removeClass('display-none');
    } else {
        $('.submit-workorder').addClass('display-none');
    }
}

function loadData($tab, type) {
    checkReloadPage();
    if (load_data) {
        $('[data-toggle="tooltip"], .tooltip').tooltip("hide");
        arrayValue = ['summary', 'scope', 'orthers'];
        if(arrayValue.indexOf(type) == -1) {
            if (!$('#workorder-content-' + type + ' .table-content-' + type).length) {
                $spin = $('<p><i class="fa fa-2x fa-refresh fa-spin"></i></p>').prependTo($tab);
                url = urlGetContentTable;
                data = {
                    _token: token,
                    projectId: project_id,
                    type: type
                };
                $.ajax({
                    url: url,
                    data: data,
                    dataType: 'json',
                    type: 'post',
                    success:function(data) {

                        if (data.status) {
                            if (type == TYPE_PROJECT_MEMBER) {
                                rkWoAllocation.init(data.data, $('#workorder-content-' + type));
                                return true;
                            }
                            $('#workorder-content-' + type).html(data.content);
                            $(document).tooltip({ selector: ".is-tooltip",
                                placement: "top",
                                trigger: "hover",
                                animation: false
                            });
                            /*if (type == TYPE_PROJECT_MEMBER) {
                             $('.employee_id_select2').select2();
                             $('.tr-project .type-project-member').select2();
                             iniTableSorter();
                             }*/
                            if (type == TYPE_STAGE_AND_MILESTONE) {
                                $('.select-stage').select2();
                            }
                            if (type == TYPE_DELIVERABLE) {
                                $('.select-stage-deliverable').select2();
                            }
                        }
                    },
                    complete: function () {
                        $spin.remove();
                    },
                });
            }
        } else {
            if(type == 'summary') {
                $('#manager_id').select2().on('select2:close', function(evt) { tabToChange (evt)});
                $('#state').select2().on('select2:close', function(evt) { tabToChange (evt)});
                $('#manager_id').select2().on('select2:close', function(evt) { tabToChange (evt)});
                $('#cust_contact_id').select2().on('select2:close', function(evt) { tabToChange (evt)});
                $('.select_leader_id').select2().on('select2:close', function(evt) { tabToChange (evt)});
                $('#level').select2().on('select2:close', function(evt) { tabToChange (evt)});
                $('#type').select2().on('select2:close', function(evt) { tabToChange (evt)});
                $valueTypeproject = $('#type').attr('data-original-title');
                $("#type-project .select2-container").tooltip({
                    title: $valueTypeproject,
                });
                if($('#type-project .input-basic-info').hasClass('changed')) {
                    $('#type-project .select2-selection').css('background', '#f7f0cb');
                }

                if($('#status-project .input-basic-info').hasClass('changed')) {
                    $('#status-project .select2-selection').css('background', '#f7f0cb');
                }
                if($('#manager-project .input-basic-info').hasClass('changed')) {
                    $('#manager-project .select2-selection').css('background', '#f7f0cb');
                }
                $valueStatusproject = $('#state').attr('data-original-title');
                $("#status-project .select2-container").tooltip({
                    title: $valueStatusproject,
                });
                $valueManagerProject = $('#manager_id').attr('data-original-title');
                $("#manager-project .select2-container").tooltip({
                    title: $valueManagerProject,
                });
                if($('#select-team #team_id').hasClass('changed')) {
                    $('#select-team .multiselect').css('background', '#f7f0cb');
                }
                $valueTeam = $('#team_id').attr('data-original-title');
                $("#select-team .team-dropdown").tooltip({
                    title: $valueTeam,
                });
                $valueLeader = $('#leader_id').attr('data-original-title');
                $(".div-leader-id .select2-container").tooltip({
                    title: $valueLeader,
                });
                $valueTypeMM = $('#type_mm').attr('data-original-title');
                $('[same-id="type_mm"]').next('.select2-container').tooltip({
                    title: $valueTypeMM
                });
                if($('#leader_id').hasClass('changed')) {
                    $('.div-leader-id .select2-selection').css('background', '#f7f0cb');
                }
                if ($('#type_mm').hasClass('changed')) {
                    $('#type_mm').next('.select2-container').find('.select2-selection').css('background', '#f7f0cb');
                    $('[same-id="'+ $('#type_mm').attr('same-id') +'"]').next('.select2-container').find('.select2-selection').css('background', '#f7f0cb');
                }
            }
        }
    }
}

function showFormEditQuality(className) {
    $('.btn-edit-' + className).addClass('display-none');
    $('.input-' + className).removeClass('display-none');
    $('.btn-save-' + className).removeClass('display-none');
    $('.btn-cancel-' + className).removeClass('display-none');
    $('.btn-edit-' + className).addClass('display-none');
}
function hiddeFormEditQuality(className) {
    oldValue = $('.edit-draft-' + className).text();
    if (!oldValue) {
        oldValue = $('.content-' + className + '-approved').text();
    }
    $('.input-' + className).val(oldValue.trim());
    $('.btn-cancel-' + className).addClass('display-none');
    $('.input-' + className).addClass('display-none');
    $('.btn-edit-' + className).removeClass('display-none');
    $('.btn-save-' + className).addClass('display-none');
    $('.content-' + className + ' .error-validate').remove();
}

function iniTableSorter(columnDefault) {
    if (typeof columnDefault == 'undefined') {
        columnDefault = 4;
    }
    $("#table-project-member table").tablesorter({
        sortList: [[columnDefault,0]],
        headers: {
            '.no-sorter' : {
                sorter: false
            }
        },
        cssChildRow: "tablesorter-childRow"
    });
}

function getDataColumSort(type) {
    if (type == TYPE_PROJECT_MEMBER) {
        return $("#table-project-member table").find('th[aria-sort!=none]').attr('data-column');
    }
    return;
}

function displayTextDeleteConfirm(element) {
    modalConfirm = $('#modal-delete-confirm-new');
    if ($(element).hasClass('fa-times')) {
        modalConfirm.find('.text-default').hide();
        modalConfirm.find('.text-undo').hide();
        modalConfirm.find('.text-change').show();
    } else if($(element).hasClass('fa-undo')) {
        modalConfirm.find('.text-default').hide();
        modalConfirm.find('.text-undo').show();
        modalConfirm.find('.text-change').hide();
    } else {
        modalConfirm.find('.text-default').show();
        modalConfirm.find('.text-undo').hide();
        modalConfirm.find('.text-change').hide();
    }
}

function tabToChange(evt) {
    var context = $(evt.target);

    $(document).on('keydown.select2', function(e) {
        if (e.which === 9) { // tab
            var highlighted = context
                .data('select2')
                .$dropdown
                .find('.select2-results__option--highlighted');
            if (highlighted) {
                var id = highlighted.data('data').id;
                context.val(id).trigger('change');
            }
        }
    });

    // unbind the event again to avoid binding multiple times
    setTimeout(function() {
        $(document).off('keydown.select2');
    }, 1);
}
function checkReloadPage(type)
{
    if (typeof type != 'undefined') {
        setInterval(function(){
            sendRequestIsChangeStatusWo(type);
        }, 30000);
    } else {
        sendRequestIsChangeStatusWo();
    }
}

function sendRequestIsChangeStatusWo(type) {
    var data = {
        _token: token,
        status: statusWo,
        project_id: projectId
    }
    $.ajax({
        url: urlCheckIsChangeStatusWo,
        type: 'post',
        data: data,
        dataType: 'json',
        success: function(data) {
            if(data.status) {
                if (!$('#modal-warning-reload').is(':visible')) {
                    $('#modal-warning-reload').modal('show');
                }
                if (typeof type == 'undefined') {
                    load_data = false;
                }
            }
        }
    });
}
function tabToChange(evt) {
    var context = $(evt.target);

    $(document).on('keydown.select2', function(e) {
        if (e.which === 9) { // tab
            var highlighted = context
                .data('select2')
                .$dropdown
                .find('.select2-results__option--highlighted');
            if (highlighted) {
                var id = highlighted.data('data').id;
                context.val(id).trigger('change');
            }
        }
    });

    // unbind the event again to avoid binding multiple times
    setTimeout(function() {
        $(document).off('keydown.select2');
    }, 1);
}

$(document).on('click', '.popover-wo-other', function (event) {
    var elThis = $(this);
    var dataId = elThis.data('id');
    var name = elThis.attr('name');
    var type = elThis.data('type');
    if (type == 'assumptions' || type == 'constraints') {
        type = assumptionType;
    } else if(type == 'critical') {
        type = criticalType;
    } else if(type == 'security') {
        type = securityType;
    } else if(type == 'skill_request') {
        type = skillRequiredType;
    } else if(type == 'member_communication') {
        type = memberCommunicationType;
    } else if(type == 'customer_communication') {
        type = customerCommunicationType;
    } else if(type == 'communication') {
        type = projCommunicationType;
    }
    $('.popover-wo-other').not(elThis).popover('hide');
    var data = {
        _token: token,
        type: type,
        attribute: name,
        id: dataId
    };
    $.ajax({
        url: urlPopoverWoOther,
        type: 'post',
        data: data,
        dataType: 'json',
        success: function(data) {
            elThis.attr('data-content', data);
            elThis.popover({
                placement: 'bottom',
            });
            if (!elThis.hasClass("inActive")) {
                elThis.popover('show');
                elThis.addClass("inActive");
            } else {
                elThis.popover('hide');
                elThis.removeClass("inActive");
            }
        }
    });

});
