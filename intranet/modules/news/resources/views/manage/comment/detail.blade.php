<?php
    use Rikkei\Core\View\View as CoreView;
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\News\Model\PostComment;
?>

@extends('layouts.default')

@section('title')
    {{ $titleHeadPage }}
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_news/css/comment.css') }}"/>
@endsection

@section('content')
    @if (session()->has('message'))
        <div class="col-md-12">
            <div class="alert alert-success alert-dismissible fade in alert-hiden" role="alert">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">×</a>
                {{ session('message') }}
            </div>
        </div>
    @endif
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-info">
                <div class="box-body">
                    <form action="{{ route('news::manage.comment.changeStatusComment', $comment->id) }}" method="post">
                        <table class="table table-striped dataTable table-bordered table-hover table-grid-data table-comment">
                            <input type="hidden" name="id" value="{{ $comment->id }}">
                        <tbody>
                            <tr>
                                <td class="col-md-2">ID</td>
                                <td>{{ $comment->id }}</td>
                            </tr>
                            <tr>
                                <td class="col-md-2">{{ trans('news::view.Comment') }}</td>
                                <td class="comment">
                                    @if(is_null($comment->edit_comment) )
                                        {!! nl2br($comment->comment) !!}
                                    @else
                                        {!! $comment->status == PostComment::STATUS_COMMENT_ACTIVE
                                                ? nl2br($comment->edit_comment) : nl2br($comment->comment) !!}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="col-md-2">{{ trans('news::view.User') }}</td>
                                <td>{{ CoreView::getNickName($comment->email) }}</td>
                            </tr>
                            <tr>
                                <td class="col-md-2">{{ trans('news::view.Post title') }}</td>
                                <td>{{ $comment->title }}</td>
                            </tr>
                            <tr>
                                <td class="col-md-2">{{ trans('news::view.Status') }}</td>
                                <td>
                                    <label class="radio-inline">
                                        <input type="radio" name="status" value="1" {{ 
                                            (($comment->status ==  PostComment::STATUS_COMMENT_ACTIVE)
                                            && is_null($comment->edit_comment) ) ?  'checked' : ''}} >{{ trans('news::view.Approve') }}
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="status" value="0" {{ 
                                            (($comment->status == PostComment::STATUS_COMMENT_NOT_ACTIVE)
                                            || !is_null($comment->edit_comment) )  ?  'checked' : ''}} >{{ trans('news::view.UnApprove') }}
                                    </label>
                                    <input type="hidden" value="{{ csrf_token() }}" name="_token">
                                </td>
                            </tr>
                            <tr>
                                <td class="col-md-2">{{ trans('news::view.Created_at') }}</td>
                                <td>{{ $comment->created_at }}</td>
                            </tr>

                            <tr>
                                <td class="col-md-2">
                                </td>
                                <td>
                                    <a class="btn btn-default pull-left margin-right-20" href="{{ route('news::manage.comment.index') }}">{{ trans('news::view.Back') }}</a>
                                    <button class="btn btn-primary pull-left" type="submit">{{ trans('news::view.Submit') }}</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ CoreUrl::asset('asset_news/js/comment_post.js')}}"></script>
@endsection
