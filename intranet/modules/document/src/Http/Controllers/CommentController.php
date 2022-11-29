<?php

namespace Rikkei\Document\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Rikkei\Document\Models\Document;
use Rikkei\Document\Models\DocComment;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Document\Models\File;
use Illuminate\Support\Facades\DB;
use Rikkei\Document\View\DocConst;
use Rikkei\Team\View\Permission;
use Validator;

class CommentController extends Controller
{
    /*
     * save comment
     */
    public function save($docId, Request $request)
    {
        $doc = Document::findOrFail($docId);
        $docPermiss = Document::getDocPermission($doc);
        if (!$docPermiss['view']) {
            CoreView::viewErrorPermission();
        }
        $data = $request->except('_token');
        $maxSize = DocConst::fileMaxSize();
        $valid = Validator::make($data, [
            'content' => 'required_without:comment_file',
            'comment_file' => 'max:'. $maxSize
        ], [
            'content.required_without' => trans('doc::message.required', ['attribute' => 'Comment']),
            'comment_file.max' => trans('doc::message.file_max_size', ['max' => $maxSize])
        ]);
        if ($valid->fails()) {
            return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors($valid->errors());
        }
        $data['doc_id'] = $doc->id;
        $commentFile = $request->file('comment_file');
        DB::beginTransaction();
        try {
            if ($commentFile) {
                if (!in_array($commentFile->getClientOriginalExtension(), ['txt', 'doc', 'docx', 'pdf'])) {
                    return redirect()
                            ->back()
                            ->withInput()
                            ->with('messages', ['errors' => [trans('doc::message.mimes', ['attribute' => 'File comment', 'mimes' => '.txt, .doc, .docx, .pdf'])]]);
                }
                $file = File::insertData($commentFile, null, false, null);
                $data['file_id'] = $file->id;
            }
            $comment = DocComment::insertOrUpdate($data);
            DB::commit();
            return redirect()
                    ->to(route('doc::admin.edit', $docId) . '#comment_' . $comment->id);
        } catch (\Exception $ex) {
            DB::rollback();
            return redirect()
                    ->back()
                    ->withInput()
                    ->with('messages', ['errors' => [trans('doc::message.An error occurred')]]);
        }
    }

    /*
     * delete comment
     */
    public function delete($docId, $commentId)
    {
        $doc = Document::find($docId);
        if (!$doc) {
            return response()->json(['message' => trans('doc::message.Document not found')], 404);
        }
        $docPermiss = Document::getDocPermission($doc);
        if (!$docPermiss['view']) {
            return CoreView::viewErrorPermission();
        }
        $comment = DocComment::find($commentId);
        if (!$comment) {
            return response()->json(['message' => trans('doc::message.Cocument not found')], 404);
        }
        $author = $comment->author;
        if ($author && $author->email != Permission::getInstance()->getEmployee()->email) {
            return CoreView::viewErrorPermission();
        }
        $comment->delete();
        return response()->json(trans('doc::message.Delete successful'));
    }
}
