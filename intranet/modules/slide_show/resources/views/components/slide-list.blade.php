<?php
    use Rikkei\SlideShow\View\View as ViewSlideShow;
    use Rikkei\SlideShow\Model\Repeat;
    use Rikkei\SlideShow\Model\Slide;
?>
    <div class="col-md-4">
        <div class="box box-info">
            <div class="box-body">
                <div class="responsive form-group">
                    <table class="table-list-slide table table-bordered table-condensed dataTable">
                        <thead>
                            <tr>
                                <th>{{trans('slide_show::view.Slide lists')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allHour as $key => $hour)
                                <tr>
                                    <td class="list-slide-by-hour">
                                        <div class="panel panel-info">
                                            <div class="panel-heading">
                                                {{$key}}
                                                <span data-skin="skin-blue" class="btn btn-xs pull-right btn-add-slide" data-hour-form="{{$key}}" data-hour-to="{{$allHour[$key]}}"><i class="fa fa-plus"></i></span>
                                            </div>
                                            <div class="panel-body">
                                                <ul>
                                                    @foreach($slides as $slide)
                                                        <?php
                                                            $allTypeRepeat = Repeat::where('slide_id', $slide['id'])->lists('type')->toArray();
                                                            $checkDisplay = ViewSlideShow::checkHourSlide($key, $slide, $allTypeRepeat);
                                                        ?>
                                                        @if($checkDisplay['result'])
                                                        @if ($checkDisplay['isMainSlider'])
                                                        <li><a class="cursor-pointer btn-detail-slide btn-detail-slide-{{$slide['id']}}" data-id="{{$slide['id']}}" id="btn-detail-slide-{{$slide['id']}}">{{ViewSlideShow::generateHourLoop($slide, $key, $allTypeRepeat)}} : {{$slide['title']}}</a></li>
                                                        @else
                                                        <li><a class="cursor-pointer btn-detail-slide btn-detail-slide-{{$slide['id']}}" data-id="{{$slide['id']}}">{{ViewSlideShow::generateHourLoop($slide, $key, $allTypeRepeat)}} : {{$slide['title']}}</a></li>
                                                        @endif
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>