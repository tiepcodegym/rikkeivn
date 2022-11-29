<?php
use Rikkei\Core\View\CoreUrl;
?>
@extends('layouts.default')

@section('title')
    {{ trans('project::view.Project Plan') . ':' . $project->name}}
    @if(isset($pmActive) && $pmActive)
        {{ ' - PM: ' . \Rikkei\Project\View\GeneralProject:: getNickName($pmActive->email)}}
    @endif
@endsection
@section('css')
    <link rel="stylesheet" href="{!! URL::asset('project/css/mentiony.css') !!}" />
    <link rel="stylesheet" href="{!! CoreUrl::asset('project/css/plan.css') !!}" />
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6">
            @if(isset($isPmOfProject))
                <div class="row">
                    <div class="col-md-10 ">
                        <label class="fileUpload btn btn-primary">
                            {{ trans('project::view.Add files') }}
                            <input type="file" id="files" name="project-file" class="form-control hidden" multiple/>
                        </label>
                        <label id="project-file-error" class="error" for="project-file" hidden>{{ trans('project::view.This field is required.') }}</label>
                        <div id="file-list" style="margin-top: 10px;"></div>
                    </div>
                    <div class="col-md-2 ">
                        <button id="upload" class="btn btn-info">{{ trans('project::view.upload') }}</button>
                    </div>
                </div>
            @endif
            <p class="margin-top-20" style="font-size: 20px; font-weight: bold">{{ trans('project::view.Files list') }}:</p>
            <div class="grid-data-file-list">
                @include('project::plan.resource_list', ['resourceList' => $resourceList])
            </div>
        </div>
        <div class="col-md-6">
            <!-- comment feedback -->
            <input type="hidden" name="_token" id="token-comment" value="{{ csrf_token() }}">
            <div id="project-plan-comments">
                <div class="box box-solid box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ trans('project::view.Comments') }}</h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        </div>
                        <!-- /.box-tools -->
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-10">
                                <textarea class="form-control text-resize-y" id="proj-plan-comment" rows="3" aria-required="true" aria-invalid="false">
                                </textarea>
                                <label id="plan-comment-error" class="error" for="comment-content" hidden>{{ trans('project::view.This field is required.') }}</label>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-primary" id="add-comment-feedback"  onclick="saveComment()">{{ trans('project::view.Add') }} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                            </div>
                        </div>
                        <br/><br/>
                        <div class="grid-data-query" data-url="{!! route('project::plan.comment.list.ajax', ['id' => $projectId]) !!}">
                            <div class="grid-data-query-table">
                                @include('project::plan.comment_list', ['collectionModel' => $collectionModel])
                            </div>
                        </div>
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script type="text/javascript" src="{{ CoreUrl::asset('project/js/mentiony.js') }}"></script>
    <script type="text/javascript">
        var token = '{{ csrf_token() }}';
        var projId = '{{$projectId}}';
        var urlDownload = '{{route('project::plan.download', ":file")}}';
        var urlDeleteFile = '{!! route('project::plan.delete-file', '') . '/' !!}';
        var urlGetMembers = '{!! route("project::plan.projectMember") !!}';
        var urlSaveComment = '{!! route("project::plan.saveComment") !!}';
        var txtConfirmDeleteFile = '{!! trans('project::message.Are you sure you want to delete this file?') !!}';
        var txtNoFiles = '{!! trans('project::view.No file uploaded') !!}';
        var urlUpload = '{!! route("project::plan.upload") !!}';
        var titleDownload = '{{ trans('project::view.download') }}';
        var maxFileSize = parseInt('{{ \Rikkei\Core\View\CoreFile::getInstance()->getMaxFileSize() }}');
        var maxFileSizeMsg = "{{ trans('project::view.Server allow file max size', ['max' => intval(\Rikkei\Core\View\CoreFile::getInstance()->getMaxFileSize())]) }}";
        var maxTotalFileSizeMsg = "{{ trans('project::view.Server allow total file size', ['max' => intval(\Rikkei\Core\View\CoreFile::getInstance()->getMaxFileSize())]) }}";
    </script>
    <script type="text/javascript" src="{{ CoreUrl::asset('project/js/plan.js') }}"></script>
@endsection