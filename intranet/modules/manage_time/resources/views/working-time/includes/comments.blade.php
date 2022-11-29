<div class="box-header with-border">
    <h3 class="box-title">{{ trans('manage_time::view.Notes') }}</h3>
</div>
<ul class="comments-list list-unstyled">
    @foreach ($listComments as $comment)
    <?php
    $nickName = $comment->getNickName();
    ?>
    <li class="comment media">
        <div class="media-left pull-left">
            <img class="avatar" src="{{ $comment->avatar_url }}" alt="{{ $nickName }}">
        </div>
        <div class="media-body">
            <div class="comment-user">
                <strong class="name">{{ $nickName }}</strong> at 
                <span class="date">{{ $comment->created_at }}</span>
            </div>
            <p class="{{ $comment->getClassColor() }}">{!! $comment->getTextStatus() !!} {{ $comment->content }}</p>
        </div>
    </li>
    @endforeach
</ul>
<div class="text-center">
    {!! $listComments->links() !!}
</div>