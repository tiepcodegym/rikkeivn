@extends('layouts.default', ['createProject' => true])
@section('title')
    @if(isset($collectionModel) && $collectionModel->id)
            {{ trans('education::view.Show detail setting education') }}
    @endif
@endsection

@section('css')
    <link href="{{ asset('education/css/setting-education.css') }}" rel="stylesheet" type="text/css" >
@endsection
<?php
use Rikkei\Education\Model\SettingEducation;
?>
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-info">
                <div class="box-body">
                    <div class="form-horizontal">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-label-left">
                                    <label for="code" class="col-sm-3 control-label">
                                        {{ trans('education::view.Code') }}
                                        <em class="required">*</em></label>
                                    <div class="col-sm-9">
                                        <input name="code" disabled class="form-control input-field" type="text" id="code" value="{{ $collectionModel->code }}" />
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
                                        <input name="name" disabled class="form-control input-field" type="text" id="name" value="{{ $collectionModel->name }}" />
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
                                    <div class="col-sm-9">
                                        <select name="status" id="status" disabled class="form-control input-field">
                                            <option value="{{ SettingEducation::STATUS_DISABLED }}" {{ $collectionModel->status == SettingEducation::STATUS_DISABLED ? 'selected' : null }}>{{ trans('education::view.Status Disabled') }}</option>
                                            <option value="{{ SettingEducation::STATUS_ENABLE }}" {{ $collectionModel->status == SettingEducation::STATUS_ENABLE ? 'selected' : null }}>{{ trans('education::view.Status Enable') }}</option>
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
                                        <textarea rows="9" disabled name="description" class="form-control input-field" id="description">{{ $collectionModel->description }}</textarea>
                                    </div>
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
@endsection
