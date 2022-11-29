@extends('test::layouts.front')

@section('title', trans('test::test.view_results'))

@section('body_class', 'test-page result-page')

@section('content')

<h1 class="page-header single-title">
    <span class="name pull-left">{{ trans('test::test.test_result') }}</span>
</h1>

@section('messages')
@stop

@include('messages.success')
@include('messages.errors')

<?php
use Rikkei\Test\View\ViewTest;
use Rikkei\Notes\Model\ReleaseNotes;
use Rikkei\Test\Models\Test;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

if ($testResult->question_index != 'all') {
    $question_index = explode(',', $testResult->question_index);
}
$total_corrects = $testResult->total_corrects;
$total_answers = $testResult->total_answers;
$currentYear = Carbon::now()->format('Y');
$version = ReleaseNotes::select('version')->orderBy('release_at', 'desc')->first();
$version = !$version ? '1.0.0' : $version->version;
?>

<table class="table test-info table-condensed">
    <tr>
        <th>{{ trans('test::test.test_name') }}</th>
        <td>{{ $test->name . ' - ' . $test->time }} {{ trans('test::test.minute') }}</td>
        <th>{{ trans('test::test.full_name') }}</th>
        <td>{{ $testResult->employee_name }}</td>
    </tr>
    <tr>
        <th>{{ trans('test::test.total_corrects') }}</th>
        <td><strong>{{ $total_corrects . '/' . $testResult->total_question }}</strong></td>
        <th>{{ trans('test::test.email') }}</th>
        <td>{{ $testResult->employee_email }}</td>
    </tr>
    <tr>
        @if ($test->set_min_point == Test::SET_MIN_POINT && $test->min_point)
            <th>
                {{ trans('test::test.Min point') }}
                <a href="#" title="{{ trans('test::test.min_point_tooltip') }}">
                    <i class="fa fa-fw fa-question-circle" style="font-size: 15px;"></i>
                </a>
            </th>
            <td><strong>{{ $test->min_point ? (int)$test->min_point : '' }}</strong></td>
        @endif
        <th>{{ trans('test::test.tested_time') }}</th>
        <td>{{ $testResult->created_at }}</td>
        @if ($test->set_min_point == Test::NOT_SET_MIN_POINT)
            <td></td>
            <td></td>
        @endif
    </tr>
    <tr>
        @if ($testResult->tester_type == ViewTest::TESTER_PUBLISH)
            <th>{{ trans('test::test.phone_number') }}</th>
            <td>{{ $testResult->phone }}</td>
            <td></td>
            <td></td>
        @endif
    </tr>

</table>
@if (Session::has('textWarning'))
    <p style="font-weight: bold; font-size: 20px; color: red">{{ Session::get('textWarning') }}</p>
@endif
@if ($resultDetail === false)
<!--don't show result detail-->
@elseif (!$resultDetail->isEmpty() || (isset($writtenDetail) && !empty($writtenDetail)))
    <div class="align-left test-content">
        <h4>
            A: {{ trans('test::test.question multiple choice') }}
        </h4>
    </div>
    <div class="test-content">
        <?php
        $curr_correct = 0;
        $testCreatedBefore = $test->created_at <= ViewTest::TYPE2_TIME;
        ?>
        
        @foreach($resultDetail as $q_item)

        <?php
        $isType1 = in_array($q_item->type, ViewTest::ARR_TYPE_1);
        $isType2 = in_array($q_item->type, ViewTest::ARR_TYPE_2);
        ?>
        <div id="question_{{ $q_item->id }}" class="q_item">
            <?php
            $tick_class = 'fa-close tick_false';
            if ($q_item->is_correct) {
                $tick_class = 'fa-check tick_true';
                $curr_correct ++;
            }
            //correct answer
            $answers = $q_item->answers;
            // if has random asnwer
            if ($test->random_answer && $randAnswerLabels && isset($randAnswerLabels[$q_item->id])) {
                $answers = ViewTest::genAnswerFromArrLabels($randAnswerLabels[$q_item->id], $answers);
            }
            
            $childs = $q_item->childs()
                    ->with(['results' => function ($query) use ($q_item, $resultId) {
                        $query->where('test_result_id', $resultId)
                                ->where('test_id', $q_item->test_id);
                    }])->get();
            
            //check answer
            $isAnswer = true;
            if ($isType1) {
                if (!$q_item->answer_content) {
                    $isAnswer = false;
                }
            } else if ($isType2) {
                $isAnswer = false;
                if (!$childs->isEmpty()) {
                    foreach ($childs as $cItem) {
                        $qResult = $cItem->results->first();
                        if ($qResult && $qResult->answer_id) {
                            $isAnswer = true;
                            break;
                        }
                    }
                }
            } else {
                if (!$q_item->answer_content) {
                    $isAnswer = false;
                } else {
                    $isAnswer = unserialize($q_item->answer_content);
                }
            }
            ?>
            
            <p class="q_order">{{ trans('test::test.question') }} {{ $q_item->number }}. <span class="tick fa {{ $tick_class }}"> {{ $q_item->is_correct ? $curr_correct : '' }}</span></p>
            @if (!$isType2 || !$testCreatedBefore)
            <div class="q_content {{ $q_item->is_editor ? 'editor' : '' }}">{!! $q_item->content !!}</div>
            @endif
            <!--check no answer-->
            @if ($isAnswer)
                @if($q_item->explain)
                    <div class="q_explain"><span class="explain-title">{{ trans('test::test.answer_explain') }}:</span> {!!$q_item->explain!!}</div>
                @endif

                @if (!$answers->isEmpty())
                    <!--check not type 2-->
                    @if (!$isType2)
                        <ul class="list-inline q_answers">
                        <!--check type 1-->
                        @if ($isType1)

                            <li class="aw_item {{ $q_item->is_correct ? 'awt_true' : 'awt_false' }}">
                                {!! Form::text('answers['. $q_item->id .']', $q_item->answer_content, ['disabled', 'class' => 'form-control']) !!}
                            </li>
                            <li class="aw_item">
                                <span>{{ trans('test::test.correct_answer') }}: </span>
                                <span class="aw_correct">
                                    <?php $aw_correct = ''; ?>
                                    @foreach($answers as $ans)
                                    <?php $aw_correct .= $ans->content.', ' ?>
                                    @endforeach
                                    {!! trim($aw_correct, ', ') !!}
                                </span>
                            </li>
                        @else
                            <!--check if type 3-->
                            @if (!$answers->isEmpty())
                                <?php 
                                $answer_content = unserialize($q_item->answer_content);
                                $answer_content = $answer_content ? $answer_content : [];
                                ?>
                                @if($q_item->multi_choice != 0)
                                    @foreach($answers as $aw_item)
                                    <li class="aw_item
                                    {{ $aw_item->pivot->is_correct ? 'aw_true' : '' }}
                                    {{ $aw_item->pivot->is_correct && !(in_array($aw_item->id, $answer_content)) ? 'rs_true':'' }}
                                    {{ $aw_item->pivot->is_correct && (in_array($aw_item->id, $answer_content)) ? 'rs_true':'' }}
                                    {{ (in_array($aw_item->id, $answer_content)) && !$aw_item->pivot->is_correct ? 'rs_false' :  '' }}"
                                    >
                                        <span class="ans_box">
                                            {!! Form::checkbox('answers['. $q_item->id .'][]', $aw_item->id, in_array($aw_item->id, $answer_content) ? true : false ,['disabled', 'id' => 'answer_'.$aw_item->id]) !!}
                                            <span class="aw_label">{{ $aw_item->label }}. </span>
                                            <label class="aw_content" for="answer_{{ $aw_item->id }}">{!! $aw_item->content !!}</label>
                                        </span>
                                    </li>
                                    @endforeach
                                @else
                                    @foreach($answers as $aw_item)
                                    <li class="aw_item {{ $aw_item->pivot->is_correct ? 'aw_true' : '' }} {{ (in_array($aw_item->id, $answer_content)) ? $q_item->is_correct ? 'rs_true' : 'rs_false' : '' }}">
                                        <input type="radio" id="answer_{{ $aw_item->id }}" class="_answer"
                                               value="{{$aw_item->id}}" name="answers[{{$q_item->id }}][]" disabled 
                                               @if (in_array($aw_item->id, $answer_content)) checked @endif>
                                        <span class="ans_box">
                                            <span class="aw_label">{{ $aw_item->label }}. </span>
                                            <label class="aw_content" for="answer_{{ $aw_item->id }}">{!! $aw_item->content !!}</label>
                                        </span>
                                    </li>
                                    @endforeach
                                @endif
                            @endif
                        @endif
                        <!--end check type 1-->
                    <!--else check type 2-->
                        </ul>
                    @else
                        <ul class="list-inline q_answers q_childs_type2">
                        @if (!$childs->isEmpty())
                            <?php
                            $answerLabels = [];
                            if ($test->random_answer && $randAnswerLabels && isset($randAnswerLabels[$q_item->id])) {
                                $answerLabels = $randAnswerLabels[$q_item->id];
                            }
                            ?>

                            @foreach($childs as $key => $q_child)
                            <?php
                            $q_result = $q_child->results->first();
                            $q_child_ans = $q_child->answers()->first();
                            ?>
                            <li class="child_item {{ ($q_result && $q_result->answer_id == $q_child_ans->id) ? 'aw_true' : 'aw_false' }}">
                                <div class="row">
                                    <div class="col-sm-8">
                                        <div class="child_content">
                                            <span class="child_order">{{ $key + 1 }}. </span>
                                            <div class="q_content {{ $q_child->is_editor ? 'editor' : '' }}">{!! $q_child->content !!}</div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        @if (!$answers->isEmpty())
                                            <select name="answers[{{ $q_item->id }}][{{ $q_child->id }}]" disabled class="form-control select-search answer_select">
                                                <option value="">--{{ trans('test::test.select_answer') }}--</option>
                                                @foreach($answers as $aw_item)
                                                <option value="{{ $aw_item->id }}" {{ ($q_result && $q_result->answer_id == $aw_item->id) ? 'selected' : '' }}>{{ $aw_item->label . '. ' . $aw_item->content }}</option>
                                                @endforeach
                                            </select>
                                            @if ($q_child_ans)
                                            <?php
                                            $q_child_ans_label = isset($answerLabels[$q_child_ans->id]) ? $answerLabels[$q_child_ans->id] : $q_child_ans->label;
                                            ?>
                                            <div class="correct_box">
                                                {{ trans('test::test.correct_answer') }}: 
                                                <span class="aw_correct">
                                                    <span class="aw_label">{{ $q_child_ans_label }}. </span>
                                                    <span class="aw_content">{{ $q_child_ans->content }}</span>
                                                </span>
                                            </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        @endif
                        </ul>
                    @endif
                    <!--end check type 2-->
                @endif

            @else
                <p><strong class="text-red">{{ trans('test::test.you_dont_do_this_question') }}</strong></p>
            @endif
            <!--end check not asnwer-->
        </div>
        
        @endforeach
    </div>
    @if(isset($writtenDetail) && $writtenDetail->count() > 0)
        <div class="align-left test-content">
            <h4>
                B: {{ trans('test::test.question written') }}
            </h4>
        </div>
        <div class="test-content">
            @foreach($writtenDetail as $key=>$q_item)
                <div id="question_{{ $q_item->id }}" class="q_item">
                    <p class="q_order">{{ trans('test::test.question') }} {{ $key + 1 }}.</p>
                    <div class="q_content editor">{!! $q_item->question !!}</div>
                    <ul class="list-inline q_answers">
                        <li class="aw_item">
                            <span class="q_order">{{ trans('test::test.answer') }}: </span>
                            @if($q_item->answer)
                                <label class="aw_content" for="answer_{{ $q_item->id }}">{!! $q_item->answer !!}</label>
                            @else
                                <span>{{ trans('test::test.you_dont_do_this_question') }} </span>
                            @endif
                        </li>
                    </ul>
                </div>
                <p></p>
            @endforeach
        </div>
    @endif

@else
    <label>{{ trans('test::test.no_item') }}</label>
@endif

@stop

@section('footer')

<div class="main-footer">
    <div class="pull-right hidden-xs">
        <b>Version</b> {!!$version!!}
    </div>
    <strong>Copyright &copy; {!! $currentYear !!} <a class="link" href="http://rikkeisoft.com/">RikkeiSoft</a>.</strong> All rights reserved.
</div><!-- /.container -->

@stop
