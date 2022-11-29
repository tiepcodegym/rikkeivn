@extends('layouts.guest_bg')

@section('title')
{{ trans('event::view.Confirmation of attendance', [], '', $languageView) }}
@endsection

@section('header')
    <div class="logo-event">
        <h1 class="logo">
            <img src="{{ URL::asset('common/images/logo_10_en.png') }}" 
                alt="Rikkeisoft Intranet" class="img-responsive" />
        </h1>
    </div>
@endsection

@section('content')
<?php
use Rikkei\Event\Model\EventBirthday;
use Rikkei\Core\Model\CoreConfigData;

$patternsArray = [
    '/\{\{\sreadmoreLink\s\}\}/'
];
$replacesArray = [
    '<a href="#" class="tour-details-link">' . trans('event::view.Details', [], '', $languageView) . '</a>'
];
if ($languageView == 'ja') {
    $buletHead = '■ ';
} else {
    $buletHead = '';
}
?>
<div class="page-main">
    @if ($languageView == 'vi')
        <h1 class="register-title">Thông tin nhận thư mời</h1>
        <div class="register-note-title">
            <strong>Cám ơn {{ $receiveGender }} {{ $customerEvent->name }} đã xác nhận tham gia sự kiện 
                Buổi lễ kỷ niệm 5 năm thành lập Công ty Rikkeisoft.</strong>
            <br/>Buổi lễ sẽ được tổ chức long trọng vào thời gian: <strong>18:30 (18h nhận tiếp đón ở lễ tân) Thứ Tư,  Ngày 05/04/2017</strong>.
            <br/>Địa điểm: <strong>Hội trường Grand Ballroom, khách sạn JW Marriott</strong>
            <br />Địa chỉ： <strong>Số 8, Đỗ Đức Dục, Mễ Trì, Nam Từ Liêm, Hà Nội</strong>
            <br/><br/>
            Xin {{ $receiveGender }} vui lòng điền thông tin nhận thư mời ở bên dưới.
            <br/>Chúng tôi mong chờ sự có mặt của {{ $receiveGender }} trong buổi lễ!
            <br/>Xin chân thành cám ơn {{ $receiveGender }}.
        </div>
    @else
        <h1 class="register-title">ご出席のご確認</h1>
        <div class="register-note-title">
            ありがとうございます。ご出席承りました。
            <br/>以下、記念イベントの概要についてご案内いたします。
            <br/>ご宿泊やオプショナルツアーについてのお申し込みをお願いいたします。
            <br/><br/><span style="color: #bf202f;"><b>１．スケジュール</b></span>
            <br/><b>7月17日（日）</b>
            <br/>午前　ハノイよりハロンへ移動（所要時間2時間30分）
            <br/>午後　記念式典及び祝賀会
            <br/><br/><b>7月18日（月）</b>
            <br/>午前　オプショナルツアーをお楽しみください。
            <br/>午後　ハロンよりハノイへ移動
            <br/>ハノイ到着後解散
            <br/><span style="color: #bf202f;">※開催が近づきましたら、詳細スケジュールをお送りいたします。</span>
        </div>
    @endif

    <div class="register-form">
        <form id="form-register-birth" method="post" action="{{ route('event::brithday.register.post', ['token' => $customerEvent->token]) }}" 
        autocomplete="off"  role="form" class="form-submit-ajax has-valid">
            {!! csrf_field() !!}
            
            <div class="form-group">
                <span style="color: #bf202f;"><b>２．ご出席者のご確認</b></span>
                <br/>★参加者人数（お申込者様含めます）　　
                <input style="display: inline-block; width: 86px;" required type="number" class="form-control" id="number_attacher" value="1" min="1" />&nbsp;名
                <br/>★参加者情報（お申込者様含めます）
            </div>
            <div class="attacher_list">
                <div class="attacher" style="    border: 1px solid #ccc;    padding: 10px;">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>お名前</label>
                            <input type="text" maxlength="100" class="form-control info-name" placeholder="お名前 ..." required name="item[attacher][0][name]" data-no="0"
                                   value="{{ $customerEvent->name }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label>アルファベット</label>
                            <input type="text" maxlength="100" class="form-control info-alphabet" placeholder="アルファベット ..." required name="item[attacher][0][alphabet]"
                                   value="">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>会社名</label>
                            <input type="text" maxlength="100" class="form-control info-company" placeholder="会社名 ..." required name="item[attacher][0][company]"
                                   value="{{ $customerEvent->company }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label>メールアドレス</label>
                            <input type="email" maxlength="100" class="form-control info-email" placeholder="メールアドレス ..." required name="item[attacher][0][email]"
                                   value="{{ $customerEvent->email }}">
                        </div>
                    </div>
                    
                </div>
            </div>
            <br/>
            
            @if ($languageView == 'vi')
                <div class="form-group">
                    <label for="address" class="required">{{ trans('event::view.Address receive from post office', [], '', $languageView) }} <em>*</em></label>
                    <div class="form-input">
                        <input type="text" class="form-control" id="address" name="item[address]" value />
                    </div>
                </div>
            @else
                
                <div class="form-group">
                    <b><span style="color: #bf202f;">３．宿泊及びオプショナルツアーのお申込み</span></b>
                    <br><label for="booking_room" class="required"><span>{{ trans('event::view.Stay in hotel', [], '', $languageView) }}</span></label>
                    <div class="form-input">
                        {{ trans('event::view.time and hotel name', [], '', $languageView) }}
                        <label class="radio-inline"><input type="radio" name="item[booking_room]" value="{{ EventBirthday::BOOKING_RK }}" checked>{{ trans('event::view.Yes', [], '', $languageView) }}</label>
                        <label class="radio-inline"><input type="radio" name="item[booking_room]" value="{{ EventBirthday::BOOKING_SELF }}" >{{ trans('event::view.No', [], '', $languageView) }}</label>
                    </div>
                </div>
                @if ($customerEvent->show_tour == EventBirthday::SHOW_TOUR)
                    <div class="form-group">
                        <p>
                            {!! trans('event::view.tour_join_header_note', [], '', $languageView) !!}
                        </p>
                        <strong class="tour-title-choose" data-value="1"><span style="color: #bf202f;">{{ trans('event::view.tour_join_title_nojoin', [], '', $languageView) }}</span></strong>
                        <strong class="tour-title-choose hidden" data-value="Hanoi">Rikkeisoft 10周年記念ゴルフコンペ</strong>
                        <strong class="tour-title-choose hidden" data-value="Danang">世界遺産ハロン湾クルーズ</strong>
                        <div class="form-input event-tour-wrapper">
                            <div class="event-tour-item active" data-modal="modal-tour-no" data-value="{{ EventBirthday::TOUR_NO }}">
                                <div class="eti-toasts">
                                    <span>{{ trans('event::view.I dont join tour', [], '', $languageView) }}</span>
                                </div>
                                <div class="eti-inner">
                                    <div class="eti-image">
                                        <img src="{{ URL::asset('event/images/12.jpg') }}" />
                                    </div>
                                    <div class="eti-main">
                                        <div class="eti-main-inner">
                                            <div class="eti-main-box">
                                                {!! preg_replace($patternsArray, $replacesArray, CoreConfigData::getValueDb('event.birthday.tour.nojoin.short')) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="event-tour-item" data-modal="modal-tour-hanoi" data-value="{{ EventBirthday::TOUR_GOLF }}">
                                <div class="eti-toasts">
                                    <span>{{ trans('event::view.I choose this tour', [], '', $languageView) }}</span>
                                </div>
                                <div class="eti-inner">
                                    <div class="eti-image">
                                        <img src="{{ URL::asset('event/images/golf.jpg') }}" />
                                    </div>
                                    <div class="eti-main">
                                        <div class="eti-main-inner">
                                            <div class="eti-main-box">
                                                Rikkeisoft 10周年記念ゴルフコンペ
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="event-tour-item last-child" data-modal="modal-tour-danang" data-value="{{ EventBirthday::TOUR_DU_THUYEN }}">
                                <div class="eti-toasts">
                                    <span>{{ trans('event::view.I choose this tour', [], '', $languageView) }}</span>
                                </div>
                                <div class="eti-inner">
                                    <div class="eti-image">
                                        <img src="{{ URL::asset('event/images/heritage-cruise-1.jpg') }}" />
                                    </div>
                                    <div class="eti-main">
                                        <div class="eti-main-inner">
                                            <div class="eti-main-box">
                                                世界遺産ハロン湾クルーズ
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <input type="hidden" name="item[join_tour]" value="{{ EventBirthday::TOUR_NO }}" id="event-tour-item-value" />
                        </div>
                    </div>
                <div class="tour_person_list hidden">
                    <span class="tour-choose"></span>の参加者を選んでください。
                </div>
                @endif
            @endif
            <div class="form-group">
                <label for="note" class="required"><span >{{ trans('event::view.Other requirments', [], '', $languageView) }}</span></label>
                <div class="form-input">
                    <textarea class="form-control" id="note" name="item[note]" rows="3"></textarea>
                </div>
            </div>
            @if ($languageView == 'ja')
            <div class="form-group">
                <strong><span style="color: #bf202f;">４．費用について</span></strong>
                    <br/>
                上記スケジュールのハノイ⇄ハロン湾の移動（往復）、17日（日）の宿泊、18日（月）のオプショナルツアーについては、Rikkeisoftが手配しております。上記以外の移動、手配しているホテル以外の施設等に宿泊される場合、その他自由行動での飲食にかかる費用についてはご負担をお願いいたします。
                <br/><br/>
                <span style="color: #bf202f;"><b>５．ベトナム及び日本の入国規制（水際対策）</b></span>
                <br/>［ベトナム入国について］
                <br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ベトナム入国においては、待機や隔離等はございません。
                <br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;5月15日から出発前72時間以内のPCR検査も必要ございません。
                <br/><br/>［日本入国について］
                <br/>必要なもの
                <ul>
                    <li>出発前72時間以内の陰性証明書（厚生労働省が指定する項目が記載されていること）
                    <br/>&nbsp;&nbsp;&nbsp;（検査の場所や当日のご案内は弊社でご手配いたします）</li>
                    <li>My SOSアプリのインストール
                    <br/>&nbsp;&nbsp;&nbsp;（入国前16時間以内にアプリより所定資料を申告しておくと入国がスムーズです。）</li>
                </ul>
                
                <div class="map-wrapper" style="height: 400px; width: 100%;">
                    <div id="rk-map" style="height: 100%; width: 100%;"></div>
                </div>
            </div>
            @else
                <p>■ Liên hệ</p>
                <p>Nguyễn Quang Kỷ</p>
                <p>Email: kynq@rikkeisoft.com</p>
                <p>Điện thoại: (+84) 985-932-246 </p>
            @endif
            <div class="form-submit">
                <button type="button" id="btn-register-submit-confirm" class="btn-register-submit">{{ trans('event::view.Submit', [], '', $languageView) }} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
            </div>
            <div class="modal fade modal-warning" id="modal-confirm" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header" style="border-bottom: none;">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span></button>
                            <h4>この内容で登録してもよろしいでしょうか。</h4>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn-register-submit">{{ trans('event::view.Submit', [], '', $languageView) }} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal" style="color: #fff;
    background-color: #6c757d;font-size: 14px;padding: 6px 25px; border-radius: 0; margin-left: 10px;
    margin-top: -3px;
    border: none;">キャンセル</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@if ($languageView == 'ja')
<div class="modal fade modal-success" id="modal-tour-no" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4>{{ trans('event::view.tour_join_title_nojoin', [], '', $languageView) }}</h4>
            </div>
            <div class="modal-body">
                {!! CoreConfigData::getValueDb('event.birthday.tour.nojoin') !!}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-close" data-dismiss="modal">{{ trans('event::view.Close', [], '', $languageView) }}</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade modal-success" id="modal-tour-hanoi" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4>{{ trans('event::view.tour_join_title_hanoi', [], '', $languageView) }}</h4>
            </div>
            <div class="modal-body">
                {!! CoreConfigData::getValueDb('event.birthday.tour.hanoi') !!}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-close" data-dismiss="modal">{{ trans('event::view.Close', [], '', $languageView) }}</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade modal-success" id="modal-tour-danang" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4>{{ trans('event::view.tour_join_title_danang', [], '', $languageView) }}</h4>
            </div>
            <div class="modal-body">
                {!! CoreConfigData::getValueDb('event.birthday.tour.danang') !!}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-close" data-dismiss="modal">{{ trans('event::view.Close', [], '', $languageView) }}</button>
            </div>
        </div>
    </div>
</div>

<div class="attacher_genarate hidden">
    <div class="attacher " style=" border: 1px solid #ccc; margin-top: 15px;   padding: 10px;">
        <div class="row">
            <div class="form-group col-md-6">
                <label>お名前</label>
                <input type="text" maxlength="100" class="form-control info-name" required placeholder="お名前 ..." ">
            </div>
            <div class="form-group col-md-6">
                <label>アルファベット</label>
                <input type="text" maxlength="100" class="form-control info-alphabet" required placeholder="アルファベット ...">
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-6">
                <label>会社名</label>
                <input type="text" maxlength="100" class="form-control info-company" required placeholder="会社名 ..." >
            </div>
            <div class="form-group col-md-6">
                <label>メールアドレス</label>
                <input type="email" maxlength="100" class="form-control info-email" required placeholder="メールアドレス ..." >
            </div>
        </div>
    </div>
</div>

<div class="tour_person hidden">
    <div class="form-group person">
        <div class="checkbox">
            <label>
                <input type="checkbox">
                <span class="tour_person_name"></span>
            </label>
        </div>
    </div>
</div>

@endif
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="http://maps.google.com/maps/api/js?key=AIzaSyAY93vqKZUpL2yqWsx6zSikaInxWCySzn0"></script>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $('#modal-warning-notification .modal-title').text('通知');
        $('#modal-warning-notification .btn-close').text('閉じる');
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
            messages: messages
        });
        $('.event-tour-item a.tour-details-link').click(function(event) {
            event.preventDefault();
            var modal = $(this).closest('.event-tour-item').data('modal');
            if (modal && $('#' + modal).length) {
                $('#' + modal).modal('show');
            }
        });
        $('.event-tour-item.active .eti-toasts').stop().fadeIn(500);
        $('.event-tour-item').click(function(event) {
            if ($(event.toElement).hasClass('tour-details-link')) {
                return true;
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
                    __this.find('.eti-toasts').stop().fadeOut(5000);
                }, 2000);*/
                // Nếu có chọn tour
                if (value != 1) {
                    $('.tour_person_list').removeClass('hidden');
                    if (value == 'golf') {
                        $('.tour-choose').text('Rikkeisoft 10周年記念ゴルフコンペ');
                    }
                    if (value == 'du_thuyen') {
                        $('.tour-choose').text('世界遺産ハロン湾クルーズ');
                    }
                    // số lượng form
                    var number_attachers = $('.attacher_list .attacher').length;
                    // số lượng nhập ô checkbox
                    var input_attachers = $('.tour_person_list .person').length;

                    if (input_attachers > number_attachers) {
                        var thua = input_attachers - number_attachers;
                        for (var i = 0; i < thua; i++) {
                            $('.tour_person_list').children().last().remove();
                        }
                    } else if (number_attachers > input_attachers) {
                        var thieu =  number_attachers - input_attachers;
                        for (var i = 0; i < thieu; i++) {
                            $('.tour_person_list').append($('.tour_person').html());

                        }
                    }
                    $('.tour_person_list .person').each(function(i, item) {
                        $(item).find('.tour_person_name')
                                .text($('.attacher_list').find('.info-name[data-no='+i+']').val()+'様').attr('data-no', i);
                                
                        $(item).find('input')
                                .attr('name', 'item[attacher]['+i+'][tour]').attr('data-no', i);
                    });
                } else {
                    $('.tour_person_list').addClass('hidden');
                }
            }
        });
        $(window).load(function() {
            if (!$('#rk-map').length) {
                return true;
            }
            // Multiple Markers
            var markers = [
                ['FLC Grand Hotel Halong', 20.955142551691495, 107.11196794373213],
                ['Rikkeisoft 10周年記念ゴルフコンペ', 20.955072451358824, 107.11304078122859],
//                ['クラウンプラザ', 21.027188,105.7658434],
//                ['マイ・ウェイホテル', 21.0317197,105.7813431],
//                ['サクラホテル', 21.0328113,105.7896735],
            ];
            var latlng = new google.maps.LatLng(20.955142551691495, 107.11196794373213),
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
            var map = new google.maps.Map(document.getElementById(idDom), myOptions),
                bounds = new google.maps.LatLngBounds();
            for( i = 0; i < markers.length; i++ ) {
                var position = new google.maps.LatLng(markers[i][1], markers[i][2]);
                bounds.extend(position);
                marker = new google.maps.Marker({
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
        
        // show form attacher
        $(document).on('keyup', '#number_attacher', function() {
            addRemoveAttacherForm();
        })
        $(document).on('change', '#number_attacher', function() {
            addRemoveAttacherForm();
        })
        
        $(document).on('change', '.info-name', function() {
            var no = $(this).data('no');
            $('.tour_person_list').find('.tour_person_name[data-no='+no+']').text($(this).val()+'様');
        })
        
        function addRemoveAttacherForm() {
            // số lượng form
            var number_attachers = $('.attacher_list .attacher').length;
            // số lượng nhập ở ô input
            var input_attachers = $('#number_attacher').val();
            var value = $('#event-tour-item-value').val();
            if (number_attachers > input_attachers) {
                var thua = number_attachers - input_attachers;
                for (var i = 0; i < thua; i++) {
                    $('.attacher_list').children().last().remove();
                    $('.tour_person_list').children().last().remove();
                }
            } else if (number_attachers < input_attachers) {
                var thieu =  input_attachers - number_attachers;
                for (var i = 0; i < thieu; i++) {
                    $('.attacher_list').append($('.attacher_genarate').html());
                    if (value != 1) {
                        $('.tour_person_list').append($('.tour_person').html());
                        
                    }
                }
                $('.attacher_list .attacher').each(function(i, item) {
                    $(item).find('.info-name').attr('name', 'item[attacher]['+i+'][name]').attr('data-no', i);
                    $(item).find('.info-alphabet').attr('name', 'item[attacher]['+i+'][alphabet]');
                    $(item).find('.info-company').attr('name', 'item[attacher]['+i+'][company]');
                    $(item).find('.info-email').attr('name', 'item[attacher]['+i+'][email]');
                });
                $('.tour_person_list .person').each(function(i, item) {
                    if (value != 1) {
                        $(item).find('input').attr('name', 'item[attacher]['+i+'][tour]').attr('data-no', i);
                        $(item).find('.tour_person_name').text($('.attacher_list').find('.info-name[data-no='+i+']').val() + '様').attr('data-no', i);
                    }
                    
                });
            } else {
                return false;
            }
        }
        
        $('#btn-register-submit-confirm').click(function() {
            $('#modal-confirm').modal('show');
        });
    });
</script>
@endsection
