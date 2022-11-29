<?php 
    use Carbon\Carbon;
    use Rikkei\Core\View\View;
    use Rikkei\Core\Model\EmailQueue;
    $layout = EmailQueue::getLayoutConfig();
?>

@extends($layout)

@section('content')
    <p>{{ trans('asset::view.Dear :receiver_name,', ['receiver_name' => $data['name_sent_to']]) }}</p>
    <p>{{ trans('asset::view.Having a new required property needs your confirmation.') }}</p>
    <p>{!! trans('asset::view.Request name: :request_name', ['request_name' => View::nl2br($data['request_name'])]) !!}</p>
    <p>{!! trans('asset::view.Request date: :request_date', ['request_date' => Carbon::parse($data['request_date'])->format('d/m/Y')]) !!}</p>
    <p>{!! trans('asset::view.Petitioner: :petitioner_name', ['petitioner_name' => $data['petitioner_name']]) !!}</p>
    <p>{!! trans('asset::view.Creator: :creator_name', ['creator_name' => $data['creator_name']]) !!}</p>
    <p>{!! trans('asset::view.Reviewer: :reviewer_name', ['reviewer_name' => $data['reviewer_name']]) !!}</p>
    <p><a href="{{ $data['link'] }}">{{ trans('asset::view.View detail') }}</a></p>
@endsection