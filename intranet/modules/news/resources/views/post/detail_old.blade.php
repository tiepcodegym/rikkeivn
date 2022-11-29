<?php
    use Rikkei\News\Model\Post;
    use Rikkei\News\Model\LikeManage;
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\News\View\ViewNews;
    use Rikkei\News\Model\PostComment;
    use Rikkei\Core\Model\CoreConfigData;

    header("Cache-Control: no-store, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    $sttAutoSettingApproveComment = CoreConfigData::getAccountToEmail(1, CoreConfigData::AUTO_APPROVE_COMMNENT_KEY);
    $sttAutoApproveComment = CoreConfigData::AUTO_APPROVE;
?>

@extends('layouts.blog')

@section('title')
    {{ $titleHeadPage }}
@endsection

@section('css')
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_news/css/news.css') }}"/>
@endsection

@section('after_header')
    <div class="after-header">
        <div class="header-banner">
            <div class="bgr-text">
                <h3 class="hb-text">{{ $headingTitle }}</h3>
            </div>
        </div>
    </div>
@endsection
@section('content')
    <div class="blog-main" autocomplete="off">
        <div class="row">
            <!-- sidebar -->
        @include('news::post.include.sidebar')
        <!-- end sidebar -->
            <div class="col-md-8 blog-content">
                <div class="blog-detail">
                    <div class="bc-inner">
                        @if (!$postDetail)
                            <p class="not-found-item">{{ trans('news::view.Not found post') }}</p>
                        @else
                            <div class="post-detail">
                                <h2 class="bci-header">{{ $postDetail->title }}</h2>
                                <div class="row">
                                    <p class="post-meta post-date col-xs-6"
                                       style="padding-top: 20px;">{{ $postDetail->getPublicDate() }}</p>
                                    <div class="post-like col-xs-6 text-right" data-post-id="{{$postDetail->id}}">
                                        <span data-count-view style="font-size: 17px"></span>
                                        <i class="fa fa-eye eye-view" aria-hidden="true"></i>

                                        <a href="javascript:void(0)" class="btn-link number-size count-like" data-count-like></a>
                                        <button class="btn-primary-outline" type="button" data-post-btn="like" data-item-id="{{$postDetail->id}}">
                                            <i class="fa fa-thumbs-up thumb-size thumb-dislike" aria-hidden="true" data-post-icon="like"></i>
                                        </button>

                                        @if($postDetail->is_set_comment)
                                            <span class="number-size count-comment" data-count-cmt></span>
                                            <i class="fa fa-comments comment-icon thumb-size" aria-hidden="true"></i>
                                        @endif
                                    </div>
                                </div>
                                <div class="post-desc cke" style="padding-top: 15px">
                                    {!! $postDetail->desc !!}
                                </div>
                                <div>
                                    <p class="author" style="padding-top: 27px;">@if($postDetail->author)
                                            - {{ $postDetail->author }} -@endif</p>
                                </div>
                            </div>
                            @if ($postRelate && count($postRelate))
                                <div class="post-relate">
                                    <div class="blog-box-title">
                                        <span>{{ trans('news::view.Post relate') }}</span>
                                    </div>
                                    <div class="blog-box-content">
                                        <ul>
                                            @foreach ($postRelate as $post)
                                                <li>
                                                    <a href="{{ URL::route('news::post.view', ['slug' => $post->slug]) }}">{{ $post->title }}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    {{--check set comment--}}
        @if($postDetail->is_set_comment == Post::BE_COMMENTED)
            <div class="col-md-8 comment-container">
                <div class="row">
                    <div class="col-xs-12" data-post-id="{{$postDetail->id}}">
                        <h4><span class="count-comment" data-count-cmt></span> {{trans('news::view.Label comment')}}</h4>
                    </div>
                    <div class="col-xs-12">
                        <div class="img-parent">
                            <img class="img-circle" src="{{$userInfo->avatar_url}}">
                        </div>
                        <form id="create_comment" method="post" onsubmit="return false" class="no-validate">
                            <input type="hidden" value="{{ csrf_token() }}" name="_token">
                            <div class="parent-comment">
                                <textarea class="form-control comment textarea-parent root-comment emojis-wysiwyg" rows="2"
                                          title="{{trans('news::view.Comment title')}}"
                                          placeholder="{{trans('news::view.Comment title')}}" name="comment"
                                          id="comment" data-parent-id="0"></textarea>
                                <span class="info-comment">{{ trans('news::view.support markdown') }}</span>
                                <label id="comment-error" class="error" for="comment"></label>
                                <input type="hidden" name="post_id" id="post_id" value="{{$postDetail->id}}">
                                <input type="hidden" name="id" id="id" value="">
                            </div>
                        </form>
                    </div>
                    <div class="col-xs-12">
                        <div id="comment-content-pending">
                        </div>
                        <div id="list-comment">
                            @include('news::post.include.list_comment')
                        </div>
                        <div id="get-more-comment"></div>
                    </div>

                    <button id="load_more_comment" class="btn btn-primary hidden" data-post-cmt-load="{{$postDetail->id}}">
                        <span>{{trans('news::view.Load more')}} <i class="fa fa-spin fa-refresh hidden"></i></span>
                    </button>

                </div>
            </div>
        @endif
    </div>
@include('news::post.include.post_list_like')
    <div class="modal modal-danger" id="comment-error-modal">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span></button>
              <h4 class="modal-title">{{ trans('news::view.Error') }}</h4>
            </div>
            <div class="modal-body">
              <p>{{ trans('news::view.An error occurred') }}</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline pull-left" data-dismiss="modal">{{ trans('news::view.Close') }}</button>
            </div>
          </div>
          <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
      </div>
@endsection
@section('script')
    <script>
        var comment_url = '{{route('news::post.comment')}}';
        var like_url = '{{route('news::post.like')}}';;
        var approve_url = '{{route('news::post.approveComment')}}';
        var requiredField = '{{trans('core::message.This field is required')}}';
        var title = '{{trans('news::view.Comment title')}}';
        var btn_reply = '{{trans('news::view.Reply')}}';
        var btn_cancel = '{{trans('news::view.Cancel')}}';
        var url_load_more = '{{route('news::post.moreComment')}}';
        var post_id = '{{$postDetail->id}}';
        var status = '{{PostComment::STATUS_COMMENT_NOT_ACTIVE}}';
        var parent_id = '{{PostComment::PARENT_COMMENT_ID}}';
        var page = '{{PostComment::PAGE}}';
        var avatar = '{{$userInfo->avatar_url}}';
        var required_comment = '{{trans('news::message.Required comment')}}';
        var delete_comment = '{{ route('news::post.comment.delete') }}';
        var _token = '{{ csrf_token() }}';
        var stt = '{{PostComment::STATUS_COMMENT_NOT_ACTIVE}}';
        var checkPermission = '{{$checkPermission}}';
        var sttAutoSettingApproveComment = '{{$sttAutoSettingApproveComment}}';
        var sttAutoApproveComment = '{{$sttAutoApproveComment}}';
        var typeComment = {{ LikeManage::TYPE_COMMENT }};
        var likeText = "{{ trans('news::view.Like') }}";
        var emoticonPath = "{{ asset('asset_news/images/emoticons') }}";
        var commentPerPage = {{ PostComment::NEW_PER_PAGE }};
    </script>
    <script>
        var varGlobPass = {
            urlListLike: '{!! URL::route('news::post.listLike') !!}',
            urlActionLike: '{!! URL::route('news::post.like') !!}',
            urlGetAllCount: '{!! URL::route('news::post.get.all.count') !!}',
            cmtPerpage: {!!PostComment::NEW_PER_PAGE!!},
            trans: {
                like: '{!! trans('news::view.Like') !!}',
                dislike: '{!! trans('news::view.DisLike') !!}',
            },
        };
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/showdown/1.9.0/showdown.min.js"></script>
    <script src="{{ CoreUrl::asset('lib/js/emoticons/jquery.emojiarea.min.js') }}"></script>
    <script src="{{ CoreUrl::asset('lib/js/autosize.min.js') }}"></script>
    <script src="{{ CoreUrl::asset('asset_news/js/news.js') }}"></script>
    <script src="{{ CoreUrl::asset('asset_news/js/emojiicons.js')}}"></script>
    <script src="{{ CoreUrl::asset('asset_news/js/comment_post.js')}}"></script>
@endsection

