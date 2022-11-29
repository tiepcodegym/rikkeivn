<?php
use Rikkei\Core\View\CoreUrl;
use Rikkei\Team\Model\Team;
?>
@extends('layouts.default')
@section('title', trans('resource::view.Employee busy rate'))
@section('content')
<div class="row">
    <div class="col-md-3">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h2 class="box-title"><i class="fa fa-search"></i> {!!trans('resource::view.Search')!!}</h2>
            </div>
            <div class="box-body">
                <form id="form-busy" class="form-horizontal" method="get" action="." autocomplete="off">
                    <div class="form-group">
                        <label class="col-md-4 control-label required">{!!trans('resource::view.Start')!!}<em>*</em></label>
                        <div class="col-md-8">
                            <input type="text" name="start" class="form-control" 
                                placeholder="yyyy-mm-dd" data-flag-type="date" data-pager-input />
                            <i class="fa fa-calendar form-control-feedback margin-right-20"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label required">{!!trans('resource::view.End')!!}<em>*</em></label>
                        <div class="col-md-8">
                            <input type="text" name="end" class="form-control" 
                                placeholder="yyyy-mm-dd" data-flag-type="date" data-pager-input />
                            <i class="fa fa-calendar form-control-feedback margin-right-20"></i>
                        </div>
                    </div>
                    <div class="form-group form-group-select2">
                        <label class="col-md-4 control-label">{!!trans('resource::view.Team')!!}</label>
                        <div class="col-md-8">
                            <select name="t[]" class="hidden" data-fg-dom="team-dev" multiple
                                data-select2-dom="1" data-select2-multi-trim="1" data-pager-input></select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">{!!trans('resource::view.Skills')!!}</label>
                        <div class="col-md-8">
                            <button type="button" class="btn btn-primary btn-xs margin-top-10" data-fg-dom="btn-add-skill">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-group-select2 hidden" data-fg-dom="filter-skill">
                        <div class="row form-group" data-fg-dom="f-skill-item">
                            <div class="col-md-6">
                                <select class="hidden" name="s[xxx]" data-select2-dom="1" data-select2-search="1" data-pager-input>
                                    <option value="">&nbsp;</option>
                                    @foreach ($tagCollection as $item)
                                        <option value="{!!$item->id!!}">{!!$item->value!!}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="number" name="e[xxx]" value="" placeholder="2 (year)" class="form-control"
                                    title="Experience: >= 2 Year" data-pager-input />
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-danger btn-xs margin-top-5" data-fg-dom="btn-remove-skill">
                                    <i class="fa fa-minus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary" data-pager-search-btn>
                        <i class="fa fa-search"></i> {!!trans('resource::view.Search')!!}
                        <i class="fa fa-spin fa-refresh hidden" data-ico-load="ajax"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="box box-primary">
            <div class="box-body">
                <h4 data-pager-not>{!!trans('resource::view.No items found')!!}</h4>
                <div class="table-responsive hidden" data-pager-result>
                    <div class="row">
                        @for ($i = 4; $i > 0; $i--)
                            <div class="col-md-3">
                                <p><span data-count-busy="{!!$i!!}">0</span> {!!trans('resource::view.member')!!}: &nbsp;{!!trans('resource::view.Note bar color.' . $i)!!}</p>
                            </div>
                        @endfor
                    </div>
                    <table class="table table-striped dataTable table-bordered table-hover table-grid-data not-padding-th">
                        <colgroup>
                            <col style="width: 200px;"/>
                            <col />
                            <col style="width: 56px" />
                        </colgroup>
                        <thead>
                            <tr>
                                <th>{!!trans('resource::view.Employee')!!}</th>
                                <th>{!!trans('resource::view.Busy rate')!!}</th>
                                <th>&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody data-pager-list data-pager-url="{!!route('resource::busy.index')!!}">
                            <tr data-pager-item>
                                <td>
                                    <span>{name}</span><br/>
                                    <small>{email} - {teams}</small>
                                </td>
                                <td>
                                    <div class="progressbar effort-bar ui-progressbar ui-corner-all ui-widget ui-widget-content"
                                        role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="100"
                                        data-progress-dom="wrapper">
                                        <div class="ui-progressbar-value {color}" data-toggle="tooltip"
                                            style="width: {width}%;" title="week {week}: {effort}%"></div>
                                    </div>
                                </td>
                                <td>
                                    <a href="{url}" class="btn btn-primary">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <?php /*<div class="" data-pager-more>
                        <i class="fa fa-spin fa-refresh"></i>
                    </div> */ ?>
                </div>
            </div>
            <div class="box-footer">
                <div>
                    {!!trans('resource::view.Note bar guide')!!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
@endsection
@section('script')
<script type="text/javascript">
var globalPassModule = {
    urlCV: '{!!route('team::member.profile.index', ['employeeId' => 'xxx', 'type' => 'cv'])!!}',
    teamPath: JSON.parse('{!! json_encode(Team::getTeamPathTree()) !!}'),
    trans: {
        end_greater: '{!!trans('validation.Must be greater or equal than :attribute.', ['attribute' => trans('resource::view.Start')])!!}',
    },
};
</script>
<script src="{{ URL::asset('lib/js/moment.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="{{ CoreUrl::asset('common/js/methods.validate.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script type="text/javascript" src="{{ CoreUrl::asset('resource/js//busy/busy.js') }}"></script>
@endsection
