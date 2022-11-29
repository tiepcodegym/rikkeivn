@extends('layouts.ticket_layout')

@section('title-ticket')
    {{ trans('ticket::view.Ticket') }} 
@endsection

@section('css-ticket')  
<?php 
  use Rikkei\Core\View\CoreUrl;
?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-wysiwyg/0.3.3/bootstrap3-wysihtml5.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('team/css/style.css') }}" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_ticket/css/ticket.css') }}" />
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700" rel="stylesheet">
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_ticket/css/jquery.fileuploader.css') }}" />
@endsection

@section('content-ticket')
    <!-- Box add task -->
    <div class="box box-primary" id="box_add" hidden="">
        @include('ticket::include.add')
    </div>
    <!-- /.box --> 

    <!-- Box ticket list -->
    <div class="box box-primary" id="ticket_list">
        @include('ticket::include.ticket_list')
    </div>
    <!-- /. box -->
@endsection

@section('script-ticket')
    {{-- plugins --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-wysiwyg/0.3.3/bootstrap3-wysihtml5.all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.min.js"></script>
    <script type="text/javascript" src="{{ CoreUrl::asset('asset_ticket/js/jquery.fileuploader.js') }}"></script>
    {{-- CONST --}}
    <script type="text/javascript">
        const MESSAGE_REQUIRE = '{{ trans('core::message.This field is required') }}';
        const MESSAGE_RANGE_LENGTH = '{{ trans('core::view.This field not be greater than :number characters', ['number' => 255]) }}';
        const checkLeader = {{ $checkLeader }};
    </script>
    {{-- JS MAIN --}}
    <script type="text/javascript" src="{{ CoreUrl::asset('asset_ticket/js/ticket.js') }}"></script>
    <script type="text/javascript" src="{{ CoreUrl::asset('asset_ticket/js/ticket_menu.js') }}"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            $('.uploadFile input:file').fileuploader({
                addMore: true,
                fileMaxSize : 1,
                extensions : ['jpg', 'jpeg', 'png', 'bmp'],
            });
            
            $(".select-search").select2();

            $('#team_id').on('select2:select', function (evt) {
                leader_id = $('#team_id').select2().find(":selected").attr("data-leader");
                $('#leader_id').val(leader_id);
                $('#leader-error').hide();
                $('#submit').prop("disabled", false);
            });

            $("#related_persons").select2({
                ajax: {
                    url: "{{ route('ticket::it.request.find-employee') }}",
                    dataType: "JSON",
                    data: function (params) {
                        return {
                            q: $.trim(params.term)
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                }
            });

            $('tr td a.mark-read').click(function(){
                id_ticket = $(this).closest('tr').attr("data-val");
                status = $(this).closest('tr').attr("data-status");
                var pgurl = window.location.href.substr(window.location.href);

                $.ajax({
                    type: "GET",
                    data: { 
                        id: id_ticket,
                        status: status
                    },
                    url: "{{ route('ticket::it.request.mark-read') }}",
                    success: function (result){
                        window.location = pgurl;
                    }
                });  
            });

            $('.comelate-calendar').on('click', function() {
                $('.select-search').select2('close'); 
                $('#datetimepicker1').data("DateTimePicker").show();
            });
        });
    </script>
@endsection
