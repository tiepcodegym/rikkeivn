<?php
$quantity = 1;
$count = 0;
if (isset($request)) {
    $quantity = $request->quantity;
    $count = $request->count_asset;
}
$noAsset = $count == 0 || $count < $quantity;
$urlSearchAssetByEmpId = route('asset::asset.search.ajax_by_emp_id');
?>

@if (!isset($request))
<table id="asset_allowcate_item_tmp">
    <tbody>
@endif
        @if ($permissAllowcate)
            @for ($i = 0; $i < $quantity; $i++)
                <tr class="request-item{{ $noAsset ? ' no-asset' : '' }}" data-cat="{{ isset($request) ? $request->asset_category_id : null }}">
                    @if ($i == 0)
                        <td rowspan="{{ $quantity }}" class="rowspan" >
                            <select class="form-control category" name="cate_id[]"
                                    data-cat-id="{{ isset($request) ? $request->asset_category_id : null }}"
                                    data-remote-url="{{ $urlUpdateCate }}">
                                @if (count($assetCategoriesList))
                                    @foreach ($assetCategoriesList as $item)
                                        <option value="{{ $item->id }}" {{ (isset($request) && $item->id == $request->asset_category_id) ? 'selected' : '' }}>{{ $item->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="error js-err-cateId" data-cat-id="{{ isset($request) ? $request->asset_category_id : null }}"></div>
                        </td>
                        <td rowspan="{{ $quantity }}" class="rowspan">
                            <input type="number" class="form-control update-request-qty" name="qty[]"
                                   value="{{ $quantity }}" data-url="{{ $urlUpdateQty }}"
                                   data-cat-id="{{ isset($request) ? $request->asset_category_id : null }}" min="1" max="10">
                        </td>
                    @endif
                    <td>
                        <select class="select-search-asset form-control" name="asset_id[]" data-remote-url="{{ $urlSearchAssetByEmpId }}"
                                style="width: 230px;">
                        </select>
                    </td>
                    @if ($i == 0)
                        <td rowspan="{{ $quantity }}" class="rowspan">
                            <button class="btn btn-danger btn-del-item" type="button"><i class="fa fa-trash"></i></button>
                        </td>
                    @endif
                </tr>
            @endfor
        @else
        <tr class="{{ $noAsset ? 'no-asset' : ''}}">
            <td>{{ isset($request) ? $request->asset_category_name : null }}</td>
            <td>{{ isset($request) ? $request->quantity : null }}</td>
        </tr>
        @endif
@if (!isset($request))
    </tbody>
</table>
@endif
