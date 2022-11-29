<?php

use Rikkei\Core\View\CoreUrl;
?>
@extends('layouts.default')

@section('title')
{{ $titleHeadPage }}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" type="text/css" href="{{ CoreUrl::asset('assets/notes/css/notes.css') }}">
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="box box-danger">
            <div class="box-body">
                <div class="head">
                    <p class="version">{{ trans('notes::view.Version') }} {{$data['version']}}</p>
                    <p class="infor">{{ trans('notes::view.Update at') }} : {{ substr($data['release_at'], 0, 10)}}</p>
                </div>
                <div class="allcontent">
                    <div class="content-box">
                        {!! $data['content'] !!}
                    </div>
                </div>
            </div>	
        </div>
    </div>
</div>
@endsection