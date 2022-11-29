<?php
    use Rikkei\Assets\View\AssetPermission;

    $allowAddAndEdit = AssetPermission::createAndEditPermision();
    $disabled = '';
    if (!$allowAddAndEdit) {
        $disabled = 'disabled';
    }
?>
<div class="modal fade in" data-backdrop="static" id="modal_add_asset_group">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form_add_asset_group" method="POST" action="{{ route('asset::asset.group.save') }}" accept-charset="UTF-8" autocomplete="off">
                {!! csrf_field() !!}
                <input type="hidden" name="id" id="asset_group_id" />
                <div class="modal-header">
                    <h3 class="modal-title">{{ trans('asset::view.Add asset group') }}</h3>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label required">{{ trans('asset::view.Asset group name') }} <em>*</em></label>
                        <div class="input-box">
                            <input type="text" name="item[name]" class="form-control" id="asset_group_name" {{ $disabled }} />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label">{{ trans('asset::view.Note') }}</label>
                        <div class="input-box">
                            <textarea name="item[note]" class="form-control textarea-100" id="asset_group_note" {{ $disabled }}></textarea>
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