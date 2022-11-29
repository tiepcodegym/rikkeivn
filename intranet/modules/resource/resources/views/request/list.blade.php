@extends('layouts.default')
@section('title')
    {{ trans('resource::view.Request.List.Request list') }}
@endsection

@section('content')
<?php
use Carbon\Carbon;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View;
use Rikkei\Resource\View\getOptions;
use Illuminate\Support\Facades\URL;
use Rikkei\Team\View\Permission;
use Rikkei\Team\View\TeamList;
use Rikkei\Core\View\CoreUrl;

$urlSubmit = route('resource::request.showChannel');
$teamsOptionAll = TeamList::toOption(null, true, false);
$teamFilter = Form::getFilterData('exception', 'request_team.team_id');
$proLangFilter = Form::getFilterData('exception', 'pro_lang_ids');
$statusOption = getOptions::getInstance()->getStatusApproveOption();
$statusFilter =  Form::getFilterData('requests.status');
$publishFilter =  Form::getFilterData('requests.published');
$approveOption = getOptions::getInstance()->getApproveOption();
$approveFilter =  Form::getFilterData('requests.approve');
$today = strtotime(date("Y-m-d"));
$now = Carbon::now()->format('Y-m-d');
$teamPath = Team::getTeamPath();
?>

<div class="row list-css-page list-request-page">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-6"></div>
                    <div class="col-sm-6"></div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="box-body">
                            <div class="filter-action">
                                <div class="pull-left"> 
                                    <h3 class="margin-top-0">
                                        {{ trans('resource::view.Request.List.Sum.Request.Resource')}} &nbsp;
                                        <span class="label bg-blue font-percent-80">{{ $sum }}</span>
                                    </h3>
                                </div>
                                @if (Permission::getInstance()->isAllow('resource::request.create'))
                                <a class="btn btn-success" href="{{ route('resource::request.create') }}">
                                    {{ trans('resource::view.Add new') }}
                                </a>
                                @endif
                                <button class="btn btn-primary btn-reset-filter">
                                    <span>{{ trans('resource::view.Reset filter') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
                                </button>
                                <button class="btn btn-primary btn-search-filter">
                                    <span>{{ trans('resource::view.Search') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
                                </button>
                            </div>
                        </div>
                        <div class="filter-input-grid">
                            <div class="col-sm-12">
                                <div class="form-group row col-sm-4">
                                    <label for="" class="col-sm-3 col-form-label">{{ trans('project::view.Title') }}</label>
                                    <div class="form-group row col-sm-9">
                                        <input type="text" class='form-control filter-grid' name="filter[except][requests.title]" value="{{ Form::getFilterData('except', 'requests.title') }}" placeholder="{{ trans('team::view.Search') }}..." />
                                    </div>
                                </div>
                                <div class="form-group row col-sm-4">
                                    <label for="" class="col-sm-3 col-form-label">{{ trans('resource::view.Team') }}</label>
                                    <div class="form-group row col-sm-9">
                                        <div class="list-team-select-box">
                                            {{-- show team available --}}
                                            @if (is_object($teamIdsAvailable))
                                                <p>
                                                    <b>Team:</b>
                                                    <span>{{ trim($teamIdsAvailable->name) }}</span>
                                                </p>
                                            @elseif ($teamIdsAvailable || ($teamTreeAvailable && count($teamTreeAvailable)))
                                                <div class="input-box filter-multi-select multi-select-style btn-select-team">
                                                    <select name="filter[except][team_ids][]" id="select-team-member" multiple
                                                            class="form-control filter-grid multi-select-bst select-multi"
                                                            autocomplete="off">
                                                        {{-- show team available --}}
                                                        @if ($teamIdsAvailable === true || (count($teamsOptionAll) && $teamTreeAvailable))
                                                            @foreach($teamsOptionAll as $option)
                                                                @if ($teamIdsAvailable === true || in_array($option['value'], $teamTreeAvailable))
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
                                    </div>
                                </div>
                                <div class="form-group row col-sm-4">
                                    <label for="" class="col-sm-3 col-form-label">{{ trans('resource::view.Recruiter') }}</label>
                                    <div class="form-group row col-sm-9">
                                        <input type="text" class='form-control filter-grid' name="filter[except][requests.recruiter]" value="{{ Form::getFilterData('except', 'requests.recruiter') }}" placeholder="{{ trans('team::view.Search') }}..." />
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-12 btn-programming-search">
                                <div class="form-group row col-sm-4 btn-prog-search">
                                    <label for="" class="col-sm-3 col-form-label">{{ trans('resource::view.Candidate.List.Programming languages') }}</label>
                                    <div class="form-group row col-sm-9">
                                        <select style="width: 250px" id="pro_lang" name="filter[exception][pro_lang_ids][]" class="form-control multi-select-bst filter-grid" multiple="multiple">
                                            @foreach($proLangs as $proLang)
                                                <option value="{{ $proLang->id }}"<?php
                                                if (isset($proLangFilter) && in_array($proLang->id, $proLangFilter)): ?> selected<?php endif;
                                                    ?>>{{ $proLang->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row col-sm-4">
                                    <label for="" class="col-sm-3 col-form-label">{{ trans('resource::view.Status') }}</label>
                                    <div class="form-group row col-sm-9">
                                        <select style="width:100px" name="filter[requests.status]" class="form-control select-grid filter-grid select-search">
                                            <option value="">&nbsp;</option>
                                            @foreach($statusOption as $option)
                                                <option value="{{ $option['id'] }}"<?php
                                                if ($option['id'] == $statusFilter || $option['id'] == getOptions::STATUS_INPROGRESS): ?> selected<?php endif;
                                                    ?>>{{ $option['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row col-sm-4">
                                    <label for="" class="col-sm-3 col-form-label">{{ trans('resource::view.Request.List.Approve') }}</label>
                                    <div class="form-group row col-sm-9">
                                        <select style="width:100px" name="filter[requests.approve]" class="form-control select-grid filter-grid select-search">
                                            <option value="">&nbsp;</option>
                                            @foreach($approveOption as $option)
                                                <option value="{{ $option['id'] }}"<?php
                                                if ($option['id'] == $approveFilter): ?> selected<?php endif;
                                                    ?>>{{ $option['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group row col-sm-4">
                                    <label for="" class="col-sm-3 col-form-label">{{ trans('resource::view.Request.Create.Publish') }}</label>
                                    <div class="form-group row col-sm-9">
                                        <select style="width:100px" name="filter[requests.published]" class="form-control select-grid filter-grid select-search">
                                            <option value="">&nbsp;</option>
                                            <option value="{{ getOptions::STATUS_PUBLISH }}" {{ !empty($publishFilter) && getOptions::STATUS_PUBLISH == $publishFilter ? 'selected' : '' }}>{{ trans('resource::view.Request.Create.Published') }}</option>
                                            <option value="0" {{ !is_null($publishFilter) && getOptions::STATUS_DISPUBLISH == $publishFilter ? 'selected' : '' }}>{{ trans('resource::view.Request.Create.Dispublished') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>

                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover dataTable" role="grid" aria-describedby="example2_info">
                                <thead>
                                    <tr role="row">
                                        <th style="width:8%" class="sorting {{ Config::getDirClass('id') }}" data-order="id" data-dir="{{ Config::getDirOrder('id') }}" >{{ trans('resource::view.Request.List.Id') }}</th>
                                        <th class="sorting col-sm-2 {{ Config::getDirClass('title') }}" data-order="title" data-dir="{{ Config::getDirOrder('title') }}" >{{ trans('resource::view.Request.List.Title') }}</th>
                                        <th class="sorting width-100 {{ Config::getDirClass('team_name') }}" data-order="team_name" data-dir="{{ Config::getDirOrder('team_name') }}" >{{ trans('resource::view.Request.List.Team') }}</th>
                                        <th class="sorting col-sm-3 {{ Config::getDirClass('recruiter') }}" data-order="recruiter" data-dir="{{ Config::getDirOrder('recruiter') }}" >{{ trans('resource::view.Recruiter') }}</th>
                                        <th class="sorting col-sm-3 {{ Config::getDirClass('pro_lang_names') }}" data-order="pro_lang_names" data-dir="{{ Config::getDirOrder('pro_lang_names') }}" >{{ trans('resource::view.Programming language') }}</th>
                                        <th class="sorting col-sm-2 {{ Config::getDirClass('deadline') }}" data-order="deadline" data-dir="{{ Config::getDirOrder('deadline') }}" >{{ trans('resource::view.Request.List.Deadline') }}</th>
                                        <th style="width:20%;text-align:center">{{ trans('resource::view.Request.List.Status') }}</th>
                                        <th style="width:20%;text-align:center">{{ trans('resource::view.Request.List.Approve') }}</th>
                                        <th style="width:12%" id="tooltip-request-list">{{ trans('resource::view.Request.List.Number.Passed') }}
                                            <i class="fa fa-question-circle" data-toggle="tooltip"  title="{{ trans('resource::view.Request.List.Number.Passed.Over') }}"></i>
                                        </th>
                                        <th class="col-sm-1" rowspan="1" colspan="1" ></th>
                                   </tr>
                                </thead>
                                <tbody>
                                    @if(count($collectionModel) > 0)
                                    @foreach($collectionModel as $item)
                                    <tr role="row" class="odd">
                                        <td rowspan="1" colspan="1" >{{ $item->id }}</td>
                                        <td rowspan="1" colspan="1" >
                                            @if (Permission::getInstance()->isAllow('resource::request.detail'))
                                            <a title="" href='{{ URL::route('resource::request.detail', ['id' => $item->id]) }}'>
                                                <span @if ($item->deadlineTime < $today && 
                                                        (int) $item->status !== getOptions::STATUS_CLOSE)
                                                        style="color:red"
                                                    @endif>
                                                {{ $item->title }}
                                                </span>
                                            </a>
                                            @else
                                                <span @if ($item->deadlineTime < $today && 
                                                        (int) $item->status !== getOptions::STATUS_CLOSE)
                                                        style="color:red"
                                                    @endif>
                                                {{ $item->title }}
                                                </span>
                                            @endif
                                        </td>
                                        <td rowspan="1" colspan="1" >{{ $item->team_name }}</td>
                                        <td rowspan="1" colspan="1" >{{ $item->recruiter }}</td>
                                        <td rowspan="1" colspan="1" >{{ $item->pro_lang_names }}</td>
                                        <td rowspan="1" colspan="1" >{{ $item->deadline }}</td>
                                        <td rowspan="1" colspan="1" class="text-align-center" >
                                            @if ($item->deadline < $now && $item->status == getOptions::STATUS_INPROGRESS)
                                                {{ trans('resource::view.Request.Create.Expired') }}
                                            @else
                                                {{ getOptions::getInstance()->getStatus($item->status) }}
                                            @endif
                                        </td>
                                        <td rowspan="1" colspan="1" class="text-align-center" >
                                            {{ getOptions::getInstance()->getApprove($item->approve) }}
                                        </td>
                                        <td>
                                            {{ $item->countCandidatePass}} / {{$item->sumOfOneResource}}
                                        </td>
                                        <td rowspan="1" colspan="1" >
                                            @if (Permission::getInstance()->isAllow('resource::request.edit'))
                                            <a class="btn-edit" title="Edit" href='{{ URL::route('resource::request.edit', ['id' => $item->id]) }}'>
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                    @else
                                    <tr><td colspan="8" class="text-align-center"><h2>{{trans('sales::view.No result not found')}}</h2></td></tr>
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
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
    <!-- /.col -->
</div>
<!-- /.row -->
<div class="modal " id="modal-channel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">

            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
@endsection
<!-- Styles -->
@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link href="{{ asset('sales/css/sales.css') }}" rel="stylesheet" type="text/css" >
<link href="{{ asset('resource/css/resource.css') }}" rel="stylesheet" type="text/css" >
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" >
@endsection

<!-- Script -->
@section('script')
<script src="{{ asset('resource/js/request/list.js') }}"></script>
<script src="{{ CoreUrl::asset('team/js/xlsx-func.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
<script type="text/javascript">
    var teamPath = {!! json_encode($teamPath) !!};
    jQuery(document).ready(function ($) {
        selectSearchReload();
        $('#pro_lang').multiselect();
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
        selectSearchReload();
        changeLabelSelected();
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
