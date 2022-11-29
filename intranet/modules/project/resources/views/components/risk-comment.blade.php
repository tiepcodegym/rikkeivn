<?php
use Rikkei\Core\View\CoreUrl;
?>

@if (isset($riskInfo))
    <div class="box box-primary box-solid">
        <div class="box-header with-border">
            <h3 class="box-title">{{ trans('project::view.Comments') }}</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
            <div class="row">
                <form id="form-risk-comment" method="post" action="{{ route('project::risk.save.comment') }}" autocomplete="off" data-callback-success="commentSuccess" novalidate="novalidate" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="risk_id" value="{{ $riskInfo->id }}">
                    <input type="hidden" name="project_id" value="{{ $projectId }}">
                    <input type="hidden" name="comment_id" value="">
                    <div class="col-md-10 form-group" id="b-dropzone-wrapper">
                        <textarea class="mention" name="content" class="form-control text-resize-y" rows="3" id="comment"></textarea>
                        <span class="text-esc hidden" style="font-size: 11px; margin-left: 5px;">Nhấn Esc để hủy edit</span>
                        <label for="attach_risk_comment" style="color:blue;"><i class="fa fa-upload" aria-hidden="true"></i>Attach a file</label>
                        <input type="file" id="attach_risk_comment" name="attach_risk_comment[]" style="display: none" multiple />
                    </div>
                    <div class="col-md-2 form-group">
                        <button id="button-comment-add" class="btn btn-primary" type="submit">Thêm <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                    </div>
                </form>
            </div>
            <div class="col-xs-12 comment-list">
                <div class="" >
                    <span><i class="fa fa-spin fa-refresh hidden"></i></span>
                    <div class="grid-data-query-table comment-create-task">
                        <div>
                            @foreach ($comments as $comment)
                                <div class="item">
                                    <p class="author"><strong>{{ $comment->name }}</strong> <i>at {{ $comment->created_at }}</i></p>
                                    <p class="comment">{!! nl2br($comment->content) !!}</p>
                                    @if($comment->paths)
                                        @foreach (explode(",", $comment->paths) as $path)
                                            <div data-file="{{explode("*", $path)[1]}}"><a href="{{ route('project::issue.download', ['id' => explode("*", $path)[1]]) }}">{{ basename(explode("*", $path)[0]) }}</a>
                                                <span><button type="button" class="delete-file" data-id="{{explode("*", $path)[1]}}"><i class="fa fa-remove" style="font-size:15px; color:red;"></i></button></span> <br>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
{{-- <link rel="stylesheet" href="{{ URL::asset('lib/mentions-input/jquery.mentionsInput.css') }}" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.13.2/underscore-min.js" integrity="sha512-anTuWy6G+usqNI0z/BduDtGWMZLGieuJffU89wUU7zwY/JhmDzFrfIZFA3PY7CEX4qxmn3QXRoXysk6NBh5muQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="{{ CoreUrl::asset('lib/mentions-input/lib/jquery.events.input.js') }}" type='text/javascript'></script>
<script src="{{ CoreUrl::asset('lib/mentions-input/lib/jquery.elastic.js') }}" type='text/javascript'></script>
<script src="{{ CoreUrl::asset('lib/mentions-input/jquery.mentionsInput.js') }}" type='text/javascript'></script> --}}

<link href='{{ CoreUrl::asset('lib/mentions-input/v2/jquery.mentionsInput.css') }}' rel='stylesheet' type='text/css'>
<script src='https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js' type='text/javascript'></script>
<script src='https://cdn.rawgit.com/jashkenas/underscore/1.8.3/underscore-min.js' type='text/javascript'></script>
<script src='{{ CoreUrl::asset('lib/mentions-input/v2/jquery.events.input.js') }}' type='text/javascript'></script>
<script src='{{ CoreUrl::asset('lib/mentions-input/v2/jquery.elastic.js') }}' type='text/javascript'></script>
<script src='{{ CoreUrl::asset('lib/mentions-input/v2/jquery.mentionsInput.js') }}' type='text/javascript'></script>
<script>

    var $char = $('#comment').val();
    var test = "";
    $("#comment").change(function() {
        $char = $(this).val();
    });

    $('#attach_risk_comment').change(function(e) {
        var fileUpload = $("#attach_risk_comment").get(0);
        var files = fileUpload.files;
        var hostname = location.protocol + "//" + document.domain;
        var filename = "";

        let name_file = $('#comment').val();
        for (var i = 0; i < files.length; i++) {
            filename = e.target.files[i].name;
            name_file += "[:" + hostname + '/' + 'storage/project/risk' + '/' + filename + ":]" + "\n";
        }
        $('#comment').val(name_file + "\n");
    });

    var mentions = $('#comment').mentionsInput({
        allowRepeat: true,
        onDataRequest: async function (mode, query, callback) {
            let data = [];
            await $.ajax({
                url: '{{route('team::employee.list.search.ajax')}}',
                method: "GET",
                data: {
                    _token: token,
                    q: query,
                },
                success: function(response) {
                    data = response.items;
                    data = data.map(item => {
                        item.name = item.text;
                        return item;
                    });
                }
            });

            data = _.filter(data, function(item) { return item.name.toLowerCase().indexOf(query.toLowerCase()) > -1 });
            callback.call(this, data);
        },
        templates:{
            mentionItemHighlight: _.template('<strong><span><%= value %></span><input type="hidden" name="emp_mention[]" value="<%= id %>" /></strong>')
        }
    });

    $('#button-comment-add').click(function() {
        $('textarea.mention').mentionsInput('val', function(text) {
            $('#comment').val(text);
        });
    })
</script>