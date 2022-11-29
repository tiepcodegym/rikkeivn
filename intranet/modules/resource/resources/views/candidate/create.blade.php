@extends('layouts.default')
<?php

use Carbon\Carbon;
use Rikkei\Core\View\View as ViewHelper;
use Rikkei\Resource\View\getOptions;
use Rikkei\Resource\Model\Channels;
use Rikkei\Team\Model\Team;
use Rikkei\Sales\View\View;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Team\View\TeamList;
use Rikkei\Resource\View\View as Rview;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Resource\View\CandidatePermission;
?>
@section('title')
@if (isset($candidate) && $candidate)
{{ trans('resource::view.Candidate.Create.Edit candidate') }}
@else
{{ trans('resource::view.Candidate.Create.Create candidate') }}
@endif

@endsection
<?php
if (isset($candidate)) {
    $checkEdit = true;
    $urlSubmit = route('resource::candidate.postCreate', ['id' => $candidate->id]);
} else {
    $checkEdit = false;
    $urlSubmit = route('resource::candidate.postCreate');
}
$teamsOptionAll = TeamList::toOption(null, true, false);
$urlGetTeam = route('resource::candidate.getTeamByRequest');
$urlGetPosition = route('resource::candidate.getPositionByTeam');
$interestedOptions = getOptions::listInterestedOptions();
$useremail = Auth::user()->email;
$username = ViewHelper::getNickName($useremail);
$userID = Auth::user()->employee_id;
$receivedCvDate = Carbon::now()->format('Y-m-d');
?>

@section('css')
<style>
.placeholder {
    position: relative;
}
.placeholder::after {
    position: absolute;
    top: 30%;
    right: 17%;
    content: attr(data-placeholder);
}
</style>
<link href="{{ CoreUrl::asset('sales/css/sales.css') }}" rel="stylesheet" type="text/css" >
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" >
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">    
<link rel="stylesheet" href="{{ CoreUrl::asset('project/css/edit.css') }}" />
<link rel="stylesheet" href="{{ CoreUrl::asset('resource/css/resource.css') }}" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/minimal/_all.css" />
@endsection

@section('content')
<div class="css-create-page request-create-page candidate-create-page">
    <div class="css-create-body">
        @if ($checkEdit)
        <div class="row position-relative">
            <div class="col-md-12 request-detail-link right-link">
                @if (CandidatePermission::canReApply($candidate->status) &&
                    $candidate->countActiveStatus() === 0)
                <form id="frm-replicate-candidate"  class="form-horizontal" method="post" action="{{ route('resource::candidate.reapply') }}" enctype="multipart/form-data">
                    <input type="hidden" name="candidate_id" value="{{ $candidate->id }}" />
                    {!! csrf_field() !!}
                    <button type="submit" class="btn btn-primary btn-lg">Re-apply</button>
                </form>
                @endif
                <a class="pull-right" target="_blank" href="{{ route('resource::candidate.detail', ['id'=>$candidate->id]) }}">
                    <span>
                        <i class="fa fa-hand-pointer-o"></i>
                        {{ trans('resource::view.View detail') }}
                    </span>
                </a>
                @include('resource::candidate.include.apply_list', ['page' => 'edit'])
                @if ($candidate->status == getOptions::DRAFT)
                <form action="{{ route('resource::candidate.update_status_candidate') }}" method="post">
                    <input type="hidden" name="_token" value="{{ csrf_token()}}">
                    <input type="hidden" name="candidate_id" value="{{ $candidate->id}} ">
                    <button type="submit" class="btn btn-warning">Confirm</button>
                </form>
                @endif
            </div>
        </div>
        @endif
        <form id="form-create-candidate" class="form-horizontal" method="post" action="{{$urlSubmit}}" enctype="multipart/form-data">
            {!! csrf_field() !!}
            @if($checkEdit)
            <input type="hidden" name="candidate_id" value="{{$candidate->id}}">
            @foreach ($allLangs as $lang)
            <input class="language-val" type="hidden" name="languages[{{ $lang->lang_id }}]" value="{{ $lang->lang_level_id }}" />
            @endforeach
            @endif
            <input type="hidden" name="presenter_id" value="{{$checkEdit ? $candidate->presenter_id : 0}}">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group margin-top-10">
                        <label class="col-md-3 control-label">{{trans('resource::view.Candidate.Create.Upload CV')}}</label>
                        <div class="col-md-9 choose_file btn btn-success btn-outline-secondary">
                            <span>{{ trans('resource::view.Choose file') }}</span>
                            <input type="file" class="width-93 form-control" name="cv" id="cv">
                        </div>
                        @if ($checkEdit && $candidate->cv)
                            <?php
                            $filename = substr($candidate->cv, strrpos($candidate->cv, '/') + 1);
                            $filePath = $pathFolder . '/' . $filename;
                            $fileDiskPath = storage_path("app/" . Config::get('general.upload_storage_public_folder') . "/" . Candidate::UPLOAD_CV_FOLDER . $filename);
                            ?>
                            <label class="filename">
                                @if (file_exists($fileDiskPath))
                                    <a href="#" data-toggle="modal"
                                       onclick='viewFile({{ Candidate::TYPE_CV }}, "https://docs.google.com/gview?url={{ $filePath }}&embedded=true");'>{{ trans('resource::view.View CV') }}</a>
                                @else
                                    <span class="error">{{ trans('resource::message.File does not exist') . ', ' . trans('resource::message.Please update new file') }}</span>
                                @endif
                            </label>
                            @include('resource::candidate.include.modal.view_file')
                        @endif
                    </div>
                </div>
            </div>
            <div class="row">
                <!-- Basic information -->
                <div class="col-md-4">
                    <div class="box box-primary padding-bottom-30">
                        <div class="box-header with-border">
                            <h4 class="box-title">{{ trans('resource::view.Basic information') }}</h4>
                        </div>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group margin-top-10">
                                        <label for="fullname" class="col-md-3 control-label">{{trans('resource::view.Candidate.Create.Fullname')}}<em class="required" aria-required="true">*</em></label>
                                        <div class="col-md-9">
                                            <span>                                  
                                                <input type="text" id="fullname" name="fullname" class="form-control" tabindex="1" value="{{$checkEdit ? $candidate->fullname : old('fullname', '')}}" />
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group margin-top-10">
                                        <label for="email" class="col-md-3 control-label">{{trans('resource::view.Candidate.Create.Email')}}<em class="required" aria-required="true">*</em></label>
                                        <div class="col-md-9">
                                            <span>                                  
                                                <input type='text' class="form-control" id="email" name="email"  tabindex=2 value="{{$checkEdit ? $candidate->email: ''}}" />
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group margin-top-10">
                                        <label for="mobile" class="col-md-3 control-label">{{trans('resource::view.Candidate.Create.Mobile')}}</label>
                                        <div class="col-md-9">
                                            <span>                                  
                                                <input type='text' class="form-control" id="mobile" name="mobile"  tabindex=3 maxlength="50" value="{{$checkEdit ? $candidate->mobile: ''}}" />
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group margin-top-10">
                                        <label for="birthday" class="col-md-3 control-label">{{trans('resource::view.Candidate.Create.Birthday')}}<em class="required" aria-required="true">*</em></label>
                                        <div class="col-md-9">
                                            <span>                                  
                                                <input type='text' class="form-control date" id="birthday" name="birthday" data-provide="datepicker" placeholder="YYYY-MM-DD" tabindex=4 value="{{$checkEdit ? $candidate->birthday: ''}}" autocomplete="off"/>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group margin-top-10">
                                        <label for="gender" class="col-md-3 control-label">{{ trans('resource::view.Gender') }}<em class="required" aria-required="true">*</em></label>
                                        <div class="col-md-9">
                                            <select id="gender" name="gender" class="form-control select-search" tabindex="5">
                                                <option value="{{ Candidate::GENDER_MALE }}" {{ ($checkEdit && $candidate->gender == Candidate::GENDER_MALE) ? 'selected' : '' }}>{{ trans('resource::view.Male') }}</option>
                                                <option value="{{ Candidate::GENDER_FEMALE }}" {{ ($checkEdit && $candidate->gender == Candidate::GENDER_FEMALE) ? 'selected' : '' }}>{{ trans('resource::view.Female') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group margin-top-10">
                                        <label for="skype" class="col-md-3 control-label">{{trans('resource::view.Skype')}}<em class="required">*</em></label>
                                        <div class="col-md-9">
                                            <span>                                  
                                                <input type='text' class="form-control" id="skype" name="skype"  tabindex=6 maxlength="50" value="{{$checkEdit ? $candidate->skype : ''}}" />
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group margin-top-10">
                                        <label for="other_contact" class="col-md-3 control-label">{{trans('resource::view.Other contact')}}</label>
                                        <div class="col-md-9">
                                            <span>                                  
                                                <textarea rows="3" class="form-control" id="other_contact" name="other_contact"  tabindex=7 maxlength="500"  >{{$checkEdit ? $candidate->other_contact : ''}}</textarea>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ./Basic information -->
                <!-- Experience information -->
                <div class="col-md-8">
                    <div class="box box-primary padding-bottom-30">
                        <div class="box-header with-border">
                            <h4 class="box-title">{{ trans('resource::view.Experience information') }}</h4>
                        </div>
                        <div class="box-body">
                            <div class="row">                           
                                <div class="col-md-12">
                                    <div class="form-group margin-top-10">
                                        <label for="languages" class="col-md-3 control-label">{{trans('resource::view.Candidate.Create.Languages')}}</label>
                                        <div class="col-md-9">
                                            <span>                                  
                                                <input type="text" id="languages" class="form-control bg-white cursor-pointer" readonly=""
                                                       value="{{ $checkEdit ? $langSelected->lang_selected : '' }}"
                                                />
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group margin-top-10">
                                        <label id="candidate-program-lang" for="programs" class="col-md-3 control-label">{{trans('resource::view.Candidate.Create.Programming languages')}} </label>
                                        <div class="col-md-9">
                                                <span>
                                                    <input type="text" id="programLanguages" class="form-control bg-white cursor-pointer"readonly="" @if($programmingLangs) value="{{ $programmingLangs->programs_name }}" @endif data-toggle="collapse" data-target="#expand"/>
                                                </span>
                                                <div id="expand" class="box-body collapse" style="width: 93%; border: 1px solid rgb(210, 214, 222);">
                                                    <div id="programmingLangs">
                                                    @if($allProgrammingLangs)
                                                    @foreach($allProgrammingLangs as $item)
                                                        <div class="row">
                                                            <div class="col-md-6" style="margin-bottom: 20px">
                                                                <select id="program" name="programs[]" style="width: 100%;" class="form-control" tabindex="9" aria-required="true">
                                                                @foreach($programs as $option)
                                                                    <option value="{{ $option->id }}" @if(isset($item) && $option->id == $item) selected @endif>{{ $option->name }}</option>
                                                                @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-md-4 placeholder" style="margin-bottom: 20px;" data-placeholder="{{trans('resource::view.Years')}}">
                                                                <input type="text" class="form-control bg-white cursor-pointer" style="width: 100%;" id="expYear" name="inputYear[]" value="{{Candidate::getExpYear($candidate->id,$item)}}"/>
                                                            </div>
                                                            <div class="col-md-2">
                                                                <button type="button" class="btn btn-delete" style="float: right;">{{trans('resource::view.Remove')}} <i class="fa fa-close"></i></button>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                    @endif
                                                    </div>
                                                    <div>
                                                        <button type="button" class="btn btn-success btn-success-language">{{trans('resource::view.Add new')}} <i class="fa fa-plus"></i></button>
                                                    </div>
                                                </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group margin-top-10">
                                        <label for="experience" class="col-md-3 control-label">{{trans('resource::view.Candidate.Create.Experience')}}<em class="required" aria-required="true">*</em></label>
                                        <div class="col-md-9">
                                            <span>                                  
                                                <input id="experience" name="experience" type="number" class="form-control num" min="0" value="{{$checkEdit ? $candidate->experience: '0'}}" tabindex='10' />
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group margin-top-10">
                                        <label for="university" class="col-md-3 control-label">{{trans('resource::view.Candidate.Create.University')}}<em class="required" aria-required="true">*</em></label>
                                        <div class="col-md-9">
                                            <span>                                  
                                                <textarea maxlength="500" rows="3" class="form-control col-md-9" id="university" name="university" tabindex=11>{{$checkEdit ? $candidate->university: ''}}</textarea>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group margin-top-10">
                                        <label for="certificate" class="col-md-3 control-label">{{trans('resource::view.Candidate.Create.Certificate')}}</label>
                                        <div class="col-md-9">
                                            <span>                                  
                                                <textarea maxlength="500" rows="3" class="form-control col-md-9" id="certificate" name="certificate" tabindex=12>{{$checkEdit ? $candidate->certificate: ''}}</textarea>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group margin-top-10">
                                        <label for="old_company" class="col-md-3 control-label">{{trans('resource::view.Old company')}}</label>
                                        <div class="col-md-9">
                                            <span>                                  
                                                <textarea maxlength="500" rows="3" class="form-control col-md-9" id="old_company" name="old_company" tabindex=13>{{$checkEdit ? $candidate->old_company: ''}}</textarea>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ./ Experience information -->
            </div>
            <div class="row">
                <!-- Request information -->
                <div class="col-md-4">
                    <div class="box box-primary  padding-bottom-30">
                        <div class="box-header with-border">
                            <h4 class="box-title">{{ trans('resource::view.Request information') }}</h4>
                        </div>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group margin-top-10">
                                        <label for="name" class="col-md-3 control-label">{{trans('resource::view.Candidate.Create.Request', ['request'=> ''])}}</label>
                                        <div class="col-md-9">
                                            <span>                                  
                                                <select id="request_id" name="requests[]" class="form-control width-93 multiple_select" multiple="multiple">
                                                    @foreach($listRequest as $option)
                                                    <option value="{{ $option->id }}" @if(isset($allRequests) && in_array($option->id, $allRequests)) selected @endif>{{ Team::getTeamNameById($option->team_id) }} - {{ Rview::subString($option->title, Candidate::SUB_TITLE_LEN) }}</option>
                                                    @endforeach
                                                </select>
                                            </span>
                                        </div>
                                    </div>
                                </div>                   
                                <div class="col-md-12">
                                    <div class="form-group margin-top-10 row-team">
                                        <label for="team_id" class="col-md-3 control-label">{{trans('resource::view.Team')}}<em class="required" aria-required="true">*</em> <i class="fa fa-spin fa-refresh hidden"></i></label>
                                        <div class="col-md-9">
                                            <span>                                  
                                                <select id="team_id" name="teams[]" class="form-controlwidth-100-per bootstrap-multiselect teams multiple_select" multiple="multiple" >
                                                    @foreach($teamsOptionAll as $option)
                                                    <option value="{{ $option['value'] }}" 
                                                            @if(isset($allTeams) && in_array($option['value'], $allTeams)) selected @endif
                                                            >{{ $option['label'] }}</option>
                                                    @endforeach
                                                </select>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group margin-top-10">
                                        <label for="position_apply" class="col-md-3 control-label">{{trans('resource::view.Candidate.Create.Position apply')}}<em class="required" aria-required="true">*</em> <i class="fa fa-spin fa-refresh hidden"></i></label>
                                        <div class="col-md-9">
                                            <span>                                  
                                                <select id="position_apply" name="positions[]" class="form-control width-93 multiple_select" multiple="multiple">
                                                    @foreach($roles as $key => $option)
                                                    <option value="{{ $key }}" 
                                                            @if(isset($positions) && in_array($key, $positions)) selected @endif
                                                            >{{ $option }}</option>
                                                    @endforeach
                                                </select>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div> 
                        </div>
                    </div>
                </div>
                <!-- ./Request information -->       
                <!-- Recruit information --> 
                <div class="col-md-8">
                    <div class="box box-primary padding-bottom-30">
                        <div class="box-header with-border">
                            <h4 class="box-title">{{ trans('resource::view.Recruit information') }}</h4>
                        </div>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-6 item-recruit">
                                    <div class="form-group margin-top-10">
                                        <label for="received_cv_date" class="col-md-3 control-label">{{trans('resource::view.Candidate.Create.Received CV date')}}<em class="required" aria-required="true">*</em></label>
                                        <div class="col-md-9">
                                            <span>                                  
                                                <input type='text' class="form-control date" id="received_cv_date" name="received_cv_date"data-provide="datepicker"
                                                       placeholder="YYYY-MM-DD" tabindex=9 value="{{$checkEdit ? $candidate->received_cv_date: date('Y-m-d')}}" autocomplete="off" />
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 item-recruit">
                                    <div class="form-group margin-top-10">
                                        <label for="name" class="col-md-3 control-label">{{trans('resource::view.Candidate.Recruiter')}}<em class="required" aria-required="true">*</em></label>
                                        <div class="col-md-9">
                                            <span>                                  
                                                <select id="recruiter" name="recruiter" class="form-control width-93 select2-hidden-accessible">
                                                    <option value="0">{{trans('resource::view.Candidate.Create.Select recruiter')}}</option>
                                                    @foreach ($hrAccounts as $nickname => $email)
                                                    <option value="{{$email}}" @if ($checkEdit && $email == $candidate->recruiter) selected @endif>{{$email}}</option>
                                                    @endforeach
                                                </select>
                                            </span>

                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 item-recruit">
                                    <div class="form-group margin-top-10">
                                        <label for="found_by" class="col-md-3 control-label">{{trans('resource::view.Found by')}}</label>
                                        <div class="col-md-9">
                                            <span>                                  
                                                <select id="found_by" name="found_by" class="form-control width-93" data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}">
                                                @if ($checkEdit && $founder)
                                                <option value="{{ $founder->id }}" selected="">{{ ViewHelper::getNickName($founder->email) }}</option>
                                                @endif
                                                </select>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 item-recruit">
                                    <div class="form-group margin-top-10">
                                        <label for="channel_id" class="col-md-3 control-label">{{trans('resource::view.Candidate.Create.Channel')}}<em class="required" aria-required="true">*</em></label>
                                        <div class="col-md-9">
                                            <span>                                  
                                                <select id="channel_id" name="channel_id" class="form-control width-93 select-search" >
                                                    <option value="0" is_presenter="0">{{trans('resource::view.Candiadte.Create.Select channel')}}</option>
                                                    @foreach($channels as $option)
                                                    <option value="{{ $option->id }}" is_presenter="{{ $option->is_presenter }}" @if($checkEdit && $option->id == $candidate->channel_id) selected @endif>{{ $option->name }}</option>
                                                    @endforeach
                                                </select>
                                                <br>
                                                <a class="view-detail 
                                                <?php if (!$checkEdit || ($checkEdit && $candidate->presenter_id == 0)) {
                                                    echo 'hidden';
                                                } ?>
                                                   " href="javascript:void(0)" onclick="viewDetail();">View detail</a>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                @if($checkEdit)
                                <div class="col-md-6 item-recruit cost {{ $candidate->channel_type == Channels::COST_CHANGE  ? '' : 'hidden' }}">
                                    <div class="form-group margin-top-10">
                                        <label class="col-md-3 control-label">{{ trans('resource::view.Request.Detail.Cost') }}</label>
                                        <div class="col-md-9">
                                            <span>
                                                <span>
                                                    <input autocomplete="off" type='text' class="form-control col-md-9" name="cost" value="@if($checkEdit){{ $candidate->cost ? number_format($candidate->cost) : null }}@endif" >
                                                </span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                @else
                                    <div class="col-md-6 item-recruit cost hidden">
                                        <div class="form-group margin-top-10">
                                            <label class="col-md-3 control-label">{{ trans('resource::view.Request.Detail.Cost') }}</label>
                                            <div class="col-md-9">
                                            <span>
                                                <span>
                                                    <input autocomplete="off" type='text' class="form-control col-md-9" name="cost" value="" >
                                                </span>
                                            </span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="col-md-6 item-recruit">
                                    <div class="form-group margin-top-10">
                                        <label class="col-md-3 control-label">{{trans('resource::view.Type')}}<em class="required" aria-required="true">*</em></label>
                                        <div class="col-md-9">
                                            <span>
                                                <span>                                  
                                                    <select id="type" name="type" class="form-control select-search" >
                                                        <option value="0">{{trans('resource::view.Select type')}}</option>
                                                        @foreach($typeOptions as $option)
                                                        <option value="{{ $option['id'] }}" @if($checkEdit && $option['id'] == $candidate->type) selected @endif>{{ $option['name'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </span>                            
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 item-recruit">
                                    <div class="form-group margin-top-10">
                                        <label class="col-md-3 control-label">{{trans('resource::view.Screening')}}</label>
                                        <div class="col-md-9">
                                            <span>
                                                <span>
                                                    <textarea maxlength="500" rows="1" class="form-control col-md-9" id="screening" name="screening" tabindex="11">{{ $checkEdit ? $candidate->screening : '' }}</textarea>
                                                </span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                @if ($checkEdit)
                                    <div class="col-md-6 item-recruit">
                                        <div class="form-group margin-top-10">
                                            <label class="col-md-3 control-label">{{trans('resource::view.Candidate.Detail.Status')}}<em class="required" aria-required="true">*</em></label>
                                            <div class="col-md-9">
                                                <span>                                  
                                                    <select id="status" name="status" class="form-control select-search" disabled>
                                                        @foreach ($statusOption as $option)
                                                        <option value="{{ $option['id'] }}" @if($option['id'] == getOptions::getInstance()->getSelectedCandidateStatus($candidate)) selected @endif>{{ $option['name'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 item-recruit">
                                        <div class="form-group margin-top-10">
                                            <label class="col-md-3 control-label">{{trans('resource::view.Candidate.Create.Reason update')}}<em class="required" aria-required="true">*</em></label>
                                            <div class="col-md-9">
                                                <span>                                  
                                                    <textarea maxlength="500" rows="1" class="form-control col-md-9" id="note" name="note" tabindex="10">{{ $checkEdit ? $candidate->note : '' }}</textarea>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="col-md-6 item-recruit">
                                    <div class="form-group margin-top-10">
                                        <label class="col-md-3 control-label">{{trans('resource::view.Candidate.Create.Interested')}}</label>
                                        <div class="col-md-9">
                                            <span>
                                                <select id="interested" name="interested" class="form-control">
                                                    @foreach ($interestedOptions as $key => $interested)
                                                        <option value="{!! $key !!}"
                                                                class="{!! $interested['class'] !!} font-15"
                                                                @if ($checkEdit && (int)$candidate->interested === $key) selected @endif>{!! $interested['label'] !!}</option>
                                                    @endforeach
                                                </select>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                @if ($checkEdit && $candidate->type_candidate == Candidate::TYPE_FROM_PRESENTER )
                                    <div class="col-md-6 item-recruit">
                                        <div class="form-group margin-top-10">
                                            <label class="col-md-3 control-label">{{trans('resource::view.Recommend.comment')}}</label>
                                            <div class="col-md-9">
                                                <span>
                                                    <textarea disabled maxlength="500" rows="1" class="form-control col-md-9" tabindex="10">{{ $candidate->comment ? $candidate->comment : '' }}</textarea>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ./ Recruit information -->
            </div>
            <div class="row">
                <div class="col-md-12 align-center margin-top-40">
                    <button type="submit" class="btn btn-primary save_values">{{trans('resource::view.Request.Create.Submit')}}</button>                    
                    <button id="restore_values" class="btn btn-default" type="button">{{ trans('resource::view.Restore Values') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="modal " id="modal-channel" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{trans('resource::view.Nhập người giới thiệu')}}</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="channel_set" />
                <div class="row">
                    <div class="col-md-12 margin-top-20">
                        <div class="presenter-id-container <?php if ($checkEdit && !empty($candidate->presenter_text)) {echo 'hidden';} ?>">
                            <select id="presenter"  class="form-control width-93" style="width:100%" 
                                    data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}"
                                    data-old-id="{{ ($checkEdit && $candidate->presenter_id) ? $candidate->presenter_id : 0 }}">
                                @if ($checkEdit && $presenter)
                                <option value="{{ $presenter->id }}" selected="">{{ ViewHelper::getNickName($presenter->email) }}</option>
                                @endif
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" onclick="cancelPresenter();">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="setPresenter();">Save</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
@include('resource::candidate.include.modal.choose_language')
@endsection

@section('script')
<script type="text/javascript">
    $(".btn-success-language").click(function () {
        $("#programmingLangs").append('<div class="row"><div class="col-md-6" style="margin-bottom: 20px"><select id="program" style="width: 100%;" name="programs[]" class="form-control" tabindex="9" aria-required="true"><option value="0"> {{ trans('resource::view.Choose programming language') }} </option>@foreach($programs as $option)<option value="{{ $option->id }}">{{ $option->name }}</option>@endforeach</select></div><div class="col-md-4 placeholder" style="margin-bottom: 20px" data-placeholder="{{trans('resource::view.Years')}}"><input type="text" class="form-control bg-white cursor-pointer" style="width: 100%;" id="inputYear" name="inputYear[]" value=""/></div><div class="col-md-2"><button type="button" class="btn btn-delete" style="float: right;">{{trans('resource::view.Remove')}} <i class="fa fa-close"></i></button></div></div>');
    });
</script>
<script>
    var chooseEmployeeText = '<?php echo trans("resource::view.Choose employee") ?>';
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="{{ CoreUrl::asset('common/js/methods.validate.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="{{ CoreUrl::asset('resource/js/candidate/select2.common.js') }}"></script>
<script src="{{ CoreUrl::asset('resource/js/candidate/create.js') }}"></script>
<script src="{{ CoreUrl::asset('resource/js/candidate/common.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/icheck.min.js"></script>
<script src="{{ CoreUrl::asset('lib/js/jquery.match.height.addtional.js') }}"></script>
<script>
    $('#position_apply').multiselect();
    $('#role').select2();
    $('#channels').select2();
    $('#recruiter').select2();
    jQuery(document).ready(function($) {
        selectSearchReload();
        RKfuncion.bootstapMultiSelect.init({
            nonSelectedText: '{{ trans('project::view.Choose items') }}',
            allSelectedText: '{{ trans('project::view.All') }}',
            nSelectedText: '{{ trans('project::view.items selected') }}',
        });
        $(function() {
            $('.item-basic, .item-recruit, .item-experience').matchHeight();
        });
    });

    function addCommaInNumber(number) {
        return (number + '').replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    function removeCommaInNumber(number) {
        return (number + '').replace(/,/g, '');
    }

    $(document).on('focusin', '.cost input', function (event) {
        let number = removeCommaInNumber($(this).val());
        $(this).val(number);
    });
    $(document).on('focusout', '.cost input', function (event) {
        let number = addCommaInNumber(parseInt($(this).val() ? $(this).val() : 0));
        $(this).val(number);
    });

    $('#request_id').multiselect({
        enableFiltering: true,
        enableCaseInsensitiveFiltering: true,
        buttonText: function(options, select) {
            return customBtnText(options);
        },
    });
</script>
<script>
//define texts
var channelChange = JSON.parse('{!! json_encode($channelChange) !!}');
var requiredText = '{{trans('resource::message.Required field')}}';
var invalidYear = '{{trans('resource::message.Year is invalid')}}';
var valueYear = '{{trans('resource::message.Value not exceeding 100')}}';
var languageRepeats = '{{trans('resource::view.The selected language repeats!')}}';
var invalidEmail = '{{trans('resource::message.Email is invalid')}}';
var token = '{{Session::token()}}';
var phoneValidText = '{{trans('resource::message.Phone is invalid')}}';
var checkEdit = {{$checkEdit ? $checkEdit : 0}};
var notAllowTypeText = '{{trans("resource::message.Not allow file type.")}}';
var notAllowSizeText = '{{trans("resource::message.Not allow file size.")}}';
var presenterYes = {{Channels::PRESENTER_YES}};
var urlCheckMail = '{{route("resource::candidate.checkCandidateMail")}}';
var urlCheckMailRecommend = '{{ route("resource::candidate.checkMailRecommend") }}';
var uniqueMail = '{{trans("resource::view.Email is exist")}}';
var id = 0;
var routeGetTeam = '{{$urlGetTeam}}';
var routeGetPosition = '{{$urlGetPosition}}';
var urlGetLangLevel = '{{route("resource::candidate.getLevelByLang")}}';
var chooseLevelText = '{{ trans("resource::view.Choose level") }}';
var noLanguageSelectedMessage = '{{ trans("resource::message.No language selected") }}';
// get position develop
var devPosition = {{getOptions::ROLE_DEV}};
<?php 
    if ($checkEdit) { ?>
        id = $('input[name=candidate_id]').val();
<?php    }
?>

//Store languages with level
var langArray = <?php echo json_encode($langArray); ?>;
var typeCv = {{ Candidate::TYPE_CV }},
    username = '{{ $username }}',
    useremail = '{{ $useremail }}',
    userId = '{{ $userID }}',
    receivedCvDate = '{{ $receivedCvDate }}',
    urlGenerateCV = '{{ config('api.pdf_cv_generate') }}';
</script>
@endsection
