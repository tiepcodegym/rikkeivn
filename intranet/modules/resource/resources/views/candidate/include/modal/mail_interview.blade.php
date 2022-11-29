<?php
use Rikkei\Resource\Model\Candidate;
use Rikkei\Resource\View\getOptions;
use Rikkei\Resource\View\View;
use Rikkei\Resource\Model\CandidateMail;
use Rikkei\Team\View\Permission;
use Rikkei\Core\View\View as CoreView;

$lastSendInterview = CandidateMail::getLastSend($candidate->email, [Candidate::MAIL_INTERVIEW_TEST_HH3, Candidate::MAIL_INTERVIEW_TEST_HH4, Candidate::MAIL_INTERVIEW_TEST_DN]);

$aryMailLocates = [
    Candidate::MAIL_INTERVIEW_TEST_HH3 => [
        'label' => trans('resource::view.Interview HH3'),
    ],
    Candidate::MAIL_INTERVIEW_TEST_HH4 => [
        'label' => trans('resource::view.Interview HH4'),
    ],
    Candidate::MAIL_INTERVIEW_TEST_HANDICO => [
        'label' => trans('resource::view.Interview Handico'),
    ],
    Candidate::MAIL_INTERVIEW_TEST_DN => [
        'label' => trans('resource::view.Interview Đà Nẵng'),
    ],
    Candidate::MAIL_INTERVIEW_TEST_HCM => [
        'label' => trans('resource::view.Interview HCM'),
    ],
    Candidate::MAIL_INTERVIEW_TEST_JP => [
        'label' => trans('resource::view.Interview Japan'),
    ],
    Candidate::MAIL_INTERVIEW_CONFIRM_JP => [
        'label' => trans('resource::view.Interview confirm Japan')
    ],
];
$mailActive = $isDn ? Candidate::MAIL_INTERVIEW_TEST_DN : Candidate::MAIL_INTERVIEW_TEST_HH3;
$articleLink = config('services.tuyen_dung_url');
?>
<div class="modal fade" id="modal-interview_content" tabindex="-1" role="dialog"  data-keyboard="false" >
    <div class="modal-dialog" style="width: 800px">
        <div class="modal-content"  >
            <div class="modal-body bg-wrapper">
                <h4 class="modal-title">{{ trans('resource::view.Mail content') }}</h4>
                <section class="box box-info" data-has="1">
                    <div class="box-body">
                        @foreach($aryMailLocates as $idLocate => $locate)
                        <label class="form-label">
                            <input type="radio" name="mail_interview_content" value="{{ $idLocate }}" data-target="interview_content_{{ $idLocate }}" {{ $mailActive == $idLocate ? 'checked="checked"' : '' }} />
                            <span>{{ $locate['label'] }}</span>
                        </label>
                        @endforeach

                        <div>
                            @if ($lastSendInterview)
                            <span class="error font-size-12px"><em>*</em> {{ trans('resource::view.Last send: :date', [ 'date' =>  View::getDate($lastSendInterview->updated_at, 'Y-m-d H:i')]) }}</span>
                            @endif
                        </div>

                        @foreach($aryMailLocates as $idLocate => $locate)
                        <div class="container-interview container-interview_content_{{ $idLocate }} {{ $mailActive == $idLocate ? '' : 'hidden' }}">
                            <textarea id="interview_content_{{ $idLocate }}">
                            
                            </textarea>
                        </div>
                        @endforeach
                    </div>
                    <input type="hidden" id="candidate_email" value="{{$candidate->email}}" />
                    <input type="hidden" id="candidate_id" value="{{$candidate->id}}" />
                    <input type="hidden" id="type" value="{{Candidate::MAIL_INTERVIEW}}" />
                </section>
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
                <button type="button" class="btn btn-primary btn-send-mail-interview" onclick="sendMail(this, {{Candidate::MAIL_INTERVIEW_TEST_HH3}}, 'mail_interview');">
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
    var aryMailLocates = JSON.parse('{!! json_encode(array_keys($aryMailLocates)) !!}');
</script>

<?php
$mailTestTime = View::getDate($candidate->interview_plan, 'd/m/Y H:i');

$strPos = [];
if (!empty($candidate->positions)) :
    $positions = explode(',', $candidate->positions);
    if (is_array($positions) && count($positions)) :
        foreach ($positions as $pos) :
            $strPos[] = getOptions::getInstance()->getRole($pos);
        endforeach;
    endif;
endif;

?>

<div class="mail-interview_content_{{ Candidate::MAIL_INTERVIEW_TEST_HH4 }} hidden">
    <p>{{ trans('resource::view.Dear :name,', [ 'name' => $candidate->fullname ]) }}</p>
    <p>{{ trans('resource::view.Thank you to submited :apply', [ 'apply' => implode(', ', $strPos) ]) }}</p>
    <p>{{ trans('resource::view.Mail interview invite to interview at position with information') }}</p>
    <p>{{ trans('resource::view.- Test time', [ 'date' => $mailTestTime ]) }}</p>
    <p>{{ trans('resource::view.- Test place HH4') }} (Bản đồ: <a target="_blank" href="https://goo.gl/maps/Z4T8VRxPhjn">https://goo.gl/maps/Z4T8VRxPhjn</a>).</p>
    <p>- {{ trans('resource::view.Please complete the candidate information link before participating in the interview') }}
        <a class="link" target="_blank" href="{{ route('test::candidate.input_infor') }}">https://rikkei.vn/test/candidate-information</a>
    </p>
    <p>{!! trans('resource::view.reply this email before 1 day to confirm. Contact person: :m: :name – :phone', [ 'm' => $gender, 'name' =>  $recruiter ? $recruiter->name : '', 'phone' => $recruiter ? $recruiter->mobile_phone : '']) !!}</p>
    <p style="font-style: italic;">
        {!! trans('resource::view.For more favorable and more favorable interviews, please :name read more information about our company at the links :link below!', ['link' => $articleLink]) !!}</p>
    <p>{{ trans('resource::view.We look forward to welcoming you') }}</p>
    <p>{{ trans('resource::view.Thanks & best regards') }}</p>
    {!! $signatureHtml !!}
</div>
<div class="mail-interview_content_{{ Candidate::MAIL_INTERVIEW_TEST_HANDICO }} hidden">
    <p>{{ trans('resource::view.Dear :name,', [ 'name' => $candidate->fullname ]) }}</p>
    <p>{{ trans('resource::view.Thank you to submited :apply', [ 'apply' => implode(', ', $strPos) ]) }}</p>
    <p>{{ trans('resource::view.Mail interview invite to interview at position with information') }}</p>
    <p>{{ trans('resource::view.- Test time', [ 'date' => $mailTestTime ]) }}</p>
    <p>{{ trans('resource::view.- Test place Handico') }}</p>
    <p>- {{ trans('resource::view.Please complete the candidate information link before participating in the interview') }}
        <a class="link" target="_blank" href="{{ route('test::candidate.input_infor') }}">https://rikkei.vn/test/candidate-information</a>
    </p>
    <p>{!! trans('resource::view.reply this email before 1 day to confirm. Contact person: :m: :name – :phone', [ 'm' => $gender, 'name' =>  $recruiter ? $recruiter->name : '', 'phone' => $recruiter ? $recruiter->mobile_phone : '']) !!}</p>
    <p style="font-style: italic;">
        {!! trans('resource::view.For more favorable and more favorable interviews, please :name read more information about our company at the links :link below!', ['link' => $articleLink]) !!}</p>
    <p>{{ trans('resource::view.We look forward to welcoming you') }}</p>
    <p>{{ trans('resource::view.Thanks & best regards') }}</p>
    {!! $signatureHtml !!}
</div>

<div class="mail-interview_content_{{ Candidate::MAIL_INTERVIEW_TEST_HH3 }} hidden">
    <p>{{trans('resource::view.Dear :name,', [ 'name' => $candidate->fullname ])}}</p>
    <p>{{ trans('resource::view.Thank you to submited :apply', [ 'apply' => implode(', ', $strPos) ]) }}</p>
    <p>{{ trans('resource::view.Mail interview invite to interview at position with information') }}</p>
    <p>{{ trans('resource::view.- Test time', [ 'date' => $mailTestTime ]) }}</p>
    <p>{{ trans('resource::view.- Test place') }}</p>
    <p>- {{ trans('resource::view.Please complete the candidate information link before participating in the interview') }}
        <a class="link" target="_blank" href="{{ route('test::candidate.input_infor') }}">https://rikkei.vn/test/candidate-information</a>
    </p>
    <p>{!! trans('resource::view.reply this email before 1 day to confirm. Contact person: :m: :name – :phone', ['m' => $gender, 'name' =>  $recruiter ? $recruiter->name : '', 'phone' => $recruiter ? $recruiter->mobile_phone : '']) !!}</p>
    <p style="font-style: italic;">
        {!! trans('resource::view.For more favorable and more favorable interviews, please :name read more information about our company at the links :link below!', ['link' => $articleLink]) !!}</p>
    <p>{{ trans('resource::view.We look forward to welcoming you') }}</p>
    <p>{{ trans('resource::view.Thanks & best regards') }}</p>
    {!! $signatureHtml !!}
</div>
<div class="mail-interview_content_{{ Candidate::MAIL_INTERVIEW_TEST_DN }} hidden">
    <p>{{trans('resource::view.Dear :name,', [ 'name' => $candidate->fullname ])}}</p>
    <p>{{ trans('resource::view.Thank you to submited :apply', [ 'apply' => implode(', ', $strPos) ]) }}</p>
    <p>{{ trans('resource::view.Mail interview invite to interview at position with information') }}</p>
    <p>{{ trans('resource::view.- Test time', [ 'date' => $mailTestTime ]) }}</p>
    <p>{{ trans('resource::view.- Test place DN') }}</p>
    <p>- {{ trans('resource::view.Please complete the candidate information link before participating in the interview') }}
        <a class="link" target="_blank" href="{{ route('test::candidate.input_infor') }}">https://rikkei.vn/test/candidate-information</a>
    </p>
    <p>{!! trans('resource::view.reply this email before 1 day to confirm. Contact person: :m: :name – :phone', ['m' => $gender, 'name' =>  $recruiter ? $recruiter->name : '', 'phone' => $recruiter ? $recruiter->mobile_phone : '']) !!}</p>
    <p style="font-style: italic;">
        {!! trans('resource::view.For more favorable and more favorable interviews, please :name read more information about our company at the links :link below!', ['link' => $articleLink]) !!}</p>
    <p>{{ trans('resource::view.We look forward to welcoming you') }}</p>
    <p>{{ trans('resource::view.Thanks & best regards') }}</p>
    {!! $signatureHtml !!}
</div>

<div class="mail-interview_content_{{ Candidate::MAIL_INTERVIEW_TEST_HCM }} hidden">
    <p>{{trans('resource::view.Dear :name,', [ 'name' => $candidate->fullname ])}}</p>
    <p>{{ trans('resource::view.Thank you to submited :apply', [ 'apply' => implode(', ', $strPos) ]) }}</p>
    <p>{{ trans('resource::view.Mail interview invite to interview at position with information') }}</p>
    <p>{{ trans('resource::view.- Test time', [ 'date' => $mailTestTime ]) }}</p>
    <p>{{ trans('resource::view.- Test place HCM') }}</p>
    <p>- {{ trans('resource::view.Please complete the candidate information link before participating in the interview') }}
        <a class="link" target="_blank" href="{{ route('test::candidate.input_infor') }}">https://rikkei.vn/test/candidate-information</a>
    </p>
    <p>{!! trans('resource::view.reply this email before 1 day to confirm. Contact person: :m: :name – :phone', ['m' => $gender, 'name' =>  $recruiter ? $recruiter->name : '', 'phone' => $recruiter ? $recruiter->mobile_phone : '']) !!}</p>
    <p style="font-style: italic;">
        {!! trans('resource::view.For more favorable and more favorable interviews, please :name read more information about our company at the links :link below!', ['link' => $articleLink]) !!}</p>
    <p>{{ trans('resource::view.We look forward to welcoming you') }}</p>
    <p>{{ trans('resource::view.Thanks & best regards') }}</p>

    {!! $signatureHCMHtml !!}
</div>
<div class="mail-interview_content_{{ Candidate::MAIL_INTERVIEW_TEST_JP }} hidden">
    {!! trans('resource::view.mail_invite_interview.jp', [
        'dear_name' => e($candidate->fullname),
        'test_time' => $mailTestTime,
        'extra_link' => $extraJapanLink
    ]) !!}
    {!! $signatureJPHtml !!}
</div>
<div class="mail-interview_content_{{ Candidate::MAIL_INTERVIEW_CONFIRM_JP }} hidden">
    {!! trans('resource::view.mail_confirm_interview.jp', [
        'dear_name' => e($candidate->fullname),
        'test_time' => $mailTestTime,
        'extra_link' => $extraJapanLink
    ]) !!}
    {!! $signatureJPHtml !!}
</div>
