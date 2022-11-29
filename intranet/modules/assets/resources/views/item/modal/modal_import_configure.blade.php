<div id="importFileConfigure" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <form action="{{ route('asset::asset.importFileConfigure') }}" method="post" enctype="multipart/form-data" id="form-import-supplier">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <div class="modal-content">
                <div class="modal-body">
                    <h3 class="text-center">Import file cấu hình máy tính</h3>
                    <hr>
                    <div class="form-group">
                        <label class="control-label">1. Chọn loại file</label>
                        <div class="radio">
                            <label><input type="radio" name="type" value="1" checked>Thống kê Case</label>
                        </div>
                        <div class="radio">
                            <label><input type="radio" name="type" value="2">Nhân viên onsite khai báo</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label">2. {{ trans('asset::view.Choose file import') }}</label>
                        <div class="input-box">
                            <input type="file" name="file_upload" class="form-control" placeholder="{{ trans('asset::view.Add file') }}" required />
                        </div>
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