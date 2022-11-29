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
                <th class="sorting {!!TeamConfig::getDirClass('name')!!}" data-order="name" data-dir="{!!TeamConfig::getDirOrder('name')!!}" style="width:190px;">{{ trans('team::profile.Skill name') }}</th>
                <th class="sorting {!!TeamConfig::getDirClass('type')!!}" data-order="type" data-dir="{!!TeamConfig::getDirOrder('type')!!}">{{ trans('team::profile.Skill type') }}</th>
                <th class="sorting {!!TeamConfig::getDirClass('level')!!}" data-order="level" data-dir="{!!TeamConfig::getDirOrder('level')!!}">{{ trans('team::view.Level') }}</th>
                <th class="sorting {!!TeamConfig::getDirClass('experience')!!}" data-order="experience" data-dir="{!!TeamConfig::getDirOrder('experience')!!}">{{ trans('team::view.Experience') }}</th>
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
                        <?php
                        $filterAssignedName = Form::getFilterData('number', 'type');
                        ?>
                        <select name="filter[number][type]" class="form-control select-grid filter-grid select-search" autocomplete="off">
                            <option>&nbsp;</option>
                            @foreach($skillTypes as $key => $value)
                                <option class="ds" value="{{ $key }}" <?php
                                    if ($key == $filterAssignedName): ?> selected<?php endif; 
                                        ?>>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </td>
            <td>
                <div class="row">
                    <div class="col-md-12">
                        <div class="col-md-12">
                        <?php
                        $filterAssignedName = Form::getFilterData('number', 'level');
                        ?>
                        <select name="filter[number][level]" class="form-control select-grid filter-grid select-search" autocomplete="off">
                            <option>&nbsp;</option>
                            @for($i = 1; $i < 6; $i++)
                                <option class="ds" value="{{ $i }}" <?php
                                    if ($i == $filterAssignedName): ?> selected<?php endif; 
                                        ?>>{{ $i }}</option>
                            @endfor
                        </select>
                        </div>
                    </div>
                </div>
            </td>
            <td>
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" min="0" name="filter[experience]" value="{{ Form::getFilterData('experience') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                    </div>
                </div>
            </td>
            <td>&nbsp;</td>
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
                    <td>{{ CoreView::getValueArray($skillTypes, [$item->type]) }}</td>
                    <td>{{ $item->level }}</td>
                    <?php $item->loadExper(); ?>
                    <td>{{ ($item->exp_y ? $item->exp_y . ' ' . trans('team::profile.Year') : '') . ' ' .
                        ($item->exp_m ? $item->exp_m . ' ' . trans('team::profile.Month') : '') }}</td>
                    <td class="{!!$isAccessSubmitForm!!}">
                        <button class="btn-delete" title="{{ trans('core::view.Remove') }}"
                            data-btn-submit="ajax"
                            data-submit-noti="{!!trans('team::profile.Are you sure delete this skill?')!!}"
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
