<?php
use Rikkei\Sales\View\OpporView;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Permission;

$statusLabels = OpporView::statusLabels();
?>

@extends('layouts.default')

@section('title', $item ? trans('sales::view.Opportunity detail') : trans('sales::view.Create opportunity'))

@section('css')
<base href="/sales/request-opportunity/">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css">
<link rel="stylesheet" href="{{ CoreUrl::asset('sales/css/opportunity.css') }}">
<link rel="stylesheet" href="{{ CoreUrl::asset('sales/opportunity/styles.css') }}">
@stop

@section('content')

<app-opportunity style="min-height: 65vh; display: block;">
    <p class="text-center"><i class="fa fa-spin fa-refresh"></i></p>
</app-opportunity>

@stop

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.15.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script src="{{ CoreUrl::asset('/lib/xlsx-style/cpexcel.js') }}"></script>
<script src="{{ CoreUrl::asset('/lib/xlsx-style/jszip.js') }}"></script>
<script src="{{ CoreUrl::asset('/lib/xlsx-style/ods.js') }}"></script>
<script src="{{ CoreUrl::asset('/lib/xlsx-style/shim.js') }}"></script>
<script src="{{ CoreUrl::asset('/lib/xlsx-style/xlsx.full.min.js') }}"></script>
<script src="{{ CoreUrl::asset('/lib/xlsx-style/xlsx_table_to_book.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/1.3.3/FileSaver.min.js"></script>

<script>
    <?php
    if ($item) {
        $item->prog_ids = $item->programs->lists('id')->toArray();
        if (!$permissEdit) {
            $item->customer_name = null;
        }
        $creator = $item->creator;
        if ($creator) {
            $item->creator_name = $creator->getNickName();
        }
    }
    ?>
    var ITEM = null;
    @if ($item)
        ITEM = {!! $item !!};
    @endif
</script>
@include('sales::req-oppor.includes.scripts');
<!--angular js-->
<script type="text/javascript" src="{{ CoreUrl::asset('sales/opportunity/runtime.js') }}"></script>
<script type="text/javascript" src="{{ CoreUrl::asset('sales/opportunity/es2015-polyfills.js') }}" nomodule></script>
<script type="text/javascript" src="{{ CoreUrl::asset('sales/opportunity/polyfills.js') }}"></script>
<script type="text/javascript" src="{{ CoreUrl::asset('sales/opportunity/main.js') }}"></script>
<!--end angular js-->
<!--<script src="{{ CoreUrl::asset('sales/js/opportunity.js') }}"></script>-->
@stop