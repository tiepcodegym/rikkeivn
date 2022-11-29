<?php
namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Project\Model\ProjectSector;


class ProjectSectorSeed extends CoreSeeder
{
    const SUB_SECTOR_BAKING = 1;
    const SUB_SECTOR_INSURANCE = 2;
    const SUB_SECTOR_SECURITIES = 3;
    const SUB_SECTOR_ENTERTAINMENT = 4;
    const SUB_SECTOR_TELE = 5;
    const SUB_SECTOR_PUBLISHING = 6;
    const SUB_SECTOR_OTHERS = 7;

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
        $arrSector = self::lablelSector();
        $arrInsert = [];
        foreach ($arrSector as $key => $value) {
            if($key == self::SUB_SECTOR_OTHERS) {
                $arrInsert[] = [
                    'sub_sector' => $value,
                    'is_other_type' => 1,
                ];
            } else {
                $arrInsert[] = [
                    'sub_sector' => $value,
                    'is_other_type' => 0,
                ];
            }
        }
        ProjectSector::insert($arrInsert);

        $this->insertSeedMigrate();
    }

    /**
     * label of state
     *
     * @return array
     */
    public static function lablelSector() {
        return [
            self::SUB_SECTOR_BAKING => 'Banking',
            self::SUB_SECTOR_INSURANCE => 'Insurance',
            self::SUB_SECTOR_SECURITIES => 'Securities',
            self::SUB_SECTOR_ENTERTAINMENT => 'Entertainment',
            self::SUB_SECTOR_TELE => 'Telecommunications',
            self::SUB_SECTOR_PUBLISHING => 'Publishing and Advertising',
            self::SUB_SECTOR_OTHERS => 'Other'
        ];
    }
}
