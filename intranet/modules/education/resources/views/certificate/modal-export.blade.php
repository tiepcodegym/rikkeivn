<div class="modal" tabindex="-1" role="dialog" id="modal-confirm-export">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{!! trans('education::view.Confirm Export') !!}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -33px;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>{!! trans('education::view.messages.confirm export') !!}</p>
            </div>
            <div class="modal-footer">
                <form method="post" action="{!! route('education::education.certificates.export') !!}"class="no-validate" id="form_export">
                    {{csrf_field()}}
                    <input type="hidden" value="{{$teamIdCurrent}}" name="team_id">
                    @if(isset($dataSearch['status']))
                        <input type="hidden" value="{{$dataSearch['status']}}" name="status">
                    @endif
                    @if(isset($dataSearch['name']))
                        <input type="hidden" value="{{$dataSearch['name']}}" name="name">
                    @endif
                    @if(isset($dataSearch['start_at']))
                        <input type="hidden" value="{{$dataSearch['start_at']}}" name="start_at">
                    @endif
                    @if(isset($dataSearch['end_at']))
                        <input type="hidden" value="{{$dataSearch['end_at']}}" name="end_at">
                    @endif
                    <button type="submit" class="btn btn-primary">{!! trans('education::view.buttons.export') !!}</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{!! trans('education::view.buttons.close modal') !!}</button>
                </form>
            </div>
        </div>
    </div>
</div>
