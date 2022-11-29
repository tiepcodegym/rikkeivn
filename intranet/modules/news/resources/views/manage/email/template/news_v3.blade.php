<?php
use Rikkei\News\View\ViewNews;
use Rikkei\Core\View\View as CoreView;
$gt = true;
?><!DOCTYPE html>
<html dir="ltr">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    </head>
    <body>
        <?php
        $styleMainTitle = 'background: #ff5252; color: #fff; font-size: 36px; '
            . 'display: inline-block; font-weight: 300;';
        
        $stylePaddingTrMainTitle = 'line-height: 9px';
        $stylePaddingTrBoxTitle = 'line-height: 15px';
        $stylePaddingTrBoxTitle12 = 'line-height: 12px';
        $styleBoxContent = 'background: #F2F2F2; border-bottom-left-radius: 16px; '
            . 'border-bottom-right-radius: 16px;';
        $styleBoxContentPadding = 'line-height: 13px';
        $styleBoxMargin = 'line-height: 35px';
        $styleImage = 'width: 100%; max-width: 100%; height: auto; border-radius: 10px;';
        
        $styleLinkMore = '-webkit-appearance: button; -moz-appearance: button; appearance: button; background-color: #337ab7; color: #fff; border-color: #2e6da4; display: inline-block; text-decoration: none; padding-top: 6px; padding-right: 12px; padding-bottom: 6px; padding-left: 12px;';
        
        $styleMainTitleV2 = 'background: #9A0000; color: #fff; font-size: 18px; '
            . 'display: inline-block; font-weight: bold; border-radius: 8px; text-transform: uppercase;';
        $styleCard = 'background: #fff; border-radius: 34px; padding: 30px 12px;';
        $styleBoxTitle = 'background: #9A0000; color: #fff; font-size: 17px; '
            . 'text-transform: uppercase; border-top-right-radius: 16px; '
            . 'border-top-left-radius: 16px;';
        $styleBoxTitleA = 'color: #fff; text-decoration: none; font-weight: bold;  font-size: 16px; text-transform: uppercase;';
        $styleShortDesc = 'font-size: 16px; margin: 5px 0 20px; max-height: 160px; line-height: 22px; overflow: hidden;';
        ?>
        <style>
            .main-title{
                padding: 9px 30px; background: #ff5252; color: #fff; font-size: 36px; display: inline-block; margin-bottom: 15px; font-weight: 300;
            }
            .box-title{
                background: #0097a7; color: #fff; font-size: 17px; text-transform: uppercase; padding: 15px;
                border-top-right-radius: 10px; border-top-left-radius: 10px;
            }
            .box-title a {
                color: #fff;
                text-decoration: none;
            }
            .box-content{
                
            }
            .more-posts{
                margin: 5px 0;
            }
            .more-posts li{
                margin-bottom: 3px;
            }
            .more-posts li a{
                display: inline-block; color: #231f20; text-decoration: none;
            }
            img {
                max-width: 100%;
            }

            .btn-view-more{
                background: linear-gradient(180deg, #990000 14.51%, #5B0000 124.3%, rgba(170, 0, 0, 0) 124.31%);
                color: #fff;
                border-radius: 8px;
                display: inline-block;
                text-decoration: none;
                padding: 8px 15px;
                font-size: 12px;
                font-weight: 700;
                text-transform: uppercase;
                
            }
        </style>
        <div id="wrapper" dir="ltr" leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0"
            {{-- style="word-wrap: break-word; width: 100%; color: #000; font-family: arial, sans-serif; background: url({{ URL::asset('asset_news/images/mail/parttern.png') }}) repeat;"> --}}
            style="word-wrap: break-word; width: 100%; color: #000; font-family: arial, sans-serif; 
            background: url({{ URL::asset('asset_news/images/mail/bg_1.png') }}), url({{ URL::asset('asset_news/images/mail/bg_2.png') }}), url({{ URL::asset('asset_news/images/mail/bg_1.png') }});
            background-repeat: no-repeat;
            background-position: 100% 0, 0 50%, 75% 100%;
            background-size: 37%;
            ">

            <div style="width: 600px; margin: auto; background: #E0E0E0;">
                <table border="0" cellpadding="0" cellspacing="0" width="600px" height="176" 
                    style="background: url({{ URL::asset('asset_news/images/mail/calendar.png') }}), url({{ URL::asset('asset_news/images/mail/header_v2.png') }});
                    background-repeat: no-repeat;
                    background-position: 93% 50%, 100% 0;
                    background-size: 92px, cover;
                ">
                    <tr>
                        <td id="header_wrapper" width="700" style="width: 700px">&nbsp;</td>
                        <td width="80">
                            <div style="text-align: center; padding: 45px 5px 5px;">
                                <span style="color: #2C2C2C; font-size: 48px; font-weight: bold; line-height: 27px;">{{ '12' }}</span>
                            </div>
                        </td>
                        <td width="65" style="width: 65px">&nbsp;</td>
                    </tr>
                </table>
            </div>

            <div style="width: 600px; margin: auto; background: #E0E0E0; padding-top: 35px;">
                <!-- Sự kiện nổi bật -->
                @if (isset($dataPost['feature']) && count($dataPost['feature']))
                <table border="0" cellpadding="0" cellspacing="0" width="550" style="margin:auto; background: #fff; border-radius: 34px;">
                    <tr>
                        <td>
                            <table border="0" cellpadding="0" cellspacing="0" style="width: 100%;">
                                <tr style="line-height: 30px;">
                                    <td colspan="2">&nbsp;</td>
                                </tr>
                                <tr style="text-align: center">
                                    <td colspan="2">
                                        <table border="0" cellpadding="0" cellspacing="0" style="{{ $styleMainTitleV2 }}">
                                            <tr height="9" style="{{ $stylePaddingTrMainTitle }}">
                                                <td colspan="3">&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <td width="30">&nbsp;</td>
                                                <td>
                                                    <span>
                                                        Sự kiện nổi bật
                                                    </span>
                                                </td>
                                                <td width="30">&nbsp;</td>
                                            </tr>
                                            <tr height="9" style="{{ $stylePaddingTrMainTitle }}">
                                                <td colspan="3">&nbsp;</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                {{-- <tr style="line-height: 30px;">
                                    <td colspan="2">&nbsp;</td>
                                </tr> --}}
        
                                @foreach ($dataPost['feature'] as $key => $post)
                                <tr style="line-height: 20px">
                                    <td colspan="2">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="">
                                            <tr>
                                                <td width="15">&nbsp;</td>
                                                <td style="text-align: center;">
                                                    <a href="{{ $post->getUrl() }}" style="color: #000; font-size: 16px; font-weight: 700; text-decoration: none;">
                                                        {{ $post->title }}
                                                    </a>
                                                </td>
                                                <td width="15">&nbsp;</td>
                                            </tr>
                                            <tr style="{{ $stylePaddingTrBoxTitle }}">
                                                <td colspan="3">&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <td width="13">&nbsp;</td>
                                                <td>
                                                    <div style="height: 280px; max-height: 280px; overflow: hidden;">
                                                        <img src="{{ $post->getImage(true) }}" style="width: 100%; height: 100%; object-fit: cover;" />
                                                    </div>
                                                </td>
                                                <td width="13">&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <td width="13">&nbsp;</td>
                                                <td style="font-size: 16px; text-align: center;">
                                                    <div style="margin: 15px 0; line-height: 22px;">
                                                        {!! ViewNews::cutTextHtml($post->short_desc, 135, $gt) !!}
                                                        @if ($gt)
                                                            ...
                                                        @endif
                                                    </div>
                                                </td>
                                                <td width="13">&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <td colspan="3">
                                                    <div style="text-align:center">
                                                        <a href="{{ $post->getUrl() }}" class="btn-view-more" target="_blank">{{ trans('project::view.View more') }} <img src="{{ URL::asset('asset_news/images/mail/icon_right.png') }}" alt=""></a>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr style="line-height: 20px">
                                    <td colspan="2">
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="">
                                            <tr>
                                                <td width="40">&nbsp;</td>
                                                <td style="{{ $key+1 < count($dataPost['feature']) ? 'border-bottom: 0.5px solid #C4C4C4;' : '' }}"></td>
                                                <td width="40">&nbsp;</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>
                </table>
                @endif
                <!-- end Sự kiện nổi bật -->

                <!-- Bản tin trong tuần -->
                @if (isset($dataPost['week']) && count($dataPost['week']))
                <table border="0" cellpadding="0" cellspacing="0" width="550" style="margin:auto; background: #fff; border-radius: 34px; margin-top: 40px;">
                    <tr>
                        <td width="25">&nbsp;</td>
                        <td>
                            <table border="0" cellpadding="0" cellspacing="0" style="width: 100%;">
                                <tr style="line-height: 30px;">
                                    <td colspan="2">&nbsp;</td>
                                </tr>
                                <tr style="text-align: center">
                                    <td colspan="2">
                                        <table border="0" cellpadding="0" cellspacing="0" style="{{ $styleMainTitleV2 }}">
                                            <tr height="9" style="{{ $stylePaddingTrMainTitle }}">
                                                <td colspan="3">&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <td width="30">&nbsp;</td>
                                                <td>
                                                    <span>
                                                        Bản tin trong tuần
                                                    </span>
                                                </td>
                                                <td width="30">&nbsp;</td>
                                            </tr>
                                            <tr height="9" style="{{ $stylePaddingTrMainTitle }}">
                                                <td colspan="3">&nbsp;</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr style="line-height: 30px;">
                                    <td colspan="2">&nbsp;</td>
                                </tr>
        
                                <?php $i = 0; ?>
                                @foreach ($dataPost['week'] as $post)
                                    <?php $i++; ?>
                                    @if ($i % 2 == 1)
                                        <tr>
                                    @endif
                                    <td style="width: 50%; {{ ($i % 2 == 0) ? ' padding-left: 10px;' : (count($dataPost['week']) > 1) ? ' padding-right: 10px;' : '' }}">
                                        <table border="0" cellpadding="0" cellspacing="0" style="border-radius: 10px; width: 100%;">
                                            <tr>
                                                <td>
                                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="{{ $styleBoxTitle  }}">
                                                        <tr style="{{ $stylePaddingTrBoxTitle12 }}">
                                                            <td colspan="3">&nbsp;</td>
                                                        </tr>
                                                        <tr>
                                                            <td width="10">&nbsp;</td>
                                                            <td>
                                                                <div style="height: 40px; overflow: hidden; line-height: 20px; font-size: 15px;">
                                                                    <a href="{{ $post->getUrl() }}" style="{{ $styleBoxTitleA }}">
                                                                        {{ CoreView::cutWordLimitStr($post->title, 50) }}
                                                                    </a>
                                                                </div>
                                                            </td>
                                                            <td width="10">&nbsp;</td>
                                                        </tr>
                                                        <tr style="{{ $stylePaddingTrBoxTitle12 }}">
                                                            <td colspan="3">&nbsp;</td>
                                                        </tr>
                                                    </table>
                                                    
                                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="{{ $styleBoxContent  }} border: 0.5px solid #6B6B6B;">
                                                        <tr style="line-height: 10px">
                                                            <td colspan="3">&nbsp;</td>
                                                        </tr>
                                                        <tr>
                                                            <td width="10">&nbsp;</td>
                                                            <td>
                                                                <div style="height: 120px;max-height: 120px; overflow: hidden;">
                                                                    <img src="{{ $post->getImage(true) }}" style="width: 100%; max-width: 100%; height: 100%; border-radius: 10px; object-fit: cover;" />
                                                                </div>
                                                                <div style="font-size: 16px; margin: 10px 0 20px; height: 160px; max-height: 160px; line-height: 22px; overflow: hidden;">
                                                                    {!! ViewNews::cutTextHtml($post->short_desc, 180, $gt) !!}
                                                                    @if ($gt)
                                                                        ...
                                                                    @endif
                                                                </div>
                                                                <div style="text-align:center">
                                                                    <a href="{{ $post->getUrl() }}" class="btn-view-more" target="_blank">{{ trans('project::view.View more') }} <img src="{{ URL::asset('asset_news/images/mail/icon_right.png') }}" alt=""></a>
                                                                </div>
                                                            </td>
                                                            <td width="10">&nbsp;</td>
                                                        </tr>
                                                        <tr style="line-height: 30px">
                                                            <td colspan="3">&nbsp;</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr style="line-height: 22px;">
                                                <td colspan="2">&nbsp;</td>
                                            </tr>
                                        </table>
                                    </td>
                                    @if ($i % 2 == 0)
                                    </tr>
                                    @endif
                                @endforeach
                                @if ($i % 2 == 1)
                                    <tr>
                                @endif
                            </table>
                        </td>
                        <td width="25">&nbsp;</td>
                    </tr>
                    <tr style="line-height: 30px;">
                        <td colspan="2">&nbsp;</td>
                    </tr>
                </table>
                @endif
                <!-- end bản tin trong tuần -->

                <!-- Start tin thêm -->
                @if (isset($dataPost['more']) && count($dataPost['more']))
                <table border="0" cellpadding="0" cellspacing="0" width="550" style="margin:auto; background: #fff; border-radius: 34px; margin-top: 40px;">
                    <tr>
                        <td width="25">&nbsp;</td>
                        <td>
                            <table border="0" cellpadding="0" cellspacing="0" style="width: 100%;">
                                <tr style="line-height: 30px;">
                                    <td colspan="2">&nbsp;</td>
                                </tr>
                                <tr style="text-align: center">
                                    <td colspan="2">
                                        <table border="0" cellpadding="0" cellspacing="0" style="{{ $styleMainTitleV2 }}">
                                            <tr height="9" style="{{ $stylePaddingTrMainTitle }}">
                                                <td colspan="3">&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <td width="30">&nbsp;</td>
                                                <td>
                                                    <span>
                                                        Tin thêm
                                                    </span>
                                                </td>
                                                <td width="30">&nbsp;</td>
                                            </tr>
                                            <tr height="9" style="{{ $stylePaddingTrMainTitle }}">
                                                <td colspan="3">&nbsp;</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr style="line-height: 30px;">
                                    <td colspan="2">&nbsp;</td>
                                </tr>
        
                                <tr>
                                    <td colspan="2">
                                        <table border="0" cellpadding="0" cellspacing="0" style="">
                                            @foreach ($dataPost['more'] as $post)
                                                <tr>
                                                    <td>
                                                        <table border="0" cellpadding="0" cellspacing="0" width="100%"
                                                            style="{{ $styleBoxTitle  }}">
                                                            <tr style="{{ $stylePaddingTrBoxTitle12 }}">
                                                                <td colspan="3">&nbsp;</td>
                                                            </tr>
                                                            <tr>
                                                                <td width="10">&nbsp;</td>
                                                                <td>
                                                                    <div style="height: 40px; overflow: hidden;line-height: 20px;font-size: 15px;">
                                                                        <a href="{{ $post->getUrl() }}" style="{{ $styleBoxTitleA }}">
                                                                            {{ $post->title }}
                                                                        </a>
                                                                    </div>
                                                                </td>
                                                                <td width="10">&nbsp;</td>
                                                            </tr>
                                                            <tr style="{{ $stylePaddingTrBoxTitle12 }}">
                                                                <td colspan="3">&nbsp;</td>
                                                            </tr>
                                                        </table>
                                                        
                                                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="{{ $styleBoxContent  }} border: 0.5px solid #6B6B6B;">
                                                            <tr style="{{ $styleBoxContentPadding }}">
                                                                <td colspan="3">&nbsp;</td>
                                                            </tr>
                                                            <tr>
                                                                <td width="13">&nbsp;</td>
                                                                <td>
                                                                    <table border="0" cellpadding="0" cellspacing="0" class="box-content" width="100%">
                                                                        <tr>
                                                                            <td width="220">
                                                                                <div style="overflow: hidden;">
                                                                                    <img src="{{ $post->getImage(true) }}" alt="rikkeisoft" style="width: 100%; max-width: 100%; height: 100%; border-radius: 20px;">
                                                                                </div>
                                                                            </td>
                                                                            <td width="20">&nbsp;</td>
                                                                            <td width="232">
                                                                                <div style="font-size: 16px; margin-bottom: 20px; max-height: 160px; line-height: 22px; overflow: hidden;">
                                                                                    {!! ViewNews::cutTextHtml($post->short_desc, 142, $gt) !!}
                                                                                    @if ($gt)
                                                                                        ...
                                                                                    @endif
                                                                                </div>
                                                                                <div style="text-align:center">
                                                                                    <a href="{{ $post->getUrl() }}" class="btn-view-more" target="_blank">{{ trans('project::view.View more') }} <img src="{{ URL::asset('asset_news/images/mail/icon_right.png') }}" alt=""></a>
                                                                                </div>
                                                                            </td>
                                                                        <tr>
                                                                    </table>
                                                                </td>
                                                                <td width="13">&nbsp;</td>
                                                            </tr>
                                                            <tr style="line-height: 20px">
                                                                <td colspan="3">&nbsp;</td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                                <tr style="line-height: 20px">
                                                    <td colspan="2">&nbsp;</td>
                                                </tr>
                                            @endforeach
                                            <tr style="line-height: 40px">
                                                <td colspan="2">&nbsp;</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td width="25">&nbsp;</td>
                    </tr>
                </table>
                @endif
                <!-- End tin thêm -->

                <!-- Đọc thêm tin khác -->
                <table border="0" cellpadding="0" cellspacing="0" width="550" style="margin:auto;">
                    <tr style="line-height: 30px">
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="background: #9A0000; border-radius: 50px;">
                            <table border="0" cellpadding="0" cellspacing="0" style="width: 550px; text-align: center;">
                                <tr style="line-height: 10px;">
                                    <td colspan="3">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td>
                                        <a href="{{ URL::to('/') }}" style="text-align: center; font-size: 18px; font-weight: 700; text-transform: uppercase; color: #fff; text-decoration: none;">
                                            Đọc thêm các tin khác
                                        </a>
                                    </td>
                                </tr>
                                <tr style="line-height: 10px;">
                                    <td colspan="3">&nbsp;</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr style="line-height: 50px">
                        <td>&nbsp;</td>
                    </tr>
                </table>
                
            </div>
        </div>
    </body>
</html>