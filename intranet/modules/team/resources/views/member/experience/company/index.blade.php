<?php
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Team\View\Config as TeamConfig; ?>
@extends('team::member.profile_row_layout')
@section('content_profile')
<div class="row">
    <div class="col-md-12">
        <p>
            <a href="{!!route('team::member.profile.index', ['employeeId' => $employeeModelItem->id, 'type' => 'experience'])!!}">
                <u>{!!trans('team::profile.Experiences')!!}</u>
            </a>
        </p>
    </div>
</div>
<div class="table-responsive">
    <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
        <thead>
            <tr>
                <th style="width:40px;">{!!trans('core::view.NO.')!!}</th>
                <th class="sorting {!!TeamConfig::getDirClass('name')!!}" data-order="name" data-dir="{!!TeamConfig::getDirOrder('name')!!}" style="width:190px;">{{ trans('team::view.Name') }}</th>
                <th class="sorting {!!TeamConfig::getDirClass('position')!!}" data-order="position" data-dir="{!!TeamConfig::getDirOrder('position')!!}">{{ trans('team::profile.Position') }}</th>
                <th class="sorting {!!TeamConfig::getDirClass('start_at')!!}" data-order="start_at" data-dir="{!!TeamConfig::getDirOrder('start_at')!!}">{{ trans('team::profile.From') }}</th>
                <th class="sorting {!!TeamConfig::getDirClass('end_at')!!}" data-order="end_at" data-dir="{!!TeamConfig::getDirOrder('end_at')!!}">{{ trans('team::profile.To') }}</th>
                <th class="col-action col-a2{!!$isAccessSubmitForm!!}" style="width: 50px;"></th>
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
                <td>
                    <div class="row">
                        <div class="col-md-12">
                            <input type="text" name="filter[position]" value="{{ Form::getFilterData('position') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
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
                </td>
                <td class="col-action col-a2{!!$isAccessSubmitForm!!}"></td>
            </tr>
            @if (count($collectionModel))
                <?php $i = CoreView::getNoStartGrid($collectionModel); ?>
                @foreach ($collectionModel as $item)
                <tr>
                    <td>{{ $i++ }}</td>
                    <td>
                        <a href="{!!route('team::member.profile.index', ['employeeId' => $employeeModelItem->id, 'type' => $tabType, 'typeId' => $item->id])!!}">
                            {{ $item->name }}
                        </a>
                    </td>
                    <td>{{ $item->position }}</td>
                    <td>{{ $item->start_at }}</td>
                    <td>{{ $item->end_at }}</td>
                    <td class="{!!$isAccessSubmitForm!!}">
                        <button class="btn-delete" title="{{ trans('core::view.Remove') }}"
                            data-btn-submit="ajax"
                            data-submit-noti="{!!trans('team::profile.Are you sure delete this company?')!!}"
                            action="{!!route('team::member.profile.item.relate.delete', ['employeeId' => $employeeModelItem->id, 'type' => $tabType, 'typeId' => $item->id])!!}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="10" class="text-center">{{ trans('resource::message.No data') }}</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
@endsection
