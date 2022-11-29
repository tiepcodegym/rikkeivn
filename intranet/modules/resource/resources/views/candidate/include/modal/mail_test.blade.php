<?php
use Rikkei\Resource\Model\Candidate;
use Rikkei\Resource\View\getOptions;
use Rikkei\Resource\View\View;
use Rikkei\Resource\Model\CandidateMail;
use Rikkei\Team\View\Permission;

$lastSendTest = CandidateMail::getLastSend($candidate->email, Candidate::MAIL_TEST);
?>
<div class="modal fade" id="modal-test_content" tabindex="-1" role="dialog"  data-keyboard="false" >
    <div class="modal-dialog" style="width: 800px">
        <div class="modal-content"  >
            <div class="modal-body bg-wrapper">
                <h4 class="modal-title">{{ trans('resource::view.Mail content') }}</h4>
                <section class="box box-info" data-has="1">
                    <div class="box-body">
                        @if ($lastSendTest)
                        <span class="error font-size-12px"><em>*</em> {{ trans('resource::view.Last send: :date', [ 'date' =>  View::getDate($lastSendTest->updated_at, 'Y-m-d H:i')]) }}</span>
                        @endif
                        <textarea id="test_content">
                            
                        </textarea>
                    </div>
                    <input type="hidden" id="candidate_email" value="{{$candidate->email}}" />
                    <input type="hidden" id="candidate_id" value="{{$candidate->id}}" />
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
                <button type="button" class="btn btn-primary btn-send-mail-test" onclick="sendMail(this, {{Candidate::MAIL_TEST}});">
                    <span>
                        {{ Lang::get('resource::view.Send') }}
                        <i class="fa fa-spin fa-refresh hidden"></i>
                    </span>
                </button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
<div class="mail-test_content hidden">
    <p>{{trans('resource::view.Dear: :name,', [ 'name' => $candidate->fullname ])}}</p>
    <p>&nbsp;</p>
    <p>{{ trans('resource::view.Thank you to submited.') }}</p>
    <p>&nbsp;</p>
    <p>{{ trans('resource::view.HR Department would like to invite you to participate in the professional test, detailed information as follows:') }}</p>
    <p>{{ trans('resource::view.- Test time', [ 'date' => View::getDate($candidate->test_plan, 'd/m/Y h:i') ]) }}</p>
    <p>{{ trans('resource::view.- Test place') }}</p>
    <p>{{ trans('resource::view.- Test content') }}</p>
    <p>{{ trans('resource::view.- Test form') }}</p>
    <p>&nbsp;</p>
    <p>{{ trans('resource::view.Pass candidates will receive the invitation to interview after 05 days.') }}</p>
    <p>&nbsp;</p>
    <p>{{ trans('resource::view.We hope you arrange your time to attend on time. Contact person: :name – HR department.', [ 'name' =>  $recruiter ? $recruiter->name : '']) }}</p>
    <p>&nbsp;</p>
    <p>{{ trans('resource::view.Thanks & best regards') }}</p>
    <div style="font-family: monospace;">
        <p style="font-size: 12px;margin:0px;"> -- </p>
        <p style="font-size: 12px;margin:0px;">--------------------------------------------</p>
        <p style="font-size: 12px;margin:0px;">Thanks & Best Regards,</p>
        <p style="font-size: 12px;margin:0px;">&#123;&#123; Name &#125;&#125; | HR</p>
        <p style="font-size: 12px;margin:0px;">Rikkeisoft Co,. Ltd.</p>
        <p style="font-size: 12px;margin:0px;">Mobile:&#123;&#123; Phone &#125;&#125;</p>
        <p style="font-size: 12px;margin:0px;">Skype:&#123;&#123; Skype &#125;&#125;</p>
        <p style="font-size: 12px;margin:0px;">Email: &#123;&#123; Email &#125;&#125;</p>
        <p style="font-size: 12px;margin:0px;">--------------------------------------------</p>
        <p style="font-size: 12px;margin:0px;">Head Office: 21st Floor, Handico Tower, Pham Hung St., Nam Tu Liem District, Hanoi</p>
        <p style="font-size: 12px;margin:0px;">Tel: (+84) 243 623 1685</p>
        <p style="font-size: 12px;margin:0px;">Page: https://www.facebook.com/rikkeisoft?fref=ts</p>
        <p style="font-size: 12px;margin:0px;">Website: http://rikkeisoft.com/</p>
    </div>
</div>
