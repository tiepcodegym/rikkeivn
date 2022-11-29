<?php
use Rikkei\Core\Model\CoreConfigData;
?>
@extends('layouts.default')
@section('title', 'Project Setting')

@section('content')
<div class="col-md-12">
    <div class="box box-info">
        <div class="box-body">
            <form id="form-system-general" method="post" action="{{ route('project::setting.general') }}"
                class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}
                <?php $itemKey = 'project.me.baseline_date'; ?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group row">
                            <label for="project.baseline_date" class="col-md-4 control-label">{{trans('core::view.Project baseline date')}}</label>
                            <div class="col-md-6">
                                <input name="item[{{ $itemKey }}]" class="form-control input-field" type="number" min="1" max="31" 
                                    id="project.baseline_date" value="{{ CoreConfigData::getValueDb($itemKey) }}" />
                            </div>
                            <div class="col-md-2">
                                <button class="btn-add" type="submit">{{trans('core::view.Save')}} 
                                    <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
