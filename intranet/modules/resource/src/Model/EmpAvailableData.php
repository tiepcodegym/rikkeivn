<?php

namespace Rikkei\Resource\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\Model\Project;
use Illuminate\Support\Facades\DB;
use Rikkei\Tag\Model\Tag;
use Rikkei\Team\Model\EmployeeSkill;
use Rikkei\Team\Model\Skill;
use Rikkei\Team\Model\EmplCvAttrValue;

class EmpAvailableData extends CoreModel
{
    protected $table = 'employee_available_data';
    protected $primaryKey = 'employee_id';
    protected $fillable = ['employee_id', 'projects', 'foreign_langs', 'program_langs'];
    public $incrementing = false;

    /*
     * cronjob update data
     */
    public static function cronUpdate()
    {
        $projTbl = Project::getTableName();
        $projMbTbl = ProjectMember::getTableName();
        $empSkillTbl = EmployeeSkill::getTableName();
        $tagTbl = Tag::getTableName();
        $empCvAttrTbl = EmplCvAttrValue::getTableName();

        //collect projects allocation
        $collection = ProjectMember::select($projMbTbl.'.employee_id', 'proj.name', $projMbTbl.'.start_at', $projMbTbl.'.end_at', $projMbTbl.'.effort')
                ->join($projTbl . ' as proj', 'proj.id', '=', $projMbTbl.'.project_id')
                ->where($projMbTbl.'.status', ProjectMember::STATUS_APPROVED)
                ->whereNull('proj.deleted_at')
                ->whereNull($projMbTbl.'.deleted_at')
                ->orderBy($projMbTbl.'.end_at', 'desc')
                ->groupBy($projMbTbl.'.id')
                ->get()
                ->groupBy('employee_id')
                ->toArray();
        //collect language
        $collectProgLangs = EmployeeSkill::select(
            $empSkillTbl.'.employee_id',
            DB::raw('CAST(('. $empSkillTbl . '.exp_y + ' . $empSkillTbl . '.exp_m / 12) AS DECIMAL(4,1)) AS exp_ym'),
            'tag.id',
            'tag.value as name'
        )
            //language tag
            ->join($tagTbl . ' as tag', function ($join) use ($empSkillTbl) {
                $join->on($empSkillTbl.'.tag_id', '=', 'tag.id')
                        ->whereNull('tag.deleted_at');
            })
            ->where($empSkillTbl.'.type', Skill::TYPE_PROGRAM)
            ->orderBy($empSkillTbl.'.exp_y', 'desc')
            ->orderBy($empSkillTbl.'.exp_m', 'desc')
            ->groupBy($empSkillTbl.'.id')
            ->get()
            ->groupBy('employee_id')
            ->toArray();
        //collect foreign language
        $collectForeigns = EmplCvAttrValue::select('foreign.employee_id', 'foreign.code', 'foreign.value')
            ->from($empCvAttrTbl . ' as foreign')
            ->whereIn('foreign.code', ['lang_en_level', 'lang_ja_level'])
            ->get()
            ->groupBy('employee_id')
            ->toArray();

        $empIds = array_unique(
            array_merge(
                array_keys($collection),
                array_keys($collectProgLangs),
                array_keys($collectForeigns)
            )
        );
        if (!$empIds) {
            return;
        }

        DB::beginTransaction();
        try {
            foreach ($empIds as $id) {
                $empItem = self::find($id);
                $empData = ['employee_id' => $id];
                if (isset($collection[$id]) && ($empProj = $collection[$id])) {
                    $empData['projects'] = json_encode($empProj);
                }
                if (isset($collectForeigns[$id]) && ($empForeign = $collectForeigns[$id])) {
                    $strForeign = '';
                    foreach ($empForeign as $foreign) {
                        $strForeign .= $foreign['value'] . ', ';
                    }
                    $empData['foreign_langs'] = trim($strForeign, ', ');
                }
                if (isset($collectProgLangs[$id]) && ($empProgs = $collectProgLangs[$id])) {
                    $aryProgs = [];
                    foreach ($empProgs as $prog) {
                        $aryProgs[] = [
                            'id' => $prog['id'],
                            'name' => $prog['name'],
                            'exp_ym' => $prog['exp_ym']
                        ];
                    }
                    $empData['program_langs'] = json_encode($aryProgs);
                }

                if (!$empItem) {
                    self::create($empData);
                } else {
                    $empItem->update($empData);
                }
            }

            DB::commit();
            return true;
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
            return false;
        }
    }
}
