<?php
use Rikkei\Team\View\TeamList;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\EmployeeWork;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\EmplCvAttrValue;
use Rikkei\Core\View\Form;

$teamsOptionAll = TeamList::toOption(null, false, false);
$teamPath = Team::getTeamPath();

//get table name
$teamTableAs = 'team_table';
$employeeTableAs = 'employees';
$employeeTeamTableAs = 'team_member_table';
$roleTabelAs = 'role_table';
$roleSpecialTabelAs = 'role_special_table';
$employeeWorkTbl = EmployeeWork::getTableName();
$permissExport = Permission::getInstance()->isAllow('team::team.member.export_member');
$tableEmplCvAttrValue = EmplCvAttrValue::getTableName();
?>

@extends('layouts.default')

@section('title')
{{ trans('team::view.Employee List') }} 
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" >
<link rel="stylesheet" href="{{ CoreUrl::asset('team/css/style.css') }}" />
<style>
    .multi-select-style .multiselect-container {
        width: 350px;
    }
    .multi-select-style .multiselect-container .multiselect-item .input-group .multiselect-search{
        width: 100%;
    }
    .multi-select-style .multiselect-container .multiselect-item .input-group .input-group-btn{
        display: none;
    }
</style>
@endsection

@section('content')
    @if (session('status'))
        <div class="alert alert-warning">
            {{ session('status') }}
        </div>
    @endif
    <div id="preview_table"></div>
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-info filter-wrapper"
                 data-url="{{ $urlFilter }}">
                <div class="box-body filter-mobile-left">
                    <div class="row" style="margin: 0px; display: flex;">
                        <div class="list-team-select-box" style="display: flex;">
                            {{-- show team available --}}
                            @if (is_object($teamIdsAvailable))
                                <p>
                                    <b>Team:</b>
                                    <span>{{ trim($teamIdsAvailable->name) }}</span>
                                </p>
                            @elseif ($teamIdsAvailable || ($teamTreeAvailable && count($teamTreeAvailable)))
                                <label for="select-team-member" style="float: left; margin-right: 5px">{{ trans('team::view.Choose team') }}</label>
                                <div class="input-box filter-multi-select multi-select-style btn-select-team">
                                    <select name="filter[except][team_ids][]" id="select-team-member" multiple
                                            class="form-control filter-grid multi-select-bst select-multi"
                                            autocomplete="off">
                                        {{-- show team available --}}
                                        @if ($teamIdsAvailable === true || (count($teamsOptionAll) && $teamTreeAvailable))
                                            @foreach($teamsOptionAll as $option)
                                                @if ($teamIdsAvailable === true || in_array($option['value'], $teamIdsAvailable))
                                                    <option value="{{ $option['value'] }}" class="checkbox-item"
                                                            {{ in_array($option['value'], array_map("trim", explode(",", $teamIdCurrent))) ? 'selected' : '' }}<?php
                                                            if ($teamIdsAvailable === true):
                                                            elseif (! in_array($option['value'], $teamIdsAvailable)): ?> disabled<?php else:
                                                        ?>{{ $option['option'] }}<?php endif; ?>>{{ $option['label'] }}</option>
                                                @endif
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            @endif
                            {{-- end show team available --}}
                        </div>
                        @if($statusWork === 'work')

                        <div class="select-status" style="display: flex;">
                            <?php
                                $filterStatus = Form::getFilterData('expect', "status", $urlFilter);
                                ?>
                                <label style="margin-left: 20px; margin-right: 5px" for="select-team-member" style="float: left; margin-right: 5px">{{ trans('team::view.Status') }}</label>
                                <div class="input-box filter-multi-select multi-select-style btn-select-team">
                                    <select style="width:fit-content" name="filter[expect][status]" class="form-control select-grid filter-grid select-search" autocomplete="off">
                                        <option>&nbsp;</option>
                                        @foreach($optionWorkingStatus as $key => $value)
                                        <option value="{{ $key }}"<?php if ($key == $filterStatus): ?> selected<?php endif;
                                    ?>>{{ $value }}</option>
                                        @endforeach
                                </select>
                                </div>
                        </div>
                        @endif      
                    </div>
                    <div class="text-right member-group-btn">
                        @if ($permissExport)
                        <button class="btn btn-success" type="button" data-toggle="modal" data-target="#modal_member_export">{{ trans('team::view.Export') }}</button>
                        <button type="button" class="btn btn-success export-relationship" id="modal_member_relationship_export" data-url="{!! URL::route('team::team.member.export_member.relationship') !!}"><i class="fa fa-download"></i> {!! trans('team::view.Export Relationship') !!}</button>
                        @endif
                        @include('team::include.filter')
                    </div>
                </div>
                <div class="tab-content">
                    <ul class="nav nav-tabs">
                        <li id="work" <?php if ($statusWork === 'work') echo ' class="active"'; ?>>
                            <a >{{ trans('team::view.Working') }}</a>
                        </li>
                        <li id="leave" <?php if ($statusWork === 'leave') echo ' class="active"'; ?>>
                            <a >{{ trans('team::view.EndWork') }}</a>
                        </li>
                        <li id="all" <?php if ($statusWork === 'all') echo ' class="active"'; ?>>
                            <a >{{ trans('team::view.All') }}</a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div id="home" class="tab-pane fade in active">
                            @include('team::member.tab')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($permissExport)
        @include('team::member.member-modal')
        @include('team::member.include.member-relationship-modal')
    @endif

@endsection

@section('script')
    <script>
        var textNoneItemSelected = '{!! trans("team::export.none_item_selected") !!}';
        var textNoneColSelected = '{!! trans("team::export.none_col_selected") !!}';
        var teamPath = {!! json_encode($teamPath) !!};
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/1.3.3/FileSaver.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script type="text/javascript" src="{{ asset('lib/xlsx/jszip.js') }}"></script>
    <script type="text/javascript" src="{{ asset('lib/xlsx/xlsx.js') }}"></script>
    <script src="{{ CoreUrl::asset('team/js/xlsx-func.js') }}"></script>
    <script src="{{ CoreUrl::asset('team/js/script.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
    <script>
        $(function () {
            $('.list_export_cols').sortable({
                stop: function (event, ui) {
                    $('.list_export_cols li input').each(function (index) {
                        $(this).attr('name', 'columns['+ index +']');
                    });
                },
            });
            $('#form_export_relationship button[type="submit"]').click(function () {
                var btn = $(this);
                setTimeout(function () {
                    btn.prop('disabled', false);
                    btn.closest('.modal').modal('hide');
                }, 500);
            });
            $('#work').click(function(){
                window.location.href = "{{ route('team::team.member.index') }}";
            });
            $('#leave').click(function(){
                window.location.href = "{{ route('team::team.member.index', ['statusWork' => 'leave']) }}";
            });
            $('#all').click(function(){
                window.location.href = "{{ route('team::team.member.index', ['statusWork' => 'all']) }}";
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
            $('.js-select-multi-role').multiselect({
                numberDisplayed: 1,
                nonSelectedText: '--------------',
                allSelectedText: '{{ trans('project::view.All') }}',
                enableCaseInsensitiveFiltering: true,
                onDropdownHide: function(event) {
                    RKfuncion.filterGrid.filterRequest(this.$select);
                }
            });
            // Limit the string length to column roles.
            $('.role-special').shortedContent({showChars: 150});
        });

        $(document).on('mouseup', 'li.checkbox-item', function () {
            var domInput = $(this).find('input');
            var id = domInput.val();
            var isChecked = !domInput.is(':checked');
            if (teamPath[id] && typeof teamPath[id].child !== "undefined") {
                var teamChild = teamPath[id].child;
                $('li.checkbox-item input').map((i, el) => {
                    if (teamChild.indexOf(parseInt($(el).val())) !== -1 && $(el).is(':checked') === !isChecked) {
                        $(el).click();
                    }
                });
            }
            setTimeout(() => {
                changeLabelSelected();
            }, 0)
        });
        $(document).ready(function () {
            changeLabelSelected();
        });

        function changeLabelSelected() {
            var checkedValue = $(".list-team-select-box option:selected");
            var title = '';
            if (checkedValue.length === 0) {
                $(".list-team-select-box .multiselect-selected-text").text('--------------');
            }
            if (checkedValue.length === 1) {
                $(".list-team-select-box .multiselect-selected-text").text($.trim(checkedValue.text()));
            }
            for (let i = 0; i < checkedValue.length; i++) {
                title += $.trim(checkedValue[i].label) + ', ';
            }
            $('.list-team-select-box button').prop('title', title.slice(0, -2))
        }
    </script>
@endsection
