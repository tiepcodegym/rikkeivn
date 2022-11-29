<?php

namespace Rikkei\Test\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Test\Models\Type;
use Illuminate\Support\Facades\DB;

class AddRikkeiCodeTypeSeeder extends CoreSeeder {

    public function run() {
        if ($this->checkExistsSeed()) {
            return;
        }
        
        DB::beginTransaction();
        try {
            $type = 'Rikkei Code';
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
            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex){
            DB::rollback();
            \Log::info($ex);
            throw $ex;
        }
    }

}
