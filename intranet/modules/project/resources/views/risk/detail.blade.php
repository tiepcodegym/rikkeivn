<?php
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\Employee;
use Rikkei\Project\Model\Risk;

if (isset($riskInfo) && $riskInfo) {
    $checkEdit = true;
    $urlSubmit = route('project::wo.saveRisk', ['riskId' => $riskInfo->id]);
    $urlDetailRisk = route('project::report.risk.detail', ['riskId' => $riskInfo->id]);
} else {
    $checkEdit = false;
    $urlSubmit = route('project::wo.saveRisk');
}
?>

@extends('layouts.default')
@section('title')
    {{ trans('project::view.Risk detail') }}  {{ ($riskInfo) ? ' - Project: '. $riskInfo->project_name .' (PM: '.Employee::getNickNameById($riskInfo->manager_id). ')' : '' }}
@endsection
@section('content')

<div class="css-create-page request-create-page request-detail-page word-break">
    <div class="css-create-body candidate-detail-page">
        @include('project::components.risk-detail', ['btnSave' => true])
        @include('project::components.risk-comment')
    </div>
</div>
<!-- /.row -->
@endsection
<!-- Styles -->
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
    #comment{
        font-size: 14px;
    }
</style>
@endsection

<!-- Script -->
@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="{{ URL::asset('lib/js/moment.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
<script>
    var requiredText = '{{trans("project::view.This field is required.")}}';
    var statusOpen = {{ Risk::STATUS_OPEN }};
    var statusHappen = {{ Risk::STATUS_HAPPEN }};
    var statusClosed = {{ Risk::STATUS_CLOSED }};
    var token = '{{ csrf_token() }}';

    @if ($checkEdit)
        var riskStatus = {{ $riskInfo->status }};
        var riskId = {{ $riskInfo->id }};
        $('.select-risk-status').select2({minimumResultsForSearch: -1});
        $('.select-status-container .select2-selection').css({
            'height' : "45px",
            'font-size' : "25px",
            'padding-top' : '14px',
        });
        $('.select-status-container .select2-selection__arrow').css('height', '48px');
    @endif

    jQuery(document).ready(function() {
        $('.mitigation-box .row-mitigation').find('#task-assignee').each(function() {
            RKfuncion.select2.elementRemote(
                $(this)
            );
        });
        $(document).on('click', '.btn-delete', function() {
            $(this).closest('.row-mitigation').remove();
        });

        $('.contigency-box .row-contigency').find('#contigency-assignee').each(function() {
            RKfuncion.select2.elementRemote(
                $(this)
            );
        });
        $(document).on('click', '.btn-delete', function() {
            $(this).closest('.row-contigency').remove();
        });
        $(".form-riks-detail").validate({
            errorPlacement: function(error, element) {
                if (element.attr("name") == "team_owner" ) {
                    $("#error-team-owner").html( error );
                } else {
                    error.insertAfter(element);
                }
            },
            rules: {
                content: 'required',
                weakness: 'required',
                level_important: 'required',
                team_owner:{required: function(){
                    if($('#owner').val() === "")
                        return true;
                    else
                        return false;
                   }
                },
                type: 'required',
                posibility_using: 'required',
                impact_using: 'required',
                source: 'required',
                trigger: 'required',
                due_date: 'required',
            },
            messages: {
                content: requiredText,
                weakness: requiredText,
                level_important: requiredText,
                team_owner: requiredText,
                due_date: requiredText,
            },
        });
        RKfuncion.select2.elementRemote(
            $('#performer')
        );
        RKfuncion.select2.elementRemote(
            $('#tester')
        );
        RKfuncion.select2.elementRemote(
            $('#owner')
        );
        RKfuncion.select2.elementRemote(
            $('#team_owner')
        );
        $('.modal.risk-dialog').removeAttr('tabindex').css('overflow', 'hidden');
        var heightBrowser = $(window).height() - 200;
        resizeModal('.modal.risk-dialog .modal-body', heightBrowser);
        @if(!isset($permissionEdit) || !$permissionEdit)
            $('.modal #btn-save').remove();
            $('textarea, input, select').prop('disabled', true);
        @endif
    });

    $(window).resize(function() {
        var heightBrowser = $(window).height() - 200;
        resizeModal('.modal.risk-dialog .modal-body', heightBrowser);
    });

    function resizeModal(element, heightBrowser) {
        $(element).css({
            'height':  heightBrowser,
            'overflow-y': 'scroll'
        });
    }
</script>
@endsection
