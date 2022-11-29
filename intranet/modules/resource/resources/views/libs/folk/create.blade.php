<?php
use Carbon\Carbon;
use Rikkei\Core\View\View as ViewHelper;
use Rikkei\Resource\View\View;
use Rikkei\Resource\Model\Languages;
use Rikkei\Core\View\CoreUrl;

if (!isset($model)) {
    $model = new Rikkei\Team\Model\LibsFolk();
}
?>
<?php

$urlSubmit = route('resource::libfolk.store');
?>
<div class="row hidden" id="lib-folk-create">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="box-body">
                <form method="post" action="{{$urlSubmit}}" 
                      enctype="multipart/form-data" autocomplete="off" id="form-create-folk" class="form-horizontal">
                    {!! csrf_field() !!}
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="name" class="col-sm-4 control-label align-right">
                                {{ trans('resource::view.Name') }} 
                                <em class="required">*</em></label>
                                <div class="col-sm-8">
                                    <input name="name" class="form-control input-field" type="text" id="name" aria-required="true" aria-invalid="true"
                                        value="{{ old('name',$model->name) }}" placeholder="{{ trans('resource::view.Libs.Folk.List Folk name') }}" />
                                    <input type="hidden" name="id" value="{{ old('id', $model->id) }}"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 align-center">
                            <button class="btn-add" type="submit">
                                {{ trans('resource::view.Libs.Folk.Create Folk') }}
                            </button>
                            <button class="btn btn-default" type="button" id="close">
                                <i class="fa fa-ban"></i>
                                {{ trans('resource::view.Libs.Folk.Cancel') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>