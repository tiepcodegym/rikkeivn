@extends('layouts.default')

@section('title', trans('event::view.Mailing list'))

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
@endsection

@section('content')
<?php
    use Rikkei\Core\View\Form;
    use Rikkei\Event\Model\EventBirthCustEmail;
?>
<div class="row">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="box-body">
                @include('team::include.filter')
            </div>
            <div class="table-responsive">
                <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                    <thead>
                        <tr>
                            <th class="col-id" style="width: 20px;">{{ trans('project::view.No.') }}</th>
                            <th>Email gửi</th>
                            <th>Email khách hàng</th>
                            <th width="200">Trạng thái</th>
                            <th>Email người tạo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="filter-input-grid">
                            <td>&nbsp;</td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[sale_email]" value="{{ Form::getFilterData("name") }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[email]" value="{{ Form::getFilterData("email") }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <select class="form-control select-grid filter-grid select-search" name="filter[status]">
                                            <option value="">&nbsp;</option>
                                            @foreach($statusOptions as $key => $value)
                                                <option value="{{ $key }}" {{ (is_numeric(Form::getFilterData('status')) && Form::getFilterData('status') == $key) ? 'selected' : '' }}>{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </td>
                            <td></td>
                        </tr>
                        @if(isset($collectionModel) && count($collectionModel))
                            @foreach($collectionModel as $key => $item)
                                <tr>
                                    <td>{{ $key+1 }}</td>
                                    <td>{{ $item->sale_email }}</td>
                                    <td>{{ $item->email }}</td>
                                    <td align="center">
                                        @if ($item->status == EventBirthCustEmail::STATUS_YES)
                                            <i class="fa fa-check" aria-hidden="true" style="color: green;"></i>
                                        @else
                                            <i class="fa fa-times" aria-hidden="true" style="color: red;"></i>
                                        @endif
                                    </td>
                                    <td>{{ $item->email_sender }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="14" class="text-center">
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
@endsection
