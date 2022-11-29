<?php

namespace Rikkei\News\Http\Controllers;


use Illuminate\Support\Facades\Lang;
use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Rikkei\Core\View\Menu;
use Rikkei\News\Model\PostComment;
use Rikkei\Team\View\Permission;
use Rikkei\News\View\ViewNews;
use Rikkei\News\View\ManageComment;

class ManageCommentController extends Controller
{
    /**
     * ManageCommentController constructor.
     */
    public function __construct()
    {
        Menu::setActive('admin', 'news');
    }

    /**
     * List comment
     */
    public function index()
    {
        return view('news::manage.comment.index', [
            'titleHeadPage' => Lang::get('news::view.Manage comment'),
            'collectionModel' => PostComment::getAllComment(),
            'allStatus' => PostComment::getAllStatus(),
            'optionTrimWord' => ViewNews::getOptionTrimWord(),
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAllComment(Request $request)
    {
        $data = $request->all('data');
        PostComment::deleteAllComment($data);
        return response()->json(['success'=>"Comments Deleted successfully."]);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function detail($id)
    {
        $comment = PostComment::getDetailComment($id);
        if (!$comment) {
            abort(404);
        }
        return view('news::manage.comment.detail', [
            'comment' => $comment,
            'titleHeadPage' => Lang::get('news::view.Detail comment'),
            'allStatus' => PostComment::getAllStatus()
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeStatusComment(Request $request)
    {
        if (Permission::getInstance()->isAllow('news::manage.comment.changeStatusComment')) {
            $postComment = PostComment::where('id', $request->id)->first();
            if (!$postComment) {
                $message = Lang::get('core::message.Not found item');
            } else {
                $data = [];
                if ($request->status == PostComment::STATUS_COMMENT_ACTIVE) {
                    $data = ['status' => PostComment::STATUS_COMMENT_ACTIVE];
                    if (!is_null($postComment->edit_comment)) {
                        $data = [
                            'edit_comment' => null,
                            'comment' => $postComment->edit_comment
                        ];
                    }
                } else {
                    $data = ['status' => PostComment::STATUS_COMMENT_NOT_ACTIVE];
                    if (!is_null($postComment->edit_comment)) {
                        $data = [
                            'comment' => $postComment->edit_comment,
                            'edit_comment' => null,
                        ];
                    }
                }
                $postComment->update($data);
                $message = Lang::get('core::message.Save success');
            }
            return redirect()->route('news::manage.comment.detail', $request->id)->with(['message' =>  $message]);
        }
    }

    /**
     * @param Request $request
     */
    public function changeStatusAll(Request $request)
    {
        if (Permission::getInstance()->isAllow('news::manage.comment.changeStatusAll')) {
            $listId = $request->data;
            ManageComment::updateCommentNotApproveToApprove($listId);
        }
    }

    /**
     * @param Request $request
     */
    public function unApproveAll(Request $request)
    {
        if (Permission::getInstance()->isAllow('news::manage.comment.unApproveAll')) {
            $listId = $request->data;
            foreach ($listId as $commentId) {
                $comment = PostComment::find($commentId);
                if (is_null($comment->edit_comment)) {
                    if ($comment->status === PostComment::STATUS_COMMENT_ACTIVE) {
                        $comment->status = PostComment::STATUS_COMMENT_NOT_ACTIVE;
                    }
                } else {
                    $comment->status = PostComment::STATUS_COMMENT_NOT_ACTIVE;
                    $comment->comment = $comment->edit_comment;
                    $comment->edit_comment = null;
                }
                $comment->save();
            }
        }
    }
}