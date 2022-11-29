<?php
use Carbon\Carbon;
use Rikkei\Core\View\View;
use Rikkei\Core\Model\EmailQueue;
$layout = EmailQueue::getLayoutConfig();
?>

@extends($layout)

@section('content')

    <p>{{ trans('asset::view.Dear :receiver_name,', ['receiver_name' => $data['receiver_name']]) }}</p>
    <p>{{ $data['mail_title'] }}{{ trans('asset::view.Bellow here are the newly asset need you confirm:') }}</p>
    <p>{!! trans('asset::view.Please click on the link below to view detail') !!} :
        @if (isset($data['href']) && $data['href'])
            <a href="{{ $data['href'] }}" class="asset-profile">{{ trans('asset::view.List asset') }}</a>
        @endif
    </p>

@endsection