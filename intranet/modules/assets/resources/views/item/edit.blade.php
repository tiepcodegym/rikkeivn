@extends('layouts.default')

<?php
    use Carbon\Carbon;
    use Rikkei\Core\View\Form;
    use Rikkei\Core\View\View;
    use Rikkei\Team\View\Config;
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Assets\Model\AssetCategory;
    use Rikkei\Assets\Model\AssetOrigin;
    use Rikkei\Assets\Model\AssetSupplier;
    use Rikkei\Assets\Model\AssetAttribute;
    use Rikkei\Assets\Model\AssetItem;
    use Rikkei\Assets\View\AssetView;
    use Rikkei\Team\View\TeamList;
    use Rikkei\Team\View\Permission;

    $teamsOptionAll = TeamList::toOption(null, true, false);

    $title = trans('asset::view.Add asset');
    if (Form::getData('asset.name')) {
        $title = Form::getData('asset.name');
    }
    $assetCategoriesList = AssetCategory::getAssetCategoriesList();
    $assetOriginList = AssetOrigin::getAssetOriginsList();
    $assetSupplierList = AssetSupplier::getAssetSuppliersList();
    $valueDefaultAssetCategory = null;
    $assetCodePrefix = '';
    $curEmp = Permission::getInstance()->getEmployee();
    $branchCode = AssetView::getRegionByEmp($curEmp->id, true);
    $regionByEmp = AssetView::getPrefixPerson($branchCode);
    if (Form::getData('asset.category_id')) {
        $valueDefaultAssetCategory = Form::getData('asset.category_id');
    } else {
        if (count($assetCategoriesList)) {
            foreach ($assetCategoriesList as $item) {
                $valueDefaultAssetCategory = $item->id;
                $assetCodePrefix = $item->prefix_asset_code;
                break;
            }
        }
    }
    $assetCode = Form::getData('asset.code');
    if (!$assetCode) {
        if (isset($regionByEmp) && $regionByEmp) {
            $assetCodePrefix = $regionByEmp.$assetCodePrefix;
        } else {
            $assetCodePrefix = 'HN'.$assetCodePrefix;
        }
        $maxAssetCode = AssetItem::getMaxAssetCodeByCategory($valueDefaultAssetCategory);
        $maxAssetCode = filter_var($maxAssetCode, FILTER_SANITIZE_NUMBER_INT);
        $maxAssetCode = intval($maxAssetCode);
        $assetCode = AssetView::generateCode($assetCodePrefix, $maxAssetCode);
    }
    $assetAttributesList = AssetAttribute::getAssetAttributesList($valueDefaultAssetCategory);

    $disabled = '';
    $readonly = '';
    if (isset($allowEdit) && !$allowEdit) {
        $disabled = 'disabled';
    }
if (isset($configEdit) && $configEdit) {
        $allowEdit = true;
        $readonly = 'readonly';
        $disabled = '';
    }

    $disabledWarranty = '';
    if (!Form::getData('asset.id') || !Form::getData('asset.purchase_date') || (isset($allowEdit) && !$allowEdit)) {
        $disabledWarranty = 'disabled';
    }

    $configDaysAlertOod = Rikkei\Assets\View\AssetConst::getConfigDaysOOD();
    $configure = Form::getData('asset.configure');
    $configure = explode('|', $configure);
    $textConfig = '';
    foreach ($configure as $item) {
        $textConfig .= $item . "\n";
    }
?>

@section('title')
    {{ $title }}
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/all.css" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('manage_asset/css/style.css') }}" />
    <style>
       input[readonly], select[readonly], select[readonly].select2-hidden-accessible + .select2-container {
            pointer-events: none;
            touch-action: none;
            background-color: #eee !important;
        }
        select[readonly].select2-hidden-accessible + .select2-container .select2-selection {
            background-color: #eee !important;
        }
    </style>
@endsection

@section('content')

    <?php
    $warehouse = Form::getData('asset.warehouse_id');
    ?>

    <div class="row">
        <form action="{{ route('asset::asset.save') }}" method="post" id="form_edit_asset_item" autocomplete="off" enctype="multipart/form-data">
            {!! csrf_field() !!}
            <input type="hidden" name="id" id="asset_id" value="{{ Form::getData('asset.id') }}" />
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h2 class="box-title">{{ trans('asset::view.Asset information') }}</h2>
                        <span class="pull-right">
                            <a href="{{ route('asset::asset.add') }}" class="btn btn-success">
                                <i class="fa fa-plus" aria-hidden="true"></i> {{ trans('asset::view.Add new') }}
                            </a>
                            <a href="{{ route('asset::asset.index') }}" class="btn btn-primary">
                                {{ trans('asset::view.List') }}
                            </a>
                        </span>
                    </div>
                    <div class="box-body">
                        <div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">
                            <div class="row">
                                <div class="col-sm-6 form-group">
                                    <label class="control-label required">{{ trans('asset::view.Asset code') }} <em>*</em></label>
                                    <div class="input-box">
                                        <input type="text" name="item[code]" class="form-control" id="asset_code" value="{{ $assetCode }}" readonly {{ $disabled }} {{ $readonly }}/>
                                    </div>
                                </div>

                                <div class="col-sm-6 form-group">
                                    <label class="control-label required">{{ trans('asset::view.Asset name') }} <em>*</em></label>
                                    <div class="input-box">
                                        <input type="text" name="item[name]" class="form-control" value="{{ Form::getData('asset.name') }}" {{ $disabled }} {{ $readonly }}/>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6 form-group form-group-select2">
                                    <label class="control-label required">{{ trans('asset::view.Asset category') }} <em>*</em></label>
                                    <div class="input-box">
                                        <select name="item[category_id]" id="category_id" class="form-control select-search has-search" {{ $disabled }} {{ $readonly }}>
                                            @if (count($assetCategoriesList))
                                                @foreach ($assetCategoriesList as $item)
                                                    <option value="{{ $item->id }}" {{ $item->id == Form::getData('asset.category_id') ? 'selected' : '' }}>{{ $item->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-6 form-group">
                                    <?php $serial = Form::getData('asset.serial'); ?>
                                    <label class="control-label required">{{ trans('asset::view.Serial') }}</label>
                                    <div class="input-box">
                                        <input type="text" name="item[serial]" class="form-control" value="{{ Form::getData('asset.serial') }}" {{ $disabled }} {{ $readonly }}/>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6 form-group">
                                    <label class="control-label required">{{ trans('asset::view.Address warehouse') }} <em>*</em></label>
                                    <div class="input-box">
                                        <select name="item[warehouse_id]" id="warehouse_id" class="form-control select-search has-search" {{ $disabled }} {{ $readonly }}>
                                            <option value="">&nbsp;</option>
                                            @if (count($warehouseList))
                                                @foreach ($warehouseList as $item)
                                                    <option value="{{ $item->id }}" {{ (isset($warehouse) && $item->id == $warehouse) ? 'selected' : '' }}>{{ $item->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-6 form-group">
                                    <label class="control-label">{{ trans('asset::view.Manage asset person') }}</label>
                                    <div class="input-box">
                                        <select name="item[manager_id]" class="form-control select-search" id="manager_id" data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}" {{ $disabled }} {{ $readonly }}>
                                            @if (Form::getData('asset.manager_id'))
                                                <option value="{{ Form::getData('asset.manager_id') }}" selected>{{ View::getNickName(Form::getData('asset.manager_email')) }}</option>
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6 form-group form-group-select2">
                                    <label class="control-label">{{ trans('asset::view.Origin') }}</label>
                                    <div class="input-box">
                                        <select name="item[origin_id]" id="origin_id" class="form-control select-search has-search" {{ $disabled }} {{ $readonly }}>
                                            <option value="">&nbsp;</option>
                                            @if (count($assetOriginList))
                                                @foreach ($assetOriginList as $item)
                                                    <option value="{{ $item->id }}" {{ $item->id == Form::getData('asset.origin_id') ? 'selected' : '' }}>{{ $item->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-6 form-group form-group-select2">
                                    <label class="control-label">{{ trans('asset::view.Supplier') }}</label>
                                    <div class="input-box">
                                        <select name="item[supplier_id]" id="supplier_id" class="form-control select-search has-search" {{ $disabled }} {{ $readonly }}>
                                            <option value="">&nbsp;</option>
                                            @if (count($assetSupplierList))
                                                @foreach ($assetSupplierList as $item)
                                                    <option value="{{ $item->id }}" {{ $item->id == Form::getData('asset.supplier_id') ? 'selected' : '' }}>{{ $item->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-4 form-group">
                                    <label class="control-label">{{ trans('asset::view.Purchase date') }}</label>
                                    <div class="input-box">
                                        <div class='input-group date' id="purchase_datetime_picker">
                                            <span class="input-group-addon">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                            </span>
                                            <input type="text" name="item[purchase_date]" class="form-control" id="purchase_date" value="{{ Form::getData('asset.purchase_date') }}" {{ $disabled }} {{ $readonly }}/>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-4 form-group">
                                    <label class="control-label">{{ trans('asset::view.Warranty priod') }}</label>
                                    <div class="input-box">
                                        <input type="text" name="item[warranty_priod]" class="form-control" value="{{ Form::getData('asset.warranty_priod') }}" {{ $disabled }} {{ $readonly }} id="warranty_priod"/>
                                    </div>
                                </div>

                                <div class="col-sm-4 form-group">
                                    <label class="control-label">{{ trans('asset::view.Warranty exp date') }}</label>
                                    <div class="input-box">
                                        <div class='input-group date' id="warranty_datetime_picker">
                                            <span class="input-group-addon">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                            </span>
                                            <input type="text" name="item[warranty_exp_date]" class="form-control" id="warranty_exp_date" value="{{ Form::getData('asset.warranty_exp_date') }}" disabled />
                                            <input type="hidden" name="item[warranty_exp_date]" class="form-control" id="warranty_exp_date" value="{{ Form::getData('asset.warranty_exp_date') }}" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-4 form-group">
                                    <label class="control-label">{{ trans('asset::view.Out of date') }}</label>
                                    <div class="input-box">
                                        <div class='input-group date datetime-picker' id="out_of_date_datetime_picker">
                                            <span class="input-group-addon">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                            </span>
                                            <input type="text" name="item[out_of_date]" class="form-control" value="{{ Form::getData('asset.out_of_date') }}" {{ $disabled }} {{ $readonly }}/>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4 form-group">
                                    <label class="control-label">{{ trans('asset::view.Days before alert out of date') }}</label>
                                    <div class="input-box">
                                        <?php
                                        $dayAlertBefore = Form::getData('asset.days_before_alert_ood');
                                        $dayAlertBefore = $dayAlertBefore !== null ? $dayAlertBefore : $configDaysAlertOod[$branchCode]
                                        ?>
                                        <input type="number" min="0" max="1000" name="item[days_before_alert_ood]" class="form-control"
                                               value="{{ $dayAlertBefore ? $dayAlertBefore : null }}" {{ $disabled }} {{ $readonly }} />
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label">{{ trans('asset::view.Specification') }}</label>
                                <div class="input-box">
                                    <textarea name="item[specification]" class="form-control textarea-100" {{ $disabled }} {{ $readonly }}>{{ Form::getData('asset.specification') }}</textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label">{{ trans('asset::view.Configure') }}</label>
                                <div class="input-box">
                                    <textarea name="item[configure]" class="form-control textarea-100" {{ $disabled }} rows="5">{{ trim($textConfig, "\n") }}</textarea>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label">{{ trans('asset::view.Note') }}</label>
                                <div class="input-box">
                                    <textarea name="item[note]" class="form-control textarea-100" {{ $disabled }} {{ $readonly }}>{{ Form::getData('asset.note') }}</textarea>
                                </div>
                            </div>
                            <div id="asset_attributes_list">
                                @include('asset::item.include.asset_attributes_list')
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        @if (isset($allowEdit) && $allowEdit)
                            <div class="col-md-10 col-md-offset-2">
                                <button type="submit" class="btn btn-primary" name="submit"><i class="fa fa-floppy-o"></i> {{ trans('asset::view.Save') }}</button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Box history -->
    @if (isset($assetHistories) && count($assetHistories))
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary box-solid">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ trans('asset::view.History') }}</h3>
                    </div>
                    <div class="box-body" style="max-height:500px; overflow:auto">
                        @foreach ($assetHistories as $item)
                            <div class="col-md-12">
                                <p class="author"><strong>- {{ $item->creator_name }} ({{ View::getNickName($item->creator_email) }})</strong> <i>at {{ Carbon::parse($item->created_at)->format('d-m-Y H:i:s') }}</i></p>
                                <pre class="date">{!! $item->note !!}</pre>
                                @if ($item->change_date)
{{--                                    <p class="date">--}}
{{--                                        {{ trans('asset::view.Date:') }} {{ Carbon::createFromFormat('Y-m-d', $item->change_date)->format('d-m-Y') }}--}}
{{--                                    </p>--}}
                                @endif
                                @if ($item->change_reason)
                                    <p>{{ trans('asset::view.Reason:') }} {!! View::nl2br($item->change_reason) !!}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif
    <?php
        // Remove flash session
        Form::forget();
    ?>
@endsection

@section('script')
    <script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/icheck.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
    <script src="{{ CoreUrl::asset('manage_asset/js/asset/create.js') }}"></script>

    <script type="text/javascript">
        var urlAjaxGetAssetAttributes = '{{ route('asset::asset.ajax-get-attribute-and-code') }}';
        var requiredText = '{{ trans('asset::message.The field is required') }}';
        var rangelengthText = '{{ trans('asset::message.The field not be greater than :number characters', ['number' => 100]) }}';
        var minNumber = '{{ trans('asset::message.Warranty priod be greater than :number', ['number' => 0]) }}';
        var requiredNumber = '{{ trans('asset::message.Warranty priod be type number') }}';
        var readonly = '{{ $readonly }}';
    </script>
@endsection