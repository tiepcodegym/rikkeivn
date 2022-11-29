@extends('layouts.default')

<?php
    use Rikkei\SlideShow\View\View as ViewSlideShow;
    use Rikkei\SlideShow\Model\File as FileModel;
    use Rikkei\SlideShow\Model\Slide;
    use Rikkei\SlideShow\View\RunBgSlide;
    use Rikkei\Core\View\CoreUrl;
?>
@section('title')
{{trans('slide_show::view.Slide setting')}}
@endsection
@section('css')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link href="{{ asset('lib/jQueryFiler/css/jquery.filer.css') }}" type="text/css" rel="stylesheet"/>
<link href="{{ asset('lib/jQueryFiler/css/themes/jquery.filer-dragdropbox-theme.css') }}" type="text/css" rel="stylesheet"/>
<link href="{{ CoreUrl::asset('slide_show/css/edit.css') }}" rel="stylesheet" type="text/css" >
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="box-body">
                <div class="row margin-bottom-30">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="date" class="col-sm-3 padding-right-0 margin-top-7">{{trans('slide_show::view.Choose day')}}: </label>
                            <span class="col-sm-8">
                                <div class="input-group ">
                                    <input type="text" class="form-control input-field date input-date border-radius-4-left" value="{{$dateNow}}" id="date" name="date" data-date-format="yyyy-mm-dd" data-old-value="{{$dateNow}}" data-provide="datepicker" data-date-today-highlight="true" placeholder="{{trans('slide_show::view.Choose day')}}">
                                    <span class="input-group-btn">
                                        <button class="btn btn-default calendar-button" type="button"><i class="fa fa-calendar"></i></button>
                                    </span>
                                </div>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="content-setting">
                    <div class="row">
                        <div class="slide-list">

                        </div>
                        <div class="loader display-none">
                            <i class="fa fa-spinner fa-5x fa-spin" aria-hidden="true"></i>
                        </div>
                        <div class="detail-slide">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- form create slide -->
    @include('slide_show::components.create')

    <div class="modal-slide">
        <div class="modal">
            <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title modal-title-success">{{trans('slide_show::view.Success')}}</h4>
                    <h4 class="modal-title modal-title-error display-none">{{trans('slide_show::view.Error')}}</h4>
                  </div>
                  <div class="modal-body">
                    <p class="text-message" data-delete-success="{{trans('slide_show::message.Delete slide success')}}" data-delete-error="{{trans('slide_show::message.Delete slide error')}}"></p>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-outline" data-dismiss="modal">{{trans('slide_show::view.Close')}}</button>
                  </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!-- /.modal -->
    </div>
    @include('slide_show::templates.modal-delete')
</div>
@endsection
@section('script')
<script>
    var imageDefault = '{{ URL::asset('common/images/noimage.png') }}';
    var urlGetSlideList = '{{route('slide_show::slide-list')}}';
    var getDetailSlide = '{{route('slide_show::slide-detail')}}';
    var urlCreateSlide = '{{route('slide_show::slide-create')}}';
    var urlGetTemplateImage = '{{route('slide_show::get-template-image')}}';
    var urlDeleteSlide = '{{route('slide_show::delete-slide')}}';
    var urlGetTemplateInterval = '{{route('slide_show::get-template-interval')}}';
    var urlChangePassword = '{{route('slide_show::change-paswword')}}';
    var urSlidelSetting = '{{route('slide_show::list-slider')}}';
    var labelCompany = '{{ trans('slide_show::view.Company name') }}';
    var requiredCompany = '{{ trans('slide_show::message.Company name field is required') }}';
    var labelTitle = '{{ trans('slide_show::view.Title') }}';
    var requiredTitle = '{{ trans('slide_show::message.Title field is required') }}';

    var optionPassGlobal = {
        imageTypeAllowRegister: ["jpeg","jpg","png","bmp","gif","bin"],
        sizeImageMaxRegister: 3,
        messageErrorSizeImage: jQuery.parseHTML('{{ trans('slide_show::message.Capacity must smaller 5 MB')}}')[0].nodeValue,
        messageErrorTypeImage: jQuery.parseHTML('{{ trans('slide_show::message.File type dont allow upload') }}')[0].nodeValue,
        maxWidthRegister: 110,
        maxHeightRegister: 110,
        urlCheckProcess: '{{ URL::route('slide_show::process.check') }}'
    };
    var messageConfirmDeleteImage = "{!! trans('slide_show::message.Are you sure you want to delete this file ?') !!}";
    var messageWarningFileTypeUpload = "{!! trans('slide_show::message.Only Images are allowed to be uploaded') !!}";
    var PATH_DEFAULT = "{{Config::get('general.upload_folder') . '/' .  FileModel::PATH_DEFAULT}}";
    var TYPE_IMAGE = '{{Slide::TYPE_IMAGE}}';
    var OPTION_WELCOME = '{{Slide::OPTION_WELCOME}}';
    var OPTION_NOMAL = '{{Slide::OPTION_NOMAL}}';
    var OPTION_QUOTATIONS = '{{Slide::OPTION_QUOTATIONS}}';
    var OPTION_BIRTHDAY = '{{Slide::OPTION_BIRTHDAY}}';
    var widthValidate = '{{$sizeImageValidate['width']}}';
    var heightValidate = '{{$sizeImageValidate['height']}}';
    var urlGetTemplateLogo = '{{route('slide_show::get-template-logo')}}';
</script>
<script src="{{ asset('lib/jQueryFiler/js/jquery.filer.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="{{ URL::asset('lib/ckeditor/ckeditor.js') }}"></script>
<script src="{{ URL::asset('lib/ckfinder/ckfinder.js') }}"></script>
<script src="{{ CoreUrl::asset('slide_show/js/edit.js') }}"></script>
<script>
RKfuncion.addItems.init();
</script>
@endsection
