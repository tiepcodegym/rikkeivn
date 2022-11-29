<?php

use Rikkei\Core\View\View;
use Rikkei\Project\Model\CommonRisk;
use Rikkei\Project\Model\Risk;

if (isset($riskInfo) && $riskInfo) {
    $checkEdit = true;
    $urlSubmit = route('project::commonRisk.save', ['riskId' => $riskInfo->id]);
    $urlDetailRisk = route('project::commonRisk.detail', ['riskId' => $riskInfo->id]);
} else {
    $checkEdit = false;
    $urlSubmit = route('project::commonRisk.save');
}
?>

<form class="form-horizontal form-common-risk-detail" method="post" autocomplete="off" action="{{$urlSubmit}}"
      enctype="multipart/form-data">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    @if ($checkEdit)
        <input type="hidden" id="id" name="id" value="{{ $riskInfo->id }}"/>
    @endif
    @if (!empty($redirectUrl))
        <input type="hidden" name="redirectUrl" value="{{ $redirectUrl }}">
    @endif
    @if (!empty($urlDetailRisk))
        <input type="hidden" name="redirectDetailRisk" value="{{ $urlDetailRisk }}"/>
    @endif
    <div class="box box-info">
        <div class="box-header with-border">
            <h3 class="box-title">{{ trans('project::view.General information') }}</h3>
        </div>
        <div class="box-body">
            <!-- ROW 1 -->
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="content"
                           class="col-sm-3 control-label">{{ trans('project::view.LBL_RISK_DESCRIPTION') }}<em
                                class="required" aria-required="true">*</em></label>
                    <div class="col-sm-9">
                        <textarea class="form-control" id="risk_description" name="risk_description"
                                  placeholder="{{ trans('project::view.LBL_RISK_DESCRIPTION') }}"
                                  @if(isset($permissionEdit) && !$permissionEdit) disabled @endif >@if ($checkEdit){!!$riskInfo->risk_description!!}@endif</textarea>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label for="suggest_action"
                           class="col-sm-3 control-label">{{ trans('project::view.LBL_SUGGEST_ACTION') }}<em
                                class="required" aria-required="true">*</em></label>
                    <div class="col-sm-9">
                        <textarea class="form-control" id="suggest_action" name="suggest_action"
                                  placeholder="{{ trans('project::view.LBL_SUGGEST_ACTION') }}"
                                  @if(isset($permissionEdit) && !$permissionEdit) disabled @endif >@if ($checkEdit){!!$riskInfo->suggest_action!!}@endif</textarea>
                    </div>
                </div>
            </div>
            <!-- ROW 2 -->
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="impact_using" class="col-sm-3 control-label">{{ trans('project::view.Process') }}<em
                                class="required" aria-required="true">*</em></label>
                    <div class="col-sm-9">
                        <select class="form-control" id="process" name="process"
                                @if(isset($permissionEdit) && !$permissionEdit) disabled @endif >
                            <option value=""></option>
                            @foreach (CommonRisk::getSourceListProcess() as $keyLevel => $valueLevel)
                                <option value="{{ $keyLevel }}">{{ $valueLevel }}</option>
                                @if ($checkEdit && $riskInfo->process)
                                    <option value="{{$riskInfo->process}}"
                                            selected>{{CommonRisk::getSourceListProcess()[$riskInfo->process]}}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label for="risk_source" class="col-sm-3 control-label">{{ trans('project::view.Risk Source') }}<em
                                class="required" aria-required="true">*</em></label>
                    <div class="col-sm-9">
                        <select class="form-control" id="risk_source" name="risk_source"
                                @if(isset($permissionEdit) && !$permissionEdit) disabled @endif>
                            <option value=""></option>
                            @foreach (Risk::getSourceList() as $keyRiskSource => $valueRiskSource)
                                <option value="{{ $keyRiskSource }}"
                                        @if ($checkEdit && $riskInfo->risk_source == $keyRiskSource) selected @endif
                                >{{ $valueRiskSource }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="box box-info">
        <div class="box-body">
            <div class="row">
                <div class="align-center">
                    @if (isset($permissionEdit) && $permissionEdit && isset($btnSave))
                        <a type="button" href="{{ route('project::report.common-risk') }}"
                           class="btn btn-primary">{{trans('project::view.Back')}}</a>
                        <button type="submit" class="btn-add">
                            {{trans('project::view.Save')}}
                            <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</form>

<script type="text/javascript">
    var riskId = '{{ isset($riskInfo) ? $riskInfo->id : '' }}';
    var token = '{{ csrf_token() }}';
    var requiredText = '{{ trans("project::view.This field is required.") }}';

    $(document).ready(function () {
        $(".form-common-risk-detail").validate({
            errorPlacement: function (error, element) {
                error.insertAfter(element);
            },
            rules: {
                risk_description: 'required',
                suggest_action: 'required',
                risk_source: 'required',
                process: 'required',
            },
            messages: {
                risk_description: requiredText,
                suggest_action: requiredText,
                risk_source: requiredText,
                process: requiredText,
            },
        });
        $('.modal.risk-dialog').removeAttr('tabindex').css('overflow', 'hidden');
        var heightBrowser = $(window).height() - 200;
        resizeModal('.modal.risk-dialog .modal-body', heightBrowser);

        $(window).resize(function () {
            var heightBrowser = $(window).height() - 200;
            resizeModal('.modal.risk-dialog .modal-body', heightBrowser);
        });
    });

    function resizeModal(element, heightBrowser) {
        $(element).css({
            'height': heightBrowser,
            'overflow-y': 'scroll'
        });
    }


</script>
