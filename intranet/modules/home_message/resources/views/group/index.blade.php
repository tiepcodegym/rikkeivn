@extends('layouts.default')

@section('title')

@endsection
<?php
use Rikkei\Core\View\CoreUrl;
$collectionModel = $collection;
$perPage = $collectionModel->perPage();
$perPage = $perPage ? (int)$perPage : 10;
$currentPage = $collectionModel->currentPage();
$currentPage = $currentPage ? (int)$currentPage : 1;

?>

@section('css')
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css"/>
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css"/>
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_managetime/css/common.css') }}"/>
@endsection

@section('content')
    <div class="row">
        <div class="row">
            <!-- Menu left -->
            <div class="col-lg-2 col-md-3">
                @include('HomeMessage::include.menu_left')
            </div>
            <!-- /.col -->
            <div class="col-lg-10 col-md-9 content-ot">
                <div class="box box-primary">
                    <div class="action" style="margin: 10px;">
                        <a class="btn btn-primary ot-margin-bottom-5" href="{{route('HomeMessage::home_message.detail-group',['id'=>0])}}">
                            <i class="fa fa-plus"></i> <span>{{ trans('HomeMessage::view.Add new group') }}</span>
                        </a>
                    </div>

                    <table style="padding-right: 15px;" class="table dataTable table-striped table-grid-data table-responsive table-hover table-bordered list-ot-table">
                        <thead class="list-head">
                        <tr>
                            <th>{{trans('HomeMessage::view.Order')}}</th>
                            <th>{{trans('HomeMessage::view.Group name')}}</th>
                            <th>{{trans('HomeMessage::view.Priority')}}</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @for($i=0; $i< $collection->count();$i++)
                            <tr>
                                <td style="text-align: center;width: 50px">{{$perPage * ($currentPage -1) + $i + 1}}</td>
                                <td style="width: auto">
                                    VI: {{htmlentities($collection[$i]->name_vi)}}<br/>
                                    EN: {{htmlentities($collection[$i]->name_en)}}<br/>
                                    JP: {{htmlentities($collection[$i]->name_jp)}}
                                </td>

                                <td style="text-align: center;width: 150px">{{$collection[$i]->priority}}</td>
                                <td style="width: 85px;text-align: center">
                                    <button class="btn btn-success edit-row" style="width: 40px; height: 35px"
                                            onclick="singleRecord('{{route('HomeMessage::home_message.detail-group',['id'=>$collection[$i]->id])}}')">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger delete-row" style="width: 40px; height: 35px"
                                            onclick="deleteRecord('{{route('HomeMessage::home_message.delete-group',['id'=>$collection[$i]->id])}}')">
                                        <i class="fa fa-remove"></i>
                                    </button>
                                </td>
                            </tr>
                        @endfor
                        </tbody>
                    </table>
                    <div class="box-footer no-padding">
                        <div class="mailbox-controls">
                            @include('HomeMessage::include.pager')
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.col -->
        </div>
        <!-- /.col -->
    </div>
@endsection
@section('script')
    <script>

        function singleRecord(url) {
            window.location.href = url;
        }

        function deleteRecord(url) {
            if(!confirm('{!! trans('HomeMessage::message.Are you sure delete item selected?') !!}'))
            {
                return false;
            }
            $.ajax({
                url: url,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                success: function (resp) {
                    window.location.reload();
                },
                error: function (errors) {
                    alert(errors.responseJSON);
                }
            })
        }
    </script>
@endsection
