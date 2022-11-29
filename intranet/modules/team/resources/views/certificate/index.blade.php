<?php
use Rikkei\Team\View\TeamList;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Core\View\CookieCore;
$teamsOptionAll = TeamList::toOption(null, true, false);
?>
@extends('layouts.default')
@section('title')
    {{ trans('team::view.Certificate list') }}
@endsection
@section('css')
    <link rel="stylesheet" type="text/css"
          href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/css/select2.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="{{ CoreUrl::asset('common/css/style.css') }}"/>
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <div class="row">
                        <div class="box-body filter-mobile-left">
                            <div class="col-lg-2 col-sm-3">
                                <div class="team-select-box">
                                    {{-- show team available --}}
                                    @if (is_object($teamIdsAvailable))
                                        <p>
                                            <b>Team:</b>
                                            <span>{{ $teamIdsAvailable->name }}</span>
                                        </p>
                                    @elseif ($teamIdsAvailable || ($teamTreeAvailable && count($teamTreeAvailable)))
                                        <label for="select-team-member">{{ trans('team::view.Choose team') }}</label>
                                        <div class="input-box">
                                            <select multiple="multiple" name="team_all" id="select-team-member"
                                                    class="form-control "
                                                    autocomplete="off">
                                                {{-- show all member --}}
                                                @if ($teamIdsAvailable === true)
                                                    <option value="0" <?php
                                                    if (!$teamIdCurrent): ?> selected<?php endif;
                                                    ?><?php
                                                    if ($teamIdsAvailable !== true): ?> disabled<?php endif;
                                                        ?>>All team
                                                    </option>
                                                @endif
                                                {{-- show team available --}}
                                                @if ($teamIdsAvailable === true || (count($teamsOptionAll) && $teamTreeAvailable))
                                                    @foreach($teamsOptionAll as $option)
                                                        @if ($teamIdsAvailable === true || in_array($option['value'], $teamTreeAvailable))
                                                            @if(!empty($cookieFilter->team_ids) && in_array($option['value'],$cookieFilter->team_ids))
                                                                <option selected="selected"
                                                                        value="{{ $option['value'] }}" <?php
                                                                        if ($option['value'] == $teamIdCurrent): ?> selected<?php endif;
                                                                ?><?php
                                                                if ($teamIdsAvailable === true):
                                                                elseif (!in_array($option['value'], $teamIdsAvailable)): ?> disabled<?php else:
                                                                    ?>{{ $option['option'] }}<?php endif; ?>>{{ $option['label'] }}</option>
                                                            @else
                                                                <option value="{{ $option['value'] }}" <?php
                                                                if ($option['value'] == $teamIdCurrent): ?> selected<?php endif;
                                                                ?><?php
                                                                if ($teamIdsAvailable === true):
                                                                elseif (!in_array($option['value'], $teamIdsAvailable)): ?> disabled<?php else:
                                                                    ?>{{ $option['option'] }}<?php endif; ?>>{{ $option['label'] }}</option>
                                                            @endif
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    @endif
                                    {{-- end show team available --}}
                                </div>
                            </div>
                            <div class="col-lg-2 col-sm-3">
                                <div>
                                    <label for="select-certificate">{{ trans('team::view.Certificate type') }}</label>
                                </div>
                                <select name="team_all" class="form-control" id="select-certificate" autocomplete="off">
                                    @if (isset($listCertificate) && count($listCertificate) > 0)
                                        <option value="">&nbsp;</option>
                                        @foreach($listCertificate as $index=>$certificate)
                                            @if(!empty($cookieFilter->type) && in_array($index,[$cookieFilter->type]))
                                                <option selected="selected" value="{{$index}}">{{$certificate}}</option>
                                            @else
                                                <option value="{{$index}}">{{$certificate}}</option>
                                            @endif

                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="pull-right">
                                <div>
                                    <label class="col-md-12">&nbsp;</label>
                                </div>
                                <button class="btn btn-primary" id="search-certificate">
                                    <span>{{ trans('team::view.Search') }}<i
                                                class="fa fa-spin fa-refresh hidden"></i></span>
                                </button>
                                <button class="btn btn-primary btn-reset-filter" id="reset-certificate">
                                    <span>{{ trans('team::view.Reset filter') }}<i
                                                class="fa fa-spin fa-refresh hidden"></i></span>
                                </button>
                                <a class="btn btn-success" id="export-data" type="button"
                                   style="display: none;">{{ trans('team::view.Export') }}</a>
                                <a class="btn btn-success" id="export-image" type="button"
                                   style="display: none;">{{ trans('team::view.Export img') }}</a>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive" id="table-load">
                        <table class="table table-striped dataTable table-bordered table-hover table-grid-data"
                               id="managetime_table_primary">
                            <thead class="managetime-thead">
                            <tr>
                                <th class="col-no" style="min-width: 15px;">{{trans('team::view.No.')}}</th>
                                <th class="col-creator-code"
                                    style="min-width: 60px; max-width: auto;">{{trans('team::view.Employee code')}}</th>
                                <th class="col-creator-name"
                                    style="min-width: 90px; max-width: auto;">{{trans('team::view.Employee')}}</th>
                                <th class="col-location"
                                    style="min-width: 200px;max-width: 200px; ">{{trans('team::view.Certificate')}}</th>
                                <th class="col-location"
                                    style="min-width: 200px;max-width: 200px; ">{{trans('team::view.Level')}}</th>
                                <th class="col-date-start"
                                    style="min-width: 100px; max-width: 100px;">{{trans('team::view.From date')}}</th>
                                <th class="col-date-end"
                                    style="min-width: 100px; max-width: 100px;">{{trans('team::view.To date')}}</th>
                                <th class="col-date-confirm"
                                    style="min-width: 200px; max-width: 200px;">{{trans('team::view.Date approved/Reject')}}
                                </th>
                                <th class="col-status"
                                    style="min-width: 200px; max-width: 200px;">Status
                                </th>
                            </tr>
                            <tr>
                                <th class="col-no" style="min-width: 15px;"></th>
                                <th class="col-creator-code" style="min-width: 60px; max-width: auto;"></th>
                                <th class="col-creator-name" style="min-width: 90px; max-width: auto;"></th>
                                <th class="col-location" style="min-width: 200px;max-width: 200px; ">
                                    <select name="team_all" class="form-control" id="select-certificate-2"
                                            autocomplete="off">
                                        @if (isset($certificates) && count($certificates) > 0)
                                            <option value="">&nbsp;</option>
                                            @foreach($certificates as $index=>$certificate)
                                                @if(!empty($cookieFilter->certificateId) && in_array($certificate['id'],[$cookieFilter->certificateId]))
                                                    <option selected="selected"
                                                            value="{{$certificate['id']}}">{{$certificate['name']}}</option>
                                                @else
                                                    <option value="{{$certificate['id']}}">{{$certificate['name']}}</option>
                                                @endif
                                            @endforeach
                                        @endif
                                    </select>
                                </th>
                                <th class="col-location" style="min-width: 200px;max-width: 200px; "></th>
                                <th class="col-date-start" style="min-width: 100px;max-width: 100px; ">
                                    <input type="text" class="form-control"
                                           <?php if(!empty($cookieFilter->startDate)): ?> value="{{$cookieFilter->startDate}}"
                                           <?php endif;?> id="startDate" autocomplete="off" placeholder="yyyy-mm-dd"
                                           data-flag-type="date"/>
                                </th>
                                <th class="col-date-end" style="min-width: 100px; max-width: 100px; ">
                                    <input type="text" class="form-control"
                                           <?php if(!empty($cookieFilter->endDate)): ?> value="{{$cookieFilter->endDate}}"
                                           <?php endif;?> id="endDate" placeholder="yyyy-mm-dd" data-flag-type="date"
                                           autocomplete="off"/>
                                </th>
                                <th class="col-date-confirm-to" style="min-width: 200px;max-width: 200px; ">
                                    <input type="text" class="form-control"
                                           <?php if(!empty($cookieFilter->dateConfirm)): ?> value="{{$cookieFilter->dateConfirm}}"
                                           <?php endif;?> id="dateConfirm" autocomplete="off" placeholder="yyyy-mm-dd"
                                           data-flag-type="date"/>
                                </th>
                                <th class="col-status" style="min-width: 200px; max-width: 200px; ">
                                    <select name="team_all" class="form-control" id="select-certificate-status"
                                            autocomplete="off">
                                        @if (isset($statusCertificate) && count($statusCertificate) > 0)
                                            <option value="">&nbsp;</option>
                                            @foreach($statusCertificate as $key => $value)
                                                @if(!empty($cookieFilter->status) && in_array($key , [$cookieFilter->status]))
                                                    <option selected="selected"
                                                            value="{{$key}}">{{$value}}</option>
                                                @else
                                                    <option value="{{$key}}">{{$value}}</option>
                                                @endif
                                            @endforeach
                                        @endif
                                    </select>
                                </th>
                            </tr>
                            </thead>
                            <tbody id="position_start_header_fixed">
                            <tr id="no-data">
                                <td colspan="7" class="text-center"
                                    height="100">{{trans('team::view.Please click button search to get information')}}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endsection
        @section('script')
            <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/js/select2.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
            <script type="text/javascript"
                    src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
            <script src="{{ CoreUrl::asset('team/js/script.js') }}"></script>
            <script>

                $(function () {
                    var listId = [];
                    $('#startDate').datetimepicker();
                    $('#endDate').datetimepicker({
                        useCurrent: false //Important! See issue #1075
                    });
                    $("#startDate").on("dp.change", function (e) {
                        $('#endDate').data("DateTimePicker").minDate(e.date);
                        createCookieFilter();
                    });
                    $("#endDate").on("dp.change", function (e) {
                        $('#startDate').data("DateTimePicker").maxDate(e.date);
                        createCookieFilter();
                    });
                    $("#select-team-member").select2();
                    $('#select-certificate').select2({});
                    $('#select-certificate-2').select2({});
                    $('#select-certificate-status').select2({});
                    $('#reset-certificate').click(function () {
                        forgetCookieFilter();
                    });
                    $('#select-team-member').change(function () {
                        createCookieFilter();
                    })
                    $('#select-certificate').change(function () {
                        createCookieFilter();
                    })
                    $('#select-certificate-2').change(function () {
                        createCookieFilter();
                    })
                    $('#select-certificate-status').change(function () {
                        createCookieFilter();
                    })

                    function forgetCookieFilter() {
                        var token = "{{ csrf_token() }}";
                        $.ajax({
                            url: '/team/report/forgetCertificate',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                "_token": token,
                            }
                        }).done(function (res) {

                        })
                    }

                    function createCookieFilter() {
                        var team_ids = $("#select-team-member").select2('val');
                        var type = $("#select-certificate").select2('val');
                        var startDate = $('#startDate').val();
                        var endDate = $('#endDate').val();
                        var dateConfirm = $('#dateConfirm').val();
                        var status = $('#status').val();
                        var select = $('#select-certificate-2').select2('val');
                        var token = "{{ csrf_token() }}";
                        $.ajax({
                            url: '/team/report/cookieCertificate',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                "_token": token,
                                'team_ids': team_ids,
                                'startDate': startDate,
                                'endDate': endDate,
                                'dateConfirm': dateConfirm,
                                'status': status,
                                'type': type,
                                'certificateId': select,

                            }
                        }).done(function (res) {

                        })
                    }

                    function htmlEntities(str) {
                        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
                    }

                    $('#search-certificate').click(function () {
                        var team_ids = $("#select-team-member").select2('val');
                        var type = $("#select-certificate").select2('val');
                        var startDate = $('#startDate').val();
                        var endDate = $('#endDate').val();
                        var dateConfirm = $('#dateConfirm').val();
                        var select = $('#select-certificate-2').select2('val');
                        var status = $('#select-certificate-status').select2('val');
                        if (startDate !== '') {
                            startDate = moment(startDate).format("YYYY/MM/DD");
                        }
                        if (endDate !== '') {
                            endDate = moment(endDate).format("YYYY/MM/DD");
                        }
                        if (dateConfirm !== '') {
                            dateConfirm = moment(dateConfirm).format("YYYY/MM/DD");
                        }
                        var token = "{{ csrf_token() }}";
                        $.ajax({
                            url: '/team/report/reportCertificate',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                "_token": token,
                                'team_ids': team_ids,
                                'startDate': startDate,
                                'endDate': endDate,
                                'dateConfirm': dateConfirm,
                                'status': status,
                                'type': type,
                                'certificateId': select,
                            }
                        }).done(function (res) {
                            if (res.success) {
                                var listTeam = Object.values(res.listTeam);
                                if (listTeam.length <= 0) {
                                    $('#export-data').css('display', 'none');
                                    $('#export-image').css('display', 'none');
                                    $('#table-load').find('#position_start_header_fixed').html('<tr id="no-data"><td colspan="7" class="text-center" height="100">' + "{{trans('team::view.No data found')}}" + '</td></tr>');
                                } else {
                                    $('#table-load').find('#position_start_header_fixed').html('');
                                }
                                listTeam.forEach(function (item) {
                                    var value = Object.values(item);
                                    value = value[0][0];
                                    $i = 0;
                                    var html = '<tr><td colspan="7" class="text-left"><b>' + value['teams_name'] + '</b></td></tr>';
                                    $('#table-load').find('#position_start_header_fixed').append(html);
                                    Object.values(item).forEach(function (item1) {
                                        var htmlChild = '';
                                        htmlChild = htmlChild + '<tr><td colspan="7"></td></tr>';
                                        item1.forEach(function (item2, index) {
                                            $i++;
                                            let confirmDate = item2['confirm_date'] !== null ? moment(item2['confirm_date']).format("YYYY-MM-DD") : '';
                                            let start_at = item2['start_at'] !== null ? moment(item2['start_at']).format("YYYY-MM-DD") : '';
                                            let end_at = item2['end_at'] !== null ? moment(item2['end_at']).format("YYYY-MM-DD") : '';
                                            listId.push(parseInt(item2['employee_certies_id']));
                                            htmlChild = htmlChild + '<tr>' +
                                                '<td class="col-no" style="min-width: 15px;">' + $i + '</td>' +
                                                '<td class="col-creator-code" style="min-width: 60px; max-width: auto;">' + item2['employee_code'] + '</td>' +
                                                '<td class=" managetime-show-popup">' + item2['employees_name'] + '</td>' +
                                                '<td class="" style="min-width: 200px;max-width: 200px ">' + htmlEntities(item2['name']) + '</td>' +
                                                '<td class="col-location" style="min-width: 200px;max-width: 200px ">' + item2['level'] + '</td>' +
                                                '<td class="col-date-start" style="min-width: 100px;max-width: 100px; ">' + start_at + '</td>' +
                                                '<td class="col-date-end" style="min-width: 200px;max-width: 200px; ">' + end_at + '</td>' +
                                                '<td class="col-date-confirm" style="min-width: 200px;max-width: 200px; ">' + confirmDate + '</td>' +
                                                '<td class="col-status" style="min-width: 200px;max-width: 200px; ">' + item2['status'] + '</td>' +
                                                '</tr>';
                                        });
                                        $('#table-load').find('#position_start_header_fixed').append(htmlChild);

                                    })
                                })
                                if (listTeam.length > 0) {
                                    var startDate = $('#startDate').val();
                                    var endDate = $('#endDate').val();
                                    var team_ids = $("#select-team-member").select2('val');
                                    var select = $('#select-certificate-2').select2('val');
                                    if (startDate !== '') {
                                        startDate = moment(startDate).format("YYYY/MM/DD");
                                    }
                                    if (endDate !== '') {
                                        endDate = moment(endDate).format("YYYY/MM/DD");
                                    }
                                    var select = $('#select-certificate-2').select2('val');
                                    if (dateConfirm !== '') {
                                        dateConfirm = moment(dateConfirm).format("YYYY/MM/DD");
                                    }
                                    var status = $('#select-certificate-status').select2('val');
                                    var type = $("#select-certificate").select2('val');
                                    $("#export-data").attr("href", "/team/report/exportCertificate?_token=" + token + '&team_ids=' + team_ids + '&startDate=' + startDate + '&endDate=' + endDate + '&certificateId=' + select + '&type=' + type + '&dateConfirm=' + dateConfirm +
                                        '&status=' + status);
                                    $("#export-image").attr("href", "/team/report/exportCertificate?_token=" + token + '&team_ids=' + team_ids + '&startDate=' + startDate + '&endDate=' + endDate + '&certificateId=' + select + '&type=' + type + '&dateConfirm=' + dateConfirm +
                                        '&status=' + status + '&isImg=true');
                                    $('#export-data').css('display', '');
                                    $('#export-image').css('display', '');
                                }
                            }
                        });
                    });
                });
            </script>

@endsection
