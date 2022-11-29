@extends('test::layouts.popup')

<?php
use Rikkei\Test\View\ViewTest;
use Rikkei\Core\View\CoreLang;
use Rikkei\Test\Models\WrittenQuestion;

$title = trans('test::test.add_new');
$routeSubmit = 'test::admin.test.question.store';
$typeSubmit = 'post';
$writtenCat = [];
$collectCats = [];

if (isset($writtenQuestion) && $writtenQuestion->id) {
    $title = trans('test::test.edit_question');
    $routeSubmit = ['test::admin.test.question.written_update', $writtenQuestion->id];
    $typeSubmit = 'put';
    $writtenCat = WrittenQuestion::getWrittenCat($writtenQuestion->id);
}
$allLangs = CoreLang::allLang();
$currentLang = request()->get('lang');
if (!$currentLang) {
    $currentLang = Session::get('locale');
}
$currentLangName = isset($allLangs[$currentLang]) ? $allLangs[$currentLang] : null;
$qLangId = request()->get('q_lang_id');
if ($test) {
    $collectCats = WrittenQuestion::listWrittenCatByTestID($test->id);
}
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
                    @if (!$qLangId && isset($questionClone))
                        <p><i class="text-yellow">({{ trans('test::test.Data is suggesting from other language, please updating data') }})</i></p>
                    @endif
                    @if (!isset($writtenQuestion) || !$writtenQuestion->id)
                        <p><span class="text-green">{{ trans('test::test.Change only in edit question mode') }}</span></p>
                    @endif
                    <select class="form-control select-search" id="question_change_lang"
                            name="lang_code" data-url="{{ request()->fullUrl() }}"
                            {{ !$qLangId && (!isset($writtenQuestion) || !$writtenQuestion->id) ? 'disabled' : '' }}>
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
                ?>
                    <div class="row form-group">
                        <div class="col-sm-8">
                            <label>{{ trans('test::test.question_type') }} </label>
                            <select class="form-control select2" name="question[type]" id="question_type" disabled>
                                <option value="4">{{ $qTypes[4] }}</option>
                            </select>
                            <input type="hidden" name="question[type]" value="4">
                        </div>
                    </div>
            </div>

            <div class="form-group">
                <?php
                $content = old('question.content');
                if (isset($writtenQuestion) && !$content) {
                    $content = $writtenQuestion->content;
                }
                ?>
                <label>{{ trans('test::test.question_content') }} <em>*</em></label>
                <textarea id="edit_question_content" class="editor_question_content" name="question[content]">{!! $content !!}</textarea>

                <p class="hidden error" id="q-content-error">{{ trans('validation.required', ['attribute' => 'Test content']) }}</p>
            </div>

            <div class="form-group hidden">
                <div class="col-sm-8">
                    <div class="aw_label index">
                        <input type="text" name="answers_new[0][label]" maxlength="1"
                               class="form-control" value="0">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <?php
                $status = old('question.status');
                if (isset($writtenQuestion) && !$status) {
                    $status = $writtenQuestion->status;
                }
                ?>
                <label>{{ trans('test::test.status') }}</label>
                <select name="question[status]" class="form-control select-search">
                    @foreach (ViewTest::listStatusLabel() as $value => $label)
                        <option value="{{ $value }}" {{ $status == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <div class="form-group">
                    <label>{{ trans('test::test.category') }}</label>
                    <select class="form-control margin-bottom-5 select2" name="type_cats" data-minsearch="10">
                        <option value="">&nbsp;</option>
                        @if (isset($collectCats) && $collectCats)
                            @foreach ($collectCats as $cat)
                                <option value="{{ $cat->cat_id }}"
                                        {{ $writtenCat && $writtenCat->cat_id == $cat->cat_id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        @elseif (isset($writtenCat) && $writtenCat)
                                <option value="{{ $writtenCat->cat_id }}" selected>{{ $writtenCat->name }}</option>
                        @endif
                    </select>
                </div>

                <div class="add-type-box">
                    <div class="type-cat-new-box margin-bottom-5 hidden">
                        <div class="form-add-cat" data-url="{{ route('test::admin.test.question.add_category') }}">
                            <div class="input-group">
                                <input type="text" class="form-control cat_name">
                                <input type="hidden" value="{{ 4 }}" class="type_cat">
                                @if (isset($writtenQuestion))
                                    <input type="hidden" class="question_id" value="{{ $writtenQuestion->id }}">
                                @endif
                                @if ($test)
                                    <input type="hidden" class="test_id" value="{{ $test->id }}">
                                @endif
                                <span class="input-group-btn">
                                <button class="btn btn-primary btn-submit-cat" type="button">{{ trans('test::test.add_btn') }}</button>
                            </span>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-primary btn-add-type-cat" type="button"><i class="fa fa-plus"></i></button>
                </div>
            </div>
            <input type="hidden" name="test_id" value="{{ $test ? $test->id : null }}" />

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

        <input type="hidden" id="eit_question_id" value="{{ isset($writtenQuestion) ? $writtenQuestion->id : null }}" />
    </div>
    <div class="ans-content-col hidden">
        <div class="ans_box select-group-index action-group" data-new="new_">
            <textarea></textarea>
        </div>
    </div>

@stop


@section('script')

    <script src="{{ URL::asset('lib/js/jquery-ui.min.js') }}"></script>
    <script src="{{ URL::asset('lib/ckeditor/ckeditor.js') }}"></script>
    <script>
        var isType1 = true;
        var isType4 = true;
        var isType2 = false;
    </script>

    @include('test::template.script')

    @if (Session::has('window_script'))
        {!! Session::get('window_script') !!}
    @endif

@stop
