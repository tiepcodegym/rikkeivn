<?php
use Rikkei\Resource\Model\Candidate;
use Rikkei\Resource\View\getOptions;
use Rikkei\Resource\View\View;
use Rikkei\Resource\Model\CandidateMail;
use Rikkei\Team\View\Permission;
use Rikkei\Core\View\View as CoreView;

$aryMailInterFails = [
    Candidate::MAIL_INTERVIEW_FAIL_JP => [
        'label' => trans('resource::view.Interview fail Japan')
    ],
    Candidate::MAIL_INTERVIEW_FAIL_HN => [
        'label' => trans('resource::view.Interview fail HN')
    ],
    Candidate::MAIL_INTERVIEW_FAIL_DN => [
        'label' => trans('resource::view.Interview fail DN')
    ],
    Candidate::MAIL_INTERVIEW_FAIL_HCM => [
        'label' => trans('resource::view.Interview fail HCM')
    ],
];
$mailInterFailActive = $isDn ? Candidate::MAIL_INTERVIEW_FAIL_DN : Candidate::MAIL_INTERVIEW_FAIL_HN;

?>

<div class="modal fade" id="modal-interview_fail_content" tabindex="-1" role="dialog"  data-keyboard="false" >
    <div class="modal-dialog" style="width: 800px">
        <div class="modal-content">
            <div class="modal-body bg-wrapper">
                <h4 class="modal-title">{{ trans('resource::view.Mail content') }}</h4>
                <section class="box box-info" data-has="1">
                    <div class="box-body">
                        @foreach($aryMailInterFails as $idLocate => $locate)
                        <label class="form-label">
                            <input type="radio" name="mail_interview" value="{{ $idLocate }}" data-target="interview_fail_content_{{ $idLocate }}" {{ $mailInterFailActive == $idLocate ? 'checked' : '' }} />
                            <span>{{ $locate['label'] }}</span>
                        </label>
                        @endforeach

                        <div>
                            @if ($lastSendInterviewFail)
                            <span class="error font-size-12px"><em>*</em> {{ trans('resource::view.Last send: :date', [ 'date' =>  View::getDate($lastSendInterviewFail->updated_at, 'Y-m-d H:i')]) }}</span>
                            @endif
                        </div>

                        @foreach($aryMailInterFails as $idLocate => $locate)
                        <div class="container-interview container-interview_fail_content_{{ $idLocate }} {{ $mailInterFailActive == $idLocate ? '' : 'hidden' }}">
                            <textarea id="interview_fail_content_{{ $idLocate }}">
                            
                            </textarea>
                        </div>
                        @endforeach
                    </div>
                    <input type="hidden" id="candidate_email" value="{{$candidate->email}}" />
                    <input type="hidden" id="candidate_id" value="{{$candidate->id}}" />
                    <input type="hidden" id="type" value="{{Candidate::MAIL_INTERVIEW_FAIL}}" />
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
                <button type="button" class="btn btn-primary btn-send-mail-interview" onclick="sendMail(this, {{ $mailInterFailActive }}, 'mail_fail_interview');">
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
    var aryMailInterFails = JSON.parse('{!! json_encode(array_keys($aryMailInterFails)) !!}');
</script>

<div class="mail-interview_fail_content_{{ Candidate::MAIL_INTERVIEW_FAIL_JP }} hidden">
    {!! trans('resource::view.mail_fail_interview.jp', [
        'dear_name' => e($candidate->fullname)
    ]) !!}
    {!! $signatureJPHtml !!}
</div>
<div class="mail-interview_fail_content_{{ Candidate::MAIL_INTERVIEW_FAIL_HN }} hidden">
    {!! trans('resource::view.mail_fail_interview.hn', [
        'dear_name' => e($candidate->fullname)
    ]) !!}
    {!! $signatureHtml !!}
</div>
<div class="mail-interview_fail_content_{{ Candidate::MAIL_INTERVIEW_FAIL_DN }} hidden">
    {!! trans('resource::view.mail_fail_interview.hn', [
        'dear_name' => e($candidate->fullname)
    ]) !!}
    {!! $signatureHtml !!}
</div>
<div class="mail-interview_fail_content_{{ Candidate::MAIL_INTERVIEW_FAIL_HCM }} hidden">
    {!! trans('resource::view.mail_fail_interview.hn', [
        'dear_name' => e($candidate->fullname)
    ]) !!}
    {!! $signatureHtml !!}
</div>

