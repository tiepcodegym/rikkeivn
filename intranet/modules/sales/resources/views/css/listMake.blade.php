@extends('layouts.default')
<?php

use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View;

?>

@section('title')
    {{ trans('sales::view.Css result list of')}}<strong>{{$css->project_name}}</strong>
@endsection

@section('css')
<style>
    .multiselect-container label.checkbox {
        margin-top: 0;
        margin-bottom: 0;
        font-weight: normal;
    }
    .btn-group button {
        width: 200px;
    }
</style>
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
                            <div class="form-horizontal" style="margin-bottom: 30px;">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="inputEmail3" class="col-sm-2 control-label">Status</label>
                                            <div class="col-sm-10">
                                                <select multiple="multiple" style="width: 300px" name="filter[except][css_result.status][]" class="form-control filter-grid multi-select-bst select-multi">
                                                    @foreach ($statusList as $key => $value)
                                                    <option value="{{ $key }}" {{ !empty($filterStatus) && in_array($key, $filterStatus) ? 'selected' : '' }}>{{ $value }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <table id="example2" class="table table-bordered dataTable" role="grid" aria-describedby="example2_info">
                                <thead>
                                    <tr role="row">
                                        <th class="text-align-center">{{ trans('sales::view.Id') }}</th>
                                        <th class="sorting {{ Config::getDirClass('name') }}" data-order="name" data-dir="{{ Config::getDirOrder('name') }}"  >{{ trans('sales::view.Make name') }}</th>
                                        <th class="sorting {{ Config::getDirClass('sale_name') }}" data-order="sale_name" data-dir="{{ Config::getDirOrder('sale_name') }}"  >{{ trans('sales::view.Sale name 2') }}</th>
                                        <th class="sorting {{ Config::getDirClass('avg_point') }}" data-order="avg_point" data-dir="{{ Config::getDirOrder('avg_point') }}" >{{ trans('sales::view.CSS mark') }}</th>
                                        <th class="sorting {{ Config::getDirClass('created_at') }}" data-order="created_at" data-dir="{{ Config::getDirOrder('created_at') }}"  >{{ trans('sales::view.Make date css') }}</th>
                                        <th >{{ trans('sales::view.Status') }}</th>
                                        <th tabindex="0" aria-controls="example2" rowspan="1" colspan="1" >{{ trans('sales::view.View css detail') }}</th>
                                   </tr>
                                </thead>
                                <tbody>
                                    @if(count($cssResults))
                                    @foreach($cssResults as $item)
                                    <tr role="row" class="odd ">
                                        <td rowspan="1" colspan="1" class="text-align-center position-relative">
                                            @if ($item->count_make > 1)
                                            <span class="cursor-pointer color-green position-absolute left-3 icon-display" data-dir='closed' data-loaded='false' onclick="showAllMake(this, {{$css->id}}, '{{$item->code}}', {{$item->id}});" title="{{trans('sales::view.Show other points')}}"><i class="fa fa-circle fa-chevron-circle-down"></i>&nbsp;<i class="fa fa-spin fa-refresh hidden"></i></span>
                                            @endif
                                            {{ $item->id }}
                                        </td>
                                        <td rowspan="1" colspan="1" >{{ $item->name }}</td>
                                        <td rowspan="1" colspan="1" >{{ $item->sale_name }}</td>
                                        <td rowspan="1" colspan="1" >{{ number_format($item->avg_point,2) }}</td>
                                        <td rowspan="1" colspan="1" >{{ $item->make_date }}</td>
                                        <td rowspan="1" colspan="1" >{{ $statusList[$item->status] }}</td>
                                        <td rowspan="1" colspan="1" ><a href="/css/detail/{{$item->id}}">{{ trans('sales::view.View') }}</a></td>
                                    </tr>
                                    @endforeach
                                    @else
                                    <tr><td colspan="7" class="text-align-center"><h2>{{ trans('sales::view.No result not found')}}</td></tr></h2>
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
                            <?php echo $cssResults->render(); ?>
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
<meta name="_token" content="{{ csrf_token() }}"/>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link href="{{ asset('sales/css/sales.css') }}" rel="stylesheet" type="text/css" >
@endsection

<!-- Script -->
@section('script')
<script>
    var urlShowAllMake = '{{route("sales::css.showAllMake")}}';
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
<script src="{{ asset('sales/js/css/listMake.js') }}"></script>
<script>
    $('.select-multi').multiselect({
        numberDisplayed: 1,
        nonSelectedText: '--------------',
        allSelectedText: '{{ trans('project::view.All') }}',
        onDropdownHide: function(event) {
            RKfuncion.filterGrid.filterRequest(this.$select);
        }
    });
</script>
@endsection
