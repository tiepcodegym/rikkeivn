@if(isset($approversList) && count($approversList))
    @foreach($approversList as $item)
        <option value="{{ $item->id }}">{{ $item->name . ' (' . preg_replace('/@.*/', '',$item->email) . ')' }}</option>
    @endforeach
@endif