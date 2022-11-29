<?php 
    use Rikkei\Ticket\Model\Ticket;
?>
<!-- Ticket change priority modal -->
<div class="modal fade in" id="modal_change_priority">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('ticket::it.request.change-priority') }}" accept-charset="UTF-8" class="ng-pristine ng-valid" id="form-change-priority">
                {!! csrf_field() !!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">{{ trans('ticket::view.Change priority') }}</h4>
                </div>
                <div class="modal-body">
                    <div id="assign_body">
                        <div class="form-group form-group-select2">
                            <div class="form-group" >
                                <label class="control-label">{{ trans('ticket::view.Priority') }}</label>
                                <div class="input-box">
                                    <select name="priority" id="change_priority" class="form-control select-search">
                                        <option value="{{ Ticket::PRIORITY_LOW }}" <?php if($ticket->ticket_priority == Ticket::PRIORITY_LOW) { ?>selected <?php } ?>>{{ trans('ticket::view.Low') }}</option>
                                        <option value="{{ Ticket::PRIORITY_NORMAl }}" <?php if($ticket->ticket_priority == Ticket::PRIORITY_NORMAl) { ?>selected <?php } ?>>{{ trans('ticket::view.Normal') }}</option>
                                        <option value="{{ Ticket::PRIORITY_HIGH }}" <?php if($ticket->ticket_priority == Ticket::PRIORITY_HIGH) { ?>selected <?php } ?>>{{ trans('ticket::view.Hight') }}</option>
                                        <option value="{{ Ticket::PRIORITY_EMERGENCY }}" <?php if($ticket->ticket_priority == Ticket::PRIORITY_EMERGENCY) { ?>selected <?php } ?>>{{ trans('ticket::view.Emergency') }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label required">{{ trans('ticket::view.Change reason') }} <em>*</em></label>
                                <div class="input-box">
                                    <textarea id="reason_change_priority" name="reason_change_priority" class="form-control required" style="height: 100px; max-width: 100%;"></textarea>
                                </div>
                            </div>
                            <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" onclick="closeChangePriority();" data-dismiss="modal" id="dismis4">{{ trans('ticket::view.Close') }}</button>
                    <button type="submit" class="btn btn-primary pull-right" id="submt2">{{ trans('ticket::view.Save') }}</button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
<!-- /.Ticket change priority modal -->