<?php 
    use Rikkei\Ticket\Model\Ticket;
?>
<div class="modal fade in" id="modal_close_request">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('ticket::it.request.change-status-resolved') }}" accept-charset="UTF-8" class="ng-pristine ng-valid" id="form-close-request">
                {!! csrf_field() !!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="closeRequest();"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">{{ trans('ticket::view.Rating request') }}</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <div class="radio" id="satisfied">
                            <input type="radio" class="minimal" checked name="rating" value="{{ Ticket::RATING_SATISFIED }}">&nbsp;<b>{{ trans('ticket::view.Satisfied') }}</b>
                        </div>
                        <div class="radio" id="unsatisfied">
                            <input type="radio" class="minimal" name="rating" value="{{ Ticket::RATING_UNSATISFIED }}">&nbsp;<b>{{ trans('ticket::view.Unsatisfied') }}</b>
                        </div>
                    </div>

                    <div class="form-group" id="box_reason_unsatisfied" style="display: none;">
                        <label class="control-label" style="font-weight: 100">{{ trans('ticket::view.Unsatisfied reason') }}<em style="color: red;">&nbsp;*</em></label>
                        <div class="input-box">
                            <textarea name="reason_unsatisfied" id="reason_unsatisfied" class="form-control required" style="height: 100px; max-width: 100%;"></textarea>
                        </div>
                    </div>
                    <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
                    <input type="hidden" name="status" value="{{ $ticket->ticket_status }}">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" onclick="closeRequest();" data-dismiss="modal">{{ trans('ticket::view.Close') }}</button>
                    <button type="submit" class="btn btn-primary pull-right">{{ trans('ticket::view.Save') }}</button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>