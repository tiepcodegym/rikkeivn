<?php
use Rikkei\Assets\View\AssetConst;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Assets\Model\AssetCategory;
use Rikkei\Team\View\TeamList;
use Rikkei\Assets\Model\AssetItem;

$assetCategories = AssetCategory::getAssetCategoriesList();
$teamsOptionAll = TeamList::toOption(null, true, false);
$assetCategoryDefault = '';
$assetItems = AssetItem::getAssetItemsByCategory();
?>
<div id="modalReport-{{ AssetConst::REPORT_TYPE_DETAIL_ON_ASSET_USE_PROCESS }}" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <!-- Modal content-->
        <div class="modal-content">
            <form id="" class="form-report-use-process" method="POST"
                  accept-charset="UTF-8" autocomplete="off">
                {!! csrf_field() !!}
                <input type="hidden" name="report_type"
                       value="{{ AssetConst::REPORT_TYPE_DETAIL_ON_ASSET_USE_PROCESS }}">
                <div class="modal-header">
                    <h4 class="modal-title">{{ trans('asset::view.Report parameters') }}</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label required">{{ trans('asset::view.Team') }} <em>*</em></label>
                        <div class="input-box">
                            <select class="form-control select-search input-select-team-member" name="team_id"
                                    autocomplete="off" style="width: 100%;" id="team_id">
                                <option value="">{{ trans('asset::view.All team') }}</option>
                                @foreach($teamsOptionAll as $option)
                                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-box">
                            <div class="row">
                                <div class="col-sm-6">
                                    <label class="control-label">{{ trans('asset::view.Statistics from date') }}</label>
                                    <div class='input-group date statistic_date_picker'>
                                            <span class="input-group-addon">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                            </span>
                                        <input type="text" name="date_from" class="form-control" id="date_from"/>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label class="control-label">{{ trans('asset::view.To date') }}</label>
                                    <div class="input-group date statistic_date_picker">
                                            <span class="input-group-addon">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                            </span>
                                        <input type="text" name="date_to" class="form-control" id="date_to"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label required">{{ trans('asset::view.Asset category') }}
                            <em>*</em></label>
                        <div class="input-box">
                            <select name="category_id" id="category_id_report" class="form-control select-search"
                                    style="width: 100%;">
                                <option value="">{{ trans('asset::view.All asset category') }}</option>
                                @if (count($assetCategories))
                                    @foreach ($assetCategories as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="input-box" id="group_assets">
                            @include('asset::item.report.include.assets_list', ['assetItems' => $assetItems])
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left"
                            data-dismiss="modal">{{ trans('asset::view.Close') }}</button>
                    <button type="submit"
                            class="btn btn-primary pull-right btn-submit submit-report">{{ trans('asset::view.Select') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
