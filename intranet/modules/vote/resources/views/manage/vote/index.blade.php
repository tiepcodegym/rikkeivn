<?php
use Rikkei\Vote\View\VoteConst;
use Rikkei\Core\View\Form as FormView;
use Rikkei\Team\View\Config as ConfigView;
?>

@extends('layouts.default')

@section('title', trans('vote::view.list_votes'))

@section('css')

@include('vote::include.css')

@stop

@section('content')

<div class="box box-info">
    <div class="box-body">
        <div class="row">
            <div class="col-sm-4">
                @if (VoteConst::hasPermissCreate())
                <a href="{{ route('vote::manage.vote.create') }}" class="btn-add"><i class="fa fa-plus"></i> {{ trans('vote::view.create_vote') }}</a>
                @endif
            </div>
            <div class="col-sm-8">
                @include('team::include.filter')
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-striped dataTable table-bordered table-hover table-grid-data vote-table">
            <thead>
                <tr>
                    <th>STT</th>
                    <th class="sorting {{ ConfigView::getDirClass('title') }} col-title" data-order="title" data-dir="{{ ConfigView::getDirOrder('title') }}">{{ trans('vote::view.title') }}</th>
                    <th class="sorting {{ ConfigView::getDirClass('nominate_start_at') }} col-title" data-order="nominate_start_at" data-dir="{{ ConfigView::getDirOrder('nominate_start_at') }}">{{ trans('vote::view.nominate_start_at') }}</th>
                    <th class="sorting {{ ConfigView::getDirClass('nominate_end_at') }} col-title" data-order="nominate_end_at" data-dir="{{ ConfigView::getDirOrder('nominate_end_at') }}">{{ trans('vote::view.nominate_end_at') }}</th>
                    <th class="sorting {{ ConfigView::getDirClass('vote_start_at') }} col-title" data-order="vote_start_at" data-dir="{{ ConfigView::getDirOrder('vote_start_at') }}">{{ trans('vote::view.vote_start_at') }}</th>
                    <th class="sorting {{ ConfigView::getDirClass('vote_end_at') }} col-title" data-order="vote_end_at" data-dir="{{ ConfigView::getDirOrder('vote_end_at') }}">{{ trans('vote::view.vote_end_at') }}</th>
                    <th class="sorting {{ ConfigView::getDirClass('nominee_max') }} col-title" data-order="nominee_max" data-dir="{{ ConfigView::getDirOrder('nominee_max') }}">{{ trans('vote::view.nominee_max') }}</th>
                    <th class="sorting {{ ConfigView::getDirClass('vote_max') }} col-title" data-order="vote_max" data-dir="{{ ConfigView::getDirOrder('vote_max') }}">{{ trans('vote::view.vote_max') }}</th>
                    <th class="min-w-65 sorting {{ ConfigView::getDirClass('status') }} col-title" data-order="status" data-dir="{{ ConfigView::getDirOrder('status') }}">{{ trans('vote::view.status') }}</th>
                    <th class="min-w-120"></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td></td>
                    <td>
                        <input type="text" name="filter[title]" value="{{ FormView::getFilterData('title') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                    </td>
                    <td>
                        <input type="text" name="filter[nominate_start_at]" value="{{ FormView::getFilterData('nominate_start_at') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                    </td>
                    <td>
                        <input type="text" name="filter[nominate_end_at]" value="{{ FormView::getFilterData('nominate_end_at') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                    </td>
                    <td>
                        <input type="text" name="filter[vote_start_at]" value="{{ FormView::getFilterData('vote_start_at') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                    </td>
                    <td>
                        <input type="text" name="filter[vote_end_at]" value="{{ FormView::getFilterData('vote_end_at') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                    </td>
                    <td>
                        <input type="text" name="filter[nominee_max]" value="{{ FormView::getFilterData('nominee_max') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                    </td>
                    <td>
                        <input type="text" name="filter[vote_max]" value="{{ FormView::getFilterData('vote_max') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                    </td>
                    <td>
                        <select name="filter[status]" class="form-control select-grid filter-grid select-search">
                            <?php 
                            $statuses = VoteConst::getVoteStatuses(); 
                            $filterStatus = FormView::getFilterData('status');
                            ?>
                            <option value="">&nbsp;</option>
                            @foreach ($statuses as $key => $label)
                            <option value="{{ $key }}" {{ $key == $filterStatus ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td></td>
                </tr>
                @if (!$collectionModel->isEmpty())
                <?php
                $perPage = $collectionModel->perPage();
                $currentPage = $collectionModel->currentPage();
                ?>
                @foreach($collectionModel as $order => $item)
                <tr>
                    <td>{{ $order + 1 + $perPage * ($currentPage - 1) }}</td>
                    <td class="td-title max-w-350">{{ $item->title }}</td>
                    <td>{{ $item->nominate_start_at ? $item->nominate_start_at->format('Y-m-d') : '' }}</td>
                    <td>{{ $item->nominate_end_at ? $item->nominate_end_at->format('Y-m-d') : '' }}</td>
                    <td>{{ $item->vote_start_at->format('Y-m-d') }}</td>
                    <td>{{ $item->vote_end_at->format('Y-m-d') }}</td>
                    <td>{{ $item->nominee_max }}</td>
                    <td>{{ $item->vote_max }}</td>
                    <td>{{ $item->getStatusLabel() }}</td>
                    <td class="content-group">
                        @if (VoteConst::hasPermissEdit($item, 'vote::manage.vote.update'))
                            <a href="{{ route('vote::manage.vote.edit', ['id' => $item->id]) }}" data-toggle="tooltip" title="{{ trans('vote::view.edit') }}" data-placement="top" class="btn-edit"><i class="fa fa-edit"></i></a>

                            {!! Form::open(['method' => 'delete', 'route' => ['vote::manage.vote.delete', $item->id], 'class' => 'form-inline']) !!}
                            <button class="btn-delete delete-confirm" data-toggle="tooltip" title="{{ trans('vote::view.delete') }}" data-placement="top"><i class="fa fa-trash"></i></button>
                            {!! Form::close() !!}
                        @else 
                            <a href="{{ route('vote::manage.vote.edit', ['id' => $item->id]) }}" data-toggle="tooltip" title="{{ trans('vote::view.view') }}" data-placement="top" class="btn-edit"><i class="fa fa-eye"></i></a>
                        @endif
                    </td>
                </tr>
                @endforeach
                @else
                <tr>
                    <td colspan="10" class="text-center">
                        <h3>{{ trans('vote::message.not_found_item') }}</h3>
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

@stop

@section('script')

@include('vote::include.script')

@stop

