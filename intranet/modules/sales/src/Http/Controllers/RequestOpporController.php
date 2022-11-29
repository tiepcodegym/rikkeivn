<?php

namespace Rikkei\Sales\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller as Controller;
use Illuminate\Http\Request;
use Rikkei\Resource\Model\Programs;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Sales\Model\ReqOpportunity;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Sales\Model\ReqOpporCv;
use Rikkei\Core\View\View as CoreView;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Illuminate\Support\Facades\Validator;
use Rikkei\Team\View\Permission;
use Carbon\Carbon;
use Excel;

class RequestOpporController extends Controller
{
    /**
     * construct more
     */
    protected function _construct()
    {
        Menu::setActive('sales');
        Breadcrumb::add('Request opportunity' , route('sales::req.list.oppor.index'));
        if (app()->getLocale() == 'ja') {
            app()->setLocale('en');
        }
    }

    public function index()
    {
        $collectionModel = ReqOpportunity::getGridData();
        $programs = Programs::getListOption();
        $roles = ProjectMember::getTypeMember();
        return view('sales::req-oppor.index', compact('collectionModel', 'programs', 'roles'));
    }

    /**
     * Create request page view
     * @return view
     */
    public function edit($id = null)
    {
        return redirect()->route('sales::req.apply.oppor.view', $id);
    }

    /*
     * save request
     */
    public function save(Request $request)
    {
        $isAjax = $request->ajax() || $request->wantsJson();
        $data = $request->all();
        $itemId = isset($data['id']) ? $data['id'] : null;
        $valid = Validator::make($data, [
            'name' => 'required',
            'code' => 'required|unique:' . ReqOpportunity::getTableName() . ',code' . ($itemId ? ',' . $itemId : ''),
            'duedate' => 'required|date_format:Y-m-d',
            'sale_id' => 'required',
            'number_member' => 'required'
        ]);
        if ($valid->fails()) {
            if ($isAjax) {
                return response()->json($valid->messages(), 422);
            }
            return redirect()->back()
                    ->withInput()
                    ->withErrors($valid->errors());
        }
        if ($itemId) {
            if (!ReqOpportunity::permissEdit(ReqOpportunity::find($itemId))) {
                return CoreView::viewErrorPermission();
            }
        }
        DB::beginTransaction();
        try {
            $item = ReqOpportunity::insertOrUpdate($data);
            DB::commit();
            if ($isAjax) {
                $response = [
                    'message' => trans('sales::message.Save data successful!')
                ];
                if (!$itemId) {
                    $response['redirect'] = route('sales::req.apply.oppor.view', ['id' => $item->id]);
                }
                return response()->json($response);
            }
            return redirect()
                    ->route('sales::req.oppor.edit', ['id' => $item->id])
                    ->with('messages', ['success' => [trans('sales::message.Save data successful!')]]);
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
            if ($isAjax) {
                return response()->json(['messages' => ['errors' => [trans('sales::message.Error system, please try again later!')]]], 500);
            }
            return redirect()
                    ->back()
                    ->withInput()
                    ->with('messages', ['errors' => [trans('sales::message.Error system, please try again later!')]]);
        }
    }

    /**
     * view detail and apply member
     */
    public function view($id = null)
    {
        $permissEdit = Permission::getInstance()->isAllow('sales::req.oppor.edit');
        $permissApply = Permission::getInstance()->isAllow('sales::req.apply.oppor.view');
        if ((!$id && !$permissEdit) || (!$permissEdit && !$permissApply)) {
            return CoreView::viewErrorPermission();
        }
        $item = null;
        $itemCode = null;
        if ($id) {
            $item = ReqOpportunity::findOrFail($id);
            $permissEdit = ReqOpportunity::permissEdit($item);
        } else {
            $itemCode = ReqOpportunity::makeCode();
        }
        return view('sales::req-oppor.view', [
            'item' => $item,
            'itemCode' => $itemCode,
            'typeOptions' => Candidate::listTypes(),
            'langLevels' => CoreView::getLangLevelSplit(),
            'permissEdit' => $permissEdit,
            'permissApply' => $permissApply
        ]);
    }

    /*
     * get list cv notes
     */
    public function getListCvNotes($requestId)
    {
        return [
            'cvNotes' => ReqOpporCv::getList($requestId)
        ];
    }

    /**
     * check validate exists field
     * @param Request $request
     * @return type
     */
    public function checkExists(Request $request)
    {
        $itemId = $request->get('id');
        $field = $request->get('field');
        $value = $request->get('value');
        return response()->json(!ReqOpportunity::checkExists($field, $value, $itemId));
    }

    /**
     * delete item
     * @param type $id
     * @return type
     */
    public function delete($id)
    {
        $item = ReqOpportunity::findOrFail($id);
        if (!ReqOpportunity::permissEdit($item)) {
            return CoreView::viewErrorPermission();
        }
        $item->delete();
        return redirect()
                ->back()
                ->with('messages', ['success' => [trans('sales::message.Delete data successful!')]]);
    }

    /**
     * export excel
     * @param type $id
     * @return type
     */
    public function export(Request $request)
    {
        $ids = $request->get('ids');
        if (!$ids) {
            return redirect()->back()->with(['messages' => ['errors' => [trans('sales::message.None item checked')]]]);
        }
        $ids = explode(',', $ids);
        $collection = ReqOpportunity::getGridData($ids, true);
        if (!$collection) {
            return redirect()->back()->with(['messages' => ['errors' => [trans('sales::message.None item found')]]]);
        }

        $fileName = Carbon::now()->format('Ymd') . '_export_opportunity';
        //create excel file
        Excel::create($fileName, function ($excel) use ($collection) {
            $excel->setTitle('Opportunity');
            $excel->sheet('opportunity', function ($sheet) use ($collection) {
                //set row header
                $rowHeader = ['No.', 'Name', 'Sales', 'Number of employees', 'Program languages', 'Deadline', 'Duration', 'Location'];
                $sheet->row(1, $rowHeader);
                //format data type column
                $sheet->setColumnFormat(array(
                    'B' => '@',
                    'C' => '@',
                    'D' => '0',
                    'E' => '0',
                    'F' => '@',
                    'G' => '@',
                    'H' => '@',
                ));
                //set data
                $roles = ProjectMember::getTypeMember();
                foreach ($collection as $order => $item) {
                    $rowData = [
                        $order + 1,
                        $item->name,
                        ucfirst(preg_replace('/@(.*)/', '', $item->sale_name)),
                        $item->number_member . ' ' . (isset($roles[$item->role]) ? $roles[$item->role] : ''),
                        $item->prog_names,
                        $item->duedate,
                        $item->duration,
                        $item->location . ($item->country_name ? ($item->location ? ' - ' : '') . $item->country_name : '')
                    ];
                    $sheet->row($order + 2, $rowData);
                }
                //set customize style
                $sheet->getStyle('A1:H1')->applyFromArray([
                        'fill' => [
                            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => ['rgb' => '004e00']
                        ],
                        'font' => [
                            'color' => ['rgb' => 'ffffff'],
                            'bold' => true
                        ]
                    ]
                );
                //set wrap text
                $sheet->getStyle('A2:F' . ($collection->count() + 1))->getAlignment()->setWrapText(true);
            });
        })->export('xlsx');
    }

    public function getOppor($id)
    {
        $item = ReqOpportunity::find($id);
        if (!$item) {
            return response()->json(trans('sales::message.Not found item'), 404);
        }
        return $item;
    }

    /*
     * save note
     */
    public function saveCvMember(Request $request)
    {
        $reqId = $request->get('req_oppor_id');
        $cvId = $request->get('id');
        $reqOppor = ReqOpportunity::find($reqId);
        if (!$reqId || !$reqOppor) {
            return response()->json(trans('sales::message.Not found item'), 404);
        }
        $data = $request->except(['id']);
        $cvItem = ReqOpporCv::insertOrUpdate($cvId, $data, $reqOppor);
        if (!$cvItem) {
            return response()->json(trans('sales::message.Not found item'), 404);
        }
        $currentUser = \Rikkei\Team\View\Permission::getInstance()->getEmployee();
        $cvItem->name = $currentUser->name;
        $cvItem->account = preg_replace('/@(.*)/', "", $currentUser->email);
        $cvItem->team_names = $currentUser->getRoleAndTeams();
        return [
            'cvItem' => $cvItem,
            'message' => trans('sales::message.Save data successful!')
        ];
    }

    /*
     * delete note
     */
    public function deleteCvMember($cvId)
    {
        $cvItem = ReqOpporCv::find($cvId);
        if (!$cvItem) {
            return response()->json(trans('sales::message.Not found item'), 404);
        }
        $cvItem->delete();
        return [
            'message' => trans('sales::message.Delete data successful!')
        ];
    }

}
