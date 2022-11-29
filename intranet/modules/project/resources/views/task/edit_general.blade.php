@extends('layouts.default')

@section('title')
{{ $titlePage }}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" href="{{ URL::asset('project/css/edit.css') }}" />
@endsection

@section('content')

<div class="row">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="box-body">
                <form id="form-task-edit" method="post" action="{{route('project::task.general.save', ['id' => $taskItem->id])}}" class="form-horizontal form-submit-ajax has-valid" autocomplete="off">
                    {!! csrf_field() !!}

                    @include('project::task.include.task_general_body')
                </form>
            </div>
        </div>
    </div>
</div>
@include ('project::task.include.comment')
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="{{ URL::asset('lib/js/moment.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script type="text/javascript" src="{{ asset('project/js/script.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $('input.date-picker').datetimepicker({
            format: 'YYYY-MM-DD'
        });
        RKfuncion.select2.init();
    });
</script>
@endsection
