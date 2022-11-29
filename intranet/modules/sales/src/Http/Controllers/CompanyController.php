<?php

namespace Rikkei\Sales\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller as Controller;
use Illuminate\Http\Request;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Team\View\Permission;
use Illuminate\Support\Facades\Validator;
use Rikkei\Sales\Model\Company;
use Lang;
use Rikkei\Core\View\Menu;
use Illuminate\Support\Facades\Input;
use Rikkei\Sales\Model\Customer;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\Employee;
use Rikkei\Sales\View\CompanyHelp;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\SaleProject;
use DB;

class CompanyController extends Controller {
    
    /**
     * construct more
     */
    protected function _construct()
    {
        Menu::setActive('sales');
        Breadcrumb::add('Company' , route('sales::company.list'));
    }
    
    /**
     * Create customer page view
     * @return view
     */
    public function create() {
        Breadcrumb::add(Lang::get('sales::view.Company.Create.Create company'));
        $teamsSale = Team::where('type', Team::TEAM_TYPE_SALE)->lists('id')->toArray();
        return view('sales::company.create', [
            'company' => new Company(),
            'salers' => Employee::getEmpByTeams($teamsSale),
            'extractManagers' => \Rikkei\Sales\View\View::companyManagerExtract(),
        ]);
    }

    /*
     * store customer
     */
    public function store(Request $request) {
        $companyHelper = new CompanyHelp();
        if (! Permission::getInstance()->isAllow()) {
            echo view('errors.permission');
            exit;
        }
        $data = $request->all();
        $tableCompany = Company::getTableName();
        $messages = [
            'company.required' => Lang::get('sales::view.Company.Create.Name required'),
            'company.max' => Lang::get('sales::view.Company.Create.Name greater than', ['number'=> 255]),
            'company.unique' => Lang::get('sales::view.Company.Create.Company name is exist'),
            'manager_id.required' => Lang::get('sales::message.Management account required'),
//            'contract_security.required' => Lang::get('sales::message.Contract security required'),
//            'contract_quality.required' => Lang::get('sales::message.Contract quality required'),
//            'contract_other.required' => Lang::get('sales::message.Contract other required'),
        ];
        $rules = [
            'manager_id' => 'required',
//            'contract_security' => 'required',
//            'contract_quality' => 'required',
//            'contract_other' => 'required',
        ];
        if (isset($data['company_id'])) {
            $companyId = $data['company_id'];
            $rules['company'] = "required|max:255|unique:$tableCompany,company,$companyId,id,deleted_at,NULL";
        } else {
            $rules['company'] = "required|max:255|unique:$tableCompany,company,NULL,id,deleted_at,NULL";
        }
        $validator = Validator::make($data, $rules, $messages);
        if ($validator->fails()) {
            return redirect()->route('sales::company.create')
                        ->withErrors($validator)
                        ->withInput();
        }
        if (isset($data['company_id']) && $data['company_id']) {
            $company = Company::getCompanyById($data['company_id']);
            if (!$company) {
                return redirect()->route('sales::company.list')->withErrors(Lang::get('sales::message.Not found item.'));
            }
            if (!$companyHelper->hasPermissionEdit($company)) {
                echo view('errors.permission');
                exit;
            }
        } else {
            $company = new Company();
            $company->created_by = Permission::getInstance()->getEmployee()->id;
        }

        DB::beginTransaction();
        try {
            if (isset($data['company_id']) && $data['company_id']) {
                $company->beforeSave($data);
            }

            $company->fill($data);
            $company->sale_support_id = empty($company->sale_support_id) ? null : $company->sale_support_id;

            $company->save();
            if(isset($data['company_id']) && $data['company_id']) {
                $msg = Lang::get('sales::message.Update company success');
                // Update sales of projects
                $projsOfCom = Project::where('company_id', $company->id)->pluck('id')->toArray();
                SaleProject::whereIn('project_id', $projsOfCom)->delete();
                $dataInsert = [];
                $dataSales = [$company->manager_id, $company->sale_support_id];
                foreach ($projsOfCom as $projId) {
                    foreach ($dataSales as $saleId) {
                        if ($saleId) {
                            $dataInsert[] = [
                                'employee_id' => $saleId,
                                'project_id' => $projId,
                                'created_at' => date('y-m-d H:i:s'),
                                'updated_at' => date('y-m-d H:i:s'),
                            ];
                        }
                    }
                }
                SaleProject::insert($dataInsert);
            } else {
                $msg = Lang::get('sales::message.Create company success');
            }
            $messages = [
                    'success'=> [
                        $msg,
                    ]
            ];
            DB::commit();
            return redirect()->route('sales::company.edit', ['id' => $company ->id])->with('messages', $messages);
        } catch (Exception $ex) {
            DB::rollBack();
        }
    }

    /*
     * list customer
     */
    public function lists() {
        if (! Permission::getInstance()->isAllow()) {
            echo view('errors.permission');
            exit;
        }
        Breadcrumb::add(Lang::get('sales::view.Company.List'));
        $collectionModel = Company::getGridData();
        return view('sales::company.index', [
            'collectionModel' => $collectionModel
        ]);
    }

    /**
     * Edit customer page
     *
     * @param int $id company_id
     *
     * @return Response view
     */
    public function edit($id) {
        if (! Permission::getInstance()->isAllow()) {
            echo view('errors.permission');
            exit;
        }
        $company = Company::getCompanyById($id);
        if (! $company) {
            return redirect()->route('sales::company.list')->withErrors(Lang::get('sales::message.Not found item.'));
        }

        //Check permission edit this company
        $companyHelp = new CompanyHelp();
        if (!$companyHelp->hasPermissionEdit($company)) {
            echo view('errors.permission');
            exit;
        }

        $customerModel = new Customer();
        $customerOfCompany = Customer::customerByCompany($id);
        Breadcrumb::add(Lang::get('sales::view.Company.Create.Update company'));
        $teamsSale = Team::where('type', Team::TEAM_TYPE_SALE)->lists('id')->toArray();
        $salers = Employee::getEmpByTeams($teamsSale);
        $extractManagers = \Rikkei\Sales\View\View::companyManagerExtract();

        if (!empty($company->manager_id) || !empty($company->sale_support_id)) {
            $salersId = $salers->lists('id')->toArray();
            foreach ($extractManagers as $extract) {
                $salersId[] = $extract->id;
            }
            if (!empty($company->manager_id) && !in_array($company->manager_id, $salersId)) {
                $manager = Employee::getEmpById($company->manager_id);
                if (count($manager)) {
                    $salers->push($manager);
                }
            }
            if (!empty($company->sale_support_id) && !in_array($company->sale_support_id, $salersId)) {
                $supporter = Employee::getEmpById($company->sale_support_id);
                if (count($supporter)) {
                    $salers->push($supporter);
                }
            }
        }

        return view('sales::company.create', compact([
            'company', 'customerOfCompany', 'salers', 'extractManagers'
        ]));
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
        $company = Company::getCompanyById($id);
        if (! $company) {
            return redirect()->route('sales::company.list')->withErrors(Lang::get('team::messages.Not found item.'));
        }
        $company->delete();
        $messages = [
            'success'=> [
                Lang::get('team::messages.Delete item success!'),
            ]
        ];
        return redirect()->route('sales::company.list')->with('messages', $messages);
    }
    /**
     * Merge company to one
     * @return response json
     */
    public function merge(Request $request)
    {
        if (!$request->ajax()) {
            return redirect('/');
        }
        $idsMerge = $request->get('idsMerge');
        $idMergeIn = $request->get('idMergeIn');
        $companiesMerge = Company::whereIn('id', $idsMerge)
                        ->where('id', '<>', $idMergeIn)
                        ->select('id', 'company', 'name_ja', 'contract_security', 'contract_quality', 'contract_other')
                        ->orderBy('company')
                        ->get();
        $comanyMergeIn = Company::find($idMergeIn);
        $arrrayFields = ['contract_security', 'contract_quality', 'contract_other'];
        foreach ($arrrayFields as $field) {
            foreach ($companiesMerge as $company) {
                if (!$comanyMergeIn->{$field} && $company->{$field}) {
                    $comanyMergeIn->{$field} = $company->{$field};

                    break;
                }
            }
        }
        $comanyMergeIn->save();

        Customer::whereIn('company_id', $idsMerge)
                ->update([
                    'company_id' => $idMergeIn,
                ]);

        Company::whereIn('id', $idsMerge)
                ->where('id', '<>', $idMergeIn)
                ->delete();

        $request->session()->flash('messages', ['success' => [Lang::get('sales::message.Merge company success.')]]);
        $request->session()->flash('message-type', 'success');
        return response() ->json([]);
    }

    /**
     * Check exits name company
     *
     * @return bool
     */
    public function checkExits()
    {
        $companyId = Input::get('companyId');
        $companyId = ($companyId !== 'undefined') ? $companyId : null;
        $companyName = Input::get('companyName');
        if ($companyId) {
            return Company::where('company', $companyName)
                    ->whereNotIn('id', [$companyId])
                    ->count() == Company::COMPANY_EXITS ? 'true' : 'false';
        } else {
            return Company::where('company', $companyName)
                    ->count() == Company::COMPANY_EXITS ? 'true' : 'false';
        }
    }

    /**
     * Search company ajax
     */
    public function searchAjax()
    {
        return response()->json(
            Company::searchAjax(Input::get('q'))
        );
    }
}
