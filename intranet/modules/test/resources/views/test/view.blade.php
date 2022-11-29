@extends('test::layouts.front')

@section('title', $test->name)

@section('head')
<?php
use Rikkei\Core\View\CookieCore;
use Rikkei\Test\View\ViewTest;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

$currentYear = Carbon::now()->format('Y');

header("Cache-Control: no-store, must-revalidate, max-age=0");
header("Pragma: no-cache");

$testTime = $test->time;
$testSecond = 0;
$testId = $test->id;
$timeStart = Session::get('timeStart_'. $testId);
$timeNow = Carbon::now()->getTimestamp();
if ($timeStart > $timeNow) {
    $diffTime = $timeStart - $timeNow;
    $testTime = floor($diffTime / 60);
    $testSecond = $diffTime % 60;
} else {
    $testTime = 0;
    Session::forget('timeStart_'. $testId);
}

?>
@stop

<script>
    var is_test_auth = '{{ $test->is_auth }}';
    @if ($testTemp)
        var testTemp = {
            test_id: {{ $testTemp->test_id }},
            email: '{{ $testTemp->employee_email }}',
        };
    @endif
    var saveTempResultUrl = '{{ route('test::save_temp', ['id' => $test->id]) }}';
    var PER_PAGE = {{ ViewTest::PER_PAGE }};
</script>

@section('body_class', 'test-page testing-page')

@section('content')

<?php

//render display option questions
$questionIndex = ViewTest::genDisplayQuestion($test, $questions);

$isRandomAnswer = $test->random_answer;
if ($isRandomAnswer) {
    //random answer
    $keyListLabels = 'question_answers_labels_' . $test->group_id . '_';
    if ($test->is_auth) {
        $keyListLabels .= 'auth_' . auth()->id();
    } else if ($candidate) {
        $keyListLabels .= 'candidate_' . $candidate->id;
    } else {
        $keyListLabels .= Session::getId();
    }
    $keyListLabels = md5($keyListLabels);
    $arrTestRanAnswers = Session::get($keyListLabels); //[testId => [questionId => [answerId => label]]
    $arrTestRanAnswers = $arrTestRanAnswers ? $arrTestRanAnswers : [];

    $arrRanAnswers = [];
    $otherRanAnswers = [];
    $originOtherAnswers = [];
    $qIndexValues = $questionIndex ? array_values($questionIndex) : $questions->pluck('id')->toArray();

    if (!isset($arrTestRanAnswers[$test->id])) {
        if ($arrTestRanAnswers) {
            $otherRanTestId = array_keys($arrTestRanAnswers)[0];
            $otherRanAnswers = $arrTestRanAnswers[$otherRanTestId];
            $originOtherAnswers = ViewTest::getAnswersByQuestionIds(array_keys($arrTestRanAnswers[$otherRanTestId]), $otherRanTestId);
        }
    } else {
        $arrRanAnswers = $arrTestRanAnswers[$test->id];
    }
    $hasSessionAnswers = count($arrRanAnswers) > 0;
}
$totalQuestions = $questions->count();
if (!$noWritten && count($writtenQuestions)) {
    $writtenIds = $writtenQuestions->pluck('id')->toArray();
    $writtenArrayKey = Session::put('writtenQuestions_' . $test->group_id, $writtenIds);
    $questions['written'] = $writtenQuestions;
    $totalQuestions = $questions->count() - 1;
}
?>

<h1 class="page-header single-title">
    <span class="name pull-left">
        {{ $test->name }} ({{ $test->time.' '.trans('test::test.minute') }})<br>
        {{ (!$noWritten ? $questions->count() - 1 : $questions->count()) . ' ' . trans('test::test.question multiple choice') }}
        {{ !$noWritten && $writtenQuestions ? ' - '. $writtenQuestions->count() . ' ' . trans('test::test.question written') : '' }}<br>
        {{ $test->description ? $test->description : null }}
    </span>
</h1>

@section('messages')
@stop

@include('messages.success')
@include('messages.errors')

@if (Session::has('tested') && Session::get('tested'))

<div class="flash-message test-mess">
    <div class="alert alert-danger">
        <ul>
            <li>{{ trans('test::test.you_had_done_this_test') }},  
                <a href="{{ route('test::result', ['id' => Session::get('tested')]) }}">
                    {{ trans('test::test.view_results') }}
                </a>
            </li>
        </ul>
    </div>
</div>

@endif

@if (!$questions->isEmpty())

{!! Form::open(['method' => 'post', 'route' => 'test::submit_test', 'id' => 'submit_test_form']) !!}
    
    <div class="submit-box">
        <button type="submit" id="btn_submit_test" data-noti="{{ trans('test::test.are_you_sure_want_to_submit') }}" class="btn btn-primary"><i class="fa fa-check"></i> {{ trans('test::test.submit_test') }}</button>
        <button type="button" class="btn btn-info time-btn">
            <i class="fa fa-clock-o"></i> <span class="minute">{{ $testTime < 10 ? '0'.$testTime : $testTime }}</span>:<span class="second">{{ $testSecond < 10 ? $testSecond.'0' : $testSecond }}</span>
        </button>
    </div>

    @if (!$test->is_auth)
        @include('test::template.tester-infor')
    @endif
    
    @if($questionIndex)
        <input type="hidden" id="question_index" name="question_index" value="{{ implode($questionIndex, ',') }}" class="form-control" />
        <input type="hidden" name="total_question" value="{{ $totalQuestions }}" class="form-control" id="haha" />
    @else
        <input type="hidden" name="total_question" value="{{ $totalQuestions }}" class="form-control" />
    @endif
    
    <div id="required_answer"></div>
    {!! Form::hidden('test_id', $test->id, ['id' => 'test_id']) !!}
    
    <div class="test-content">
        @if (!$questions->isEmpty() || count($writtenQuestions) > 0)
            <?php
            $order = 0;
            $count = 1;
            $testCreatedBefore = $test->created_at <= ViewTest::TYPE2_TIME;
            ?>
            @foreach($questions as $q_item)
            <?php
            $order++;
            $type4 = is_a($q_item, 'Illuminate\Database\Eloquent\Collection') ? true : false;
            if (isset($questions['written'])) unset($questions['written']);
            if (isset($q_item->type)) {
                $isType2 = in_array($q_item->type, ViewTest::ARR_TYPE_2);
            }
            ?>

            <div id="question_{{ isset($q_item->id) ? $q_item->id : "" }}" class="q_item hidden" data-id="{{ isset($q_item->id) ? $q_item->id : "" }}">
                @if(!$noWritten)
                    @if($writtenQuestions && count($writtenQuestions) > 0)
                    <div class="align-left">
                        <h4>
                            {{ trans('test::test.question written') }}:
                        </h4>
                    </div>
                    @foreach($writtenQuestions as $written)
                        <div style="padding: 15px 0px;">
                            <p class="q_order" data-order="{{ $order }}">{{ trans('test::test.question') }} {{ $count++ }}.</p>
                            <div class="q_content editor">{!! $written['content'] !!}</div>
                            <div>
                                {!! Form::textArea('answers[written]['. $written['id'] .']', old('answers.'.$written['id']), ['class' => 'form-control _answer written_answer', 'placeholder' => trans('test::test.input_answer')]) !!}
                            </div>
                        </div>
                    @endforeach
                        <?php $writtenQuestions = collect() ?>
                    @endif
                @endif
                @if(count($writtenQuestions) == 0 && !isset($reup))
                    <div class="align-left" style="padding: 15px 0px;">
                        <h4>
                            {{ trans('test::test.question multiple choice') }}:
                        </h4>
                    </div>
                    <?php $reup = true; ?>
                @endif
                @if(!$type4)
                <p class="q_order" data-order="{{ $order }}">{{ trans('test::test.question') }} {{ $order }}.</p>
                @if (!$isType2 || !$testCreatedBefore)
                <div class="q_content {{ $q_item->is_editor ? 'editor' : '' }}">{!! $q_item->content !!}</div>
                @endif
                <?php
                $answers = $q_item->answers->sortBy('label');
                ?>
                @if (!$answers->isEmpty())
                    @if (!$isType2)
                    <ul class="list-inline q_answers">
                        @if (in_array($q_item->type, ['type1', '1']))
                            <div>
                                {!! Form::text('answers['. $q_item->id .']', old('answers.'.$q_item->id), ['class' => 'form-control _answer', 'placeholder' => trans('test::test.input_answer')]) !!}
                            </div>
                        @else
                            @if (!$answers->isEmpty())
                                <?php
                                if ($isRandomAnswer) {
                                    if ($arrRanAnswers && isset($arrRanAnswers[$q_item->id])
                                            && ($arrRanLabels = $arrRanAnswers[$q_item->id])) {
                                        $answers = ViewTest::genAnswerFromArrLabels($arrRanLabels, $answers);
                                    } else {
                                        $shuffleAnswers = ViewTest::shuffleAnswers($answers, $otherRanAnswers, $qIndexValues, $originOtherAnswers);
                                        $answers = $shuffleAnswers['answers'];
                                        $arrRanAnswers[$q_item->id] = $shuffleAnswers['save'];
                                    }
                                }
                                ?>
                                @if ($q_item->multi_choice != 0 || $answers->where('pivot.is_correct', 1)->count() > 1)
                                    @foreach($answers as $aw_item)
                                        <li class="aw_item">
                                            {!! Form::checkbox('answers['. $q_item->id .'][]', $aw_item->id, old('answers.'.$q_item->id) == $aw_item->id ? true : false, ['id' => 'answer_'.$aw_item->id, 'class' => '_answer']) !!}
                                            <label class="aw_label" for="answer_{{ $aw_item->id }}">{{ $aw_item->label }}. </label>
                                            <label class="aw_content" for="answer_{{ $aw_item->id }}">{!! $aw_item->content !!}</label>
                                        </li>
                                    @endforeach
                                @else
                                    @foreach($answers as $aw_item)
                                        <li class="aw_item">
                                            <input type="radio" id="answer_{{ $aw_item->id }}" class="_answer" value="{{$aw_item->id}}" name="answers[{{$q_item->id }}][]">
                                            <label class="aw_label" for="answer_{{ $aw_item->id }}">{{ $aw_item->label }}. </label>
                                            <label class="aw_content" for="answer_{{ $aw_item->id }}">{!! $aw_item->content !!}</label>
                                        </li>
                                    @endforeach
                                @endif
                            @endif
                        @endif
                    </ul>
                    @else
                    
                    <?php 
                    $childs = $q_item->childs; 
                    if ($isRandomAnswer && !$answers->isEmpty()) {
                        if ($arrRanAnswers && isset($arrRanAnswers[$q_item->id])
                                && ($arrRanLabels = $arrRanAnswers[$q_item->id])) {
                            $answers = ViewTest::genAnswerFromArrLabels($arrRanLabels, $answers);
                        } else {
                            $shuffleAnswers = ViewTest::shuffleAnswers($answers, $otherRanAnswers, $qIndexValues, $originOtherAnswers, $q_item->id);
                            $answers = $shuffleAnswers['answers'];
                            $arrRanAnswers[$q_item->id] = $shuffleAnswers['save'];
                        }
                    }
                    ?>
                    
                    @if (!$answers->isEmpty())
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
                    @endif
                    
                    <ul class="list-inline q_answers q_childs_type2">
                        @if (!$childs->isEmpty())
                            @foreach($childs as $key => $q_child)
                            <li class="child_item" data-child="{{ $q_child->id }}">
                                <div class="row">
                                    <div class="col-sm-8">
                                        <div class="child_content">
                                            <span class="child_order">{{ $key + 1 }}. </span>
                                            <div class="q_content {{ $q_child->is_editor ? 'editor' : '' }}">{!! $q_child->content !!}</div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        @if (!$answers->isEmpty())
                                        <select name="answers[{{ $q_item->id }}][{{ $q_child->id }}]" class="form-control _answer select-search answer_select">
                                            <option value="">--{{ trans('test::test.select_answer') }}--</option>
                                            @foreach($answers as $aw_item)
                                            <option value="{{ $aw_item->id }}" 
                                                    {{ old('answers.'.$q_item->id.'.'.$q_child->id) == $aw_item->id ? 'selected' : '' }}>
                                                    {{ $aw_item->label }}
                                            </option>
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
                @endif <!-- end empty -->
            </div>
            @endforeach
        @endif
        @if(isset($noWritten) && $noWritten)
                <input type="text" class="hidden written_answer" value="showSubmit">
        @endif
    </div>
    @include('test::template.paginate')

    <?php
    if ($isRandomAnswer) {
        $arrTestRanAnswers[$test->id] = $arrRanAnswers;
        Session::put($keyListLabels, $arrTestRanAnswers);
        echo Form::hidden('key_rand_answer', $keyListLabels);
    }
    ?>
    
{!! Form::close() !!}

@else
    <?php
    if ($candidate) {
        CookieCore::forgetRaw(ViewTest::KEY_CURR_TEST . $candidate->id);
    }
    ?>
    <label>{{ trans('test::test.no_item') }}</label>
@endif

@stop

<?php
ViewTest::updateTestTemp($testTemp, [
    'test' => $test,
    'questionIndex' => $questionIndex,
    'isRandomAnswer' => $isRandomAnswer,
    'arrRanAnswers' => isset($arrRanAnswers) ? $arrRanAnswers : null
]);
?>

@section('footer')

<div class="main-footer">
    <div class="pull-right hidden-xs">
        <b>Version</b> {{ Config::get('view.product_version') }}
    </div>
    <strong>Copyright &copy; {{ $currentYear }} <a class="link" target="_blank" href="http://rikkeisoft.com/">RikkeiSoft</a>.</strong> All rights reserved.
</div><!-- /.container -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script>
    var mesNoAnswersForWritten = '{!! trans('test::test.You must answer at least 1 written question') !!}';
    var mesNoAnswersForWrittenTestDoesntCount = '{!! trans('test::test.Test does not count') !!} - {!! trans('test::test.You must answer at least 1 written question') !!}';
</script>
@stop
