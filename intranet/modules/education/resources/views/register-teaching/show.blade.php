@extends('layouts.default', ['createProject' => true])

@section('title')
    {{ trans('education::view.Teaching registration details') }}
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
?>
@section('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" >
    <link href="{{ asset('education/css/register-teaching.css') }}" rel="stylesheet" type="text/css" >
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-info">
                <div class="box-body form-horizontal" id="form-create-update">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group form-label-left">
                                <label for="code" class="col-sm-3 control-label">
                                    {{ trans('education::view.Title') }}
                                    <em class="required">*</em></label>
                                <div class="col-sm-9">
                                    <input name="title" class="form-control input-field" type="text" id="title" value="{{ $collectionModel->title }}"/>
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
                                    <select name="scope" class="form-control" style="min-width: 230px;">
                                        @foreach($scopes as $key => $value)
                                            <option value="{{ $key }}"
                                            @if ($collectionModel->scope == $key)
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
                                <label for="course_type_id" class="col-sm-3 control-label lbl-required">
                                    {{ trans('education::view.Education Types') }}
                                    <em class="required hidden">*</em>
                                </label>
                                <div class="col-sm-9">
                                    <select name="course_type_id" class="form-control" id="course_type_id">
                                        @foreach($educationTypes as $key => $educationType)
                                            <option value="{{$key}}"
                                            @if ($collectionModel->course_type_id == $key)
                                                {{ 'selected' }}
                                                    @endif
                                            >{{ $educationType }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group form-label-left">
                                <label for="class_id" class="col-sm-3 control-label">
                                    {{ trans('education::view.Education.Team') }}
                                </label>
                                <div class="col-sm-9">
                                    <textarea class="form-control" rows="5" name="target">{{ $teamsSelected }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
{{--                    <div class="row">--}}
{{--                        <div class="col-md-6">--}}
{{--                            <div class="form-group form-label-left">--}}
{{--                                <label for="type" class="col-sm-3 control-label">--}}
{{--                                    {{ trans('education::view.Registration type') }}--}}
{{--                                </label>--}}
{{--                                <div class="col-sm-9">--}}
{{--                                    <label class="radio-inline"><input type="radio" id="register-type1" class="register-type" {{ $collectionModel->type == '1' ? 'checked' : '' }}   name="type" value="1">{{ trans('education::view.Course available') }}</label>--}}
{{--                                    <label class="radio-inline"><input type="radio" id="register-type2" class="register-type" {{ $collectionModel->type == '2' ? 'checked' : '' }} name="type"  value="2">{{ trans('education::view.According to demand') }}</label>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="row hidden" id="course">--}}
{{--                        <div class="col-md-6">--}}
{{--                            <div class="form-group form-label-left">--}}
{{--                                <label for="course_id" class="col-sm-3 control-label">--}}
{{--                                    {{ trans('education::view.Course') }}--}}
{{--                                </label>--}}
{{--                                <div class="col-sm-9">--}}
{{--                                    @if (count($collectionModel->educationCourses))--}}
{{--                                        <input name="course" class="form-control input-field" type="text" id="course"--}}
{{--                                               value="{{ $collectionModel->educationCourses[0]->name }}"/>--}}
{{--                                    @endif--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="row hidden" id="class">--}}
{{--                        <div class="col-md-6">--}}
{{--                            <div class="form-group form-label-left">--}}
{{--                                <label for="class_id" class="col-sm-3 control-label">--}}
{{--                                    {{ trans('education::view.Class') }}--}}
{{--                                </label>--}}
{{--                                <div class="col-sm-9">--}}
{{--                                    @if (count($collectionModel->Classes))--}}
{{--                                        <input name="course" class="form-control input-field" type="text" id="course"--}}
{{--                                               value="{{ $collectionModel->Classes[0]->class_name }}"/>--}}
{{--                                    @endif--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group form-label-left">
                                <label for="time" class="col-sm-3 control-label lbl-required">
                                    {{ trans('education::view.Number of hours taught') }}
                                    <em class="required hidden">*</em>
                                </label>
                                <div class="col-sm-9">
                                    <input type="number" min="0" class="form-control tranning_hour" name="tranning_hour" value="{{ $collectionModel->tranning_hour }}" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group form-label-left">
                                <label for="time" class="col-sm-3 control-label lbl-required">
                                    {{ trans('education::view.Training time') }}
                                </label>
                                <div class="col-sm-9 hidden" id="detail-class">
                                    <i class="fa fa-spin fa-refresh hidden" id="update_detail_loading"></i>
                                    <div class="list-group detail-class">
                                    </div>
                                </div>
                                <div class="col-sm-9">
                                    <div class="list-group hidden detail_class_choose">
                                        <table class="table table-striped table-bordered table-hover table-grid-data table-grid-data">
                                            <tbody class="tblDetailInput">
                                            </tbody>
                                        </table>
                                    </div>
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
                                <div class="col-sm-9">
                                    <textarea class="form-control content" rows="3" name="content">{{ $collectionModel->content }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group form-label-left">
                                <label for="class_id" class="col-sm-3 control-label">
                                    {{ trans('education::view.Teaching target') }}
                                </label>
                                <div class="col-sm-9">
                                    <textarea class="form-control" rows="5" name="target">{{ $collectionModel->target }}</textarea>
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
                                    <textarea class="form-control" rows="5" name="condition">{{ $collectionModel->condition }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('lib/js/moment.min.js') }}"></script>
    <script>
        var globalIsShow = '{{ $isShow }}';
        var globalCa = '{{ trans("education::view.Education.Ca") }}';
        var globalStartTime = '{{ trans("education::view.Start Time") }}';
        var globalEndTime = '{{ trans("education::view.End Time") }}';
        var globalType = '{{ $collectionModel->type }}';
        var varGlobalPassModule = {
            dataRegisterTime : JSON.parse('{!! json_encode($collectionModel->teacherTime) !!}')
        }
    </script>
    <script src="{{ asset('education/js/register-teaching-show.js') }}"></script>
    <script src="{{ asset('education/js/register-teaching-render.js') }}"></script>
@endsection
