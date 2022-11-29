<div id="importSerialNumber" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <form action="{{ route('asset::asset.import-serial-number') }}" method="post" enctype="multipart/form-data" id="form-import-supplier">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <div class="modal-content">
                <div class="modal-body">
                    <h3 class="text-center">Import serial</h3>
                    <hr>
                    <div class="form-group">
                        <label class="control-label">{{ trans('asset::view.Choose file import') }} (xlsx)</label>
                        <div class="input-box">
                            <input type="file" name="file_upload_serial" class="form-control" placeholder="{{ trans('asset::view.Add file') }}" required/>
                        </div>
                    </div>
                    <br/>
                    <div class="text-center">
                        <p>Định dạng file</p>
                        <img src="{{ URL::asset('asset_managetime/images/template/import_serial_number.png') }}"
                            style="width:70%;"
                            alt="import serial number"
                        >
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('core::view.Close') }}</button>
                        <button type="submit" class="btn btn-primary pull-right">{{ trans('asset::view.Import') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
