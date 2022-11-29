<?php
use Rikkei\Team\Model\Employee;
use Rikkei\Core\View\Form as FilterForm;
use Rikkei\Team\View\Config;
use Rikkei\FinesMoney\Model\FinesMoney;
use Rikkei\Core\View\View;

$employee = Employee::getTableName();
$obFinesMoney = new FinesMoney();
$types = $obFinesMoney->getTypes();
$isNote = [
    1 => trans('fines_money::view.yes_note'),
    0 => trans('fines_money::view.no_note'),
]
?>
<div class="table-responsive">
    <table class="table dataTable table-bordered working-time-tbl table-grid-data table-striped">
        <thead>
        <tr>
            <th></th>
            <th>{{ trans('core::view.NO.') }}</th>
            <th class="sorting{{ Config::getDirClass('employee_code') }} col-employee_code width-90"
                data-order="employee_code"
                data-dir="{{ Config::getDirOrder('employee_code') }}">{{ trans('fines_money::view.Code employees') }}</th>
            <th class="sorting {{ Config::getDirClass('name') }} col-name" data-order="name"
                data-dir="{{ Config::getDirOrder('name') }}">{{ trans('fines_money::view.Name employees') }}</th>
            <th class="sorting {{ Config::getDirClass('month') }} col-month" data-order="month"
                data-dir="{{ Config::getDirOrder('month') }}">{{ trans('fines_money::view.month') }}</th>
            <th class="sorting {{ Config::getDirClass('year') }} col-year" data-order="year"
                data-dir="{{ Config::getDirOrder('year') }}">{{ trans('fines_money::view.year') }}</th>
            <th class="sorting {{ Config::getDirClass('amount') }} col-amount" data-order="amount"
                data-dir="{{ Config::getDirOrder('amount') }}">{{ trans('fines_money::view.amount') }}</th>
            <th class="sorting {{ Config::getDirClass('type') }} col-type" data-order="type"
                data-dir="{{ Config::getDirOrder('type') }}">{{ trans('fines_money::view.type') }}</th>
            <th class="sorting {{ Config::getDirClass('note') }} col-note" data-order="note"
                data-dir="{{ Config::getDirOrder('note') }}">{{ trans('fines_money::view.Note') }}</th>
            <th class="sorting {{ Config::getDirClass('status_amount') }} col-status_amount" data-order="status_amount"
                data-dir="{{ Config::getDirOrder('status_amount') }}">{{ trans('fines_money::view.status_amount') }}</th>
            <th>{{ trans('fines_money::view.Action') }}</th>
        </tr>
        </thead>
        <tbody class="table-check-list checkbox-list" data-all="#tbl_check_all">
        <tr>
            <td><input type="checkbox" class="check-all" id="tbl_check_all" data-list=".table-check-list"></td>
            <td></td>
            <td>
                {{ Form::text("filter[$employee.employee_code]", FilterForm::getFilterData($employee.'.employee_code', null, $urlFilter), ['class' => 'form-control filter-grid', 'placeholder' => trans('fines_money::view.Code employees') ]) }}
            </td>
            <td>
                {{ Form::text("filter[$employee.name]", FilterForm::getFilterData($employee.'.name', null, $urlFilter), ['class' => 'form-control filter-grid', 'placeholder' => trans('fines_money::view.Name employees') ]) }}
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td>
                {{ Form::select('filter[fines_money.type]', [null => trans('fines_money::view.select_all')] + $types,
                                                                     FilterForm::getFilterData('fines_money.type', null, $urlFilter),
                                                                      ['class' => 'form-control select-grid filter-grid']) }}
            </td>
            <td>
                <div class="row">
                    <div class="col-md-4">
                        {{ Form::select('filter[search][is_note]', [null => trans('fines_money::view.select_all')] + $isNote,
                                                                       FilterForm::getFilterData('search', 'is_note', $urlFilter),
                                                                       ['class' => 'form-control select-search select-grid filter-grid']) }}
                    </div>
                    <div class="col-md-8">
                        {{ Form::text("filter[note]", FilterForm::getFilterData('note', null, $urlFilter), ['class' => 'form-control filter-grid', 'placeholder' => trans('fines_money::view.Note') ]) }}
                    </div>
                </div>
            </td>
            <td>
                {{ Form::select('filter[status_amount]', [null => trans('fines_money::view.select_all')] + $status,
                                                                     FilterForm::getFilterData('status_amount', null, $urlFilter),
                                                                      ['class' => 'form-control select-grid filter-grid']) }}
            </td>
            <td></td>
        </tr>
        @if(isset($collectionModel) && count($collectionModel))
            @php $key = View::getNoStartGrid($collectionModel); @endphp
            @foreach($collectionModel as $item)
                <?php $color = $obFinesMoney->getColorByStatus($item->status_amount); ?>
                <tr style="background: {{ $color }}">
                    {{ Form::hidden('type', $item->type, ['class' => 'input']) }}
                    {{ Form::hidden('id', $item->id, ['class' => 'input']) }}
                    <td>
                        <input type="checkbox" class="check-item" value="{{ $item->id }}">
                    </td>
                    <td>{{ $key++ }}</td>
                    <td>{{ $item->employee_code }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->month }}</td>
                    <td>{{ $item->year }}</td>
                    <td>
                        <span class="{{ $item->type == FinesMoney::TYPE_LATE ? 'text' : '' }} text_amount">{{ $obFinesMoney->formatMoney($item->amount) }}</span>
                        @if($item->type == FinesMoney::TYPE_LATE)
                            {{ Form::text('amount', $obFinesMoney->formatMoney($item->amount), ['class' => 'form-control hidden input']) }}
                        @endif
                    </td>
                    <td>
                        {{ data_get($types, $item->type) }}
                    </td>
                    <td>
                        <span class="text text_note">{{ $item->note ? $item->note : '' }}</span>
                        {{ Form::text('note', $item->note, ['class' => 'form-control hidden input']) }}
                        <span class="error-note"></span>
                    </td>
                    <td>
                        <span class="text text_status_amount">{{ data_get($status, $item->status_amount) }}</span>
                        {{ Form::select('status_amount', $status, $item->status_amount, ['class' => 'form-control hidden input']) }}
                    </td>
                    <td class="col-actions">
                        <button type="button" title="{{ trans('team::view.Edit') }}"
                                class="btn btn-success btn-edit-money"
                                data-id="{{ $item->id }}" data-type="{{ $item->type }}">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-primary btn-money-save btn-editing hidden"
                                data-action="{{ route('fines-money::fines-money.manage.edit-money') }}">
                            <i class="fa fa-save"></i>
                        </button>
                        <button type="button" title="{{ trans('team::view.Cancel') }}"
                                class="btn btn-danger btn-money-cancel btn-editing hidden">
                            <i class="fa fa-close"></i>
                        </button>
                    </td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="10" class="text-center">
                    <h2 class="no-result-grid">{{trans('files::view.No results found')}}</h2>
                </td>
            </tr>
        @endif
        </tbody>
    </table>
</div>

<div class="box-body">
    @include('team::include.pager', ['urlSubmitFilter' => $urlFilter])
</div>