<?php
    use Rikkei\News\Model\Post;

$getAllTypePost = Post::getAllTypePost();
?>
@extends('layouts.default')

@section('title')
{{ $titleHeadPage }}
@endsection

@section('css')
<?php use Rikkei\Core\View\CoreUrl; ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="{{ URL::asset('asset_news/css/news.css') }}" />
<style>
    .schedulepost-hidden {
        display: none;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="box-body">
                <form id="form-post-edit" method="post" action="{{ URL::route('news::manage.post.save') }}"
                    class="form-submit has-valid " enctype="multipart/form-data" autocomplete="off">
                    {!! csrf_field() !!}
                    @if ($postItem->id)
                        <input type="hidden" name="id" value="{{ $postItem->id }}" />
                        <input type="hidden" name="post[public_at]" value="{{ $postItem->public_at }}" />
                        <input type="hidden" name="post[category]" id="input_category_webvn" value="3" />
                    @endif
                        <input type="hidden" value="{{ URL::route('news::post.guest') }}" id="link-render">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group form-group-select2">
                                <label for="is_video" class="control-label required">Type <em>*</em></label>
                                <div class="fg-valid-custom">
                                    <select name="post[is_video]" id="is_video" class="select-search form-control">
                                        @foreach($getAllTypePost as $key =>$val)
                                            <option value="{{$key}}" @if($postItem->is_video == $key) selected @endif>{{$val}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="title" class="control-label required">{{ trans('news::view.Title') }} <em>*</em></label>
                                <div class="">
                                    <input name="post[title]" class="form-control input-field" type="text" id="title"
                                        value="{{ $postItem->title }}" placeholder="{{ trans('news::view.Title') }}" onkeyup="ChangeToSlug()";/>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="title" class="control-label required">{{ trans('news::view.Author') }}</label>
                                <div class="">
                                    <input name="post[author]" class="form-control input-field" type="text" id="author"
                                        value="{{ $postItem->author }}" placeholder="{{ trans('news::view.Author') }}" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="slug" class="control-label">{{ trans('news::view.Slug') }}</label>
                                <div class="">
                                    <input name="post[slug]" class="form-control input-field" type="text" id="slug"
                                        value="{{ $postItem->slug }}" placeholder="{{ trans('news::view.Slug') }}" />
                                </div>
                            </div>
                            <!-- <div class="form-group">
                                <button class="btn btn-primary" type="button" id="btn-render">Render public link</button>
                                <button class="btn btn-primary" type="button" id="btn-remove-render">Bỏ render</button>
                            </div>
                            <div class="form-group">
                                <input class="form-control input-field" type="text" id="render-link" disabled="true"
                                    @if($postItem->render)value="{{ URL::route('news::post.guest',[$postItem->render]) }}"@endif/>
                                    <input name="post[render]" class="form-control input-field" type="hidden" id="render"
                                    value="{{ $postItem->render }}"/>
                            </div> -->
                            <div class="form-group">
                                <label for="short_description" class="control-label">
                                    {{ trans('news::view.Short description') }}
                                    ({{ trans('help::view.Input limit', ['limit' => '205']) }})
                                </label>
                                <div class="">
                                    <textarea name="post[short_desc]" class="form-control" id="short_description"  style="width: 100%; height: 219px">{{ $postItem->short_desc }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            @if ($postItem->public_at)
                            <div class="form-group">
                                <label class="control-label">{{ trans('news::view.Public at') . ': ' . $postItem->public_at }}</label>
                                <p>
                                    <a href="{{ $postItem->getUrl() }}">{{ trans('news::view.View post') }}</a>
                                </p>
                            </div>
                            @endif
                            <div class="form-group form-group-select2">
                                <label for="status" class="control-label required">{{ trans('news::view.Is public') }}</label>
                                <div class="fg-valid-custom">
                                    <select name="post[is_public]" id="is_public" class="select-search">
                                        @foreach ($optionPublic as $key => $value)
                                            <option value="{{ $key }}"{{ $postItem->is_public == $key ? ' selected' : '' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group form-group-select2">
                                <label for="status" class="control-label required">{{ trans('news::view.Status') }} <em>*</em></label>
                                <div class="fg-valid-custom">
                                    <select name="post[status]" id="status" class="select-search">
                                        <option value="">&nbsp;</option>
                                        @foreach ($optionStatus as $key => $value)
                                            <option value="{{ $key }}"{{ $postItem->status == $key ? ' selected' : '' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group form-group-schedulepost {{ $postItem->status == Post::STATUS_ENABLE  ? 'schedulepost-hidden' : '' }} ">
                                <label for="schedulepost">Schedule a post (date and time):</label>
                                <input type="datetime-local" id="schedulepost" name="schedulePost" value="{{ $postItem->publish_at }}">
                            </div>
                            <div class="form-group ckfinder-preview-wrapper ">
                                <label for="image" class="control-label">
                                    {{ trans('news::view.Image') }}
                                    &nbsp;<button class="btn btn-primary btn-sm btn-ckfinder-browse-file"
                                                type="button" data-element="#image">
                                        <i class="fa fa-file-image-o btn-submit-main"></i>
                                    </button>
                                </label>
                                <div>
                                    <input name="post[image]" class="form-control input-field" type="text" id="image"
                                        value="{{ $postItem->image }}" />
                                </div>
                                <div class="news-manage-image max-h-400 margin-top-10 ckfinder-img-preview">
                                    @if ($postItem->getImage())
                                        <img src="{{ $postItem->getImage() }}" />
                                    @endif
                                </div>
                            </div>
                            @if ($allCategory && count($allCategory))
                                <div class="form-group form-group-select2">
                                    <label for="category" class="control-label">{{ trans('news::view.Category') }}</label>
                                    <div>
                                        <select name="category[id][]" id="category" class="select-search-multi" multiple>
                                            @foreach ($allCategory as $value)
                                                <option value="{{ $value['id'] }}"{{ in_array($value['id'], $postCategories) ? ' selected' : '' }}>{{ $value['title'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endif
                            <?php $tags = explode(',', $postItem->tags); ?>
                            <div class="form-group form-group-select2">
                                <label for="category" class="required control-label">{{ trans('news::view.Tags') }}<em>*</em></label>
                                <div>
                                    <select name="post[tags][]" id="tags" class="select-tag-multi" multiple>
                                        @if ($tags && count($tags))
                                            @foreach ($tags as $value)
                                                @if ($value != '')
                                                    {{$value = str_replace('#', '', $value)}}
                                                    <option value="{{ $value }}" selected>{{ $value }}</option>
                                                @endif
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="form-group form-group-select2 col-md-4 padding-left-0">
                                <label for="setComment" class="control-label">{{ trans('news::view.Set Comment') }}</label>
                                <div class="input-group">
                                    <div id="radioBtn" class="btn-group btn-radio">
                                        @if($postItem->id)
                                            <a class="btn btn-primary btn-sm @if($postItem->is_set_comment == Post::BE_COMMENTED) active @else notActive @endif" data-toggle="setComment" data-title="1">{{ trans('news::view.YES') }}</a>
                                            <a class="btn btn-primary btn-sm @if($postItem->is_set_comment == Post::NO_COMMENT) active @else notActive @endif" data-toggle="setComment" data-title="0">{{ trans('news::view.NO') }}</a>
                                        @else
                                            <a class="btn btn-primary btn-sm active" data-toggle="setComment" data-title="1">{{ trans('news::view.YES') }}</a>
                                            <a class="btn btn-primary btn-sm notActive" data-toggle="setComment" data-title="0">{{ trans('news::view.NO') }}</a>
                                        @endif
                                    </div>
                                    <input type="hidden" name="post[is_set_comment]" id="setComment" value="{{ $postItem->id ? $postItem->is_set_comment : Post::BE_COMMENTED }}">
                                </div>
                            </div>
                            <div class="form-group form-group-select2 col-md-4 padding-left-0 js-post-news">
                                <label for="setComment" class="control-label">{{ trans('news::view.Important') }}</label>
                                <div class="input-group">
                                    <div id="radioBtnImportant" class="btn-group btn-radio">
                                        @if($postItem->id)
                                            <a class="btn btn-primary btn-sm @if($postItem->important == Post::BE_IMPORTANT) active @else notActive @endif" data-toggle="setImportant" data-title="1">{{ trans('news::view.YES') }}</a>
                                            <a class="btn btn-primary btn-sm @if($postItem->important == Post::NO_IMPORTANT) active @else notActive @endif" data-toggle="setImportant" data-title="0">{{ trans('news::view.NO') }}</a>
                                        @else
                                            <a class="btn btn-primary btn-sm active" data-toggle="setImportant" data-title="1">{{ trans('news::view.YES') }}</a>
                                            <a class="btn btn-primary btn-sm notActive" data-toggle="setImportant" data-title="0">{{ trans('news::view.NO') }}</a>
                                        @endif
                                    </div>
                                    <input type="hidden" name="post[important]" id="setImportant" value="{{ $postItem->id ? $postItem->important : Post::BE_IMPORTANT }}">
                                </div>
                            </div>
                            <div class="form-group form-group-select2 col-md-4 padding-left-0">
                                <label for="setTopPost" class="control-label">{{ trans('news::view.Set Top') }}</label>
                                <div class="input-group">
                                    <div id="radioBtnSetTop" class="btn-group btn-radio">
                                        @if($postItem->id)
                                            <a class="btn btn-primary btn-sm @if($postItem->set_top == Post::SET_TOP_POST) active @else notActive @endif" data-toggle="setTopPost" data-title="1">{{ trans('news::view.YES') }}</a>
                                            <a class="btn btn-primary btn-sm @if($postItem->set_top == Post::NOT_SET_TOP_POST) active @else notActive @endif" data-toggle="setTopPost" data-title="0">{{ trans('news::view.NO') }}</a>
                                        @else
                                            <a class="btn btn-primary btn-sm active" data-toggle="setTopPost" data-title="1">{{ trans('news::view.YES') }}</a>
                                            <a class="btn btn-primary btn-sm notActive" data-toggle="setTopPost" data-title="0">{{ trans('news::view.NO') }}</a>
                                        @endif
                                    </div>
                                    <input type="hidden" name="post[set_top]" id="setTopPost" value="{{ $postItem->id ? $postItem->set_top : Post::SET_TOP_POST }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group js-post-news hidden" >
                                <label for="description" class="control-label">{{ trans('news::view.Description') }}</label>
                                <div class="">
                                    <textarea name="post[desc]" class="ckedittor-text" id="description">{{ $postItem->desc }}</textarea>
                                </div>
                    </div>
                    <div class="form-group hidden" id="js-youtube-link">
                        <label for="description" class="control-label">{{ trans('news::view.Link Youtube') }}</label>
                        <div class="">
                            <input name="post[youtube_link]" class="form-control input-field" type="text"
                                   value="{{ $postItem->youtube_link }}" placeholder="{{ trans('news::view.Link Youtube') }}" onkeyup="getYoutubeId();"/>
                            <input type="hidden" name="post[youtube_id]" value="{{ $postItem->youtube_id }}" id="js-youtube-id">
                        </div>
                    </div>
                    <div class="form-group" id="js-audio-link">
                        <label for="description" class="control-label required">Audio link<em>*</em></label>
                        <div class="box-body">
                            @if ($postItem->path && !$postItem->deleted_attach)
                                <div class="margin-bottom-5">
                                    <a href="{{ asset('storage/'. $postItem->path) }}">{{ basename($postItem->path) }}</a>
                                    <span><button type="button" class="delete-file" data-id="{{$postItem->attach_id}}"><i class="fa fa-remove" style="font-size:15px; color:red;"></i></button></span>
                                </div>
                                <audio src="{{ asset('storage/'. $postItem->path) }}" controls="controls">{{$postItem->path}}</audio>
                            @else
                                <input type="file" name="audio_link" class="file_audio" accept="audio/mp3, audio/mpeg">
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 text-center">
                            <button type="submit" class="btn btn-success btn-submit-ckeditor btn-save-news" id="btn-submit" data-published="{{ $postItem->published }}">
                                {{ trans('news::view.Save') }}
                            </button>
                            <a href=""  class="btn btn-primary" id="btn-preview">
                               {{ trans('news::view.Preview') }}
                             <i class="fa fa-spin fa-refresh hidden"></i>
                            </a>
                            @if ($postItem->id)
                                @if ($postItem->published)
                                    <button type="button" class="btn btn-primary btn-publish-news" title="Republishing">{{ trans('news::view.Published') }}</button>
                                @else
                                    <button type="button" class="btn btn-primary btn-publish-news">{{ trans('news::view.Publish') }}</button>
                                @endif
                            @endif
                        </div>
                    </div>
                </form>
               <div class="post-detail hidden" style="margin: 0 auto; width: 800px">
                    <h2 class="bci-header preview-title"></h2>
                    <div class="row">
                        <p class="post-meta post-date col-xs-6 preview-date" style="padding-top: 20px;">
                            <?php
                                $publicAt = $postItem->public_at ? date('d/m/Y', strtotime($postItem->public_at)): '';
                                    if($publicAt!=NULL){
                                        echo $publicAt;
                                    }else{
                                        echo date('d/m/Y');
                                    }
                            ?>
                        </p>
                        <div class="post-like col-xs-6 text-right">
                            <span class="btn-link"  style="font-size: 17px">0</span>
                            <i class="fa fa-eye eye-detail" aria-hidden="true"></i>
                            <span class="btn-link count-like" style="font-size: 17px; cursor: pointer;">0</span>
                            <button class="btn-primary-outline" title="{{ trans('news::view.Like') }}">
                                <i class="fa fa-thumbs-up thumb-dislike size-detail" aria-hidden="true"></i>
                            </button>
                            <span class="btn-link count-comment-up" style="font-size: 17px; padding-right: 4px">0</span>
                            <i class="fa fa-comments comment-icon size-detail" aria-hidden="true"></i>
                        </div>
                    </div>
                        <div class="post-desc ckedittor-text preview-content" style="padding-top: 15px"></div>

                        <div>
                            <p class="author" style="padding-top: 27px;"></p>
                        </div>
                        <div class="align-center">
                            <button class="btn btn-primary btn-back">{{ trans('news::view.Back') }}</button>
                        </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade modal-warning" id="modal-publish-news"  role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">Publish to Webvn</h4>
                </div>
                <div class="modal-body">
                    <p class="text-default"><span class="error">*Dữ liệu chưa lưu sẽ không được publish</span></p>
                    <label>Chọn danh mục</label>
                    <select class="form-control" id="category_webvn">
                        <option value="3">Tin tức sự kiện</option>
                        <option value="5">-- Nội bộ</option>
                        <option value="6">-- Đối ngoại</option>
                        <option value="7">-- Báo chí - truyền thông nói về chúng tôi</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-close pull-left" data-dismiss="modal">{{ Lang::get('core::view.Cancel') }}</button>
                    <button type="button" class="btn btn-primary btn-publish-recruitment">{{ Lang::get('core::view.OK') }}</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>
</div>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="{{ URL::asset('lib/js/jquery.validate.min.js') }}"></script>
<script src="{{ URL::asset('lib/ckeditor/ckeditor.js') }}"></script>
<script src="{{ URL::asset('lib/ckfinder/ckfinder.js') }}"></script>
<script src="{{ CoreUrl::asset('asset_news/js/news.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script type="text/javascript">
    var urlPublishNews = '{{ route('news::post.publishNews') }}';
    var urlPublishNewsRecruitment = '{{ route('news::post.publishNewsRecruitment') }}';
    var statusPublished =  {{ Post::STATUS_PUBLISHED }};
    var urlDeleteFile = '{{ route('news::manage.post.delete.file') }}';
    var token = '{{ csrf_token() }}';
    var today = new Date().toISOString().slice(0, 16);
    document.getElementsByName("schedulePost")[0].min = today;
    jQuery(document).ready(function ($) {
        selectSearchReload();
        
        $('.fg-valid-custom').change(function() {
            var status = $('#status :selected').text();
            var isDisplaySchedulePost = (status == 'Disable') ? 'block' : 'none';
            $('.form-group-schedulepost').css('display', isDisplaySchedulePost);
        });

        $('.select-search-multi').select2();

        $(".select-tag-multi").select2({
            tags: true,
            tokenSeparators: ['/',',',';']
        });

        if ($('#is_video').val() == 1) {
            $('#js-youtube-link').removeClass('hidden');
            $('.js-post-news').addClass('hidden');
            $('#js-audio-link').addClass('hidden');
        } else if ($('#is_video').val() == 0) {
            $('#js-youtube-link').addClass('hidden');
            $('.js-post-news').removeClass('hidden');
            $('#js-audio-link').addClass('hidden');
        } else {
            $('#js-youtube-link').addClass('hidden');
            $('.js-post-news').addClass('hidden');
            $('#js-audio-link').removeClass('hidden');
        }
        $('#is_video').on('change', function() {
            if ($(this).val() == 1) {
                $('#js-youtube-link').removeClass('hidden');
                $('.js-post-news').addClass('hidden');
                $('#js-audio-link').addClass('hidden');
            } else if ($(this).val() == 0) {
                $('#js-youtube-link').addClass('hidden');
                $('.js-post-news').removeClass('hidden');
                $('#js-audio-link').addClass('hidden');
            } else {
                $('#js-youtube-link').addClass('hidden');
                $('.js-post-news').addClass('hidden');
                $('#js-audio-link').removeClass('hidden');
            }
        });

        var desEditor = CKEDITOR.replace( 'description', {
            extraPlugins: 'autogrow,image2,fixed',
            removePlugins: 'justify,colorbutton,indentblock,resize',
            removeButtons: 'About',
            startupFocus: true
        });

        CKFinder.setupCKEditor( desEditor, '/lib/ckfinder' );

        $('.btn-submit-ckeditor').click(function() {
            desEditor.updateElement();
            var aux = document.createElement("input");
            aux.setAttribute("value", $('#render-link').val());
            document.body.appendChild(aux);
            aux.select();
            document.execCommand("copy");

            document.body.removeChild(aux);
        });

        CKFinder.config.resourceType='Images';
        CKFinder.config.rememberLastFolder = true;
        $('.btn-ckfinder-browse-file').click(function(event) {
            event.preventDefault();
            var idInput = $(this).data('element');
            if(!idInput || !$(idInput).length) {
                return false;
            }
            var finder = new CKFinder();
            finder.selectActionFunction = function(fileUrl) {
                fileUrl = fileUrl.replace(/^[\/]+|[\/]+$/gm, '');
                $(idInput).val(fileUrl);
                $(idInput).closest('.ckfinder-preview-wrapper').find('.ckfinder-img-preview').
                    html('<img src=" ' + baseUrl + fileUrl + '" />');
            };
            finder.popup();
        });

        var messageValidate = {
            required: '{{ trans('core::message.This field is required') }}',
            max: '{{ trans('core::message.This field is max 20971520') }}',
            mp3: '{{ trans('core::message.This field is mp3') }}',
        };

        $('#form-post-edit').validate({
            rules: {
                'post[title]': {
                    required: true
                },
                'post[status]': {
                    required: true
                },
                'post[tags][]': {
                    required: true
                },
                'audio_link': {
                    required: true,
                    maxlength: 20971520,
                    validateAudio: true,
                },
            },
            messages: {
                'post[title]': {
                    required: messageValidate.required
                },
                'post[slug]': {
                    required: messageValidate.required
                },
                'post[tags][]': {
                    required: messageValidate.required
                },
                'post[status]': {
                    required: messageValidate.required
                },
                'audio_link': {
                    required: messageValidate.required,
                    maxlength: messageValidate.max,
                },
            }
        });

        $.validator.addMethod("validateAudio", function (value, element) {
            var extension = value.replace(/^.*\./, '');
            return jQuery.inArray(extension, ['mp3', 'mpeg']) !== -1;
        }, "Hãy thêm vào một file audio");

        $(document).on("click", ".delete-file", function () {
            var fileId = $('.delete-file').attr('data-id');
            $.ajax({
                url: urlDeleteFile,
                method: "POST",
                dataType: "json",
                data: {
                    _token: token,
                    fileId: fileId,
                },
                success: function(data) {
                    window.location.reload();
                }
            });
        });
    });
</script>
@endsection
