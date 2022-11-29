@extends('test::layouts.popup')
@section('title')
    {{ trans('education::view.Education.Class Register Title') }}
@endsection
@section('css')
    @include('test::template.css')
    <style>
        .content-header h1 {
            text-align: center;
        }

        .box {
            width: 80%;
            margin: 0 auto;
        }

        .content-header h1 {
            font-size: 30px;
            margin-top: 50px;
            margin-bottom: 5px
        }
    </style>
@endsection
@section('content')
    <div class="box">
        <div class="modal-content">
            <div class="modal-body">
                @foreach($dataShift as $value)
                    <b class="modal-class-name" data-course_id="{{ $value->course_id }}">{{ trans('education::view.Education.Class') . " : $value->class_name" }}</b>
                    <br>
                    @foreach($value->data_shift as $keyShift => $shift)
                        <input type="checkbox" {{ $shift->check_end_time_register == 0 ? '' : 'disabled'}}
                        value="{{$shift->id}}"
                               <?php if ($shift->check_register == 1) {
                                   echo "checked";
                               } ?> class="ng-valid ng-dirty ng-touched input-register">
                        <span> {{ trans('education::view.Education.Ca2') . $shift->name . " : " . $shift->start_date_time . " - " . $shift->end_date_time }} </span>
                        <br>
                    @endforeach
                @endforeach
                <br>
            </div>
            <div class="modal-footer text-center">
                <button type="button" class="btn btn-danger" id="modalRegister">
                    {{ trans('education::view.Education.Register') }}
                    <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh save-refresh"></i>
                </button>
            </div>
        </div><!-- /.modal-content -->
    </div>
@endsection
@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.min.js"></script>
    <script>
        $(document).ready(function () {
            $(document).on("click", "#modalRegister", function (e) {
                $('#modalRegister .save-refresh').removeClass('hidden');
                $('#modalRegister').prop('disabled', true);
                var dataRegister = [],
                    bodyModal = '{{ trans('education::view.Education.Class Empty') }}';
                if ($('.input-register:checked').length) {
                    $.each($('.input-register:checked'), function (i, v) {
                        dataRegister.push($(this).val());
                    })
                }
                var parameter = {
                    'shift_id': dataRegister,
                    'course_id': $('.modal-class-name').data('course_id'),
                    '_token': '{{ csrf_token() }}',
                };
                if ($('.input-register:checked').length > 0) {
                    $.ajax({
                        url: '{{ route('education::education-profile.registerShift') }}',
                        type: 'post',
                        dataType: 'json',
                        data: parameter,
                        success: function (data) {
                            window.close();
                        }
                    });
                } else {
                    $('#modalRegister .save-refresh').addClass('hidden');
                    $('#modalRegister').prop('disabled', false);
                    bootbox.alert({
                        message: bodyModal,
                        className: 'modal-danger',
                    });
                }
            });
        })
    </script>
@endsection