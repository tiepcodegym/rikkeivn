@extends('layouts.default')
@section('title')
    {{ trans('education::view.education') }}
@endsection
@section('content')
    <?php
    use Rikkei\Education\Model\Status;
    use Rikkei\Education\Model\EducationCourse;
    use Rikkei\Education\Http\Services\ManagerService;
    use Rikkei\Core\View\Form as CoreForm;
    use Illuminate\Support\Facades\URL;

    $tableTask = EducationCourse::getTableName();
    $status = Status::$STATUS;
    use Rikkei\Team\Model\Team;
    $teamPath = Team::getTeamPathTree();
    ?>

    <div class="row list-css-page">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="col-sm-3">
                                <div class="form-group form-group-select2">
                                    <label style="text-align: center" for="status" class="col-md-3 control-label margin-top-10">{{ trans('education::view.start_date') }}</label>
                                    <div class="col-sm-9">
                                        <div class="input-group padding-0">
                                                        <span class="input-group-btn">
                                                            <button class="btn btn-default" type="button"><i class="fa fa-calendar"></i></button>
                                                        </span>
                                            <input type='text' autocomplete="off" class="filter-grid form-control date from_date" id="from_date" name="filter[search][from_date]" value="{{ CoreForm::getFilterData("search","from_date") }}"  placeholder="DD/MM/YYYY" tabindex=9/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group form-group-select2">
                                    <label style="text-align: center" for="status" class="col-md-3 control-label margin-top-10">{{ trans('education::view.end_date') }}</label>
                                    <div class="input-group padding-0">
                                                        <span class="input-group-btn">
                                                            <button class="btn btn-default" type="button"><i class="fa fa-calendar"></i></button>
                                                        </span>
                                        <input type='text' autocomplete="off" class="filter-grid form-control date to_date" id="to_date" name="filter[search][to_date]" value="{{ CoreForm::getFilterData("search","to_date") }}"  placeholder="YYYY/MM/DD" tabindex=9/>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                @include('education::include.team-patch')
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group form-group-select2">
                                    <label style="text-align: center" for="status" class="col-md-4 control-label margin-top-10">{{ trans('education::view.type_education') }}</label>
                                    <div class="col-md-8">

                                        <select class="form-control select-grid filter-grid select-search" id="education-scope" name="filter[search][type_id]">
                                            <option value="">All</option>
                                            @if($types && count($types))
                                                @foreach($types as $item)
                                                    <option value="{{ $item['id'] }}" {{ CoreForm::getFilterData('search', 'type_id') == $item['id'] ? 'selected' : '' }}>{{ $item['name'] }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <br>

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="box-body">
                                @include('education::include.filter')
                            </div>
                            <div class="table-responsive" id="myTask-index">
                                <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                                    <thead>
                                    <tr>
                                        <th class="col-id width-10" style="width: 20px;"></th>
                                        <th class="sorting  col-title" style="width: 250px;" data-order="title" data-dir="desc">{{trans('education::view.Education_Code')}}</th>
                                        <th class="sorting  col-email" data-order="email" data-dir="desc">{{trans('education::view.Education_Name')}}</th>
                                        <th class="sorting  col-project_name" data-order="project_name" data-dir="desc">{{trans('education::view.Education_Student')}}</th>
                                        <th class="sorting  col-group" data-order="team_name" data-dir="desc">{{trans('education::view.Education_Amount')}}</th>
                                        <th class="sorting  col-type" data-order="type" data-dir="desc">{{trans('education::view.Education_Status')}}</th>
                                        <th class="sorting  col-status" data-order="status" data-dir="desc">{{trans('education::view.Education_From Date')}}</th>
                                        <th class="sorting  col-priority" data-order="priority" data-dir="desc">{{trans('education::view.Education_Location')}}</th>
                                        <th class="sorting  col-created_at" data-order="created_at" data-dir="desc" style="width: 100px;">{{trans('education::view.Education_HR_assign')}}</th>
                                        <th class="sorting  col-duedate" data-order="duedate" data-dir="desc" style="width: 100px;">{{trans('education::view.Education_Cost')}}</th>
                                        <th class="" data-order="status" data-dir="desc"></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr class="filter-input-grid">
                                        <td>&nbsp;</td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <?php $filterCourseCode = CoreForm::getFilterData('search', 'course_code');?>
                                                    <select class="form-control filter-grid select-search-course_code" name="filter[search][course_code]" data-remote-url="{{ URL::route('education::education.ajax-course-code') }}" id="filter-course_code">{!! !empty($filterCourseCode) ? "<option value='{$filterCourseCode}' selected>{$filterCourseCode}</option>" : ''  !!}</select>
                                                    {{--<input type="text" name="filter[search][course_code]" value="{{ CoreForm::getFilterData("search","course_code") }}" placeholder="Tìm kiếm..." class="filter-grid form-control">--}}
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <input type="text" name="filter[search][name]" value="{{ CoreForm::getFilterData("search","name") }}" placeholder="Tìm kiếm..." class="filter-grid form-control">
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <?php $filterGiangVien = CoreForm::getFilterData('search', 'giangvien');?>
                                                    <select class="form-control filter-grid select-search-giang-vien" name="filter[search][giangvien]" data-remote-url="{{ URL::route('education::education.ajax-giang-vien') }}" id="filter-giang-vien">{!! !empty($filterGiangVien) ? "<option value='{$filterGiangVien}' selected>{$filterGiangVien}</option>" : ''  !!}</select>
                                                    {{--<input type="text" name="filter[search][giangvien]" value="{{ CoreForm::getFilterData("search","giangvien") }}" placeholder="Tìm kiếm..." class="filter-grid form-control">--}}
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">

                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <select style="width: 90px" class="form-control select-grid filter-grid select-search" name="filter[search][status]">
                                                        <option value="">All</option>
                                                        @foreach($taskStatus as $key => $value)
                                                            <option value="{{ $key }}" {{ CoreForm::getFilterData('search', 'status') == $key ? 'selected' : '' }}>{{ trans($value) }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">

                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <select style="width: 90px" class="form-control select-grid filter-grid select-search" name="filter[search][location]">
                                                        <option value="">All</option>
                                                        @foreach($location as $key => $value)
                                                            @if($value)
                                                                <option value="{{ $value->location_name }}" {{ CoreForm::getFilterData('search', 'location') == $value->location_name ? 'selected' : '' }}>{{ $value->location_name }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    {{--<select style="width: 90px" class="form-control select-grid filter-grid select-search" name="filter[search][hr_id]">--}}
                                                    {{--<option value="">All</option>--}}
                                                    {{--@foreach($hrAssign as $key => $value)--}}
                                                    {{--@if($value)--}}
                                                    {{--<option value="{{ $value->hr_id }}" {{ CoreForm::getFilterData('search', 'hr_id') == $value->hr_id ? 'selected' : '' }}>{{ $value->name }}</option>--}}
                                                    {{--@endif--}}
                                                    {{--@endforeach--}}
                                                    {{--</select>--}}
                                                    <?php $filterHrId = CoreForm::getFilterData('search', 'hr_id');?>
                                                    <select class="form-control filter-grid select-search-hr-id" name="filter[search][hr_id]" data-remote-url="{{ URL::route('education::education.ajax-hr_id') }}" id="filter-hr-id"></select>
                                                    {{--<input type="text" name="filter[search][course_code]" value="{{ CoreForm::getFilterData("search","course_code") }}" placeholder="Tìm kiếm..." class="filter-grid form-control">--}}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="total-cost"></td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">

                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @if($collectionModel && count($collectionModel))
                                        @foreach($collectionModel as $item)
                                            <tr role="row">

                                                <td rowspan="1" colspan="1" >
                                                    <button id="btnCollapse{{$item->id}}"  class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target=".{{$item->course_code ? $item->course_code : ''}}" aria-expanded="false" aria-controls="collapseTwo">

                                                        <i class="fa fa-plus" data-parent-id="{{$item->id}}"></i>
                                                    </button>
                                                </td>
                                                <td rowspan="1" colspan="1">{{$item && $item->course_code ? $item->course_code : ''}}</td>
                                                <td rowspan="1" colspan="1">{{$item->name ? $item->name : ''}}</td>
                                                <td rowspan="1" colspan="1"></td>
                                                <td rowspan="1" colspan="1"></td>
                                                <td rowspan="1" colspan="1">{{$item && $item->status ? trans($status[$item->status]) : 0}}</td>
                                                <td rowspan="1" colspan="1"></td>
                                                <td rowspan="1" colspan="1"></td>
                                                <td rowspan="1" colspan="1">{{$item && $item->employee && $item->employee->name ? $item->employee->name : ''}}</td>
                                                <td class="cost" rowspan="1" colspan="1" data-toggle="tooltip" title="Chi phí giảng viên: {{$item->teacher_cost? $item->teacher_cost : 0}} , Chi phí tổ chức: {{$item->education_cost ? $item->education_cost : 0}}" data-container="body"> {{$item && $item->teacher_cost &&  $item->education_cost ? (int)($item->teacher_cost) + (int)($item->education_cost)  : 0}} </td>
                                                <td rowspan="1" colspan="1" class="text-align-center">
                                                    <a href="{{ route('education::education.detail', ['id' => $item->id , 0]) }}" class="add-general-task  btn btn-primary">{{ trans('education::view.detail') }}</a>
                                                </td>
                                            </tr>

                                            @foreach($item->classes as $data)
                                                @if($item->classes && count($item->classes))
                                                    @foreach($data->classShift as $it)
                                                        <tr role="row" data-parent-id="{{$item->id}}" class="collapse {{$item->course_code ? $item->course_code : ''}}" >
                                                            <td rowspan="1" colspan="1" >
                                                            </td>
                                                            <td rowspan="1" colspan="1">{{$it && $it->class_code  ? $it->class_code : ''}}</td>
                                                            <td rowspan="1" colspan="1">{{$data && $data->class_name && $it && $it->name ? 'Lớp ' . $data->class_name . '- Ca ' . $it->name : ''}}</td>
                                                            <td rowspan="1" colspan="1">{{$data && $data->related_name && $data->related_id ?  ManagerService::getNameTeacher($data->related_name, $data->related_id ) : ''}}</td>
                                                            <td rowspan="1" colspan="1">{{$it && $it->id ? ManagerService::countEmployee($it->id) : ''}}</td>
                                                            <td rowspan="1" colspan="1"></td>
                                                            <td rowspan="1" colspan="1">{{$it && $it->start_date_time ? date_format(new DateTime($it->start_date_time), 'd-m-Y') : ''}}</td>
                                                            <td rowspan="1" colspan="1">{{$it && $it->location_name ? $it->location_name : ''}}</td>
                                                            <td rowspan="1" colspan="1"></td>
                                                            <td rowspan="1" colspan="1"></td>
                                                            <td rowspan="1" colspan="1" class="text-align-center">

                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            @endforeach
                                        @endforeach
                                    @endif

                                    </tbody>
                                </table>
                            </div>
                            <div class="box-body">
                                @include('team::include.pager')
                            </div>
                            <div class="col-md-12 align-center">
                                <button id="export_education" class="btn-add" type="submit" data-url="{{ route('education::education.export') }}">
                                    {{ trans('education::view.Export') }}
                                    <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i>
                                </button>
                                <button id="eventClose" class="btn-add" type="submit">
                                    {{ trans('education::view.Education.Close') }}
                                    <a href="#">
                                        <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i>
                                    </a>

                                </button>
                            </div>
                        </div>
                        <!-- /.box-body -->
                    </div>
                    <!-- /.box -->
                </div>
                <!-- /.col -->
            </div>

        @endsection
        <!-- Styles -->
            @section('css')
                <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
                <link href="{{ asset('resource/css/candidate/list.css') }}" rel="stylesheet" type="text/css" >
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
                <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" >
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
                <link rel="stylesheet" href="{{ asset('education/css/education-manager.css') }}" rel="stylesheet" type="text/css" >
                <link rel="stylesheet" href="{{ asset('team/css/style.css') }}" rel="stylesheet" type="text/css" >
            @endsection

        <!-- Script -->
            @section('script')
                <?php
                use Rikkei\Core\View\CoreUrl;
                ?>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
                <script src="{{ CoreUrl::asset('resource/js/candidate/list.js') }}"></script>
                <script src="{{ CoreUrl::asset('resource/js/request/list.js') }}"></script>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.min.js"></script>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
                <script type="text/javascript">
                  var $filterCourseCode = "{{ !empty($filterCourseCode) ? $filterCourseCode : '' }}";
                  var $filterGiangVien = "{{ !empty($filterGiangVien) ? $filterGiangVien : '' }}";
                  var $filterHrId = "{{ !empty($filterHrId) ? $filterHrId : '' }}";
                  var from_date = "{{ (CoreForm::getFilterData('search', 'from_date')) ? CoreForm::getFilterData('search', 'from_date') : '' }}";
                  var to_date = "{{ (CoreForm::getFilterData('search', 'to_date')) ? CoreForm::getFilterData('search', 'to_date') : '' }}";
                  var filterAssigned = "{{ CoreForm::getFilterData('search', 'hr_id') }}";
                  var teamPath = JSON.parse('{!! json_encode($teamPath) !!}');
                  jQuery(document).ready(function ($) {
                    // Select2 search ajax
                    $.fn.selectSearchAjax = function(options) {
                      var defaults = {
                        url: "",
                        pages: 1,
                        delay: 300,
                        placeholder: "Search ...",
                        multiple: false,
                        allowClear: true,
                        allowHtml: true,
                        tags: false,
                        minimumInputLength: 2,
                        maximumSelectionLength: 1,
                        initSelection : function (element, callback) {
                          var id = '';
                          var text = '';
                          var data = [];
                          data.push({id: id, text: text});
                          callback(data);
                        },
                      };
                      var settings = $.extend( {}, defaults, options );
                      var search = this;

                      search.init = function(selector) {
                        $(selector).select2({
                          multiple: settings.multiple,
                          closeOnSelect : settings.closeOnSelect,
                          allowClear: settings.allowClear,
                          allowHtml: settings.allowHtml,
                          tags: settings.tags,
                          minimumInputLength: settings.minimumInputLength,
                          maximumSelectionLength: settings.minimumInputLength,
                          ajax: {
                            url: settings.url,
                            dataType: 'json',
                            delay: settings.delay,
                            data: function (params) {
                              return {
                                q: params.term,
                                  {{--employee_branch: "{{ $employee_branch['branch'] }}",--}}
                                  page: params.page
                              };
                            },
                            processResults: function (data, params) {
                              console.log(data.items);
                              params.page = params.page || 1;
                              return {
                                results: data.items,
                                pagination: {
                                  more: (params.page * 10) < data.total_count
                                }
                              };
                            },
                            cache: true
                          },
                          escapeMarkup: function (markup) {
                            return markup;
                          },
                          placeholder: settings.placeholder,
                          templateResult: search.formatRepo,
                          templateSelection: search.formatRepoSelection,
                          initSelection : settings.initSelection,
                        });
                      }

                      // temple
                      search.formatRepo = function(repo) {
                        if (repo.loading) {
                          return repo.text;
                        }

                        return markup  = repo.text;
                      }

                      // temple
                      search.formatRepoSelection = function(repo) {
                        return repo.text;
                      }

                      // Event select
                      search.on("select2:select", function (e) {
                        // remove all sesssion storage
                        sessionStorage.clear();
                        var a = sessionStorage.getItem('employee-assigned-')
                        // assign session storage
                        var id = $("#filter-hr-id").val();
                        var text = $("#filter-hr-id option:selected").text();
                        if (text != null) {
                          sessionStorage.setItem('employee-assigned-' + id, text);
                        }

                        // Trigger on close select2
                        $('.btn-search-filter').trigger('click');
                      })

                      // init
                      var selectors = $(this);
                      return $.each(selectors, function(index, selector){
                        search.init(selector);
                      });
                    };

                    var RKVarPassGlobal = {
                      textSave: '{{ trans('project::view.Save') }}',
                      textClose: '{{ trans('project::view.Close') }}',
                      multiSelectTextNone: '{{ trans('project::view.Choose items') }}',
                      multiSelectTextAll: '{{ trans('project::view.All') }}',
                      multiSelectTextSelected: '{{ trans('project::view.items selected') }}',
                      multiSelectTextSelectedShort: '{{ trans('project::view.items') }}',
                      teamPath: JSON.parse('{!! json_encode($teamPath) !!}'),
                      teamSelected: JSON.parse('{!! json_encode($teamSelected) !!}'),
                    }
                    // render team dev option
                    if (typeof RKVarPassGlobal !== 'undefined' && $('select.team-dev-tree').length) {
                      var teamDevOption = RKfuncion.teamTree.init(RKVarPassGlobal.teamPath, RKVarPassGlobal.teamSelected);
                      var htmlTeamDevOption, disabledTeamDevOption, selectedTeamDevOption;
                      $.each(teamDevOption, function(i,v) {
                        disabledTeamDevOption = v.disabled ? ' disabled' : '';
                        selectedTeamDevOption = v.selected ? ' selected' : '';
                        htmlTeamDevOption += '<option value="'+v.id+'"'+disabledTeamDevOption+''
                          +selectedTeamDevOption+'>' + v.label+'</option>';
                      });
                      $('select.team-dev-tree').append(htmlTeamDevOption);
                    }
                    // end render team dev option

                    $('#team_id').multiselect({
                      includeSelectAllOption: false,
                      numberDisplayed: 2,
                      nonSelectedText: "{{ trans('project::view.Choose team') }}",
                      nSelectedText: "{{ trans('project::view.Team') }}",
                      enableFiltering: true,
                      onChange: function (option, checked, event) {
                        $('#team_id-error').remove();
                        // Get selected options.
                        var selectedOptions = $('#team_id option:selected');
                        input = '';
                        selectedOptions.each(function (index, el) {
                          if (index == 0) {
                            input += $(this).text().trim();
                          } else {
                            input += ', ' + $(this).text().trim();
                          }
                        });
                        setTimeout(function () {
                          if (input.length) {
                            $('#select-team .btn-group .multiselect').attr('title', input);
                          }
                        }, 10);
                        if (selectedOptions.length <= 4) {
                          setTimeout(function () {
                            if (input.length) {
                              $('#select-team .btn-group .multiselect-selected-text').html(input);
                            }
                          }, 10);
                        }
                      }
                    });

                    // Block Datetimepick
                    // init datetimepicker
                    $('.from_date').datetimepicker({
                      format: 'DD/MM/YYYY',
                      useCurrent: false
                    })
                    $('.to_date').datetimepicker({
                      format: 'DD/MM/YYYY',
                      useCurrent: false
                    });

                    // Check to_date and from_date has value before
                    var minDate = from_date ? moment(new Date(moment(from_date, 'DD/MM/YYYY').format('YYYY-MM-DD'))) : false;
                    var maxDate = to_date ? moment(new Date(moment(to_date, 'DD/MM/YYYY').format('YYYY-MM-DD'))) : false;

                    // Check from_date null, to_date !null
                    if (minDate && maxDate) {
                      $('.from_date').data('DateTimePicker').maxDate(maxDate)
                    }

                    // Check from_date !null
                    if (minDate) {
                      $('.to_date').data('DateTimePicker').minDate(minDate)
                    }

                    // Check from_date when focus
                    $('.from_date').on('dp.change', function (e) {
                      // Compare from_date - to_date
                      datetimeValid();

                      $('.from_date').attr('data-focus', 'true');
                      var curValFromDate = $('.from_date').val();
                      var curValToDate = $('.to_date').val();
                      var incrementDay = '';

                      // Check to_date focus before
                      if($('.to_date').attr('data-focus') == 'true') {
                        incrementDay = moment(new Date(e.date));
                        // incrementDay.add(1, 'days');
                        $('.to_date').data('DateTimePicker').minDate(incrementDay);
                      }

                      // Check to_date !null
                      if (curValToDate) {
                        var decrementDay = moment(new Date(moment(curValToDate, 'DD/MM/YYYY').format('YYYY-MM-DD')));
                        $('.from_date').data('DateTimePicker').maxDate(decrementDay);
                      }


                      // Check from_date !null
                      if (curValFromDate) {
                        incrementDay = moment(new Date(moment(curValFromDate, 'DD/MM/YYYY').format('YYYY-MM-DD')));
                        $('.to_date').data('DateTimePicker').minDate(incrementDay);
                      }

                      $(this).data("DateTimePicker").hide();
                    });

                    //Chú ý: to_date thay đổi thì from_date luôn thay đổi theo. Cần kiểm tra các trường hợp.
                    $('.to_date').on('dp.change', function (e) {
                      $('.to_date').attr('data-focus', 'true');
                      var decrementDay = '';
                      var curValToDate = $('.to_date').val();

                      // Check from_date focus before
                      if($('.from_date').attr('data-focus') == 'true') {
                        decrementDay = moment(new Date(e.date));
                        // decrementDay.subtract(1, 'days');
                        $('.from_date').data('DateTimePicker').maxDate(decrementDay);
                      }

                      // Check to_date !null before
                      if (maxDate) {
                        decrementDay = moment(new Date(moment(curValToDate, 'DD/MM/YYYY').format('YYYY-MM-DD')));
                        $('.from_date').data('DateTimePicker').maxDate(decrementDay);
                      }

                      // Compare from_date - to_date
                      // datetimeValid();

                      // $(this).data("DateTimePicker").hide();
                    });

                    // valid datetime
                    function datetimeValid() {
                      var fromDate = moment(new Date(moment($('.from_date').val(), 'DD/MM/YYYY').format('YYYY-MM-DD')));
                      var toDate = moment(new Date(moment($('.to_date').val(), 'DD/MM/YYYY').format('YYYY-MM-DD')));
                      if (fromDate > toDate) {
                        $('.from_date').val($('.to_date').val());
                        $('.from_date-error').html(messageFromDate);
                      } else {
                        $('.from_date-error').html('')
                      }
                    }
                    // End Block Datetimepick

                    $('.elect5-hidden').select2();

                    $('.select-search-hr-id').selectSearchAjax({
                      url: $('.select-search-hr-id').data('remote-url'),
                      // minimumInputLength: 1,
                      initSelection: function (element, callback) {
                        // get session storage
                        var id = filterAssigned;
                        var text = '';
                        if ( typeof(Storage) !== 'undefined') {
                          if (sessionStorage.getItem('employee-assigned-' + id) !== null) {
                            text = sessionStorage.getItem('employee-assigned-' + id);
                            $('select[name="filter[search][hr_id]"]').append("<option value='" + id + "' selected>" + text + "</option>");
                          }
                        } else {
                          console.log('Trình duyệt của bạn không hỗ trợ!');
                        }
                        var data = [];
                        data.push({id: id, text: text});
                        callback(data);
                      }
                    });

                    // filter search
                    $('.select-search-course_code').selectSearchAjax({
                      url: $('.select-search-course_code').data('remote-url'),
                      initSelection: function (element, callback) {
                        var id = $filterCourseCode;
                        var text = $filterCourseCode;
                        var data = [];
                        data.push({id: id, text: text});
                        callback(data);
                      }
                    });
                    // filter course name

                    $('.select-search-giang-vien').selectSearchAjax({
                      url: $('.select-search-giang-vien').data('remote-url'),
                      initSelection: function (element, callback) {
                        var id = $filterGiangVien;
                        var text = $filterGiangVien;
                        var data = [];
                        data.push({id: id, text: text});
                        callback(data);
                      }
                    });

                    $(".collapse").on('show.bs.collapse', function(event) {
                      var parentId = $(event.target).data('parent-id');
                      $("#btnCollapse" + parentId).find(".fa").removeClass("fa-plus").addClass("fa-minus");
                    }).on('hide.bs.collapse', function() {
                      var parentId = $(event.target).data('parent-id');
                      $("#btnCollapse" + parentId).find(".fa").removeClass("fa-minus").addClass("fa-plus");
                    });
                  });

                  // Export Excel
                  $('#export_education').click(function (e) {
                    e.preventDefault();
                    var form = document.createElement('form');
                    form.setAttribute('method', 'post');
                    form.setAttribute('action', $(this).data('url'));
                    var params = {
                      _token: siteConfigGlobal.token,
                    };

                    for (var key in params) {
                      var hiddenField = document.createElement('input');
                      hiddenField.setAttribute('type', 'hidden');
                      hiddenField.setAttribute('name', key);
                      hiddenField.setAttribute('value', params[key]);
                      form.appendChild(hiddenField);
                    }

                    document.body.appendChild(form);
                    form.submit();
                    form.remove();
                  });

                  $('.select-multi').multiselect({
                    numberDisplayed: 1,
                    nonSelectedText: '-------------------',
                    allSelectedText: '{{ trans('project::view.All') }}',
                    onDropdownHide: function(event) {
                      RKfuncion.filterGrid.filterRequest(this.$select);
                    }
                  });
                  //  total  cost
                  var arrCost = [];
                  var total=0;
                  var cost = document.querySelectorAll(".cost");
                  for (var i = 0; i <  cost.length; i++) {
                    arrCost.push(cost[i].textContent);
                  }
                  for(var i = 0; i < arrCost.length; i++) {
                    total += parseInt(arrCost[i]);
                  }
                  $('.total-cost').text(total);

                  $(document).on("click", "#eventClose", function(e) {
                    e.preventDefault();
                    window.location.href = '{{  URL::to('/') }}';
                  });
                </script>
                <script src="{{ CoreUrl::asset('education/js/team_scope.js') }}"></script>
@endsection