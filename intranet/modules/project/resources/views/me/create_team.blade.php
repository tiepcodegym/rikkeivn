@extends('layouts.default')

@section('title', trans('project::me.Monthly Evaluation'))

@section('css')
<?php
use Rikkei\Core\View\CoreUrl;
?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="{{ CoreUrl::asset('project/css/edit.css') }}" />
<link rel="stylesheet" href="{{ CoreUrl::asset('project/css/me_style.css') }}" />
<link rel="stylesheet" href="{{ CoreUrl::asset('project/css/me_team_style.css') }}" />
@endsection

@section('body_class', 'me_team_create')

@section('content')

<div class="box box-info _me_create_page">
    <div class="box-body">
        <div class="row">
            <div class="col-md-10">
                <form id="eval_form_filter" action="{{route('project::team.eval.load_members')}}" data-change="1" class="no-validate">
                    <div class="form-inline box-action select-media mgr-35">
                        <select class="form-control select-search has-search" id="_me_team">
                            <option value="">{{trans('project::me.Select team')}}</option>
                            @if ($fullTeam)
                                @if ($teamList)
                                    @foreach($teamList as $team)
                                    <option value="{{ $team['value'] }}">{{ $team['label'] }}</option>
                                    @endforeach
                                @endif
                            @else
                                @if (!$teamList->isEmpty())
                                    <?php $teamCount = $teamList->count(); ?>
                                    @foreach($teamList as $team)
                                    <option value="{{ $team->id }}" {{ $teamCount == 1 ? 'selected' : '' }}>{{ $team->name }}</option>
                                    @endforeach
                                @endif
                            @endif
                        </select>
                    </div>
                    <div class="form-inline box-action select-media mgr-35">
                        <select class="form-control select-search" id="_me_month">
                            <option value="">{{trans('project::me.Select month')}}</option>
                            @if ($selectMonths)
                                @foreach($selectMonths as $month)
                                <option value="{{ $month['timestamp'] }}" {{ $month['string'] == $prevMonth ? 'selected' : '' }}>{{ $month['string'] }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="form-inline box-action select-media">
                        <span class="month-range-time"></span>
                    </div>
                </form>
            </div>
            <div class="col-md-2 text-right">
                <a target="_blank" href="{{ route('project::project.eval.help') }}" class="btn btn-primary">{{ trans('project::me.Help') }}</a>
            </div>
        </div>
        <div class="text-right"><i>{{ trans('project::me.Right click to comment') }}</i></div>
    </div>
    <div id="_status_content" class="box-body"></div>
    <div class="pdh-10">
        <div class="table-responsive _me_table_responsive fixed-table-container">
            <table id="_me_table" class="fixed-table table dataTable table-striped table-bordered table-hover table-grid-data table-th-middle hidden">

                @include('project::me.template.thead', ['create_team' => true, 'checkbox' => true])

                <tbody></tbody>

                @include('project::me.template.tfoot', ['create_team' => true])
            </table>
        </div>
    </div>
    <div class="box-body text-center">
        <form id="_me_assignee_form" action="{{route('project::team.eval.submit')}}" class="no-validate hidden">
            {!! csrf_field() !!}
            <div class="select-media form-inline text-left mgr-20 hidden">
                <label>{{trans('project::me.Status')}}</label>
                <select class="form-control select-search" id="_me_status">
                    @foreach ($statuses as $key => $label) 
                    <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="select-media form-inline text-left hidden">
                <label>{{trans('project::me.Assignee')}}</label>
                <select class="form-control select-search" disabled id="_me_assignee">
                    <option value="0">{{trans('project::me.Selection')}}</option>
                </select>
            </div>
            <button type="submit" class="btn-add btn-sets-box" data-noti="{{trans('project::me.Confirm submit')}}">{{trans('project::me.Submit')}}</button>
        </form>
    </div>

</div>

@endsection

@section('warn_confirn_class', 'modal-default')

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script type="text/javascript" src="{{ asset('project/js/script.js') }}"></script>
<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script src="{{ asset('lib/fixed-table/tableHeadFixer.js') }}"></script>
@include('project::me.template.script')
<script>
    IS_TEAM = true;
    newMeUrl = "{{ route('me::team.edit') }}";
</script>
<script type="text/javascript" src="{{ CoreUrl::asset('project/js/me_script.js') }}"></script>
@endsection


