<?php
use Rikkei\Core\View\CoreUrl;
?>
@extends('layouts.default')

@section('title')
{{ trans('notes::view.List notes') }}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">
<link rel="stylesheet" type="text/css" href="{{ CoreUrl::asset('assets/notes/css/notes.css') }}">
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="box-body button-create">
                <a href="{{route('notes::manage.notes.create')}}" class="btn btn-primary"><i class="fa fa-plus" ></i> {{ trans('notes::view.Create notes') }}</a>
            </div>
            <div class="table-responsive">
                
                <table id="table_id" class="display">
                    <thead>
                    <tr>
                            <th class="no-sort">{{ trans('notes::view.No.') }}</th>
                            <th>{{ trans('notes::view.Version') }}</th>
                            <th>{{ trans('notes::view.Created by') }}</th>
                            <th>{{ trans('notes::view.Release at') }}</th>
                            <th>{{ trans('notes::view.Status') }}</th>
                            <th class="no-sort">&nbsp;</th>
                        </tr>
                    </thead>
                    <!-- tbody -->
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.js"></script>
    <script type="text/javascript">
        var manage_notes_anyData = '{{ route('notes::manage.notes.anyData') }}';
        var manage_notes_edit = '{{ route('notes::manage.notes.edit', ['id' => '' ]) }}';
        var notes_view_Edit = '{{ trans('notes::view.Edit') }}';
    </script>
    <script type="text/javascript" src="{{ URL::asset('assets/notes/js/notes.js') }}"></script>
@endsection
