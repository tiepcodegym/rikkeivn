<?php
namespace Rikkei\Team\Seeds;

use Illuminate\Support\Facades\DB;
use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Team\Model\Faculty;
use Illuminate\Support\Facades\Artisan;

class SFacutlySeeder extends CoreSeeder
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
                DIRECTORY_SEPARATOR .  's_faculty';
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
                if (Faculty::where('name', $name)->count()) {
                    continue;
                }
                $inserts[] = [
                    'name' => $name
                ];
            }
            if (count($inserts)) {
                Faculty::insert($inserts);
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
