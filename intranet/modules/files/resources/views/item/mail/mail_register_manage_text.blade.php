<?php
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Files\Model\ManageFileText;

$typeCvdi = ManageFileText::CVDI;
$typeCvden = ManageFileText::CVDEN;
?>
@extends(EmailQueue::getLayoutConfig())

@section('content')
    <p>{{ trans('files::view.Dear') }} <b> {{ $data['mail_name'] }},</b></p>
    <p>{{ trans('files::view.Có một văn bản vừa được gửi đến cho bạn.') }}</p>
    <p><b>{{ trans('files::view.Type') }}:</b>         
        @if($data['type'] == $typeCvden)
            {{ trans('files::view.Công văn đến') }}
        @elseif($data['type'] == $typeCvdi)
            {{ trans('files::view.Công văn đi') }}
        @endif</p>
    <p><b>{{ trans('files::view.Số VB:') }}</b> {{ $data['code_file'] }}</p>
    <p><b>{{ trans('files::view.Đơn vị xử lý VB') }}:</b> {{ $data['team_id'] }} </p>
    <p><b>{{ trans('files::view.Trích yếu VB:') }} </b></p>
    <p> {!! $data['quote_text'] !!}</p>
    <p><b>{{ trans('files::view.Nội dung') }}: </b>
        @if($data['content'] != null)
            {!! $data['content'] !!}
        @else
            <i>{{ trans('files::view.Xem chi tiết trong file đính kèm') }}.</i>
        @endif
    </p>
        @if($data['tick'] == 1)
            <p><b> {{ $data['position'] }}</b></p>
            <p><i style="font-family: Italic;text-indent: 1em;">({{ trans('files::view.Đã Ký') }})</i></p>
            <p><b>{{ $data['signer'] }}</b></p>
        @endif
    <p>{{ trans('files::view.Sincerely thank you!') }}</p>
    <p><b>Intranet team.</b></p>
@endsection
