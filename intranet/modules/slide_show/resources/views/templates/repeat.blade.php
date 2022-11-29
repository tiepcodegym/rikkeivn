<?php
use Rikkei\SlideShow\Model\Repeat;
?>
@if($slide)
    <?php
        $arrayRepeat = $slide->repeatSlide()->lists('type')->toArray();
    ?>
    @if($status)
        <!-- @foreach($allTypeRepeat as $key => $type)
        @if ($key != Repeat::TYPE_REPEAT_HOURLY)
        <label class="checkbox-inline col-sm-4"><input type="checkbox" value="{{$key}}" name="repeat[]" class="repeat" {{in_array($key, $arrayRepeat) ? 'checked' : ''}}>{{$type}}</label>
        @endif
        @endforeach -->
    @else
        @foreach($allTypeRepeat as $key => $type)
        @if ($key == Repeat::TYPE_REPEAT_HOURLY)
        <label class="checkbox-inline col-sm-4"><input type="checkbox" value="{{$key}}" name="repeat[]" class="repeat" {{in_array($key, $arrayRepeat) ? 'checked' : ''}}>{{$type}}</label>
        @endif
        @endforeach
    @endif
@else
    @if($status)
        <!-- @foreach($allTypeRepeat as $key => $type)
        @if ($key != Repeat::TYPE_REPEAT_HOURLY)
        <label class="checkbox-inline col-sm-4"><input type="checkbox" value="{{$key}}" name="repeat[]" class="repeat">{{$type}}</label>
        @endif
        @endforeach -->
    @else
        @foreach($allTypeRepeat as $key => $type)
        @if ($key == Repeat::TYPE_REPEAT_HOURLY)
        <label class="checkbox-inline col-sm-4"><input type="checkbox" value="{{$key}}" name="repeat[]" class="repeat">{{$type}}</label>
        @endif
        @endforeach
    @endif
@endif
