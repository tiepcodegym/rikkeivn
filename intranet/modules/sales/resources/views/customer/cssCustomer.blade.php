@extends('layouts.guest')
<?php
use Rikkei\Sales\Model\Css;
use Illuminate\Support\Facades\Config as SupportConfig;
use Rikkei\Core\View\CoreUrl;
$lang = SupportConfig::get('langs.'.$css->lang_id);
if ($lang == null) {
    $lang = SupportConfig::get('langs.'.Css::JAP_LANG);
}

?>
@section('content')

<div class="make-css-page">
    <div class="row">
        <div class="col-md-12">
            <section id="make-header">
                <div class="logo-rikkei"><img src="{{ URL::asset('common/images/logo-rikkei.png') }}"></div>
                <h2 class="title <?php if($css->project_type_id === 2){ echo 'title-base'; }?> <?php if($lang == "en") echo 'title-eng'; ?>">
                {{trans('sales::view.Welcome title',[],'',$lang)}}</h2>
                <span class="visible-check"></span>
                <div class="total-point-container <?php if($css->project_type_id === 2){ echo 'total-point-container-base'; }?>">
                    <div class="total-point-text">{{ trans('sales::view.Total point',[],'',$lang)}}</div>
                    <div class="total-point <?php if($css->project_type_id === 2){ echo 'total-point-base'; }?>" >{{number_format($cssResult->avg_point,2)}}</div>
                </div>
            </section>
            <section>

                <!-- PROJECT INFORMATION -->
                <div class="row project-info">
                    <div class="col-xs-12 header <?php if($css->project_type_id === 2){ echo 'header-base'; }?>">{{ trans('sales::view.Project information',[],'',$lang) }}</div>
                    <div class="col-xs-12 col-sm-6 padding-left-right-5px <?php if($lang == "en") :?>font-viet <?php endif; ?>">
                        <div class="row-info">{{ trans('sales::view.Project name jp',[],'',$lang) }} @if(trim($css->project_name_css) != null) {{$css->project_name_css}}  @else {{ $css->project_name}} @endif</div>
                        <div class="row-info">{{ trans('sales::view.Customer name jp',[],'',$lang) }} {{ $css->customer_name }}
                        @if($lang == "ja") 様 @endif</div>
                        <div class="row-info">{{ trans('sales::view.Make name jp',[],'',$lang)}}<span class="make-name">{{ $cssResult->name }}</span>@if($lang == "ja") 様 @endif</div>
                    </div>
                    <div class="col-xs-12 col-sm-6 padding-left-right-5px <?php if($lang == "en") :?>font-viet <?php endif; ?>">
                        <div class="row-info">{{ trans('sales::view.Sale name jp',[],'',$lang) }}{{ $css->sale_name_jp }}</div>
                        @if($css->project_type_id === 1)
                 <div class="row-info">{{ trans('sales::view.PM name jp osdc',[],'',$lang) }}{{ $css->pm_name_jp }}</div>
            @else
                 <div class="row-info">{{ trans('sales::view.PM name jp',[],'',$lang) }}{{ $css->pm_name_jp }}</div>
            @endif
                        <div class="row-info">{{ trans('sales::view.Project date jp',[],'',$lang) }}{{ date("Y/m/d",strtotime($css->start_date)) }} - {{ date("Y/m/d",strtotime($css->end_date)) }}</div>
                    </div>
                </div>
                <!-- END PROJECT INFORMATION -->

                <!-- PROJECT DETAIL -->
                <div class="row project-detail">
                    <div class="col-xs-12 header <?php if($css->project_type_id === 2){ echo 'header-base'; }?>">
                        <div class="col-xs-12 col-sm-5">{{ trans('sales::view.Question',[],'',$lang) }}</div>
                        <div class="col-sm-2 rating-header">{{ trans('sales::view.Rating',[],'',$lang) }}</div>
                        <div class="col-sm-5 comment-header">{{ trans('sales::view.Comment',[],'',$lang) }}</div>
                    </div>
                    <?php
                    $itemChild_zindex = 100;
                    $item_zindex = 50;
                    ?>
                    @foreach($cssCate as $item)
                        @if($item['noCate'] == 1)
                        <div class="col-xs-12 root-cate root-cate-first <?php if($css->project_type_id === 2){ echo 'root-cate-base'; }?>">
                            {{$item["sort_order"] . ". " .$item['name']}}
                        </div>
                        @else
                        <div class="col-xs-12 root-cate <?php if($css->project_type_id === 2){ echo 'root-cate-base'; }?>">
                            {{$item["sort_order"] . ". " .$item['name']}}
                        </div>
                        @endif

                        @if($item['cssCateChild'])
                            @foreach($item['cssCateChild'] as $itemChild)
                                <div class="col-xs-12 child-cate">{{$itemChild["sort_order"] . ". " .$itemChild['name']}}</div>
                                @if($itemChild['questionsChild'])
                                    @foreach($itemChild['questionsChild'] as $questionChild)
                                    <?php $itemChild_zindex = $itemChild_zindex -1; ?>
                                    <div class="row container-question">
                                        <div class="col-xs-12 col-sm-5 question"><span class="num-border">{{ $questionChild->sort_order }}</span> {{ $questionChild->content}}</div>
                                        <div class="col-xs-12 col-sm-2 rate"><div class="rateit" data-rateit-value="{{$questionChild->point}}" data-rateit-step='1' data-rateit-resetable="false" data-rateit-readonly="true" data-questionid="{{$questionChild->id}}" onclick="totalMark(this);" ontouchend="totalMark(this);" ></div></div>
                                        {{--<div class="col-xs-12 col-sm-5 comment"><input type="text" readonly="true" class="comment-question form-control" type="text" maxlength="1000" data-questionid="{{$questionChild->id}}" onkeyup="checkPoint(this);" value='{{$questionChild->comment}}' /></div>--}}
                                        <div class="col-xs-12 col-sm-5 comment @if($questionChild->comment) dropdown @endif" style="z-index: {{$itemChild_zindex}}">
                                            <textarea type="text" readonly="true" class="comment-question form-control" type="text" maxlength="1000" data-questionid="{{$questionChild->id}}" onkeyup="checkPoint(this);" value='{{$questionChild->comment}}' >{{$questionChild->comment}}</textarea>
                                            <div class="dropdown-content make-conten-dropdown">
                                                <textarea type="text" rows="5" readonly="true" class="form-control" type="text" maxlength="1000" data-questionid="{{$questionChild->id}}" onkeyup="checkPoint(this);" value='{{$questionChild->comment}}' >{{$questionChild->comment}}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                @endif
                            @endforeach
                        @elseif($item['questions'])
                            @foreach($item['questions'] as $question)
                            <?php $item_zindex = $item_zindex -1; ?>
                            <div class="row container-question">
                                <div class="col-xs-12 col-sm-5 question"><span class="num-border">{{ $question->sort_order }}</span> {{ $question->content}}</div>
                                <div class="col-xs-12 col-sm-2 rate"><div class="rateit" data-rateit-value="{{$question->point}}" data-rateit-step='1' data-rateit-resetable="false" data-rateit-readonly="true" data-questionid="{{$question->id}}" onclick="totalMark(this);" ontouchend="totalMark(this);" ></div></div>
                                {{--<div class="col-xs-12 col-sm-5 comment"><input type="text" readonly="true" class="comment-question form-control" type="text" maxlength="1000" data-questionid="{{$question->id}}" onkeyup="checkPoint(this);" value='{{$question->comment}}' /></div>--}}
                                <div class="col-xs-12 col-sm-5 comment @if($question->comment) dropdown @endif" style="z-index: {{$item_zindex}}">
                                    <textarea type="text" readonly="true" class="comment-question form-control" type="text" maxlength="1000" data-questionid="{{$question->id}}" onkeyup="checkPoint(this);" value='{{$question->comment}}'>{{$question->comment}}</textarea>
                                    <div class="dropdown-content make-conten-dropdown">
                                        <textarea type="text" rows="5" readonly="true" class="form-control" type="text" maxlength="1000" data-questionid="{{$question->id}}" onkeyup="checkPoint(this);" value='{{$question->comment}}'>{{$question->comment}}</textarea>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @endif
                    @endforeach

                    <!-- Overview question -->
                    <div class="col-xs-12 root-cate <?php if($css->project_type_id === 2){ echo 'root-cate-base'; }?>">@if($css->project_type_id ==1)
                    {{$noOverView . ". " .trans('sales::view.Overall',[],'',$lang) }}
                    @else
                    {{$noOverView . ". " .trans('sales::view.Totally',[],'',$lang) }}
                    @endif
                    </div>
                    <div class="row container-question">
                        <div class="col-xs-12 col-sm-5 question">@if($lang == "ja") {{ $overviewQuestionContent }} @else
                            {{trans('sales::view.OverviewQuestionContent OSDC',[],'',$lang)}}
                        @endif</div>
                        <div class="col-xs-12 col-sm-2 rate"><div id="tongquat" class="rateit" data-rateit-value="{{$resultDetailRowOfOverview->point}}" data-rateit-step='1' data-rateit-resetable="false" data-rateit-readonly="true" data-questionid="{{$overviewQuestionId}}" onclick="totalMark(this);" ontouchend="totalMark(this);" ></div></div>
                        {{--<div class="col-xs-12 col-sm-5 comment"><input type="text" readonly="true" class="comment-question form-control" type="text" maxlength="1000" data-questionid="{{$overviewQuestionId}}" id="comment-tongquat" onkeyup="checkPoint(this);" value='{{$resultDetailRowOfOverview->comment}}' /></div>--}}
                        <div class="col-xs-12 col-sm-5 comment @if($resultDetailRowOfOverview->comment) dropdown @endif" style="z-index: 1">
                            <textarea type="text" readonly="true" class="comment-question form-control" type="text" maxlength="1000" data-questionid="{{$overviewQuestionId}}" id="comment-tongquat" onkeyup="checkPoint(this);" value='{{$resultDetailRowOfOverview->comment}}'>{{$resultDetailRowOfOverview->comment}}</textarea>
                            <div class="dropdown-content make-conten-dropdown">
                                <textarea type="text" rows="5" readonly="true" class="form-control" type="text" maxlength="1000" data-questionid="{{$overviewQuestionId}}" id="comment-tongquat" onkeyup="checkPoint(this);" value='{{$resultDetailRowOfOverview->comment}}'>{{$resultDetailRowOfOverview->comment}}</textarea>

                            </div>
                        </div>
                    </div>
                    <!-- Proposed -->
                    <div class="row proposed container-question">
                        <div class="col-xs-12 col-sm-5 question">
                            <div>{{ trans('sales::view.Proposed line 1',[],'',$lang) }}</div>
                            <div>{{ trans('sales::view.Proposed line 2',[],'',$lang) }}</div>
                            <div>{{ trans('sales::view.Proposed line 3',[],'',$lang) }}</div>
                            <div>{{ trans('sales::view.Proposed line 4',[],'',$lang) }}</div>
                            <div>{{ trans('sales::view.Proposed line 5',[],'',$lang) }}</div>
                            <div>{{ trans('sales::view.Proposed line 6',[],'',$lang) }}</div>
                        </div>
                        <div class="col-xs-12 col-sm-7 comment proposed-comment"><textarea readonly="true" class="proposed form-control" id="proposed" maxlength="2000">{{$cssResult->proposed}}</textarea></div>
                    </div>

                </div>
                <!-- END PROJECT DETAIL -->


            </section>
        </div>
    </div>
</div>
@endsection
<!-- Styles -->
@section('css')
<link href="{{ CoreUrl::asset('sales/css/css_customer.css') }}" rel="stylesheet" type="text/css" >
<link href="{{ asset('lib/rateit/rateit.css') }}" rel="stylesheet" type="text/css" >
@endsection

<!-- Script -->
@section('script')
<script src="{{ asset('lib/rateit/jquery.rateit.js') }}"></script>
<script src="{{ asset('lib/js/jquery.visible.js') }}"></script>
<script src="{{ asset('sales/js/css/make.js') }}"></script>
@endsection