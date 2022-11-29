<?php
use Rikkei\News\Model\Poster;
use Rikkei\Core\View\CoreUrl;

?>
@if(Session::has('flash_success'))
    <div class="alert alert-success alert-dismissible fade in alert-hiden" role="alert">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">×</a>
        {{ Session::get('flash_success') }}
    </div>
@endif
@if(Session::has('flash_error'))
    <div class="alert alert-danger alert-dismissible fade in alert-hiden" role="alert">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">×</a>
        {{ Session::get('flash_error') }}
    </div>
@endif

<div class="row">
    <div class="col-md-8">
        <div class="form-group">
            <label for="title" class="control-label required">{{ trans('news::view.Title') }} <em>*</em></label>
            <div class="">
                <input  name="title" class="form-control input-field" type="text" id="title"
                       value="{{ old('title', $poster->title)  }}" placeholder="{{ trans('news::view.Title') }}" onkeyup="ChangeToSlug();"/>
                @if($errors->has('title'))
                    <p class="error">{{ trans('core::message.This field is required') }}</p>
                @endif
            </div>
        </div>
        <div class="form-group">
            <label for="title" class="control-label required">{{ trans('news::view.Link Poster') }} <em>*</em></label>
            <div class="">
                <input  name="link" class="form-control input-field" type="text" id="link"
                        value="{{ old('link', $poster->link)  }}" placeholder="{{ trans('news::view.Link Poster') }}"/>
                @if($errors->has('title'))
                    <p class="error">{{ trans('core::message.This field is required') }}</p>
                @endif
            </div>
        </div>
        <div class="form-group">
            <label for="slug" class="control-label required">{{ trans('news::view.Slug') }}<em>*</em></label>
            <div class="">
                <input  name="slug" class="form-control input-field" type="text" id="slug"
                       value="{{ old('slug', $poster->slug) }}" placeholder="{{ trans('news::view.Slug') }}" />
                @if($errors->has('slug'))
                    <p class="error">{{ trans('core::message.This field is required') }}</p>
                @endif
            </div>
        </div>

        <div class="form-group row">
            <div class="team-ot-select-box col-md-6">
                <label for="start_at" class="control-label required">{{trans('manage_time::view.From date')}}<em>*</em></label>
                <div class="input-box">
                    <input  type="text"
                           id="fromDate"
                           class='form-control date-picker filter-grid form-inline'
                           value="{{ old('start_at', $poster->start_at) ? \Carbon\Carbon::parse(old('start_at', $poster->start_at))->format('d/m/Y') : null }}"

                    />
                    <input id="altFromDate" name="start_at" type="hidden" value="{{ old('start_at', $poster->start_at) }}" >
                    @if($errors->has('start_at'))
                        <p class="error">{{ trans('core::message.This field is required') }}</p>
                    @endif
                </div>
            </div>
            <div class="team-ot-select-box col-md-6">
                <label for="end_at" class="control-label required">{{trans('manage_time::view.End date')}}<em>*</em></label>
                <div class="input-box">
                    <input  type="text"
                           id="toDate"
                           class='form-control date-picker filter-grid form-inline'
                           value="{{ old('end_at', $poster->end_at) ? \Carbon\Carbon::parse(old('end_at', $poster->end_at))->format('d/m/Y') : null }}"
                    />
                    <input id="altToDate" name="end_at" type="hidden" value="{{ old('end_at', $poster->end_at) }}" >
                @if($errors->has('end_at'))
                        <p class="error">{{ trans('core::message.This field is required') }}</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="order" class="control-label required">{{ trans('news::view.Order') }}<em>*</em> <em>({{trans('news::view.Order warning')}})</em></label>
            <div class="">
                <input  name="order" class="form-control input-field" type="number"
                       value="{{ old('order', $poster->order) }}" placeholder="{{ trans('news::view.Order') }}" />
                @if($errors->has('order'))
                    <p class="error">{{ trans('core::message.This field is required') }}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group ckfinder-preview-wrapper">
            <label for="image" class="control-label required">
                {{ trans('news::view.Image') }}<em>*</em>
                &nbsp;<button class="btn btn-primary btn-sm btn-ckfinder-browse-file"
                              type="button" data-element="#image">
                    <i class="fa fa-file-image-o btn-submit-main"></i>
                </button>
            </label>
            <div>
                <input  name="image" class="form-control input-field" type="text" id="image"
                       value="{{ old('image', $poster->image) }}" />
                @if($errors->has('image'))
                    <p class="error">{{ trans('core::message.This field is required') }}</p>
                @endif
            </div>
            <div class="news-manage-image max-h-400 margin-top-10 ckfinder-img-preview">
                @if ($poster->getImage())
                    <img src="{{ $poster->getImage() }}" />
                @else
                    <img src="{{URL::asset(old('image', $poster->image))}}" alt="">
                @endif
            </div>
        </div>
        <div class="form-group form-group-select2 padding-left-0">
            <label for="setComment" class="control-label">{{ trans('news::view.is_active') }}</label>
            <div class="input-group">
                <div id="radioBtn" class="btn-group">
                    @if($poster->id)
                        <a class="btn btn-primary btn-sm @if($poster->status == Poster::STATUS_ACTIVE) active @else notActive @endif" data-toggle="setStatus" data-title="2">{{ trans('news::view.YES') }}</a>
                        <a class="btn btn-primary btn-sm @if($poster->status == Poster::STATUS_INACTIVE) active @else notActive @endif" data-toggle="setStatus" data-title="1">{{ trans('news::view.NO') }}</a>
                    @else
                        <a class="btn btn-primary btn-sm active" data-toggle="setStatus" data-title="2">{{ trans('news::view.YES') }}</a>
                        <a class="btn btn-primary btn-sm notActive" data-toggle="setStatus" data-title="1">{{ trans('news::view.NO') }}</a>
                    @endif
                </div>
                <input type="hidden" name="status" id="setStatus" value="{{ $poster->id ? $poster->status : Poster::STATUS_INACTIVE }}">
            </div>
        </div>
        <div class="form-group form-group-select2 padding-left-0">
            <label for="setIsGif" class="control-label">{{ trans('news::view.Is GIF') }}</label>
            <div class="input-group">
                <div id="radioBtnIsGif" class="radioBtn btn-group">
                    @if($poster->id)
                        <a class="btn btn-primary btn-sm @if($poster->is_gif) active @else notActive @endif" data-toggle="setIsGif" data-title="1">{{ trans('news::view.YES') }}</a>
                        <a class="btn btn-primary btn-sm @if(!$poster->is_gif) active @else notActive @endif" data-toggle="setIsGif" data-title="0">{{ trans('news::view.NO') }}</a>
                    @else
                        <a class="btn btn-primary btn-sm active" data-toggle="setIsGif" data-title="1">{{ trans('news::view.YES') }}</a>
                        <a class="btn btn-primary btn-sm notActive" data-toggle="setIsGif" data-title="0">{{ trans('news::view.NO') }}</a>
                    @endif
                </div>
                <input type="hidden" name="is_gif" id="setIsGif" value="{{ $poster->id ? $poster->is_gif : 0 }}">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12 text-center">
            <button type="submit" class="btn btn-success btn-submit-ckeditor btn-save-news" id="btn-submit">
                {{ trans('news::view.Save') }}
                <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i>
            </button>
        </div>
    </div>
</div>

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.19.0/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.13/moment-timezone-with-data.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="{{ URL::asset('lib/js/jquery.validate.min.js') }}"></script>
    <script src="{{ URL::asset('lib/ckeditor/ckeditor.js') }}"></script>
    <script src="{{ URL::asset('lib/ckfinder/ckfinder.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
    <script type="text/javascript">
        var messageValidate = {
            required: '{{ trans('core::message.This field is required') }}'
        };
    </script>
    <script src="{{ CoreUrl::asset('asset_news/js/poster.js') }}"></script>
@endsection