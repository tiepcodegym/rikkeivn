<!DOCTYPE html>
<html>
<head>
   <?php
    use Rikkei\SlideShow\Model\VideoDefault;
    use Rikkei\SlideShow\Model\Slide;
    use Rikkei\SlideShow\View\View;
    use Rikkei\SlideShow\View\ImageHelper;
    use Rikkei\Core\Model\CoreConfigData;
    use Rikkei\SlideShow\Model\SlideQuotation;
    use Rikkei\Core\View\View as CoreView;
    use Rikkei\Core\View\CoreUrl;
    ?>
    <title>{{trans('slide_show::view.Rikkeisoft Intranet')}}</title>
    <meta content='{{trans('slide_show::view.Rikkeisoft Intranet')}}' name='description' />
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no">
    <link rel="icon" href="{{ URL::asset('favicon.ico') }}" type="image/x-icon">
    <link rel="Shortcut Icon" type="image/x-icon" href="{{ URL::asset('favicon.ico') }}">
    <link rel="stylesheet" type="text/css" href="{{asset('lib/swiper/css/swiper.min.css')}}" />
    <link rel="stylesheet" type="text/css" href="{{asset('lib/flipclock/css/flipclock.css')}}" />
    <link rel="stylesheet" href="{{ URL::asset('lib/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ CoreUrl::asset('slide_show/css/styles.css')}}" />
    <link rel="stylesheet" href="{{ URL::asset('adminlte/bootstrap/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ URL::asset('common/css/login.css') }}" />
</head>
<body>
    <div class="content-slides">
        <div class="content-slide">
            <div class="container-fluid">
                <div class="row">
                    <div class="swiper-container col-sm-12 col-md-12 col-lg-12">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide display-initial">
                                <div class="slide-bg-image">
                                    <img class="slide-avatar-image" src="{{ $image }}">
                                    <div class="slide-bday-text">
                                        {!! $content !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ URL::asset('adminlte/plugins/jQuery/jquery-2.2.3.min.js') }}"></script>
    <script src="{{asset('lib/swiper/js/swiper.jquery.min.js')}}"></script>
    <script src="{{ URL::asset('lib/js/jquery.backstretch.min.js') }}"></script>
</body>
</html>