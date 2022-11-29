<?php
use Rikkei\Team\Model\SkillSheetComment;
use Rikkei\Core\View\View as CoreView;

?>
@if(isset($collectionModel) && count($collectionModel))
    @foreach($collectionModel as $item)
    <div class="item">
        <?php
        if ($item->type == SkillSheetComment::TYPE_FEEDBACK) {
            $content = trans('project::view.Feedback') . ': ' . CoreView::nl2br($item->content);
        } else {
            $content = CoreView::nl2br($item->content);
        }
            $createdBy = $item->name . ' (' . preg_replace('/@.*/', '', $item->email) . ')';
        ?>
        <p class="author"><strong>{{ $createdBy }}</strong> <i>{{ trans('project::view.at') . ' ' . $item->created_at }}</i></p>
        <p class="comment{{ $item->type == SkillSheetComment::TYPE_FEEDBACK ? ' bg-danger' : '' }}">{!! $content !!}</p>
    </div>
    @endforeach
@endif
@include('team::include.pager', ['domainTrans' => 'project', 'isShow' => true])