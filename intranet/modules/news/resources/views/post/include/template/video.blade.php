<?php
use Rikkei\News\Model\Category;
use Rikkei\News\View\ViewNews;

?>
<div class="home-videos home-body-section">
    <div class="home-videos__title category-title">
        <span class="category-title-content">{{ trans('news::view.Videos') }}</span>
        <a href="{{ URL::route('news::post.index.cat', ['slug' => Category::SLUG_VIDEO]) }}" class="see-more item-right">{{ trans('news::view.View more') }}<i class="fa fa-chevron-right"
                                                                                                                                                                     aria-hidden="true"></i></a>
    </div>
    @if(isset($videos) && count($videos))
        <div class="home-videos__content">
            <div class="home-videos__content__left">
                <div class="youtube-player primary-video-wrapper">
                    <div id="js-primary-video"></div>
                </div>
            </div>
            <div class="home-videos__content__right">
                <div class="home-videos__content__right_items"  ss-container>
                    @foreach($videos as $video)
                        <div class="home-videos__content__right_item" data-id="{{$video->youtube_id}}">
                            <div class="youtube-player youtube-player-list youtube-cover" data-id="{{$video->youtube_id}}"></div>
                            <div class="video-desc">
                                <div class="video-desc-short">
                                    <span>
                                        {{$video->title}}
                                    </span>
                                </div>
                                <div class="video-desc-info news-item-content-info" data-post-id="{{$video->id}}">
                                    {{--<span class="item-left">--}}
                                        {{--@if($video->author)--}}
                                            {{--<i class="fa fa-user icon" aria-hidden="true"></i>--}}
                                            {{--<span class="body-content author" data-toggle="tooltip" title="{{$video->author}}" real-auth="{{$video->author}}" short-auth="{{ViewNews::shortAuthor($post->author,24)}}" short-auth-1024="{{ViewNews::shortAuthor($post->author,10)}}" short-auth-375="{{ViewNews::shortAuthor($video->author,16)}}">{{$video->author}}</span>--}}
                                        {{--@endif--}}
                                    {{--</span>--}}
                                    <span class="item-left">
                                        <i class="fa fa-clock-o icon-clock" aria-hidden="true"></i> <span
                                        class="body-content created-date">{{ $video->getPublicDate() }}</span>
                                    </span>
                                    <span class="item-right">
                                        <i class="fa fa-eye eye-view " aria-hidden="true"></i>
                                        <span class="number-size" style="text-decoration:none;" data-count-view></span>
                                    </span>
                                    @if($video->is_set_comment)
                                        <span class="item-right">
                                            <i class="fa fa-comments comment-icon thumb-size" aria-hidden="true"></i>
                                            <span class="number-size" data-count-cmt></span>
                                        </span>
                                    @endif
                                    <span class="item-right">
                                        <button class="btn-primary-outline" type="button" data-post-btn="like" data-item-id="{{$video->id}}">
                                            <i class="fa fa-thumbs-up thumb-size thumb-dislike" aria-hidden="true" data-post-icon="like"></i>
                                        </button>
                                        <a style="color: inherit" href="javascript:void(0)" class="btn-link number-size count-like" data-count-like></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
