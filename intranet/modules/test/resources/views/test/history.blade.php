@extends('test::layouts.front')

@section('title', trans('test::test.candidate_test_history'))

@section('body_class', 'history-page')

@section('content')
    
    <div class="row">
        <div class="col-md-4">
            <h1 class="page-title">{{ trans('test::test.candidate_test_history') }}</h1>
        </div>
        <div class="col-md-8 candidate-col">
            <span><strong>{{ trans('test::test.full_name') }}:</strong> <span>{{ $candidate->fullname }}</span></span>
            &nbsp;&nbsp;&nbsp;
            <span><strong>{{ trans('test::test.email') }}:</strong> <span class="email">{{ $candidate->email }}</span></span>
        </div>
    </div>
    @if (!$tests->isEmpty())
    <div class="table-responsive">
        <table class="table" style="border-top: 1px solid #ddd;">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>{{ trans('test::test.test_name') }}</th>
                    <th>{{ trans('test::test.test_type') }}</th>
                    <th>{{ trans('test::test.time') }}</th>
                    <th>{{ trans('test::test.test_date') }}</th>
                    <th>{{ trans('test::test.total_corrects') }}</th>
                    <th>{{ trans('test::test.detail') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tests as $order => $test)
                <tr>
                    <td>{{ ($order + 1) }}</td>
                    <td>{{ $test->name }}</td>
                    <td>{{ $test->getTypeLabel() }}</td>
                    <td>{{ $test->time }} {{ trans('test::test.minute') }}</td>
                    <td>{{ $test->updated_at->format('H:i d-m-Y') }}</td>
                    <td>{{ $test->total_corrects . '/' . $test->questions()->count() }}</td>
                    <td><a href="{{ route('test::result', ['id' => $test->result_id]) }}" class="link">{{ trans('test::test.view') }}</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <h3 class="text-center">{{ trans('test::test.no_item') }}</h3>
    @endif

@stop


