<?php 
header("Cache-Control: no-store, must-revalidate, max-age=0");
header("Pragma: no-cache");
use Rikkei\Core\View\CoreUrl;
?>
@extends('layouts.default')

@section('title')
    {{$titleHeadPage}}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="{{ URL::asset('asset_news/css/news.css') }}" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css"/>
<style type="text/css">
	.time-remove {
		color: #72AFD2;
		position: absolute;
		top: -6px; right: 10px;
		font-size: 15px;
	}
</style>
@endsection

@section('content')
<?php
use Rikkei\Team\View\Config as TeamConfig;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Music\View\ViewMusic;

?>
<div class="row">
    <div class="col-sm-12">
    	@if(Session::has('error'))
			<div class="alert alert-warning">
				<ul>
					<li>
						{{ Session::get('error') }}
					</li>
				</ul>
			</div>
		@endif
		@if(Session::has('saveSuccess'))
			<div class="alert alert-success">
				<ul>
					<li>
						{{ Session::get('saveSuccess') }}
					</li>
				</ul>
			</div>
		@endif
		@if(Session::has('delSuccess'))
			<div class="alert alert-success">
				<ul>
					<li>
						{{ Session::get('delSuccess') }}
					</li>
				</ul>
			</div>
		@endif
        <div class="box box-info">
	        <div class="box-body">
		        <form method="POST" action="{{URL::route('music::manage.offices.save')}}" id="form-edit-office">
		        	{{ csrf_field() }}
		        	@if ($office->id)
                        <input type="hidden" name="id" value="{{ $office->id }}" />
                    @endif
		            <div class="row">
		            	<div class="col-sm-6">
		            		<div class="form-group col-sm-12">
			            		<p for="name" class="col-sm-3" style="text-align: right;">{{trans('music::view.Office name edit')}}<em style="color:red; ">*</em> </p>
			            		<div class="col-sm-8">
			            			<input class="form-control" type="text" name="music_offices[name]" id="name" checkName="{{URL::route('music::manage.offices.checkName')}}" @if($office->id) edit="{{$office->id}}" @endif @if($office->name) value="{{$office->name}}" @endif>
			            		</div>
		            		</div>
		            		<div class="form-group col-sm-12">
			            		<p class="col-sm-3" style="text-align: right;">{{trans('music::view.Office order')}}</p>
			            		<div class="col-sm-8">
			            			<input class="form-control" type="text" name="music_offices[sort_order]" id="sort_order" @if($office->sort_order) value="{{$office->sort_order}}" @endif>
			            		</div>
		            		</div>
		            	</div>
		            	<div class="col-sm-6">
		            		<div class="form-group col-sm-12">
			            		<p class="col-sm-3" style="text-align: right;">{{trans('music::view.Office status')}}<em style="color:red; ">*</em> </p>
			            		<div class="col-sm-8">
			            			<select class="form-control" name="music_offices[status]" id="status">
			            				@if((int) $office->status === 0)
			            					<option value="0" selected="true">Disable</option>
			            					<option value="1" >Enable</option>
			            				@else
			            					<option value="1"  selected="true">Enable</option>
			            					<option value="0" >Disable</option>
			            				@endif
			            			</select>
			            		</div>
		            		</div>
		            		<div class="form-group col-sm-12">
			            		<p class="col-sm-3" style="text-align: right;">{{trans('music::view.Office employee noti')}}</p>
			            		<div class="col-sm-8">
			            			<select class="form-control select2" type="text" name="music_offices[employee_noti]" id="employee_noti">
			            				@if($office->employee_noti)
			            					<option value="{{$office->employee_noti}}">{{CoreView::getNickName($office->email)}}</option>
			            				@endif
			            			</select>
			            			<h6>{{trans('music::view.Office noti note')}}</h6>
			            		</div>
		            		</div>
		            	</div>
		            </div>
	            	<div class="row form-group">
	        			<div class="col-lg-6">
	        				<div class="col-sm-12">
			            		<p class="col-sm-3" style="text-align: right;">{{trans('music::view.Time play music')}}<br>
	            				<label id="time[]-error" class="error" for="time[]"></label></p>
			            		<div class="row col-sm-9" id="time">
			            			<?php $times = $office->getAllTime()  ?>
				            		@if(count($times)>0)
				            			@foreach($times as $time)
					            			<div class="col-sm-4 col-md-3 col-lg-4 mini-time">
							            		<div class="input-group">
							            			<input class="form-control time" type="text" name="time[]" value="{{ViewMusic::shortTime($time['time'])}}" readonly/>
							            			<span class="input-group-addon">
								                        <span>
								                        	<i class="fa fa-clock-o" aria-hidden="true"></i>
								                        </span>
								                    </span>
							            		</div>
							            		<span class="delTime">
						                    		<i class="fa fa-times-circle time-remove hidden" aria-hidden="true"></i>
						                    	</span>
						            		</div>
					            		@endforeach
				            		@else
					            		<div class="col-sm-4 col-md-3 col-lg-4 mini-time">
						            		<div class="input-group">
						            			<input class="form-control time" type="text" name="time[]" readonly />
						            			<span class="input-group-addon">
							                        <span>
							                        	<i disable="false" class="fa fa-clock-o" aria-hidden="true"></i>
							                        </span>
							                    </span>
						            		</div>
						            		<span class="delTime">
					                    		<i class="fa fa-times-circle time-remove hidden" aria-hidden="true"></i>
					                    	</span>
					            		</div>
					            	@endif
			            		</div>
		            		</div>
	            		</div>
	            	</div>

	            	<div class="row form-group">
	            		<div class="col-lg-6">
		            		<div class="col-sm-12">
		            			<p class="col-sm-3"></p>
		            			<div class="col-sm-2">
			            			<button type="button" class="btn btn-primary" onclick="addTime();">
			            				<i class="fa fa-plus"></i>
			            			</button>
		            			</div>
		            		</div>
	            		</div>
	            	</div>

		            <div class="row text-center">
		            @if(Session::has('saveSuccess'))
		            	<button type="submit" class="btn btn-success">{{trans('music::view.Office save')}}</button>
		            @else
		            	<button type="submit" class="btn btn-primary">{{trans('music::view.Office save')}}</button>
		            @endif
		        </form>
	            @if($office->id)
	            	<form style="display: inline-block;" action="{{URL::route('music::manage.offices.del',$office->id)}}" method="POST">
	            		<input type="hidden" name="del-hidden" value="hidden">
	            		{{ csrf_field() }}
	            		<button type="submit" class="btn-delete delete-confirm btn btn-danger">{{trans('music::view.Office delete')}}
	            		</button>
	            	</form>
	            @endif
	        </div>
	        </div>
        </div>
    </div>
    <input type="hidden" id="search-member" value="{{URL::route('team::employee.list.search.ajax')}}">
</div>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script type="text/javascript" src= "{{URL::asset('lib/js/moment.min.js')}}"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.min.js"></script>
<script src="{{ CoreUrl::asset('asset_music/js/music.js') }}"></script>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        selectSearchReload();
    });
</script>
@endsection

