@extends('layouts.default')

@section('title')

@endsection
<?php
use Carbon\Carbon;
use Rikkei\Core\Http\Requests\Request;
use Rikkei\Core\View\CoreUrl;
use Rikkei\HomeMessage\Helper\Constant;
use Rikkei\HomeMessage\Helper\Helper;

$id = request()->segment(4);
?>

@section('css')
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css"/>
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css"/>
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_managetime/css/common.css') }}"/>
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
                <div class="box box-primary row" style="padding-bottom: 20px !important;overflow:hidden;">
                    <h3 style="text-align: center">{{ $id == 0 ? trans('HomeMessage::view.Single home message banner') : trans('HomeMessage::view.Single update home message banner') }}</h3>
                    <div class="clearfix" style="height: 50px"></div>
                    <div class="col-md-5 col-md-offset-3">
                        <form method="post" id="frmMain"
                              class="form-horizontal" autocomplete="off" id="form-register">
                            {!! csrf_field() !!}

                            <div class="row">
                                <div class="form-group">
                                    <label class="col-md-3 control-label required">{{ trans('HomeMessage::view.Banner image') }}
                                        <em>*</em></label>
                                    <div class="col-md-9">
                                        <div class='input-box'>
                                            <input type="file" id="image"
                                                   class="form-control  {{$id > 0 ? '' :'required'}}" accept="image/*"
                                                   name="image">
                                        </div>
                                        @if(isset($collection->image) && strlen($collection->image))
                                            <input type="hidden" id="old_image" name="old_image"
                                                   value="{{$collection->image}}">
                                            <img src="{{$collection->image}}"
                                                 style="max-height: 120px; max-width: 100%; border: solid 1px #3333;"/>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <!-- End banner image -->

                            <div class="row">
                                <div class="form-group">
                                    <label class="col-md-3 control-label required">{{ trans('HomeMessage::view.Banner name') }}
                                        <em>*</em></label>
                                    <div class="input-box col-md-9">
                                        <input type="text" id="display_name" class="form-control required"
                                               value="{{$collection->display_name}}"
                                               name="display_name">
                                    </div>
                                </div>
                            </div>
                            <!-- End banner name -->

                            <div class="row">
                                <div class="form-group">
                                    <label class="col-md-3 control-label required">{{ trans('HomeMessage::view.Banner begin at') }}
                                        <em>*</em></label>
                                    <div class="input-box col-md-9">
                                        <div class='input-group date' id='datepicker_start_at'>
                                            <input type='text' class="form-control" name="begin_at"
                                                   data-date-format="{{ Constant::jsDateTimeFormat() }}" id="begin_at"
                                                   value="{{ $collection->begin_at }}"/>
                                            <span class="input-group-addon">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                            </span>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <!-- End banner begin at -->

                            <div class="row">
                                <div class="form-group">
                                    <label class="col-md-3 control-label required">{{ trans('HomeMessage::view.Banner end at') }}
                                        <em>*</em></label>
                                    <div class="input-box col-md-9">
                                        <div class='input-group date' id='datepicker_end_at'>
                                            <input type='text' class="form-control" name="end_at"
                                                   data-date-format="{{ Constant::jsDateTimeFormat() }}" id="end_at"
                                                   value="{{ $collection->end_at }}"/>
                                            <span class="input-group-addon">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                            </span>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <!-- End banner end at -->

                            <div class="row">
                                <div class="form-group">
                                    <label class="col-md-3 control-label required">{{ trans('HomeMessage::view.Banner action type') }}</label>
                                    <div class="input-box col-md-9">
                                        <select name="type" id="type" class="form-control"
                                                onchange="onTypeHandleChange(this)">
                                            @foreach(Constant::homeMessageBannerTypes() as $key => $value)
                                                <option
                                                        value="{{$key}}"
                                                        @if($key == $collection->type)
                                                        selected="selected"
                                                        @endif>
                                                    {{$value}}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!-- End group type -->

                            <div class="row" id="testBox"
                                 style="display:{{$collection->type == Constant::HOME_MESSAGE_BANNER_TYPE_TEST ? 'block' : 'none'}};">
                                <div class="form-group">
                                    <label class="col-md-3 control-label required">{{ trans('HomeMessage::view.Banner select test') }}
                                        <em>*</em></label>
                                    <div class="input-box col-md-9">
                                        <select name="test_id" id="test_id" class="form-control" style="width: 100%">
                                            @foreach($tests as $key => $test)
                                                <option
                                                        {{old('test_id', $collection->action_id ) == $test->id ? 'selected' : ''}}
                                                        value="{{$test->id}}">
                                                    {{$test->name}}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!-- End test -->

                            <div class="row"
                                 style="display: {{$collection->type == Constant::HOME_MESSAGE_BANNER_TYPE_SHAKE ||  $collection->type == Constant::HOME_MESSAGE_BANNER_TYPE_TEST || $collection->type == Constant::HOME_MESSAGE_BANNER_TYPE_DONATE ? 'none' : 'block'}}">
                                <div class="form-group">
                                    <label class="col-md-3 control-label required">{{ trans('HomeMessage::view.Banner link') }}
                                        <em style="display: {{$collection->type == Constant::HOME_MESSAGE_BANNER_TYPE_NEWS ? 'inline-block' : 'none'}}">*</em></label>
                                    <div class="input-box col-md-9">
                                        <input type="text" id="link" class="form-control"
                                               value="{{$collection->link}}"
                                               onchange="onLinkHandleChnage(this)"
                                               {{$collection->type == Constant::HOME_MESSAGE_BANNER_TYPE_NEWS ? 'required="required"' : ''}}
                                               name="link">
                                    </div>
                                </div>
                            </div>
                            <!-- End banner link -->

                            <div class="row" style="display: {{$collection->type == Constant::HOME_MESSAGE_BANNER_TYPE_SHAKE ? 'block' : 'none'}}">
                                <div class="form-group">
                                    <label class="col-md-3 control-label required">{{ trans('HomeMessage::view.Banner event') }}
                                        <em style="display: {{$collection->type == Constant::HOME_MESSAGE_BANNER_TYPE_SHAKE ? 'inline-block' : 'none'}}">*</em></label>
                                    <div class="input-box col-md-9">
                                        <select name="event_id"
                                                id="event_id"
                                                class="form-control"
                                                {{$collection->type == Constant::HOME_MESSAGE_BANNER_TYPE_SHAKE ? 'required="required"' : ''}}
                                                style="color: #333 !important;">
                                            <option value="">--</option>
                                            @foreach($events as $event)
                                                <option value="{{ $event['id'] }}" {{ $event['id'] == $collection->event_id ? 'selected' : '' }}>{{ $event['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!-- End group event_id -->
                            <div class="row"
                                 style="display: {{$collection->type == Constant::HOME_MESSAGE_BANNER_TYPE_DONATE ? 'block' : 'none'}}">
                                <div class="form-group">
                                    <label class="col-md-3 control-label required">{{ trans('HomeMessage::view.Banner select donate') }}
                                        <em style="display: {{$collection->type == Constant::HOME_MESSAGE_BANNER_TYPE_DONATE ? 'inline-block' : 'none'}}">*</em></label>
                                    <div class="input-box col-md-9">
                                        <select name="donate_id"
                                                id="donate_id"
                                                class="form-control"
                                                {{$collection->type == Constant::HOME_MESSAGE_BANNER_TYPE_DONATE ? 'required="required"' : ''}}
                                                style="color: #333 !important;">
                                            <option value="">--</option>
                                            @foreach($donates as $donate)
                                                <option value="{{ $donate['id'] }}" {{ $donate['id'] == $collection->action_id ? 'selected' : '' }}>{{ $donate['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group">
                                    <label class="col-md-3 control-label required">{{ trans('HomeMessage::view.Banner team') }}
                                        <em>*</em></label>
                                    <div class="col-md-9">
                                        <div class='input-box'>
                                            <?php $oldBranches = old('branches', $collection->teams->count() ? $collection->teams->pluck('id')->toArray() : []) ?>
                                            <select class="has-search form-control"
                                                    style="width:100%;min-height:200px;font-size:14px;color:#333;"
                                                    data-flag-dom="select2"
                                                    id="branches"
                                                    required="required"
                                                    name="branches[]" multiple="multiple">
                                                @foreach($branches as $key => $branch)
                                                    <option {{in_array($branch['value'], $oldBranches) ? 'selected' : ''}}
                                                            value="{{ $branch['value'] }}">
                                                        {{ Helper::BODParser($branch['label']) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End branch  -->

                            <div class="row">
                                <div class="form-group">
                                    <label class="col-md-3 control-label required">{{ trans('HomeMessage::view.Banner gender') }}</label>
                                    <div class="input-box col-md-9">
                                        <select name="gender_target" id="gender" class="form-control">
                                            @foreach(Constant::homeMessageBannerGenders() as $key => $option)
                                                <option
                                                        value="{{$option['value']}}"
                                                        @if($option['value'] == $collection->gender_target)
                                                        selected="selected"
                                                        @endif>
                                                    {{$option['label']}}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!-- End select gender  -->

                            <div class="row">
                                <div class="form-group">
                                    <label class="col-md-3 control-label required">{{ trans('HomeMessage::view.status') }}
                                        <em>*</em></label>
                                    <div class="input-box col-md-9" style="margin-top: 5px">
                                        <input type="radio"
                                               value="1" @if($collection->status == 1 || !$id) checked @endif
                                               name="status"> Khả dụng<br>
                                        <input type="radio"
                                               value="0" @if($collection->status == 0 && $id) checked @endif
                                               name="status"> Không khả dụng
                                    </div>
                                </div>
                            </div>
                            <!-- End status -->
                            <div class="row" style="text-align: center;margin-top: 20px">
                                <button type="button"
                                        onclick="window.history.back()"
                                        style="width: 150px;margin-right: 15px"
                                        class="btn btn-default">
                                    {{trans('HomeMessage::view.GoBack')}}
                                </button>
                                @if($id != 0)
                                    <button type="button"
                                            onclick="update()"
                                            style="width: 150px"
                                            class="btn btn-primary">
                                        {{trans('HomeMessage::view.Update')}}
                                    </button>
                                @else
                                    <button type="button" id="save_home_banner"
                                            onclick="insert()"
                                            style="width: 150px"
                                            class="btn btn-primary">
                                        {{trans('HomeMessage::view.Create')}}
                                    </button>
                                @endif

                            </div>
                            <input type="hidden" name="action_id" id="action_id" value="{{$collection->action_id}}">
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
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script>
        $('#test_id').select2();
        function onLinkHandleChnage(el) {
            const type = $('#type');
            const typeValue = parseInt(type.val());
            if (typeValue === {{Constant::HOME_MESSAGE_BANNER_TYPE_NEWS}} && el.value.length !== 0) {
                const url = "{{ URL::route('HomeMessage::home_message.find-blog-banner') }}";
                $.ajax({
                    url: url,
                    method: 'GET',
                    data: {
                        url: el.value,
                    },
                    success: function (resp) {
                        console.log(resp)
                        $('#action_id').val(resp.id);
                    },
                    error: function (errors) {
                        console.log(errors);
                        $('#action_id').val("");
                        alert(errors.responseJSON);
                    }
                })
            }
        }

        function onTypeHandleChange(el) {
            const parent = $("#event_id").closest('.row');
            const requiredLabel = parent.find("label em");
            const selectBox = parent.find("#event_id");
            const linkEl = $('#link');
            const linkParent = linkEl.closest('.row');
            const requiredLink = linkParent.find("label em");
            const value = parseInt(el.value);
            const testBox = $("#testBox");
            const testField = $("#test_id");
            const parentDonate = $("#donate_id").closest('.row');
            const requiredLabelDonate = parent.find("label em");
            const selectBoxDonate = parent.find("#donate_id");

            if (value === {{Constant::HOME_MESSAGE_BANNER_TYPE_TEST}}) {
                testBox.show();
                testField.removeAttr("disabled");
                requiredLabelDonate.hide();
                parentDonate.hide();
                selectBoxDonate.attr("required", "required")
                requiredLabel.hide();
                parent.hide();
                requiredLink.hide();
                linkParent.hide();
                selectBox.attr("required", "required");
                linkEl.removeAttr('required');
            }

            if (value === {{Constant::HOME_MESSAGE_BANNER_TYPE_SHAKE}}) {
                requiredLabel.show();
                parent.show();
                requiredLink.hide();
                linkParent.hide();
                selectBox.attr("required", "required");
                requiredLabelDonate.hide();
                parentDonate.hide();
                linkEl.removeAttr('required');
                testBox.hide();
                testField.attr("disabled", "");
            }
            if (value === {{Constant::HOME_MESSAGE_BANNER_TYPE_NEWS}}) {
                requiredLink.show();
                requiredLabelDonate.hide();
                parentDonate.hide();
                selectBoxDonate.attr("required", "required")
                linkParent.show();
                parent.hide();
                linkEl.attr('required', 'required');
                requiredLabel.hide();
                testBox.hide();
                testField.attr("disabled", "");
            }
            if (value === {{Constant::HOME_MESSAGE_BANNER_TYPE_OTHER}}) {
                requiredLabelDonate.hide();
                parentDonate.hide();
                selectBoxDonate.attr("required", "required")
                requiredLabel.hide();
                parent.hide();
                requiredLink.hide();
                linkParent.show();
                linkEl.removeAttr('required');
                selectBox.removeAttr("required");
                testBox.hide();
                testField.attr("disabled", "");
            }
            if (value === {{Constant::HOME_MESSAGE_BANNER_TYPE_GRATEFUL}}) {
                requiredLabelDonate.hide();
                parentDonate.hide();
                selectBoxDonate.attr("required", "required")
                requiredLink.hide();
                linkParent.hide();
                parent.hide();
                linkEl.attr('required', 'required');
                requiredLabel.hide();
                testBox.hide();
                testField.attr("disabled", "");
            }
          if (value === {{Constant::HOME_MESSAGE_BANNER_TYPE_LUNAR}}) {
            requiredLabelDonate.hide();
            parentDonate.hide();
            selectBoxDonate.attr("required", "required")
            requiredLink.hide();
            linkParent.hide();
            parent.hide();
            linkEl.attr('required', 'required');
            requiredLabel.hide();
            testBox.hide();
            testField.attr("disabled", "");
          }
            if (value === {{Constant::HOME_MESSAGE_BANNER_TYPE_DONATE}}) {
                requiredLabelDonate.show();
                requiredLink.show();
                linkParent.hide();
                parent.hide();
                parentDonate.show();
                selectBoxDonate.attr("required", "required");
                testBox.hide();
                testField.attr("disabled", "");
                linkEl.hide()
            }
            if (value === {{Constant::HOME_MESSAGE_BANNER_TYPE_TEN_YEARS_GIFT}}
                || value === {{Constant::HOME_MESSAGE_BANNER_TYPE_WOMEN_DAY}}
            ) {
                requiredLabelDonate.hide();
                parentDonate.hide();
                requiredLink.hide();
                linkParent.hide();
                parent.hide();
                requiredLabel.hide();
                testBox.hide();
                testField.attr("disabled", "");
            }
        }

        function branchAllHandleChange(el) {
            let _el = $(el);
            let checkboxList = _el.closest('.form-group').find('#branchList input');
            checkboxList.map(function (i, checkbox) {
                checkbox.checked = el.checked;
            });
        };

        function branchHandleChange(el, parent) {
            let _parent = $(parent);
            let wrapper = $(el).closest('#branchList');
            let checkboxCount = wrapper.find('input').length;
            let checkedCount = wrapper.find('input:checked').length;

            if (checkboxCount === checkedCount) {
                _parent[0].checked = true;
            } else {
                _parent[0].checked = false;
            }
        }

        $('#datepicker_start_at,#datepicker_end_at').datetimepicker(
            {
                allowInputToggle: true,
                format: 'DD-MM-YYYY',
                sideBySide: true,
            }
        );
        $.validator.addMethod('compareTimeStart', function (value, element, param) {
            console.log('compareTimeStart');
            var dateCompare = new Date(moment($(param).val(), "{{ Constant::jsDateTimeFormat() }}"));
            var arrParam = $(param).val().split(" ");
            var arrValue = value.split(" ");
            if ($(param).val()) {
                return (new Date(moment(value, "{{ Constant::jsDateTimeFormat() }}"))) <= dateCompare;
            } else {
                return true;
            }
        });
        $.validator.addMethod('compareTimeEnd', function (value, element, param) {
            var dateCompare = new Date(moment($(param).val(), "{{ Constant::jsDateTimeFormat() }}"));
            var arrParam = $(param).val().split(" ");
            var arrValue = value.split(" ");
            return (new Date(moment(value, "{{ Constant::jsDateTimeFormat() }}"))) >= dateCompare;
        });

        $('#frmMain').validate({
            rules: {
                'display_name': {
                    required: true,
                },
                'begin_at': {
                    required: true,
                    compareTimeStart: '#end_at'
                },
                'end_at': {
                    required: true,
                    compareTimeEnd: '#begin_at'
                },
                'priority': {
                    number: true,
                },
                'image': {
                    required: $('#image').hasClass('required'),
                },
                'branches': {
                    required: true,
                },
            },
            messages: {
                'display_name': {
                    required: '{!! trans('HomeMessage::message.This field is required') !!}',
                },
                'begin_at': {
                    required: '{!! trans('HomeMessage::message.This field is required') !!}',
                    compareTimeStart: '{!! trans('HomeMessage::message.Time start at not less than  end at') !!}',
                },
                'end_at': {
                    required: '{!! trans('HomeMessage::message.This field is required') !!}',
                    compareTimeEnd: '{!! trans('HomeMessage::message.Time start at not less than  end at') !!}',
                },
                'priority': {
                    min: '{!! trans('HomeMessage::message.Please enter a value greater than or equal to 1') !!}',
                    number: '{!! trans('HomeMessage::message.Please enter a valid number.') !!}',
                },
                'image': {
                    required: '{!! trans('HomeMessage::message.This field is required') !!}',
                },
                'event_id': {
                    required: '{!! trans('HomeMessage::message.This field is required') !!}',
                },
                'link': {
                    required: '{!! trans('HomeMessage::message.This field is required') !!}',
                },
                'test_id': {
                    required: '{!! trans('HomeMessage::message.This field is required') !!}',
                },
                'branches[]': {
                    required: '{!! trans('HomeMessage::message.This field is required') !!}',
                },
            },
            errorPlacement: function (error, element) {
                error.insertAfter(element);
            },
        });


        function update() {
            if (!$('#frmMain').valid() || !confirm('{!! trans('HomeMessage::message.Are you sure update item selected?') !!}')) {
                return false;
            }
            var formData = new FormData($('#frmMain')[0]);
            var url = "{{ URL::route('HomeMessage::home_message.update-banner',['id'=>$id]) }}";
            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                success: function (resp) {
                    window.location.href = "{{route('HomeMessage::home_message.all-banner')}}";
                },
                error: function (errors) {
                    console.log(errors);
                    alert('{!! trans('HomeMessage::message.System error') !!}');
                }
            })
        }


        function insert() {
            if (!$('#frmMain').valid()) {
                return false;
            }
            $('#save_home_banner').attr('disabled', true);
            var frmMain = $('#frmMain');
            var url = "{{ URL::route('HomeMessage::home_message.insert-banner') }}";
            var formData = new FormData(frmMain[0]);
            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                success: function (resp) {
                    window.location.href = "{{route('HomeMessage::home_message.all-banner')}}";
                },
                error: function (errors) {
                    try {
                        var err = JSON.parse(errors.responseText);
                        alert(err.txt_group_name[0]);
                        return false;
                    } catch (e) {
                    }
                    alert('{!! trans('HomeMessage::message.System error') !!}');
                }
            })
        }
    </script>
@endsection
