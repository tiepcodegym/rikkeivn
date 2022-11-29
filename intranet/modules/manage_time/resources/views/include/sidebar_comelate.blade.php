<?php
    use Illuminate\Support\Facades\Auth;
    use Rikkei\ManageTime\Model\ComeLateRegister;
    use Rikkei\ManageTime\View\ComeLatePermission;

    $userCurrent = \Rikkei\Team\View\Permission::getInstance()->getEmployee();

    $isScopeApproveOfSelf = ComeLatePermission::isScopeApproveOfSelf();
    $isScopeApproveOfTeam = ComeLatePermission::isScopeApproveOfTeam();
    $isScopeApproveOfCompany = ComeLatePermission::isScopeApproveOfCompany();

    $countRegistersCreatedBy = ComeLateRegister::countRegistersCreatedBy($userCurrent->id);
    $countRegistersApprovedBy = ComeLateRegister::countRegistersApprovedBy($userCurrent->id);

    $statusUnapprove = ComeLateRegister::STATUS_UNAPPROVE;
    $statusApproved = ComeLateRegister::STATUS_APPROVED;
    $statusDisapprove = ComeLateRegister::STATUS_DISAPPROVE;
?>

<div class="box box-solid" id="box_register">
    <div class="box-header with-border">
        <div class="pull-left managetime-menu-title">
            <h3 class="box-title">{{ trans('manage_time::view.My late in early out') }}</h3>
        </div>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="box-body no-padding">
        <ul class="nav nav-pills nav-stacked managetime-menu">
            <li>
                <a href="{{ route('manage_time::profile.comelate.register') }}">
                    <i class="fa fa-share-square-o"></i> {{ trans('manage_time::view.Register') }}
                </a>
            </li>
            <li>
                <a href="{{ route('manage_time::profile.comelate.register-list') }}">
                    <i class="fa fa-inbox"></i> {{ trans('manage_time::view.All') }}
                    @if(count($countRegistersCreatedBy))
                        @if($countRegistersCreatedBy->all_register > 0)
                            <span class="label bg-aqua pull-right">{{ $countRegistersCreatedBy->all_register }}</span>
                        @endif
                    @endif
                </a>
            </li>
            <li>
                <a href="{{ route('manage_time::profile.comelate.register-list', ['status' => $statusUnapprove]) }}">
                    <i class="fa fa-hourglass-half"></i> {{ trans('manage_time::view.Unapprove') }}
                    @if(count($countRegistersCreatedBy))
                        @if($countRegistersCreatedBy->status_unapprove > 0)
                            <span class="label bg-green pull-right">{{ $countRegistersCreatedBy->status_unapprove }}</span>
                        @endif
                    @endif
                </a>
            </li>
            <li>
                <a href="{{ route('manage_time::profile.comelate.register-list', ['status' => $statusApproved]) }}">
                    <i class="fa fa-check"></i> {{ trans('manage_time::view.Approved') }}
                    @if(count($countRegistersCreatedBy))
                        @if($countRegistersCreatedBy->status_approved > 0)
                            <span class="label bg-gray-active pull-right">{{ $countRegistersCreatedBy->status_approved }}</span>
                        @endif
                    @endif
                </a>
            </li>
            <li>
                <a href="{{ route('manage_time::profile.comelate.register-list', ['status' => $statusDisapprove]) }}">
                    <i class="fa fa-calendar-times-o"></i> {{ trans('manage_time::view.Disapprove') }}
                    @if(count($countRegistersCreatedBy))
                        @if($countRegistersCreatedBy->status_disapprove > 0)
                            <span class="label bg-red pull-right">{{ $countRegistersCreatedBy->status_disapprove }}</span>
                        @endif
                    @endif
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
                <h3 class="box-title">{{ trans('manage_time::view.I approves late in early out register') }}</h3>
            </div>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body no-padding">
            <ul class="nav nav-pills nav-stacked managetime-menu">
                <li>
                    <a href="{{ route('manage_time::profile.comelate.approve-list') }}">
                        <i class="fa fa-inbox"></i> {{ trans('manage_time::view.All') }}
                        @if(count($countRegistersApprovedBy))
                            @if($countRegistersApprovedBy->all_register > 0)
                                <span class="label bg-aqua pull-right">{{ $countRegistersApprovedBy->all_register }}</span>
                            @endif
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('manage_time::profile.comelate.approve-list', ['status' => $statusUnapprove]) }}">
                        <i class="fa fa-hourglass-half"></i> {{ trans('manage_time::view.Unapprove') }}
                        @if(count($countRegistersApprovedBy))
                            @if($countRegistersApprovedBy->status_unapprove > 0)
                                <span class="label bg-green pull-right">{{ $countRegistersApprovedBy->status_unapprove }}</span>
                            @endif
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('manage_time::profile.comelate.approve-list', ['status' => $statusApproved]) }}">
                        <i class="fa fa-check"></i> {{ trans('manage_time::view.Approved') }}
                        @if(count($countRegistersApprovedBy))
                            @if($countRegistersApprovedBy->status_approved > 0)
                                <span class="label bg-gray-active pull-right">{{ $countRegistersApprovedBy->status_approved }}</span>
                            @endif
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('manage_time::profile.comelate.approve-list', ['status' => $statusDisapprove]) }}">
                        <i class="fa fa-calendar-times-o"></i> {{ trans('manage_time::view.Disapprove') }}
                        @if(count($countRegistersApprovedBy))
                            @if($countRegistersApprovedBy->status_disapprove > 0)
                                <span class="label bg-red pull-right">{{ $countRegistersApprovedBy->status_disapprove }}</span>
                            @endif
                        @endif
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <!-- /. box -->
@endif