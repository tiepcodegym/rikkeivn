
<!-- Modal infor annual paid leave acquisition status -->
<?php
    use Rikkei\ManageTime\Model\LeaveDayRegister;
    use Rikkei\ManageTime\Model\LeaveDay;
    use Carbon\Carbon;

    $statusUnapprove = LeaveDayRegister::STATUS_UNAPPROVE;
    $statusApproved = LeaveDayRegister::STATUS_APPROVED;
    $statusDisapprove = LeaveDayRegister::STATUS_DISAPPROVE;
    $statusCancel = LeaveDayRegister::STATUS_CANCEL;
    $leaveDayJapanNotice = LeaveDay::LEAVE_DAY_JAPAN_NOTICE;
?>
    <div class="modal-dialog modal-full-width">
        <div class="modal-content">
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-3">
                        <table class="table table-bordered table-hover">
                            <tr>
                                <td>従業員コード</td>
                                <td>{{ $employee->employee_code }}
                            </tr>
                        </table>
                    </div>
                    <div class="col-sm-3">
                        <table class="table table-bordered table-hover">
                            <tr>
                                <td>{{ trans('manage_time::view.Department book') }}</td>
                                <td>{{ $employee->team_name }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-sm-3">
                        <table class="table table-bordered table-hover">
                            <tr>
                                <td>{{ trans('manage_time::view.Name') }}</td>
                                <td>{{ $employee->name }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-sm-3">
                        <table class="table table-bordered table-hover">
                            <tr>
                                <td>{{ $employee && $employee->last_grant_date ? \Carbon\Carbon::parse($employee->last_grant_date)->format('Y') : null }}</td>
                                <td>{{ trans('manage_time::view.For the year') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-3">
                        <table class="table table-bordered table-hover">
                            <tr>
                                <td>⼊社⽇</td>
                                <td>{{ $employee && $employee->join_date ? \Carbon\Carbon::parse($employee->join_date)->format('Y-m-d') : null }}</td>
                            </tr>
                            <tr>
                                <td>基準⽇</td>
                                <td>{{ $employee && $employee->last_grant_date ? \Carbon\Carbon::parse($employee->last_grant_date)->format('Y-m-d') : null }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-sm-3">
                        <table class="table table-bordered table-hover">
                            <tr>
                                <td>前期繰越⽇数</td>
                                <td>{{ number_format($employee->leave_day_baseline['day_last_transfer'], 2) }}</td>
                            </tr>
                            <tr>
                                <td>今期付与⽇数</td>
                                <td>{{ number_format($employee->leave_day_baseline['day_current_year'] + $employee->leave_day_baseline['day_seniority'], 2) }}</td>
                            </tr>
                            <tr>
                                <td>合計⽇数</td>
                                <td>{{ number_format($employee->total_day, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-sm-3">
                        <table class="table table-bordered table-hover">
                            <tr>
                                <td>今期取得合計⽇数</td>
                                <td>{{ number_format($employee->total_num_day_off, 2) }}</td>
                            </tr>
                            <tr>
                                <td>年5⽇の時季指定残⽇数</td>
                                <td>
                                    @if($employee->total_num_day_off < $leaveDayJapanNotice)
                                        <span>{{ number_format($leaveDayJapanNotice - $employee->total_num_day_off, 2) }}</span>
                                    @else
                                        <span>0.00</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <table class="table table-striped dataTable table-bordered table-hover table-grid-data" id="table-day">
                    <thead class="managetime-thead">
                        <tr>
                            <th>No</th>
                            <th>{{ trans('manage_time::view.Application date') }}</th>
                            <th>{{ trans('manage_time::view.At the start date') }}</th>
                            <th>{{ trans('manage_time::view.At the end of the day') }}</th>
                            <th>{{ trans('manage_time::view.Number of paid leave application days') }}</th>
                            <th>{{ trans('manage_time::view.Number of days of paid leave taken') }}</th>
                            <th>{{ trans('manage_time::view.Number of paid vacation days left') }}</th>
                            <th>{{ trans('manage_time::view.Situation') }}</th>
                            <th>{{ trans('manage_time::view.Reason for changing season') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($employee && count($employee->leave_day_registers))
                            <?php $i = 1; $remain_day = $employee->total_day; ?>
                            @foreach($employee->leave_day_registers as $item)
                                <?php 
                                    $dayOff = 0;
                                    $status = '';
                                    $tdClass = '';
                                    if ($item->status == $statusApproved) {
                                        $status = '承認済み';
                                    } elseif ($item->status == $statusDisapprove) {
                                        $status = '時季変更';
                                    } else {
                                        $status = '未承認';
                                    }
                                    
                                    $isIncludeOtherYear = false;
                                    $grantDate = [
                                        'last_grant_date' => $employee['last_grant_date'],
                                        'next_grant_date' => $employee['next_grant_date']
                                    ];
                                    // Tính toán xem dữ liệu ngày nghỉ phép có bao gồm cả ở trong kỳ cấp phép khác hay không
                                    $isIncludeOtherYear = LeaveDayRegister::checkLeaveDayRegisterGrantDateOtherYear(Carbon::parse($item->date_start)->format('Y-m-d'), Carbon::parse($item->date_end)->format('Y-m-d'), $grantDate);

                                    if ($isIncludeOtherYear)
                                    {
                                        $tdClass = 'label-red';
                                    }
                                    $isInFuture = false;
                                    if (Carbon::now()->format('Y-m-d') < Carbon::parse($item->date_start)->format('Y-m-d'))
                                    {
                                        $isInFuture = true;
                                    }
                                    if ($item->status == $statusApproved && !$isInFuture) 
                                    {
                                        $remain_day -= $item->number_days_off;
                                    }
                                    if ($item->status == $statusApproved && !$isInFuture) {
                                        $dayOff = $item->number_days_off;
                                    }
                                ?>
                                <tr>
                                    <td class="{{ $tdClass }}">{{ $i }}</td>
                                    <td class="{{ $tdClass }}">{{ $item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('Y-m-d H:i') : '' }}</td>
                                    <td class="{{ $tdClass }}">{{ $item->date_start ? \Carbon\Carbon::parse($item->date_start)->format('Y-m-d H:i') : '' }}</td>
                                    <td class="{{ $tdClass }}">{{ $item->date_end ? \Carbon\Carbon::parse($item->date_end)->format('Y-m-d H:i') : '' }}</td>
                                    <td class="{{ $tdClass }}">{{ number_format($item->number_days_off, 2) }}</td>
                                    <td class="{{ $tdClass }}">{{ number_format($dayOff, 2) }}</td>
                                    <td class="{{ $tdClass }}">{{ number_format($remain_day, 2) }}</td>
                                    <td class="{{ $tdClass }}">{{ $status }}</td>
                                    <td class="{{ $tdClass }}">
                                        @if($item->status == $statusDisapprove && isset($employee->manage_time_comments) && count($employee->manage_time_comments))
                                            @foreach($employee->manage_time_comments as $comment)
                                                @if($comment->register_id == $item->id)
                                                    <?php echo nl2br($comment->comment); ?>
                                                @endif
                                            @endforeach
                                        @endif
                                    </td>
                                </tr>
                                <?php $i++; ?>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="9" class="text-center">
                                    <h2 class="no-result-grid">データなし</h2>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" id="close_form" data-dismiss="modal">閉じる</button>
            </div>
        </div>
    </div>
