<?php
use Rikkei\Resource\Model\CandidateComment;
use Rikkei\Core\View\View as CoreView;

$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
$commentList = CandidateComment::getGridData($data['candidate_id']);
?>

@extends($layout)

@section('content')
@if (isset($data['dearName']) && !empty($data['dearName']))
<p>{!! trans('resource::view.Hello <b>:name</b>,', ['name' => $data['dearName']]) !!}</p>
@endif
{!! $data['content'] !!}

<p>&nbsp;</p>
<!--comment-->
@if (!$commentList->isEmpty())
<p><strong>{{ trans('resource::view.Comments') }}</strong></p>
<ul class="comments-list" style="padding-left: 0;">
    @foreach ($commentList as $item)
    <li style="margin-bottom: 5px;">
        <strong style="color: #110f88;">{{ $item->name }} ({{ CoreView::getNickName($item->email) }}): </strong>
        <span style="white-space: pre-line;">{{ $item->content }}</span>
    </li>
    @endforeach
</ul>
@endif
@if ($commentList->hasMorePages())
<p><a href="{{ route('resource::candidate.detail', $data['candidate_id']) . '#tab_interview' }}">{{ trans('resource::view.View more comments') }}</a></p>
@endif

@endsection
