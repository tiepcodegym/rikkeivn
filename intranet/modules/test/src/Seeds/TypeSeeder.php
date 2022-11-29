<?php

namespace Rikkei\Test\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Test\Models\Type;
use Illuminate\Support\Facades\DB;

class TypeSeeder extends CoreSeeder {

    public function run() {
        if ($this->checkExistsSeed('TypeSeeder-v2')) {
            return;
        }
        
        DB::beginTransaction();
        try {
            $types = ['PHP', 'Python', 'Ruby', 'Net', 'Java', 'Unity', 'Android', 'Object-C', 'Swift'];
            foreach ($types as $type) {
                $typeItem = Type::where('name', $type)->first();
                if (!$typeItem) {
                    Type::create([
                        'name' => $type,
                        'code' => str_slug($type)
                    ]);
                } else {
                    $typeItem->update([
                        'code' => str_slug($typeItem->name)
                    ]);
                }
            }
            //seeder version 2
            $typesOld = Type::all();
            $subjectType = [
                    'name' => 'Chuyên môn',
                    'code' => 'subject'
                ];
            $gmatType = [
                    'name' => 'GMAT',
                    'code' => 'gmat'
                ];

            $subject = Type::where('code', 'subject')->first();
            if (!$subject) {
                $subject = Type::create($subjectType);
            }
            $gmat = Type::where('code', 'gmat')->first();
            if (!$gmat) {
                $gmat = Type::create($gmatType);
            }
            //update parent_id
            if (!$typesOld->isEmpty()) {
                foreach ($typesOld as $type) {
                    $type->update(['parent_id' => $subject->id]);
                }
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
