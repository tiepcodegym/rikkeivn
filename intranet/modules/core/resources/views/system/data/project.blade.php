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
                            <label for="project.required_rikkei" class="col-md-2 control-label">{{trans('core::view.Project requirements')}}</label>
                            <div class="col-md-9">
                                <textarea name="item[project.required_rikkei]" class="form-control input-field" type="text"
                                          id="project.required_rikkei" rows="5">{{ CoreConfigData::getValueDb('project.required_rikkei') }}</textarea>
                                <p class="hint">{{trans('core::view.Project requirements of Rikkei')}}</p>
                            </div>
                            <div class="col-md-1">
                                <button class="btn-add" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <form id="form-system-general" method="post" action="{{ route('core::setting.system.data.save') }}"
                  class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group form-label-left row">
                            <label for="project.account_coo" class="col-md-2 control-label">{{trans('core::view.Email approver of workorder')}}</label>
                            <div class="col-md-9">
                                <input name="item[project.account_coo]" class="form-control input-field" type="text"
                                       id="project.account_coo" value="{{ CoreConfigData::getCOOAccount(2) }}" />
                                <p class="hint">{{trans('core::view.Email to approve workorder')}}</p>
                            </div>
                            <div class="col-md-1">
                                <button class="btn-add" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <form id="form-system-general" method="post" action="{{ route('core::setting.system.data.save') }}"
                  class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group form-label-left row">
                            <label for="project.account_approver_reward" class="col-md-2 control-label">{{trans('core::view.Email approver of workorder')}}</label>
                            <div class="col-md-9">
                                <input name="item[project.account_approver_reward]" class="form-control input-field" type="text"
                                       id="project.account_approver_reward" value="{{ CoreConfigData::getValueDB('project.account_approver_reward') }}" />
                                <p class="hint">{{trans('core::view.Email to approve reward')}}</p>
                            </div>
                            <div class="col-md-1">
                                <button class="btn-add" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <form id="form-system-general" method="post" action="{{ route('core::setting.system.data.save') }}"
                  class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group form-label-left row">
                            <label for="bod_email" class="col-md-2 control-label">{{trans('core::view.BOD Email Address')}}</label>
                            <div class="col-md-9">
                                <input name="item[bod_email]" class="form-control input-field" type="text"
                                       id="bod_email" value="{{ CoreConfigData::getValueDB('bod_email') }}" />
                                <p class="hint">{{trans('core::view.BOD Email Address')}}</p>
                            </div>
                            <div class="col-md-1">
                                <button class="btn-add" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <form id="form-system-general" method="post" action="{{ route('core::setting.system.data.save') }}"
                  class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group form-label-left row">
                            <label for="project.account_qa" class="col-md-2 control-label">{{trans('core::view.QA Lead Email Address')}}</label>
                            <div class="col-md-9">
                                <input name="item[project.account_qa]" class="form-control input-field" type="text"
                                       id="project.account_qa" value="{{ CoreConfigData::getQAAccount(2) }}" />
                                <p class="hint">{{trans('core::view.Email of PQA leader, assign default to review workorder')}}</p>
                            </div>
                            <div class="col-md-1">
                                <button class="btn-add" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <form id="form-system-general" method="post" action="{{ route('core::setting.system.data.save') }}"
                  class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group form-label-left row">
                            <label for="project.account_sqa" class="col-md-2 control-label">{{trans('core::view.SQA Email Address')}}</label>
                            <div class="col-md-9">
                                <input name="item[project.account_sqa]" class="form-control input-field" type="text"
                                       id="project.account_sqa" value="{{ CoreConfigData::getSQA(2) }}" />
                                <p class="hint">{{trans('core::view.Email of SQA leader, assign default to review workorder')}}</p>
                            </div>
                            <div class="col-md-1">
                                <button class="btn-add" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <form id="form-system-general" method="post" action="{{ route('core::setting.system.data.save') }}"
                  class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group form-label-left row">
                            <label for="project.email_lead_watcher" class="col-md-2 control-label">{{trans('core::view.Project Watcher Email Address')}}</label>
                            <div class="col-md-9">
                                <input name="item[project.email_lead_watcher]" class="form-control input-field" type="text"
                                       id="project.email_lead_watcher" value="{{ CoreConfigData::getValueDb('project.email_lead_watcher') }}" />
                                <p class="hint">{{trans('core::view.Email watcher: budget')}}</p>
                            </div>
                            <div class="col-md-1">
                                <button class="btn-add" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <form id="form-system-general" method="post" action="{{ route('core::setting.system.data.save') }}"
                  class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}
                <?php
                $keyMeActivity = Rikkei\Project\View\MeView::KEY_MAIL_ACTIVITY;
                ?>
                <div class="form-group form-label-left row">
                    <label class="col-md-2 control-label">{{trans('core::view.ME Reminder Sender')}}</label>
                    <div class="col-md-9">
                        <textarea name="item[{{ $keyMeActivity }}]" class="form-control input-field text-resize-y" type="text"
                                  rows="5">{{ CoreConfigData::getValueDb($keyMeActivity) }}</textarea>
                        <p class="hint">{{trans('core::view.ME Reminder Sender Email Address')}}</p>
                    </div>
                    <div class="col-md-1">
                        <button class="btn-add" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                    </div>
                </div>
            </form>

            <form id="form-system-general" method="post" action="{{ route('core::setting.system.data.save') }}"
                  class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}
                <?php $itemKey = 'project.me.baseline_date'; ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group form-label-left row">
                            <label for="project.baseline_date" class="col-md-2 control-label">{{trans('core::view.Project baseline date')}}</label>
                            <div class="col-md-9">
                                <input name="item[{{ $itemKey }}]" class="form-control input-field" type="number" min="1" max="31"
                                       id="project.baseline_date" value="{{ CoreConfigData::getValueDb($itemKey) }}" />
                            </div>
                            <div class="col-md-1">
                                <button class="btn-add" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <form id="form-system-general" method="post" action="{{ route('core::setting.system.data.save') }}"
                  class="form-horizontal form-submit-ajax no-validate project-ot" autocomplete="off">
                {!! csrf_field() !!}
                <?php $itemKey = 'project.ot.18h'; ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group form-label-left row">
                            <label for="project-ot-18h" class="col-md-2 control-label">{{trans('core::view.Projects of which OT is allowed from 18:00')}} </label>
                            <div class="col-md-9">
                                <select name="item[project.ot.18h][]" id="project-ot-18h" style="width: 100%; height: 34px;" data-remote-url="{{ URL::route('project::list.search.ajax') }}" multiple="multiple">
                                @if ($projectOT)
                                    @foreach ($projectOT as $proj)
                                    <option value="{{ $proj->id }}" selected>{{ $proj->name }}</option>
                                    @endforeach
                                @endif
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button class="btn-add" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <form id="form-system-general" method="post" action="{{ route('core::setting.system.data.save') }}"
                  class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}
                <?php $itemKey = 'project.production.slide.pass'; ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group form-label-left row">
                            <label for="project-slide-pass" class="col-md-4 control-label">{{trans('core::view.Password for Production Dashboard (Slides)')}} </label>
                            <div class="col-md-7">
                                <input name="item[{{ $itemKey }}]" class="form-control input-field"
                                       id="project-slide-pass" value="{{ CoreConfigData::getValueDb($itemKey) }}" />
                            </div>
                            <div class="col-md-1">
                                <button class="btn-add" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <?php /*<form id="form-system-general" method="post" action="{{ route('core::setting.system.data.save') }}"
                  class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}
                <?php
                $itemKey = 'project.me.baseline_day';
                $daysInWeek = CoreView::daysInWeek();
                ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group form-label-left row">
                            <label for="project.baseline_day" class="col-sm-2 control-label">Project baseline day</label>
                            <div class="col-md-9">
                                <select class="form-control select-search" name="item[{{ $itemKey }}]">
                                    @foreach($daysInWeek as $day => $label)
                                    <option value="{{ $day }}" {{ CoreConfigData::getValueDb($itemKey) == $day ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button class="btn-add" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>*/ ?>
        </div>
    </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.3/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>