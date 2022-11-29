@extends('layouts.default')

@section('title')
    {{ trans('news::view.Featured post list') }}
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="{{ URL::asset('asset_news/css/news.css') }}" />
@endsection

@section('content')
    <?php
    use Rikkei\Team\View\Config as TeamConfig;
    use Rikkei\Core\View\View as CoreView;
    use Rikkei\Core\View\Form as CoreForm;
    use Rikkei\News\Model\Post;

    ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-info">
                <div class="box-body">
                    @include('team::include.filter', ['domainTrans' => 'news'])
                </div>
                <div class="table-responsive">
                    <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                        <thead>
                        <tr>
                            <th class="col-id width-10" style="width: 20px;">{{ trans('news::view.No.') }}</th>
                            <th>{{ trans('news::view.Image') }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('title') }} col-title" data-order="title" data-dir="{{ TeamConfig::getDirOrder('title') }}">{{ trans('news::view.Title') }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('slug') }} col-slug" data-order="slug" data-dir="{{ TeamConfig::getDirOrder('slug') }}">{{ trans('news::view.Slug') }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('status') }} col-status" data-order="status" data-dir="{{ TeamConfig::getDirOrder('status') }}">{{ trans('news::view.Status') }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('is_video') }} col-status" data-order="is_video" data-dir="{{ TeamConfig::getDirOrder('is_video') }}">{{ trans('project::view.Type') }}</th>
                            <th>{{ trans('news::view.Important') }}</th>
                            <th>{{ trans('news::view.Set Top') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="filter-input-grid">
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[title]" value="{{ CoreForm::getFilterData("title") }}" placeholder="{{ trans('news::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[slug]" value="{{ CoreForm::getFilterData("slug") }}" placeholder="{{ trans('news::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <select class="form-control select-grid filter-grid select-search" name="filter[number][status]">
                                            <option value="">&nbsp;</option>
                                            <?php $filterStatus = CoreForm::getFilterData('number','status');?>
                                            @foreach($optionStatus as $key => $value)
                                                <option value="{{ $key }}" {{ is_numeric($filterStatus) && (intval($filterStatus) === $key) ? 'selected' : '' }}>{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <select class="form-control select-grid filter-grid select-search" name="filter[number][is_video]">
                                            <option value="">&nbsp;</option>
                                            <?php $filterType = CoreForm::getFilterData('number','is_video');?>
                                            @foreach($optionType as $key => $value)
                                                <option value="{{ $key }}" {{ is_numeric($filterType) && (intval($filterType) === $key) ? 'selected' : '' }}>{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </td>
                            <td></td>
                            <td></td>
                        </tr>
                        @if(isset($collectionModel) && count($collectionModel))
                            <?php $i = CoreView::getNoStartGrid($collectionModel); ?>
                            @foreach($collectionModel as $item)
                                <tr data-index="{{ $i }}">
                                    <td>{{ $i }}</td>

                                    <td class="news-grid-image">
                                        <div class="news-manage-image">
                                            <img src="{{ $item->getThumbnail(true) }}" />
                                        </div>
                                    </td>

                                    <td>
                                        <a href="{{ route('news::manage.post.edit', ['id' => $item->id ]) }}">{{ $item->title }}</a>
                                    </td>
                                    <td>{{ $item->slug }}</td>
                                    <td>{{ $item->getLabelStatus($optionStatus) }}</td>
                                    <td>{{ $optionType[$item->is_video]}}</td>
                                    <td>
                                        <div class="input-group {{ $item->is_video == 1 ? 'hidden' : ''}}">
                                            <div id="radioBtnImportant_{{ $i }}" class="btn-group btn-radio">
                                                <a class="btn btn-primary btn-sm @if($item->important == Post::BE_IMPORTANT) active @else notActive @endif" data-toggle="setImportant_{{ $i }}" data-title="1" data-submit="form-submit-important-{{$i}}">

                                                    {{ trans('news::view.YES') }}
                                                </a>
                                                <a class="btn btn-primary btn-sm @if($item->important == Post::NO_IMPORTANT) active @else notActive @endif" data-toggle="setImportant_{{ $i }}" data-title="0" data-submit="form-submit-important-{{$i}}">{{ trans('news::view.NO') }}</a>
                                                <form action="{{URL::route('news::manage.featured_article.update')}}" method="POST">
                                                    <input type="hidden" name="_method" value="PUT">
                                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                    <input type="hidden" name="important" id="setImportant_{{ $i }}" value="{{ $item->id ? $item->important : Post::BE_IMPORTANT }}">
                                                    <input type="hidden" name="id" id="post_id_{{ $i }}" value="{{ $item->id ? $item->id : ''}}">
                                                    <input type="submit" id="form-submit-important-{{$i}}" class="btn btn-danger hidden"/>
                                                </form>

                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <div id="radioBtnSetTop_{{ $i }}" class="btn-group btn-radio">
                                                <a class="btn btn-primary btn-sm @if($item->set_top == Post::SET_TOP_POST) active @else notActive @endif" data-toggle="setTopPost_{{ $i }}" data-title="1" data-submit="form-submit-set-top-{{$i}}">{{ trans('news::view.YES') }}</a>
                                                <a class="btn btn-primary btn-sm @if($item->set_top == Post::NOT_SET_TOP_POST) active @else notActive @endif" data-toggle="setTopPost_{{ $i }}" data-title="0" data-submit="form-submit-set-top-{{$i}}">{{ trans('news::view.NO') }}</a>
                                                <form action="{{URL::route('news::manage.featured_article.update')}}" method="POST">
                                                    <input type="hidden" name="_method" value="PUT">
                                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                    <input type="hidden" name="set_top" id="setTopPost_{{ $i }}" value="{{ $item->id ? $item->set_top : Post::SET_TOP_POST }}">
                                                    <input type="hidden" name="id" id="id_post_{{ $i }}" value="{{ $item->id ? $item->id : ''}}">
                                                    <input type="submit" id="form-submit-set-top-{{$i}}" class="btn btn-danger hidden"/>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php $i++; ?>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="text-center">
                                    <h2 class="no-result-grid">{{ trans('news::view.No results found') }}</h2>
                                </td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
                <div class="box-body">
                    @include('team::include.pager')
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            selectSearchReload();
        });

        $(document).ready(function () {
            setTimeout(function () {
                $('.flash-message').remove();
            }, 2000);

            // set post silde and set top post
            $('.btn-radio a').on('click', function(e){
                e.preventDefault();
                var tabindex = $(this).closest('tr').data("index");
                var sel = $(this).data('title');
                var tog = $(this).data('toggle');
                var idSubmit = $(this).data('submit');
                $('#'+tog).prop('value', sel);

                $('a[data-toggle="'+tog+'"]').not('[data-title="'+sel+'"]').removeClass('active').addClass('notActive');
                $('a[data-toggle="'+tog+'"][data-title="'+sel+'"]').removeClass('notActive').addClass('active');

                $('#'+idSubmit).click();
            });
        });
    </script>
@endsection

