<?php
use Rikkei\Sales\View\OpporView;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Team\View\Config;
use Rikkei\Team\View\Permission;
use Rikkei\Sales\Model\ReqOpportunity;

$listLanguages = OpporView::listLanguages();
$listLocations = OpporView::listLocations();
$listStatuses = OpporView::statusLabels();
$permissEdit = Permission::getInstance()->isAllow('sales::req.oppor.edit');
$permissView = Permission::getInstance()->isAllow('sales::req.apply.oppor.view');
$permissOpporIds = ReqOpportunity::permissEditOppors($collectionModel->lists('id')->toArray());
?>

@extends('layouts.default')

@section('title', trans('sales::view.List opportunity'))

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css">
<link rel="stylesheet" href="{{ CoreUrl::asset('sales/css/opportunity.css') }}">
@stop

@section('content')
<div class="box box-primary">
    <div class="box-body">
        <div class="row">
            <div class="col-md-6">
                @if ($permissEdit)
                <a href="{{ route('sales::req.apply.oppor.view') }}" class="btn btn-success"><i class="fa fa-plus"></i> {{ trans('sales::view.Create') }}</a>
                @endif
            </div>
            <div class="col-md-6 text-right">
                @if ($permissEdit)
                <button id="export_oppors" class="btn btn-success" data-url="{{ route('sales::req.oppor.export') }}">
                    <i class="fa fa-download"></i> 
                    {{ trans('sales::view.Export') }}
                </button>
                @endif
                <div class="form-inline">
                    @include('team::include.filter')
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive" style="min-height: 300px;">
        <table class="table table-hover table-striped dataTable table-bordered">
            <thead>
                <tr>
                    @if ($permissEdit)
                    <th><input type="checkbox" class="check-all"></th>
                    @endif
                    <th>No.</th>
                    <th class="sorting {{ Config::getDirClass('name') }} col-name" data-order="name" data-dir="{{ Config::getDirOrder('name') }}">{{ trans('sales::view.Name') }}</th>
                    <th class="sorting {{ Config::getDirClass('number_member') }} col-name" data-order="number_member" data-dir="{{ Config::getDirOrder('number_member') }}">{{ trans('sales::view.Number of employees') }}</th>
                    <th class="sorting {{ Config::getDirClass('prog_names') }} col-name" data-order="prog_names" data-dir="{{ Config::getDirOrder('prog_names') }}">{{ trans('sales::view.Program language') }}</th>
                    <th class="sorting {{ Config::getDirClass('lang') }} col-name" data-order="lang" data-dir="{{ Config::getDirOrder('lang') }}">{{ trans('sales::view.Language') }}</th>
                    <th class="sorting {{ Config::getDirClass('duration') }} col-name" data-order="duration" data-dir="{{ Config::getDirOrder('duration') }}">{{ trans('sales::view.Duration') }}</th>
                    <th class="sorting {{ Config::getDirClass('duedate') }} col-name" data-order="duedate" data-dir="{{ Config::getDirOrder('duedate') }}">{{ trans('sales::view.Deadline') }}</th>
                    <th class="sorting {{ Config::getDirClass('sale_name') }} col-name" data-order="sale_name" data-dir="{{ Config::getDirOrder('sale_name') }}">{{ trans('sales::view.Salesperson') }}</th>
                    @if ($permissEdit)
                    <th class="sorting {{ Config::getDirClass('customer_name') }} col-name" data-order="customer_name" data-dir="{{ Config::getDirOrder('customer_name') }}">{{ trans('sales::view.Company') }}</th>
                    <th class="sorting {{ Config::getDirClass('curator') }} col-name" data-order="curator" data-dir="{{ Config::getDirOrder('curator') }}">{{ trans('sales::view.Customer') }}</th>
                    <th class="sorting {{ Config::getDirClass('curator_email') }} col-name white-space-nowrap" data-order="curator_email" data-dir="{{ Config::getDirOrder('curator_email') }}">{{ trans('sales::view.Email') }}</th>
                    @endif
                    <th width="150" class="sorting {{ Config::getDirClass('status') }} col-name" data-order="status" data-dir="{{ Config::getDirOrder('status') }}">{{ trans('sales::view.Status') }}</th>
                    <th class="sorting {{ Config::getDirClass('code') }} col-name" data-order="code" data-dir="{{ Config::getDirOrder('code') }}">{{ trans('sales::view.Code') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    @if ($permissEdit)
                    <td></td>
                    @endif
                    <td></td>
                    <td>
                        <input type="text" name="filter[req_op.name]" class="form-control filter-grid" 
                               placeholder="{{ trans('sales::view.Search') }}..." value="{{ CoreForm::getFilterData('req_op.name') }}"
                               style="min-width: 200px;">
                    </td>
                    <td>
                        <input type="text" name="filter[number][req_op.number_member]" class="form-control filter-grid" 
                               placeholder="{{ trans('sales::view.Search') }}..." value="{{ CoreForm::getFilterData('number', 'req_op.number_member') }}">
                    </td>
                    <td>
                        <div class="filter-multi-select" style="min-width: 180px;">
                            <?php
                            $filterProgIds = CoreForm::getFilterData('excerpt', 'prog_ids');
                            $filterProgIds = $filterProgIds ? $filterProgIds : [];
                            ?>
                            <select name="filter[excerpt][prog_ids][]" class="form-control multi-select-bst filter-grid bootstrap-multiselect" multiple>
                                @foreach ($programs as $value => $label)
                                <option value="{{ $value }}" {{ in_array($value, $filterProgIds) ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </td>
                    <td>
                        <select name="filter[req_op.lang]" class="form-control select-grid filter-grid select-search"
                                style="width: 110px;">
                            <option value="">&nbsp;</option>
                            @foreach ($listLanguages as $value => $label)
                            <option value="{{ $value }}" {{ CoreForm::getFilterData('req_op.lang') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="text" name="filter[req_op.duration]" class="form-control filter-grid" 
                               placeholder="{{ trans('sales::view.Search') }}..." value="{{ CoreForm::getFilterData('req_op.duration') }}">
                    </td>
                    <td>
                        <input type="text" name="filter[req_op.duedate]" class="form-control filter-grid" 
                               placeholder="{{ trans('sales::view.Search') }}..." value="{{ CoreForm::getFilterData('req_op.duedate') }}">
                    </td>
                    <td>
                        <input type="text" name="filter[sale.email]" class="form-control filter-grid" 
                               placeholder="{{ trans('sales::view.Search') }}..." value="{{ CoreForm::getFilterData('sale.email') }}">
                    </td>
                    @if ($permissEdit)
                    <td>
                        <input type="text" name="filter[req_op.customer_name]" class="form-control filter-grid" 
                               placeholder="{{ trans('sales::view.Search') }}..." value="{{ CoreForm::getFilterData('req_op.customer_name') }}">
                    </td>
                    <td>
                        <input type="text" name="filter[req_op.curator]" class="form-control filter-grid" 
                               placeholder="{{ trans('sales::view.Search') }}..." value="{{ CoreForm::getFilterData('req_op.curator') }}">
                    </td>
                    <td>
                        <input type="text" name="filter[req_op.curator_email]" class="form-control filter-grid" 
                               placeholder="{{ trans('sales::view.Search') }}..." value="{{ CoreForm::getFilterData('req_op.curator_email') }}">
                    </td>
                    @endif
                    <td>
                        <select name="filter[req_op.status]" class="form-control select-grid filter-grid select-search"
                                style="width: 110px;">
                            <option value="">&nbsp;</option>
                            @foreach ($listStatuses as $value => $label)
                            <option value="{{ $value }}" {{ CoreForm::getFilterData('req_op.status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="text" name="filter[req_op.code]" class="form-control filter-grid" 
                               placeholder="{{ trans('sales::view.Search') }}..." value="{{ CoreForm::getFilterData('req_op.code') }}">
                    </td>
                    <td></td>
                </tr>
                @if (!$collectionModel->isEmpty())
                <?php
                $perPage = $collectionModel->perPage();
                $currPage = $collectionModel->currentPage();
                ?>
                @foreach ($collectionModel as $order => $item)
                <?php
                $permissEditItem = ReqOpportunity::checkPermissInList($item->id, $permissOpporIds, $item->created_by);
                ?>
                <tr>
                    @if ($permissEdit)
                    <td><input type="checkbox" class="check-item" value="{{ $item->id }}"></td>
                    @endif
                    <td>{{ $order + 1 + ($currPage - 1) * $perPage }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->number_member }} {{ isset($roles[$item->role]) ? $roles[$item->role] : null }}</td>
                    <td>{{ $item->prog_names }}</td>
                    <td>{{ $item->getLangLabel($listLanguages) }}</td>
                    <td>{{ $item->duration }}</td>
                    <td>{{ $item->duedate }}</td>
                    <td>{{ CoreView::getNickName($item->sale_name) }}</td>
                    @if ($permissEdit)
                    <td class="white-space-nowrap">{{ $permissEditItem ? $item->customer_name : 'N/A' }}</td>
                    <td class="white-space-nowrap">{{ $permissEditItem ? $item->curator : 'N/A' }}</td>
                    <td class="white-space-nowrap">{{ $permissEditItem ? $item->curator_email : 'N/A' }}</td>
                    @endif
                    <?php
                    $percentRecieve = $item->number_member ? number_format($item->number_recieved * 100 / $item->number_member, 2, '.', ',') : 0;
                    ?>
                    <td>{!! OpporView::renderStatusHtml($item->status, $listStatuses, 'label', $percentRecieve) !!}</td>
                    <td>{{ $item->code }}</td>
                    <td class="white-space-nowrap">
                        @if ($permissView || $permissEdit)
                            <a href="{{ route('sales::req.apply.oppor.view', $item->id) }}" class="btn btn-info"
                               title="{{ trans('sales::view.Detail') }}"><i class="fa fa-info-circle"></i></a>
                        @endif
                        @if ($permissEditItem)
                            {!! Form::open(['method' => 'delete', 'route' => ['sales::req.oppor.delete', $item->id], 'class' => 'form-inline no-validate']) !!}
                            <button type="submit" class="btn btn-danger btn-del-item" title="{{ trans('sales::view.Delete') }}"
                                    data-noti="{{ trans('sales::message.Are you sure want to delete?') }}">
                                <i class="fa fa-trash"></i>
                            </button>
                            {!! Form::close() !!}
                        @endif
                    </td>
                </tr>
                @endforeach
                @else
                <tr>
                    <td colspan="{{ $permissEdit ? 12 : 11 }}"><h4 class="text-center">{{ trans('sales::message.None item found') }}</h4></td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    
    <div class="box-body">
        @include('team::include.pager')
    </div>
</div>
@stop

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
<script src="{{ CoreUrl::asset('sales/js/opportunity.js') }}"></script>
<script>
    RKfuncion.bootstapMultiSelect._overfollowClose =  function(dom) {
        var wrapper = dom.closest('.multiselect2-wrapper.flag-over-hidden');
        if (wrapper.length) {
            wrapper.removeAttr('style');
        }
        $('.btn-search-filter').click();
    };

    var textNoneItemChecked = '{!! trans("sales::message.None item checked") !!}';
    $('.btn-del-item').click(function () {
        var btn = $(this);
        bootbox.confirm({
            className: 'modal-warning',
            message: btn.data('noti'),
            callback: function (result) {
                if (result) {
                    btn.closest('form').submit();
                }
            },
        });
        return false;
    });
</script>
@stop