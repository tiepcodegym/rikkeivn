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
        $styleBoxTitle = 'background: #0097a7; color: #fff; font-size: 17px; '
            . 'text-transform: uppercase; border-top-right-radius: 10px; '
            . 'border-top-left-radius: 10px;';
        $styleBoxTitleA = 'color: #fff; text-decoration: none;';
        $stylePaddingTrMainTitle = 'line-height: 9px';
        $stylePaddingTrBoxTitle = 'line-height: 15px';
        $stylePaddingTrBoxTitle12 = 'line-height: 12px';
        $styleBoxContent = 'background: #b2ebf2; border-bottom-left-radius: 10px; '
            . 'border-bottom-right-radius: 10px;';
        $styleBoxContentPadding = 'line-height: 13px';
        $styleBoxMargin = 'line-height: 35px';
        $styleImage = 'width: 100%; max-width: 100%; height: auto; border-radius: 10px;';
        $styleShortDesc = 'font-size: 14px; margin: 5px 0 8px; max-height: 160px; line-height: 22px;'
            . 'overflow: hidden;';
        $styleLinkMore = '-webkit-appearance: button; -moz-appearance: button; appearance: button; background-color: #337ab7; color: #fff; border-color: #2e6da4; display: inline-block; text-decoration: none; padding-top: 6px; padding-right: 12px; padding-bottom: 6px; padding-left: 12px;';
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
        </style>
        <div id="wrapper" dir="ltr" leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0"
            style="word-wrap: break-word; width: 100%; color: #000; font-family: arial, sans-serif; background: url({{ URL::asset('asset_news/images/mail/parttern.png') }}) repeat;">

            <div style="width: 600px; margin: auto">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" height="160" style="background: url({{ URL::asset('asset_news/images/mail/header.png') }}) no-repeat; margin-bottom: 45px;">
                    <tr>
                        <td id="header_wrapper" width="70" style="width: 70px">&nbsp;</td>
                        <td width="205" style="width: 205px">
                            <div style="text-align: center;">
                                <img src="{{ URL::asset('asset_news/images/mail/btnb.png') }}" />
                            </div>
                        </td>
                        <td width="190" style="width: 190px">&nbsp;</td>
                        <td width="70" style="width: 70px">
                            <div style="text-align: center; background: #fff; padding: 10px 5px 5px;">
                                <span style="font-size: 18px; color: #49b2aa; display: inline-block; 
                                      border-bottom: 1px solid #49b2aa; margin-bottom: 10px;">{{ trans('project::view.Week') }}</span>
                                <span style="font-size: 32px; color: #49b2aa; line-height: 27px;">{{ $week }}</span>
                            </div>
                        </td>
                        <td width="65" style="width: 65px">&nbsp;</td>
                    </tr>
                </table>

                <table border="0" cellpadding="0" cellspacing="0" width="550" style="margin: auto;">
                    <!-- feature -->
                    @if (isset($dataPost['feature']) && count($dataPost['feature']))
                        <tr style="text-align: center">
                            <td colspan="2">
                                <table border="0" cellpadding="0" cellspacing="0"
                                    style="{{ $styleMainTitle }}">
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
                        <tr style="{{ $stylePaddingTrBoxTitle }}">
                            <td colspan="2">&nbsp;</td>
                        </tr>
                        @foreach ($dataPost['feature'] as $post)
                            <tr>
                                <td colspan="2">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%"
                                        style="{{ $styleBoxTitle  }}">
                                        <tr style="{{ $stylePaddingTrBoxTitle }}">
                                            <td colspan="3">&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td width="15">&nbsp;</td>
                                            <td>
                                                <a href="{{ $post->getUrl() }}" style="{{ $styleBoxTitleA }}">
                                                    {{ $post->title }}
                                                </a>
                                            </td>
                                            <td width="15">&nbsp;</td>
                                        </tr>
                                        <tr style="{{ $stylePaddingTrBoxTitle }}">
                                            <td colspan="3">&nbsp;</td>
                                        </tr>
                                    </table>
                                    
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%"
                                        style="{{ $styleBoxContent  }}">
                                        <tr style="{{ $styleBoxContentPadding }}">
                                            <td colspan="3">&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td width="13">&nbsp;</td>
                                            <td>
                                                <div style="max-height: 250px; overflow: hidden;">
                                                    <img src="{{ $post->getImage(true) }}" style="{{ $styleImage }}" />
                                                </div>
                                                <div style="{{ $styleShortDesc }}">
                                                    {!! ViewNews::cutTextHtml($post->short_desc, 200, $gt) !!}
                                                    @if ($gt)
                                                        ...
                                                    @endif
                                                </div>
                                               <div style="text-align: center">
                                                    <a href="{{ $post->getUrl() }}" target="_blank" style="{{$styleLinkMore}}">{{ trans('project::view.View more') }}</a>
                                                </div>
                                            </td>
                                            <td width="13">&nbsp;</td>
                                        </tr>
                                        <tr style="{{ $styleBoxContentPadding }}">
                                            <td colspan="3">&nbsp;</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr style="{{ $styleBoxMargin }}">
                                <td colspan="2">&nbsp;</td>
                            </tr>
                        @endforeach
                    @endif
                    <!-- end feature -->
                    
                    @if (isset($dataPost['week']) && count($dataPost['week']))
                        <tr style="text-align: center;">
                            <td colspan="2">
                                <table border="0" cellpadding="0" cellspacing="0"
                                    style="{{ $styleMainTitle }}">
                                    <tr style="{{ $stylePaddingTrBoxTitle12 }}">
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
                                    <tr style="{{ $stylePaddingTrBoxTitle12 }}">
                                        <td colspan="3">&nbsp;</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr style="{{ $stylePaddingTrBoxTitle }}">
                            <td colspan="2">&nbsp;</td>
                        </tr>
                        <?php $i = 0; ?>
                        @foreach ($dataPost['week'] as $post)
                            <?php $i++; ?>
                            @if ($i % 2 == 1)
                                <tr>
                            @endif
                            <td>
                                <table border="0" cellpadding="0" cellspacing="0" style="border-radius: 10px; width: 260px;{{ ($i % 2 == 0) ? ' float: right;' : '' }}">
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
                                            
                                            <table border="0" cellpadding="0" cellspacing="0" width="100%"
                                                style="{{ $styleBoxContent  }}">
                                                <tr style="{{ $styleBoxContentPadding }}">
                                                    <td colspan="3">&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td width="13">&nbsp;</td>
                                                    <td>
                                                        <div style="height: 150px;max-height: 150px; overflow: hidden;">
                                                            <img src="{{ $post->getImage(true) }}" style="{{ $styleImage }}" />
                                                        </div>
                                                        <div style="{{ $styleShortDesc }} height: 160px;">
                                                            {!! ViewNews::cutTextHtml($post->short_desc, 200, $gt) !!}
                                                            @if ($gt)
                                                                ...
                                                            @endif
                                                        </div>
                                                        <div style="text-align: center">
                                                            <a href="{{ $post->getUrl() }}" target="_blank" style="{{$styleLinkMore}}">{{ trans('project::view.View more') }}</a>
                                                        </div>
                                                    </td>
                                                    <td width="13">&nbsp;</td>
                                                </tr>
                                                <tr style="{{ $styleBoxContentPadding }}">
                                                    <td colspan="3">&nbsp;</td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr style="{{ $styleBoxMargin }}">
                                        <td colspan="2">&nbsp;</td>
                                    </tr>
                                </table>
                            </td>
                            @if ($i % 2 == 0)
                                <tr>
                            @endif
                        @endforeach
                        @if ($i % 2 == 1)
                            <tr>
                        @endif
                    @endif
                    
                    @if (isset($dataPost['more']) && count($dataPost['more']))
                        <tr style="text-align: center;">
                            <td colspan="2">
                                <table border="0" cellpadding="0" cellspacing="0"
                                    style="{{ $styleMainTitle }}">
                                    <tr style="{{ $stylePaddingTrMainTitle }}">
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
                                    <tr style="{{ $stylePaddingTrMainTitle }}">
                                        <td colspan="3">&nbsp;</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr style="{{ $stylePaddingTrBoxTitle }}">
                            <td colspan="2">&nbsp;</td>
                        </tr>
                        @foreach ($dataPost['more'] as $post)
                            <tr>
                                <td colspan="2" style="border-radius: 10px;">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%"
                                        style="{{ $styleBoxTitle  }}">
                                        <tr style="{{ $stylePaddingTrBoxTitle }}">
                                            <td colspan="3">&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td width="15">&nbsp;</td>
                                            <td>
                                                <a href="{{ $post->getUrl() }}" style="{{ $styleBoxTitleA }}">
                                                    {{ $post->title }}
                                                </a>
                                            </td>
                                            <td width="15">&nbsp;</td>
                                        </tr>
                                        <tr style="{{ $stylePaddingTrBoxTitle }}">
                                            <td colspan="3">&nbsp;</td>
                                        </tr>
                                    </table>
                                    
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%"
                                        style="{{ $styleBoxContent  }}">
                                        <tr style="{{ $styleBoxContentPadding }}">
                                            <td colspan="3">&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td width="13">&nbsp;</td>
                                            <td>
                                                <table border="0" cellpadding="0" cellspacing="0" class="box-content" width="100%">
                                                    <tr>
                                                        <td width="240">
                                                            <div style="max-height: 160px; overflow: hidden;">
                                                                <img src="{{ $post->getImage(true) }}" alt="rikkeisoft" style="{{ $styleImage }}">
                                                            </div>
                                                        </td>
                                                        <td width="35">&nbsp;</td>
                                                        <td>
                                                            <div style="{{ $styleShortDesc }}">
                                                                {!! ViewNews::cutTextHtml($post->short_desc, 200, $gt) !!}
                                                                @if ($gt)
                                                                    ...
                                                                @endif
                                                            </div>
                                                            <div style="text-align: center">
                                                                <a href="{{ $post->getUrl() }}" target="_blank" style="{{$styleLinkMore}}">{{ trans('project::view.View more') }}</a>
                                                            </div>
                                                        </td>
                                                    <tr>
                                                </table>
                                            </td>
                                            <td width="13">&nbsp;</td>
                                        </tr>
                                        <tr style="{{ $styleBoxContentPadding }}">
                                            <td colspan="3">&nbsp;</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr style="{{ $styleBoxMargin }}">
                                <td colspan="2">&nbsp;</td>
                            </tr>
                        @endforeach
                    @endif
                    <tr>
                        <td colspan="2" height="5"></td>
                    </tr>
                    
                    <tr style="text-align: center;">
                        <td colspan="2">
                            <table border="0" cellpadding="0" cellspacing="0"
                                style="{{ $styleMainTitle }} background: #0071bc;">
                                <tr style="{{ $stylePaddingTrMainTitle }}">
                                    <td colspan="3">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td width="30">&nbsp;</td>
                                    <td>
                                        <a href="{{ URL::to('/') }}" style="{{ $styleBoxTitleA }}">
                                            Đọc thêm các tin khác
                                        </a>
                                    </td>
                                    <td width="30">&nbsp;</td>
                                </tr>
                                <tr style="{{ $stylePaddingTrMainTitle }}">
                                    <td colspan="3">&nbsp;</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <?php /*
                    <tr style="{{ $stylePaddingTrBoxTitle }}">
                            <td colspan="2">&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="{{ $styleBoxContent }} border-radius: 10px;">
                                <div>
                                    <ul class="more-posts">
                                        @foreach ($dataPost['another'] as $post)
                                            <li>
                                                <a href="{{ $post->getUrl() }}">{{ $post->title }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <tr style="line-height: 30px">
                            <td>&nbsp;</td>
                        </tr>
                    */ ?>
                </table>
            </div>
        </div>
    </body>
</html>