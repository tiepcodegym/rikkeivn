<?php
use Rikkei\Core\View\View as CoreView;
use Rikkei\Core\Model\CoreConfigData;
?>

<div class="col-md-12">
    <div class="box box-info">
        <div class="box-body">
            <div class="box-body-header">
                <h2 class="box-body-title">{{ trans('core::view.Holidays') }}</h2>
            </div>
            <form id="form-system-special_holidays" method="post" action="{{ route('core::setting.system.data.save', ['holidays' => 1, 'region' => 'hn']) }}"
                  class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group form-label-left">
                            <label for="project.special_holidays" class="col-md-2 control-label">{{trans('core::view.Special Holidays Ha Noi')}}</label>
                            <div class="col-md-7">
                                <textarea name="item[project.special_holidays_hn]" class="form-control input-field project-sh-textarea" type="text"
                                          rows="5" id="project.special_holidays_hn">{{ CoreConfigData::getSpecHolidaysByRegion('hn') }}</textarea>
                                <p class="hint">{{ trans('core::view.Format: YYYY-MM-DD') . ', ' . trans('core::view.split by semi-colon or break line'). ', ' . trans('core::view.holidays of Vietnam calendar, compensation, company') }}</p>
                            </div>
                            <div class="col-md-2" >
                                <div id="project_sh-datetime_hn" class="project_sh-datetime">
                                    <button type="button" class="btn project_sh-datetime-btn">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <button class="btn-add" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <form id="form-system-special_holidays" method="post" action="{{ route('core::setting.system.data.save', ['holidays' => 1, 'region' => 'dn']) }}"
                  class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group form-label-left">
                            <label for="project.special_holidays" class="col-md-2 control-label">{{trans('core::view.Special Holidays Da Nang')}}</label>
                            <div class="col-md-7">
                                <textarea name="item[project.special_holidays_dn]" class="form-control input-field project-sh-textarea" type="text"
                                          rows="5" id="project.special_holidays_dn">{{ CoreConfigData::getSpecHolidaysByRegion('dn') }}</textarea>
                                <p class="hint">{{ trans('core::view.Format: YYYY-MM-DD') . ', ' . trans('core::view.split by semi-colon or break line'). ', ' . trans('core::view.holidays of Vietnam calendar, compensation, company') }}</p>
                            </div>
                            <div class="col-md-2" >
                                <div id="project_sh-datetime_dn" class="project_sh-datetime">
                                    <button type="button" class="btn project_sh-datetime-btn">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <button class="btn-add" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <form id="form-system-special_holidays" method="post" action="{{ route('core::setting.system.data.save', ['holidays' => 1, 'region' => 'hcm']) }}"
                  class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group form-label-left">
                            <label for="project.special_holidays_hcm" class="col-md-2 control-label">{{trans('core::view.Special Holidays Ho Chi Minh')}}</label>
                            <div class="col-md-7">
                                <textarea name="item[project.special_holidays_hcm]" class="form-control input-field project-sh-textarea" type="text"
                                          rows="5" id="project.special_holidays_hcm">{{ CoreConfigData::getSpecHolidaysByRegion('hcm') }}</textarea>
                                <p class="hint">{{ trans('core::view.Format: YYYY-MM-DD') . ', ' . trans('core::view.split by semi-colon or break line'). ', ' . trans('core::view.holidays of Vietnam calendar, compensation, company') }}</p>
                            </div>
                            <div class="col-md-2" >
                                <div id="project_sh-datetime_hcm" class="project_sh-datetime">
                                    <button type="button" class="btn project_sh-datetime-btn">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <button class="btn-add" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <form id="form-system-special_holidays" method="post" action="{{ route('core::setting.system.data.save', ['holidays' => 1, 'region' => 'jp']) }}"
                  class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group form-label-left">
                            <label for="project.special_holidays" class="col-md-2 control-label">{{trans('core::view.Special Holidays Japan')}}</label>
                            <div class="col-md-7">
                                <textarea name="item[project.special_holidays_jp]" class="form-control input-field project-sh-textarea" type="text"
                                          rows="5" id="project.special_holidays_jp">{{ CoreConfigData::getSpecHolidaysByRegion('jp') }}</textarea>
                                <p class="hint">{{ trans('core::view.Format: YYYY-MM-DD') . ', ' . trans('core::view.split by semi-colon or break line'). ', ' . trans('core::view.holidays of Vietnam calendar, compensation, company') }}</p>
                            </div>
                            <div class="col-md-2" >
                                <div id="project_sh-datetime_nb" class="project_sh-datetime">
                                    <button type="button" class="btn project_sh-datetime-btn">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <button class="btn-add" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <form id="form-system-annual_holidays" method="post" action="{{ route('core::setting.system.data.save', ['holidays' => 1]) }}"
                  class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group form-label-left">
                            <label for="project.annual_holidays" class="col-md-2 control-label">{{trans('core::view.Annual Holidays')}}</label>
                            <div class="col-md-9">
                                <textarea name="item[project.annual_holidays]" class="form-control input-field project-sh-textarea" type="text"
                                          rows="5" id="project.annual_holidays">{{ CoreConfigData::getAnnualHolidays() }}</textarea>
                                <p class="hint">{{ trans('core::view.Format: MM-DD') . ', ' . trans('core::view.split by semi-colon or break line') }}</p>
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

<div class="col-md-12">
    <div class="box box-info">
        <div class="box-body">
            <div class="box-body-header">
                <h2 class="box-body-title">{{ trans('core::view.Compensatory work date') }}</h2>
            </div>
            <form id="form-system-special_holidays" method="post" action="{{ route('core::setting.system.data.save', ['compensatory' => 1, 'region' => 'hn']) }}"
                  class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group form-label-left">
                            <label for="project.compensatory_work_hn" class="col-md-2 control-label">{{trans('core::view.Compensatory work date Hanoi')}}</label>
                            <div class="col-md-7">
                                <textarea name="item[project.compensatory.work.hn]" class="form-control input-field" type="text"
                                          rows="5" id="project.compensatory_work_hn">{{ CoreConfigData::getCompensatoryDates('hn') }}</textarea>
                                <p class="hint">{{ trans('core::view.compensatory config hint') }}</p>
                            </div>
                            <div class="col-md-2" >
                                <div id="project_sh-datetime_hn" class="project_sh-datetime">
                                    <button type="button" class="btn project_sh-datetime-btn">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <button class="btn-add" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <form id="form-system-special_holidays" method="post" action="{{ route('core::setting.system.data.save', ['compensatory' => 1, 'region' => 'dn']) }}"
                  class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group form-label-left">
                            <label for="project.compensatory_work_dn" class="col-md-2 control-label">{{trans('core::view.Compensatory work date Danang')}}</label>
                            <div class="col-md-7">
                                <textarea name="item[project.compensatory.work.dn]" class="form-control input-field" type="text"
                                          rows="5" id="project.compensatory_work_dn">{{ CoreConfigData::getCompensatoryDates('dn') }}</textarea>
                                <p class="hint">{{ trans('core::view.compensatory config hint') }}</p>
                            </div>
                            <div class="col-md-2" >
                                <div id="project_sh-datetime_dn" class="project_sh-datetime">
                                    <button type="button" class="btn project_sh-datetime-btn">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <button class="btn-add" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <form id="form-system-special_holidays" method="post" action="{{ route('core::setting.system.data.save', ['compensatory' => 1, 'region' => 'hcm']) }}"
                  class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group form-label-left">
                            <label for="project.compensatory_work_hcm" class="col-md-2 control-label">{{trans('core::view.Compensatory work date HCM')}}</label>
                            <div class="col-md-7">
                                <textarea name="item[project.compensatory.work.hcm]" class="form-control input-field" type="text"
                                          rows="5" id="project.compensatory_work_hcm">{{ CoreConfigData::getCompensatoryDates('hcm') }}</textarea>
                                <p class="hint">{{ trans('core::view.compensatory config hint') }}</p>
                            </div>
                            <div class="col-md-2" >
                                <div id="project_sh-datetime_hcm" class="project_sh-datetime">
                                    <button type="button" class="btn project_sh-datetime-btn">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <button class="btn-add" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <form id="form-system-special_holidays" method="post" action="{{ route('core::setting.system.data.save', ['compensatory' => 1, 'region' => 'jp']) }}"
                  class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group form-label-left">
                            <label for="project.compensatory_work_jp" class="col-md-2 control-label">{{trans('core::view.Compensatory work date Japan')}}</label>
                            <div class="col-md-7">
                                <textarea name="item[project.compensatory.work.jp]" class="form-control input-field" type="text"
                                          rows="5" id="project.compensatory_work_jp">{{ CoreConfigData::getCompensatoryDates('jp') }}</textarea>
                                <p class="hint">{{ trans('core::view.compensatory config hint') }}</p>
                            </div>
                            <div class="col-md-2" >
                                <div id="project_sh-datetime_nb" class="project_sh-datetime">
                                    <button type="button" class="btn project_sh-datetime-btn">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </button>
                                </div>
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
