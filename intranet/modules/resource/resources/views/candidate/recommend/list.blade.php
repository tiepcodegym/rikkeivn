@extends('layouts.default')
@section('title')
    {{ trans('resource::view.Recommend list candidate') }}
@endsection

<?php

use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Resource\View\getOptions;
use Rikkei\Team\View\TeamList;

$teamsOptionAll = TeamList::toOption(null, true, false);
$recruiterFilter = Form::getFilterData('except', 'candidates.recruiter');
$statusOptionsAll = getOptions::getInstance()->getCandidateStatusOptionsAll();
$statusCandidateFilter = Form::getFilterData('except', 'candidates.status');
$filterPrograming = Form::getFilterData('except', 'candidate_programming.programming_id');
?>

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/>
    <link href="{{ asset('resource/css/candidate/list.css') }}" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css"/>
@endsection

@section('content')
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
                                <div class="col-sm-12 filter-action">
                                    <p class="error" style="float: left; text-align: left">{{ trans('resource::view.Recommend reward note') }}</p>
                                    <a href=" {{ route('resource::candidate.create.recommend') }}"
                                       class="btn btn-primary">{{ trans('resource::view.Recommend candidate') }}</a>
                                    <button class="btn btn-primary btn-reset-filter">
                                        <span>{{ trans('team::view.Reset filter') }} <i
                                                    class="fa fa-spin fa-refresh hidden"></i></span>
                                    </button>
                                    <button class="btn btn-primary btn-search-filter">
                                        <span>{{ trans('team::view.Search') }} <i
                                                    class="fa fa-spin fa-refresh hidden"></i></span>
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table id="candidates" class="table table-bordered table-hover dataTable"
                                       role="grid" aria-describedby="example2_info">
                                    <thead>
                                    <tr role="row">
                                        <th class="sorting {{ Config::getDirClass('fullname') }}"
                                            data-order="fullname"
                                            data-dir="{{ Config::getDirOrder('fullname') }}">{{ trans('resource::view.Candidate.List.Fullname') }}</th>
                                        <th class="sorting {{ Config::getDirClass('email') }}"
                                            data-order="email"
                                            data-dir="{{ Config::getDirOrder('email') }}">{{ trans('resource::view.Candidate.List.Email') }}</th>
                                        <th class="sorting {{ Config::getDirClass('mobile') }}"
                                            data-order="mobile"
                                            data-dir="{{ Config::getDirOrder('mobile') }}">{{ trans('resource::view.Candidate.List.Mobile') }}</th>
                                        <th class="sorting width-70 {{ Config::getDirClass('experience') }}"
                                            data-order="experience"
                                            data-dir="{{ Config::getDirOrder('experience') }}">{{ trans('resource::view.Candidate.List.Experience') }}</th>
                                        <th class="width-70">{{ trans('resource::view.Candidate.List.Programming languages') }}</th>
                                        <th class="width-70">{{ trans('resource::view.University') }}</th>
                                        <th class="width-70">{{ trans('resource::view.Certificate') }}</th>
                                        <th class="sorting {{ Config::getDirClass('status') }}"
                                            data-order="status"
                                            data-dir="{{ Config::getDirOrder('status') }}">{{ trans('resource::view.Candidate.List.Status') }}</th>
                                        <th class="sorting {{ Config::getDirClass('recruiter') }}"
                                            data-order="recruiter"
                                            data-dir="{{ Config::getDirOrder('recruiter') }}">{{ trans('resource::view.Recruiter') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr class="filter-input-grid">
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <input type="text" class='form-control filter-grid'
                                                           name="filter[candidates.fullname]"
                                                           value="{{ Form::getFilterData('candidates.fullname') }}"
                                                           placeholder="{{ trans('team::view.Search') }}..."/>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <input type="text" class='form-control filter-grid'
                                                           name="filter[candidates.email]"
                                                           value="{{ Form::getFilterData('candidates.email') }}"
                                                           placeholder="{{ trans('team::view.Search') }}..."/>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <input type="text" class='form-control filter-grid'
                                                           name="filter[candidates.mobile]"
                                                           value="{{ Form::getFilterData('candidates.mobile') }}"
                                                           placeholder="{{ trans('team::view.Search') }}..."/>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <input type="text" class='form-control filter-grid'
                                                           name="filter[candidates.experience]"
                                                           value="{{ Form::getFilterData('candidates.experience') }}"
                                                           placeholder="{{ trans('team::view.Search') }}..."/>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <select name="filter[except][candidate_programming.programming_id]"
                                                            class="form-control select-grid filter-grid select-search width-100">
                                                        <option value="">&nbsp;</option>
                                                        @foreach($programs as $pro)
                                                            <option value="{{ $pro->id }}"
                                                                    {{ $pro->id == $filterPrograming ? 'selected' : '' }} >
                                                                {{ $pro->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <input type="text" class='form-control filter-grid'
                                                           name="filter[candidates.university]"
                                                           value="{{ Form::getFilterData('candidates.university') }}"
                                                           placeholder="{{ trans('team::view.Search') }}..."/>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <input type="text" class='form-control filter-grid'
                                                           name="filter[candidates.certificate]"
                                                           value="{{ Form::getFilterData('candidates.certificate') }}"
                                                           placeholder="{{ trans('team::view.Search') }}..."/>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <select name="filter[except][candidates.status]"
                                                            class="form-control select-grid filter-grid select-search width-100">
                                                        <option value="">&nbsp;</option>
                                                        @foreach($statusOptionsAll as $option)
                                                            <option value="{{ $option['id'] }}"
                                                                    {{ $option['id'] == $statusCandidateFilter ?  'selected' : '' }}>
                                                                {{ $option['name'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <select name="filter[except][candidates.recruiter]"
                                                            class="form-control select-grid filter-grid select-search width-160">
                                                        <option value="">&nbsp;</option>
                                                        @foreach ($hrAccounts as $nickname => $email)
                                                            <option value="{{$email}}" {{ $recruiterFilter == $email ? 'selected' :'' }}>{{$email}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </td>
                                        <td></td>
                                    </tr>
                                    @if(isset($collectionModel))
                                        @foreach($collectionModel as $key=>$item)
                                            <?php $statusOption = getOptions::getInstance()->getCandidateResultOptions($item);
                                            foreach ($statusOption as $option) {
                                                if ($option['id'] == getOptions::getInstance()->getSelectedCandidateStatus($item)) {
                                                    $status = $option['name'];
                                                }
                                            }?>
                                            <tr>
                                                <td>{{ $item->fullname }}</td>
                                                <td>{{ $item->email }}</td>
                                                <td>{{ $item->mobile }}</td>
                                                <td>{{ $item->experience }}</td>
                                                <td>{{ $item->programs_name }}</td>
                                                <td>{{ $item->university }}</td>
                                                <td>{{ $item->certificate }}</td>
                                                <td>{{ isset($status) ? $status : '' }}</td>
                                                <td>{{ $item->recruiter }}</td>
                                                <td>
                                                    <a class="btn-edit {{ $item->status == getOptions::CONTACTING ? '' : 'hidden' }}"
                                                       title="{{ trans('resource::view.Candidate.List.Edit') }}"
                                                       href="{{ route('resource::candidate.edit.recommend', ['id' => $item->id]) }}">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                    <a class="btn btn-primary  {{ in_array($item->status, $listFailStatus)  ? '' : 'hidden' }}"
                                                       title="{{ trans('resource::view.Recommend.reapply candidate') }}"
                                                       href="{{ route('resource::candidate.reapply.edit', ['id' => $item->id]) }}">
                                                        <i class="glyphicon glyphicon-refresh"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="7" class="text-center">
                                                <h2 class="no-result-grid">{{ trans('fines_money::view.data_not_found') }}</h2>
                                            </td>
                                        </tr>
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="box-body">
                        @include('team::include.pager')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
