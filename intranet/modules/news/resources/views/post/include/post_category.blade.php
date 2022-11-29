<?php
use Rikkei\News\View\ViewNews;
use Rikkei\News\Model\PostComment;
use Rikkei\News\Model\Post;
use Carbon\Carbon;

header("Cache-Control: no-store, must-revalidate, max-age=0");
header("Pragma: no-cache");
?>
<section class="news-section home-news-body">
    <div class="home-news-body-container container">
        <div class="home-news-body-row row">
            <div class="home-news-body-left col-sm-7 col-md-8">
                <div class="main-section row">
                    <div class="home-categories home-body-section col-sm-12">
                        <div class="categories-title">
                            <div class="breadcrumb">
                                <ul>
                                    <li>
                                        <a href="">{{ trans('news::view.Category') }}</a>
                                    </li>
                                    <li>
                                        <a href="">{{ $headingTitle }}</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="home-latest-news-content">
                            @if(isset($collectionModel) && count($collectionModel))
                                @foreach($collectionModel as $post)
                                    <div class="home-latest-news-item news-item row">
                                        <div class="home-latest-news-item-image  col-md-5 col-sm-12">
                                            <div class="news-item-image-wrapper news-item-image">
                                                <a href="{{ URL::route('news::post.view', ['slug' => $post->slug]) }}">
                                                    <img src="{{ $post->getThumbnail(true) }}" />
                                                    @if (ViewNews::getDiffDay($post->public_at) < 7)
                                                        <img class="new-icon" src="{{ asset('asset_news/images/new_icon_10.png') }}" />
                                                    @endif
                                                </a>
                                            </div>
                                        </div>
                                        <div class="home-latest-news-item-content news-item-content col-md-7  col-sm-12">
                                            <div class="latest-news-item-title">
                                                <h4 class="body-content limit-line-2"  title="{{$post->title}}">
                                                    <a style="color: inherit" href="{{ URL::route('news::post.view', ['slug' => $post->slug]) }}">
                                                        {{Viewnews::shortDesc($post->title,20) }}
                                                    </a>
                                                </h4>
                                            </div>
                                            <div class="latest-news-item-body ">
                                                @if($post->is_video != \Rikkei\News\Model\Post::TYPE_AUDIO && isset($post->path))
                                                <div class="body-content limit-line-5">
                                                    {{ ViewNews::shortDesc($post->short_desc, 20) }}
                                                </div>
                                                @else
                                                    <audio src="{{ asset('storage/'. $post->path) }}" controls="controls">{{$post->path}}</audio>
                                                @endif
                                            </div>
                                            <div class="latest-news-item-info news-item-content-info" data-post-id="{{$post->id}}">
                                                <span class="item-left">
                                                    @if($post->author)
                                                        <i class="fa fa-user icon icon-user" aria-hidden="true"></i>
                                                        <span class="body-content author" data-toggle="tooltip" title="{{$post->author}}" real-auth="{{$post->author}}" short-auth="{{ViewNews::shortAuthor($post->author,24)}}" short-auth-1024="{{ViewNews::shortAuthor($post->author,10)}}" short-auth-375="{{ViewNews::shortAuthor($post->author,16)}}">{{$post->author}}</span>
                                                    @endif
                                                </span>
                                                <span class="item-left">
                                                    <i class="fa fa-clock-o icon-clock" aria-hidden="true"></i> <span
                                                            class="body-content created-date">{{ $post->getPublicDate() }}</span>
                                                </span>

                                                <span class="item-right">
                                                    <i class="fa fa-eye eye-view icon-eye" aria-hidden="true"></i>
                                                                                                        <span class="number-size number-count" style="text-decoration:none;" data-count-view></span>

                                                 </span>
                                                @if($post->is_set_comment)
                                                    <span class="item-right">
                                                        <i class="fa fa-comments comment-icon thumb-size icon-comment" aria-hidden="true"></i>
                                                                                                                <span class="number-size number-count" data-count-cmt></span>

                                                    </span>
                                                @endif
                                                <span class="item-right">
                                                    <button class="btn-primary-outline" type="button" data-post-btn="like" data-item-id="{{$post->id}}">
                                                        <i class="fa fa-thumbs-up thumb-size thumb-dislike icon-dislike" aria-hidden="true" data-post-icon="like"></i>
                                                    </button>
                                                                                                        <a style="color: inherit" href="javascript:void(0)" class="btn-link number-size number-count count-like" data-count-like></a>

                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                    <nav aria-label="Page navigation" class="">
                                        @if(isset($collectionModel) && $collectionModel->total())
                                            <div class="pagination-wrapper">
                                                @if (isset($searchParams) && $searchParams)
                                                    {{ $collectionModel->appends(['search' => $searchParams])->links() }}
                                                @else
                                                    {{ $collectionModel->links() }}
                                                @endif
                                            </div>
                                        @endif
                                    </nav>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="home-news-body-top  col-sm-5 col-md-4 ">
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
                                           <div class="news-item-image ">
                                               <a href="{{ URL::route('news::post.view', ['slug' => $post->slug]) }}">
                                                   <img  src="{{ $post->getThumbnail(true) }}" />
                                               </a>
                                           </div>
                                       </div>
                                       <div class="home-top-articles-item-content news-item-content col-xs-8">
                                           <div class="top-articles-item-title"  title="{{$post->title}}">
                                               <h5 class="body-content">
                                                   <a style="color: inherit" href="{{ URL::route('news::post.view', ['slug' => $post->slug]) }}">
                                                       {{Viewnews::shortDesc($post->title,20) }}
                                                   </a>
                                               </h5>
                                           </div>
                                           <div class="top-articles-item-info news-item-content-info">
                                              <span class="item-left">
                                                  @if($post->author)
                                                      <i class="fa fa-user icon" aria-hidden="true"></i>
                                                      <span class="body-content author" data-toggle="tooltip" title="{{$post->author}}" real-auth="{{$post->author}}" short-auth="{{ViewNews::shortAuthor($post->author,24)}}" short-auth-1024="{{ViewNews::shortAuthor($post->author,10)}}" short-auth-375="{{ViewNews::shortAuthor($post->author,16)}}">{{$post->author}}</span>
                                                  @endif
                                              </span>
                                               <span class="item-left">
                                                        <i class="fa fa-clock-o" aria-hidden="true"></i> <span
                                                           class="body-content created-date">{{ $post->getPublicDate() }}</span>
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
               </div>
            </div>

        </div>
</section>
