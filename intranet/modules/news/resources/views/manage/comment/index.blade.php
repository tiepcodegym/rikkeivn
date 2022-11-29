<?php
use Rikkei\Team\View\Config as TeamConfig;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Core\View\CoreUrl;
use Rikkei\News\Model\PostComment;
use Rikkei\Test\View\ViewTest;
?>

@extends('layouts.default')

@section('title')
    {{ $titleHeadPage }}
@endsection
@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css"/>
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_news/css/comment.css') }}"/>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-info">
                <div class="box-body">
                    <div class="left-action" style="width: 405px; float: left;">
                        <button class="btn btn-primary" id="approveAll">{{ trans('news::view.Approve') }}</button>
                        <button class="btn btn-danger" id="un_approve_all">{{ trans('news::view.UnApprove') }}</button>
                    </div>
                    <div style="width: 780px;float: right;text-align: right;">
                        <button type="button"
                                class="btn btn-primary deleteAll">{{ trans('news::view.Delete') }}</button>
                        <button class="btn btn-primary btn-reset-filter">
                            <span>{{ trans('news::view.Reset filter') }} <i
                                        class="fa fa-spin fa-refresh hidden"></i></span>
                        </button>
                        <button class="btn btn-primary btn-search-filter">
                            <span>{{ trans('news::view.Search') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped dataTable table-bordered table-hover table-grid-data table-comment">
                        <thead>
                        <tr>
                            <th class="col-id width-20" style="width: 20px;"></th>
                            <th class="col-id width-20 sorting"
                                width="20px" {{ TeamConfig::getDirClass('blog_post_comments.id') }}"
                            data-order="blog_post_comments.id"
                            data-dir="{{ TeamConfig::getDirOrder('blog_post_comments.id') }}">{{ trans('news::view.No.') }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('comment') }} col-comment"
                                data-order="comment"
                                data-dir="{{ TeamConfig::getDirOrder('comment') }}">{{ trans('news::view.Comment') }}</th>
                            <th class="sorting {{ TeamConfig::getDirClass('nickname') }} col-nickname"
                                data-order="nickname"
                                data-dir="{{ TeamConfig::getDirOrder('nickname') }}">{{ trans('news::view.User') }}</th>
                            <th class="col-status">{{ trans('news::view.Status') }}</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="filter-input-grid">
                            <td>
                                <input type="checkbox" id="checkedAll" name="checkedAll">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            </td>
                            <td class="col-sm-1"></td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[blog_post_comments.comment]"
                                               value="{{ CoreForm::getFilterData('blog_post_comments.comment') }}"
                                               placeholder="{{ trans('news::view.Search') }}..."
                                               class="filter-grid form-control"/>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[emp.email]"
                                               value="{{ CoreForm::getFilterData('emp.email') }}"
                                               placeholder="{{ trans('news::view.Search') }}..."
                                               class="filter-grid form-control"/>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-10">
                                        <select class="form-control select-grid filter-grid select-search"
                                                name="filter[except][blog_post_comments.status]">
                                            <option value="">{{ trans('news::view.Status Select') }}</option>
                                            <?php $filterStatus = CoreForm::getFilterData('except',
                                                'blog_post_comments.status');?>

                                            @foreach($allStatus as $key => $value)
                                                <option value="{{ $key }}" {{ is_numeric($filterStatus) & intval($filterStatus) === $key ? 'selected' : ''}}>
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </td>
                            <td></td>
                        </tr>
                        @if(isset($collectionModel) && count($collectionModel))
                            @foreach($collectionModel as $item)
                                <tr id="row-{{ $item->id }}">
                                    <td><input type="checkbox" value="{{ $item->id }}" name="checkAll"
                                               class="checkSingle"></td>
                                    <td>
                                        <center>{{ $item->id }}</center>
                                    </td>
                                    <td class="comment">
                                        @php
                                            if (is_null($item->edit_comment)) :
                                                $comment = $item->comment;
                                            else :
                                                $comment = $item->status == PostComment::STATUS_COMMENT_ACTIVE
                                                ? $item->edit_comment : $item->comment;                                      
                                            endif;
                                        @endphp
                                        <div class="short-message ws-pre-line">{{$comment }}</div>
                                    </td>
                                    <td>{{ CoreView::getNickName($item->email) }}</td>
                                    <td class="changeStatus-{{ $item->id }}">
                                        @if(is_null($item->edit_comment) && $item->status == PostComment::STATUS_COMMENT_ACTIVE)
                                            {{ trans('news::view.Approve') }}
                                        @else
                                            {{ trans('news::view.UnApprove') }}
                                        @endif
                                        {{-- {{ $item->getLabelStatus($allStatus) }} --}}
                                    </td>
                                    <td>
                                        <a class="btn-edit" href="{{ url('news/manage/comment/detail', $item->id) }}">
                                            <i class="fa fa-info-circle"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="text-center">
                                    <h2 class="no-result-grid">{{ trans('news::view.Not found comment') }}</h2>
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
    <div class="modal fade" id="modal-comment" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body">
                    <p class="text-default"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-close" data-dismiss="modal">Đ&oacute;ng</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>
@endsection
@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/showdown/1.9.0/showdown.min.js"></script>
    <script src="{{ CoreUrl::asset('lib/js/emoticons/jquery.emojiarea.min.js') }}"></script>
    <script src="{{ CoreUrl::asset('asset_news/js/emojiicons.js')}}"></script>
    <script type="text/javascript">
        jQuery(document).ready(function () {
            selectSearchReload();
        });
        var url_unapprove_all = '{{ url('news/manage/comment/unApproveAll') }}';
        var url_approve_all = '{{ url('news/manage/comment/changeStatusAll') }}';
        var url_delete = '{{ url('news/manage/comment/deleteAll') }}';
        var _token = '{{ csrf_token() }}';
        var emoticonPath = "{{ asset('asset_news/images/emoticons') }}";
        $('.short-message').shortedContent(
            {showChars: 100}
        );
    </script>
    <script src="{{ CoreUrl::asset('asset_news/js/comment_post.js')}}"></script>
@endsection

