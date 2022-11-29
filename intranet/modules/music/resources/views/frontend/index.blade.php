<?php use Rikkei\Core\View\CoreUrl; ?>
@extends('music::layouts.music')

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="{{ CoreUrl::asset('asset_music/css/music.css') }}" />
<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400&amp;subset=vietnamese" rel="stylesheet">
@endsection
@section('content')
<div class="row">
    <div class="col-md-4">
        @include('music::include.music_order_form')
    </div>
    <div class="col-md-8 list-content">
        @include('music::include.music_order_list')
    </div>
</div>

@endsection
@section('script')
<script>
    var urlInvalid = '{{ trans('music::message.Please enter a valid URL.') }}';
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="{{ CoreUrl::asset('lib/js/jquery.validate.min.js') }}"></script>
<script src="{{ CoreUrl::asset('lib/ckeditor/ckeditor.js') }}"></script>
<script src="{{ CoreUrl::asset('asset_music/js/music_frontend.js') }}"></script>
@endsection
