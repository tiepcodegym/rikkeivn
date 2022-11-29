<?php
use Rikkei\Test\View\ViewTest;

$exFileLink = ViewTest::EX_FILE_LINK;
$currLangName = isset($allLangs[$currentLang]) ? $allLangs[$currentLang] : null;
?>

<div class="modal fade modal-default" id="modal_import_file" tabindex="-1" role="dialog"
     data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ trans('test::test.import_file') }} ({{ $currLangName }})</h4>
            </div>
            <div class="modal-body">
                <div class="form-group row">
                    <label class="col-sm-4">
                        {{ trans('test::test.import_excel_file') }} <em>*</em> 
                        <span class="loading_file hidden"><i class="fa fa-spin fa-refresh"></i></span>
                    </label>
                    <div class="col-sm-8">
                        {!! Form::file('excel_file', [
                            'id' => 'excel_file', 
                            'class' => '_inline', 
                            'data-url' => route('test::admin.test.import')
                        ]) !!}
                        <label><a target="_blank" href="{{ $exFileLink }}" class="link"><i>({{ trans('test::test.view_example_file') }})</i></a></label>
                    </div>
                </div>
                
                <div id="import_result">
                    
                </div>
                
                <hr />
                <div class="form-group">
                    <label class="modal-title" style="padding-bottom: 15px;">{{ trans('test::test.Option for multiple choice questions') }}<em>*</em></label>
                    <ul>
                        <li>
                            <label><input type="radio" name="option_import" value="append"> {{ trans('test::test.append_question') }}</label>
                        </li>
                        <li>
                            <label><input type="radio" name="option_import" value="replace" checked> {{ trans('test::test.replace_question') }}</label>
                        </li>
                    </ul>
                    <input type="hidden" id="current_test_id" value="{{ $item ? $item->id : null }}">
                </div>
                <div style="text-align: right">
                    <button type="button" class="btn btn-close" data-dismiss="modal" id="cancel_import">
                        {{ trans('test::test.close') }}
                    </button>
                    <button type="button" class="btn btn-success" id="submit_import" disabled
                            data-noti="{{ trans('test::test.Are you sure do action?') }}">{{ trans('test::test.Import') }}</button>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>

<div class="modal fade modal-default" id="modal_copy_to" role="dialog" aria-labelledby="copy to" aria-hidden="true"
     data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ trans('test::test.copy_to_test') }}</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>{{ trans('test::test.select_test') }}</label>
                    <div class="select2-block">
                        <select class="form-control" data-remote-url="{{ route('test::admin.test.search_test') }}"
                                id="select_search_test">
                        </select>
                    </div>
                </div>
                <hr />
                <div class="form-group">
                    <ul>
                        <li>
                            <label><input type="radio" name="option_copy" value="append" checked> {{ trans('test::test.append_question') }}</label>
                        </li>
                        <li>
                            <label>
                                <input type="radio" name="option_copy" value="replace"> {{ trans('test::test.replace_question') }} 
                                <i class="error">({{ trans('test::test.old_question_will_be_delete') }})</i>
                            </label>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-close" data-dismiss="modal" id="cancel_copy">
                    {{ trans('test::test.close') }}
                </button>
                <button type="button" class="btn btn-success" id="submit_copy" disabled
                        data-noti="{{ trans('test::test.Are you sure do action?') }}"
                        data-url="{{ route('test::admin.test.question.copy_to') }}">
                            {{ trans('test::test.Copy') }}
                </button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>

