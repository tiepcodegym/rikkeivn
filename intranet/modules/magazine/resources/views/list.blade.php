@extends('layouts.blog')

@section('title', trans('magazine::view.Yume magazine'))

@section('css')
<?php use Rikkei\Core\View\CoreUrl; ?>
<link rel="stylesheet" href="/lib/swiper-3d/css/swiper.css" />
<link rel="stylesheet" href="{{ CoreUrl::asset('asset_news/css/news.css') }}" />
<link rel="stylesheet" href="{{ CoreUrl::asset('magazine/css/swiper-custom.css') }}" />
@endsection

@section('after_header')
<div class="after-header">
    <div class="header-banner">
        <div class="bgr-text">
            <h3 class="hb-text">{{ trans('magazine::view.Yume magazine') }}</h3>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="blog-main">
    <div class="row">
        @include('news::post.include.sidebar')
        <div class="col-md-9 blog-content">
            <div class="blog-list">
                @if (!$collectionModel->isEmpty())
                    <div class="bc-inner">

                        <div class="swiper-container">
                            <div class="swiper-wrapper">
                                @foreach($collectionModel as $post)    
                                    <?php
                                    $image = $post->images->first();
                                    $imageSrc = null;
                                    if ($image) {
                                        $imageSrc = $image->getSrc('slide');
                                    }
                                    if (!$imageSrc) {
                                        $imageSrc = URL::asset('common/images/noimage.png');
                                    }
                                    ?>
                                    <div class="swiper-slide">
                                        <div class="post-slide">
                                            <a target="_blank" href="{{ route('magazine::read', ['id' => $post->id, 'slug' => $post->slug]) }}"><img src="{{ $imageSrc }}"></a>
                                            <div class="post-desc">
                                                <h3 class="post-title">
                                                    <a target="_blank" href="{{ route('magazine::read', ['id' => $post->id, 'slug' => $post->slug]) }}">
                                                        {{ $post->name }}
                                                    </a>
                                                </h3>
                                                <div class="post-date">{{ $post->getPublicDate() }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- If we need navigation buttons -->
                            <div class="swiper-button-prev"></div>
                            <div class="swiper-button-next"></div>

                            <!-- If we need scrollbar -->
                            <div class="swiper-scrollbar"></div>
                        </div>

                    </div>
                @else
                    <p class="not-found-item">{{ trans('news::view.Not found post') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')

<script src="/lib/swiper-3d/js/swiper.jquery.min.js"></script>
<script>   
    $(document).ready(function () {
        
        var mySwiper = new Swiper ('.swiper-container', {
            paginationClickable: true,
            effect: 'coverflow',
            slidesPerView: 3,
            centeredSlides: true,
            preventClicks: false,
            autoHeight: true,
            coverflow: {
                rotate: 50,
                stretch: 0,
                depth: 100,
                modifier: 1,
                slideShadows : true
            },
            nextButton: '.swiper-button-next',
            prevButton: '.swiper-button-prev',
            scrollbar: '.swiper-scrollbar',
            scrollbarHide: false
        });
        mySwiper.slideTo(1);
        
    });
</script>
</body>

@endsection