<?php

namespace Rikkei\Sales\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Team\View\Config;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Team\View\Permission;
use Rikkei\Project\View\TaskHelp;
use Rikkei\Sales\Model\Customer;
use Rikkei\Project\Model\Project;
use Rikkei\Team\Model\Employee;
use Rikkei\Project\Model\Task;
use Rikkei\Project\Model\TaskAssign;
use Rikkei\Core\Model\EmailQueue;
use Lang;
use DB;

class Company extends CoreModel
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    const TYPE_SYSTENA = 1;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'cust_companies';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['company', 'homepage', 'address', 
        'phone', 'fax', 'note', 'name_ja', 'contract_security', 'contract_quality',
        'contract_other', 'manager_id', 'sale_support_id', 'type', 'crm_account_id'];

    const COMPANY_EXITS = 0;

    /*
     * get customer by id
     * @param int 
     * return array
     */
    public static function getCompanyById($id) {
    	return self::find($id);
    }

    /*
     * get collection to show grid data
     * @return collection model
     */
    public static function getGridData() {
        $pager = Config::getPagerData(null, ['order' => 'company']);
        $collection = self::select(['id', 'company', 'name_ja'])
            ->orderBy($pager['order'], $pager['dir']);
        $urlRoute = 'sales::company.list';
        if (Permission::getInstance()->isScopeCompany(null, $urlRoute)) {
            // company => view full list
        } elseif (Permission::getInstance()->isScopeSelf(null, $urlRoute)
            || Permission::getInstance()->isScopeTeam(null, $urlRoute)) {
            $collection->where(function ($query) {
                $currentUser = Permission::getInstance()->getEmployee();
                $query->where('manager_id', $currentUser->id)
                    ->orWhere('sale_support_id', $currentUser->id)
                    ->orWhere('created_by', $currentUser->id);
            });
        } else {
            //nothing
        }
        self::filterGrid($collection, [], null, 'LIKE');
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /*
     * get all customer
     * @return collection model
     */
    public static function getAllCompany()
    {
        return self::select(['id', 'company'])->get();
    }

    public function beforeSave($newData)
    {
        $fieldsChanged = $this->fieldsChanged($this, $newData);

        if (count($fieldsChanged)) {
            $projects = $this->getProjects($this->id);
            if ($projects && count($projects)) {
                $this->assignTaskToPM($projects, $fieldsChanged);
            }
        }
    }

    public function assignTaskToPM($projects, $fieldsChanged)
    {
        //Create task
        $taskDelete = Task::whereIn('project_id', $projects->lists('id')->toArray())
            ->where('type', Task::TYPE_CONTRACT_CONFIRM)
            ->where('status', Task::STATUS_NEW)
            ->lists('id')
            ->toArray();

        TaskAssign::whereIn('task_id', $taskDelete)->delete();
        Task::whereIn('id', $taskDelete)->delete();

        foreach ($projects as $project) {
            $task = new Task();
            $task->project_id = $project->id;
            $task->title = Lang::get('sales::view.Confirm customer contract in project');
            $task->type = Task::TYPE_CONTRACT_CONFIRM;
            $task->status = Task::STATUS_NEW;
            $task->save();
            $taskAssign = new TaskAssign();
            $taskAssign->task_id = $task->id;
            $taskAssign->employee_id = $project->manager_id;
            $taskAssign->status = TaskAssign::STATUS_NO;
            $taskAssign->role = TaskAssign::ROLE_OWNER;
            $taskAssign->save();

            self::sendMailConfirmContract($project->manager_id, $project, $fieldsChanged);
        }

    }

    public static function sendMailConfirmContract($employeeId, $project, $fieldsChanged = [])
    {
        $employee = Employee::getEmpByid($employeeId);
        $emailQueue = new EmailQueue();
        if ($employee) {
            $emailQueue->setTo($employee->email)
                ->setSubject('[Rikkeisoft Intranet] Xác nhận hợp đồng của khách hàng trong dự án ' . $project->name)
                ->setTemplate('project::emails.task_confirm_contract', [
                    'name' => $employee->name,
                    'projectId' => $project->id,
                    'projectName' => $project->name,
                    'link' => route('project::project.edit', $project->id) . '#scope',
                    'fieldsChanged' => $fieldsChanged,
                ])
                ->setNotify(
                    $employee,
                    null,
                    route('project::project.edit', $project->id) . '#scope', ['category_id' => RkNotify::CATEGORY_PROJECT]
                )
                ->save();
        }
    }

    public function fieldsChanged($oldCompany, $newCompany)
    {
        if (!$oldCompany || !$newCompany) {
            return null;
        }

        $results = [];
        $fields = ['contract_security', 'contract_quality'];
        $taskHelp = new TaskHelp();
        foreach ($fields as $field) {
            $oldField = $oldCompany->$field;
            if ($taskHelp->isFieldChanged($oldField, $newCompany[$field])) {
                $results[] = [
                    'field' => $field,
                    'old' => empty($oldField) ? '' : $oldField,
                    'new' => empty($newCompany[$field]) ? '' : $newCompany[$field],
                ];
            }
        }

        return $results;
    }

    public function getProjects($companyId)
    {
        $companyTbl = self::getTableName();
        $cusTbl = Customer::getTableName();
        $projTbl = Project::getTableName();
        $empTbl = Employee::getTableName();

        return self::join("{$cusTbl}", "{$cusTbl}.company_id", "=", "{$companyTbl}.id")
            ->join("{$projTbl}", "{$projTbl}.cust_contact_id", "=", "{$cusTbl}.id")
            ->join("{$empTbl}", "{$projTbl}.manager_id", "=", "{$empTbl}.id")
            ->where("{$companyTbl}.id", $companyId)
            ->where("{$projTbl}.status", Project::STATUS_APPROVED)
            ->whereIn("{$projTbl}.state", [
                Project::STATE_NEW,
                Project::STATE_PROCESSING,
                Project::STATE_PENDING,
            ])
            ->select([
                "{$projTbl}.id",
                "{$projTbl}.name",
                "{$projTbl}.manager_id",
                "{$empTbl}.email",
            ])
            ->groupBy("{$projTbl}.id")
            ->get();
    }

    /**
     * Get saler by company
     *
     * @param int $companyId
     * @return array|\Rikkei\Team\Model\type
     */
    public static function getSaleByCompany($companyId)
    {
       $company = self::select('manager_id', 'sale_support_id')->where('id', $companyId)->first();
       $employees = [];
       if ($company->manager_id) {
            $employees[] = $company->manager_id;
       }
       if ($company->sale_support_id) {
           $employees[] = $company->sale_support_id;
       }
       if (!empty($employees)) {
           $employees = Employee::select(DB::raw("SUBSTRING_INDEX(email, '@', 1) as nickname"), 'id', 'email')->wherein('id', $employees)->get();
       }
       return $employees;
    }

    /**
     * Get all company
     */
    public static function getCompanies()
    {
        return self::pluck('company', 'id')->toArray();
    }

    /**
     * search ajax company by name
     *
     * @param string $param
     * @return array
     */
    public static function searchAjax($param)
    {
        $config = [
            'page' => 1,
            'limit' => 20
        ];
        $collection = self::select(['id', 'company'])
            ->where('company', 'like', '%'.$param.'%')
            ->orWhere('name_ja', 'like', '%'.$param.'%');

        self::pagerCollection($collection, $config['limit'], $config['page']);
        $result = [
            'total_count' => $collection->total(),
            'incomplete_results' => true,
            'items' => []
        ];
        foreach ($collection as $item) {
            $result['items'][] = [
                'id' => $item->id,
                'text' => $item->company,
            ];
        }
        return $result;
    }

    public static function getCustCompany()
    {
        return self::select(
            'cust_companies.company',
            'cust_companies.name_ja',
            'cust_companies.homepage',
            'cust_companies.address',
            'cust_companies.phone',
            'cust_companies.fax',
            'cust_companies.note',
            'cust_companies.created_by',
            'cust_companies.contract_security',
            'cust_companies.contract_quality',
            'cust_companies.contract_other',
            'manager_employee.email as manager_email',
            'sale_support.email as sale_support_email',
            'cust_companies.type'
        )
            ->leftJoin('employees as manager_employee', 'manager_employee.id', '=', 'cust_companies.manager_id')
            ->leftJoin('employees as sale_support', 'sale_support.id', '=', 'cust_companies.sale_support_id')
            ->whereNull('cust_companies.deleted_at')
            ->get();
    }
        
    /**
     * getCompaniesByCrmId
     *
     * @param  array $crmIds
     * @return collection
     */
    public function getCompaniesByCrmId($crmIds)
    {
        return self::select('*')
        ->whereIn('crm_account_id', $crmIds)
        ->get();
    }
}
