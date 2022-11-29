@extends('layouts.default')
@section('title')
    {{ trans('resource::view.Candidate.List.Candidate list') }}
@endsection

@section('content')
<?php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\URL;
    use Rikkei\Core\Model\CoreModel;
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Core\View\Form;
    use Rikkei\Core\View\View;
    use Rikkei\Project\Model\ProjectMember;
    use Rikkei\Resource\Model\Candidate;
    use Rikkei\Resource\Model\ResourceRequest;
    use Rikkei\Resource\View\getOptions;
    use Rikkei\Team\View\Config;
    use Rikkei\Team\View\Permission;
    use Rikkei\Team\View\TeamList;


$teamsOptionAll = TeamList::toOption(null, true, false);
$teamFilter = Form::getFilterData('except','teams.id');
$teamSelectedFilter = Form::getFilterData('except','team.selected');
$recruiterFilter = Form::getFilterData('except','candidates.recruiter');
$requestFilter = Form::getFilterData('except','request');
$statusOptionsAll = getOptions::getInstance()->getCandidateStatusOptionsAll();
$typeOptions = Candidate::getTypeOptions();
$statusCandidateFilter = Form::getFilterData('except', 'candidates.status');
$typeCandidateFilter = Form::getFilterData('candidates.type');
$allTypeCandidate = Candidate::getAllTypeCandidate();
$typeSelectedFilter = Form::getFilterData('candidates.type_candidate');

$startDateTime = Carbon::now()->format('Y-m-d 00:00:00');
$endDateTime = Carbon::now()->format('Y-m-d 23:59:60');
?>

<div class="row list-css-page">
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
                            <div class="row">
                                <div class="col-md-4">
                                    @if (Permission::getInstance()->isAllow('resource::candidate.updateRecruiter'))
                                    <form method="post" id="update-recruiter" action="{{ URL::route('resource::candidate.updateRecruiter') }}">
                                        {!! csrf_field() !!}
                                        <div class="col-sm-6 input-group pull-left">
                                            <span class="input-group-btn">
                                                <button type="button" id="submit-btn" class="btn btn-primary">{{ trans('resource::view.Candidate.List.Assign to Recruiter') }}</button>
                                            </span>
                                            <select class="form-control recruiter-box" id="recruiterList" name="recruiterList" style="width: 250px">
                                                <option value="">&nbsp;</option>
                                                @foreach($recruiters as $option)
                                                    <option value="{{ $option }}">{{ $option }}</option>
                                                @endforeach
                                            </select>
                                            <span class="error errTxt"></span>
                                        </div>
                                    </form>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                </div>
                                <div class="col-md-2">
                                    <div class="group-btn float-right text-right">
                                        <button class="btn btn-primary btn-reset-filter">
                                            <span>{{ trans('team::view.Reset filter') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
                                        </button>
                                        <button class="btn btn-primary btn-search-filter">
                                            <span>{{ trans('team::view.Search') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="note">
                                        <br>
                                        <b>Chú thích:</b>
                                        <ul>
                                            <li>Ứng viên có lịch phỏng vẫn hôm nay thì background màu xanh</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="candidateTbl" class="table table-bordered table-hover dataTable" role="grid" aria-describedby="example2_info">
                                <thead>
                                    <tr role="row">
                                        <th class="sorting {{ Config::getDirClass('id') }}" data-order="id" data-dir="{{ Config::getDirOrder('id') }}" >{{ trans('resource::view.Candidate.List.Id') }}</th>
                                        <th class="sorting {{ Config::getDirClass('fullname') }}" style="min-width: 75px" data-order="fullname" data-dir="{{ Config::getDirOrder('fullname') }}" >{{ trans('resource::view.Candidate.List.Fullname') }}</th>
                                        <th class="sorting {{ Config::getDirClass('email') }}" data-order="email" data-dir="{{ Config::getDirOrder('email') }}" >{{ trans('resource::view.Candidate.List.Email') }}</th>
                                        <th class="sorting {{ Config::getDirClass('request_id') }}" data-order="request_id" data-dir="{{ Config::getDirOrder('request_id') }}" >{{ trans('resource::view.Candidate.List.Request',['request'=>'']) }}</th>
                                        <th class="sorting {{ Config::getDirClass('team_name') }}" data-order="team_name" data-dir="{{ Config::getDirOrder('team_name') }}" >{{ trans('resource::view.Candidate.List.Team') }}</th>
                                        <th class="width-70">{{ trans('resource::view.Candidate.List.Position apply') }}</th>
                                        <th class="sorting {{ Config::getDirClass('recruiter') }}" data-order="recruiter" data-dir="{{ Config::getDirOrder('recruiter') }}" >{{ trans('resource::view.Recruiter') }}</th>
                                        <th class="sorting width-70 {{ Config::getDirClass('experience') }}" data-order="experience" data-dir="{{ Config::getDirOrder('experience') }}" >{{ trans('resource::view.Candidate.List.Experience') }}</th>
                                        <th class="width-70">{{ trans('resource::view.Candidate.List.Programming languages') }}</th>
                                        <th class="sorting {{ Config::getDirClass('status') }}" data-order="status" data-dir="{{ Config::getDirOrder('status') }}" >{{ trans('resource::view.Candidate.List.Status') }}</th>
                                        <th class="sorting {{ Config::getDirClass('status_update_date') }}" data-order="status_update_date" data-dir="{{ Config::getDirOrder('status_update_date') }}" >{{ trans('resource::view.Candidate.List.Status update date') }}</th>
                                        <th class="sorting {{ Config::getDirClass('type') }}" data-order="type" data-dir="{{ Config::getDirOrder('type') }}" >{{ trans('resource::view.Type') }}</th>
                                        <th class="sorting {{ Config::getDirClass('type_candidate') }}" data-order="type_candidate" data-dir="{{ Config::getDirOrder('type_candidate') }}" >{{ trans('resource::view.Type_candidate') }}</th>
                                        @if (Permission::getInstance()->isAllow('resource::candidate.edit')
                                        || Permission::getInstance()->isAllow('resource::candidate.deleteCandidate')
                                        || Permission::getInstance()->isAllow('resource::candidate.detail'))
                                        <th style="width: 150px"></th>
                                        @endif
                                   </tr>
                                </thead>
                                <tbody>
                                    <tr class="filter-input-grid">
                                        <td>&nbsp;</td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <input type="text" class='form-control filter-grid' name="filter[candidates.fullname]" value="{{ Form::getFilterData('candidates.fullname') }}" placeholder="{{ trans('team::view.Search') }}..." />
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <input type="text" class='form-control filter-grid' name="filter[candidates.email]" value="{{ Form::getFilterData('candidates.email') }}" placeholder="{{ trans('team::view.Search') }}..."  />
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <select id="request_filter" name="filter[except][request]"  style="width:100%"
                                                            class="form-control select-grid filter-grid width-93 select2-hidden-accessible select-search"
                                                            data-remote-url="{{ URL::route('resource::request.list.search.ajax') }}">
                                                        @if ($requestFilter)
                                                        <option value="{{ $requestFilter }}" selected>{{ ResourceRequest::find($requestFilter)->title }}</option>
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <select style="width: 160px" name="filter[except][teams.id]" class="form-control select-grid filter-grid select-search">
                                                        <option value="">&nbsp;</option>
                                                        @foreach($teamsOptionAll as $option)
                                                            <option value="{{ $option['value'] }}"<?php
                                                                if ($option['value'] == $teamFilter): ?> selected<?php endif;
                                                                    ?>>{{ $option['label'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <select style="width: 100px" name="filter[except][candidates.position]" class="form-control select-grid filter-grid select-search">
                                                        <option value="">&nbsp;</option>
                                                        @foreach($positionOptions as $key => $value)
                                                            <option value="{{ $key }}"<?php
                                                                if ($key == Form::getFilterData('except','candidates.position')): ?> selected<?php endif;
                                                                    ?>>{{ $value }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <select name="filter[except][candidates.recruiter]" class="form-control select-grid filter-grid select-search width-160">
                                                        <option value="">&nbsp;</option>
                                                        @foreach($recruiters as $option)
                                                            <option value="{{ $option }}"<?php
                                                                if ($option == $recruiterFilter): ?> selected<?php endif;
                                                                    ?>>{{ $option }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <input type="text" class='form-control filter-grid' name="filter[candidates.experience]" value="{{ Form::getFilterData('candidates.experience') }}" placeholder="{{ trans('team::view.Search') }}..."  />
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <select name="filter[except][candidate_programming.programming_id]" class="form-control select-grid filter-grid select-search width-100">
                                                        <option value="">&nbsp;</option>
                                                        @foreach($programList as $pro)
                                                            <option value="{{ $pro->id }}"<?php
                                                                if ($pro->id == Form::getFilterData('except', 'candidate_programming.programming_id')): ?> selected<?php endif;
                                                                    ?>>{{ $pro->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <select name="filter[except][candidates.status]" class="form-control select-grid filter-grid select-search width-100">
                                                        <option value="">&nbsp;</option>
                                                        @foreach($statusOptionsAll as $option)
                                                            <option value="{{ $option['id'] }}"<?php
                                                                if ($option['id'] == $statusCandidateFilter): ?> selected<?php endif;
                                                                    ?>>{{ $option['name'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <input type="text" class='form-control filter-grid width-80' name="filter[candidates.status_update_date]" value="{{ Form::getFilterData('candidates.status_update_date') }}" placeholder="{{ trans('team::view.Search') }}..." />
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <select name="filter[candidates.type]" class="form-control select-grid filter-grid select-search width-100">
                                                        <option value="">&nbsp;</option>
                                                        @foreach($typeOptions as $option)
                                                            <option value="{{ $option['id'] }}"<?php
                                                                if ($option['id'] == $typeCandidateFilter): ?> selected<?php endif;
                                                                    ?>>{{ $option['name'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <select name="filter[candidates.type_candidate]" class="form-control select-grid filter-grid select-search width-100">
                                                        <option value="">&nbsp;</option>
                                                        @foreach($allTypeCandidate as $key => $value)
                                                            <option value="{{ $key }}"<?php
                                                            if ($key == $typeSelectedFilter): ?> selected<?php endif;
                                                                    ?>>{{ $value }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </td>
                                        @if (Permission::getInstance()->isAllow('resource::candidate.edit')
                                        || Permission::getInstance()->isAllow('resource::candidate.deleteCandidate')
                                        || Permission::getInstance()->isAllow('resource::candidate.detail'))
                                        <td></td>
                                        @endif
                                    </tr>
                                    @if(count($collectionModel) > 0)
                                    @foreach($collectionModel as $item)
                                    <tr role="row" class="odd
                                        @if (($item->interview_plan >= $startDateTime && $item->interview_plan <= $endDateTime) ||
                                            ($item->interview2_plan >= $startDateTime && $item->interview2_plan <= $endDateTime))
                                            candidate-bg-primary
                                            {{$item->interview_plan }} {{ $item->interview2_plan}}
                                        @endif"
                                    >
                                        <td rowspan="1" colspan="1" >{{ $item->id }}</td>
                                        <td rowspan="1" colspan="1" >
                                            @if ($item->status != getOptions::DRAFT)
                                                <a href='{{ URL::route('resource::candidate.detail', ['id' => $item->id]) }}'>
                                                    {{ $item->fullname }}
                                                </a>
                                            @else
                                                {{ $item->fullname }}
                                            @endif
                                        </td>
                                        <td rowspan="1" colspan="1" class="width-160 break-all" >{{ $item->email }}</td>
                                        <td rowspan="1" colspan="1" >
                                        <?php
                                            if (!empty($item->requests)) :
                                                $strUrl = [];
                                                $requests = explode(CoreModel::GROUP_CONCAT, $item->requests);
                                                if (is_array($requests) && count($requests)) :
                                                    foreach ($requests as $requestStr) :
                                                        if (!empty($requestStr)) :
                                                            $requestInfo = explode(CoreModel::CONCAT, $requestStr);
                                                            if (is_array($requestInfo) && count($requestInfo)) :
                                                                $strUrl[] = "<a target='_blank' href='" . route('resource::request.detail', ['id' => $requestInfo[1]]) . "'>" . $requestInfo[0] . "</a>";
                                                            endif;
                                                        endif;
                                                    endforeach;
                                                endif;
                                                echo implode('<br>', $strUrl);
                                            endif;
                                        ?>
                                        </td>
                                        <td rowspan="1" colspan="1" >{{ $item->team_name }}</td>
                                        <td rowspan="1" colspan="1" >
                                        <?php
                                            if (!empty($item->positions)) :
                                                $strPos = [];
                                                $positions = explode(',', $item->positions);
                                                if (is_array($positions) && count($positions)) :
                                                    foreach ($positions as $pos) :
                                                        $strPos[] = getOptions::getInstance()->getRole($pos);
                                                    endforeach;
                                                endif;
                                                echo implode(', ', $strPos);
                                            endif;
                                        ?>
                                        </td>
                                        <td rowspan="1" colspan="1" class="width-160 break-all">{{ $item->recruiter }}</td>
                                        <td rowspan="1" colspan="1" >{{ $item->experience }}</td>
                                        <td rowspan="1" colspan="1" >{{ $item->programs_name }}</td>
                                        <td rowspan="1" colspan="1" >{{ getOptions::getInstance()->getCandidateStatus($item->status, $item) }}</td>
                                        <td rowspan="1" colspan="1" >{{ $item->status_update_date }}</td>
                                        <td rowspan="1" colspan="1" >{{ Candidate::getType($item->type) }}</td>
                                        <td rowspan="1" colspan="1" >{{ Candidate::getTypeCandidate($item->type_candidate) }}</td>
                                        @if (Permission::getInstance()->isAllow('resource::candidate.edit')
                                        || Permission::getInstance()->isAllow('resource::candidate.delete')
                                        || Permission::getInstance()->isAllow('resource::candidate.detail'))
                                        <td class="text-align-center white-space-nowrap">
                                            @if (Permission::getInstance()->isAllow('resource::candidate.edit'))
                                            <a class="btn-edit" title="{{trans('resource::view.Candidate.List.Edit')}}" href='{{ URL::route('resource::candidate.edit', ['id' => $item->id]) }}'>
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            @endif
                                            @if (Permission::getInstance()->isAllow('resource::candidate.deleteCandidate'))
                                            <form action="{{route('resource::candidate.deleteCandidate')}}" method="post" class="form-inline">
                                                {!! csrf_field() !!}
                                                <input type="hidden" name="id" value="{{$item->id}}">
                                                <button class="btn-delete delete-confirm" title="{{trans('resource::view.Candidate.List.Delete')}}" >
                                                    <span><i class="fa fa-trash"></i></span>
                                                </button>
                                            </form>
                                            @endif
                                        </td>
                                        @endif
                                    </tr>
                                    @endforeach
                                    @else
                                    <tr><td colspan="13" class="text-align-center"><h2>{{trans('sales::view.No result not found')}}</h2></td></tr>
                                    @endif
                                </tbody>
                            </table>
                        </form>
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
<div class="modal fade modal-success" id="update-confirm">
    <div class="modal-dialog">
        <div class="modal-content">
            {!! csrf_field() !!}
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h3 class="box-title">{{ trans('resource::view.Candidate.List.Update candidate list') }}</h3>
            </div>
            <div class="modal-body">
                <div id="assign_body">
                    <div class="form-group form-group-select2">
                        <p>{{ trans('resource::message.Update recruiter for all filtered records?') }}</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline pull-left" data-dismiss="modal">{{ trans('resource::view.Candidate.List.Close') }}</button>
                <button type="button" id="accept" class="btn btn-outline pull-right">{{ trans('resource::view.Candidate.List.Accept') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>

@endsection
<!-- Styles -->
@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link href="{{ CoreUrl::asset('resource/css/candidate/list.css') }}" rel="stylesheet" type="text/css" >
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<style>
    .select2-results__options {
        width: max-content !important;
    }
    .select2-dropdown {
        width: auto !important;
    }
</style>
@endsection

<!-- Script -->
@section('script')
<script src="{{ CoreUrl::asset('resource/js/candidate/list.js') }}"></script>
<script src="{{ CoreUrl::asset('resource/js/request/list.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script type="text/javascript">
    var errMsg = "{{ trans('resource::message.Required field') }}";
    jQuery(document).ready(function ($) {
        selectSearchReload();
        $('#recruiterList').select2();
        $('.select-search').select2();
        $('#submit-btn').preSaveProcessing();
        RKfuncion.select2.elementRemote(
            $('#request_filter')
        );
    });
</script>
@endsection
