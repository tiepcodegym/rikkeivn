<?php
use Rikkei\Test\View\ViewTest;
use Rikkei\Test\Models\Test;
?>

@extends('test::layouts.front')

@section('title', $test->name)

@section('content')

<h1 class="page-title text-center">{{ trans('test::test.test_info') }}</h1>
<br />

{!! Form::open(['method' => 'post', 'route' => ['test::view', $test->url_code], 'id' => 'submit_test_form']) !!}

<div class="row">
    <div class="col-sm-6 col-sm-offset-3">
        <table class="table">
            <tr>
                <th>{{ trans('test::test.test_name') }}</th>
                <td>{{ $test->name }}</td>
            </tr>
            <tr>
                <th>{{ trans('test::test.total_questions') }}</th>
                @if($test->limit_question != 0)
                <td>{{ $test->total_question}}</td>
                @else
                <td>{{$test->questions()->where('status', ViewTest::STT_ENABLE)->count()}}</td>
                @endif
            </tr>
            @if(isset($test->total_written_question) && $test->total_written_question > 0)
            <tr>
                <th>{{ trans('test::test.total written questions') }}</th>
                <td>{{ $test->total_written_question}}</td>
            </tr>
            @endif
            <tr>
                <th>{{ trans('test::test.time') }}</th>
                <td>{{ $test->time . ' ('. trans('test::test.minute') .')' }}</td>
            </tr>
            @if ($test->set_min_point == Test::SET_MIN_POINT && $test->min_point)
            <tr>
                <th>{{ trans('test::test.Min point') }}</th>
                <td><strong>{{ $test->min_point ? (int)$test->min_point : '' }}</strong></td>
            </tr>
            @endif
            @if (!$test->is_auth && !$hasTested)
            <tr>
                <th colspan="2" class="text-center">{{ trans('test::test.input_name_and_email_to_do_test') }}</th>
            </tr>
            <tr>
                <th><label>{{ trans('test::test.full_name') }} <em>*</em></label></th>
                <td>
                    {!! Form::text('person[name]', old('person.name'), [
                        'class' => 'form-control',
                        'placeholder' => trans('test::test.full_name'),
                        'maxlength' => 255,
                        'autocomplete' => 'off'
                    ]) !!}
                </td>
            </tr>
            <tr>
                <th><label>{{ trans('test::test.email') }} <em>*</em></label></th>
                <td>
                    {!! Form::email('person[email]', old('person.email'), [
                        'data-url' => route('test::check_do_test'),
                        'id' => 'person_email',
                        'class' => 'form-control',
                        'placeholder' => trans('test::test.email'),
                        'maxlength' => 255,
                        'autocomplete' => 'off'
                    ]) !!}
                </td>
            </tr>
            <tr>
                <th><label>{{ trans('test::test.phone_number') }} <em>*</em></label></th>
                <td>
                    {!! Form::text('person[phone]', old('person.phone'), [
                        'class' => 'form-control',
                        'placeholder' => trans('test::test.phone_number'),
                        'maxlength' => 11,
                        'autocomplete' => 'off'
                    ]) !!}
                </td>
            </tr>
            @endif
        </table>
    </div>
</div>

<div class="text-center">
    @if ($hasTested)
        <span style="font-size: 17px;">
            <span>{{ trans('test::test.you_had_done_this_test') }}</span>
            <a class="link" href="{{ route('test::result', ['id' => $hasTested]) }}">{{ trans('test::test.view_results') }}</a>
        </span>
    @else
        <button type="submit" class="btn btn-primary">{{ $startText }}</button>
    @endif
</div>

{!! Form::close() !!}

@stop


