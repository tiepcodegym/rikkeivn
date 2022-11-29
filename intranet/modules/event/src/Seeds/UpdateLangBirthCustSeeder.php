<?php

namespace Rikkei\Event\Seeds;

use Illuminate\Support\Facades\DB;
use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Event\Model\EventBirthday;
use Rikkei\Core\Model\CoreConfigData;

class UpdateLangBirthCustSeeder extends CoreSeeder
{

    public function run()
    {
        if ($this->checkExistsSeed(3)) {
            return;
        }
        try {
            DB::beginTransaction();
            EventBirthday::whereNull('lang')->update(["lang" => "ja"]);
            CoreConfigData::where("key", "event.bitrhday.company.subject")->update(["value" => "RIKKEISOFT 10th Anniversary Party Invitation"]);

            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
        }
    }

}
