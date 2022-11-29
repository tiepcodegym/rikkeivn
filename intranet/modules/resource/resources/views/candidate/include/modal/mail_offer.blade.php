<?php
use Rikkei\Resource\Model\Candidate;
use Rikkei\Resource\View\getOptions;
use Rikkei\Resource\View\View;
use Rikkei\Resource\Model\CandidateMail;
use Rikkei\Team\Model\Team;
use Rikkei\Core\View\View as CoreView;

$lastSendOffer = CandidateMail::getLastSend($candidate->email, [Candidate::MAIL_OFFER_HH3, Candidate::MAIL_OFFER_HH4, Candidate::MAIL_OFFER_DN]);

$aryMailOffers = [
    Candidate::MAIL_OFFER_HH3 => [
        'label' => trans('resource::view.Offer HH3'),
    ],
    Candidate::MAIL_OFFER_HH4 => [
        'label' => trans('resource::view.Offer HH4'),
    ],
    Candidate::MAIL_OFFER_HANDICO => [
        'label' => trans('resource::view.Offer Handico'),
    ],
    Candidate::MAIL_OFFER_DN => [
        'label' => trans('resource::view.Offer Đà Nẵng'),
    ],
    Candidate::MAIL_OFFER_HCM => [
        'label' => trans('resource::view.Offer HCM'),
    ],
    Candidate::MAIL_OFFER_JP => [
        'label' => trans('resource::view.Offer Japan'),
    ],
];
$teamId = Team::getTeamById($candidate->team_id);
$mailOfferActive = $isDn ? Candidate::MAIL_OFFER_DN : Candidate::MAIL_OFFER_HH3;
?>
<div class="modal fade" id="modal-offer_content" tabindex="-1" role="dialog"  data-keyboard="false" >
    <div class="modal-dialog" style="width: 800px">
        <div class="modal-content"  >
            <div class="modal-body bg-wrapper">
                <h4 class="modal-title">{{ trans('resource::view.Mail content') }}</h4>
                <section class="box box-info" data-has="1">
                    <div class="box-body">
                        @foreach ($aryMailOffers as $idOffer => $offer)
                        <label class="form-label">
                            <input type="radio" name="mail_offer" value="{{ $idOffer }}" data-target="offer_content_{{ $idOffer }}" {{ $mailOfferActive == $idOffer ? 'checked' : '' }} />
                            <span>{{ $offer['label'] }}</span>
                        </label>
                        @endforeach

                         @if ($lastSendOffer)
                         <div><span class="error font-size-12px"><em>*</em> {{ trans('resource::view.Last send: :date', [ 'date' =>  View::getDate($lastSendOffer->updated_at, 'Y-m-d H:i')]) }}</span></div>
                        @endif

                        @foreach ($aryMailOffers as $idOffer => $offer)
                        <div class="container-offer container-offer_content_{{ $idOffer }} {{ $mailOfferActive == $idOffer ? '' : 'hidden' }}">
                            <textarea id="offer_content_{{ $idOffer }}">

                            </textarea>
                        </div>
                        @endforeach
                    </div>
                    <input type="hidden" id="candidate_email" value="{{$candidate->email}}" />
                    <input type="hidden" id="candidate_id" value="{{$candidate->id}}" />
                    <input type="hidden" id="candidate_fullname" value="{{$candidate->fullname}}" />
                </section>
                <label class="filename">
                    <img src="{{ asset('common/images/attachment_mail.png') }}" />
                    <a href="#" data-toggle="modal" onclick='viewFile({{ Candidate::TYPE_ATTACH }}, "https://docs.google.com/gview?url={{ $pathFolderAttach . '/' .  Candidate::FILE_NAME_TUTORIAL}}&embedded=true");'>{{ Candidate::FILE_NAME_TUTORIAL }}</a>
                </label>
                <label class="filename invite-letter-label">

                </label>
            </div>
            <div class="row" style="margin: 10px 5px;padding-bottom: 12px;">
                <div class="col-md-6 hint-note">
                    <p>Tên: &#123;&#123; Name &#125;&#125;</p>
                    <p>Số điện thoại: &#123;&#123; Phone &#125;&#125;</p>
                </div>
                <div class="col-md-6 hint-note">
                    <p>Skype: &#123;&#123; Skype &#125;&#125;</p>
                    <p>Email: &#123;&#123; Email &#125;&#125;</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary pull-left" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-info btn-show-pdf" onclick="showPdf({{Candidate::MAIL_OFFER_HH3}});">
                    <span>
                        {{ Lang::get('resource::view.View invite letter') }}
                        <i class="fa fa-spin fa-refresh hidden"></i>
                    </span>
                </button>

                <button type="button" class="btn btn-primary btn-send-mail-offer hidden" onclick="sendMail(this, {{Candidate::MAIL_OFFER_HH3}}, 'mail_offer');">
                    <span>
                        {{ Lang::get('resource::view.Send') }}
                        <i class="fa fa-spin fa-refresh hidden"></i>
                    </span>
                </button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>

<script>
    var aryMailOffers = JSON.parse('{!! json_encode(array_keys($aryMailOffers)) !!}');
</script>

<?php
$signatureHtml = isset($recruiter) ? view('resource::candidate.include.modal.signature', ['recruiter' => $recruiter])->render() : view('resource::candidate.include.modal.signature')->render();
?>

<div class="mail-offer_content_{{ Candidate::MAIL_OFFER_HH3 }} hidden">
    <p>{{trans('resource::view.Dear: :name', [ 'name' => $candidate->fullname ])}}</p>
    <p>{{trans('resource::view.Mail offer notification', [ 'date' => date('m/Y') ])}}</p>
    <p>{{trans('resource::view.- Position', ['position' => getOptions::getInstance()->getRole($candidate->position_apply)])}}</p>
    <p>{{trans('resource::view.- Team', ['team' => $candidate->team_id ? ($teamId ? $teamId->name : '') : ''])}}</p>
    <p>{{trans('resource::view.- Start working date', ['date' => View::getDate($candidate->start_working_date, 'd/m/Y')])}}</p>
    <p>{{trans('resource::view.Working place')}}</p>
    <p>{!! trans('resource::view.Mail offer, information in file') !!}</p>
    @if($recruiter)
    <p>{!! trans('resource::view.Mail offer, lien he :m: :name – :email – :skype – :phone',['m' => $gender, 'name'=>$recruiter->name, 'email'=>$recruiter->email, 'skype'=> $recruiter->skype, 'phone'=>$recruiter->mobile_phone]) !!}</p>
    @endif
    <p>{{trans('resource::view.Confirm mail offer notice')}}</p>
    <p>{{trans('resource::view.Welcome :name to Rikkeisoft')}}</p>
    <p>{{trans('resource::view.Thanks & best regards')}}</p>
    {!! $signatureHtml !!}
</div>
<div class="mail-offer_content_{{ Candidate::MAIL_OFFER_HANDICO }} hidden">
    <p>{{trans('resource::view.Dear: :name', [ 'name' => $candidate->fullname ])}}</p>
    <p>{{trans('resource::view.Mail offer notification', [ 'date' => date('m/Y') ])}}</p>
    <p>{{trans('resource::view.- Position', ['position' => getOptions::getInstance()->getRole($candidate->position_apply)])}}</p>
    <p>{{trans('resource::view.- Team', ['team' => $candidate->team_id ? ($teamId ? $teamId->name : '') : ''])}}</p>
    <p>{{trans('resource::view.- Start working date', ['date' => View::getDate($candidate->start_working_date, 'd/m/Y')])}}</p>
    <p>{{trans('resource::view.Working place Handico')}}</p>
    <p>{!! trans('resource::view.Mail offer, information in file') !!}</p>
    @if($recruiter)
    <p>{!! trans('resource::view.Mail offer, lien he :m: :name – :email – :skype – :phone',['m' => $gender, 'name'=>$recruiter->name, 'email'=>$recruiter->email, 'skype'=> $recruiter->skype, 'phone'=>$recruiter->mobile_phone]) !!}</p>
    @endif
    <p>{{trans('resource::view.Confirm mail offer notice')}}</p>
    <p>{{trans('resource::view.Welcome :name to Rikkeisoft')}}</p>
    <p>{{trans('resource::view.Thanks & best regards')}}</p>
    {!! $signatureHtml !!}
</div>
<div class="mail-offer_content_{{ Candidate::MAIL_OFFER_HH4 }} hidden">
    <p>{{trans('resource::view.Dear: :name', [ 'name' => $candidate->fullname ])}}</p>
    <p>{{trans('resource::view.Mail offer notification', [ 'date' => date('m/Y') ])}}</p>
    <p>{{trans('resource::view.- Position', ['position' => getOptions::getInstance()->getRole($candidate->position_apply)])}}</p>
    <p>{{trans('resource::view.- Team', ['team' => $candidate->team_id ? ($teamId ? $teamId->name : '') : ''])}}</p>
    <p>{{trans('resource::view.- Start working date', ['date' => View::getDate($candidate->start_working_date, 'd/m/Y')])}}</p>
    <p>{{trans('resource::view.Working place HH4')}} (Bản đồ: <a target="_blank" href="https://goo.gl/maps/Z4T8VRxPhjn">https://goo.gl/maps/Z4T8VRxPhjn</a>).</p>
    <p>{!! trans('resource::view.Mail offer, information in file') !!}</p>
    @if($recruiter)
    <p>{!! trans('resource::view.Mail offer, lien he :m: :name – :email – :skype – :phone',['m' => $gender, 'name'=>$recruiter->name, 'email'=>$recruiter->email, 'skype'=> $recruiter->skype, 'phone'=>$recruiter->mobile_phone]) !!}</p>
    @endif
    <p>{{trans('resource::view.Confirm mail offer notice')}}</p>
    <p>{{trans('resource::view.Welcome :name to Rikkeisoft')}}</p>
    <p>{{trans('resource::view.Thanks & best regards')}}</p>
    {!! $signatureHtml !!}
</div>
<div class="mail-offer_content_{{ Candidate::MAIL_OFFER_DN }} hidden">
    <p>{{trans('resource::view.Dear: :name', [ 'name' => $candidate->fullname ])}}</p>
    <p>{{trans('resource::view.Mail offer notification', [ 'date' => date('m/Y') ])}}</p>
    <p>{{trans('resource::view.- Position', ['position' => getOptions::getInstance()->getRole($candidate->position_apply)])}}</p>
    <p>{{trans('resource::view.- Team', ['team' => $candidate->team_id ? ($teamId ? $teamId->name : '') : ''])}}</p>
    <p>{{trans('resource::view.- Start working date', ['date' => View::getDate($candidate->start_working_date, 'd/m/Y')])}}</p>
    <p>{{trans('resource::view.Working place DN')}}</p>
    <p>{!! trans('resource::view.Mail offer, information in file') !!}</p>
    @if($recruiter)
    <p>{!! trans('resource::view.Mail offer, lien he :m: :name – :email – :skype – :phone',['m' => $gender, 'name'=>$recruiter->name, 'email'=>$recruiter->email, 'skype'=> $recruiter->skype, 'phone'=>$recruiter->mobile_phone]) !!}</p>
    @endif
    <p>{{trans('resource::view.Confirm mail offer notice')}}</p>
    <p>{{trans('resource::view.Welcome :name to Rikkeisoft')}}</p>
    <p>{{trans('resource::view.Thanks & best regards')}}</p>
    {!! $signatureHtml !!}
</div>
<div class="mail-offer_content_{{ Candidate::MAIL_OFFER_HCM }} hidden">
    <p>{{trans('resource::view.Dear: :name', [ 'name' => $candidate->fullname ])}}</p>
    <p>{{trans('resource::view.Mail offer notification', [ 'date' => date('m/Y') ])}}</p>
    <p>{{trans('resource::view.- Position', ['position' => getOptions::getInstance()->getRole($candidate->position_apply)])}}</p>
    <p>{{trans('resource::view.- Team', ['team' => $candidate->team_id ? ($teamId ? $teamId->name : '') : ''])}}</p>
    <p>{{trans('resource::view.- Start working date', ['date' => View::getDate($candidate->start_working_date, 'd/m/Y')])}}</p>
    <p>{!! trans('resource::view.- Working time of day HCM') !!}</p>
    <p>{{trans('resource::view.Working place HCM')}}</p>
    <p>{!! trans('resource::view.Mail offer, information in file') !!}</p>
    @if($recruiter)
        <p>{!! trans('resource::view.Mail offer, lien he :m: :name – :email – :skype – :phone',['m' => $gender, 'name'=>$recruiter->name, 'email'=>$recruiter->email, 'skype'=> $recruiter->skype, 'phone'=>$recruiter->mobile_phone]) !!}</p>
    @endif
    <p>{{trans('resource::view.Confirm mail offer notice')}}</p>
    <p>{{trans('resource::view.Welcome :name to Rikkeisoft')}}</p>
    <p>{{trans('resource::view.Thanks & best regards')}}</p>
    {!! $signatureHCMHtml !!}
</div>
<div class="mail-offer_content_{{ Candidate::MAIL_OFFER_JP }} hidden">
    {!! trans('resource::view.mail_offer_content.jp', [
        'dear_name' => e($candidate->fullname)
    ]) !!}
    {!! $signatureJPHtml !!}
</div>
