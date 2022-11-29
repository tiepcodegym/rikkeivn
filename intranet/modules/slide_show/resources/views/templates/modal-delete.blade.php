<!-- modal delete cofirm -->
<div class="modal fade modal-danger" id="modal-delete-slide" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ Lang::get('slide_show::view.Confirm') }}</h4>
            </div>
            <div class="modal-body">
                <p class="text-default">{{ Lang::get('slide_show::message.Are you sure delete this slide?') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-close" data-dismiss="modal">{{ Lang::get('slide_show::view.Close') }}</button>
                <button type="button" class="btn btn-outline btn-ok">{{ Lang::get('slide_show::view.OK') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div> <!-- modal delete cofirm -->