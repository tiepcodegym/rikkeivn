<?php
    use Rikkei\SlideShow\Model\Slide;
    use Rikkei\SlideShow\Model\Repeat;

    $allHour = Slide::getHour();
    $time_start = key($allHour);
    $array_values = array_values($allHour);
    $time_end = end($array_values);
    $allFontSize = Slide::getAllFontSize();
?>
<div class="create-slide display-none">
    <div class="col-md-8 content-create-slide">
        <form class="form-horizontal" id="fr-create-slide">
            {{ csrf_field() }}
            <div class="box box-info" style="min-height: inherit;">
                <div class="box-header with-border">
                    <h3 class="box-title title pull-left">{{trans('slide_show::view.Create slide between')}} <span class="text-form"></span> - <span class="text-to"></span></h3>
                    <span class="pull-right">
                        @foreach($allOptionSlide as $key => $type)
                            <?php
                                if ($key == Slide::OPTION_BIRTHDAY) {
                                    continue;
                                }
                            ?>
                        <label class="radio-inline"><input type="radio" name="option" value="{{$key}}" {{$key == Slide::OPTION_NOMAL ? 'checked' : ''}} class="option">{{$type}}</label>
                        @endforeach
                    </span>
                </div>
                <div class="box-body">
                    <div class="form-group input-language display-none" data-slide-option="{{ Slide::OPTION_WELCOME }}">
                        <label for="language" class="col-sm-2 control-label">{{trans('slide_show::view.Language')}}</label>
                        <div class="col-sm-10 form-group-select2 select-language">
                            <select name="language" class="display-none form-control language">
                                @foreach ($allLanguageSlide as $key => $option)
                                    <option value="{{ $key }}">{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

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
                    {{--
                    <div class="form-group input-slide-show-type" data-type="image">
                        <label for="slide_effect" class="col-sm-2 control-label">{{trans('slide_show::view.Effect')}}</label>
                        <div class="col-sm-6 form-group-select2">
                            <select name="effect" class="display-none form-control">
                            @foreach ($effectSlide as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                            </select>
                        </div>
                    </div>
                    --}}
                    <div class="option-nomal" data-slide-option="{{ Slide::OPTION_NOMAL }}">
                        <div class="form-group input-font-size" data-type="image">
                            <label for="slide_effect" class="col-sm-2 control-label">{{trans('slide_show::view.Font size')}}</label>
                            <div class="col-sm-2 form-group-select2">
                                <select name="font_size" class="font_size form-control">
                                @foreach ($allFontSize as $size)
                                    <option value="{{ $size }}">{{ $size }}</option>
                                @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group checkbox-type-slide">
                            <label for="hour_start" class="col-sm-2 control-label">{{trans('slide_show::view.File')}}</label>
                            <div class="col-sm-10">
                                @foreach($allTypeSlide as $key => $type)
                                <label class="radio-inline col-sm-3"><input type="radio" name="type" value="{{$key}}" {{$key == Slide::TYPE_IMAGE ? 'checked' : ''}} class="type">{{$type}}</label>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group input-image">
                            <div>
                                <script>
                                    var arrayDescription = null;
                                </script>
                                <input type="file" name="files[]" id="filer_input2" multiple="multiple" data-message="{{trans('slide_show::message.Image field is required')}}">
                            </div>
                        </div>
                        <div class="form-group input-video display-none">
                            <label for="title" class="col-sm-1 control-label">{{trans('slide_show::view.Url')}}</label>
                            <div class="col-sm-11">
                                <input type="text" class="form-control url_video border-radius-4" name="url_video" placeholder="{{trans('slide_show::view.Url video youtube')}}" data-message="{{trans('slide_show::message.Url video youtube incorrect')}}">
                            </div>
                        </div>
                        <div class="col-sm-11 col-sm-offset-1 padding-right-0 padding-left-0 preview-video display-none" style="margin-top:20px ">
                            <iframe width="100%" height="100%" src="" frameborder="0" class="youtube-video"  webkitallowfullscreen mozallowfullscreen allowfullscreen style="min-height: 500px"></iframe>
                        </div>
                    </div>
                    <div class="display-none option-welcome" data-slide-option="{{ Slide::OPTION_WELCOME }}">
                        <div class="form-group">
                            <label for="name_customer" class="col-sm-2 control-label">{{trans('slide_show::view.Customer name')}}</label>

                            <div class="col-sm-10">
                                <input type="text" class="form-control name_customer border-radius-4" name="name_customer" placeholder="{{trans('slide_show::view.Customer name')}}" data-message="{{trans('slide_show::message.Customer name field is required')}}">
                            </div>
                        </div>
                        <div class="form-group input-logo-company">
                            <div>
                                <input type="file" name="files[]" id="logo_company" multiple="multiple">
                            </div>
                        </div>
                    </div>

                    <div class="display-none option-quotations add-items-container" data-slide-option="{{ Slide::OPTION_QUOTATIONS }}">
                        <div class="form-group form-group-nmargin">
                            <label class="col-md-12">{{trans('slide_show::view.Quotations')}}</label>
                            <div class="add-items-wapper"></div>
                        </div>
                        <div class="form-group">
                            <button type="button" class="btn-add add-items-btn-add btn-add-quotations">{{ trans('slide_show::view.Add quotation') }}</button>
                        </div>
                        <div class="hidden add-items-template">
                            @include('slide_show::components.quotation_item')
                        </div>
                    </div>
                </div>
                <div class="box-footer text-center">
                    <div class="progress active display-none">
                        <div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="" aria-valuemin="0" aria-valuemax="100">
                          <span></span>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary btn-create-slide">{{trans('slide_show::view.Create slide')}} <i class="fa fa-refresh fa-lg fa-spin display-none"></i></button>
                </div>
            </div>
        </form>
    </div>
</div>

