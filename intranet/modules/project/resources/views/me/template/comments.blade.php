<div class="dropdown me_comment_box"
     data-eval="{{ $item_id }}"
     data-attr="{{ $attr_id }}"
     data-project="{{ $project_id }}"
     @if (isset($comment_type))
     data-comment-type="{{ $comment_type }}"
     @endif
     data-leader="{{ (isset($is_leader) && $is_leader) ? 1 : 0 }}"
     data-staff="{{ (isset($is_staff) && $is_staff) ? 1 : 0 }}">
    <button type="button" class="close" ><span>&times;</span></button>
    <h4 class="me_comment_title">
        @if (isset($comment_type))
        {{ trans('project::me.Note') }}
        @else
        {{ trans('project::me.Comments') }}
        @endif
    </h4>
    <div class="me_comment_form">
        <div class="_loading"><i class="fa fa-spin fa-refresh"></i></div>
        <div class="me_comments_list"></div>
        <div class="text-center"><a href="#" class="_comment_loadmore hidden">{{trans('project::me.Load more')}}</a></div>
        <div class="media _comment_form">
            <div class="media-body">
                <h4 class="media-heading">{{ $user->name }}</h4>
            </div>
        </div>
        <div class="input-group">
            <textarea type="text" class="me_comment_text form-control resize-none" rows="1"></textarea>
            <span class="input-group-btn"><button type="submit" class="me_comment_submit btn btn-primary">{{trans('project::me.Add')}}</button></span>
        </div>
    </div>
</div>
<!--<div class="me_arrow"></div>-->

