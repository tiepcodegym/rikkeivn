<?php
    use Rikkei\Core\View\CoreUrl;
?>
<form id="form_asset_lost_notification" class="form-disabled-submit" method="POST" action="{{ route('asset::asset.asset-return') }}" accept-charset="UTF-8" autocomplete="off">
    {!! csrf_field() !!}
    <div class="modal-header">
        <h3 class="modal-title">{{ trans('asset::view.Return customer') }}</h3>
    </div>
    <div class="modal-body">
        <div class="box box-solid box-modal
        ">
            @include('asset::item.include.asset_information')
        </div>
        <!-- /. box -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-info-circle"></i> {{ trans('asset::view.Asset notification information') }}</h3>
            </div>
            <div class="box-body">
                <div class="form-group">
                    <label class="control-label required">{{ trans('asset::view.Return date') }} <em>*</em></label>
                     <div class="input-box">
                        <div class="row">
                            <div class="col-sm-5">
                                <div class='input-group date datetime-picker' id="received_date_picker">
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                    <input type="text" name="item[change_date]" id="received_date" class="form-control" />
                                </div>
                                <label class="asset-error" id="received_date-error">{{ trans('asset::message.The field is required') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label required">{{ trans('asset::view.Reason return') }} <em>*</em></label>
                    <div class="input-box">
                        <textarea name="item[reason]" id="reason" class="form-control textarea-100"></textarea>
                        <label class="asset-error" id="reason-error">{{ trans('asset::message.The field is required') }}</label>
                    </div>
                </div>
            </div>
        </div>
        <!-- /. box -->
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('asset::view.Close') }}</button>
        <button type="submit" class="btn btn-primary pull-right btn-submit" onclick="return validateSubmit()">{{ trans('asset::view.Save') }}</button>
    </div>
</form>

<script src="{{ CoreUrl::asset('manage_asset/js/manage_asset.approve.js') }}"></script>