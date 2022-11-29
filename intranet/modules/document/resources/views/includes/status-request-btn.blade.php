<?php
use Rikkei\Document\View\DocConst;
?>

@if ($requestPermiss['submit'])
    <button type="submit" class="btn btn-lg btn-submit btn-primary" data-status="{{ DocConst::STT_SUBMITED }}"
        data-noti="{{ trans('doc::message.confirm_do_action', ['action' => 'Submit']) }}">{{ trans('doc::view.Submit') }}</button>
@endif

@if ($requestPermiss['approve'])
    <button type="submit" class="btn btn-lg btn-submit btn-primary" data-status="{{ DocConst::STT_APPROVED }}"
        data-noti="{{ trans('doc::message.confirm_do_action', ['action' => 'Approve']) }}">{{ trans('doc::view.Approve') }}</button>
@endif

@if ($requestPermiss['create_doc'])
    <?php
    $createParams = ['id' => null];
    if ($item) {
        $createParams['request_id'] = $item->id;
    }
    ?>
    <a href="{{ route('doc::admin.edit', $createParams) }}" class="btn btn-lg btn-success">{{ trans('doc::view.Create document') }}</a>
@endif

@if ($requestPermiss['feedback'])
    <button type="button" class="btn btn-lg btn-danger"
        data-toggle="modal" data-target="#request_feedback_modal">{{ trans('doc::view.Feedback') }}</button>
@endif
