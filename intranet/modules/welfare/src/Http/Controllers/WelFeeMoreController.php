<?php
namespace Rikkei\Welfare\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Rikkei\Welfare\Model\WelEmployeeAttachs;
use Rikkei\Welfare\Model\WelFeeMore;
use Yajra\Datatables\Datatables;
use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WelFeeMoreController extends Controller
{
    public function getBasic()
    {
        return view('datatables.eloquent.basic');
    }

    public function getBasicData($id = '')
    {
        if ($id != '') {
            $users = WelFeeMore::getGridData($id);
            $data = Datatables::of($users)
                ->addColumn('action', function ($data) {
                    return '<td style="">
                                    <button type="button"id="btn_wel_fee_more_update" class="btn btn-info btn-edit btn_wel_fee_more_update" route="' . route('welfare::welfare.WelFreMore.save') . '" data-id="' . $data->id . '">
                                        <span><i class="fa fa-edit"></i></span>
                                    </button>
                                    <button type="button" class="delete-modal-item btn btn-danger btn-delete" modal="modal-delete-wel-fee-more" data-id="' . $data->id . '" route="' . route('welfare::welfare.WelFreMore.delete') . '/' . $data->id . '">
                                       <span><i class="fa fa-trash"></i></span>
                                    </button>
                                   </td>';
                })
                ->setRowAttr([
                    'wel_id' => function ($data) {
                        return $data->wel_id;
                    },
                    'id' => function ($data) {
                        return $data->id;
                    }
                ])
                ->editColumn('cost', '{{number_format($cost)}}')
                ->removeColumn('wel_id')
                ->removeColumn('id')
                ->make();
            return $data;
        }
        return 'new';
    }

    public function saveAjax(Request $request)
    {
        if (isset($request->id) && $request->id != '') {
            $welfremore = WelFeeMore::findOrFail($request->id);
        } else {
            $welfremore = new WelFeeMore();
        }
        $rules = [
            'Extra_payments_name' => 'required|unique:wel_fee_more,name,'.$request->id.',id,deleted_at,NULL,wel_id,'.$request->wel_id,
            'Extra_payments_budget' => 'required',
            'wel_id' => 'required',
        ];
        $messages = [
            'required' => Lang::get('core::message.This field is required'),
            'Extra_payments_name.unique' => Lang::get('welfare::view.Validate unique message'),
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return response()->json([
                    'success' => 0,
                    'messages' => $validator->messages()->toArray()
            ]);
        }
        $welfremore->wel_id = $request->wel_id;
        $welfremore->name = htmlentities($request->Extra_payments_name);
        $welfremore->source = htmlentities($request->Extra_payments_src);
        $welfremore->cost = filter_var($request->Extra_payments_budget, FILTER_SANITIZE_NUMBER_INT);
        $welfremore->created_by = Auth::id();
        $welfremore->save();
        WelFeeMore::updateFeeActual($welfremore->wel_id, $welfremore->cost, true);
        return response()->json([
            'status' => 'true',
            'cost' => $welfremore->cost,
        ]);
    }

    public function delete($id)
    {
        $welGroup = WelFeeMore::find($id);
        WelFeeMore::updateFeeActual($welGroup->wel_id, $welGroup->cost, false);
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
                ],
                'cost' => $welGroup->cost
            ];
        }

        return $messages;
    }
}