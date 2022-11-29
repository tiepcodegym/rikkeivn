<?php
namespace Rikkei\Welfare\Http\Controllers;

use Rikkei\Welfare\Model\PartnerGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Response;
use Yajra\Datatables\Datatables;
use Rikkei\Welfare\View\TableView;
use Illuminate\Support\Facades\DB;

class PartnerGroupController extends \Rikkei\Core\Http\Controllers\Controller
{
    /**
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function add(Request $request)
    {
        // validations
        $rules = [
            'name' => 'required|unique:partner_types,name,,id,deleted_at,NULL',
         ];
        $validator = Validator::make ( Input::all (), $rules );

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->getMessageBag()->toArray()]);
        } else {
            /** PartnerGroup $pertnerGroup */
            $partnerGroup = new PartnerGroup();
            $partnerGroup->name = $request->name;

            $partnerGroup->save();
            return response()->json($partnerGroup);
        }

    }

    /**
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        /** PartnerGroup $pertnerGroup */
        $pertnerGroup = PartnerGroup::findOrFail($request->id);
        $rules = [
            'name' => 'required|unique:partner_types,name,' . $request->id . ',id,deleted_at,NULL',
        ];
        $validator = Validator::make ( Input::all (), $rules );

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->getMessageBag()->toArray()]);
        }
        $pertnerGroup->name = $request->name;
        $pertnerGroup->save();

        return response()->json($pertnerGroup);
    }

    /**
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        /** PartnerGroup $pertnerGroup */
        $pertnerGroup = PartnerGroup::findOrFail($request->id);

        $pertnerGroup->delete();

        return response()->json('PartnerGroup');
    }

    /**
     * List Partner Group
     *
     * @return \Illuminate\Support\Facades\Response
     */
    public function getList()
    {
        $group = PartnerGroup::select('id', 'name');
        $data = Datatables::of($group)
            ->addColumn('action', function ($data) {
                return TableView::actionPartnerGroup($data);
            })
            ->setRowClass(function ($data) {
                return 'check-group-partner item'. $data->id;
            })
            ->removeColumn('id')
            ->removeColumn('created_at')
            ->make();
        return $data;
    }
}
