<?php
    use Illuminate\Support\Facades\Auth;
    use Rikkei\ManageTime\Model\LeaveDayRegister;
    use Rikkei\ManageTime\View\LeaveDayPermission;

    $userCurrent = \Rikkei\Team\View\Permission::getInstance()->getEmployee();
    $statusUnapprove = LeaveDayRegister::STATUS_UNAPPROVE;
    $statusApproved = LeaveDayRegister::STATUS_APPROVED;
    $statusDisapprove = LeaveDayRegister::STATUS_DISAPPROVE;

    $isScopeApproveOfSelf = LeaveDayPermission::isScopeApproveOfSelf();
    $isScopeApproveOfTeam = LeaveDayPermission::isScopeApproveOfTeam();
    $isScopeApproveOfCompany = LeaveDayPermission::isScopeApproveOfCompany();

    $isScopeAcquisitionOfSelf = LeaveDayPermission::isScopeAcquisitionOfSelf();
    $isScopeAcquisitionOfTeam = LeaveDayPermission::isScopeAcquisitionOfTeam();
    $isScopeAcquisitionOfCompany = LeaveDayPermission::isScopeAcquisitionOfCompany();

    $countRegistersCreatedBy = LeaveDayRegister::countRegistersCreatedBy($userCurrent->id);
    $countRegistersApprovedBy = LeaveDayRegister::countRegistersApprovedBy($userCurrent->id);

    // Calculate applicants related
    $countRegistersRelated = LeaveDayRegister::countRegistersRelated($userCurrent->id);
    $countRegistersRelatedUnapprove = isset($countRegistersRelated[$statusUnapprove]) ? count($countRegistersRelated[$statusUnapprove]) : 0;
    $countRegistersRelatedApproved = isset($countRegistersRelated[$statusApproved]) ? count($countRegistersRelated[$statusApproved]) : 0;
    $countRegistersRelatedDisapprove = isset($countRegistersRelated[$statusDisapprove]) ? count($countRegistersRelated[$statusDisapprove]) : 0;
    $countRegistersRelatedAll = $countRegistersRelatedUnapprove + $countRegistersRelatedApproved + $countRegistersRelatedDisapprove;
?>

<!-- Box self -->
<div class="box box-solid" id="box_register">
    <div class="box-header with-border">
        <div class="pull-left managetime-menu-title">
            <h3 class="box-title">{{ trans('manage_time::view.My leave day') }}</h3>
        </div>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="box-body no-padding">
        <ul class="nav nav-pills nav-stacked managetime-menu">
            <li>
                <a href="{{ route('manage_time::profile.leave.register') }}">
                    <i class="fa fa-share-square-o"></i> {{ trans('manage_time::view.Register') }}
                </a>
            </li>
            <li>
                <a href="{{ route('manage_time::profile.leave.register-list') }}">
                    <i class="fa fa-inbox"></i> {{ trans('manage_time::view.All') }}
                    @if(count($countRegistersCreatedBy))
                        <span class="label bg-aqua pull-right">{{ $countRegistersCreatedBy->all_register }}</span>
                    @endif
                </a>
            </li>
            <li>
                <a href="{{ route('manage_time::profile.leave.register-list', ['status' => $statusUnapprove]) }}">
                    <i class="fa fa-hourglass-half"></i> {{ trans('manage_time::view.Unapprove') }}
                    @if(count($countRegistersCreatedBy))
                            <span class="label bg-yellow pull-right">{{ $countRegistersCreatedBy->status_unapprove }}</span>
                    @endif
                </a>
            </li>
            <li>
                <a href="{{ route('manage_time::profile.leave.register-list', ['status' => $statusApproved]) }}">
                    <i class="fa fa-check"></i> {{ trans('manage_time::view.Approved') }}
                    @if(count($countRegistersCreatedBy))
                            <span class="label bg-green pull-right">{{ $countRegistersCreatedBy->status_approved }}</span>
                    @endif
                </a>
            </li>
            <li>
                <a href="{{ route('manage_time::profile.leave.register-list', ['status' => $statusDisapprove]) }}">
                    <i class="fa fa-calendar-times-o"></i> {{ trans('manage_time::view.Disapprove') }}
                    @if(count($countRegistersCreatedBy))
                            <span class="label bg-red pull-right">{{ $countRegistersCreatedBy->status_disapprove }}</span>
                    @endif
                </a>
            </li>
        </ul>
    </div>
</div>
<!-- /. box -->

<!-- Box approver -->
@if($isScopeApproveOfSelf || $isScopeApproveOfTeam || $isScopeApproveOfCompany)
    <div class="box box-solid" id="box_approve">
        <div class="box-header with-border">
            <div class="pull-left managetime-menu-title">
                <h3 class="box-title">{{ trans('manage_time::view.I approves leave day register') }}</h3>
            </div>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body no-padding">
            <ul class="nav nav-pills nav-stacked managetime-menu">
                <li>
                    <a href="{{ route('manage_time::profile.leave.approve-list') }}">
                        <i class="fa fa-inbox"></i> {{ trans('manage_time::view.All') }}
                        @if(count($countRegistersApprovedBy))
                                <span class="label bg-aqua pull-right">{{ $countRegistersApprovedBy->all_register }}</span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('manage_time::profile.leave.approve-list', ['status' => $statusUnapprove]) }}">
                        <i class="fa fa-hourglass-half"></i> {{ trans('manage_time::view.Unapprove') }}
                        @if(count($countRegistersApprovedBy))
                                <span class="label bg-yellow pull-right">{{ $countRegistersApprovedBy->status_unapprove }}</span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('manage_time::profile.leave.approve-list', ['status' => $statusApproved]) }}">
                        <i class="fa fa-check"></i> {{ trans('manage_time::view.Approved') }}
                        @if(count($countRegistersApprovedBy))
                                <span class="label bg-green pull-right">{{ $countRegistersApprovedBy->status_approved }}</span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('manage_time::profile.leave.approve-list', ['status' => $statusDisapprove]) }}">
                        <i class="fa fa-calendar-times-o"></i> {{ trans('manage_time::view.Disapprove') }}
                        @if(count($countRegistersApprovedBy))
                                <span class="label bg-red pull-right">{{ $countRegistersApprovedBy->status_disapprove }}</span>
                        @endif
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <!-- /. box -->
@endif

<!-- Box relater -->
<div class="box box-solid" id="box_register">
    <div class="box-header with-border">
        <div class="pull-left managetime-menu-title">
            <h3 class="box-title">{{ trans('manage_time::view.An application for leave relates to me') }}</h3>
        </div>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="box-body no-padding">
        <ul class="nav nav-pills nav-stacked managetime-menu">
            <li>
                <a href="{{ route('manage_time::profile.leave.related-list') }}">
                    <i class="fa fa-inbox"></i> {{ trans('manage_time::view.All') }}
                    <span class="label bg-aqua pull-right">{{ $countRegistersRelatedAll }}</span>
                </a>
            </li>
            <li>
                <a href="{{ route('manage_time::profile.leave.related-list', ['status' => $statusUnapprove]) }}">
                    <i class="fa fa-hourglass-half"></i> {{ trans('manage_time::view.Unapprove') }}
                    <span class="label bg-yellow pull-right">{{ $countRegistersRelatedUnapprove }}</span>
                </a>
            </li>
            <li>
                <a href="{{ route('manage_time::profile.leave.related-list', ['status' => $statusApproved]) }}">
                    <i class="fa fa-check"></i> {{ trans('manage_time::view.Approved') }}
                    <span class="label bg-green pull-right">{{ $countRegistersRelatedApproved }}</span>
                </a>
            </li>
            <li>
                <a href="{{ route('manage_time::profile.leave.related-list', ['status' => $statusDisapprove]) }}">
                    <i class="fa fa-calendar-times-o"></i> {{ trans('manage_time::view.Disapprove') }}
                    <span class="label bg-red pull-right">{{ $countRegistersRelatedDisapprove }}</span>
                </a>
            </li>
        </ul>
    </div>
</div>
<!-- /. box -->

<!-- Box relater -->
@if($isScopeAcquisitionOfSelf || $isScopeAcquisitionOfTeam || $isScopeAcquisitionOfCompany)
<div class="box box-solid" id="box_register">
    <div class="box-header with-border">
        <div class="pull-left managetime-menu-title">
            <h3 class="box-title">{{ trans('manage_time::view.Annual paid leave management book') }}</h3>
        </div>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="box-body no-padding">
        <ul class="nav nav-pills nav-stacked managetime-menu">
            <li>
                <a href="{{ route('manage_time::profile.leave.acquisition-status') }}">
                    <i class="fa fa-inbox"></i> {{ trans('manage_time::view.All') }}
                </a>
            </li>
        </ul>
    </div>
</div>
@endif
<!-- /. box -->
