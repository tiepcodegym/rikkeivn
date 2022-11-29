<?php

namespace Rikkei\FinesMoney\Http\Controllers;

use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;
use Maatwebsite\Excel\Facades\Excel;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\Menu;
use Rikkei\FinesMoney\Model\FinesMoney;
use Rikkei\FinesMoney\Model\JobFinesMoney;
use Rikkei\FinesMoney\View\ImportFinesMoney;
use Rikkei\Team\View\Config;

class FinesMoneyController extends Controller
{
    protected $finesMoney;

    public function __construct(FinesMoney $finesMoney)
    {
        parent::_construct();
        $this->finesMoney = $finesMoney;
    }

    public function index(Request $request)
    {
        Breadcrumb::add(trans('fines_money::view.label_profile'));
        Breadcrumb::add(trans('fines_money::view.label_timekeeping'));
        Breadcrumb::add(trans('fines_money::view.fines_money_title'));
        Menu::setActive('Profile');
        $employeeId = auth()->user()->employee_id;
        $fines = $this->finesMoney->calculateFinesMoneyByEmployeeId($employeeId);
        return view('fines_money::index', [
            'types' => $this->finesMoney->getTypes(),
            'status' => $this->finesMoney->getStatus(),
            'collectionModel' => $this->finesMoney->getGridData($employeeId),
            'fines' => $fines
        ]);
    }

    /**
     * List danh sach tien phat
     *
     * @param string $tab
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function listFinesMoney($tab = 'all')
    {
        Breadcrumb::add('Fines Money');
        Breadcrumb::add('List');
        $urlFilter = URL::route('fines-money::fines-money.manage.list', ['tab' => $tab]);
        $data = Form::getFilterData('search', null, $urlFilter);
        $collectionQuery = $this->finesMoney->getDataByDate($data, $tab, $urlFilter, true);
        $resultNotPaginate = $collectionQuery->get();

        $result = [
            'sum' => 0,
            'paid' => 0,
        ];
        foreach ($resultNotPaginate as $item) {
            $result['sum'] += $item->amount;
            if ($item->status_amount == FinesMoney::STATUS_PAID) {
                $result['paid'] += $item->amount;
            }
        }
        $pager = Config::getPagerData($urlFilter);
        $collectionQuery = FinesMoney::pagerCollection($collectionQuery, $pager['limit'], $pager['page']);
        return view('fines_money::manage.index', [
            'collectionModel' => $collectionQuery,
            'result' => $result,
            'status' => $this->finesMoney->getStatus(),
            'currentTab' => $tab,
            'urlFilter' => $urlFilter,
        ]);
    }

    /**
     * Get list history fines money
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function historyFinesMoney()
    {
        Breadcrumb::add('Fines Money');
        Breadcrumb::add('History');
        Menu::setActive('Fines Money');
        $collectionModel = $this->finesMoney->historyFinesMoney();
        return view('fines_money::manage.history', [
            'collectionModel' => $collectionModel,
            'types' => $this->finesMoney->getTypes(),
        ]);
    }

    /**
     * Edit money
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function editMoney(Request $request)
    {
        $data = $request->all();
        $response = [];
        if (!$request->ajax() || !$data['id']) {
            return;
        }
        $editMoney = FinesMoney::findOrFail($data['id']);

        if ($editMoney->type == FinesMoney::TYPE_TURN_OFF) {
            unset($data['amount']);
        }
        DB::beginTransaction();
        try {
            $editMoney = $this->finesMoney->updateFinesMoney($editMoney, $data);
            DB::commit();
            $response['data'] = $editMoney;
            return response()->json($response, 200);
        } catch (\Exception $ex) {
            \Log::info($ex);
            DB::rollback();
            $response['message'] = trans('core::message.Error system');
            return response()->json($response, 400);
        }
    }

    /**
     * Import tien phat TH chua co trong bang cong
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function importFile(Request $request)
    {
        if (!$request->file) {
            return redirect()->back()->withErrors(Lang::get('core::message.Please choose file to upload'));
        }
        $exFile = $request->file->getClientOriginalExtension();
        if (!in_array($exFile, ['xlsx', 'xls'])) {
            return redirect()->back()->withErrors(Lang::get('asset::message.File not invalid'));
        }
        DB::beginTransaction();
        try {
            $excel = Excel::selectSheetsByIndex(0)->load($request->file->getRealPath(), function ($reader) {
                $reader->skipColumns(2);
                $reader->setHeaderRow(4);
                $reader->noHeading();
                $reader->setDateFormat(false);
                $reader->ignoreEmpty(true);
            });

            $arrayExcel = $excel->toArray();
            $heading = (isset($arrayExcel[0]) && $arrayExcel[0]) ? $arrayExcel[0] : [];
            $importFine = new ImportFinesMoney();
            $checkFormat = $importFine->checkHeading($heading);
            if (!$checkFormat) {
                throw new \Exception(Lang::get('fines_money::view.Format file invalid'));
            }
            $importFine->importFileBySheet($arrayExcel);
            DB::commit();
            return redirect()->back()->with('messages', ['success' => [trans('manage_time::message.Update success')]]);
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
            return redirect()->back()->withErrors($ex->getMessage());
        }
    }

    /**
     * Export fines money
     *
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function export(Request $request)
    {
        $urlFilter = !empty($request->tab) ?
            URL::route('fines-money::fines-money.manage.list',['tab'=> $request->tab]) :
            URL::route('fines-money::fines-money.manage.list',['tab'=> 'all']) ;

        $data = !empty(Form::getFilterData('search', null, $urlFilter)) ? Form::getFilterData('search', null, $urlFilter) : [];
        $conditions = array_merge($data, $request->all());
        $collection = $this->finesMoney->getDataByDate($conditions, $request->tab, $urlFilter);
        $results = $this->finesMoney->buildDataExport($collection);
        $fileName = 'Fines_money_'.Carbon::now()->format('Ymd');

        try {
            Excel::create($fileName, function ($excel) use ($results, $fileName) {
                $excel->sheet('Sheet1', function ($sheet) use ($results) {
                    // Format currency column G to VND
                    $sheet->setColumnFormat(array(
                        'G' => FinesMoney::FORMAT_CURRENCY_VND,
                    ));
                    $sheet->fromArray($results, null, 'A1', true, false);
                });
            })->download('xlsx');
        } catch (\Exception $ex) {
            Log::error($ex);
            return response()->json(['success' => false, 'message' => trans('core::message.Error system')]);
        }
    }

    /**
     * Update import fines money
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateImport()
    {
        $file = Input::file('file');
        if (!$file) {
            return redirect()->back()->withErrors(Lang::get('core::message.Please choose file to upload'));
        }
        if (!in_array($file->getClientOriginalExtension(), ['xlsx', 'xls'])) {
            return redirect()->back()->withErrors(Lang::get('asset::message.File not invalid'));
        }

        $importFines = new ImportFinesMoney();
        $titleIndex = $importFines->getHeadingIndexFines();

        DB::beginTransaction();
        try {


            //save file
            $fileName = $importFines->storeFile(null, $file);
            $fileUpload = storage_path('app/'  . $fileName);//get file upload
            $importFines->getFileErrorDayAgo();//remove file error
            if (!file_exists($fileUpload)) {
                return redirect()->back()->withErrors(Lang::get('asset::message.File not exits'));
            }
            $curEmp = auth()->user()->employee_id;
            $excel = Excel::filter('chunk')->noHeading()->load($fileUpload);
            $totalRow = $excel->getTotalRowsOfFile();
            //update count job
            JobFinesMoney::insert([
                'total' => $totalRow,
                'created_by' => $curEmp,
                'file' =>  $fileName,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            $excel->chunk(ImportFinesMoney::CHUNK_ROW, function ($results) use (&$titleIndex, $importFines, $curEmp) {
                    $importFines->importUpdate($results,$titleIndex, $curEmp);
                }, 'fines_money');

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::error($ex);
            return redirect()->back()->withErrors($ex->getMessage());
        }
        return redirect()->route('fines-money::fines-money.manage.list', ['tab' => 'all'])->with('messages', ['success' => [trans('manage_time::message.Update success')]]);
    }
}
