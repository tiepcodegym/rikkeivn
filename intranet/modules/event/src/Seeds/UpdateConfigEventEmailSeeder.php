<?php

namespace Rikkei\Event\Seeds;

use Illuminate\Support\Facades\DB;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Core\Seeds\CoreSeeder;

class UpdateConfigEventEmailSeeder extends CoreSeeder
{

    public function run()
    {
        if ($this->checkExistsSeed()) {
            return;
        }
        
        $dataInsert = [
            'key' => 'event.eventday.company.subject.ja',
            'value' => '名古屋支社開設のお知らせ'
        ];

        try {
            DB::beginTransaction();
            $subjectJs = CoreConfigData::where('key', 'event.eventday.company.subject.ja')->first();
            if ($subjectJs) {
                $subjectJs->update([
                    'value' => '名古屋支社開設のお知らせ'
                ]);
            } else {
                CoreConfigData::insert($dataInsert);
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
        }
    }

}
