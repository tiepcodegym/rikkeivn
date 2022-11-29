
<div class="modal fade" id="modal-content" tabindex="-1" role="dialog"  data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content"  >
            <div class="modal-body bg-wrapper">
                <h4 class="modal-title">{{ trans('resource::view.Content of request') }}</h4>
                <textarea id="content"  class="text-editor" name="content" >{{ $checkEdit ? $request->content: ''}}</textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary save-content" data-dismiss="modal">{{ Lang::get('resource::view.Close') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
