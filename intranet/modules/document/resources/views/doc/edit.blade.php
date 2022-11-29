<?php
use Rikkei\Document\View\DocConst;
use Rikkei\Magazine\Model\Magazine;

$title = trans('doc::view.Create document');
if ($item) {
    $title = trans('doc::view.Edit document');
}
$currentFile = $listFiles->first();
$magazineName = old('name');
$routeMagazine = 'magazine::save';
$isMagazine = false;
if ($currentFile) {
    if ($currentFile->magazine_id != null) {
        $magazine = Magazine::find($currentFile->magazine_id);
        $routeMagazine = ['magazine::update', $currentFile->magazine_id];
        $magazineImages = $magazine->images()->orderBy('order', 'asc')->get();
        $magazineName = $magazine->name;
        $isMagazine = true;
    }
}
$permisEdit = $docPermiss['submit'];
$disabled = !$permisEdit ? 'disabled' : '';

$docStatuses = DocConst::listDocStatuses();
?>

@extends('layouts.default')

@section('title', $title)

@section('css')
    @include('doc::includes.css')
    <link href="{{ asset('magazine/css/style.css') }}" rel="stylesheet" type="text/css" >
@stop

@section('content')

    <div class="box box-primary">
        <div class="box-body">
            {!! Form::open([
                'method' => 'post',
                'route' => ['doc::admin.save'],
                'class' => 'doc-form',
                'files' => true
            ]) !!}

            <div class="row">
                <div class="col-md-2 text-uppercase">
                </div>
                <div class="col-md-4 col-md-offset-6 text-right">
                    <a target="_blank" href="{{ route('doc::admin.help') }}" class="btn btn-info">
                        {{ trans('doc::view.Help') }} <i class="fa fa-question"></i>
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-8">

                    <div class="form-group">
                        <label>{{ trans('doc::view.Document name') }} <em class="text-red">*</em></label>
                        <input type="text" name="code" value="{{ old('code') ? old('code') : ($item ? $item->code : null) }}"
                               class="form-control" {{ $disabled }}>
                    </div>

                    <div class="form-group file-group row">
                        <div class="col-sm-6">
                            <label>
                                <input type="radio" @if(!$permisEdit) disabled="true" @endif checked="true" name="type_document" id="type_document"> {{ trans('doc::view.Document') }} <em class="required">*</em>
                            </label>
                            <div class="col-sm-12 type_document">
                                <div>
                                    @if ($currentFile && !$isMagazine)
                                        <p class="document-file">
                                            <a target="_blank" href="{{ $currentFile->downloadLink($item->id) }}">{{ $currentFile->name }}</a>
                                        </p>
                                    @endif
                                </div>
                                @if ($permisEdit)
                                    @if ($currentFile && !$isMagazine)
                                    <input type="hidden" name="file_id" value="{{ $currentFile->id }}">
                                    @endif
                                    <div class="form-group file-upload">
                                        <label>{{ trans('doc::view.File upload') }}</label>
                                        <input type="file" name="file" @if($isMagazine) disabled="true" @endif/>
                                    </div>
                                    <div class="form-group file-link">
                                        <label>{{ trans('doc::view.Or input link') }}</label>
                                        <input type="text" @if($isMagazine) disabled="true" @endif name="file_link" value="{{ old('file_link') ? old('file_link') : null }}" class="form-control">
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-sm-6 file-group">
                            <label>
                                <input type="radio" @if(!$permisEdit) disabled="true" @endif @if($isMagazine) checked="true" @endif name="type_document" id="type_magazine"> {{ trans('doc::view.Handbook document') }} <em class="required">*</em> : (@if (!$isMagazine){{ trans('doc::view.Add document and click save') }}@else{{ trans('doc::view.Edit document and click save') }}@endif)
                            </label>
                            <div class="col-sm-12 type_magazine">
                                <div>
                                    @if ($isMagazine)
                                        <p class="document-file">
                                            <a target="_blank" href="{{ $currentFile->url }}">{{ $currentFile->name }}</a>
                                        </p>
                                    @endif
                                </div>
                                @if ($permisEdit)
                                <input type="hidden" @if(!$isMagazine) disabled="true" @endif name="magazine_name" id="magazine_name" value="@if($isMagazine){{ $magazine->name }}@endif"/>
                                <input type="hidden" @if(!$isMagazine) disabled="true" @endif name="id_magazine" id="id_magazine" value="@if($isMagazine){{ $magazine->id }}@endif"/>
                                <button type="button" class="btn btn-primary" @if(!$isMagazine) disabled="true" @endif id="add_magazine">@if (!$isMagazine) {{ trans('doc::view.Add') }} @else {{ trans('doc::view.Edit') }} @endif</button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <hr />

                    <div class="form-group">
                        <div>
                            <div>
                                <label>{{ trans('doc::view.Attach file') }}</label>
                                @if (!$attachFiles->isEmpty())
                                <div class="document-file">
                                    @foreach ($attachFiles as $file)
                                    <div class="attach-file-item">
                                        @if ($permisEdit)
                                        <input type="hidden" name="attach_file_ids[]" value="{{ $file->id }}">
                                        <button class="btn btn-danger btn-sm btn-del-file" type="button" title="{{ trans('doc::view.Delete') }}">
                                            <i class="fa fa-close"></i>
                                        </button>
                                        @endif
                                        <a href="{{ $file->downloadLink($item->id) }}">{{ $file->name }}</a>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                            @if ($permisEdit)
                            <div class="upload-wrapper">
                                <div class="list-input-fields">
                                    <div class="attach-file-item">
                                        <button class="btn btn-danger btn-sm btn-del-file" type="button" title="{{ trans('doc::view.Delete') }}">
                                            <i class="fa fa-close"></i>
                                        </button>
                                        <input type="file" name="attach_files[]">
                                    </div>
                                </div>
                                <button type="button" class="btn btn-primary btn-sm" id="btn_add_input_file"
                                        data-name="attach_files[]"><i class="fa fa-plus"></i></button>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <hr />

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <?php
                                if ($oldTeams = old('team_ids')) {
                                    $selectedTeams = DocConst::getOldTeams($oldTeams);
                                }
                                ?>
                                <label>
                                    <span>{{ trans('doc::view.Belong to group') }} <em class="text-red">*</em></span> &nbsp;&nbsp;
                                    @if (!$selectedTeams->isEmpty())
                                    <span>({{ $selectedTeams->implode('name', ', ') }})</span>
                                    @endif
                                </label>
                                <div class="row">
                                    <div class="col-xs-6">
                                        <label class="inline">
                                            <input type="checkbox" class="checkbox-all doc-team-check-all" {{ $disabled }}> 
                                            {{ trans('doc::view.Select all') }}
                                        </label>
                                    </div>
                                </div>
                                <div class="checkbox-group doc-team-group">
                                    <ul class="list-unstyled">
                                        {!! DocConst::toNestedCheckbox($teamList, $selectedTeams->lists('id')->toArray(), 'team_ids[]', !$permisEdit) !!}
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>{{ trans('doc::view.Description') }}</label>
                        <textarea class="form-control noresize" name="description" rows="5"
                                  {{ $disabled }}>{{ old('description') ? old('description') : ($item ? $item->description : null) }}</textarea>
                    </div>

                </div>

                <div class="col-sm-4">

                    @if ($item)
                        @include('doc::includes.doc-assignee')

                        <div class="form-group">
                            <label class="inline">{{ trans('doc::view.Author') }}: </label>
                            <span>{{ $item->getAuthorName() }}</span>
                        </div>
                    @endif

                    @include('doc::includes.doc-type')
                        <label>{{ trans('doc::view.Published to') }}: </label>
                        @if ($item && $item->publish_all)
                            <span>{{ trans('doc::view.All') }}</span>
                        @else
                            @if (!$listPublished->isEmpty())
                                <?php
                                $publishTeams = array_filter($listPublished->lists('team_name', 'team_id')->toArray());
                                $publishAccounts = array_filter($listPublished->lists('employee_email', 'employee_id')->toArray());
                                ?>
                                @if ($publishTeams)
                                <div>
                                    <span>{{ trans('doc::view.Group') }}:</span> {{ implode(', ', $publishTeams) }}
                                </div>
                                @endif
                                @if ($publishAccounts)
                                <div>
                                    <span>{{ trans('doc::view.Account') }}:</span> {{ implode(', ', $publishAccounts) }}
                                </div>
                                @endif
                            @endif
                        @endif
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-6">
                                    <label class="inline">
                                        <input type="checkbox" class="checkbox-all doc-team-check-all" {{ $disabled }}>
                                        {{ trans('doc::view.Select all') }}
                                    </label>
                                </div>
                            </div>
                            <div class="checkbox-group doc-team-group">
                                <ul class="list-unstyled">
                                    {!! DocConst::toNestedCheckbox($teamList, $listPublished->lists('team_id')->toArray(), 'team_publish_ids[]', !$permisEdit) !!}
                                </ul>
                            </div>
                            <div class="form-group select2-group">
                                <label>{{ trans('doc::view.Add accounts') }}</label>
                                <select class="form-control select-search-employee select-search" multiple name="account_ids[]"
                                        id="account_ids" data-remote-url="{{ route('team::employee.list.search.ajax') }}">
                                    @if (isset($publishAccounts) && $publishAccounts)
                                        @foreach ($publishAccounts as $empId => $empEmail)
                                            <option value="{{ $empId }}" selected>{{ DocConst::getAccount($empEmail) }}</option>
                                        @endforeach
                                    @else
                                        @if (!$accoutsPublish->isEmpty())
                                            @foreach ($accoutsPublish as $emp)
                                                <option value="{{ $emp->id }}" selected>{{ DocConst::getAccount($emp->email) }}</option>
                                            @endforeach
                                        @endif
                                    @endif
                                </select>
                            </div>
                        </div>
                </div>
            </div>

            <div class="row margin-top-10">
                <div class="col-sm-8">
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <a href="{{ route('doc::admin.index') }}" class="btn btn-primary">
                            <i class="fa fa-long-arrow-left"></i> {{ trans('doc::view.Back') }}
                        </a>
                        @if ($item)
                        <input type="hidden" name="id" value="{{ $item->id }}">
                        @endif
                        @if ($docPermiss['edit'])
                        <button type="submit" class="btn-edit" id="btn_save_doc"><i class="fa fa-save"></i> {{ trans('doc::view.Save') }}</button>
                        @endif
                    </div>
                </div>
            </div>

            {!! Form::close() !!}
        </div>
    </div>

    <div class="modal magazine" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document" style="width: 80%">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4>
                        {{ trans('doc::view.Handbook document') }}
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="box box-primary">
                        <div class="box-body">
                            <div id="update_file">
                                <div id="error_box" class="hidden"></div>
                                {!! Form::open(['method' => 'post', 'route' => $routeMagazine, 'files' => true, 'id' => 'create_magazine', 'class' => 'imageloaderForm']) !!}
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group row">
                                            <label class="col-sm-3">{{ trans('doc::view.File name') }} <em class="text-red">*</em></label>
                                            <div class="col-sm-9">
                                                {!! Form::text('name', $magazineName, ['id' => 'name_magazine', 'name' => 'name_magazine', 'class' => 'form-control', 'placeholder' => trans('doc::view.File name')]) !!}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="fileUpload btn btn-primary">
                                            {{ trans('doc::view.Add image') }} <i class="hidden uploading fa fa-spin fa-refresh"></i>
                                            <input name="image[]" id="fileUpload" class="upload" type="file" accept="image/*" multiple/>
                                        </label>
                                        <span><i>{{ trans('magazine::view.Drag image to range order, check image to choose background') }}</i></span>
                                        <br />
                                        {{ trans('magazine::view.Recommend size') }}
                                    </div>
                                </div>
                                <div id="uploadPreview">
                                    @if(isset($magazineImages))
                                        @if (!$magazineImages->isEmpty())
                                            @foreach($magazineImages as $image)
                                                @include('magazine::template.image-item', ['image' => $image])
                                            @endforeach
                                        @endif
                                    @endif
                                </div>
                                <input type="hidden" name="selected" id="selected" value="">
                                <div class="form-group text-center">
                                    <br />
                                    <p class="submit-alert hidden"><i class="fa fa-spin fa-refresh"></i> {{ trans('magazine::message.Processing image, please wait') }}</p>
                                    @if ($isMagazine)
                                    <label><em class="required">*</em> {{ trans('doc::view.Edit document and click save') }}</label>
                                    </br>
                                    <button id="submit_magazine" class="btn-edit" type="submit"><i class="fa fa-save"></i> {{ trans('doc::view.Update') }}</button>
                                    @else
                                    <label><em class="required">*</em> {{ trans('doc::view.Add document and click save') }}</label>
                                    </br>
                                    <button id="submit_magazine" class="btn-add" type="submit"><i class="fa fa-save"></i> {{ trans('doc::view.Create') }}</button>
                                    @endif
                                </div>
                                {!! Form::close() !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-primary" data-dismiss="modal">{{ trans('welfare::view.Close') }}</button>
                </div>
            </div>
        </div>
    </div>

    @if ($item)

        <div class="row">
            <div class="col-md-8" cl>
                @include('doc::includes.doc-version')

                @include('doc::includes.doc-comment')
            </div>
            <div class="col-md-4">
                @include('doc::includes.doc-history')
            </div>
        </div>
        @include('doc::includes.doc-modal')

    @endif

@stop

@section('script')
<script src="/lib/js/exif.js"></script>
@include('magazine::template.script')
    <script type="text/javascript">
        var scroll = 0;
        window.addEventListener("scroll", function(event) {
            scroll = this.scrollY;
        }, false);
        $(document).on('keyup', '.select2-search__field', function (e) {
            if ($(this).val().length < 2) {
                window.scrollTo(0, scroll + 1);
                window.scrollTo(0, scroll);
                return;
            }
        });
        $('#type_document').click(function() {
            $('.type_magazine').find('button').attr('disabled', 'true');
            $('.type_magazine').find('input').attr('disabled', 'true');
            $('.type_document').find('input').prop('disabled', '');
            $('.type_document').find('a').attr('style', '');
            $('.type_magazine').find('a').attr('style', 'pointer-events: none; cursor: default; color: #7b7b7b;');
        });
        $('#type_magazine').click(function() {
            $('.type_magazine').find('button').prop('disabled', '');
            $('.type_magazine').find('input').prop('disabled', '');
            $('.type_document').find('input').attr('disabled', 'true');
            $('.type_document').find('a').attr('style', 'pointer-events: none; cursor: default; color: #7b7b7b;');
            $('.type_magazine').find('a').attr('style', '');
        });
        $('#add_magazine').click(function() {
            $(".magazine").modal('show');
        });
        //Append array of image after sort to Form to send server
        $("#create_magazine").submit(function () {
            var el_this = $(this);
            $('#error_box').addClass('hidden').html('');
            var btn = el_this.find('button[type="submit"]');

            var formData = new FormData();
            formData.append('_token', el_this.find('input[name="_token"]').val());
            formData.append('name', $('#name_magazine').val());
            formData.append('select_index', select_index);
            formData.append('document', 'document');

            var size = 0;
            $('#uploadPreview .imgPreviewWrap').each(function (e_index) {
                if (typeof $(this).attr('data-index') != "undefined") {
                    var item_index = $(this).attr('data-index');
                    var file = tempFiles[item_index];
                    if (file) {
                        if (typeof file == 'object') {
                            // check each file size < 10MB
                            if (file.size > MAX_FILE_SIZE * 1024 * 1024) {
                                showModalError(msg['validFileSize'] + ' ('+ file.name +' - ' + Math.floor(file.size / 1024) + 'KB)');
                                btn.prop('disabled', false);
                                return false;
                            }
                            size += file.size;
                            formData.append('images['+ e_index +']', file);
                        } else {
                            formData.append('image_ids['+ e_index +']', file);
                        }
                    }
                }
            });
            //check total file size < server allow post file size
            if (size > MAX_SIZE * 1024 * 1024) {
                showModalError(msg['fileMaxSize']);
                btn.prop('disabled', false);
                return false;
            }
            // begin upload file
            $('#fileUpload').prop('disabled', true);
            // show loading
            $('.submit-alert').removeClass('hidden');

            $.ajax({
                type: 'POST',
                url: el_this.attr('action'),
                dataType: 'json',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    if (data.err) {
                        $('#error_box').removeClass('hidden').html(data.err);
                    } else {
                        $('#id_magazine').val(data.id);
                        $('#magazine_name').val(data.name);
                        if ($('#submit_magazine').hasClass('btn-add')) {
                            $('#add_magazine').html('{{ trans('doc::view.Edit') }}');
                            var id = data.id;
                            $.ajax({
                                type: 'get',
                                url: '{{ route('doc::update_file') }}',
                                data: {
                                    id
                                },
                                dataType: 'json',
                                success: function (data) {
                                    $('#update_file').html(data);
                                },
                            });
                        } else {
                            $('#error_box').removeClass('hidden').html('<div class="flash-message"><div class="alert alert-success"><ul><li>'+ data.message +'</li></ul></div></div>');
                            setTimeout(function() {$('#error_box').addClass('hidden');}, 4000);
                        }
                    }
                },
                error: function (err) {
                    if (err.status == 422) {
                        $('#error_box').removeClass('hidden').html(err.responseJSON);
                    } else {
                        showModalError(err.responseJSON);
                    }
                },
                complete: function () {
                    btn.prop('disabled', false);
                    $('.submit-alert').addClass('hidden');
                    $('#fileUpload').prop('disabled', false);
                }
            });
            
            return false;
        });
    </script>
    <script src="{{ asset('lib/ckeditor/ckeditor.js') }}"></script>
    <script>
        var documentId = null;
        @if ($item)
            documentId = {{ $item->id }};
        @endif
    </script>
    @include('doc::includes.script')

    <script>
        (function ($) {
            RKfuncion.select2.init();
            @if ($docPermiss['publish'])
                $(document).ready(function () {
                    RKfuncion.CKEditor.init(['publish_mail_content']);
                });
            @endif
        })(jQuery);
    </script>

@stop

