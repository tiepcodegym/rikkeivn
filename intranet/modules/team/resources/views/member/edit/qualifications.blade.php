<?php

use Rikkei\Core\View\Form;
use Rikkei\Core\View\View as ViewHelper;
use Rikkei\Team\View\Permission;
use Rikkei\Core\Model\CoreModel;

if (isset($isCreatePage) && $isCreatePage || Permission::getInstance()->isScopeCompany(null, 'team::team.member.edit')) {
    $skillPermission = true;
} else {
    $skillPermission = false;
}
?>
<?php
/**
 * function html render employee school
*/
function getHtmlEmployeeSchool($employeeSchool = null, $i = 0) 
{ 
    if (! $employeeSchool) {
        $employeeSchool = new CoreModel();
    }
?>
    <div class="col-sm-6 employee-school-item esbw-item<?php if(! $i): ?> hidden<?php endif; ?>" data-id="{{ $i }}">
        <div class="esi-image">
            <img src="{{ ViewHelper::getLinkImage($employeeSchool->image) }}" 
                class="img-responsive image-preview" data-tbl="school" data-col="image" />
        </div>
        <div class="esi-content">
            <p>
                <a href="#" class="esi-title" data-tbl="school" data-col="name"
                   data-modal="true">{{ $employeeSchool->name }}</a>
            </p>
            <p>
                <span>{{ trans('team::view.Class of') }} <span data-tbl="employee_school" data-col="start_at" data-date-format="Y">{{ ViewHelper::formatDateTime('Y-m-d H:i:s', 'Y', $employeeSchool->start_at) }}</span>
                &nbsp;&nbsp;&nbsp;
                <span data-tbl="employee_school" data-col="majors">{{ $employeeSchool->majors }}</span>
                &nbsp;&nbsp;&nbsp;
                <span data-tbl="school" data-col="province">{{ $employeeSchool->province }}</span>, <span data-tbl="school" data-col="country">{{ $employeeSchool->country }}</span>
            </p>
        </div>
        <script>
            employeeSkill.schools[{{ $i }}] = {
                school: {
                    id: '{{ $employeeSchool->id }}',
                    name: '{{ $employeeSchool->name }}',
                    country: '{{ $employeeSchool->country }}',
                    province: '{{ $employeeSchool->province }}',
                    image: '{{ ViewHelper::getLinkImage($employeeSchool->image) }}',
                },
                employee_school: {
                    majors: '{{ $employeeSchool->majors }}',
                    start_at: '{{ ViewHelper::getDate($employeeSchool->start_at) }}',
                    end_at: '{{ ViewHelper::getDate($employeeSchool->end_at) }}',
                    quality: '{{ $employeeSchool->quality }}',
                    type: '{{ $employeeSchool->type }}',
                    degree: '{{ $employeeSchool->degree }}',
                    faculty : '{{ $employeeSchool->faculty }}',
                    is_graduated : '{{ $employeeSchool->is_graduated }}',
                    awarded_date :'{{ $employeeSchool->awarded_date }}',
                    note : '{{ $employeeSchool->note }}',
                }
            };
        </script>
    </div>
<?php } //end function school html
?>
    
<?php
/**
 * function html render employee school
*/
function getHtmlEmployeeLanguage($employeeLanguage = null, $i = 0) 
{ 
    if (! $employeeLanguage) {
        $employeeLanguage = new CoreModel();
    }
?>
    <div class="col-sm-6 employee-school-item esbw-item<?php if(! $i): ?> hidden<?php endif; ?>" data-id="{{ $i }}">
        <div class="esi-image">
            <img src="{{ ViewHelper::getLinkImage($employeeLanguage->image) }}" 
                class="img-responsive image-preview" data-tbl="language" data-col="image" />
        </div>
        <div class="esi-content">
            <p>
                <a href="#" class="esi-title" data-tbl="language" data-col="name"
                   data-modal="true">{{ $employeeLanguage->name }}</a>
            </p>
            <p>
                <span>{{ trans('team::view.Level') }} <span data-tbl="employee_language" data-col="level" data-label-format="level_language">{{ ViewHelper::getLabelLanguageLevel($employeeLanguage->level) }}</span>
                &nbsp;&nbsp;&nbsp;
                <span>{{ trans('team::view.From') }} <span data-tbl="employee_language" data-col="start_at" data-date-format="M/Y">{{ ViewHelper::formatDateTime('Y-m-d', 'm/Y', $employeeLanguage->start_at) }}</span>
            </p>
        </div>
        <script>
            employeeSkill.languages[{{ $i }}] = {
                language: {
                    id: '{{ $employeeLanguage->id }}',
                    name: '{{ $employeeLanguage->name }}',
                    image: '{{ ViewHelper::getLinkImage($employeeLanguage->image) }}',
                },
                employee_language: {
                    level: '{{ $employeeLanguage->level }}',
                    start_at: '{{ $employeeLanguage->start_at }}',
                    end_at: '{{ $employeeLanguage->end_at }}',
                    note: '{{ $employeeLanguage->note }}',
                    listen: '{{ $employeeLanguage->listen }}',
                    speak: '{{ $employeeLanguage->speak }}',
                    read: '{{ $employeeLanguage->read }}',
                    write: '{{ $employeeLanguage->write }}',
                    place: '{{ $employeeLanguage->place }}',
                    sum: '{{ $employeeLanguage->sum }}',
                }
            };
        </script>
    </div>
<?php } //end function languages html
?>

<?php
/**
 * function html render employee school
*/
function getHtmlEmployeeCetificate($employeeCetificate = null, $i = 0) 
{ 
    if (! $employeeCetificate) {
        $employeeCetificate = new CoreModel();
    }
?>
    <div class="col-sm-6 employee-school-item esbw-item<?php if(! $i): ?> hidden<?php endif; ?>" data-id="{{ $i }}">
        <div class="esi-image">
            <img src="{{ ViewHelper::getLinkImage($employeeCetificate->image) }}" 
                class="img-responsive image-preview" data-tbl="cetificate" data-col="image" />
        </div>
        <div class="esi-content">
            <p>
                <a href="#" class="esi-title" data-tbl="cetificate" data-col="name"
                   data-modal="true">{{ $employeeCetificate->name }}</a>
            </p>
            <p>
                <span>{{ trans('team::view.From') }} <span data-tbl="employee_cetificate" data-col="start_at" data-date-format="M/Y">{{ ViewHelper::formatDateTime('Y-m-d', 'm/Y', $employeeCetificate->start_at) }}</span>
            </p>
        </div>
        <script>
            employeeSkill.cetificates[{{ $i }}] = {
                cetificate: {
                    id: '{{ $employeeCetificate->id }}',
                    name: '{{ $employeeCetificate->name }}',
                    image: '{{ ViewHelper::getLinkImage($employeeCetificate->image) }}',
                },
                employee_cetificate: {
                    start_at: '{{ $employeeCetificate->start_at }}',
                    end_at: '{{ $employeeCetificate->end_at }}',
                    level: '{{ $employeeCetificate->level }}',
                    note: '{{ $employeeCetificate->note }}',
                    place: '{{ $employeeCetificate->place }}',
                }
            };
        </script>
    </div>
<?php } //end function languages html
?>

<?php
/**
 * function html render employee skill
*/
function getHtmlEmployeeSkill($employeeSkill = null, $i = 0, $type = 'program')
{ 
    if (! $employeeSkill) {
        $employeeSkill = new CoreModel();
    }
?>
    <div class="col-sm-6 employee-school-item esbw-item<?php if(! $i): ?> hidden<?php endif; ?>" data-id="{{ $i }}">
        <div class="esi-image">
            <img src="{{ ViewHelper::getLinkImage($employeeSkill->image) }}" 
                class="img-responsive image-preview" data-tbl="{{ $type }}" data-col="image" />
        </div>
        <div class="esi-content">
            <p>
                <a href="#" class="esi-title" data-tbl="{{ $type }}" data-col="name"
                   data-modal="true">{{ $employeeSkill->name }}</a>
            </p>
            <p>
                <span>{{ trans('team::view.Level') }} <span data-tbl="employee_{{ $type }}" data-col="level" data-label-format="level_normal">{{ ViewHelper::getLabelNormalLevel($employeeSkill->level) }}</span>
                ~
                <span data-tbl="employee_{{ $type }}" data-col="experience">{{ $employeeSkill->experience }}</span> <span>{{ trans('team::view.years') }}
            </p>
        </div>
        <script>
            employeeSkill.{{ $type }}s[{{ $i }}] = {
                {{ $type }}: {
                    id: '{{ $employeeSkill->id }}',
                    name: '{{ $employeeSkill->name }}',
                    image: '{{ ViewHelper::getLinkImage($employeeSkill->image) }}',
                },
                employee_{{ $type }}: {
                    level: '{{ $employeeSkill->level }}',
                    experience: '{{ $employeeSkill->experience }}',
                }
            };
        </script>
    </div>
<?php } //end function skill html
?>

<!-- employee school -->
<div class="row skill-list-row">
    <div class="col-sm-12">
        <h5 class="skill-title">{{ trans('team::view.Content has studied') }}</h5>
    </div>
    <div class="col-sm-12">
        <div class="row employee-skill-box-wrapper" data-btn-modal="true" 
            data-group="schools" data-href="#employee-school-form" data-change="schools">
            <div class="col-sm-10 employee-skill-items">
                <div class="row">
                    @if ($employeeSchools && count($employeeSchools))
                        <?php $i = 0; ?>
                        @foreach ($employeeSchools as $employeeSchool)
                            <?php $i++; ?>
                            <?php getHtmlEmployeeSchool($employeeSchool, $i); ?>
                        @endforeach
                    @else
                        <div class="col-sm-12">
                            <p class="text-warning">{{ trans('team::view.Not found item') }}</p>
                        </div>
                    @endif
                </div>
                <?php getHtmlEmployeeSchool(null, 0); ?>
            </div>
            @if ($skillPermission)
                <div class="col-sm-2 add-skill-item">
                    <button type="button" class="btn-add add-college" data-toggle="tooltip" 
                        data-placement="bottom" title="{{ trans('team::view.Add a college') }}"
                        data-modal="true">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
<!-- //end employee school -->

<!-- employee Language -->
<div class="row skill-list-row">
    <div class="col-sm-12">
        <h5 class="skill-title">{{ trans('team::view.Language foreign') }}</h5>
    </div>
    <div class="col-sm-12">
        <div class="row employee-skill-box-wrapper" data-btn-modal="true" 
            data-group="languages" data-href="#employee-language-form">
            <div class="col-sm-10 employee-skill-items">
                <div class="row">
                    @if ($employeeLanguages && count($employeeLanguages))
                        <?php $i = 0; ?>
                        @foreach ($employeeLanguages as $employeeLanguage)
                            <?php $i++; ?>
                            <?php getHtmlEmployeeLanguage($employeeLanguage, $i); ?>
                        @endforeach
                    @else
                        <div class="col-sm-12">
                            <p class="text-warning">{{ trans('team::view.Not found item') }}</p>
                        </div>
                    @endif
                </div>
                <?php getHtmlEmployeeLanguage(null, 0); ?>
            </div>
            @if ($skillPermission)
                <div class="col-sm-2 add-skill-item">
                    <button type="button" class="btn-add add-college" data-toggle="tooltip" 
                        data-placement="bottom" title="{{ trans('team::view.Add new language') }}"
                        data-modal="true">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
<!-- //end employee language -->

<!-- employee Cetificate -->
<div class="row skill-list-row">
    <div class="col-sm-12">
        <h5 class="skill-title">{{ trans('team::view.Cetificate') }}</h5>
    </div>
    <div class="col-sm-12">
        <div class="row employee-skill-box-wrapper" data-btn-modal="true" 
            data-group="cetificates" data-href="#employee-cetificate-form">
            <div class="col-sm-10 employee-skill-items">
                <div class="row">
                    @if ($employeeCetificates && count($employeeCetificates))
                        <?php $i = 0; ?>
                        @foreach ($employeeCetificates as $employeeCetificate)
                            <?php $i++; ?>
                            <?php getHtmlEmployeeCetificate($employeeCetificate, $i); ?>
                        @endforeach
                    @else
                        <div class="col-sm-12">
                            <p class="text-warning">{{ trans('team::view.Not found item') }}</p>
                        </div>
                    @endif
                </div>
                <?php getHtmlEmployeeCetificate(null, 0); ?>
            </div>
            @if ($skillPermission)
                <div class="col-sm-2 add-skill-item">
                    <button type="button" class="btn-add add-college" data-toggle="tooltip" 
                        data-placement="bottom" title="{{ trans('team::view.Add new cetificate') }}"
                        data-modal="true">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
<!-- //end employee Cetificate -->

<!-- employee program -->
<div class="row skill-list-row">
    <div class="col-sm-12">
        <h5 class="skill-title">{{ trans('team::view.Programming Language') }}</h5>
    </div>
    <div class="col-sm-12">
        <div class="row employee-skill-box-wrapper" data-btn-modal="true" 
            data-group="programs" data-href="#employee-program-form">
            <div class="col-sm-10 employee-skill-items">
                <div class="row">
                    @if ($employeePrograms && count($employeePrograms))
                        <?php $i = 0; ?>
                        @foreach ($employeePrograms as $employeeProgram)
                            <?php $i++; ?>
                            <?php getHtmlEmployeeSkill($employeeProgram, $i, 'program'); ?>
                        @endforeach
                    @else
                        <div class="col-sm-12">
                            <p class="text-warning">{{ trans('team::view.Not found item') }}</p>
                        </div>
                    @endif
                </div>
                <?php getHtmlEmployeeSkill(null, 0, 'program'); ?>
            </div>
            @if ($skillPermission)
                <div class="col-sm-2 add-skill-item">
                    <button type="button" class="btn-add add-college" data-toggle="tooltip" 
                        data-placement="bottom" title="{{ trans('team::view.Add new programming language') }}"
                        data-modal="true">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
<!-- //end employee program -->

<!-- employee database -->
<div class="row skill-list-row">
    <div class="col-sm-12">
        <h5 class="skill-title">{{ trans('team::view.Database') }}</h5>
    </div>
    <div class="col-sm-12">
        <div class="row employee-skill-box-wrapper" data-btn-modal="true" 
            data-group="databases" data-href="#employee-database-form">
            <div class="col-sm-10 employee-skill-items">
                <div class="row">
                    @if ($employeeDatabases && count($employeeDatabases))
                        <?php $i = 0; ?>
                        @foreach ($employeeDatabases as $employeeDatabase)
                            <?php $i++; ?>
                            <?php getHtmlEmployeeSkill($employeeDatabase, $i, 'database'); ?>
                        @endforeach
                    @else
                        <div class="col-sm-12">
                            <p class="text-warning">{{ trans('team::view.Not found item') }}</p>
                        </div>
                    @endif
                </div>
                <?php getHtmlEmployeeSkill(null, 0, 'database'); ?>
            </div>
            @if ($skillPermission)
                <div class="col-sm-2 add-skill-item">
                    <button type="button" class="btn-add add-college" data-toggle="tooltip" 
                        data-placement="bottom" title="{{ trans('team::view.Add new database') }}"
                        data-modal="true">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
<!-- //end employee database -->

<!-- employee os -->
<div class="row skill-list-row">
    <div class="col-sm-12">
        <h5 class="skill-title">{{ trans('team::view.OS') }}</h5>
    </div>
    <div class="col-sm-12">
        <div class="row employee-skill-box-wrapper" data-btn-modal="true" 
            data-group="oss" data-href="#employee-os-form">
            <div class="col-sm-10 employee-skill-items">
                <div class="row">
                    @if ($employeeOss && count($employeeOss))
                        <?php $i = 0; ?>
                        @foreach ($employeeOss as $employeeOs)
                            <?php $i++; ?>
                            <?php getHtmlEmployeeSkill($employeeOs, $i, 'os'); ?>
                        @endforeach
                    @else
                        <div class="col-sm-12">
                            <p class="text-warning">{{ trans('team::view.Not found item') }}</p>
                        </div>
                    @endif
                </div>
                <?php getHtmlEmployeeSkill(null, 0, 'os'); ?>
            </div>
            @if ($skillPermission)
                <div class="col-sm-2 add-skill-item">
                    <button type="button" class="btn-add add-college" data-toggle="tooltip" 
                        data-placement="bottom" title="{{ trans('team::view.Add new os') }}"
                        data-modal="true">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
<!-- //end employee os -->