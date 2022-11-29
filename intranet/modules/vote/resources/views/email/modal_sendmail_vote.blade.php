<div class="modal fade sendmail_modal" id="modal_send_mail_vote">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            {!! Form::open(['method' => 'post', 'route' => ['vote::manage.vote.sendmail.vote', $vote->id], 'id' => 'vote_form_mail', 'class' => 'vote_form_mail']) !!}
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title text-center">{{ trans('vote::view.send_vote_mail') }}</h4>
            </div>
            <div class="modal-body">
                
                <div class="message_box"></div>

                <div class="form-group row select-group-full">
                    <div class="col-sm-3 col-md-2 mgb-5">
                        <button class="btn btn-primary" 
                                data-text="#text_vote_team"
                                data-field="#mail_vote_team_ids" type="button" 
                                data-toggle="modal" data-target="#team_modal">{{ trans('vote::view.select_team') }}</button>
                    </div>
                    <div class="col-sm-9 col-md-10">
                        <input type="text" disabled="" id="text_vote_team" class="form-control">
                        <select name="mail_vote_team_ids[]" id="mail_vote_team_ids" class="hidden validate-field" data-relate="#mail_vote_bcc" multiple></select>
                    </div>
                </div>
                
                <div class="form-group select-group-full">
                    <label>{{ trans('vote::view.email_add_bcc') }}</label>
                    <select name="mail_vote_bcc[]" id="mail_vote_bcc" data-url="{{ route('team::employee.list.search.ajax') }}" class="form-control mail_bcc validate-field" multiple>
                        <option value="">{{ trans('vote::view.select_employees') }}</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="required">{{ trans('vote::view.title') }} <em>*</em></label>
                    <input type="text" name="mail_vote_subject" class="form-control validate-field" value="{{ $subjectVoteEmail }}" placeholder="{{ trans('vote::view.title') }}">
                </div>
                
                <div>
                    <label class="required">{{ trans('vote::view.content') }} <em>*</em></label>
                    <textarea name="mail_vote_content" id="mail_vote_content" class="form-control validate-field">{{ $contentVoteEmail }}</textarea>
                </div>
                
            </div>
            <div class="modal-footer" style="text-align: center;">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('vote::view.cancel') }}</button>
                <button type="submit" class="btn btn-primary"><i class="fa fa-send"></i> {{ trans('vote::view.send') }}</button>
            </div>
            {!! Form::close() !!}
        </div><!-- /.modal-content -->
    </div>
</div>

