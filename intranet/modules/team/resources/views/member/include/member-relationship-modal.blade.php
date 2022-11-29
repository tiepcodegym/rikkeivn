<div class="modal" tabindex="-1" role="dialog" id="modal-confirm-export">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{!! trans('team::view.Confirm Export') !!}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -33px;">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>{!! trans('team::view.You will export personal information of some members of the employee family') !!}</p>
            </div>
            <div class="modal-footer">
                <form method="post" action="{!! route('team::team.member.export_member.relationship') !!}"
                      class="no-validate" id="form_export_relationship">
                    <input type="hidden" id="dataTeamId" name="dataTeamId" value="{{$teamIdCurrent}}">
                    <input type="hidden" id="statusWork" name="statusWork" value="{{$statusWork}}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <button type="submit" class="btn btn-primary">{!! trans('team::view.Export') !!}
                
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{!! trans('team::view.Close export') !!}</button>
                </form>
            </div>
        </div>
    </div>
</div>
