<div class="modal fade modal-default" id="modal_export_me_reward">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ trans('me::view.Export excel') }}</h4>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label>
                            <input type="radio" name="is_all" value="0" checked /> 
                            {{ trans('me::view.export_only_selected') }}
                        </label>
                        {!! str_repeat('&nbsp;', 8) !!}
                        <label>
                            <input type="radio" name="is_all" value="1" /> 
                            {{ trans('me::view.export_all') }}
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-close pull-left btn-default" data-dismiss="modal">{{ trans('core::view.Close') }}</button>
                <button type="button" class="btn btn-outline btn-ok btn-primary" id="btn_export_me_reward"
                        data-url="{{ route('project::me.reward.export_data') }}">
                    {{ trans('core::view.Export') }} &nbsp;
                    <i class="fa fa-download"></i>
                </button>
            </div>
        </div>
    </div>
</div>