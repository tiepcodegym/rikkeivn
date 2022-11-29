@extends('layouts.default')
@section('title')
    {{ trans('sales::view.Css list') }}
@endsection
@section('content')
<?php

use Rikkei\Sales\Model\CssResult;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View;
use Rikkei\Core\View\MobileDetect;
use Rikkei\Team\View\Permission;
use Rikkei\Sales\Model\Css;
use Rikkei\Team\View\TeamList;

$detectMobile = new MobileDetect();
$deviceIos = $detectMobile->isiOS();
$analyzeFilter = Form::getFilterData('except', 'analyze_status');
$filterStatusApprove = Form::getFilterData('except', 'approve_status');
$teamsOptionAll = TeamList::toOption(null, true, false);
$teamChargeFilter = Form::getFilterData('except','team_charge_id', null);
$categoryFilter = Form::getFilterData('except', 'css_project_type.name');
$teamFilter = Form::getFilterData('except','teams.name');
?>
<div class="row list-css-page">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-6"></div>
                    <div class="col-sm-6"></div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="box-body">
                            <tr class="filter-input-grid">
                                <div class="row col-sm-12">
                                    <div class="form-group row col-sm-4">
                                        <label for="" class="col-sm-3 col-form-label">{{ trans('sales::view.Type') }}</label>
                                        <div class="col-sm-9">
                                            <select type="text" class='form-control filter-grid' name="filter[except][css_project_type.name]" >
                                                <option value="">&nbsp;</option>
                                                @foreach(Css::getCssProjectTypeLabel() as $key => $option)
                                                    <option value="{{ $key }}"<?php
                                                    if ($key == $categoryFilter): ?> selected<?php endif;
                                                        ?>>{{ $option }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row col-sm-4">
                                        <label for="" class="col-sm-3 col-form-label">{{ trans('sales::view.Project') }}</label>
                                        <div class="col-sm-9">
                                            <input type="text" class='form-control filter-grid' name="filter[except][project_name]" value="{{ Form::getFilterData('except', 'project_name') }}" placeholder="{{ trans('team::view.Search') }}..." />
                                        </div>
                                    </div>
                                    <div class="form-group row col-sm-4">
                                        <label for="" class="col-sm-3 col-form-label">{{ trans('project::view.Team_in_charge') }}</label>
                                        <div class="col-sm-9">
                                            <select name="filter[except][team_charge_id]" class="form-control select-grid filter-grid select-search  has-search">
                                                <option value="">&nbsp;</option>
                                                @foreach($teamsOptionAll as $option)
                                                    <option value="{{ $option['value'] }}"<?php
                                                    if ($option['value'] == $teamChargeFilter): ?> selected<?php endif;
                                                        ?>>{{ $option['label'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row col-sm-12">
                                    <div class="form-group row col-sm-4">
                                        <label for="" class="col-sm-3 col-form-label">{{ trans('project::view.Team') }}</label>
                                        <div class="col-sm-9">
                                            <select name="filter[except][teams.name]" class="form-control select-grid filter-grid select-search has-search">
                                                <option value="">&nbsp;</option>
                                                @foreach($teamsOptionAll as $option)
                                                    <option value="{{ $option['value'] }}"<?php
                                                    if ($option['value'] == $teamFilter): ?> selected<?php endif;
                                                        ?>>{{ $option['label'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row col-sm-4">
                                        <label for="" class="col-sm-3 col-form-label">{{ trans('sales::view.Creator') }}</label>
                                        <div class="col-sm-9">
                                            <input type="text" class='form-control filter-grid' name="filter[except][employees.name]" value="{{ Form::getFilterData('except', 'employees.name') }}" placeholder="{{ trans('team::view.Search') }}..."  />
                                        </div>
                                    </div>
                                    <div class="form-group row col-sm-4">
                                        <label for="" class="col-sm-3 col-form-label">{{ trans('sales::view.Company customer') }}</label>
                                        <div class="col-sm-9">
                                            <input type="text" class='form-control filter-grid' name="filter[except][company_name]" value="{{ Form::getFilterData('except', 'company_name') }}" placeholder="{{ trans('team::view.Search') }}..." />
                                        </div>
                                    </div>
                                </div>
                                <div class="row col-sm-12">
                                    <div class="form-group row col-sm-4">
                                        <label for="" class="col-sm-3 col-form-label">{{ trans('sales::view.Customer represent') }}</label>
                                        <div class="col-sm-9">
                                            <input type="text" class='form-control filter-grid' name="filter[except][customer_name]" value="{{ Form::getFilterData('except', 'customer_name') }}" placeholder="{{ trans('team::view.Search') }}..." />
                                        </div>
                                    </div>
                                    <div class="form-group row col-sm-4">
                                        <label for="" class="col-sm-3 col-form-label">{{ trans('sales::view.From date2') }}</label>
                                        <div class="col-sm-9">
                                            <input class='date-picker form-control filter-grid' id="time_from" name="filter[except][created_at]" value="{{ Form::getFilterData('except', 'created_at', null) }}" placeholder="{{ trans('team::view.Search') }}..." />
                                        </div>
                                    </div>
                                    <div class="form-group row col-sm-4">
                                        <label for="" class="col-sm-3 col-form-label">{{ trans('sales::view.To date2') }}</label>
                                        <div class="col-sm-9">
                                            <input class='date-picker form-control filter-grid' id="time_to" name=filter[except][updated_at]" value="{{ Form::getFilterData('except', 'updated_at', null) }}" placeholder="{{ trans('team::view.Search') }}..." />
                                        </div>
                                    </div>
                                </div>
                                <div class="row col-sm-12">
                                    <div class="form-group row col-sm-4">
                                        <label for="" class="col-sm-3 col-form-label">{{ trans('sales::view.Status analyzed') }}</label>
                                        <div class="col-sm-9">
                                            <select name="filter[except][analyze_status]" class="form-control select-grid filter-grid select-search">
                                                <option value="">{{ trans('sales::view.All') }}</option>
                                                <option value="3" <?php
                                                if ($analyzeFilter == 3): ?> selected<?php endif;
                                                    ?>>{{ trans('sales::view.Not make yet') }}</option>
                                                <option value="1"<?php
                                                if ($analyzeFilter == 1): ?> selected<?php endif;
                                                    ?>>{{ trans('sales::view.Not analyzed yet') }}</option>
                                                <option value="2"<?php
                                                if ($analyzeFilter == 2): ?> selected<?php endif;
                                                    ?>>{{ trans('sales::view.Analyzed') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row col-sm-4">
                                        <label for="" class="col-sm-3 col-form-label">{{ trans('sales::view.CSS.List.Date created from') }}</label>
                                        <div class="col-sm-9">
                                            <input class='date-picker form-control filter-grid' id="css_created_from" name="filter[except][css_created_from]" value="{{ Form::getFilterData('except', 'css_created_from', null) }}" placeholder="{{ trans('team::view.Search') }}..." />
                                        </div>
                                    </div>
                                    <div class="form-group row col-sm-4">
                                        <label for="" class="col-sm-3 col-form-label">{{ trans('sales::view.CSS.List.Date created to') }}</label>
                                        <div class="col-sm-9">
                                            <input class='date-picker form-control filter-grid' id="css_created_to" name="filter[except][css_created_to]" value="{{ Form::getFilterData('except', 'css_created_to', null) }}" placeholder="{{ trans('team::view.Search') }}..." />
                                        </div>
                                    </div>
                                </div>
                                <div class="row col-sm-12">
                                    <div class="form-group row col-sm-4">
                                        <label for="" class="col-sm-3 col-form-label">{{ trans('sales::view.Status approve') }}</label>
                                        <div class="col-sm-9">
                                            <select name="filter[except][approve_status]" class="form-control select-grid filter-grid select-search">
                                                <option value="">{{ trans('sales::view.All') }}</option>
                                                <option value="{{CssResult::STATUS_APPROVED}}" <?php
                                                if ($filterStatusApprove == CssResult::STATUS_APPROVED): ?> selected<?php endif;
                                                    ?>>{{ trans('sales::view.Approved') }}</option>
                                                <option value="1"<?php
                                                if ($filterStatusApprove == 1): ?> selected<?php endif;
                                                    ?>>{{ trans('sales::view.Unapproved') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>
                                <!--                                            <select style="width: 160px" name="filter[except][analyze_status]" class="form-control select-grid filter-grid select-search">
                                                <option value="">&nbsp;</option>
                                                <option value="1"<?php
                                if ($analyzeFilter == 1): ?> selected<?php endif;
                                ?>>Not analyzed yet</option>
                                                <option value="2"<?php
                                if ($analyzeFilter == 2): ?> selected<?php endif;
                                ?>>Analyzed</option>
                                            </select>-->
                                </td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                            <div class="filter-action col-sm-12">
                                <?php /*@if($isRoot)
                                <button class="btn btn-primary btn-reset-css" >
                                    <span>{{trans('sales::view.List.Delete all')}}<i class="fa fa-spin fa-refresh hidden"></i></span>
                                </button>
                                @endif*/ ?>
                                <div class="col-md-8"></div>
                                <button class="btn btn-primary btn-reset-filter col-sm-1" style="margin-right: 5px">
                                    <span>{{ trans('team::view.Reset filter') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
                                </button>
                                <button class="btn btn-primary btn-search-filter col-sm-1" style="margin-right: 5px">
                                    <span>{{ trans('team::view.Search') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
                                </button>
                                <form action="{{ route('sales::css.export.css') }}" method="post">
                                    {!! csrf_field() !!}
                                        <button type="submit" class="btn btn-primary btn-submit-action col-sm-1">Export Css</button>
                                </form>
                            </div>
                            @include('team::include.pager')
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover dataTable" role="grid" aria-describedby="example2_info">
                                <thead>
                                    <tr role="row">
                                        <th class="sorting {{ Config::getDirClass('id') }}" data-order="id" data-dir="{{ Config::getDirOrder('id') }}" >{{ trans('sales::view.Id') }}</th>
                                        <th class="sorting {{ Config::getDirClass('project_type_name') }}" data-order="project_type_name" data-dir="{{ Config::getDirOrder('project_type_name') }}" >{{ trans('sales::view.Type') }}</th>
                                        <th class="sorting {{ Config::getDirClass('project_name') }}" data-order="project_name" data-dir="{{ Config::getDirOrder('project_name') }}" >{{ trans('sales::view.Project') }}</th>
                                        <th class="sorting {{ Config::getDirClass('teams.name') }}" data-order="teams.name" data-dir="{{ Config::getDirOrder('teams.name') }}" >{{ trans('project::view.Team_in_charge') }}</th>
                                        <th class="sorting {{ Config::getDirClass('teams.name') }}" data-order="teams.name" data-dir="{{ Config::getDirOrder('teams.name') }}" >{{ trans('project::view.Team') }}</th>
                                        <th class="sorting {{ Config::getDirClass('sale_name') }}" data-order="sale_name" data-dir="{{ Config::getDirOrder('sale_name') }}" >{{ trans('sales::view.Creator') }}</th>
                                        <th class="sorting {{ Config::getDirClass('company_name') }}" data-order="company_name" data-dir="{{ Config::getDirOrder('company_name') }}" >{{ trans('sales::view.Company customer') }}</th>
                                        <th class="sorting {{ Config::getDirClass('customer_name') }}" data-order="customer_name" data-dir="{{ Config::getDirOrder('customer_name') }}" >{{ trans('sales::view.Customer represent') }}</th>
                                        <th class="sorting {{ Config::getDirClass('css.created_at') }}" data-order="css.created_at" data-dir="{{ Config::getDirOrder('css.created_at') }}" >{{ trans('sales::view.CSS.List.Date created') }}</th>
                                        <th class="sorting {{ Config::getDirClass('lastWork') }}" data-order="lastWork" data-dir="{{ Config::getDirOrder('lastWork') }}" >{{ trans('sales::view.Last working day') }}</th>
                                        <th class="sorting {{ Config::getDirClass('countViewCss') }}" data-order="countViewCss" data-dir="{{ Config::getDirOrder('countViewCss') }}"  >{{ trans('sales::view.Viewed') }}</th>
                                        <th class="sorting {{ Config::getDirClass('countMakeCss') }}" data-order="countMakeCss" data-dir="{{ Config::getDirOrder('countMakeCss') }}"  >{{ trans('sales::view.Marked') }}</th>
                                        <th >{{ trans('sales::view.Status') }}</th>
                                        <th class="sorting {{ Config::getDirClass('avg_point') }}" data-order="avg_point" data-dir="{{ Config::getDirOrder('avg_point') }}"  >{{ trans('sales::view.Average point') }}</th>
                                        <th class="sorting {{ Config::getDirClass('lang_id') }}" data-order="lang_id" data-dir="{{ Config::getDirOrder('lang_id') }}"  >{{ trans('sales::view.Language') }}</th>
                                        <th  rowspan="1" colspan="1" >{{ trans('sales::view.CSS.List.Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(count($css) > 0)
                                    @foreach($css as $item)
                                    <tr role="row" class="odd">
                                        <td rowspan="1" colspan="1" >{{ $item->id }}</td>
                                        <td rowspan="1" colspan="1" >{{ $item->project_type_name }}</td>
                                        <td rowspan="1" colspan="1" class="font-japan"><a href="{{ route('project::point.edit', ['id' => $item->projs_id]) }}" target="_blank">{{ $item->project_name }}</a></td>
                                        <td rowspan="1" colspan="1">{{ $item->team_leader_name }}</td>
                                        <td rowspan="1" colspan="1" >{{ $item->teamsName }}</td>
                                        <td rowspan="1" colspan="1" >{{ $item->sale_name }}</td>
                                        <td rowspan="1" colspan="1" class="font-japan">{{ $item->company_name }}</td>
                                        <td rowspan="1" colspan="1" class="font-japan">{{ $item->customer_name }}</td>
                                        <td rowspan="1" colspan="1" >{{ $item->created_date }}</td>
                                        <td rowspan="1" colspan="1">{{$item->lastWork_date}}</td>
                                        <td rowspan="1" colspan="1" class="text-align-center" >
                                            @if($item->countViewCss > 0)
                                                <a href="{{$item->hrefView}}">{{ $item->countViewCss }}</a>
                                            @else
                                                {{ $item->countViewCss }}
                                            @endif
                                        </td>
                                        <td rowspan="1" colspan="1" class="text-align-center" >
                                            @if($item->countMakeCss > 0)
                                                <a href="{{$item->hrefMake}}">{{ $item->countMakeCss }}</a>
                                            @else
                                                {{ $item->countMakeCss }}
                                            @endif
                                        </td>
                                        <td rowspan="1" colspan="1" class="text-align-center" >
                                            {{ CSS::getAnalyzeStatus($item->analyze_status) }}
                                        </td>
                                        <td rowspan="1" colspan="1" class="text-align-center">{{ View::formatNumber($item->avg_point, 2) }}</td>
                                        <td rowspan="1" colspan="1" class="text-align-center">
                                        @if ($item->lang_id == Css::ENG_LANG) 
                                            {{ trans('sales::view.English')}}
                                        @elseif ($item->lang_id == Css::VIE_LANG)
                                            {{ trans('sales::view.Vietnamese') }}
                                        @else
                                            {{trans('sales::view.Japanese')}}
                                        @endif
                                        </td>
                                        <td  rowspan="1" colspan="1" class="width-220" >
                                            <!-- Button copy to clipboard -->
                                            @if ($deviceIos)
                                            <span>
                                                <input placeholder="{{trans('sales::view.Url send customer')}}" class="form-control css-list-copy-url" value="{{ $item->url }}" />
                                            </span>
                                            @else
                                            <a class="btn-edit" title="{{trans('sales::view.CSS.List.Url send customer. Click to copy to clipboard')}}" href="javascript:void(0)" data-href="{{$item->url}}" onclick="copyToClipboard(this);"><i class="fa fa-copy"></i></a>
                                            @endif
                                            
                                            <!-- Button to CSS preview page -->
                                            <a class="btn-edit" title="{{trans('sales::view.CSS.List.Url preview')}}" href="{{$item->urlPreview}}"><i class="fa fa-eye"></i></a>
                                            
                                            <!-- Button view detail -->
                                            @if (Permission::getInstance()->isAllow('sales::css.cssDetail'))
                                            <a class="btn-edit" title="{{trans('sales::view.CSS.List.View detail')}}" href="{{route('sales::css.update', ['id' => $item->id])}}?type=detail"><i class="fa fa-info-circle"></i></a>
                                            @endif

                                            <!-- Button cancel CSS -->
                                            @if ($item->status != Css::STATUS_CANCEL)
                                                <span data-id="{{$item->id}}"><a data-id="{{$item->id}}" class="btn-edit button_cancel_css button_cancel_css_{{$item->id}}" title="{{trans('sales::view.CSS.List.Cancel Css')}}"><i class="fa fa-times"></i></a></span>
                                            @endif

                                            <!-- Button delete CSS -->
                                            @if (Permission::getInstance()->isAllow('sales::css.deleteItem'))
                                            <form action="{{route('sales::css.deleteItem')}}" method="post" class="form-inline">
                                                {{ csrf_field() }}
                                                <input type="hidden" name="id" value="{{$item->id}}">
                                                <button class="btn-delete delete-confirm" title="{{ trans('sales::view.Delete') }}">
                                                    <span><i class="fa fa-trash"></i></span>
                                                </button>
                                            </form>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                    @else
                                    <tr><td colspan="14" class="text-align-center"><h2>{{trans('sales::view.No result not found')}}</h2></td></tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>    
                        <div class="box-body">
                            @include('team::include.pager')
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-5">

                    </div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers" id="example2_paginate">
                            <?php echo $css->render(); ?>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
    <!-- /.col -->
</div>
<!-- /.row -->
<div class="modal modal-primary" id="modal-clipboard">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ trans('sales::view.Notification') }}</h4>
            </div>
            <div class="modal-body">
                <p>{{ trans('sales::view.Text notification copy clipboard')}}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline pull-right" data-dismiss="modal">{{ trans('sales::view.Close') }}</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<div class="modal fade modal-danger" id="modal-cancel-css" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ Lang::get('core::view.Confirm') }}</h4>
            </div>
            <div class="modal-body">
                <p class="text-default">{{ Lang::get('sales::view.Are you sure to cancel it?') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-close" data-dismiss="modal">{{ Lang::get('core::view.Close') }}</button>
                <button type="button" class="btn btn-outline btn-submit">{{ Lang::get('core::view.OK') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
@endsection
<!-- Styles -->
@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css">
<link href="{{ asset('sales/css/sales.css') }}" rel="stylesheet" type="text/css" >
@endsection

<!-- Script -->
@section('script')
<script src="{{ asset('sales/js/css/list.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script>
    var urlCancelCss = '{{ route('sales::cancel.css') }}';
    var token = '{!! csrf_token() !!}';
    $(document).ready(function () {
        $('input.date-picker').datepicker({
            format: 'yyyy-mm-dd'
        });

        $('.button_cancel_css').on('click', function() {
            $('#modal-cancel-css').modal('show');
            var $cssId = $(this).attr('data-id');
            cancel_item_meta($cssId);
        });

        function cancel_item_meta($cssId) {
            $(document).on('click', '#modal-cancel-css .btn-submit', function () {
                $('#modal-cancel-css').modal('hide');
                var html = '<div class="loader"></div>';
                $('#button_cancel_css_' + $cssId).html(html);
                $.ajax({
                    url: urlCancelCss,
                    type: 'post',
                    data: {
                        _token: token,
                        cssId: $cssId
                    },
                    success: function (data) {
                        $("span[data-id='" + $cssId + "']").remove();
                    },
                    error: function () {
                        alert('ajax fail to fetch data');
                    },
                });
            });
        }
    });
</script>
@endsection
