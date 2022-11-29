@extends('layouts.default')

@section('title')

@endsection
<?php
use Rikkei\Core\View\CoreUrl;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Core\View\Form as CoreForm;use Rikkei\HomeMessage\Model\HomeMessage;use Rikkei\HomeMessage\Model\HomeMessageGroup;
use Rikkei\Team\View\Config as TeamConfig;
use Carbon\Carbon;

$collectionModel = $collection;
$perPage = $collectionModel->perPage();
$perPage = $perPage ? (int)$perPage : 10;
$currentPage = $collectionModel->currentPage();
$currentPage = $currentPage ? (int)$currentPage : 1;
$homeMessageTable = HomeMessage::makeInstance()->getTable();
$homeMessageGroupTable = HomeMessageGroup::makeInstance()->getTable();

function getGroupName($group)
{
    if (!is_object($group)) {
        return '';
    }
    $name = '';
    if (trim($group->name_vi) != '') {
        $name = $name != '' ? $name . ' - ' . $group->name_vi : $group->name_vi;
    }
    if (trim($group->name_en) != '') {
        $name = $name != '' ? $name . ' - ' . $group->name_en : $group->name_en;
    }
    if (trim($group->name_jp) != '') {
        $name = $name != '' ? $name . ' - ' . $group->name_jp : $group->name_jp;
    }
    return htmlentities($name);
}
?>

@section('css')
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css"/>
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css"/>
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_managetime/css/reason_list.css') }}">
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_managetime/css/common.css') }}"/>
    <link rel="stylesheet" href="{{ CoreUrl::asset('common/css/style.css') }}" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('team/css/style.css') }}" />
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
                    <div class="box-header filter-mobile-left">
                        <a class="btn btn-primary ot-margin-bottom-5" style="float: left"
                           href="{{route('HomeMessage::home_message.detail-home-message',['id'=>0])}}">
                            <i class="fa fa-plus"></i>
                            <span>{{ trans('HomeMessage::view.Add new home message') }}</span>
                        </a>

                        @include('HomeMessage::include.filter')
                    </div>
                    <table style="padding-right: 15px;" class="table dataTable table-striped table-grid-data table-responsive table-hover table-bordered list-ot-table">
                        <thead class="list-head">
                        <tr>
                            <th>{{trans('HomeMessage::view.Order')}}</th>
                            <th class="col-width-75 col-title ">{{trans('HomeMessage::view.Message')}}</th>
                            <th>{{trans('HomeMessage::view.Icon')}}</th>
                            <th>{{trans('HomeMessage::view.Group name')}}</th>
                            <th>{{trans('HomeMessage::view.Branch')}}</th>
                            <th></th>
                        </tr>
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th>
                                <div class="row">
                                    <div class="col-md-12">
                                        <select name="filter[{{$homeMessageTable}}.group_id]"
                                                class="form-control select-grid filter-grid select-search"
                                                autocomplete="off" style="width: 100%;">
                                            <option value="">&nbsp;</option>
                                            @foreach($allGroup as $group)
                                                <option {{CoreForm::getFilterData("{$homeMessageTable}.group_id") == $group->id ? 'selected' : ''}} value="{{$group->id}}">
                                                    <?php
                                                    echo getGroupName($group);
                                                    ?>
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </th>
                            <th>
                                <div class="row">
                                    <div class="col-md-12">
                                        <select name="filter[except][team_id]"
                                                class="form-control select-grid filter-grid select-search"
                                                autocomplete="off" style="width: 100%;">
                                            <option value="">&nbsp;</option>
                                            @foreach($allBranch as $branch)
                                                <option {{CoreForm::getFilterData('except',"team_id") == $branch->id ? 'selected' : ''}} value="{{$branch->id}}">{{$branch->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @if($collection->count())
                            @for($i=0; $i< $collection->count();$i++)
                                <tr>
                                    <td style="text-align: center">{{$perPage * ($currentPage -1) + $i + 1}}</td>
                                    <td style="max-width: 500px">
                                        <?php
                                        echo 'VI: ' . htmlentities($collection[$i]->message_vi) . '<br/>';
                                        echo 'EN: ' . htmlentities($collection[$i]->message_en) . '<br/>';
                                        echo 'JP: ' .htmlentities($collection[$i]->message_jp) . '<br/>';
                                        ?>
                                    </td>
                                    <td style="text-align: center">
                                        @if($collection[$i]->icon_url)
                                            <img src="{{$collection[$i]->icon_url}}"
                                                 style="max-height: 30px;width: 30px; border: solid 1px #3333"/>
                                        @endif
                                    </td>
                                    <td>
                                        <?php
                                        echo getGroupName($collection[$i]->group);
                                        ?>
                                    </td>
                                    <td>{{$collection[$i]->getBranch()}}</td>
                                    <td style="width: 85px;text-align: center; white-space: nowrap">
                                        <a class="btn btn-edit edit-row" href="{{route('HomeMessage::home_message.detail-home-message',['id'=>$collection[$i]->id])}}">
                                            <i class="fa fa-pencil-square-o" aria-hidden="true" ></i>
                                        </a>
                                        <form class="form-delete" action="{{route('HomeMessage::home_message.delete-home-message',['id'=>$collection[$i]->id])}}" method="POST">
                                            {{ csrf_field() }}
                                            <button class="btn-delete delete-confirm" type="submit"><i class="fa fa-trash-o" aria-hidden="true"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endfor
                        @else
                            <tr><td colspan="13" class="text-center"><h2>{{trans('HomeMessage::view.No result not found')}}</h2></td></tr>
                        @endif
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
