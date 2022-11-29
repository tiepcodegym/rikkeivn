@extends('layouts.default')

@section('title', trans('test::test.edit_test'))

@section('css')

@include('test::template.css')

@stop

@section('content')

<div class="box box-info">
    <div class="box-body">
        <div class="row">
            <div class="col-sm-8 col-sm-offset-2">

                <div class="text-center">
                    <h3>{{ trans('test::test.confirm_edit') }}</h3>
                    
                    <p class="text-red" style="font-size: 18px; margin: 40px 0 30px;">{{ trans('test::test.confirm_this_test_have_testing') }}</p>

                    <div style="margin-bottom: 30px;">
                        <?php
                        $rqParams = array_merge(request()->all(), [
                            'id' => $testId,
                            'ignore_testing' => 1
                        ]);
                        ?>
                        <a href="{{ route('test::admin.test.index') }}" class="btn btn-warning">{{ trans('test::test.back_to_list') }}</a>
                        <a href="{{ route('test::admin.test.edit', $rqParams) }}" class="btn btn-primary">
                            {{ trans('test::test.continue_edit') }}
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@stop

@section('script')

@include('test::template.script')

@stop
