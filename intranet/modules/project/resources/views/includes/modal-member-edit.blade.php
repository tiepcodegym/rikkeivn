@if (isset($project) && $project->id)
    <!--form edit project member-->
    <div class="modal fade in bd-example-modal-lg" id="flag-modal-proj-member-edit">
        <div class="modal-dialog modal-lg">
            <div class="modal-content row">
                <form class="form-horizontal" method="post" autocomplete="off"
                      action="{{ URL::route('project::project.add_project_member') }}"
                      id="form-project-member" data-form-submit="ajax"
                      data-cb-success="projMemberSaveSuccess"
                      data-cb-before-submit="projMemberBeforeSubmit"
                      data-cb-complete="projMemberComplete"
                      data-submit-noti=""
                      data-delete-noti="{!!trans('project::view.Are you sure delete item?')!!}"
                      data-cancel-delete-noti="{!!trans('project::view.Are you sure cancel delete item?')!!}">
                    {!!csrf_field()!!}
                    <input type="hidden" name="item[id]" value="" data-input-form="id" />
                    <input type="hidden" name="item[project_id]" value="{!!$projectId!!}" />
                    <input type="hidden" name="isDelete" value="" />
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h4 class="modal-title" data-flag-dom="modal-pm-title"
                            data-title-edit="{!! trans('project::view.Edit member') !!}"
                            data-title-add="{!! trans('project::view.Add member') !!}"
                        >{!! trans('project::view.Edit member') !!}</h4>
                    </div>
                    <div class="modal-body col-md-12">
                        <div class="form-group row form-group-select2">
                            <label for="pm-position" class="control-label required col-md-3">{{ trans('project::view.Position') }}<em>*</em></label>
                            <div class="col-md-9 fg-valid-custom">
                                <select name="item[type]" class="select-search has-search" id="pm-position" data-input-form="type"></select>
                            </div>
                        </div>

                        <div class="form-group form-group-select2 row">
                            <label for="field-account" class="control-label required col-md-3">{{ trans('project::view.Account') }}<em>*</em></label>
                            <div class="col-md-9 fg-valid-custom">
                                <select name="item[employee_id]" id="field-account" data-input-form="employee_id"
                                        data-remote-url="{{ URL::route('team::employee.list.search.ajax', ['type' => 1, 'fullName' => 1, 'projectId' => $project->id, 'get-leaver' => 'true']) }}" class="select-search"></select>
                            </div>
                        </div>

                        <div class="form-group form-group-select2 row">
                            <label for="field-account" class="control-label col-md-3" title="{!!trans('project::view.Programming language')!!}">{{ trans('project::view.PL') }}</label>
                            <div class="col-md-9 fg-valid-custom">
                                <select name="item[prog_langs][]" id="field-pl"
                                        multiple="multiple" class="select-search has-search" data-select2-trim="0"
                                        data-input-form="program"></select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="field-start_at" class="control-label col-md-3 required">{{ trans('project::view.Start date') }}<em>*</em></label>
                            <div class="col-md-9">
                                <input type="text" class="form-control" name="item[start_at]" data-input-form="start_at"
                                       data-flag-dom="datetime-picker" value="" placeholder="YY-MM-DD" id="field-start_at" />
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="field-end_at" class="control-label col-md-3 required">{{ trans('project::view.End date') }}<em>*</em></label>
                            <div class="col-md-9">
                                <input type="text" class="form-control" name="item[end_at]" data-input-form="end_at"
                                       data-flag-dom="datetime-picker"   value="" placeholder="YY-MM-DD" id="field-end_at" />
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="field-effort" class="control-label col-md-3 required">{{ trans('project::view.Effort') }}(%)<em>*</em></label>
                            <div class="col-md-9">
                                <input type="text" class="form-control" name="item[effort]" data-input-form="effort"
                                       value="" placeholder="%" id="field-effort" />
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="field-actual_effort" class="control-label col-md-3">{{ trans('project::view.Actual Effort') }}(<span data-proj-label="label-type"></span>)</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control" disabled readonly data-input-form="flat_resource"
                                       value="" />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="field-actual_effort" class="control-label col-md-3">{{ trans('project::view.Current Skill') }}</label>
                            <div class="col-md-9">
                                <select class="js-example-matcher-start  current-skill-box" multiple="multiple" id="currentSkills">
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <div class="text-center">
                            <button type="submit" class="btn-add margin-right-10" data-flag-dom="btnSaveProjMember">{{ trans('project::view.Save') }}</button>
                            <button type="submit" class="btn btn-danger" data-flag-dom="btnDeleteProjMember">{{ trans('project::view.Delete') }}</button>
                            <button type="submit" class="btn btn-danger hidden" data-flag-dom="btnDeleteProjMember" data-flag-del="revert">{{ trans('project::view.Cancel delete') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
