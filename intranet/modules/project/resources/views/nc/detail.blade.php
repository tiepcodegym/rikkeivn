<?php
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\Employee;
use Rikkei\Project\Model\Risk;
use Rikkei\Project\Model\Task;

$urlDelteFile = route('project::project.delete.file');
$urlSubmit = route('project::nc.save');
$status = Task::statusLabel();
?>
@extends('layouts.default')
@section('title', 'NC detail')

@section('content')
    <div class="css-create-page request-create-page request-detail-page word-break">
        <div class="css-create-body candidate-detail-page">
            @include('project::components.nc-detail', ['btnSave' => true])
        </div>
    </div>
    <!-- /.row -->
@endsection
@section('css')
    <meta name="_token" content="{{ csrf_token() }}"/>
    <link href="{{ asset('sales/css/sales.css') }}" rel="stylesheet" type="text/css" >
    <link rel="stylesheet" href="{{ URL::asset('project/css/edit.css') }}" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css" />
@endsection
@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
    <script src="{{ URL::asset('lib/js/moment.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
    <script>
        var projectId = '{{ isset($projectId) ? $projectId : '' }}';
        var urlSubmit = '{{ $urlSubmit }}';
        var urlDeleteFile = '{{ $urlDelteFile }}';
        var token = '{{ csrf_token() }}';

        $(document).ready(function () {
            console.log($('#team').val());
            $("#form-nc-detail").validate({
                rules: {
                    title: 'required',
                    employee_owner: {
                        required: function () {
                            if ($('#employee_owner').val() === "")
                                return true;
                            else
                                return false;
                        }
                    },
                    priority: 'required',
                    employee_assignee: {
                        required: function () {
                            if ($('#employee_assignee').val() === "")
                                return true;
                            else
                                return false;
                        }
                    },
                    employee_approver: {required: function(){
                            if($('#employee_approver').val() === "")
                                return true;
                            else
                                return false;
                        }
                    },
                    project_id:{required: function(){
                            if($('#project_id').val() === "")
                                return true;
                            else
                                return false;
                        }
                    },
                    team: {
                        required: function () {
                            if ($('#team').val() === "" || $('#team').val() == undefined || $('#team').val() == "undefined")
                                return true;
                            else
                                return false;
                        }
                    },
                },
                messages: {
                    title: requiredText,
                    employee_owner: requiredText,
                    priority: requiredText,
                    employee_assignee: requiredText,
                    employee_approver: requiredText,
                    project_id: requiredText,
                    team: requiredText,
                },
            });

            $('#employee_approver').select2();
            RKfuncion.select2.elementRemote(
                $('#employee_owner')
            );
            RKfuncion.select2.elementRemote(
                $('#project_id')
            );
            RKfuncion.select2.elementRemote(
                $('#employee_assignee')
            );
            RKfuncion.select2.elementRemote(
                $('#reporter')
            );

            $('.modal.task-dialog').removeAttr('tabindex').css('overflow', 'hidden');
            var heightBrowser = $(window).height() - 200;
            resizeModal('.modal.task-dialog .modal-body', heightBrowser);

            $('input.date-picker').datetimepicker({
                format: 'YYYY-MM-DD'
            });

            $(window).resize(function() {
                var heightBrowser = $(window).height() - 200;
                resizeModal('.modal.task-dialog .modal-body', heightBrowser);
            });

            function resizeModal(element, heightBrowser) {
                $(element).css({
                    'height': heightBrowser,
                    'overflow-y': 'scroll'
                });
            }
        });

        $(document).on("click", ".delete-file", function () {
            var fileId = $('.delete-file').attr('data-id');
            // if (!confirm("Do you want to delete")){
            //     return false;
            // }
            $.ajax({
                url: urlDeleteFile,
                method: "POST",
                dataType: "json",
                data: {
                    _token: token,
                    fileId: fileId,
                },
                success: function(data) {
                    $("div[data-file='" + fileId + "']").remove();
                }
            });
        });
    </script>
@endsection