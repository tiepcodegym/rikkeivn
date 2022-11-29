<?php
use Rikkei\Core\View\CoreUrl;
?>
@extends('layouts.full-screen')
@section('title', 'Project production dashboard')

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/bxslider/4.2.12/jquery.bxslider.css">
<link rel="stylesheet" href="{!!CoreUrl::asset('assets/statistic/css/statistic.css')!!}" />
@endsection

@section('content')
<div class="jumbotron">
    @include('messages.errors')
            <div class="container-fluid">
                <section class="content">
                    <div class="login-wrapper">
                        <div class="login-action">
                            <div class="col-sm-6 col-sm-offset-3">
                                <div class="form-group col-sm-offset-2">
                                    <form method="post" action="{!!route('statistic::project.activity.slide.pass.post')!!}" autocomplete="off">
                                        {!! csrf_field() !!}
                                        <span class="col-sm-7">
                                            <input type="password" class="form-control" id="password" name="password" placeholder="{{trans('statistic::view.Password')}}">
                                        </span>
                                        <div class="col-sm-1">
                                            <button type="submit" class="btn btn-primary" id="typing-password">{{trans('statistic::view.Submit')}}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div><!-- /.login-wrapper -->
                </section>
                <!-- /.content -->
            </div>
        </div>
@endsection
