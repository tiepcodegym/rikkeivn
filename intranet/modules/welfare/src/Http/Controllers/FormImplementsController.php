<?php
namespace Rikkei\Welfare\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Rikkei\Welfare\Model\FormImplements;
use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
use Yajra\Datatables\Datatables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FormImplementsController extends Controller
{
    /**
     * @return all item*
     */
    public function index()
    {
        return FormImplements::all();
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
            'name' => 'required||unique:wel_form_implements,name,' . $request->id . ',id,deleted_at,NULL|max:255',
        ],$messages);
        if ($v->fails()) {
            return $v->messages();
        }
        $event = new FormImplements();
        $event->name = $request->name;
        $event->created_by = Auth::id();
        $event->save();
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
            'name' => 'required||unique:wel_form_implements,name,' . $request->id . ',id,deleted_at,NULL|max:255',
        ],$messages);
        if ($v->fails()) {
            return $v->messages();
        }
        $event = FormImplements::findOrFail($request->id);
        $event->name = $request->name;
        $event->created_by = Auth::id();
        $event->save();
        return $event;
    }


    /*
     * get list item bind to datatable
     */
    public function getList()
    {
        $group = FormImplements::getAllItem();
        $data = Datatables::of($group)
            ->addColumn('action', function ($data) {
                return '<td style="">
                                    <button type="button" class="btn btn-edit edit-modal-groupEvent" route="' . route('welfare::welfare.formImplements.saveAjax'). '" id="edit-modal-groupEvent" data-id="' . $data->id . '" data-select="select-formImplements">
                                        <span><i class="fa fa-edit"></i></span>
                                    </button>
                                    <button type="button" class="delete-modal-item btn btn-danger btn-delete" modal="modal-delete-formImplement" data-id="' . $data->id . '" route="' . route('welfare::welfare.formImplements.delete') . '/' . $data->id . '" data-select="select-formImplements">
                                       <span><i class="fa fa-trash"></i></span>
                                    </button>
                                </td>';
            })
            ->setRowAttr([
                'numrow' => function ($data) {
                    return $data->id;
                },
                'fillto' => 'event[wel_form_imp_id]'
            ])
            ->editColumn('name', function ($data) {
                $html1 = '<div id="edit' . $data->id . '">' . $data->name . '</div>';
                return $html1;
            })
            ->removeColumn('id')
            ->make();
        return $data;
    }
    /*
    ** delete event formimplement  item
    */
    public function delete($id)
    {
        $welGroup = FormImplements::find($id);
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