@if(Session::has('flash_success'))
    <div class="alert alert-success">
        <ul>
            <li>
                {{ Session::get('flash_success') }}
            </li>
        </ul>
    </div>
@endif
@if(Session::has('flash_error'))
    <div class="alert alert-warning not-found">
        <ul>
            <li>
                {{ Session::get('flash_error') }}
            </li>
        </ul>
    </div>
@endif
{!! csrf_field() !!}
<div class="row">
    {{-- Education Subscribers --}}
    <div class="col-md-4">
        <div class="form-group">
            <label class="col-sm-3 control-label">{{ trans('education::view.Education.Subscribers') }}</label>
            <div class="col-sm-9">
                <p class="form-control" disabled="">{{ $employee->name }}</p>
            </div>
        </div>
    </div>
    {{-- Education Position --}}
    <div class="col-md-4">
        <div class="form-group">
            <label class="col-sm-3 control-label">{{ trans('education::view.Education.Position') }}</label>
            <div class="col-sm-9">
                <p class="form-control" disabled="">{{ $position->position }}</p>
            </div>
        </div>
    </div>
    {{-- Education Division --}}
    <div class="col-md-4">
        <div class="form-group">
            <label class="col-sm-3 control-label">{{ trans('education::view.Education.Division') }}</label>
            <div class="col-sm-9">
                <p class="form-control" disabled="">{{ $division->division }}</p>
            </div>
        </div>
    </div>
    {{-- Education Status --}}
    <div class="col-md-4">
        <div class="form-group">
            <label class="col-sm-3 control-label">{{ trans('education::view.Education.Status') }}</label>
            <div class="col-sm-9">
                @if($action == 'create')
                    <input type="text" class="hidden" name="status" value="{{ $statusRequest }}">
                    <p class="form-control" disabled="">{{ trans('education::view.Education.status.Create new') }}</p>
                @else
                    @if($isScopeHrOrCompany)
                        <select class="form-control {{ in_array($education->status, [$statusPending, $statusReject]) ? 'label-warning' : '' }}" name="status" id="status">
                            @foreach($status as $key => $item)
                                <option value="{{ $key }}" {{ old('status', $education->status) == $key ? 'selected' : '' }}>{{ $item }}</option>
                            @endforeach
                        </select>
                    @else
                        <input type="text" class="hidden" name="status" value="{{ $education->status }}">
                        <input type="text" class="form-control {{ in_array($education->status, [$statusPending, $statusReject]) ? 'label-warning' : '' }}" value="{{ $status[$education->status] }}" disabled="">
                    @endif
                @endif
            </div>
        </div>
    </div>
    {{-- Education Register Date --}}
    <div class="col-md-4">
        <div class="form-group">
            <label class="col-sm-3 control-label">{{ trans('education::view.Education.Registration Date') }}</label>
            <div class="col-sm-9">
                <p class="form-control" disabled="">{{ $education->created_at ? \Carbon\Carbon::parse($education->created_at)->format('d-m-Y') : $curDate }}</p>
            </div>
        </div>
    </div>
    {{-- Education person assigned --}}
    <div class="col-md-4">
        <div class="form-group">
            <label class="col-sm-3 control-label">{{ trans('education::view.Education.Person assigned') }}</label>
            <div class="col-sm-9">
                <select class="form-control select-search-person" id="assign_id" name="assign_id" data-remote-url="{{ URL::route('education::education.request.ajax-person-assigned-list') }}" {{ $disable ? 'readonly' : '' }} {{ $disable ? 'disabled' : '' }}>
                    @if($education->assigned)
                        <option value="{{ $education->assigned->id }}" selected>{{ $education->assigned->name }}</option>
                    @endif
                </select>
            </div>
        </div>
    </div>

    {{-- Education title --}}
    <div class="col-md-12">
        <div class="row form-group">
            <label class="col-sm-3 col-md-1 control-label required">{{ trans('education::view.Education.Title') }}<em>*</em></label>
            <div class="col-sm-9 col-md-7">
                <input type="text" class="form-control" value="{{ ($education->title) ? $education->title : old('title') }}" name="title" placeholder="{{ trans('education::view.Education.Title.placeholder') }}..." id="title" class="form-control" autocomplete="off" {{ $disable ? 'readonly' : '' }}>
                @if($errors->has('title'))
                    <p class="error">{{$errors->first('title')}}</p>
                @endif
            </div>
        </div>
        <div class="row">
            {{-- Education Scope & Type --}}
            <div class="col-md-12">
                <div class="row">
                    {{-- Education Division scope --}}
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-sm-3 control-label required" >{{ trans('education::view.Education.Scope') }}<em>*</em></label>
                            <div class="col-sm-9">
                                <select class="form-control" id="scope_total" name="scope_total" {{ $disable ? 'readonly' : '' }}>
                                    @if($action == 'create')
                                        @foreach($scopeTotal as $key => $item)
                                            <option value="{{ $key }}" {{ old('scope_total') == $key ? 'selected' : '' }}>{{ $item }}</option>
                                        @endforeach
                                    @else
                                        @foreach($scopeTotal as $key => $item)
                                            @if(old('scope_total'))
                                                <option value="{{ $key }}" {{ old('scope_total') == $key ? 'selected' : '' }}>{{ $item }}</option>
                                            @else
                                                <option value="{{ $key }}" {{ $education->scope_total == $key ? 'selected' : '' }}>{{ $item }}</option>
                                            @endif
                                        @endforeach
                                    @endif
                                </select>
                                @if($errors->has('scope_total'))
                                    <p class="error">{{$errors->first('scope_total')}}</p>
                                @endif
                                <span id="scope_total-error" class="data-error error"></span>
                            </div>
                        </div>
                    </div>
                    {{-- Education Scope --}}
                    <div class="col-md-4">
                        <div class="form-group select-full">
                            <label class="col-sm-3 control-label required">{{ trans('education::view.Education.Division') }}<em>*</em></label>
                            <div class="col-sm-9">
                                <div id="select-team" class="multi-select-style">
                                    @php
                                        if($errors->has('team_id')) {
                                            $teamSelected = old('team_id');
                                        }
                                    @endphp
                                    <select name="team_id[]" class="form-control hidden select-multi" multiple="multiple" {{ $disable ? 'readonly' : '' }} {{ $disable ? 'disabled' : '' }}>
                                        @if($action == 'create')
                                            @foreach($teamsOptionAll as $option)
                                                <option class="js-team" value="{{ $option['value'] }}"
                                                        {{ old('team_id') && in_array($option['value'], old('team_id')) ? 'selected' : '' }} data-is-checked="{{ (old('team_id') && in_array($option['value'], old('team_id')))  ? 'true' : 'false'}}">{{ $option['label'] }}
                                                </option>
                                            @endforeach
                                        @else
                                            @if(!old('team_id'))
                                                @foreach($teamsOptionAll as $option)
                                                    <option class="js-team" value="{{ $option['value'] }}"
                                                            {{ (old('team_id') && in_array($option['value'], old('team_id'))) || (isset($teamSelected) && in_array($option['value'], $teamSelected)) ? 'selected' : '' }} data-is-checked="{{ (old('team_id') && in_array($option['value'], old('team_id'))) || (isset($teamSelected) && in_array($option['value'], $teamSelected)) ? 'true' : 'false'}}">{{ $option['label'] }}
                                                    </option>
                                                @endforeach
                                            @else
                                                @foreach($teamsOptionAll as $option)
                                                    <option class="js-team" value="{{ $option['value'] }}"
                                                            {{ old('team_id') && in_array($option['value'], old('team_id')) ? 'selected' : '' }} data-is-checked="{{ (old('team_id') && in_array($option['value'], old('team_id')))  ? 'true' : 'false'}}">{{ $option['label'] }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        @endif
                                    </select>
                                </div>
                                @if($errors->has('team_id'))
                                    <p class="error">{{$errors->first('team_id')}}</p>
                                @endif
                                <span id="team_id-error" class="data-error error"></span>
                            </div>
                        </div>
                    </div>
                    {{-- Education Type --}}
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-sm-3 control-label required">{{ trans('education::view.Education.Education Type') }}<em>*</em></label>
                            <div class="col-sm-9">
                                <select class="form-control" id="type_id" name="type_id" {{ $disable ? 'readonly' : '' }}>
                                    <option value=""></option>
                                    @if($types)
                                        @if(old('type_id'))
                                            @foreach($types as $item)
                                                <option value="{{ $item['id'] }}" {{ old('type_id') == $item['id'] ? 'selected' : '' }}>{{ $item['name'] }}</option>
                                            @endforeach
                                        @else
                                            @foreach($types as $item)
                                                <option value="{{ $item['id'] }}" {{ ($education->type_id == $item['id'] ? 'selected' : '') }}>{{ $item['name'] }}</option>
                                            @endforeach
                                        @endif
                                    @endif
                                </select>
                                @if($errors->has('type_id'))
                                    <p class="error">{{$errors->first('type_id')}}</p>
                                @endif
                                <span id="type_id-error" class="data-error error"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Education Lecturers & Start date --}}
            <div class="col-md-12">
                <div class="row">
                    {{-- Education Lecturers --}}
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{ trans('education::view.Education.Teacher') }} </label>
                            <div class="col-sm-9">
                                <select class="form-control select-search-employee" name="teacher_id" data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee') }}" {{ $disable ? 'readonly' : '' }} {{ $disable ? 'disabled' : '' }}>
                                    @if($education->teacher)
                                        <option value="{{ $education->teacher->id }}" selected>{{ $education->teacher->name }}</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                    {{-- Education Start date --}}
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-sm-3 control-label" for="start_date">{{ trans('education::view.Education.Start Date') }}</label>
                            <div class="col-sm-9">
                                <div class="input-group padding-0">
                                                        <span class="input-group-btn">
                                                            <button class="btn btn-default" type="button"><i class="fa fa-calendar"></i></button>
                                                        </span>
                                    <input type='text' autocomplete="off" class="form-control date start-date" id="start_date" name="start_date" data-provide="datepicker" placeholder="dd-mm-yyyy" tabindex=9 value="{{ $education->start_date ? \Carbon\Carbon::parse($education->start_date)->format('d-m-Y') : old('start_date') }}" {{ $disable ? 'readonly' : '' }} />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Education Object --}}
            <div class="col-md-12">
                <div class="form-group select-full">
                    <label class="col-sm-3 col-md-1 control-label required">{{ trans('education::view.Education.Object') }}<em>*</em></label>
                    <div class="col-sm-9 col-md-11" id="education_object">
                        @php
                            if($errors->has('object')) {
                                $object_ids = old('object');
                            }
                        @endphp
                        <select class="form-control education_object" id="object_id" name="object[]"  multiple="multiple" {{ $disable ? 'readonly' : '' }} {{ $disable ? 'disabled' : '' }}>
                            @if($action == 'create')
                                @if($objects)
                                    @foreach($objects as $key => $value)
                                        <option value="{{ $key }}" <?php if(old('object') && in_array($key, old('object'))){ echo 'selected';} ?>>{{ $value }}</option>
                                    @endforeach
                                @endif
                            @else
                                @if(!old('object'))
                                    @if($objects)
                                        @foreach($objects as $key => $value)
                                            <option value="{{ $key }}" <?php if((old('object') && in_array($key, old('object'))) || (isset($object_ids) &&  in_array($key, $object_ids))){ echo 'selected';} ?>>{{ $value }}</option>
                                        @endforeach
                                    @endif
                                @else
                                    @if($objects)
                                        @foreach($objects as $key => $value)
                                            <option value="{{ $key }}" <?php if(old('object') && in_array($key, old('object'))){ echo 'selected';} ?>>{{ $value }}</option>
                                        @endforeach
                                    @endif
                                @endif
                            @endif
                        </select>
                        @if($errors->has('object'))
                            <p class="error">{{$errors->first('object')}}</p>
                        @endif
                        <span id="object_id-error" class="data-error error"></span>
                    </div>
                </div>
            </div>
            {{-- Education Content --}}
            <div class="col-md-12">
                <div class="form-group">
                    <label class="col-sm-3 col-md-1 control-label required">{{ trans('education::view.Education.Education Content') }}<em>*</em></label>
                    <div class="col-sm-9 col-md-11">
                        <textarea rows="10" name="description" id="description" class="form-control" {{ $disable ? 'readonly' : '' }}>{{ $education->description ? $education->description : old('description') }}</textarea>
                        @if($errors->has('description'))
                            <p class="error">{{$errors->first('description')}}</p>
                        @endif
                        <span id="description-error" class="data-error error"></span>
                    </div>
                </div>
            </div>
            {{-- Education Content --}}
            <div class="col-md-12">
                <div class="form-group" id="education-tag">
                    <label class="col-sm-3 col-md-1 control-label required">{{ trans('education::view.Education.Keywords') }}<em>*</em></label>
                    <div class="col-sm-9 col-md-11">
                        @php
                            if($errors->has('tag')) {
                                $tag_ids = old('tag');
                            }
                        @endphp
                        <select name="tag[]" class="education-tag" id="tag_id" multiple {{ $disable ? 'readonly' : '' }} {{ $disable ? 'disabled' : '' }}>
                            @if($action == 'create')
                                @if(old('tag'))
                                    @foreach (old('tag') as $item)
                                        <option value="{{ $item }}" selected>{{ $item }}</option>
                                    @endforeach
                                @endif
                            @else
                                @if(!old('tag'))
                                    @if($tags)
                                        @foreach ($tags as $item)
                                            <option value="{{ $item['name'] }}" {{ isset($tag_ids) && in_array($item['id'], $tag_ids) ? " selected" : "" }}>{{ $item['name'] }}</option>
                                        @endforeach
                                    @endif
                                @else
                                    @if(old('tag'))
                                        @foreach (old('tag') as $item)
                                            <option value="{{ $item }}" selected>{{ $item }}</option>
                                        @endforeach
                                    @endif
                                @endif
                            @endif
                        </select>
                        @if($errors->has('tag'))
                            <p class="error">{{$errors->first('tag')}}</p>
                        @endif
                        <span id="tag_id-error" class="data-error error"></span>
                    </div>
                </div>
            </div>
            @if($isScopeHrOrCompany)
                {{-- Education Reason --}}
                <div class="col-md-12 reason" id="reason-form">
                    <?php
                    $descArr = [];
                    $desc = '';
                    if($education->reason) {
                        $descArr = json_decode($education->reason, true);
                        $desc = end($descArr);
                    }
                    ?>
                    <div class="form-group">
                        <label class="col-sm-1 control-label required">{{ trans('education::view.Education.Reason') }}<em>*</em></label>
                        <div class="col-sm-11">
                            <textarea rows="5" id="reason" name="reason" class="form-control" >{!! old('reason', $desc ? $desc['description'] : '') !!}</textarea>
                            @include('education::education-request.include.reason-histories')
                            @if($errors->has('reason'))
                                <p class="error">{{$errors->first('reason')}}</p>
                            @endif
                        </div>
                    </div>
                </div>
                {{-- Education Course --}}
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{ trans('education::view.Education.Course') }}</label>
                        <div class="col-sm-9">
                            <select class="form-control select-search-course" name="course_id" data-remote-url="{{ URL::route('education::education.request.ajax-course-list') }}">
                                @if($education->course)
                                    <option value="{{ $education->course->id }}" selected>{{ $education->course->name }}</option>
                                @endif
                            </select>
                            @if($education->course)
                                <div class="course">
                                    <a class="course_name" href="{{ URL::route('education::education-profile.detail', ['id' => $education->course->id, 0]) }}">{{ $education->course->name }}</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                @if($education->status == 4)
                    <div class="col-md-12 course">
                        <div class="form-group">
                            <label class="col-sm-1 control-label">{{ trans('education::view.Education.Course') }}</label>
                            <div class="col-sm-11">
                                @if($education->course)
                                    <a class="course_name" href="{{ URL::route('education::education-profile.detail', ['id' => $education->course->id, 0]) }}">{{ $education->course->name }}</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
                @if(in_array($education->status, [$statusPending, $statusReject]))
                    <div class="col-sm-12 reason">
                        <div class="form-group">
                            <label class="col-sm-1 control-label">{{ trans('education::view.Education.Reason') }}</label>
                            <div class="col-sm-11">
                                @include('education::education-request.include.reason-histories')
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>