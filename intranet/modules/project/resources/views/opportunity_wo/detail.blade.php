<?php
use Rikkei\Team\Model\Employee;
?>

@extends('layouts.default')
@section('title')
    Opportunity detail {{ $projectData ? ' - Project: '. $projectData->name .' (PM: '.Employee::getNickNameById($projectData->manager_id). ')' : '' }}
@endsection

@section('content')
    <div class="css-create-page request-create-page request-detail-page word-break">
        <div class="css-create-body candidate-detail-page">
            @include('project::components.opportunity-detail', ['btnSave' => true])
        </div>
    </div>
    <!-- /.row -->
@endsection
@section('css')
    <meta name="_token" content="{{ csrf_token() }}"/>
    <link href="{{ asset('sales/css/sales.css') }}" rel="stylesheet" type="text/css" >
    <link rel="stylesheet" href="{{ URL::asset('project/css/edit.css') }}" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css" />
@endsection
@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
    <script src="{{ URL::asset('lib/js/moment.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
@endsection