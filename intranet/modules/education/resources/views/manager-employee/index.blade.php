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
use Rikkei\Team\Model\Employee;
use Rikkei\Team\View\TeamList;
use \Rikkei\Education\Http\Services\EmployeeService;

$dataSearch = Form::getFilterData('search_employee', null, null);
$teamsOptionAll = TeamList::toOption(null, true, false);

?>
@extends('layouts.default')

@section('title')
    {{ trans('education::view.manager_employee.Manager employee') }}
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css"
          rel="stylesheet" type="text/css">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css"/>
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
                                    <input type="text"
                                           id="fromDate"
                                           class='form-control filter-grid'
                                           name="filter[search_employee][from_date]"
                                           value="{{ isset($dataSearch['from_date']) ? $dataSearch['from_date'] : null }}"
                                           placeholder="{{ trans('education::view.manager_employee.labels.search') }}"/>
                                </div>
                            </div>
                            <div class="team-select-box" style="width: 25%">
                                <label for="select-member-role">{{trans('education::view.manager_employee.labels.to_date')}}</label>
                                <div class="input-box">
                                    <input type="text"
                                           id="toDate"
                                           class='form-control filter-grid'
                                           name="filter[search_employee][to_date]"
                                           value="{{ isset($dataSearch['to_date']) ? $dataSearch['to_date'] : null }}"
                                           placeholder="{{ trans('education::view.manager_employee.labels.search') }}"/>
                                </div>
                            </div>
                            <div class="team-select-box">
                                <label>{{trans('education::view.manager_employee.labels.role')}}</label>
                                <div class="input-box">
                                    <select class="form-control select-search select-grid filter-grid"
                                            id="list_study_status"
                                            name="filter[search_employee][study_role]" autocomplete="off">
                                        <option>&nbsp;</option>
                                        @foreach ($roles as $key => $role)
                                            <option value="{{ $key }}"
                                                    @if(isset($dataSearch['study_role']) && $dataSearch['study_role'] == $key) selected @endif>
                                                {{trans('education::view.manager_employee.roles.' . $role)}}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 text-right member-group-btn">

                            @if (count($collectionModel) > 0)
                                <button class="btn btn-success" id="modal-confirm-export" type="button"
                                        data-toggle="modal"
                                        data-target="#modal_member_export">{{ trans('education::view.manager_employee.buttons.export') }}</button>
                            @endif
                            <button class="btn btn-primary btn-reset-filter">
                                <span>{{ trans('education::view.manager_employee.buttons.reset_field') }}
                                    <i class="fa fa-spin fa-refresh hidden"></i>
                                </span>
                            </button>
                            <button class="btn btn-primary btn-search-filter">
                                <span>{{ trans('education::view.manager_employee.buttons.search') }}
                                    <i class="fa fa-spin fa-refresh hidden"></i>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover dataTable" aria-describedby="example2_info">
                        <thead>
                        <tr role="row">
                            <th class="col-sm-1">{{trans('education::view.manager_employee.header_table.Code')}}</th>
                            <th class="col-sm-1">{{trans('education::view.manager_employee.header_table.Name')}}</th>
                            <th class="col-sm-1">{{trans('education::view.manager_employee.header_table.Email')}}</th>
                            <th class="col-sm-1">{{trans('education::view.manager_employee.header_table.Team')}}</th>
                            <th class="col-sm-1">{{trans('education::view.manager_employee.header_table.Position')}}</th>
                            <th class="col-sm-1">{{trans('education::view.manager_employee.header_table.Leader')}}</th>
                            <th class="col-sm-2">{{trans('education::view.manager_employee.header_table.Number class teaching')}}</th>
                            <th class="">{{trans('education::view.manager_employee.header_table.Number hours teaching')}}</th>
                            <th class="col-sm-1">{{trans('education::view.manager_employee.header_table.Number class study')}}</th>
                            <th class="col-sm-1">{{trans('education::view.manager_employee.header_table.Number hours study')}}</th>
                            <th style="width: 30px"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="">
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" class='form-control filter-grid select-grid'
                                               name="filter[search_employee][employee_code]"
                                               value="{{ isset($dataSearch['employee_code']) ? $dataSearch['employee_code'] : null  }}"
                                               placeholder="{{ trans('education::view.manager_employee.labels.search') }}"/>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text"
                                               class='form-control filter-grid'
                                               name="filter[search_employee][employee_name]"
                                               value="{{ isset($dataSearch['employee_name']) ? $dataSearch['employee_name'] : null  }}"
                                               placeholder="{{ trans('education::view.manager_employee.labels.search') }}"/>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text"
                                               class='form-control filter-grid'
                                               name="filter[search_employee][employee_email]"
                                               value="{{ isset($dataSearch['employee_email']) ? $dataSearch['employee_email'] : null  }}"
                                               placeholder="{{ trans('team::view.Search') }}..."/>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">

                                        @if (is_object($teamIdsAvailable))
                                            <select style="width: 100%" name="filter[search_employee][team_id]"
                                                    id="filter_team_id"
                                                    class="form-control select-grid filter-grid select-search has-search">
                                                <option value="{{$teamIdsAvailable->id}}">
                                                    {{ $teamIdsAvailable->name }}
                                                </option>
                                            </select>
                                        @elseif ($teamIdsAvailable || ($teamTreeAvailable && count($teamTreeAvailable)))
                                            <select style="width: 100%" name="filter[search_employee][team_id]"
                                                id="filter_team_id"
                                                class="form-control select-grid filter-grid select-search has-search">
                                            @if ($teamIdsAvailable === true)
                                                <option value="" <?php
                                                if (!$teamIdCurrent): ?> selected<?php endif;
                                                ?><?php
                                                if ($teamIdsAvailable !== true): ?> disabled<?php endif;
                                                    ?>>&nbsp;
                                                </option>
                                            @endif
                                            {{-- show team available --}}
                                            @if ($teamIdsAvailable === true || (count($teamsOptionAll) && $teamTreeAvailable))
                                                @foreach($teamsOptionAll as $option)
                                                    @if ($teamIdsAvailable === true || in_array($option['value'], $teamTreeAvailable))
                                                        <option value="{{$option['value']}}"
                                                                {{--set selected--}}
                                                                <?php if ($option['value'] == $teamIdCurrent): ?>
                                                                selected
                                                                <?php endif; ?>

                                                                {{--Set disabled--}}
                                                                <?php
                                                                if ($teamIdsAvailable === true):
                                                                    elseif (!in_array($option['value'], $teamIdsAvailable)):
                                                                ?>
                                                                disabled
                                                        <?php else: ?>
                                                                {{--Set value for option--}}
                                                                {{ $option['option'] }}
                                                        <?php endif; ?> >
                                                            {{--Set label for option--}}
                                                            {{ $option['label'] }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </select>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @foreach($collectionModel as $employee)
                            <tr>
                                <td>{{$employee->employee_code}}</td>
                                <td>{{$employee->name}}</td>
                                <td>{{$employee->email}}</td>
                                <td>
                                    <?php
                                    $teamNames = $employee->getTeamOfEmployee->pluck('team_name')->toArray();
                                    $leaderNames = $employee->getLeaderOfTeam->pluck('name')->toArray();
                                    $dataEducationOfMember = [
                                        'number_class_of_teacher' => 0,
                                        'number_hours_of_teacher' => 0,
                                        'number_class_of_member' => 0,
                                        'number_hours_of_member' => 0,
                                    ];

                                    foreach ($employee->educationClassDetail as $item) {
                                        if ($item->role == Employee::ROLE_STUDENT) {
                                            $dataEducationOfMember['number_class_of_member'] = $item->count_study;
                                            $dataEducationOfMember['number_hours_of_member'] = $item->sum_time;
                                        }
                                        if ($item->role == Employee::ROLE_TEACHER) {
                                            $dataEducationOfMember['number_class_of_teacher'] = $item->count_study;
                                            $dataEducationOfMember['number_hours_of_teacher'] = $item->sum_time;
                                        }
                                    }

                                    ?>
                                    @if(count($teamNames) > 0)
                                        {{implode('; ', $teamNames )}}
                                    @endif
                                </td>
                                <td>{{$employee->role_name}}</td>
                                <td>
                                    {{implode('; ', $leaderNames )}}
                                </td>
                                <td>
                                    {{$dataEducationOfMember['number_class_of_teacher']}}
                                </td>
                                <td>
                                    {{round($dataEducationOfMember['number_hours_of_teacher'] / 60, 2)}}
                                </td>
                                <td>
                                    {{$dataEducationOfMember['number_class_of_member']}}
                                </td>
                                <td>
                                    {{round($dataEducationOfMember['number_hours_of_member'] / 60, 2)}}
                                </td>
                                <td></td>
                                <td>
                                    <a href="javascript:void(0)"
                                       class="detail-employee"
                                       data-url="{{route('education::education.manager.employee.detail', ['id' => $employee->id])}}">
                                        {{ trans('education::view.manager_employee.buttons.detail') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        @if(count($collectionModel) == 0)
                            <tr>
                                <td colspan="11" class="text-align-center">
                                    <h2>{{trans('education::view.manager_employee.labels.no_data')}}</h2>
                                </td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
                <div class="box-body">
                    @include('team::include.pager')
                </div>
            </div>
        </div>
        @if (count($collectionModel) > 0)
            @include('education::manager-employee.modal')
        @endif
    </div>
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript"
            src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/1.3.3/FileSaver.min.js"></script>
    <script type="text/javascript" src="{{ asset('lib/xlsx/jszip.js') }}"></script>
    <script type="text/javascript" src="{{ asset('lib/xlsx/xlsx.js') }}"></script>
    <script src="{{ CoreUrl::asset('team/js/xlsx-func.js') }}"></script>
    <script src="{{ CoreUrl::asset('team/js/script.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
    <script type="text/javascript" src="{{ CoreUrl::asset('education/js/manager-employee.js') }}"></script>
@endsection
