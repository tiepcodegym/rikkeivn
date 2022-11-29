<?php
use Rikkei\Core\Model\EmailQueue;
use Rikkei\SlideShow\View\RunBgSlide;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\ManageTime\View\View as MView;
?>

@if ($isScopeCompany)
<div class="col-md-12">
    <div class="box box-info">
        <div class="box-body">
            <div class="row">
                <div class="col-md-11">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group form-label-left row">
                                @if (EmailQueue::checkProcessing())
                                <label class="col-sm-3 control-label">Delete process email queue</label>
                                <div class="col-md-9">
                                    <button class="btn-delete post-ajax" data-url-ajax="{{ route('core::setting.system.data.delete.email.process.queue') }}" 
                                            type="button">{{ trans('core::view.Delete') }}<i class="fa fa-spin fa-refresh hidden submit-ajax-refresh-btn"></i></button>
                                </div>
                                @else
                                <label class="col-sm-3 control-label">{{ trans('core::view.Delete email queue data') }}</label>
                                <div class="col-md-9">
                                    <button class="btn-delete post-ajax" data-url-ajax="{{ route('core::setting.system.data.delete.email.queue.data') }}" 
                                            type="button">{{ trans('core::view.Delete') }} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh-btn"></i></button>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group form-label-left row">
                                <label class="col-sm-3 control-label">{{ trans('core::view.Delete acl draft') }}</label>
                                <div class="col-md-9">
                                    <button class="btn-delete post-ajax" data-url-ajax="{{ route('core::setting.system.data.delete.acl.draft') }}" 
                                            type="button">{{ trans('core::view.Delete') }} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh-btn"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-11">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group form-label-left row">
                                <label class="col-sm-3 control-label">{{ trans('core::view.Refresh version seeder menu, acl') }} </label>
                                <div class="col-md-9">
                                    <button class="btn-edit post-ajax" data-url-ajax="{{ route('core::setting.system.data.refresh.version.seeder') }}" 
                                        type="button">{{ trans('core::view.Refresh') }} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh-btn"></i></button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group form-label-left row">
                                @if (MView::isProcess())
                                <label class="col-sm-3 control-label">Delete process upload chấm công</label>
                                <div class="col-md-9">
                                    <button class="btn-delete post-ajax" data-url-ajax="{{ route('core::setting.system.data.delete.process.queue', ['type' => 'upload_time']) }}" 
                                            type="button">{{ trans('core::view.Delete') }} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh-btn"></i></button>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if (RunBgSlide::isProcess())
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group form-label-left row">
                        <label class="col-sm-2 control-label">Delete process resize image</label>
                        <div class="col-md-1">
                            <button class="btn-delete post-ajax" data-url-ajax="{{ route('core::setting.system.data.delete.process.queue', ['type' => 'slide']) }}" 
                                    type="button">Delete <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh-btn"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            @endif
<?php /*
            <!-- delete process timekeeping -->
            @if (TimekeepingSplit::isProcessSplit())
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group form-label-left row">
                        <label class="col-sm-2 control-label">Delete process timekeeping</label>
                        <div class="col-md-1">
                            <button class="btn-delete post-ajax" data-url-ajax="{{ route('core::setting.system.data.delete.timekeeping.process') }}" 
                                    type="button">Delete <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh-btn"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            <!-- // end delete process timekeeping-->
*/ ?>
            <?php
            $redmineApi = CoreConfigData::getRemineApi();
            ?>
            <form id="form-system-api_redmine" method="post" action="{{ route('core::setting.system.data.save') }}"
                  class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}
                <div class="row">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-label-left row">
                                    <label for="project.redmine_api_url" class="col-sm-3 control-label">{{ trans('core::view.Redmine url') }}</label>
                                    <div class="col-md-9">
                                        <input name="item[project.redmine_api_url]" class="form-control input-field" type="text" 
                                               id="project.redmine_api_url" value="{{ $redmineApi['url'] }}" />
                                        <p class="hint">{{ trans('core::view.Domain redmine') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-label-left row">
                                    <label for="project.redmine_api_key" class="col-sm-3 control-label">{{ trans('core::view.Redmine key') }}</label>
                                    <div class="col-md-9">
                                        <input name="item[project.redmine_api_key]" class="form-control input-field" type="text" 
                                               id="project.redmine_api_key" value="{{ $redmineApi['key'] }}" />
                                        <p class="hint">Redmine api access key</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-label-left row">
                                    <label for="project.redmine_api_project_url" class="col-sm-3 control-label">{{ trans('core::view.Redmine project url') }}</label>
                                    <div class="col-md-9">
                                        <input name="item[project.redmine_api_project_url]" class="form-control input-field" type="text" 
                                               id="project.redmine_api_project_url" value="{{ $redmineApi['project_url'] }}" />
                                        <p class="hint">{{ trans('core::view.Link to project on Redmine') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <button class="btn-add margin-bottom-5" type="submit">{{ trans('core::view.Save') }} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                        <button class="btn-add post-ajax" data-url-ajax="{{ route('core::setting.system.data.check.connect', ['api' => 'redmine']) }}" 
                                type="button" title="{{ trans('core::view.Save before check') }}" data-toggle="tooltip">{{ trans('core::view.Check') }} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh-btn"></i></button>
                    </div>
                </div>
            </form>

            <?php
            $gitlabApi = CoreConfigData::getGitlabApi();
            ?>
            <form id="form-system-api_git" method="post" action="{{ route('core::setting.system.data.save') }}"
                  class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}
                <div class="row">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-label-left row">
                                    <label for="project.gitlab_api_url" class="col-sm-3 control-label">{{ trans('core::view.Gitlab url') }}</label>
                                    <div class="col-md-9">
                                        <input name="item[project.gitlab_api_url]" class="form-control input-field" type="text" 
                                               id="project.gitlab_api_url" value="{{ $gitlabApi['url'] }}" />
                                        <p class="hint">{{ trans('core::view.use API Gitlab URL v4') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-label-left row">
                                    <label for="project.gitlab_api_token" class="col-sm-3 control-label">Gitlab token</label>
                                    <div class="col-md-9">
                                        <input name="item[project.gitlab_api_token]" class="form-control input-field" type="text" 
                                               id="project.gitlab_api_token" value="{{ $gitlabApi['token'] }}" />
                                        <p class="hint">Gitlab token access</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-label-left row">
                                    <label for="project.gitlab_api_project_url" class="col-sm-3 control-label">{{ trans('core::view.Gitlab Project Url') }}</label>
                                    <div class="col-md-9">
                                        <input name="item[project.gitlab_api_project_url]" class="form-control input-field" type="text" 
                                               id="project.gitlab_api_project_url" value="{{ $gitlabApi['project_url'] }}" />
                                        <p class="hint">{{ trans('core::view.Link to project on Gitlab') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <button class="btn-add margin-bottom-5" type="submit">{{ trans('core::view.Save') }} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                        <button class="btn-add post-ajax" data-url-ajax="{{ route('core::setting.system.data.check.connect', ['api' => 'gitlab']) }}" 
                                type="button" title="{{ trans('core::view.Save before check') }}" data-toggle="tooltip">{{ trans('core::view.Check') }}<i class="fa fa-spin fa-refresh hidden submit-ajax-refresh-btn"></i></button>
                    </div>
                </div>
            </form>

            <?php
                $token = CoreConfigData::getApiToken();
            ?>
            <form id="form-system-api_git" method="post" action="{{ route('core::setting.system.data.save') }}"
                  class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}
                <div class="row">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-label-left row">
                                    <label for="project.api_token" class="col-sm-3 control-label">Intranet API token</label>
                                    <div class="col-md-9">
                                        <input name="item[project.api_token]" class="form-control input-field" type="text"
                                        id="project.api_token" value=" {{ $token }}" />
                                        <p class="hint">{{ trans('core::view.Get Data API Token') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <button class="btn-add margin-bottom-5" type="submit">{{ trans('core::view.Save') }} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                        <button class="btn-add post-ajax" data-url-ajax="{{ route('core::setting.system.data.check.connect', ['api' => 'gitlab']) }}"
                                type="button" title="{{ trans('core::view.Save before check') }}" data-toggle="tooltip">{{ trans('core::view.Check') }} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh-btn"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div class="box box-info">
        <div class="box-body">
            <h2 class="box-body-title">Sonar</h2>
            <?php
            $sonarUrlApi = CoreConfigData::getValueDb('api.sonar.url');
            $sonarTokenApi = CoreConfigData::getValueDb('api.sonar.token');
            ?>
            <form id="form-system-api_git" method="post" action="{{ route('core::setting.system.data.save') }}"
                  class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}
                <div class="row">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-label-left row">
                                    <label for="api.sonar.url" class="col-sm-3 control-label">{{ trans('core::view.Sonar url') }}</label>
                                    <div class="col-md-9">
                                        <input name="item[api.sonar.url]" class="form-control input-field" type="text" 
                                               id="api.sonar.url" value="{{ $sonarUrlApi }}" />
                                        <p class="hint">http://sonar.rikkei.org</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-label-left row">
                                    <label for="api.sonar.token" class="col-sm-3 control-label">{{ trans('core::view.Sonar token') }}</label>
                                    <div class="col-md-9">
                                        <input name="item[api.sonar.token]" class="form-control input-field" type="text" 
                                               id="api.sonar.token" value="{{ $sonarTokenApi }}" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <button class="btn-add margin-bottom-5" type="submit">{{ trans('core::view.Save') }} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                        <button class="btn-add post-ajax" data-url-ajax="{{ route('core::setting.system.data.check.connect', ['api' => 'sonar']) }}" 
                                type="button" title="{{ trans('core::view.Save before check') }}" data-toggle="tooltip">{{ trans('core::view.Check') }} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh-btn"></i></button>
                    </div>
                </div>
            </form>
            <p>&nbsp;</p>
            <?php
            ?>
            <form id="form-system-api_git" method="post" action="{{ route('core::setting.system.data.save') }}"
                  class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}
                <div class="row">
                    <div class="col-md-11">
                        <div class="row">
                            <div class='col-md-12'>
                                <div class="col-md-4">
                                    <div class="form-group form-label-left row">
                                        <label for="api.jenkins.url" class="col-sm-3 control-label">{{ trans('core::view.Jenkins url') }}</label>
                                        <div class="col-md-9">
                                            <input name="item[api.jenkins.url]" class="form-control input-field" type="text" 
                                                   id="api.jenkins.url" value="{{ CoreConfigData::getValueDb('api.jenkins.url') }}" />
                                            <p class="hint">http://ci.rikkei.org</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group form-label-left row">
                                        <label for="api.jenkins.auth" class="col-sm-3 control-label">{{ trans('core::view.Jenkins auth') }}</label>
                                        <div class="col-md-9">
                                            <input name="item[api.jenkins.auth]" class="form-control input-field" type="text" 
                                                   id="api.jenkins.auth" value="{{ CoreConfigData::getValueDb('api.jenkins.auth') }}" />
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group form-label-left row">
                                        <label for="api.jenkins.token" class="col-sm-3 control-label">{{ trans('core::view.Jenkins token') }}</label>
                                        <div class="col-md-9">
                                            <input name="item[api.jenkins.token]" class="form-control input-field" type="text" 
                                                   id="api.jenkins.token" value="{{ CoreConfigData::getValueDb('api.jenkins.token') }}" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class='col-md-12'>
                                <div class="col-md-4">
                                    <div class="form-group form-label-left row">
                                        <label for="api.jenkins.crumb" class="col-sm-3 control-label">{{ trans('core::view.Jenkins Crumb') }}</label>
                                        <div class="col-md-9">
                                            <input name="item[api.jenkins.crumb]" class="form-control input-field" type="text" 
                                                   id="api.jenkins.crumb" value="{{ CoreConfigData::getValueDb('api.jenkins.crumb') }}" />
                                            <p class="hint">Jenkins-Crumb</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group form-label-left row">
                                        <label for="api.jenkins.crumb_val" class="col-sm-3 control-label">{{ trans('core::view.Jenkins Crumb Value') }}</label>
                                        <div class="col-md-9">
                                            <input name="item[api.jenkins.crumb_val]" class="form-control input-field" type="text" 
                                                   id="api.jenkins.crumb_val" value="{{ CoreConfigData::getValueDb('api.jenkins.crumb_val') }}" />
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group form-label-left row">
                                        <button class="btn-add post-ajax" data-url-ajax="{{ route('core::setting.system.data.check.connect', ['api' => 'jenkinscrumb']) }}" 
                                            type="button">{{ trans('core::view.Jenkins get crumb') }} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh-btn"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <button class="btn-add margin-bottom-5" type="submit">{{ trans('core::view.Save') }} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                        <button class="btn-add post-ajax" data-url-ajax="{{ route('core::setting.system.data.check.connect', ['api' => 'jenkins']) }}" 
                                type="button" title="{{ trans('core::view.Save before check') }}" data-toggle="tooltip">{{ trans('core::view.Check') }} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh-btn"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- @include('api::setting') --}}

    <div class="box box-info">
        <div class="box-body">
            <h2 class="box-body-title">IM Messenger</h2>
            <?php
            $sonarUrlApi = CoreConfigData::getValueDb('api.sonar.url');
            $sonarTokenApi = CoreConfigData::getValueDb('api.sonar.token');
            ?>
            <form id="form-system-im-api" method="post" action="{{ route('core::setting.system.data.save') }}"
                  class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}
                <div class="row">
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-label-left row">
                                    <label class="col-sm-3 control-label">{{ trans('core::view.IM Admin ID') }}</label>
                                    <div class="col-md-9">
                                        <input name="item[im.admin.id]" class="form-control input-field" type="text" 
                                               value="{{ CoreConfigData::getValueDb('im.admin.id') }}" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-label-left row">
                                    <label class="col-sm-3 control-label">{{ trans('core::view.IM Admin Token') }}</label>
                                    <div class="col-md-9">
                                        <input name="item[im.admin.token]" class="form-control input-field" type="text" 
                                            value="{{ CoreConfigData::getValueDb('im.admin.token') }}" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <button class="btn-add margin-bottom-5" type="submit">{{ trans('core::view.Save') }} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endif