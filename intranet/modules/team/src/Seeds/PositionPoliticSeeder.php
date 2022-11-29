<?php
namespace Rikkei\Team\Seeds;

use DB;
use Exception;
use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Team\Model\PartyPosition;
use Rikkei\Team\Model\UnionPosition;
use const RIKKEI_TEAM_PATH;

class PositionPoliticSeeder extends CoreSeeder
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
                DIRECTORY_SEPARATOR .  'position_politic.php';
        if (! file_exists($dataFilePath)) {
            return;
        }
        $dataDemo = require $dataFilePath;
        if (! $dataDemo || ! count($dataDemo)) {
            return;
        }
        DB::beginTransaction();
        try {
            $parties = (array) $dataDemo['party'];
            $unions = (array) $dataDemo['union'];
            foreach ($parties as $data) {
                $model = PartyPosition::find($data['id']);
                if (!$model) {
                    $model = new PartyPosition();
                }
                $model->setData([
                    'id' => $data['id'],
                    'name' => $data['name'],
                ]);
                $model->save();
            }

            foreach ($unions as $data) {
                $model = UnionPosition::find($data['id']);
                if (!$model) {
                    $model = new UnionPosition();
                }
                $model->setData([
                    'id' => $data['id'],
                    'name' => $data['name'],
                ]);
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
