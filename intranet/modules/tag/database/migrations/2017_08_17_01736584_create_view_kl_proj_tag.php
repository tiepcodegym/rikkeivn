<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Rikkei\Tag\Model\Tag;
use Rikkei\Tag\Model\TagValue;
use Rikkei\Tag\Model\Field;
use Rikkei\Project\Model\Project;
use Rikkei\Tag\View\TagConst;

class CreateViewKlProjTag extends Migration
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
            return;
        }
        $tableTag = Tag::getTableName();
        $tableTagValue = TagValue::getTableName();
        $tableField = Field::getTableName();
        $tableProject = Project::getTableName();
        
        $query = 'CREATE ALGORITHM=MERGE VIEW ' . $this->tbl . ' AS ( '
            . 'SELECT t_tag_value.tag_id, t_tag_value.field_id, '
            . 't_tag.value as tag_name, t_field.color AS field_color, '
            . 't_tag_value.entity_id AS project_id, t_proj.name AS proj_name, '
            . 't_proj.status AS proj_status '
            . 'FROM ' . $tableTagValue . ' AS t_tag_value '
            . 'JOIN ' . $tableTag . ' AS t_tag ON t_tag.id = t_tag_value.tag_id '
                . 'AND t_tag.deleted_at is null '
            . 'JOIN ' . $tableField . ' AS t_field ON t_field.id = t_tag.field_id '
                . 'AND t_field.deleted_at is null AND t_field.set = ' 
                . TagConst::SET_TAG_PROJECT 
            . ' JOIN ' . $tableProject . ' AS t_proj ON t_proj.id = t_tag_value.entity_id '
                . 'AND t_proj.deleted_at is null '
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
