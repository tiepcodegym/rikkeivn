<?php

namespace Rikkei\Sales\Model;

use Carbon\Carbon;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Team\View\Permission;
use Rikkei\Core\View\Form;
use DB;
use Rikkei\Core\View\View;

class Customer extends CoreModel
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'cust_contacts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'name_ja', 'company_id', 'crm_contact_id', 'email', 'phone', 'skype',
        'chatwork', 'birthday', 'address', 'note', 'contract', 'deleted_at'
    ];

    /*
     * get customer by id
     * @param int 
     * return array
     */
    public static function getCustomerById($id)
    {
        return self::find($id);
    }

    public static function getCustomerWithCompanyName($id)
    {
        $cusTbl = self::getTableName();
        $comTbl = Company::getTableName();
        return self::join($comTbl, "{$comTbl}.id", "=", "{$cusTbl}.company_id")
            ->where("{$cusTbl}.id", $id)
            ->select("{$cusTbl}.*", "{$comTbl}.company")
            ->first();
    }

    /*
     * get collection to show grid data
     * @return collection model
     */
    public static function getGridData($projectIds, $customerIds)
    {
        $customerTable = self::getTableName();
        $companyTable = Company::getTableName();

        $pager = Config::getPagerData();
        $collection = self::select([
            $customerTable . '.id', $customerTable . '.name_ja',
            $customerTable . '.name', $customerTable . '.email', $customerTable . '.contract',
            $companyTable . '.company', $companyTable . '.name_ja as company_name_ja'
        ])
            ->leftJoin($companyTable, function ($join) use ($companyTable, $customerTable) {
                $join->on($companyTable . '.id', '=', $customerTable . '.company_id')
                    ->whereNull($companyTable . '.deleted_at');
            });
        $urlRoute = 'sales::customer.list';
        if (Permission::getInstance()->isScopeCompany(null, $urlRoute)) {
        } elseif (Permission::getInstance()->isScopeTeam(null, $urlRoute)) {
            $collection->where(function ($query) use ($customerIds, $projectIds) {
                $currentUser = Permission::getInstance()->getEmployee();
                $teamsOfEmp = \Rikkei\Team\View\CheckpointPermission::getArrTeamIdByEmployee($currentUser->id);
                $employeesOfTeam = DB::table('team_members')->whereIn('team_id', $teamsOfEmp);
                $employeeIds = $employeesOfTeam->lists('employee_id');
                $query->whereIn('cust_contacts.created_by', $employeeIds)
                    ->orwhere('cust_companies.manager_id', $currentUser->id)
                    ->orWhere('cust_companies.sale_support_id', $currentUser->id)
                    ->orWhere('cust_companies.created_by', $currentUser->id);
                if (!empty($projectIds) && is_array($projectIds) && count($projectIds)) {
                    $query->orWhereIn('cust_contacts.id', $customerIds);
                }
            });
        } elseif (Permission::getInstance()->isScopeSelf(null, $urlRoute)) {
            $collection->where(function ($query) {
                $currentUser = Permission::getInstance()->getEmployee();
                $query->where('cust_contacts.created_by', $currentUser->id);
            });
        } else {
            View::viewErrorPermission();
        }

        $collection->groupBy('cust_contacts.id');
        $collection->orderBy($pager['order'], $pager['dir']);
        $companyName = trim(Form::getFilterData('excerpt', 'company'));
        $collection->where(function ($query) use ($companyTable, $companyName) {
            $query->orWhere($companyTable . '.company', 'like', "%$companyName%")
                ->orWhere($companyTable . '.name_ja', 'like', "%$companyName%")
                ->orWhere(DB::raw("CONCAT({$companyTable}.company, ' (', {$companyTable}.name_ja, ')')"), 'like', "%$companyName%");
        });
        self::filterGrid($collection, [], null, 'LIKE');
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /*
     * get all customer
     * @return collection model
     */
    public static function getAllCustomer()
    {
        return self::select(['id', 'name', 'email'])->get();
    }

    /*
     * get all customer
     * @return collection model
     */
    public static function getAllCustomerCompany()
    {
        $tableCustomer = self::getTableName();
        $tableCompany = Company::getTableName();

        return self::select(
            "{$tableCustomer}.id as id",
            "{$tableCustomer}.name as name",
            "{$tableCompany}.company as company_name",
            "{$tableCustomer}.name_ja as name_ja"
        )
            ->leftJoin($tableCompany, "{$tableCompany}.id", '=', "{$tableCustomer}.company_id")
            ->orderBy("{$tableCustomer}.name")
            ->get();
    }

    /**
     * saveCrmCustomer
     *
     * @param  mixed $data
     * @return void
     */
    public static function saveCrmContactCustomer($data)
    {
        if (empty($data['crm_contact_id'] && $data['crm_account_id'])) {
            return '';
        };
        $company = Company::where('crm_account_id', $data['crm_account_id'])->first();
        if($company){
            if (!$data['id']) {
                $customer = [
                    'name' => $data['name'],
                    'crm_contact_id' => $data['crm_contact_id'],
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'company_id' => $company->id
                ];
                self::insert($customer);
                return '';
            }
            $customer = self::find($data['id']);
            if (isset($customer) != '') {
                $customer->crm_contact_id = $data['crm_contact_id'];
                $customer->name = $data['name'];
                $customer->company_id = $company->id;
                $customer->save();
                return;
            }
            return '';
        }
    }

    /**
     * check exists customer
     * @param array
     * @return string
     */
    public static function checkExists($input)
    {
        if ($input['isEdit']) {
            $isCheck = self::where('email', $input['email'])
                ->whereNotIn('id', [$input['customerId']])
                ->first();
        } else {
            $isCheck = self::where('email', $input['email'])
                ->get();
        }
        if ($isCheck) {
            return 'false';
        }
        return 'true';
    }

    /**
     * search ajajx customer follow name and name ja
     * 
     * @param string $param
     * @param int $companyId
     * @return array
     */
    public static function searchAjax($param, $companyId = null, $options = [])
    {
        $config = array_merge([
            'page' => 1,
            'limit' => 20
        ], $options);
        $collection = self::select(
            'id',
            DB::raw('CASE WHEN name_ja IS NOT NULL AND TRIM(name_ja) != "" '
                . 'THEN CONCAT(name, " (", name_ja, ")") ELSE name END AS text')
        )
            ->where(function ($query) use ($param) {
                $query->orWhere('name', 'like', '%' . $param . '%')
                    ->orWhere('name_ja', 'like', '%' . $param . '%');
            });
        if ($companyId) {
            $collection->where('company_id', $companyId);
        }
        if ($config['page'] < 0) {
            return $collection->get()->toArray();
        }
        self::pagerCollection($collection, $config['limit'], $config['page']);
        return [
            'total_count' => $collection->total(),
            'incomplete_results' => true,
            'items' => $collection->toArray()
        ];
    }

    public static function customerByCompany($companyId)
    {
        return self::where('company_id', $companyId)->get();
    }

    /**
     * list customer managed by employee
     *
     * @param int $empId
     * @return Customer collection
     */
    public static function listManagedByEmployee($empId)
    {
        $tblCustomer = self::getTableName();
        $tblCompany = Company::getTableName();
        return self::join("{$tblCompany}", "{$tblCompany}.id", '=', "{$tblCustomer}.company_id")
            ->where("{$tblCompany}.manager_id", $empId)
            ->orWhere("{$tblCompany}.sale_support_id", $empId)
            ->select("{$tblCustomer}.*")
            ->get();
    }
}
