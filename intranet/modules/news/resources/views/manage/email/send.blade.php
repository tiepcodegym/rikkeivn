@extends('layouts.default')

@section('title')
{{ $titleHeadPage }}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="{{ URL::asset('asset_news/css/news.css') }}" />
@endsection

@section('content')
<?php 
use Carbon\Carbon; 
$weekCurrent = Carbon::parse()->format('W');
?>
<div class="row">
    <div class="col-sm-12">
        <form id="form-post-send-email" method="post" action="{{ URL::route('news::manage.email.send.post') }}" 
            class="form-submit-ajax" autocomplete="off" data-callback-success="previewSendPost">
            <input type="hidden" name="preview" value="0" />
            {!! csrf_field() !!}
        <div class="box box-info">
            <div class="box-body">
                <div class="row post-render-wrapper">
                    <div class="col-sm-6">
                        <div class="form-group form-group-select2">
                            <label class="required">{!!trans('news::view.Mail receive')!!} <em>*</em></label>
                            <div>
                                <input type="text" name="mail_to" value="" placeholder="" class="form-control" />
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group form-group-select2">
                            <label>{{ trans('news::view.Week') }}</label>
                            <div>
                                <select class="select-search has-search" name="week">
                                    @for($i = 1 ; $i <= 54 ; $i++)
                                        <option value="{{ $i }}"{{ $i == $weekCurrent ? ' selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-sm-12">
                        <div class="row post-render-container"></div>
                    </div>
                </div>
            </div>
        </div>
            
        <div class="box box-info">
            <div class="box-body">
                <div class="row post-render-wrapper">
                    <div class="col-sm-12">
                        <div class="form-group form-group-select2 row">
                            <label class="control-label col-sm-2">{{ trans('news::view.Post feature') }}</label>
                            <div class="col-sm-10">
                                <select class="select-search-remote" data-remote-url="{{ URL::route('news::manage.email.send.post.list.ajax') }}" data-type="feature"></select>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="row post-render-container"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="box box-info">
            <div class="box-body">
                <div class="row post-render-wrapper">
                    <div class="col-sm-12">
                        <div class="form-group form-group-select2 row">
                            <label class="control-label col-sm-2">{{ trans('news::view.Post in week') }}</label>
                            <div class="col-sm-10">
                                <select class="select-search-remote" data-remote-url="{{ URL::route('news::manage.email.send.post.list.ajax') }}" data-type="week"></select>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="row post-render-container"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="box box-info">
            <div class="box-body">
                <div class="row post-render-wrapper">
                    <div class="col-sm-12">
                        <div class="form-group form-group-select2 row">
                            <label class="control-label col-sm-2">{{ trans('news::view.Post more') }}</label>
                            <div class="col-sm-10">
                                <select class="select-search-remote" data-remote-url="{{ URL::route('news::manage.email.send.post.list.ajax') }}" data-type="more"></select>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="row post-render-container"></div>
                    </div>
                </div>
            </div>
        </div>
        <?php /*
        <div class="box box-info">
            <div class="box-body">
                <div class="row post-render-wrapper">
                    <div class="col-sm-12">
                        <div class="form-group form-group-select2 row">
                            <label class="control-label col-sm-2">{{ trans('news::view.Post another') }}</label>
                            <div class="col-sm-10">
                                <select class="select-search-remote" data-remote-url="{{ URL::route('news::manage.email.send.post.list.ajax') }}" data-type="another"></select>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="row post-render-container"></div>
                    </div>
                </div>
            </div>
        </div>
        */ ?>
        <div class="row margin-top-20">
            <div class="col-sm-12 text-center">
                <button type="submit" class="btn btn-primary btn-submit-sp" data-preview="1">
                    {{ trans('news::view.Preview') }}
                </button>
                <button type="submit" class="btn btn-primary btn-submit-sp warn-confirm" data-preview="0"
                    data-noti="{{ trans('news::view.Are you sure to send email?') }}">
                    {{ trans('news::view.Send email') }}
                    <i class="fa fa-paper-plane"></i>
                </button>
            </div>
        </div>
        </form>
    </div>
</div>
<div class="hidden post-render-template">
    <div class="col-sm-6 remove-block-wrapper">
        <div class="pr-item">
            <div class="pr-image">
                <img src="{ image }" />
            </div>
            <div class="pr-meta">
                <div class="pr-title">
                    <p>{ title }</p>
                </div>
            </div>
            <input type="hidden" name="post[{ type }][]" value="{ id }"/>
            <div class="clearfix"></div>
            <a class="remove-block-click" href="#"><i class="fa fa fa-minus-circle"></i></a>
        </div>
    </div>
</div>
<!-- modal success cofirm -->
<div class="modal fade" id="modal-preview-email">
    <div class="modal-dialog modal-full-width">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ trans('news::view.Preview') }}</h4>
            </div>
            <div class="modal-body">
                <div class="preview-send-email"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-close" data-dismiss="modal">{{ trans('core::view.Close') }}</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div><!-- end modal warning cofirm -->
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="{{ URL::asset('lib/js/jquery.validate.min.js') }}"></script>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        var textValidMail = '{{ trans('core::view.Please enter a valid email address') }}';
        RKfuncion.select2Post = {
            option: {},
            init: function (option) {
                if (typeof $().select2 == 'undefined') {
                    return true;
                }
                var __this = this,
                    optionDefault = {
                        showSearch: false
                    };
                option = $.extend(optionDefault, option);
                __this.option = option;
                $('.select-search-remote').each(function() {
                    __this.elementRemote($(this), option);
                });
            },
            elementRemote: function(dom, option) {
                if (!dom.data('remote-url')) {
                    return true;
                }
                var __this = this,
                    optionDefault = {
                        delay: 500
                    };
                option = $.extend(optionDefault, option);
                __this.option = option;
                dom.select2({
                    id: function(response){ 
                        return response.id;
                    },
                    ajax: {
                        url: dom.data('remote-url'),
                        dataType: 'json',
                        delay: option['delay'],
                        data: function (params) {
                            return {
                                q: params.term, // search term
                                page: params.page
                            };
                        },
                        processResults: function (data, params) {
                            params.page = params.page || 1;
                            return {
                                results: data.items,
                                pagination: {
                                    more: (params.page * 20) < data.total_count
                                }
                            };
                        },
                        cache: true
                    },
                    escapeMarkup: function (markup) { 
                        return markup; 
                    }, // let our custom formatter work    
                    minimumInputLength: 1,
                    templateResult: __this.__formatReponse, // omitted for brevity, see the source of this page
                    templateSelection: __this.__formatReponesSelection // omitted for brevity, see the source of this page
                });
                dom.on('select2:select', function (e) {
                    var type = dom.data('type'),
                        postRenderTemplate = $('.post-render-template').html();
                    if (!type || !postRenderTemplate) {
                        return true;
                    }
                    postRenderTemplate = postRenderTemplate.replace(/\{\simage\s\}/g, e.params.data.image);
                    postRenderTemplate = postRenderTemplate.replace(/\{\stitle\s\}/g, e.params.data.text);
                    postRenderTemplate = postRenderTemplate.replace(/\{\stype\s\}/g, type);
                    postRenderTemplate = postRenderTemplate.replace(/\{\sid\s\}/g, e.params.data.id);
                    dom.closest('.post-render-wrapper').find('.post-render-container').append(postRenderTemplate);
                });
            },
            __formatReponse: function (response) {
                if (response.loading) {
                    return RKfuncion.general.parseHtml(response.text);
                }
                return markup = 
                    "<div class='select2-result-repository clearfix'>" +
                        "<div class='select2-result-repository__avatar'><img src='" + response.image + "' /></div>" +
                        "<div class='select2-result-repository__meta'>" +
                            "<div class='select2-result-repository__title'>" + response.text + "</div>" +
                        "</div>" +
                    "</div>";
              },
            __formatReponesSelection: function (response) {
                return response.text;
            }
        };
        $('.btn-submit-sp').click(function() {
            var data = $(this).data('preview');
            $('form#form-post-send-email input[name="preview"]').val(data);
        });
        RKfuncion.formSubmitAjax.previewSendPost = function(dom, data) {
            if (typeof data.html == 'undefined' || !data.html) {
                return true;
            }
            var iframe = $('<iframe style="height: 450px; width: 100%;">');
            $('.preview-send-email').html(iframe);
            setTimeout( function() {
                var doc = iframe[0].contentWindow.document;
                var body = $('body', doc);
                body.replaceWith(data.html);
                $('#modal-preview-email').modal('show');
            }, 1 );
        };
        RKfuncion.select2.init();
        RKfuncion.select2Post.init();
        RKfuncion.general.removeBlock();

        // validate form
        $('#form-post-send-email').validate({
            rules: {
                mail_to: {
                    required: true,
                    email: true,
                },
            },
            messages: {
                mail_to: {
                    required: requiredText,
                    email: textValidMail,
                },
            },
        });
    });
</script>
@endsection
