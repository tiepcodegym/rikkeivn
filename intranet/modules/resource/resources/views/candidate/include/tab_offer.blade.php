<?php
use Rikkei\Resource\View\getOptions;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Team\View\Permission;
use Rikkei\Resource\View\CandidatePermission;
use Rikkei\Resource\View\View as Rview;
use Rikkei\Team\Model\Team;

//$disabled = $candidate->isWorkingOrEndOrLeave() && !in_array($candidate->working_type, array_keys(getOptions::listWorkingTypeExternal())) ? 'disabled' : '';
$disabled = '';
$isWorking = in_array($candidate->status, [getOptions::WORKING]);
$readonly = $candidate->isWorkingOrEndOrLeave() && !in_array($candidate->working_type, array_keys(getOptions::listWorkingTypeExternal())) ? 'disabled' : '';
$readonly = '';
?>

<div class="tab-pane <?php if($tabActive == 'tab_offer'): ?> active <?php endif; ?>" id="tab_offer">
    @if (CandidatePermission::isShowTabOffer($candidate))
    <form id="form-offering-candidate" class="form-horizontal form-candidate-detail" method="post" action="{{$urlSubmit}}" enctype="multipart/form-data">
        {!! csrf_field() !!}
        <input type="hidden" name="candidate_id" value="{{$candidate->id}}">
        @if (count($allRequests))
        <input type="hidden" name="check_request" value="1">
        @endif
        @if ($isWorking)
        <input type="hidden" name="start_working_date" value="{{$candidate->start_working_date}}">
        <input type="hidden" name="trial_work_start_date" value="{{$candidate->trial_work_start_date}}">
        <input type="hidden" name="trial_work_end_date" value="{{$candidate->trial_work_end_date}}">
        <input type="hidden" name="official_date" value="{{$candidate->official_date}}">
        @endif

        <div class="form-group row">
            <div class="col-md-10 col-md-offset-2 main-cl">
                <i>{!! trans('resource::view.tab_offer_note') !!}</i>
            </div>
        </div>

        <div class="form-group position-relative row">
            <label for="offer_result" class="col-md-2 control-label">{{trans('resource::view.Candidate.Detail.Offer result')}}</label>
            <div class="col-md-10">
                <span>  
                    <select id="offer_result" name="offer_result" class="form-control" {{ $employee && $candidate->offer_result == getOptions::RESULT_PASS ? 'disabled' : '' }}>
                        <option value="0">{{ trans('resource::view.Offering') }}</option>
                        @foreach ($resultOptions as $option)
                            <option value="{{ $option['id'] }}" @if($checkEdit && $option['id'] == $candidate->offer_result) selected @endif>{{ $option['name'] }}</option>
                        @endforeach
                    </select>
                    @if ($employee)
                    <input type="hidden" name="update_contract_working" value="1">
                    @endif
                </span>
            </div>
        </div>

        <div class="collapse margin-top-5" id="offer_pass_collapse">
            <div class="form-group row">
                <label class="col-md-2 control-label">{{ trans('resource::view.Contract') }}</label>
                <div class="col-md-10">
                    <div class="well well-sm margin-bottom-0">
                        <p class="break-init"><i>{{ trans('resource::view.Candidate.Detail.If offer result is Working, you must fill in the fields below') }}</i></p>    
                        <div class="row">
                            <div class="col-md-6">
                                <div class="margin-bottom-5">
                                    <label class="control-label">{{ trans('resource::view.Candidate.Detail.Working.Working Type') }}</label>
                                    <select id="working_type" name="working_type" class="form-control" {{ $disabled }}>
                                        <option value="0"></option>
                                        @foreach ($workingtypeOptions as $option)
                                            <option value="{{ $option['id'] }}" 
                                                    @if($checkEdit && !empty($candidate->working_type))
                                                        @if($option['id'] == $candidate->working_type) selected
                                                        @endif
                                                    @endif>
                                                    {{ $option['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="margin-bottom-5" id="contract_length_wrapper">
                                    <label class="control-label">{{ trans('resource::view.Candidate.Detail.Working.Contract length') }}</label>
                                    <input type="text" id="contract_length" name="contract_length" class="form-control" {{ $readonly }}
                                           value="{{ !empty($contractLength) ? $contractLength : old('contract_length') }}" autocomplete="off">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group position-relative  row">
                    <label class="col-md-4 control-label">{{ trans('resource::view.Candidate.Detail.Offer date') }} <em class="required">*</em></label>
                    <div class="col-md-8">
                        <span>                                  
                            <input type="text" id="offer_date" name="offer_date" class="form-control field-date-picker"
                                   value="{{$candidate->offer_date ? $candidate->offer_date: old('offer_date')}}" />
                        </span>
                        
                    </div>
                </div>
            </div>   
            <div class="col-md-6">
                <div class="form-group position-relative  row">
                    <label class="col-md-4 control-label">{{ trans('resource::view.Candidate.Detail.Feedback date') }} <em class="required">*</em></label>
                    <div class="col-md-8">
                        <span>                                  
                            <input type="text" id="offer_feedback_date" name="offer_feedback_date" class="form-control field-date-picker" value="{{$candidate->offer_feedback_date ? $candidate->offer_feedback_date: old('offer_feedback_date')}}" />
                        </span>
                        
                    </div>
                </div>
            </div>
            <div class="collapse col-md-6" id="start_working_date_collapse">
                <div class="form-group position-relative row">
                    <label class="col-md-4 control-label">{{trans('resource::view.Start working date')}} <em class="required" aria-required="true">*</em></label>
                    <div class="col-md-8">
                        <span>                                  
                            <input type="text" id="start_working_date" name="start_working_date" class="form-control field-date-picker" autocomplete="off"
                                   value="{{ $candidate->start_working_date ? $candidate->start_working_date: old('start_working_date')}}" />
                        </span>
                    </div>
                </div>
            </div>
            <!--<div class="col-md-6 group-contract hidden" data-hidden="{!! json_encode([getOptions::WORKING_BORROW]) !!}">
                <div class="form-group position-relative row">
                    <label class="col-md-4 control-label">{{trans('resource::view.End working date')}}</label>
                    <div class="col-md-8">
                        <span>                                  
                            <input type="text" id="end_working_date" name="end_working_date" class="form-control field-date-picker"
                                   value="{{ $candidate ? $candidate->end_working_date: '' }}" autocomplete="off"/>
                        </span>
                    </div>
                </div>
            </div>-->
            <div class="col-md-6 pos-container <?php if (!in_array($candidate->offer_result, [getOptions::RESULT_PASS, getOptions::RESULT_WORKING])) echo 'hidden'; ?>">
                <div class="form-group position-relative ">
                    <label id="candidate-program-lang" for="programming_language_id" class="col-md-4 control-label">{{ trans('resource::view.Candidate.Create.Programming languages') }} <em class="required hidden" id="programming_language_required" aria-required="true">*</em></label>
                    <div class="col-md-8">
                        <span>
                        <select id="programming_language_id" name="programming_language_id" class="form-control has-search select-search select2-hidden-accessible">
                            <option value="0"> {{ trans('resource::view.Choose language') }} </option>
                            @foreach($programs as $option)
                                <option value="{{ $option->id }}" {{ $option->id == $candidate->programming_language_id || $option->id == old('programming_language_id') ? 'selected' : '' }}>{{ $option->name }}</option>
                            @endforeach
                        </select>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-12 group-contract hidden" data-contract="{!! json_encode([
                 getOptions::WORKING_BORROW,
                 getOptions::WORKING_PROBATION,
                 getOptions::WORKING_PARTTIME
            ]) !!}">
                <div class="row collapse" id="trial_work_start_and_end_date_collapse">
                    <div class="col-md-6">
                        <div class="form-group position-relative row">
                            <label class="col-md-4 control-label">{{ trans('resource::view.Trial work start date') }} <em class="required hidden">*</em></label>
                            <div class="col-md-8">
                                <span>                                  
                                    <input type="text" id="trial_work_start_date" name="trial_work_start_date" class="form-control field-date-picker"
                                           value="{{$candidate->trial_work_start_date ? $candidate->trial_work_start_date: old('trial_work_start_date')}}" autocomplete="off" />
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">    
                        <div class="form-group position-relative row">
                            <label class="col-md-4 control-label">{{ trans('resource::view.Trial work end date') }} <em class="required hidden">*</em></label>
                            <div class="col-md-8">
                                <span>                                  
                                    <input type="text" id="trial_work_end_date" name="trial_work_end_date" class="form-control field-date-picker"
                                           value="{{$candidate->trial_work_end_date ? $candidate->trial_work_end_date: old('trial_work_end_date')}}" autocomplete="off"/>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 group-contract hidden" data-contract="{!! json_encode([getOptions::WORKING_INTERNSHIP]) !!}">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group position-relative row">
                            <label class="col-md-4 control-label">{{ trans('resource::view.Trainee start date') }} <em class="required" aria-required="true">*</em></label>
                            <div class="col-md-8">
                                <span>                                  
                                    <input type="text" id="trainee_start_date" name="trainee_start_date" class="form-control field-date-picker"
                                           value="{{ $candidate->trainee_start_date ? $candidate->trainee_start_date: old('trainee_start_date') }}" autocomplete="off" />
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">    
                        <div class="form-group position-relative row">
                            <label class="col-md-4 control-label">{{ trans('resource::view.Trainee end date') }} <em class="required" aria-required="true">*</em></label>
                            <div class="col-md-8">
                                <span>                                  
                                    <input type="text" id="trainee_end_date" name="trainee_end_date" class="form-control field-date-picker"
                                           value="{{ $candidate->trainee_end_date ? $candidate->trainee_end_date: old('trainee_end_date') }}" autocomplete="off" />
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 collapse" id="official_date_collapse">
                <div class="row">
                    <div class="col-md-6 group-contract hidden" data-contract="{!! json_encode([getOptions::WORKING_PROBATION]) !!}">
                        <div class="form-group position-relative row">
                            <label class="col-md-4 control-label">{{ trans('resource::view.Official date') }} <em class="required">*</em></label>
                            <div class="col-md-8">
                                <span>                                  
                                    <input type="text" id="official_date" name="official_date" class="form-control field-date-picker" data-format="YYYY-MM-DD"
                                           value="{{ $candidate->official_date ? $candidate->official_date: old('official_date') }}" autocomplete="off" />
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 request-container <?php if (!in_array($candidate->offer_result, [getOptions::RESULT_PASS, getOptions::RESULT_WORKING])) echo 'hidden'; ?>">
                <div class="form-group position-relative ">
                    <label for="request_id" class="col-md-4 control-label">{{trans('resource::view.Request')}} <em class="required" aria-required="true">*</em></label>
                    <div class="col-md-8">
                        <span>
                            <select id="request_id" name="request_id" class="form-control width-93 multiple_select" multiple="multiple">
                                @foreach($getRequests as $option)
                                    <option value="{{ $option->id }}" @if($option->id == $candidate->request_id) selected @endif>{{ Team::getTeamNameById($option->team_id) }} - {{ Rview::subString($option->title, Candidate::SUB_TITLE_LEN) }}</option>
                                @endforeach
                            </select>
                            <input type="text" style="opacity: 0; position: absolute" value="{{!$getRequests ? '' : 1}}" id="chk_request" name="chk_request" />
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-md-6 teams-container <?php if (!in_array($candidate->offer_result, [getOptions::RESULT_PASS, getOptions::RESULT_WORKING])) echo 'hidden'; ?>">
                <div class="form-group position-relative ">
                    <label for="team_id" class="col-md-4 control-label">{{trans('resource::view.Team')}} <em class="required" aria-required="true">*</em></label>
                    <div class="col-md-8">
                        <span>                                  
                            <select id="team_id" name="team_id" class="form-control select-search" >
                                <option value="0"> {{ trans('resource::view.Choose team') }} </option>
                                @foreach ($teamsOptionAll as $option)
                                    <option value="{{ $option['value'] }}" 
                                            {{ $option['value'] == $candidate->team_id || $option['value'] == old('team_id')? 'selected' : '' }}
                                    >
                                        {{ $option['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-6 pos-container <?php if (!in_array($candidate->offer_result, [getOptions::RESULT_PASS, getOptions::RESULT_WORKING])) echo 'hidden'; ?>">
                <div class="form-group position-relative ">
                    <label for="position_apply" class="col-md-4 control-label">{{trans('resource::view.Position')}} <em class="required" aria-required="true">*</em></label>
                    <div class="col-md-8">
                        <span>                                  
                            <select id="position_apply" name="position_apply" class="form-control select-search" >
                                <option value="0"> {{ trans('resource::view.Choose position') }} </option>
                                @foreach ($allPos as $id => $value)
                                    <option value="{{ $id }}" 
                                            {{ $id == $candidate->position_apply || $id == old('position_apply')? 'selected' : '' }}
                                    >
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group position-relative  row">
                    <label for="offer_note" class="col-md-4 control-label">{{trans('resource::view.Candidate.Detail.Note')}}</label>
                    <div class="col-md-8">
                        <span>  
                            <textarea rows="4" name="offer_note" class="form-control" />{{$candidate->offer_note ? $candidate->offer_note: old('offer_note')}}</textarea>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-12 <?php if((int)$candidate->offer_result !== \Rikkei\Resource\View\getOptions::RESULT_FAIL): ?>hidden<?php endif; ?> interested-input-container">
                <div class="form-group position-relative">
                    <label class="col-md-2 control-label">{{trans('resource::view.Candidate.Create.Interested')}}</label>
                    <div class="col-md-10">
                        <span>
                            <select name="interested" class="form-control">
                                @foreach ($interestedOptions as $key => $interested)
                                    <option value="{!! $key !!}"
                                            class="{!! $interested['class'] !!} font-15"
                                            @if ((int)$candidate->interested === $key) selected @endif>{!! $interested['label'] !!}</option>
                                @endforeach
                            </select>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @if (!$isWorkingOrLeaveOff)
        <div class="row  margin-top-40">
            <div class="col-md-6">
                <div class="form-group position-relative ">
                    <div class="col-md-4">

                    </div>
                    <div class="col-md-8">
                        @if ($candidate->offer_result != getOptions::RESULT_FAIL
                            && Permission::getInstance()->isAllow('resource::candidate.sendMailOffer')
                            && $candidate->recruiter == $curEmp->email
                        )
                        <button type="button" class="btn btn-info pull-left" onclick="showMailContent({{Candidate::MAIL_OFFER}}, 'mail_offer');">{{trans('resource::view.Mailing to offer')}}</button>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <button type="submit" id="btn_submit_offer" class="btn btn-primary pull-right">
                    {{trans('resource::view.Candidate.Detail.Submit Offer')}}
                </button>
            </div>
        </div>
        <input type="hidden" name="detail" value="detail" />
        @endif
    </form>
    @else
    <div></div>            
    @endif    
</div>

<!-- modal cofirm -->
<div class="modal fade modal-warning" id="_modal_confirm" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ Lang::get('core::view.Confirm') }}</h4>
            </div>
            <div class="modal-body">
                <p class="text-body">{{ Lang::get('core::view.Are you sure delete item(s)?') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-close pull-left btn-default" data-dismiss="modal">{{ Lang::get('core::view.Close') }}</button>
                <button type="button" class="btn btn-outline btn-ok btn-primary">{{ Lang::get('core::view.OK') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div> <!-- modal delete cofirm -->

@include('resource::candidate.include.modal.mail_offer')
@include('resource::candidate.include.modal.invite_letter_body')
