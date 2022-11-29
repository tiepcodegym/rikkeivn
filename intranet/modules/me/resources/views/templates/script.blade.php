<?php
use Rikkei\Project\Model\MeAttribute;
use Rikkei\Me\Model\ME;
use Rikkei\Me\View\View as MeView;
use Rikkei\Me\Model\Comment as MeComment;
use Carbon\Carbon;

//time now
$currentTime = MeView::defaultFilterMonth();
$currUser = auth()->user();
$request = request();
$currMonth = $request->get('month');
if (!$currMonth) {
    $currMonth = $request->get('time');
}
if (!$currMonth) {
    $currMonth = $currentTime->startOfMonth()->format('Y-m-d');
}
?>

<script type="text/javascript">
    //prevent scroll blocking select2
    $('body').on('select2:open', '.filter-field', function (e) {
        const evt = "scroll.select2";
        $(e.target).parents().off(evt);
        $(window).off(evt);
    });

    // date tooltip z-index
    $('body').on('mouseenter', '.date-tooltip [data-toggle="tooltip"]', function () {
        $(this).closest('td').addClass('z-index-3000');
    });
    $('body').on('mouseleave', '.date-tooltip [data-toggle="tooltip"]', function () {
        $(this).closest('td').removeClass('z-index-3000');
    });

    <?php
    $aryTextTrans = array_only(trans('me::view'), [
        'Select project',
        'Select project',
        'Select month',
        'No result',
        'Help',
        'Right click to comment',
        'Average',
        'Account',
        'Project name',
        'Project point',
        'Project Type Factor',
        'Summary',
        'Effort in Project',
        'days',
        'Contribution level',
        'Note',
        'Status',
        'Submit',
        'Comment',
        'Load more',
        'Are you sure want to remove comment?',
        'Are you sure want to remove item?',
        'None item checked',
        'Comment is required',
        'You must comment for this value',
        'You must comment before feedback',
        'You must comment employee',
        'Cancel',
        'Select project team',
        'Select team member',
        'Select employee',
        'Select project',
        'Level',
        'Approve',
        'Feedback',
        'Total',
        'Excellent',
        'Good',
        'Fair',
        'Satisfactory',
        'Unsatisfactory',
        'Confirm submit',
        'Not Evaluate',
        'persons evaluated',
        'Project Manager',
        'Group',
        'Project code',
        'Project',
        'Member',
        'Please select project and month',
        'Please select team and month',
        'Select team',
        'Select project team',
        'View old item before',
        'click here',
        'Avg project joined',
        'Saving data',
        'Saved data',
        'Work day in this project',
        'Project index detail',
        'You must comment before submiting',
        'Bulk Comment',
    ]);
    ?>
    var textTrans = JSON.parse('{!! json_encode($aryTextTrans, JSON_UNESCAPED_UNICODE) !!}');
    textTrans = $.extend({}, textTrans, {
        'Total': "{!! trans('core::view.Total') !!}",
        'page': "{!! trans('core::view.page') !!}",
        'Show': "{!! trans('core::view.Show') !!}",
        'entity': "{!! trans('core::view.entity') !!}",
        'Reset filter': "{!! trans('core::view.Reset filter') !!}",
        'confirm_feedback': "{!! trans('me::view.Are you sure you want to do this action', ['action' => trans('me::view.Feedback')]) !!}",
        'confirm_approve': "{!! trans('me::view.Are you sure you want to do this action', ['action' => trans('me::view.Approve')]) !!}",
        'Please checking network connection!': "{!! trans('core::view.Please checking network connection!') !!}",
    });

    var pageParams = {
        _token: '{{ csrf_token() }}',
        SEP_MONTH: '{{ config("project.me_sep_month") }}',
        MAX_ATTR_POINT: 10,
        MIN_ATTR_POINT: 0,
        GR_NEW_NORMAL: '{{ MeAttribute::GR_NEW_NORMAL }}',
        GR_NEW_PERFORM: '{{ MeAttribute::GR_NEW_PERFORM }}',
        CM_TYPE_COMMENT: '{{ MeComment::TYPE_COMMENT }}',
        CM_TYPE_NOTE: '{{ MeComment::TYPE_NOTE }}',
        CM_TYPE_LATE_TIME: '{{ MeComment::TYPE_LATE_TIME }}',
        STT_FEEDBACK: '{{ ME::STT_FEEDBACK }}',
        STT_SUBMITED: '{{ ME::STT_SUBMITED }}',
        STT_APPROVED: '{{ ME::STT_APPROVED }}',
        STT_CLOSED: '{{ ME::STT_CLOSED }}',
        LEADER_UPDATED: '{{ ME::LEADER_UPDATED }}',
        COO_UPDATED: '{{ ME::COO_UPDATED }}',
        ATTR_TYPE_WORK_PERFORM: '{{ MeAttribute::TYPE_WORK_PERFORM }}',
        currUser: {
            id: '{{ $currUser->employee_id }}',
            name: '{{ $currUser->name }}',
            email: '{{ $currUser->email }}'
        },
        meTbl: 'me_evaluations',
        currProjId: '{{ $request->get("project_id") }}',
        currMonth: '{{ $currMonth }}',
        currTeamId: '{{ $request->get("team_id") }}',
        listContriLabels: JSON.parse('{!! json_encode(MeView::getInstance()->listContributeLabels()) !!}'),
        listOldContriLabels: JSON.parse('{!! json_encode(MeView::getInstance()->listOldContributeLabels()) !!}'),
        sttsShowSubmit: JSON.parse('{!! json_encode([ME::STT_DRAFT, ME::STT_FEEDBACK]) !!}'),
        listStatuses: JSON.parse('{!! json_encode(ME::arrayStatus()) !!}'),
        listFilterStatuses: JSON.parse('{!! json_encode(ME::filterStatus()) !!}'),
        listProjTypes: JSON.parse('{!! json_encode(\Rikkei\Project\Model\Project::labelTypeProject()) !!}'),
        urlOldMe: "{{ route('project::project.eval.index') }}",
        urlLoadProjsOfPm: "{{ route('me::proj.get_pm_projects') }}",
        urlLoadMonthsOfProj: "{{ route('me::proj.get_months_project') }}",
        urlLoadMembersOfProj: "{{ route('me::proj.get_members_project') }}",
        urlProjPoint: "{{ route('project::point.edit', ['id' => null]) }}",
        urlSavePoint: "{{ route('me::save_point') }}",
        urlSubmitMe: "{{ route('me::proj.submit') }}",
        urlLoadComment: "{{ route('me::comment.get_attr_comments') }}",
        urlAddComment: "{{ route('me::comment.add') }}",
        urlDelComment: "{{ route('me::comment.delete', ['id' => null]) }}",
        urlGetReviewData: "{{ route('me::review.data') }}",
        urlUpdatestatus: "{{ route('me::review.update_status') }}",
        urlMultiUpdatestatus: "{{ route('me::review.multi_update_status') }}",
        urlDeleteItem: "{{ route('me::admin.delete_item') }}",
        urlSearchEmployee: "{{ route('team::employee.list.search.ajax') }}",
        urlSearchProject: "{{ route('project::me.search.project.team.ajax') }}",
        urlGetProjNotEval: "{{ route('me::review.proj_not_eval') }}",
        urlGetConfirmData: "{{ route('me::profile.confirm.data') }}",
        urlGetViewMemberData: "{{ route('me::view.member.data') }}",
        urlHelpPage: "{{ route('project::project.eval.help') }}",
        urlAddListComment: "{{ route('me::comment.add-list-comment') }}",
    };
</script>
<script src="/lib/js/jquery.number.min.js"></script>
