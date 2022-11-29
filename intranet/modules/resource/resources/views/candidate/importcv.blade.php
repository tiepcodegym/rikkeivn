@extends('layouts.default')
@section('title')
    {{ trans('resource::view.Candidate.Importcv.Candidate Import Cv') }}
@endsection
<?php
	$urlSubmit = route('resource::candidate.postimportcv');
?>
@section('css')
<link href="{{ asset('sales/css/sales.css') }}" rel="stylesheet" type="text/css" >
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" >
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">    
<link rel="stylesheet" href="{{ URL::asset('project/css/edit.css') }}" />
<link rel="stylesheet" href="{{ URL::asset('resource/css/resource.css') }}" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/minimal/_all.css" />
@endsection
@section('content')
<div class="css-create-page request-create-page candidate-create-page">
    <div class="css-create-body">
        <div class="box box-primary padding-bottom-30">
        		<form method="post" action="{{$urlSubmit}}" enctype="multipart/form-data">
                {!! csrf_field() !!}
			    <div class="row">
                    <div class="col-md-12">
                        <div class="form-group form-label-left row">
                            <label class="col-md-2 control-label" for="excel_file">{{ trans('resource::view.Candidate.Importcv.Import file') }} (.xlsx)</label>
                            <div class="col-md-6">
                                <input class="form-control" type="file" name="file">
                            </div>
                        </div>
                    </div>
                </div>
			    <div class="row">
                    <div class="col-md-12 align-center">
                        <button class="btn-add" type="submit"><i class="fa fa-upload"></i> {{ trans('resource::view.Candidate.Importcv.Import') }} <span class="_uploading hidden"><i class="fa fa-spin fa-refresh"></i></span></button>
                    </div>
                </div>
			    </form>
		</div>
	</div>
</div>
@endsection
<!-- Styles -->
@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link href="{{ asset('common/css/style.css') }}" rel="stylesheet" type="text/css" >
<link href="{{ asset('sales/css/sales.css') }}" rel="stylesheet" type="text/css" >
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
@endsection

<!-- Script -->
@section('script')
<script src="{{ asset('resource/js/request/list.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
@endsection
