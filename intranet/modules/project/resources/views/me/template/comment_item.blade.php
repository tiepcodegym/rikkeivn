<?php 
use Rikkei\Core\View\View as CoreView;

$avatar = $comment->avatar_url;
if (!$avatar) {
    $avatar = '/common/images/noimage.png';
}
?>
<div class="media comment_item {{ $comment->type_class }}" id="me_comment_{{ $comment->id }}">
    @if (auth()->id() == $comment->employee_id)
    <button type="button" class="btn_del_comment" data-url="{{ route('project::project.eval.remove_comment', ['id' => $comment->id]) }}">×</button>
    @endif
    <div class="media-left pull-left">
        <img class="_comment_avatar" id="_avatar_{{$comment->employee_id}}" data-id="{{$comment->google_id}}" src="{{ $avatar }}" alt="">
    </div>
    <div class="media-body">
        <h4 class="media-heading">{{$comment->name}}</h4>
        <div class="date"><i class="fa fa-clock-o"></i> {{$comment->created_at->format('H:i d/m/Y')}}</div>
        <div class="comment_content">{!! CoreView::nl2br($comment->content) !!}</div>
    </div>
</div>
