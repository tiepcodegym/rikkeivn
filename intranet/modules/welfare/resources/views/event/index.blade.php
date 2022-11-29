@extends('layouts.default')

@section('title')
    {{ $titleHeadPage }}
@endsection
<?php

    use Rikkei\Team\View\Config as TeamConfig;
    use Rikkei\Core\View\View as CoreView;
    use Rikkei\Core\View\Form as CoreForm;
    use Rikkei\Team\View\Permission;
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Welfare\Model\Event;

    $permision = Permission::getInstance()->isAllow('welfare::welfare.event.save');
?>
@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css"/>
    <link rel="stylesheet" href="{{ URL::asset('asset_news/css/news.css') }}"/>
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_welfare/css/style.css') }}"/>

@endsection

@section('content')
    <div class="row event-list">
        <div class="col-sm-12">
            <div class="box box-info">
                <div class="box-body">
                    <div class="col-md-6">
                        <a href="{{ URL::route('welfare::welfare.event.create') }}">
                            <button id="add-new-group" type="button" class="btn-add add-college" data-toggle="modal"
                                    data-placement="bottom" data-target="" title="thêm mới"
                                    data-modal="true">
                                <i class="fa fa-plus"></i>
                            </button>
                        </a>
                    </div>
                    <div class="clo-md-6">@include('team::include.filter')</div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                        <thead>
                        <tr>
                            <th class="col-id width-10"></th>
                            <th class="sorting {{ TeamConfig::getDirClass('name') }} col-name" data-order="name"
                                data-dir="{{ TeamConfig::getDirOrder('name') }}">{{trans('welfare::view.Name event')}}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('groupName') }} col-name"
                                data-order="groupName"
                                data-dir="{{ TeamConfig::getDirOrder('groupName') }}">{{trans('welfare::view.Group event')}}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('address') }} col-name" data-order="address"
                                data-dir="{{ TeamConfig::getDirOrder('address') }}">{{trans('welfare::view.Address')}}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('start_at_exec') }} col-name"
                                data-order="start_at_exec"
                                data-dir="{{ TeamConfig::getDirOrder('start_at_exec') }}">{{trans('welfare::view.Start_at_exec')}}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('end_at_exec') }} col-name"
                                data-order="end_at_exec"
                                data-dir="{{ TeamConfig::getDirOrder('end_at_exec') }}">{{trans('welfare::view.End_at_exec')}}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('end_at_register') }} col-name"
                                data-order="end_at_register"
                                data-dir="{{ TeamConfig::getDirOrder('end_at_register') }}">{{trans('welfare::view.End_at_register')}}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('fee_total') }} col-name"
                                data-order="fee_total"
                                data-dir="{{ TeamConfig::getDirOrder('fee_total') }}">{{trans('welfare::view.Fee_total')}}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('fee_total_actual') }} col-name"
                                data-order="fee_total_actual"
                                data-dir="{{ TeamConfig::getDirOrder('fee_total_actual') }}">{{trans('welfare::view.Fee_total_actual')}}</th>
                            <th>{{trans('welfare::view.Is register')}}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('status') }} col-name" data-order="status"
                                data-dir="{{ TeamConfig::getDirOrder('status') }}">{{trans('welfare::view.Status')}}</th>
                            @if($permision)
                                <th class=""></th>
                            @endif
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="filter-input-grid">
                            <td>&nbsp;</td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[welfares.name]"
                                               value="{{ CoreForm::getFilterData("welfares.name") }}"
                                               placeholder="{{ trans('team::view.Search') }}"
                                               class="filter-grid form-control"/>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[welfare_groups.name]"
                                               value="{{ CoreForm::getFilterData("welfare_groups.name") }}"
                                               placeholder="{{ trans('team::view.Search') }}"
                                               class="filter-grid form-control"/>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[welfares.address]"
                                               value="{{ CoreForm::getFilterData("welfares.address") }}"
                                               placeholder="{{ trans('team::view.Search') }}"
                                               class="filter-grid form-control"/>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[welfares.start_at_exec]"
                                               value="{{ CoreForm::getFilterData("welfares.start_at_exec") }}"
                                               placeholder="{{ trans('team::view.Search') }}"
                                               class="filter-grid form-control"/>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[welfares.end_at_exec]"
                                               value="{{ CoreForm::getFilterData("welfares.end_at_exec") }}"
                                               placeholder="{{ trans('team::view.Search') }}"
                                               class="filter-grid form-control"/>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[welfares.end_at_register]"
                                               value="{{ CoreForm::getFilterData("welfares.end_at_register") }}"
                                               placeholder="{{ trans('team::view.Search') }}"
                                               class="filter-grid form-control"/>
                                    </div>
                                </div>
                            </td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php
                                            $listStatus = Event::getOptionStatus();
                                            $filterStatus =  CoreForm::getFilterData("welfares.status");
                                        ?>
                                         <select name="filter[welfares.status]" class="form-control select-grid filter-grid select-search">
                                            <option>&nbsp;</option>
                                            @foreach($listStatus as $key => $value)
                                            <option class="ds" value="{{ $key }}" <?php if ($key == $filterStatus): ?> selected<?php endif; ?>>{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </td>
                            <td>&nbsp;</td>
                        </tr>
                        @if(isset($collectionModel) && count($collectionModel))
                            <?php $i = CoreView::getNoStartGrid($collectionModel);?>
                            @foreach($collectionModel as $item)
                                <tr id="{{ $item->id}}"
                                    href="{{ route('welfare::welfare.event.edit', ['id' => $item->id ]) }}">
                                    <td id="item_id" hidden="true"></td>
                                    <td class="detail_item">{{ $i }}</td>
                                    <td class="detail_item">
                                        {{ $item->name }}
                                    </td>
                                    <td class="detail_item">{{ $item->groupName}}</td>
                                    <td class="detail_item">{{ $item->address}}</td>
                                    <td class="detail_item wel-time">{{ $item->start_at_exec}}</td>
                                    <td class="detail_item wel-time">{{ $item->end_at_exec}}</td>
                                    <td class="detail_item wel-time">@if($item->end_at_register!=0){{ $item->end_at_register}} @endif</td>
                                    <td class="detail_item number-format">{{ number_format($item->fee_total) }}</td>
                                    <td class="detail_item number-format">{{ number_format($item->fee_total_actual) }}</td>
                                    <td><input data-id="{{$item->id}}" id="view-list-is-register-online" type="checkbox" class="format-checkox" @if(date('Y-m-d H:i:s') > $item->end_at_register) disabled @endif @if($item->is_register_online) checked="checked" @endif></td>
                                    <td class="detail_item">{{ $status[$item->status]}}</td>
                                    <td class="detail_item" hidden>{{ $item->participant_desc}}</td>
                                    <td class="detail_item numbe" hidden>{{ number_format($item->empl_trial_fee)}}</td>
                                    <td class="detail_item number"
                                        hidden>{{ number_format($item->empl_trial_company_fee)}}</td>
                                    <td class="detail_item number"
                                        hidden>{{ number_format($item->empl_offical_company_fee)}}</td>

                                    @if($permision)
                                        <td class="row">
                                            <div class="col-md-6">
                                                <a href="{{ route('welfare::welfare.event.edit', ['id' => $item->id ]) }}"
                                                   class="btn-edit" title="{{ trans('team::view.Edit') }}"><i
                                                            class="fa fa-edit"></i></a>
                                            </div>
                                            <div class="col-md-6">
                                                <form action="{{ route('welfare::welfare.event.delete') }}"
                                                      method="post"
                                                      class="form-inline">
                                                    {!! csrf_field() !!}
                                                    {!! method_field('delete') !!}
                                                    <input type="hidden" name="id" value="{{ $item->id }}"/>
                                                    <button href="" class="btn-delete delete-confirm" disabled>
                                                        <span><i class="fa fa-trash"></i></span>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                                <?php $i++; ?>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="text-center">
                                    <h2 class="no-result-grid">{{ trans('welfare::view.No results found') }}</h2>
                                </td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
                <div class="box-body">
                    @include('team::include.pager')
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade row modal-detail-welfare" id="myModal">
        <div class="modal-dialog">
            <div class="modal-content col-md-12">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="fa fa-info-circle"></i> {{trans('welfare::view.Common info')}}</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="box box-solid">
                                    <div class="table-responsive">
                                        <table id="moal-table1" class="col-md-12  col-sm-12 table table-striped table-bordered table-grid-data">
                                            <tr name="name">
                                                <td class="col-md-6">{{trans('welfare::view.Name event')}}</td>
                                            </tr>
                                            <tr name="groupName">
                                                <td>{{trans('welfare::view.Group event')}}</td>
                                            </tr>
                                            <tr name="namePur">
                                                <td>{{trans('welfare::view.Purpose')}}</td>
                                            </tr>
                                            <tr name="address">
                                                <td>{{trans('welfare::view.Address')}}</td>
                                            </tr>
                                            <tr name="nameOrg">
                                                <td>{{trans('welfare::view.Organizer')}}</td>
                                            </tr>
                                            <tr name="namePart">
                                                <td>{{trans('welfare::view.Partners')}}</td>
                                            </tr>
                                            <tr name="status">
                                                <td>{{trans('welfare::view.Status')}}</td>
                                            </tr>
                                            <tr name="start_at_exec">
                                                <td>{{trans('welfare::view.Start_at_exec')}}</td>
                                            </tr>
                                            <tr name="end_at_exec">
                                                <td>{{trans('welfare::view.End_at_exec')}}</td>
                                            </tr>
                                            <tr name="convert_end_at_register">
                                                <td>{{trans('welfare::view.End_at_register')}}</td>
                                            </tr>
                                            <tr name="description" id="description">
                                                <td>{{trans('welfare::view.Description Description')}}</td>
                                            </tr>
                                        </table>
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    <!-- Modal -->
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="{{ CoreUrl::asset('asset_welfare/js/tab_participants_employee.js') }}"></script>
    <script>
        url = '{{ route("welfare::welfare.register.online") }}';
    </script>
@endsection

