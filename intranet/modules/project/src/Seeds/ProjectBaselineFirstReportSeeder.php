<?php
namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Illuminate\Support\Facades\DB;
use Exception;
use Rikkei\Project\Model\ProjPointBaseline;
use Rikkei\Core\View\View as CoreView;

class ProjectBaselineFirstReportSeeder extends CoreSeeder
{
    public function run()
    {
        if ($this->checkExistsSeed()) {
            return true;
        }
        $sundayLastWeek = CoreView::getDateLastWeek(null, 7);
        $collection = ProjPointBaseline::select('id', 'created_at', 'first_report')
            ->whereNull('first_report')
            ->whereDate('created_at', '<=', 
                $sundayLastWeek->format('Y-m-d'))
            ->get();
        if (!count($collection)) {
            $this->insertSeedMigrate();
            return true;
        }
        DB::beginTransaction();
        try {
            foreach ($collection as $item) {
                $item->first_report = $item->created_at;
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
