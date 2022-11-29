<?php
use Rikkei\Team\View\Config as Config;
use Rikkei\Core\View\View;
use Rikkei\Core\View\Form;
use Carbon\Carbon;
use Rikkei\Core\View\CoreUrl;

?>

@extends('layouts.default')

@section('title')
    {{ trans('news::view.Create Poster') }}
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css"/>
    <link rel="stylesheet" href="{{ URL::asset('asset_news/css/news.css') }}" />
    <style>
        .flash-message {
            display: none;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-info">
                <div class="box-body">
                    <form id="form-poster-edit" method="post" action="{{ URL::route('news::posters.store') }}"
                          class="has-valid" autocomplete="off">
                        {!! csrf_field() !!}
                        @include('news::poster._form')
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection



