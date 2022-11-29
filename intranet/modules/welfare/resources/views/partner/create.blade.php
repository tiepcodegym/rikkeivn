<?php
use Rikkei\Welfare\Model\PartnerGroup;
use Rikkei\Welfare\Model\Partners;

$listPartnerGroup = PartnerGroup::select('name', 'id')->get();

$genderOption = Partners::optionGender();

?>
<div id="modal-add-partner" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <!-- Modal content-->
        <div class="modal-content">
            {{ Form::open(['route' => 'welfare::welfare.partner.add', 'class' => 'addPartner', 'id' => 'form-add-partner']) }}
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="bootstrap-dialog-title" id="title-add"><i class="fa fa-plus-square"></i> {{ trans('welfare::view.Add Partners') }}</h4>
                <h4 class="bootstrap-dialog-title hidden" id="title-update"><i class="fa fa fa-pencil-square"></i> {{ trans('welfare::view.Update Partners') }}</h4>
            </div>

            <div class="modal-body">
                <input type="text" class="hidden" id="isPartner" name="isPartner" value=""/>
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-info-circle"></i> {{ trans('welfare::view.General Information') }}</h3>
                    </div>
                    <div class="box-body">
                        <div class="col-md-5 form-horizontal code-partner">
                            <div class="form-group">
                                <label class="col-lg-3 control-label required">{{ trans('welfare::view.Code Partners') }}<em>*</em></label>
                                <div class="input-box col-lg-9">
                                    <input type="text" name="code" id="code" class="form-control" placeholder="{{ trans('welfare::view.Code Partners') }}" value="" disabled="disabled" readonly="true" />
                                    <label id="error-code-partner" class="error hidden" style="color: red;">{{ trans('core::message.This field is required') }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7 form-horizontal">
                            <div class="form-group">
                                <label class="col-lg-3 control-label required">{{ trans('welfare::view.Name Partners') }}<em>*</em></label>
                                <div class="input-box col-lg-9">
                                    {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => trans('welfare::view.Name Partners'), 'id' => 'name']) }}
                                    <label id="error-name-partner" class="error hidden" style="color: red;">{{ trans('core::message.This field is required') }}</label>
                                    <p id="error-general" class="hidden error" style="margin-top: 7px;"></p>
                                </div>
                            </div>
                        </div>
                        <div class="row form-horizontal partner-type-id">
                            <div class="col-md-11">
                                <div class="form-group">
                                    <label class="col-lg-2 control-label required">{{ trans('welfare::view.Group Partners') }}<em>*</em></label>
                                    <div class="input-box col-lg-10">
                                        {{-- Form::select('partner_type_id', $listPartnerGroup, null, ['class' => 'form-control select-search val-custom', 'id' => 'partner_type_id', 'data-placeholder' => trans('welfare::view.Select a partner group')]) --}}
                                        <select class="form-control val-custom"
                                                id="partner_type_id" data-placeholder="{{ trans('welfare::view.Select a partner group') }}"
                                                name="partner_type_id" >
                                            <option></option>
                                            @if(isset($listPartnerGroup))
                                                @foreach($listPartnerGroup as $item)
                                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <div class="val-message"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-1 form-horizontal">
                                <div class="form-group pull-right">
                                    <button type="button" class="btn btn-default" id="partner-group" data-toggle="Partner Group" data-target="#list-partner-group">...</button>
                                </div>
                            </div>
                        </div>

                        <div class="row tax-code-partner">
                            <div class="col-md-6 form-horizontal">
                                <div class="form-group">
                                    {{ Form::label('address', trans('welfare::view.Address Partners'), ['class' => 'col-lg-3 control-label']) }}
                                    <div class="input-box col-lg-9 address-partner">
                                        {{ Form::text('address', null, ['class' => 'form-control', 'placeholder' => trans('welfare::view.Address Partners')]) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6"></div>
                        </div>
                        <div class="col-md-6 form-horizontal">
                            <div class="form-group">
                                {{ Form::label('phone', trans('welfare::view.Phone Partners'), ['class' => 'col-lg-3 control-label']) }}
                                <div class="input-box col-lg-9">
                                    {{ Form::text('phone', null, ['class' => 'form-control', 'placeholder' => trans('welfare::view.Phone Partners')]) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 form-horizontal">
                            <div class="form-group">
                                {{ Form::label('fax', trans('welfare::view.Fax Partners'), ['class' => 'col-lg-3 control-label']) }}
                                <div class="input-box col-lg-9">
                                    {{ Form::text('fax', null, ['class' => 'form-control', 'placeholder' => trans('welfare::view.Fax Partners')]) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 form-horizontal">
                            <div class="form-group">
                                {{ Form::label('part_email', trans('welfare::view.Email Partners'), ['class' => 'col-lg-3 control-label']) }}
                                <div class="input-box col-lg-9">
                                    {{ Form::text('part_email', null, ['class' => 'form-control', 'placeholder' => trans('welfare::view.Email Partners')]) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 form-horizontal">
                            <div class="form-group">
                                {{ Form::label('website', trans('welfare::view.Website Partners'), ['class' => 'col-lg-3 control-label']) }}
                                <div class="input-box col-lg-9">
                                    {{ Form::text('website', null, ['class' => 'form-control', 'placeholder' => trans('welfare::view.Website Partners')]) }}
                                </div>
                            </div>
                        </div>
                        <div class="row tax-code-partner">
                            <div class="col-md-6 form-horizontal">
                                <div class="form-group">
                                    {{ Form::label('tax_code', trans('welfare::view.Tax Code Partners'), ['class' => 'col-lg-3 control-label']) }}
                                    <div class="input-box col-lg-9">
                                        {{ Form::text('tax_code', null, ['class' => 'form-control', 'placeholder' => trans('welfare::view.Tax Code Partners')]) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                            </div>
                        </div>
                        <div class="col-md-6 form-horizontal">
                            <div class="form-group">
                                {{ Form::label('bank_account', trans('welfare::view.Bank Account Partners'), ['class' => 'col-lg-3 control-label']) }}
                                <div class="input-box col-lg-9">
                                    {{ Form::text('bank_account', null, ['class' => 'form-control', 'placeholder' => trans('welfare::view.Bank Account Partners')]) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 form-horizontal">
                            <div class="form-group">
                                {{ Form::label('bank_account_address', trans('welfare::view.Bank Account Address Partners'), ['class' => 'col-lg-3 control-label']) }}
                                <div class="input-box col-lg-9">
                                    {{ Form::text('bank_account_address', null, ['class' => 'form-control', 'placeholder' => trans('welfare::view.Bank Account Address Partners')]) }}
                                </div>
                            </div>
                        </div>
                        <div class="row form-horizontal tax-code-partner">
                            <div class="col-md-6 form-horizontal">
                                <div class="form-group">
                                    {{ Form::label('note', trans('welfare::view.Note'), ['class' => 'col-lg-3 control-label']) }}
                                    <div class="input-box col-lg-9">
                                        {{ Form::textarea('note', null, ['class' => 'form-control', 'placeholder' => trans('welfare::view.Note'), 'rows' => '4']) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-info-circle"></i> {{ trans('welfare::view.Contact Info') }}</h3>
                    </div>
                    <div class="box-body">
                        <div class="col-md-6 form-horizontal">
                            <div class="form-group">
                                {{ Form::label('rep_name', trans('welfare::view.Rep Name'), ['class' => 'col-lg-3 control-label']) }}
                                <div class="input-box col-lg-9">
                                    {{ Form::text('rep_name', null, ['class' => 'form-control', 'placeholder' => trans('welfare::view.Rep Name')]) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 form-horizontal">
                            <div class="form-group">
                                {{ Form::label('rep_card_id', trans('welfare::view.Rep Card ID'), ['class' => 'col-lg-3 control-label']) }}
                                <div class="input-box col-lg-9">
                                    {{ Form::text('rep_card_id', null, ['class' => 'form-control', 'placeholder' => trans('welfare::view.Rep Card ID')]) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 form-horizontal">
                            <div class="form-group">
                                {{ Form::label('rep_position', trans('welfare::view.Rep Position'), ['class' => 'col-lg-3 control-label']) }}
                                <div class="input-box col-lg-9">
                                    {{ Form::text('rep_position', null, ['class' => 'form-control', 'placeholder' => trans('welfare::view.Rep Position')]) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 form-horizontal">
                            <div class="form-group">
                                {{ Form::label('rep_card_id_date', trans('welfare::view.Rep Card ID Date'), ['class' => 'col-lg-3 control-label']) }}
                                <div class="input-box col-lg-9">
                                    {{ Form::text('rep_card_id_date', null, ['class' => 'form-control date-picker', 'placeholder' => 'yyyy-mm-dd']) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 form-horizontal">
                            <div class="form-group">
                                {{ Form::label('rep_gender', trans('welfare::view.Rep Gender'), ['class' => 'col-lg-3 control-label']) }}
                                <div class="input-box col-lg-9">
                                    {{ Form::select('rep_gender', $genderOption, null, ['class' => 'form-control']) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 form-horizontal">
                            <div class="form-group">
                                {{ Form::label('rep_card_id_address', trans('welfare::view.Rep Card ID Address'), ['class' => 'col-lg-3 control-label']) }}
                                <div class="input-box col-lg-9">
                                    {{ Form::text('rep_card_id_address', null, ['class' => 'form-control', 'placeholder' => trans('welfare::view.Rep Card ID Address')]) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 form-horizontal">
                            <div class="form-group">
                                {{ Form::label('rep_email', trans('welfare::view.Email Partners'), ['class' => 'col-lg-3 control-label']) }}
                                <div class="input-box col-lg-9">
                                    {{ Form::text('rep_email', null, ['class' => 'form-control', 'placeholder' => trans('welfare::view.Email Partners')]) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 form-horizontal">
                            <div class="form-group">
                                {{ Form::label('rep_phone', trans('welfare::view.Rep Phone'), ['class' => 'col-lg-3 control-label']) }}
                                <div class="input-box col-lg-9">
                                    {{ Form::text('rep_phone', null, ['class' => 'form-control', 'placeholder' => trans('welfare::view.Rep Phone')]) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 form-horizontal">
                            <div class="form-group">
                                {{ Form::label('rep_phone_home', trans('welfare::view.Rep Phone Home'), ['class' => 'col-lg-3 control-label']) }}
                                <div class="input-box col-lg-9">
                                    {{ Form::text('rep_phone_home', null, ['class' => 'form-control', 'placeholder' => trans('welfare::view.Rep Phone Home')]) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 form-horizontal">
                            <div class="form-group">
                                {{ Form::label('rep_phone_company', trans('welfare::view.Rep Phone Company'), ['class' => 'col-lg-3 control-label']) }}
                                <div class="input-box col-lg-9">
                                    {{ Form::text('rep_phone_company', null, ['class' => 'form-control', 'placeholder' => trans('welfare::view.Rep Phone Company')]) }}
                                </div>
                            </div>
                        </div>
                        <div class="row tax-code-partner">
                            <div class="col-md-6 form-horizontal">
                                <div class="form-group">
                                    {{ Form::label('rep_address', trans('welfare::view.Address Partners'), ['class' => 'col-lg-3 control-label']) }}
                                    <div class="input-box col-lg-9 contacts-address-partner">
                                        {{ Form::text('rep_address', null, ['class' => 'form-control', 'placeholder' => trans('welfare::view.Address Partners')]) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <div class="bootstrap-dialog-footer-buttons">
                    <button type="submit" class="btn-add-partner btn-add" id="btn-add-partner"><i class="fa fa-check-square-o"></i> {{ trans('welfare::view.Add') }} <span class="_uploading hidden" id="uploading"><i class="fa fa-spin fa-refresh"></i></span></button>
                    <button type="button" class="btn-close-modal btn-add" data-dismiss="modal"><span class="bootstrap-dialog-button-icon fa fa-close"></span> {{ trans('welfare::view.Close') }}</button>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>

