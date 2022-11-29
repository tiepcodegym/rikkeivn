<?php

use Rikkei\Core\Model\EmailQueue;
use Rikkei\Sales\Model\Css;

extract($data);
?>
@extends(EmailQueue::getLayoutConfig())

@section('content')
<p><strong>Dear {{ $dear_name }}</strong>,</p>
@if (isset($option['status']) && $option['status'] == Css::STATUS_FEEDBACK)
<p>Thông báo tới bạn có css phân tích được feedback trong dự án.</p>
            <p><strong>Nội dung Feedback: </strong>{{ $option['content'] }}</p>
@elseif (isset($option['status']) && $option['status'] == Css::STATUS_APPROVED)
<p>Thông báo tới bạn có css phân tích được approved trong dự án.</p>
@elseif ((isset($option['status']) && $option['status'] == Css::STATUS_SUBMITTED))
<p>Thông báo tới bạn có css phân tích cần bạn feedback trong dự án.</p>
@else
<p>Thông báo tới bạn có css phân tích cần bạn approve trong dự án.</p>
@endif
<p></p>
<p><strong>Project name: </strong><a href="{{ route('project::project.edit', ['id' => $option['projs_id']]) }}">{{ $option['name_projs'] }}</a></p>
<p><strong>PM: </strong>{{ $option['name_pm'] }}</p>
<p>Mời xem chi tiết: <a href="{{ URL::route('sales::css.detail', ['id' => $cssResultId]) }}" target="_blank">{{ URL::route('sales::css.detail', ['id' => $cssResultId]) }}</a></p>

@endsection