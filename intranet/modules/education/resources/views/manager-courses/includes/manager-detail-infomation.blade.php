<?php
use Carbon\Carbon;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Education\Http\Controllers\EducationCourseController;
use Rikkei\Team\View\TeamList;
use Rikkei\Education\Http\Services\EducationCourseService;

$courseFormInt = EducationCourseService::FORM_COURSE;
$vocationalFormInt = EducationCourseService::FORM_VOCATIONAL;

$teamsOptionAll = TeamList::toOption(null, true, false);
$statusNew = EducationCourseService::STATUS_NEW;
$statusRegister = EducationCourseService::STATUS_REGISTER;
$statusOpen = EducationCourseService::STATUS_OPEN;
$statusPending = EducationCourseService::STATUS_PENDING;
$statusFinish = EducationCourseService::STATUS_FINISH;
?>
<div class="education-request-body margin-top-10">
    <div class="form-horizontal education-teleport col-md-12">
        <form id="frm_create_education" method="post" action="" class="has-valid " autocomplete="off">
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
                                        <option value="{{ $key }}" {{ $key == $dataCourse[0]->course_form ? 'selected' : '' }}>{{ $item }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <label for="name" class="col-md-3 control-label"></label>
                            <div class="col-md-3">
                                <label>
                                    <input type="checkbox" name="is_mail_list" {{ $dataCourse[0]->is_mail_list == '1' ? 'checked' : '' }} style="position: relative; top: 2px;">
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
                                    <input id="course_code" name="course_code" type="text" class="form-control"
                                           value="{{ $dataCourse[0]->course_code }}" disabled>
                                </span>
                            </div>
                            <label for="name" class="col-md-3 control-label">
                                {{ trans('education::view.Education.Total hours') }}
                                <em class="error">*</em>
                            </label>
                            <div class="col-md-3">
                                <span>
                                    <input id="total_hours" name="total_hours" type="text" class="form-control"
                                           value="{{ $dataCourse[0]->hours }}" disabled>
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
                                        <?php if ($dataCourse[0]->status == '5') {
                                            echo 'disabled';
                                        } ?> data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee') }}">
                                    <option value="{{ $dataCourse[0]->hr_id }}"
                                            selected>{{ $dataCourse[0]->name . ($dataCourse[0]->nickname ? ' (' . $dataCourse[0]->nickname .')' : '') }}</option>
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
                                    <select name="type" id="status_id" <?php if ($dataCourse[0]->status == '5') {
                                        echo 'disabled';
                                    } ?> class="form-control" aria-invalid="false">
                                        <option value="1" <?php if ($dataCourse[0]->status == '1') {
                                            echo 'selected';
                                        } ?>>{{ trans('education::view.Education.Create new') }}</option>
                                        <option value="2" <?php if ($dataCourse[0]->status == '2') {
                                            echo 'selected';
                                        } ?>>{{ trans('education::view.Education.Register') }}</option>
                                        <option value="3" <?php if ($dataCourse[0]->status == '3') {
                                            echo 'selected';
                                        } ?>>{{ trans('education::view.Education.Open class') }}</option>
                                        <option value="4" <?php if ($dataCourse[0]->status == '4') {
                                            echo 'selected';
                                        } ?>>{{ trans('education::view.Education.Pending') }}</option>
                                        <option value="5" <?php if ($dataCourse[0]->status == '5') {
                                            echo 'selected';
                                        } ?>>{{ trans('education::view.Education.Finish') }}</option>
                                    </select>
                                </span>
                            </div>
                            <label for="name" class="col-md-3 control-label">
                                {{ trans('education::view.Education.Education Type') }}
                                <em class="error">*</em>
                            </label>
                            <div class="col-md-3">
                                <span>
                                    <select name="type" id="education_type" class="form-control" disabled
                                            aria-invalid="false">
                                        @if($educationTypes && count($educationTypes))
                                            @foreach ($educationTypes as $type)
                                                <option value="{{ $type->code }}"
                                                        data-id="{{$type->id}}" <?php if ($type->id == $dataCourse[0]->type) {
                                                    echo 'selected';
                                                } ?>>{{ $type->name }}</option>
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
                                <select class="form-control" id="scope_total" name="scope_total" {{ $dataCourse[0]->status != $statusNew ? 'disabled' : '' }}>
                                    @foreach($scopeTotal as $key => $item)
                                        <option value="{{ $key }}" {{ $dataCourse[0]->scope_total == $key ? 'selected' : '' }}>{{ $item }}</option>
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
                                    <input id="title" maxlength="100" name="title" type="text" class="form-control"
                                           <?php if ($dataCourse[0]->status == '5') {
                                               echo 'disabled';
                                           } ?> value="{{ $dataCourse[0]->course_name }}"
                                           placeholder="{{ trans('education::view.Education.Max 100 text') }}">
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
                                        @include('education::manager-courses.includes.team-patch-pro', ['test' => 'division1'])
                                    </span>
                                </div>
                                @if($errors->has('team_id'))
                                    <label class="error">{{$errors->first('team_id')}}</label>
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
                                    <textarea rows="2" class="form-control col-md-9"
                                              <?php if ($dataCourse[0]->status == '5') {
                                                  echo 'disabled';
                                              } ?> id="target" name="target">{{ $dataCourse[0]->target }}</textarea>
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
                                    <textarea rows="6" class="form-control col-md-9"
                                              <?php if ($dataCourse[0]->status == '5') {
                                                  echo 'disabled';
                                              } ?> id="description"
                                              name="description">{{ $dataCourse[0]->description }}</textarea>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row vocational-affect">
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
                    @if($dataClass && count($dataClass) > 0)
                        @foreach($dataClass as $keyClass => $class)
                            <div id="class_{{$class->class_element}}" class="class-child"
                                 data-classname="{{$class->class_element}}">
                                <div class="row">
                                    <div class="col-md-9">
                                        <div class="form-group margin-top-10">
                                            <label for="name" class="col-md-2 control-label">
                                                {{ trans('education::view.Education.Class Name') }}
                                                <em class="error">*</em>
                                            </label>
                                            <div class="col-md-6">
                                                <span>
                                                    <input type="text" class="class-id-hidden hidden"
                                                           value="{{$class->id}}">
                                                    @if(count($collection) && $collection)
                                                        <input type="hidden" class="class-id-hidden hidden"
                                                               name="teaching_id" value="{{$collection->id}}">
                                                    @endif
                                                    <input id="class_title_{{$class->class_element}}"
                                                           <?php if ($dataCourse[0]->status == '5') {
                                                               echo 'disabled';
                                                           } ?> maxlength="100"
                                                           name="class_title_{{$class->class_element}}" type="text"
                                                           class="form-control class_title"
                                                           value="{{ $class->class_name }}"
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
                                                    <input id="class_code_{{$class->class_element}}"
                                                           name="class_code_{{$class->class_element}}" type="text"
                                                           class="form-control class_code"
                                                           value="{{ $class->class_code }}" disabled>
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
                                                               class="ng-valid ng-dirty ng-touched check-rent" <?php if ($class->related_name == 'teacher_without') {
                                                            echo 'checked';
                                                        } ?> <?php if ($dataCourse[0]->status == '5') {
                                                            echo 'disabled';
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
                                            @if($class->related_name == 'employee')
                                                <div class="col-md-3 teacher-select">
                                                    <select class="form-control select-search-employee teacher_id_select"
                                                            <?php if ($dataCourse[0]->status == '5') {
                                                                echo 'disabled';
                                                            } ?> id="teacher_id_select_{{$class->class_element}}"
                                                            name="teacher_id_select_{{$class->class_element}}"
                                                            data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee') }}">
                                                        @if(count($collection) && $collection)
                                                            @if($collection->class_id == $class->id)
                                                                <option value="{{ $collection->employee_id }}"
                                                                        selected>{{ EducationCourseController::getNameTeacher($collection->employee_id, 1) }}</option>
                                                            @endif
                                                        @else
                                                            <option value="{{ $class->related_id }}"
                                                                    selected>{{ EducationCourseController::getNameTeacher($class->related_id, 1) }}</option>
                                                        @endif
                                                    </select>
                                                </div>
                                                <div class="col-md-3 hide teacher-input">
                                                    <input id="teacher_id_input_{{$class->class_element}}"
                                                           <?php if ($dataCourse[0]->status == '5') {
                                                               echo 'disabled';
                                                           } ?> name="teacher_id_input_{{$class->class_element}}"
                                                           type="text" class="form-control teacher_id_input" value="">
                                                </div>
                                            @elseif($class->related_name == 'teacher_without')
                                                <div class="col-md-3 hide teacher-select">
                                                    <select class="form-control select-search-employee teacher_id_select"
                                                            <?php if ($dataCourse[0]->status == '5') {
                                                                echo 'disabled';
                                                            } ?> id="teacher_id_select_{{$class->class_element}}"
                                                            name="teacher_id_select_{{$class->class_element}}"
                                                            data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee') }}">
                                                        @if($teachers && count($teachers))
                                                            <option value="{{ $teachers->id }}"
                                                                    selected>{{ $teachers->name }}</option>
                                                        @endif
                                                    </select>
                                                </div>
                                                <div class="col-md-3 teacher-input">
                                                    <input id="teacher_id_input_{{$class->class_element}}"
                                                           <?php if ($dataCourse[0]->status == '5') {
                                                               echo 'disabled';
                                                           } ?> name="teacher_id_input_{{$class->class_element}}"
                                                           type="text" class="form-control teacher_id_input"
                                                           value="{{ EducationCourseController::getNameTeacher($class->related_id, 2) }}">
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
                                                               class="ng-valid ng-dirty ng-touched check-commitment" <?php if ($class->is_commitment == 1) {
                                                            echo 'checked';
                                                        } ?> <?php if ($dataCourse[0]->status == '5') {
                                                            echo 'disabled';
                                                        } ?>>
                                                        <span>
                                                            {{ trans('education::view.Education.Commitment') }}
                                                        </span>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="commitment-show <?php if ($class->is_commitment == 1) {
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
                                                               <?php if ($dataCourse[0]->status == '5') {
                                                                   echo 'disabled';
                                                               } ?> id="start_date_{{$class->class_element}}"
                                                               name="start_date_{{$class->class_element}}"
                                                               data-provide="datepicker" placeholder="YYYY-MM-DD H:mm"
                                                               value="{{ $class->start_date }}"/>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group margin-top-10">
                                            <div class="commitment-show <?php if ($class->is_commitment == 1) {
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
                                                               <?php if ($dataCourse[0]->status == '5') {
                                                                   echo 'disabled';
                                                               } ?> id="end_date_{{$class->class_element}}"
                                                               name="end_date_{{$class->class_element}}"
                                                               data-provide="datepicker" placeholder="YYYY-MM-DD H:mm"
                                                               value="{{ $class->end_date }}"/>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-ca">
                                    @if($class->data_shift && count($class->data_shift) > 0)
                                        @foreach ($class->data_shift as $shift => $valShift)
                                            <div class="row row-ca" data-shift-id="{{$valShift->id}}">
                                                <div class="col-md-6">
                                                    <div class="form-group margin-top-10">
                                                        <label for="request_date"
                                                               class="col-md-3 control-label"></label>
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
                                                                @if($valShift->calendar_id == $calendarId)
                                                                    <input type='text' autocomplete="off"
                                                                           class="form-control date start-date-ca"
                                                                           <?php if ($dataCourse[0]->status == '5') {
                                                                               echo 'disabled';
                                                                           } ?> data-provide="datepicker"
                                                                           placeholder="YYYY-MM-DD H:mm"
                                                                           value="{{ $valShift->start_date_time }}" disabled/>
                                                                @else
                                                                    <input type='hidden' autocomplete="off"
                                                                           class="form-control date start-date-ca"
                                                                           <?php if ($dataCourse[0]->status == '5') {
                                                                               echo 'disabled';
                                                                           } ?> data-provide="datepicker"
                                                                           placeholder="YYYY-MM-DD H:mm"
                                                                           value="{{ $valShift->start_date_time }}" disabled/>
                                                                    <span class="form-control bg-secondary not-allow" data-toggle="tooltip" title="{{ trans('education::view.Education.Google Calender Not Creator') }}">
                                                                        {{ Carbon::parse($valShift->start_date_time)->format('Y-m-d H:m') }}
                                                                    </span>
                                                                @endif

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
                                                                @if($valShift->calendar_id == $calendarId)
                                                                    <input type='text' autocomplete="off"
                                                                           class="form-control date end-date-ca"
                                                                           <?php if ($dataCourse[0]->status == '5') {
                                                                               echo 'disabled';
                                                                           } ?> data-provide="datepicker"
                                                                           placeholder="YYYY-MM-DD H:mm"
                                                                           value="{{ $valShift->end_date_time }}" disabled/>
                                                                @else
                                                                    <input type='hidden' autocomplete="off"
                                                                           class="form-control date end-date-ca"
                                                                           <?php if ($dataCourse[0]->status == '5') {
                                                                               echo 'disabled';
                                                                           } ?> data-provide="datepicker"
                                                                           placeholder="YYYY-MM-DD H:mm"
                                                                           value="{{ $valShift->end_date_time }}" disabled/>
                                                                    <span class="form-control bg-secondary not-allow" data-toggle="tooltip" title="{{ trans('education::view.Education.Google Calender Not Creator') }}">
                                                                        {{ Carbon::parse($valShift->end_date_time)->format('Y-m-d H:m') }}
                                                                    </span>
                                                                @endif

                                                            </div>
                                                        </div>
                                                        <div class="col-md-3 select-container">
                                                            @if($shift == 0)
                                                                <button class="btn-add add-ca vocational-affect" <?php if ($dataCourse[0]->status == '5') {
                                                                    echo 'disabled';
                                                                } ?>>
                                                                    <i class="fa fa-plus"></i>
                                                                </button>
                                                            @else
                                                                <button class="btn-delete rm_ca" <?php if ($dataCourse[0]->status == '5') {
                                                                    echo 'disabled';
                                                                } ?>>
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
                                                                       value="{{ $valShift->end_time_register }}"/>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 element-right">
                                                    <input type="text" class="event_id hidden"
                                                           value="{{ $valShift->event_id }}">
                                                    <input type="text" class="calendar_id hidden"
                                                           value="{{ $valShift->calendar_id }}">
                                                    <div class="form-group margin-top-10">
                                                        <label for="name" class="col-md-3 control-label">
                                                            {{ trans('education::view.Education.Location') }}
                                                            <em class="error">*</em>
                                                        </label>
                                                        @if($valShift->calendar_id == $calendarId)
                                                            <div class="col-md-3 select-container">
                                                                <select class="form-control calendar-room" disabled="true">
                                                                    <option value="{{-1}}">{{ $valShift->location_name }}</option>
                                                                </select>
                                                                <i class="fa fa-refresh fa-spin loading-room hidden"></i>
                                                            </div>
                                                            <div class="col-md-3 select-container">
                                                                <div class="btn-edit btn-edit-calendar">
                                                                    <i class="fa fa-edit"></i>
                                                                </div>
                                                            </div>
                                                        @else
                                                            <div class="col-md-3 select-container">
                                                                <span class="form-control bg-secondary not-allow" data-toggle="tooltip" title="{{ trans('education::view.Education.Google Calender Not Creator') }}">
                                                                            {{ $valShift->location_name }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
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
                                                        @if(isset($class->documents) && count($class->documents) > 0)
                                                            @foreach ($class->documents as $key => $valDoc)
                                                                <li style="list-style:none">
                                                                    <a data-image-id="{{$valDoc->id}}"
                                                                       <?php if ($dataCourse[0]->status != '5') { ?>
                                                                       href="http://rikkei.sd/storage/education/{{$valDoc->url}}"
                                                                       <?php } ?>
                                                                       style="margin-right: 10px">{{$valDoc->name}}</a>
                                                                    <button data-id="{{$valDoc->id}}"
                                                                            style="margin-bottom: 10px"
                                                                            class="btn btn-danger btn-sm btn-del-file"
                                                                            type="button"
                                                                            title="{{ trans('doc::view.Delete') }}"
                                                                    <?php if ($dataCourse[0]->status == '5') {
                                                                        echo 'disabled';
                                                                    } ?>>
                                                                        <i class="fa fa-close"></i>
                                                                    </button>
                                                                </li>
                                                            @endforeach
                                                        @endif
                                                    </ul>
                                                    <div class="list-input-fields">
                                                        <div class="attach-file-item form-group">
                                                            <div class="col-md-2">
                                                                <button style="margin-bottom: 10px;"
                                                                        class="btn btn-danger btn-sm btn-del-file"
                                                                        type="button"
                                                                        title="{{ trans('doc::view.Delete') }}"
                                                                <?php if ($dataCourse[0]->status == '5') {
                                                                    echo 'disabled';
                                                                } ?>>
                                                                    <i class="fa fa-close"></i>
                                                                </button>
                                                            </div>
                                                            <div class="col-md-10">
                                                                <input type="file" class="filebrowse" name="attach_files[]"
                                                                <?php if ($dataCourse[0]->status == '5') {
                                                                    echo 'disabled';
                                                                } ?>>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12 form-group">
                                                        <button type="button" class="btn btn-primary btn-sm"
                                                                data-name="attach_files[]"
                                                        <?php if ($dataCourse[0]->status == '5') {
                                                            echo 'disabled';
                                                        } ?>><i class="fa fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if($keyClass > 0)
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group margin-top-10">
                                                <label for="request_date" class="col-md-3 control-label"></label>
                                                <div class="col-md-6">
                                                    <button class="btn-delete deleteClass" <?php if ($dataCourse[0]->status == '5') {
                                                        echo 'disabled';
                                                    } ?>>
                                                        <i class="fa fa-trash"></i>
                                                    </button> {{ trans('education::view.Education.Remove Class') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
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
                        @endforeach
                    @endif
                </div>

                {{--------------------------------}}

                <div class="row vocational-affect">
                    <div class="col-md-6">
                        <div class="form-group margin-top-10">
                            <label for="request_date" class="col-md-3 control-label">
                            </label>
                            <div class="col-md-6">
                                <button class="btn-add vocational-affect" id="addClass" <?php if ($dataCourse[0]->status == '5') {
                                    echo 'disabled';
                                } ?>>
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
                        <button type="button" class="btn btn-success btn-submit-confirm "
                                id="eventSave">
                            {{ trans('education::view.Education.Save') }}
                            <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh save-refresh"></i>
                        </button>
                        <button type="button" class="btn btn-warning btn-submit-confirm"
                                id="eventCoppy">{{ trans('education::view.Education.Coppy') }}</button>
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

<div class="modal fade" id="modal-education-info" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
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

<div class="modal fade" id="modal-education-info-error" tabindex="-1" role="dialog" aria-labelledby="myModalLabelError">
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
                <textarea name="ck-template-mail-course" class="ckedittor-text hidden" id="ck-template-mail-course">{{ $templateMail->contains('name', 'invite') ? $templateMail->where('name', 'invite')->first()->description : 'No content' }}</textarea>
                <textarea name="ck-template-mail-vocational" class="ckedittor-text hidden" id="ck-template-mail-vocational">{{ $templateMail->contains('name', 'vocational') ? $templateMail->where('name', 'vocational')->first()->description : 'No content'}}</textarea>
                <textarea name="ck-template-mail-finish" class="ckedittor-text hidden" id="ck-template-mail-finish">{{ $templateMail->contains('name', 'thank') ? $templateMail->where('name', 'thank')->first()->description : 'No content' }}</textarea>
            </div>
            <div class="modal-footer">
                <button id="modal-education-ckeditor-close" type="button" class="btn btn-secondary"
                        data-dismiss="modal">{{ trans('education::view.Education.Close') }}</button>
                <button id="modal-education-ckeditor-ok" type="button"
                        class="btn btn-primary">{{ trans('education::view.Education.Send') }}</button>
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

<div id="error_append">
    <label class="error" style="display: block;"></label>
</div>
<input id="token" type="hidden" value="{{ Session::token() }}"/>
<!-- Check value if press back button then reload page -->
<input type="hidden" id="refreshed" value="no">

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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/icheck.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="{{ asset('asset_managetime/js/script.js') }}"></script>
    <script src="{{ URL::asset('lib/ckeditor/ckeditor.js') }}"></script>
    <script src="{{ URL::asset('lib/ckfinder/ckfinder.js') }}"></script>
    <script>
        // Reset button vocational-affect
        if ($("#course_form").val() == "{{ $vocationalFormInt }}") {
            $(".vocational-affect").hide();
        }

        var $filterEmail = "{{ !empty($filterEmail) ? $filterEmail : '' }}";
        var $filterName = "{{ !empty($filterName) ? $filterName : '' }}";
        var $filterNameCode = "{{ !empty($filterNameCode) ? $filterNameCode : '' }}";

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
        var ckeditor3 = CKEDITOR.replace('ck-template-mail-finish', {
            extraPlugins: 'autogrow,image2,fixed',
            removePlugins: 'justify,colorbutton,indentblock,resize,fixed,resize,autogrow',
            removeButtons: 'About',
            startupFocus: true,
        });
        CKFinder.setupCKEditor(ckeditor, '/lib/ckfinder');
        var teamPath = JSON.parse('{!! json_encode($teamPath) !!}');
        var globalStoreFiles = {};

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        $('body').on('click', '.btn-del-file', function (e) {
            e.preventDefault();
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
            format: 'YYYY-MM-DD H:mm',
            sideBySide: true
        });

        $('.end-time-register').datetimepicker({
            allowInputToggle: true,
            maxDate: $('.start-date-ca').val(),
            format: 'YYYY-MM-DD H:mm',
            sideBySide: true
        });

        ///////////

        $('.start-date-ca').datetimepicker({
            allowInputToggle: true,
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
                maxDate: dataStart,
                format: 'YYYY-MM-DD H:mm',
                sideBySide: true
            });
        });

        $('.end-date-ca').datetimepicker({
            allowInputToggle: true,
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

        var currentEditCalendar = true;

        var newWindow = null;

        var run = false;

        var isAuthorizeGoogleCalendar = false;

        var selectMeetingRoomText = '{{ trans('resource::view.Select meeting room') }}';
        var urlCheckRoomAvailable = '{{ route("resource::candidate.checkRoomAvailable") }}';
        var urlGetFormCalendar = '{{ route('education::education.getFormCalendar') }}';

        $('body').on('click', '.btn-edit-calendar', function (event) {
            var parent = $(this).closest('.element-right');
            var selectedEvent = parent.find('.event_id');
            currentEditCalendar = selectedEvent;
            showCalendars(true, selectedEvent);
            $(this).hide();
        });

        $('body').on('click', '.calendar-room', function (event) {
            var isFristAuthorizeCalendar = !isAuthorizeGoogleCalendar;
            $(this).attr("disabled", "disabled");
            showCalendars(isFristAuthorizeCalendar, $(this));
        });

        function getCalendarErrorHandle(bodyModal) {
            closeWindow(newWindow);
            var titleModal = '{{ trans('education::view.Education.Warning') }}';
            var classModal = 'modal-danger';

            $('#modal-education-info-error').addClass(classModal);
            $('#modal-education-info-error').find('.modal-title').html(titleModal);
            $('#modal-education-info-error').find('.text-default').html(bodyModal);
            $('#modal-education-info-error').modal('show');
        }

        function showCalendars(isFirst, element) {

            if (isFirst) {
                newWindow = window.open('', '_blank', 'width=500,height=500');
            }

            var event_id = element.val();

            var calendarId = element.parent().find('.calendar_id ').val();

            $.ajax({
                url: urlGetFormCalendar,
                type: 'GET',
                data: {
                    calendarId: calendarId,
                    eventId: event_id,
                },
                dataType: 'json',
                success: function (res) {
                    if (parseInt(res['success']) === 1) {
                        isAuthorizeGoogleCalendar = true;
                        if (!res['isCreator']) {
                            var bodyModal = '{{ trans('education::view.Education.Google Calender Not Creator') }}';
                            getCalendarErrorHandle(bodyModal);
                            return false;
                        }
                        element.parents('.row-ca').find('.start-date-ca').data("DateTimePicker").date(res['minDate']);
                        element.parents('.row-ca').find('.end-date-ca').data("DateTimePicker").date(res['maxDate']);
                        $('.end-date-ca').trigger('blur');
                        checkRoomAvailable(element.parents('.row-ca').find('.end-date-ca'), res['roomId']);
                        var dataStart = element.parents('.row-ca').find('.start-date-ca').val();
                        element.parents('.row-ca').find('.end-time-register').datetimepicker('destroy');
                        element.parents('.row-ca').find('.end-time-register').val(dataStart);
                        element.parents('.row-ca').find('.end-time-register').datetimepicker({
                            allowInputToggle: true,
                            maxDate: dataStart,
                            format: 'YYYY-MM-DD H:mm',
                            sideBySide: true
                        });
                        closeWindow(newWindow);
                        $('.start-date-ca, .end-date-ca').prop('disabled', false);
                    } else {
                        if (isFirst) {
                            newWindow.location.href = res['auth_url'];
                            run = true;
                        }
                    }
                },
                error: function () {
                    var bodyModal = '{{ trans('education::view.Education.Google Calender Error') }}';
                    getCalendarErrorHandle(bodyModal);
                },
            });
        }

        setInterval(function () {
            if (run && newWindow != null && newWindow.closed) {
                showCalendars(false, currentEditCalendar);
                newWindow = null;
                run = false;
            }
        }, 1000);

        function closeWindow(newWindow) {
            if (newWindow !== null) {
                newWindow.close();
                newWindow = null;
            }
        }

        function checkRoomAvailable(element, roomId = null) {
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
                    if (roomId != null) {
                        calendarSelect.find('option[value="' + roomId + '"]').prop('disabled', false);
                        calendarSelect.val(roomId);
                    }
                    calendarSelect.select2({
                        minimumResultsForSearch: -1
                    });
                    element.parents('.row-ca').find('.date ').prop('disabled', false);
                },
                error: function () {
                    var bodyModal = '{{ trans('education::view.Education.Google Calender Error') }}';
                    getCalendarErrorHandle(bodyModal);
                },
            });
        }

        //////////////////////////////////////////////////////////////////////////

        $(function () {
            $('.select-search-employee').selectSearchEmployee();
            $('.select-search-email').selectSearchEmployee();
            $('.select-search-employee_name').selectSearchEmployee();
            $('.select-search-employee_code').selectSearchEmployee();
        });

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
            strHtml += '<div class="col-md-6"></div>';
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
            strHtml += '<em class="error">*</em>';
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

        var dataShiftDelete = [];

        $(document).on("click", ".rm_ca", function (e) {
            e.preventDefault();
            var dataShiftId = $(this).parents('.row-ca').data('shift-id');
            if (typeof dataShiftId != 'undefined') {
                dataShiftDelete.push(dataShiftId);
            }
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

        var class_int = 0;

        $(document).on("click", "#addClass", function (e) {
            e.preventDefault();

            class_int = $('.class-child').length;

            setClassCode(class_int);

            class_int = class_int + 1;
        });

        var maxCourseID = $('#course_code').val().substr($('#course_code').val().length - 4);

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
                            if (data > 1) {
                                $(this).val($(this).attr('value'));
                            } else {
                                $(this).attr('id', 'class_code_' + (i + 1) + '');
                                $(this).val(courseCode + '_' + (data + i));
                            }
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

                        if (data > 1) {
                            var autoLength = data;
                        } else {
                            var autoLength = data + class_int;
                        }

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
                        strHtml += '<input type="text" class="class-id-hidden hidden" value="">';
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
                        strHtml += '<input id="class_code_' + autoLength + '" name="class_code_' + autoLength + '" type="text" class="form-control class_code" value="' + courseCode + '_' + autoLength + '" disabled>';
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
                        strHtml += '<div class="col-md-6"></div>';
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
                        strHtml += '<button  class="btn-add add-ca vocational-affect">';
                        strHtml += '<i class="fa fa-plus"></i>';
                        strHtml += ' </button>';
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
                        strHtml += '<em class="error">*</em>';
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
                        strHtml += '<div class="row vocational-affect">';
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
                    }
                });
            }
        }

        $(document).on("change", "#education_type", function () {
            if ($('#education_type').val() != '') {
                var scale = 'A';
                setCourseCode(scale);
            } else {
                $('#course_code, #class_code_1').val('');
            }
        });

        var dataClassDelete = [];

        $(document).on("click", ".deleteClass", function (e) {
            e.preventDefault();
            var rangeForm = $(this).parents('.class-child');
            var classId = $(this).parents('.class-child').find('.class-id-hidden').val();
            if (typeof classId != 'undefined') {
                dataClassDelete.push(classId);
            }
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

        $(document).on("change", "#status_id", function () {
            if ($(this).val() == 1) {
                $('#education_type').attr('disabled', true);
            }
        });

        $('#team_id_search').multiselect({
            numberDisplayed: 0,
            nonSelectedText: '{{ trans('education::view.Education.All') }}',
            allSelectedText: '{{ trans('education::view.Education.All') }}',
            onDropdownHide: function (event) {
                RKfuncion.filterGrid.filterRequest(this.$select);
            }
        });

        $('#class_search, #class_attend').multiselect({
            numberDisplayed: 0,
            nonSelectedText: '{{ trans('education::view.Education.All') }}',
            allSelectedText: '{{ trans('education::view.Education.All') }}',
            onDropdownHide: function (event) {
                RKfuncion.filterGrid.filterRequest(this.$select);
            }
        });

        $('#is_finish').multiselect({
            numberDisplayed: 0,
            nonSelectedText: '{{ trans('education::view.Education.Empty') }}',
            allSelectedText: '{{ trans('education::view.Education.All') }}',
        });

        $('#team_id').multiselect({
            nonSelectedText: "-------------------",
            allSelectedText: '{{ trans('education::view.Education.All') }}',
            numberDisplayed: 0,
            onDropdownHide: function (event) {
                var teamValue = $('#team_id').val();
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

        (function ($) {
            // Select2 title ajax
            $.fn.selectSearchTitle = function (options) {
                var defaults = {
                    url: "",
                    pages: 1,
                    delay: 300,
                    placeholder: "",
                    multiple: false,
                    allowClear: true,
                    allowHtml: true,
                    tags: false,
                    minimumInputLength: 2,
                    maximumSelectionLength: 1,
                };
                var settings = $.extend({}, defaults, options);
                var title = this;
                var idCourse = this.data('course-id');

                title.init = function (selector) {
                    $(selector).select2({
                        multiple: settings.multiple,
                        closeOnSelect: settings.closeOnSelect,
                        allowClear: settings.allowClear,
                        allowHtml: settings.allowHtml,
                        tags: settings.tags,
                        minimumInputLength: settings.minimumInputLength,
                        ajax: {
                            url: settings.url,
                            dataType: 'json',
                            delay: settings.delay,
                            data: function (params) {
                                return {
                                    q: params.term,
                                    page: params.page,
                                    id: idCourse
                                };
                            },
                            processResults: function (data, params) {
                                params.page = params.page || 1;
                                return {
                                    results: data.items,
                                    pagination: {
                                        more: (params.page * 10) < data.total_count
                                    }
                                };
                            },
                            cache: true
                        },
                        escapeMarkup: function (markup) {
                            return markup;
                        },
                        placeholder: settings.placeholder,
                        templateResult: title.formatRepo,
                        templateSelection: title.formatRepoSelection
                    });
                }

                // temple
                title.formatRepo = function (repo) {
                    if (repo.loading) {
                        return repo.text;
                    }
                    return markup = "<div class='select2-result-repository clearfix'>" +
                        "<div class='select2-result-repository__title'>" + repo.text + "</div>" +
                        "</div>" +
                        "</div>";
                }

                // temple
                title.formatRepoSelection = function (repo) {
                    return repo.text;
                }

                // Event select
                title.on("select2:select", function (e) {
                    $('.btn-search-filter').trigger('click');
                })

                // init
                var selectors = $(this);
                return $.each(selectors, function (index, selector) {
                    title.init(selector);
                });
            };

            var totalPointTeacher = 0;

            var totalPointCompany = 0;

            $('.teacher_point').each(function () {
                totalPointTeacher += Number($(this).text()) / $('.teacher_point').length;
            });

            $('.company_point').each(function () {
                totalPointCompany += Number($(this).text()) / $('.company_point').length;
            });

            $('.feedback_teacher_point').text(parseFloat(totalPointTeacher).toFixed(1));

            $('.feedback_company_point').text(parseFloat(totalPointCompany).toFixed(1));

        }(jQuery));

        var countClickAddEmp = 0;

        $(document).on("click", "#eventAddEmp", function (e) {
            e.preventDefault();

            countClickAddEmp = countClickAddEmp + 1;

            var autoStt = null;

            $('.stt').each(function () {
                var value = parseFloat($(this).text());
                autoStt = (value > autoStt) ? value : autoStt;
            });

            var maxStt = autoStt + 1;

            var maxDetailId = parseInt('{{$getMaxDetailId}}') + countClickAddEmp;

            var strHtml = '';
            strHtml += '<tr>';
            strHtml += '<td class="stt" data-detail="' + maxDetailId + '">' + maxStt + '</td>';
            strHtml += '<td><select class="form-control select-search-employee-email new_employee_email" name="new_employee_email" data-remote-url="{{ URL::route('education::education.ajaxSearchEmployeeEmail') }}"></select><label class="error error-email hidden">{{ trans('education::view.Education.Required not empty') }}</label><label class="error error-class-exist hidden">{{ trans('education::view.Education.Teacher Exist') }}</label></td>';
            strHtml += '<td class="emp-name-new"></td>';
            strHtml += '<td class="emp-code-new"></td>';
            strHtml += '<td class="team-new"> </td>';

            strHtml += '<td class="emp-class-new">';
            strHtml += '<div class="row">';
            strHtml += '<div class="col-md-12 multi-select-style select-full">';
            strHtml += '<select class="form-control class-new-option" multiple>';
            strHtml += '<option class="checked_all" value="">{{trans('education::view.Education.All')}}</option>';
            strHtml += '@foreach($dataClass as $key => $value)';
            strHtml += '@foreach($value->data_shift as $keyShift => $valueShift)';
            strHtml += '@if($value)';
            strHtml += '<option class="class_search_item" data-class-code="{{$value->class_code}}" data-shift-code="{{$valueShift->name}}" value="{{ $value->id . '-' . $valueShift->id }}" {{ CoreForm::getFilterData('search', 'class_id') == $value->id . '-' . $valueShift->id ? 'selected' : '' }}>{{ trans('education::view.Education.Class') . ' ' . $value->class_name . ' - ' . trans('education::view.Education.Ca2')  . ' ' . $valueShift->name}}</option>';
            strHtml += '@endif';
            strHtml += '@endforeach';
            strHtml += '@endforeach';
            strHtml += '</select><label class="error error-class hidden">{{ trans('education::view.Education.Required not empty') }}</label>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</td>';

            strHtml += '<td class="emp-class-attend-new">';
            strHtml += '<div class="row">';
            strHtml += '<div class="col-md-12 multi-select-style select-class-div">';
            strHtml += '<select class="form-control select-class-attend" multiple="multiple">';
            strHtml += '@foreach($dataClass as $key => $value)';
            strHtml += '@foreach($value->data_shift as $keyShift => $valueShift)';
            strHtml += '@if($value)';
            strHtml += '<option class="class_search_item" data-class-code="{{$value->class_code}}" data-shift-code="{{$valueShift->name}}" value="{{ $value->id . '-' . $valueShift->id }}" {{ CoreForm::getFilterData('search', 'class_attend') == $value->id . '-' . $valueShift->id ? 'selected' : '' }}>{{ trans('education::view.Education.Class') . ' ' . $value->class_name . ' - ' . trans('education::view.Education.Ca2')  . ' ' . $valueShift->name}}</option>';
            strHtml += '@endif';
            strHtml += '@endforeach';
            strHtml += '@endforeach';
            strHtml += '</select>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</td>';

            strHtml += '<td class="teacher_point"></td>';
            strHtml += '<td class="company_point"></td>';
            strHtml += '<td></td>';
            strHtml += '<td>';
            strHtml += '<div class="row">';
            strHtml += '<div class="col-md-12">';
            strHtml += '<button class="btn-add save-row">';
            strHtml += '<span><i class="fa fa-save"></i></span>';
            strHtml += '</button> ';
            strHtml += '<button class="btn-delete delete-confirm">';
            strHtml += '<span><i class="fa fa-trash"></i></span>';
            strHtml += '</button>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</td>';
            strHtml += '</tr>';
            $('.table-check-list').append(strHtml);

            $('.class-new-option').multiselect({
                numberDisplayed: 0,
                nonSelectedText: '{{ trans('education::view.Education.Empty') }}',
                allSelectedText: '{{ trans('education::view.Education.All') }}'
            });

            $('.select-class-attend').select2({
                tags: true,
                tokenSeparators: [',', ' ']
            });

            $(function () {
                $('.select-search-employee-email').selectSearchEmployee();
            });

        });

        $(document).on("change", ".new_employee_email", function (e) {
            e.preventDefault();
            var dataNewEmp = $(this);
            var parameter = {
                data: $(this).val()
            };
            $.ajax({
                url: '{{ route('education::education.getEmpCodeById') }}',
                type: 'post',
                dataType: 'json',
                data: parameter,
                success: function (data) {
                    var empName = data[0]['name'] + ' (' + data[0]['nickname'] + ')';
                    var dataCode = data[0]['employee_code'] + ' (' + data[0]['nickname'] + ')';
                    var teamName = data[0]['teams_name'];
                    var empId = data[0]['id'];
                    dataNewEmp.parents('tr').find('.emp-name-new').html(empName);
                    dataNewEmp.parents('tr').find('.emp-code-new').html(dataCode);
                    dataNewEmp.parents('tr').find('.team-new').attr("data-emp", empId);
                    dataNewEmp.parents('tr').find('.team-new').html(teamName);
                }
            });
        });

        var checkSaveEvent = false;

        $(document).on("click", ".save-row", function (e) {
            var dataElement = $(this);
            var dataEmailRow = dataElement.parents('tr').find('.new_employee_email').text();
            if (!dataEmailRow) {
                dataEmailRow = dataElement.parents('tr').find('.email-new').text();
            }
            var dataEmailEmp = dataElement.parents('tr').find('.team-new').data('emp');
            var dataClassRow = dataElement.parents('tr').find('.class-new-option').not('.checked_all').val();
            var dataClassName = dataElement.parents('tr').find('.class-new-option :selected').not('.checked_all');
            var dataIdAttend = dataElement.parents('tr').find('.select-class-attend').val();
            var dataClassAttend = dataElement.parents('tr').find('.select-class-attend :selected');
            if (dataClassName.length > 0) {
                var dataClassText = "";
                $.each(dataClassName, function (i, v) {
                    if ($(this).val()) {
                        dataClassText += $(this).data('class-code') + " " + "{{trans('education::view.Education.Ca2')}}" + " " + $(this).data('shift-code') + "\n";
                    }
                })
            }
            var dataClassAttendText = "";
            if (dataClassAttend.length > 0) {
                $.each(dataClassAttend, function (i, v) {
                    if ($(this).val()) {
                        dataClassAttendText += $(this).data('class-code') + " " + "{{trans('education::view.Education.Ca2')}}" + " " + $(this).data('shift-code') + "\n";
                    }
                })
            }
            if (dataEmailRow && dataClassRow) {
                var parameter = {
                    'id': dataEmailEmp,
                    'class_id': dataClassRow
                }
                $.ajax({
                    url: '{{ route('education::education.checkEmailTeacher') }}',
                    type: 'post',
                    data: parameter,
                    success: function (data) {
                        if (data.flag) {
                            dataElement.parents('tr').find('.error-class-exist').removeClass('hidden');
                        } else if (data.flag == false) {
                            dataElement.parents('tr').find('.error-class-exist').addClass('hidden');
                            dataElement.parents('tr').find('td').eq(1).replaceWith('<td class="email-new" data-emp="' + dataEmailEmp + '">' + dataEmailRow + '<label class="error error-class-exist hidden">{{ trans('education::view.Education.Teacher Exist') }}</label></td>');
                            dataElement.parents('tr').find('td').eq(5).replaceWith('<td class="class-new" data-id="' + dataClassRow + '">' + dataClassText + '</td>');
                            dataElement.parents('tr').find('td').eq(6).replaceWith('<td class="class-attend-new" data-id="' + dataIdAttend + '">' + dataClassAttendText + '</td>');
                            dataElement.replaceWith('<button class="btn-edit edit-confirm"><span><i class="fa fa-edit"></i></span></button>')
                            if (dataElement.parents('tr').find('.error-email').not('.hidden')) {
                                dataElement.parents('tr').find('.error-email').addClass('hidden');
                            }
                            if (dataElement.parents('tr').find('.error-class').not('.hidden')) {
                                dataElement.parents('tr').find('.error-class').addClass('hidden');
                            }
                            if (dataElement.parents('tr').find('.error-class-attend').not('.hidden')) {
                                dataElement.parents('tr').find('.error-class-attend').addClass('hidden');
                            }
                            if (checkSaveEvent) {
                                $('#eventSaveList').trigger('click');
                            }
                        }
                    }
                });
            } else {
                if (!dataEmailRow) {
                    dataElement.parents('tr').find('.error-email').removeClass('hidden');
                } else {
                    dataElement.parents('tr').find('.error-email').addClass('hidden');
                }
                if (!dataClassRow) {
                    dataElement.parents('tr').find('.error-class').removeClass('hidden');
                } else {
                    dataElement.parents('tr').find('.error-class').addClass('hidden');
                }
            }
        });

        $(document).on("click", ".edit-confirm", function (e) {
            var dataElement = $(this);

            var strHtml = '';

            strHtml += '<td class="emp-class-new">';
            strHtml += '<div class="row">';
            strHtml += '<div class="col-md-12 multi-select-style select-full">';
            strHtml += '<select class="form-control class-new-option" multiple>';
            strHtml += '<option class="checked_all" value="">{{trans('education::view.Education.All')}}</option>';
            strHtml += '@foreach($dataClass as $key => $value)';
            strHtml += '@foreach($value->data_shift as $keyShift => $valueShift)';
            strHtml += '@if($value)';
            strHtml += '<option class="class_search_item" data-class-code="{{$value->class_code}}" data-shift-code="{{$valueShift->name}}" value="{{ $value->id . '-' . $valueShift->id }}" {{ CoreForm::getFilterData('search', 'class_id') == $value->id . '-' . $valueShift->id ? 'selected' : '' }}>{{ trans('education::view.Education.Class') . ' ' . $value->class_name . ' - ' . trans('education::view.Education.Ca2')  . ' ' . $valueShift->name}}</option>';
            strHtml += '@endif';
            strHtml += '@endforeach';
            strHtml += '@endforeach';
            strHtml += '</select>';
            strHtml += '</select><label class="error error-class hidden">{{ trans('education::view.Education.Required not empty') }}</label>';
            strHtml += '</div>';
            strHtml += '</div>';
            strHtml += '</td>';

            dataElement.parents('tr').find('.class-new').replaceWith(strHtml);

            var strHtmlAttend = '';

            strHtmlAttend += '<td class="emp-class-attend-new">';
            strHtmlAttend += '<div class="row">';
            strHtmlAttend += '<div class="col-md-12 multi-select-style select-class-div">';
            strHtmlAttend += '<select class="form-control select-class-attend" multiple="multiple">';
            strHtmlAttend += '@foreach($dataClass as $key => $value)';
            strHtmlAttend += '@foreach($value->data_shift as $keyShift => $valueShift)';
            strHtmlAttend += '@if($value)';
            strHtmlAttend += '<option class="class_search_item" data-class-code="{{$value->class_code}}" data-shift-code="{{$valueShift->name}}" value="{{ $value->id . '-' . $valueShift->id }}" {{ CoreForm::getFilterData('search', 'class_attend') == $value->id . '-' . $valueShift->id ? 'selected' : '' }}>{{ trans('education::view.Education.Class') . ' ' . $value->class_name . ' - ' . trans('education::view.Education.Ca2')  . ' ' . $valueShift->name}}</option>';
            strHtmlAttend += '@endif';
            strHtmlAttend += '@endforeach';
            strHtmlAttend += '@endforeach';
            strHtmlAttend += '</select>';
            strHtmlAttend += '</div>';
            strHtmlAttend += '</div>';
            strHtmlAttend += '</td>';

            dataElement.parents('tr').find('.class-attend-new').replaceWith(strHtmlAttend);
            dataElement.replaceWith('<button class="btn-add save-row"><span><i class="fa fa-save"></i></span></button>')

            $('.class-new-option').multiselect({
                numberDisplayed: 0,
                nonSelectedText: '{{ trans('education::view.Education.Empty') }}',
                allSelectedText: '{{ trans('education::view.Education.All') }}'
            });

            $('.select-class-attend').select2({
                tags: true,
                tokenSeparators: [',', ' ']
            });
        });

        var dataRowDelete = '';
        var dataIdDelete = '';
        var totalIdDelete = [];

        $(document).on("click", ".delete-confirm", function (e) {
            dataRowDelete = $(this).parents('tr');
            dataIdDelete = $(this).parents('tr').find('.team-new').data('emp');
        });

        $('.modal-footer').on('click', '.btn-ok', function () {
            dataRowDelete.remove();
            if (dataIdDelete) {
                totalIdDelete.push(dataIdDelete);
            }
        });

        $(document).on("click", "#eventSaveList", function (e) {
            var dataBtn = $(this);

            dataBtn.attr('disabled', true);
            $('.save-refresh').removeClass('hidden');

            var educationCost = $('#education_cost');
            var teacherCost = $('#teacher_cost');
            var teacherFeedback = $('#teacher_feedback');
            var hrFeedback = $('#hr_feedback');
            var is_finish = $('#is_finish');

            var allData = $('.table-check-list').find('tr:not(:first)');

            if (allData.length > 0) {
                if ($('.table-check-list').find('.save-row').length == 0) {
                    var dataTransfer = [];
                    allData.each(function (index, value) {
                        var dataElement = $(this);
                        var dataRow = {
                            detail_id: dataElement.find('.stt').data('detail'),
                            employee_id: dataElement.find('.team-new').data('emp'),
                            class_id: dataElement.find('.class-new').data('id'),
                            teacher_point: dataElement.find('.teacher_point').text(),
                            company_point: dataElement.find('.company_point').text(),
                            feedback: dataElement.find('.feedback-new').text(),
                            class_attend_id: dataElement.find('.class-attend-new').data('id'),
                        }
                        dataTransfer.push(dataRow);
                    });
                    var dataCourseUpdate = {
                        id: '{{$id}}',
                        education_cost: educationCost.val(),
                        teacher_cost: teacherCost.val(),
                        teacher_feedback: teacherFeedback.val(),
                        hr_feedback: hrFeedback.val(),
                        is_finish: is_finish.val()
                    }
                    var totalError = $('.education-error').length;
                    if (totalError == 0) {
                        var parameter = {
                            dataCourseUpdate: dataCourseUpdate,
                            updateOrCreate: dataTransfer,
                            delete: totalIdDelete
                        };
                        $.ajax({
                            url: '{{ route('education::education.updateCourse') }}',
                            type: 'post',
                            dataType: 'json',
                            data: parameter,
                            success: function (data) {
                                if (data.flag) {
                                    var titleModal = '{{ trans('education::view.Education.Success') }}';
                                    var classModal = 'modal-success';
                                } else {
                                    var titleModal = '{{ trans('education::view.Education.Error') }}';
                                    var classModal = 'modal-danger';
                                }
                                var bodyModal = data.message;
                                $('#eventSaveList').attr('disabled', false);
                                $('.save-refresh').addClass('hidden');
                                $('#modal-education').addClass(classModal);
                                $('#modal-education').find('.modal-title').html(titleModal);
                                $('#modal-education').find('.text-default').html(bodyModal);
                                $('#modal-education').modal('show');
                            }
                        });
                    } else {
                        var titleModal = '{{ trans('education::view.Education.Warning') }}';
                        var classModal = 'modal-danger';
                        var bodyModal = '{{ trans('education::view.Education.Required All') }}';
                        $('#modal-education-error').addClass(classModal);
                        $('#modal-education-error').find('.modal-title').html(titleModal);
                        $('#modal-education-error').find('.text-default').html(bodyModal);
                        $('#modal-education-error').modal('show');
                        $('#eventSaveList').attr('disabled', false);
                        $('.save-refresh').addClass('hidden');
                    }
                } else {
                    checkSaveEvent = true;
                    $('.save-row').trigger('click');
                    $('#eventSaveList').attr('disabled', false);
                    $('.save-refresh').addClass('hidden');
                }
            } else {
                var dataCourseUpdate = {
                    id: '{{$id}}',
                    education_cost: educationCost.val(),
                    teacher_cost: teacherCost.val(),
                    teacher_feedback: teacherFeedback.val(),
                    hr_feedback: hrFeedback.val(),
                    is_finish: is_finish.val()
                }
                var parameter = {
                    delete: totalIdDelete,
                    dataCourseUpdate: dataCourseUpdate,
                };
                $.ajax({
                    url: '{{ route('education::education.updateCourse') }}',
                    type: 'post',
                    dataType: 'json',
                    data: parameter,
                    success: function (data) {
                        if (data.flag) {
                            var titleModal = '{{ trans('education::view.Education.Success') }}';
                            var classModal = 'modal-success';
                        } else {
                            var titleModal = '{{ trans('education::view.Education.Error') }}';
                            var classModal = 'modal-danger';
                        }
                        var bodyModal = data.message;
                        dataBtn.attr('disabled', false);
                        $('.save-refresh').addClass('hidden');
                        $('#modal-education').addClass(classModal);
                        $('#modal-education').find('.modal-title').html(titleModal);
                        $('#modal-education').find('.text-default').html(bodyModal);
                        $('#modal-education').modal('show');
                    }
                });
            }
        });

        $('#modal-education, #modal-education-info').on('hidden.bs.modal', function () {
            location.reload();
        });

        var dataImageDelete = [];
        $(document).on("click", ".btn-del-file", function (e) {
            e.preventDefault();
            var id = $(this).data("id");
            $('*[data-image-id="' + id + '"]').css({display: "none"});
            $('*[data-id="' + id + '"]').css({display: "none"});
            $(this).closest('li').remove();
            if (typeof id != 'undefined') {
                dataImageDelete.push(id);
            }
        });
        $(document).on("click", "#eventSave, #eventSent", function (e) {
            e.preventDefault();
            var elementButton = $(this);
            var statusCourse = "{{ $dataCourse[0]->status }}";
            $('.save-refresh').removeClass('hidden');
            if (elementButton.attr('id') != 'eventSent') {
                $(this).attr('disabled', true);
            }
            var course_code = $('#course_code');
            var total_hours = $('#total_hours');
            var powerful_id = $('#powerful_id');
            var status_id = $('#status_id');
            var education_type = $('#education_type');
            var team_id = $('#team_id');
            var scope_total = $('#scope_total');
            var title = $('#title');
            var target = $('#target');
            var description = $('#description');
            var course_form = $('#course_form');
            var is_mail_list = $('input[name="is_mail_list"]').is(":checked");
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
                    var class_id = $(this).find($('.class-id-hidden'));
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
                    var className = [];
                    var location_name = [];
                    var location_name_shift = [];
                    var location_id = [];
                    var end_time_register = [];
                    var event_id = [];
                    var calendar_id = [];
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
                            var calendarId = $(this).parents('.row-ca').find('.calendar_id').val();
                            if (locationId != -1 &&( calendarId == '{{$calendarId}}' || calendarId == undefined)) {
                                location_id.push(locationId);
                                var eventId = $(this).parents('.row-ca').find('.event_id').val();
                                validateClientDropdown($(this), '{{ trans('education::view.Education.Required not empty') }}');
                                location_name.push(locationName);
                                event_id.push(eventId);
                                calendar_id.push(calendarId);
                            } else {
                                location_id.push(0);
                                location_name.push(0);
                                event_id.push(0);
                                calendar_id.push(0);
                            }
                            location_name_shift.push(locationName);
                        });
                    }
                    var totalNameCa = focusClass.find($('.auto-gen'));
                    if (totalNameCa.length > 0) {
                        $.each(totalNameCa, function (i, v) {
                            className.push(($(this).text()).trim());
                        });
                    }
                    var totalError = focusClass.find('.education-error').length;
                    var totalErrorConflict = focusClass.find('.education-error-conflict').length;
                    if (totalError == 0 && totalErrorConflict == 0) {
                        dataClass[i] = {
                            'class_id': class_id.val(),
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
                            'className': className,
                            'location_name': location_name,
                            'location_name_shift': location_name_shift,
                            'location_id': location_id,
                            'event_id': event_id,
                            'calendar_id': calendar_id,
                            'files': globalStoreFiles[class_code.val()]
                        }
                    }
                });

                setTimeout(function () {
                    var totalError = $('.education-error').length;
                    var totalErrorConflict = $('.education-error-conflict').length;
                    if (totalError == 0 && totalErrorConflict == 0) {
                        var parameterCourse = {
                            id: '{{$id}}',
                            course_code: course_code.val().trim(),
                            total_hours: total_hours.val(),
                            powerful_id: powerful_id.val(),
                            status: status_id.val(),
                            education_type: education_type.find(':selected').data('id'),
                            scope_total: scope_total.val(),
                            team_id: team_id.val(),
                            title: title.val().trim(),
                            target: target.val().trim(),
                            description: description.val().trim(),
                            dataClass: dataClass,
                            course_form: course_form.val(),
                            is_mail_list: is_mail_list ? is_mail_list : false,
                            dataClassDelete: dataClassDelete,
                            dataShiftDelete: dataShiftDelete,
                            dataImageDelete: dataImageDelete
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
                        $('#eventSave').attr('disabled', true);
                        $('#education-message').addClass('hidden');
                        $('#education-message').removeAttr('class');
                        // Check event send email
                        if (elementButton.attr('id') == 'eventSent') {
                            parameterCourse.send_mail = true;
                            if(status_id.val() == "{{ $statusFinish }}") {
                                // Hide preload page
                                hideLoading();

                                var ckeditor_content = '';
                                var pattern = '';
                                var replaced = '';
                                var titleModal = '{{ trans('education::mail.Mail thank finish course') }}';
                                var classModal = 'modal-default';
                                var modalMail = $('#modal-education-ckeditor');
                                var modalEmployeeList = $('#modal-education-employee-list');
                                var isMailListChecked = is_mail_list;
                                var countCurrentStudent = $("#employee_tab").find($('.email-new')).length;

                                $(".ckedittor-text").show();

                                // Show finish mail editor
                                $("#cke_ck-template-mail-course").hide();
                                $("#cke_ck-template-mail-vocational").hide();
                                $("#cke_ck-template-mail-finish").show();
                                modalMail.find('.modal-title').html("<strong>Tiêu đề</strong><input class='form-control class_title' value='" + titleModal + "'>");
                                modalMail.modal('show');

                                // Check button send within modal
                                $("#modal-education-ckeditor-ok").click(function () {
                                    // Show preload page before execute logic
                                    showLoading();

                                    // get Data template mail
                                    ckeditor_content = ckeditor3.getData();
                                    ckeditor3.setData(ckeditor_content);
                                    parameterCourse.templateMail = ckeditor3.getData();
                                    parameterCourse.titleTemplateMail = modalMail.find('.modal-title input').val();
                                    updateCourseInfoAjax(parameterCourse);
                                    modalMail.modal('toggle');
                                });

                                // Check modal close
                                modalMail.on('hidden.bs.modal', function () {
                                    $('#eventSent').attr('disabled', false);
                                    $("#eventSave").attr('disabled', false);
                                    if (elementButton.attr('id') == 'eventSent' && statusCourse == "{{ $statusNew }}" || elementButton.attr('id') == 'eventSent' && statusCourse == "{{ $statusPending }}") {
                                        $('#eventSave').attr('disabled', false);
                                    }
                                    $('.save-refresh').addClass('hidden');
                                });
                            } else if (statusCourse == "{{ $statusNew }}") {
                                // Hide preload page
                                hideLoading();

                                var isMailListChecked = is_mail_list;
                                var countCurrentStudent = $("#employee_tab").find($('.email-new')).length;
                                var ckeditor_content = '';
                                var pattern = '';
                                var replaced = '';
                                var titleModal = isMailListChecked ? '{{ trans('education::mail.Mail invite course') }}'
                                                                   : '{{ trans('education::mail.Mail invite register course') }}';
                                var classModal = 'modal-default';
                                var modalMail = $('#modal-education-ckeditor');
                                var modalEmployeeList = $('#modal-education-employee-list');
                                // reset ckeditor mail
                                $(".ckedittor-text").show();

                                modalMail.find('.modal-title').html("<strong>Tiêu đề</strong><input class='form-control class_title' value='" + titleModal + "'>");
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
{{--                                    titleModal = '{{ trans('education::mail.Invitation to join vocational transmission') }}';--}}
                                    var titleModal = isMailListChecked ? '{{ trans('education::mail.Mail invite vocational') }}'
                                        : '{{ trans('education::mail.Mail invite register vocational') }}';
                                    modalMail.find('.modal-title').html("<strong>Tiêu đề</strong><input class='form-control class_title' value='" + titleModal + "'>");
                                    ckeditor_content = ckeditor2.getData();
                                    pattern = ['CHI_TIET_KHOA_HOC'];
                                    replaced = [description.val()];
                                    jQuery.each( pattern, function( i, val ) {
                                        ckeditor_content = ckeditor_content.replace(val, replaced[i])
                                    });
                                    ckeditor2.setData(ckeditor_content);

                                    // Show vocational mail editor
                                    $("#cke_ck-template-mail-vocational").show();
                                    $("#cke_ck-template-mail-finish").hide();
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
                                    $("#cke_ck-template-mail-finish").hide();
                                    $("#cke_ck-template-mail-course").show();
                                }

                                // Check is_mail_list checked. Confirm has student before show ckeditor
                                if (isMailListChecked && countCurrentStudent == 0) {
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
                                $("#modal-education-ckeditor-ok").click(function () {
                                    // Show preload page before execute logic
                                    showLoading();

                                    // get Data template mail
                                    if (course_form.val() == "{{ $vocationalFormInt }}") {
                                        parameterCourse.templateMail = ckeditor2.getData();
                                    } else {
                                        parameterCourse.templateMail = ckeditor.getData();
                                    }
                                    parameterCourse.titleTemplateMail = modalMail.find('.modal-title input').val();
                                    updateCourseInfoAjax(parameterCourse);
                                    modalMail.modal('toggle');
                                });

                                // Check modal close
                                modalMail.on('hidden.bs.modal', function () {
                                    $('#eventSent').attr('disabled', false);
                                    if (elementButton.attr('id') == 'eventSent' && statusCourse == "{{ $statusNew }}" || elementButton.attr('id') == 'eventSent' && statusCourse == "{{ $statusPending }}") {
                                        $('#eventSave').attr('disabled', false);
                                    }
                                    $('.save-refresh').addClass('hidden');
                                });
                            } else {

                                updateCourseInfoAjax(parameterCourse);
                            }
                        } else {
                            updateCourseInfoAjax(parameterCourse);
                        }
                    } else {
                        // Hide preload page
                        hideLoading();

                        var titleModal = '{{ trans('education::view.Education.Warning') }}';
                        var classModal = 'modal-danger';
                        var bodyModal = '{{ trans('education::view.Education.Required All') }}';

                        $('#modal-education-info-error').addClass(classModal);
                        $('#modal-education-info-error').find('.modal-title').html(titleModal);
                        $('#modal-education-info-error').find('.text-default').html(bodyModal);
                        $('#modal-education-info-error').modal('show');

                        $('#eventSave').attr('disabled', false);
                        if (elementButton.attr('id') == 'eventSent' && statusCourse != "{{ $statusNew }}" || elementButton.attr('id') == 'eventSent' && statusCourse != "{{ $statusPending }}") {
                            $('#eventSave').attr('disabled', true);
                        }
                        $('.save-refresh').addClass('hidden');
                    }
                }, 1000);
            }
        });

        $('#education_cost, #teacher_cost').keyup(function (event) {

            // skip for arrow keys
            if (event.which >= 37 && event.which <= 40) {
                event.preventDefault();
            }

            $(this).val(function (index, value) {
                value = value.replace(/,/g, '');
                return numberWithCommas(value);
            });
        });

        function showLoading() {
            $('body').append("<div class=\"background-stop\">\n" +
                "    <div class=\"spinner-grow\"></div>\n" +
                "</div>");
        }

        function hideLoading() {
            $('body').find('.background-stop').remove();
        }

        function updateCourseInfoAjax(parameterCourse) {
            $.ajax({
                url: '{{ route('education::education.updateCourseInfo') }}',
                type: 'post',
                dataType: 'json',
                data: parameterCourse,
                success: function (data) {
                    if (data.flag) {
                        var titleModal = '{{ trans('education::view.Education.Success') }}';
                        var classModal = 'modal-success';
                    } else {
                        var titleModal = '{{ trans('education::view.Education.Error') }}';
                        var classModal = 'modal-danger';
                        var statusCourse = "{{ $dataCourse[0]->status }}";
                        $('#education-message').removeClass('hidden');
                        $('#education-message').addClass('alert alert-warning');
                        $('.message-return').text(data.message);
                        $('.save-refresh').addClass('hidden');
                        if (statusCourse != "{{ $statusNew }}" || statusCourse != "{{ $statusPending }}") {
                            $('#eventSave').attr('disabled', false);
                        }
                        $('#eventCoppy').attr('disabled', false);
                        $('.tab-disabled').removeClass('ui-state-disabled');
                        $('html, body').animate({scrollTop: 0}, 'slow');
                    }
                    var bodyModal = data.message;
                    $('#modal-education-info').addClass(classModal);
                    $('#modal-education-info').find('.modal-title').html(titleModal);
                    $('#modal-education-info').find('.text-default').html(bodyModal);
                    $('#modal-education-info').modal('show');
                },
                complete: function () {
                    // Hide loading
                    hideLoading();
                }
            });
        }

        function numberWithCommas(x) {
            var parts = x.toString().split(".");
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            return parts.join(".");
        }

        $('#education_cost, #teacher_cost').trigger('keyup');
        // Export Excel
        $('#eventExportList, #eventExportResult').click(function (e) {
            e.preventDefault();
            var form = document.createElement('form');
            form.setAttribute('method', 'post');
            form.setAttribute('action', $(this).data('url'));
            var params = {
                _token: siteConfigGlobal.token,
            };

            for (var key in params) {
                var hiddenField = document.createElement('input');
                hiddenField.setAttribute('type', 'hidden');
                hiddenField.setAttribute('name', key);
                hiddenField.setAttribute('value', params[key]);
                form.appendChild(hiddenField);
            }

            document.body.appendChild(form);
            form.submit();
            form.remove();
        });

        $(document).on("click", "#eventCoppy", function (e) {
            var parameter = {
                data: '{{$id}}'
            };
            $.ajax({
                url: '{{ route('education::education.copyCourse') }}',
                type: 'post',
                dataType: 'json',
                data: parameter,
                success: function (data) {
                    if (data.flag == true) {
                        window.open(data.url);
                    }
                }
            });
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
        $('body').on('change', '.checked_all input', function (e) {
            var lengthTotal = $(e.target).closest('ul').find('li:not(".checked_all") input').length;
            if (this.checked) {
                $(e.target).closest('ul').find('li:not(".checked_all") input:not(:checked)').trigger('click').attr('checked', this.checked);
            } else {
                var lengthChecked = $(e.target).closest('ul').find('li:not(".checked_all") input:checked').length;
                if (lengthChecked === lengthTotal) {
                    $(e.target).closest('ul').find('li:not(".checked_all") input:checked').trigger('click').attr('checked', this.checked);
                }
            }
        })

        $('body').on('change', '.class_search_item input', function (e) {
            var lengthTotal = $(e.target).closest('ul').find('li:not(".checked_all") input').length;
            if (this.checked) {
                var lengthChecked = $(e.target).closest('ul').find('li:not(".checked_all") input:checked').length;
                if (lengthChecked === lengthTotal) {
                    $(e.target).closest('ul').find('li.checked_all input').trigger('click').attr('checked', this.checked);
                }
            } else {
                $(e.target).closest('ul').find('li.checked_all input:checked').trigger('click').attr('checked', this.checked);
            }
        })
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
