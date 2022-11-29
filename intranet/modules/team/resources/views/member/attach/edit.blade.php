<?php
use Rikkei\Core\View\View;

$tabTitleSub = trans('team::profile.attach_title_sub');
?>
@extends('team::member.profile_layout')
@section('content_profile')
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label required">{{ trans('team::profile.File name') }}<em>*</em></label>
            <div class="input-box col-md-8">
                <input type="text" class="form-control" name="attach[title]"
                       placeholder="{{ trans('team::profile.File name') }}"
                       value="{{ $employeeItemMulti->title }}"
                       {!!$disabledInput!!} />
            </div>
        </div>
    </div>
</div>
<div class="row" data-doc-scan="files">
    <div class="col-md-12 form-horizontal">
        <div class="form-group row">
            <label class="col-md-2 control-label">{{ trans('team::view.Image') }}</label>
            <div class="input-box col-md-10">
                <div id="profile-attach-files"></div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12 form-horizontal">
        <div class="form-group row">
            <label class="col-md-2 control-label">{{ trans('team::profile.Note') }}</label>
            <div class="input-box col-md-10">
                <textarea class="form-control"
                      rows="6" name="attach[note]"
                      id="employee_attachment-note"
                      placeholder="{{ trans('team::profile.Note') }}"
                      {!!$disabledInput!!}>{{ $employeeItemMulti->note }}</textarea>
            </div>
        </div>
    </div>
</div>
@endsection

@section('profile_css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/file-uploader/5.16.0/all.fine-uploader/fine-uploader-gallery.min.css" />
@endsection
@section('profile_js_file')
<script src="https://cdnjs.cloudflare.com/ajax/libs/file-uploader/5.16.0/fine-uploader.min.js"></script>
<script type="text/template" id="qq-template">
    <div class="qq-uploader-selector qq-uploader qq-gallery" qq-drop-area-text="Drop files here">
        <div class="qq-total-progress-bar-container-selector qq-total-progress-bar-container">
            <div role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" class="qq-total-progress-bar-selector qq-progress-bar qq-total-progress-bar"></div>
        </div>
        <div class="qq-upload-drop-area-selector qq-upload-drop-area" qq-hide-dropzone>
            <span class="qq-upload-drop-area-text-selector"></span>
        </div>
        <div class="qq-upload-button-selector qq-upload-button">
            <div>{{ trans('team::view.Upload a file') }}</div>
        </div>
        <span class="qq-drop-processing-selector qq-drop-processing">
            <span>Processing dropped files...</span>
            <span class="qq-drop-processing-spinner-selector qq-drop-processing-spinner"></span>
        </span>
        <ul class="qq-upload-list-selector qq-upload-list" role="region" aria-live="polite" aria-relevant="additions removals">
            <li>
                <span role="status" class="qq-upload-status-text-selector qq-upload-status-text"></span>
                <div class="qq-progress-bar-container-selector qq-progress-bar-container">
                    <div role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" class="qq-progress-bar-selector qq-progress-bar"></div>
                </div>
                <span class="qq-upload-spinner-selector qq-upload-spinner"></span>
                <div class="qq-thumbnail-wrapper">
                    <img class="qq-thumbnail-selector cursor-pointer" qq-max-size="120" qq-server-scale>
                </div>
                <button type="button" class="qq-upload-cancel-selector qq-upload-cancel">X</button>
                <button type="button" class="qq-upload-retry-selector qq-upload-retry">
                    <span class="qq-btn qq-retry-icon" aria-label="Retry"></span>
                    Retry
                </button>

                <div class="qq-file-info">
                    <div class="qq-file-name">
                        <a href="javascript:void(0);" class="qq-upload-file-selector qq-upload-file" data-qq-selector="download"></a>
                        <span class="qq-edit-filename-icon-selector qq-btn qq-edit-filename-icon" aria-label="Edit filename"></span>
                    </div>
                    <input class="qq-edit-filename-selector qq-edit-filename" tabindex="0" type="text">
                    <span class="qq-upload-size-selector qq-upload-size"></span>
                    <button type="button" class="qq-btn qq-upload-delete-selector qq-upload-delete">
                        <span class="qq-btn qq-delete-icon" aria-label="Delete"></span>
                    </button>
                    <button type="button" class="qq-btn qq-upload-pause-selector qq-upload-pause">
                        <span class="qq-btn qq-pause-icon" aria-label="Pause"></span>
                    </button>
                    <button type="button" class="qq-btn qq-upload-continue-selector qq-upload-continue">
                        <span class="qq-btn qq-continue-icon" aria-label="Continue"></span>
                    </button>
                </div>
            </li>
        </ul>

        <dialog class="qq-alert-dialog-selector">
            <div class="qq-dialog-message-selector"></div>
            <div class="qq-dialog-buttons">
                <button type="button" class="qq-cancel-button-selector">Close</button>
            </div>
        </dialog>

        <dialog class="qq-confirm-dialog-selector">
            <div class="qq-dialog-message-selector"></div>
            <div class="qq-dialog-buttons">
                <button type="button" class="qq-cancel-button-selector">No</button>
                <button type="button" class="qq-ok-button-selector">Yes</button>
            </div>
        </dialog>

        <dialog class="qq-prompt-dialog-selector">
            <div class="qq-dialog-message-selector"></div>
            <input type="text">
            <div class="qq-dialog-buttons">
                <button type="button" class="qq-cancel-button-selector">Cancel</button>
                <button type="button" class="qq-ok-button-selector">Ok</button>
            </div>
        </dialog>
    </div>
</script>
<script>
    var attachFiles = {!!json_encode($attachFiles)!!};
</script>
@endsection
