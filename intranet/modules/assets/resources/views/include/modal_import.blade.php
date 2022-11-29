<div id="importFile" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <form action="{{ route($route) }}" method="post" enctype="multipart/form-data" id="form-import-supplier">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label">{{ trans('asset::view.Choose file import') }}</label>
                        <div class="input-box">
                            <input type="file" name="file_upload" class="form-control" placeholder="{{ trans('asset::view.Add file') }}" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('core::view.Close') }}</button>
                        <button type="submit" class="btn btn-primary pull-right">{{ trans('asset::view.Import') }}</button>
                    </div>
                    <hr>
                    <div class="form-group">
                        <?php if (!empty($importGuide)): ?>
                            {!! $importGuide !!}
                        <?php else: ?>
                            {!! trans('asset::view.import asset guide') !!}
                        <?php endif ?>
                        <div>
                            <strong>{{ trans('asset::view.Format excel file') }}: </strong>
                            <?php if (!empty($type)): ?>
                                <a href="{{ asset('manage_asset/files/Import_' . $type . '.xlsx') }}">{{ trans('asset::view.Download') }} <i class="fa fa-download"></i></a>                                
                            <?php else: ?>
                                <a href="{{ asset('manage_asset/files/mau_import_tai_san.xlsx') }}">{{ trans('asset::view.Download') }} <i class="fa fa-download"></i></a>
                            <?php endif ?>

                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>