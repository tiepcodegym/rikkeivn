<?php
use Rikkei\Document\View\DocConst;

$title = trans('doc::view.Create type');
if ($item) {
    $title = trans('doc::view.Edit type');
}
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
            'route' => ['doc::admin.type.save'],
            'class' => 'no-validate'
        ]) !!}

        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label>{{ trans('doc::view.Name') }} <em class="text-red">*</em></label>
                    <input type="text" name="name" value="{{ old('name') ? old('name') : ($item ? $item->name : null) }}"
                           class="form-control">
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <?php
                    $status = old('status') ? old('status') : ($item ? $item->status : null);
                    ?>
                    <label>{{ trans('doc::view.Status') }}</label>
                    <select class="form-control select-search" name="status">
                        @foreach (DocConst::listTypeStatuses() as $value => $label)
                        <option value="{{ $value }}" {{ $status == $value ? "selected" : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <?php
                    $parentId = old('parent_id') ? old('parent_id') : ($item ? $item->parent_id : null);
                    ?>
                    <label>{{ trans('doc::view.Parent') }}</label>
                    <select class="form-control select-search" name="parent_id">
                        <option value="">&nbsp;</option>
                        {!! DocConst::toNestedOptions($listTypes, $parentId) !!}
                    </select>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label>{{ trans('doc::view.Sort order') }}</label>
                    <input type="number" min="0" name="order" class="form-control"
                           value="{{ old('order') ? old('order') : ($item ? $item->order : null) }}">
                </div>
            </div>
        </div>

        <div class="form-group text-center">
            <a href="{{ route('doc::admin.type.index') }}" class="btn btn-primary">
                <i class="fa fa-long-arrow-left"></i> {{ trans('doc::view.Back') }}
            </a>
            @if ($item)
            <input type="hidden" name="id" value="{{ $item->id }}">
            @endif
            <button type="submit" class="btn-edit"><i class="fa fa-save"></i> {{ trans('doc::view.Save') }}</button>
        </div>
        
        {!! Form::close() !!}
    </div>
</div>

@stop

@section('script')

@include('doc::includes.script')

@stop

