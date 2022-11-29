<?php
use Rikkei\Core\View\CoreUrl;
use Rikkei\Document\View\DocConst;

$fileMaxSize = DocConst::fileMaxSize();
$docItemId = isset($item) && $item ? $item->id : null;
?>

<script>
    var textShowLess = '<?php echo trans('doc::view.show less') ?>';
    var textShowMore = '<?php echo trans('doc::view.show more') ?>';
    var textConfirmDelete = '<?php echo trans('doc::message.Are you sure want to  delete') ?>';
    var textRequestAccount = '<?php echo trans('doc::message.You must input account') ?>';
    var textAlertInputData = '<?php echo trans('doc::message.Please input valid data') ?>';
    var textNoPeople = '{!! trans("doc::message.There are no reviewer") !!}';
    var textMustHasPerson = '{!! trans("doc::message.Must has at least one person") !!}'
    var docParams = {
        maxSize: parseFloat('<?php echo $fileMaxSize ?>'),
        errorFileMaxSize: '<?php echo trans('doc::message.file_max_size', ['max' => $fileMaxSize]) ?>',
        editUrl: '{{ route("doc::admin.edit", $docItemId) }}',
        urlCheckExistCode: '{{ route("doc::admin.check_exists") }}',
        errorCodeExists: '<?php echo trans('validation.unique', ['attribute' => 'Document code']) ?>',
        typeEditor: '{{ DocConst::TYPE_ASSIGNE_EDITOR }}',
        urlSearchReviewers: '{{ route("doc::admin.search_assignees") }}',
        urlGetSuggestReviewers: '{{ route("doc::admin.suggest_reviewers") }}',
        urlAddAssignee: '{{ route("doc::admin.add_assignee", $docItemId) }}',
        typeReviewer: '{{ DocConst::TYPE_ASSIGNE_REVIEW }}',
        typePublisher: '{{ DocConst::TYPE_ASSIGNE_PUBLISH }}',
    };
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="{{ CoreUrl::asset('asset_doc/js/main.js') }}"></script>
<script>
    (function ($) {
        selectSearchReload();
    })(jQuery);
</script>
