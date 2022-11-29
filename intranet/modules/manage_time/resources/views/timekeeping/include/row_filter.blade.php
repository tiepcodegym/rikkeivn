<?php

use Rikkei\ManageTime\View\ManageTimeCommon;
use Rikkei\ManageTime\Model\TimekeepingAggregate;
use Rikkei\Core\View\Form;

$aggregateTbl = TimekeepingAggregate::getTableName();
$specialKeyFilter = [
    'total_ot_weekdays', 'total_ot_weekends', 'total_ot_holidays', 'total_business_trip',
    'total_leave_day', 'total_supplement', 'total_holiday', 'total_official_ot',
    'total_trial_ot', 'total_official_salary', 'total_trial_salary', 'total_late_shift', 'total_early_shift', 'total_leave_basic_salary', 'total_leave_basic_salary_s'
];
$keyExcept = in_array($key, $specialKeyFilter) ? 'except' : 'except2';

?>

<input type="text" class="form-control text-label"
    placeholder="{{ trans('manage_time::view.Find') }}"
    value="{{ ManageTimeCommon::valueFilter(Form::getFilterData('compare', "{$aggregateTbl}.{$key}_compare"), Form::getFilterData($keyExcept, "{$aggregateTbl}.{$key}"))}}" />

<select class="hidden compare form-control filter-grid"
    name="filter[compare][{{ $aggregateTbl }}.{{ $key }}_compare]">
    @foreach ($optionsCompare as $value)
    <option value="{{ $value }}" {{ Form::getFilterData('compare', "{$aggregateTbl}.{$key}_compare") == $value ? 'selected' : '' }}>{{ $value }}</option>
    @endforeach
</select>

<input type="text"
    name="filter[{{ $keyExcept }}][{{ $aggregateTbl }}.{{ $key }}]"
    value='{{ Form::getFilterData($keyExcept, "{$aggregateTbl}.{$key}") }}'
    placeholder="{{ trans('team::view.Search') }}..."
    class="filter-grid form-control hidden num" autocomplete="off" />