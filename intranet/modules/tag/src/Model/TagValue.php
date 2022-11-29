<?php

namespace Rikkei\Tag\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Tag\Model\Field;
use Rikkei\Tag\View\TagConst;
use Rikkei\Tag\Model\Tag;
use Illuminate\Support\Facades\DB;
use Rikkei\Team\View\Permission;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\Model\TeamProject;
use Rikkei\Project\Model\SaleProject;
use Rikkei\Team\View\Config;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Core\View\CoreQB;
use Rikkei\Project\Model\ProjectMeta;
use Rikkei\Project\Model\ProjQuality;
use Rikkei\Project\Model\Project;

class TagValue extends CoreModel
{
    protected $table = 'kl_tag_values';
    protected $fillable = ['field_id', 'tag_id', 'entity_id'];
    public $timestamps = false;
    protected $primaryKey = false;
    public $incrementing = false;
    
    /**
     * check exists project tag
     * @param type $projectId
     * @param type $tagId
     * @param type $fieldId
     * @return type
     */
    public static function checkExistsProjectTag ($projectId, $tagId, $fieldId) {
        return self::where('entity_id', $projectId)
                ->where('tag_id', $tagId)
                ->where('field_id', $fieldId)
                ->first();
    }
    
    /**
     * get limit project tags
     * @param type $projectId
     * @param type $fieldIds
     * @param type $limit
     * @return collection
     */
    public static function showNumProjectTags ($projectId, $checkStatus = false, $fieldIds = [], $limit = null) {
        if (!$limit) {
            $limit = TagConst::NUM_SHOW_TAGS + 1;
        }
        if (!$fieldIds) {
            $fieldIds = Field::getChildIds(TagConst::SET_TAG_PROJECT);
        }
        $tblSelf = self::getTableName();
        $tblField = Field::getTableName();
        $queryStatus = 'tag.status';
        if (!$checkStatus) {
            $queryStatus = TagConst::TAG_STATUS_APPROVE;
        }
        
        return self::join(Tag::getTableName().' as tag', function ($join) use ($tblSelf) {
            $join->on($tblSelf.'.tag_id', '=', 'tag.id');
        })
        ->join($tblField.' as field', function ($join) use ($tblSelf) {
            $join->on($tblSelf.'.field_id', '=', 'field.id');
        })
        ->leftJoin(DB::raw('(SELECT tag_id, COUNT(*) as count_tag FROM '. $tblSelf .' GROUP BY tag_id ORDER BY count_tag DESC) AS tag_c'),
                $tblSelf.'.tag_id', '=', 'tag_c.tag_id')
        ->whereIn($tblSelf.'.field_id', $fieldIds)
        ->where($tblSelf.'.entity_id', '=', $projectId)
        ->select('tag.id', 'tag.value', DB::raw('IFNULL(field.color, "'. TagConst::COLOR_DEFAULT .'") as color'))
        ->groupBy('tag.id')
        ->orderBy('tag_c.count_tag', 'DESC')
        ->take($limit)
        ->get();
    }
    
    /**
     * get count tags of fields in project
     * @param type $projectId
     * @param type $fieldIds
     * @param type $statuses
     * @param null|string|array $addSelect
     * @return collection
     */
    public static function countTagOfFieldsInProject($projectId, $fieldIds = [], $statuses = [], $addSelect = null) {
        if (!$fieldIds) {
            $fieldIds = Field::getChildIds(TagConst::SET_TAG_PROJECT);
        }
        $tblFieldValue = self::getTableName();
        $tblField = Field::getTableName();
        
        $result = Field::leftJoin($tblFieldValue . ' as fvl', function ($join) use ($tblField, $projectId) {
                    $join->on($tblField.'.id', '=', 'fvl.field_id')
                            ->where('fvl.entity_id', '=', $projectId);
                })
                ->whereIn($tblField.'.id', $fieldIds)
                ->where($tblField.'.type', TagConst::FIELD_TYPE_TAG);
               
        //left join get all field
        $result->leftJoin(Tag::getTableName().' as tg', function ($join) use ($statuses) {
                $join->on('fvl.tag_id', '=', 'tg.id')
                        ->whereNull('tg.deleted_at');
                if ($statuses) {
                    $join->whereIn('tg.status', $statuses);
                }
            });
        
        $result->select($tblField.'.id as field_id',
                        DB::raw('COUNT(DISTINCT(tg.id)) as tag_count'));
        if ($addSelect) {
            $result->addSelect($addSelect);
        }
        return $result->groupBy($tblField.'.id')
                    ->get();
    }
    
    /**
     * get default tag checked from filter
     * @param type $inputParams
     * @return type
     */
    public static function getCheckedTagOfField ($inputParams) {
        $limit = 3;
        $fieldIds = $inputParams['fieldIds'];
        $tagSelect = ['field_id', 'id as tag_id', 'value as tag_name', DB::raw('0 as tag_count')];
        if (!$fieldIds) {
            $fields = Field::with(['tags' => function ($query) use ($tagSelect) {
                        $query->select($tagSelect);
                    }])
                    ->withCount('tags')
                    ->where('set', TagConst::SET_TAG_PROJECT)
                    ->orderBy('tags_count', 'desc')
                    ->take($limit)
                    ->get();
        } else {
            $tagIds = $inputParams['tagIds'];
            $fields = Field::with(['tags' => function ($query) use ($tagIds, $tagSelect) {
                    $query->select($tagSelect);
                    if ($tagIds) {
                        $query->orderBy(DB::raw('FIELD(id, '. implode(',', $tagIds) .')'), 'desc');
                    }
                }])
                ->select('id')
                ->whereIn('id', $fieldIds)
                ->get();
        }
        if ($fields->isEmpty()) {
            return [];
        }
        $list = [];
        foreach ($fields as $field) {
            $list[$field->id] = $field->tags->splice(0, $limit);
        }
        return $list;
    }
    
    /**
     * get most tags of field
     *      or tag selected
     * 
     * @param array $inputParams
     *          [tagIds => [], 'field' => [], 'fieldIds' => []]
     * @param int $setTypeField
     * @param int $limit
     * @return array
     */
    public static function getMostTagsOfField(
        $inputParams = [],
        &$option = [],
        $limit = 10
    ) {
        $projects = self::getProjInfo($inputParams, $option);
        $option['resultProjects'] = $projects['projectPager'];
        if (!count($projects['projectFull'])) {
            return self::getCheckedTagOfField($inputParams);
        }
        $tableProjTag = ViewProjTag::getTableName();
        // get field - tag avai in project
        $queryString = 'SELECT t_proj_tag.field_id, t_proj_tag.tag_id '
            . 'FROM ' . $tableProjTag . ' AS t_proj_tag '
            . 'WHERE t_proj_tag.project_id IN ('. implode(',', $projects['projectFull']) .') '
            . 'GROUP BY t_proj_tag.field_id, t_proj_tag.tag_id';
        $collection = DB::select($queryString);
        if (!count($collection)) {
            return self::getCheckedTagOfField($inputParams);
        }
        // get field - tag array
        $fieldTagsId = [];
        foreach ($collection as $item) {
            $fieldTagsId[$item->field_id][] = $item->tag_id;
        }
        $query = '';
        $queryCountTag = '';
        $queryTotalNumberProjOfField = '';
        $fieldBindings = [];
        foreach ($fieldTagsId as $fieldId => $tagIds) {
            $selectQuery = '(SELECT t_proj_tag.field_id, t_proj_tag.tag_id, '
                . 't_proj_tag.tag_name, COUNT(*) AS tag_count ';
            $fromJoinQuery = 'FROM ' . $tableProjTag . ' AS t_proj_tag '
                . 'WHERE t_proj_tag.field_id = ' . $fieldId . ' '
                . 'AND t_proj_tag.tag_id IN (' . implode(',', $tagIds) . ') '
                . 'AND t_proj_tag.project_id IN ('. 
                    implode(',', $projects['projectFull']) .') ';
            $groupQuery = 'GROUP BY t_proj_tag.field_id, t_proj_tag.tag_id ';
            //uu tien tag selected len tren
            $tagInput = [];
            if (isset($inputParams['field'][$fieldId]) && ($tagInput = $inputParams['field'][$fieldId])) {
                $selectQuery .= ', CASE WHEN t_proj_tag.tag_id IN ('. CoreQB::convertArraySymPDO($tagInput) .') THEN 1 ELSE 0 END as tag_index ';
                $fieldBindings = array_merge($fieldBindings, $tagInput);
            } else {
                $selectQuery .= ', 0 as tag_index ';
            }
            $orderQuery = 'ORDER BY tag_index DESC, tag_count DESC, tag_name ASC ';
            $limitQuery = 'LIMIT ' . ($limit + count($tagInput));
            $unionQuery = ') UNION ';
            // get field-tag avai
            $query .= $selectQuery . $fromJoinQuery . $groupQuery 
                . $orderQuery . $limitQuery . $unionQuery;
            // count number tag in field
            $queryCountTag .= '(SELECT COUNT(*) AS tag_number, field_id FROM ('
                . 'SELECT t_proj_tag.field_id ' 
                . $fromJoinQuery . $groupQuery .') AS t_field_tag_number '
                . $unionQuery;
            // count total number tag of field
            $queryTotalNumberProjOfField .= '(SELECT field_id, COUNT(*) AS total_item '
                . 'FROM (SELECT t_proj_tag.field_id ' . $fromJoinQuery 
                . 'GROUP BY t_proj_tag.field_id, t_proj_tag.project_id' .') AS t_total_tag '
                . $unionQuery;
        }
        $query = substr($query, 0, -6);
        $queryCountTag = substr($queryCountTag, 0, -6);
        $queryTotalNumberProjOfField = substr($queryTotalNumberProjOfField, 0, -6);
        $collection = DB::select($query, $fieldBindings);
        if (!count($collection)) {
            return self::getCheckedTagOfField($inputParams);
        }
        // get field  - tag result
        $collection = collect($collection)
                ->sortByDesc('tag_count');
        
        if ($collection) {
            //group by field id
            $fieldTags = [];
            foreach ($collection as $item) {
                $fieldId = $item->field_id;
                unset($item->field_id);
                unset($item->tag_index);
                if (!isset($fieldTags[$fieldId])) {
                    $fieldTags[$fieldId] = [$item];
                } else {
                    $fieldTags[$fieldId][] = $item;
                }
            }
            //filter limit tags
            foreach ($fieldTags as $fieldId => $tags) {
                if (count($tags) > $limit
                        && isset($inputParams['field'][$fieldId]) 
                        && ($tagInput = $inputParams['field'][$fieldId])) {
                    $subTagsLen = count($tagInput);
                    $subTags = array_slice($tags, 0, $limit);
                    for ($i = $limit; $i < $subTagsLen + $limit; $i++) {
                        if (in_array($tags[$i]->tag_id, $tagInput)) {
                            array_push($subTags, $tags[$i]);
                        }
                    }
                    $tags = $subTags;
                }
                $fieldTags[$fieldId] = $tags;
            }
        }
        
        // get number tag of field
        $collection = DB::select($queryCountTag);
        $option['resultNumberTagOfField'] = [];
        foreach ($collection as $item) {
            $option['resultNumberTagOfField'][$item->field_id] = $item->tag_number;
        }
        
        // get total tag in field
        $collection = DB::select($queryTotalNumberProjOfField);
        $option['resultTotalProjInField'] = $collection;
        
        // get count tag of team
        $option['resultTeamCountProj'] = self::teamCountProj($projects['projectFull']);
        //$option['projectIds'] = $projects['projectFull'];
                
        return $fieldTags;
    }
    
    /**
     * get project infomation
     * 
     * @param array $inputParams
     * @param int $data
     * @return collection
     */
    public static function getProjInfo(
        array $inputParams, 
        $data = []
    ) {
        if (!$data['projectFull'] || !$data['projectFull']) {
            return [
                'projectPager' => null,
                'projectFull' => null
            ];
        }
        $tableProjTag = ViewProjTag::getTableName();
        $tableProj = Project::getTableName();
        $tableProjMeta = ProjectMeta::getTableName();
        $tableEmployee = Employee::getTableName();
        $tableProjQuality = ProjQuality::getTableName();
        $tableTeamProject = TeamProject::getTableName();
        $tableTeam = Team::getTableName();
        
        $bindings = [];
        $queryTmp = [];
        $queryTmp[0] = CoreQB::resetQuery();
        // get project id available have field and tag
        $queryTmp[0]['select'] .= 't_proj_tag.project_id';
        $queryTmp[0]['from'] = $tableProjTag . ' AS t_proj_tag';
        // check permission get project
        $permission = Permission::getInstance();
        if ($permission->isScopeCompany(null, TagConst::RA_VIEW_SEARCH)) {
            // search all project of company
        } elseif ($permission->isScopeTeam(null, TagConst::RA_VIEW_SEARCH)) {
            // search all project of team
            $teams = $permission->getTeams();
            $tableMember = ProjectMember::getTableName();
            $tableSaleProject = SaleProject::getTableName();
            $userId = $permission->getEmployee()->id;
            $queryTmp[0]['join'] .= 'JOIN ' . $tableMember . ' AS t_proj_member ON '
                . 't_proj_member.project_id = t_proj_tag.project_id '
                . 'JOIN ' . $tableTeamProject . ' AS t_proj_team ON '
                . 't_proj_team.project_id = t_proj_tag.project_id '
                . 'LEFT JOIN ' . $tableSaleProject . ' AS t_proj_sale '
                . 'ON t_proj_sale.project_id = t_proj_tag.project_id '
                . 'JOIN ' . $tableProj 
                . ' AS t_proj ON t_proj.id = t_proj_tag.project_id '
                . 'AND t_proj.deleted_at is null ';
            // view all project of self
            $queryTmp[0]['where'] .= 'AND ((t_proj_member.employee_id = '.$permission->getEmployee()->id
                    .' AND t_proj_member.status = '.ProjectMember::STATUS_APPROVED.') '
                    . 'OR t_proj_sale.employee_id = ' . $permission->getEmployee()->id . ' '
                    . 'OR t_proj.leader_id = ' . $userId . ' '
                    . 'OR t_proj.manager_id = ' . $userId . ' ';
            if ($teams) { // view all project in team
                $queryTmp[0]['where'] .= 'OR t_proj_team.team_id IN ('.implode(',', $teams).')';
            }
            $queryTmp[0]['where'] .= ') ';
        } elseif ($permission->isScopeSelf(null, TagConst::RA_VIEW_SEARCH)) {
            $tableSaleProject = SaleProject::getTableName();
            $tableMember = ProjectMember::getTableName();
            $userId = $permission->getEmployee()->id;
            $queryTmp[0]['join'] .= 'LEFT JOIN ' . $tableSaleProject . ' AS t_proj_sale '
                . 'ON t_proj_sale.project_id = t_proj_tag.project_id '
                . 'JOIN ' . $tableMember . ' AS t_proj_member ON '
                . 't_proj_member.project_id = t_proj_tag.project_id '
                . 'JOIN ' . $tableProj 
                . ' AS t_proj ON t_proj.id = t_proj_tag.project_id '
                . 'AND t_proj.deleted_at is null ';
            $queryTmp[0]['where'] .= 'AND ((t_proj_member.employee_id = '.$userId
                .' AND t_proj_member.status = '.ProjectMember::STATUS_APPROVED.') '
                . 'OR t_proj_sale.employee_id = ' . $permission->getEmployee()->id . ' '
                . 'OR t_proj.leader_id = ' . $userId . ' '
                . 'OR t_proj.manager_id = ' . $userId
                . ') ';
        } else {
            return [
                'projectPager' => null,
                'projectFull' => null
            ];
        }
        // where project full get from local
        $bindString = CoreQB::convertArraySymPDO($data['projectFull']);
        $bindings = array_merge($bindings, (array) $data['projectFull']);
        $queryTmp[0]['where'] .= 'AND t_proj_tag.project_id IN ('.$bindString.') ';
        $queryTmp[0]['group'] .= 't_proj_tag.project_id';
        // filter search
        $search = null;
        $keySearch = 'search';
        $dataPager = $data['filter'];
        if (isset($dataPager[$keySearch])) {
            $search = json_decode($dataPager[$keySearch], true);
            unset($dataPager[$keySearch]);
        }

        if ($search) {
            $queryTmp[1] = CoreQB::resetQuery();
            $queryTmp[1]['select'] = 't_tmp_proj1.project_id';
            $queryTmp[1]['from'] = '('.CoreQB::getQuery($queryTmp[0]).') AS t_tmp_proj1';
            $queryTmp[1]['join'] .= 'JOIN ' . $tableProjTag . ' AS t_proj_tag '
                . 'ON t_tmp_proj1.project_id = t_proj_tag.project_id ';
            
            //search man month
            $excerptKeys = ['billable_effort'];
            $queryTmp[1]['join'] .= 'JOIN ' . $tableProj 
                . ' AS t_proj ON t_proj.id = t_proj_tag.project_id '
                . 'AND t_proj.deleted_at is null ';
            foreach ($search as $key => $value) {
                if (in_array($key, $excerptKeys)) {
                    if ($key == 'billable_effort' && is_array($value)) {
                        $queryTmp[1]['join'] .= 'LEFT JOIN ' . $tableProjQuality 
                            . ' AS t_pql ON t_pql.project_id = t_proj.id '
                            . 'AND t_pql.deleted_at is null AND t_pql.status = ' 
                            . Project::STATUS_APPROVED . ' ';
                        $queryTmp[1]['where'] .= 'AND (CASE WHEN t_proj.type_mm = '
                            . Project::MD_TYPE
                            . ' THEN t_pql.billable_effort/20 ELSE t_pql.billable_effort END) BETWEEN ? AND ? ';
                        $bindings[] = $value[0];
                        $bindings[] = $value[1];
                    }
                } else if (is_array($value)) {
                    if (count($value)) {
                        $bindString = CoreQB::convertArraySymPDO($value);
                        $bindings = array_merge($bindings, $value);
                        $queryTmp[1]['where'] .= 'AND ' . $key . ' IN ('.$bindString.') ';
                    }
                } else {
                    $queryTmp[1]['where'] .= 'AND ' . $key . " like ? ";
                    $bindings[] = '%'.$value.'%';
                }
            }
            if (key_exists('tpj.team_id', $search)) {
                $tblTeam = Team::getTableName();
                $tblTeamProj = TeamProject::getTableName();
                $queryTmp[1]['join'] .= 'JOIN ' . $tblTeamProj . ' AS tpj '
                    . 'ON t_proj_tag.project_id = tpj.project_id '
                    . 'JOIN ' . $tblTeam . ' AS team '
                    . 'ON tpj.team_id = team.id ';
            }
        } else {
            $queryTmp[1] = $queryTmp[0];
        }
        $query = CoreQB::resetQuery();
        $query['select'] = 't_tmp_proj.project_id';
        $query['from'] = '(' . CoreQB::getQuery($queryTmp[1])
            .') AS t_tmp_proj';
        $pager = Config::getPagerData(null, $dataPager);
        $status = Project::STATUS_APPROVED;
        
        $query['select'] .= ', t_proj_tag.project_id AS id, '
            . 't_proj.name, '
            . 'SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT(t_proj_tag.tag_id) '
                . 'SEPARATOR "-"), "-", '. (TagConst::NUM_SHOW_TAGS + 1)  
                .') as tag_ids, '
            . 'COUNT(DISTINCT(t_proj_tag.tag_id)) as count_tags, '
            . 't_proj.status as proj_status, t_pm.email as pm_email, '
            . 'CASE WHEN t_proj.type_mm = '. Project::MD_TYPE 
                . ' THEN t_pql.billable_effort/20 ELSE t_pql.billable_effort END '
                . 'AS effort, '
            . 't_proj.start_at as start_date, t_proj.end_at as end_date, '
            . 't_proj.leader_id, '
            . 'GROUP_CONCAT(DISTINCT(CONCAT(t_team.id, ":" ,t_team.name)) '
                . 'SEPARATOR "|") as team_idnames, '
            . 't_proj_meta.scope_desc, t_proj_meta.scope_scope';
        $query['join'] .= 'JOIN ' . $tableProjTag . ' AS t_proj_tag '
            . 'ON t_tmp_proj.project_id = t_proj_tag.project_id '
            . 'JOIN ' . $tableProj . ' AS t_proj ON t_proj.id = t_proj_tag.project_id '
                . 'AND t_proj.deleted_at is null '
            . 'JOIN ' . $tableProjMeta . ' AS t_proj_meta '
            . 'ON t_proj_meta.project_id = t_proj_tag.project_id '
            . 'JOIN ' . $tableEmployee . ' AS t_pm ON t_proj.manager_id = t_pm.id '
                . 'AND t_pm.deleted_at is null '
            . 'LEFT JOIN ' . $tableProjQuality . ' AS t_pql ON t_proj.id = t_pql.project_id '
                . 'AND t_pql.deleted_at is null AND t_pql.status = ' . $status . ' '
            . 'JOIN ' . $tableTeamProject . ' AS t_tpj ON t_proj.id = t_tpj.project_id '
                . 'AND t_tpj.deleted_at is null '
            . 'JOIN ' . $tableTeam . ' AS t_team ON t_tpj.team_id = t_team.id '
                . 'AND t_team.deleted_at is null ';
        $query['group'] .= 't_proj_tag.project_id';
        if (!isset($dataPager['order']) || $dataPager['order'] == 'created_at') {
            $query['order'] = 't_proj_tag.project_id DESC';
        } else {
            $query['order'] = $pager['order'] . ' ' . $pager['dir'];
        }
        $queryBuilder = new CoreQB([
            'perPage' => 50,
            'query' => $query,
            'bindings' => $bindings,
            'colCount' => 't_proj_tag.project_id'
        ]);
        $projectPager = $queryBuilder->execQuery()->renderPager();
        $query['select'] = 't_proj_tag.project_id';
        $query['order'] = '';
        $queryBuilder->setDataPager([
            'query' => $query
        ]);
        $projectFullResult = $queryBuilder->getFullResult();
        $projectFull = array_map(function($item) {
            return (int) $item->project_id;
        }, $projectFullResult);
        
        return [
            'projectPager' => $projectPager,
            'projectFull' => $projectFull
        ];
    }
    
    /**
     * get count project of team
     * 
     * @param array $projectIds
     * @return array 
     */
    public static function teamCountProj(array $projectIds)
    {
        $tableTeam = Team::getTableName();
        $tableTeamProj = TeamProject::getTableName();
        $query = 'SELECT COUNT(*) AS count_proj, t_team_proj.team_id FROM '
            . $tableTeamProj . ' AS t_team_proj '
            . 'JOIN ' . $tableTeam . ' AS t_team ON t_team.id = t_team_proj.team_id '
                . ' AND t_team.deleted_at is null '
            . 'WHERE t_team_proj.project_id IN ('.  implode(',', $projectIds).') '
            . 'GROUP BY t_team_proj.team_id';
        return DB::select($query);
    }
    
    /**
     * get employees list from project ids
     * @param array $projectIds
     * @return type
     */
    public static function getEmployeesList($projectIds, $data = []) 
    {
        $search = null;
        if (isset($data['search'])) {
            $search = json_decode($data['search']);
        }
        $pager = Config::getPagerData(null, $data);
        
        $tblProj = ProjectTag::getTableName();
        $tblProjMember = ProjectMember::getTableName();
        $tblEmp = Employee::getTableName();
        $tblTeam = Team::getTableName();
        $tblTeamMember = TeamMember::getTableName();
        $tblTagValue = self::getTableName();
        $tblTag = Tag::getTableName();
        $tblField = Field::getTableName();
        $fieldIds = Field::getChildIds(TagConst::SET_TAG_PROJECT);
        
        $collection = DB::table($tblEmp.' as emp')
                ->join($tblProjMember.' as pjm', function ($join) {
                    $join->on('emp.id', '=', 'pjm.employee_id')
                            ->where('pjm.status', '=', ProjectMember::STATUS_APPROVED);
                })
                ->join($tblProj.' as proj', function ($join) {
                    $join->on('pjm.project_id', '=', 'proj.id')
                            ->whereNull('proj.deleted_at');
                })
                ->leftJoin($tblTeamMember.' as tmb', 'tmb.employee_id', '=', 'emp.id')
                ->leftJoin($tblTeam.' as team', 'tmb.team_id', '=', 'team.id')
                ->leftJoin(DB::raw('(SELECT tvl2.entity_id, tvl2.tag_id, '
                        . 'tg2.value, tg2.field_id, tg2.status, tg2.sort_order, '
                        . 'fd2.color, tvl3.count_tag '
                    . 'FROM '. $tblTagValue .' AS tvl2 '
                    . 'INNER JOIN '. $tblTag .' AS tg2 '
                        . 'ON tvl2.tag_id = tg2.id '
                    . 'INNER JOIN '. $tblField .' AS fd2 '
                        . 'ON tg2.field_id = fd2.id '
                    . 'LEFT JOIN '
                        . '(SELECT tag_id, COUNT(*) as count_tag '
                            . 'FROM '. $tblTagValue .' '
                            . 'GROUP BY tag_id '
                            . 'ORDER BY count_tag DESC) AS tvl3 '
                        . 'ON tvl2.tag_id = tvl3.tag_id ' //select most tags
                    . 'WHERE tg2.deleted_at IS NULL '
                    . 'AND tg2.field_id IN ('. implode(',', $fieldIds) .') '
                    . 'ORDER BY tvl3.count_tag DESC) AS tgvl'), 
                function ($join) use ($tblProj) {
                    $join->on('proj.id', '=', 'tgvl.entity_id');
                })
                ->whereIn('proj.id', $projectIds)
                ->whereNull('emp.deleted_at')
                ->groupBy('emp.id');
        
        if ($search) {
            //excerpt keys
            $excerptKeys = ['emp.email'];
            foreach ($search as $key => $value) {
                if (in_array($key, $excerptKeys)) {
                    if ($key == 'emp.email') {
                        $collection->where(function ($query) use ($value) {
                            $query->where(DB::raw('SUBSTRING(emp.email, 1, LOCATE("@", emp.email) - 1)'), 'like', '%'. $value .'%')
                                    ->orWhere('emp.name', 'like', '%'. $value .'%');
                        });
                    }
                } elseif (is_array($value)) {
                    if (count($value) > 0) {
                        $collection->whereIn($key, $value);
                    }
                } else {
                    $collection->where($key, 'like', '%' . $value . '%');
                }
            }
        }
        // check permission get project
        $permission = Permission::getInstance();
        if ($permission->isScopeCompany(null, TagConst::RA_VIEW_SEARCH)) {
            // search all employee of company
        } elseif ($permission->isScopeTeam(null, TagConst::RA_VIEW_SEARCH)) {
            // search all employee of team
            $teams = $permission->getTeams();
            if (!count($teams)) {
                return null;
            }
            $collection->whereIn('tmb.team_id', $teams);
        } elseif ($permission->isScopeSelf(null, TagConst::RA_VIEW_SEARCH)) {
            $collection->where('emp.id', $permission->getEmployee()->id);
        } else {
            return null;
        }
        
        $collection->select('emp.id', 'emp.name', 'emp.email', 'emp.birthday',
            DB::raw('CASE WHEN emp.gender = 0 THEN "'. trans('tag::view.Female') .'" '
                    . 'ELSE "'. trans('tag::view.Male') .'" END AS gender_text'),
            DB::raw('GROUP_CONCAT(DISTINCT(team.name) SEPARATOR ", ") as team_names'),
            DB::raw('SUBSTRING_INDEX('
                . 'GROUP_CONCAT(DISTINCT(tgvl.tag_id) '
                . 'ORDER BY tgvl.count_tag DESC, tgvl.value ASC SEPARATOR "-")'
                . ', "-", '. (TagConst::NUM_SHOW_TAGS + 1) 
            .') as tag_ids'),
            DB::raw('COUNT(DISTINCT(tgvl.tag_id)) as count_tags'));
        if (!isset($data['order'])) {
            $collection->orderBy($tblProj.'.created_at', 'desc');
        } else {
            $collection->orderBy($pager['order'], $pager['dir']);
        }
        $collection->orderBy('emp.name', 'asc');
        return $collection->paginate($pager['limit']);
    }
    
    /**
     * insert multi record of tag field
     * 
     * @param object $project
     * @param object $field
     * @param array $tagIds
     */
    public static function insertMulti($project, $field, $tagIds)
    {
        $collection = self::select('tag_id')
            ->where('entity_id', $project->id)
            ->where('field_id', $field->id)
            ->get();
        $tagIdExists = [];
        foreach ($collection as $item) {
            $tagIdExists[] = $item->tag_id;
        }
        $arrayInsert = [];
        $count = 0;
        foreach ($tagIds as $item) {
            if (in_array($item, $tagIdExists)) {
                continue;
            }
            $arrayInsert[] = [
                'field_id' => $field->id,
                'entity_id' => $project->id,
                'tag_id' => $item
            ];
            $count++;
        }
        if ($arrayInsert) {
            self::insert($arrayInsert);
        }
        return $count;
    }
}
