@extends('layouts.blog')

@section('title')
{{ $titleHeadPage }}
@endsection

@section('css')
<?php
use Rikkei\Core\View\CoreUrl;
use Rikkei\Core\View\CacheBase;
?>
<link rel="stylesheet" href="{{asset('lib/slick/cs/slick.css')}}">
<link rel="stylesheet" href="{{asset('lib/slick/cs/slick-theme.css')}}">
<link rel="stylesheet" href="{{asset('lib/simple-scrollbar/simple-scrollbar.css')}}">
<link rel="stylesheet" href="{{ CoreUrl::asset('asset_news/css/external.css') }}" />
<link rel="stylesheet" href="{{ CoreUrl::asset('asset_news/css/common.css') }}" />
<link rel="stylesheet" href="{{ CoreUrl::asset('asset_news/css/home.css') }}" />

@endsection

@section('after_header')
    <?php
        if (!$isCategory && $categories = CacheBase::getFile(CacheBase::HOME_PAGE, 'categories')) {
        } else {
            $categories = view('news::post.include.header_news', array_except(get_defined_vars(), array('__data', '__path')))->render();
        }
        if (!$isCategory) {
            CacheBase::putFile(CacheBase::HOME_PAGE, 'categories', $categories);
        }
        echo $categories;
    ?>
@endsection

@section('content')
    @if(!$isCategory)
        @include('news::post.include.post_list_new')
    @else
        @include('news::post.include.post_category')
    @endif
    @include('news::post.include.post_list_like')
@endsection


@section('footer')
    @include('news::post.include.footer')
@endsection

@section('script')
<script>
var varGlobPass = {
    urlListLike: '{!! URL::route('news::post.listLike') !!}',
    urlActionLike: '{!! URL::route('news::post.like') !!}',
    urlGetAllCount: '{!! URL::route('news::post.get.all.count') !!}',
    trans: {
        like: '{!! trans('news::view.Like') !!}',
        dislike: '{!! trans('news::view.DisLike') !!}',
    },
};

var globalFeedbacksubmitURL = '{{URL::route('news::opinions.store')}}';

</script>
<!-- Latest compiled and minified JavaScript -->
<script src="{{asset('lib/slick/js/slick.min.js')}}"></script>
<script src="{{asset('lib/simple-scrollbar/simple-scrollbar.min.js')}}"></script>
<script src="{{ CoreUrl::asset('asset_news/js/common.js') }}"></script>
<script src="{{ CoreUrl::asset('asset_news/js/home.js') }}"></script>
<script src="{{ CoreUrl::asset('asset_news/js/news.js') }}"></script>
@endsection
