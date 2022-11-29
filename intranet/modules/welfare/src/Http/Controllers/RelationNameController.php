<?php

namespace Rikkei\Welfare\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Rikkei\Team\View\Permission;
use URL;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Welfare\Model\RelationName;

class RelationNameController extends Controller
{

    const SUCCESS = 1;
    const UNSUCCESS = 0;

    /**
     *
     * @return \Illuminate\Support\Facades\Response
     */
    public function index()
    {
        $permision = Permission::getInstance()->isAllow(null, 'welfare::welfare.relation.list');
        if (!$permision) {
            View::viewErrorPermission();
        }
        Breadcrumb::add('welfare', URL::route('welfare::welfare.event.index'));
        Breadcrumb::add('relations', URL::route('welfare::welfare.relation.list'));

        return view('welfare::relationship.index', [
            'relations' => RelationName::orderBy('created_at', 'desc')->get(),
        ]);
    }

    /**
     *
     * @return \Illuminate\Support\Facades\Response
     */
    public function create()
    {
        $permision = Permission::getInstance()->isAllow(null, 'welfare::welfare.relation.create');
        if (!$permision) {
            View::viewErrorPermission();
        }
        Breadcrumb::add('welfare', URL::route('welfare::welfare.event.index'));
        Breadcrumb::add('relations', URL::route('welfare::welfare.relation.list'));

        return view('welfare::relationship.edit');
    }

    /**
     *
     * @param int $id
     * @return \Illuminate\Support\Facades\Response;
     */
    public function edit($id)
    {
        $permision = Permission::getInstance()->isAllow(null, 'welfare::welfare.relation.edit');
        if (!$permision) {
            View::viewErrorPermission();
        }
        Breadcrumb::add('welfare', URL::route('welfare::welfare.event.index'));
        Breadcrumb::add('relations', URL::route('welfare::welfare.relation.list'));

        $relation = RelationName::find($id);

        if (!$relation) {
            return redirect()->route('welfare::welfare.relation.list')->withErrors(Lang::get('team::messages.Not found item.'));
        }

        return view('welfare::relationship.edit', [
            'relation' => $relation,
        ]);
    }

    /**
     *
     * @param Request $request
     * @return \Illuminate\Support\Facades\Response
     */
    public function save(Request $request)
    {
        if ($request->has('submit_delete')) {
            return $this->delete($request);
        }
        $permision = Permission::getInstance()->isAllow(null, 'welfare::welfare.relation.save');
        if (!$permision) {
            View::viewErrorPermission();
        }

        $id = $request->id;
        if (!$id) {
            $model = new RelationName();
            $model->created_by = Auth::id();
        } else {
            $model = RelationName::find($id);
            if (!$model) {
                return response()->json([
                        'success' => self::SUCCESS,
                        'messages' => Lang::get('team::messages.Not found item.'),
                        'url' => route('welfare::welfare.relation.list'),
                ])->withCookie(cookie('msgwarring', Lang::get('team::messages.Not found item.'), 1));
            }
        }
        $rules = [
            'name' => 'required|max:30|unique:relation_names,name,' . $id . ',id,deleted_at,NULL',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => self::UNSUCCESS,
                'messages' => $validator->getMessageBag()->toArray()
                ]);
        }
        $model->name = trim($request->name);
        try {
            $model->saveOrFail();
        } catch (Exception $ex) {
            return redirect()->route('welfare::welfare.event.index')->withErrors($ex);
        }
        $messages = [
            'success' => [
                Lang::get('team::messages.Save data success!'),
            ]
        ];
        return response()->json([
            'success' => self::SUCCESS,
            'messages' => $messages,
            'url' => route('welfare::welfare.relation.edit', ['relation' => $model->id]),
        ]);
    }

    /**
     *
     * @param Request $request
     * @return \Illuminate\Support\Facades\Response
     */
    public function delete(Request $request)
    {
        $permision = Permission::getInstance()->isAllow(null, 'welfare::welfare.relation.delete');
        if (!$permision) {
            View::viewErrorPermission();
        }
        $relations = RelationName::find($request->id);

        if (!$relations) {
            return redirect()->route('welfare::welfare.relation.list')->withErrors(Lang::get('team::messages.Not found item.'));
        }

        $relations->delete();

        $messages = [
                'success'=> [
                    Lang::get('team::messages.Delete item success!'),
                ]
        ];
        if ($request->has('submit_delete')) {
            return response()->json([
                    'success' => self::SUCCESS,
                    'messages' => $messages,
                    'url' => route('welfare::welfare.relation.list'),
            ])->withCookie(cookie('msgdelete', Lang::get('team::messages.Delete item success!'), 1));
        } else {
            return redirect()->route('welfare::welfare.relation.list')->with('messages', $messages);
        }
    }

    /**
     * Check name unique with jquery valitae
     *
     * @param Request $request
     */
    public function checkName(Request $request)
    {
        $permision = Permission::getInstance()->isAllow(null, 'welfare::welfare.relation.check.name');
        if (!$permision) {
            View::viewErrorPermission();
        }
        $requestedName = trim($request->name);
        $requestedId = $request->id;

        if ($requestedId) {
            $relation = RelationName::find($requestedId);
            if (!$relation) {
                return [
                    'status' => false,
                    'msg' => Lang::get('team::messages.Not found item.'),
                ];
            }
            if ($relation->name == $requestedName) {
                echo 'true';
                return;
            }
        }
        $oldName = RelationName::where('name', $requestedName)->first();
        if ($oldName) {
            echo 'false';
        } else {
            echo 'true';
        }
    }

}
