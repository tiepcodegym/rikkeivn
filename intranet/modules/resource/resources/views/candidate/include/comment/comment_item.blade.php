<?php 
use Rikkei\Project\Model\TaskComment;
use Rikkei\Core\View\View;
use Rikkei\Project\View\GeneralProject;
use Rikkei\Team\View\Permission;
 ?>
<div class="item cdd-comment-item">
    <?php
        $content = $item->content;
        $createdBy = $item->name . ' (' . GeneralProject::getNickNameNormal($item->email) . ')';
        $curEmp = Permission::getInstance()->getEmployee();
    ?>
    <span class="author"><strong>{{ $createdBy }}</strong> <i>{{ trans('project::view.at') . ' ' . $item->created_at }}</i></span>
    @if ($curEmp->id == $item->created_by)
        <div style="display: block; float: right;" class="btn-group button-action" id="">
            <button class="dropdown-toggle action-comment" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true"><i class="fa fa-ellipsis-h"></i>
            </button>
            <ul class="dropdown-menu">
                <li>
                    <a href="#" class="edit-comment" data-content="{{ $content }}" data-id="{{ $item->id }}">
                        <span><i class="fa fa-pencil-square-o" aria-hidden="true"></i></span> {{ trans('resource::view.Candidate.Detail.Edit') }}
                    </a>
                </li>
                <li>
                    <a href="#" class="delete-comment" data-id="{{ $item->id }}">
                        <span><i class="fa fa-trash-o" aria-hidden="true"></i></span> &nbsp;{{ trans('resource::view.Candidate.Detail.Delete') }}
                    </a>
                </li>
            </ul>
        </div>
    @endif
    <div style="max-width: 700px;">
    	<p class="comment{{ $item->type == TaskComment::TYPE_COMMENT_FEEDBACK ? ' bg-danger' : '' }}">{!! $content !!}<p>
    </div>
</div>