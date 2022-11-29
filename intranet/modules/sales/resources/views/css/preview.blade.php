<?php

$a = \Rikkei\Project\Model\ProjectApprovedProductionCost::select('project_approved_production_cost.*','kind_id')
    ->leftJoin("projs", "projs.id", "=", "project_approved_production_cost.project_id")
    ->where('project_id', 4489)
    ->orderBy('year')
    ->orderBy('month')
    ->get()
    ->toArray();
//$f = array_unique(array_column($a, 'year'));
//
//foreach ($f as $j){
//    foreach ($a as $child){
//        if($j==$child["year"]){
//            $data[$j][]=$child;
//        }
//    }
//}
//$data2 = [];
//foreach ($a as $key => $child){
//    $data = $child;
//    $data['rowspan'] = empty($data['year']) ? 1 : 0;
//    $data2[$data['year']][] = $data;
//    if (!empty($data['year'])) {
//        $data2[$data['year']][0]['rowspan'] = count($data2[$data['year']]);
//    }
//}

$data3 = [];
foreach ($a as $child){
    $data = $child;
    $data['rowspan'] = empty($data['year']) ? 1 : 0;
    $data3[$child['year']][$child['month']][] = $child;
    foreach ($data3[$child['year']] as $p){
       $p[0]['rowspan1'] = count($data3[$child['year']][$child['month']]);
    }
}


dd($data3);









use Rikkei\Team\View\Permission;
use Rikkei\Sales\Model\CssMail;
use Rikkei\Sales\Model\Css;
use Illuminate\Support\Facades\Config as SupportConfig;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Project\Model\Project;
use Rikkei\Sales\View\View;
use Illuminate\Support\Str;

$count = count(CssMail::getCssMailByCssId($css->id));
if ($count) {
    $resend = true;
}

//Check permission send mail
if (Permission::getInstance()->isAllow('sales::css.sendMailCustomer')) {
    $sendMailPermission = true;
} else {
    $sendMailPermission = false;
}
//check language

$lang = SupportConfig::get('langs.'.$css->lang_id);
if ($lang == null) {
    $lang = SupportConfig::get('langs.'.Css::JAP_LANG);
}
$lang = View::checkLang($lang, $css->project_type_id, $css->lang_id, $css->created_at);
$data['lang'] = $lang;
$data['project_type_id'] = $css->project_type_id;
$data['project_name'] = $css->project_name_css ? $css->project_name_css : $css->project_name;
$curUser = Permission::getInstance()->getEmployee()->id;
$data['employee'] = !empty($css->css_creator_name) ? $css->css_creator_name : ucwords(Str::slug(Session::get('employee_'.$curUser)->name, ' '));
$data['time_reply'] = $css->time_reply;
$transType = null;
if ($css->project_type_id == Css::TYPE_ONSITE) {
    $transType = '_onsite';
}
$date = explode('-', $data['time_reply']);
?>

@extends('layouts.default')

@section('title')
{{ trans('sales::view.Preview.Title') }}
@endsection

@section('content')
<div class="box box-primary preview-page">
@if ($hasPermission)
    <div class="body-padding">
        <div class="row box-title">
            <div class="col-sm-2">
                <h3>
                    {{ trans('sales::view.Preview.Url CSS') }}
                </h3>
            </div>
            <div class="col-sm-10" style="margin-top: 21px;">
                <a href="{{ route('project::point.edit', ['id' => $css->projs_id]) }}" target="_blank" class="link-workorder-filter">{{ trans('sales::view.Project Report') }}</a>
                <a href="{{ route('project::project.edit', ['id' => $css->projs_id]) }}" target="_blank" class="link-workorder-filter">{{ trans('sales::view.View workorder') }}</a>
            </div>
        </div>
        <hr style="margin-top: 6px;">
        {!! trans('sales::view.Preview.Link make css info') !!}
        <h3 id="link-make" class="wrap">{{$hrefMake}}</h3>
        <div class="text-align-center">
            <a href="{{$hrefUpdateCss}}" class="btn btn-primary btn-update-css">Back</a>
        </div>
    </div>

    <!--Send mail to customer -->
    @if ($sendMailPermission)
    <hr>
    <div>
        <!-- form start -->
        <div class="form-group form-sendmail">
            <div class="col-sm-12">
                <div class="form-group">
                    <div class="margin-top-10 row">
                        <div class="col-md-1"></div>
                        <div class="col-md-6">
                            <a href="{{ route('sales::css.downloadTemplate', ['type' => 'en']) }}" style="padding: 0 20px 0 0;">Download the default English template</a> | 
                            <a href="{{ route('sales::css.downloadTemplate', ['type' => 'jp']) }}" style="padding: 0 20px;">Download the default Japan template</a>
                        </div>
                        <div class="col-md-4">
                            <a href="https://docs.google.com/document/d/1LbJcumh_qjEHYgseEKeVcMvQmDt4LdY8oxD6Nb_9Kq4/edit#heading=h.9ipf5khywj4u" target="_blank">Help document</a>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>

        {!! Form::open([
            'method' => 'post', 
            'route' => ['sales::css.importTemplate', 'token'=>$token,'id'=>$id],
            'files' => true, 
            'id' => 'upload-member',
            'class' => 'no-validate form-horizontal'
        ]) !!}
            <div class="form-group form-sendmail">
                <div class="col-sm-12">
                    <div class="form-group">
                        <div class="margin-top-10 row">
                            <div class="col-md-1"></div>
                            <div class="col-md-4">
                                <input class="form-control excel-file" type="file" name="excel_file" accept=".csv, .xlsx, .xls">
                                <span class="error err-excel_file">{{ $errors->first('excel_file') }}</span>
                            </div>
                            <input type="hidden" name="css_id" value="{{ $css->id }}">
                            <div class="col-md-2">
                                <button type="submit" class="btn-add"><i class="fa fa-upload"></i> Import template</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        {!! Form::close() !!}

        <form class="form-horizontal form-sendmail">
            <div class="box-body">
                <div class="form-group">
                    <div class="col-sm-12">
                        <div class="form-group position-relative cus-row-container">
                            @if (count($cssMail))
                            @foreach ($cssMail as $item)
                            <div class="cus-row margin-top-10 row" id="item-{{ $item->id }}">
                                <div class="col-md-1">
                                    <!-- if customer not make Css then show checkbox -->
                                    @if (!in_array($item->code, $codeResults))
                                    <input type="checkbox" class="check" />
                                    @endif
                                </div>
                                <div class="col-md-2">
                                    <select class="form-control select-gender" disabled="disabled" name="select-gender">
                                        @if (isset($item->gender))
                                            <option value="{{ $item->gender }}">{{ CssMail::getGenderCustomer()[$item->gender] }}</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control col-sm-3 customer-name" placeholder="{{ trans('sales::view.Customer name') }}" value="{{$item->name}}" disabled="disabled">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control col-sm-9 customer-email" placeholder="{{ trans('sales::view.Email address') }}" value="{{$item->mail_to}}" disabled="disabled">
                                    <span>{{trans('sales::view.Last send: ') . $item->updated_at}}</span>
                                </div>
                                <div class="col-md-1 delete-css-email">
                                    <span class="btn-delete"><i class="fa fa-trash"></i></span>
                                    <input class="id-email-css" type="hidden" name="idEmailCss" value="{{$item->id}}" />
                                </div>
                            </div>
                            @endforeach
                            @else
                            <div class="cus-row margin-top-10 row cus-row-new" id="item-first">
                                <div class="col-md-1">
                                    <input type="checkbox" class="check" />
                                </div>
                                <div class="col-md-2">
                                    <select class="form-control select-gender" name="select-gender">
                                            <option value="1">Mr.</option>
                                            <option value="0">Ms.</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control col-sm-3 customer-name" placeholder="{{ trans('sales::view.Customer name') }}">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control col-sm-9 customer-email" placeholder="{{ trans('sales::view.Email address') }}">
                                </div>
                                <div class="col-md-1">
                                    <span class="btn-delete delete-x" onclick="removeRow(this);"><i class="fa fa-remove"></i></span>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-11">
                        <span class="btn-add add-cus pull-right row"><i class="fa fa-plus"></i></span>
                    </div>
                    <div class="col-sm-11">
                        <button id="send-mail" type="button" class="btn btn-primary pull-right row margin-top-20">
                            <span>
                                {{ trans('sales::view.CSS.Preview.Send mail to customer') }} 
                                <i class="fa fa-spin fa-refresh hidden"></i>
                            </span>
                        </button>
                        <button id="check-all" type="button" class="btn btn-primary pull-right margin-right-20 margin-top-20">
                            <span>
                                {{ trans('sales::view.CSS.Preview.Check all customer') }} 
                                <i class="fa fa-spin fa-refresh hidden"></i>
                            </span>
                        </button>
                        <button id="uncheck-all" type="button" class="btn btn-primary pull-right margin-right-20 margin-top-20 hidden">
                            <span>
                                {{ trans('sales::view.CSS.Preview.Uncheck all customer') }} 
                                <i class="fa fa-spin fa-refresh hidden"></i>
                            </span>
                        </button>
                    </div>
                </div>
                
            </div>
            
        </form>
    </div>
    @endif
    <!-- /.Button send mail -->
    <hr>
    <div class="body-padding">
  <button type="button" class="btn btn-info btn-update-css btn-show-hide" >{{trans('sales::view.CSS.Preview.Hide preview')}}</button>
@endif
  <div id="preview" >
    @if ($sendMailPermission && $hasPermission)
    <!-- PREVIEW MAIL CONTENT -->
    <div class="welcome-body body-padding{{ $lang == 'vi' ? ' font-viet' : '' }}" @if ($lang == 'vi') style='font-family: "Helvetica Neue",Helvetica,Arial,sans-serif !important' @endif>
        <h3 class="box-title">{{ trans('sales::view.Preview.Preview') }}</h3>
        <div class="row">
            <div class="col-md-6">
                <h4 class="preview-title">{{ trans('sales::view.CSS.Preview.Mail content complete')}}</h4>
                @include('sales::css.customerMail')
            </div>
            <div class="col-md-6">
                <h4 class="preview-title">{{ trans('sales::view.CSS.Preview.Mail content periodically')}}</h4>
                @include('sales::css.email.customerMailPeriodic')
            </div>
        </div>
        <hr>
    </div>
    <!-- /.PREVIEW MAIL CONTENT -->
    @endif
    
    <!-- PREVIEW WELCOME PAGE -->
    <div class="welcome-body body-padding">
        <h3 class="box-title">{{ trans('sales::view.Preview.Preview') }}</h3>
        <h4 class="preview-title">{{ trans('sales::view.Preview.Welcome CSS title', [], '', $lang) }}</h4>
        <div class="logo-rikkei">
            <img src="{{ URL::asset('common/images/logo-rikkei.png') }}">
        </div>
        <div class="welcome-header">
            <h2 class="welcome-title <?php if($css->project_type_id === 1 || $css->project_type_id === 5){ echo 'color-blue'; } ?>">{{ trans('sales::view.Welcome title well',[],'',$lang) }}</h2>
        </div>
        <div class="container-fluid">
            <div class="row-fluid">
                <div class="span12">
                    <div class="<?php if(in_array($lang, ["en", "vi"])) :?> content-well font-viet <?php endif; ?>">
                        {!!trans('sales::view.CSS.Welcome.Content',[],'',$lang)!!}
                    </div>
                </div>
            </div>
            <div class="row-fluid ">
                <div class="css-make-info">
                    <div>
                        <div class="company-name-title <?php if(in_array($lang, ["en", "vi"])) :?> width-200 <?php endif; ?>">{{ trans('sales::view.Customer company name jp',[],'',$lang)}}</div>
                        <div class="company-name inline-block <?php if(in_array($lang, ["en", "vi"])) :?> width-270 <?php endif; ?>">{{ $css->company_name}} @if($lang == "ja") 様 @endif </div>
                    </div>
                    <div style="position: relative">
                        <div class="project-name-title <?php if(in_array($lang, ["en", "vi"])) :?> width-200 <?php endif; ?>">{{ trans('sales::view.Project name jp well',[],'',$lang)}}</div>
                        <div class="project-name inline-block <?php if(in_array($lang, ["en", "vi"])) :?> width-270 <?php endif; ?>">@if(trim($css->project_name_css) != null) {{$css->project_name_css}}  @else {{ $css->project_name}} @endif</div>
                    </div>
                    <div>
                        <div class="customer-name-title margin-top-13 <?php if(in_array($lang, ["en", "vi"])) :?> width-200 <?php endif; ?>">{{ trans('sales::view.Make name jp',[],'',$lang)}}</div>
                        <div class="inline-block <?php if(in_array($lang, ["en", "vi"])) :?> width-270 <?php endif; ?>">
                            <div class="input-group goto-make-parent">
                                <input type="text" class="form-control" id="make_name" name="make_name" readonly="true" value="" maxlength="100" />
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default btn-to-make <?php if($css->project_type_id === 1 || $css->project_type_id === 5){ echo 'bg-color-blue'; } ?>" name="submit"><img src="{{ URL::asset('sales/images/splash.png') }}" /></button>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="clear-both"></div>
    
    <!-- PREVIEW MAKE CSS PAGE -->
    <hr />
    <div class="make-css-page body-padding">
        <h3 class="box-title">{{ trans('sales::view.Preview.Preview') }}</h3>
        <h4 class="preview-title">{{ trans('sales::view.Preview.Make CSS title', [], '', $lang) }}</h4>
        <div class="row">
            <div class="col-md-12">
                <section id="make-header">
                    <div class="logo-rikkei"><img src="{{ URL::asset('common/images/logo-rikkei.png') }}"></div>
                    <h2 class="title <?php if($css->project_type_id === 2){ echo 'title-base'; }?>"
                        {!! $css->project_type_id == 5 ? 'style="font-size: 27px;"' : '' !!}> {{ trans('sales::view.Welcome title'.$transType, [], '', $lang) }} </h2>
                    <div class="total-point-container <?php if($css->project_type_id === 2){ echo 'total-point-container-base'; }?>">
                        <div class="total-point-text">{{ trans('sales::view.Total point',[],'',$lang)}}</div>
                        <div class="total-point <?php if($css->project_type_id === 2){ echo 'total-point-base'; }?>" >00.00</div>
                    </div>
                </section>
                <section>

                    <!-- PROJECT INFORMATION -->
                    <div class="row project-info">
                        <div class="col-xs-12 header <?php if($css->project_type_id === 2){ echo 'header-base'; }?>">{{ trans('sales::view.Project information',[],'',$lang) }}
                            <span id="help" class="help" data-toggle="tooltip" data-placement="top" title='{{trans("sales::view.Title help",[],"",$lang)}}'><i class="fa fa-question-circle" aria-hidden="true"></i></span>
                        </div>
                        <div class="col-xs-12 col-sm-6 padding-left-right-5px <?php if(in_array($lang, ["en", "vi"])) :?>font-viet <?php endif; ?>">
                            <div class="row-info">{{ trans('sales::view.Project name jp',[],'',$lang) }} @if(trim($css->project_name_css) != null) {{$css->project_name_css}}  @else {{ $css->project_name}} @endif</div>
                            <div class="row-info">{{ trans('sales::view.Customer company name jp',[],'',$lang) }} {{ $css->company_name }}
                        @if($lang == "ja") 様 @endif</div>
                        <div class="row-info">{{ trans('sales::view.Make name jp',[],'',$lang)}}</div>
                        </div>
                        <div class="col-xs-12 col-sm-6 padding-left-right-5px <?php if(in_array($lang, ["en", "vi"])) :?>font-viet <?php endif; ?>">
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
                        @foreach($cssCate as $item)
                            @if($item['noCate'] == 1)
                            <div class="col-xs-12 root-cate root-cate-first <?php if($css->project_type_id === 2){ echo 'root-cate-base'; }?>">
                                <span>{{$item["sort_order"] . ". " .$item['name']}}</span>
                                <span class ="help-noti <?php if($css->project_type_id === 1 || $css->project_type_id === 5 && $lang == 'en'){echo 'help-noti-eng';} ?>"><i class="fa "><span>※</span> {{trans('sales::view.Help noti',[],'',$lang)}}</i></span>
                                <div>
                                    <span class="help-noti"> <i class="fa ">{{ trans('sales::view.Help noti 2',[],'',$lang) }}</i> </span>
                                </div>
                            </div>
                            @else
                            <div class="col-xs-12 root-cate <?php if($css->project_type_id === 2){ echo 'root-cate-base'; }?>">
                                <span>{{$item["sort_order"] . ". " .$item['name']}}</span>
                                <span class ="help-noti <?php if($css->project_type_id === 1 || $css->project_type_id === 5 && $lang == 'en'){echo 'help-noti-eng';} ?>"><i class="fa "><span>※</span> {{trans('sales::view.Help noti',[],'',$lang)}}</i></span>
                                <div>
                                    <span class="help-noti"><i class="fa ">{{ trans('sales::view.Help noti 2',[],'',$lang) }}</i></span>
                                </div>
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
                                        @php
                                            $questionChildExplain = isset($questionChild['question_explanation']) ? $questionChild['question_explanation'] : (isset($questionChild['explain']) ? $questionChild['explain'] : null);
                                        @endphp
                                        <div class="row container-question">
                                            <div class="col-xs-12 col-sm-5 question container-question-child"><span class="num-border">{{ $questionChild['sort_order'] }}</span> {{ Str::limit($questionChild['content'], 190) }} @if ($questionChildExplain) <i class="fa fa-question-circle" data-toggle="tooltip" data-html="true" title="{{ View::renderQsExplain($questionChildExplain) }}"></i> @endif </div>
                                            <div class="col-xs-12 col-sm-2 rate container-question-child"><div class="rateit" data-rateit-step='1' data-rateit-resetable="false" data-rateit-readonly="true" data-questionid="{{$questionChild['id']}}" onclick="totalMark(this);" ontouchend="totalMark(this);" ></div></div>
                                            <div class="col-xs-12 col-sm-5 comment container-question-child"><input type="text" readonly="true" class="comment-question form-control" type="text" maxlength="1000" data-questionid="{{$questionChild['id']}}" onkeyup="checkPoint(this);" /></div>
                                        </div>
                                        @endforeach
                                    @endif
                                @endforeach
                            @elseif($item['questions'])
                                @foreach($item['questions'] as $question)
                                @php
                                    $questionExplain = isset($question['question_explanation']) ? $question['question_explanation'] : (isset($question['explain']) ? $question['explain'] : null);;
                                @endphp
                                <div class="row container-question">
                                    <div class="col-xs-12 col-sm-5 question container-question-child"><span class="num-border">{{ $question['sort_order'] }}</span> {{ Str::limit($question['content'], 190) }} @if ($questionExplain) <i class="fa fa-question-circle" data-toggle="tooltip" data-html="true" title="{{ View::renderQsExplain($questionExplain) }}"></i> @endif </div>
                                    <div class="col-xs-12 col-sm-2 rate container-question-child"><div class="rateit" data-rateit-step='1' data-rateit-resetable="false" data-rateit-readonly="true" data-questionid="{{$question['id']}}" onclick="totalMark(this);" ontouchend="totalMark(this);" ></div></div>
                                    <div class="col-xs-12 col-sm-5 comment container-question-child"><input type="text" readonly="true" class="comment-question form-control" type="text" maxlength="1000" data-questionid="{{$question['id']}}" onkeyup="checkPoint(this);" /></div>
                                </div>
                                @endforeach
                            @endif
                        @endforeach

                        <!-- Overview question -->
                        <div class="col-xs-12 root-cate <?php if($css->project_type_id === 2){ echo 'root-cate-base'; }?>">
                            {{$noOverView . ". " .trans('sales::view.General',[],'',$lang) }}
                        </div>
                        <div class="row container-question">
                            <div class="col-xs-12 col-sm-5 question container-question-child">
                                @if ($cssOld) @if ($lang == "ja" || $css->project_type_id == 5) {{ $overviewQuestionContent }} @else {{ trans('sales::view.OverviewQuestionContent OSDC', [], '', $lang) }} @endif @else {{ $overviewQuestionContent }} @endif @if ($overviewQuestionExplain) <i class="fa fa-question-circle" data-toggle="tooltip" data-html="true" title="{{ View::renderQsExplain($overviewQuestionExplain) }}"></i> @endif
                            </div>
                            <div class="col-xs-12 col-sm-2 rate container-question-child"><div id="tongquat" class="rateit" data-rateit-step='1' data-rateit-resetable="false" data-rateit-readonly="true" data-questionid="{{$overviewQuestionId}}" onclick="totalMark(this);" ontouchend="totalMark(this);" ></div></div>
                            <div class="col-xs-12 col-sm-5 comment container-question-child"><input type="text" readonly="true" class="comment-question form-control" type="text" maxlength="1000" data-questionid="{{$overviewQuestionId}}" id="comment-tongquat" onkeyup="checkPoint(this);" /></div>
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
                            <div class="col-xs-12 col-sm-7 comment proposed-comment"><textarea readonly="true" class="proposed form-control" id="proposed" maxlength="2000"></textarea></div>
                        </div> --}}
                        <!-- Button submit -->
                        <div class="col-xs-12 container-submit"><button type="button" class="btn btn-primary <?php if($css->project_type_id === 2){ echo 'button-base'; }?>">{{trans('sales::view.Send',[],'',$lang)}}</button></div>
                    </div>
                    <!-- END PROJECT DETAIL -->


                </section>
            </div>
        </div>
    </div>
  </div>
</div>
<div class="modal modal-success" id="modal-success">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"></span></button>
                <h4>{{trans('sales::message.Success')}}</h4>
            </div>
            <div class="modal-body">
                <p></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline pull-right" data-dismiss="modal">{{ trans('sales::view.CSS.Preview.Close') }}</button>
                
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<div class="modal modal-danger" id="modal-error">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"></span></button>
                <h4>{{trans('sales::view.CSS.Preview.Error!')}}</h4>
            </div>
            <div class="modal-body">
                <p></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline pull-right" data-dismiss="modal">{{ trans('sales::view.CSS.Preview.Close') }}</button>
                
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<div class="modal modal-warning" id="modal-warning">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"></span></button>
                <h4>{{trans('sales::view.CSS.Preview.Warning!')}}</h4>
            </div>
            <div class="modal-body">
                <p></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline pull-right" data-dismiss="modal">{{ trans('sales::view.CSS.Preview.Close') }}</button>
                
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<div class="modal modal-default" id="modal-confirm">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"></span></button>
                <h4>{{trans('sales::view.CSS.Preview.Confirm!')}}</h4>
            </div>
            <div class="modal-body">
                <div class="form-group mb-25"></div>
                <p></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn pull-left" data-dismiss="modal">{{ trans('sales::view.CSS.Preview.Cancel') }}</button>
                <button type="button" id="send-mail-confirm" class="btn btn-primary pull-right" data-dismiss="modal">{{ trans('sales::view.CSS.Preview.Submit') }}</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<!-- modal confirm delete email css -->
<div class="modal modal-warning" id="modal-confirm-delete-email">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"></span></button>
                <h4>{{trans('sales::view.CSS.Preview.Confirm!')}}</h4>
            </div>
            <div class="modal-body">
                <p></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline pull-left" data-dismiss="modal">{{ trans('sales::view.CSS.Preview.Cancel') }}</button>
                <button type="button" id="delete-mail-confirm" class="btn btn-outline pull-right" data-dismiss="modal">{{ trans('sales::view.CSS.Preview.Delete') }}</button>
                <input class="button-confirm-delete" type="hidden" name="idEmail" value="" />
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
@endsection
<!-- Styles -->
@section('css')
<link href="{{ CoreUrl::asset('sales/css/sales.css') }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/minimal/_all.css" />
<link href="{{ asset('lib/rateit/rateit.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('lib/css/inputTags.css') }}" rel="stylesheet" type="text/css" />
@include('sales::css.css_tooltip');
@endsection

<!-- Script -->
@section('script')
<script>

var urlSubmit = '{{route("sales::css.sendMailCustomer")}}';
var urlSave = '{{route("sales::css.saveCssMail")}}';
var urlSubmitDelete = '{{route("sales::deleteMail")}}';
var token = '{{Session::token()}}';
var requiredField = '{{trans("sales::view.CSS.Preview.Required field")}}';
var cssId = {{$css->id}};
var resendText = '{{ trans("sales::view.CSS.Preview.Resend mail to customer") }} ';
var sendMailError = {{ CssMail::MAIL_ERROR }};
var email_exist = '{{ trans("sales::view.CSS.Preview.Email is exist") }}';
var email_invalid = '{{ trans("sales::view.CSS.Preview.Email is invalid") }}';
var sendErrorText = '{{ trans("sales::message.Send mail to customer error.") }}';
var checkboxRequired = '{{ trans("sales::view.Dont have any checkbox checked.") }}';
var sureSendMail = '{{ trans("sales::view.Are you sure?") }}';
var sureDeleteMail = '{{ trans("sales::view.Are you delete sure?") }}';
var sendMailSuccess = '{{ trans('sales::message.Send mail to customer success.') }}';
var deleteMailSuccess = '{{ trans('sales::message.Delete mail to customer success.') }}';
var showText = '{{trans('sales::view.CSS.Preview.Show preview')}}';
var hideText = '{{trans('sales::view.CSS.Preview.Hide preview')}}';
var lastSendText = '{{trans('sales::view.Last send: ')}}';
var customer_email = '{{$projectOfCss->cus_email ? $projectOfCss->cus_email : ''}}';
var customer_name = '{{$projectOfCss->cus_contact? $projectOfCss->cus_contact : ''}}';
</script>
<script src="{{ asset('lib/rateit/jquery.rateit.js') }}"></script>
<script src="{{ asset('common/js/script.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/icheck.min.js"></script>
<script src="{{ asset('sales/js/css/preview.js') }}"></script>
<script src="{{ asset('lib/js/inputTags.jquery.min.js') }}"></script>
<script>
    var customerName = $('.customer-name').val();
    var customerEmail = $('.customer-email').val();
    if (!customerName) {
        $('.customer-name').val(customer_name);
    }
    if (!customerEmail) {
        $('.customer-email').val(customer_email);
    }
</script>
@endsection
