<?php
    use Rikkei\Team\View\Config as TeamConfig;
    use Rikkei\Core\View\View as CoreView;
    use Rikkei\Core\View\Form as CoreForm;
    use Carbon\Carbon;
    use Rikkei\Ot\Model\OtRegister;

    $registerTable = OtRegister::getTableName();
    $employeeApproveTable = 'employee_table_for_approver';
    $employeeCreateTable = 'employee_table_for_creator';
    $statusUnapprove = OtRegister::WAIT;
    $statusApproved = OtRegister::DONE;
    $statusDisapprove = OtRegister::REJECT;
    $statusCancel = OtRegister::REMOVE;
    $userCurrent = \Rikkei\Team\View\Permission::getInstance()->getEmployee();
?>

<div class="box-header filter-mobile-left">
    <div class="filter-panel-left pager-filter">
        <h3 class="box-title ot-box-title">{{ trans('ot::view.OT register list') }}</h3>
    </div>
    @include('ot::include.filter')
</div>
<!-- /.box-header -->

<div class="box-body no-padding">
    <div class="table-responsive register-list-wrapper">
        <table class="table dataTable table-striped table-grid-data table-responsive table-hover table-bordered list-ot-table">
            <thead class="list-head">
                <tr>
                    @if ($empType != OtRegister::REGISTER)
                        <th class="col-check-all"><input id="check_all" type="checkbox"></th>
                    @endif
                    <th class="col-width-25">{{ trans('ot::view.No') }}</th>
                    <th class="col-width-75 sorting col-title {{ TeamConfig::getDirClass('creator_name') }}" data-order="creator_name" data-dir="{{ TeamConfig::getDirOrder('creator_name') }}">{{ trans('ot::view.Applicant') }}</th>
                    <th class="col-width-60 sorting col-title {{ TeamConfig::getDirClass('start_at') }}" data-order="start_at" data-dir="{{ TeamConfig::getDirOrder('start_at') }}">{{ trans('ot::view.OT from') }}</th>
                    <th class="col-width-60 sorting col-title {{ TeamConfig::getDirClass('end_at') }}" data-order="end_at" data-dir="{{ TeamConfig::getDirOrder('end_at') }}">{{ trans('ot::view.OT to') }}</th>
                    <th class="col-width-60 sorting col-title {{ TeamConfig::getDirClass('approved_at') }}" data-order="approved_at" data-dir="{{ TeamConfig::getDirOrder('approved_at') }}">{{ trans('ot::view.Approval time') }}</th>
                    <th class="col-width-40 th-time-break">{{ trans('ot::view.Total break time (h)') }}</th>
                    <th class="col-width-100 sorting col-title {{ TeamConfig::getDirClass('reason') }}" data-order="reason" data-dir="{{ TeamConfig::getDirOrder('reason') }}" >{{ trans('ot::view.OT reason') }}</th>
                    @if ($empType == OtRegister::REGISTER)
                        <th class="col-width-75 sorting col-title {{ TeamConfig::getDirClass('approver_name') }}" data-order="approver_name" data-dir="{{ TeamConfig::getDirOrder('approver_name') }}">{{ trans('ot::view.Approver') }}</th>
                    @endif
                    @if(isset($status))
                        <th class="col-width-100 col-title">{{ trans('manage_time::view.Status') }}</th>
                    @else
                        <th class="col-width-100 col-title sorting {{ TeamConfig::getDirClass('status') }}" data-order="status" data-dir="{{ TeamConfig::getDirOrder('status') }}">{{ trans('manage_time::view.Status') }}</th>
                    @endif
                    @if ($empType != OtRegister::REGISTER)
                        <th class="col-width-40 th-btn-manage"></th>
                    @else
                        <th class="col-width-85 th-btn-manage"></th>
                    @endif
                </tr>                
            </thead>
            <tbody>
                <tr>
                    @if ($empType != OtRegister::REGISTER)
                        <td></td>
                    @endif
                    <td></td>
                    <td>
                        <div class="row">
                            <div class="col-md-12">
                                <input type="text" name="filter[{{ $employeeCreateTable }}.name]" value='{{ CoreForm::getFilterData("{$employeeCreateTable}.name") }}' placeholder="{{ trans('ot::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="row">
                            <div class="col-md-12">
                                <input type="text" name="filter[except][start_at]" value='{{ CoreForm::getFilterData("except", "start_at") }}' placeholder="{{ trans('ot::view.Search') }}..." class="filter-grid form-control filter-date" autocomplete="off" />
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="row">
                            <div class="col-md-12">
                                <input type="text" name="filter[except][end_at]" value='{{ CoreForm::getFilterData("except", "end_at") }}' placeholder="{{ trans('ot::view.Search') }}..." class="filter-grid form-control filter-date" autocomplete="off" />
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="row">
                            <div class="col-md-12">
                                <input type="text" name="filter[except][approved_at]" value='{{ CoreForm::getFilterData("except", "approved_at") }}' placeholder="{{ trans('ot::view.Search') }}..." class="filter-grid form-control filter-date" autocomplete="off" />
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="row">
                            <div class="col-md-12">
                                <?php
                                    $timeBreakFilter = CoreForm::getFilterData('except', "$registerTable.time_break");
                                ?>
                                <input type="text" name="filter[except][{{ $registerTable }}.time_break]" value='{{ $timeBreakFilter }}' placeholder="{{ trans('ot::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="row">
                            <div class="col-md-12">
                                <input type="text" name="filter[reason]" value='{{ CoreForm::getFilterData("reason") }}' placeholder="{{ trans('ot::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                            </div>
                        </div>
                    </td>
                    @if ($empType == OtRegister::REGISTER)
                        <td>
                            <div class="row">
                                <div class="col-md-12">
                                    <input type="text" name="filter[{{ $employeeApproveTable }}.name]" value='{{ CoreForm::getFilterData("{$employeeApproveTable}.name") }}' placeholder="{{ trans('ot::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                </div>
                            </div>
                        </td>
                    @endif
                    @if(empty($pageType))
                        <td style="min-width: 100px; max-width: 100px;">
                            <div class="row">
                                <div class="col-md-12">
                                    <?php
                                        $filterStatus = CoreForm::getFilterData('number', "ot_registers.status");
                                    ?>
                                    <select name="filter[number][ot_registers.status]" class="form-control select-grid filter-grid select-search" autocomplete="off" style="width: 100%;">
                                        <option value="">&nbsp;</option>
                                        <option value="{{ $statusUnapprove }}" <?php
                                            if ($filterStatus == $statusUnapprove): ?> selected<?php endif; 
                                                ?>>{{ trans('manage_time::view.Unapprove') }}
                                        </option>
                                        <option value="{{ $statusApproved }}" <?php
                                            if ($filterStatus == $statusApproved): ?> selected<?php endif; 
                                                ?>>{{ trans('manage_time::view.Approved') }}
                                        </option>
                                        <option value="{{ $statusDisapprove }}" <?php
                                            if ($filterStatus == $statusDisapprove): ?> selected<?php endif; 
                                                ?>>{{ trans('manage_time::view.Disapprove') }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </td>
                    @else
                        <td>&nbsp;</td>
                    @endif
                    <td></td>
                </tr>
                @if (isset($collectionModel) && count($collectionModel))
                    <?php $i = CoreView::getNoStartGrid($collectionModel); ?>
                    @foreach ($collectionModel as $reg)
                        <?php 
                            $tdClass = '';
                            $status = '';
                            if ($reg->status == $statusApproved) {
                                $tdClass = 'ot-approved';
                                $status = trans('ot::view.Approved Label');
                            } elseif ($reg->status == $statusDisapprove) {
                                $tdClass = 'ot-disapprove';
                                $status = trans('ot::view.Rejected Label');
                            } else {
                                $tdClass = 'ot-unapprove';
                                $status = trans('ot::view.Unapproved Label');
                            }
                        ?>
                        <tr>
                            <?php
                                $totalBreakTime = $reg->time_break;
                                $startAt = $reg->start_at;
                                $endAt = $reg->end_at;
                                $approvedAt = $reg->approved_at;
                                if ($empType == OtRegister::REGISTER && $reg->emp_employee_id == $userCurrent->id) {
                                    $totalBreakTime = $reg->emp_time_break;
                                    $startAt = $reg->emp_start_at;
                                    $endAt = $reg->emp_end_at;
                                }
                            ?>
                            @if ($empType != OtRegister::REGISTER)
                                <td class="col-check-all"><input class="check_select" type="checkbox" value="{{ $reg->id }}"></td>
                            @endif
                            <td class="{{ $tdClass }}">{{ $i }}</td>
                            <td class="{{ $tdClass }}">{{ $reg->creator_name }}</td>
                            <td class="{{ $tdClass }}">{{ Carbon::createFromFormat('Y-m-d H:i:s', $startAt)->format('d-m-Y H:i') }}</td>
                            <td class="{{ $tdClass }}">{{ Carbon::createFromFormat('Y-m-d H:i:s', $endAt)->format('d-m-Y H:i') }}</td>
                            @if (isset($approvedAt))
                                <td class="{{ $tdClass }}">{{ Carbon::createFromFormat('Y-m-d H:i:s', $approvedAt)->format('d-m-Y H:i') }}</td>
                            @else
                                <td class="{{ $tdClass }}"></td>
                            @endif
                            <td class="{{ $tdClass }}">{{ $totalBreakTime }}</td>
                            <td class="{{ $tdClass }} ot-read-more">{!! CoreView::nl2br($reg->reason) !!}</td>
                            @if ($empType == OtRegister::REGISTER)
                                <td class="{{ $tdClass }}">{{ $reg->approver_name }}</td>
                            @endif
                            <td class="{{ $tdClass }}">{{ $status }}</td>
                            <td class="list-btn-manage">
                                @if($reg->status == $statusApproved || $empType != OtRegister::REGISTER || ($empType == OtRegister::REGISTER && $reg->creator_id != $userCurrent->id))
                                    <a class="btn btn-success" title="View detail" href="{{ route('ot::ot.detail', ['id' => $reg->id]) }}" >
                                        <i class="fa fa-info-circle"></i>
                                    </a>
                                @endif
                                
                                @if ($empType == OtRegister::REGISTER)                                        
                                    @if ($reg->status != OtRegister::DONE && $reg->creator_id == $userCurrent->id)
                                        <a class="btn btn-success" title="{{ trans('manage_time::view.Edit') }}" href="{{ route('ot::ot.editot', ['id' => $reg->id]) }}" >
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger" title="{{ trans('manage_time::view.Delete') }}" onclick="setDeleteId({{ $reg->id }})" data-toggle="modal" data-target="#delete_warning"><i class="fa fa-trash-o"></i></button>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        <?php $i++; ?>
                    @endforeach
                @else
                    <tr>
                        <td colspan="9" class="text-center">
                            <h2 class="no-result-grid">{{ trans('ot::view.No results found') }}</h2>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>  
        <!-- /.table -->
    </div>
    <!-- /.table-responsive -->
</div>
<!-- /.box-body -->

<div class="box-footer no-padding">
    <div class="mailbox-controls">   
        @include('team::include.pager')
    </div>
</div>
