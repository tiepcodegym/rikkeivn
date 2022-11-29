<div class="myModal_group_event modal fade row modal-search" id="modal_form_implements"
     route="{!! route('welfare::welfare.formImplements.list') !!}">
    <div class="modal-dialog modal-lg">
        <div class="modal-content col-md-12">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{{trans('welfare::view.Create_wel_form_implements')}}</h4>
            </div>
            <div class="modal-body">
                <div class="">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="box box-info">
                        <div class="box-body">
                            <div class="form-horizontal form-label-left">
                                <div class="form-group">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <label class="col-md-3 control-label required"
                                           aria-required="true">{{ trans('welfare::view.Wel_form_implements') }}
                                        <em>*</em></label>
                                    <div class="input-box col-md-7">
                                        <input type="text" name="formImplement" class="form-control"
                                               aria-required="true"
                                               placeholder="{{trans('welfare::view.Wel_form_implements')}}">
                                        <p class="massage_exist" style="color: red"
                                           hidden> {{trans('welfare::view.Validate name of event group')}}</p>
                                        <p class="massage_null" style="color: red"
                                           hidden> {{ trans('welfare::view.Wel_form_implements')}} {{trans('welfare::view.Not_Null')}}</p>
                                    </div>
                                    <button type="button" class="btn-save-event-popup btn-add"
                                            name="submit" value="Save" id="btn-save-group"
                                            tableid="table_form_implement"
                                            route="{{ URL::route('welfare::welfare.formImplements.save') }}"
                                            selector="event[wel_form_imp_id]" modal="modal_form_implements"
                                            message="{{trans('welfare::view.Wel_form_implements')}} {{trans('welfare::view.Not_Null')}}"
                                            inputname="formImplement">{{ trans('welfare::view.Add New') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="">
                    <h4 class="modal-title">{{ trans('welfare::view.List_form_implement') }}</h4>
                </div>
                <div class="box box-info">
                    <div class="box-body">
                        <div class="table-responsive col-md-12">
                            <div class="row">
                                <div class="col-sm-12">
                                    <table class="table table-bordered" id="table_form_implement" width="100%">
                                        <thead>
                                        <tr>
                                            <th>{{trans('welfare::view.Form_implement_name')}}</th>
                                            <th></th>
                                        </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button table="table_form_implement" id="choiceBtnGroupWel" type="button" modal="modal_form_implements"
                        class="btn btn-primary choiceBtnGroupWel">{{ trans('welfare::view.Choose') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
<!-- modal delete cofirm -->
<div class="modal-delete-confirm modal fade @yield('confirm_class', 'modal-danger')" id="modal-delete-formImplement"
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
                <button type="button" class="btn btn-outline btn-delete btn-delete-fee-more">{{ trans('welfare::view.Ok') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div> <!-- modal delete cofirm -->
