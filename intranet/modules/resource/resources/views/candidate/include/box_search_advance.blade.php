<?php

use Rikkei\Resource\Model\Candidate;
use Rikkei\Resource\Model\CandidateRequest;
use Rikkei\Resource\Model\CandidateTeam;
use Rikkei\Resource\Model\CandidatePosition;
use Rikkei\Test\Models\Result;
use Rikkei\Resource\Model\CandidateLanguages;
use Rikkei\Resource\Model\CandidateProgramming;
use Rikkei\Resource\View\getOptions;

$candidateTable = Candidate::getTableName();
$candidateRequestTable = CandidateRequest::getTableName();
$candidateTeamTable = CandidateTeam::getTableName();
$candidatePosTable = CandidatePosition::getTableName();
$resultTable = Result::getTableName();
$candidateLangtable = CandidateLanguages::getTableName();
$canProTable = CandidateProgramming::getTableName();
?>
<form autocomplete="off">
<div class="form-group col-sm-12 select-columns">
    <select class="form-control filter-choice" multiple="multiple">
        @foreach (Candidate::COLUMN_SEARCH as $field)
            <?php $name = ucfirst(str_replace('_', ' ', $field)); ?>
        <option value="{{ $field }}">{{ trans("resource::view.$name") }}</option>
        @endforeach
    </select>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="id" class="col-sm-3 control-label">{{ trans('resource::view.Id') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.id">
        @include('resource::candidate.include.compare_list', ['like' => false])
    </div>
    <div class="col-sm-6">
        <input type="text" class="form-control num filter-item" id="id" placeholder="{{ trans('resource::view.Id') }}"
               data-compare='{{ Candidate::COMPARE_EQUAL }}' 
               data-field="{{ $candidateTable }}.id">
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="fullname" class="col-sm-3 control-label">{{ trans('resource::view.Full name') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.fullname">
        @include('resource::candidate.include.compare_list', ['greaterSmaller' => false])
    </div>
    <div class="col-sm-6">
        <input type="text" class="form-control filter-item" id="fullname"  placeholder="{{ trans('resource::view.Full name') }}"
               data-compare='{{ Candidate::COMPARE_LIKE }}' 
               data-field="{{ $candidateTable }}.fullname">
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="email" class="col-sm-3 control-label">Email</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.email">
        @include('resource::candidate.include.compare_list', ['greaterSmaller' => false])
    </div>
    <div class="col-sm-6">
        <input type="text" class="form-control filter-item" id="email" placeholder="Email"
               data-compare='{{ Candidate::COMPARE_LIKE }}' 
               data-field="{{ $candidateTable }}.email">
    </div>
</div>

<div class="form-group col-sm-12 hidden">
    <label for="request" class="col-sm-3 control-label">{{ trans('resource::view.Request') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateRequestTable }}.request_id">
        @include('resource::candidate.include.compare_list', ['greaterSmaller' => false, 'like' => false])
    </div>
    <div class="col-sm-6">
        <select class="form-control select-search filter-item" id="request" data-compare='{{ Candidate::COMPARE_EQUAL }}'
                data-remote-url="{{ URL::route('resource::request.list.search.ajax') }}"
                data-field="{{ $candidateRequestTable }}.request_id"
                data-jointable="{{ $candidateRequestTable }}"
                data-joinfield="{{ $candidateRequestTable }}.candidate_id"
                data-jointofield="{{ $candidateTable }}.id"
                data-except="except">
        </select>
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="group" class="col-sm-3 control-label">{{ trans('resource::view.Groups of request') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{$candidateTeamTable}}.team_id">
        @include('resource::candidate.include.compare_list', ['greaterSmaller' => false, 'like' => false])
    </div>
    <div class="col-sm-6">
        <select class="form-control select-search filter-item" id="group_of_request" data-compare='{{ Candidate::COMPARE_EQUAL }}'
                data-field="{{$candidateTeamTable}}.team_id"
                data-jointable="{{ $candidateTeamTable }}"
                data-joinfield="{{ $candidateTeamTable }}.candidate_id"
                data-jointofield="{{ $candidateTable }}.id"
                data-except="except">
            <option value="">&nbsp;</option>
            @foreach($teamsOptionAll as $option)
            <option value="{{ $option['value'] }}">
                {{ $option['label'] }}
            </option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="position_apply" class="col-sm-3 control-label">{{ trans('resource::view.Position') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{$candidatePosTable}}.position_apply">
        @include('resource::candidate.include.compare_list', ['greaterSmaller' => false, 'like' => false])
    </div>
    <div class="col-sm-6">
        <select class="form-control select-search has-search filter-item" id="position_apply" data-compare='{{ Candidate::COMPARE_EQUAL }}'
                data-field="{{$candidatePosTable}}.position_apply"
                data-jointable="{{ $candidatePosTable }}"
                data-joinfield="{{ $candidatePosTable }}.candidate_id"
                data-jointofield="{{ $candidateTable }}.id"
                data-except="except">
            <option value="">&nbsp;</option>
            @foreach($positionOptions as $key => $value)
            <option value="{{ $key }}">{{ $value }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="channel" class="col-sm-3 control-label">{{ trans('resource::view.Channel') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.channel_id">
        @include('resource::candidate.include.compare_list', ['greaterSmaller' => false, 'like' => false])
    </div>
    <div class="col-sm-6">
        <select class="form-control filter-item select-search has-search" id="channel" 
                data-field="{{ $candidateTable }}.channel_id">
            <option value="">&nbsp;</option>
            @foreach ($channelOptions as $channel)
            <option value="{{ $channel->id }}">{{ $channel->name }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="birthday" class="col-sm-3 control-label">{{ trans('resource::view.Birthday') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.birthday">
        @include('resource::candidate.include.compare_list')
    </div>
    <div class="col-sm-6">
        <input type="text" class="form-control date filter-item" id="birthday"  placeholder="yyyy-mm-dd"
               data-compare='{{ Candidate::COMPARE_LIKE }}' 
               data-field="{{ $candidateTable }}.birthday">
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="mobile" class="col-sm-3 control-label">{{ trans('resource::view.Mobile') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.mobile">
        @include('resource::candidate.include.compare_list', ['greaterSmaller' => false])
    </div>
    <div class="col-sm-6">
        <input type="text" class="form-control filter-item" id="mobile"  placeholder="{{ trans('resource::view.Mobile') }}"
               data-compare='{{ Candidate::COMPARE_EQUAL }}' 
               data-field="{{ $candidateTable }}.mobile">
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="university" class="col-sm-3 control-label">{{ trans('resource::view.University') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.university">
        @include('resource::candidate.include.compare_list', ['greaterSmaller' => false])
    </div>
    <div class="col-sm-6">
        <input type="text" class="form-control filter-item" id="university"  placeholder="{{ trans('resource::view.University') }}"
               data-compare='{{ Candidate::COMPARE_LIKE }}' 
               data-field="{{ $candidateTable }}.university">
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="certificate" class="col-sm-3 control-label">{{ trans('resource::view.Certificate') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.certificate">
        @include('resource::candidate.include.compare_list', ['greaterSmaller' => false])
    </div>
    <div class="col-sm-6">
        <input type="text" class="form-control filter-item" id="certificate"  placeholder="{{ trans('resource::view.Certificate') }}"
               data-compare='{{ Candidate::COMPARE_LIKE }}' 
               data-field="{{ $candidateTable }}.certificate">
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="old_company" class="col-sm-3 control-label">{{ trans('resource::view.Old company') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.old_company">
        @include('resource::candidate.include.compare_list', ['greaterSmaller' => false])
    </div>
    <div class="col-sm-6">
        <input type="text" class="form-control filter-item" id="old_company"  placeholder="{{ trans('resource::view.Company name') }}"
               data-compare='{{ Candidate::COMPARE_LIKE }}' 
               data-field="{{ $candidateTable }}.old_company">
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="experience" class="col-sm-3 control-label">{{ trans('resource::view.Experience') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.experience">
        @include('resource::candidate.include.compare_list')
    </div>
    <div class="col-sm-6">
        <input type="text" class="form-control num filter-item" id="experience"  placeholder="{{ trans('resource::view.Experience') }}"
               data-compare='{{ Candidate::COMPARE_LIKE }}' 
               data-field="{{ $candidateTable }}.experience">
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="received_cv_date" class="col-sm-3 control-label">{{ trans('resource::view.Received cv date') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.received_cv_date">
        @include('resource::candidate.include.compare_list')
    </div>
    <div class="col-sm-6">
        <input type="text" class="form-control date filter-item" id="received_cv_date"  placeholder="yyyy-mm-dd"
               data-compare='{{ Candidate::COMPARE_LIKE }}' 
               data-field="{{ $candidateTable }}.received_cv_date">
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="contact_result" class="col-sm-3 control-label">{{ trans('resource::view.Contact result') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.contact_result">
        @include('resource::candidate.include.compare_list', ['greaterSmaller' => false, 'like' => false])
    </div>
    <div class="col-sm-6">
        <select class="form-control filter-item select-search" id="contact_result" 
                data-field="{{ $candidateTable }}.contact_result">
            <option value="">&nbsp;</option>
            <option value="0">{{ trans('resource::view.Contacting') }}</option>
            @foreach ($resultOptions as $option)
            <option value="{{ $option['id'] }}">{{ $option['name'] }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="test_plan" class="col-sm-3 control-label">{{ trans('resource::view.Test plan') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.test_plan">
        @include('resource::candidate.include.compare_list')
    </div>
    <div class="col-sm-6">
        <input type="text" class="form-control date filter-item" id="test_plan"  placeholder="yyyy-mm-dd"
               data-field="{{ $candidateTable }}.test_plan">
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="test_mark" class="col-sm-3 control-label">{{ trans('resource::view.Test mark') }}</label>
    <div class="col-sm-3">
        <select class="form-control select-search has-search pull-left extra-item" data-field="test_mark">
            <option value="">&nbsp</option>
            <option value="{{ Candidate::GMAT }}">{{ Candidate::GMAT }}</option>
            @foreach ($testType as $type)
            <option value="{{ $type->id }}">{{ $type->name }}</option>
            @endforeach
            <option value="{{ Candidate::SPECIALIZE }}">{{ Candidate::SPECIALIZE }}</option>
        </select>
    </div>
    <div class="col-sm-3 compare-item" data-field="test_mark">
        @include('resource::candidate.include.compare_list', ['like' => false])
    </div>
    <div class="col-sm-3">
        <input type="text" class="form-control filter-item pull-right num num-with-forward-slash" id="test_mark"  placeholder="{{ trans('resource::view.Test mark') }}"
               value="0"
               data-field="test_mark"
               data-except="except">
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="test_result" class="col-sm-3 control-label">{{ trans('resource::view.Test result') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.test_result">
        @include('resource::candidate.include.compare_list', ['greaterSmaller' => false, 'like' => false])
    </div>
    <div class="col-sm-6">
        <select class="form-control filter-item select-search" id="test_result" 
                data-field="{{ $candidateTable }}.test_result">
            <option value="">&nbsp;</option>
            <option value="0">{{ trans('resource::view.Testing') }}</option>
            @foreach ($resultOptions as $option)
            <option value="{{ $option['id'] }}">{{ $option['name'] }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="interview_plan" class="col-sm-3 control-label">{{ trans('resource::view.Interview plan') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.interview_plan">
        @include('resource::candidate.include.compare_list')
    </div>
    <div class="col-sm-6">
        <input type="text" class="form-control date filter-item" id="interview_plan"  placeholder="yyyy-mm-dd"
               data-field="{{ $candidateTable }}.interview_plan">
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="interview2_plan" class="col-sm-3 control-label">{{ trans('resource::view.Interview plan 2') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.interview2_plan">
        @include('resource::candidate.include.compare_list')
    </div>
    <div class="col-sm-6">
        <input type="text" class="form-control date filter-item" id="interview2_plan"  placeholder="yyyy-mm-dd"
               data-field="{{ $candidateTable }}.interview2_plan">
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="interview_result" class="col-sm-3 control-label">{{ trans('resource::view.Interview result') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.interview_result">
        @include('resource::candidate.include.compare_list', ['greaterSmaller' => false, 'like' => false])
    </div>
    <div class="col-sm-6">
        <select class="form-control filter-item select-search" id="interview_result" 
                data-field="{{ $candidateTable }}.interview_result">
            <option value="">&nbsp;</option>
            <option value="0">{{ trans('resource::view.Interviewing') }}</option>
            @foreach ($resultOptions as $option)
            <option value="{{ $option['id'] }}">{{ $option['name'] }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="created_by" class="col-sm-3 control-label">{{ trans('resource::view.Created by') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.created_by">
        @include('resource::candidate.include.compare_list', ['greaterSmaller' => false, 'like' => false])
    </div>
    <div class="col-sm-6">
        <select id="created_by" 
                class="form-control width-93 filter-item select2-hidden-accessible select-search"
                data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}"
                data-field="{{ $candidateTable }}.created_by">
            <option value="">&nbsp;</option>
        </select>
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="test_note" class="col-sm-3 control-label">{{ trans('resource::view.Test note') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.test_note">
        @include('resource::candidate.include.compare_list', ['greaterSmaller' => false])
    </div>
    <div class="col-sm-6">
        <input type="text" class="form-control filter-item" id="test_note"  placeholder="{{ trans('resource::view.Test note') }}"
               data-field="{{ $candidateTable }}.test_note">
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="created_at" class="col-sm-3 control-label">{{ trans('resource::view.Created at') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.created_at">
        @include('resource::candidate.include.compare_list')
    </div>
    <div class="col-sm-6">
        <input type="text" class="form-control date filter-item" id="created_at"  placeholder="yyyy-mm-dd"
               data-field="{{ $candidateTable }}.created_at">
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="updated_at" class="col-sm-3 control-label">{{ trans('resource::view.Updated at') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.updated_at">
        @include('resource::candidate.include.compare_list')
    </div>
    <div class="col-sm-6">
        <input type="text" class="form-control date filter-item" id="updated_at"  placeholder="yyyy-mm-dd"
               data-field="{{ $candidateTable }}.updated_at">
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="status" class="col-sm-3 control-label">{{ trans('resource::view.Status') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.status">
        @include('resource::candidate.include.compare_list', ['greaterSmaller' => false, 'like' => false])
    </div>
    <div class="col-sm-6">
        <select class="form-control filter-item select-search" id="status" 
                data-field="{{ $candidateTable }}.status"
                data-except="except">
            <option value="">&nbsp;</option>
            @foreach ($statusOptions as $status)
            <option value="{{ $status['id'] }}">{{ $status['name'] }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="interview_note" class="col-sm-3 control-label">{{ trans('resource::view.Interview note') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.interview_note">
        @include('resource::candidate.include.compare_list', ['greaterSmaller' => false])
    </div>
    <div class="col-sm-6">
        <input type="text" class="form-control filter-item" id="interview_note"  placeholder="{{ trans('resource::view.Interview note') }}"
               data-field="{{ $candidateTable }}.interview_note">
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="offer_date" class="col-sm-3 control-label">{{ trans('resource::view.Offer date') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.offer_date">
        @include('resource::candidate.include.compare_list')
    </div>
    <div class="col-sm-6">
        <input type="text" class="form-control date filter-item" id="offer_date"  placeholder="yyyy-mm-dd"
               data-field="{{ $candidateTable }}.offer_date">
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="offer_result" class="col-sm-3 control-label">{{ trans('resource::view.Offer result') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.offer_result">
        @include('resource::candidate.include.compare_list', ['greaterSmaller' => false, 'like' => false])
    </div>
    <div class="col-sm-6">
        <select class="form-control filter-item select-search" id="offer_result" 
                data-field="{{ $candidateTable }}.offer_result">
            <option value="">&nbsp;</option>
            <option value="0">{{ trans('resource::view.Offering') }}</option>
            @foreach ($resultOptions as $option)
            <option value="{{ $option['id'] }}">{{ $option['name'] }}</option>
            @endforeach
            <option value="{{ getOptions::RESULT_WORKING }}">{{ trans('resource::view.Candidate.Detail.Working') }}</option>
        </select>
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="offer_feedback_date" class="col-sm-3 control-label">{{ trans('resource::view.Offer feedback') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.offer_feedback_date">
        @include('resource::candidate.include.compare_list')
    </div>
    <div class="col-sm-6">
        <input type="text" class="form-control date filter-item" id="offer_feedback_date"  placeholder="yyyy-mm-dd"
               data-field="{{ $candidateTable }}.offer_feedback_date">
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="offer_note" class="col-sm-3 control-label">{{ trans('resource::view.Offer note') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.offer_note">
        @include('resource::candidate.include.compare_list', ['greaterSmaller' => false])
    </div>
    <div class="col-sm-6">
        <input type="text" class="form-control filter-item" id="offer_note"  placeholder="{{ trans('resource::view.Offer note') }}"
               data-field="{{ $candidateTable }}.offer_note">
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="interviewer" class="col-sm-3 control-label">{{ trans('resource::view.Interviewer') }}</label>
    <div class="col-sm-3 compare-item" data-field="interviewer">
        @include('resource::candidate.include.compare_list', ['greaterSmaller' => false, 'like' => false])
    </div>
    <div class="col-sm-6">
        <select id="interviewer" 
                class="form-control width-93 filter-item select2-hidden-accessible select-search"
                data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}"
                data-field="interviewer"
                data-except="except">
            <option value="">&nbsp;</option>
        </select>
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="recruiter" class="col-sm-3 control-label">{{ trans('resource::view.Recruiter') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.recruiter">
        @include('resource::candidate.include.compare_list', ['greaterSmaller' => false, 'like' => false])
    </div>
    <div class="col-sm-6">
        <select id="recruiter" class="form-control select-search has-search filter-item"
                data-field="{{ $candidateTable }}.recruiter">
            <option value="">&nbsp;</option>
            @foreach ($hrAccounts as $nickname => $email)
            <option value="{{$email}}">{{$email}}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="presenter_id" class="col-sm-3 control-label">{{ trans('resource::view.Presenter') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.presenter_id">
        @include('resource::candidate.include.compare_list', ['greaterSmaller' => false, 'like' => false])
    </div>
    <div class="col-sm-6">
        <select id="presenter_id" 
                class="form-control width-93 filter-item select2-hidden-accessible select-search"
                data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}"
                data-field="{{ $candidateTable }}.presenter_id"
                <option value="">&nbsp;</option>
        </select>
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="found_by" class="col-sm-3 control-label">{{ trans('resource::view.Found by') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.found_by">
        @include('resource::candidate.include.compare_list', ['greaterSmaller' => false, 'like' => false])
    </div>
    <div class="col-sm-6">
        <select id="found_by" 
                class="form-control width-93 filter-item select2-hidden-accessible select-search"
                data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}"
                data-field="{{ $candidateTable }}.found_by"
                <option value="">&nbsp;</option>
        </select>
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="start_working_date" class="col-sm-3 control-label">{{ trans('resource::view.Start working') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.start_working_date">
        @include('resource::candidate.include.compare_list')
    </div>
    <div class="col-sm-6">
        <input type="text" class="form-control date filter-item" id="start_working_date"  placeholder="yyyy-mm-dd"
               data-field="{{ $candidateTable }}.start_working_date">
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="trial_work_end_date" class="col-sm-3 control-label">{{ trans('resource::view.Trial end date') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.trial_work_end_date">
        @include('resource::candidate.include.compare_list')
    </div>
    <div class="col-sm-6">
        <input type="text" class="form-control date filter-item" id="trial_work_end_date"  placeholder="yyyy-mm-dd"
               data-field="{{ $candidateTable }}.trial_work_end_date">
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="screening" class="col-sm-3 control-label">{{ trans('resource::view.Screening') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.screening">
        @include('resource::candidate.include.compare_list', ['greaterSmaller' => false])
    </div>
    <div class="col-sm-6">
        <input type="text" class="form-control filter-item" id="screening"  placeholder="{{ trans('resource::view.Screening') }}"
               data-field="{{ $candidateTable }}.screening">
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="type" class="col-sm-3 control-label">{{ trans('resource::view.Type') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.type">
        @include('resource::candidate.include.compare_list', ['greaterSmaller' => false, 'like' => false])
    </div>
    <div class="col-sm-6">
        <select class="form-control filter-item select-search" id="type" 
                data-field="{{ $candidateTable }}.type">
            <option value="">&nbsp;</option>
            @foreach ($typeOptions as $type)
            <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="type_candidate" class="col-sm-3 control-label">{{ trans('resource::view.Type_candidate') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.type_candidate">
        @include('resource::candidate.include.compare_list', ['greaterSmaller' => false, 'like' => false])
    </div>
    <div class="col-sm-6">
        <select class="form-control filter-item select-search" id="type_candidate"
                data-field="{{ $candidateTable }}.type_candidate">
            <option value="">&nbsp;</option>
            @foreach ($allTypeCandidate as $key => $value)
                <option value="{{ $key }}">{{ $value }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="identify" class="col-sm-3 control-label">{{ trans('resource::view.Identify') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.identify">
        @include('resource::candidate.include.compare_list', ['greaterSmaller' => false])
    </div>
    <div class="col-sm-6">
        <input type="text" class="form-control filter-item" id="identify"  placeholder="{{ trans('resource::view.Identify') }}"
               data-field="{{ $candidateTable }}.identify">
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="skype" class="col-sm-3 control-label">{{ trans('resource::view.Skype') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.skype">
        @include('resource::candidate.include.compare_list', ['greaterSmaller' => false])
    </div>
    <div class="col-sm-6">
        <input type="text" class="form-control filter-item" id="skype"  placeholder="{{ trans('resource::view.Skype') }}"
               data-field="{{ $candidateTable }}.skype">
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="other_contact" class="col-sm-3 control-label">{{ trans('resource::view.Other contact') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.other_contact">
        @include('resource::candidate.include.compare_list', ['greaterSmaller' => false])
    </div>
    <div class="col-sm-6">
        <input type="text" class="form-control filter-item" id="other_contact"  placeholder="{{ trans('resource::view.Other contact') }}"
               data-field="{{ $candidateTable }}.other_contact">
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="gender" class="col-sm-3 control-label">{{ trans('resource::view.Gender') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.gender">
        @include('resource::candidate.include.compare_list', ['greaterSmaller' => false, 'like' => false])
    </div>
    <div class="col-sm-6">
        <select class="form-control filter-item select-search" id="gender" 
                data-field="{{ $candidateTable }}.gender">
            <option value="">&nbsp;</option>
            <option value="{{ Candidate::GENDER_MALE }}">{{ trans('resource::view.Male') }}</option>
            <option value="{{ Candidate::GENDER_FEMALE }}">{{ trans('resource::view.Female') }}</option>
        </select>
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="language" class="col-sm-3 control-label">{{ trans('resource::view.Language') }}</label>
    <div class="col-sm-3 compare-item" data-field="language">
        @include('resource::candidate.include.compare_list', ['greaterSmaller' => false, 'like' => false])
    </div>
    <div class="col-sm-3">
        <select class="form-control select-search filter-item pull-left" id="language" data-compare='{{ Candidate::COMPARE_EQUAL }}'
                data-field="language"
                data-except="except">
            <option value="">&nbsp;</option>
            @foreach($langs as $lang)
            <option value="{{ $lang->id }}">
                {{ $lang->name }}
            </option>
            @endforeach
        </select>
    </div>
    <div class="col-sm-3">
        <select class="form-control pull-right extra-item lang-level hidden" 
                data-field="language"
                data-field-extra="{{$candidateLangtable}}.lang_level_id">
            <option value="">&nbsp;</option>
        </select>
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="program" class="col-sm-3 control-label">{{ trans('resource::view.Pro lang') }}</label>
    <div class="col-sm-3 compare-item" data-field="program">
        @include('resource::candidate.include.compare_list', ['greaterSmaller' => false, 'like' => false])
    </div>
    <div class="col-sm-6">
        <select class="form-control select-search filter-item" id="program" 
                data-field="program"
                data-except="except">
            <option value="">&nbsp;</option>
            @foreach($programs as $lang)
            <option value="{{ $lang->id }}">
                {{ $lang->name }}
            </option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label class="col-sm-3 control-label">{{ trans('resource::view.Group') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{$candidateTable}}.team_id">
        @include('resource::candidate.include.compare_list', ['greaterSmaller' => false, 'like' => false])
    </div>
    <div class="col-sm-6">
        <select class="form-control select-search filter-item" id="group" data-compare='{{ Candidate::COMPARE_EQUAL }}'
                data-field="{{$candidateTable}}.team_id">
            <option value="">&nbsp;</option>
            @foreach($teamsOptionAll as $option)
            <option value="{{ $option['value'] }}">
                {{ $option['label'] }}
            </option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group col-sm-12 hidden">
    <label for="working_type" class="col-sm-3 control-label">{{ trans('resource::view.Working type') }}</label>
    <div class="col-sm-3 compare-item" data-field="{{ $candidateTable }}.working_type">
        @include('resource::candidate.include.compare_list', ['greaterSmaller' => false, 'like' => false])
    </div>
    <div class="col-sm-6">
        <select class="form-control filter-item select-search" id="working_type" 
                data-field="{{ $candidateTable }}.working_type">
            <option value="0">{{ trans('resource::view.No contract') }}</option>
            @foreach ($workingtypeOptions as $option)
                <option value="{{ $option['id'] }}">{{ $option['name'] }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="col-md-12">
    <div class="col-sm-6 col-md-offset-6">
        <button class="btn btn-primary pull-right filter">{{ trans('team::view.Search') }} <i class="fa fa-spin fa-refresh hidden"></i></button>
    </div>
</div>
</form>
