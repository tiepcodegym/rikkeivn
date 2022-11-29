

<div id="modal-review-person" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">{{ trans('welfare::view.List attach employee') }}</h4>
            </div>
            <div class="modal-body">
                <div class="box">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>{{ trans('welfare::view.Person attach') }}</th>
                                <th>{{ trans('welfare::view.Relation') }}</th>
                                <th>{{ trans('welfare::view.Birthday') }}</th>
                                <th>{{ trans('welfare::view.Rep Gender') }}</th>
                                <th>{{ trans('welfare::view.Phone') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="content-table-employee-attach">
                            
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('welfare::view.Close') }}</button>
            </div>
        </div>
    </div>
</div>

<div class="modal modal-danger fade in modal-noti-dange" id="">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ trans('welfare::view.Message_notification') }}</h4>
            </div>
        <div class="modal-body">
            <p style="padding: 10px" id="content-noti">{{ trans('welfare::view.Do you want cancel event ?') }}</p>
        </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline pull-left" data-dismiss="modal">{{ trans('welfare::view.Close') }}</button>
                <button type="button" class="btn btn-outline btn-cancel-employee" data-url="">{{ trans('welfare::view.Ok') }}</button>
            </div>
        </div>
    <!-- /.modal-content -->
    </div>
  <!-- /.modal-dialog -->
</div>

<!-- Modal -->
<div id="modal-add-attach-person" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content" id="modal-content-attach-employee">
           
        </div>
    </div>
</div>

<!-- modal edit person -->
<div class="modal fade in" id="modal-edit-person">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ trans('welfare::view.Edit infor cost') }}</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div class="box box-info">
                        <div class="box-body welfare-content" id="welfare-content-popup">
                        </div>
                    </div>
                </form>  
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-info" id="save-cost-event">{{ trans('welfare::view.Save') }}</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('welfare::view.Close') }}</button>
            </div>
        </div>
    <!-- /.modal-content -->
    </div>
  <!-- /.modal-dialog -->
</div>