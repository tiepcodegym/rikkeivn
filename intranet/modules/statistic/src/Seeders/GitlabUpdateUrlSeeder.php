<?php
namespace Rikkei\Statistic\Seeders;

use Rikkei\Core\Seeds\CoreSeeder;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\Model\CoreConfigData;

class GitlabUpdateUrlSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed(1)) {
            return true;
        }
        $gitlabUrl = CoreConfigData::getItem('project.gitlab_api_url');
        DB::beginTransaction();
        try {
            $gitlabUrl->value = 'http://git.rikkei.org/api/v4/';
            $gitlabUrl->save();
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
