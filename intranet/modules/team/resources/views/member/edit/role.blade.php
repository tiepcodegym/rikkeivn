<?php
$roleIdsCurrent = [];
?>
<div class="form-horizontal form-label-left">
    <div class="form-group">
        <ul class="employee-roles">
            @if (isset($employeeRoles) && count($employeeRoles))
                @foreach ($employeeRoles as $employeeRole)
                    <li>
                        <span>
                            {{ $employeeRole->role }}
                        </span>
                    </li>
                    <?php 
                        $roleIdsCurrent[] = $employeeRole->role_id;
                     ?>
                @endforeach
            @endif
        </ul>
    </div>
</div>

@if ($permissEditRole)
    <div class="modal fade" id="employee-role-form" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-role-employee" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ trans('team::view.Change Role of employee') }}</h4>
                </div>
                <div class="modal-body">
                    @if (isset($rolesOption) && count($rolesOption))
                        @foreach ($rolesOption as $role)
                            <div class="checkbox">
                                <label>
                                    <input name="role[]" type="checkbox" value="{{ $role->id }}"<?php
                                        if (in_array($role->id, $roleIdsCurrent)): ?> checked<?php endif; ?>>{{ $role->role }} 
                                    @if ($role->description)
                                    <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="right" data-html="true" title="<span>{{ $role->description }}</span>"></i>
                                    @endif
                                </label>
                            </div>
                        @endforeach
                    @endif
                </div>
                <div class="box-footer text-center">
                    <button type="button" class="btn btn-primary" data-dismiss="modal" aria-label="Close">{{ trans('core::view.OK') }}</button>
                </div>
            </div>
        </div>
    </div>
@endif
