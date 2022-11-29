<?php
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\Task;
use Rikkei\Team\Model\Team;
use Rikkei\Project\View\ProjConst;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\Model\CriticalDependencie;
use Rikkei\Project\Model\AssumptionConstrain;
use Rikkei\Project\Model\Security;
use Rikkei\Project\Model\SkillRequest;
use Rikkei\Project\Model\MemberCommunication;
use Rikkei\Project\Model\CustomerCommunication;
use Rikkei\Project\Model\CommunicationProject;
use Rikkei\Core\View\CoreUrl;
?>
<script>
    /*
     * Format repo for template result select2
     */
    function formatRepo(response) {
        if (response.loading) {
            return response.text;
        }
        return markup  = (response.avatar)?
            "<div class='select2-result-repository clearfix'>" +
            "<div class='select2-result-repository__title'>" +
            "<img style=\"margin-right:8px;max-width: 32px;max-height: 32px;border-radius: 50%;\" src=\""+
            response.avatar+"\">" + response.text +
            "</div>" +
            "</div>"
            :
            "<div class='select2-result-repository clearfix'>" +
            "<div class='select2-result-repository__title'>" +
            "<i style='margin-right:8px' class='fa fa-user-circle fa-2x' aria-hidden='true'></i>" +
            response.text +
            "</div>" +
            "</div>";
    }

    /*
     * Format repo selection for template selection select2
     */
    function formatRepoSelection(response) {
        return response.text;
    }

    $(function() {
        /*
         * Select search employee by ajax select2
         */
        $('.select-search-employee').select2({
            minimumInputLength: 2,
            ajax: {
                url: $('.select-search-employee').data('remote-url'),
                dataType: 'json',
                delay: 500,
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
                            more: (params.page * 20) < data.total_count
                        }
                    };
                },
                cache: true
            },
            escapeMarkup: function (markup) {
                return markup;
            },
            templateResult: formatRepo,
            templateSelection: formatRepoSelection
        });
    });
    TABLE_PROJECT = 1;
    TABLE_PROJECT_META = 2;
    typeTeam = '{{Project::GROUP}}';
    typecCompany = '{{Project::COMPANY}}';
    textChooseTeam = '<?php echo trans('project::view.Choose team') ?>';
    textTeam = '<?php echo trans('project::view.Team') ?>';
    checkAll = '<?php echo trans('project::view.All') ?>';
    messageError = '<?php echo trans('project::view.Error while processing add') ?>';
    urlCheckExists = '{{ route('project::project.checkExists') }}';
    checkExistsSourceServer = '{{ route('project::project.checkExistsSourceServer') }}';
    checkEdit = '{{$checkEdit}}';
    projectId = '{{$projectId}}';
    urlAddCriticalDepenrencies = '{{route('project::project.add_critical_dependencies')}}';
    urlAddAssumptionConstrain = '{{route('project::project.add_assumption_constrain')}}';
    urlAddMemberCommunication = '{{route('project::project.add_member_communication')}}';
    urlAddCustomerCommunication = '{{route('project::project.add_customer_communication')}}';
    urlAddProjCommunication = '{{route('project::project.add_project_communication')}}';
    urlSyncTeamAllocation = '{{ route('project::project.sync_project_allocation') }}';
    urlSyncReportExample = '{{ route('project::project.sync_report_example') }}';
    urlAddRisk = '{{route('project::project.add_risk')}}';
    urlAddStageAndMilestone = '{{route('project::project.add_stage_and_milestone')}}';
    urlAddTraining = '{{route('project::project.add_training')}}';
    urlAddExternalInterface = '{{route('project::project.add_external_interface')}}';
    urlAddToolAndInfrastructure = '{{route('project::project.add_tool_and_infrastructure')}}';
    urlAddCommunication = '{{route('project::project.add_communication')}}';
    urlAddDeliverable = '{{route('project::project.add_deliverable')}}';
    urlAddAssumptions = '{{route('project::project.add_assumptions')}}';
    urlAddSkillRequest = '{{route('project::project.add_skill_request')}}';
    urlAddSecurity = '{{route('project::project.add_security')}}';
    urlGetALlPmOfProjectByAjax = '{{route('project::project.wo.update-pm')}}';
    urlUpdateTime = '{{route('project::project.updateTime')}}';
    urlAddDerivedExpenese = '{{route('project::project.add_devices_expenses')}}';
    textSale = '<?php echo trans('project::view.Sales') ?>';
    textChooseSale = '<?php echo trans('project::view.Choose sale') ?>';
    typeAssumption = '{{ Task::TYPE_WO_ASSUMPTIONS }}';
    typeConstraints = '{{ Task::TYPE_WO_CONSTRAINTS }}';
    typeMeetingCom = '{{ Task::TYPE_WO_MEETING_COMMUNICATION }}';
    typeReportCom = '{{ Task::TYPE_WO_REPORT_COMMUNICATION }}';
    typeOtherCom = '{{ Task::TYPE_WO_OTHER_COMMUNICATION }}';
    typeMemberCommunication = '{{ Task::TYPE_WO_MEMBER_COMMUNICATION }}';
    typeCustomerCommunication = '{{ Task::TYPE_WO_CUSTOMER_COMMUNICATION }}';
    @if($checkEdit)
        project_id =  {{$checkEdit ? $project->id : ''}};
    @endif
        TYPE_CRITICAL_DEPENDENCIES = "{{$allNameTab[Task::TYPE_WO_CRITICAL_DEPENDENCIES]}}";
    TYPE_ASSUMPTION_CONSTRAIN = "{{$allNameTab[Task::TYPE_WO_ASSUMPTION_CONSTRAINS]}}";
    TYPE_RISK = "{{$allNameTab[Task::TYPE_WO_RISK]}}";
    TYPE_STAGE_AND_MILESTONE = "{{$allNameTab[Task::TYPE_WO_STAGE_MILESTONE]}}";
    TYPE_TRAINING = "{{$allNameTab[Task::TYPE_WO_TRANING]}}";
    TYPE_COMMUNICATION_MEETING = "{{$allNameTab[Task::TYPE_WO_MEETING_COMMUNICATION]}}";
    TYPE_COMMUNICATION_REPORT = "{{$allNameTab[Task::TYPE_WO_REPORT_COMMUNICATION]}}";
    TYPE_COMMUNICATION_OTHER = "{{$allNameTab[Task::TYPE_WO_OTHER_COMMUNICATION]}}";
    TYPE_EXTERNAL_INTERFACE = "{{$allNameTab[Task::TYPE_WO_EXTERNAL_INTERFACE]}}";
    TYPE_COMMUNICATION = "{{$allNameTab[Task::TYPE_WO_COMMINUCATION]}}";
    TYPE_TOOL_AND_INFRASTRUCTURE = "{{$allNameTab[Task::TYPE_WO_TOOL_AND_INFRASTRUCTURE]}}";
    TYPE_DELIVERABLE = "{{$allNameTab[Task::TYPE_WO_DELIVERABLE]}}";
    TYPE_PERFORMANCE = "{{$allNameTab[Task::TYPE_WO_PERFORMANCE]}}";
    TYPE_QUALITY = "{{$allNameTab[Task::TYPE_WO_QUALITY]}}";
    TYPE_PROJECT_MEMBER = "{{$allNameTab[Task::TYPE_WO_PROJECT_MEMBER]}}";
    TYPE_QUALITY_PLAN= "{{$allNameTab[Task::TYPE_WO_QUALITY_PLAN]}}";
    TYPE_CM_PLAN = "{{$allNameTab[Task::TYPE_WO_CM_PLAN]}}";
    TYPE_CHANGE_WO = "{{$allNameTab[Task::TYPE_WO_CHANGE_WO]}}";
    TYPE_PROJECT_LOG = "{{$allNameTab[Task::TYPE_WO_PROJECT_LOG]}}";
    TYPE_WO_OVER_PLAN = "{{$allNameTab[Task::TYPE_WO_OVER_PLAN]}}";
    TYPE_WO_DEVICES_EXPENSE = "{{$allNameTab[Task::TYPE_WO_DEVICES_EXPENSE]}}";
    TYPE_ASSUMPTIONS = "{{$allNameTab[Task::TYPE_WO_ASSUMPTIONS]}}";
    TYPE_CONSTRAINTS = "{{$allNameTab[Task::TYPE_WO_CONSTRAINTS]}}";
    TYPE_SECURITY = "{{$allNameTab[Task::TYPE_WO_SECURITY]}}";
    TYPE_SKILL_REQUEST = "{{$allNameTab[Task::TYPE_WO_SKILL_REQUEST]}}";
    TYPE_MEMBER_COMMUNICATION = "{{$allNameTab[Task::TYPE_WO_MEMBER_COMMUNICATION]}}";
    TYPE_CUSTOMER_COMMUNICATION = "{{$allNameTab[Task::TYPE_WO_CUSTOMER_COMMUNICATION]}}";

    TYPE_SUBMITTED = 1;

    nameRequired = '{{trans('project::message.The name field is required')}}';
    nameMax = '{{trans('project::message.Please enter name a value between 1 and 255 characters long')}}';
    nameUnique = '{{trans('project::message.The value of name field must be unique')}}';
    teamRequired = '{{trans('project::message.The team field is required')}}';
    saleRequired = '{{trans('project::message.The sale field is required')}}';
    companyRequired = '{{trans('project::message.The company field is required')}}';
    customerRequired = '{{trans('project::message.The customer field is required')}}';
    saleRequiredTabBasic = '{{trans('project::message.Tab basic info: the sale field is required')}}';
    customerRequiredTabBasic = '{{trans('project::message.Tab basic info: the customer field is required')}}';
    companyRequiredTabBasic = '{{trans('project::message.Tab basic info: the company field is required')}}';
    approvedProdCostRequired = '{{ trans('project::message.The approved production cost field is required') }}';
    approvedProdCostRequiredTabBasic = '{{ trans('project::message.Tab basic info: the Approved production cost field is required') }}';
    kindRequiredTabBasic = '{{trans('project::message.The project kind field in tab Basic Info is required')}}';
    categoryRequiredTabBasic = '{{trans('project::message.The project category field in tab Basic Info is required')}}';
    classificationRequiredTabBasic = '{{trans('project::message.The classification field in tab Basic Info is required')}}';
    businessRequiredTabBasic = '{{trans('project::message.The business domain field in tab Basic Info is required')}}';
    subSectorRequiredTabBasic = '{{trans('project::message.The sub sector field in tab Basic Info is required')}}';
    cusEmailRequiredTabBasic = '{{trans('project::message.The email of customer field in tab Basic Info is required')}}';
    cusContactRequiredTabBasic = '{{trans('project::message.The contact of customer field in tab Basic Info is required')}}';
    projectMarketRequiredTabBasic = '{{trans('project::message.The project market field in tab Basic Info is required')}}';
    approveCostInvalidTabBasic = '{{trans('project::message.The Approve Production Cost is less than total Approve Production Cost Detail')}}';
    startAtRequired = '{{trans('project::message.The start at field is required')}}';
    endAtRequired = '{{trans('project::message.The end date field is required')}}';
    projectCodeRequired = '{{trans('project::message.The short project name field is required')}}';
    projectCodeUnique = '{{trans('project::message.The value of short project name field must be unique')}}';
    scheduleLinkRequired = '{{trans('project::message.The plan - schedule link field is required')}}';
    scheduleLinkUrl = '{{trans('project::message.Please enter plan - schedule link a valid URL')}}';
    scheduleLinkUnique = '{{trans('project::message.The value of plan - schedule link field must be unique')}}';
    baselineNumber = '{{trans('project::message.Please enter line of code baseline a valid number')}}';
    currentNumber = '{{trans('project::message.Please enter line of code current a valid number')}}';
    idRemineMax = '{{trans('project::message.Please enter id redmine a value between 1 and 100 characters long')}}';
    idRemineUnique = '{{trans('project::message.The value of id redmine field must be unique')}}';
    idGitMax = '{{trans('project::message.Please enter id git a value between 1 and 100 characters long')}}';
    idGitUnique = '{{trans('project::message.The value of id git field must be unique')}}';
    idSvnMax = '{{trans('project::message.Please enter id svn a value between 1 and 100 characters long')}}';
    idSvnUnique = '{{trans('project::message.The value of id svn field must be unique')}}';
    inputGroupLeader = '{{trans('project::message.Please choose team to show group leader')}}';
    inputProgramingLanguage = '{{trans('The programming language field is required')}}';

    idRemineRequired = '{{trans('project::message.The value of id redmine field is required')}}';
    idGitRequired = '{{trans('project::message.The value of id git field is required')}}';
    idSvnRequired = '{{trans('project::message.The value of id svn field is required')}}';
    startDateBefore = '{{trans('project::message.The start at must be before end at')}}';

    billableEffortRequired = '{{trans('project::message.The billable effort field is required')}}';
    billableEffortSelectRequired = '{{trans('project::message.You have to Select one item MD or MM')}}';
    billableEffortNumber = '{{trans('project::message.The billable effort must be numberic')}}';
    planEffortRequired = '{{trans('project::message.The plan effort field is required')}}';
    planEffortNumber = '{{trans('project::message.The plan effort must be numberic')}}';
    costApprovedProductionRequired = '{{trans('project::message.The cost_approved_production field is required')}}';
    costApprovedProductionNumber = '{{trans('project::message.The cost_approved_production must be numberic')}}';
    valueGreaterThanZero = '{{trans('project::message.The value must be greater than zero')}}';
    kindIdRequired = '{{trans('project::message.The project kind field is required')}}';
    categoryRequired = '{{trans('project::message.The category field is required')}}';
    classificationRequired = '{{trans('project::message.The classification field is required')}}';
    businessRequired = '{{trans('project::message.The business domain field is required')}}';
    subSectorRequired = '{{trans('project::message.The sub sector field is required')}}';
    cusEmailRequired = '{{trans('project::message.The email of customer field is required')}}';
    cusContactRequired = '{{trans('project::message.The contact of customer field is required')}}';
    projectMarketRequired = '{{trans('project::message.The project market field is required')}}';
    emailFormatRequired = '{{trans('project::message.Incorrect email format')}}';

    workorderApproved = {!! json_encode(config('project.workorder_approved')) !!};
    urlUpdateNote = '{{route('project::project.update_note')}}';
    urlAddPerformance = '{{route('project::project.add_performance')}}';
    urlAddQuality = '{{route('project::project.add_quality')}}';
    urlAddQualityPlan = '{{route('project::project.add_quality_plan')}}';
    urlAddCMPlan = '{{route('project::project.add_cm_plan')}}';
    urlAddProjectMember = '{{route('project::project.add_project_member')}}';
    urlUpdateReason = '{{route('project::project.update_reason')}}';
    urlGenerateSelectLeader = '{{route('project::project.generate_select_leader')}}';
    urlGetContentTable = '{{route('project::project.get_content_table')}}';
    urlCheckStage = '{{route('project::project.check_has_stage')}}';
    urlCheckIsChangeStatusWo = '{{route('project::project.check_is_change_status_wo')}}';
    urlSubmitWorkorder = '{{ route('project::project.submit_workorder', ['id' => $project->id])}}';
    urlGenCusByCompany = '{{ route('project::project.gen_cus_and_sale_by_company') }}';
    tabActiveWO = '{{$tabActiveWO}}';
    tabActive = '{{$tabActive}}';
    teamTypePqa = JSON.parse('{!! json_encode(Team::getTeamTypePqa(), true) !!}');
    urlEditBasicInfo = '{{route('project::project.edit_basic_info')}}';
            @if (isset($project) && $project->id)
    var globalPassModule = {
            urlSyncSource: '{{ URL::route('project::sync.source.server', ['id' => $project->id]) }}',
            teamProject: '{{ isset($teamsProject) && $teamsProject ? $teamsProject : '' }}',
            project: {
                id: {{ $project->id }},
                resource_type: {{ isset($projectDraft) && $projectDraft ? $projectDraft->type_mm : $project->type_mm }}
            },
            status: {!! json_encode(ProjConst::woStatus()) !!},
            editWOAvai: {!!(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder) ? '1' : '0'!!}
        };
    var globalTrans = {!!json_encode(trans('project::view'))!!};
            @endif
            @if ($taskWOApproved)
            @if(isset($taskWOApproved->status))
    var statusWo = {{$taskWOApproved->status}};
            @else
    var statusWo = null;
            @endif
            @else
            @if ($checkHasTaskWorkorderApproved)
    var statusWo = {{Task::STATUS_APPROVED}};
            @else
    var statusWo = null;
            @endif
            @endif
    var RKVarPassGlobal = {
            textSave: '{{ trans('project::view.Save') }}',
            textClose: '{{ trans('project::view.Close') }}',
            multiSelectTextNone: '{{ trans('project::view.Choose items') }}',
            multiSelectTextAll: '{{ trans('project::view.All') }}',
            multiSelectTextSelected: '{{ trans('project::view.items selected') }}',
            multiSelectTextSelectedShort: '{{ trans('project::view.items') }}',
            memberTypeDev: {{ ProjectMember::TYPE_DEV }},
            memberTypeLeader: {{ ProjectMember::TYPE_TEAM_LEADER }},
            memberTypePm: {{ ProjectMember::TYPE_PM }},
            memberTypeSubPm: {{ProjectMember::TYPE_SUBPM}},
            teamPath: JSON.parse('{!! json_encode($teamPath) !!}'),
            teamSelected: JSON.parse('{!! json_encode($allTeamDraft) !!}'),
            <?php if(isset($projectProgramLangs) && $projectProgramLangs): ?>projLangs: {!! json_encode($projectProgramLangs) !!},<?php endif ?>
        }
    var urlEditRisk = '{{ route("project::wo.editRisk") }}';
    var urlEditNc = '{{ route("project::wo.editNc") }}';
    var urlDeleteIssue = '{{ route("project::issue.delete") }}';
    var urlEditIssue = '{{ route("project::wo.editIssue") }}';
    var urlGetFormNC = '{{ route("project::wo.getFormNC") }}';
    var modalRiskTitle = '{{ trans("project::view.Risk info") }}';
    var modalNcTitle = '{{ trans('project::view.General information') }}';
    var modalIssueTitle = '{{ trans("project::view.Issue info") }}';
    var modalNCTitle = 'NC info';
    var urlGetFormOpportunity = '{{ route("project::wo.getFormOpportunity") }}';
    var urlGetFormViewOpp = '{{ route("project::wo.getFormViewOpp") }}';
    var modalOpportunityTitle = 'Opportunity info';
    var requiredText = '{{trans("project::view.This field is required.")}}';
    var approvedText = '{{ trans("project::view.Approved Value") }}';
    var MD_TYPE = {{ Project::MD_TYPE }};
    var urlSearchEmployee = '{{ URL::route('project::list.search.member.ajax') }}';
    var urlPopoverWoOther = '{{ URL::route('project::project.wo.other.popover')}}';
    var criticalType = '{{CriticalDependencie::getTableName()}}';
    var securityType = '{{Security::getTableName()}}';
    var skillRequiredType = '{{SkillRequest::getTableName()}}';
    var memberCommunicationType = '{{MemberCommunication::getTableName()}}';
    var customerCommunicationType = '{{CustomerCommunication::getTableName()}}';
    var projCommunicationType = '{{CommunicationProject::getTableName()}}';
    var assumptionType = '{{AssumptionConstrain::getTableName()}}';
    var token = '{{ csrf_token() }}';
    var typeIssueCSS = {{ Task::TYPE_ISSUE_CSS }};
    var urlTaskRisk = '{{ route("project::task.task_risk.ajax") }}';
    var urlGetCustomers = "{!! route('sales::search.ajax.searchCustomerAjax') !!}";
    var typesSkill = {language:{label:"program language",ph:"pl"},frame:{label:"framework / ide",ph:"framework / ide"},database:{label:"db",ph:"db"},os:{label:"os",ph:"os"},english:{label:"English",ph:"english"}};
    var urlDeleteNC = '{{ route("project::nc.delete") }}';
</script>
<script type="text/javascript" src="{{ asset('lib/js/bootstrap-dialog.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="{{ asset('lib/js/jquery.flexText.min.js') }}"></script>
<script src="{{ URL::asset('lib/js/moment.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
<script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/fixedcolumns/3.2.3/js/dataTables.fixedColumns.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/vis/4.21.0/vis.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
<script type="text/javascript" src="{{ URL::asset('lib/js/jquery.cookie.min.js') }}"></script>
<script type="text/javascript" src="{{ CoreUrl::asset('project/js/wo-allocation.js') }}"></script>
<script src="{{ CoreUrl::asset('project/js/edit.js') }}"></script>
<script src="{{ CoreUrl::asset('project/js/software.js') }}"></script>
<script type="text/javascript" src="{{ CoreUrl::asset('project/js/script.js') }}"></script>
<script type="text/javascript" src="{{ asset('lib/table-sorter/js/table-sorter.js') }}"></script>

<!-- Fullcalendar script -->
<script type="text/javascript" src="{{ asset('assets/fullcalendar/core/main.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/fullcalendar/interaction/main.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/fullcalendar/daygrid/main.min.js') }}"></script>
<!-- /.Fullcalendar script-->

<script type="text/javascript">
    @if (isset($project) && $project->id)
    var projectId = {{$project->id}};
    var managerId = {{$projectDraft->manager_id}};
    @endif
    jQuery(document).ready(function($) {
        RKfuncion.select2.init();
        $('input.date-picker').datetimepicker({
            format: 'YYYY-MM-DD'
        });
    });
    $(document).on('click', '#table-risk .btn-add-task', function() {
        var ajaxUrl = $(this).data('url-ajax');
        $.ajax({
            url: ajaxUrl,
            type: 'post',
            dataType: 'json',
            timeout: 30000,
            data: {_token: token},
            success: function (result) {
                $('#modal-task-risk .modal-body').html(result['htmlModal']);
                $('#modal-task-risk').modal('show');
            },
            error: function (x, t, m) {
                if (t == "timeout") {
                    alert("got timeout");
                } else {
                    alert('ajax fail to fetch data');
                }
            },
            complete: function () {

            },
        });

    });

    function displayTaskRisk(riskId, self, index) {
        self = $(self);
        if (self.data('direction') === 'open') {
            $.ajax({
                url: urlTaskRisk,
                type: 'post',
                dataType: 'html',
                data: {
                    _token: token,
                    riskId: riskId,
                    index: index,
                },
                success: function (data) {
                    self.closest('tr').after(data);
                    self.data('direction', 'close');
                    self.find('span.glyphicon').removeClass('glyphicon-menu-down').addClass('glyphicon-menu-up');
                },
                error: function() {

                },
                complete: function () {

                }
            });
        } else {
            $('tr[data-risk-id='+self.data('id')+']').remove();
            self.data('direction', 'open');
            self.find('span.glyphicon').removeClass('glyphicon-menu-up').addClass('glyphicon-menu-down');
        }

    }

    jQuery(document).ready(function ($) {
        $('.clone-project').on('click', function () {
            $('#modal-clone-project').modal('show');
        })

        $('.project-name').on('input', function () {
            var name = $(this).val(),
                btnClone = $('.submit-clone');

            btnClone.prop('disabled', true)
            if (name !== '' && name !== null) {
                btnClone.prop('disabled', false)
            }
        })

        $('#clone-project').submit(function () {
            $('.submit-clone').prop('disabled', true)
            $('.submit-clone .fa-refresh').removeClass('hidden')
        });
    });

    $(document).on('click', '.add-nc', function () {
        var $curElem = $(this);
        var projectId = $curElem.data('project-id');
        $('.add-nc').prop('disabled', true);
        $curElem.find('i.fa-plus').removeClass('fa-plus').addClass('fa-refresh').addClass('fa-spin');
        $.ajax({
            url: urlGetFormNC,
            type: 'get',
            data: {
                projectId: projectId,
                isTabWO: 1,
            },
            dataType: 'text',
            success: function (data) {
                BootstrapDialog.show({
                    title: modalNCTitle,
                    cssClass: 'task-dialog',
                    message: $('<div></div>').html(data),
                    closable: false,
                    buttons: [{
                        id: 'btn-nc-close',
                        icon: 'fa fa-close',
                        label: 'Close',
                        cssClass: 'btn-primary',
                        autospin: false,
                        action: function(dialogRef){
                            dialogRef.close();
                        }
                    },{
                        id: 'btn-nc-save',
                        icon: 'glyphicon glyphicon-check',
                        label: 'Save',
                        cssClass: 'btn-primary',
                        autospin: false,
                        action: function(dialogRef){
                            $('.form-nc-detail').submit();
                        }
                    }]
                });
            },
            error: function () {
                alert('ajax fail to fetch data');
            },
            complete: function () {
                $curElem.find('i.fa-refresh').addClass('fa-plus').removeClass('fa-refresh').removeClass('fa-spin');
                $('.add-nc').prop('disabled', false);
            }
        });
    });

    $(document).on('click', '.btn-delete-nc', function() {
        var issueId = $(this).attr('data-id');
        $('#modal-delete-confirm-nc').modal('show');
        $('#modal-delete-confirm-nc').find(".btn-submit").attr('data-id', issueId);
    });
    $(document).on('click', '#modal-delete-confirm-nc .btn-submit', function () {
        $('#modal-delete-confirm-nc').modal('hide');
        var issueId = $(this).attr('data-id');
        $.ajax({
            url: urlDeleteNC,
            type: 'GET',
            data: {
                issueId: issueId
            },
            success: function (data) {
                console.log(data);
                $("tr[data-id='" + issueId + "']").remove();
            },
            error: function () {
                alert('ajax fail to fetch data');
            },
        });
    });

    //Opportunity
    $(document).on('click', '.add-opportunity', function () {
        var $curElem = $(this);
        var projectId = $curElem.data('project-id');
        $('.add-opportunity').prop('disabled', true);
        $curElem.find('i.fa-plus').removeClass('fa-plus').addClass('fa-refresh').addClass('fa-spin');
        $.ajax({
            url: urlGetFormOpportunity,
            type: 'get',
            data: {
                projectId: projectId,
                isTabWO: 1,
            },
            dataType: 'text',
            success: function (data) {
                BootstrapDialog.show({
                    title: modalOpportunityTitle,
                    cssClass: 'task-dialog',
                    message: $('<div></div>').html(data),
                    closable: false,
                    buttons: [{
                        id: 'btn-opportunity-close',
                        icon: 'fa fa-close',
                        label: 'Close',
                        cssClass: 'btn-primary',
                        autospin: false,
                        action: function(dialogRef){
                            dialogRef.close();
                        }
                    },{
                        id: 'btn-opportunity-save',
                        icon: 'glyphicon glyphicon-check',
                        label: 'Save',
                        cssClass: 'btn-primary',
                        autospin: false,
                        action: function(dialogRef){
                            $('.form-opportunity-detail').submit();
                        }
                    }]
                });
            },
            error: function () {
                alert('ajax fail to fetch data');
            },
            complete: function () {
                $curElem.find('i.fa-refresh').addClass('fa-plus').removeClass('fa-refresh').removeClass('fa-spin');
                $('.add-opportunity').prop('disabled', false);
            }
        });
    });

    $(document).on('click', '.btn-delete-opportunity', function() {
        var issueId = $(this).attr('data-id');
        $('#modal-delete-confirm-opportunity').modal('show');
        $('#modal-delete-confirm-opportunity').find(".btn-submit").attr('data-id', issueId);
    });
    $(document).on('click', '#modal-delete-confirm-opportunity .btn-submit', function () {
        $('#modal-delete-confirm-opportunity').modal('hide');
        var issueId = $(this).attr('data-id');
        $.ajax({
            url: urlDeleteNC,
            type: 'GET',
            data: {
                issueId: issueId
            },
            success: function (data) {
                console.log(data);
                $("tr[data-id='" + issueId + "']").remove();
            },
            error: function () {
                alert('ajax fail to fetch data');
            },
        });
    });

    $(document).on('click', '.edit-opportunity', function () {
        var $curElem = $(this);
        var projectId = $(this).data('project-id');
        var oopId = $(this).data('id');
        $('.edit-opportunity').prop('disabled', true);
        $(this).find('i.fa-plus').removeClass('fa-plus').addClass('fa-refresh').addClass('fa-spin');
        $.ajax({
            url: urlGetFormViewOpp,
            type: 'get',
            data: {
                projectId: projectId,
                oopId: oopId,
                isTabWO: 1,
            },
            dataType: 'text',
            success: function (data) {
                BootstrapDialog.show({
                    title: modalOpportunityTitle,
                    cssClass: 'task-dialog',
                    message: $('<div></div>').html(data),
                    closable: false,
                    buttons: [{
                        id: 'btn-opportunity-close',
                        icon: 'fa fa-close',
                        label: 'Close',
                        cssClass: 'btn-primary',
                        autospin: false,
                        action: function(dialogRef){
                            dialogRef.close();
                        }
                    }]
                });
            },
            error: function () {
                alert('ajax fail to fetch data');
            },
            complete: function () {
                $curElem.find('i.fa-refresh').addClass('fa-plus').removeClass('fa-refresh').removeClass('fa-spin');
                $('.edit-opportunity').prop('disabled', false);
            }
        });
    });
</script>
