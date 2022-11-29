<?php
use Rikkei\Resource\Model\Candidate;

$candidateAs = Candidate::getCandidate($employeeModelItem->id);
?>
@extends('layouts.default')
@section('title')
{{ trans('team::view.Profile of :employeeName', ['employeeName' => $employeeModelItem->name]) }}
@endsection

@section('css')
<link rel="stylesheet" href="{{ URL::asset('team/css/style.css') }}" />
@endsection

@section('content')
@if (isset($candidateAs) && $candidateAs->id)
<div class="row">
    <a href="{!!route('resource::candidate.detail', ['id'=>$candidateAs->id])!!}" target="_blank" style="float: right; padding: 0px 15px 0px 10px;">Candidate detail</a><i class="fa fa-yelp" style="float: right;"></i>
</div>
@endif
<div class="row member-profile">
    <div class="col-lg-2 col-md-3">
        @include('team::member.left_menu',['active' => $tabType])
    </div>
    <div class="col-lg-10 col-md-9 tab-content">
        <div class="box box-info tab-pane active">
            <div class="box-header with-border">
                <h2 class="box-title">{!!$tabTitle!!}</h2>
                @if (isset($helpLink) && $helpLink)
                <a href="{!!$helpLink!!}" target="_blank" title="Help">
                    <i class="fa fa-fw fa-question-circle" style="font-size: 18px;"></i>
                </a>
                @endif
            </div>
            <div class="box-body">
               @yield('content_profile')
            </div>
        </div>
    </div>
</div>
@endsection
