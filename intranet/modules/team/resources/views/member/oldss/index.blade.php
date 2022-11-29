<?php
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\EmplCvAttrValue;
use Rikkei\Core\View\CoreUrl;

if ($isAccess) {
    $flagEditable = '<span class="flg-editable"></span>';
} else {
    $flagEditable = '';
}
?>
@extends('layouts.default')

@section('body_class', 'page-cv')

@section('title')
{{ trans('team::view.Skill sheet of :employeeName', ['employeeName' => $employeeModelItem->name]) }}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/3.5.4/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/3.5.4/select2-bootstrap.min.css" />
<link rel="stylesheet" href="{{ URL::asset('team/css/style.css') }}" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/css/bootstrap-datepicker3.min.css" />
<link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet"/>
@endsection

@section('content')
<form action="{!!route('team::member.profile.save', ['employeeId' => $employeeModelItem->id, 'type' => $tabType])!!}"
    autocomplete="off" method="post" id="form-employee-cv"
    data-form-submit="ajax">
    {!! csrf_field() !!}
<div class="row member-cv">
    <!-- Right column-->
    <!-- Edit form -->
    <div class="col-md-12 tab-content">
        <div class="box box-info tab-pane active">
            <div class="row sks-header">
                <div class="col-md-8">
                    <ol class="steps-ui">
                        <li{!! $employeeCvEav->getVal('status') == EmplCvAttrValue::STATUS_SAVE ? ' class="current"' : '' !!}><strong>Saved</strong></li>
                        <li{!! $employeeCvEav->getVal('status') == EmplCvAttrValue::STATUS_SUBMIT ? ' class="current"' : '' !!}><strong>Submitted</strong></li>
                        <li{!! $employeeCvEav->getVal('status') == EmplCvAttrValue::STATUS_APPROVE ? ' class="current"' : '' !!}><strong>Approved</strong></li>
                    </ol>
                    <div class="multi-lang">
                        <div class="btn-group" data-toggle="buttons">
                            <label class="btn btn-default active form-check-label">
                                <input value="en" class="form-check-input" type="radio" name="cv_view_lang" autocomplete="off"> EN
                            </label>
                            <label class="btn btn-default form-check-label">
                                <input value="ja" class="form-check-input" type="radio" name="cv_view_lang" autocomplete="off"> JP
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 margin-top-10">
                    <div class="pull-right margin-right-10">
                        @if ($isAccess)
                            <button type="button" class="btn btn-primary" data-btn-submit="1">Save</button>
                            <button type="button" class="btn btn-primary" data-btn-submit="2">Submit</button>
                        @endif
                        @if ($isAccessTeamEdit)
                            <button type="button" class="btn btn-success" data-btn-submit="3">Approve</button>
                        @endif
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="box-header with-border text-center">
                        <h2 class="box-title" data-lang-r="cv title"></h2>
                    </div>
                </div>
            </div>
            <div class="box-body row">
                <div class="col-md-9 cv-left" data-tbl-res="left">
                    <div class="table-responsive">
                        @include('team::member.ss.baseinfo')
                    </div>
                    <div class="table-responsive">
                        @include ('team::member.ss.project')
                    </div>
                </div>
                <div class="col-md-3 cv-right" data-tbl-res="right">
                    <div class="table-responsive">
                        <!-- experience skill -->
                        @include ('team::member.ss.skill')
                        <!-- end experience skill -->
                    </div>
                </div>
            </div>
            <div class="box-footer">
            <div class="row">
                <div class="col-md-12">
                    <p><strong>{!!trans('team::cv.Note')!!}</strong></p>
                    {!!trans('team::profile.skill sheet guide')!!}
                </div>
            </div>
        </div>
        </div>
    </div>
</div>
</form>
@endsection

@section('script')
<script>
    var globalPassModule = {
        tabType: '{!!$tabType!!}',
        isSelfProfile: {!!$isSelfProfile ? 1 : 0!!},
        urlSaveCv: '{!!route('team::member.profile.save', ['employeeId' => $employeeModelItem->id, 'type' => $tabType, 'typeId' => 0])!!}',
        urlRemoteos: '{!!route('tag::search.tag.select2', ['fieldCode' => 'os'])!!}',
        urlRemotelang: '{!!route('tag::search.tag.select2', ['fieldCode' => 'language-database'])!!}',
        urlRemotelanguage: '{!!route('tag::search.tag.select2', ['fieldCode' => 'language'])!!}',
        urlRemotedatabase: '{!!route('tag::search.tag.select2', ['fieldCode' => 'database'])!!}',
        isAccess: {!!(int) $isAccess!!},
        tagData: {!!json_encode($tagData)!!},
    },
    globalTrans = {
        en: {!!json_encode(trans('team::cv', [], '', 'en'))!!},
        ja: {!!json_encode(trans('team::cv', [], '', 'ja'))!!},
        vi: {!!json_encode(trans('team::cv', [], '', 'vi'))!!},
    },
    globalDbTrans = {!!json_encode($employeeCvEav->eav)!!};
</script>
<script src="{{ URL::asset('lib/js/moment.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/3.5.4/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
<script src="{{ asset('lib/x-editable-1.15.1/bootstrap-editable.min.js') }}"></script>
<script src="{{ CoreUrl::asset('common/js/methods.validate.js') }}"></script>
<script src="{{ CoreUrl::asset('team/js/ss.js') }}"></script>
@endsection
