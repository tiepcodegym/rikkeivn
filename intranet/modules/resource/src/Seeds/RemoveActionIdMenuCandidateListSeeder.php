<?php
namespace Rikkei\Resource\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use DB;
use Rikkei\Core\Model\MenuItem;

class RemoveActionIdMenuCandidateListSeeder extends CoreSeeder
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
            MenuItem::where('name', 'Candidate list')
                ->update([
                    'action_id' => null,
                ]);
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
