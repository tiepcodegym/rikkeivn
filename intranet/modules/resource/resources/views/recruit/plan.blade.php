@extends('layouts.default')

@section('title', trans('resource::view.Building plan'))

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="{{ URL::asset('resource/css/recruit.css') }}" />
@endsection

@section('content')

<div class="box box-info">
    <div class="box-body">
        {!! Form::open(['method' => 'get', 'route' => 'resource::recruit.build_plan', 'class' => 'no-validate']) !!}
        <div class="row">
            <div class="col-sm-3">
                <div class="row">
                    <label class="col-md-3 margin-top-5 bold-label">{{ trans('resource::view.Year') }}</label>
                    <div class="col-md-9">
                        <input type="number" min="1" name="year" class="form-control date-picker" value="{{ $currYear }}">
                    </div>
                </div>
            </div>
            <div class="col-sm-9">
                <div class="margin-top-5"><i>({{ trans('resource::view.Click cells to edit') }})</i></div>
            </div>
        </div>
        {!! Form::close() !!}
    </div>
    {!! Form::open(['method' => 'post', 'route' => 'resource::recruit.update_plan']) !!}
    <div class="table-responsive">
        <table id="plan_table" class="table table-striped dataTable table-bordered table-hover table-grid-data">
            <thead>
                <tr class="bg-light-blue">
                    <th rowspan="2" class="vertical-align-middle">{{ trans('resource::view.Team') }}</th>
                    <th colspan="12" class="text-center">{{ trans('resource::view.Month') }}</th>
                </tr>
                <tr class="bg-light-blue">
                    @for($month = 1; $month <= 12; $month++)
                    <th class="text-right" width="80" style="padding-right: 8px;">{{ $month }}</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @foreach($teamList as $team)
                <tr>
                    <td>
                        @if ($hasPermissEditTeam)
                        <a href="{{ route('resource::plan.team.edit', ['id' => $team->id]) }}" target="_blank">{{ $team->name }}</a>
                        @else
                        {{ $team->name }}
                        @endif
                    </td>
                    @for($month = 1; $month <= 12; $month++)
                    <?php
                    $value = isset($plansArray[$team->id][$currYear][$month]) ? $plansArray[$team->id][$currYear][$month] : old('plans.' . $team['value'] . '.' . $month);
                    ?>
                    <td class="input-edit" data-month="{{ $month }}">
                        <div class="value">{{ $value }}</div>
                        <input type="number" min="0" step="1" name="plans[{{ $team->id }}][{{ $month }}]" value="{{ $value }}" data-old="{{ $value }}" class="form-control hidden">
                    </td>
                    @endfor
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-light-blue">
                    <th>{{ trans('resource::view.Sum') }} ({{ trans('resource::view.Human resource') }})</th>
                    @for ($month = 1; $month <= 12; $month++)
                    <td class="text-right">
                        <strong class="sum-month-{{ $month }}"></strong>
                    </td>
                    @endfor
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="box-body text-center">
        <input type="hidden" name="year" value="{{ $currYear }}" />
        <button id="submit_plan" type="submit" class="btn-delete delete-confirm hidden submit_plan" 
                data-noti="{{ trans('resource::message.Are you sure want to save change') }}"
                data-click="0"><i class="fa fa-save"></i> {{ trans('resource::view.Save') }}</button>
        <button type="submit" class="btn-add submit_plan" data-click="0"><i class="fa fa-save"></i> {{ trans('resource::view.Save') }}</button>
    </div>
    <input type="hidden" name="is_edit" value="{{ $checkEdit ? 1 : 0 }}">
    <input type="hidden" name="is_change" id="is_change_edit" value="0">
    {!! Form::close() !!}
</div>

@endsection

@section('confirm_class', 'modal-warning')

@section('script')

@include('resource::recruit.script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="{{ URL::asset('resource/js/recruit/index.js') }}"></script>
<script>
    (function ($) {
        window.onbeforeunload = function (e) {
            var is_change_edit = parseInt($('#is_change_edit').val());
            var is_submit_plan = parseInt($('#submit_plan').attr('data-click'));
            if (is_change_edit == 1 && is_submit_plan == 0) {
                return true;
            }
        };

        $('.date-picker').datepicker({
            format: 'yyyy',
            viewMode: "years", 
            minViewMode: "years",
            autoclose: true
        }).on('changeDate', function (e) {
            $(e.target).closest('form').submit();
        });
    })(jQuery);
</script>

@endsection