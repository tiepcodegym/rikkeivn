<?php
use Rikkei\Team\View\Config;
use Rikkei\Core\View\View;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Education\Http\Controllers\EducationCourseController;
use Rikkei\Team\View\TeamList;
use Rikkei\Document\View\DocConst;
use Rikkei\Core\View\CookieCore;
use Rikkei\Education\Http\Services\EducationCourseService;

$teamsOptionAll = TeamList::toOption(null, true, false);
$fileMaxSize = DocConst::fileMaxSize();

$dataCookieJson = \Rikkei\Core\View\CacheBase::getFile('Education/', 'dataCourse');
$dataCookie = json_decode($dataCookieJson);

$dataCookieClass = \Rikkei\Core\View\CacheBase::getFile('Education/', 'dataClass');
$dataCookieClassSet = json_decode($dataCookieClass);

$courseFormInt = EducationCourseService::FORM_COURSE;
$vocationalFormInt = EducationCourseService::FORM_VOCATIONAL;

\Rikkei\Core\View\CacheBase::forgetFile('Education/', 'dataCourse');
\Rikkei\Core\View\CacheBase::forgetFile('Education/', 'dataClass');
?>
<div class="education-request-body margin-top-10">
    <div class="form-horizontal education-teleport col-md-12">
        <form id="frm_create_education" method="post" action="" class="has-valid " autocomplete="off"
              enctype="multipart/form-data">
            {!! csrf_field() !!}
            <div class="detail">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group margin-top-10">
                            <label for="name" class="col-md-3 control-label">
                                {{ trans('education::view.Form') }}
                                <em class="error">*</em>
                            </label>
                            <div class="col-md-3">
                                <select class="form-control" id="course_form" name="course_form">
                                    @foreach($courseForm as $key => $item)
                                        <option value="{{ $key }}"
                                        <?php
                                            if (count($collection)) {
                                                if ($key == $collection->course_form) {
                                                    echo 'selected';
                                                }
                                            }  else {
                                                if (isset($dataCookie[0]->course_form)) {
                                                    if ($dataCookie[0]->course_form == $key) {
                                                        echo 'selected';
                                                    }
                                                }
                                            }
                                            ?>
                                        >{{ $item }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <label for="name" class="col-md-3 control-label"></label>
                            <div class="col-md-3">
                                <label>
                                    <input type="checkbox" name="is_mail_list" {{ (isset($dataCookie[0]->is_mail_list) && $dataCookie[0]->is_mail_list == '1') ? 'checked' : '' }} style="position: relative; top: 2px;">
                                    <span>{{ trans('education::view.Send with data mail')}}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group margin-top-10">
                            <label for="name" class="col-md-3 control-label">
                                {{ trans('education::view.Education.Course Code') }}
                                <em class="error">*</em>
                            </label>
                            <div class="col-md-3">
                                <span>
                                    <input id="course_code" name="course_code" type="text" class="form-control" value=""
                                           disabled>
                                </span>
                            </div>
                            <label for="name" class="col-md-3 control-label">
                                {{ trans('education::view.Education.Total hours') }}
                                <em class="error">*</em>
                            </label>
                            <div class="col-md-3">
                                <span>
                                    @if (count($collection) && $collection)
                                        <input id="total_hours" name="total_hours" type="text" class="form-control" value="{{ $collection->tranning_hour }}"
                                               disabled>
                                    @else
                                        <input id="total_hours" name="total_hours" type="text" class="form-control" value=""
                                               disabled>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group margin-top-10">
                            <label for="request_date" class="col-md-3 control-label">
                                {{ trans('education::view.Education.Powerful') }}
                                <em class="error">*</em>
                            </label>
                            <div class="col-md-3">
                                <select class="form-control select-search-employee" id="powerful_id" name="powerful_id"
                                        data-remote-url="{{ URL::route('education::education.searchHrAjaxList') }}">
                                    @if($auth && count($auth))
                                        <option value="{{ $auth->id }}"
                                                selected>{{ $auth->name . ' (' . $auth->nickname .')' }}</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group margin-top-10">
                            <label for="name" class="col-md-3 control-label">
                                {{ trans('project::view.Status') }}
                                <em class="error">*</em>
                            </label>
                            <div class="col-md-3">
                                <span>
                                    <select name="type" class="form-control" aria-invalid="false" disabled>
                                        <option value="1">{{ trans('education::view.Education.Create new') }}</option>
                                    </select>
                                </span>
                            </div>
                            <label for="name" class="col-md-3 control-label">
                                {{ trans('education::view.Education.Education Type') }}
                                <em class="error">*</em>
                            </label>
                            <div class="col-md-3">
                                <span>
                                    <select name="type" id="education_type" class="form-control" aria-invalid="false">
                                        <option value=""></option>
                                        @if($educationTypes && count($educationTypes))
                                            @foreach ($educationTypes as $type)
                                                <option value="{{ $type->code }}" <?php
                                                if (count($collection) && $collection) {
                                                    if ($type->id == $collection->course_type_id) {
                                                        echo 'selected';
                                                    }
                                                } else {
                                                    if (isset($dataCookie[0]->type) && count($dataCookie[0]->type) > 0) {
                                                        if ($dataCookie[0]->type == $type->id) {
                                                            echo 'selected';
                                                        }
                                                    }
                                                }
                                                ?> data-id="{{$type->id}}">{{ $type->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </span>
                            </div>

                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group margin-top-10">
                            <label for="name" class="col-md-3 control-label">
                                {{ trans('education::view.Education.Scope') }}
                                <em class="error">*</em>
                            </label>
                            <div class="col-md-3">
                                <select class="form-control" id="scope_total" name="scope_total">
                                    @foreach($scopeTotal as $key => $item)
                                        <option value="{{ $key }}"
                                        <?php
                                            if (count($collection)) {
                                                if ($key == $collection->scope) {
                                                    echo 'selected';
                                                }
                                            }
                                            ?>
                                        >
                                            {{ $item }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group margin-top-10">
                            <label for="name" class="col-md-3 control-label">
                                {{ trans('education::view.Education.Course Name') }}
                                <em class="error">*</em>
                            </label>
                            <div class="col-md-9">
                                <span>
                                    <input id="title" maxlength="100" name="title" type="text" class="form-control" value="<?php
                                        if (count($collection)) {
                                            echo $collection->title;
                                        } else {
                                            if (isset($dataCookie[0]->course_name) && count($dataCookie[0]->course_name) > 0) {
                                                echo $dataCookie[0]->course_name;
                                            }
                                        }
                                        ?>" placeholder="{{ trans('education::view.Education.Max 100 text') }}">
                                        @if(count($collection) && $collection)
                                            <input type="hidden" class="hidden" name="teaching_id" value="{{$collection->id}}">
                                        @endif
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group margin-top-10">
                            <label for="name" class="col-md-3 control-label">
                                {{ trans('education::view.Education.Team') }}
                                <em class="error">*</em>
                            </label>
                            <div class="col-md-3">
                                <div class="dropdown team-dropdown select-full" id="select-team">
                                            <span>
                                                @include('education::manager-courses.includes.team-patch-pro-add')
                                            </span>
                                </div>
                                @if($errors->has('team_id_add'))
                                    <label class="error">{{$errors->first('team_id_add')}}</label>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-9">
                        <div class="form-group margin-top-10">
                            <label for="name" class="col-md-2 control-label">
                                {{ trans('education::view.Education.Course Target') }}
                                <em class="error">*</em>
                            </label>
                            <div class="col-md-10">
                                <span>
                                    <textarea rows="2" class="form-control col-md-9" id="target" name="target"><?php
                                        if (count($collection)) {
                                            echo $collection->target;
                                        } else {
                                            if (isset($dataCookie[0]->target) && count($dataCookie[0]->target) > 0) {
                                                echo $dataCookie[0]->target;
                                            }
                                        }
                                        ?></textarea>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-9">
                        <div class="form-group margin-top-10">
                            <label for="name" class="col-md-2 control-label">
                                {{ trans('education::view.Education.Content') }}
                                <em class="error">*</em>
                            </label>
                            <div class="col-md-10">
                                <span>
                                    <textarea rows="6" class="form-control col-md-9" id="description" name="description"><?php
                                        if (count($collection)) {
                                            echo $collection->content;
                                        } else {
                                            if (isset($dataCookie[0]->description) && count($dataCookie[0]->description) > 0) {
                                                echo $dataCookie[0]->description;
                                            }
                                        }
                                        ?></textarea>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-9">
                        <div class="form-group">
                            <label for="name" class="col-md-2 control-label">
                                {{ trans('education::view.Education.Class List') }}
                            </label>
                            <div class="col-md-10">
                                <hr>
                            </div>
                        </div>
                    </div>
                </div>

                {{---------------------------------------}}

                <div class="form-class">

                    <?php if (isset($dataCookieClassSet) && count($dataCookieClassSet) > 0) { ?>

                    @foreach ($dataCookieClassSet as $index => $value)

                        <div id="class_{{$value->class_element}}" class="class-child"
                             data-classname="{{$value->class_element}}">

                            <div class="row">
                                <div class="col-md-9">
                                    <div class="form-group margin-top-10">
                                        <label for="name" class="col-md-2 control-label">
                                            {{ trans('education::view.Education.Class Name') }}
                                            <em class="error">*</em>
                                        </label>
                                        <div class="col-md-6">
                                            <span>
                                                <input id="class_title_{{$value->class_element}}" maxlength="100"
                                                       name="class_title_{{$value->class_element}}" type="text"
                                                       class="form-control class_title" value="{{$value->class_name}}"
                                                       placeholder="{{ trans('education::view.Education.Max 100 text') }}">
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-9">
                                    <div class="form-group margin-top-10">
                                        <label for="name" class="col-md-2 control-label">
                                            {{ trans('education::view.Education.Class Code') }}
                                            <em class="error">*</em>
                                        </label>
                                        <div class="col-md-2">
                                            <span>
                                                <input id="class_code_{{$value->class_element}}"
                                                       name="class_code_{{$value->class_element}}" type="text"
                                                       class="form-control class_code" value="" disabled>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group margin-top-10">
                                        <label for="request_date" class="col-md-3 control-label">
                                        </label>
                                        <div class="col-md-3">
                                            <div class="form-group col-md-12">
                                                <label>
                                                    <input type="checkbox" value="1"
                                                           class="ng-valid ng-dirty ng-touched check-rent" <?php if ($value->related_name == 'teacher_without') {
                                                        echo 'checked';
                                                    } ?>>
                                                    <span>
                                                        {{ trans('education::view.Education.Teacher Rent') }}
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                        <label for="request_date" class="col-md-3 control-label">
                                            {{ trans('education::view.Education.Teacher') }}
                                        </label>
                                        @if($value->related_name == 'employee')
                                            <div class="col-md-3 teacher-select">
                                                <select class="form-control select-search-employee teacher_id_select" id="teacher_id_select_{{$value->class_element}}" name="teacher_id_select_{{$value->class_element}}" data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee') }}">
                                                    <option value="{{ $value->related_id }}" selected>{{ EducationCourseController::getNameTeacher($value->related_id, 1) }}</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3 hide teacher-input">
                                                <input id="teacher_id_input_{{$value->class_element}}"
                                                       name="teacher_id_input_{{$value->class_element}}" type="text"
                                                       class="form-control teacher_id_input" value="">
                                            </div>
                                        @elseif($value->related_name == 'teacher_without')
                                            <div class="col-md-3 hide teacher-select">
                                                <select class="form-control select-search-employee teacher_id_select"
                                                        id="teacher_id_select_{{$value->class_element}}"
                                                        name="teacher_id_select_{{$value->class_element}}"
                                                        data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee') }}">
                                                    @if($teachers && count($teachers))
                                                        <option value="{{ $teachers->id }}"
                                                                selected>{{ $teachers->name }}</option>
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="col-md-3 teacher-input">
                                                <input id="teacher_id_input_{{$value->class_element}}"
                                                       name="teacher_id_input_{{$value->class_element}}" type="text"
                                                       class="form-control teacher_id_input"
                                                       value="{{ EducationCourseController::getNameTeacher($value->related_id, 2) }}">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group margin-top-10">
                                        <label for="request_date" class="col-md-3 control-label"></label>
                                        <div class="col-md-3">
                                            <div class="form-group col-md-12">
                                                <label>
                                                    <input type="checkbox" value="1"
                                                           class="ng-valid ng-dirty ng-touched check-commitment" <?php if ($value->is_commitment == 1) {
                                                        echo 'checked';
                                                    } ?>>
                                                    <span>
                                                    {{ trans('education::view.Education.Commitment') }}
                                                </span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="commitment-show <?php if ($value->is_commitment == 1) {
                                            echo '';
                                        } else {
                                            echo 'hide';
                                        } ?>">
                                            <label for="request_date" class="col-md-3 control-label">
                                                {{ trans('education::view.Education.Start') }}
                                                <em class="error">*</em>
                                            </label>
                                            <div class="col-md-3">
                                                <div class="input-group padding-0">
                                                <span class="input-group-btn">
                                                    <button class="btn btn-default" type="button"><i
                                                                class="fa fa-calendar"></i></button>
                                                </span>
                                                    <input type='text' autocomplete="off"
                                                           class="form-control date start-date"
                                                           id="start_date_{{$value->class_element}}"
                                                           name="start_date_{{$value->class_element}}"
                                                           data-provide="datepicker" placeholder="YYYY-MM-DD H:mm"
                                                           value="{{$value->start_date}}"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group margin-top-10">
                                        <div class="commitment-show <?php if ($value->is_commitment == 1) {
                                            echo '';
                                        } else {
                                            echo 'hide';
                                        } ?>">
                                            <label for="request_date" class="col-md-3 control-label">
                                                {{ trans('education::view.Education.End') }}
                                                <em class="error">*</em>
                                            </label>
                                            <div class="col-md-3">
                                                <div class="input-group padding-0">
                                                    <span class="input-group-btn">
                                                        <button class="btn btn-default" type="button"><i
                                                                    class="fa fa-calendar"></i></button>
                                                    </span>
                                                    <input type='text' autocomplete="off"
                                                           class="form-control date end-date"
                                                           id="end_date_{{$value->class_element}}"
                                                           name="end_date_{{$value->class_element}}"
                                                           data-provide="datepicker" placeholder="YYYY-MM-DD H:mm"
                                                           value="{{$value->end_date}}"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-ca">
                                @if($value->data_shift && count($value->data_shift) > 0)
                                    @foreach ($value->data_shift as $shift => $valShift)
                                        <div class="row row-ca">
                                            <div class="col-md-6">
                                                <div class="form-group margin-top-10">
                                                    <label for="request_date" class="col-md-3 control-label"></label>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label>
                                                                    <span>
                                                                        {{ trans('education::view.Education.Ca') }}
                                                                    </span>
                                                                <span class="auto-gen">{{ $valShift->name }}
                                                                    </span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <label for="request_date" class="col-md-3 control-label">
                                                        {{ trans('education::view.Education.Start') }}
                                                        <em class="error">*</em>
                                                    </label>
                                                    <div class="col-md-3">
                                                        <div class="input-group padding-0">
                                                                <span class="input-group-btn">
                                                                    <button class="btn btn-default" type="button"><i
                                                                                class="fa fa-calendar"></i></button>
                                                                </span>
                                                            <input type='text' autocomplete="off"
                                                                   class="form-control date start-date-ca"
                                                                   data-provide="datepicker"
                                                                   placeholder="YYYY-MM-DD H:mm"
                                                                   value="{{ $valShift->start_date_time }}"/>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group margin-top-10">
                                                    <label for="request_date" class="col-md-3 control-label">
                                                        {{ trans('education::view.Education.End') }}
                                                        <em class="error">*</em>
                                                    </label>
                                                    <div class="col-md-3">
                                                        <div class="input-group padding-0">
                                                                <span class="input-group-btn">
                                                                    <button class="btn btn-default" type="button"><i
                                                                                class="fa fa-calendar"></i></button>
                                                                </span>
                                                            <input type='text' autocomplete="off"
                                                                   class="form-control date end-date-ca"
                                                                   data-provide="datepicker"
                                                                   placeholder="YYYY-MM-DD H:mm"
                                                                   value="{{ $valShift->end_date_time }}"/>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 select-container">
                                                        <button class="btn-add add-ca vocational-affect">
                                                            <i class="fa fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group margin-top-10">
                                                    <label for="request_date" class="col-md-3 control-label"></label>
                                                    <div class="col-md-3">
                                                        <div class="col-md-6"></div>
                                                        <div class="form-group">
                                                        </div>
                                                    </div>
                                                    <label for="request_date" class="col-md-3 control-label">
                                                        {{ trans('education::view.Education.End Time Register') }}
                                                        <em class="error">*</em>
                                                    </label>
                                                    <div class="col-md-3">
                                                        <div class="input-group padding-0">
                                                                <span class="input-group-btn">
                                                                    <button class="btn btn-default" type="button"><i
                                                                                class="fa fa-calendar"></i></button>
                                                                </span>
                                                            <input type='text' autocomplete="off"
                                                                   class="form-control end-time-register" data-provide="datepicker"
                                                                   placeholder="YYYY-MM-DD H:mm"
                                                                   value="{{ $valShift->end_time_register }}"/>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6 element-right">
                                                <div class="form-group margin-top-10">
                                                    <label for="name" class="col-md-3 control-label">
                                                        {{ trans('education::view.Education.Location') }}
                                                        <em class="error">*</em>
                                                    </label>
                                                    <div class="col-md-3 select-container">
                                                        <select class="form-control calendar-room" >
                                                            <option value="0">{{ trans('resource::view.Select meeting room') }}</option>
                                                        </select>
                                                        <i class="fa fa-refresh fa-spin loading-room hidden"></i>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    @endforeach
                                @else
                                    <div class="row row-ca">
                                        <div class="col-md-6">
                                            <div class="form-group margin-top-10">
                                                <label for="request_date" class="col-md-3 control-label"></label>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>
                                                            <span>
                                                                {{ trans('education::view.Education.Ca') }}
                                                            </span>
                                                            <span class="auto-gen">1</span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <label for="request_date" class="col-md-3 control-label">
                                                    {{ trans('education::view.Education.Start') }}
                                                    <em class="error">*</em>
                                                </label>
                                                <div class="col-md-3">
                                                    <div class="input-group padding-0">
                                                    <span class="input-group-btn">
                                                        <button class="btn btn-default" type="button"><i
                                                                    class="fa fa-calendar"></i></button>
                                                    </span>
                                                        <input type='text' autocomplete="off"
                                                               class="form-control date start-date-ca"
                                                               data-provide="datepicker" placeholder="YYYY-MM-DD H:mm"
                                                               value="{{ (!empty($minDate) && isset($minDate)) ? $minDate : '' }}"/>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group margin-top-10">
                                                <label for="request_date" class="col-md-3 control-label">
                                                    {{ trans('education::view.Education.End') }}
                                                    <em class="error">*</em>
                                                </label>
                                                <div class="col-md-3">
                                                    <div class="input-group padding-0">
                                                    <span class="input-group-btn">
                                                        <button class="btn btn-default" type="button"><i
                                                                    class="fa fa-calendar"></i></button>
                                                    </span>
                                                        <input type='text' autocomplete="off"
                                                               class="form-control date end-date-ca"
                                                               data-provide="datepicker" placeholder="YYYY-MM-DD H:mm"
                                                               value="{{ (!empty($maxDate) && isset($maxDate)) ? $maxDate : '' }}"/>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 select-container">
                                                    <button class="btn-add add-ca vocational-affect">
                                                        <i class="fa fa-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6 element-right">
                                            <div class="form-group margin-top-10">
                                                <label for="name" class="col-md-3 control-label">
                                                    {{ trans('education::view.Education.Location') }}
                                                    <em class="error">*</em>
                                                </label>
                                                <div class="col-md-3 select-container">
                                                    <select class="form-control calendar-room" >
                                                        <option value="0">{{ trans('resource::view.Select meeting room') }}</option>
                                                    </select>
                                                    <i class="fa fa-refresh fa-spin loading-room hidden"></i>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                @endif
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group margin-top-10">
                                        <label for="request_date" class="col-md-3 control-label">
                                            {{ trans('education::view.Education.Document') }}
                                        </label>
                                        <div class="col-md-6">
                                            <div class="upload-wrapper">

                                                <ul>
                                                    @if(isset($value->documents) && count($value->documents) > 0)
                                                        @foreach ($value->documents as $key => $valDoc)
                                                            <li>
                                                                <a href="http://rikkei.sd/storage/education/{{$valDoc->url}}">{{$valDoc->name}}</a>
                                                            </li>
                                                        @endforeach
                                                    @endif
                                                </ul>

                                                <div class="list-input-fields">
                                                    <div class="attach-file-item form-group">
                                                        <div class="col-md-2">
                                                            <button style="margin-bottom: 10px;"
                                                                    class="btn btn-danger btn-sm btn-del-file" type="button"
                                                                    title="{{ trans('doc::view.Delete') }}">
                                                                <i class="fa fa-close"></i>
                                                            </button>
                                                        </div>
                                                        <div class="col-md-10">
                                                            <input type="file" class="filebrowse" name="attach_files[]"
                                                            >
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 form-group">
                                                    <button type="button" class="btn btn-primary btn-sm"
                                                        data-name="attach_files[]"><i class="fa fa-plus"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-9">
                                    <div class="form-group">
                                        <label for="name" class="col-md-2 control-label">
                                        </label>
                                        <div class="col-md-10">
                                            <hr>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                    @endforeach

                    <?php } else { ?>

                    <div id="class_1" class="class-child" data-classname="1">
                        <div class="row">
                            <div class="col-md-9">
                                <div class="form-group margin-top-10">
                                    <label for="name" class="col-md-2 control-label">
                                        {{ trans('education::view.Education.Class Name') }}
                                        <em class="error">*</em>
                                    </label>
                                    <div class="col-md-6">
                                            <span>
                                                <input id="class_title_1" maxlength="100" name="class_title_1"
                                                       type="text" class="form-control class_title" value=""
                                                       placeholder="{{ trans('education::view.Education.Max 100 text') }}">
                                            </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-9">
                                <div class="form-group margin-top-10">
                                    <label for="name" class="col-md-2 control-label">
                                        {{ trans('education::view.Education.Class Code') }}
                                        <em class="error">*</em>
                                    </label>
                                    <div class="col-md-2">
                                            <span>
                                                <input id="class_code_1" name="class_code_1" type="text"
                                                       class="form-control class_code" value="" disabled>
                                            </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group margin-top-10">
                                    <label for="request_date" class="col-md-3 control-label">
                                    </label>
                                    <div class="col-md-3">
                                        <div class="form-group col-md-12">
                                            <label>
                                                <input type="checkbox" value="1"
                                                       class="ng-valid ng-dirty ng-touched check-rent">
                                                <span>
                                                        {{ trans('education::view.Education.Teacher Rent') }}
                                                    </span>
                                            </label>
                                        </div>
                                    </div>
                                    <label for="request_date" class="col-md-3 control-label">
                                        {{ trans('education::view.Education.Teacher') }}
                                    </label>
                                    <div class="col-md-3 teacher-select">
                                        <select class="form-control select-search-employee teacher_id_select"
                                                id="teacher_id_select_1" name="teacher_id_select_1"
                                                data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee') }}">
                                            @if(count($collection) && $collection)
                                                <option value="{{ $collection->employee_id }}" selected>{{ EducationCourseController::getNameTeacher($collection->employee_id, 1) }}</option>
                                            @else
                                                @if($teachers && count($teachers))
                                                    <option value="{{ $teachers->id }}"
                                                            selected>{{ $teachers->name }}</option>
                                                @endif
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-3 hide teacher-input">
                                        <input id="teacher_id_input_1" name="teacher_id_input_1" type="text"
                                               class="form-control teacher_id_input" value="">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group margin-top-10">
                                    <label for="request_date" class="col-md-3 control-label"></label>
                                    <div class="col-md-3">
                                        <div class="form-group col-md-12">
                                            <label>
                                                <input type="checkbox" value="1"
                                                       class="ng-valid ng-dirty ng-touched check-commitment">
                                                <span>
                                                    {{ trans('education::view.Education.Commitment') }}
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="commitment-show hide">
                                        <label for="request_date" class="col-md-3 control-label">
                                            {{ trans('education::view.Education.Start') }}
                                            <em class="error">*</em>
                                        </label>
                                        <div class="col-md-3">
                                            <div class="input-group padding-0">
                                                <span class="input-group-btn">
                                                    <button class="btn btn-default" type="button"><i
                                                                class="fa fa-calendar"></i></button>
                                                </span>
                                                <input type='text' autocomplete="off"
                                                       class="form-control date start-date" id="start_date_1"
                                                       name="start_date_1" data-provide="datepicker"
                                                       placeholder="YYYY-MM-DD H:mm"
                                                       value="{{ (!empty($education->start_date) && isset($education->start_date)) ? $education->start_date : old('start_date') }}"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group margin-top-10">
                                    <div class="commitment-show hide">
                                        <label for="request_date" class="col-md-3 control-label">
                                            {{ trans('education::view.Education.End') }}
                                            <em class="error">*</em>
                                        </label>
                                        <div class="col-md-3">
                                            <div class="input-group padding-0">
                                                    <span class="input-group-btn">
                                                        <button class="btn btn-default" type="button"><i
                                                                    class="fa fa-calendar"></i></button>
                                                    </span>
                                                <input type='text' autocomplete="off" class="form-control date end-date"
                                                       id="end_date_1" name="end_date_1" data-provide="datepicker"
                                                       placeholder="YYYY-MM-DD H:mm"
                                                       value="{{ (!empty($education->end_date) && isset($education->end_date)) ? $education->end_date : old('end_date') }}"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-ca">
                            @if(count($collection) && $collection)
                                @if(count($collection->teacherTime) > 0 && $collection->teacherTime)
                                    @foreach ($collection->teacherTime as $shift => $valShift)
                                        <div class="row row-ca" data-shift-id="{{$valShift->id}}">
                                            <div class="col-md-6">
                                                <div class="form-group margin-top-10">
                                                    <label for="request_date" class="col-md-3 control-label"></label>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label>
                                                                    <span>
                                                                        {{ trans('education::view.Education.Ca') }}
                                                                    </span>
                                                                <span class="auto-gen">{{ $valShift->name }}
                                                                    </span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <label for="request_date" class="col-md-3 control-label">
                                                        {{ trans('education::view.Education.Start') }}
                                                        <em class="error">*</em>
                                                    </label>
                                                    <div class="col-md-3">
                                                        <div class="input-group padding-0">
                                                                <span class="input-group-btn">
                                                                    <button class="btn btn-default" type="button"><i class="fa fa-calendar"></i></button>
                                                                </span>
                                                            <input type='text' autocomplete="off" class="form-control date start-date-ca"  data-provide="datepicker" placeholder="YYYY-MM-DD H:mm" value="{{ $valShift->start_date }}" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group margin-top-10">
                                                    <label for="request_date" class="col-md-3 control-label">
                                                        {{ trans('education::view.Education.End') }}
                                                        <em class="error">*</em>
                                                    </label>
                                                    <div class="col-md-3">
                                                        <div class="input-group padding-0">
                                                                <span class="input-group-btn">
                                                                    <button class="btn btn-default" type="button"><i class="fa fa-calendar"></i></button>
                                                                </span>
                                                            <input type='text' autocomplete="off" class="form-control date end-date-ca"  data-provide="datepicker" placeholder="YYYY-MM-DD H:mm" value="{{ $valShift->end_date }}" />
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 select-container">
                                                        @if($shift == 0)
                                                            <button class="btn-add add-ca vocational-affect">
                                                                <i class="fa fa-plus"></i>
                                                            </button>
                                                        @else
                                                            <button class="btn-delete rm_ca">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group margin-top-10">
                                                    <label for="request_date" class="col-md-3 control-label"></label>
                                                    <div class="col-md-3">
                                                        <div class="col-md-6"></div>
                                                        <div class="form-group">
                                                        </div>
                                                    </div>
                                                    <label for="request_date" class="col-md-3 control-label">
                                                        {{ trans('education::view.Education.End Time Register') }}
                                                        <em class="error">*</em>
                                                    </label>
                                                    <div class="col-md-3">
                                                        <div class="input-group padding-0">
                                                                <span class="input-group-btn">
                                                                    <button class="btn btn-default" type="button"><i
                                                                                class="fa fa-calendar"></i></button>
                                                                </span>
                                                            <input type='text' autocomplete="off"
                                                                   class="form-control end-time-register" data-provide="datepicker"
                                                                   placeholder="YYYY-MM-DD H:mm"
                                                                   value=""/>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6 element-right">
                                                <div class="form-group margin-top-10">
                                                    <label for="name" class="col-md-3 control-label">
                                                        {{ trans('education::view.Education.Location') }}
                                                        <em class="error">*</em>
                                                    </label>
                                                    <div class="col-md-3 select-container">
                                                        <select class="form-control calendar-room" >
                                                            <option value="0">{{ trans('resource::view.Select meeting room') }}</option>
                                                        </select>
                                                        <i class="fa fa-refresh fa-spin loading-room hidden"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            @else
                                <div class="row row-ca">
                                    <div class="col-md-6">
                                        <div class="form-group margin-top-10">
                                            <label for="request_date" class="col-md-3 control-label"></label>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>
                                                        <span>
                                                            {{ trans('education::view.Education.Ca') }}
                                                        </span>
                                                        <span class="auto-gen">
                                                            1
                                                        </span>
                                                    </label>
                                                </div>
                                            </div>
                                            <label for="request_date" class="col-md-3 control-label">
                                                {{ trans('education::view.Education.Start') }}
                                                <em class="error">*</em>
                                            </label>
                                            <div class="col-md-3">
                                                <div class="input-group padding-0">
                                                        <span class="input-group-btn">
                                                            <button class="btn btn-default" type="button"><i
                                                                        class="fa fa-calendar"></i></button>
                                                        </span>
                                                    <input type='text' autocomplete="off"
                                                           class="form-control date start-date-ca" data-provide="datepicker"
                                                           placeholder="YYYY-MM-DD H:mm"
                                                           value="{{ (!empty($minDate) && isset($minDate)) ? $minDate : '' }}"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group margin-top-10">
                                            <label for="request_date" class="col-md-3 control-label">
                                                {{ trans('education::view.Education.End') }}
                                                <em class="error">*</em>
                                            </label>
                                            <div class="col-md-3">
                                                <div class="input-group padding-0">
                                                        <span class="input-group-btn">
                                                            <button class="btn btn-default" type="button"><i
                                                                        class="fa fa-calendar"></i></button>
                                                        </span>
                                                    <input type='text' autocomplete="off"
                                                           class="form-control date end-date-ca" data-provide="datepicker"
                                                           placeholder="YYYY-MM-DD H:mm"
                                                           value="{{ (!empty($maxDate) && isset($maxDate)) ? $maxDate : '' }}"/>
                                                </div>
                                            </div>
                                            <div class="col-md-3 select-container">
                                                <button class="btn-add add-ca vocational-affect">
                                                    <i class="fa fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group margin-top-10">
                                            <label for="request_date" class="col-md-3 control-label"></label>
                                            <div class="col-md-3">
                                                <div class="col-md-6"></div>
                                                <div class="form-group">
                                                </div>
                                            </div>
                                            <label for="request_date" class="col-md-3 control-label">
                                                {{ trans('education::view.Education.End Time Register') }}
                                                <em class="error">*</em>
                                            </label>
                                            <div class="col-md-3">
                                                <div class="input-group padding-0">
                                                                <span class="input-group-btn">
                                                                    <button class="btn btn-default" type="button"><i
                                                                                class="fa fa-calendar"></i></button>
                                                                </span>
                                                    <input type='text' autocomplete="off"
                                                           class="form-control end-time-register" data-provide="datepicker"
                                                           placeholder="YYYY-MM-DD H:mm"
                                                           value=""/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group margin-top-10">
                                            <label for="name" class="col-md-3 control-label">
                                                {{ trans('education::view.Education.Location') }}
                                                <em class="error">*</em>
                                            </label>
                                            <div class="col-md-3 select-container">
                                                <select class="form-control calendar-room" >
                                                    <option value="0">{{ trans('resource::view.Select meeting room') }}</option>
                                                </select>
                                                <i class="fa fa-refresh fa-spin loading-room hidden"></i>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            @endif
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group margin-top-10">
                                    <label for="request_date" class="col-md-3 control-label">
                                        {{ trans('education::view.Education.Document') }}
                                    </label>
                                    <div class="col-md-6">
                                        <div class="upload-wrapper">
                                            <div class="list-input-fields">
                                                <div class="attach-file-item form-group">
                                                    <div class="col-md-2">
                                                        <button style="margin-bottom: 10px;"
                                                                         class="btn btn-danger btn-sm btn-del-file" type="button"
                                                                         title="{{ trans('doc::view.Delete') }}">
                                                            <i class="fa fa-close"></i>
                                                        </button>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="file" class="filebrowse" name="attach_files[]">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12 form-group">
                                                <button type="button" class="btn btn-primary btn-sm"
                                                    data-name="attach_files[]"><i class="fa fa-plus"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row vocational-affect">
                            <div class="col-md-9">
                                <div class="form-group">
                                    <label for="name" class="col-md-2 control-label">
                                    </label>
                                    <div class="col-md-10">
                                        <hr>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <?php } ?>

                </div>

                <div class="row vocational-affect">
                    <div class="col-md-6">
                        <div class="form-group margin-top-10">
                            <label for="request_date" class="col-md-3 control-label">
                            </label>
                            <div class="col-md-6">
                                <button class="btn-add vocational-affect" id="addClass">
                                    <i class="fa fa-plus"></i>
                                </button>
                                {{ trans('education::view.Education.Add Class') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row vocational-affect">
                    <div class="col-md-9">
                        <div class="form-group">
                            <label for="name" class="col-md-2 control-label">
                            </label>
                            <div class="col-md-10">
                                <hr>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-10 align-center margin-top-40">
                        <button type="button" class="btn btn-success btn-submit-confirm " id="eventSaveAdd">
                            {{ trans('education::view.Education.Save') }}
                            <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh save-refresh"></i>
                        </button>
                        <button type="button" class="btn btn-warning btn-submit-confirm" id="eventCoppy"
                                disabled>{{ trans('education::view.Education.Coppy') }}</button>
                        <button type="button" class="btn btn-primary btn-submit-confirm"
                                id="eventSent">{{ trans('education::view.Education.Sent') }}</button>
                        <button type="button" class="btn btn-danger btn-submit-confirm"
                                id="eventClose">{{ trans('education::view.Education.Close') }}</button>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>
<div id="error_append">
    <label class="error" style="display: block;"></label>
</div>
<input id="token" type="hidden" value="{{ Session::token() }}"/>
<!-- Check value if press back button then reload page -->
<input type="hidden" id="refreshed" value="no">

<div class="modal fade" id="modal-education-add" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
                <p class="text-default">
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default"
                        data-dismiss="modal">{{ trans('education::view.Education.Close') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>

<div class="modal fade" id="modal-education-add-error" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
                <p class="text-default">
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default"
                        data-dismiss="modal">{{ trans('education::view.Education.Close') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>

<div class="modal fade" id="modal-education-form" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
                <p class="text-default">
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default"
                        data-dismiss="modal">{{ trans('education::view.Education.Close') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>

<div class="modal fade" id="modal-education-ckeditor" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
                <textarea name="ck-template-mail-course" class="ckedittor-text hidden" id="ck-template-mail-course">{{ isset($templateMail[0]) ? $templateMail[0]->description : 'No content' }}</textarea>
                <textarea name="ck-template-mail-vocational" class="ckedittor-text hidden" id="ck-template-mail-vocational">{{ isset($templateMail[1]) ? $templateMail[1]->description : 'No content' }}</textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ trans('education::view.Education.Close') }}</button>
                <button id="modal-education-ckeditor-ok"  type="button" class="btn btn-primary">{{ trans('education::view.Education.Send') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>

<div class="modal fade" id="modal-education-employee-list" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
                <p class="text-default"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default"
                        data-dismiss="modal">OK</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>

<!-- Styles -->
@section('css')
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/minimal/_all.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css"/>
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css"
          rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="{{ asset('team/css/style.css') }}" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="{{ asset('education/css/education.css') }}" rel="stylesheet" type="text/css">
@endsection

<!-- Script -->
@section('script')
    <script>
        var docParams = {
            maxSize: parseFloat('<?php echo $fileMaxSize ?>'),
            errorFileMaxSize: '<?php echo trans('doc::message.file_max_size', ['max' => $fileMaxSize]) ?>',
            urlCheckExistCode: '{{ route("doc::admin.check_exists") }}',
            errorCodeExists: '<?php echo trans('validation.unique', ['attribute' => 'Document code']) ?>',
            typeEditor: '{{ DocConst::TYPE_ASSIGNE_EDITOR }}',
            urlSearchReviewers: '{{ route("doc::admin.search_assignees") }}',
            urlGetSuggestReviewers: '{{ route("doc::admin.suggest_reviewers") }}',
            typeReviewer: '{{ DocConst::TYPE_ASSIGNE_REVIEW }}',
            typePublisher: '{{ DocConst::TYPE_ASSIGNE_PUBLISH }}',
        };
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/icheck.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
    <script src="{{ asset('asset_managetime/js/script.js') }}"></script>
    <script src="{{ URL::asset('lib/ckeditor/ckeditor.js') }}"></script>
    <script src="{{ URL::asset('lib/ckfinder/ckfinder.js') }}"></script>
    <script type="text/javascript">
    </script>
    <script>
        var ckeditor = CKEDITOR.replace('ck-template-mail-course', {
            extraPlugins: 'autogrow,image2,fixed',
            removePlugins: 'justify,colorbutton,indentblock,resize,fixed,resize,autogrow',
            removeButtons: 'About',
            startupFocus: true,
        });

        var ckeditor2 = CKEDITOR.replace('ck-template-mail-vocational', {
            extraPlugins: 'autogrow,image2,fixed',
            removePlugins: 'justify,colorbutton,indentblock,resize,fixed,resize,autogrow',
            removeButtons: 'About',
            startupFocus: true,
        });

        CKFinder.setupCKEditor( ckeditor, '/lib/ckfinder' );
        var teamPath = JSON.parse('{!! json_encode($teamPath) !!}');
        var globalStoreFiles = {};

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        setTimeout(function () {
            $('#education_type').trigger('change');
        }, 2000);

        var RKVarPassGlobal = {
            teamPath: JSON.parse('{!! json_encode($teamPath) !!}'),
            teamSelected: JSON.parse('{!! json_encode($allTeamDraft) !!}'),
        }
        // render team dev option
        if (typeof RKVarPassGlobal !== 'undefined' && $('select.team-dev-tree').length) {
            var teamDevOption = RKfuncion.teamTree.init(RKVarPassGlobal.teamPath, RKVarPassGlobal.teamSelected);
            var htmlTeamDevOption, disabledTeamDevOption, selectedTeamDevOption;
            $.each(teamDevOption, function (i, v) {
                disabledTeamDevOption = '';
                selectedTeamDevOption = v.selected ? ' selected' : '';
                htmlTeamDevOption += '<option value="' + v.id + '"' + disabledTeamDevOption + '' + selectedTeamDevOption + '>' + v.label + '</option>';
            });
            $('select.team-dev-tree').append(htmlTeamDevOption);
        }

        $('.start-date, .end-date').datetimepicker({
            allowInputToggle: true,
            minDate: moment().format('YYYY-MM-DD'),
            format: 'YYYY-MM-DD H:mm',
            sideBySide: true
        });

        $('.end-time-register').datetimepicker({
            allowInputToggle: true,
            minDate: moment().format('YYYY-MM-DD'),
            maxDate: $('.start-date-ca').val(),
            format: 'YYYY-MM-DD H:mm',
            sideBySide: true
        });

        ///////////

        $('.start-date-ca').datetimepicker({
            allowInputToggle: true,
            minDate: moment().format('YYYY-MM-DD H:mm'),
            format: 'YYYY-MM-DD H:mm',
            sideBySide: true
        }).on('dp.hide', function () {
            if ($(this).val()) {
                $(this).parents('.row-ca').find('.end-date-ca').data('DateTimePicker').minDate($(this).val());
            } else {
                $(this).parents('.row-ca').find('.end-date-ca').data('DateTimePicker').minDate('2000-01-01');
            }
            validateClientConflictOff($(this));
            validateClientConflictOff($(this).parents('.row-ca').find('.end-date-ca'));
            checkRoomAvailable($(this));
            var dataStart = $(this).val();
            $(this).parents('.row-ca').find('.end-time-register').datetimepicker('destroy');
            $(this).parents('.row-ca').find('.end-time-register').val(dataStart);
            $(this).parents('.row-ca').find('.end-time-register').datetimepicker({
                allowInputToggle: true,
                minDate: moment().format('YYYY-MM-DD H:mm'),
                maxDate: dataStart,
                format: 'YYYY-MM-DD H:mm',
                sideBySide: true
            });
        });

        $('.end-date-ca').datetimepicker({
            allowInputToggle: true,
            minDate: moment().format('YYYY-MM-DD H:mm'),
            format: 'YYYY-MM-DD H:mm',
            sideBySide: true
        }).on('dp.hide', function () {
            if ($(this).val()) {
                $(this).parents('.row-ca').find('.start-date-ca').data('DateTimePicker').maxDate($(this).val());
            } else {
                $(this).parents('.row-ca').find('.start-date-ca').data('DateTimePicker').maxDate('2099-01-01');
            }
            validateClientConflictOff($(this));
            validateClientConflictOff($(this).parents('.row-ca').find('.start-date-ca'));
            checkRoomAvailable($(this));
        });

        var xhr = null;

        var newWindow = null;

        var run = false;

        var isAuthorizeGoogleCalendar = false;

        var selectMeetingRoomText = '{{ trans('resource::view.Select meeting room') }}';
        var urlCheckRoomAvailable = '{{ route("resource::candidate.checkRoomAvailable") }}';
        var urlGetFormCalendar = '{{ route('education::education.getFormCalendar') }}';

        $('body').on('click', '.calendar-room', function (event) {
            $(this).attr("disabled", "disabled");
            showCalendars(true);
        });
        setInterval(function() {
            if (run && newWindow != null && newWindow.closed) {
                showCalendars();
                newWindow = null;
                run = false;
            }
        }, 1000);

        var roomSelected = null;

        function checkRoomAvailable(element) {
            if (!isAuthorizeGoogleCalendar) return;
            element.parents('.row-ca').find('.date ').prop('disabled', true);
            var modalFormCalendar = element.parents('.row-ca');
            var minDate = modalFormCalendar.find('.start-date-ca').val();
            var maxDate = modalFormCalendar.find('.end-date-ca').val();
            var calendarSelect = modalFormCalendar.find('.calendar-room');
            calendarSelect.html('<option value="0">' + selectMeetingRoomText + '</option>');
            if (!minDate || !maxDate) {
                element.parents('.row-ca').find('.date ').prop('disabled', false);
            }
            calendarSelect.prop('disabled', true);
            calendarSelect.select2();
            modalFormCalendar.find('.loading-room').removeClass('hidden');
            xhr = $.ajax({
                url: urlCheckRoomAvailable,
                type: 'POST',
                data: {
                    'minDate': minDate,
                    'maxDate': maxDate,
                },
                dataType: 'html',
                success: function (res) {
                    calendarSelect.html(res);
                    calendarSelect.prop('disabled', false);
                    modalFormCalendar.find('.loading-room').addClass('hidden');
                    //if is update, selected old room
                    if (roomSelected != null) {
                        calendarSelect.find('option[value="' + roomSelected + '"]').prop('disabled', false);
                        calendarSelect.val(roomSelected);
                    }
                    calendarSelect.select2({
                        minimumResultsForSearch: -1
                    });
                    element.parents('.row-ca').find('.date').prop('disabled', false);
                },
                error: function () {
                },
            });
        }

        function closeWindow(newWindow) {
            if (newWindow !== null) {
                newWindow.close();
                newWindow = null;
            }
        }

        function showCalendars(isFirst) {

            if (isFirst) {
                newWindow = window.open('', '_blank', 'width=500,height=500');
            }

            $.ajax({
                url: urlGetFormCalendar,
                type: 'GET',
                data: {
                    calendarId: '',
                    eventId: ''
                },
                dataType: 'json',
                success: function (res) {
                    if (parseInt(res['success']) === 1) {
                        closeWindow(newWindow);
                        isAuthorizeGoogleCalendar = true;
                    } else {
                        if (isFirst) {
                            isFirst = false;
                            newWindow.location.href = res['auth_url'];
                            run = true;
                        }
                    }
                    $('.end-date-ca').trigger('blur');
                    checkRoomAvailable($('.end-date-ca'));
                }
            });
        }

        //////////////////////////////////////////////////////////////////////////

        $(function () {
            $('.select-search-employee').selectSearchEmployee();
        });
        checkRoomAvailable

        $('#team_id_add').multiselect({
            nonSelectedText: "{{ trans('education::view.Education.Non select') }}",
            allSelectedText: '{{ trans('education::view.Education.All') }}',
            numberDisplayed: 1,
            onDropdownHide: function (event) {
                var teamValue = $('#team_id_add').val();
                if (teamValue != null) {
                    var parameter = {
                        data: teamValue
                    };
                    $.ajax({
                        url: '{{ route('education::education.MaxCourseCode') }}',
                        type: 'post',
                        dataType: 'json',
                        data: parameter,
                        success: function (data) {
                            if (data.length == 0) {
                                var scale = 'B';
                            } else {
                                var scale = 'D';
                            }
                            if ($('#education_type').val() != '') {
                                setCourseCode(scale);
                            }
                        }
                    });
                } else {
                    if ($('#education_type').val() != '') {
                        var scale = 'A';
                        setCourseCode(scale);
                    }
                }
            }
        });
        $('body').on('click', '.btn-del-file', function (e) {
            e.preventDefault();
            if ($(this).closest('.attach-file-item').find('input').prop('files')[0]) {
              var indexFile = $(this).closest('.attach-file-item').find('input').prop('files')[0].lastModified;
              var classCode = $(e.target).closest('.class-child').find('.class_code').val();
              if (classCode) {
                delete globalStoreFiles[classCode][indexFile];
              }
            }
            $(this).closest('.attach-file-item').remove();
        });
        $(document).on("click", ".btn-sm", function (e) {
            e.preventDefault();
            var listInputField = $(this).closest('.upload-wrapper').find('.list-input-fields');
            listInputField.append(
                '<div class="attach-file-item form-group">' +
                '<div class="col-md-2">' +
                '<button class="btn btn-danger btn-sm btn-del-file" type="button" style="margin-bottom: 10px">' +
                '<i class="fa fa-close"></i>' +
                '</button>' +
                '</div>'+
                '<div class="col-md-10">' +
                '<input class="filebrowse" type="file" name="' + $(this).data('name') + '">' +
                '</div>' +
                '</div>'
            );
        });

        function getCurrentFileSize(input) {
            var size = 0;
            input.closest('form').find('input[type="file"]').each(function () {
                var files = $(this)[0].files;
                if (files.length > 0) {
                    for (var i = 0; i < files.length; i++) {
                        size += files[i].size;
                    }
                }
            });
            return size;
        }

        $(document).on("click", ".add-ca", function (e) {
            e.preventDefault();
            var rangeForm = $(this).parents('.class-child');
            var elCourseForm = $("#course_form");
            var maxCa = null;

            rangeForm.find($('.auto-gen')).each(function () {
                var value = parseFloat($(this).text());
                maxCa = (value > maxCa) ? value : maxCa;
            });

            var autoLength = maxCa + 1;
            var strHtml = '';
            strHtml += '<div class="row row-ca">';
            strHtml += '<div class="col-md-6">';
            strHtml += '<div class="form-group margin-top-10">';
            strHtml += '<label for="request_date" class="col-md-3 control-label">';
            strHtml += '</label>';
            strHtml += '<div class="col-md-3">';
            strHtml += '<div class="form-group">';
            strHtml += '<label>';
            strHtml += '<span>';
            strHtml += '{{ trans('education::view.Education.Ca') }}';
            strHtml += '</span><span class="auto-gen">' + ' ' + autoLength + '</span></label>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '<label for="request_date" class="col-md-3 control-label">';
            strHtml += '{{ trans('education::view.Education.Start') }}' + '<em class="error"> *</em>';
            strHtml += '</label>';
            strHtml += '<div class="col-md-3">';
            strHtml += '<div class="input-group padding-0">';
            strHtml += '<span class="input-group-btn">';
            strHtml += '<button class="btn btn-default" type="button"><i class="fa fa-calendar"></i></button>';
            strHtml += '</span>';
            strHtml += '<input type="text" autocomplete="off" class="form-control date start-date-ca" data-provide="datepicker" placeholder="YYYY-MM-DD H:mm" value="{{ (!empty($minDate) && isset($minDate)) ? $minDate : '' }}" />';
            strHtml += '</div></div></div></div>';
            strHtml += '<div class="col-md-6">';
            strHtml += '<div class="form-group margin-top-10">';
            strHtml += '<label for="request_date" class="col-md-3 control-label">';
            strHtml += '{{ trans('education::view.Education.End') }}' + '<em class="error"> *</em>';
            strHtml += '</label>';
            strHtml += '<div class="col-md-3">';
            strHtml += '<div class="input-group padding-0">';
            strHtml += '<span class="input-group-btn"><button class="btn btn-default" type="button"><i class="fa fa-calendar"></i></button></span>';
            strHtml += '<input type="text" autocomplete="off" class="form-control date end-date-ca" data-provide="datepicker" placeholder="YYYY-MM-DD H:mm" value="{{ (!empty($maxDate) && isset($maxDate)) ? $maxDate : '' }}" />';
            strHtml += '</div></div>';
            strHtml += '<div class="col-md-3 select-container">';
            strHtml += '<button  class="btn-delete rm_ca"><i class="fa fa-trash"></i></button >';
            strHtml += '</div>';
            strHtml += '</div></div>';
            strHtml += '<div class="col-md-6">';
            strHtml += '<div class="form-group margin-top-10">';
            strHtml += '<label for="request_date" class="col-md-3 control-label"></label>';
            strHtml += '<div class="col-md-3">';
            strHtml += '<div class="col-md-6"></div>';
            strHtml += '<div class="form-group">';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '<label for="request_date" class="col-md-3 control-label">';
            strHtml += '{{ trans('education::view.Education.End Time Register') }}';
            strHtml += '<em class="error"> *</em>';
            strHtml += '</label>';
            strHtml += '<div class="col-md-3">';
            strHtml += '<div class="input-group padding-0">';
            strHtml += '<span class="input-group-btn">';
            strHtml += '<button class="btn btn-default" type="button"><i class="fa fa-calendar"></i></button>';
            strHtml += '</span>';
            strHtml += '<input type="text" autocomplete="off" class="form-control end-time-register" data-provide="datepicker" placeholder="YYYY-MM-DD H:mm" value=""/>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '<div class="col-md-6 element-right">';
            strHtml += '<div class="form-group margin-top-10">';
            strHtml += '<label for="name" class="col-md-3 control-label">';
            strHtml += '{{ trans('education::view.Education.Location') }}';
            strHtml += '<em class="error"> *</em>';
            strHtml += '</label>';
            strHtml += '<div class="col-md-3 select-container">';
            strHtml += '<select class="form-control calendar-room">';
            strHtml += '<option value="0">{{ trans('resource::view.Select meeting room') }}</option>';
            strHtml += '</select>';
            strHtml += '<i class="fa fa-refresh fa-spin loading-room hidden"></i>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</div>';

            rangeForm.find($('.form-ca')).append(strHtml);

            // trigger function course form
            courseForm(elCourseForm);

            rangeForm.find('.end-time-register').datetimepicker({
                allowInputToggle: true,
                minDate: moment().format('YYYY-MM-DD'),
                maxDate: rangeForm.find('.start-date-ca').last().val(),
                format: 'YYYY-MM-DD H:mm',
                sideBySide: true
            });

            rangeForm.find('.start-date-ca').last().datetimepicker({
                allowInputToggle: true,
                minDate: moment().format('YYYY-MM-DD'),
                format: 'YYYY-MM-DD H:mm',
                sideBySide: true
            }).on('dp.hide', function () {
                if ($(this).val()) {
                    $(this).parents('.row-ca').find('.end-date-ca').data('DateTimePicker').minDate($(this).val());
                } else {
                    $(this).parents('.row-ca').find('.end-date-ca').data('DateTimePicker').minDate('2000-01-01');
                }
                validateClientConflictOff($(this));
                validateClientConflictOff($(this).parents('.row-ca').find('.end-date-ca'));
                checkRoomAvailable($(this));
                var dataStart = $(this).val();
                $(this).parents('.row-ca').find('.end-time-register').datetimepicker('destroy');
                $(this).parents('.row-ca').find('.end-time-register').val(dataStart);
                $(this).parents('.row-ca').find('.end-time-register').datetimepicker({
                    allowInputToggle: true,
                    minDate: moment().format('YYYY-MM-DD'),
                    maxDate: dataStart,
                    format: 'YYYY-MM-DD H:mm',
                    sideBySide: true
                });
            });

            rangeForm.find('.end-date-ca').last().datetimepicker({
                allowInputToggle: true,
                minDate: moment().format('YYYY-MM-DD'),
                format: 'YYYY-MM-DD H:mm',
                sideBySide: true
            }).on('dp.hide', function () {
                if ($(this).val()) {
                    $(this).parents('.row-ca').find('.start-date-ca').data('DateTimePicker').maxDate($(this).val());
                } else {
                    $(this).parents('.row-ca').find('.start-date-ca').data('DateTimePicker').maxDate('2099-01-01');
                }
                validateClientConflictOff($(this));
                validateClientConflictOff($(this).parents('.row-ca').find('.start-date-ca'));
                checkRoomAvailable($(this));
            });

            rangeForm.find('.end-date-ca').last().trigger('blur');

            checkRoomAvailable(rangeForm.find('.end-date-ca').last());
        });

        $(document).on("click", ".rm_ca", function (e) {
            e.preventDefault();
            $(this).parents('.row-ca').remove();
        });

        $(document).on("change", ".check-rent", function () {
            var rangeForm = $(this).parents('.class-child');
            if (this.checked) {
                rangeForm.find($('.teacher-input')).removeClass('hide');
                rangeForm.find($('.teacher-select')).addClass('hide');
            } else {
                rangeForm.find($('.teacher-input')).addClass('hide');
                rangeForm.find($('.teacher-select')).removeClass('hide');
            }
        });

        $(document).on("change", ".check-commitment", function () {
            var rangeForm = $(this).parents('.class-child');
            if (this.checked) {
                rangeForm.find($('.commitment-show')).removeClass('hide');
            } else {
                rangeForm.find($('.commitment-show')).addClass('hide');
            }
        });

        $(document).on("click", "#addClass", function (e) {
            e.preventDefault();

            var maxClass = null;
            $('.class-child').each(function () {
                var value = parseFloat($(this).data('classname'));
                maxClass = (value > maxClass) ? value : maxClass;
            });
            var autoLength = maxClass + 1;

            setClassCode(autoLength);

            var strHtml = '';
            strHtml += '<div id="class_' + autoLength + '" class="class-child" data-classname="' + autoLength + '">';
            strHtml += '<div class="row">';
            strHtml += '<div class="col-md-9">';
            strHtml += '<div class="form-group margin-top-10">';
            strHtml += '<label for="name" class="col-md-2 control-label">';
            strHtml += '{{ trans('education::view.Education.Class Name') }}';
            strHtml += '<em class="error"> *</em>';
            strHtml += '</label>';
            strHtml += '<div class="col-md-6">';
            strHtml += '<span>';
            strHtml += '<input id="class_title_' + autoLength + '" maxlength="100" name="class_title_' + autoLength + '" type="text" class="form-control class_title" value="" placeholder="' + '{{ trans('education::view.Education.Max 100 text') }}' + '">';
            strHtml += '</span>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '<div class="row">';
            strHtml += '<div class="col-md-9">';
            strHtml += '<div class="form-group margin-top-10">';
            strHtml += '<label for="name" class="col-md-2 control-label">';
            strHtml += '{{ trans('education::view.Education.Class Code') }}';
            strHtml += '<em class="error"> *</em>';
            strHtml += '</label>';
            strHtml += '<div class="col-md-2">';
            strHtml += '<span>';
            strHtml += '<input id="class_code_' + autoLength + '" name="class_code_' + autoLength + '" type="text" class="form-control class_code" value="" disabled>';
            strHtml += '</span>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '<div class="row">';
            strHtml += '<div class="col-md-6">';
            strHtml += '<div class="form-group margin-top-10">';
            strHtml += '<label for="request_date" class="col-md-3 control-label">';
            strHtml += '</label>';
            strHtml += '<div class="col-md-3">';
            strHtml += '<div class="form-group col-md-12">';
            strHtml += '<label>';
            strHtml += '<input type="checkbox" value="1" class="ng-valid ng-dirty ng-touched check-rent">';
            strHtml += '<span> ';
            strHtml += '{{ trans('education::view.Education.Teacher Rent') }}';
            strHtml += '</span>';
            strHtml += '</label>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '<label for="request_date" class="col-md-3 control-label"> ';
            strHtml += '{{ trans('education::view.Education.Teacher') }}';
            strHtml += '</label>';
            strHtml += '<div class="col-md-3 teacher-select">';
            strHtml += '<select class="form-control select-search-employee teacher_id_select" id="teacher_id_select_' + autoLength + '" name="teacher_id_select_' + autoLength + '" data-remote-url=" ' + '{{ URL::route('manage_time::profile.comelate.ajax-search-employee') }}' + '">';
            strHtml += '@if($teachers && count($teachers))';
            strHtml += '<option value="{{ $teachers->id }}" selected>{{ $teachers->name }}</option>';
            strHtml += '@endif';
            strHtml += '</select>';
            strHtml += '</div>';
            strHtml += '<div class="col-md-3 hide teacher-input">';
            strHtml += '<input id="teacher_id_input_' + autoLength + '" name="teacher_id_input_' + autoLength + '" type="text" class="form-control teacher_id_input" value="">';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '<div class="row">';
            strHtml += '<div class="col-md-6">';
            strHtml += '<div class="form-group margin-top-10">';
            strHtml += '<label for="request_date" class="col-md-3 control-label"></label>';
            strHtml += '<div class="col-md-3">';
            strHtml += '<div class="form-group col-md-12">';
            strHtml += '<label>';
            strHtml += '<input type="checkbox" value="1" class="ng-valid ng-dirty ng-touched check-commitment">';
            strHtml += '<span> ';
            strHtml += '{{ trans('education::view.Education.Commitment') }}';
            strHtml += '</span>';
            strHtml += '</label>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '<div class="commitment-show hide">';
            strHtml += '<label for="request_date" class="col-md-3 control-label"> ';
            strHtml += '{{ trans('education::view.Education.Start') }}';
            strHtml += '<em class="error"> *</em>';
            strHtml += '</label>';
            strHtml += '<div class="col-md-3">';
            strHtml += '<div class="input-group padding-0">';
            strHtml += '<span class="input-group-btn">';
            strHtml += '<button class="btn btn-default" type="button"><i class="fa fa-calendar"></i></button>';
            strHtml += '</span>';
            strHtml += '<input type="text" autocomplete="off" class="form-control date start-date" id="start_date_' + autoLength + '" name="start_date_' + autoLength + '" data-provide="datepicker" placeholder="YYYY-MM-DD H:mm" value="' + '{{ (!empty($education->start_date) && isset($education->start_date)) ? $education->start_date : old('start_date') }}"' + ' />';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '<div class="col-md-6">';
            strHtml += '<div class="form-group margin-top-10">';
            strHtml += '<div class="commitment-show hide">';
            strHtml += '<label for="request_date" class="col-md-3 control-label"> ';
            strHtml += '{{ trans('education::view.Education.End') }}';
            strHtml += '<em class="error"> *</em>';
            strHtml += '</label>';
            strHtml += '<div class="col-md-3">';
            strHtml += '<div class="input-group padding-0">';
            strHtml += '<span class="input-group-btn">';
            strHtml += '<button class="btn btn-default" type="button"><i class="fa fa-calendar"></i></button>';
            strHtml += '</span>';
            strHtml += '<input type="text" autocomplete="off" class="form-control date end-date" id="end_date_' + autoLength + '" name="end_date_' + autoLength + '" data-provide="datepicker" placeholder="YYYY-MM-DD H:mm" value="' + '{{ (!empty($education->end_date) && isset($education->end_date)) ? $education->end_date : old('end_date') }}"' + ' />';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '<div class="form-ca">';
            strHtml += '<div class="row row-ca">';
            strHtml += '<div class="col-md-6">';
            strHtml += '<div class="form-group margin-top-10">';
            strHtml += '<label for="request_date" class="col-md-3 control-label"></label>';
            strHtml += '<div class="col-md-3">';
            strHtml += '<div class="form-group">';
            strHtml += '<label>';
            strHtml += '<span>';
            strHtml += '{{ trans('education::view.Education.Ca') }}';
            strHtml += '</span>';
            strHtml += '<span class="auto-gen">';
            strHtml += ' 1';
            strHtml += '</span>';
            strHtml += '</label>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '<label for="request_date" class="col-md-3 control-label"> ';
            strHtml += '{{ trans('education::view.Education.Start') }}';
            strHtml += '<em class="error"> *</em>';
            strHtml += '</label>';
            strHtml += '<div class="col-md-3">';
            strHtml += '<div class="input-group padding-0">';
            strHtml += '<span class="input-group-btn">';
            strHtml += '<button class="btn btn-default" type="button"><i class="fa fa-calendar"></i></button>';
            strHtml += '</span>';
            strHtml += '<input type="text" autocomplete="off" class="form-control date start-date-ca" data-provide="datepicker" placeholder="YYYY-MM-DD H:mm" value="{{ (!empty($minDate) && isset($minDate)) ? $minDate : '' }}"' + ' />';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '<div class="col-md-6">';
            strHtml += '<div class="form-group margin-top-10">';
            strHtml += '<label for="request_date" class="col-md-3 control-label"> ';
            strHtml += '{{ trans('education::view.Education.End') }}';
            strHtml += '<em class="error"> *</em>';
            strHtml += '</label>';
            strHtml += '<div class="col-md-3">';
            strHtml += '<div class="input-group padding-0">';
            strHtml += '<span class="input-group-btn">';
            strHtml += '<button class="btn btn-default" type="button"><i class="fa fa-calendar"></i></button>';
            strHtml += '</span>';
            strHtml += '<input type="text" autocomplete="off" class="form-control date end-date-ca" data-provide="datepicker" placeholder="YYYY-MM-DD H:mm" value="{{ (!empty($maxDate) && isset($maxDate)) ? $maxDate : '' }}"' + ' />';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '<div class="col-md-3 select-container">';
            strHtml += '<button class="btn-add add-ca vocational-affect">';
            strHtml += '<i class="fa fa-plus"></i>';
            strHtml += '</button>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '<div class="col-md-6">';
            strHtml += '<div class="form-group margin-top-10">';
            strHtml += '<label for="request_date" class="col-md-3 control-label"></label>';
            strHtml += '<div class="col-md-3">';
            strHtml += '<div class="col-md-6"></div>';
            strHtml += '<div class="form-group">';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '<label for="request_date" class="col-md-3 control-label">';
            strHtml += '{{ trans('education::view.Education.End Time Register') }}';
            strHtml += '<em class="error"> *</em>';
            strHtml += '</label>';
            strHtml += '<div class="col-md-3">';
            strHtml += '<div class="input-group padding-0">';
            strHtml += '<span class="input-group-btn">';
            strHtml += '<button class="btn btn-default" type="button"><i class="fa fa-calendar"></i></button>';
            strHtml += '</span>';
            strHtml += '<input type="text" autocomplete="off" class="form-control end-time-register" data-provide="datepicker" placeholder="YYYY-MM-DD H:mm" value=""/>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '<div class="col-md-6 element-right">';
            strHtml += '<div class="form-group margin-top-10">';
            strHtml += '<label for="name" class="col-md-3 control-label">';
            strHtml += '{{ trans('education::view.Education.Location') }}';
            strHtml += '<em class="error"> *</em>';
            strHtml += '</label>';
            strHtml += '<div class="col-md-3 select-container">';
            strHtml += '<select class="form-control calendar-room">';
            strHtml += '<option value="0">{{ trans('resource::view.Select meeting room') }}</option>';
            strHtml += '</select>';
            strHtml += '<i class="fa fa-refresh fa-spin loading-room hidden"></i>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '<div class="row">';
            strHtml += '<div class="col-md-6">';
            strHtml += '<div class="form-group margin-top-10">';
            strHtml += '<label for="request_date" class="col-md-3 control-label">';
            strHtml += '{{ trans('education::view.Education.Document') }}';
            strHtml += '</label>';
            strHtml += '<div class="col-md-6">';
            strHtml += '<div class="form-group">';
            strHtml += '<div class="col-md-12">';
            strHtml += '<div class="upload-wrapper">';
            strHtml += '<div class="list-input-fields">';
            strHtml += '<div class="attach-file-item form-group">';
            strHtml += '<div class="col-md-2">';
            strHtml += '<button style="margin-bottom: 10px;" class="btn btn-danger btn-sm btn-del-file" type="button" title="{{ trans("doc::view.Delete") }}">';
            strHtml += '<i class="fa fa-close">';
            strHtml += '</i>';
            strHtml += '</button>';
            strHtml += '</div>';
            strHtml += '<div class="col-md-10">';
            strHtml += '<input class="filebrowse" type="file" name="attach_files[]">';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '<div class="col-md-12 form-group">';
            strHtml += '<button type="button" class="btn btn-primary btn-sm"  data-name="attach_files[]">';
            strHtml += '<i class="fa fa-plus">';
            strHtml += '</i>';
            strHtml += '</button>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '<div class="row">';
            strHtml += '<div class="col-md-6">';
            strHtml += '<div class="form-group margin-top-10">';
            strHtml += '<label for="request_date" class="col-md-3 control-label">';
            strHtml += '</label>';
            strHtml += '<div class="col-md-6">';
            strHtml += '<button class="btn-delete deleteClass">';
            strHtml += '<i class="fa fa-trash"></i>';
            strHtml += '</button> ';
            strHtml += '{{ trans('education::view.Education.Remove Class') }}';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '<div class="row">';
            strHtml += '<div class="col-md-9">';
            strHtml += '<div class="form-group">';
            strHtml += '<label for="name" class="col-md-2 control-label">';
            strHtml += '</label>';
            strHtml += '<div class="col-md-10">';
            strHtml += '<hr>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</div>';

            $('.form-class').append(strHtml);
            // trigger function course form
            var elCourseForm = $("#course_form");
            courseForm(elCourseForm);

            $(function () {
                $('.select-search-employee').selectSearchEmployee();
            });

            rangeForm = $('.form-class').find('.class-child').last();

            rangeForm.find('.start-date, .end-date').datetimepicker({
                allowInputToggle: true,
                minDate: moment().format('YYYY-MM-DD'),
                format: 'YYYY-MM-DD H:mm',
                sideBySide: true
            });

            rangeForm.find('.end-time-register').datetimepicker({
                allowInputToggle: true,
                minDate: moment().format('YYYY-MM-DD'),
                maxDate: rangeForm.find('.start-date-ca').last().val(),
                format: 'YYYY-MM-DD H:mm',
                sideBySide: true
            });

            rangeForm.find('.start-date-ca').last().datetimepicker({
                allowInputToggle: true,
                minDate: moment().format('YYYY-MM-DD'),
                format: 'YYYY-MM-DD H:mm',
                sideBySide: true
            }).on('dp.hide', function () {
                if ($(this).val()) {
                    $(this).parents('.row-ca').find('.end-date-ca').data('DateTimePicker').minDate($(this).val());
                } else {
                    $(this).parents('.row-ca').find('.end-date-ca').data('DateTimePicker').minDate('2000-01-01');
                }
                validateClientConflictOff($(this));
                validateClientConflictOff($(this).parents('.row-ca').find('.end-date-ca'));
                checkRoomAvailable($(this));
                var dataStart = $(this).val();
                $(this).parents('.row-ca').find('.end-time-register').datetimepicker('destroy');
                $(this).parents('.row-ca').find('.end-time-register').val(dataStart);
                $(this).parents('.row-ca').find('.end-time-register').datetimepicker({
                    allowInputToggle: true,
                    minDate: moment().format('YYYY-MM-DD'),
                    maxDate: dataStart,
                    format: 'YYYY-MM-DD H:mm',
                    sideBySide: true
                });
            });

            rangeForm.find('.end-date-ca').last().datetimepicker({
                allowInputToggle: true,
                minDate: moment().format('YYYY-MM-DD'),
                format: 'YYYY-MM-DD H:mm',
                sideBySide: true
            }).on('dp.hide', function () {
                if ($(this).val()) {
                    $(this).parents('.row-ca').find('.start-date-ca').data('DateTimePicker').maxDate($(this).val());
                } else {
                    $(this).parents('.row-ca').find('.start-date-ca').data('DateTimePicker').maxDate('2099-01-01');
                }
                validateClientConflictOff($(this));
                validateClientConflictOff($(this).parents('.row-ca').find('.start-date-ca'));
                checkRoomAvailable($(this));
            });

            rangeForm.find('.end-date-ca').last().trigger('blur');

            checkRoomAvailable(rangeForm.find('.end-date-ca').last());
        });

        var CourseID = '{{ $getMaxId }}';

        var maxCourseID = pad(CourseID, 4);

        function setCourseCode(scale) {
            var courseCode = $('#education_type').val().toUpperCase() + scale + maxCourseID;
            $('#course_code').val(courseCode);
            var parameter = {
                data: courseCode
            };
            $.ajax({
                url: '{{ route('education::education.MaxClassCode') }}',
                type: 'post',
                dataType: 'json',
                data: parameter,
                success: function (data) {
                    if ($('.class_code').length > 0) {
                        $.each($('.class_code'), function (i, v) {
                            $(this).attr('id', 'class_code_' + (i + 1) + '');
                            $(this).val(courseCode + '_' + (data + i));
                        });
                    }
                }
            });
        }

        function setClassCode(class_int) {
            var courseCode = $('#course_code').val();
            if (courseCode) {
                var parameter = {
                    data: courseCode
                };
                $.ajax({
                    url: '{{ route('education::education.MaxClassCode') }}',
                    type: 'post',
                    dataType: 'json',
                    data: parameter,
                    success: function (data) {
                        $('#class_code_' + class_int + '').val(courseCode + '_' + (data + class_int - 1));
                    }
                });
            }
        }

        function getBase64(file) {
            // var f = evt.target.files[0]; // FileList object

        }


        $(document).on("change", "#education_type", function () {
            if ($('#education_type').val() != '') {
                var teamValue = $('#team_id_add').val();
                if (teamValue != null) {
                    var parameter = {
                        data: teamValue
                    };
                    $.ajax({
                        url: '{{ route('education::education.MaxCourseCode') }}',
                        type: 'post',
                        dataType: 'json',
                        data: parameter,
                        success: function (data) {
                            if (data.length == 0) {
                                var scale = 'B';
                            } else {
                                var scale = 'D';
                            }
                            if ($('#education_type').val() != '') {
                                setCourseCode(scale);
                            }
                        }
                    });
                } else {
                    if ($('#education_type').val() != '') {
                        var scale = 'A';
                        setCourseCode(scale);
                    }
                }
            } else {
                $('#course_code, #class_code_1').val('');
            }
        });

        $(document).on("click", ".deleteClass", function (e) {
            e.preventDefault();
            var rangeForm = $(this).parents('.class-child');
            rangeForm.remove();
        });

        function pad(str, max) {
            str = str.toString();
            return str.length < max ? pad("0" + str, max) : str;
        }

        function handleFileSelect(evt) {
            var f = evt.target.files[0]; // FileList object
            if (f) {
                $(evt.target).data('file-id', f.lastModified);
                $(evt.target).data('file-name', f.name);
                $(evt.target).data('file-mimetype', f.type);
                var reader = new FileReader();
                this.evt = evt;
                // Closure to capture the file information.
                reader.onload = (function (theFile, evt) {
                    return function (e) {
                        var binaryData = e.target.result;
                        //Converting Binary Data to base 64
                        var base64String = window.btoa(binaryData);
                        var classCode = $(evt.target).closest('.class-child').find('.class_code').val();
                        if (!globalStoreFiles[classCode]) {
                            globalStoreFiles[classCode] = {};
                        }

                        globalStoreFiles[classCode][$(evt.target).data('file-id')] = {
                            'base64': base64String,
                            'name': $(evt.target).data('file-name'),
                            'type': $(evt.target).data('file-mimetype'),

                        };
                        //showing file converted to base64
                    };
                })(f, evt);
                // Read in the image file as a data URL.
                reader.readAsBinaryString(f);
            }
        }

        $(document).on('change', '.filebrowse', function (e) {
            handleFileSelect(e);
        });

        $(document).on("click", "#eventSaveAdd, #eventSent", function (e) {
            e.preventDefault();
            var elementButton = $(this);
            $('.save-refresh').removeClass('hidden');
            $(this).attr('disabled', true);
            var course_code = $('#course_code');
            var total_hours = $('#total_hours');
            var powerful_id = $('#powerful_id');
            var education_type = $('#education_type');
            var team_id = $('#team_id_add');
            var scope_total = $('#scope_total');
            var title = $('#title');
            var target = $('#target');
            var description = $('#description');
            var course_form = $('#course_form');
            var is_mail_list = $('input[name="is_mail_list"]').is(":checked");
            var teaching_id = $('input[name="teaching_id"]');
            validateClient(course_code, '{{ trans('education::view.Education.Required not empty') }}');
            validateClient(total_hours, '{{ trans('education::view.Education.Required not empty') }}');
            validateClient(powerful_id, '{{ trans('education::view.Education.Required not empty') }}');
            validateClient(education_type, '{{ trans('education::view.Education.Required not empty') }}');
            validateClient(title, '{{ trans('education::view.Education.Required not empty') }}');
            validateClient(target, '{{ trans('education::view.Education.Required not empty') }}');
            validateClient(description, '{{ trans('education::view.Education.Required not empty') }}');
            validateClient(scope_total, '{{ trans('education::view.Education.Required not empty') }}');
            validateClient(course_form, '{{ trans('education::view.Education.Required not empty') }}');
            validateClientDropdownTeam(team_id, '{{ trans('education::view.Education.Required not empty') }}');
            var class_child = $('.class-child');
            var class_child_length = class_child.length;
            if (class_child_length > 0) {
                // Show preload page before execute logic
                showLoading();

                var dataClass = [];
                $.each(class_child, function (i, v) {
                    var focusClass = $(this);
                    var class_title = $(this).find($('.class_title'));
                    var class_code = $(this).find($('.class_code'));
                    var check_rent = $(this).find($('.check-rent')).is(":checked");
                    var teacher_id_select = $(this).find($('.teacher_id_select'));
                    var teacher_id_input = $(this).find($('.teacher_id_input'));
                    var check_commitment = $(this).find($('.check-commitment')).is(":checked");
                    var start_date = $(this).find($('.start-date'));
                    var end_date = $(this).find($('.end-date'));
                    var rent_value = '';
                    var is_rent = '';
                    var is_commitment = '';
                    var commitment_value_start = '';
                    var commitment_value_end = '';
                    var startCa = [];
                    var endCa = [];
                    var location_name = [];
                    var end_time_register = [];
                    var location_id = [];
                    validateClient(class_title, '{{ trans('education::view.Education.Required not empty') }}');
                    validateClient(class_code, '{{ trans('education::view.Education.Required not empty') }}');
                    if (check_rent) {
                        is_rent = 1;
                        rent_value = teacher_id_input;
                    } else {
                        is_rent = 0;
                        rent_value = teacher_id_select;
                    }
                    if (check_commitment) {
                        validateClientCalendar(start_date, '{{ trans('education::view.Education.Required not empty') }}');
                        validateClientCalendar(end_date, '{{ trans('education::view.Education.Required not empty') }}');
                        commitment_value_start = start_date.val();
                        commitment_value_end = end_date.val();
                        is_commitment = 1;
                    } else {
                        validateRemoveError(start_date);
                        validateRemoveError(end_date);
                        is_commitment = 2;
                    }
                    var totalStartDateCa = focusClass.find($('.start-date-ca'));
                    if (totalStartDateCa.length > 0) {
                        $.each(totalStartDateCa, function (i, v) {
                            validateClientCalendar($(this), '{{ trans('education::view.Education.Required not empty') }}');
                            startCa.push($(this).val());
                        });
                    }
                    var totalEndDateCa = focusClass.find($('.end-date-ca'));
                    if (totalEndDateCa.length > 0) {
                        $.each(totalEndDateCa, function (i, v) {
                            validateClientCalendar($(this), '{{ trans('education::view.Education.Required not empty') }}');
                            endCa.push($(this).val());
                        });
                    }
                    var totalEndTimeRegister = focusClass.find($('.end-time-register'));
                    if (totalEndTimeRegister.length > 0) {
                        $.each(totalEndTimeRegister, function (i, v) {
                            validateClientCalendar($(this), '{{ trans('education::view.Education.Required not empty') }}');
                            end_time_register.push($(this).val());
                        });
                    }
                    var locationCheck = focusClass.find($('.calendar-room'));
                    if (locationCheck.length > 0) {
                        $.each(locationCheck, function (i, v) {
                            var locationName = $(this).find(":selected").text();
                            var locationId = $(this).val();
                            validateClientDropdown($(this), '{{ trans('education::view.Education.Required not empty') }}');
                            location_name.push(locationName);
                            location_id.push(locationId);
                        });
                    }
                    var totalError = focusClass.find('.education-error').length;
                    var totalErrorConflict = focusClass.find('.education-error-conflict').length;
                    if (totalError == 0 && totalErrorConflict == 0) {
                        dataClass[i] = {
                            'class_title': class_title.val().trim(),
                            'class_code': class_code.val().trim(),
                            'is_rent': is_rent,
                            'rent_value': rent_value.val(),
                            'is_commitment': is_commitment,
                            'commitment_value_start': commitment_value_start,
                            'commitment_value_end': commitment_value_end,
                            'startCa': startCa,
                            'endCa': endCa,
                            'end_time_register' : end_time_register,
                            'location_name': location_name,
                            'location_id': location_id,
                            'files': globalStoreFiles[class_code.val()]
                        }
                    }
                });

                console.log(dataClass);

                setTimeout(function () {
                    var totalError = $('.education-error').length;
                    var totalErrorConflict = $('.education-error-conflict').length;
                    if (totalError == 0 && totalErrorConflict == 0) {
                        var parameterCourse = {
                            course_code: course_code.val().trim(),
                            total_hours: total_hours.val(),
                            powerful_id: powerful_id.val(),
                            status: 1,
                            education_type: education_type.find(':selected').data('id'),
                            scope_total: scope_total.val(),
                            team_id: team_id.val(),
                            title: title.val().trim(),
                            target: target.val().trim(),
                            description: description.val().trim(),
                            dataClass: dataClass,
                            course_form: course_form.val(),
                            is_mail_list: is_mail_list ? is_mail_list : false,
                            teaching_id: (typeof teaching_id.val() != 'undefined') ? teaching_id.val() : ""
                        };

                        var form_data = new FormData();
                        for (var k in parameterCourse) {
                            if (parameterCourse.hasOwnProperty(k)) {
                                form_data.append(k, parameterCourse[k]);
                            }
                        }
                        for (var i = 0; i < dataClass.length; i++) {
                            for (k in dataClass[i]) {
                                if (dataClass[i].hasOwnProperty(k)) {
                                    form_data.append('dataClass[' + i + '].' + k, dataClass[i][k]);
                                }
                            }
                        }

                        $('#eventSaveAdd').attr('disabled', true);
                        $('#education-message').addClass('hidden');
                        $('#education-message').removeAttr('class');

                        // Check send email
                        if (elementButton.attr('id') == 'eventSent') {
                            // Hide preload page
                            hideLoading();

                            parameterCourse.send_mail = true;

                            // Find and replace text in ckeditor

                            var isMailListChecked = is_mail_list;
                            var ckeditor_content = '';
                            var pattern = '';
                            var replaced = '';
                            var titleModal = isMailListChecked ? '{{ trans('education::mail.Mail invite course') }}'
                                : '{{ trans('education::mail.Mail invite register course') }}';
                            var classModal = 'modal-default';
                            var modalMail = $('#modal-education-ckeditor');
                            var modalEmployeeList = $('#modal-education-employee-list');
                            modalMail.find('.modal-title').html("<input class='form-control class_title' value='" + titleModal + "'>");

                            // reset ckeditor mail
                            $(".ckedittor-text").show();

                            // Setup Ckeditor for vocational
                            if (course_form.val() == "{{ $vocationalFormInt }}") {
                                var isRentChecked = class_child.find($('.check-rent')).is(":checked");
                                parameterCourse.is_rent_checked = "0";

                                // check rent teacher
                                if (isRentChecked) {
                                    var teacherName = class_child.find($('.teacher_id_input')).val();
                                    parameterCourse.is_rent_checked = "1";
                                    parameterCourse.teacher_name = teacherName;
                                }
                                var titleModal = isMailListChecked ? '{{ trans('education::mail.Mail invite vocational') }}'
                                    : '{{ trans('education::mail.Mail invite register vocational') }}';
                                modalMail.find('.modal-title').html("<input class='form-control class_title' value='" + titleModal + "'>");
                                ckeditor_content = ckeditor2.getData();
                                pattern = ['CHI_TIET_KHOA_HOC'];
                                replaced = [description.val()];
                                jQuery.each( pattern, function( i, val ) {
                                    ckeditor_content = ckeditor_content.replace(val, replaced[i])
                                });
                                ckeditor2.setData(ckeditor_content);

                                // Show vocational mail editor
                                $("#cke_ck-template-mail-vocational").show();
                                $("#cke_ck-template-mail-course").hide();
                            } else {
                                // Setup Ckeditor for course
                                ckeditor_content = ckeditor.getData();
                                pattern = ['MUC_TIEU_KHOA_HOC', 'CHI_TIET_KHOA_HOC'];
                                replaced = [target.val(), description.val()];
                                jQuery.each( pattern, function( i, val ) {
                                    ckeditor_content = ckeditor_content.replace(val, replaced[i])
                                });
                                ckeditor.setData(ckeditor_content);


                                // Show course mail editor
                                $("#cke_ck-template-mail-vocational").hide();
                                $("#cke_ck-template-mail-course").show();
                            }

                            // Check is_mail_list checked. Confirm has student before show ckeditor
                            if (isMailListChecked) {
                                // Show confirm has student
                                modalEmployeeList.addClass('modal-info');
                                modalEmployeeList.find('.modal-title').html('{{ trans('education::view.Confirm') }}');
                                modalEmployeeList.find('.text-default').html('{{ trans('education::view.There are no students in this course yet') }}');
                                modalEmployeeList.modal('show');

                                // Confirm ok and show ckeditor
                                modalEmployeeList.on('hidden.bs.modal', function () {
                                    modalMail.find('.modal-title').html("<strong>Tiêu đề</strong><input class='form-control class_title' value='" + titleModal + "'>");
                                    modalMail.modal('show');
                                });
                            } else {
                                modalMail.find('.modal-title').html("<strong>Tiêu đề</strong><input class='form-control class_title' value='" + titleModal + "'>");
                                modalMail.modal('show');
                            }

                            // Check button send within modal
                            $( "#modal-education-ckeditor-ok" ).click(function() {
                                // Show preload page before execute logic
                                showLoading();

                                // get Data template mail
                                if (course_form.val() == "{{ $vocationalFormInt }}") {
                                    parameterCourse.templateMail = ckeditor2.getData();
                                } else {
                                    parameterCourse.templateMail = ckeditor.getData();
                                }
                                parameterCourse.titleTemplateMail = modalMail.find('.modal-title input').val();
                                addCourseAjax(parameterCourse);
                                modalMail.modal('toggle');
                            });

                            // Check modal close
                            modalMail.on('hidden.bs.modal', function () {
                                $('#eventSent').attr('disabled', false);
                                $('#eventSaveAdd').attr('disabled', false);
                                $('.save-refresh').addClass('hidden');
                            });
                        } else {
                            addCourseAjax(parameterCourse);
                        }
                    } else {
                        // Hide preload page
                        hideLoading();

                        var titleModal = '{{ trans('education::view.Education.Warning') }}';
                        var classModal = 'modal-danger';
                        var bodyModal = '{{ trans('education::view.Education.Required All') }}';

                        $('#modal-education-add-error').addClass(classModal);
                        $('#modal-education-add-error').find('.modal-title').html(titleModal);
                        $('#modal-education-add-error').find('.text-default').html(bodyModal);
                        $('#modal-education-add-error').modal('show');

                        $('#eventSent').attr('disabled', false);
                        $('#eventSaveAdd').attr('disabled', false);
                        $('.save-refresh').addClass('hidden');
                    }
                }, 1000);
            }
        });

        var dataRedirect = '';

        $('#modal-education-add').on('hidden.bs.modal', function () {
            window.location.href = dataRedirect;
        });

        function showLoading () {
            $('body').append("<div class=\"background-stop\">\n" +
                "    <div class=\"spinner-grow\"></div>\n" +
                "</div>");
        }

        function hideLoading () {
            $('body').find('.background-stop').remove();
        }

        function addCourseAjax (parameterCourse) {
            $.ajax({
                url: '{{ route('education::education.addCourse') }}',
                type: 'post',
                dataType: 'json',
                data: parameterCourse,
                cache: false,
                success: function (data) {
                    if (data.flag) {
                        var titleModal = '{{ trans('education::view.Education.Success') }}';
                        var classModal = 'modal-success';
                    } else {
                        var titleModal = '{{ trans('education::view.Education.Error') }}';
                        var classModal = 'modal-danger';
                        $('#education-message').removeClass('hidden');
                        $('#education-message').addClass('alert alert-warning');
                        $('.message-return').text(data.message);
                        $('.save-refresh').addClass('hidden');
                        $('#eventSaveAdd').attr('disabled', false);
                        $('#eventCoppy').attr('disabled', false);
                        $('.tab-disabled').removeClass('ui-state-disabled');
                        window.scrollTo(0, 0);
                    }
                    dataRedirect = data.url;
                    var bodyModal = data.message;
                    $('#modal-education-add').addClass(classModal);
                    $('#modal-education-add').find('.modal-title').html(titleModal);
                    $('#modal-education-add').find('.text-default').html(bodyModal);
                    $('#modal-education-add').modal('show');
                },
                complete: function() {
                    // Hide loading
                    hideLoading();
                }
            });
        }

        function validateClient($id_element, $mess_element) {
            if ($id_element.val().trim() == '' || $id_element.find(':selected').data('id') == 'undefined') {
                $id_element.next('label').remove();
                $id_element.after('<label class="error" for="title">' + $mess_element + '</label>');
                $id_element.addClass('education-error');
            } else {
                $id_element.next('label').remove();
                $id_element.removeClass('education-error');
            }
        }

        function validateClientDropdown($id_element, $mess_element) {
            if ($id_element.val() == null || $id_element.val() == 0) {
                $id_element.next('span').next('label').remove();
                $id_element.next('span').after('<label class="error" for="title">' + $mess_element + '</label>');
                $id_element.addClass('education-error');
            } else {
                $id_element.next('span').next('label').remove();
                $id_element.removeClass('education-error');
            }
        }

        function validateClientDropdownTeam($id_element, $mess_element) {
            if ($id_element.val() == null) {
                $id_element.next('.btn-group').next('label').remove();
                $id_element.next('.btn-group').after('<label class="error" for="title">' + $mess_element + '</label>');
                $id_element.addClass('education-error');
            } else {
                $id_element.next('.btn-group').next('label').remove();
                $id_element.removeClass('education-error');
            }
        }

        function validateClientCalendar($id_element, $mess_element) {
            if ($id_element.val().trim() == '') {
                $id_element.parents('.col-md-3').find('label').remove();
                $id_element.parents('.col-md-3').append('<label class="error" for="title">' + $mess_element + '</label>');
                $id_element.addClass('education-error');
            } else {
                $id_element.parents('.col-md-3').find('label').remove();
                $id_element.removeClass('education-error');
            }
        }

        function validateClientConflictOn($id_element, $mess_element) {
            $id_element.parents('.col-md-3').find('label').remove();
            $id_element.parents('.col-md-3').append('<a class="error" for="title">' + $mess_element + '</a>');
            $id_element.addClass('education-error-conflict');
        }

        function validateClientConflictOff($id_element) {
            $id_element.parents('.col-md-3').find('a').remove();
            $id_element.removeClass('education-error-conflict');
        }

        function validateRemoveError($id_element) {
            $id_element.removeClass('education-error');
        }

        $(document).on("blur", ".start-date-ca", function () {
            var totalHours = 0;
            $.each($('.start-date-ca'), function (i, v) {
                if (Date.parse($(this).val() == '')) {
                    startHours = 0;
                } else {
                    startHours = Date.parse($(this).val());
                }
                if ($(this).parents('.row-ca').find('.end-date-ca').val() == '') {
                    endHours = 0;
                } else {
                    endHours = Date.parse($(this).parents('.row-ca').find('.end-date-ca').val());
                }
                if (startHours < endHours) {
                    eachHours = ((endHours - startHours) / 1000) / 3600;
                    totalHours = totalHours + eachHours;
                }
            });
            $('#total_hours').val(totalHours);
        });

        $(document).on("blur", ".end-date-ca", function () {
            var totalHours = 0;
            $.each($('.end-date-ca'), function (i, v) {
                if (Date.parse($(this).val() == '')) {
                    endHours = 0;
                } else {
                    endHours = Date.parse($(this).val());
                }
                if ($(this).parents('.row-ca').find('.start-date-ca').val() == '') {
                    startHours = 0;
                } else {
                    startHours = Date.parse($(this).parents('.row-ca').find('.start-date-ca').val());
                }
                if (startHours < endHours) {
                    eachHours = ((endHours - startHours) / 1000) / 3600;
                    totalHours = totalHours + eachHours;
                }
            });
            $('#total_hours').val(totalHours);
        });

        $(document).on("click", "#eventClose", function (e) {
            e.preventDefault();
            window.location.href = '{{ route('education::education.list') }}';
        });

        $(document).on("change", ".calendar-room", function () {
            var dataSelect = $(this);
            var countOffset = $('option[value="' + dataSelect.val() + '"]:selected').closest('.calendar-room');
            if (countOffset.length > 1) {
                $.each(countOffset, function (i, v) {
                    if (!$(countOffset[i]).is(dataSelect)) {
                        if (dataSelect.parents('.row-ca').find('.start-date-ca').val() >= $(countOffset[i]).parents('.row-ca').find('.start-date-ca').val() &&
                            dataSelect.parents('.row-ca').find('.start-date-ca').val() <= $(countOffset[i]).parents('.row-ca').find('.end-date-ca').val() ||
                            dataSelect.parents('.row-ca').find('.end-date-ca').val() >= $(countOffset[i]).parents('.row-ca').find('.start-date-ca').val() &&
                            dataSelect.parents('.row-ca').find('.end-date-ca').val() <= $(countOffset[i]).parents('.row-ca').find('.end-date-ca').val() ||
                            dataSelect.parents('.row-ca').find('.start-date-ca').val() <= $(countOffset[i]).parents('.row-ca').find('.start-date-ca').val() &&
                            dataSelect.parents('.row-ca').find('.end-date-ca').val() >= $(countOffset[i]).parents('.row-ca').find('.end-date-ca').val()) {
                            validateClientConflictOn(dataSelect.parents('.row-ca').find('.start-date-ca'), '{{ trans('education::view.Education.Conflict Time') }}');
                            validateClientConflictOn(dataSelect.parents('.row-ca').find('.end-date-ca'), '{{ trans('education::view.Education.Conflict Time') }}');
                        } else {
                            validateClientConflictOff(dataSelect.parents('.row-ca').find('.start-date-ca'));
                            validateClientConflictOff(dataSelect.parents('.row-ca').find('.end-date-ca'));
                        }
                    }
                });
            } else {
                validateClientConflictOff(dataSelect.parents('.row-ca').find('.start-date-ca'));
                validateClientConflictOff(dataSelect.parents('.row-ca').find('.end-date-ca'));
            }
        });

        // Course form
        function courseForm(el) {
            var courseFormEl = el.val();
            var vocationalAffect = $(".vocational-affect");
            var countRowCa = $('body').find('.row-ca').length;
            var popup = $("#modal-education-form");
            // Make exists only one shift
            if (countRowCa != 1 && courseFormEl == "{{ $vocationalFormInt }}") {
                //Show line
                vocationalAffect.show();

                var titleModal = '{{ trans('education::view.Education.Warning') }}';
                var classModal = 'modal-danger';
                var bodyModal = "{{ trans('education::message.The form of vocational training applies only to 1 classroom and 1 shift') }}";

                popup.addClass(classModal);
                popup.find('.modal-title').html(titleModal);
                popup.find('.text-default').html(bodyModal);
                popup.modal('show');

                // Reset select Form to first value
                el.prop('selectedIndex', 0);

                // Check modal close
                popup.on('hidden.bs.modal', function () {
                    //Show line
                    vocationalAffect.show();
                });
            }

            // Check courseForm is vocational
            if (courseFormEl == "{{ $vocationalFormInt }}") {
                // Hide line
                vocationalAffect.hide();
            } else {
                //Show line
                vocationalAffect.show();
            }
        }
        $('#course_form').on('change', function(){
            courseForm($(this));
        });
        //End Course form
    </script>
    <script src="{{ CoreUrl::asset('education/js/team_scope.js') }}"></script>
@endsection
