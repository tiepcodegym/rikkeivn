<?php 
use Rikkei\Core\View\View as ViewHelper;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\ProjectWOBase;
use Rikkei\Project\Model\RiskAttach;
use Rikkei\Project\View\ProjectGitlab;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Project\Model\Task;
use Rikkei\Core\View\View;

$configGit = (array) CoreConfigData::getGitlabApi();
$configRedmine = (array) CoreConfigData::getRemineApi();
$allNameTab = Task::getAllNameTabWorkorder();
$projectUrlGiblab = $configGit['project_url'].$sourceServer->id_git;
$projectUrlRedmine = $configRedmine['project_url'].$sourceServer->id_redmine;
$sonarUrl = CoreConfigData::getValueDb('api.sonar.url') . '/dashboard?id=';
$jenkinsUrl = CoreConfigData::getValueDb('api.jenkins.url') . '/';
$urlPathDev = config('project.sonar.jenkins.path_suffix_dev');   
$urlPathPre = config('project.sonar.jenkins.path_suffix_preview');
$responseAttach = RiskAttach::getAttachs($project->id, RiskAttach::TYPE_OTHERS);
?>
<div class="table-content-{{$allNameTab[Task::TYPE_WO_CM_PLAN]}}">
    <div class="box box-info box-solid collapsed-box">
        <div class="box-header with-border">
            <h3 class="box-title">{{trans('project::view.Source Code Repository')}} &amp; {{trans('project::view.Issue Tracker')}}</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                </button>
            </div>
            <!-- /.box-tools -->
        </div>
        <!-- /.box-header -->
        <div class="box-body" style="display: none;">
            <div class="row">
                <div class="form-group col-sm-6">
                    <label class="col-sm-4 control-label">{{trans('project::view.LoC: Baseline')}}</label>
                    <div class="col-sm-8">
                        @if(isset($permissionEdit) && $permissionEdit) 
                        <input type="text" class="form-control scope lineofcode_baseline input-basic-info" name="lineofcode_baseline" id="lineofcode_baseline" value="{{$project->projectMeta->lineofcode_baseline}}">
                        @else
                        <p class="form-control-static">{{$project->projectMeta->lineofcode_baseline}}</p>
                        @endif
                    </div>
                </div>
                <div class="form-group col-sm-6">
                    <label class="col-sm-4 control-label">{{trans('project::view.LoC: Current')}}</label>
                    <div class="col-sm-8">
                        @if(isset($permissionEdit) && $permissionEdit) 
                        <input type="text" class="form-control scope lineofcode_current input-basic-info" name="lineofcode_current" id="lineofcode_current" value="{{$project->projectMeta->lineofcode_current}}">
                        @else
                        <p class="form-control-static">{{$project->projectMeta->lineofcode_current}}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-sm-12">
                    <label class="col-sm-4 col-md-2 control-label">1. {{trans('project::view.Source Code Repository')}}</label>
                    <div class="col-sm-8 col-md-7">
                    @if ($permissionEdit)
                        <div class="radio">
                            <label>
                                <input class="radio-toggle-click" type="radio" id="is_check_git" name="is_check_git" value="1" data-id="is_check_git" {{ $sourceServer->is_check_git ? 'checked' : '' }}/> {{trans('project::view.Use Rikkei GitLab')}}
                            </label>
                        </div>
                        <div class="radio-toggle-click-show-is_check_git {{($projectUrlGiblab && $sourceServer->is_check_git) ? '' : 'display-none'}} radio-is_check_git">
                            <a href="{{ $projectUrlGiblab }}" target="_blank">{{ $projectUrlGiblab }}</a>
                            <button class="btn btn-primary btn-sync-source-server" data-type="git" id="sync_project_git">{{trans('project::view.Create project gitlab')}} <i class="fa fa-spin fa-refresh hidden sync-loading"></i></button>
                        </div>
                    @else
                        @if ($sourceServer->is_check_git)
                        <p class="form-control-static">{{trans('project::view.Gitlab Rikkei')}}: <a href="{{ $projectUrlGiblab }}" target="_blank">{{ $projectUrlGiblab }}</a></p>
                        @endif
                    @endif
                    @if ($permissionEdit)
                        <div class="radio">
                            <label>
                              <input type="radio" name="is_check_git" class="radio-toggle-click" value="0" data-id="is_check_git_external" {{ !$sourceServer->is_check_git ? 'checked' : '' }} > {{trans('project::view.External Repository')}}
                            </label>
                        </div>
                        <div class="radio-toggle-click-show-is_check_git {{$sourceServer->is_check_git ? 'display-none' : ''}} radio-is_check_git_external">
                            <input type="text" class="form-control input-basic-info not-approved id-source-server" id="id_git_external" placeholder="{{trans('project::view.External Repository URL')}}" name="ss[id_git_external]" value="{{ $sourceServer->id_git_external }}">
                        </div>
                    @else
                        @if (!$sourceServer->is_check_git)
                        <p class="form-control-static">{{trans('project::view.External Repository')}}: {{ $sourceServer->id_git_external }}</p>
                        @endif
                    @endif
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-sm-12">
                    <label class="col-sm-4 col-md-2 control-label">2. {{trans('project::view.Issue Tracker')}}</label>
                    <div class="col-sm-8 col-md-7">
                    @if ($permissionEdit)
                        <div class="radio">
                            <label>
                                <input class="radio-toggle-click" type="radio" id="is_check_redmine" name="is_check_redmine" value="1" data-id="is_check_redmine" {{ $sourceServer->is_check_redmine ? 'checked' : '' }}/> {{trans('project::view.Use Rikkei Redmine')}}
                            </label>
                        </div>
                        <div class="radio-toggle-click-show-is_check_redmine {{($projectUrlRedmine && $sourceServer->is_check_redmine) ? '' : 'display-none' }} radio-is_check_redmine">
                            <a href="{{ $projectUrlRedmine }}" target="_blank">{{ $projectUrlRedmine }}</a>
                            <button class="btn btn-primary btn-sync-source-server" data-type="redmine" id="sync_project_redmine">{{trans('project::view.Sync project in server')}} <i class="fa fa-spin fa-refresh hidden sync-loading"></i></button>
                        </div>
                    @else
                        @if ($sourceServer->is_check_redmine)
                        <p class="form-control-static">{{trans('project::view.Redmine Rikkei')}}: <a href="{{ $projectUrlRedmine }}" target="_blank">{{ $projectUrlRedmine }}</a></p>
                        @endif
                    @endif
                    @if ($permissionEdit)
                        <div class="radio">
                            <label>
                              <input type="radio" name="is_check_redmine" class="radio-toggle-click" value="0" data-id="is_check_redmine_external" {{ !$sourceServer->is_check_redmine ? 'checked' : '' }} > {{trans('project::view.External Issue Tracker')}}
                            </label>
                        </div>
                        <div class="radio-toggle-click-show-is_check_redmine {{$sourceServer->is_check_redmine ? 'display-none' : ''}} radio-is_check_redmine_external">
                            <input type="text" class="form-control input-basic-info not-approved id-source-server" id="id_redmine_external" placeholder="{{trans('project::view.External Issue Tracker URL')}}" name="ss[id_redmine_external]" value="{{ $sourceServer->id_redmine_external }}">
                        </div>
                    @else
                        @if (!$sourceServer->is_check_redmine)
                        <p class="form-control-static">{{trans('project::view.External Issue Tracker')}}: {{ $sourceServer->id_redmine_external }}</p>
                        @endif
                    @endif
                    </div>
                </div>
            </div>
            <!-- sonar -->
            <div class="row">
                <div class="form-group col-sm-12">
                    @if ($sourceServer->id_sonar)
                        <label class="col-sm-4 col-md-2 control-label">3. Sonar
                            <a target="_blank" href="{!!route('help::display.help.view', ['id' => 36])!!}"
                               data-toggle="tooltip" title="{!!trans('project::view.Help')!!}">
                                <i class="fa fa-fw fa-question-circle"></i>
                            </a>
                        </label>
                        <div class="col-sm-8 col-md-10">
                            <p>
                                <span>{{trans('project::view.Sonar project')}}</span>&nbsp;
                                <a href="{{ $sonarUrl . $sourceServer->id_sonar }}" target="_blank">{{ $sonarUrl . $sourceServer->id_sonar }}</a>
                                @if ($permissionEdit)
                                    <br/>
                                    <button class="btn-add post-ajax" data-url-ajax="{{route('call_api::sonar.project.create', ['id' => $project->id]) }}">
                                        {{trans('project::view.Create project sonar')}}
                                        <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh-btn"></i>
                                    </button>
                                @endif
                            </p>
                            <p>
                            <table class="table">
                                <tr>
                                    <td>{{trans('project::view.Jenkins project')}}&nbsp;&nbsp;</td>
                                    <td>{{trans('project::view.Develop')}}: <a href="{{ $jenkinsUrl . $sourceServer->id_jenkins . $urlPathDev }}" target="_blank">{{ $jenkinsUrl . $sourceServer->id_jenkins . $urlPathDev }}</a></td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>{{trans('project::view.Preview')}}: <a href="{{ $jenkinsUrl . $sourceServer->id_jenkins . $urlPathPre }}" target="_blank">{{ $jenkinsUrl . $sourceServer->id_jenkins . $urlPathPre }}</a></td>
                                </tr>
                            </table>
                                @if ($permissionEdit)
                                    <br/>
                                    <button class="btn-add post-ajax" data-url-ajax="{{route('call_api::sonar.project.create', ['id' => $project->id, 'type' => 'jenkins']) }}">
                                        {{trans('project::view.Create project jenkins')}}
                                        <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh-btn"></i>
                                    </button>
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>
            <!-- /sonar -->
        </div>
        <!-- /.box-body -->
    </div>
    <div class="box box-info box-solid collapsed-box">
        <div class="box-header with-border">
            <h3 class="box-title">{{trans('project::view.Environments')}}</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                </button>
            </div>
          <!-- /.box-tools -->
        </div>
        <!-- /.box-header -->

        <div class="box-body" style="display: none;">
            <div class="row">
                <div class="form-group col-sm-6">
                    <label class="col-md-4 control-label">{{trans('project::view.Schedule link')}}</label>
                    <div class="col-md-8">
                        @if(isset($permissionEdit) && $permissionEdit) 
                        <input type="text" class="form-control scope schedule_link input-basic-info" name="schedule_link" id="schedule_link" value="{{$project->projectMeta->schedule_link}}">
                        @else
                        <p class="form-control-static">{{$project->projectMeta->schedule_link}}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-sm-6">
                    <label class="col-md-4 control-label">{{trans('project::view.Development')}}</label>
                    <div class="col-md-8">
                        @if(isset($permissionEdit) && $permissionEdit) 
                        <textarea class="form-control scope env_dev input-basic-info" rows="5" name="env_dev" id="env_dev">{{$project->projectMeta->env_dev}}</textarea>
                        @else
                        <p class="form-control-static">{!! View::nl2br($project->projectMeta->env_dev) !!}</p>
                        @endif
                    </div>
                </div>
                <div class="form-group col-sm-6">
                    <label class="col-md-4 control-label">{{trans('project::view.Test')}}</label>
                    <div class="col-md-8">
                        @if(isset($permissionEdit) && $permissionEdit) 
                        <textarea class="form-control scope scope_env_test input-basic-info" rows="5" name="scope_env_test" id="scope_env_test">{{$project->projectMeta->scope_env_test}}</textarea>
                        @else
                        <p class="form-control-static">{!! View::nl2br($project->projectMeta->scope_env_test) !!}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-sm-6">
                    <label class="col-md-4 control-label">{{trans('project::view.Staging')}}</label>
                    <div class="col-md-8">
                        @if(isset($permissionEdit) && $permissionEdit) 
                        <textarea class="form-control scope env_staging input-basic-info" rows="5" name="env_staging" id="env_staging">{{$project->projectMeta->env_staging}}</textarea>
                        @else
                        <p class="form-control-static">{!! View::nl2br($project->projectMeta->env_staging) !!}</p>
                        @endif
                    </div>
                </div>
                <div class="form-group col-sm-6">
                    <label class="col-md-4 control-label">{{trans('project::view.Production')}}</label>
                    <div class="col-md-8">
                        @if(isset($permissionEdit) && $permissionEdit) 
                        <textarea class="form-control scope env_production input-basic-info" rows="5" name="scope_env_tes" id="env_production">{{$project->projectMeta->env_production}}</textarea>
                        @else
                        <p class="form-control-static">{!! View::nl2br($project->projectMeta->env_production) !!}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    <!-- /.box-body -->
    </div>
</div>
<div class="box box-info box-solid collapsed-box">
    <div class="box-header with-border">
        <h3 class="box-title">{{trans('project::view.Others')}}</h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
        </div>
        <!-- /.box-tools -->
    </div>
    <!-- /.box-header -->
    <div class="box-body" style="display: none;">
        <div class="form-group">
            <form id="form-plan-comment" method="post" autocomplete="off" data-callback-success="commentSuccess" novalidate="novalidate" enctype="multipart/form-data">
        <div class="col-md-8 col-md-offset-2">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="project_id" value="{{ $project->id }}">
            @if(isset($permissionEdit) && $permissionEdit)
                <textarea class="form-control scope others input-basic-info" name="others" id="others" rows="7">{{$project->projectMeta->others}}</textarea>

                <div id="append_url">
                    @if (isset($responseAttach))
                        @foreach($responseAttach as $a)
                            <div data-file="{{$a->id}}"><a href="{{ route('project::issue.download', ['id' => $a->id]) }}">{{ basename($a->path) }}</a>
                                <span><button type="button" class="delete-file" data-id="{{$a->id}}"><i class="fa fa-remove" style="font-size:15px; color:red;"></i></button></span></div>
                        @endforeach
                    @endif
                    <div>
                        <input type="file" id="attach_comment" name="attach_comment[]" multiple />
                    </div>
                </div>
            @else
                <p class="form-control-static">{!! View::nl2br($project->projectMeta->others) !!}</p>
            @endif
        </div>
        <button type="button" class="btn-add" id="save_file">
            {{trans('project::view.Save')}}</button>
    </form>
</div>
</div>
    <!-- /.box-body -->
</div>
<script>
    var projectId = '{{ $project->id }}';
    var _token = '{{ csrf_token() }}';
    var urlSaveFilePlan = '{{ route('project::plan.save.comment') }}';
    var urlDeleteFile = '{{ route('project::project.delete.file') }}';

    var $char = $('#others').val();
    $("#others").change(function() {
        $char = $(this).val();
    });
    $("#save_file").click(function() {
        var fileUpload = $("#attach_comment").get(0);
        var files = fileUpload.files;
        var fd = new FormData();
        var ins = files.length;
        for (var x = 0; x < ins; x++) {
            fd.append("attach_comment[]", files[x]);
        }
        fd.append('projectId', projectId);
        fd.append('_token', token);
        $.ajax({
            url: urlSaveFilePlan,
            type: 'post',
            mimeType: "multipart/form-data",
            data: fd,
            contentType: false,
            processData: false,
            success: function (response) {
                var html = '';
                var obj = jQuery.parseJSON(response);
                if (obj.message_error) {
                    $.each(obj.message_error, function (index, val) {
                        html += `<div class="text-danger">${val[0]}</div>`
                        $('#append_url').html(html);
                    });
                } else {
                    $.each(obj, function (index, val) {
                        var urlDownload = '{{ route('project::issue.download', ":id") }}';
                        urlDownload = urlDownload.replace(':id', val.id);
                        html += `<div data-file="${val.id}"><a href="` +urlDownload+` ">${val.path}</a>
                        <span><button type="button" class="delete-file" data-id="${val.id}"><i class="fa fa-remove" style="font-size:15px; color:red;"></i></button></span></div>`
                        // $('#append_url').html(html);
                    });
                }
                html += '<input type="file" id="attach_comment" name="attach_comment[]" multiple />';
                $('#append_url').html(html);
            },
            error: function () {
                let html = 'Dung lượng file quá lớn hoặc file không hợp lệ';
                $('#append_url').html(html);
            }
        });
    });

    $(document).on("click", ".delete-file", function () {
        var fileId = $(this).attr('data-id');
        $.ajax({
            url: urlDeleteFile,
            method: "POST",
            dataType: "json",
            data: {
                _token: token,
                fileId: fileId,
            },
            success: function(data) {
                $("div[data-file='" + fileId + "']").remove();
            }
        });
    });
</script>
