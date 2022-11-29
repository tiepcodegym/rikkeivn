<?php

namespace Rikkei\Document\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Rikkei\Document\Models\DocRequest;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Document\Models\DocHistory;
use Validator;

class RequestController extends Controller
{
    public function _construct()
    {
        Menu::setActive('document');
        Breadcrumb::add(trans('doc::view.Document request'), route('doc::admin.request.index'));
    }

    /**
     * list types
     * @return type
     */
    public function index()
    {
        $collectionModel = DocRequest::getGridData();
        return view('doc::request.index', compact('collectionModel'));
    }

    /**
     * edit type
     * @param type $id
     * @return type
     */
    public function edit($id = null)
    {
        $title = trans('doc::view.Create');
        $item = null;
        $creators = null;
        $histories = collect();
        if ($id) {
            $item = DocRequest::findOrFail($id);
            $title = trans('doc::view.Edit');
            $creators = $item->creators;
            $histories = DocHistory::getByDocument(null, $id);
        }
        $requestPermiss = DocRequest::getPermission($item);
        if (!$requestPermiss['view']) {
            CoreView::viewErrorPermission();
        }
        Breadcrumb::add($title);

        return view(
            'doc::request.edit',
            compact(
                'item',
                'title',
                'creators',
                'requestPermiss',
                'histories'
            )
        );
    }

    /**
     * save/update type
     * @param Request $request
     * @return type
     */
    public function save(Request $request)
    {
        $id = $request->get('id');
        $item = null;
        if ($id) {
            $item = DocRequest::findOrFail($id);
        }
        $docReqPermiss = DocRequest::getPermission($item);
        if (!$docReqPermiss['view']) {
            CoreView::viewErrorPermission();
        }
        $data = $request->except('_token');
        if ($docReqPermiss['edit']) {
            $valid = Validator::make($data, [
                'name' => 'required|unique:'. DocRequest::getTableName() .',name' . ($id ? ','.$id : ''),
                'content' => 'required',
                'creator_ids' => 'required'
            ], [
                'name.required' => trans('doc::message.required', ['attribute' => trans('doc::view.Request name')]),
                'name.unique' => trans('doc::message.unique', ['attribute' => trans('doc::view.Request name')]),
                'content.required' => trans('doc::message.required', ['attribute' => trans('doc::view.Content')]),
                'creator_ids.required' => trans('doc::message.required', ['attribute' => trans('doc::view.Document creator')])
            ]);
            if ($valid->fails()) {
                return redirect()->back()->withInput()->withErrors($valid->errors());
            }
        }

        $message = trans('doc::message.Create successful');
        if ($item) {
            $message = trans('doc::message.Update successful');
        }
        $item = DocRequest::insertOrUpdate($data, $item);
        if (!$item) {
            return redirect()
                ->back()
                ->withInput()
                ->with('messages', ['errors' => [trans('doc::message.An error occurred')]]);
        }
        return redirect()
                ->route('doc::admin.request.edit', $item->id)
                ->withInput()
                ->with('messages', ['success' => [$message]]);
    }

    /*
     * feedback document request
     */
    public function feedback($id, Request $request)
    {
        $valid = Validator::make($request->all(), [
            'feedback_reason' => 'required'
        ]);
        if ($valid->fails()) {
            return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors($valid->errors());
        }
        $item = DocRequest::findOrFail($id);
        $requestPermiss = DocRequest::getPermission($item);
        if (!$requestPermiss['view']) {
            CoreView::viewErrorPermission();
        }
        if (DocRequest::feedbackItem($item, $request->get('feedback_reason'))) {
            return redirect()
                    ->back()
                    ->with('messages', ['success' => [trans('doc::message.Feedback successful')]]);
        }
        return redirect()
                ->back()
                ->withInput()
                ->with('messages', ['errors' => [trans('doc::message.An error occurred')]]);
    }

    /**
     * delete type
     * @param type $id
     * @return type
     */
    public function delete($id)
    {
        $item = DocRequest::findOrFail($id);
        $item->delete();
        return redirect()
                ->back()
                ->with('messages', ['success' => [trans('doc::message.Delete successful')]]);
    }

    public function searchAjax(Request $request)
    {
        return DocRequest::searchAjax($request->get('q'), $request->except('q'));
    }
}
