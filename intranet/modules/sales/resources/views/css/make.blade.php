<?php 
use Rikkei\Sales\Model\Css;
use Illuminate\Support\Facades\Config as SupportConfig;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Project\Model\Project;
use Rikkei\Sales\View\View;
use Illuminate\Support\Str;

$lang = SupportConfig::get('langs.'.$css->lang_id);
if ($lang == null) {
    $lang = SupportConfig::get('langs.'.Css::JAP_LANG);
}
$lang = View::checkLang($lang, $css->project_type_id, $css->lang_id, $css->created_at);
$data['lang'] = $lang;
$transType = null;
if ($css->project_type_id == Css::TYPE_ONSITE) {
    $transType = '_onsite';
}
?>

@extends('layouts.guest')

@section('title')
    {{trans('sales::view.Customer service survey',[],'',$lang)}}
@endsection

@section('content')
<div class="make-css-page">
    <div class="row">
        <div class="col-md-12">
            <section id="make-header">
                <div class="logo-rikkei"><img src="{{ URL::asset('common/images/logo-rikkei.png') }}"></div>
                <h2 class="title{{ $css->project_type_id === 2 ? ' title-base' : ''}}{{ $lang == 'en' ? ' title-eng' : '' }}
                    {{ ($lang == 'vi') ? ' font-viet' : '' }}"
                    {!! $css->project_type_id == 5 ? 'style="font-size: 27px;"' : '' !!}>
                {{ trans('sales::view.Welcome title'.$transType, [], '', $lang) }}</h2>
                <span class="visible-check"></span>
                <div class="total-point-container <?php if($css->project_type_id === 2){ echo 'total-point-container-base'; }?>">
                    <div class="total-point-text">{{ trans('sales::view.Total point',[],'',$lang)}}</div>
                    <div class="total-point <?php if($css->project_type_id === 2){ echo 'total-point-base'; }?>" >00.00</div>
                </div>
            </section>
            <section>

                <!-- PROJECT INFORMATION -->
                <div class="row project-info{{ ($lang == 'vi') ? ' font-viet' : '' }}">
                    <div class="col-xs-12 header <?php if($css->project_type_id === 2){ echo 'header-base'; }?>">{{ trans('sales::view.Project information',[],'',$lang) }}
                        <span id="help" class="help" data-toggle="tooltip" data-placement="top" title='{{trans("sales::view.Title help",[],"",$lang)}}'><i class="fa fa-question-circle" aria-hidden="true"></i></span>
                    </div>
                    <div class="col-xs-12 col-sm-6 padding-left-right-5px <?php if($lang == "en") :?>font-viet <?php endif; ?>">
                        <div class="row-info">{{ trans('sales::view.Project name jp',[],'',$lang) }}@if(trim($css->project_name_css) != null) {{$css->project_name_css}}  @else {{ $css->project_name}} @endif</div>
                        <div class="row-info">{{ trans('sales::view.Customer company name jp',[],'',$lang) }}{{ $css->company_name }}
                        @if($lang == "ja") 様 @endif</div>
                        <div class="row-info">{{ trans('sales::view.Make name jp',[],'',$lang)}}<span class="make-name">{{ $makeName }}</span> @if($lang == "ja") 様 @endif </div>
                    </div>
                    <div class="col-xs-12 col-sm-6 padding-left-right-5px <?php if($lang == "en") :?>font-viet <?php endif; ?>">
                        <div class="row-info">{{ trans('sales::view.Sale name jp',[],'',$lang) }}{{ $css->sale_name_jp }}</div>
                        @if ($projectOfCss->type == Project::TYPE_ONSITE)
                            <div class="row-info">{{ trans('sales::view.Onsiter',[],'',$lang) }}: {{ $css->pm_name_jp }}</div>
                            <div class="row-info">{{ trans('sales::view.Onsite date',[],'',$lang) }}: {{ $css->getOnsiteRangeDate() }}</div>
                        @else
                            @if($css->project_type_id === 1)
                                <div class="row-info">{{ trans('sales::view.PM name jp osdc',[],'',$lang) }}{{ $css->pm_name_jp }}</div>
                            @else
                                 <div class="row-info">{{ trans('sales::view.PM name jp',[],'',$lang) }}{{ $css->pm_name_jp }}</div>
                            @endif
                        @endif
                        
                    </div>
                </div>
                <!-- END PROJECT INFORMATION -->

                <!-- PROJECT DETAIL -->
                <div class="row project-detail{{ ($lang == 'vi') ? ' font-viet' : '' }}">
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
                        <div class="col-xs-12 root-cate
                            <?php if($item['noCate'] == 1) echo 'root-cate-first'; ?>
                            <?php if($css->project_type_id === 2){ echo 'root-cate-base'; }?>">
                            <span> {{$item["sort_order"] . ". " .$item['name']}} </span>
                            <span class ="help-noti <?php if($css->project_type_id === 1 && $lang == 'en'){echo 'help-noti-eng';} ?>">
                            <i class="fa"><span>※</span> {{trans('sales::view.Help noti',[],'',$lang)}}</i></span>
                            <div>
                                <span class="help-noti"><i class="fa ">{{ trans('sales::view.Help noti 2',[],'',$lang) }}</i></span>
                            </div>
                        </div>

                        @if($item['cssCateChild'])
                            @foreach($item['cssCateChild'] as $itemChild)
                                <div class="col-xs-12 child-cate">
                                    <div class="cate-des-left">{{$itemChild["sort_order"] . ". " .$itemChild['name']}}</div>
                                    <div class="cate-des-right">{{ $itemChild["question_explanation"] }}</div>
                                </div>
                                @if($itemChild['questionsChild'])
                                    @foreach($itemChild['questionsChild'] as $questionChild)
                                    <?php $itemChild_zindex = $itemChild_zindex -1; ?>
                                    <div class="row container-question">
                                        <div class="col-xs-12 col-sm-5 question"><span class="num-border">{{ $questionChild->sort_order }}</span> {{ Str::limit($questionChild->content, 190) }} @if ($questionChild->explain) <i class="fa fa-question-circle" data-toggle="tooltip" data-html="true" title="{{ View::renderQsExplain($questionChild->explain) }}"></i> @endif </div>
                                        <div class="col-xs-12 col-sm-2 rate">
                                            <div class="rateit" data-rateit-step='1'
                                                 data-rateit-resetable="false"
                                                 data-questionid="{{$questionChild->id}}"
                                                 onclick="totalMark(this);"
                                                 ontouchend="totalMark(this);"></div>    
                                        </div>
                                        {{--<div class="col-xs-12 col-sm-5 comment tooltip" title=""><textarea  type="text" class="comment-question form-control" maxlength="1000" data-questionid="{{$questionChild->id}}" onkeyup="checkPoint(this);" ></textarea></div>--}}
                                        <div class="col-xs-12 col-sm-5 comment" style="z-index: {{$itemChild_zindex}}">
                                            <textarea type="text" class="comment-question form-control" data-questionid="{{$questionChild->id}}" onkeyup="checkPoint(this);" ></textarea>
                                            <div class="dropdown-content make-conten-dropdown">
                                                <textarea  type="text" rows="5" class="form-control" data-questionid="{{$questionChild->id}}" onkeyup="checkPoint(this);" ></textarea>
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
                                <div class="col-xs-12 col-sm-5 question"><span class="num-border">{{ $question->sort_order }}</span> {{ Str::limit($question->content, 190) }} @if ($question->explain) @endif <i class="fa fa-question-circle" data-toggle="tooltip" data-html="true" title="{{ View::renderQsExplain($question->explain) }}"></i></div>
                                <div class="col-xs-12 col-sm-2 rate">
                                    <div class="rateit" data-rateit-step='1'
                                         data-rateit-resetable="false"
                                         data-questionid="{{$question->id}}"
                                         onclick="totalMark(this);"
                                         ontouchend="totalMark(this);" ></div>
                                </div>
                                {{--<div class="col-xs-12 col-sm-5 comment tooltip" title=""><textarea type="text" class="comment-question form-control" maxlength="1000" data-questionid="{{$question->id}}" onkeyup="checkPoint(this);" ></textarea></div>--}}
                                <div class="col-xs-12 col-sm-5 comment" style="z-index: {{$item_zindex}}">
                                    <textarea type="text" class="comment-question form-control" data-questionid="{{$question->id}}" onkeyup="checkPoint(this);" ></textarea>
                                    <div class="dropdown-content make-conten-dropdown">
                                        <textarea type="text" rows="5" class="form-control" data-questionid="{{$question->id}}" onkeyup="checkPoint(this);" ></textarea>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @endif
                    @endforeach

                    <!-- Overview question -->
                    <div class="col-xs-12 root-cate <?php if($css->project_type_id === 2){ echo 'root-cate-base'; }?>">
                        {{$noOverView . ". " .trans('sales::view.General',[],'',$lang) }}
                    </div>
                    <div class="row container-question">
                        <div class="col-xs-12 col-sm-5 question">
                            @if ($cssOld) @if ($lang == "ja" || $css->project_type_id == 5) {{ $overviewQuestionContent }} @else {{ trans('sales::view.OverviewQuestionContent OSDC', [], '', $lang) }} @endif @else {{ $overviewQuestionContent }} @endif @if ($overviewQuestionExplain) <i class="fa fa-question-circle" data-toggle="tooltip" data-html="true" title="{{ View::renderQsExplain($overviewQuestionExplain) }}"></i> @endif
                        </div>
                        <div class="col-xs-12 col-sm-2 rate">
                            <div id="tongquat" class="rateit" data-rateit-step='1'
                                 data-rateit-resetable="false"
                                 data-questionid="{{$overviewQuestionId}}"
                                 onclick="totalMark(this);"
                                 ontouchend="totalMark(this);"></div>
                        </div>
                        {{--<div class="col-xs-12 col-sm-5 comment tooltip" title=""><textarea type="text" class="comment-question form-control" maxlength="1000" data-questionid="{{$overviewQuestionId}}" id="comment-tongquat" onkeyup="checkPoint(this);" ></textarea></div>--}}
                        <div class="col-xs-12 col-sm-5 comment" style="z-index: 1">
                            <textarea type="text" rows="1" class="comment-question form-control" data-questionid="{{$overviewQuestionId}}" id="comment-tongquat" onkeyup="checkPoint(this);" ></textarea>
                            <div class="dropdown-content make-conten-dropdown">
                                <textarea type="text" rows="5" class="form-control" data-questionid="{{$overviewQuestionId}}" id="comment-tongquat" onkeyup="checkPoint(this);" ></textarea>
                            </div>
                        </div>
                    </div>
                    <!-- Proposed -->
                    {{-- <div class="row proposed container-question">
                        <div class="col-xs-12 col-sm-5 question">
                            <div>{{ trans('sales::view.Proposed line 1'.$transType, [], '', $lang) }}</div>
                            <div>{{ trans('sales::view.Proposed line 2'.$transType, [], '', $lang) }}</div>
                            <div>{{ trans('sales::view.Proposed line 3'.$transType, [], '', $lang) }}</div>
                            <div>{{ trans('sales::view.Proposed line 4'.$transType, [], '', $lang) }}</div>
                            <div>{{ trans('sales::view.Proposed line 5'.$transType, [], '', $lang) }}</div>
                            <div>{{ trans('sales::view.Proposed line 6'.$transType, [], '', $lang) }}</div>
                        </div>
                        <div class="col-xs-12 col-sm-7 comment proposed-comment"><textarea class="proposed form-control" id="proposed" maxlength="2000"></textarea></div>
                    </div> --}}
                    <!-- Button submit -->
                    <div class="col-xs-12 container-submit"><button type="button" class="btn btn-primary <?php if($css->project_type_id === 2){ echo 'button-base'; }?>" onclick="confirm('{{$arrayValidate}}');">{{trans('sales::view.Send',[],'',$lang)}}</button></div>
                </div>
                <!-- END PROJECT DETAIL -->

                <!-- MODALS -->
                <div class="modal modal-danger" id="modal-alert">
                    <div class="modal-dialog">
                        <div class="modal-content{{ $lang == 'vi' ? ' font-viet' : '' }}">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">×</span></button>
                                <h4 class="modal-title">{{ trans('sales::view.Confirm make css',[],'',$lang) }}</h4>
                            </div>
                            <div class="modal-body">
                                <p>One fine body…</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline pull-right" data-dismiss="modal">{{ trans('sales::view.Close jp',[],'',$lang)}}</button>
                            </div>
                        </div>
                        <!-- /.modal-content -->
                    </div>
                    <!-- /.modal-dialog -->
                </div>

                <div class="modal modal-primary" id="modal-confirm">
                    <div class="modal-dialog">
                        <div class="modal-content{{ $lang == 'vi' ? ' font-viet' : '' }}">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">×</span></button>
                                <h4 class="modal-title">{{ trans('sales::view.Confirm make css',[],'',$lang) }}</h4>
                            </div>
                            <div class="modal-body">
                                <p>One fine body…</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline pull-left cancel" data-dismiss="modal">{{ trans('sales::view.Cancel make css',[],'',$lang) }}</button>
                                <button type="button" class="btn btn-outline submit" onclick="submit('{{ Session::token() }}',{{$css->id}});">{{ trans('sales::view.Submit make css',[],'',$lang) }}</button>
                            </div>
                        </div>
                        <!-- /.modal-content -->
                    </div>
                    <!-- /.modal-dialog -->
                </div>

                <div class="modal modal-warning" id="modal-confirm-make">
                    <div class="modal-dialog">
                        <div class="modal-content{{ $lang == 'vi' ? ' font-viet' : '' }}">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true"></span></button>
                                <h4 class="modal-title">{{ trans('sales::view.Warning vn',[],'',$lang) }}</h4>
                            </div>
                            <div class="modal-body">
                                <p>{{ trans('sales::message.Make confirm line 1',[],'',$lang) }}</p>
                                <p>{{ trans('sales::message.Make confirm line 2',[],'',$lang) }}</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline pull-left" data-dismiss="modal" onclick="goToFinish();">{{ trans('sales::view.No',[],'',$lang) }}</button>
                                <button type="button" class="btn btn-outline" onclick="hideModalConfirmMake();">{{ trans('sales::view.Yes',[],'',$lang) }}</button>
                            </div>
                        </div>
                        <!-- /.modal-content -->
                    </div>
                    <!-- /.modal-dialog -->
                </div>
                <div class="modal apply-click-modal"><img class="loading-img" src="{{ asset('sales/images/loading.gif') }}" /></div>

                <!-- modal help -->
                <div class="modal modal-help" id="help-popup">
                    <div class="modal-dialog">
                        <div class="modal-content{{ $lang == 'vi' ? ' font-viet' : '' }}">
                            <div class="modal-header modal-header-help <?php if($css->project_type_id === 2){ echo 'modal-header-help-2'; }?>">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span></button>
                                <h4 class="modal-title"><i class="fa fa-question-circle" aria-hidden="true"></i> {{trans('sales::view.Title help',[],'',$lang)}}</h4>
                            </div>
                            <div class="modal-body modal-body-help">
                                {!! trans('sales::view.Content help',[],'',$lang) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection

<!-- Styles -->
@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" >
<link href="{{ CoreUrl::asset('sales/css/css_customer.css') }}" rel="stylesheet" type="text/css" >
<link href="{{ CoreUrl::asset('lib/rateit/rateit.css') }}" rel="stylesheet" type="text/css" >
@include('sales::css.css_tooltip');
<style>
    .child-cate{
        display: flex;
    }
    .child-cate .cate-des-left{
        flex: 0 0 33%;
        max-width: 33%;
        width: 33%;
        padding-right: 20px;
        border-right: 1px solid #ccc;
    }
    .child-cate .cate-des-right{
        flex-grow: 1;
        padding-left: 20px;
    }
</style>
@endsection

<!-- Script -->
@section('script')
<script>
    var code = '{{ $code }}';
    var urlSubmit = '{{route("sales::saveResult")}}';
    var tran = '<?php echo trans('sales::view.Please leave the comment here',[],'',$lang) ?>';
    var urlSuccess = {{ $css->lang_id }};
    var messagecoment1 = '<?php echo trans('sales::message.The current score is',[],'',$lang) ?>';
    var messagecoment2 = '<?php echo trans('sales::message.Do you want to submit the result',[],'',$lang) ?>';
    var buttonBack = '<?php echo trans('sales::message.Back to the survey',[],'',$lang) ?>';
    var buttonSubmit = '<?php echo trans('sales::message.Submit as',[],'',$lang) ?>';
    var buttonSend = '<?php echo trans('sales::message.Send the survey',[],'',$lang) ?>';
    var textUnsatisfactory = "<?php echo trans('sales::view.Unsatisfactory', [], '', $lang) ?>";
    var textSatisfactory = "<?php echo trans('sales::view.Satisfactory', [], '', $lang) ?>";
    var textFair = "<?php echo trans('sales::view.Fair', [], '', $lang) ?>";
    var textGood = "<?php echo trans('sales::view.Good', [], '', $lang) ?>";
    var textExcellent = "<?php echo trans('sales::view.Excellent', [], '', $lang) ?>";
    var jpLang = "{{ SupportConfig::get('langs.'.Css::JAP_LANG) }}";
    var enLang = "{{ SupportConfig::get('langs.'.Css::ENG_LANG) }}";
    var currentLang = "{{ $lang }}";
</script>
<script src="{{ CoreUrl::asset('lib/rateit/jquery.rateit.js') }}"></script>
<script src="{{ CoreUrl::asset('lib/js/jquery.visible.js') }}"></script>
<script src="{{ CoreUrl::asset('sales/js/css/customer.js') }}"></script>
<script src="{{ CoreUrl::asset('sales/js/css/make.js') }}"></script>
<script type="text/javascript">
    <?php if(Auth::check()): ?>
        $('#modal-confirm-make').show();
    <?php endif; ?>

    $(document).on('click','#help',function(){
        $('#help-popup').modal('show');
    });

</script>
@endsection
