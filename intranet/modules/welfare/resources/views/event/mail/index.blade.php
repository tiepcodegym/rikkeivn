<?php

use Rikkei\Welfare\Model\Event;
?>
<div class="row send-mail-event">
    <div class="col-sm-12">
        <div class="">
                {!! csrf_field() !!}
                <input class="hidden" name="email[wel_id]" value="{{ $item['id'] }}"/>
                <div class="">
                    <div class="col-md-8 col-md-offset-2">
                        <div class="success-message-send-mail hidden">
                            <div class="alert alert-success">
                                <ul>
                                    <li></li>
                                </ul>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="links" class="control-label">{{ trans('welfare::view.Link to join the event') }}: </label>
                            <a href="javascript:void(0)" onclick="confirmPreview()">{{ route("welfare::welfare.confirm.welfare", $item["id"]) }}</a>
                        </div>
                        <div class="form-group">
                            <label for="title" class="control-label required">{{ trans('emailnoti::view.Subject') }} <em>*</em></label>
                            <div class="">
                                <input name="email[subject]" class="form-control input-field" type="text" id="welfare-subject" value="" />
                                <label id="error-subject" class="error hidden" style="color: red;">{{ trans('core::message.This field is required') }}</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="content" class="control-label">{{ trans('emailnoti::view.Content') }} <em style="color: red">*</em></label>
                            <div class="">
                                <textarea id="content" name="email[content]" class="form-control" rows="18" value=""></textarea>
                                <label id="error-content" class="errorContent error hidden" style="color: red;">{{ trans('core::message.This field is required') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 text-center">
                        <button type="button" class="btn btn-warning preview-email-event" data-toggle="modal" data-target="">{{ trans('welfare::view.Preview') }}</button>
                        <button type="button" onclick="return submitForm();" class="btn btn-add" data-url="{{ route('welfare::welfare.send.mail') }}" id="btn-send-mail" data-allow="{{ $checkEmplOfWelfare }}" data-toggle="modal" data-target="" >
                            {{ trans('welfare::view.Send Mail') }}
                        </button>
                    </div>
                </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-preview-email" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">{{ trans('welfare::view.Content mail') }}</h4>
            </div>
            <div class="modal-body">
                <iframe id="ifr-preview-mail" frameborder="0" scrolling="auto" ></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('core::view.Close') }}</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade modal-warning" id="modal-success-send-mail">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ trans('welfare::view.Confirm send mail') }}</h4>
            </div>
            <div class="modal-body">
                <p class="text-default">{{ trans('welfare::view.Confirm send mail to employee') }}</p>
                <p class="text-change"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-close" data-dismiss="modal">{{ trans('welfare::view.Close') }}</button>
                <button type="button" class="btn btn-outline btn-confirm" data-dismiss="modal">{{ trans('welfare::view.Confirm') }}</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div><!-- end modal warning cofirm -->
<div class="modal fade modal-warning" id="modal-warning-send-mail">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ trans('welfare::view.warning modal title') }}</h4>
            </div>
            <div class="modal-body">
                <p class="text-default">{{ trans('welfare::view.No employee participation') }}</p>
                <p class="text-change"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-close" data-dismiss="modal">{{ trans('welfare::view.Close') }}</button>
                <button type="button" class="btn btn-outline btn-confirm" data-dismiss="modal">{{ trans('welfare::view.Confirm') }}</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div><!-- end modal warning cofirm -->
