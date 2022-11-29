<?php
use Rikkei\Core\View\CoreUrl;
use Rikkei\Core\View\Form as FilterForm;
use Rikkei\Team\View\TeamList;
use Rikkei\FinesMoney\Model\FinesMoney;

$teamsOptionAll = TeamList::toOption(null, false, false);
?>

@extends('layouts.default')

@section('title', trans('fines_money::view.List working time'))

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css">
    <style>
        .fines-group-btn .filter-action{
            display: inline-block;
        }
    </style>
@stop

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-info filter-wrapper"
                 data-url="{{ URL::route('fines-money::fines-money.manage.list',['tab'=> $currentTab])}}">
                <div class="box-header with-border">
                    <?php
                    $unpaid = ($result['paid'] < $result['sum'] && $result['sum']) ? $result['sum'] - $result['paid'] : 0
                    ?>
                    <div class="col-lg-2 col-sm-3" id="display-sum-money" style="font-size: 14px;">
                        <table class="table table-bordered table-grid-data">
                            <tbody>
                            <tr class="info">
                                <th width="150">{{ trans('fines_money::view.total') }}</th>
                                <td align="right">{{ $result['sum'] ? number_format($result['sum']) : 0}}</td>
                            </tr>
                            <tr class="success">
                                <th>{{ trans('fines_money::view.total_paid') }}</th>
                                <td align="right">{{ $result['paid'] ? number_format($result['paid']) : 0}}</td>
                            </tr>
                            <tr class="warning">
                                <th>{{ trans('fines_money::view.total_un_paid') }}</th>
                                <td align="right">{{ number_format($unpaid) }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-lg-2 col-sm-3">
                        {{ Form::label('startMonth', trans('fines_money::view.From month')) }}
                        <div class="form-group">
                            <div class='input-group search-datepicker'>
                                {{ Form::text('filter[search][startMonth]', FilterForm::getFilterData('search', 'startMonth', $urlFilter), ['class' => 'form-control filter-grid search-datepicker', 'autocomplete' => 'off']) }}
                                <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-sm-3">
                        {{ Form::label('endMonth', trans('fines_money::view.To month')) }}
                        <div class="form-group">
                            <div class='input-group date search-datepicker'>
                                {{ Form::text('filter[search][endMonth]', FilterForm::getFilterData('search', 'endMonth', $urlFilter), ['class' => 'form-control filter-grid search-datepicker', 'autocomplete' => 'off']) }}
                                <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-sm-3">
                        {{ Form::label('team', trans('fines_money::view.Team')) }}
                        <div class="form-group">
                            <select name="filter[search][team_id]" id="groupTeam"
                                    class="form-control select-search select-grid filter-grid"
                                    autocomplete="off">
                                <option value="0">&emsp;</option>
                                @if ($teamsOptionAll)
                                    <?php $filterTeamId = FilterForm::getFilterData('search', 'team_id', $urlFilter); ?>
                                    @foreach($teamsOptionAll as $option)
                                        <option value="{{ $option['value'] }}"
                                                class="setBod" {{ $filterTeamId && $option['value'] == $filterTeamId ? 'selected' : '' }}>{{ $option['label'] }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>

                    <div class="pull-right fines-group-btn">
                        {{--<button type="button" class="btn btn-success" data-toggle="modal"--}}
                                {{--data-target="#import_fines_money"--}}
                                {{--title="{{ trans('fines_money::view.Using in case import fines money not in time keeping') }}"> {{ trans('manage_time::view.Import') }}</button>--}}
                        <button type="button" class="btn btn-success" data-toggle="modal"
                                data-target="#update_fines_money"
                                title="{{ trans('fines_money::view.Using in case update fines money') }}"> {{ trans('manage_time::view.Import') }}</button>
                        <button class="btn btn-success" type="button" data-toggle="modal"
                                data-target="#modal_export_fines_money">Export
                        </button>
                        @include('team::include.filter')
                    </div>
                </div>
                <div class="tab-content">
                    <ul class="nav nav-tabs">
                        <li <?php if ($currentTab === 'unpaid') echo ' class="active"'; ?>>
                            <a href="{{ URL::route('fines-money::fines-money.manage.list',['tab'=>'unpaid'])}}">
                                {{ trans('fines_money::view.List employee with status unpaid') }}
                            </a>
                        </li>
                        <li <?php if ($currentTab === 'paid') echo ' class="active"'; ?>>
                            <a href="{{ URL::route('fines-money::fines-money.manage.list',['tab'=>'paid']) }}">
                                {{ trans('fines_money::view.List employee with status paid') }}
                            </a>
                        </li>
                        <li <?php if (!in_array($currentTab, ['paid', 'unpaid'])) echo ' class="active"'; ?>>
                            <a href="{{ URL::route('fines-money::fines-money.manage.list',['tab'=>'all'])}}">
                                {{ trans('fines_money::view.List employee fines money') }}
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div id="home" class="tab-pane fade in active">
                            @include('fines_money::manage.include.tab')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('fines_money::manage.include.col_export', ['tab' => $currentTab])
    @include('fines_money::manage.include.import_update')
    @include('fines_money::manage.include.modal_import')
@stop

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.15.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
    <script type="text/javascript">
        var _token = '{{ csrf_token() }}';
        var listStatus = JSON.parse('{!! json_encode($status) !!}');
        var textConfirm = '{{ trans('core::view.Are you sure to do this action?') }}';
        var MAX_TEXT = 100;
        var lengthMax = '{{ trans('core::view.This field not be greater than :number characters', ['number' => 100]) }}';
        var textRequired = '{{ trans('core::view.This field is required') }}';
        var STATUS_PAID = '{{ FinesMoney::STATUS_PAID }}';
        var STATUS_UNPAID = '{{ FinesMoney::STATUS_UN_PAID }}';
        var STATUS_UPDATE_MONEY = '{{ FinesMoney::STATUS_UPDATE_MONEY }}';
        var arrayUnpaid = [STATUS_UNPAID, STATUS_UPDATE_MONEY];
        var textNoneItemSelected = '{{ trans("team::export.none_item_selected") }}';
        $('#form_update_fines_money').validate({
            rules: {
                file: 'required',
            },
            messages: {
                file: textRequired,
            },
        });
    </script>
    <script src="{{ CoreUrl::asset('fines_money/js/index.js') }}"></script>
@stop

