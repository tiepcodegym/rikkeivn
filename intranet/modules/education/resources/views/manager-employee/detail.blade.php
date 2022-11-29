<?php

use Rikkei\Core\View\CoreUrl;
/**
 * Created by PhpStorm.
 * User: quanhv
 * Date: 08/01/20
 * Time: 11:03
 */

use Rikkei\Team\View\Permission;
use Carbon\Carbon;
use Rikkei\Core\View\Form;

?>
@extends('layouts.default')

@section('title')
    {{ trans('education::view.manager_employee.Education detail') . ': ' . $employee->name }}
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css"/>
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="{{ CoreUrl::asset('team/css/style.css') }}"/>
@endsection
@section('content')
    <div id="preview_table"></div>
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-info filter-wrapper"
                 data-url="{{ $urlFilter }}">
                <div class="box-body filter-mobile-left">
                    <div class="row">
                        <div class="col-sm-8">
                            <div class="team-select-box" style="width: 25%">
                                <label for="select-member-role">{{trans('education::view.manager_employee.labels.from_date')}}</label>
                                <div class="input-box">
                                    <input type="text" class='form-control' id="from_date" value="{{ $fromDate }}"
                                           placeholder="{{ trans('education::view.manager_employee.labels.search') }}"/>
                                </div>
                            </div>
                            <div class="team-select-box" style="width: 25%">
                                <label for="select-member-role">{{trans('education::view.manager_employee.labels.to_date')}}</label>
                                <div class="input-box">
                                    <input type="text" class='form-control' id="to_date" value="{{ $toDate }}"
                                           placeholder="{{ trans('education::view.manager_employee.labels.search') }}"/>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-12">
                        <h3>{{ trans('education::view.manager_employee.header_table.Join training') }}</h3>
                        <div class="table-responsive" autocomplete="off">
                            <table class="table table-bordered table-hover dataTable" id="table_list_teaching"
                                   role="grid" aria-describedby="example2_info">
                                <thead>
                                <tr role="row">
                                    <th class="col-sm-1">{{trans('education::view.manager_employee.header_table.No')}}</th>
                                    <th class="col-sm-1">{{trans('education::view.manager_employee.header_table.Shift')}}</th>
                                    <th class="col-sm-2">{{trans('education::view.manager_employee.header_table.Class name')}}</th>
                                    <th class="col-sm-1">{{trans('education::view.manager_employee.header_table.Course name')}}</th>
                                    <th class="col-sm-1">{{trans('education::view.manager_employee.header_table.Enrolment Date')}}</th>
                                    <th class="col-sm-1">{{trans('education::view.manager_employee.header_table.Number student')}}</th>
                                    <th class="col-sm-2">{{trans('education::view.manager_employee.header_table.Number hours teaching')}}</th>
                                    <th class="col-sm-1">{{trans('education::view.manager_employee.header_table.Point average')}}</th>
                                    <th class="col-sm-1">{{trans('education::view.manager_employee.header_table.Curator')}}</th>
                                </tr>
                                <tr class="filter-input-grid">
                                    <td class="no-border table-custom-border"></td>
                                    <td class="no-border"></td>
                                    <td class="no-border">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" class='form-control' id="teaching_class_name"
                                                       placeholder="{{ trans('education::view.manager_employee.labels.search') }}"/>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="no-border">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" class='form-control' id="teaching_courses_name"
                                                       placeholder="{{ trans('education::view.manager_employee.labels.search') }}"/>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="no-border"></td>
                                    <td class="no-border"></td>
                                    <td class="no-border"></td>
                                    <td class="no-border"></td>
                                    <td class="no-border"></td>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-sm-12">
                        <h3>{{ trans('education::view.manager_employee.header_table.Join study') }}</h3>
                        <div class="table-responsive" autocomplete="off">
                            <table class="table table-striped table-bordered table-condensed dataTable"
                                   id="table_list_study" role="grid" aria-describedby="example2_info">
                                <thead>
                                <tr role="row">
                                    <th class="col-sm-1">{{trans('education::view.manager_employee.header_table.No')}}</th>
                                    <th class="col-sm-1">{{trans('education::view.manager_employee.header_table.Shift')}}</th>
                                    <th class="col-sm-2">{{trans('education::view.manager_employee.header_table.Class name')}}</th>
                                    <th class="col-sm-1">{{trans('education::view.manager_employee.header_table.Course name')}}</th>
                                    <th class="col-sm-1">{{trans('education::view.manager_employee.header_table.Enrolment Date')}}</th>
                                    <th class="col-sm-2">{{trans('education::view.manager_employee.header_table.Number hours study')}}</th>
                                    <th class="col-sm-1">{{trans('education::view.manager_employee.header_table.Start date commitment')}}</th>
                                    <th class="col-sm-1">{{trans('education::view.manager_employee.header_table.End date commitment')}}</th>
                                    <th class="col-sm-1">{{trans('education::view.manager_employee.header_table.Curator')}}</th>
                                </tr>
                                <tr>
                                    <th class="no-border"></th>
                                    <th class="no-border"></th>
                                    <td class="no-border">
                                        <div class="row" style="z-index: -1">
                                            <div class="col-md-12">
                                                <input type="text" class='form-control' id="student_class_name"
                                                       placeholder="{{ trans('education::view.manager_employee.labels.search') }}"/>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="no-border">
                                        <div class="row" style="z-index: -1">
                                            <div class="col-md-12">
                                                <input type="text" class='form-control' id="student_courses_name"
                                                       placeholder="{{ trans('education::view.manager_employee.labels.search') }}"/>
                                            </div>
                                        </div>
                                    </td>
                                    <th class="no-border"></th>
                                    <th class="no-border"></th>
                                    <th class="no-border"></th>
                                    <th class="no-border"></th>
                                    <th class="no-border"></th>
                                </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 text-right member-group-btn">
            <a class="btn btn-primary"
               href="{{route('education::education.manager.employee.index')}}">{{ trans('education::view.manager_employee.buttons.back') }}</a>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ URL::asset('lib/js/moment.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript">
        let globalAjaxUrl = '{{route('education::education.manager.employee.ajax.education.detail')}}';
        var globalDataLang = {
            'sProcessing': '{{ trans('education::view.manager_employee.paging_ajax_label.sProcessing') }}',
            'sLengthMenu': '{{ trans('education::view.manager_employee.paging_ajax_label.sLengthMenu') }}',
            'sZeroRecords': '{{ trans('education::view.manager_employee.paging_ajax_label.sZeroRecords') }}',
            'sInfo': '{{ trans('education::view.manager_employee.paging_ajax_label.sInfo') }}',
            'sInfoEmpty': '{{ trans('education::view.manager_employee.paging_ajax_label.sInfoEmpty') }}',
            'sInfoFiltered': '',
            'sInfoPostFix': '',
            'sUrl': '',
            'oPaginate': {
                'sFirst': '{{trans('education::view.manager_employee.paging_ajax_label.sFirst')}}',
                'sPrevious': '{{trans('education::view.manager_employee.paging_ajax_label.sPrevious')}}',
                'sNext': '{{trans('education::view.manager_employee.paging_ajax_label.sNext')}}',
                'sLast': '{{trans('education::view.manager_employee.paging_ajax_label.sLast')}}',
            }
        };
        let globalEmployeeId = '{{$employee->id}}';
    </script>
    <script src="{{ URL::asset('education/js/manager-employee-detail.js') }}"></script>
@endsection
