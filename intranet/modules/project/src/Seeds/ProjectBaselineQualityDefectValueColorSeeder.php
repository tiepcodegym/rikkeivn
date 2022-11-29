<?php
namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Project\Model\ProjPointBaseline;
use Illuminate\Support\Facades\DB;
use Exception;
use Rikkei\Project\Model\ProjectPoint;
use Rikkei\Project\View\View as ProjectView;

class ProjectBaselineQualityDefectValueColorSeeder extends CoreSeeder
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
        $collection = ProjPointBaseline::whereDate('created_at', '>', '2017-05-14')
            ->get();
        if (!count($collection)) {
            $this->insertSeedMigrate();
            return true;
        }
        $pointDefault = ProjectPoint::pointValueDefault();
        DB::beginTransaction();
        try {
            foreach ($collection as $item) {
                $item->qua_defect_target = $pointDefault['qua_defect_target'];
                $item->qua_defect_ucl = $pointDefault['qua_defect_ucl'];
                $item->qua_defect_lcl = $pointDefault['qua_defect_lcl'];
                $quaDefect = $item->getQuaDefect();
                $quaLeakage = $item->getQuaLeakage();
                $quaLeakageColor = $item->getColor('qua_leakage_target', 
                    'qua_leakage_ucl', $quaLeakage);
                $quaDefectColor = $item->getQuaDefectColor($quaDefect);
                $quaColor = ProjectView::calculatorTotalColor([$quaLeakageColor, $quaDefectColor]);
                $item->quality = $quaColor;
                if ($item->summary < $quaColor) {
                    $item->summary = $quaColor;
                }
                $item->save();
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
