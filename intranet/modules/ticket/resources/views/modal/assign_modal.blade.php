<div class="modal fade in" id="modal_assign">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('ticket::it.request.assign') }}" accept-charset="UTF-8" id="" class="ng-pristine ng-valid">
                {!! csrf_field() !!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">{{ trans('ticket::view.Assignee') }}</h4>
                </div>
                <div class="modal-body">
                    <div id="assign_body">
                        <div class="form-group form-group-select2">
                            <p>{{ trans('ticket::view.Assigned to?') }}</p>
                            <select id="asssign" class="form-control select-search" name="assign_to"> 
                                @if(isset($membersOfTeamIT) && count($membersOfTeamIT))
                                    @foreach($membersOfTeamIT as $item)
                                        <option value="{{ $item->employee_id }}" <?php if($item->employee_id == $ticket->assigned_to) { ?> selected <?php } ?>>{{ $item->name }} ({{ preg_replace('/@.*/', '',$item->email) }}) </option>
                                    @endforeach
                                @endif
                            </select>
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