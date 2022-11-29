<?php
    use Carbon\Carbon;
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Core\View\View;
    use Rikkei\Team\View\Permission;
    use Rikkei\Team\Model\Team; 
    use Rikkei\Team\Model\Employee;
    use Rikkei\ManageTime\Model\SupplementRegister;
    
    $statusUnapprove = SupplementRegister::STATUS_UNAPPROVE;
    $statusApproved = SupplementRegister::STATUS_APPROVED;
    $statusDisapprove = SupplementRegister::STATUS_DISAPPROVE;
    $statusCancel = SupplementRegister::STATUS_CANCEL;
?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            <h3 class="box-title">{{ trans('manage_time::view.Register information of supplement') }}</h3>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="box-body">
                        <input type="hidden" name="register_id" value="{{ $registerRecord->register_id }}">
                        <div class="form-group row">
                            <div class="col-sm-4">
                                <?php
                                    if ($registerRecord->status == $statusDisapprove) {
                                        $classStatus = 'callout-disapprove';
                                        $status = trans('manage_time::view.Disapprove');
                                    } elseif ($registerRecord->status == $statusUnapprove) {
                                        $classStatus = 'callout-unapprove';
                                        $status = trans('manage_time::view.Unapprove');
                                    } elseif ($registerRecord->status == $statusApproved) {
                                        $classStatus = 'callout-approved';
                                        $status = trans('manage_time::view.Approved');
                                    } else {
                                        $classStatus = 'callout-cancelled';
                                        $status = trans('manage_time::view.Cancelled');
                                    }
                                ?>
                                <div class="managetime-callout {{ $classStatus }}">
                                    <p class="text-center"><strong>{{ $status }}</strong></p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6 managetime-form-group">
                                <label class="control-label managetime-label">{{ trans('manage_time::view.Registrant') }}</label>
                                <div class="input-box">
                                    <input type="text" name="employee_name" class="form-control" value="{{ $registerRecord->creator_name }}" disabled />
                                </div>
                            </div>
                            <div class="col-sm-6 managetime-form-group">
                                <label class="control-label managetime-label">{{ trans('manage_time::view.Employee code') }}</label>
                                <div class="input-box">
                                    <input type="text" name="employee_code" class="form-control" value="{{ $registerRecord->creator_code }}" disabled />
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label managetime-label">{{ trans('manage_time::view.Position') }}</label>
                            <div class="input-box">
                                <input type="text" name="role_name" class="form-control" value="{{ $registerRecord->role_name }}" disabled />
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6 managetime-form-group">
                                <label class="control-label required managetime-label">{{ trans('manage_time::view.Approver') }} <em>*</em></label>
                                <div class="input-box">
                                    <select id="approver" class="form-control managetime-select-2" name="approver" disabled>
                                        <option value="{{ $registerRecord->approver_id }}" selected>{{ $registerRecord->approver_name . ' (' . preg_replace('/@.*/', '',$registerRecord->approver_email) . ')' }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6 managetime-form-group">
                                <label class="control-label managetime-label">{{ trans('manage_time::view.Related persons need notified') }}</label>
                                <div class="input-box">
                                    <select id="related_persons" name="related_persons_list[]" class="form-control" multiple disabled>
                                        @if(isset($relatedPersonsList) && count($relatedPersonsList))
                                            @foreach($relatedPersonsList as $item)
                                                <option value="{{ $item->relater_id }}" selected>{{ $item->relater_name . ' (' . preg_replace('/@.*/', '',$item->relater_email) . ')' }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6 managetime-form-group">
                                <label class="control-label required managetime-label">{{ trans('manage_time::view.From date') }} <em>*</em></label>
                                <div class='input-group date' id='datetimepicker-start-date'>
                                    <span class="input-group-addon managetime-icon-date" id="managetime-icon-date-start">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                    <input type='text' class="form-control managetime-date" name="start_date" id="start_date" data-date-format="DD-MM-YYYY HH:mm" value="{{ Carbon::parse($registerRecord->date_start)->format('d-m-Y H:i') }}" disabled />
                                </div>
                            </div>
                            <div class="col-sm-6 managetime-form-group">
                                <label class="control-label required managetime-label">{{ trans('manage_time::view.End date') }} <em>*</em></label>
                                <div class='input-group date' id='datetimepicker-end-date'>
                                    <span class="input-group-addon managetime-icon-date" id="managetime-icon-date-end">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                    <input type='text' class="form-control" name="end_date" id="end_date" data-date-format="DD-MM-YYYY HH:mm" value="{{ Carbon::parse($registerRecord->date_end)->format('d-m-Y H:i') }}" disabled />
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6 managetime-form-group">
                                <label class="control-label managetime-label">{{ trans('manage_time::view.Number of days supplement') }}</label>
                                <div class="input-box">
                                    <input type="text" name="number_days_off" id="number_days_off" class="form-control" value="{{ $registerRecord->number_days_supplement }}" disabled />
                                </div>
                            </div>
                        </div>
                    
                        <div class="form-group">
                            <label class="control-label required managetime-label">{{ trans('manage_time::view.Supplement reason') }} <em>*</em></label>
                            <div class="input-box">
                                <textarea id="reason" name="reason" class="form-control required managetime-textarea" disabled>{{ $registerRecord->reason }}</textarea>
                            </div>
                        </div>
                    </div>
                    <!-- /.box-body -->
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default pull-left" data-dismiss="modal" style="margin-left: 10px;">{{ trans('manage_time::view.Close') }}</button>
            <a class="pull-right" href="{{ route('manage_time::profile.supplement.detail', ['id' => $registerRecord->register_id]) }}"><button type="button" class="btn btn-primary">{{ trans('manage_time::view.See details') }}</button></a>
        </div>
    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->