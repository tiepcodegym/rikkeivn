<?php 
use Rikkei\Resource\View\getOptions;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Resource\View\View;
use Rikkei\Resource\Model\LanguageLevel;
?>
<div class="modal fade" id="modal-choose-language" tabindex="-1" role="dialog"  data-keyboard="false" >
    <div class="modal-dialog" style="width: 800px">
        <div class="modal-content"  >
            <div class="modal-body bg-wrapper">
                <section class="box box-primary">
                    <div class="box-body form-horizontal language-container">
                        
                    </div>
                </section>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-save" onclick="saveLang();" >
                    <span>
                        <i class="fa fa-save"></i>&nbsp;
                        {{ Lang::get('resource::view.Request.Create.Save') }}
                    </span>
                </button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
<div class="add-box-lang hidden">
    <div class="row row-add form-group margin-bottom-20">
        <div class="col-md-6">
            <div class="row">
                <label class="control-label align-right col-md-3">{{ trans('resource::view.Language') }}</label>
                <div class="col-md-9">
                    <select class="language form-control">
                        <option value="0">{{ trans('resource::view.Choose language') }}</option>
                        @foreach ($langs as $language)
                        <option value="{{ $language->id }}">{{ $language->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="row">
                <label class="control-label align-right col-md-3">{{ trans('resource::view.Level') }} <i class="fa fa-spin fa-refresh hidden"></i></label>
                <div class="col-md-9">
                    <select class="level form-control">
                        <option value="0">{{ trans('resource::view.Choose level') }}</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-md-1">
            <span href="#" class="btn-delete btn-delete-row float-right" onclick="removeLang(this);"><i class="fa fa-trash"></i></span>
        </div>
    </div>
</div>

<div class="language-save hidden">
    @if ($checkEdit)
        @foreach ($allLangs as $lang)
            <div class="row row-add form-group margin-bottom-20">
                <div class="col-md-6">
                    <div class="row">
                        <label class="control-label align-right col-md-3">{{ trans('resource::view.Language') }}</label>
                        <div class="col-md-9">
                            <select class="language form-control">
                                <option value="0">{{ trans('resource::view.Choose language') }}</option>
                                @foreach ($langs as $language)
                                <option value="{{ $language->id }}" @if ($language->id == $lang->lang_id) selected @endif>{{ $language->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="row">
                        <label class="control-label align-right col-md-3">{{ trans('resource::view.Level') }} <i class="fa fa-spin fa-refresh hidden"></i></label>
                        <div class="col-md-9">
                            <select class="level form-control">
                                <option value="0">{{ trans('resource::view.Choose level') }}</option>
                                <?php $levels = LanguageLevel::getLevelByLanguage($lang->lang_id); ?>
                                @foreach ($levels as $level)
                                <option value="{{ $level->id }}" @if ($level->id == $lang->lang_level_id) selected @endif>{{ $level->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-1">
                    <span href="#" class="btn-delete btn-delete-row float-right" onclick="removeLang(this);"><i class="fa fa-trash"></i></span>
                </div>
            </div>
        @endforeach
    @endif
    <span href="#" class="btn btn-success btn-add-lang">
        <i class="fa fa-plus"></i>&nbsp;<b>{{ trans('resource::view.Add language') }}</b>
    </span>
</div>
