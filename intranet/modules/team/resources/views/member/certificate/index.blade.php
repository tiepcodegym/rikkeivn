<?php
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Team\View\Config as TeamConfig;
use Rikkei\Team\Model\Certificate;

$tabTitleSub = trans('team::profile.cer_title_sub');
$status = Certificate::getOptionStatus();
$filter = Form::getFilterData();
?>
@extends('team::member.profile_row_layout')
@section('content_profile')
<div class="table-responsive">
    <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
        <thead>
            <tr>
                <th style="width:40px;">{!!trans('core::view.NO.')!!}</th>
                <th class="sorting {!!TeamConfig::getDirClass('name')!!}" data-order="name" data-dir="{!!TeamConfig::getDirOrder('name')!!}" style="width:190px;">{{ trans('team::view.Certificate') }}</th>
              <!--   <th class="sorting {!!TeamConfig::getDirClass('type')!!}" data-order="type" data-dir="{!!TeamConfig::getDirOrder('type')!!}">{{ trans('team::view.Type') }}</th> -->
                <th class="sorting {!!TeamConfig::getDirClass('level')!!}" data-order="level" data-dir="{!!TeamConfig::getDirOrder('level')!!}">{{ trans('team::view.Level') }}</th>
                <th class="sorting {!!TeamConfig::getDirClass('start_at')!!}" data-order="start_at" data-dir="{!!TeamConfig::getDirOrder('start_at')!!}">{{ trans('team::profile.From') }}</th>
                <th class="sorting {!!TeamConfig::getDirClass('end_at')!!}" data-order="end_at" data-dir="{!!TeamConfig::getDirOrder('end_at')!!}">{{ trans('team::profile.To') }}</th>
                <th class="sorting {!!TeamConfig::getDirClass('status')!!}" data-order="status" data-dir="{!!TeamConfig::getDirOrder('status')!!}">{{ trans('team::view.Status') }}</th>
                <th class="col-action col-a2{!!$isAccessSubmitForm!!}" style="width: 50px;">Action</th>
            </tr>
        </thead>
        <tbody>
            <tr class="filter-input-grid">
                <td>&nbsp;</td>
                <td>
                    <div class="row">
                        <div class="col-md-12">
                            <input type="text" name="filter[name]" value="{{ Form::getFilterData('name') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                        </div>
                    </div>
                </td>
               <!--  <td>
                    <div class="row">
                        <div class="col-md-12">
                            <select name="filter[number][type]" class="form-control select-grid filter-grid select-search" autocomplete="off">
                                <option value="">&nbsp;</option>
                                <?php
                                $filterRaise = Form::getFilterData('number', 'type');
                                ?>
                                @foreach($certificateTypes as $key => $value)
                                    <option value="{{ $key }}"<?php
                                        if ($key == $filterRaise): ?> selected<?php endif;
                                            ?>>{!! $value !!}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </td> -->
                <td>
                    <div class="row">
                        <div class="col-md-12">
                            <input type="text" name="filter[level]" value="{{ Form::getFilterData('level') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                        </div>
                    </div>
                </td>
                <td>
                    <div class="row">
                        <div class="col-md-12">
                            <input type="text" name="filter[start_at]" value="{{ Form::getFilterData('start_at') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                        </div>
                    </div>
                </td>
                <td>
                    <div class="row">
                        <div class="col-md-12">
                            <input type="text" name="filter[end_at]" value="{{ Form::getFilterData('end_at') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                        </div>
                    </div>
                </td><td>
                    <div class="row">
                        <div class="col-md-12">
                            <select class="form-control recruiter-box filter-grid" id="recruiterList" name="filter[status]">
                                <option value="">&nbsp;</option>
                                @foreach($status as $key=>$option)
                                    <option value="{{ $key }}" {{ isset($filter['status']) ? ($filter['status'] == $key ? 'selected' : '') : '' }}
                                    >{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </td>
                <td class="col-action col-a2{!!$isAccessSubmitForm!!}" style="width: 50px;"></td>
            </tr>
            @if (count($collectionModel))
                <?php $i = CoreView::getNoStartGrid($collectionModel); ?>
                @foreach ($collectionModel as $item)
                <tr>
                    <td>{{ $i++ }}</td>
                    <td>
                        <a href="{!!route('team::member.profile.index', ['employeeId' => $employeeModelItem->id, 'type' => $tabType, 'typeId' => $item->id])!!}" data-text-short>
                            {{ $item->name }}
                        </a>
                    </td>
                   <!--  <td>{{ CoreView::getValueArray($certificateTypes, [$item->type]) }}</td> -->
                    <td>{{ $item->level }}</td>
                    <td>{{ $item->start_at }}</td>
                    <td>{{ $item->end_at }}</td>
                    <td>{{ $status[$item->status] }}</td>
                    <td class="{!!$isAccessSubmitForm!!}">
                        <button class="btn-delete" title="{{ trans('core::view.Remove') }}"
                            data-btn-submit="ajax"
                            data-submit-noti="{!!trans('team::profile.Are you sure delete this certificate?')!!}"
                            action="{!!route('team::member.profile.item.relate.delete', ['employeeId' => $employeeModelItem->id, 'type' => $tabType, 'typeId' => $item->id])!!}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="9" class="text-center">{{ trans('resource::message.No data') }}</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
@endsection
