<?php
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Team\View\Config as TeamConfig; ?>
@extends('team::member.profile_row_layout')
@section('content_profile')
<div class="table-responsive">
    <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
        <thead>
            <tr>
                <th style="width:40px;">{!!trans('core::view.NO.')!!}</th>
                <th class="sorting {!!TeamConfig::getDirClass('name')!!}" data-order="name" data-dir="{!!TeamConfig::getDirOrder('name')!!}" style="width:190px;">{{ trans('team::profile.Doc name') }}</th>
                <th class="sorting {!!TeamConfig::getDirClass('place')!!}" data-order="place" data-dir="{!!TeamConfig::getDirOrder('place')!!}">{{ trans('team::profile.Doc place') }}</th>
                <th class="sorting {!!TeamConfig::getDirClass('issue_date')!!}" data-order="issue_date" data-dir="{!!TeamConfig::getDirOrder('issue_date')!!}">{{ trans('team::profile.Doc issue date') }}</th>
                <th class="sorting {!!TeamConfig::getDirClass('expire_date')!!}" data-order="expire_date" data-dir="{!!TeamConfig::getDirOrder('expire_date')!!}">{{ trans('team::profile.Doc expire date') }}</th>
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
                            <input type="text" name="filter[place]" value="{{ Form::getFilterData('place') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                        </div>
                    </div>
                </td>
                <td>
                    <div class="row">
                        <div class="col-md-12">
                            <input type="text" name="filter[issue_date]" value="{{ Form::getFilterData('issue_date') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                        </div>
                    </div>
                </td>
                <td>
                    <div class="row">
                        <div class="col-md-12">
                            <input type="text" name="filter[expired_date]" value="{{ Form::getFilterData('expired_date') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
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
                            {{ $item->name }}
                        </a>
                    </td>
                    <td>{{ $item->place }}</td>
                    <td>{{ $item->issue_date }}</td>
                    <td>{{ $item->expired_date }}</td>
                    <td class="{!!$isAccessSubmitForm!!}">
                        <button class="btn-delete" title="{{ trans('core::view.Remove') }}"
                            data-btn-submit="ajax"
                            data-submit-noti="{!!trans('team::profile.Are you sure delete this document?')!!}"
                            action="{!!route('team::member.profile.item.relate.delete', ['employeeId' => $employeeModelItem->id, 'type' => $tabType, 'typeId' => $item->id])!!}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="6" class="text-center">{{ trans('resource::message.No data') }}</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
@endsection
