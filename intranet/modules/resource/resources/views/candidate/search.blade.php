<?php
use Rikkei\Resource\Model\Candidate;
use Rikkei\Core\View\CoreUrl;
?>

@extends('layouts.default')

@section('title')
    {{ trans('resource::view.Candidate search advance') }}
@endsection

@section('content')
<div class="row list-css-page">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header">
                @if ($permissExport)
                <div class="actions-box">
                    <button id="btn_export_search" class="btn btn-success filter" type="button"
                            data-type="export">
                        {{ trans('resource::view.Export') }} 
                        <i class="fa fa-spin fa-refresh hidden"></i>
                    </button>
                </div>
                @endif
                <div class="row form-horizontal">
                    <div class="col-sm-6">
                        <label class="control-label">{{ trans('resource::view.Selected column') }}</label>
                        <select class="form-control column-choice" multiple="multiple">
                            @foreach ($columns as $column)
                            <option value="{{ $column['data'] }}" data-field="{{ $column['field'] }}" 
                                    @if ($column['data'] == 'id') disabled @endif
                                    @if (in_array($column['field'], Candidate::getFieldOfColumnsSelected($columnsSelected))) selected @endif>{{ $column['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 filter-box">
                        <label class="control-label">{{ trans('resource::view.Selected column') }}</label>
                        @include('resource::candidate.include.box_search_advance')
                    </div>
                </div>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="edit-table table table-striped table-bordered table-condensed" cellspacing="0" width="100%" id="search-advance">
                        <thead>
                            <tr>
                                @foreach ($columnsSelected as $column)
                                <th>{{ $column['label'] }}</th>
                                @endforeach
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
<!-- Styles -->
@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">    
<link href="{{ asset('resource/css/candidate/search.css') }}" rel="stylesheet" type="text/css" >
@endsection

<!-- Script -->
@section('script')
<script src="https://cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/1.3.3/FileSaver.min.js"></script>
<script type="text/javascript" src="{{ asset('lib/xlsx/jszip.js') }}"></script>
<script type="text/javascript" src="{{ asset('lib/xlsx/xlsx.js') }}"></script>
<script type="text/javascript">
    var urlAdvanceSearch = '{{ route("resource::candidate.searchAdvance") }}';
    var nullCompareValue = '{{ Candidate::COMPARE_IS_NULL }}';
    var notNullCompareValue = '{{ Candidate::COMPARE_IS_NOT_NULL }}';
    var likeCompareValue = '{{ Candidate::COMPARE_LIKE }}';
    var greaterCompareValue = '{{ Candidate::COMPARE_GREATER }}';
    var smallerComparevalue = '{{ Candidate::COMPARE_SMALLER }}';
    var greaterEqualCompareValue = '{{ Candidate::COMPARE_GREATER_EQUAL }}';
    var smallerEqualComparevalue = '{{ Candidate::COMPARE_SMALLER_EQUAL }}';
    var dataLang = {
        'sZeroRecords': '{{ trans('welfare::view.sZeroRecords') }}',
        'sInfo': '{{ trans('welfare::view.sInfo') }}',
        'sInfoEmpty': '{{ trans('welfare::view.sInfoEmpty') }}',
    };
    //Store languages with level
    var langArray = <?php echo json_encode($langArray); ?>;
    var columns = [];
    @foreach ($columnsSelected as $column)
    columns.push({data: '{{ $column["data"] }}', name: '{{ $column["data"] }}'});
    @endforeach
</script>
<script src="{{ CoreUrl::asset('resource/js/candidate/search.js') }}"></script>
@endsection
