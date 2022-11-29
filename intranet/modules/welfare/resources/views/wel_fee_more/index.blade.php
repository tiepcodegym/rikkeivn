<div class="col-md-12">
    <button id="addRow" class="btn-add" type="button"  data-toggle="tooltip"
            data-placement="bottom" title="{{ trans('welfare::view.Add New') }}">
        <span class="glyphicon glyphicon-plus"></span>
    </button>
</div>
<div class="col-sm-12">
    <table class="table table-bordered" id="table_wel_fee_more" data-list="{!! route('welfare::welfare.WelFreMore.data') !!}/{{$item['id']}}">
        <thead>
        <tr>
            <th>{{trans('welfare::view.Extra_payments_name')}}</th>
            <th>{{trans('welfare::view.Extra_payments_src')}}</th>
            <th>{{trans('welfare::view.Extra_payments_budget')}}</th>
            <th class="sorting_1"></th>
        </tr>
        </thead>
    </table>
</div>
<div class="modal-delete-confirm modal fade @yield('confirm_class', 'modal-danger')" id="modal-delete-wel-fee-more"
     tabindex="-1" role="dialog" confirm="0">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ trans('welfare::view.Confirm') }}</h4>
            </div>
            <div class="modal-body">
                <p class="text-default">{{ trans('welfare::view.Confirm_message') }}</p>
                <p class="text-change"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-close"
                        data-dismiss="modal">{{ trans('welfare::view.Close') }}</button>
                <button type="button" class="btn btn-delete-fee-more btn-outline">{{ trans('welfare::view.Ok') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div> <!-- modal delete cofirm -->


<div class="modal fade row wel_fee_more_update" id="wel_fee_more_update"
     route="{!! route('welfare::welfare.WelFreMore.save') !!}">
    <div class="modal-dialog">
        <div class="modal-content col-md-12">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{{trans('welfare::view.Create_Purpose_name')}}</h4>
            </div>
            <div class="modal-body">
                <div class="">
                    <div class="box box-info">
                        <div class="box-body">
                            <div class="form-horizontal form-label-left">
                                    <div class="form-group">
                                        <input type="hidden" name="id" value="">
                                        <input type="hidden" name="wel_id" value="">
                                        <label class="col-md-4 control-label required"
                                               aria-required="true">{{ trans('welfare::view.Extra_payments_name') }}<em>*</em></label>
                                        <div class="input-box col-md-8">
                                            <input type="text" name="Extra_payments_name" class="form-control"
                                                   aria-required="true"
                                                   placeholder="{{trans('welfare::view.Extra_payments_name')}}">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-4 control-label required"
                                               aria-required="true">{{ trans('welfare::view.Extra_payments_src') }}
                                            <em>*</em></label>
                                        <div class="input-box col-md-8">
                                            <input type="text" name="Extra_payments_src" class="form-control"
                                                   aria-required="true"
                                                   placeholder="{{trans('welfare::view.Extra_payments_src')}}">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-4 control-label required"
                                               aria-required="true">{{ trans('welfare::view.Extra_payments_budget') }}
                                            <em>*</em></label>
                                        <div class="input-box col-md-8">
                                            <input type="text" name="Extra_payments_budget" class="form-control"
                                                   aria-required="true"
                                                   placeholder="0.00" maxlength="19">
                                        </div>
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn_save_wel_fee_more  btn btn-warning"
                        name="submit" value="Save">{{ trans('welfare::view.Update') }}</button>
            </div>
        </div>
    </div>
</div>
