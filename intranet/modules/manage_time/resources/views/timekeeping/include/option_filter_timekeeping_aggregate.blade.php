@if (isset($timekeepingTablesList) && count($timekeepingTablesList))
    <option>&nbsp;</option>
    @foreach ($timekeepingTablesList as $item)
        <option value="{{ route('manage_time::timekeeping.timekeeping-aggregate', ['id' => $item->timekeeping_table_id]) }}" {{ $item->timekeeping_table_id == $timekeepingTableId ? 'selected' : '' }}>{{ $item->timekeeping_table_name }}</option>
    @endforeach
@endif