<div class="modal fade" id="voter_modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title text-center">{{ trans('vote::view.voter_detail_nominee') }} <strong class="nominee-name"></strong></h4>
            </div>
            
            <div class="modal-body">
                <div class="grid-data-query" data-url="">
                    <h5 class="box-title padding-left-15"><i class="block-loading-icon fa fa-spin fa-refresh hidden"></i></h5>
                    <div class="grid-data-query-table">
                        @include('vote::manage.include.voter_list')
                    </div>
                </div>
            </div>
            
            <div class="modal-footer" style="text-align: center;">
                <button type="button" class="btn btn-primary" data-dismiss="modal">{{ trans('vote::view.close') }}</button>
            </div>
            
        </div><!-- /.modal-content -->
    </div>
</div>
