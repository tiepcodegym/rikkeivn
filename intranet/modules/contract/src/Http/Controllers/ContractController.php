<?php

namespace Rikkei\Contract\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Rikkei\Contract\Http\Requests\CreateContractImportExcel;
use Rikkei\Contract\Http\Requests\CreateContractRequest;
use Rikkei\Contract\Http\Requests\EditContractRequest;
use Rikkei\Contract\Model\ContractConfirmExpire;
use Rikkei\Contract\Model\ContractModel;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\View;
use Rikkei\Resource\View\getOptions;
use Rikkei\Team\Http\Controllers\ProfileController;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;
use Session;
use URL;

class ContractController extends Controller
{

    const ACCESS_FOLDER = '0777';
    const FOLDER_LOG = 'contract_import_log';

    /**
     * construct
     */
    public function _construct()
    {
        $url = url()->current();
        if (strpos($url, 'manage')) {
            Breadcrumb::add(trans('contract::vi.Contract'), URL::route('contract::manage.contract.index', ['tab' => 'all']));
        }
        Menu::setActive(null, null, 'contract');
    }

    /**
     * Show all contract
     * @param string $tab all||about-to-expire||none
     * @return type
     */
    public function index($tab = 'all')
    {
        $allTypeContract = ContractModel::getAllTypeContract();
        $employeeTable = Employee::getTableName();
        $contractTable = ContractModel::getTableName();
        $dataFilter = Form::getFilterData(null, null, request()->url());
        $dataFilter['except'] = isset($dataFilter['except']) ? $dataFilter['except'] : [];
        $collectionModel = null;
        try {
            $collectionModel = ContractModel::getAllContract($dataFilter, $tab);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
        }
        return view('contract::index', [
            'collectionModel' => $collectionModel,
            'allTypeContract' => $allTypeContract,
            'currentTab' => $tab,
            'employeeTable' => $employeeTable,
            'contractTable' => $contractTable,
        ]);
    }

    public function create()
    {
        Breadcrumb::add(trans('contract::vi.create contract'));
        $allTypeContract = ContractModel::getAllTypeContract();
        return view('contract::create', [
            'collectionModel' => 'listAboutToExpire',
            'allTypeContract' => $allTypeContract,
        ]);
    }

    /**
     * Create add new contract
     * @param CreateContractRequest $request
     * @return type
     */
    public function insert(CreateContractRequest $request)
    {
        $data = $request->all();
        $imployeeInfo = Employee::getEmpById($data['sel_employee_id']);
        try {
            ContractModel::saveContract($data);
        } catch (Exception $ex) {
            Log::error($ex->getTraceAsString());
            return redirect()->back()->withErrors(trans('contract::message.Create contract failed'));
        }
        return redirect()->route('contract::manage.contract.index', ['tab' => 'all'])->with('success', trans('contract::message.Create contract :employee_name succeed', ['employee_name' => $imployeeInfo->name]));
    }

    /**
     * Show view single contract
     * @param int $id contract id
     * @return type
     */
    public function edit($id)
    {
        Breadcrumb::add(trans('contract::view.Edit contract'));
        $allTypeContract = ContractModel::getAllTypeContract();
        $collectionModel = ContractModel::getContractById($id);
        if (!$collectionModel) {
            return abort(404);
        }
        $employeeInfo = $collectionModel->employee;
        $isPermissionEdit = $collectionModel->isContractLast();
        $objConfirmExpire = new ContractConfirmExpire();
        $allTypeContractExpire = $objConfirmExpire->getAllLabelType();
        $bgText = $objConfirmExpire->getbgText();
        return view('contract::update', [
            'collectionModel' => $collectionModel,
            'employeeInfo' => $employeeInfo,
            'allTypeContract' => $allTypeContract,
            'isPermissionEdit' => $isPermissionEdit, //False->Không cho sửa hợp đồng cũ
            'allTypeContractExpire' => $allTypeContractExpire,
            'bgText' => $bgText,
        ]);
    }

    /**
     * Do update contract info
     * @param Rikkei\Contract\Http\Requests\EditContractRequest $request
     * @param int $id contract id
     * @return
     */
    public function update(EditContractRequest $request, $id)
    {
        $data = $request->all();
        try {
            $modelContract = ContractModel::saveContract($data, $id);
            if (!$modelContract) {
                return abort(404);
            }
            $employeeInfo = $modelContract->employee;
        } catch (Exception $ex) {
            $employeeInfo = null;
            Log::error($ex->getTraceAsString());
            return redirect()->back()->withErrors($ex->getMessage());
        }
        return redirect()->route('contract::manage.contract.index', ['tab' => 'all'])->with('success', trans('contract::message.Update contract :employee_name succeed', ['employee_name' => $employeeInfo->name]));
    }

    /**
     * Delete contract
     * @param int $id contract id
     * @return json
     */
    public function delete($id)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        try {
            ContractModel::deleteContract($id);
        } catch (Exception $ex) {
            Log::error($ex->getTraceAsString());
            return response()->json(['message' => trans('contract::message.Delete contract failed.'), 'success' => 0]);
        }
        return response()->json(['message' => trans('contract::message.Delete contact successfully.'), 'success' => 1]);
    }

    /**
     * Show detail contract
     * @param int $id contract id
     * @return type
     */
    public function show($id)
    {
        Breadcrumb::add(trans('contract::view.Detail contract'));
        $allTypeContract = ContractModel::getAllTypeContract();
        $collectionModel = ContractModel::getContractById($id);
        if (!$collectionModel) {
            return abort(404);
        }
        $employeeInfo = $collectionModel->employee;
        $isPermissionEdit = $collectionModel->isContractLast();

        $objConfirmExpire = new ContractConfirmExpire();
        $allTypeContractExpire = $objConfirmExpire->getAllLabelType();
        $bgText = $objConfirmExpire->getbgText();
        return view('contract::single', [
            'collectionModel' => $collectionModel,
            'employeeInfo' => $employeeInfo,
            'allTypeContract' => $allTypeContract,
            'isPermissionEdit' => $isPermissionEdit,
            'allTypeContractExpire' => $allTypeContractExpire,
            'bgText' => $bgText,
        ]);
    }

    /**
     * search employee by ajax
     */
    public function listSearchAjax()
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        return response()->json(Employee::searchAjax(Input::get('q'), [], [getOptions::FAIL_CDD]));
    }

    public function importExcel(Request $requests)
    {
        $data = $requests->file('fileToUpload');
        if (empty($data)) {
            return redirect()->route('contract::manage.contract.index', ['tab' => 'all'])
                ->withErrors(trans('contract::message.Not found item'));
        }
        $nameFileType = $data->getClientOriginalExtension();
        if (!in_array($nameFileType, ['xls', 'xlsx'])) {
            return redirect()->route('contract::manage.contract.index', ['tab' => 'all'])
                ->withErrors(Lang::get('contract::message.File is not formatted correctly [.xls,.xlsx]!'));
        }
        $configIndex = [
            'order' => 'A',
            'employee_email' => 'B',
            'type' => 'C',
            'start_at' => 'D',
            'end_at' => 'E',
            'response' => 'F'
        ];
        $countError = 0;
        $countSuccess = 0;
        try {
            $excel = Excel::load($data->getRealPath(), function ($reader) use ($configIndex, &$countError, &$countSuccess) {
                $doc = $reader->getSheet(0);
                $totalRow = $doc->getHighestRow();

                for ($i = 2; $i <= $totalRow; $i++) {
                    if ((int)$totalRow > 1001) {
                        throw new Exception(trans('contract::message.Limit max import contract support 1000 row'), 9999);
                    }
                    $order = $doc->getCell("{$configIndex['order']}$i")->getValue();
                    if (trim($order) == '') {
                        continue;
                    }
                    $empEmail = $doc->getCell("{$configIndex['employee_email']}$i")->getValue();
                    $empInfo = Employee::getEmpByEmail($empEmail);
                    if (!$empInfo) {
                        //Log error: emp not found
                        $countError++;
                        $doc->setCellValue("{$configIndex['response']}$i", "Địa chỉ email nhân viên không hợp lệ.");
                        continue;
                    }
                    $startAt = $doc->getCell("{$configIndex['start_at']}$i")->getValue();
                    try {
                        $startAt = (gettype($startAt) === 'string') ? \Carbon\Carbon::parse($startAt)->format('d-m-Y') : \PHPExcel_Style_NumberFormat::toFormattedString($startAt, 'DD-MM-YYYY');
                    } catch (Exception $ex) {
                        $countError++;
                        $doc->setCellValue("{$configIndex['response']}$i", 'Thời gian bắt đầu hợp đồng không đúng định dạng');
                        continue;
                    }

                    $endAt = $doc->getCell("{$configIndex['end_at']}$i")->getValue();
                    try {
                        $endAt = (gettype($endAt) === 'string') ? \Carbon\Carbon::parse($endAt)->format('d-m-Y') : \PHPExcel_Style_NumberFormat::toFormattedString($endAt, 'DD-MM-YYYY');
                    } catch (Exception $ex) {
                        $countError++;
                        $doc->setCellValue("{$configIndex['response']}$i", 'Thời gian kết thúc hợp đồng không đúng định dạng');
                        continue;
                    }
                    $dataImport = [
                        'sel_employee_id' => $empInfo->id,
                        'sel_contract_type' => (int) $doc->getCell("{$configIndex['type']}$i")->getValue(),
                        'txt_start_at' => $startAt,
                        'txt_end_at' => $endAt,
                    ];

                    $requestValid = new CreateContractImportExcel();
                    $validator = Validator::make($dataImport, $requestValid->rules($dataImport), $requestValid->messages());
                    $requestValid->extendValidator($validator, $dataImport);

                    if ($validator->fails()) {

                        $countError++;
                        $errors = $validator->errors()->all();
                        $message = is_array($errors) && count($errors) > 0 ? '- ' . implode("\n - ", $errors) : '- ' . $errors;
                        $doc->setCellValue("{$configIndex['response']}$i", $message);
                        continue;
                    }
                    try {
                        ContractModel::saveContract($dataImport);
                        $countSuccess++;
                        $doc->setCellValue("{$configIndex['response']}$i", 'Successfully');
                    } catch (Exception $ex) {
                        $countError++;
                        $doc->setCellValue("{$configIndex['response']}$i", $ex->getMessage());
                        throw $ex;
                    }
                }
            });
        } catch (Exception $ex) {
            Log::error($ex->getTraceAsString());
            if ($ex->getCode() == 9999) {
                return redirect()->route('contract::manage.contract.index', ['tab' => 'all'])
                    ->withErrors($ex->getMessage());
            }
        }
        if (isset($excel)) {
            $fileLog = 'log_' . date('YmdHis');
            @chmod(storage_path(self::FOLDER_LOG), self::ACCESS_FOLDER);
            $excel->setFilename($fileLog)->store('xls', storage_path(self::FOLDER_LOG));
        }

        if ($countError > 0 && $excel) {
            return redirect()->route('contract::manage.contract.index', ['tab' => 'all'])
                ->with('warning', trans('contract::message.Import contract success :successCount and errors :errorCount. File respone :urlReponse', [
                    'successCount' => $countSuccess,
                    'errorCount' => $countError,
                    'urlReponse' => route('contract::manage.contract.download', ['fileName' => $fileLog . '.xls'])
                ]));
        }
        return redirect()->route('contract::manage.contract.index', ['tab' => 'all'])->with('success', trans('contract::message.Import contract successfully'));
    }

    public function histories()
    {
        $scanDir = scandir(storage_path(self::FOLDER_LOG));
        $arrFile = [];
        foreach ($scanDir as $file) {
            if (in_array($file, ['.', '..'])) {
                continue;
            }
            $arrFile[] = [
                'fileName' => $file,
                'created_at' => date("Y-m-d H:i:s", filemtime(storage_path(self::FOLDER_LOG) . DIRECTORY_SEPARATOR . $file))
            ];
        }
        return view('contract::modals.import-history', [
            'arrFile' => $arrFile
        ]);
    }

    public function download($fileName)
    {
        if (trim($fileName) == '') {
            Log::error('File download not found');
            return redirect()->route('contract::manage.contract.index', ['tab' => 'all'])->withErrors(trans('contract::message.Download file error'));
        }
        $fileName = str_replace('.tmp', '.xls', $fileName);
        try {
            @chmod(storage_path(self::FOLDER_LOG) . DIRECTORY_SEPARATOR . $fileName, self::ACCESS_FOLDER);
            Excel::load(storage_path(self::FOLDER_LOG) . DIRECTORY_SEPARATOR . $fileName)->download('xls');
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return redirect()->route('contract::manage.contract.index', ['tab' => 'all'])->withErrors(trans('contract::message.Download file error'));
        }
    }

    public function pushContractToEmp()
    {
        $id = Input::get('id');
        try {
            ContractModel::pubToProfile($id);
        } catch (Exception $ex) {
            Log::error($ex->getTraceAsString());
            return response()->json(['success' => false, 'message' => $ex->getMessage()]);
        }
        return response()->json(['success' => true, 'message' => trans('contract::message.Synchronize contract to profile successfully')]);
    }

    public function downloadFormatFile()
    {
        $fileName = 'File format';
        $teamCode = Team::where('code', '!=', '')->select('name', 'code')->get();
        try {
            Excel::create($fileName, function ($excel) use ($teamCode) {
                $excel->sheet('Sheet1', function ($sheet) {
                    //set row header
                    $rowHeader = [
                        'STT',
                        'Email nhân viên',
                        'Mã loại hợp đồng',
                        'Thời gian bắt đầu hợp đồng',
                        'Thời gian kết thúc hợp đồng'
                    ];
                    $sheet->row(1, $rowHeader);

                    //format data
                    $rowData = [
                        '1', 'email_example@rikkeisoft.com', '4', '20-09-2019', '20-12-2020'
                    ];
                    $sheet->row(2, $rowData);
                });
                $excel->sheet('List Team Code', function ($sheet) use ($teamCode) {
                    $rowHeader = ['No.', 'Team name', 'Team Code'];
                    $stt = 0;
                    $sheet->row(1, $rowHeader);
                    foreach ($teamCode as $item) {
                        $stt = $stt + 1;
                        $rowData = [
                            $stt, $item->name, $item->code
                        ];
                        $sheet->row($stt + 1, $rowData);
                    }
                });
            })->download('xlsx');
        } catch (Exception $exception) {
            Log::error($exception->getTraceAsString());
            return response()->json(['success' => false, 'message' => $exception->getMessage()]);
        }
    }

    /**
     * Export Contract
     * @param Request $request
     * @param $tab
     * @return \Illuminate\Http\RedirectResponse
     */
    public function export(Request $request, $tab)
    {
        $dataFilter = array_filter($request->input('filter'));
        $dataFilter['except'] = isset($dataFilter['except']) ? $dataFilter['except'] : [];

        try {
            $data = ContractModel::export($dataFilter, $tab);

            $fileName = trans('contract::view.file_name_contract_list');
            if ($tab == 'about-to-expire') {
                $fileName = trans('contract::view.file_name_contract_list_expire');
            }

            if ($tab == 'not-yet-extended') {
                $fileName = trans('contract::view.contract_list_not_yet_extended');
            }

            Excel::create($fileName, function ($excel) use ($data) {

                $excel->sheet('Sheet 1', function ($sheet) use ($data) {

                    $sheet->fromArray($data, null, 'A1', false, false);

                });

            })->download('xlsx');

        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            Session::flash('messages', [
                    'errors' => [
                        trans('contract::view.export_error'),
                    ]
                ]
            );

            return back()->withInput();
        }
    }

    /**
     * get list all contract employee
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function contract()
    {
        Breadcrumb::add('My Profile', URL::route('team::member.profile.index', ['employeeId' => Auth()->id()]));
        Breadcrumb::add(trans('contract::view.file_name_contract_list'));
        $objConfirmExpire = new ContractConfirmExpire();

        $listContract = ContractModel::getContractByEmpId(Auth()->id());
        $allTypeContract = ContractModel::getAllTypeContract();
        $allTypeContractExpire = $objConfirmExpire->getAllLabelType();
        $bgText = $objConfirmExpire->getbgText();

        $param = [
            'listContract' => $listContract,
            'allTypeContract' => $allTypeContract,
            'allTypeContractExpire' => $allTypeContractExpire,
            'bgText' => $bgText,
            'user' => Employee::findOrFail(Auth()->id()) //name auth <> name employee (name profile of employee)
        ];
        return view('contract::profile_list', $param);
    }

    /**
     * update confirm contract when has expired
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateConfirm(Request $request)
    {
        $valid = Validator::make($request->all(),[
            'id' => 'required',
            'cat' => 'required',
        ]);
        if ($valid->fails()) {
            return redirect()->back()->withInput()->withErrors($valid->errors());
        }
        $objConfirmExpire = new ContractConfirmExpire();
        $data = $objConfirmExpire->getConfirmByContactId($request->id);
        if (!$data) {
            return redirect()->back()->withInput()->withErrors(trans('contract::message.The contract has ended'));
        }
        try {
            $data->update([
                'type' => $request->cat,
                'note' => $request->note
            ]);
            return redirect()->back()->with('success', trans('contract::message.Update successfully'));
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return redirect()->back()->withInput()->withErrors($ex->getMessage());
        }
    }

    /**
     * get list all contract employee
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function contractByEmpId($id)
    {
        if (Auth()->id() == $id) {
            return $this->contract();
        }

        Breadcrumb::reset();
        $objConfirmExpire = new ContractConfirmExpire();
        $profile = new ProfileController();
        $profile->preExec($id);
        if (!$profile->isScopeViewProfile()) {
            View::viewErrorPermission();
        }
        $listContract = ContractModel::getContractByEmpId($id);
        $allTypeContract = ContractModel::getAllTypeContract();
        $allTypeContractExpire = $objConfirmExpire->getAllLabelType();
        $bgText = $objConfirmExpire->getbgText();

        Breadcrumb::add('My Profile', URL::route('team::member.profile.index', ['employeeId' => Auth()->id()]));
        Breadcrumb::add(trans('contract::view.file_name_contract_list'));
        $param = [
            'listContract' => $listContract,
            'allTypeContract' => $allTypeContract,
            'allTypeContractExpire' => $allTypeContractExpire,
            'bgText' => $bgText,
            'user' => $profile->getEmployee(),
        ];
        return view('contract::profile_list_employee', $param);
    }
}
