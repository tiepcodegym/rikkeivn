@extends('layouts.default')
<?php
use Rikkei\Emailnoti\View\TeamList;
use Rikkei\Core\View\Form;
use Rikkei\Team\Model\Role;
use Rikkei\Core\Model\CoreConfigData;

$teamTreeHtml = TeamList::getTreeHtml(Form::getData('id'));
$positionAll = Role::getAllPosition();
$roleAll = Role::getAllRole();
$emailAddressSystem = CoreConfigData::getEmailAddress();
?>

@section('title', trans('emailnoti::view.Send notification'))
@section('css')
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('lib/jQueryUi/css/jquery-ui.min.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('lib/jQueryTree/css/jquery.tree.min.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('lib/tokenize2/css/tokenize2.min.css') }}"/>
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css"/>
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="{{ URL::asset('team/css/style.css') }}"/>
    <link rel="stylesheet" href="{{ URL::asset('emailnoti/css/style.css') }}"/>
    <style>
        .content-header {
            display: none;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-2 col-md-3">
            @include('notify::admin.include.menu_left')
        </div>
        <div class="col-lg-10 col-md-9">
            <div class="team-position-wrapper hight-same">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"></h3>
                    </div>
                    <div class="row team-list-action box-body">
                        <form id="form-post-edit" method="post" action="{{ route('notify::admin.notify.store') }}"
                              class="" autocomplete="off" enctype="multipart/form-data">
                            {!! csrf_field() !!}
                            <div class="form-group">
                                <div class="col-md-8 col-md-offset-2">
                                    <div class="form-group">
                                        <label for="title"
                                               class="control-label required">{{ trans('emailnoti::view.To') }}</label>
                                        <div class="row">
                                            <div class="col-md-2 col-sm-12">
                                                <button type="button" id="select-team"
                                                        class="btn btn-primary btn-submit-sp"
                                                        data-toggle="modal"
                                                        data-target="#getTeamModal">{{ trans('emailnoti::view.Select team') }}</button>
                                            </div>
                                            <div class="col-md-10 col-sm-12">
                                                <input name="team_name" class="form-control input-field" type="text"
                                                       id="team_name" readonly/>
                                            </div>
                                            <input class="form-control input-field" type="hidden" name="team_list"
                                                   id="team_list"/>
                                            <!-- Modal -->
                                            <div id="getTeamModal" class="modal fade" role="dialog">
                                                <div class="modal-dialog">
                                                    <div class="modal-content" style="border-radius: 10px;">
                                                        <div class="modal-header">
                                                            <button type="button" class="close"
                                                                    onclick="closeSelectTeam()">
                                                                &times;
                                                            </button>
                                                            <h4 class="modal-title">{{ trans('emailnoti::view.List team') }}</h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div id="trees" style="border: 0px solid #aaaaaa;">
                                                                @if (strip_tags($teamTreeHtml))
                                                                    {!! $teamTreeHtml !!}
                                                                @else
                                                                    <p class="alert alert-warning">{{ trans('emailnoti::view.Not found team') }}</p>
                                                                @endif
                                                            </div>
                                                            <label id="error-select-team" class="error"
                                                                   style="color: red; display: none;">{{ trans('emailnoti::view.You have not select team') }}</label>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <div class="pull-left">
                                                                <button type="button" class="btn btn-primary btn-sm"
                                                                        onclick="selectAll()">{{ trans('emailnoti::view.Select all') }}</button>
                                                                <button type="button" class="btn bg-olive btn-sm"
                                                                        onclick="deSelect()">{{ trans('emailnoti::view.Deselect') }}</button>
                                                            </div>
                                                            <button type="button" class="btn btn-default"
                                                                    onclick="closeSelectTeam()">{{ trans('emailnoti::view.Close') }}</button>
                                                            <button type="button" id="select_team"
                                                                    class="btn btn-primary"
                                                                    onclick="return getTeam()">{{ trans('emailnoti::view.Save') }}</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="title"
                                               class="control-label required">{{ trans('emailnoti::view.Subject') }}
                                            <em>*</em></label>
                                        <div class="">
                                            <input name="title" class="form-control input-field" type="text"
                                                   value="{{ old('title') }}" id="title"/>
                                        </div>
                                        <label id="error-subject" class="error"
                                               style="color: red; display: none;">{{ trans('core::message.This field is required') }}</label>
                                    </div>
                                    <div class="form-group">
                                        <label for="content"
                                               class="control-label">{{ trans('emailnoti::view.Content') }} <em
                                                    style="color: red">*</em></label>
                                        <div class="">
                                    <textarea id="content" name="content" class="form-control"
                                              rows="5">{{ old('content') }}</textarea>
                                        </div>
                                        <label id="error-content" class="errorContent error"
                                               style="color: red;">{{ trans('core::message.This field is required') }}</label>
                                    </div>
                                    <div class="form-group">
                                        <div class="">
                                            <label for="content"
                                                   class="control-label">{{ trans('notify::view.available_title') }} <em
                                                        style="color: red">*</em></label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="date_type"
                                                       {{ old('date_type') == 1 || strlen(old('date_type')) == 0 ? 'checked' : null }}
                                                       value="1"
                                                       id="date_type1">
                                                <label class="form-check-label" for="date_type1">{{ trans('notify::view.send_now') }}</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="date_type" value="2"
                                                       {{ old('date_type') == 2 ? 'checked' : null }}
                                                       id="date_type2">
                                                <label class="form-check-label" for="date_type2">{{ trans('notify::view.choose_date') }}</label>
                                            </div>
                                            <div class='input-group date'
                                                 style="{{ old('date_type') == 2 ? null  : 'display: none' }}"
                                                 id='datepicker_start_at'>
                                                <input type='text' class="form-control"
                                                       name="available_at"
                                                       {{ old('date_type') == 2 ? null : 'disabled' }}
                                                       value="{{ old('available_at', \Carbon\Carbon::now()->addMinute(2)->format('d-m-Y H:i')) }}"
                                                       id="available_at"/>
                                                <span class="input-group-addon">
                                                    <span class="glyphicon glyphicon-calendar"></span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-12 text-center">
                                    <input class="form-control input-field" type="hidden" name="check_comment"
                                           id="check_comment" value="false"/>
                                    <input type="submit" style="width: 100px" id="btn" class="btn-add" name="submit"
                                           value="{{ trans('notify::view.send') }}"/>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div> <!-- end notification position manage --></div>
    </div>
    </div>
@endsection
@section('script')
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="{{ URL::asset('lib/jQueryTree/js/jquery.tree.min.js') }}"></script>
    <script src="{{ URL::asset('lib/tokenize2/js/tokenize2.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
    <script src="{{ URL::asset('notify/js/app.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script src="{{ asset('asset_notify/js/create-edit.js') }}"></script>
@endsection
