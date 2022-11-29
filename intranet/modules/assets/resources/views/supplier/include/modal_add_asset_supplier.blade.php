<?php
    use Rikkei\Assets\View\AssetPermission;

    $allowAddAndEdit = AssetPermission::createAndEditPermision();
    $disabled = '';
    if (!$allowAddAndEdit) {
        $disabled = 'disabled';
    }
?>
<div class="modal fade in" data-backdrop="static" id="modal_add_asset_supplier">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="form_add_asset_supplier" method="POST" action="{{ route('asset::asset.supplier.save') }}" accept-charset="UTF-8" autocomplete="off">
                {!! csrf_field() !!}
                <input type="hidden" name="id" id="asset_supplier_id" />
                <div class="modal-header">
                    <h3 class="modal-title">{{ trans('asset::view.Add asset supplier') }}</h3>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label required">{{ trans('asset::view.Supplier code') }} <em>*</em></label>
                        <div class="input-box">
                            <input type="text" name="item[code]" class="form-control" id="asset_supplier_code" readonly {{ $disabled }} />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label required">{{ trans('asset::view.Supplier name') }} <em>*</em></label>
                        <div class="input-box">
                            <input type="text" name="item[name]" class="form-control" id="asset_supplier_name" {{ $disabled }} />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label required">{{ trans('asset::view.Address') }} <em>*</em></label>
                        <div class="input-box">
                            <input type="text" name="item[address]" class="form-control" id="asset_supplier_address" {{ $disabled }} />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label">{{ trans('asset::view.Phone') }}</label>
                        <div class="input-box">
                            <input type="text" name="item[phone]" class="form-control" id="asset_supplier_phone" {{ $disabled }} />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label">{{ trans('asset::view.Email') }}</label>
                        <div class="input-box">
                            <input type="text" name="item[email]" class="form-control" id="asset_supplier_email" {{ $disabled }} />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label">{{ trans('asset::view.Website') }}</label>
                        <div class="input-box">
                            <input type="text" name="item[website]" class="form-control" id="asset_supplier_website" {{ $disabled }} />
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