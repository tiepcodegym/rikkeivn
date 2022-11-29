<?php
use Rikkei\Assets\Model\RequestAsset;
use Rikkei\Team\View\Permission;

$isPermissApprove = Permission::getInstance()->isAllow('asset::resource.request.approve');
$isPermissReview = Permission::getInstance()->isAllow('asset::resource.request.review');
$arrayStatuses = [
    null => [
        'title' => trans('asset::view.All'),
        'label_icon' => 'label-primary',
    ],
    RequestAsset::STATUS_INPROGRESS => [
        'title' => trans('asset::view.Inprogress'),
        'label_icon' => 'label-warning'
    ],
    RequestAsset::STATUS_REJECT => [
        'title' => trans('asset::view.Reject'),
        'label_icon' => 'label-default'
    ],
    RequestAsset::STATUS_REVIEWED => [
        'title' => trans('asset::view.Reviewed'),
        'label_icon' => 'label-info'
    ],
    RequestAsset::STATUS_APPROVED => [
        'title' => trans('asset::view.Approved'),
        'label_icon' => 'label-success'
    ],
    RequestAsset::STATUS_CLOSE => [
        'title' => trans('asset::view.Closed'),
        'label_icon' => 'label-danger'
    ]
];
$statistic = RequestAsset::myStatistic();
$reqStt = request()->get('status');
$reqType = request()->get('type');
$route = 'asset::profile.my_request_asset';
$routeName = request()->route()->getName();
?>

<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title managetime-box-title">{{ trans('asset::view.My request asset') }}</h3>
    </div>
    <ul class="nav nav-pills nav-stacked">
        @foreach ($arrayStatuses as $status => $arrStt)
        <?php
        $active = ($route == $routeName && $reqStt == $status && ($reqType == 'creator' || !$reqType)) ? 'active' : '';
        $keyStt = 'register' . ($status ? '_' . $status : '');
        ?>
        <li>
            <a href="{{ route($route, $status ? ['status' => $status] : []) }}" class="{{ $active }}">
                {{ $arrStt['title'] }} 
                <span class="label pull-right {{ $arrStt['label_icon'] }}">{{ isset($statistic[$keyStt]) ? $statistic[$keyStt] : 0 }}</span>
            </a>
        </li>
        @endforeach
    </ul>
</div>

@if ($isPermissReview)
<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title managetime-box-title">{{ trans('asset::view.Review request asset') }}</h3>
    </div>
    <ul class="nav nav-pills nav-stacked">
        @foreach ($arrayStatuses as $status => $arrStt)
        <?php
        $active = ($route == $routeName && $reqStt == $status && $reqType == 'reviewer') ? 'active' : '';
        $keyStt = 'reviewer' . ($status ? '_' . $status : '');
        $routeParams = ['type' => 'reviewer'];
        if ($status) {
            $routeParams['status'] = $status;
        }
        ?>
        <li>
            <a href="{{ route($route, $routeParams) }}" class="{{ $active }}">
                {{ $arrStt['title'] }}
                <span class="label pull-right {{ $arrStt['label_icon'] }}">{{ isset($statistic[$keyStt]) ? $statistic[$keyStt] : 0 }}</span>
            </a>
        </li>
        @endforeach
    </ul>
</div>
@endif

@if ($isPermissApprove)
<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title managetime-box-title">{{ trans('asset::view.Approve request asset') }}</h3>
    </div>
    <ul class="nav nav-pills nav-stacked">
        @foreach ($arrayStatuses as $status => $arrStt)
        <?php
        $active = ($route == $routeName && $reqStt == $status && $reqType == 'approver') ? 'active' : '';
        $keyStt = 'approver' . ($status ? '_' . $status : '');
        $routeParams = ['type' => 'approver'];
        if ($status) {
            $routeParams['status'] = $status;
        }
        ?>
        <li>
            <a href="{{ route($route, $routeParams) }}" class="{{ $active }}">
                {{ $arrStt['title'] }}
                <span class="label pull-right {{ $arrStt['label_icon'] }}">{{ isset($statistic[$keyStt]) ? $statistic[$keyStt] : 0 }}</span>
            </a>
        </li>
        @endforeach
        <li>
            <a href="{{ route('asset::resource.request.index') }}">
                <strong>{{ trans('asset::view.View full page') }}</strong>
            </a>
        </li>
    </ul>
</div>
@endif

