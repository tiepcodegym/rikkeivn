<?php
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Team\View\Config as TeamConfig;

$tabTitleSub = trans('team::profile.attach_title_sub');
?>
@extends('team::member.profile_row_layout')
@section('content_profile')

<p>( <em class="text-red">*</em> ): {{ trans('team::profile.Required information') }}</p>

<div class="table-responsive">
    <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
        <thead>
            <tr>
                <th style="width:40px;">{!!trans('core::view.NO.')!!}</th>
                <th class="sorting {!!TeamConfig::getDirClass('title')!!}" data-order="title" data-dir="{!!TeamConfig::getDirOrder('title')!!}">{!! trans('team::profile.File name') !!}</th>
                <th class="sorting {!!TeamConfig::getDirClass('is_file')!!}" data-order="is_file" data-dir="{!!TeamConfig::getDirOrder('is_file')!!}" style="width:60px;">{!! trans('team::profile.has file?') !!}</th>
                <th class="col-action col-a2{!!$isAccessSubmitForm!!}" style="width: 50px;"></th>
            </tr>
        </thead>
        <tbody>
            <tr class="filter-input-grid">
                <td>&nbsp;</td>
                <td>
                    <div class="row">
                        <div class="col-md-12">
                            <input type="text" name="filter[title]" value="{{ Form::getFilterData('title') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                        </div>
                    </div>
                    <td>&nbsp;</td>
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
                            {{ $item->title }} {!! $item->required ? '<em class="text-red">*</em>' : '' !!}
                        </a>
                    </td>
                    <td>
                        @if ($item->is_file)
                            <i class="fa fa-check"></i>
                        @endif
                    </td>
                    <td class="{!!$isAccessSubmitForm!!}">
                        @if (!$item->required)
                        <button class="btn-delete" title="{{ trans('core::view.Remove') }}"
                            data-btn-submit="ajax"
                            data-submit-noti="{!!trans('team::profile.Are you sure delete this document?')!!}"
                            action="{!!route('team::member.profile.item.relate.delete', ['employeeId' => $employeeModelItem->id, 'type' => $tabType, 'typeId' => $item->id])!!}">
                            <i class="fa fa-trash"></i>
                        </button>
                        @endif
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
