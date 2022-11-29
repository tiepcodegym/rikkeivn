<?php

use Rikkei\Core\View\View;
?>

<!-- word experience -->
<div class="modal fade employee-college-modal employee-skill-modal" 
    id="employee-work_experience-form" role="dialog" data-id="1"
    data-group="work_experiences">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ URL::route('core::upload.skill') }}" method="post" 
                    enctype="multipart/form-data" class="skill-modal-form" id="form-employee-work_experience">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ trans('team::view.Infomation of company') }}</h4>
                </div>
                <div class="modal-body">
                    <div class="form-horizontal form-label-left">
                        <input type="hidden" name="id" value="" class="college-id input-skill-modal not-auto" 
                            data-tbl="work_experience" data-col="id" />
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="work_experience-company">{{ trans('team::view.Company') }}</label>
                            <div class="input-box col-md-9">
                                <input type="text" class="form-control work_experience-company input-skill-modal" placeholder="{{ trans('team::view.Company') }}" 
                                    value="" name="name" id="work_experience-company"
                                    data-tbl="work_experience" data-col="company" data-autocomplete="true" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="work_experience-image">{{ trans('team::view.Image') }}</label>
                            <div class="input-box col-md-9 input-box-img-preview">
                                <div class="image-preview">
                                    <img src="{{ URL::asset('common/images/noimage.png') }}"
                                         id="work_experience-image-preview" class="img-responsive work_experience-image-preview skill-modal-image-preview" 
                                         data-tbl="work_experience" data-col="image_preview"/>
                                </div>
                                <div class="img-input">
                                    <input type="file" class="form-control work_experience-image skill-modal-image input-skill-modal" value="" 
                                        name="image" id="work_experience-image" 
                                        data-tbl="work_experience" data-col="image" />
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="work_experience-position">{{ trans('team::view.Position') }}</label>
                            <div class="input-box col-md-9">
                                <input type="text" class="form-control work_experience-position input-skill-modal not-auto" placeholder="{{ trans('team::view.Position') }}" 
                                    value="" name="position" id="work_experience-position" 
                                    data-tbl="work_experience" data-col="position" />
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="work_experience-start">{{ trans('team::view.Start at') }}</label>
                            <div class="input-box col-md-9">
                                <input type="text" id="work_experience-start" 
                                    class="form-control date-picker work_experience-start input-skill-modal not-auto" placeholder="yyyy-mm-dd" 
                                    value="" name="start_at" id="work_experience-start"
                                    data-tbl="work_experience" data-col="start_at" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="work_experience-end not-auto">{{ trans('team::view.End at') }}</label>
                            <div class="input-box col-md-9">
                                <input type="text" id="work_experience-end" 
                                    class="form-control date-picker work_experience-end input-skill-modal not-auto" placeholder="yyyy-mm-dd" 
                                    value="" name="end_at" id="work_experience-end"
                                    data-tbl="work_experience" data-col="end_at" />
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
<!-- end word experience -->
