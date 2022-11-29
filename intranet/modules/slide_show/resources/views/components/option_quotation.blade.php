<div class="form-group title-slide">
    <label for="title" class="col-sm-2 control-label">{{trans('slide_show::view.Title')}}</label>
    <div class="col-sm-10">
        <input type="text" class="form-control title border-radius-4" name="title" placeholder="{{trans('slide_show::view.Title')}}" data-message="{{trans('slide_show::message.Title field is required')}}">
    </div>
</div>
<div class="form-group hour-slide">
                        <label for="hour_start" class="col-sm-2 control-label">{{trans('slide_show::view.Time')}}</label>
                        <div class="col-sm-2 select-hour-start">
                            <?php
                                $tStart = strtotime($time_start);
                                $tEnd = strtotime($time_end);
                                $tNow = $tStart;
                            ?>
                            <select name="hour_start" class="select-hour display-none form-control hour_start" data-message="{{trans('slide_show::message.Hour start field is required')}}">
                            @while($tNow <= $tEnd)
                                <option value="{{date("H:i",$tNow)}}">{{date("H:i",$tNow)}}</option>
                                <?php $tNow = strtotime('+10 minutes',$tNow); ?>
                            @endwhile
                            </select>
                        </div>
                        
                        <label class="col-sm-2 control-label label-custom">{{trans('slide_show::view.to')}}</label>
                        <div class="col-sm-2 select-hour-end">
                            <?php
                                $tStart = strtotime($time_start);
                                $tEnd = strtotime($time_end);
                                $tNow = $tStart;
                            ?>
                            <select name="hour_end" class="select-hour display-none form-control hour_end" data-message="{{trans('slide_show::message.Hour end field is required')}}">
                            @while($tNow <= $tEnd)
                                <option value="{{date("H:i",$tNow)}}">{{date("H:i",$tNow)}}</option>
                                <?php $tNow = strtotime('+10 minutes',$tNow); ?>
                            @endwhile
                            </select>
                        </div>
                        <div class="col-sm-4 type-repeat">
                            @foreach($allTypeRepeat as $key => $type)
                            @if($key == Repeat::TYPE_REPEAT_HOURLY)
                            <label class="checkbox-inline col-sm-4"><input type="checkbox" value="{{$key}}" name="repeat[]" class="repeat">{{$type}}</label>
                            @endif
                            @endforeach
                        </div>
                    </div>