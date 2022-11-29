<?php
namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Project\Model\ProjRewardMeta;
use Rikkei\Project\Model\TaskHistory;

class ProjRewardMetasSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed('ProjRewardMetas-v2')) {
            return true;
        }
        $TaskHistory = TaskHistory::getTableName();
        $projRewardMetaTable = ProjRewardMeta::getTableName();
        $collection = ProjRewardMeta::select($projRewardMetaTable.'.id', 
                $projRewardMetaTable.'.task_id', $projRewardMetaTable.'.approve_date',
                $TaskHistory.'.updated_at'
        )
            ->join($TaskHistory, $projRewardMetaTable.'.task_id', '=', $TaskHistory.'.task_id')
            ->whereNull($projRewardMetaTable.'.approve_date')
            ->where($TaskHistory.'.content', '=', 'Confirmed Reward')
            ->get();
        if (!count($collection)) {
            return;
        }
        foreach ($collection as $item) {
            $item->approve_date = $item->updated_at;
            $item->save();
        }
        $this->insertSeedMigrate();
    }
}
