<?php
use Rikkei\Core\View\View;
use Rikkei\Project\Model\Risk;
use Rikkei\Project\View\View as ViewProject;
use Rikkei\Core\View\View as ViewHelper;
use Carbon\Carbon;
$today = Carbon::parse(Carbon::today());
?>

<div class="row">
    <div class="col-sm-12">
        <div class="table-responsive">
            <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                <thead>
                    <tr>
                        <th>{{ trans('project::view.No.') }}</th>
                        <th >{{trans('project::view.ID risk')}}</th>
                        <th >{{ trans('project::view.Type') }}</th>
                        <th style="width: 80px;">{{ trans('project::view.Title') }}</th>
                        <th >{{ trans('project::view.Status') }}</th>
                        <th >{{ trans('project::view.Priority') }}</th>
                        <th class="">{{ trans('project::view.Owner') }}</th>
                        <th class="">{{ trans('project::view.Due Date') }}</th>
                        <th class="">{{ trans('project::view.Create date') }}</th>
                        <th class="">{{ trans('project::view.Update date') }}</th>
                    </tr>
                </thead>
                <tbody>
                @if(isset($collectionModel) && count($collectionModel))
                    @foreach($collectionModel as $key => $risk)
                        <tr class="background-{{ViewProject::getColorStatusWorkOrder($risk->status)}}" data-toggle="tooltip" data-placement="top" title="{{Risk::statusLabel()[$risk->status]}}">
                            <td class="align-center">{{$key + 1}}</td>
                            <td>{{ $risk->id }}</td>
                            <td>
                                {{ empty(Risk::getTypeList()[$risk->type]) ? '' : Risk::getTypeList()[$risk->type] }}
                            </td>
                            <td>
                                <a href="{{ route('project::report.risk.detail', ['id' => $risk->id]) }}">{!!nl2br(e($risk->content))!!}</a>
                            </td>
                            <td>
                                {{ empty(Risk::statusLabel()[$risk->status]) ? Risk::statusLabel()[Risk::STATUS_OPEN] : Risk::statusLabel()[$risk->status] }}
                            </td>
                            <td>
                                {{ Risk::getKeyLevelRisk($risk->level_important) }}
                            </td>
                            <td>
                                @if ($risk->team_owner)
                                    {{ $risk->team_name }}
                                @endif
                                @if ($risk->owner)
                                    @if ($risk->team_owner)
                                        {{ ' - ' }}
                                    @endif
                                    {{ViewHelper::getNickName($risk->owner_email)}}
                                @endif
                            </td>
                            <td>
                                @if ($risk->due_date)
                                    {{ $risk->due_date }}
                                @endif
                            </td>
                            <td>
                                @if ($risk->created_at)
                                    {{ Carbon::parse($risk->created_at)->format('Y-m-d') }}
                                @endif
                            </td>
                            <td>
                                @if ($risk->updated_at)
                                    {{ Carbon::parse($risk->updated_at)->format('Y-m-d') }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="10" class="text-center">
                            <h2 class="no-result-grid">{{ trans('project::view.No results found') }}</h2>
                        </td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
        <div class="box-body">
            @include('team::include.pager', ['domainTrans' => 'project'])
        </div>
    </div>
</div>