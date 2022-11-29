@extends('layouts.default')

<?php
use Rikkei\Core\View\View as CoreView;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Core\View\Form as CoreForm;
?>

@section('title', trans('asset::view.My request asset'))

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('manage_asset/css/style.css') }}" />
    <style>
        .th-action-date .tooltip-inner {
            white-space: unset;
        }
    </style>
@endsection

@section('content')

<div class="content-sidebar">
    <div class="content-col">
        
        <div class="box box-info">
            <div class="box-body">
                <div class="pull-left">
                    <h3 class="box-title managetime-box-title margin-top-0">{{ $pageTitle }}</h3>
                </div>
                <div class="pull-right">
                    <a href="{{ route('asset::resource.request.edit') }}" class="btn btn-success">{{ trans('asset::view.Create new') }}</a>
                    <div class="form-inline">
                        @include('team::include.filter')
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                    <thead>
                        <tr>
                            <th class="width-25">{{ trans('core::view.NO.') }}</th>
                            <th class="sorting {{ Config::getDirClass('request_name') }} col-title" data-order="request_name" data-dir="{{ Config::getDirOrder('request_name') }}">{{ trans('asset::view.Request name') }}</th>
                            <th class="sorting {{ Config::getDirClass('request_date') }} col-title" data-order="request_date" data-dir="{{ Config::getDirOrder('request_date') }}">{{ trans('asset::view.Request date') }}</th>
                            <th class="sorting {{ Config::getDirClass('emp_email') }} col-title" data-order="emp_email" data-dir="{{ Config::getDirOrder('emp_email') }}">{{ trans('asset::view.Asset user') }}</th>
                            <th class="sorting {{ Config::getDirClass('team_names') }} col-title" data-order="team_names" data-dir="{{ Config::getDirOrder('team_names') }}">{{ trans('asset::view.Team') }}</th>
                            <th class="sorting {{ Config::getDirClass('reviewer_email') }} col-title" data-order="reviewer_email" data-dir="{{ Config::getDirOrder('reviewer_email') }}">{{ trans('asset::view.Reviewer') }}</th>
                            <th class="sorting {{ Config::getDirClass('creator_email') }} col-title" data-order="creator_email" data-dir="{{ Config::getDirOrder('creator_email') }}">{{ trans('asset::view.Creator request') }}</th>
                            <th class="sorting white-space-nowrap {{ Config::getDirClass('status') }} col-title" data-order="status" data-dir="{{ Config::getDirOrder('status') }}">{{ trans('asset::view.Status') }}</th>
                            <th style="width: 130px;" class="th-action-date">{{ trans('asset::view.Action date') }}
                                <i class="fa fa-question-circle" data-toggle="tooltip"  title="{{ trans('asset::view.Action date submit/review/approve') }}"></i>
                            </th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td></td>
                            <td>
                                <input type="text" name="filter[{{ $tblRq }}.request_name]" class="form-control filter-grid" placeholder="{{ trans('asset::view.Search') }}..."
                                       value="{{ CoreForm::getFilterData($tblRq.'.request_name') }}">
                            </td>
                            <td>
                                <input type="text" name="filter[{{ $tblRq }}.request_date]" class="form-control filter-grid" placeholder="{{ trans('asset::view.Search') }}..."
                                       value="{{ CoreForm::getFilterData($tblRq.'.request_date') }}">
                            </td>
                            <td>
                                <input type="text" name="filter[emp.email]" class="form-control filter-grid" placeholder="{{ trans('asset::view.Search') }}..."
                                       value="{{ CoreForm::getFilterData('emp.email') }}">
                            </td>
                            <td>
                                <select name="filter[excerpt][team]" class="form-control filter-grid select-grid select-search"
                                        style="min-width: 150px;">
                                    <?php
                                    $filterTeam = CoreForm::getFilterData('excerpt', 'team');
                                    ?>
                                    <option value="">&nbsp;</option>
                                    @foreach ($teamList as $team)
                                    <option value="{{ $team['value'] }}" {{ $filterTeam == $team['value'] ? 'selected' : '' }}>{{ $team['label'] }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                @if ($type != 'reviewer')
                                <input type="text" name="filter[reviewer.email]" class="form-control filter-grid" placeholder="{{ trans('asset::view.Search') }}..."
                                       value="{{ CoreForm::getFilterData('reviewer.email') }}">
                                @endif
                            </td>
                            <td>
                                @if ($type && $type != 'creator')
                                <input type="text" name="filter[creator.email]" class="form-control filter-grid" placeholder="{{ trans('asset::view.Search') }}..."
                                       value="{{ CoreForm::getFilterData('creator.email') }}">
                                @endif
                            </td>
                            <td></td>
                            <td></td>
                        </tr>
                        @if (!$collectionModel->isEmpty())
                            <?php
                            $perPage = $collectionModel->perPage();
                            $currentPage = $collectionModel->currentPage();
                            ?>
                            @foreach ($collectionModel as $order => $item)
                            <tr>
                                <td>{{ $order + 1 + ($currentPage - 1) * $perPage }}</td>
                                <td>
                                    <div class="minw-230">
                                        <a href="{{ route('asset::profile.view', ['id' => $item->id]) }}">{{ $item->request_name }}</a>
                                    </div>
                                </td>
                                <td class="white-space-nowrap">{{ $item->request_date }}</td>
                                <td>{{ CoreView::getNickName($item->emp_email) }}</td>
                                <td>{{ $item->team_names }}</td>
                                <td>{{ CoreView::getNickName($item->reviewer_email) }}</td>
                                <td>{{ CoreView::getNickName($item->creator_email) }}</td>
                                <td>{!! $item->renderStatusHtmlItem($listStatuses, 'label') !!}</td>
                                <td>
                                    <?php
                                    $date = '';
                                    if ($item->action_date) {
                                        $arrDate = explode(",", $item->action_date);
                                        $date = $arrDate[0];
                                    }
                                    ?>
                                    {{ $date }}
                                </td>
                                <td>
                                    <form action="{{ route('asset::resource.request.delete')}}" method="post" class="form-inline">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="hidden" name="_method" value="delete">
                                        <input type="hidden" name="id" value="{{ $item->id }}">
                                        <input type="hidden" name="status" value="{{ $item->status }}">
                                        <button href="" class="btn-delete delete-confirm" title="{{ trans('asset::view.Delete') }}" >
                                            <span><i class="fa fa-trash"></i></span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        @else
                        <tr>
                            <td colspan="8">
                                <h4 class="text-center">{{ trans('asset::view.No results data') }}</h4>
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="box-body">
                @include('team::include.pager')
            </div>
        </div>
        
    </div>
    <div class="sidebar-col">
        <div class="sidebar-inner">
            @include('asset::profile.sidebar')
        </div>
    </div>
</div>

@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script>
        selectSearchReload();
        $(function() {
            $("input[name=status]").each(function() {
                if ($(this).val() != 1) {
                    $(this).next("button").attr("disabled", "disabled");
                }
            });
        });
    </script>
@endsection
