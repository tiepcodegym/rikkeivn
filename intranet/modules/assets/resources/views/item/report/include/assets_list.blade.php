<?php
    use Rikkei\Core\View\CoreUrl;
?>
<label class="hidden" id="no_asset-error"  style="margin-top: 3px; color: red">{{ trans('asset::message.Please select a asset item') }}</label>
<table class="table table-striped dataTable table-bordered table-hover table-grid-data asset-table-report" style="width: 100%;">
    <thead>
        <tr>
            <th class="width-20 text-center">
                @if(isset($assetItems) && count($assetItems))
                    <input type="checkbox" class="checkbox-all-report" name="" value="">
                @endif
            </th>
            <th class="width-80">{{ trans('asset::view.Asset code') }}</th>
            <th class="width-120">{{ trans('asset::view.Asset name') }}</th>
            <th class="width-120">{{ trans('asset::view.Received date') }}</th>
        </tr>
    </thead>
    <tbody class="table-body">
        @if(isset($assetItems) && count($assetItems))
            @foreach($assetItems as $item)
                <tr>
                    <td >
                        {{ $item->id }}
                    </td>
                    <td>{{ $item->code }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->received_date }}</td>
                </tr>
            @endforeach
        @else
            <tr>
                <td class="hidden"></td>
                <td class="hidden"></td>
                <td class="hidden"></td>
                <td colspan="4" class="text-center">
                    <h2 class="no-result-grid">{{ trans('asset::view.No results data') }}</h2>
                </td>
            </tr>
        @endif
    </tbody>
</table>


