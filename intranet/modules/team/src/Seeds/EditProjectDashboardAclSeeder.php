<?php
namespace Rikkei\Team\Seeds;

use DB;
use Rikkei\Team\Model\Action;

class EditProjectDashboardAclSeeder extends \Rikkei\Core\Seeds\CoreSeeder
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
        Action::where('route', 'project::dashborad')->update([
            'route' => 'project::dashboard',
        ]);
        DB::beginTransaction();
        try {
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
