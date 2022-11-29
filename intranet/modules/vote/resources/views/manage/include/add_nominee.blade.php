<div class="modal fade" id="modal_add_nominee">
    <div class="modal-dialog">
        <div class="modal-content">
            {!! Form::open(['method' => 'post', 'route' => 'vote::manage.vote_nominee.store', 'id' => 'add_nominee_form']) !!}
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title text-center">{{ trans('vote::view.add_nominee') }} <strong class="nominee-name"></strong></h4>
            </div>
            
            <div class="modal-body">
                
                <div class="message_box"></div>
                
                <div class="form-group select-group-full">
                    <label class="required">{{ trans('vote::view.email') }} <em>*</em></label>
                    <select name="nominee_employee_id" class="form-control mail_bcc" data-vote-id="{{ $vote->id }}" data-url="{{ route('vote::manage.vote_nominee.list_employee') }}">
                        <option value="">{{ trans('vote::view.select_email') }}</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>{{ trans('vote::view.name') }}</label>
                    <input type="text" id="nominee_name" class="form-control" disabled="">
                </div>
                
                <div>
                    <label>{{ trans('vote::view.description') }}</label>
                    <textarea name="nominee_description" class="form-control no-resize" rows="3"></textarea>
                </div>
                
            </div>
            
            <div class="modal-footer">
                <input type="hidden" name="vote_id" value="{{ $vote->id }}">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('vote::view.close') }}</button>
                <button type="submit" class="btn-add">{{ trans('vote::view.save') }}</button>
            </div>
            {!! Form::close() !!}
        </div><!-- /.modal-content -->
    </div>
</div>
