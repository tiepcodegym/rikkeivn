<?php
use Rikkei\News\View\ViewNews;

$auth = Auth::user()->getEmployee();
$email = 'news@rikkeisoft.com';
?>
        @if(!isset($searchParams))
        <section class="home-header-wrapper news-section">
            <div class="home-header-container">
                <div class="home-header-row row">
                    <div class="col-sm-12 col-md-12 home-header-news">
                        <div class="home-header-news-highlight">
                            <div id="rikkeiCarousel" class="carousel slide carousel-fade" data-ride="carousel" data-interval="false">

                                <!-- Indicators -->
                                <ol class="carousel-indicators">
                                    @foreach($postSlide as $key => $post)
                                        <li data-target="#rikkeiCarousel" data-slide-to="{{$key}}" class="{{$key == 0 ? 'active' : ''}}"/>
                                    @endforeach
                                </ol>

                                <!-- Wrapper for slides -->
                                <div class="carousel-inner anim">
                                    <!-- Slide 1 : Active -->
                                    @if(isset($postSlide) && count($postSlide))
                                        @foreach($postSlide as $key => $post)
                                            <div class="item {{ $key == 0 ? 'active' : '' }} content">
                                                <div class="content-overlay"></div>
                                                <img class="content-image"
                                                     src="{{ $post->getDetailImage() }}"
                                                     alt="">
                                                <div class="carousel-caption content-details fadeIn-bottom">
                                                    <h3 class="content-title">
                                                        <a href="{{ URL::route('news::post.view', ['slug' => $post->slug]) }}">
                                                            {{Viewnews::shortDesc($post->title,20) }}
                                                        </a>
                                                    </h3>
                                                    <p class="content-text">
                                                        {{ ViewNews::shortDesc($post->short_desc, 20) }}
                                                    </p>
                                                </div><!-- /.carousel-caption -->
                                            </div><!-- /Slide1 -->
                                        @endforeach
                                    @endif
                                </div><!-- /Wrapper for slides .carousel-inner -->
                                <!-- Controls -->
                                <div class="control-box">
                                    <a class="left carousel-control" href="#rikkeiCarousel" data-slide="prev"><i
                                                class="fa fa-chevron-left"></i></a>
                                    <a class="right carousel-control" href="#rikkeiCarousel" data-slide="next"><i
                                                class="fa fa-chevron-right"></i></a>
                                </div><!-- /.control-box -->
                            </div><!-- /#rikkeiCarousel -->
                        </div>
                    </div>

{{--                    <div class="col-sm-5 col-md-4">--}}
{{--                        <div class="gg-calender-header">--}}
{{--                            <div class="gg-calender-title category-title">--}}
{{--                                <span class="title-content category-title-content">{{trans('news::view.Event')}}</span>--}}
{{--                            </div>--}}
{{--                            <div class="gg-calender-body">--}}
{{--                                <iframe src="https://calendar.google.com/calendar/embed?src=bmV3c0ByaWtrZWlzb2Z0LmNvbQ&amp;src=YWRkcmVzc2Jvb2sjY29udGFjdHNAZ3JvdXAudi5jYWxlbmRhci5nb29nbGUuY29t&amp;src=dmkudmlldG5hbWVzZSNob2xpZGF5QGdyb3VwLnYuY2FsZW5kYXIuZ29vZ2xlLmNvbQ&amp;showTabs=0&bgcolor=%23bc2026&amp;ctz=Asia%2FHo_Chi_Minh&amp;color=%23039BE5&amp;color=%23F09300&amp;color=%23AD1457&amp;color=%23E4C441&amp;color=%23EF6C00&amp;color=%23AD1457&amp;showTitle=0&amp;showTabs=1&amp;showPrint=0&amp;showDate=1&amp;showCalendars=1&amp;showTz=0" style="border-width:0" width="100%" height="100%" frameborder="0" scrolling="no"></iframe>--}}
{{--                                <iframe src="https://calendar.google.com/calendar/embed?src=bmV3c0ByaWtrZWlzb2Z0LmNvbQ&amp;src=YWRkcmVzc2Jvb2sjY29udGFjdHNAZ3JvdXAudi5jYWxlbmRhci5nb29nbGUuY29t&amp;src=dmkuamFwYW5lc2UjaG9saWRheUBncm91cC52LmNhbGVuZGFyLmdvb2dsZS5jb20&amp;src=dmkudmlldG5hbWVzZSNob2xpZGF5QGdyb3VwLnYuY2FsZW5kYXIuZ29vZ2xlLmNvbQ&amp;color=%230B8043&amp;color=%230B8043&amp;showTabs=0&bgcolor=%23bc2026&amp;ctz=Asia%2FHo_Chi_Minh&amp;color=%23039BE5&amp;color=%23F09300&amp;color=%23AD1457&amp;color=%23E4C441&amp;color=%23EF6C00&amp;color=%23AD1457&amp;showTitle=0&amp;showTabs=1&amp;showPrint=0&amp;showDate=1&amp;showCalendars=1&amp;showTz=0" style="border-width:0" width="100%" height="100%" frameborder="0" scrolling="no"></iframe>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
                </div>
            </div>
        </section>
        @endif
