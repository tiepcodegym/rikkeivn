<?php
namespace Rikkei\Team\Seeds;

use DB;
use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Team\Model\EmplProjExperTag;
use Rikkei\Team\Model\EmployeeProjExper;

class SkillProjResConvertSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed(5)) {
            return true;
        }

        /*$aryConvert = [
            ProjectMember::TYPE_DEV => ['Coding', 'Unit Test', 'Maintenance'],
            ProjectMember::TYPE_SQA => ['Integration Test'],
            ProjectMember::TYPE_TEAM_LEADER => ['Basic Detail', 'Coding', 'Unit Test', 'Maintenance'],
            ProjectMember::TYPE_BA => ['Requirement Definition'],
            ProjectMember::TYPE_BRSE => ['Requirement Definition'],
        ];*/
        DB::beginTransaction();
        try {
            $experTbl = EmplProjExperTag::getTableName();
            $dupTagIdItems = EmplProjExperTag::select('*', DB::raw('COUNT(proj_exper_id) as count'))
                    ->where('type', 'res')
                    ->whereNotNull('tag_id')
                    ->groupBy('proj_exper_id', 'tag_id')
                    ->having(DB::raw('COUNT(proj_exper_id)'), '>', 1)
                    ->get();
            if (!$dupTagIdItems->isEmpty()) {
                $dataInsert = [];
                foreach ($dupTagIdItems as $item) {
                    DB::table($experTbl)->where('proj_exper_id', $item->proj_exper_id)
                        ->where('tag_id', $item->tag_id)
                        ->where('type', 'res')
                        ->where('lang', 'en')
                        ->delete();
                    $dataInsert[] = [
                        'proj_exper_id' => $item->proj_exper_id,
                        'tag_id' => $item->tag_id,
                        'type' => 'res',
                        'tag_text' => null,
                        'lang' => 'en'
                    ];
                }
                if ($dataInsert) {
                    EmplProjExperTag::insert($dataInsert);
                }
            }

            $dupTagTextItems = EmplProjExperTag::select('*', DB::raw('COUNT(proj_exper_id) as count'))
                    ->where('type', 'res')
                    ->whereNull('tag_id')
                    ->whereNotNull('tag_text')
                    ->groupBy('proj_exper_id', 'tag_text')
                    ->having(DB::raw('COUNT(proj_exper_id)'), '>', 1)
                    ->get();
            if (!$dupTagTextItems->isEmpty()) {
                $dataInsert = [];
                foreach ($dupTagTextItems as $item) {
                    DB::table($experTbl)->where('proj_exper_id', $item->proj_exper_id)
                        ->where('tag_text', $item->tag_text)
                        ->where('type', 'res')
                        ->where('lang', 'en')
                        ->delete();
                    $dataInsert[] = [
                        'proj_exper_id' => $item->proj_exper_id,
                        'tag_id' => null,
                        'type' => 'res',
                        'tag_text' => $item->tag_text,
                        'lang' => 'en'
                    ];
                }
                if ($dataInsert) {
                    EmplProjExperTag::insert($dataInsert);
                }
            }

            /*$dataInsert = [];
            $experTbl = EmplProjExperTag::getTableName();
            foreach ($aryConvert as $tagId => $newTexts) {
                $experItems = EmplProjExperTag::where(function ($query) use ($tagId) {
                    $query->where('tag_id', $tagId)
                            ->orWhereIn('tag_text', ProjectMember::getTypeMember());
                })
                    ->where('type', 'res')
                    ->where('lang', 'en')
                    ->get();
                if ($experItems->isEmpty()) {
                    continue;
                }
                foreach ($experItems as $item) {
                    foreach ($newTexts as $text) {
                        $textIndex = $this->findResIdByName($text);
                        $existItem = EmplProjExperTag::where('proj_exper_id', $item->proj_exper_id)
                                ->where('type', 'res')
                                ->where('lang', 'en')
                                ->where(function ($query) use ($text, $textIndex) {
                                    $query->where('tag_text', $text)
                                            ->orWhere('tag_id', $textIndex);
                                })
                                ->first();
                        if (!$existItem) {
                            $dataInsert[] = [
                                'proj_exper_id' => $item->proj_exper_id,
                                'tag_id' => null,
                                'type' => 'res',
                                'tag_text' => $text,
                                'lang' => 'en'
                            ];
                        }
                    }
                    //delete old item
                    DB::table($experTbl)->where('proj_exper_id', $item->proj_exper_id)
                        ->where(function ($query) {
                            $query->whereNotNull('tag_id')
                                    ->orWhereIn('tag_text', ProjectMember::getTypeMember());
                        })
                        ->where('type', 'res')
                        ->where('lang', 'en')
                        ->delete();
                }
            }
            //insert new
            if ($dataInsert) {
                EmplProjExperTag::insert($dataInsert);
            }*/
            $this->insertSeedMigrate();

            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    function findResIdByName($name)
    {
        $resEn = EmployeeProjExper::getResponsiblesDefine()['en'];
        if (($index = array_search($name, $resEn)) !== false) {
            return $index;
        }
        return -1;
    }
}
