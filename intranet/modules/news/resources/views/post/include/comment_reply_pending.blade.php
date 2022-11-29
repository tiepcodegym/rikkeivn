<?php
use Rikkei\News\Model\PostComment;
use Rikkei\News\View\ViewNews;
use Rikkei\News\Model\LikeManage;
?>
<div class="content-reply-{{ $comment['id'] }}">
    <div class="img-parent">
        <img class="img-circle" src="{{ $userInfo['avatar_url'] }}">
    </div>
    <div class="area-comment" style="margin-top: 5px">
        <div class="no-margin-left custom-comment">
            <span class="name-user-color" style=" padding-right: 10px">{{$employee['name']}}</span>
            @if($comment['status'] === PostComment::STATUS_COMMENT_NOT_ACTIVE)
                <span class="error" id="approve-message-{{$comment['id']}}">({{trans('news::message.Not approve')}})</span>
            @endif
            <div class="content-reply-comment-{{ $comment['id']  }} content-reply-comment content-block">
                <script>cmtAll[{{ $comment['id']}}] = {!!json_encode($comment['comment'])!!}</script>
                <div class="parent-comment hidden">
                    <textarea rows="2"  class="form-control reply-text emojis-wysiwyg" name="comment" data-id="{{ $comment['id'] }}"></textarea>
                    <span class="info-comment">{{ trans('news::view.Esc to cancel') }}</span>
                    <label id="comment-error" class="error" for="comment"></label>
                    <input type="hidden" name="id" value="{{ $comment['id'] }}">
                </div>
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" class="value_status_reply_{{ $comment['id'] }}" value="{{ $comment['status'] }}">
                <span class="comment-{{ $comment['id']}} content-comment span-comment" data-id="{{ $comment['id']}}" data-more-height="200"></span>
            </div>
        </div>

        <div class="row no-margin-left custom-comment-like">
            <div class="format" style="padding: 0; margin-bottom: 0">
                @if($comment['status'] === PostComment::STATUS_COMMENT_ACTIVE)
                    <div class="like-div">
                        <a class="like-button" onclick="like(this, {{ $comment['id'] }}, {{ LikeManage::TYPE_COMMENT }})" link="{{ URL::route('news::post.like') }}">
                            <i class="font-style-normal {{ $comment['check_liked'] ? 'thumb-like' : 'thumb-dislike' }}">{{ trans('news::view.Like') }}</i>
                        </a>
                        <div class="like-container {{ $comment['count_like_comment'] ? '' : 'hidden' }}" onclick="showLike({{ $comment['id'] }}, {{ LikeManage::TYPE_COMMENT }})" title="{{ $comment['count_like_comment'] }}">
                            <i class="fa fa-thumbs-up thumb-like size-detail" aria-hidden="true"></i>
                            <span class="count-like">{{ ViewNews::compactTotal($comment['count_like_comment']) }}</span>
                        </div>
                    </div>
                @endif

                @if ($comment['user_id'] == $userInfo['employee_id'] || ($checkPermission && $comment['status'] == PostComment::STATUS_COMMENT_NOT_ACTIVE) )
                    <div style="display: block" class="dropdown reply-comment-menu">
                        <button class="dropdown-toggle action-reply" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                        </button>
                        <div class="dropdown-menu action-reply-comment">
                            @if ($comment['user_id'] == $userInfo['employee_id'])
                                <li><a href="#" class="edit-reply-comment" data-id="{{ $comment['id'] }}"><span><i class="fa fa-pencil-square-o" aria-hidden="true"></i></span> Edit</a></li>
                                <li><a href="#" class="delete-reply-comment" data-id="{{ $comment['id'] }}"><span><i class="fa fa-trash-o" aria-hidden="true"></i></span> &nbsp;Del</a></li>
                            @endif
                            @if ($checkPermission && $comment['status'] == PostComment::STATUS_COMMENT_NOT_ACTIVE)
                                <li><a class="btn-approve" id="approve-{{$comment['id']}}" data-id="{{$comment['id']}}"><span><i class="fa fa-check-square-o" aria-hidden="true"></i></span> {{ trans('news::view.Approve') }}</a></li>
                            @endif
                        </div>
                    </div>
                @endif
                <div class="item-right">
                    <i class="fa fa-clock-o" aria-hidden="true"></i>
                    <span class="date-color">{{ViewNews::formatDateTimeComment($comment['updated_at'])}}</span>
                </div>
            </div>
        </div>
    </div>
</div>