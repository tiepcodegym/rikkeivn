<?php
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Team\View\Config as TeamConfig;
?>

@extends('team::member.profile_row_layout')
@section('content_profile')

<div class="table-responsive">
    <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
        <thead>
            <tr>
                <th style="width:40px;">{!!trans('core::view.NO.')!!}</th>
                <th class="sorting {!!TeamConfig::getDirClass('name')!!}" data-order="name" data-dir="{!!TeamConfig::getDirOrder('name')!!}" style="width:190px;">{{ trans('team::profile.Full name') }}</th>
                <th class="sorting {!!TeamConfig::getDirClass('relationship')!!}" data-order="relationship" data-dir="{!!TeamConfig::getDirOrder('relationship')!!}">{{ trans('team::profile.Emergency contact relationship') }}</th>
                <th class="sorting {!!TeamConfig::getDirClass('date_of_birth')!!}" data-order="date_of_birth" data-dir="{!!TeamConfig::getDirOrder('date_of_birth')!!}">{{ trans('team::profile.Date of birth') }}</th>
                <th class="sorting {!!TeamConfig::getDirClass('mobile')!!}" data-order="mobile" data-dir="{!!TeamConfig::getDirOrder('mobile')!!}" style="width:190px;">{{ trans('team::profile.Mobile phone') }}</th>
                <th class="sorting {!!TeamConfig::getDirClass('career')!!}" data-order="career" data-dir="{!!TeamConfig::getDirOrder('career')!!}">{{ trans('team::profile.Carrer') }}</th>
                <th class="sorting {!!TeamConfig::getDirClass('is_dependent')!!}" data-order="is_dependent" data-dir="{!!TeamConfig::getDirOrder('is_dependent')!!}" style="width: 80px;">{{ trans('team::profile.Is dependent') }}</th>
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
                        <div class="col-md-12 select2-np">
                            <select name="filter[number][relationship]" class="form-control select-grid filter-grid select-search" autocomplete="off">
                                <option value="">&nbsp;</option>
                                <?php
                                $filterRaise = Form::getFilterData('number', 'relationship');
                                ?>
                                @foreach($toOptionsRelation as $key => $value)
                                    <option value="{{ $key }}"<?php
                                        if ($key == $filterRaise): ?> selected<?php endif;
                                            ?>>{!! $value !!}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="row">
                        <div class="col-md-12">
                            <input type="text" name="filter[date_of_birth]" value="{{ Form::getFilterData('date_of_birth') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                        </div>
                    </div>
                </td>
                <td>
                    <div class="row">
                        <div class="col-md-12">
                            <input type="text" name="filter[mobile]" value="{{ Form::getFilterData('mobile') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                        </div>
                    </div>
                </td>
                <td>
                    <div class="row">
                        <div class="col-md-12">
                            <input type="text" name="filter[career]" value="{{ Form::getFilterData('career') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                        </div>
                    </div>
                </td>
                <td>&nbsp;</td>
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
                    <td>{{CoreView::getValueArray($toOptionsRelation, [$item->relationship])}}</td>
                    <td>{{ $item->date_of_birth }}</td>
                    <td>{{ $item->mobile }}</td>
                    <td data-text-short>{{ $item->career }}</td>
                    <td>{!!$item->is_dependent ? '<i class="fa fa-check"></i>' : ''!!}</td>
                    <td class="{!!$isAccessSubmitForm!!}">
                        <button class="btn-delete" title="{{ trans('core::view.Remove') }}"
                            data-btn-submit="ajax"
                            data-submit-noti="{!!trans('team::profile.Are you sure delete this relations?')!!}"
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