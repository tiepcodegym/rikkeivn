@extends('layouts.default')

@section('title', trans('project::me.Monthly evaluation attributes'))

@section('css')
<?php
use Rikkei\Core\View\CoreUrl;
?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="{{ CoreUrl::asset('project/css/edit.css') }}" />
<link rel="stylesheet" href="{{ CoreUrl::asset('project/css/me_style.css') }}" />
@endsection

@section('content')

<div class="box box-info">
    <div class="box-body">
<!--        <div class="row">
            <div class="col-sm-6">
                <a href="{{route('project::eval.attr.create')}}" class="btn-add"><i class="fa fa-plus"></i> {{trans('project::me.Create')}}</a>
            </div>
        </div>
        <div class="clearfix"></div>-->
    </div>
    @if (!$collectionModel->isEmpty())
    <div class="table-responsive">
        <table class="table me_attr_table table-striped table-bordered table-hover table-grid-data">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>{{trans('project::me.Label')}}</th>
                    <th>{{trans('project::me.Name')}}</th>
                    <th width="90">{{trans('me::view.Weight')}} (%)</th>
                    <th>{{trans('project::me.Order')}}</th>
                    <th width="80">{{trans('project::me.Min value')}}</th>
                    <th width="80">{{trans('project::me.Max value')}}</th>
                    <th>{{trans('project::me.Step')}}</th>
                    <th>{{trans('project::me.Group')}}</th>
                    <th>{{trans('project::me.Default')}}</th>
                    <th>{{trans('project::me.Fill')}}</th>
                    <th width="100"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($collectionModel as $item)
                <tr>
                    <td>{{$item->id}}</td>
                    <td>{{$item->label}}</td>
                    <td>{{$item->name}}</td>
                    <td>{{$item->weight}}</td>
                    <td>{{$item->order}}</td>
                    <td>{{$item->range_min}}</td>
                    <td>{{$item->range_max}}</td>
                    <td>{{$item->range_step}}</td>
                    <td>{{$item->group_label}}</td>
                    <td>{{$item->default ? $item->default : 'NULL'}}</td>
                    <td><input type="checkbox" {{$item->can_fill ? 'checked' : ''}} disabled=""></td>
                    <td>
                        <a href="{{route('project::eval.attr.edit', ['id' => $item->id])}}" class="btn-edit"><i class="fa fa-edit"></i></a>
                        <a href="{{route('project::eval.attr.destroy', ['id' => $item->id])}}" class="btn-delete"><i class="fa fa-trash"></i></a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="cleafix"></div>
    <div class="box-body">
        @include('team::include.pager')
    </div>
    @else
    <div class="box-body">{{trans('project::me.No result')}}</div>
    @endif
</div>

@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script type="text/javascript" src="{{ asset('project/js/script.js') }}"></script>
<script type="text/javascript">
jQuery(document).ready(function ($) {
    selectSearchReload();
});
</script>
@endsection



