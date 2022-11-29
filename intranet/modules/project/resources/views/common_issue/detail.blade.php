<?php

use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\Employee;
use Rikkei\Project\Model\Risk;
?>
@extends('layouts.default')
@section('title')
    {{ trans('project::view.LBL_COMMON_ISSUE_DETAIL') }}
@endsection
@section('content')
    <div class="css-create-page request-create-page request-detail-page word-break">
        <div class="css-create-body candidate-detail-page">
            @include('project::components.common_issue_detail', ['btnSave' => true])
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
    <style>
        .mentions-input-box .mentions > div {
            font-size: 14px;
        }
        .mentions-input-box .mentions > div > strong {
            font-weight: normal;
            background: #d8dfea;
            font-size: 14px;
        }
    </style>
@endsection
@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
    <script src="{{ URL::asset('lib/js/moment.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
    <script>
        var token = '{{ csrf_token() }}';
        $(document).ready(function () {
            $('.modal.risk-dialog').removeAttr('tabindex').css('overflow', 'hidden');

            function resizeModal(element, heightBrowser) {
                $(element).css({
                    'height':  heightBrowser,
                    'overflow-y': 'scroll'
                });
            }
        });
    </script>
@endsection