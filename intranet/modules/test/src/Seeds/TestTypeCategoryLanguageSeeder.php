<?php

namespace Rikkei\Test\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Test\Models\Type;
use Illuminate\Support\Facades\DB;
use Rikkei\Test\Models\TypeMeta;

class TestTypeCategoryLanguageSeeder extends CoreSeeder {

    public function run() {
        if ($this->checkExistsSeed('TestTypeCategoryLanguageSeeder')) {
            return;
        }

        DB::beginTransaction();
        try {
            // Insert type vi
            $types = Type::all();
            $dataTypes = [];
            foreach ($types as $type) {
                $dataTypes[] = [
                    'name' => $type->name,
                    'lang_code' => 'vi',
                    'type_id' => $type->id,
                ];
            }
            TypeMeta::insert($dataTypes);

            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex){
            DB::rollback();
            \Log::info($ex);
            throw $ex;
        }
    }

}
