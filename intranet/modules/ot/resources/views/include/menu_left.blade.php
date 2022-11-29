<?php
    use Rikkei\Team\View\Permission;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\URL;
    use Rikkei\Ot\Model\OtRegister;
    use Rikkei\Ot\View\OtPermission;

    $auth = \Rikkei\Team\View\Permission::getInstance()->getEmployee();
?>

<!-- MENU_MYOT-->
<div class="box box-solid">
    <div class="box-header with-border">
        <div class="pull-left ot-menu-title">
            <h3 class="box-title">{{ trans('ot::view.My register') }}</h3>
        </div>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="box-body no-padding">
        <ul class="nav nav-pills nav-stacked menu-ot">
            <li>
                <a href="{{ Url::route('ot::ot.register') }}" class="menu-title" id="register">
                    <i class="fa fa-share-square-o"></i> {{ trans('ot::view.Register') }}
                </a>
            </li>
            <li>
                <a href="{{ Url::route('ot::ot.list', ['empType' => 1]) }}" class="menu-title" id="unapproved">
                    <i class="fa fa-inbox"></i> {{ trans('ot::view.All') }}
                    @if ($totalRegister['0'])
                        <span class="pull-right">
                            <span class="label bg-aqua pull-right all-cdd-cn">
                                {{  $totalRegister['0'] }}                                
                            </span>
                        </span>
                    @endif
                </a>
            </li>
            <li>
                <a href="{{ Url::route('ot::ot.list', ['empType' => 1, 'listType' => 3]) }}" class="menu-title" id="unapproved">
                    <i class="fa fa-hourglass-half"></i> {{ trans('ot::view.Unapproved Label') }}
                    @if ($totalRegister['3'])
                        <span class="pull-right">
                            <span class="label bg-yellow pull-right all-cdd-cn">
                                {{  $totalRegister['3'] }}                                
                            </span>
                        </span>
                    @endif
                </a>
            </li>
            <li>
                <a href="{{ Url::route('ot::ot.list', ['empType' => 1, 'listType' => 4]) }}" class="menu-title" id="done"> 
                    <i class="fa fa-check"></i> {{ trans('ot::view.Approved Label') }}
                    @if ($totalRegister['4'])
                        <span class="pull-right">
                            <span class="label bg-green pull-right all-dd-cn">
                                {{ $totalRegister['4'] }}     
                            </span>
                        </span>
                    @endif
                </a>
            </li>
            <li>    
                <a href="{{ Url::route('ot::ot.list', ['empType' => 1, 'listType' => 2]) }}" class="menu-title" id="rejected"> 
                    <i class="fa fa-calendar-times-o"></i> {{ trans('ot::view.Rejected Label') }}
                    @if ($totalRegister['2'])
                        <span class="pull-right">
                            <span class="label bg-red pull-right all-btc-cn">
                                {{ $totalRegister['2'] }}     
                            </span>
                        </span>
                    @endif
                </a>
            </li>
        </ul>
    </div>
    <!-- /.box-body -->
</div>
<!-- /. box -->

@if (OtPermission::isScopeApproveOfSelf() || OtPermission::isScopeApproveOfTeam() || OtPermission::isScopeApproveOfCompany())
<!-- MENU_MYAPPROVE -->
<div class="box box-solid">
    <div class="box-header with-border">
        <div class="pull-left ot-menu-title">
            <h3 class="box-title">{{ trans('ot::view.My approval')}}</h3>
        </div>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="box-body no-padding">
        <ul class="nav nav-pills nav-stacked menu-ot">
            <li>
                <a href="{{ Url::route('ot::ot.list', ['empType' => 2]) }}" class="menu-title" id="pending">
                    <i class="fa fa-inbox"></i> {{ trans('ot::view.All') }}
                    @if ($totalApproval['0'])
                        <span class="pull-right">
                            <span class="label bg-aqua pull-right all-cd-td">
                                {{ $totalApproval['0'] }}
                            </span>
                        </span>
                    @endif
                </a>
            </li>
            <li>
                <a href="{{ Url::route('ot::ot.list', ['empType' => 2, 'listType' => 3]) }}" class="menu-title" id="pending">
                    <i class="fa fa-hourglass-half"></i> {{ trans('ot::view.Unapproved Label') }}
                    @if ($totalApproval['3'])
                        <span class="pull-right">
                            <span class="label bg-yellow pull-right all-cd-td">
                                {{ $totalApproval['3'] }}
                            </span>
                        </span>
                    @endif
                </a>
            </li>
            <li>
                <a href="{{ Url::route('ot::ot.list', ['empType' => 2, 'listType' => 4]) }}" class="menu-title" id="approved">
                    <i class="fa fa-check"></i> {{ trans('ot::view.Approved Label') }}
                    @if ($totalApproval['4'])
                        <span class="pull-right">
                            <span class="label bg-green pull-right all-dd-td">
                                {{ $totalApproval['4'] }}
                            </span>
                        </span>
                    @endif
                </a>
            </li>
            <li>
                <a href="{{ Url::route('ot::ot.list', ['empType' => 2, 'listType' => 2]) }}" class="menu-title"> 
                    <i class="fa fa-calendar-times-o"></i> {{ trans('ot::view.Rejected Label') }}
                    @if ($totalApproval['2'])
                        <span class="pull-right">
                            <span class="label bg-red pull-right all-tc-td">
                                {{  $totalApproval['2'] }}
                            </span>
                        </span>
                    @endif
                </a>
            </li>
        </ul>
    </div>
    <!-- /.box-body -->
</div>
@endif
@if (isset($isLeader) && $isLeader)
<!-- MENU_RELATE -->
<div class="box box-solid">
    <div class="box-header with-border">
        <div class="pull-left ot-menu-title">
            <h3 class="box-title">{{ trans('ot::view.My Relate to')}}</h3>
        </div>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="box-body no-padding">
        <ul class="nav nav-pills nav-stacked menu-ot">
            <li>
                <a href="{{ Url::route('ot::ot.list', ['empType' => 3]) }}" class="menu-title" id="pending">
                    <i class="fa fa-inbox"></i> {{ trans('ot::view.All') }}
                    @if ($listTotalRelateTo['0'])
                        <span class="pull-right">
                            <span class="label bg-aqua pull-right all-cd-td">
                                {{ $listTotalRelateTo['0'] }}
                            </span>
                        </span>
                    @endif
                </a>
            </li>
            <li>
                <a href="{{ Url::route('ot::ot.list', ['empType' => 3, 'listType' => 3]) }}" class="menu-title" id="pending">
                    <i class="fa fa-hourglass-half"></i> {{ trans('ot::view.Unapproved Label') }}
                    @if ($listTotalRelateTo['3'])
                        <span class="pull-right">
                            <span class="label bg-yellow pull-right all-cd-td">
                                {{ $listTotalRelateTo['3'] }}
                            </span>
                        </span>
                    @endif
                </a>
            </li>
            <li>
                <a href="{{ Url::route('ot::ot.list', ['empType' => 3, 'listType' => 4]) }}" class="menu-title" id="approved">
                    <i class="fa fa-check"></i> {{ trans('ot::view.Approved Label') }}
                    @if ($listTotalRelateTo['4'])
                        <span class="pull-right">
                            <span class="label bg-green pull-right all-dd-td">
                                {{ $listTotalRelateTo['4'] }}
                            </span>
                        </span>
                    @endif
                </a>
            </li>
            <li>
                <a href="{{ Url::route('ot::ot.list', ['empType' => 3, 'listType' => 2]) }}" class="menu-title"> 
                    <i class="fa fa-calendar-times-o"></i> {{ trans('ot::view.Rejected Label') }}
                    @if ($listTotalRelateTo['2'])
                        <span class="pull-right">
                            <span class="label bg-red pull-right all-tc-td">
                                {{  $listTotalRelateTo['2'] }}
                            </span>
                        </span>
                    @endif
                </a>
            </li>
        </ul>
    </div>
    <!-- /.box-body -->
</div>
@endif
