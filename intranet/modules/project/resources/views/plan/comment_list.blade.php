<?php
use Rikkei\Team\Model\SkillSheetComment;
use Rikkei\Core\View\View as CoreView;

?>
@if(isset($collectionModel) && count($collectionModel))
    @foreach($collectionModel as $item)
        <div class="item">
            <?php
                $content = CoreView::nl2br($item->content);
                $createdBy = $item->name . ' (' . $item->email . ')';
                $content = str_replace("&lt;name-tag&gt;", "<span class='tag-style'>", $content);
                $content = str_replace("&lt;/name-tag&gt;", "</span>", $content);
            ?>
            <p class="author"><strong>{{ $createdBy }}</strong> <i>{{ trans('project::view.at') . ' ' . $item->created_at }}</i></p>
            <p class="comment">{!! $content !!}</p>
        </div>
    @endforeach
@endif
@if(isset($limit))
    @include('project::plan.pager', ['domainTrans' => 'project', 'isShow' => true, 'limit' => $limit])
@else
    @include('project::plan.pager', ['domainTrans' => 'project', 'isShow' => true])
@endif
