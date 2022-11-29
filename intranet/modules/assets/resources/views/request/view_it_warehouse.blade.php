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
use Rikkei\Core\View\Form as CoreForm;

$assetCategoriesList = AssetCategory::getAssetCategoriesList();
$urlSearchAsset = route('asset::asset.search.ajax');
$branch = $reqIt ? $reqIt->branch : $mainTeamCurrent->branch_code;
$labelRequestAsset = RequestAsset::labelStates();
?>

@section('title', 'Danh sách yêu cầu tài sản tới kho')

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css" />
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="{{ CoreUrl::asset('team/css/style.css') }}" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('manage_asset/css/request.css') }}" />
    <style type="text/css">
        .asset-callout {
            background-color: #00a65a !important;
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

        .tr-active{
            background: #367fa9 !important;
            color: #fff;
        }
        .color-red{
            color: red;
        }
        .tr-emp{
            cursor: pointer;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="abc">
            <div class="col-lg-4">
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
            </div>
            <div class="col-lg-8">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title request-box-title">{{ trans('asset::view.Asset request information') }}</h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        @if (isset($requestAsssetItem) && count($requestAsssetItem))
                            <table class="table dataTable table-bordered table-grid-data" id="list-asset-category">
                                <thead>
                                    <tr>
                                        <th>{{ trans('asset::view.Asset category request') }} <i class="fa fa-spin fa-refresh hidden" id="update_cate_loading"></i></th>
                                        <th width="100">{{ trans('asset::view.Quantity') }} <i class="fa fa-spin fa-refresh hidden" id="update_qty_loading"></i></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($requestAsssetItem as $requestItem)
                                        @if ($requestItem->quantity > 0)
                                            <tr>
                                                <td>{{ $requestItem->asset_category_name }}</td>
                                                <td>{{ $requestItem->quantity }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-5">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title request-box-title">Thông tin request đã cấp phát đủ</h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <table class="table dataTable table-bordered table-grid-data">
                        <thead>
                            <tr>
                                <th>{{ trans('asset::view.Asset name') }}</th>
                                <th width="150">{{ trans('asset::view.Had allocation') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($assetAllocates) && count($assetAllocates) > 0)
                                @foreach ($assetAllocates as $item)
                                    <tr>
                                        <td>{{ $item->cate_name }}</td>
                                        <td>{{ $item->allocate }}</td>
                                    </tr>
                                @endforeach
                            @else
                            <tr>
                                <td colspan="3" class="text-center">{{ trans('asset::message.None asset allowcated') }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- /. box -->
        </div>
        <div class="col-lg-7">
            @if ($reqAsset->status == RequestAsset::STATUS_APPROVED)
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title request-box-title">Thông tin request chưa cấp phát đủ</h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <br>
                    {!! Form::open(['method' => 'post', 'route' => 'asset::asset.asset-allocation', 'class' => 'no-validate', 'id' => 'form-asset-submit']) !!}
                    <table class="table dataTable table-bordered table-grid-data" id="list-asset-category">
                        <thead>
                            <tr>
                                <th>{{ trans('asset::view.Asset category request') }} <i class="fa fa-spin fa-refresh hidden" id="update_cate_loading"></i></th>
                                <th width="100">{{ trans('asset::view.Quantity') }} <i class="fa fa-spin fa-refresh hidden" id="update_qty_loading"></i></th>
                                <th width="50"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($assetUnallocates) && count($assetUnallocates))
                                @foreach ($assetUnallocates as $val)
                                    <tr class="request-item" data-id="{{ $val->id }}" data-cat="{{ $val->cate_id }}">
                                        <td rowspan="" class="rowspan" >
                                            <select class="form-control category" name="cate_id[]">
                                                @if (count($assetCategoriesList))
                                                    @foreach ($assetCategoriesList as $item)
                                                        <option value="{{ $item->id }}" {{ $item->id == $val->cate_id ? 'selected' : '' }}>{{ $item->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <input type="hidden" name="ids[]" value="{{ $val->id }}">
                                            <div class="error js-err-cateId" data-cat-id="{{ $val->cate_id }}"></div>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control update-request-qty" name="qty[]"
                                            value="{{ $val->quantity }}"  min="1" max="10">
                                        </td>
                                        <td rowspan="" class="rowspan">
                                            <button class="btn btn-danger btn-del-item" type="button"><i class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                @endforeach                                
                            @endif
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2"></td>
                                <td>
                                    <button type="button" class="btn btn-primary" id="btn_add_item"><i class="fa fa-plus"></i></button>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                    <label class="asset-error request-category-duplicate-error">{{ trans('asset::message.The request asset category can not be duplicated') }}</label>

                    <div class="margin-bottom-15"></div>
                    <div class="form-group text-center">
                        <input type="hidden" name="item[request_id]" value="{{ $reqId }}">
                        <input type="hidden" name="item[employee_id]" value="{{ $userCurrent->id }}">
                        <input type="hidden" name="branch" value="{{ $branch }}" class="js-type-branch">
                        <button type="submit" class="btn btn-info" id="btn_req_warehouse">Request tới kho</button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="hidden">
        <table id="asset_allowcate_item_tmp">
            <tbody>
                <tr class="request-item" data-id="0" data-cat="">
                    <td rowspan="" class="rowspan" >
                        <select class="form-control category" name="cate_id[]">
                            @if (count($assetCategoriesList))
                                @foreach ($assetCategoriesList as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            @endif
                        </select>
                        <input type="hidden" name="ids[]" value="">
                        <div class="error js-err-cateId" data-cat-id=""></div>
                    </td>
                    <td>
                        <input type="number" class="form-control update-request-qty" name="qty[]" value="1"  min="1" max="10">
                    </td>
                    <td rowspan="" class="rowspan">
                        <button class="btn btn-danger btn-del-item" type="button"><i class="fa fa-trash"></i></button>
                    </td>
                </tr>
            </tbody>
        </table>
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
    var textOutOfCatQuantity = '<?php echo trans("asset::message.Out of category quantity") ?>';
    var textError = '<?php echo trans("project::me.Save error, please try again laster") ?>';
    var urlShowDetail = '{{ route('asset::asset.show_asset_to_warehouse') }}';
    var URL_SUBMIT = '{{ route('asset::asset.save_asset_to_warehouse') }}';
    var textAreYouWantoDelete = '<?php echo trans("asset::message.Are you sure want to delete?") ?>';
    $( document ).ready(function() {
        //remove item
        $('body').on('click', '.btn-del-item', function (e) {
            e.preventDefault();
            var idCat = $(this).closest('tr').data("cat");
            bootbox.confirm({
                message: textAreYouWantoDelete,
                className: 'modal-warning',
                callback: function (result) {
                    if (result) {
                        $('#list-asset-category').find(`tr[data-cat='${idCat}']`).remove();
                        if (idCat) {
                            let html = `<input type="hidden" name="idDel[]" value="${idCat}">`;
                            $('#list-asset-category').append(html);
                        }
                    }
                }
            });
        });

        //check dupliace category
        $('body').on('change', '.category', function () {
            var catIdNew = $(this).val();
            catId = $(this).closest('tr').attr('data-cat');
            $('.request-category-duplicate-error').hide();
            $('#btn_req_warehouse').prop('disabled', false);
            if(catIdNew === catId) {
                return false;
            }
            var categoryDuplicate = [];
            $reqItem = $('#list-asset-category').find('.request-item');
            $reqItem.each(function() {
                categoryDuplicate[$(this).attr('data-cat')] = $(this).attr('data-cat');
            });
            if (jQuery.inArray(catIdNew, categoryDuplicate) >= 0) {
                $('.request-category-duplicate-error').show();
                $('#btn_req_warehouse').prop('disabled', true);
                return false;
            }
            $(this).closest('tr').attr('data-cat', catIdNew);
            $(this).closest('tr').find('.js-err-cateId').attr('data-cat-id', catIdNew);
        });

        //add new item
        $('#btn_add_item').click(function (e) {
            e.preventDefault();
            var listTable = $('#list-asset-category tbody');

            var dupCatIds = [];
            listTable.find('tr.request-item .category').each(function (e) {
                dupCatIds.push($(this).val());
            });

            var item = $('#asset_allowcate_item_tmp tbody tr:first').clone().attr('data-cat', 0);
            var itemCat = item.find('.category');
            var newCatId = null;
            itemCat.find('option').each(function () {
                var optionVal = $(this).attr('value');
                if (!newCatId && dupCatIds.indexOf(optionVal) < 0) {
                    newCatId = optionVal;
                    return false;
                }
            });
            if (!newCatId) {
                bootbox.alert({
                className: 'modal-danger',
                message: textOutOfCatQuantity,
                });
                return false;
            }
            itemCat.val(newCatId);
            item.appendTo(listTable);
            itemCat.closest('tr').attr('data-cat', newCatId);
            itemCat.closest('tr').find('.js-err-cateId').attr('data-cat-id', newCatId);
        });

        //check number
        $('body').on('change', '.update-request-qty', function () {
            if ($(this).val() <= 0) {
                bootbox.alert({
                    message: 'Số lượng phải lớn hơn 0.',
                    className: 'modal-danger'
                });
            }
        });

        //Submit request to warehouse
        $('#btn_req_warehouse').click(function (e) {
            e.preventDefault();
            //check number
            var $iptQty = $('#form-asset-submit').find('.update-request-qty');
            var isFalse = false;
            $.each($iptQty, function (i, val) {
                if ($(val).val() <= 0) {
                    isFalse = true;
                }
            });
            if (isFalse) {
                bootbox.alert({
                    message: 'Vui lòng nhập đầy đủ thông tin.',
                    className: 'modal-danger'
                });
                return;
            }
            if ($('#list-asset-category tbody tr').length == 0) {
                bootbox.alert({
                    message: 'Vui lòng chọn ít nhất 1 tài sản.',
                    className: 'modal-danger'
                });
                return;
            }

            var $modalConfirm = $('#modal-choose-branch');
            $modalConfirm.modal('show');
        });
        $('#type-branch').change(function (e) {
            e.preventDefault();
            var typeBranch = $('#type-branch').val();
            $('.js-type-branch').val(typeBranch);
        });
        $('#send-choose-branch').click(function (e) {
            e.preventDefault();
            $('.js-err-cateId').html("");
            var typeBranch = $('#type-branch').val();
            if (typeBranch == 0) {
                $('.message-choose-branch').removeClass('hidden');
                return false;
            }
            $('.message-choose-branch').addClass('hidden');
            $(this).prop("disabled", true);
            var url = $(this).data('url');
            var $form = $('#form-asset-submit');
            var formData = new FormData($form[0]);
            var $button = $('#send-choose-branch');
            
            $.ajax({
                type: "POST",
                url: url,
                data: formData,
                processData: false,
                contentType: false,
            }).done(function (data) {
                $('#modal-choose-branch').modal('hide');
                if (!data.success) {
                    $.each(data.errors, function (i, val) {
                        $(`.js-err-cateId[data-cat-id='${i}']`).html(val.mess);
                    });
                }
                bootbox.alert({
                    message: data.message,
                    className: data.className
                });
                $button.prop("disabled", false);
            });
        });
    });
</script>
@endsection