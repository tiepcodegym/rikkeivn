<?php
namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Project\Model\ProjectKind;


class ProjectKindSeed extends CoreSeeder
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
        $arrKind = self::lablelKind();
        $arrInsert = [];
        foreach ($arrKind as $key => $value) {
            if($key == self::KIND_OTHER) {
                $arrInsert[] = [
                    'kind_name' => $value,
                    'is_other_type' => 1,
                    'status' => 1
                ];
            } else {
                $arrInsert[] = [
                    'kind_name' => $value,
                    'is_other_type' => 0,
                    'status' => 1
                ];
            }
        }
        ProjectKind::insert($arrInsert);

        $this->insertSeedMigrate();
    }

    /**
     * label of state
     *
     * @return array
     */
    public static function lablelKind() {
        return [
            self::KIND_OFFSHORE_VN => 'Offshore VN',
            self::KIND_OFFSHORE_JP => 'Offshore JP',
            self::KIND_OFFSHORE_EN => 'Offshore EN',
            self::KIND_ONSITE_JP => 'Onsite JP',
            self::KIND_INTERNAL => 'Internal',
            self::KIND_OTHER => 'Other'
        ];
    }
}
