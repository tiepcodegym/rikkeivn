<?php
use Rikkei\Sales\Model\Css;
use Illuminate\Support\Facades\Config as SupportConfig;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Project\Model\Project;
use Rikkei\Sales\Model\CssResult;
use Rikkei\Sales\View\View;
use Illuminate\Support\Str;

$lang = SupportConfig::get('langs.'.$css->lang_id);
if ($lang == null) {
    $lang = SupportConfig::get('langs.'.Css::JAP_LANG);
}
$lang = View::checkLang($lang, $css->project_type_id, $css->lang_id, $css->created_at);
if ($cssResult->status) {
    if ($cssResult->status == CssResult::STATUS_NEW || $cssResult->status == CssResult::STATUS_SUBMITTED) {
        $classCss = 'btn-primary';
    } elseif ($cssResult->status == CssResult::STATUS_FEEDBACK) {
        $classCss = 'btn-warning ';
    } elseif ($cssResult->status == CssResult::STATUS_CANCEL) {
        $classCss = 'btn-danger ';
    } else {
        $classCss = 'btn-success';
    }
}
$transType = null;
if ($css->project_type_id == Css::TYPE_ONSITE) {
    $transType = '_onsite';
}
?>

@extends('layouts.default')

@section('title')
    {{ trans('sales::view.Detail.Title') }}
@endsection

@section('content')
<div>
    <a class="btn btn-primary" href="{{url('/css/export_excel/'.$cssResult->id)}}" target="_blank">Export excel</a>
    @if ($cssResult->status)
    <label class="margin-left-15" style="font-size: 17px;">Status:</label>
    <label id="status-css" class="btn width-150 margin-left-15 {!! $classCss !!}">{{ CssResult::getLabelStatusCssResult()[$cssResult->status] }}</label>
    @endif
    <a href="{{ route('project::point.edit', ['id' => $projectOfCss->id]) }}" target="_blank"  class="link-workorder-css margin-left-25">{{ trans('sales::view.Project Report') }}</a>
    <a href="{{ route('project::project.edit', ['id' => $projectOfCss->id]) }}" target="_blank" class="link-workorder-css">{{ trans('sales::view.View workorder') }}</a>
</div>
<div class="box box-primary detail-css-page make-css-page body-padding">
    <div class="body-padding">
        <div>
            <section id="make-header">
                <div class="logo-rikkei"><img src="{{ URL::asset('common/images/logo-rikkei.png') }}"></div>
                <h2 class="title <?php if($css->project_type_id === 2){ echo 'title-base'; }?><?php if($lang == "en") echo 'title-eng'; ?>"
                    {!! $css->project_type_id == 5 ? 'style="font-size: 27px!important;"' : '' !!}>
                {{ trans('sales::view.Welcome title' . $transType, [], '', $lang)}}</h2>
                <span class="visible-check"></span>
                <div class="total-point-container <?php if($css->project_type_id === 2){ echo 'total-point-container-base'; }?>">
                    <div class="total-point-text">{{ trans('sales::view.Total point',[],'',$lang)}}</div>
                    <div class="total-point <?php if($css->project_type_id === 2){ echo 'total-point-base'; }?> " >{{number_format($cssResult->avg_point,2)}}</div>
                </div>
            </section>
            
            <section>
                <!-- PROJECT INFORMATION -->
                <div class="row project-info{{ $lang == 'vi' ? ' font-viet' : '' }}">
                    <div class="col-xs-12 header <?php if($css->project_type_id === 2){ echo 'header-base'; }?>">{{ trans('sales::view.Project information',[],'',$lang) }}</div>
                    <div class="col-xs-12 col-sm-6 padding-left-right-5px <?php if($lang == "en") :?>font-viet <?php endif; ?>">
                        <div class="row-info">{{ trans('sales::view.Project name jp',[],'',$lang) }} @if(trim($css->project_name_css) != null) {{$css->project_name_css}}  @else {{ $css->project_name}} @endif</div>
                        <div class="row-info">{{ trans('sales::view.Customer company name jp',[],'',$lang) }} {{ $css->company_name }}
                        @if($lang == "ja") 様 @endif</div>
                        <div class="row-info">{{ trans('sales::view.Make name jp',[],'',$lang)}}<span class="make-name">{{ $cssResult->name }}</span>@if($lang == "ja") 様 @endif</div>
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
                        <div class="row-info">{{ trans('sales::view.Project date jp',[],'',$lang) }}{{ date("Y/m/d",strtotime($css->start_date)) }} - {{ date("Y/m/d",strtotime($css->end_date)) }}</div>
                    </div>
                </div>
                <!-- END PROJECT INFORMATION -->

                <!-- PROJECT DETAIL -->
                <div class="row project-detail{{ $lang == 'vi' ? ' font-viet' : '' }}">
                    <div class="col-xs-12 header <?php if($css->project_type_id === 2){ echo 'header-base'; }?>">
                        <div class="col-xs-12 col-sm-3 criteria-header">{{ trans('sales::view.Question',[],'',$lang) }}</div>
                        <div class="col-sm-2 rating-header">{{ trans('sales::view.Rating',[],'',$lang) }}</div>
                        <div class="col-sm-3 comment-header">{{ trans('sales::view.Comment',[],'',$lang) }}</div>
                        <div class="col-sm-4 analysis-header">{{ trans('sales::view.Analysis',[],'',$lang) }}</div>
                    </div>
                    <?php
                    $itemChild_zindex = 100;
                    $item_zindex = 50;
                    ?>
                    @foreach($cssCate as $item)
                        @if($item['noCate'] == 1)
                        <div class="col-xs-12 root-cate root-cate-first <?php if($css->project_type_id === 2){ echo 'root-cate-base'; }?>">
                            <span>{{$item["sort_order"] . ". " .$item['name']}}</span>
                            <span class="help-noti"><i class="fa ">{{ trans('sales::view.Help noti 2',[],'',$lang) }}</i></span>
                        </div>
                        @else
                        <div class="col-xs-12 root-cate <?php if($css->project_type_id === 2){ echo 'root-cate-base'; }?>">
                            <span>{{$item["sort_order"] . ". " .$item['name']}}</span>
                            <span class="help-noti"><i class="fa ">{{ trans('sales::view.Help noti 2',[],'',$lang) }}</i></span>
                        </div>
                        @endif

                        @if($item['cssCateChild'])
                            @foreach($item['cssCateChild'] as $itemChild)
                                <div class="col-xs-12 child-cate">
                                    <div class="cate-des-left">{{$itemChild["sort_order"] . ". " .$itemChild['name']}}</div>
                                    <div class="cate-des-right">{{ $itemChild["question_explanation"] }}</div>
                                </div>
                                @if($itemChild['questionsChild'])
                                    @foreach($itemChild['questionsChild'] as $questionChild)
                                    <?php $itemChild_zindex = $itemChild_zindex -1; ?>
                                    <div class="row container-question" data-url='{{ route('sales::insertAnalysisResult') }}' data-token='{{ Session::token() }}' data-cssid='{{$css->id}}'>
                                        <div class="col-xs-12 col-sm-3 question"><span class="num-border">{{ $questionChild->sort_order }}</span> {{ Str::limit($questionChild->content, 150) }} @if ($questionChild->explain) <i class="fa fa-question-circle" data-toggle="tooltip" data-html="true" title="{{ View::renderQsExplain($questionChild->explain) }}"></i> @endif</div>
                                        <div class="col-xs-12 col-sm-2 rate"><div class="rateit" data-rateit-value="{{$questionChild->point}}" data-rateit-step='1' data-rateit-resetable="false" data-rateit-readonly="true" data-questionid="{{$questionChild->id}}" onclick="totalMark(this);" ontouchend="totalMark(this);" ></div></div>
                                        {{--<div class="col-xs-12 col-sm-5 comment tooltip" title="{{$questionChild->comment}}"><textarea type="text" readonly="true" class="comment-question form-control" type="text" maxlength="1000" data-questionid="{{$questionChild->id}}" onkeyup="checkPoint(this);" value='{{$questionChild->comment}}' >{{$questionChild->comment}}</textarea></div>--}}
                                        <div class="col-xs-12 col-sm-3 comment @if($questionChild->comment) dropdown @endif" style="z-index: {{$itemChild_zindex}}">
                                            <textarea type="text" readonly="true" class="comment-question form-control" type="text" maxlength="1000" data-questionid="{{$questionChild->id}}" onkeyup="checkPoint(this);" value='{{$questionChild->comment}}' >{{$questionChild->comment}}</textarea>
                                            <div class="dropdown-content detail-conten-dropdown">
                                                <textarea type="text" rows="5" readonly="true" class="form-control" type="text" maxlength="1000" data-questionid="{{$questionChild->id}}" onkeyup="checkPoint(this);" value='{{$questionChild->comment}}' >{{$questionChild->comment}}</textarea>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-4 @if($questionChild->analysis) dropdown @endif" style="z-index: {{$itemChild_zindex}}">
                                            <textarea type="text" class="analysis comment-question form-control" type="text" data-questionid="{{$questionChild->id}}" value='{{$questionChild->analysis}}' name="analysis-question-{{$questionChild->id}}" data-cssresultid = '{{ $resultDetailRowOfOverview->css_result_id }}' <?php echo $accessSubmit ? '' : 'disabled="true"'?> >{{$questionChild->analysis}}</textarea>
                                            <div class="dropdown-content detail-conten-dropdown">
                                                <textarea type="text" rows="5" class="form-control" type="text" data-questionid="{{$questionChild->id}}" value='{{$questionChild->analysis}}' name="analysis-question-{{$questionChild->id}}" data-cssresultid = '{{ $resultDetailRowOfOverview->css_result_id }}' <?php echo $accessSubmit ? '' : 'disabled="true"'?> >{{$questionChild->analysis}}</textarea>
                                            </div>
                                            <p class="error analysis-error hidden" for="analysis" style="display: inline;">This field is required.</p>
                                            <p class="hint">{{ trans('sales::view.comment-analysis',[],'',$lang) }}</p>
                                        </div>
                                    </div>
                                    @endforeach
                                @endif
                            @endforeach
                        @elseif($item['questions'])
                            @foreach($item['questions'] as $question)
                            <?php $item_zindex = $item_zindex -1; ?>
                            <div class="row container-question" data-url='{{ route('sales::insertAnalysisResult') }}' data-token='{{ Session::token() }}' data-cssid='{{$css->id}}'>
                                <div class="col-xs-12 col-sm-3 question"><span class="num-border">{{ $question->sort_order }}</span> {{ Str::limit($question->content, 150) }} @if ($question->explain) <i class="fa fa-question-circle" data-toggle="tooltip" data-html="true" title="{{ View::renderQsExplain($question->explain) }}"></i> @endif </div>
                                <div class="col-xs-12 col-sm-2 rate"><div class="rateit" data-rateit-value="{{$question->point}}" data-rateit-step='1' data-rateit-resetable="false" data-rateit-readonly="true" data-questionid="{{$question->id}}" onclick="totalMark(this);" ontouchend="totalMark(this);" ></div></div>
                                {{--<div class="col-xs-12 col-sm-5 comment tooltip" title="{{$question->comment}}"><textarea type="text" readonly="true" class="comment-question form-control" type="text" maxlength="1000" data-questionid="{{$question->id}}" onkeyup="checkPoint(this);" value='{{$question->comment}}' >{{$question->comment}}</textarea></div>--}}
                                <div class="col-xs-12 col-sm-3 comment @if($question->comment) dropdown @endif" style="z-index: {{$item_zindex}}">
                                    <textarea type="text" readonly="true" class="comment-question form-control" type="text" maxlength="1000" data-questionid="{{$question->id}}" onkeyup="checkPoint(this);" value='{{$question->comment}}' >{{$question->comment}}</textarea>
                                    <div class="dropdown-content detail-conten-dropdown">
                                        <textarea type="text" rozws="5" readonly="true" class="form-control" type="text" maxlength="1000" data-questionid="{{$question->id}}" onkeyup="checkPoint(this);" value='{{$question->comment}}' >{{$question->comment}}</textarea>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-4 @if($question->analysis) dropdown @endif" style="z-index: {{$item_zindex}}">
                                    <textarea type="text" class="analysis comment-question form-control" type="text" data-questionid="{{$question->id}}" value='{{$question->analysis}}' name="analysis-question-{{$question->id}}" data-cssresultid = '{{ $resultDetailRowOfOverview->css_result_id }}' <?php echo $accessSubmit ? '' : 'disabled="true"'?> >{{$question->analysis}}</textarea>
                                    <div class="dropdown-content detail-conten-dropdown">
                                        <textarea type="text" rows="5" class="form-control" type="text" data-questionid="{{$question->id}}" value='{{$question->analysis}}' name="analysis-question-{{$question->id}}" data-cssresultid = '{{ $resultDetailRowOfOverview->css_result_id }}' <?php echo $accessSubmit ? '' : 'disabled="true"'?> >{{$question->analysis}}</textarea>
                                    </div>
                                    <p class="error analysis-error hidden" for="analysis" style="display: inline;">This field is required.</p>
                                    <p class="hint">{{ trans('sales::view.comment-analysis',[],'',$lang) }}</p>
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
                        <div class="col-xs-12 col-sm-3 question">
                            @if ($cssOld) @if ($lang == "ja" || $css->project_type_id == 5) {{ $overviewQuestionContent }} @else {{ trans('sales::view.OverviewQuestionContent OSDC', [], '', $lang) }} @endif @else {{ $overviewQuestionContent }} @endif @if ($overviewQuestionExplain) <i class="fa fa-question-circle" data-toggle="tooltip" data-html="true" title="{{ View::renderQsExplain($overviewQuestionExplain) }}"></i> @endif
                        </div>
                        <div class="col-xs-12 col-sm-2 rate"><div id="tongquat" class="rateit" data-rateit-value="{{$resultDetailRowOfOverview->point}}" data-rateit-step='1' data-rateit-resetable="false" data-rateit-readonly="true" data-questionid="{{$overviewQuestionId}}" onclick="totalMark(this);" ontouchend="totalMark(this);" ></div></div>
                        {{--<div class="col-xs-12 col-sm-5 comment tooltip" title="{{$resultDetailRowOfOverview->comment}}"><textarea type="text" readonly="true" class="comment-question form-control" type="text" maxlength="1000" data-questionid="{{$overviewQuestionId}}" id="comment-tongquat" onkeyup="checkPoint(this);" value='{{$resultDetailRowOfOverview->comment}}' >{{$resultDetailRowOfOverview->comment}}</textarea></div>--}}
                        <div class="col-xs-12 col-sm-3 comment @if($resultDetailRowOfOverview->comment) dropdown @endif" style="z-index: 1">
                            <textarea type="text" readonly="true" class="comment-question form-control" type="text" maxlength="1000" data-questionid="{{$overviewQuestionId}}" id="comment-tongquat" onkeyup="checkPoint(this);" value='{{$resultDetailRowOfOverview->comment}}' >{{$resultDetailRowOfOverview->comment}}</textarea>
                            <div class="dropdown-content detail-conten-dropdown">
                                {{--<textarea type="text" rows="5" class="form-control" maxlength="1000" data-questionid="{{$questionChild->id}}" onkeyup="checkPoint(this);" ></textarea>--}}
                                <textarea type="text" rows="5" readonly="true" class="form-control" type="text" maxlength="1000" data-questionid="{{$overviewQuestionId}}" id="comment-tongquat" onkeyup="checkPoint(this);" value='{{$resultDetailRowOfOverview->comment}}' >{{$resultDetailRowOfOverview->comment}}</textarea>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-4 @if($resultDetailRowOfOverview->analysis) dropdown @endif" style="z-index: 1">
                            <textarea type="text" class="analysis comment-question form-control" type="text" data-questionid="{{$overviewQuestionId}}" value='{{$resultDetailRowOfOverview->analysis}}' name="analysis-question-{{$overviewQuestionId}}" data-cssresultid = '{{ $resultDetailRowOfOverview->css_result_id }}' <?php echo $accessSubmit ? '' : 'disabled="true"'?> >{{$resultDetailRowOfOverview->analysis}}</textarea>
                            <div class="dropdown-content detail-conten-dropdown">
                                <textarea type="text" rows="5" class="form-control" type="text" data-questionid="{{$overviewQuestionId}}" value='{{$resultDetailRowOfOverview->analysis}}' name="analysis-question-{{$overviewQuestionId}}" data-cssresultid = '{{ $resultDetailRowOfOverview->css_result_id }}' <?php echo $accessSubmit ? '' : 'disabled="true"'?> >{{$resultDetailRowOfOverview->analysis}}</textarea>
                            </div>
                            <p class="error analysis-error hidden" for="analysis" style="display: inline;">This field is required.</p>
                            <p class="hint">{{ trans('sales::view.comment-analysis',[],'',$lang) }}</p>
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
                        <div class="col-xs-12 col-sm-7 comment proposed-comment"><textarea readonly="true" class="proposed form-control" id="proposed" maxlength="2000">{{$cssResult->proposed}}</textarea></div>
                    </div> --}}
                        
                </div>
                @if ($accessSubmit || $accessApprove || $accessCancel || $accessReview)
                <div class="row">
                    <div class="col-sm-offset-5 col-sm-7">
                        @if ($accessCancel)
                        <button class="btn btn-cancel-css-result" style="margin-top: 15px; margin-bottom: 15px;" data-cssresultid='{{$cssResult->id}}' data-token='{{ Session::token() }}'+
                            data-url='{{ route('sales::cancelCssResult') }}'>{{ $cssResult->status == Css::STATUS_CANCEL ? 'Remove cancel status' : 'Cancel' }}</button>
                        @endif
                        @if ($accessSubmit)
                        <button class="btn btn-primary submit-status" id="submit-status" style="margin-top: 15px; margin-bottom: 15px;" data-value="{{ Css::STATUS_SUBMITTED }}"
                                data-url='{{ route('sales::insertAnalysisResult') }}' data-token='{{ Session::token() }}' data-cssid='{{$css->id}}'>Submit</button>
                        <input type="hidden" class="submit-resultDetailCss" value="{{ $resultDetailCss }}"/>
                        @endif
                        @if ($cssResult->status && $cssResult->status != CssResult::STATUS_NEW && $cssResult->status != CssResult::STATUS_CANCEL && ($accessApprove || $accessReview))
                            <button class="btn btn-danger feedback-status" style="margin-top: 15px; margin-bottom: 15px;" data-value="{{ Css::STATUS_FEEDBACK }}" data-url="{{ $accessApprove ?  route('sales::approveStatusCss') : route('sales::reviewStatusCss') }}">Feedback</button>
                            @if($accessApprove && $cssResult->status == CssResult::STATUS_REVIEW)
                                <button class="btn btn-primary approve-status" id="approve-status" style="margin-top: 15px; margin-bottom: 15px;" data-value="{{ Css::STATUS_APPROVED }}" data-url="{{ route('sales::approveStatusCss') }} @if($cssResult->status == Css::STATUS_APPROVED) disabled @endif">Approve</button>
                            @endif
                            @if($accessReview)
                                <button class="btn btn-primary review-status" id="review-status" style="margin-top: 15px; margin-bottom: 15px;" data-value="{{ Css::STATUS_REVIEW }}" data-url="{{ route('sales::reviewStatusCss') }}" @if($cssResult->status == Css::STATUS_REVIEW) disabled @endif>Reviewed</button>
                            @endif
                            <input type="hidden" class="approve-resultDetailCss" value="{{ $resultDetailCss }}"/>
                        @endif
                    </div>
                </div>
                @endif
                <!-- END PROJECT DETAIL -->
                <!--guide for analisys.-->
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h2 class="box-title">Chú ý khi submit, approve, feedback css analysis</h2>
                    </div>
                    <div class="box-body">
                        <pre class="alert alert-warning">
    - Mỗi bài Css do khách hàng khảo sát cần được PM submit để có thể close dự án khi dự án kết thúc.
    - Mỗi Css thì có thể có nhiều bài Css do khách hàng tạo ra.PM phải phân tích ít nhất 1 trong số bài Css đó.
    - Nếu mỗi câu hỏi có số điểm(ngôi sao) <=3 thì bắt buộc phải điền vào ô analysis tương ứng với câu hỏi đó thì mới được submit.
    - Sau khi submit, PQA leader là người Review (hoặc Feedback), Division leader của dự án sẽ là người Approve (hoặc Feedback).
                        </pre>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
<!--modal feedback customer css-->
<div class="modal" id="modal-confirm-feedback-css">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"></span></button>
                <h3>Feedback Content</h3>
            </div>
            <div class="modal-body">
                <textarea rows="5" class="form-control" id="content-css"></textarea>
                <p class="error comment-css-error hidden" for="content-css">This field is required.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn pull-left" data-dismiss="modal">Cancel</button>
                <button type="button" id="comment-analysis-css" class="btn btn-danger">Feedback</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
@endsection

<!-- Styles -->
@section('css')
<link href="{{ CoreUrl::asset('sales/css/sales.css') }}" rel="stylesheet" type="text/css" >
<link href="{{ asset('lib/rateit/rateit.css') }}" rel="stylesheet" type="text/css" >
@include('sales::css.css_tooltip');
<style>
    @media screen and (min-width: 768px){
        .container-question{
            height: 122px !important;
        }
        @media screen and (min-width: 992px){
            .container-question{
                height: 85px !important;
            }
        }
    }
</style>
@endsection

<!-- Script -->
@section('script')
<script src="{{ asset('lib/rateit/jquery.rateit.js') }}"></script>
<script src="{{ asset('lib/js/jquery.visible.js') }}"></script>
<script src="{{ asset('sales/js/css/make.js') }}"></script>

@endsection
