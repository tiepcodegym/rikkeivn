@extends('manage_time::layout.common_layout')

@section('title-common')
	{{ trans('manage_time::view.Salary list') }}
@endsection

<?php
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Core\View\View as CoreView;
    use Rikkei\Core\View\Form as CoreForm;
    use Rikkei\Team\View\Config as TeamConfig;
?>

@section('css-common')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
    <style type="text/css">
        #filter_year {
            width: 160px;
        }
        @media screen and (max-width: 767px) {
            #filter_year {
                width: 100%;
            }
        }
    </style>
@endsection

@section('sidebar-common')
    @include('manage_time::salary.include.sidebar_salary')
@endsection

@section('content-common')
    <div class="box box-primary">
        <div class="box-header with-border">
        	<div class="row">
                <div class="col-md-5">
                    <div class="team-select-box" style="width: 100%;">
                        <label for="" class="control-label" style="margin-right: 10px; margin-top: 8px;">{{ trans('manage_time::view.Year') }}</label>
                        <div class="input-group date" id="filter_year">
                            <input type="text" class="form-control filter-grid" name="filter[except][year]" value="{{ $yearFilter }}">
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="pull-right">
                        @include('team::include.filter')
                    </div>
                </div>
            </div>
        </div>
        <!-- /.box-header -->

        <div class="box-body no-padding">
            <div class="table-responsive">
                <table class="table table-striped dataTable table-bordered table-hover table-grid-data managetime-table-control">
                    <thead>
                        <tr>
                            <th class="managetime-col-40 text-center">{{ trans('manage_time::view.Month') }}</th>
                            <th class="managetime-col-70 text-center">{{ trans('manage_time::view.Total income (đ)') }}</th>
                            <th class="managetime-col-70 text-center">{{ trans('manage_time::view.Total deduction (đ)') }}</th>
                            <th class="managetime-col-70 text-center">{{ trans('manage_time::view.Money received (đ)') }}</th>
                            <th class="managetime-col-70 text-center">{{ trans('manage_time::view.This preiod of payment (đ)') }}</th>
                            <th class="managetime-col-70 text-center">{{ trans('manage_time::view.Unpaid wages (đ)') }}</th>
                            <th class="managetime-col-70 text-center">{{ trans('manage_time::view.Total payment (đ)') }}</th>
                            <th class="managetime-col-40 text-center">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (isset($collectionModel) && count($collectionModel))
                            @foreach($collectionModel as $item)
                                <tr class="text-right">
                                    <td class="text-center">{{ $item->month }}</td>
                                    <td>{{ number_format($item->total_income, 2) }}</td>
                                    <td>{{ number_format($item->total_deduction, 2) }}</td>
                                    <td>{{ number_format($item->money_received, 2) }}</td>
                                    <td>{{ number_format($item->money_received, 2) }}</td>
                                    <td>0.00</td>
                                    <td>{{ number_format($item->money_received, 2) }}</td>
                                    <td class="align-center">
                                        <a href="{{ route('manage_time::profile.salary.salary-detail', ['id' => $item->salary_table_id]) }}" class="btn btn-success" title="{{ trans('manage_time::view.View detail') }}">
                                            <i class="fa fa-info-circle"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="8" class="text-center">
                                    <h2 class="no-result-grid">{{ trans('manage_time::view.No results found') }}</h2>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        <!-- /.box-body -->

        <div class="box-footer no-padding">
            @include('team::include.pager')
        </div>
        <!-- /.box-footer -->
    </div>
    <!-- /. box -->
@endsection

@section('script-common')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $('#filter_year').datepicker({
                format: " yyyy",
                viewMode: "years",
                minViewMode: "years",
                autoclose: true,
            }).on('changeDate', function(selected) {
                $('.btn-search-filter').trigger('click');
            });

            $('#filter_year input').keypress(function (e) {
                if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                    return false;
                }
            });
            
            $('#filter_year input').on('keyup', function(e) {
                e.stopPropagation();
                if (e.keyCode == 13) {
                    $('.btn-search-filter').trigger('click');
                }
            });
        });
    </script>
@endsection