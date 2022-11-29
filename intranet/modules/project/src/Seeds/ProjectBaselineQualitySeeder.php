<?php
namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Project\Model\ProjPointBaseline;
use Illuminate\Support\Facades\DB;
use Exception;
use Rikkei\Project\Model\ProjectPoint;

class ProjectBaselineQualitySeeder extends CoreSeeder
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
        $collection = ProjPointBaseline::select('id', 'qua_defect_errors',
                'quality', 'summary')
            ->where('qua_defect_errors', '>=', ProjectPoint::FLAG_COLOR_QUA_DEFECT_YELLOW)
            ->get();
        if (!count($collection)) {
            $this->insertSeedMigrate();
            return true;
        }
        DB::beginTransaction();
        try {
            foreach ($collection as $item) {
                // check yellow color
                if ($item->qua_defect_errors < ProjectPoint::FLAG_COLOR_QUA_DEFECT_RED) {
                    $this->changeColor($item, [
                            'quality',
                            'summary'
                        ], 
                        [ProjectPoint::COLOR_STATUS_BLUE], 
                        ProjectPoint::COLOR_STATUS_YELLOW
                    );
                    continue;
                }
                // check red color
                $this->changeColor($item, [
                        'quality',
                        'summary'
                    ], 
                    [ProjectPoint::COLOR_STATUS_BLUE,ProjectPoint::COLOR_STATUS_YELLOW], 
                    ProjectPoint::COLOR_STATUS_RED
                );
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
    
    /**
     * change color for item baseline
     * 
     * @param model $item
     * @param array $arrayColumn columns compare color
     * @param array $ifColorOrg color origin 
     * @param int $colorChange color change
     */
    private function changeColor($item, $arrayColumn, $ifColorOrg, $colorChange)
    {
        foreach ($arrayColumn as $column) {
            if (in_array($item->{$column}, $ifColorOrg)) {
                $item->{$column} = $colorChange;
            }
        }
        $item->save();
        return $item;
    }
}
