<option value="0">{{ trans('resource::view.Select meeting room') }}</option>
@foreach ($calendarList as $groupName => $groupArray)
<optgroup label="{{ $groupName }}">
    @foreach ($groupArray as $room)
    <option value="{{ $room['id'] }}" {{ in_array($room['id'], $roomUnavailable) ? 'disabled' : '' }}>{{ $room['summary'] }}</option>
    @endforeach
</optgroup>
@endforeach

