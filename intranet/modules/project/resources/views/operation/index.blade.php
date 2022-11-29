<?php

use Carbon\Carbon;
use Rikkei\Core\View\CookieCore;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Team\Model\Team;

$startDateFilter = Carbon::now()->startOfyear()->format('Y-m');
$endDateFilter = Carbon::now()->endOfYear()->format('Y-m');
$teamId = $teamIdCurrent;
$dataCookie = CookieCore::getRaw('filter.project.' . $typeViewMain);
$teamPath = Team::getTeamPathTree();
if ($dataCookie) {
    $startDateFilter = isset($dataCookie['monthFrom']) ? $dataCookie['monthFrom'] : $startDateFilter;
    $endDateFilter = isset($dataCookie['monthTo']) ? $dataCookie['monthTo'] : $endDateFilter;
    $teamId = isset($dataCookie['teamId']) ? $dataCookie['teamId'] : $teamId;
}

$currentMonth = Carbon::now()->format('Y-m');
$curUrl = url()->full();
?>
@extends('layouts.default')
@section('title', 'Operation')
@section('css')

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/qtip2/3.0.3/jquery.qtip.min.css" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" >
  {{--<link rel="stylesheet" href="{{ URL::asset('project/css/edit.css') }}" />--}}
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
  {{--<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />--}}
  @if($typeViewMain == 'projects')
      <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
  @endif
  <link rel="stylesheet" type="text/css" href="{{ CoreUrl::asset('project/css/operation.css') }}">
  <style>
    .nav-tabs-custom>.nav-tabs>li>a:hover, .operation-tab .nav>li>a:focus {
        color: #999;
        font-weight: normal;
        background: #fff;
    }
    .nav-tabs-custom>.nav-tabs>li.active {
        border-top-color: #3c8dbc !important;
    }
  </style>
@endsection
@section('content')
  <div class="row">
    <div class="col-xs-12 alert-message hidden">
      <div class="alert alert-success alert-dismissible">
        <strong>Success!</strong> {{ trans('project::view.Create project success') }}
      </div>
    </div>
    <div class="col-xs-12 alert-message-delete hidden">
      <div class="alert alert-success alert-dismissible">
        <strong>Success!</strong> {{ trans('project::view.Delete operation success') }}
      </div>
    </div>
    <div class="col-xs-12 alert-message-error hidden">
      <div class="alert alert-danger alert-dismissible">
        <strong>Error!</strong><span class="str-error-message"></span>
      </div>
    </div>
    <div class="col-xs-12">
      <div class="nav-tabs-custom tab-danger tab-keep-status operation-tab" data-type="workorder">
        <ul class="nav nav-tabs">
          @if ($checkPerOverview)
            <li class="{{ $curUrl == route('project::operation.overview') ? 'active' : '' }}">
              <a href="{{ route('project::operation.overview') }}">Overview</a>
            </li>
          @endif
          @if ($checkPerMember)
            <li class="{{ $curUrl == route('project::operation.members') ? 'active' : '' }}">
              <a href="{{ route('project::operation.members') }}">Members Report</a>
            </li>
          @endif
          @if ($checkPerProject)
            <li class="{{ $curUrl == route('project::operation.projects') ? 'active' : '' }}">
              <a href="{{ route('project::operation.projects') }}">Projects Report</a>
            </li>
          @endif
        </ul>

        <!-- Tab panes -->
        <div class="wo-tab-content tab-content">
          @if (view()->exists('project::operation.includes.' . $typeViewMain))
            @include('project::operation.includes.' . $typeViewMain)
          @else
            @include('project::operation.includes.overview')
          @endif
        </div>
      </div>
    </div>
  </div>
@endsection

@section('script')
  <script>
      var globalTypeViewMain = '{{$typeViewMain}}';
      var globalGetOperationUrl = '{{ route("project::operation.getOperationReport") }}';
      var gloabalStartDateFilter = '{{$startDateFilter}}';
      var gloabalEndDateFilter = '{{$endDateFilter}}';
      var gloabalTeamId = '{{$teamId}}';
      var globalCurrentMonth = '{{$currentMonth}}';
      var globalMessage = {
          'Montherror': "{{trans('project::me.Montherror')}}",
          'Error System': "{{ trans('core::view.Error system') }}",
          'No results found': "{{ trans('project::view.No results found') }}",
          'Input all': "{{ trans('project::message.Input all') }}"
      };
      var globalProjectUrl = "{{route('project::project.edit', ['id' => 'id'])}}";
      var globalHeader = {
          'Reward Total': "{{ trans('project::view.Reward Total') }}",
          'No': "{{ trans('project::view.No') }}",
          'Member': "{{ trans('project::view.Member') }}",
          'Account Name': "{{ trans('project::view.Account Name') }}",
          'Point': "{{ trans('project::view.Point') }}",
          'Month': "{{ trans('project::view.Month') }}",
          'Number of human actual': "{{ trans('project::view.Number of human actual') }}",
          'Work effort': "{{ trans('project::view.Work effort') }}",
          'OSDC': "{{ trans('project::view.OSDC') }}",
          'Onsite': "{{ trans('project::view.Onsite') }}",
          'Project Base': "{{ trans('project::view.Project Base') }}",
          'Busy rate': "{{ trans('project::view.Busy rate') }}",
          'Approved production cost': "{{trans('project::view.Approved production cost')}}",
          'Note' : "{{trans('project::view.Note')}}",
          'Delete': "{{ trans('sales::view.Delete') }}"
      };
      var globalPassModule = {
          teamPath: JSON.parse('{!! json_encode($teamPath) !!}'),
          teamSelected: JSON.parse('{!! json_encode(is_array($teamId) ? $teamId : [$teamId]) !!}')
      };
      var globaleTeamPQA = JSON.parse('{!! json_encode($teamPQAIds) !!}');

      $(document).ready(function () {
          removeUselessTeam('.input-select-team-member');
      });

      function removeUselessTeam(selectorSelect) {
          if ( $(selectorSelect).length) {
              var teamDevOption = RKfuncion.teamTree.init(globalPassModule.teamPath, globalPassModule.teamSelected);
              var idTeamAvailable = [];
              $.each(teamDevOption, function(i,v) {
                  idTeamAvailable.push(v.id);
              });
              $(selectorSelect + " > option").each(function() {
                  let conditionNoInTeamAvailable = $.inArray(+this.value, idTeamAvailable) < 0;
                  let conditionInTeamPQA = $.inArray(this.value, globaleTeamPQA) >= 0;
                  if (conditionNoInTeamAvailable || conditionInTeamPQA) {
                      $(this).remove();
                  } else {
                      $(this).prop('disabled', false);
                      $(this).removeClass('style-disabled');
                  }
              });

          }
      }
  </script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.15.1/moment.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.bundle.min.js"></script>
  <script>
      var x,y,currenTop, currentLeft,down;

      $("#reportWrapper").mousedown(function(e){
          if ($(e.target).hasClass('input-point')) {
              return;
          }
          down = true;
          x = e.pageX;
          y = e.pageY;
          currenTop = $(this).scrollTop();
          currentLeft = $(this).scrollLeft();
      });

      $("body").mousemove(function(e){
          if(down){
              var newX = e.pageX;
              var newY = e.pageY;

              //console.log(y+", "+newY+", "+top+", "+(top+(newY-y)));

              $("#reportWrapper").scrollTop(currenTop - newY+y);
              $("#reportWrapper").scrollLeft(currentLeft - newX+x);
          }
      });

      $("body").mouseup(function(e){down = false;});
  </script>
@endsection
