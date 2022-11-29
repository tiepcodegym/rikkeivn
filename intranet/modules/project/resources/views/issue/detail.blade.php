<?php
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\Employee;
use Rikkei\Project\Model\Risk;
$urlDelteFile = route('project::project.delete.file');
?>
@extends('layouts.default')
@section('title')
    {{ trans('project::view.Issue detail') }}
@endsection
@section('content')
    <div class="css-create-page request-create-page request-detail-page word-break">
        <div class="css-create-body candidate-detail-page">
            @include('project::components.issue-detail', ['btnSave' => true])
            @include('project::components.issue-comment')
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
    <style>
        .mentions-input-box .mentions > div {
            font-size: 14px;
        }
        .mentions-input-box .mentions > div > strong {
            font-weight: normal;
            background: #d8dfea;
            font-size: 14px;
        }
        #comment{
            font-size: 14px;
        }
    </style>
@endsection
@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
    <script src="{{ URL::asset('lib/js/moment.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
    <script>
        var urlDeleteFile = '{{ $urlDelteFile }}';
        var token = '{{ csrf_token() }}';
        $(document).ready(function () {
            $('.mitigation-box .row-mitigation').find('#issue-assignee').each(function() {
                RKfuncion.select2.elementRemote(
                    $(this)
                );
            });
            $(document).on('click', '.btn-delete', function() {
                $(this).closest('.row-mitigation').remove();
            });

                $("#form-issue-detail").validate({
                    errorPlacement: function(error, element) {
                        if (element.attr("name") == "team" ) {
                            $("#error-team-owner").html( error );
                        } else {
                            error.insertAfter(element);
                        }

                        if (element.attr("name") == "employee_owner" ) {
                            $("#error-employee-owner").html( error );
                        } else {
                            error.insertAfter(element);
                        }
                    },
                    rules: {
                        title: 'required',
                        content: 'required',
                        employee_owner: {required: function(){
                                if ($('#employee_owner').val() === "")
                                    return true;
                                else
                                    return false;
                            }
                        },
                        level_important: 'required',
                        priority: 'required',
                        team:{required: function(){
                                if ($('#team').val() === "")
                                    return true;
                                else
                                    return false;
                            }
                        },
                        type: 'required',
                        solution: 'required',
                        cause: 'required',
                        reporter: 'required'
                    },
                    messages: {
                        title: requiredText,
                        content: requiredText,
                        reporter: requiredText,
                        level_important: requiredText,
                        team: requiredText,
                        type: requiredText,
                        priority: requiredText,
                        solution: requiredText,
                        cause: requiredText,
                        employee_owner: requiredText,
                    },
                });
                addRules();
                function addRules() {
                    $('.mitigation-box').find('.issue-content').each(function() {
                        $(this).rules('add', {
                            required: true
                        });
                    });
                    $('.mitigation-box').find('.issue-assignee').each(function() {
                        $(this).rules('add', {
                            required: true
                        });
                    });
                    $('.mitigation-box').find('.issue-duedate').each(function() {
                        $(this).rules('add', {
                            required: true
                        });
                    });
                    $('.mitigation-box').find('.issue-status').each(function() {
                        $(this).rules('add', {
                            required: true
                        });
                    });
                }
                RKfuncion.select2.elementRemote(
                    $('#employee_owner')
                );

                $('.modal.task-dialog').removeAttr('tabindex').css('overflow', 'hidden');

                function resizeModal(element, heightBrowser) {
                    $(element).css({
                        'height':  heightBrowser,
                        'overflow-y': 'scroll'
                    });
                }

                $('input.date-picker').datetimepicker({
                    format: 'YYYY-MM-DD'
                });
        });
        $(document).on("click", ".delete-file", function () {
            var fileId = $('.delete-file').attr('data-id');
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