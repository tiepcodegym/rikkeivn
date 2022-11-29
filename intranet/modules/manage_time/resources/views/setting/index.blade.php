@extends('manage_time::layout.common_layout')

@section('title-common', trans('manage_time::view.The person in charge of the timesheet'))

<?php
    $urlSearchRelatedPerson = route('manage_time::profile.mission.find-employee');
?>

@section('css-common')
<style>
    .item-branch {
        margin: 0 0 10px;
    }
</style>
@endsection

@section('content')
<div class=" setting-system-data-page">

    <div class="box box-info">
        <div class="box-header">
            <h2 class="box-body-title">Cấu hình dữ liệu</h2>
        </div>
        <div class="box-body">
            <form id="form-system-tour_event_birthday" method="post" action="{{ route('manage_time::admin.timekeeping-management.update') }}"
                  class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}

                <div class="form-group row form-label-left">
                    <div class="col-md-12">
                        <div class="row">
                            <label class="col-md-2 col-sm-3">Hà Nội</label>
                            <div class="col-md-10 col-sm-9 item-branch">
                                <select name="hanoi[]" class="form-control select-search-employee" data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee') }}" multiple>
                                    @if(isset($hanoi) && count($hanoi))
                                        @foreach($hanoi as $item)
                                            <option value="{{ $item->employee_id }}" selected>{{ $item->employee_name . ' (' . preg_replace('/@.*/', '',$item->employee_email) . ')' }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-2 col-sm-3">Đà Nẵng</label>
                            <div class="col-md-10 col-sm-9 item-branch">
                                <select name="danang[]" class="form-control select-search-employee" data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee') }}" multiple>
                                    @if(isset($danang) && count($danang))
                                        @foreach($danang as $item)
                                            <option value="{{ $item->employee_id }}" selected>{{ $item->employee_name . ' (' . preg_replace('/@.*/', '',$item->employee_email) . ')' }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-2 col-sm-3">Hồ Chí Minh</label>
                            <div class="col-md-10 col-sm-9 item-branch">
                                <select name="hochiminh[]" class="form-control select-search-employee" data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee') }}" multiple>
                                    @if(isset($hochiminh) && count($hochiminh))
                                        @foreach($hochiminh as $item)
                                            <option value="{{ $item->employee_id }}" selected>{{ $item->employee_name . ' (' . preg_replace('/@.*/', '',$item->employee_email) . ')' }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <label class="col-md-2 col-sm-3"></label>
                            <div class="col-md-10 col-sm-9">
                                <button class="btn-add" type="submit">{{trans('core::view.Save')}}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@section('script-common')
    <script type="text/javascript">
    	var urlSearchRelatedPerson = '{{ $urlSearchRelatedPerson }}';
        $(function() {
            $('.select-search-employee').selectSearchEmployee();
            $('.select-search-employee-no-pagination').selectSearchEmployeeNoPagination();
        });
    </script>
@endsection