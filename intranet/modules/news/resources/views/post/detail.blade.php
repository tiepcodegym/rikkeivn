
<?php
use Rikkei\News\Model\Post;
use Rikkei\News\Model\LikeManage;
use Rikkei\Core\View\CoreUrl;
use Rikkei\News\View\ViewNews;
use Rikkei\News\Model\PostComment;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Core\View\CacheBase;

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
    <link rel="stylesheet" href="{{asset('lib/slick/cs/slick.css')}}">
    <link rel="stylesheet" href="{{asset('lib/slick/cs/slick-theme.css')}}">
    <link rel="stylesheet" href="{{asset('lib/emojis/emojionearea.min.css')}}">
    <link rel="stylesheet" href="{{asset('lib/simple-scrollbar/simple-scrollbar.css')}}">
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_news/css/common.css') }}"/>
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_news/css/detail.css') }}"/>
@endsection
@section('content')
    <div class="home-wrapper" id="categoriesWrapper">
        @include('news::post.include.header_news')
        <section class="news-section home-news-body">
            <div class="home-news-body-container container">
                <div class="home-news-body-row row">

                    <!-- content left -->
                    <div class="home-news-body-left col-sm-7 col-md-8">
                        <div class="main-section row">
                            <div class="detail home-body-section col-sm-12">
{{--                                <div id="top-hashtag">--}}
{{--                                    <h5 class="top-hashtag">{{trans('news::view.top_tags')}}: </h5>--}}
{{--                                    @include('news::post.include.template.hashtag', ['tags' => $topHashTag])--}}
{{--                                </div>--}}
                                <div class="breadcrumb">
                                    <ul>
                                        <li>
                                            <a href="{{ URL::to('/') }}">Tin tức</a>
                                        </li>
                                        @if($headingTitle)
                                            <li>
                                                <a href="{{ ViewNews::getCategoryUrl($category['slug']) }}">{{ $headingTitle }}</a>
                                            </li>
                                        @endif
                                        <li>
                                            <a href="">{{ $postDetail->title }}</a>
                                        </li>
                                    </ul>
                                </div>
                                @if (!$postDetail)
                                    <p class="not-found-item">{{ trans('news::view.Not found post') }}</p>
                                @else
                                    <div class="home-latest-news-content home-body-section">
                                        <div id="title">
                                            <h3>{{ $postDetail->title }}</h3>
                                        </div>
                                        <div id="date-create">
                                            <div class="latest-news-item-info news-item-content-info" data-post-id="{{$postDetail->id}}">
                                                @if($postDetail->author)
                                                    <span class="item-left">
                                                        <i class="fa fa-user icon" aria-hidden="true"></i>
                                                        <span class="body-content author">{{$postDetail->author}}</span>
                                                    </span>
                                                @endif

                                                <span class="item-left">
                                                    <i class="fa fa-clock-o" aria-hidden="true"></i> <span class="body-content created-date">{{ $postDetail->getPublicDate() }}</span>
                                                </span>

                                                <span class="item-right">
                                                    <i class="fa fa-eye eye-view " aria-hidden="true"></i>
                                                    <span class="number-size" style="text-decoration:none;" data-count-view></span>
                                                </span>
                                                @if($postDetail->is_set_comment)
                                                    <span class="item-right">
                                                        <button class="btn-primary-outline" type="button" id="icon-comment">
                                                            <i class="fa fa-comments comment-icon thumb-size thumb-dislike" aria-hidden="true"></i>
                                                        </button>
{{--                                                    <i class="fa fa-comments comment-icon thumb-size" aria-hidden="true"></i>--}}
                                                                                                            <span class="number-size" data-count-cmt></span>

                                                </span>
                                                @endif
                                                <span class="item-right">
                                                    <button class="btn-primary-outline" type="button" data-post-btn="like" data-item-id="{{$postDetail->id}}">
                                                        <i class="fa fa-thumbs-up thumb-size thumb-dislike" aria-hidden="true" data-post-icon="like"></i>
                                                    </button>
                                                    <a style="color: inherit" href="javascript:void(0)" class="btn-link number-size count-like" data-count-like></a>
                                                </span>
                                            </div>
                                        </div>

                                        @if($postDetail->is_video == \Rikkei\News\Model\Post::TYPE_OTHER)
                                        <div id="content">
                                            {!! $postDetail->desc !!}
                                        </div>
                                        @elseif ($postDetail->is_video == \Rikkei\News\Model\Post::TYPE_AUDIO)
                                            <div id="content">
                                                {!! $postDetail->short_desc !!}
                                            </div>
                                            @if ($attach)
                                                <audio src="{{ asset('storage/'. $attach->path) }}" controls="controls">{{$attach->path}}</audio>
                                            @endif
                                        @else
                                            <div id="content">
                                                {!! $postDetail->short_desc !!}
                                            </div>
                                            <div id="ytb-content" class="youtube-player youtube-player-detail" data-id="{{$postDetail->youtube_id}}"></div>
                                        @endif
                                        <div id="post-hashtag">
                                            <h5 class="hashtag">{{trans('news::view.tags')}}: </h5>
                                            @include('news::post.include.template.hashtag', ['tags' => $arrTags])
                                        </div>
                                    </div>
                                @endif
                            <!-- related -->
                                @if(!$postDetail->is_video)
                                    @include('news::post.include.template.video')

                                    @if ($postRelate && count($postRelate))
                                        <div class="home-missing-articles home-body-section">
                                            <div class="home-missing-articles-title category-title">
                                                <span class="category-title-content">{{ trans('news::view.Related articles') }}</span>
                                            </div>
                                            <div class="missing-articles-content row" id="jsPostRelated">
                                                @foreach($postRelate as $post)
                                                    <div class="missing-articles-item news-item col-sm-6 col-lg-4">
                                                        <div class="missing-articles-item-image news-item-image ">
                                                            <a href="{{ URL::route('news::post.view', ['slug' => $post->slug]) }}">
                                                                <img src="{{ $post->getThumbnail(true) }}" alt="img">
                                                            </a>
                                                        </div>
                                                        <div class="missing-articles-item-content news-item-content">
                                                            <div class="missing-articles-item-title" style="min-height: 47px;">
                                                                <h4 class="body-content limit-line-2"  title="{{$post->title}}">
                                                                    {{ str_limit($post->title, 60, '...') }}</a>
                                                                </h4>
                                                            </div>
                                                            <div class="missing-articles-item-info news-item-content-info">
                                                       <span class="item-left">
                                                           @if($post->author)
                                                               <i class="fa fa-user icon" aria-hidden="true"></i>
                                                               <span class="body-content author">{{$post->author}}</span>
                                                           @endif
                                                        </span>
                                                                <span class="item-right">
                                                                <i class="fa fa-clock-o" aria-hidden="true"></i>
                                                                <span
                                                                        class="body-content created-date">{{ $post->getPublicDate() }}
                                                                </span>
                                                            </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <div class="home-missing-articles home-body-section">
                                        <div class="home-missing-articles-title category-title">
                                            <span class="category-title-content">{{ trans('news::view.Related articles') }}</span>
                                        </div>
                                        <div class="missing-articles-content row">
                                            @foreach($postRelate as $post)
                                                <div class="missing-articles-item news-item col-sm-6 col-lg-4">
                                                    <div class="missing-articles-item-image news-item-image ">
                                                        <a href="{{ URL::route('news::post.view', ['slug' => $post->slug]) }}">
                                                            <img src="{{ $post->getThumbnail(true) }}" alt="img">
                                                        </a>
                                                    </div>
                                                    <div class="missing-articles-item-content news-item-content">
                                                        <div class="missing-articles-item-title" style="min-height: 47px;">
                                                            <h4 class="body-content limit-line-2"  title="{{$post->title}}">
                                                                {{ str_limit($post->title, 60, '...') }}</a>
                                                            </h4>
                                                        </div>
                                                        <div class="missing-articles-item-info news-item-content-info">
                                                       <span class="item-left">
                                                           @if($post->author)
                                                               <i class="fa fa-user icon" aria-hidden="true"></i>
                                                               <span class="body-content author">{{$post->author}}</span>
                                                           @endif
                                                        </span>
                                                            <span class="item-right">
                                                                <i class="fa fa-clock-o" aria-hidden="true"></i>
                                                                <span
                                                                        class="body-content created-date">{{ $post->getPublicDate() }}
                                                                </span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                            <!-- comments -->
                                @if($postDetail->is_set_comment == Post::BE_COMMENTED)
                                    <div class="should-read-articles home-body-section list-comment" id="my-comment">
                                        <div class="should-read-articles-title category-title" data-post-id="{{$postDetail->id}}">
                                            <span class="category-title-content">
                                                Bình luận
                                                <span class="count-comment" data-count-cmt></span>
                                            </span>
                                        </div>
                                        <div class="should-read-articles-content">
                                            <div class="col-xs-12" id="input-parent-comment">
                                                <form id="create_comment" method="post" onsubmit="return false" class="no-validate">
                                                    <input type="hidden" value="{{ csrf_token() }}" name="_token">
                                                    <div class="parent-comment">
                                                        <textarea class="form-control comment textarea-parent root-comment emojis-wysiwyg"
                                                                  rows="2"
                                                                  title="{{trans('news::view.Comment title')}}"
                                                                  placeholder="{{trans('news::view.Comment title')}}"
                                                                  name="comment"
                                                                  id="comment"
                                                                  data-parent-id="0"></textarea>
                                                        <span class="info-comment">{{ trans('news::view.support markdown') }}</span>
                                                        <label id="comment-error"
                                                               class="error"
                                                               for="comment">
                                                        </label>
                                                        <input type="hidden" name="post_id" id="post_id" value="{{$postDetail->id}}">
                                                        <input type="hidden" name="id" id="id" value="">
                                                    </div>
                                                </form>
                                            </div>

                                            <!-- list comment -->
                                            <div class="col-xs-12" id="list-comments">
                                                <div id="comment-content-pending">
                                                </div>
                                                <div id="list-comment" class="read-more-comment">
                                                    @include('news::post.include.comment_list')
                                                </div>
                                                <div id="get-more-comment"></div>
                                                <button id="load_more_comment" class="btn btn-primary hidden" data-post-cmt-load="{{$postDetail->id}}">
                                                    <span>{{trans('news::view.Load more')}} <i class="fa fa-spin fa-refresh hidden"></i></span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <!-- end content left -->



                <!-- content right -->
                    <div class="home-news-body-top col-sm-5 col-md-4 ">
                        <div class="row">
                            <div class="home-top-articles col-sm-12 home-body-section">
                                <div class="home-top-articles-title category-title">
                                    <span class="category-title-content">{{ trans('news::view.Top posts of the month') }}</span>
                                </div>
                                @if(isset($topPost) && count($topPost))
                                    @foreach($topPost as $post)
                                        <div class="home-top-articles-content">
                                            <div class="home-top-articles-item news-item row">
                                                <div class="home-top-articles-item-image col-xs-4">
                                                    <div class="news-item-image">
                                                        <a href="{{ URL::route('news::post.view', ['slug' => $post->slug]) }}">
                                                            <img  src="{{ $post->getThumbnail(true) }}" />
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="home-top-articles-item-content news-item-content col-xs-8">
                                                    <div class="top-articles-item-title" title="{{$post->title}}">
                                                        <h5 class="body-content">
                                                            <a style="color: inherit" href="{{ URL::route('news::post.view', ['slug' => $post->slug]) }}" class="cut_top_title_news">
                                                                {{Viewnews::shortDesc($post->title,20) }}
                                                            </a>
                                                        </h5>
                                                    </div>
                                                    <div class="top-articles-item-info news-item-content-info">
                                                    <span class="item-left">
                                                        @if($post->author)
                                                            <i class="fa fa-user icon" aria-hidden="true"></i>
                                                            <span class="body-content">{{$post->author}}</span>
                                                        @endif
                                                    </span>
                                                        <span class="item-left">
                                                        <i class="fa fa-clock-o" aria-hidden="true"></i> <span class="body-content created-date">{{ $post->getPublicDate() }}</span>
                                                    </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            @include('news::post.include.poster')
                            @include('news::post.include.feedback')
                            <div class="home-feedback home-body-right-section col-sm-12">
                                <h5 class="top-hashtag">{{trans('news::view.top_tags')}}: </h5>
                                <div id="tagcloud" class="hashtag-wrapper">
                                    <ul class="hashtag-list">
                                    @foreach($topHashTag as $key => $count)
                                        @php
                                            $tagsearch = str_replace('#', '', $key);
                                            $tag2 = str_replace('_', ' ', $tagsearch);
                                            if (!$tagsearch) continue;
                                        @endphp
                                        <li class="hashtag-item">
                                            <a href="{{route('news::post.index.cat', ['search' => $tagsearch, 'slug' => 'tags'])}}" rel="{{ $count }}">
                                                <svg style="padding-right: 5px" width="25" height="25" viewBox="0 0 30 30" fill="none"><circle cx="15" cy="15" r="15" fill="#1E1B1D"></circle><path d="M10.78 21h1.73l.73-3.2h2.24l-.74 3.2h1.76l.72-3.2h3.3v-1.6H17.6l.54-2.4H21v-1.6h-2.5l.72-3.2h-1.73l-.73 3.2h-2.24l.74-3.2H13.5l-.73 3.2H9.5v1.6h2.93l-.56 2.4H9v1.6h2.52l-.74 3.2zm2.83-4.8l.54-2.4h2.24l-.54 2.4H13.6z" fill="#fff"></path></svg>
                                                {{ $tag2 }}
                                            </a>
                                        </li>
                                    @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end content right -->

                </div>
            </div>
            @include('news::post.include.post_list_like')
        </section>

        <footer class="news-footer">
            <div class="container">
                <div class="footer-copyright">
                    Copyright 2020 <span class="footer-company">Rikkeisoft</span>. All rights reserved.
                </div>
            </div>
        </footer>
    </div>
@endsection
@section('script')
    <script>
        var comment_url = '{{route('news::post.comment')}}';
        var like_url = '{{route('news::post.like')}}';
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
        var typeComment = '{{ LikeManage::TYPE_COMMENT }}';
        var likeText = "{{ trans('news::view.Like') }}";
        var emoticonPath = "{{ asset('asset_news/images/emoticons') }}";
        var commentPerPage = '{{ PostComment::NEW_PER_PAGE }}';
    </script>
    <script>
        var varGlobPass = {
            urlListLike: '{!! URL::route('news::post.listLike') !!}',
            urlActionLike: '{!! URL::route('news::post.like') !!}',
            urlGetAllCount: '{!! URL::route('news::post.get.all.count') !!}',
            cmtPerpage: '{!!PostComment::NEW_PER_PAGE!!}',
            trans: {
                like: '{!! trans('news::view.Like') !!}',
                dislike: '{!! trans('news::view.DisLike') !!}',
            },
        };
        var globalFeedbacksubmitURL = '{{URL::route('news::opinions.store')}}';
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/showdown/1.9.0/showdown.min.js"></script>
    <!-- Latest compiled and minified JavaScript -->
    <script src="{{ CoreUrl::asset('lib/tag-cloud/jquery.tagcloud.js') }}"></script>
    <script src="{{ CoreUrl::asset('lib/js/emoticons/jquery.emojiarea.min.js') }}"></script>
    <script src="{{asset('lib/slick/js/slick.min.js')}}"></script>
    <script src="{{asset('lib/emojis/emojionearea.min.js')}}"></script>
    <script src="{{asset('lib/simple-scrollbar/simple-scrollbar.min.js')}}"></script>
    <script src="{{ CoreUrl::asset('asset_news/js/common.js') }}"></script>
    {{--<script src="{{ CoreUrl::asset('asset_news/js/home.js') }}"></script>--}}
    <script src="{{ CoreUrl::asset('asset_news/js/news.js') }}"></script>
    <script src="{{ CoreUrl::asset('asset_news/js/detail.js') }}"></script>
    <script src="{{ CoreUrl::asset('asset_news/js/emojiicons.js')}}"></script>
    <script src="{{ CoreUrl::asset('common/js/blacklist.js')}}"></script>
    <script src="{{ CoreUrl::asset('asset_news/js/comment_post.js')}}"></script>
    <script src="{{ CoreUrl::asset('lib/js/autosize.min.js') }}"></script>
@endsection
