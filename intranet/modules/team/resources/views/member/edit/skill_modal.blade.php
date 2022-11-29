<?php

use Rikkei\Core\View\View;

$toOptionQuality = Rikkei\Team\Model\QualityEducation::getAll();
$toOptionDegree = Rikkei\Team\Model\EmployeeSchool::listDegree();
$toOptionType = Rikkei\Team\Model\EmployeeSchool::listEduType();
?>

<!-- school -->
<div class="modal fade employee-college-modal employee-skill-modal" 
    id="employee-school-form" tabindex="-1" role="dialog" data-id="1"
    data-group="schools">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ URL::route('core::upload.skill') }}" method="post" 
                    enctype="multipart/form-data" class="skill-modal-form" id="employee-skill-school-form">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ trans('team::view.Infomation of College') }}</h4>
                </div>
                <div class="modal-body">
                    <div class="form-horizontal form-label-left">
                        <input type="hidden" name="college[0][id]" value="" class="college-id input-skill-modal" 
                            data-tbl="school" data-col="id" />
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="college-name">{{ trans('team::view.Name') }}</label>
                            <div class="input-box col-md-9">
                                <input type="text" class="form-control college-name input-skill-modal" placeholder="{{ trans('team::view.Name') }}" 
                                    value="" name="name" id="college-name"
                                    data-tbl="school" data-col="name" data-autocomplete="true" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="college-image">{{ trans('team::view.Image') }}</label>
                            <div class="input-box col-md-9 input-box-img-preview college-image-box">
                                <div class="image-preview">
                                    <img src="{{ URL::asset('common/images/noimage.png') }}"
                                         id="college-image-preview" class="img-responsive college-image-preview skill-modal-image-preview" 
                                         data-tbl="school" data-col="image_preview"/>
                                </div>
                                <div class="img-input">
                                    <input type="file" class="form-control college-image skill-modal-image input-skill-modal" value="" 
                                        name="image" id="college-image" 
                                        data-tbl="school" data-col="image" />
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="college-country">{{ trans('team::view.Country') }}</label>
                            <div class="input-box col-md-9">
                                <input type="text" class="form-control college-country input-skill-modal" placeholder="{{ trans('team::view.Country') }}" 
                                    value="" name="country" id="college-country" 
                                    data-tbl="school" data-col="country" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="college-province">{{ trans('team::view.Province') }}</label>
                            <div class="input-box col-md-9">
                                <input type="text" class="form-control college-province input-skill-modal" placeholder="{{ trans('team::view.Province') }}" 
                                    value="" name="province" id="college-province" 
                                    data-tbl="school" data-col="province" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="college-faculty">{{ trans('team::profile.Falcuty') }}</label>
                            <div class="input-box col-md-9">
                                <input type="text" class="form-control college-faculty input-skill-modal" placeholder="{{ trans('team::profile.Falcuty') }}" 
                                    value="" name="faculty" id="college-faculty" 
                                    data-tbl="employee_school" data-col="faculty" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="college-majors">{{ trans('team::view.Majors') }}</label>
                            <div class="input-box col-md-9">
                                <input type="text" class="form-control college-majors input-skill-modal" placeholder="{{ trans('team::view.Majors') }}" 
                                    value="" name="majors" id="college-majors" 
                                    data-tbl="employee_school" data-col="majors" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="college-quality">{{ trans('team::profile.Quality') }}</label>
                            <div class="input-box col-md-9">
                                <select name="quality" class="form-control select-search college-quality input-skill-modal"
                                    id="college-quality"
                                    data-tbl="employee_school" data-col="quality">
                                    @foreach ($toOptionQuality as $code => $name)
                                        <option value="{{ $code }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="college-type">{{ trans('team::profile.Type') }}</label>
                            <div class="input-box col-md-9">
                                <select name="type" class="form-control select-search college-type input-skill-modal"
                                    id="college-type"
                                    data-tbl="employee_school" data-col="type">
                                    @foreach ($toOptionType as $code => $name)
                                        <option value="{{ $code }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="college-degree">{{ trans('team::profile.Degree') }}</label>
                            <div class="input-box col-md-9">
                                <select name="degree" class="form-control select-search college-degree input-skill-modal"
                                    id="college-degree"
                                    data-tbl="employee_school" data-col="degree">
                                    @foreach ($toOptionDegree as $code => $name)
                                        <option value="{{ $code }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="college-start">{{ trans('team::view.Start at') }}</label>
                            <div class="input-box col-md-9">
                                <input type="text" id="college-start" 
                                    class="form-control date-picker college-start input-skill-modal" placeholder="yyyy-mm-dd" 
                                    value="" name="start_at" id="college-start"
                                    data-tbl="employee_school" data-col="start_at" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="college-end">{{ trans('team::view.End at') }}</label>
                            <div class="input-box col-md-9">
                                <input type="text" id="college-end" 
                                    class="form-control date-picker college-end input-skill-modal" placeholder="yyyy-mm-dd" 
                                    value="" name="end_at" id="college-end"
                                    data-tbl="employee_school" data-col="end_at" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="college-is_graduated"></label>
                            <div class="input-box col-md-9">
                                <input type="checkbox" name="is_graduated"
                                       id="college-is_graduated"
                                       class="checkbox input-skill-modal college-is_graduated"
                                       data-col="is_graduated"
                                       data-tbl="employee_school"
                                       style="display: inline;"
                                       />
                                {{ trans('team::profile.Graduated')}}
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-3 control-label" for="college-awarded_date">{{ trans('team::profile.Awarded date') }}</label>
                            <div class="input-box col-md-9">
                                <input type="text" id="employee_school-awarded_date" 
                                    class="form-control date-picker college-awarded_date input-skill-modal" placeholder="yyyy-mm-dd" 
                                    value="" 
                                    name="awarded_date"
                                    id="college-awarded_date"
                                    data-tbl="employee_school" data-col="awarded_date"
                                    />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-3 control-label">{{ trans('team::profile.Note') }}</label>
                            <div class="input-box col-md-9">
                                <textarea class="form-control college-note input-skill-modal" placeholder="{{ trans('team::profile.Note') }}" 
                                    name="note"
                                    id="college-note"
                                    data-tbl="employee_school" data-col="note"></textarea>
                            </div>
                        </div>
<div class="form-group">
                            <label class="col-md-3 control-label" for="college-is_graduated"></label>
                            <div class="input-box col-md-9">
                                <input type="checkbox" name="is_graduated"
                                       id="college-is_graduated"
                                       class="checkbox input-skill-modal college-is_graduated"
                                       data-col="is_graduated"
                                       data-tbl="employee_school"
                                       style="display: inline;"
                                       />
                                {{ trans('team::profile.Graduated')}}
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-3 control-label" for="college-awarded_date">{{ trans('team::profile.Awarded date') }}</label>
                            <div class="input-box col-md-9">
                                <input type="text" id="employee_school-awarded_date" 
                                    class="form-control date-picker college-awarded_date input-skill-modal" placeholder="yyyy-mm-dd" 
                                    value="" 
                                    name="awarded_date"
                                    id="college-awarded_date"
                                    data-tbl="employee_school" data-col="awarded_date"
                                    />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-3 control-label">{{ trans('team::profile.Note') }}</label>
                            <div class="input-box col-md-9">
                                <textarea class="form-control college-note input-skill-modal" placeholder="{{ trans('team::profile.Note') }}" 
                                    name="note"
                                    id="college-note"
                                    data-tbl="employee_school" data-col="note"></textarea>
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
                    <button type="submit" class="btn-edit btn-action hidden">
                        <span>{{ trans('team::view.Save') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- ------------end school -->


<!-- languages -->
<div class="modal fade employee-skill-modal" 
    id="employee-language-form" role="dialog" data-id="1"
    data-group="languages">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ URL::route('core::upload.skill') }}" method="post" 
                    enctype="multipart/form-data" class="skill-modal-form" id="employee-skill-language-form">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ trans('team::view.Infomation of language') }}</h4>
                </div>
                <div class="modal-body">
                    <div class="form-horizontal form-label-left">
                        <input type="hidden" name="id" value="" class="input-skill-modal language-id" 
                            data-tbl="language" data-col="id" />
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="language-name">{{ trans('team::view.Name') }}</label>
                            <div class="input-box col-md-9">
                                <input type="text" class="form-control language-name input-skill-modal" placeholder="{{ trans('team::view.Name') }}" 
                                    value="" name="name" id="language-name"
                                    data-tbl="language" data-col="name" data-autocomplete="true" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="language-image">{{ trans('team::view.Image') }}</label>
                            <div class="input-box col-md-9 input-box-img-preview">
                                <div class="image-preview">
                                    <img src="{{ URL::asset('common/images/noimage.png') }}"
                                         class="img-responsive college-image-preview skill-modal-image-preview" 
                                         data-tbl="language" data-col="image_preview"/>
                                </div>
                                <div class="img-input">
                                    <input type="file" class="form-control skill-modal-image input-skill-modal" value="" 
                                        name="image" id="language-image" 
                                        data-tbl="language" data-col="image" />
                                </div>
                            </div>
                        </div>
                        <div class="form-group form-group-select2">
                            <label class="col-md-3 control-label" for="language-level">{{ trans('team::view.Level') }}</label>
                            <div class="input-box col-md-9">
                                <select name="level" id="language-level" class="form-control language-level input-skill-modal select-search has-search"
                                        value=""  data-tbl="employee_language" data-col="level">
                                    @foreach (View::toOptionLanguageLevel() as $option)
                                        <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="language-start">{{ trans('team::view.Start at') }}</label>
                            <div class="input-box col-md-9">
                                <input type="text" id="language-start" 
                                    class="form-control date-picker language-start input-skill-modal" placeholder="yyyy-mm-dd" 
                                    value="" name="start_at" id="language-start"
                                    data-tbl="employee_language" data-col="start_at" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="language-end">{{ trans('team::view.End at') }}</label>
                            <div class="input-box col-md-9">
                                <input type="text" id="language-end" 
                                    class="form-control date-picker language-end input-skill-modal" placeholder="yyyy-mm-dd" 
                                    value="" name="end_at" id="language-end"
                                    data-tbl="employee_language" data-col="end_at" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="language-place">{{ trans('team::profile.Certificate place') }}</label>
                            <div class="input-box col-md-9">
                                <input type="text" id="language-place" 
                                    class="form-control language-end input-skill-modal"
                                    value="" name="place" id="language-place"
                                    data-tbl="employee_language" data-col="place" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="language-listen">{{ trans('team::profile.Listen') }}</label>
                            <div class="input-box col-md-9">
                                <input type="text" id="language-listen" 
                                    class="form-control language-end input-skill-modal"
                                    value="" name="listen" id="language-listen"
                                    data-tbl="employee_language" data-col="listen" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="language-speak">{{ trans('team::profile.Speak') }}</label>
                            <div class="input-box col-md-9">
                                <input type="text" id="language-speak" 
                                    class="form-control language-speak input-skill-modal"
                                    value="" name="speak" id="language-speak"
                                    data-tbl="employee_language" data-col="speak" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="language-read">{{ trans('team::profile.Read') }}</label>
                            <div class="input-box col-md-9">
                                <input type="text" id="language-read" 
                                    class="form-control language-read input-skill-modal" 
                                    value="" name="read" id="language-read"
                                    data-tbl="employee_language" data-col="read" />
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="language-write">{{ trans('team::profile.Write') }}</label>
                            <div class="input-box col-md-9">
                                <input type="text" id="language-write" 
                                    class="form-control language-write input-skill-modal" 
                                    value="" name="write" id="language-write"
                                    data-tbl="employee_language" data-col="write" />
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="language-sum">{{ trans('team::profile.Sum') }}</label>
                            <div class="input-box col-md-9">
                                <input type="number"
                                    class="form-control language-sum input-skill-modal" 
                                    value="" name="sum" id="language-sum"
                                    data-tbl="employee_language" data-col="sum" />
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="language-note">{{ trans('team::profile.Note') }}</label>
                            <div class="input-box col-md-9">
                                <textarea id="language-note" 
                                    class="form-control language-note input-skill-modal" 
                                    value="" name="note" id="language-note"
                                    data-tbl="employee_language" data-col="note"></textarea>
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
<!-- ------------end languages -->

<!-- cetificate -->
<div class="modal fade employee-skill-modal" 
    id="employee-cetificate-form" role="dialog" data-id="1"
    data-group="cetificates">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ URL::route('core::upload.skill') }}" method="post" 
                    enctype="multipart/form-data" class="skill-modal-form" id="employee-skill-cetificate-form">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ trans('team::view.Infomation of cetificates') }}</h4>
                </div>
                <div class="modal-body">
                    <div class="form-horizontal form-label-left">
                        <input type="hidden" name="id" value="" class="input-skill-modal cetificate-id" 
                            data-tbl="cetificate" data-col="id" />
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="cetificate-name">{{ trans('team::view.Name') }}</label>
                            <div class="input-box col-md-9">
                                <input type="text" class="form-control cetificate-name input-skill-modal" placeholder="{{ trans('team::view.Name') }}" 
                                    value="" name="name" id="cetificate-name"
                                    data-tbl="cetificate" data-col="name" data-autocomplete="true" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="cetificate-image">{{ trans('team::view.Image') }}</label>
                            <div class="input-box col-md-9 input-box-img-preview">
                                <div class="image-preview">
                                    <img src="{{ URL::asset('common/images/noimage.png') }}"
                                         class="img-responsive college-image-preview skill-modal-image-preview" 
                                         data-tbl="cetificate" data-col="image_preview"/>
                                </div>
                                <div class="img-input">
                                    <input type="file" class="form-control skill-modal-image input-skill-modal" value="" 
                                        name="image" id="cetificate-image" 
                                        data-tbl="cetificate" data-col="image" />
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="cetificate-start">{{ trans('team::view.Start at') }}</label>
                            <div class="input-box col-md-9">
                                <input type="text" id="cetificate-start" 
                                    class="form-control date-picker cetificate-start input-skill-modal" placeholder="yyyy-mm-dd" 
                                    value="" name="start_at"
                                    data-tbl="employee_cetificate" data-col="start_at" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="cetificate-end">{{ trans('team::view.End at') }}</label>
                            <div class="input-box col-md-9">
                                <input type="text" id="cetificate-end" 
                                    class="form-control date-picker cetificate-end input-skill-modal" placeholder="yyyy-mm-dd" 
                                    value="" name="end_at"
                                    data-tbl="employee_cetificate" data-col="end_at" />
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="cetificate-place">{{ trans('team::profile.Certificate place') }}</label>
                            <div class="input-box col-md-9">
                                <input type="text" id="cetificate-place" 
                                    class="form-control place-place input-skill-modal"
                                    value="" name="place"
                                    data-tbl="employee_cetificate" data-col="place" />
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="cetificate-note">{{ trans('team::profile.Note') }}</label>
                            <div class="input-box col-md-9">
                                <textarea id="cetificate-note" 
                                    class="form-control cetificate-note input-skill-modal" 
                                    value="" name="note"
                                    data-tbl="employee_cetificate" data-col="note"></textarea>
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
<!-- ------------end cetificate -->

<?php
function getHtmlModalSkill($type = 'program', $programs = null) { ?>
<div class="modal fade employee-skill-modal" 
    id="employee-{{ $type }}-form" role="dialog" data-id="1"
    data-group="{{ $type }}s">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ URL::route('core::upload.skill') }}" method="post" 
                    enctype="multipart/form-data" class="skill-modal-form" id="employee-skill-{{ $type }}-form">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ trans('team::view.Infomation of ' . $type) }}</h4>
                </div>
                <div class="modal-body">
                    <div class="form-horizontal form-label-left">
                        <input type="hidden" name="id" value="" class="input-skill-modal {{ $type }}-id" 
                            data-tbl="{{ $type }}" data-col="id" />
                        <div class="form-group form-group-select2">
                            <label class="col-md-3 control-label" for="{{ $type }}-name">{{ trans('team::view.Name') }}</label>
                            <div class="input-box col-md-9">
                                @if ($type == 'program' && $programs)
                                <select name="id" id="{{ $type }}-id" class="form-control {{ $type }}-id input-skill-modal select-search"
                                        value=""  data-tbl="{{ $type }}" data-col="id">
                                    <option value="0">{{ trans('team::view.---Choose programming language---') }}</option>
                                    @foreach ($programs as $pro)
                                    <option value="{{ $pro->id }}">{{ $pro->name }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" class="form-control {{ $type }}-name input-skill-modal" placeholder="{{ trans('team::view.Name') }}" 
                                    value="" name="name" id="{{ $type }}-name"
                                    data-tbl="{{ $type }}" data-col="name" data-autocomplete="true" />
                                @else
                                    <input type="text" class="form-control {{ $type }}-name input-skill-modal" placeholder="{{ trans('team::view.Name') }}" 
                                    value="" name="name" id="{{ $type }}-name"
                                    data-tbl="{{ $type }}" data-col="name" data-autocomplete="true" />
                                @endif
                            </div>
                        </div>
                        @if ($type != 'program')
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="{{ $type }}-image">{{ trans('team::view.Image') }}</label>
                            <div class="input-box col-md-9 input-box-img-preview">
                                <div class="image-preview">
                                    <img src="{{ URL::asset('common/images/noimage.png') }}"
                                         class="img-responsive college-image-preview skill-modal-image-preview" 
                                         data-tbl="{{ $type }}" data-col="image_preview"/>
                                </div>
                                <div class="img-input">
                                    <input type="file" class="form-control skill-modal-image input-skill-modal" value="" 
                                        name="image" id="{{ $type }}-image" 
                                        data-tbl="{{ $type }}" data-col="image" />
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="form-group form-group-select2">
                            <label class="col-md-3 control-label" for="{{ $type }}-level">{{ trans('team::view.Level') }}</label>
                            <div class="input-box col-md-9">
                                <select name="level" id="{{ $type }}-level" class="form-control {{ $type }}-level input-skill-modal select-search"
                                        value=""  data-tbl="employee_{{ $type }}" data-col="level">
                                    @foreach (View::toOptionNormalLevel() as $option)
                                        <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="{{ $type }}-experience">{{ trans('team::view.Experience') }}</label>
                            <div class="input-box col-md-9">
                                <input type="number" id="{{ $type }}-experience" 
                                    class="form-control {{ $type }}-experience input-skill-modal" placeholder="{{ trans('team::view.Experience year') }}" 
                                    value="" name="experience" id="{{ $type }}-experience"
                                    data-tbl="employee_{{ $type }}" data-col="experience" />
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
<?php } //end function html modal skill ?>

<?php
 getHtmlModalSkill('program', $programs);
 getHtmlModalSkill('database');
 getHtmlModalSkill('os');
?>