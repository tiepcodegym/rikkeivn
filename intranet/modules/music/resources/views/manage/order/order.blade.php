<?php 
use Rikkei\Core\View\CoreUrl;
header("Cache-Control: no-store, must-revalidate, max-age=0");
header("Pragma: no-cache");
?>
@extends('layouts.default')

@section('title')
    {{$titleHeadPage}}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" type="text/css" href="{{CoreUrl::asset('asset_music/css/order_list.css') }}">
@endsection

@section('content')
<?php
use Rikkei\Team\View\Config as TeamConfig;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Music\View\ViewMusic;
?>
<div class="row">
    <div class="col-sm-12">
        @if(Session::has('delSuccess'))
            <div class="alert alert-success">
                <ul>
                    <li>
                        {{ Session::get('delSuccess') }}
                    </li>
                </ul>
            </div>
        @endif
        @if(Session::has('error'))
            <div class="alert alert-warning not-found">
                <ul>
                    <li>
                        {{ Session::get('error') }}
                    </li>
                </ul>
            </div>
        @endif
        <div id="error">
        </div>
        <div class="box box-info">
            <div class="box-body">
                <div class="pull-left">   
                    <div class="btn_actions">
                        @if(!$collectionModel->isEmpty())
                        <a href="{{URL::route('music::manage.order.delMany')}}" action="delete" class="m_action_btn btn-delete delete-confirm" token="{{csrf_token()}}">
                            <i class="fa fa-trash"></i> <span class="">{{trans('music::view.Delete btn')}}</span>
                        </a>
                        @endif
                    </div>
                </div>

                @include('team::include.filter', ['domainTrans' => 'music'])
            </div>
            <div class="table-responsive">
                <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                    <thead>
                        <tr>
                        @if(!$collectionModel->isEmpty())
                            <th style="width: 30">
                                    <input type="checkbox" name="massdel" class="check_all" style="vertical-align: text-top; margin-top: 3px;" autocomplete="off">
                            </th>
                        @endif
                            <th class="col-id width-10" style="width: 20px;">{{ trans('music::view.No') }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('orders.name') }} col-name" data-order="name" data-dir="{{ TeamConfig::getDirOrder('name') }}">{{ trans('music::view.Order name') }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('link') }} col-link" data-order="link" data-dir="{{ TeamConfig::getDirOrder('link') }}">{{ trans('music::view.Link') }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('sender') }} col-sender" data-order="sender" data-dir="{{ TeamConfig::getDirOrder('sender') }}">{{ trans('music::view.Sender') }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('receiver') }} col-receiver" data-order="receiver" data-dir="{{ TeamConfig::getDirOrder('receiver') }}">{{ trans('music::view.Receiver') }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('message') }} col-message" data-order="message" data-dir="{{ TeamConfig::getDirOrder('message') }}">{{ trans('music::view.Message order') }}</th>
                            <!-- <th class="sorting {{ TeamConfig::getDirClass('total_vote') }} col-total_vote" data-order="total_vote" data-dir="{{ TeamConfig::getDirOrder('total_vote') }}">{{ trans('music::view.Number of vote order') }}</th> -->
                            <th class="sorting {{ TeamConfig::getDirClass('created_at') }} col-created_at" data-order="created_at" data-dir="{{ TeamConfig::getDirOrder('created_at') }}">{{ trans('music::view.Date of oder') }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('office_name') }} col-office_name" data-order="office_name" data-dir="{{ TeamConfig::getDirOrder('office_name') }}">{{ trans('music::view.Office') }}</th>
                            <th >&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="filter-input-grid">
                        @if(!$collectionModel->isEmpty())
                            <td>&nbsp;</td>
                        @endif
                            <td>&nbsp;</td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[music_orders.name]" value="{{ CoreForm::getFilterData('music_orders.name') }}" placeholder="{{ trans('music::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                       <input type="text" name="filter[music_orders.link]" value="{{ CoreForm::getFilterData('music_orders.link') }}" placeholder="{{ trans('music::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[music_orders.sender]" value="{{ CoreForm::getFilterData('music_orders.sender') }}" placeholder="{{ trans('music::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[music_orders.receiver]" value="{{ CoreForm::getFilterData('music_orders.receiver') }}" placeholder="{{ trans('music::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[music_orders.message]" value="{{ CoreForm::getFilterData('music_orders.message') }}" placeholder="{{ trans('music::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <!-- <td>
                                <div class="row">
                                    <div class="col-md-12">
                                       <input type="number" min="0" name="filter[spec][total_vote]" value="{{ CoreForm::getFilterData('spec','total_vote') }}" placeholder="{{ trans('music::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td> -->
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[music_orders.created_at]" value="{{ CoreForm::getFilterData("music_orders.created_at") }}" placeholder="{{ trans('music::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[music_offices.name]" value="{{ CoreForm::getFilterData("music_offices.name") }}" placeholder="{{ trans('music::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>&nbsp;</td>
                        </tr>
                        @if(isset($collectionModel) && count($collectionModel))
                            <?php $i = CoreView::getNoStartGrid($collectionModel); ?>
                            @foreach($collectionModel as $item)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="check_items[]" value="{{$item->id}}" autocomplete="off">
                                    </td>
                                    <td>{{ $i }}</td>
                                    @if ($item->name)
                                        <td class="mini">
                                            {{$item->name}}
                                        </td>
                                    @else
                                        <td>&nbsp;</td>
                                    @endif
                                    @if ($item->link)
                                        <td class="mini"> 
                                            <a href="{{$item->link}}" target="_blank">{{$item->link}}</a>
                                        </td>
                                    @else
                                        <td>&nbsp;</td>
                                    @endif
                                    @if ($item->sender)
                                        <td class="mini">
                                            {{$item->sender}}
                                        </td>
                                    @else
                                        <td>&nbsp;</td>
                                    @endif
                                    @if ($item->receiver)
                                        <td class="mini">
                                            {{$item->receiver}}
                                        </td>
                                    @else
                                        <td>&nbsp;</td>
                                    @endif
                                    @if ($item->message)
                                        <td style="max-width: 506px">
                                            @if (str_word_count($item->message) > 20)
                                                {{ViewMusic::shortMess($item->message,20)}}
                                                <br>
                                                <button class="btn-link" style="padding-left: 1px; outline: none; border: none;" onclick="showMess({{$item->id}});">{{trans('music::view.Oder view more')}}</button>
                                            @else
                                                {{$item->message}}
                                            @endif
                                            <input type="hidden" id="mess{{$item->id}}" value="{{$item->message}}">
                                        </td>
                                    @else
                                        <td>&nbsp;</td>
                                    @endif
                                    <td>{{$item->created_at}}</td>
                                    <td class="mini">{{$item->office_name}}</td>
                                    <td class="text-center" style="width: 30px">
                                        <form action="{{URL::route('music::manage.order.del',$item->id)}}" method="POST">
                                            {{ csrf_field() }}
                                            <button class="btn-delete delete-confirm" type="submit"><i class="fa fa-trash-o" aria-hidden="true"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                <?php $i++; ?>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="text-center">
                                    <h2 class="no-result-grid">{{ trans('music::view.No results found') }}</h2>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="box-body">
                @include('team::include.pager')
            </div>
            <!-- modal show full message -->
            <div id="showMess" class="modal fade" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">{{ trans('music::view.Message order') }}</h4>
                      </div>
                      <div class="modal-body">
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                      </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="{{ CoreUrl::asset('asset_music/js/order_mn.js') }}"></script>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        selectSearchReload();
    });
</script>
@endsection

