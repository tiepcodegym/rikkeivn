<?php

use Rikkei\News\View\ViewNews;
use Rikkei\News\Model\PostComment;
use Rikkei\Test\View\ViewTest;
use Rikkei\News\Model\LikeManage;

//$data = ViewNews::getReplyComment($comment['id'], PostComment::NEW_PER_PAGE, $page, $userInfo);
?>

@if (isset($commentsReply[$comment['id']]))
@foreach($commentsReply[$comment['id']] as $subComment)
    <?php
        $showEditSubComment = false;
        $showMessageSubNotApprove = false;
        if( ($subComment['status'] === PostComment::STATUS_COMMENT_NOT_ACTIVE) || 
            //Load thông báo chưa duyệt cho người có permission
            (!is_null($subComment['edit_comment']) && $checkPermission) ||
            //Load thông báo chưa duyệt cho chủ comment
            (!is_null($subComment['edit_comment']) &&$comment['user_id'] == $userInfo['employee_id']) ) :
            $showMessageSubNotApprove = true;
            if(!is_null($subComment['edit_comment'])) :
                $subCommentContent = $subComment['edit_comment'];
                $showEditSubComment = true;
            else :
                $subCommentContent = $subComment['comment'];
            endif;
        else :
            $subCommentContent = $subComment['comment'];
        endif;
    ?>
    <div class="content-reply-{{ $subComment['id'] }}">
        <div class="img-parent">
            <img class="img-circle" src="{{ $subComment->avatar_url }}">
        </div>
        <div class="area-comment" style="margin-top: 5px">
            <div class="no-margin-left custom-commen">
                <span class="name-user-color" style="padding-right: 10px">{{$subComment['employee_name']}}</span>
                @if($showMessageSubNotApprove)
                    <span class="error" id="approve-message-{{$subComment['id']}}">({{trans('news::message.Not approve')}})</span>
                @endif
                <div class="content-reply-comment-{{ $subComment['id']  }} content-reply-comment content-block"
                     @if ($showEditSubComment)
                     style="background: #f7f0cb" data-toggle="tooltip" title="Approved Value: {{$subComment['comment']}}"
                        @endif
                >
                    <script>cmtAll[{{ $subComment['id']}}] = {!!json_encode($subCommentContent)!!}</script>
                    <div class="parent-comment hidden">
                        <textarea rows="2"  class="form-control reply-text emojis-wysiwyg" name="comment" data-id="{{ $subComment['id'] }}"></textarea>
                        <span class="info-comment">{{ trans('news::view.Esc to cancel') }}</span>
                        <label id="comment-error" class="error" for="comment"></label>
                        <input type="hidden" name="id" value="{{ $subComment['id'] }}">
                    </div>
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" class="value_status_reply_{{ $subComment['id'] }}" value="{{ $subComment['status'] }}">
                    <span class="comment-{{ $subComment['id']}} span-comment" data-id="{{ $subComment['id']}}" data-more-height="200"></span>
                </div>
            </div>
            <div class="row no-margin-left custom-comment-like">
                <div class="format" style="padding: 0; margin-bottom: 0">
                    @if($subComment['status'] === PostComment::STATUS_COMMENT_ACTIVE)
                        <div class="like-div" style="float: left">
                            <a class="like-button" data-item-id="{{ $subComment['id'] }}" data-like-type="{{ LikeManage::TYPE_COMMENT }}" data-post-btn="like">
                                <i class="font-style-normal {{ $subComment['check_liked'] ? 'thumb-like' : 'thumb-dislike' }}" data-item-id="{{ $subComment['id'] }}" data-post-icon="like_cmt">{{ trans('news::view.Like') }}</i>
                            </a>
                            <div class="like-container {{ $subComment['count_like_comment'] ? '' : 'hidden' }}" data-item-closest="{{ $subComment['id'] }}">
                                <i class="fa fa-thumbs-up thumb-like size-detail" aria-hidden="true"></i>
                                <span class="count-like" data-like-type="{!!LikeManage::TYPE_COMMENT!!}" data-count-like_cmt data-item-id="{{ $subComment['id'] }}">{{ ViewNews::compactTotal($subComment['count_like_comment']) }}</span>
                            </div>
                        </div>
                    @endif
                    <div class="item-right">
                        <i class="fa fa-clock-o" aria-hidden="true"></i>
                        <span class="date-color created-date">{{ViewNews::formatDateTimeComment($subComment['updated_at'])}}</span>
                        @if ($subComment['user_id'] == $userInfo['employee_id'] || ($checkPermission && $subComment['status'] == \Rikkei\News\Model\PostComment::STATUS_COMMENT_NOT_ACTIVE) || ($checkPermission && !is_null($subComment['edit_comment'])))
                            <div style="display: block" class="dropdown reply-comment-menu">
                                <button class="dropdown-toggle action-reply" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                </button>
                                <div class="dropdown-menu action-reply-comment">
                                    @if ($subComment['user_id'] == $userInfo['employee_id'])
                                        <li><a href="#" class="edit-reply-comment" data-id="{{ $subComment['id'] }}"><span><i class="fa fa-pencil-square-o" aria-hidden="true"></i></span> Edit</a></li>
                                        <li><a href="#" class="delete-reply-comment" data-id="{{ $subComment['id'] }}"><span><i class="fa fa-trash-o" aria-hidden="true"></i></span> &nbsp;Del</a></li>
                                    @endif
                                    @if ( ($checkPermission && $subComment['status'] == PostComment::STATUS_COMMENT_NOT_ACTIVE) || ($checkPermission && !is_null($subComment['edit_comment'])) )
                                        <li><a class="btn-approve" id="approve-{{$subComment['id']}}" data-id="{{$subComment['id']}}"><span><i class="fa fa-check-square-o" aria-hidden="true"></i></span> {{ trans('news::view.Approve') }}</a></li>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endif
