<?php
namespace Rikkei\Team\Seeds;

use Rikkei\Team\Model\EmployeeProjExper;
use Rikkei\Team\Model\EmplCvAttrValue;
use Rikkei\Team\Model\EmplCvAttrValueText;
use Rikkei\Team\Model\EmplProjExperTag;
use DB;

class CvProjectLangSeeder extends \Rikkei\Core\Seeds\CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed()) {
            return true;
        }
        $cvProjs = EmployeeProjExper::select(
            'cvproj.*',
            DB::raw('GROUP_CONCAT(DISTINCT(CONCAT(val.code, "---", val.value)) SEPARATOR "|||") as proj_name_values'),
            DB::raw('GROUP_CONCAT(DISTINCT(CONCAT(valtext.code, "---", valtext.value)) SEPARATOR "|||") as proj_desc_values')
        )
            ->from(EmployeeProjExper::getTableName() . ' as cvproj')
            ->leftJoin(EmplCvAttrValue::getTableName() . ' as val', function ($join) {
                $join->on('cvproj.employee_id', '=', 'val.employee_id')
                        ->on('val.code', 'LIKE', DB::raw('CONCAT("proj_", cvproj.id, "_name_%")'));
            })
            ->leftJoin(EmplCvAttrValueText::getTableName() . ' as valtext', function ($join) {
                $join->on('cvproj.employee_id', '=', 'valtext.employee_id')
                        ->on('valtext.code', 'LIKE', DB::raw('CONCAT("proj_", cvproj.id, "_description_%")'));
            })
            ->where(function ($query) {
                $query->whereNotNull('val.code')
                        ->orWhereNotNull('valtext.code');
            })
            ->groupBy('cvproj.id')
            ->get();

        if ($cvProjs->isEmpty()) {
            return true;
        }

        DB::beginTransaction();
        try {
            $oldCodeNeedDel = [
                'val' => [],
                'text' => []
            ];
            foreach ($cvProjs as $proj) {
                $aryLangNames = explode('|||', $proj->proj_name_values);
                if (count($aryLangNames) == 1) {
                    //proj_id_name_en---value
                    $codeNames = explode('_', explode('---', $aryLangNames[0])[0]);
                    $proj->lang_code = $codeNames[count($codeNames) - 1];
                    $proj->save();
                } elseif (count($aryLangNames) == 2) {
                    //keep en proj
                    $proj->lang_code = 'en';
                    $proj->save();
                    $dataLang = $this->convertLangCode($proj);
                    //make new employee project cv
                    $dataNewProj = array_only($proj->toArray(), $proj->getFillable());
                    $dataNewProj['employee_id'] = $proj->employee_id;
                    $dataNewProj['lang_code'] = 'ja';
                    $dataNewProj['en_id'] = $proj->id;
                    $jaProj = EmployeeProjExper::create($dataNewProj);
                    //value
                    EmplCvAttrValue::create([
                        'employee_id' => $jaProj->employee_id,
                        'code' => 'proj_' . $jaProj->id . '_name_ja',
                        'value' => $dataLang['ja']['name']
                    ]);
                    //value text
                    if (isset($dataLang['ja']['description'])) {
                        EmplCvAttrValueText::create([
                            'employee_id' => $jaProj->employee_id,
                            'code' => 'proj_' . $jaProj->id . '_description_ja',
                            'value' => $dataLang['ja']['description']
                        ]);
                    }
                    $experTags = $proj->experTags;
                    if (!$experTags->isEmpty()) {
                        $dataInsertTags = [];
                        foreach ($experTags as $tag) {
                            $aryTag = $tag->toArray();
                            $aryTag['proj_exper_id'] = $jaProj->id;
                            if ($aryTag['lang'] == 'en') {
                                $aryTag['lang'] = 'ja';
                            }
                            $dataInsertTags[] = $aryTag;
                        }
                        EmplProjExperTag::insert($dataInsertTags);
                    }
                    //remove name and decription old ja
                    $oldCodeNeedDel['val'][] = $dataLang['ja']['key_name'];
                    if (isset($dataLang['ja']['key_description'])) {
                        $oldCodeNeedDel['text'][] = $dataLang['ja']['key_description'];
                    }
                }
            }
            //remove old code
            if ($oldCodeNeedDel['val']) {
                EmplCvAttrValue::whereIn('code', $oldCodeNeedDel['val'])->delete();
            }
            if ($oldCodeNeedDel['text']) {
                EmplCvAttrValueText::whereIn('code', $oldCodeNeedDel['text'])->delete();
            }

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
        }
    }

    /**
     * find index by lang code
     * @param type $code en, ja
     * @param type $aryNames ['proj_id_name_en---value', 'proj_id_name_ja---value']
     */
    public function convertLangCode($proj)
    {
        $aryNames = explode('|||', $proj->proj_name_values);
        $aryDescs = explode('|||', $proj->proj_desc_values);
        $results = [];
        foreach ($aryNames as $index => $strValue) {
            $aryCodeValue = explode('---', $strValue);
            $codeNames = explode('_', $aryCodeValue[0]);
            $code = $codeNames[count($codeNames) - 1];
            if (!isset($results[$code])) {
                $results[$code] = [];
            }
            $results[$code] = [
                'name' => $aryCodeValue[1],
                'key_name' => $aryCodeValue[0],
            ];
            foreach ($aryDescs as $strDesc) {
                $aryDescValue = explode('---', $strDesc);
                $codeDescs = explode('_', $aryDescValue[0]);
                $codeDesc = $codeDescs[count($codeDescs) - 1];
                if ($codeDesc == $code) {
                    $results[$code]['description'] = $aryDescValue[1];
                    $results[$code]['key_description'] = $aryDescValue[0];
                }
            }
        }
        return $results;
    }

    public function convertProjTags($projId)
    {
        $tagCollection = EmplProjExperTag::select(['proj_exper_id', 'tag_id', 'type',
            'tag_text', 'lang'])
            ->where('proj_exper_id', $projId)
            ->get();
        if ($tagCollection->isEmpty()) {
            return [];
        }
        $result = [];
        foreach ($tagCollection as $item) {
            $itemResult = [
                'id' => $item->tag_id,
                'text' => $item->tag_text,
                'lang' => $item->lang,
            ];
            if (!isset($result[$item->type])) {
                $result[$item->type] = [];
            }
            $result[$item->type][] = $itemResult;
        }
        return $result;
    }
}
