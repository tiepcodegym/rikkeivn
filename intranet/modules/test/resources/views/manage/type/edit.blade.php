@extends('layouts.default')

@section('title', trans('test::test.edit_test_type'))

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="{{ URL::asset('tests/css/main.css') }}" />
@stop

@section('content')

<div class="box box-info">
    <div class="box-body">

        {!! Form::open(['method' => 'put', 'route' => ['test::admin.type.update', $item->id], 'class' => 'validate_form']) !!}

        <div class="form-group row">
            <div class="col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4">
                @foreach($allLang as $langVal => $langText)
                <div class="form-group">
                    <label>{{ $langText }}</label>
                    <input type="text" name="name_{{ $langVal }}" value="{{ old('name_' . $langVal) ? old('name_' . $langVal) : isset($typesMeta[$langVal]) ? $typesMeta[$langVal]->name : '' }}" 
                           class="form-control" placeholder="{{ trans('test::test.name', [], '', $langVal) }}" style="margin-bottom: 10px;">
                </div>
                @endforeach
                <div class="form-group">
                    <label>{{ trans('test::test.parent') }} ({{ trans('test::test.group_type') }})</label>
                    <select class="form-control select-search has-search" name="parent_id">
                        <option value="">&nbsp;</option>
                        @if (!$groupTypes->isEmpty())
                            @foreach ($groupTypes as $group)
                            <option value="{{ $group->id }}" {{ $group->id == $item->parent_id ? 'selected' : '' }}>{{ $group->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                
                <div class="form-group">
                    <a href="{{route('test::admin.type.index')}}" class="btn btn-primary"><i class="fa fa-long-arrow-left"></i> {{trans('test::test.back')}}</a>
                    <button type="submit" class="btn-edit"><i class="fa fa-save"></i> {{ trans('test::test.save') }}</button>
                </div>
            </div>
        </div>

        {!! Form::close() !!}

    </div>
</div>

@stop

@section('script')

@include('test::template.script')

@stop
