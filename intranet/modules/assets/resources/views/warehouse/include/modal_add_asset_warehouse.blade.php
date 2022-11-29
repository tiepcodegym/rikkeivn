<div class="modal fade in" data-backdrop="static" id="modal_add_asset_warehouse">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form_add_asset_warehouse" method="POST" action="{{ route('asset::asset.warehouse.save') }}" accept-charset="UTF-8" autocomplete="off">
                {!! csrf_field() !!}
                <div class="modal-header">
                    <h3 class="modal-title">{{ trans('asset::view.Add asset warehouse') }}</h3>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label required">{{ trans('asset::view.Asset code warehouse') }} <em>*</em></label>
                        <div class="input-box">
                            <input type="hidden" name="item[id]" id="warehouse_id" class="form-control" />
                            <input type="text" name="item[code]" class="form-control"  id="warehouse_code"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label required">{{ trans('asset::view.Asset name warehouse') }} <em>*</em></label>
                        <div class="input-box">
                            <input type="text" name="item[name]" class="form-control" id="warehouse_name" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label required">{{ trans('asset::view.Address warehouse') }} <em>*</em></label>
                        <div class="input-box">
                            <textarea name="item[address]" class="form-control" cols="30" rows="5" id="warehouse_address"></textarea>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="control-label required">Nhân viên phụ trách <em>*</em></label>
                        <div class="input-box">
                            <select name="item[manager_id]" class="form-control select-search-employee" id="warehouse_manager_id">
                                <option value=""></option>
                                @foreach ($employees as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>  
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label required">Chi nhánh <em>*</em></label>
                        <div class="input-box">
                            <select name="item[branch]" class="form-control" id="warehouse_branch" >
                                <option value=""></option>
                                @foreach ($branchs as $item)
                                    <option value="{{ $item }}">{{ $item }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('asset::view.Close') }}</button>
                    <button type="submit" class="btn btn-primary pull-right btn-submit">{{ trans('asset::view.Save') }}</button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>

