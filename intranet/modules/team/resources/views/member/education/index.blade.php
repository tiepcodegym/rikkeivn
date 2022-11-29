<?php
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Team\View\Config as TeamConfig;

$tabTitleSub = trans('team::profile.edu_title_sub');
?>
@extends('team::member.profile_row_layout')
@section('content_profile')
<div class="table-responsive">
    <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
        <thead>
            <tr>
                <th style="width:40px;">{!!trans('core::view.NO.')!!}</th>
                <th class="sorting {!!TeamConfig::getDirClass('school')!!}" data-order="school" data-dir="{!!TeamConfig::getDirOrder('school')!!}" style="width:190px;">{{ trans('team::profile.Education Place') }}</th>
                <th class="sorting {!!TeamConfig::getDirClass('start_at')!!}" data-order="start_at" data-dir="{!!TeamConfig::getDirOrder('start_at')!!}">{{ trans('team::profile.From') }}</th>
                <th class="sorting {!!TeamConfig::getDirClass('end_at')!!}" data-order="end_at" data-dir="{!!TeamConfig::getDirOrder('end_at')!!}">{{ trans('team::profile.To') }}</th>
                <th class="sorting {!!TeamConfig::getDirClass('majors')!!}" data-order="majors" data-dir="{!!TeamConfig::getDirOrder('majors')!!}">{{ trans('team::profile.Major') }}</th>
                <th class="sorting {!!TeamConfig::getDirClass('quality')!!}" data-order="quality" data-dir="{!!TeamConfig::getDirOrder('quality')!!}">{{ trans('team::profile.Quality') }}</th>
                <th class="sorting {!!TeamConfig::getDirClass('degree')!!}" data-order="degree" data-dir="{!!TeamConfig::getDirOrder('degree')!!}">{{ trans('team::profile.Degree') }}</th>
                <th class="col-action col-a2{!!$isAccessSubmitForm!!}" style="width: 50px;"></th>
            </tr>
        </thead>
        <tbody>
            <tr class="filter-input-grid">
                <td>&nbsp;</td>
                <td>
                    <div class="row">
                        <div class="col-md-12">
                            <select name="filter[number][school_id]" class="form-control select-grid filter-grid select-search has-search" autocomplete="off">
                                <option value="">&nbsp;</option>
                                <?php
                                $filterSchool = Form::getFilterData('number', 'school_id');
                                ?>
                                @foreach($educationList as $key => $value)
                                    <option value="{{ $key }}"<?php
                                        if ($key == $filterSchool): ?> selected<?php endif;
                                            ?>>{!! $value !!}</option>
                                @endforeach
                            </select>
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
                <td>
                    <div class="row">
                        <div class="col-md-12">
                            <select name="filter[number][major_id]" class="form-control select-grid filter-grid select-search has-search" autocomplete="off">
                                <option value="">&nbsp;</option>
                                <?php
                                $filterM = Form::getFilterData('number', 'major_id');
                                ?>
                                @foreach($majorList as $key => $value)
                                    <option value="{{ $key }}"<?php
                                        if ($key == $filterM): ?> selected<?php endif;
                                            ?>>{!! $value !!}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="row">
                        <div class="col-md-12">
                            <select name="filter[number][quality]" class="form-control select-grid filter-grid select-search" autocomplete="off">
                                <option value="">&nbsp;</option>
                                <?php
                                $filterRaise = Form::getFilterData('number', 'quality');
                                ?>
                                @foreach($educationQualities as $key => $value)
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
                            <select name="filter[number][degree]" class="form-control select-grid filter-grid select-search" autocomplete="off">
                                <option value="">&nbsp;</option>
                                <?php
                                $filterRaise = Form::getFilterData('number', 'degree');
                                ?>
                                @foreach($educationDegree as $key => $value)
                                    <option value="{{ $key }}"<?php
                                        if ($key == $filterRaise): ?> selected<?php endif;
                                            ?>>{!! $value !!}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </td>
            </tr>
            @if (count($collectionModel))
                <?php $i = CoreView::getNoStartGrid($collectionModel); ?>
                @foreach ($collectionModel as $item)
                <tr>
                    <td>{{ $i++ }}</td>
                    <td>
                        <a href="{!!route('team::member.profile.index', ['employeeId' => $employeeModelItem->id, 'type' => $tabType, 'typeId' => $item->id])!!}" data-text-short>
                            @if (isset($educationList[$item->school_id]) && $educationList[$item->school_id])
                                {{ $educationList[$item->school_id] }}
                            @else
                                <i class="fa fa-university"></i><i class="fa fa-university"></i><i class="fa fa-university"></i>
                            @endif
                        </a>
                    </td>
                    <td>{{ $item->start_at }}</td>
                    <td>{{ $item->end_at }}</td>
                    <td data-text-short>
                        @if (isset($majorList[$item->major_id]) && $majorList[$item->major_id])
                            {{ $majorList[$item->major_id] }}
                        @endif
                    </td>
                    <td>{{ CoreView::getValueArray($educationQualities, [$item->quality]) }}</td>
                    <td>{{ CoreView::getValueArray($educationDegree, [$item->degree]) }}</td>
                    <td class="{!!$isAccessSubmitForm!!}">
                        <button class="btn-delete" title="{{ trans('core::view.Remove') }}"
                            data-btn-submit="ajax"
                            data-submit-noti="{!!trans('team::profile.Are you sure delete this education?')!!}"
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
