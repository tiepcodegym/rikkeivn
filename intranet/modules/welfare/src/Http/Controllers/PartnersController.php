<?php
namespace Rikkei\Welfare\Http\Controllers;

use Rikkei\Welfare\Model\Partners;
use Illuminate\Http\Request;
use Response;
use Rikkei\Welfare\View\PartnerList;
use Illuminate\Support\Facades\Validator;
use Yajra\Datatables\Datatables;
use Rikkei\Welfare\View\TableView;
use Carbon\Carbon;

class PartnersController extends \Rikkei\Core\Http\Controllers\Controller
{
    /**
     *
     * @return \Illuminate\Support\Facades\Response
     */
    public function index()
    {
        return view('welfare::partner.index');
    }

    /**
     * Send code Partners for form create partners
     *
     * @return \Illuminate\Support\Facades\Response
     */
    public function create()
    {
        $lastIdPartner = Partners::withTrashed()->select('id')->orderBy('id', 'desc')->first();
        $codePartner = (isset($lastIdPartner) ? $lastIdPartner->id + 1 : 1);
        $codePartner = 'NCC000'.$codePartner;

        return response()->json($codePartner);
    }

    /**
     * Add information of Partners
     *
     * @param Request $request
     * @return \Illuminate\Support\Facades\Response
     */
    public function add(Request $request)
    {
        if(!$request->ajax()){
            return redirect('/');
        }

        $isPartner = false;
        $req = $request->attribute;
        $attr = array_merge($req, ['email' => $req['part_email']]);
        $partner = new Partners();

        $rules = [
            'name' => 'required|max:50|unique:partners,name,' . $attr['isPartner'] . ',id,deleted_at,NULL',
            'code' => 'required',
            'partner_type_id' => 'required|exists:partner_types,id',
        ];

        $validator = Validator::make ( $request->attribute, $rules );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->getMessageBag()->toArray()]);
        }

        if (isset($attr['isPartner']) && $attr['isPartner']) {
            /* @var Partners $pertner */
            $partner = Partners::findOrFail($attr['isPartner']);
            $isPartner = true;
        }

        $partner->fill($attr);
        $partner->rep_card_id_date = $req['rep_card_id_date'] == '' ? Null : $req['rep_card_id_date'];
        $partner->save();

        $response = [
            'isPartner' => $isPartner,
            'partner' => PartnerList::getTrTableHtml($partner),
            'id' => $partner->id,
            'name' => $partner->name,
        ];

        return response()->json($response);

    }

    /**
     * Send information of Partners for Form edit partners
     *
     * @param Request $request
     * @return \Illuminate\Support\Facades\Response
     */
    public function edit(Request $request)
    {
        if(!$request->ajax()){
            return redirect('/');
        }

        /** PartnerGroup $pertnerGroup */
        $pertner = Partners::find($request->id)->toArray();

        $response = array_merge($pertner, ['part_email'  => $pertner['email']]);

        return response()->json($response);
    }

    /**
     * Delete Partners
     *
     * @param Request $request
     * @return \Illuminate\Support\Facades\Response
     */
    public function delete(Request $request)
    {
        /** PartnerGroup $pertnerGroup */
        $pertner = Partners::find($request->id);

        $pertner->delete();

        return response()->json('Partner');
    }

    /**
     * List Partner use DataTable
     *
     * @return \Illuminate\Support\Facades\Response
     */
    public function getList()
    {
        $partners = Partners::select('id', 'name', 'email', 'phone', 'address', 'website', 'created_at');
        $data = Datatables::of($partners)
            ->addColumn('action', function ($data) {
                return TableView::actionPartner($data);
            })
            ->setRowClass(function ($data) {
                return 'item'. $data->id ;
            })
            ->removeColumn('id')
            ->removeColumn('created_at')
            ->make();
        return $data;
    }
}
