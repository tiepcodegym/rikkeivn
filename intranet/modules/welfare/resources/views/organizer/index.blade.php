<?php
    use Rikkei\Team\Model\Employee;
 
    if(isset($organizer)){
        $checkOrganizer = Employee::getIdEmpByEmail($organizer->email_company);
    }
    if(isset($checkOrganizer) && $checkOrganizer) {
        $disable = "readonly";
    } else {
        $disable = "";
    }
?>
<div class="row information-partners">
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-6 form-horizontal form-label-left">
                <div class="form-group">
                    <label for="wel_organizer_name" class="col-md-12 col-lg-2">{{trans('welfare::view.Rep Name')}}<em>*</em></label>
                    <div class="col-md-12 col-lg-10">
                        <div class="wel_organizer">
                            <div class="input-text" style="position: relative">
                                <input {{$disable}} type="text" name="wel_organizer[name]" id='wel_organizer_name' class = 'form-control' placeholder = '{{ trans('welfare::view.Rep Name') }}' @if(isset($organizer)) value="{{$organizer->name}}" @endif>
                                <span style="color: red" class="hidden error-name-organizer">{{trans('welfare::view.Require name attach')}}</span>
                                <i data-toggle="tooltip" data-placement="top" title="Xóa người tổ chức hiện tại và thêm thông tin mới" id ="cancel-chose-employee"class="fa fa-times-circle" aria-hidden="true" style="position: absolute;top: 10px;right: 8px;display:<?php if(isset($checkOrganizer) && $checkOrganizer) echo"block"; else echo "none";?>"></i>
                            </div>
                            <div class="button">
                                <input type="hidden" name="wel_organizer[id]"
                                       @if(isset($organizer)) value="{{$organizer->id}}" @endif>
                                <button type="button" class="btn btn-default show-popup-list-employee">...
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 form-horizontal form-label-left">
                <div class="form-group">
                    <label for="phone" class="col-md-12 col-lg-2 control-label">{{trans('welfare::view.Rep Phone')}}</label>
                    <div class="input-box col-md-12 col-lg-10">
                        <input  {{$disable}} type="text" id="wel_organizer_phone" name="wel_organizer[phone]" @if(isset($organizer)) value="{{$organizer->phone}}" @endif class="form-control" placeholder ="{{trans('welfare::view.Rep Phone')}}" onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,'')">
                        <span style="color: red" class="hidden error-phone-organizer">{{ trans('welfare::view.Required phone length 13') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 form-horizontal form-label-left">
                <div class="form-group">
                    {{ Form::label('position', trans('welfare::view.Job Position'), ['class' => 'col-md-12 col-lg-2 control-label']) }}
                    <div class="input-box col-md-12 col-lg-10">
                        <input {{$disable}} type="text" id="wel_organizer_role" name="wel_organizer[position]" @if(isset($organizer)) value="{{$organizer->position}}" @endif class="form-control" placeholder ="{{trans('welfare::view.Job Position')}}">
                    </div>
                </div>
            </div>
            <div class="col-md-6 form-horizontal form-label-left">
                <div class="form-group">
                    <label for="phone" class="col-md-12 col-lg-2 control-label">{{trans('welfare::view.Company email')}}</label>
                    <div class="input-box col-md-12 col-lg-10">
                        <input {{$disable}} type="text" id="wel_organizer_email" name="wel_organizer[email_company]" @if(isset($organizer)) value="{{$organizer->email_company}}" @endif class="form-control" placeholder ="{{trans('welfare::view.Company email')}}">
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 form-horizontal form-label-left">
                <div class="form-group">
                    {{ Form::label('company', trans('welfare::view.Department job'), ['class' => 'col-md-12 col-lg-2 control-label']) }}
                    <div class="input-box col-md-12 col-lg-10">
                        <input {{$disable}} type="text" id="wel_organizer_dep" name="wel_organizer[company]" @if(isset($organizer)) value="{{$organizer->company}}" @endif class="form-control" placeholder ="{{trans('welfare::view.Department job')}}">
                    </div>
                </div>
            </div>
            <div class="col-md-6 form-horizontal form-label-left">
                <div class="form-group">
                    {{ Form::label('note', trans('welfare::view.Wel_organizers_note'), ['class' => 'col-md-12 col-lg-2 control-label']) }}
                    <div class="input-box col-md-12 col-lg-10">
                        <textarea size = "50x30" id="wel_organizer_note" type="text" name="wel_organizer[note]" class="form-control" placeholder ="{{trans('welfare::view.Wel_organizers_note')}}">@if(isset($organizer)){{$organizer->note}}@endif</textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12 form-horizontal form-label-left text-center">
            <button class="btn btn-primary" id="btn-save-organizer"><i class="fa fa-floppy-o "></i> {{ trans('welfare::view.Save') }} <i class="fa fa-spin fa-refresh hidden" id="disable-btn-save-organizer"></i></button>
        </div>
    </div>
</div>

<div id="modal-list-employee" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-list"></i> {{ trans('welfare::view.List employee') }}</h4>
            </div>
            <div class="modal-body">
                <div class="box box-info">
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="table-employee-organizer">
                                <thead>
                                    <tr>
                                        <th class="">{{ trans('welfare::view.Employee name') }}</th>
                                        <th class="">{{ trans('welfare::view.Rep Phone') }}</th>
                                        <th class="">{{ trans('welfare::view.Department job') }}</th>
                                        <th class=" ">{{ trans('welfare::view.Job Position') }}</th>
                                        <th class="">{{ trans('welfare::view.Company email') }}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-add" id="btn-choose-employee"><i class="fa fa-check-square-o"></i> {{ trans('welfare::view.Choose') }}</button>
                <button type="button" class="btn-add" data-dismiss="modal"><span class="bootstrap-dialog-button-icon fa fa-close"></span> {{ trans('welfare::view.Close') }}</button>
            </div>
        </div>
    </div>
</div>
