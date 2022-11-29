<?php 
use Rikkei\Sales\View\View;
use Rikkei\Sales\Model\CssProjectType;
use Rikkei\Core\View\CoreUrl;

if ($save == 'create') {
    $title = trans('sales::view.Create CSS title');
    $detail = false;
} else {
    if ($type && $type == 'detail') { // detail page
        $title = trans('sales::view.Detail CSS title');
        $detail = true;
    } else { // update page
        $title = trans('sales::view.Update CSS title');
        $detail = false;
    }
}

?>

@extends('layouts.default')

@section('title')
    {{ $title }}
@endsection

@section('content')

<div class="box box-primary css-create-page">
    <div class="css-create-body form-horizontal">
       <!--<form id="frm_create_css" method="post" action="{{ route('sales::css.save') }}"  >-->
            <input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />
            @if($save === 'create')
            <input type="hidden" id="create_or_update" value="create">
            <input type="hidden" id="css_id" value="">
            @else
            <input type="hidden" id="create_or_update" value="update">
            <input type="hidden" id="css_id" value="{{$css->id}}">
            @endif
            <div class="row">
            <!-- LEFT COLUMN -->
                <div class="col-md-6">
                    <div class="form-group form-label-left">
                        <label for="set_pj" class="col-sm-3 control-label">{{ trans('sales::view.CSS.Create.Project') }} <em class="required">*</em>
                        </label>
                        <div class="col-sm-9">
                            <select class="form-control" id="set_pj" name="set_pj" onchange="setTeam('{{ Session::token() }}');" <?php if ($detail) echo 'disabled'; ?>>
                                <option value="0">{{trans('sales::view.CSS.Create.Set project')}}</option>
                                @if($projects && count($projects) > 0)
                                    @foreach($projects as $item)
                                        <option value="{{$item->id}}" <?php if($css->projs_id == $item->id){ echo 'selected';} ?>>{{$item->name}}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="form-group form-label-left">
                        <label class="col-sm-3 control-label tooltip-group">{{trans('sales::view.Create.Project name css')}}
                        <span class="tooltip">{{ trans('sales::view.CSS.Create.Project name css tooltip') }}</span>
                        </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="project_name_css" name="project_name_css" maxlength="200" placeholder="Project name" value="{{$css->project_name_css}}" <?php if ($detail) echo 'disabled'; ?>>
                        </div>
                    </div>
                    <div class="form-group form-label-left">
                        <label class="col-sm-3 control-label">{{ trans('sales::view.Create.Project code') }} </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="project_code" name="project_code" maxlength="200" placeholder="{{ trans('sales::view.Create.Project code') }}" value="{{$css->project_code}}" >
                        </div>
                    </div>
                    <div class="form-group form-label-left">
                        <label class="col-sm-3 control-label">{{ trans('sales::view.Create.Customer company') }} </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="company_name" name="company_name" maxlength="200" placeholder="{{ trans('sales::view.Customer company name') }}" value="{{$css->company_name}}" >
                            <label class="sama_label">様</label>
                        </div>
                    </div>
                    <div class="form-group form-label-left">
                        <label class="col-sm-3 control-label">{{ trans('sales::view.Create.Customer') }} </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="customer_name" name="customer_name"  maxlength="100" placeholder="{{ trans('sales::view.Customer name') }}" value="{{$css->cus_name ? $css->cus_name : ''}}{{$css->cus_email ? ' ('.$css->cus_email.')' : ''}}" >
                            <label class="sama_label">様</label>
                        </div>
                    </div>
                    <div class="form-group form-label-left">
                        <label class="col-sm-3 control-label" for="project_type">{{ trans('sales::view.Project type') }} </label>
                        <div class="col-sm-9">
                            <label class="control-label project_type" >{{$css->project_type_id ? CssProjectType::getTextById($css->project_type_id) : ''}}</label>
                        </div>
                    </div>
                    <div class="form-group form-label-left position-relative date-container">
                        <label class="col-sm-3 control-label" for="start_date">{{ trans('sales::view.Project date') }} <em class="required">*</em></label>
                        <div class="col-sm-9">
                            <div class="col-xs-12 col-sm-6 padding-0" >
                                <div class="input-group "> 
                                    <span class="input-group-btn"> 
                                        <button class="btn btn-default" type="button"><i class="fa fa-calendar"></i></button> 
                                    </span> 
                                    <input type='text' class="form-control date start-date" id="start_date" name="start_date" data-provide="datepicker" placeholder="YYYY/MM/DD" tabindex=1 value="{{$css->start_date}}" <?php if ($detail) echo 'disabled'; ?> />
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-6 padding-0" >
                                <div class="input-group ">
                                    <span class="input-group-btn"> 
                                        <button class="btn btn-default" type="button"><i class="fa fa-calendar"></i></button></i></button> 
                                    </span> 
                                    <input type='text' class="form-control date end-date" id="end_date" name="end_date" data-provide="datepicker" placeholder="YYYY/MM/DD" tabindex=2 value="{{$css->end_date}}" <?php if ($detail) echo 'disabled'; ?> />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group form-label-left lang-row">
                        <label class="col-sm-3 control-label">{{ trans('sales::view.Language') }}</label>
                        <div class="col-sm-9">
                            <div class = "col-xs-12 col-sm-4">
                                <label class="control-label"><input type="radio" name="lang"  {{ $css && $css->lang_id == 0 ? 'checked' : '' }} value="0" <?php if ($detail) echo 'disabled'; ?>> {{ trans('sales::view.Japan') }}</label>
                            </div>
                            <div class="col-xs-12 col-sm-4">
                                <label class="control-label"><input type="radio" name="lang" {{ $css && $css->lang_id == 1 ? 'checked' : '' }} value="1" <?php if ($detail) echo 'disabled'; ?>> {{ trans('sales::view.English') }}</label>
                            </div>
                            <div class="col-xs-12 col-sm-4 extra-langs">
                                <label class="control-label"><input type="radio" name="lang" {{ $css && $css->lang_id == 2 ? 'checked' : '' }} value="2" {{ $detail ? 'disabled' : '' }}> {{ trans('sales::view.Vietnamese') }}</label>
                            </div>
                            {{-- <div class="col-xs-12 text-language"><i>{{ trans('sales::view.Note select language') }}</i></div> --}}
                        </div>
                    </div>                    
                </div>
                
            <!-- RIGHT COLUMN -->
                <div class="col-md-6">
                    <div class="form-group form-label-left">
                        <label for="pm_email" class="col-sm-3 control-label" >{{ trans('sales::view.Create.PM') }} </label>
                        <div class="col-sm-9">
                            <input type="text" data-name='pm_email' name="pm_email" class="form-control" data-length='1' id="pm_email"  maxlength="100" placeholder="{{ trans('sales::view.Create.PM email') }} " value="{{$css->pm ? $css->pm : ''}}" >
                        </div>
                    </div>
                    <div class="form-group form-label-left">
                        <label for="pm_email_jp" class="col-sm-3 control-label tooltip-group" >
                            {{ trans('sales::view.Create.PM name jp') }} 
                            <em class="required">*</em>
                            <span class="tooltip">{{ trans('sales::view.CSS.Create.PM jp tooltip') }}</span>
                        </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="pm_email_jp" name="pm_name_jp" tabindex=3  maxlength="255" placeholder="{{ trans('sales::view.Create.PM name jp') }} " value="{{$css->pm_name_jp}}" <?php if ($detail) echo 'disabled'; ?> >
                        </div>
                    </div>
                    <div class="form-group form-label-left">
                        <label for="japanese_name" class="col-sm-3 control-label tooltip-group">
                            {{ trans('sales::view.Create.Sale name jp') }} 
                            <em class="required">*</em>
                            <span class="tooltip">{{ trans('sales::view.CSS.Create.Sale jp tooltip') }}</span>
                        </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control  input-field" id="japanese_name" name="sale_name_jp" value="{{ $css->sale_name_jp }}" tabindex=4 maxlength="100" placeholder="{{ trans('sales::view.Create.Sale name jp') }}" <?php if ($detail) echo 'disabled'; ?> >
                        </div>
                    </div>
                    <div class="form-group form-label-left position-relative date-container onsite-date-container
                         {{ $save == 'create' || ($save == 'update' && $css->project_type_id != 5) ? ' hidden' : '' }}">
                        <label class="col-sm-3 control-label">{{ trans('sales::view.Onsite date') }} <em class="required">*</em></label>
                        <div class="col-sm-9">
                            <div class="col-xs-12 col-sm-6 padding-0" >
                                <div class="input-group "> 
                                    <span class="input-group-btn"> 
                                        <button class="btn btn-default" type="button"><i class="fa fa-calendar"></i></button> 
                                    </span> 
                                    <input type='text' autocomplete="off" class="form-control date start-onsite-date" id="start_onsite_date" name="start_onsite_date" data-provide="datepicker" placeholder="YYYY/MM/DD" tabindex=1 value="{{$css->start_onsite_date}}" <?php if ($detail) echo 'disabled'; ?> />
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-6 padding-0" >
                                <div class="input-group ">
                                    <span class="input-group-btn"> 
                                        <button class="btn btn-default" type="button"><i class="fa fa-calendar"></i></button></i></button> 
                                    </span> 
                                    <input type='text' autocomplete="off" class="form-control date end-onsite-date" id="end_onsite_date" name="end_onsite_date" data-provide="datepicker" placeholder="YYYY/MM/DD" tabindex=2 value="{{$css->end_onsite_date}}" <?php if ($detail) echo 'disabled'; ?> />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group form-label-left margin-bottom-0">
                        <label for="rikker_relate" class="col-sm-3 control-label tooltip-group" >
                            {{ trans('sales::view.Create.People relate') }} 
                            <em class="required">*</em>
                            <span class="tooltip">{{ trans('sales::view.CSS.Create.Will recevie email bcc') }}</span>
                        </label>
                        <div class="col-sm-9">
                            <div class="rikker-relate-container">
                                <div class="rikker-set <?php if(count($rikker_relate) > 0){echo 'display-inline';} ?>" data-for='rikker_relate' flag="unValidate">
                                    @if(count($rikker_relate) > 0)
                                        @foreach($rikker_relate as $rikker)
                                            <span class="vN bfK a3q" email="{{$rikker['email']}}">
                                                <div class="vT">{{$rikker['name']}}</div>
                                                @if (!$detail)
                                                <div class="vM" data-remove="{{$rikker['email']}}" data-for="rikker_relate" onclick="removeRikker(this);"></div>
                                                @endif
                                            </span>
                                        @endforeach
                                    @endif
                                </div>
                                
                                <input type="text" data-name='rikker_relate' autocomplete="off" class="form-control <?php if(count($rikker_relate) > 0) echo 'rikker-relate-update' ?>" data-length='max' id="rikker_relate"  tabindex=5  maxlength="100" placeholder="{{ trans('sales::view.Create.People relate') }}" value="" <?php if ($detail) echo 'style="opacity: 0; visible: hidden;"'; ?> >
                                <input id="rikker_relate_check" name="rikker_relate_check" type="text" value="<?php echo ($save == 'create') ? '' : 1 ?>" style="visibility: hidden; position: absolute;">
                                <input id="rikker_relate_validate" name="rikker_relate_validate" type="text" value="1" style="visibility: hidden; position: absolute;">
                                <div class="rikker-result" data-for='rikker_relate'></div>

                                @if(count($rikker_relate) > 0)
                                    @foreach($rikker_relate as $rikker)
                                        <input type="hidden" data-for="rikker_relate" name="rikker_relate[]" value="{{$rikker['email']}}" />
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div> 
                    <div class="form-group form-label-left">
                        <label class="col-sm-3 control-label" >{{ trans('sales::view.CSS.Create.Team relate') }}</label>
                        <div class="col-sm-9">
                            <input id="team_relate" type="text" class="form-control"  maxlength="100" 
                               placeholder="{{ trans('sales::view.CSS.Create.Team relate') }} " value="<?php if($save === 'update'){ echo $strTeamsNameSet; } ?>" >
                            <input id="team_ids" class="team_id" type="hidden" name="teams" value="<?php if($save === 'update') { echo $strTeamIds; } ?>" />
                        </div>
                    </div>
                    <div class="form-group form-label-left">
                        <label for="name" class="col-sm-3 control-label">{{ trans('sales::view.Create.Creator') }}</label>
                        <div class="col-sm-9">
                            <input type="hidden" id="employee_id" name="employee_id" value="{{$employee->id}}">
                            <input type="text" class="form-control input-field" id="employee_name" name="employee_name" value="{{$employee->name}} ({{View::getAccName($employee->email)}})" disabled="disabled" placeholder="{{ trans('sales::view.Create.Sale name') }}">
                        </div>
                    </div>
                    <div class="form-group form-label-left">
                        <label for="css_creator_name" class="col-sm-3 control-label">{{ trans('sales::view.Create.Creator JP') }}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control input-field" id="css_creator_name" name="css_creator_name" value="{{$css->css_creator_name ? $css->css_creator_name : ''}}" placeholder="{{ trans('sales::view.Create.Creator JP') }}" <?php if ($detail) echo 'disabled'; ?>>
                        </div>
                    </div>
                    <div class="form-group form-label-left">
                        <label for="time_reply" class="col-sm-3 control-label">{{ trans('sales::view.Time customer reply') }}</label>
                        <div class="col-sm-9">
                            <div class="input-group ">
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="button"><i class="fa fa-calendar"></i></button>
                                </span>
                                <input type='text' class="form-control date time_reply" id="time_reply" name="time_reply" data-provide="datepicker" placeholder="YYYY/MM/DD" tabindex=1 value="{{$css->time_reply ? $css->time_reply : ''}}" <?php if ($detail) echo 'disabled'; ?> />
                            </div>
                        </div>
                    </div>
                    
                </div>
                
                <!-- Button submit -->
                @if (!$detail)
                <div class="col-md-12 text-align-center " >
                    <button class="btn btn-primary btn-create" type="button" >
                        @if($save === 'create')
                            {{ trans('sales::view.Create CSS') }}
                        @else
                            {{ trans('sales::view.Update CSS') }}
                        @endif
                        <i class="fa fa-spin fa-refresh hidden"></i>
                    </button>
                </div>
                @endif
                <!-- ./Button submit -->
            </div>
        <!--</form>-->  
        @if ($detail)
        <div class="row">
            <div class="col-md-6 margin-top--15 margin-top--15-md">
                <div class="form-group form-label-left">
                    <label class="col-sm-3 control-label">{{ trans('sales::view.CSS.Create.Total view CSS') }} </label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control input-field" value="{{$totalViewCss}}" disabled="disabled" >
                    </div>
                </div>
            </div>
            <div class="col-md-6 margin-top--15-md">
                <div class="form-group form-label-left">
                    <label class="col-sm-3 control-label">{{ trans('sales::view.CSS.Create.Total make CSS') }} </label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control input-field" value="{{$totalMakeCss}}" disabled="disabled" >
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 margin-top--15 margin-top--15-md">
                <div class="form-group form-label-left">
                    <label class="col-sm-3 control-label">{{ trans('sales::view.CSS.Create.Url preview') }} </label>
                    <div class="col-sm-9">
                        <a class="font-size-14px margin-top-6 display-inline-block break-word" href="{{$previewUrl}}" target="_blank">{{$previewUrl}}</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 margin-top--15-md">
                <div class="form-group form-label-left">
                    <label class="col-sm-3 control-label">{{ trans('sales::view.CSS.Create.Url welcome') }} </label>
                    <div class="col-sm-9">
                        <a class="font-size-14px margin-top-6 display-inline-block break-word" href="{{$welcomeUrl}}" target="_blank">{{$welcomeUrl}}</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 margin-top--15 margin-top--15-md">
                <div class="form-group form-label-left">
                    <label class="col-sm-3 control-label">{{ trans('sales::view.Links Project Report') }} </label>
                    <div class="col-sm-9">
                        <a class="font-size-14px margin-top-6 display-inline-block break-word" href="{{ $workOrderUrl }}" target="_blank">{{ $workOrderUrl }}</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 margin-top--15-md">
                <div class="form-group form-label-left">
                    <label class="col-sm-3 control-label">{{ trans('sales::view.Links View workorder') }} </label>
                    <div class="col-sm-9">
                        <a class="font-size-14px margin-top-6 display-inline-block break-word" href="{{ $projectReportUrl }}" target="_blank">{{ $projectReportUrl }}</a>
                    </div>
                </div>
            </div>
        </div>


        @endif
        <!-- CSS mail address list -->
        @if ($detail)
        <div class="row margin-top-30">
            <div class="col-md-6">
                <h3>{{ trans('sales::view.CSS.Create.Email list send to customer') }}</h3>
                <table class="table table-bordered table-hover dataTable t-table" style="width: 100%">
                    <thead>
                        <tr>
                            <th>{{ trans('sales::view.CSS.Create.No.') }}</th>
                            <th>{{ trans('sales::view.CSS.Create.Customer name') }}</th>
                            <th>{{ trans('sales::view.CSS.Create.Customer email address') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (count($cssMail))
                            @foreach ($cssMail as $key => $item)
                            <tr>
                                <td>{{ ++$key }}</td>
                                <td>{{$item->name}}</td>
                                <td>{{$item->mail_to}}</td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="col-md-6">
                <h3>Lịch sử làm CSS</h3>
                <table class="table table-bordered table-hover dataTable t-table" style="width: 100%">
                    <thead>
                        <tr>
                            <th>{{ trans('sales::view.CSS.Create.No.') }}</th>
                            <th>{{ trans('sales::view.date work css') }}</th>
                            <th>{{ trans('sales::view.custumer work css') }}</th>
                            <th>{{ trans('sales::view.Email work css') }}</th>
                            <th>{{ trans('sales::view.point work css') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (count($listCssWork))
                            @foreach ($listCssWork as $keyWork => $valueWork)
                            <tr>
                                <td>{{ ++$keyWork }}</td>
                                <td>{{$valueWork->created_at}}</td>
                                <td>{{$valueWork->name}}</td>
                                <td>{{$valueWork->email_cus}}</td>
                                <td>{{$valueWork->avg_point}}</td>

                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        @endif
         <!-- ./CSS mail address list -->
    </div>
    
</div>
@include('sales::css.include.no_customer_modal')
<input id="token" type="hidden" value="{{ Session::token() }}" />
<input type="hidden" id="refreshed" value="no">
@endsection

<!-- Styles -->
@section('css')
<link href="{{ CoreUrl::asset('sales/css/sales.css') }}" rel="stylesheet" type="text/css" >
<link href="{{ CoreUrl::asset('sales/css/customer_create.css') }}" rel="stylesheet" type="text/css" >
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/minimal/_all.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
@endsection

<!-- Script -->
@section('script')
<script>
    var requiredProj = '{{ trans('sales::message.Please select a project') }}';
    var requiredName = '{{ trans('sales::message.Please enter a name') }}';
    var requiredRelPerson = '{{ trans('sales::message.Please select a related person') }}';
    var requiredEmail = '{{ trans('sales::message.Please enter email') }}';
    var requiredStartDate = '{{ trans('sales::message.Please enter project start date') }}';
    var requiredEndDate = '{{ trans('sales::message.Please enter project end date') }}';
    var requiredTimeReply = '{{ trans('sales::message.Please enter project time reply') }}';
    var requiredEndOnsite = '{{ trans('sales::message.Please enter onsite end date') }}';
    var requiredStartOnsite = '{{ trans('sales::message.Please enter onsite start date') }}';
    var token = '{{ csrf_token() }}';
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.valaidate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/icheck.min.js"></script>
<script src="{{ CoreUrl::asset('common/js/box.email.js') }}"></script>
<script src="{{ CoreUrl::asset('sales/js/css/create.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        @if (isset($cust) && !$cust)
            $('#modal-no-customer').modal('show');
        @endif
        $('input[type=radio][name=project_type_id]').change(function () {
            if (this.value == '1') {
                $(".project_name").text('<?php echo trans('sales::view.Project OSDC name') ?>');
            } else if (this.value == '2') {
                $(".project_name").text('<?php echo trans('sales::view.Project base name') ?>');
            }
        });

        $('#time_reply').datepicker({
            format: 'yyyy-mm-dd'
        })

        $('#set_pj').on('change', function () {
            var projectId = $(this).val();
            $.ajax({
                url: '{{ route('sales::ajax_get.pm_and_sales') }}',
                method: "POST",
                data: {
                    _token: token,
                    projectId: projectId,
                },
                success: function(data) {
                    $('#pm_email_jp').val(data.pm);
                    $('#japanese_name').val(data.sale);

                }
            });
        });
    });
</script>
<script type="text/javascript">
    <?php if($save === 'update'){ ?>
        var teamArray = []; 
        <?php foreach($teamsSet as $team): ?>
          teamArray.push([<?php echo $team->id ?>, '<?php echo $team->name ?>']); 
        <?php endforeach; ?>
    <?php } ?>
    
</script>
<script>
$('#set_pj').select2();
var emailInvalid = '{{trans("team::messages.Email invalid")}}';
var emailFormat = '{{trans("sales::message.Email validate address")}}'
</script>
<script type="text/javascript">
    //If click back button of browser then reload page
    onload=function(){
        var e=document.getElementById("refreshed");
        if (e.value=="no") e.value="yes";
        else {e.value="no";$('#rikker_relate_check').val('');location.reload();}
    }

</script>
@endsection