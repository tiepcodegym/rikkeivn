<?php
namespace Rikkei\Team\Seeds;

use Illuminate\Support\Facades\DB;
use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Team\Model\School;
use Illuminate\Support\Facades\Artisan;

class SchoolSeeder extends CoreSeeder
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
        $dataFilePath = RIKKEI_TEAM_PATH . 'data-sample' . DIRECTORY_SEPARATOR . 'seed' . 
                DIRECTORY_SEPARATOR .  's_schools';
        if (!file_exists($dataFilePath)) {
            return;
        }
        $file = fopen($dataFilePath, 'r');
        DB::beginTransaction();
        try {
            $inserts = [];
            while(!feof($file)) {
                $name = trim(fgets($file));
                if (!$name) {
                    continue;
                }
                if (School::where('name', $name)->count()) {
                    continue;
                }
                $inserts[] = [
                    'name' => $name
                ];
            }
            if (count($inserts)) {
                School::insert($inserts);
            }
            Artisan::call('cache:clear');
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        } finally {
            fclose($file);
        }
    }
}
