<?php

namespace Rikkei\Project\Model;

use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Rikkei\Core\View\Form;
use Rikkei\Team\Model\PqaResponsibleTeam;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\View\Config;
use Rikkei\Team\View\Permission;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Project\Model\Task;
use Rikkei\Core\View\CacheHelper;
use DB;
use Lang;
use Rikkei\Project\View\View;
use Rikkei\Project\Model\Project;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\Employee;
use Rikkei\Sales\Model\Customer;
use Rikkei\Sales\Model\Company;

class Risk extends ProjectWOBase
{
    use SoftDeletes;

    const LEVEL_LOW = 1;
    const LEVEL_NORMAL = 2;
    const LEVEL_HIGH = 3;
    const LEVEL_CRITICAL = 4;

    /**
     * TYPE OF RISK
     */
    const TYPE_QUALITY = 1;
    const TYPE_PROCESS = 2;
    const TYPE_COST = 3;
    const TYPE_DELIVERY = 4;
    const TYPE_CUSTOMER_COMPLAINT = 5;

    /**
     * SOURCE OF RISK
     */
    const SOURCE_CUSTOMER = 1;
    const SOURCE_CONTRACT = 2;
    const SOURCE_RESOURCES = 3;
    const SOURCE_MANAGEMENT_PROCESS = 4;
    const SOURCE_DEPENDENCIES = 5;
    const SOURCE_REQUIREMENT = 6;
    const SOURCE_DESIGN = 7;
    const SOURCE_CODING = 8;
    const SOURCE_TESTING = 9;
    const SOURCE_DEPLOYEMENT = 10;
    const SOURCE_TECHNICAL = 12; 
    const SOURCE_PROJECT_INFRASTRUCTURE = 13; 
    const SOURCE_PROJECT_CHARACTERISTICS = 14;
    const SOURCE_PROJECT_ESTIMATION = 15; 
    const SOURCE_OTHERS = 11;

    const UPLOAD_ATTACH = 'project/risk';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'proj_op_ricks';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['project_id', 'content', 'note', 'description', 'code', 'level_important',
                            'status', 'created_by', 'type', 'state', 'threat', 'weakness', 'solution_using',
                            'start_at', 'type', 'posibility_using', 'impact_using', 'value_using',
                            'handling_method_using', 'solution_suggest', 'possibility_suggest',
                            'impact_suggest', 'value_suggest', 'risk_acceptance_criteria',
                            'handling_method_suggest', 'acceptance_reason', 'owner',
                            'finish_date', 'performer', 'evidence', 'result', 'test_date',
                            'tester', 'confirm', 'team_owner', 'trigger', 'source', 'due_date', 'probability_backup', 'impact_backup'];
    
    /**
     * List hangling method of risk
     */
    const METHOD_ACCEPT = 1;
    const METHOD_MINIMIZE = 2;
    const METHOD_PREVENTION = 3;
    
    /**
     * Result of solution pass or fail
     */
    const RESULT_PASS = 1;
    const RESULT_FAIL = 2;

    /**
     * Status of risk item
     */
    const STATUS_OPEN = 0;
    const STATUS_HAPPEN = 1;
    const STATUS_CLOSED = 2;
    const STATUS_OCCURED = 3;
    const STATUS_CANCELLED = 4;

    /**
     * Keyes cache
     */
    const KEY_CACHE_RISK = 'risk_by_id';

    public static function statusLabel()
    {
        return [
            self::STATUS_OPEN => Lang::get('project::view.Open'),
            self::STATUS_HAPPEN => Lang::get('project::view.Inprogress'),
            self::STATUS_OCCURED => Lang::get('project::view.Occured'),
            self::STATUS_CANCELLED => Lang::get('project::view.Cancelled'),
            self::STATUS_CLOSED => Lang::get('project::view.Closed'),
        ];
    }

    public static function impactLabel()
    {
        return [
            self::LEVEL_HIGH => Lang::get('project::view.Level High'),
            self::LEVEL_NORMAL => Lang::get('project::view.Level Normal'),
            self::LEVEL_LOW => Lang::get('project::view.Level Low'),
        ];
    }

    public static function getPriorityLabel($impact, $probability)
    {
        if (isset($impact) && isset($probability)) {
            if (($probability == self::LEVEL_HIGH  && ($impact == self::LEVEL_HIGH || $impact == self::LEVEL_NORMAL))
            || ($probability == self::LEVEL_NORMAL && $impact == self::LEVEL_HIGH)) {
                return trans('project::view.Level High');
            } else if ($probability == self::LEVEL_LOW && $impact == self::LEVEL_LOW) {
                return trans('project::view.Level Low');
            } else if ($probability == '' || $impact == '') {
                return '';
            } else {
                return trans('project::view.Level Normal');
            }
        }
        return '';
    }

    public static function priorityLabel()
    {
        return [
            self::LEVEL_HIGH => Lang::get('project::view.Level High'),
            self::LEVEL_NORMAL => Lang::get('project::view.Level Normal'),
            self::LEVEL_LOW => Lang::get('project::view.Level Low'),
            self::LEVEL_CRITICAL => Lang::get('project::view.Critical'),
        ];
    }

    public static function statusBackground()
    {
        return [
            self::STATUS_OPEN => '#fff',
            self::STATUS_HAPPEN => '#d73925',
            self::STATUS_CLOSED => '#d6d6d6',
        ];
    }

    public static function getTypeList()
    {
        return [
            self::TYPE_QUALITY => Lang::get('project::view.Quality'),
            self::TYPE_PROCESS => Lang::get('project::view.Process'),
            self::TYPE_COST => Lang::get('project::view.Cost'),
            self::TYPE_DELIVERY => Lang::get('project::view.Delivery'),
        ];
    }

    public static function getSourceList()
    {
        return [
            self::SOURCE_CUSTOMER => Lang::get('project::view.Customer'),
            self::SOURCE_CONTRACT => Lang::get('project::view.Contract'),
            self::SOURCE_RESOURCES => Lang::get('project::view.Resources'),
            self::SOURCE_MANAGEMENT_PROCESS => Lang::get('project::view.Management process'),
            self::SOURCE_DEPENDENCIES => Lang::get('project::view.Dependencies'),
            self::SOURCE_REQUIREMENT => Lang::get('project::view.Requirement'),
            self::SOURCE_DESIGN => Lang::get('project::view.Design'),
            self::SOURCE_CODING => Lang::get('project::view.Coding'),
            self::SOURCE_TESTING => Lang::get('project::view.Testing'),
            self::SOURCE_TECHNICAL => Lang::get('project::view.Technical'),
            self::SOURCE_PROJECT_INFRASTRUCTURE => Lang::get('project::view.Project Infrastructure'),
            self::SOURCE_PROJECT_CHARACTERISTICS => Lang::get('project::view.Project Characteristics'),
            self::SOURCE_PROJECT_ESTIMATION => Lang::get('project::view.Project estimation'),
            self::SOURCE_OTHERS => Lang::get('project::view.Others'),
        ];
    }

    /**
     * List result
     * 
     * @return array
     */
    public static function getResults() {
        return [
            self::RESULT_PASS => Lang::get('project::view.Pass'),
            self::RESULT_FAIL => Lang::get('project::view.Fail'),
        ];
    }

    /**
     * Get result by key
     * 
     * @param int $result
     * @return string
     */
    public static function getResult($result) {
        switch ($result) {
            case self::RESULT_PASS: return Lang::get('project::view.Pass');
            case self::RESULT_FAIL: return Lang::get('project::view.Fail');
            default: return '';
        }
    }
    
    /**
     * List hangling method of risk
     * 
     * @return array string
     */
    public static function getMethods() {
        return [
            self::METHOD_ACCEPT => Lang::get('project::view.Accept'),
            self::METHOD_MINIMIZE => Lang::get('project::view.Minimize'),
            self::METHOD_PREVENTION => Lang::get('project::view.Prevention'),
        ];
    }
    
    /**
     * hangling method of risk
     * get by method key
     * 
     * @param int $method
     * @return string
     */
    public static function getMethod($method) {
        switch ($method) {
            case self::METHOD_ACCEPT: return Lang::get('project::view.Accept');
            case self::METHOD_MINIMIZE: return Lang::get('project::view.Minimize');
            case self::METHOD_PREVENTION: return Lang::get('project::view.Prevention');
            default: return '';
        }
    }

    /**
     * Get the risk child
     */
    public function projectRiskChild() {
        return $this->hasOne('Rikkei\Project\Model\Risk', 'parent_id');
    }                        

    /*
     * get all risk by project id
     * @param int
     * @return collection
     */
    public static function getAllRisk($projectId, $columns = ['*'], $statusList = null, $typeRisk = null)
    {
        $pager = Config::getPagerDataQuery();
        $item = self::select($columns)
            ->where('project_id', $projectId);
        $item->leftJoin("employees", "employees.id", "=", "proj_op_ricks.owner")
             ->leftJoin("teams", "teams.id", "=", "proj_op_ricks.team_owner");
        $item->addSelect(DB::raw("(SELECT duedate FROM risk_actions WHERE risk_id = proj_op_ricks.id order by duedate desc limit 1) AS duedate"));
        if (isset($statusList)) {
            $item->whereIn('proj_op_ricks.status', $statusList);
        }
        if (isset($typeRisk)) {
            $item->whereIn('proj_op_ricks.type', $typeRisk);
        }
        if (config('project.workorder_approved.risk')) {
            $item = $item->whereNull('parent_id')
                        ->orderBy('proj_op_ricks.created_at', 'desc');
        } else {
            $item = $item->orderBy('proj_op_ricks.created_at', 'desc');
        }
        CacheHelper::put(self::KEY_CACHE_WO, $item, $projectId);
        self::pagerCollection($item, $pager['limit'], $pager['page']);
        return $item;
    }

    public static function getAllRiskExport($columns = ['*'], $conditions, $teamIdsAvailable)
    {
        $urlFilter = trim(URL::route('project::report.risk'), '/') . '/';
        $tableRisk = self::getTableName();
        $tableProject = Project::getTableName();
        $tableTeamMember = TeamMember::getTableName();
        $tableTeam = Team::getTableName();
        $tableRiskAction = RiskAction::getTableName();
        $tableRiskComment = RiskComment::getTableName();
        $tableEmp = Employee::getTableName();
        $collection = self::select($columns)
            ->leftJoin($tableProject, "{$tableRisk}.project_id", '=', "{$tableProject}.id")
            ->leftJoin($tableTeamMember, "{$tableTeamMember}.employee_id", '=', "{$tableProject}.leader_id")
            ->leftJoin($tableTeam, "{$tableTeamMember}.team_id", '=', "{$tableTeam}.id")
            ->leftJoin($tableEmp, "{$tableEmp}.id", '=', "{$tableRisk}.owner")
            ->leftJoin('project_members', "project_members.project_id", "=", "projs.id")
            ->leftJoin("{$tableRiskAction} as miti", function ($join) use ($tableRisk) {
                $join->on("miti.risk_id", '=', "{$tableRisk}.id")
                    ->where('miti.type', '=', RiskAction::TYPE_RISK_MITIGATION);
            })
            ->leftJoin("{$tableRiskAction} as conti", function ($join) use ($tableRisk) {
                $join->on("conti.risk_id", '=', "{$tableRisk}.id")
                    ->where('conti.type', '=', RiskAction::TYPE_RISK_CONTIGENCY);
            })
            ->leftJoin("{$tableRiskComment} as cmt", function ($join) use ($tableRisk) {
                $join->on("cmt.obj_id", '=', "{$tableRisk}.id")
                    ->where('cmt.type', '=', RiskComment::TYPE_RISK);
            })
            ->addSelect("{$tableEmp}.name as owner_name", "{$tableTeam}.name as team_name", "{$tableProject}.name as project_name",
                DB::raw('GROUP_CONCAT(DISTINCT(miti.content) SEPARATOR ",") AS miti_content'),
                DB::raw('GROUP_CONCAT(DISTINCT(conti.content) SEPARATOR ",") AS conti_content'),
                DB::raw('GROUP_CONCAT(DISTINCT(cmt.content) SEPARATOR ",") AS cmt_content')
                );
        $collection->groupBy("{$tableRisk}.id")
            ->orderBy("{$tableRisk}.level_important");
        $emp = Permission::getInstance()->getEmployee();
        if (Permission::getInstance()->isScopeCompany(null, 'project::report.risk')) {
            return $collection;
        } else {
            if (!empty($teamIdsAvailable)) {
                $collection->where(function ($p) use ($teamIdsAvailable, $emp) {
                    $p->orWhereIn('teams.id', $teamIdsAvailable)
                        ->orWhere(function ($p) use ($emp) {
                            $p->where('project_members.employee_id', $emp->id)
                                ->whereDate('project_members.start_at', '<=', Carbon::now()->format('Y-m-d'))
                                ->whereDate('project_members.end_at', '>=', Carbon::now()->format('Y-m-d'))
                                ->where('project_members.status', ProjectMember::STATUS_APPROVED);
                        });
                });
            } else {
                $collection->where('project_members.employee_id', $emp->id)
                    ->whereDate('project_members.start_at', '<=', Carbon::now()->format('Y-m-d'))
                    ->whereDate('project_members.end_at', '>=', Carbon::now()->format('Y-m-d'))
                    ->where('project_members.status', ProjectMember::STATUS_APPROVED);
            }
        }
        return $collection;
    }

    public static function filterRisk($collection, $conditions)
    {
        if (isset($conditions['teams.id'])) {
            $collection->whereIn("team_members.team_id", $conditions['teams.id']);
        }
        if (isset($conditions['proj_op_ricks.due_date'])) {
            $collection->whereDate('proj_op_ricks.due_date', '>=', $conditions['proj_op_ricks.due_date']);
        }
        if (isset($conditions['proj_op_ricks.finish_date'])) {
            $collection->whereDate('proj_op_ricks.due_date', '<=', $conditions['proj_op_ricks.finish_date']);
        }
        if (isset($conditions['proj_op_ricks.created_at'])) {
            $collection->whereDate('proj_op_ricks.created_at', '>=', $conditions['proj_op_ricks.created_at']);
        }
        if (isset($conditions['proj_op_ricks.test_date'])) {
            $collection->whereDate('proj_op_ricks.created_at', '<=', $conditions['proj_op_ricks.test_date']);
        }
        if (isset($conditions['proj_op_ricks.updated_at'])) {
            $collection->whereDate('proj_op_ricks.updated_at', '>=', $conditions['proj_op_ricks.updated_at']);
        }
        if (isset($conditions['proj_op_ricks.deleted_at'])) {
            $collection->whereDate('proj_op_ricks.updated_at', '<=', $conditions['proj_op_ricks.deleted_at']);
        }
        return $collection;
    }

    /*
     * add risk
     * @param array
     */
    public static function insertRisk($input)
    {
        DB::beginTransaction();
        try {
            $arrayLablelStatus = self::getLabelStatusForProjectLog();
            $labelElement = self::getLabelElementForWorkorder()[Task::TYPE_WO_RISK];
            if (config('project.workorder_approved.risk')) {
                if (isset($input['isAddNew'])) {
                    $risk = new Risk();
                    $risk->project_id = $input['project_id'];
                    $risk->status = self::STATUS_DRAFT;
                    $risk->content = $input['content_1'];
                    $risk->note = $input['note_1'];
                    $risk->created_by = Permission::getInstance()->getEmployee()->id;
                    $status = self::STATUS_DRAFT;
                    $risk->save();
                } else if (isset($input['isEdit'])) {
                    if (isset($input['status']) && $input['status'] == self::STATUS_APPROVED) {
                        $status = self::STATUS_EDIT_APPROVED;
                        $risk = new Risk();
                        $risk->project_id = $input['project_id'];
                        $risk->status = self::STATUS_DRAFT_EDIT;
                        $risk->parent_id = $input['id'];
                        $risk->created_by = Permission::getInstance()->getEmployee()->id;
                    } else {
                        $risk = self::find($input['id']);
                        if ($risk->status == self::STATUS_FEEDBACK_EDIT) {
                            $status = self::STATUS_FEEDBACK_EDIT;
                            $risk->status = self::STATUS_DRAFT_EDIT;
                        }
                        if ($risk->status == self::STATUS_FEEDBACK) {
                            $status = self::STATUS_FEEDBACK;
                            $risk->status = self::STATUS_DRAFT;
                        }
                        if ($risk->status == self::STATUS_DRAFT_EDIT) {
                            $status = self::STATUS_DRAFT_EDIT;
                        }
                        if ($risk->status == self::STATUS_DRAFT) {
                            $status = self::STATUS_UPDATED_DRAFT;
                        }
                    }
                    $risk->content = $input['content_1'];
                    $risk->note = $input['note_1'];
                    $riskAttributes = $risk->attributes;
                    $riskOrigin = $risk->original;
                    $risk->save();
                }
                $statusText = $arrayLablelStatus[$status];
                if ($status == self::STATUS_DRAFT || $status == self::STATUS_EDIT_APPROVED) {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                } else {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, $riskAttributes, $riskOrigin);
                }
            } else {
                if (isset($input['id'])) {
                    $status = self::STATUS_EDIT;
                    $risk = self::find($input['id']);
                } else {
                    $status = self::STATUS_ADD;
                    $risk = new Risk;
                    $risk->project_id = $input['project_id'];
                    $risk->created_by = Permission::getInstance()->getEmployee()->id;
                    $risk->status = self::STATUS_APPROVED;
                }
                $risk->content = $input['content_1'];
                $risk->note = $input['note_1'];
                $riskAttributes = $risk->attributes;
                $riskOrigin = $risk->original;
                $risk->save();

                $statusText = $arrayLablelStatus[$status];
                if ($status == self::STATUS_ADD) {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                } else {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, $riskAttributes, $riskOrigin);
                }
            }
            DB::commit();
            CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
            return true;
        } catch (Exception $ex) {
            DB::rollback();
            return false;
        }
    }     
    
    /**
     * Save risk
     * 
     * @param array $data
     * @return boolean
     */
    public static function store($data) {
        if (isset($data['riskId'])) {
            $riskId = $data['riskId'];
            $risk = Risk::find($riskId);
        } else {
            $risk = new Risk();
        }
        DB::beginTransaction();
        try {
            $risk->fill($data);
            $arrCanNull = ['probability_backup', 'impact_backup', 'performer', 'tester', 'finish_date', 'test_date', 
                'posibility_using', 'impact_using', 'value_using', 'handling_method_using',
                'solution_suggest', 'possibility_suggest', 'impact_suggest', 'value_suggest',
                'risk_acceptance_criteria', 'owner', 'team_owner', 'solution_using'];
            foreach ($arrCanNull as $field) {
                if (!isset($data[$field]) || empty($data[$field]) || !$data[$field]) {
                    $risk->$field = null;
                }
            }
            $risk->save();
            DB::commit();
            return $risk;
        } catch (Exception $ex) {
            DB::rollback();
            return false;
        }
        
    }

    /*
     * delete risk
     * @param array
     * @return boolean
     */
    public static function deleteRisk($input)
    {
        $arrayLablelStatus = self::getLabelStatusForProjectLog();
        $labelElement = self::getLabelElementForWorkorder()[Task::TYPE_WO_RISK];
        if (config('project.workorder_approved.risk')) {
            $risk = self::find($input['id']);
            if ($risk) {
                if($risk->status == self::STATUS_APPROVED) {
                    $riskDelete = $risk->replicate();
                    $riskDelete->status = self::STATUS_DRAFT_DELETE;
                    $riskDelete->parent_id = $input['id'];
                    $status = self::STATUS_DELETE_APPROVED;
                    if ($riskDelete->save()) {
                        CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
                        return true;
                    }
                } else {
                    if ($risk->status == self::STATUS_DRAFT_EDIT) {
                        $status = self::STATUS_DELETE_DRAFT_EDIT;
                    } else if ($risk->status == self::STATUS_DRAFT) {
                        $status = self::STATUS_DELETE_DRAFT;
                    } else if ($risk->status == self::STATUS_FEEDBACK_DELETE) {
                        $status = self::STATUS_FEEDBACK_DELETE;
                    } else if ($risk->status == self::STATUS_FEEDBACK_EDIT) {
                        $status = self::STATUS_DELETE_FEEDBACK_EDIT;
                    }  else if ($risk->status == self::STATUS_FEEDBACK) {
                        $status = self::STATUS_DELETE_FEEDBACK;
                    }  else if ($risk->status == self::STATUS_DRAFT_DELETE) {
                        $status = self::STATUS_DRAFT_DELETE;
                    }
                    if ($risk->delete()) {
                        CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
                        $statusText = $arrayLablelStatus[$status];
                        View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                        return true;
                    } 
                }
            }
        } else {
            $status = self::STATUS_DELETE;
            $risk = self::find($input['id']);
            if ($risk) {
                if ($risk->delete()) {
                    CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
                    $statusText = $arrayLablelStatus[$status];
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * get content task approved
     * @param int
     * @param array
     * @return string
     *
     */
    public static function getContentTaskApproved($typeWO, $input, $content, $typeSubmit = null)
    {
        $riskDraft = self::where('project_id', $input['project_id']);
        if ($typeSubmit) {
            $riskDraft = $riskDraft->whereIn('status', [self::STATUS_DRAFT, self::STATUS_FEEDBACK]);
        } else {
            $riskDraft = $riskDraft->where('status', self::STATUS_DRAFT);
        }
        $riskDraft = $riskDraft->get();
        if(count($riskDraft) > 0) {
            $title = Lang::get('project::view.Add object for Risk');
            $content .= view('project::template.content-task', ['inputs' => $riskDraft, 'title' => $title, 'type' => $typeWO])->render();
        }

        $riskDraftEdit = self::where('project_id', $input['project_id']);
        if ($typeSubmit) {
            $riskDraftEdit = $riskDraftEdit->whereIn('status', [self::STATUS_DRAFT_EDIT, self::STATUS_FEEDBACK_EDIT]);
        } else {
            $riskDraftEdit = $riskDraftEdit->where('status', self::STATUS_DRAFT_EDIT);
        }
        $riskDraftEdit = $riskDraftEdit->get();
        if(count($riskDraftEdit) > 0) {
            if (!isset($content)) {
                $content = '';
            }
            $title = Lang::get('project::view.Edit object for Risk');
            $content .= view('project::template.content-task', ['inputs' => $riskDraftEdit, 'title' => $title, 'type' => $typeWO])->render();
        }

        $riskDelete = self::where('project_id', $input['project_id']);
        if ($typeSubmit) {
            $riskDelete = $riskDelete->whereIn('status', [self::STATUS_DRAFT_DELETE, self::STATUS_FEEDBACK_DELETE]);
        } else {
            $riskDelete = $riskDelete->where('status', self::STATUS_DRAFT_DELETE);
        }
        $riskDelete = $riskDelete->get();
        if(count($riskDelete) > 0) {
            if (!isset($content)) {
                $content = '';
            }
            $title = Lang::get('project::view.Delete object for Risk');
            $content .= view('project::template.content-task', ['inputs' => $riskDelete, 'title' => $title, 'type' => $typeWO])->render();
        }
        return $content;
    }
    
    /**
     * check status submit
     * @param int
     * @return boolean
     */
    public static function checkStatusSubmit($projectId)
    {
        $status = false;
        if ($checkStatus = CacheHelper::get(self::KEY_CACHE_WO, $projectId)) {
            return $checkStatus;
        }
        $items = self::where('project_id', $projectId)
                       ->whereIn('status', [self::STATUS_DRAFT, self::STATUS_DRAFT_EDIT, self::STATUS_DRAFT_DELETE])->count();
        if ($items > 0) {
            $status = true;
        }
        CacheHelper::put(self::KEY_CACHE_WO, $status, $projectId);
        return $status;
    }

    /**
     * update status when submit workorder
     * @param array
     */
    public static function updateStatusWhenSubmitWorkorder($task, $input)
    {
        $riskDraft = self::where('project_id', $input['project_id'])
                                    ->whereIn('status', [self::STATUS_DRAFT, self::STATUS_FEEDBACK])
                                    ->get();
        if(count($riskDraft) > 0) {
            foreach($riskDraft as $risk) {
                $risk->status = self::STATUS_SUBMITTED;
                $risk->task_id = $task->id;
                $risk->save();
            }
        }

        $riskEdit = self::where('project_id', $input['project_id'])
                                        ->whereIn('status', [self::STATUS_DRAFT_EDIT, self::STATUS_FEEDBACK_EDIT])
                                        ->whereNotNull('parent_id')
                                        ->get();
        if(count($riskEdit) > 0) {
            foreach($riskEdit as $risk) {
                $risk->status = self::STATUS_SUBMIITED_EDIT;
                $risk->task_id = $task->id;
                $risk->save();
            }
        }

        $riskDelete = self::where('project_id', $input['project_id'])
                                    ->whereIn('status', [self::STATUS_DRAFT_DELETE, self::STATUS_FEEDBACK_DELETE])
                                    ->get();
        if(count($riskDelete)) {
            foreach($riskDelete as $risk) {
                $risk->status = self::STATUS_SUBMMITED_DELETE;
                $risk->task_id = $task->id;
                $risk->save();
            }
        }
        
        CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
    }

    /**
     * update status when submit slove workorder
     * @param array
     */
    public static function updateStatusWhenSloveWorkorder($statusTask, $projectId)
    {   
        self::updateItemWorkorder(self::TYPE_ADD, $statusTask, $projectId);
        self::updateItemWorkorder(self::TYPE_EDIT, $statusTask, $projectId);
        self::updateItemWorkorder(self::TYPE_DELETE, $statusTask, $projectId);
        CacheHelper::forget(self::KEY_CACHE_WO, $projectId);
    }

    /**
     * update itemt workorder
     * @param int
     * @param int
     * @param int
     */
    public static function updateItemWorkorder($type, $statusTask, $projectId)
    {
        $riskDraft = self::where('project_id', $projectId);
        if ($statusTask == Task::STATUS_APPROVED) {
            if ($type == self::TYPE_ADD) {
                $riskDraft = $riskDraft->where('status', self::STATUS_REVIEWED);
            } else if($type == self::TYPE_EDIT) {
                $riskDraft = $riskDraft->where('status', self::STATUS_REVIEWED_EDIT);
            } else {
                $riskDraft = $riskDraft->where('status', self::STATUS_REVIEWED_DELETE);
            }
        } else if ($statusTask == Task::STATUS_FEEDBACK || $statusTask == Task::STATUS_REVIEWED) {
            if ($type == self::TYPE_ADD) {
                $riskDraft = $riskDraft->whereIn('status', [self::STATUS_SUBMITTED, self::STATUS_REVIEWED]);
            } else if($type == self::TYPE_EDIT) {
                $riskDraft = $riskDraft->whereIn('status', [self::STATUS_SUBMIITED_EDIT, self::STATUS_REVIEWED_EDIT]);
            } else {
                $riskDraft = $riskDraft->whereIn('status', [self::STATUS_SUBMMITED_DELETE, self::STATUS_REVIEWED_DELETE]);
            }
        }
        $riskDraft = $riskDraft->get();
        if(count($riskDraft) > 0) {
            if ($statusTask == Task::STATUS_APPROVED) {
                if ($type == self::TYPE_DELETE) {
                    foreach($riskDraft as $risk) {
                        $riskParent = self::find($risk->parent_id);
                        $risk->delete();
                        if($riskParent) {
                            $riskParent->delete();
                        }
                    }
                } else {
                    foreach($riskDraft as $risk) {
                        $riskParent = self::find($risk->parent_id);
                        if($riskParent) {
                            $risk->parent_id = null;
                            $risk->task_id = null;
                            $risk->save();
                            $riskParent->delete();
                        }
                        $risk->status = self::STATUS_APPROVED;
                        $risk->save();
                    }
                }
            } else if ($statusTask == Task::STATUS_REVIEWED) {
                foreach($riskDraft as $risk) {
                    if ($risk->status == self::STATUS_SUBMITTED) {
                        $risk->status = self::STATUS_REVIEWED;
                    }
                    if ($risk->status == self::STATUS_SUBMIITED_EDIT) {
                        $risk->status = self::STATUS_REVIEWED_EDIT;
                    }
                    if ($risk->status == self::STATUS_SUBMMITED_DELETE) {
                        $risk->status = self::STATUS_REVIEWED_DELETE;
                    }
                    $risk->save();
                }
            } else if ($statusTask == Task::STATUS_FEEDBACK) {
                foreach($riskDraft as $risk) {
                    if ($risk->status == self::STATUS_SUBMITTED ||
                        $risk->status == self::STATUS_REVIEWED) {
                        $risk->status = self::STATUS_FEEDBACK;
                    }
                    if ($risk->status == self::STATUS_SUBMIITED_EDIT ||
                        $risk->status == self::STATUS_REVIEWED_EDIT) {
                        $risk->status = self::STATUS_FEEDBACK_EDIT;
                    }
                    if ($risk->status == self::STATUS_SUBMMITED_DELETE ||
                        $risk->status == self::STATUS_REVIEWED_DELETE) {
                        $risk->status = self::STATUS_FEEDBACK_DELETE;
                    }
                    $risk->save();
                }
            }
        }
    }

    /**
     * get conten table after submit
     * @param array
     * @return string
     */
    public static function getContentTable($project)
    {
        $permission = View::checkPermissionEditWorkorder($project);
        $permissionEdit = $permission['persissionEditPM'] || $permission['permissionEditSubPM'] || $permission['permissionEditSale'] || $permission['permissionEditPqa'];
        $permissionEditQA = $permission['permissionEditQA'];
        $checkEditWorkOrder = Task::checkEditWorkOrder($project->id);
        $columnsSelect = ['proj_op_ricks.id', 'content', 'level_important', 'proj_op_ricks.type', 'owner', 'employees.email as owner_email', 'teams.name', 'proj_op_ricks.status',
            'proj_op_ricks.due_date', 'proj_op_ricks.updated_at', 'proj_op_ricks.created_at'];
        $allRisks = self::getAllRisk($project->id, $columnsSelect);
        return view('project::components.risk-management', [
            'permissionEdit' => $permissionEdit,
            'checkEditWorkOrder' => $checkEditWorkOrder,
            'allRisks' => $allRisks,
            'detail' => true,
            'project' => $project,
            'permissionEditQA' => $permissionEditQA,
        ])->render();
    }
    
    /**
     * Risk by id
     * 
     * @param int $riskId
     * @return Risk 
     */
    public static function getById($riskId)
    {
        $item = self::leftJoin("employees as p", "p.id", "=", "proj_op_ricks.performer")
            ->leftJoin("employees as t", "t.id", "=", "proj_op_ricks.tester")
            ->leftJoin("employees as o", "o.id", "=", "proj_op_ricks.owner")
            ->leftJoin("teams", "teams.id", "=", "proj_op_ricks.team_owner")
            ->join('projs', "projs.id", "=", "proj_op_ricks.project_id")
            ->where('proj_op_ricks.id', $riskId)
            ->select('proj_op_ricks.*', 'p.email as performer_email', 't.email as tester_email',
                'o.email as owner_mail', 'teams.name as team_name', 'projs.name as project_name', 'projs.manager_id')
            ->first();
        CacheHelper::put(self::KEY_CACHE_RISK, $item, $riskId);
        return $item;
    }

    public static function checkTypeOfRisk($type)
    {
        return in_array($type, [self::TYPE_QUALITY, self::TYPE_PROCESS, self::TYPE_COST, self::TYPE_DELIVERY]);
    }

    /**
     * Get risks
     * 
     * @param array $conditions
     * @param array $columns
     * @return Risk collection
     */
    public static function getRisks($columns = ['*'], $conditions = [], $order = 'proj_op_ricks.id', $dir = 'desc', $teamIdsAvailable) {
        $risks = self::select($columns);
        $risks->join("projs", "projs.id", "=", "proj_op_ricks.project_id")
                ->leftJoin('project_members', "project_members.project_id", "=", "projs.id")
              ->leftJoin("employees", "employees.id", "=", "proj_op_ricks.owner")
              ->leftJoin('team_members', 'team_members.employee_id', '=', 'projs.leader_id')
              ->leftJoin("teams", "teams.id", "=", "team_members.team_id");
        $risks->addSelect(DB::raw("(SELECT duedate FROM risk_actions WHERE risk_id = proj_op_ricks.id order by duedate desc limit 1) AS duedate"),
            "projs.name as proj_name", "projs.id as proj_id");
        $risks->orderBy($order, $dir);
        $risks->groupBy('proj_op_ricks.id');
        $emp = Permission::getInstance()->getEmployee();
        if (Permission::getInstance()->isScopeCompany(null, 'project::report.risk')) {
            return $risks;
        } else {
            if (!empty($teamIdsAvailable)) {
                $risks->where(function ($p) use ($teamIdsAvailable, $emp) {
                    $p->orWhereIn('teams.id', $teamIdsAvailable)
                        ->orWhere(function ($p) use ($emp) {
                            $p->where('project_members.employee_id', $emp->id)
                                ->whereDate('project_members.start_at', '<=', Carbon::now()->format('Y-m-d'))
                                ->whereDate('project_members.end_at', '>=', Carbon::now()->format('Y-m-d'))
                                ->where('project_members.status', ProjectMember::STATUS_APPROVED);
                        });
                });
            } else {
                $risks->where('project_members.employee_id', $emp->id)
                    ->whereDate('project_members.start_at', '<=', Carbon::now()->format('Y-m-d'))
                    ->whereDate('project_members.end_at', '>=', Carbon::now()->format('Y-m-d'))
                    ->where('project_members.status', ProjectMember::STATUS_APPROVED);
            }
        }
        return $risks;
    }

    /**
     * Get list level risk
    */
    public static function getListLevelRisk($hasCritical = false)
    {
        $data = [];
        return array_merge($data, [
            Lang::get('project::view.Level High') => self::LEVEL_HIGH,
            Lang::get('project::view.Level Normal') => self::LEVEL_NORMAL,
            Lang::get('project::view.Level Low') => self::LEVEL_LOW,
        ]);
    }

    public static function levelImportantLabel($hasCritical = false)
    {
        $data = [];
        if ($hasCritical) {
            $data[self::LEVEL_CRITICAL] = Lang::get('project::view.Critical');
        }
        return array_merge($data, [
            self::LEVEL_HIGH => Lang::get('project::view.Level High'),
            self::LEVEL_NORMAL => Lang::get('project::view.Level Normal'),
            self::LEVEL_LOW => Lang::get('project::view.Level Low'),
        ]);
    }

    /**
     * Get key level important risk by id
    */
    public static function getKeyLevelRisk($id) {
        switch ($id) {
            case self::LEVEL_LOW: return Lang::get('project::view.Level Low');
            case self::LEVEL_NORMAL: return Lang::get('project::view.Level Normal');
            case self::LEVEL_HIGH: return Lang::get('project::view.Level High');
            case self::LEVEL_CRITICAL: return Lang::get('project::view.Level Urgent');
            default: return Lang::get('project::view.Level Low');
               
        }
    }

    
    public static function getRisksOfSale($options = [])
    {
        if (!Permission::getInstance()->isAllow(null, 'project::dashboard')) {
            return null;
        }

        $tblRisk = self::getTableName();
        $tblEmployee = Employee::getTableName();
        $tblTeam = Team::getTableName();
        $tableProject = Project::getTableName();
        $tableSaleProject = SaleProject::getTableName();
        $customerTable = Customer::getTableName();
        $companyTable = Company::getTableName();

        $curEmp = Permission::getInstance()->getEmployee();

        $risks = self::leftJoin("{$tblEmployee}", "{$tblEmployee}.id", '=', "{$tblRisk}.owner")
            ->leftJoin("{$tblTeam}", "{$tblTeam}.id", '=', "{$tblRisk}.team_owner")
            ->join("$tableProject", "{$tableProject}.id", "=", "{$tblRisk}.project_id")
            ->select("{$tblRisk}.*", "{$tblEmployee}.email as owner", "{$tblTeam}.name as team_owner", "{$tableProject}.name as project_name", "{$tableProject}.id as project_id");
        $risks->addSelect(DB::raw("(SELECT COUNT(task_id) FROM task_risk WHERE risk_id = {$tblRisk}.id) AS count_task"));
        //Filter
        if (isset($options['status']) && $options['status'] != '') {
            $risks->where("{$tblRisk}.status", $options['status']);
        }
        if (isset($options['project']) && $options['project'] != '') {
            $risks->where("{$tableProject}.name", 'Like' , '%'.$options['project'].'%');
        }
        if (!empty($options['content'])) {
            $risks->where("{$tblRisk}.content", 'Like', '%'.$options['content'].'%');
        }
        if (!empty($options['weakness'])) {
            $risks->where("{$tblRisk}.weakness", 'Like', '%'.$options['weakness'].'%');
        }
        if (!empty($options['level_important'])) {
            $risks->where("{$tblRisk}.level_important", $options['level_important']);
        }
        if (!empty($options['owner'])) {
            $risks->where(function ($query) use ($options, $tblEmployee, $tblTeam) {
                $query->where("{$tblEmployee}.email", 'Like', '%'.$options['owner'].'%')
                    ->orWhere("{$tblTeam}.name", 'Like', '%'.$options['owner'].'%');
            });
            
        }

        /* Check Permission
         * Company: view all
         * Team, Self: view only project's risk of saler or is manager, supporter of customers
         */
        if (Permission::getInstance()->isScopeCompany(null, 'project::dashboard')) {

        } else {
            $projectIdsManager = Customer::join("{$companyTable}", "{$companyTable}.id", '=', "{$customerTable}.company_id")
                ->join("{$tableProject}", "{$tableProject}.cust_contact_id", "=", "{$customerTable}.id")
                ->where(function ($query) use ($curEmp) {
                    $query->where('cust_companies.manager_id', $curEmp->id)
                        ->orWhere('cust_companies.sale_support_id', $curEmp->id)
                        ->orWhere('cust_companies.created_by', $curEmp->id)
                        ->orWhere('cust_contacts.created_by', $curEmp->id);
                })
                ->select("{$tableProject}.id")
                ->groupBy("{$tableProject}.id")
                ->lists("{$tableProject}.id")
                ->toArray();

            $risks->join("$tableSaleProject", "{$tableProject}.id", "=", "{$tableSaleProject}.project_id")
                ->where(function ($query) use ($tableSaleProject, $curEmp, $tblRisk, $projectIdsManager) {
                    $query->where("{$tableSaleProject}.employee_id", $curEmp->id)
                        ->orWhere("{$tblRisk}.created_by", $curEmp->id)
                        ->orWhere("{$tblRisk}.owner", $curEmp->id)
                        ->orWhereIn("{$tblRisk}.project_id", $projectIdsManager);
                });
      
        }

        $risks->groupBy("{$tblRisk}.id");

        return $risks->get();
    }

    public static function cancelRisk($request)
    {
        if (isset($request->riskId)) {
            $risk = self::find($request->riskId);
            $risk->status = self::STATUS_CANCELLED;
            $risk->save();
            return $risk;
        }
        return false;
    }
}