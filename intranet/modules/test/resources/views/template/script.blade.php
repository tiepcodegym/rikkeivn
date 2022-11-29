<?php 
use Rikkei\Test\Models\Test; 
use Rikkei\Core\View\CoreUrl;
?>

<div class="modal fade modal-danger" id="_modal_delete" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ Lang::get('core::view.Confirm') }}</h4>
            </div>
            <div class="modal-body">
                <p class="text-default">{{ Lang::get('core::view.Are you sure delete item(s)?') }}</p>
                <p class="text-change"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-close" data-dismiss="modal">{{ Lang::get('core::view.Close') }}</button>
                <button type="button" class="btn btn-outline btn-ok">{{ Lang::get('core::view.OK') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div> <!-- modal delete cofirm -->

@include('test::template.audio')

<script>
    var _token = "{{csrf_token()}}";
    var text_confirm_edit_upload = '<?php echo trans('test::test.confirm_edit_upload_test_file') ?>';
    var text_select_answers = '<?php echo trans('test::test.select_answer') ?>';
    var text_no_valid_question = '<?php echo trans('test::test.there_are_no_valid_question') ?>';
    var text_question = '<?php echo trans('test::test.question'); ?>';
    var text_error_input_number_question = '<?php echo trans('test::validate.please_input_number_questions') ?>';
    var text_error_unique_display_option = '<?php echo trans('test::validate.display_option_is_unique') ?>';
    var text_error_required_select_cat = '<?php echo trans('test::validate.please_select_type_cat') ?>';
    var time_end_must_greater_than_time_start = '<?php echo trans('test::validate.time_end_must_greater_than_time_start') ?>';
    var please_input_value_not_less_than = '<?php echo trans('test::validate.please_input_value_not_less_than') ?>';
    var please_input_value_not_greater_than = '<?php echo trans('test::validate.please_input_value_not_greater_than') ?>';
    var total_question_has_exceeded_the_limit = '<?php echo trans('test::validate.total_question_has_exceeded_the_limit') ?>';
    var textNoneItemSelected = '<?php echo trans('test::validate.no_item_selected') ?>';
    
    var GMAT_TYPE = '<?php echo Test::getGMATId(); ?>';
    var get_edit_question_url = '<?php echo route('test::admin.test.question.edit'); ?>';
    var _urlUploadImage = '<?php echo route('test::admin.upload_images'); ?>';
    var text_confirm_delete = '<?php echo trans('test::validate.Are you sure want to delete'); ?>';
    var textNoItem = '<?php echo trans('test::test.no_item'); ?>';
    var isEdit = false;
    var oldThumbnail = '';
    var currentTestId = null;
    @if (isset($item) && $item)
        isEdit = true;
        currentTestId = {{ $item->id }};
        oldThumbnail = '{!! $item->thumbnail !!}';
    @endif
    @if (isset($currentLang))
        var currentLangEdit = '{{ $currentLang }}';
    @else
        var currentLangEdit = '{{ Session::get("locale") }}';
    @endif
    var imageAllows = '{!! json_encode(config('services.file.image_allow')) !!}';
    imageAllows = JSON.parse(imageAllows);
    var errorFileNotAllow = '{!! trans('core::message.File type dont allow') !!}';
    var mesNoAnswersForWritten = '{{ trans('test::test.You must answer at least 1 written question') }}'
</script>
<script type="text/x-mathjax-config">
  MathJax.Hub.Config({
    tex2jax: {inlineMath: [["$$","$$"],["\\(","\\)"]]}
  });
</script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.0/MathJax.js?config=TeX-AMS_HTML"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="{{ URL::asset('lib/ckeditor/plugins/codesnippet/lib/highlight/highlight.pack.js') }}"></script>
<script type="text/javascript" src= "{{URL::asset('lib/js/moment.min.js')}}"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script src="{{ CoreUrl::asset('tests/ad_src/main.js') }}"></script>
<script src="{{ CoreUrl::asset('tests/js/audio.js') }}"></script>
<script>
    $(document).ready(function () {
        $('.dataTables_filter').hide();
    });
</script>
