@extends('layouts.default')
<?php
use Rikkei\Core\View\Form;
use Carbon\Carbon;
use Rikkei\Core\View\View;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\View as ViewHelper;
use Rikkei\Core\Model\CoreConfigData;
use Illuminate\Support\Str;
use Rikkei\Team\View\Permission;
use Rikkei\Core\View\CoreUrl;

$customerTable = Rikkei\Sales\Model\Customer::getTableName();
$companyTable = Rikkei\Sales\Model\Company::getTableName();
$collectionModel = $allCustomer;
?>
@section('title')
    {{ trans('sales::view.Customer list') }}
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link href="{{ asset('sales/css/customer_index.css') }}" rel="stylesheet" type="text/css">
@endsection

@section('content')
    @include('contract::message-alert')
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-info">
                <div class="box-body">
                    <div class="col-md-6">
                        {{-- <a href="{{ route('sales::customer.create') }}" class="btn btn-edit"><span
                                class="glyphicon glyphicon-plus"></span>
                            &nbsp;<span>{{ trans('sales::view.Create new') }}</span></a> --}}
                        <button class="btn btn-primary btn-merge" onclick="mergeConfirm();" disabled=""><span
                                class="glyphicon glyphicon-resize-small"></span>
                            &nbsp;<span>{{ trans('sales::view.Merge') }}</span></button>
                    </div>
                    <div class="col-md-6">
                        <div class="col-sm-5">
                        </div>
                        <button class="btn btn-success btn-submit-action col-sm-2" onclick="fc_show_model_upload_file()"
                            id="modal_customer_import_excel" data-url="{!! URL::route('sales::customer.import-excel') !!}" style="margin-right: 10px">
                            <i class="fa fa-upload"></i>
                            {!! trans('sales::vi.Import customer') !!}
                        </button>

                        <button class="btn btn-primary btn-reset-filter col-sm-2" style="margin-right: 10px">
                            <span>{{ trans('team::view.Reset filter') }} <i
                                    class="fa fa-spin fa-refresh hidden"></i></span>
                        </button>
                        <button class="btn btn-primary btn-search-filter col-sm-2">
                            <span>{{ trans('team::view.Search') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
                        </button>
                    </div>
                </div>
                <!-- set up the modal to start hidden and fade in and out -->
                <div id="model-import-excel" class="modal fade" data-backdrop="static" data-keyboard="false">
                    <form name="frmMain" id="frmMain" enctype="multipart/form-data" method="POST"
                        action="{{ route('sales::customer.import-excel') }}">
                        {!! csrf_field() !!}
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <!-- dialog body -->
                                <div class="modal-body">
                                    <h4>{{ trans('sales::vi.Select file to import') }} <em style="color:red">*</em></span>
                                    </h4>
                                    <input type="file" name="fileToUpload" id="fileToUpload" accept=".xlsx, .xls">
                                    <label style="display: none;color: red"
                                        id="error-import-excel">{{ trans('contract::message.File import is not null') }}
                                    </label>
                                    <br/>
                                    {!!trans('sales::vi.help-import-excel')!!}
                                </div>
                                <div class="col-md-12">
                                    <h4>
                                        <a href="{{route('sales::customer.downloadFormatFile')}}">{{ trans('sales::vi.Format excel file') }}
                                            <i class="fa fa-download"></i></a></h4>
                                </div>
                                <!-- dialog buttons -->
                                <div class="modal-footer">
                                    <button type="submit" style="display: none" id="btn_submit_import_excel"></button>
                                    <button type="button" onclick="fc_summit_import()" class="btn btn-primary btn-ok">Upload
                                    </button>
                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                        <thead>
                            <tr>
                                <th class="align-center"><input type="checkbox" class="check-parent"
                                        onclick="parentCheck(this);" /></th>
                                <th class="col-id width-5-per">{{ trans('sales::view.No.') }}</th>
                                <th class="sorting {{ Config::getDirClass('name') }} col-name" data-order="name"
                                    data-dir="{{ Config::getDirOrder('name') }}">{{ trans('sales::view.Name') }}</th>
                                <th class="sorting {{ Config::getDirClass('name_ja') }} col-name_ja" data-order="name_ja"
                                    data-dir="{{ Config::getDirOrder('name_ja') }}">
                                    {{ trans('sales::view.Japanese name') }}</th>
                                <th class="sorting {{ Config::getDirClass('company') }} col-company" data-order="company"
                                    data-dir="{{ Config::getDirOrder('company') }}">{{ trans('sales::view.Company') }}
                                </th>
                                <th class="col-action width-10-per">&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="filter-input-grid">
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" name="filter[{{ $customerTable }}.name]"
                                                value="{{ Form::getFilterData("{$customerTable}.name") }}"
                                                placeholder="{{ trans('project::view.Search') }}..."
                                                class="filter-grid form-control" />
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" name="filter[{{ $customerTable }}.name_ja]"
                                                value="{{ Form::getFilterData("{$customerTable}.name_ja") }}"
                                                placeholder="{{ trans('project::view.Search') }}..."
                                                class="filter-grid form-control" />
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" name="filter[excerpt][company]"
                                                value="{{ Form::getFilterData('excerpt', 'company') }}"
                                                placeholder="{{ trans('project::view.Search') }}..."
                                                class="filter-grid form-control" />
                                        </div>
                                    </div>
                                </td>
                                <td>&nbsp;</td>
                            </tr>
                            @if (isset($collectionModel) && count($collectionModel))
                                <?php $i = View::getNoStartGrid($collectionModel); ?>
                                @foreach ($collectionModel as $item)
                                    <tr>
                                        <td class="align-center"><input type="checkbox" class="check-child"
                                                value="{{ $item->id }}" data-name='{{ $item->name }}'
                                                onclick="childCheck(this);" /></td>
                                        <td>{{ $i }}</td>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->name_ja }}</td>
                                        <td>{{ $item->company . ($item->company_name_ja ? ' (' . $item->company_name_ja . ')' : '') }}
                                        </td>
                                        <td class="text-center">
                                            {{-- <a href="{{ route('sales::customer.edit', ['id' => $item->id]) }}"
                                                class="btn-edit">
                                                <i class="fa fa-edit"></i>
                                            </a> --}}
                                            @if (Permission::getInstance()->isAllow('sales::customer.delete'))
                                                <form action="{{ route('sales::customer.delete') }}" method="post"
                                                    class="form-inline">
                                                    {{ csrf_field() }}
                                                    <input type="hidden" name="id" value="{{ $item->id }}">
                                                    <button class="btn-delete delete-confirm"
                                                        title="{{ trans('sales::view.Delete') }}">
                                                        <span><i class="fa fa-trash"></i></span>
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                    <?php $i++; ?>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="10" class="text-center">
                                        <h2 class="no-result-grid">{{ trans('project::view.No results found') }}</h2>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="box-body">
                    @include('team::include.pager', ['domainTrans' => 'project'])
                </div>
            </div>
        </div>
    </div>
    @include('sales::customer.include.modal_merge_confirm')
@endsection

@section('script')
    <script>
        var routeMerge = '{{ route('sales::customer.merge') }}';
        var token = '{{ csrf_token() }}';

        function fc_show_model_upload_file() {
            $('#model-import-excel').modal();
            $('#frmMain #fileToUpload').val('');

        }

        function fc_summit_import() {
            if ($('#fileToUpload').val().trim() == '') {
                $('#error-import-excel').show();
                return false;
            }
            $('#error-import-excel').hide();
            $('#model-import-excel').modal('hide');
            $('#btn_submit_import_excel').trigger('click');
        }
    </script>
    <script src="{{ CoreUrl::asset('sales/js/common.js') }}"></script>
@endsection
