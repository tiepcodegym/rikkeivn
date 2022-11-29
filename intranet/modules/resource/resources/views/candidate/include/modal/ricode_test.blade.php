<div class="modal fade in" id="test-ricode-modal" tabindex="-1" role="dialog"  data-keyboard="false" >
    <div class="modal-dialog">
        <div class="modal-content">
            <form method='post' autocomplete="off" action='{{ route('resource::candidate.create-ricode-test') }}' id='form-ricode-test'>
                {{ csrf_field() }}
                <input type='hidden' name='candidate_id' value='{{ $candidate->id }}' />
                <input type='hidden' name='ricode_app_url' value='{{ config('app.ricode_app_url') }}' />
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    <h4 class="modal-title">Ricode Test</h4>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <div class='col-md-12' id='message-ricode'></div>
                        <div class="col-md-4 select-group-full form-group input-ricode">
                            <label for="level_easy">{{ trans('resource::view.Level Easy') }}</label>
                            <input type="text" class="form-control" placeholder="Easy" name='level_easy' value='{{isset($candidate->ricodeTest) ? $candidate->ricodeTest->level_easy : 0}}'>
                        </div>
                        <div class="col-md-4 select-group-full form-group input-ricode">
                            <label for="level_medium">{{ trans('resource::view.Level Medium') }}</label>
                            <input type="text" class="form-control" placeholder="Medium" name='level_medium' value='{{isset($candidate->ricodeTest) ? $candidate->ricodeTest->level_medium : 0}}'>
                        </div>
                        <div class="col-md-4 select-group-full form-group input-ricode">
                            <label for="level_hard">{{ trans('resource::view.Level Hard') }}</label>
                            <input type="text" class="form-control" placeholder="Hard" name='level_hard' value='{{isset($candidate->ricodeTest) ? $candidate->ricodeTest->level_hard : 0}}'>
                        </div>
                        <div class="col-md-12 select-group-full form-group input-ricode">
                            <label for="duration">{{ trans('resource::view.Duration') }} ({{trans('resource::view.minute')}})</label>
                            <input type="text" class="form-control" placeholder="Enter duration" name='duration' value='{{isset($candidate->ricodeTest) ? $candidate->ricodeTest->duration : 0}}'>
                        </div>
                        <div class="col-md-12 select-group-full form-group input-ricode" id='password-candidate-gen'></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class='col-md-12'>
                    <button type="submit" class="btn btn-primary pull-left" id='create-ricode-test'>{{$candidate->ricodeTest ? 'Recreate' : 'Create'}}</button>
                        <button type="submit" class="btn btn-primary" id='update-ricode-test' name="{{$candidate->ricodeTest ? 'action-is-update' : 'action-is-create'}}">Update</button>
                    </div>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
