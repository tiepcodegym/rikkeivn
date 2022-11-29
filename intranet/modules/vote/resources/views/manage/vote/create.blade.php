<?php
use Rikkei\Core\View\CoreUrl;
?>

@extends('layouts.default')

@section('title', trans('vote::view.create_vote'))

@section('css')

@include('vote::include.css')

@stop

@section('content')

<div class="box box-info create-vote-page">
    <div class="box-header with-border">
        {{ trans('vote::view.vote_info') }}
    </div>
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="box-body">
                {!! Form::open(['method' => 'post', 'route' => 'vote::manage.vote.store', 'id' => 'vote_form']) !!}
                
                @include('vote::manage.include.basic_info')
                
                <div class="col-md-12">
                    <div class="form-group text-center">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ trans('vote::view.create_vote') }}</button>
                    </div>
                </div>

                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>

@stop

@section('script')

<script src="{{ CoreUrl::asset('lib/ckeditor/ckeditor.js') }}"></script>
@include('vote::include.script')

@stop

