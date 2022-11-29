<?php $guest_detail = true; ?>
@extends('layouts.guest')
<?php 
use Rikkei\Core\View\CoreUrl; 
use Rikkei\News\View\ViewNews;
header("Cache-Control: no-store, must-revalidate, max-age=0");
header("Pragma: no-cache");
?>
@section('title')
	{{ $titleHeadPage }}
@endsection
@section('css')
@include('layouts.include.css_default')
<link rel="stylesheet" href="{{ CoreUrl::asset('asset_news/css/news.css') }}" />
@endsection
<div class="">
    <img src="{{ CoreUrl::asset('common/images/logo-rikkei.png') }}" class="guest-logo center-block img-responsive">
</div>
@section('content')

<div class="" autocomplete="off">
    <div class="row">

        <div class="col-md-12 blog-content">
            <div class="blog-detail">
                <div class="bc-inner">
                    @if (!$postDetail)
                        <p class="not-found-item">{{ trans('news::view.Not found post') }}</p>
                    @else
                        <div class="post-detail">
                            <h2 class="bci-header">{{ $postDetail->title }}</h2>
                            <div class="row">
                                 <p class="post-meta post-date col-sm-6" style="padding-top: 20px;">{{ $postDetail->getPublicDate() }}</p>
                                 <div class="post-like col-sm-6 text-right">
                                    <?php 
                                        $greater1 = false;
                                        $greater2 = false;
                                        $likeReal = 0;
                                        $viewReal = 0;
                                        $likeCount = ViewNews::compactTotal($postDetail->getTotalLike(), $likeReal, $greater1);
                                        $viewCount = ViewNews::compactTotal($postDetail->getToltalView(),$viewReal, $greater2);
                                    ?>
                                     
                                    <span class="btn-link" id="countView{{$postDetail->id}}" style="font-size: 17px" @if ($greater2) data-toggle="tooltip" title="{{ $likeReal }}"@endif>{{$viewCount}}</span>
                                     
                                    <i class="fa fa-eye eye-detail" aria-hidden="true"></i>
                                    <span class="btn-link" id="countLike{{$postDetail->id}}" style="font-size: 17px"@if ($greater1) data-toggle="tooltip" title="{{ $likeReal }}"@endif>{{ $likeCount }}</span>

                                    <button class="btn-primary-outline" title="{{ trans('news::view.Like') }}"  link="{{ URL::route('news::post.like') }}"><i class="fa fa-thumbs-up thumb-dislike size-detail" aria-hidden="true"></i></button>

                                 </div>
                             </div>
                            <div class="post-desc cke" style="padding-top: 15px">
                                {!! $postDetail->desc !!}
                            </div>
                            <div>
                                <p class="author" style="padding-top: 27px;">@if($postDetail->author)- {{ $postDetail->author }} -@endif</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
 <script src="{{ CoreUrl::asset('asset_news/js/news.js') }}"></script>
 <script type="text/javascript">
     $('.policy').hide();
     $('.float-left').hide();
     var url = '<?php echo CoreUrl::asset("common/images/login-background.png");?>';

     $('body').css('background-image', 'url(' + url + ')');
 </script>
@endsection