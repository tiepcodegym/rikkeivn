<?php
use Rikkei\Core\View\CoreUrl;
?>
<script>
    var _token = '{{ csrf_token() }}';
    var textErrorMessage = '<?php echo trans('vote::message.na_error') ?>';
    var textValidRequired = '<?php echo trans('vote::message.this_field_is_required') ?>';
    var textValidNumber = '<?php echo trans('vote::message.this_field_is_number') ?>';
    var textValidMin = '<?php echo trans('vote::message.this_field_is_greater_than_min', ['min' => '']) ?>';
    var textValidLessThan = '<?php echo trans('vote::message.this_field_must_be_less_than', ['field' => '']) ?>';
    var textValidGreaterThan = '<?php echo trans('vote::message.this_field_must_be_greater_than', ['field' => '']) ?>';
    var textValidSelectTeamOrEmail = '<?php echo trans('vote::message.you_must_slect_team_or_reciver_email') ?>';
    var textFieldNominateStartAt = '<?php echo trans('vote::view.nominate_start_at') ?>';
    var textFieldNominateEndAt = '<?php echo trans('vote::view.nominate_end_at') ?>';
    var textFieldVoteEndAt = '<?php echo trans('vote::view.vote_end_at') ?>';
    var textFieldVoteStartAt = '<?php echo trans('vote::view.vote_start_at') ?>';
    var textValidSaveBeforeSendMail = '<?php echo trans('vote::message.you_must_save_data_before_send_email') ?>';
    var textRequiredValue = '<?php echo trans('vote::messsage.data_not_null') ?>';
    var textNotSelected = '<?php echo trans('vote::view.not_selected') ?>';
    var textSelected = '<?php echo trans('vote::view.selected') ?>';
    var textOr = '<?php echo trans('vote::view.or') ?>';
    var textValidMaxLen = '<?php echo trans('vote::message.please_input_max_length', ['max' => '']) ?>';
    var textSymbol = '<?php echo trans('vote::view.symbol') ?>';
    var textAnd = '<?php echo trans('vote::view.and') ?>';
    var textShowMore = '<?php echo trans('vote::view.read_more') ?>';
    var textShowLess = '<?php echo trans('vote::view.show_less') ?>';
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="{{ CoreUrl::asset('lib/js/moment.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>

<script src="{{ CoreUrl::asset('vote/manage/js/vote.js') }}"></script>