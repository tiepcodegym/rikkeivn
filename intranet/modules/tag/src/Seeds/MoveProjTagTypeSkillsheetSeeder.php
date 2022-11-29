<?php

namespace Rikkei\Tag\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Team\Model\EmplProjExperTag;
use Rikkei\Tag\Model\Tag;
use Rikkei\Tag\Model\Field;
use Rikkei\Tag\View\TagConst;
use Illuminate\Support\Facades\DB;

class MoveProjTagTypeSkillsheetSeeder extends CoreSeeder
{
    protected $fromProjType = 'lang'; // EmplProjExperTag change type from
    protected $fromTagType = 'database';// Tag type of tag
    protected $toProjType = 'other';// EmplProjExperTag change type to this

    public function run()
    {
        if ($this->checkExistsSeed(1)) {
            return;
        }

        $projTags = EmplProjExperTag::select(
                'proj_et.proj_exper_id',
                'proj_et.tag_id',
                'proj_et.type'
            )
            ->from(EmplProjExperTag::getTableName() . ' as proj_et')
            ->join(Tag::getTableName() . ' as tag', function ($join) {
                $join->on('tag.id', '=', 'proj_et.tag_id')
                    ->where('tag.status', '=', TagConst::TAG_STATUS_APPROVE)
                    ->whereNull('tag.deleted_at');
            })
            ->join(Field::getTableName() . ' as field', function ($join) {
                $join->on('field.id', '=', 'tag.field_id')
                    ->whereNull('field.deleted_at')
                    ->where('field.code', '=', $this->fromTagType);
            })
            ->where('proj_et.type', $this->fromProjType)
            ->groupBy('proj_et.proj_exper_id', 'proj_et.tag_id')
            ->get();

        if ($projTags->isEmpty()) {
            return;
        }

        DB::beginTransaction();
        try {
            foreach ($projTags as $projTag) {
                $projTag->type = $this->toProjType;
                $projTag->save();
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex) {
            \Log::info($ex);
            DB::rollback();
        }
    }

}
