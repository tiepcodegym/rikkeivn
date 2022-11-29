<div class="modal" id="modal-clone-project" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('project::project.create') }}" enctype="multipart/form-data" autocomplete="off"
                  method="post" novalidate="novalidate" id="clone-project">
                {{ csrf_field() }}
                <div class="modal-header">
                    <h4 class="modal-title">{{ trans('project::view.Clone Project') }}</h4>
                </div>
                <div class="modal-body col-sm-12">
                    <span class="col-sm-3 required">{{ trans('project::view.Project Name') }}<em>*</em> </span>
                    <input type="text" class="col-sm-9 project-name" name="name">
                </div>
                <input type="text" class="hidden" name="clone_id" value="{{ $project->id }}">
                <div class="modal-footer">
                    <button type="button" class="btn btn-close btn-danger"
                            data-dismiss="modal">{{ trans('core::view.Close') }}</button>
                    <button type="submit" class="btn btn-success submit-clone" disabled>{{ trans('core::view.OK') }}
                        <i class="fa fa-spin fa-refresh hidden"></i></button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div> <!-- modal delete cofirm -->
