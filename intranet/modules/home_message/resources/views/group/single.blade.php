@extends('layouts.default')

@section('title')

@endsection
<?php
use Rikkei\Core\Http\Requests\Request;
use Rikkei\Core\View\CoreUrl;use Rikkei\HomeMessage\Model\HomeMessageGroup;
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

$instanceHomeMessage = new HomeMessageGroup();
$id = isset(request()->id) ? request()->id : $collection->id;
$name_vi = isset(request()->txt_group_name_vi) ? request()->txt_group_name_vi : $collection->name_vi;
$name_en = isset(request()->txt_group_name_en) ? request()->txt_group_name_en : $collection->name_en;
$name_jp = isset(request()->txt_group_name_jp) ? request()->txt_group_name_jp : $collection->name_jp;
$priority = isset(request()->txt_priority) ? request()->txt_priority : $collection->priority;
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
                    <h3 style="text-align: center">{{ $id == 0 ? trans('HomeMessage::view.Single home message group') : trans('HomeMessage::view.Single update home message group') }}</h3>
                    <div class="clearfix" style="height: 50px"></div>
                    <div class="col-md-5 col-md-offset-3">
                        <form method="post" id="frmMain"
                              class="form-horizontal" autocomplete="off" id="form-register">
                            {!! csrf_field() !!}

                            <div class="row">
                                <div class="form-group">
                                    <label class="col-md-3 control-label required">{{ trans('HomeMessage::view.Group name') }}
                                        <em>*</em></label>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label required">vi</label>
                                    <div class="input-box col-md-9">
                                        <input type="text" id="txt_group_name_vi" class="form-control"
                                               value="{{$name_vi}}"
                                               name="txt_group_name_vi">
                                    </div>
                                </div>
                            </div>
                            <!-- End group name -->


                            <div class="row">
                                <div class="form-group">
                                    <label class="col-md-3 control-label required">en</label>
                                    <div class="input-box col-md-9">
                                        <input type="text" id="txt_group_name_en" class="form-control"
                                               value="{{$name_en}}"
                                               name="txt_group_name_en">
                                    </div>
                                </div>
                            </div>
                            <!-- End group name -->

                            <div class="row">
                                <div class="form-group">
                                    <label class="col-md-3 control-label required">jp</label>
                                    <div class="input-box col-md-9">
                                        <input type="text" id="txt_group_name_jp" class="form-control"
                                               value="{{$name_jp}}"
                                               name="txt_group_name_jp">
                                    </div>
                                </div>
                            </div>
                            <!-- End group name -->

                            <div class="row">
                                <div class="form-group">
                                    <label class="col-md-3 control-label required">{{ trans('HomeMessage::view.Priority') }}
                                        <em>*</em></label>
                                    <div class="input-box col-md-9">
                                        <input type="number" min="1" id="txt_priority" name="txt_priority"
                                               value="{{$priority}}"
                                               class="form-control required">
                                    </div>
                                </div>
                            </div>

                            <!-- End group priority -->
                            <div class="row" style="text-align: center;margin-top: 80px; margin-bottom: 150px">
                                <button type="button" onclick="window.history.back()" style="width: 150px;margin-right: 15px"
                                        class="btn btn-default">{{trans('HomeMessage::view.GoBack')}}</button>
                                @if($id != 0)
                                    <button id="btn-update-home-message-group" type="button" onclick="update()" style="width: 150px"
                                            class="btn btn-primary"> {{trans('HomeMessage::view.Update')}}</button>
                                @else
                                    <button  id="btn-create-home-message-group" type="button" style="width: 150px"
                                            onclick="insert()"
                                            class="btn btn-primary"> {{trans('HomeMessage::view.Create')}}</button>
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
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
    <script>
        $.validator.addMethod('requiredName', function (value, element, param) {
            if (($('#txt_group_name_vi').val() == '' && $('#txt_group_name_en').val() == '' && $('#txt_group_name_jp').val() == '')) {
                $('label[for="txt_group_name_vi"]').show();
                $('label[for="txt_group_name_en"]').show();
                $('label[for="txt_group_name_jp"]').show();
                return false;
            } else {
                $('label[for="txt_group_name_vi"]').hide();
                $('label[for="txt_group_name_en"]').hide();
                $('label[for="txt_group_name_jp"]').hide();
                return true;
            }
        });
        $('#frmMain').validate({
            rules: {
                'txt_group_name_vi': {
                    requiredName: '#txt_group_name_vi',
                },
                'txt_group_name_en': {
                    requiredName: '#txt_group_name_vi',
                },
                'txt_group_name_jp': {
                    requiredName: '#txt_group_name_vi',
                },

                'txt_priority': {
                    required: true,
                    number: true,
                    min: 1,
                },
            },
            messages: {
                'txt_group_name_vi': {
                    requiredName: '{!! trans('HomeMessage::message.This field is required') !!}',
                },
                'txt_group_name_en': {
                    requiredName: '{!! trans('HomeMessage::message.This field is required') !!}',
                },
                'txt_group_name_jp': {
                    requiredName: '{!! trans('HomeMessage::message.This field is required') !!}',
                },
                'txt_priority': {
                    required: '{!! trans('HomeMessage::message.This field is required') !!}',
                    min: '{!! trans('HomeMessage::message.Please enter a value greater than or equal to 1') !!}',
                    number: '{!! trans('HomeMessage::message.Please enter a valid number.') !!}',
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
            var frmMain = $('#frmMain');
            var url = "{{ URL::route('HomeMessage::home_message.update-group',['id'=>$id]) }}";
            $('#btn-update-home-message-group').attr('disabled', true);
            $.ajax({
                url: url,
                method: 'POST',
                data: frmMain.serialize(),
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                success: function (resp) {
                    window.location.href = "{{route('HomeMessage::home_message.all-group')}}";
                },
                error: function (errors) {
                    $('#btn-update-home-message-group').removeAttr('disabled');
                    console.log(errors);
                    alert('{!! trans('HomeMessage::message.System error') !!}');
                }
            })
        }


        function insert() {
            if (!$('#frmMain').valid()) {
                return false;
            }
            var frmMain = $('#frmMain');
            var url = "{{ URL::route('HomeMessage::home_message.insert-group') }}";
            $('#btn-create-home-message-group').attr('disabled', true);
            $.ajax({
                url: url,
                method: 'POST',
                data: frmMain.serialize(),
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                success: function (resp) {
                    window.location.href = "{{route('HomeMessage::home_message.all-group')}}";
                },
                error: function (errors) {
                    $('#btn-create-home-message-group').removeAttr('disabled');
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
