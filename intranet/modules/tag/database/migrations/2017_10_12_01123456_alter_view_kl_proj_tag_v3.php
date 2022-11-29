<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Rikkei\Tag\Model\Tag;
use Rikkei\Tag\Model\TagValue;
use Rikkei\Tag\Model\Field;
use Rikkei\Tag\View\TagConst;

class AlterViewKlProjTagV3 extends Migration
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
        
        $query = 'CREATE ALGORITHM=MERGE VIEW ' . $this->tbl . ' AS ( '
            . 'SELECT t_tag_value.tag_id, t_tag_value.field_id, '
                . 't_tag_value.entity_id AS project_id, t_tag.value as tag_name '
            . 'FROM ' . $tableTagValue . ' AS t_tag_value '
            . 'JOIN ' . $tableTag . ' AS t_tag ON t_tag.id = t_tag_value.tag_id '
                . 'AND t_tag.deleted_at is null '
            . 'JOIN ' . $tableField . ' AS t_field ON t_field.id = t_tag.field_id '
                . 'AND t_field.deleted_at is null AND t_field.set = ' 
                . TagConst::SET_TAG_PROJECT 
            . ' GROUP BY t_tag_value.tag_id, t_tag_value.field_id, t_tag_value.entity_id'
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
