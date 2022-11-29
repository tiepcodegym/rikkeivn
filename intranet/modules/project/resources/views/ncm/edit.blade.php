@extends('layouts.default')

@section('title')

@if ($taskItem->id)
    {{ trans('project::view.Edit None compliance') }}
@else
    {{ trans('project::view.Create None compliance') }}
@endif
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" >
<link rel="stylesheet" href="{{ CoreUrl::asset('project/css/edit.css') }}" />
@endsection

@section('content')

@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="{{ URL::asset('lib/js/moment.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
<script type="text/javascript" src="{{ CoreUrl::asset('project/js/script.js') }}"></script>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $('input.date-picker').datetimepicker({
            format: 'YYYY-MM-DD'
        });
        RKfuncion.select2.init();
        RKfuncion.bootstapMultiSelect.init({
            nonSelectedText: '{{ trans('project::view.Choose items') }}',
            allSelectedText: '{{ trans('project::view.All') }}',
            nSelectedText: '{{ trans('project::view.items selected') }}',
        });
        $('#form-task-ncm-edit').validate({
            rules: {
                'teams[]': {
                    requried: true
                },
                'ncm[request_date]': {
                    requried: true
                }
            },
            messages: {
                
            }
        });
    });
</script>
@endsection
