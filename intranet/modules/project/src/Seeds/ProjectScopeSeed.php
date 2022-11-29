<?php
namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Project\Model\ProjectScope;


class ProjectScopeSeed extends CoreSeeder
{
    const REQUIREMENT_SCOPE = 1;
    const REQUIREMENT_DESIGN = 2;
    const REQUIREMENT_CODING = 3;
    const REQUIREMENT_UT = 4;
    const REQUIREMENT_IT = 5;
    const REQUIREMENT_ST = 6;
    const REQUIREMENT_OTHER = 7;

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
        $arrScope = self::lablelScope();
        $arrInsert = [];
        foreach ($arrScope as $key => $value) {
            if($key == self::REQUIREMENT_OTHER) {
                $arrInsert[] = [
                    'proj_scope' => $value,
                    'is_other_type' => 1,
                ];
            } else {
                $arrInsert[] = [
                    'proj_scope' => $value,
                    'is_other_type' => 0,
                ];
            }
        }
        ProjectScope::insert($arrInsert);

        $this->insertSeedMigrate();
    }

    /**
     * label of state
     *
     * @return array
     */
    public static function lablelScope() {
        return [
            self::REQUIREMENT_SCOPE => 'Requirement',
            self::REQUIREMENT_DESIGN => 'Design',
            self::REQUIREMENT_CODING => 'Coding',
            self::REQUIREMENT_UT => 'UT',
            self::REQUIREMENT_IT => 'IT',
            self::REQUIREMENT_ST => 'ST',
            self::REQUIREMENT_OTHER => 'Other'
        ];
    }
}
