@extends('layouts.default')
@section('title')
@if (isset($video))
{{trans('slide_show::view.Video')}}: {{$video->title}}
@else
{{trans('slide_show::view.Create video default')}}
@endif
@endsection
<?php
use Rikkei\SlideShow\Model\VideoDefault;
use Rikkei\SlideShow\View\View;

?>
@section('css')
<link href="{{ asset('slide_show/css/edit.css') }}" rel="stylesheet" type="text/css" >
@endsection

@section('content')
<div class="row">
    <form class="form-horizontal no-validate" id="fr-upload-video" action="{{route('slide_show::post-video-default')}}" method="post" enctype="multipart/form-data" autocomplete="off" novalidate="novalidate">
        {{ csrf_field() }}
        @if (isset($video))
        <input type="hidden" name="id" value="{{$video->id}}">
        @endif
        <div class="box box-info">
            <div class="box-body">
                <div class="form-group">
                    <label for="title" class="col-sm-2 control-label">{{trans('slide_show::view.Title')}}</label>
                    <div class="col-sm-8">
                        @if (isset($video))
                        <input type="text" class="form-control title" name="title" placeholder="{{trans('slide_show::view.Title')}}" data-message="{{trans('slide_show::message.Title field is required')}}" value="{{$video->title}}">
                        @else
                        <input type="text" class="form-control title" name="title" placeholder="{{trans('slide_show::view.Title')}}" data-message="{{trans('slide_show::message.Title field is required')}}">
                        @endif
                    </div>
                </div>
                <div class="form-group">
                    <label for="url" class="col-sm-2 control-label">{{trans('slide_show::view.Url video youtube')}}</label>
                    <div class="col-sm-8">
                        @if (isset($video))
                        <input type="text" class="form-control url" name="url" placeholder="{{trans('slide_show::view.Url video youtube')}}" data-message="{{trans('slide_show::message.Url video field is required')}}" value="https://www.youtube.com/watch?v={{$video->file_name}}" data-incorrect="{{trans('slide_show::message.Url video youtube incorrect')}}">
                        @else
                        <input type="text" class="form-control url" name="url" placeholder="{{trans('slide_show::view.Url video youtube')}}" data-message="{{trans('slide_show::message.Url video youtube incorrect')}}" data-incorrect="{{trans('slide_show::message.Url video youtube incorrect')}}">
                        @endif
                    </div>
                </div>
                @if (isset($video))
                <?php
                    $urlDefault = View::urlVideoYoutube($video->file_name);
                ?>
                <div class="form-group preview-video">
                    <div class="col-sm-8 col-sm-offset-2" style="margin-top:20px ">
                        <iframe width="100%" height="100%" src="{{$urlDefault}}" frameborder="0" class="youtube-video"  webkitallowfullscreen mozallowfullscreen allowfullscreen style="min-height: 500px"></iframe>
                    </div>
                </div>
                @else
                <div class="form-group preview-video display-none">
                    <div class="col-sm-8 col-sm-offset-2" style="margin-top:20px ">
                        <iframe width="100%" height="100%" src="" frameborder="0" class="youtube-video"  webkitallowfullscreen mozallowfullscreen allowfullscreen style="min-height: 500px"></iframe>
                    </div>
                </div>
                @endif
                <!-- <div class="form-group input-video">
                    <div class="col-sm-1 col-sm-offset-1 padding-left-0 padding-right-0">
                        <div tabindex="500" class="btn btn-primary btn-file">
                            <div class="fa-icon">
                                <i class="fa fa-youtube-play"></i>
                            </div>
                            @if (isset($video))
                            <input id="file_video" type="file" class="file" accept="video/*" name="video"data-message="{{trans('slide_show::message.Video is required')}}" data-id="{{$video->id}}">
                            @else
                            <input id="file_video" type="file" class="file" accept="video/*" name="video"data-message="{{trans('slide_show::message.Video is required')}}">
                            @endif
                            <i class="fa fa-plus fa-lg"></i>
                        </div>
                    </div>
                    @if (isset($video))
                    <div class="col-sm-8 col-sm-offset-1 preview-video">
                        <?php
                            $url = url('/') . '/' . Config::get('general.upload_folder') . '/' .  VideoDefault::PATH_VIDEO_DEFAULT.'/'. $video->file_name;
                        ?>
                        <video controls autoplay loop class="width-100-per" src="{{$url}}">
                        </video>
                    </div>
                    @else
                    <div class="col-sm-8 col-sm-offset-1 preview-video display-none">
                        <video controls autoplay loop class="width-100-per">
                        </video>
                    </div>          
                    @endif
                </div> -->
                <div class="progress active display-none col-sm-8 col-sm-offset-3 padding-right-0 padding-left-0">
                    <div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="" aria-valuemin="0" aria-valuemax="100">
                      <span></span>
                    </div>
                </div>
            </div>
            <div class="box-footer text-center">
            @if (isset($video))
                <button type="submit" class="btn btn-primary btn-upload">{{trans('slide_show::view.Update')}} <i class="fa fa-refresh fa-lg fa-spin display-none" data-id="{{$video->id}}"></i></button>
            @else
                <button type="submit" class="btn btn-primary btn-upload">{{trans('slide_show::view.Create')}} <i class="fa fa-refresh fa-lg fa-spin display-none"></i></button>
            @endif
            </div>
        </div>
    </form>
</div>
@endsection
@section('script')
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script> -->
<script src="{{ asset('lib/js/jquery.form.js') }}"></script>
<script src="{{ asset('slide_show/js/video_default.js') }}"></script>
<script>
    var urlSetting = '{{route('slide_show::setting')}}';
</script>
@endsection