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
        @if(isset($dataReleaseNotes)  && count($dataReleaseNotes))
        @foreach($dataReleaseNotes as $item)
        <div class="box box-danger">
            <div class="box-body">
                <div class="head">
                    <a class="version" href="{{route('notes::notes.detail', ['id' => $item['id'] ])}}">{{ trans('notes::view.Version') }} {{ $item['version']}}</a>
                    <p class="infor">{{ trans('notes::view.Update at') }} : {{ substr($item['release_at'], 0, 10)}}</p>
                </div>
                <div class="allcontent">
                    <div class="content-box" id="content-{{ $item['id']}}" data-more-height="300">
                        {!! $item['content'] !!}
                    </div>			    
                </div>
            </div>
        </div>
        @endforeach

        @else
        <h2 class="no-result-grid">{{trans('notes::view.No results found')}}</h2>
        @endif
        {!! $dataReleaseNotes->links() !!}
    </div>
</div>

@endsection
@section('script')
<script type="text/javascript">
    var view_view = '{!!trans('notes::view.View less')!!}';
    var view_view_more = '{!!trans('notes::view.View more')!!}';
</script>
<script type="text/javascript" src="{{ URL::asset('assets/notes/js/notes.js') }}"></script>
@endsection
