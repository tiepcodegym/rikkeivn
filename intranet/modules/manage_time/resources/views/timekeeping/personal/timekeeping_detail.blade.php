@extends('manage_time::layout.common_layout')

<?php
    use Carbon\Carbon;
    use Rikkei\Team\View\Permission;
    use Rikkei\ManageTime\View\ManageTimeConst;
    use Rikkei\ManageTime\View\ManageTimeCommon;
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\ManageTime\View\View as ManageTimeView;
    use Rikkei\Team\Model\Team;
    use Rikkei\ManageTime\Model\TimekeepingTable;

    $userCurrent = Permission::getInstance()->getEmployee();
    $totalHoliday = $timekeepingAggregate->total_official_holiay + $timekeepingAggregate->total_trial_holiay;
    $totalOTWeekdays = $timekeepingAggregate->total_official_ot_weekdays + $timekeepingAggregate->total_trial_ot_weekdays;
    $totalOTWeekends = $timekeepingAggregate->total_official_ot_weekends + $timekeepingAggregate->total_trial_ot_weekends;
    $totalOTHolidays = $timekeepingAggregate->total_official_ot_holidays + $timekeepingAggregate->total_trial_ot_holidays;
    $totalWorkingDays = $timekeepingAggregate->total_official_working_days + $timekeepingAggregate->total_trial_working_days;
    $totalLeaveDayHasSalary = $timekeepingAggregate->total_official_leave_day_has_salary + $timekeepingAggregate->total_trial_leave_day_has_salary;
    $totalRegisterBusinessTrip = $timekeepingAggregate->total_official_business_trip + $timekeepingAggregate->total_trial_business_trip;
    $totalRegisterSupplement = $timekeepingAggregate->total_official_supplement + $timekeepingAggregate->total_trial_supplement;
    $totalCompensation = $timekeepingAggregate->number_com_off + $timekeepingAggregate->number_com_tri;
    $totalLeaveDayBasic = $timekeepingAggregate->total_official_leave_basic_salary + $timekeepingAggregate->total_trial_leave_basic_salary;

    $totalWorking = $timekeepingAggregate->total_working_officail + $timekeepingAggregate->total_working_trial;

    $datesTimekeeping = ManageTimeCommon::getDateRange(Carbon::parse($timekeepingTable->start_date), Carbon::parse($timekeepingTable->end_date));
    $worksInTimekeeping = ManageTimeCommon::countWorkingDay($timekeepingTable->start_date, $timekeepingTable->end_date);
?>



@section('title-common')
    {{ trans('manage_time::view.Timekeeping month :month year :year', ['month' => $timekeepingTable->month, 'year' => $timekeepingTable->year]) }}
@endsection

@section('css-common')
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_managetime/css/personal.css') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css">

    <style>
        .box-note {
            border: 2px solid #00a7d0;
            box-shadow: 0 0 3px 1px #00a7d078;
            padding: 10px 15px 0px 20px;
        }
        .color-7d0 {
            color: #00a7d0
        }
        .view-pc {
            display: block;
        }
        .view-mobile {
            display: none;
        }
        @media only screen and (min-width: 768px) {
            .timekeeping-personal-detail .table-timekeeping-aggregate {
                width: 380px !important;
            }
            .top-box-title {
                float: left;
            }
            .box-header .text-center i{
                margin-left: -20%;
            }
        }
        @media only screen and (max-width: 768px) {
            .view-pc {
                display: none;
            }
            .view-mobile {
                display: block;
            }
        }
        .table-hover>tbody>tr:hover {
            background-color: #e1979745;
        }
        .table-hover>tbody>tr:hover td {
            border: aliceblue;
        }
    </style>
@endsection

@section('sidebar-common')
    @include('manage_time::timekeeping.personal.sidebar_timekeeping')
@endsection

@section('content-common')
<div class="row">
    <div class=" col-md-12">
    <div class="box box-solid timekeeping-personal-detail">
        <div class="box-header with-border">
            <h3 class="box-title top-box-title"><i class="fa fa-globe"></i> {{ trans('manage_time::view.Aggregate of timekeeping') }}</h3>
            @if ($timekeepingTable->lock_up == TimekeepingTable::CLOSE_LOCK_UP)
                <div class="text-center">
                    <i class="fa fa-lock color-7d0" aria-hidden="true" style="font-size: 20px"></i> :
                    {{ Carbon::parse( $timekeepingTable->lock_up_time)->format('d-m-Y H:i') }}
                </div>
            @endif
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <h3 class="box-title">{{ trans('manage_time::view.Total timekeeping has salary') }}<span class="fa fa-question-circle help"><span class="help-note">{{ trans('manage_time::view.Timekeeping personal help note') }}</span></span> : {{ $totalWorking . ' / ' . $worksInTimekeeping }}</h3>
                    <table class="table table-striped table-bordered table-hover table-grid-data table-timekeeping-aggregate">
                        <thead style="background-color: #d9edf7;">
                            <tr>
                                <th class="managetime-col-200 text-center">{{ trans('manage_time::view.Detail') }}</th>
                                <th class="managetime-col-60 text-center">{{ trans('manage_time::view.Number of timekeeping') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ trans('manage_time::view.The total of working days (+)') }}</td>
                                <td class="text-center">{{ number_format($totalWorkingDays, 2) }}</td>
                            </tr>
                            <tr>
                                <td>{{ trans('manage_time::view.Overtime on weekdays') }}</td>
                                <td class="text-center">{{ number_format($totalOTWeekdays, 2) }}</td>
                            </tr>
                            <tr>
                                <td>{{ trans('manage_time::view.Overtime on weekends') }}</td>
                                <td class="text-center">{{ number_format($totalOTWeekends, 2) }}</td>
                            </tr>
                            <tr>
                                <td>{{ trans('manage_time::view.Overtime on holidays') }}</td>
                                <td class="text-center">{{ number_format($totalOTHolidays, 2) }}</td>
                            </tr>
                            <tr>
                                <td>{{ trans('manage_time::view.Overtime no salary (OTKL)') }}</td>
                                <td class="text-center">{{ number_format($timekeepingAggregate->total_ot_no_salary, 2) }}</td>
                            </tr>
                            <tr>
                                <td>{{ trans('manage_time::view.Total number of late in') }}</td>
                                <td class="text-center">{{ number_format($timekeepingAggregate->total_number_late_in, 0) }}</td>
                            </tr>
                            <tr>
                                <td>{{ trans('manage_time::view.Total number of early out') }}</td>
                                <td class="text-center">{{ number_format($timekeepingAggregate->total_number_early_out, 0) }}</td>
                            </tr>
                            <tr>
                                <td>{{ trans('manage_time::view.Leave day (P)') }}</td>
                                <td class="text-center">{{ number_format($totalLeaveDayHasSalary, 2) }}</td>
                            </tr>
                            <tr>
                                <td>{{ trans('manage_time::view.Leave day no salary (KL)') }}</td>
                                <td class="text-center">{{ number_format($timekeepingAggregate->total_leave_day_no_salary, 2) }}</td>
                            </tr>
                            <tr>
                                <td>{{ trans('manage_time::view.Basic salary (LCB)') }}</td>
                                <td class="text-center">{{ number_format($totalLeaveDayBasic, 2) }}</td>
                            </tr>
                            <tr>
                                <td>{{ trans('manage_time::view.Holiday (L)') }}</td>
                                <td class="text-center">{{ number_format($totalHoliday, 2) }}</td>
                            </tr>
                            <tr>
                                <td>{{ trans('manage_time::view.Business trip (CT)') }}</td>
                                <td class="text-center">{{ number_format($totalRegisterBusinessTrip, 2) }}</td>
                            </tr>
                            <tr>
                                <td>{{ trans('manage_time::view.Supplement (BS)') }}</td>
                                <td class="text-center">{{ number_format($totalRegisterSupplement, 2) }}</td>
                            </tr>
                            <tr>
                                <td>{{ trans('manage_time::view.Late start shift (M1)') }}</td>
                                <td class="text-center">{{ number_format($timekeepingAggregate->total_late_start_shift, 2) }}</td>
                            </tr>
                            <tr>
                                <td>{{ trans('manage_time::view.Early mid shift (S1)') }}</td>
                                <td class="text-center">{{ number_format($timekeepingAggregate->total_early_mid_shift, 2) }}</td>
                            </tr>
                            <tr>
                                <td>{{ trans('manage_time::view.Late mid shift (M2)') }}</td>
                                <td class="text-center">{{ number_format($timekeepingAggregate->total_late_mid_shift, 2) }}</td>
                            </tr>
                            <tr>
                                <td>{{ trans('manage_time::view.Early end shift (S2)') }}</td>
                                <td class="text-center">{{ number_format($timekeepingAggregate->total_early_end_shift, 2) }}</td>
                            </tr>
                        </tbody>
                        @if (isset($dataNotLate) && count($dataNotLate))	
                        <table class="table table-striped table-bordered table-hover table-grid-data table-timekeeping-aggregate">	
                            <tr style="background-color: #d9edf7;">	
                                <td colspan="">Nhân viên được đi muộn mỗi ngày (M1)
                                    <span class="fa fa-question-circle help float-right">	
                                        <span class="help-note">	
                                            <ul style="padding-left: 15px">	
                                                <li>Áp dụng cho nhân viên đến làm trước 10:30 </li>	
                                                <li>Nhân viên đến làm >= 10:30 thì buổi sáng không được tính công</li>	
                                            </ul>	
                                        </span>	
                                    </span>	
                                </td>	
                                <td>Số phút được đi muộn</td>	
                            </tr>	
                            @foreach($dataNotLate as $item)	
                                <tr>	
                                    <td>	
                                        {{ $item->start_date }}  - {{ $item->end_date }}	
                                    </td>	
                                    <td class="text-center">	
                                        {{ $item->minute }}	
                                    </td>	
                                </tr>	
                            @endforeach	
                        </table>	
                    @endif
                </table>
                </div>
                <div class="col-lg-5 col-lg-offset-3 col-md-6">
                    <div class="box box-solid timekeeping-note-sidebar">
                        <div class="box-header with-border bg-aqua-active view-mobile" style="margin-bottom: 10px">
                            <div class="pull-left managetime-menu-title">
                                <h3 class="box-title">Tính công làm việc</h3>
                            </div>
                            <div class="box-tools pull-right">
                                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                                </button>
                            </div>
                            <br>
                        </div>
                        <div class="box-body no-padding">
                            <div class="box-note">
                                <h3 class="text-center view-pc">Tính công làm việc</h3>
                                <p>CÔNG LÀM VIỆC HẰNG NGÀY</p>
                                <ul>
                                    <li>Thời gian vào ra nhân viên được thêm vào bảng công khi Admin đồng bộ máy chấm công với hệ thống (rikkei.vn)</li>
    {{--                                <li>Thời gian vào ra hôm nay được thêm vào bảng công sáng hôm sau</li>--}}
    {{--                                <li>Tính thời gian làm việc hôm trước được tính vào sáng hôm sau</li>--}}
                                </ul>
                                <p>PHÉP</p>
                                <ul>
                                    {{-- <li>Các đơn phép được thêm vào bảng công khi được Admin cập nhật bảng chấm công</li> --}}
                                    <li>Các đơn phép được duyệt sẽ được thêm vào bảng chấm công</li>
                                    <li>Các đơn phép đã duyệt và cập nhật không duyệt sẽ xóa khỏi bảng chấm công</li>
                                </ul>
                                <p>CÔNG TÁC (CT)</p>
                                <ul>
                                    <li>Các đơn CT được duyệt sẽ được thêm vào bảng chấm công</li>
                                    <li>Các đơn CT đã duyệt và cập nhật không duyệt sẽ xóa khỏi bảng chấm công</li>
                                </ul>
                                <p>BỔ SUNG CÔNG (BSC)</p>
                                <ul>
                                    {{-- <li>Các đơn BSC được thêm vào bảng công khi được Admin cập nhật bảng chấm công</li> --}}
                                    <li>Các đơn BSC được duyệt sẽ được thêm vào bảng chấm công</li>
                                    <li>Các đơn BSC đã duyệt và cập nhật không duyệt sẽ xóa khỏi bảng chấm công</li>
                                </ul>
                                <p>BSC OT</p>
                                <ul>
                                    {{-- <li>Các đơn BSC OT được thêm vào bảng công khi được Admin cập nhật bảng chấm công</li> --}}
                                    <li>Các đơn BSC OT đã duyệt, thời gian làm việc OT được tính khi có đơn OT</li>
                                    <li>Các đơn BSC OT đã duyệt và cập nhật không duyệt sẽ xóa khỏi bảng chấm công</li>
                                </ul>
                                <p>ĐĂNG KÝ OT</p>
                                <ul>
                                    <li>Các đơn OT đã duyệt, thời gian làm việc OT được tính khi nhân viên có thời gian vào ra</li>
                                    <li>Tính thời gian OT onsite không cần thời gian vào ra của nhân viên</li>
                                    <li>Không tính OT sau 22:00 và trước 8:00</li>
                                </ul>
                                <p>KHÓA BẢNG CÔNG</p>
                                <ul>
                                    <li>Bảng công sau khi bị khóa sẽ hiển thị thời gian khóa cuối cùng.</li>
                                    <li>Các đơn duyệt sau khi khóa bảng công sẽ không vào bảng chấm công</li>
                                    <li>Để đơn duyệt đó vào được bảng chấm công thì cần liên hệ Admin để xem xét</li>
                                </ul>
                                <p>TIỀN PHẠT ĐI MUỘN</p>
                                <ul>
                                    <li>Được cập nhật khi bảng chấm công cập nhật</li>
    {{--                                <li>Được cập nhật hằng ngày sau khi thời gian vào ra của nhân viên được thêm vào bảng công</li>--}}
                                    <li><a href="{{ route('fines-money::profile.fines-money') }}">xem tiền phạt</a></li>
                                </ul>
                                <p style="color: red">CHÚ Ý</p>
                                <ul>
                                    <li>Các đơn Phép, CT, BSC, BSC OT, OT được duyệt trên mobile đươc thêm vào bảng công khi Admin cập nhật bảng chấm công</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /. box -->
</div>
    <div class=" col-md-12">
    <div class="box box-solid">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-info"></i> {{ trans('manage_time::view.Timekeeping detail') }}</h3>
        </div>
        <div class="table-responsive box-body">
            <table class="table table-striped table-bordered table-hover table-grid-data">
                <thead style="background-color: #d9edf7;">
                    <tr>
                        <th class="managetime-col-60">{{ trans('manage_time::view.Day of month') }}</th>
                        <th class="managetime-col-60">{{ trans('manage_time::view.Day of week') }}</th>
                        <th class="managetime-col-60">{{ trans('manage_time::view.Time in morning') }}</th>
                        <th class="managetime-col-60">{{ trans('manage_time::view.Time out morning') }}</th>
                        <th class="managetime-col-60">{{ trans('manage_time::view.Time in afternoon') }}</th>
                        <th class="managetime-col-60">{{ trans('manage_time::view.Time out afternoon') }}</th>
                        <th class="managetime-col-60 text-center">Số phút làm thừa/thiếu <span class="fa fa-question-circle tooltip-leave" data-toggle="tooltip" title="" data-html="true" data-original-title="<ul><li>> 0: Làm thừa</li> <li>< 0: Làm thiếu</li></ul>"></span></th>
                        <th class="managetime-col-200 text-center">{{ trans('manage_time::view.Timekeeping sign') }}</th>
                        @if (strpos($timekeepingTable->code, 'japan') !== false)
                        <th class="managetime-col-60 text-center">{{ trans('manage_time::view.Late in') }}</th>
                        <th class="managetime-col-60 text-center">{{ trans('manage_time::view.Early out') }}</th>
                        {{-- @else
                        <th class="managetime-col-60 text-center">{{ trans('manage_time::view.Fines late in') }}</th> --}}
                        @endif
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $days = ManageTimeConst::days();
                        $totalFinesLateIn = 0;
                        $totalLateJp = 0;
                        $totalEarlyJp = 0;
                    ?>
                    @if (isset($datesTimekeeping) && count($datesTimekeeping))
                        @foreach ($datesTimekeeping as $date)
                            <?php
                                $data = $dataKeeping[date('Y-m-d', strtotime($date))];
                                $timekeepingSign = ManageTimeCommon::getTimekeepingSign($data, $teamCodePrefix, $compensationDays, $arrHolidays[$teamCodePrefix]);
                                $timeInOut = ManageTimeView::displayTimeInOut($dataKeeping[$date->format('Y-m-d')]);
                                if (strpos($timekeepingTable->code, 'japan') !== false) {
                                    $lateJp = ManageTimeView::getLateEarly($data)['late'];
                                    $earlyJp = ManageTimeView::getLateEarly($data)['early'];
                                    $totalLateJp += $lateJp;
                                    $totalEarlyJp += $earlyJp;
                                }
                                $strWT = '';
                                if (isset($workingTimdDate) && isset($workingTimdDate[$date->format('Y-m-d')])) {
                                    $strWT = $workingTimdDate[$date->format('Y-m-d')];
                                    $arrWT = explode(" ", $strWT);
                                }
                            ?>
                            <tr title="{{ $strWT }}" class="timekeeping">
                                <td date="{{$date}}" timeStart="{{ $date->format('d-m-Y') }} {{$arrWT[1]}}" timeEnd="{{ $date->format('d-m-Y') }} {{$arrWT[6]}}">{{ $date->format('d/m/Y') }}</td>
                                <td>{{ $days[$date->dayOfWeek] }}</td>
                                <td>{{ $timeInOut['timeInMor'] }}</td>
                                <td>{{ $timeInOut['timeOutMor'] }}</td>
                                <td>{{ $timeInOut['timeInAfter'] }}</td>
                                <td>{{ $timeInOut['timeOutAfter'] }}</td>
                                <td>{{ $timeInOut['timeOver'] }}</td>
                                <td id="status">{{ $timekeepingSign[0] }}</td>
                                @if (strpos($timekeepingTable->code, 'japan') !== false)
                                <td class="text-center">
                                    {{ $lateJp }}
                                </td>
                                <td class="text-center">
                                    {{ $earlyJp }}
                                </td>
                                {{-- @else
                                <td class="text-right">
                                    <span>
                                        @php
                                            $finesMoney = with(new ManageTimeConst())->getFinesMoneyLateIn($timekeepingSign[1], $timekeepingTable->code);
                                            $totalFinesLateIn += $finesMoney;
                                            echo number_format($finesMoney, 0);
                                        @endphp
                                         đ
                                    </span>
                                </td> --}}
                                @endif
                            </tr>
                        @endforeach
                        @if (strpos($timekeepingTable->code, 'japan') !== false)
                        <tr>
                            <td colspan="7">
                                <span><b>{{ trans('manage_time::view.Total late in/early out') }}</b></span>
                            </td>
                            <td class="text-center">
                                <span><b>{{ $totalLateJp }}</b></span>
                            </td>
                            <td class="text-center">
                                <b>{{ $totalEarlyJp }}</b>
                            </td>
                        </tr>
                        {{-- @else
                        <tr>
                            <td colspan="7">
                                <span><b>{{ trans('manage_time::view.Total fines late in') }}</b></span>
                            </td>
                            <td class="text-right">
                                <span><b>
                                {{ number_format($totalFinesLateIn, 0) }} đ
                                </b></span>
                            </td>
                        </tr> --}}
                        @endif
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    <!-- /. box -->
    </div>
</div>

<div class="modal fade leave-register-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title" id="exampleModalLabel">{{ trans('manage_time::view.Leave day register') }}</h5>
            </div>
            <div class="modal-body">
                ...
            </div>
    </div>
</div>
@endsection

@section('script-common')
<script src="{{ CoreUrl::asset('asset_managetime/js/personal.js') }}"></script>

<script>
    $(function() {
        w = window.innerWidth;
        if (w <= 768) {
            $('.btn-box-tool').click();
        }
    })
    $(document).on('contextmenu', '.timekeeping', function(e){
        e.preventDefault();
        const id = $(this).find('.tooltiptext').attr('id');
        const tooltiptexts = $('.tooltiptext');
        tooltiptexts.each(function(){
            if($(this).attr('id') != id){
                $(this).remove();
            }
        });

        const that = $(this);
        const timekeepingSign = that.find('#status').text().trim();
        const date = that.find('td:first-child').attr('date').trim();
        const timeStart = that.find('td:first-child').attr('timeStart').trim();
        const timeEnd = that.find('td:first-child').attr('timeEnd').trim();
        if(timekeepingSign == 'V'){
            //$(this).css('cursor','pointer').attr('title', 'This is a hover text.');
            $(this).css('position', 'relative');
            //$(this).append(`<span style="background-color: aquamarine;">aaa</span>`);

            $(this).find('td:first-child').append(`
                <div class="tooltiptext" id="tooltiptext-{{rand(1, 10000)}}" date="`+date+`" timeStart="`+timeStart+`" timeEnd="`+timeEnd+`" style="display:inline-grid;position:absolute;z-index:100;top:20px;">
                     <span class="btn btn-primary tooltiptext-item" id="leave-register" style="border:1px;background-color:white;border-style:solid;border-width:1px;border-color:red;padding:3px;color:red;left:20px;"
                         data-toggle="modal" data-target=".leave-register-modal">{{ trans('manage_time::view.Leave day register') }}</span>
                     <span class="btn btn-primary tooltiptext-item" id="supplement-register" style="border:1px;background-color:white;border-style:solid;border-width:1px;border-color:red;padding:3px;color:red;left:20px;"
                         data-toggle="modal" data-target=".leave-register-modal">ĐK Bổ sung công</span>
                     <span class="btn btn-primary tooltiptext-item" id="ot-register" style="border:1px;background-color:white;border-style:solid;border-width:1px;border-color:red;padding:3px;color:red;left:20px;"
                        data-toggle="modal" data-target=".leave-register-modal">ĐK Làm thêm (OT)
                    </span>
                </div>
            `);
        }
    })

    $(document).on('click', '.tooltiptext-item', function(){
        const parentTooltip = $(this).parent();
        let url = '';
        const id = $(this).attr('id');
        if(id == 'leave-register'){
            url = window.location.origin + '/get-leave-register';
        }else if(id == 'supplement-register'){
            url = window.location.origin + '/supplement-register';
        }else if(id == 'ot-register'){
            url = window.location.origin + '/get-leave-register';
        }
        const date = parentTooltip.attr('date');
        let timeStart = parentTooltip.attr('timeStart');
        let timeEnd = parentTooltip.attr('timeEnd');
        $.ajax ({
            url: url,
            method : 'POST',
            data: {
                date: date,
                "_token": "{{ csrf_token() }}"
            },
            success: function(data) {
                $('.leave-register-modal .modal-body').html(data.renderHtml);

                if(id == 'leave-register'){

                }else if(id == 'supplement-register'){
                    $('#start_date').val(timeStart);
                    $('#end_date').val(timeEnd);
                    $('#exampleModalLabel').text('ĐK Bổ sung công');
                    $('#number_days_off').val(1);
                }else if(id == 'ot-register'){

                }
                parentTooltip.remove();
            }
        });
    })

    $(document).on('click', '#submit ', function(e){
        e.preventDefault();
        console.log('aaa')
        var data = $('#form-register').serialize();
        data += '&_token={{ csrf_token() }}';
        data += '&isAjax=1';
        let url = window.location.origin + '/leave-register'
        const action = $(this).attr('action');
        if(action == 'SupplementRegister'){
            url = window.location.origin + '/submit-supplement-register';
        }

        $.ajax ({
            url: url,
            method : 'POST',
            dataType: 'json',
            data: data,
            success: function(data) {
                alert(data.message)
            }
        });
    })

</script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>

<script>
    function checkFormLeaveDayRegister() {
        $('.managetime-error').hide();
        console.log($('#check_status'))
        if ($('#check_status').length > 0) {
            var checkStatus = $('#check_status').val();
            if (checkStatus == STATUS_APPROVED) {
                $('#show_notification').text(notificationStatusApproved);
                $('#modal_allow_edit').modal('show');
                return false;
            }
            if (checkStatus == STATUS_CANCEL) {
                $('#show_notification').text(notificationStatusCanceled);
                $('#modal_allow_edit').modal('show');
                return false;
            }
        }

        var status = 1;
        $('#check_submit').val(1);

        var startDate = $('#datetimepicker-start-date').data("DateTimePicker").date();
        if (startDate == null) {
            $('#start_date-error').show();
            status = 0;
        }
        var endDate = $('#datetimepicker-end-date').data("DateTimePicker").date();
        if (endDate == null) {
            $('#end_date-error').show();
            status = 0;
        }

        if (startDate != null && endDate != null) {
            var diffDate = $('#number_days_off').val();
            if (parseFloat(diffDate) <= 0) {
                $('#end_date_before_start_date-error').show();
                status = 0;
            } else {
                var registerId = $('#register_id').val();
                var employeeId = $('#employee_id').val();
                if ($('#employee_id').length) {
                    if (employeeId != null) {
                        var isExistRegister = checkExistRegister($('#start_date').val(), $('#end_date').val(), registerId, employeeId);
                        if (isExistRegister) {
                            $('#register_exist_error').show();
                            status = 0;
                        }
                    }
                }
            }
        }

        if ($('#employee_id').length) {
            var employeeId = $('#employee_id').val();
            if (employeeId == null) {
                $('#registrant-error').show();
                status = 0;
            }
        }

        if ($('#approver').length) {
            var approver = $('#approver').val();
            if (approver == null) {
                $('#approver-error').show();
                status = 0;
            }
        }

        // Validate leave reason
        var reasonSelected = $('#reason').find(":selected");
        var reasonCode = reasonSelected.attr("data-reason-code");
        var numberDaysOff = $('#number_days_off').val();
        if (reasonCode == USED_LEAVE_DAY) {
            var numberDaysRemain = $('#number_days_remain').val();
            var numberDaysUnapprove = $('#number_unapprove').val();

            if (parseFloat(numberDaysOff) - parseFloat(oldNumberDaysOff) > parseFloat(numberDaysRemain) - parseFloat(numberDaysUnapprove)) {
                $('#number_days_off-error').show();
                status = 0;
            }

        }

        // validate leave special reason
        if (reasonSelected.data('type') === leaveSpecialType) {
            if (numberDaysOff > reasonSelected.data('value')) {
                $('#reason_special_value-error').show();
                status = 0;
            } else {
                if (parseInt(reasonSelected.data('repeated')) > 0) {
                    var registerId = $('#register_id').val();
                    var employeeId = $('#employee_id').val();
                    if ($('#employee_id').length) {
                        if (employeeId != null) {
                            var isExistRegisterType = checkExistRegisterType($('#start_date').val(), registerId, employeeId);
                            if (isExistRegisterType.exist) {
                                $('#register_type_exist_error').text(isExistRegisterType.message)
                                $('#register_type_exist_error').show();
                                status = 0;
                            }
                        }
                    }
                }
            }
        }

        var reasonRegister = $('#reason').val().trim();
        if (reasonRegister == '') {
            $('#reason-error').show();
            status = 0;
        }

        var teamCodePreOfEmp = $('#team_code_pre_of_emp').val().trim();
        var codePrefixJp = $('#code_prefix_jp').val().trim();
        var reason = $('#reason').val().trim();
        var reasonPaidLeaveJp = $('#reason_paid_leave_jp').val().trim();
        // if user type japan
        if (teamCodePreOfEmp == codePrefixJp) {
            // if leave day reason not paid leave
            if (reason != reasonPaidLeaveJp) {
                var note = $('#note').val().trim();
                if (note == '') {
                    $('#note-error').show();
                    status = 0;
                }
            }
        } else {
            var note = $('#note').val().trim();
            if (note == '') {
                $('#note-error').show();
                status = 0;
            }
        }

        if (status == 0) {
            return false;
        }

        return true;
    }
</script>
@endsection