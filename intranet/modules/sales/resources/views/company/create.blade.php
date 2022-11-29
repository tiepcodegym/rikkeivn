@extends('layouts.default')
<?php
use Carbon\Carbon;
use Rikkei\Core\View\View as ViewHelper;
use Rikkei\Core\View\CoreUrl;

?>
@section('title')
@if (isset($company) && $company->id)
{{ trans('sales::view.Company.Create.Update company') }}
@else
{{ trans('sales::view.Company.Create.Create company') }}
@endif
@endsection
<?php 
if ($company->id) {
    $urlSubmit = route('sales::company.postCreate', ['id' => $company->id]);
} else {
    $urlSubmit = route('sales::company.postCreate');
}
?>
@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link href="{{ asset('sales/css/sales.css') }}" rel="stylesheet" type="text/css" >
<link href="{{ asset('sales/css/customer_create.css') }}" rel="stylesheet" type="text/css" >
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css">
@endsection

@section('content')
@if(Session::has('message'))
<div class="flash_message">
    <div class="alert alert-success">
        <ul>
            <li>{{ trans('sales::message.Merge customers success.') }}</li>   
        </ul>
    </div>
</div>
@endif
<div class="row create-company-page">
    <div class="col-md-{{ !empty($company->id) ? '6' : '12' }}">
        <div class="box box-info">
            <div class="box-body">
                <form id="form-create-company" method="post" action="{{ $urlSubmit }}" 
                      enctype="multipart/form-data" autocomplete="off" class="form-horizontal form-sales-module {{ empty($company->id) ? 'form-center' : '' }}">
                    {!! csrf_field() !!}
                    @if($company->id)
                        <input type="hidden" name="company_id" value="{{ $company->id }}" />
                    @endif
                    <div class="row">

                        <!-- Basic information -->
                        <div class="box-header">
                            <h3 class="margin-top-0">{{ trans('sales::view.Basic info') }}</h3>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group form-label-left">
                                <label for="company" class="col-sm-3 control-label">{{ trans('sales::view.Company.Create.Name') }} <em class="required">*</em></label>
                                <div class="col-sm-9">
                                    <input name="company" class="form-control input-field" type="text" id="company" 
                                        value="{{ old('company', $company->company) }}" placeholder="{{ trans('sales::view.Company.Create.Name') }}" />
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group form-label-left">
                                <label for="name_ja" class="col-sm-3 control-label">{{ trans('sales::view.Name ja') }}</label>
                                <div class="col-sm-9">
                                    <input name="name_ja" class="form-control input-field" type="text" id="name_ja" 
                                        value="{{ $company->name_ja }}" placeholder="{{ trans('sales::view.Name ja') }}" />
                                </div>
                            </div>
                        </div>
                        <br/><br/>

                        <!-- Manage information -->
                        <div class="box-header pull-left width-100-per">
                            <h3>{{ trans('sales::view.Manage information') }}</h3>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group form-label-left">
                                <label for="manager_id" class="col-sm-3 control-label">{{ trans('sales::view.Management person') }} <em class="required">*</em></label>
                                <div class="col-sm-9 select_manager">
                                    <select id="manager_id" name="manager_id" class="form-control select-search has-search">
                                        <option value="0">&nbsp;</option>
                                        @foreach ($extractManagers as $extract)
                                        <option value="{{ $extract->id }}" {{ !empty($company->manager_id) && $extract->id == $company->manager_id ? 'selected' : '' }}>{{ ViewHelper::getNickName($extract->email) }}</option>
                                        @endforeach
                                        @foreach ($salers as $saler)
                                        <option value="{{ $saler->id }}" {{ !empty($company->manager_id) && $saler->id == $company->manager_id ? 'selected' : '' }}>{{ ViewHelper::getNickName($saler->email) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group form-label-left">
                                <label for="sale_support_id" class="col-sm-3 control-label">{{ trans('sales::view.Sale support') }}</label>
                                <div class="col-sm-9">
                                    <select id="sale_support_id" name="sale_support_id" class="form-control select-search has-search">
                                        <option value="">&nbsp;</option>
                                        @foreach ($salers as $saler)
                                        <option value="{{ $saler->id }}" {{ !empty($company->sale_support_id) && $saler->id == $company->sale_support_id ? 'selected' : '' }}>{{ ViewHelper::getNickName($saler->email) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <br/><br/>

                        <!-- Contract information -->
                        <div class="box-header pull-left width-100-per">
                            <h3>{{ trans('sales::view.Contract information') }}</h3>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group form-label-left">
                                <label for="contract_security" class="col-sm-3 control-label">{{ trans('sales::view.Information security') }} </label>
                                <div class="col-sm-9">
                                    <textarea name="contract_security" class="form-control input-field" type="text" id="contract_security" rows="5"
                                              placeholder="{{ trans('sales::view.Information security') }}" >{{ $company->contract_security }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group form-label-left">
                                <label for="contract_quality" class="col-sm-3 control-label">{{ trans('sales::view.Quality') }} </label>
                                <div class="col-sm-9">
                                    <textarea name="contract_quality" class="form-control input-field" type="text" id="contract_quality" rows="5"
                                              placeholder="{{ trans('sales::view.Quality') }}" >{{ $company->contract_quality }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group form-label-left">
                                <label for="contract_other" class="col-sm-3 control-label">{{ trans('sales::view.Other') }}</label>
                                <div class="col-sm-9">
                                    <textarea name="contract_other" class="form-control input-field" type="text" id="contract_other" rows="5"
                                              placeholder="{{ trans('sales::view.Other') }}" >{{ $company->contract_other }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 align-center">
                            <button class="btn-add" type="submit">
                                {{ trans('sales::view.Submit') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @if ($company->id)
    <div class="col-md-6">
        <div class="box box-info">
            <div class="box-body">
                <div class="box-header">
                    <h3 class="margin-top-0">{{ trans('sales::view.Customers list') }}</h3>
                </div>
                <div class="box-body">
                    @if (!empty($customerOfCompany))
                    <div style="height: 30px; margin-bottom: 10px;">
                        <button class="btn btn-primary btn-merge" onclick="mergeConfirm();" disabled=""><span class="glyphicon glyphicon-resize-small"></span> &nbsp;<span>{{ trans('sales::view.Merge') }}</span></button>
                    </div>
                    <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                        <thead>
                            <tr>
                                <th class="align-center"><input type="checkbox" class="check-parent" onclick="parentCheck(this);" /></th>
                                <th class="width-100">{{ trans('sales::view.CSS.Create.No.') }}</th>
                                <th>{{ trans('sales::view.CSS.Create.Customer name') }}</th>
                                <th>{{ trans('sales::view.Customer name (jp)') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($customerOfCompany as $key => $customer)
                            <tr>
                                <td class="align-center"><input type="checkbox" class="check-child" value="{{ $customer->id }}" data-name='{{ $customer->name }}' onclick="childCheck(this);" /></td>
                                <td>{{ $key + 1 }}</td>
                                <td><a title="{{ trans('sales::view.Redirect to customer information page') }}" href="{{ route('sales::customer.edit', $customer->id) }}">{{ $customer->name }}</a></td>
                                <td>{{ $customer->name_ja }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    @else
                    <h4 class="align-center">{{ trans('sales::view.Not found customer') }}</h4>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@if($company->id)
    @include('sales::company.include.project-list')
@endif
@include('sales::customer.include.modal_merge_confirm')
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>
<script src="{{ CoreUrl::asset('common/js/methods.validate.js') }}"></script>

<script>
    jQuery(document).ready(function ($) {
        $('#sale_support_id, #manager_id').select2();
        messages = {
            company : {
                required: '<?php echo trans('sales::view.Company.Create.Name required'); ?>',
                remote: '<?php echo trans('sales::view.Company.Create.Name company has exits in system'); ?>',
                rangelength: '<?php echo trans('sales::view.Company.Create.Name greater than', ['number'=> 255]); ?>',
            },
            manager_id: {
                valueNotEquals: '<?php echo trans("sales::message.Management account required") ?>',
            },
//            contract_security: {
//                required: '<?php echo trans("sales::message.Contract security required") ?>',
//            },
//            contract_quality: {
//                required: '<?php echo trans("sales::message.Contract quality required") ?>',
//            },
//            contract_other: {
//                required: '<?php echo trans("sales::message.Contract other required") ?>',
//            },
        };

        rules = {
            company : {
                required: true,
                remote:{
                    url: urlCheckNameExits,
                    type: 'POST',
                    data: {
                        companyId: function () {
                            return $('input[name="company_id"]').val();
                        },
                        companyName: function () {
                            return $('#company').val().trim();
                        },
                        _token: token,
                    },
                },
                rangelength: [1, 255],
            },
            manager_id: {
                valueNotEquals: "0",
            },
//            contract_security: {
//                required: true,
//            },
//            contract_quality: {
//                required: true,
//            },
//            contract_other: {
//                required: true,
//            },
        };

        $('#form-create-company').validate({
            rules: rules,
            messages: messages,
            errorPlacement: function (error, element) {
                if (element.attr("name") == "manager_id") {
                    error.insertAfter(".select_manager .select2-container");
                } else {
                    error.insertAfter(element);
                }
            },
        });
    });
    var routeMerge = '{{ route("sales::customer.merge") }}';
    var token = '{{ csrf_token() }}';
    var urlCheckNameExits = '{{ route('sales::company.checkExits') }}';
</script>
// generate datatable list project of company
<script>
    $(document).ready(function () {
        var urlProjectsList = '{{ route("sales::customer.getProjectsList", ['type' => 'company', 'id' => $company->id]) }}',
            messageEmptyData = '{{trans('sales::message.project not found')}}',
            textSInfo = '{{trans('sales::view.display :start to :end of :total record', ["start" => "_START_", "end" => "_END_", "total" => "_TOTAL_"])}}',
            textBefore = '{{trans('sales::view.Before')}}',
            textNext = '{{trans('sales::view.After')}}',
            isEdit = '{{ $company->id ? true : false}}';

        if (isEdit) {
            $('#projects-table').DataTable({
                processing: true,
                lengthChange: false,
                bFilter: false,
                serverSide: true,
                ajax: urlProjectsList,
                pageLength: 10,
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'name', name: 'name'},
                    {data: 'team_name', name: 'team_name'},
                    {data: 'email', name: 'email'},
                    {data: 'state', name: 'state'},
                    {data: 'type', name: 'type'},
                ],
                oLanguage: {
                    sEmptyTable: messageEmptyData,
                    sInfo: textSInfo,
                    oPaginate: {
                        sPrevious: textBefore,
                        sNext: textNext,
                    },
                },
            });
        }
    });
</script>
<script src="{{ CoreUrl::asset('sales/js/common.js') }}"></script>
@endsection