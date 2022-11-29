<?php
namespace Rikkei\Team\Seeds;

use DB;
use Exception;
use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Team\Model\Country;
use const RIKKEI_TEAM_PATH;

class LibCountrySeeder extends CoreSeeder
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
                DIRECTORY_SEPARATOR .  'country.php';
        if (! file_exists($dataFilePath)) {
            return;
        }
        $dataDemo = require $dataFilePath;
        if (! $dataDemo || ! count($dataDemo)) {
            return;
        }
        DB::beginTransaction();
        try {
            foreach($dataDemo as $item) {
                $model = Country::where('country_code', $item['Code'])
                    ->first();
                if (!$model) {
                    $model = new Country();
                }
                $model->setData([
                    'country_code' => $item['Code'],
                    'name'         => $item['Name'],
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
