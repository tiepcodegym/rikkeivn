<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Rikkei\Tag\Model\Tag;
use Rikkei\Tag\Model\TagValue;
use Rikkei\Tag\Model\Field;
use Rikkei\Project\Model\Project;
use Rikkei\Tag\View\TagConst;
use Rikkei\Project\Model\ProjQuality;
use Rikkei\Team\Model\Team;
use Rikkei\Project\Model\TeamProject;
use Rikkei\Team\Model\Employee;

class AlterViewKlProjTagV2 extends Migration
{
    protected $tbl = 'view_kl_proj_tag';
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->tbl)) {
            DB::statement('DROP VIEW ' . $this->tbl);
        }
        $tableTag = Tag::getTableName();
        $tableTagValue = TagValue::getTableName();
        $tableField = Field::getTableName();
        $tableProject = Project::getTableName();
        $tableProjQuality = ProjQuality::getTableName();
        $tableTeam = Team::getTableName();
        $tableTeamProject = TeamProject::getTableName();
        $tableEmployee = Employee::getTableName();
        $status = Project::STATUS_APPROVED;
        
        $query = 'CREATE ALGORITHM=MERGE VIEW ' . $this->tbl . ' AS ( '
            . 'SELECT t_tag_value.tag_id, t_tag_value.field_id, '
                . 't_tag.value as tag_name, t_field.color AS field_color, '
                . 't_tag_value.entity_id AS project_id, t_proj.name AS proj_name, '
                . 't_proj.status AS proj_status, t_proj.type as proj_type, '
                . 'DATE(t_proj.start_at) as start_date, DATE(t_proj.end_at) as end_date, '
                . 't_proj.leader_id, '
                . 'GROUP_CONCAT(DISTINCT(CONCAT(t_team.id, ":" ,t_team.name)) '
                    . 'SEPARATOR "|") as team_idnames, '
                . 'CASE WHEN t_proj.type_mm = '. Project::MD_TYPE .' '
                    . 'THEN t_pql.billable_effort/20 ELSE t_pql.billable_effort END AS proj_effort, '
                . 't_pm.email as pm_email '
            . 'FROM ' . $tableTagValue . ' AS t_tag_value '
            . 'JOIN ' . $tableTag . ' AS t_tag ON t_tag.id = t_tag_value.tag_id '
                . 'AND t_tag.deleted_at is null '
            . 'JOIN ' . $tableField . ' AS t_field ON t_field.id = t_tag.field_id '
                . 'AND t_field.deleted_at is null AND t_field.set = ' 
                . TagConst::SET_TAG_PROJECT 
            . ' JOIN ' . $tableProject . ' AS t_proj ON t_proj.id = t_tag_value.entity_id '
                . 'AND t_proj.deleted_at is null '
            . 'LEFT JOIN ' . $tableProjQuality . ' AS t_pql ON t_proj.id = t_pql.project_id '
                . 'AND t_pql.deleted_at is null AND t_pql.status = ' . $status . ' '
            . 'JOIN ' . $tableTeamProject . ' AS t_tpj ON t_proj.id = t_tpj.project_id '
                . 'AND t_tpj.deleted_at is null '
            . 'JOIN ' . $tableTeam . ' AS t_team ON t_tpj.team_id = t_team.id '
                . 'AND t_team.deleted_at is null '
            . 'JOIN ' . $tableEmployee . ' AS t_pm ON t_proj.manager_id = t_pm.id '
                . 'AND t_pm.deleted_at is null '
            . 'GROUP BY t_tag.id, t_proj.id, t_field.id'
        . ')';
        DB::statement($query);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW ' . $this->tbl);
    }
}
