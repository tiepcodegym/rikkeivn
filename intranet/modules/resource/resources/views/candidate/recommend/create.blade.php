@extends('layouts.default')
<?php

use Rikkei\Resource\View\getOptions;
use Rikkei\Sales\View\View;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Team\View\TeamList;
use Rikkei\Core\View\CoreUrl;
use Illuminate\Support\Facades\Session;
use Rikkei\Team\Model\Team;

$teamsOptionAll = TeamList::toOption(null, true, false);
$urlGetTeam = route('resource::candidate.getTeamByRequest');
$urlGetPosition = route('resource::candidate.getPositionByTeam');
$teamCode = Team::listRegion();

if (isset($recommendCandidate)) {
    $checkEdit = true;
    $urlSubmit = route('resource::candidate.update.recommend', ['id' => $recommendCandidate->id]);
} else {
    $checkEdit = false;
    $urlSubmit = route('resource::candidate.create.recommend');
}
if (isset($reapply)){
    $urlSubmit = route('resource::candidate.reapply.recommend');
}
?>
@section('title')
    {{ trans('resource::view.Recommend candidate') }}
@endsection

@section('css')
    <style>
        .placeholder {
            position: relative;
        }

        .placeholder::after {
            position: absolute;
            top: 30%;
            right: 17%;
            content: attr(data-placeholder);
        }
    </style>
    <link href="{{ CoreUrl::asset('sales/css/sales.css') }}" rel="stylesheet" type="text/css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css"
          rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css"/>
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="{{ CoreUrl::asset('project/css/edit.css') }}"/>
    <link rel="stylesheet" href="{{ CoreUrl::asset('resource/css/resource.css') }}"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/minimal/_all.css"/>
@endsection

@section('content')
    <div class="css-create-page request-create-page candidate-create-page">
        <div class="css-create-body">
            <form id="form-recommend-candidate" class="form-horizontal" method="post"
                  action="{{ $urlSubmit }}"
                  enctype="multipart/form-data">
                @if ($checkEdit)
                    <input type="hidden" name="candidate_id" value="{{ $recommendCandidate->id }}"/>
                @endif
                {!! csrf_field() !!}
                <div class="row">
                    <!-- Basic information -->
                    <div class="col-md-4">
                        <div class="box box-primary padding-bottom-30">
                            <div class="box-header with-border">
                                <h4 class="box-title">{{ trans('resource::view.Basic information') }}</h4>
                            </div>
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group margin-top-10">
                                            <label for="fullname"
                                                   class="col-md-3 control-label">{{trans('resource::view.Candidate.Create.Fullname')}}
                                                <em class="required" aria-required="true">*</em></label>
                                            <div class="col-md-9">
                                            <span>
                                                <input type="text" id="fullname" name="fullname" class="form-control"
                                                       tabindex="1" maxlength="100"
                                                       value="{{$checkEdit ? $recommendCandidate->fullname : old('fullname', '')}}"/>
                                            </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group margin-top-10">
                                            <label for="email"
                                                   class="col-md-3 control-label">{{trans('resource::view.Candidate.Create.Email')}}
                                                <em class="required" aria-required="true">*</em></label>
                                            <div class="col-md-9">
                                            <span>
                                                <input type='text' class="form-control" id="email" name="email"
                                                       tabindex=2
                                                       value="{{$checkEdit ? $recommendCandidate->email : old('email', '')}}"/>
                                            </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group margin-top-10">
                                            <label for="mobile"
                                                   class="col-md-3 control-label">{{trans('resource::view.Candidate.Create.Mobile')}}
                                                <em class="required" aria-required="true">*</em></label>
                                            <div class="col-md-9">
                                            <span>
                                                <input type='text' class="form-control" id="mobile" name="mobile"
                                                       tabindex=3 maxlength="12"
                                                       value="{{$checkEdit ? $recommendCandidate->mobile : old('mobile', '')}}"/>
                                            </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group margin-top-10">
                                            <label for="birthday"
                                                   class="col-md-3 control-label">{{trans('resource::view.Candidate.Create.Birthday')}}</label>
                                            <div class="col-md-9">
                                            <span>
                                                <input type='text' class="form-control date" id="birthday"
                                                       name="birthday" data-provide="datepicker"
                                                       placeholder="YYYY-MM-DD" tabindex=4 autocomplete="off"
                                                       value="{{ $checkEdit && strtotime($recommendCandidate->birthday) >= 0 ? $recommendCandidate->birthday: null }}"/>
                                            </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group margin-top-10">
                                            <label for="gender"
                                                   class="col-md-3 control-label">{{ trans('resource::view.Gender') }}
                                            </label>
                                            <div class="col-md-9">
                                                <select id="gender" name="gender" class="form-control select-search"
                                                        tabindex="5">
                                                    <option value="{{ Candidate::GENDER_MALE }}" {{ ($checkEdit && $recommendCandidate->gender == Candidate::GENDER_MALE) ? 'selected' : '' }}>{{ trans('resource::view.Male') }}</option>
                                                    <option value="{{ Candidate::GENDER_FEMALE }}" {{ ($checkEdit && $recommendCandidate->gender == Candidate::GENDER_FEMALE) ? 'selected' : '' }}>{{ trans('resource::view.Female') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group margin-top-10">
                                            <label for="skype"
                                                   class="col-md-3 control-label">{{trans('resource::view.Skype')}}</label>
                                            <div class="col-md-9">
                                            <span>
                                                <input type='text' class="form-control" id="skype" name="skype"
                                                       tabindex=6 maxlength="50"
                                                       value="{{$checkEdit ? $recommendCandidate->skype : ''}}"/>
                                            </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group margin-top-10">
                                            <label for="other_contact"
                                                   class="col-md-3 control-label">{{trans('resource::view.Other contact')}}</label>
                                            <div class="col-md-9">
                                            <span>
                                                <textarea rows="4" class="form-control" id="other_contact"
                                                          name="other_contact" tabindex=7
                                                >{{$checkEdit ? $recommendCandidate->other_contact : ''}}</textarea>
                                            </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- ./Basic information -->
                    <!-- Experience information -->
                    <div class="col-md-8">
                        <div class="box box-primary padding-bottom-30">
                            <div class="box-header with-border">
                                <h4 class="box-title">{{ trans('resource::view.Experience information') }}</h4>
                            </div>
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group margin-top-10">
                                            <label for="languages"
                                                   class="col-md-3 control-label">{{trans('resource::view.Candidate.Create.Languages')}}</label>
                                            <div class="col-md-9">
                                            <span>
                                                <input type="text" id="languages" name="languages"
                                                       class="form-control bg-white cursor-pointer" readonly=""
                                                       value="{{ $checkEdit ? $langSelected->lang_selected : '' }}"/>
                                            </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group margin-top-10">
                                            <label id="candidate-program-lang" for="programs"
                                                   class="col-md-3 control-label">{{trans('resource::view.Candidate.Create.Programming languages')}} </label>
                                            <div class="col-md-9">
                                                <div class="filter-multi-select multi-select-style">
                                                    <select name="programs[]" class="programmingLangs filter-grid multi-select-bst select-multi" multiple>
                                                        @foreach($programs as $option)
                                                            <option value="{{ $option->id }}" {{ $checkEdit && in_array($option->id, $allProgrammingLangs) ? 'selected' : '' }}>{{ $option->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group margin-top-10">
                                            <label for="experience"
                                                   class="col-md-3 control-label">{{ trans('resource::view.Candidate.Create.Experience') }}
                                            </label>
                                            <div class="col-md-9">
                                                <span>
                                                    <input id="experience" name="experience" type="number"
                                                           class="form-control num" min="0" tabindex='10'
                                                           value="{{ $checkEdit ? $recommendCandidate->experience: '' }}"/>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group margin-top-10">
                                            <label for="university"
                                                   class="col-md-3 control-label">{{trans('resource::view.Candidate.Create.University')}}
                                            </label>
                                            <div class="col-md-9">
                                                <span>
                                                    <textarea maxlength="500" rows="3" class="form-control col-md-9"
                                                              id="university" name="university" tabindex=11
                                                    >{{ $checkEdit ? $recommendCandidate->university: '' }}</textarea>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group margin-top-10">
                                            <label for="certificate"
                                                   class="col-md-3 control-label">{{trans('resource::view.Candidate.Create.Certificate')}}</label>
                                            <div class="col-md-9">
                                                    <textarea maxlength="500" rows="3" class="form-control col-md-9"
                                                              id="certificate" name="certificate"
                                                              tabindex=12
                                                    >{{ $checkEdit ? $recommendCandidate->certificate: '' }}</textarea>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group margin-top-10">
                                            <label for="old_company"
                                                   class="col-md-3 control-label">{{trans('resource::view.Old company')}}</label>
                                            <div class="col-md-9">
                                                <span>
                                                    <textarea maxlength="500" rows="3" class="form-control col-md-9"
                                                              id="old_company" name="old_company" tabindex=13
                                                    >{{ $checkEdit ? $recommendCandidate->old_company: '' }}</textarea>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="box box-primary padding-bottom-30">
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-12 item-region">
                                        <div class="form-group margin-top-10">
                                            <label for="name"
                                                   class="col-md-3 control-label">{{ trans('resource::view.region') }}
                                                <em class="required">*</em>
                                            </label>
                                            <div class="col-md-9">
                                                <span>
                                                    <select id="region" name="region"
                                                            class="form-control width-93">
                                                        <option value="">{{ trans('resource::view.select region') }}</option>
                                                        @foreach ($teamCode as $key=>$region)
                                                            <option value="{{ $key }}"
                                                                    @if ($checkEdit && $branch == mb_strtolower($region)) selected @endif>{{ $region }}</option>
                                                        @endforeach
                                                    </select>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 item-recruit" style="display: {{ $checkEdit ? 'block' : 'none' }}">
                                        <div class="form-group margin-top-10">
                                            <label for="name"
                                                   class="col-md-3 control-label">{{ trans('resource::view.Candidate.Recruiter') }}
                                                <em class="required">*</em>
                                            </label>
                                            <div class="col-md-9">
                                                <span>
                                                    <select id="recruiter" name="recruiter"
                                                            class="form-control width-93">
                                                        <option value="">{{ trans('resource::view.Candidate.Create.Select recruiter') }}</option>
                                                        @if(isset($hrAccounts))
                                                            @foreach ($hrAccounts as $nickname => $email)
                                                                <option value="{{$email}}"
                                                                        @if ($checkEdit && $email == $recommendCandidate->recruiter) selected @endif>{{$email}}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group margin-top-10">
                                            <label class="col-md-3 control-label">{{trans('resource::view.Candidate.Create.Upload CV')}}
                                                <em class="required">*</em></label>
                                            <div class="col-md-9">
                                            <span>
                                                <input type="file" class="width-93 form-control" name="cv" id="cv">
                                                   @if ($checkEdit && $recommendCandidate->cv)
                                                    <?php
                                                    $filename = substr($recommendCandidate->cv, strrpos($recommendCandidate->cv, '/') + 1);
                                                    $filePath = $pathFolder . '/' . $filename;
                                                    $fileDiskPath = storage_path("app/" . Config::get('general.upload_storage_public_folder') . "/" . Candidate::UPLOAD_CV_FOLDER . $filename);
                                                    ?>
                                                    <label class="filename">
                                                        @if (file_exists($fileDiskPath))
                                                            <a href="#" data-toggle="modal"
                                                               onclick='viewFile({{ Candidate::TYPE_CV }}, "https://docs.google.com/gview?url={{ $filePath }}&embedded=true");'>{{ trans('resource::view.View CV') }}</a>
                                                        @else
                                                            <span class="error">{{ trans('resource::message.File does not exist') . ', ' . trans('resource::message.Please update new file') }}</span>
                                                        @endif
                                                    </label>
                                                    @include('resource::candidate.include.modal.view_file')
                                                @endif
                                            </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="box box-primary padding-bottom-30">
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group margin-top-10">
                                            <label class="col-md-3 control-label">{{trans('resource::view.Request.asset.detail.Comment')}}
                                                <em class="required" aria-required="true">*</em></label>
                                            <div class="col-md-9">
                                                <span>
                                                    <textarea rows="4" class="form-control col-md-9" id="comment"
                                                              name="comment" tabindex=7 maxlength="500"
                                                    >{{ $checkEdit && $recommendCandidate->comment ? $recommendCandidate->comment : '' }}</textarea>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 align-center margin-top-40">
                        <button type="submit"
                                class="btn btn-primary save_values">{{trans('resource::view.Request.Create.Submit')}}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@include('resource::candidate.include.modal.choose_language')
@endsection
@section('script')
    <script>
        var chooseEmployeeText = '<?php echo trans("resource::view.Choose employee") ?>';
        //define texts
        var requiredText = '{{ trans('resource::message.Required field') }}';
        var invalidYear = '{{ trans('resource::message.Year is invalid') }}';
        var valueYear = '{{ trans('resource::message.Value not exceeding 100') }}';
        var languageRepeats = '{{ trans('resource::view.The selected language repeats!') }}';
        var invalidEmail = '{{ trans('resource::message.Email is invalid') }}';
        var token = '{{ Session::token() }}';
        var phoneValidText = '{{ trans('resource::message.Phone is invalid') }}';
        var checkEdit = {{ $checkEdit ? 0 : 1 }};
        var notAllowTypeText = '{{ trans("resource::message.Not allow file type.") }}';
        var notAllowSizeText = '{{ trans("resource::message.Not allow file size.") }}';
        var urlCheckMail = '{{ route("resource::candidate.checkCandidateMail" )}}';
        var urlCheckMailRecommend = '{{ route("resource::candidate.checkMailRecommend") }}';
        var uniqueMail = '{{ trans("resource::view.Email is exist") }}';
        var id = 0;
        var routeGetTeam = '{{ $urlGetTeam }}';
        var routeGetPosition = '{{ $urlGetPosition }}';
        var urlGetLangLevel = '{{ route("resource::candidate.getLevelByLang") }}';
        var chooseLevelText = '{{ trans("resource::view.Choose level") }}';
        var noLanguageSelectedMessage = '{{ trans("resource::message.No language selected") }}';
        // get position develop
        var devPosition = {{ getOptions::ROLE_DEV }};
        var urlSearchByRegion = '{{ route("resource::candidate.SearchByRegion") }}';

        //Store languages with level
        var langArray = <?php echo json_encode($langArray); ?>;
        var typeCv = {{ Candidate::TYPE_CV }};
        <?php if ($checkEdit) { ?>
            id = $('input[name=candidate_id]').val();
        <?php } ?>
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
    <script src="{{ CoreUrl::asset('common/js/methods.validate.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="{{ CoreUrl::asset('resource/js/candidate/select2.common.js') }}"></script>
    <script src="{{ CoreUrl::asset('resource/js/candidate/create.js') }}"></script>
    <script src="{{ CoreUrl::asset('resource/js/candidate/common.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/icheck.min.js"></script>

<script>
    $('#recruiter').select2();
    jQuery(document).ready(function ($) {
        selectSearchReload();
        RKfuncion.bootstapMultiSelect.init({
            nonSelectedText: '{{ trans('project::view.Choose items') }}',
            allSelectedText: '{{ trans('project::view.All') }}',
            nSelectedText: '{{ trans('project::view.items selected') }}',
        });
    });

    $('#region').click(function () {
        if ($('#region').val() !== '') {
            $.ajax({
                type: 'POST',
                url: urlSearchByRegion,
                data: {
                    region: $('#region').val()
                },
                success: function (response) {
                    var listHrByRegion = '<option value="">{{ trans("resource::view.Candidate.Create.Select recruiter") }}</option>';
                    response.hrAccounts.map(function (hrAccount) {
                        listHrByRegion += '<option value="' + hrAccount + '">' + hrAccount + '</option>';
                    });
                    $('#recruiter').html(listHrByRegion);
                },
                error: function () {
                    alert('Something went wrong!');
                }
            });
            $('.item-recruit').show();
        } else {
            $('.item-recruit').hide();
        }
    });

    $('.programmingLangs').select2();
</script>
@endsection
