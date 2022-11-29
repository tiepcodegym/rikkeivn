<?php
    use Rikkei\Welfare\Model\RelationName;
    if($relative) {
        $data = explode(',',$relative);
    }
?>
<option value="">{{ trans('welfare::view.Please choose') }}</option>
@if(isset($data))
    @foreach($data as $value)
        <option value="{{ $value }}">{{RelationName::getNameById($value)}}</option>
    @endforeach  
@endif
