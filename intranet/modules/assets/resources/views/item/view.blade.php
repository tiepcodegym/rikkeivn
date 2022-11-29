@extends('layouts.default')

<?php
    use Carbon\Carbon;
    use Rikkei\Core\View\View;
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Assets\Model\AssetItem;
    use Rikkei\Assets\Model\AssetHistory;

    $labelStates = AssetItem::labelStates();
    $labelStatesHistory = AssetHistory::labelStates();
?>

@section('title')
    {{ trans('asset::view.Asset detail') }}
@endsection

@section('css')
    <link rel="stylesheet" href="{{ CoreUrl::asset('team/css/style.css') }}" />
    <style type="text/css">
        .asset-callout {
            background-color: #00a65a!important;
            border-color: #0097bc;
            color: #fff;
            border-radius: 3px;
            margin: 0 0 20px;
            padding: 15px;
        }
        .asset-callout p:last-child {
            margin-bottom: 0;
        }
        .content-wrapper .content .box-primary {
            padding: 0
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title request-box-title">{{ trans('asset::view.Asset information') }}</h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <div class="row">
                        <div class="form-group col-md-12">
                            <div class="col-sm-4">
                                <div class="asset-callout">
                                    <p class="text-center text-uppercase"><strong>{{ $labelStates[$assetItem->state] }}</strong></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label class="col-md-3 control-label"><b>{{ trans('asset::view.Asset code') }} </b></label>
                            <div class="col-md-9">
                                <span>{{ $assetItem->code }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label class="col-md-3 control-label"><b>{{ trans('asset::view.Asset name') }} </b></label>
                            <div class="col-md-9">
                                <span>{{ $assetItem->name }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label class="col-md-3 control-label"><b>{{ trans('asset::view.Asset category') }} </b></label>
                            <div class="col-md-9">
                                <span>{{ $assetItem->category_name }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label class="col-md-3 control-label"><b>{{ trans('asset::view.Serial') }} </b></label>
                            <div class="col-md-9">
                                <span>{{ $assetItem->serial }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label class="col-md-3 control-label"><b>{{ trans('asset::view.Management team') }} </b></label>
                            <div class="col-md-9">
                                <span>{{ $assetItem->getTeamManageName() }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label class="col-md-3 control-label"><b>{{ trans('asset::view.Manage asset person') }} </b></label>
                            <div class="col-md-9">
                                <span>{{ $assetItem->manager_name }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label class="col-md-3 control-label"><b>{{ trans('asset::view.Origin') }} </b></label>
                            <div class="col-md-9">
                                <span>{{ $assetItem->getAssetOriginName() }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label class="col-md-3 control-label"><b>{{ trans('asset::view.Supplier') }} </b></label>
                            <div class="col-md-9">
                                <span>{{ $assetItem->getAssetSupplierName() }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label class="col-md-3 control-label"><b>{{ trans('asset::view.Purchase date') }} </b></label>
                            <div class="col-md-9">
                                <span>{{ $assetItem->purchase_date }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label class="col-md-3 control-label"><b>{{ trans('asset::view.Warranty priod') }} </b></label>
                            <div class="col-md-9">
                                <span>{{ $assetItem->warranty_priod }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label class="col-md-3 control-label"><b>{{ trans('asset::view.Warranty exp date') }} </b></label>
                            <div class="col-md-9">
                                <span>{{ $assetItem->warranty_exp_date }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label class="col-md-3 control-label"><b>{{ trans('asset::view.Specification') }} </b></label>
                            <div class="col-md-9">
                                <span>{!! View::nl2br($assetItem->specification) !!}</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label class="col-md-3 control-label"><b>{{ trans('asset::view.Out of date') }} </b></label>
                            <div class="col-md-9">
                                <span>{{ $assetItem->out_of_date }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label class="col-md-3 control-label"><b>{{ trans('asset::view.Days before alert out of date') }} </b></label>
                            <div class="col-md-9">
                                <span>{{ $assetItem->days_before_alert_ood }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label class="col-md-3 control-label"><b>{{ trans('asset::view.Note') }} </b></label>
                            <div class="col-md-9">
                                <span>{!! View::nl2br($assetItem->note) !!}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.box-body -->
            </div> <!-- /. box -->

            @if ($assetItem->employee_id)
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title request-box-title">{{ trans('asset::view.Asset allocation information') }}</h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                         <div class="row">
                            <div class="form-group col-md-12">
                                <label class="col-md-3 control-label"><b>{{ trans('asset::view.Asset user') }} </b></label>
                                <div class="col-md-9">
                                    <span>{{ $assetItem->employee_name }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-12">
                                <label class="col-md-3 control-label"><b>{{ trans('asset::view.Allocation date') }} </b></label>
                                <div class="col-md-9">
                                    @if ($assetItem->received_date)
                                    <span>{{ Carbon::createFromFormat('Y-m-d', $assetItem->received_date)->format('d-m-Y') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-12">
                                <label class="col-md-3 control-label"><b>{{ trans('asset::view.Request name') }} </b></label>
                                <div class="col-md-9">
                                    <span>{{ $assetItem->request_name }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- /. box -->
            @endif

            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title request-box-title">{{ trans('asset::view.Process of using asset') }}</h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                        <thead>
                            <tr>
                                <th>{{ trans('asset::view.Employee code') }}</th>
                                <th>{{ trans('asset::view.Employee name') }}</th>
                                <th>{{ trans('asset::view.Position') }}</th>
                                <th>{{ trans('asset::view.State') }}</th>
                                <th>{{ trans('asset::view.Date') }}</th>
                                <th>{{ trans('asset::view.Reason') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($processUsingAsset) && count($processUsingAsset))
                                @foreach ($processUsingAsset as $item)
                                    <tr>
                                        <td>{{ $item->employee_code }}</td>
                                        <td>{{ $item->employee_name }}</td>
                                        <td>{{ $item->role_name }}</td>
                                        <td>{{ $labelStatesHistory[$item->state] }}</td>
                                        <td>{{ $item->change_date ? Carbon::createFromFormat('Y-m-d', $item->change_date)->format('d-m-Y') : '' }}</td>
                                        <td>{{ $item->change_reason }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div> <!-- /. box -->
        </div>
        <div class="col-lg-4">
            <div class="box box-primary box-solid" style="border: none;">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ trans('asset::view.History') }}</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-xs-12 history-list">
                            <div class="box-body">
                                @if (isset($assetHistories) && count($assetHistories))
                                    @foreach ($assetHistories as $item)
                                        <div class="col-md-12">
                                            <p class="author"><strong>- {{ $item->creator_name }} ({{ View::getNickName($item->creator_email) }})</strong>
                                                <i>at {{ $item->created_at ? Carbon::parse($item->created_at)->format('d-m-Y H:i:s') : ''}}</i>
                                            </p>
                                            <p class="date">{!! View::nl2br($item->note) !!}</p>
                                            @if ($item->change_date)
                                                <p class="date">
                                                    {{ trans('asset::view.Date:') }} {{ Carbon::createFromFormat('Y-m-d', $item->change_date)->format('d-m-Y') }}
                                                </p>
                                            @endif
                                            @if ($item->change_reason)
                                                </p>{{ trans('asset::view.Reason:') }} {!! View::nl2br($item->change_reason) !!}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
@endsection