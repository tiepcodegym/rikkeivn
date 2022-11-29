<?php
use Rikkei\Core\View\CoreUrl;

$request = request();
?>

@extends('layouts.default')

@section('title', trans('me::view.Monthly Evaluation'))

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="{{ CoreUrl::asset('project/css/me_style.css') }}" />
<link rel="stylesheet" href="{{ CoreUrl::asset('project/css/me_team_style.css') }}" />
@endsection

@section('content')

<div class="box box-rikkei _me_create_page">
    <div id="me_team_edit_container"></div>
</div>

@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="{{ asset('lib/fixed-table/tableHeadFixer.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
@include('me::templates.script')
<script>
    pageParams = $.extend({}, pageParams, {
        IS_TEAM: true,
        urlOldMe: "{{ route('project::team.eval.create') }}",
        currTeamId: "{{ $request->get('team_id') }}",
        evalTeamList: JSON.parse('{!! json_encode($evalTeamList, JSON_UNESCAPED_UNICODE) !!}'),
        listEvalTeamMonths: JSON.parse('{!! json_encode($listEvalTeamMonths) !!}'),
        urlLoadMembersOfTeam: "{{ route('me::team.get_member') }}",
        urlSumitMeTeam: "{{ route('me::team.submit') }}",
    });
</script>
<script type="text/javascript" src="{{ CoreUrl::asset('/me/js/me-team-edit.js') }}"></script>
@endsection
