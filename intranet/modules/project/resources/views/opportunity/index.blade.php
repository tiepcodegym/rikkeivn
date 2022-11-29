<?php
use Rikkei\Core\View\CoreUrl;
use Rikkei\Core\View\Form as FormView;
use Rikkei\Project\View\OpporView;
use Rikkei\Team\View\Config;
?>

@extends('layouts.default')

@section('title', trans('project::view.Opportunity'))

@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css">
@endsection

@section('content')

<div class="box box-primary">
    <div class="box-body">
        <div class="row">
            <div class="col-md-8">
                <a href="{{ route('project::oppor.edit') }}" class="btn btn-primary"><i class="fa fa-plus"></i> {{ trans('project::view.Create') }}</a>
            </div>
            <div class="col-md-4">
                @include('team::include.filter', ['domainTrans' => 'project'])
            </div>
        </div>
    </div>
 
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped dataTable wr-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th class="sorting white-space-nowrap {{ Config::getDirClass('name') }}" data-order="name" data-dir="{{ Config::getDirOrder('name') }}">{{ trans('project::view.Name') }}</th>
                        <th class="sorting white-space-nowrap {{ Config::getDirClass('cust_name') }}" data-order="cust_name" data-dir="{{ Config::getDirOrder('cust_name') }}">{{ trans('project::view.Customer') }}</th>
                        <th class="sorting white-space-nowrap {{ Config::getDirClass('team_names') }}" data-order="team_names" data-dir="{{ Config::getDirOrder('team_names') }}">{{ trans('project::view.Group') }}</th>
                        <th class="sorting white-space-nowrap {{ Config::getDirClass('approved_cost') }}" data-order="approved_cost" data-dir="{{ Config::getDirOrder('approved_cost') }}">{{ trans('project::view.Approved cost') }}</th>
                        <th class="sorting white-space-nowrap {{ Config::getDirClass('billable_effort') }}" data-order="billable_effort" data-dir="{{ Config::getDirOrder('billable_effort') }}">{{ trans('project::view.Billable Effort') }}</th>
                        <th class="sorting white-space-nowrap {{ Config::getDirClass('start_date') }}" data-order="start_date" data-dir="{{ Config::getDirOrder('start_date') }}">{{ trans('project::view.Start Date') }}</th>
                        <th class="sorting white-space-nowrap {{ Config::getDirClass('end_date') }}" data-order="end_date" data-dir="{{ Config::getDirOrder('end_date') }}">{{ trans('project::view.End Date') }}</th>
                        <th class="sorting white-space-nowrap {{ Config::getDirClass('sale_emails') }}" data-order="sale_emails" data-dir="{{ Config::getDirOrder('sale_emails') }}">{{ trans('project::view.Salesperson') }}</th>
                        <th class="sorting white-space-nowrap {{ Config::getDirClass('creator_email') }}" data-order="creator_email" data-dir="{{ Config::getDirOrder('creator_email') }}">{{ trans('project::view.Created by') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td></td>
                        <td>
                            <input type="text" name="filter[{{ $opTbl }}.name]" value="{{ FormView::getFilterData($opTbl.'.name') }}" 
                               placeholder="{{ trans('project::view.Search') }}..." class="filter-grid form-control minw-130">
                        </td>
                        <td>
                            <input type="text" name="filter[cust.name]" value="{{ FormView::getFilterData('cust.name') }}" 
                               placeholder="{{ trans('project::view.Search') }}..." class="filter-grid form-control minw-130">
                        </td>
                        <td>
                            <input type="text" name="filter[team.name]" value="{{ FormView::getFilterData('team.name') }}" 
                               placeholder="{{ trans('project::view.Search') }}..." class="filter-grid form-control minw-130">
                        </td>
                        <td></td>
                        <td></td>
                        <td>
                            <input type="text" name="filter[{{ $opTbl }}.start_at]" value="{{ FormView::getFilterData($opTbl.'.start_at') }}" 
                               placeholder="{{ trans('project::view.Search') }}..." class="filter-grid form-control minw-130">
                        </td>
                        <td>
                            <input type="text" name="filter[{{ $opTbl }}.end_at]" value="{{ FormView::getFilterData($opTbl.'.end_at') }}" 
                               placeholder="{{ trans('project::view.Search') }}..." class="filter-grid form-control minw-130">
                        </td>
                        <td>
                            <input type="text" name="filter[sale.email]" value="{{ FormView::getFilterData('sale.email') }}" 
                               placeholder="{{ trans('project::view.Search') }}..." class="filter-grid form-control minw-130">
                        </td>
                        <td>
                            <input type="text" name="filter[creator.email]" value="{{ FormView::getFilterData('creator.email') }}" 
                               placeholder="{{ trans('project::view.Search') }}..." class="filter-grid form-control minw-130">
                        </td>
                        <td></td>
                    </tr>
                    @if (!$collectionModel->isEmpty())
                        @foreach ($collectionModel as $order => $item)
                        <tr>
                            <td>{{ $order + 1 + ($collectionModel->currentPage() - 1) * $collectionModel->perPage() }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->cust_name }}</td>
                            <td>{{ $item->team_names }}</td>
                            <td>
                                @if ($item->approved_cost)
                                {{ $item->approved_cost }} ({{ $item->getLabelTypeMM() }})
                                @endif
                            </td>
                            <td>
                                @if ($item->billable_effort)
                                {{ $item->billable_effort }} ({{ $item->getLabelTypeMM() }})
                                @endif
                            </td>
                            <td>{{ $item->start_date }}</td>
                            <td>{{ $item->end_date }}</td>
                            <td>{{ OpporView::renderAccount($item->sale_emails) }}</td>
                            <td>{{ ucfirst(preg_replace('/\s|@.*/', '', $item->creator_email)) }}</td>
                            <td class="white-space-nowrap">
                                <a href="{{ route('project::oppor.edit', $item->id) }}" class="btn btn-success" title="{{ trans('project::view.Edit') }}"><i class="fa fa-edit"></i></a>
                                {!! Form::open(['method' => 'DELETE', 'route' => ['project::oppor.delete', $item->id], 'class' => 'no-validate form-inline']) !!}
                                    <input type="hidden" name="method" value="DELETE">
                                    <button class="btn-delete delete-confirm" title="{{ trans('project::view.Delete') }}"><i class="fa fa-trash"></i></button>
                                {!! Form::close() !!}
                            </td>
                        </tr>
                        @endforeach
                    @else
                    <tr>
                        <td colspan="11" class="text-center"><h4>{{ trans('resource::message.Not found item') }}</h4></td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        @include('team::include.pager', ['domainTrans' => 'resource'])
    </div>
</div>

@endsection

@section('script')
<script src="{{ URL::asset('lib/js/moment.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
<script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
@endsection
