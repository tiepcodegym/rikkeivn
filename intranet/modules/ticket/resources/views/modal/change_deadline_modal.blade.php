<?php
    use Carbon\Carbon;
?>
<!-- Ticket assign modal -->
<div class="modal fade in" id="modal_change_deadline">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('ticket::it.request.change-deadline') }}" accept-charset="UTF-8" id="form-change-deadline" class="ng-pristine ng-valid" onsubmit="return checkSubmitChangeDeadline();">
                {!! csrf_field() !!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="closeChangeDeadline();"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">{{ trans('ticket::view.Change deadline') }}</h4>
                </div>
                <div class="modal-body">
                    <div id="assign_body">
                        <div class="form-group form-group-select2">
                            <div class="form-group" >
                                <label class="control-label">{{ trans('ticket::view.Current deadline') }}</label>
                                <div class='input-text'>
                                    <input type='text' class="form-control" value="{{ Carbon::parse($ticket->deadline)->format('d-m-Y H:i') }}" readonly="true" />
                                </div>
                            </div>

                            <div class="form-group" >
                                <label class="control-label">{{ trans('ticket::view.Deadline want') }}</label>
                                <div class='input-group date' id='datetimepicker-change-deadline'>
                                    <input type='text' class="form-control" name="change_deadline" data-date-format="DD-MM-YYYY HH:mm" id="change_deadline" placeholder="DD-MM-YYYY HH:mm" />
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                </div>
                                <span style="color: red; font-size: 14px;" hidden="" class="error-change-deadline">Deadline tối thiểu 2h sau thời điểm thay đổi</span>
                            </div>

                            <div class="form-group">
                                <label class="control-label required">{{ trans('ticket::view.Change reason') }} <em>*</em></label>
                                <div class="input-box">
                                    <textarea id="reason_change_deadline" name="reason_change_deadline" class="form-control required" style="height: 100px; max-width: 100%;"></textarea>
                                </div>
                            </div>
                            <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
                            <input type="hidden" name="" id="check_submit_change_deadline" value="false">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" onclick="closeChangeDeadline();" data-dismiss="modal" id="dismis4">{{ trans('ticket::view.Close') }}</button>
                    <button type="submit" class="btn btn-primary pull-right" id="submt2">{{ trans('ticket::view.Save') }}</button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
<!-- /.Ticket assign modal -->