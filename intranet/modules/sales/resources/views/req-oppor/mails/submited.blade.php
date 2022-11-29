<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
extract($data);
?>
@extends($layout)

@section('content')

<p>&nbsp;</p>
<p>Opportunity: {!! $content !!}</p>
<ul>
    <li>Deadline: <strong>{{ $deadline }}</strong></li>
    <li>Salesperson: <strong>{{ $saleName }}</strong></li>
</ul>
<p>&nbsp;</p>

<p><a href="{{ $detailLink }}" style="color: #15c">{{ trans('sales::view.View detail') }}</a></p>

@endsection
