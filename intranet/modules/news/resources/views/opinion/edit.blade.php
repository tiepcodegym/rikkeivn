<?php
use Rikkei\Team\View\Config as Config;
use Rikkei\Core\View\View;
use Rikkei\Core\View\Form;
use Carbon\Carbon;
use Rikkei\Core\View\CoreUrl;

?>

@extends('layouts.default')

@section('title')
    {{ trans('news::view.List opinion') }}
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css"/>
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_news/css/opinion.css') }}"/>
@endsection

@section('content')

    <div class="row">
        <div class="col-sm-12">
            <div class="box box-info">
                <div class="row opinion-wrapper">
                    <div class="col-md-6 col-md-offset-3">
                        <div class="col-sm-6  opinion-label">{{trans('news::view.Employee Name')}}:</div>
                        <div class="col-sm-6  opinion-value">{{$model->employee->name}} ({{$model->employee->email}})</div>
                        <div class="col-sm-6  opinion-label">{{trans('news::view.Created At')}}:</div>
                        <div class="col-sm-6  opinion-value">{{\Carbon\Carbon::parse($model->created_at)->format('d/m/Y') }}</div>
                        <div class="col-sm-6  opinion-label">{{trans('news::view.Content')}}:</div>
                        <div class="col-sm-6  opinion-value">{{$model->content}}</div>
                        <div class="col-sm-6  opinion-label">{{trans('news::view.Status')}}:</div>
                        <div class="col-sm-6  opinion-value"><span class="{{$listStatusLabel[$model->status]}}">{{ trans($listStatus[$model->status]) }}</span></div>
                        <div class="col-sm-6 opinion-button item-left ">
                            <form action="{{ route('news::opinions.update', ['id' => $model->id]) }}" method="post" class="form-inline">
                                {!! csrf_field() !!}
                                @if ($model->status == \Rikkei\News\Model\Opinion::STATUS_NEW)
                                    <input type="hidden" name="status" value="{{\Rikkei\News\Model\Opinion::STATUS_SEEN}}">
                                    <button type="submit" class="btn btn-success">
                                        {{trans('news::view.Btn Mark seen')}}
                                    </button>
                                @else
                                    <input type="hidden" name="status" value="{{\Rikkei\News\Model\Opinion::STATUS_NEW}}">
                                    <button type="submit" class="btn btn-warning">
                                        {{trans('news::view.Btn Mark new')}}
                                    </button>
                                @endif
                            </form>
                        </div>
                        <div class="col-sm-6 opinion-button item-right">
                            <form action="{{ route('news::opinions.delete', ['id' => $model->id]) }}" method="post" class="form-inline">
                                {!! csrf_field() !!}
                                {!! method_field('delete') !!}
                                <input type="hidden" name="id" value="{{ $model->id }}" />
                                <button href="" class="btn-delete delete-confirm" title="{{ trans('manage_time::view.Delete') }}">
                                    {{trans('core::view.Remove')}}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.19.0/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.13/moment-timezone-with-data.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>

    <script type="text/javascript">
    </script>
@endsection

