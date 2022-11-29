@extends('layouts.guest_bg')

@section('title')
{{ trans('event::view.Confirmation of attendance', [], '', $languageView) }}
@endsection

@section('header')
<!--<style>
    .logo-event {
        height: 113px;
        background: url('https://rikkei.vn/common/images/email/event-banner-header.png')no-repeat ;
    }
</style>
<div class="logo-event">
    
</div>-->
@endsection

@section('content')
<?php

use Rikkei\Event\Model\EventBirthday;
use Rikkei\Core\Model\CoreConfigData;

$patternsArray = [
    '/\{\{\sreadmoreLink\s\}\}/'
];

$buletHead = '■ ';
?>
<style>
    @media only screen and (max-width: 600px)  {
        .tc-title
        {
            font-size: 28px !important;
        }
        .bt-right
        {
            float: left !important;
            text-align: left !important;
        }
        .br-text
        {
            display: block;
        }
        .tc-content
        {
            font-size: 13px !important;
        }
        .tc-page
        {
            padding-top: 0px !important;
        }
    }

</style>
<div class="page-main">
    <h1 class="">
        <img src="https://rikkei.vn/common/images/email/event-banner-header.png" 
             alt="Rikkeisoft Intranet" class="img-responsive" />
    </h1>

    <h1 class="register-title">ご出席のご確認</h1>
    <div class="register-note-title">
        RIKKEISOFTお客様感謝会にご出席、誠にありがとうございます。
        <br/>{{ $buletHead }}RIKKEISOFTお客様感謝会
        <br/>日時：2019年8月30日（金）19:00～21:00
        <br/>※18:30～受付開始、19:00開会
        <br/>場所：ホテル雅叙園東京
        <p>会場：舞扇</p>
        <p>住所：〒153-0064 東京都目黒区下目黒1-8-1</p>
        <p>&nbsp;</p>
        <p>以下の項目をご記入ください。</p>
    </div>
    <div class="register-form">
        <form id="form-register-birth" method="post" action="{{ route('event::eventday.register.post', ['token' => $customerEvent->token]) }}" 
              autocomplete="off"  role="form" class="form-submit-ajax has-valid">
            {!! csrf_field() !!}
            <div class="form-group">
                {{ trans('event::view.Full name', [], '', $languageView) }}: {{ $customerEvent->name }}{{ ($languageView == 'ja') ? ' 様' : '' }}
            </div>
            <div class="form-group">
                <div class="form-input">
                    <textarea class="form-control" id="attacher" name="item[attacher]" rows="2" placeholder="{{ trans('event::view.Attacher', [], '', $languageView) }}"></textarea>
                </div>
            </div>
            <div class="form-group">
                <div class="form-input">
                    <textarea class="form-control" id="note" name="item[note]" rows="3" placeholder="{{ trans('event::view.Other requirments', [], '', $languageView) }}"></textarea>
                </div>
            </div>
            <div class="form-group">
                <p>■ お問合せ先</p>
                <p>小野寺智佳（Onodera Chika）</p>
                <p>Email: onodera@rikkeisoft.com</p>
                <p>携帯: (+81) 3-6435-0754 </p>
            </div>

            <div class="form-submit">
                <button type="submit" class="btn-register-submit">{{ trans('event::view.Submit', [], '', $languageView) }} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="http://maps.google.com/maps/api/js?key=AIzaSyAY93vqKZUpL2yqWsx6zSikaInxWCySzn0"></script>
<script type="text/javascript">
jQuery(document).ready(function ($) {
var rules = {
'item[company]': {
required: true
        },
        'item[address]': {
        required: true
        },
        'item[phone]': {
        required: true
        },
        'item[email_register]': {
        required: true,
                email: true
        }
};
var messages = {
'item[company]': {
required: '{{ trans('event::view.This field is required', [], '', $languageView) }}'
        },
        'item[address]': {
        required: '{{ trans('event::view.This field is required', [], '', $languageView) }}'
        },
        'item[phone]': {
        required: '{{ trans('event::view.This field is required', [], '', $languageView) }}'
        },
        'item[email_register]': {
        required: '{{ trans('event::view.This field is required', [], '', $languageView) }}',
                email: '{{ trans('event::view.Please enter a valid email address', [], '', $languageView) }}'
        }
};
$('#form-register-birth').validate({
rules: rules,
        messages: messa        ges
        });
$('.event-tour-item a.tour-details-link').click(function(event) {
event.preventDefault();
var modal = $(this).closest('.event-tour-item').data('modal');
if (modal && $('#' + modal).length) {
$('#' + mo    da    l).m    odal('show');
}
});
$('.event-tour-item.active .e        ti-toasts').stop().fadeIn(500);
$('.event-tour-item').click(function(event) {
if ($(event.toElement).hasClass('    to    ur    -d    etails-link')) {
retu        rn true;
}
event.stopPropagation();
var __this = $(this),
        value = __this.data('value');
if (value) {
$('.tour-title-choose').addClass('hidden');
$('.tour-title-choose[data-value="' + value + '"]').removeClass('hidden');
$('.event-tour-item').removeClass('active');
$('.event-tour-item .eti-toasts').stop().fadeOut(500);
__this.addClass('active');
__this.find('.eti-toasts').stop().fadeIn(500);
$('input[name="item[join_tour]"]').val(value);
/*setTimeout(function() {
 __this.fin    d(    '.    et    i-toasts').stop().fadeOut(5000);
 }, 2000);*/
}
});
$(win    do      w).load(function() {
if (!$('#rk-map').length) {
return true;
}
// Multiple Markers
var markers = [
        ['マリオットホテル', 21.0071559, 105.7826313],
        ['グランドプラザホテル', 21.0072094, 105.7962175],
        ['クラウンプラザ', 21.027188, 105.7658434],
        ['マイ・ウェイホテル', 21.0317197, 105.7813431],
        ['サクラホテル', 21.0328113, 105.7896735],
        ];
var latlng = new google.maps.LatLng(21.016427, 105.779418),
        idDom = 'rk-map',
        myOptions = {
        zoom: 13,
//                    center: latlng,
                mapTypeControlOptions: {
                mapTypeIds: ['noText', google.maps.MapTypeId.ROADMAP]
                },
                scrollwheel: true
                //disableDefaultUI: true
        };
var map = new google.maps.Map(document.getE        lementById(idDom), myOptions),
        bounds = new google.maps.LatLngBounds();
for (i = 0; i < markers.length; i++) {
var position = new google.maps.LatLng(markers[i][1], markers[i][2]);
bounds.extend(position);
marker = new google.maps.Mark        er({
position: position,
        map: map,
        title: markers[i][0]
        });
map.fitBounds(bounds);
}
var boundsListener = google.maps.event.addListener((map), 'bounds_changed', function(event) {
this.setZoom(14);
google.maps.event.removeListener(boundsListener);
});
});
});
</script>
@endsection
