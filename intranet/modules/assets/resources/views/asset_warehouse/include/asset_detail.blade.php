<?php
use Rikkei\Assets\Model\AssetCategory;

$assetCategoriesList = AssetCategory::getAssetCategoriesList();
$urlSearchAsset = route('asset::asset.search.ajax_by_warehouse');
?>

{!! Form::open(['method' => 'post', 'route' => 'asset::asset.save_asset_to_warehouse', 'class' => 'no-validate', 'id' => 'form-asset-warehouse']) !!}
<table class="table dataTable table-bordered table-grid-data" id="list-asset-category">
    <thead>
        <tr>
            <th width="20">
                <input type="checkbox" class="js-all-checkbox">
            </th>
            <th>{{ trans('asset::view.Asset category request') }} <i
                    class="fa fa-spin fa-refresh hidden" id="update_cate_loading"></i></th>
            <th width="100">{{ trans('asset::view.Quantity') }} <i
                    class="fa fa-spin fa-refresh hidden" id="update_qty_loading"></i></th>
            <th style="width: 230px;">{{ trans('asset::view.Asset information') }}</th>
            {{-- <th style="width: 50px;"></th> --}}
        </tr>
    </thead>
    <tbody>
        @if (count($assets))
        @foreach ($assets as $key => $item)
            @if (in_array($item->branch, $empBranch))
                @if ($item->unallocate > 0)
                    @for ($i = 0; $i < $item->unallocate; $i++)
                    <tr class="request-item" data-cat="{{ $item->asset_category_id }}" data-id="{{ $item->id }}">
                        @if ($i == 0)
                            <td rowspan="{{ $item->unallocate }}" class="rowspan">
                                <input type="checkbox" class="js-checkbox" data-id="{{ $item->id }}" name="arr_checkbox[]" value="{{ $item->id }}">
                            </td>
                            <td rowspan="{{ $item->unallocate }}" class="rowspan" >
                                {{ $item->assetCategory->name ? $item->assetCategory->name : '' }}
                            </td>
                            <td rowspan="{{ $item->unallocate }}" class="rowspan">
                                <input type="number" class="form-control update-request-qty" name="qty{{$item->id}}"
                                        value="{{ $item->unallocate }}"
                                        data-cat-id="{{ $item->asset_category_id }}" data-id="{{ $item->id }}" min="0" max="20">
                            </td>
                        @endif
                        <td>
                            <select class="select-search-asset form-control" name="asset_id{{$item->id}}[]" data-remote-url="{{ $urlSearchAsset }}"
                                data-id="{{ $item->id }}" data-branch="{{ $item->branch }}"
                                style="width: 230px;">
                            </select>
                        </td>
                        {{-- @if ($i == 0)
                            <td rowspan="{{ $item->unallocate }}" class="rowspan">
                                <button class="btn btn-danger btn-del-item" type="button" data-cat="{{ $item->asset_category_id }}" data-id="{{ $item->id }}"><i class="fa fa-trash"></i></button>
                            </td>
                        @endif --}}
                    </tr>
                    @endfor
                @endif
            @endif
        @endforeach
        @else
            <tr>
                <td colspan="3">
                    <h4 class="text-center">{{ trans('asset::view.No results data') }}</h4>
                </td>
            </tr>
        @endif
    </tbody>
</table>
<label class="asset-error request-category-duplicate-error">{{ trans('asset::message.The request asset category can not be duplicated') }}</label>
<div class="margin-bottom-15"></div>
@if (count($assets))
<div class="form-group text-center">
    <button type="submit" class="btn btn-primary" id="btn_req_allocation"
        data-noti="{{ trans('asset::message.Please input completely information') }}"
        data-noti_branch="{{ trans('asset::message.There are assets not belonging to employees\' branches. Are you sure want to continue?') }}">
        {{ trans('asset::view.Allocation') }}</button>
</div>
<input type="hidden" value="{{ $empId }}" name="empId">
@endif
{!! Form::close() !!}