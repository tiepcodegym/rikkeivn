<?php
namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Project\Model\ProjectClassification;


class ProjectClassificationOtherSeeder extends CoreSeeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed()) {
            return true;
        }
        $arrInsert = [];
        $arrInsert[] = [
            'classification_name' => 'Other',
            'is_other_type' => 1,
        ];

        ProjectClassification::insert($arrInsert);

        $this->insertSeedMigrate();
    }
}
