@extends('layouts.default')

@section('title')
{{ trans('event::view.List invitation') }}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
@endsection

@section('content')
@include('event::eventday.customer.message-alert')
<?php

use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View;
?>
<div class="row">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="box-body">
                <a href="{{route('event::eventday.customer.create')}}"  class="btn btn-success" style="float: right;margin-left: 3px">{{trans('event::view.Add new customer')}} </a>
                <a href="{{route('event::eventday.export')}}"  class="btn btn-primary" style="float: right;margin-left: 3px"> Export </a>
                @include('team::include.filter', ['domainTrans' => 'event'])
            </div>
            <div class="table-responsive">
                <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                    <thead>
                        <tr>
                            <th class="col-id" style="width: 20px;">{{ trans('project::view.No.') }}</th>
                            <th class="sorting {{ Config::getDirClass('name') }} col-name" data-order="name" data-dir="{{ Config::getDirOrder('name') }}">{{ trans('event::view.Customer name') }}</th>
                            <th class="sorting {{ Config::getDirClass('email') }} col-name" data-order="email" data-dir="{{ Config::getDirOrder('email') }}">{{ trans('event::view.Customer mail') }}</th>
                            <th class="sorting {{ Config::getDirClass('company') }} col-name" data-order="company" data-dir="{{ Config::getDirOrder('company') }}">{{ trans('event::view.Company customer') }}</th>
                            
                            <th class="sorting {{ Config::getDirClass('status') }} col-name" data-order="status" data-dir="{{ Config::getDirOrder('status') }}">{{ trans('event::view.Status') }}</th>
                            <th class="">{{ trans('event::view.Attacher') }}</th>
                            <th class="sorting {{ Config::getDirClass('sender_name') }} col-name" data-order="sender_name" data-dir="{{ Config::getDirOrder('sender_name') }}">{{ trans('event::view.Sender name') }}</th>
                            <th class="sorting {{ Config::getDirClass('sender_email') }} col-name" data-order="sender_email" data-dir="{{ Config::getDirOrder('sender_email') }}">{{ trans('event::view.Sender email') }}</th>
                            <th class="">{{ trans('event::view.Note') }}</th>
                            <th class="sorting {{ Config::getDirClass('created_at') }}" style="width: 50px;" data-order="created_at" data-dir="{{ Config::getDirOrder('created_at') }}">{{ trans('event::view.Send at') }}</th>
                            <th style="width: 80px;" >&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="filter-input-grid">
                            <td>&nbsp;</td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[name]" value="{{ Form::getFilterData("name") }}" placeholder="{{ trans('event::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[email]" value="{{ Form::getFilterData("email") }}" placeholder="{{ trans('event::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[company]" value="{{ Form::getFilterData("company") }}" placeholder="{{ trans('event::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <select style="width: 90px" class="form-control select-grid filter-grid select-search" name="filter[status]">
                                            <option value="">&nbsp;</option>
                                            @foreach($statusOptions as $key => $value)
                                            <option value="{{ $key }}" {{ (is_numeric(Form::getFilterData('status')) && Form::getFilterData('status') == $key) ? 'selected' : '' }}>{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </td>


                            <td >
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[attacher]" value="{{ Form::getFilterData("attacher") }}" placeholder="{{ trans('event::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[sender_name]" value="{{ Form::getFilterData("sender_name") }}" placeholder="{{ trans('event::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[sender_email]" value="{{ Form::getFilterData("sender_email") }}" placeholder="{{ trans('event::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[note]" value="{{ Form::getFilterData("note") }}" placeholder="{{ trans('event::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[created_at]" value="{{ Form::getFilterData("created_at") }}" placeholder="{{ trans('event::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <th >&nbsp;</th>
                        </tr>
                        @if(isset($collectionModel) && count($collectionModel))
                        <?php $i = View::getNoStartGrid($collectionModel); ?>
                        @foreach($collectionModel as $item)
                        <tr>
                            <td>{{ $i }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->email }}</td>
                            <td>{{ $item->company }}</td>
                            <td>{{ $item->getStatus($statusOptions) }}</td>
                            <td>{!! View::nl2br($item->attacher) !!}</td>
                            <td>{{ $item->sender_name }}</td>
                            <td>{{ $item->sender_email }}</td>
                            <td>{!! View::nl2br($item->note) !!}</td>
                            <td>{{ $item->created_at }}</td>
                            <th>
                                <a  href="{{route('event::eventday.customer.edit',['id'=>$item->id])}}" class="btn btn-primary"><i class="fa fa-edit"></i></a>
                                <a href="#" onclick="fc_delete('{{route('event::eventday.customer.delete',['id'=>$item->id])}}')" class="btn btn-danger"><i class="fa fa-trash"></i></a>
                            </th>
                        </tr>
                        <?php $i++; ?>
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

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script type="text/javascript">
                                    function fc_delete(url)
                                    {
                                        if (confirm('Xác nhận xóa đối tượng đã chọn?'))
                                        {
                                            $.ajax({
                                                url: url,
                                                type:'DELETE',
                                                data: {
                                                    _token: '{{csrf_token()}}'
                                                },
                                                success: function (data) {
                                                    window.location.href = "{{route('event::eventday.company.list')}}";
                                                },
                                                error: function (jqXHR, textStatus, errorThrown) {
                                                    alert(jqXHR.responseText);
                                                    console.log(jqXHR, textStatus, errorThrown);
                                                }
                                            })
                                        }
                                    }

                                    jQuery(document).ready(function ($) {
                                        selectSearchReload();
                                    });
</script>
@endsection
