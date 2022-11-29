<?php
    use Rikkei\Core\View\CoreUrl;
    use Carbon\Carbon;
?>
<form id="form_asset_allocation" class="form-disabled-submit" method="POST" action="{{ route('asset::asset.asset-allocation') }}" accept-charset="UTF-8" autocomplete="off">
    {!! csrf_field() !!}
    <div class="modal-header">
        <h3 class="modal-title">{{ trans('asset::view.Asset allocation') }}</h3>
    </div>
    <div class="modal-body">
        <div class="box box-solid box-modal
        ">
            @include('asset::item.include.asset_information')
        </div>
        <!-- /. box -->
        <div class="box box-solid box-modal">
            <div class="box-header with-border box-header-modal">
                <h3 class="box-title"><i class="fa fa-info-circle"></i> {{ trans('asset::view.Asset allocation information') }}</h3>
            </div>
            <div class="box-body">
                <div class="form-group">
                    <label class="control-label required">{{ trans('asset::view.Asset user') }} <em>*</em></label>
                    <div class="input-box">
                        <select name="item[employee_id]" id="employee_id" class="form-control select-search search-employee" data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}" style="width: 100%;">
                            <option value="">&nbsp;</option>
                        </select>
                        <label class="asset-error" id="employee_id-error">{{ trans('asset::message.The field is required') }}</label>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label required">{{ trans('asset::view.Select the asset request') }} <em>*</em></label>
                    <div class="input-box">
                        <select name="item[request_id]" id="request_asset" class="form-control select-search search-request-asset" style="width: 100%;">
                        </select>
                        <label class="asset-error" id="request_asset-error">{{ trans('asset::message.The field is required') }}</label>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label required">{{ trans('asset::view.Allocation date') }} <em>*</em></label>
                    <div class="input-box">
                        <div class="row">
                            <div class="col-sm-5">
                                <div class='input-group date datetime-picker' id="received_date_picker">
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                    <input type="text" name="item[received_date]" id="received_date" class="form-control" value="{{ Carbon::now()->format('d-m-Y') }}"/>
                                </div>
                                <label class="asset-error" id="received_date-error">{{ trans('asset::message.The field is required') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label required">{{ trans('asset::view.Allocation reason') }} <em>*</em></label>
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
        <button type="submit" class="btn btn-primary pull-right btn-submit" onclick="return validateSubmitAllocation();">{{ trans('asset::view.Save') }}</button>
    </div>
</form>

<script src="{{ CoreUrl::asset('manage_asset/js/manage_asset.approve.js') }}"></script>