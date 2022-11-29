<?php
use Rikkei\Document\View\DocConst;

$title = trans('doc::view.Create document request');
if ($item) {
    $title = trans('doc::view.Edit document request');
}
$permissSubmit = $requestPermiss['edit'];
?>

@extends('layouts.default')

@section('title', $title)

@section('css')

@include('doc::includes.css')

@stop

@section('content')

<div class="box box-primary">
    <div class="box-body">

        {!! Form::open([
            'method' => 'post',
            'route' => ['doc::admin.request.save'],
            'class' => 'no-validate'
        ]) !!}

        <div class="row">
            <div class="col-sm-8">
                <div class="form-group">
                    <label>{{ trans('doc::view.Name') }} <em class="text-red">*</em></label>
                    <input type="text" name="name" value="{{ old('name') ? old('name') : ($item ? $item->name : null) }}"
                           class="form-control" {!! $permissSubmit ? '' : 'disabled' !!}>
                </div>
                
                <div class="form-group">
                    <label>{{ trans('doc::view.Content') }} <em class="text-red">*</em></label>
                    <textarea class="form-control" name="content" id="request_content"rows="5"
                              {!! $permissSubmit ? '' : 'disabled' !!}>{{ old('content') ? old('content') : ($item ? $item->content : null) }}</textarea>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <label>{{ trans('doc::view.Document creator') }} <em class="required">*</em></label>
                    <select class="form-control select-search-employee select-search" name="creator_ids[]"
                            data-remote-url="{{ route('team::employee.list.search.ajax') }}"
                            {!! $permissSubmit ? '' : 'disabled' !!} multiple>
                        <?php
                        if ($oldCreators = DocConst::getOldEmployee(old('creator_ids'))) {
                            $creators = $oldCreators;
                        }
                        ?>
                        @if ($creators && !$creators->isEmpty())
                            @foreach ($creators as $creator)
                            <option value="{{ $creator->id }}" selected>{{ DocConst::getAccount($creator->email) }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                
                <div class="form-group">
                    <label>{{ trans('doc::view.Note') }}</label>
                    <textarea class="form-control noresize" name="note" rows="5"
                              {!! $permissSubmit ? '' : 'disabled' !!}>{{ old('note') ? old('note') : ($item ? $item->note : null) }}</textarea>
                </div>
                
                @if ($item)
                    <?php
                    $author = $item->author;
                    ?>
                    <div class="form-group">
                        <label>{{ trans('doc::view.Author') }}: </label> <span>{{ DocConst::getAccount($author ? $author->email : null) }}</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-sm-8">
                <div class="form-group text-center">
                    <a href="{{ route('doc::admin.request.index') }}" class="btn btn-primary">
                        <i class="fa fa-long-arrow-left"></i> {{ trans('doc::view.Back') }}
                    </a>

                    @if ($requestPermiss['create_doc'])
                        <?php
                        $createParams = ['id' => null];
                        if ($item) {
                            $createParams['request_id'] = $item->id;
                        }
                        ?>
                        <a href="{{ route('doc::admin.edit', $createParams) }}" class="btn btn-info">
                            <i class="fa fa-plus"></i>
                            {{ trans('doc::view.Create document') }}
                        </a>
                    @endif

                    @if ($item)
                    <input type="hidden" name="id" value="{{ $item->id }}">
                    @endif
                    @if ($requestPermiss['edit'])
                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> {{ trans('doc::view.Save') }}</button>
                    @endif
                </div>
            </div>
        </div>

        {!! Form::close() !!}
    </div>
</div>

@if ($item)

<div class="row">
    <div class="col-sm-8">
    @include('doc::includes.doc-history')
    </div>
</div>

@include('doc::includes.request-modal')

@endif

@stop

@section('script')

<script src="{{ asset('lib/ckeditor/ckeditor.js') }}"></script>
@include('doc::includes.script')

<script>
    (function ($) {
        RKfuncion.select2.init();
        
        $(document).ready(function () {
            RKfuncion.CKEditor.init(['request_content']);
        });
    })(jQuery);
</script>

@stop

