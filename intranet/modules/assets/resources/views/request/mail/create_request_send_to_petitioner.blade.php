<?php 
    use Carbon\Carbon;
    use Rikkei\Core\View\View;
    use Rikkei\Core\Model\EmailQueue;
    $layout = EmailQueue::getLayoutConfig();
?>

@extends($layout)

@section('content')
    <p>{{ trans('asset::view.Dear :receiver_name,', ['receiver_name' => $data['receiver_name']]) }}</p>
    <p>{{ trans('asset::view.Have new a request assset has been created for you.') }}</p>
    <p>{!! trans('asset::view.Request name: :request_name', ['request_name' => View::nl2br($data['request_name'])]) !!}</p>
    <p>{!! trans('asset::view.Request date: :request_date', ['request_date' => Carbon::parse($data['request_date'])->format('d/m/Y')]) !!}</p>
    <p>{!! trans('asset::view.Creator: :creator_name', ['creator_name' => $data['creator_name']]) !!}</p>
    <p>{!! trans('asset::view.Reviewer: :reviewer_name', ['reviewer_name' => $data['reviewer_name']]) !!}</p>
    <p><a href="{{ $data['href'] }}">{{ trans('asset::view.View detail') }}</a></p>
@endsection