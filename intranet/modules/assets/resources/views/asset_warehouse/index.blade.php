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
?>

@section('title', 'Danh sách yêu cầu tài sản từ kho của nhân viên')

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
        <div class="col-lg-4">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <div class="pull-left">
                        <h3 class="box-title request-box-title">Danh sách nhân viên</h3>
                    </div>
                    <div class="pull-right">
                        <button class="btn btn-primary btn-reset-filter">
                            <span>Reset bộ lọc <i class="fa fa-spin fa-refresh hidden"></i></span>
                        </button>
                        <button class="btn btn-primary btn-search-filter">
                            <span>Tìm kiếm <i class="fa fa-spin fa-refresh hidden"></i></span>
                        </button>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body no-padding">
                    <div class="table-responsive">
                        <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                            <thead>
                                <tr>
                                    <th class="width-30">{{ trans('core::view.NO.') }}</th>
                                    <th class="col-title" data-order="name" data-dir="">Nhân viên yêu cầu</th>
                                </tr>
                            </thead>
                            <tbody class="js-tbody-index">
                                <tr>
                                    <td></td>
                                    <td>
                                        <input type="text" name="filter[emp.name]" class="form-control filter-grid" placeholder="{{ trans('asset::view.Search') }}..."
                                               value="{{ CoreForm::getFilterData('emp.name') }}">
                                    </td>
                                </tr>
                                @if (count($collectionModel))
                                    @foreach ($collectionModel as $key => $item)
                                        <tr data-id="{{ $item->employee_id }}" class="js-employee tr-emp">
                                            <td>{{ $key+1 }}</td>
                                            <td>{{ $item->emp_name }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="2">
                                            <h4 class="text-center">{{ trans('asset::view.No results data') }}</h4>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="box-body">
                        @include('team::include.pager')
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title request-box-title">{{ trans('asset::view.Asset request information') }}</h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body js-box-body">
                    
                </div>
            </div>
        </div>
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
    var urlShowDetail = '{{ route('asset::asset.show_asset_to_warehouse') }}';
    var URL_SUBMIT = '{{ route('asset::asset.save_asset_to_warehouse') }}';
    var textAreYouWantoDelete = '<?php echo trans("asset::message.Are you sure want to delete?") ?>';
    $( document ).ready(function() {
        $('.js-employee').click(function () {
            $('.js-employee').removeClass('tr-active');
            $(this).addClass('tr-active');
            let id = $(this).data("id");
            $.ajax({
                url: urlShowDetail,
                type: 'get',
                data: {
                    id: id
                },
                success: function (data) {
                    $(".js-box-body").html(data.html);
                    initSelectAsset();
                },
                error: function () {
                    alert('ajax fail to fetch data');
                },
            });
        });

        function initSelectAsset() {
            $('.select-search-asset').each(function () {
                var dom = $(this);
                var catId = dom.closest('tr').attr('data-cat');
                var branch = dom.data('branch');
                dom.select2({
                    minimumInputLength: 2,
                    ajax: {
                        url: dom.data('remote-url'),
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            var excludeIds = [];
                            $('[data-cat="'+ catId +'"] .select-search-asset').each(function () {
                                var value = $(this).val();
                                if (value) {
                                    excludeIds.push(value);
                                }
                            });
                            return {
                                q: params.term, // search term
                                page: params.page,
                                cat_id: catId,
                                branch: branch,
                                employee_id: $('input[name="item[employee_id]"]').val(),
                                'exclude_ids[]': excludeIds,
                            };
                        },
                        processResults: function (data, params) {
                            params.page = params.page || 1;
                            return {
                                results: data.items,
                                pagination: {
                                    more: (params.page * 20) < data.total_count
                                },
                            };
                        },
                        cache: true,
                    },
                });
            });
        };

        //update-request-qty
        $('body').on('change', '.update-request-qty', function () {
            var input = $(this);
            var catId = input.data('cat-id');
            var id = input.data('id');
            var quantity = input.val();
            var iconLoading = $('#update_qty_loading');
            if (quantity > 20) {
                bootbox.alert({
                    message: 'Too large quantity!',
                    className: 'modal-danger',
                });
                return;
            }

            if (!catId || !quantity || !iconLoading.hasClass('hidden')) {
                return;
            }

            var trCats = $('#list-asset-category tr[data-id="'+ id +'"]');
            trCats.eq(0).find('td.rowspan').each(function () {
                $(this).attr('rowspan', quantity);
            });
            var trLen = trCats.length;
            if (trLen > quantity) {
                for (var i = trLen; i > quantity; i--) {
                    trCats.eq(i - 1).remove();
                    if (trLen == 1) {
                        let html = `<input type="hidden" name="id${id}" value="${id}">
                                <input type="hidden" name="qty${id}" value="0">
                                <input type="hidden" name="asset_id${id}[]" value="">`;
                        $('#form-asset-warehouse').append(html);
                    }                    
                }
            } else if (trLen < quantity) {
                var trCatLast = trCats.last();
                for (var i = 0; i < quantity - trLen; i++) {
                    var trItem = trCatLast.clone();
                    trItem.find('td.rowspan').remove();
                    trItem.find('td .select2-container').remove();
                    trItem.find('td select').val('');
                    trItem.find('td select option').remove();
                    trItem.insertAfter(trCatLast);
                }
            } else {
                //none
            }
            initSelectAsset();
        });

        /*
        * remove item
        */
        $('body').on('click', '.btn-del-item', function (e) {
            e.preventDefault();
            var id = $(this).data("id");
            bootbox.confirm({
                message: textAreYouWantoDelete,
                className: 'modal-warning',
                callback: function (result) {
                    if (result) {
                        $('#list-asset-category').find(`tr[data-id='${id}']`).remove();
                        let html = `<input type="hidden" name="id${id}" value="${id}">
                                    <input type="hidden" name="qty${id}" value="0">
                                    <input type="hidden" name="asset_id${id}[]" value="">`;
                        $('#form-asset-warehouse').append(html);
                    }
                }
            });
        });

        //check all checkbox 
        $('body').on('click', ".js-all-checkbox", function() {
            $('body').find("input[type=checkbox]").prop('checked', $(this).prop('checked'));
        });

        //Save
        $('body').on('click', '#btn_req_allocation', function (e) {
            e.preventDefault();
            $('body').find('.color-red').text('');
            $(this).prop("disabled", true);
            $('body').find('.js-checkbox')
            let $this = $(this),
                $button = $('body').find('#btn_req_allocation'),
                is_false = false,
                is_no_checked = true,
                $checkbox = $('body').find('.js-checkbox');
            $checkbox.each(function( i, val ) {
                if ($(val).is(":checked"))
                {
                    is_no_checked = false;
                    let id = $(val).data('id'),
                        $selects = $('body').find(`.select-search-asset[data-id='${id}']`);
                    $selects.each(function( index ) {
                        let asset_id = $(this).val();
                        if (!asset_id) {
                            is_false = true;
                            let html = '<div class="color-red">Trường này bắt buộc nhập</div>';
                            $(this).before(html);
                        }
                    });
                }
            });
            if (is_no_checked) {
                bootbox.alert({
                    message: 'Chọn ít nhất 1 tài sản để cấp phát!',
                    className: 'modal-danger'
                });
            }
            if (is_false || is_no_checked) {
                $button.prop("disabled", false);
                return false;
            }

            let $form = $this.closest('form'),
                formData = new FormData($form[0]);
            $.ajax({
                type: "POST",
                url: URL_SUBMIT,
                data: formData,
                processData: false,
                contentType: false,
            }).done(function (data) {
                if (data.success) {                        
                    $.each(data.requests, function (i, val) {
                        let id = val.id;
                        if (val.status == 2) {
                            $('#list-asset-category').find(`tr[data-id='${id}']`).remove();
                        } else {
                            let quantity = val.unallocate;
                            $('body').find(`input[name=qty${id}]`).val(quantity);
                            $('body').find('.select-search-asset[data-id="'+ id +'"]').val(null).trigger("change");
                            $('body').find(".js-checkbox").prop("checked", false);

                            var $trCats = $('#list-asset-category tr[data-id="'+ id +'"]');
                            $trCats.eq(0).find('td.rowspan').each(function () {
                                $(this).attr('rowspan', quantity);
                            });
                            var trLen = $trCats.length;
                            if (trLen > quantity) {
                                for (var i = trLen; i > quantity; i--) {
                                    $trCats.eq(i - 1).remove();
                                    if (trLen == 1) {
                                        let html = `<input type="hidden" name="id${id}" value="${id}">
                                                <input type="hidden" name="qty${id}" value="0">
                                                <input type="hidden" name="asset_id${id}[]" value="">`;
                                        $('#form-asset-warehouse').append(html);
                                    }                    
                                }
                            } else if (trLen < quantity) {
                                var trCatLast = $trCats.last();
                                for (var i = 0; i < quantity - trLen; i++) {
                                    var trItem = trCatLast.clone();
                                    trItem.find('td.rowspan').remove();
                                    trItem.find('td .select2-container').remove();
                                    trItem.find('td select').val('');
                                    trItem.find('td select option').remove();
                                    trItem.insertAfter(trCatLast);
                                }
                            } else {
                                //none
                            }
                            initSelectAsset();
                        }
                    });

                    let empId = data.empId,
                        $domTrList = $('.js-tbody-index tr');
                        $listTrDetail = $('body').find('#list-asset-category tbody tr');
                    if ($listTrDetail.length == 0) {
                        $('body').find(`.js-tbody-index tr[data-id='${empId}']`).remove();
                        $(".js-box-body").html('');
                    }
                    if ($domTrList.length < 2) {
                        let html = `<tr>
                                        <td colspan="2">
                                            <h4 class="text-center">{{ trans('asset::view.No results data') }}</h4>
                                        </td>
                                    </tr>`;
                        $('.js-tbody-index').html(html);
                    }
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
