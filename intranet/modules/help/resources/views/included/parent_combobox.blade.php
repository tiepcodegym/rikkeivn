<option class="level-{{$level}}" value="{{ $option['id'] }}" {{ $helpItem->parent == $option['id'] ? ' selected' : '' }}>{{ $str.$option['title'] }}</option>
<?php
    $str .= '- ';
    $level++;
?>
    @if ($option['children'])
        @foreach($option['children'] as $option)
            @include('help::included.parent_combobox', $option)
        @endforeach
    @endif
