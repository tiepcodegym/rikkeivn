@extends('layouts.default')

@section('title')
{{ $titleHeadPage }}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="box-body">
                <form id="form-post-edit" method="post" action="{{ URL::route('news::manage.category.save') }}"
                    class="form-submit-ajax has-valid form-horizontal" autocomplete="off">
                    {!! csrf_field() !!}
                    @if ($categoryItem->id)
                        <input type="hidden" name="id" value="{{ $categoryItem->id }}" />
                    @endif
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="title" class="control-label required col-sm-3">{{ trans('news::view.Title') }} <em>*</em></label>
                                <div class="col-sm-9">
                                    <input name="cate[title]" class="form-control input-field" type="text" id="title"
                                        value="{{ $categoryItem->title }}" placeholder="{{ trans('news::view.Title') }}" />
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="slug" class="control-label col-sm-3">{{ trans('news::view.Slug') }}</label>
                                <div class="col-sm-9">
                                    <input name="cate[slug]" class="form-control input-field col-sm-9" type="text" id="slug"
                                        value="{{ $categoryItem->slug }}" placeholder="{{ trans('news::view.Slug') }}" />
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="title" class="control-label required col-sm-3">{{ trans('news::view.Title en') }} <em>*</em></label>
                                <div class="col-sm-9">
                                    <input name="cate[title_en]" class="form-control input-field" type="text" id="title_en"
                                           value="{{ $categoryItem->title_en }}" placeholder="{{ trans('news::view.Title en') }}" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group form-group-select2">
                                <label for="status" class="control-label required col-sm-3">{{ trans('news::view.Status') }} <em>*</em></label>
                                <div class="fg-valid-custom col-sm-9">
                                    <select name="cate[status]" id="status" class="select-search">
                                        @foreach ($optionStatus as $key => $value)
                                            <option value="{{ $key }}"{{ $categoryItem->status == $key ? ' selected' : '' }}>{{ trans('news::view.'.$value) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="sort_order" class="control-label col-sm-3">{{ trans('news::view.Order') }}</label>
                                <div class="col-sm-9">
                                    <input name="cate[sort_order]" class="form-control input-field" type="number" id="sort_order"
                                        value="{{ $categoryItem->sort_order }}" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group form-group-select2">
                                <label for="parentId" class="control-label required col-sm-3">{{ trans('news::view.Menu') }}</label>
                                <div class="fg-valid-custom col-sm-9">
                                    <select name="cate[parent_id]" id="parentId" class="select-search">
                                        <option value="0"{{ $categoryItem->parent_id == 0 ? ' selected' : '' }}>Parent</option>
                                        <option disabled="disabled">----</option>
                                        <optgroup label="Sub menu">
                                            @foreach ($parentMenu as $key => $value)
                                                <option value="{{ $key }}"{{ $categoryItem->parent_id == $key ? ' selected' : '' }}>{{ $value }}</option>
                                            @endforeach
                                        </optgroup>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12 text-center">
                            <button type="submit" class="btn btn-success btn-submit-ckeditor">
                                {{ trans('news::view.Save') }}
                                <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="{{ URL::asset('lib/js/jquery.validate.min.js') }}"></script>
<script src="{{ URL::asset('lib/ckeditor/ckeditor.js') }}"></script>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        selectSearchReload();
        var messageValidate = {
            required: '{{ trans('core::message.This field is required') }}',
            number: '{{ trans('core::message.Please enter a valid number') }}'
        };
        $('#form-post-edit').validate({
            rules: {
                'cate[title]': {
                    required: true
                },
                'cate[title_en]': {
                    required: true
                },
                'cate[status]': {
                    required: true
                },
                'cate[sort_order]': {
                    number: true
                }
            },
            messages: {
                'cate[title]': {
                    required: messageValidate.required
                },
                'cate[title_en]': {
                    required: messageValidate.required
                },
                'cate[sort_order]': {
                    number: messageValidate.number
                },
                'cate[status]': {
                    required: messageValidate.required
                }
            }
        });
    });
</script>
@endsection
