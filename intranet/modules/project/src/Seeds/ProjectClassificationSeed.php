<?php
namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Project\Model\ProjectClassification;


class ProjectClassificationSeed extends CoreSeeder
{
    const CLASS_SCRUM = 1;
    const CLASS_WATERFALL = 2;

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
        $arrClass = self::lablelClassification();
        $arrInsert = [];
        foreach ($arrClass as $key => $value) {
            $arrInsert[] = [
                'classification_name' => $value,
                'is_other_type' => 0,
            ];
        }
        ProjectClassification::insert($arrInsert);

        $this->insertSeedMigrate();
    }

    /**
     * label of state
     *
     * @return array
     */
    public static function lablelClassification() {
        return [
            self::CLASS_SCRUM => 'Scrum',
            self::CLASS_WATERFALL => 'Waterfall ',
        ];
    }
}
