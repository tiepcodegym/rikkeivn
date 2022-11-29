<?php
namespace Rikkei\Team\Seeds;

use Illuminate\Support\Facades\DB;
use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Team\Model\Province;
use Rikkei\Team\Model\Country;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Exception;

class ProvinceViSeeder extends CoreSeeder
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
        $folder = RIKKEI_TEAM_PATH . 'data-sample' . DIRECTORY_SEPARATOR
            . 'seed' . DIRECTORY_SEPARATOR . 'province';
        if (!File::exists($folder) || File::isFile($folder)) {
            return;
        }
        $files = File::files($folder);
        if (!$files) {
            return true;
        }
        $inserts = [];
        foreach ($files as $file) {
            try {
                $fileName = preg_replace('/.*(\/|\\\)/', '', $file);
            } catch (Exception $ex) {
                $fileName = preg_replace('/.*(\/)/', '', $file);
            }
            if (!$fileName) {
                continue;
            }
            $country = Country::select(['id'])
                ->where('country_code', '=', $fileName)
                ->first();
            if (!$country) {
                continue;
            }
            $country = $country->id;
            $file = fopen($file, 'r');
            try {
                while(!feof($file)) {
                    $name = trim(fgets($file));
                    if (!$name) {
                        continue;
                    }
                    if (
                        Province::select(['id'])
                            ->where('province', '=', $name)
                            ->where('country_id', '=', $country)
                            ->first()
                    ) {
                        continue;
                    }
                    $inserts[] = [
                        'country_id' => $country,
                        'province' => $name
                    ];
                }
            } catch (Exception $ex) {
                throw $ex;
            } finally {
                fclose($file);
            }
        }
        DB::beginTransaction();
        try {
            if (count($inserts)) {
                Province::insert($inserts);
            }
            Artisan::call('cache:clear');
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
