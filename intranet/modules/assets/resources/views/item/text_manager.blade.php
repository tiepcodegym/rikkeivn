@extends('layouts.default')

@section('title')
    {{ trans('manage_time::view.Business trip register') }}
@endsection
<?php
use Rikkei\Core\View\CoreUrl;
?>
@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/all.css" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('manage_asset/css/style.css') }}" />
    <style type="text/css">
    	.displayNone {
    		display: none;
    	}
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!-- Box register -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title managetime-box-title">Thông tin văn bản</h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body no-padding">
                    <div class="col-lg-10 col-lg-offset-1">
                        <form role="form" method="post" action="{{ route('file::file.postAddFile') }}" class="managetime-form-register" id="form-register" enctype="multipart/form-data" autocomplete="off">
                            {!! csrf_field() !!}
                            <div class="row">
                                <div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">
                                    <div class="box-body">
                                        <div class="row">
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <label class="control-label">Loại sổ</label>
					                                <div class="input-box">
					                                    <select id="typeText" class="form-control" name="typeText">
					                                        <option value="2">Công văn đi</option>
					                                        <option value="1">Công văn đến</option>
					                                    </select>
					                                </div>
                                            </div>
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <label class="control-label required managetime-label">Số kí hiệu <em> *</em></label>
                                                <div class="input-box">
                                                    <input type="text" name="codeText" class="form-control" value="" />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row option2">
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <label class="control-label required">Số đi <em> *</em></label>
                                                <div class="input-box">
                                                    <input type="text" name="numberOptione2" class="form-control" value="01" disabled="" />
                                                </div>
                                            </div>
                                            <div class="col-sm-6 form-group form-group-select2">
			                                  <label class="control-label">Ngày phát hành</label>
			                                    <div class="input-box">
			                                        <div class='input-group date'>
			                                            <span class="input-group-addon">
			                                                <span class="glyphicon glyphicon-calendar"></span>
			                                            </span>
			                                            <input type="text" id="dateRelease" name="dateRelease" class="form-control" value="" />
			                                        </div>
			                                    </div>
                                            </div>
                                        </div>

                                        <div class="row option1 displayNone">
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <label class="control-label required">Số đến <em> *</em></label>
                                                <div class="input-box">
                                                    <input type="text" name="numberOptione1" class="form-control" value="01" disabled="" />
                                                </div>
                                            </div>
                                            <div class="col-sm-6 form-group form-group-select2">
			                                  <label class="control-label">Ngày văn bản</label>
			                                    <div class="input-box">
			                                        <div class='input-group date'>
			                                            <span class="input-group-addon">
			                                                <span class="glyphicon glyphicon-calendar"></span>
			                                            </span>
			                                            <input type="text" name="dateOption2" class="form-control dateOption2" value="" />
			                                        </div>
			                                    </div>
                                            </div>
                                        </div>

                                        <div class="row option1 displayNone">
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <label class="control-label required">Ngày văn bản đến<em> *</em></label>
                                                <div class="input-box">
			                                        <div class='input-group date'>
			                                            <span class="input-group-addon">
			                                                <span class="glyphicon glyphicon-calendar"></span>
			                                            </span>
			                                            <input type="text" name="dateOption1" class="form-control" id="dateOption1" value="" />
			                                        </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 form-group form-group-select2">
                                            	<label class="control-label required">Đơn vị nhận văn bản <em> *</em></label>
			                                    <div class="input-box">
			                                        <input type="text" name="groupText" class="form-control" value="" />
			                                    </div>
                                            </div>
                                        </div>
                                        <div class="row option1 displayNone">
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <label class="control-label">Nơi gửi</label>
                                                <div class="input-box">
                                                    <input type="text" name="toSendOption1" class="form-control" value="" />
                                                </div>
                                            </div>
                                            <div class="col-sm-6 form-group form-group-select2">
                                            </div>
                                        </div>
                                        <div class="row option2">
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <label class="control-label">Loại văn bản</label>
					                                <div class="input-box">
					                                    <select id="typeText2" class="form-control" name="typeText">
					                                        <option value="1">Quyết định</option>
					                                        <option value="2">Công văn</option>
					                                    </select>
					                                </div>
                                            </div>
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <label class="control-label required">Nơi nhận</label>
                                                <div class="input-box">
                                                    <input type="text" name="receiverOptione2" class="form-control" value="" />
                                                </div>
                                            </div>
                                        </div>

										<div class="row option2">
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <label class="control-label required">Ngày văn bản<em> *</em></label>
			                                    <div class="input-box">
			                                        <div class='input-group date'>
			                                            <span class="input-group-addon">
			                                                <span class="glyphicon glyphicon-calendar"></span>
			                                            </span>
			                                            <input type="text" name="dateOption2" class="form-control dateOption2" value="" />
			                                        </div>
			                                    </div>
                                            </div>
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <label class="control-label required managetime-label">Đơn vị soạn thảo</label>
                                                <div class="input-box">
                                                    <select name="group_email[]" class="form-control" id="selectTeam">
                                                        @foreach ($groupTeam as $value)   
                                                            <option value="{{ $value->id }}">{{ $value->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

										<div class="row">
                                            <div class="col-sm-6 form-group form-group-select2 option2">
                                                <label class="control-label required">Người ký<em> *</em></label>
                                                <div class="input-box">
                                                    <select name="request[employee_id]" id="employee_id" class="form-control">
                                                        <option value="1">test</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 form-group form-group-select2">
                                                <label class="control-label required managetime-label">Nơi lưu bản gốc</label>
                                                <div class="input-box">
                                                    <input type="text" name="saveTextOptione2" class="form-control" value="" />
                                                </div>
                                            </div>
                                        </div>

										<div class="row option2">
			                                <div class="col-sm-6 form-group form-group-select2">
			                                    <div class="input-box">
			                                        <div class="checkbox">
			                                            <label>
			                                                <input type="radio" name="approved" value="1"> Đã ký
			                                            </label>    
			                                        </div>
			                                        <div class="checkbox">
			                                            <label>
			                                                <input type="radio" name="approved" value="2"> Chưa ký
			                                            </label>    
			                                        </div>
			                                    </div>
			                                </div>
 										</div>

                                        <div class="row">
                                        	<div class="col-sm-12 managetime-form-group">
                                            	<label class="control-label required">Trích Yếu<em> *</em></label>
	                                            <div class="input-box">
	                                                <textarea name="quote" class="form-control"></textarea>
	                                            </div>
                                        	</div>
                                        </div>

                                        <div class="form-group">
                                            <label class="control-label required">Ghi chú</label>
                                            <div class="input-box">
                                                <textarea name="note" class="form-control"></textarea>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-12 managetime-form-group">
                                                <label class="control-label required">Tệp nội dung</label>
                                                <div class="input-box">
                                                    <input type="file" name="fileContent" multiple>
                                                </div>
                                            </div>
                                            <div></div>
                                        </div>
                                    </div>
                                    <!-- /.box-body -->

                                    <div class="box-footer">
                                        <button type="submit" class="btn btn-primary" id="submit"><i class="fa fa-floppy-o"></i> Submit</button>
                                        <input type="hidden" id="check_submit" name="" value="0">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /. box -->
        </div>
    </div>
    <!-- /.row -->
@endsection

@section('script')
    <script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/icheck.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function () {
            var getLeaderTeam = '{{ asset("file/manager-text/get-leader-team") }}';
			$('#dateRelease').datepicker({
		        autoclose: true,
		        format: 'dd-mm-yyyy',
		        weekStart: 1,
		        todayHighlight: true,
		    });

			$('.dateOption2').datepicker({
		        autoclose: true,
		        format: 'dd-mm-yyyy',
		        weekStart: 1,
		        todayHighlight: true,
		    });

			$('#dateOption1').datepicker({
		        autoclose: true,
		        format: 'dd-mm-yyyy',
		        weekStart: 1,
		        todayHighlight: true,
		    });

			$('#selectTeam').change(function() {
				var idTeam = $(this).val();
                $.ajax ({
                    url: getLeaderTeam,
                    method : 'post',
                    data: {idTeam: idTeam},
                    cache: false,
                    success: function(data) {
                        console.log(data);
                    }
                });
			});

            $('#typeText').change(function() {
                var valType = $(this).val();
                if (valType == 1) {
                    $('div.option2').addClass('displayNone');
                    $('div.option1').removeClass('displayNone');
                } else {
                    $('div.option1').addClass('displayNone');
                    $('div.option2').removeClass('displayNone');
                }
            });
		});
	</script>
@endsection
