<?php

namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Illuminate\Support\Facades\DB;
use Rikkei\Project\Model\ProjectBusiness;
use Exception;

class ProjectBusinessUpdateSeeder extends CoreSeeder
{
    const BUSINESS_ENTER = 6;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        if ($this->checkExistsSeed()) {
            return;
        }
        $enter = ProjectBusiness::select('id', 'business_name')
            ->where('id', self::BUSINESS_ENTER)
            ->first();
        if (!count($enter)) {
            $this->insertSeedMigrate();
            return;
        }
        DB::beginTransaction();
        try {
            $enter->business_name = "Media-Entertainment";
            $enter->save();
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
