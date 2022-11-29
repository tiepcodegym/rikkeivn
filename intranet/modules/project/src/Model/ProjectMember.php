<?php

namespace Rikkei\Project\Model;

use FontLib\TrueType\Collection;
use Rikkei\Team\Model\Employee;
use Rikkei\Project\View\View;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Team\View\Permission;
use Lang;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Resource\Model\Programs;
use Exception;
use Rikkei\Resource\Model\Dashboard;
use Rikkei\Resource\View\View as ResourceView;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\Model\Team;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Project\View\ProjConst;
use Rikkei\Resource\View\getOptions;

class ProjectMember extends ProjectWOBase
{
    const TYPE_DEV = 1;
    const TYPE_SQA = 2;
    const TYPE_PQA = 3;
    const TYPE_TEAM_LEADER = 4;
    const TYPE_BRSE = 5;
    const TYPE_SUB_BRSE = 51;
    const TYPE_COMTOR = 6;
    const TYPE_PM = 10;
    const TYPE_COO = 11;
    const TYPE_QALEAD = 12;
    const TYPE_SUBPM = 13;
    const TYPE_REWARD = 20; // add member to project reward
    const TYPE_BA = 22;
    const TYPE_DESIGNER = 23;

    const TYPE_DEV_OT = 101;
    const TYPE_SQA_OT = 102;
    const TYPE_PQA_OT = 103;
    const TYPE_TEAM_LEADER_OT = 104;
    const TYPE_BRSE_OT = 105;
    const TYPE_SUB_BRSE_OT = 151;
    const TYPE_COMTOR_OT = 106;
    const TYPE_PM_OT = 110;
    const TYPE_SUBPM_OT = 113;

    const STATUS_APPROVED = 1;

    /**
     * Color of type, show at Role chart in resource dashboard
     * count color = count project member type
     * when add new project member type then add new color
     */
    const TYPE_COLOR = ["#2ecc71", "#3498db", "#95a5a6", "#9b59b6", "#f1c40f", "#e74c3c", "#34495e"];

    const KEY_CACHE_MEMBER_APPROVED = 'project_member_approved';
    const EFFORT_PM_DEFAUTL = 100;
    const KEY_CACHE_CHART_ROLE = 'project_member_chart_role';
    const KEY_CACHE_CHART_PRO_LANG = 'project_member_pro_lang';
    const KEY_CACHE_CHART_MM_PRO_TYPE = 'project_member_mm_pro_type';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'project_members';

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
    protected $fillable = ['project_id', 'employee_id', 'start_at',
        'end_at', 'effort', 'type'];


    /**
     * Get the project member child
     */
    public function projectMemberChild()
    {
        return $this->hasOne('Rikkei\Project\Model\ProjectMember', 'parent_id');
    }

    /**
     * get label of type member
     *
     * @return array
     */
    public static function getTypeMember($exportRole = false)
    {
        return [
            self::TYPE_DEV => 'Dev',
            self::TYPE_SQA => 'SQA',
            self::TYPE_PQA => 'PQA',
            self::TYPE_PM => 'PM',
            self::TYPE_TEAM_LEADER => $exportRole ? 'Project Team Leader' : 'Team Leader',
            self::TYPE_BRSE => 'BrSE',
            self::TYPE_SUB_BRSE => 'Sub-BrSE',
            self::TYPE_COMTOR => 'Comtor',
            self::TYPE_SUBPM => 'Sub-PM',
            self::TYPE_BA => 'BA',
            self::TYPE_DESIGNER => 'Designer',
        ];
    }

    /**
     * mapping role in skillsheet to project members
     */
    public static function mapProjMemberRoles()
    {
        return [
            getOptions::ROLE_SQA => self::TYPE_SQA,
            getOptions::ROLE_BA => self::TYPE_BA,
            getOptions::ROLE_DESIGNER => self::TYPE_DESIGNER
        ];
    }

    /**
     * get label of type member with onsite
     * @return array
     */
    public static function getTypeWithOT()
    {
        $typeOnsite = [
            self::TYPE_DEV_OT => 'Dev onsite',
            self::TYPE_SQA_OT => 'SQA onsite',
            self::TYPE_PQA_OT => 'PQA onsite',
            self::TYPE_TEAM_LEADER_OT => 'Team leader onsite',
            self::TYPE_BRSE_OT => 'BrSE onsite',
            self::TYPE_SUB_BRSE_OT => Lang::get('resource::view.Sub-BrSE') . ' onsite',
            self::TYPE_COMTOR_OT => 'Comtor onsite',
            self::TYPE_PM_OT => 'PM onsite',
            self::TYPE_SUBPM_OT => 'Sub-PM onsite'
        ];
        return self::getTypeMember() + $typeOnsite;
    }

    /**
     * get label of type member
     *
     * @return array
     */
    public static function getKeyTypeDevTeam()
    {
        return [
            self::TYPE_DEV,
            self::TYPE_PM,
            self::TYPE_TEAM_LEADER,
            self::TYPE_BRSE,
            self::TYPE_SUB_BRSE,
            self::TYPE_COMTOR,
        ];
    }

    /**
     * get label of type member
     *
     * @param int $key
     * @return array
     */
    public static function getTypeMemberByKey($key)
    {
        switch ($key) {
            case self::TYPE_DEV:
                return 'Dev';
            case self::TYPE_SQA:
                return 'SQA';
            case self::TYPE_PQA:
                return 'PQA';
            case self::TYPE_PM:
                return 'PM';
            case self::TYPE_TEAM_LEADER:
                return 'Team leader';
            case self::TYPE_BRSE:
                return 'BrSE';
            case self::TYPE_COMTOR:
                return 'Comtor';
            case self::TYPE_SUBPM:
                return 'Sub-PM';
            case self::TYPE_SUB_BRSE:
                return Lang::get('resource::view.Sub-BrSE');
            case self::TYPE_BA:
                return Lang::get('resource::view.BA');
            case self::TYPE_DESIGNER:
                return Lang::get('resource::view.Designer');
            default:
                return 'Not found';
        }
    }

    /**
     * get type label of type
     *
     * @param int $type
     * @param array $types
     * @return string
     */
    public static function getType($type, array $types = [])
    {
        if (!$types) {
            $types = self::getTypeMember();
        }
        if (isset($types[$type])) {
            return $types[$type];
        }
        return null;
    }

    /**
     * get qa list of project
     * @param int
     * @return object
     */
    public static function getQaListOfProject($projectId)
    {
        return self::where('project_id', $projectId)
            ->where('type', self::TYPE_PQA)
            ->where('status', self::STATUS_APPROVED)
            ->first();
    }

    /**
     * get qa list of project
     * @param int
     * @return object
     */
    public static function getPQAOfProjectLifetime($projectId)
    {
        $now = Carbon::now()->format('Y-m-d');
        return self::where('project_id', $projectId)
            ->where('type', self::TYPE_PQA)
            ->whereDate('start_at', '<=', $now)
            ->whereDate('end_at', '>=', $now)
            ->where('status', self::STATUS_APPROVED)
            ->whereNull('deleted_at')
            ->get();
    }
    
    /**
     * get qa list of project
     * @param int
     * @return object
     */
    public static function getEmpPQAOfProjectLifetime($projectId)
    {
        $now = Carbon::now()->format('Y-m-d');
        return self::select(
            'employees.id',
            'employees.id as employee_id',
            'employees.email',
            'employees.name'
        )
        ->where('project_id', $projectId)
        ->leftJoin('employees', 'project_members.employee_id', '=', 'employees.id')
        ->where('type', self::TYPE_PQA)
        ->whereDate('start_at', '<=', $now)
        ->whereDate('end_at', '>=', $now)
        ->where('project_members.status', self::STATUS_APPROVED)
        ->whereNull('project_members.deleted_at')
        ->get();
    }
    

    /**
     * get member approve in project
     *
     * @param int $projectId
     * @return object
     */
    public static function getMemberAprroved($projectId)
    {
        if ($collection = CacheHelper::get(self::KEY_CACHE_MEMBER_APPROVED, $projectId)) {
            return $collection;
        }
        $employeeTable = Employee::getTableName();
        $memberTable = self::getTableName();

        $collection = self::select("{$employeeTable}.id as id", $employeeTable . '.name',
            $employeeTable . '.email', $memberTable . '.type',
            $memberTable . '.start_at', $memberTable . '.end_at', $memberTable . '.effort')
            ->join($employeeTable, "{$employeeTable}.id", '=', "{$memberTable}.employee_id")
            ->where($memberTable . '.status', self::STATUS_APPROVED)
            ->where('project_id', $projectId);
        if (Employee::isUseSoftDelete()) {
            $collection->whereNull("{$employeeTable}.deleted_at");
        }
        $collection = $collection->get();
        CacheHelper::put(self::KEY_CACHE_MEMBER_APPROVED, $collection, $projectId);
        return $collection;
    }

    /**
     * get total effort member approved
     *
     * @param object $memberApproved
     * @param int $projectId
     * @return int
     */
    public static function getTotalEffortMemberApproved($memberApproved = null, $projectId = null)
    {
        $result = [
            'total' => 0, //mm
            'current' => 0, //mm
            'effort_dev_current' => 0 //md
        ];
        if ($memberApproved === null) {
            $memberApproved = self::getMemberAprroved($projectId);
        }
        if (!count($memberApproved)) {
            return $result;
        }
        $current = Carbon::now();
        $resultMMAnother = null;
        $typeMM = Project::getTypeMMById($projectId);
        foreach ($memberApproved as $member) {
            $startDate = $member->start_at;
            $endDate = $member->end_at;
            $effortMember = $member->effort;
            if (!$startDate || !$endDate || !$effortMember) {
                continue;
            }
            $endDate = Carbon::parse($endDate);
            $startDate = Carbon::parse($startDate);
            $interval = $endDate->diff($current);
            $dayWorkEnddate = View::getMM($startDate, $endDate, $typeMM, $resultMMAnother);
            $effortEnddate = round($dayWorkEnddate * $effortMember / 100, 2);
            $result['total'] += $effortEnddate;
            // member not start ultil now
            if (!$current->diff($startDate)->invert) {
                continue;
            }
            if ($interval->invert == 0) { // end date work < current
                $effortCurrent = $effortEnddate;
                $effortCurrentMD = $effortCurrent;
                if ($typeMM == Project::MM_TYPE) {
                    $effortCurrentMD = round($resultMMAnother['dayWorks'] * $effortMember / 100, 2);
                }
            } else {
                $dayWorkCurrent = View::getMM($startDate, $current, $typeMM, $resultMMAnother);
                $effortCurrent = round($dayWorkCurrent * $effortMember / 100, 2);
                $effortCurrentMD = $effortCurrent;
                if ($typeMM == Project::MM_TYPE) {
                    $effortCurrentMD = round($resultMMAnother['dayWorks'] * $effortMember / 100, 2);
                }
            }
            $result['current'] += $effortCurrent;
            if (in_array($member->type, [self::TYPE_DEV])) {
                $result['effort_dev_current'] += $effortCurrentMD;
            }
        }
        return $result;
    }

    /**
     * get total effort team approved
     *
     * @param object $memberApproved
     * @param int $projectId
     * @return int
     */
    public static function getTotalEffortTeamApproved($memberApproved = null, $projectId = null)
    {
        if ($memberApproved === null) {
            $memberApproved = self::getMemberAprroved($projectId);
        }
        $effort = [
            'total' => 0,
            'dev' => 0,
            'pm' => 0,
            'qa' => 0,
            'count' => 0,
        ];

        $arrayId = [];
        $typeMM = Project::getTypeMMById($projectId);
        foreach ($memberApproved as $member) {
            if (!in_array($member->id, $arrayId)) {
                array_push($arrayId, $member->id);
            }
            $startDate = $member->start_at;
            $endDate = $member->end_at;
            $effortMember = $member->effort;
            if (!$startDate || !$endDate || !$effortMember) {
                continue;
            }
            if ($member->type == self::TYPE_PM || $member->type == self::TYPE_TEAM_LEADER || $member->type == self::TYPE_SUBPM) {
                $effort['pm'] += round(View::getMM($startDate, $endDate, $typeMM) * $effortMember / 100, 2);
            } else if ($member->type == self::TYPE_PQA) {
                $effort['qa'] += round(View::getMM($startDate, $endDate, $typeMM) * $effortMember / 100, 2);
            } else {
                $effort['dev'] += round(View::getMM($startDate, $endDate, $typeMM) * $effortMember / 100, 2);
            }
        }
        $effort['count'] = count($arrayId);
        $effort['total'] = $effort['pm'] + $effort['qa'] + $effort['dev'];
        if ($effort['total']) {
            $effort['dev'] = round($effort['dev'] / $effort['total'] * 100, 2);
            $effort['qa'] = round($effort['qa'] / $effort['total'] * 100, 2);
            $effort['pm'] = round(100 - ($effort['dev'] + $effort['qa']), 2);
        }
        return $effort;
    }

    /**
     * get member of project
     *
     * @param int $projectId
     * @return object
     */
    public static function getMemberOfProject($projectId)
    {
        if ($collection = CacheHelper::get(self::KEY_CACHE_WO, $projectId)) {
            return $collection;
        }
        $employeeTable = Employee::getTableName();
        $memberTable = self::getTableName();

        $collection = self::select("{$memberTable}.id as id", 'name', 'email', 'employee_id',
            DB::raw("group_concat(distinct type SEPARATOR ', ') as type_emp"),
             'start_at', 'end_at', 'effort', 'status', "{$memberTable}.created_at as created_at", "{$memberTable}.is_disabled as is_disabled")
            ->join($employeeTable, "{$employeeTable}.id", '=', "{$memberTable}.employee_id")
            ->where("{$memberTable}.status", self::STATUS_APPROVED)
            ->where('project_id', $projectId);

        if (Employee::isUseSoftDelete()) {
            $collection->whereNull("{$employeeTable}.deleted_at");
        }
        if (config('project.workorder_approved.project_member')) {
            $collection = $collection->whereNull('parent_id');
        }
        $collection->groupBy('employee_id');

        $collection = $collection->orderBy('created_at')->get();
        CacheHelper::put(self::KEY_CACHE_WO, $collection, $projectId);
        return $collection;
    }

    /*
     * add project member
     * @param array
     */
    public static function insertProjectMember($input, $project)
    {
        DB::beginTransaction();
        try {
            $arrayLablelStatus = self::getLabelStatusForProjectLog();
            $labelElement = self::getLabelElementForWorkorder()[Task::TYPE_WO_PROJECT_MEMBER];
            //get project draft
            $typeResource = $project->type_mm;
            $projectDraft = Project::where('parent_id', $project->id)
                ->where('status', '!=', Project::STATUS_APPROVED)
                ->first();
            if ($projectDraft) {
                $typeResource = $projectDraft->type_mm;
            }

            $member = self::find($input['id']);
            if (config('project.workorder_approved.project_member')) {
                // add new member
                if (!isset($input['id']) || !is_numeric($input['id'])) {
                    $memberChild = new ProjectMember();
                    $memberChild->project_id = $input['project_id'];
                    $memberChild->status = self::STATUS_DRAFT;
                    if (isset($input['status']) && $input['status']) {
                        $memberChild->status = $input['status'];
                    }
                    $memberChild->fill($input);
                    $memberChild->created_by = Permission::getInstance()->getEmployee()->id;
                    $status = self::STATUS_DRAFT;
                    $memberChild->flatResourceItem(false, $typeResource);
                    $memberChild->save([
                        'prog_lang' => true,
                        'project' => $project,
                        'prog_ids' => CoreView::getValueArray($input, ['prog_langs'], [])
                    ]);
                } else { // edit member
                    $allStatus = ProjConst::woStatus()['sDelete'];
                    // edit member approved
                    if ($member->status == self::STATUS_APPROVED) {
                        $memberParent = $member;
                        $memberChild = new self();
                        $memberChild->fill($input);
                        $memberChild->flatResourceItem(false, $typeResource);
                        $memberParent->loadProgramLangs();
                        $memberChild->prog_langs = CoreView::getValueArray($input, ['prog_langs'], []);
                        $isChangeValue = View::isChangeValue($memberParent, $memberChild);
                        if (!$isChangeValue) {
                            return $memberParent;
                        }
                        $memberOrigin = $memberParent->original;
                        $memberAttributes = $memberChild->attributes;
                        $status = self::STATUS_EDIT_APPROVED;
                        $memberChild->project_id = $input['project_id'];
                        $memberChild->status = self::STATUS_DRAFT_EDIT;
                        $memberChild->parent_id = $input['id'];
                        $memberChild->created_by = Permission::getInstance()->getEmployee()->id;
                    } else if (in_array((int)$member->status, $allStatus)) {
                        // edit member delete -> error
                        return $member;
                    } else { // edit member not approve
                        $memberParent = self::where('id', $member->parent_id)
                            ->first();
                        $memberChild = $member;
                        if ($memberParent) {
                            $memberChild->fill($input);
                            $memberChild->flatResourceItem(false, $typeResource);
                            $memberOrigin = $memberParent->original;
                            $memberAttributes = $memberChild->attributes;
                            $status = self::STATUS_EDIT_APPROVED;
                            $memberParent->loadProgramLangs();
                            $memberChild->prog_langs = CoreView::getValueArray($input, ['prog_langs'], []);
                            $isChangeValue = View::isChangeValue($memberParent, $member);
                            if (!$isChangeValue) {
                                if (in_array((int)$memberChild->status, [self::STATUS_DRAFT_EDIT, self::STATUS_FEEDBACK_EDIT])) {
                                    $memberChild->forceDelete();
                                } else {
                                    $memberChild->status = self::STATUS_FEEDBACK_DELETE;
                                    $memberChild->save([
                                        'prog_lang' => true,
                                        'project' => $project,
                                        'prog_ids' => CoreView::getValueArray($input, ['prog_langs'], [])
                                    ]);
                                }
                                DB::commit();
                                CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
                                return $memberParent;
                            }
                        } else {
                            $memberChild->fill($input);
                            $memberChild->flatResourceItem(false, $typeResource);
                            $memberAttributes = $memberChild->attributes;
                            $memberOrigin = $memberChild->original;
                        }
                        switch ($memberChild->status) {
                            case self::STATUS_FEEDBACK_EDIT:
                                $status = self::STATUS_FEEDBACK_EDIT;
                                $memberChild->status = self::STATUS_DRAFT_EDIT;
                                break;
                            case self::STATUS_FEEDBACK:
                                $status = self::STATUS_FEEDBACK;
                                $memberChild->status = self::STATUS_DRAFT;
                                break;
                            case self::STATUS_DRAFT_EDIT:
                                $status = self::STATUS_DRAFT_EDIT;
                                break;
                            case self::STATUS_DRAFT:
                                $status = self::STATUS_UPDATED_DRAFT;
                                break;
                            default:
                                $status = self::STATUS_EDIT_APPROVED;
                                break;
                        }
                    }
                    $memberChild->save([
                        'prog_lang' => true,
                        'project' => $project,
                        'prog_ids' => CoreView::getValueArray($input, ['prog_langs'], [])
                    ]);
                }
                $statusText = $arrayLablelStatus[$status];
                if ($status == self::STATUS_DRAFT || $status == self::STATUS_EDIT_APPROVED) {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                } else {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, $memberAttributes, $memberOrigin);
                }
            } else {
                if (isset($input['id'])) {
                    $status = self::STATUS_EDIT;
                    $memberChild = self::find($input['id']);
                } else {
                    $status = self::STATUS_ADD;
                    $memberChild = new self;
                    $memberChild->project_id = $input['project_id'];
                    $memberChild->created_by = Permission::getInstance()->getEmployee()->id;
                    $memberChild->status = self::STATUS_APPROVED;
                }
                $memberChild->fill($input);
                $memberAttributes = $member->attributes;
                $memberOrigin = $member->original;
                $memberChild->flatResourceItem(false, $typeResource);
                $memberChild->save([
                    'prog_lang' => true,
                    'project' => $project,
                    'prog_ids' => CoreView::getValueArray($input, ['prog_langs'], [])]);

                $statusText = $arrayLablelStatus[$status];
                if ($status == self::STATUS_ADD) {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                } else {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, $memberAttributes, $memberOrigin);
                }
            }
            DB::commit();
            CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
            return $memberChild;
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    public static function countMemberProj($id, $current = null)
    {
         $project = Project::getProjectById($id);
         $collection =  self::select('employee_id')->where('project_id', $id)
            ->where('status', self::STATUS_APPROVED)
            ->whereDate('start_at', '>=', $project['start_at'])
            ->whereDate('end_at', '<=', $project['end_at'])
            ->where('type', '!=', self::TYPE_PQA);
         if ($current) {
             $collection->whereDate('start_at', '<=', Carbon::now()->format('Y-m-d'))
                 ->whereDate('end_at', '>=', Carbon::now()->format('Y-m-d'));
         }
            return $collection->groupBy('employee_id')
            ->get()->count();
    }

    /*
     * delete project member
     *
     * @param array
     * @return array
     */
    public static function deleteProjectMember($input)
    {
        $arrayLablelStatus = self::getLabelStatusForProjectLog();
        $labelElement = self::getLabelElementForWorkorder()[Task::TYPE_WO_PROJECT_MEMBER];
        $response = [
            'delete' => 1,
            'approve' => 0,
            'member' => null
        ];
        if (config('project.workorder_approved.project_member')) {
            $member = self::find($input['id']);
            if (!$member) {
                $response['delete'] = 0;
                return $response;
            }
            if ($member->status == self::STATUS_APPROVED) {
                $memberDelete = $member->replicate();
                $memberDelete->status = self::STATUS_DRAFT_DELETE;
                $memberDelete->parent_id = $input['id'];
                $status = self::STATUS_DELETE_APPROVED;
                if ($memberDelete->save()) {
                    CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
                    $response['approve'] = 1;
                    $response['member'] = $memberDelete;
                    return $response;
                }
            } else {
                switch ($member->status) {
                    case self::STATUS_DRAFT_EDIT:
                        $status = self::STATUS_DELETE_DRAFT_EDIT;
                        break;
                    case self::STATUS_DRAFT:
                        $status = self::STATUS_DELETE_DRAFT;
                        break;
                    case self::STATUS_FEEDBACK_EDIT:
                        $status = self::STATUS_DELETE_FEEDBACK_EDIT;
                        break;
                    case self::STATUS_FEEDBACK:
                        $status = self::STATUS_DELETE_FEEDBACK;
                        break;
                    default:
                        $status = self::STATUS_DRAFT_DELETE;
                        break;
                }
                if ($member->delete()) {
                    CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
                    $statusText = $arrayLablelStatus[$status];
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                    $response['member'] = $member;
                    return $response;
                }
            }
        } else {
            $status = self::STATUS_DELETE;
            $member = self::find($input['id']);
            if ($member) {
                if ($member->delete()) {
                    CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
                    $statusText = $arrayLablelStatus[$status];
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                    $response['member'] = $member;
                    return $response;
                }
            }
        }
        $response['delete'] = 0;
        return $response;
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
        if ($typeSubmit) {
            $memberDraft = self::getProjectMemberByStatus([self::STATUS_DRAFT, self::STATUS_FEEDBACK], $input);
        } else {
            $memberDraft = self::getProjectMemberByStatus([self::STATUS_DRAFT], $input);
        }
        if (count($memberDraft) > 0) {
            $title = Lang::get('project::view.Add object for Team Project');
            $content .= view('project::template.content-task', ['inputs' => $memberDraft, 'title' => $title, 'type' => $typeWO])->render();
        }

        if ($typeSubmit) {
            $memberDraftEdit = self::getProjectMemberByStatus([self::STATUS_DRAFT_EDIT, self::STATUS_FEEDBACK_EDIT], $input);
        } else {
            $memberDraftEdit = self::getProjectMemberByStatus([self::STATUS_DRAFT_EDIT], $input);
        }
        if (count($memberDraftEdit) > 0) {
            $title = Lang::get('project::view.Edit object for Team Project');
            $content .= view('project::template.content-task', ['inputs' => $memberDraftEdit, 'title' => $title, 'type' => $typeWO])->render();
        }

        if ($typeSubmit) {
            $memberDelete = self::getProjectMemberByStatus([self::STATUS_DRAFT_DELETE, self::STATUS_FEEDBACK_DELETE], $input);
        } else {
            $memberDelete = self::getProjectMemberByStatus([self::STATUS_DRAFT_DELETE], $input);
        }
        if (count($memberDelete) > 0) {
            $title = Lang::get('project::view.Delete object for Team Project');
            $content .= view('project::template.content-task', ['inputs' => $memberDelete, 'title' => $title, 'type' => $typeWO])->render();
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
     * get project member by status
     * @param int
     * @param array
     * @return collection
     */
    public static function getProjectMemberByStatus($status, $input)
    {
        $employeeTable = Employee::getTableName();
        $memberTable = self::getTableName();
        $projMemberProgLangsTable = ProjectMemberProgramLang::getTableName();
        $programTable = Programs::getTableName();

        $members = self::select("{$memberTable}.id as id", $employeeTable . '.name',
            $employeeTable . '.email', $memberTable . '.type',
            DB::raw("DATE(`{$memberTable}`.`start_at`) as start_at"),
            DB::raw("DATE(`{$memberTable}`.`end_at`) as end_at"),
            $memberTable . '.effort', $memberTable . '.status',
            $memberTable . '.parent_id', $memberTable . '.flat_resource')
            ->join($employeeTable, "{$employeeTable}.id", '=', "{$memberTable}.employee_id")
            ->leftJoin($projMemberProgLangsTable, $projMemberProgLangsTable . '.proj_member_id',
                '=', $memberTable . '.id')
            ->leftJoin($programTable, $programTable . '.id', '=',
                $projMemberProgLangsTable . '.prog_lang_id')
            ->addSelect(DB::raw('GROUP_CONCAT(' . $programTable . '.name SEPARATOR \', \')'
                . ' as prog_langs'))
            ->whereIn($memberTable . '.status', $status)
            ->where($memberTable . '.project_id', $input['project_id'])
            ->groupBy($memberTable . '.id');
        if (isset($input['parent_id']) && $input['parent_id']) {
            $members->where($memberTable . '.parent_id', $input['parent_id'])
                ->whereNotNull($memberTable . '.task_id');
        }
        if (isset($input['member_id']) && $input['member_id']) {
            $members->where($memberTable . '.id', $input['member_id']);
        }
        if (Employee::isUseSoftDelete()) {
            $members->whereNull("{$employeeTable}.deleted_at");
        }
        return $members->get();
    }

    /**
     * update status when submit workorder
     * @param array
     */
    public static function updateStatusWhenSubmitWorkorder($task, $input)
    {
        $memberDraft = self::where('project_id', $input['project_id'])
            ->whereIn('status', [self::STATUS_DRAFT, self::STATUS_FEEDBACK])
            ->get();
        if (count($memberDraft) > 0) {
            foreach ($memberDraft as $member) {
                $member->status = self::STATUS_SUBMITTED;
                $member->task_id = $task->id;
                $member->save();
            }
        }

        $memberEdit = self::where('project_id', $input['project_id'])
            ->whereIn('status', [self::STATUS_DRAFT_EDIT, self::STATUS_FEEDBACK_EDIT])
            ->whereNotNull('parent_id')
            ->get();
        if (count($memberEdit) > 0) {
            foreach ($memberEdit as $member) {
                $member->status = self::STATUS_SUBMIITED_EDIT;
                $member->task_id = $task->id;
                $member->save();
            }
        }

        $memberDelete = self::where('project_id', $input['project_id'])
            ->whereIn('status', [self::STATUS_DRAFT_DELETE, self::STATUS_FEEDBACK_DELETE])
            ->get();
        if (count($memberDelete)) {
            foreach ($memberDelete as $member) {
                $member->status = self::STATUS_SUBMMITED_DELETE;
                $member->task_id = $task->id;
                $member->save();
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
        self::updateItemWorkorder(self::TYPE_EDIT, $statusTask, $projectId);
        self::updateItemWorkorder(self::TYPE_DELETE, $statusTask, $projectId);
        self::updateItemWorkorder(self::TYPE_ADD, $statusTask, $projectId);
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
        $memberDraft = self::where('project_id', $projectId);
        if ($statusTask == Task::STATUS_APPROVED) {
            if ($type == self::TYPE_ADD) {
                $memberDraft = $memberDraft->where('status', self::STATUS_REVIEWED);
            } else if ($type == self::TYPE_EDIT) {
                $memberDraft = $memberDraft->where('status', self::STATUS_REVIEWED_EDIT);
            } else {
                $memberDraft = $memberDraft->where('status', self::STATUS_REVIEWED_DELETE);
            }
        } else if ($statusTask == Task::STATUS_FEEDBACK || $statusTask == Task::STATUS_REVIEWED) {
            if ($type == self::TYPE_ADD) {
                $memberDraft = $memberDraft->whereIn('status', [self::STATUS_SUBMITTED, self::STATUS_REVIEWED]);
            } else if ($type == self::TYPE_EDIT) {
                $memberDraft = $memberDraft->whereIn('status', [self::STATUS_SUBMIITED_EDIT, self::STATUS_REVIEWED_EDIT]);
            } else {
                $memberDraft = $memberDraft->whereIn('status', [self::STATUS_SUBMMITED_DELETE, self::STATUS_REVIEWED_DELETE]);
            }
        }
        $memberDraft = $memberDraft->get();
        if (count($memberDraft) > 0) {
            if ($statusTask == Task::STATUS_APPROVED) {
                if ($type == self::TYPE_DELETE) {
                    foreach ($memberDraft as $member) {
                        $memberParent = self::find($member->parent_id);
                        $member->delete();
                        if ($memberParent) {
                            $memberParent->delete();
                        }
                    }
                } else if ($type == self::TYPE_ADD) {
                    foreach ($memberDraft as $key => $member) {
                        $pm = self::getPmOfProject($projectId);
                        if ($pm) {
                            $pmEdit = self::getPmEditOfProject($projectId, $pm->id);
                        }
                        /*if ($member->type == self::TYPE_PM) {
                            if (isset($pmEdit)) {
                                if ($pmEdit) {
                                    $getMemberDisable = View::checkMemberDisable($pmEdit, $member);
                                    if ($getMemberDisable) {
                                        $memberDisable = $member;
                                        $memberApproved = $pmEdit;
                                    } else {
                                        $memberDisable = $pmEdit;
                                        $memberApproved = $member;
                                    }
                                    $memberDisable->parent_id = null;
                                    $memberDisable->save();
                                    $memberDisable->task_id = null;
//                                    $memberDisable->is_disabled = ProjectWOBase::STATUS_DISABLED;
                                    $memberDisable->status = self::STATUS_APPROVED;
                                    if($memberDisable->end_at <= $memberApproved->start_at) {
                                        $memberDisable->end_at = $memberApproved->start_at;
                                    }
                                    $memberDisable->save();
                                    $project = Project::find($projectId);
                                    if ($project) {
                                        $project->manager_id = $memberApproved->employee_id;
                                        $project->save();
                                    }
                                    $memberApproved->status = self::STATUS_APPROVED;
                                    $memberApproved->parent_id = null;
                                    $memberApproved->task_id = null;
                                    $memberApproved->save();
                                    $pm->delete();
                                }
                            } else {
                                if($pm) {
                                    $getMemberDisable = View::checkMemberDisable($pm, $member);
                                    if ($getMemberDisable) {
                                        $memberDisable = $member;
                                        $memberApproved = $pm;
                                    } else {
                                        $memberDisable = $pm;
                                        $memberApproved = $member;
                                    }
//                                    $memberDisable->is_disabled = ProjectWOBase::STATUS_DISABLED;
                                    if($memberDisable->end_at <= $memberApproved->start_at) {
                                        $memberDisable->end_at = $memberApproved->start_at;
                                    }
                                    $memberDisable->status = self::STATUS_APPROVED;
                                    $memberDisable->save();
                                    $project = Project::find($projectId);
                                    if ($project) {
                                        $project->manager_id = $memberApproved->employee_id;
                                        $project->save();
                                    }
                                    $memberApproved->status = self::STATUS_APPROVED;
                                    $memberApproved->save();
                                }
                            }
                        } else {*/
                        $member->status = self::STATUS_APPROVED;
                        $member->save();
                        /*}*/
                    }
                } else { // edit item
                    foreach ($memberDraft as $member) {
                        $memberParent = self::find($member->parent_id);
                        if ($memberParent) {
                            /*if ($member->type == self::TYPE_PM) {
                                $project = Project::find($projectId);
                                if ($project) {
                                    $project->manager_id = $member->employee_id;
                                    $project->save();
                                }
                            }*/
                            ProjectMemberProgramLang::updateLangForParent($memberParent, $member);
                            View::updateValueWhenApproved($memberParent, $member);
                        }
                    }
                }
            } else if ($statusTask == Task::STATUS_REVIEWED) {
                foreach ($memberDraft as $member) {
                    if ($member->status == self::STATUS_SUBMITTED) {
                        $member->status = self::STATUS_REVIEWED;
                    }
                    if ($member->status == self::STATUS_SUBMIITED_EDIT) {
                        $member->status = self::STATUS_REVIEWED_EDIT;
                    }
                    if ($member->status == self::STATUS_SUBMMITED_DELETE) {
                        $member->status = self::STATUS_REVIEWED_DELETE;
                    }
                    $member->save();
                }
            } else if ($statusTask == Task::STATUS_FEEDBACK) {
                foreach ($memberDraft as $member) {
                    if ($member->status == self::STATUS_SUBMITTED ||
                        $member->status == self::STATUS_REVIEWED) {
                        $member->status = self::STATUS_FEEDBACK;
                    }
                    if ($member->status == self::STATUS_SUBMIITED_EDIT ||
                        $member->status == self::STATUS_REVIEWED_EDIT) {
                        $member->status = self::STATUS_FEEDBACK_EDIT;
                    }
                    if ($member->status == self::STATUS_SUBMMITED_DELETE ||
                        $member->status == self::STATUS_REVIEWED_DELETE) {
                        $member->status = self::STATUS_FEEDBACK_DELETE;
                    }
                    $member->save();
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
        $permissionEdit = $permission['persissionEditPM'] || $permission['permissionEditSubPM'];
        $checkEditWorkOrder = Task::checkEditWorkOrder($project->id);
        $allMembers = self::getMembersAllStatusOfProject($project->id);
        $allTypeMember = self::getTypeMember();
        $arrayCoo = CoreConfigData::getCOOAccount();
        return view('project::components.project-member', [
            'permissionEdit' => $permissionEdit,
            'checkEditWorkOrder' => $checkEditWorkOrder,
            'allMembers' => $allMembers,
            'allTypeMember' => $allTypeMember,
            'arrayCoo' => $arrayCoo,
            'detail' => true,
            'project' => $project,
            'projectProgramsOption' => ProjectProgramLang::getProgramLangOfProject($project)
        ])->render();
    }

    /**
     * get member of project
     *
     * @param int $projectId
     * @return array
     */
    public static function getMembersAllStatusOfProject($projectId)
    {
        $employeeTable = Employee::getTableName();
        $memberTable = self::getTableName();
        $projMemberProgLangTable = ProjectMemberProgramLang::getTableName();

        $collection = self::select("{$memberTable}.id as id", $employeeTable . '.name',
            $employeeTable . '.email', $memberTable . '.employee_id',
            $memberTable . '.type', $memberTable . '.start_at', $memberTable . '.end_at',
            $memberTable . '.effort', $memberTable . '.status',
            "{$memberTable}.created_at as created_at",
            "{$memberTable}.is_disabled as is_disabled", $memberTable . '.flat_resource',
            $memberTable . '.parent_id')
            ->addSelect(DB::raw('GROUP_CONCAT(`' .
                $projMemberProgLangTable . '`.`prog_lang_id` SEPARATOR \',\') '
                . 'as prog_lang'))
            ->join($employeeTable, "{$employeeTable}.id", '=', "{$memberTable}.employee_id")
            ->leftJoin($projMemberProgLangTable, $projMemberProgLangTable . '.proj_member_id',
                '=', $memberTable . '.id')
            ->where('project_id', $projectId)
            ->orderBy('is_disabled', 'desc')
            ->groupBy($memberTable . '.id');
        if (Employee::isUseSoftDelete()) {
            $collection->whereNull("{$employeeTable}.deleted_at");
        }
        $collection = $collection->get();
        if (!count($collection)) {
            return null;
        }
        $programLang = Programs::getListOption();
        $result = [];
        foreach ($collection as $item) {
            $itemLangs = $item->prog_lang;
            if ($itemLangs) {
                $itemLangs = explode(',', $itemLangs);
                $progLangIds = [];
                $progLangNames = '';
                foreach ($itemLangs as $itemLang) {
                    $itemLang = (int)(trim($itemLang));
                    $progLangIds[] = $itemLang;
                    if (isset($programLang[$itemLang]) && $programLang[$itemLang]) {
                        $progLangNames .= $programLang[$itemLang] . ', ';
                    }
                }
                $progLangNames = substr($progLangNames, 0, -2);
                $item->prog_lang_ids = $progLangIds;
                $item->prog_lang_names = $progLangNames;
            } else {
                $item->prog_lang = null;
                $item->prog_lang_ids = [];
                $item->prog_lang_names = null;
            }
            if (View::checkItemIsParent($item->status)) {
                $result[$item->id]['parent'] = $item;
            } else {
                $result[$item->parent_id]['child'] = $item;
            }
        }
        return $result;
    }

    /**
     * get member of project
     *
     * @param model $project
     * @return array
     */
    public static function getAllMemberAvai($project)
    {
        $employeeTable = Employee::getTableName();
        $memberTable = self::getTableName();
        $projMemberProgLangTable = ProjectMemberProgramLang::getTableName();

        return self::select(["{$memberTable}.id as id", $employeeTable . '.name',
            $employeeTable . '.email', $memberTable . '.employee_id',
            $memberTable . '.type', $memberTable . '.start_at', $memberTable . '.end_at',
            $memberTable . '.effort', $memberTable . '.status',
            "{$memberTable}.created_at", $memberTable . '.flat_resource',
            $memberTable . '.parent_id',
            DB::raw('GROUP_CONCAT(`' .
                $projMemberProgLangTable . '`.`prog_lang_id` SEPARATOR \'-\') '
                . 'as prog_lang_ids')])
            ->join($employeeTable, "{$employeeTable}.id", '=', "{$memberTable}.employee_id")
            ->leftJoin($projMemberProgLangTable, $projMemberProgLangTable . '.proj_member_id',
                '=', $memberTable . '.id')
            ->where('project_id', $project->id)
            ->whereNull("{$employeeTable}.deleted_at")
            ->groupBy($memberTable . '.id')
            ->get();
    }

    /**
     * get PM of project
     *
     * @param int $projectId
     * @return object
     */
    public static function getPmOfProject($projectId)
    {
        return self::where('project_id', $projectId)
            ->where('type', self::TYPE_PM)
            ->where('status', ProjectWOBase::STATUS_APPROVED)
            ->where('is_disabled', '!=', ProjectWOBase::STATUS_DISABLED)
            ->first();
    }

    /**
     * get PM of Project Member approved and draft
     *
     * @param int $id
     * @return object
     */
    public static function getPmByIdProjectMember($id)
    {
        $pmId = self::where('id', $id)
            ->where('type', self::TYPE_PM)
            ->whereIn('status', array(ProjectWOBase::STATUS_APPROVED, ProjectWOBase::STATUS_DRAFT))
            //->where('is_disabled', '!=', ProjectWOBase::STATUS_DISABLED)
            ->first();
        if ($pmId) {
            return $pmId->employee_id;
        }
        return null;
    }

    /**
     * get all PM of project approved and draft
     *
     * @param int $projectId
     * @return object
     */
    public static function getAllPmOfProject($projectId)
    {
        $tableName = self::getTableName();
        return self
            //select all Pm and exclude deleted Pms
            ::whereNotIn('id', function ($query) use ($tableName, $projectId) {
                $query->select('parent_id')
                    ->from($tableName)
                    ->where('project_id', $projectId)
                    ->where('type', self::TYPE_PM)
                    ->whereIn('status', array(ProjectWOBase::STATUS_DRAFT_DELETE, ProjectWOBase::STATUS_SUBMMITED_DELETE,
                        ProjectWOBase::STATUS_REVIEWED_DELETE, ProjectWOBase::STATUS_FEEDBACK_DELETE));
            })
            ->WhereNotIn('id', function ($query) use ($tableName, $projectId) {
                $query->select('id')
                    ->from($tableName)
                    ->where('project_id', $projectId)
                    ->where('type', self::TYPE_PM)
                    ->whereIn('status', array(ProjectWOBase::STATUS_DRAFT_DELETE, ProjectWOBase::STATUS_SUBMMITED_DELETE,
                        ProjectWOBase::STATUS_REVIEWED_DELETE, ProjectWOBase::STATUS_FEEDBACK_DELETE));
            })
            //exclude edited Pms.
            ->whereNotIn('id', function ($query) use ($tableName, $projectId) {
                $query->select('id')
                    ->from($tableName)
                    ->where('project_id', $projectId)
                    ->whereIn('id', function ($query) use ($tableName) {
                        $query->select('parent_id')
                            ->from($tableName)
                            ->where('type', '<>', self::TYPE_PM)
                            ->whereIn('status', array(ProjectWOBase::STATUS_DRAFT_EDIT, ProjectWOBase::STATUS_SUBMIITED_EDIT,
                                ProjectWOBase::STATUS_REVIEWED_EDIT, ProjectWOBase::STATUS_FEEDBACK_EDIT));
                    })
                    ->where('type', self::TYPE_PM)
                    ->where('status', ProjectWOBase::STATUS_APPROVED);
            })
            ->where('type', self::TYPE_PM)
            ->where('project_id', $projectId)
            ->select('employee_id')
            ->groupBy('employee_id')
            ->get()
            ->toArray();
    }

    public static function getPmEditOfProject($projectId, $pmId)
    {
        return self::where('project_id', $projectId)
            ->where('type', self::TYPE_PM)
            ->where('parent_id', $pmId)
            ->where('is_disabled', '!=', ProjectWOBase::STATUS_DISABLED)
            ->first();
    }

    /**
     * get total effort all team
     *
     * @param int $projectId
     * @return array
     */
    public static function getTotalEffortAllTeam($projectId)
    {
        $members = self::getMemberDraftOfProject($projectId);
        $effort = [
            'total' => 0,
            'dev' => 0,
            'pm' => 0,
            'qa' => 0,
        ];
        $arrayId = [];
        $typeMM = Project::getTypeMMById($projectId);
        foreach ($members as $member) {
            if (!in_array($member->employee_id, $arrayId) && !$member->parent_id) {
                array_push($arrayId, $member->employee_id);
            }
            $startDate = $member->start_at;
            $endDate = $member->end_at;
            $effortMember = $member->effort;
            if (!$startDate || !$endDate || !$effortMember) {
                continue;
            }

            if (!count($member->projectMemberChild)) {
                if ($member->type == self::TYPE_PM || $member->type == self::TYPE_TEAM_LEADER || $member->type == self::TYPE_SUBPM) {
                    $effort['pm'] += round(View::getMM($startDate, $endDate, $typeMM) * $effortMember / 100, 2);
                } else if ($member->type == self::TYPE_PQA) {
                    $effort['qa'] += round(View::getMM($startDate, $endDate, $typeMM) * $effortMember / 100, 2);
                } else {
                    $effort['dev'] += round(View::getMM($startDate, $endDate, $typeMM) * $effortMember / 100, 2);
                }
            }
        }
        $effort['count'] = count($arrayId);
        $effort['total'] = $effort['pm'] + $effort['qa'] + $effort['dev'];
        if ($effort['total']) {
            $effort['dev'] = round($effort['dev'] / $effort['total'] * 100, 2);
            $effort['qa'] = round($effort['qa'] / $effort['total'] * 100, 2);
            $effort['pm'] = round(100 - ($effort['dev'] + $effort['qa']), 2);
        }
        return $effort;
    }

    /**
     * get member draft of project
     * @param int
     * @return array
     */
    public static function getMemberDraftOfProject($projectId)
    {
        return self::where('project_id', $projectId)
            ->whereNotIn('status', [ProjectWOBase::STATUS_DRAFT_DELETE
                , ProjectWOBase::STATUS_SUBMMITED_DELETE,
                ProjectWOBase::STATUS_REVIEWED_DELETE,
                ProjectWOBase::STATUS_FEEDBACK_DELETE])
            ->get();
    }

    /**
     * count member draft in project
     * @param int
     * @return int
     */
    public static function countMemberDraft($projectId)
    {
        return self::where('project_id', $projectId)
            ->where('status', '!=', ProjectWOBase::STATUS_APPROVED)
            ->count();
    }

    /**
     * check has team allocation
     * @param int
     * @return int
     */
    public static function checkHasTeamAllocation($projectId)
    {
        return self::where('project_id', $projectId)->count();
    }

    /**
     * check has pm draft
     * @param int
     * @param int
     * @return boolean
     */
    public static function checkPMDraft($projectId, $parameters)
    {
        $arrayStatus = [
            ProjectMember::STATUS_DRAFT,
            ProjectMember::STATUS_FEEDBACK,
            ProjectMember::STATUS_DRAFT_EDIT,
            ProjectMember::STATUS_FEEDBACK_EDIT,
        ];
        if (is_array($projectId)) {
            if (isset($projectId[1]) && $projectId[1]) {
                $idInput = $projectId[1];
            }
            $projectId = $projectId[0];
        }
        if (!is_array($parameters)) {
            $parameters = [$parameters];
        }
        $memberPMDraft = ProjectMember::where('project_id', $projectId)
            ->where('type', $parameters[0])
            ->whereIn('status', $arrayStatus)
            ->where('is_disabled', '!=', self::STATUS_DISABLED);
        if (isset($idInput)) {
            $memberPMDraft = $memberPMDraft->where('id', '!=', $idInput);
        }
        $memberPMDraft = $memberPMDraft->first();
        if ($memberPMDraft) {
            return false;
        }
        return true;
    }

    /**
     * check error time where submit workorder
     * @param int
     * @param array
     * @return boolean
     */
    public static function checkErrorTime($projectId, $projectDraft)
    {
        $arrayStatusDelete = self::getArrayStatusDelete();
        $members = self::where(function ($query) use ($projectDraft) {
            $query->orWhereDate('start_at', '<', $projectDraft->start_at)
                ->orWhereDate('start_at', '>', $projectDraft->end_at)
                ->orWhereDate('end_at', '<', $projectDraft->start_at)
                ->orWhereDate('end_at', '>', $projectDraft->end_at);
        })
            ->where('project_id', $projectId)
            ->where('is_disabled', '!=', self::STATUS_DISABLED)
            ->whereNotIn('status', $arrayStatusDelete)
            ->get();
        $checkError = false;
        foreach ($members as $member) {
            if ($member->projectMemberChild) {
                continue;
            }
            $checkError = true;
            break;
        }
        return $checkError;
    }

    /**
     * rewrite changes object after submit
     *
     * @param int $projectId
     * @param null $type
     * @return array
     */
    public static function getChangesAfterSubmit($projectId, $type = null)
    {
        $result = [];
        $types = self::getTypeMember();
        // add items
        $collection = self::getProjectMemberByStatus(
            [self::STATUS_DRAFT, self::STATUS_FEEDBACK, self::STATUS_SUBMITTED],
            ['project_id' => $projectId]
        );
        if (count($collection)) {
            $result[TaskWoChange::FLAG_TEAM_ALLOCATION][TaskWoChange::FLAG_STATUS_ADD]
                = self::toArrayLabelMember($collection, $types);
        }

        // delete item
        $collection = self::getProjectMemberByStatus(
            [self::STATUS_DRAFT_DELETE, self::STATUS_FEEDBACK_DELETE, self::STATUS_SUBMMITED_DELETE],
            ['project_id' => $projectId]
        );
        if (count($collection)) {
            $collection = ProjectMemberProgramLang::loadProgLangForMembersParent($collection);
            $result[TaskWoChange::FLAG_TEAM_ALLOCATION][TaskWoChange::FLAG_STATUS_DELETE]
                = self::toArrayLabelMember($collection, $types);
        }

        // edit item
        $collection = self::getProjectMemberByStatus(
            [self::STATUS_DRAFT_EDIT, self::STATUS_FEEDBACK_EDIT, self::STATUS_SUBMIITED_EDIT],
            ['project_id' => $projectId]
        );
        if (count($collection)) {
            foreach ($collection as $item) {
                $itemParent = self::getProjectMemberByStatus(
                    [self::STATUS_APPROVED],
                    ['project_id' => $projectId, 'member_id' => $item->parent_id]
                )->first();
                if ($itemParent) {
                    $result[TaskWoChange::FLAG_TEAM_ALLOCATION][TaskWoChange::FLAG_STATUS_EDIT][] = [
                        TaskWoChange::FLAG_STATUS_EDIT_OLD => self::toArrayLabelMemberItem($itemParent, $types),
                        TaskWoChange::FLAG_STATUS_EDIT_NEW => self::toArrayLabelMemberItem($item, $types),
                    ];
                }
            }
        }
        $result[TaskWoChange::FLAG_TEAM_ALLOCATION][TaskWoChange::FLAG_TYPE_TEXT]
            = TaskWoChange::FLAG_TYPE_MULTI;
        return $result;
    }

    /**
     * to array with stage label
     *
     * @param object $collection
     * @return array
     */
    protected static function toArrayLabelMember($collection, array $typesLabel = [])
    {
        if (!count($collection)) {
            return [];
        }
        if (!$typesLabel) {
            $typesLabel = self::getTypeMember();
        }
        $result = [];
        $arrayColumn = [
            'id',
            'name',
            'email',
            'type',
            'prog_langs',
            'start_at',
            'end_at',
            'effort',
            'status',
            'parent_id'
        ];
        $i = 0;
        foreach ($collection as $item) {
            foreach ($arrayColumn as $column) {
                if ($column == 'type') {
                    $result[$i][$column] =
                        self::getType($item->{$column}, $typesLabel);
                } else {
                    $result[$i][$column] = $item->{$column};
                }
            }
            $result[$i]['resource'] = $item->flat_resource;
            $i++;
        }
        return $result;
    }

    /**
     * to array with stage label
     *
     * @param object $collection
     * @return array
     */
    protected static function toArrayLabelMemberItem($item, array $typesLabel = [])
    {
        if (!$item) {
            return [];
        }
        if (!$typesLabel) {
            $typesLabel = self::getTypeMember();
        }
        $result = [];
        $arrayColumn = [
            'id',
            'name',
            'email',
            'type',
            'prog_langs',
            'start_at',
            'end_at',
            'effort',
            'status',
            'parent_id'
        ];
        foreach ($arrayColumn as $column) {
            if ($column == 'type') {
                $result[$column] =
                    self::getType($item->{$column}, $typesLabel);
            } else {
                $result[$column] = $item->{$column};
            }
        }
        $result['resource'] = $item->flat_resource;
        return $result;
    }

    /**
     * get column name to compare changes
     *
     * @return array
     */
    public static function getColumnChanges()
    {
        return [
            'name' => 'Name',
            'email' => 'Email',
            'type' => 'Position',
            'prog_langs' => 'Programs',
            'start_at' => 'Start Date',
            'end_at' => 'End Date',
            'effort' => 'Effort (%)',
            'resource' => 'Resource (MM)',
        ];
    }

    /**
     * flat all item
     *
     * @return bool
     * @throws Exception
     */
    public static function flatAllResource()
    {
        $tableMember = self::getTableName();
        $tableProject = Project::getTableName();

        $collection = self::select($tableMember . '.id', $tableMember . '.start_at',
            $tableMember . '.end_at', $tableMember . '.effort',
            $tableMember . '.flat_resource', $tableProject . '.type_mm')
            ->join($tableProject, $tableProject . '.id', '=', $tableMember . '.project_id')
            ->whereIn($tableProject . '.state', [Project::STATE_NEW, Project::STATE_PROCESSING])
            ->get();
        if (!count($collection)) {
            return true;
        }
        DB::beginTransaction();
        try {
            foreach ($collection as $item) {
                $item->flatResourceItem(true, $item->type_mm);
            }
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }

    }

    /**
     * flat resource a item
     */
    public function flatResourceItem($isSave = true, $typeResource = 1)
    {
        $typeResource = in_array($typeResource, Project::getTypeResourceEffort())
            ? $typeResource : Project::MM_TYPE;
        $this->flat_resource = round(View::getMM(
                $this->start_at, $this->end_at, $typeResource
            ) * $this->effort / 100, 2);
        if ($isSave) {
            $this->save();
        }
    }

    /**
     * get member pm, dev, brse approve in project
     *
     * @param int $projectId
     * @return object
     */
    public static function getPMDEVAprroved($projectId)
    {
        $employeeTable = Employee::getTableName();
        $memberTable = self::getTableName();

        $collection = self::select($memberTable . '.employee_id', $memberTable . '.type',
            $memberTable . '.start_at', $memberTable . '.end_at', $memberTable . '.effort')
            ->join($employeeTable, "{$employeeTable}.id", '=', "{$memberTable}.employee_id")
            ->where($memberTable . '.status', self::STATUS_APPROVED)
            ->where($memberTable . '.project_id', $projectId);
        //->where($memberTable.'.type', '!=', self::TYPE_SUBPM);
        /*->whereIn($memberTable.'.type', [
            self::TYPE_PM,
            self::TYPE_DEV,
            self::TYPE_BRSE,
            self::TYPE_COMTOR,
            self::TYPE_TEAM_LEADER,
        ]);*/
        if (Employee::isUseSoftDelete()) {
            $collection->whereNull("{$employeeTable}.deleted_at");
        }
        return $collection->get();
    }

    /**
     * check update team allocation when status project is close, cancle, postpone
     * @return boolean
     */
    public static function checkTimeTeamAllocation($project)
    {
        $arrayStatusDelete = self::getArrayStatusDelete();
        $allMemeberNotUpdate = self::where('project_id', $project->id)
            ->where('is_disabled', '!=', ProjectWOBase::STATUS_DISABLED)
            ->whereNotIn('status', $arrayStatusDelete)
            ->whereDate('end_at', '>', date('Y-m-d'))
            ->where(function ($query) {
                $query->orWhere(function ($query) {
                    $query->where('status', '!=', self::STATUS_APPROVED);
                })
                    ->orWhere(function ($query) {
                        $query->where('status', self::STATUS_APPROVED)
                            ->whereDoesntHave('projectMemberChild');
                    });
            })
            ->count();
        if ($allMemeberNotUpdate) {
            return true;
        }
        return false;
    }

    /**
     * rewrite save model
     *
     * @param array $config
     * @param array $options
     * @return bool
     * @throws Exception
     */
    public function save(array $config = [], array $options = array())
    {
        DB::beginTransaction();
        try {
            unset($this->prog_langs);
            if (isset($config['type_mm'])) {
                $this->flatResourceItem(false, $config['type_mm']);
            }
            $result = parent::save($options);
            if (isset($config['prog_lang']) && $config['prog_lang']) {
                ProjectMemberProgramLang::insertMemberPrograms(
                    $this, $config['prog_ids'], $config
                );
            }
            DB::commit();
            return $result;
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    /**
     * load program language of member
     *
     * @return \self
     */
    public function loadProgramLangs()
    {
        $this->prog_langs = (array)
        ProjectMemberProgramLang::getProgramLangIdsOfMember($this);
        return $this;
    }

    /**
     * rewrite delete model
     *
     * @throws Exception
     */
    public function delete()
    {
        DB::beginTransaction();
        try {
            ProjectMemberProgramLang::where('proj_member_id', $this->id)
                ->delete();
            $result = parent::delete();
            DB::commit();
            return $result;
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    /**
     * Count programming language in current
     * Group by project and employee
     *
     * @param array $teamIds
     * @param int|null $progLangId
     * @return int
     */
    public static function countProgLang($teamIds, $progLangId = null)
    {
        $projMemTable = self::getTableName();
        $month = date('m');
        $year = date('Y');
        $firstLastMonth = ResourceView::getInstance()->getFirstLastDaysOfMonth($month, $year);
        $start = $firstLastMonth[0];
        $end = $firstLastMonth[1];
        $result = self::join("proj_member_prog_langs", "proj_member_prog_langs.proj_member_id", "=", "project_members.id")
            ->where("project_members.status", ProjectMember::STATUS_APPROVED)
            ->whereRaw("((project_members.start_at between '{$start}' AND '{$end}')
                        OR (project_members.end_at between '{$start}' AND '{$end}')
                        OR (project_members.start_at <= '{$start}' AND project_members.end_at >= '{$end}'))")
            ->groupBy("{$projMemTable}.employee_id");
        if ($progLangId) {
            $result->where('prog_lang_id', $progLangId);
        }
        if ($teamIds) {
            $teamProjTable = TeamProject::getTableName();
            $teamMemberTable = TeamMember::getTableName();
            $result->leftJoin("{$teamProjTable}", "{$teamProjTable}.project_id", "=", "{$projMemTable}.project_id");
            $result->leftJoin("{$teamMemberTable}", "{$teamMemberTable}.employee_id", "=", "{$projMemTable}.employee_id");
            $result->where(function ($query) use ($teamMemberTable, $teamProjTable, $teamIds, $projMemTable) {
                $query->whereIn("{$teamProjTable}.team_id", $teamIds);
                $query->orWhereIn("{$teamMemberTable}.team_id", $teamIds);
            });
        }
        $result = count($result->get());
        return $result;
    }

    /**
     * Get role of project in current month
     *
     * @param array $firstLastDay
     * @param array $teamIds
     * @return type
     */
    public static function roleWO($firstLastDay, $teamIds = null)
    {
        $start = $firstLastDay[0];
        $end = $firstLastDay[1];
        $projMemTable = self::getTableName();
        $EmpTable = Employee::getTableName();
        $result = ProjectMember::where("status", ProjectMember::STATUS_APPROVED)
            ->join("{$EmpTable}", "{$EmpTable}.id", "=", "{$projMemTable}.employee_id")
            ->whereNull("{$EmpTable}.leave_date")
            ->whereRaw("((project_members.start_at between '{$start}' AND '{$end}')
                        OR (project_members.end_at between '{$start}' AND '{$end}')
                        OR (project_members.start_at <= '{$start}' AND project_members.end_at >= '{$end}'))")
            ->groupBy("{$projMemTable}.employee_id", 'type')
            ->select("{$projMemTable}.employee_id", 'type');
        if ($teamIds) {
            $teamProjTable = TeamProject::getTableName();
            $teamMemberTable = TeamMember::getTableName();
            $result->leftJoin("{$teamProjTable}", "{$teamProjTable}.project_id", "=", "{$projMemTable}.project_id");
            $result->leftJoin("{$teamMemberTable}", "{$teamMemberTable}.employee_id", "=", "{$projMemTable}.employee_id");
            $result->where(function ($query) use ($teamMemberTable, $teamProjTable, $teamIds, $projMemTable) {
                $query->whereIn("{$teamProjTable}.team_id", $teamIds);
                $query->orWhereIn("{$teamMemberTable}.team_id", $teamIds);
            });
        }
        return $result->get();
    }

    /**
     * Get records in month
     *
     * @param int $month
     * @param int $year
     * @param array $teamIds
     * @return collection
     */
    public static function effortInMonth($month, $year, $teamIds)
    {
        $projMembers = ProjectMember::getTableName();
        $projTable = Project::getTableName();
        $concat = Dashboard::CONCAT;
        $groupConcat = Dashboard::GROUP_CONCAT;
        $firstLastMonth = ResourceView::getInstance()->getFirstLastDaysOfMonth($month, $year);
        $start = $firstLastMonth[0];
        $end = $firstLastMonth[1];
        $result = self::join("{$projTable}", "{$projMembers}.project_id", "=", "{$projTable}.id")
            ->where("{$projMembers}.status", ProjectMember::STATUS_APPROVED)
            ->whereRaw("((project_members.start_at between '{$start}' AND '{$end}')
                        OR (project_members.end_at between '{$start}' AND '{$end}')
                        OR (project_members.start_at <= '{$start}' AND project_members.end_at >= '{$end}'))")
            ->select(
                "{$projMembers}.project_id",
                "{$projMembers}.employee_id",
                "{$projMembers}.type as role_type",
                "{$projMembers}.start_at",
                "{$projMembers}.end_at",
                "{$projMembers}.effort",
                "{$projTable}.type"
            );
        if ($teamIds) {
            $teamProjTable = TeamProject::getTableName();
            $teamMemberTable = TeamMember::getTableName();
            $result->leftJoin("{$teamProjTable}", "{$teamProjTable}.project_id", "=", "{$projMembers}.project_id");
            $result->leftJoin("{$teamMemberTable}", "{$teamMemberTable}.employee_id", "=", "{$projMembers}.employee_id");
            $result->where(function ($query) use ($teamMemberTable, $teamProjTable, $teamIds, $projMembers) {
                $query->whereIn("{$teamProjTable}.team_id", $teamIds);
                $query->orWhereIn("{$teamMemberTable}.team_id", $teamIds);
            });
        }
        return $result->get();
    }

    /**
     * Get count project by project type in month
     *
     * @param int $month
     * @param int $year
     * @param array $teamIds
     * @return collection
     */
    public static function countProjByProjType($month, $year, $teamIds)
    {
        $projMembers = ProjectMember::getTableName();
        $projTable = Project::getTableName();
        $firstLastMonth = ResourceView::getInstance()->getFirstLastDaysOfMonth($month, $year);
        $start = $firstLastMonth[0];
        $end = $firstLastMonth[1];
        $result = self::join("{$projTable}", "{$projMembers}.project_id", "=", "{$projTable}.id")
            ->where("{$projMembers}.status", ProjectMember::STATUS_APPROVED)
            ->whereRaw("((project_members.start_at between '{$start}' AND '{$end}')
                        OR (project_members.end_at between '{$start}' AND '{$end}')
                        OR (project_members.start_at <= '{$start}' AND project_members.end_at >= '{$end}'))")
            ->groupBy("{$projTable}.type")
            ->selectRaw(
                "count(distinct {$projMembers}.project_id) count_project, projs.type"
            );
        if ($teamIds) {
            $teamProjTable = TeamProject::getTableName();
            $teamMemberTable = TeamMember::getTableName();
            $result->leftJoin("{$teamProjTable}", "{$teamProjTable}.project_id", "=", "{$projMembers}.project_id");
            $result->leftJoin("{$teamMemberTable}", "{$teamMemberTable}.employee_id", "=", "{$projMembers}.employee_id");
            $result->where(function ($query) use ($teamMemberTable, $teamProjTable, $teamIds, $projMembers) {
                $query->whereIn("{$teamProjTable}.team_id", $teamIds);
                $query->orWhereIn("{$teamMemberTable}.team_id", $teamIds);
            });
        }
        return $result->get();
    }

    /**
     * update flat resource base type resource
     * @param type $project
     * @return type
     * @throws Exception
     */
    public static function updateFlatResource($project)
    {
        if (!is_object($project)) {
            $project = Project::find($project);
        }
        $projectId = $project->parent_id ? $project->parent_id : $project->id;

        $collection = self::select('id', 'start_at', 'end_at', 'effort', 'flat_resource', 'parent_id')
            ->where('project_id', $projectId)
            ->get();

        DB::beginTransaction();
        $result = [];
        try {
            foreach ($collection as $item) {
                $item->flatResourceItem(true, $project->type_mm);
                array_push($result, [
                    'id' => $item->id,
                    'flat_resource' => $item->flat_resource,
                    'parent_id' => $item->parent_id
                ]);
            }
            DB::commit();
            return $result;
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    /**
     * search all member on project by email
     * @param int $projectId
     * @return array contain id and email of employee
     */
    public static function searchAjax($email, $projectId)
    {
        $result = [];
        $projectMemberTable = ProjectMember::getTableName();
        $employeetable = Employee::getTableName();
        $teamMemberTable = TeamMember::getTableName();
        $teamProjectTable = TeamProject::getTableName();
        $projectDraft = Project::where('parent_id', $projectId)->first();
        $status = [
            ProjectWOBase::STATUS_DRAFT_DELETE,
            ProjectWOBase::STATUS_SUBMMITED_DELETE,
            ProjectWOBase::STATUS_FEEDBACK_DELETE,
            ProjectWOBase::STATUS_REVIEWED_DELETE
        ];
        $roleMembers = [
            Team::ROLE_TEAM_LEADER,
            Team::ROLE_SUB_LEADER,
        ];

        $members = DB::table($employeetable)->select('id', 'email')
            ->whereIn('id', function ($query) use (
                $employeetable, $projectMemberTable, $projectId, $status,
                $teamProjectTable, $teamMemberTable, $roleMembers, $projectDraft
            ) {
                $query->select('id')
                    ->from($employeetable)
                    ->whereIn('id', function ($query) use (
                        $projectMemberTable, $projectId,
                        $status, $teamProjectTable
                    ) {
                        /**
                         * get all member who isn't deleted in the Project
                         */
                        $query->select('employee_id')
                            ->from($projectMemberTable)
                            ->where('project_id', $projectId)
                            ->whereNotIn('id', function ($query) use (
                                $projectMemberTable, $projectId,
                                $status, $teamProjectTable
                            ) {
                                $query->select('parent_id')
                                    ->from($projectMemberTable)
                                    ->where('project_id', $projectId)
                                    ->whereIn('status', $status)
                                    ->distinct()->get();
                            })
                            ->whereNotIn('status', $status)->distinct()->get();
                    })
                    ->orWhereIn('id', function ($query) use (
                        $teamMemberTable, $teamProjectTable,
                        $projectId, $roleMembers, $projectDraft
                    ) {
                        /**
                         * get sub leader and leader in Team that joined the project.
                         */
                        $query->select('employee_id')
                            ->from($teamMemberTable)
                            ->whereIn('team_id', function ($query) use ($teamProjectTable, $projectId, $projectDraft) {
                                if ($projectDraft) {
                                    $query->select('team_id')
                                        ->from($teamProjectTable)
                                        ->where('project_id', $projectDraft->id)
                                        ->get();
                                } else {
                                    $query->select('team_id')
                                        ->from($teamProjectTable)
                                        ->where('project_id', $projectId)
                                        ->get();
                                }
                            })
                            ->whereIn('role_id', $roleMembers)
                            ->get();
                    })->distinct()->get();
            })
            ->where('email', 'LIKE', '%' . $email . '%')
            ->orderBy('email')->distinct()->get();

        foreach ($members as $item) {
            $result['items'][] = [
                'id' => $item->id,
                'text' => substr($item->email, 0, strpos($item->email, '@')),
            ];
        }
        return $result;
    }

    /*
     * get pm of parent_id
     * @param int $childId
     * return attribute parent_id
     */
    public static function getPmOfParentIdById($childId)
    {
        $tableName = self::getTableName();
        $pmId = self::where('id', function ($query) use ($tableName, $childId) {
            $query->select('parent_id')
                ->from($tableName)
                ->where('id', $childId);
        })->where('type', self::TYPE_PM)
            ->whereIn('status', array(ProjectWOBase::STATUS_APPROVED, ProjectWOBase::STATUS_DRAFT))
            ->first();
        if ($pmId) {
            return $pmId->employee_id;
        }
        return null;
    }

    /* total effort of dev
     * @param int $projectId
     * @return int $total
     */
    public static function getTotalFlatResourceOfDev($projectId)
    {
        $devMembers = self::where('project_id', $projectId)
            ->where('status', ProjectWOBase::STATUS_APPROVED)
            ->where('type', self::TYPE_DEV)
            ->get();
        $total = 0;
        foreach ($devMembers as $devMember) {
            $total += $devMember->flat_resource;
        }
        return $total;

    }

    /**
     * get all PM Id of project
     * @param int $projectId
     * @return array $result list pm_id of project
     */
    public static function getAllPmIdOfProject($projectId)
    {
        $result = [];
        $pmIds = self::getAllPmOfProject($projectId);
        if ($pmIds) {
            foreach ($pmIds as $pmId) {
                array_push($result, $pmId['employee_id']);
            }
        }
        return $result;
    }

    /**
     * update time end_at for members of project
     *
     * @param $project
     * @param $projectDraf
     * @throws Exception
     */
    public static function updateTime($project, $projectDraf)
    {
        $projStart = $project->start_at;
        $projEnd = $project->end_at;
        if ($projectDraf) {
            $projStart = $projectDraf->start_at;
            $projEnd = $projectDraf->end_at;
        }
        $tableProjectMember = self::getTableName();
        // find all member approve not child or child
        $members = self::whereNotIn('id', function ($query) use ($tableProjectMember, $project) {
            $query->select('parent_id')->from($tableProjectMember)
                ->where('project_id', $project->id)
                ->whereNotNull('parent_id');
        })
            ->where('project_id', $project->id)
            ->get();
        $typeResource = $project->type_mm;
        foreach ($members as $member) {
            $change = false;
            if ($member->parent_id ||
                in_array($member->status, [
                    self::STATUS_DRAFT,
                    self::STATUS_DRAFT_EDIT,
                    self::STATUS_SUBMITTED,
                    self::STATUS_SUBMIITED_EDIT,
                    self::STATUS_FEEDBACK,
                    self::STATUS_FEEDBACK_EDIT
                ])
            ) {
                if ($member->start_at < $projStart) {
                    $member->start_at = $projStart;
                    $change = true;
                } elseif ($member->start_at > $projEnd) {
                    $member->start_at = $projEnd;
                    $change = true;
                }
                if ($member->end_at > $projEnd) {
                    $member->end_at = $projEnd;
                    $change = true;
                } elseif ($member->end_at < $projStart) {
                    $member->end_at = $projStart;
                    $change = true;
                }
                if ($change) {
                    $member->flatResourceItem(false, $typeResource);
                    $member->save(['type_mm' => $typeResource]);
                }
            } else {
                if ($member->status == self::STATUS_APPROVED) {
                    $memberDraft = new self();
                    $memberDraft = $member->replicate();
                    if ($memberDraft->start_at < $projStart) {
                        $memberDraft->start_at = $projStart;
                        $change = true;
                    } elseif ($memberDraft->start_at > $projEnd) {
                        $memberDraft->start_at = $projEnd;
                        $change = true;
                    }
                    if ($memberDraft->end_at > $projEnd) {
                        $memberDraft->end_at = $projEnd;
                        $change = true;
                    } elseif ($memberDraft->end_at < $projStart) {
                        $memberDraft->end_at = $projStart;
                        $change = true;
                    }
                    if ($change) {
                        $memberDraft->status = self::STATUS_DRAFT_EDIT;
                        $memberDraft->parent_id = $member->id;
                        $memberDraft->save(['type_mm' => $typeResource]);
                        $programLangIds = ProjectMemberProgramLang::getProgramLangIdsOfMember($member);
                        ProjectMemberProgramLang::insertMemberPrograms($memberDraft, $programLangIds);
                    }
                }

            }
        }
    }

    /**
     * get SubPM of project
     *
     * @param int $projectId
     * @return object
     */
    public static function getSubPmOfProject($projectId)
    {
        $employeeTable = Employee::getTableName();
        $memberTable = self::getTableName();
        $today = date("Y-m-d");
        $collection = self::select("{$employeeTable}.id as id", $employeeTable . '.name',
            $employeeTable . '.email', $memberTable . '.type')
            ->join($employeeTable, "{$employeeTable}.id", '=', "{$memberTable}.employee_id")
            ->where($memberTable . '.status', self::STATUS_APPROVED)
            ->where($memberTable . '.project_id', $projectId)
            ->where($memberTable . '.type', self::TYPE_SUBPM)
            ->where($memberTable . '.end_at', '>', $today)
            ->where($memberTable . '.start_at', '<=', $today);
        if (Employee::isUseSoftDelete()) {
            $collection->whereNull("{$employeeTable}.deleted_at");
        }
        $collection = $collection->get();
        return $collection;
    }

    public function getMembersNotInAnyProject($start, $end)
    {
        $projMemTbl = self::getTableName();
        $empTbl = Employee::getTableName();
        $teamMemTbl = TeamMember::getTableName();
        $teamTbl = Team::getTableName();
        return Employee::join("{$teamMemTbl}", "{$empTbl}.id", "=", "{$teamMemTbl}.employee_id")
            ->join("{$teamTbl}", "{$teamMemTbl}.team_id", "=", "{$teamTbl}.id")
            ->whereNotIn("{$empTbl}.id", function ($query) use ($projMemTbl, $start, $end) {
                $query->select('employee_id')
                    ->from($projMemTbl)
                    ->where("status", self::STATUS_APPROVED)
                    ->whereRaw("((start_at between '{$start}' AND '{$end}')
                            OR (end_at between '{$start}' AND '{$end}')
                            OR (start_at <= '{$start}' AND end_at >= '{$end}'))")
                    ->get();
            })
            ->where("{$teamTbl}.is_soft_dev", Team::IS_SOFT_DEVELOPMENT)
            ->where("{$empTbl}.working_type", "<>", \Rikkei\Resource\View\getOptions::WORKING_INTERNSHIP)
            ->whereRaw("DATE({$empTbl}.join_date) <= CURDATE()")
            ->where(function ($query) use ($empTbl) {
                $query->whereRaw("DATE({$empTbl}.leave_date) >= CURDATE()")
                    ->orWhereNull($empTbl . '.leave_date');
            })
            ->groupBy("{$empTbl}.id", "$teamTbl.id")
            ->select(
                "{$empTbl}.id",
                "{$empTbl}.email",
                "{$empTbl}.name",
                "{$teamTbl}.name as team_name",
                "{$teamTbl}.id as team_id"
            )
            ->get();
    }

    /**
     * Get List manager of project
     *
     * @param $projectId
     * @param array|null $type
     * @return array (employee_id of leader, PM, Sub PM)
     */
    public static function getManagerOfProject($projectId, array $type = null)
    {
        if (empty($type)) {
            $type = [
                self::TYPE_PM,
                self::TYPE_SUBPM,
            ];
        }

        $project = Project::select("leader_id")
            ->where("id", $projectId)
            ->first();

        $member = ProjectMember::query()->select('employee_id')
            ->where('project_id', $projectId)
            ->whereIn('type', $type)
            ->where('status', ProjectWOBase::STATUS_APPROVED)
            ->get();

        $manager[] = $project->leader_id;

        if (!empty($member)) {
            $member = $member->pluck('employee_id')->toArray();
            $manager = array_merge($manager, $member);
        }

        return $manager;
    }

    public static function getMemberOfManyProject($projectId, $approver = false)
    {
        $collection = DB::table('project_members')
            ->select(
                'project_members.id',
                'project_members.effort',
                'project_members.flat_resource',
                'projs.type_mm',
                'project_members.type',
                'emp_member.name as member_name',
                'project_members.employee_id',
                'project_members.start_at',
                'project_id',
                'projs.name as project_name',
                'emp_leader.name as leader_name',
                DB::raw('MAX(project_members.end_at) AS end_at'),
                DB::raw('GROUP_CONCAT(DISTINCT(CONCAT(team.name)) SEPARATOR "; ") as team_names')
            )
            ->leftjoin('employees as emp_member', 'project_members.employee_id', '=', 'emp_member.id')
            ->join('projs', 'projs.id', '=', 'project_members.project_id')
            ->leftjoin('employees as emp_leader', 'emp_leader.id', '=', 'projs.leader_id')
            ->join(TeamMember::getTableName() . ' as tmb', 'project_members.employee_id', '=', 'tmb.employee_id')
            ->leftJoin(Team::getTableName() . ' as team', 'tmb.team_id', '=', 'team.id')
            ->whereIn('project_members.project_id', $projectId)
            ->whereNull('project_members.deleted_at');
        if ($approver) {
            $collection = $collection->where('project_members.status', self::STATUS_APPROVED);
        } else {
            $collection = $collection->where('project_members.end_at', '>=', Carbon::now());
        }
        $collection = $collection->groupBy(['project_members.id', 'project_members.project_id'])->get();
        $response = array();
        $count = array();
        $total = 0;
        foreach ($collection as $key => $item) {
            if (!isset($response[$item->project_id])) {
                $response[$item->project_id] = [
                    'project_name' => $item->project_name,
                    'leader_name' => $item->leader_name,
                    'project_id' => $item->project_id,
                    'type_mm' => $item->type_mm,
                    'type' => static::getTypeMember()[$item->type],
                    'member' => [],
                ];
                $count[$item->project_id] = 0;
            }
            if ($response[$item->project_id]['project_id'] == $item->project_id) {
                if (isset($response[$item->project_id]['member'][$item->employee_id])) {
                    $response[$item->project_id]['member'][$item->employee_id]['allocate'][$item->id] = [
                        'start_at' => $item->start_at,
                        'end_at' => $item->end_at,
                        'effort' => $item->effort,
                        'type' => static::getTypeMember()[$item->type],
                        'flat_resource' => $item->type_mm == Project::MM_TYPE ? $item->flat_resource : round($item->flat_resource / 20, 3),
                    ];
                    $count[$item->project_id]++;
                    $total += $response[$item->project_id]['member'][$item->employee_id]['allocate'][$item->id]['flat_resource'];
                } else {
                    $response[$item->project_id]['member'][$item->employee_id] = [
                        'member_name' => $item->member_name,
                        'end_at' => $item->end_at,
                        'team_names' => $item->team_names,
                        'start_at' => $item->start_at,
                        'effort' => $item->effort,
                        'type' => static::getTypeMember()[$item->type],
                        'flat_resource' => $item->type_mm == Project::MM_TYPE ? $item->flat_resource : round($item->flat_resource / 20, 3),
                        'allocate' => array(),
                    ];
                    $count[$item->project_id]++;
                    $total += $response[$item->project_id]['member'][$item->employee_id]['flat_resource'];
                }
            }
        }
        $response = array_values(array_filter($response));
        foreach ($response as $key => $totalAllocate) {
            $response[$key]['count'] = 1;
            if (isset($count[$totalAllocate['project_id']])) {
                $response[$key]['count'] = $count[$totalAllocate['project_id']];
            }
        }
        if ($approver) {
            $response[0]['total'] = $total;
        }
        return $response;
    }

    /*
     * list project by employee
     */
    public static function getProjsOfEmployee($empId, $select = ['*'])
    {
        return self::select($select)
            ->from(self::getTableName() . ' as pjm')
            ->join(Project::getTableName() . ' as proj', function ($join) {
                $join->on('proj.id', '=', 'pjm.project_id')
                    ->whereNull('proj.deleted_at');
            })
            ->where('pjm.employee_id', $empId)
            ->where('pjm.status', self::STATUS_APPROVED)
            ->groupBy('proj.id')
            ->get();
    }

    /**
     * get member of Project
     *
     * @param int $projectId
     * @return Collection
     */
    public static function getProjectMemberById($projectId)
    {
        return DB::table('project_members')
            ->join('projs', 'projs.id', '=', 'project_members.project_id')
            ->join('employees', 'employees.id', '=', 'project_members.employee_id')
            ->join('users', 'users.employee_id', '=', 'project_members.employee_id')
            ->where('project_members.project_id', $projectId)
            ->whereNull('project_members.deleted_at')
            ->select('employees.id', 'employees.name', 'users.avatar_url', 'employees.employee_code as info', 'employees.email as href')
            ->groupBy('employees.id')
            ->get();
    }

    /**
     * check is PM of project
     *
     * @param int $projectId , $empId
     * @return object
     */
    public static function checkIsPmOfProject($projectId, $empId)
    {
        return self::join('projs', function ($join) {
            $join->on("project_members.employee_id", "=", "projs.manager_id");
            $join->on("project_members.project_id", "=", "projs.id");
        })
            ->where('project_members.type', self::TYPE_PM)
            ->where('projs.manager_id', $empId)
            ->where('project_members.project_id', $projectId)
            ->where('project_members.status', ProjectWOBase::STATUS_APPROVED)
            ->where('project_members.is_disabled', '!=', ProjectWOBase::STATUS_DISABLED)
            ->first();
    }

    /**
     * @param int $cloneId
     * @param object $project
     * @return bool
     */
    public static function cloneProjectMember($cloneId, $project)
    {
        $cloneMember = self::where('project_members.status', self::STATUS_APPROVED)
            ->where('project_id', $cloneId)
            ->whereNull('deleted_at')
            ->where('employee_id', '!=', $project->manager_id)
            ->get();
        if ($cloneMember && count($cloneMember)) {
            $cloneMember->map(function ($item) use ($project) {
                unset($item->id);
                unset($item->created_at);
                unset($item->updated_at);
                $item->project_id = $project->id;
                return $item;
            });
            $cloneMember = $cloneMember->toArray();
            return self::insert($cloneMember);
        }
        return null;
    }
    
    public function getEmployeeProject($projId, $startDate, $endDate)
    {
        return static::select(
            'project_members.project_id',
            'project_members.employee_id',
            'project_members.start_at',
            'project_members.end_at',
            'project_members.project_id',
            'employees.name as employee_name',
            'employees.employee_code as employee_code',
            'employees.email as employee_email'
        )
        ->join('projs', "project_members.project_id", "=", "projs.id")
        ->join('employees', "employees.id", "=", "project_members.employee_id")
        ->where("project_members.project_id", $projId)
        ->whereDate('project_members.start_at', '<=', $endDate)
        ->whereDate('project_members.end_at', '>=', $startDate)
        ->where("project_members.status", static::STATUS_APPROVED)
        ->whereNull("project_members.parent_id")
        ->whereNull('projs.deleted_at')
        ->whereNull('project_members.deleted_at')
        ->get();
    }

    /**
     * get member is active of project 
     *
     * @param int $projectId
     * @return object
     */
    public static function getMemberActiveOfProject($projectId)
    {
        $employeeTable = Employee::getTableName();
        $memberTable = self::getTableName();
        $collection = self::select("{$memberTable}.id as id", 'name', 'email', 'employee_id',
            'type', 'start_at', 'end_at', 'effort', 'status', "{$memberTable}.created_at as created_at", "{$memberTable}.is_disabled as is_disabled")
            ->join($employeeTable, "{$employeeTable}.id", '=', "{$memberTable}.employee_id")
            ->where('project_id', $projectId)
            ->where('status', 1)
            ->orderBy('created_at')
            ->get();
        return $collection;
    }

}
