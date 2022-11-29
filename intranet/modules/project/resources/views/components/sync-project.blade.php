<?php 
use Rikkei\Core\View\View as ViewHelper;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\ProjectWOBase;
use Rikkei\Project\View\ProjectGitlab;
use Rikkei\Core\Model\CoreConfigData;

$checkShowCheckboxIndentify = false;
if (($checkEdit && isset($permissionEdit) && $permissionEdit) || !$checkEdit) {
  $checkShowCheckboxIndentify = true;
}
$projectGitlabProcess = ProjectGitlab::getInstance()->isProcess($project->id);
$configGit = (array) CoreConfigData::getGitlabApi();
$configRedmine = (array) CoreConfigData::getRemineApi();
?>
<div class="row">
  <div class="col-md-6">
     <div class="form-group position-relative form-label-left">
        <label for="lineofcode_baseline" class="col-sm-3 control-label">{{trans('project::view.LoC: Baseline')}}</label>
        <div class="col-md-9">
        <?php
          $oldBaseline = old('lineofcode_baseline');
          if (isset($oldBaseline)) {
            $oldBaseline = true;
          } else {
            $oldBaseline = false;
          }
        ?>  
          @if($checkEdit)
            @if(isset($permissionEdit) && $permissionEdit) 
            <span>                                  
                <input type="text" class="form-control input-field" id="lineofcode_baseline" name="lineofcode_baseline" value="{{$oldBaseline ? old('lineofcode_baseline') : $projectMeta->lineofcode_baseline}}"  placeholder="{{trans('project::view.LoC: Baseline')}}">
                @if($errors->has('lineofcode_baseline'))
                    <label id="lineofcode_baseline-error" class="padding-left-15 error" for="lineofcode_baseline">{{$errors->first('lineofcode_baseline')}}</label>
                @endif
            </span>
            @else
                <label for="lineofcode_baseline" class="control-label">{{$checkEdit ? $projectMeta->lineofcode_baseline: ''}}</label>
            @endif
          @else
            <span>                                  
              <input type="text" class="form-control input-field" id="lineofcode_baseline" name="lineofcode_baseline" placeholder="{{trans('project::view.LoC: Baseline')}}" value="{{$oldBaseline ? old('lineofcode_baseline') : ''}}">
              @if($errors->has('lineofcode_baseline'))
                <label id="lineofcode_baseline-error" class="padding-left-15 error" for="lineofcode_baseline">{{$errors->first('lineofcode_baseline')}}</label>
              @endif
              </span>
          @endif
        </div>
     </div>
  </div>
  <div class="col-md-6">
     <div class="form-group position-relative form-label-left">
        <label for="lineofcode_current" class="col-sm-3 control-label">{{trans('project::view.LoC: Current')}}</label>
        <div class="col-md-9">
          <?php
            $oldCurrent = old('lineofcode_current');
            if (isset($oldCurrent)) {
              $oldCurrent = true;
            } else {
              $oldCurrent = false;
            }
          ?>  
          @if($checkEdit)
            @if(isset($permissionEdit) && $permissionEdit) 
                <span>                                  
                    <input type="text" class="form-control input-field" id="lineofcode_current" name="lineofcode_current" value="{{$oldCurrent ? old('lineofcode_current') : $projectMeta->lineofcode_current}}"  placeholder="{{trans('project::view.LoC: Current')}}">
                    @if($errors->has('lineofcode_current'))
                        <label id="lineofcode_current-error" class="padding-left-15 error" for="lineofcode_current">{{$errors->first('lineofcode_current')}}</label>
                    @endif
                </span>
            @else
            <label for="lineofcode_current" class="control-label">{{$checkEdit ? $projectMeta->lineofcode_current: ''}}</label>
            @endif
          @else
            <span>                                  
                <input type="text" class="form-control input-field" id="lineofcode_current" name="lineofcode_current" placeholder="{{trans('project::view.LoC: Current')}}" value="{{$oldCurrent ? old('lineofcode_current') : ''}}">
                @if($errors->has('lineofcode_current'))
                    <label id="lineofcode_current-error" class="padding-left-15 error" for="lineofcode_current">{{$errors->first('lineofcode_current')}}</label>
                @endif
            </span>
          @endif
        </div>
     </div>
  </div>
</div>

<!-- remdmine indentify -->
<div class="row">
    <div class="col-md-12">
        <div class="form-group position-relative form-label-left">
            <label class="col-sm-2 control-label">{{trans('project::view.Redmine identifier')}}</label>
            <div class="col-md-10 radio-toggle-click-wrapper">
                @if ($permissionEdit)
                    <div class="form-group position-relative form-label-left row">
                        <div class="col-sm-2 control-label">
                            <input type="radio" class="input-radio-inline radio-toggle-click" 
                                id="is_check_redmine" name="ss[is_check_redmine]" value="1" 
                                {{ $sourceServer->is_check_redmine ? 'checked' : '' }} />
                            <label for="is_check_redmine">{{trans('project::view.Redmine Rikkei')}}</label>
                        </div>
                        <div class="col-md-10 radio-toggle-click-show" data-id="is_check_redmine">
                            <div class="input-label">
                                @if ($sourceServer->id_redmine)
                                    {{ $sourceServer->id_redmine }}&nbsp;&nbsp;&nbsp;
                                    @if ($configRedmine['url'])
                                        <button class="btn btn-primary btn-sync-source-server" data-type="redmine" 
                                            id="sync_project_redmine">{{trans('project::view.Sync project in server')}} <i class="fa fa-spin fa-refresh hidden sync-loading"></i></button>
                                        <p class="hint-note">{{ trans('project::view.create project, get number leakage bug and defect bug in server') . ' ' . $configRedmine['url'] }}</p>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="form-group position-relative form-label-left row">
                        <div class="col-sm-2 control-label">
                            <input type="radio" class="input-radio-inline radio-toggle-click" id="is_check_redmine_external" 
                                name="ss[is_check_redmine]" value="0" 
                                {{ !$sourceServer->is_check_redmine ? 'checked' : '' }} />
                            <label for="is_check_redmine_external">{{trans('project::view.Redmine external')}}</label>
                        </div>
                        <div class="col-md-6 radio-toggle-click-show" data-id="is_check_redmine_external">
                            <input type="text" class="form-control input-field" id="id_redmine_external" name="ss[id_redmine_external]" value="{{ $sourceServer->id_redmine_external }}" />
                        </div>
                    </div>
                @else
                    @if ($sourceServer->is_check_redmine)
                        <div class="form-group position-relative form-label-left row">
                            <div class="col-sm-12 control-label">
                                <label>{{trans('project::view.Redmine Rikkei')}}: {{ $sourceServer->id_redmine }}</label>
                            </div>
                        </div>
                    @else
                        <div class="form-group position-relative form-label-left row">
                            <div class="col-sm-12 control-label">
                                <label>{{trans('project::view.Redmine external')}}: {{ $sourceServer->id_redmine_external }}</label>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
<!-- end remdmine indentify -->

<!-- git indentify -->
<div class="row">
    <div class="col-md-12">
        <div class="form-group position-relative form-label-left">
            <label class="col-sm-2 control-label">{{trans('project::view.Gitlab repo')}}</label>
            <div class="col-md-10 radio-toggle-click-wrapper">
                @if ($permissionEdit)
                    <div class="form-group position-relative form-label-left row">
                        <div class="col-sm-2 control-label">
                            <input type="radio" class="input-radio-inline radio-toggle-click" 
                                id="is_check_git" name="ss[is_check_git]" value="1" 
                                {{ $sourceServer->is_check_git ? 'checked' : '' }} />
                            <label for="is_check_git">{{trans('project::view.Gitlab Rikkei')}}</label>
                        </div>
                        <div class="col-md-10 radio-toggle-click-show" data-id="is_check_git">
                            <div class="input-label">
                                @if ($sourceServer->id_git)
                                    {{ $sourceServer->id_git }}&nbsp;&nbsp;&nbsp;
                                    @if ($configGit['url'])
                                        <button class="btn btn-primary btn-sync-source-server sync-parent{{ $projectGitlabProcess ? ' hidden' : ''}}"
                                            data-type="git" 
                                            id="sync_project_git">{{trans('project::view.Sync project in server')}} <i class="fa fa-spin fa-refresh hidden sync-loading"></i>
                                        </button>
                                        <button class="btn btn-primary btn-sync-source-server sync-child{{ $projectGitlabProcess ? '' : ' hidden'}}" id="sync_project_git">{{trans('project::view.Syncing ...')}}</button>
                                        <p class="hint-note">{{ trans('project::view.create project, get loc in server') . ' ' . $configGit['url'] }}</p>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="form-group position-relative form-label-left row">
                        <div class="col-sm-2 control-label">
                            <input type="radio" class="input-radio-inline radio-toggle-click" id="is_check_git_external" 
                                name="ss[is_check_git]" value="0" 
                                {{ !$sourceServer->is_check_git ? 'checked' : '' }} />
                            <label for="is_check_git_external">{{trans('project::view.Git repo external')}}</label>
                        </div>
                        <div class="col-md-6 radio-toggle-click-show" data-id="is_check_git_external">
                            <input type="text" class="form-control input-field" id="id_git_external" name="ss[id_git_external]" value="{{ $sourceServer->id_git_external }}" />
                        </div>
                    </div>
                @else
                    @if ($sourceServer->is_check_redmine)
                        <div class="form-group position-relative form-label-left row">
                            <div class="col-sm-12 control-label">
                                <label>{{trans('project::view.Gitlab Rikkei')}}: {{ $sourceServer->id_git }}</label>
                            </div>
                        </div>
                    @else
                        <div class="form-group position-relative form-label-left row">
                            <div class="col-sm-12 control-label">
                                <label>{{trans('project::view.Git repo external')}}: {{ $sourceServer->id_git_external }}</label>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
<!-- end git indentify -->