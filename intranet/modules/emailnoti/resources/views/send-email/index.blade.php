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
    <link rel="stylesheet" href="{{ URL::asset('team/css/style.css') }}" />
    <link rel="stylesheet" href="{{ URL::asset('emailnoti/css/style.css') }}" />
@endsection

@section('content')
    <div class="team-position-wrapper hight-same">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"></h3>
            </div>
            <div class="row team-list-action box-body">
                <form id="form-post-edit" method="post" action="{{ route('emailnoti::email.notification.send-email') }}"
                      class="" autocomplete="off" enctype="multipart/form-data">
                    {!! csrf_field() !!}
                    <div class="form-group">
                        <div class="col-md-8 col-md-offset-2">
                            <div>
                                <label class="control-label">{{ trans('emailnoti::view.limit excel') }}</label>
                                <input name="limit" class="form-control input-field" type="number" value="1000"/>
                            </div>
                            <div class="form-group">
                                <label for="title" style="margin-bottom: 0px"
                                       class="control-label">{{ trans('emailnoti::view.From email') }}: </label>
                                <label style="margin-bottom: 0px">
                                    <input type="radio" name="tab" value="2" style="margin-left: 10px; margin-top: 5px"
                                           class="send-mail" checked="checked"/>
                                    {{ trans('emailnoti::view.Company Email') }}
                                </label>
                                <label style="margin-bottom: 0px">
                                    <input type="radio" name="tab" value="1" style="margin-left: 10px; margin-top: 5px"
                                           class="send-mail"/>
                                    {{ trans('emailnoti::view.Personal Email') }}
                                </label>
                                <br><em class="comment hidden" style="color: red"> {!! trans('emailnoti::view.suggest personal email',
                                ['url' => route('team::member.profile.index', ['employeeId' => $authId, 'type' => 'api'])]) !!}</em>
                            </div>
                            <div class="form-group">
                                <label for="title" class="control-label required">{{ trans('emailnoti::view.To') }}</label>
                                <div class="row">
                                    <div class="col-md-2 col-sm-12">
                                        <button type="button" id="select-team" class="btn btn-primary btn-submit-sp" data-toggle="modal" onclick="hideErrorTitle()" data-target="#getTeamModal">{{ trans('emailnoti::view.Select team') }}</button>
                                    </div>
                                    <div class="col-md-10 col-sm-12">
                                        <input name="team_name" class="form-control input-field" type="text" id="team_name"
                                            value="" readonly />
                                    </div>
                                    <input class="form-control input-field" type="hidden" name="team_list" id="team_list" value="" />
                                    <!-- Modal -->
                                    <div id="getTeamModal" class="modal fade" role="dialog">
                                        <div class="modal-dialog">
                                            <div class="modal-content" style="border-radius: 10px;">
                                                <div class="modal-header">
                                                    <button type="button" class="close" onclick="closeSelectTeam()">&times;</button>
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
                                                    <label id="error-select-team" class="error" style="color: red; display: none;">{{ trans('emailnoti::view.You have not select team') }}</label>
                                                </div>
                                                <div class="modal-footer">
                                                    <div class="pull-left">
                                                        <button type="button" class="btn btn-primary btn-sm" onclick="selectAll()">{{ trans('emailnoti::view.Select all') }}</button>
                                                        <button type="button" class="btn bg-olive btn-sm" onclick="deSelect()">{{ trans('emailnoti::view.Deselect') }}</button>
                                                    </div>
                                                    <button type="button" class="btn btn-default" onclick="closeSelectTeam()">{{ trans('emailnoti::view.Close') }}</button>
                                                    <button type="button" id="select_team" class="btn btn-primary" onclick="return getTeam()">{{ trans('emailnoti::view.Save') }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="csv_tet" class="col-md-2 col-lg-2 control-label required">{{ trans('event::view.File(excel, csv)') }}</label>
                                <div class="col-md-5 col-lg-12">
                                    <input class="form-control" type="file" name="csv_file" id="csv_file">
                                    <em style="color: red">{{ trans('event::view.Note:') }} {{ trans('event::view.File uploads must not be more than 1000 emails') }}</em>
                                </div>
                            </div>
                            <div class="form-group" >
                                <label for="title" class="control-label required">{{ trans('emailnoti::view.To other') }}</label>
                                <input class="form-control input-field" type="hidden" name="to_other" id="to_other" value="" />
                                <div class="" id="get_email">
                                    <!-- <select class="tokenize-callable-to-other" multiple hidden id="getEmployees">
                                        @if (count($list_employee))
                                            @foreach($list_employee as $row)
                                                <option value="{{$row->email}}">{{$row->email}}</option>
                                            @endforeach
                                        @endif
                                    </select> -->
                                    <select class="tokenize-callable-to-other" multiple hidden id="getEmployees">

                                    </select>
                                </div>
                                <label id="title-error" class="error" for="title" style=" display: none;">{{ trans('emailnoti::view.Please specify at least one recipient2') }}</label>
                                <label id="mail-error" class="error" for="title" style=" display: none;">{{ trans('emailnoti::view.Not a valid e-mail address') }}</label>
                            </div>
                            <div class="form-group">
                                <label for="title" class="control-label required">{{ trans('emailnoti::view.Subject') }} <em>*</em></label>
                                <div class="">
                                    <input name="subject" class="form-control input-field" type="text" id="subject"
                                        value="{{$subject}}" />
                                </div>
                                <label id="error-subject" class="error" style="color: red; display: none;">{{ trans('core::message.This field is required') }}</label>
                            </div>
                            <div class="form-group">
                                <label for="content" class="control-label">{{ trans('emailnoti::view.Content') }} <em style="color: red">*</em></label>
                                <div class="">
                                    <textarea id="content" name="content" class="form-control" style="height: 300px">{{$content}}</textarea>
                                </div>
                                <label id="error-content" class="errorContent error" style="color: red;">{{ trans('core::message.This field is required') }}</label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12 text-center">
                            <input class="form-control input-field" type="hidden" name="check_comment" id="check_comment" value="false" />
                            <input type="submit" onclick="return submitForm();" id="btn" class="btn-add" name="submit" value="{{ trans('emailnoti::view.Send email') }}" />
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="box box-info">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        <h4><a href="{{ asset('emailnoti/files/csv_file.xlsx') }}">{{ trans('emailnoti::view.Format excel file') }} <i class="fa fa-download"></i></a></h4>
                        <br><br>
                        <img src="{{ URL::asset('event/images/template/notification.png') }}" class="img-responsive"/>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- end notification position manage -->
</div>
@endsection

@section('script')
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="{{ URL::asset('lib/jQueryTree/js/jquery.tree.min.js') }}"></script>
    <script src="{{ URL::asset('lib/tokenize2/js/tokenize2.min.js') }}"></script>
    <script src="{{ URL::asset('lib/ckeditor/ckeditor.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
    <script src="{{ URL::asset('emailnoti/js/emailnoti.js') }}"></script>

    <script type="text/javascript">
        function selectAll() {
            var checkboxes = document.getElementsByTagName('input');
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i].type == 'checkbox') {
                    checkboxes[i].checked = true;
                }
            }
            var x = document.getElementById('error-select-team');
            x.style.display = 'none';
        }

        function deSelect() {
            var checkboxes = document.getElementsByTagName('input');
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i].type == 'checkbox') {
                    checkboxes[i].checked = false;
                }
            }
        }

        function getTeam() {
            var team_id = [];
            var team_name = [];
            $.each($("input[name='team']:checked"), function(){
                team_id.push($(this).val());
                team_name.push($(this).next('span').text());
            });
            document.getElementById('team_list').value = team_id.join(", ");
            document.getElementById('team_name').value = team_name.join(", ");

            $('#getTeamModal').modal('hide');

            team_list = document.getElementById('team_list').value;
            to_other = document.getElementById('to_other').value;

            check_comment = document.getElementById('check_comment').value;
            if(check_comment == 'true')
            {
                if (team_list.trim().length == 0 && to_other.trim().length == 0)
                {
                    var error_title = document.getElementById('title-error');
                    error_title.style.display = 'block';
                }
            }
        }

        function closeSelectTeam() {
            var team_id = [];
            str_team_id = document.getElementById('team_list').value;
            team_id = str_team_id.split(",");

            if(team_id.length == 0)
            {
                var checkboxes = document.getElementsByTagName('input');
                for (var i = 0; i < checkboxes.length; i++)
                {
                    if (checkboxes[i].type == 'checkbox')
                    {
                        checkboxes[i].checked = false;
                    }
                }
            } else {
                var index_checkbox = [];
                var checkboxes = document.getElementsByTagName('input');
                for (var i = 0; i < checkboxes.length; i++)
                {
                    if (checkboxes[i].type == 'checkbox')
                    {
                        checkboxes[i].checked = false;
                        for(var k = 0; k < team_id.length; k++)
                        {
                            if (team_id[k].trim() == checkboxes[i].value.trim())
                            {
                                checkboxes[i].checked = true;
                            }
                        }
                    }
                }
            }
            $('#getTeamModal').modal('hide');
            team_list = document.getElementById('team_list').value;
            to_other = document.getElementById('to_other').value;
            file = document.getElementById('csv_file').value;

            if (team_list.trim().length == 0 && to_other.trim().length == 0 && file.trim().length == 0)
            {
                var error_title = document.getElementById('title-error');
                error_title.style.display = 'block';
            }
        }
    </script>
    <script type="text/javascript">
        jQuery(function ()
        {
            $('.tokenize-callable-to-other').on('tokenize:search', function(e, value){
                $.ajax({
                    data: {key_search: value},
                    type: "GET",
                    url: "{{ route('emailnoti::email.notification.search-email') }}",
                    success: function (result) {
                        var option = "";
                        $.each(result, function(key, value) {
                            option += "<option value='"+value['id']+"'>"+value['text']+"</option>";
                        })
                        $("#getEmployees").html(option);
                    }
                });
            });
        });
    </script>
    <script>
        $('body').on('change', '.send-mail', function () {
            if ($(this).val() == 1) {
                $('.comment').removeClass('hidden')
            } else {
                $('.comment').addClass('hidden').find('input').val('');
            }
        })
    </script>

@endsection
