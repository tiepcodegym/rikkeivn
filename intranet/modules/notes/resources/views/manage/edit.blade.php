<?php

use Rikkei\Core\View\CoreUrl;
use Rikkei\Notes\Model\ReleaseNotes;
?>
@extends('layouts.default')

@section('title')
    {{ trans('notes::view.Notes edit') }}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.1/css/bootstrap-datepicker.min.css"></link>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="box-body">
                <form id="form-notes-edit" method="post" action="{{ URL::route('notes::manage.notes.save') }}" autocomplete="off">
                    {!! csrf_field() !!}
                    @if ($notes->id)
                    <input type="hidden" name="id" value="{{ $notes->id }}" />
                    @endif
                    <input type="hidden" value="{{ URL::route('news::post.guest') }}" id="link-render">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group">
                                <label for="title" class="control-label required">{{ trans('notes::view.Version') }} <em>*</em></label>
                                <div class="">
                                    <input name="notes[version]" class="form-control input-field" type="text" id="version" 
                                           value="{{ $notes->version }}" placeholder="{{ trans('notes::view.Version to note') }}" />
                                </div>
                            </div>
                            <div class="form-group <?php if ($notes->id !== null && (int)$notes->status !== ReleaseNotes::STATUS_ENABLE): ?> hidden <?php endif; ?>">
                                <label>
                                    <input type="checkbox" name="has_notify"> {!! trans('notes::view.Send notification') !!}
                                </label>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label required">{{ trans('notes::view.Release at') }}</label>
                                <div class="input-group date" data-date-format="yyyy-mm-dd">
                                    <input type='text' class="form-control" / placeholder="yyyy-mm-dd" name="notes[release_at]" value="{{ substr($notes->release_at, 0, 10)}}">
                                           <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                </div>   
                            </div>
                            <div class="form-group form-group-select2">
                                <label for="status" class="control-label required">{{ trans('notes::view.Status') }} <em>*</em></label>
                                <div class="fg-valid-custom">
                                    <select name="notes[status]" id="status" class="form-control">
                                        <option value="">&nbsp;</option>
                                        @foreach ($optionStatus as $key => $value)
                                        <option value="{{ $key }}"{{ $notes->status == $key ? ' selected' : '' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="form-group">
                        <label class="control-label">{{ trans('notes::view.Content') }} </label>
                        <div class="">
                            <textarea name="notes[content]" class="ckedittor-text" id="content" >{{ htmlspecialchars($notes->content) }}</textarea>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12 text-center">
                            <button type="submit" class="btn btn-success btn-submit-ckeditor btn-save-notes" id="btn-submit" style="font-size: 18px; width: 100px">
                                {{ trans('notes::view.Save') }}
                                <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="{{ URL::asset('lib/js/jquery.validate.min.js') }}"></script>
<script src="{{ URL::asset('lib/ckeditor/ckeditor.js') }}"></script>
<script src="{{ URL::asset('lib/ckfinder/ckfinder.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.1/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript">
    var message_required = "{{ trans('notes::message.This field is required') }}";
    var message_date = "{{ trans('notes::message.Enter the characters with the correct date characters') }}";
    var enableStatus = '{!! ReleaseNotes::STATUS_ENABLE !!}';
</script>
<script type="text/javascript" src="{{ URL::asset('assets/notes/js/notes.js') }}"></script>

@endsection
