<div class="modal fade" id="projectFuture" style="display: none;">
    <div class="modal-dialog modal-lg" style="width: 60%;">
        <div class="modal-content">
            <form method="post" action="{{route('project::operation.project-future.post')}}" class="form-horizontal has-valid" autocomplete="off">
                {!! csrf_field() !!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">{{ trans('project::view.Operation project') }}</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="row">
                            <div class="col-sm-12">
                                <p class="error operation-error">{!!trans('project::view.note operation future')!!}</p>
                                <div class="box box-info">

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="align-center">
                            <button type="submit" class="btn-add btn-project-future-submit btn-submit">
                                {{trans('project::view.Save')}}
                                <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                            <button type="button"  id="close-modal1" class="btn btn-close margin-left-10" data-dismiss="modal">{{trans('project::view.Close')}}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
