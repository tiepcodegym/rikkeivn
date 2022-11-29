@extends('layouts.default')

@section('title', trans('resource::view.Add new team'))

<?php
use Rikkei\Core\View\CoreUrl;
?>
@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css">
<link rel="stylesheet" href="{{ CoreUrl::asset('resource/css/recruit.css') }}">
@endsection

@section('content')

<div class="box box-info">
    <div class="box-body">
        
        {!! Form::open(['method' => 'post', 'route' => 'resource::plan.team.store']) !!}
        
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                
                <div class="form-group row">
                    <label class="col-sm-3 required sm-label">{{ trans('resource::view.Team name') }} <em>*</em></label>
                    <div class="col-sm-9">
                        <input type="text" name="name" class="form-control" autocomplete="off" value="{{ old('name') }}">
                    </div>
                </div>
                
                <div class="form-group row">
                    <label class="col-sm-3 sm-label">{{ trans('resource::view.Alias team') }}</label>
                    <div class="col-sm-9">
                        <select class="form-control select-search" name="team_alias">
                            <option value="">&nbsp;</option>
                            @if ($teamList)
                                @foreach ($teamList as $team)
                                <option value="{{ $team['value'] }}" {{ $team['value'] == old('team_alias') ? 'selected' : '' }}>{{ $team['label'] }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                
                <div class="form-group row">
                    <label class="col-sm-3 sm-label">{{ trans('resource::view.Is software development') }} </label>
                    <div class="col-sm-9">
                        <input type="checkbox" name="is_soft_dev" >
                    </div>
                </div>
                
                <div class="form-group row">
                    <label class="col-sm-3 sm-label">{{ trans('resource::view.Sort order') }} </label>
                    <div class="col-sm-9">
                        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order') ? old('sort_order') : $suggestOrder }}" min="0">
                    </div>
                </div>
                
                <div class="text-center">
                    <a href="{{ route('resource::plan.team.index') }}" class="btn btn-primary"><i class="fa fa-long-arrow-left"></i> {{ trans('resource::view.Back to list') }}</a>
                    <button type="submit" class="btn-add"><i class="fa fa-save"></i> {{ trans('resource::view.Save') }}</button>
                </div>
                
            </div>
        </div>
        
        {!! Form::close() !!}
        
    </div>
</div>

@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script>
    selectSearchReload();
</script>
@endsection