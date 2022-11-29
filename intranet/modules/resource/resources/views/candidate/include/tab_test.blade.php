<?php
use Rikkei\Resource\View\getOptions;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Team\View\Permission;
use Rikkei\Test\Models\Type;
use Rikkei\Resource\View\CandidatePermission;
?>
<div class="tab-pane <?php if($tabActive == 'tab_test'): ?> active <?php endif; ?>" id="tab_test">
    @if (CandidatePermission::isShowTabTest($candidate))
    <form id="form-test-candidate" class="form-horizontal form-candidate-detail" method="post" action="{{$urlSubmit}}" enctype="multipart/form-data" autocomplete="off">
        {!! csrf_field() !!}
        <input type="hidden" name="candidate_id" value="{{$candidate->id}}">
        @if ($candidate->request_id)
        <input type="hidden" name="request_id" value="{{$candidate->request_id}}">
        @endif
        <div class="row">
            <div class="col-md-6">
                <div class="form-group position-relative">
                    <label class="col-md-4 control-label">{{ trans('resource::view.Candidate.Detail.Test option') }}</label>
                    <div class="col-md-8 control-label">
                        <div class="row">
                            <div class="col-md-12 select-group-full select-type-test-candidate">
                                <select name="test_option_type_ids[]" class="form-control" id="test_type_id" multiple aria-describedby="basic-addon3">
                                    @if (!$testTypes->isEmpty())
                                        <?php 
                                        $itemTestTypeIds = $candidate->test_option_type_ids; 
                                        ?>
                                        {!! Type::toNestedOptions($testTypes, $itemTestTypeIds) !!}
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="ricode {{$rikkeiCode ? '' : 'level-rikkei-code'}}" id='level-rikkei-code'>
                    <div class="form-group position-relative">
                        <label class="col-md-4 control-label"></label>
                        <div class="col-md-8">
                            <button type="button" class="btn btn-primary" id='btn-test-ricode'>Ricode configuration</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group position-relative ">
                    <label for="test_mark" class="col-md-4 control-label">
                        {{trans('resource::view.Candidate.Detail.Test mark')}}
                        @if ($candidate->test_result == getOptions::RESULT_PASS)
                            <em class="required" aria-required="true">*</em>
                        @endif
                    </label>
                    <div class="col-md-8">
                        <div class="row">
                            <?php
                            if ($resultTest->isEmpty()) {
                                $resultTest = collect([
                                    ['name' => 'GMAT', 'input_name' => 'test_mark'],
                                    ['name' => trans('resource::view.Expertise'), 'input_name' => 'test_mark_specialize']
                                ]);
                            }
                            ?>
                            @foreach ($resultTest as $result)
                            <div class="input-group col-md-8 margin-bottom-5">
                                <span class="input-group-addon" id="basic-addon3">{{ !is_array($result) ? $result->name : $result['name'] }}</span>
                                <input type="text" class="form-control" aria-describedby="basic-addon3"
                                        @if (!is_array($result))
                                            value="{{ $result->total_corrects . '/' .  $result->total_questions }}"
                                            readonly
                                        @else
                                            name="{{ $result['input_name'] }}" value="{{ $candidate->{$result['input_name']} }}"
                                            id="{{ $result['input_name'] }}"
                                        @endif
                                        >
                            </div>
                            @endforeach
                            
                            <div class="col-md-4 padding-left-0">
                                <button type="button" class="btn btn-info" id="btn-test-history">{{ trans('resource::view.Test history') }}</button>
                            </div>
                        </div>
                        
                        
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group position-relative ">
                    <label for="test_plan" class="col-md-4 control-label">
                        {{trans('resource::view.Candidate.Detail.Test plan')}}
                        @if ($candidate->test_result == getOptions::RESULT_PASS)
                            <em class="required" aria-required="true">*</em>
                        @endif
                    </label>
                    <div class="col-md-8">
                        <span>                                  
                            <input type="text" id="test_plan" name="test_plan" class="form-control" value="{{$candidate->test_plan ? $candidate->test_plan: ''}}" />
                        </span>
                        
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group position-relative ">
                    <label for="test_result" class="col-md-4 control-label">{{trans('resource::view.Candidate.Detail.Test result')}}</label>
                    <div class="col-md-8">
                        <span>  
                            <select id="test_result" name="test_result" class="form-control">
                                <option value="0">{{ trans('resource::view.Testing') }}</option>
                                @foreach ($resultOptions as $option)
                                <option value="{{ $option['id'] }}" @if($checkEdit && $option['id'] == $candidate->test_result) selected @endif>{{ $option['name'] }}</option>
                                @endforeach
                            </select>
                        </span>
                        
                    </div>
                </div>
            </div>
        </div> 
        <div class="row">
            <div class="col-md-12">
                <div class="form-group position-relative ">
                    <label for="test_note" class="col-md-2 control-label">{{trans('resource::view.Candidate.Detail.Note')}}</label>
                    <div class="col-md-10">
                        <span>                                  
                            <textarea rows="5" name="test_note" class="form-control" />{{$candidate->test_note ? $candidate->test_note: ''}}</textarea>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 <?php if((int)$candidate->test_result !== \Rikkei\Resource\View\getOptions::RESULT_FAIL): ?>hidden<?php endif; ?> interested-input-container">
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
        <div class="row margin-top-40">
            <div class="col-md-6">
                <div class="form-group position-relative ">
                    <div class="col-md-4">
                        
                    </div>
                    <div class="col-md-8">
                        @if ($candidate->test_result != getOptions::RESULT_FAIL
                            && Permission::getInstance()->isAllow('resource::candidate.sendMailOffer')
                            && $candidate->recruiter == $curEmp->email
                        )
                        <button type="button" class="btn btn-info pull-left" onclick="showMailContent({{Candidate::MAIL_TEST}});">{{trans('resource::view.Mailing invite to test')}}</button>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <button type="submit" class="btn btn-primary pull-right">{{trans('resource::view.Candidate.Detail.Submit Test')}}</button>
            </div>
        </div>
        <input type="hidden" name="detail" value="detail" />
    </form>
    @else
    <div></div>
    @endif
</div>
@include('resource::candidate.include.modal.mail_test')
@include('resource::candidate.include.modal.ricode_test')
@include('resource::candidate.include.modal.test_history')