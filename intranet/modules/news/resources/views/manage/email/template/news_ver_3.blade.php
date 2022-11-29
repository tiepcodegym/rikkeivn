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
            style="word-wrap: break-word; width: 100%; color: #000; font-family: arial, sans-serif;">

            <div style="width: 600px; margin: auto; background: #fff;">
                <div style="width: 100%;">
                    <img src="{{ URL::asset('asset_news/images/mail/header_v2.png') }}" alt="" style="width: 100%; object-fit: contain;">
                </div>
                <div style="margin-top: 20px;">
                    <div style="width: 180px; margin: auto;">
                        <table cellpadding="0" cellspacing="0" width="100%" style="text-align: center; border: 3px solid #540101; border-radius: 13px;">
                            <tr>
                                <td width="90" style="background: #9A0000; color: #fff; 
                                    border-radius: 9px 0 0 9px; padding: 12px 0;
                                    font-weight: bold; font-size: 24px;
                                ">TUẦN</td>
                                <td width="80" style="background: #fff; color: #9A0000; 
                                    border-radius: 0 13px 13px 0;
                                    font-weight: bold; font-size: 34px;
                                ">{{ $week }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Sự kiện nổi bật -->
                @if (isset($dataPost['feature']) && count($dataPost['feature']))
                <div style="background: #9A0000;
                    margin-top: 40px;
                    color: #fff;
                    font-size: 24px;
                    font-weight: bold;
                    border-radius: 11px 11px 0 0;
                    text-transform: uppercase;
                    width: 600px;
                    padding: 15px 0;
                    text-align: center;">
                    Sự kiện nổi bật
                </div>
                <table border="0" cellpadding="0" cellspacing="0" width="600" style="margin:auto; background: #D5D6D6; border-radius: 0 0 23px 23px;">
                    <tr style="line-height: 20px">
                        <td>&nbsp;</td>
                    </tr>
                    @foreach ($dataPost['feature'] as $key => $post)
                    <tr>
                        <td>
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="">
                                <tr>
                                    <td width="15">&nbsp;</td>
                                    <td style="text-align: center; width: 570px;">
                                        <a href="{{ $post->getUrlByWeb10years() }}" style="color: #fff; font-size: 16px; font-weight: 700; text-decoration: none;
                                        background: #9A0000; display: block; padding: 15px 20px; border-radius: 16px 16px 0 0;">
                                            {{ $post->title }}
                                        </a>
                                    </td>
                                    <td width="15">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td width="13">&nbsp;</td>
                                    <td>
                                        <div style="height: 280px; max-height: 280px; overflow: hidden; background: #fff;">
                                            <img src="{{ $post->getImage(true) }}" style="width: 100%; max-width: 100%; height: auto;" />
                                        </div>
                                    </td>
                                    <td width="13">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td width="13">&nbsp;</td>
                                    <td style="font-size: 16px; text-align: center; background: #fff">
                                        <div style="margin: 15px 0; line-height: 22px; padding: 0 20px;">
                                            {!! ViewNews::cutTextHtml($post->short_desc, 135, $gt) !!}
                                            @if ($gt)
                                                ...
                                            @endif
                                        </div>
                                    </td>
                                    <td width="13">&nbsp;</td>
                                </tr>
                                <tr style="line-height: 10px;">
                                    <td width="13">&nbsp;</td>
                                    <td style="background: #fff">&nbsp;</td>
                                    <td width="13">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td width="13">&nbsp;</td>
                                    <td style="background: #fff">
                                        <div style="text-align:center">
                                            <a href="{{ $post->getUrlByWeb10years() }}" class="btn-view-more" target="_blank">{{ trans('project::view.View more') }} <img src="{{ URL::asset('asset_news/images/mail/icon_right.png') }}" alt=""></a>
                                        </div>
                                    </td>
                                    <td width="13">&nbsp;</td>
                                </tr>
                                <tr style="line-height: 20px;">
                                    <td width="13">&nbsp;</td>
                                    <td style="background: #fff; border-radius: 0 0 16px 16px;">&nbsp;</td>
                                    <td width="13">&nbsp;</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr style="line-height: 20px">
                        <td>&nbsp;</td>
                    </tr>
                    @endforeach
                </table>
                @endif
                <!-- end Sự kiện nổi bật -->

                <!-- Bản tin trong tuần -->
                @if (isset($dataPost['week']) && count($dataPost['week']))
                <div style="background: #9A0000;
                    margin-top: 40px;
                    color: #fff;
                    font-size: 24px;
                    font-weight: bold;
                    border-radius: 11px 11px 0 0;
                    text-transform: uppercase;
                    width: 600px;
                    padding: 15px 0;
                    text-align: center;">
                    Bản tin trong tuần
                </div>
                <table border="0" cellpadding="0" cellspacing="0" width="600" style="margin:auto; background: #D5D6D6; border-radius: 0 0 23px 23px;">
                    <tr>
                        <td width="15">&nbsp;</td>
                        <td>
                            <table border="0" cellpadding="0" cellspacing="0" style="width: 100%;">
                                <tr style="line-height: 20px;">
                                    <td colspan="2">&nbsp;</td>
                                </tr>
        
                                <?php $i = 0; $total = count($dataPost['week']) ?>
                                @foreach ($dataPost['week'] as $post)
                                    <?php $i++; ?>
                                    @if ($i % 2 == 1)
                                        <tr>
                                    @endif
                                    <td style="width: 50%; {{ ($i % 2 == 0) ? ' padding-left: 7px;' : (($total > 1) ? ' padding-right: 7px;' : '') }}">
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
                                                                    <a href="{{ $post->getUrlByWeb10years() }}" style="{{ $styleBoxTitleA }}">
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
                                                    
                                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="{{ $styleBoxContent  }}">
                                                        <tr style="line-height: 10px">
                                                            <td colspan="3">&nbsp;</td>
                                                        </tr>
                                                        <tr>
                                                            <td width="10">&nbsp;</td>
                                                            <td>
                                                                <div style="height: 130px;max-height: 130px; overflow: hidden; border-radius: 10px;">
                                                                    <img src="{{ $post->getImage(true) }}" style="width: 100%; max-width: 100%; height: auto; border-radius: 10px;" />
                                                                </div>
                                                                <div style="font-size: 16px; margin: 10px 0 20px; height: 160px; max-height: 160px; line-height: 22px; overflow: hidden;">
                                                                    {!! ViewNews::cutTextHtml($post->short_desc, 180, $gt) !!}
                                                                    @if ($gt)
                                                                        ...
                                                                    @endif
                                                                </div>
                                                                <div style="text-align:center">
                                                                    <a href="{{ $post->getUrlByWeb10years() }}" class="btn-view-more" target="_blank">{{ trans('project::view.View more') }} <img src="{{ URL::asset('asset_news/images/mail/icon_right.png') }}" alt=""></a>
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
                                            <tr style="line-height: 20px;">
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
                        <td width="15">&nbsp;</td>
                    </tr>
                    <tr style="line-height: 10px;">
                        <td colspan="2">&nbsp;</td>
                    </tr>
                </table>
                @endif
                <!-- end bản tin trong tuần -->

                <!-- Start tin thêm -->
                @if (isset($dataPost['more']) && count($dataPost['more']))
                <div style="background: #9A0000;
                    margin-top: 40px;
                    color: #fff;
                    font-size: 24px;
                    font-weight: bold;
                    border-radius: 11px 11px 0 0;
                    text-transform: uppercase;
                    width: 600px;
                    padding: 15px 0;
                    text-align: center;">
                    Tin thêm
                </div>
                <table border="0" cellpadding="0" cellspacing="0" width="600" style="margin:auto; background: #D5D6D6;">
                    <tr>
                        <td width="15">&nbsp;</td>
                        <td>
                            <table border="0" cellpadding="0" cellspacing="0" style="width: 100%;">
                                <tr style="line-height: 30px;">
                                    <td colspan="2">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <table border="0" cellpadding="0" cellspacing="0" style="width: 100%;">
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
                                                                <td style="width: 570px;">
                                                                    <div style="height: 40px; overflow: hidden;line-height: 20px;font-size: 15px;">
                                                                        <a href="{{ $post->getUrlByWeb10years() }}" style="{{ $styleBoxTitleA }}">
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
                                                                                    <img src="{{ $post->getImage(true) }}" alt="rikkeisoft" style="width: 100%; max-width: 100%; height: 100%; border-radius: 20px; max-height: 140px; object-fit: cover;">
                                                                                </div>
                                                                            </td>
                                                                            <td width="20">&nbsp;</td>
                                                                            <td width="232">
                                                                                <div style="font-size: 16px; margin-bottom: 10px; max-height: 160px; line-height: 22px; overflow: hidden;">
                                                                                    {!! ViewNews::cutTextHtml($post->short_desc, 135, $gt) !!}
                                                                                    @if ($gt)
                                                                                        ...
                                                                                    @endif
                                                                                </div>
                                                                                <div style="text-align:center">
                                                                                    <a href="{{ $post->getUrlByWeb10years() }}" class="btn-view-more" target="_blank">{{ trans('project::view.View more') }} <img src="{{ URL::asset('asset_news/images/mail/icon_right.png') }}" alt=""></a>
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
                                            <tr style="line-height: 20px">
                                                <td colspan="2">&nbsp;</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td width="15">&nbsp;</td>
                    </tr>
                </table>
                @endif
                <!-- End tin thêm -->

                <!-- Đọc thêm tin khác -->
                <div style="background: #D5D6D6; padding-bottom: 50px; border-radius: 0 0 23px 23px;">
                    <div style="background: #9A0000;
                    width: 570px;
                    margin: auto;
                    border-radius: 50px;
                    text-transform: uppercase;
                    padding: 15px 0;
                    text-align: center;">
                        <a href="{{ config('services.rikkei_10years_news_url') }}" style="text-align: center; font-size: 18px; font-weight: 700; text-transform: uppercase; color: #fff; text-decoration: none;">
                            Đọc thêm các tin khác
                        </a>
                    </div>
                </div>
                <!-- End Đọc thêm tin khác -->
            </div>
        </div>
    </body>
</html>