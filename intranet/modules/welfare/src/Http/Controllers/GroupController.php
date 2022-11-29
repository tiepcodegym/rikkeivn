<?php
namespace Rikkei\Welfare\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Rikkei\Welfare\Model\GroupEvent;
use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
use \Illuminate\Http\Request;
use Yajra\Datatables\Datatables;

class GroupController extends Controller
{
    /**
     * @return all item*
     */
    public function index()
    {
        $data = GroupEvent::all();
        foreach ($data as $val) {
            $val->name = htmlentities($val->name);
        }
        return $data;
    }

    /**
     * @return all item* bind to list
     */
    public function getList()
    {
        $group = GroupEvent::getAllItem();
        $data = Datatables::of($group)
            ->addColumn('action', function ($data) {
                return '<td style="">
                            <span class="hidden">'. $data->created_at .'</span>
                            <button type="button" class="btn btn-edit edit-modal-groupEvent" data-select="select-group-welfare" route="' . route('welfare::welfare.group.saveAjax') . '" id="edit-modal-groupEvent" data-id="' . $data->id . '">
                                <span><i class="fa fa-edit"></i></span>
                            </button>
                            <button type="button" class="delete-modal-item btn btn-danger btn-delete" data-select="select-group-welfare" modal="modal-delete-group" data-id="' . $data->id . '" route="' . route('welfare::welfare.group.delete') . '/' . $data->id . '">
                               <span><i class="fa fa-trash"></i></span>
                            </button>
                        </td>';
            })
            ->setRowAttr([
                'numrow' => function ($data) {
                    return $data->id;
                },
                'fillto' => 'event[welfare_group_id]',
                'class' => 'tr-wel-group'
            ])
            ->editColumn('name', function ($data) {
                $html1 = '<div id="edit' . $data->id . '">' . htmlentities($data->name) . '</div>';
                return $html1;
            })
            ->removeColumn('id')
            ->removeColumn('created_at')
            ->make();
        return $data;
    }

    /**
     * save data
     */
    public function save(Request $request)
    {
        $messages = [
            'max' => trans('welfare::view.Validate max charactor message'),
            'required' => trans('welfare::view.Validate requite message'),
            'unique' => trans('welfare::view.Validate unique message'),
        ];
        $v = Validator::make($request->all(), [
            'name' => 'required|unique:welfare_groups,name,NULL,id,deleted_at,NULL|max:255',
        ], $messages);
        if ($v->fails()) {
            return $v->messages();
        }
        $event = new GroupEvent();
        $event->name = ($request->name);
        $event->created_by = Auth::id();
        $event->save();
        $event->name = htmlentities($event->name);
        return $event;
    }

    public function saveAjax(Request $request)
    {
        $messages = [
            'max' => trans('welfare::view.Validate max charactor message'),
            'required' => trans('welfare::view.Validate requite message'),
            'unique' => trans('welfare::view.Validate unique message'),
        ];
        $v = Validator::make($request->all(), [
            'name' => 'required|unique:welfare_groups,name,' . $request->id . ',id,deleted_at,NULL|max:255',
        ],$messages);
        if ($v->fails()) {
            return $v->messages();
        }
        $event = GroupEvent::findOrFail($request->id);
        $event->name = $request->name;
        $event->created_by = Auth::id();
        $event->save();
        $event->name = htmlentities($event->name);
        return $event;
    }

    /*
     ** delete group event item
     */
    public function delete($id)
    {
        $welGroup = GroupEvent::find($id);
        if (!$welGroup) {
            $messages = [
                'messages' => [
                    Lang::get('team::messages.Not found item.'),
                ]
            ];
        } else {
            $welGroup->delete();
            $messages = [
                'messages' => [
                    Lang::get('team::messages.Delete item success!'),
                ]
            ];
        }

        return $messages;
    }
}