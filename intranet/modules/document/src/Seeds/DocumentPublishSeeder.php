<?php

namespace Rikkei\Document\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Document\Models\Document;
use Rikkei\Document\Models\DocPublish;
use Rikkei\Document\View\DocConst;
use Rikkei\Team\Model\Team;
use Illuminate\Support\Facades\DB;

class DocumentPublishSeeder extends CoreSeeder
{

    public function run()
    {
        if ($this->checkExistsSeed(5)) {
            return;
        }

        DB::beginTransaction();
        try {
            Document::where('status', DocConst::STT_PUBLISH)
                ->update(['publish_all' => 1]);

            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            \Log::info($ex);
            DB::rollback();
        }
    }

}
