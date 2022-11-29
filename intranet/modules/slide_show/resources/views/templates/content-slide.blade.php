<?php
use Rikkei\SlideShow\Model\Slide;
use Rikkei\SlideShow\Model\VideoDefault;
use Rikkei\SlideShow\View\ImageHelper;
use Rikkei\SlideShow\View\View;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\SlideShow\Model\SlideQuotation;
use Rikkei\SlideShow\Model\SlideBirthday;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Core\View\CoreUrl;

$imageHelper = new ImageHelper();
$sizeImageShow = CoreConfigData::getSizeImageShow();
$sizeResizeWidth = CoreConfigData::get('slide_show.size_image_show.width');
$sizeResizeHeight = CoreConfigData::get('slide_show.size_image_show.height');

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
<div class="content-slide" data-is-show-countdown="{{$isShowCountdow}}">
    @if($slide)
        @if($slide->option == Slide::OPTION_NOMAL)
            @if($slide->type == Slide::TYPE_IMAGE)
                <div class="loader jumbotron">
                    <div class="container-fluid">
                        <section class="content">
                            <div class="login-wrapper">
                                <h1 class="login-title">
                                    <img src="{{ URL::asset('common/images/logo_login.png') }}" />
                                </h1><!-- /.login-logo -->
                                <div class="login-action">
                                    <div class="col-sm-6 col-sm-offset-3">
                                        <i class="fa fa-spinner fa-2x fa-spin" aria-hidden="true"></i>
                                    </div>
                                </div>
                            </div><!-- /.login-wrapper -->
                        </section>
                        <!-- /.content -->
                    </div>
                </div>
                <div id="wowslider-container1" data-id="{{$slide->id}}" class="swiper-container">
                    <div class="swiper-wrapper">
                        @foreach($fileOfSlide as $key => $file)
                        <?php
                            $url = $imageHelper->setImage($urlImage. $file->file_name)
                                    ->resizeWatermark($sizeResizeWidth, $sizeResizeHeight);
                        ?>
                            <div class="swiper-slide" style="background-image:url({{ $url }});">
                                @if ($file->description)
                                    <div class="text slide-desc" data-swiper-parallax="-500" data-swiper-parallax-duration="1000">
                                        <p class="sd-inner" @if($slide->font_size) style="font-size:{{$slide->font_size}}px" @endif>{{ $file->description }}</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
                <script>var effectSwiper = '{{ $slide->effect }}';</script>
            @else
                <div class="video">
                    @if($fileOfSlide)
                    <iframe width="100%" height="100%" src="{{View::urlVideoYoutube($fileOfSlide->file_name)}}" frameborder="0" class="youtube-video"  webkitallowfullscreen mozallowfullscreen allowfullscreen style="min-height: 500px"></iframe>
                    @else
                    <?php
                        // $rand_keys = array_rand($videoDefault);
                        // $videoDefault = $videoDefault[$rand_keys];
                        if ($videoDefault) {
                            $urlDefault = View::urlVideoYoutube($videoDefault->file_name);
                        } else {
                            $urlDefault = View::urlVideoYoutube('e6MNgrHT6Q0');
                        }
                    ?>
                    <iframe width="100%" height="100%" src="{{$urlDefault}}" frameborder="0" class="youtube-video"  webkitallowfullscreen mozallowfullscreen allowfullscreen style="min-height: 500px"></iframe>
                    @endif
                </div>    
            @endif
        @elseif($slide->option == Slide::OPTION_WELCOME)
            @include('slide_show::layout_preview.welcome_header')
                @if($slide->language == Slide::LANG_JAPAN)
                <h1 class="text-uppercase text-center" style="margin-top: 0px; font-size: 80px">
                <span class="font-myriad-pro-light" style="font-weight: 700">{{trans('slide_show::message.Rikkeisoft')}}</span>
                <span class="font-myriad-pro-light">{{trans('slide_show::message.Welcome(JP)')}}</span>
                </h1>
                @else
                <h1 class="text-uppercase text-center font-new-cicle-fina" style="font-size: 80px">{{trans('slide_show::message.Welcome to rikkeisoft!(Eng)')}}</h1>
                @endif
                <?php
                    $arrayCompany = explode(",",$slide->title);
                    $arrayCustomer = explode(",",$slide->name_customer);
                ?>
                @foreach($arrayCompany as $company)
                <h1 class="text-uppercase text-center font-myria-pro-regular" style="font-size: 70px">{{$company}}</h1>
                @endforeach
                @foreach($arrayCustomer as $customer)
                <h2 class="text-uppercase text-center font-myriad-pro-light-italic" style="font-size: 50px">{{$customer}} @if($slide->language == Slide::LANG_JAPAN) 様 @endif</h2>
                @endforeach
                @if($fileOfSlide)
                @foreach ($fileOfSlide->chunk(3) as $images)
                    <div class="row logo-list">
                        @foreach ($images as $image)
                            <?php
                                $url = $imageHelper->setImage($urlImage. $image->file_name)
                                    ->resizeWatermark(200, 120);
                            ?>
                            <div class="logo-item"><img src="{{$url}}"/></div>
                        @endforeach
                    </div>
                @endforeach
                @endif
            @include('slide_show::layout_preview.welcome_footer')           
        @elseif($slide->option == Slide::OPTION_QUOTATIONS)
            @include('slide_show::layout_preview.welcome_header')
                <div class="slide-preview-quotations">
                    <?php
                        $slideQuotations = SlideQuotation::getSlideQuotation($slide);
                    ?>
                    @if (count($slideQuotations))
                        <div id="wowslider-container1" data-id="{{$slide->id}}" class="swiper-container"
                             data-slide-autoHeight="true">
                            <div class="swiper-wrapper">
                                @foreach ($slideQuotations as $slideQuotationsItem)
                                    @if (!trim($slideQuotationsItem->content))
                                        <?php continue; ?>
                                    @endif
                                    <div class="swiper-slide">
                                        <div class="spq-item">
                                            <div class="spqi-content">
                                                <p class="spqic-inner">{!! CoreView::nl2br($slideQuotationsItem->content) !!}</p>
                                            </div>
                                            <?php
                                            if (!$slideQuotationsItem->author || !trim($slideQuotationsItem->author)) {
                                                $author = '-Khuyết danh-';
                                            } else {
                                                $author = '-'.trim($slideQuotationsItem->author).'-';
                                            }
                                            ?>
                                            <div class="spqi-author">
                                                <p class="spqia-inner">{{ $author }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @include('slide_show::layout_preview.welcome_footer')
        @elseif($slide->option == Slide::OPTION_BIRTHDAY)
            @if(isset($fakeData) && $fakeData)
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
            @else 
                <?php
                    $slideBirthday = SlideBirthday::getSlideBirthday($slide);
                ?>
                <div class="container-fluid">
                    <div class="row">
                        <div class="swiper-container col-sm-12 col-md-12 col-lg-12">
                            <div class="swiper-wrapper">
                                @foreach ($slideBirthday as $birthdayItem)
                                <div class="swiper-slide display-initial">
                                    <div class="slide-bg-image">
                                        <img class="slide-avatar-image" src="{{ $birthdayItem->avatar }}">
                                        <div class="slide-bday-text">
                                            {!! $birthdayItem->content !!}
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif
    @else
    <?php
        if ($videoDefault) {
            $urlDefault = View::urlVideoYoutube($videoDefault->file_name);
        } else {
            $urlDefault = View::urlVideoYoutube('e6MNgrHT6Q0');
        }
        // $rand_keys = array_rand($videoDefault);
        // $videoDefault = $videoDefault[$rand_keys];
    ?>
    <div class="video">
        <iframe width="100%" height="100%" src="{{$urlDefault}}" frameborder="0" class="youtube-video"  webkitallowfullscreen mozallowfullscreen allowfullscreen style="min-height: 500px"></iframe>
    </div>
    @endif
</div>
<?php
    if (!isset($secondToBirthday)) {
        $secondToBirthday = 0;
    }
?>
