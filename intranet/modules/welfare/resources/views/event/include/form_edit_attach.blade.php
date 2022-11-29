<?php
    use Rikkei\Welfare\Model\RelationName;
    use Rikkei\Welfare\Model\WelEmployeeAttachs;
    use Rikkei\Welfare\Model\WelAttachFee;

    $relation = RelationName::pluck('name', 'id')->toArray();
    $male = WelEmployeeAttachs::optionGender();
    $attachFee = WelAttachFee::getLisfeeWellAttach();
    if (empty($relativeAttach['card_id'])) {
        $input = "hidden";
        $radioNo = "checked";
        $radioYes = "";
    } else {
        $input = "";
        $radioNo = "";
        $radioYes = "checked";
    }
    $listRelative = explode(',',RelationName::getListRelation($relativeAttach['welfare_id'],$relativeAttach['support_cost']));
    
?>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title"> {{ trans('welfare::view.Edit attach employee') }} </h4>
</div>
<form class="form-horizontal" id="form-submit-add-person" method="POST">
    <div class="modal-body">
        <div class="box box-info">
            <div class="box-body welfare-content">
                <div class="form-group ">
                    <label class="col-md-3 control-label">{{ trans('welfare::view.Employee') }} <em>*</em></label>
                    <div class="input-box col-md-9 fg-valid-custom">
                        {{ csrf_field() }}
                        <input type="text" class="form-control" id="name-employee" value="{{$relativeAttach['employeeName']}}" disabled>
                        <input type="hidden" name="employee_id" value="{{$relativeAttach['employeeId']}}">
                        <input type="hidden" name="id" value="{{$relativeAttach['id']}}" id="id_attach_employee_hidden">
                        <input type="hidden" name="welfare_id" value="{{$relativeAttach['welfare_id']}}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">{{ trans('welfare::view.Rep Name') }} <em>*</em></label>
                    <div class="input-box col-md-9 fg-valid-custom">
                        <input type="text" class="form-control" placeholder="{{ trans('welfare::view.Rep Name') }}" name="name" id="name" value="{{ $relativeAttach['name'] }}">
                        <span style="color: red; display: none;" id="name-error">{{ trans('welfare::view.Require name attach') }} </span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Chế độ ưu tiên<em>*</em></label>
                    <div class="input-box col-md-9 fg-valid-custom">
                        <select data-url = {{route('welfare::welfare.check.favorable')}} class="form-control" style="width: 100%" name="support_cost" id="fee_favorable_employee_attach">
                            <option value="">{{ trans('welfare::view.Please choose') }}</option>
                            @foreach($attachFee as $keyFee => $valueFee)
                            <option value="{{$keyFee}}" @if($relativeAttach['support_cost'] == $keyFee) selected @endif>{{$valueFee}}</option>
                            @endforeach
                        </select>
                        <span style="color: red; display: none;" id="favorable-require-max-aa">{{ trans('welfare::view.favorable-require-max') }}</span>
                        <span style="color: red; display: none;" id="favorable-require-aa">{{ trans('welfare::view.Please choose 2') }} </span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">{{ trans('welfare::view.Relation') }} <em>*</em></label>
                    <div class="input-box col-md-9 fg-valid-custom">
                        <select class="form-control" style="width: 100%" name="relation_name_id" id="show-relative-favorable">
                            @if(isset($relativeAttach['relation_name_id']))
                                @foreach($listRelative as $valueListRelative)
                                    <option value="{{ $valueListRelative }}" @if($valueListRelative == $relativeAttach['relation_name_id']) selected="" @endif>{{RelationName::getNameById($valueListRelative)}}</option>
                                @endforeach
                            @else
                            <option value="">{{ trans('welfare::view.Please choose') }}</option>
                            @endif
                        </select>
                        <span style="color: red; display: none;" id="relative-attach-require">{{ trans('welfare::view.Please push relative') }} </span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">{{ trans('welfare::view.Gender') }} </label>
                    <div class="col-md-9">
                        <select class="form-control relative_employee_id val-custom" style="width: 100%" name="gender">
                            @foreach($male as $keyMale => $valueMale)
                            <option value="{{ $keyMale }}" <?php if($keyMale == $relativeAttach['gender']):?> selected <?php endif; ?>>{{ $valueMale }}</option>
                            @endforeach                                       
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">{{ trans('welfare::view.Number CMTND') }} <em>*</em></label>
                    <div class="col-md-9 radio">
                        <label style="margin-right:20px"><input {{$radioYes}} type="radio" name="check-cmt" class="check-cmt" value="1"> {{ trans('welfare::view.Yes') }}</label>   
                        <label><input type="radio" name="check-cmt" {{$radioNo}} class="check-cmt" value="2"> {{ trans('welfare::view.Not') }}</label>
                    </div>
                </div>
                <div class="form-group {{$input}}" id="form-input-cmt">
                    <label class="col-md-3 control-label"></label>
                    <div class="col-md-9 radio">
                        <input type="number" class="form-control" name="inforcar" value="{{$relativeAttach['card_id']}}" placeholder="{{ trans('welfare::view.Number CMTND') }}" id="tab-employee-CMTND">
                        <span style="color: red" class="hidden error-CMTND-employee-require"> {{ trans('welfare::view.Please push CMT') }}</span>
                        <span style="color: red" class="hidden error-CMTND-employee">{{ trans('welfare::view.Required CMTND length 13') }}</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">{{ trans('welfare::view.Birthday') }} <em>*</em></label>
                    <div class="col-md-9">
                        <input type="text" class="form-control" id="date-attach-employee" placeholder="{{ trans('welfare::view.Birthday') }}" name="birthday" value="{{$relativeAttach['birthday']}}">
                        <span style="color: red" class="hidden birthday-attach-employee-require">{{ trans('welfare::view.Please push birthday') }}</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">{{ trans('welfare::view.Phone') }}</label>
                    <div class="col-md-9">
                        <input type="number" class="form-control" placeholder="{{ trans('welfare::view.Phone') }}" name="phone" value="{{$relativeAttach['phone']}}" id="tab-employee-phone">
                        <span style="color: red" class="hidden error-phone-employee">{{ trans('welfare::view.Required phone length 13') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="submit" id="btn-add-employee-attach" class="btn btn-info btn-save-form">{{ trans('welfare::view.Save') }}</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('welfare::view.Close') }}</button>
    </div>
</form>