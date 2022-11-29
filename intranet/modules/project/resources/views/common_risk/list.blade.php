@extends('layouts.default')
@section('title')
    {{ trans('project::view.LBL_COMMON_RISK') }}
@endsection
@section('content')
    <?php

    use Rikkei\Team\Model\Team;
    use Rikkei\Team\View\Config;
    use Rikkei\Core\View\Form;
    use Rikkei\Core\View\View;
    use Rikkei\Project\Model\Risk;
    use Rikkei\Project\Model\CommonRisk;
    use Rikkei\Core\View\CoreUrl;

    $urlFilter = trim(URL::route('project::report.common-risk'), '/') . '/';
    ?>
    <div class="row list-css-page">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-body">
                    <div class="row form-horizontal filter-input-grid">
                        <div class="col-sm-12">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="inputEmail3"
                                           class="col-sm-3 control-label">{{ trans('project::view.Risk Source') }}</label>
                                    <div class="col-sm-6">
                                        <select class="form-control filter-grid" id="filter_risk_source"
                                                name="filter[common_risk.risk_source]">
                                            <option value=""></option>
                                            @foreach (Risk::getSourceList() as $keyRiskSource => $valueRiskSource)
                                                <option value="{{ $keyRiskSource }}" {{ !is_null(Form::getFilterData('common_risk.risk_source')) && Form::getFilterData('common_risk.risk_source') == $keyRiskSource ? 'selected' : '' }}>{{ $valueRiskSource }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="inputEmail3"
                                           class="col-sm-3 control-label">{{ trans('project::view.Process') }}</label>
                                    <div class="col-sm-6">
                                        <select class="form-control filter-grid" id="filter_process"
                                                name="filter[common_risk.process]">
                                            <option value=""></option>
                                            @foreach (CommonRisk::getSourceListProcess() as $keyProcess => $valueProcess)
                                                <option value="{{ $keyProcess }}" {{ !is_null(Form::getFilterData('common_risk.process')) && Form::getFilterData('common_risk.process') == $keyProcess ? 'selected' : '' }}>{{ $valueProcess }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <form id="form-export" action="{{ route('project::project.export.commonRisk') }}"
                              method="post">{!! csrf_field() !!}
                        </form>
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="box-body">
                                    <div class="row">
                                        <div class="col-md-12 bg-light text-right">
                                            @if($permissionEdit)
                                                <button href="#"
                                                        class="btn add-common-risk btn-success"><i
                                                            class="fa fa-plus"></i> {{ trans('project::view.Add risk') }}
                                                </button>
                                            @endif
                                            <button class="btn btn-success btn-export">
                                                {{ trans('project::view.Export') }}
                                            </button>
                                            <button type="submit" class="btn btn-primary btn-search-filter">
                                                <span>{{ trans('team::view.Search') }} <i
                                                            class="fa fa-spin fa-refresh hidden"></i></span>
                                            </button>
                                            <button class="btn btn-primary btn-reset-filter">
                                                <span>{{ trans('team::view.Reset filter') }} <i
                                                            class="fa fa-spin fa-refresh hidden"></i></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover dataTable" role="grid"
                                           aria-describedby="example2_info">
                                        <thead>
                                        <tr role="row">
                                            <th style="width: 10px;">{{trans('project::view.ID risk')}}</th>
                                            <th style="width: 150px;">{{trans('project::view.Risk Source')}}</th>
                                            <th style="width: 350px;">{{trans('project::view.LBL_RISK_DESCRIPTION')}}</th>
                                            <th style="width: 150px;">{{trans('project::view.Process')}}</th>
                                            <th style="width: 250px;">{{trans('project::view.LBL_SUGGEST_ACTION')}}</th>
                                            <th>{{trans('project::view.Create date')}}</th>
                                            <th>{{trans('project::view.Update date')}}</th>
                                            <th></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(count($collectionModel) > 0)
                                            @foreach($collectionModel as $index => $risk)
                                                <tr role="row" data-id="{{ $risk->id  }}">
                                                    <td>{{ $risk->id }}</td>
                                                    <td>{{ Risk::getSourceList()[$risk->risk_source] }}</td>
                                                    <td>{!! View::nl2br($risk->risk_description) !!}</td>
                                                    <td>{{ CommonRisk::getSourceListProcess()[$risk->process] }}</td>
                                                    <td>{!! View::nl2br($risk->suggest_action) !!}</td>
                                                    <td>{{ substr($risk->created_at, 0, 10)}}</td>
                                                    <td>{{ substr($risk->updated_at, 0, 10)}}</td>
                                                    @if ($permissionEdit)
                                                        <td style="text-align: center;">
                                                            <a class="btn-edit"
                                                               href="{{ route('project::commonRisk.detail', ['id' => $risk->id]) }}">
                                                                <i class="fa fa-edit"></i>
                                                            </a>
                                                            <button class="btn-delete btn-delete-common-risk"
                                                                    data-id="{{ $risk->id }}">
                                                                <i class="fa fa-trash-o"></i>
                                                            </button>
                                                        </td>
                                                    @else
                                                        <td>
                                                            <button class="btn-edit btn-show-detail"
                                                                    data-id="{{ $risk->id }}">
                                                                <i class="fa fa-info-circle"></i>
                                                            </button>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="11" class="text-align-center">
                                                    <h2>{{trans('sales::view.No result not found')}}</h2>
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
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    @include('project::components.modal_delete_confirm_panel')
    @endsection
    <!-- Styles -->
        @section('css')
            <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/>
            <link href="{{ asset('sales/css/sales.css') }}" rel="stylesheet" type="text/css">
            <link href="{{ asset('resource/css/resource.css') }}" rel="stylesheet" type="text/css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css"/>
            <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css"
                  rel="stylesheet" type="text/css">
            <link rel="stylesheet"
                  href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.34.7/css/bootstrap-dialog.min.css">
            <link rel="stylesheet" href="{{ URL::asset('project/css/edit.css') }}"/>
        @endsection

    <!-- Script -->
        @section('script')
            <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
            <script src="{{ URL::asset('lib/js/moment.min.js') }}"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
            <script type="text/javascript" src="{{ CoreUrl::asset('project/js/script.js') }}"></script>
            <script type="text/javascript" src="{{ asset('lib/js/bootstrap-dialog.min.js') }}"></script>
            <script type="text/javascript">
                var token = '{!! csrf_token() !!}';
                var idItemDelete = '';
                var requiredText = 'Trường bắt buộc nhập';
                var urlAddRisk = '{{ route("project::commonRisk.edit") }}';
                var urlDeleteCommonRisk = '{{ route("project::commonRisk.delete") }}';
                jQuery(document).ready(function () {
                    $(document).on('click', '.add-common-risk', function () {
                        var $curElem = $(this);
                        $('.add-common-risk').prop('disabled', true);
                        $curElem.find('i.fa-plus').removeClass('fa-plus').addClass('fa-refresh').addClass('fa-spin');
                        $.ajax({
                            url: urlAddRisk.trim(),
                            type: 'get',
                            data: {},
                            dataType: 'text',
                            success: function (data) {
                                BootstrapDialog.show({
                                    cssClass: 'risk-dialog',
                                    message: $('<div></div>').html(data),
                                    closable: false,
                                    buttons: [{
                                        id: 'btn-close',
                                        icon: 'fa fa-close',
                                        label: 'Close',
                                        cssClass: 'btn-primary',
                                        autospin: false,
                                        action: function (dialogRef) {
                                            dialogRef.close();
                                        }
                                    }, {
                                        id: 'btn-save',
                                        icon: 'glyphicon glyphicon-check',
                                        label: 'Save',
                                        cssClass: 'btn-primary',
                                        autospin: false,
                                        action: function (dialogRef) {
                                            $('.form-common-risk-detail').submit();
                                        }
                                    }]
                                });
                            },
                            error: function () {
                                alert('ajax fail to fetch data');
                            },
                            complete: function () {
                                $curElem.find('i.fa-refresh').addClass('fa-plus').removeClass('fa-refresh').removeClass('fa-spin');
                                $('.add-common-risk').prop('disabled', false);
                            }
                        });
                    });
                });

                $(document).ready(function () {
                    selectSearchReload();
                });
                $(document).on('click', '.btn-delete-common-risk', function () {
                    $('#modal-delete-confirm-panel').modal('show');
                    idItemDelete = $(this).attr('data-id');
                });
                $(document).on('click', '#modal-delete-confirm-panel .btn-submit', function () {
                    $('#modal-delete-confirm-panel').modal('hide');
                    $('.modal-backdrop').remove();
                    let issueId = idItemDelete;
                    $.ajax({
                        url: urlDeleteCommonRisk,
                        type: 'GET',
                        data: {
                            id: issueId
                        },
                        success: function (data) {
                            $("tr[data-id='" + issueId + "']").remove();
                        },
                        error: function () {
                            alert('ajax fail to fetch data');
                        },
                    });
                });
                $(document).on('click', '.btn-export', function () {
                    $('#form-export').submit();
                });
                $(document).on('click', '.btn-show-detail', function () {
                    var idElement = $(this).attr('data-id');
                    $.ajax({
                        url: urlAddRisk.trim(),
                        type: 'get',
                        data: {
                            isCreateNew: false,
                            id: idElement,
                        },
                        dataType: 'text',
                        success: function (data) {
                            BootstrapDialog.show({
                                cssClass: 'risk-dialog',
                                message: $('<div></div>').html(data),
                                closable: false,
                                buttons: [{
                                    id: 'btn-close',
                                    icon: 'fa fa-close',
                                    label: 'Close',
                                    cssClass: 'btn-primary',
                                    autospin: false,
                                    action: function (dialogRef) {
                                        dialogRef.close();
                                    }
                                }]
                            });
                        },
                        error: function () {
                            alert('ajax fail to fetch data');
                        },
                    });
                });
            </script>
@endsection