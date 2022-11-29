<?php

use Rikkei\Core\View\CoreUrl;
use Rikkei\Core\View\View;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\Employee;
use Rikkei\Ot\Model\OtRegister;
?>

<div class="modal fade" id="register-preview">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h3 class="box-title">{{ trans('ot::view.Register information') }}</h3>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-3 form-group">
                                    <!--register status-->
                                    @if ($empType == OtRegister::REGISTER)
                                        @if ($pageType == OtRegister::WAIT)
                                        <div class="alert bg-aqua status">
                                            <p class="text-center" id="preview_status"></p>
                                        </div>
                                        @elseif ($pageType == OtRegister::DONE)
                                        <div class="alert bg-gray status">
                                            <p class="text-center" id="preview_status"></p>
                                        </div>
                                        @elseif ($pageType == OtRegister::REJECT)
                                        <div class="alert bg-red status">
                                            <p class="text-center" id="preview_status"></p>
                                        </div>      
                                        @else
                                        <div class="alert bg-orange status">
                                            <p class="text-center" id="preview_status"></p>
                                        </div>
                                        @endif
                                    @else
                                        @if ($pageType == OtRegister::WAIT)
                                        <div class="alert bg-aqua status">
                                            <p class="text-center" id="preview_status"></p>
                                        </div>
                                        @elseif ($pageType == OtRegister::DONE)
                                        <div class="alert bg-gray status">
                                            <p class="text-center" id="preview_status"></p>
                                        </div>
                                        @else
                                        <div class="alert bg-red status">
                                            <p class="text-center" id="preview_status"></p>
                                        </div>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <!--employee name-->
                                    <label class="control-label">{{ trans('ot::view.Applicant') }}</label>
                                    <div class="input-box">
                                        <input type="text" id="employee_name" name="employee_name" class="form-control" value="" disabled />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <!--employee code-->
                                    <label class="control-label">{{ trans('ot::view.Employee Code') }}</label>
                                    <div class="input-box">
                                        <input type="text" id="employee_code" name="employee_code" class="form-control" value="" disabled />
                                    </div>
                                </div>
                            </div>
                            <br/>
                            <div class="row">
                                <div class="col-md-6">
                                    <!--employee position-->
                                    <label class="control-label">{{ trans('ot::view.Position') }}</label>
                                    <div class="input-box">
                                        <input type="text" id="employee_position" name="employee_position" class="form-control" value="" disabled />
                                    </div>
                                </div>
                            </div>
                            <br/>
                            <div class="row">
                                <div class="col-md-6">
                                    <!--register project-->
                                    <label class="control-label">{{ trans('ot::view.OT Project') }}</label>
                                    <div class="input-box">
                                        <input type="text" id="register_project" name="register_project" class="form-control" value="" disabled />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <!--register approver-->
                                    <label class="control-label">{{ trans('ot::view.Approver') }}</label>
                                    <div class="input-box">
                                        <input type="text" id="register_approver" name="register_approver" class="form-control" value="" disabled />
                                    </div>
                                </div>                                
                            </div>
                            <br/>
                            <div class="row">
                                <div class="col-md-6">
                                    <!--register start time-->
                                    <label class="control-label">{{ trans('ot::view.OT from') }}</label>
                                    <div class="input-box">
                                        <input type="text" id="register_start_at" name="register_start_at" class="form-control" value="" disabled />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <!--register end time-->
                                    <label class="control-label">{{ trans('ot::view.OT to') }}</label>
                                    <div class="input-box">
                                        <input type="text" id="register_end_at" name="register_end_at" class="form-control" value="" disabled />
                                    </div>
                                </div>
                            </div>
                            <br/>
                            <div class="row">
                                <div class="col-md-12">
                                    <label class="control-label">{{ trans('ot::view.OT reason') }}</label>
                                    <div class="input-box">
                                        <textarea rows="7" id="register_reason" name="register_reason" class="form-control textarea" readonly=""></textarea>
                                    </div>
                                </div>
                            </div>
                            <!--todo: reject comment-->
                        </div>
                        <!-- /.box-body -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a id="view_detail" class="btn btn-primary pull-left">{{ trans('ot::view.View details') }}</a>
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('ot::view.Close') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div>
</div><!-- /.modal-dialog -->
