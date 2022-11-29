<?php

namespace Rikkei\Test\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Test\Models\LangGroup;
use Rikkei\Test\Models\Test;
use Illuminate\Support\Facades\DB;

class LangGroupSeeder extends CoreSeeder
{

    public function run() {
        if ($this->checkExistsSeed()) {
            return;
        }

        $tests = Test::all();
        if ($tests->isEmpty()) {
            return;
        }

        DB::beginTransaction();
        try {
            $groupId = 1;
            $defaultLang = 'vi';
            $dataInsert = [];
            foreach ($tests as $test) {
                $exists = LangGroup::where('test_id', $test->id)
                    ->where('group_id', $groupId)
                    ->first();
                if ($exists) {
                    $groupId++;
                    continue;
                }
                $dataInsert[] = [
                    'group_id' => $groupId++,
                    'test_id' => $test->id,
                    'lang_code' => $defaultLang,
                ];
            }
            if (count($dataInsert) > 0) {
                LangGroup::insert($dataInsert);
            }

            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex){
            DB::rollback();
            \Log::info($ex);
            throw $ex;
        }
    }

}
