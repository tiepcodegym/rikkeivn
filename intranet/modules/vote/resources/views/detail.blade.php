<?php
use Rikkei\Core\View\CoreUrl;
use Rikkei\Vote\View\VoteConst;

$formatDay = trans('vote::view.format_day');
?>
@extends('layouts.default')

@section('title', $vote->title)

@section('css')
<link rel="stylesheet" href="{{ CoreUrl::asset('vote/css/front.css') }}">
@endsection

@section('content')

<div class="box box-info">
    <div class="box-header with-border">
        <div class="vote-info mgb-10">
            <div class="row">
                @if ($vote->nominate_start_at || $vote->nominate_end_at)
                <div class="col-sm-6">
                    @if ($vote->nominate_start_at)
                    <div><strong>{{ trans('vote::view.nominate_start_at') }}</strong>: <span>{{ $vote->nominate_start_at->format('H\hi '. $formatDay .' d/m/Y') }}</span></div>
                    @endif
                    @if ($vote->nominate_end_at)
                    <div><strong>{{ trans('vote::view.nominate_end_at') }}</strong>: <span>{{ $vote->nominate_end_at->format('H\hi '. $formatDay .' d/m/Y') }}</span></div>
                    @endif
                </div>
                @endif
                <div class="col-sm-6">
                    <div><strong>{{ trans('vote::view.vote_start_at') }}</strong>: <span>{{ $vote->vote_start_at->format('H\hi '. $formatDay .' d/m/Y') }}</span></div>
                    <div><strong>{{ trans('vote::view.vote_end_at') }}</strong>: <span>{{ $vote->vote_end_at->format('H\hi '. $formatDay .' d/m/Y') }}</span></div>
                </div>
            </div>
        </div>
        
        <div class="vote-desc show-full">
            <strong>{{ trans('vote::view.content') }}:</strong>
            <div class="vote-content">
                {!! $vote->content !!}
            </div>
        </div>
    </div>
</div>

@endsection

