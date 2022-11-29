<?php
use Rikkei\News\View\ViewNews;
use Rikkei\News\Model\PostComment;
use Rikkei\News\Model\Post;
use Rikkei\News\Model\Category;
use Carbon\Carbon;

header("Cache-Control: no-store, must-revalidate, max-age=0");
header("Pragma: no-cache");
?>
<section class="news-section home-news-body">
    <div class="home-news-body-container container">
        <div class="home-news-body-row row">
            <div class="home-news-body-left col-sm-7 col-md-8">
                @include('news::post.include.template.header_top')
                <div class="main-section row">
                    <div class="home col-sm-12">
                        <div class="home-latest-news home-body-section">
                            <div class="home-latest-news-title category-title">
                                @if(!isset($searchParams))
                                    <span class="category-title-content">{{ trans('news::view.Post new') }}</span>
                                @else
                                    @if(!$isHashTag)
                                        <span class="category-title-content">{{ trans('news::view.Search Post') }}</span>
                                    @else
                                        <span class="category-title-content">{{ trans('news::view.Search By Tag') }}</span>
                                    @endif
                                @endif
                            </div>
                            {{--//Latest news--}}
                            <div class="home-latest-news-content">
                                <!-- // common block  -->
                                @if(isset($collectionModel) && count($collectionModel))
                                    @foreach($collectionModel as $post)
                                        <div class="home-latest-news-item news-item row">
                                            <div class="home-latest-news-item-image  col-md-5 col-sm-12">
                                                <div class="news-item-image news-item-image-wrapper">
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
                                                        <a style="color: inherit" href="{{ URL::route('news::post.view', ['slug' => $post->slug]) }}" class="cut_title_news">
                                                            {{Viewnews::shortDesc($post->title,40) }}
                                                        </a>
                                                    </h4>
                                                </div>
                                                <div class="latest-news-item-body ">
                                                    <div class="body-content limit-line-5 cut_desc_news">
                                                        {{ ViewNews::shortDesc($post->short_desc, 60) }}
                                                    </div>
                                                </div>
                                                <div class="latest-news-item-info news-item-content-info" data-post-id="{{$post->id}}">
                                                <span class="item-left">
                                                    @if($post->author)
                                                        <i class="fa fa-user icon icon-user" aria-hidden="true"></i>
                                                        <span class="body-content author author-span" title="{{$post->author}}" real-auth="{{$post->author}}" short-auth="{{ViewNews::shortAuthor($post->author,24)}}" short-auth-1024="{{ViewNews::shortAuthor($post->author,10)}}" short-auth-375="{{ViewNews::shortAuthor($post->author,16)}}">{{$post->author}}</span>
                                                    @endif
                                                </span>
                                                    <span class="item-left">
                                                    <i class="fa fa-clock-o icon-clock" aria-hidden="true"></i> <span
                                                                class="body-content created-date data-span">{{ $post->getPublicDate() }}</span>
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
{{--                                                    <button class="btn-primary-outline" type="button" data-post-btn="like" data-item-id="{{$post->id}}">--}}
                                                        <i class="fa fa-thumbs-up thumb-size thumb-dislike icon-dislike" aria-hidden="true" data-post-icon="like"></i>
{{--                                                    </button>--}}
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
                                @else
                                    <div class="blog-list">
                                        <div class="bc-inner">
                                            <p class="not-found-item">{{ trans('news::view.Not found post') }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            {{--//End Lates news--}}
                        </div>
                    @if(!isset($searchParams))
                            <!-- Video -->
                            @include('news::post.include.template.video')

                            <!-- End Video -->
                            <!-- Top member old -->
                            <!-- Unread Post -->
{{--                            <div class="home-missing-articles home-body-section">--}}
{{--                                <div class="home-missing-articles-title category-title">--}}
{{--                                    <span class="category-title-content">{{ trans('news::view.Unread posts') }}</span>--}}
{{--                                    <a href="{{ URL::route('news::post.index.cat', ['slug' => Category::SLUG_UNREAD_POST]) }}" class="see-more item-right">{{ trans('news::view.View more') }}<i class="fa fa-chevron-right"--}}
{{--                                                                                                                                                                                                 aria-hidden="true"></i></a>--}}
{{--                                </div>--}}
{{--                                @if(isset($postsMiss) && count($postsMiss))--}}
{{--                                    <div class="missing-articles-content row" id="jsPostRelated">--}}
{{--                                        @foreach($postsMiss as $post)--}}
{{--                                            <div class="missing-articles-item news-item col-md-6 col-lg-4">--}}
{{--                                                <div class="missing-articles-item-image news-item-image ">--}}
{{--                                                    <a href="{{ URL::route('news::post.view', ['slug' => $post->slug]) }}">--}}
{{--                                                        <img  src="{{ $post->getThumbnail(true) }}" />--}}
{{--                                                    </a>--}}
{{--                                                </div>--}}
{{--                                                <div class="missing-articles-item-content news-item-content">--}}
{{--                                                    <div class="missing-articles-item-title">--}}
{{--                                                        <h4 class="body-content limit-line-2"  title="{{$post->title}}">--}}
{{--                                                            <a style="color: inherit" href="{{ URL::route('news::post.view', ['slug' => $post->slug]) }}">--}}
{{--                                                                {{Viewnews::shortDesc($post->title,20) }}--}}
{{--                                                            </a>--}}
{{--                                                        </h4>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="missing-articles-item-info news-item-content-info">--}}
{{--                                                    <span class="item-left">--}}
{{--                                                         @if($post->author)--}}
{{--                                                            <i class="fa fa-user icon" aria-hidden="true"></i>--}}
{{--                                                            <span class="body-content author" data-toggle="tooltip" title="{{$post->author}}" real-auth="{{$post->author}}" short-auth="{{ViewNews::shortAuthor($post->author,24)}}" short-auth-1024="{{ViewNews::shortAuthor($post->author,10)}}" short-auth-375="{{ViewNews::shortAuthor($post->author,16)}}">{{$post->author}}</span>--}}
{{--                                                        @endif--}}
{{--                                                    </span>--}}
{{--                                                        <span class="item-left">--}}
{{--                                                        <i class="fa fa-clock-o" aria-hidden="true"></i> <span--}}
{{--                                                                    class="body-content created-date">{{ $post->getPublicDate() }}</span>--}}
{{--                                                    </span>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                        @endforeach--}}
{{--                                    </div>--}}
{{--                                @endif--}}
{{--                            </div>--}}
                            <!-- End Unread Post -->


                        <!-- Yume -->
                            <div class="home-yume home-body-section">
                                <div class="home-yume-title category-title">
                                    <span class="category-title-content">{{trans('news::view.Yume')}}</span>
                                    <a href="{{ URL::route('news::post.index.cat', ['slug' => Category::SLUG_YUME]) }}"class="see-more item-right">{{ trans('news::view.View more') }} <i class="fa fa-chevron-right"
                                                                                                                                                                                          aria-hidden="true"></i></a>
                                </div>
                                <div class="home-yume-content row">
                                    <div class="home-yume-item">
                                        <div class="home-yume-highlight">
                                            <div id="yume">
                                                @if(!$collectionMagazine->isEmpty())
                                                    @foreach($collectionMagazine as $post)
                                                        <?php
                                                        $image = $post->images->first();
                                                        $imageSrc = null;
                                                        if ($image) {
                                                            $imageSrc = $image->getSrc('slide');
                                                        }
                                                        if (!$imageSrc) {
                                                            $imageSrc = asset('common/images/noimage.png');
                                                        }
                                                        ?>
                                                        <div class="item">
                                                            <div class="content">
                                                                <a target="_blank" href="{{ route('magazine::read', ['id' => $post->id, 'slug' => $post->slug]) }}">
                                                                    <img class="content-image" src="{{ $imageSrc }}">
                                                                    <div class="content-overlay"></div>
                                                                </a>
                                                                <div class="content-details">
                                                                    <h3 class="content-title">
                                                                        <a target="_blank" href="{{ route('magazine::read', ['id' => $post->id, 'slug' => $post->slug]) }}">
                                                                            {{ $post->name }}
                                                                        </a>
                                                                    </h3>
                                                                    <span class="item-left">
                                                        <i class="fa fa-clock-o" aria-hidden="true"></i> <span
                                                                                class="body-content created-date">{{ $post->created_at->format('H:i d/m/Y') }}</span>
                                                      </span>
                                                                </div><!-- /.carousel-caption -->
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div><!-- /#rikkeiCarousel -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="home-news-body-top col-sm-5 col-md-4 ">
                <div class="row">
                    {{--Top Post--}}
                    <div class="home-news-body-top home-top-articles home-body-section col-sm-12">
                        <div class="home-top-articles-title category-title">
                            <span class="category-title-content">{{ trans('news::view.Top posts of the month') }}</span>
                        </div>
                        <div class="home-top-articles-content">
                            @if($topPost && count($topPost))
                                @foreach($topPost as $key => $value)
                                    <div class="home-top-articles-item news-item row" data-post-id="{{$value->id}}">
                                        <div class="home-top-articles-item-image col-xs-4">
                                            <div class=" news-item-image">
                                                <a href="{{ URL::route('news::post.view', ['slug' => $value->slug]) }}">
                                                    <img src="{{ $value->getThumbnail(true) }}" alt="img">
                                                </a>
                                            </div>
                                        </div>
                                        <div class="home-top-articles-item-content news-item-content col-xs-8">
                                            <div class="top-articles-item-title"  title="{{$value->title}}">
                                                <h5 class="body-content">
                                                    <a style="color: inherit" href="{{ URL::route('news::post.view', ['slug' => $value->slug]) }}" class="cut_top_title_news">
                                                        {{ Viewnews::shortDesc($value->title,19) }}
                                                    </a>
                                                </h5>
                                            </div>
                                            <div class="top-icon-item-info news-item-content-info">
{{--                                                @if($value->author)--}}
{{--                                                    <span class="item-left">--}}
{{--                                              <i class="fa fa-user icon" aria-hidden="true"></i> <span class="body-content author"> {{ViewNews::shortAuthor($value->author,24)}}</span>--}}
{{--                                            </span>--}}
{{--                                                @endif--}}
{{--                                                <span class="item-right">--}}
{{--                                          <i class="fa fa-clock-o" aria-hidden="true"></i> <span--}}
{{--                                                            class="body-content created-date">{{ $value->getPublicDate() }}</span>--}}
{{--                                        </span>--}}
                                                <span class="item-right">
                                                        <i class="fa fa-thumbs-up thumb-size thumb-dislike icon-dislike" aria-hidden="true" data-post-icon="like"></i>
                                                    <a style="color: inherit" href="javascript:void(0)" class="btn-link number-size number-count count-like" data-count-like></a>
                                                </span>
                                                @if($value->is_set_comment)
                                                    <span class="item-right">
                                                        <i class="fa fa-comments comment-icon thumb-size icon-comment" aria-hidden="true"></i>
                                                        <span class="number-size number-count" data-count-cmt></span>
                                                    </span>
                                                @endif
                                                <span class="item-right">
                                                    <i class="fa fa-eye eye-view icon-eye" aria-hidden="true"></i>
                                                    <span class="number-size number-count" style="text-decoration:none;" data-count-view></span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    {{--End Top Post--}}

                    <div class="home-news-body-right col-sm-12">
                        <!-- Top Member -->
                        <div class="home-top-members home-body-section">
                            <div class="home-top-members-title category-title">
                                <span class="category-title-content">{{ trans('news::view.Top members') }}</span>
                            </div>
                            <div class="top-members-wrapper circlethingholder">
                                @if(isset($topMember) && count($topMember))
                                    @foreach($topMember as $key => $value)
                                        <div class="top-members-item">
                                            <a href="{{route('contact::index', ['s' => $value->email])}}" target="_blank">
                                                <div class="circlething" style="
                                                        background-image: url({{ $value->avatar_url }});
                                                        background-position: center;
                                                        background-size: cover;
                                                        ">
                                                    <div class="circlething__badge">
                                                        <div class="circlething__badge--inner">
                                                            <span class="order-number">{{ ($key + 1) }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="circlething__name">
                                                    <span class="circlething_name_contet">{{ $value->name }}</span>
                                                </div>
                                            </a>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        <!-- End Top Member  -->
                    </div>
                    <div class="home-news-body-right col-sm-12 home-body-section">
                        <div class="home-body-section">
                            @include('news::post.include.template.weather')
                        </div>
                    </div>
                    <div class="home-news-body-right col-sm-12">
                        <div class="main-section row">
                            @include('news::post.include.poster')
                        </div>

                        <div class="main-section row">
                            @include('news::post.include.feedback')
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
