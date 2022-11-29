<?php
use Rikkei\Core\View\CoreUrl;
?>

@extends('layouts.default')

@section('title')
{{ trans('project::view.Monthly report') . ' D config' }}
@endsection

@section('css')
<link href="{{ CoreUrl::asset('project/css/monthly_report.css') }}" rel="stylesheet" type="text/css" >
@endsection

@section('content')

<form method="post" action="{{ route('project::monthly.report.save_config') }}">
    {!! csrf_field() !!}
    <div class="nav-tabs-custom">
        @if (!$teamsPermiss->isEmpty())
        <ul class="nav nav-tabs" role="tablist">
        @foreach ($teamsPermiss as $idx => $team)
        <li class="{{ $idx == 0 ? 'active' : '' }}">
            <a role="tab" data-toggle="tab" href="#dconfig_{{ $team->id }}">{{ $team->name }}</a>
        </li>
        @endforeach
        </ul>

        <div class="tab-content">
            <p class="text-blue">{{ trans('project::message.d_config_description') }}</p>
            @foreach ($teamsPermiss as $idx => $team)
            <div role="tabpanel" class="tab-pane {{ $idx == 0 ? 'active' : '' }}" id="dconfig_{{ $team->id }}">
                @foreach ($typeMembers as $key => $label)
                <div class="form-group row">
                    <label class="col-md-2 col-sm-3">{{ $label }}</label>
                    <div class="col-md-10 col-sm-9">
                        <input type="number" name="config[{{ $team->id }}][{{ $key }}]" min="0" class="form-control"
                               value="{{ isset($dConfig[$team->id][$key]) ? $dConfig[$team->id][$key] : null }}">
                    </div>
                </div>
                @endforeach
            </div>
            @endforeach
            <div class="form-group text-center">
                <button type="submit" class="btn btn-primary">
                    {{ trans('project::view.Save') }}
                </button>
            </div>
        </div>
        @endif
    </div>
</form>

@endsection

