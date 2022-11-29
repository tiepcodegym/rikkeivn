<div>
    @if(isset($collectionModel) && count($collectionModel))
        @foreach($collectionModel as $item)
            @include('resource::candidate.include.comment.comment_item')
        @endforeach
    @endif
    <div class="box-body">
        @include('team::include.pager', ['domainTrans' => 'project', 'isShow' => true])
    </div>
</div>
