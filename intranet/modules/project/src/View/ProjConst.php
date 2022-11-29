<?php

namespace Rikkei\Project\View;

use Rikkei\Project\Model\ProjectMember;

class ProjConst
{
    const KEY_CK_DAYWORK = 'ck-dw';
    
    const DAY_REWARD_PAID = 5;
    
    public static function woStatus()
    {
        return [
            'sApprove' => [
                ProjectMember::STATUS_APPROVED
            ],
            'sDelete' => [
                ProjectMember::STATUS_DRAFT_DELETE,
                ProjectMember::STATUS_SUBMMITED_DELETE,
                ProjectMember::STATUS_REVIEWED_DELETE,
                ProjectMember::STATUS_FEEDBACK_DELETE,
                // status log
                ProjectMember::STATUS_DELETE_APPROVED,
                ProjectMember::STATUS_DELETE_DRAFT_EDIT,
                ProjectMember::STATUS_DELETE_DRAFT,
                ProjectMember::STATUS_DELETE_FEEDBACK_EDIT,
                ProjectMember::STATUS_DELETE_FEEDBACK,
                ProjectMember::STATUS_DELETE,
            ],
            'sAdd' => [
                ProjectMember::STATUS_DRAFT,
                ProjectMember::STATUS_SUBMITTED,
                ProjectMember::STATUS_REVIEWED,
                ProjectMember::STATUS_FEEDBACK,
                // status log
                ProjectMember::STATUS_ADD
            ],
            'sEdit' => [
                ProjectMember::STATUS_DRAFT_EDIT,
                ProjectMember::STATUS_SUBMIITED_EDIT,
                ProjectMember::STATUS_REVIEWED_EDIT,
                ProjectMember::STATUS_FEEDBACK_EDIT,
                // status log
                ProjectMember::STATUS_EDIT,
                ProjectMember::STATUS_EDIT_APPROVED
            ],
        ];
    }
}