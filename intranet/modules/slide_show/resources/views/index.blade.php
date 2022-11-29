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
    
    $sizeResizeWidth = CoreConfigData::get('slide_show.size_image_show.width');
    $sizeResizeHeight = CoreConfigData::get('slide_show.size_image_show.height');
    $imageHelper = new ImageHelper();
    ?>
    @if($slide)
        <title>{{$slide->title}}</title>
        <meta content='{{$slide->title}}' name='description' />
    @else
        <title>{{trans('slide_show::view.Rikkeisoft Intranet')}}</title>
        <meta content='{{trans('slide_show::view.Rikkeisoft Intranet')}}' name='description' />
    @endif
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no">
    <link rel="icon" href="{{ URL::asset('favicon.ico') }}" type="image/x-icon">
    <link rel="Shortcut Icon" type="image/x-icon" href="{{ URL::asset('favicon.ico') }}">
    <link rel="stylesheet" type="text/css" href="{{asset('lib/swiper/css/swiper.min.css')}}" />
    <link rel="stylesheet" type="text/css" href="{{asset('lib/flipclock/css/flipclock.css')}}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" type="text/css" href="{{ CoreUrl::asset('slide_show/css/styles.css')}}" />
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ URL::asset('common/css/login.css') }}" />
</head>
<body>
    <?php
        $isShowCountdow = false;
        if ($slide) {
            if ($slide->option == Slide::OPTION_NOMAL) {
                if (isset($secondToBirthday)) {
                    $isShowCountdow = true;
                }
            }
        } else {
            if(isset($secondToBirthday)) {
                $isShowCountdow = true;
            }
        }
    ?>
    @if(View::checkDisplayBirthday())
        @if ($isShowCountdow)
            <div class="birthday">
        @else
            <div class="birthday" style="display: none">
        @endif
        
        @if(isset($secondToBirthday) && $secondToBirthday > 0)
            <div class="div-clock" data-second="{{$secondToBirthday}}">
                <span class="clock"></span>
                <span class="pull-right message-to-the-party text-uppercase">{{ trans('slide_show::view.To celebrate')}}</span>
            </div>
        @else
            <div class="is-show-message">
                <span class="logo-lg pull-left">
                    <img src="{{ asset('/common/images/logo-5-year.png') }}" class="logo-five-year" />
                </span>
                <span class="message-birthday text-uppercase">{{ trans('slide_show::message.Welcome birthday companies')}}</span>
            </div>
        @endif
        
        <div class="message-birthday-hide" style="display: none">
            <span class="logo-lg pull-left">
                <img src="{{ asset('/common/images/logo-5-year.png') }}" class="logo-five-year" />
            </span>
            <span class="message-birthday text-uppercase">{{ trans('slide_show::message.Welcome birthday companies')}}</span>
        </div>
    </div>
    @endif
    
    <div class="content-slides">
        @include('slide_show::templates.content-slide')
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.3/jquery.min.js"></script>
    <script src="{{asset('lib/swiper/js/swiper.jquery.min.js')}}"></script>
    <script src="{{asset('lib/flipclock/js/flipclock.js')}}"></script>
    <script src="{{ URL::asset('lib/js/jquery.backstretch.min.js') }}"></script>
    <script>
        var secondPlaySlide = '{{$secondPlaySlide}}',
            isPreview = '{{$isPreview}}';
        var siteConfigGlobal = {
            token: '{{ csrf_token() }}'
        };
        var urlLoadSlideAjax = '{{route('slide_show::slide-ajax')}}';
        var urlGetFileForSlider = '{{route('slide_show::get-file-for-slide')}}';
        jQuery(document).ready(function($) {
            $.backstretch('{{ URL::asset('common/images/login-background.png') }}');
            
        })
    </script>
    <script src="{{ CoreUrl::asset('slide_show/js/index.js') }}"></script>
</body>
</html>