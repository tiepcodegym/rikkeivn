<div class="modal fade in" id="modal_change_team">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('ticket::it.request.change-team') }}" accept-charset="UTF-8" id="" class="ng-pristine ng-valid">
                {!! csrf_field() !!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">{{ trans('ticket::view.Change department IT') }}</h4>
                </div>
                <div class="modal-body">
                    <div id="assign_body">
                        <div class="form-group form-group-select2">
                            <p>{{ trans('ticket::view.Change team to?') }}</p>
                            <select id="change_team" class="form-control select-search" name="team_id"> 
                                @if(isset($getTeamsOfDeparmentIT) && count($getTeamsOfDeparmentIT))
                                    @foreach($getTeamsOfDeparmentIT as $item)
                                        <option value="{{ $item->id }}" data-leader="{{ $item->leader_id }}" <?php if($item->id == $ticket->team_id) { ?> selected <?php } ?>>{{ $item->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <label id="leader-change-error" class="error" for="leader" style="display: none;">{{ trans('ticket::view.The department has not team leader') }}</label>
                            <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
                            <input type="hidden" name="leader_id" id="leader_id_change" value="">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="closeChangeTeam();" class="btn btn-default pull-left" data-dismiss="modal" id="dismis4">{{ trans('ticket::view.Close') }}</button>
                    <button type="submit" onclick="return checkHasLeader();" class="btn btn-primary pull-right" id="submt2">{{ trans('ticket::view.Save') }}</button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>