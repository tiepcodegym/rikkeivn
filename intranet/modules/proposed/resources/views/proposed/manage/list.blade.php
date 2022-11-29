@extends('layouts.default')

<?php
    use Carbon\Carbon;
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Core\View\View as CoreView;
    use Rikkei\Core\View\Form as CoreForm;
    use Rikkei\Proposed\Model\Proposed;
    use Rikkei\Proposed\Model\ProposedCategory;
    use Rikkei\ManageTime\View\ManageTimeCommon;

    $arrStatus = Proposed::getStatus();
    $arrFeedback = Proposed::getFeedback();
    $arrLevelRecognition = Proposed::getLevelRecognition();
    $listCategories = ProposedCategory::getlistProCategoies();
    $teamsOptionAll = \Rikkei\Team\View\TeamList::toOption(null, true, false);
?>
@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('common/css/style.css') }}" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_managetime/css/reason_list.css') }}">
    <link rel="stylesheet" href="{{ CoreUrl::asset('team/css/style.css') }}" />
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-2">
            @include('proposed::nav_left')
        </div>
        <div class="col-sm-10">
            @include('proposed::message')
            <div class="box box-info">
                <div class="box-body">
                    <div class="col-sm-12">
                        <div class="box-header">
                            <div class="team-select-box">
                                <label for="select-team-member">{{ trans('team::view.Choose team') }}
                                    <span class="fa fa-question-circle" data-toggle="tooltip" title="{{ trans('proposed::view.Tooltip list propose by team') }}"></span>
                                </label>
                                 <div class="input-box">
                                    <select name="team_all" id="select-team-member" class="form-control select-search input-select-team-member" autocomplete="off" style="width: 100%;">
                                        <option value="{{URL::route('proposed::manage-proposed.index')}}">&nbsp;</option>
                                        @foreach($teamsOptionAll as $option)
                                           <option value="{{URL::route('proposed::manage-proposed.index', $option['value'])}}"
                                            @if($option['value'] == $teamIdsAvailable) selected @endif
                                           >
                                            {{ $option['label'] }}
                                           </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="pull-right">
                                @include('team::include.filter')
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped dataTable table-bordered table-hover table-grid-data" id="table">
                        <thead>
                            <tr>
                                <th class="col-id width-10 text-center">{{ trans('manage_time::view.No.') }}</th>
                                <th class="maxw-300 text-center">{{ trans('proposed::view.Content proposed') }}</th>
                                {{-- <th class="">{{ trans('proposed::view.Name category') }}</th> --}}
                                <th class="text-center">{{ trans('proposed::view.Proposer') }}</th>
                                <th class="width-150 text-center">{{ trans('proposed::view.CreatedAt') }}</th>
                                <th class="width-150 text-center">{{ trans('proposed::view.Status') }}</th>
                                <th class="width-150 text-center">{{ trans('proposed::view.Level of recognition') }}</th>
                                <th class="width-150 text-center">{{ trans('proposed::view.Feedback') }}</th>
                                <th class="managetime-col-85 width-150 text-center"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="filter-input-grid">
                                <td>&nbsp;</td>
                                <td>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" name="filter[proposes.proposed_content]" value="{{ CoreForm::getFilterData('proposes.proposed_content') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                        </div>
                                    </div>
                                </td>
                                {{-- <td>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <select name="filter[proCat.id]"  class="form-control select-grid filter-grid select-search">
                                                <option value="">&nbsp;</option>
                                                @foreach($listCategories as $key => $listCategory)
                                                    <option value="{{ $listCategory->id }}"
                                                @if ($listCategory->id == CoreForm::getFilterData('proCat.id'))
                                                    selected
                                                @endif
                                                    >{{ $listCategory->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </td> --}}
                                 <td>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" name="filter[empCreate.name]" value="{{ CoreForm::getFilterData('empCreate.name') }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                        </div>
                                    </div>
                                </td>
                                <td>&nbsp;</td>
                                <td>
                                    <select name="filter[proposes.status]" class="form-control select-grid filter-grid select-search" style="width: 100%">
                                        <option value="">&nbsp;</option>
                                        @foreach($arrStatus as $key => $status)
                                            <option value="{{ $key }}"
                                            @if ($key == CoreForm::getFilterData('proposes.status'))
                                                selected
                                            @endif
                                            >{{ $status }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                 <td>
                                    <select name="filter[proposes.level]" class="form-control select-grid filter-grid select-search" style="width: 100%">
                                        <option value="">&nbsp;</option>
                                        @foreach($arrLevelRecognition as $key => $level)
                                            <option value="{{ $key }}"
                                            @if ($key == CoreForm::getFilterData('proposes.level'))
                                                selected
                                            @endif
                                            >{{ $level }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="filter[proposes.feedback]" class="form-control select-grid filter-grid select-search" style="width: 100%">
                                        <option value="">&nbsp;</option>
                                        @foreach($arrFeedback as $key => $feedback)
                                            <option value="{{ $key }}"
                                            @if ($key == CoreForm::getFilterData('proposes.feedback'))
                                                selected
                                            @endif
                                            >{{ $feedback }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>&nbsp;</td>
                            </tr>
                            @if(isset($collectionModel) && count($collectionModel))
                                <?php $i = CoreView::getNoStartGrid($collectionModel); ?>
                                @foreach($collectionModel as $item)
                                    <tr>
                                        <td>{{ $i++ }}</td>
                                        <td class="maxw-300">{{ str_limit($item->proposed_content, 180) }}</td>
                                        <td>{{ $item->nameEmpCreate }}</td>
                                        <td class="text-center">{{ Carbon::parse($item->created_at)->format('d-m-Y')}}</td>
                                        <td>{{ $arrStatus[$item->status] }}</td>
                                        <td>{{ $arrLevelRecognition[$item->level]}}</td>
                                        <td>{{ $arrFeedback[$item->feedback]}}</td>
                                        <td class="align-center td-button">
                                            <a class="btn btn-edit edit-row" href="{{URL::route('proposed::manage-proposed.edit', $item->id)}}" role="button" title="{{ trans('proposed::view.Answered proposed')}}">
                                                <i class="fa fa-pencil-square-o" aria-hidden="true" ></i>
                                            </a>
                                            <form class="form-delete" action="{{URL::route('proposed::manage-proposed.delete', $item->id)}}" method="POST">
                                                {{ csrf_field() }}
                                                <button class="btn-delete delete-confirm" type="submit"><i class="fa fa-remove" aria-hidden="true"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <h2 class="no-result-grid">{{ trans('proposed::view.No results found') }}</h2>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script>
        $('.input-select-team-member').on('change', function(event) {
            value = $(this).val();
            window.location.href = value;
        });
    </script>
    <script src="{{ CoreUrl::asset('team/js/xlsx-func.js') }}"></script>
    <script src="{{ CoreUrl::asset('team/js/script.js') }}"></script>
@endsection

