
<?php
use Rikkei\Welfare\Model\Partners;
use Rikkei\Welfare\Model\WelfarePartner;
use Rikkei\Welfare\View\PartnerList;

$partners = Partners::all();
$optionPartners = $partners->pluck('name', 'id')->toArray();
$genderOption = Partners::optionGender();

?>

<div class="row information-partners">
    <div class="col-sm-12">
        <div class="row">
            <div class="partners-implementation col-md-7">
                <div class="box box-solid">
                    <div class="box-body">
                        <h4>
                            {{ trans('welfare::view.Information Partner Implementation')}}
                        </h4>
                        <div class="form-horizontal">
                            <div class="form-group">
                                {{ Form::label('partner_id', trans('welfare::view.Partners'), ['class' => 'col-lg-2 control-label']) }}
                                <div class="input-box col-lg-9">
                                    {{ Form::select('wel_partner[partner_id]', $optionPartners, isset($welfarePartner) ? $welfarePartner->partner_id : '', ['class' => 'form-control partner_id', 'placeholder' => trans('welfare::view.Select a partner'), 'onload' => 'loadText()']) }}
                                </div>
                                <div class="col-lg-1">
                                    <button type="button" class="btn btn-default" id="list-partner" data-toggle="modal" data-target="#modal-list-partner">...</button>
                                </div>
                            </div>
                        </div>
                        <div class="form-horizontal">
                            <div class="form-group">
                                {{ Form::label('address', trans('welfare::view.Address Partners'), ['class' => 'col-lg-2 control-label']) }}
                                <div class="input-box col-lg-10">
                                    {{ Form::text('wel_partner[address]', isset($welfarePartner) ? $welfarePartner->address : null, ['class' => 'form-control', 'readonly']) }}
                                </div>
                            </div>
                        </div>
                        <div class="form-horizontal">
                            <div class="form-group">
                                {{ Form::label('website', trans('welfare::view.Website Partners'), ['class' => 'col-lg-2 control-label']) }}
                                <div class="input-box col-lg-10">
                                    {{ Form::text('wel_partner[website]', isset($welfarePartner) ? $welfarePartner->website : null, ['class' => 'form-control', 'readonly']) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="other-information col-md-5">
                <div class="box box-solid">
                    <div class="box-body">
                        <h4>
                            {{ trans('welfare::view.Other Information')}}
                        </h4>
                        <div class="form-horizontal">
                            <div class="form-group">
                                <label class="col-lg-3 control-label">{{ trans('welfare::view.Fee Return') }}</label>
                                <div class="input-box col-lg-9">
                                    {{ Form::text('wel_partner[fee_return]', isset($welfarePartner) ? number_format($welfarePartner->fee_return) : null,
                                                ['class' => 'form-control', 'placeholder' => '0.00', 'id' => 'partner-fee-return']) }}
                                </div>
                            </div>
                        </div>
                        <div class="form-horizontal">
                            <div class="form-group">
                                <label class="col-lg-3 control-label">{{ trans('welfare::view.Note') }}</label>
                                <div class="input-box col-lg-9">
                                    {{ Form::textarea('wel_partner[note]', isset($welfarePartner) ? $welfarePartner->note : null, ['class' => 'form-control', 'rows' => '3']) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="contact">
            <div class="box box-solid">
                <div class="box-body">
                    <h4>
                        {{ trans('welfare::view.Contact Information')}}
                    </h4>
                    <div class="col-md-6 form-horizontal">
                        <div class="form-group">
                            {{ Form::label('rep_name', trans('welfare::view.Rep Name'), ['class' => 'col-md-2 control-label']) }}
                            <div class="input-box col-lg-10">
                                {{ Form::text('wel_partner[rep_name]', isset($welfarePartner) ? $welfarePartner->rep_name : null, ['class' => 'form-control', 'placeholder' => trans('welfare::view.Rep Name')]) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 form-horizontal">
                        <div class="form-group">
                            {{ Form::label('rep_phone', trans('welfare::view.Rep Phone'), ['class' => 'col-md-2 control-label']) }}
                            <div class="input-box col-lg-10">
                                {{ Form::text('wel_partner[rep_phone]', isset($welfarePartner) ? $welfarePartner->rep_phone : null, ['class' => 'form-control', 'placeholder' => trans('welfare::view.Rep Phone')]) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 form-horizontal">
                        <div class="form-group">
                            {{ Form::label('rep_gender', trans('welfare::view.Rep Gender'), ['class' => 'col-md-2 control-label']) }}
                            <div class="input-box col-lg-10">
                                {{ Form::select('wel_partner[rep_gender]', $genderOption, isset($welfarePartner) ? $welfarePartner->rep_gender : null, ['class' => 'form-control']) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 form-horizontal">
                        <div class="form-group">
                            {{ Form::label('rep_phone_company', trans('welfare::view.Rep Phone Company'), ['class' => 'col-md-2 control-label']) }}
                            <div class="input-box col-lg-10">
                                {{ Form::text('wel_partner[rep_phone_company]', isset($welfarePartner) ? $welfarePartner->rep_phone_company : null, ['class' => 'form-control', 'placeholder' => trans('welfare::view.Rep Phone Company')]) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 form-horizontal">
                        <div class="form-group">
                            {{ Form::label('rep_position', trans('welfare::view.Rep Position'), ['class' => 'col-md-2 control-label']) }}
                            <div class="input-box col-lg-10">
                                {{ Form::text('wel_partner[rep_position]', isset($welfarePartner) ? $welfarePartner->rep_position : null, ['class' => 'form-control', 'placeholder' => trans('welfare::view.Rep Position')]) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 form-horizontal">
                        <div class="form-group">
                            {{ Form::label('email', trans('welfare::view.Email Partners'), ['class' => 'col-md-2 control-label']) }}
                            <div class="input-box col-lg-10">
                                {{ Form::text('wel_partner[rep_email]', isset($welfarePartner) ? $welfarePartner->email : null, ['class' => 'form-control', 'placeholder' => trans('welfare::view.Email Partners')]) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal-list-partner" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-list"></i> {{ trans('welfare::view.List Partners') }}</h4>
            </div>
            <div class="modal-body">
                <div class="box box-info">
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover partner-dataTable" id="table-partner" data-url="{{ route('welfare::welfare.partner.list') }}">
                                <thead>
                                    <tr>
                                        <th class="">{{ trans('welfare::view.Name Partners') }}</th>
                                        <th class="">{{ trans('welfare::view.Email Partners') }}</th>
                                        <th class="">{{ trans('welfare::view.Phone Partners') }}</th>
                                        <th class="">{{ trans('welfare::view.Address Partners') }}</th>
                                        <th class="">{{ trans('welfare::view.Website Partners') }}</th>
                                        <th class="no-sort">&nbsp;
                                            <button type="button" class="btn btn-edit edit-modal-partner hidden" data-id="" data-name="" data-url="{{ route('welfare::welfare.partner.edit') }}">
                                                <span class="glyphicon glyphicon-edit"></span>
                                            </button>
                                            <button type="button" class="delete-modal-partner btn btn-danger hidden disabled" data-id="" data-name="" data-url="{{ route('welfare::welfare.partner.delete') }}" data-confirm="{{ trans('welfare::view.Confirm Delete') }}">
                                                <span class="glyphicon glyphicon-trash"></span>
                                            </button>
                                        </th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-add" id="add-partner" data-url="{{ route('welfare::welfare.partner.create') }}"><i class="fa fa-plus-circle"></i> {{ trans('welfare::view.Add New') }}</button>
                <button type="button" class="btn-add" id="btn-choose-partner" data-url="{{ route('welfare::welfare.partner.edit') }}"><i class="fa fa-check-square-o"></i> {{ trans('welfare::view.Choose') }}</button>
                <button type="button" class="btn-add" data-dismiss="modal"><span class="bootstrap-dialog-button-icon fa fa-close"></span> {{ trans('welfare::view.Close') }}</button>
            </div>
        </div>
    </div>
</div>
