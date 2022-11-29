<?php
use Illuminate\Support\Str;
?>
<div id="workorder_skill_request">
    <h5>{{ trans('project::view.Note security') }}
        <a href="{{ asset('project/images/skill_request.png') }}" target="_blank" data-toggle="tooltip" data-html="true">{{ trans('project::view.Here') }}</a> {{ trans('project::view.view sample report') }}</h5>
    @if(isset($detail))
        <div class="table-responsive table-content" id="table-skill_request">
            <table class="edit-table table table-bordered table-condensed dataTable">
                <thead>
                    <tr>
                        <th class="width-5-per">{{trans('project::view.No')}}</th>
                        <th class="width-10-per">{{ trans('project::view.Knowledge/Skills') }}</th>
                        <th class="width-10-per">{{trans('project::view.Category')}}</th>
                        <th class="width-5-per">{{ trans('project::view.Course Name') }}</th>
                        <th class="width-10-per">{{ trans('project::view.Mode') }}</th>
                        <th class="width-10-per">{{ trans('project::view.Provider') }}</th>
                        <th class="width-10-per">{{ trans('project::view.Required of Role') }}</th>
                        <th class="width-5-per">{{ trans('project::view.Hours') }}</th>
                        <th class="width-10-per">{{ trans('project::view.Skill level Assessment Method') }}</th>
                        <th class="width-10-per">{{trans('project::view.Remark')}}</th>
                        @if(isset($permissionEdit) && $permissionEdit  && $checkEditWorkOrder)
                        <th class="width-5-per">&nbsp;</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                @foreach($getSkillRequest as $key => $kill)
                    <tr class=" tr-skill_request tr-skill_request-css"  data-toggle="tooltip" data-placement="top" title="">
                        <td>{{ $key + 1 }}</td>
                        <td>
                            <span data-toggle="popover" data-html='true' name="skill" data-type="skill_request" data-id="{{$kill->id}}" class="skill-skill_request-{{$kill->id}} white-space popover-wo-other">{!!Str::words(nl2br(e($kill->skill)), 30, '...')!!}</span>

                            <textarea class="display-none form-control input-skill-skill_request-{{$kill->id}} white-space popover-wo-other" name="skill" rows="2">{!! $kill->skill !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' name="category" data-type="skill_request" data-id="{{$kill->id}}" class="category-skill_request-{{$kill->id}} white-space popover-wo-other">{!!Str::words(nl2br(e($kill->category)), 30, '...')!!}</span>

                            <textarea class="display-none form-control input-category-skill_request-{{$kill->id}} white-space" name="category" rows="2">{!! $kill->category !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' name="course_name" data-type="skill_request" data-id="{{$kill->id}}" class="course_name-skill_request-{{$kill->id}} white-space popover-wo-other">{!!Str::words(nl2br(e($kill->course_name)), 30, '...')!!}</span>

                            <textarea class="display-none form-control input-course_name-skill_request-{{$kill->id}} white-space" name="course_name" rows="2">{!! $kill->course_name !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' name="mode" data-type="skill_request" data-id="{{$kill->id}}" class="mode-skill_request-{{$kill->id}} white-space popover-wo-other">{!!Str::words(nl2br(e($kill->mode)), 30, '...')!!}</span>

                            <textarea class="display-none form-control input-mode-skill_request-{{$kill->id}} white-space" name="mode" rows="2">{!! $kill->mode !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' name="provider" data-type="skill_request" data-id="{{$kill->id}}" class="provider-skill_request-{{$kill->id}} white-space popover-wo-other">{!!Str::words(nl2br(e($kill->provider)), 30, '...')!!}</span>

                            <textarea class="display-none form-control input-provider-skill_request-{{$kill->id}} white-space" name="provider" rows="2">{!! $kill->provider !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' name="required_for_role" data-type="skill_request" data-id="{{$kill->id}}" class="required_role-skill_request-{{$kill->id}} white-space popover-wo-other">{!!Str::words(nl2br(e($kill->required_for_role)), 30, '...')!!}</span>

                            <textarea class="display-none form-control input-required_role-skill_request-{{$kill->id}} white-space" name="required_role" rows="2">{!! $kill->required_for_role !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' name="hours" data-type="skill_request" data-id="{{$kill->id}}" class="hours-skill_request-{{$kill->id}} white-space popover-wo-other">{!!Str::words(nl2br(e($kill->hours)), 30, '...')!!}</span>

                            <textarea class="display-none form-control input-hours-skill_request-{{$kill->id}} white-space" name="hours" rows="2">{!! $kill->hours !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' name="level_assessment_method" data-type="skill_request" data-id="{{$kill->id}}" class="level-skill_request-{{$kill->id}} white-space popover-wo-other">{!!Str::words(nl2br(e($kill->level_assessment_method)), 30, '...')!!}</span>

                            <textarea class="display-none form-control input-level-skill_request-{{$kill->id}} white-space" name="level" rows="2">{!! $kill->level_assessment_method !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' name="remark" data-type="skill_request" data-id="{{$kill->id}}" class="remark-skill_request-{{$kill->id}} white-space popover-wo-other">{!!Str::words(nl2br(e($kill->remark)), 30, '...')!!}</span>

                            <textarea class="display-none form-control input-remark-skill_request-{{$kill->id}} white-space" name="remark" rows="2">{!! $kill->remark !!}</textarea>
                        </td>
                        <td>
                            @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                                <span>
                                    <i class="fa fa-floppy-o display-none btn-add save-skill_request save-skill_request-{{$kill->id}}" data-id="{{$kill->id}}" data-status="{{$kill->status}}"></i>
                                    <i class="fa fa-pencil-square-o width-38 btn-edit edit-skill_request edit-skill_request-{{$kill->id}}" data-id="{{$kill->id}}" data-status="{{$kill->status}}"></i>
                                    <span class="btn btn-primary display-none loading-item" id="loading-item-skill_request-{{$kill->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                                    <i class="fa fa-trash-o btn-delete delete-skill_request delete-confirm-new delete-skill_request-{{$kill->id}}" data-id="{{$kill->id}}" data-status="{{$kill->status}}"></i>
                                    <i class="display-none fa fa-arrow-left btn-refresh btn-primary refresh-skill_request refresh-skill_request-{{$kill->id}}" data-id="{{$kill->id}}" data-status="{{$kill->status}}"></i>
                                </span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                    <tr class="tr-add-skill_request">
                        <td colspan="8" class="slove-skill_request">
                            <span href="#" class="btn-add add-skill_request"><i class="fa fa-plus"></i></span>
                        </td>
                    </tr>
                    <tr class="display-none tr-skill_request tr-skill_request-hidden tr-skill_request-css">
                        <td></td>
                        <td>
                            <span>
                                <textarea class="form-control width-100 skill-skill_request" name="skill" rows="2"></textarea>
                            </span>
                        </td>
                        <td>
                            <span>
                                <textarea class="form-control width-100 category-skill_request" name="category" rows="2"></textarea>
                            </span>
                        </td>
                        <td>
                            <span>
                                <textarea class="form-control width-100 course_name-skill_request" name="course_name" rows="2"></textarea>
                            </span>
                        </td>
                        <td>
                            <span>
                                <textarea class="form-control width-100 mode-skill_request" name="mode" rows="2"></textarea>
                            </span>
                        </td>
                        <td>
                            <span>
                                <textarea class="form-control width-100 provider-skill_request" name="provider" rows="2"></textarea>
                            </span>
                        </td>
                        <td>
                            <span>
                                <textarea class="form-control width-100 required_role-skill_request" name="required_role" rows="2"></textarea>
                            </span>
                        </td>
                        <td>
                            <span>
                                <textarea class="form-control width-100 hours-skill_request" name="hours" rows="2"></textarea>
                            </span>
                        </td>
                        <td>
                            <span>
                                <textarea class="form-control width-100 level-skill_request" name="level" rows="2"></textarea>
                            </span>
                        </td>
                        <td>
                            <span>
                                <textarea class="form-control width-100 remark-skill_request" name="remark" rows="2"></textarea>
                            </span>
                        </td>
                        <td>
                            <span>
                                <span class="btn btn-primary display-none loading-item loading-item"><i class="fa fa-refresh fa-spin"></i></span>
                                <i class="fa fa-floppy-o btn-add add-new-skill_request"></i>
                                <i class="fa fa-trash-o btn-delete remove-skill_request"></i>
                            </span>
                        </td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    @else
        <div class="table-responsive table-content" id="table-skill_request">
            <table class="edit-table table table-bordered table-condensed dataTable">
                <thead>
                    <tr>
                        <th class="width-5-per">{{trans('project::view.No')}}</th>
                        <th class="width-10-per">{{ trans('project::view.Knowledge/Skills') }}</th>
                        <th class="width-10-per">{{trans('project::view.Category')}}</th>
                        <th class="width-5-per">{{ trans('project::view.Course Name') }}</th>
                        <th class="width-10-per">{{ trans('project::view.Mode') }}</th>
                        <th class="width-10-per">{{ trans('project::view.Provider') }}</th>
                        <th class="width-10-per">{{ trans('project::view.Required of Role') }}</th>
                        <th class="width-5-per">{{ trans('project::view.Hours') }}</th>
                        <th class="width-10-per">{{ trans('project::view.Skill level Assessment Method') }}</th>
                        <th class="width-10-per">{{trans('project::view.Remark')}}</th>
                        @if(isset($permissionEdit) && $permissionEdit  && $checkEditWorkOrder)
                            <th class="width-5-per">&nbsp;</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                @foreach($getSkillRequest as $key => $kill)
                    <tr class=" tr-skill_request tr-skill_request-css"  data-toggle="tooltip" data-placement="top" title="">
                        <td>{{ $key + 1 }}</td>
                        <td>
                            <span data-toggle="popover" data-html='true' name="skill" data-type="skill_request" data-id="{{$kill->id}}" class="skill-skill_request-{{$kill->id}} white-space popover-wo-other">{!!Str::words(nl2br(e($kill->skill)), 30, '...')!!}</span>

                            <textarea class="display-none form-control input-skill-skill_request-{{$kill->id}} white-space" name="skill" rows="2">{!! $kill->skill !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' name="category" data-type="skill_request" data-id="{{$kill->id}}" class="category-skill_request-{{$kill->id}} white-space popover-wo-other">{!!Str::words(nl2br(e($kill->category)), 30, '...')!!}</span>

                            <textarea class="display-none form-control input-category-skill_request-{{$kill->id}} white-space" name="category" rows="2">{!! $kill->category !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' name="course_name" data-type="skill_request" data-id="{{$kill->id}}" class="course_name-skill_request-{{$kill->id}} white-space popover-wo-other">{!!Str::words(nl2br(e($kill->course_name)), 30, '...')!!}</span>

                            <textarea class="display-none form-control input-course_name-skill_request-{{$kill->id}} white-space" name="course_name" rows="2">{!! $kill->course_name !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' name="mode" data-type="skill_request" data-id="{{$kill->id}}" class="mode-skill_request-{{$kill->id}} white-space popover-wo-other">{!!Str::words(nl2br(e($kill->mode)), 30, '...')!!}</span>

                            <textarea class="display-none form-control input-mode-skill_request-{{$kill->id}} white-space" name="mode" rows="2">{!! $kill->mode !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' name="provider" data-type="skill_request" data-id="{{$kill->id}}" class="provider-skill_request-{{$kill->id}} white-space popover-wo-other">{!!Str::words(nl2br(e($kill->provider)), 30, '...')!!}</span>

                            <textarea class="display-none form-control input-provider-skill_request-{{$kill->id}} white-space" name="provider" rows="2">{!! $kill->provider !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' name="required_for_role" data-type="skill_request" data-id="{{$kill->id}}" class="required_role-skill_request-{{$kill->id}} white-space popover-wo-other">{!!Str::words(nl2br(e($kill->required_for_role)), 30, '...')!!}</span>

                            <textarea class="display-none form-control input-required_role-skill_request-{{$kill->id}} white-space" name="required_role" rows="2">{!! $kill->required_for_role !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' name="hours" data-type="skill_request" data-id="{{$kill->id}}" class="hours-skill_request-{{$kill->id}} white-space popover-wo-other">{!!Str::words(nl2br(e($kill->hours)), 30, '...')!!}</span>

                            <textarea class="display-none form-control input-hours-skill_request-{{$kill->id}} white-space" name="hours" rows="2">{!! $kill->hours !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' name="level_assessment_method" data-type="skill_request" data-id="{{$kill->id}}" class="level-skill_request-{{$kill->id}} white-space popover-wo-other">{!!Str::words(nl2br(e($kill->level_assessment_method)), 30, '...')!!}</span>

                            <textarea class="display-none form-control input-level-skill_request-{{$kill->id}} white-space" name="level" rows="2">{!! $kill->level_assessment_method !!}</textarea>
                        </td>
                        <td>
                            <span data-toggle="popover" data-html='true' name="remark" data-type="skill_request" data-id="{{$kill->id}}" class="remark-skill_request-{{$kill->id}} white-space popover-wo-other">{!!Str::words(nl2br(e($kill->remark)), 30, '...')!!}</span>

                            <textarea class="display-none form-control input-remark-skill_request-{{$kill->id}} white-space" name="remark" rows="2">{!! $kill->remark !!}</textarea>
                        </td>
                        @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                            <td>
                                <span>
                                    <i class="fa fa-floppy-o display-none btn-add save-skill_request save-skill_request-{{$kill->id}}" data-id="{{$kill->id}}" data-status="{{$kill->status}}"></i>
                                    <i class="fa fa-pencil-square-o width-38 btn-edit edit-skill_request edit-skill_request-{{$kill->id}}" data-id="{{$kill->id}}" data-status="{{$kill->status}}"></i>
                                    <span class="btn btn-primary display-none loading-item" id="loading-item-skill_request-{{$kill->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                                    <i class="fa fa-trash-o btn-delete delete-skill_request delete-confirm-new delete-skill_request-{{$kill->id}}" data-id="{{$kill->id}}" data-status="{{$kill->status}}"></i>
                                    <i class="display-none fa fa-arrow-left btn-refresh btn-primary refresh-skill_request refresh-skill_request-{{$kill->id}}" data-id="{{$kill->id}}" data-status="{{$kill->status}}"></i>
                                </span>
                            </td>
                        @endif
                    </tr>
                @endforeach
                @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                    <tr class="tr-add-skill_request">
                        <td colspan="8" class="slove-skill_request">
                            <span href="#" class="btn-add add-skill_request"><i class="fa fa-plus"></i></span>
                        </td>
                    </tr>
                    <tr class="display-none tr-skill_request tr-skill_request-hidden tr-skill_request-css">
                        <td></td>
                        <td>
                        <span>
                            <textarea class="form-control width-100 skill-skill_request" name="skill" rows="2"></textarea>
                        </span>
                        </td>
                        <td>
                        <span>
                            <textarea class="form-control width-100 category-skill_request" name="category" rows="2"></textarea>
                        </span>
                        </td>
                        <td>
                        <span>
                            <textarea class="form-control width-100 course_name-skill_request" name="course_name" rows="2"></textarea>
                        </span>
                        </td>
                        <td>
                        <span>
                            <textarea class="form-control width-100 mode-skill_request" name="mode" rows="2"></textarea>
                        </span>
                        </td>
                        <td>
                        <span>
                            <textarea class="form-control width-100 provider-skill_request" name="provider" rows="2"></textarea>
                        </span>
                        </td>
                        <td>
                        <span>
                            <textarea class="form-control width-100 required_role-skill_request" name="required_role" rows="2"></textarea>
                        </span>
                        </td>
                        <td>
                        <span>
                            <textarea class="form-control width-100 hours-skill_request" name="hours" rows="2"></textarea>
                        </span>
                        </td>
                        <td>
                        <span>
                            <textarea class="form-control width-100 level-skill_request" name="level" rows="2"></textarea>
                        </span>
                        </td>
                        <td>
                        <span>
                            <textarea class="form-control width-100 remark-skill_request" name="remark" rows="2"></textarea>
                        </span>
                        </td>
                        <td>
                        <span>
                            <span class="btn btn-primary display-none loading-item loading-item"><i class="fa fa-refresh fa-spin"></i></span>
                            <i class="fa fa-floppy-o btn-add add-new-skill_request"></i>
                            <i class="fa fa-trash-o btn-delete remove-skill_request"></i>
                        </span>
                        </td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    @endif
</div>