<?php
namespace Rikkei\Team\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Team\Model\CheckpointQuestion;
use DB;

class UpdateContentAchievement extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return bool
     * @throws Exception
     * @throws \Exception
     */
    public function run()
    {
        if ($this->checkExistsSeed('UpdateContentAchievement-v3')) {
            return true;
        }
        DB::beginTransaction();
        try {
            $dataTollTip = 'Experience at rikkei in 6 month (:from~:to)';
            CheckpointQuestion::join('checkpoint_category', 'checkpoint_category.id', '=', 'checkpoint_question.category_id')
                ->where('checkpoint_question.content', 'like', '%Số man month làm trong 6 tháng trước%')
                ->toBase()
                ->update([
                    'tooltip' => $dataTollTip,
                ]);
            $dataTollTip2 = 'Personal award at rikkei (:from~:to)';
            CheckpointQuestion::join('checkpoint_category', 'checkpoint_category.id', '=', 'checkpoint_question.category_id')
                ->where('checkpoint_question.content', 'like', '%Giải thưởng cá nhân tại Rikkei%')
                ->toBase()
                ->update([
                    'tooltip' => $dataTollTip2,
                ]);

            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
