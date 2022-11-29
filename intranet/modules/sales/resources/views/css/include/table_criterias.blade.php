<!------ table project type -------------->
@if(isset($projectType) && count($projectType) > 0)
<table class="table table-hover  tbl-criteria table-fixed table-filter-data" data-id="tcProjectType" >
    <thead>
        <tr>
            <th class="col-xs-1">{{trans('sales::view.No.')}}</th>
            <th class="col-xs-2">{{trans('sales::view.Project type')}}</th>
            <th class="col-xs-2">{{trans('sales::view.Count css')}}</th>
            <th class="col-xs-2">{{trans('sales::view.Avg css')}}</th>
            <th class="col-xs-2">{{trans('sales::view.Max css')}}</th>
            <th class="col-xs-2">{{trans('sales::view.Min css')}}</th>
            <th class="col-xs-1">
                <label class="label-normal">
                    <div class="icheckbox">
                        <input type="checkbox" name="team[4]" id="checkProjectType">
                    </div>
                </label>
            </th>
        </tr>
        {{--
        <tr class="filter-input-grid">
            <th class="col-xs-1 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
            <th class="col-xs-2 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
            <th class="col-xs-2 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
            <th class="col-xs-2 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
            <th class="col-xs-2 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
            <th class="col-xs-2 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
        </tr>
        --}}
    </thead>
    <tbody>
        @foreach($projectType as $item)
        <tr>
            <td class="col-xs-1">{{$item["no"]}}</td>
            <td class="col-xs-2">{{$item["projectTypeName"]}}</td>
            <td class="col-xs-2">{{$item["countCss"]}}</td>
            <td class="col-xs-2">{{$item["avgPoint"]}}</td>
            <td class="col-xs-2">{{$item["maxPoint"]}}</td>
            <td class="col-xs-2">{{$item["minPoint"]}}</td>
            <td class="col-xs-1">
                @if($item["countCss"] > 0)
                <label class="label-normal">
                    <div class="icheckbox">
                        <input type="checkbox" data-id='{{$item["projectTypeId"]}}' class="checkProjectTypeItem">
                    </div>
                </label>
                @else
                -
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table> 
@else
<div class="col-md-12 no-result no-result-tcProjectType"><h3>{{trans('sales::view.No result not found')}}</h3></div>
@endif

<!------ table project name -------------->
@if(isset($projectName) && count($projectName) > 0)
    <table class="table table-hover tbl-criteria table-fixed table-filter-data" data-id="tcProjectName" >
        <thead>
        <tr>
            <th class="col-xs-1">{{trans('sales::view.No.')}}</th>
            <th class="col-xs-3">{{trans('sales::view.Project name')}}</th>
            <th class="col-xs-1">{{trans('sales::view.Count css')}}</th>
            <th class="col-xs-2">{{trans('sales::view.Avg css')}}</th>
            <th class="col-xs-2">{{trans('sales::view.Max css')}}</th>
            <th class="col-xs-2">{{trans('sales::view.Min css')}}</th>
            <th class="col-xs-1">
                <label class="label-normal">
                    <div class="icheckbox">
                        <input type="checkbox" name="team[4]" id="checkProjectName">
                    </div>
                </label>
            </th>
        </tr>
        {{--
        <tr class="filter-input-grid">
            <th class="col-xs-1 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
            <th class="col-xs-2 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
            <th class="col-xs-2 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
            <th class="col-xs-2 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
            <th class="col-xs-2 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
            <th class="col-xs-2 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
        </tr>
        --}}
        </thead>
        <tbody>
        @foreach($projectName as $item)
            <tr>
                <td class="col-xs-1">{{$item["no"]}}</td>
                <td class="col-xs-3">{{$item["name"]}}</td>
                <td class="col-xs-1">{{$item["countCss"]}}</td>
                <td class="col-xs-2">{{$item["avgPoint"]}}</td>
                <td class="col-xs-2">{{$item["maxPoint"]}}</td>
                <td class="col-xs-2">{{$item["minPoint"]}}</td>
                <td class="col-xs-1">
                    @if($item["countCss"] > 0)
                        <label class="label-normal">
                            <div class="icheckbox">
                                <input type="checkbox" data-id='{{$item["name"]}}' class="checkProjectNameItem">
                            </div>
                        </label>
                    @else
                        -
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@else
    <div class="col-md-12 no-result no-result-tcProjectName"><h3>{{trans('sales::view.No result not found')}}</h3></div>
@endif

<!------ table team -------------->
@if(isset($team) && count($team) > 0)
<table class="table table-hover  tbl-criteria table-fixed table-filter-data" data-id="tcTeam">
    <thead>
        <tr>
            <th class="col-xs-1">{{trans('sales::view.No.')}}</th>
            <th class="col-xs-2">{{trans('sales::view.Team')}}</th>
            <th class="col-xs-2">{{trans('sales::view.Count css')}}</th>
            <th class="col-xs-2">{{trans('sales::view.Avg css')}}</th>
            <th class="col-xs-2">{{trans('sales::view.Max css')}}</th>
            <th class="col-xs-2">{{trans('sales::view.Min css')}}</th>
            <th class="col-xs-1">
                <label class="label-normal">
                    <div class="icheckbox">
                        <input type="checkbox" name="team[4]" id="checkTeam">
                    </div>
                </label>
            </th>
        </tr>
        {{--
        <tr class="filter-input-grid">
            <th class="col-xs-1 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
            <th class="col-xs-2 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
            <th class="col-xs-2 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
            <th class="col-xs-2 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
            <th class="col-xs-2 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
            <th class="col-xs-2 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
        </tr>
        --}}
    </thead>
    <tbody>
        @foreach($team as $item)
        <tr>
            <td class="col-xs-1">{{$item["no"]}}</td>
            <td class="col-xs-2">{{$item["teamName"]}}</td>
            <td class="col-xs-2">{{$item["countCss"]}}</td>
            <td class="col-xs-2">{{$item["avgPoint"]}}</td>
            <td class="col-xs-2">{{$item["maxPoint"]}}</td>
            <td class="col-xs-2">{{$item["minPoint"]}}</td>
            <td class="col-xs-1">
                @if($item["countCss"] > 0)
                <label class="label-normal">
                    <div class="icheckbox">
                        <input type="checkbox" data-id='{{$item["teamId"]}}' class="checkTeamItem">
                    </div>
                </label>
                @else
                -
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table> 
@else
<div class="col-md-12 no-result no-result-tcTeam"><h3>{{trans('sales::view.No result not found')}}</h3></div>
@endif

<!------ table PM -------------->
@if(isset($pm) && count($pm) > 0)
<table class="table table-hover  tbl-criteria table-fixed table-filter-data" data-id="tcPm">
    <thead>
        <tr>
            <th class="col-xs-1">{{trans('sales::view.No.')}}</th>
            <th class="col-xs-3">{{trans('sales::view.PM')}}</th>
            <th class="col-xs-1">{{trans('sales::view.Count css')}}</th>
            <th class="col-xs-2">{{trans('sales::view.Avg css')}}</th>
            <th class="col-xs-2">{{trans('sales::view.Max css')}}</th>
            <th class="col-xs-2">{{trans('sales::view.Min css')}}</th>
            <th class="col-xs-1">
                <label class="label-normal">
                    <div class="icheckbox">
                        <input type="checkbox" name="team[4]" id="checkPm">
                    </div>
                </label>
            </th>
        </tr>
        {{--
        <tr class="filter-input-grid">
            <th class="col-xs-1 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
            <th class="col-xs-3 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
            <th class="col-xs-1 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
            <th class="col-xs-2 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
            <th class="col-xs-2 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
            <th class="col-xs-2 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
        </tr>
        --}}
    </thead>
    <tbody>
        @foreach($pm as $item)
        <tr>
            <td class="col-xs-1">{{$item["no"]}}</td>
            <td class="col-xs-3">{{$item["name"]}}</td>
            <td class="col-xs-1">{{$item["countCss"]}}</td>
            <td class="col-xs-2">{{$item["avgPoint"]}}</td>
            <td class="col-xs-2">{{$item["maxPoint"]}}</td>
            <td class="col-xs-2">{{$item["minPoint"]}}</td>
            <td class="col-xs-1">
                @if($item["countCss"] > 0)
                <label class="label-normal">
                    <div class="icheckbox">
                        <input type="checkbox" data-id='{{$item["name"]}}' class="checkPmItem">
                    </div>
                </label>
                @else
                -
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table> 
@else
<div class="col-md-12 no-result no-result-tcPm"><h3>{{trans('sales::view.No result not found')}}</h3></div>
@endif
{{--
<!------ table BrSE -------------->
@if(isset($brse) && count($brse) > 0)
<table class="table table-hover  tbl-criteria table-fixed table-filter-data" data-id="tcBrse">
    <thead>
        <tr>
            <th class="col-xs-1">{{trans('sales::view.No.')}}</th>
            <th class="col-xs-3">{{trans('sales::view.BrSE name')}}</th>
            <th class="col-xs-1">{{trans('sales::view.Count css')}}</th>
            <th class="col-xs-2">{{trans('sales::view.Avg css')}}</th>
            <th class="col-xs-2">{{trans('sales::view.Max css')}}</th>
            <th class="col-xs-2">{{trans('sales::view.Min css')}}</th>
            <th class="col-xs-1">
                <label class="label-normal">
                    <div class="icheckbox">
                        <input type="checkbox" name="team[4]" id="checkBrse">
                    </div>
                </label>
            </th>
        </tr>
        <tr class="filter-input-grid">
            <th class="col-xs-1 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
            <th class="col-xs-3 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
            <th class="col-xs-1 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
            <th class="col-xs-2 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
            <th class="col-xs-2 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
            <th class="col-xs-2 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
        </tr>
    </thead>
    <tbody>
        @foreach($brse as $item)
        <tr>
            <td class="col-xs-1">{{$item["no"]}}</td>
            <td class="col-xs-3 font-japan">{{$item["name"]}}</td>
            <td class="col-xs-1">{{$item["countCss"]}}</td>
            <td class="col-xs-2">{{$item["avgPoint"]}}</td>
            <td class="col-xs-2">{{$item["maxPoint"]}}</td>
            <td class="col-xs-2">{{$item["minPoint"]}}</td>
            <td class="col-xs-1">
                @if($item["countCss"] > 0)
                <label class="label-normal">
                    <div class="icheckbox">
                        <input type="checkbox" data-id='{{$item["name"]}}' class="checkBrseItem">
                    </div>
                </label>
                @else
                -
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table> 
@else
<div class="col-md-12 no-result no-result-tcBrse"><h3>{{trans('sales::view.No result not found')}}</h3></div>
@endif
--}}

<!------ table Customer -------------->
@if(isset($customer) && count($customer) > 0)
<table class="table table-hover  tbl-criteria table-fixed table-filter-data" data-id="tcCustomer">
    <thead>
        <tr>
            <th class="col-xs-1">{{trans('sales::view.No.')}}</th>
            <th class="col-xs-3">{{trans('sales::view.Customer name')}}</th>
            <th class="col-xs-1">{{trans('sales::view.Count css')}}</th>
            <th class="col-xs-2">{{trans('sales::view.Avg css')}}</th>
            <th class="col-xs-2">{{trans('sales::view.Max css')}}</th>
            <th class="col-xs-2">{{trans('sales::view.Min css')}}</th>
            <th class="col-xs-1">
                <label class="label-normal">
                    <div class="icheckbox">
                        <input type="checkbox" name="team[4]" id="checkCustomer">
                    </div>
                </label>
            </th>
        </tr>
        {{--
        <tr class="filter-input-grid">
            <th class="col-xs-1 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
            <th class="col-xs-3 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
            <th class="col-xs-1 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
            <th class="col-xs-2 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
            <th class="col-xs-2 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
            <th class="col-xs-2 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
        </tr>
        --}}
    </thead>
    <tbody>
        @foreach($customer as $item)
        <tr>
            <td class="col-xs-1">{{$item["no"]}}</td>
            <td class="col-xs-3 font-japan">{{$item["name"]}}</td>
            <td class="col-xs-1">{{$item["countCss"]}}</td>
            <td class="col-xs-2">{{$item["avgPoint"]}}</td>
            <td class="col-xs-2">{{$item["maxPoint"]}}</td>
            <td class="col-xs-2">{{$item["minPoint"]}}</td>
            <td class="col-xs-1">
                @if($item["countCss"] > 0)
                <label class="label-normal">
                    <div class="icheckbox">
                        <input type="checkbox" data-id='{{$item["name"]}}' class="checkCustomerItem">
                    </div>
                </label>
                @else
                -
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table> 
@else
<div class="col-md-12 no-result no-result-tcCustomer"><h3>{{trans('sales::view.No result not found')}}</h3></div>
@endif

<!------ table sale -------------->
@if(isset($sale) && count($sale) > 0)
<table class="table table-hover  tbl-criteria table-fixed table-filter-data" data-id="tcSale">
    <thead>
        <tr>
            <th class="col-xs-1">{{trans('sales::view.No.')}}</th>
            <th class="col-xs-3">{{trans('sales::view.Pqa')}}</th>
            <th class="col-xs-1">{{trans('sales::view.Count css')}}</th>
            <th class="col-xs-2">{{trans('sales::view.Avg css')}}</th>
            <th class="col-xs-2">{{trans('sales::view.Max css')}}</th>
            <th class="col-xs-2">{{trans('sales::view.Min css')}}</th>
            <th class="col-xs-1">
                <label class="label-normal">
                    <div class="icheckbox">
                        <input type="checkbox" name="team[4]" id="checkSale">
                    </div>
                </label>
            </th>
        </tr>
        {{--
        <tr class="filter-input-grid">
            <th class="col-xs-1 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
            <th class="col-xs-3 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
            <th class="col-xs-1 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
            <th class="col-xs-2 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
            <th class="col-xs-2 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
            <th class="col-xs-2 td-filter">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid-disable form-control" />
                    </div>
                </div>
            </th>
        </tr>
        --}}
    </thead>
    <tbody>
        @foreach($sale as $item)
        <tr>
            <td class="col-xs-1">{{$item["no"]}}</td>
            <td class="col-xs-3">{{$item["name"]}}</td>
            <td class="col-xs-1">{{$item["countCss"]}}</td>
            <td class="col-xs-2">{{$item["avgPoint"]}}</td>
            <td class="col-xs-2">{{$item["maxPoint"]}}</td>
            <td class="col-xs-2">{{$item["minPoint"]}}</td>
            <td class="col-xs-1">
                @if($item["countCss"] > 0)
                <label class="label-normal">
                    <div class="icheckbox">
                        <input type="checkbox" data-id='{{$item["id"]}}' class="checkSaleItem">
                    </div>
                </label>
                @else
                -
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table> 
@else
<div class="col-md-12 no-result no-result-tcSale"><h3>{{trans('sales::view.No result not found')}}</h3></div>
@endif

