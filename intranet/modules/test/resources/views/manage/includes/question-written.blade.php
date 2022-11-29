<?php
use Rikkei\Test\View\ViewTest;

if (!$item) {
    $writtenQuestion = collect();
}
?>

<div class="form-group">
    <p><span class="text-warning">{!! trans('test::test.question_multilang_description') !!}</span></p>

    <div class="row">
        <div class="col-sm-8">
            <label>{{ trans('test::test.add_question') }}: </label> {{ str_repeat('&nbsp;', 4) }}
            <button type="button" id="btn-create-written-question" class="btn-add margin-bottom-5 btn-create-question"
                    data-url="{{ route('test::admin.test.question.create', ['test_id' => $item ? $item->id : null, 'lang' => $currentLang, 'type' => 4]) }}">
                <i class="fa fa-plus"></i> {{ trans('test::test.add_new') }}
            </button>
        </div>
    </div>

    <div class="question_box">
        <div class="margin-bottom-10">
            <p class="error hidden" id="select_q_error">
                {{ trans('test::validate.please_select_field', ['field' => trans('test::test.question')]) }}
            </p>
        </div>

        <div class="table-responsive table-body">
            <table class="table table-bordered table-striped table-hover table-questions table-written-questions">
                <thead>
                <tr>
                    <th><input type="checkbox" class="check_all"></th>
                    <th>{{ trans('core::view.NO.') }}</th>
                    <th class="minw-500">{{ trans('test::test.question_content') }}</th>
                    <th class="minw-500">{{ trans('test::test.category') }}</th>
                    <th>{{ trans('test::test.status') }}</th>
                    <th class="minw-90"></th>
                </tr>
                <tr class="thead-filter">
                    <td hidden></td>
                    <td hidden></td>
                    <td hidden></td>
                    <td hidden></td>
                    <td hidden></td>
                    <td hidden></td>
                </tr>
                </thead>
                <tbody id="list_written_question">

                @if (isset($writtenQuestion) && !$writtenQuestion->isEmpty())
                    @foreach($writtenQuestion as $order => $qItem)
                        @include('test::manage.includes.tr-written-item', ['testId' => $item ? $item->id : null])
                    @endforeach
                @endif
                </tbody>
            </table>
        </div>
    </div>
</div>