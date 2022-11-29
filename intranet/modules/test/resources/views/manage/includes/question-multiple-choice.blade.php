<?php
use Rikkei\Test\View\ViewTest;
?>

<div class="form-group">
    <p><span class="text-warning">{!! trans('test::test.question_multilang_description') !!}</span></p>

    <div class="row">
        <div class="col-sm-8">
            <label>{{ trans('test::test.add_question') }}: </label> {{ str_repeat('&nbsp;', 4) }}
            <button type="button" class="btn btn-info margin-bottom-5" data-toggle="modal" data-target="#modal_import_file"
                    id="btn_modal_import">
                <i class="fa fa fa-file-excel-o"></i> {{ trans('test::test.import_file') }}
            </button>
            <input type="hidden" name="test_id" value="{{ $item ? $item->id : null }}">
            <span>&nbsp;&nbsp;{{ trans('test::test.or') }}&nbsp;&nbsp;</span>
            <button type="button" id="btn-create-question" class="btn-add margin-bottom-5 btn-create-question"
                    data-url="{{ route('test::admin.test.question.create', ['test_id' => $item ? $item->id : null, 'lang' => $currentLang]) }}">
                <i class="fa fa-plus"></i> {{ trans('test::test.add_new') }}
            </button>
        </div>
        <div class="col-sm-4">
            <label>{{ trans('test::test.export_question') }}: </label> {{ str_repeat('&nbsp;', 4) }}
            <button type="button" class="btn btn-primary margin-bottom-5" id="btn_copy_to" data-toggle="modal" data-target="#modal_copy_to"
                    data-url="{{ route('test::admin.test.search_test') }}">
                <i class="fa fa-copy"></i> {{ trans('test::test.copy_to') }}
            </button> 
            {{ str_repeat('&nbsp;', 2) }}
            <button type="button" class="btn btn-success margin-bottom-5" id="btn_export_question"
                    data-url="{{ route('test::admin.test.question.export_excel') }}">
                <i class="fa fa-sign-in"></i> {{ trans('test::test.export_excel') }}
            </button>
        </div>
    </div>

    <?php
    if (old('q_items')) {
        $questions = ViewTest::getQuestionsByIds(old('q_items'));
    } else {
        if (!$item) {
            $questions = collect();
        }
    }
    $arrayCategories = ViewTest::ARR_CATS;
    ?>
    
    <div class="question_box">
        <div class="margin-bottom-10">
            <label>{{ trans('test::test.select_questions') }} <em>*</em></label> {{ str_repeat('&nbsp;', 4) }}
            <i>({{ trans('test::test.drag_to_range_order') }})</i>
            <p class="error hidden" id="select_q_error">
                {{ trans('test::validate.please_select_field', ['field' => trans('test::test.question')]) }}
            </p>
        </div>
        <div class="table-responsive table-body">
            <table class="table table-bordered table-striped table-hover table-questions table-multiple-choice">
                <thead>
                    <tr>
                        <th><input type="checkbox" class="check_all"></th>
                        <th>{{ trans('core::view.NO.') }}</th>
                        <th class="minw-500">{{ trans('test::test.question_content') }}</th>
                        @foreach($arrayCategories as $key => $slug)
                        <th>{{ trans('test::test.category') . $key }}</th>
                        @endforeach
                        <th>{{ trans('test::test.status') }}</th>
                        <th class="minw-90"></th>
                    </tr>
                    <tr class="thead-filter">
                        <td></td>
                        <td></td>
                        <td></td>
                        @foreach($arrayCategories as $key => $slug)
                        <td></td>
                        @endforeach
                        <td></td>
                        <td>
                            <button class="btn btn-primary btn-sm" type="button" class="btn_reset_filter">
                                {{ trans('team::view.Reset filter') }}
                            </button>
                        </td>
                    </tr>
                </thead>
                <tbody id="list_question">
                    @if (!$questions->isEmpty())
                        @foreach($questions as $order => $qItem)
                            @include('test::manage.includes.tr-question-item', ['testId' => $item ? $item->id : null])
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    
</div>

