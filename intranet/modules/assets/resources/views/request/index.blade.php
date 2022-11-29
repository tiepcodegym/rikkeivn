@extends('layouts.default')

<?php
    use Carbon\Carbon;
    use Rikkei\Core\View\View;
    use Rikkei\Core\View\Form;
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Team\View\Config;
    use Rikkei\Team\Model\Employee;
    use Rikkei\Team\View\TeamList;
    use Rikkei\Assets\Model\RequestAsset;
    use Rikkei\Assets\Model\RequestAssetTeam;
    use Rikkei\Assets\View\RequestAssetPermission;
    use Rikkei\Assets\View\AssetConst;
    use Rikkei\Assets\Model\AssetItem;
    use Rikkei\Team\View\Permission;

    $tblRequestAsset = RequestAsset::getTableName();
    $tblRequestAssetTeam = RequestAssetTeam::getTableName();
    $tblEmployee = Employee::getTableName();
    $tblEmployeeAsReviewer = 'tbl_employee_as_reviewer';
    $tblEmployeeAsApprover = 'tbl_employee_as_approver';
    $tblEmployeeAsCreator = 'tbl_employee_as_creator';
    $labelRequestAsset = RequestAsset::labelStates();
    $teamsOptionAll = TeamList::toOption(null, true, false);
    $listPermissIds = RequestAssetPermission::permissEditRequesets($collectionModel->lists('id')->toArray());
    $ignoreStatus = RequestAsset::ignoreStatus();
    $labelConfirmAsset = AssetItem::labelAllocationConfirm();
    $tblRequest = 'tbl_request';
?>

@section('title')
    {{ trans('asset::view.Request asset list') }}
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="{{ CoreUrl::asset('team/css/style.css') }}" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('manage_asset/css/request.css') }}" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('manage_asset/css/style.css') }}" />
@endsection

@section('content')
    <div class="box box-primary">
        <div class="box-header">
            <div class="pull-left">
                <a href="{{ route('asset::resource.request.edit') }}" class="btn btn-success"><i class="fa fa-plus"></i> {{ trans('asset::view.Create request') }}</a>
                @if (Permission::getInstance()->isAllow('asset::resource.request.delete-request'))
                    <a class="btn btn-danger btn-delete-request disabled" data-toggle="modal" data-target="#modal_delete">
                        <span><i class="fa fa-check"></i> {{ trans('asset::view.Delete request') }}</span>
                    </a>
                @endif
                <a href="{{ route('asset::resource.request.index') }}" class="btn btn-default" title="Danh sách tất cả request">Tất cả</a>
                <a href="{{ route('asset::resource.request.index', ['type' => 'not_yet']) }}" class="btn btn-default" title="Danh sách request chưa tạo yêu cầu tài sản đến kho">Chưa request kho - {{ $countReqNotYet }}</a>
                <a href="{{ route('asset::resource.request.index', ['type' => 'not_enough']) }}" class="btn btn-default" title="Danh sách request đã được kho cấp phát tài sản nhưng chưa đủ">Kho chưa cấp phát dủ - {{ $countReqNotEnough }}</a>
                <a href="{{ route('asset::resource.request.index', ['type' => 'enough']) }}" class="btn btn-default" title="Danh sách request đã được kho cấp phát đủ tải sản yêu cầu">Đã cấp phát đủ - {{ $countReqEnough }}</a>
            </div>
            <div class="pull-right">   
                @include('team::include.filter', ['domainTrans' => 'asset'])
            </div>
        </div>
        <!-- /.box-header -->

        <div class="box-body no-padding">
            <div class="table-responsive">
                <table class="table table-striped dataTable table-bordered table-hover table-grid-data table-request-asset">
                    <thead>
                        <tr>
                            <th class="width-25">
                                <input type="checkbox" name="check_all" class="btn-delete-all">
                                <input type="hidden" name="id" id="request_ids">
                            </th>
                            <th class="width-25">{{ trans('core::view.NO.') }}</th>
                            <th class="width-180 sorting {{ Config::getDirClass('request_name') }} col-title" data-order="request_name" data-dir="{{ Config::getDirOrder('request_name') }}">{{ trans('asset::view.Request name') }}</th>
                            <th class="width-60 sorting {{ Config::getDirClass('request_date') }} col-title" data-order="request_date" data-dir="{{ Config::getDirOrder('request_date') }}">{{ trans('asset::view.Request date') }}</th>
                            <th class="width-80 sorting {{ Config::getDirClass('petitioner_name') }} col-title" data-order="petitioner_name" data-dir="{{ Config::getDirOrder('petitioner_name') }}">{{ trans('asset::view.Petitioner') }}</th>
                            <th class="width-120 sorting {{ Config::getDirClass('role_name') }} col-title" data-order="role_name" data-dir="{{ Config::getDirOrder('role_name') }}">{{ trans('asset::view.Position') }}</th>
                            <th class="width-80 sorting {{ Config::getDirClass('team_name') }} col-title" data-order="team_name" data-dir="{{ Config::getDirOrder('team_name') }}">{{ trans('asset::view.Team') }}</th>
                            <th class="width-80 col-title">{{ trans('asset::view.Reviewer') }}</th>
                            <th class="width-80 white-space-nowrap col-title">{{ trans('asset::view.Creator request') }}</th>
                            <th class="width-60 sorting {{ Config::getDirClass('status') }} col-title" data-order="status" data-dir="{{ Config::getDirOrder('status') }}">{{ trans('asset::view.Status') }}</th>
                            <th class="width-60"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[{{ $tblRequest }}.request_name]" value='{{ Form::getFilterData("{$tblRequest}.request_name") }}' placeholder="{{ trans('asset::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[except][{{ $tblRequest }}.request_date]" value='{{ Form::getFilterData('except', "{$tblRequest}.request_date") }}' placeholder="{{ trans('asset::view.Search') }}..." class="filter-grid form-control filter-date" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[except][{{ $tblRequest }}.petitioner_name]" value='{{ Form::getFilterData('except',"{$tblRequest}.petitioner_name") }}' placeholder="{{ trans('asset::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>&nbsp;</td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php
                                            $teamFilter = Form::getFilterData('except', "{$tblRequest}.team_id");
                                        ?>
                                        <select style="width: 160px" name="filter[except][{{ $tblRequest }}.team_id]" class="form-control select-grid filter-grid select-search" autocomplete="off">
                                            <option value="">&nbsp;</option>
                                            @foreach($teamsOptionAll as $option)
                                                <option value="{{ $option['value'] }}"<?php
                                                    if ($option['value'] == $teamFilter): ?> selected<?php endif; 
                                                        ?>>{{ $option['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[except][{{ $tblRequest }}.reviewer_name]" value='{{ Form::getFilterData('except', "{$tblRequest}.reviewer_name") }}' placeholder="{{ trans('asset::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[except][{{ $tblRequest }}.creator_name]" value='{{ Form::getFilterData('except', "{$tblRequest}.creator_name") }}' placeholder="{{ trans('asset::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php
                                            $filterStatus = Form::getFilterData('number', "{$tblRequest}.status");
                                        ?>
                                        <select name="filter[number][{{ $tblRequest }}.status]" class="form-control select-grid filter-grid select-search" style="width: 100%;" autocomplete="off">
                                            <option>&nbsp;</option>
                                            @if (count($labelRequestAsset))
                                                @foreach($labelRequestAsset as $key => $value)
                                                    <option value="{{ $key }}" <?php if ($filterStatus !== null && $key == $filterStatus): ?> selected<?php endif; ?>>{{ $value }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </td>
                            <td>&nbsp;</td>
                        </tr>
                        
                        @if(isset($collectionModel) && count($collectionModel))
                            <?php
                            $i = View::getNoStartGrid($collectionModel);
                            ?>
                            @foreach($collectionModel as $item)
                                <?php
                                $countAssets = isset($assetState->count_assets) ? $assetState->count_assets : null;
                                $teamCode = isset($assetState->prefix) ? $assetState->prefix : null;
                                $bgDanger = (!in_array($item->status, $ignoreStatus) && !AssetConst::hasCatAsset($item->str_qty, $countAssets));
                                ?>
                                <tr {!! $bgDanger ? 'class="danger"' : '' !!} team-code="{{ $teamCode }}" data-count="{{ $item->str_qty . '|' . $countAssets }}">
                                    <td>
                                        <input type="checkbox" name="id" value="{{ $item->id }}">
                                    </td>
                                    <td>{{ $i }}</td>
                                    <td class="word-break-word"><a href="{{ route('asset::resource.request.view', ['id' => $item->id]) }}">{{ $item->request_name }}</a></td>
                                    <td>{{ Carbon::createFromFormat('Y-m-d', $item->request_date)->format('d-m-Y') }}</td>
                                    <td>{{ $item->petitioner_name }}</td>
                                    <td>{{ $item->role_name }}</td>
                                    <td>{{ $item->team_name }}</td>
                                    <td>{{ isset($reviewersRequest[$item->id]) ? $reviewersRequest[$item->id] : null }}</td>
                                    <td>{{ isset($assetCreator[$item->id]) ? $assetCreator[$item->id] : null }}</td>
                                    <td>
                                        @if( $item->status == RequestAsset::STATUS_CLOSE)
                                            {{ $labelRequestAsset[$item->status] }} - {{ isset($item->state) ? $labelConfirmAsset[$item->state] : 0 }}
                                        @else
                                            {{ $labelRequestAsset[$item->status] }}
                                        @endif
                                    </td>
                                    <td>
                                        @if (RequestAssetPermission::checkPermissInList($item->id, $listPermissIds, $item->created_by))
                                        <a class="btn btn-success" href="{{ route('asset::resource.request.edit', ['id' => $item->id]) }}">
                                            <i class="fa fa-pencil-square-o"></i>
                                        </a>
                                        @endif
                                        @if (in_array($item->status, [RequestAsset::STATUS_APPROVED, RequestAsset::STATUS_CLOSE]))
                                            <a class="btn btn-info" title="Request tài sản tới kho" href="{{ route('asset::resource.request.view_it_warehouse', ['id' => $item->id]) }}" >
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                                <?php $i++; ?>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="10" class="text-center">
                                    <h2 class="no-result-grid">{{ trans('asset::view.No results data') }}</h2>
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
    </div>
    <!-- /. box -->
    @include('asset::request.include.modal_confirm_delete')
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.15.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script src="{{ CoreUrl::asset('manage_asset/js/request_asset.js') }}"></script>
@endsection