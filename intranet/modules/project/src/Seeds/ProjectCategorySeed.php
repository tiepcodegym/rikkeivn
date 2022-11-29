<?php
namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Project\Model\ProjectCategory;


class ProjectCategorySeed extends CoreSeeder
{
    const CATEGORY_DEVELOPMENT = 1;
    const CATEGORY_MIGRATION = 2;
    const CATEGORY_MAINTENACE = 3;
    const CATEGORY_TEST = 4;
    const CATEGORY_OTHER = 5;

    /**
     * Run the database seeds.
     *
     * @return bool
     * @throws \Exception
     */
    public function run()
    {
        if ($this->checkExistsSeed()) {
            return true;
        }
        $arrCategory = self::lablelCategory();
        $arrInsert = [];
        foreach ($arrCategory as $key => $value) {
            if($key == self::CATEGORY_OTHER) {
                $arrInsert[] = [
                    'category_name' => $value,
                    'is_other_type' => 1,
                ];
            } else {
                $arrInsert[] = [
                    'category_name' => $value,
                    'is_other_type' => 0,
                ];
            }
        }
        ProjectCategory::insert($arrInsert);

        $this->insertSeedMigrate();
    }

    /**
     * label of state
     *
     * @return array
     */
    public static function lablelCategory() {
        return [
            self::CATEGORY_DEVELOPMENT => 'Development',
            self::CATEGORY_MIGRATION => 'Migration',
            self::CATEGORY_MAINTENACE => 'Maintenance',
            self::CATEGORY_TEST => 'Test',
            self::CATEGORY_OTHER => 'Other'
        ];
    }
}
