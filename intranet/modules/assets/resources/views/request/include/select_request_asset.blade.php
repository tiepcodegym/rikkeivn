@if(isset($requestAsset) && count($requestAsset))
    @foreach($requestAsset as $item)
        <option value="{{ $item->id }}">{{ $item->request_name }}</option>
    @endforeach
@endif