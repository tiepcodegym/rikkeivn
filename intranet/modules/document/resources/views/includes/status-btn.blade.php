<?php
use Rikkei\Document\View\DocConst;

?>

@if ($docPermiss['submit'])
    <button type="submit" class="btn btn-lg btn-submit btn-primary" data-status="{{ DocConst::STT_SUBMITED }}"
        data-noti="{{ trans('doc::message.confirm_do_action', ['action' => trans('doc::view.Submit')]) }}">{{ trans('doc::view.Submit') }}</button>
@endif

@if ($docPermiss['review'])
    <button type="submit" class="btn btn-lg btn-submit btn-primary" data-status="{{ DocConst::STT_REVIEWED }}"
        data-noti="{{ trans('doc::message.confirm_do_action', ['action' => trans('doc::view.Review')]) }}">{{ trans('doc::view.Review') }}</button>
@endif

@if ($docPermiss['publish'])
    <button type="button" class="btn btn-lg btn-success"
        data-toggle="modal" data-target="#doc_publish_modal">
        {{ $item && $item->status == DocConst::STT_PUBLISH ? trans('doc::view.Re-publish') : trans('doc::view.Publish') }}
    </button>
@endif

@if ($docPermiss['feedback'])
    <button type="button" class="btn btn-lg btn-danger"
        data-toggle="modal" data-target="#doc_feedback_modal">{{ trans('doc::view.Feedback') }}</button>
@endif