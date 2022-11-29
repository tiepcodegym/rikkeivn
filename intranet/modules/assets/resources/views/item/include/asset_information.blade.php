<?php
    use Carbon\Carbon;
    use Rikkei\Assets\Model\AssetItem;

    $labelStates = AssetItem::labelStates();
?>
<div class="box-header with-border box-header-modal">
    <h3 class="box-title"><i class="fa fa-globe"></i> {{ trans('asset::view.Asset information') }}</h3>
</div>
<div class="box-body">
    <div class="box-body no-padding">
        <div class="table-responsive">
            <table class="table table-striped dataTable table-bordered table-hover table-grid-data" id="table_asset">
                <thead>
                <tr>
                    <th class="width-25">{{ trans('core::view.NO.') }}</th>
                    <th class="width-70">{{ trans('asset::view.Asset code') }}</th>
                    <th class="width-100">{{ trans('asset::view.Asset name') }}</th>
                    <th class="width-90">{{ trans('asset::view.Asset category') }}</th>
                    <th class="width-90">{{ trans('asset::view.Asset user') }}</th>
                    <th class="width-90">{{ trans('asset::view.Warehouse') }}</th>
                    <th class="width-70">{{ trans('asset::view.Purchase date') }}</th>
                    <th class="width-120">{{ trans('asset::view.State') }}</th>
                </tr>
                </thead>
                <tbody>
                    <?php $i = 0; ?>
                    @foreach($assetItem as $item)
                        <?php $i++;
                        $purchaseDate = '';
                        if ($item->purchase_date) {
                            $purchaseDate = Carbon::parse($item->purchase_date)->format('d-m-Y');
                        }
                        ?>
                        <tr>
                            <td>{{ $i }}</td>
                            <td>{{ $item->code }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->category_name }}</td>
                            <td>{{ isset($item->employee_name) ? $item->employee_name : '' }}</td>
                            <td>{{ $item->warehoue_name }}</td>
                            <td>{{ $purchaseDate }}</td>
                            <td>{{ $labelStates[$item->state] }}</td>
                        </tr>
                        <input type="hidden" name="asset_category_id[]" value="{{ $item->category_id }}">
                        <input type="hidden" name="asset_id[]" value="{{ $item->id }}">
                    @endforeach
                </tbody>
            </table>
            <!-- /.table -->
        </div>
    </div>
</div>
