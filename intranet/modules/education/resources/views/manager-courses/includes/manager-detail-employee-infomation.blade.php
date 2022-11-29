<?php
use Rikkei\Team\View\Config;
use Rikkei\Core\View\View;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Education\Http\Controllers\EducationCourseController;
use Rikkei\Team\View\TeamList;

$teamsOptionAll = TeamList::toOption(null, true, false);
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
                                    <select name="type" id="status_id" disabled class="form-control"
                                            aria-invalid="false">
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
                                <select class="form-control" id="scope_total" name="scope_total" disabled>
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
                                           disabled value="{{ $dataCourse[0]->course_name }}"
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
                                        @include('education::manager-courses.includes.team-patch-pro-profile', ['test' => 'division1'])
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
                                    <textarea rows="2" class="form-control col-md-9" disabled id="target"
                                              name="target">{{ $dataCourse[0]->target }}</textarea>
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
                                    <textarea rows="6" class="form-control col-md-9" disabled id="description"
                                              name="description">{{ $dataCourse[0]->description }}</textarea>
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
                                                    <input id="class_title_{{$class->class_element}}" disabled
                                                           maxlength="100" name="class_title_{{$class->class_element}}"
                                                           type="text" class="form-control class_title"
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
                                                               class="ng-valid ng-dirty ng-touched check-rent"
                                                               <?php if ($class->related_name == 'teacher_without') {
                                                                   echo 'checked';
                                                               } ?> disabled>
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
                                                            disabled id="teacher_id_select_{{$class->class_element}}"
                                                            name="teacher_id_select_{{$class->class_element}}"
                                                            data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee') }}">
                                                        <option value="{{ $class->related_id }}"
                                                                selected>{{ EducationCourseController::getNameTeacher($class->related_id, 1) }}</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3 hide teacher-input">
                                                    <input id="teacher_id_input_{{$class->class_element}}" disabled
                                                           name="teacher_id_input_{{$class->class_element}}" type="text"
                                                           class="form-control teacher_id_input" value="">
                                                </div>
                                            @elseif($class->related_name == 'teacher_without')
                                                <div class="col-md-3 hide teacher-select">
                                                    <select class="form-control select-search-employee teacher_id_select"
                                                            disabled id="teacher_id_select_{{$class->class_element}}"
                                                            name="teacher_id_select_{{$class->class_element}}"
                                                            data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee') }}">
                                                        @if($teachers && count($teachers))
                                                            <option value="{{ $teachers->id }}"
                                                                    selected>{{ $teachers->name }}</option>
                                                        @endif
                                                    </select>
                                                </div>
                                                <div class="col-md-3 teacher-input">
                                                    <input id="teacher_id_input_{{$class->class_element}}" disabled
                                                           name="teacher_id_input_{{$class->class_element}}" type="text"
                                                           class="form-control teacher_id_input"
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
                                                               class="ng-valid ng-dirty ng-touched check-commitment"
                                                               <?php if ($class->is_commitment == 1) {
                                                                   echo 'checked';
                                                               } ?> disabled>
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
                                                               class="form-control date start-date" disabled
                                                               id="start_date_{{$class->class_element}}"
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
                                                               class="form-control date end-date" disabled
                                                               id="end_date_{{$class->class_element}}"
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
                                                                <input type='text' autocomplete="off"
                                                                       class="form-control date start-date-ca" disabled
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
                                                                       class="form-control date end-date-ca" disabled
                                                                       data-provide="datepicker"
                                                                       placeholder="YYYY-MM-DD H:mm"
                                                                       value="{{ $valShift->end_date_time }}"/>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group margin-top-10">
                                                        <label for="request_date" class="col-md-3 control-label"></label>
                                                        <div class="col-md-3">
                                                            <div class="col-md-6"></div>
                                                            <div class="form-group col-md-6">
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
                                                                       class="form-control end-time-register" disabled data-provide="datepicker"
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
                                                        <div class="col-md-3 select-container">
                                                            <select class="form-control calendar-room" disabled>
                                                                <option value="0">{{ $valShift->location_name }}</option>
                                                            </select>
                                                            <i class="fa fa-refresh fa-spin loading-room hidden"></i>
                                                        </div>
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
                                                                       href="http://rikkei.sd/storage/education/{{$valDoc->url}}"
                                                                       style="margin-right: 10px">{{$valDoc->name}}</a>
                                                                </li>
                                                            @endforeach
                                                        @endif
                                                    </ul>
                                                    @if ($class->related_id == $userId)
                                                        <div class="list-input-fields">
                                                            <div class="attach-file-item form-group">
                                                                <div class="col-md-2">
                                                                    <button style="margin-bottom: 10px;"
                                                                            class="btn btn-danger btn-sm btn-del-file"
                                                                            type="button"
                                                                            title="{{ trans('doc::view.Delete') }}">
                                                                        <i class="fa fa-close"></i>
                                                                    </button>
                                                                </div>
                                                                <div class="col-md-10">
                                                                    <input type="file" class="filebrowse"
                                                                       name="attach_files[]">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12 form-group">
                                                            <button type="button" class="btn btn-primary btn-sm"
                                                                    data-name="attach_files[]"><i class="fa fa-plus"></i>
                                                            </button>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php if ($keyClass + 1 < count($dataClass)) { ?>
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

                                <?php } ?>

                            </div>
                        @endforeach
                    @endif
                </div>

                {{--------------------------------}}

                <div class="row">
                    <div class="col-md-9">
                        <div class="form-group">
                            <label for="name" class="col-md-2 control-label">
                                {{ trans('education::view.Education.Class Review') }}
                            </label>
                            <div class="col-md-10">
                                <hr>
                            </div>
                        </div>
                    </div>
                </div>

                @if(isset($dataClassEmployee) && $dataClassEmployee)

                    @foreach($dataClassEmployee as $keyClass => $class)

                        @if(isset($class->data_shift) && $class->data_shift)

                            @foreach($class->data_shift as $keyShift => $shift)

                                @if($shift->check_register == 1)

                                    <div class="class-child-feedback <?php if ($shift->check_time_in == 1) {
                                        echo "class-feedback-select";
                                    } ?>" data-class="{{$shift->id}}" data-class-id="{{$class->class_id}}" data-role="{{ $class->role }}">

                                        <div class="row">
                                            <div class="col-md-9">
                                                <div class="form-group margin-top-10">
                                                    <label for="name" class="col-md-2 control-label">
                                                        {{ trans('education::view.Education.Class Code') }}
                                                        <em class="error">*</em>
                                                    </label>
                                                    <div class="col-md-2">
                                                    <span>
                                                        <input type="text" class="form-control class_code_review"
                                                               value="{{ $shift->class_code }}" disabled>
                                                    </span>
                                                    </div>

                                                    <label for="name" class="col-md-2 control-label">
                                                        {{ trans('education::view.Education.Ca2') }}
                                                        <em class="error">*</em>
                                                    </label>
                                                    <div class="col-md-2">
                                                    <span>
                                                        <input type="text" class="form-control class_code_review"
                                                               value="{{ $shift->name }}" disabled>
                                                    </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-9">
                                                <div class="form-group margin-top-10">
                                                    <label for="name" class="col-md-2 control-label">
                                                        {{ trans('education::view.Education.Class Role') }}
                                                        <em class="error">*</em>
                                                    </label>
                                                    <div class="col-md-2">
                                                        <span>
                                                            <?php if ($class->role == '1') { ?>
                                                                <input type="text" class="form-control class_code"
                                                                       value="{{ trans('education::view.Education.Student') }}"
                                                                       disabled>
                                                            <?php } else { ?>
                                                                <input type="text" class="form-control class_code"
                                                                       value="{{ trans('education::view.Education.Teacher') }}"
                                                                       disabled>
                                                            <?php } ?>
                                                        </span>
                                                    </div>

                                                    <label for="name" class="col-md-2 control-label">
                                                        {{ trans('education::view.Education.Feedback Teacher') }}
                                                        <em class="error">*</em>
                                                    </label>
                                                    <div class="col-md-2">
                                                        <select name="type" class="form-control feedback_teacher_point"
                                                                <?php if ($class->role == '2' || $shift->check_time_in == 0) {
                                                                    echo "disabled";
                                                                } ?> aria-invalid="false">
                                                            <option value=""></option>
                                                            <option value="1" <?php if ($shift->feedback_teacher_point == 1) {
                                                                echo "selected";
                                                            } ?>>1
                                                            </option>
                                                            <option value="2" <?php if ($shift->feedback_teacher_point == 2) {
                                                                echo "selected";
                                                            } ?>>2
                                                            </option>
                                                            <option value="3" <?php if ($shift->feedback_teacher_point == 3) {
                                                                echo "selected";
                                                            } ?>>3
                                                            </option>
                                                            <option value="4" <?php if ($shift->feedback_teacher_point == 4) {
                                                                echo "selected";
                                                            } ?>>4
                                                            </option>
                                                            <option value="5" <?php if ($shift->feedback_teacher_point == 5) {
                                                                echo "selected";
                                                            } ?>>5
                                                            </option>
                                                        </select>
                                                    </div>

                                                    <label for="name" class="col-md-2 control-label">
                                                        {{ trans('education::view.Education.Feedback Education') }}
                                                        <em class="error">*</em>
                                                    </label>
                                                    <div class="col-md-2">
                                                        <select name="type" class="form-control feedback_company_point"
                                                                <?php if ($class->role == '2' || $shift->check_time_in == 0) {
                                                                    echo "disabled";
                                                                } ?> aria-invalid="false">
                                                            <option value=""></option>
                                                            <option value="1" <?php if ($shift->feedback_company_point == 1) {
                                                                echo "selected";
                                                            } ?>>1
                                                            </option>
                                                            <option value="2" <?php if ($shift->feedback_company_point == 2) {
                                                                echo "selected";
                                                            } ?>>2
                                                            </option>
                                                            <option value="3" <?php if ($shift->feedback_company_point == 3) {
                                                                echo "selected";
                                                            } ?>>3
                                                            </option>
                                                            <option value="4" <?php if ($shift->feedback_company_point == 4) {
                                                                echo "selected";
                                                            } ?>>4
                                                            </option>
                                                            <option value="5" <?php if ($shift->feedback_company_point == 5) {
                                                                echo "selected";
                                                            } ?>>5
                                                            </option>
                                                        </select>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-9">
                                                <div class="form-group margin-top-10">
                                                    <label for="name" class="col-md-2 control-label">
                                                        {{ trans('education::view.Education.Feedback Class') }}
                                                        <em class="error">*</em>
                                                    </label>
                                                    <div class="col-md-10">
                                                    <span>
                                                        <textarea rows="3"
                                                                  <?php if ($shift->check_time_in == 0 || $shift->check_time_in == 1 && $dataCourse[0]->status != '5') {
                                                                      echo "disabled";
                                                                  } ?> class="form-control col-md-9 feedback-class">{{$shift->feedback}}</textarea>
                                                    </span>
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

                                @endif

                            @endforeach

                        @endif

                    @endforeach

                @endif

                {{--------------------------------}}

                <div class="row">
                    <div class="col-md-10 align-center margin-top-40">
                        <button type="button" class="btn btn-danger btn-submit-confirm"
                                id="eventRegister" <?php if ($dataCourse[0]->status != '2') {
                            echo 'disabled';
                        } ?>>
                            {{ trans('education::view.Education.Register') }}
                            <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh save-refresh"></i>
                        </button>
                        @if($checkClassHasTeacher)
                            <button type="button" class="btn btn-success btn-submit-confirm" id="eventSend">
                                {{ trans('education::view.Education.Save') }}
                                <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh save-refresh"></i>
                            </button>
                        @else
                            <button type="button" class="btn btn-success btn-submit-confirm"
                                    id="eventSend" <?php if ($dataCourse[0]->status == '1' || $dataCourse[0]->status == '2' || $dataCourse[0]->status == '3' || $dataCourse[0]->status == '4' || $dataCourse[0]->status == '5' && $shift->check_time_in == 0) {
                                echo 'disabled';
                            } ?>>
                                {{ trans('education::view.Education.Save') }}
                                <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh save-refresh"></i>
                            </button>
                        @endif
                        <button type="button" class="btn btn-warning btn-submit-confirm"
                                id="eventCancel" <?php if ($dataCourse[0]->status == '3' || $dataCourse[0]->status == '4' || $dataCourse[0]->status == '5') {
                            echo 'disabled';
                        } ?>>
                            {{ trans('education::view.Education.Cancel') }}
                            <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh save-refresh"></i>
                        </button>
                        <button type="button" class="btn btn-primary btn-submit-confirm"
                                id="eventClose">{{ trans('education::view.Education.Close') }}</button>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modal-education-register" tabindex="-1" role="dialog"
     aria-labelledby="myModalLabelRegister">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-center btn-color-setting">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ trans('education::view.Education.Class Register Title') }}</h4>
            </div>
            <div class="modal-body">
                @if(isset($dataClassEmployeeClass) && $dataClassEmployeeClass)
                    @foreach($dataClassEmployeeClass as $keyClass => $class)
                        <b class="modal-class-name">{{ trans('education::view.Education.Class') . " : ". $class->class_name }}</b>
                        <br>
                        @if(isset($class->data_shift) && $class->data_shift)
                            @foreach($class->data_shift as $keyShift => $shift)
                                @if($shift->check_end_time_register == 0)
                                    <input type="checkbox" value="{{$shift->id}}"
                                           <?php if ($class->role == 2 && $class->employee_id == $userId) {
                                               echo "disabled";
                                           } ?> <?php if ($shift->check_register == 1) {
                                               echo "checked";
                                           } ?> class="ng-valid ng-dirty ng-touched input-register">
                                    <span> {{ trans('education::view.Education.Ca2') . $shift->name . " : " . $shift->start_date_time . " - " . $shift->end_date_time }} </span>
                                    <br>
                                @endif
                            @endforeach
                        @endif
                        @if($keyClass + 1 < count($dataClassEmployeeClass))
                            <hr>
                        @else
                            <br>
                        @endif
                    @endforeach
                @endif
            </div>
            <div class="modal-footer text-center">
                <button type="button" class="btn btn-color-setting" id="modalRegister">
                    {{ trans('education::view.Education.Register') }}
                    <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh save-refresh"></i>
                </button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>

<div class="modal fade" id="modal-education-show" tabindex="-1" role="dialog" aria-labelledby="myModalLabelShow">
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

<div id="error_append">
    <label class="error" style="display: block;"></label>
</div>
<input id="token" type="hidden" value="{{ Session::token() }}"/>
<!-- Check value if press back button then reload page -->
<input type="hidden" id="refreshed" value="no">
@include('education::manager-courses.includes.modal-education-response-message')
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
    <script>
        var teamPath = JSON.parse('{!! json_encode($teamPath) !!}');
        var globalStoreFiles = {};

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
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

        ///////////

        var xhr = null;

        var isFirst = true;

        var newWindow = null;

        var run = false;

        var selectMeetingRoomText = '{{ trans('resource::view.Select meeting room') }}';
        var urlCheckRoomAvailable = '{{ route("resource::candidate.checkRoomAvailable") }}';
        var urlGetFormCalendar = '{{ route('education::education.getFormCalendar') }}';


        function showCalendars(isFirst, element) {

            if (isFirst) {
                newWindow = window.open('', '_blank', 'width=500,height=500');
            }

            var event_id = element.val();

            var calendarId = '{{$calendarId}}';

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
                        element.parents('.row-ca').find('.start-date-ca').data("DateTimePicker").date(res['minDate']);
                        element.parents('.row-ca').find('.end-date-ca').data("DateTimePicker").date(res['maxDate']);
                        $('.end-date-ca').trigger('blur');
                        checkRoomAvailable(element.parents('.row-ca').find('.end-date-ca'), res['roomId']);
                        closeWindow(newWindow);
                    } else {
                        if (isFirst) {
                            newWindow.location.href = res['auth_url'];
                            run = true;
                        }
                    }
                },
                error: function () {
                    closeWindow(newWindow);
                    var titleModal = '{{ trans('education::view.Education.Warning') }}';
                    var classModal = 'modal-danger';
                    var bodyModal = '{{ trans('education::view.Education.Google Calender Error') }}';

                    $('#modal-education-info-error').addClass(classModal);
                    $('#modal-education-info-error').find('.modal-title').html(titleModal);
                    $('#modal-education-info-error').find('.text-default').html(bodyModal);
                    $('#modal-education-info-error').modal('show');
                },
            });
        }

        setInterval(function () {
            if (run && newWindow != null && newWindow.closed) {
                $.each($('.event_id'), function (key, val) {
                    showCalendars(false, $(this));
                });
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
                    modalFormCalendar.find('.loading-room').addClass('hidden');

                    //if is update, selected old room
                    if (roomId != null) {
                        calendarSelect.val(roomId);
                    }
                    calendarSelect.select2({
                        minimumResultsForSearch: -1
                    });
                    $('.end-date-ca').trigger('blur');
                },
                error: function () {
                },
            });
        }

        //////////////////////////////////////////////////////////////////////////

        $(function () {
            $('.select-search-employee').selectSearchEmployee();
        });

        $('#team_id').multiselect({
            nonSelectedText: "-------------------",
            allSelectedText: '{{ trans('education::view.Education.All') }}',
            numberDisplayed: 1,
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
                    console.log(settings.url);
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
                    console.log(repo);
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

        }(jQuery));

        $('#modal-education, #modal-education-info').on('hidden.bs.modal', function () {
            location.reload();
        });

        $(document).on("click", "#eventRegister", function (e) {
            $('#modal-education-register').modal('show');
        });

        $(document).on("click", "#modalRegister", function (e) {
            $('#modalRegister').attr('disabled', true);
            $('#modalRegister .save-refresh').removeClass('hidden');
            var dataRegister = [];
            if ($('.input-register:checked').length) {
                $.each($('.input-register:checked'), function (i, v) {
                    dataRegister.push($(this).val());
                })
            }
            var parameter = {
                'shift_id': dataRegister,
                'course_id': '{{$id}}'
            }
            if ($('.input-register:checked').length > 0) {
                $.ajax({
                    url: '{{ route('education::education-profile.registerShift') }}',
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
                        $('#modalRegister').attr('disabled', false);
                        $('#modalRegister .save-refresh').addClass('hidden');
                        $('#modal-education-info').addClass(classModal);
                        $('#modal-education-info').find('.modal-title').html(titleModal);
                        $('#modal-education-info').find('.text-default').html(bodyModal);
                        $('#modal-education-info').modal('show');
                    }
                });
            } else {
                var titleModal = '{{ trans('education::view.Education.Warning') }}';
                var classModal = 'modal-warning';
                var bodyModal = '{{ trans('education::view.Education.Class Empty') }}';
                $('#modalRegister').attr('disabled', false);
                $('#modalRegister .save-refresh').addClass('hidden');
                $('#modal-education-show').addClass(classModal);
                $('#modal-education-show').find('.modal-title').html(titleModal);
                $('#modal-education-show').find('.text-default').html(bodyModal);
                $('#modal-education-show').modal('show');
            }
        });

        function AddDocumentFromTeacher() {
            var dataClass = [];
            $.each($('.list-input-fields'), function (i, v) {
                var class_code = $(this).parents('.class-child').find('.class_code');
                if (globalStoreFiles[class_code.val()]) {
                    dataClass.push({
                        'class_id': $(this).parents('.class-child').find('.class-id-hidden').val(),
                        'files': globalStoreFiles[class_code.val()]
                    });
                }
            });
            var parameter = {
                'class_data': dataClass,
                'course_id': '{{ $id }}',
            }
            if (dataClass.length > 0) {
                $.ajax({
                    url: '{{ route('education::education-profile.addDocumentFromTeacher') }}',
                    type: 'post',
                    dataType: 'json',
                    data: parameter,
                    cache: false,
                    success: function (data) {
                        if (data.flag) {
                            var titleModal = '{{ trans('education::view.Education.Success') }}';
                            var classModal = 'modal-success';
                        } else {
                            var titleModal = '{{ trans('education::view.Education.Error') }}';
                            var classModal = 'modal-danger';
                        }
                        var bodyModal = data.message;
                        $('#eventSave').attr('disabled', false);
                        $('#eventSave .save-refresh').addClass('hidden');
                        $('#modal-education-info').addClass(classModal);
                        $('#modal-education-info').find('.modal-title').html(titleModal);
                        $('#modal-education-info').find('.text-default').html(bodyModal);
                        $('#modal-education-info').modal('show');
                    },
                    complete: function() {
                        // Hide loading
                        hideLoading();
                    }
                });
            } else {
                // Hide loading
                hideLoading();

                var titleModal = '{{ trans('education::view.Education.Warning') }}';
                var classModal = 'modal-warning';
                var bodyModal = '{{ trans('education::view.Education.Document Empty') }}';
                $('#modalRegister .save-refresh').addClass('hidden');
                $('#modal-education-show').addClass(classModal);
                $('#modal-education-show').find('.modal-title').html(titleModal);
                $('#modal-education-show').find('.text-default').html(bodyModal);
                $('#modal-education-show').modal('show');
            }
        }

        $(document).on("click", "#eventSend", function (e) {
            // Show preload page before execute logic
            showLoading();

            var countDataFeedback = $('.class-feedback-select');
            if (countDataFeedback.length > 0) {
                $('#eventSend').attr('disabled', true);
                $('#eventSend .save-refresh').removeClass('hidden');
                var parameter = {};
                $.each(countDataFeedback, function (i, v) {
                    parameter[i] = {
                        'shift_id': $(this).data('class'),
                        'feedback_teacher_point': $(this).find('.feedback_teacher_point').val(),
                        'feedback_company_point': $(this).find('.feedback_company_point').val(),
                        'feedback': $(this).find('.feedback-class').val(),
                        'class_id': $(this).data('class-id'),
                        'class_role': $(this).data('role'),
                        'course_id': '{{ $id }}',
                    }
                });
                $.ajax({
                    url: '{{ route('education::education-profile.sendFeedback') }}',
                    type: 'post',
                    dataType: 'json',
                    data: parameter,
                    success: function (data) {
                        if (data.flag) {
                            if ($('.list-input-fields').length > 0) {
                                AddDocumentFromTeacher();
                            } else {
                                // Hide loading
                                hideLoading();

                                var titleModal = '{{ trans('education::view.Education.Success') }}';
                                var classModal = 'modal-success';
                                var bodyModal = data.message;
                                $('#eventSend').attr('disabled', false);
                                $('#eventSend .save-refresh').addClass('hidden');
                                $('#modal-education-info').addClass(classModal);
                                $('#modal-education-info').find('.modal-title').html(titleModal);
                                $('#modal-education-info').find('.text-default').html(bodyModal);
                                $('#modal-education-info').modal('show');
                            }
                        } else {
                            // Hide loading
                            hideLoading();

                            var titleModal = '{{ trans('education::view.Education.Error') }}';
                            var classModal = 'modal-danger';
                            var bodyModal = data.message;
                            $('#eventSend').attr('disabled', false);
                            $('#eventSend .save-refresh').addClass('hidden');
                            $('#modal-education-info').addClass(classModal);
                            $('#modal-education-info').find('.modal-title').html(titleModal);
                            $('#modal-education-info').find('.text-default').html(bodyModal);
                            $('#modal-education-info').modal('show');
                        }
                    },
                    complete: function() {
                        // Hide loading
                        hideLoading();
                    }
                });
            } else {
                if ($('.list-input-fields').length > 0) {
                    AddDocumentFromTeacher();
                } else {
                    // Hide loading
                    hideLoading();

                    var titleModal = '{{ trans('education::view.Education.Warning') }}';
                    var classModal = 'modal-warning';
                    var bodyModal = '{{ trans('education::view.Education.Feedback Empty') }}';
                    $('#modalRegister .save-refresh').addClass('hidden');
                    $('#modal-education-show').addClass(classModal);
                    $('#modal-education-show').find('.modal-title').html(titleModal);
                    $('#modal-education-show').find('.text-default').html(bodyModal);
                    $('#modal-education-show').modal('show');
                }
            }
        });

        $(document).on("click", "#eventCancel", function (e) {
            $('#eventCancel').attr('disabled', true);
            $('#eventCancel .save-refresh').removeClass('hidden');

            var parameter = {
                'shift_id': '',
                'course_id': '{{$id}}'
            }
            $.ajax({
                url: '{{ route('education::education-profile.registerShift') }}',
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
                    var bodyModal = '{{ trans('education::view.Education.Cancel') . ' ' . trans('education::view.Education.Success') }}';
                    $('#eventCancel').attr('disabled', false);
                    $('#eventCancel .save-refresh').addClass('hidden');
                    $('#modal-education-info').addClass(classModal);
                    $('#modal-education-info').find('.modal-title').html(titleModal);
                    $('#modal-education-info').find('.text-default').html(bodyModal);
                    $('#modal-education-info').modal('show');
                }
            });
        });

        $(document).on("click", "#eventClose", function (e) {
            e.preventDefault();
            window.location.href = '{{ route('education::profile.profileList') }}';
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

        $('body').on('click', '.btn-del-file', function (e) {
            e.preventDefault();
            $(this).closest('.attach-file-item').remove();
        });

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

        function showLoading () {
            $('body').append("<div class=\"background-stop\">\n" +
                "    <div class=\"spinner-grow\"></div>\n" +
                "</div>");
        }

        function hideLoading () {
            $('body').find('.background-stop').remove();
        }

        if ($('.class-child-feedback').length == 0) {
            $('#eventCancel').hide();
        }
    </script>
    <script src="{{ CoreUrl::asset('education/js/team_scope.js') }}"></script>
@endsection