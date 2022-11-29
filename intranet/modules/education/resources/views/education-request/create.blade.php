<?php
use Rikkei\Core\View\CoreUrl;
use Rikkei\Education\Model\EducationRequest;
?>
@extends('layouts.default')
@section('title')
    {{ $titlePage }}
@endsection

@section('content')
    <?php
    $disable = false;
    $statusPending = EducationRequest::STATUS_PENDING;
    $statusReject = EducationRequest::STATUS_REJECT;
    $statusRequest = EducationRequest::STATUS_REQUESTING;
    // Check dlead and status != 3 or not is self created
    if(!$isScopeHrOrCompany && $education->status != $statusRequest || !$isSelfCreated) {
        $disable = true;
    }

    // Check hr and is self created or action is create or permission and not hr with status 3
    if($isScopeHrOrCompany && $isSelfCreated || $action == 'create' || $isAvailableTeamId && !$isScopeHrOrCompany && $education->status == $statusRequest) {
        $disable = false;
    }
    ?>
    <div class="box box-primary education-request">
        <div class="education-request-body margin-top-20">
            <div class="container-fluid">
                <div class="row">
                    <div class="form-horizontal col-md-12">
                        <form id="frm_create_education" method="post"
                            @if($isScopeHrOrCompany)
                            action="{{ URL::route('education::education.request.hr.store') }}"
                            @else
                            action="{{ URL::route('education::education.request.store') }}"
                            @endif>
                            @include('education::education-request._form')
                            <div class="row btn-container">
                                <div class="col-md-12 text-align-center margin-bottom-30" >
                                    <button class="btn btn-primary btn-create" type="submit">
                                        {{ trans('education::view.Register') }}
                                        <i class="fa fa-spin fa-refresh hidden"></i>
                                    </button>
                                    <a class="btn btn-primary" href="{{ $isScopeHrOrCompany ? route('education::education.request.hr.list') : route('education::education.request.list') }}">
                                        {{ trans('education::view.Education.Cancel') }}
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input id="token" type="hidden" value="{{ Session::token() }}" />
    <!-- Check value if press back button then reload page -->
    <input type="hidden" id="refreshed" value="no">
@endsection

<!-- Styles -->
@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/minimal/_all.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" >
    <link href="{{ asset('team/css/style.css') }}" rel="stylesheet" type="text/css" >
    <link rel="stylesheet" href="{{ asset('education/css/education.css') }}" rel="stylesheet" type="text/css" >
    <link rel="stylesheet" href="{{ asset('education/css/education-request.css') }}" rel="stylesheet" type="text/css" >
@endsection

<!-- Script -->
@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/icheck.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
    <script src="{{ asset('asset_managetime/js/script.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script>
        var teamPath = JSON.parse('{!! json_encode($teamPath) !!}');
        var messageError = "{{ trans('core::message.This field is required') }}";
        var statusPending = '{{ $statusPending }}';
        var statusReject = '{{ $statusReject }}';
        var assignId = "{{ old('assign_id') }}";
    </script>
    <script src="{{ CoreUrl::asset('education/js/education_request_create.js') }}"></script>
    <script src="{{ CoreUrl::asset('education/js/team_scope.js') }}"></script>
@endsection
