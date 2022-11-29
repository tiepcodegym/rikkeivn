<?php

namespace Rikkei\Tag\Model;

use Rikkei\Sales\Model\Customer;
use Rikkei\Project\Model\SaleProject;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Project\Model\TeamProject;
use Rikkei\Project\Model\ProjectProgramLang;
use Rikkei\Project\Model\ProjQuality;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\Model\ProjectMemberProgramLang;
use Rikkei\Team\View\Permission;
use Exception;
use Rikkei\Project\Model\Project;
use Rikkei\Team\Model\Team;
use Rikkei\Tag\View\TagConst;
use Rikkei\Team\View\Config;
use Rikkei\Project\Model\ProjectMeta;
use Rikkei\Project\Model\SourceServer;
use Rikkei\Project\Model\ProjectWOBase;
use Rikkei\Project\Model\ProjectPoint;
use Rikkei\Project\Model\ProjPointFlat;
use Rikkei\Core\View\CacheHelper;

class ProjectTag extends Project
{

    /**
     * get customer of project
     * 
     * @param model $project
     * @return array
     */
    public static function getCustomerName($project)
    {
        if (!$project->cust_contact_id) {
            return null;
        }
        $customer = Customer::select('name', 'name_ja')
            ->where('id', $project->cust_contact_id)
            ->first();
        if (!$customer) {
            return null;
        }
        $name = $customer->name;
        if ($customer->name_ja) {
            $name .= ' (' . $customer->name_ja . ')';
        }
        return $name;
    }
    
    /**
     * get leader pm name of project
     * 
     * @param model $project
     * @return array
     */
    public static function getLeaderPMName($project)
    {
        $collection = Employee::select('id', 'name', 'email')
            ->whereIn('id', [
                $project->leader_id,
                $project->manager_id
            ])
            ->get();
        if (!count($collection)) {
            return null;
        }
        return $collection;
    }
    
    /**
     * get sales of project
     * 
     * @param type $project
     * @return type
     */
    public static function getSales($project)
    {
        $tableSale = SaleProject::getTableName();
        $tableEmployee = Employee::getTableName();
        
        $collection = Employee::select($tableEmployee.'.id', 
            $tableEmployee.'.email')
            ->join($tableSale, $tableSale.'.employee_id', '=',
                $tableEmployee.'.id')
            ->where($tableSale.'.project_id', '=', $project->id)
            ->orderBy($tableEmployee.'.email', 'asc')
            ->get();
        if (!count($collection)) {
            return null;
        }
        $result = [];
        foreach ($collection as $item) {
            $result[$item->id] = [
                'id' => $item->id,
                'label' => CoreView::getNickName($item->email)
            ];
        }
        return $result;
    }
    
    /**
     * get team ids of project
     * 
     * @param type $project
     * @return array
     */
    public static function getTeamIdsOfProj($project)
    {
        $collection = TeamProject::select('team_id')
            ->where('project_id', $project->id)
            ->get();
        if (!count($collection)) {
            return null;
        }
        $result = [];
        foreach ($collection as $item) {
            $result[] = $item->team_id;
        }
        return $result;
    }
    
    /**
     * get lang ids of project
     * 
     * @param model $project
     * @return array
     */
    public static function getLangIds($project)
    {
        $collection = ProjectProgramLang::select('prog_lang_id')
            ->where('project_id', $project->id)
            ->get();
        if (!count($collection)) {
            return null;
        }
        $result = [];
        foreach ($collection as $item) {
            $result[] = $item->prog_lang_id;
        }
        return $result;
    }
    
    /**
     * get billable and plan effort
     * 
     * @param model $project
     * @return array
     */
    public static function getQuaEffort($project)
    {
        $item = ProjQuality::select('billable_effort', 'plan_effort')
            ->where('project_id', $project->id)
            ->first();
        return [
            'billable_effort' => $item ? $item->billable_effort : null,
            'plan_effort' => $item ? $item->plan_effort : null
        ];
    }
    
    /**
     * save base information project
     * 
     * @param model $project
     * @param array $data
     * @return model
     */
    public static function saveBase($project, $data, $option = [])
    {
        $project->setData($data);
        DB::beginTransaction();
        try {
            if (isset($option['type_resource']) && $option['type_resource']) {
                ProjectMember::updateFlatResource($project);
            }
            $result = $project->save();
            DB::commit();
            return $result;
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
    
    /**
     * save team of project
     * 
     * @param model $project
     * @param array $data
     * @param int $leaderId
     * @return model
     */
    public static function saveTeam($project, $data, $leaderId)
    {
        DB::table(TeamProject::getTableName())
            ->where('project_id', $project->id)
            ->delete();
        if (!$data || !count($data)) {
            return true;
        }
        $dataInsert = [];
        $now = Carbon::now()->format('Y-m-d H:i:s');
        foreach ($data as $item) {
            $dataInsert[] = [
                'team_id' => $item,
                'project_id' => $project->id,
                'created_at' => $now,
                'updated_at' => $now
            ];
        }
        DB::beginTransaction();
        try {
            if ($leaderId) {
                $project->leader_id = $leaderId;
                $project->save();
            }
            TeamProject::insert($dataInsert);
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
    
    /**
     * save sale of project
     * 
     * @param model $project
     * @param array $data
     * @return model
     */
    public static function saveSale($project, $data)
    {
        DB::table(SaleProject::getTableName())
            ->where('project_id', $project->id)
            ->delete();
        if (!$data || !count($data)) {
            return true;
        }
        $dataInsert = [];
        $now = Carbon::now()->format('Y-m-d H:i:s');
        foreach ($data as $item) {
            $dataInsert[] = [
                'employee_id' => $item,
                'project_id' => $project->id,
                'created_at' => $now,
                'updated_at' => $now
            ];
        }
        DB::beginTransaction();
        try {
            SaleProject::insert($dataInsert);
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
    
    /**
     * save quality project
     * 
     * @param model $project
     * @param array $data
     * @return model
     */
    public static function saveQuality($project, $data)
    {
        $quality = ProjQuality::where('project_id', $project->id)
            ->first();
        $quality->setData($data);
        return $quality->save();
    }
    
    /**
     * insert lang
     * 
     * @param type $project
     * @param type $data
     */
    public static function saveLang($project, $data) 
    {
        return ProjectProgramLang::insertItems($project, (array) $data);
    }
    
    public static function getMembersOfProj($project)
    {
        $employeeTable = Employee::getTableName();
        $memberTable = ProjectMember::getTableName();
        $projMemberProgLangTable = ProjectMemberProgramLang::getTableName();
        
        $collection = ProjectMember::select($memberTable.'.id', $employeeTable.'.name',
                $employeeTable.'.email', $memberTable.'.employee_id',
                $memberTable.'.type', $memberTable.'.start_at', $memberTable.'.end_at',
                $memberTable.'.effort', $memberTable.'.created_at', 
                $memberTable.'.flat_resource')
            ->addSelect(DB::raw('GROUP_CONCAT(`'.
                $projMemberProgLangTable.'`.`prog_lang_id` SEPARATOR \',\') '
                . 'as prog_lang'))
            ->join($employeeTable, $employeeTable.'.id', '=',
                $memberTable.'.employee_id')
            ->leftJoin($projMemberProgLangTable, 
                $projMemberProgLangTable.'.proj_member_id',
                 '=', $memberTable.'.id')
            ->where($memberTable.'.project_id', $project->id)
            ->groupBy($memberTable.'.id');
        if (Employee::isUseSoftDelete()) {
            $collection->whereNull($employeeTable.'.deleted_at');
        }
        $collection = $collection->get();
        if (!count($collection)) {
            return null;
        }
        return $collection;
    }
    
    /**
     * save member of project
     * 
     * @param type $project
     * @param array $input
     * @return boolean
     * @throws \Rikkei\Tag\Model\Exception
     */
    public static function saveMember($project, array $input) 
    {
        DB::beginTransaction();
        try {
            $typeResource = $project->type_mm;
            if (isset($input['id']) && $input['id']) {
                $member = ProjectMember::find($input['id']);
            } else {
                $member = new ProjectMember();
                $member->project_id = $input['project_id'];
                $member->created_by = Permission::getInstance()->getEmployee()->id;
            }
            $member->status = ProjectMember::STATUS_APPROVED;
            $member->fill($input);
            $member->flatResourceItem(false, $typeResource);
            $member->save(['prog_lang' => true, 'project' => $project]);
            DB::commit();
            return $member;
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
    
    /**
     * delete member
     * 
     * @param model $project
     * @param int $id
     * @return boolean
     * @throws \Rikkei\Tag\Model\Exception
     */
    public static function deleteMember($project, $id) 
    {
        DB::beginTransaction();
        try {
            $member = ProjectMember::find($id);
            if (!$member || $member->project_id != $project->id) {
                return false;
            }
            $member->delete();
            DB::commit();
            return true;
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
    
    /**
     * get tags belongs to project
     * @param type $fieldIds
     * @return type
     */
    public function tags ($fieldIds = []) {
        if (!$fieldIds) {
            $fieldIds = [TagConst::SET_TAG_PROJECT];
        }
        return $this->belongsToMany('\Rikkei\Tag\Model\Tag', 
                'kl_tag_values', 
                'entity_id', 
                'tag_id')
            ->wherePivotIn('field_id', $fieldIds)
            ->groupBy('tag_id');
    }
    
    /**
     * list project to set tags
     * @param type $data
     * @return type
     */
    public static function getDataList ($data, $scopeRoute = 'tag::object.project.data.list') {
        $scope = Permission::getInstance();
        $dataPager = $data;
        $search = null;
        if (isset($dataPager['search'])) {
            $search = json_decode($data['search']);
            unset($dataPager['search']);
        }
        $pager = Config::getPagerData(null, $dataPager);
        $tblTeam = Team::getTableName();
        $tblTeamProj = TeamProject::getTableName();
        $tblProj = Project::getTableName();
        $tblEmp = Employee::getTableName();
        $tblTag = Tag::getTableName();
        $tblTagValue = TagValue::getTableName();
        $tableProjMeta = ProjectMeta::getTableName();
        $tblProjTag = ViewProjTag::getTableName();
        
        $collection = Project::join($tblTeamProj.' as tpj', $tblProj.'.id', '=', 
                'tpj.project_id')
            ->join($tblTeam.' as team', 'tpj.team_id', '=', 'team.id')
            //->leftJoin($tblEmp.' as emp', $tblProj.'.leader_id', '=', 'emp.id')
            ->leftJoin($tblEmp.' as empas', $tblProj.'.tag_assignee', '=', 
                'empas.id')
            ->join($tableProjMeta . ' AS t_proj_meta', 't_proj_meta.project_id',
                '=', $tblProj.'.id')
            ->leftJoin($tblProjTag . ' AS t_proj_tag', 't_proj_tag.project_id', 
                    '=', $tblProj.'.id')
            ->whereIn($tblProj.'.status', [
                Project::STATUS_APPROVED, 
                Project::STATUS_OLD
            ]);
        if ($search) {
            foreach ($search as $key => $value) {
                if (is_array($value)) {
                    if (count($value) > 0) {
                        $collection->whereIn($key, $value);
                    }
                } else {
                    if ($key === 'tg_s.value') {
                        $collection->leftJoin($tblTagValue.' as tvl_s', 
                            $tblProj.'.id', '=', 'tvl_s.entity_id')
                            ->leftJoin($tblTag.' as tg_s', function ($join) {
                                $join->on('tvl_s.tag_id', '=', 'tg_s.id')
                                        ->whereNull('tg_s.deleted_at');
                            });
                    }
                    $collection->where($key, 'like', '%' . $value . '%');
                }
            }
        }
        if (isset($data['is_review']) && $data['is_review']) {
            $collection->whereIn($tblProj.'.tag_status', [
                TagConst::TAG_STATUS_REVIEW, 
                TagConst::TAG_STATUS_APPROVE
            ]);
        }
        if (isset($data['project_ids']) && 
            $data['project_ids'] && 
            is_array($data['project_ids'])
        ) {
            $collection->whereIn($tblProj.'.id', $data['project_ids']);
        }
        
        $currentUser = $scope->getEmployee();
        //permission
        if ($scope->isScopeCompany(null, $scopeRoute)) {
            //view all
        } else if ($scope->isScopeTeam(null, $scopeRoute)) {
            $teamIds = $scope->getTeams();
            $collection->where(function ($query) use ($teamIds, $currentUser, $tblProj) {
                $query->whereIn('tpj.team_id', $teamIds)
                    ->orWhere($tblProj.'.tag_assignee', $currentUser->id)
                    ->orWhere($tblProj.'.leader_id', $currentUser->id)
                    ->orWhere($tblProj.'.manager_id', $currentUser->id);
                });
        } else {
            $collection->where(function ($query) use ($tblProj, $currentUser) {
                $query->orWhere($tblProj.'.leader_id', $currentUser->id)
                    ->orWhere($tblProj.'.tag_assignee', $currentUser->id)
                    ->orWhere($tblProj.'.manager_id', $currentUser->id);
            });
        }
        
        $collection->groupBy($tblProj.'.id')
            ->select($tblProj.'.id', $tblProj.'.name', $tblProj.'.created_at', 
                $tblProj.'.tag_status',
                DB::raw('GROUP_CONCAT(DISTINCT(CONCAT(team.id, ":" ,team.name)) '
                    . 'SEPARATOR "|") as team_idnames'),
                'empas.email AS assignee_name', 
                'empas.id AS assignee_id',
                $tblProj.'.status as proj_status',
                $tblProj.'.leader_id',
                't_proj_meta.scope_desc', 
                't_proj_meta.scope_scope',
                $tblProj.'.id as project_id',
                $tblProj.'.start_at as start_date',
                $tblProj.'.end_at as end_date',
                DB::raw('SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT(t_proj_tag.tag_id) '
                    . 'SEPARATOR "-"), "-", '. (TagConst::NUM_SHOW_TAGS + 1)  
                    .') as tag_ids'),
                DB::raw('COUNT(DISTINCT(t_proj_tag.tag_id)) as count_tags')
            );
        
        if (!isset($dataPager['order'])) {
            $collection->orderBy($tblProj.'.created_at', 'desc');
        } else {
            if ($pager['order'] === 'team_names') {
                $collection->addSelect(DB::raw('GROUP_CONCAT(DISTINCT(team.name) '
                    . 'SEPARATOR ", ") as team_names'));
            }
            $collection->orderBy($pager['order'], $pager['dir']);
        }
        return $collection->paginate($pager['limit']);
    }
    
    /**
     * get all tags by project id
     * @param type $projectId
     * @return collection
     */
    public static function getTagsFromId ($projectId, $fieldIds = [], $checkStatus = false) {
        $tblTagValue = TagValue::getTableName();
        $tblTag = Tag::getTableName();
        if (!$fieldIds) {
            $fieldIds = Field::getChildIds(TagConst::SET_TAG_PROJECT);
        }
        $result = Tag::join($tblTagValue.' as tvl', $tblTag.'.id', '=', 'tvl.tag_id')
                ->where('tvl.entity_id', $projectId)
                ->whereIn($tblTag.'.field_id', $fieldIds);
        
        $result->select($tblTag.'.id', $tblTag.'.value', 'tvl.field_id')
                ->groupBy($tblTag.'.id');
        $addSelect = $tblTag.'.status';
        if (!$checkStatus) {
            $addSelect = DB::raw(TagConst::TAG_STATUS_APPROVE . ' as status');
        }
        $result->addSelect($addSelect);
        
        return $result->get();
    }
    
    /**
     * get list project with leader info
     * @param type $projectIds
     * @return collection
     */
    public static function getWithLeader ($projectIds) {
        $tblProj = self::getTableName();
        $tblEmp = Employee::getTableName();
        $result = self::join($tblEmp.' as ld', $tblProj.'.leader_id', '=', 'ld.id')
                ->whereIn($tblProj.'.id', $projectIds)
                ->select($tblProj.'.id', $tblProj.'.tag_status', $tblProj.'.tag_assignee', $tblProj.'.leader_id', $tblProj.'.name', 
                        'ld.name as leader_name', 'ld.email as leader_email')
                ->get();
        return $result;
    }
    
    /**
     * get list project witht assignee
     * @param type $projectIds
     * @return collection
     */
    public static function getWithAssignee ($projectIds) {
        $tblProj = self::getTableName();
        $tblEmp = Employee::getTableName();
        $result = self::leftJoin($tblEmp.' as ld', $tblProj.'.leader_id', '=', 'ld.id')
                ->leftJoin($tblEmp.' as emas', $tblProj.'.tag_assignee', '=', 'emas.id')
                ->whereIn($tblProj.'.id', $projectIds)
                ->select($tblProj.'.id', $tblProj.'.tag_status', $tblProj.'.tag_assignee', $tblProj.'.manager_id', 
                        $tblProj.'.name', $tblProj.'.leader_id', 'ld.email as leader_email',
                        DB::raw('IFNULL(emas.id, ld.id) as assignee_id'),
                        DB::raw('IFNULL(emas.name, ld.name) as assignee_name'),
                        DB::raw('IFNULL(emas.email, ld.email) as assignee_email'))
                ->get();
        return $result;
    }
    
    /**
     * save project tags, must contain try catch, and DB transaction
     * @param type $data
     * @return type
     * @throws Exception
     */
    public static function saveListTags ($data) {
        $valid = \Validator::make($data, [
            'project_id' => 'required|numeric'
        ]);
        if ($valid->fails()) {
            throw new Exception(trans('tag::message.Not found item'));
        }
        $projectId = $data['project_id'];
        $projectTags = isset($data['proj_tags']) ? $data['proj_tags'] : [];
        $project = self::find($projectId);
        if (!$project || !$projectTags) {
            throw new Exception(trans('tag::message.Not found item'));
        }
        $fieldIds = array_keys($projectTags);
        $hasPermissApprove = Permission::getInstance()->isAllow('tag::object.project.approve.tag');
        if ($project->tag_status == TagConst::TAG_STATUS_APPROVE
                && !$hasPermissApprove) {
            throw new Exception(trans('core::message.You don\'t have access'));
        }

        $tagIds = [];
        foreach ($projectTags as $fieldId => $listTags) {
            if (!$listTags) {
                continue;
            }
            foreach ($listTags as $tagName) {
                $tag = Tag::createOrFindTag($fieldId, $tagName, TagConst::TAG_STATUS_REVIEW);
                if ($tag) {
                    $tagIds[$tag->id] = [
                        'field_id' => $tag->field_id
                    ];
                }
            }
        }
        //save project tags
        $project->tags($fieldIds)->sync($tagIds);
        $tagResults = TagValue::showNumProjectTags($projectId);

        return [
            'tag_status' => $project->tag_status,
            'tag_results' => $tagResults
        ];
    }
    
    /**
     * set tag status that belongs to project
     */
    public function submitDraftTags($fieldIds = []) {
        if (!$fieldIds) {
            $fieldIds = Field::getChildIds(TagConst::SET_TAG_PROJECT);
        }
        $this->tags($fieldIds)
                ->where('status', TagConst::TAG_STATUS_DRAFT)
                ->update(['status' => TagConst::TAG_STATUS_REVIEW]);
    }
    
    /*
     * insert project
     * 
     * @param array $data
     */
    public static function createProj($data)
    {
        DB::beginTransaction();
        try {
            $createdBy = Permission::getInstance()->getEmployee() ? 
                Permission::getInstance()->getEmployee()->id : null;
            $project = new Project();
            if (isset($data['base']['type_mm']) && 
                in_array($data['base']['type_mm'], Project::getTypeResourceEffort())
            ) {
                $project->type_mm = $data['base']['type_mm'];
            } else {
                $project->type_mm = Project::MM_TYPE;
            }
            $project->created_by = $createdBy;
            $project->setData($data['base'])->save();
            $projectMeta = new ProjectMeta();
            $projectMeta->project_id = $project->id;
            $projectMeta->level = 1;
            $projectMeta->save();
            
            if (isset($data['quality'])) {
                $quality = new ProjQuality();
                $quality->status = self::STATUS_APPROVED;
                $quality->project_id = $project->id;
                $quality->created_by = $createdBy;
                $quality->setData($data['quality'])->save();
            }
            
            $projectMember = new ProjectMember();
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
            
            ProjectPoint::findFromProject($project->id);
            ProjPointFlat::findFlatFromProject($project->id);
            
            $project->teamProject()->attach($data['team_id']);
            
            if ($data['sale_id']) {
                $project->saleProject()->attach($data['sale_id']);
            }
            
            $fullTeamName = null;
            $project->renderProjectCodeAuto($fullTeamName);
            SourceServer::saveFromRequest($project, $fullTeamName);
            if ($data['prog_langs']) {
                ProjectProgramLang::insertItems(
                        $project, 
                        (array) $data['prog_langs'],
                        [
                            'create' => true
                        ]
                );
            }
            DB::commit();
            CacheHelper::forget(self::KEY_CACHE_TEAM, $project->id);
            CacheHelper::forget(self::KEY_CACHE_MEMBER, $project->id);
            CacheHelper::forget(self::KEY_CACHE, $project->id);
            return $project;
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
