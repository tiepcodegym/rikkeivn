@extends('layouts.default')

@section('title')
{{ trans('event::view.List invitation') }}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
@endsection

@section('content')
<?php
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View;
?>
<div class="row">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="box-body">
                <div class="filter-action">
                    <button class="btn btn-success" id="btn-export-event-birthday">
                        <span>Export</span>
                    </button>
                    <button class="btn btn-primary btn-reset-filter">
                        <span>{{ trans('team::view.Reset filter') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
                    </button>
                    <button class="btn btn-primary btn-search-filter">
                        <span>{{ trans('team::view.Search') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                    <thead>
                        <tr>
                            <th class="col-id" style="width: 20px;">{{ trans('project::view.No.') }}</th>
                            <th class="sorting {{ Config::getDirClass('name') }} col-name" data-order="name" data-dir="{{ Config::getDirOrder('name') }}">{{ trans('event::view.Name') }}</th>
                            <th class="sorting {{ Config::getDirClass('email') }} col-name" data-order="email" data-dir="{{ Config::getDirOrder('email') }}">Email</th>
                            <th class="sorting {{ Config::getDirClass('company') }} col-name" data-order="company" data-dir="{{ Config::getDirOrder('company') }}">{{ trans('event::view.Company customer') }}</th>
                            <th class="sorting {{ Config::getDirClass('status') }} col-name" data-order="status" data-dir="{{ Config::getDirOrder('status') }}">{{ trans('event::view.Status') }}</th>
                            <th class="sorting {{ Config::getDirClass('join_tour') }} col-name" data-order="join_tour" data-dir="{{ Config::getDirOrder('join_tour') }}">{{ trans('event::view.Join tour') }}</th>
                            <th>Người tham gia</th>
                            <th class="sorting {{ Config::getDirClass('booking_room') }}" data-order="booking_room" data-dir="{{ Config::getDirOrder('booking_room') }}">Đăng ký khách sạn</th>
                            <th class="sorting {{ Config::getDirClass('sender_name') }} col-name" data-order="sender_name" data-dir="{{ Config::getDirOrder('sender_name') }}">{{ trans('event::view.Sender name') }}</th>
                            <th class="sorting {{ Config::getDirClass('sender_email') }} col-name" data-order="sender_email" data-dir="{{ Config::getDirOrder('sender_email') }}">{{ trans('event::view.Sender email') }}</th>
                            <th class="">{{ trans('event::view.Note') }}</th>
                            <th class="sorting {{ Config::getDirClass('created_at') }}" style="width: 50px;" data-order="created_at" data-dir="{{ Config::getDirOrder('created_at') }}">{{ trans('event::view.Send at') }}</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="filter-input-grid">
                            <td>&nbsp;</td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[name]" value="{{ Form::getFilterData("name") }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[email]" value="{{ Form::getFilterData("email") }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[company]" value="{{ Form::getFilterData("company") }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                           
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <select style="width: 90px" class="form-control select-grid filter-grid select-search" name="filter[status]">
                                            <option value="">&nbsp;</option>
                                            @foreach($statusOptions as $key => $value)
                                                <option value="{{ $key }}" {{ (is_numeric(Form::getFilterData('status')) && Form::getFilterData('status') == $key) ? 'selected' : '' }}>{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </td>
                            
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <select class="form-control select-grid filter-grid select-search" name="filter[join_tour]">
                                            <option value="">&nbsp;</option>
                                            @foreach($joinTourOptions as $key => $value)
                                                <option value="{{ $key }}"{{ (Form::getFilterData('join_tour') == $key) ? ' selected' : '' }}>{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </td>
                            <td>
                                
                            </td>
                            <td>
                                
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[sender_name]" value="{{ Form::getFilterData("sender_name") }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[sender_email]" value="{{ Form::getFilterData("sender_email") }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[note]" value="{{ Form::getFilterData("note") }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[updated_at]" value="{{ Form::getFilterData("updated_at") }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            {{-- <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <select class="form-control select-grid filter-grid select-search" name="filter[booking_room]">
                                            <option value="">&nbsp;</option>
                                            @foreach($bookingOptions as $key => $value)
                                                <option value="{{ $key }}"{{ (Form::getFilterData('booking_room') == $key) ? ' selected' : '' }}>{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </td> --}}
                        </tr>
                        @if(isset($collectionModel) && count($collectionModel))
                            <?php $i = View::getNoStartGrid($collectionModel); ?>
                            @foreach($collectionModel as $item)
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->email }}</td>
                                    <td>{{ $item->company }}</td>
                                    <td>{{ $item->getStatus($statusOptions) }}</td>
                                    <td>{{ $item->getJoinTour() }}</td>
                                    <td>
                                        @php
                                        $attachers = json_decode($item->attacher, true)
                                        @endphp
                                        @if (count($attachers))
                                            @foreach(json_decode($item->attacher, true) as $attach)
                                            <div>{{ $attach['name'] }}</div>
                                            <div>{{ $attach['alphabet'] }}</div>
                                            <div>{{ $attach['company'] }}</div>
                                            <div>{{ $attach['email'] }}</div>
                                            @if (isset($attach['tour']))
                                            <div>Tham gia tour {{ $item->getJoinTour() }}</div>
                                            @endif
                                            <hr>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td>{{ is_null($item->booking_room) ? '' : ($item->booking_room == 0 ? 'Không' : 'Có') }}</td>
                                    <td>{{ $item->sender_name }}</td>
                                    <td>{{ $item->sender_email }}</td>
                                    <td>{!! View::nl2br($item->note) !!}</td>
                                    <td>{{ $item->updated_at }}</td>
                                </tr>
                                <?php $i++; ?>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="14" class="text-center">
                                    <h2 class="no-result-grid">{{ trans('project::view.No results found') }}</h2>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="box-body">
                @include('team::include.pager', ['domainTrans' => 'project'])
            </div>
        </div>
    </div>
</div>
<form action="{{ route('event::brithday.company_list.export') }}" method="post" id="export-event-birthday" class="no-validate">
    {!! csrf_field() !!}
</form>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        selectSearchReload();
    });
    $(document).ready(function() {
        $('#btn-export-event-birthday').click(function () {
            $('#export-event-birthday').submit();
        });
    });
</script>
@endsection
