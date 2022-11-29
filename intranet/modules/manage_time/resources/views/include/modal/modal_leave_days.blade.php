<?php
use Rikkei\Team\Model\Team;
?>
<!-- Modal infor leave days -->
<div id="modal_leave_days" class="modal fade tooltip-white" role="dialog">
    <div class="modal-dialog modal-lg" style="width: 80%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">{{trans('manage_time::view.Infor leave days')}}</h4>
            </div>
            <div class="modal-body managetime-form-group">
                <table class="table table-striped dataTable table-bordered table-hover table-grid-data" id="table-day">
                    <thead>
                    <tr>
                        <th>{{ trans('manage_time::view.Employee code') }}</th>
                        <th style="min-width: 80px;">{{ trans('manage_time::view.Employee fullname') }}</th>
                        <th>
                            {{ trans('manage_time::view.Number day last year') }} <sup>(1)</sup>
                            @if($teamCodePreOfEmp != Team::CODE_PREFIX_JP )
                                <span class="fa fa-question-circle" data-toggle="tooltip"
                                      title="{!! trans('manage_time::view.Tooltip day last year') !!}" data-html="true">
                                </span>
                            @endif
                        </th>
                        <th>
                            {{ trans('manage_time::view.Number day last year use') }} <sup>(2)</sup>
                            @if($teamCodePreOfEmp != Team::CODE_PREFIX_JP )
                                <span class="fa fa-question-circle" data-toggle="tooltip"
                                      title="{!! trans('manage_time::view.Tooltip day last transfer') !!}" data-html="true">
                                </span>
                            @endif
                        </th>
                        <th>
                            {{ trans('manage_time::view.Number day current year') }} <sup>(3)</sup>
                            @if($teamCodePreOfEmp != Team::CODE_PREFIX_JP )
                                <span class="fa fa-question-circle" data-toggle="tooltip"
                                      title="{!! trans('manage_time::view.Tooltip day current year') !!}"  data-placement="bottom" data-html="true">
                                </span>
                            @endif
                        </th>
                        <th>
                            {{ trans('manage_time::view.Number day seniority') }} <sup>(4)</sup>
                            @if($teamCodePreOfEmp != Team::CODE_PREFIX_JP )
                                <span class="fa fa-question-circle" data-toggle="tooltip"
                                      title="{!! trans('manage_time::view.Tooltip day seniority') !!}"  data-placement="bottom" data-html="true">
                                </span>
                            @endif
                        </th>
                        <th>
                            {{ trans('manage_time::view.Number day OT') }} <sup>(5)</sup>
                            @if($teamCodePreOfEmp != Team::CODE_PREFIX_JP )
                                <span class="fa fa-question-circle" data-toggle="tooltip"
                                      title="{!! trans('manage_time::view.Tooltip day ot') !!}"  data-html="true">
                                </span>
                            @endif
                        </th>
                        <th>
                            {{ trans('manage_time::view.Total number day') }} <sup>(6)</sup>
                            @if($teamCodePreOfEmp != Team::CODE_PREFIX_JP )
                                <span class="fa fa-question-circle" data-toggle="tooltip"
                                      title="{!! trans('manage_time::view.Tooltip total day') !!}"  data-html="true">
                                </span>
                            @endif
                        </th>
                        <th>
                            {{ trans('manage_time::view.Number day used') }} <sup>(7)</sup>
                            @if($teamCodePreOfEmp != Team::CODE_PREFIX_JP )
                                <span class="fa fa-question-circle" data-toggle="tooltip"
                                      title="{!! trans('manage_time::view.Tooltip day used') !!}"  data-html="true">
                                </span>
                            @endif
                        </th>
                        <th>
                            {{ trans('manage_time::view.Number day remain') }} <sup>(8)</sup>
                            @if($teamCodePreOfEmp != Team::CODE_PREFIX_JP )
                                <span class="fa fa-question-circle" data-toggle="tooltip"
                                      title="{!! trans('manage_time::view.Tooltip remain day') !!}" data-placement="left"  data-html="true">
                                </span>
                            @endif
                        </th>
                    </tr>
                    </thead>
                    <tbody class="checkbox-body">
                    @if(isset($leaveDay))
                        <tr day-id="{{$leaveDay->id}}" class="reason-data">
                            @if ($leaveDay->employee_code)
                                <td class="employee_code">{{$leaveDay->employee_code}}</td>
                            @else
                                <td>&nbsp;</td>
                            @endif
                            @if ($leaveDay->name)
                                <td class="full_name">{{$leaveDay->name}}</td>
                            @else
                                <td>&nbsp;</td>
                            @endif
                            <td class="day_last_year">{{$leaveDay->day_last_year}}</td>
                            <td class="day_last_transfer">{{$leaveDay->day_last_transfer}}</td>
                            <td class="day_current_year">{{$leaveDay->day_current_year}}</td>
                            <td class="day_seniority">{{$leaveDay->day_seniority}}</td>
                            <td class="day_OT">{{$leaveDay->day_ot}}</td>
                            <td class="total_day">{{$leaveDay->total_day}}</td>
                            <td class="day_used">{{$leaveDay->day_used}}</td>
                            <td>{{$leaveDay->remain_day}}</td>
                        </tr>
                    @else
                        <tr>
                            <td colspan="10" class="text-center">
                                <h2 class="no-result-grid">{{ trans('manage_time::view.No results found') }}</h2>
                            </td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" id="close_form" data-dismiss="modal">{{ trans('manage_time::view.Close') }}</button>
            </div>
        </div>
    </div>
</div>