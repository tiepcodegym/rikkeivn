<?php
    use Rikkei\SlideShow\Model\Slide;
    use Rikkei\SlideShow\Model\Repeat;
    use Rikkei\SlideShow\View\View;
    use Rikkei\Core\Model\CoreConfigData;

    $allHour = Slide::getHour();
    $time_start = key($allHour);
    $array_values = array_values($allHour);
    $time_end = end($array_values);
    $allFontSize = Slide::getAllFontSize();
    $allOptionSlide = Slide::getAllOptionSlide();
    $allLanguageSlide = Slide::getAllLanguageSlide();
    $birthSlideContent = CoreConfigData::getValueDb(Slide::BIRTHDAY_CONFIG_DATA);
?>
<div class="detail-slide-element detail-slide-element-{{$slide->id}}">
    <div class="col-md-8">
        <form class="form-horizontal is-update" id="fr-update-slide" data-id="{{$slide->id}}">
            <div class="box box-info" style="min-height: inherit;">
                <div class="box-header with-border">
                    <h3 class="box-title title pull-left">{{trans('slide_show::view.Slide between')}} {{$slide->hour_start}} - {{$slide->hour_end}}</h3>
                    @if(!$optionBirthday)
                    <span class="pull-right">
                        @foreach($allOptionSlide as $key => $type)
                        @if($key == Slide::OPTION_BIRTHDAY)
                            <?php continue; ?>
                        @endif
                        <label class="radio-inline"><input type="radio" name="option" value="{{$key}}" {{$key == $slide->option ? 'checked' : ''}} class="option">{{$type}}</label>
                        @endforeach
                    </span>
                    @else 
                    <input type="radio" value="{{Slide::OPTION_BIRTHDAY}}" class="option hidden" checked>
                    @endif
                </div>
                <div class="box-body">
                    @if($slide->option != Slide::OPTION_WELCOME)
                    <div class="form-group input-language display-none" data-slide-option="{{ Slide::OPTION_WELCOME }}">
                        <label for="language" class="col-sm-2 control-label">{{trans('slide_show::view.Language')}}</label>
                        <div class="col-sm-10 form-group-select2 select-language">
                            <select name="language" class="display-none form-control language select-2">
                                @foreach ($allLanguageSlide as $key => $option)
                                    <option value="{{ $key }}">{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @else
                    <div class="form-group input-language" data-slide-option="{{ Slide::OPTION_WELCOME }}">
                        <label for="language" class="col-sm-2 control-label">{{trans('slide_show::view.Language')}}</label>
                        <div class="col-sm-10 form-group-select2 select-language">
                            <select name="language" class="display-none form-control language select-2">
                                @foreach ($allLanguageSlide as $key => $option)
                                    <option value="{{ $key }}" {{ $key == $slide->language ? 'selected' : ''}}>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @endif
                    
                    <div class="form-group title-slide">
                        @if($slide->option != Slide::OPTION_WELCOME)
                            <label for="title" class="col-sm-2 control-label">{{trans('slide_show::view.Title')}}</label>
                        @else
                            <label for="title" class="col-sm-2 control-label">{{trans('slide_show::view.Company name')}}</label>
                        @endif
                        <div class="col-sm-10">
                            @if($slide->option != Slide::OPTION_WELCOME)
                                <input type="text" class="form-control title border-radius-4 is-titel" placeholder="{{trans('slide_show::view.Title')}}" value="{{$slide->title}}" data-message="{{trans('slide_show::message.Title field is required')}}" data-value="{{$slide->title}}">
                            @else
                                <input type="text" class="form-control title border-radius-4 is-company" placeholder="{{trans('slide_show::view.Company name')}}" value="{{$slide->title}}" data-message="{{trans('slide_show::message.Company name field is required')}}" data-value="{{$slide->title}}">
                            @endif
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
                            <select name="hour_start" class="select-hour display-none form-control select-2 hour_start" data-message="{{trans('slide_show::message.Hour start field is required')}}">
                            @while($tNow <= $tEnd)
                                <option value="{{date("H:i",$tNow)}}" {{date("H:i",$tNow) == $slide->hour_start ? 'selected' : ''}}>{{date("H:i",$tNow)}}</option>
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
                            <select name="hour_end" class="select-hour display-none form-control hour_end select-2" data-message="{{trans('slide_show::message.Hour end field is required')}}">
                            @while($tNow <= $tEnd)
                                <option value="{{date("H:i",$tNow)}}" {{date("H:i",$tNow) == $slide->hour_end ? 'selected' : ''}}>{{date("H:i",$tNow)}}</option>
                                <?php $tNow = strtotime('+10 minutes',$tNow); ?>
                            @endwhile
                            </select>
                        </div>
                        <div class="col-sm-4 type-repeat">
                            <?php
                                $arrayRepeat = $slide->repeatSlide()->lists('type')->toArray();
                            ?>
                            @if($isAllowRepeatHourly)
                                <?php /*@foreach($allTypeRepeat as $key => $type)
                                @if ($key != Repeat::TYPE_REPEAT_HOURLY)
                                <label class="checkbox-inline col-sm-4"><input type="checkbox" value="{{$key}}" name="repeat[]" class="repeat" {{in_array($key, $arrayRepeat) ? 'checked' : ''}}>{{$type}}</label>
                                @endif
                                @endforeach */ ?>
                            @else
                                @foreach($allTypeRepeat as $key => $type)
                                    @if ($key == Repeat::TYPE_REPEAT_HOURLY)
                                        <label class="checkbox-inline col-sm-4"><input type="checkbox" value="{{$key}}" name="repeat[]" class="repeat" {{in_array($key, $arrayRepeat) ? 'checked' : ''}}>{{$type}}</label>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>
                    <?php /*
                    <div class="form-group input-slide-show-type{{ $slide->type == Slide::TYPE_IMAGE ? '' : ' display-none' }}" data-type="image">
                        <label for="slide_effect" class="col-sm-2 control-label">{{trans('slide_show::view.Effect')}}</label>
                        <div class="col-sm-6 form-group-select2">
                            <select name="effect" class="display-none form-control select-2">
                            @foreach ($effectSlide as $option)
                                <option value="{{ $option }}"{{ $option == $slide->effect ? ' selected' : '' }}>{{ $option }}</option>
                            @endforeach
                            </select>
                        </div>
                    </div>
                    */ ?>
                    @if($slide->option == Slide::OPTION_NOMAL)
                        <div class="option-nomal" data-slide-option="{{ Slide::OPTION_NOMAL }}">
                            <div class="form-group input-font-size{{ $slide->type == Slide::TYPE_IMAGE ? '' : ' display-none' }}" data-type="image">
                                <label for="font_size" class="col-sm-2 control-label">{{trans('slide_show::view.Font size')}}</label>
                                <div class="col-sm-2 form-group-select2">
                                    <select name="font_size" class="form-control select-2 font_size">
                                    @foreach ($allFontSize as $size)
                                        <option value="{{ $size }}"{{ $size == $slide->font_size ? ' selected' : '' }}>{{ $size }}</option>
                                    @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group checkbox-type-slide">
                                <label for="hour_start" class="col-sm-1 control-label">{{trans('slide_show::view.File')}}</label>
                                <div class="col-sm-11">
                                    @foreach($allTypeSlide as $key => $type)
                                    <label class="radio-inline col-sm-3"><input type="radio" name="type" value="{{$key}}" {{$slide->type == $key ? 'checked' : ''}} class="type">{{$type}}</label>
                                    @endforeach
                                </div>
                            </div>
                            <?php
                                if ($slide->type == Slide::TYPE_IMAGE ){
                                    $isTypeImage = true;
                                } else {
                                    $isTypeImage = false;
                                }
                            ?>
                            <div class="form-group input-image {{$isTypeImage ? '' : 'display-none'}}">
                                <div>
                                    <?php
                                        if ($isTypeImage) {
                                            $imageDescription = '';
                                            if ($allFile && count($allFile)) {
                                                $imageDescription = '[';
                                                foreach ($allFile as $file) {
                                                    $imageDescription .= '"';
                                                    $imageDescription .= addslashes($file->description);
                                                    $imageDescription .= '",';
                                                }
                                                $imageDescription = substr($imageDescription, 0, strlen($imageDescription) - 1);
                                                $imageDescription .= ']';
                                            }
                                        }
                                    ?>
                                    <script>
                                    @if($isTypeImage)
                                        @if (isset($imageDescription) && $imageDescription)
                                            var arrayDescription = {!! $imageDescription !!};
                                        @else
                                            var arrayDescription = null;
                                        @endif
                                    @else
                                        var arrayDescription = null;
                                    @endif
                                    </script>
                                    @if($isTypeImage)
                                    <input oldDescription='{{($allFile) ? $allFile->implode('description',','):''}}' 
                                   oldValue='{{($allFile) ? $allFile->implode('file_name',','):''}}' 
                                   type="file" name="files[]" id="filer_input_edit" multiple="multiple" data-message="{{trans('slide_show::message.Image field is required')}}"
                                   imageShow='{{ ($allFile) ? $allFile->implode('full_file_name',','):''}}'>
                                   @else
                                   <input type="file" name="files[]" id="filer_input_edit" multiple="multiple" data-message="{{trans('slide_show::message.Image field is required')}}">
                                   @endif
                                </div>
                            </div>
                            <div class="form-group input-video {{$isTypeImage ? 'display-none' : ''}}">
                                <label for="title" class="col-sm-1 control-label">{{trans('slide_show::view.Url')}}</label>
                                <div class="col-sm-11">
                                @if($isTypeImage)
                                    <input type="text" class="form-control url_video border-radius-4" name="url_video" placeholder="{{trans('slide_show::view.Url video youtube')}}" data-message="{{trans('slide_show::message.Url video youtube incorrect')}}">
                                @else
                                    <input type="text" class="form-control url_video border-radius-4" name="url_video" value="https://www.youtube.com/watch?v={{$allFile->file_name}}" placeholder="{{trans('slide_show::view.Url video youtube')}}" data-message="{{trans('slide_show::message.Url video youtube incorrect')}}">
                                @endif
                                </div>
                            </div>
                            <div class="col-sm-11 col-sm-offset-1 padding-right-0 padding-left-0 preview-video input-slide-show-type{{$isTypeImage ? ' display-none' : ''}}" style="margin-top:20px" data-type="video">
                            @if($isTypeImage)
                                <iframe width="100%" height="100%" src="" frameborder="0" class="youtube-video"  webkitallowfullscreen mozallowfullscreen allowfullscreen style="min-height: 500px"></iframe>
                            @else
                                <iframe width="100%" height="100%" src="{{View::urlVideoYoutube($allFile->file_name)}}" frameborder="0" class="youtube-video"  webkitallowfullscreen mozallowfullscreen allowfullscreen style="min-height: 500px"></iframe>
                            @endif    
                            </div>
                        </div>
                    @elseif($slide->option == Slide::OPTION_WELCOME)
                        <div class="option-welcome" data-slide-option="{{ Slide::OPTION_WELCOME }}">
                            <div class="form-group">
                                <label for="name_customer" class="col-sm-2 control-label">{{trans('slide_show::view.Customer name')}}</label>

                                <div class="col-sm-10">
                                    <input type="text" class="form-control name_customer border-radius-4" name="name_customer" placeholder="{{trans('slide_show::view.Customer name')}}" value="{{$slide->name_customer}}" data-message="{{trans('slide_show::message.Customer name field is required')}}">
                                </div>
                            </div>
                            <div class="form-group input-logo-company">
                                <div>
                                    <input oldDescription='{{($allFile) ? $allFile->implode('description',','):''}}' 
                                   oldValue='{{($allFile) ? $allFile->implode('file_name',','):''}}' 
                                   type="file" name="files[]" id="logo_company_edit" multiple="multiple"
                                   imageShow='{{ ($allFile) ? $allFile->implode('full_file_name',','):''}}'>
                                </div>
                            </div>
                        </div>
                    @elseif($slide->option == Slide::OPTION_QUOTATIONS)
                        <div class="option-quotations add-items-container" data-slide-option="{{ Slide::OPTION_QUOTATIONS }}">
                            <div class="form-group form-group-nmargin">
                                <label class="col-md-12">{{trans('slide_show::view.Quotations')}}</label>
                                <div class="add-items-wapper">
                                    @if(count($slideQuotations))
                                        <?php
                                        $countSlideQuotations = count($slideQuotations);
                                        ?>
                                        @foreach ($slideQuotations as $slideQuotationsItem)
                                            @include('slide_show::components.quotation_item_data', ['countSlideQuotations' => $countSlideQuotations])
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                            <div class="form-group">
                                <button type="button" class="btn-add add-items-btn-add btn-add-quotations">{{ trans('slide_show::view.Add quotation') }}</button>
                            </div>
                            <div class="hidden add-items-template">
                                @include('slide_show::components.quotation_item')
                            </div>
                        </div>
                    @endif
                    
                    <!-- hidden of option slide -->
                    @if($slide->option != Slide::OPTION_NOMAL)
                        <!-- normal create -->
                        <div class="option-nomal display-none" data-slide-option="{{ Slide::OPTION_NOMAL }}">
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
                                    <input type="file" name="files[]" id="filer_input_edit" multiple="multiple" data-message="{{trans('slide_show::message.Image field is required')}}">
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
                        <!-- end normal create -->
                    @endif
                    
                    @if($slide->option != Slide::OPTION_WELCOME)
                        <!-- company create -->
                        <div class="display-none option-welcome" data-slide-option="{{ Slide::OPTION_WELCOME }}">
                            <div class="form-group">
                                <label for="name_customer" class="col-sm-2 control-label">{{trans('slide_show::view.Customer name')}}</label>

                                <div class="col-sm-10">
                                    <input type="text" class="form-control name_customer border-radius-4" name="name_customer" placeholder="{{trans('slide_show::view.Customer name')}}" data-message="{{trans('slide_show::message.Customer name field is required')}}">
                                </div>
                            </div>
                            <div class="fom-group input-logo-company">
                                <div>
                                    <input type="file" name="files[]" id="logo_company_edit" multiple="multiple">
                                </div>
                            </div>
                        </div>
                        <!-- end company create -->
                    @endif
                    
                    @if($slide->option != Slide::OPTION_QUOTATIONS)
                        <!-- quotation create -->
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
                        <!-- end quotation create -->
                    @endif
                </div>
                <div class="box-footer text-center">
                    <div class="form-group">
                        <div class="progress active display-none">
                            <div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="" aria-valuemin="0" aria-valuemax="100">
                              <span></span>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary btn-update-slide margin-right-20" data-id="{{$slide->id}}">{{trans('slide_show::view.Update slide')}}<i class="fa fa-refresh fa-lg fa-spin display-none"></i></button>
                    @if($slide->option == Slide::OPTION_BIRTHDAY)
                        <a target="_blank" href="{{route('slide_show::preview', ['id' => $slide->id, 'fakeData' => true])}}" class="btn btn-primary margin-right-20">{{trans('slide_show::view.Preview')}}</a>
                    @else 
                        <a target="_blank" href="{{route('slide_show::preview', ['id' => $slide->id])}}" class="btn btn-primary margin-right-20">{{trans('slide_show::view.Preview')}}</a>
                    @endif
                    
                    <button type="button" class="btn-delete btn-delete-slide" data-id="{{$slide->id}}">{{trans('slide_show::view.Delete slide')}}</button>
                </div>
            </div>
        </form>
    </div>
</div>