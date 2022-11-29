@extends('layouts.default', ['createProject' => true])
@section('title')
    @if(isset($collectionModel) && $collectionModel->id)
        {{ trans('education::view.Education edit education') }}
    @else
        {{ trans('education::view.Education.Create.Create Education') }}
    @endif
@endsection
<?php
use Rikkei\Education\Model\SettingEducation;
if(isset($collectionModel) && $collectionModel->id) {
    $urlSubmit = route('education::education.settings.types.update',['id' => $collectionModel->id]);
    $checkEdit = true;
} else {
    $urlSubmit = route('education::education.settings.types.store');
    $checkEdit = false;
}
?>
@section('css')
    <link href="{{ asset('education/css/setting-education.css') }}" rel="stylesheet" type="text/css" >
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-info">
                <div class="box-body">
                    <form method="post" action="{{$urlSubmit}}"
                          enctype="multipart/form-data"
                          autocomplete="off"
                          id="form-create-setting-education"
                          class="form-horizontal">
                    {!! csrf_field() !!}
                    @if($checkEdit)
                        {{ method_field('put') }}
                    @endif
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-label-left">
                                    <label for="code" class="col-sm-3 control-label">
                                        {{ trans('education::view.Code') }}
                                        <em class="required">*</em></label>
                                    <div class="col-sm-9">
                                        <input name="code" class="form-control input-field" type="text" id="code"
                                               placeholder="{{ trans('education::view.Code') }}" value="{{ trim(old('code', $collectionModel->code)) }}"/>
                                        @if($errors->has('code'))
                                            <label id="code-error" class="error" for="code">{{$errors->first('code')}}</label>
                                        @endif
                                        @if($checkEdit)
                                            <input type="hidden" name="id" id="id_education" value="{{ old('id', $collectionModel->id) }}" />
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-label-left">
                                    <label for="name" class="col-sm-3 control-label">
                                        {{ trans('education::view.Name') }}
                                        <em class="required">*</em></label>
                                    <div class="col-sm-9">
                                        <input name="name" class="form-control input-field" type="text" id="name"
                                               placeholder="{{ trans('education::view.Name') }}" value="{{ old('name', $collectionModel->name) }}" />
                                        @if($errors->has('name'))
                                            <label id="name-error" class="error" for="name">{{$errors->first('name')}}</label>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-label-left">
                                    <label for="name" class="col-sm-3 control-label">
                                        {{ trans('education::view.Status') }}
                                    </label>
                                    <div class="col-sm-9 ">
                                        <select name="status" id="status" class="form-control input-field">
                                            <option value="{{ SettingEducation::STATUS_ENABLE }}" {{ $collectionModel->status == SettingEducation::STATUS_ENABLE ? 'selected' : null }}>{{ trans('education::view.Status Enable') }}</option>
                                            <option value="{{ SettingEducation::STATUS_DISABLED }}" {{ $collectionModel->status == SettingEducation::STATUS_DISABLED ? 'selected' : null }}>{{ trans('education::view.Status Disabled') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-label-left">
                                    <label for="description" class="col-sm-3 control-label">
                                        {{ trans('education::view.Description') }}
                                    </label>
                                    <div class="col-sm-9">
                                        <textarea rows="9" name="description" class="form-control input-field" id="description" placeholder="{{ trans('education::view.Description') }}">{{ old('description', $collectionModel->description) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 align-center">
                                <button class="btn-add setting-submit" type="button"
                                    data-mode="{{ (isset($collectionModel) && $collectionModel->id) ? 'mode_update' : 'mode_create'  }}">
                                    @if (isset($collectionModel) && $collectionModel->id)
                                        {{ trans('education::view.Update setting education') }}
                                    @else
                                        {{ trans('education::view.Create setting education') }}
                                    @endif
                                </button>
                                @if (isset($collectionModel) && $collectionModel->id)
                                    <button class="show-warning hidden" type="button" data-noti="{{ trans('education::view.There are training courses :name Are you sure you want to fix', ['name' => $collectionModel->code]) }}"></button>
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
    <script src="{{ asset('education/js/setting-education.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
    <script>
        var isShow = '{{ $checkEdit }}';
        var errorText = '{{ trans("project::view.An error occurred") }}';
        var errorTimeoutText = '{{ trans("project::view.Request time out") }}';
        if(isShow) {
            var globurlSubmit = '{{ route('education::education.settings.types.check-exit-code',['id' => $collectionModel->id]) }}';
        }
    </script>
@endsection
