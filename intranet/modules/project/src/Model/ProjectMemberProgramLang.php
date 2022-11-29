<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Support\Facades\DB;
use Exception;
use Rikkei\Resource\Model\Programs;

class ProjectMemberProgramLang extends CoreModel
{
    public $timestamps = false;
    protected $table = 'proj_member_prog_langs';
    
    /**
     * check exists members has programs not include new programs
     * 
     * @param model $project
     * @param array $programIds
     * @return boolean
     */
    public static function existsMembersNotIncludeProgram($project, array $programIds = [])
    {
        if (!$project || !$project->id) {
            return false;
        }
        $tableMemberProgram = self::getTableName();
        $tableMember = ProjectMember::getTableName();
        
        $collection = self::select(DB::raw('count(*) as count'))
            ->join($tableMember, $tableMember.'.id', '=',
                $tableMemberProgram.'.proj_member_id')
            ->where($tableMember.'.project_id', $project->id)
            ->whereNotIn($tableMemberProgram.'.prog_lang_id', $programIds);
        if (ProjectMember::isUseSoftDelete()) {
            $collection->whereNull($tableMember.'.deleted_at');
        }
        $collection = $collection->first();
        if ($collection->count) {
            return true;
        }
        return false;
    }
    
    /**
     * get programs language of member
     * 
     * @param model $member
     * @return array
     */
    public static function getProgramLangIdsOfMember($member)
    {
        return self::select('prog_lang_id')
            ->where('proj_member_id', $member->id)
            ->lists('prog_lang_id')->toArray();
    }
    
    /**
     * insert or update languages to member of project
     * 
     * @param model $project
     * @param model $member
     * @param array $programLangIds
     * @param array $option
     */
    public static function insertMemberPrograms(
            $member,
            array $programLangIds = [], 
            array $option = []
    ) {
        if (!$member || !$member->id) {
            return false;
        }
        if (!in_array($member->type, self::getTypeMemberAvaiLang())) {
            self::where('proj_member_id', $member->id)
                ->delete();
            return true;
        }
        
        // check items old same items new
        $itemsOldIds = self::select('prog_lang_id')
            ->where('proj_member_id', $member->id)
            ->lists('prog_lang_id')->toArray();
        if ($programLangIds) { // not change
            if (!array_diff($itemsOldIds, $programLangIds) && 
                !array_diff($programLangIds, $itemsOldIds)
            ) {
                return false;
            }
        } elseif (!count($itemsOldIds)) { // not change, not add
            return false;
        }
        if (!isset($option['project']) || !$option['project']) {
            $option['project'] = Project::find($member->project_id);
            if (!$option['project']) {
                return false;
            }
        }
        $projectProgramLang = array_keys(
            ProjectProgramLang::getProgramLangOfProject($option['project'])
        );
        $programLangInsert = array_intersect($projectProgramLang, $programLangIds);
        DB::beginTransaction();
        try {
            self::where('proj_member_id', $member->id)
                ->delete();
            if (!$programLangIds || !$programLangInsert) {
                DB::commit();
                return true;
            }
            $dataInsert = [];
            foreach ($programLangInsert as $id) {
                $dataInsert[] = [
                    'proj_member_id' => $member->id,
                    'prog_lang_id' => $id
                ];
            }
            self::insert($dataInsert);
            DB::commit();
            return true;
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
    
    /**
     * load collection with program lang of parent
     * 
     * @param object $collection
     * @return object|array
     */
    public static function loadProgLangForMembersParent($collection)
    {
        if (!$collection || !count($collection)) {
            return $collection;
        }
        $ids = [];
        foreach ($collection as $item) {
            if ($item->parent_id) {
                $ids[] = $item->parent_id;
            }
        }
        if (!$ids) {
            return $collection;
        }
        $tableMember = ProjectMember::getTableName();
        $tableMemberProg = self::getTableName();
        $tableProgram = Programs::getTableName();
        // get member parent with language
        $collectionMemberLang = ProjectMember::select($tableMember.'.id')
            ->addSelect(DB::raw('GROUP_CONCAT('.$tableProgram.'.name '
                . 'SEPARATOR \', \') as prog_langs'))
            ->join($tableMemberProg, $tableMemberProg.'.proj_member_id', '=',
                $tableMember.'.id')
            ->join($tableProgram, $tableProgram.'.id', '=',
                $tableMemberProg.'.prog_lang_id')
            ->whereIn($tableMember.'.id', $ids)
            ->get()
            ->keyBy('id');
        if (!count($collectionMemberLang)) {
            return $collection;
        }
        // process member child has programs be parent
        $result = [];
        foreach ($collection as $item) {
            if (!$item->parent_id) {
                $result[] = $item;
                continue;
            }
            $itemParent = $collectionMemberLang->get($item->parent_id);
            if (!$itemParent) {
                $result[] = $item;
                continue;
            }
            $item->prog_langs = $itemParent->prog_langs;
            $result[] = $item;
        }
        return $result;
    }
    
    /**
     * update language of 2 object member
     * 
     * @param model $memberParent
     * @param model $memberChild
     * @throws Exception
     */
    public static function updateLangForParent($memberParent, $memberChild)
    {
        DB::beginTransaction();
        try {
            //delete langage of parent
            self::where('proj_member_id', $memberParent->id)
                ->delete();
            // update language of child = update proj_member_id
            self::where('proj_member_id', $memberChild->id)
                ->update([
                    'proj_member_id' => $memberParent->id
                ]);
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
    
    /**
     * get type of member availabel language
     * 
     * @return array
     */
    public static function getTypeMemberAvaiLang()
    {
        return [
            ProjectMember::TYPE_DEV, 
            ProjectMember::TYPE_TEAM_LEADER, 
            ProjectMember::TYPE_PM,
            ProjectMember::TYPE_SUBPM
        ];
    }
}
