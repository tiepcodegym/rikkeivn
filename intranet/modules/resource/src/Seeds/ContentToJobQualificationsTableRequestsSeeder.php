<?php
namespace Rikkei\Resource\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use DB;
use Rikkei\Resource\Model\ResourceRequest;

class ContentToJobQualificationsTableRequestsSeeder extends CoreSeeder
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
        DB::beginTransaction();
        try {
            $sql = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(content, '</p>\r\n', ''), '<p>', ''), '<h1>', ''), '</h1>\r\n', ''), '<b>', ''), '</b>', '')";
            ResourceRequest::whereRaw('1 = 1')->update([
                'job_qualifi' => DB::raw($sql),
            ]);
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
