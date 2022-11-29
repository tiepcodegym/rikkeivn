<?php
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Team\View\Config as TeamConfig;

$tabTitleSub = trans('team::profile.onsite_title_sub');
?>
@extends('team::member.profile_row_layout')
@section('content_profile')
<div class="table-responsive">
    <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
        <thead>
            <tr>
                <th style="width:40px;">{!!trans('core::view.NO.')!!}</th>
                <th class="sorting {!!TeamConfig::getDirClass('place')!!}" data-order="place" data-dir="{!!TeamConfig::getDirOrder('place')!!}">{{ trans('team::profile.Place') }}</th>
                <th class="sorting {!!TeamConfig::getDirClass('start_at')!!}" data-order="start_at" data-dir="{!!TeamConfig::getDirOrder('start_at')!!}">{{ trans('team::profile.Start at') }}</th>
                <th class="sorting {!!TeamConfig::getDirClass('end_at')!!}" data-order="end_at" data-dir="{!!TeamConfig::getDirOrder('end_at')!!}">{{ trans('team::profile.End at') }}</th>
                <th class="col-action col-a2{!!$isAccessSubmitForm!!}" style="width: 50px;"></th>
            </tr>
        </thead>
        <tbody>
            <tr class="filter-input-grid">
                <td>&nbsp;</td>
                <td>
                    <div class="row">
                        <div class="col-md-12">
                            <input type="text" name="filter[place]" value="{{ Form::getFilterData('place') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                        </div>
                    </div>
                </td>
                <td>
                    <div class="row">
                        <div class="col-md-12">
                            <input type="text" name="filter[date][start_at]" value="{{ Form::getFilterData('date', 'start_at') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                        </div>
                    </div>
                </td>
                <td>
                    <div class="row">
                        <div class="col-md-12">
                            <input type="text" name="filter[date][end_at]" value="{{ Form::getFilterData('date', 'end_at') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                        </div>
                    </div>
                </td>
                <td class="{!!$isAccessSubmitForm!!}">&nbsp;</td>
            </tr>
            @if (count($collectionModel))
                <?php $i = CoreView::getNoStartGrid($collectionModel); ?>
                @foreach ($collectionModel as $item)
                <tr>
                    <td>{{ $i++ }}</td>
                    <td>
                        <a href="{!!route('team::member.profile.index', ['employeeId' => $employeeModelItem->id, 'type' => $tabType, 'typeId' => $item->id])!!}" data-text-short>
                            {{ $item->place }}
                        </a>
                    </td>
                    <td>{{ $item->start_at }}</td>
                    <td>{{ $item->end_at }}</td>
                    <td class="{!!$isAccessSubmitForm!!}">
                        <button class="btn-delete" title="{{ trans('core::view.Remove') }}"
                            data-btn-submit="ajax"
                            data-submit-noti="{!!trans('team::profile.Are you sure delete this want?')!!}"
                            action="{!!route('team::member.profile.item.relate.delete', ['employeeId' => $employeeModelItem->id, 'type' => $tabType, 'typeId' => $item->id])!!}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="8" class="text-center">{{ trans('resource::message.No data') }}</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
@endsection