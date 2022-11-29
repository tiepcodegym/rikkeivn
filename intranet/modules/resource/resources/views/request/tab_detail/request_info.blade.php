<?php
use Rikkei\Resource\View\getOptions;
use Rikkei\Resource\Model\ResourceRequest;
use Rikkei\Team\View\Permission;
?>
<div class="detail request-info">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group position-relative form-label-left">
                <label for="title" class="col-sm-3 control-label">{{trans('resource::view.Request.Create.Title')}}</label>
                <div class="col-md-9 word-break">
                    <span class="title">
                        {{ $request->title }}
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group position-relative form-label-left">
                <label for="customer" class="col-sm-3 control-label">{{trans('resource::view.Request.Create.Customer')}}</label>
                <div class="col-md-9">
                    <span>
                        {{$request->customer}}
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group position-relative form-label-left">
                <label for="name" class="col-sm-3 control-label">{{trans('resource::view.Request.Create.Team')}}</label>
                <div class="col-md-9">
                    <span class="team_name">
                        {{$request->team_name}}
                        <a title="{{trans('resource::view.Show team detail')}}" href="javascript:void(0)" onclick="showTeam();">
                            <i class="fa fa-info-circle"></i>
                        </a>
                    </span>

                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group position-relative form-label-left">
                <label for="name" class="col-sm-3 control-label">{{trans('resource::view.Request.Create.Languages')}}</label>
                <div class="col-md-9">
                    <span>
                        {{ implode(', ', $langs) }}
                    </span>

                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group position-relative form-label-left">
                <label for="name" class="col-sm-3 control-label">{{trans('resource::view.Request.Create.Start working')}}</label>
                <div class="col-md-9">
                    <span>
                        {{$request->start_working}}
                    </span>

                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group position-relative form-label-left">
                <label for="name" class="col-sm-3 control-label">{{trans('resource::view.Request.Create.End working')}}</label>
                <div class="col-md-9">
                    <span>
                        {{$request->end_working}}
                    </span>

                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group position-relative form-label-left">
                <label for="name" class="col-sm-3 control-label">{{trans('resource::view.Request.Create.Programming languages')}}</label>
                <div class="col-md-9">
                    <span>
                        {{ implode(', ', $programs) }}
                    </span>

                    <input type="text" style="opacity: 0; position: absolute" value="{{isset($allProgrammingLangs) ? 1 : ''}}" id="chk_pro" name="chk_pro" />
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group position-relative form-label-left">
                <label for="name" class="col-sm-3 control-label">{{trans('resource::view.Request.Create.Effort')}}</label>
                <div class="col-md-9">
                    <span>
                        {{ $effort }}
                    </span>

                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group position-relative form-label-left">
                <label for="name" class="col-sm-3 control-label">{{trans('resource::view.Request.Create.Deadline')}}</label>
                <div class="col-md-9">
                    <span class="deadline">
                        {{$request->deadline}}
                    </span>

                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group position-relative form-label-left">
                <label for="name" class="col-sm-3 control-label">{{trans('resource::view.Request.Create.Saler')}}</label>
                <div class="col-md-9">
                    <span>
                        {{$saler ? $saler->email : ''}} {{$saler ? '('.$saler->name.')' : ''}}
                    </span>

                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group position-relative form-label-left">
                <label for="name" class="col-sm-3 control-label">{{trans('resource::view.Request.Create.Onsite')}}</label>
                <div class="col-md-9">
                    <span>
                        {{$onsite}}
                    </span>

                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group position-relative form-label-left">
                <label for="name" class="col-sm-3 control-label">{{trans('resource::view.Request.Create.Salary')}}</label>
                <div class="col-md-9">
                    <span class="salary">
                        {{$request->salary}}
                    </span>

                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group position-relative form-label-left">
                        <label for="name" class="col-sm-3 control-label">{{trans('resource::view.Request.Create.Interviewer')}}</label>
                        <div class="col-md-9">
                            <span>    
                                <?php 
                                    $interName = [];
                                    if (count($interviewers)) :
                                        foreach($interviewers as $interviewer) :
                                            $interName[] = $interviewer->email;
                                        endforeach;
                                    endif;
                                    echo implode(', ', $interName);
                                ?>
                            </span>
                        </div>
                    </div>
                </div>  
            </div>
        </div>
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group position-relative form-label-left">
                        <label for="name" class="col-sm-3 control-label">{{trans('resource::view.Request.Create.Created by')}}</label>
                        <div class="col-md-9">
                            <span>
                                {{$createdBy->email}} ({{$createdBy->name}})
                            </span>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group position-relative form-label-left">
                        <label for="name" class="col-sm-3 control-label">{{trans('resource::view.Request.Create.Location')}}</label>
                        <div class="col-md-9">
                            <span class="location">
                                @if ($request->location)
                                    {{ $places[$request->location]}}
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group position-relative form-label-left">
                        <label for="name" class="col-sm-3 control-label">{{trans('resource::view.Request.Create.Request date')}}</label>
                        <div class="col-md-9">
                            <span>
                                {{$request->request_date}}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group position-relative form-label-left">
                <label class="col-sm-3 control-label">{{trans('resource::view.Candidate type')}}</label>
                <div class="col-md-9 description">
                    {{ $strCandidateType }}
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group position-relative form-label-left">
                <label for="name" class="col-sm-3 control-label">{{trans('resource::view.Request.Create.Description')}}</label>
                <div class="col-md-9">
                    <span class="description content_br">{{ $request->description }}</span>

                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group position-relative form-label-left">
                <label for="name" class="col-sm-3 control-label">{{trans('resource::view.Request.Create.Job qualifications')}}</label>
                <div class="col-md-9">
                    <span class="job_qualifi content_br">{{ $request->job_qualifi }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group position-relative form-label-left">
                <label for="name" class="col-sm-3 control-label">{{trans('resource::view.Request.Create.Benefits')}}</label>
                <div class="col-md-9">
                    <span class="benefits content_br">{{ $request->benefits }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group position-relative form-label-left">
                <label for="name" class="col-sm-3 control-label">{{trans('resource::view.Request.Create.Note')}}</label>
                <div class="col-md-9">
                    <span class="content_br">{{ $request->note }}</span>
                </div>
            </div>
        </div>
    </div>
    {{--@if ($request->type == getOptions::TYPE_RECRUIT --}}
        {{--&& $request->status == getOptions::STATUS_INPROGRESS --}}
        {{--&& $request->deadline >= date('Y-m-d')--}}
        {{--&& Permission::getInstance()->isAllow('resource::request.postDataRequest'))--}}
    {{--<div class="row">--}}
        {{--<div class="col-md-12 align-center margin-top-40">--}}
            {{--<button type="button" class="btn btn-primary btn-preview">--}}
                {{--@if ($request->published)--}}
                {{--{{trans('resource::view.Request.Create.Republish')}}--}}
                {{--@else--}}
                {{--{{trans('resource::view.Request.Create.Publish')}}--}}
                {{--@endif--}}
            {{--</button>--}}
        {{--</div>--}}
    {{--</div>--}}
    {{--@endif--}}
</div>
<div id="input_value" class="hidden">
    <input type="hidden" id="title" value="{{ $request->title }}" />
    <input type="hidden" id="deadline" value="{{ $request->deadline }}" />
    <input type="hidden" id="location" value="{{ $request->location }}" />
    <input type="hidden" id="salary" value="{{ $request->salary }}" />
    <input type="hidden" id="description" value="{{ $request->description }}" />
    <input type="hidden" id="benefits" value="{{ $request->benefits }}" />
    <input type="hidden" id="job_qualifi" value="{{ $request->job_qualifi }}" />
    <input type="hidden" id="status" value="{{ $request->status }}" />
    @if (count($programIds))
    <input type="hidden" id="programs" value="{{ implode(',', $programIds) }}" />
    @endif
    @if (count($arrCandidateTypeId))
    <input type="hidden" id="types" value="{{ implode(',', $arrCandidateTypeId) }}" />
    @endif
</div>
<!-- INCLUDE MODAL TEAMS OF REQUEST -->
@include ('resource::request.include.add_team_request')
@include ('resource::request.include.request_preview')
