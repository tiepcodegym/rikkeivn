<div class="row welfare-file-upload">
    <div class="col-sm-12">
        <div class="col-md-6">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-upload"></i> {{ trans('welfare::view.Upload File Welfare') }}</h3>
                </div>
                <div class="box-body">
                    <div class="file-upload">
                        <div class=" row form-horizontal form-label-left ">
                                <input class="hidden" value="{{ $item['id'] }}" name="wel_id" />
                                <div class="file">
                                    <div class="form-group">
                                        <div class="col-md-10">
                                            <input class="form-control wel-file" type="file" name="wel_file[]" />
                                            <label id="error-file-required" class="error hidden" style="color: red;">{{ Lang::get('core::message.Please choose file to upload') }}</label>
                                            <label id="error-file-size" class="error hidden" style="color: red;">{{ Lang::get('welfare::view.Max size file') }}</label>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-delete delete-file-input " data-toggle="tooltip" data-placement="bottom">
                                                <i class="fa fa-minus"></i>
                                            </button></div>
                                        </div>
                                    </div>
                            <div class="col-md-12">
                                <label id="error-file-empty" class="error hidden" style="color: red;">{{ Lang::get('welfare::view.Nothing to upload') }}</label>
                                <label id="error-file-extension" class="error hidden" style="color: red;"></label>
                                <p class="hint">{{ trans('welfare::view.File type uploaded') }}</p>
                            </div>
                            <div class="col-md-6">
                                <button type="button" class="btn btn-add add-file-input" data-toggle="tooltip" data-placement="bottom" title=""><i class="fa fa-plus"></i> {{ trans('welfare::view.Add File Input')}}</button>
                                <button type="button" class="btn btn-add upload-file-input" data-toggle="tooltip" data-url="{{ route('welfare::welfare.add.file') }}" data-placement="bottom" title=""><i class="fa fa-upload"></i> {{ trans('welfare::view.Upload File')}}<span class="_uploading hidden"><i class="fa fa-spin fa-refresh"></i></span></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="list-file col-md-6">
            <?php
            use Rikkei\Welfare\Model\WelfareFile;

            if (isset($item)) {
                $listWelFiles = WelfareFile::getFileByEvent($item['id']);
            }
            $stt = 1;
            ?>
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-list"></i> {{ trans('welfare::view.List File') }}</h3>
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table class="table table-striped dataTable table-bordered table-hover table-grid-data text-center">
                            <thead>
                                <tr>
                                    <th>{{ trans('welfare::view.No') }}</th>
                                    <th>{{ trans('welfare::view.File') }}</th>
                                    <th>&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($listWelFiles) && count($listWelFiles))
                                @foreach($listWelFiles as $item)
                                <tr>
                                    <td>{{ $stt++ }}</td>
                                    <td>{{ $item->files }}</td>
                                    <td>
                                        <button type="button" class="delete-welfare-file btn btn-danger" data-id="{{ $item->id }}" data-name="" data-url="{{ route('welfare::welfare.file.delete') }}">
                                            <span class="glyphicon glyphicon-trash"></span>
                                            <span class="_uploading hidden"><i class="fa fa-spin fa-refresh"></i></span>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <h4 class="no-result-grid">{{ trans('welfare::view.No results found') }}</h4>
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade modal-danger" id="modal-delete-welfare-file" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <h4 class="modal-title">{{ trans('welfare::view.Confirm Delete') }}</h4>
                    </div>
                    <div class="modal-body">
                        <!--<form action="" method="POST" class="form-confirm-delete-file">-->
                            <div class="deleteContent">
                                {{ trans('welfare::view.Are you sure') }}<span class="hidden did"></span>
                            </div>
                            <p class="text-change"></p>
                        <!--</form>-->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline btn-close" data-dismiss="modal">{{ trans('welfare::view.Close') }}</button>
                        <button type="button" class="btn btn-outline btn-ok" data-dismiss="modal" data-url="">{{ trans('welfare::view.Confirm Delete') }}</button>
                    </div>
            </div>
        </div>
    </div>
</div>
