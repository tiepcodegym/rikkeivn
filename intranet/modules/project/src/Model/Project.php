<?php

namespace Rikkei\Project\Model;


use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Core\View\CookieCore;
use Rikkei\Core\View\CurlHelper;
use Rikkei\Core\View\Form;
use Rikkei\ManageTime\View\WorkingTime as WorkingTimeProject;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Project\View\TimesheetHelper;
use Rikkei\Project\View\View as ViewProject;
use Rikkei\Resource\View\View as ResourceView;
use Rikkei\Sales\Model\Company;
use Rikkei\Sales\Model\Customer;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\EmployeeRole;
use Rikkei\Team\Model\EmployeeTeamHistory;
use Rikkei\Team\Model\PqaResponsibleTeam;
use Rikkei\Team\Model\Role;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\View\CheckpointPermission;
use Rikkei\Team\View\Config;
use Rikkei\Team\View\Permission;
use Rikkei\Team\View\TeamList;

class Project extends ProjectWOBase
{

    use SoftDeletes;

    const KEY_CACHE = 'project';
    const KEY_CACHE_TEAM = 'project_team';
    const KEY_CACHE_MEMBER = 'project_member';
    const KEY_CACHE_TASK = 'project_task';
    const LENTH_PROJECT_CODE_NO = 3;
    const URI_GET_TOKEN = '/Api/access_token';
    const URI_GET_PURCHASE_ORDER = '/Api/V8/custom/line-items/product-revenue/list';
    const URI_SAVE_PURCHASE_ID_TO_CRM = '/Api/V8/custom/po/update_project';

    /*
     * point project default
     */
    const POINT_PROJECT_TYPE_TRANING = 10;
    const POINT_PROJECT_TYPE_RD = 15;
    const POINT_PROJECT_TYPE_ONSITE = 19.5;

    /*
     * type MM, MD
     */
    const MM_TYPE = 1;
    const MD_TYPE = 2;

    const PER_PAGE = 50;
    const NONE_VALUE = 0;
    
    const DAY_ALLOWED = 30; //khoảng cách cho phép(ngày)
    const DAY_IN_MONTH = 30;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'projs';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at', 'start_at', 'end_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['cust_contact_id', 'name', 'manager_id', 'company_id',
        'start_at', 'description', 'state', 'leader_id',
        'end_at', 'type', 'project_code', 'type_mm', 'is_important', 'kind_id'];

    /*
     * cycle of reward
     */

    const CYCLE_REWARD = 3;

    /*
     * state project
     */
    const STATE_NEW = 1;
    const STATE_PROCESSING = 2;
    const STATE_PENDING = 3;
    const STATE_CLOSED = 4;
    const STATE_REJECT = 5;
    const STATE_OPPORTUNITY = 30;

    /*apiGetPoByProjectId
     * type project
     */
    const TYPE_OSDC = 1;
    const TYPE_BASE = 2;
    const TYPE_TRAINING = 3;
    const TYPE_RD = 4;
    const TYPE_ONSITE = 5;
    const TYPE_OPPORTUNITY = 30;

    /*
     * level project
     */
    const GROUP = 1;
    const COMPANY = 2;

    /*
     * check exits
     */
    const PROJECT_EXISTS = 0;
    const PROJECT_EXISTS_EDIT = 1;
    const TABLE_PROJECT = 1;


    /*
     * checked project indetify
     */
    const SOURCE_SERVER_APPROVED = 3;
    const SOURCE_SERVER_REVIEWED = 2;
    const CHECKED_PROJECT_INDETIFY = 1;
    const UN_CHECKED_PROJECT_INDETIFY = 0;

    /*
     * value of field is_important
     */
    const IS_IMPORTANT = 1;

    /*
     * value of effort team allocation
     */
    const MAX_EFFORT = 200;
    const STATUS_ENABLE = 1;
    const STATUS_PROJECT_CLOSE = 4;

    //category project
    const CATEGORY_DEVELOPMENT = 1;
    const CATEGORY_MAINTENANCE = 3;
    /**
     * Get the project meta for the project
     */
    public function projectMeta() {
        return $this->hasOne('Rikkei\Project\Model\ProjectMeta', 'project_id');
    }

    /**
     * Get the project wo note for the project
     */
    public function projectWONote() {
        return $this->hasOne('Rikkei\Project\Model\ProjectWONote', 'project_id');
    }

    /**
     * Get the project child
     */
    public function projectChild() {
        return $this->hasOne('Rikkei\Project\Model\Project', 'parent_id');
    }

    /**
     * Get project manager
     */
    public function employeePm()
    {
        return $this->hasOne('Rikkei\Team\Model\Employee', 'id', 'manager_id');
    }

    /**
     * The project has many team join
     */
    public function teamProject() {
        $tableTeamProject = TeamProject::getTableName();
        return $this->belongsToMany('Rikkei\Team\Model\Team', $tableTeamProject, 'project_id', 'team_id')->withTimestamps()->withTrashed();
    }

    /** get all team name of project
     */
    public static function teamProjectName($id) {
        $project = self::find($id);
        $results = [];
        if (!$project) {
            return null;
        }
        foreach ($project->teamProject as $iteam) {
            $results[] = $iteam->name;
        }
        return implode(",", $results);
    }

    /**
     * The project has many sale employee
     */
    public function saleProject() {
        $tableSaleProject = SaleProject::getTableName();
        return $this->belongsToMany('Rikkei\Team\Model\Employee', $tableSaleProject, 'project_id', 'employee_id')->withTimestamps();
    }

    /**
     * get short label type project
     */
    public static function labelTypeProject() {
        return [
            self::TYPE_OSDC => trans('project::view.OSDC'),
            self::TYPE_BASE => trans('project::view.Base'),
            self::TYPE_TRAINING => trans('project::view.Training'),
            self::TYPE_RD => trans('project::view.R&D'),
            self::TYPE_ONSITE => trans('project::view.Onsite'),
        ];
    }

    /**
     * get chart label type project
     */
    public static function labelChartTypeProject() {
        return [
            self::TYPE_OSDC => trans('project::view.OSDC'),
            self::TYPE_BASE => trans('project::view.Base'),
            self::TYPE_TRAINING => trans('project::view.Training'),
            self::TYPE_RD => trans('project::view.RD'),
            self::TYPE_ONSITE => trans('project::view.Onsite'),
            self::TYPE_OPPORTUNITY => 'Opportunity'
        ];
    }

    /**
     * get full label type project
     */
    public static function labelTypeProjectFull() {
        return [
            self::TYPE_OSDC => trans('project::view.Project OSDC-Time material'),
            self::TYPE_BASE => trans('project::view.Project Base'),
            self::TYPE_TRAINING => trans('project::view.Project Training'),
            self::TYPE_RD => trans('project::view.Project R&D'),
            self::TYPE_ONSITE => trans('project::view.Project Onsite'),
        ];
    }

    /**
     * label of field
     *
     * @return array
     */
    public static function lablelFieldProject() {
        return [
            'cust_contact_id' => Lang::get('project::view.customer of project'),
            'name' => Lang::get('project::view.name of project'),
            'manager_id' => Lang::get('project::view.PM of project'),
            'start_at' => Lang::get('project::view.start date of project'),
            'state' => Lang::get('project::view.status of project'),
            'type' => Lang::get('project::view.type of project'),
            'project_code' => Lang::get('project::view.project code of project'),
        ];
    }

    /**
     * label of state
     *
     * @return array
     */
    public static function lablelState() {
        return [
            self::STATE_NEW => 'New',
            self::STATE_PROCESSING => 'Processing',
            self::STATE_PENDING => 'Postpone',
            self::STATE_CLOSED => 'Closed',
            self::STATE_REJECT => 'Cancelled',
            self::STATE_OPPORTUNITY => 'Opportunity'
        ];
    }

    /**
     * label of state
     *
     * @return array
     */
    public static function lablelStateWithoutPqa() {
        return [
            self::STATE_NEW => 'New',
            self::STATE_PROCESSING => 'Processing',
            self::STATE_PENDING => 'Postpone',
            self::STATE_REJECT => 'Cancelled',
            self::STATE_OPPORTUNITY => 'Opportunity'
        ];
    }

    /**
     * get label of state
     *
     * @param int $state
     * @param array $labels
     * @return string
     */
    public static function getLabelType($state, array $labels = []) {
        if (!$labels) {
            $labels = self::lablelState();
        }
        if (isset($labels[$state])) {
            return $labels[$state];
        }
        return null;
    }

    /**
     * get label of type
     *
     * @param int $type
     * @param array $labels
     * @return string
     */
    public static function getLabelState($type, array $labels = []) {
        if (!$labels) {
            $labels = self::labelTypeProject();
        }
        if (isset($labels[$type])) {
            return $labels[$type];
        }
        return null;
    }

    /**
     * get label state of project
     */
    public function getStateLabel() {
        $label = self::lablelState();
        if (isset($label[$this->state])) {
            return $label[$this->state];
        }
        return null;
    }

    /**
     * get label type of project
     */
    public function getTypeLabel() {
        $label = self::labelTypeProject();
        if (isset($label[$this->type])) {
            return $label[$this->type];
        }
        return null;
    }

    /**
     * get label type MM or MD
     *
     * @return array
     */
    public static function getTypeResourceEffort() {
        return [
            self::MM_TYPE,
            self::MD_TYPE
        ];
    }

    /**
     * get label type MM or MD
     * @return string
     */
    public function getLabelTypeMM($type = null) {
        if ($type) {
            if ($type == self::MD_TYPE) {
                return trans('project::view.MD');
            }
            return trans('project::view.MM');
        }
        if ($this->type_mm == self::MD_TYPE) {
            return trans('project::view.MD');
        }
        return trans('project::view.MM');
    }

    /**
     * get type mm/md by project id
     * @param type $projectId
     * @return type
     */
    public static function getTypeMMById($projectId = null) {
        if ($projectId) {
            $project = self::find($projectId, ['type_mm']);
            if ($project && in_array($project->type_mm, self::getTypeResourceEffort())) {
                return $project->type_mm;
            }
        }
        return self::MM_TYPE;
    }

    public static function getLeaderOfProject($projectId)
    {
        return self::leftJoin('employees', 'employees.id', '=', 'projs.leader_id')
            ->where('projs.id', $projectId)
            ->select('employees.email', 'projs.leader_id')
            ->first();
    }

    /*
     * insert or update project
     * @param array
     */

    public static function insertOrUpdateProject($input, $isFlagCreate = false, $cloneId = null) {
        $input = array_filter($input);
        self::setAssignDefaultValue($input, [
            'scope_desc',
            'scope_customer_provide',
            'scope_products',
            'scope_require',
            'scope_scope',
            'scope_env_test',
            'requirements',
            'attach',
            'lineofcode_baseline',
            'lineofcode_current'
        ]);
        if (!isset($input['scope_desc'])) {
            $input['scope_desc'] = null;
        }
        unset($input['project_code_auto']);
        DB::beginTransaction();
        try {
            $checkHasDraftEdit = false;
            $createdBy = Permission::getInstance()->getEmployee()->id;
            if (isset($input['project_id']) &&
                    $input['project_id'] &&
                    !$isFlagCreate
            ) {
                $project = self::getProjectById($input['project_id']);
                $projectMeta = $project->projectMeta;
                $isCreateProject = false;
            } else {
                $project = new Project;
                $projectMeta = new ProjectMeta();
                $projectMember = new ProjectMember();
                $quality = new ProjQuality();
                if (isset($input['type_mm']) &&
                        in_array($input['type_mm'], self::getTypeResourceEffort())
                ) {
                    $project->type_mm = $input['type_mm'];
                } else {
                    $project->type_mm = self::MM_TYPE;
                }
                $isCreateProject = true;
            }

            if ($project->id && $project->state != self::STATE_NEW) {
                if ($input['state'] == self::STATE_NEW) {
                    unset($input['state']);
                }
            }
            $project->created_by = $createdBy;
            $project->category_id = $input['category'];
            $project->classification_id = $input['classification'];
            $project->business_id = $input['business'];
            $project->sub_sector = $input['sub_sector'];
            if (isset($input['cus_email'])) {
                $project->cus_email = $input['cus_email'];
            }
            if (isset($input['cus_contact'])) {
                $project->cus_contact = $input['cus_contact'];
            }
            if (isset($input['purchase_order_id'])) {
                $project->po_id = $input['purchase_order_id'];
            }
            $project->fill($input);
            if ($cloneId) {
                $input['team_id'] = self::getAllTeamOfProject($cloneId);
            }
            if (isset($input['leader_id']) && isset($input['team_id'])) {
                $leadersAvai = EmployeeRole::getLeadAndSub($input['team_id']);
                if (!key_exists($input['leader_id'], $leadersAvai)) {
                    throw new Exception('Please choose a leader for project');
                }
            }
            if (isset($input['status']) && $input['status']) {
                $project->status = $input['status'];
            }
            $projectAttributes = $project->attributes;
            $projectOriginal = $project->original;
            $project->save();
            if ($cloneId) {
                $teamOld = self::getAllSaleOfProject($cloneId);
                $project->saleProject()->attach($teamOld);
                $option = [
                    'create' => true
                ];
                // insert program language
                $programLang = ProjectProgramLang::getProgramLangOfProject($cloneId);
                ProjectProgramLang::insertItems(
                    $project, array_keys($programLang), $option
                );
                //insert approved cost
                ProjectApprovedProductionCost::insertCloneApprovedCost($cloneId, $project->id);
                //insert billable effort
                ProjQuality::insertCloneEffort($cloneId, $project->id);
                //insert scope and object
                ProjectMeta::cloneProjectMeta($cloneId, $project->id);
                //insert stages and Deliverable
                StageAndMilestone::cloneProjectStage($cloneId, $project->id);
                //insert Member
                ProjectMember::cloneProjectMember($cloneId, $project);
            }
            $projectMeta->fill($input);
            if (isset($quality)) {
                $quality->status = self::STATUS_APPROVED;
                $quality->project_id = $project->id;
                $quality->created_by = Permission::getInstance()->getEmployee()->id;
                $quality->fill($input);
                $quality->save();
            }
            if (isset($projectMember)) {
                $projectMember->project_id = $project->id;
                $projectMember->employee_id = $project->manager_id;
                $projectMember->start_at = $project->start_at;
                $projectMember->end_at = $project->end_at;
                $projectMember->type = ProjectMember::TYPE_PM;
                $projectMember->effort = ProjectMember::EFFORT_PM_DEFAUTL;
                $projectMember->created_by = $createdBy;
                $projectMember->status = ProjectWOBase::STATUS_APPROVED;
                $projectMember->flatResourceItem(false, $project->type_mm);
                $projectMember->save();
            }
            $projectMetaAttributes = $projectMeta->attributes;
            $projectMetaOriginal = $projectMeta->original;
            $project->projectMeta()->save($projectMeta);
            ProjectPoint::findFromProject($project->id);
            ProjPointFlat::findFlatFromProject($project->id);
            if (isset($input['project_id']) && $input['project_id']) {
                //Get old team_id
                $teamOld = self::getAllTeamOfProject($project->id);
                //Delete old team
                $project->teamProject()->detach($teamOld);

                //Get old sale_id
                $teamOld = self::getAllSaleOfProject($project->id);
                //Delete old sale
                $project->saleProject()->detach($teamOld);
            }
            $project->teamProject()->attach($input['team_id']);
            if (isset($input['sale_id'])) {
                $project->saleProject()->attach($input['sale_id']);
            }
            if (isset($input['project_id']) && $input['project_id']) {
                $arrayLabelProject = self::lablelFieldProject();
                ViewProject::checkChangeAndInsertProjectLog(
                        $project->id, $arrayLabelProject, $projectAttributes, $projectOriginal
                );
                $arraylableProjectMeta = ProjectMeta::labelFiledProjectMeta();
                ViewProject::checkChangeAndInsertProjectLog(
                        $projectMeta->project_id, $arraylableProjectMeta, $projectMetaAttributes, $projectMetaOriginal
                );
            } else {
                $nameCreated = ViewProject::getNickName();
                $textCreated = 'created project';
                $content = $project->created_at . ': ' . $nameCreated . ' ' . $textCreated;
                ProjectLog::insertProjectLog($project->id, $content, $nameCreated);
            }
            $fullTeamName = null;
            $project->renderProjectCodeAuto($fullTeamName);
            SourceServer::saveFromRequest($project, $fullTeamName);
            if (isset($input['prog_langs']) && $input['prog_langs']) {
                if (!isset($input['project_id']) || !$input['project_id']) {
                    $option = [
                        'create' => true
                    ];
                } else {
                    $option = [];
                }
                ProjectProgramLang::insertItems(
                        $project, (array) $input['prog_langs'], $option
                );
            }
            if (isset($input['data_project_cost']) && $input['data_project_cost']) {
                $dataProductionCost = json_decode($input['data_project_cost'], true);
                ProjectApprovedProductionCost::insertProjectProductionCost($dataProductionCost, $project->id, true);
            }
            if (isset($input['data_billable_detail']) && $input['data_billable_detail']) {
                $dataBillableCostDetail = json_decode($input['data_billable_detail'], true);
                ProjectBillableCost::insertProjectBillableCostDetail($dataBillableCostDetail, $project->id);
            }
            DB::commit();
            CacheHelper::forget(self::KEY_CACHE_TEAM, $project->id);
            CacheHelper::forget(self::KEY_CACHE_MEMBER, $project->id);
            CacheHelper::forget(self::KEY_CACHE, $project->id);
            return $project->id;
        } catch (QueryException $ex) {
            DB::rollback();
            throw $ex;
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    /**
     * set value default for array input
     *
     * @param array $input
     * @param array $keyArray
     * @return array
     */
    protected static function setAssignDefaultValue(&$input, $keyArray) {
        foreach ($keyArray as $item) {
            if (!isset($input[$item])) {
                $input[$item] = null;
            }
        }
        return $input;
    }

    /*
     * get project by id
     * @param int
     * @param array
     */
    public static function getProjectById($id) {
        return self::find($id);
    }

    public static function savePurchaseId($request) {
        if (isset($request->projectId)) {
            $project = self::find($request->projectId);
            $project->po_id = $request->purchaseOrdId;
            $project->save();
            return $project;
        }
        return false;
    }

    public static function getTeamIdByProject($request)
    {
        return self::leftJoin('team_members', 'team_members.employee_id', '=', 'projs.leader_id')
            ->where('projs.id', $request->projectId)
            ->leftJoin('teams', 'teams.id', '=', 'team_members.team_id')
            ->select('teams.id', 'teams.name')
            ->get();
    }

    /**
     * Get projects from list id
     *
     * @param array $ids
     * @param array $columns
     *
     * @return Project collection
     */
    public static function getProjectByIds($ids, $columns = ['*'])
    {
        return self::whereIn('id', $ids)->select($columns)->get();
    }

    /*
     * check project exists
     * @param array
     * @return string
     */

    public static function checkExists($input) {
        if ($input['table'] == Project::TABLE_PROJECT) {
            if ($input['projectId']) {
                return self::where($input['name'], $input['value'])
                                ->where('status', self::STATUS_APPROVED)
                                ->whereNotIn('id', [$input['projectId']])->count() == Project::PROJECT_EXISTS ? 'true' : 'false';
            } else {
                return self::where($input['name'], $input['value'])
                                ->where('status', self::STATUS_APPROVED)
                                ->count() == Project::PROJECT_EXISTS ? 'true' : 'false';
            }
        } else {
            if ($input['projectId']) {
                return ProjectMeta::where($input['name'], $input['value'])
                                ->whereNotIn('project_id', [$input['projectId']])->count() == Project::PROJECT_EXISTS ? 'true' : 'false';
            } else {
                return ProjectMeta::where($input['name'], $input['value'])
                                ->count() == Project::PROJECT_EXISTS ? 'true' : 'false';
            }
        }
    }

    /*
     * Get all team of project
     * @parram int
     * @return array
     */

    public static function getAllTeamOfProject($id) {
        $project = self::find($id);
        if (!$project) {
            return;
        }
        $teams = array();
        foreach ($project->teamProject as $team) {
            array_push($teams, $team->id);
        }
        return $teams;
    }

    /*
     * delete project
     * @param array
     * @return booblean
     */

    public static function deleteProject($project) {
        DB::beginTransaction();
        try {
            $project->projectMeta()->delete();
            TeamProject::where('project_id', $project->id)->delete();
            $project->delete();
            DB::commit();
            return true;
        } catch (Exception $ex) {
            DB::rollback();
            return false;
        }
    }

    public function getProjectsJoined($isPqa = true, $isSale = true)
    {
        $tableTeamProject = TeamProject::getTableName();
        $tableTeam = Team::getTableName();
        $tableProject = self::getTableName();
        $tableEmployee = Employee::getTableName();
        $tableMember = ProjectMember::getTableName();
        $tableCustomer = Customer::getTableName();
        $tableCompany = Company::getTableName();

        $collection = self::select("{$tableProject}.id as project_id", "{$tableProject}.name as name", "{$tableProject}.cust_contact_id")
                ->leftJoin($tableTeamProject, "{$tableTeamProject}.project_id", '=', "{$tableProject}.id")
                ->leftJoin($tableTeam, "{$tableTeam}.id", '=', "{$tableTeamProject}.team_id")
                ->leftJoin($tableEmployee, "{$tableEmployee}.id", '=', "{$tableProject}.manager_id")
                ->groupBy("{$tableProject}.id")
                ->leftJoin($tableCustomer, "{$tableCustomer}.id", '=', "{$tableProject}.cust_contact_id")
                ->leftJoin($tableCompany, "{$tableCompany}.id", '=', "{$tableCustomer}.company_id");
        if (Team::isUseSoftDelete()) {
            $collection = $collection->whereNull("{$tableTeam}.deleted_at");
        }

        // check permission
        if (Permission::getInstance()->isScopeCompany(null, 'project::dashboard')) {

        } else { //view self project sale or PQA
            $tableSaleProject = SaleProject::getTableName();
            $collection->join($tableMember, "{$tableMember}.project_id", '=', "{$tableProject}.id")
                    ->leftJoin($tableSaleProject, "{$tableSaleProject}.project_id", '=', "{$tableProject}.id")
                    ->where(function ($query) use (
                            $tableMember, $tableSaleProject, $isPqa, $isSale
                            ) {
                        $userCurrent = Permission::getInstance()->getEmployee();
                        $query->orWhere(function($query) use (
                                $tableMember, $tableSaleProject, $userCurrent, $isPqa, $isSale
                                ) {
                            // PQA
                            if ($isPqa) {
                                $query->orwhere(function($query) use ($userCurrent, $tableMember) {
                                    $query->where("{$tableMember}.employee_id", $userCurrent->id)
                                        ->where("{$tableMember}.type", ProjectMember::TYPE_PQA)
                                        ->where("{$tableMember}.status", '=', ProjectMember::STATUS_APPROVED);
                                });
                            }
                            // Sale
                            if ($isSale) {
                                $query->orwhere("{$tableSaleProject}.employee_id", $userCurrent->id);
                            }
                        });
                    });
        }
        $collection->where("{$tableProject}.status", self::STATUS_APPROVED);
        $collection->whereNull("{$tableCustomer}.deleted_at");

        return $collection->get();
    }

    /**
     * get collection to show grid data
     *
     * @param datetime $firstWeek
     * @param datetime $lastWeek
     * @param false $isWatch
     * @return collection model
     */
    public static function getGridData($firstWeek = null, $lastWeek = null, $isWatch = false)
    {
        $tableTeamProject = TeamProject::getTableName();
        $tableTeam = Team::getTableName();
        $tableTeamMembers = TeamMember::getTableName();
        $tableProject = self::getTableName();
        $tableEmployee = Employee::getTableName();
        $tablePointFlat = ProjPointFlat::getTableName();
        $tableCustomer = Customer::getTableName();
        $tableCompany = Company::getTableName();
        $urlSubmitFilter = trim(URL::route('project::dashboard'), '/') . '/';
        if ($isWatch) $urlSubmitFilter = trim($urlSubmitFilter, '/') . '?' . $isWatch . '/';
        $pager = Config::getPagerData($urlSubmitFilter, ['limit' => 50]);

        $collection = self::select("{$tableProject}.id as project_id", "{$tableProject}.name as name", "{$tableProject}.state as state", "{$tableEmployee}.email as email", "{$tableProject}.type as type", $tableProject . '.end_at')
                ->leftJoin($tableTeamProject, "{$tableTeamProject}.project_id", '=', "{$tableProject}.id")
                ->leftJoin($tableTeam, "{$tableTeam}.id", '=', "{$tableTeamProject}.team_id")
                ->leftJoin($tableEmployee, "{$tableEmployee}.id", '=', "{$tableProject}.manager_id")
                ->groupBy("{$tableProject}.id")
                ->addSelect(DB::raw(
                "(SELECT GROUP_CONCAT(DISTINCT `t`.`name`) ".
                "FROM `{$tableTeam}` as `t` join `{$tableTeamProject}` as `t_p` on `t`.`id` = `t_p`.`team_id`".
                "where `t_p`.`project_id` = `{$tableTeamProject}`.`project_id`)".
                "as name_team"))
                ->addSelect("{$tableProject}.is_important")
                ->leftJoin($tableCustomer, function ($join) use ($tableCustomer, $tableProject){
                    $join->on("{$tableCustomer}.id", '=', "{$tableProject}.cust_contact_id")
                        ->whereNull("{$tableCustomer}.deleted_at");
                })
                ->leftJoin($tableTeamMembers, function ($join) use ($tableTeamMembers, $tableProject){
                    $join->on("{$tableTeamMembers}.employee_id", '=', "{$tableProject}.leader_id");
                })
                ->leftJoin("{$tableTeam} as leader_team", "leader_team.id", '=', "{$tableTeamMembers}.team_id")
                ->addSelect("leader_team.name as team_charge", "leader_team.id as team_charge_id")
                ->leftJoin($tableCompany, "{$tableCompany}.id", '=', "{$tableCustomer}.company_id")
                ->addSelect("{$tableCustomer}.id as customer_id", "{$tableCustomer}.name as customer_name", "{$tableCustomer}.name_ja as customer_name_jp", "{$tableCustomer}.email as customer_email", "{$tableCompany}.id as company_id", "{$tableCompany}.company as company_name", "{$tableCompany}.name_ja as company_name_ja");
        // variable store check view dashboard as baseline last week
        if (!$firstWeek) { // view dashboard
            $tableProjectPoint = ProjectPoint::getTableName();
            $collection->join($tablePointFlat, "{$tablePointFlat}.project_id", '=', "{$tableProject}.id");
            $collection
                    ->addSelect("{$tableProject}.id as id", "{$tablePointFlat}.summary", "{$tablePointFlat}.cost", "{$tablePointFlat}.quality", "{$tablePointFlat}.tl", "{$tablePointFlat}.proc", "{$tablePointFlat}.css", "{$tablePointFlat}.point_total", "{$tableProjectPoint}.raise", "{$tableProjectPoint}.raise_note")
                    ->leftJoin($tableProjectPoint, "{$tableProjectPoint}.project_id", '=', "{$tableProject}.id");
        } else { // view baseline
            $tableProjectPoint = ProjPointBaseline::getTableName();
            $tablePointFlat = $tableProjectPoint;
            $collection->join($tableProjectPoint, "{$tableProjectPoint}.project_id", '=', "{$tableProject}.id")
                    ->addSelect($tableProjectPoint . '.id', $tableProjectPoint . '.summary', $tableProjectPoint . '.cost', $tableProjectPoint . '.quality', $tableProjectPoint . '.tl', $tableProjectPoint . '.proc', $tableProjectPoint . '.css', $tableProjectPoint . '.point_total', $tableProjectPoint . '.project_evaluation', $tableProjectPoint . '.first_report', $tableProjectPoint . '.raise', $tableProjectPoint . '.raise_note', $tableProjectPoint . '.position')
                    ->whereDate("{$tableProjectPoint}.created_at", '>=', $firstWeek->format('Y-m-d H:i:s'))
                    ->whereDate("{$tableProjectPoint}.created_at", '<=', $lastWeek->format('Y-m-d H:i:s'));
        }

        if (Form::getFilterPagerData('order', $urlSubmitFilter)) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy("{$tableProject}.state", 'asc')
                    ->orderBy("{$tableProjectPoint}.position", 'desc')
                    ->orderBy("{$tableProjectPoint}.raise", 'asc')
                    ->orderBy("{$tablePointFlat}.summary", 'desc')
                    ->orderBy("{$tablePointFlat}.point_total", 'asc')
                    ->orderBy("{$tableProject}.created_at", 'desc')
                    ->orderBy("{$tableProject}.type", 'asc');
        }


        // check permission
        if (Permission::getInstance()->isScopeCompany(null, 'project::dashboard')) {
            if ($isWatch) {
                $projectIds = ProjectWatch::listMyTracking();
                $collection->whereIn('projs.id', $projectIds);
            }
        } elseif (Permission::getInstance()->isScopeTeam(null, 'project::dashboard')) {
            $collection = static::dashboardScopeTeam($collection, $isWatch);
        } else { //view self project
            $collection = static::DashboardScopeSelf($collection, $isWatch);
        }

        return $collection;
    }

    public static function gridDataFilter($collection, $isPager = true)
    {
        $tableTeamProject = TeamProject::getTableName();
        $tableProject = self::getTableName();
        $tableCompany = Company::getTableName();
        $urlSubmitFilter = trim(URL::route('project::dashboard'), '/') . '/';
        $pager = Config::getPagerData($urlSubmitFilter, ['limit' => 50]);
        //filter team charge
        $teamChargeFilter = Form::getFilterData('except', 'team_charge_id', $urlSubmitFilter);
        if ($teamChargeFilter) {
            $teamChargeFilter = (int) $teamChargeFilter;
            $arrayTeamChargeFilter = [$teamChargeFilter];
            $teamPath = Team::getTeamPath();
            if (isset($teamPath[$teamChargeFilter]) &&
                isset($teamPath[$teamChargeFilter]['child'])
            ) {
                $arrayTeamChargeFilter = array_merge($arrayTeamChargeFilter, (array) $teamPath[$teamChargeFilter]['child']);
            }
            $collection->whereIn("leader_team.id", $arrayTeamChargeFilter);
        }

        // filter team
        // $teamFilter = Form::getFilterData('exception', 'team_id', $urlSubmitFilter);
        // if ($teamFilter) {
        //     $teamFilter = (int) $teamFilter;
        //     $arrayTeamFilter = [$teamFilter];
        //     $teamPath = Team::getTeamPath();
        //     if (isset($teamPath[$teamFilter]) &&
        //         isset($teamPath[$teamFilter]['child'])
        //     ) {
        //         $arrayTeamFilter = array_merge($arrayTeamFilter, (array) $teamPath[$teamFilter]['child']);
        //     }
        //     $collection->whereIn("{$tableProject}.id", function ($query)
        //     use (
        //         $tableProject,
        //         $tableTeamProject,
        //         $arrayTeamFilter
        //     ) {
        //         $query->select("{$tableProject}.id")
        //             ->from($tableProject)
        //             ->join($tableTeamProject, "{$tableTeamProject}.project_id", '=', "{$tableProject}.id")
        //             ->whereIn('team_id', $arrayTeamFilter);
        //     });
        // }

        $arrayTeamFilter = Form::getFilterData('except', 'team_ids', $urlSubmitFilter);
        if ($arrayTeamFilter) {
            $collection->whereIn("{$tableProject}.id", function ($query) use ($tableProject, $tableTeamProject, $arrayTeamFilter) {
                $query->select("{$tableProject}.id")
                    ->from($tableProject)
                    ->join($tableTeamProject, "{$tableTeamProject}.project_id", '=', "{$tableProject}.id")
                    ->whereIn('team_id', $arrayTeamFilter);
            });
        }

        $companyFilter = Form::getFilterData('except', "{$tableCompany}.company", $urlSubmitFilter);
        if ($companyFilter) {
            $collection->where(function ($query) use ($tableCompany, $companyFilter) {
                $query ->where("{$tableCompany}.company", 'LIKE', addslashes("%$companyFilter%"))
                    ->orWhere("{$tableCompany}.name_ja", 'LIKE', addslashes("%$companyFilter%"));
            });
        }

        $companyFilter = Form::getFilterData('except', "{$tableCompany}.company", $urlSubmitFilter);
        if ($companyFilter) {
            $collection->where(function ($query) use ($tableCompany, $companyFilter) {
                $query  ->where("{$tableCompany}.company", 'LIKE', "%".addslashes(trim($companyFilter)) . "%")
                    ->orWhere("{$tableCompany}.name_ja", 'LIKE', "%".addslashes(trim($companyFilter)) . "%");
            });
        }

        $stateFilter = Form::getFilterData('exception', "{$tableProject}.state", $urlSubmitFilter);
        if ($stateFilter) {
            $collection->whereIn('projs.state', $stateFilter);
        } else {
            $collection->whereIn('projs.state', Project::getStateSFilterDefault());
        }

        $collection->where("{$tableProject}.status", self::STATUS_APPROVED);
        if (isset($projectIds) && $projectIds) {
            if (!is_array($projectIds)) {
                $projectIds = (array)$projectIds;
            }
            $collection = $collection->whereIn('projs.id', $projectIds);
        }

        self::filterGrid($collection, ['exception'], $urlSubmitFilter, $compare = 'LIKE');
        if($isPager) {
            self::pagerCollection($collection, $pager['limit'], $pager['page']);
            return $collection;
        }

        return $collection->GroupBy('projs.id')
            ->orderBy('projs.start_at', 'DESC')
            ->get();
    }


    private static function dashboardScopeTeam($collection, $isWatch = false)
    {
        $tableTeamProject = TeamProject::getTableName();
        $tableSaleProject = SaleProject::getTableName();
        $tableAssignee = TaskAssign::getTableName();
        $tableTask = Task::getTableName();
        $tableProject = self::getTableName();
        $tableCompany = Company::getTableName();
        $tableTeamMember = TeamMember::getTableName();
        $tableMember = ProjectMember::getTableName();
        $tablePqa = \Rikkei\Team\Model\PqaResponsibleTeam::getTableName();
        $projectIds = array();
        if ($isWatch) {
            $projectIds = ProjectWatch::listMyTracking();
        }
        $userCurrent = Permission::getInstance()->getEmployee();
        // Get các dự án mà user có task đc assign
        $listPicProject = Task::join($tableAssignee, "{$tableAssignee}.task_id", '=', "{$tableTask}.id")
                    ->where("{$tableAssignee}.employee_id", $userCurrent->id)
                    ->select('project_id')->get()->pluck('project_id')->toArray();
        // Get các dự án mà user tham gia dự án
        $listMemberProject = ProjectMember::where("{$tableMember}.employee_id", $userCurrent->id)
                    ->where("{$tableMember}.status", '=', ProjectMember::STATUS_APPROVED)
                    ->select('project_id')->get()->pluck('project_id')->toArray();
        $collection->leftJoin($tableSaleProject, "{$tableSaleProject}.project_id", '=', "{$tableProject}.id")

            ->leftJoin($tablePqa, "{$tablePqa}.team_id", '=', "{$tableTeamMember}.team_id")
            ->where(function($query) use ($userCurrent, $tableMember, $tableTeamProject, $tableSaleProject, $tableAssignee, $tableCompany, $tablePqa, $projectIds, $isWatch, $listPicProject, $listMemberProject) {
                $query->where(function($query) use ($userCurrent, $tableMember, $tableSaleProject, $tableAssignee, $tableCompany, $tablePqa, $tableTeamProject,$projectIds, $isWatch, $listPicProject, $listMemberProject) {
                    $statusApproved = ProjectMember::STATUS_APPROVED;
                    //$teams = Permission::getInstance()->getTeams();
                    $teams = Permission::getInstance()->isScopeTeam(null, 'project::dashboard');
                    //Get teams child from employee's teams
                    $teamsTemp = [];
                    foreach ($teams as $teamId) {
                        $teamsTemp = array_merge($teamsTemp, CheckpointPermission::getTeamChild($teamId));
                    }
                    $teams = array_unique($teamsTemp);
                    // Is member in project
                    $query->orWhereIn('projs.id', $listMemberProject);
                    //Filter by permission team
                    $query->orWhereIn("{$tableTeamProject}.team_id", $teams);
                    $query->orWhere(function($query) use ($statusApproved, $userCurrent) {
                        $query->join(DB::raw("(
                            select project_id
                            from project_members
                            where employee_id = {$userCurrent->id}
                            and status = {$statusApproved}) as projectIdTbl"), 'projs.id', '=','projectIdTbl.project_id'
                        );
                    })
                    // or where sale
                    ->orWhere("{$tableSaleProject}.employee_id", $userCurrent->id)
                    // or where assign
                    ->orWhereIn('projs.id', $listPicProject)
                    // or manager, supporter of customer of project
                    ->orWhere("{$tableCompany}.manager_id", $userCurrent->id)
                    ->orWhere("{$tableCompany}.sale_support_id", $userCurrent->id)
                    // or pqa of team
                    ->orWhere("{$tablePqa}.employee_id", $userCurrent->id);
                });
                if($isWatch){
                    $query->whereIn('projs.id', $projectIds);
                }
            });
        return $collection;
    }

    private static function dashboardScopeSelf($collection, $isWatch = false)
    {
        $tableSaleProject = SaleProject::getTableName();
        $tableAssignee = TaskAssign::getTableName();
        $tableTask = Task::getTableName();
        $tableMember = ProjectMember::getTableName();
        $tableProject = self::getTableName();
        $tableCompany = Company::getTableName();
        $tableTeamMembers = TeamMember::getTableName();
        $tablePqa = \Rikkei\Team\Model\PqaResponsibleTeam::getTableName();
        $tableTeamProject = TeamProject::getTableName();
        $tableTeam = Team::getTableName();

        $userCurrent = Permission::getInstance()->getEmployee();
        $projectIds = array();
        if ($isWatch) {
            $projectIds = ProjectWatch::listMyTracking();
        }

        $projectTaskAssign = self::join($tableTask, "{$tableTask}.project_id", '=', "{$tableProject}.id")
            ->join($tableAssignee, "{$tableAssignee}.task_id", '=', "{$tableTask}.id")
            ->where("{$tableAssignee}.employee_id", $userCurrent->id)
            ->select("{$tableProject}.id")
            ->get()->pluck('id')->toArray();
            
        $projectPQA = self::join($tableTeamMembers, function ($join) use ($tableTeamMembers, $tableProject){
                    $join->on("{$tableTeamMembers}.employee_id", '=', "{$tableProject}.leader_id");
                })
                ->join($tablePqa, "{$tablePqa}.team_id", '=', "{$tableTeamMembers}.team_id")
                ->where("{$tablePqa}.employee_id", $userCurrent->id)
                ->select("{$tableProject}.id")
                ->get()->pluck('id')->toArray();

        $collection->join($tableMember, "{$tableMember}.project_id", '=', "{$tableProject}.id")
            ->leftJoin($tableSaleProject, "{$tableSaleProject}.project_id", '=', "{$tableProject}.id")
            //->leftJoin($tableTask, "{$tableTask}.project_id", '=', "{$tableProject}.id")
            //->leftJoin($tableAssignee, "{$tableAssignee}.task_id", '=', "{$tableTask}.id")
            //->leftJoin($tablePqa, "{$tablePqa}.team_id", '=', "{$tableTeamMember}.team_id")
            ->where(function ($query) use (
                    $tableMember, $tableSaleProject, $tableAssignee, $tableCompany, $tablePqa, $userCurrent, $projectIds, $isWatch,
                    $projectTaskAssign, $projectPQA, $tableProject
                    ) {
                $query->orWhere(function($query) use (
                        $tableMember, $tableSaleProject, $userCurrent, $tableAssignee, $tableCompany, $tablePqa,
                        $projectTaskAssign, $projectPQA, $tableProject
                        ) { // or member
                    $query->orwhere(function($query) use ($userCurrent, $tableMember) {
                        $query->where("{$tableMember}.employee_id", $userCurrent->id)
                        ->where("{$tableMember}.status", '=', ProjectMember::STATUS_APPROVED);
                    })
                    // or where sale
                    ->orWhere("{$tableSaleProject}.employee_id", $userCurrent->id)
                    // or where assign
                    ->orWhereIn("{$tableProject}.id", $projectTaskAssign)
                    // or manager, supporter of customer of project
                    ->orWhere("{$tableCompany}.manager_id", $userCurrent->id)
                    ->orWhere("{$tableCompany}.sale_support_id", $userCurrent->id)
                    // or pqa of team
                    ->orWhereIn("{$tableProject}.id", $projectPQA);
                });
                if($isWatch){
                    $query->whereIn('projs.id', $projectIds);
                }
            });

        return $collection;
    }

    /**
     * get all team of a project
     *
     * @return object
     */
    public function getTeams() {
        if ($collection = CacheHelper::get(self::KEY_CACHE_TEAM, $this->id)) {
            return $collection;
        }
        $tableTeamProject = TeamProject::getTableName();
        $tableTeam = Team::getTableName();

        $collection = Team::select("{$tableTeam}.id", 'name')
                ->join($tableTeamProject, "{$tableTeamProject}.team_id", '=', "{$tableTeam}.id")
                ->where("{$tableTeamProject}.project_id", $this->id)
                ->get();
        CacheHelper::put(self::KEY_CACHE_TEAM, $collection, $this->id);
        return $collection;
    }

    /**
     * get teams of project format string
     *
     * @param object $teams
     * @return string
     */
    public function getTeamsString($teams = null) {
        if (!$teams) {
            $teams = $this->getTeams();
        }
        $result = '';
        foreach ($teams as $item) {
            $result .= $item->name . ', ';
        }
        $result = substr($result, 0, -2);
        return e($result);
    }

    /**
     * get team ids of a project
     *
     * @return array
     */
    public function getTeamIds() {
        if ($result = CacheHelper::get(self::KEY_CACHE_TEAM, $this->id)) {
            return $result;
        }
        $teams = $this->getTeams();
        $result = [];
        foreach ($teams as $team) {
            $result[] = $team->id;
        }
        CacheHelper::put(self::KEY_CACHE_TEAM, $result, $this->id);
        return $result;
    }

    /**
     * get all member of a project
     *
     * @return object
     */
    public function getMembers() {
        if ($collection = CacheHelper::get(self::KEY_CACHE_MEMBER, $this->id)) {
            return $collection;
        }
        $tableMember = ProjectMember::getTableName();
        $tableEmployee = Employee::getTableName();
        $tableProject = self::getTableName();
        $tableTeamMember = TeamMember::getTableName();
        $tblResPonsible = PqaResponsibleTeam::getTableName();
        $projectId = (int) $this->id;
        $today = date("Y-m-d");
        $typeSubPm = ProjectMember::TYPE_SUBPM;

        $approveStatus = ProjectMember::STATUS_APPROVED;
        $isDisabled = ProjectWOBase::STATUS_DISABLED;
        $softDeleteEmployee = $softDeleteProject = false;
        $coo = CoreConfigData::getCOOAccount();
        $qa = CoreConfigData::getQAAccount();
        if (Employee::isUseSoftDelete()) {
            $softDeleteEmployee = true;
        }
        if (Project::isUseSoftDelete()) {
            $softDeleteProject = true;
        }
        $expiredSubPm = DB::select("select GROUP_CONCAT(`{$tableMember}`.`id`) as id from `{$tableMember}` "
                        . "where `{$tableMember}`.`type` = '{$typeSubPm}' "
                        . "AND (`{$tableMember}`.`end_at` < '{$today}' OR  `{$tableMember}`.`start_at` > '{$today}') "
                        . "AND `{$tableMember}`.`project_id` = '{$projectId}' ");
        $expiredSubPm = reset($expiredSubPm)->id;
        $queryMember = "select `{$tableEmployee}`.`id` as `id`, "
                . "`{$tableEmployee}`.`name` as `name`, "
                . "`{$tableEmployee}`.`email` as `email`, "
                . "`{$tableMember}`.`type` as `type` "
                . "from `{$tableEmployee}` "
                . "inner join `{$tableMember}` "
                . "on `{$tableMember}`.`employee_id` = `{$tableEmployee}`.`id` "
                . "and `{$tableMember}`.`project_id` = '{$projectId}' "
                . "and `{$tableMember}`.`status` = '{$approveStatus}' "
                . "and `{$tableMember}`.`is_disabled` != '{$isDisabled}' ";
        if ($expiredSubPm) {
            $queryMember .= "WHERE `{$tableMember}`.`id` NOT IN ( {$expiredSubPm} ) ";
            if ($softDeleteEmployee) {
                $queryMember .= "AND `{$tableEmployee}`.`deleted_at` is null ";
            }
        } else {
            if ($softDeleteEmployee) {
                $queryMember .= "WHERE `{$tableEmployee}`.`deleted_at` is null ";
            }
        }
        $queryPm = "select `{$tableEmployee}`.`id` as `id`, "
                . "`{$tableEmployee}`.`name` as `name`, "
                . "`{$tableEmployee}`.`email` as `email`, "
                . ProjectMember::TYPE_PM . " as `type` "
                . "from `{$tableEmployee}` "
                . "inner join `{$tableProject}` "
                . "on `{$tableProject}`.`manager_id` = `{$tableEmployee}`.`id` "
                . "and `{$tableProject}`.`id` = '{$projectId}' ";
        if ($softDeleteProject) {
            $queryPm .= "and `{$tableProject}`.`deleted_at` is null ";
        }
        if ($softDeleteEmployee) {
            $queryPm .= "where `{$tableEmployee}`.`deleted_at` is null";
        }

        if ($coo) {
            $coo = '\'' . implode('\',\'', $coo) . '\'';
            $queryCoo = "select `{$tableEmployee}`.`id` as `id`, "
                    . "`{$tableEmployee}`.`name` as `name`, "
                    . "`{$tableEmployee}`.`email` as `email`, "
                    . ProjectMember::TYPE_COO . " as `type` "
                    . "from `{$tableEmployee}` "
                    . "where `email` in ({$coo}) ";
            if ($softDeleteEmployee) {
                $queryCoo .= "and `{$tableEmployee}`.`deleted_at` is null";
            }
        } else {
            $queryCoo = null;
        }
        if ($qa) {
            $qa = '\'' . implode('\',\'', $qa) . '\'';
            $queryqa = "select `{$tableEmployee}`.`id` as `id`, "
                    . "`{$tableEmployee}`.`name` as `name`, "
                    . "`{$tableEmployee}`.`email` as `email`, "
                    . ProjectMember::TYPE_QALEAD . " as `type` "
                    . "from `{$tableEmployee}` "
                    . "where `email` in ({$qa}) ";
            if ($softDeleteEmployee) {
                $queryqa .= "and `{$tableEmployee}`.`deleted_at` is null";
            }
        } else {
            $queryqa = null;
        }
        $queryTeamLeader = "select `{$tableTeamMember}`.`team_id`"
            . "from `{$tableTeamMember}` "
            . "left join `{$tableProject}` "
            . "on `{$tableTeamMember}`.`employee_id` = `{$tableProject}`.`leader_id` "
            . "and `{$tableProject}`.`id` = '{$projectId}' group by `team_id` ";

        if ($queryTeamLeader) {
            $queryqa = "select `{$tableEmployee}`.`id` as `id`, "
                . "`{$tableEmployee}`.`name` as `name`, "
                . "`{$tableEmployee}`.`email` as `email`, "
                . ProjectMember::TYPE_PQA . " as `type` "
                . "from `{$tableEmployee}` "
                . "inner join `{$tblResPonsible}` "
                . "on `{$tableEmployee}`.`id` = `{$tblResPonsible}`.`employee_id` "
                . "where `{$tblResPonsible}`.`team_id` in ({$queryTeamLeader}) ";
            if ($softDeleteEmployee) {
                $queryqa .= "and `{$tableEmployee}`.`deleted_at` is null";
            }
        } else {
            $queryqa = null;
        }
        // query select all member assign able
        $query = 'select `id`, `name`, `email`, GROUP_CONCAT(DISTINCT ' .
                '`project_member_union`.`type` SEPARATOR \',\') as `type` FROM (' .
                $queryMember . ' union all ' . $queryPm . ' union all ' .$queryqa;
        if ($queryqa) {
            $query .= ' union ' . $queryqa;
        }
        if ($queryCoo) {
            $query .= ' union ' . $queryCoo;
        }
        $query .= ')'
                . ' as `project_member_union` group by `project_member_union`.`id`';

        $collection = DB::select($query);
        CacheHelper::put(self::KEY_CACHE_MEMBER, $collection, $this->id);
        return $collection;
    }

    /**
     * get member ids of a project
     *
     * @return array
     */
    public function getMemberIds() {
        $member = $this->getMembers();
        $result = [];
        foreach ($member as $member) {
            $result[] = $member->id;
        }
        return $result;
    }

    /**
     * get member ids of a project
     *
     * @return array
     */
    public function getMembersKey() {
        $member = $this->getMembers();
        $result = [];
        foreach ($member as $member) {
            $result[$member->id] = $member;
        }
        return $result;
    }

    /**
     * get member follow id
     *
     * @return array
     */
    public function getMemberFollowId($id) {
        $member = $this->getMembers();
        foreach ($member as $member) {
            if ($member->id == $id) {
                return $member;
            }
        }
        return null;
    }

    /**
     * get member follow id
     *
     * @return array
     */
    public function getMemberFollowIds($ids) {
        $result = [];
        $member = $this->getMembers();
        foreach ($member as $member) {
            if (in_array($member->id, $ids)) {
                $result[] = $member;
            }
        }
        return $result;
    }

    /**
     * get member type of a project
     *
     * @return array
     */
    public function getMemberTypes() {
        $members = $this->getMembers();
        $result = [];
        foreach ($members as $member) {
            $type = $member->type;
            $type = explode(',', $type);
            if (in_array(ProjectMember::TYPE_PM, $type)) {
                $result[ProjectMember::TYPE_PM][] = $member->id;
                $result[ProjectMember::TYPE_PM]['obj'][] = $member;
            }
            if (in_array(ProjectMember::TYPE_SUBPM, $type)) {
                $result[ProjectMember::TYPE_SUBPM][] = $member->id;
                $result[ProjectMember::TYPE_SUBPM]['obj'][] = $member;
            }
            if (in_array(ProjectMember::TYPE_SQA, $type)) {
                $result[ProjectMember::TYPE_SQA][] = $member->id;
                $result[ProjectMember::TYPE_SQA]['obj'][] = $member;
            }
            if (in_array(ProjectMember::TYPE_PQA, $type)) {
                $result[ProjectMember::TYPE_PQA][] = $member->id;
                $result[ProjectMember::TYPE_PQA]['obj'][] = $member;
            }
            if (in_array(ProjectMember::TYPE_DEV, $type)) {
                $result[ProjectMember::TYPE_DEV][] = $member->id;
                $result[ProjectMember::TYPE_DEV]['obj'][] = $member;
            }
            if (in_array(ProjectMember::TYPE_COO, $type)) {
                $result[ProjectMember::TYPE_COO][] = $member->id;
                $result[ProjectMember::TYPE_COO]['obj'][] = $member;
            }
            if (in_array(ProjectMember::TYPE_QALEAD, $type)) {
                $result[ProjectMember::TYPE_QALEAD][] = $member->id;
                $result[ProjectMember::TYPE_QALEAD]['obj'][] = $member;
            }
            if (in_array(ProjectMember::TYPE_TEAM_LEADER, $type)) {
                $result[ProjectMember::TYPE_TEAM_LEADER][] = $member->id;
                $result[ProjectMember::TYPE_TEAM_LEADER]['obj'][] = $member;
            }
            if (in_array(ProjectMember::TYPE_BRSE, $type)) {
                $result[ProjectMember::TYPE_BRSE][] = $member->id;
                $result[ProjectMember::TYPE_BRSE]['obj'][] = $member;
            }
            if (in_array(ProjectMember::TYPE_COMTOR, $type)) {
                $result[ProjectMember::TYPE_COMTOR][] = $member->id;
                $result[ProjectMember::TYPE_COMTOR]['obj'][] = $member;
            }
        }
        return $result;
    }

    /**
     * get member have access wo
     *
     * @return array
     */
    public function getMemberAccessWO() {
        $member = $this->getMembers();
        $result = [];
        $arrayTypeAccess = [
            ProjectMember::TYPE_PQA,
            ProjectMember::TYPE_COO,
            ProjectMember::TYPE_QALEAD
        ];
        foreach ($member as $member) {
            $type = $member->type;
            $type = explode(',', $type);
            if (count(array_intersect($type, $arrayTypeAccess))) {
                $result['id'][] = $member->id;
                $result['obj'][] = $member;
            }
        }
        return $result;
    }

    /**
     * get member assign able
     *
     * @param
     * @return array
     */
    public function getMemberAccessWOAssign($task) {
        if (!$task->isTaskApproved()) {
            return null;
        }
        $member = $this->getMembers();
        $result = [];
        if ($task->status == Task::STATUS_SUBMITTED) {
            $arrayTypeAccess = [
                ProjectMember::TYPE_PQA,
                ProjectMember::TYPE_PM,
                ProjectMember::TYPE_QALEAD,
                ProjectMember::TYPE_COO,
                ProjectMember::TYPE_SUBPM
            ];
            foreach ($member as $member) {
                $type = $member->type;
                $type = explode(',', $type);
                if (count(array_intersect($type, $arrayTypeAccess))) {
                    $result['id'][] = $member->id;
                    $result['obj'][] = $member;
                }
            }
        } else if ($task->status == Task::STATUS_REVIEWED) {
            $arrayTypeAccess = [
                ProjectMember::TYPE_COO,
                ProjectMember::TYPE_PM,
                ProjectMember::TYPE_SUBPM
            ];
            foreach ($member as $member) {
                $type = $member->type;
                $type = explode(',', $type);
                if (count(array_intersect($type, $arrayTypeAccess))) {
                    $result['id'][] = $member->id;
                    $result['obj'][] = $member;
                }
            }
        } else {
            $arrayTypeAccess = [
                ProjectMember::TYPE_COO,
                ProjectMember::TYPE_PM,
                ProjectMember::TYPE_PQA,
                ProjectMember::TYPE_QALEAD,
                ProjectMember::TYPE_SUBPM
            ];
            foreach ($member as $member) {
                $type = $member->type;
                $type = explode(',', $type);
                if (count(array_intersect($type, $arrayTypeAccess))) {
                    $result['id'][] = $member->id;
                    $result['obj'][] = $member;
                }
            }
        }
        return $result;
    }

    /**
     * get project meta
     *
     * @return object
     */
    public function getProjectMeta() {
        CacheHelper::flush();
        if ($item = CacheHelper::get(self::KEY_CACHE, $this->id)) {
            return $item;
        }
        $item = ProjectMeta::where('project_id', $this->id)->first();
        if (!$item) {
            $item = ProjectMeta::findFromProject($this->id);
        }
        CacheHelper::put(self::KEY_CACHE, $item, $this->id);
        return $item;
    }

    /**
     * get task open title of project
     *
     * @param int $limit
     */
    public function getTaskTitle($limit = 10) {
        if ($titles = CacheHelper::get(self::KEY_CACHE_TASK, $this->id)) {
            return $titles;
        }
        $tasksCost = $this->getTaskTitleUnion(Task::TYPE_ISSUE_COST, $limit);
        $tasksQua = $this->getTaskTitleUnion(Task::TYPE_ISSUE_QUA, $limit);
        $tasksTl = $this->getTaskTitleUnion(Task::TYPE_ISSUE_TL, $limit);
        $tasksProc = $this->getTaskTitleUnion(Task::TYPE_ISSUE_PROC, $limit);
        $tasksCss = $this->getTaskTitleUnion(Task::TYPE_ISSUE_CSS, $limit);

        $tasks = $tasksCost->union($tasksQua)
                ->union($tasksTl)
                ->union($tasksProc)
                ->union($tasksCss)
                ->get();
        $titles = [
            Task::TYPE_ISSUE_COST => '',
            Task::TYPE_ISSUE_QUA => '',
            Task::TYPE_ISSUE_TL => '',
            Task::TYPE_ISSUE_PROC => '',
            Task::TYPE_ISSUE_CSS => '',
        ];
        if (!count($tasks)) {
            return $titles;
        }
        $keyType = array_keys($titles);
        foreach ($tasks as $task) {
            $type = $task->type;
            if (!in_array($type, $keyType)) {
                continue;
            }
            $titles[$type] .= '<p>';
            $titles[$type] .= '- ';
            $titles[$type] .= e($task->title);
            $titles[$type] .= '</p>';
        }
        CacheHelper::put(self::KEY_CACHE_TASK, $titles, $this->id);
        return $titles;
    }

    /**
     * get task title
     *
     * @param int $type
     * @param int $limit
     * @return object
     */
    protected function getTaskTitleUnion($type, $limit = 10) {
        return Task::select('title', 'type')
                        ->where('project_id', $this->id)
                        ->where('status', Task::STATUS_NEW)
                        ->where('type', $type)
                        ->orderBy('priority', 'desc')
                        ->orderBy('created_at', 'desc')
                        ->limit($limit);
    }

    /**
     * refresh flat data of point
     */
    public function refreshFlatDataPoint($isChangeColor = true) {
        return ProjPointFlat::flatItemProject($this, null, null, $isChangeColor);
    }

    /**
     * check project state open
     *
     * @return boolean
     */
    public function isOpen() {
        if (in_array($this->state, [
                    self::STATE_NEW,
                    self::STATE_PROCESSING,
                    self::STATE_OPPORTUNITY])
        ) {
            return true;
        }
        return false;
    }

    /**
     * check project type not need report
     *
     * @return boolean
     */
    public function isTypeTrainNotReport() {
        if (in_array($this->type, [self::TYPE_TRAINING, self::TYPE_RD])) {
            return true;
        }
        return false;
    }

    /**
     * check project state open
     *
     * @return boolean
     */
    public function isClosed() {
        if ($this->state == self::STATE_CLOSED) {
            return true;
        }
        return false;
    }

    /**
     * check project state open
     *
     * @return boolean
     */
    public function isApproved() {
        if ($this->status == self::STATUS_APPROVED) {
            return true;
        }
        return false;
    }

    /**
     * check can change dashboard
     * @return type
     */
    public function canChangeDashboard() {
        if (in_array($this->state, [
            self::STATE_CLOSED, self::STATE_NEW, self::STATE_PROCESSING
        ]) &&
            Permission::getInstance()->isCOOAccount() &&
            count(Input::get('css_css')) == 1
        ) {
            return true;
        }
        $endAt = Carbon::parse($this->end_at)->setTime(0, 0, 0);
        $weekendEnd = Carbon::now()->subWeek()->setTime(0, 0, 0);
        return !in_array($this->state, [self::STATE_CLOSED, self::STATE_PENDING, self::STATE_REJECT]) &&
                ($weekendEnd->lte($endAt) ||
                Permission::getInstance()->isCOOAccount());
    }

    /**
     * refresh data workorder
     * @param int
     */
    public function refreshDataWorkOrder($statusTask) {
        ProjectWOBase::sloveWorkorder($statusTask, $this->id);
    }

    /**
     * get customer contact
     * @return object
     */
    public function customerContact() {
        return $this->belongsTo('\Rikkei\Sales\Model\Customer', 'cust_contact_id', 'id');
    }

    /**
     * referesh data source server
     * @param int
     */
    public function refreshDataSourceServer($statusTask) {
        SourceServer::refreshDataSourceServer($statusTask, $this->id);
    }

    /**
     * get group leader of project
     * @return object
     */
    public function groupLeader() {
        return $this->belongsTo('\Rikkei\Team\Model\Employee', 'leader_id', 'id')->select('id', 'name', 'nickname', 'email');
    }

    /**
     * render automatic project code
     *  ex: 17003Pro_short_name
     *
     * @param self $project
     * @return string
     */
    public function renderProjectCodeAuto(&$fullTeamName = null) {
        $project = $this;
        if ($project->project_code_auto) {
            return $project->project_code_auto;
        }
        $year = $project->start_at->format('y');
        $countInYear = self::where('project_code_auto', 'like', "{$year}%")
                        ->withTrashed()->count();
        $countInYear++;
        $lengthAdditionCount = self::LENTH_PROJECT_CODE_NO - strlen($countInYear);
        for ($i = 0; $i < $lengthAdditionCount; $i++) {
            $countInYear = '0' . $countInYear;
        }

        // get short name team
        $nameShortTeam = self::getOnlyTeamName($project, $fullTeamName);
        //get short code slug
        $projectShortName = $project->project_code ? $project->project_code : $project->name;
        $projectShortName = Str::slug($projectShortName, '_');
        $code =  $year . $countInYear . $nameShortTeam . '_' . $projectShortName;
        $project->project_code_auto = $code;
        $project->save();
        return $code;
    }

    /**
     * get short team name of project
     *  if project have many teams, get team of leader
     *
     * @param type $project
     * @param type $fullName
     * @return type
     */
    public static function getOnlyTeamName($project, &$fullName = null) {
        $teams = $project->getTeams();
        if (!count($teams)) {
            return null;
        }
        $nameShortTeam = $teams[0]->name;
        $fullName = $nameShortTeam;
        $nameShortTeam = substr($nameShortTeam, 0, 3);
        $nameShortTeam = ucfirst(strtolower($nameShortTeam));
        if (!$project->leader_id) {
            return $nameShortTeam;
        }
        $tableTeam = Team::getTableName();
        $tableTeamMember = TeamMember::getTableName();

        $teamLeader = Team::select("{$tableTeam}.id", "{$tableTeam}.name")
                ->join($tableTeamMember, "{$tableTeamMember}.team_id", '=', "{$tableTeam}.id")
                ->where("{$tableTeamMember}.employee_id", $project->leader_id)
                ->get();
        if (!count($teamLeader)) {

        } else if (count($teamLeader) == 1) {
            $nameShortTeam = $teamLeader[0]->name;
            $fullName = $nameShortTeam;
            $nameShortTeam = substr($nameShortTeam, 0, 3);
            $nameShortTeam = ucfirst(strtolower($nameShortTeam));
        } else {
            $projectTeamIds = (array) $project->getTeamIds();
            foreach ($teamLeader as $teamLeaderItem) {
                if (in_array($teamLeaderItem->id, $projectTeamIds)) {
                    $nameShortTeam = $teamLeaderItem->name;
                    $fullName = $nameShortTeam;
                    $nameShortTeam = substr($nameShortTeam, 0, 3);
                    $nameShortTeam = ucfirst(strtolower($nameShortTeam));
                    break;
                }
            }
        }
        return $nameShortTeam;
    }

    /*
     * Get all sale employee of project
     * @parram int
     * @return array
     */

    public static function getAllSaleOfProject($id) {
        $project = self::find($id);
        if (!$project) {
            return;
        }
        $sales = array();
        foreach ($project->saleProject as $sale) {
            array_push($sales, $sale->id);
        }
        return $sales;
    }

    /**
     * get all member of a project
     *   member, sale, assignee
     * @return object
     */
    public function getAccessAbleIds($idCheck = null) {
        $tableMember = ProjectMember::getTableName();
        $tableEmployee = Employee::getTableName();
        $tableProject = self::getTableName();
        $tableSale = SaleProject::getTableName();
        $tableAssignee = TaskAssign::getTableName();
        $tableTeamMember = TeamMember::getTableName();
        $tblResPonsible = PqaResponsibleTeam::getTableName();
        $tableTask = Task::getTableName();
        $projectId = (int) $this->id;

        $softDeleteEmployee = $softDeleteProject = false;
        if (Employee::isUseSoftDelete()) {
            $softDeleteEmployee = true;
        }
        if (Project::isUseSoftDelete()) {
            $softDeleteProject = true;
        }
        //  get member project
        $statusApproved = self::STATUS_APPROVED;
        $queryMember = "select `{$tableEmployee}`.`id` as `id` "
                . "from `{$tableEmployee}` "
                . "inner join `{$tableMember}` "
                . "on `{$tableMember}`.`employee_id` = `{$tableEmployee}`.`id` "
                . "and `{$tableMember}`.`project_id` = '{$projectId}' "
                . "and `{$tableMember}`.`status` = '{$statusApproved}' ";
        if ($softDeleteEmployee) {
            $queryMember .= "where `{$tableEmployee}`.`deleted_at` is null";
        }
        // get pm project
        $queryPm = "select `{$tableEmployee}`.`id` as `id` "
                . "from `{$tableEmployee}` "
                . "inner join `{$tableProject}` "
                . "on `{$tableProject}`.`manager_id` = `{$tableEmployee}`.`id` "
                . "and `{$tableProject}`.`id` = '{$projectId}' ";
        if ($softDeleteProject) {
            $queryPm .= "and `{$tableProject}`.`deleted_at` is null ";
        }
        if ($softDeleteEmployee) {
            $queryPm .= "where `{$tableEmployee}`.`deleted_at` is null";
        }
        // get sale project
        $querySales = "select `{$tableEmployee}`.`id` as `id` "
                . "from `{$tableEmployee}` "
                . "inner join `{$tableSale}` "
                . "on `{$tableSale}`.`employee_id` = `{$tableEmployee}`.`id` "
                . "and `{$tableSale}`.`project_id` = '{$projectId}' ";
        if ($softDeleteEmployee) {
            $querySales .= "where `{$tableEmployee}`.`deleted_at` is null";
        }
        // get assign of task in project
        $queryAssign = "select `{$tableEmployee}`.`id` as `id` "
                . "from `{$tableEmployee}` "
                . "inner join `{$tableAssignee}` "
                . "on `{$tableAssignee}`.`employee_id` = `{$tableEmployee}`.`id` "
                . "inner join `{$tableTask}` "
                . "on `{$tableTask}`.`id` = `{$tableAssignee}`.`task_id` "
                . "and `{$tableTask}`.`project_id` = '{$projectId}' ";
        if ($softDeleteEmployee) {
            $queryAssign .= "where `{$tableEmployee}`.`deleted_at` is null";
        }

        // get manager of customer
        $tableCustomer = Customer::getTableName();
        $tableCompany = Company::getTableName();
        $queryManager = "Select `{$tableCompany}`.`manager_id` as id "
            . "from `{$tableCompany}` "
            . "inner join `{$tableCustomer}` "
            . "on `{$tableCustomer}`.`company_id` = `{$tableCompany}`.`id` "
            . "WHERE `{$tableCustomer}`.`id` = '" . $this->cust_contact_id . "'";

        // get supporter of customer
        $querySupporter = "Select `{$tableCompany}`.`sale_support_id` as id "
            . "from `{$tableCompany}` "
            . "inner join `{$tableCustomer}` "
            . "on `{$tableCustomer}`.`company_id` = `{$tableCompany}`.`id` "
            . "WHERE `{$tableCustomer}`.`id` = '" . $this->cust_contact_id . "'";

        //check pqa by group leader
        $queryTeamLeader = "select `{$tableTeamMember}`.`team_id`"
            . "from `{$tableTeamMember}` "
            . "left join `{$tableProject}` "
            . "on `{$tableTeamMember}`.`employee_id` = `{$tableProject}`.`leader_id` "
            . "and `{$tableProject}`.`id` = '{$projectId}' group by `team_id` ";

        if ($queryTeamLeader) {
            $queryqa = "select `{$tableEmployee}`.`id` as `id` "
                . "from `{$tableEmployee}` "
                . "inner join `{$tblResPonsible}` "
                . "on `{$tableEmployee}`.`id` = `{$tblResPonsible}`.`employee_id` "
                . "where `{$tblResPonsible}`.`team_id` in ({$queryTeamLeader}) ";
            if ($softDeleteEmployee) {
                $queryqa .= "and `{$tableEmployee}`.`deleted_at` is null";
            }
        } else {
            $queryqa = null;
        }

        // query select all member assign able
        $query = 'select `id` FROM (' .
                $queryMember . ' union all ' . $queryPm . ' union all ' . $querySales
                . ' union all ' . $queryAssign . ' union all ' . $queryManager . ' union all ' . $queryqa
                . ' union all ' . $querySupporter;
        $query .= ')'
                . ' as `project_member_union` ';
        if ($idCheck) {
            $query .= 'where `project_member_union`.`id` = \'' . $idCheck . '\' ';
        }
        $query .= 'group by `project_member_union`.`id`';
        $collection = DB::select($query);
        if ($idCheck) {
            if ($collection) {
                return true;
            }
            return false;
        }
        return $collection;
    }

    /**
     * get ids of access of project
     *
     * @param object $collection
     * @return array
     */
    public function getIdsEmployeeAccessViewProject($collection = null) {
        if (!$collection) {
            $collection = $this->getAccessAbleIds();
        }
        $ids = [];
        foreach ($collection as $item) {
            $ids[] = $item->id;
        }
        return $ids;
    }

    /**
     * get PM of project
     *
     * @return null|object
     */
    public function getPMActive() {
        $pm = $this->manager_id;
        if (!$pm) {
            return null;
        }
        return Employee::find($pm);
    }

    /**
     * get All PM of project approved and not approved
     *
     * @return null|object
     */
    public function getAllPMActive() {
        $pms = ProjectMember::getAllPmOfProject($this->id);
        if (!$pms) {
            return null;
        }
        return Employee::find($pms);
    }

    /**
     * get PM's ID of project approved or not approved
     * @param int
     * @return null|int
     */
    public static function getCurrentIdOfPM($projectId) {
        $pm_id = self::where('parent_id', $projectId)->first(); //find row not approved
        if (!$pm_id) {
            $pm_id = self::find($projectId); //find row approved
            if (!$pm_id) {
                return null;
            }
        }
        return $pm_id->manager_id;
    }

    /**
     * check project type is training
     * @return boolean
     */
    public function isTypeTrainingOfRD($type = null) {
        if (in_array($this->type, [Project::TYPE_TRAINING, Project::TYPE_RD, Project::TYPE_ONSITE])) {
            return true;
        }
        if (in_array($type, [Project::TYPE_TRAINING, Project::TYPE_RD, Project::TYPE_ONSITE])) {
            return true;
        }
        return false;
    }

    /*
     * check is type training or rd
     */
    public function isTypeTrainingOrRD()
    {
        return in_array($this->type, [self::TYPE_TRAINING, self::TYPE_RD]);
    }

    /**
     * get array project id of leader
     * @return array
     */
    public static function getProjectOfLeader() {
        $scope = Permission::getInstance();
        $project = self::where('state', self::STATE_PROCESSING)
                ->where('status', self::STATUS_APPROVED);
        $leaderId = $scope->getEmployee()->id;
        if ($scope->isScopeSelf(null, 'project::project.eval.list_by_leader')) {
            $project = $project->where('leader_id', $leaderId);
            return $project->lists('id')->toArray();
        }
        if ($scope->isScopeTeam(null, 'project::project.eval.list_by_leader')) {
            $teamMemberTbl = TeamMember::getTableName();
            $projTeamTbl = TeamProject::getTableName();
            $projectIds = TeamProject::join($teamMemberTbl . ' as tmb', $projTeamTbl . '.team_id', '=', 'tmb.team_id')
                    ->where('tmb.employee_id', $leaderId)
                    ->where($projTeamTbl . '.status', self::STATUS_APPROVED)
                    ->groupBy($projTeamTbl . '.project_id')
                    ->lists($projTeamTbl . '.project_id')
                    ->toArray();
            return $projectIds;
        }
        return $project->lists('id')->toArray();
    }

    /**
     * get project by type
     * @param int
     * @return array
     */
    public static function getProjectByTypes($types) {
        return self::whereIn('type', $types)
                        ->where('status', self::STATUS_APPROVED)
                        ->select('id', 'name')->get();
    }

    /**
     * get project by multi conditions
     *
     * @param array $conditions
     * @return Project collection
     */
    public static function getProjectByConditions($conditions) {
        $result = self::select('id');
        if ($conditions && is_array($conditions)) {
            foreach ($conditions as $field => $value) {
                $result->where($field, $value);
            }
        }
        return $result->get();
    }

    /**
     * edit project basic information
     * @param array
     * @param array
     * @return array
     */
    public static function editBasicInfo($data, $project) {
        $result = array();
        $result['status'] = false;
        if ($data['isApproved'] == 'true') {
            $projectDraft = self::where('parent_id', $project->id)
                    ->where('status', '!=', self::STATUS_APPROVED)
                    ->first();
            if (!$projectDraft) {
                $projectDraft = new Project();
                $projectDraft = $project->replicate();
                $projectDraft->status = self::STATUS_DRAFT;
                $projectDraft->project_code_auto = '';
                $projectDraft->parent_id = $project->id;
                $projectDraft->save();
                foreach ($project->teamProject as $team) {
                    $projectDraft->teamProject()->attach($team);
                }
            }
        } else {
            $projectDraft = $project;
        }
        if ($data['name'] == 'sale_id') {
            //Get old sale_id
            $saleOld = self::getAllSaleOfProject($projectDraft->id);
            //Delete old sale
            $projectDraft->saleProject()->detach($saleOld);
            if ($data['value']) {
                $projectDraft->saleProject()->attach($data['value']);
            }
        } else if ($data['name'] == 'team_id') {
            //Get old team
            $teamOld = self::getAllTeamOfProject($projectDraft->id);
            //Delete old tean
            $projectDraft->teamProject()->detach($teamOld);
            if ($data['value']) {
                $projectDraft->teamProject()->attach($data['value']);
            }
        } else if ($data['name'] == 'cust_contact_id') {
            if ($data['value']) {
                $project->cust_contact_id = $data['value'];
            } else {
                $project->cust_contact_id = null;
            }
            $project->save();
        } else if ($data['name'] == 'is_important') {
            $project->is_important = $data['value'];
            $project->save();
        } else if ($data['name'] == 'company_id') {
            $project->company_id = $data['value'];
            $project->save();
        } else {
            if ($data['name'] == 'state' && $data['value'] != 4) {
                $projectDraft->close_date = null;
            }
            $projectDraft->{$data['name']} = $data['value'];
        }
        if ($projectDraft->save()) {
            $result['status'] = true;
            $isChange = true;
            if ($projectDraft->{$data['name']} == $project->{$data['name']}) {
                $isChange = false;
            }
            $result['isChange'] = $isChange;
            //if change type_mm update flat resource
            if ($data['name'] == 'type_mm') {
                $result['flat_resources'] = ProjectMember::updateFlatResource($projectDraft);
            }
        }
        $result['duration'] = ViewProject::getDurationProject($project);
        CacheHelper::forget(self::KEY_CACHE, $project->id);
        return $result;
    }

    /**
     * update status when submit workorder
     * @param array
     * @param array
     */
    public static function updateStatusWhenSubmitWorkorder($task, $input) {
        $projectDraft = self::where('parent_id', $input['project_id'])
                ->whereIn('status', [self::STATUS_DRAFT, self::STATUS_FEEDBACK])
                ->first();
        if ($projectDraft) {
            $projectDraft->status = self::STATUS_SUBMITTED;
            $projectDraft->task_id = $task->id;
            $projectDraft->save();
        }
        CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
    }

    /**
     * get content task approved
     * @param int
     * @param array
     * @param string
     * @return string
     *
     */
    public static function getContentTaskApproved($typeWO, $input, $content, $typeSubmit = null) {
        $projectDraft = self::where('parent_id', $input['project_id']);
        $project = Project::getProjectById($input['project_id']);
        if ($typeSubmit) {
            $projectDraft = $projectDraft->whereIn('status', [
                self::STATUS_DRAFT,
                self::STATUS_DRAFT_EDIT,
                self::STATUS_FEEDBACK,
                self::STATUS_FEEDBACK_EDIT,
            ]);
        } else {
            $projectDraft = $projectDraft->where('status', self::STATUS_DRAFT);
        }
        $projectDraft = $projectDraft->first();
        if ($projectDraft) {
            $title = Lang::get('project::view.Edit basic info for project');
            $labelTypeProject = Project::labelTypeProjectFull();
            $labelStatusProject = Project::lablelState();
            $content .= view('project::template.content-task', [
                'inputs' => $projectDraft,
                'title' => $title,
                'type' => $typeWO,
                'project' => $project,
                'labelTypeProject' => $labelTypeProject,
                'labelStatusProject' => $labelStatusProject
                    ])->render();
        }
        return $content;
    }

    /**
     * update status when submit slove workorder
     * @param int
     * @param int
     */
    public static function updateStatusWhenSloveWorkorder($statusTask, $projectId) {
        $projectDraft = self::where('parent_id', $projectId)
                        ->where('status', '!=', self::STATUS_APPROVED)->first();
        if ($projectDraft) {
            if ($statusTask == Task::STATUS_APPROVED) {
                $project = self::getProjectById($projectId);
                $arrayField = ['name', 'manager_id', 'project_code', 'type',
                    'state', 'start_at', 'end_at', 'leader_id', 'type_mm', 'is_important', 'close_date'];
                foreach ($arrayField as $field) {
                    if (ViewProject::isChangeValueProject($projectDraft, $project, $field)) {
                        $project->$field = $projectDraft->$field;
                    }
                }
                if (ViewProject::isChangeValueProject($projectDraft, $project, 'team_id')) {
                    $allTeam = self::getAllTeamOfProject($project->id);
                    $allTeamDraft = self::getAllTeamOfProject($projectDraft->id);
                    $project->teamProject()->detach($allTeam);
                    $project->teamProject()->attach($allTeamDraft);
                    $projectDraft->teamProject()->detach($allTeamDraft);
                }
                $allTeamDraft = self::getAllTeamOfProject($projectDraft->id);
                TeamProject::where('project_id', $projectDraft->id)->forceDelete();
                ProjectMeta::where('project_id', $projectDraft->id)->forceDelete();
                ProjPointFlat::where('project_id', $projectDraft->id)->forceDelete();
                ProjectPoint::where('project_id', $projectDraft->id)->forceDelete();
                SaleProject::where('project_id', $projectDraft->id)->forceDelete();
                //$projectDraft->teamProject()->detach($allTeamDraft);echo '<pre>';var_dump($projectDraft);
                $project->save();
                $projectDraft->forceDelete();
            } else if ($statusTask == Task::STATUS_REVIEWED) {
                $projectDraft->status = self::STATUS_REVIEWED_EDIT;
                $projectDraft->save();
            } else if ($statusTask == Task::STATUS_FEEDBACK) {
                $projectDraft->status = self::STATUS_FEEDBACK_EDIT;
                $projectDraft->save();
            }
        }

        CacheHelper::forget(self::KEY_CACHE_WO, $projectId);
    }

    /**
     * check display button submit workorder
     * @param int
     * @return boolean
     */
    public static function checkStatuSubmit($projectId) {
        $project = self::getProjectById($projectId);
        $projectDraft = self::where('parent_id', $projectId)
                ->where('status', '!=', self::STATUS_APPROVED)
                ->first();
        if (!$projectDraft) {
            return false;
        }
        $status = false;
        $arrayField = ['name', 'manager_id', 'project_code', 'type', 'state', 'start_at', 'end_at', 'leader_id', 'team_id', 'type_mm', 'is_important'];
        foreach ($arrayField as $field) {
            if (ViewProject::isChangeValueProject($projectDraft, $project, $field)) {
                $status = true;
                break;
            }
        }
        return $status;
    }

    /**
     * update team join project
     * @param array
     * @param array
     * @return array
     */
    public static function updateTeam($data, $project) {
        $projectDraft = self::where('parent_id', $project->id)
                ->where('status', '!=', self::STATUS_APPROVED)
                ->first();
        if (!$projectDraft) {
            $projectDraft = new Project();
            $projectDraft = $project->replicate();
            $projectDraft->status = self::STATUS_DRAFT;
            $projectDraft->project_code_auto = '';
            $projectDraft->parent_id = $project->id;
            $projectDraft->save();
        }
        $result = array();
        $result['status'] = false;
        try {
            //Get old team
            $teamOld = self::getAllTeamOfProject($projectDraft->id);
            //Delete old tean
            $projectDraft->teamProject()->detach($teamOld);
            if ($data['value']) {
                $projectDraft->teamProject()->attach($data['value']);
            }
            $leaderIdDraf = $projectDraft->leader_id;
            $allTeam = self::getAllTeamOfProject($project->id);
            $teamsOptionAll = TeamList::toOption(null, true, false);
            $isCheck = false;
            foreach ($teamsOptionAll as $team) {
                if (in_array($team['value'], $data['value'])) {
                    if ($leaderIdDraf == $team['leader_id']) {
                        $isCheck = true;
                    }
                }
            }
            if (!$isCheck) {
                $projectDraft->leader_id = Team::getLeaderOfTeam($data['value'][0]);
                $projectDraft->save();
            }
            $result['isChange'] = ViewProject::compareTwoArray($data['value'], $allTeam);
            $result['status'] = true;
        } catch (Exception $ex) {
            throw new Exception($ex);
        }
        return $result;
    }

    /**
     * rewrite changes object after submit
     *
     * @param object $project
     * @param string $type
     */
    public static function getChangesAfterSubmit($project, $type = null) {
        $result = [];
        // basic info
        $projectEdit = self::where('parent_id', $project->id)
                        ->whereIn('status', [
                            self::STATUS_DRAFT,
                            self::STATUS_DRAFT_EDIT,
                            self::STATUS_FEEDBACK,
                            self::STATUS_FEEDBACK_EDIT,
                            self::STATUS_SUBMITTED,
                            self::STATUS_SUBMIITED_EDIT
                        ])->first();
        if (!$projectEdit) {
            $projectEdit = new self();
        }
        $changes = [];
        $columnChanges = self::getColumnChanges();
        $columnChanges['type_mm'] = 'Type resource';
        foreach ($columnChanges as $column => $label) {
            $changes[$column] = [
                TaskWoChange::FLAG_STATUS_EDIT_OLD =>
                ($project->{$column} instanceof DateTime) ?
                $project->{$column}->format('Y-m-d') : $project->{$column},
                TaskWoChange::FLAG_STATUS_EDIT_NEW =>
                ($projectEdit->{$column} instanceof DateTime) ?
                $projectEdit->{$column}->format('Y-m-d') : $projectEdit->{$column},
            ];
        }

        // state
        $labelsState = self::lablelState();
        if (isset($changes['state']) &&
                ($changes['state'][TaskWoChange::FLAG_STATUS_EDIT_NEW] != $changes['state'][TaskWoChange::FLAG_STATUS_EDIT_OLD])
        ) {
            $changes['state'][TaskWoChange::FLAG_STATUS_EDIT_NEW] = self::getLabelState($changes['state'][TaskWoChange::FLAG_STATUS_EDIT_NEW], $labelsState);
            $changes['state'][TaskWoChange::FLAG_STATUS_EDIT_OLD] = self::getLabelState($changes['state'][TaskWoChange::FLAG_STATUS_EDIT_OLD], $labelsState);
        }
        // Project Manager
        if (($project->manager_id != $projectEdit->manager_id) &&
                $project->manager_id && $projectEdit->manager_id
        ) {
            $managerOld = Employee::getNameEmailById($project->manager_id);
            if ($managerOld) {
                $managerOld = $managerOld->name . ' (' . $managerOld->email . ')';
            }
            $managerNew = Employee::getNameEmailById($projectEdit->manager_id);
            if ($managerNew) {
                $managerNew = $managerNew->name . ' (' . $managerNew->email . ')';
            }
            $changes['manager_id'] = [
                TaskWoChange::FLAG_STATUS_EDIT_OLD => $managerOld,
                TaskWoChange::FLAG_STATUS_EDIT_NEW => $managerNew
            ];
        }
        // billabe, plan effort
        $qualityApproved = ProjQuality::where('project_id', $project->id)
                ->where('status', self::STATUS_APPROVED)
                ->first();
        $qualityBillEdit = ProjQuality::getQualityDraft($project->id, 'billable_effort');
        $qualityPlanEdit = ProjQuality::getQualityDraft($project->id, 'plan_effort');
        $qualityProdCostEdit = ProjQuality::getQualityDraft($project->id, 'cost_approved_production');
        if ($qualityApproved) {
            if (!$qualityBillEdit) {
                $qualityBillEdit = new ProjQuality();
            }
            if (!$qualityPlanEdit) {
                $qualityPlanEdit = new ProjQuality();
            }
            if (!$qualityProdCostEdit) {
                $qualityProdCostEdit = new ProjQuality();
            }
            $changes['billable_effort'] = [
                TaskWoChange::FLAG_STATUS_EDIT_OLD => $qualityApproved->billable_effort,
                TaskWoChange::FLAG_STATUS_EDIT_NEW => $qualityBillEdit->billable_effort
            ];
            $changes['plan_effort'] = [
                TaskWoChange::FLAG_STATUS_EDIT_OLD => $qualityApproved->plan_effort,
                TaskWoChange::FLAG_STATUS_EDIT_NEW => $qualityPlanEdit->plan_effort
            ];
            $changes['cost_approved_production'] = [
                TaskWoChange::FLAG_STATUS_EDIT_OLD => $qualityApproved->cost_approved_production,
                TaskWoChange::FLAG_STATUS_EDIT_NEW => $qualityProdCostEdit->cost_approved_production
            ];
        }
        // team
        $projectTeamIds = TeamProject::getTeamIds($project->id);
        $projectEditTeamIds = TeamProject::getTeamIds($projectEdit->id);
        if ($projectEditTeamIds && $projectTeamIds &&
                (array_diff($projectEditTeamIds, $projectTeamIds) ||
                array_diff($projectTeamIds, $projectEditTeamIds))
        ) {
            $changes['team'] = [
                TaskWoChange::FLAG_STATUS_EDIT_OLD =>
                implode(', ', Team::getTeamsName($projectTeamIds)),
                TaskWoChange::FLAG_STATUS_EDIT_NEW =>
                implode(', ', Team::getTeamsName($projectEditTeamIds))
            ];
        }
        // leader
        if (($project->leader_id != $projectEdit->leader_id) &&
                $project->leader_id && $projectEdit->leader_id
        ) {
            $leaderOld = Employee::getNameEmailById($project->leader_id);
            if ($leaderOld) {
                $leaderOld = $leaderOld->name . ' (' . $leaderOld->email . ')';
            }
            $leaderNew = Employee::getNameEmailById($projectEdit->leader_id);
            if ($leaderNew) {
                $leaderNew = $leaderNew->name . ' (' . $leaderNew->email . ')';
            }
            $changes['leader'] = [
                TaskWoChange::FLAG_STATUS_EDIT_OLD => $leaderOld,
                TaskWoChange::FLAG_STATUS_EDIT_NEW => $leaderNew
            ];
        }
        // status
        $labelsType = self::labelTypeProject();
        if (isset($changes['type']) &&
            ($changes['type'][TaskWoChange::FLAG_STATUS_EDIT_NEW] != $changes['type'][TaskWoChange::FLAG_STATUS_EDIT_OLD])
        ) {
            $changes['type'][TaskWoChange::FLAG_STATUS_EDIT_NEW] = self::getLabelType($changes['type'][TaskWoChange::FLAG_STATUS_EDIT_NEW], $labelsType);
            $changes['type'][TaskWoChange::FLAG_STATUS_EDIT_OLD] = self::getLabelType($changes['type'][TaskWoChange::FLAG_STATUS_EDIT_OLD], $labelsType);
        }
        $lblProjectKind = ProjectKind::all()->pluck('kind_name','id')->toArray();
        // Project Kind
        $changes['kind_id'][TaskWoChange::FLAG_STATUS_EDIT_NEW] = isset($lblProjectKind[$projectEdit->kind_id]) ? $lblProjectKind[$projectEdit->kind_id] : null;
        $changes['kind_id'][TaskWoChange::FLAG_STATUS_EDIT_OLD] = isset($lblProjectKind[$project->kind_id]) ? $lblProjectKind[$project->kind_id] : null;

        $result[TaskWoChange::FLAG_BASIC_INFO][TaskWoChange::FLAG_STATUS_EDIT] = $changes;
        $result[TaskWoChange::FLAG_BASIC_INFO][TaskWoChange::FLAG_TYPE_TEXT] = TaskWoChange::FLAG_TYPE_SINGLE;

        return $result;
    }

    /**
     * get column name to compare changes
     *
     * @return array
     */
    public static function getColumnChanges() {
        return [
            'name' => 'Name',
            'state' => 'State',
            'start_at' => 'Start Date',
            'end_at' => 'End Date',
            'close_date' => 'Close Date',
            'team' => 'Team',
            'leader' => 'Leader',
            'billable_effort' => 'Billable Effort',
            'plan_effort' => 'Plan Effort',
            'cost_approved_production' => 'Approved production cost',
            'manager_id' => 'PM',
            'type' => 'Project type',
            'is_important' => 'Is important',
            'kind_id' => 'Project kind',
        ];
    }

    /**
     * get id and name of project
     *
     * @param int $empId
     * @param array $teamIds
     * @return object
     */
    public static function getProjectOptions($empId = null, $teamIds = null) {
        $projTable = self::getTableName();
        $projMemTable = ProjectMember::getTableName();
        $result = self::select("{$projTable}.id", 'name');

        if ($empId) {
            $result->join("{$projMemTable}", "{$projMemTable}.project_id", "=", "{$projTable}.id");
            $result->where("{$projMemTable}.employee_id", $empId);
            $result->where("{$projMemTable}.status", ProjectMember::STATUS_APPROVED);
        }
        if ($teamIds) {
            $teamProjTable = TeamProject::getTableName();
            $result->join("{$teamProjTable}", "{$teamProjTable}.project_id", "=", "{$projTable}.id");
            $result->whereIn("{$teamProjTable}.team_id", $teamIds);
        }
        $result->whereNull("{$projTable}.parent_id");
        $result->groupBy("{$projTable}.id");
        return $result->get();
    }

    /**
     * check if long project have enough month or project is closed
     *
     * */
    public function isLongOrClosed()
    {
        if ($this->isClosed()) {
            return true;
        }
        // TODO disble long project
        return false;
        $now = Carbon::today();
        if ($this->isLong() &&
            $now->gt($this->start_at) &&
            $now->diffInMonths($this->start_at) >= self::CYCLE_REWARD
        ) {
            return true;
        }
        return false;
    }

    /**
     * check project is long
     *
     * */
    public function isLong()
    {
        // TODO disble long project
        return false;
        $dateApply = CoreConfigData::get('project.reward_long.apply_date');
        if ($dateApply &&
            $this->start_at->lt(Carbon::parse($dateApply)) &&
            Carbon::parse($this->created_at)->lt(Carbon::parse($dateApply))
        ) {
            return false;
        }
        if ($this->end_at->diffInMonths($this->start_at) >= self::CYCLE_REWARD + 2) {
            return true;
        }
        return false;
    }

    /**
     * check current day is reward or not
     */
    public function isDayReward($day)
    {
        $currentDay = Carbon::today();
        if($currentDay->gte(Carbon::parse($day))) {
            return true;
        }
        return false;
    }

    /**
     * get month reward for long project
     * */
    public function getMonthReward()
    {
        // TODO disble long project
        return [];
        $start_time = Carbon::parse($this->start_at);
        $end_time = Carbon::parse($this->end_at);
        $months_reward = [];
        $month = $start_time;
        while ($end_time->diffInMonths($month) > self::CYCLE_REWARD + 1) {
            $month = $start_time->addMonths(self::CYCLE_REWARD);
            $months_reward[] = $month->toDateTimeString();
            $start_time = $month;
        }
        $months_reward[] = $end_time->toDateTimeString();
        return array_unique($months_reward);
    }

    /**
     * check this month is month reward or not
     * */
    public function checkMonthReward($monthReward) {
        if($this->isClosed()) {
            return true;
        }
        $currentDay = Carbon::today();
        if ($currentDay->gte(Carbon::parse($monthReward))) {
            return true;
        }
        return false;
    }

    /**
     * check type reward
     */
    public function isTypeReward() {
        if ($this->type == self::TYPE_BASE) {
            return true;
        }
        return false;
    }

    /**
     * check project have reward
     *
     * @param model $project
     * @return boolean
     */
    public static function rewardAvai($project) {
        if (!$project || !$project->isLongOrClosed() || !$project->isApproved() ||
                !$project->isTypeReward()) {
            return false;
        }
        $task = Task::findReward($project);

        if (!$task || !count($task)) {
            return false;
        }
        return $task;
    }

    /**
     * check projec is pendding
     * @return boolean
     */
    public function isPendding() {
        if ($this->state == self::STATE_PENDING ||
                $this->state == self::STATE_CLOSED ||
                $this->state == self::STATE_REJECT) {
            return true;
        }
        return false;
    }

    /**
     * show notice message close project
     * @return type
     */
    public function noticeToClose() {
        if ($this->state != self::STATE_PROCESSING) {
            return null;
        }
        $endDate = Carbon::parse($this->end_at);
        $now = Carbon::now();
        if ($now->subDay(30)->gte($endDate)) {
            return '<div class="notice-message">'
                    . '<p class="alert alert-warning">' .
                    trans('project::message.The end date of project is over a month, please close project') .
                    '</p>'
                    . '</div>';
        }
        return null;
    }

    /**
     * Send mail to PM project
     */
    public static function sendMailReportToPmProject()
    {
        $projTb = self::getTableName();
        $collection = self::where($projTb.'.status', self::STATUS_APPROVED)
        ->where($projTb.'.state', self::STATE_PROCESSING)
        ->whereDate('end_at', '>=', Carbon::today())
        ->whereDate('start_at', '<', Carbon::tomorrow())
        ->whereIn('type', [Project::TYPE_OSDC, Project::TYPE_BASE])
        ->with(['employeePm' => function ($q) {
            return $q->select('id', 'name', 'email');
        }])->get();
        if (empty($collection)) {
            return;
        }
        $listManager = [];
        $listProjectFollowManagerId = [];
        foreach ($collection as $item) {
           if (!in_array($item->manager_id, $listManager)) {
                array_push($listManager, $item->manager_id);
                $listProjectFollowManagerId[$item->manager_id] = [$item];
           } else {
               array_push($listProjectFollowManagerId[$item->manager_id], $item);
           }
        }
        $subject = trans('project::email.Remind project report subject');
        foreach ($listProjectFollowManagerId as $key => $itemList) {
            if (!empty($itemList[0]->employeePm)) {
                $listProject = [];
                foreach ($itemList as $item) {
                    array_push($listProject, ['route' => route('project::point.edit', ['id' => $item->id]), 'name' => $item['name']]);
                }
                $data = [
                    'projects' => $listProject,
                    'pm_name' => $itemList[0]->employeePm->name,
                ];
                $emailQueue = new EmailQueue();
                $emailQueue->setTo($itemList[0]->employeePm->email, $itemList[0]->employeePm->name)
                ->setTemplate('project::emails.remind_report_project', $data)
                ->setSubject($subject)
                ->save();
            }
        }
    }

    /**
     * send email note PM close project processing had expired end date 30 days
     * @return type
     */
    public static function noticeCloseProjectMail() {
        $now = Carbon::now();
        $projTbl = self::getTableName();
        $projMemberTbl = ProjectMember::getTableName();
        $employeeTbl = Employee::getTableName();
        $collection = self::join($projMemberTbl . ' as pm', function ($join) use ($projTbl) {
                    $join->on($projTbl . '.id', '=', 'pm.project_id')
                    ->where('pm.status', '=', ProjectMember::STATUS_APPROVED)
                    ->where('pm.type', '=', ProjectMember::TYPE_PM)
                    ->where('pm.is_disabled', '!=', ProjectMember::STATUS_DISABLED);
                })
                ->join($employeeTbl . ' as emp', 'pm.employee_id', '=', 'emp.id')
                ->where($projTbl . '.state', self::STATE_PROCESSING)
                ->where($projTbl . '.status', self::STATUS_APPROVED)
                ->where($projTbl . '.end_at', '<=', $now->subDay(30)->toDateTimeString())
                ->groupBy('emp.id')
                ->select(
                    DB::raw('GROUP_CONCAT(DISTINCT ' . $projTbl . '.name SEPARATOR ", ") as project_names'),
                    'emp.name as employee_name',
                    'emp.email as employee_email',
                    'emp.id'
                )
                ->get();
        if ($collection->isEmpty()) {
            return;
        }
        foreach ($collection as $item) {
            $data = [
                'project_names' => $item->project_names,
                'pm_name' => $item->employee_name
            ];
            $contentDetail = RkNotify::renderSections('project::emails.alert_close_proj', $data);
            $emailQueue = new EmailQueue();
            $subject = trans('project::email.[Project dashboard] Notice close project(s)');
            $emailQueue->setTo($item->employee_email, $item->employee_name)
                    ->setTemplate('project::emails.alert_close_proj', $data)
                    ->setSubject($subject)
                    ->setNotify($item->id, $subject . ' on project "'. $item->project_names .'"', route('project::dashboard'),
                        ['icon' => 'project.png', 'category_id' => RkNotify::CATEGORY_PROJECT, 'content_detail' => $contentDetail])
                    ->save();
        }
    }

    /**
     * check time end projec when pendding project
     * @param int
     * @return boolean
     */
    public static function checkTimeEndProjectWhenPendding($project) {
        $projectChild = $project->projectChild;
        $now = Carbon::now();
        $now->hour = 0;
        $now->minute = 0;
        $now->second = 0;
        if (count($projectChild)) {
            if ($projectChild->end_at > $now) {
                return true;
            }
        } else {
            if ($project->end_at > $now) {
                return true;
            }
        }
        return false;
    }

    public static function searchAjax($name, array $config = []) {
        $result = [];
        $arrayDefault = [
            'page' => 1,
            'limit' => 20,
            'typeExclude' => null
        ];
        $config = array_merge($arrayDefault, $config);
        $collection = self::select(['id', 'name'])
                ->where('name', 'LIKE', '%' . $name . '%')
                ->where('status', self::STATUS_APPROVED)
                ->orderBy('name');

        self::pagerCollection($collection, $config['limit'], $config['page']);
        foreach ($collection as $item) {
            $result['items'][] = [
                'id' => $item->id,
                'text' => $item->name,
            ];
        }
        return $result;
    }

    /**
     * return infor for email follow month reward
     * */
    public static function getInforProjectEmail($monthReward) {
        $taskTable = Task::getTableName();
        $taskMetaTable = ProjRewardMeta::getTableName();
        $empTable = Employee::getTableName();
        $projTable = self::getTableName();
        $collection = self::join($taskTable, $projTable . '.id', '=', $taskTable . '.project_id')
                ->join($taskMetaTable, $taskTable . '.id', '=', $taskMetaTable . '.task_id')
                ->join($empTable, $projTable . '.manager_id', '=', $empTable . '.id')
                ->where($taskTable.'.type', '=', Task::TYPE_REWARD)
                ->where($taskMetaTable . '.month_reward', '=', $monthReward)
                ->select($empTable.'.id as pm_id',$empTable . '.name as pm_name', $empTable . '.email as pm_email', $taskMetaTable . '.month_reward as month_reward', $projTable . '.name as proj_name', $projTable . '.id as proj_id', $taskTable . '.id as task_id')
                ->get();
        $result = [];
        foreach ($collection as $key => $item) {
            $result[$item->pm_id]["pm_name"] = $item->pm_name;
            $result[$item->pm_id]['pm_email'] = $item->pm_email;
            $result[$item->pm_id]['month_reward'] = $item->month_reward;
            $result[$item->pm_id]['pm_project'][] = [
                "proj_id" => $item->proj_id,
                "task_id" => $item->task_id,
                "proj_name" => $item->proj_name,
            ];
        }
        return $result;
    }

    /**
     * Get billable effort, actual effort in a month
     *
     * @param int $month
     * @param int $year
     * @param int|null $teamId
     * @return Collection
     */
    public static function getBillActualEffortInMonth($month, $year, $teamId = null)
    {
        $projTable = Project::getTableName();
        $teamTable = Team::getTableName();
        $projQuality = ProjQuality::getTableName();
        $projPoint = ProjectPoint::getTableName();
        $firstLastMonth = ResourceView::getInstance()->getFirstLastDaysOfMonth($month, $year);
        $firstDay = $firstLastMonth[0];
        $lastDay = $firstLastMonth[1];
        $result = Project::leftJoin("{$projQuality}", "{$projQuality}.project_id", "=", "{$projTable}.id")
                    ->leftJoin("{$projPoint}", "{$projPoint}.project_id", "=", "{$projTable}.id")
                    ->where("{$projTable}.start_at", "<=", $lastDay)
                    ->where("{$projTable}.end_at", ">=", $firstDay)
                    ->where('state', '<>', Project::STATE_NEW)
                    ->where("{$projQuality}.status", ProjQuality::STATUS_APPROVED)
                    ->where("{$projTable}.type", "<>", Project::TYPE_TRAINING)
                    ->where("{$projTable}.type", "<>", Project::TYPE_RD)
                    ->selectRaw("billable_effort, "
                            . "cost_actual_effort , "
                        . "type_mm, "
                        . "{$projTable}.id, "
                        . "{$projTable}.name, "
                            . "{$projTable}.leader_id"
                    )
                    ->groupBy("{$projTable}.id")
                    ->orderByRaw("IF(type_mm = " . Project::MM_TYPE . ", billable_effort, billable_effort/20) desc");
        $result->join('employee_team_history', 'employee_team_history.employee_id', '=', "{$projTable}.leader_id")
                ->whereRaw("(DATE(employee_team_history.start_at) <= DATE(?) or employee_team_history.start_at is null) and (DATE(employee_team_history.end_at) >= DATE(?) or employee_team_history.end_at is null)", [$lastDay, $firstDay]);
        //Show only team is software development
        $result->leftJoin("{$teamTable}", "employee_team_history.team_id" , "=", "{$teamTable}.id");
        $result->where("{$teamTable}.is_soft_dev", Team::IS_SOFT_DEVELOPMENT);
        if (!empty($teamId)) {
            $result->where('employee_team_history.team_id', $teamId);
        }
        return $result->get();
    }

    /**
     * Get project has not sale
     *
     * @return Project collection
     */
    public static function getProjectsHasNotSale()
    {
        $projTable = self::getTableName();
        $saleProjTable = SaleProject::getTableName();
        return self::whereNotIn("id", function ($query) use ($saleProjTable) {
                        $query->select('project_id')
                        ->from("{$saleProjTable}");
                    })
                ->select('id')
                ->get();
    }

    public static function getEmpByRoleNotApproveInProject($projectId, $role)
    {
        $projMemTable = ProjectMember::getTableName();
        $projTable = Project::getTableName();
        $empTable = Employee::getTableName();
        return self::join("{$projMemTable}", "{$projMemTable}.project_id", "=", "{$projTable}.id")
            ->join("{$empTable}", "{$empTable}.id", "=", "{$projMemTable}.employee_id")
            ->whereDate("{$projMemTable}.end_at", '>=', Carbon::now()->format('Y-m-d'))
            ->whereDate("{$projMemTable}.start_at", '<=', Carbon::now()->format('Y-m-d'))
            ->where("{$projMemTable}.type", $role)
            ->where("{$projMemTable}.project_id", $projectId)
            ->select("{$empTable}.*")
            ->get();
    }

    /**
     * Get employees by role in project
     *
     * @param int $role
     * @return type
     */
    public static function getEmpByRoleInProject($projectId, $role)
    {
        $projMemTable = ProjectMember::getTableName();
        $projTable = Project::getTableName();
        $empTable = Employee::getTableName();
        return self::join("{$projMemTable}", "{$projMemTable}.project_id", "=", "{$projTable}.id")
                ->join("{$empTable}", "{$empTable}.id", "=", "{$projMemTable}.employee_id")
                ->where("{$projMemTable}.type", $role)
                ->where("{$projMemTable}.status", self::STATUS_APPROVED)
                ->where("{$projMemTable}.project_id", $projectId)
                ->select("{$empTable}.*")
                ->get();
    }

    /**
     * Get leader, pm, pqa, salers of project
     * Get more owner if task is risk type
     *
     * @param Project $project
     * @param Risk $risk
     *
     * @return null|Employee collection
     */
    public static function getRelatersOfProject($project, $risk = null, $isDleadPQA = false)
    {
        $saleIdsOfProj = Project::getAllSaleOfProject($project->id);
        $relaterIds = array_merge($saleIdsOfProj, [(int)$project->manager_id, (int)$project->leader_id]);
        $pqaInProject = Project::getEmpByRoleInProject($project->id, ProjectMember::TYPE_PQA);
        if (!empty($pqaInProject)) {
            foreach ($pqaInProject as $pqa) {
                $relaterIds[] = $pqa->id;
            }
        }
        if ($risk && $risk->owner) {
            $relaterIds[] = $risk->owner;
        }
        if ($isDleadPQA) {
            $idLeader = Team::getLeaderOfTeam(Team::TEAM_PQA_ID);
            $relaterIds[] = $idLeader;
        }
        $idPqaTeam = PqaResponsibleTeam::getPqaResponsibleTeamOfProjs($project->id);
        if (isset($idPqaTeam)) {
            foreach($idPqaTeam as $value) {
                $relaterIds[] = $value['employee_id'];
            }
        }

        if (!empty($relaterIds)) {
            $relaterIds = array_unique($relaterIds);
            $relater = Employee::getEmpByIds($relaterIds);
            return $relater;
        }
        return null;
    }

    public static function getTeamInChargeOfProject($projectId)
    {
        return self::select('team_members.team_id', 'teams.name')
            ->leftJoin('team_members', 'projs.leader_id', '=', 'team_members.employee_id')
            ->leftJoin('teams', 'team_members.team_id', '=', 'teams.id')
            ->where('projs.id', $projectId)
            ->first();
    }

    public static function getRelatersOfProjectByIssue($project, $issue = null)
    {
        $relaterIds = [];
        $getTeamInChargeOfProject = self::getTeamInChargeOfProject($project->id);
        if ($issue['type'] == Task::TYPE_CRITICIZED) {
            $saleIdsOfProj = Project::getAllSaleOfProject($project->id);
            $leaderTeamPqa = Team::getLeaderOfTeam(Team::TEAM_PQA_ID);
            $relaterIds = array_merge($saleIdsOfProj, [(int)$project->manager_id, (int)$project->leader_id]);
            if (isset($leaderTeamPqa)) {
                $relaterIds = array_merge([(int)$leaderTeamPqa], $relaterIds);
            }
            $pqaTeamCharge = PqaResponsibleTeam::getEmpIdResponsibleTeamAsTeamId($getTeamInChargeOfProject->team_id);
            if (isset($pqaTeamCharge)) {
                $relaterIds = array_merge($pqaTeamCharge, $relaterIds);
            }
            $pqaInProject = Project::getEmpByRoleInProject($project->id, ProjectMember::TYPE_PQA);
            if (!empty($pqaInProject)) {
                foreach ($pqaInProject as $pqa) {
                    $relaterIds[] = $pqa->id;
                }
            }
        }
        if ($issue && isset($issue['issue'])) {
            foreach($issue['issue'] as $value) {
                $relaterIds[] = $value['assignee'];
            }
        }

        if (!empty($relaterIds)) {
            $relaterIds = array_unique($relaterIds);
            $relater = Employee::getEmpByIdsNotLeave($relaterIds);
            return $relater;
        }
        return null;
    }

    /**
     * Get group leader information: id, email, name
     * @return [object]
     */
    public function getInformationGroupLeader()
    {
        $tableTeam = Team::getTableName();
        $tableTeamMember = TeamMember::getTableName();
        $tableRole = Role::getTableName();

        return Employee::select('employees.id', 'employees.email', 'employees.name', 't_team.id as team_id', 't_team_mem.role_id as role_id')
            ->join($tableTeamMember . ' AS t_team_mem', 't_team_mem.employee_id', '=', 'employees.id')
            ->join($tableTeam . ' AS t_team', 't_team.id', '=', 't_team_mem.team_id')
            ->join($tableRole . ' AS t_role', 't_role.id', '=', 't_team_mem.role_id')
            ->whereNull('employees.deleted_at')
            ->whereNull('t_team_mem.deleted_at')
            ->whereNull('t_team.deleted_at')
            ->whereNull('t_role.deleted_at')
            ->where('t_role.special_flg', Role::FLAG_POSITION)
            ->whereIn('t_team_mem.role_id', [Team::ROLE_TEAM_LEADER, Team::ROLE_SUB_LEADER])
            ->where('employees.id', $this->leader_id)
            ->first();
    }

    /**
     * Get Lead and Sub of Division in Charge of
     * And Lead And Sub Of Sivision Partners
     *
     * @param array $leaderAndSubProject
     * @param object $groupleaderInformation
     *
     * @return array
     */
    public static function classifyLeadAndSubForEachTypeDivision($leaderAndSubProject, $groupleaderInformation)
    {
        // Check if group leader not exist in $leaders
        if ($groupleaderInformation && !array_key_exists($groupleaderInformation->id, $leaderAndSubProject)) {
            $leaderAndSubProject[$groupleaderInformation->id] = [
                'id' => $groupleaderInformation->id,
                'name' => $groupleaderInformation->name,
                'email' => $groupleaderInformation->email,
                'team_id' => $groupleaderInformation->team_id,
                'role_id' => $groupleaderInformation->role_id,
            ];
        }
        $leadersAndSubOfDivPartnersIds = [];
        $leadersAndSubOfDivInChargedIds = [];
        foreach ($leaderAndSubProject as $key => $value) {
            if (!empty($groupleaderInformation) && $value['team_id'] === $groupleaderInformation->team_id) {
                $leadersAndSubOfDivInChargedIds[] = $value['id'];
            } else {
                $leadersAndSubOfDivPartnersIds[] = $value['id'];
            }
        }

        return [
            'leadSubInCharged' => $leadersAndSubOfDivInChargedIds,
            'leadSubPartners' => $leadersAndSubOfDivPartnersIds
        ];
    }
    /**
     * Get Division Partners
     *
     * @return array
     */
    public static function getDivisionPartners($allTeams, $divInCharged)
    {
        return array_diff($allTeams, [$divInCharged]);
    }


    /**
     * Get projects with start at older than 1 week aog unreviewed reward budget
     * Select tbl projs.*, employee.email
     *
     * @return Project collection
     */
    public static function getProjectUnreviewedRewardBudget()
    {
        $tblProj = Project::getTableName();
        $tblTask = Task::getTableName();
        $tblProjMeta = ProjectMeta::getTableName();
        $tblEmployee = Employee::getTableName();
        return Project::join("{$tblTask}", "{$tblTask}.project_id", "=", "{$tblProj}.id")
                //Get only projects state `New` and `Processing`
                ->whereIn("{$tblProj}.state", [Project::STATE_NEW, Project::STATE_PROCESSING])
                //Get only projects were approved
                ->where("{$tblProj}.status", Project::STATUS_APPROVED)
                ->where("{$tblTask}.type", Task::TYPE_WO)
                ->where("{$tblTask}.status", Task::STATUS_APPROVED)
                //Get only project type is base
                ->where("{$tblProj}.type", Project::TYPE_BASE)
                //Get only projects reward budget is not review
                ->join("{$tblProjMeta}", "{$tblProjMeta}.project_id", "=", "{$tblProj}.id")
                ->where(function ($query) use ($tblProjMeta) {
                    $query->where("{$tblProjMeta}.is_show_reward_budget", ProjectMeta::REWARD_BUGGET_SUBMIT)
                          ->orWhereNull("{$tblProjMeta}.is_show_reward_budget");
                })
                //Get only projects start older than 1 week ago
                ->whereRaw("DATE({$tblProj}.start_at) <= CURDATE() - INTERVAL 1 WEEK")
                //Join tbl employees to get leader's email, name of project
                ->join("{$tblEmployee}", "{$tblEmployee}.id", "=", "{$tblProj}.leader_id")
                //Select data
                ->groupBy("{$tblProj}.id")
                ->select("{$tblProj}.id", "{$tblProj}.name", "{$tblProj}.leader_id", "{$tblEmployee}.email as leader_email", "{$tblEmployee}.name as leader_name")
                ->get();
    }

    /*
     * program languages relationship
     */
    public function projectLanguages()
    {
        return $this->belongsToMany('\Rikkei\Resource\Model\Programs', ProjectProgramLang::getTableName(), 'project_id', 'prog_lang_id');
    }

    /*
     * list array type mm
     */
    public static function arrayTypeMM()
    {
        return [
            self::MD_TYPE => 'MD',
            self::MM_TYPE => 'MM'
        ];
    }

    /**
     * Get projects list of customer
     * @param int $cusId
     * @param int|null $companyId
     * @return object
     */
    public function getProjectsOfCustomer($cusId, $companyId = null)
    {
        $projTbl = self::getTableName();
        $projTeamTbl = TeamProject::getTableName();
        $teamTbl = Team::getTableName();
        $empTbl = Employee::getTableName();
        return self::join("{$projTeamTbl}", "{$projTeamTbl}.project_id", "=", "{$projTbl}.id")
                ->join("{$teamTbl}", "{$projTeamTbl}.team_id", "=", "{$teamTbl}.id")
                ->join("{$empTbl}", "{$projTbl}.manager_id", "=", "{$empTbl}.id")
                ->where(function ($query) use($cusId, $companyId, $projTbl){
                    if ($companyId) {
                        $query->where("{$projTbl}.company_id", $companyId);
                    } else {
                        $query->where("{$projTbl}.cust_contact_id", $cusId);
                    }

                })
                ->where("{$projTbl}.status", self::STATUS_APPROVED)
                ->groupBy("{$projTbl}.id")
                ->select(
                    "{$projTbl}.id",
                    "{$projTbl}.name",
                    "{$projTbl}.state",
                    "{$projTbl}.type",
                    "{$empTbl}.email",
                    DB::raw("group_concat(distinct {$teamTbl}.name SEPARATOR ', ') as team_name")
                )
                ->get();
    }

    /**
     * Get projects has end date or deliver on current week.
     *
     * @return [object].
     */
    public static function getProjectEndDateOfWeek()
    {
        $projsTable = self::getTableName();
        $deliverProjsTable = ProjDeliverable::getTableName();
        $projsMemberTable = ProjectMember::getTableName();
        $empTable = Employee::getTableName();

        $committedDateColumn = "IF({$deliverProjsTable}.change_request_by = " . ProjDeliverable::CHANGE_BY_CUSTOMER . ","
            . " {$deliverProjsTable}.re_commited_date,"
            . " {$deliverProjsTable}.committed_date)";
        $dayStartOfWeek = Carbon::now()->startOfWeek();
        $dayEndOfWeek = Carbon::now()->endOfWeek();
        return self::leftJoin("{$deliverProjsTable}", "{$deliverProjsTable}.project_id", "=", "{$projsTable}.id")
                ->leftJoin("{$empTable}", "{$empTable}.id", "=", "{$projsTable}.manager_id")
                ->leftJoin("{$projsMemberTable} as pjm", function ($join) use ($projsTable, $dayStartOfWeek, $dayEndOfWeek) {
                    $join->on("pjm.project_id", "=", "{$projsTable}.id")
                        ->where("pjm.type", "=", ProjectMember::TYPE_PQA)
                        ->where('pjm.start_at', '<=', $dayEndOfWeek)
                        ->where('pjm.end_at', '>=', $dayStartOfWeek);
                })
                ->leftJoin("{$empTable} as empTable2", "pjm.employee_id", "=", "empTable2.id")
                ->select(
                    "{$projsTable}.id",
                    "{$projsTable}.name",
                    "{$projsTable}.manager_id",
                    "{$empTable}.name as manager_name",
                    "{$empTable}.email as manager_email",
                    "{$projsTable}.end_at",
                    DB::raw("group_concat(distinct {$committedDateColumn}) as committed_date"),
                    DB::raw('group_concat(distinct empTable2.email) as pqa_email')
                )
                ->where("{$projsTable}.status", "=", self::STATUS_APPROVED)
                ->where("{$deliverProjsTable}.status", ProjDeliverable::STATUS_APPROVED)
                ->whereNull("{$deliverProjsTable}.deleted_at")
                ->where(function ($query) use ($projsTable, $dayStartOfWeek, $dayEndOfWeek, $committedDateColumn) {
                    $query->whereBetween("{$projsTable}.end_at", [$dayStartOfWeek, $dayEndOfWeek]);
                    $query->orWhereBetween(DB::raw($committedDateColumn), [$dayStartOfWeek, $dayEndOfWeek]);
                })->groupBy("{$projsTable}.id")->get();
    }

    /**
    *get projects for sale tracking
    *
    */
    public static function getProjectsOfSaleTracking()
    {
        $tableProject = self::getTableName();
        $dateCondition = Carbon::now()->subMonth()->format('Y-m-d');
        $tableMember = ProjectMember::getTableName();
        $tableCustomer = Customer::getTableName();
        $tableCompany = Company::getTableName();
        $tableTeamProject = TeamProject::getTableName();
        $tableTeam = Team::getTableName();
        $project = self::join("{$tableMember}", "{$tableMember}.project_id", '=', "{$tableProject}.id")->where(function ($query) use ($tableMember, $tableProject, $dateCondition) {
                                        $query->where("{$tableMember}.status", '=', ProjectMember::STATUS_APPROVED)
                                        ->whereIn("{$tableProject}.state", [self::STATE_NEW, self::STATE_PROCESSING, self::STATE_PENDING])
                                        ->orWhere(function ($query2) use ($dateCondition, $tableProject) {
                                            $query2->where("{$tableProject}.state", '=', self::STATE_CLOSED)
                                                ->where("{$tableProject}.end_at", '>=', $dateCondition);
                                        });
                                    });
        $curEmp = Permission::getInstance()->getEmployee();
        /**
        * check project and permission
        */
        if (Permission::getInstance()->isScopeCompany(null, 'project::dashboard')) {

        } elseif (Permission::getInstance()->isScopeTeam(null, 'project::dashboard')) {
            $project->leftJoin($tableTeamProject, "{$tableTeamProject}.project_id", '=', "{$tableProject}.id")
                    ->leftJoin($tableTeam, "{$tableTeam}.id", '=', "{$tableTeamProject}.team_id")
                                ->where(function ($query) use ($tableTeamProject, $tableMember, $curEmp) {
                                    $teams = Permission::getInstance()->getTeams();
                                    if ($teams) {
                                        $query->orWhereIn("{$tableTeamProject}.team_id", $teams);
                                        $query->orWhere(function ($query) use ($tableMember, $curEmp) {
                                            $query->where("{$tableMember}.employee_id", $curEmp->id);
                                        });
                                    }
                                });
        } else { //view self project.
            $tableSaleProject = SaleProject::getTableName();
            $tableAssignee = TaskAssign::getTableName();
            $tableTask = Task::getTableName();

            $project->leftJoin($tableSaleProject, "{$tableSaleProject}.project_id", '=', "{$tableProject}.id")
                            ->leftJoin($tableTask, "{$tableTask}.project_id", '=', "{$tableProject}.id")
                            ->leftJoin($tableAssignee, "{$tableAssignee}.task_id", '=', "{$tableTask}.id")
                            ->leftJoin($tableCustomer, "{$tableCustomer}.id", '=', "{$tableProject}.cust_contact_id")
                            ->leftJoin($tableCompany, "{$tableCompany}.id", '=', "{$tableCustomer}.company_id")
                            ->where(function ($query) use (
                                    $tableProject, $tableMember, $tableSaleProject, $tableAssignee, $tableCompany, $dateCondition, $curEmp
                                    ) {
                                $query->orWhere(function ($query) use (
                                        $tableMember, $tableSaleProject, $curEmp, $tableAssignee, $tableCompany, $tableProject
                                        ) { // or member
                                    $query->orwhere(function ($query) use ($curEmp, $tableMember) {
                                        $query->where("{$tableMember}.employee_id", $curEmp->id);
                                    })
                                    // or where sale
                                    ->orWhere("{$tableSaleProject}.employee_id", $curEmp->id)
                                    // or where assign
                                    ->orWhere("{$tableAssignee}.employee_id", $curEmp->id)
                                    // or manager, supporter of customer of project
                                    ->orWhere("{$tableCompany}.manager_id", $curEmp->id)
                                    ->orWhere("{$tableCompany}.sale_support_id", $curEmp->id);
                                });
                            });
        }
         $project->select("{$tableProject}.id", "{$tableProject}.name");
        return $project->groupBy("{$tableProject}.id")->get();
    }

    /**
     * check user is pm, pqa, salers of project.
     * return true if user is one of the this permission.
     *
     * @param Project $project
     * @param  int $employeeId
     * @return boolean.
     */
    public static function checkRelatersOfProject($employeeId, $project)
    {
        $empTbl = Employee::getTableName();
        $projsTbl = self::getTableName();
        $projsMemberTbl = ProjectMember::getTableName();
        $saleProjsMemberTbl = SaleProject::getTableName();

        $checkPMAndPQA = self::select("{$projsTbl}.id")
            ->join($projsMemberTbl, "{$projsMemberTbl}.project_id", '=', "{$projsTbl}.id")
            ->where(function ($query) use ($projsTbl, $employeeId, $projsMemberTbl) {
                $query->orWhere("{$projsTbl}.manager_id", '=', $employeeId)
                       ->orWhere(function ($query2) use ($projsMemberTbl, $employeeId) {
                            $query2->where("{$projsMemberTbl}.employee_id", '=', $employeeId)
                                    ->where("{$projsMemberTbl}.type", '=', ProjectMember::TYPE_PQA);
                        });
            })
            ->where("{$projsTbl}.id", '=', $project->id)
            ->first();
        $checkSale = self::select("{$projsTbl}.id")
                            ->join($saleProjsMemberTbl, "{$saleProjsMemberTbl}.project_id", '=', "{$projsTbl}.id")
                            ->where("{$saleProjsMemberTbl}.employee_id", '=', $employeeId)
                            ->where("{$projsTbl}.id", '=', $project->id)
                            ->first();

        if ($checkPMAndPQA == null && $checkSale == null) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * check user is pm, pqa of project.
     * return true if user is one of the this permission.
     *
     * @param int $projectId
     * @param  int $employeeId
     * @return boolean.
     */
    public static function checkPMAndPQAOfProject($employeeId, $projectId)
    {
        $projsTbl = self::getTableName();
        $projsMemberTbl = ProjectMember::getTableName();

        $checkPMAndPQA = self::select("{$projsTbl}.id")
            ->join($projsMemberTbl, "{$projsMemberTbl}.project_id", '=', "{$projsTbl}.id")
            ->where(function ($query) use ($projsTbl, $employeeId, $projsMemberTbl) {
                $query->where("{$projsTbl}.manager_id", '=', $employeeId);
//                       ->orWhere(function ($query2) use ($projsMemberTbl, $employeeId) {
//                            $query2->where("{$projsMemberTbl}.employee_id", '=', $employeeId)
//                                    ->where("{$projsMemberTbl}.type", '=', ProjectMember::TYPE_PQA);
//                        });
            })
            ->where("{$projsTbl}.id", '=', $projectId)
            ->first();

        if ($checkPMAndPQA) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get leader, pm, pqa of project
     * Get more owner if task is risk type
     *
     * @param [object] $project, [array] $rikkerRelateCss.
     * @param Risk $risk
     *
     * @return null|Employee collection
     */
    public static function getLeaderPMAndPQAOfProject($project, $rikkerRelateCss = [])
    {
        $relaterIds = array((int)$project->manager_id, (int)$project->leader_id);
        $pqaInProject = Project::getEmpByRoleInProject($project->id, ProjectMember::TYPE_PQA);
        if (!empty($pqaInProject)) {
            foreach ($pqaInProject as $pqa) {
                $relaterIds[] = $pqa->id;
            }
        }

        if (isset($rikkerRelateCss) && $rikkerRelateCss) {
            $relaterIds = array_merge($relaterIds, $rikkerRelateCss);
        }
        if (!empty($relaterIds)) {
            $relaterIds = array_unique($relaterIds);
            return Employee::getEmpByIds($relaterIds);
        }
        return null;
    }

    /**
     * get main team code prefix
     * @return string
     */
    public function getLeaderTeamCodePrefix()
    {
        $teamCode = Employee::getNewestTeamCode($this->leader_id);
        return Team::getTeamCodePrefix($teamCode);
    }

    /**
     * get config of reward evaluation unit
     * @param array $rewardMetaConfig
     * @return array
     */
    public function getRewardEvalUnitConfig($rewardMetaConfig = null)
    {
        if (!$rewardMetaConfig) {
            $rewardMetaConfig = config('project.reward');
        }
        $defaultCode = 'hanoi';
        $teamCodePrefix = $this->getLeaderTeamCodePrefix();
        if (isset($rewardMetaConfig['evaluation_unit'][$teamCodePrefix])) {
            return $rewardMetaConfig['evaluation_unit'][$teamCodePrefix];
        }
        return $rewardMetaConfig['evaluation_unit'][$defaultCode];
    }

    /**
     * export get employee project systena
     * @param  [type] $dataFilter
     * @param  [string] $month [Y-m]
     * @return [type]
     */
    public static function getEmployeeProjectSystenaExport($dataFilter, $month)
    {
        $tblProj = self::getTableName();
        $empSystena = ProjEmployeeSystena::getAllEmpSystena($month);

        $collection = self::getEmpProjectSystena($dataFilter, $month);
        $collection->addSelect(DB::raw("1 as projInfor"))
            ->groupBy("projMem.project_id")
            ->groupBy("projMem.employee_id")
            ->orderBy("emp.name", "DESC")
            ->orderBy("projMem.start_at", "ASC")
            ->orderBy("projMem.project_id", "DESC");

        $collection = self::filterGrid($collection, [], null, 'LIKE');
        $collection->union($empSystena);
        return $collection;
    }

    /**
     * get information employee project systena
     * @param  [type] $dataFilter
     * @param  [type] $month [Y-m]
     * @return [type]
     */
    public static function getEmployeeProjectSystena($dataFilter, $month)
    {
        $empSystena = ProjEmployeeSystena::getAllEmpSystena($month);

        $tblProj = self::getTableName($dataFilter, $month);

        $collection = self::getEmpProjectSystena($dataFilter, $month);
        $collection->addSelect(
            DB::raw("GROUP_CONCAT(DISTINCT CONCAT(projs.id ,'_', projs.start_at ,'_', projs.end_at ,'_', projs.name)) AS projInfor")
        )
        ->groupBy("projMem.employee_id")
            ->orderBy("emp.name", "ASC")
            ->orderBy("projMem.start_at", "ASC")
            ->orderBy("projMem.project_id", "DESC");

        $collection->orderBy($pager['order'], $pager['dir']);
        $collection = self::filterGrid($collection, [], null, 'LIKE');
        $collection->union($empSystena);

        $page = request()->get('page', 1);
        $perPage = self::PER_PAGE;
        $collection = $collection->get();
        $slice = $collection->slice($perPage * ($page - 1), $perPage);
        $data = new Paginator($slice, count($collection), $perPage);
        $data->appends(['month' => $month])->links();
        return $data->setPath(request()->url());
    }

    /**
     * buider information employee project systena
     * @param  [type] $dataFilter
     * @param  [type] $month [Y-m]
     * @return [type]
     */
    public static function getEmpProjectSystena($dataFilter, $month = false)
    {
        $tblProj = self::getTableName();
        $tblEmp = Employee::getTableName();
        $tblproMember = ProjectMember::getTableName();
        $tblCusCompany = Company::getTableName();

        $collection = self::withTrashed()->select(
            "{$tblProj}.id as projId",
            "{$tblProj}.name as projName",
            "emp.id as empId",
            "emp.employee_code as empCode",
            "emp.name as empName",
            "projMem.id as projMemId",
            "projMem.start_at as empStart",
            "projMem.end_at as empEnd",
            "{$tblProj}.start_at as projStart",
            "{$tblProj}.end_at as projEnd"
        )
        ->leftJoin("{$tblproMember} as projMem", "projMem.project_id", '=', "{$tblProj}.id")
        ->join("{$tblEmp} as emp", "emp.id", '=', "projMem.employee_id")
        ->leftJoin("{$tblCusCompany} as cusCompany", "cusCompany.id", '=', "{$tblProj}.company_id");
        try {
            if (isset($dataFilter["projMem.start_at"])) {
                $startDateFilter = Carbon::parse($dataFilter["projMem.start_at"])->toDateString();
                $collection->where(function ($query) use ($startDateFilter) {
                    $query->whereDate("projMem.start_at", ">=", $startDateFilter);
                });
            }
            if (isset($dataFilter["projMem.end_at"])) {
                $endDateFilter = Carbon::parse($dataFilter["projMem.end_at"])->toDateString();
                $collection->where(function ($query) use ($endDateFilter) {
                    $query->whereDate("projMem.end_at", "<=", $endDateFilter);
                });
            }
            if (isset($dataFilter["projs.start_at"])) {
                $startDateFilter = Carbon::parse($dataFilter["projs.start_at"])->toDateString();
                $collection->where(function ($query) use ($startDateFilter) {
                    $query->whereDate("projs.start_at", ">=", $startDateFilter);
                });
            }
            if (isset($dataFilter["projs.end_at"])) {
                $endDateFilter = Carbon::parse($dataFilter["projs.end_at"])->toDateString();
                $collection->where(function ($query) use ($endDateFilter) {
                    $query->whereDate("projs.end_at", "<=", $endDateFilter);
                });
            }
        } catch (\Exception $e) {
            return null;
        }
        if ($month) {
            $collection->whereRaw(DB::raw("DATE_FORMAT(projs.start_at, '%Y-%m') <= '" . $month . "' AND DATE_FORMAT(projs.end_at, '%Y-%m') >= '" . $month . "'"));
            $collection->whereRaw(DB::raw("DATE_FORMAT(projMem.start_at, '%Y-%m') <= '" . $month . "' AND DATE_FORMAT(projMem.end_at, '%Y-%m') >= '" . $month . "'"));
        }
        $collection->whereIn("{$tblProj}.state", [Project::STATE_NEW, Project::STATE_PROCESSING])
            ->where("{$tblProj}.status", Project::STATUS_APPROVED)
            ->whereIn("{$tblProj}.type", [Project::TYPE_OSDC, Project::TYPE_BASE])
            ->where("cusCompany.type", "=", Company::TYPE_SYSTENA)
            ->where(function ($query) use ($tblProj) {
                $query->where("projMem.status", "=", Project::STATUS_APPROVED)
                    ->orWhereNotNull("{$tblProj}.deleted_at");
            })
            ->whereNull("emp.deleted_at");
        return $collection;
    }

    /**
     * cut string in group concat function getEmployeeProjectSystena
     * @param  [string] $string
     * @return [array]
     */
    public static function cutStringProjSystena($string)
    {
        $arrInfor = explode(',', $string);
        if (count($arrInfor) == 1) {
            return [];
        }
        $arr = [];
        foreach ($arrInfor as $key => $value) {
            $arr[] = explode('_', $value);
        }
        return $arr;
    }

    /**
     * get project systena
     * @return [type] [description]
     */
    public static function getProjectSystena()
    {
        $tblProj = self::getTableName();
        $tblTeamProjs = TeamProject::getTableName();
        $tblTeam = Team::getTableName();
        $tblEmp = Employee::getTableName();
        $tblproMember = ProjectMember::getTableName();

        return self::select(
            "{$tblProj}.id as projId",
            "{$tblProj}.name as projName",
            "emp.id as empId",
            "emp.employee_code as empCode",
            "emp.name as empName",
            "{$tblProj}.start_at as projStart",
            "{$tblProj}.end_at as projEnd",
            "teamProjs.team_id as projTeamId"
        )
        ->leftJoin("{$tblproMember} as projMem", "projMem.project_id", '=', "{$tblProj}.id")
        ->join("{$tblEmp} as emp", "emp.id", '=', "projMem.employee_id")
        ->join("{$tblTeamProjs} as teamProjs", "teamProjs.project_id", '=', "{$tblProj}.id")
        ->join("{$tblTeam} as team", "team.id", '=', "teamProjs.team_id")
        ->whereIn("{$tblProj}.state", [Project::STATE_NEW, Project::STATE_PROCESSING])
        ->where("{$tblProj}.status", Project::STATUS_APPROVED)
        ->whereIn("{$tblProj}.type", [Project::TYPE_OSDC, Project::TYPE_BASE])
        ->where("team.type", "=", Team::TEAM_TYPE_SYSTENA)
        ->whereNull("{$tblProj}.deleted_at")
        ->groupBy("projMem.project_id")
        ->groupBy("projMem.employee_id")
        ->get();
    }

    public static function getEmployeeProjById($idPro)
    {
        $tblProj = self::getTableName();
        $tblEmp = Employee::getTableName();
        $tblproMember = ProjectMember::getTableName();
        $tblTeamProjs = TeamProject::getTableName();
        $tblTeamEmp = TeamMember::getTableName();
        $tblTeam = Team::getTableName();

        return self::select(
            "{$tblProj}.id as projId",
            "{$tblProj}.name as projName",
            "emp.id as empId",
            "emp.employee_code as empCode",
            "emp.name as empName",
            "projMem.id as projMemId",
            "projMem.start_at as empStart",
            "projMem.end_at as empEnd",
            "{$tblProj}.name as projsName",
            "teamProjs.team_id as projTeamId",
            "{$tblProj}.start_at as projStart",
            "{$tblProj}.end_at as projEnd"
        )
        ->leftJoin("{$tblproMember} as projMem", "projMem.project_id", '=', "{$tblProj}.id")
        ->join("{$tblEmp} as emp", "emp.id", '=', "projMem.employee_id")
        ->join("{$tblTeamProjs} as teamProjs", "teamProjs.project_id", '=', "{$tblProj}.id")
        ->join("{$tblTeam} as team", "team.id", '=', "teamProjs.team_id")
        ->where("{$tblProj}.id", "=", $idPro)
        ->get();
    }

    public static function checkIsLeaderToEditTask($project, $currentUser)
    {
        return DB::table('projs')
                ->join('tasks', 'projs.id', '=', 'tasks.project_id')
                ->where([
                    'tasks.project_id' => $project,
                    'projs.leader_id' => $currentUser,
                    'projs.status' => Project::STATUS_APPROVED
                ])->get();
    }

    /**
     * get project employee is working on
     *
     * @param $empId
     * @return Collection
     */
    public function getProjectByEmpId($empId)
    {
        return ProjectMember::select(
                'project_members.project_id as id',
                'projs.name as projName',
                'project_members.start_at',
                'project_members.end_at'
            )
            ->leftJoin('projs', 'projs.id', '=', 'project_members.project_id')
            ->where('projs.state', '=', Project::STATE_PROCESSING)
            ->whereNull('project_members.deleted_at')
            ->whereNull('projs.deleted_at')
            ->where(function($query) use ($empId){
                $query->where('project_members.status', '=', ProjectWOBase::STATUS_APPROVED);
                $query->where('project_members.employee_id', '=', $empId);
                $query->orWhere('projs.leader_id', '=', $empId);
            })
            ->orderBy('projs.name')
            ->groupBy('project_members.project_id')
            ->distinct()
            ->get();
    }

    /**
     * Get States Filter default in project dashboard
     *
     * @return array
     */
    public static function getStateSFilterDefault()
    {
        return [
            self::STATE_PROCESSING,
            self::STATE_NEW,
        ];
    }

    /**
     * Get Project that are on-going up to now
     * @param null $projectIds
     * @return mixed
     */
    public static function getManyProject($projectIds = null)
    {
        $tableProjectPoint = ProjectPoint::getTableName();
        $tablePointFlat = ProjPointFlat::getTableName();
        $tableCustomer = Customer::getTableName();
        $tableCompany = Company::getTableName();
        $collection = self::select(
            'projs.id',
            'projs.name AS Name',
            'projs.status',
            'projs.category_id',
            'e.name AS D_Leader',
            'employees.name AS PM',
            'employees.email AS PM_email',
            DB::raw(
                "(SELECT GROUP_CONCAT(DISTINCT `t`.`name`) ".
                "FROM `teams` as `t` join `team_projs` as `t_p` on `t`.`id` = `t_p`.`team_id`".
                "where `t_p`.`project_id` = `team_projs`.`project_id`)".
                "as Division"),
            DB::raw('(CASE
                WHEN projs.type = 1 THEN "OSDC"
                WHEN projs.type = 2 THEN "Base"
                WHEN projs.type = 3 THEN "Training"
                WHEN projs.type = 4 THEN "R&D"
                WHEN projs.type = 5 THEN "Onsite"
                ELSE "Opportunity"
                END) AS Type'),
            'projs.start_at AS start_at',
            'projs.end_at AS end_at',
            'projs.state',
            'pq.billable_effort AS Billable_effort',
            'pq.plan_effort AS Plan_effort',
            'pq.cost_approved_production',
            DB::raw("(SUM(pm.flat_resource) * COUNT(DISTINCT pm.id) / COUNT(*)) as 'SUM(pm.flat_resource)'"),
            'pmt.scope_scope AS Scope',
            DB::raw('GROUP_CONCAT(DISTINCT(programming_languages.name) SEPARATOR ",") as proj_prog_lang')
        )
            ->leftjoin('employees as e', 'e.id', '=', 'projs.leader_id')
            ->leftjoin('proj_prog_langs', 'proj_prog_langs.project_id', '=', 'projs.id')
            ->leftjoin('programming_languages', 'proj_prog_langs.prog_lang_id', '=', 'programming_languages.id')
            ->leftjoin('employees as employees', 'employees.id', '=', 'projs.manager_id')
            ->leftjoin('proj_qualities as pq', 'projs.id', '=', 'pq.project_id')
            ->leftJoin('team_projs', "team_projs.project_id", '=', "projs.id")
            ->leftJoin('team_members', function ($join) {
                $join->on("team_members.employee_id", '=', "projs.leader_id");
            })
            ->leftJoin("teams as leader_team", "leader_team.id", '=', "team_members.team_id")
            ->addSelect("leader_team.name as team_charge", "leader_team.id as team_charge_id")
            ->leftjoin('project_members as pm', 'projs.id', '=', 'pm.project_id')
            ->leftjoin('project_metas as pmt', 'projs.id', '=', 'pmt.project_id')
            ->addSelect('pmt.id as scope_id')
            ->leftjoin('team_projs as tp', 'projs.id', '=', 'tp.project_id')
            ->leftjoin('teams as t', 't.id', '=', 'tp.team_id')
            ->leftjoin('team_members as t_lead', 't_lead.employee_id', '=', 'projs.leader_id')
            ->leftjoin('teams as team_lead', 'team_lead.id', '=', 't_lead.team_id')
            ->leftjoin($tablePointFlat, "{$tablePointFlat}.project_id", '=', "projs.id")
            ->addSelect("{$tableProjectPoint}.raise")
            ->leftJoin($tableProjectPoint, "{$tableProjectPoint}.project_id", '=', "projs.id")
            ->leftJoin($tableCustomer, function ($join) use ($tableCustomer){
                $join->on("{$tableCustomer}.id", '=', "projs.cust_contact_id")
                    ->whereNull("{$tableCustomer}.deleted_at");
            })
            ->leftJoin($tableCompany, "{$tableCompany}.id", '=', "{$tableCustomer}.company_id")
            ->addSelect("{$tableCustomer}.id as customer_id", "{$tableCustomer}.name as customer_name", "{$tableCustomer}.name_ja as customer_name_jp", "{$tableCustomer}.email as customer_email", "{$tableCompany}.id as company_id", "{$tableCompany}.company as company_name", "{$tableCompany}.name_ja as company_name_ja")
        //->where('projs.state', self::STATE_PROCESSING)
            ->where('projs.status', self::STATUS_ENABLE)
            ->where('pq.status', 1)
            ->where('pm.status', 1)
            ->whereNull('projs.deleted_at');

        if (isset($projectIds) && $projectIds) {
            if (!is_array($projectIds)) {
                $projectIds = (array)$projectIds;
            }
            $collection = $collection->whereIn('projs.id', $projectIds);
        }

        return $collection;
    }


    /**
     * Get all project
     *
     * @param bool $getWithInClose
     * @return mixed
     */
    public function getAllProject($getAllState = false)
    {
        //get all project is active
        $query = Project::select('name', 'id')
            ->where('status', Project::STATUS_APPROVED);

        if (!$getAllState) {
           $query->where('state', Project::STATE_PROCESSING);
        }

        $query->whereNull('parent_id');
        return $query->pluck('name', 'id')->toArray();
    }

    /**
     * Get List Project ID by Team
     *
     * @param array $teamIds
     * @param bool $getWithInClose
     * @return mixed
     */
    public function getProjectByTeam($teamIds = [], $getAllState = false)
    {
        $permission = Permission::getInstance();

        //nếu không truyền team Id, lấy ra team id của user hiện tại
        if (empty($teamIds)) {
            $teamIds = $permission->isScopeTeam();
        }

        if(!is_array($teamIds)) {
            $teamIds = [$teamIds];
        }

        $query = Project::select('name', 'id')
            ->join('team_projs', 'projs.id', '=', 'team_projs.project_id')
            ->whereIn('team_id', $teamIds);

        if (!$getAllState) {
            $query->where('state', Project::STATE_PROCESSING);
        }
        $query->whereNull('parent_id');
        $query->groupBy('projs.id');

        return $query->pluck('name', 'id')->toArray();

    }

    /**
     * Lấy danh sách Project mà user đang là PM
     *
     * @param bool $getAllState
     * @return mixed
     */
    public function getProjectByEmployee($employeeId = null, $getAllState = false)
    {
        if(empty($employeeId)) {
            $permission = Permission::getInstance();
            $user = $permission->getEmployee();
            $employeeId = $user->id;
        }

        $query = Project::select('name', 'id')
            ->where('manager_id', $employeeId)
            ->orderBy('projs.name');

        if (!$getAllState) {
            $query->where('state', Project::STATE_PROCESSING);
        }

        $query->whereNull('parent_id');
        $query->groupBy('projs.id');

        return $query->pluck('projs.name', 'projs.id')->toArray();
    }

    /**
     * get employeee project onsite by employee
     *
     * @param  array $empIds
     * @return collection
     */
    public function getEmployeeOnsite($empIds)
    {
        $tbl = static::getTableName();
        $tblProjEmplyee = ProjectMember::getTableName();
        return static::select(
            "{$tbl}.id",
            "tblProjEmp.employee_id",
            "tblProjEmp.start_at",
            "tblProjEmp.end_at",
            "tblProjEmp.effort"
        )
        ->leftJoin("{$tblProjEmplyee} as tblProjEmp", 'tblProjEmp.project_id', '=', "{$tbl}.id")
        ->where("{$tbl}.type", Project::TYPE_ONSITE)
        ->where("tblProjEmp.status", ProjectMember::STATUS_APPROVED)
        ->whereNull('tblProjEmp.deleted_at')
        ->whereIn('tblProjEmp.employee_id', $empIds)
        ->get();
    }
    
    
    /**
     * getEmployeeByPmId
     *
     * @param  int $idPm
     * @param  date $month (Y-m-d)
     * @return collection
     */
    public function getEmployeeByPM($idPm, $date)
    {
        $tbl = static::getTableName();
        $tblProjEmplyee = ProjectMember::getTableName();
        return static::select(
            "{$tbl}.id",
            "tblProjEmp.employee_id",
            "tblProjEmp.start_at",
            "tblProjEmp.end_at",
            "tblProjEmp.effort"
        )
        ->leftJoin("{$tblProjEmplyee} as tblProjEmp", 'tblProjEmp.project_id', '=', "{$tbl}.id")
        ->where("tblProjEmp.status", ProjectMember::STATUS_APPROVED)
        ->whereDate('tblProjEmp.start_at', '<=', $date)
        ->whereDate('tblProjEmp.end_at', '>=', $date)
        ->where("{$tbl}.manager_id", $idPm)
        ->whereNull("{$tbl}.parent_id")
        ->whereNull('tblProjEmp.deleted_at')
        ->get();
    }
        
    /**
     * getProjectByPermission
     *
     * @param  string $permission
     * @param  int $empId
     * @return collection
     */
    public function getProjectByPermission($permission, $empId, $date = null)
    {
        if (!$date) {
            $date = Carbon::now()->format('Y-m-d');
        }
        $proj = static::select(
            'projs.id',
            'projs.name',
            'projs.start_at',
            'projs.end_at',
            'projs.leader_id',
            'projs.status'
        )
        ->where("status", ProjectMember::STATUS_APPROVED)
        ->whereDate('projs.end_at' , '>=', $date);
        switch ($permission) {
            case 'x':
                $proj->where('manager_id', $empId);
                break;
            case 'team':
                $team = Permission::getInstance()->isScopeTeam(null, WorkingTimeProject::ROUTE_REGISTER);
                $proj->leftJoin('employee_team_history', 'employee_team_history.employee_id', '=', 'projs.leader_id');
                $proj->where(function($query)  use ($date) {
                    $query->whereDate('employee_team_history.end_at', '>=', $date)
                        ->orWhereNull('employee_team_history.end_at');
                });
                //$proj->where('employee_team_history.is_working', EmployeeTeamHistory::IS_WORKING);
                $proj->whereNull('employee_team_history.deleted_at');
                $proj->whereIn('employee_team_history.team_id', $team);
                break;
            default:
        }
        return $proj->groupBy('projs.id')->orderBy('projs.name')->get();
    }
        
    /**
     * getProjectByname
     *
     * @param  array $arrName
     * @param  array $columns
     * @return coollection
     */
    public function getProjectByname($arrName,  $columns = ['*'])
    {
        return self::whereIn('name', $arrName)->select($columns)->get();
    }

    /**
     * Check current user has permission view cost detail of Approved production cost
     *
     * @param int $userId
     * @param Project $project
     * @param array $leaderIds  mảng các leader_id của các bộ phận tham gia dự án (các giá trị đã được duyệt)
     * @return boolean
     */
    public static function hasPermissionViewCostDetail($userId, $project, $allTeam)
    {
        $arrIdsPM = static::hasPermissionViewCostDetailByPM($project->id);
        return $userId == $project->manager_id || in_array($userId, $arrIdsPM) || static::hasPermissionViewCostPriceDetail($userId, $allTeam);
    }

    public static function hasPermissionViewCostDetailByPM($projectId)
    {
        $today = date('Y-m-d');
        $collection = ProjectMember::where('project_id', $projectId)
            ->where('status', ProjectMember::STATUS_APPROVED)
            ->where('type', ProjectMember::TYPE_PM)
            ->whereDate('start_at','<=', $today)
            ->whereDate('end_at','>=', $today)
            ->whereNull('deleted_at')
            ->pluck('employee_id')->toArray();
        return $collection;
    }

    /**
     * Check current user has permission view cost price detail of Approved production cost
     *
     * @param type $userId
     * @param type $leaderIds   mảng các leader_id của các bộ phận tham gia dự án (các giá trị đã được duyệt)
     * @return boolean
     */
    public static function hasPermissionViewCostPriceDetail($userId, $allTeam)
    {
        if (self::hasPermissionViewCostPriceDetailByCompany($userId, $allTeam) || self::hasPermissionViewCostPriceDetailByTeam($userId, $allTeam)) {
            return true;
        }
        return false;
    }

    // Quyền công ty ok
    public static function hasPermissionViewCostPriceDetailByCompany($userId, $allTeam)
    {
        $route = 'project::project.edit-approved-production-cost-detail';
        if (Permission::getInstance()->isScopeCompany(null, $route)) {
            return true;
        }
        return false;
    }

    // Quyền team trong các bộ phận tham gia trong dự án
    public static function hasPermissionViewCostPriceDetailByTeam($userId, $allTeam)
    {
        $route = 'project::project.edit-approved-production-cost-detail';
        if ($teamIds = Permission::getInstance()->isScopeTeam(null, $route)) {
            if (count(array_intersect($teamIds, $allTeam))) {
                return true;
            }
        }
        return false;
    }

    public static function apiGetPoByProjectId($poId)
    {
        $tokenHelper = new TimesheetHelper();
        $token = $tokenHelper->getToken();

        $header = [
            "Authorization: Bearer {$token}",
            "Content-Type: application/x-www-form-urlencoded",
        ];

        $url = config('sales.api_base_url') . self::URI_GET_PURCHASE_ORDER;
        if ($poId) {
            $param = [
                'purchase_order_id' => $poId,
            ];

            $response = CurlHelper::httpPost($url, $param, $header);
            $response = json_decode($response, true);
            if (!isset($response['data'])) {
                // Nếu không có data trả về thì get lại token
                // Remove token cũ
                Storage::put('sale_token.json', '');
                \Log::info('Không có data');
                \Log::info(print_r($response, true));
                return false;
            } else {
                return $response['data'];
            }
        }
        return false;
    }

    public static function savePurchaseIdToCRM($request) {
        if ($arrPoIds = $request->get('arrPurchasIds')) {
            $tokenHelper = new TimesheetHelper();
            $token = $tokenHelper->getToken();
            $header = [
                "Authorization: Bearer {$token}",
                "Content-Type: application/x-www-form-urlencoded",
            ];
            $url = config('sales.api_base_url') . self::URI_SAVE_PURCHASE_ID_TO_CRM;        
            $param = [
                'po_id' => $arrPoIds,
                'project_id' => $request->get('projectId'),
                'project_name' => $request->get('projectName'),
                'pm_name' => $request->get('pmName'),
            ];

            $response = CurlHelper::httpPost($url, $param, $header);
            $response = json_decode($response, true);
            if (!isset($response['data']['success']) || $response['data']['success'] == false || $response['data']['success'] == 'false') {
                // Nếu không có data trả về thì get lại token
                // Remove token cũ
                Storage::put('sale_token.json', '');
                \Log::info('Không có data');
                \Log::info(print_r($response, true));
                return false;
            }
            return $response['data']['success'];
        }
        return false;
    }

    public static function updateDate($request)
    {
        $project = self::find($request->projectId);
        if ($project) {
            if ($request->statusProj == self::STATUS_PROJECT_CLOSE) {
                return false;
            } else {
                $project->close_date = null;
            }
            $project->save();
            return $project;
        }
        return false;
    }

    public static function saveContactCustomer($request)
    {
        $project = self::find($request->projectId);
        if ($project) {
            if (!$project->cus_contact) {
                $project->cus_contact = $request->cusContact;
                return $project;
            } else {
                return false;
            }
        }
        return false;
    }

    public static function getDayOfProjectWork($startDate, $endDate)
    {
        if ($startDate && $endDate) {
            $days = ViewProject::getMM($startDate, $endDate, $type = 2);
            return $days;
        }
        return false;
    }

    public static function saveProjCate($request)
    {
        $projectCate = self::find($request->projectId);
        if ($projectCate) {
            if ($request->category != self::NONE_VALUE) {
                $projectCate->category_id = $request->category;
            }
            if ($request->classification != self::NONE_VALUE) {
                $projectCate->classification_id = $request->classification;
            }
            if ($request->business != self::NONE_VALUE) {
                $projectCate->business_id = $request->business;
            }
            if ($request->subSector != self::NONE_VALUE) {
                $projectCate->sub_sector = $request->subSector;
            }
            $projectCate->cus_email = $request->cusEmail ? $request->cusEmail : null;
            $projectCate->cus_contact = $request->cusContact ? $request->cusContact : null;
            $projectCate->save();
            return $projectCate;
        }
        return false;
    }
}

