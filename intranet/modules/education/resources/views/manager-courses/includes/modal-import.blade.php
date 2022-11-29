<div class="modal fade modal-default" id="modal_import_file" tabindex="-1" role="dialog"
     data-keyboard="false" data-backdrop="static">
    <form action="{{ route('education::education.teaching.import') }}" method="post" enctype="multipart/form-data">
        {!! csrf_field() !!}
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">×</span></button>
                    <h4 class="modal-title">{{ trans('test::test.import_file') }}</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group row">
                        <div class="col-sm-8">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>{{ trans('project::view.Import excel file') }} <em
                                                class="required">*</em></label>
                                    <label><a target="_blank"
                                              href="{{ \Rikkei\Education\View\EducationRemindCronJob::FORM_IMPORT_LINK }}"
                                              class="link"><i>({{ trans('test::test.view_example_file') }}
                                                )</i></a></label>
                                    <input type="file" name="excel_file"
                                           accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                                </div>
                                <div class="form-group loading-block text-blue hidden">
                                    <i class="fa fa-spin fa-refresh"></i>
                                    <i>{{ trans('project::message.Time processing file may take several minutes, please wait!') }}</i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="text-align: right">
                        <button type="button" class="btn btn-close" data-dismiss="modal" id="cancel_import">
                            {{ trans('test::test.close') }}
                        </button>
                        <button type="submit" class="btn btn-success"
                                id="submit_import">{{ trans('fines_money::view.Submit') }}</button>
                    </div>
                </div>
                <div class="modal-footer"></div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </form>
</div>