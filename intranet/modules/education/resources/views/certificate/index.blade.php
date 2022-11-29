
<?php
use Rikkei\Core\View\CoreUrl;
use Rikkei\Team\View\Permission;
use Rikkei\Team\View\TeamList;
use Carbon\Carbon;
use \Rikkei\Team\Model\Employee;
$teamsOptionAll = TeamList::toOption(null, true, false);
?>
@section('title')
    {{ trans('education::view.Certificate List') }}
@endsection
@extends('layouts.default')
@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" >
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('team/css/style.css') }}" />
@endsection

@section('content')
    <div id="preview_table"></div>
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-info filter-wrapper">
                <div class="box-body filter-mobile-left">
                    <div class="row">
                        <div class="col-sm-9">
                            <div class="team-select-box">
                                <label for="select-team-member">{{ trans('education::view.Choose team') }}</label>
                                <div class="input-box">
                                    @if (is_object($teamIdsAvailable))
                                        <select style="width: 100%" name="filter[search][team_id]"
                                                autocomplete="off"
                                                class="form-control select-search select-grid filter-grid">
                                            <option value="{{$teamIdsAvailable->id}}">
                                                {{ $teamIdsAvailable->name }}
                                            </option>
                                        </select>
                                    @elseif($teamIdsAvailable || ($teamTreeAvailable && count($teamTreeAvailable)))
                                        <select name="filter[search][team_id]"
                                                class="form-control select-search select-grid filter-grid"
                                                autocomplete="off">
                                            @if ($teamIdsAvailable === true)
                                                <option value=""<?php
                                                if (!$teamIdCurrent): ?> selected<?php endif;
                                                ?><?php
                                                if ($teamIdsAvailable !== true): ?> disabled<?php endif;
                                                    ?>>{{ trans('education::view.team_default') }}</option>
                                            @endif
                                            {{-- show team available --}}
                                            @if ($teamIdsAvailable === true || (count($teamsOptionAll) && $teamTreeAvailable))
                                                @foreach($teamsOptionAll as $option)
                                                    @if ($teamIdsAvailable === true || in_array($option['value'], $teamTreeAvailable))
                                                        <option value="{{$option['value']}}"
                                                                {{ ($option['value'] == $teamIdCurrent) ? 'selected' : ''  }}
                                                                <?php if ($teamIdsAvailable === true): elseif (! in_array($option['value'], $teamIdsAvailable)): ?> disabled <?php else: ?>
                                                                {{ $option['option'] }}
                                                        <?php endif; ?> >{{ $option['label'] }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </select>
                                    @endif
                                </div>
                            </div>
                            <div class="team-select-box">
                                <label for="select-team-member">{{ trans('education::view.Choose status') }}</label>
                                <div class="input-box">
                                    <select id="select-status"
                                            class="form-control select-search select-grid filter-grid"
                                            name="filter[search][status]"
                                            autocomplete="off">
                                        @foreach($listStatus as $key => $value)
                                            <option value="{{ $key }}" {{ (isset($dataSearch['status']) && $dataSearch['status'] == $key) ? 'selected' : '' }} >{{trans("education::view.list_level.$value")}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3 text-right member-group-btn">
                            @if (count($collectionModel) > 0)
                                <button type="button" @if(count($collectionModel) == 0) disabled @endif class="btn btn-success export-relationship" id="modal_member_relationship_export"><i class="fa fa-download"></i> {!! trans('education::view.buttons.export') !!}</button>
                            @endif
                                <button class="btn btn-primary btn-reset-filter">
                                    <span>{{ trans('education::view.buttons.reset filter') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
                                </button>
                                <button class="btn btn-primary btn-search-filter">
                                    <span>{{ trans('education::view.buttons.search') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
                                </button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="table-">
                                <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                                    <thead>
                                        <tr>
                                            <th class="sorting" data-order="name">{{ trans('education::view.export.certificate.header.Certificate') }}</th>
                                            <th class="sorting" data-order="name">{{ trans('education::view.export.certificate.header.Level') }}</th>
                                            <th class="sorting" data-order="name">{{ trans('education::view.export.certificate.header.From') }}</th>
                                            <th class="sorting" data-order="name">{{ trans('education::view.export.certificate.header.To') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{--Start header search field--}}
                                        <tr class="filter-input-grid">
                                            <td>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <input type="text" name="filter[search][name]" value="{{ isset($dataSearch['name']) ? $dataSearch['name'] : '' }}" placeholder="{{ trans('education::view.buttons.search') }}..." class="filter-grid form-control" autocomplete="off" />
                                                    </div>
                                                </div>
                                            </td>
                                            <td></td>
                                            <td>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <input type="text" name="filter[search][start_at]" id="startDate" value="{{ isset($dataSearch['start_at']) ? $dataSearch['start_at'] : '' }}" placeholder="yyyy-mm-dd" class="filter-grid form-control" autocomplete="off" />
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <input type="text" name="filter[search][end_at]" id="endDate"  value="{{ isset($dataSearch['end_at']) ? $dataSearch['end_at'] : '' }}" placeholder="yyyy-mm-dd" class="filter-grid form-control" autocomplete="off" />
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        {{--End header search field--}}
                                        {{--Start list data--}}
                                        @if(count($collectionModel) > 0)
                                            @foreach($collectionModel as $key => $value)
                                                <?php
                                                    $obj = $value->employeeCerties;
                                                ?>
                                                @if(count($obj) > 0)
                                                    <tr>
                                                        <td colspan="4" style="background-color: #00c0ef">
                                                            <div>{{$value->name}}
                                                            <?php
                                                                $teams = $value->getTeamMember->pluck('name')->toArray();
                                                            ?>
                                                            @if(count($teams) > 0)
                                                                    -
                                                                    {{implode(', ', $teams)}}
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @foreach($obj as $certificate)
                                                        <tr>
                                                            <td style="border: 1px solid #cccccc">{{$certificate->name}}</td>
                                                            <td style="border: 1px solid #cccccc">{{$certificate->level}}</td>
                                                            <td style="border: 1px solid #cccccc">{{$certificate->start_at}}</td>
                                                            <td style="border: 1px solid #cccccc">{{$certificate->end_at}}</td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="4" class="text-center">{{ trans('education::view.messages.Data not found') }}</td>
                                            </tr>
                                        @endif
                                        {{--End list data--}}
                                    </tbody>
                                </table>
                                <div class="box-body">
                                    @include('team::include.pager')
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            <div>
        <div>
    <div>
    @if (count($collectionModel) > 0)
        @include('education::certificate.modal-export')
    @endif
@endsection
@section('script')
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/1.3.3/FileSaver.min.js"></script>
    <script type="text/javascript" src="{{ asset('lib/xlsx/jszip.js') }}"></script>
    <script type="text/javascript" src="{{ asset('lib/xlsx/xlsx.js') }}"></script>
    <script src="{{ CoreUrl::asset('team/js/xlsx-func.js') }}"></script>
    <script src="{{ CoreUrl::asset('team/js/script.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>

    <script type="text/javascript">
        $(function () {
            $('.list_export_cols').sortable({
                stop: function (event, ui) {
                    $('.list_export_cols li input').each(function (index) {
                        $(this).attr('name', 'columns['+ index +']');
                    });
                },
            });
            $('#form_export button[type="submit"]').click(function () {
                let btn = $(this);
                setTimeout(function () {
                    btn.prop('disabled', false);
                    btn.closest('.modal').modal('hide');
                }, 500);
            });

            selectSearchReload();
            $('.select-multi').multiselect({
                numberDisplayed: 1,
                nonSelectedText: '--------------',
                allSelectedText: '{{ trans('project::view.All') }}',
                onDropdownHide: function(event) {
                    RKfuncion.filterGrid.filterRequest(this.$select);
                }
            });
            // Limit the string length to column roles.
            $('.role-special').shortedContent({showChars: 150});

            $('#startDate').datetimepicker({
                format: 'Y-MM-DD',
            });
            $('#endDate').datetimepicker({
                format: 'Y-MM-DD',
            });
        });
    </script>
@endsection
