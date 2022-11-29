@extends('layouts.default')

@section('title')
    {{ $titleHead }}
@endsection

@section('content')
    <div class="box box-info">
        <div class="box-body">

            <form method="get" action="{{ request()->url() }}" class="form-horizontal" id="form_branch">
                <div class="form-group row">
                    <label class="col-md-2 col-lg-1 control-label pdt-0">{{ trans('event::view.Branch') }}</label>
                    <div class="col-md-10 col-lg-11">
                        @foreach($listBranch as $value => $label)
                            <label><input type="radio" name="branch"
                                          value="{{ $value }}" {{ $value == $teamCode ? 'checked' : '' }}>
                                <strong>{{ $label }}</strong></label>
                            &nbsp;&nbsp;&nbsp;
                        @endforeach
                    </div>
                </div>
            </form>

            <form id="form-event-create" method="post"
                  action="{{ route('event::send.email.employees.turnoff.post') }}"
                  class="form-horizontal has-valid" autocomplete="off" enctype="multipart/form-data">
                {!! csrf_field() !!}
                <div class="form-group row">
                    <label for="csv_tet" class="col-md-2 col-lg-1 control-label required">File(excel)<em>*</em></label>
                    <div class="col-md-10 col-lg-11">
                        <input class="form-control" type="file" name="csv_file" id="csv_tet">
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label for="subject"
                                   class="col-sm-1 control-label required">{{ trans('event::view.Subject') }}<em>*</em></label>
                            <div class="col-sm-11">
                                <input name="subject" class="form-control input-field" type="text" id="subject"
                                       value="{{ $subjectEmail }}" placeholder="subject"/>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-2 col-lg-1 control-label required">{{ trans('event::view.Content') }}<em>*</em></label>
                    <div class="col-md-10 col-lg-11 iframe-full-width">
                        <textarea id="editor-content-event" class="text-editor"
                                  name="content">{{ $contentEmail }}</textarea>
                    </div>
                    <div class="col-sm-12 col-sm-offset-1 hint-note">
                        <p>&#123;&#123; name &#125;&#125;: Name</p>
                        <p>&#123;&#123; account &#125;&#125;: Account</p>
                        <p>&#123;&#123; date &#125;&#125;: Date</p>
                        <p>&#123;&#123; month &#125;&#125;: Month</p>
                        <p>&#123;&#123; n &#125;&#125;: Số lần</p>
                        <p>&#123;&#123; listDate &#125;&#125;: List date forgot turnoff</p>
                    </div>
                </div>

                <div class="text-center">
                    <input type="hidden" name="branch" value="{{ $teamCode }}">
                    <button type="submit" class="btn-add btn-submit-ckeditor">{{ trans('event::view.Send mail') }}
                        <i class="fa fa-paper-plane"></i>
                        <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="box box-info">
        <div class="box-body">
            <div class="row">
                <div class="col-md-12">
                    <h4>Format excel file</h4>
                    <a style="font-size: 16px"
                       href="{{ asset('event/files/Warnings_20190820.xlsx') }}">{{ trans('event::view.file_template') }}</a>
                    <br><br>
                    <img src="{{ URL::asset('event/images/template/turnOff.png') }}" class="img-responsive"/>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script src="{{ URL::asset('lib/ckeditor/ckeditor.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
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
                },
                messages = {
                    'csv_file': {
                        required: '{{ trans('core::view.This field is required') }}',
                    },
                    'subject': {
                        required: '{{ trans('core::view.This field is required') }}',
                    },
                    'content': {
                        required: '{{ trans('core::view.This field is required') }}',
                    }
                };
            $('#form-event-create').validate({
                rules: rules,
                messages: messages
            });

            $('[name="branch"]').change(function () {
                var form = $(this).closest('form');
                form.submit();
            });
        });
    </script>
@endsection
