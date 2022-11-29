@extends('layouts.default')

@section('title', trans('test_old::test.test'))

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
@endsection

@section('content')

{!! show_messes() !!}

<style>
    table{width: 100%;}
    .link{width: 20%; white-space: pre-line; word-wrap: break-word;}
</style>

<div class="box box-primary">

    <div class="box-body">
        <div class="table_nav">
            <div class="pull-left">   
                <div class="btn_actions">
                    <a href="{{route('test_old::admin.test.create')}}" class="create-btn btn-add" data-toggle="tooltip" title="{{trans('test_old::test.add_new')}}" data-placement="top">
                        <i class="fa fa-plus"></i> <span class="">{{trans('test_old::test.add_new')}}</span>
                    </a>
                    <a href="{{route('test_old::admin.test.m_action')}}" action="delete" class="m_action_btn btn-delete delete-confirm" data-toggle="tooltip" title="{{trans('test_old::test.delete')}}" data-placement="top">
                        <i class="fa fa-trash"></i> <span class="">{{trans('test_old::test.delete')}}</span>
                    </a>
                </div>
            </div>
            
            @include('team::include.filter')
            
            <div class="clearfix"></div>
        </div>
    </div>
    
    

    @if(!$collectionModel->isEmpty())
    <div class="table-responsive">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th width="30"><input type="checkbox" name="massdel" class="check_all"/></th>
                    <th>#</th>
                    <th>{{trans('test_old::test.name')}}</th>
                    <th class="link">{{trans('test_old::test.link')}}</th>
                    <th>{{trans('test_old::test.time')}} ({{trans('test_old::test.minute')}})</th>
                    <th>{{trans('test_old::test.teams')}}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($collectionModel as $item)
                <tr>
                    <td><input type="checkbox" name="check_items[]" class="check_item" value="{{$item->id}}"></td>
                    <td>{{$item->id}}</td>
                    <td>{{$item->name}}</td>
                    <td class="link">{{$item->link}}</td>
                    <td>{{$item->time}}</td>
                    <td>{{$item->catName()}}</td>
                    <td>
                        <a href="{{route('test_old::admin.test.edit', ['id' => $item->id])}}" title="{{trans('test_old::test.edit')}}" class="btn-sm btn-edit"><i class="fa fa-edit"></i></a>
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
    <div class="box-body">{{trans('test_old::test.no_item')}}</div>
    @endif

</div>

@stop

@section('css')
<link rel="stylesheet" href="{{ URL::asset('tests_old/ad_src/main.css') }}">
@stop
@section('script')
<script>
    var _token = "{{csrf_token()}}";
    var textNoItem = '<?php echo trans('test::test.no_item'); ?>';
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="{{ URL::asset('tests_old/ad_src/main.js') }}"></script>
@stop

