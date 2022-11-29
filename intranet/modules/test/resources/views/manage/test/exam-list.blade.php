<?php
use Rikkei\Core\View\CoreUrl;
use Rikkei\Team\View\TeamList;

$teamsOptionAll = TeamList::toOption(null, true, false);
?>

@extends('layouts.default')

@section('title', trans('test::test.Exam list'))

@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" >
<link rel="stylesheet" href="{{ CoreUrl::asset('resource/css/resource.css') }}" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/minimal/_all.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.1.0/css/flag-icon.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="{{ CoreUrl::asset('tests/css/main.css') }}">
@endsection

@section('content')

<div class="content-container">
	<div class="box box-info">
		<div class="box-header with-border">
		    <h4 class="box-title">{{ trans('test::test.test') }}</h4>
		</div>
		<div class="box-body">
			<div class="row">
				<div class="col-md-6 form-group row-test">
                    <div class="col-md-9" style="padding: unset;">
	                    <label for="test" class="col-md-3 control-label">{{ trans('test::test.test') }}</label>
	                    <div class="col-md-9">
	                        <span>                                  
	                            <select id="test" name="test" class="form-control has-search select-search select2-hidden-accessible" autocomplete="off">
	                            <option value="0">&nbsp;</option>
	                            @foreach($testOption as $option)
	                                <option value="{{ $option->id }}">{{ $option->name }}</option>
	                            @endforeach
	                            </select>
	                        </span>
	                    </div>
                    </div>
                    <div class="col-md-3">
                    	<input type="checkbox" id="multi_times" name="">{{ trans('test::test.Test multiple times') }}
                    	<span class="fa fa-question-circle help" title="{{ trans('test::test.Default test one times') }}"></span>
                    </div>
                </div>
				<div class="col-md-6">
					<label class="col-md-2 lable-config">{{ trans('test::test.Time from') }}</label>
					<div class='col-md-4 date time-from'>
		                <input type='text' id='time_from' class="form-control" placeholder="yyyy-mm-ddd h:i:s" autocomplete="off"/>
		            </div>
		            <label class="col-md-2 lable-config">{{ trans('test::test.Time to') }}</label>
					<div class='col-md-4 date time-to'>
		                <input type='text' id='time_to' class="form-control" placeholder="yyyy-mm-ddd h:i:s" autocomplete="off"/>
		            </div>
				</div>
			</div>
		</div>
	</div>
	<div class="box box-info">
		<div class="box-header with-border">
		    <h4 class="box-title">{{trans('test::test.Select division')}}</h4>
		</div>
		<div class="box-body">
			<div class="row">
				<div class="col-md-6 form-group row-team">
                    <label for="division" class="col-md-4 control-label">{{trans('resource::view.Team')}}</label>
                    <div class="col-md-8">
                        <span>                                  
                            <select id="division" name="teams[]" class="form-controlwidth-100-per bootstrap-multiselect teams multiple_select" multiple="multiple" >
                            @foreach($teamsOptionAll as $option)
                                <option value="{{ $option['value'] }}" @if(isset($allTeams) && in_array($option['value'], $allTeams)) selected @endif>{{ $option['label'] }}</option>
                            @endforeach
                            </select>
                        </span>
                    </div>
                </div>
				<div class="col-md-6">
					<label class="col-md-4 control-label lable-config">{{trans('test::test.List is selected')}}</label>
					<div class="col-md-8 select-division">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="box box-info">
		<div class="box-header with-border">
		    <h4 class="box-title">{{trans('test::test.Select employee')}}</h4>
		</div>
		<div class="box-body">
			<div class="row">
		        <div class="col-md-6 form-group row-team">
		        	<div class="row search-filter">
			        	<div class="col-md-4">
		                    <label for="team_id" class="control-label">{!!trans('team::view.Team')!!} <i class="fa fa-spin fa-refresh hidden"></i></label>
		                    <div class="">
		                        <span>                                  
		                            <select id="team_id" name="teams[]" class="form-controlwidth-100-per bootstrap-multiselect teams multiple_select" multiple="multiple" >
		                                @foreach($teamsOptionAll as $option)
		                                <option value="{{ $option['value'] }}" @if(isset($allTeams) && in_array($option['value'], $allTeams)) selected @endif>{{ $option['label'] }}</option>
		                            @endforeach
		                            </select>
		                        </span>
		                    </div>
		                </div>
		                <div class="col-md-2">
		                	<label>{{ trans('team::view.Name') }}</label>
		                	<input type="text" id="input-name" class="form-control">
		                </div>
		                <div class="col-md-2">
		                	<label>{{ trans('team::view.Email') }}</label>
		                	<input type="text" id="input-email" class="form-control">
		                </div>
		                <div class="col-md-4 btn-filter">
		                		<label class="control-label">&nbsp;</label>
		                		<div class="col-md-12 btn-filter">
			                		<div class="col-md-6" style="padding: unset; float: right;">
			                			<button class="btn btn-primary btn-reset-filter-emp">{{ trans('team::view.Reset filter') }}</button>
			                		</div>
		                			<div class="col-md-6" style="padding: unset; float: right;">
		                				<button class="btn btn-primary btn-search-filter-emp">{{ trans('team::view.Search') }}</button>
		                			</div>
		                		</div>
		                </div>
	                </div>
	                <div class="row search-employee">
						@include('test::manage.includes.select-employee')
					</div>
					<div class="end-seach-employee"></div>
                </div>
				<div class="col-md-6">
					<div class="row col-md-12">
						<label class="col-md-4 control-label">{{trans('test::test.List is selected')}}</label>
					</div>
					<div class="row select-employee">
					
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="box-footer">
		<div class="row">
			<div class="col-md-12 text-center">
				<button type="submit" id="btn-save" class="btn btn-primary">{{trans('core::view.Save')}}</button>
			</div>
		</div>
	</div>
</div>

@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="{{ CoreUrl::asset('common/js/methods.validate.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/icheck.min.js"></script>
<script src="{{ CoreUrl::asset('lib/js/jquery.match.height.addtional.js') }}"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript">
	var checkEmployee = [];
	var checkDivision = [];
    var token = '{{ csrf_token() }}';
	var urlOption = '{{route('test::select_option')}}';
	var urlList = '{{route('test::test.exam_list')}}';
	var urlEmployee = '{{route('test::select_employee')}}';
	var urlSave = '{{route('test::save_data')}}';
	var timeFromValidate = '{{ trans('test::test.Time from validate') }}';
	var timeToValidate = '{{ trans('test::test.Time to validate') }}';

	jQuery(document).ready(function($) {
        selectSearchReload();
        RKfuncion.bootstapMultiSelect.init({
            nonSelectedText: '{{ trans('project::view.Choose items') }}',
            allSelectedText: '{{ trans('project::view.All') }}',
            nSelectedText: '{{ trans('project::view.items selected') }}',
        });
    });
</script>
<script src="{{ CoreUrl::asset('tests/js/scripts.js') }}"></script>
@endsection
