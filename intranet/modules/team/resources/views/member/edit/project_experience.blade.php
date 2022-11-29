<?php

use Rikkei\Core\View\Form;
use Rikkei\Core\View\View as ViewHelper;
use Rikkei\Team\View\Permission;
use Rikkei\Core\Model\CoreModel;

if (isset($isCreatePage) && $isCreatePage || Permission::getInstance()->isScopeCompany(null, 'team::team.member.edit')) {
    $experiencePermission = true;
} else {
    $experiencePermission = false;
}
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
    <div class="col-sm-12 project-exerience-wrapper employee-experience-item esbw-item<?php if(! $i): ?> hidden<?php endif; ?>" data-id="{{ $i }}">
        <div class="esi-image">
            <img src="{{ ViewHelper::getLinkImage($employeeExperience->image) }}" 
                class="img-responsive image-preview" data-tbl="project_experience" data-col="image" />
        </div>
        <div class="esi-content">
            <p>
                <a href="#" class="esi-title" data-tbl="project_experience" data-col="name"
                   data-modal="true">{{ $employeeExperience->name }}</a>
            </p>
            <p>
                <span>{{ trans('team::view.Period') }}</span>
                <span data-tbl="project_experience" data-col="start_at" data-date-format="M/Y">{{ ViewHelper::formatDateTime('Y-m-d', 'm/Y', $employeeExperience->start_at) }}</span>
                ~
                <span data-tbl="project_experience" data-col="end_at" data-date-format="M/Y">{{ ViewHelper::formatDateTime('Y-m-d', 'm/Y', $employeeExperience->end_at) }}</span>
                &nbsp;&nbsp;&nbsp;
                <span>{{ trans('team::view.Language') }}:<span> <span data-tbl="project_experience" data-col="enviroment_language"><?php 
                        if ($enviromentItem = $employeeExperience->getEnvironment('language')) {
                            echo e($enviromentItem);
                        } else {
                            echo e($employeeExperience->enviroment_language);
                        }
                    ?></span>
                &nbsp;&nbsp;&nbsp;
                <span>{{ trans('team::view.Environment') }}:<span> <span data-tbl="project_experience" data-col="enviroment_enviroment"><?php 
                        if ($enviromentItem = $employeeExperience->getEnvironment('enviroment')) {
                            echo e($enviromentItem);
                        } else {
                            echo e($employeeExperience->enviroment_enviroment);
                        }
                    ?></span>
                &nbsp;&nbsp;&nbsp;
                <span>{{ trans('team::view.OS') }}:<span> <span data-tbl="project_experience" data-col="enviroment_os"><?php 
                        if ($enviromentItem = $employeeExperience->getEnvironment('os')) {
                            echo e($enviromentItem);
                        } else {
                            echo e($employeeExperience->enviroment_os);
                        }
                    ?></span>
            </p>
            <p>
                <span>{{ trans('team::view.Responsible') }}:<span> <span data-tbl="project_experience" data-col="responsible">{{ $employeeExperience->responsible }}</span>
            </p>
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
                    description: '{{ $employeeExperience->description }}',
                    poisition: '{{ $employeeExperience->poisition }}',
                    no_member: '{{ $employeeExperience->no_member }}',
                    other_tech: '{{ $employeeExperience->other_tech }}',
                }
            };
        </script>
    </div>
<?php } //end function project html
}
?>
    
<!-- employee project experience -->
<div class="row skill-list-row">
    <div class="col-sm-12">
        <div class="row employee-skill-box-wrapper" data-btn-modal="true" 
            data-group="project_experiences" data-href="#employee-project_experience-form">
            <div class="col-sm-12 employee-skill-items item-full-col">
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
                <div class="col-sm-2">
                    <button type="button" class="btn-add add-college" data-toggle="tooltip" 
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
