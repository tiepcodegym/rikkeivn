<div class="modal fade in modal-warning" id="modal_delete">
    <div class="modal-dialog">
        <form action="{{ route('asset::resource.request.delete-request') }}" method="post" id="form-delete-request" class="no-disabled">
            {!! csrf_field() !!}
            <input type="hidden" name="id" value="{{ (isset($requestAsset) && $requestAsset) ? $requestAsset->id : '' }}" id="request_ids"/>
            <input type="hidden" name="url_previous" value="{{ url()->previous() }}" id="request_ids"/>
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">{{ trans('asset::view.Confirm') }}</h4>
                </div>
                <div class="modal-body">
                    <p class="text-default">{{ trans('asset::view.Are you sure to do this action?') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline pull-left" data-dismiss="modal">{{ trans('asset::view.Close') }}</button>
                    <button type="submit" class="btn btn-outline pull-right">{{ trans('asset::view.Yes') }}</button>
                </div>
            </div>
        </form>
    </div><!-- /.modal-dialog -->
</div>