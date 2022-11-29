<?php 
use Rikkei\Music\View\ViewMusic;
header("Cache-Control: no-store, must-revalidate, max-age=0");
header("Pragma: no-cache");
use Rikkei\Core\View\CoreUrl;
?>
<div class="row music-list">
    <div class="col-xs-1 prev-next">
        @if(count($collectionModel)&&$collectionModel->currentPage()>1)
            <a href="{{$collectionModel->previousPageUrl()}}" id="left" class="inner-prev-next">
                <img src="{{CoreUrl::asset('/asset_music/images/left.png')}}" hov="{{CoreUrl::asset('/asset_music/images/left-h.png')}}">
            </a>
        @endif
    </div>
    <div class="col-xs-10">
        <div class="list">
            @foreach($collectionModel as $order)
            <?php
            if (!empty($order->link)) {
                parse_str(parse_url($order->link, PHP_URL_QUERY), $link_youtube);
                $message = htmlspecialchars($order->message);
            }
            ?>
            <div class="box box-music">
                <div class="row">
                    <div class="col-xs-12 col-sm-3 col-md-4 col-lg-3">
                        <div class="thumbnail">
                            <a target="_blank" href="{{$order->link}}">
                                 @if(isset($link_youtube['v']) && $link_youtube['v'] )
                                    <img src="{{'https://img.youtube.com/vi/'.$link_youtube['v'].'/mqdefault.jpg '}}">

                                @else
                                    <img alt="{{$order->name}}" src="{{ asset('/asset_music/images/play-order.jpg') }}" />
                                @endif
                            </a>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-9 col-md-8 col-lg-9">
                        <div class="box-script">
                            <div class="box-title" data-toggle="tooltip" title="{{$order->name}}">
                                <a target="_blank" href="{{$order->link}}" >
                                    <b class="glyphicon glyphicon-cd"></b>
                                    <span>
                                        {{$order->name}}
                                    </span>
                                </a>
                            </div>
                            <div class="box-infor">
                            <p>
                                <span class="from-to">{{ trans('music::view.From') }}</span>
                                <span class="short-name" data-toggle="tooltip" @if ($order->sender) title="{{$order->sender}}" @else title="{{trans('music::view.Nameless')}}..." @endif>  
                                    <b >
                                        @if ($order->sender)
                                            {{$order->sender}}
                                        @else
                                            {{ trans('music::view.Nameless') }}...
                                        @endif
                                    </b>
                                </span>
                                <span class="from-to">{{ trans('music::view.To') }}</span>
                                <span class="short-name" data-toggle="tooltip" @if ($order->receiver) title="{{$order->receiver}}" @else title="{{ trans('music::view.Someone') }}..." @endif> 
                                    <b >
                                        @if ($order->receiver)
                                            {{$order->receiver}}
                                        @else
                                            {{ trans('music::view.Someone') }}...
                                        @endif
                                    </b>
                                </span>
                            </p>
                                <div class="box-mess">
                                    <span> 
                                        <i class="fa fa-envelope"></i>
                                        {{ trans('music::view.With Message') }}...
                                    </span>
                                    <br>
                                    <span class="short-mess">
                                        
                                        <span class="mess">{!!ViewMusic::shortMess($order->message, 60, true)!!}</span>
                                        <!-- <p class="mess">{!!$message!!}</p> -->
                                        <br>
                                       <!--  <button class="btn-link show-mess">{{trans('music::view.Oder view more')}}</button> -->
                                        <input type="hidden" id="mess{{$order->id}}" value="{{$message}}">
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class=" align-right">
                    <div class="box-time">
                        <span >
                            &nbsp;&nbsp;&nbsp;&nbsp;{{$order->created_at->format('d/m/Y')}}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{$order->created_at->format('H:i')}}&nbsp;&nbsp;&nbsp;&nbsp;
                        </span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @if(is_object($collectionModel))
            <div class="pagination-order box-pagination align-center">
                @if($collectionModel->lastPage() > 1)
                    <ul class="pagination">
                        <?php
                            $currentPage = $collectionModel->currentPage();
                            $lastPage = $collectionModel->lastPage();
                            $firstPage = 1;
                            $prevPage = $currentPage - 1;
                            $nextPage = $currentPage + 1;
                            $totalPage = 5;

                            $render = ViewMusic::getPage($currentPage, $firstPage, $lastPage, $totalPage);
                        ?>
                        @if($render['isFirst']) 
                            <li class = "disabled">
                                <span>{{trans('music::view.First')}}</span>    
                            </li>
                            <li class = "disabled">
                                <span>«</span>    
                            </li>
                        @else
                            <li >
                                <a href="{{ $collectionModel->url(1) }}">{{trans('music::view.First')}}</a>    
                            </li>
                            <li >
                                <a href="{{ $collectionModel->previousPageUrl() }}">«</a>   
                            </li>
                        @endif

                        @for ($i = $render['start'] ; $i <= $render['end'] ; $i++)
                        <li class="{{ ($collectionModel->currentPage() == $i) ? 'active' : '' }}">
                            <a href="{{ $collectionModel->url($i) }}">{{$i}}</a>    
                        </li>
                        @endfor

                        @if($render['isLast']) 
                            <li class = "disabled">
                                <span>»</span>    
                            </li>
                            <li class = "disabled">
                                <span>{{trans('music::view.Last')}}</span>    
                            </li>
                        @else
                            <li >
                                <a href="{{ $collectionModel->nextPageUrl() }}">»</a>    
                            </li>
                            <li >
                                <a href="{{ $collectionModel->url($collectionModel->lastPage()) }}">{{trans('music::view.Last')}}</a>   
                            </li>
                        @endif   
                    </ul>  
                @endif
            </div>
        @endif
    </div>
    <div class="col-xs-1 prev-next">
        @if(count($collectionModel)&&$collectionModel->currentPage()<$collectionModel->lastPage())
            <a href="{{$collectionModel->nextPageUrl()}}" id="right" class="inner-prev-next">
                <img src="{{CoreUrl::asset('/asset_music/images/right.png')}}" hov="{{CoreUrl::asset('/asset_music/images/right-h.png')}}">
            </a>
        @endif
    </div>
</div>

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.min.js"></script>
<script src="{{ CoreUrl::asset('asset_music/js/music_frontend.js') }}"></script>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        var urlInvalid = '{{ trans('music::message.Please enter a valid URL.') }}';
        selectSearchReload();
    });
</script>
@endsection
