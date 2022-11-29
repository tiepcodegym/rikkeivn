<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Resource\Model\Programs;
use Illuminate\Support\Facades\DB;
use Exception;
use Rikkei\Core\View\CacheHelper;
use Illuminate\Support\Facades\Lang;

class ProjectProgramLang extends CoreModel
{
    
    const KEY_CACHE = 'proj_program_lang';

    public $timestamps = false;
    protected $table = 'proj_prog_langs';
    
    /**
     * get programs language of project
     * 
     * @param type $project
     * @return type
     */
    public static function getProgramLangOfProject($project)
    {
        if (!is_object($project)) {
            $projectId = $project;
            $project = collect();
            $project->id = $projectId;
        }
        if ($result = CacheHelper::get(self::KEY_CACHE, $project->id)) {
            return $result;
        }
        $tableProjectProgram = self::getTableName();
        $tableProgram = Programs::getTableName();
        
        $collection = self::select($tableProjectProgram.'.prog_lang_id',
            $tableProgram.'.name')
            ->join($tableProgram, $tableProgram.'.id', '=',
                    $tableProjectProgram.'.prog_lang_id')
            ->where($tableProjectProgram.'.project_id', $project->id)
            ->get();
        if (!count($collection)) {
            return [];
        }
        $result = [];
        foreach ($collection as $item) {
            $result[$item->prog_lang_id] = $item->name;
        }
        CacheHelper::put(self::KEY_CACHE, $result, $project->id);
        return $result;
    }
    
    /**
     * get name project programming language
     * 
     * @param model $project
     * @param array $projectProgram
     * @return string
     */
    public static function getNameProgramLangOfProject(
        $project = null, 
        $projectProgram = null
    ) {
        if ($projectProgram === null) {
            $projectProgram = self::getProgramLangOfProject($project);
        }
        $result = '';
        if (!$projectProgram) {
            return $result;
        }
        foreach ($projectProgram as $item) {
            $result .= $item . ', ';
        }
        return substr($result, 0, -2);
    }
    
    /**
     * insert or update languages to project
     * 
     * @param model $project
     * @param array $programLangIds
     * @param array $option
     */
    public static function insertItems(
            $project, 
            array $programLangIds = [], 
            array $option = []
    ) {
        if (!$project || !$project->id) {
            return true;
        }
        // check items old same items new
        if ($programLangIds) {
            /*update data of prolang at cost productive prolang. */
            ProjectPoint::updateProductiveProgLang($project->id, $programLangIds);
            /* end update*/
            $itemsOld = self::getProgramLangOfProject($project);
            $langIds = array_keys($itemsOld);
            if (!array_diff($langIds, $programLangIds) && 
                !array_diff($programLangIds, $langIds)
            ) {
                return true;
            }
        }
        $programLangAvai = array_keys(Programs::getListOption());
        $programLangInsert = array_intersect($programLangAvai, $programLangIds);
        if (!isset($option['create']) || !$option['create']) {
            if (ProjectMemberProgramLang::existsMembersNotIncludeProgram(
                    $project, 
                    $programLangInsert)
            ) {
                return [
                    'status' => false,
                    'message_error' => [
                        'prog_langs' => Lang::get("project::message.Exists member's program language dont have new value")
                    ]
                ];
            }
        }
        DB::beginTransaction();
        try {
            if (!isset($option['create']) || !$option['create']) {
                self::where('project_id', $project->id)
                    ->delete();
            }
            CacheHelper::forget(self::KEY_CACHE, $project->id);
            if (!$programLangIds || !$programLangInsert) {
                DB::commit();
                return true;
            }
            $dataInsert = [];
            foreach ($programLangInsert as $id) {
                $dataInsert[] = [
                    'project_id' => $project->id,
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
     * check exists programs of project include programs ids
     * 
     * @param int $projectId
     * @param array $programIds
     * @return boolean
     */
    public static function isIncludeProgramIds($projectId, array $programIds = [])
    {
        if (!$projectId) {
            return false;
        }
        $collection = self::select(DB::raw('count(*) as count'))
            ->where('project_id', $projectId)
            ->whereIn('prog_lang_id', $programIds)
            ->first();
        if ($collection->count == count($programIds)) {
            return true;
        }
        return false;
    }
}
