@extends('layouts.default')

@section('title')
    {{ $titleHeadPage }}
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
@endsection
<?php
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Team\View\Config;
use Carbon\Carbon;
use Rikkei\Core\Model\User;
use Rikkei\Education\Model\EducationTeacher;
?>
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-info">
                <div class="box-body">
                    <a class="btn btn-primary btn-create" href="{{ URL::route('education::education.teaching.teachings.create') }}">
{{--                        {{ trans('education::view.Add') }}--}}
                        Đăng ký
                        <i class="fa fa-spin fa-refresh hidden"></i>
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                        <thead>
                            <tr>
                                <th class="col-title" style="width: 100px;">{{ trans('education::view.Title') }}</th>
                                <th class="col-time text-center" style="width: 100px;">{{ trans('education::view.Time') }}</th>
                                <th class="col-time-hours text-center" style="width: 100px;">{{ trans('education::view.Number of hours taught') }}</th>
                                <th class="col-scope text-center" style="width: 150px;">{{ trans('education::view.Scope') }}</th>
{{--                                <th class="col-type text-center" style="width: 150px;">{{ trans('education::view.Registration type') }}</th>--}}
                                <th class="col-course text-center" style="width: 120px;">{{ trans('education::view.Course') }}</th>
                                <th class="col-status text-center" style="width: 150px;">{{ trans('education::view.Status') }}</th>
                                <th class="col-traning text-center" style="width: 150px;">{{ trans('education::view.Training in charge') }}</th>
                                <th class="col-action"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="filter-input-grid">
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
{{--                                <td>--}}
{{--                                    <div class="row">--}}
{{--                                        <div class="col-md-12">--}}
{{--                                            <select name="filter[search][type]" class="form-control select-grid filter-grid select-search">--}}
{{--                                                <option value="">All</option>--}}
{{--                                                @foreach($registerType as $key => $value)--}}
{{--                                                    <option value="{{ $key }}" {{ CoreForm::getFilterData('search', 'type') == $key ? 'selected' : '' }}>{{ trans($value) }}</option>--}}
{{--                                                @endforeach--}}
{{--                                            </select>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </td>--}}
                                <td>&nbsp;</td>
                                <td>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <select name="filter[search][status]" class="form-control select-grid filter-grid select-search">
                                                <option value="">All</option>
                                                @foreach($status as $key => $value)
                                                    <option value="{{ $key }}" {{ CoreForm::getFilterData('search', 'status') == $key ? 'selected' : '' }}>{{ trans($value) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </td>
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
                                @foreach($collectionModel as $item)
                                    <tr>
                                        <td>{{ $item->title }}</td>
                                        <td class="text-center">{{ Carbon::parse($item->created_at)->format('d-m-Y') }}</td>
                                        <td class="text-center">{{ $item->tranning_hour }}</td>
                                        <td class="text-center">
                                            @if($item->scope == EducationTeacher::SCOPE_COMPANY)
                                                {{ trans('education::view.Company') }}
                                            @elseif($item->scope == EducationTeacher::SCOPE_DIVISION)
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
                                            @if($item->status == EducationTeacher::STATUS_NEW || $item->status == EducationTeacher::STATUS_UPDATE)
{{--                                                <a class="btn btn-primary"  href="{{ route('education::education.teaching.teachings.show',['id' => $item->id]) }}">{{ trans('education::view.Update') }}</a>--}}
                                                <a class="btn btn-primary"  href="{{ route('education::education.teaching.teachings.send',['id' => $item->id]) }}">{{ trans('education::view.Send') }}</a>
                                            @endif
                                            @if($item->status == EducationTeacher::STATUS_REJECT)
                                                <button class="btn btn-primary show-warning warn-confirm" data-noti="{{ $item->reject ?  $item->reject : 'Not Item' }}" >{{ trans('education::view.See reason') }}</button>
                                                <a class="btn btn-primary"  href="{{ route('education::education.teaching.teachings.show',['id' => $item->id]) }}">{{ trans('education::view.Update') }}</a>
                                            @endif
                                        </td>
                                    </tr>
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
    <script>
        $(document).ready(function () {
            setTimeout(function () {
                $('.flash-message').remove();
            }, 2000);
        });
    </script>
@endsection
