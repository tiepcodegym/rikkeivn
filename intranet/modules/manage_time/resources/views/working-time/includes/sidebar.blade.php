<?php
use Rikkei\ManageTime\View\ManageTimeConst as MTConst;
use Rikkei\ManageTime\View\WorkingTime;

$objWorkingTime = new WorkingTime();
$listStatuses = $objWorkingTime->listWTStatusesWithIcon();

$routeName = request()->route()->getName();
$reqStt = isset($status) ? $status : null;
?>

<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title managetime-box-title">{{ trans('manage_time::view.My working time register') }}</h3>
    </div>
    <ul class="nav nav-pills nav-stacked">
        <li>
            <?php
            $route = 'manage_time::wktime.register';
            ?>
            <a href="{{ route($route) }}" class="{{ $route == $routeName ? 'active' : '' }}">
                <i class="fa fa-edit"></i> {{ trans('manage_time::view.Register') }}
            </a>
        </li>
        <?php $route = 'manage_time::wktime.register.list'; ?>
        @foreach ($listStatuses as $status => $arrStt)
        <?php
            $active = $route == $routeName && $reqStt == $status ? 'active' : '';
            $keyStt = 'register' . ($status ? '_' . $status : '');
        ?>
        <li>
            <a href="{{ route($route, $status ? ['status' => $status] : []) }}" class="{{ $active }}">
                <i class="fa {{ $arrStt['icon'] }}"></i> {{ $arrStt['title'] }} 
                <span class="label pull-right {{ $arrStt['label_icon'] }}">{{ isset($statistic[$keyStt]) ? $statistic[$keyStt] : 0 }}</span>
            </a>
        </li>
        @endforeach
    </ul>
</div>

@if ($isPermissApprove)
<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title managetime-box-title">{{ trans('manage_time::view.I approve working time register') }}</h3>
    </div>
    <ul class="nav nav-pills nav-stacked">
        <?php $route = 'manage_time::wktime.register.approve.list'; ?>
        @foreach ($listStatuses as $status => $arrStt)
        <?php
        $active = $route == $routeName && $reqStt == $status ? 'active' : '';
        $keyStt = 'approve' . ($status ? '_' . $status : '');
        ?>
        <li>
            <a href="{{ route($route, $status ? ['status' => $status] : []) }}" class="{{ $active }}">
                <i class="fa {{ $arrStt['icon'] }}"></i> {{ $arrStt['title'] }}
                <span class="label pull-right {{ $arrStt['label_icon'] }}">{{ isset($statistic[$keyStt]) ? $statistic[$keyStt] : 0 }}</span>
            </a>
        </li>
        @endforeach
    </ul>
</div>

<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title managetime-box-title">{{ trans('manage_time::view.Working time register relates to me') }}</h3>
    </div>
    <ul class="nav nav-pills nav-stacked">
        <?php $route = 'manage_time::wktime.register.related.list'; ?>
        @foreach ($listStatuses as $status => $arrStt)
        <?php
        $active = $route == $routeName && $reqStt == $status ? 'active' : '';
        $keyStt = 'related' . ($status ? '_' . $status : '');
        ?>
        <li>
            <a href="{{ route($route, $status ? ['status' => $status] : []) }}" class="{{ $active }}">
                <i class="fa {{ $arrStt['icon'] }}"></i> {{ $arrStt['title'] }}
                <span class="label pull-right {{ $arrStt['label_icon'] }}">{{ isset($statistic[$keyStt]) ? $statistic[$keyStt] : 0 }}</span>
            </a>
        </li>
        @endforeach
    </ul>
</div>
@endif