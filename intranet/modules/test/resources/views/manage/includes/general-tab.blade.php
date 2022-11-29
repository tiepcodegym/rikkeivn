<?php
use Rikkei\Test\Models\Test;
use Rikkei\Test\Models\Type;
use Rikkei\Test\View\ViewTest;
?>
     
<div class="row">
    <div class="col-md-6">

        <div class="form-group">
            <?php
            $testName = old('name') ? old('name') : ($item ? $item->name : null);
            ?>
            <label>{{trans('test::test.test_name')}} <em>*</em></label>
            {!! Form::text('name', $testName, ['class' => 'form-control', 'placeholder' => trans('test::test.name'), 'autocomplete' => 'off']) !!}
        </div>

        <div class="form-group">
            <?php
            $testTime = old('time') ? old('time') : ($item ? $item->time : null);
            ?>
            <label>{{trans('test::test.time')}} <em>*</em></label>
            {!! Form::number('time', $testTime, ['class' => 'form-control', 'min' => 0, 'placeholder' => trans('test::test.time')]) !!}
        </div>

        <div class="form-group">
            <?php
            $testIsAuth = old('is_auth') ? old('is_auth') : ($item ? $item->is_auth : 0);
            ?>
            <label>{!! Form::checkbox('is_auth', 1, !$testIsAuth) !!} {{ trans('test::test.test_publish') }}</label>
        </div>
        <div class="form-group">
            <?php
            $testIsLunar = old('is_lunar') ? old('is_lunar') : ($item ? $item->is_lunar : 0);
            ?>
            <label>{!! Form::checkbox('is_lunar', 1, $testIsLunar) !!} {{ trans('test::test.test_lunar') }}</label>
        </div>

        <?php
        $timeValid = old('set_valid_time') ? old('set_valid_time') : ($item ? (int) $item->set_valid_time : 0);
        ?>
        <div class="form-group" id="time-valid">
            <label>
                <input type="checkbox" name="set_valid_time" value="1" id="set_time" {{ $timeValid == 1 ? 'checked' : '' }}> 
                {{trans('test::test.validity period')}}:
            </label>
        </div>
        <div class="row {{ $timeValid != 1 ? 'hidden' : '' }}" id="time-from-to">
            <div class="col-md-6">
                <div class="form-group row">
                    <div class="col-sm-3 align-right">
                        <label>{{trans('test::test.test_from')}}</label>
                    </div>
                    <div class="col-sm-9">
                        <div class="input-group time-group">
                            <input class="form-control time" type="text" name="time_start" id="time_start" 
                                   value="{{ old('time_start') ? old('time_start') : ($item ? $item->time_start : null) }}" readonly />
                            <span class="input-group-addon">
                                <span>
                                    <i class="fa fa fa-calendar" aria-hidden="true"></i>
                                </span>
                            </span>
                       </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group row">
                <div class="col-sm-3 align-right">
                    <label>{{trans('test::test.test_to')}}</label>
                </div>
                    <div class="col-sm-9">
                        <div class="input-group time-group">
                            <input class="form-control time" type="text" name="time_end" id="time_end" 
                                   value="{{ old('time_end') ? old('time_end') : ($item ? $item->time_end : null) }}" readonly />
                            <span class="input-group-addon">
                                <span>
                                    <i class="fa fa fa-calendar" aria-hidden="true"></i>
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php
        $validMinPoint = old('set_min_point') ? old('set_min_point') : ($item ? (int) $item->set_min_point : Test::NOT_SET_MIN_POINT);
        ?>
        <div class="form-group">
            <label>
                <input type="checkbox" name="set_min_point" value="1" id="set_min_point" {{  $validMinPoint == 1 ? 'checked' : ''}}>
                {{trans('test::test.Set min point')}}
            </label>
        </div>
        <div class="row {{ $validMinPoint == Test::SET_MIN_POINT ? '' : 'hidden' }}" id="min_point">
            <div class="col-md-12">
                <div class="form-group row">
                    <div class="col-sm-3">
                        <label>{{trans('test::test.Min point')}}</label>
                    </div>
                    <div class="col-sm-9">
                        <div class="input-group">
                            <input class="form-control time" type="text" name="min_point" value="{{ old('min_point') ? old('min_point') : ($item ? $item->min_point : null) }}" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label>{{ trans('test::test.option') }}</label>
            <div class="row">
                <div class="col-md-6">
                    <ul class="list-unstyled">
                        <?php
                        $testRandomOrder = old('random_order') ? old('random_order') : ($item ? $item->random_order : null);
                        $testRandomAnswer = old('random_answer') ? old('random_answer') : ($item ? $item->random_answer : null);
                        $validViewTime = old('valid_view_time') ? old('valid_view_time') : ($item ? $item->valid_view_time : null);
                        $showAnswerOptions = [Test::SHOW_RESULT_ONLY, Test::SHOW_WRONG_ANSWER, Test::SHOW_ALL_ANSWER];
                        if (old('show_detail_answer')) {
                            $showAnswer = in_array(old('show_detail_answer'), $showAnswerOptions) ? old('show_detail_answer') : Test::SHOW_RESULT_ONLY;
                        } else {
                            $showAnswer = $item ? (int)$item->show_detail_answer : Test::SHOW_RESULT_ONLY;
                        }
                        ?>
                        <li><label>{!! Form::checkbox('random_order', 1, $testRandomOrder) !!} {{ trans('test::test.random_order_question') }}</label></li>
                        <li><label>{!! Form::checkbox('random_answer', 1, $testRandomAnswer) !!} {{ trans('test::test.random_answer') }}</label></li>
                        <li><label>{!! Form::checkbox('valid_view_time', 1, $validViewTime) !!} {{ trans('test::test.valid_view_time') }}</label> ({{ Test::VIEW_RESULT_TIME . ' ' . trans('test::test.minute') }})</li>
                        <ul class="list-unstyled show-answer-options">
                            <li>
                                <label>
                                    {!! Form::radio('show_detail_answer', Test::SHOW_RESULT_ONLY, $showAnswer === Test::SHOW_RESULT_ONLY) !!}
                                    {{ trans('test::test.show_result_only') }}
                                </label>
                            </li>
                            <li>
                                <label>
                                    {!! Form::radio('show_detail_answer', Test::SHOW_WRONG_ANSWER, $showAnswer === Test::SHOW_WRONG_ANSWER) !!}
                                    {{ trans('test::test.show_wrong_answer') }}
                                </label>
                            </li>
                            <li>
                                <label>
                                    {!! Form::radio('show_detail_answer', Test::SHOW_ALL_ANSWER, $showAnswer === Test::SHOW_ALL_ANSWER) !!}
                                    {{ trans('test::test.show_all_answer') }}
                                </label>
                            </li>
                        </ul>
                    </ul>
                </div>
            </div>

        </div>

    </div>
    
    <div class="col-md-6">

        <div class="form-group">
            <?php
            $testDesc = old('description') ? old('description') : ($item ? $item->description : null);
            ?>
            <label>{{ trans('test::test.description') }}</label>
            {!! Form::textarea('description', $testDesc, ['class' => 'form-control _no_resize', 'rows' => 5, 'placeholder' => trans('test::test.description')]) !!}
        </div>
        
        <div class="form-group">
            <?php
            $testType = old('type_id') ? old('type_id') : ($item ? $item->type_id : null);
            ?>
            <label>{{ trans('test::test.test_type') }} <em>*</em> <a class="link" target="_blank" href="{{ route('test::admin.type.create') }}">( {{ trans('test::test.add_new') }} )</a></label>
            <select class="form-control select-search data-target has-search" name="type_id" id="subjects">
                <option value="">&nbsp;</option>
                {!! Type::toNestedOptions($types, $testType) !!}
            </select>
        </div>

        <div class="form-group">
            <label>{{ trans('test::test.Thumbnail') }}</label>
            <input class="form-control" type="file" name="thumbnail">
        </div>
        @if ($item && !empty($item->thumbnail))
            <div class="form-group">
                <img src="{!! asset($item->thumbnail) !!}" alt="thumbnail" class="img-bordered-sm img-responsive img-thumbnail" width="100" height="100">
            </div>
        @endif
        
        @if ($item)
        <div class="form-group">
            <label>{{ trans('test::test.creator') }}</label>
            @if (!$item->created_by)
            <select class="form-control select-search" name="created_by" id="created_by"
                    data-remote-url="{{ route('team::employee.list.search.ajax') }}">
            </select>
            @else
            <span>
                @php
                $author = $item->author
                @endphp
                @if ($author)
                : {{ ucfirst(preg_replace('/@.*/', '', $author->email)) }}
                @endif
            </span>
            @endif
        </div>
        @endif

    </div>

    @if ($item)
    <input type="hidden" name="id" value="{{ $item->id }}" />
    @endif
</div>
