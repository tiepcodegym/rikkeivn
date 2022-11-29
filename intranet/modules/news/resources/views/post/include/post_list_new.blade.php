<?php
use Rikkei\News\View\ViewNews;
use Rikkei\News\Model\PostComment;
use Rikkei\News\Model\Post;

?>
<div class="home-wrapper" id="homeWrapper">
    {{--@include('news::post.include.header_news')--}}
{{--    @include('news::post.include.template.header_top')--}}
    @include('news::post.include.template.post_list_new')
</div>
