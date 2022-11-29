@extends('layouts.default', ['createProject' => true])
@section('title')
    {{ trans('education::view.Set up mail address') }}
@endsection
<?php
if(isset($collectionModel) && $collectionModel->id) {
    $urlSubmit = route('education::education.settings.update-mail', ['id' => $collectionModel->id]);
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
                          enctype="multipart/form-data" autocomplete="off" id="form-create-setting-education" class="form-horizontal form-sales-module">
                        {!! csrf_field() !!}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-label-left">
                                    <label for="code_education" class="col-sm-3 control-label">
                                        {{ trans('education::view.Branch name') }}
                                    </label>
                                    <div class="col-sm-9">
                                        <input name="name" disabled class="form-control input-field" type="text" id="name"
                                               placeholder="{{ trans('education::view.Branch name') }}" value="{{ old('name', $collectionModel->name) }}"/>
                                        <input name="team_id" type="hidden" value="{{ $collectionModel->id }}"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-label-left">
                                    <label for="name" class="col-sm-3 control-label">
                                        {{ trans('education::view.Branch code') }}
                                    </label>
                                    <div class="col-sm-9">
                                        <input name="branch_code" disabled class="form-control input-field" type="text" id="branch_code" placeholder="{{ trans('education::view.Branch code') }}" value="{{ old('branch_code', $collectionModel->branch_code) }}" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-label-left">
                                    <label for="name" class="col-sm-3 control-label">
                                        {{ trans('education::view.Email') }}
                                    </label>
                                    <div class="col-sm-9">
                                        <input name="email" class="form-control input-field" type="email" id="email" placeholder="{{ trans('education::view.Email') }}" value="{{ old('email', count($collectionModel->addressMail) ? $collectionModel->addressMail->email : '') }}" />
                                        @if($errors->has('email'))
                                            <label id="email-error" class="error" for="email">{{$errors->first('email')}}</label>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 align-center">
                                <button class="btn-add" type="submit" >
                                    Submit
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
@endsection
