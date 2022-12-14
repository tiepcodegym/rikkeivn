@extends('files::layout.file_layout')

@section('title')
    {{ trans('files::view.File text') }}
@endsection
<?php
use Rikkei\Core\View\CoreUrl;
use Carbon\Carbon;
use Rikkei\Files\Model\ManageFileText;
use Rikkei\Team\View\TeamList;

$urlCheckCodeFileExist = route('file::file.check-code-file-exist');
$typeCvdi = ManageFileText::CVDI;
$typeCvden = ManageFileText::CVDEN;
$approver = ManageFileText::APPROVAL;
$unApprover = ManageFileText::UNAPPROVAL;
$cv =  ManageFileText::TYPEFILE_CV;
$qd = ManageFileText::TYPEFILE_QD;
$teamsOptionAll = TeamList::toOption(null, true, false);
?>
@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="{{ CoreUrl::asset('manage_asset/css/style.css') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.1.20/jquery.fancybox.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <style type="text/css">
        .displayNone {
            display: none;
        }
    </style>
@endsection

@section('sidebar-common')
@include('files::include.sidebar_leave')
@endsection

@section('content-common')
<div class="row">
    <div class="col-md-12">
        <!-- Box register -->
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title managetime-box-title">{{ trans('files::view.Chi tiết văn bản đi') }}</h3>
            </div>
            @if($status == $approver && $type == $typeCvdi)
                <div class="box-body no-padding">
                    <div class="col-lg-10 col-lg-offset-1">
                        <form>
                            <div class="row">
                                <div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">
                                    <div class="box-body">
                                        <div class="row">
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <label class="control-label">{{ trans('files::view.Type') }}</label>
                                                    <div class="input-box">
                                                        <select class="form-control" disabled="">
                                                            <option value="2" selected="">{{ trans('files::view.Công văn đi') }}</option>
                                                            <option value="1">{{ trans('files::view.Công văn đến') }}</option>
                                                        </select>
                                                    </div>
                                            </div>
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <label class="control-label required">{{ trans('files::view.Số kí hiệu') }} <em> *</em></label>
                                                <div class="input-box">
                                                    <input type="text" name="codeText" class="form-control" value="{{ $itemNumberGo->code_file }}" readonly />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row option2">
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <label class="control-label required">{{ trans('files::view.Số đi') }} <em> *</em></label>
                                                <div class="input-box">
                                                    <input type="text" class="form-control" value="{{ $itemNumberGo->number_go }}" readonly />
                                                </div>
                                            </div>
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <label class="control-label">{{ trans('files::view.Loại văn bản') }} </label>
                                                <div class="input-box">
                                                    <select class="form-control type_file" name="type_file" disabled="">
                                                            <option value="1" <?php if ($itemNumberGo->type_file == $type) : ?>
                                                                selected
                                                            <?php endif ?>>{{ trans('files::view.Quyết định') }} </option>
                                                            <option value="2"<?php if ($itemNumberGo->type_file == $type) : ?>
                                                                selected
                                                            <?php endif ?>>{{ trans('files::view.Công văn') }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row option2">
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <label class="control-label required managetime-label">{{ trans('files::view.Nơi lưu bản gốc') }}</label>
                                                <div class="input-box">
                                                    <input type="text" name="save_file" class="form-control" disabled value="{{ $itemNumberGo->save_file}}" />
                                                </div>
                                            </div>
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <label class="control-label required">{{ trans('files::view.Nơi nhận') }}</label>
                                                <div class="input-box">
                                                    <input type="text" name="file_from" class="form-control" value="{{ $itemNumberGo->file_from}}" disabled="" />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row option2">
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <label class="control-label required">{{ trans('files::view.Ngày văn bản') }}<em> *</em></label>
                                                <div class="input-box">
                                                    <div class='input-group date'>
                                                        <span class="input-group-addon">
                                                            <span class="glyphicon glyphicon-calendar"></span>
                                                        </span>
                                                        <input type="text" name="date_file" class="form-control dateOption2" value="{{ Carbon::parse($itemNumberGo->date_file)->format('Y-m-d') }}" disabled="" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 form-group form-group-select2">
                                              <label class="control-label required">{{ trans('files::view.Ngày phát hành') }} <em> *</em></label>
                                                <div class="input-box">
                                                    <div class='input-group date'>
                                                        <span class="input-group-addon">
                                                            <span class="glyphicon glyphicon-calendar"></span>
                                                        </span>
                                                        <input type="text" class="form-control" value="{{ Carbon::parse($itemNumberGo->date_released)->format('Y-m-d') }}" disabled="" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-6 form-group form-group-select2">
                                               <label class="control-label required">{{ trans('files::view.Đơn vị soạn thảo') }} <em> *</em></label>
                                                <div class="input-box">
                                                    <select name="groupTeam" class="form-control" id="groupTeam" disabled="">
                                                        <option> {{ $itemNumberGo->name_teams}}</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 form-group form-group-select2 option2">
                                                <label class="control-label required">{{ trans('files::view.Người ký') }}<em> *</em></label>
                                                <div class="input-box">
                                                    <select class="form-control" disabled="" id="selectEmp">
                                                        <option>{{ $itemNumberGo->name_employees }}</option>
                                                    </select>
                                                    <!-- <input type="text" id="employee_id" name="signer" class="form-control" disabled="" /> -->
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row option2">
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <div class="input-box">
                                                    <div class="checkbox">
                                                        <label>
                                                            <input type="radio" name="tick" value="1" disabled="" 
                                                            <?php if ($itemNumberGo->tick == $approver): ?>
                                                                checked
                                                            <?php endif ?>> {{ trans('files::view.Đã ký') }}
                                                        </label>    
                                                    </div>
                                                    <div class="checkbox">
                                                        <label>
                                                            <input type="radio" name="tick" value="2" disabled=""
                                                            <?php if ($itemNumberGo->tick == $unApprover): ?>
                                                                checked
                                                            <?php endif ?>> {{ trans('files::view.Chưa ký') }}
                                                        </label>    
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <div class="input-box">
                                                    <span id="tickSinger">
                                                        <p>{{ $itemNumberGo->position_employees }}</p> 
                                                        <p style="text-indent: 1em;">({{ trans('files::view.Đã ký') }})</p>
                                                        <p>{{ $itemNumberGo->name_employees }}</p>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="form-group">
                                                <label class="col-sm-12 control-label required">{{ trans('files::view.Trích Yếu') }} <em>*</em></label>
                                                <div class="col-sm-12 control-label">
                                                    <textarea id="editor-content-event" class="quote_text" name="quote_text" disabled="">{{ $itemNumberGo->quote_text }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <br/>

                                        <div class="row" id="div-content">
                                            <div class="form-group">
                                            <label class="col-sm-12 control-label required">{{ trans('files::view.Nội dung') }} <em>*</em></label>
                                                <div class="col-sm-12 control-label">
                                                    <textarea id="content" class="text-editor" name="content" disabled="">{{ $itemNumberGo->content }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <br/>
                                        <div class="form-group">
                                            <label class="control-label required">{{ trans('files::view.Ghi chú') }}</label>
                                            <div class="input-box">
                                                <textarea name="note_text" id="note_text" disabled="" class="form-control">{{ $itemNumberGo->note_text }}</textarea>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-6 form-group-select2">
                                                <label class="control-label required">{{ trans('files::view.Tệp nội dung') }}</label>
                                                <div class="input-box">
                                                    <input type="text" name="save_file" class="form-control" disabled value="{{ $itemNumberGo->file_content}}" />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                           <div class="col-sm-6 form-group form-group-select2">
                                                <label class="control-label required">{{ trans('manage_time::view.Related persons need notified') }} <em> *</em></label>
                                                <div class="input-box">
                                                    <select name="related_persons_list[]" class="form-control select-search-employee" multiple disabled="">
                                                        @if (!empty($groupTeamEdit))
                                                        @foreach($groupTeamEdit as $value)
                                                            <option value="{{ $value->id }}" selected>{{ $value->name }}</option>
                                                        @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 managetime-form-group">
                                                <label class="control-label managetime-label">{{ trans('manage_time::view.Group email need notified') }}</label>
                                                <div class="input-box">
                                                    <select name="group_email[]" class="form-control group-email" multiple disabled="">
                                                        @if (!empty($groupEmailEdit))
                                                        @foreach($groupEmailEdit as $value)
                                                            <option value="{{$value}}" selected>{{substr($value, 0, strrpos($value, '@'))}}</option>
                                                        @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <div class="box-body no-padding">
                    <div class="col-lg-10 col-lg-offset-1">
                        <form role="form" method="post" action="{{ route('file::file.postEditFile') }}" class="managetime-form-register" id="form-register-file" enctype="multipart/form-data" autocomplete="off">
                            {!! csrf_field() !!}
                            @if($itemNumberGo->id)
                                <input type="hidden" name="file_id" value="{{ $itemNumberGo->id }}" />
                            @endif
                            <div class="row">
                                <div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">
                                    <div class="box-body">
                                        <div class="row">
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <label class="control-label">{{ trans('files::view.Type') }}</label>
                                                    <div class="input-box">
                                                        <select id="type" class="form-control" name="type" disabled="">
                                                            <option value="2" selected>{{ trans('files::view.Công văn đi') }}</option>
                                                            <option value="1">{{ trans('files::view.Công văn đến') }}</option>
                                                        </select>
                                                    </div>
                                            </div>
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <label class="control-label required">{{ trans('files::view.Số kí hiệu') }} <em> *</em></label>
                                                <div class="input-box">
                                                    <input type="text" name="codeText" class="form-control codeText" value="{{ $itemNumberGo->code_file }}"/>
                                                    <label id="codeText-error" class="error displayNone" for="approver">{{ trans('files::view.The field is required') }}</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row option2">
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <label class="control-label required">{{ trans('files::view.Số đi') }} <em> *</em></label>
                                                <div class="input-box">
                                                    <input type="text" name="numberGo" class="form-control" value="{{ $itemNumberGo->number_go }}" readonly />
                                                </div>
                                            </div>
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <label class="control-label">{{ trans('files::view.Loại văn bản') }} </label>
                                                <div class="input-box">
                                                    <select class="form-control type_file" name="type_file">
                                                            <option value="1" <?php if ($itemNumberGo->type_file == $qd): ?>
                                                                selected
                                                            <?php endif ?>>{{ trans('files::view.Quyết định') }} </option>
                                                            <option value="2" <?php if ($itemNumberGo->type_file == $cv): ?>
                                                                selected
                                                            <?php endif ?>>{{ trans('files::view.Công văn') }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row option2">
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <label class="control-label required managetime-label">{{ trans('files::view.Nơi lưu bản gốc') }}</label>
                                                <div class="input-box">
                                                    <input type="text" name="save_file" class="form-control" value="{{ $itemNumberGo->save_file }}" />
                                                </div>
                                            </div>
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <label class="control-label required">{{ trans('files::view.Nơi nhận') }}</label>
                                                <div class="input-box">
                                                    <input type="text" name="file_from" class="form-control" value="{{ $itemNumberGo->file_from }}" />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row option2">
                                            <div class="col-sm-6 form-group form-group-select2">
                                              <label class="control-label required">{{ trans('files::view.Ngày văn bản') }}<em> *</em></label>
                                                <div class="input-box">
                                                    <div class='input-group date' id='datetimepicker-date_file'>
                                                        <span class="input-group-addon">
                                                            <span class="glyphicon glyphicon-calendar"></span>
                                                        </span>
                                                        <input type="text" name="date_file" class="form-control dateOption2" value="{{ Carbon::parse($itemNumberGo->date_file)->format('Y-m-d') }}" />
                                                    </div>
                                                    <label id="date_file-error" class="date_file-error error displayNone" for="approver">{{ trans('files::view.The field is required') }}</label>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 form-group form-group-select2">
                                              <label class="control-label required">{{ trans('files::view.Ngày phát hành') }} <em> *</em></label>
                                                <div class="input-box">
                                                    <div class='input-group date' id="datetimepicker-date_released">
                                                        <span class="input-group-addon">
                                                            <span class="glyphicon glyphicon-calendar"></span>
                                                        </span>
                                                        <input type="text" id="dateRelease" name="date_released" class="form-control" value="{{ Carbon::parse($itemNumberGo->date_released)->format('Y-m-d') }}" />
                                                    </div>
                                                    <label id="date_released-error" class="date_released-error error displayNone" for="approver">{{ trans('files::view.The field is required') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6 form-group form-group-select2 selectBod">
                                               <label class="control-label required">{{ trans('files::view.Đơn vị soạn thảo') }} <em class="hiddenEm"> *</em></label>
                                                <div class="team-select-box">
                                                    <div class="input-box">
                                                        <select name="groupTeam" id="groupTeam"
                                                            class="form-control select-search"
                                                            autocomplete="off">
                                                                <option value="0">&emsp;</option>
                                                                @foreach($teamsOptionAll as $option)
                                                                    <option value="{{ $option['value'] }}" 
                                                                    code="{{ $option['code'] }}" 
                                                                    <?php if ($itemNumberGo->id_teams == $option['value']): ?>
                                                                        selected
                                                                    <?php endif ?> >{{ $option['label'] }}</option>
                                                                @endforeach
                                                        </select>
                                                        <label id="groupTeam-error" class="groupTeam-error error displayNone" for="approver">{{ trans('files::view.The field is required') }}</label>
                                                        <p id='appendTeam'></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 form-group form-group-select2 option2">
                                                <label class="control-label required">{{ trans('files::view.Người ký') }}<em> *</em></label>
                                                <div class="input-box">
                                                    <select name="signer" class="form-control" id="selectEmp">
                                                        <option value="{{ $itemNumberGo->signer }}">{{ $itemNumberGo->name_employees }}</option>
                                                    </select>
                                                    <label id="signer-error" class="signer-error error displayNone" for="approver">{{ trans('files::view.The field is required') }}</label>
                                                    <!-- <input type="text" id="employee_id" name="signer" class="form-control" disabled="" /> -->
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row option2">
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <div class="input-box">
                                                    <div class="checkbox">
                                                        <label>
                                                            <input type="radio" name="tick" value="1" 
                                                            <?php if ($itemNumberGo->tick == $approver): ?>
                                                                checked
                                                            <?php endif ?>> {{ trans('files::view.Đã ký') }}
                                                        </label>    
                                                    </div>
                                                    <div class="checkbox">
                                                        <label>
                                                            <input type="radio" name="tick" value="2"
                                                            <?php if ($itemNumberGo->tick == $unApprover): ?>
                                                                checked
                                                            <?php endif ?>> {{ trans('files::view.Chưa ký') }}
                                                        </label>    
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <div class="input-box">
                                                    <span id="tickSinger">
                                                        <p>{{ $itemNumberGo->position_employees }}</p> 
                                                        <p style="text-indent: 1em;">({{ trans('files::view.Đã ký') }})</p>
                                                        <p>{{ $itemNumberGo->name_employees }}</p>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="form-group">
                                                <label class="col-sm-12 control-label required">{{ trans('files::view.Trích Yếu') }} <em>*</em></label>
                                                <div class="col-sm-12 control-label">
                                                    <textarea id="quote_text" class="text-editor" name="quote_text">{{ $itemNumberGo->quote_text }}</textarea>
                                                    <label id="quote-error" class="quote-error error displayNone" for="approver">{{ trans('files::view.The field is required') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                        <br/>

                                        <div class="row" id="div-content">
                                            <div class="form-group">
                                            <label class="col-sm-12 control-label required">{{ trans('files::view.Nội dung') }} <em>*</em></label>
                                                <div class="col-sm-12 control-label">
                                                    <textarea id="content" class="text-editor" name="content">{{ $itemNumberGo->content }}</textarea>
                                                    <label id="content-error" class="content-error error displayNone" for="approver">{{ trans('files::view.The field is required') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                        <br/>

                                        <div class="form-group">
                                            <label class="control-label required">{{ trans('files::view.Ghi chú') }}</label>
                                            <div class="input-box">
                                                <textarea name="note_text" id="note_text" class="form-control">{{ $itemNumberGo->note_text }}</textarea>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-12 form-group-select2">
                                                <label class="control-label required">{{ trans('files::view.Tệp nội dung') }} <em class="displayNone contenRequired">*</em></label><p class="detaiFile">{{ $itemNumberGo->file_content}}</p>
                                                <div class="input-box">
                                                    <input type="file" name="file_content" id="file_content">
                                                    <p class="fileError"></p>
                                                    <label id="file_content-error" class="file_content-error error displayNone" for="approver">{{ trans('files::view.The field is required') }}</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row displayNone showFormMail">
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <label class="control-label required">{{ trans('manage_time::view.Related persons need notified') }}<em> *</em></label>
                                                <div class="input-box">
                                                    <select name="related_persons_list[]" class="form-control select-search-employee" data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee') }}" multiple>
                                                    </select>
                                                    <p class="search-employee-error displayNone error">{{ trans('files::view.The field is required') }}</p>
                                                </div>
                                            </div>

                                            @if (isset($groupEmail) && count($groupEmail))
                                                <div class="col-sm-6 form-group form-group-select2">
                                                    <label class="control-label">{{ trans('manage_time::view.Group email need notified') }}</label>
                                                    <div class="input-box">
                                                        <select name="group_email[]" class="form-control group-email" multiple>
                                                            @foreach($groupEmail as $value)
                                                                <option value="{{$value}}">{{substr($value, 0, strrpos($value, '@'))}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <div class="input-box">
                                                    <div class="checkbox">
                                                        <label>
                                                            <input type="checkbox" name="sendMail" id="sendMail" value="1">{{ trans('files::view.Gửi Mail') }} 
                                                        </label>    
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /.box-body -->

                                    <div class="box-footer">
                                        <button type="submit" class="btn btn-primary submit-save" onclick="return checkFileForm('{{ $urlCheckCodeFileExist }}');"><i class="fa fa-floppy-o"></i> {{ trans('files::view.Update') }} </button>
                                        <input type="hidden" id="check_submit" name="" value="0">
                                        <button type="submit" class="btn btn-danger displayNone showFormMail" onclick="return checkClickEmail('{{ $urlCheckCodeFileExist }}');" ><i class="fa fa-envelope-square"></i> {{ trans('files::view.Send Mail') }}</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
            <!-- /.box-body -->
        </div>
        <!-- /. box -->
    </div>
</div>
    <!-- /.row -->
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.1.20/jquery.fancybox.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/script.js') }}"></script>
    <script src="https://cdn.ckeditor.com/4.12.1/standard/ckeditor.js"></script>
    <script src="{{ CoreUrl::asset('team/js/script.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            CKEDITOR.replace('quote_text').on('key',
                function(e){
                    setTimeout(function(){
                        quote_text = document.getElementById('quote_text').value = e.editor.getData();
                        if (quote_text.length == '') {
                            $('#quote-error').show();
                        } else {
                            $('#quote-error').hide();
                        }
                    },10);
                }
            );

            CKEDITOR.replace('content').on('key',
                function(e){
                    setTimeout(function(){
                        content = document.getElementById('content').value = e.editor.getData();
                        if (content.length == '') {
                            $('#content-error').show();
                        } else {
                            $('#content-error').hide();
                        }
                    },10);
                }
            );

            var error = "{{ trans('files::view.The field is required') }}";
            var idTeam = $('#groupTeam').val();
            var code = $('#groupTeam option:selected').attr("code");
            var type = $('#type').val();
            var valSelectEmp = $('#selectEmp :selected').attr('attrnew');
            var nameTeam = $('#groupTeam :selected').text();
            var type_file = $('.type_file').val();
            var numberGo = $("input[name=numberGo]").val();
            var tick = $("input[name=tick]:checked").val();
            $("#tickSinger").html(valSelectEmp);

            if (type_file == '<?php echo $qd;?>') {
                $('#groupTeam').attr('disabled', true);
                $(".selectBod").css({"display":"none"});
                $('#appendTeam').html('<input type="hidden" name="groupTeam" value="<?php echo ManageFileText::TEAMBOD;?>">');
                $("#div-content").hide();
                $(".contenRequired").removeClass('displayNone');
            }

            if (tick == 2) {
                $("#tickSinger").css("display", "none");
            }

            $("#form-register-file").validate({
                rules: {
                    codeText: "required",
                    groupTeam: "required"
                },
                messages: {
                    codeText: error,
                    groupTeam: error
                }
            });

            var date = new Date();
            date.setDate(date.getDate());
            $('#dateRelease').datepicker({
                autoclose: true,
                format: 'yyyy-mm-dd',
                defaultDate: date,
                weekStart: 1,
                todayHighlight: true,
                onSelect: function () {
                    selectedDate = $.datepicker.formatDate("yyyy-mm-dd", $(this).datepicker('getDate'));
                }
            });

            $('.dateOption2').datepicker({
                autoclose: true,
                format: 'yyyy-mm-dd',
                weekStart: 1,
                todayHighlight: true,
                onSelect: function () {
                    selectedDate = $.datepicker.formatDate("yyyy-mm-dd", $(this).datepicker('getDate'));
                }
            });

            $('#dateOption1').datepicker({
                autoclose: true,
                format: 'yyyy-mm-dd',
                weekStart: 1,
                todayHighlight: true,
                onSelect: function () {
                    selectedDate = $.datepicker.formatDate("yyyy-mm-dd", $(this).datepicker('getDate'));
                }
            });

            $('.codeText').change(function() {
                codeText = $('.codeText').val();
                if (codeText.trim() == '') {
                    $('#codeText-error').show();
                } else {
                    $('#codeText-error').hide();
                }
            });

            $('#groupTeam').change(function() {
                checkGroupTeam = $('#groupTeam :selected').val();
                if (checkGroupTeam == 0) {
                    $('#groupTeam-error').show();
                    idTeam = $(this).val();
                    code = $('#groupTeam option:selected').attr("code");
                    nameTeam = $('#groupTeam :selected').text().trim();
                    numberGo = $("input[name=numberGo]").val();
                    $('.codeText').val(numberGo+'/CV-RIKKEI-'+nameTeam);
                    loadLeader(idTeam, code);
                } else {
                    $('#groupTeam-error').hide();
                    idTeam = $(this).val();
                    code = $('#groupTeam option:selected').attr("code");
                    nameTeam = $('#groupTeam :selected').text().trim();
                    numberGo = $("input[name=numberGo]").val();
                    $('.codeText').val(numberGo+'/CV-RIKKEI-'+nameTeam);
                    loadLeader(idTeam, code);
                }

            });

            $('#type').change(function() {
                var valType = $(this).val();
                if (valType == 1) {
                    window.location.href = '{{ route("file::file.add", $typeCvden) }}';
                    $('div.option2').addClass('displayNone');
                    $('div.option1').removeClass('displayNone');
                }
            });

            $('#file_content').change(function() {
                $('.fileError').html('');
                nameFileDetail = '{{ $itemNumberGo->file_content}}';
                messErrFile = '{{ trans('files::view.Only allow file csv, xlsx, doc, xls, pdf') }}';
                fileExtension = ['csv', 'xlsx', 'xls', 'doc', 'pdf'];
                if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
                    $('.fileError').append('<div class="error">'+ messErrFile +'</div>');
                    $('.detaiFile').html(nameFileDetail);
                    $('#file_content-error').hide();
                } else {
                    $('.fileError').html('');
                    $('.detaiFile').html('');
                }
            })
            
            $('.type_file').change(function() {
                var valType = $(this).val();
                if (valType == 1) {
                    nameTeam = $('#groupTeam option[value="<?php echo ManageFileText::TEAMBOD;?>"]').text();
                    getCeoCompany();
                    $('#groupTeam option[value="<?php echo ManageFileText::TEAMBOD;?>"]').attr('selected', true);
                    $('#appendTeam').html('<input type="hidden" name="groupTeam" value="<?php echo ManageFileText::TEAMBOD;?>">');
                    $('.codeText').val(numberGo+'/QĐ-RIKKEI-'+nameTeam);
                    $('.hiddenEm').html('');
                    $(".selectBod").css({"display":"none"});
                    $("#div-content").hide();
                    $(".contenRequired").removeClass('displayNone');
                              } 
                if (valType == 2) {
                    loadLeader(idTeam, code);
                    $('#appendTeam').html('');
                    $('#groupTeam').attr('disabled', false);
                    $('.codeText').val('<?php echo $itemNumberGo->code_file;?>');
                    $(".selectBod").css({"display":"block"});
                    $("#div-content").show();
                    $(".contenRequired").addClass('displayNone');
                }
            });

            $("#sendMail").click(function() {
                if($('#sendMail').is(':checked')) {
                    $('.showFormMail').removeClass('displayNone');
                    $('.submit-save').addClass('displayNone');
                    $('.select-search-employee').html('');
                } else {
                    $('.showFormMail').addClass('displayNone');
                    $('.submit-save').removeClass('displayNone');
                    $('.select-search-employee').html('');
                }
            });

            $(".group-email").select2({
                tags: true,
                placeholder: "<?php echo trans('files::view.Group email') ?>",
            });
        });

        $("#selectEmp").change(function() {
            valSelectEmp = $('#selectEmp :selected').attr('attrnew');
            if (valSelectEmp == undefined) {
                $("#tickSinger").html('');
            } else {
                $("#tickSinger").html(valSelectEmp);
            }
        })


        $("input[name='tick']").change(function() { 
            val = $('#selectEmp :selected').val();
            if($(this).is(":checked") && $(this).val() == 1) {
                $("#tickSinger").css("display", "block");
            } else if($(this).is(":checked") && $(this).val() == 2) {
                $("#tickSinger").css("display", "none");
            }
        });

        $('.dateOption2').change(function() {
            dateFile = $('.dateOption2').val();
            if (dateFile.trim() == '') {
                $('#date_file-error').show();
            } else {
                $('#date_file-error').hide();
            }
        });

        $('#dateRelease').change(function() {
            dateFile = $('#dateRelease').val();
            if (dateFile.trim() == '') {
                $('#date_released-error').show();
            } else {
                $('#date_released-error').hide();
            }
        });

        $('.codeText').change(function() {
            codeText = $('.codeText').val();
            if (codeText.trim() == '') {
                $('#codeText-error').show();
            } else {
                $('#codeText-error').hide();
            }
        });

        $('#groupTeam').change(function() {
            setTimeout(function() { 
                selectEmp = $("#selectEmp :selected").val();
                if (selectEmp.trim() == '') {
                    $('#signer-error').show();
                } else {
                    $('#signer-error').hide();
                }
            }, 500); 
        });

        $('.select-search-employee').change(function() {
            employee = $('.select-search-employee').val();
            if (employee == null) {
                $('.search-employee-error').show();
            } else {
                $('.search-employee-error').hide();
            }
        });

        signer = $('#selectEmp :selected').val();
        if (signer.trim() == '') {
            $('.signer-error').show();
        } else {
            $('.signer-error').hide();
        }

        $('#file_content').change(function() {
            file_content = $('#file_content').val();
            if (file_content == null) {
                $('#file_content-error').show();
            } else {
                $('#file_content-error').hide();
            }
        });
        /*get leader team*/
        function loadLeader(idTeam, code) {
            var getLeaderTeam = '{{ route("file::file.get-leader-team") }}';
            $.ajax ({
                url: getLeaderTeam,
                method : 'GET',
                data: {idTeam: idTeam, code: code},
                cache: false,
                success: function(data) {
                    if (data.length > 0) {
                        $('#selectEmp').html('');
                        $.each(data, function(index, item) {
                            $('#selectEmp').append($('<option>', { 
                                value : item.id,
                                text : item.name,
                                attrnew : item.position
                            }));
                        });
                    } 
                    else if (data.length == undefined) {
                        $('#selectEmp').html($('<option>', { 
                            value: data.id,
                            text : data.name,
                            attrnew : data.position
                        }));
                    } else {
                        $('#selectEmp').html($('<option>', { 
                            value: '',
                            text : '',
                            attrnew : ''
                        }));
                    }

                    getAtttSelectEmp = $("#selectEmp :selected").attr('attrnew');
                    $("#tickSinger").text(getAtttSelectEmp);
                }
            });
        }

        /*get TGD and CEO */
        function getCeoCompany() {
            var getCeoCompany = '{{ route("file::file.get-ceo-company") }}';
            $.ajax ({
                url: getCeoCompany,
                method : 'GET',
                cache: false,
                success: function(data) {
                    $('#selectEmp').html('');
                    if (data.length > 0) {
                        $.each(data, function(index, item) {
                            $('#selectEmp').append($('<option>', { 
                                value : item.id,
                                text : item.name,
                                attrnew : item.position
                            }));
                        });
                        getAtttSelectEmp = $("#selectEmp :selected").attr('attrnew');
                        $("#tickSinger").text(getAtttSelectEmp);
                    }

                }
            });
        }
        /*check validate form click button save*/
        function checkFileForm(urlCheckCodeFileExist) {
            var status = 1;
            $('#check_submit').val(1);

  /*          if (checkExistRegister(urlCheckCodeFileExist)) {
                status = 0;
            }*/

            codeText = $('.codeText').val();
            if (codeText.trim() == '') {
                $('#codeText-error').show();
                status = 0;
            }

            date_released = $('#datetimepicker-date_released').datepicker('getDate');;
            if (date_released == null) {
                $('#date_released-error').show();
                status = 0;
            }

            date_file = $('#datetimepicker-date_file').datepicker('getDate');;
            if (date_file == null) {
                $('#date_file-error').show();
                status = 0;
            }

            selectEmp = $("#selectEmp :selected").val();
            if (selectEmp.trim() == '') {
                $('#signer-error').show();
                status = 0;
            }

            quote = CKEDITOR.instances["quote_text"].getData();
            if (quote.trim() == '') {
                $('#quote-error').show();
                status = 0;
            }

            checkGroupTeam = $('#groupTeam :selected').val();
            if (checkGroupTeam == 0) {
                $('#groupTeam-error').show();
            }

            if($('.type_file').val() == '<?php echo ManageFileText::TYPEFILE_CV;?>') {            
                content = CKEDITOR.instances["content"].getData();
                if (content.trim() == '') {
                    $('#content-error').show();
                    status = 0;
                }
            }

            if($('.type_file').val() == '<?php echo ManageFileText::TYPEFILE_QD;?>') {            
                file_content = $('#file_content').val();
                detaiFile = $('.detaiFile').text();
                if (file_content.trim() == '' && detaiFile.trim() == '') {
                    $('#file_content-error').show();
                    status = 0;
                }
            }

            if (status == 0) {
                return false;
            }
            return true;
        }

        /*check validate form click button email*/
        function checkClickEmail(urlCheckCodeFileExist) {
            var status = 1;
            $('#check_submit').val(1);

/*            if (checkExistRegister(urlCheckCodeFileExist)) {
                status = 0;
            }
*/
            date_released = $('#datetimepicker-date_released').datepicker('getDate');;
            if (date_released == null) {
                $('#date_released-error').show();
                status = 0;
            }

            codeText = $('.codeText').val();
            if (codeText.trim() == '') {
                $('#codeText-error').show();
                status = 0;
            }

            date_file = $('#datetimepicker-date_file').datepicker('getDate');;
            if (date_file == null) {
                $('#date_file-error').show();
                status = 0;
            }

            selectEmp = $("#selectEmp :selected").val();
            if (selectEmp.trim() == '') {
                $('#signer-error').show();
                status = 0;
            }

            quote = CKEDITOR.instances["quote_text"].getData();
            if (quote.trim() == '') {
                $('#quote-error').show();
                status = 0;
            }

            checkGroupTeam = $('#groupTeam :selected').val();
            if (checkGroupTeam == 0) {
                $('#groupTeam-error').show();
            }

            selectEmployee = $('.select-search-employee').val();
            if (selectEmployee == null) {
                $('.search-employee-error').show();
                status = 0;
            }

            if($('.type_file').val() == '<?php echo ManageFileText::TYPEFILE_CV;?>') {            
                content = CKEDITOR.instances["content"].getData();
                if (content.trim() == '') {
                    $('#content-error').show();
                    status = 0;
                }
            }

            if($('.type_file').val() == '<?php echo ManageFileText::TYPEFILE_QD;?>') {            
                file_content = $('#file_content').val();
                detaiFile = $('.detaiFile').text();
                if (file_content.trim() == '' && detaiFile.trim() == '') {
                    $('#file_content-error').show();
                    status = 0;
                }
            }

            if (status == 0) {
                return false;
            }
            return true;
        }

/*        function checkExistRegister(urlCheckCodeFileExist) {
            var isExist = false;
            var codeText = $(".codeText").val();
            $.ajax({
                type: "GET",
                url: urlCheckCodeFileExist,
                dataType: 'json',
                async: false,
                data: {
                    codeText: codeText,
                },
                success: function (data) {
                    $('.codeError').html('');
                    if (data.length > -1) {
                        isExist = false;
                    } else {
                        $('.codeError').append('<div class="error">'+ data.error +'</div>');
                        isExist = true;
                    }
                }
            });
            return isExist;
        }*/

        $(function() {
            $('.select-search-employee').selectSearchEmployee();
        });
    </script>
@endsection
