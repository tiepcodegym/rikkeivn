@extends('layouts.default')

@section('title', trans('team::view.Import family info'))

@section('css')
<link href="{{ asset('project/css/edit.css') }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="{{ URL::asset('project/css/me_style.css') }}" />
@endsection

@section('content')

<div class="box box-info">
    <div class="box-body">
        <div class="row">
            <div class="col-md-12">
            {!! Form::open([
                'method' => 'post', 
                'route' => 
                'team::team.member.post-upload-family-info', 
                'files' => true, 
                'id' => 'upload-member',
                'class' => 'no-validate form-horizontal'
            ]) !!}
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group form-label-left row">
                            <label class="col-md-2 control-label" for="excel_file">Family information (CSV or Excel format)</label>
                            <div class="col-md-6">
                                <input class="form-control" type="file" name="excel_file">
                                
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 align-center">
                        <button class="btn-add" type="submit"><i class="fa fa-upload"></i> {{trans('team::view.Upload')}} <span class="_uploading hidden"><i class="fa fa-spin fa-refresh"></i></span></button>
                    </div>
                </div>
            {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script type="text/javascript" src="{{ asset('project/js/script.js') }}"></script>
<script type="text/javascript">
jQuery(document).ready(function ($) {
    RKfuncion.radioToggleClickShow.init();
    var _uploading = false;
    $('#upload-member').submit(function () {
        $(this).find('button').prop('disabled', true);
       $('._uploading').removeClass('hidden'); 
       setTimeout(function () {
        _uploading = true;
       }, 2000);
    });
    window.onbeforeunload = function () {
      if (_uploading) {
          return true;
      }  
    };

    @if (Session::get('upload_file'))
        var checking = false;
        var intervalChecking = setInterval(function () {
            if (checking) {
                return;
            }
            checking = true;
            $.ajax({
               type: 'GET',
               url: '{{ route("team::team.member.check-uploaded") }}',
               success: function (result) {
                   window.location.reload();
               },
               error: function () {
                   console.log('not done, uplaading');
               },
               complete: function () {
                   checking = false;
               }
            });
        }, 15000);
    @endif
});
</script>
@endsection
