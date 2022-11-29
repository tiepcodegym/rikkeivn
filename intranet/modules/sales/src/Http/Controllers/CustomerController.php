<?php

namespace Rikkei\Sales\Http\Controllers;

use Exception;
use Rikkei\Core\Http\Controllers\Controller as Controller;
use Illuminate\Http\Request;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Team\View\Permission;
use Illuminate\Support\Facades\Validator;
use Rikkei\Sales\Model\Customer;
use Lang;
use Rikkei\Core\View\Menu;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Rikkei\Sales\Model\Company;
use Illuminate\Support\Facades\URL;
use Maatwebsite\Excel\Facades\Excel;
use Rikkei\Sales\Http\Requests\CreateCustomerImportExcel;
use Yajra\Datatables\Datatables;
use Rikkei\Project\Model\Project;
use Rikkei\Sales\View\CustomerHelp;

class CustomerController extends Controller {
    
    const ACCESS_FOLDER = '0777';
    const FOLDER_LOG = 'customer_import_log';

    /**
     * construct more
     */
    protected function _construct()
    {
        Menu::setActive('sales');
    }
    
    /**
     * Create customer page view
     * @return view
     */
    public function create() {
        if (! Permission::getInstance()->isAllow()) {
            echo view('errors.permission');
            exit;
        }
        Breadcrumb::add(Lang::get('sales::view.Customer list'), URL::route('sales::customer.list'));
        Breadcrumb::add(Lang::get('sales::view.Create customer'));
        $companies = Company::all();
        $customer = new Customer();
        return view('sales::customer.create', [ 
            'companies' => $companies,
            'customer' => $customer,
            'hasPermissionEdit' => true,
            'hasPermissionView' => true,
        ]);
    }

    /*
     * store customer
     */
    public function store(Request $request) {
        if (! Permission::getInstance()->isAllow()) {
            echo view('errors.permission');
            exit;
        }
        $data = $request->all();
        $data = array_map('trim', $data);
        if ($request->file('image')) {
            $data['image'] = $request->file('image');
        }
        $messages = [
            'name.required' => Lang::get('sales::view.Customer name field is required'),
            'name.max' => Lang::get('sales::view.Customer name field not be greater than :number characters', ['number'=> 255]),
            'name_jp.max' => Lang::get('sales::view.Customer name jp field not be greater than :number characters', ['number'=> 255]),
            'email.required' => Lang::get('sales::view.Customer email field is required'),
            'email.unique' => Lang::get('sales::view.The value of email field must be unique'),
            'email.email' => Lang::get('sales::view.Customer email must be email'),
            'email.max' => Lang::get('sales::view.Customer email field no be greater than :number characters', ['number' => 100]),
            'phone.max' => Lang::get('sales::view.Customer phone field not be greater than :number characters', ['number' => 100]),
            'skype.max' => Lang::get('sales::view.Customer skype field not be greater than :number characters', ['number' => 45]),
            'chatwork.max' => Lang::get('sales::view.Customer chatwork field not be greater than :number characters', ['number' => 45]),
            'image.image' => Lang::get('sales::view.Customer avatar must be format image'),
            'company_id.requried' => Lang::get('sales::view.Company name field is required'),
        ];
        $rules = [
            'name' => 'required|max:255',
            'name_jp' => 'max:255',
            'company_id' => 'required'
        ];

        $validator = Validator::make($data, $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()
                        ->withErrors($validator)
                        ->withInput();
        }
        if (isset($data['customer_id']) && $data['customer_id']) {
            $customer = Customer::getCustomerById($data['customer_id']);
            if (!$customer) {
                return redirect()->route('sales::customer.list')->withErrors(Lang::get('sales::message.Not found item.'));
            }

            //check permission edit
            $customerHelp = new CustomerHelp();
            $hasPermissionEdit = $customerHelp->hasPermissionEdit($customer);
            $hasPermissionView = $customerHelp->hasPermissionView($customer);
            if (!$hasPermissionEdit && !$hasPermissionView) {
                echo view('errors.permission');
                exit;
            }
            //if has only permission edit note then unset other data
            if (!$hasPermissionEdit) {
                unset($data['name']);
                unset($data['name_ja']);
                unset($data['company_id']);
            }
        } else {
            $customer = new Customer();
            $customer->created_by = Permission::getInstance()->getEmployee()->id;
        }
        $customer->fill($data);

        $customer->save();
        if(isset($data['customer_id']) && $data['customer_id']) {
            $msg = Lang::get('sales::message.Update customer success');
        } else {
            $msg = Lang::get('sales::message.Create customer success');
        }
        $messages = [
                'success'=> [
                    $msg,
                ]
        ];
        return redirect()->route('sales::customer.edit', ['id' => $customer->id])->with('messages', $messages);
    }

    /*
     * list customer
     */
    public function lists() {
        if (! Permission::getInstance()->isAllow()) {
            echo view('errors.permission');
            exit;
        }
        Breadcrumb::add(Lang::get('sales::view.Customer list'));

        //get project by permission project dashboard
        $projectModel = new Project();
        $projects = $projectModel->getProjectsJoined();
        $projectIds = $projects->lists('project_id')->toArray();
        $customerIds = $projects->lists('cust_contact_id')->toArray();

        //get customers list
        $allCustomer = Customer::getGridData($projectIds, $customerIds);
        
        return view('sales::customer.index', compact(['allCustomer']));
    }

    /*
     * edit customer
     * @param int 
     * @return view
     */
    public function edit($id) {
        if (! Permission::getInstance()->isAllow()) {
            echo view('errors.permission');
            exit;
        }
        $customer = Customer::getCustomerWithCompanyName($id);
        if (! $customer) {
            return redirect()->route('sales::customer.list')->withErrors(Lang::get('sales::message.Not found item.'));
        }

        $customerHelp = new CustomerHelp();
        $hasPermissionEdit = $customerHelp->hasPermissionEdit($customer);
        $hasPermissionView = $customerHelp->hasPermissionView($customer);
        if (!$hasPermissionEdit && !$hasPermissionView) {
            echo view('errors.permission');
            exit;
        }

        Breadcrumb::add(Lang::get('sales::view.Customer list'), URL::route('sales::customer.list'));
        Breadcrumb::add(Lang::get('sales::view.Edit customer'));
        $companies = Company::all();
        return view('sales::customer.create', [
            'customer' => $customer,
            'companies' => $companies,
            'hasPermissionEdit' => $hasPermissionEdit,
            'hasPermissionView' => $hasPermissionView,
        ]);
    }
    
    /*
     * delete customer
     */
    public function delete()
    {
        if (! Permission::getInstance()->isAllow()) {
            echo view('errors.permission');
            exit;
        }
        $id = Input::get('id');
        $customer = Customer::getCustomerById($id);
        if (! $customer) {
            return redirect()->route('sales::customer.list')->withErrors(Lang::get('team::messages.Not found item.'));
        }
        $customer->delete();
        $messages = [
            'success'=> [
                Lang::get('team::messages.Delete item success!'),
            ]
        ];
        return redirect()->route('sales::customer.list')->with('messages', $messages);
    }

    /**
     * check exists customer
     * @param array
     * @return string
     */
    public static function checkExists(Request $request)
    {
        $data = $request->all();
        return Customer::checkExists($data);
    }
    
    /**
     * search ajax customer
     */
    public function searchAjax()
    {
        $inputId = Input::get('id');
        $companyId = (isset($inputId) && $inputId) ? $inputId : null;
        return response()->json(
            Customer::searchAjax(Input::get('q'), $companyId)
        );
    }

    /**
     * Get projects list of customer
     * Datatables method
     *
     * @param string $type
     * @param int $id
     * @param Datatables $datatables
     * @return Datatables
     */
    public function getProjectsList($type, $id, Datatables $datatables = null)
    {
        $projectModel = new Project();
        $companyId = $type == 'company' ? $id : null;
        $projects = $projectModel->getProjectsOfCustomer($id, $companyId);
        return $datatables
                ->of($projects)
                ->editColumn('name', function ($model) {
                    return '<a target="_blank" href="' . route('project::point.edit', ['id' => $model->id]) . '" >' . $model->name . '</a>';
                })
                ->editColumn('state', function ($model) {
                    $labelsState = Project::lablelState();
                    return $labelsState[$model->state];
                })
                ->editColumn('type', function ($model) {
                    $labelsType = Project::labelTypeProject();
                    return $labelsType[$model->type];
                })
                ->make(true);
    }

    /**
     * Merge custome to one
     *
     * @param Request $request
     *
     * @return response json
     */
    public function merge(Request $request)
    {
        if(!$request->ajax()){
            return redirect('/');
        }

        $idsMerge = $request->get('idsMerge');
        $idMergeIn = $request->get('idMergeIn');

        Project::whereIn('cust_contact_id', $idsMerge)
                ->update([
                    'cust_contact_id' => $idMergeIn,
                ]);

        Customer::whereIn('id', $idsMerge)
                ->where('id', '<>', $idMergeIn)
                ->delete();
    $request->session()->flash('message', 'New customer added successfully.');
    $request->session()->flash('message-type', 'success');
        return response() ->json([]);
    }

    /**
     * Search customer by company ajax
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchCustomerAjax()
    {
        return response()->json(
            Customer::searchAjax(Input::get('q'),  Input::get('id'), Input::except(['q', 'id']))
        );
    }

        
    /**
     * importExcel
     *
     * @param  mixed $requests
     * @return void
     */
    public function importExcel(Request $requests)
    {
        
        $data = $requests->file('fileToUpload');
        if (empty($data)) {
            return redirect()->route('sales::customer.list', ['tab' => 'all'])
                ->withErrors(trans('sales::vi.Not found item'));
        }
        $nameFileType = $data->getClientOriginalExtension();
        if (!in_array($nameFileType, ['xls', 'xlsx'])) {
            return redirect()->route('sales::customer.list', ['tab' => 'all'])
            ->withErrors(trans('sales::vi.File is not formatted correctly [.xls,.xlsx]!'));
        }
        $configIndex = [
            'id' => 'A',
            'name' => 'B',
            'crm_contact_id' => 'C',
            'crm_account_id'  => 'D'
        ];
        $countError = 0;
        $countSuccess = 0;

        try {
            $excel = Excel::load($data->getPathname(), function ($reader) use ($configIndex, &$countError, &$countSuccess) {
                $doc = $reader->getSheet(0);
                $totalRow = $doc->getHighestRow();
                for ($i = 2; $i <= $totalRow; $i++) {
                    if ((int)$totalRow > 1001) {
                        throw new Exception(trans('sales::vi.Limit max import contract support 1000 row'), 9999);
                    }
                    $name = $doc->getCell("{$configIndex['name']}$i")->getValue();
                    if (trim($name) == '') {
                        continue;
                    }
                    $empId = $doc->getCell("{$configIndex['id']}$i")->getValue();
                    $crmId = $doc->getCell("{$configIndex['crm_contact_id']}$i")->getValue();   
                    $crmAccountId = $doc->getCell("{$configIndex['crm_account_id']}$i")->getValue();  
                  
                    $dataImport = [
                        'id' => $empId,
                        'name' => $name,
                        'crm_contact_id' => $crmId,
                        'crm_account_id' => $crmAccountId,
                    ];
                    try {    
                        Customer::saveCrmContactCustomer($dataImport);
                        $countSuccess++;
                    } catch (Exception $ex) {
                        $countError++;
                        throw $ex;
                    }
                }
            });
        } catch (Exception $ex) {
            Log::error($ex->getTraceAsString());
            if ($ex->getCode() == 9999) {
                return redirect()->route('sales::customer.list', ['tab' => 'all'])
                    ->withErrors($ex->getMessage());
            }
        }
        if ($countError > 0 && isset($excel)) {
            return redirect()->route('sales::customer.list', ['tab' => 'all'])
                ->with('warning', trans('contract::message.Import contract success :successCount and errors :errorCount.', [
                    'successCount' => $countSuccess,
                    'errorCount' => $countError
                ]));
        }
        return redirect()->route('sales::customer.list', ['tab' => 'all'])->with('success', trans('sales::vi.Import customer successfully'));
    }

    public function downloadFormatFile()
    {
        $fileName = 'File format';
        try {
            Excel::create($fileName, function ($excel) {
                $excel->sheet('Sheet1', function ($sheet) {
                    //set row header
                    $rowHeader = [
                        'id',
                        'name crm',
                        'crm_id',
                        'crm_account_id',
                    ];
                    $sheet->row(1, $rowHeader);
                    //format data
                    $rowData = [
                        '0', 'Demo', '123a-456b-789c', '123a-456b-789c'
                    ];
                    $sheet->row(2, $rowData);
                });
            })->download('xlsx');
        } catch (Exception $exception) {
            Log::error($exception->getTraceAsString());
            return response()->json(['success' => false, 'message' => $exception->getMessage()]);
        }
    }
}
