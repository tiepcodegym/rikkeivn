<?php
use Rikkei\Core\View\View as CoreView;
use Rikkei\Core\Model\CoreConfigData;
?>

<div class="col-md-12">
    <div class="box box-info">
        <div class="box-body">
            <form id="form-system-general" method="post" action="{{ route('core::setting.system.data.save') }}"
                  class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group form-label-left row">
                            <label class="col-md-2 control-label">{{ trans('core::view.List tables save Log') }}</label>
                            <div class="col-md-9">
                                <textarea name="item[db_log.tables]" class="form-control input-field" rows="3">{{ CoreConfigData::getValueDb('db_log.tables') }}</textarea>
                                <p class="hint">{{ trans('core::view.db_log_note') }}</p>
                            </div>
                            <div class="col-md-1">
                                <button class="btn-add" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

