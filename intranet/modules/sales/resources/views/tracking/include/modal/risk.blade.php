<?php
use Rikkei\Team\Model\Employee;
use Rikkei\Core\View\View;
use Rikkei\Project\Model\Risk;

if (isset($riskInfo) && $riskInfo) {
    $checkEdit = true;
    $urlSubmit = route('sales::.tracking.save.risks', ['riskId' => $riskInfo->id]);
} else {
    $checkEdit = false;
    $urlSubmit = route('sales::tracking.save.risks');
}
?>
<div class="modal fade" id="riskModal" style="display: none;">
   <div class="modal-dialog modal-lg" style="width: 90%;">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span></button>
            <h4 class="modal-title">{{ trans('project::view.Risk info') }}</h4>
         </div>
         <div class="modal-body">
            <div class="modal-ncm-editor-main">
               <div class="row">
                    <form class="form-horizontal form-riks-detail" method="post" autocomplete="off" action="{{$urlSubmit}}" enctype="multipart/form-data">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        @if ($checkEdit)
                        <input type="hidden" id="id" name="id" value="{{ $riskInfo->id }}" />
                        @endif
                        @if (!empty($redirectUrl))
                        <input type="hidden" name="redirectUrl" value="{{ $redirectUrl }}">
                        @endif
                        <input type="hidden" id="project_id" name="project_id" value="" />
                        <div class="box box-info">
                            <div class="box-header with-border">
                                <h3 class="box-title">{{ trans('project::view.General information') }}</h3>
                            </div>
                            <div class="box-body">
                                <!-- ROW 1 -->
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="content" class="col-sm-3 control-label required">{{ trans('project::view.Content') }}<em>*</em></label>
                                        <div class="col-sm-9">
                                            <textarea class="form-control input-field" id="content" name="content" placeholder="{{ trans('project::view.Content') }}">@if ($checkEdit){!!$riskInfo->content!!}@endif</textarea>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="weakness" class="col-sm-3 control-label required">{{ trans('project::view.Weakness') }}<em>*</em></label>
                                        <div class="col-sm-9">
                                            <textarea class="form-control" id="weakness" name="weakness" placeholder="{{ trans('project::view.Weakness') }}">@if ($checkEdit){!!$riskInfo->weakness!!}@endif</textarea>
                                        </div>
                                    </div>
                                </div>
                                <!-- ROW 2 -->
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="level_important" class="col-sm-3 control-label required">{{ trans('project::view.Level important') }}<em>*</em></label>
                                        <div class="col-sm-9">
                                            <select class="form-control" id="level_important" name="level_important"  >
                                                <option value="">{{ trans('project::view.Level important please choose') }}</option>
                                                @foreach (Risk::getListLevelRisk() as $keyLevel => $valueLevel)
                                                    <option value="{{ $valueLevel }}"
                                                        @if ($checkEdit && $riskInfo->level_important == $valueLevel) selected @endif
                                                    >{{ $keyLevel }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label  class="col-sm-3 control-label required">{{ trans('project::view.Owner') }}<em>*</em></label>
                                        <div class="col-sm-9">
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <select class="form-control width-93 select2-hidden-accessible select-search" id="team_owner" name="team_owner" style="width:100%" 
                                                        data-remote-url="{{ URL::route('team::team.list.search.ajax') }}">
                                                        <option value="">{{ trans('project::view.Choose team') }}</option>
                                                        @if ($checkEdit && $riskInfo->team_owner) 
                                                        <option value="{{$riskInfo->team_owner}}" selected>{{$riskInfo->team_name}}</option>
                                                        @endif
                                                    </select>
                                                </div>
                                                <div class="col-sm-6" >
                                                    <select class="form-control width-93 select2-hidden-accessible select-search" id="owner" name="owner" style="width:100%" 
                                                        data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}">
                                                        <option value="">{{ trans('project::view.Choose employee') }}</option>
                                                        @if ($checkEdit && $riskInfo->owner) 
                                                        <option value="{{$riskInfo->owner}}" selected>{{View::getNickName($riskInfo->owner_mail)}}</option>
                                                        @endif
                                                    </select>
                                                </div>
                                                <div id="error-team-owner" style="margin-left:15px">&#160;</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Row 3 -->
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="status" class="col-sm-3 control-label required">{{ trans('project::view.Select project') }}<em>*</em></label>
                                        <div class="col-sm-9">
                                            @if($accessEditTask)
                                                <select name="project_id" class="form-control form" id="project">
                                                        <option value="">&nbsp</option>
                                                    @foreach ($project as $projs)
                                                        <option value="{{ $projs->id }}">{{ $projs->name }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <input class="form-control input-field" type="text" id="status" disabled value="" />
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box box-info">
                            <div class="box-header with-border">
                                <h3 class="box-title">{{ trans('project::view.Solution using') }}</h3>
                            </div>
                            <div class="box-body">
                                <!-- ROW 1 -->
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="solution_using" class="col-sm-3 control-label">{{ trans('project::view.Solution') }}</label>
                                        <div class="col-sm-9">
                                            <textarea class="form-control" id="solution_using" name="solution_using" placeholder="{{ trans('project::view.Solution using') }}">@if ($checkEdit){!!$riskInfo->solution_using!!}@endif</textarea>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="posibility_using" class="col-sm-3 control-label">{{ trans('project::view.Posibility') }}</label>
                                        <div class="col-sm-9">
                                            <select class="form-control" id="posibility_using" name="posibility_using"  >
                                                <option value="">{{ trans('project::view.Level important please choose') }}</option>
                                                @foreach (Risk::getListLevelRisk() as $keyLevel => $valueLevel)
                                                    <option value="{{ $valueLevel }}"
                                                        @if ($checkEdit && $riskInfo->posibility_using == $valueLevel) selected @endif
                                                    >{{ $keyLevel }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <!-- ROW 2 -->
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="value_using" class="col-sm-3 control-label">{{ trans('project::view.Value') }}</label>
                                        <div class="col-sm-9">
                                            <input type="number" min="0" class="form-control num" id="value_using" name="value_using" placeholder="{{ trans('project::view.Value') }}" 
                                               @if ($checkEdit) value="{{$riskInfo->value_using}}" @endif    
                                            />
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="impact_using" class="col-sm-3 control-label">{{ trans('project::view.Impact') }}</label>
                                        <div class="col-sm-9">
                                            <select class="form-control" id="impact_using" name="impact_using"  >
                                                <option value="">{{ trans('project::view.Level important please choose') }}</option>
                                                @foreach (Risk::getListLevelRisk() as $keyLevel => $valueLevel)
                                                    <option value="{{ $valueLevel }}"
                                                        @if ($checkEdit && $riskInfo->impact_using == $valueLevel) selected @endif
                                                    >{{ $keyLevel }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <!-- ROW 3 -->
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="handling_method_using" class="col-sm-3 control-label">{{ trans('project::view.Handling method') }}</label>
                                        <div class="col-sm-9">
                                            <select class="form-control" id="handling_method_using" name="handling_method_using"  >
                                                <option value="0">{{ trans('project::view.Please choose method') }}</option>
                                            @if ($methods && count($methods))
                                                @foreach ($methods as $methodKey => $methodValue)
                                                    <option value="{{ $methodKey }}"
                                                        @if ($checkEdit && $riskInfo->handling_method_using == $methodKey) selected @endif
                                                    >{{ $methodValue }}</option>
                                                @endforeach
                                            @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box box-info">
                            <div class="box-header with-border">
                                <h3 class="box-title">{{ trans('project::view.Solution suggest') }}</h3>
                            </div>
                            <div class="box-body">
                                <!-- ROW 1 -->
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="solution_suggest" class="col-sm-3 control-label">{{ trans('project::view.Solution') }}</label>
                                        <div class="col-sm-9">
                                            <textarea class="form-control" id="solution_suggest" name="solution_suggest" placeholder="{{ trans('project::view.Solution suggest') }}">@if ($checkEdit){!!$riskInfo->solution_suggest!!}@endif</textarea>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="possibility_suggest" class="col-sm-3 control-label">{{ trans('project::view.Posibility') }}</label>
                                        <div class="col-sm-9">
                                            <select class="form-control" id="possibility_suggest" name="possibility_suggest"  >
                                                <option value="">{{ trans('project::view.Level important please choose') }}</option>
                                                @foreach (Risk::getListLevelRisk() as $keyLevel => $valueLevel)
                                                    <option value="{{ $valueLevel }}"
                                                        @if ($checkEdit && $riskInfo->possibility_suggest == $valueLevel) selected @endif
                                                    >{{ $keyLevel }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <!-- ROW 2 -->
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="value_suggest" class="col-sm-3 control-label">{{ trans('project::view.Value') }}</label>
                                        <div class="col-sm-9">
                                            <input type="number" min="0" class="form-control num" id="value_suggest" name="value_suggest" placeholder="{{ trans('project::view.Value') }}" 
                                                 @if ($checkEdit) value="{{$riskInfo->value_suggest}}" @endif   
                                            />
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="impact_suggest" class="col-sm-3 control-label">{{ trans('project::view.Impact') }}</label>
                                        <div class="col-sm-9">
                                            <select class="form-control" id="impact_suggest" name="impact_suggest"  >
                                                <option value="">{{ trans('project::view.Level important please choose') }}</option>
                                                @foreach (Risk::getListLevelRisk() as $keyLevel => $valueLevel)
                                                    <option value="{{ $valueLevel }}"
                                                        @if ($checkEdit && $riskInfo->impact_suggest == $valueLevel) selected @endif
                                                    >{{ $keyLevel }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <!-- ROW 3 -->
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="handling_method_suggest" class="col-sm-3 control-label">{{ trans('project::view.Handling method') }}</label>
                                        <div class="col-sm-9">
                                            <select class="form-control" id="handling_method_suggest" name="handling_method_suggest"  >
                                                <option value="0">{{ trans('project::view.Please choose method') }}</option>
                                            @if ($methods && count($methods))
                                                @foreach ($methods as $methodKey => $methodValue)
                                                    <option value="{{ $methodKey }}"
                                                        @if ($checkEdit && $riskInfo->handling_method_suggest == $methodKey) selected @endif
                                                    >{{ $methodValue }}</option>
                                                @endforeach
                                            @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="risk_acceptance_criteria" class="col-sm-3 control-label">{{ trans('project::view.Risk acceptance criteria') }}</label>
                                        <div class="col-sm-9">
                                            <input type="number" min="0" class="form-control num" id="risk_acceptance_criteria" name="risk_acceptance_criteria" placeholder="{{ trans('project::view.Risk acceptance criteria') }}" 
                                                @if ($checkEdit) value="{{$riskInfo->risk_acceptance_criteria}}" @endif   
                                            />
                                        </div>
                                    </div>
                                </div>
                                <!-- ROW 4 -->
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="acceptance_reason" class="col-sm-3 control-label">{{ trans('project::view.Acceptance reason') }}</label>
                                        <div class="col-sm-9">
                                            <textarea class="form-control" id="acceptance_reason" name="acceptance_reason" placeholder="{{ trans('project::view.Acceptance reason') }}">@if ($checkEdit){!!$riskInfo->acceptance_reason!!}@endif</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box box-info">
                            <div class="box-header with-border">
                                <h3 class="box-title">{{ trans('project::view.Result') }}</h3>
                            </div>
                            <div class="box-body">
                                <!-- ROW 1 -->
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="finish_date" class="col-sm-3 control-label">{{ trans('project::view.Finish date') }}</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control date" id="finish_date" name="finish_date" placeholder="{{ trans('project::view.YYYY-MM-DD') }}" 
                                               @if ($checkEdit) value="{{$riskInfo->finish_date}}" @endif     
                                            />
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="performer" class="col-sm-3 control-label">{{ trans('project::view.Performer') }}</label>
                                        <div class="col-sm-9">
                                            <select id="performer" name="performer" style="width:100%" 
                                                class="form-control width-93 select2-hidden-accessible select-search"
                                                data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}">
                                                <option value="0"></option>
                                                @if ($checkEdit && $riskInfo->performer_email) 
                                                <option value="{{$riskInfo->performer}}" selected>{{View::getNickName($riskInfo->performer_email)}}</option>
                                                @endif
                                            </select>
                                            
                                        </div>
                                    </div>
                                </div>
                                <!-- ROW 2 -->
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="test_date" class="col-sm-3 control-label">{{ trans('project::view.Test date') }}</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control date" id="test_date" name="test_date" placeholder="{{ trans('project::view.YYYY-MM-DD') }}" 
                                                @if ($checkEdit) value="{{$riskInfo->test_date}}" @endif        
                                            />
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="tester" class="col-sm-3 control-label">{{ trans('project::view.Tester') }}</label>
                                        <div class="col-sm-9">
                                            <select id="tester" name="tester" style="width:100%" 
                                                class="form-control width-93 select2-hidden-accessible select-search"
                                                data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}">
                                                <option value="0"></option>
                                                @if ($checkEdit && $riskInfo->tester_email) 
                                                <option value="{{$riskInfo->tester}}" selected>{{View::getNickName($riskInfo->tester_email)}}</option>
                                                @endif
                                            </select>
                                            
                                        </div>
                                    </div>
                                </div>
                                <!-- ROW 3 -->
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="evidence" class="col-sm-3 control-label">{{ trans('project::view.Evidence') }}</label>
                                        <div class="col-sm-9">
                                            <textarea class="form-control" id="evidence" name="evidence" placeholder="{{ trans('project::view.Evidence') }}">@if ($checkEdit){!!$riskInfo->evidence!!}@endif</textarea>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="result" class="col-sm-3 control-label">{{ trans('project::view.Result') }}</label>
                                        <div class="col-sm-9">
                                            <select class="form-control" id="result" name="result"  >
                                                <option value="0">{{ trans('project::view.Not choose') }}</option>
                                                @foreach ($results as $resultKey => $resultValue)
                                                    <option value="{{ $resultKey }}"
                                                        @if ($checkEdit && $riskInfo->result == $resultKey) selected @endif  
                                                    >{{ $resultValue }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                 <div class="col-md-12 align-center">
                                         <button class="btn-add" type="submit">
                                             @if ($taskItem->id)
                                                 {{trans('project::view.Save')}}
                                             @else
                                                 {{trans('project::view.Create')}}
                                             @endif
                                            <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i>
                                        </button>
                                     </div>
                                 </div>
                            </div>    
                        </div>
                    </form>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
