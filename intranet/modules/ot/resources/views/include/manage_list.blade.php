<?php
    use Rikkei\Ot\Model\OtRegister;
    use Rikkei\Team\View\Config as TeamConfig;
    use Rikkei\Core\View\View as CoreView;
    use Rikkei\Core\View\Form as CoreForm;
    use Carbon\Carbon;
    use Rikkei\Ot\View\OtPermission;
    use Rikkei\ManageTime\View\ManageTimeCommon;

    $teamsOptionAll = \Rikkei\Team\View\TeamList::toOption(null, true, false);
    $registerTable = OtRegister::getTableName();
    $allowCreateEditOther = OtPermission::allowCreateEditOther();
    $colspan = 12;
    if ($allowCreateEditOther) {
        $colspan = 13;
    }

    if (isset($reportOT)) {
        $urlTeam = 'ot::ot.manage.report_manage_ot';
    } else {
        $urlTeam = 'ot::profile.manage.ot';
    }
?>

<!-- Box manage list -->
<div class="box box-primary" id="mission_manage_list">
    <div class="box-header">
        <div class="team-select-box">
            @if (is_object($teamIdsAvailable))
                <p>
                    <b>Team:</b>
                    <span>{{ $teamIdsAvailable->name }}</span>
                </p>
            @elseif ($teamIdsAvailable || ($teamTreeAvailable && count($teamTreeAvailable)))
                <label for="select-team-member">{{ trans('team::view.Choose team') }}</label>
                <div class="input-box">
                    <select name="team_all" id="select-team-member"
                        class="form-control select-search input-select-team-member"
                        autocomplete="off" style="width: 100%;">
                        
                        @if ($teamIdsAvailable === true)
                            <option value="{{ URL::route($urlTeam) }}"<?php
                                    if (! $teamIdCurrent): ?> selected<?php endif; 
                                    ?><?php
                                    if ($teamIdsAvailable !== true): ?> disabled<?php endif;
                                    ?>>&nbsp;</option>
                        @endif
                        
                        @if ($teamIdsAvailable === true || (count($teamsOptionAll) && $teamTreeAvailable))
                            @foreach($teamsOptionAll as $option)
                                @if ($teamIdsAvailable === true || in_array($option['value'], $teamTreeAvailable))
                                    <option value="{{ URL::route($urlTeam, ['team' => $option['value']]) }}"<?php
                                        if ($option['value'] == $teamIdCurrent): ?> selected<?php endif; 
                                            ?><?php
                                        if ($teamIdsAvailable === true):
                                        elseif (! in_array($option['value'], $teamIdsAvailable)): ?> disabled<?php else:
                                        ?>{{ $option['option'] }}<?php endif; ?>>{{ $option['label'] }}</option>
                                @endif
                            @endforeach
                        @endif
                    </select>
                </div>
            @endif
        </div>
        <div class="pull-right">
            <div class="filter-action">
                @if ($allowCreateEditOther && !isset($reportOT))
                    <a class="btn btn-success managetime-margin-bottom-5" href="{{ route('ot::ot.admin-register') }}" target="_blank">
                        <span><i class="fa fa-plus"></i> {{ trans('manage_time::view.Register') }}</span>
                    </a>
                @endif
                <button class="btn btn-primary btn-reset-filter managetime-margin-bottom-5">
                    <span>{{ trans('manage_time::view.Reset filter') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
                </button>
                <button class="btn btn-primary btn-search-filter managetime-margin-bottom-5">
                    <span>{{ trans('manage_time::view.Search') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
                </button>
            </div>
        </div>
    </div>
    <!-- /.box-header -->

    <div class="box-body no-padding">
        <div class="table-responsive">
            <table class="table dataTable table-striped table-grid-data table-responsive table-hover table-bordered manage-ot-table list-ot-table" id="manage_ot_list">
                <thead class="manage-ot-thead list-head">
                    <tr>
                        <th class="col-width-25">{{ trans('ot::view.No') }}</th>
                        <th class="col-width-60 sorting {{ TeamConfig::getDirClass('emp.employee_code') }} col-employee-code" data-order="emp.employee_code" data-dir="{{ TeamConfig::getDirOrder('emp.employee_code') }}">{{ trans('ot::view.Employee code') }}</th>
                        <th class="col-width-80 sorting {{ TeamConfig::getDirClass('emp.name') }} col-emp-reg-name" data-order="emp.name" data-dir="{{ TeamConfig::getDirOrder('emp.name') }}">{{ trans('ot::view.Employee Name') }}</th>
                        <th class="col-width-120 sorting {{ TeamConfig::getDirClass('teamp_emp') }} col-role-name" data-order="teamp_emp" data-dir="{{ TeamConfig::getDirOrder('teamp_emp') }}">{{ trans('ot::view.Position') }}</th>
                        <th class="col-width-120 sorting {{ TeamConfig::getDirClass('project.name') }} col-project-name" data-order="project.name" data-dir="{{ TeamConfig::getDirOrder('project.name') }}">{{ trans('ot::view.Project Name') }}</th>
                        <th class="col-width-60 sorting {{ TeamConfig::getDirClass('ot_employees.start_at') }} col-start-at" data-order="ot_employees.start_at" data-dir="{{ TeamConfig::getDirOrder('ot_employees.start_at') }}">{{ trans('ot::view.OT from') }}</th>
                        <th class="col-width-60 sorting {{ TeamConfig::getDirClass('ot_employees.end_at') }} col-end-at" data-order="ot_employees.end_at" data-dir="{{ TeamConfig::getDirOrder('ot_employees.end_at') }}">{{ trans('ot::view.OT to') }}</th>
                        <th class="col-width-60 sorting {{ TeamConfig::getDirClass('ot_reg.created_at') }} col-created-at" data-order="ot_reg.created_at" data-dir="{{ TeamConfig::getDirOrder('ot_reg.created_at') }}">{{ trans('ot::view.Register Date') }}</th>
                        <th class="col-width-60 sorting {{ TeamConfig::getDirClass('ot_reg.approved_at') }} col-created-at" data-order="ot_reg.approved_at" data-dir="{{ TeamConfig::getDirOrder('ot_reg.approved_at') }}">{{ trans('ot::view.Approval time') }}</th>
                        <th class="col-width-40 sorting {{ TeamConfig::getDirClass('ot_employees.time_break') }} col-time-break" data-order="ot_employees.time_break" data-dir="{{ TeamConfig::getDirOrder('ot_employees.time_break') }}">{{ trans('ot::view.Total break time (h)') }}</th>
                        <th class="col-width-80 sorting {{ TeamConfig::getDirClass('emp_app.id') }} col-emp-app-name" data-order="emp_app.id" data-dir="{{ TeamConfig::getDirOrder('emp_app.id') }}">{{ trans('ot::view.Approver') }}</th>
                        @if (!isset($reportOT))
                        <th class="col-width-120 sorting {{ TeamConfig::getDirClass('ot_reg.status') }} col-status" data-order="ot_reg.status" data-dir="{{ TeamConfig::getDirOrder('ot_reg.status') }}">{{ trans('ot::view.Status') }}</th>
                        @if ($allowCreateEditOther)
                            <th class="col-width-85"></th>
                        @endif
                        @endif
                    </tr>
                </thead>
                <tbody id="position_start_header_fixed">
                    <tr>
                        <td>&nbsp;</td>

                        <td>
                            <div class="row">
                                <div class="col-md-12">
                                    <input type="text" name="filter[emp.employee_code]" value="{{ CoreForm::getFilterData("emp.employee_code") }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="row">
                                <div class="col-md-12">
                                    <input type="text" name="filter[emp.name]" value="{{ CoreForm::getFilterData("emp.name") }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                </div>
                            </div>
                        </td>
                        
                        <td>&nbsp;</td>

                        <td>
                            <div class="row">
                                <div class="col-md-12">
                                    <input type="text" name="filter[project.name]" value="{{ CoreForm::getFilterData("project.name") }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="row">
                                <div class="col-md-12">
                                    <input type="text" name="filter[except][ot_employees.start_at]" value="{{ CoreForm::getFilterData('except', "ot_employees.start_at") }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control filter-date" autocomplete="off" />
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="row">
                                <div class="col-md-12">
                                    <input type="text" name="filter[except][ot_employees.end_at]" value="{{ CoreForm::getFilterData('except', "ot_employees.end_at") }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control filter-date" autocomplete="off" />
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="row">
                                <div class="col-md-12">
                                    <input type="text" name="filter[except][ot_reg.created_at]" value="{{ CoreForm::getFilterData('except', "ot_reg.created_at") }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control filter-date" autocomplete="off" />
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="row">
                                <div class="col-md-12">
                                    <input type="text" name="filter[except][ot_reg.approved_at]" value="{{ CoreForm::getFilterData('except', "ot_reg.approved_at") }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control filter-date" autocomplete="off" />
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="row">
                                <div class="col-md-12">
                                    <?php
                                        $timeBreakFilter = CoreForm::getFilterData('except', "ot_employees.time_break");
                                    ?>
                                    <input type="text" name="filter[except][ot_employees.time_break]" value='{{ $timeBreakFilter }}' placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="row">
                                <div class="col-md-12">
                                    <input type="text" name="filter[emp_approve.name]" value="{{ CoreForm::getFilterData("emp_approve.name") }}" placeholder="Tìm kiếm..." class="filter-grid form-control"  autocomplete="off" />
                                </div>
                            </div>
                        </td>
                        @if (!isset($reportOT))
                        <td>
                            <div class="row">
                                <div class="col-md-12">
                                    <select class="form-control select-grid filter-grid select-search" name="filter[number][ot_reg.status]" autocomplete="off">
                                        <option value="">&nbsp;</option>
                                        @foreach ($optionStatus as $key => $value)
                                            <option value="{{ $key }}" {{ CoreForm::getFilterData('number', 'ot_reg.status') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </td>
                        @if ($allowCreateEditOther)
                            <td>&nbsp;</td>
                        @endif
                        @endif
                    </tr>

                    @if(isset($collectionModel) && count($collectionModel))
                        <?php 
                            $i = CoreView::getNoStartGrid($collectionModel);
                            $regId = 0;
                            $j = 0;

                        ?>
                        @foreach($collectionModel as $item)
                            <?php 
                                $tdClass = '';
                                $status = '';

                                switch ($item->status) {
                                    case OtRegister::WAIT :
                                        $tdClass = 'text-green';
                                        $status = OtRegister::getStatusLabel(2, OtRegister::WAIT);
                                        break;
                                    case OtRegister::REJECT :
                                        $tdClass = 'text-red';
                                        $status = OtRegister::getStatusLabel(2, OtRegister::REJECT);
                                        break;
                                    case OtRegister::DONE :
                                        $tdClass = 'managetime-approved';
                                        $status = OtRegister::getStatusLabel(2, OtRegister::DONE);
                                        break;
                                    case OtRegister::REMOVE :
                                        $tdClass = 'text-orange';
                                        $status = OtRegister::getStatusLabel(1, OtRegister::REMOVE);
                                        break;
                                }
                                $create = Carbon::createFromFormat('Y-m-d H:i:s', $item->created_at)->format('d-m-Y H:i');
                                $srart = Carbon::createFromFormat('Y-m-d H:i:s', $item->start_at)->format('d-m-Y H:i');
                                $end = Carbon::createFromFormat('Y-m-d H:i:s', $item->end_at)->format('d-m-Y H:i');
                                if (isset($item->approved_at)) {
                                    $approved = Carbon::createFromFormat('Y-m-d H:i:s', $item->approved_at)->format('d-m-Y H:i');
                                } else {
                                    $approved = '';
                                }
                                if ($item->empIdReg == $item->empId ) {
                                    $postion = $item->teamp_reg;
                                } else {
                                    $postion = $item->teamp_emp;
                                }
                            ?>
                                <tr>
                                    @if ($item->idRegister == $regId)
                                        <td class="text-right">{{ $i - 1 }}.{{ ++$j }}</td>
                                        <td>{{ $item->employee_code }}</td>
                                        <td>{{ $item->empName }}</td>
                                        <td>{{ $postion }}</td>
                                        <td></td>
                                        <td></td>
                                        <td>{{ $srart }}</td>
                                        <td>{{ $end }}</td>
                                        <td class="{{ $tdClass }}">{{ $approved }}</td>
                                        <td>{{ $item->time_break }}</td>
                                        <td></td>
                                        <td></td>
                                        @if (!isset($reportOT))
                                        <td></td>
                                        @endif
                                    @else
                                        <?php $j = 0; ?>
                                        <td class="{{ $tdClass }}">{{ $i }}</td>
                                        <td class="{{ $tdClass }} ot-show-popup" data-empregid="{{ $item->idRegister }}">
                                            <a class="{{ $tdClass }}" value="{{ $item->idRegister }}" style="cursor: pointer; color: #0673b3 !important">{{ $item->employee_code }}</a>
                                        </td>
                                        <td class="{{ $tdClass }}">{{ $item->empName }}</td>
                                        <td class="{{ $tdClass }}">{{ $postion }}</td>
                                        <td class="{{ $tdClass }}">{{ $item->nameProject }}</td>
                                        <td class="{{ $tdClass }}">{{ $srart }}</td>
                                        <td class="{{ $tdClass }}">{{ $end }}</td>
                                        <td class="{{ $tdClass }}">{{ $create }}</td>
                                        @if (isset($approved))
                                            <td class="{{ $tdClass }}">{{ $approved }}</td>
                                        @endif
                                        <td class="{{ $tdClass }}">{{ $item->time_break }}</td>
                                        <td class="{{ $tdClass }}" data-empappid="{{ $item->emp_app_id }}">{{ $item->emp_app_name }}</td>
                                        @if (!isset($reportOT))
                                        <td class="{{ $tdClass }}">{{ $status }}</td>
                                        @if ($allowCreateEditOther)
                                            <td>
                                                @if($item->status == OtRegister::DONE || $item->status == OtRegister::REMOVE)
                                                    <a class="btn btn-success" title="View detail" href="{{ route('ot::ot.detail', ['id' => $item->idRegister]) }}" >
                                                        <i class="fa fa-info-circle"></i>
                                                    </a>
                                                @else                                    
                                                    <a class="btn btn-success" title="Edit" href="{{ route('ot::ot.editot', ['id' => $item->idRegister]) }}" >
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-danger" title="Delete" onclick="setDeleteId({{ $item->idRegister }})" data-toggle="modal" data-target="#delete_warning"><i class="fa fa-trash-o"></i></button>
                                                @endif
                                            </td>
                                        @endif
                                        @endif
                                    @endif
                                </tr>
                            <?php 
                                if ($item->idRegister != $regId) {
                                    $i++;
                                }
                                $regId = $item->idRegister;
                            ?>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="{{ $colspan }}" class="text-center">
                                <h2 class="no-result-grid">{{ trans('ot::view.No results found') }}</h2>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
            <!-- /.table -->

            <!-- View modal -->
            <div class="modal fade in ot-modal" id="modal_view">
            </div>
            @include('ot::include.modals.delete_warning')
        </div>
        <!-- /.table-responsive -->
    </div>
    <!-- /.box-body -->

    <div class="box-footer no-padding">
        <div class="mailbox-controls">   
            @include('team::include.pager')
        </div>
    </div>
</div>
<!-- /. box -->
