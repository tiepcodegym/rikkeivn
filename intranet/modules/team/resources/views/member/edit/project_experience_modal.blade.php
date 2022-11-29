<?php

use Rikkei\Core\View\View;
?>

<!-- project experience -->
<div class="modal fade employee-college-modal employee-skill-modal" 
    id="employee-project_experience-form" role="dialog" data-id="1"
    data-group="project_experiences">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ URL::route('core::upload.skill') }}" method="post" 
                    enctype="multipart/form-data" class="skill-modal-form" id="form-employee-project_experience" data-work_experience_ids="">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ trans('team::view.Infomation of project') }}</h4>
                </div>
                <div class="modal-body">
                    <div class="form-horizontal form-label-left">
                        <input type="hidden" name="id" value="" class="project_experience-id input-skill-modal not-auto" 
                            data-tbl="project_experience" data-col="id" />
                        <div class="form-group">
                            <label class="col-md-3 control-label required" for="project_experience-name">{{ trans('team::view.Name') }}<em>*</em></label>
                            <div class="input-box col-md-9">
                                <input type="text" class="form-control project_experience-name input-skill-modal not-auto" placeholder="{{ trans('team::view.Name') }}" 
                                    value="" name="name" id="project_experience-name"
                                    data-tbl="project_experience" data-col="name" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="project_experience-image">{{ trans('team::view.Image') }}</label>
                            <div class="input-box col-md-9 input-box-img-preview">
                                <div class="image-preview">
                                    <img src="{{ URL::asset('common/images/noimage.png') }}"
                                         id="project_experience-image-preview" class="img-responsive project_experience-image-preview skill-modal-image-preview" 
                                         data-tbl="project_experience" data-col="image_preview"/>
                                </div>
                                <div class="img-input">
                                    <input type="file" class="form-control project_experience-image skill-modal-image input-skill-modal not-auto" value="" 
                                        name="image" id="project_experience-image" 
                                        data-tbl="project_experience" data-col="image" />
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label required" for="project_experience-customer_name">{{ trans('team::view.Cutomer Name') }}<em>*</em></label>
                            <div class="input-box col-md-9">
                                <input type="text" class="form-control project_experience-customer_name input-skill-modal not-auto" placeholder="{{ trans('team::view.Cutomer Name') }}" 
                                    value="" name="customer_name" id="project_experience-customer_name" 
                                    data-tbl="project_experience" data-col="customer_name" />
                                <input type="hidden" class="form-control project_experience-work_experience_id input-skill-modal not-auto" 
                                    value="" name="work_experience_id" id="project_experience-work_experience_id" 
                                    data-tbl="project_experience" data-col="work_experience_id" />
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="col-md-3 control-label required" for="project_experience-description">{{ trans('team::view.Description') }}<em>*</em></label>
                            <div class="input-box col-md-9">
                                <textarea type="text" class="form-control project_experience-description input-skill-modal not-auto" placeholder="{{ trans('team::view.Description') }}" 
                                    name="description" id="project_experience-description" 
                                    data-tbl="project_experience" data-col="description"></textarea>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="col-md-3 control-label required" for="project_experience-poisition">{{ trans('team::view.Position') }}<em>*</em></label>
                            <div class="input-box col-md-9">
                                <input type="text" class="form-control project_experience-poisition input-skill-modal not-auto" placeholder="{{ trans('team::view.Position') }}" 
                                    value="" name="poisition" id="project_experience-poisition" 
                                    data-tbl="project_experience" data-col="poisition" />
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="col-md-3 control-label required" for="project_experience-no_member">{{ trans('team::view.No member') }}<em>*</em></label>
                            <div class="input-box col-md-9">
                                <input type="text" class="form-control project_experience-no_member num input-skill-modal not-auto" placeholder="{{ trans('team::view.No member') }}"
                                    value="" name="no_member" id="project_experience-no_member" 
                                    data-tbl="project_experience" data-col="no_member" />
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="project_experience-enviroment_language">{{ trans('team::view.Language') }}</label>
                            <div class="input-box col-md-9">
                                <input type="text" class="form-control project_experience-enviroment_language input-skill-modal not-auto" placeholder="{{ trans('team::view.Language') }}" 
                                    value="" name="enviroment_language" id="project_experience-enviroment_language" 
                                    data-tbl="project_experience" data-col="enviroment_language" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="project_experience-enviroment_enviroment">{{ trans('team::view.Environment') }}</label>
                            <div class="input-box col-md-9">
                                <input type="text" class="form-control project_experience-enviroment_enviroment input-skill-modal not-auto" placeholder="{{ trans('team::view.Environment') }}" 
                                    value="" name="enviroment_enviroment" id="project_experience-enviroment_enviroment" 
                                    data-tbl="project_experience" data-col="enviroment_enviroment" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="project_experience-enviroment_os">{{ trans('team::view.OS') }}</label>
                            <div class="input-box col-md-9">
                                <input type="text" class="form-control project_experience-enviroment_language input-skill-modal not-auto" placeholder="{{ trans('team::view.OS') }}" 
                                    value="" name="enviroment_os" id="project_experience-enviroment_os" 
                                    data-tbl="project_experience" data-col="enviroment_os" />
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="project_experience-other_tech">{{ trans('team::view.Other Tech') }}</label>
                            <div class="input-box col-md-9">
                                <textarea type="text" class="form-control project_experience-other_tech input-skill-modal not-auto" placeholder="{{ trans('team::view.Other Tech') }}" 
                                    name="other_tech" id="project_experience-other_tech" 
                                    data-tbl="project_experience" data-col="other_tech"></textarea>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="col-md-3 control-label required" for="project_experience-responsible">{{ trans('team::view.Responsible') }}<em>*</em></label>
                            <div class="input-box col-md-9">
                                <textarea type="text" class="form-control project_experience-responsible input-skill-modal not-auto" placeholder="{{ trans('team::view.Responsible') }}" 
                                    name="responsible" id="project_experience-responsible" 
                                    data-tbl="project_experience" data-col="responsible"></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label required" for="project_experience-start">{{ trans('team::view.Start at') }}<em>*</em></label>
                            <div class="input-box col-md-9">
                                <input type="text" id="project_experience-start" 
                                    class="form-control date-picker project_experience-start input-skill-modal not-auto" placeholder="yyyy-mm-dd" 
                                    value="" name="start_at" id="project_experience-start"
                                    data-tbl="project_experience" data-col="start_at" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label required" for="project_experience-end">{{ trans('team::view.End at') }}<em>*</em></label>
                            <div class="input-box col-md-9">
                                <input type="text" id="project_experience-end" 
                                    class="form-control date-picker project_experience-end input-skill-modal not-auto" placeholder="yyyy-mm-dd" 
                                    value="" name="end_at" id="project_experience-end"
                                    data-tbl="project_experience" data-col="end_at" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-delete btn-action">
                        <span>{{ trans('team::view.Remove') }}</span>
                    </button>
                    <button type="submit" class="btn-add btn-action">
                        <span>{{ trans('team::view.Save') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- end project experience -->
