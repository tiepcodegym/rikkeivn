<?php
    use Rikkei\Assets\View\AssetPermission;

    $allowAddAndEdit = AssetPermission::createAndEditPermision();
    $disabled = '';
    if (!$allowAddAndEdit) {
        $disabled = 'disabled';
    }
?>
<div class="modal fade in" data-backdrop="static" id="modal_add_asset_category">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form_add_asset_category" method="POST" action="{{ route('asset::asset.category.save') }}" accept-charset="UTF-8" autocomplete="off">
                {!! csrf_field() !!}
                <input type="hidden" name="id" id="asset_category_id" />
                <div class="modal-header">
                    <h3 class="modal-title">{{ trans('asset::view.Add asset category') }}</h3>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label required">{{ trans('asset::view.Asset category name') }} <em>*</em></label>
                        <div class="input-box">
                            <input type="text" name="item[name]" class="form-control" id="asset_category_name" {{ $disabled }} />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label required">{{ trans('asset::view.Asset group') }} <em>*</em></label>
                        <div class="input-box">
                            <select class="select-search form-control" name="item[group_id]" id="asset_group_id" style="width: 100%;" {{ $disabled }}>
                                @if (count($assetGroupsList))
                                    @foreach ($assetGroupsList as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label required">{{ trans('asset::view.Asset code prefix') }} <em>*</em></label>
                        <div class="input-box">
                            <input type="text" name="item[prefix_asset_code]" class="form-control" id="prefix_asset_code" {{ $disabled }} />
                        </div>
                    </div>
                    <div class="form-group">
                        <label><input type="checkbox" name="item[is_default]" value="1" id="is_default"> {{ trans('asset::view.Set default') }}</label>
                    </div>
                    <div class="form-group">
                        <label class="control-label">{{ trans('asset::view.Note') }}</label>
                        <div class="input-box">
                            <textarea name="item[note]" class="form-control textarea-100" id="asset_category_note" {{ $disabled }}></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('asset::view.Close') }}</button>
                    @if ($allowAddAndEdit)
                        <button type="submit" class="btn btn-primary pull-right btn-submit">{{ trans('asset::view.Save') }}</button>
                    @endif
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>