<?php 
use Rikkei\Project\View\View as ViewProject;
use Rikkei\Core\View\View as ViewHelper;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\Model\Task;
use Rikkei\Team\Model\Employee;
use Rikkei\Project\View\GeneralProject;
use Rikkei\Project\Model\ProjectWOBase;

$allNameTab = Task::getAllNameTabWorkorder();
if(!isset($detail)) {
    return;
}
?>

@if(config('project.workorder_approved.project_member'))
<div class="table-responsive table-content-{{$allNameTab[Task::TYPE_WO_PROJECT_MEMBER]
}} multiselect2-wrapper flag-over-hidden" id="table-project-member">
    <table class="edit-table table table-bordered table-condensed dataTable tablesorter-blue tablesorter">
        <thead>
            <tr>
                <th class="width-5-per no-sorter">{{trans('project::view.No')}}</th>
                <th class="width-15-per">{{trans('project::view.Position')}}</th>
                <th class="width-20-per">{{trans('project::view.Account')}}</th>
                <th style="min-width: 110px;">
                    <span data-toggle="tooltip" title="{{ trans('project::view.Programming language') }}">{{trans('project::view.PL')}}</span>
                    
                </th>
                <th class="width-17-per" style="width: 100px;">{{trans('project::view.Start date')}}</th>
                <th class="width-16-per" style="width: 100px;">{{trans('project::view.End date')}}</th>
                <th class="width-9-per no-sorter">{{trans('project::view.Effort')}} (%)</th>
                <th class="width-9-per no-sorter">{{trans('project::view.Resource')}}</th>
                @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                <th class="width-9-per no-sorter">&nbsp;</th>
                @endif
            </tr>
        </thead>
        <tbody>
            <?php
                $totalResource = 0;
                $totalResourceDraft = 0;
                $keyIndex = 0;
                $hasDraft = false;
                $arrayStatusDelete = [ProjectMember::STATUS_DRAFT_DELETE,
                                        ProjectMember::STATUS_SUBMMITED_DELETE,
                                        ProjectMember::STATUS_FEEDBACK_DELETE,
                                        ProjectMember::STATUS_REVIEWED_DELETE];
            ?>
            @foreach($allMembers as $member)
                <?php
                $hasChild = false;
                $memberParent = $member['parent'];
                $memberChild = $member['parent'];
                if ($memberParent->status == ProjectMember::STATUS_APPROVED) {
                    if (isset($member['child']) && $member['child']) {
                        if (ViewProject::isChangeValue($memberParent, $member['child'])) {
                            $memberChild = $member['child'];
                            $hasChild = true;
                        }
                    }
                }
                $member = $memberParent;
                ?>
                @if($member->status == ProjectMember::STATUS_APPROVED && $hasChild)
                    <?php
                        if (in_array($memberChild->status, $arrayStatusDelete)) {
                            $background = ViewProject::getColorStatusWorkOrder($memberChild->status);
                            $isOpenTooltip = true;   
                        } else {
                            $isOpenTooltip = false;   
                            $background = ViewProject::getColorStatusWorkOrder($member->status);
                        }
                    ?>
                    @if ($member->is_disabled)
                        <tr class="background-{{$background}} tr-project-{{$memberChild->id}} tr-project is-tooltip" title="{{trans('project::view.Status disabled')}}">
                    @else
                        @if($isOpenTooltip)
                        <tr class="background-{{$background}} tr-project-{{$memberChild->id}} tr-project is-tooltip" title="{{ProjectMember::statusLabel()[$memberChild->status]}}">
                        @else
                        <tr class="background-{{$background}} tr-project-{{$memberChild->id}} tr-project">
                        @endif
                    @endif
                @else
                    @if ($member->is_disabled)
                        <tr class="background-{{ViewProject::getColorStatusWorkOrder($member->status)}} tr-project-{{$memberChild->id}} tr-project is-tooltip" title="{{trans('project::view.Status disabled')}}">
                    @else
                        <tr class="background-{{ViewProject::getColorStatusWorkOrder($member->status)}} tr-project-{{$memberChild->id}} tr-project is-tooltip" title="{{ProjectMember::statusLabel()[$member->status]}}">
                    @endif
                @endif
                    <td>{{ ++$keyIndex }}</td>
                    <!-- position -->
                    @if($hasChild && $memberChild->type != $member->type)
                        <td class="td-type is-change-value" data-container="body" data-toggle="tooltip" data-placement="top" title="{{trans('project::view.Approved Value')}}: {{$allTypeMember[$member->type]}}">
                    @else
                        <td class="td-type">
                    @endif
                        <span class="type-project-member-{{$memberChild->id}}" data-value="{{$memberChild->type}}">{{$allTypeMember[$memberChild->type]}}</span>
                        <select name="type" class="display-none form-control width-100 input-type-project-member-{{$memberChild->id}} type-project-member select-proj-member-type">
                            @foreach($allTypeMember as $key => $type)
                                <option value="{{$key}}" class="form-control width-100" {{$memberChild->type == $key ? 'selected' : ''}}>{{$type}}</option>
                            @endforeach                        
                        </select>
                    </td>
                    <!-- end position -->
                    
                    <!-- email -->
                    @if($hasChild && $memberChild->employee_id != $member->employee_id)
                    <td class="td-employee is-change-value" data-container="body" data-toggle="tooltip" data-placement="top" title="{{trans('project::view.Approved Value')}}: {{ GeneralProject::getNickName($member->email) }}">
                    @else
                    <td class="td-employee">
                    @endif
                        <span class="employee_id-project-member-{{ $memberChild->id }}" data-value="{{$memberChild->employee_id}}">{{ GeneralProject::getNickName($memberChild->email) }}</span>
                        <select name="employee_id" class="form-control width-100 hidden input-employee_id-project-member-{{$memberChild->id}} select-search-remote-member"
                            data-id="{{ $memberChild->id }}" data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}">
                            <option value="{{ $memberChild->employee_id }}">{{ GeneralProject::getNickName($memberChild->email) }}</option>
                        </select>
                    </td>
                    <!-- end email -->
                    
                    <!-- programming language -->
                    @if($hasChild && $memberChild->prog_lang_names != $member->prog_lang_names && !GeneralProject::isStatusDelete($memberChild->status))
                        <td class="td-prog is-change-value form-group-select2" data-container="body" 
                            data-toggle="tooltip" data-placement="top" 
                            title="{{trans('project::view.Approved Value')}}: {{ $member->prog_lang_names }}">
                    @else
                        <td class="td-prog form-group-select2">
                    @endif
                        @if (GeneralProject::isStatusDelete($memberChild->status))
                            <span>{{ $member->prog_lang_names }}</span>
                        @else
                            <div class="team-dropdown proj-member-prog-lang fg-valid-custom"{{ in_array($memberChild->type, [ProjectMember::TYPE_DEV, ProjectMember::TYPE_PM, ProjectMember::TYPE_SUBPM, ProjectMember::TYPE_TEAM_LEADER]) ? ' data-dev=1' : '' }}>
                                <span class="prog-project-member-{{ $memberChild->id }}" 
                                    data-value="{{ $memberChild->prog_lang }}">{{ $memberChild->prog_lang_names }}</span>
                                <select name="prog_langs[]" class="display-none input-project-member-{{ $memberChild->id }}
                                    input-prog-project-member-{{ $memberChild->id }} prog-project-member 
                                    multiselect2" multiple>
                                    @foreach($projectProgramsOption as $key => $value)
                                        <option value="{{ $key }}"{{ in_array($key, $memberChild->prog_lang_ids) ? ' selected' : ''}}> {{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </td>
                    <!-- end programming language -->
                    
                    @if($hasChild && $memberChild->start_at != $member->start_at)
                        <td class="is-change-value" data-container="body" data-toggle="tooltip" data-placement="top" title="{{trans('project::view.Approved Value')}}: {{ ViewHelper::getDate($member->start_at) }}">
                    @else
                        <td>
                    @endif
                        <span class="start_at-project-member-{{$memberChild->id}}" data-value="{{$memberChild->type}}">{{ViewHelper::getDate($memberChild->start_at)}}</span>
                        <input type="text" class="display-none form-control width-100 input-start_at-project-member-{{$memberChild->id}}" name="start_at" value="{{ViewHelper::getDate($memberChild->start_at)}}" data-date-week-start="1" data-date-format="yyyy-mm-dd" data-provide="datepicker"  data-date-today-highlight="true" placeholder="{{trans('project::view.YY-MM-DD')}}">
                    </td>
                    @if($hasChild && $memberChild->end_at != $member->end_at)
                    <td class="is-change-value" data-container="body" data-toggle="tooltip" data-placement="top" title="{{trans('project::view.Approved Value')}}: {{ ViewHelper::getDate($member->end_at) }}">
                    @else
                    <td>
                    @endif
                        <span class="end_at-project-member-{{$memberChild->id}}">{{ViewHelper::getDate($memberChild->end_at)}}</span>
                        <input type="text" class="display-none form-control width-100 input-end_at-project-member-{{$memberChild->id}}" name="end_at" value="{{ViewHelper::getDate($memberChild->end_at)}}" data-date-week-start="1" data-date-format="yyyy-mm-dd" data-provide="datepicker"  data-date-today-highlight="true" placeholder="{{trans('project::view.YY-MM-DD')}}">
                    </td>
                    @if($hasChild && $memberChild->effort != $member->effort)
                    <td class="is-change-value" data-container="body" data-toggle="tooltip" data-placement="top" title="{{trans('project::view.Approved Value')}}: {{ $member->effort }}">
                    @else
                    <td>
                    @endif

                        <span class="effort-project-member-{{$memberChild->id}}">{{$memberChild->effort}}</span>
                        <input type="text" class="display-none form-control width-100 input-effort-project-member-{{$memberChild->id}}" name="effort" value="{{$memberChild->effort}}">
                    </td>
                    @if($hasChild && $memberChild->flat_resource != $member->flat_resource)
                    <td class="is-change-value flat_resource_col" data-container="body" data-toggle="tooltip" data-placement="top" title="{{trans('project::view.Approved Value')}}: {{ $member->flat_resource }}">
                    @else
                    <td class="flat_resource_col">
                    @endif
                        <span>{{ $memberChild->flat_resource }}</span>
                    </td>
                    <?php
                        if ($hasChild) {
                            if (!in_array($memberChild->status, $arrayStatusDelete)) {
                                $totalResourceDraft += $memberChild->flat_resource;
                            }
                            $totalResource += $member->flat_resource; 
                        }  else {
                            if ($member->status == ProjectMember::STATUS_APPROVED) {
                                $totalResourceDraft += $memberChild->flat_resource;
                                $totalResource += $member->flat_resource; 
                            } else if(in_array($member->status, $arrayStatusDelete)) {
                                $totalResource += $member->flat_resource;
                            } else {
                                $totalResourceDraft += $memberChild->flat_resource;
                            }
                        }
                    ?>
                    @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
                    <td>
                        @if(in_array($memberChild->status, [
                            ProjectWOBase::STATUS_DRAFT,
                            ProjectWOBase::STATUS_DRAFT_EDIT,
                            ProjectWOBase::STATUS_FEEDBACK,
                            ProjectWOBase::STATUS_FEEDBACK_EDIT,
                            ProjectWOBase::STATUS_FEEDBACK_DELETE,
                            ProjectWOBase::STATUS_DRAFT_DELETE,
                            ProjectWOBase::STATUS_SUBMITTED,
                            ProjectWOBase::STATUS_SUBMIITED_EDIT,
                            ProjectWOBase::STATUS_SUBMMITED_DELETE
                        ]) ||
                        ($memberChild->status == ProjectMember::STATUS_APPROVED && !$hasChild))
                        <span>
                            <i class="fa fa-floppy-o display-none btn-add save-project-member save-project-member-{{$memberChild->id}}" data-id="{{$memberChild->id}}" data-status="{{$memberChild->status}}"></i>
                            @if (!in_array($memberChild->status, $arrayStatusDelete))
                            <i class="fa fa-pencil-square-o width-38 btn-edit edit-project-member edit-project-member-{{$memberChild->id}}" data-id="{{$memberChild->id}}" data-status="{{$memberChild->status}}"></i>
                            @endif
                            <span class="btn btn-primary display-none loading-item" id="loading-item-{{$allNameTab[Task::TYPE_WO_PROJECT_MEMBER]}}-{{$memberChild->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                            <i class="fa @if($hasChild) @if(in_array($memberChild->status, $arrayStatusDelete)) fa-undo @else fa-times @endif @else fa-trash-o @endif btn-delete delete-project-member delete-confirm-new delete-project-member-{{$memberChild->id}}" data-id="{{$memberChild->id}}" data-status="{{$memberChild->status}}"></i>
                            <i class="display-none fa fa-ban btn-refresh btn-primary refresh-project-member refresh-project-member-{{$memberChild->id}}" data-id="{{$memberChild->id}}" data-status="{{$memberChild->status}}" data-employee_id="{{ $memberChild->employee_id }}"></i>
                        </span>
                        @endif
                    </td>
                    @endif
                </tr>
            @endforeach
            
            <!-- total resource -->
            <tr class="tr-total-resource">
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td colspan="2">{{trans('project::view.Total resource allocation')}}</td>
                @if((float)$totalResourceDraft != (float)$totalResource)
                <td class="is-change-value total_flat_resource" data-container="body" data-toggle="tooltip" data-placement="top" title="{{trans('project::view.Approved Value')}}: {{ $totalResource }}">
                    <span>{{ $totalResourceDraft }}</span>
                </td>
                @else
                <td class="total_flat_resource">
                    <span>{{ $totalResource }}</span>
                </td>
                @endif
                <td>&nbsp;</td>
            </tr>
            <!-- end total resource -->
            
            @if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
            <!-- add member -->
            <tr class="tr-add-project-member">
                <td colspan="8" class="slove-project-member">
                  <span href="#" class="btn-add add-project-member"><i class="fa fa-plus"></i></span>
                </td>
            </tr>
            <tr class="display-none tr-project-member tr-projects tr-project-hidden">
                <td></td>
                <td class="td-type">
                    <span>
                        <select name="type" class="form-control width-100 type-project-member-new type-project-member select-search-add select-proj-member-type">
                        @foreach($allTypeMember as $key => $type)
                            <option value="{{$key}}" class="form-control width-100">{{$type}}</option>
                        @endforeach                        
                        </select>
                    </span>
                </td>
                <td class="td-employee">
                    <select name="employee_id" class="form-control width-100 employee_id-project-member select-search-remote-member-add"
                        data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}" id=""></select>
                </td>
                <td class="td-prog form-group-select2">
                    <div class="team-dropdown proj-member-prog-lang object-default fg-valid-custom prog-project-member-add-new">
                        @if (count($projectProgramsOption) == 1)
                            <span class="prog-project-member">{{ reset($projectProgramsOption) }}</span>
                            <input type="hidden" name="prog_langs[]" class="prog-project-member" value="{{ key($projectProgramsOption) }}" />
                        @elseif (count($projectProgramsOption) > 1)
                            <select name="prog_langs[]" class="display-none prog-project-member 
                                multiselect2-proj-add" multiple>
                                @foreach($projectProgramsOption as $key => $value)
                                    <option value="{{ $key }}"> {{ $value }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                </td>
                <td>
                    <input type="text" class="form-control width-100 start_at-project-member" name="start_at" data-date-format="yyyy-mm-dd" data-provide="datepicker" data-date-week-start="1"  data-date-today-highlight="true" placeholder="{{trans('project::view.YY-MM-DD')}}">
                </td>
                <td>
                    <input type="text" class="form-control width-100 end_at-project-member" name="end_at" data-date-format="yyyy-mm-dd" data-provide="datepicker" data-date-week-start="1"  data-date-today-highlight="true" placeholder="{{trans('project::view.YY-MM-DD')}}">
                </td>
                <td>
                    <input type="text" class="form-control width-100 effort-project-member" name="effort">
                </td>
                <td>&nbsp;</td>
                <td>
                    <span>
                        <span class="btn btn-primary display-none loading-item loading-item-{{$allNameTab[Task::TYPE_WO_PROJECT_MEMBER]}}"><i class="fa fa-refresh fa-spin"></i></span>
                        <i class="fa fa-floppy-o btn-add add-new-project-member"></i>
                        <i class="fa fa-trash-o btn-delete remove-project-member"></i>
                    </span>
                </td>
            </tr>
            <!-- end add member -->
            @endif
        </tbody>
    </table>
</div>
@endif
<script>
    jQuery(document).ready(function() {
        $('.select-search-remote-member').each (function() {
            RKfuncion.select2.elementRemote(
                $(this)
            );
        });
        $('.multiselect2').multiselect({
            includeSelectAllOption: false,
            numberDisplayed: 1,
            nonSelectedText: RKVarPassGlobal.multiSelectTextNone,
            allSelectedText: RKVarPassGlobal.multiSelectTextAll,
            nSelectedText: RKVarPassGlobal.multiSelectTextSelectedShort,
            enableFiltering: true,
            onDropdownShown: function() {
                RKfuncion.multiselect2.overfollow(this);
            },
            onDropdownHide: function() {
                RKfuncion.multiselect2.overfollowClose(this);
            }
        });
        $('.team-dropdown.proj-member-prog-lang .btn-group').addClass('display-none');
        $('.team-dropdown.proj-member-prog-lang:not([data-dev="1"]) > .btn-group').addClass('hidden');
    });
</script>