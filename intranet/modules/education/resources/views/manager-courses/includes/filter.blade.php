<?php
if (!isset($domainTrans) || !$domainTrans) {
    $domainTrans = 'team';
}
?>
<div class="col-md-12">
    <button type="button" class="btn btn-primary btn-submit-detail" <?php if ($dataCourse[0]->status == 4 || $dataCourse[0]->status == 5) { echo "disabled"; } ?> id="eventAddEmp">{{ trans('education::view.Education.Add employee') }}</button>
    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modal_import_file"
            id="btn_modal_import">
        <i class="fa fa fa-file-excel-o"></i> {{ (trans('test::test.import_file')) }}</button>
    <button type="button" class="btn btn-success btn-submit-detail" id="eventExportList" data-url="{{ route('education::education.export-list' , ['id' => $id, 'flag' => $flag]) }}">{{ trans('education::view.Education.Export List') }}</button>
    <button type="button" class="btn btn-success btn-submit-detail" id="eventExportResult" <?php if ($dataCourse[0]->status == 1 || $dataCourse[0]->status == 2 || $dataCourse[0]->status == 3 || $dataCourse[0]->status == 4) { echo "disabled"; } ?> data-url="{{ route('education::education.export-result' , ['id' => $id, 'flag' => $flag]) }}">{{ trans('education::view.Education.Export Result') }}</button>
    <button class="btn btn-primary btn-reset-filter">
        <span>{{ trans($domainTrans . '::view.Reset filter') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
    </button>
    <button class="btn btn-primary btn-search-filter">
        <span>{{ trans($domainTrans . '::view.Search') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
    </button>
</div>
