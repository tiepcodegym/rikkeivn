<?php
use Rikkei\Resource\View\View as ViewResource;
use Rikkei\Resource\View\getOptions;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Team\Model\Team;
use Rikkei\Resource\Model\CandidateMail;
use Rikkei\Team\View\Permission;
use Rikkei\Resource\View\CandidatePermission;
use Rikkei\Resource\View\View as Rview;
use Rikkei\Core\View\View as CoreView;
use Carbon\Carbon;

$lastSend = CandidateMail::getLastSend($candidate->recruiter, Candidate::MAIL_RECRUITER, $candidate->id);
$isSendMailThanks = CandidateMail::getLastSend($candidate->email, Candidate::MAIL_THANKS);
$disabled = $candidate->isWorking() ? 'disabled' : '';
$permissSendMail = Permission::getInstance()->isAllow('resource::candidate.sendMailOffer');
?>
<div class="tab-pane <?php if($tabActive == 'tab_interview'): ?> active <?php endif; ?>" id="tab_interview">
    @if (CandidatePermission::isShowTabInterview($candidate))
    <div class="row">
        <div class="col-sm-5">
            <form id="form-interview-candidate" class="form-horizontal" method="post" action="{{$urlSubmit}}" enctype="multipart/form-data">
                {!! csrf_field() !!}
                <input type="hidden" name="candidate_id" value="{{ $candidate->id }}">
                <input type="hidden" id="interviewer_ids" value="{{ $candidate->interviewer }}">
                @if ($candidate->request_id)
                <input type="hidden" name="request_id" value="{{ $candidate->request_id }}">
                @endif
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group position-relative">
                            <label for="interview_plan" class="col-lg-3 control-label">{{trans('resource::view.Interview plan 1st')}}</label>
                            <div class="col-lg-9">
                                <span>                                  
                                    <input type="text" id="interview_plan" name="interview_plan" class="form-control"
                                           value="{{$candidate->interview_plan ? $candidate->interview_plan: ''}}" autocomplete="off"/>
                                </span>  
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group position-relative">
                            <label for="interview2_plan" class="col-lg-3 control-label">{{trans('resource::view.Candidate.Detail.Interview plan 2nd')}}</label>
                            <div class="col-lg-9">
                                <span>                                  
                                    <input type="text" id="interview2_plan" name="interview2_plan" class="form-control"
                                           value="{{$candidate->interview2_plan ? $candidate->interview2_plan: ''}}" autocomplete="off"/>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group position-relative ">
                            <label for="interview_result" class="col-lg-3 control-label">{{trans('resource::view.Candidate.Detail.Interview result')}}</label>
                            <div class="col-lg-9">
                                <div class="input-group input-group-edit padding-left-0 padding-right-0" style="width: 100%;">
                                    <select id="interview_result" name="interview_result" class="form-control"
                                            {{ $candidate->isFail() ? 'disabled' : '' }}>
                                        <option value="0">{{ trans('resource::view.Interviewing') }}</option>
                                        @foreach ($resultOptions as $option)
                                        <option value="{{ $option['id'] }}" @if($checkEdit && $option['id'] == $candidate->interview_result) selected @endif>{{ $option['name'] }}</option>
                                        @endforeach
                                    </select>
                                    @if ($candidate->isFail())
                                    <span class="input-group-btn btn-interview">
                                        <button class="btn btn-primary btn-toggle-edit"
                                                data-title-edit="{{ trans('resource::view.Edit') }}"
                                                data-title-disable="{{ trans('resource::view.Disable') }}"
                                                data-toggle="tooltip"
                                                title="{{ trans('resource::view.change_status_note') }}">
                                            <span>{{ trans('resource::view.Edit') }}</span> <i class="fa fa-question"></i>
                                        </button>
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group position-relative ">
                            <label for="interviewer" class="col-lg-3 control-label">{{trans('resource::view.Candidate.Detail.Interviewer')}} <em class="required" aria-required="true">*</em></label>
                            <div class="col-lg-9">
                                <span>  
                                    <select name="interviewer[]" id="interviewer" data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}" multiple="multiple">
                                        @if ($interviewers)
                                            @foreach($interviewers as $interviewer)
                                            <option value="{{ $interviewer->id }}" selected="">{{ CoreView::getNickName($interviewer->email) }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </span>
                               
                                <input type="text" style="opacity: 0; position: absolute" value="{{isset($candidate->interviewer) ? 1 : ''}}" id="chk_interviewer" name="chk_interviewer" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" id="showStatus">
                    <div class="col-md-12" style="margin-top: 10px;">
                        <div class="form-group position-relative ">
                            <label for="working_type_interview" class="col-lg-3 control-label" id="working_type_interview_label">{{trans('resource::view.Candidate.Detail.Working.Working Type')}}
                                @if ($candidate->interview_result == getOptions::RESULT_PASS)
                                    <em class="required" aria-required="true">*</em>
                                @endif
                            </label>
                            <div class="col-lg-9">
                            <?php
                                $workingtypeArr = array_pluck($workingtypeOptions, 'name', 'id');
                                $workingtypeArr = [null => trans('resource::view.choose_type')] + $workingtypeArr;
                            ?>
                                {{ Form::select('working_type', $workingtypeArr, $contractType, ['id' => 'working_type_interview', 'class' => 'form-control', $disabled]) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12" style="margin-top: 10px;">
                        <div class="form-group position-relative ">
                            <label for="contract_length" class="col-lg-3 control-label">{{trans('resource::view.Candidate.Detail.Working.Contract length')}}</label>
                            <div class="col-lg-9">
                                <span>  
                                    <input type="text" id="contract_length_interview" name="contract_length" class="form-control" value="{{ !empty($contractLength) ? $contractLength : old('contract_length') }}">
                                </span>
                            </div>
                        </div>
                    </div>
                </div>  
                <div class="row">
                    <div class="col-md-12" id="showStatusWorkDate">
                        <div class="form-group position-relative row">
                            <label for="start_working_date" class="col-md-3 control-label">{{trans('resource::view.Start working date')}}</label>
                            <div class="col-md-9">
                                <span>                                  
                                    <input type="text" id="start_working_date_interview" name="start_working_date" class="form-control"
                                           value="{{$candidate->start_working_date ? $candidate->start_working_date: ''}}" autocomplete="off"/>
                                </span>
                            </div>
                        </div>
                    </div> 
                    <div class="col-md-12">
                        <div class="form-group position-relative ">
                            <label for="candidate_request" class="col-lg-3 control-label">{{trans('resource::view.Request')}} <em class="required" aria-required="true">*</em></label>
                            <div class="col-lg-9">
                                <select id="candidate_request" name="requests[]" class="form-control width-93 multiple_select" multiple="multiple">
                                    @php
                                    $arrId = [];
                                    @endphp
                                    @foreach($listRequest as $option)
                                    @if ((!Candidate::checkFull($option) && Carbon::parse($option->deadline)->format('y-m-d') >= date('y-m-d')) || (isset($allRequests) && in_array($option->id, $allRequests)))
                                    <option value="{{ $option->id }}" @if(isset($allRequests) && in_array($option->id, $allRequests)) selected @endif>{{ Team::getTeamNameById($option->team_id) }} - {{ Rview::subString($option->title, Candidate::SUB_TITLE_LEN) }}</option>
                                    @endif
                                    @endforeach
                                </select>
                                <input type="text" style="opacity: 0; position: absolute" value="{{!$allRequests ? '' : 1}}" id="chk_request" name="chk_request" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group position-relative ">
                            <label for="interview_note" class="col-lg-3 control-label">{{trans('resource::view.Candidate.Detail.Note')}}</label>
                            <div class="col-lg-9">
                                <span>  
                                    <textarea rows="5" name="interview_note" class="form-control interview_note" />{{$candidate->interview_note ? $candidate->interview_note: ''}}</textarea>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 <?php if((int)$candidate->interview_result !== \Rikkei\Resource\View\getOptions::RESULT_FAIL): ?>hidden<?php endif; ?> interested-input-container">
                        <div class="form-group position-relative">
                            <label class="col-lg-3 control-label">{{trans('resource::view.Candidate.Create.Interested')}}</label>
                            <div class="col-lg-9">
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
                <div class="row">
                    <div class="form-group position-relative ">
                        <div class="col-lg-3">

                        </div>
                        <div class="col-lg-4">
                            @if ($candidate->interview_result != getOptions::RESULT_FAIL
                                && $permissSendMail
                                && $candidate->recruiter == $curEmp->email
                            )
                            <button id="button-mail-interview" type="button" class="btn btn-info pull-left btn-mailing">
                                {{ trans('resource::view.Mailing invite to interview') }}
                            </button>
                            @endif
                            @if (Permission::getInstance()->isAllow('resource::candidate.sendMailThanks')
                                && $candidate->recruiter == $curEmp->email
                            )
                            <button type="button" class="btn btn-info pull-left btn-mailing"
                                    onclick="(typeof showMailContent == 'undefined') ? console.log('loading') : showMailContent({{ Candidate::MAIL_THANKS }}, 'mail_thanks');">
                                <span>
                                    {{trans('resource::view.Mailing thank interviewed')}}
                                </span>
                            </button>
                            @endif

                            @if ($candidate->interview_result == getOptions::RESULT_FAIL
                                && $permissSendMail
                                && $candidate->recruiter == $curEmp->email)
                            <button type="button" class="btn btn-info pull-left btn-mailing"
                                    onclick="(typeof showMailContent == 'undefined') ? console.log('loading') : showMailContent({{ Candidate::MAIL_INTERVIEW_FAIL }}, 'mail_fail_interview');"
                                    {{ $lastSendInterviewFail ? 'disabled' : '' }}>
                                {{ trans('resource::view.Mailing interview fail') }}
                                @if ($lastSendInterviewFail)
                                <i class="fa fa-check"></i>
                                @endif
                            </button>
                            @endif

                            @if (!empty($candidate->recruiter)
                                && $candidate->interview_result == getOptions::RESULT_PASS
                                && Permission::getInstance()->isAllow('resource::candidate.sendMailRecruiter')
                            )
                            <button type="button" class="btn btn-info btn-send-recruiter btn-mailing"
                                    onclick="(typeof showMailContent == 'undefined') ? console.log('loading') : showMailContent({{ Candidate::MAIL_RECRUITER }});"
                                    data-toggle="tooltip" data-placement="bottom"
                                    title="{{ trans('resource::view.Send recruitment request to the HR') }} ">
                                <span>
                                    {{trans('resource::view.Request to Recruit')}}
                                    <i class="fa fa-spin fa-refresh hidden"></i>
                                </span>
                            </button>
                            @endif
                        </div>
                        <div class="col-lg-5">
                            <div class="submit_interview">
                                <button type="button" onclick="(typeof submitInterviewClick == 'undefined') ? console.log('loading') : submitInterviewClick();" class="btn btn-primary pull-right" style="margin-bottom: 5px; height: 35px; margin-right: 15px; margin-top: 5px;">{{trans('resource::view.Candidate.Detail.Submit Interview')}}</button>
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="detail" value="detail" />
                <input type="hidden" name="tab_interview" value="1" />
                <input type="hidden" id="store_interviewers_to_send" name="store_interviewers_to_send" />
                <input type="hidden" id="store_send_content_to_interviewers" name="store_send_content_to_interviewers" />
            </form>
        </div>
        <div class="col-sm-7">
            @include ('resource::candidate.include.comment.comment')
        </div>
    </div>
    
    @else
    <div></div>
    @endif
</div>
@include('resource::candidate.include.modal.mail_interview')
@include('resource::candidate.include.modal.mail_interview_fail')
@include('resource::candidate.include.modal.mail_recruiter')
@include('resource::candidate.include.modal.mail_thanks')
@include('resource::candidate.include.modal.popup_calendar_confirm')
