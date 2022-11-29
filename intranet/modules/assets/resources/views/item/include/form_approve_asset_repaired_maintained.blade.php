<?php
    use Carbon\Carbon;
    use Rikkei\Core\View\View;
    use Rikkei\Assets\Model\AssetItem;
    use Rikkei\Core\View\CoreUrl;

    $labelStates = AssetItem::labelStates();
?>
<form id="" method="POST" action="{{ route('asset::asset.confirm-repaired-maintained') }}" accept-charset="UTF-8" autocomplete="off">
    {!! csrf_field() !!}
    <input type="hidden" name="state" value="{{ AssetItem::STATE_REPAIRED_MAINTAINED }}">
    <div class="modal-header">
        <h3 class="modal-title">{{ trans('asset::view.Approval of asset repaired, maintained') }}</h3>
    </div>
    <div class="modal-body">
        <div class="box box-solid box-modal">
            <div class="box-header with-border box-header-modal">
                <h3 class="box-title"><i class="fa fa-globe"></i> {{ trans('asset::view.Asset repaired, maintained list') }}</h3>
            </div>
            <div class="box-body">
                <div class="table-responsive" style="overflow-x: auto; padding-left: 0px !important; padding-right: 0px !important;">
                    <table class="table table-striped dataTable table-bordered table-hover table-grid-data asset-table" style="width: 1145px; padding-bottom: 20px;" id="table-asset-approval">
                        <thead>
                            <tr>
                                <th class="width-20 text-center checkbox-all">
                                    @if(isset($assetItems) && count($assetItems))
                                        <input type="checkbox" class="minimal" name="" value="">
                                    @endif
                                </th>
                                <th class="width-25">{{ trans('core::view.NO.') }}</th>
                                <th class="width-70">{{ trans('asset::view.Asset code') }}</th>
                                <th class="width-100">{{ trans('asset::view.Asset name') }}</th>
                                <th class="width-120">{{ trans('asset::view.Asset category') }}</th>
                                <th class="width-100">{{ trans('asset::view.Asset user') }}</th>
                                <th class="width-130">{{ trans('asset::view.Position') }}</th>
                                <th class="width-100">{{ trans('asset::view.State') }}</th>
                                <th class="width-90">{{ trans('asset::view.Suggest repair, maintenance date') }}</th>
                                <th class="width-145">{{ trans('asset::view.Suggest repair, maintenance reason') }}</th>
                            </tr>
                        </thead>
                        <tbody class="table-body">
                            @if(isset($assetItems) && count($assetItems))
                                <?php $i = 1; ?>
                                @foreach($assetItems as $item)
                                    <?php
                                        $changeDate = '';
                                        if ($item->change_date) {
                                            $changeDate = Carbon::parse($item->change_date)->format('d-m-Y');
                                        }
                                    ?>
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" class="minimal" name="item[{{ $item->id }}]" value="{{ $item->id }}">
                                        </td>
                                        <td>{{ $i }}</td>
                                        <td>{{ $item->code }}</td>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->category_name }}</td>
                                        <td>{{ $item->user_name }}</td>
                                        <td>{{ $item->role_name }}</td>
                                        <td>{{ $labelStates[$item->state] }}</td>
                                        <td>{{ $changeDate }}</td>
                                        <td>{!! View::nl2br($item->reason) !!}</td>
                                    </tr>
                                    <?php $i++; ?>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="10" class="text-center">
                                        <h2 class="no-result-grid">{{ trans('asset::view.No results data') }}</h2>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- /. box -->
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('asset::view.Close') }}</button>
        <div class="pull-right">
            <button type="submit" class="btn btn-success btn-submit btn-approve-asset" name="approve" value="1" disabled><i class="fa fa-check"></i> {{ trans('asset::view.Approve') }}</button>
        </div>
    </div>
</form>

<script src="{{ CoreUrl::asset('manage_asset/js/manage_asset.approve.js') }}"></script>