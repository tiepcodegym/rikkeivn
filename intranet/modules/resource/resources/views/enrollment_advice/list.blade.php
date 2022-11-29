@extends('layouts.default')
@section('title')
    {{ trans('resource::view.Enrollment Advice.List') }}
@endsection

@section('content')
<?php

use Rikkei\Core\View\Form;
use Rikkei\Resource\View\getOptions;
use Rikkei\Resource\Model\EnrollmentAdvice;

$statusOption = getOptions::getInstance()->getStatusEnrollmentAdvice();
$statusFilter =  Form::getFilterData('enrollment_advice.status');
?>

<div class="row list-css-page list-request-page">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-6"></div>
                    <div class="col-sm-6"></div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="box-body">
                            <div class="filter-action">
                                <div class="pull-left"> 
                                   
                                </div>
                                <button class="btn btn-primary btn-reset-filter">
                                    <span>{{ trans('resource::view.Reset filter') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
                                </button>
                                <button class="btn btn-primary btn-search-filter">
                                    <span>{{ trans('resource::view.Search') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
                                </button>
                            </div>
                        </div>
                        <form class="table-responsive" autocomplete="off">
                            <table class="table table-bordered table-hover dataTable" role="grid" aria-describedby="example2_info">
                                <thead>
                                    <tr role="row">
                                        <th style="width:8%" class="sorting id" data-order="id" data-dir="id">{{ trans('resource::view.Enrollment Advice.List.Id') }}</th>
                                        <th class="sorting col-sm-3 name" data-order="name" data-dir="name">{{ trans('resource::view.Enrollment Advice.List.Name') }}</th>
                                        <th class="sorting col-sm-100 email" data-order="name" data-dir="email">{{ trans('resource::view.Enrollment Advice.List.Email') }}</th>
                                        <th class="sorting col-sm-2 phone" data-order="name" data-dir="phone">{{ trans('resource::view.Enrollment Advice.List.Phone') }}</th>
                                        <th class="sorting col-sm-2 language " data-order="name" data-dir="language ">{{ trans('resource::view.Enrollment Advice.List.Language') }}</th>
                                        <th class="sorting col-sm-2 status" data-order="name" data-dir="status">{{ trans('resource::view.Enrollment Advice.List.Status') }}</th>
                                   </tr>
                                </thead>
                                <tr class="filter-input-grid">
                                    <td>&nbsp;</td>
                                    <td>
                                        <input type="text" class='form-control filter-grid' name="filter[enrollment_advice.name]" value="{{ Form::getFilterData('enrollment_advice.name') }}" placeholder="{{ trans('team::view.Search') }}..." />
                                    </td>
                                    <td>
                                        <input type="text" class='form-control filter-grid' name="filter[enrollment_advice.email]" value="{{ Form::getFilterData('enrollment_advice.email') }}" placeholder="{{ trans('team::view.Search') }}..." />
                                    </td>
                                    <td>
                                        <input type="text" class='form-control filter-grid' name="filter[enrollment_advice.phone]" value="{{ Form::getFilterData('enrollment_advice.phone') }}" placeholder="{{ trans('team::view.Search') }}..." />
                                    </td>
                                    <td>
                                        <input type="text" class='form-control filter-grid' name="filter[enrollment_advice.language]" value="{{ Form::getFilterData('enrollment_advice.language') }}" placeholder="{{ trans('team::view.Search') }}..." />
                                    </td>
                                    <td>
                                       
                                        <select name="filter[enrollment_advice.status]" class="form-control select-grid filter-grid select-search">
                                            <option value="">&nbsp;</option>
                                            @foreach($statusOption as $option)
                                                <option value="{{ $option['id'] }}"<?php
                                                    if ($option['id'] == $statusFilter): ?> selected<?php endif; 
                                                        ?>>{{ $option['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                 </tr>
                                @if ($collectionModel && count($collectionModel) > 0)
                                @foreach($collectionModel as $item)
                                <tr role="row" class="odd">
                                    <td rowspan="1" colspan="1" >{{ $item->id }}</td>
                                    <td rowspan="1" colspan="1" >{{ $item->name }}</td>
                                    <td rowspan="1" colspan="1" >{{ $item->email }}</td>
                                    <td rowspan="1" colspan="1" >{{ $item->phone }}</td>
                                    <td rowspan="1" colspan="1" >{{ $item->language }}</td>
                                    <td rowspan="1" colspan="1" id="html_{{ $item->id }}">
                                        @if ($item->status == EnrollmentAdvice::STATE_OPEN)
                                            <button type="button" class="btn btn-success" data-id="{{ $item->id }}">Open</button>    
                                        @else
                                            <button type="button" class="btn btn-danger" disabled>CLose </button>   
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                                @endif
                            </table>
                        </form>    
                        <div class="box-body">
                            @include('team::include.pager')
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
@endsection

<!-- Script -->
@section('script')
<script>
     var urlStatusUpdate = '{{ route("resource::enroll_addvice.update.status") }}';
     var messageError = '{{ trans("resource::message.System Error!") }}';
    $(function() {
        $( "body" ).on( "click", "button[type=button]", function( event ) {
            var id = $( this ).attr( "data-id" );
            $.ajax({
                url: urlStatusUpdate,
                data: {
                    "_token": "{{ csrf_token() }}",
                    id: id
                },
                type: "POST",
                success: function (data) {
                    $('#html_'+id).html('<button type="button" class="btn btn-danger" disabled>CLose </button>');
                },
                error: function (status) {
                    alert(messageError);
                },
            });
        });
    })
</script>
@endsection
