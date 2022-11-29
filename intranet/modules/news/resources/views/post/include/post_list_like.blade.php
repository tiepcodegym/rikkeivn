<div id="showLike" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">{!! trans('news::view.All') !!}</h4>
            </div>
            <div class="modal-body">
                <div data-like-dom="list">
                    <div class="col-md-6 show-like">
                        <img src="" class="avartar" />
                        <span class="name">{name}</span>
                        <hr style="margin-top: 10px; margin-bottom: 10px;">
                    </div>
                </div>
                <div class="row text-center hidden" data-like-dom="no-list">
                    <h4>{!! trans('news::view.No one likes') !!}</h4>
                </div>
            </div>
        </div>
    </div>
</div>
