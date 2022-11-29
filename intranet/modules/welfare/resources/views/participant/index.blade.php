<?php

use Rikkei\Team\Model\Role;
use Rikkei\Team\Model\Team;
use Rikkei\Welfare\Model\Event;
use Rikkei\Team\View\Permission;
use Rikkei\Welfare\View\TeamList;

$teamTreeHtml = TeamList::getTreeHtml();

?>
<div class="row information-participants">
    <div class="col-md-12">
        <div class="form-horizontal">
            <div class="form-group">
                <div class="col-lg-6">
                    {{ Form::label('is_register_online', trans('welfare::view.Register Online'), ['class' => 'control-label']) }}
                    <input id="is_register_online" name="is_register_online" type="checkbox" value="{{ Event::IS_REGISTER_ONLINE }} " class="format-checkox"
                    <?php if (isset($item['is_register_online']) && $item['is_register_online'] == Event::IS_REGISTER_ONLINE) : ?>
                               checked="checked"
                           <?php endif; ?>>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="box box-info">
            <div class="box-header">
                <h3 class="box-title with-border">{{ trans('team::view.List team') }}</h3>
            </div>
            <div class="box-body">
                @if (strip_tags($teamTreeHtml))
                    {!! $teamTreeHtml !!}
                @else
                    <p class="alert alert-warning">{{ trans('team::view.Not found team') }}</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="box box-info">
            <div class="box-header">
                <h3 class="box-title with-border">{{ trans('team::view.Employee List') }} </h3>
            </div>
            <div class="box-body">
                <div>
                    <button class="btn btn-success check-all-team" data-type="true" value="1"><i class="fa fa-check" aria-hidden="true"></i> {{ trans('welfare::view.Select all') }} </button>
                    <button class="btn btn-danger check-all-team" data-type ="false" value="1"><i class="fa fa-times" aria-hidden="true"></i> {{ trans('welfare::view.Unselect all') }} </button>
                    <button class="btn btn-primary btn-save-employee"><i class="fa fa-floppy-o "></i> {{ trans('welfare::view.Save') }} <i class="fa fa-spin fa-refresh hidden" id="disable-btn-save-employee"></i></button>
                </div>
                <table class="table table-bordered table-grid-data" id="table-employee">
                    <thead >
                        <tr>
                            <th class="disable_sort"><input type="checkbox" class="check_all" style="margin: 0px"></th>
                            <th>{{trans('welfare::view.Employee code')}}</th>
                            <th>{{trans('welfare::view.Employee name')}}</th>
                            <th>{{trans('welfare::view.Job Position')}}</th>
                            <th>{{trans('welfare::view.Phone')}}</th>
                            <th>{{trans('welfare::view.Company email')}}</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
