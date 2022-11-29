<?php
?>

<div class="modal fade" id="add-period-checkpoint" tabindex="-1" role="dialog" aria-labelledby="add-period-checkpoint" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">{!! trans('team::view.Add checkpoint') !!}</h2>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{!! route('team::checkpoint.period.save') !!}" id="form-save-period">
                    <input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />
                    <div class="form-group">
                        <label for="exampleFormControlSelect1">{!! trans('team::view.Period:') !!}</label>
                        <select class="form-control" id="period-checkpoint" name='period-checkpoint'>
                            <option value="0">{!! trans('team::view.Select period') !!}</option>
                            <option value="3">3</option>
                            <option value="9">9</option>
                        </select>
                        <span class="error hidden message-period-error">{!! trans('team::messages.Period is require') !!}</span>
                    </div>
                    <div class="form-group">
                        <label for="message-text" class="col-form-label">{!! trans('team::view.Year:') !!}</label>
                        <div class="container">
                            <div class="row">
                                <div class='col-sm-6' style="padding-left: 0px;">
                                    <div class='input-group date datetimepicker1-checkpoint'>
                                        <input type='text' class="form-control" name="year-checkpoint"/>
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </span>
                                    </div>  
                                </div>
                            </div>
                        </div>
                        <span class="error hidden message-year-require">{!! trans('team::messages.Year checkpoint is require') !!}</span>
                        <span class="error hidden message-year-number">{!! trans('team::messages.Year checkpoint error, check again') !!}</span>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{!! trans('team::view.Cancel') !!}</button>
                <button type="button" class="btn btn-primary" id="button-save-period">{!! trans('team::view.Create new') !!}</button>
            </div>
        </div>
    </div>
</div>
