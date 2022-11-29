@extends('layouts.default')
<?php

use Rikkei\Core\View\Form;
use Rikkei\Team\Model\WorkExperience;
use Rikkei\Team\View\Permission;
use Rikkei\Core\View\View;

$employeePermission = Permission::getInstance()->isAllow('team::team.member.edit.exerience');
$employeeWorkExperiences  = null;
$employeeWorkExperiencesJapan = null;
$employeeProjectExperiences = null;

if(isset($employeeModelItem) && $employeeModelItem) {
    $employeeWorkExperiences = $employeeModelItem->getWorkExperience();
    $workExperienceIds = collect($employeeWorkExperiences)->map(function($i) {
        return $i->id;
    })->toArray();
    
    $workExperiencesJapan = $employeeModelItem->getWorkExperienceJapan();
    Form::setData($workExperiencesJapan,'work_experience_japan');
    $employeeProjectExperiences = $employeeModelItem->getProjectExperienceGroupWork($workExperienceIds);
}

?>

@section('title')
{{ trans('team::view.Profile of :employeeName', ['employeeName' => Form::getData('employee.name')]) }}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.min.css" />
<link rel="stylesheet" href="{{ URL::asset('team/css/style.css') }}" />
@endsection

@section('content')
<?php
$urlSubmit = route('team::member.profile.japanExperience.save', ['employeeId' => $employeeId]);
?>
<div class="row member-profile">
    <form action="{{ $urlSubmit }}" method="post" id="form-work-experiences-japan"
          enctype="multipart/form-data" autocomplete="off">
        {!! csrf_field() !!}
        @if (Form::getData('employee.id'))
        <input type="hidden" name="employee_id" value="{{ Form::getData('employee.id') }}" />
        @endif
        
        @if (isset($isEdit) && $isEdit)
            <input type="hidden" name="isEdit" value="1" />
        @endif
        <input type="hidden" name="school[id]" value="{{ Form::getData('school.id') }}" />
        <input type="hidden" name="employee_skill" value="">  
        <!-- left menu -->
        <div class="col-lg-2 col-md-3">
            @include('team::member.left_menu',['active'=>'japan_experience'])
        </div>
        <!-- /. End left menu -->

        <!-- Right column-->
        <!-- Edit form -->
        <script>
            /**
             * employee skill data format json object
             */
            var employeeSkill = {
                work_experiences: {},
                project_experiences: {},
            };
        </script>
        <div class="col-lg-10 col-md-9 tab-content" style="padding: 0 50px;">
            <div class="content-experience">
            <!-- box work experience -->
            <div class="box box-info work-experience-box">
                <div class="box-header with-border">
                    <h2 class="box-title" style="font-size: 21px;max-width: 100%;word-wrap: break-word;">
                        <i class="fa fa-tripadvisor"></i>
                        {{ trans('team::profile.Japan experiences') }}
                    </h2>
                </div>
                <div class="box-body">
                    @include('team::member.experiences.jp_work_experience')
                </div>
            </div> <!-- end box work experience -->
            @if($employeePermission)
            <div class="col-md-12 box-action">
                <p class="text-center">
                    <button type="submit" class="hidden" name="submit" id="form-work-experiences-japan-save" />
                        <i class="fa fa-save"></i>
                        {{ trans('team::view.Save') }}
                    </button>
                </p>
            </div>
            @endif
            </div>
        </div>
    </form>
    <div class="col-lg-10 col-md-9 tab-content" style="padding: 0 50px;">
        @include('team::member.experiences._form')
    </div>
    
    @if (Permission::getInstance()->isAllow('team::team.member.edit.exerience'))
        @include('team::member.edit.project_experience_modal')
    @endif
</div>
<?php
//remove flash session
Form::forget();
?>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="{{ URL::asset('lib/js/moment.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="{{ URL::asset('common/js/methods.validate.js') }}"></script>
<script src="{{ URL::asset('team/js/script.js') }}"></script>

<script>
    jQuery(document).ready(function($) {
        radioChange();
        
        var autoComplete = {};
        var urlLoadAutoComplete = '{{ URL::route('core::ajax.skills.autocomplete') }}';
        var groupChange = {};
        var employeeSkillNo = {};
        <?php if($employeeWorkExperiences): ?>
            employeeSkillNo.work_experiences = <?php echo count($employeeWorkExperiences) ?>;
        <?php else: ?>
            employeeSkillNo.work_experiences = 0;
        <?php endif;?>
            
        <?php if($employeeProjectExperiences): ?>
            employeeSkillNo.project_experiences = <?php echo count($employeeProjectExperiences) ?>;
        <?php else: ?>
            employeeSkillNo.project_experiences = 0;
        <?php endif;?>
            employeeSkillNo.project_experiences++;
            
        $().employeeSkillAction({
            'autoComplete' : autoComplete,
            'urlLoadAutoComplete': urlLoadAutoComplete,
            'employeeSkillNo': employeeSkillNo,
            'employeeSkill': employeeSkill,
            'groupChange' : groupChange,
        });
        
        //validate leave date
        jQuery.validator.addMethod("laterThan", 
            function(value, element, params) {     
                if (!value){
                    return true;
                }
                return new Date(value) > new Date($(params).val());
            },'Date must be afer '+$("#work_experience_japan-from").val()+'.');
        $("#work_experience_japan-to").rules('add', { laterThan: "#work_experience_japan-from" });
        
        var exRules = {
            'name': {
                required: true,
                rangelength: [0, 255],
            },
            'position': {
                required: true,
                rangelength: [0, 255],
            },
            'start_at' :{
                required: true,
            },
            'end_at' :{
                required: true,
            }
        };
        
        var exMessages = {
            'name': {
                required: '<?php echo trans('core::view.This field is required'); ?>',
                rangelength: '<?php echo trans('core::view.This field not be greater than :number characters', ['number' => 255]) ; ?>',
            },
            'position': {
                required: '<?php echo trans('core::view.This field is required'); ?>',
                rangelength: '<?php echo trans('core::view.This field not be greater than :number characters', ['number' => 255]) ; ?>',
            },
            'start_at' :{
                required: '<?php echo trans('core::view.This field is required'); ?>',
            },
            'end_at' :{
                required: '<?php echo trans('core::view.This field is required'); ?>',
            }
        };
       
        var proMessages = $.extend(exMessages, {
            'customer_name':{
                required: '<?php echo trans('core::view.This field is required'); ?>',
                rangelength: '<?php echo trans('core::view.This field not be greater than :number characters', ['number' => 255]) ; ?>',
            },
            'description': {
                required: '<?php echo trans('core::view.This field is required'); ?>',
                rangelength: '<?php echo trans('core::view.This field not be greater than :number characters', ['number' => 255]) ; ?>',
            },
            'no_member': {
                required: '<?php echo trans('core::view.This field is required'); ?>',
                number: '<?php echo trans('core::view.Please enter a valid number');?>',
            },
            'poisition': {
                required: '<?php echo trans('core::view.This field is required'); ?>',
                rangelength: '<?php echo trans('core::view.This field not be greater than :number characters', ['number' => 255]) ; ?>',
            },
            'responsible': {
                required: '<?php echo trans('core::view.This field is required'); ?>',
                rangelength: '<?php echo trans('core::view.This field not be greater than :number characters', ['number' => 255]) ; ?>',
            },
        });
        var proRules = $.extend(exRules, {
            'customer_name':{
                required: true,
                rangelength: [0, 255],
            },
            'description': {
                required: true,
                rangelength: [0, 255],
            },
            'no_member': {
                required: true,
                number: true,
            },
            'poisition': {
                required: true,
                rangelength: [0, 255],
            },
            'responsible': {
                required: true,
                rangelength: [0, 255],
            },
        });
        
        $('#form-employee-work_experience').validate({
            rules : exRules,
            messages : exMessages,
            lang: 'vi',
            success: "valid",
            errorPlacement: function(error, element) {
                    if(element.hasClass('date-picker')) {
                        error.insertAfter(element.parent());
                    } else {
                        error.insertAfter(element)
                    }
            },//end errorPlacement
        });
        
        $('#form-employee-project_experience').validate({
            rules: proRules,
            messages: proMessages,
            lang:'vi',
            success: 'valid',
            submitHandler : function(form) {
                //get data-work_experience_id compare data-id
                var work_experience_ids = JSON.parse($('#form-employee-project_experience').attr('data-work_experience_ids'));
                $.each(work_experience_ids, function(i, v){
                    var ele = $('.employee-skill-box-wrapper[data-href="#employee-project_experience-form"] .employee-experience-item[data-id='+i+']');
                    if(ele.length) {
                        ele.attr('data-work_experience_id', v);
                    }
                })
            },
        });
        //Date picker
        var optionDatePicker = {
            autoclose: true,
            format: 'yyyy-mm-dd',
            weekStart: 1,
            todayHighlight: true
        };
        $('.date-picker').datepicker(optionDatePicker);
        
        $('.employee-skill-box-wrapper[data-href="#employee-project_experience-form"] .employee-experience-item').on('click', function() {
            var skillbox = $('.employee-skill-box-wrapper[data-href="#employee-project_experience-form"]');
            var arr = Array();
            skillbox.find('.employee-experience-item.esbw-item:not([data-id=0])').each(function() {
                var that = $(this);
                arr[that.attr('data-id')] = that.attr('data-work_experience_id');
            });
            $('#form-employee-project_experience').attr('data-work_experience_ids', JSON.stringify(arr));
        });

        function radioChange()
        {
           $('#work_experience_japan-want_to_japan').on('change', function(e) {
                e.preventDefault();
                var val = $(this).is(':checked');
                $('#work_experience_japan-from , #work_experience_japan-to, #work_experience_japan-note').prop('disabled', !val);
           });
        }
        
        function showForm($id) {
            $id = parseInt($id);
            setFormData($id);
            var work_experience_id = parseInt(employeeSkill.work_experiences[$id].work_experience.id);
            $('.project-exerience-wrapper').addClass('hidden');
            $('.project-exerience-wrapper[data-work_experience_id="'+ work_experience_id +'"]').removeClass('hidden');
            
            $('#box_add').show(800);
            $('#project-experience-box').show(800);
            $('.content-experience').hide(800);
            $('#work_experience-index').val($id);
            if($id != 0) {
                $('#remove').removeClass('hidden');
            } else {
                $('#remove').addClass('hidden');       
            }
        }
        
        function hideForm(){
            $('#box_add').hide(800);
            $('#project-experience-box').hide(800);
            $('.content-experience').show(800);
            $('#work_experience-index').val(0);
            resetData();
        }
        function resetData() {
            $('#form-employee-work_experience').find("input[type=text], textarea").each(function(){
                var that = $(this);
                that.val("");
                that.removeClass('error');
                that.parent().find('label[class="error"]').text("");
            });
            $('#experience-submit').removeAttr('disabled');
        }
        
        function setFormData($id) {
            var work_experience = employeeSkill.work_experiences[$id].work_experience;
            $('#form-employee-work_experience').find("input[type=text], input[type=hidden], textarea").each(function($item) {
                var that = $(this);
                if(that.attr('data-tbl') && that.attr('data-col') && that.attr('data-tbl') == 'work_experience') {
                    that.val(work_experience[that.attr('data-col')]);
                }
            });
        }
        $('#add-work-experience, .work_experience-title').on('click' , function(){
            if( (id = $(this).data('id')) ) {
                showForm(id);
            } else  {
                showForm(0);
            }
        });
        
        $('.add-project-experience').on('click', function(e) {
            var work_id =  parseInt($('#form-employee-work_experience').find('input[data-tbl=work_experience][data-col=id]').first().val());
            // check if set work_experience_id complete change to new id
            var inter = window.setInterval(function() {
                var id = $('#project_experience-work_experience_id').val();
                if(!id) {
                    window.clearInterval(inter);
                }            
                //add work_experience_id for #employee-project_experience-form on update 
                $('#project_experience-work_experience_id').val(work_id);
            },1000);
        });
        
        $('#close').on('click', function() {
            hideForm();
        });
        $('#form-employee-work_experience').submit(function(e){
            e.preventDefault();
            if($(this).valid()){
                var id = $('#work_experience-index').val();
                var work_experience = objectifyForm($(this).serializeArray());
                if(!id || id == 0) {
                   id = ++employeeSkillNo.work_experiences;
                }
                employeeSkill.work_experiences[id] = {
                    work_experience: {
                        id: work_experience.id,
                        company: work_experience.name,
                        position: work_experience.position,
                        start_at: work_experience.start_at,
                        end_at: work_experience.end_at,
                        image: work_experience.image,
                        type: work_experience.type,
                        address: work_experience.address,
                    }
                };
                $('#form-work-experiences-japan-save').trigger('click');
            }
        });
        
        //submit form work-experiences-japan
        $('#form-work-experiences-japan').submit(function(e) {
            $('input[name=employee_skill]').val($.param(employeeSkill));
            return true;
        });
        
        //process data delete
        $('#remove').on('click', function(event) {
            var result = confirm('<?php echo trans('team::profile.Are you sure delete this item?')?>');
            if (result) {
                dataId = $('#work_experience-index').val();
                dataId = parseInt(dataId);
                if (dataId != 0) {
                    delete employeeSkill['work_experiences'][dataId];
                    $('.employee-experience-item[data-id='+dataId+']').remove();
                }
                $('#form-work-experiences-japan-save').trigger('click');
            }
        });
        
        function objectifyForm(formArray) {
            var returnArray = {};
            for (var i = 0; i < formArray.length; i++){
              returnArray[formArray[i]['name']] = formArray[i]['value'];
            }
            return returnArray;
        }
        
        function htmlExperienceItem($id) {
            var ele = $('#employee-work_experience .employee-experience-item[data-id='+$id+']');
            if(!ele.length){
                var original = $('#employee-work_experience .employee-experience-item[data-id=0]');
                var newHtml = original.clone();
                newHtml.attr('data-id',$id);
                newHtml.removeClass('hidden');
                
                original.parent('#employee-work_experience.employee-skill-items').find('.row').append($.parseHTML(newHtml[0].outerHTML));
                ele = $('#employee-work_experience .employee-experience-item[data-id='+$id+']');
            }
            setSpanData(ele,$id);   
        }
        
        function setSpanData(ele,$id) {
            ele.find('[data-tbl="work_experience"]').each(function($item) {
                var col = $(this).attr('data-col');
                var data = employeeSkill.work_experiences[$id].work_experience;
                if(data[col]) {
                    $(this).text(data[col]);
                    if(col == 'company') {
                        $(this).attr('data-id',$id);
                    }
                }
            });
            ele.find('.esi-title').on('click', function(){
                return showForm($id);
            });
        }
    });
</script>
@endsection