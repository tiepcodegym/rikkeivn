@extends('layouts.default')

@section('title', trans('resource::view.Recruitment statistics'))

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="{{ URL::asset('resource/css/recruit.css') }}" />
@endsection

@section('content')
<?php
use Rikkei\Team\View\Permission;
?>
<div class="box box-info">
    <div class="box-body">
        {!! Form::open(['method' => 'get', 'route' => 'resource::candidate.indexCandidate', 'class' => 'no-validate']) !!}
        <div class="row recruiter-select">
            <div class="col-md-3">           
                <label class="year-label col-md-3 margin-top-5 bold-label">{{ trans('resource::view.Year') }}</label>
                <div class="col-md-6">
                    <select class="form-control select-search has-search" name="year" id="rc_year">
                        <option value="">--{{ trans('resource::view.Choose year') }}--</option>
                        @for($i = $rangeYears[0]; $i <= $rangeYears[1]; $i++)
                        <option value="{{ $i }}" {{ $i == $currYear ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
            </div>
            @if(Permission::getInstance()->isScopeTeam(null, 'resource::candidate.indexCandidate') 
            || Permission::getInstance()->isScopeCompany(null, 'resource::candidate.indexCandidate'))
            <div class="col-md-6">
                <label for="name" class="recruiter-label col-md-3 control-label margin-top-5 bold-label">{{trans('resource::view.Candidate.Recruiter')}}</label>
                <div class="col-md-5">
                    <select id="recruiter" name="recruiter" class="form-control select2-hidden-accessible">
                        <option value="">--{{trans('resource::view.Candidate.Create.Select recruiter')}}--</option>
                        <option value="{{trans('resource::view.Candidate.Create.All recruiters')}}" {{ !$recruiter ? 'selected' : '' }}>{{trans('resource::view.Candidate.Create.All recruiters')}}</option>
                        @foreach ($hrAccounts as $nickname => $email)
                        <option value="{{$email}}" {{ $email == $recruiter ? 'selected' : '' }}>{{$email}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @endif
        </div>
        {!! Form::close() !!}    
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">                
                <table class="table table-striped dataTable table-bordered table-hover table-grid-data statistics-table">
                    <thead>
                        <tr class="bg-light-blue">
                            <th rowspan="2" style="vertical-align: middle">{{ trans('resource::view.Month') }}</th>
                            <th rowspan="2" style="vertical-align: middle">{{ trans('resource::view.CV') }}</th>
                            <th colspan="5">{{ trans('resource::view.Test') }}</th>
                            <th colspan="5">{{ trans('resource::view.Interview') }}</th>
                            <th colspan="4">{{ trans('resource::view.Offer') }}</th>                      
                        </tr>
                        <tr class="bg-light-blue">                    
                            <th>{{ trans('resource::view.Candidate.Detail.Plan') }}</th>
                            <th>{{ trans('resource::view.Candidate.Detail.Wait') }}</th>
                            <th>{{ trans('resource::view.Candidate.Detail.Absence') }}</th>
                            <th>{{ trans('resource::view.Candidate.Detail.Pass') }}</th>
                            <th>{{ trans('resource::view.Candidate.Detail.Fail') }}</th>
                            <th>{{ trans('resource::view.Candidate.Detail.Plan') }}</th>
                            <th>{{ trans('resource::view.Candidate.Detail.Wait') }}</th>
                            <th>{{ trans('resource::view.Candidate.Detail.Absence') }}</th>
                            <th>{{ trans('resource::view.Candidate.Detail.Pass') }}</th>
                            <th>{{ trans('resource::view.Candidate.Detail.Fail') }}</th>
                            <th>{{ trans('resource::view.Candidate.Detail.Plan') }}</th>
                            <th>{{ trans('resource::view.Candidate.Detail.Wait') }}</th>
                            <th>{{ trans('resource::view.Candidate.Detail.Pass') }}</th>
                            <th>{{ trans('resource::view.Candidate.Detail.Fail') }}</th>
                        </tr>
                    </thead>
                    <tbody>     
                        <?php
                        $sumCV = 0;
                        $sumTestPlan = 0;
                        $sumTestWait = 0;
                        $sumTestAbsence = 0;
                        $sumTestFail = 0;
                        $sumTestPass = 0;
                        $sumInterviewPlan = 0;
                        $sumInterviewWait = 0;
                        $sumInterviewAbsence = 0;
                        $sumInterviewFail = 0;
                        $sumInterviewPass = 0;
                        $sumOfferPlan = 0;
                        $sumOfferWait = 0;
                        $sumOfferFail = 0;
                        $sumOfferPass = 0;
                        ?>
                        @for ($month = 1; $month <= 12; $month++)
                        <?php
                        $cvMonth = isset($cvReceived[$month]) ? $cvReceived[$month] : 0;
                        $sumCV += $cvMonth;

                        $testResultsWait = isset($testResults[$month]) ? $testResults[$month]['WaitCount'] : 0;
                        $sumTestWait += $testResultsWait;
                        $testResultsAbsence = isset($testResults[$month]) ? $testResults[$month]['AbsenceCount'] : 0;
                        $sumTestAbsence += $testResultsAbsence;
                        $testResultsPass = isset($testResults[$month]) ? $testResults[$month]['PassCount'] : 0;
                        $sumTestPass += $testResultsPass;
                        $testResultsFail = isset($testResults[$month]) ? $testResults[$month]['FailCount'] : 0;
                        $sumTestFail += $testResultsFail;
                        $testResultsPlan = $testResultsFail + $testResultsPass + $testResultsWait + $testResultsAbsence;
                        $sumTestPlan += $testResultsPlan;

                        $interviewResultsWait = isset($interviewResults[$month]) ? $interviewResults[$month]['WaitCount'] : 0;
                        $sumInterviewWait += $interviewResultsWait;
                        $interviewResultsAbsence = isset($interviewResults[$month]) ? $interviewResults[$month]['AbsenceCount'] : 0;
                        $sumInterviewAbsence += $interviewResultsAbsence;
                        $interviewResultsPass = isset($interviewResults[$month]) ? $interviewResults[$month]['PassCount'] : 0;
                        $sumInterviewPass += $interviewResultsPass;
                        $interviewResultsFail = isset($interviewResults[$month]) ? $interviewResults[$month]['FailCount'] : 0;
                        $sumInterviewFail += $interviewResultsFail;
                        $interviewResultsPlan = $interviewResultsFail + $interviewResultsPass + $interviewResultsWait + $interviewResultsAbsence;
                        $sumInterviewPlan += $interviewResultsPlan;

                        $offerResultsWait = isset($offerResults[$month]) ? $offerResults[$month]['WaitCount'] : 0;
                        $sumOfferWait += $offerResultsWait;
                        $offerResultsPass = isset($offerResults[$month]) ? $offerResults[$month]['PassCount'] : 0;
                        $sumOfferPass += $offerResultsPass;
                        $offerResultsFail = isset($offerResults[$month]) ? $offerResults[$month]['FailCount'] : 0;
                        $sumOfferFail += $offerResultsFail;
                        $offerResultsPlan = $offerResultsFail + $offerResultsPass + $offerResultsWait;
                        $sumOfferPlan += $offerResultsPlan;
                        ?>
                        <tr>
                            <td class="text-center">{{ $month }}</td>
                            <td class="text-center">{{ $cvMonth }}</td>
                            <td class="text-center">{{ $testResultsPlan }}</td>
                            <td class="text-center">{{ $testResultsWait }}</td>
                            <td class="text-center">{{ $testResultsAbsence }}</td>
                            <td class="text-center">{{ $testResultsPass }}</td>
                            <td class="text-center">{{ $testResultsFail }}</td>
                            <td class="text-center">{{ $interviewResultsPlan }}</td>
                            <td class="text-center">{{ $interviewResultsWait }}</td>
                            <td class="text-center">{{ $interviewResultsAbsence }}</td>
                            <td class="text-center">{{ $interviewResultsPass }}</td>
                            <td class="text-center">{{ $interviewResultsFail }}</td>
                            <td class="text-center">{{ $offerResultsPlan }}</td>
                            <td class="text-center">{{ $offerResultsWait }}</td>
                            <td class="text-center">{{ $offerResultsPass }}</td>
                            <td class="text-center">{{ $offerResultsFail }}</td>
                        </tr>  
                        @endfor
                    </tbody>
                    <tfoot>
                        <tr class="bg-light-blue">
                            <th>{{ trans('resource::view.Sum') }}</th>
                            <td class="text-center">{{ $sumCV }}</td>
                            <td class="text-center">{{ $sumTestPlan }}</td>
                            <td class="text-center">{{ $sumTestWait }}</td>
                            <td class="text-center">{{ $sumTestAbsence }}</td>
                            <td class="text-center">{{ $sumTestPass }}</td>
                            <td class="text-center">{{ $sumTestFail }}</td>
                            <td class="text-center">{{ $sumInterviewPlan }}</td>
                            <td class="text-center">{{ $sumInterviewWait }}</td>
                            <td class="text-center">{{ $sumInterviewAbsence }}</td>
                            <td class="text-center">{{ $sumInterviewPass }}</td>
                            <td class="text-center">{{ $sumInterviewFail }}</td>
                            <td class="text-center">{{ $sumOfferPlan }}</td>
                            <td class="text-center">{{ $sumOfferWait }}</td>
                            <td class="text-center">{{ $sumOfferPass }}</td>
                            <td class="text-center">{{ $sumOfferFail }}</td>
                        </tr>                   
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')

@include('resource::recruit.script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="{{ URL::asset('lib/chartjs/Chart_2.5_.min.js') }}"></script>
<script src="{{ URL::asset('resource/js/recruit/index.js') }}"></script>
@endsection
