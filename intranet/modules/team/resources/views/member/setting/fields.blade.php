<div class="box-header with-border">
    <h2 class="box-title">{{ trans('team::profile.Password open email attachment file') }}</h2>
</div>
<div class="box-body">
    <button type="button" class="btn btn-primary margin-bottom-5" data-toggle="collapse" data-target="#attach_pass_box">{{ trans('team::profile.Change password') }}</button>
    <button type="button" class="btn btn-primary margin-bottom-5" id="btn_show_password" data-modal="#attach_pass_modal">{{ trans('team::profile.Password history') }}</button>
    <div id="attach_pass_box" class="collapse">
        <div class="well">
            <form method="post" data-form-submit="ajax" data-cb-success="formEmployeeInfoSuccess" id="form_file_password" class="reset-after-submit"
                  action="{{ route('team::member.profile.save', ['employeeId' => $employeeModelItem->id, 'type' => 'setting']) }}">
                {!! csrf_field() !!}
                <?php
                $openFilePass = $employeeModelItem->getSetting('pass_open_file');
                ?>
                <div class="form-group">
                    <label>{{ trans('team::profile.Current password') }}</label>
                    <input type="password" name="emp_setting[pass_open_file]" value=""
                           class="form-control" autocomplete="off">
                </div>
                <div class="form-group">
                    <label>{{ trans('team::profile.New password') }}</label>
                    <input type="password" name="new_password" value=""
                           class="form-control" autocomplete="off">
                </div>
                <div class="form-group">
                    <label>{{ trans('team::profile.Confirm password') }}</label>
                    <input type="password" name="new_password_confirmation" value=""
                           class="form-control" autocomplete="off">
                </div>
                @if (!$disabledInput)
                <div class="form-group">
                    <button class="btn btn-primary btn-save-form" type="submit">
                        {!! trans('team::view.Save') !!}
                        <i class="fa fa-spin fa-refresh loading-submit hidden"></i>
                    </button>
                </div>
                @endif
            </form>
        </div>
    </div>
    <div style="color: red"><i>{!! trans('team::messages.Forget password, please contact admin') !!}</i></div>
    <div id="attach_pass_modal" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ trans('team::profile.Password history') }}</h4>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>{{ trans('team::profile.Password') }}</th>
                                <th>{{ trans('team::profile.Updated date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="none-item">
                                <td colspan="2"><h5 class="text-center">{{ trans('team::messages.None item') }}</h5></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>