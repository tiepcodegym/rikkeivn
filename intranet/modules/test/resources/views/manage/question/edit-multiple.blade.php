@extends('test::layouts.popup')

<?php
use Rikkei\Test\View\ViewTest;
use Rikkei\Core\View\CoreLang;

$title = trans('test::test.add_new');
$routeSubmit = 'test::admin.test.question.store';
$typeSubmit = 'post';
if ($question && $question->id) {
    $title = trans('test::test.edit_question');
    $routeSubmit = ['test::admin.test.question.full_update', $question->id];
    $typeSubmit = 'put';
}
$allLangs = CoreLang::allLang();
$currentLang = request()->get('lang');
if (!$currentLang) {
    $currentLang = Session::get('locale');
}
$currentLangName = isset($allLangs[$currentLang]) ? $allLangs[$currentLang] : null;
$qLangId = request()->get('q_lang_id');
?>
@section('title', $title)

@section('css')
    @include('test::template.css')
    <style>
        form label {
            font-size: 15px;
            font-weight: 600;
        }
    </style>
@stop

@section('content')

    <div class="box" id="edit_question_window">

        <div class="box-body">

            {!! Form::open([
                'method' => $typeSubmit,
                'route' => $routeSubmit,
                'id' => 'form_edit_question'
            ]) !!}

            <div class="form-group row">
                <div class="col-sm-8">
                    <label>{{ trans('test::test.Language') }} </label>
                    <span class="text-blue">({{ trans('test::test.note_edit_question_lang') }})</span>
                    <a href="#question_lang_helper" data-toggle="collapse" class="link"><i class="fa fa-question-circle"></i></a>
                    <p id="question_lang_helper" class="collapse">
                        {{ trans('test::test.fields_will_synchronized') }}:
                        {{ trans('test::test.question_type') }},
                        {{ trans('test::test.category') }},
                        {{ trans('test::test.status') }}
                    </p>
                    @if (!$qLangId && isset($questionClone))
                        <p><i class="text-yellow">({{ trans('test::test.Data is suggesting from other language, please updating data') }})</i></p>
                    @endif
                    @if (!$question || !$question->id)
                        <p><span class="text-green">{{ trans('test::test.Change only in edit question mode') }}</span></p>
                    @endif
                    <select class="form-control select-search" id="question_change_lang"
                            name="lang_code" data-url="{{ request()->fullUrl() }}"
                            {{ !$qLangId && (!$question || !$question->id) ? 'disabled' : '' }}>
                        @foreach ($allLangs as $langCode => $langName)
                            <option value="{{ $langCode }}" {{ $langCode == $currentLang ? 'selected' : '' }}>{{ $langName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-4 text-right">
                    <button class="btn-edit" type="submit"><i class="fa fa-save"></i> {{ trans('test::test.save') }}</button>
                </div>
            </div>

            <div class="form-group">
                <?php
                $qTypes = ViewTest::listQuestionTypes();
                $questionType = old('question.type');
                $answers = collect();
                if ($question) {
                    $questionType = $questionType ? $questionType : $question->type;
                    if($question->answers){
                        $answers = $question->answers()->orderBy('label', 'asc')->get();
                    }
                } else {
                    $questionType = $questionType ? $questionType : ViewTest::ARR_TYPE_3[0];
                }
                if (in_array($questionType, ViewTest::ARR_TYPE_4)) {
                    $questionType = ViewTest::ARR_TYPE_3[1];
                }
                $isType1 = in_array($questionType, ViewTest::ARR_TYPE_1);
                $isType2 = in_array($questionType, ViewTest::ARR_TYPE_2);
                $answersOld = old('answers') ? old('answers') : [];
                $answersNewOld = old('answers_new');
                $answersNewCorrectOld = old('answers_new_correct');
                ?>
                <div class="row form-group">
                    <div class="col-sm-8">
                        <label>{{ trans('test::test.question_type') }} </label>
                        <a class="link" href="#question_type_helper" data-toggle="collapse"> <i>({{ trans('test::test.view_help') }})</i></a>
                        <div class="collapse" id="question_type_helper">
                            {!! trans('test::test.test_help_question_type') !!}
                            <p><a class="link" target="_blank" href="{{ ViewTest::getHelpLink() }}"> ({{ trans('test::test.detail') }})</a></p>
                        </div>
                        <select class="form-control select2" name="question[type]" id="question_type"
                                data-id="{{ $question ? $question->id : null }}"
                                data-url="{{ route('test::admin.test.queston.update_type') }}">
                            @foreach($qTypes as $key => $label)
                                <option value="{{ $key }}" {{ in_array($questionType, ViewTest::arrayTypes($key)) ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <?php
                $content = old('question.content');
                if ($question && !$content) {
                    $content = $question->content;
                    if (!$question->is_editor) {
                        $content = nl2br($content);
                    }
                    $content = htmlentities($content);
                }
                ?>
                <label>{{ trans('test::test.question_content') }} <em>*</em></label>
                <textarea id="edit_question_content" class="editor_question_content" name="question[content]">{!! $content !!}</textarea>

                <p class="hidden error" id="q-content-error">{{ trans('validation.required', ['attribute' => 'Test content']) }}</p>
            </div>

            @if ($isType2)
                <div class="form-group">
                    @include('test::manage.includes.question-childs')
                </div>
            @endif

            <div class="form-group">
                <div class="row">

                    <div class="col-sm-8">
                        <label>{{ trans('test::test.answers_list') }} <em>*</em></label>
                        @include('test::manage.includes.question-answers')
                    </div>

                @if ($isType2)
                    <!--none-->
                    @else

                        @if (!$isType1)
                            <div class="col-sm-4 ans-check-col">
                                @if (!$isType2 && !$isType1)
                                    <div>
                                        <?php
                                        $multiChoice = old('question.multi_choice');
                                        if ($question && !$multiChoice) {
                                            $multiChoice = $question->multi_choice;
                                        }
                                        $typeCheckAns = 'radio';
                                        if ($multiChoice) {
                                            $typeCheckAns = 'checkbox';
                                        }
                                        ?>
                                        <label>
                                            {{ trans('test::test.multi_choice') }} &nbsp;&nbsp;
                                            <input type="checkbox" name="question[multi_choice]" value="1" {{ $multiChoice ? 'checked' : '' }}
                                            class="input-middle" id="check_multi_choice">
                                        </label>
                                    </div>
                                @endif

                                <label>{{ trans('test::test.correct_answer') }} <em>*</em></label>
                                @if (!$answers->isEmpty())
                                    @foreach ($answers as $ans)
                                        <div class="ans_check" data-new="{{ $ans->id }}">
                                            <label>
                                                <input type="{{ $typeCheckAns }}" name="answers_correct[]" value="{{ $ans->id }}"
                                                        {{ $ans->pivot->is_correct ? 'checked' : '' }}>
                                                <span class="ans_label">{{ $ans->label }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                @endif
                            <!--temp answer check-->
                                @if ($answersNewOld)
                                    @foreach ($answersNewOld as $key => $ansData)
                                        <div class="ans_check" data-new="new_{{ $key }}">
                                            <label>
                                                <input type="{{ $typeCheckAns }}" name="answers_new_correct[]" value="{{ $key }}"
                                                        {{ $answersNewCorrectOld && in_array($key, $answersNewCorrectOld) ? 'checked' : '' }}>
                                                <span class="ans_label">{{ $ansData['label'] }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                @endif

                                <p class="error hidden" id="ans-select-error">{{ trans('test::test.Please select correct answer') }}</p>
                            </div>
                        @endif

                    @endif

                </div>
            </div>

            <div class="form-group">
                <?php
                $explain = old('question.explain');
                if ($question && !$explain) {
                    $explain = $question->explain;
                }
                ?>
                <label>{{ trans('test::test.answer_explain') }}</label>
                <textarea class="form-control resize-v" name="question[explain]">{{ $explain }}</textarea>
            </div>

            @include('test::manage.includes.question-cats')

            <div class="form-group">
                <?php
                $statuses = ViewTest::listStatusLabel();
                $status = old('question.status');
                if ($question && !$status) {
                    $status = $question->status;
                }
                ?>
                <label>{{ trans('test::test.status') }}</label>
                <select name="question[status]" class="form-control select-search">
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" {{ $status == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <input type="hidden" name="test_id" value="{{ $test ? $test->id : null }}" />
            @if (isset($qOrder))
                <input type="hidden" name="q_order" value="{{ $qOrder }}" />
            @endif

            <div class="text-center">
                <button class="btn-edit" type="submit"><i class="fa fa-save"></i>
                    {{ trans('test::test.save') }}
                </button>
            </div>

            {!! Form::close() !!}

        </div>

    </div>

    <div class="hidden">
        <div id="ans_box_tpl" class="ans_box select-group-index action-group">
            <div class="aw_label index">
                <input type="text" class="form-control">
            </div>
            <textarea class="form-control"></textarea>
            <div class="action">
                <button class="btn btn-danger btn-del-answer" type="button">
                    <i class="fa fa-close"></i>
                </button>
            </div>
        </div>

        @if ($isType2)
            <div id="ans_type2_tpl" class="select-group-index qchild-box action-group">
                <div class="index">
                    (<span class="child_num"></span>)
                </div>
                <div class="row">
                    <div class="col-sm-8">
                        <textarea rows="3" name=""></textarea>
                    </div>
                    <div class="col-sm-4">
                        <select class="form-control">
                            <option value="">&nbsp;</option>
                            @if (!$answers->isEmpty())
                                @foreach ($answers as $ans)
                                    <option value="{{ $ans->id }}" data-new="{{ $ans->id }}">{{ $ans->label }}</option>
                                @endforeach
                            @endif
                        <!--append temp answers-->
                            @if ($answersNewOld)
                                @foreach ($answersNewOld as $key => $ansData)
                                    <option value="new_{{ $key }}" data-new="new_{{ $key }}">{{ $ansData['label'] }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                <div class="action">
                    <button type="button" class="btn btn-danger btn-del-qchild">
                        <i class="fa fa-close"></i>
                    </button>
                </div>
            </div>
        @endif

        <input type="hidden" id="eit_question_id" value="{{ $question ? $question->id : null }}" />
    </div>


@stop


@section('script')

    <script src="{{ URL::asset('lib/js/jquery-ui.min.js') }}"></script>
    <script src="{{ URL::asset('lib/ckeditor/ckeditor.js') }}"></script>
    <script>
        var isType1 = parseInt('{{ $isType1 }}');
        var isType2 = parseInt('{{ $isType2 }}');
        var isType4 = false;
    </script>

    @include('test::template.script')

    <script>
        $('select.select2').select2({
            minimumResultsForSearch: 20
        });

        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        })
    </script>

    @if (Session::has('window_script'))
        {!! Session::get('window_script') !!}
    @endif

@stop
