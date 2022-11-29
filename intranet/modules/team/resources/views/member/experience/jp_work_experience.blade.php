<?php

use Rikkei\Core\View\Form;
use Rikkei\Core\View\View as ViewHelper;
use Rikkei\Team\View\Permission;
use Rikkei\Core\Model\CoreModel;

$experiencePermission = Permission::getInstance()->isAllow('team::team.member.edit.exerience');
?>
<?php
/**
 * function html render employee work experience
*/
function getHtmlEmployeeWordExperience($employeeExperience = null, $i = 0) 
{ 
    if (! $employeeExperience) {
        $employeeExperience = new CoreModel();
    }
?>
    <div class="col-sm-6 employee-experience-item employee-jp-experience-item esbw-item<?php if(! $i): ?> hidden<?php endif; ?>" data-id="{{ $i }}">
        <div class="esi-content jp-esi-content">
            <h4 class="jp-esi-title">
                <a href="javascript:void(0);" class="esi-title work_experience-title" data-tbl="work_experience" data-col="company"
                   data-id="{{ $i }}"
                   data-modal="true">{{ $employeeExperience->company }}</a>
            </h4>
            <p class="jp-experience-callout">
                <span data-tbl="work_experience" data-col="start_at" data-date-format="M/Y">{{ ViewHelper::formatDateTime('Y-m-d', 'm/Y', $employeeExperience->start_at) }}</span>
                ~
                <span data-tbl="work_experience" data-col="end_at" data-date-format="M/Y">{{ ViewHelper::formatDateTime('Y-m-d', 'm/Y', $employeeExperience->end_at) }}</span>
            </p>
            <p class="jp-experience-callout">
                <b>{{ trans('team::view.Address') }}:</b> <span data-tbl="work_experience" data-col="address">{{ $employeeExperience->address }}</span>
            </p>
            <p class="jp-experience-callout">
                <b>{{ trans('team::view.Positon') }}:</b> <span data-tbl="work_experience" data-col="position">{{ $employeeExperience->position }}</span>
            </p>
        </div>
        <script>
            employeeSkill.work_experiences[{{ $i }}] = {
                work_experience: {
                    id: '{{ $employeeExperience->id }}',
                    company: '{{ $employeeExperience->company }}',
                    position: '{{ $employeeExperience->position }}',
                    start_at: '{{ $employeeExperience->start_at }}',
                    end_at: '{{ $employeeExperience->end_at }}',
                    image: '{{ ViewHelper::getLinkImage($employeeExperience->image) }}',
                    type:'{{ $employeeExperience->type }}',
                    address: '{{ $employeeExperience->address }}'
                }
            };
        </script>
    </div>
<?php } //end function work experience html
?>
    
<!-- employee work experience -->
<div class="row skill-list-row">
    <div class="col-sm-12">
        <div class="row employee-skill-box-wrapper" data-btn-modal="true" 
            data-group="work_experiences" data-href="#employee-work_experience-form">
            <div class="col-sm-10 employee-skill-items" id="employee-work_experience">
                <div class="row">
                    @if ($employeeWorkExperiences && count($employeeWorkExperiences))
                        <?php $i = 0; ?>
                        @foreach ($employeeWorkExperiences as $employeeWorkExperience)
                            <?php $i++; ?>
                            <?php getHtmlEmployeeWordExperience($employeeWorkExperience, $i); ?>
                        @endforeach
                    @else
                        <div class="col-sm-12">
                            <p class="text-warning">{{ trans('team::view.Not found item') }}</p>
                        </div>
                    @endif
                </div>
                <?php getHtmlEmployeeWordExperience(null, 0); ?>
            </div>
            @if ($experiencePermission)
                <div class="col-sm-2 add-skill-item">
                    <button type="button" class="btn-add add-college" data-toggle="tooltip" id="add-work-experience" 
                        data-placement="bottom" title="{{ trans('team::view.Add new company') }}"
                        data-modal="true">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
<!-- //end employee experience -->
