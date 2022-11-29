<?php
use Rikkei\Team\View\Config;
use Rikkei\Core\View\View;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Core\View\Form as CoreForm;

$classList = CoreForm::getFilterData('search', 'class_id') ? CoreForm::getFilterData('search', 'class_id') : [];
$classAttend = CoreForm::getFilterData('search', 'class_attend') ? CoreForm::getFilterData('search', 'class_attend') : [];
?>

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/>
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css"
          rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="{{ asset('education/css/education.css') }}" rel="stylesheet" type="text/css">
@endsection

<div class="education-request-body margin-top-10">
    <div class="form-horizontal education-teleport-detail col-md-12">
        <form id="frm_create_education_2" method="post" action="" class="has-valid " autocomplete="off">
            {!! csrf_field() !!}
            <div class="detail">

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group margin-top-10">
                            <label for="name" class="col-md-3 control-label">
                                {{ trans('education::view.Education.Education Cost') }}
                            </label>
                            <div class="col-md-3">
                                <span>
                                    <input id="education_cost" name="education_cost" type="text" class="form-control"
                                           <?php if ($dataCourse[0]->status == '4' || $dataCourse[0]->status == '5') {
                                               echo 'disabled';
                                           } ?> value="{{ $dataCourse[0]->education_cost }}">
                                </span>
                            </div>
                            <label for="name" class="col-md-3 control-label">
                                {{ trans('education::view.Education.Teacher Cost') }}
                            </label>
                            <div class="col-md-3">
                                <span>
                                    <input id="teacher_cost" name="teacher_cost" type="text" class="form-control"
                                           <?php if ($dataCourse[0]->status == '4' || $dataCourse[0]->status == '5') {
                                               echo 'disabled';
                                           } ?> value="{{ $dataCourse[0]->teacher_cost }}">
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group margin-top-10">
                            <label for="request_date" class="col-md-3 control-label">
                            </label>
                            <div class="col-md-3">
                                <div class="form-group col-md-12">
                                    <label>
                                        <input type="checkbox" value="1"
                                               class="ng-valid ng-dirty ng-touched check-send-mail" <?php if ($dataCourse[0]->is_mail == 1) {
                                            echo 'checked';
                                        } ?>>
                                        <span>
                                                {{ trans('education::view.Education.Send mail remind') }}
                                            </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group margin-top-10">
                            <label for="name" class="col-md-3 control-label">
                                {{ trans('education::view.Education.Teacher feedback') }}
                            </label>
                            <div class="col-md-6">
                                <span>
                                    <textarea rows="3" class="form-control col-md-9" <?php if ($flag == 1) {
                                        echo 'disabled';
                                    } ?> id="teacher_feedback"
                                              name="teacher_feedback">{{ $dataCourse[0]->teacher_feedback }}</textarea>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group margin-top-10">
                            <label for="name" class="col-md-3 control-label">
                                {{ trans('education::view.Education.Hr feedback') }}
                            </label>
                            <div class="col-md-6">
                                <span>
                                    <textarea rows="3" class="form-control col-md-9" <?php if ($flag == 1) {
                                        echo 'disabled';
                                    } ?> id="hr_feedback"
                                              name="hr_feedback">{{ $dataCourse[0]->hr_feedback }}</textarea>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group margin-top-10">
                            <label for="name" class="col-md-3 control-label">
                                {{ trans('education::view.Education.Shift success') }}
                                <em class="error">*</em>
                            </label>
                            <div class="col-md-6 filter-multi-select multi-select-style select-full">
                                <select class="form-control filter-grid select-multi class_search_wrapper"
                                        id="is_finish"
                                        multiple <?php if ($dataCourse[0]->status == '1' || $dataCourse[0]->status == '2') {
                                    echo 'disabled';
                                } ?>>
                                    @foreach($dataClass as $key => $value)
                                        @foreach($value->data_shift as $keyShift => $valueShift)
                                            @if($value)
                                                <option class="class_check_finish"
                                                        value="{{ $valueShift->id }}" <?php if ($valueShift->is_finish == 1) {
                                                    echo "selected";
                                                } ?> >{{ trans('education::view.Education.Class') . ' ' . $value->class_name . ' - ' . trans('education::view.Education.Ca2')  . ' ' . $valueShift->name}}</option>
                                            @endif
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group margin-top-10">
                            <label for="request_date" class="col-md-3 control-label">
                            </label>
                            @include('education::manager-courses.includes.filter')
                        </div>
                    </div>
                </div>

            </div>
        </form>
        <br>
    </div>

    <table class="table table-striped dataTable table-bordered table-hover table-grid-data" style="min-height: 200px">
        <thead>
        <tr>
            <th class="col-id">{{ trans('education::view.Education.STT') }}</th>
            <th class="col-name" style="width: 250px;">{{ trans('education::view.Education.Employee Email') }}</th>
            <th class="col-name" style="width: 250px;">{{ trans('education::view.Education.Employee Name') }}</th>
            <th class="col-name" style="width: 150px;">{{ trans('education::view.Education.Employee Code') }}</th>
            <th class="col-name" style="width: 150px;">{{ trans('education::view.Education.Employee Team') }}</th>
            <th class="col-name" style="width: 150px;">{{ trans('education::view.Education.Class Register') }}</th>
            <th class="col-name" style="width: 150px;">{{ trans('education::view.Education.Class Attend') }}</th>
            <th class="col-name" style="width: 150px;">{{ trans('education::view.Education.Feedback Teacher') }}</th>
            <th class="col-name" style="width: 150px;">{{ trans('education::view.Education.Feedback Education') }}</th>
            <th class="col-name" style="width: 250px;">{{ trans('education::view.Education.Feedback') }}</th>
            <th class="col-name" style="width: 116px;">{{ trans('education::view.Education.Action') }}</th>
        </tr>
        </thead>
        <tbody class="checkbox-list table-check-list" data-all="#tbl_check_all" data-export="">
        <tr class="filter-input-grid">
            <td>&nbsp;</td>

            <td>
                <div class="row">
                    <div class="col-md-12">
                        <?php $filterEmail = CoreForm::getFilterData('search', 'email');?>
                        <select class="filter-grid form-control select-search-email" name="filter[search][email]"
                                data-remote-url="{{ URL::route('education::education.searchEmployeeAjaxEmailList') }}"
                                id="filter-email">{!! !empty($filterEmail) ? "<option value='{$filterEmail}' selected>{$filterEmail}</option>" : ''  !!}</select>
                    </div>
                </div>
            </td>

            <td>
                <div class="row">
                    <div class="col-md-12">
                        <?php $filterEmpName = CoreForm::getFilterData('search', 'employee_name');?>
                        <select class="filter-grid form-control select-search-employee_name"
                                name="filter[search][employee_name]"
                                data-remote-url="{{ URL::route('education::education.searchEmployeeAjaxNameList') }}"
                                id="filter-employee_name">{!! !empty($filterEmpName) ? "<option value='{$filterEmpName}' selected>{$filterEmpName}</option>" : ''  !!}</select>
                    </div>
                </div>
            </td>

            <input type="text" class="form-control filter-grid hidden" name="filter[search][course_id]"
                   value="{{ $id }}">

            <td>
                <div class="row">
                    <div class="col-md-12">
                        <?php $filterEmpNameCode = CoreForm::getFilterData('search', 'employee_code');?>
                        <select class="filter-grid form-control select-search-employee_code"
                                name="filter[search][employee_code]"
                                data-remote-url="{{ URL::route('education::education.searchEmployeeAjaxNameCodeList') }}"
                                id="filter-employee_code">{!! !empty($filterEmpNameCode) ? "<option value='{$filterEmpNameCode}' selected>{$filterEmpNameCode}</option>" : ''  !!}</select>
                    </div>
                </div>
            </td>

            <td>
                <div class="row">
                    @include('education::manager-courses.includes.team-patch-pro-search',  ['test' => 'division2'])
                </div>
            </td>

            <td>
                <div class="row">
                    <div class="col-md-12 filter-multi-select multi-select-style select-full">
                        <select class="form-control filter-grid select-multi class_search_wrapper" id="class_search"
                                name="filter[search][class_id][]" multiple>
                            <option value=""
                                    {{ in_array('', $classList) ? 'selected' : '' }}  class="checked_all">{{trans('education::view.Education.All')}}</option>
                            @foreach($dataClass as $key => $value)
                                @if($value)
                                    <option class="class_search_item"
                                            value="{{ $value->id }}" {{ in_array($value->id, $classList) ? 'selected' : '' }}>{{ $value->class_code . ' - ' . trans('education::view.Education.Class')  . ' ' . $value->class_name}}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
            </td>

            <td>
                <div class="row">
                    <div class="col-md-12 filter-multi-select multi-select-style select-full">
                        <select class="form-control filter-grid select-multi class_search_wrapper" id="class_attend"
                                name="filter[search][class_attend][]" multiple>
                            <option value=""
                                    {{ in_array('', $classAttend) ? 'selected' : '' }}  class="checked_all">{{trans('education::view.Education.All')}}</option>
                            @foreach($dataClass as $key => $value)
                                @foreach($value->data_shift as $keyShift => $valueShift)
                                    @if($value)
                                        <option class="class_search_item"
                                                value="{{ $value->id . '-' . $valueShift->id }}" {{ in_array($value->id. '-' . $valueShift->id, $classAttend) ? 'selected' : '' }}>{{ $value->class_code . ' - ' . trans('education::view.Education.Ca2')  . ' ' . $valueShift->name}}</option>
                                    @endif
                                @endforeach
                            @endforeach
                        </select>
                    </div>
                </div>
            </td>

            <td class="feedback_teacher_point"></td>

            <td class="feedback_company_point"></td>

            <td></td>

            <td></td>
        </tr>

        @if($collectionModel)
            <?php $i = View::getNoStartGrid($collectionModel);?>
            @foreach($collectionModel as $item)
                <tr>
                    <td class="stt" data-detail="{{$item->id}}">{{ $i }}</td>
                    <td class="email-new">{{ $item->email }}</td>
                    <td class="name-new">{{ $item->employees_name . ($item->nickname ? ' (' . $item->nickname . ')' : null) }}</td>
                    <td class="code-new">{{ $item->employee_code }}</td>
                    <td class="team-new" data-emp="{{ $item->employee_id }}">{{ $item->team_names }}</td>
                    <td class="class-new" style="white-space: pre-line"
                        data-id="{{ $item->class_group_id }}">{{ str_replace(',', "\n", $item->class_concat) }}</td>
                    <td class="class-attend-new" style="white-space: pre-line"
                        data-id="{{ $item->class_group_attend }}">{{ str_replace(',', "\n", $item->class_attend) }}</td>
                    <td class="teacher_point">{{ str_replace(',', "\n", $item->feedback_teacher_point) }}</td>
                    <td class="company_point">{{ str_replace(',', "\n", $item->feedback_company_point) }}</td>
                    <td class="feedback-new">{{ str_replace(',', "\n", $item->feedback) }}</td>
                    <td>
                        <div class="row">
                            <div class="col-md-12">
                                <button class="btn-edit edit-confirm">
                                    <span><i class="fa fa-edit"></i></span>
                                </button>
                                <button class="btn-delete delete-confirm">
                                    <span><i class="fa fa-trash"></i></span>
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php $i++; ?>
            @endforeach
        @else
            <tr>
                <td colspan="12" class="text-center">
                    <h2 class="no-result-grid">{{trans('core::view.No results found')}}</h2>
                </td>
            </tr>
        @endif

        </tbody>
    </table>


    <div class="box-body">
        @include('team::include.pager')
    </div>

    <div class="row">
        <div class="col-md-12 align-center margin-top-40">
            <button type="button" class="btn btn-success btn-submit-confirm " id="eventSaveList">
                {{ trans('education::view.Education.Save') }}
                <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh save-refresh"></i>
            </button>
            <button type="button" class="btn btn-danger btn-submit-confirm"
                    id="eventClose">{{ trans('education::view.Education.Close') }}</button>
        </div>
    </div>

    <div class="modal fade" id="modal-education" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">×</span></button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body">
                    <p class="text-default">
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default"
                            data-dismiss="modal">{{ trans('education::view.Education.Close') }}</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>

    <div class="modal fade" id="modal-education-error" tabindex="-1" role="dialog" aria-labelledby="myModalLabelError">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">×</span></button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body">
                    <p class="text-default">
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default"
                            data-dismiss="modal">{{ trans('education::view.Education.Close') }}</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>
</div>
@include('education::manager-courses.includes.modal-import')