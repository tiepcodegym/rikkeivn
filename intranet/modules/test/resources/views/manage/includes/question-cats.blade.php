<?php
use Rikkei\Test\View\ViewTest;
?>

<div class="form-group">
    <label>{{ trans('test::test.category') }} <i>({{ trans('test::test.set_empty_to_delete') }})</i></label>
    <div class="row">
        <?php
        $groupCats = [];
        $collectCats = [];
        if ($question) {
            if (isset($questionClone)) {
                $question = $questionClone;
            }
            $groupCats = $question->getArrCatIds();
        }
        if ($test) {
            if (isset($testClone)) {
                $test = $testClone;
            }
            $collectCats = $test->getQuestionCats();
            if ($collectCats && !$collectCats->isEmpty()) {
                $collectCats = $collectCats->groupBy('type_cat');
            }
        }
        ?>
        @foreach (array_keys(ViewTest::ARR_CATS) as $key)
        <div class="col-sm-4 margin-bottom-5">
            <div class="form-group">
                <label>{{ trans('test::test.category') }} {{ $key }}</label>
                <select class="form-control margin-bottom-5 select2" name="type_cats[{{ $key }}]" data-minsearch="10">
                    <option value="">&nbsp;</option>
                    @if (isset($collectCats[$key]) && $collectCats[$key])
                        @foreach ($collectCats[$key] as $cat)
                        <option value="{{ $cat->id }}" 
                                {{ isset($groupCats[$key][$cat->id]) ? 'selected' : '' }}>
                                {{ $cat->name }}
                        </option>
                        @endforeach
                    @elseif (isset($groupCats[$key]) && $groupCats[$key])
                        @foreach ($groupCats[$key] as $catId => $catName)
                        <option value="{{ $catId }}" selected>{{ $catName }}</option>
                        @endforeach
                    @endif
                </select>
            </div>

            <div class="add-type-box">
                <div class="type-cat-new-box margin-bottom-5 hidden">
                    <div class="form-add-cat" data-url="{{ route('test::admin.test.question.add_category') }}">
                        <div class="input-group">
                            <input type="text" class="form-control cat_name">
                            <input type="hidden" value="{{ $key }}" class="type_cat">
                            @if ($question)
                            <input type="hidden" class="question_id" value="{{ $question->id }}">
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
        @endforeach
    </div>
</div>