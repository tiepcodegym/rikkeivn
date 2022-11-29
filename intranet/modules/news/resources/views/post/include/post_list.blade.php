<?php 
use Rikkei\News\View\ViewNews;
use Rikkei\News\Model\PostComment;
use Rikkei\News\Model\Post;
use Carbon\Carbon;

header("Cache-Control: no-store, must-revalidate, max-age=0");
header("Pragma: no-cache");
?>
@if(isset($collectionModel) && count($collectionModel))
    <?php $i = 0; ?>
    @foreach($collectionModel as $post)
        <?php $i++; ?>
        @if ($i % 3 == 1)
        <div class="row bl-row">
        @endif
            <div class="col-md-4 hight-same bl-post-item">
                <div class="bc-item">
                    <h3 class="bci-header">
                        <a href="{{ URL::route('news::post.view', ['slug' => $post->slug]) }}">
                            {{Viewnews::shortDesc($post->title,20) }}
                        </a>
                    </h3>
                    <div class="thumbnail">
                        <a href="{{ URL::route('news::post.view', ['slug' => $post->slug]) }}">
                            <img class="portrait" src="{{ $post->getThumbnail(true) }}" />
                            @if (ViewNews::getDiffDay($post->public_at) < 7)
                            <img class="new-icon" src="{{ asset('asset_news/images/new_icon_10.png') }}" />
                            @endif
                        </a>
                        
                    </div>
                    <div class="row">
                        <p class="post-meta date col-xs-6">{{ $post->getPublicDate() }}</p>
                        <div class="post-like col-xs-6 text-right" data-post-id="{{$post->id}}">
                            <span class="number-size" style="text-decoration:none;" data-count-view></span>
                            <i class="fa fa-eye eye-view" aria-hidden="true"></i>

                            <a href="javascript:void(0)" class="btn-link number-size count-like" data-count-like></a>
                            <button class="btn-primary-outline" type="button" data-post-btn="like" data-item-id="{{$post->id}}">
                                <i class="fa fa-thumbs-up thumb-size thumb-dislike" aria-hidden="true" data-post-icon="like"></i>
                            </button>
                            @if($post->is_set_comment)
                                <span class="number-size" data-count-cmt></span>
                                <i class="fa fa-comments comment-icon thumb-size" aria-hidden="true"></i>
                            @endif
                        </div>
                    </div>
                    <div class="post-desc cke">
                        {{ ViewNews::shortDesc($post->short_desc, 20) }}
                    </div>
                    <div class="row">
                        <div class="col-xs-8 post-author">
                        @if($post->author)
                            <span data-toggle="tooltip" title="{{$post->author}}" real-auth="{{$post->author}}" short-auth="{{ViewNews::shortAuthor($post->author,24)}}" short-auth-1024="{{ViewNews::shortAuthor($post->author,10)}}" short-auth-375="{{ViewNews::shortAuthor($post->author,16)}}"></span>
                        @endif
                        </div>
                        <div class="col-xs-4 post-read-more">
                            <a href="{{ URL::route('news::post.view', ['slug' => $post->slug]) }}">{{ trans('news::view.Read more') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        @if ($i % 3 == 0)
            </div>
        @endif
    @endforeach
    @if ($i % 3 != 0)
        </div>
    @endif
@else
<p class="not-found-item">{{ trans('news::view.Not found post') }}</p>
@endif

@if(isset($collectionModel) && $collectionModel->total())
    <div class="pagination-wrapper text-center">
        @if (isset($searchParams) && $searchParams)
            {{ $collectionModel->appends(['search' => $searchParams])->links() }}
        @else
            {{ $collectionModel->links() }}
        @endif
    </div>
@endif
