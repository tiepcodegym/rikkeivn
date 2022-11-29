<?php
use Rikkei\Core\View\CoreUrl;

$currProjId = request()->get('project_id');
$currProject = null;
if ($currProjId) {
    $currProject = \Rikkei\Me\Model\ME::getInstance()->getProjectOrTeam($currProjId);
}
?>

@extends('layouts.default')

@section('title', trans('project::me.Monthly Evaluation'))

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="{{ CoreUrl::asset('project/css/me_style.css') }}" />

@endsection

@section('content')

<div class="box box-rikkei _me_create_page">
    <div id="me_edit_container"></div>
</div>

@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="{{ asset('lib/fixed-table/tableHeadFixer.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
@include('me::templates.script')
<script>
    pageParams.currProject = null;
    @if ($currProjId)
    pageParams.currProject = JSON.parse('{!! json_encode($currProject->toArray(), JSON_UNESCAPED_UNICODE) !!}');
    @endif
</script>
<script type="text/javascript" src="{{ CoreUrl::asset('/me/js/me-edit.js') }}"></script>
@endsection
