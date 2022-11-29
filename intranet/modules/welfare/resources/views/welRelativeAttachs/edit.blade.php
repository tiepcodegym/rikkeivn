<?php
use Rikkei\Welfare\Model\RelationName;
use Rikkei\Welfare\Model\WelEmployeeAttachs;
use Rikkei\Welfare\Model\WelAttachFee;

$relation = RelationName::pluck('name', 'id')->toArray();
$attachFee = WelAttachFee::getLisfeeWellAttach();

?>
<div class="modal fade" id="modal-add-relatives" role="dialog" aria-labelledby="myModalLabel"
     data-list-select2="{{ route('welfare::welfare.employee.search.ajax', ['id' => $item->id]) }}">
  <div class="modal-dialog" role="document">
      <div class="modal-content">
          <form action="{{ route('welfare::welfare.relative.attach.add') }}" method="POST"
              class="form-horizontal" id="form-add-wel-empl-relatives">
            {{ Form::token() }}
          <input type="hidden" id="id" name="id" value="">
          <input type="hidden" id="welid" name="welid" value="{{ $item->id }}">
          <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title" id="myModalLabel">{{ trans('welfare::view.Information relatives') }}</h4>
          </div>
          <div class="modal-body">
              <div class="box box-info">
                  <div class="box-body">
                      <div class="form-group">
                          <label class="col-md-3 control-label required" aria-required="true">{{ trans('welfare::view.Employee') }}<em>*</em></label>
                          <div class="input-box col-md-9">
                              <select name="relative_employee_id" class="form-control relative_employee_id_attached val-custom"
                                      required aria-required="true" id="employee_id" style="width: 100%"
                                      data-placeholder="{{ trans('welfare::view.Please choose employee') }}">
                              </select>
                              <div class="val-message"></div>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-md-3 control-label required" aria-required="true">{{ trans('welfare::view.Full Name') }}<em>*</em></label>
                          <div class="input-box col-md-9">
                              <input type="text" name="name" id="name" class="form-control" placeholder="{{ trans('welfare::view.Full Name') }}" value="">
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-md-3 control-label">{{ trans('welfare::view.Priority mode') }}</label>
                          <div class="input-box col-md-9 fg-valid-custom">
                              <select data-url = "{{ route('welfare::welfare.relation.select.ajax') }}" class="form-control" style="width: 100%"
                                      name="support_cost" id="fee_favorable_attached">
                                    <option value="0">{{ trans('welfare::view.Please choose') }}</option>
                                    @foreach($attachFee as $keyFee => $valueFee)
                                    <option value="{{$keyFee}}">{{$valueFee}}</option>
                                    @endforeach
                              </select>
                              <p style="color: red; display: none;" id="favorable-require-max" class="error">{{ trans('welfare::view.favorable-require-max') }}</p>
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-md-3 control-label required" aria-required="true">{{ trans('welfare::view.Relation') }}<em>*</em></label>
                          <div class="input-box col-md-9">
                              <select class="form-control val-custom relation_name_id" id="relation_name_id" data-col="relation_name_id" style="width: 100%"
                                      name="relation_name_id" tabindex="-1" aria-hidden="true" data-placeholder="{{ trans('welfare::view.Please choose') }}">
                                    <option value="">{{ trans('welfare::view.Please choose') }}</option>
                                    @foreach($relation as $keyRelation => $valueRelation)
                                    <option value="{{$keyRelation}}">{{$valueRelation}}</option>
                                    @endforeach
                              </select>
                              <div class="val-message"></div>
                          </div>
                      </div>
                      <div class="form-group input-attached-gender">
                          <label class="col-md-3 control-label required" aria-required="true">{{ trans('welfare::view.Gender') }}<em>*</em></label>
                          <div class="input-box col-md-9">
                              {{ Form::select('gender', WelEmployeeAttachs::optionGender(), null, ['class' => 'form-control', 'id' => 'gender', 'data-col' => 'gender']) }}
                          </div>
                      </div>
                      <div class="form-group check-allow-import-id">
                          <label class="col-md-3 control-label required label-card-id" aria-required="true">{{ trans('welfare::view.Rep Card ID') }}<em>*</em></label>
                          <div class="input-box col-md-9 input-relative_card_id">
                              <input type="text" name="relative_card_id" id="card_id" class="form-control" placeholder="{{ trans('welfare::view.Rep Card ID') }}" value="" onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,'')">
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-md-3 control-label required" aria-required="true">{{ trans('welfare::view.Birthday') }}<em>*</em></label>
                          <div class="input-box col-md-9">
                              <input type="text" name="birthday" id="birthday" class="form-control" placeholder="{{ trans('welfare::view.Birthday') }}" value="">
                          </div>
                      </div>
                      <div class="form-group">
                          <label class="col-md-3 control-label required" aria-required="true">{{ trans('welfare::view.Phone') }}</label>
                          <div class="input-box col-md-9">
                              <input type="text" name="phone" id="phone" class="form-control" placeholder="{{ trans('welfare::view.Phone') }}" value="" data-col="phone">
                          </div>
                      </div>
                  </div>
              </div>
          </div>
          <div class="modal-footer">
              <button type="submit" class="btn btn-primary btn-add-wel-empl-relatives">{{trans('welfare::view.Save') }}</button>
              <button type="button" class="btn btn-default" data-dismiss="modal">{{trans('welfare::view.Close') }}</button>
          </div>
          </form>
      </div>
  </div>
</div>
<div class="modal fade modal-danger" id="modal-delete-relative-attached" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('welfare::welfare.relative.attach.delete') }}" method="POST"
                class="" id="form-delete-relative-attached">
                {{ Form::token() }}
                <input type="hidden" name="id-relative-attach" id="id-relative-attach" value=""/>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ trans('welfare::view.Confirm Delete') }}</h4>
            </div>
            <div class="modal-body">
                <div class="deleteContent">
                    {{ trans('welfare::view.Are you sure') }}
                </div>
                <p class="text-change"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-close" data-dismiss="modal">{{ trans('welfare::view.Close') }}</button>
                <button type="button" class="btn btn-outline btn-ok" data-dismiss="modal" data-url="">{{ trans('welfare::view.Confirm Delete') }}</button>
            </div>
            </form>
        </div>
    </div>
</div>
