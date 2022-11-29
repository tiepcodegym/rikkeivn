<?php

namespace Rikkei\Document\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Document\Models\Type;

class DocumentTypeSeeder extends CoreSeeder
{

    public function run()
    {
        if ($this->checkExistsSeed()) {
            return;
        }

        $dataDemo = [
            ['name' => 'Sổ tay chất lượng và An ninh thông tin'],
            ['name' => 'Các quy trình'],
            ['name' => 'Quy định công việc tại Công ty'],
        ];

        Type::insert($dataDemo);

        $this->insertSeedMigrate();
    }

}
