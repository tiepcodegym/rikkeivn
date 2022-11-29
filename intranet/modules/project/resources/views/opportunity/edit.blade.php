<?php
use Rikkei\Core\View\CoreUrl;
use Rikkei\Project\Model\Task;
use Rikkei\Project\Model\Opportunity;
use Rikkei\Project\View\ProjConst;
use Rikkei\Project\Model\ProjectMember;

$pageTitle = trans('project::view.Create Opportunity');
if ($project) {
    $pageTitle = trans('project::view.Update Opportunity');
}
$approvedStatus = Opportunity::STATUS_APPROVED;
?>

@extends('layouts.default')

@section('title', $pageTitle)

@section('css')
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/vis/4.21.0/vis-timeline-graph2d.min.css">
<link rel="stylesheet" type="text/css" href="{{ CoreUrl::asset('project/css/edit.css') }}">
<link rel="stylesheet" type="text/css" href="{{ CoreUrl::asset('project/css/opportunity.css') }}">
@endsection

@section('content')

    
@if (!$project)
<div class="box box-primary">
    <div class="box-header with-border">{{ trans('project::view.Basic info') }}</div>
@endif

    <form class="form-horizontal frm-create-project" id="create-project-form" method="post"
          action="{{ route('project::oppor.store') }}"  novalidate="novalidate">
        {!! csrf_field() !!}

        @if ($project)
        <div class="nav-tabs-custom tab-primary" style="margin-bottom: 0;">
            <ul class="nav nav-tabs" role="tablist" id="opportunity_tabs">
                <li class="">
                    <a href="#basic_info" data-toggle="tab">{{ trans('project::view.Basic info') }}</a>
                </li>
                <li class="">
                    <a href="#scope" data-toggle="tab">{{ trans('project::view.Scope & Object') }}</a>
                </li>
                <li class="load-ajax">
                    <a href="#team_allocation" data-toggle="tab">{{ trans('project::view.Team Allocation') }}</a>
                </li>
                <li class="load-ajax">
                    <a href="#risk" data-toggle="tab">{{ trans('project::view.Risk') }}</a>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane" id="basic_info">
                    @include('project::opportunity.includes.tab-basic')
                </div>
                <div class="tab-pane" id="scope">
                    @include('project::opportunity.includes.scope-object')
                </div>
                <div class="tab-pane" id="team_allocation">
                    <div class="inner-content"></div>
                    @include('project::opportunity.includes.team-allocation')
                </div>
                <div class="tab-pane" id="risk">
                    <div class="inner-content"></div>
                </div>
            </div>
        </div>
        @else
        <div class="box-body">
            @include('project::opportunity.includes.tab-basic')
        </div>
        @endif

        <div class="box-footer">
            <p class="text-center">
                <a class="btn btn-warning" href="{{ route('project::oppor.index') }}"><i class="fa fa-long-arrow-left"></i> {{ trans('project::view.Back to list') }}</a>
                @if ($project)
                <input type="hidden" name="id" value="{{ $project->id }}">
                <input type="hidden" name="tab" id="current_tab" value="">
                <button class="btn btn-primary btn-create" type="submit">
                    {{ trans('project::view.Update Opportunity') }}
                </button>
                @else
                <button class="btn btn-primary btn-create" type="submit">
                    {{ trans('project::view.Create Opportunity') }}
                </button>
                @endif
            </p>
        </div>

    </form>

@if (!$project)
</div>
@endif

@include('project::opportunity.includes.modal-edit-member')

@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="{{ URL::asset('lib/js/moment.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/vis/4.21.0/vis.min.js"></script>
<script type="text/javascript" src="{{ asset('lib/js/bootstrap-dialog.min.js') }}"></script>
<script type="text/javascript">

    var textFieldRequired = '<?php echo trans("project::message.This field is required") ?>';
    var textFieldUnique = '<?php echo trans("project::message.This field has already been taken") ?>';
    var textFieldMax255 = '<?php echo trans("project::message.This field may not be greater than :max characters", ["max" => 255]) ?>';
    var textFieldNumber = '<?php echo trans("project::message.This field must be a number") ?>';
    var textChooseTeam = '<?php echo trans('project::view.Choose team') ?>';
    var checkAll = '<?php echo trans('project::view.All') ?>';
    var textTeam = '<?php echo trans('project::view.Team') ?>';
    var urlCheckExists = '{{ route("project::oppor.check_exists") }}';
    var urlGetContentTable = '{{ route("project::oppor.get_tab_content") }}';
    var valueGreaterThanZero = '{{ trans("project::message.The value must be greater than zero") }}';
    var startDateBefore = '{{ trans("project::message.The start at must be before end at") }}';
    var TABLE_PROJECT = 1;
    var MD_TYPE = {{ Opportunity::MD_TYPE }};
    var projectId = null;
    @if ($project)
        projectId = {{ $project->id }};
    @endif

    var RKVarPassGlobal = {
        multiSelectTextNone: '{{ trans("project::view.Choose items") }}',
        multiSelectTextAll: '{{ trans("project::view.All") }}',
        multiSelectTextSelected: '{{ trans("project::view.items selected") }}',
        multiSelectTextSelectedShort: '{{ trans("project::view.items") }}',
        teamPath: JSON.parse('{!! json_encode($teamPath) !!}'),
        teamSelected: JSON.parse('{!! json_encode($allTeamDraft) !!}'),
        memberTypeDev: {{ ProjectMember::TYPE_DEV }},
        memberTypeLeader: {{ ProjectMember::TYPE_TEAM_LEADER }},
        memberTypePm: {{ ProjectMember::TYPE_PM }},
        memberTypeSubPm: {{ProjectMember::TYPE_SUBPM}},
    }
    @if ($projectPrograms)
        RKVarPassGlobal.projLangs = {!! json_encode($projectPrograms) !!};
    @endif
    var globalPassModule = {
        project: {
            id: '{{ $project ? $project->id : null }}',
            resource_type: {{ $project ? $project->type_mm : 0 }},
        },
        status: {!! json_encode(ProjConst::woStatus()) !!},
        editWOAvai: 1,
    };
    var globalTrans = {!! json_encode(trans('project::view')) !!};
    var IS_OPPORTUNITY = 1;
    var urlEditRisk = '{{ route("project::wo.editRisk") }}';
    var urlAddRisk = '{{ route('project::project.add_risk') }}';
    var modalRiskTitle = '{{ trans("project::view.Risk info") }}';
    var requiredText = '{{trans("project::view.This field is required.")}}';
    var approvedText = '{{ trans("project::view.Approved Value") }}';
    var messageError = '<?php echo trans('project::view.Error while processing add') ?>';

    jQuery(document).ready(function($) {
        RKfuncion.select2.init();
        $('input.date-picker').datetimepicker({
            format: 'YYYY-MM-DD'
        });
    });

</script>
<script src="{{ CoreUrl::asset('/project/js/wo-allocation.js') }}"></script>
<script src="{{ CoreUrl::asset('/project/js/opportunity.js') }}"></script>
@endsection
