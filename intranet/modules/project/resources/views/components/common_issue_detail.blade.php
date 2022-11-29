<?php

use Rikkei\Project\Model\Project;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\View\View;
use Rikkei\Project\Model\CommonRisk;
use Rikkei\Project\Model\Task;
use Rikkei\Project\Model\Risk;
use Rikkei\Project\Model\RiskAction;

if (isset($issueInfo) && $issueInfo) {
    $checkEdit = true;
    $urlSubmit = route('project::commonIssue.save', ['riskId' => $issueInfo->id]);
    $urlDetail = route('project::commonIssue.detail', ['riskId' => $issueInfo->id]);
} else {
    $checkEdit = false;
    $urlSubmit = route('project::commonIssue.save');
}

?>

<form class="form-horizontal form-common-issue-detail" method="post" autocomplete="off" action="{{$urlSubmit}}"
      enctype="multipart/form-data">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    @if ($checkEdit)
        <input type="hidden" id="id" name="id" value="{{ $issueInfo->id }}"/>
    @endif
    @if (!empty($redirectUrl))
        <input type="hidden" name="redirectUrl" value="{{ $redirectUrl }}">
    @endif
    @if (!empty($urlDetail))
        <input type="hidden" name="redirectDetail" value="{{ $urlDetail }}"/>
    @endif
    <div class="box box-info">
        <div class="box-header with-border">
            <h3 class="box-title">{{ trans('project::view.General information') }}</h3>
        </div>
        <div class="box-body">
            <!-- ROW 1 -->
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="content" class="col-sm-3 control-label">{{ trans('project::view.Issue Type') }}<em
                                class="required" aria-required="true">*</em></label>
                    <div class="col-sm-9">
                        <select class="form-control" id="issue_type" name="issue_type"
                                @if(isset($permissionEdit) && !$permissionEdit) disabled @endif>
                            <option value="">&nbsp;</option>
                            @foreach(Task::typeLabelForIssue() as $key => $value)
                                <option value="{{ $key }}"
                                        @if ($checkEdit && $issueInfo->issue_type == $key) selected @endif >{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label for="suggest_action"
                           class="col-sm-3 control-label">{{ trans('project::view.LBL_ISSUE_SOURCE') }}<em
                                class="required" aria-required="true">*</em></label>
                    <div class="col-sm-9">
                        <select class="form-control" id="issue_source" name="issue_source"
                                @if(isset($permissionEdit) && !$permissionEdit) disabled @endif>
                            <option value=""></option>
                            @foreach (Risk::getSourceList() as $keyIssueSource => $valueIssueSource)
                                <option value="{{ $keyIssueSource }}"
                                        @if ($checkEdit && $issueInfo->issue_source == $keyIssueSource) selected @endif
                                >{{ $valueIssueSource }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <!-- ROW 2 -->
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="impact_using"
                           class="col-sm-3 control-label">{{ trans('project::view.LBL_ISSUE_DESCRIPTION') }}<em
                                class="required" aria-required="true">*</em></label>
                    <div class="col-sm-9">
                        <textarea class="form-control" id="issue_description" name="issue_description"
                                  placeholder="{{ trans('project::view.LBL_ISSUE_DESCRIPTION') }}"
                                  @if(isset($permissionEdit) && !$permissionEdit) disabled @endif>@if ($checkEdit){!!$issueInfo->issue_description!!}@endif</textarea>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label for="risk_source" class="col-sm-3 control-label">{{ trans('project::view.LBL_CAUSE') }}<em
                                class="required" aria-required="true">*</em></label>
                    <div class="col-sm-9">
                        <textarea class="form-control" id="cause" name="cause"
                                  placeholder="{{ trans('project::view.LBL_CAUSE') }}"
                                  @if(isset($permissionEdit) && !$permissionEdit) disabled @endif>@if ($checkEdit){!!$issueInfo->cause!!}@endif</textarea>
                    </div>
                </div>
            </div>
            <!-- ROW 3 -->
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="impact_using" class="col-sm-3 control-label">{{ trans('project::view.Action') }}<em
                                class="required" aria-required="true">*</em></label>
                    <div class="col-sm-9">
                        <textarea class="form-control" id="action" name="action"
                                  placeholder="{{ trans('project::view.Action') }}"
                                  @if(isset($permissionEdit) && !$permissionEdit) disabled @endif>@if ($checkEdit){!!$issueInfo->action!!}@endif</textarea>
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
                        <a type="button" href="{{ route('project::report.common-issue') }}"
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
<script type="text/javascript">
    var riskId = '{{ isset($issueInfo) ? $issueInfo->id : '' }}';
    var token = '{{ csrf_token() }}';
    var requiredText = '{{ trans("project::view.This field is required.") }}';

    $(document).ready(function () {
        $(".form-common-issue-detail").validate({
            errorPlacement: function (error, element) {
                error.insertAfter(element);
            },
            rules: {
                issue_type: 'required',
                issue_source: 'required',
                issue_description: 'required',
                cause: 'required',
                action: 'required',
            },
            messages: {
                issue_type: requiredText,
                issue_source: requiredText,
                issue_description: requiredText,
                cause: requiredText,
                action: requiredText,
            },
        });
        $('.modal.risk-dialog').removeAttr('tabindex').css('overflow', 'hidden');
        var heightBrowser = $(window).height() - 200;
        resizeModal('.modal.risk-dialog .modal-body', heightBrowser);

        $(window).resize(function () {
            var heightBrowser = $(window).height() - 200;
            resizeModal('.modal.issue-dialog .modal-body', heightBrowser);
        });
    });

    function resizeModal(element, heightBrowser) {
        $(element).css({
            'height': heightBrowser,
            'overflow-y': 'scroll'
        });
    }


</script>
