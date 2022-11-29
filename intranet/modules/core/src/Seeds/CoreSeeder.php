<?php
namespace Rikkei\Core\Seeds;

use Illuminate\Database\Seeder;
use DB;
use Exception;

abstract class CoreSeeder extends Seeder
{
    protected $tableMigrate = 'migrations';
    protected $key = null;
    protected $existsSeed = false;
    protected $version = 1;
    /**
     * check seed exists in table migrate
     * 
     * @param string|null $version
     * @return boolean
     */
    protected function checkExistsSeed($version = null)
    {
        // set key to insert to migrate table
        if (!$version) {
            $this->version = 1;
        } else {
            $this->version = preg_replace('/[^0-9]+/', '', $version);
        }
        $key = get_called_class();
        $key = 'seed-' . $key;
        $this->key = $key;
        $item = DB::table($this->tableMigrate)
            ->where('migration', $key)
            ->first();
        if (!$item) {
            return false;
        }
        $this->existsSeed = true;
        if ($item->batch == $this->version) {
            return true;
        }
        return false;
    }
    
    /**
     * insert data seed check into migrate table
     * 
     * @return null
     * @throws Exception
     */
    protected function insertSeedMigrate()
    {
        if (!$this->key) {
            return true;
        }
        try {
            if ($this->existsSeed) {
                DB::table($this->tableMigrate)
                    ->where('migration', $this->key)
                    ->update([
                        'batch' => $this->version
                    ]);
            } else {
                DB::table($this->tableMigrate)
                    ->insert([[
                        'migration' => $this->key,
                        'batch' => $this->version
                    ]]);
            }
        } catch (Exception $ex) {
            throw $ex;
        }
    }
}

