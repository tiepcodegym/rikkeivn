@extends('layouts.default')

@section('title', trans('project::me.Monthly Evaluation Activity'))

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
@endsection

@section('content')

<div class="box box-info">
    <div class="box-body">
        <div class="row">
            <div class="col-md-5">
                <form class="form-inline no-validate" method="get" action="{{ request()->url() }}" id="form_month_filter">
                    <div class="form-group">
                        <strong>{{ trans('project::me.Month') }}: </strong>&nbsp;&nbsp;&nbsp;
                        <input type="text" id="activity_month" name="month" class="form-control form-inline month-picker maxw-230" value="{{ $month }}" autocomplete="off">
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="box-body">
        @if (!$activityFields->isEmpty())
        
            {!! Form::open([
                'method' => 'post',
                'route' => 'project::profile.me.save.activity',
                'class' => 'no-validate'
            ])  !!}
            
            @foreach ($activityFields as $field)
            <div class="form-group row">
                <div class="col-md-5">
                    <p><strong>{{ $field->label }}</strong></p>
                    <p>{!! $field->description !!}</p>
                </div>
                <div class="col-md-7">
                    <?php
                    $oldContent = old('activities.' . $field->id);
                    ?>
                    <textarea class="form-control" name="activities[{{ $field->id }}]" rows="8"
                              {{ $isEditable ? '' : 'disabled' }}
                              >{{ $oldContent ? $oldContent : (isset($activities[$field->id]) ? $activities[$field->id]->first()->content : null) }}</textarea>
                </div>
            </div>
            @endforeach
            
            <div class="form-group row">
                <div class="col-md-7 col-md-offset-5">
                    <input type="hidden" name="month" value="{{ $month }}">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ trans('project::me.Save') }}</button>
                </div>
            </div>
            
            {!! Form::close() !!}
            
        @endif
    </div>
</div>

@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script>
    $('.month-picker').datepicker({
        format: 'yyyy-mm',
        viewMode: "months", 
        minViewMode: "months",
        autoclose: true
    }).on('changeDate', function (e) {
        $('#form_month_filter').submit();
    });
</script>
@endsection


