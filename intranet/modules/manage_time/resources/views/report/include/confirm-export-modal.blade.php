<div class="modal" tabindex="-1" role="dialog" id="modal-confirm-export">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{!! trans('manage_time::view.Confirm Export') !!}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -33px;">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-footer">
                <form method="post" action="{!! route('manage_time::timekeeping.manage.export') !!}"
                      class="no-validate" id="form_export_busines_trip">
                    <input type="hidden" id="filterDate" name="filterDate" value="{{$filterDate}}">
                    <input type="hidden" id="sel_country" name="sel_country" value="{{$selCountry}}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <button type="submit" class="btn btn-primary">{!! trans('manage_time::view.Export') !!}
                
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{!! trans('manage_time::view.Close export') !!}</button>
                </form>
            </div>
        </div>
    </div>
</div>
