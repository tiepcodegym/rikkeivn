<?php

namespace Rikkei\News\View;

use Rikkei\News\Model\PostComment;

class ManageComment
{

    /**
     * update comment not approve to approve
     *
     * @param array $listId
     */
    public static function updateCommentNotApproveToApprove($listId)
    {
        foreach ($listId as $commentId) {
            $comment = PostComment::find($commentId);
            if (!is_null($comment->edit_comment)) {
                $comment->comment = $comment->edit_comment;
                $comment->edit_comment = null;
            }
            $comment->status = PostComment::STATUS_COMMENT_ACTIVE;
            $comment->approved_by = null;
            $comment->save();
        }
    }
}