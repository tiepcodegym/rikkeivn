<?php
namespace Rikkei\Team\Seeds;

use Exception;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Team\Model\MilitaryArm;
use Rikkei\Team\Model\MilitaryPosition;
use Rikkei\Team\Model\MilitaryRank;
use const RIKKEI_TEAM_PATH;

class MilitarySeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed(2)) {
            return true;
        }
        $dataFilePath = RIKKEI_TEAM_PATH . 'data-sample' . DIRECTORY_SEPARATOR . 'seed' . 
                DIRECTORY_SEPARATOR .  'military.php';
        if (! file_exists($dataFilePath)) {
            return;
        }
        $dataDemo = require $dataFilePath;
        if (! $dataDemo || ! count($dataDemo)) {
            return;
        }
        DB::beginTransaction();
        try {
            $positions = (array) $dataDemo['position'];
            $ranks = (array) $dataDemo['rank'];
            $arms = (array) $dataDemo['arm'];

            foreach ($positions as $data) {
                $model = MilitaryPosition::where('name', $data)
                    ->first();
                if (!$model) {
                    $model = new MilitaryPosition();
                }
                $model->setData(['name' => $data]);
                $model->save();
            }

            foreach ($ranks as $data) {
                $model = MilitaryRank::where('name', $data)
                    ->first();
                if (!$model) {
                    $model = new MilitaryRank();
                }
                $model->setData(['name' => $data]);
                $model->save();
            }

            foreach ($arms as $data) {
                $model = MilitaryArm::where('name', $data)
                    ->first();
                if (!$model) {
                    $model = new MilitaryArm();
                }
                $model->setData(['name' => $data]);
                $model->save();
            }

            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
