@extends('layouts.default')

<?php
use Rikkei\Team\View\Config;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Assets\View\AssetConst;

$listInventoryState = AssetConst::listInventoryState();
?>

@section('title', trans('asset::view.Inventory assets'))

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('manage_asset/css/style.css') }}" />
@endsection

@section('content')
<div class="box box-primary">
    <div class="box-header">
        <div class="pull-left">
            <a href="{{ route('asset::inventory.edit') }}" class="btn btn-primary"><i class="fa fa-plus"></i> {{ trans('asset::view.Create new') }}</a>
        </div>
        <div class="pull-right">
            @include('team::include.filter', ['domainTrans' => 'asset'])
        </div>
    </div>
    <!-- /.box-header -->

    <div class="box-body no-padding">
        <div class="table-responsive">
            <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                <thead>
                    <tr>
                        <th>{{ trans('core::view.NO.') }}</th>
                        <th class="sorting {{ Config::getDirClass('name') }} col-title" data-order="name" data-dir="{{ Config::getDirOrder('name') }}">{{ trans('asset::view.Name') }}</th>
                        <th class="sorting {{ Config::getDirClass('time') }} col-title" data-order="time" data-dir="{{ Config::getDirOrder('time') }}">{{ trans('asset::view.Time end') }}</th>
                        <th class="sorting {{ Config::getDirClass('status') }} col-title" data-order="status" data-dir="{{ Config::getDirOrder('status') }}">{{ trans('asset::view.Status') }}</th>
                        <th class="sorting {{ Config::getDirClass('team_names') }} col-title" data-order="team_names" data-dir="{{ Config::getDirOrder('team_names') }}">{{ trans('asset::view.Department') }}</th>
                        <th class="sorting {{ Config::getDirClass('created_at') }} col-title" data-order="created_at" data-dir="{{ Config::getDirOrder('created_at') }}">{{ trans('asset::view.Created time') }}</th>
                        <th class="sorting {{ Config::getDirClass('creator_name') }} col-title" data-order="creator_name" data-dir="{{ Config::getDirOrder('creator_name') }}">{{ trans('asset::view.Creator') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td></td>
                        <td>
                            <input type="text" name="filter[inv.name]" class="form-control filter-grid" placeholder="{{ trans('asset::view.Search') }}..."
                                   value="{{ CoreForm::getFilterData('inv.name') }}">
                        </td>
                        <td>
                            <input type="text" name="filter[inv.time]" class="form-control filter-grid" placeholder="{{ trans('asset::view.Search') }}..."
                                   value="{{ CoreForm::getFilterData('inv.time') }}">
                        </td>
                        <td>
                            <select class="form-control select-grid filter-grid select-search" style="min-width: 80px;"
                                    name="filter[inv.status]">
                                <option value="">&nbsp;</option>
                                <?php
                                $filterInvStatus = CoreForm::getFilterData('inv.status');
                                ?>
                                @foreach ($listInventoryState as $value => $label)
                                <option value="{{ $value }}" {{ $filterInvStatus == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <select class="form-control select-grid filter-grid select-search" style="min-width: 120px;"
                                    name="filter[excerpt][team_id]">
                                <option value="">&nbsp;</option>
                                @if ($teamList)
                                    <?php $filterTeamId = CoreForm::getFilterData('excerpt', 'team_id'); ?>
                                    @foreach ($teamList as $option)
                                    <option value="{{ $option['value'] }}" {{ $filterTeamId == $option['value'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </td>
                        <td>
                            <input type="text" name="filter[inv.created_at]" class="form-control filter-grid" placeholder="{{ trans('asset::view.Search') }}..."
                                   value="{{ CoreForm::getFilterData('inv.created_at') }}">
                        </td>
                        <td>
                            <input type="text" name="filter[emp.email]" class="form-control filter-grid" placeholder="{{ trans('asset::view.Search') }}..."
                                   value="{{ CoreForm::getFilterData('emp.email') }}">
                        </td>
                        <td></td>
                    </tr>
                    @if (!$collectionModel->isEmpty())
                        <?php
                        $currentPage = $collectionModel->currentPage();
                        $perPage = $collectionModel->perPage();
                        ?>
                        @foreach ($collectionModel as $order => $item)
                        <tr>
                            <td>{{ $order + 1 + ($currentPage - 1) * $perPage }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->time }}</td>
                            <td>
                                @if (isset($listInventoryState[$item->status]))
                                    {{ $listInventoryState[$item->status] }}
                                @endif
                            </td>
                            <td>{{ $item->team_names }}</td>
                            <td>{{ $item->created_at }}</td>
                            <td>{{ CoreView::getNickName($item->creator_name) }}</td>
                            <td class="white-space-nowrap">
                                <button type="button" class="btn btn-info btn-noti-inventory"
                                        data-noti="{{ trans('asset::message.Alert will send email, are you sure?') }}"
                                        data-url="{{ route('asset::inventory.alert', $item->id) }}"
                                        title="{{ trans('asset::view.Alert inventory') }}">
                                    <span class="fa fa-bell"></span>
                                    <i class="fa fa-refresh fa-spin hidden"></i>
                                </button>
                                <a href="{{ route('asset::inventory.item_detail', ['id' => $item->id]) }}" class="btn btn-primary" title="{{ trans('asset::view.View detail') }}"><i class="fa fa-eye"></i></a>
                                <a href="{{ route('asset::inventory.edit', ['id' => $item->id]) }}" class="btn btn-success" title="{{ trans('asset::view.Edit') }}"><i class="fa fa-edit"></i></a>
                                {!! Form::open(['method' => 'DELETE', 'route' => ['asset::inventory.delete', $item->id], 'class' => 'form-inline']) !!}
                                <button type="submit" class="btn-delete delete-confirm" title="{{ trans('asset::view.Delete') }}"><i class="fa fa-trash"></i></button>
                                {!! Form::close() !!}
                            </td>
                        </tr>
                        @endforeach
                    @else
                    <tr>
                        <td colspan="7"><h4 class="text-center">{{ trans('asset::message.None item found') }}</h4></td>
                    </tr>
                    @endif
                </tbody>
            </table>
            <!-- /.table -->
        </div>
        <!-- /.table-responsive -->
    </div>
    <!-- /.box-body -->

    <div class="box-body">
        @include('team::include.pager')
    </div>
</div>
<!-- /. box -->
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
    <script src="{{ CoreUrl::asset('manage_asset/js/manage_asset.script.js') }}"></script>
    <script>
        selectSearchReload();
    </script>
@endsection