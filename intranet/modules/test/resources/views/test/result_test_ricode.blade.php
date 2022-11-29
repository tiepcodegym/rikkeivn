@extends('test::layouts.front')

@section('title', trans('test::test.view_results'))

@section('body_class', 'test-page result-page')

@section('content')

<h1 class="page-header single-title">
    <span class="name pull-left">{{ trans('test::test.test_result') }}</span>
</h1>

<div id='result-ricode'>
    <div class='col-md-4'>
        <div class='row'>
            <div class='col-md-12'>
                <div class='row'>
                <b>Test name:</b> {{(isset($ricodeTest) && isset($ricodeTest->title)) ? $ricodeTest->title : null}}
                </div>
            </div>
            <div class='col-md-12'>
                <div class='row'>
                    <b>Total correct answers:</b> {{(isset($ricodeTest) && isset($ricodeTest->total_correct_answers)) ? $ricodeTest->total_correct_answers : null}}
                </div>
            </div>
            <div class='col-md-12'>
                <div class='row'>
                    <b>Duration:</b> {{(isset($ricodeTest) && isset($ricodeTest->time_remaining)) ? $ricodeTest->time_remaining : null}}
                </div>
            </div>
        </div>
    </div>
    <div class='col-md-4'>
        <div class='row'>
            <div class='col-md-12'>
                <div class='row'>
                    <b>Full name:</b> {{(isset($candidate) && isset($candidate->fullname)) ? $candidate->fullname : null}}
                </div>
            </div>
            <div class='col-md-12'>
                <div class='row'>
                    <b>Email:</b> {{(isset($candidate) && isset($candidate->email)) ? $candidate->email : null}}
                </div>
            </div>
        </div>
    </div>
    <div class='col-md-4'>
        <div class='row'>
            <div class='col-md-12'>
                <div class='row'>
                    <b>Time:</b> {{(isset($ricodeTest) && isset($ricodeTest->start_time)) ? $ricodeTest->start_time : null}}
                </div>
            </div>
            <div class='col-md-12'>
                <div class='row'>
                    <b>Penalty point:</b> {{(isset($ricodeTest) && isset($ricodeTest->penalty_point)) ? $ricodeTest->penalty_point : null}}
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('footer')
<div class="main-footer">
    <div class="pull-right hidden-xs">
        <b>Version</b> {!! isset($version) ? $version : null !!}
    </div>
    <strong>Copyright &copy; {!! isset($currentYear) ? $currentYear : null !!} <a class="link" href="http://rikkeisoft.com/">RikkeiSoft</a>.</strong> All rights reserved.
</div><!-- /.container -->
@stop