<?php
use Rikkei\News\Model\PostComment;
use Rikkei\Test\View\ViewTest;
use Rikkei\News\Model\LikeManage;
use Rikkei\News\View\ViewNews;
?>

<div class="content-reply-pending">
    <div class="comment-post-{{ $comment['id'] }}">
        <div class="img-parent">
            <img class="img-circle" src="{{ $userInfo->avatar_url }}">
        </div>
        <div class="area-comment" style="margin-top: 5px">
            <div class="no-margin-left custom-comment">
                <span class="name-user-comment name-user-color" style="float: left">{{$employee['name']}}</span>
                @if($comment['status'] === PostComment::STATUS_COMMENT_NOT_ACTIVE)
                    <span class="error" id="approve-message-{{$comment['id']}}">({{trans('news::message.Not approve')}})</span>
                @endif
                <div class="content-comment-{{ $comment['id'] }} content-comment content-block" style="text-align: justify">
                    <script>cmtAll[{{ $comment['id']}}] = {!!json_encode($comment['comment'])!!}</script>
                    <div class="parent-comment hidden">
                        <textarea rows="2" class="form-control textarea-parent emojis-wysiwyg" name="comment"  id="comment" autofocus></textarea>
                        <span class="info-comment">{{ trans('news::view.Esc to cancel') }}</span>
                        <label id="comment-error" class="error" for="comment"></label>
                        <input type="hidden" value="{{$comment['id']}}" name="id" >
                    </div>
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" class="status_value_{{ $comment['id'] }}" value="{{ $comment['status'] }}" />
                    <span class="comment-{{ $comment['id']}} content-comment span-comment" data-id="{{ $comment['id']}}" data-more-height="200"></span>
                </div>
            </div>
            <div class="row no-margin-left custom-comment-like">

                <div class="format format-{{ $comment['id'] }} date-create" style="padding: 0; margin-bottom: 0">
                    @if($comment['status'] === PostComment::STATUS_COMMENT_ACTIVE)
                        @if ($comment['parent_id'] == PostComment::PARENT_COMMENT_ID)
                            <div class="like-div">
                                <a class="like-button" onclick="like(this, {{ $comment['id'] }}, {{ LikeManage::TYPE_COMMENT }})" link="{{ URL::route('news::post.like') }}"  data-root="1">
                                    <i class="font-style-normal {{ $comment['check_liked'] ? 'thumb-like' : 'thumb-dislike' }}">{{ trans('news::view.Like') }}</i>
                                </a>
                                <div class="like-container {{ $comment['count_like_comment'] ? '' : 'hidden' }}" onclick="showLike({{ $comment['id'] }}, {{ LikeManage::TYPE_COMMENT }})" title="{{ $comment['count_like_comment'] }}">
                                    <i class="fa fa-thumbs-up thumb-like size-detail" aria-hidden="true"></i>
                                    <span class="count-like">{{ ViewNews::compactTotal($comment['count_like_comment']) }}</span>
                                </div>
                            </div>
                            <a class="reply-comment" data-id="{{$comment['id']}}">{{trans('news::view.Reply')}}</a>
                        @endif
                        <div style="display: block" class="btn-group" >
                                @if ($comment['user_id'] == $userInfo['employee_id'] || ($checkPermission && $comment['status'] == PostComment::STATUS_COMMENT_NOT_ACTIVE) )
                                    <button type="button" class="dropdown-toggle action-comment" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    </button>
                                    <ul class="dropdown-menu">
                                        @if ($comment['user_id'] == $userInfo['employee_id'])
                                            <li><a href="#" class="edit-comment" data-id="{{ $comment['id'] }}"><span><i class="fa fa-pencil-square-o" aria-hidden="true"></i></span> Edit</a></li>
                                            <li><a href="#" class="delete-comment" data-id="{{ $comment['id'] }}"><span><i class="fa fa-trash-o" aria-hidden="true"></i></span> &nbsp;Del</a></li>
                                        @endif
                                        @if ($checkPermission && $comment['status'] == PostComment::STATUS_COMMENT_NOT_ACTIVE)
                                            <li><a class="btn-approve" id="approve-{{$comment['id']}}" data-id="{{$comment['id']}}" data-root="1"><span><i class="fa fa-check-square-o" aria-hidden="true"></i></span> {{ trans('news::view.Approve') }}</a></li>
                                        @endif
                                    </ul>
                                @endif
                            </div>

                        <div class="item-right">
                            <i class="fa fa-clock-o" aria-hidden="true"></i>
                            <span class="date-color">{{date('d/m/Y')}}</span>
                        </div>
                    @else
                        <div class="item-right">
                            <i class="fa fa-clock-o" aria-hidden="true"></i>
                            <span class="date-color">{{date('d/m/Y')}}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="border-reply">
            <div id="content-reply-{{$comment['id']}}" class="reply">
            </div>
        </div>
        <div id="add_new_reply_{{$comment->id}}">
            <div id="reply-{{$comment->id}}" class="hidden">
                <div class="img-parent">
                    <img class="img-circle" src="{{ $userInfo->avatar_url }}">
                </div>
                <div class="parent-comment">
                    <textarea class="form-control reply-text reply-new emojis-wysiwyg" name="reply-comment-{{$comment->id}}" 
                              placeholder="{{trans('news::view.Comment title')}}" id="reply-comment-{{$comment->id}}" 
                              data-parent-id="{{$comment->id}}" autofocus style="width: 92%"></textarea>
                    <label id="comment-error" class="error" for="comment"></label>
                </div>
            </div>
        </div>
    </div>
</div>