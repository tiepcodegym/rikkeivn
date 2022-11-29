<?php
namespace Rikkei\Assets\Seeds;

use Illuminate\Support\Facades\Log;
use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Team\Model\EmployeeTeamHistory;
use Rikkei\Assets\Model\RequestAsset;
use Rikkei\Team\Model\Team;
use Illuminate\Support\Facades\DB;
use Rikkei\Assets\View\AssetConst;

class UpdateTeamPrefixOfEmployeeAssetRequest extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed()) {
            return;
        }
        DB::beginTransaction();
        try {
            $tblTeamHistory = EmployeeTeamHistory::getTableName();
            $tblRequestAsset = RequestAsset::getTableName();
            $teamTable = Team::getTableName();
            DB::table('request_assets')->leftJoin(
                DB::raw(
                    '(SELECT eth1.* FROM '. $tblTeamHistory .' AS eth1 '
                    . 'INNER JOIN '
                    . '(SELECT employee_id, MAX(start_at) as max_start_at FROM '. $tblTeamHistory .' '
                    . 'WHERE end_at IS NULL '
                    . 'GROUP BY employee_id) as eth2 '
                    . 'ON eth1.employee_id = eth2.employee_id '
                    . 'AND eth1.start_at = eth2.max_start_at AND eth1.end_at is null '
                    . 'GROUP BY eth1.employee_id) as eth'
                ), $tblRequestAsset . '.employee_id', '=', 'eth.employee_id')
                ->leftJoin($teamTable . ' as teamh', 'eth.team_id', '=', 'teamh.id')
                ->update([
                    'request_assets.team_prefix' => DB::raw('('.AssetConst::selectCasePrefix('SUBSTRING_INDEX(teamh.code, "_", 1)').')'),
                ]);
            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex) {
            Log::info($ex);
            DB::rollback();
        }
    }
}
