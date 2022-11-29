<?php

use Rikkei\Core\View\Form;
use Rikkei\Core\View\View as ViewHelper;
use Rikkei\Team\View\Permission;
use Rikkei\Core\Model\CoreModel;

$experiencePermission = Permission::getInstance()->isAllow('team::team.member.edit.exerience');
?>
<?php

if (!function_exists('getHtmlEmployeeProjectExperience')) {
/**
* function html render employee project
*/
function getHtmlEmployeeProjectExperience($employeeExperience = null, $i = 0) 
{ 
    if (! $employeeExperience) {
        $employeeExperience = new CoreModel();
    }
?>
    <div class="col-md-12 project-exerience-wrapper employee-experience-item esbw-item<?php if(! $i): ?> hidden<?php endif; ?>" data-id="{{ $i }}"
         data-work_experience_id = "{{ $employeeExperience->work_experience_id }}">
        <div class="esi-content">
            <div class="col-xs-3">
                <p>
                    <a href="javascript:void(0);" class="esi-title" data-tbl="project_experience" data-col="name"
                       data-modal="true">{{ $employeeExperience->name }} 
                    </a>
                    <p style="margin-top: 5px;">
                        ( <span data-tbl="project_experience" data-col="start_at" data-date-format="M/Y">
                            {{ ViewHelper::formatDateTime('Y-m-d', 'm/Y', $employeeExperience->start_at) }}
                        </span>
                        ~
                        <span data-tbl="project_experience" data-col="end_at" data-date-format="M/Y">
                            {{ ViewHelper::formatDateTime('Y-m-d', 'm/Y', $employeeExperience->end_at) }}
                        </span>
                        )
                    </p>
                </p>
            </div>
            <div class="col-xs-9">
                <div class="row">
                    <div class="col-md-6 border-left-5">
                        <p class="jp-experience-callout">
                            <b>{{ trans('team::view.Language') }}:</b>
                            <span data-tbl="project_experience" data-col="enviroment_language"><?php 
                                    if ($enviromentItem = $employeeExperience->getEnvironment('language')) {
                                        echo e($enviromentItem);
                                    } else {
                                        echo e($employeeExperience->enviroment_language);
                                    }
                                ?></span>
                        </p>
                        <p class="jp-experience-callout">
                            <b>{{ trans('team::view.Environment') }}:</b>
                            <span data-tbl="project_experience" data-col="enviroment_enviroment"><?php 
                                    if ($enviromentItem = $employeeExperience->getEnvironment('enviroment')) {
                                        echo e($enviromentItem);
                                    } else {
                                        echo e($employeeExperience->enviroment_enviroment);
                                    }
                                ?></span>
                        </p>
                        <p class="jp-experience-callout">
                                <b>{{ trans('team::view.OS') }}:</b>
                                <span data-tbl="project_experience" data-col="enviroment_os"><?php 
                                    if ($enviromentItem = $employeeExperience->getEnvironment('os')) {
                                        echo e($enviromentItem);
                                    } else {
                                        echo e($employeeExperience->enviroment_os);
                                    }
                                ?></span>
                        </p>
                        <p class="jp-experience-callout">
                            <b>{{ trans('team::view.Responsible') }}:</b>
                            <span data-tbl="project_experience" data-col="responsible">{{ $employeeExperience->responsible }}</span>
                        </p>  
                    </div>
                    
                    <div class="col-md-6 border-left-5">
                        <p class="jp-experience-callout">
                            <b>{{ trans('team::view.Cutomer Name') }}:</b>
                            <span data-tbl="project_experience" data-col="customer_name">{{ $employeeExperience->customer_name }}</span>
                        </p>

                        <p class="jp-experience-callout">
                            <b>{{ trans('team::view.Description') }}:</b>
                            <span data-tbl="project_experience" data-col="description">{{ $employeeExperience->description }}</span>
                        </p>

                        <p class="jp-experience-callout">
                            <b>{{ trans('team::view.Poisition') }}:</b>
                            <span data-tbl="project_experience" data-col="poisition">{{ $employeeExperience->poisition }}</span>
                        </p>

                        <p class="jp-experience-callout">
                            <b>{{ trans('team::view.Other Tech') }}:</b>
                            <span data-tbl="project_experience" data-col="other_tech">{{ $employeeExperience->other_tech }}</span>
                        </p>

                        <p class="jp-experience-callout">
                            <b>{{ trans('team::view.No member') }}:</b>
                            <span data-tbl="project_experience" data-col="no_member">{{ $employeeExperience->no_member }}</span>
                        </p>  
                    </div>
                </div>
            </div>
        <script>
            employeeSkill.project_experiences[{{ $i }}] = {
                project_experience: {
                    id: '{{ $employeeExperience->id }}',
                    name: '{{ $employeeExperience->name }}',
                    work_experience_id : '{{ $employeeExperience->work_experience_id }}',
                    enviroment_language: '<?php 
                        if ($enviromentItem = $employeeExperience->getEnvironment('language')) {
                            echo e($enviromentItem);
                        } else {
                            echo e($employeeExperience->enviroment_language);
                        }
                    ?>',
                    enviroment_enviroment: '<?php 
                        if ($enviromentItem = $employeeExperience->getEnvironment('enviroment')) {
                            echo e($enviromentItem);
                        } else {
                            echo e($employeeExperience->enviroment_enviroment);
                        }
                    ?>',
                    enviroment_os: '<?php 
                        if ($enviromentItem = $employeeExperience->getEnvironment('os')) {
                            echo e($enviromentItem);
                        } else {
                            echo e($employeeExperience->enviroment_os);
                        }
                    ?>',
                    start_at: '{{ $employeeExperience->start_at }}',
                    end_at: '{{ $employeeExperience->end_at }}',
                    image: '{{ ViewHelper::getLinkImage($employeeExperience->image) }}',
                    responsible: '{{ $employeeExperience->responsible }}',
                    customer_name: '{{ $employeeExperience->customer_name }}',
                    description: "{!! $employeeExperience->description !!}",
                    poisition: '{{ $employeeExperience->poisition }}',
                    no_member: '{{ $employeeExperience->no_member }}',
                    other_tech: "{!! $employeeExperience->other_tech !!}",
                }
            };
        </script>
    </div>
</div>
<?php } //end function project html
}
?>
    
<!-- employee project experience -->
<div class="row skill-list-row">
    <div class="col-sm-12">
        <div class="row employee-skill-box-wrapper" data-btn-modal="true" 
            data-group="project_experiences" data-href="#employee-project_experience-form">
            <div class="col-sm-11 col-sm-offset-1 employee-skill-items item-full-col">
                <div class="row">
                    @if (isset($employeeProjectExperiences) && count($employeeProjectExperiences))
                        <?php $i = 0; ?>
                        @foreach ($employeeProjectExperiences as $employeeProjectExperience)
                            <?php $i++; ?>
                            <?php getHtmlEmployeeProjectExperience($employeeProjectExperience, $i); ?>
                        @endforeach
                    @else
                        <div class="col-sm-12">
                            <p class="text-warning">{{ trans('team::view.Not found item') }}</p>
                        </div>
                    @endif
                </div>
                <?php getHtmlEmployeeProjectExperience(null, 0); ?>
            </div>
            @if ($experiencePermission)
            <div class="col-sm-2 col-sm-offset-1">
                <button type="button" class="btn-add add-college add-project-experience" data-toggle="tooltip" 
                    data-placement="bottom" title="{{ trans('team::view.Add new project') }}"
                    data-modal="true">
                    <i class="fa fa-plus"></i>
                </button>
            </div>
            @endif
        </div>
    </div>
</div>
<!-- //end employee project experience -->
