@extends('layouts.default')
@section('title')
    {{ trans('education::view.education_profile_list') }}
@endsection
@section('content')
    <?php
    use Carbon\Carbon;
    use Rikkei\Core\View\Form as CoreForm;
    use Rikkei\Education\Http\Controllers\EducationCourseController;
    use Rikkei\Education\Http\Services\EducationCourseService;
    use Rikkei\Education\Model\EducationCourse;
    use Rikkei\Education\Model\Status;
    use Rikkei\Team\Model\Team;
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Education\Http\Services\ManagerService;
    use Rikkei\Team\Model\Employee;

    $tableTask = EducationCourse::getTableName();
    $status = Status::$STATUS;
    $status_new = Status::STATUS_NEW;
    $status_register = Status::STATUS_SUBMITTED;
    $status_close = Status::STATUS_CLOSED;
    $role = Status::$ROLE;
    $teamPath = Team::getTeamPathTree();
    $userId = Auth::user()->employee_id;
    ?>

    <div class="row list-css-page">
        <div class="col-xs-12">
            <div class="box box-primary">
                <br>
                @if(Session::has('flash_success'))
                    <div class="alert alert-success">
                        <ul>
                            <li>
                                {{ Session::get('flash_success') }}
                            </li>
                        </ul>
                    </div>
                @endif
                @if(Session::has('flash_error'))
                    <div class="alert alert-danger  not-found">
                        <ul>
                            <li>
                                {{ Session::get('flash_error') }}
                            </li>
                        </ul>
                    </div>
                @endif
                <div class="box-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="col-sm-3">
                                <div class="form-group form-group-select2">
                                    <label style="text-align: center" for="status"
                                           class="col-md-3 control-label margin-top-10">{{ trans('education::view.start_date') }}</label>
                                    <div class="col-sm-9">
                                        <div class="input-group padding-0">
                                                        <span class="input-group-btn">
                                                            <button class="btn btn-default" type="button"><i
                                                                        class="fa fa-calendar"></i></button>
                                                        </span>
                                            <input type='text' autocomplete="off" class="filter-grid form-control date from_date" id="from_date" name="filter[search][from_date]" value="{{ CoreForm::getFilterData("search","from_date") }}"  placeholder="DD/MM/YYYY" tabindex=9/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group form-group-select2">
                                    <label style="text-align: center" for="status"
                                           class="col-md-3 control-label margin-top-10">{{ trans('education::view.end_date') }}</label>
                                    <div class="input-group padding-0">
                                                        <span class="input-group-btn">
                                                            <button class="btn btn-default" type="button"><i
                                                                        class="fa fa-calendar"></i></button>
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
                                    <label style="text-align: center" for="status"
                                           class="col-md-4 control-label margin-top-10">{{ trans('education::view.type_education') }}</label>
                                    <div class="col-md-8">
                                        <select class="form-control select-grid filter-grid select-search"
                                                id="education-scope" name="filter[search][type_id]">
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
                                @include('education::include.filter_profile')
                            </div>
                            <div class="table-responsive" id="myTask-index">
                                <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                                    <thead>
                                    <tr>
                                        <th class="col-id width-10" style="width: 20px;"></th>
                                        <th class="sorting text-center col-email" data-order="email" data-dir="desc">{{trans('education::view.Education_Name')}}
                                        </th>
                                        <th class="sorting text-center col-project_name" data-order="project_name" data-dir="desc">
                                            Vai trò
                                        </th>
                                        <th class="sorting text-center col-type" data-order="type" data-dir="desc">{{trans('education::view.Education_Status')}}</th>
                                        <th class="sorting text-center col-status" data-order="status" data-dir="desc">Ngày đào
                                            tạo
                                        </th>
                                        <th class="text-center">Giảng viên</th>
                                        <th class="" data-order="status" data-dir="desc"></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr class="filter-input-grid">
                                        <td>&nbsp;</td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <input type="text" name="filter[search][name]"
                                                           value="{{ CoreForm::getFilterData("search","name") }}"
                                                           placeholder="Tìm kiếm..." class="filter-grid form-control">
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <select
                                                            class="form-control select-grid filter-grid select-search"
                                                            name="filter[search][role_profile]">
                                                        <option value="">All</option>
                                                        @foreach($roles as $key => $value)
                                                            <option value="{{ $key }}" {{ CoreForm::getFilterData('search', 'role_profile') == $key ? 'selected' : '' }}>{{ trans($value) }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <select
                                                            class="form-control select-grid filter-grid select-search"
                                                            name="filter[search][status]">
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

                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">

                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @if($collectionModel && count($collectionModel))
                                        @foreach($collectionModel as $item)
                                            <?php
                                            $disabled = '';
                                            foreach ($item->classes as $class) {
                                                $disabled = count($class->classShift) ? '' : 'disabled';
                                            }
                                            ?>
                                            <tr role="row">
                                                <td rowspan="1" colspan="1">
                                                    <button id="btnCollapse{{$item->id}}" class="btn btn-link collapsed"
                                                            type="button" data-toggle="collapse"
                                                            data-target=".{{$item->course_code ? $item->course_code : ''}}"
                                                            aria-expanded="false" aria-controls="collapseTwo">
                                                        <i class="fa fa-minus" data-parent-id="{{$item->id}}"></i>
                                                    </button>
                                                </td>
                                                <td rowspan="1" colspan="1">{{$item->name ? $item->name : ''}}</td>
                                                <td rowspan="1" colspan="1"></td>
                                                <td rowspan="1"
                                                    colspan="1">{{$item->status ? trans($status[$item->status]) : 0}}</td>
                                                <td rowspan="1" colspan="1"></td>
                                                <td rowspan="1" colspan="1"></td>
                                                <td rowspan="1" colspan="1" class="text-align-center">
                                                    <a href="{{ route('education::education-profile.detail', ['id' => $item->id , 0]) }}"
                                                       class="add-general-task  btn btn-primary">{{ trans('education::view.detail') }}</a>
{{--                                                    <button type="button" class="btn btn-danger btn-submit-confirm"--}}
{{--                                                            data-url="{{ route('education::education.teaching.courses.register', ['id' => isset($item->id) ? $item->id : 0]) }}"--}}
{{--                                                            {{ $disabled }} id="eventRegister">--}}
{{--                                                        {{ trans('education::view.Education.Register') }}--}}
{{--                                                    </button>--}}
                                                </td>
                                            </tr>
                                            @foreach($item->classes as $data)
                                                <?php
                                                $hr = Employee::getEmpById($data->related_id);
                                                if ($hr) $hr = $hr->name;
                                                ?>
                                                @if($item->classes && count($item->classes))
                                                    @foreach($data->classShift as $it)
                                                        <?php
                                                        $now = Carbon::now();
                                                        $currentDate = Carbon::parse($now)->format('d-m-Y H:i');
                                                        $endTimeDate = Carbon::parse($it->end_time_register)->format('d-m-Y H:i');
                                                        ?>
                                                        @if($it && $it->end_time_register && $currentDate < $endTimeDate)
                                                            <tr role="row" data-parent-id="{{$item->id}}"
                                                                class="collapse in {{$item->course_code ? $item->course_code : ''}}">
                                                                <td rowspan="1" colspan="1">
                                                                </td>
                                                                <td rowspan="1"
                                                                    colspan="1">{{$data && $data->class_name && $it && $it->name ? 'Lớp '.$data->class_name . '- Ca ' . $it->name : ''}}</td>
                                                                <td rowspan="1"
                                                                    colspan="1">{{$it && $it->id ? (ManagerService::getRoleShift($it->id) ? trans($role[(int)ManagerService::getRoleShift($it->id)]): '') : ''}}</td>
                                                                <td rowspan="1" colspan="1"></td>
                                                                <td rowspan="1"
                                                                    colspan="1">{{$it && $it->start_date_time ? date_format(new DateTime($it->start_date_time), 'd-m-Y H:i') : ''}}</td>
                                                                <td rowspan="1"
                                                                    colspan="1">{{ $hr }}</td>
                                                                <td rowspan="1" colspan="1" class="text-align-center">
                                                                    @if ($item && $item->id && ManagerService::checkStatus($item->id) !== $status_register
                                                                    || $item && $item->id && ManagerService::checkStatus($item->id)  == $status_register && ManagerService::getRoleShift($it->id) == 2)

                                                                    @endif
                                                                    @if (($item && $item->id && ManagerService::checkStatus($item->id)  == $status_register && ManagerService::getRoleShift($it->id) != 1 ) &&
                                                                    ($item && $item->id && ManagerService::checkStatus($item->id)  == $status_register && ManagerService::getRoleShift($it->id) != 2))
                                                                        <a href="{{ route('education::profile.register', [$data->id, $it->id]) }}"
                                                                           class="add-general-task  btn btn-primary">{{ trans('education::view.register') }}</a>
                                                                    @endif
                                                                    @if (($item && $item->id && ManagerService::checkStatus($item->id)  == $status_register && ManagerService::getRoleShift($it->id) == 1 ))
                                                                        <a href="{{ route('education::profile.delete', $it->id) }}"
                                                                           class="add-general-task  btn btn-danger">{{ trans('education::view.delete') }}</a>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endif
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
                                <button class="btn-add" type="submit" id="eventClose">
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
        @include('education::manager-courses.includes.modal-education-response-message')
        @endsection
        <!-- Styles -->
            @section('css')
                <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/>
                <link href="{{ asset('resource/css/candidate/list.css') }}" rel="stylesheet" type="text/css">
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css"/>
                <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css"
                      rel="stylesheet" type="text/css">
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
                <link rel="stylesheet" href="{{ asset('education/css/education-manager.css') }}" rel="stylesheet" type="text/css" >
                <link rel="stylesheet" href="{{ asset('team/css/style.css') }}" rel="stylesheet" type="text/css">
            @endsection

        <!-- Script -->
            @section('script')
                <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
                <script src="{{ CoreUrl::asset('resource/js/candidate/list.js') }}"></script>
                <script src="{{ CoreUrl::asset('resource/js/request/list.js') }}"></script>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.min.js"></script>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
                <script type="text/javascript">
                    var teamPath = JSON.parse('{!! json_encode($teamPath) !!}');
                    var $filterCourseCode = "{{ !empty($filterCourseCode) ? $filterCourseCode : '' }}";
                    var from_date = "{{ (CoreForm::getFilterData('search', 'from_date')) ? CoreForm::getFilterData('search', 'from_date') : '' }}";
                    var to_date = "{{ (CoreForm::getFilterData('search', 'to_date')) ? CoreForm::getFilterData('search', 'to_date') : '' }}";
                    jQuery(document).ready(function ($) {
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

                          // assign session storage
                          var id = $("#employee-assigned").val();
                          var text = $("#employee-assigned").text();
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
                            $.each(teamDevOption, function (i, v) {
                                disabledTeamDevOption = v.disabled ? ' disabled' : '';
                                selectedTeamDevOption = v.selected ? ' selected' : '';
                                htmlTeamDevOption += '<option value="' + v.id + '"' + disabledTeamDevOption + ''
                                    + selectedTeamDevOption + '>' + v.label + '</option>';
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

                        $(".collapse").on('show.bs.collapse', function (event) {
                            var parentId = $(event.target).data('parent-id');
                            $("#btnCollapse" + parentId).find(".fa").removeClass("fa-plus").addClass("fa-minus");
                        }).on('hide.bs.collapse', function () {
                            var parentId = $(event.target).data('parent-id');
                            $("#btnCollapse" + parentId).find(".fa").removeClass("fa-minus").addClass("fa-plus");
                        });
                    });

                    $('.select-multi').multiselect({
                        numberDisplayed: 1,
                        nonSelectedText: '-------------------',
                        allSelectedText: '{{ trans('project::view.All') }}',
                        onDropdownHide: function (event) {
                            RKfuncion.filterGrid.filterRequest(this.$select);
                        }
                    });
                    $(document).on("click", "#eventClose", function (e) {
                        e.preventDefault();
                        window.location.href = '{{  URL::to('/') }}';
                    });
                </script>
                <script src="{{ CoreUrl::asset('education/js/team_scope.js') }}"></script>

                <script>
                    $(document).on("click", "#eventRegister", function (e) {
                        var url = $(this).data('url'),
                            popup = popupWinEdit(url, $(window).width() * 0.4, $(window).height() * 0.4);
                        $(window).on('beforeunload', function () {
                            popup.close();
                        });
                    });

                    function popupWinEdit(url, width, height) {
                        var leftPosition = (window.screen.width / 2) - ((width / 2) + 10);
                        var topPosition = (window.screen.height / 2) - ((height / 2) + 50);
                        //Open the window.
                        return window.open(url, "edit_question",
                            "status=no,height=" + height + ",width=" + width + ",resizable=yes,left="
                            + leftPosition + ",top=" + topPosition + ",screenX=" + leftPosition + ",screenY="
                            + topPosition + ",toolbar=no,menubar=no,scrollbars=1,location=no,directories=no");
                    }
                </script>
@endsection
