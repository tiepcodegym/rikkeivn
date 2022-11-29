@extends('layouts.default')

<?php
    use Carbon\Carbon;
    use Rikkei\Core\View\View;
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Assets\Model\RequestAsset;
    use Rikkei\Assets\View\AssetConst;
    use Rikkei\Assets\View\AssetView;
    use Rikkei\Assets\View\RequestAssetPermission;
    use Rikkei\Assets\Model\RequestAssetHistory;
    use Rikkei\Team\View\Permission;
    use Rikkei\Assets\Model\AssetItem;
    use Rikkei\Assets\Model\AssetCategory;
    use Rikkei\Team\Model\EmployeeTeamHistory;

    $userCurrent = Permission::getInstance()->getEmployee();
    $labelRequestAsset = RequestAsset::labelStates();
    $labelConfirmAsset = AssetItem::labelAllocationConfirm();
    $assetCategoriesList = AssetCategory::getAssetCategoriesList();

    $reviewPermision = RequestAssetPermission::isAllowReviewRequest($requestAsset, $userCurrent->id);
    $approvePermision = RequestAssetPermission::isAllowApproveRequest($requestAsset, $userCurrent->id);
    $permissAllowcate = ($requestAsset->status == RequestAsset::STATUS_APPROVED) && Permission::getInstance()->isAllow('asset::asset.asset-allocation');
    $branch = $reqIt ? $reqIt->branch : $mainTeamCurrent->branch_code;
?>

@section('title')
    {{ trans('asset::view.Request asset detail') }}
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="{{ CoreUrl::asset('team/css/style.css') }}" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('manage_asset/css/request.css') }}" />
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
        <div class="col-lg-7">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title request-box-title">{{ trans('asset::view.Request information') }}</h3>
                    <div class="pull-right">
                        <a class="request-link" target="_blank" href="{{ route('asset::resource.request.edit', ['id' => $requestAsset->id]) }}">
                            <span><i class="fa fa-hand-pointer-o"></i> {{ trans('asset::view.Edit request') }}</span>
                        </a>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <div class="row">
                        <div class="form-group col-md-12">
                            <div class="col-sm-4">
                                {!! RequestAsset::renderStatusHtml($requestAsset->status, $labelRequestAsset) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label class="col-md-3 control-label"><b>{{ trans('asset::view.Request name') }} </b></label>
                            <div class="col-md-9">
                                <span>{{ $requestAsset->request_name }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label class="col-md-3 control-label"><b>{{ trans('asset::view.Request date') }} </b></label>
                            <div class="col-md-9">
                                <span>{{ $requestAsset->request_date }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label class="col-md-3 control-label"><b>{{ trans('asset::view.Division') }} </b></label>
                            <div class="col-md-9">
                                <span>{{ $requestAsset->divison }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label class="col-md-3 control-label"><b>{{ trans('asset::view.Petitioner') }} </b></label>
                            <div class="col-md-9">
                                <?php
                                    $petitioner = $requestAsset->getPetitionerInfomation();
                                    $petitionerName = '';
                                    if ($petitioner) {
                                        $petitionerName = $petitioner->name . ' (' . AssetView::getNickName($petitioner->email) . ')';
                                    }
                                ?>
                                <span>{{ $petitionerName }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label class="col-md-3 control-label"><b>{{ trans('asset::view.Skype (of user)') }} </b></label>
                            <div class="col-md-9">
                                <span>{{ $requestAsset->skype }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label class="col-md-3 control-label"><b>{{ trans('asset::view.Creator request') }} </b></label>
                            <div class="col-md-9">
                                <?php
                                    $creator = $requestAsset->getCreatorInfomation();
                                    $creatorName = '';
                                    if ($creator) {
                                        $creatorName = $creator->name . ' (' . AssetView::getNickName($creator->email) . ')';
                                    }
                                ?>
                                <span>{{ $creatorName }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label class="col-md-3 control-label"><b>{{ trans('asset::view.Reviewer') }} </b></label>
                            <div class="col-md-9">
                                <?php
                                    $reviewer = $requestAsset->getReviewerInfomation();
                                    $reviewerName = '';
                                    if ($reviewer) {
                                        $reviewerName = $reviewer->name . ' (' . AssetView::getNickName($reviewer->email) . ')';
                                    }
                                ?>
                                <span>{{ $reviewerName }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label class="col-md-3 control-label"><b>{{ trans('asset::view.Request reason') }} </b></label>
                            <div class="col-md-9">
                                <span>{!! View::nl2br($requestAsset->request_reason) !!}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /. box -->
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title request-box-title">{{ trans('asset::view.Asset request information') }}</h3>
                    <a class="pull-right" href="
                            @if (Route::current()->getName() == 'asset::profile.view')
                                {{ route('asset::profile.my_request_asset') }}
                            @elseif (Route::current()->getName() == 'asset::resource.request.view')
                                {{ route('asset::resource.request.index') }}
                            @endif
                            ">
                        <span><i class="fa fa-long-arrow-left"></i> {{ trans('asset::view.Request list') }}</span>
                    </a>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    @if (isset($requestAsssetItem) && count($requestAsssetItem))
                        @if ($permissAllowcate)
                            {!! Form::open(['method' => 'post', 'route' => 'asset::asset.asset-allocation', 'class' => 'no-validate', 'id' => 'form-asset-submit']) !!}
                        @endif
                        <table class="table dataTable table-bordered table-grid-data" id="list-asset-category">
                            <thead>
                                <tr>
                                    <th>{{ trans('asset::view.Asset category request') }} <i class="fa fa-spin fa-refresh hidden" id="update_cate_loading"></i></th>
                                    <th width="100">{{ trans('asset::view.Quantity') }} <i class="fa fa-spin fa-refresh hidden" id="update_qty_loading"></i></th>
                                    @if ($permissAllowcate)
                                    <th style="width: 230px;">{{trans('asset::view.Asset information') }}</th>
                                    <th style="width: 50px;"></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $urlSearchAsset = route('asset::asset.search.ajax');
                                $urlUpdateQty = route('asset::resource.request.update_qty', $requestAsset->id);
                                $urlUpdateCate= route('asset::resource.request.update_cate_id', $requestAsset->id);
                                ?>
                                @foreach ($requestAsssetItem as $requestItem)
                                    @include('asset::request.include.allowcate-item', ['request' => $requestItem])
                                @endforeach
                            </tbody>
                            @if ($permissAllowcate)
                            <tfoot>
                                <tr>
                                    <td colspan="3"></td>
                                    <td>
                                        <button type="button" class="btn btn-primary" id="btn_add_item"><i class="fa fa-plus"></i></button>
                                    </td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                        <label class="asset-error request-category-duplicate-error">{{ trans('asset::message.The request asset category can not be duplicated') }}</label>

                        @if ($permissAllowcate)
                        <div class="margin-bottom-15"></div>
                        <div class="form-group text-center">
                            <input type="hidden" name="item[request_id]" value="{{ $requestAsset->id }}">
                            <input type="hidden" name="item[employee_id]" value="{{ $petitioner ? $petitioner->id : null }}">
                            <input type="hidden" name="item[received_date]" value="{{ Carbon::now()->format('d-m-Y') }}">
                            <textarea class="hidden" name="item[reason]">{{ $requestAsset->request_reason }}</textarea>
                            <input type="hidden" name="redirect_url" value="{{ route('asset::resource.request.view', ['id' => $requestAsset->id]) }}">
                            <input type="hidden" name="save_history" value="1">
                            <input type="hidden" name="ngoc" value="1">
                            <input type="hidden" name="branch" value="{{ $branch }}" class="js-type-branch">
                            {{-- <button type="submit" class="btn btn-info" id="btn_req_warehouse">Request tới kho</button> --}}
                            <button type="submit" class="btn btn-primary" id="btn_req_allocation"
                                    data-noti="{{ trans('asset::message.Please input completely information') }}"
                                    data-noti_branch="{{ trans('asset::message.There are assets not belonging to employees\' branches. Are you sure want to continue?') }}"> {{ trans('asset::view.Allocation') }}</button>
                            <a href="{{ route('asset::resource.request.view_it_warehouse', ['id' => $requestAsset->id]) }}" style="margin-left: 10px;">Request tới kho</a>
                        </div>
                        {!! Form::close() !!}
                        @endif
                    @endif

                    <table class="table dataTable table-bordered table-grid-data">
                        <thead>
                            <tr>
                                <th>{{ trans('asset::view.Asset code') }}</th>
                                <th>{{ trans('asset::view.Asset name') }}</th>
                                <th>{{ trans('asset::view.Had allocation') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($assetsHistoryRequests) && count($assetsHistoryRequests) > 0)
                                @foreach ($assetsHistoryRequests as $assetsHistoryRequest)
                                    @if (!$permissAllowcate)
                                    <tr>
                                        <td>{{ $assetsHistoryRequest->code }}</td>
                                        <td>{{ $assetsHistoryRequest->asset_name }}</td>
                                        <td>{{ $labelConfirmAsset[ $assetsHistoryRequest->allocation_confirm ] }}</td>
                                    </tr>
                                    @endif
                                @endforeach
                            @elseif (isset($listAssets) && count($listAssets) > 0)
                            {{-- view data old --}}
                                @foreach ($listAssets as $listAsset)
                                    @if (!$permissAllowcate)
                                    <tr>
                                        <td>{{ $listAsset->code }}</td>
                                        <td>{{ $listAsset->name }}</td>
                                        <td>{{ $labelConfirmAsset[ $listAsset->allocation_confirm ] }}</td>
                                    </tr>
                                    @endif
                                @endforeach
                             {{-- end data old --}}
                            @else
                            <tr>
                                <td colspan="3" class="text-center">{{ trans('asset::message.None asset allowcated') }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>

                </div>

                    <div class="box-footer">
                        <div class="col-md-12">
                            @if ($requestAsset->status == RequestAsset::STATUS_INPROGRESS || $requestAsset->status == RequestAsset::STATUS_REVIEWED)
                                @if ($reviewPermision && $requestAsset->status == RequestAsset::STATUS_INPROGRESS)
                                    <a class="btn btn-success btn-approve-request" data-toggle="modal" data-target="#modal_review">
                                        <span><i class="fa fa-check"></i> {{ trans('asset::view.Confirm') }}</span>
                                    </a>
                                    <a class="btn btn-danger btn-disreview-request" data-toggle="modal" data-target="#modal_disreview">
                                        <span><i class="fa fa-minus-circle"></i> {{ trans('asset::view.Request.Disapprove') }}</span>
                                    </a>
                                @endif
                                @if ($approvePermision && $requestAsset->status == RequestAsset::STATUS_REVIEWED)
                                    <a class="btn btn-success btn-approve-request" data-toggle="modal" data-target="#modal_approve">
                                        <span><i class="fa fa-check"></i> {{ trans('asset::view.Approve') }}</span>
                                    </a>
                                    <a class="btn btn-danger btn-disapprove-request" data-toggle="modal" data-target="#modal_disapprove">
                                        <span><i class="fa fa-minus-circle"></i> {{ trans('asset::view.Request.Disapprove') }}</span>
                                    </a>
                                @endif
                            @endif
                            @if (Permission::getInstance()->isAllow('asset::resource.request.delete-request'))
                                <a class="btn btn-danger btn-delete-request" data-toggle="modal" data-target="#modal_delete">
                                    <span><i class="fa fa-trash"></i> {{ trans('asset::view.Delete') }}</span>
                                </a>
                            @endif
                            @if (Route::current()->getName() == 'asset::profile.view')
                                <a class="btn btn-warning float-right" href="{{ route('asset::profile.my_request_asset')."?type=reviewer&status=1" }}">
                                    <span><i class="fa fa-long-arrow-left"></i> {{ trans('asset::view.Back to asset review list') }}</span>
                                </a>
                            @endif
                        </div>
                    </div>


            </div>
            <!-- /. box -->
        </div>
        <div class="col-lg-5">
            <div class="box box-primary box-solid" style="border: none;">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ trans('asset::view.History') }}</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-xs-12 history-list">
                            <div class="box-body">
                                @if (!empty($requestAssetHistories))
                                    @foreach ($requestAssetHistories as $item)
                                        <div class="col-md-12 history-item">
                                            <div>
                                                <p class="author">
                                                    - <strong>{{ $item->creator_name . ' (' . AssetView::getNickName($item->creator_email) . ')' }}</strong> <i>at {{ $item->created_at }}</i>
                                                </p>
                                                <p class="comment">
                                                    {!! RequestAssetHistory::getContentHistory($item->action) !!}
                                                    @if ($item->action == RequestAssetHistory::ACTION_ALLOCATE)
                                                    <ul>
                                                        @if (isset($assetsHistoryRequests) && count($assetsHistoryRequests) > 0)
                                                            @foreach ($assetsHistoryRequests as $assetsHistoryRequest)
                                                                <li>
                                                                    {{ $assetsHistoryRequest->code }} - {{ $assetsHistoryRequest->asset_name }} - {{ $labelConfirmAsset[ $assetsHistoryRequest->allocation_confirm ] }}
                                                                </li>
                                                            @endforeach
                                                        @endif
                                                    </ul>
                                                    @endif
                                                </p>
                                                @if ($item->action == RequestAssetHistory::ACTION_REJECT && $item->note)
                                                    <p class="note">
                                                        Lý do: {!! View::nl2br($item->note) !!}
                                                    </p>
                                                @endif
                                            </div>
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

    <div class="hidden">
        @include('asset::request.include.allowcate-item')
    </div>

    <!-- Approve modal -->
    <div class="modal fade in modal-warning" id="modal_review">
        <div class="modal-dialog">
            <form action="{{ route('asset::resource.request.review') }}" method="post">
                {!! csrf_field() !!}
                <input type="hidden" name="id" value="{{ $requestAsset->id }}" />
                <input type="hidden" name="status" value="{{ AssetConst::APPROVE_REQUEST }}" />
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <h4 class="modal-title">{{ trans('asset::view.Confirm') }}</h4>
                    </div>
                    <div class="modal-body">
                        <p class="text-default">{{ trans('asset::view.Are you sure to do this action?') }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline pull-left" data-dismiss="modal">{{ trans('asset::view.Close') }}</button>
                        <button type="submit" class="btn btn-outline pull-right">{{ trans('asset::view.Yes') }}</button>
                    </div>
                </div>
            </form>
        </div><!-- /.modal-dialog -->
    </div>

    <!-- Disapprove modal -->
    <div class="modal fade in" id="modal_disreview">
        <div class="modal-dialog">
            <form id="form_disreview_request" action="{{ route('asset::resource.request.review') }}" method="post">
                {!! csrf_field() !!}
                <input type="hidden" name="id" value="{{ $requestAsset->id }}" />
                <input type="hidden" name="status" value="{{ AssetConst::DISAPPROVE_REQUEST }}" />
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <h4 class="modal-title">{{ trans('asset::view.Confirm') }}</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label required">{{ trans('asset::view.Request.Disapprove reason') }} <em>*</em></label>
                            <div class="input-box">
                                <textarea name="disapprove_reason" class="form-control required request-textarea-100"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('asset::view.Close') }}</button>
                        <button type="submit" class="btn btn-primary pull-right">{{ trans('asset::view.Yes') }}</button>
                    </div>
                </div>
            </form>
        </div><!-- /.modal-dialog -->
    </div>

    <!-- Approve modal -->
    <div class="modal fade in modal-warning" id="modal_approve">
        <div class="modal-dialog">
            <form action="{{ route('asset::resource.request.approve') }}" method="post">
                {!! csrf_field() !!}
                <input type="hidden" name="id" value="{{ $requestAsset->id }}" />
                <input type="hidden" name="status" value="{{ AssetConst::APPROVE_REQUEST }}" />
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <h4 class="modal-title">{{ trans('asset::view.Confirm') }}</h4>
                    </div>
                    <div class="modal-body">
                        <p class="text-default">{{ trans('asset::view.Are you sure to do this action?') }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline pull-left" data-dismiss="modal">{{ trans('asset::view.Close') }}</button>
                        <button type="submit" class="btn btn-outline pull-right">{{ trans('asset::view.Yes') }}</button>
                    </div>
                </div>
            </form>
        </div><!-- /.modal-dialog -->
    </div>

    <!-- Disapprove modal -->
    <div class="modal fade in" id="modal_disapprove">
        <div class="modal-dialog">
            <form id="form_disapprove_request" action="{{ route('asset::resource.request.approve') }}" method="post">
                {!! csrf_field() !!}
                <input type="hidden" name="id" value="{{ $requestAsset->id }}" />
                <input type="hidden" name="status" value="{{ AssetConst::DISAPPROVE_REQUEST }}" />
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <h4 class="modal-title">{{ trans('asset::view.Confirm') }}</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label required">{{ trans('asset::view.Request.Disapprove reason') }} <em>*</em></label>
                            <div class="input-box">
                                <textarea name="disapprove_reason" class="form-control required request-textarea-100"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('asset::view.Close') }}</button>
                        <button type="submit" class="btn btn-primary pull-right">{{ trans('asset::view.Yes') }}</button>
                    </div>
                </div>
            </form>
        </div><!-- /.modal-dialog -->
    </div>
    <div class="modal modal-default" id="modal-choose-branch">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"></span></button>
                    <h4>{{trans('sales::view.CSS.Preview.Confirm!')}}</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-25">
                        <label for="exampleFormControlSelect1" class="fs-17">Chọn kho</label>
                        <select class="form-control h-35" id="type-branch">
                            <option value="0"></option>
                            @foreach ($branchs as $item)
                                <option value="{{ $item }}" {{ $item == $branch ? 'selected' : '' }}>{{ $item }}</option>
                            @endforeach
                        </select>
                        <span class="message-choose-branch error hidden">Yêu cầu chọn kho trước khi gửi.</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn pull-left" data-dismiss="modal">{{ trans('sales::view.CSS.Preview.Cancel') }}</button>
                    <button type="button" id="send-choose-branch" class="btn btn-primary pull-right" data-dismiss="modal" data-url="{{ route('asset::asset.asset-request-to-wh') }}">{{ trans('sales::view.CSS.Preview.Submit') }}</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    @include('asset::request.include.modal_confirm_delete')
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.15.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
    <script>
        var currUserNameAcc = '{{ $userCurrent->name . " (". $userCurrent->getNickName() .")" }}';
        var textOutOfCatQuantity = '<?php echo trans("asset::message.Out of category quantity") ?>';
        var textAreYouWantoDelete = '<?php echo trans("asset::message.Are you sure want to delete?") ?>';
        var regionOfEmp = '{{ $regionOfEmp }}';
    </script>
    <script src="{{ CoreUrl::asset('manage_asset/js/request_asset.js') }}"></script>

    <script type="text/javascript">
        var requiredText = '{{ trans('asset::message.The field is required') }}';
        var validatorDisreview = $('#form_disreview_request').validate({
            rules: {
                'disapprove_reason': {
                    required: true,
                },
            },
            messages: {
                'disapprove_reason': {
                    required: requiredText,
                },
            }
        });
        var validatorDisapprove = $('#form_disapprove_request').validate({
            rules: {
                'disapprove_reason': {
                    required: true,
                },
            },
            messages: {
                'disapprove_reason': {
                    required: requiredText,
                },
            }
        });
        $('.btn-disreview-request').click(function() {
            validatorDisreview.resetForm();
        });
        $('.btn-disapprove-request').click(function() {
            validatorDisapprove.resetForm();
        });
    </script>
@endsection