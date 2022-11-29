<?php

use Rikkei\Core\View\Form;
use Rikkei\Resource\View\getOptions;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Team\View\Config;
use Illuminate\Support\Facades\URL;
use Rikkei\Recruitment\Model\CddMailSent;
use Rikkei\Team\View\Permission;

$recruiterFilter = Form::getFilterData('except', 'candidates.recruiter');
$statusOptions = getOptions::getInstance()->listFailCandidateStatus();
$interestedOptions = getOptions::listInterestedOptions();
unset($interestedOptions[0]);
$typeOptions = Candidate::getTypeOptions();
$statusCandidateFilter = Form::getFilterData('except', 'candidates.status');
$interestedCandidateFilter = Form::getFilterData('candidates.interested');
$typeCandidateFilter = Form::getFilterData('candidates.type');
$typeMailFilter = (int)Form::getFilterData('except', 'candidates.mail_type');
$statusMailFilter = (int)Form::getFilterData('except', 'candidates.mail_status');
$isTabBirthday = $type === getOptions::TYPE_BIRTHDAY_CANDIDATE_LIST;
$curEmp = Permission::getInstance()->getEmployee();
$contentMail = '';
$subjectMail = '';
if ($isTabBirthday) {
    $contentMail = view('resource::candidate.mail.content_birthday')->render();
    $subjectMail = trans('resource::view.Happy birthday to interested candidate');
}
?>

@extends('layouts.default')
@section('title')
    {{ trans('resource::view.Candidate.List.Interested candidate list') }}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="{!! asset('resource/css/candidate/list.css') !!}" />
@endsection

@section('content')
<div class="row list-css-page">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="box-body">
                            <div class="col-sm-6 input-group pull-left">
                                <a href="{!! route('resource::candidate.interested') !!}">
                                    <button class="btn btn-primary" @if (!$isTabBirthday) disabled @endif>
                                        {{ trans('resource::view.Candidate.List.Follow special candidate') }}
                                    </button>
                                </a>
                                <a href="{!! route('resource::candidate.interested', ['type' => getOptions::TYPE_BIRTHDAY_CANDIDATE_LIST]) !!}">
                                    <button class="btn btn-primary" @if ($isTabBirthday) disabled @endif>
                                        {{ trans('resource::view.Candidate.List.Follow birthday candidate') }}
                                    </button>
                                </a>
                            </div>
                            <div class="col-sm-16 filter-action">
                                <button class="btn btn-success btn-send-mail" disabled>
                                    <span>
                                        {!! $isTabBirthday ? trans('resource::view.Send mail CMSN') : trans('resource::view.Send mail') !!}
                                        <i class="fa fa-spin fa-refresh hidden"></i>
                                    </span>
                                </button>
                                <button class="btn btn-primary btn-reset-filter">
                                    <span>{{ trans('team::view.Reset filter') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
                                </button>
                                <button class="btn btn-primary btn-search-filter">
                                    <span>{{ trans('team::view.Search') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="candidateTbl" class="table table-bordered table-hover dataTable" role="grid" aria-describedby="example2_info">
                                <thead>
                                <tr role="row">
                                    <th class="w-15"><input type="checkbox" class="check-all-items"></th>
                                    <th class="sorting w-20 {{ Config::getDirClass('id') }}" data-order="id" data-dir="{{ Config::getDirOrder('id') }}" >{{ trans('resource::view.Candidate.List.Id') }}</th>
                                    <th class="sorting w-120 min-w-80 {{ Config::getDirClass('interested') }}" data-order="interested" data-dir="{{ Config::getDirOrder('interested') }}" >{{ trans('resource::view.Candidate.List.Interested') }}</th>
                                    <th class="sorting w-120 min-w-80 {{ Config::getDirClass('fullname') }}" data-order="fullname" data-dir="{{ Config::getDirOrder('fullname') }}" >{{ trans('resource::view.Candidate.List.Fullname') }}</th>
                                    @if ($isTabBirthday)
                                    <th class="sorting w-80 min-w-50 {{ Config::getDirClass('birthday') }}" data-order="birthday" data-dir="{{ Config::getDirOrder('birthday') }}" >{{ trans('resource::view.Candidate.List.Date of birth') }}</th>
                                    @endif
                                    <th class="sorting w-120 min-w-80 {{ Config::getDirClass('email') }}" data-order="email" data-dir="{{ Config::getDirOrder('email') }}" >{{ trans('resource::view.Candidate.List.Email') }}</th>
                                    <th class="w-100 min-w-80">{{ trans('resource::view.Candidate.List.Position apply') }}</th>
                                    @if ($isScopeTeam)
                                    <th class="sorting w-120 min-w-80 {{ Config::getDirClass('recruiter') }}" data-order="recruiter" data-dir="{{ Config::getDirOrder('recruiter') }}" >{{ trans('resource::view.Recruiter') }}</th>
                                    @endif
                                    <th class="sorting w-60 min-w-50 {{ Config::getDirClass('experience') }}" data-order="experience" data-dir="{{ Config::getDirOrder('experience') }}" >{{ trans('resource::view.Candidate.List.Experience') }}</th>
                                    <th class="w-70 min-w-50">{{ trans('resource::view.Candidate.List.Programming languages') }}</th>
                                    <th class="w-100 min-w-70">{{ trans('resource::view.Candidate.List.Status') }}</th>
                                    @if (!$isTabBirthday)
                                    <th class="sorting w-45 min-w-45 {{ Config::getDirClass('status_update_date') }}" data-order="status_update_date" data-dir="{{ Config::getDirOrder('status_update_date') }}" >{{ trans('resource::view.Candidate.List.Status update date') }}</th>
                                    @endif
                                    <th class="sorting w-35 min-w-35 {{ Config::getDirClass('type') }}" data-order="type" data-dir="{{ Config::getDirOrder('type') }}" >{{ trans('resource::view.Type') }}</th>
                                    @if (!$isTabBirthday)
                                    <th class="sorting w-45 min-w-45 {{ Config::getDirClass('max_sent_date') }}" data-order="max_sent_date" data-dir="{{ Config::getDirOrder('max_sent_date') }}">{!! trans('resource::view.Latest mailing date') !!}</th>
                                    <th class="w-90 min-w-90">{!! trans('resource::view.Mail type') !!}</th>
                                    <th class="w-60 min-w-60">&nbsp;</th>
                                    @else
                                    <th class="sorting w-50 min-w-50 {{ Config::getDirClass('max_sent_date') }}" data-order="max_sent_date" data-dir="{{ Config::getDirOrder('max_sent_date') }}">{!! trans('resource::view.Mail status') !!}</th>
                                    @endif
                                </tr>
                                </thead>
                                <tbody>
                                <tr class="filter-input-grid">
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <select id="interested" name="filter[candidates.interested]" class="form-control select-grid filter-grid">
                                                    <option value="">&nbsp;</option>
                                                    @foreach ($interestedOptions as $key => $interested)
                                                        <option value="{{ $key }}" class="{!! $interested['class'] !!} "
                                                                <?php if ($key == $interestedCandidateFilter): ?> selected <?php endif;?> > {!! $interested['label'] !!}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" class='form-control filter-grid' name="filter[candidates.fullname]" value="{{ Form::getFilterData('candidates.fullname') }}" placeholder="{{ trans('team::view.Search') }}..." />
                                            </div>
                                        </div>
                                    </td>
                                    @if ($isTabBirthday)
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" class='form-control filter-grid' name="filter[candidates.birthday]" value="{{ Form::getFilterData('candidates.birthday') }}" placeholder="{{ trans('team::view.Search') }}..." />
                                            </div>
                                        </div>
                                    </td>
                                    @endif
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" class='form-control filter-grid' name="filter[candidates.email]" value="{{ Form::getFilterData('candidates.email') }}" placeholder="{{ trans('team::view.Search') }}..."  />
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <select name="filter[except][candidates.position]" class="form-control select-grid filter-grid select-search">
                                                    <option value="">&nbsp;</option>
                                                    @foreach($positionOptions as $key => $value)
                                                        <option value="{{ $key }}"<?php
                                                        if ($key == Form::getFilterData('except','candidates.position')): ?> selected<?php endif;
                                                            ?>>{{ $value }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </td>
                                    @if ($isScopeTeam)
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <select name="filter[except][candidates.recruiter]" class="form-control select-grid filter-grid select-search">
                                                    <option value="">&nbsp;</option>
                                                    @foreach($recruiters as $option)
                                                        <option value="{{ $option }}"<?php
                                                        if ($option == $recruiterFilter): ?> selected<?php endif;
                                                            ?>>{{ $option }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </td>
                                    @endif
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" class='form-control filter-grid' name="filter[candidates.experience]" value="{{ Form::getFilterData('candidates.experience') }}" placeholder="{{ trans('team::view.Search') }}..."  />
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <select name="filter[except][candidate_programming.programming_id]" class="form-control select-grid filter-grid select-search">
                                                    <option value="">&nbsp;</option>
                                                    @foreach($programList as $pro)
                                                        <option value="{{ $pro->id }}"<?php
                                                        if ($pro->id == Form::getFilterData('except', 'candidate_programming.programming_id')): ?> selected<?php endif;
                                                            ?>>{{ $pro->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <select name="filter[except][candidates.status]" class="form-control select-grid filter-grid select-search">
                                                    <option value="">&nbsp;</option>
                                                    @foreach($statusOptions as $option)
                                                        <option value="{{ $option['id'] }}"<?php
                                                        if ($option['id'] == $statusCandidateFilter): ?> selected<?php endif;
                                                            ?>>{{ $option['name'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </td>
                                    @if (!$isTabBirthday)
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" class='form-control filter-grid' name="filter[candidates.status_update_date]" value="{{ Form::getFilterData('candidates.status_update_date') }}" placeholder="{{ trans('team::view.Search') }}..." />
                                            </div>
                                        </div>
                                    </td>
                                    @endif
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <select name="filter[candidates.type]" class="form-control select-grid filter-grid select-search">
                                                    <option value="">&nbsp;</option>
                                                    @foreach($typeOptions as $option)
                                                        <option value="{{ $option['id'] }}"<?php
                                                        if ($option['id'] == $typeCandidateFilter): ?> selected<?php endif;
                                                            ?>>{{ $option['name'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </td>
                                    @if (!$isTabBirthday)
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="text" class='form-control filter-grid' name="filter[except][candidates.max_sent_date]" placeholder="{!! trans('team::view.Search') !!}..."
                                                       value="{{ Form::getFilterData('except', 'candidates.max_sent_date') }}"/>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <select name="filter[except][candidates.mail_type]" class="form-control select-grid filter-grid select-search">
                                                    <option value="">&nbsp;</option>
                                                    <option value="{!! CddMailSent::TYPE_MAIL_MARKETING !!}"
                                                            {!! CddMailSent::TYPE_MAIL_MARKETING === $typeMailFilter ? 'selected' : '' !!}>
                                                        {!! trans('resource::view.Mail marketing') !!}</option>
                                                    <option value="{!! CddMailSent::TYPE_MAIL_INTERESTED !!}"
                                                            {!! CddMailSent::TYPE_MAIL_INTERESTED === $typeMailFilter ? 'selected' : '' !!}>
                                                        {!! trans('resource::view.Mail interested') !!}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </td>
                                    <td></td>
                                    @else
                                    <td>
                                        <select name="filter[except][candidates.mail_status]" class="form-control select-grid filter-grid select-search">
                                            <option value="">&nbsp;</option>
                                            <option value="{!! CddMailSent::STATUS_CMSN !!}"
                                                    {!! CddMailSent::STATUS_CMSN === $statusMailFilter ? 'selected' : '' !!}>
                                                {!! trans('resource::view._CMSN') !!}</option>
                                            <option value="{!! CddMailSent::STATUS_NOT_CMSN !!}"
                                                    {!! CddMailSent::STATUS_NOT_CMSN === $statusMailFilter ? 'selected' : '' !!}>
                                                {!! trans('resource::view.not yet CMSN') !!}</option>
                                        </select>
                                    </td>
                                    @endif
                                </tr>
                                @if(count($collectionModel) > 0)
                                    @foreach($collectionModel as $item)
                                        <tr role="row" class="odd">
                                            <td><input type="checkbox" class="check-item" value="{!! $item->id !!}" @if ($isTabBirthday && $item->max_sent_date) disabled @endif></td>
                                            <td rowspan="1" colspan="1" >{{ $item->id }}</td>
                                            <td rowspan="1" colspan="1" class="text-center">
                                                <i class="fa fa-star-o font-25 {!! $interestedOptions[$item->interested]['class'] !!}"></i>
                                            </td>
                                            <td rowspan="1" colspan="1" >
                                                <a href="{!! route('resource::candidate.detail', $item->id) !!}">{{ $item->fullname }}</a>
                                            </td>
                                            @if ($isTabBirthday)
                                            <td rowspan="1" colspan="1">{!! $item->birthday !!}</td>
                                            @endif
                                            <td rowspan="1" colspan="1" class="break-all" >{{ $item->email }}</td>
                                            <td rowspan="1" colspan="1" >
                                                <?php
                                                if (!empty($item->positions)) :
                                                    $strPos = [];
                                                    $positions = explode(',', $item->positions);
                                                    if (is_array($positions) && count($positions)) :
                                                        foreach ($positions as $pos) :
                                                            $strPos[] = getOptions::getInstance()->getRole($pos);
                                                        endforeach;
                                                    endif;
                                                    echo implode(', ', $strPos);
                                                endif;
                                                ?>
                                            </td>
                                            @if ($isScopeTeam)
                                            <td rowspan="1" colspan="1" class="width-160 break-all">{{ $item->recruiter }}</td>
                                            @endif
                                            <td rowspan="1" colspan="1" >{{ $item->experience }}</td>
                                            <td rowspan="1" colspan="1" >{{ $item->programs_name }}</td>
                                            <td rowspan="1" colspan="1" >{{ getOptions::getInstance()->getCandidateStatus($item->status, $item) }}</td>
                                            @if (!$isTabBirthday)
                                            <td rowspan="1" colspan="1">{{ $item->status_update_date }}</td>
                                            <td rowspan="1" colspan="1" >{{ Candidate::getType($item->type) }}</td>
                                            <td rowspan="1" colspan="1" class="sent-date">{!! $item->max_sent_date !!}</td>
                                            <td rowspan="1" colspan="1" class="mail-type">
                                                <?php
                                                    if ($item->mail_type !== null) {
                                                        $aryMailType = array_unique(explode(',', $item->mail_type));
                                                        $txtMailType = '';
                                                        if (in_array(CddMailSent::TYPE_MAIL_MARKETING, $aryMailType)) {
                                                            $txtMailType .= trans('resource::view.Mail marketing') . ', ';
                                                        }
                                                        if (in_array(CddMailSent::TYPE_MAIL_INTERESTED, $aryMailType)) {
                                                            $txtMailType .= trans('resource::view.Mail interested') . ', ';
                                                        }
                                                        echo trim($txtMailType, ', ');
                                                    }
                                                ?>
                                            </td>
                                            <td class="text-align-center white-space-nowrap">
                                                <form action="{{ route('resource::candidate.remove-interested') }}" method="post" class="form-inline">
                                                    {!! csrf_field() !!}
                                                    <input type="hidden" name="id" value="{{$item->id}}">
                                                    <button class="btn-delete delete-confirm" style="padding: 2px 4px; font-size: 12px"
                                                            data-noti="{{ trans('resource::view.Candidate.List.Confirm remove interested') }}">
                                                        {{ trans('resource::view.Candidate.List.Remove interested') }}
                                                    </button>
                                                </form>
                                            </td>
                                            @else
                                            <td rowspan="1" colspan="1" >{{ Candidate::getType($item->type) }}</td>
                                            <td rowspan="1" colspan="1" class="mail-status">{!! $item->max_sent_date ? trans('resource::view._CMSN') : trans('resource::view.not yet CMSN') !!}</td>
                                            @endif
                                        </tr>
                                    @endforeach
                                @else
                                    <tr><td colspan="16" class="text-center"><h2>{!! trans('sales::view.No result not found') !!}</h2></td></tr>
                                @endif
                                </tbody>
                            </table>
                            <div class="box-body">
                                @include('team::include.pager')
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.row -->
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
    <!-- /.col -->
</div>

<div class="modal fade" id="modal-send-mail" tabindex="0" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="form-send-mail">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                @if ($isTabBirthday)
                <h4 class="modal-title">{!! trans('resource::view.Happy birthday to interested candidate') !!}</h4>
                @else
                <h4 class="modal-title">{!! trans('resource::view.Send mail to special candidate') !!}</h4>
                @endif
            </div>
            <div class="modal-body">
                <div class="form-group row">
                    <div class="col-md-6">
                        <label>{!! trans('resource::view.Email') !!} <em class="error">*</em></label>
                        <input type="text" name="email" class="form-control" value="{{ $curEmp->email }}" disabled>
                    </div>
                    <div class="col-md-6">
                        <label>{!! trans('resource::view.App password') !!} <em class="error">*</em></label>
                        <input type="password" name="app_pass" class="form-control" value="{{ $curEmp->app_password }}">
                    </div>
                </div>
                <div class="form-group">
                    <label>{!! trans('resource::view.Subject') !!} <em class="error">*</em></label>
                    <input type="text" name="subject" class="form-control" value="{!! $subjectMail !!}">
                </div>
                <div class="form-group">
                    <label>{!! trans('resource::view.Content') !!} <em class="error">*</em></label>
                    <textarea name="content" id="content">{!! $contentMail !!}</textarea>
                    <div class="hint-note">
                        <p>&#123;&#123; name &#125;&#125;: {!! trans('resource::view.Fullname') !!}</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default pull-left" data-dismiss="modal">{!! trans('core::view.Close') !!}</button>
                <button class="btn btn-primary btn-preview"><i class="fa fa-eye"></i> {!! trans('resource::view.Preview') !!} <i class="fa fa-spin fa-refresh hidden"></i></button>
                <button class="btn btn-info btn-send"><i class="fa fa-send"></i> {!! trans('resource::view.Send') !!} <i class="fa fa-spin fa-refresh hidden"></i></button>
            </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-preview-email">
    <div class="modal-dialog modal-full-width">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
                <h4 class="modal-title">{!! trans('resource::view.Preview')  !!}</h4>
            </div>
            <div class="modal-body">
                <p><strong>{!! trans('resource::view.Subject') !!}: </strong><span class="preview-send-email-subject"></span></p>
                <div class="preview-send-email"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-close" data-dismiss="modal">{!! trans('core::view.Close') !!}</button>
            </div>
        </div>
    </div>
</div>
@endsection

<!-- Script -->
@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="{!! URL::asset('lib/ckeditor/ckeditor.js') !!}"></script>
<script>
    const typeMailFollow = '{!! CddMailSent::TYPE_MAIL_FOLLOW !!}';
    const typeMailBirthday = '{!! CddMailSent::TYPE_MAIL_BIRTHDAY !!}';
    var _token = '{!! csrf_token() !!}';
    var urlPreviewMail = '{!! route('resource::candidate.interested.preview-mail') !!}';
    var urlSendMail = '{!! route('resource::candidate.interested.send-mail') !!}';
    var isTabBirthday = '{!! $isTabBirthday !!}' || false;
    var txtSentMailCMSN = '{!! trans('resource::view._CMSN') !!}';
    var txtMailTypeMarketing = '{!! trans('resource::view.Mail marketing') !!}';
    var txtMailTypeInterested = '{!! trans('resource::view.Mail interested') !!}';
    var validateMessages = {
        app_pass: '{!! trans('core::view.This field is required') !!}',
        subject: '{!! trans('core::view.This field is required') !!}',
        content: '{!! trans('core::view.This field is required') !!}',
    };
</script>
<script src="{!! CoreUrl::asset('resource/js/candidate/interested.js') !!}"></script>
@endsection
