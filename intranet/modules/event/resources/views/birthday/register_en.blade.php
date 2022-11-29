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
    <h1 class="register-title">Confirmation of attendance</h1>
    <div class="register-note-title">
        We will inform you about the detailed schedule of the commemorative event as below. <br>
        Please apply for accommodation and optional tours if needed.

        <br/><br/><span style="color: #bf202f;"><b>１．Schedule</b></span>
        <br/><b>July 17th（Sun）</b>
        <br/>Morning:　Moving from Hanoi to Halong in the morning (expected time: 2 hours 30 minutes)
        <br/>Afternoon:　The Commemoration Ceremony and Celebration Party
        <br/><br/><b>July 18（Mon）</b>
        <br/>Morning:　Optional Tours (Golf Competition or Cruise Tour)
        <br/>Afternoon:　Moving back to Hanoi
        <br/><span style="color: #bf202f;">※We will send you a more detailed schedule as soon as possible.</span>
    </div>

    <div class="register-form">
        <form id="form-register-birth" method="post" action="{{ route('event::brithday.register.post', ['token' => $customerEvent->token]) }}" 
        autocomplete="off"  role="form" class="form-submit-ajax has-valid">
            {!! csrf_field() !!}
            
            <div class="form-group">
                <span style="color: #bf202f;"><b>２．Confirmation of attendees</b></span>
                <br/>Number of participants (including applicants)
                <input style="display: inline-block; width: 86px;" required type="number" class="form-control" id="number_attacher" value="1" min="1" />
                <br/>★Participant information (including applicants)
            </div>
            <div class="attacher_list">
                <div class="attacher" style="    border: 1px solid #ccc;    padding: 10px;">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Name</label>
                            <input type="text" maxlength="100" class="form-control info-name" placeholder="Name ..." required name="item[attacher][0][name]" data-no="0"
                                   value="{{ $customerEvent->name }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Alphabet</label>
                            <input type="text" maxlength="100" class="form-control info-alphabet" placeholder="Alphabet ..." required name="item[attacher][0][alphabet]"
                                   value="">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Company name</label>
                            <input type="text" maxlength="100" class="form-control info-company" placeholder="Company name ..." required name="item[attacher][0][company]"
                                   value="{{ $customerEvent->company }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Email </label>
                            <input type="email" maxlength="100" class="form-control info-email" placeholder="Email  ..." required name="item[attacher][0][email]"
                                   value="{{ $customerEvent->email }}">
                        </div>
                    </div>
                    
                </div>
            </div>
            <br/>
            
            <div class="form-group">
                <b><span style="color: #bf202f;">３．Apply for accommodation and optional tours</span></b>
                <br><label for="booking_room" class="required"><span>★ Accommodation application</span></label>
                <div class="form-input">
                    17th (Sun) Accommodation (FLC Grand Hotel Halong)
                    <label class="radio-inline"><input type="radio" name="item[booking_room]" value="{{ EventBirthday::BOOKING_RK }}" checked>Yes</label>
                    <label class="radio-inline"><input type="radio" name="item[booking_room]" value="{{ EventBirthday::BOOKING_SELF }}" >No</label>
                </div>
            </div>
            @if ($customerEvent->show_tour == EventBirthday::SHOW_TOUR)
                <div class="form-group">
                    <p>★ Apply for an optional tour</p>
                    <strong class="tour-title-choose" data-value="1"><span style="color: #bf202f;">Not Attending</span></strong>
                    <strong class="tour-title-choose hidden" data-value="Hanoi">Rikkeisoft 10th Anniversary Golf Competition</strong>
                    <strong class="tour-title-choose hidden" data-value="Danang">World Heritage Halong Bay Cruise</strong>
                    <div class="form-input event-tour-wrapper">
                        <div class="event-tour-item active" data-modal="modal-tour-no" data-value="{{ EventBirthday::TOUR_NO }}">
                            <div class="eti-toasts">
                                <span>Not Attending</span>
                            </div>
                            <div class="eti-inner">
                                <div class="eti-image">
                                    <img src="{{ URL::asset('event/images/12.jpg') }}" />
                                </div>
                                <div class="eti-main">
                                    <div class="eti-main-inner">
                                        <div class="eti-main-box">
                                            Not Attending
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="event-tour-item" data-modal="modal-tour-hanoi" data-value="{{ EventBirthday::TOUR_GOLF }}">
                            <div class="eti-toasts">
                                <span>I choose this tour</span>
                            </div>
                            <div class="eti-inner">
                                <div class="eti-image">
                                    <img src="{{ URL::asset('event/images/golf.jpg') }}" />
                                </div>
                                <div class="eti-main">
                                    <div class="eti-main-inner">
                                        <div class="eti-main-box">
                                            Rikkeisoft 10th Anniversary Golf Competition
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="event-tour-item last-child" data-modal="modal-tour-danang" data-value="{{ EventBirthday::TOUR_DU_THUYEN }}">
                            <div class="eti-toasts">
                                <span>I choose this tour</span>
                            </div>
                            <div class="eti-inner">
                                <div class="eti-image">
                                    <img src="{{ URL::asset('event/images/heritage-cruise-1.jpg') }}" />
                                </div>
                                <div class="eti-main">
                                    <div class="eti-main-inner">
                                        <div class="eti-main-box">
                                            World Heritage Halong Bay Cruise
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
                    <span class="tour-choose"></span>. Please select a participant.
                </div>
            @endif
            <div class="form-group">
                <label for="note" class="required"><span >If you have any questions or other requests, please write them down.</span></label>
                <div class="form-input">
                    <textarea class="form-control" id="note" name="item[note]" rows="3"></textarea>
                </div>
            </div>

            <div class="form-group">
                <strong><span style="color: #bf202f;">４．About the costs</span></strong>
                <br/> Rikkeisoft will bear all the costs for the above schedule including moving expenses by buses from Hanoi to Halong Bay (round trip), accommodation on 17th (Sun), and the optional tour on 18th (Mon).
                <br/> Please note that if you decide to move or stay at a hotel other than our arranging ones, you will have to pay the costs on your own.
                <div class="map-wrapper" style="height: 400px; width: 100%;">
                    <div id="rk-map" style="height: 100%; width: 100%;"></div>
                </div>
            </div>

            <div class="form-group">
                <strong><span style="color: #bf202f;">5．Immigration restrictions for Vietnam (Border measures)</span></strong>
                <br> [When entering Vietnam]
                <div style="margin-left: 15px;">There is no quarantine needed when entering Vietnam.</div>
                <div style="margin-left: 15px;">PCR testing within 72 hours before departure is no longer required from May 15th.</div>
            </div>

            <div class="form-submit">
                <button type="button" id="btn-register-submit-confirm" class="btn-register-submit">Register <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
            </div>
            <div class="modal fade modal-warning" id="modal-confirm" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header" style="border-bottom: none;">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span></button>
                            <h4>Would you like to register these details?</h4>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn-register-submit">Register<i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal" style="color: #fff;
                                background-color: #6c757d;font-size: 14px;padding: 6px 25px; border-radius: 0; margin-left: 10px;
                                margin-top: -3px;
                                border: none;">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

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
                <button type="button" class="btn btn-outline btn-close" data-dismiss="modal">Close</button>
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
                <button type="button" class="btn btn-outline btn-close" data-dismiss="modal">Close</button>
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
                <button type="button" class="btn btn-outline btn-close" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="attacher_genarate hidden">
    <div class="attacher " style=" border: 1px solid #ccc; margin-top: 15px; padding: 10px;">
        <div class="row">
            <div class="form-group col-md-6">
                <label>Name</label>
                <input type="text" maxlength="100" class="form-control info-name" required placeholder="Name ...">
            </div>
            <div class="form-group col-md-6">
                <label>Alphabet</label>
                <input type="text" maxlength="100" class="form-control info-alphabet" required placeholder="Alphabet ...">
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-6">
                <label>Company name</label>
                <input type="text" maxlength="100" class="form-control info-company" required placeholder="Company name ..." >
            </div>
            <div class="form-group col-md-6">
                <label>Email</label>
                <input type="email" maxlength="100" class="form-control info-email" required placeholder="Email ..." >
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

@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="http://maps.google.com/maps/api/js?key=AIzaSyAY93vqKZUpL2yqWsx6zSikaInxWCySzn0"></script>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $('#modal-warning-notification .modal-title').text('Notification');
        $('#modal-warning-notification .btn-close').text('Close');
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
                        $('.tour-choose').text('Rikkeisoft 10th Anniversary Golf Competition');
                    }
                    if (value == 'du_thuyen') {
                        $('.tour-choose').text('World Heritage Halong Bay Cruis');
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
                                .text($('.attacher_list').find('.info-name[data-no='+i+']').val()).attr('data-no', i);
                                
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
                ['Rikkeisoft 10th Anniversary Golf Competition', 20.955072451358824, 107.11304078122859],
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
            $('.tour_person_list').find('.tour_person_name[data-no='+no+']').text($(this).val());
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
                        $(item).find('.tour_person_name').text($('.attacher_list').find('.info-name[data-no='+i+']').val()).attr('data-no', i);
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
