@extends('layouts.default', ['createProject' => true])

@section('title')
    @if(isset($collectionModel) && $collectionModel->id)
        {{ trans('education::view.Teaching registration details') }}
    @else
        {{ trans('education::view.Register of teaching') }}
    @endif
@endsection
<?php
use Rikkei\Team\View\Config;
use Rikkei\Core\View\View;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Team\View\TeamList;
use Rikkei\Document\View\DocConst;
use Rikkei\Core\View\CookieCore;

$teamsOptionAll = TeamList::toOption(null, true, false);
$fileMaxSize = DocConst::fileMaxSize();

$dataCookie = CookieCore::getRaw('data');

CookieCore::forgetRaw('data');

if(isset($collectionModel) && $collectionModel->id) {
    $urlSubmit = route('education::education.teaching.teachings.update',['id' => $collectionModel->id]);
    $checkEdit = true;
} else {
    $urlSubmit = route('education::education.teaching.teachings.store');
    $checkEdit = false;
}
?>

@section('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" >
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css" />
    <link href="{{ asset('education/css/register-teaching.css') }}" rel="stylesheet" type="text/css" >
    <style>
        .btn-group, .btn-group-vertical {
            width: 100%;
        }
        .btn-group>.btn:first-child {
            width: 100%;
            text-align: left;
        }
        .multiselect-container {
            overflow: scroll;
            width: 100%;
            overflow-x: hidden;
            height: 200px;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-info">
                <div class="box-body">
                    <form method="post" action="{{$urlSubmit}}" enctype="multipart/form-data" autocomplete="off" class="form-horizontal" id="form-create-update">
                        {!! csrf_field() !!}
                        @if($checkEdit)
                            {{ method_field('put') }}
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-label-left">
                                    <label for="code" class="col-sm-3 control-label">
                                        {{ trans('education::view.Title') }}
                                        <em class="required">*</em></label>
                                    <div class="col-sm-9 dv-title">
                                        <input name="title" class="form-control input-field" type="text" id="title"
                                               placeholder="{{ trans('education::view.Title') }}" value="{{ old('title', $collectionModel->title) }}"/>
                                        @if($errors->has('title'))
                                            <label id="title-error" class="error" for="title">{{$errors->first('title')}}</label>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-label-left">
                                    <label for="scope" class="col-sm-3 control-label">
                                        {{ trans('education::view.Scope') }}
                                    </label>
                                    <div class="col-sm-9">
                                        <select name="scope" class="form-control" style="min-width: 230px;" id="education_type">
                                            @foreach($scopes as $key => $value)
                                                <option value="{{ $key }}"
                                                     @if (old('scope', $collectionModel->scope) == $key)
                                                         {{ 'selected' }}
                                                     @endif
                                                >{{ trans($value) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-label-left">
                                    <label for="name" class="col-md-3 control-label">
                                        {{ trans('education::view.Education.Team') }}
                                    </label>
                                    <div class="col-sm-9 filter-multi-select multi-select-style division select-full">
                                        <select name="teams[]" id="team_id_add" multiple
                                                class="form-control filter-grid select-multi" autocomplete="off">
                                            @foreach($teamsOptionAll as $option)
                                                <option class="js-team" value="{{ $option['value'] }}"
                                                        {{ (!empty($teamsSelected) && in_array($option['value'], $teamsSelected)) ? 'selected' : '' }}
                                                        {{ $option['option'] }}>{{ $option['label'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($errors->has('team_id_add'))
                                            <label class="error">{{$errors->first('team_id_add')}}</label>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-label-left">
                                    <label for="course_type_id" class="col-sm-3 control-label lbl-required">
                                        {{ trans('education::view.Education Types') }}
                                    </label>
                                    <div class="col-sm-9">
                                        <select name="course_type_id" class="form-control" id="course_type_id">
                                            @foreach($educationTypes as $key => $educationType)
                                                <option value="{{$key}}"
                                                    @if (old('course_type_id', $collectionModel->course_type_id) == $key)
                                                        {{ 'selected' }}
                                                    @endif
                                                >{{ $educationType }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
{{--                        <div class="row">--}}
{{--                            <div class="col-md-6">--}}
{{--                                <div class="form-group form-label-left">--}}
{{--                                    <label for="type" class="col-sm-3 control-label">--}}
{{--                                        {{ trans('education::view.Registration type') }}--}}
{{--                                    </label>--}}
{{--                                    <div class="col-sm-9">--}}
{{--                                        <label class="radio-inline"><input type="radio" id="register-type1" class="register-type" {{ $collectionModel->type == '1' ? 'checked' : '' }}  {{ old('type')=="1" ? 'checked='.'"'.'checked'.'"' : '' }} name="type" value="1">{{ trans('education::view.Course available') }}</label>--}}
{{--                                        <label class="radio-inline"><input type="radio" id="register-type2" class="register-type" {{ $collectionModel->type == '2' ? 'checked' : '' }} name="type" {{ old('type')=="2" ? 'checked='.'"'.'checked'.'"' : '' }} value="2">{{ trans('education::view.According to demand') }}</label>--}}
{{--                                        <div>--}}
{{--                                            <label id="radio-error" class="error"></label>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
                        <div class="row hidden" id="course">
                            <div class="col-md-6">
                                <div class="form-group form-label-left">
                                    <label for="course_id" class="col-sm-3 control-label">
                                        {{ trans('education::view.Course') }}
                                    </label>
                                    <div class="col-sm-9">
                                        <i class="fa fa-spin fa-refresh hidden" id="update_cate_loading"></i>
                                        <select name="course_id" class="form-control hidden" id="course_id">
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row hidden" id="class">
                            <div class="col-md-6">
                                <div class="form-group form-label-left">
                                    <label for="class_id" class="col-sm-3 control-label">
                                        {{ trans('education::view.Class') }}
                                    </label>
                                    <div class="col-sm-9">
                                        <i class="fa fa-spin fa-refresh hidden" id="update_class_loading"></i>
                                        <select name="class_id" class="form-control hidden" id="class_id">
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-label-left">
                                    <label for="time" class="col-sm-3 control-label lbl-required">
                                        {{ trans('education::view.Training time') }} <em class="required">*</em>
                                    </label>
                                    <div class="col-sm-9 hidden" id="detail-class">
                                        <i class="fa fa-spin fa-refresh hidden" id="update_detail_loading"></i>
                                        <div class="list-group detail-class">
                                        </div>
                                    </div>
                                    <div class="col-sm-9">
                                        <div class="list-group hidden detail_class_choose">
                                            <table class="table table-striped table-bordered table-hover table-grid-data table-grid-data" id="tblOperationBody">
                                                <tbody class="tblDetailInput">
                                                </tbody>
                                            </table>
                                            <div class="{{ $isShow ? 'hidden' : '' }}">
                                                <label class="error-input-mess"></label><br>
                                                <label><em class="required">*</em> {{ trans('education::view.Start time and end time in a day') }}</label><br>
                                                <a class="btn btn-success add-new"><i class="glyphicon glyphicon-plus"></i> Add new</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-label-left">
                                    <label for="time" class="col-sm-3 control-label lbl-required">
                                        {{ trans('education::view.Number of hours taught') }}
                                        <em class="required hidden">*</em>
                                    </label>
                                    <div class="col-sm-9">
                                        <input readonly oninput="validity.valid||(value='');" type="number" min="0" class="form-control tranning_hour" name="tranning_hour"  step="0.5" value="{{ old('tranning_hour', $collectionModel->tranning_hour) }}" />
                                        @if($errors->has('tranning_hour'))
                                            <label id="tranning_hour-error" class="error" for="title">{{$errors->first('tranning_hour')}}</label>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-label-left">
                                    <label for="content" class="col-sm-3 control-label">
                                        {{ trans('education::view.Education.Content') }} <em class="required">*</em>
                                    </label>
                                    <div class="col-sm-9 dv-content">
                                        <textarea class="form-control content" rows="3" id="content" name="content">{{ old('content', $collectionModel->content) }}</textarea>
                                        @if($errors->has('content'))
                                            <label id="content-error" class="error" for="title">{{$errors->first('content')}}</label>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-label-left">
                                    <label for="class_id" class="col-sm-3 control-label">
                                        {{ trans('education::view.Teaching target') }} <em class="required">*</em>
                                    </label>
                                    <div class="col-sm-9 tg-content">
                                        <textarea class="form-control target" rows="5" name="target">{{ old('target', $collectionModel->target) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-label-left">
                                    <label for="class_id" class="col-sm-3 control-label">
                                        {{ trans('education::view.Teaching conditions') }}
                                    </label>
                                    <div class="col-sm-9">
                                        <textarea class="form-control" placeholder="{{ trans('education::view.condition description') }}" rows="5" name="condition">{{ old('condition', $collectionModel->condition) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 align-center">
                                @if (isset($collectionModel) && $collectionModel->id)
                                    <button class="btn-add submitBtn" type="button" data-mode="mode_update"
                                            data-toggle="tooltip" data-placement="top"
                                            title="{{ trans('education::view.Save teaching information') }}">
                                        {{ trans('education::view.Update') }}
                                    </button>
                                    <a class="btn btn-primary" data-toggle="tooltip" data-placement="top"
                                       title="{{ trans('education::view.Send mail register teaching') }}"
                                       href="{{ route('education::education.teaching.teachings.send',['id' => $collectionModel->id]) }}">{{ trans('education::view.Send') }}</a>
                                @else
                                    <button class="btn-add submitBtn" type="button" data-mode="mode_create"
                                            data-toggle="tooltip" data-placement="top"
                                            title="{{ trans('education::view.Save teaching information') }}">
                                        Submit
                                    </button>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script src="{{ asset('lib/js/moment.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script>
    var globalErrorText = '{{ trans("project::view.An error occurred") }}';
    var globalCa = '{{ trans("education::view.Education.Ca") }}';
    var globalStartTime = '{{ trans("education::view.Start Time") }}';
    var globalEndTime = '{{ trans("education::view.End Time") }}';
    var globalTitle = '{{ trans('education::message.Title is required') }}';
    var globalConent = '{{ trans('education::message.Content is required') }}';
    var globalErrorTimeoutText = '{{ trans("project::view.Request time out") }}';
    var globalErrorChoosesType = '{{ trans("education::message.Registration type not selected") }}';
    var globalClassId = '{{ $collectionModel->class_id }}';
    var globalIsShow = '{{ $isShow }}';
    var globalCourseId = '{{ $collectionModel->course_id }}';
    var globalTotalTime = '{{ $collectionModel->tranning_hour }}';
    var globalContent = '{{ $collectionModel->content }}';
    var globalType = '{{ $collectionModel->type }}';
    var globalCourseType = '{{ $collectionModel->course_type_id }}';
    var varGlobalPassModule = {
        dataRegisterTime : JSON.parse('{!! json_encode($collectionModel->teacherTime) !!}')
    }
    var globalMessage = {
        'Input all': "{{ trans('education::message.Input all') }}"
    };
    var urlMaxCourseCode = '{{ route('education::education.MaxCourseCode') }}',
        nonSelectedText = "{{ trans('education::view.Education.Non select') }}",
        allSelectedText = '{{ trans('education::view.Education.All') }}',
        urlMaxClassCode = '{{ route('education::education.MaxClassCode') }}';
</script>
<script src="{{ asset('education/js/register-teaching.js') }}"></script>
<script src="{{ asset('education/js/register-teaching-render.js') }}"></script>
@endsection
