<?php
use Rikkei\Test\View\ViewTest;

$testCreatedBefore = $item->created_at <= ViewTest::TYPE2_TIME;
?>

@foreach($writtenQuestions as $order => $q_item)
<div id="question_{{ $q_item->id }}" class="q_item">
    <p class="q_order">{{ trans('test::test.question') }} {{ $order + 1 }}.</p>
    @if (!$testCreatedBefore)
    <div class="q_content {{ $q_item->is_editor ? 'editor' : '' }}">{!! $q_item->content !!}</div>
    @endif
</div>
@endforeach
