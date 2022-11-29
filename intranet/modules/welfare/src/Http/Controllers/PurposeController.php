<?php

namespace Rikkei\Welfare\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Welfare\Model\PurposeEvent;
use URL;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Welfare\Model\Event;
use Illuminate\Support\Facades\Lang;
use Yajra\Datatables\Facades\Datatables;
use Illuminate\Http\Request;

class PurposeController extends Controller
{
    /**
     * @return all item*
     */
    public function index()
    {
        $data = PurposeEvent::all();
        foreach ($data as $val) {
            $val->name = htmlentities($val->name);
        }
        return $data;
    }

    /**
     * @return detail item*
     */
    public function detail(Request $req)
    {

    }

    /**
     * get all common filed
     */
    public function getCommon()
    {

    }

    public function create()
    {
        $valiable = $this->getCommon();
        Breadcrumb::add('welfare', URL::route('welfare::welfare.event.index'));
        Breadcrumb::add('Create GroupEvent');
        return view('welfare::groupEvent.edit',
            $valiable);
    }

    /**
     * edit post
     */
    public function edit($id)
    {
        $valiable = $this->getCommon();
        Breadcrumb::add('welfare', URL::route('welfare::welfare.event.edit', ['id' => $id]));
        Breadcrumb::add('Edit');
        $model = Event::find($id);
        if (!$model) {
            return redirect()->route('welfare::welfare.event.index')->withErrors(Lang::get('welfare::view.No results found'));
        }
        $item = ($model['attributes']);
        $valiable['item'] = $item;
        return view('welfare::event.edit',
            $valiable);
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
                'name' => 'required|unique:wel_purposes,name,NULL,id,deleted_at,NULL|max:255',
        ],$messages);
        if ($v->fails()) {
            return $v->messages();
        }
        $event = new PurposeEvent();
        $event->name = $request->name;
        $event->created_by = Auth::id();
        $event->save();
        $event->name=htmlentities($event->name);
        return $event;
    }

    public function getList()
    {
        $group = PurposeEvent::getAllItem();
        $data = Datatables::of($group)
            ->addColumn('action', function ($data) {
                return '<td style="">
                            <span class="hidden">' . $data->created_at . '</span>
                            <button type="button" class="btn btn-edit edit-modal-groupEvent" data-select="select-purpose-welfare" route="' . route('welfare::welfare.purpose.saveAjax') . '" id="edit-modal-groupEvent" data-id="' . $data->id . '">
                                <span><i class="fa fa-edit"></i></span>
                            </button>
                            <button type="button" class="delete-modal-item btn btn-danger btn-delete" data-select="select-purpose-welfare" modal="modal-delete-purpose" data-id="' . $data->id . '" route="' . route('welfare::welfare.purpose.delete') . '/' . $data->id . '">
                               <span><i class="fa fa-trash"></i></span>
                            </button>
                        </td>';
            })
            ->setRowAttr([
                'numrow' => function ($data) {
                    return $data->id;
                },
                'fillto' => 'event[wel_purpose_id]',
                'class' => 'tr-wel-purpose'
            ])
            ->editColumn('name', function ($data) {
                $html = '<div id="edit' . $data->id . '">' . htmlentities($data->name) . '</div>';
                return $html;
            })
            ->removeColumn('id')
            ->removeColumn('created_at')
            ->make();
        return $data;
    }

    public function saveAjax(Request $request)
    {
        $messages = [
            'max' => trans('welfare::view.Validate max charactor message'),
            'required' => trans('welfare::view.Validate requite message'),
            'unique' => trans('welfare::view.Validate unique message'),
        ];
        $v = Validator::make($request->all(), [
            'name' => 'required||unique:wel_purposes,name,' . $request->id . ',id,deleted_at,NULL|max:255',
        ],$messages);
        if ($v->fails()) {
            return $v->messages();
        }
        $event = PurposeEvent::findOrFail($request->id);
        $event->name = $request->name;
        $event->created_by = Auth::id();
        $event->save();
        $event->name=htmlentities($event->name);
        return $event;
    }

    public function delete($id)
    {
        $welGroup = PurposeEvent::find($id);
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