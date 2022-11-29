@extends('layouts.default')
<?php

use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View;

?>

@section('title')
    {{ trans('sales::view.Css view list of')}}<strong>{{$css->project_name}}</strong>
@endsection

@section('content')

<div class="row view-css-page">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div >
                <div class="row">
                    <div class="col-sm-6"></div>
                    <div class="col-sm-6"></div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="box-body">
                            @include('team::include.filter')
                            @include('team::include.pager')
                        </div>
                        <div class="table-responsive">
                            <table id="example2" class="table table-bordered table-hover dataTable" role="grid" aria-describedby="example2_info">
                                <thead>
                                    <tr role="row">
                                        <th class="sorting {{ Config::getDirClass('id') }}" data-order="id" data-dir="{{ Config::getDirOrder('id') }}"  >{{ trans('sales::view.Id') }}</th>
                                        <th class="sorting {{ Config::getDirClass('name') }}" data-order="name" data-dir="{{ Config::getDirOrder('name') }}"  >{{ trans('sales::view.View name') }}</th>
                                        <th class="sorting {{ Config::getDirClass('sale_name') }}" data-order="sale_name" data-dir="{{ Config::getDirOrder('sale_name') }}"  >{{ trans('sales::view.Sale name 2') }}</th>
                                        <th class="sorting {{ Config::getDirClass('created_at') }}" data-order="created_at" data-dir="{{ Config::getDirOrder('created_at') }}"  >{{ trans('sales::view.View date css') }}</th>
                                        <th>{{ trans('sales::view.View.Ip address') }}</th>

                                   </tr>
                                </thead>
                                <tbody>
                                    <td>&nbsp;</td>
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" class='form-control filter-grid' name="filter[css_view.name]" value="{{ Form::getFilterData('css_view.name') }}"  />
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" class='form-control filter-grid' name="filter[css.sale_name_jp]" value="{{ Form::getFilterData('css.sale_name_jp') }}"  />
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12">
                                                
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12"> 
                                                
                                            </div>
                                        </div>
                                    </td>
                                    @if(count($cssViews))
                                    @foreach($cssViews as $item)
                                    <tr role="row" class="odd">
                                        <td>{{ $item->id }}</td>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->sale_name_jp }}</td>
                                        <td>{{ $item->view_date }}</td>
                                        <td>{{ $item->ip_address }}</td>
                                    </tr>
                                    @endforeach
                                    @else
                                    <tr><td colspan="13" class="text-align-center"><h2>{{ trans('sales::view.No result not found')}}</td></tr></h2>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-5">

                    </div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers" id="example2_paginate">
                            <?php echo $cssViews->render(); ?>
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
@endsection
<!-- Styles -->
@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link href="{{ asset('sales/css/sales.css') }}" rel="stylesheet" type="text/css" >
@endsection