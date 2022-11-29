<?php
namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Project\Model\MeTimeSheet;
use Illuminate\Support\Facades\DB;

class METimeSheetSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed('METimeSheetSeeder-v2')) {
            return;
        }
        //MeTimeSheet::where('late_time', '00:00:00')->delete();
        $condTime = '2018-01';
        MeTimeSheet::where(DB::raw('DATE_FORMAt(date, "%Y-%m")'), $condTime)
                ->whereNull('shift')
                ->delete();
        $this->insertSeedMigrate();
    }
}
