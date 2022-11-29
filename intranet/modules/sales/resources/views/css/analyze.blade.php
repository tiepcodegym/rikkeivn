@extends('layouts.default')

@section('title')
    {{ trans('sales::view.Analyze.Title') }}
@endsection

@section('content')

<div class=" analyze-body">
    <div class="box box-primary">
        <div class="row">
            <div class="col-md-3 ">
                <div class="box-header with-border">
                    <h3 class="box-title with-border">{{trans("sales::view.Analyze step 1")}}</h3>
                </div>
                <div class="box-body border-bottom-mobile">
                    <div class="team-container">
                        <ul class="list-team-tree">
                            {!! $htmlTeam !!}
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-9 ">
                <div class="box-header with-border">
                    <h3 class="box-title with-border">{{trans("sales::view.Analyze step 2")}}</h3>
                </div>
                <div class="box-body border-bottom-mobile">
                    <div class="row">
                        <div class="col-md-7 range-date-container">
                            <div class="col-md-4 project-date-label padding-left-0"><strong>{{trans('sales::view.Project date finish')}}</strong></div>
                            <div class="col-md-7">
                                {{trans('sales::view.From date2')}}<input type='text' class="form-control date" id="start_date" name="start_date" data-provide="datepicker" placeholder="YYYY-MM-DD"  value="{{ $startDateDefault }}" autocomplete="off"/>
                                {{trans('sales::view.To date2')}}<input type='text' class="form-control date" id="end_date" name="end_date" data-provide="datepicker" placeholder="YYYY-MM-DD"  value="{{ $endDateDefault }}" autocomplete="off"/>
                            </div>
                        </div>
                        <div class="col-md-5 project-type-container">
                            <label class="title-label">{{trans('sales::view.Project type')}}</label>
                            @foreach($projectType as $type)
                            <label class="icheckbox-container label-normal">
                                <div class="icheckbox">
                                    <input type="checkbox" name="project_type" value="{{$type->id}} ">&nbsp;&nbsp;{{ $type->name}}
                                </div>
                            </label>
                           @endforeach
                        </div>
                        <div class="col-xs-12 text-align-center">
                            <button class="btn btn-primary btn-filter" onclick="filterAnalyze('{{ Session::token() }}');">{{trans('sales::view.Button filter text')}}</button>
                        </div>
                    </div>
                </div>
                
                <div class="box-header with-border">
                    <h3 class="box-title with-border">{{trans("sales::view.Analyze step 3")}}</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12" style="border-right: 1px solid #f4f4f4;">
                            <div class="iradio-container">
                                <input type="radio" name="tieuchi" id="tcProjectType"  checked>
                                <label class="label-normal" for="tcProjectType">{{trans("sales::view.By project type")}}</label>
                            </div>
                            <div class="iradio-container">
                                <input type="radio" name="tieuchi" id="tcProjectName" >
                                <label class="label-normal" for="tcProjectName">{{trans("sales::view.By project name")}}</label>
                            </div>
                            <div class="iradio-container">
                                <input type="radio" name="tieuchi" id="tcTeam" >
                                <label class="label-normal" for="tcTeam">{{trans("sales::view.By team")}}</label>
                            </div>
                            <div class="iradio-container">
                                <input type="radio" name="tieuchi" id="tcPm" >
                                <label class="label-normal" for="tcPm">{{trans("sales::view.By pm")}}</label>
                            </div>
                            
                            <div class="iradio-container">
                                <input type="radio" name="tieuchi" id="tcCustomer" >
                                <label class="label-normal" for="tcCustomer">{{trans("sales::view.By customer")}}</label>
                            </div>
                            <div class="iradio-container">
                                <input type="radio" name="tieuchi" id="tcSale" >
                                <label class="label-normal" for="tcSale">{{trans("sales::view.By PQA")}}</label>
                            </div>
                            <div class="iradio-container">
                                <input type="radio" name="tieuchi" id="tcQuestion" >
                                <label class="label-normal" for="tcQuestion">{{trans("sales::view.By question")}}</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row>">
                    <div  class="theotieuchi table-responsive"></div>
                    
                    <div class="col-xs-12 text-align-center margin-top-30 margin-bottom-30" >
                          <button class="btn btn-danger btn-apply apply-analyze-b1" onclick="apply('{{ Session::token() }}');">Apply</button>
                    </div>
                </div>
            </div>
        </div>
    </div> 
    
    <!-- RESULTS -->
    <div class="ketquaapply">
        <div class="content-header padding-left-0 margin-bottom-15">
            <h1>{{ trans('sales::view.Analyze.Result title')}}</h1>
        </div>
        <!----------- Bảng danh sách du an ------------------->
        <div class="box box-primary">        
            <div class="box-header">
                <h3 class="box-title">{{trans("sales::view.Project list")}}</h3>
                <?php $dataTableFilter = 'table-filter-list-project'; ?>
                @include('core::include.filter_ajax')
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover dataTable ketqua table-filter-list-project" id='danhsachduan'>
                            <thead>
                                <tr>
                                    <th onclick="sortProject(this,'{{ Session::token() }}');" class="col-xs-1 sorting" data-sort-type="resultId" aria-type="asc">{{trans('sales::view.Id')}}</th>
                                    <th onclick="sortProject(this,'{{ Session::token() }}');" data-sort-type="projectName" aria-type="asc" class="sorting">{{trans('sales::view.Project name')}}</th>
                                    <th onclick="sortProject(this,'{{ Session::token() }}');" data-sort-type="team" aria-type="asc" class="sorting">{{trans('sales::view.Team')}}</th>
                                    <th onclick="sortProject(this,'{{ Session::token() }}');" data-sort-type="pm" aria-type="asc" class="sorting">{{trans('sales::view.PM')}}</th>
                                    <th onclick="sortProject(this,'{{ Session::token() }}');" data-sort-type="projectDate" aria-type="desc" class="sorting_desc">{{trans('sales::view.Project date finish')}}</th>
                                    <th onclick="sortProject(this,'{{ Session::token() }}');" data-sort-type="makeDate" aria-type="asc" class="sorting">{{trans('sales::view.Analyze.Make date CSS')}}</th>
                                    <th onclick="sortProject(this,'{{ Session::token() }}');" data-sort-type="projectPoint" aria-type="asc" class="sorting">{{trans('sales::view.Analyze.CSS point')}}</th>
                                </tr>
                                
                                <tr class="filter-input-grid">
                                    <th class="col-xs-1">&nbsp;</th>
                                    <th class="col-xs-2 td-filter">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" name="project_name" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-ajax form-control" />
                                            </div>
                                        </div>
                                    </th>
                                    <th class="col-xs-3 td-filter">
                                        
                                    </th>
                                    <th class="col-xs-1 td-filter">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" name="pm_name" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-ajax form-control" />
                                            </div>
                                        </div>
                                    </th>
                                    <th class="col-xs-3 td-filter">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" name="end_date" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-ajax form-control" />
                                            </div>
                                        </div>
                                    </th>
                                    <th class="col-xs-1 td-filter">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" name="created_at[date]" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-ajax form-control" />
                                            </div>
                                        </div>
                                    </th>
                                    
                                    <th class="col-xs-1 td-filter">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" name="result_point" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-ajax form-control" />
                                            </div>
                                        </div>
                                    </th>
                                </tr>
                                
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                        <div class="dataTables_paginate paging_simple_numbers">
                            <ul class="pagination"></ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        
        <!----------- Show chart ------------------->  
        <div class="box box-primary">   
            <div class="box-header with-border">
                <h3 class="box-title">{{trans("sales::view.Chart title")}}</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="small-title">{{trans('sales::view.Chart title by all')}}</h4>
                        <div id="chartAll" style="min-width: 210px; height:400px;  margin: 0 auto"></div>
                        <div id="legend-container-all"></div>
                    </div>
                    <div class="col-md-6">
                        <h4 class="small-title">{{trans('sales::view.Chart title by filter')}}</h4>
                        <div id="chartFilter" style="min-width: 210px; height:400px; margin: 0 auto"></div>
                        <div id="legend-container" class="font-japan"></div>
                    </div>
                    <div class="col-md-6">
                        <div class="chartPoint">
                            <canvas id="chartPoint" style="margin-top: 5rem"></canvas>
                        </div>
                        <div id="legend-container-point" class="font-japan"></div>
                    </div>
                </div>
            </div>
            
        </div>   
        
        
        <!----------- Question combobox ------------------->  
        <div class="box box-primary box-select-question">
            <div class="row">
                <div class="col-md-12">
                    <div class="box-header">
                        <h3 class="box-title">{{trans('sales::view.Question choose')}}</h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <select class="form-control" id="question-choose"></select>
                        </div>
                    </div>
                </div>  
            </div>
        </div>
        
        
        <!----------- Bảng danh sách dưới 3 sao ------------------->   
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title">{{trans('sales::view.List 3 * below')}}</h3>
                <?php $dataTableFilter = 'table-filter-lt-3-star'; ?>
                @include('core::include.filter_ajax')
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover dataTable duoi3sao table-filter-lt-3-star" id='duoi3sao'>
                            <thead>
                                <tr>
                                    <th class="col-xs-1">{{trans('sales::view.No.')}}</th>
                                    <th class="col-xs-2 sorting" onclick="sortLess3Star(this,'{{ Session::token() }}');" data-sort-type="projectName" aria-type="asc" data-type="all">{{trans('sales::view.Project name')}}</th>
                                    <th class="col-xs-3 sorting" onclick="sortLess3Star(this,'{{ Session::token() }}');" data-sort-type="questionName" aria-type="asc" data-type="all">{{trans('sales::view.Criteria name')}}</th>
                                    <th class="col-xs-1 sorting" onclick="sortLess3Star(this,'{{ Session::token() }}');" data-sort-type="questionPoint" aria-type="asc" data-type="all">{{trans('sales::view.Point *')}}</th>
                                    <th class="col-xs-3 sorting" onclick="sortLess3Star(this,'{{ Session::token() }}');" data-sort-type="customerComment" aria-type="asc" data-type="all">{{trans('sales::view.Customer comment')}}</th>
                                    <th class="col-xs-1 sorting_desc" onclick="sortLess3Star(this,'{{ Session::token() }}');" data-sort-type="makeDate" aria-type="desc" data-type="all">{{trans('sales::view.Analyze.Make date CSS')}}</th>
                                    <th class="col-xs-1 sorting" onclick="sortLess3Star(this,'{{ Session::token() }}');" data-sort-type="projectPoint" aria-type="asc" data-type="all">{{trans('sales::view.Analyze.CSS point')}}</th>
                                </tr>
                                
                                <tr class="filter-input-grid">
                                    <th class="col-xs-1">&nbsp;</th>
                                    <th class="col-xs-2 td-filter">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" name="project_name" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-ajax form-control" />
                                            </div>
                                        </div>
                                    </th>
                                    <th class="col-xs-3 td-filter">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" name="content" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-ajax form-control" />
                                            </div>
                                        </div>
                                    </th>
                                    <th class="col-xs-1 td-filter">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" name="point" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-ajax form-control" />
                                            </div>
                                        </div>
                                    </th>
                                    <th class="col-xs-3 td-filter">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" name="comment" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-ajax form-control" />
                                            </div>
                                        </div>
                                    </th>
                                    <th class="col-xs-1 td-filter">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" name="created_at[date]" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-ajax form-control" />
                                            </div>
                                        </div>
                                    </th>
                                    
                                    <th class="col-xs-1 td-filter">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" name="avg_point" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-ajax form-control" />
                                            </div>
                                        </div>
                                    </th>
                                </tr>
                                
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                        <div class="dataTables_paginate paging_simple_numbers">
                            <ul class="pagination"></ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        
        <!----------- Bảng danh sách de xuat cua khach hang ------------------->
        <div class="box box-primary">    
            <div class="box-header">
               <h3 class="box-title">{{trans('sales::view.Analyze.Customer propose')}}</h3>
               <?php $dataTableFilter = 'table-filter-customer-suggest'; ?>
                @include('core::include.filter_ajax')
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover dataTable ketqua table-filter-customer-suggest" id="danhsachdexuat">
                            <thead>
                                <tr>
                                    <th class="col-xs-1">{{trans('sales::view.No.')}}</th>
                                    <th class="col-xs-3 sorting" onclick="sortProposed(this,'{{ Session::token() }}');" data-sort-type="projectName" aria-type="asc" class="sorting" data-type="all">{{trans('sales::view.Project name')}}</th>
                                    <th class="col-xs-5 sorting" onclick="sortProposed(this,'{{ Session::token() }}');" data-sort-type="proposed" aria-type="asc" class="sorting" data-type="all">{{trans('sales::view.Analyze.Customer propose')}}</th>
                                    <th class="col-xs-2 sorting_desc" onclick="sortProposed(this,'{{ Session::token() }}');" data-sort-type="makeDate" aria-type="desc" class="sorting_asc" data-type="all">{{trans('sales::view.Analyze.Make date CSS')}}</th>
                                    <th class="col-xs-1 sorting" onclick="sortProposed(this,'{{ Session::token() }}');" data-sort-type="projectPoint" aria-type="asc" class="sorting" data-type="all">{{trans('sales::view.Analyze.CSS point')}}</th>
                                </tr>
                                
                                <tr class="filter-input-grid">
                                    <th class="col-xs-1">&nbsp;</th>
                                    <th class="col-xs-2 td-filter">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" name="project_name" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-ajax form-control" />
                                            </div>
                                        </div>
                                    </th>
                                    <th class="col-xs-3 td-filter">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" name="proposed" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-ajax form-control" />
                                            </div>
                                        </div>
                                    </th>
                                    <th class="col-xs-1 td-filter">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" name="result_make[date]" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-ajax form-control" />
                                            </div>
                                        </div>
                                    </th>
                                    
                                    <th class="col-xs-1 td-filter">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" name="result_point" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-ajax form-control" />
                                            </div>
                                        </div>
                                    </th>
                                </tr>
                                
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                        <div class="dataTables_paginate paging_simple_numbers">
                            <ul class="pagination"></ul>
                        </div>
                    </div>
                </div>
                
           </div>
        </div>
    </div>
</div>

<div class="modal modal-warning" id="modal-warning" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ trans('sales::view.Notification') }}</h4>
            </div>
            <div class="modal-body">
                <p>One fine body…</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline pull-right" data-dismiss="modal">{{ trans('sales::view.Close') }}</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
    <input type="hidden" id="startDate_val" value="" />
    <input type="hidden" id="endDate_val" value="" />
    <input type="hidden" id="criteriaIds_val" value="" />
    <input type="hidden" id="teamIds_val" value="" />
    <input type="hidden" id="projectTypeIds_val" value="" />
    <input type="hidden" id="criteriaType_val" value="" />
    <div class="modal apply-click-modal"><img class="loading-img" src="{{ asset('sales/images/loading.gif') }}" /></div>
    <input type="hidden" id="cssResultIds" value="" />
@endsection

<!-- Styles -->
@section('css')
<link href="{{ asset('sales/css/sales.css') }}" rel="stylesheet" type="text/css" >
<link href="{{ asset('lib/rangeSlider/css/iThing.css') }}" rel="stylesheet" type="text/css" >
<link href="{{ asset('lib/rangeSlider/demo/rangeSlider.css') }}" rel="stylesheet" type="text/css" >
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/minimal/_all.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" />
@endsection

<!-- Script -->
@section('script')
<script>
    var reProjectAndTeam = '{{ trans('sales::view.Project category and team are empty') }}';
    var reTeam = '{{ trans('sales::view.Team is empty') }}';
    var reTypeProject = '{{ trans('sales::view.Project type is empty') }}';
    var reCriteria = '{{ trans('sales::view.At least one of the criteria must be selected.') }}';
</script>
<script src="{{ asset('lib/rangeSlider/lib/jquery.mousewheel.min.js') }}"></script>
<script src="{{ asset('lib/rangeSlider/jQAllRangeSliders-min.js') }}"></script>
<script src="{{ asset('lib/rangeSlider/demo/sliderDemo.js') }}"></script>
<script src="{{ asset('lib/rangeSlider/demo/dateSliderDemo.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/icheck.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flot/0.8.3/jquery.flot.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flot/0.8.3/jquery.flot.time.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>
<script src="{{ asset('sales/js/css/analyze.js') }}"></script>
<script src="{{ asset('sales/js/css/dataTables.js') }}"></script>
@endsection
