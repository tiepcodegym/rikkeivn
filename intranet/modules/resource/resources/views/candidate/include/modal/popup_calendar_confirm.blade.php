<div class="modal fade" id="modal-calendar-confirm" tabindex="-1" role="dialog"  data-keyboard="false" >
    <div class="modal-dialog">
        <div class="modal-content"  >
            <div class="modal-header">
                <h3 class="modal-title">Yêu cầu xác nhận</h3>
            </div>
            <div class="modal-body">
                Bạn có muốn tạo mới calendar không?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn pull-left" onclick="noShowFormCalendar();">{{ trans('resource::view.No') }}</button>
                <button type="button" class="btn btn-primary" onclick="showFormCalendar();">
                    <span>
                        {{ trans('resource::view.Yes') }}
                        <i class="fa fa-spin fa-refresh hidden"></i>
                    </span>
                </button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>

<div class="modal fade" id="modal-calendar-create" data-backdrop="static" tabindex="-1" role="dialog"  data-keyboard="false" >
    <div class="modal-dialog">
        <div class="modal-content"  >
            <div class="modal-header">
                <h3 class="modal-title">{{ trans('resource::view.Candidate interview schedule') }}</h3>
            </div>
            <div class="modal-body min-height-150">
                <div class="alert alert-warning alert-dismissible hidden">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h4><i class="icon fa fa-warning"></i> {{ trans('resource::view.Notice!') }}</h4>
                    <span class="content"></span>
                </div>
                <div class="loader-container">
                    <h4 class="display-inline-block">Chờ xác thực tài khoản</h4>
                    &nbsp;&nbsp;
                    <div class="loader display-inline-block"></div>
                </div>
                <div class="error hidden">
                    <h4></h4>
                </div>
                <div class="row form-calendar hidden">
                    <div class="col-md-12">
                        <div class="form-group position-relative ">
                            <div>
                                <span>                                  
                                    <input type="text" id="calendar-title" class="form-control input-required" value="" placeholder="{{ trans('resource::view.Add title') }}" />
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <div class="row">
                                <div class="input-group col-md-6">
                                  <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                  </div>
                                  <input type="text" class="form-control date start-date input-required">
                                </div>
                                <div class="input-group col-md-6 end-date-group">
                                  <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                  </div>
                                  <input type="text" class="form-control date end-date input-required">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group margin-bottom-50">
                            <div class="row">
                                <div class="col-md-6">
                                    <textarea id="calendar-description" class="form-control resize-none" rows="5" placeholder="{{ trans('resource::view.Add description') }}"></textarea>
                                </div>
                                <div class="col-md-6 select-container">
                                    <select id="calendar-room">
                                        <option value="0">{{ trans('resource::view.Select meeting room') }}</option>
                                    </select>
                                    <i class="fa fa-refresh fa-spin loading-room hidden"></i>
                                    <div>&nbsp;</div>
                                    <select id="calendar-interviewer" class="" multiple="multiple" 
                                            data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}">
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn pull-left btn-cancel-create_calendar" data-dismiss="modal">{{ trans('resource::view.Cancel') }}</button>
                <button type="button" class="btn btn-primary btn-lg btn-create-calendar hidden" onclick="saveCalendar(this);">
                    <span>
                        {{ trans('resource::view.Save') }}
                    </span>
                </button>
                <button type="button" class="btn btn-primary btn-lg btn-create-calendar-fake hidden" disabled>
                    <span>
                        {{ trans('resource::view.Save') }}
                        <i class="fa fa-spin fa-refresh"></i>
                    </span>
                </button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
