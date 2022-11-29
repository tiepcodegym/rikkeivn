<?php

namespace Rikkei\Tag\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Permission;
use Rikkei\Tag\View\TagConst;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\Model\TeamProject;
use Rikkei\Project\Model\SaleProject;
use Rikkei\Core\View\CoreQB;
use Rikkei\Project\Model\Project;
use Rikkei\Core\Model\CoreConfigData;
use Illuminate\Support\Facades\DB;
use Rikkei\Project\Model\ProjQuality;

class ViewProjTag extends CoreModel
{
    protected $table = 'view_kl_proj_tag';
    
    /**
     * get all data
     * 
     * @return collection
     */
    public static function getAllData()
    {
        $tableProjTag = self::getTableName();
        $tableProj = Project::getTableName();
        $permission = Permission::getInstance();
        $userId = $permission->getEmployee()->id;
        
        $collection = self::select([$tableProjTag.'.tag_id', 
            $tableProjTag.'.field_id', $tableProjTag.'.tag_name', 
            $tableProjTag.'.project_id']);
        // check permission get project
        if ($permission->isScopeCompany(null, TagConst::RA_VIEW_SEARCH)) {
            // search all project of company
        } elseif ($permission->isScopeTeam(null, TagConst::RA_VIEW_SEARCH)) {
            // search all project of team
            $teams = $permission->getTeams();
            $tableMember = ProjectMember::getTableName();
            $tableTeamProject = TeamProject::getTableName();
            $tableSaleProject = SaleProject::getTableName();
            
            $collection->join($tableMember . ' AS t_proj_member', 
                't_proj_member.project_id', '=', $tableProjTag.'.project_id')
                ->join($tableTeamProject . ' AS t_proj_team', 
                    't_proj_team.project_id', '=', $tableProjTag.'.project_id')
                ->leftJoin($tableSaleProject . ' AS t_proj_sale', 
                    't_proj_sale.project_id', '=', $tableProjTag.'.project_id')
                ->join($tableProj . ' AS t_proj', 't_proj.id', '=', $tableProjTag.'.project_id')
                ->where(function($query) use ($userId, $teams){
                    // view all self
                    $query->orWhere(function($query) use ($userId) {
                        $query->where('t_proj_member.employee_id', 
                            $userId)
                            ->where('t_proj_member.status', 
                                ProjectMember::STATUS_APPROVED);
                    })
                    ->orWhere('t_proj_sale.employee_id', $userId)
                    ->orWhere('t_proj.leader_id', $userId)
                    ->orWhere('t_proj.manager_id', $userId);
                    if ($teams && count($teams)) { // view all project in team
                        $query->orWhereIn('t_proj_team.team_id', $teams);
                    }
                });
        } elseif ($permission->isScopeSelf(null, TagConst::RA_VIEW_SEARCH)) {
            $tableSaleProject = SaleProject::getTableName();
            $tableMember = ProjectMember::getTableName();
            
            $collection->join($tableMember . ' AS t_proj_member', 
                't_proj_member.project_id', '=', $tableProjTag.'.project_id')
                ->leftJoin($tableSaleProject . ' AS t_proj_sale', 
                    't_proj_sale.project_id', '=', $tableProjTag.'.project_id')
                ->join($tableProj . ' AS t_proj', 't_proj.id', '=', $tableProjTag.'.project_id')
                ->where(function($query) use ($userId) {
                    // view all self
                    $query->orWhere(function($query) use ($userId) {
                        $query->where('t_proj_member.employee_id', 
                            $userId)
                            ->where('t_proj_member.status', 
                                ProjectMember::STATUS_APPROVED);
                    })
                    ->orWhere('t_proj_sale.employee_id', $userId)
                    ->orWhere('t_proj.leader_id', $userId)
                    ->orWhere('t_proj.manager_id', $userId);
                });
        } else {
            return null;
        }
        return $collection->get();
    }
    
    /**
     * get max billable effort
     * 
     * @return int
     */
    public static function getMaxBillable()
    {
        $tableProj = Project::getTableName();
        $tableProjQuality = ProjQuality::getTableName();
        $tableProjTag = self::getTableName();
        $status = Project::STATUS_APPROVED;
        
        $mmUnit = CoreConfigData::get('project.mm');
        $query = CoreQB::resetQuery();
        $query['select'] = 'MAX(CASE WHEN `t_proj`.`type_mm` = '. Project::MD_TYPE .' '
            . 'THEN t_pql.billable_effort/'.$mmUnit.' ELSE t_pql.billable_effort END '
            . ') AS max_billable';
        $query['from'] = $tableProjTag . ' AS t_proj_tag';
        $query['join'] = 'JOIN ' . $tableProj . ' AS t_proj '
            . 'ON t_proj.id = t_proj_tag.project_id AND t_proj.deleted_at is null '
            . 'LEFT JOIN ' . $tableProjQuality . ' AS t_pql '
            . 'ON t_proj.id = t_pql.project_id '
            . 'AND t_pql.deleted_at is null AND t_pql.status = ' . $status;
        $collection = DB::select(CoreQB::getQuery($query));
        if (!$collection) {
            return 0;
        }
        return $collection[0]->max_billable;
    }
}
