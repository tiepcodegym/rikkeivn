@extends('layouts.default')

@section('title')
{{ trans('welfare::view.List Relation Name') }}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<style>
    .table thead tr th{
        background-color: #3c8dbc;
        color: #fff;
    }
</style>
@endsection
<?php
use Rikkei\Core\View\CoreUrl;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Cookie\CookieJar;

$stt = 1;
?>
@section('content')
@if (Cookie::get('msgdelete') !== null)
<div class="error-list-relation">
    <div class="alert alert-success">
        <ul>
            <li>{{ Cookie::get('msgdelete') }}</li>
        </ul>
    </div>
</div>
@endif
@if (Cookie::get('msgwarring') !== null)
<div class="error-list-relation">
    <div class="alert alert-danger">
        <ul>
            <li>{{ Cookie::get('msgwarring') }}</li>
        </ul>
    </div>
</div>
@endif
<div class="row">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="box-body">
                <div class="col-md-6">
                    <a href="{{ URL::route('welfare::welfare.relation.create') }}">
                        <button id="add-new-group" type="button" class="btn-add add-college" data-toggle="modal"
                                data-placement="bottom" data-target="" title="thêm mới"
                                data-modal="true">
                            <i class="fa fa-plus"></i>
                        </button>
                    </a>
                </div>
                <div class="col-md-6"></div>
            </div>
            <div class="table-responsive">
                <div class="col-md-6">
                    <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                        <thead>
                            <tr>
                                <th>{{ trans('welfare::view.No') }}</th>
                                <th>{{ trans('welfare::view.Name Relations') }}</th>
                                <th>&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($relations as $item)
                            <tr>
                                <td>{{ $stt++ }}</td>
                                <td>{{ $item->name }}</td>
                                <td>
                                    <a title="{{ trans('team::view.Edit') }}" class="btn-edit" href="{{ route('welfare::welfare.relation.edit', $item->id) }}">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <form action="{{ route('welfare::welfare.relation.delete') }}" method="post" class="form-inline">
                                        {!! csrf_field() !!}
                                        {!! method_field('delete') !!}
                                        <input type="hidden" name="id" value="{{ $item->id }}" />
                                        <button href="" class="btn-delete delete-confirm" disabled>
                                            <span><i class="fa fa-trash"></i></span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
    Cookie::queue(Cookie::forget('msgdelete'));
    Cookie::queue(Cookie::forget('msgwarring'));
?>
@endsection

@section('script')
<script src="{{ CoreUrl::asset('asset_welfare/js/relation.js') }}"></script>
@endsection
