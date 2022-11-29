<?php
namespace Rikkei\Team\Seeds;

use Illuminate\Support\Facades\DB;
use Rikkei\Core\Seeds\CoreSeeder;
use Illuminate\Support\Facades\Artisan;
use Rikkei\Team\Model\EmployeeProjExper;
use Illuminate\Support\Facades\Log;

class SynchronizeEmployeeProjExpresSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed(1)) {
            return true;
        }

        DB::beginTransaction();
        try {
            // seed change tag_text to tag_id
            static::changeTagTextTagId();

            static::upateProjNumberEn();
            $arrMaxNumber = static::getMaxProjNumber();
            $projEn = static::getAllProject('en');
            $projJA = static::getAllProject('ja');
            $numberJA = [];
            $empProjJa = [];
            $keyprojJa = array_keys($projJA);
            $keyprojMax = array_keys($arrMaxNumber);
            $diff = array_diff($keyprojJa, $keyprojMax);
            if (count($diff)) {
                foreach ($diff as $key) {
                    $arrMaxNumber[$key] = 0;
                }
            }

            if (count($projJA) && count($projEn)) {
                foreach ($projJA as $empId => $projs) {
                    if (array_key_exists($empId, $projEn)) {
                        foreach ($projs as $projId => $items) {
                            //so sanh
                            $same = false;
                            foreach ($projEn[$empId] as $projIdEn => $teamEn) {
                                if ($items['start_at'] == $teamEn['start_at'] &&
                                    $items['end_at'] == $teamEn['end_at'] &&
                                    !array_diff($items['os'], $teamEn['os']) &&
                                    !array_diff($items['lang'], $teamEn['lang']) &&
                                    !array_diff($items['other'], $teamEn['other']) &&
                                    !array_diff($items['res'], $teamEn['res'])) {
                                    $same = true;
                                    $key = 'e-' . $empId . '-n-' . $projEn[$empId][$projIdEn]['proj_number'];
                                    if (in_array($key, $empProjJa)) {
                                        $numberJA[$projId] = ++$arrMaxNumber[$empId];
                                    } else {
                                        $numberJA[$projId] = $projEn[$empId][$projIdEn]['proj_number'];
                                    }
                                    $empProjJa[] = 'e-' . $empId . '-n-' . $numberJA[$projId];
                                    break;
                                }
                            }
                            if (!$same) {
                                $numberJA[$projId] = ++$arrMaxNumber[$empId];
                            }
                        }
                    } else {
                        foreach ($projs as $projId => $items) {
                            $numberJA[$projId] = ++$arrMaxNumber[$empId];
                        }
                    }
                }
            }

            if (count($numberJA)) {
                foreach ($numberJA as $key => $number) {
                    DB::table('employee_proj_expers')
                        ->where('id', $key)
                        ->update(['proj_number' => $number]);
                }
            }

            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            Log::info($ex);
        }
    }

    /**
     * update number project in table employee_proj_expers
     */
    public static function upateProjNumberEn()
    {
        DB::statement("SET @row_number:=0");
        DB::statement("SET @empProj:=0");

        $collect = DB::table('employee_proj_expers')
            ->select(
                "employee_proj_expers.id",
                "employee_proj_expers.start_at",
                "employee_proj_expers.end_at",
                DB::raw("
                    @row_number:= CASE WHEN @empProj = employee_proj_expers.employee_id THEN @row_number + 1
                        ELSE 1
                    END AS proj_number
                "),
                DB::raw("@empProj:= employee_proj_expers.employee_id as employee_id")
            )
            ->where("employee_proj_expers.lang_code", "en")
            ->groupBy("employee_proj_expers.id")
            ->orderBy("employee_proj_expers.employee_id")
            ->orderBy('employee_proj_expers.start_at', 'DESC')
            ->get();

        try {
            foreach ($collect as $value) {
                DB::update('update employee_proj_expers set proj_number = ' . $value->proj_number . ' where id = ' . $value->id);
            }
        } catch (Exception $ex) {
            Log::info($ex);
        }
    }

    /**
     * [getMaxProjNumber description]
     * @return [array]
     */
    public static function getMaxProjNumber()
    {
        $collect = DB::table('employee_proj_expers')
            ->select(
                "employee_proj_expers.employee_id",
                DB::raw("MAX(employee_proj_expers.proj_number) masMax")
            )
            ->where("employee_proj_expers.lang_code", 'en')
            ->groupBy("employee_proj_expers.employee_id")
            ->orderBy('employee_proj_expers.start_at', 'DESC')
            ->get();
        $arrMax = [];
        foreach ($collect as $key => $value) {
            if ($value->masMax == null || $value->masMax == '') {
                $max = 1;
            } else {
                $max = $value->masMax;
            }
            $arrMax[$value->employee_id] = $max;
        }
        return $arrMax;
    }

    /**
     * get all project
     * @param  [type] $langCode
     * @return [type]
     */
    public static function getAllProject($langCode)
    {
        $collect = DB::table('employee_proj_expers')
            ->select(
                "employee_proj_expers.id",
                "employee_proj_expers.employee_id",
                "employee_proj_expers.start_at",
                "employee_proj_expers.end_at",
                "employee_proj_expers.lang_code",
                "employee_proj_expers.proj_number",
                DB::raw("GROUP_CONCAT(DISTINCT(tagOS.value)) as os"),
                DB::raw("GROUP_CONCAT(DISTINCT(tagLang.value)) as lang"),
                DB::raw("GROUP_CONCAT(DISTINCT(tagOther.value)) as other"),
                DB::raw("GROUP_CONCAT(DISTINCT(
                    CASE
                        WHEN projRes.tag_id IS NULL THEN projRes.tag_text
                        WHEN projRes.tag_id IS NOT NULL THEN projRes.tag_id
                        ELSE NULL
                    END)) as res")
            )
            ->leftJoin('empl_proj_exper_tags as projOs', function($join) {
                $join->on('projOs.proj_exper_id', '=', 'employee_proj_expers.id');
                $join->where('projOs.type', 'LIKE','os');
            })
            ->leftJoin('kl_tags as tagOS', 'projOs.tag_id', '=', 'tagOS.id')
            ->leftJoin('empl_proj_exper_tags as projOther', function($join) {
                $join->on('projOther.proj_exper_id', '=', 'employee_proj_expers.id');
                $join->where('projOther.type', 'LIKE','os');
            })
            ->leftJoin('kl_tags as tagOther', 'projOther.tag_id', '=', 'tagOther.id')
            ->leftJoin('empl_proj_exper_tags as projLang', function($join) {
                $join->on('projLang.proj_exper_id', '=', 'employee_proj_expers.id');
                $join->where('projLang.type', 'LIKE','lang');
            })
            ->leftJoin('kl_tags as tagLang', 'projLang.tag_id', '=', 'tagLang.id')
            ->leftJoin('empl_proj_exper_tags as projRes', function($join) {
                $join->on('projRes.proj_exper_id', '=', 'employee_proj_expers.id');
                $join->where('projRes.type', 'LIKE','res');
            })
            ->where('employee_proj_expers.lang_code', $langCode)
            ->groupBy('employee_proj_expers.id')
            ->orderBy('employee_proj_expers.employee_id')
            ->orderBy('employee_proj_expers.start_at', 'DESC')
            ->get();

        $project = [];
        if ($collect) {
            foreach ($collect as $item) {
                $project[$item->employee_id][$item->id] = [
                    'start_at' => $item->start_at,
                    'end_at' => $item->end_at,
                    'os' => empty($item->os) ? [] : explode(",", $item->os),
                    'lang' => empty($item->lang) ? [] : explode(",", $item->lang),
                    'other' => empty($item->other) ? [] : explode(",", $item->other),
                    'res' => empty($item->res) ? [] : explode(",", $item->res),
                    'proj_number' => $item->proj_number,
                ];
            }
        }
        return $project;
    }

    /**
     * file import có đuôi ing, mảng getResponsiblesDefine không có
     * dẫn đến các lưu db vào tag_text chứ ko phải vào tag_id
     * @return
     */
    public static function changeTagTextTagId()
    {
        $projPosition = EmployeeProjExper::getResponsiblesDefine();
        $projPositionEn = $projPosition['en'];
        $projPositionJa = $projPosition['ja'];
        $projAddIng = [
            0 => 'Requirement Definitioning',
            1 => 'Basic Designing',
            2 => 'Detail Designing',
            3 => 'Coding',
            4 => 'Unit Testing',
            5 => 'Integration Testing',
            6 => 'Maintenancing',
        ];
        $projAddIng2 = [
            2 => 'Detailed Designing',
        ];

        $collect = DB::table('empl_proj_exper_tags')
            ->select('proj_exper_id', 'tag_id', 'tag_text', 'lang')
            ->where('type', 'res')
            ->whereNull('tag_id')
            ->whereNotNull('tag_text')
            ->orderBy('proj_exper_id')
            ->get();

        if ($collect) {
            foreach ($collect as $item) {
                $key = false;
                if ($item->lang == 'en') {
                    // project lang code en
                    if (in_array($item->tag_text, $projAddIng)) {
                        $key = array_search($item->tag_text, $projAddIng);
                    } elseif (in_array($item->tag_text, $projPositionEn)) {
                        $key = array_search($item->tag_text, $projPositionEn);
                    } elseif (in_array($item->tag_text, $projAddIng2)) {
                        $key = array_search($item->tag_text, $projAddIng2);
                    } else {

                    }
                } else {
                    // project lang code ja
                    if (in_array($item->tag_text, $projPositionJa)) {
                        $key = array_search($item->tag_text, $projPositionJa);
                    } else {

                    }
                }
                if ($key || is_numeric($key)) {
                    try {
                        DB::update('UPDATE empl_proj_exper_tags SET tag_id = ' . $key
                            .', tag_text = NULL' 
                            . ' WHERE proj_exper_id = ' . $item->proj_exper_id
                            . ' AND type = "res"'
                            . ' AND lang = "' . $item->lang . '" '
                            . ' AND tag_text LIKE "' . $item->tag_text . '"');
                    } catch (Exception $ex) {
                        Log::info($ex);
                    }
                }
            }
        }
    }
}
