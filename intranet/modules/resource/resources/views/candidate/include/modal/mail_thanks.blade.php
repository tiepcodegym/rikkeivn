<?php

use Rikkei\Resource\Model\Candidate;
use Rikkei\Resource\View\View;

$isSendMailThanks = \Rikkei\Resource\Model\CandidateMail::getLastSend($candidate->email, Candidate::MAIL_THANKS);

$aryMailThanks = [
    Candidate::MAIL_THANKS => [
        'label' => trans('resource::view.Mailing thank interviewed'),
    ]
];
?>
<div class="modal fade" id="modal-thanks_content" tabindex="-1" role="dialog" data-keyboard="false">
    <div class="modal-dialog" style="width: 800px">
        <div class="modal-content">
            <div class="modal-body bg-wrapper">
                <h4 class="modal-title">{{ trans('resource::view.Mail content') }}</h4>
                <div>
                    @if ($isSendMailThanks)
                        <span class="error font-size-12px"><em>*</em> {{ trans('resource::view.Last send: :date', [ 'date' =>  View::getDate($isSendMailThanks->updated_at, 'Y-m-d H:i')]) }}</span>
                    @endif
                </div>
                <section class="box box-info" data-has="1">
                    <div class="box-body">
                        <div class="container-thanks_content container-thanks_{{Candidate::MAIL_THANKS}}">
                            <textarea id="thanks_content">

                            </textarea>
                        </div>
                    </div>
                </section>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary pull-left" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary btn-send-mail-thanks"
                        onclick="sendMail(this, {{ Candidate::MAIL_THANKS }}, 'mail_thanks');">
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
    var aryMailThanks = JSON.parse('{!! json_encode(array_keys($aryMailThanks)) !!}');
</script>
<div class="mail-thanks_{{ Candidate::MAIL_THANKS }} hidden">
    {!! $thanksHtml !!}
</div>
