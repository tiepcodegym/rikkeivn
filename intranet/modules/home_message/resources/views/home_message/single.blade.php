@extends('layouts.default')

@section('title')

@endsection
<?php
use Carbon\Carbon;
use Rikkei\Core\Http\Requests\Request;
use Rikkei\Core\View\CoreUrl;
use Rikkei\HomeMessage\Model\HomeMessage;
use Rikkei\HomeMessage\Model\HomeMessageGroup;
use Rikkei\HomeMessage\Helper\Constant;
use Rikkei\HomeMessage\Helper\Helper;

$id = request()->segment(3);
$teamIds = old('team_id', Helper::valueParser($id, $collection, false, [], $collection->getTeamId()));

$messageVI = old('message_vi', Helper::valueParser($id, $collection, 'message_vi'));
$messageEN = old('message_en', Helper::valueParser($id, $collection, 'message_en'));
$messageJP = old('message_jp', Helper::valueParser($id, $collection, 'message_jp'));

$iconUrl = old('icon_url', Helper::valueParser($id, $collection, 'icon_url'));
$groupId = old('group_id', Helper::valueParser($id, $collection, 'group_id'));
$startAt = old('start_at', Helper::valueParser($id, $collection, 'start_at'));
$endAt = old('end_at', Helper::valueParser($id, $collection, 'end_at'));
$allWeekOfDay = Constant::dayOfWeek();
$dateApplyOld = $collection->homeMessageDay;
$dateApply = old('txt_date_apply', Helper::valueParser($id, $dateApplyOld, 'permanent_day'));
$oldWeekDays = old('week_days', Helper::valueParser($id, $collection, false, [], $collection->homeMessageDayOfWeek()));

$actionRoute = $id == 0 ? route('HomeMessage::home_message.insert-home-message') : route('HomeMessage::home_message.update-home-message', $id);
function getGroupName($group)
{
    if (!is_object($group)) {
        return '';
    }
    $name = '';
    if (trim($group->name_vi) != '') {
        $name = $name != '' ? $name . ' - ' . $group->name_vi : $group->name_vi;
    }
    if (trim($group->name_en) != '') {
        $name = $name != '' ? $name . ' - ' . $group->name_en : $group->name_en;
    }
    if (trim($group->name_jp) != '') {
        $name = $name != '' ? $name . ' - ' . $group->name_jp : $group->name_jp;
    }
    return htmlentities($name);
}
?>

@section('css')
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css"/>
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css"/>
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_managetime/css/common.css') }}"/>

    <style>
        .icon-old ul {
            margin: 0;
            padding: 0;
        }

        .icon-old ul li {
            float: left;
            list-style-type: none;
            margin: 5px;
        }

        .icon-old ul li img {
            max-height: 30px;
            border: solid 1px #3333;
            /*opacity: 0.5;*/
        }

        .icon-old ul li {
            float: left;
            list-style-type: none;
            margin: 5px;
        }

        .icon-old ul li.active {
            opacity: 1 !important;
            background: red !important;
        }

        .select2.select2-container {
            width: 100% !important;
        }

        select.scheduler-repeat option.weekday_0, select.scheduler-repeat option.weekday_6 {
            background: #afa5a5;
        }

        #day-apply option {
            display: block;
        }

        #day-apply option.ignore {
            display: none !important;
        }

        #day-ignore option {
            display: none;
        }

        #day-ignore option.ignore {
            display: block !important;
        }

        .icon-old ul {
            padding: 0;
            margin: 0;
            overflow: hidden;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="row">
            <!-- Menu left -->
            <div class="col-lg-2 col-md-3">
                @include('HomeMessage::include.menu_left')
            </div>
            <!-- /.col -->
            <div class="col-lg-10 col-md-9">
                <div class="box box-primary row" style="padding-bottom: 20px !important;">
                    <h3 style="text-align: center">{{ $id == 0 ? trans('HomeMessage::view.Single home message') : trans('HomeMessage::view.Single update home message') }}</h3>
                    <div class="clearfix" style="height: 50px"></div>
                    <div class="col-md-6 col-md-offset-3">
                        <form action="{{$actionRoute}}" method="post" id="frmMain" class="form-horizontal" autocomplete="off" id="form-register">
                            {!! csrf_field() !!}

                            <div class="row">
                                <div class="form-group">
                                    <label class="col-md-3 control-label required">{{ trans('HomeMessage::view.Chosen team') }}
                                        <em>*</em></label>
                                    <div class="col-md-9">
                                        <div class='input-box'>
                                            <select class="has-search form-control"
                                                    style="width:100%;min-height:200px" data-flag-dom="select2"
                                                    id="team_id"
                                                    name="team_id[]" multiple="multiple">
                                                @foreach($teamsOption as $option)
                                                    <option {{in_array((int)$option['value'], $teamIds) ? 'selected' : ''}} value="{{ $option['value'] }}">
                                                        {{ Helper::BODParser($option['label']) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!--Start message-->
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-md-3 control-label required">{{ trans('HomeMessage::view.Home message') }}
                                        <em>*</em></label>
                                    <div class="col-md-9">
                                        <label class="control-label"><i>{{trans('HomeMessage::view.001')}}</i></label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label"> (vi)</label>
                                    <div class="col-md-9">
                                        <div class='input-box'>
                                            <input type="text" id="message_vi" class="form-control"
                                                   name="message_vi" value="{{$messageVI}}" maxlength="255"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">
                                        (en)</label>
                                    <div class="col-md-9">
                                        <div class='input-box'>
                                            <input type="text" id="message_en" class="form-control"
                                                   name="message_en" value="{{$messageEN}}" maxlength="255"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">
                                        (jp)</label>
                                    <div class="col-md-9">
                                        <div class='input-box'>
                                            <input type="text" id="message_jp" class="form-control"
                                                   name="message_jp" value="{{$messageJP}}" maxlength="255"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Message -->

                            <div class="row box-upload-file">
                                <div class="form-group">
                                    <label class="col-md-3 control-label required">{{ trans('HomeMessage::view.Icon') }}
                                        <em> size[100px, 100px]</em></label>
                                    <div class="col-md-3">
                                        <div class='input-box'>
                                            <label style="padding-right: 20px" for="icon_url" class="btn btn-default"><i class="fa fa-upload"></i>
                                                Tải icon lên</label>
                                            <input type="file" id="icon_url" style="display:none;"
                                                   width="10px" accept="image/*"
                                                   onchange="readURL(this)"
                                                   name="icon_url">
                                        </div>
                                    </div>

                                    <input type="hidden" id="icon_url_old" name="icon_url_old" value="{{$iconUrl}}">
                                    <img src="{{$iconUrl}}"
                                         style="max-height: 30px;width: 30px; border: solid 1px #3333; margin-top: 2px"/>

                                </div>
                            </div>
                            <?php if(isset($allIconOld) && count($allIconOld) > 0) :?>
                            <div class="row icon-old">
                                <div class="form-group">
                                    <label class="col-md-3 control-label required">{{ trans('HomeMessage::view.Icon old') }}</label>
                                    <div class="col-md-9" style=" ">
                                        <ul style="border: dotted 1px #9b9696;max-height: 125px;overflow-y: auto;">
                                            @foreach($allIconOld as $imgKey => $imgUrl)
                                                <li class="{{$iconUrl == $imgUrl ? 'active':''}}">
                                                    <img src="{{$imgUrl}}"/>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>

                                </div>
                            </div>
                        <?php endif;?>
                        <!-- End group url -->

                            <div class="row">
                                <div class="form-group">
                                    <label class="col-md-3 control-label required">{{ trans('HomeMessage::view.Group category') }}
                                        <em>*</em></label>
                                    <div class="col-md-9">
                                        <div class='input-box'>
                                            <select name="group_id" id="group_id" class="form-control required"
                                                    onchange="onGroupHandleChange(this)">
                                                @foreach($allGroup as $groupInfo)
                                                    <option {{$groupId == $groupInfo->id ? 'selected' :''}}
                                                            value="{{$groupInfo->id}}">{{getGroupName($groupInfo)}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row" id="daysOfWeek" style="display: {{$groupId == Constant::HOME_MESSAGE_GROUP_DISPLAY_DEFINED_TIME_IN_WEEK ? 'block' : 'none'}}">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('HomeMessage::view.Select days of week') }}</label>
                                    <div class="col-md-9">
                                        <div class="day-box">
                                            <div class="checkbox">
                                                <label style="font-weight: 700;">
                                                    <input type="checkbox"
                                                           name="week_days_all"
                                                           id="week_days_all"
                                                           value="all"
                                                           {{count($oldWeekDays) == count(Constant::dayOfWeek()) ? 'checked' : ''}}
                                                           onchange="onWeekDayAllHandleChange(this)">
                                                    {{ trans('HomeMessage::view.Select All') }}
                                                </label>
                                            </div>
                                            <div class="day-list">
                                                @foreach(Constant::dayOfWeek() as $key => $day)
                                                    <div class="checkbox">
                                                        <label>
                                                            <input type="checkbox"
                                                                   name="week_days[]"
                                                                   value="{{$key}}"
                                                                   {{in_array($key, $oldWeekDays) ? 'checked' : ''}}
                                                                   onchange="onWeekDayHandleChange(this)">
                                                            {{$day}}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Start schedule config -->
                            <div class="box-scheduler-config">
                                <!-- Start box-scheduler-config-only -->
                                <div class="row box-scheduler-config-only"
                                     style="display: {{$groupId == Constant::HOME_MESSAGE_GROUP_DISPLAY_DEFINED_TIME_IN_WEEK || $groupId == Constant::HOME_MESSAGE_GROUP_BIRTHDAY ? 'none' : 'block'}}"
                                     id="pickOneDay">
                                    <div class="form-group">
                                        <label class="col-md-3 control-label required">
                                            {{trans('HomeMessage::view.Chọn ngày hiển thị')}}
                                            <em id="txt_date_apply_required" style="display: {{$groupId == Constant::HOME_MESSAGE_GROUP_PRIORITY || empty($groupId) ? 'inline-block' : 'none'}}">*</em>
                                        </label>
                                        <div class="col-md-9">
                                            <label class="control-label">
                                                <i>{{trans('HomeMessage::view.Vui lòng nhập ngày cụ thể (d-m-Y) hoặc nhập ngày tháng định kỳ hàng năm (d-m)')}}</i>
                                            </label>
                                        </div>
                                        <div class="">
                                            <label class="col-md-3"></label>
                                            <div class="col-md-9" style="">
                                                <div class='input-group date' id='datepicker_date_apply'>
                                                    <input type='text' class="form-control" name="txt_date_apply" id="txt_date_apply"
                                                           {{$groupId == Constant::HOME_MESSAGE_GROUP_PRIORITY || empty($groupId) ? 'required="required"' : ''}}
                                                           value="{{ $dateApply }}"/>
                                                    <span class="input-group-addon">
                                                        <span class="glyphicon glyphicon-calendar"></span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- End box-scheduler-config-only -->

                                <!-- Start between time apply -->
                                <div class="row">
                                    <div class="form-group">
                                        <label class="col-md-3 control-label required">{{trans('HomeMessage::view.Khung giờ áp dụng')}}
                                            <em>*</em></label>
                                        <div class="input-box col-md-2">
                                            <div class='input-group date' id='datepicker_start_at'>
                                                <input type='text' class="form-control" name="start_at"
                                                       data-date-format="HH:mm" id="start_at"
                                                       required
                                                       value="{{$startAt}}"/>
                                            </div>
                                        </div>
                                        <div class="input-box col-md-1"><label
                                                    class="control-label required">{{trans('HomeMessage::view.đến')}}</label>
                                        </div>
                                        <div class="input-box col-md-2">
                                            <div class='input-group date' id='datepicker_end_at'>
                                                <input type='text' class="form-control" name="end_at"
                                                       data-date-format="HH:mm" id="end_at"
                                                       required
                                                       value="{{$endAt}}"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- End between time apply -->
                            </div>
                            <!-- End schedule config -->

                            <div class="row text-center margin-top-50 margin-bottom-50">
                                <button type="button" onclick="window.history.back()" style="width: 150px;margin-right: 15px"
                                        class="btn btn-default">{{trans('HomeMessage::view.GoBack')}}</button> &nbsp;&nbsp;
                                @if($id != 0)
                                    <button id="btn-update-home-message" type="submit"
                                            style="width: 150px"
                                            class="btn btn-success">{{trans('HomeMessage::view.Update')}}</button>
                                @else
                                    <button id="btn-create-home-message" type="submit"
                                            style="width: 150px"
                                            class="btn btn-success"> {{trans('HomeMessage::view.Create')}}</button>
                                @endif

                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- /.col -->
        </div>
        <!-- /.col -->
    </div>
@endsection
@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment-range/3.0.3/moment-range.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="{{ CoreUrl::asset('asset_home_message\js\home_msg.js') }}"></script>
@endsection
