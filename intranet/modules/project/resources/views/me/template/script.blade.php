<div id="_me_alert"></div>

<!-- modal delete cofirm -->
<div class="modal fade modal-default" id="_modal_confirm" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ trans('core::view.Confirm') }}</h4>
            </div>
            <div class="modal-body">
                <p class="text-default">{{ trans('core::view.Are you sure delete item(s)?') }}</p>
                <p class="text-change"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-close pull-left btn-default" data-dismiss="modal">{{ trans('core::view.Close') }}</button>
                <button type="button" class="btn btn-outline btn-ok btn-primary">{{ trans('core::view.OK') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div> <!-- modal delete cofirm -->

<div id="_me_tooltip"></div>
<div id="_me_comments"></div>

<?php
use Rikkei\Project\Model\MeAttribute;
use Rikkei\Project\Model\MeEvaluation;
use Rikkei\Project\View\MeView;
use Carbon\Carbon;
//get options points
$optionsPoint = MeAttribute::optionPoints(true);

$request = request();
$curr_project_id = null;
$curr_team_id = null;
$curr_month = null;
if ($request->has('project_id')) {
    $curr_project_id = $request->get('project_id');
}
if ($request->has('team_id')) {
    $curr_team_id = $request->get('team_id');
}
if ($request->has('month')) {
    $curr_month = $request->get('month');
}

$error_text = null;
if (Session::has('messages')) {
    $messages = Session::get('messages');
    if (isset($messages['errors']) && ($errors = $messages['errors'])) {
        $error_text = '<ul>';
        foreach ($errors as $err) {
            $error_text .= '<li>'. $err .'</li>';
        }
        $error_text .= '</ul>';
    }
}

//time now
$currentTime = MeView::defaultFilterMonth();
?>

<script type="text/javascript">
    jQuery(document).ready(function ($) {
        RKfuncion.select2.init();
    });
    var me_tooltip = $('#_me_tooltip');
    var cm_tooltip = $('#_me_comments');
    var curr_project_id = '{{$curr_project_id}}';
    var curr_team_id = '{{$curr_team_id}}';
    var curr_month = '{{$curr_month}}';
    var time_now = '{{ $currentTime->startOfMonth()->toDateTimeString() }}';
    var _addAttrPointUrl = "{{route('project::project.eval.add_attr_point')}}";
    var _updateAvgPointUrl = "{{ route('project::project.eval.update_avg_point') }}";
    var _addCommentUrl = "{{route('project::project.eval.add_comment')}}";
    var _loadAttrCommentsUrl = "{{route('project::project.eval.load_attr_comments')}}";
    var _loadMonthsProjectUrl = "{{route('project::project.eval.load_project_months')}}";
    var text_selection = '<?php echo trans('project::me.Selection') ?>';
    var text_error_max_value = '<?php echo trans('project::me.The average value must be smaller than 130%') ?>';
    var text_confirm_submit = '<?php echo trans('project::me.Confirm submit') ?>';
    var text_error_occurred = '<?php echo trans('project::me.An error occurred') ?>';
    var text_require_comment = '<?php echo trans('project::me.You must enter a comment') ?>';
    var text_error_assignee = '<?php echo trans('project::me.You must select assignee') ?>';
    var text_select_project = '<?php echo trans('project::me.Select project') ?>';
    var text_select_month = '<?php echo trans('project::me.Select month') ?>';
    var text_loading = '<?php echo trans('project::me.Loading') ?>...';
    var textRequiredComment = '<?php echo trans('project::me.You must comment for this value') ?>';
    var _token = '{{csrf_token()}}';
    var _api_key = "{{config('services.google.api_key')}}";
    var current_user_google_id = "{{auth()->user()->google_id}}";
    var _error_text = '<?php echo $error_text; ?>';
    var NoneItemChecked = '{{ trans('project::me.None item checked') }}';
    var optionsPoint = '<?php echo json_encode($optionsPoint) ?>';
    optionsPoint = JSON.parse(optionsPoint);
    var P_EXCELLENT = parseInt('{{ MeAttribute::EXCELLENT }}');
    var P_GOOD = parseInt('{{ MeAttribute::GOOD }}');
    var P_FAIR = parseInt('{{ MeAttribute::FAIR }}');
    var P_SATIS = parseInt('{{ MeAttribute::SATIS }}');
    var P_UNSATIS = parseInt('{{ MeAttribute::UNSATIS }}');
    var P_NA = parseInt('{{ MeAttribute::NA }}');
    var PI_MAX = parseFloat('{{ max([MeEvaluation::FT_BASE, MeEvaluation::FT_OSDC]) }}');
    var MAX_PP = parseInt('{{ MeEvaluation::MAX_PP }}');
    var MAX_POINT = parseInt('{{ MeEvaluation::MAX_POINT }}');
    
    var IS_TEAM = false;
    var newMeUrl = '';
    var SEP_MONTH = '{{ config("project.me_sep_month") }}';
    
</script>

<script src="/lib/js/jquery.number.min.js"></script>

