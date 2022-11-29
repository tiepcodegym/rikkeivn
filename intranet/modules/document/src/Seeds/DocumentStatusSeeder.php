<?php

namespace Rikkei\Document\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Document\Models\Document;
use Rikkei\Document\Models\DocRequest;
use Rikkei\Document\View\DocConst;

class DocumentStatusSeeder extends CoreSeeder
{

    public function run()
    {
        if ($this->checkExistsSeed(3)) {
            return;
        }

        //update document status from approve to review
        Document::where('status', DocConst::STT_APPROVED)
                ->update(['status' => DocConst::STT_REVIEWED]);
        //update document request
        DocRequest::where('status', '!=', DocConst::STT_APPROVED)
                ->update(['status' => DocConst::STT_APPROVED]);

        $this->insertSeedMigrate();
    }

}
