<?php
    use Rikkei\Assets\View\AssetPermission;

    $allowAddAndEdit = AssetPermission::createAndEditPermision();
    $disabled = '';
    if (!$allowAddAndEdit) {
        $disabled = 'disabled';
    }
?>
<div class="modal fade in" data-backdrop="static" id="modal_add_asset_attribute">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form_add_asset_attribute" method="POST" action="{{ route('asset::asset.attribute.save') }}" accept-charset="UTF-8" autocomplete="off">
                {!! csrf_field() !!}
                <input type="hidden" name="id" id="asset_attribute_id" />
                <div class="modal-header">
                    <h3 class="modal-title">{{ trans('asset::view.Add asset attribute') }}</h3>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label required">{{ trans('asset::view.Asset attribute name') }} <em>*</em></label>
                        <div class="input-box">
                            <input type="text" name="item[name]" class="form-control" id="asset_attribute_name" {{ $disabled }} />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label required">{{ trans('asset::view.Asset category') }} <em>*</em></label>
                        <div class="input-box">
                            <select class="select-search form-control" name="item[category_id]" id="asset_category_id" style="width: 100%;" {{ $disabled }}>
                                @if (count($assetCategoriesList))
                                    @foreach ($assetCategoriesList as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label">{{ trans('asset::view.Note') }}</label>
                        <div class="input-box">
                            <textarea name="item[note]" class="form-control textarea-100" id="asset_attribute_note" {{ $disabled }}></textarea>
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