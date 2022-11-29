<div id="myModal_group_event" class="modal fad myModal_group_event modal-search" role="dialog"
     route="{!! route('welfare::welfare.group.list') !!}">
    <div class="modal-dialog modal-lg">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    <div class="box box-info">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i
                                        class="fa fa-plus-square"></i> {{ trans('welfare::view.Create_group_name') }}
                            </h3>
                        </div>
                        <div class="box-body">
                            <div class="form-horizontal form-label-left">
                                <div class="form-group">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <label class="col-md-3 control-label required"
                                           aria-required="true">{{trans('welfare::view.Group_name')}}<em>*</em></label>
                                    <div class="input-box col-md-7">
                                        <input type="text" name="groupEvent" class="form-control" aria-required="true"
                                               placeholder="{{trans('welfare::view.Group_name')}}" >
                                        <p class="massage_exist" style="color: red" hidden> {{trans('welfare::view.Validate name of event group')}}</p>
                                        <p class="massage_null" style="color: red" hidden> {{trans('welfare::view.Not_Null')}}</p>
                                    </div>
                                    <button type="button" class="btn-save-event-popup btn-add"
                                            name="submit" value="Save" id="btn-save-group" tableid="table_group_event"
                                            route="{{ URL::route('welfare::welfare.group.save') }}"
                                            selector="event[welfare_group_id]" modal="myModal_group_event"
                                            message="{{ trans('welfare::view.Group_name')}} {{trans('welfare::view.Not_Null')}}"
                                            inputname="groupEvent">{{ trans('welfare::view.Add New') }}
                                            <span class="_uploading hidden" id="uploading">&nbsp;<i class="fa fa-spin fa-refresh"></i></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="box box-info">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i
                                        class="fa fa-list"></i> {{ trans('welfare::view.List_group_wel_name') }}</h3>
                        </div>
                        <div class="box-body">
                            <div class="table-responsive col-md-12">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <table class="table table-bordered" id="table_group_event">
                                            <thead style="color:white">
                                            <tr>
                                                <th>{{trans('welfare::view.Group_name')}}</th>
                                                <th class="sorting_1"></th>
                                            </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button table="table_group_event" id="choiceBtnGroupWel" type="button" modal="myModal_group_event"
                        class="btn btn-primary choiceBtnGroupWel">{{ trans('welfare::view.Choose') }}</button>
            </div>
        </div>
    </div>
</div>
<!-- modal delete cofirm -->
<div class="modal-delete-confirm modal fade @yield('confirm_class', 'modal-danger')" id="modal-delete-group"
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
                <button type="button" class="btn btn-outline btn-delete-fee-more">{{ trans('welfare::view.Ok') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div> <!-- modal delete cofirm -->
<!-- modal delete success -->
<div class="modal fade" id="delete-success-modal" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">{{trans('welfare::view.Message_notification')}}</h4>
            </div>
            <div class="modal-body">
                <p>{{trans('welfare::view.Delete_success_message')}}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div><!-- end modal success m-->