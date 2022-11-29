<div class="ans-content-col">
    @if (!$answers->isEmpty())
        @foreach ($answers as $answer)
        <div class="ans_box select-group-index action-group" data-new="{{ $answer->id }}">
            <?php
            $ansContent = isset($answersOld[$answer->id]) ? $answersOld[$answer->id]['content'] : $answer->content;
            ?>
            @if (!$isType1)
            <?php
            $ansContent = htmlentities($ansContent);
            $ansLabel = isset($answersOld[$answer->id]) ? $answersOld[$answer->id]['label'] : $answer->label;
            ?>
            <div class="aw_label index">
                <input type="text" name="answers[{{ $answer->id }}][label]" maxlength="1"
                       class="form-control" value="{{ $ansLabel }}">
            </div>
            @endif
            <textarea id="answer_{{ $answer->id }}" 
                      class="form-control resize-v"
                      name="answers[{{ $answer->id }}][content]">{!! $ansContent !!}</textarea>
            <div class="action">
                <button class="btn btn-danger btn-del-answer" type="button">
                    <i class="fa fa-close"></i>
                </button>
            </div>
        </div>
        @endforeach
    @endif
    <!--temp new answer-->
    @if ($answersNewOld)
        @foreach ($answersNewOld as $key => $ansData)
        <div class="ans_box select-group-index action-group" data-new="new_{{ $key }}">
            @if (!$isType1)
            <div class="aw_label index">
                <input type="text" name="answers_new[{{ $key }}][label]" maxlength="1"
                       class="form-control" value="{{ $ansData['label'] }}">
            </div>
            @endif
            <textarea id="answer_new_{{ $key }}" 
                      class="form-control resize-v"
                      name="answers_new[{{ $key }}][content]">{!! $ansData['content'] !!}</textarea>
            <div class="action">
                <button class="btn btn-danger btn-del-answer" type="button">
                    <i class="fa fa-close"></i>
                </button>
            </div>
        </div>
        @endforeach
    @endif
</div>
<p class="error hidden" id="a-required-error">{{ trans('test::test.Answers is required') }}</p>
<p class="error hidden" id="a-label-error">{{ trans('test::test.Answer label is required') }}</p>
<p class="error hidden" id="a-content-error">{{ trans('test::test.Answer content is required') }}</p>
<p class="error hidden" id="a-label-exists-error">{{ trans('test::test.Answer label is unique') }}</p>

<div class="form-group select-group-index">
    <button type="button" class="btn-add btn-add-answer">
        <i class="fa fa-plus"></i>
    </button>
</div>