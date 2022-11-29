<div class="modal fade" id="team_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title text-center">{{ trans('vote::view.team_list') }} <strong class="nominee-name"></strong></h4>
            </div>
            
            <div class="modal-body">
                @if ($teamList)
                    @foreach($teamList as $team)
                    <div>
                        <label class="normal-label"> {{ $team['prefix'] }} <input type="checkbox" class="check_team_item" value="{{ $team['value'] }}"> {{ $team['label'] }}</label>
                    </div>
                    @endforeach
                @endif
            </div>
            
            <div class="modal-footer">
                <div class="row">
                    <div class="col-xs-4 text-left">
                        <button type="button" class="btn btn-primary" id="check_all_team" 
                                not-check-label="{{ trans('vote::view.not_check') }}"
                                check-label="{{ trans('vote::view.check_all') }}">{{ trans('vote::view.check_all') }}</button>
                    </div>
                    <div class="col-xs-8">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('vote::view.close') }}</button>
                        <button type="button" class="btn btn-success" data-dismiss="modal" id="btn_submit_team"><i class="fa fa-check"></i> {{ trans('vote::view.submit_select') }}</button>
                    </div>
                </div>
            </div>
            
        </div><!-- /.modal-content -->
    </div>
</div>
