@extends('layouts.default')

@section('title', trans('test::test.view_test'))

@section('css')

@include('test::template.css')

@stop

<?php
use Rikkei\Test\View\ViewTest;
use Rikkei\Core\View\Form as FormView;
?>

@section('content')
<div class="box box-info">
    <div class="box-body">
        <div class="row">
            <div class="col-md-6 align-left">
                <h3>
                    {{ $item->name . ' - ' . $item->created_at->format('d/m/Y') . ' - (' . $item->time . trans('test::test.minute') . ')' }}
                    &nbsp;&nbsp;
                    <a class="link" style="font-size: 17px;" href="{{route('test::admin.test.index')}}">{{trans('test::test.back')}} <i class="fa fa-long-arrow-right"></i></a>
                </h3>
            </div>
            <div class="col-md-6">
                @if($item->set_valid_time != 0)
                <h4>
                    Thời hạn từ: <strong>{{ $item->time_start }}</strong> 
                    <i class="fa fa-long-arrow-right"></i> 
                    <strong>{{ $item->time_end }}</strong>
                </h4>
                @endif
                <div class="row">
                    <div class="col-sm-3 col-md-4 col-lg-3">
                        <label>{{ trans('test::test.question_status') }}</label>
                    </div>
                    <div class="col-sm-9 col-md-8 col-lg-6">
                        <?php
                        $statuses = ViewTest::listStatusLabel();
                        $filterStatus = FormView::getFilterData('status');
                        ?>
                        <select class="form-control select-search filter-grid select-grid" name="filter[status]">
                            <option value="">All</option>
                            @foreach ($statuses as $key => $label)
                            <option value="{{ $key }}" {{ $filterStatus == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
        @if ($item->description)
        <div>
            <label>{{ trans('test::test.description') }}: </label>
            <div class="test-desc">{{ $item->description }}</div>
        </div>
        @endif
        <br />

        @if (!$questions->isEmpty() || !$writtenQuestions->isEmpty())
            @if (!$questions->isEmpty())
                <div class="align-left">
                    <h4>
                        A: {{ trans('test::test.question multiple choice') }}
                    </h4>
                </div>
                <div class="questions_list">
                    @include('test::template.questions', ['questions' => $questions])
                </div>
            @endif
            @if (!$writtenQuestions->isEmpty())
                    <div class="align-left">
                        <h4>
                            B: {{ trans('test::test.question written') }}
                        </h4>
                    </div>
                <div class="questions_list">
                    @include('test::template.writtenQuestions', ['writtenQuestions' => $writtenQuestions])
                </div>
            @endif
        @else
            <div class="box-body">{{trans('test::test.no_item')}}</div>
        @endif
        
    </div>
</div>

@stop

@section('confirm_class', 'modal-warning')

@section('script')

<script src="{{ asset('lib/js/jquery-ui.min.js') }}"></script>
@include('test::template.script')

@stop
