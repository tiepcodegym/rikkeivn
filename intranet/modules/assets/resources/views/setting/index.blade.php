@extends('layouts.default')

@section('title')
    {{ trans('asset::view.Asset config') }}
@endsection

@section('css')

@endsection

@section('content')

<div class="box box-rikkei">
    <div class="box-header">
        <h2 class="box-title">{{ trans('asset::view.Asset config') }}</h2>
    </div>
    <div class="box-body">
        <?php
        $branchCodes = \Rikkei\Team\Model\Team::listPrefixBranch();
        $keyAssetAlertDb = Rikkei\Assets\View\AssetConst::KEY_DB_DAYS_ALERT_OOD;
        $configDaysAlertOod = Rikkei\Assets\View\AssetConst::getConfigDaysOOD();
        $codeIdx = 0;
        ?>
        <h5><b>{{ trans('asset::view.Days before alert out of date') }}</b></h5>
        <form id="form-system-css-mail" method="post" action="{{ route('core::setting.system.data.save') }}"
            class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
        @foreach ($branchCodes as $code => $name)
            {!! csrf_field() !!}
            <div class="row">
                <div class="col-md-11">
                    <div class="form-group form-label-left row">
                        <label  class="col-sm-2 control-label">{{ $name }} </label>
                        <div class="col-md-10">
                            <input name="item[{{ $keyAssetAlertDb }}][{{ $code }}]" class="form-control input-field"
                                type="number" min="0" max="1000"
                                id="cssmail" value="{{ $configDaysAlertOod[$code] }}" />
                            <p class="hint">{{ trans('asset::view.Days before alert out of date') }} </p>
                        </div>
                    </div>
                </div>

                @if ($codeIdx == 0)
                <div class="col-md-1">
                    <button class="btn-add margin-bottom-5" type="submit">{{ trans('core::view.Save') }} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                </div>
                @endif
                <?php $codeIdx++; ?>
            </div>
        @endforeach
        </form>
    </div>
</div>

@endsection

@section('script')
    
@endsection
