<?php
use Rikkei\Document\View\DocConst;

$currentUser = auth()->user();
?>

<div class="box box-primary box-solid" id="comment_box">
    <div class="box-header with-border">
        <h3 class="box-title">{{ trans('doc::view.Comment') }}</h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
        </div>
    </div>
    <div class="box-body">
        
        {!! Form::open([
            'method' => 'post',
            'route' => ['doc::admin.comment.save', $item->id],
            'files' => true,
            'class' => 'no-validate',
            'id' => 'comment_form'
        ]) !!}
            <textarea class="form-control margin-bottom-5 noresize" rows="3" name="content">{{ old('content') }}</textarea>
            <div class="text-right">
                <span>(txt, doc, docx, pdf file)</span>
                <input type="file" name="comment_file" accept=".txt,.doc,.docx,.pdf" style="display: inline-block;">
                <button type="submit" class="btn btn-primary">{{ trans('doc::view.Comment') }}</button>
            </div>
        {!! Form::close() !!}
        
        @if (!$commentsList->isEmpty())
        <hr style="margin-top: 5px;" />
        <div class="comments-list">
            @foreach ($commentsList as $comment)
            <div class="comment-item" id="comment_{{ $comment->id }}">
                <div class="comment-author margin-bottom-5">
                    <strong>{{ DocConst::getAccount($comment->email) }} </strong>
                    {{ $comment->type == DocConst::COMMENT_TYPE_FEEDBACK ? 'feedbacked ' : ' ' }}
                    at <span>{{ $comment->created_at }}</span>
                </div>
                <div class="comment-content ws-pre-line el-short-content
                     {{ $comment->type == DocConst::COMMENT_TYPE_FEEDBACK ? ' text-red' : '' }}">{{ $comment->content }}</div>
                @if ($comment->file_id)
                <div class="comment-file">
                    <i class="fa fa-file-text"></i> <a href="{{ DocConst::getFileSrc($comment->file_url, false) }}">{{ $comment->file_name }}</a>
                </div>
                @endif
                @if ($comment->email == $currentUser->email)
                <div class="comment-action">
                    <button type="button" class="btn btn-danger btn-sm btn-del-comment" 
                            data-url="{{ route('doc::admin.comment.delete', ['docId' => $item->id, 'id' => $comment->id]) }}">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @endif
        
        <div class="history-paginate text-center">
            {!! $commentsList->links() !!}
        </div>
        
    </div>
</div>