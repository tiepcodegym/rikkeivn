<?php 
header("Cache-Control: no-store, must-revalidate, max-age=0");
header("Pragma: no-cache");
use Rikkei\Core\View\CoreUrl;
?>
@extends('layouts.default')

@section('title')
    {{$titleHeadPage}}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<style type="text/css">
    .create-office {
        position: absolute;
        left: 11px;
    }
</style>
@endsection

@section('content')
<?php
use Rikkei\Team\View\Config as TeamConfig;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Music\View\ViewMusic;
$buttonAction['create'] = [
    'label' => 'Office create', 
    'class' => 'btn btn-primary create-office',
    'disabled' => false, 
    'url'=> URL::route('music::manage.offices.create'),
    'type' => 'link',
];
?>
<div class="row">
    <div class="col-sm-12">
        @if(Session::has('delSuccess'))
            <div class="alert alert-success">
                <ul>
                    <li>
                        {{ Session::get('delSuccess') }}
                    </li>
                </ul>
            </div>
        @endif
        <div class="box box-info">
            <div class="box-body">
                @include('team::include.filter', ['domainTrans' => 'music','buttons' => $buttonAction])
            </div>
            <div class="table-responsive">
                <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                    <thead>
                        <tr>
                            <th class="col-id width-10" style="width: 20px;">{{ trans('music::view.No') }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('name') }} col-name" data-order="name" data-dir="{{ TeamConfig::getDirOrder('name') }}">{{ trans('music::view.Office name') }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('sort_order') }} col-order" data-order="sort_order" data-dir="{{ TeamConfig::getDirOrder('sort_order') }}">{{ trans('music::view.Office order') }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('employee_noti') }} col-employee_noti" data-order="employee_noti" data-dir="{{ TeamConfig::getDirOrder('employee_noti') }}">{{ trans('music::view.Office employee noti') }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('status') }} col-status" data-order="status" data-dir="{{ TeamConfig::getDirOrder('status') }}">{{ trans('music::view.Office status') }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('created_at') }} col-created_at" data-order="created_at" data-dir="{{ TeamConfig::getDirOrder('created_at') }}">{{ trans('music::view.Office date') }}</th>
                            <th >&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="filter-input-grid">
                            <td>&nbsp;</td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[music_offices.name]" value="{{ CoreForm::getFilterData("music_offices.name") }}" placeholder="{{ trans('music::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[music_offices.sort_order]" value="{{ CoreForm::getFilterData("music_offices.sort_order") }}" placeholder="{{ trans('music::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[employees.email]" value="{{ CoreForm::getFilterData("employees.email") }}" placeholder="{{ trans('music::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <select name="filter[music_offices.status]" class="select-search select-grid filter-grid form-control">
                                            <option> </option>
                                            <option value="1"  @if(CoreForm::getFilterData("music_offices.status")==="1") selected="true" @endif>{{ trans('music::view.Enable') }}</option>
                                            <option value="0" @if(CoreForm::getFilterData("music_offices.status")==="0") selected ="true" @endif>{{ trans('music::view.Disable') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        @if(isset($collectionModel) && count($collectionModel))
                            <?php $i = CoreView::getNoStartGrid($collectionModel); ?>
                            @foreach($collectionModel as $item)
                                <tr>
                                    <td>{{ $i }}</td>
                                    @if ($item->name)
                                        <td style="max-width: 200px">
                                            {{$item->name}}
                                        </td>
                                    @else
                                        <td>&nbsp;</td>
                                    @endif
                                    @if ($item->sort_order)
                                        <td>
                                            {{$item->sort_order}}
                                        </td>
                                    @else
                                        <td>&nbsp;</td>
                                    @endif
                                    @if ($item->employee_noti)
                                        <td>
                                            {{CoreView::getNickName($item->email)}}
                                        </td>
                                    @else
                                        <td>&nbsp;</td>
                                    @endif
                                    @if ($item->status == 0)
                                        <td>
                                            Disable
                                        </td>
                                    @else
                                        <td>Enable</td>
                                    @endif
                                    <td>{{$item->created_at}}</td>
                                    <td class="text-center">
                                        <a class="btn-edit" href="{{URL::route('music::manage.offices.edit',$item->id)}}"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>
                                        <form style="display: inline-block;" action="{{URL::route('music::manage.offices.del',$item->id)}}" method="POST">
                                            {{ csrf_field() }}
                                            <button class="btn-delete delete-confirm" type="submit"><i class="fa fa-trash-o" aria-hidden="true"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                <?php $i++; ?>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="text-center">
                                    <h2 class="no-result-grid">{{ trans('music::view.No results found') }}</h2>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="box-body">
                @include('team::include.pager')
            </div>
            <!-- modal show full message -->
            <div id="showMess" class="modal fade" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">{{ trans('music::view.Message order') }}</h4>
                      </div>
                      <div class="modal-body">
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                      </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        selectSearchReload();
    });
</script>
@endsection

