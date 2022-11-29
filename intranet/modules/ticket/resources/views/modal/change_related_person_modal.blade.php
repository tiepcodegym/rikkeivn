<?php 
    use Rikkei\Ticket\Model\Ticket;
?>
<!-- Ticket change priority modal -->
<div class="modal fade in" id="modal_change_related_person">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('ticket::it.request.change-related-person') }}" accept-charset="UTF-8" class="ng-pristine ng-valid" id="form-change-related-person">
                {!! csrf_field() !!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">{{ trans('ticket::view.Change relater person') }}</h4>
                </div>
                <div class="modal-body">
                    <div id="assign_body">
                        <div class="form-group form-group-select2">
                            <div class="form-group" >
                                <label class="control-label">{{ trans('ticket::view.Related persons') }}</label>
                                <div class="input-box">
                                    <select id="change_related_person" name="related_persons_list[]" class="form-control select-search" multiple>
                                        @if(isset($relatedPersons) && count($relatedPersons))
                                            @foreach($relatedPersons as $item)
                                                <option value="{{ $item->id }}" selected>{{ $item->name . ' (' . preg_replace('/@.*/', '',$item->email) . ')' }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal" id="dismis4">{{ trans('ticket::view.Close') }}</button>
                    <button type="submit" class="btn btn-primary pull-right" id="submt2">{{ trans('ticket::view.Save') }}</button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
<!-- /.Ticket change priority modal -->