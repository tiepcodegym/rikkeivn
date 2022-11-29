<?php

use Rikkei\Team\Model\WorkExperience;
?>
<div class="box box-primary" id="box_add" hidden="" style="display: none;">
    <div class="box-header with-border">
        <h3 class="box-title">{{ trans('team::view.Infomation of company') }}</h3>
    </div>
    <!-- /.box-header -->
    <!-- form start -->
    <form  enctype="multipart/form-data" method="post" class="skill-modal-form" 
                   id="form-employee-work_experience" 
                   onsubmit="return false;">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
                <input type="hidden" name="index" value="" id="work_experience-index"/>
                <div class="box-body">
                    <div class="form-horizontal form-label-left">
                        <input type="hidden" name="id" value="" class="college-id input-skill-modal not-auto" 
                               data-tbl="work_experience" data-col="id" />
                        <input type="hidden" name="type" 
                               value="{{ WorkExperience::TYPE_JAPAN }}" 
                               id="work_experience-type" 
                               data-tbl="work_experience" 
                               data-col="type" />
                        <div class="form-group">
                            <label class="col-md-2 control-label required" for="work_experience-company">{{ trans('team::view.Company') }}<em>*</em></label>
                            <div class="input-box col-md-9">
                                <input type="text" class="form-control work_experience-company input-skill-modal" placeholder="{{ trans('team::view.Company') }}" 
                                       value="" name="name" id="work_experience-company"
                                       <?php if (!$employeePermission): ?> disabled<?php endif;?>
                                       data-tbl="work_experience" data-col="company" data-autocomplete="true" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label required" for="work_experience-position">{{ trans('team::view.Position') }}<em>*</em></label>
                            <div class="input-box col-md-9">
                                <input type="text" class="form-control work_experience-position input-skill-modal not-auto" placeholder="{{ trans('team::view.Position') }}" 
                                       value="" name="position" id="work_experience-position" 
                                       <?php if (!$employeePermission): ?> disabled<?php endif;?>
                                       data-tbl="work_experience" data-col="position" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-2 control-label" for="work_experience-position">{{ trans('team::view.Address') }}</label>
                            <div class="input-box col-md-9">
                                <input type="text" class="form-control work_experience-address input-skill-modal not-auto" placeholder="{{ trans('team::view.Address') }}" 
                                       value="" name="address" id="work_experience-address" 
                                       <?php if (!$employeePermission): ?> disabled<?php endif;?>
                                       data-tbl="work_experience" data-col="address" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-2 control-label required" for="work_experience-start">{{ trans('team::view.Start at') }}<em>*</em></label>
                            <div class="col-md-4">
                                <div class="input-group-table">
                                    <input type="text" id="work_experience-start" 
                                           class="form-control date-picker work_experience-start input-skill-modal not-auto" placeholder="yyyy-mm-dd" 
                                           value="" name="start_at" id="work_experience-start"
                                           <?php if (!$employeePermission): ?> disabled<?php endif;?>
                                           data-tbl="work_experience" data-col="start_at" />
                                    <div class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </div> 
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label required" for="work_experience-end not-auto">{{ trans('team::view.End at') }}<em>*</em></label>
                            <div class="col-md-4">
                                <div class="input-group-table">
                                    <input type="text" id="work_experience-end" 
                                           class="form-control date-picker work_experience-end input-skill-modal not-auto" placeholder="yyyy-mm-dd" 
                                           value="" name="end_at" id="work_experience-end"
                                           <?php if (!$employeePermission): ?> disabled<?php endif;?>
                                           data-tbl="work_experience" data-col="end_at" />
                                    <div class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </div> 
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.box-body -->
        </div>
    </div>
    <div class="box project-experience-box" style="display: none" id="project-experience-box">
        <div class="box-header with-border">
            <h2 class="box-title">{{ trans('team::view.Project experience') }}</h2>
        </div>
        <div class="box-body">
            @include('team::member.experiences.jp_project_experience')
        </div>
    </div> <!-- end box work experience -->
    <div class="box-footer">
        @if($employeePermission)
        <button type="submit" class="btn btn-primary" id="experience-submit"><i class="fa fa-paper-plane-o"></i>{{ trans('core::view.Save') }}</button>
        <button type="button" class="btn btn-danger hidden" id="remove"><i class="fa fa-trash"></i>{{ trans('core::view.Remove') }}</button>
        @endif
        <button type="button" class="btn btn-default" id="close"><i class="fa fa-ban"></i> {{ trans('core::view.Cancel') }}</button>
    </div>
    </form>
</div>