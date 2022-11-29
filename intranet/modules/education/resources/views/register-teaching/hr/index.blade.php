@extends('layouts.default')

@section('title')
    {{ $titleHeadPage }}
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link href="{{ asset('education/css/register-teaching.css') }}" rel="stylesheet" type="text/css" >
@endsection
<?php
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Team\View\Config;
use Carbon\Carbon;
use Rikkei\Core\Model\User;
use Rikkei\Education\Model\EducationTeacher;
use Rikkei\Core\View\View as CoreView;

$statusReject = EducationTeacher::STATUS_REJECT;
$statusArrangement = EducationTeacher::STATUS_ARRANGEMENT;
$statusNew = EducationTeacher::STATUS_NEW;
$statusUpdate = EducationTeacher::STATUS_UPDATE;
$scopeCompany = EducationTeacher::SCOPE_COMPANY;
$scopeDivision = EducationTeacher::SCOPE_DIVISION;
$typeNeed = EducationTeacher::REGISTER_TYPE_NEED;
$typeAvailable = EducationTeacher::REGISTER_TYPE_AVAILABLE;
?>
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-info">
                <div class="table-responsive">
                    <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                        <thead>
                        <tr>
                            <th class="col-title" style="width: 100px;">{{ trans('education::view.Title') }}</th>
                            <th class="col-title text-center" style="width: 100px;">{{ trans('education::view.Sender') }}</th>
                            <th class="col-time text-center" style="width: 100px;">{{ trans('education::view.Time') }}</th>
                            <th class="col-time-hours text-center" style="width: 100px;">{{ trans('education::view.Number of hours taught') }}</th>
                            <th class="col-scope text-center" style="width: 100px;">{{ trans('education::view.Scope') }}</th>
{{--                            <th class="col-type text-center" style="width: 100px;">{{ trans('education::view.Registration type') }}</th>--}}
                            <th class="col-course text-center" style="width: 120px;">{{ trans('education::view.Course') }}</th>
                            <th class="col-status text-center" style="width: 77px;">{{ trans('education::view.Status') }}</th>
                            <th class="col-traning text-center" style="width: 160px;">{{ trans('education::view.Training in charge') }}</th>
                            <th class="col-action"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="filter-input-grid">
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <select name="filter[search][scope]" class="form-control select-grid filter-grid select-search">
                                            <option value="">All</option>
                                            @foreach($scopes as $key => $value)
                                                <option value="{{ $key }}" {{ CoreForm::getFilterData('search', 'scope') == $key ? 'selected' : '' }}>{{ trans($value) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php $filterCourseId = CoreForm::getFilterData('search', 'courses_id');?>
{{--                                        <select name="filter[search][courses_id]" class="form-control select-grid filter-grid select-search"  style="max-width: 200px;">--}}
{{--                                            <option value="">All</option>--}}
{{--                                            @foreach($course as $key => $value)--}}
{{--                                                <option value="{{ $key }}" {{ CoreForm::getFilterData('search', 'courses_id') == $key ? 'selected' : '' }}>{{ trans($value) }}</option>--}}
{{--                                            @endforeach--}}
{{--                                        </select>--}}
                                            <select class="form-control select-grid filter-grid select-search select-search-course-id" name="filter[search][courses_id]" data-remote-url="{{ URL::route('education::education.teaching.ajax-course-id') }}" id="filter-course-id" style="max-width: 200px;" >
                                                {!! !empty($filterCourseId) ? "<option value='{$filterCourseId}' selected>{$filterCourseId}</option>" : ''  !!}</select>
                                    </div>
                                </div>
                            </td>
                            <td>&nbsp;</td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <select name="filter[search][tranning_manage_id]" class="form-control select-grid filter-grid select-search">
                                            <option value="">All</option>
                                            @foreach($listUserAssignee as $key => $value)
                                                <option value="{{ $value->id }}" {{ CoreForm::getFilterData('search', 'tranning_manage_id') == $value->id ? 'selected' : '' }}>{{ trans($value->name) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </td>
                            <td>&nbsp;</td>
                        </tr>
                        @if(isset($collectionModel) && count($collectionModel))
                            <?php $i = CoreView::getNoStartGrid($collectionModel); ?>
                            @foreach($collectionModel as $item)
                                <tr>
                                    <td>{{ $item->title }}</td>
                                    <td class="text-center">{{ $item->employee->name }}</td>
                                    <td class="text-center">{{ Carbon::parse($item->created_at)->format('d-m-Y') }}</td>
                                    <td class="text-center">{{ $item->tranning_hour }}</td>
                                    <td class="text-center">
                                        @if($item->scope == $scopeCompany)
                                            {{ trans('education::view.Company') }}
                                        @elseif($item->scope == $scopeDivision)
                                            {{ trans('education::view.Division') }}
                                        @else
                                            {{ trans('education::view.Branch') }}
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if(count($item->educationCourses))
                                            {{ $item->educationCourses[0]->name }}
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if(array_key_exists($item->status, EducationTeacher::getLableStatus()))
                                            {{ EducationTeacher::getLableStatus()[$item->status] }}
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if(isset($item->user) && count($item->user))
                                            {{ $item->user->name }}
                                        @endif
                                    </td>
                                    <td>
                                        <a class="btn btn-primary" href="{{ route('education::education.teaching.teachings.show_detail',['id' => $item->id]) }}">{{ trans('education::view.Detail') }}</a>
                                        @if($item->status != $statusReject && $item->status != $statusArrangement)
                                            <div class="modal fade" id="myModal-{{$i}}" role="dialog">
                                                <div class="modal-dialog">
                                                    <!-- Modal content-->
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                            <h4 class="modal-title">{{ trans('education::view.Confirm rejection') }}</h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <form action="{{URL::route('education::education.teaching.hr.reject', [ $item->id ])}}" method="POST">
                                                                <input type="hidden" name="_method" value="PUT">
                                                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                                <label>Lí do từ chối <em class="required">*</em></label>
                                                                <textarea class="form-control lbl-reject-{{$i}}" rows="3" name="reject">{{$item->reject}}</textarea>
                                                                <label id="reject-error-{{$i}}" class="error" for="title"></label>
                                                                <input type="submit" id="form-reject-{{$i}}" class="btn btn-danger hidden"/>
                                                            </form>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-primary button-reject-submit" data-id="{{$i}}">Submit</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @if(isset($item->user) && count($item->user))
                                                @if(auth()->user()->employee_id != $item->user->id)
                                                    <form action="{{URL::route('education::education.teaching.hr.update', [ $item->id ])}}" method="POST" class="hidden">
                                                        <input type="hidden" name="_method" value="PUT">
                                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                        <input type="submit" id="form-{{$i}}" class="btn btn-danger hidden"/>
                                                    </form>
                                                    <div class="modal fade" id="cofirmModal-{{$i}}" role="dialog">
                                                        <div class="modal-dialog">
                                                            <!-- Modal content-->
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                                    <h4 class="modal-title">{{ trans('education::view.Confirm registration in charge') }}</h4>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button"  class="btn btn-primary button-submit" data-id="{{$i}}">Submit</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <a class="btn btn-primary" data-toggle="modal" data-target="#cofirmModal-{{$i}}" data-id="{{$i}}" href="#">{{ trans('education::view.Curator') }}</a>
                                                @endif
                                                @if($item->status != $statusReject && $item->status != $statusArrangement)
                                                    @if(auth()->user()->employee_id == $item->user->id)
                                                        <a class="btn btn-primary"  href="{{ route('education::education.new', ['teaching_id' => $item->id ]) }}">{{ trans('education::view.Arrange class') }}</a>
                                                        <a type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal-{{$i}}">{{ trans('education::view.Status reject') }}</a>
                                                    @endif
                                                @endif
                                            @else
                                                <form action="{{URL::route('education::education.teaching.hr.update', [ $item->id ])}}" method="POST" class="hidden">
                                                    <input type="hidden" name="_method" value="PUT">
                                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                    <input type="submit" id="form-{{$i}}" class="btn btn-danger hidden"/>
                                                </form>
                                                <div class="modal fade" id="cofirmModal-{{$i}}" role="dialog">
                                                    <div class="modal-dialog">
                                                        <!-- Modal content-->
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                                <h4 class="modal-title">{{ trans('education::view.Confirm registration in charge') }}</h4>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button"  class="btn btn-primary button-submit" data-id="{{$i}}">Submit</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <a class="btn btn-primary" data-toggle="modal" data-target="#cofirmModal-{{$i}}" data-id="{{$i}}" href="#">{{ trans('education::view.Curator') }}</a>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                                <?php $i++; ?>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="9" class="text-center">
                                    <h2 class="no-result-grid">{{ trans('education::view.messages.Data not found') }}</h2>
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
    </div>
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script>
        var $filterCourseId = "{{ !empty($filterCourseId) ? $filterCourseId : '' }}";
        $(document).ready(function () {
            setTimeout(function () {
                $('.flash-message').remove();
            }, 2000);
        });

        $('.button-submit').on('click', function(e) {
            e.preventDefault();
            var index = $(this).data('id');
            $('#form-'+ index ).click();
        });

        $('.button-reject-submit').on('click', function(e) {
            e.preventDefault();
            var index = $(this).data('id');
            var valReject = $('.lbl-reject-' + index).val();
            if (valReject == '') {
                $('#reject-error-' + index).html('{{ trans('education::view.Reject required') }}')
            } else {
                $('#form-reject-'+ index ).click();
            }
        });

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
                var id = $("#filter-course-id").val();
                var text = $("#filter-course-id").text();
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

        $('.select-search-course-id').selectSearchAjax({
            url: $('.select-search-course-id').data('remote-url'),
            // minimumInputLength: 1,
            initSelection: function (element, callback) {
                // get session storage
                var id = $filterCourseId;
                var text = '';
                if ( typeof(Storage) !== 'undefined') {
                    if (sessionStorage.getItem('employee-assigned-' + id) !== null) {
                        text = sessionStorage.getItem('employee-assigned-' + id);
                        $('select[name="filter[search][courses_id]"]').append("<option value='" + id + "' selected>" + text + "</option>");
                    }
                } else {
                    console.log('Trình duyệt của bạn không hỗ trợ!');
                }
                var data = [];
                data.push({id: id, text: text});
                callback(data);
            }
        });
    </script>
@endsection
