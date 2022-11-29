<?php
use Rikkei\News\View\ViewNews;
use Rikkei\News\Model\PostComment;
use Rikkei\Test\View\ViewTest;
use Rikkei\News\Model\LikeManage;
use Illuminate\Support\Facades\Cache;
use Rikkei\News\Model\Post;
?>
<script>
    if (typeof cmtAll !== 'object') {
        var cmtAll = {};
    }
</script>
@foreach($comments as $comment)
    <?php
    $showEditComment = false;
    $showMessageNotApprove = false;
    if( ($comment['status'] === PostComment::STATUS_COMMENT_NOT_ACTIVE) ||
        //Load thông báo chưa duyệt cho người có permission
        (!is_null($comment['edit_comment']) && $checkPermission) ||
        //Load thông báo chưa duyệt cho chủ comment
        (!is_null($comment['edit_comment']) &&$comment['user_id'] == $userInfo['employee_id']) ) :
        $showMessageNotApprove = true;
        if(!is_null($comment['edit_comment'])) :
            $commentContent = $comment['edit_comment'];
            $showEditComment = true;
        else :
            $commentContent = $comment['comment'];
        endif;
    else :
        $commentContent = $comment['comment'];
    endif;
    ?>
    <div class="comment-post-{{ $comment['id'] }} comment-post">
        <div class="img-user">
            <img class="img-circle" src="{{ $comment->avatar_url }}">
        </div>
        <div class="comment-parent">
            <div class="area-comment" style="margin-top: 5px">
                <div class="no-margin-left custom-comment">
                    @if($showMessageNotApprove)
                        <span class="error" id="approve-message-{{$comment['id']}}">({{trans('news::message.Not approve')}})</span>
                    @endif
                    <div class="content-comment-{{ $comment['id'] }} content-comment content-block"
                         @if ($showEditComment)
                         style="background: #f7f0cb" data-toggle="tooltip" title="Approved Value: {{$comment['comment']}}"
                            @endif
                    >
                        <script>cmtAll[{{ $comment['id']}}] = {!!json_encode($commentContent)!!}</script>
                        <div class="parent-comment hidden">
                            <textarea rows="2" class="form-control textarea-parent emojis-wysiwyg" name="comment"  id="comment" autofocus></textarea>
                            <span class="info-comment">{{ trans('news::view.Esc to cancel') }}</span>
                            <label id="comment-error" class="error" for="comment"></label>
                            <input type="hidden" value="{{$comment['id']}}" name="id" >
                        </div>
                        <?php /*<input type="hidden" class="store_value_{{ $comment['id'] }}" value="{{ $comment['comment'] }}" />*/ ?>
                        <input type="hidden" class="status_value_{{ $comment['id'] }}" value="{{ $comment['status'] }}" />
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <span class="name-user-comment name-user-color" >{{$comment['employee_name']}}</span>
                        <span class="comment-{{ $comment['id']}} span-comment" data-id="{{ $comment['id']}}" data-more-height="200"></span>
                    </div>
                </div>
                <div class="row no-margin-left custom-comment-like" >
                    <div class="format format-{{ $comment['id'] }} date-create" style="padding: 0; margin-bottom: 0">
                        @if($comment['status'] == PostComment::STATUS_COMMENT_ACTIVE)
                            <div class="like-div">
                                <a class="like-button" data-item-id="{{ $comment['id'] }}" data-like-type="{{ LikeManage::TYPE_COMMENT }}" data-post-btn="like">
                                    <i class="font-style-normal {{ $comment['check_liked'] ? 'thumb-like' : 'thumb-dislike' }}" data-item-id="{{ $comment['id'] }}" data-post-icon="like_cmt">
                                        <span style="color: #999999">{{ trans('news::view.Like') }}</span>
                                    </i>
                                </a>
                                <div class="like-container {{ $comment['count_like_comment'] ? '' : 'hidden' }}" data-item-closest="{{ $comment['id'] }}">
                                    <i class="fa fa-thumbs-up thumb-like size-detail" aria-hidden="true"></i>
                                    <span class="count-like" data-like-type="{!!LikeManage::TYPE_COMMENT!!}" data-count-like_cmt data-item-id="{{ $comment['id'] }}">{{ ViewNews::compactTotal($comment['count_like_comment']) }}</span>
                                </div>
                            </div>
                            <a class="reply-comment" data-id="{{$comment->id}}">{{trans('news::view.Reply')}}</a>
                            <div class="item-right">
                                <i class="fa fa-clock-o" aria-hidden="true"></i>
                                <span class="date-color created-date">{{ViewNews::formatDateTimeComment($comment['updated_at'])}}</span>

                                <div style="display: block; float: right" class="btn-group" id="{{$comment['id']}}">
                                    @if ( ($comment['user_id'] == $userInfo['employee_id']) ||
                                    ($checkPermission && $comment['status'] === PostComment::STATUS_COMMENT_NOT_ACTIVE) ||
                                    ($checkPermission && !is_null($comment['edit_comment'])) )
                                        <button class="dropdown-toggle action-comment"
                                                data-toggle="dropdown"
                                                aria-haspopup="true"
                                                aria-expanded="true">
                                        </button>

                                        <ul class="dropdown-menu">
                                            @if ($comment['user_id'] == $userInfo['employee_id'])
                                                <li><a href="#" class="edit-comment" data-id="{{ $comment['id'] }}"><span><i class="fa fa-pencil-square-o" aria-hidden="true"></i></span> Edit</a></li>
                                                <li><a href="#" class="delete-comment" data-id="{{ $comment['id'] }}"><span><i class="fa fa-trash-o" aria-hidden="true"></i></span> &nbsp;Del</a></li>
                                            @endif

                                            @if ( ($checkPermission && $comment['status'] == PostComment::STATUS_COMMENT_NOT_ACTIVE) || ($checkPermission && !is_null($comment['edit_comment'])) )
                                                <li><a class="btn-approve" id="approve-{{$comment['id']}}" data-id="{{$comment['id']}}" data-root="1"><span><i class="fa fa-check-square-o" aria-hidden="true"></i></span> {{ trans('news::view.Approve') }}</a></li>
                                            @endif
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="item-right">
                                <i class="fa fa-clock-o" aria-hidden="true"></i>
                                <span class="date-color created-date">{{ViewNews::formatDateTimeComment($comment['updated_at'])}}</span>
                            </div>
                        @endif
                    </div>
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
            <div class="border-reply">
                <div id="content-reply-{{$comment->id}}" class="reply">
                </div>
                @include('news::post.include.list_reply_comment')
            </div>
            <div class="border-reply" id="get-more-reply-comment-{{$comment->id}}">
            </div>
            <?php
            $countAllCommentReply =  Cache::get(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_COUNT_COMMENT_REPLY . '_' . $comment['id']);
            if (is_null($countAllCommentReply)) {
                $countAllCommentReply = PostComment::countAllCommentReplyById($comment['id']);
                Cache::put(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_COUNT_COMMENT_REPLY . '_' . $comment['id'], $countAllCommentReply, Post::CACHE_EXPIRE);
            }
            ?>
            @if($countAllCommentReply > PostComment::NEW_PER_PAGE)
                <a class="loadmore-comment-reply" id="loadmore-reply-{{$comment['id']}}" data-id="{{$comment['id']}}">{{trans('news::view.Load more')}}</a>
            @endif
        </div>
    </div>
@endforeach
