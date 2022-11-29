<?php
use Rikkei\Project\Model\TaskComment;
use Rikkei\Core\View\View;
use Rikkei\Project\View\GeneralProject;
use Rikkei\Team\View\Permission;

$curEmp = Permission::getInstance()->getEmployee();
?>

<div class="item">
    <?php
        if ($item->type == TaskComment::TYPE_COMMENT_WO) {
            $content = '<strong>' . trans('project::view.Changed workorder') . ':</strong> ' . View::nl2br($item->content);
        } elseif ($item->type == TaskComment::TYPE_COMMENT_FEEDBACK) {
            $content = trans('project::view.Feedback') . ': ' . View::nl2br($item->content);
        } else {
            $content = View::nl2br($item->content);
        }
        $createdBy = $item->name . ' (' . GeneralProject::getNickNameNormal($item->email) . ')';
    ?>
    <p class="author"><strong>{{ $createdBy }}</strong> <i>{{ trans('project::view.at') . ' ' . $item->created_at }}</i></p>
    @if ($curEmp->id == TaskComment::find($item->id)->created_by)
        <div style="display: block; float: right;" class="btn-group button-action" id="">
            <button class="dropdown-toggle action-comment" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true"><i class="fa fa-ellipsis-h"></i>
            </button>
            <ul class="dropdown-menu width-dropdown">
               <li><a href="#" class="edit-comment" data-content="{{ $content }}" data-id="{{ $item->id }}"><span><i class="fa fa-pencil-square-o" aria-hidden="true"></i></span> Edit</a></li>
               <li><a href="#" class="delete-comment" data-id="{{ $item->id }}" data-token="{!! csrf_token() !!}" data-url="{!! route('project::task.delete.comment') !!}"><span><i class="fa fa-trash-o" aria-hidden="true"></i></span> &nbsp;Del</a></li>
            </ul>
         </div>
    @endif
    <p class="comment{{ $item->type == TaskComment::TYPE_COMMENT_FEEDBACK ? ' bg-danger' : '' }}">{!! $content !!}</p>
</div>