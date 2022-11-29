@extends('layouts.default')

@section('title')
{{ $titlePage }}
@endsection

@section('css')
<link rel="stylesheet" href="{{ URL::asset('project/css/edit.css') }}" />
@endsection

@section('content')

<div class="row">
    <div class="col-sm-12">
        @include('project::ncm.include.form')
    </div>
</div>
@endsection
@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script>
    var requiredCmt = '{{ trans('resource::message.Kindly add comments') }}';
    $('#form-task-comment').submit(function(e) {
        location.reload();
    });

    // edit comment task
    $(document).on('click', '.edit-comment', function() {
        var regex = /<br\s*[\/]?>/gi;
        var content = $(this).attr('data-content').replace(regex, '\n');
        var comment_id = $(this).data('id');

        $('#comment').val(content);
        $('input[name=comment_id]').val(comment_id);
        $('.text-esc').removeClass('hidden');
        $('textarea#comment').focus().css('box-shadow', '0px 1px 1px 1px');
        $('#button-comment-add').text('Save');
    });

    // delete comment task.
    $(document).on('click', '.delete-comment', function(event) {
        event.preventDefault();
        var comment_id = $(this).data('id');
        var token = $(this).data('token');
        var urlDelComment = $(this).data('url');
        bootbox.confirm({
            message: 'Are you sure delete comment?',
            className: 'modal-default',
            buttons: {
                cancel: {
                    label: 'Cancel', className: 'pull-left',
                },
            },
            callback: function(result) {
                if (result) {
                    $.ajax({
                        method: 'POST',
                        url: urlDelComment,
                        data: { id: comment_id, _token: token},
                        success: function (response) {
                            var e = jQuery.Event("keypress");
                            e.keyCode = $.ui.keyCode.ENTER;
                            $('input[name="page"]').val(1).trigger(e);
                            $("input[name=comment_id]").attr('value', '');
                            $('#comment').val('');
                            $('#button-comment-add').text('Add');
                            $('#comment').css('box-shadow', 'none');
                            $('.text-esc').addClass('hidden');
                        },
                    });
                }
            },
        });

    });
    if ($('#form-task-comment').length) {
        formTaskValid = $('#form-task-comment').validate({
            rules: {
                'tc[content]' : "required",
            },
            messages: {
                'tc[content]' : requiredCmt,
            },
        });
    }
    $(document).on('keyup', '#comment', function(e) {
        if (e.keyCode === 27) {
            $(this).val('');
            $("input[name=comment_id]").attr('value', '');
            $('#button-comment-add').text('Add');
            $('#comment').css('box-shadow', 'none');
            $('.text-esc').addClass('hidden');
            formTaskValid.resetForm();
        }
    });
</script>
@endsection
