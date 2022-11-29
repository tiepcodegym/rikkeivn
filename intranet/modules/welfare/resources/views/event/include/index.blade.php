<?php

use Rikkei\Team\View\Permission;
use Illuminate\Support\Facades\Input;
use Rikkei\Welfare\Model\Event;
use Rikkei\Team\View\TeamList;
use Rikkei\Welfare\Model\RelationName;

$relations = RelationName::pluck('name', 'id')->toArray();
$permision = Permission::getInstance()->isAllow('welfare::welfare.event.save');
$old = Input::old();
$maxnumber = 5;
$maxNumberFee = 11;
$teamsOptionAll = TeamList::toOption(null, true, false);
$arrayRelativeFree = [];
$arrayRelative50 =  [];
$arrayRelative100 = [];
$check = false;

if (isset($FeeAttachRelative) && $FeeAttachRelative) {
    $arrayRelativeFree = explode(',', $FeeAttachRelative->fee_free_relative);
    $arrayRelative50 =  explode(',', $FeeAttachRelative->fee50_relative);
    $arrayRelative100 = explode(',', $FeeAttachRelative->fee100_relative);
}


if (isset($item)) {
    $totalCompanyFeeActual = $welFee['empl_offical_fee_actual']*$welFee['empl_offical_number_actual'] + $welFee['empl_trial_company_fee_actual']*$welFee['empl_trial_number_actual'] + $welFee['intership_company_fee_actual']*$welFee['intership_number_actual'];
    $totalPersonalFeeActual = $welFee['intership_fee_actual']*$welFee['intership_number_actual'] + $welFee['empl_trial_fee_actual']*$welFee['empl_trial_number_actual'] + $welFee['empl_offical_fee_actual']*$welFee['empl_offical_number_actual'];
    $totalCompanyFee = $welFee['empl_offical_company_fee']*$welFee['empl_offical_number'] + $welFee['empl_trial_company_fee']*$welFee['empl_trial_number'] + $welFee['intership_company_fee']*$welFee['intership_number'];
    $totalPersonalFee = $welFee['empl_offical_fee']*$welFee['empl_offical_number'] + $welFee['empl_trial_fee']*$welFee['empl_trial_number'] + $welFee['intership_fee']*$welFee['intership_number'];
    if ($item->is_allow_attachments == Event::IS_ATTACHED) {
        $totalPersonalFeeActual += $welFee['attachments_first_fee_actual']*$welFee['attachments_first_number_actual'];
        $totalCompanyFeeActual += $welFee['attachments_first_company_fee_actual']*$welFee['attachments_first_number_actual'];
        $totalCompanyFee += $welFee['attachments_first_company_fee']*$welFee['attachments_first_number'];
        $totalPersonalFee += $welFee['attachments_first_fee']*$welFee['attachments_first_number'];
    }
}
?>
<div class="row form-horizontal">
    <!-- Description block -->
    <div class="col-md-6">
        <div class="box box-solid">
            <div class="box-body">
                <h4>
                    {{ trans('welfare::view.What event') }}
                </h4>
                <div class="form-group">
                    <label class="col-md-3 control-label required align-right"
                           aria-required="true">{{ trans('welfare::view.Name event') }}<em>*</em></label>
                    <div class="input-box col-md-9">
                        <input type="text" name="event[name]" class="form-control"
                               placeholder="{{trans('welfare::view.Name event')}}"
                               @if(isset($item)) value="{{$item['name']}}" @elseif (isset($old['event']['name'])) value="{{old('event.name')}}" @endif>
                    </div>
                </div>
                <div class="row form-group select-group-welfare">
                    <label class="col-md-3 control-label align-right"
                           aria-required="true">{{ trans('welfare::view.Group event') }}</label>
                    <div class="input-box col-md-8">
                        <select name="event[welfare_group_id]" class="form-control val-custom"
                                id="welfare_group_id" data-placeholder="{{ trans('welfare::view.Select a welfare group') }}">
                            <option></option>
                            @if(isset($groupevent))
                            @foreach( $groupevent as $value )
                            <option value="{{$value->id}}"
                                    @if(isset($item) && $item['welfare_group_id'] == $value->id) selected="true"
                                    @elseif( (old('event.welfare_group_id') == $value->id)) selected="true" @endif>{{ $value->name }}</option>
                            @endforeach
                            @endif
                        </select>
                        <div class="val-message"></div>
                    </div>
                    <div class="col-md-1">
                            <button id="add-new-group" type="button" class="btn btn-default add-college"
                                    data-toggle="modal"
                                    data-placement="bottom" data-target="#myModal_group_event" title="thêm mới"
                                    data-modal="true">...</button>
                    </div>
                </div>
                <div class="form-group select-event-team">
                    <label class="col-md-3 control-label align-right">
                        {{ trans('welfare::view.Department Organizational') }}<em>*</em></label>
                    <div class="input-box col-md-9">
                        <select class="form-control select-search input-select-team-member select2-hidden-accessible val-custom" style="width: 100%"
                                name="event_team_id" id="event-team-id" data-placeholder="{{ trans('welfare::view.Select a team') }}" autocomplete="off">
                            <option></option>
                            @if(isset($teamsOptionAll))
                            @foreach($teamsOptionAll as $option)
                            <option value="{{ $option['value'] }}"
                                    @if(isset($partinipantTeam))
                                        @foreach($partinipantTeam as $team) {
                                            @if($team->team_id == $option['value'])
                                                selected
                                            @endif
                                        @endforeach
                                    @elseif( (old('event_team_id') == $option['value']))
                                        selected="true"
                                    @endif
                                    >{{ $option['label'] }}</option>
                            @endforeach
                            @endif
                        </select>
                        <div class="val-message"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label align-right">{{ trans('welfare::view.Description') }}</label>
                    <div class="input-box col-md-9">
                        <textarea rows="4" type="text" name="event[description]" id="description"
                                  class="form-control"
                                  placeholder="{{ trans('welfare::view.Description') }}">@if(isset($item)) {{$item['description']}} @elseif (isset($old['event']['description'])) {{old('event.description')}} @endif</textarea>
                    </div>
                </div>


            </div>
        </div>
    </div>
    <!-- ./Description block -->


    <div class="col-md-6">
        <!-- When block -->
        <div class="box box-solid">
            <div class="box-body">
                <h4>
                    {{ trans('welfare::view.Time event') }}
                </h4>
                <div class="form-group">
                    <label class="col-md-4 control-label align-right required">{{ trans('welfare::view.Time_exec') }}
                        <em>*</em></label>
                    <div class="input-box col-md-4">
                        <input class="form-control valid val-custom-input" type="text" name="event[start_at_exec]"
                               id="start_at_exec" dateindex="3"
                               placeholder="yyyy-mm-dd"
                               @if(isset($item)) value="{{date_format(date_create($item['start_at_exec']), 'Y-m-d H:i')}}"
                               @elseif (isset($old['event']['start_at_exec'])) value="{{ date_format(date_create(old('event.start_at_exec')), 'Y-m-d H:i') }}" @endif>
                    </div>
                    <div class="input-box col-md-4">
                        <input class="form-control valid val-custom-input" type="text" name="event[end_at_exec]"
                               id="end_at_exec" dateindex="4"
                               placeholder="yyyy-mm-dd"
                               @if(isset($item)) value="{{date_format(date_create($item['end_at_exec']), 'Y-m-d H:i')}}"
                               @elseif (isset($old['event']['end_at_exec'])) value="{{ date_format(date_create(old('event.end_at_exec')), 'Y-m-d H:i') }}" @endif>
                    </div>
                    <div class="col-md-12 col-md-offset-4">
                        <div class="val-message-input">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-4 control-label align-right">{{ trans('welfare::view.Time_rigister') }}</label>
                    <div class="input-box col-md-4">
                        <input class="form-control valid val-custom-input" type="text"
                               name="event[start_at_register]"
                               id="start_at_register" dateindex="5"
                               placeholder="yyyy-mm-dd"
                               @if(isset($item) && $item->start_at_register!= null) value="{{date_format(date_create($item['start_at_register']), 'Y-m-d H:i')}}"
                               @elseif (isset($old['event']['start_at_register']) && $old['event']['start_at_register'] != null)
                               value=" {{ date_format(date_create(old('event.start_at_register')), 'Y-m-d H:i') }}" @endif>
                    </div>
                    <div class="input-box col-md-4">
                        <input class="form-control valid val-custom-input" type="text"
                               name="event[end_at_register]"
                               id="end_at_register" dateindex="6"
                               placeholder="yyyy-mm-dd"
                               @if(isset($item) && $item->end_at_register!= null) value="{{date_format(date_create($item['end_at_register']), 'Y-m-d H:i')}}"
                               @elseif (isset($old['event']['end_at_register']) && $old['event']['end_at_register'] != null)
                               value=" {{ date_format(date_create(old('event.end_at_register')), 'Y-m-d H:i') }}" @endif>
                    </div>
                    <div class="col-md-12 col-md-offset-4">
                        <div class="val-message-input">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- ./When block -->
        <div class="box box-solid">
            <div class="box-body">
                <h4>
                    {{ trans('welfare::view.Places purpose') }}
                </h4>
                <div class="form-group">
                    <label class="col-md-3 control-label align-right">{{ trans('welfare::view.Address') }}</label>
                    <div class="input-box col-md-9">
                        <input type="text" name="event[address]" class="form-control"
                               placeholder="{{ trans('welfare::view.Address') }}"
                               @if(isset($item)) value="{{$item['address']}}"
                               @elseif (isset($old['event']['address'])) value="{{old('event.address')}}" @endif>
                    </div>
                </div>
                <div class="form-group select-purpose-welfare">
                    <label class="col-md-3 control-label align-right">{{ trans('welfare::view.Purpose') }}</label>
                    <div class="input-box col-md-8">
                        <select name="event[wel_purpose_id]" class="form-control val-custom"
                                id="wel_purpose_id" data-placeholder="{{ trans('welfare::view.Select a welfare purose') }}">
                            <option></option>
                            @if(isset($purposes))
                            @foreach( $purposes as $value )
                            <option value="{{$value->id}}"
                                    @if((isset($item)&&$item['wel_purpose_id']==$value->id)||(old('event.wel_purpose_id')==$value->id)) selected="true" @endif>{{ $value->name }}</option>
                            @endforeach
                            @endif
                        </select>
                        <div class="val-message"></div>
                    </div>
                    <div class="col-md-1">
                            <button id="add-new-wel_purposes" type="button" class="btn btn-default add-college"
                                    data-toggle="modal"
                                    data-placement="bottom" data-target="#modal_wel_purposes" title="thêm mới"
                                    data-modal="true">...</button>
                    </div>
                </div>
                <div class="form-group form-group-select2 select-purpose-welfare">
                    <label class="col-md-3 control-label align-right">{{ trans('welfare::view.Form_imp') }}<em>*</em></label>
                    <div class="input-box col-md-8">
                        <select name="event[wel_form_imp_id]"
                                class="form-control select-wel-form-imp-id">
                            <option value=""></option>
                            @if(isset($formimp))
                                @foreach( $formimp as $key => $value )
                                <option value="{{ $formimp[$key]['value'] }}"
                                        @if((isset($item) && $item['wel_form_imp_id'] == $formimp[$key]['value'])
                                        ||(old('event.wel_form_imp_id')== $formimp[$key]['value'])) selected="true" @endif
                                        > {{ $formimp[$key]['lable'] }}
                                </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button id="add-new-wel_form_implements" type="button" class="btn btn-default add-college"
                            data-toggle="modal"
                            data-placement="bottom" data-target="#modal_form_implements" title="thêm mới"
                            data-modal="true">...</button>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-3 control-label align-right">{{ trans('welfare::view.Join_number_plan') }}</label>
                    <div class="input-box col-md-9">
                        <input type="number" min="0" name="event[join_number_plan]" class="form-control align-right" readonly="true"
                               placeholder="{{ trans('welfare::view.Join_number_plan') }}"
                               @if(isset($item)) value="{{ $item['join_number_plan'] }}"
                               @elseif (isset($old['event']['join_number_plan']))  value="{{old('event.join_number_plan')}}" @endif>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<div class="row hidden">
    <div class="form-horizontal form-label-left col-md-6">
        <div class="form-group row">
            <label class="col-md-4 control-label required"
                   aria-required="true">{{ trans('welfare::view.Status') }}<em>*</em></label>
            <div class="input-box col-md-8">

                <select name="event[status]" class="form-control">
                    @if(isset($status))
                    @foreach( $status as $key=>$value )
                    <option value="{{$key}}"
                            @if((isset($item)&&$item['status']==$key)||old('event.status')==$key) selected="true" @endif>{{$value}}</option>
                    @endforeach
                    @endif
                </select>
            </div>
        </div>
    </div>
</div>
<div class="box box-solid">
    <div class="box-body">
        <h4>
            {{ trans('welfare::view.Fee welfare') }}
        </h4>
        <div class="col-md-12">
            <div class="form-horizontal form-label-left">
                <div class="form-group">
                    <div class="col-md-6">
                        <label class="control-label col-lg-6 required" for="empl_offical_after_date"
                               aria-required="true">{{ trans('welfare::view.Official employee to date') }}</label>
                        <div class="input-group col-lg-5">
                        <input id="empl_offical_after_date" name="wel_fee[empl_offical_after_date]" type="text" class="form-control"
                               @if(isset($welFee)) value="{{ $welFee['empl_offical_after_date'] }}"
                               @elseif (isset($old['wel_fee']['empl_offical_after_date'])) value="{{old('wel_fee.empl_offical_after_date')}}" @endif />
                        </div>
                        <div class="col-lg-1"></div>
                    </div>
                    <div class="col-md-6 check-box-allow-attachments">
                        <label class="control-label required" for="is_allow_attachments"
                               aria-required="true">{{ trans('welfare::view.Allow attachments') }}</label>
                        <input id="is_allow_attachments" type="checkbox" name="event[is_allow_attachments]" value="1" class="format-checkox"
                               <?php if((isset($item) && $item->is_allow_attachments != Event::IS_ATTACHED)
                                   || (isset($old['event']['is_allow_attachments']) &&old('event.is_allow_attachments') != Event::IS_ATTACHED)) {
                               ?>
                               <?php } else { ?>
                                    checked="true"
                               <?php } ?>
                            />
                    </div>
                </div>
            </div>
        </div>
        <div id="fee-free-attach-infor" class="@if(isset($item) && $item->is_allow_attachments != Event::IS_ATTACHED)hidden @endif">
            <div class="col-md-12">
            <div class="form-horizontal form-label-left">
                <div class="form-group">
                    <div class="col-md-6">
                        <label class="control-label col-lg-6" for=""
                               >{{ trans('welfare::view.Free attachment attachments') }}</label>
                        <div class="input-group col-lg-5">
                        <select class="js-example-basic-multiple" multiple="multiple" style="width: 100%" class="form-control" name="wel_fee_att[fee_free_relative][]">
                            @foreach($relations as $keyRelative => $valueRelative)
                            <option value="{{$keyRelative}}" @if(in_array($keyRelative,$arrayRelativeFree)) selected="" @endif>{{$valueRelative}}</option>
                            @endforeach
                        </select>
                        </div>
                        <div class="col-lg-1"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="control-label col-lg-6" for=""
                               >{{ trans('welfare::view.The number of free attachments') }}</label>
                        <div class="input-group col-lg-5">
                            <input id="empl_offical_after_date" name="wel_fee_att[fee_free_count]" type="number" min="0" class="form-control" @if(isset($FeeAttachRelative) && $FeeAttachRelative) value="{{$FeeAttachRelative->fee_free_count}}" @endif/>
                        </div>
                        <div class="col-lg-1"></div>
                    </div>
                </div>
            </div>
        </div>
            <div class="col-md-12">
            <div class="form-horizontal form-label-left">
                <div class="form-group">
                    <div class="col-md-6">
                        <label class="control-label col-lg-6" for=""
                               >{{ trans('welfare::view.Payee attachment relationship 50%') }}</label>
                        <div class="input-group col-lg-5">
                            <select class="js-example-basic-multiple" multiple="multiple" style="width: 100%" class="form-control" name="wel_fee_att[fee50_relative][]">
                                @foreach($relations as $keyRelative => $valueRelative)
                                <option value="{{$keyRelative}}" @if(in_array($keyRelative,$arrayRelative50)) selected="" @endif>{{$valueRelative}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-1"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="control-label col-lg-6" for=""
                               >{{ trans('welfare::view.Number of attachments pay 50%') }}</label>
                        <div class="input-group col-lg-5">
                        <input name="wel_fee_att[fee50_count]" type="number" min="0" class="form-control" @if(isset($FeeAttachRelative) && $FeeAttachRelative) value="{{$FeeAttachRelative->fee50_count}}" @endif/>
                        </div>
                        <div class="col-lg-1"></div>
                    </div>
                </div>
            </div>
        </div>
            <div class="col-md-12">
            <div class="form-horizontal form-label-left">
                <div class="form-group">
                    <div class="col-md-6">
                        <label class="control-label col-lg-6" for=""
                               >{{ trans('welfare::view.Payee attachment relationship 100%') }}</label>
                        <div class="input-group col-lg-5">
                            <select class="js-example-basic-multiple" multiple="multiple" style="width: 100%" class="form-control" name="wel_fee_att[fee100_relative][]">
                                @foreach($relations as $keyRelative => $valueRelative)
                                <option value="{{$keyRelative}}" @if(in_array($keyRelative,$arrayRelative100)) selected="" @endif >{{$valueRelative}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-1"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="control-label col-lg-6" for=""
                               >{{ trans('welfare::view.Number of attachments pay 100%') }}</label>
                        <div class="input-group col-lg-5">
                        <input name="wel_fee_att[fee100_count]" type="number" min="0" class="form-control" @if(isset($FeeAttachRelative) && $FeeAttachRelative) value="{{$FeeAttachRelative->fee100_count}}" @endif/>
                        </div>
                        <div class="col-lg-1"></div>
                    </div>
                </div>
            </div>
        </div>
        </div>
        <div class="col-md-12">
            <div class="box-solid">
                <div class="box-header">
                    <h3 class="box-title with-border">{{ trans('welfare::view.Expected fee') }}</h3>
                </div>
                <div class="box-body">
                    <div class="form-horizontal">
                        <table class="table table-bordered" id="expected_fee">
                            <thead class="text-center">
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>{{ trans('welfare::view.Join_number_plan')}}</td>
                                    <td>{{ trans('welfare::view.Amount paid by staff')}}</td>
                                    <td>{{ trans('welfare::view.The amount the company assists')}}</td>
                                    <td>{{ trans('welfare::view.Total amount')}}</td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ trans('welfare::view.Official employee') }}</td>
                                    <td>
                                        <input group="9" maxlength="{{ $maxnumber }}" type="text" name="wel_fee[empl_offical_number]" id="empl_offical_number"
                                               class="form-control align-right number-expected"
                                               @if(isset($welFee)) value="{{ $welFee['empl_offical_number'] }}"
                                               @elseif (isset($old['wel_fee']['empl_offical_number'])) value="{{old('wel_fee.empl_offical_number')}}" @endif />
                                    </td>
                                    <td>
                                        <input  maxlength="{{$maxNumberFee }}" group="9" type="text" name="wel_fee[empl_offical_fee]" id="empl_offical_fee"
                                               class="form-control align-right personal_fee" placeholder="0.00"
                                               @if(isset($welFee)) value="{{ number_format($welFee['empl_offical_fee']) }}"
                                               @elseif (isset($old['wel_fee']['empl_offical_fee'])) value="{{ old('wel_fee.empl_offical_fee')}}" @endif />
                                    </td>
                                    <td>
                                        <input maxlength="{{$maxNumberFee }}" group="9" type="text" name="wel_fee[empl_offical_company_fee]" id="empl_offical_company_fee"
                                               class="form-control align-right company_fee" placeholder="0.00"
                                               @if(isset($welFee)) value="{{ number_format($welFee['empl_offical_company_fee']) }}"
                                               @elseif (isset($old['wel_fee']['empl_offical_company_fee'])) value="{{old('wel_fee.empl_offical_company_fee')}}" @endif />
                                    </td>
                                    <td>
                                        <input maxlength="17" group="9" type="text" name="empl_offical_fee_total" id="empl_offical_fee_total" placeholder="0.00"
                                               class="form-control align-right total-expected disabledbutton" readonly="true"
                                               @if(isset($welFee)) value="{{ number_format($welFee['empl_offical_number'] * ($welFee['empl_offical_fee'] + $welFee['empl_offical_company_fee'])) }}"
                                               @elseif (isset($old['empl_offical_fee_total'])) value="{{old('empl_offical_fee_total')}}" @endif />
                                    </td>
                                </tr>
                                <tr class="trial-employee">
                                    <td>{{ trans('welfare::view.Trial employee') }}</td>
                                    <td>
                                        <input group="8" maxlength="{{ $maxnumber }}" type="text" name="wel_fee[empl_trial_number]" id="empl_trial_number"
                                               class="form-control align-right number-expected"
                                               @if(isset($welFee)) value="{{ $welFee['empl_trial_number'] }}"
                                               @elseif (isset($old['wel_fee']['empl_trial_number'])) value="{{old('wel_fee.empl_trial_number')}}" @endif />
                                    </td>
                                    <td>
                                        <input maxlength="{{$maxNumberFee }}" group="8" type="text" name="wel_fee[empl_trial_fee]" id="empl_trial_fee"
                                               class="form-control align-right personal_fee" placeholder="0.00"
                                               @if(isset($welFee)) value="{{ number_format($welFee['empl_trial_fee']) }}"
                                               @elseif (isset($old['wel_fee']['empl_trial_fee'])) value="{{old('wel_fee.empl_trial_fee')}}" @endif />
                                    </td>
                                    <td>
                                        <input maxlength="{{$maxNumberFee }}" group="8" type="text" name="wel_fee[empl_trial_company_fee]" id="empl_trial_company_fee"
                                               class="form-control align-right company_fee" placeholder="0.00"
                                               @if(isset($welFee)) value="{{ number_format($welFee['empl_trial_company_fee']) }}"
                                               @elseif (isset($old['wel_fee']['empl_trial_company_fee'])) value="{{old('wel_fee.empl_trial_company_fee')}}" @endif />
                                    </td>
                                    <td>

                                        <input maxlength="17" group="8" type="text" name="empl_trial_fee_total" id="empl_trial_fee_total" placeholder="0.00"
                                               class="form-control align-right total-expected disabledbutton" readonly="true"
                                               @if(isset($welFee)) value="{{ number_format($welFee['empl_trial_number'] * ($welFee['empl_trial_fee'] + $welFee['empl_trial_company_fee'])) }}"
                                               @elseif (isset($old['empl_trial_fee_total'])) value="{{old('empl_trial_fee_total')}}" @endif />
                                    </td>
                                </tr>
                                <tr>
                                    <td>{{ trans('welfare::view.Interns') }}</td>
                                    <td>
                                        <input group="7" maxlength="{{ $maxnumber }}" type="text" name="wel_fee[intership_number]" id="intership_number"
                                               class="form-control align-right number-expected"
                                               @if(isset($welFee)) value="{{ $welFee['intership_number'] }}"
                                               @elseif (isset($old['wel_fee']['intership_number'])) value="{{old('wel_fee.intership_number')}}" @endif />
                                    </td>
                                    <td>
                                        <input maxlength="{{$maxNumberFee }}" group="7" type="text" name="wel_fee[intership_fee]" id="intership_fee"
                                               class="form-control align-right personal_fee" placeholder="0.00"
                                               @if(isset($welFee)) value="{{ number_format($welFee['intership_fee']) }}"
                                               @elseif (isset($old['wel_fee']['intership_fee'])) value="{{old('wel_fee.intership_fee')}}" @endif />
                                    </td>
                                    <td>
                                        <input maxlength="{{$maxNumberFee }}" group="7" type="text" name="wel_fee[intership_company_fee]" id="intership_company_fee"
                                               class="form-control align-right company_fee" placeholder="0.00"
                                               @if(isset($welFee)) value="{{ number_format($welFee['intership_company_fee']) }}"
                                               @elseif (isset($old['wel_fee']['intership_company_fee'])) value="{{old('wel_fee.intership_company_fee')}}" @endif />
                                    </td>
                                    <td>
                                        <input maxlength="17" group="7" type="text" name="intership_fee_total" id="intership_fee_total"
                                               class="form-control align-right total-expected disabledbutton" readonly="true" placeholder="0.00"
                                               @if(isset($welFee)) value="{{ number_format($welFee['intership_number'] * ($welFee['intership_fee'] + $welFee['intership_company_fee'])) }}"
                                               @elseif (isset($old['intership_fee_total'])) value="{{old('intership_fee_total')}}" @endif />
                                    </td>
                                </tr>
                                <tr class="attached attached-first attached-first-fee-plan
                                    <?php if((isset($item) && $item->is_allow_attachments != Event::IS_ATTACHED)
                                   || (isset($old['event']['is_allow_attachments']) &&old('event.is_allow_attachments') != Event::IS_ATTACHED)) :
                                    ?> hidden <?php endif; ?> ">
                                    <td>{{ trans('welfare::view.Welfare Employee Attach') }}</td>
                                    <td>
                                        <input group="6" maxlength="{{ $maxnumber }}" type="text" name="wel_fee[attachments_first_number]" id="attachments_first_number"
                                               class="form-control align-right number-expected"
                                               @if(isset($welFee)) value="{{ $welFee['attachments_first_number'] }}"
                                               @elseif (isset($old['wel_fee']['attachments_first_number'])) value="{{old('wel_fee.attachments_first_number')}}" @endif />
                                    </td>
                                    <td>
                                        <input maxlength="{{$maxNumberFee }}" group="6" type="text"  name="wel_fee[attachments_first_fee]" id="attachments_first_fee"
                                               class="form-control align-right personal_fee" placeholder="0.00"
                                               @if(isset($welFee)) value="{{ number_format($welFee['attachments_first_fee']) }}"
                                               @elseif (isset($old['wel_fee']['attachments_first_fee'])) value="{{old('wel_fee.attachments_first_fee')}}" @endif />
                                    </td>
                                    <td>
                                        <input maxlength="{{$maxNumberFee }}" group="6" type="text" name="wel_fee[attachments_first_company_fee]" id="attachments_first_company_fee"
                                               class="form-control align-right company_fee" placeholder="0.00"
                                               @if(isset($welFee)) value="{{ number_format($welFee['attachments_first_company_fee']) }}"
                                               @elseif (isset($old['wel_fee']['attachments_first_company_fee'])) value="{{old('wel_fee.attachments_first_company_fee')}}" @endif />
                                    </td>
                                    <td>
                                        <input maxlength="17" group="6" type="text" name="attachments_first_fee_total" id="attachments_first_fee_total"
                                               class="form-control align-right total-expected disabledbutton" readonly="true" placeholder="0.00"
                                               @if(isset($welFee)) value="{{ number_format($welFee['attachments_first_number'] * ($welFee['attachments_first_fee'] + $welFee['attachments_first_company_fee'])) }}"
                                               @elseif (isset($old['attachments_first_fee_total'])) value="{{old('attachments_first_fee_total')}}" @endif />
                                    </td>
                                </tr>
                                <tr>
                                    <td>{{ trans('welfare::view.Cost estimates') }}</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td>
                                        <input maxlength="17" type="text" class="form-control align-right fee-more"
                                               name="wel_fee[fee_estimates]" id="fee_estimates" placeholder="0.00"
                                               @if(isset($welFee)) value="{{ number_format($welFee['fee_estimates']) }}"
                                               @elseif (isset($old['wel_fee']['fee_estimates'])) value="{{old('wel_fee.fee_estimates')}}" @endif />
                                    </td>
                                </tr>
                                <tr>
                                    <td>{{ trans('welfare::view.Total') }}</td>
                                    <td>
                                        <input group="21" type="text" readonly="" name="number_expected" id="number_expected"
                                               class="form-control align-right  number-expected-total disabledbutton"
                                               @if(isset($welFee)) value="{{ $item['join_number_plan'] }}"
                                               @elseif (isset($old['number_expected'])) value="{{old('number_expected')}}" @endif />
                                    </td>
                                    <td>
                                        <input maxlength="17" type="text" name="total_personal_fee" id="total_personal_fee"
                                               class="form-control align-right total_personal_fee disabledbutton" readonly="true" placeholder="0.00"
                                               @if(isset($welFee)) value="{{ number_format($totalPersonalFee) }}"
                                               @elseif (isset($old['total_personal_fee'])) value="{{old('total_personal_fee')}}" @endif />
                                    </td>
                                    <td>
                                        <input maxlength="17"  type="text" name="total_company_fee" id="total_company_fee"
                                               class="form-control align-right total_company_fee disabledbutton" readonly="true" placeholder="0.00"
                                               @if(isset($welFee)) value="{{ number_format($totalCompanyFee) }}"
                                               @elseif (isset($old['total_company_fee'])) value="{{old('total_company_fee')}}" @endif />
                                    </td>
                                    <td>
                                        <input maxlength="17" group="21" type="text" name="wel_fee[fee_total]" id="fee_total"
                                               class="form-control align-right total-all-expected disabledbutton" readonly="true" placeholder="0.00"
                                               @if(isset($welFee)) value="{{ number_format($welFee['fee_total']) }}"
                                               @elseif (isset($old['wel_fee']['fee_total'])) value="{{old('wel_fee.fee_total')}}" @endif />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @if(isset($item))
        <div class="col-md-12">
        <div class="box-solid">
            <div class="box-header">
                <h3 class="box-title with-border">{{ trans('welfare::view.Actual fee') }}</h3>
            </div>
            <div class="box-body">
                <div class="form-horizontal">
                    <table class="table table-bordered" id="actual_fee">
                        <thead class="text-center">
                            <tr>
                                <td>&nbsp;</td>
                                <td>{{ trans('welfare::view.Number')}}</td>
                                <td>{{ trans('welfare::view.Amount paid by staff')}}</td>
                                <td>{{ trans('welfare::view.The amount the company assists')}}</td>
                                <td>{{ trans('welfare::view.Total amount')}}</td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ trans('welfare::view.Official employee') }}</td>
                                <td>
                                    <input  type="text" maxlength="{{ $maxnumber }}" group="11" name="wel_fee[empl_offical_number_actual]" id="empl_offical_number_actual"
                                           class="form-control align-right number-actual"
                                           @if(isset($welFee)) value="{{ $welFee['empl_offical_number_actual'] }}"
                                           @elseif (isset($old['wel_fee']['empl_offical_number_actual'])) value="{{old('wel_fee.empl_offical_number_actual')}}" @endif />
                                </td>
                                <td>
                                     <input maxlength="{{$maxNumberFee }}" group="11" type="text" name="wel_fee[empl_offical_fee_actual]" id="empl_offical_fee_actual"
                                           class="form-control align-right personal-fee-actual" placeholder="0.00"
                                           @if(isset($welFee)) value="{{ number_format($welFee['empl_offical_fee_actual']) }}"
                                           @elseif (isset($old['wel_fee']['empl_offical_fee_actual'])) value="{{old('wel_fee.empl_offical_fee_actual')}}" @endif />
                                </td>
                                <td>
                                    <input maxlength="{{$maxNumberFee }}" group="11" type="text" name="wel_fee[empl_offical_company_fee_actual]" id="empl_offical_company_fee_actual"
                                           class="form-control align-right company-fee-actual" placeholder="0.00"
                                           @if(isset($welFee)) value="{{ number_format($welFee['empl_offical_company_fee_actual']) }}"
                                           @elseif (isset($old['wel_fee']['empl_offical_company_fee_actual'])) value="{{old('wel_fee.empl_offical_company_fee_actual')}}" @endif />
                                </td>
                                <td>
                                    <input maxlength="17" group="11" type="text" name="empl_offical_fee_total_actual" id="empl_offical_fee_total_actual"
                                           class="form-control align-right total-actual" placeholder="0.00" readonly="true"
                                           @if(isset($welFee)) value="{{ number_format($welFee['empl_offical_number_actual'] * ($welFee['empl_offical_fee_actual'] + $welFee['empl_offical_company_fee_actual'])) }}"
                                           @elseif (isset($old['empl_offical_fee_total_actual'])) value="{{old('empl_offical_fee_total_actual')}}" @endif />
                                </td>
                            </tr>
                            <tr class="trial-employee">
                                <td>{{ trans('welfare::view.Trial employee') }}</td>
                                <td>
                                    <input  type="text" group="12" maxlength="{{ $maxnumber }}" name="wel_fee[empl_trial_number_actual]" id="empl_trial_number_actual"
                                           class="form-control align-right number-actual"
                                           @if(isset($welFee)) value="{{ $welFee['empl_trial_number_actual'] }}"
                                           @elseif (isset($old['wel_fee']['empl_trial_number_actual'])) value="{{old('wel_fee.empl_trial_number_actual')}}" @endif />
                                </td>
                                <td>
                                    <input maxlength="{{$maxNumberFee }}" group="12" type="text" name="wel_fee[empl_trial_fee_actual]" id="empl_trial_fee_actual"
                                           class="form-control align-right personal-fee-actual" placeholder="0.00"
                                           @if(isset($welFee)) value="{{ number_format($welFee['empl_trial_fee_actual']) }}"
                                           @elseif (isset($old['wel_fee']['empl_trial_fee_actual'])) value="{{old('wel_fee.empl_trial_fee_actual')}}" @endif />
                                </td>
                                <td>
                                    <input maxlength="{{$maxNumberFee }}" group="12" type="text" name="wel_fee[empl_trial_company_fee_actual]" id="empl_trial_company_fee_actual"
                                           class="form-control align-right company-fee-actual" placeholder="0.00"
                                           @if(isset($welFee)) value="{{ number_format($welFee['empl_trial_company_fee_actual']) }}"
                                           @elseif (isset($old['wel_fee']['empl_trial_company_fee_actual'])) value="{{old('wel_fee.empl_trial_company_fee_actual')}}" @endif />
                                </td>
                                <td>
                                    <input maxlength="17" group="12" type="text" name="empl_trial_fee_total_actual" id="empl_trial_fee_total_actual"
                                           class="form-control align-right total-actual" placeholder="0.00" readonly="true"
                                           @if(isset($welFee)) value="{{ number_format($welFee['empl_trial_number_actual'] * ($welFee['empl_trial_fee_actual'] + $welFee['empl_trial_company_fee_actual'])) }}"
                                           @elseif (isset($old['empl_trial_fee_total_actual'])) value="{{old('empl_trial_fee_total_actual')}}" @endif />
                                </td>
                            </tr>
                            <tr>
                                <td>{{ trans('welfare::view.Interns') }}</td>
                                <td>
                                    <input  type="text" group="13" maxlength="{{ $maxnumber }}" name="wel_fee[intership_number_actual]" id="intership_number_actual"
                                           class="form-control align-right number-actual"
                                           @if(isset($welFee)) value="{{ $welFee['intership_number_actual'] }}"
                                           @elseif (isset($old['wel_fee']['intership_number_actual'])) value="{{old('wel_fee.intership_number_actual')}}" @endif />
                                </td>
                                <td>
                                    <input maxlength="{{$maxNumberFee }}" group="13" type="text" name="wel_fee[intership_fee_actual]" id="intership_fee_actual"
                                           class="form-control align-right personal-fee-actual" placeholder="0.00"
                                           @if(isset($welFee)) value="{{ number_format($welFee['intership_fee_actual']) }}"
                                           @elseif (isset($old['wel_fee']['intership_fee_actual'])) value="{{old('wel_fee.intership_fee_actual')}}" @endif />
                                </td>
                                <td>
                                    <input maxlength="{{$maxNumberFee }}" group="13" type="text" name="wel_fee[intership_company_fee_actual]" id="intership_company_fee_actual"
                                           class="form-control align-right company-fee-actual" placeholder="0.00"
                                           @if(isset($welFee)) value="{{ number_format($welFee['intership_company_fee_actual']) }}"
                                           @elseif (isset($old['wel_fee']['intership_company_fee_actual'])) value="{{old('wel_fee.intership_company_fee_actual')}}" @endif />
                                <td>
                                    <input maxlength="17" group="13" type="text" name="intership_fee_total_actual" id="intership_fee_total_actual"
                                           class="form-control align-right total-actual" placeholder="0.00" readonly="true"
                                           @if(isset($welFee)) value="{{ number_format($welFee['intership_number_actual'] * ($welFee['intership_fee_actual'] + $welFee['intership_company_fee_actual'])) }}"
                                           @elseif (isset($old['intership_fee_total_actual'])) value="{{old('intership_fee_total_actual')}}" @endif />
                                </td>
                            </tr>
                            <tr class="attached attached-first attached-first-fee-actual
                                <?php if((isset($item) && $item->is_allow_attachments != Event::IS_ATTACHED)
                                   || (isset($old['event']['is_allow_attachments']) &&old('event.is_allow_attachments') != Event::IS_ATTACHED)) :
                                ?> hidden <?php endif; ?>">
                                <td>{{ trans('welfare::view.Welfare Employee Attach') }}</td>
                                <td>
                                    <input type="text" group="14" maxlength="{{ $maxnumber }}" name="wel_fee[attachments_first_number_actual]" id="attachments_first_number_actual"
                                           class="form-control align-right number-actual"
                                           @if(isset($welFee)) value="{{ $welFee['attachments_first_number_actual'] }}"
                                           @elseif (isset($old['wel_fee']['attachments_first_number_actual'])) value="{{old('wel_fee.attachments_first_number_actual')}}" @endif />
                                </td>
                                <td>
                                    <input maxlength="{{$maxNumberFee }}" group="14" type="text"  name="wel_fee[attachments_first_fee_actual]" id="attachments_first_fee_actual"
                                           class="form-control align-right personal-fee-actual" placeholder="0.00"
                                           @if(isset($welFee)) value="{{ number_format($welFee['attachments_first_fee_actual']) }}"
                                           @elseif (isset($old['wel_fee']['attachments_first_fee_actual'])) value="{{old('wel_fee.attachments_first_fee_actual')}}" @endif />
                                </td>
                                <td>
                                    <input maxlength="{{$maxNumberFee }}" group="14" type="text" name="wel_fee[attachments_first_company_fee_actual]" id="attachments_first_company_fee_actual"
                                           class="form-control align-right company-fee-actual" placeholder="0.00"
                                           @if(isset($welFee)) value="{{ number_format($welFee['attachments_first_company_fee_actual']) }}"
                                           @elseif (isset($old['wel_fee']['attachments_first_company_fee_actual'])) value="{{old('wel_fee.attachments_first_company_fee_actual')}}" @endif />
                                </td>

                                <td>
                                    <input maxlength="17" group="14" type="text" name="attachments_first_fee_total_actual" id="attachments_first_fee_total_actual"
                                           class="form-control align-right total-actual" placeholder="0.00" readonly="true"
                                           @if(isset($welFee)) value="{{ number_format($welFee['attachments_first_number_actual'] * ($welFee['attachments_first_fee_actual'] + $welFee['attachments_first_company_fee_actual'])) }}"
                                           @elseif (isset($old['attachments_first_fee_total_actual'])) value="{{old('attachments_first_fee_total_actual')}}" @endif />
                                </td>
                            </tr>
                            <tr class="total-fee-more-actual">
                                <td>{{ trans('welfare::view.Extra cost') }}</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>
                                    <input maxlength="17" type="text" class="form-control align-right fee-more" name="fee_extra"
                                           readonly="true" placeholder="0.00" id="fee_extra"
                                        @if(isset($welFee) && isset($totalWelFeeMore)) value="{{ number_format($totalWelFeeMore) }}"
                                        @endif>
                                </td>
                            </tr>
                            <tr class="all-total-fee-actual">
                                <td>{{ trans('welfare::view.Total') }}</td>
                                <td><input  type="text" name="event[join_number_exec]" id="number_expected"
                                           class="form-control align-right number-actual-total" readonly="true"
                                           @if(isset($item)) value="{{ $item['join_number_exec'] }}"
                                           @elseif (isset($old['event']['join_number_exec'])) value="{{old('event.join_number_exec')}}" @endif /></td>
                                <td>
                                    <input maxlength="17" type="text" name="total_personal_fee_actual" id="total_personal_fee_actual"
                                               class="form-control align-right total_personal_fee_actual"  placeholder="0.00" readonly="true"
                                               @if(isset($welFee)) value="{{ number_format($totalPersonalFeeActual) }}"
                                               @elseif (isset($old['total_personal_fee_actual'])) value="{{old('total_personal_fee_actual')}}" @endif />
                                </td>
                                <td>
                                    <input maxlength="17"  type="text" name="total_company_fee_actual" id="total_company_fee_actual"
                                               class="form-control align-right total_company_fee_actual"  placeholder="0.00" readonly="true"
                                               @if(isset($welFee)) value="{{ number_format($totalCompanyFeeActual) }}"
                                               @elseif (isset($old['total_company_fee_actual'])) value="{{old('total_company_fee_actual')}}" @endif />
                                </td>
                                <td><input maxlength="17" type="text" name="wel_fee[fee_total_actual]" id="fee_total_actual"
                                           class="form-control align-right total-all-actual" placeholder="0.00" readonly="true"
                                           @if(isset($welFee)) value="{{  number_format($welFee['fee_total_actual']) }}"
                                           @elseif (isset($old['wel_fee']['fee_total_actual'])) value="{{old('wel_fee.fee_total_actual')}}" @endif /></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        </div>
        @endif
    </div>
</div>
<hr>
