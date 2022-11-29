<?php
use Rikkei\Team\View\Config;
use Rikkei\Core\View\View;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Core\View\Form as CoreForm;
?>

@extends('layouts.default')

@section('title')
    {{ $titlePage }}
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" >
    <link rel="stylesheet" href="{{ asset('education/css/education.css') }}" rel="stylesheet" type="text/css" >
@endsection

@section('content')
    @if (session('status'))
        <div class="alert alert-warning">
            {{ session('status') }}
        </div>
    @endif
    <div id="preview_table"></div>
    <div class="row education-request-list">
        <div class="col-sm-12">
            <div class="box box-info filter-wrapper">
                <div class="box-body filter-mobile-left">
                    <div class="row">
                        <div class="col-sm-7">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="row">
                                        <label class="col-md-4 margin-top-5">{{ trans('resource::view.From date') }}: </label>
                                        <div class="col-md-8">
                                            <input type='text' autocomplete="off" class="form-control from_date filter-grid" id="from_date" name="filter[search][from_date]" placeholder="{{ trans('team::view.Start Date') }}" tabindex=9 value="{{ CoreForm::getFilterData('search', 'from_date') }}" data-focus="false" />
                                            <p class="error from_date-error"></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="row">
                                        <label class="col-md-4 margin-top-5">{{ trans('resource::view.To date') }}: </label>
                                        <div class="col-md-8">
                                            <input type='text' autocomplete="off" class="form-control to_date filter-grid" id="to_date" name="filter[search][to_date]" placeholder="{{ trans('team::view.End Date') }}" tabindex=9 value="{{ CoreForm::getFilterData('search', 'to_date') }}" data-focus="false"/>
                                            <p class="to_date-error"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-5">
                            @include('education::education-request.include.filter')
                        </div>
                    </div>
                </div>
                <style>
                    .dataTable {
                        table-layout: fixed;
                        width: 100%;
                    }

                    /* Column widths are based on these cells */
                    .col-id {
                        width: 30px !important;;
                    }
                    @if($isScopeHrOrCompany)
                    .col-name {
                        width: 100px !important;;
                    }
                    @else
                    .col-name {
                        width: 140px !important;;
                    }
                    @endif
                    .col-long-name {
                        width: 300px !important;
                    }
                    .col-action {
                        width: 70px !important;
                    }
                </style>
                <div class="table-responsive">
                    <table class="table table-striped dataTable table-bordered table-hover table-grid-data" style="min-height: 200px">
                        <thead>
                            <tr>
                                <th class="col-id">{{ trans('core::view.NO.') }}</th>
                                <th class="col-name col-long-name">{{ trans('education::view.Education.Title') }}</th>
                                <th class="col-name">{{ trans('education::view.Education.Created date') }}</th>
                                <th class="col-name">{{ trans('education::view.Education.Object') }}</th>
                                <th class="col-name">{{ trans('education::view.Education.Scope') }}</th>
                                <th class="col-name">{{ trans('education::view.Education.Education Type') }}</th>
                                <th class="col-name">{{ trans('education::view.Education.Status') }}</th>
                                <th class="col-name col-long-name">{{ trans('education::view.Education.Course Name') }}</th>
                                @if($isScopeHrOrCompany)
                                <th class="col-name">{{ trans('education::view.Education.Keywords') }}</th>
                                <th class="col-name">{{ trans('education::view.Education.Division') }}</th>
                                @endif
                                <th class="col-name">{{ trans('education::view.Education.Person assigned') }}</th>
                                <th class="col-action">&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody class="checkbox-list table-check-list">
                            <tr class="filter-input-grid">
                                <td>&nbsp;</td>
                                <td>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <?php $filterTitle = CoreForm::getFilterData('search', 'title');?>
                                            <select class="form-control filter-grid select-search-title" name="filter[search][title]" data-remote-url="{{ URL::route('education::education.request.ajax-title-list') }}" id="filter-title">{!! !empty($filterTitle) ? "<option value='{$filterTitle}' selected>{$filterTitle}</option>" : ''  !!}</select>
                                        </div>
                                    </div>
                                </td>
                                <td>&nbsp;</td>
                                <td>
                                    <div class="row">
                                        <div class="col-md-12 filter-multi-select select-full">
                                            <?php $filterObject = CoreForm::getFilterData('search', 'objects'); ?>
                                            <select name="filter[search][objects][]" class="form-control filter-grid hidden select-multi" multiple="multiple">
                                                @if($objects && count($objects))
                                                    @foreach($objects as $key => $value)
                                                        <option value="{{ $key }}" {{ !empty($filterObject) && in_array($key, array_values($filterObject)) ? 'selected' : '' }}>{{ $value }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="row">
                                        <div class="col-md-12 multi-select-style select-full">
                                            <?php $filterScope = CoreForm::getFilterData('search', 'scope_total');?>
                                            <select class="form-control select-grid filter-grid select-search" id="scope_total" name="filter[search][scope_total]">
                                                <option value="">--------------</option>
                                                @foreach($scopeTotal as $key => $item)
                                                    <option value="{{ $key }}" {{ !empty($filterScope) && $key == $filterScope? 'selected' : '' }}>{{ $item }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="row">
                                        <div class="col-md-12 filter-multi-select select-full">
                                            <?php $filterType = CoreForm::getFilterData('search', 'type');?>
                                            <select class="form-control select-grid filter-grid select-search select2-hidden-accessible" name="filter[search][type]">
                                                    <option value="">&nbsp;</option>
                                                @if($types && count($types))
                                                    @foreach($types as $item)
                                                        <option value="{{ $item['id'] }}" {{ !empty($filterType) && $item['id'] == $filterType ? 'selected' : '' }}>
                                                            {{ $item['name'] }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="row">
                                        <div class="col-md-12 filter-multi-select select-full">
                                            <?php $filterStatus = CoreForm::getFilterData('search', 'status'); ?>
                                            <select class="form-control select-grid filter-grid select-search select2-hidden-accessible" name="filter[search][status]">
                                                <option value="">&nbsp;</option>
                                                @if($status && count($status))
                                                    @foreach($status as $key => $item)
                                                        <option value="{{ $key }}" {{ !empty($filterStatus) && $key == $filterStatus? 'selected' : '' }}>{{ $item }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </td>
                                <td>&nbsp;</td>
                                @if($isScopeHrOrCompany)
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12 filter-multi-select2 select-full">
                                                <?php $filterTag = CoreForm::getFilterData('search', 'tags');?>
                                                <select class="form-control filter-grid hidden select2-tag" name="filter[search][tags][]" data-remote-url="{{ URL::route('education::education.request.ajax-tag-list') }}" multiple>
                                                    @if(isset($tags))
                                                        @foreach($tags as $item)
                                                            <option value="{{ $item['id'] }}" {{ !empty($filterTag) && in_array($item['id'], array_values($filterTag)) ? 'selected' : '' }}>{{ $item['name'] }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12 filter-multi-select division select-full">
                                                <?php $filterDivision = CoreForm::getFilterData('search', 'division');?>
                                                <select name="filter[search][division]" class="form-control filter-grid hidden select-multi" autocomplete="off">
                                                    <option value="">---------</option>
                                                    @foreach($teamsOptionAll as $option)
                                                        <option value="{{ $option['value'] }}" {{ (!empty($filterDivision) && $option['value'] == $filterDivision) ? 'selected' : '' }}>{{ $option['label'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </td>
                                @endif
                                <td>
                                    <div class="row">
                                        <div class="col-md-12 filter-multi-select2 select-full">
                                            <select class="form-control filter-grid hidden select-search-person" id="employee-assigned" name="filter[search][assign_id]" data-remote-url="{{ URL::route('education::education.request.ajax-person-assigned-list') }}"></select>
                                        </div>
                                    </div>
                                </td>
                                <td>&nbsp;</td>
                            </tr>
                            @if($collectionModel)
                                <?php $i = View::getNoStartGrid($collectionModel);?>
                                @foreach($collectionModel as $item)
                                    <tr>
                                        <td>{{ $i }}</td>
                                        <td>{{ $item->title }}</td>
                                        <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}</td>
                                        <td>
                                            <?php
                                                $objectStr = '';
                                                foreach($item['objects'] as $object) {
                                                    $objectStr .= $objects[$object['education_object_id']] .', ';
                                                }
                                            ?>
                                            {{ trim($objectStr, ', ') }}
                                        </td>
                                        <td>
                                            {{ $scopeTotal[$item->scope_total] }}
                                        </td>
                                        <td>
                                            {{ $item['type']['name'] }}
                                        </td>
                                        <td>{{ $status[$item->status] }}</td>
                                        <td>
                                            @if(!empty($item['course']) && in_array($item->status, [1,4]))
                                                <a href="{{ URL::route('education::education-profile.detail', ['id' => $item['course']['id'], 0]) }}">{{ $item['course']['name'] }}</a>
                                            @endif
                                        </td>
                                        @if($isScopeHrOrCompany)
                                            <td>
                                                {{ $item['tags']->implode('name', ', ') }}
                                            </td>
                                            <td>
                                                {{ $item['employee']['teams']->implode('name', ', ') }}
                                            </td>
                                        @endif
                                        @if ($item['assigned'] && count($item['assigned']))
                                        <td class="employee-assigned-{{ $item['assigned']['id'] }}" data-assigned-name="{{ $item['assigned']['name'] }}">{{ $item['assigned']['name'] }}</td>
                                        @else
                                        <td></td>
                                        @endif
                                        <td>
                                            @if($isScopeHrOrCompany)
                                                <a href="{{ URL::route('education::education.request.hr.edit', [$item->id]) }}" class="btn btn-info">{{ trans('education::view.Detail') }}</a>
                                            @else
                                                <a href="{{ URL::route('education::education.request.edit', [$item->id]) }}" class="btn btn-info">{{ trans('education::view.Detail') }}</a>
                                            @endif
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
                        @include('education::education-request.include.pager')
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script type="text/javascript" src="{{ asset('lib/xlsx/xlsx.js') }}"></script>
    <script src="{{ CoreUrl::asset('team/js/script.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
    <script>
        var teamPath = JSON.parse('{!! json_encode($teamPath) !!}');
        var filterAssigned = "{{ CoreForm::getFilterData('search', 'assign_id') }}";
        var filtertitle = "{{ !empty($filterTitle) ? $filterTitle : '' }}";
        var siteConfigGlobalToken = siteConfigGlobal.token;
        var from_date = "{{ (CoreForm::getFilterData('search', 'from_date')) ? CoreForm::getFilterData('search', 'from_date') : '' }}";
        var to_date = "{{ (CoreForm::getFilterData('search', 'to_date')) ? CoreForm::getFilterData('search', 'to_date') : '' }}";
        var messageFromDate = "{{ trans('education::view.message.Please enter from date smaller to date') }}";
        (function ( $ ) {
            // Select2 tag ajax
            $.fn.select2tagAjax = function(options) {
                var defaults = {
                    url: "",
                    pages: 1,
                    delay: 300,
                    placeholder: '',
                    multiple: true,
                    closeOnSelect : false,
                    tags: false,
                };
                var settings = $.extend( {}, defaults, options );
                var tag = this;

                tag.init = function(selector) {
                    var selector = selector;
                    $(selector).select2({
                        multiple: settings.multiple,
                        closeOnSelect : settings.closeOnSelect,
                        allowClear: settings.allowClear,
                        allowHtml: settings.allowHtml,
                        tags: settings.tags,
                        theme : settings.theme,
                        ajax: {
                            url: settings.url,
                            dataType: 'json',
                            delay: settings.delay,
                            data: function (params) {
                                return {
                                    q: params.term, // search term
                                    page: params.page
                                };
                            },
                            processResults: function (data, params) {
                                params.page = params.page || 1;
                                return {
                                    results: data.items,
                                    pagination: {
                                        more: (params.page * 5) < data.total_count
                                    }
                                };
                            },
                            cache: true
                        },
                        escapeMarkup: function (markup) {
                            return markup;
                        },
                        placeholder: settings.placeholder,
                        templateResult: tag.formatRepo,
                        templateSelection: tag.formatRepoSelection
                    });
                    tag.countItemTag(selector);
                }

                // formatRepo in select2
                tag.formatRepo = function(repo) {
                    if (repo.loading) {
                        return repo.text;
                    }
                    return markup  = repo.text;
                }

                // formatRepoSelection in select2
                tag.formatRepoSelection = function(repo) {
                    return repo.text;
                }

                tag.countItemTag = function(selector) {
                    var counter = $(selector).val() ? $(selector).val().length : 0;
                    var target = $(selector).parent()
                    var rendered = target.find('.select2-selection__rendered');
                    target.find('.select2-selection__counter').remove();
                    if (counter > 1) {
                        rendered.hide();
                        rendered.find('li:not(.select2-search--inline)').hide();
                        rendered.after('<div class="select2-selection__counter">' + counter + ' selected</div>');
                        target.find('.select2-selection__counter').show();
                    } else {
                        rendered.show();
                        target.find('.select2-selection__counter').hide()
                    }

                }

                // unselecting select2
                tag.on("select2:unselecting", function (e) {
                    var idSelected = e.params.args.data.id;
                    if ($(e.params.args.originalEvent.currentTarget).hasClass("select2-results__option")) {
                        $(e.params.args.originalEvent.currentTarget).attr('aria-selected', 'false');
                        $(".select2-tag option[value='" + idSelected + "']").remove();
                    }
                    tag.countItemTag($(this));
                })

                tag.on("select2:close", function (e) {
                    // if (tag.val().length > 0) {
                        $('.btn-search-filter').trigger('click');
                    // }
                })

                // change select2
                tag.on("change", function(e) {
                    if (tag.val().length > 0) {
                        tag.countItemTag($(this));
                    }
                })

                var selectors = $(this);
                return $.each(selectors, function(index, selector){
                    tag.init(selector);
                });
            };

            // Select2 search ajax
            $.fn.selectSearchAjax = function(options) {
                var defaults = {
                    url: "",
                    pages: 1,
                    delay: 300,
                    placeholder: "{{ trans('education::view.Search') }}...",
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
                                    employee_branch: "{{ $employee_branch['branch'] }}",
                                    page: params.page
                                };
                            },
                            processResults: function (data, params) {
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
                    var text = $("#employee-assigned option:selected").text();
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

            // Call select2 Ajax
            $('.select2-tag').select2tagAjax({
                url: $('.select2-tag').data('remote-url'),
                theme: "select2-tag-custom",
            })

            $('.select-search-person').selectSearchAjax({
                url: $('.select-search-person').data('remote-url'),
                // minimumInputLength: 1,
                initSelection: function (element, callback) {
                    // get session storage
                    var id = filterAssigned;
                    var text = '';
                    if ( typeof(Storage) !== 'undefined') {
                        if (sessionStorage.getItem('employee-assigned-' + id) !== null) {
                            text = sessionStorage.getItem('employee-assigned-' + id);
                            $('select[name="filter[search][assign_id]"]').append("<option value='" + id + "' selected>" + text + "</option>");
                        }
                    } else {
                        console.log('Trình duyệt của bạn không hỗ trợ!');
                    }
                    var data = [];
                    data.push({id: id, text: text});
                    callback(data);
                }
            });
            $('.select-search-title').selectSearchAjax({
                url: $('.select-search-title').data('remote-url'),
                initSelection: function (element, callback) {
                    var id = filtertitle;
                    var text = filtertitle;
                    var data = [];
                    data.push({id: id, text: text});
                    callback(data);
                }
            });
            $('.select-multi').multiselect({
                nonSelectedText: "--------------",
                allSelectedText: '{{ trans('project::view.All') }}',
                numberDisplayed: 2,
                onDropdownHide: function(event) {
                    RKfuncion.filterGrid.filterRequest(this.$select);
                }
            });
            $('#select-team select').multiselect({
                nonSelectedText: "--------------",
                allSelectedText: '{{ trans('project::view.All') }}',
                numberDisplayed: 1,

            });
            $('.scope .multiselect-selected-text').html(function (i, html) {
                $('.scope .multiselect-selected-text').html(html.replace(/&nbsp;/g, ''))
                $('.scope .multiselect dropdown-toggle').attr('title', html.replace(/&nbsp;/g, ''))
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
                var minDate = from_date ? moment(new Date(moment(from_date, 'DD/MM/YYYY').format('YYYY-MM-DD 00:00:00'))) : false;
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
                        var decrementDay = moment(new Date(moment(curValToDate, 'DD/MM/YYYY').format('YYYY-MM-DD ')));
                        $('.from_date').data('DateTimePicker').maxDate(decrementDay);
                    }


                    // Check from_date !null
                    if (curValFromDate) {
                        incrementDay = moment(new Date(moment(curValFromDate, 'DD/MM/YYYY').format('YYYY-MM-DD 00:00:00')));
                        $('.to_date').data('DateTimePicker').minDate(incrementDay);
                    }

                    $(this).data("DateTimePicker").hide();
                });

                //Chú ý: to_date thay đổi thì from_date luôn thay đổi theo. Cần kiểm tra các trường hợp.
                $('.to_date').on('dp.change', function (e) {
                    $('.to_date').attr('data-focus', 'true');
                    var curValToDate = $('.to_date').val();
                    var decrementDay = '';

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

                    if (curValToDate) {
                        incrementDay = moment(new Date(moment(curValToDate, 'DD/MM/YYYY').format('YYYY-MM-DD')));
                        $('.from_date').data('DateTimePicker').maxDate(incrementDay);
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

            // Export Excel
            $('#export_list').click(function (e) {
                e.preventDefault();
                var form = document.createElement('form');
                form.setAttribute('method', 'post');
                form.setAttribute('action', $(this).data('url'));
                var params = {
                    _token: siteConfigGlobalToken,
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

            // trigger event enter
            $('.filter-multi-select').on('keypress', function(e) {
                if (e.keycode === 13) {
                    $('.btn-search-filter').trigger('click');
                }
            })
        }( jQuery ));
    </script>
    <script src="{{ CoreUrl::asset('education/js/team_scope.js') }}"></script>
@endsection
