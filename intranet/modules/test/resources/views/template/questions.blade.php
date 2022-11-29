<?php
use Rikkei\Test\View\ViewTest;

$testCreatedBefore = $item->created_at <= ViewTest::TYPE2_TIME;
?>

@foreach($questions as $order => $q_item)
<?php
$isType2 = in_array($q_item->type, ViewTest::ARR_TYPE_2);
?>
<div id="question_{{ $q_item->id }}" class="q_item">
    <p class="q_order">{{ trans('test::test.question') }} {{ $order + 1 }}.</p>
    @if (!$isType2 || !$testCreatedBefore)
        <div class="q_content {{ $q_item->is_editor ? 'editor' : '' }}">{!! $q_item->content !!}</div>
    @endif
    @if($q_item->explain)
        <div class="q_explain"><span class="explain-title">Giải thích đáp án:</span> {!!$q_item->explain!!}</div>
    @endif
    <?php
    $answers = $q_item->answers->sortBy('label');
    $childs = $q_item->childs;
    ?>
    @if (!$answers->isEmpty())

        @if (!$isType2)

        <ul class="list-inline q_answers">
            @if (!$answers->isEmpty())
                @if ($q_item->multi_choice != 0 || $answers->where('pivot.is_correct', 1)->count() > 1)
                    @foreach($answers as $aw_item)
                    <li class="aw_item {{ $aw_item->pivot->is_correct ? 'aw_true' : '' }}">
                        <span class="ans_box">
                            {!! Form::checkbox('answer['. $q_item->id .']', $aw_item->id, $aw_item->pivot->is_correct, ['disabled']) !!}
                            <span class="aw_label">{{ $aw_item->label }}. </span>
                            <span class="aw_content">{!! $aw_item->content !!}</span>
                        </span>
                    </li>
                    @endforeach
                @else
                    @foreach($answers as $aw_item)
                    <li class="aw_item {{ $aw_item->pivot->is_correct ? 'aw_true' : '' }}">
                        <span class="ans_box">
                            {!! Form::radio('answer['. $q_item->id .']', $aw_item->id, $aw_item->pivot->is_correct, ['disabled']) !!}
                            <span class="aw_label">{{ $aw_item->label }}. </span>
                            <span class="aw_content">{!! $aw_item->content !!}</span>
                        </span>
                    </li>
                    @endforeach
                @endif
            @endif
        </ul>

        @else

        <div class="answer-list-box">
            <ul class="list-inline q_answers">
                @foreach ($answers as $aw_item)
                <li class="aw_item">
                    <label class="aw_label">{{ $aw_item->label }}. </label>
                    <label class="aw_content">{!! $aw_item->content !!}</label>
                </li>
                @endforeach
            </ul>
        </div>
        
        <!--<ul class="list-inline q_answers answer-type-2">-->
        <ul class="list-inline q_answers q_childs_type2">
            @if (!$childs->isEmpty())
                @foreach($childs as $key => $q_child)
                <?php
                $child_answer = $q_child->answers;
                $child_answer_id = null;
                if (!$child_answer->isEmpty()) {
                    $child_answer_id = $child_answer->first()->id;
                }
                ?>
                <li class="child_item aw_true">
                    <div class="row">
                        <div class="col-sm-8">
                            <div class="child_content">
                                <span class="child_order">{{ $key + 1 }}. </span>
                                <div class="q_content {{ $q_child->is_editor ? 'editor' : '' }}">{!! $q_child->content !!}</div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            @if (!$answers->isEmpty())
                            <select class="form-control select-search answer_select">
                                <option value="">--{{ trans('test::test.select_answer') }}--</option>
                                @foreach($answers as $aw_item)
                                <option value="{{ $aw_item->id }}" {{ $child_answer_id == $aw_item->id ? 'selected' : '' }}>{{ $aw_item->label }}</option>
                                @endforeach
                            </select>
                            @endif
                        </div>
                    </div>
                </li>
                @endforeach
            @endif
        </ul>

        @endif

    @endif
</div>
@endforeach

