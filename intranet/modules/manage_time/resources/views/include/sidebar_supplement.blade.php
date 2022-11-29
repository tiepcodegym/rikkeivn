<?php
    use Illuminate\Support\Facades\Auth;
    use Rikkei\ManageTime\Model\SupplementRegister;
    use Rikkei\ManageTime\View\SupplementPermission;

    $userCurrent = \Rikkei\Team\View\Permission::getInstance()->getEmployee();

    $isScopeApproveOfSelf = SupplementPermission::isScopeApproveOfSelf();
    $isScopeApproveOfTeam = SupplementPermission::isScopeApproveOfTeam();
    $isScopeApproveOfCompany = SupplementPermission::isScopeApproveOfCompany();

    $statusUnapprove = SupplementRegister::STATUS_UNAPPROVE;
    $statusApproved = SupplementRegister::STATUS_APPROVED;
    $statusDisapprove = SupplementRegister::STATUS_DISAPPROVE;

    $countRegistersCreatedBy = SupplementRegister::countRegistersCreatedBy($userCurrent->id);
    $countRegistersCreatedByUnapprove = isset($countRegistersCreatedBy[$statusUnapprove]) ? count($countRegistersCreatedBy[$statusUnapprove]) : 0;
    $countRegistersCreatedByApproved = isset($countRegistersCreatedBy[$statusApproved]) ? count($countRegistersCreatedBy[$statusApproved]) : 0;
    $countRegistersCreatedByDisapprove = isset($countRegistersCreatedBy[$statusDisapprove]) ? count($countRegistersCreatedBy[$statusDisapprove]) : 0;
    $countRegistersCreatedByAll = $countRegistersCreatedByUnapprove + $countRegistersCreatedByApproved + $countRegistersCreatedByDisapprove;

    $countRegistersApprovedBy = SupplementRegister::countRegistersApprovedBy($userCurrent->id);
    $countRegistersRelaterBy = SupplementRegister::countRegistersRelatesBy($userCurrent->id);
?>

<div class="box box-solid" id="box_register">
    <div class="box-header with-border">
        <div class="pull-left managetime-menu-title">
            <h3 class="box-title">{{ trans('manage_time::view.My supplement') }}</h3>
        </div>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="box-body no-padding">
        <ul class="nav nav-pills nav-stacked managetime-menu">
            <li>
                <a href="{{ route('manage_time::profile.supplement.register') }}">
                    <i class="fa fa-share-square-o"></i> {{ trans('manage_time::view.Register') }}
                </a>
            </li>
            <li>
                <a href="{{ route('manage_time::profile.supplement.register-list') }}">
                    <i class="fa fa-inbox"></i> {{ trans('manage_time::view.All') }}
                    <span class="label bg-aqua pull-right">{{ $countRegistersCreatedByAll }}</span>
                </a>
            </li>
            <li>
                <a href="{{ route('manage_time::profile.supplement.register-list', ['status' => $statusUnapprove]) }}">
                    <i class="fa fa-hourglass-half"></i> {{ trans('manage_time::view.Unapprove') }}
                    <span class="label bg-yellow pull-right">{{ $countRegistersCreatedByUnapprove }}</span>
                </a>
            </li>
            <li>
                <a href="{{ route('manage_time::profile.supplement.register-list', ['status' => $statusApproved]) }}">
                    <i class="fa fa-check"></i> {{ trans('manage_time::view.Approved') }}
                    <span class="label bg-green pull-right">{{ $countRegistersCreatedByApproved }}</span>
                </a>
            </li>
            <li>
                <a href="{{ route('manage_time::profile.supplement.register-list', ['status' => $statusDisapprove]) }}">
                    <i class="fa fa-calendar-times-o"></i> {{ trans('manage_time::view.Disapprove') }}
                    <span class="label bg-red pull-right">{{ $countRegistersCreatedByDisapprove }}</span>
                </a>
            </li>
        </ul>
    </div>
</div>
<!-- /. box -->

@if($isScopeApproveOfSelf || $isScopeApproveOfTeam || $isScopeApproveOfCompany)
    <div class="box box-solid" id="box_approve">
        <div class="box-header with-border">
            <div class="pull-left managetime-menu-title">
                <h3 class="box-title">{{ trans('manage_time::view.I approves supplement register') }}</h3>
            </div>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body no-padding">
            <ul class="nav nav-pills nav-stacked managetime-menu">
                <li>
                    <a href="{{ route('manage_time::profile.supplement.approve-list') }}">
                        <i class="fa fa-inbox"></i> {{ trans('manage_time::view.All') }}
                        @if(count($countRegistersApprovedBy))
                                <span class="label bg-aqua pull-right">{{ $countRegistersApprovedBy->all_register }}</span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('manage_time::profile.supplement.approve-list', ['status' => $statusUnapprove]) }}">
                        <i class="fa fa-hourglass-half"></i> {{ trans('manage_time::view.Unapprove') }}
                        @if(count($countRegistersApprovedBy))
                                <span class="label bg-yellow pull-right">{{ $countRegistersApprovedBy->status_unapprove }}</span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('manage_time::profile.supplement.approve-list', ['status' => $statusApproved]) }}">
                        <i class="fa fa-check"></i> {{ trans('manage_time::view.Approved') }}
                        @if(count($countRegistersApprovedBy))
                                <span class="label bg-green pull-right">{{ $countRegistersApprovedBy->status_approved }}</span>

                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('manage_time::profile.supplement.approve-list', ['status' => $statusDisapprove]) }}">
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
<div class="box box-solid" id="box_approve">
    <div class="box-header with-border">
        <div class="pull-left managetime-menu-title">
            <h3 class="box-title">{{ trans('manage_time::view.Supplement relates to me') }}</h3>
        </div>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="box-body no-padding">
        <ul class="nav nav-pills nav-stacked managetime-menu">
            <li>
                <a href="{{ route('manage_time::profile.supplement.relates-list') }}">
                    <i class="fa fa-inbox"></i> {{ trans('manage_time::view.All') }}
                    @if(count($countRegistersRelaterBy))
                            <span class="label bg-aqua pull-right">{{ $countRegistersRelaterBy->all_register }}</span>
                    @endif
                </a>
            </li>
            <li>
                <a href="{{ route('manage_time::profile.supplement.relates-list', ['status' => $statusUnapprove]) }}">
                    <i class="fa fa-hourglass-half"></i> {{ trans('manage_time::view.Unapprove') }}
                    @if(count($countRegistersRelaterBy))
                            <span class="label bg-yellow pull-right">{{ $countRegistersRelaterBy->status_unapprove }}</span>
                    @endif
                </a>
            </li>
            <li>
                <a href="{{ route('manage_time::profile.supplement.relates-list', ['status' => $statusApproved]) }}">
                    <i class="fa fa-check"></i> {{ trans('manage_time::view.Approved') }}
                    @if(count($countRegistersRelaterBy))
                            <span class="label bg-green pull-right">{{ $countRegistersRelaterBy->status_approved }}</span>
                    @endif
                </a>
            </li>
            <li>
                <a href="{{ route('manage_time::profile.supplement.relates-list', ['status' => $statusDisapprove]) }}">
                    <i class="fa fa-calendar-times-o"></i> {{ trans('manage_time::view.Disapprove') }}
                    @if(count($countRegistersRelaterBy))
                            <span class="label bg-red pull-right">{{ $countRegistersRelaterBy->status_disapprove }}</span>
                    @endif
                </a>
            </li>
        </ul>
    </div>
</div>
