<?php
use Rikkei\Document\View\DocConst;
?>

<div class="modal fade" id="doc_feedback_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form method="post" action="{{ route('doc::admin.feedback', $item->id) }}">
            {!! csrf_field() !!}
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ trans('doc::view.Feedback reason') }} <em class="required">*</em></h4>
                </div>
                <div class="modal-body">
                    <textarea class="form-control noresize" required
                              rows="5" name="feedback_reason">{{ old('feedback_reason') }}</textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('doc::view.Close') }}</button>
                    <button type="submit" class="btn btn-danger" id="task_feedback_btn">{{ trans('doc::view.Feedback') }}</button>
                </div>
            </div><!-- /.modal-content -->
        </form>
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

@if ($docPermiss['publish'])
<div class="modal fade" id="doc_publish_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <form method="post" action="{{ route('doc::admin.publish', $item->id) }}" id="doc_publish_form">
            {!! csrf_field() !!}
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ $item->status == DocConst::STT_PUBLISH ? trans('doc::view.Re publish document') : trans('doc::view.Publish document') }}</h4>
                </div>
                <div class="modal-body">
                    <ul class="list-unstyled">
                        <li>
                            <label>
                                <input type="radio" name="publish_all" value="1" class="radio-show-box"
                                       {{ $item->publish_all == 1 ? 'checked' : '' }}> {{ trans('doc::view.Publish all') }}
                            </label>
                        </li>
                        <li>
                            <label>
                                <input type="radio" name="publish_all" value="0" class="radio-show-box"
                                       {{ $item->publish_all == 0 ? 'checked' : '' }}> {{ trans('doc::view.Publish to Team or Account') }}
                            </label>
                        </li>
                    </ul>
                    <div id="radio_show_box_0" class="radio-show-box-content" {!! $item->publish_all == 1 ? 'style="display: none;"' : '' !!}>
                        <div class="form-group">
                            <label>{{ trans('doc::view.Group') }} <input type="checkbox" class="checkbox-all"></label>
                            <div class="checkbox-group">
                                <ul class="list-unstyled">
                                    {!! DocConst::toNestedCheckbox($teamList, old('team_ids') ? old('team_ids') : $listPublished->lists('team_id')->toArray(), 'team_ids[]') !!}
                                </ul>
                            </div>
                        </div>
                        <div class="form-group select2-group">
                            <label>{{ trans('doc::view.Add accounts') }}</label>
                            <select class="form-control select-search-employee select-search" multiple name="account_ids[]"
                                        id="account_ids" data-remote-url="{{ route('team::employee.list.search.ajax') }}">
                                @if (isset($publishAccounts) && $publishAccounts)
                                    @foreach ($publishAccounts as $empId => $empEmail)
                                    <option value="{{ $empId }}" selected>{{ DocConst::getAccount($empEmail) }}</option>
                                    @endforeach
                                @else
                                    @if (!$accoutsPublish->isEmpty())
                                    @foreach ($accoutsPublish as $emp)
                                    <option value="{{ $emp->id }}" selected>{{ DocConst::getAccount($emp->email) }}</option>
                                    @endforeach
                                    @endif
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><input type="checkbox" name="send_mail" value="1" id="checkbox_send_mail"/> {{ trans('doc::view.Send mail') }}</label>
                    </div>
                    <div class="send-mail-box" style="display: none;">
                        <div class="form-group">
                            <label>{{ trans('doc::view.Email subject') }} <em class="required">*</em></label>
                            <input type="text" class="form-control" name="email_subject"
                                   value="{{ old('email_subject') ? old('email_subject') : trans('doc::view.mail_publish.subject', ['code' => $item->code]) }}">
                        </div>
                        <div class="form-group">
                            <label>{{ trans('doc::view.Email content') }}</label>
                            <textarea class="form-control" name="email_content" rows="5" id="publish_mail_content"
                                      >{{ old('email_content') ? old('email_content') : trans('doc::view.mail_publish.content', ['code' => $item->code, 'link' => $item->getViewLink()]) }}</textarea>
                        </div>
                        <div>
                            <p>{{ trans('doc::view.Name') }}:  <?php echo '{{ name }}' ?></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('doc::view.Close') }}</button>
                    <button type="submit" class="btn btn-success" id="doc_publish_btn"
                            data-noti="{{ trans('doc::message.Publish document with sending email, are you sure?') }}"><i class="fa fa-send"></i> {{ trans('doc::view.Publish') }}</button>
                </div>
            </div><!-- /.modal-content -->
        </form>
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
@endif