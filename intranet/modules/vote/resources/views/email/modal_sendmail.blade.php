<div class="modal fade" id="sendmail_modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            {!! Form::open(['method' => 'post', 'route' => ['vote::manage.vote.sendmail.nominate', $vote->id], 'id' => 'nominate_form_mail', 'class' => 'vote_form_mail']) !!}
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title text-center">{{ trans('vote::view.send_mail_notify') }}</h4>
            </div>
            <div class="modal-body">
                
                <div class="message_box"></div>

                <div class="form-group row select-group-full">
                    <div class="col-sm-3 col-md-2 mgb-5">
                        <button class="btn btn-primary" 
                                data-text="#text_team"
                                data-field="#mail_team_ids" type="button" 
                                data-toggle="modal" data-target="#team_modal">{{ trans('vote::view.select_team') }}</button>
                    </div>
                    <div class="col-sm-9 col-md-10">
                        <input type="text" id="text_team" disabled class="form-control">
                        <select name="mail_team_ids[]" id="mail_team_ids" class="hidden validate-field" data-relate="#mail_bcc" multiple></select>
                    </div>
                </div>
                
                <div class="form-group select-group-full">
                    <label>{{ trans('vote::view.email_add_bcc') }}</label>
                    <select name="mail_bcc[]" id="mail_bcc" data-url="{{ route('team::employee.list.search.ajax') }}" class="form-control mail_bcc validate-field" multiple>
                        <option value="">{{ trans('vote::view.select_employees') }}</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="required">{{ trans('vote::view.title') }} <em>*</em></label>
                    <input type="text" name="mail_subject" class="form-control validate-field" value="{{ $subjectNominateEmail }}" placeholder="{{ trans('vote::view.title') }}">
                </div>
                
                <div>
                    <label class="required">{{ trans('vote::view.content') }} <em>*</em></label>
                    <textarea name="mail_content" id="mail_content" class="form-control validate-field">{{ $contentNominateEmail }}</textarea>
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

