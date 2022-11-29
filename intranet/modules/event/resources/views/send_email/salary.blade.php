<?php
use Rikkei\Core\View\CoreUrl;
use Rikkei\Event\View\ViewEvent;
use Rikkei\Team\Model\Team;

$fileMaxSize = ViewEvent::getPostMaxSize();
$teamPath = Team::getTeamPath();
?>

@extends('layouts.default')

@section('title', trans('event::view.Send mail employee salary'))

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" >
<link rel="stylesheet" href="{{ CoreUrl::asset('event/css/salary.css') }}">
@stop

@section('content')

<div class="box box-info">
    <div class="box-body">
        @include('event::salary.links', ['btnSendPass' => true])
    </div>
    <div class="box-body">
        <form method="get" action="{{ request()->url() }}" class="form-horizontal" id="form_branch">
            <div class="form-group row">
                <label class="col-md-2 col-lg-1 control-label pdt-0">{{ trans('event::view.Branch') }}</label>
                <div class="col-md-10 col-lg-11">
                    @foreach($listBranch as $value => $label)
                    <label><input type="radio" name="branch" value="{{ $value }}" {{ $value == $teamCode ? 'checked' : '' }}> <strong>{{ $label }}</strong></label>
                    &nbsp;&nbsp;&nbsp;
                    @endforeach
                </div>
            </div>
        </form>

        <form id="form-event-create" method="post" action="{{ route('event::send.email.employees.post.salary') }}" 
              class="form-horizontal has-valid" autocomplete="off" enctype="multipart/form-data">
            {!! csrf_field() !!}

            <div class="form-group row">
                <label for="csv_tet" class="col-sm-1 control-label required">{{ trans('event::view.File(excel, csv)') }} <em>*</em></label>
                <div class="col-sm-11">
                    <input class="form-control" type="file" name="csv_file" id="file_upload">
                </div>
            </div>

            <div class="form-group row">
                <label for="subject" class="col-sm-1 control-label required">{{ trans('event::view.Subject') }} <em>*</em></label>
                <div class="col-sm-11">
                    <input name="subject" class="form-control input-field" type="text" id="subject" 
                        value="{{ old('subject') ? old('subject') : $subjectEmail }}" placeholder="{{ trans('event::view.Subject') }}" />
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-1 control-label required">{{ trans('event::view.Content') }} <em>*</em></label>
                <div class="col-sm-11 iframe-full-width">
                    <textarea id="editor-content-event" class="text-editor" name="content">{{ old('content') ? old('content') : $contentEmail }}</textarea>
                </div>
                <div class="col-sm-11 col-sm-offset-1 hint-note">
                    <p>&#123;&#123; {{ trans('event::view.Name') }} &#125;&#125;: {{ trans('event::view.Name') }}</p>
                    <p>&#123;&#123; {{ trans('event::view.Account') }} &#125;&#125;: {{ trans('event::view.Account') }}</p>
                </div>
            </div>

            <div class="align-center">
                <input type="hidden" name="branch" value="{{ $teamCode }}" />
                <button type="submit" class="btn-add btn-submit-ckeditor">{{ trans('event::view.Send mail') }} <i class="fa fa-paper-plane"></i> <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
            </div>
        </form>
    </div>
</div>

<div class="box box-info">
    <div class="box-body">
        <h4> <a href="{{ asset('event/files/mau_phieu_luong_rikkei.xlsx') }}">{{ trans('event::view.Format excel file') }} <i class="fa fa-download"></i></a></h4>
    </div>
</div>

<div class="modal fade" id="modal_send_exists_pass" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="{{ route('event::send.email.employees.salary.send_exists_pass') }}">
                {!! csrf_field() !!}

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">{!! trans('event::view.send_mail_exists_password_tooltip') !!}</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{ trans('event::view.Email') }}</label>
                        <select name="emails[]" class="form-control select-search" multiple
                                data-remote-url="{{ route('team::employee.list.search.ajax') }}"></select>
                    </div>

                    <div class="list-team-select-box">
                        <label for="select-team-member">{{ trans('team::view.Choose team') }}</label>
                        <div class="input-box filter-multi-select multi-select-style btn-select-team">
                            <select name="team_ids[]" id="select-team-member" multiple
                                    class="form-control filter-grid multi-select-bst select-multi" autocomplete="off">
                                @foreach($teamsOptionAll as $option)
                                    <option value="{{ $option['value'] }}" class="checkbox-item">{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('event::view.Close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ trans('event::view.Send mail') }}</button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>

@endsection

@section('script')
<script>
    var teamPath = {!! json_encode($teamPath) !!};
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="{{ URL::asset('lib/ckeditor/ckeditor.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        RKfuncion.CKEditor.init(['editor-content-event']);
        var rules = {
            'csv_file': {
                required: true,
            },
            'subject': {
                required: true
            },
            'content': {
                required: true
            }
        };
        var messages = {
            'csv_file': {
                required: '{{ trans('event::view.This field is required') }}',
            },
            'subject': {
                required: '{{ trans('event::view.This field is required') }}',
            },
            'content': {
                required: '{{ trans('event::view.This field is required') }}',
            }
        };
        $('#form-event-create').validate({
            rules: rules,
            messages: messages
        });

        $('#send_pass_mail_exists_btn').click(function () {
            $('#modal_send_exists_pass').modal('show');
        });

        $('#modal_send_exists_pass').on('shown.bs.modal', function () {
            RKfuncion.select2.init();
        });

        $('[name="branch"]').change(function () {
            var form = $(this).closest('form');
            form.submit();
        });
    });
    var MAX_FILE_SIZE = {{ $fileMaxSize }};
    var textErrorMaxFileSize = '<?php echo trans('event::message.file_max_size', ['max' => $fileMaxSize]) ?>';
    var textConfirmSendMail = '{!! trans("event::message.Are you sure want to send mail?") !!}';
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
<script>
    $(function () {
        selectSearchReload();
        $('.select-multi').multiselect({
            numberDisplayed: 1,
            nonSelectedText: '--------------',
            allSelectedText: '{{ trans('project::view.All') }}',
            // onDropdownHide: function(event) {
            //     RKfuncion.filterGrid.filterRequest(this.$select);
            // }
        });
        // Limit the string length to column roles.
        $('.role-special').shortedContent({showChars: 150});
    });

    $(document).on('mouseup', 'li.checkbox-item', function () {
        var domInput = $(this).find('input');
        var id = domInput.val();
        var isChecked = !domInput.is(':checked');
        if (teamPath[id] && typeof teamPath[id].child !== "undefined") {
            var teamChild = teamPath[id].child;
            $('li.checkbox-item input').map((i, el) => {
                if (teamChild.indexOf(parseInt($(el).val())) !== -1 && $(el).is(':checked') === !isChecked) {
                    $(el).click();
                }
            });
        }
        setTimeout(() => {
            changeLabelSelected();
        }, 0)
    });
    $(document).ready(function () {
        changeLabelSelected();
    });

    function changeLabelSelected() {
        var checkedValue = $(".list-team-select-box option:selected");
        var title = '';
        if (checkedValue.length === 0) {
            $(".list-team-select-box .multiselect-selected-text").text('--------------');
        }
        if (checkedValue.length === 1) {
            $(".list-team-select-box .multiselect-selected-text").text($.trim(checkedValue.text()));
        }
        for (let i = 0; i < checkedValue.length; i++) {
            title += $.trim(checkedValue[i].label) + ', ';
        }
        $('.list-team-select-box button').prop('title', title.slice(0, -2))
    }
</script>
<script src="{{ CoreUrl::asset('event/js/salary.js') }}"></script>
@endsection
