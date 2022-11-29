<?php
namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Project\Model\ProjectKind;


class ProjectKindSeedOnsite extends CoreSeeder
{
    const KIND_OFFSHORE_VN = 1;
    const KIND_OFFSHORE_JP = 2;
    const KIND_OFFSHORE_EN = 3;
    const KIND_ONSITE_JP = 4;
    const KIND_INTERNAL = 5;
    const KIND_OTHER = 6;

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
            'kind_name' => 'Onsite EN',
            'is_other_type' => 0,
            'status' => 1
        ];

        ProjectKind::insert($arrInsert);

        $this->insertSeedMigrate();
    }
}
