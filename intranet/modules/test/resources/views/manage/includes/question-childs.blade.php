<div class="row">
    <div class="col-sm-8">
        <label>{{ trans('test::test.question_small_content') }}</label>
    </div>
    <div class="col-sm-4"><label>{{ trans('test::test.correct_answer') }} <em>*</em></label></div>
</div>
<?php
$qChilds = $question ? $question->childs : collect();
?>
<div class="ans-check-col">
@if (!$qChilds->isEmpty())
    @foreach ($qChilds as $key => $cItem)
    <?php
    $childContent = $cItem->content;
    if (!$cItem->is_editor) {
        $childContent = nl2br($childContent);
    }
    $childContent = htmlentities($childContent);
    ?>
    <div class="form-group select-group-index qchild-box action-group">
        <div class="index">
            (<span class="child_num">{{ $key + 1 }}</span>)
        </div>
        <div class="row">
            <div class="col-sm-8">
                <textarea id="edit_question_content_{{ $cItem->id }}" class="editor_question_content" rows="3" name="childs_content[{{$cItem->id}}]"
                          >{!! $childContent !!}</textarea>
            </div>
            <div class="col-sm-4">
                <?php $cAnswer = $cItem->answers->first(); ?>
                <select class="form-control" name="answers_correct[{{ $cItem->id }}]">
                    <option value="">&nbsp;</option>
                    @if (!$answers->isEmpty())
                        @foreach ($answers as $ans)
                        <option value="{{ $ans->id }}" data-new="{{ $ans->id }}"
                                {{ $cAnswer && $cAnswer->id == $ans->id ? 'selected' : '' }}>{{ $ans->label }}</option>
                        @endforeach
                    @endif
                    <!--append temp answer-->
                    @if ($answersNewOld)
                        @foreach ($answersNewOld as $keyAns => $ansData)
                        <option value="new_{{ $keyAns }}" data-new="new_{{ $keyAns }}">{{ $ansData['label'] }}</option>
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
    @endforeach
@endif

<?php
$childNewContentOld = old('childs_new_content');
?>
@if ($childNewContentOld)
    @foreach ($childNewContentOld as $key => $childContent)
    <div class="form-group select-group-index qchild-box action-group">
        <div class="index">
            (<span class="child_num">{{ $key }}</span>)
        </div>
        <div class="row">
            <div class="col-sm-8">
                <textarea id="edit_question_content_{{ $key }}" class="editor_question_content" rows="3"
                    name="childs_new_content[{{ $key }}]">{{ $childContent }}</textarea>
            </div>
            <div class="col-sm-4">
                <select class="form-control" name="answers_new_correct[{{ $key }}]">
                    <option value="">&nbsp;</option>
                    <!--append temp answer-->
                    @if ($answersNewOld)
                        @foreach ($answersNewOld as $keyAns => $ansData)
                        <option value="new_{{ $keyAns }}" data-new="new_{{ $keyAns }}"
                            {{ (isset($answersNewCorrectOld[$key]) && 'new_' . $keyAns == $answersNewCorrectOld[$key]) ? 'selected' : '' }}
                            >{{ $ansData['label'] }}</option>
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
    @endforeach
@endif
</div>

<p class="error hidden" id="ans-select-error">{{ trans('test::test.Please select correct answer') }}</p>

<div class="add-box-area select-group-index">
    <button type="button" class="btn-add btn-add-qchild">
        <i class="fa fa-plus"></i>
    </button>
</div>
