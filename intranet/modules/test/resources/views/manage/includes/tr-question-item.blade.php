<?php
use Rikkei\Test\View\ViewTest;

$hasMore = false;
$qItemContent = $qItem->mergeChildContent();
$shortQContent = ViewTest::trimWords($qItemContent, 
        ['num_line' => 2, 'num_word' => 15, 'num_ch' => 10000],
        '', $hasMore);
$catAttrs = '';
if (!isset($cats)) {
    $cats = $qItem->getArrCatIds();
}
if ($cats) {
    foreach ($cats as $key => $arrCats) {
        $catAttrs .= 'data-cat-' . $key . '="'. implode(',', array_keys($arrCats)) .'"';
    }
}
?>

<tr id="question_{{ $qItem->id }}" class="q_item" {!! $catAttrs !!} data-status="{{ $qItem->status }}" data-order="{{ $order }}" data-id="{{ $qItem->id }}">
    <td><input type="checkbox" class="check_item" value="{{ $qItem->id }}"></td>
    <td class="q_order">
        <span class="num">{{ ($order + 1) }}</span>
    </td>
    <td class="q_content_toggle">
        @if (!$hasMore)
            <div class="q_content {{ $qItem->is_editor ? 'editor' : '' }}">{!! $shortQContent !!}</div>
        @else
            <div class="content_short">
                <div class="q_content">{!! $shortQContent !!}...</div>
            </div>
            <div class="content_full">
                <div class="q_content {{ $qItem->is_editor ? 'editor' : '' }}">{!! $qItemContent !!}</div>
            </div>
            <a href="#" class="link q_view_more" 
               data-short-text="{{ trans('test::test.view_short') }}"
               data-full-text="{{ trans('test::test.view_more') }}">[{{ trans('test::test.view_more') }}]</a>
        @endif
    </td>
    @foreach (ViewTest::ARR_CATS as $key => $slug)
    <td>
        <?php
        if ($cats && isset($cats[$key])) {
            if ($cats[$key]) {
                $catHtml = '';
                foreach ($cats[$key] as $cat) {
//                    $catHtml .= '<span>'. e($cat) .'</span>, ';
                    $catHtml .= e($cat) . ', ';
                }
                echo trim($catHtml, ', ');
            }
        }
        ?>
    </td>
    @endforeach
    <td>{{ $qItem->statusLabel() }}</td>
    <td>
        <?php
        $itemParams = [
            'id' => $qItem->id,
            'test_id' => isset($testId) ? $testId : null,
            'lang' => $currentLang,
            'q_order' => $order,
            'q_lang_id' => $qItem->id,
        ];
        ?>
        <button type="button" class="btn btn-primary btn-popup"
                data-id="{{ $qItem->id }}"
                data-url="{{ route('test::admin.test.question.full_edit', $itemParams) }}">
            <i class="fa fa-edit"></i></button>
        <button type="button" class="btn-delete btn-delete-question"
                data-id="{{ $qItem->id }}"
                data-url="{{ route('test::admin.test.question.delete', $qItem->id) }}">
            <i class="fa fa-trash"></i></button>
    </td>
</tr>

