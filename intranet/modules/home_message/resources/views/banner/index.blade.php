@extends('layouts.default')

@section('title')

@endsection
<?php
use Rikkei\Core\View\CoreUrl;
use Rikkei\HomeMessage\Helper\Constant;
use Rikkei\HomeMessage\Helper\Helper;
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
                        <a class="btn btn-primary ot-margin-bottom-5" href="{{route('HomeMessage::home_message.detail-banner',['id'=>0])}}">
                            <i class="fa fa-plus"></i> <span>{{ trans('HomeMessage::view.Add new banner') }}</span>
                        </a>
                    </div>

                    <table class="table dataTable table-striped table-grid-data table-responsive table-hover table-bordered list-ot-table">
                        <thead class="list-head">
                        <tr>
                            <th>{{trans('HomeMessage::view.Banner id')}}</th>
                            <th>{{trans('HomeMessage::view.Banner name')}}</th>
                            <th>{{trans('HomeMessage::view.Banner image')}}</th>
                            <th style="width: 450px">{{trans('HomeMessage::view.Banner branch list')}}</th>
                            <th>{{trans('HomeMessage::view.Banner open by app')}}</th>
                            <th>{{trans('HomeMessage::view.Banner begin at')}}</th>
                            <th>{{trans('HomeMessage::view.Banner end at')}}</th>
                            <th>{{trans('HomeMessage::view.status')}}</th>
                            <th>{{trans('HomeMessage::view.Banner action')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @for($i=0; $i< $collection->count();$i++)
                            <?php
                            $teams = $collection[$i]->teams->map(function ($item, $key) {
                                return Helper::BODParser($item->name);
                            });
                            $endAt = \Carbon\Carbon::parse($collection[$i]->end_at);
                            $beginAt = \Carbon\Carbon::parse($collection[$i]->begin_at);
                            $now = \Carbon\Carbon::now();
                            if ($collection[$i]->status == Constant::HOME_MESSAGE_BANNER_STATUS_AVAILABLE) {
                                if ($now < $beginAt) {
                                    $statusLabel = trans('HomeMessage::view.Banner status is before');
                                } elseif ($beginAt <= $now && $endAt >= $now) {
                                    $statusLabel = trans('HomeMessage::view.Banner status is active');
                                } else {
                                    $statusLabel = trans('HomeMessage::view.Banner status is after');
                                }
                            } else {
                                $statusLabel = trans('HomeMessage::view.Banner status unavailable');
                            }
                            ?>
                            <tr>
                                <td>{{$perPage * ($currentPage -1) + $i + 1}}</td>
                                <td>{{$collection[$i]->display_name}}</td>
                                <td>
                                    <img src="{{asset($collection[$i]->image)}}" alt="" style="width: 120px; height: 40px; object-fit: cover">
                                </td>
                                <td>{{$teams->implode(', ')}}</td>
                                <td>{{Constant::homeMessageBannerTypes()[$collection[$i]->type]}}</td>
                                <td>{{$collection[$i]->begin_at}}</td>
                                <td>{{$collection[$i]->end_at}}</td>
                                <td>{!! $statusLabel !!}</td>
                                <td>
                                    <button class="btn btn-success"
                                            onclick="singleRecord('{{route('HomeMessage::home_message.detail-banner',['id'=>$collection[$i]->id])}}')">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger"
                                            onclick="deleteRecord('{{route('HomeMessage::home_message.delete-banner',['id'=>$collection[$i]->id])}}')">
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
            if (!confirm('{!! trans('HomeMessage::message.Are you sure delete item selected?') !!}')) {
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
