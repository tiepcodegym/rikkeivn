<?php 
    use Carbon\Carbon;
    use Rikkei\Core\View\View;
    use Rikkei\Core\Model\EmailQueue;
    $layout = EmailQueue::getLayoutConfig();
?>

@extends($layout)

@section('content')
    <p>{{ trans('asset::view.Dear :receiver_name,', ['receiver_name' => $data['receiver_name']]) }}</p>
    <p>{{ trans('asset::view.The request asset created by you has change status to:') }} <b>{{ $data['status'] }}</b>.</p>
    <p>{!! trans('asset::view.Request name: :request_name', ['request_name' => View::nl2br($data['request_name'])]) !!}</p>
    <p>{!! trans('asset::view.Request date: :request_date', ['request_date' => Carbon::parse($data['request_date'])->format('d/m/Y')]) !!}</p>
    <p>{!! trans('asset::view.Petitioner: :petitioner_name', ['petitioner_name' => $data['petitioner_name']]) !!}</p>
    <p>{!! trans('asset::view.Approver: :approver_name', ['approver_name' => $data['approver_name']]) !!}</p>
    <p><a href="{{ $data['href'] }}">{{ trans('asset::view.View detail') }}</a></p>
@endsection