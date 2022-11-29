<?php $guest_detail = true; ?>
@extends('layouts.guest')
<?php 
use Rikkei\Core\View\CoreUrl; 
use Rikkei\News\View\ViewNews;
header("Cache-Control: no-store, must-revalidate, max-age=0");
header("Pragma: no-cache");
?>
@section('title')
	Không tìm thấy bài đăng nào
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
                    <div class="post-detail">
                       <p class="bci-header">{{ trans('news::view.Not found post') }}</p>
                    </div>
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