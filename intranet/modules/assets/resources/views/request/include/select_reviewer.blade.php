@if(isset($reviewersList) && count($reviewersList))
    @foreach($reviewersList as $item)
        <option value="{{ $item->id }}">{{ $item->name . ' (' . preg_replace('/@.*/', '',$item->email) . ')' }}</option>
    @endforeach
@endif