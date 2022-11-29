<?php

use Rikkei\Team\View\Config as TeamConfig;

if (!isset($langDomain) || !$langDomain) {
    $langDomain = 'core::view.';
}
?>
<div id="list-partner-group" class="modal fade" role="dialog" style="display: none;">
    <div class="modal-dialog modal-md">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-toggle="modal" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-group"></i> {{ trans('welfare::view.Partner Group') }}</h4>
            </div>
            <div class="modal-body">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-plus-square"></i> {{ trans('welfare::view.Add new Partner Group') }}</h3>
                    </div>
                    <div class="box-body">
                    <div class="form-group row">
                        {{ Form::open(['route' => 'welfare::welfare.partner.group.add', 'id' => 'addPartnerGroup']) }}
                        <div class="col-lg-10">
                            {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => trans('welfare::view.Enter some name'), 'id' => 'namePartnerGroup', 'required' => 'true']) }}
                            <p id="error-name-unique" class="error hidden" style="color: red;"></p>
                        </div>
                        <div class="col-lg-2">
                            {{ Form::button(trans('welfare::view.Add'). ' <span class="_uploading hidden" id="uploading"><i class="fa fa-spin fa-refresh"></i></span>',
                                        ['class' => 'btn-add', 'id' => 'partner-group-add', 'type' => 'submit']) }}
                        </div>
                        {{ Form::close()}}
                    </div>
                    </div>
                </div>
                <hr>
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-list"></i> {{ trans('welfare::view.List Partner Group') }}</h3>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-partner-group" data-url="{{ route('welfare::welfare.partner.group.list') }}">
                                <thead>
                                    <tr>
                                        <th class="">{{ trans('welfare::view.Name Partner Group') }}</th>
                                        <th class="">&nbsp;</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-add btn-choose-group-partner" data-dismiss="modal"><i class="fa fa-check-square-o"></i> {{ trans('welfare::view.Choose') }}</button>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-dismiss="modal"><span class="bootstrap-dialog-button-icon fa fa-close"></span> {{ trans('welfare::view.Close') }}</button>
            </div>
        </div>
    </div>
</div>

<div id="modal-edit-partner-group" class="modal fade in" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <form class="form-horizontal" role="form" action="{{ route('welfare::welfare.partner.group.edit')}}" id="formModalPertnerGroup">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">×</button>
                    <h4 class="modal-title title-edit">{{ trans('welfare::view.Edit Partner Group') }}</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group hidden">
                        <label class="control-label col-sm-2" for="id">ID:</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="fid" disabled="">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-2" for="name">{{ trans('welfare::view.Name') }}</label>
                        <div class="col-sm-10">
                            {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => trans('welfare::view.Enter some name'), 'id' => 'n', 'required' => 'true']) }}
                            <label id="error-name" class="error hidden" style="color: red;">{{ trans('core::message.This field is required') }}</label>
                            <label id="error-name-length" class="error hidden" style="color: red;">{{ trans('core::view.This field not be greater than :number characters', ['number' => 50]) }}</label>
                            <p id="error-name-unique" class="error hidden" style="color: red;"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn actionBtn btn-success edit" data-url="{{ route('welfare::welfare.partner.group.delete')}}">
                        <span id="footer_action_button-edit" class="">{{ trans('welfare::view.Update') }}</span>
                        <span class="_uploading hidden" id="uploading"><i class="fa fa-spin fa-refresh"></i></span>
                    </button>
                    <button type="button" class="btn btn-warning" data-dismiss="modal">
                        <span class="">{{ trans('welfare::view.Close') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade modal-danger" id="modal-delete-partner-group" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ trans('welfare::view.Confirm Delete') }}</h4>
            </div>
            <div class="modal-body">
                <form action="" method="POST" class="form-confirm-delete">
                <div class="deleteContent">
                    {{ trans('welfare::view.Are you sure') }}<span class="hidden did"></span>
                </div>
                <p class="text-change"></p>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-close" data-dismiss="modal">{{ Lang::get($langDomain.'Close') }}</button>
                <button type="button" class="btn btn-outline btn-ok" data-dismiss="modal">{{ trans('welfare::view.Confirm Delete') }}</button>
            </div>
        </div>
    </div>
</div>
