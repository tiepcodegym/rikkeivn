<?php

namespace Rikkei\Me\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Rikkei\Me\Model\Comment as MeComment;
use Validator;

class CommentController extends Controller
{
    /*
     * get attribute comment of evaluation
     */
    public function getAttributeComments(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'eval_id' => 'required',
            'comment_type' => 'required',
        ]);
        if ($valid->fails()) {
            return response()->json(trans('me::view.Invalid data'), 422);
        }
        $evalId = $request->get('eval_id');
        $attrId = $request->get('attr_id');
        $commentType = $request->get('comment_type');
        if ($commentType == MeComment::TYPE_NOTE) {
            $attrId = null;
        }
        return MeComment::getInstance()->getComments($evalId, $attrId);
    }

    /*
     * add attribute comment
     */
    public function addComment(Request $request)
    {
        return (new \Rikkei\Project\Http\Controllers\MeEvalController())->addComment($request);
    }

    /*
     * delete attribute comment
     */
    public function deleteComment($id, Request $request)
    {
        return (new \Rikkei\Project\Http\Controllers\MeEvalController())->removeComment($id, $request);
    }

    public function getEvalComments(Request $request)
    {
        $evalId = $request->get('id');
        if (!$evalId) {
            return response()->json(['message' => trans('me::view.Not found item')], 404);
        }
        return MeComment::getInstance()->getEvalComments($evalId);
    }

    /*
     * add list attribute comment
     */
    public function addListComment(Request $request)
    {
        $dataId = [];
        $arrAttr = $request->data;
        $keyNhanXet = 'nhanxet';
        foreach($arrAttr as $key => $val) {
            if (!empty($val)) {
                $ex = explode("_", $key);
                if (isset($ex[1]) && $ex[1]) {
                    $dataId[$ex[1]] = $val;
                } else {
                    $dataId[$keyNhanXet] = $val;
                }
            }
        }
        if (!count($dataId)) {
            return [];
        }
        $dataResults = [];
        foreach($dataId as $attrId => $val) {
            if ($attrId == $keyNhanXet) {
                $data = [
                    'eval_ids' => $request->eval_ids,
                    'return_item' => 1,
                    'attr_id' => '',
                    'comment_text' => $val,
                    'comment_type' => 2,
                ];
            } else {
                $data = [
                    'eval_ids' => $request->eval_ids,
                    'return_item' => 1,
                    'attr_id' => $attrId,
                    'comment_text' => $val,
                ];
            }
            $requestN = new Request($data);
            $results = (new \Rikkei\Project\Http\Controllers\MeEvalController())->addComment($requestN);
            if (is_array($results)) {
                $dataResults = array_merge($dataResults, $results);
            } else {
                return $results;
            }
        }
        return $dataResults;
    }
}
