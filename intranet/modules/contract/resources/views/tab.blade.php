<?php

use Rikkei\Contract\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View;
use Rikkei\Contract\Model\ContractModel;
use Rikkei\Resource\View\getOptions;
use Carbon\Carbon;

$startDateFilter = Form::getFilterData('except', "$contractTable.start_at", $urlFilter);
$endDateFilter = Form::getFilterData('except', "$contractTable.end_at", $urlFilter);
$filterEmployeeJob = Form::getFilterData('except', "employee_job", $urlFilter);

?>
<div id="tab-content" class="table-responsive">
    <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
        <thead>
            <tr>
                <th style="width: 20px" class="col-id">{{ trans('contract::vi.NO.') }}</th>
                <th style="width: 80px" class="sorting {{ Config::getDirClass('employee_code',$urlFilter) }}" data-order="employee_code" data-dir="{{ Config::getDirOrder('employee_code',$urlFilter) }}">{{ trans('contract::vi.employee code') }}</th>
                <th style="width: 150px" class="sorting {{ Config::getDirClass('employee_name', $urlFilter) }} col-id" data-order="employee_name" data-dir="{{ Config::getDirOrder('employee_name', $urlFilter) }}">{{ trans('contract::vi.employee name') }}</th>
                <th style="width: 100px" class="sorting {{ Config::getDirClass('employee_email', $urlFilter) }} col-id" data-order="employee_email" data-dir="{{ Config::getDirOrder('employee_email', $urlFilter) }}">{{ trans('contract::vi.email') }}</th>
                <th style="width: 150px"  class="col-id" >{{ trans('contract::vi.job') }}</th>
                @if ($currentTab !== 'none')
                <th style="width: 100px" class="sorting {{ Config::getDirClass('contract_type', $urlFilter) }} col-id" data-order="contract_type" data-dir="{{ Config::getDirOrder('contract_type', $urlFilter) }}">{{ trans('contract::vi.contract type') }}</th>
                <th style="width: 80px" class="sorting {{ Config::getDirClass('start_at', $urlFilter) }} col-id" data-order="start_at" data-dir="{{ Config::getDirOrder('start_at', $urlFilter) }}">{{ trans('contract::vi.start at') }}</th>
                <th style="width: 80px" class="sorting {{ Config::getDirClass('end_at', $urlFilter) }} col-id" data-order="end_at" data-dir="{{ Config::getDirOrder('end_at', $urlFilter) }}">{{ trans('contract::vi.end at') }}</th>
                @endif
                <th style="width: 180px;">#</th>
            </tr>
        </thead>
        <tbody class="checkbox-list table-check-list" data-all="#tbl_check_all">
            <tr class="filter-input-grid">
                <td></td>
                <td>
                    <div class="row">
                        <div class="col-md-12">
                            <input type="text" name="filter[employee_code]" value="{{ Form::getFilterData('employee_code', null, $urlFilter) }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                        </div>
                    </div>
                </td>
                <td>
                    <div class="row">
                        <div class="col-md-12">
                            <input type="text" name="filter[{{$employeeTable}}.name]" value="{{ Form::getFilterData("{$employeeTable}.name", null, $urlFilter) }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                        </div>
                    </div>
                </td>
                <td>
                    <div class="row">
                        <div class="col-md-12">
                            <input type="text" name="filter[{{$employeeTable}}.email]" value="{{ Form::getFilterData("{$employeeTable}.email", null, $urlFilter) }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                        </div>
                    </div>
                </td>
                <td>
                    <div class="row">
                        <div class="col-md-12">
                            <input type="text" name="filter[except][employee_job]" value="{{ $filterEmployeeJob }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                        </div>
                    </div>
                </td>
                @if ($currentTab !== 'none')
                <td>
                    <div class="row">
                        <div class="col-md-12">
                            <select name="filter[{{$contractTable}}.type]" class="select-grid filter-grid form-control" autocomplete="off">
                                <option value=""></option>
                                <?php
                                $filterTypeSelected = Form::getFilterData("{$contractTable}.type", null, $urlFilter);
                                foreach ($allTypeContract as $k => $contractInfo) {
                                    $selectedWorkingType = (int) $filterTypeSelected  == (int) $k ? 'selected'  : '';
                                    echo "<option $selectedWorkingType value='$k' >$contractInfo</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="row">
                        <div class="col-md-12">
                            <input type="text" name="filter[except][{{$contractTable}}.start_at]" value="{{ $startDateFilter }}" placeholder="dd-mm-yyyy" class="filter-grid form-control" autocomplete="off" />
                        </div>
                    </div>
                </td>
                <td>
                    <div class="row">
                        <div class="col-md-12">
                            <input type="text" name="filter[except][{{$contractTable}}.end_at]" value="{{ $endDateFilter }}" placeholder="dd-mm-yyyy" class="filter-grid form-control" autocomplete="off" />
                        </div>
                    </div>
                </td>
                @endif
                <td>&nbsp;</td>
            </tr>
            @if($collectionModel)
            <?php $i = View::getNoStartGrid($collectionModel); ?>
            @foreach($collectionModel as $item)
            <tr>
                <td>{{ $i }}</td>
                <td>{{ $item->employee_code }}</td>
                <td>{{ $item->employee_name }}</td>
                <td>{{ $item->employee_email }}</td>
                <td>{!!$item->employee_job!!}</td>
                @if ($currentTab !== 'none')
                <td>{{($currentTab !== 'none') && isset($allTypeContract[$item->contract_type]) ? $allTypeContract[$item->contract_type]  : ''}} @if ($item->contract_type == getOptions::WORKING_OFFICIAL) {{ContractModel::getCountContractWorkingOff($item->employee_id, $item->id)}}@endif</td>
                <td>{{($currentTab !== 'none') && $item->start_at?Carbon::parse($item->start_at)->format('d-m-Y') :''}}</td>
                <td>{{($currentTab !== 'none') && $item->end_at?Carbon::parse($item->end_at)->format('d-m-Y'):''}}</td>
                @endif
                <td>
                    @if($currentTab!== 'none')
                    <a href="{{ route('contract::manage.contract.show', ['id' => $item->id]) }}" title="View contract" class="btn btn-primary">
                        <i class="fa fa-eye"></i>
                    </a>
                    @if($item->isContractLast())
                    <a href="{{ route('contract::manage.contract.edit', ['id' => $item->id]) }}" title="Edit contract" class="btn btn-primary">
                        <i class="fa fa-edit"></i>
                    </a>
                    @endif
                    <a onclick="synchronize({{$item->id}})" title="Synchronize" class="btn btn-primary">
                        <i class="fa fa-send"></i>
                    </a>
                    <button class="btn-delete delete-contract-confirm" title="Delete" type="button" data-url-ajax="{{URL::route('contract::manage.contract.delete',['id'=>$item->id])}}">
                        <span><i class="fa fa-trash"></i></span>
                    </button>
                    @endif
                </td>
            </tr>
            <?php $i++; ?>
            @endforeach
            @else
            <tr>
                <td colspan="9" class="text-center">
                    <h2 class="no-result-grid">{{trans('core::view.No results found')}}</h2>
                </td>
            </tr>
            @endif
        </tbody>
    </table>
</div>

<div class="box-body">
    @include('contract::pager', ['urlSubmitFilter' => $urlFilter])
</div>