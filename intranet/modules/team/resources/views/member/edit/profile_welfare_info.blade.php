<?php
use Rikkei\Team\View\Config as TeamConfig;
use Rikkei\Team\View\Permission;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Welfare\Model\Event;
use Rikkei\Core\View\View as CoreView;
use Carbon\Carbon;

?>
@extends('layouts.default')
@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
@endsection
@section('title')
    {{ trans('welfare::view.Welfare information') }}
@endsection
@section('content')

    <div class="row">
        <div class="col-sm-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <div class="row">
                        <div class="col-md-6">
                        </div>
                        <div class="col-md-6">
                            <div class="pull-right">
                                @include('team::include.filter')
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-body no-padding">
                    <div class="table-responsive">
                        <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                            <thead>
                            <tr>
                                <th class="col-id width-10">{{trans('welfare::view.No')}}</th>
                                <th class="sorting {{ TeamConfig::getDirClass('name') }} col-name" data-order="name"
                                    data-dir="{{ TeamConfig::getDirOrder('name') }}">{{trans('welfare::view.Name event')}}</th>
                                <th class="sorting {{ TeamConfig::getDirClass('groupName') }} col-name"
                                    data-order="groupName"
                                    data-dir="{{ TeamConfig::getDirOrder('groupName') }}">{{trans('welfare::view.Group event')}}</th>
                                <th class="sorting {{ TeamConfig::getDirClass('address') }} col-name" data-order="address"
                                    data-dir="{{ TeamConfig::getDirOrder('address') }}">{{trans('welfare::view.Address')}}</th>
                                <th class="sorting {{ TeamConfig::getDirClass('start_at_exec') }} col-name"
                                    data-order="start_at_exec"
                                    data-dir="{{ TeamConfig::getDirOrder('start_at_exec') }}">{{trans('welfare::view.Start_at_exec')}}</th>
                                <th class="sorting {{ TeamConfig::getDirClass('end_at_exec') }} col-name"
                                    data-order="end_at_exec"
                                    data-dir="{{ TeamConfig::getDirOrder('end_at_exec') }}">{{trans('welfare::view.End_at_exec')}}</th>
                                <th class="sorting {{ TeamConfig::getDirClass('end_at_register') }} col-name"
                                    data-order="end_at_register"
                                    data-dir="{{ TeamConfig::getDirOrder('end_at_register') }}">{{trans('welfare::view.End_at_register')}}</th>
                                <th>{{trans('welfare::view.Is register')}}</th>
                                <th class=""></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="filter-input-grid">
                                <td>&nbsp;</td>
                                <td>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" name="filter[welfares.name]"
                                                   value="{{ CoreForm::getFilterData("welfares.name") }}"
                                                   placeholder="{{ trans('team::view.Search') }}"
                                                   class="filter-grid form-control"/>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" name="filter[welfare_groups.name]"
                                                   value="{{ CoreForm::getFilterData("welfare_groups.name") }}"
                                                   placeholder="{{ trans('team::view.Search') }}"
                                                   class="filter-grid form-control"/>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" name="filter[welfares.address]"
                                                   value="{{ CoreForm::getFilterData("welfares.address") }}"
                                                   placeholder="{{ trans('team::view.Search') }}"
                                                   class="filter-grid form-control"/>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" name="filter[welfares.start_at_exec]"
                                                   value="{{ CoreForm::getFilterData("welfares.start_at_exec") }}"
                                                   placeholder="{{ trans('team::view.Search') }}"
                                                   class="filter-grid form-control"/>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" name="filter[welfares.end_at_exec]"
                                                   value="{{ CoreForm::getFilterData("welfares.end_at_exec") }}"
                                                   placeholder="{{ trans('team::view.Search') }}"
                                                   class="filter-grid form-control"/>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" name="filter[welfares.end_at_register]"
                                                   value="{{ CoreForm::getFilterData("welfares.end_at_register") }}"
                                                   placeholder="{{ trans('team::view.Search') }}"
                                                   class="filter-grid form-control"/>
                                        </div>
                                    </div>
                                </td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                            @if(isset($collectionModel) && count($collectionModel))
                                <?php $i = CoreView::getNoStartGrid($collectionModel);?>
                                @foreach($collectionModel as $item)
                                    <tr id="{{ $item->id}}"
                                        href="{{ route('welfare::welfare.event.detailpost', ['id' => $item->id ]) }}">
                                        <td id="item_id" hidden="true"></td>
                                        <td class="detail_item">{{ $i }}</td>
                                        <td class="detail_item">
                                            {{ $item->name }}
                                        </td>
                                        <td class="detail_item">{{ $item->groupName}}</td>
                                        <td class="detail_item">{{ $item->address}}</td>
                                        <td class="detail_item wel-time">{{ Carbon::parse($item->start_at_exec)->format('d/m/Y') }}</td>
                                        <td class="detail_item wel-time">{{ Carbon::parse($item->end_at_exec)->format('d/m/Y') }}</td>
                                        <td class="detail_item wel-time">@if($item->end_at_register!=0){{ Carbon::parse($item->end_at_register)->format('d/m/Y') }} @endif</td>
                                        <td><input data-id="{{$item->id}}" id="view-list-is-register-online" type="checkbox" class="format-checkox" @if(date('Y-m-d H:i:s') > $item->end_at_register) disabled @endif @if($item->is_register_online) checked="checked" @endif></td>
                                        <td class="row">
                                            <div class="col-md-6" style="padding-left: 0px;">
                                                <a href="{{ route('welfare::welfare.confirm.welfare', ['id' => $item->id ]) }}"
                                                   class="btn-edit" title="{!! trans('welfare::view.Register join event') !!}" target="_blank"><i
                                                            class="fa fa-info-circle"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php $i++; ?>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="11" class="text-center">
                                        <h2 class="no-result-grid">{{ trans('welfare::view.No results found') }}</h2>
                                    </td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                        <!-- /.table -->
                    </div>
                </div>
                <div class="box-footer">
                    @include('team::include.pager')
                </div>
            </div>
        </div>

    </div>
<div class="modal fade row modal-detail-welfare" id="myModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #428bca; color: #fff">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-info-circle"></i> {{trans('welfare::view.Common info')}}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <div class="box box-solid">
                                <div class="table-responsive">
                                    <table id="moal-table1" class="col-md-12  col-sm-12 table table-striped table-bordered table-grid-data">
                                        <tr name="name">
                                            <td class="col-md-6">{{ trans('welfare::view.Register join event') }}</td>
                                        </tr>
                                        <tr name="groupName">
                                            <td>{{trans('welfare::view.Group event')}}</td>
                                        </tr>
                                        <tr name="namePur">
                                            <td>{{trans('welfare::view.Purpose')}}</td>
                                        </tr>
                                        <tr name="address">
                                            <td>{{trans('welfare::view.Address')}}</td>
                                        </tr>
                                        <tr name="nameOrg">
                                            <td>{{trans('welfare::view.Organizer')}}</td>
                                        </tr>
<!--                                        <tr name="namePart">
                                            <td>{{trans('welfare::view.Partners')}}</td>
                                        </tr>-->
<!--                                        <tr name="status">
                                            <td>{{trans('welfare::view.Status')}}</td>
                                        </tr>-->
                                        <tr name="start_at_exec">
                                            <td>{{trans('welfare::view.Start_at_exec')}}</td>
                                        </tr>
                                        <tr name="end_at_exec">
                                            <td>{{trans('welfare::view.End_at_exec')}}</td>
                                        </tr>
                                        <tr name="convert_end_at_register">
                                            <td>{{trans('welfare::view.End_at_register')}}</td>
                                        </tr>
                                    </table>
                                </div>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
@endsection
@section('script')
<script>
    $(document).ready(function() {
        $('.detail_item').on('click', function () {
            var URL = this.parentElement.getAttribute('href');
            var id = this.parentElement.getAttribute('id');
            $.ajax({
                method: "GET",
                url: URL,
                data: {id: id},
                success: function (data) {
                    for (var key in data) {
                        var html = '<td  class="number-format">' + data[key] + ' </td>';
                        var arr = ['fee_total_actual', 'fee_total','empl_trial_fee', 'empl_trial_company_fee', 'empl_offical_company_fee'];
                        if (typeof $('[name="' + key + '"]') && arr.includes(key)) {
                            html = html + '<span> VND</span>';
                        }
                        $('[name="' + key + '"] td').not(':first').remove();
                        $('[name="' + key + '"]').append(html);
                    }
                    $("#myModal").modal("show"); 
                }
            });
        });
    });
</script>
@endsection